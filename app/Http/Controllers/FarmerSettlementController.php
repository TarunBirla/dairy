<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Farmer;
use App\Models\FarmerSettlement;
use App\Models\FarmerInvoice;
use App\Models\FarmerLedger;
use App\Models\FarmerAdvance;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerSettlementController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $paymentMode = $request->get('payment_mode');

        // Auto-sync any invoices that already have matching paid settlements in farmer_settlements
        try {
            FarmerInvoice::where('status', 'generated')
                ->whereExists(function ($sq) {
                    $sq->select(DB::raw(1))
                        ->from('farmer_settlements')
                        ->whereColumn('farmer_settlements.farmer_id', 'farmer_invoices.farmer_id')
                        ->whereColumn('farmer_settlements.period_start', 'farmer_invoices.period_start')
                        ->whereColumn('farmer_settlements.period_end', 'farmer_invoices.period_end')
                        ->where('farmer_settlements.status', 'paid');
                })
                ->update(['status' => 'settled']);

            // Auto-fix any settlement where deduction_amount + advance_recovered exceeds actual (gross + bonus - paid)
            $misalignedSettlements = FarmerSettlement::all();
            foreach ($misalignedSettlements as $st) {
                $actualDed = round((float) $st->gross_amount + (float) $st->bonus_amount - (float) $st->paid_amount, 2);
                $sumDed = round((float) $st->deduction_amount + (float) $st->advance_recovered, 2);
                if (abs($sumDed - $actualDed) > 0.05 && (float) $st->advance_recovered > 0) {
                    $st->update([
                        'deduction_amount' => max(0, round($actualDed - (float) $st->advance_recovered, 2))
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        // Badge counts (total pending invoices waiting for payout vs total settled payouts)
        $pendingCount = FarmerInvoice::where('status', 'generated')
            ->where('net_payment', '>', 0)
            ->count();
        $settledCount = FarmerSettlement::count();

        // 1. Pending Invoices Query (Ready for Payout)
        $pendingQuery = FarmerInvoice::with(['farmer', 'farmer.wallet'])
            ->where('status', 'generated')
            ->where('net_payment', '>', 0);

        if (!empty($startDate)) {
            $pendingQuery->whereDate('period_start', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $pendingQuery->whereDate('period_end', '<=', $endDate);
        }
        if (!empty($search)) {
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('village', 'like', "%{$search}%");
                  });
            });
        }

        $totalPendingPayable = (clone $pendingQuery)->sum('net_payment');
        $pendingInvoices = $pendingQuery->latest('period_end')->latest('id')->paginate(50, ['*'], 'pending_page')->withQueryString();

        // 2. Settled History Query
        $settledQuery = FarmerSettlement::with(['farmer', 'settler'])
            ->where('total_liters', '!=', 0);

        if (!empty($startDate)) {
            $settledQuery->whereDate('period_start', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $settledQuery->whereDate('period_end', '<=', $endDate);
        }

        if (!empty($search)) {
            $settledQuery->where(function ($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('village', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($paymentMode)) {
            $settledQuery->where('payment_mode', $paymentMode);
        }

        $totalSettledAmount = (clone $settledQuery)->where('status', 'paid')->sum('paid_amount');
        $settlements = $settledQuery->latest('settled_at')->latest('id')->paginate(20, ['*'], 'settled_page')->withQueryString();
        $farmers = Farmer::where('status', 'active')->orderBy('name')->get();

        return view('farmers.settlements.index', compact(
            'tab',
            'pendingCount',
            'settledCount',
            'pendingInvoices',
            'totalPendingPayable',
            'settlements',
            'totalSettledAmount',
            'farmers',
            'startDate',
            'endDate',
            'search',
            'paymentMode'
        ));
    }

    public function create(Request $request)
    {
        // Date range first define karein
        $periodStart = $request->get(
            'start_date',
            Carbon::now()->startOfMonth()->format('Y-m-d')
        );

        $periodEnd = $request->get(
            'end_date',
            Carbon::today()->format('Y-m-d')
        );

        // Active farmers jinki pending collections hain and non-zero liters (no 00 liters)
        $farmers = Farmer::where('status', 'active')
            ->whereIn('id', function ($query) use ($periodStart, $periodEnd) {
                $query->select('farmer_id')
                    ->from('milk_collections')
                    ->whereBetween('collection_date', [$periodStart, $periodEnd])
                    ->where('payment_status', 'pending')
                    ->groupBy('farmer_id')
                    ->havingRaw('SUM(quantity_liters) != 0');
            })
            ->orderBy('farmer_code')
            ->get();

        $selectedFarmer = $request->filled('farmer_id') ? Farmer::find($request->farmer_id) : null;
        $periodStart = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $periodEnd = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $collections = collect();
        $totalLiters = 0;
        $grossAmount = 0;
        $pendingAdvance = 0;
        $defaultAdvanceRecovered = 0;

        if ($selectedFarmer) {
            $collections = MilkCollection::where('farmer_id', $selectedFarmer->id)
                ->whereBetween('collection_date', [$periodStart, $periodEnd])
                ->where('payment_status', 'pending')
                ->get();

            $totalLiters = (float) $collections->sum('quantity_liters');
            $grossAmount = (float) $collections->sum('net_amount');
            $pendingAdvance = (float) FarmerAdvance::where('farmer_id', $selectedFarmer->id)
                ->where('status', 'pending')
                ->sum('amount');

            $deductionType = method_exists($selectedFarmer, 'getInstallmentDeductionType')
                ? $selectedFarmer->getInstallmentDeductionType()
                : ($selectedFarmer->extra_rates['installment_deduction_type'] ?? 'Fixed');
            $installmentAmount = (float) ($selectedFarmer->installment ?? 0);

            if ($deductionType === 'DeductionOff') {
                $defaultAdvanceRecovered = 0.0;
            } elseif ($deductionType === 'Fixed' && $installmentAmount > 0) {
                $defaultAdvanceRecovered = min($installmentAmount, $pendingAdvance, $grossAmount);
            } else {
                $defaultAdvanceRecovered = min($pendingAdvance, $grossAmount);
            }
        }

        return view('farmers.settlements.create', compact('farmers', 'selectedFarmer', 'periodStart', 'periodEnd', 'collections', 'totalLiters', 'grossAmount', 'pendingAdvance', 'defaultAdvanceRecovered'));
    }

    public function bulk(Request $request)
    {
        $periodStart = $request->get(
            'start_date',
            Carbon::now()->startOfMonth()->format('Y-m-d')
        );

        $periodEnd = $request->get(
            'end_date',
            Carbon::today()->format('Y-m-d')
        );

        // Active farmers jinki pending collections hain and non-zero liters (no 00 liters)
        $farmers = Farmer::where('status', 'active')
            ->whereIn('id', function ($query) use ($periodStart, $periodEnd) {
                $query->select('farmer_id')
                    ->from('milk_collections')
                    ->whereBetween('collection_date', [$periodStart, $periodEnd])
                    ->where('payment_status', 'pending')
                    ->groupBy('farmer_id')
                    ->havingRaw('SUM(quantity_liters) != 0');
            })
            ->orderBy('farmer_code')
            ->get();

        $totalCount = $farmers->count();
        $currentIndex = (int) $request->get('index', 0);
        if ($currentIndex < 0) {
            $currentIndex = 0;
        }
        if ($totalCount > 0 && $currentIndex >= $totalCount) {
            $currentIndex = $totalCount - 1;
        }

        $selectedFarmer = $totalCount > 0 ? $farmers->get($currentIndex) : null;
        $collections = collect();
        $totalLiters = 0;
        $grossAmount = 0;
        $pendingAdvance = 0;
        $defaultAdvanceRecovered = 0;

        if ($selectedFarmer) {
            $collections = MilkCollection::where('farmer_id', $selectedFarmer->id)
                ->whereBetween('collection_date', [$periodStart, $periodEnd])
                ->where('payment_status', 'pending')
                ->get();

            $totalLiters = (float) $collections->sum('quantity_liters');
            $grossAmount = (float) $collections->sum('net_amount');
            $pendingAdvance = (float) FarmerAdvance::where('farmer_id', $selectedFarmer->id)
                ->where('status', 'pending')
                ->sum('amount');

            $deductionType = method_exists($selectedFarmer, 'getInstallmentDeductionType')
                ? $selectedFarmer->getInstallmentDeductionType()
                : ($selectedFarmer->extra_rates['installment_deduction_type'] ?? 'Fixed');
            $installmentAmount = (float) ($selectedFarmer->installment ?? 0);

            if ($deductionType === 'DeductionOff') {
                $defaultAdvanceRecovered = 0.0;
            } elseif ($deductionType === 'Fixed' && $installmentAmount > 0) {
                $defaultAdvanceRecovered = min($installmentAmount, $pendingAdvance, $grossAmount);
            } else {
                $defaultAdvanceRecovered = min($pendingAdvance, $grossAmount);
            }
        }

        return view('farmers.settlements.bulk', compact(
            'farmers',
            'selectedFarmer',
            'periodStart',
            'periodEnd',
            'collections',
            'totalLiters',
            'grossAmount',
            'pendingAdvance',
            'defaultAdvanceRecovered',
            'totalCount',
            'currentIndex'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'advance_recovered' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|in:Cash,UPI,Bank Transfer',
            'payment_reference' => 'nullable|string',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);
        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$validated['period_start'], $validated['period_end']])
            ->where('payment_status', 'pending')
            ->get();

        $totalLiters = $collections->sum('quantity_liters');
        $grossAmount = $collections->sum('net_amount');
        $advance = (float) ($validated['advance_recovered'] ?? 0);
        $bonus = (float) ($validated['bonus_amount'] ?? 0);
        $deduction = (float) ($validated['deduction_amount'] ?? 0);

        $netPayable = round($grossAmount + $bonus - $deduction - $advance, 2);
        $settlementNumber = 'SETTLE-' . (FarmerSettlement::max('id') + 101);

        $settlement = FarmerSettlement::create([
            'settlement_number' => $settlementNumber,
            'farmer_id' => $farmer->id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'total_liters' => $totalLiters,
            'gross_amount' => $grossAmount,
            'bonus_amount' => $bonus,
            'deduction_amount' => $deduction,
            'advance_recovered' => $advance,
            'net_payable' => $netPayable,
            'paid_amount' => $netPayable,
            'payment_mode' => $validated['payment_mode'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'status' => 'paid',
            'settled_by' => Auth::id(),
            'settled_at' => Carbon::now(),
        ]);

        // Mark collections as settled
        MilkCollection::whereIn('id', $collections->pluck('id'))->update(['payment_status' => 'settled']);

        // Update corresponding FarmerInvoice status to settled
        FarmerInvoice::where('farmer_id', $farmer->id)
            ->whereDate('period_start', $validated['period_start'])
            ->whereDate('period_end', $validated['period_end'])
            ->update([
                'status' => 'settled',
                'payment_mode' => $validated['payment_mode'],
                'payment_reference' => $validated['payment_reference'] ?? $settlementNumber,
            ]);

        // Mark advance deducted if applicable
        if ($advance > 0) {
            FarmerAdvance::where('farmer_id', $farmer->id)
                ->where('status', 'pending')
                ->take(1)
                ->update(['status' => 'recovered', 'deducted_amount' => $advance]);
        }

        // Adjust farmer running balance
        $newBalance = max(0, $farmer->current_balance - $grossAmount);
        $farmer->update(['current_balance' => $newBalance]);

        // Ledger record
        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => Carbon::today(),
            'type' => 'debit',
            'amount' => $netPayable,
            'balance' => $newBalance,
            'reference_type' => 'settlement',
            'reference_id' => $settlement->id,
            'description' => "Period Settlement {$settlementNumber} Paid via {$validated['payment_mode']}",
        ]);

        AuditLog::log('Generated Farmer Settlement', 'FarmerSettlement', $settlement->id, ['net' => $netPayable]);

        if ($request->has('bulk_mode')) {
            $bulkIndex = (int) $request->get('bulk_index', 0);
            return redirect()->route('settlements.bulk', [
                'start_date' => $validated['period_start'],
                'end_date' => $validated['period_end'],
                'index' => $bulkIndex,
            ])->with('success', "Settlement {$settlementNumber} for {$farmer->name} (₹" . number_format($netPayable, 2) . ") disbursed successfully!");
        }

        return redirect()->route('settlements.show', $settlement)->with('success', "Settlement {$settlementNumber} for ₹" . number_format($netPayable, 2) . " processed successfully!");
    }

    public function show(FarmerSettlement $settlement)
    {
        $settlement->load(['farmer', 'settler']);
        return view('farmers.settlements.show', compact('settlement'));
    }

    /**
     * Bulk / single settle selected pending invoices
     */
    public function settleInvoices(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|integer|exists:farmer_invoices,id',
            'payment_mode' => 'required|in:Cash,UPI,Bank Transfer,Cheque',
            'payment_reference' => 'nullable|string|max:100',
            'settlement_date' => 'nullable|date',
        ]);

        $mode = $validated['payment_mode'];
        // Cash payment mode hides/clears transaction ID
        $reference = ($mode === 'Cash') ? null : ($validated['payment_reference'] ?? null);
        $settleDate = !empty($validated['settlement_date']) ? Carbon::parse($validated['settlement_date']) : Carbon::now();

        $invoices = FarmerInvoice::with('farmer')
            ->whereIn('id', $validated['invoice_ids'])
            ->where('status', 'generated')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'चयनित इनवॉइस पहले ही सेटल हो चुकी हैं या उपलब्ध नहीं हैं।');
        }

        $settledCount = 0;
        $totalPaid = 0.0;

        DB::transaction(function () use ($invoices, $mode, $reference, $settleDate, &$settledCount, &$totalPaid) {
            $maxId = FarmerSettlement::max('id') ?? 100;

            foreach ($invoices as $inv) {
                $maxId++;
                $settleNumber = 'SETTLE-' . $maxId;

                $settlement = FarmerSettlement::create([
                    'settlement_number' => $settleNumber,
                    'farmer_id' => $inv->farmer_id,
                    'period_start' => $inv->period_start,
                    'period_end' => $inv->period_end,
                    'total_liters' => $inv->total_quantity,
                    'gross_amount' => $inv->total_amount,
                    'bonus_amount' => $inv->total_credit,
                    'deduction_amount' => max(0, round((float) $inv->total_deduction - (float) $inv->advance_deduction, 2)),
                    'advance_recovered' => (float) $inv->advance_deduction,
                    'net_payable' => $inv->net_payment,
                    'paid_amount' => $inv->net_payment,
                    'payment_mode' => $mode,
                    'payment_reference' => $reference,
                    'status' => 'paid',
                    'settled_by' => Auth::id(),
                    'settled_at' => $settleDate,
                ]);

                // Update invoice status to settled
                $inv->update([
                    'status' => 'settled',
                    'payment_mode' => $mode,
                    'payment_reference' => $reference ?: $settleNumber,
                ]);

                // Mark collections settled
                MilkCollection::where('farmer_id', $inv->farmer_id)
                    ->whereBetween('collection_date', [$inv->period_start, $inv->period_end])
                    ->where('payment_status', 'pending')
                    ->update(['payment_status' => 'settled']);

                // Adjust running balance & create ledger debit record
                $farmer = $inv->farmer;
                $currentBal = $farmer ? (float) $farmer->current_balance : 0;
                $newBal = max(0, $currentBal - $inv->net_payment);
                if ($farmer) {
                    $farmer->update(['current_balance' => $newBal]);
                }

                FarmerLedger::create([
                    'farmer_id' => $inv->farmer_id,
                    'transaction_date' => $settleDate->toDateString(),
                    'type' => 'debit',
                    'amount' => $inv->net_payment,
                    'balance' => $newBal,
                    'reference_type' => 'settlement',
                    'reference_id' => $settlement->id,
                    'description' => "Invoice #{$inv->invoice_number} Paid via {$mode}" . ($reference ? " (Ref: {$reference})" : ""),
                ]);

                AuditLog::log('Settled Farmer Invoice', 'FarmerSettlement', $settlement->id, [
                    'invoice_number' => $inv->invoice_number,
                    'amount' => $inv->net_payment,
                    'payment_mode' => $mode,
                ]);

                $settledCount++;
                $totalPaid += (float) $inv->net_payment;
            }
        });

        return redirect()->route('settlements.index', ['tab' => 'settled'])
            ->with('success', "{$settledCount} किसान(ओं) का कुल भुगतान ₹" . number_format($totalPaid, 2) . " सफलतापूर्वक सेटल कर दिया गया है।");
    }

    /**
     * Delete settlement and revert the linked FarmerInvoice back to 'generated'
     */
    public function destroy(FarmerSettlement $settlement)
    {
        $settleNumber = $settlement->settlement_number;
        $farmerId = $settlement->farmer_id;
        $paidAmount = (float) $settlement->paid_amount;

        DB::transaction(function () use ($settlement, $farmerId, $paidAmount) {
            // Find linked invoice if exists
            $invoice = FarmerInvoice::where('farmer_id', $farmerId)
                ->whereDate('period_start', $settlement->period_start)
                ->whereDate('period_end', $settlement->period_end)
                ->where('status', 'settled')
                ->first();

            if ($invoice) {
                // Revert invoice back to 'generated'
                $invoice->update([
                    'status' => 'generated',
                    'payment_mode' => null,
                    'payment_reference' => null,
                ]);
            }

            // Revert milk collections in this period back to pending
            MilkCollection::where('farmer_id', $farmerId)
                ->whereBetween('collection_date', [$settlement->period_start, $settlement->period_end])
                ->where('payment_status', 'settled')
                ->update(['payment_status' => 'pending']);

            // Revert ledger & farmer balance
            $farmer = Farmer::find($farmerId);
            if ($farmer) {
                $farmer->increment('current_balance', $paidAmount);
            }

            // Remove ledger debit entry for this settlement
            FarmerLedger::where('reference_type', 'settlement')
                ->where('reference_id', $settlement->id)
                ->delete();

            // Delete the settlement record
            $settlement->delete();

            AuditLog::log('Deleted Farmer Settlement', 'FarmerSettlement', $settlement->id, [
                'settlement_number' => $settleNumber,
                'reverted_invoice' => $invoice ? $invoice->invoice_number : null,
            ]);
        });

        return back()->with('success', "सेटलमेंट ({$settleNumber}) सफलतापूर्वक हटा दिया गया है। संबंधित इनवॉइस वापस Unsettled (Generated) हो गई है जिसे अब आप आवश्यकतानुसार एडिट या डिलीट कर सकते हैं।");
    }
}