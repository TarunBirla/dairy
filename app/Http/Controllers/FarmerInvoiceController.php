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
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
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
            // Find milk collections for this period
            $collections = MilkCollection::where('farmer_id', $farmer->id)
                ->whereBetween('collection_date', [$startDate, $endDate])
                ->get();

            $totalQuantity = (float) $collections->sum('quantity_liters');
            $milkAmount = (float) $collections->sum('net_amount');

            // Find deductions for this period
            $feedDeduction = 0.00;
            $stationaryDeduction = 0.00;
            $otherDeduction = 0.00;
            $totalCredit = 0.00;

            if (Schema::hasTable('farmer_deductions')) {
                $deductions = FarmerDeduction::where('farmer_id', $farmer->id)
                    ->whereBetween('entry_date', [$startDate, $endDate])
                    ->get();

                $feedDeduction = (float) $deductions->where('deduction_type', 'cattle_feed')->where('transaction_type', 'given')->sum('amount');
                $stationaryDeduction = (float) $deductions->where('deduction_type', 'stationary')->where('transaction_type', 'given')->sum('amount');
                $otherDeduction = (float) $deductions->whereNotIn('deduction_type', ['cattle_feed', 'stationary'])->where('transaction_type', 'given')->sum('amount');
                $totalCredit = (float) $deductions->where('transaction_type', 'received')->sum('amount');
            }

            // Find in-hand advances issued
            $advanceDeduction = (float) FarmerAdvance::where('farmer_id', $farmer->id)
                ->whereBetween('advance_date', [$startDate, $endDate])
                ->sum('amount');

            $totalDeduction = $stationaryDeduction + $feedDeduction + $advanceDeduction + $otherDeduction;
            $totalAmount = $milkAmount + $totalCredit;
            $previousBalance = (float) $farmer->current_balance;
            $netPayment = max(0, $totalAmount - $totalDeduction);

            // Skip farmers with no activity at all
            if ($totalQuantity == 0 && $milkAmount == 0 && $totalDeduction == 0) {
                continue;
            }

            // Generate unique sequential invoice number
            $invNum = 'BILL-' . str_pad((FarmerInvoice::max('id') + 101), 4, '0', STR_PAD_LEFT);

            // Update or Create
            FarmerInvoice::updateOrCreate(
                [
                    'farmer_id' => $farmer->id,
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                ],
                [
                    'invoice_number' => $invNum,
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
                    'status' => 'generated',
                    'created_by' => Auth::id(),
                ]
            );

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

        $invoices = $query->get();
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
            ->get();

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
