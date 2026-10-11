<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Farmer;
use App\Models\FarmerInvoice;
use App\Models\MilkCollection;
use App\Models\FarmerDeduction;
use App\Models\FarmerAdvance;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerInvoiceController extends Controller
{
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Resiliently ensure farmer_invoices table exists on the database
     * and clean up any duplicate constructor-seeded sample advances.
     */
    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('farmer_invoices')) {
                Schema::create('farmer_invoices', function (Blueprint $table) {
                    $table->id();
                    $table->string('invoice_number')->unique();
                    $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                    $table->date('period_start');
                    $table->date('period_end');
                    $table->decimal('total_quantity', 10, 2)->default(0.00);
                    $table->decimal('milk_amount', 10, 2)->default(0.00);
                    $table->decimal('stationary_deduction', 10, 2)->default(0.00);
                    $table->decimal('feed_deduction', 10, 2)->default(0.00);
                    $table->decimal('advance_deduction', 10, 2)->default(0.00);
                    $table->decimal('other_deduction', 10, 2)->default(0.00);
                    $table->decimal('total_deduction', 10, 2)->default(0.00);
                    $table->decimal('total_credit', 10, 2)->default(0.00);
                    $table->decimal('total_amount', 10, 2)->default(0.00);
                    $table->decimal('previous_balance', 10, 2)->default(0.00);
                    $table->decimal('net_payment', 10, 2)->default(0.00);
                    $table->string('status')->default('generated');
                    $table->string('payment_mode')->nullable()->default('Bank Transfer');
                    $table->string('payment_reference')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('farmer_advances')) {
                $seededPurposes = [
                    '-PAID BY DR',
                    'Emergency Advance',
                    'Cattle Feed loan',
                    'Seasonal Festival Advance',
                    'Full Cleared Loan',
                ];
                \DB::table('farmer_advances')
                    ->whereIn('voucher_no', ['001', '002', '003', '004', '005'])
                    ->whereIn('purpose', $seededPurposes)
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Calculate accurate period billing figures for a farmer.
     * Ensures:
     *   Total Deduction (A) = Stationary + Feed + In Hand Advance
     *   Total Amount (B+C)  = Total Credit (B) + Milk Amount (C)
     *   Balance             = max(0, A - (B + C))  (remaining unpaid deduction balance)
     *   Payment (B+C-A)     = max(0, (B + C) - A)  (net payable amount to farmer)
     */
    private function calculateInvoiceMetrics(int $farmerId, string $startDate, string $endDate): array
    {
        $collections = MilkCollection::where('farmer_id', $farmerId)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->get();

        $totalQuantity = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum(function ($c) {
            $net = (float) $c->net_amount;
            if ($net > 0) {
                return $net;
            }
            $rate = (float) ($c->applied_rate ?: $c->calculated_rate ?: 0);
            return round((float) $c->quantity_liters * $rate, 2);
        }), 2);

        $feedDeduction = 0.00;
        $stationaryDeduction = 0.00;
        $advanceFromDeductions = 0.00;
        $totalCredit = 0.00;

        if (Schema::hasTable('farmer_deductions')) {
            $deductions = FarmerDeduction::where('farmer_id', $farmerId)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->get();

            $given = $deductions->where('transaction_type', 'given');
            $feedDeduction = round((float) $given->where('deduction_type', 'cattle_feed')->sum('amount'), 2);
            $advanceFromDeductions = round((float) $given->where('deduction_type', 'advance_recovery')->sum('amount'), 2);
            $stationaryDeduction = round((float) $given->whereNotIn('deduction_type', ['cattle_feed', 'advance_recovery'])->sum('amount'), 2);
            $totalCredit = round((float) $deductions->where('transaction_type', 'received')->sum('amount'), 2);
        }

        $advanceFromLoans = 0.00;
        if (Schema::hasTable('farmer_advances')) {
            $advances = FarmerAdvance::where('farmer_id', $farmerId)
                ->whereBetween('advance_date', [$startDate, $endDate])
                ->where('status', '!=', 'recovered')
                ->get();

            $advanceFromLoans = round((float) $advances->sum(function ($adv) {
                return (float) $adv->principal_balance;
            }), 2);
        }

        $advanceDeduction = round($advanceFromLoans + $advanceFromDeductions, 2);
        $otherDeduction = 0.00;

        // A = Stationary + Feed + In Hand Advance
        $totalDeduction = round($stationaryDeduction + $feedDeduction + $advanceDeduction, 2);

        // B + C = Total Credit (B) + Milk Amount (C)
        $totalAmount = round($totalCredit + $milkAmount, 2);

        // Balance = Remaining unpaid deduction if A > (B + C), else 0.00
        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);

        // Payment (B+C-A) = Net payable amount if (B + C) >= A, else 0.00
        $netPayment = round(max(0, $totalAmount - $totalDeduction), 2);

        return [
            'total_quantity' => $totalQuantity,
            'milk_amount' => $milkAmount,
            'stationary_deduction' => $stationaryDeduction,
            'feed_deduction' => $feedDeduction,
            'advance_deduction' => $advanceDeduction,
            'other_deduction' => $otherDeduction,
            'total_deduction' => $totalDeduction,
            'total_credit' => $totalCredit,
            'total_amount' => $totalAmount,
            'previous_balance' => $previousBalance,
            'net_payment' => $netPayment,
        ];
    }

    /**
     * Synchronize a FarmerInvoice record with live collections, deductions, and advances.
     */
    private function syncInvoice(FarmerInvoice $invoice): FarmerInvoice
    {
        $start = $invoice->period_start instanceof Carbon
            ? $invoice->period_start->format('Y-m-d')
            : Carbon::parse($invoice->period_start)->format('Y-m-d');
        $end = $invoice->period_end instanceof Carbon
            ? $invoice->period_end->format('Y-m-d')
            : Carbon::parse($invoice->period_end)->format('Y-m-d');

        $metrics = $this->calculateInvoiceMetrics((int) $invoice->farmer_id, $start, $end);

        $needsUpdate = false;
        foreach ($metrics as $key => $val) {
            if (round((float) $invoice->{$key}, 2) !== round((float) $val, 2)) {
                $needsUpdate = true;
                break;
            }
        }

        if ($needsUpdate) {
            $invoice->update($metrics);
        }

        return $invoice;
    }

    /**
     * Display Farmer Invoice Listing matching Screenshot 1 (media_1791388705563.png)
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $farmerSearch = $request->get('farmer_search');
        $invoiceSearch = $request->get('invoice_search');
        $perPage = (int) $request->get('per_page', 50);

        $query = FarmerInvoice::with('farmer');

        if ($startDate && $endDate) {
            $query->whereDate('period_start', '>=', $startDate)
                  ->whereDate('period_end', '<=', $endDate);
        }

        if (!empty($farmerSearch)) {
            $query->whereHas('farmer', function ($q) use ($farmerSearch) {
                $q->where('name', 'like', "%{$farmerSearch}%")
                  ->orWhere('farmer_code', 'like', "%{$farmerSearch}%")
                  ->orWhere('phone', 'like', "%{$farmerSearch}%");
            });
        }

        if (!empty($invoiceSearch)) {
            $query->where('invoice_number', 'like', "%{$invoiceSearch}%");
        }

        // Deduplicate overlapping 'generated' invoices for the same farmer & period_start (keep latest)
        $matchingInvoices = (clone $query)->orderBy('id', 'desc')->get();
        $seenFarmerPeriod = [];
        foreach ($matchingInvoices as $inv) {
            $key = $inv->farmer_id . '_' . ($inv->period_start instanceof Carbon ? $inv->period_start->format('Y-m-d') : $inv->period_start);
            if ($inv->status === 'generated' && isset($seenFarmerPeriod[$key])) {
                $inv->delete();
                continue;
            }
            $seenFarmerPeriod[$key] = true;
            $this->syncInvoice($inv);
        }

        $invoices = $query->latest('id')->paginate($perPage)->withQueryString();

        // Calculate System Totals for Table Summary Row
        $summaryQuery = clone $query;
        $totalQuantity = (float) $summaryQuery->sum('total_quantity');
        $totalStationary = (float) $summaryQuery->sum('stationary_deduction');
        $totalFeed = (float) $summaryQuery->sum('feed_deduction');
        $totalAdvance = (float) $summaryQuery->sum('advance_deduction');
        $totalDeduction = (float) $summaryQuery->sum('total_deduction');
        $totalCredit = (float) $summaryQuery->sum('total_credit');
        $totalMilkAmount = (float) $summaryQuery->sum('milk_amount');
        $totalAmount = (float) $summaryQuery->sum('total_amount');
        $totalBalance = (float) $summaryQuery->sum('previous_balance');
        $totalPayment = (float) $summaryQuery->sum('net_payment');

        // All active farmers for generate filter
        $farmers = Farmer::where('status', 'active')->orderBy('name')->get();

        return view('farmers.invoices.index', compact(
            'invoices',
            'farmers',
            'startDate',
            'endDate',
            'farmerSearch',
            'invoiceSearch',
            'perPage',
            'totalQuantity',
            'totalStationary',
            'totalFeed',
            'totalAdvance',
            'totalDeduction',
            'totalCredit',
            'totalMilkAmount',
            'totalAmount',
            'totalBalance',
            'totalPayment'
        ));
    }

    /**
     * Generate Invoices for period (matching "Generate Invoice" button in Screenshot 1)
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'farmer_id' => 'nullable|exists:farmers,id',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $farmersQuery = Farmer::where('status', 'active');
        if (!empty($validated['farmer_id'])) {
            $farmersQuery->where('id', $validated['farmer_id']);
        }

        $farmers = $farmersQuery->get();
        $generatedCount = 0;

        foreach ($farmers as $farmer) {
            $metrics = $this->calculateInvoiceMetrics((int) $farmer->id, $startDate, $endDate);

            // Skip farmers with no activity at all
            if ($metrics['total_quantity'] == 0 && $metrics['milk_amount'] == 0 && $metrics['total_deduction'] == 0 && $metrics['total_credit'] == 0) {
                continue;
            }

            // Find existing generated invoice for same farmer & period_start to avoid duplicate overlapping bills
            $existing = FarmerInvoice::where('farmer_id', $farmer->id)
                ->whereDate('period_start', $startDate)
                ->orderBy('id', 'desc')
                ->first();

            if ($existing) {
                $existing->update(array_merge($metrics, [
                    'period_end' => $endDate,
                    'status' => 'generated',
                    'created_by' => Auth::id(),
                ]));
                // Remove any older duplicate bills for same farmer & period_start
                FarmerInvoice::where('farmer_id', $farmer->id)
                    ->whereDate('period_start', $startDate)
                    ->where('id', '!=', $existing->id)
                    ->where('status', 'generated')
                    ->delete();
            } else {
                $invNum = 'BILL-' . str_pad((FarmerInvoice::max('id') + 101), 4, '0', STR_PAD_LEFT);
                FarmerInvoice::create(array_merge($metrics, [
                    'farmer_id' => $farmer->id,
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'invoice_number' => $invNum,
                    'status' => 'generated',
                    'created_by' => Auth::id(),
                ]));
            }

            $generatedCount++;
        }

        AuditLog::log("Generated {$generatedCount} Farmer Invoices", 'FarmerInvoice', null, [
            'period' => "{$startDate} to {$endDate}",
            'count' => $generatedCount
        ]);

        return redirect()->route('farmer-invoices.index', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])->with('success', "Successfully generated {$generatedCount} farmer invoices for period {$startDate} to {$endDate}!");
    }

    /**
     * Display Invoice Details full page matching Screenshot 4 (media_1791388765764.png)
     */
    public function show(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load(['farmer', 'creator']);
        $farmer = $invoice->farmer;

        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;
        $cattleFeeds = $invoice->cattle_feeds_list;

        return view('farmers.invoices.show', compact(
            'invoice',
            'farmer',
            'collections',
            'deductions',
            'cattleFeeds'
        ));
    }

    /**
     * Return JSON data for Invoice Preview Modal matching Screenshot 2 & 3
     */
    public function previewData(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load('farmer');
        $farmer = $invoice->farmer;

        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'period_start' => $invoice->period_start->format('Y-m-d'),
            'period_end' => $invoice->period_end->format('Y-m-d'),
            'farmer' => [
                'id' => $farmer->id,
                'name' => $farmer->name,
                'name_hi' => $farmer->name_hi ?? '',
                'code' => $farmer->farmer_code,
                'phone' => $farmer->phone ?? '',
                'bank_name' => $farmer->bank_name ?? 'N/A',
                'account_number' => $farmer->account_number ?? 'N/A',
                'ifsc_code' => $farmer->ifsc_code ?? 'N/A',
            ],
            'collections' => $collections,
            'deductions' => $deductions,
            'totals' => [
                'total_quantity' => (float) $invoice->total_quantity,
                'milk_amount' => (float) $invoice->milk_amount,
                'total_credit' => (float) $invoice->total_credit,
                'total_deduction' => (float) $invoice->total_deduction,
                'previous_balance' => (float) $invoice->previous_balance,
                'net_payment' => (float) $invoice->net_payment,
            ]
        ]);
    }

    /**
     * Printable view of single invoice matching Screenshot 2 & 3
     */
    public function print(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load('farmer');
        $farmer = $invoice->farmer;
        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;

        return view('farmers.invoices.print', compact('invoice', 'farmer', 'collections', 'deductions'));
    }

    /**
     * Bank Payment Print View matching "Bank Payment Print" button in Screenshot 1
     */
    public function bankPaymentPrint(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $query = FarmerInvoice::with('farmer')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate);

        if ($request->filled('selected_ids')) {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        }

        $invoices = $query->get()->map(function ($inv) {
            return $this->syncInvoice($inv);
        });
        $totalPayment = $invoices->sum('net_payment');

        return view('farmers.invoices.bank_payment_print', compact('invoices', 'startDate', 'endDate', 'totalPayment'));
    }

    /**
     * Export Excel / CSV matching "Download Excel" button in Screenshot 1
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $invoices = FarmerInvoice::with('farmer')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->get()
            ->map(function ($inv) {
                return $this->syncInvoice($inv);
            });

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"farmer_invoices_{$startDate}_to_{$endDate}.csv\"",
        ];

        $columns = [
            'Farmer Code',
            'Farmer Name',
            'Milk Quantity (L)',
            'Stationary',
            'Feed',
            'In Hand Advance',
            'Total Deduction (A)',
            'Total Credit (B)',
            'Milk Amount (C)',
            'Total Amount (B+C)',
            'Balance',
            'Payment (B+C-A)',
        ];

        $callback = function () use ($columns, $invoices) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);

            foreach ($invoices as $inv) {
                fputcsv($output, [
                    $inv->farmer->farmer_code ?? '-',
                    $inv->farmer->name ?? '-',
                    number_format($inv->total_quantity, 2, '.', ''),
                    number_format($inv->stationary_deduction, 2, '.', ''),
                    number_format($inv->feed_deduction, 2, '.', ''),
                    number_format($inv->advance_deduction, 2, '.', ''),
                    number_format($inv->total_deduction, 2, '.', ''),
                    number_format($inv->total_credit, 2, '.', ''),
                    number_format($inv->milk_amount, 2, '.', ''),
                    number_format($inv->total_amount, 2, '.', ''),
                    number_format($inv->previous_balance, 2, '.', ''),
                    number_format($inv->net_payment, 2, '.', ''),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete an invoice
     */
    public function destroy(FarmerInvoice $invoice)
    {
        $invNum = $invoice->invoice_number;
        $invoice->delete();

        AuditLog::log('Deleted Farmer Invoice', 'FarmerInvoice', $invoice->id, ['number' => $invNum]);

        return redirect()->route('farmer-invoices.index')->with('success', "Invoice #{$invNum} deleted successfully.");
    }

    /**
     * Bulk Delete Invoices
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:farmer_invoices,id',
        ]);

        $count = FarmerInvoice::whereIn('id', $validated['invoice_ids'])->delete();

        AuditLog::log("Bulk Deleted {$count} Farmer Invoices", 'FarmerInvoice', null);

        return redirect()->route('farmer-invoices.index')->with('success', "Successfully deleted {$count} selected invoices.");
    }
}
