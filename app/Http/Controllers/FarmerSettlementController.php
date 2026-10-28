<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Farmer;
use App\Models\FarmerSettlement;
use App\Models\FarmerLedger;
use App\Models\FarmerAdvance;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerSettlementController extends Controller
{
    public function index()
    {
        $settlements = FarmerSettlement::with('farmer')
            ->where('total_liters', '!=', 0)
            ->latest()->paginate(15);
        $totalSettledAmount = FarmerSettlement::where('status', 'paid')->sum('paid_amount');
        $farmers = Farmer::where('status', 'active')->get();

        return view('farmers.settlements.index', compact('settlements', 'totalSettledAmount', 'farmers'));
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
}