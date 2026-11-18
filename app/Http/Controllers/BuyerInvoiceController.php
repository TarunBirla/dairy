<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Buyer;
use App\Models\BuyerInvoice;
use App\Models\MilkSale;
use App\Models\BuyerPayment;
use App\Models\AuditLog;
use Carbon\Carbon;

class BuyerInvoiceController extends Controller
{
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Resiliently ensure buyer_invoices table exists on the database
     */
    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('buyer_invoices')) {
                Schema::create('buyer_invoices', function (Blueprint $table) {
                    $table->id();
                    $table->string('invoice_number')->unique();
                    $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                    $table->date('period_start');
                    $table->date('period_end');
                    $table->decimal('total_quantity', 10, 2)->default(0.00);
                    $table->decimal('milk_amount', 12, 2)->default(0.00);
                    $table->decimal('cash_received', 12, 2)->default(0.00);
                    $table->decimal('discount_amount', 10, 2)->default(0.00);
                    $table->decimal('other_charges', 10, 2)->default(0.00);
                    $table->decimal('previous_balance', 12, 2)->default(0.00);
                    $table->decimal('net_receivable', 12, 2)->default(0.00);
                    $table->longText('sales_breakdown')->nullable();
                    $table->string('status')->default('pending');
                    $table->boolean('is_edited')->default(false);
                    $table->string('payment_mode')->nullable()->default('Cash');
                    $table->string('payment_reference')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('milk_sales') && !Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
                Schema::table('milk_sales', function (Blueprint $table) {
                    $table->foreignId('buyer_invoice_id')->nullable()->after('status')->constrained('buyer_invoices')->nullOnDelete();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Calculate period billing metrics for a buyer accurately without double-counting.
     */
    public function calculateInvoiceMetrics(int $buyerId, string $startDate, string $endDate): array
    {
        $buyer = Buyer::find($buyerId);

        $sales = MilkSale::where('buyer_id', $buyerId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->orderBy('sale_date', 'asc')
            ->get();

        $totalQuantity = round((float) $sales->sum('quantity_liters'), 2);
        $milkAmount = round((float) $sales->sum('total_amount'), 2);

        // All payments received in this period
        // BuyerPayment contains all payments (direct DIR- payments from sales + separate Khata payments)
        $cashReceived = round((float) BuyerPayment::where('buyer_id', $buyerId)
            ->where('type', 'received')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount'), 2);

        // Fallback: If no DIR- records exist in BuyerPayment, add direct paid_amount from sales
        $hasDirReceipts = BuyerPayment::where('buyer_id', $buyerId)
            ->where('type', 'received')
            ->where('receipt_number', 'like', 'DIR-%')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->exists();

        if (!$hasDirReceipts) {
            $directSalesPaid = round((float) $sales->sum('paid_amount'), 2);
            $cashReceived = round($cashReceived + $directSalesPaid, 2);
        }

        // Opening Balance (due before startDate)
        $salesBefore = (float) MilkSale::where('buyer_id', $buyerId)
            ->where('sale_date', '<', $startDate)
            ->sum('total_amount');

        $receivedBefore = (float) BuyerPayment::where('buyer_id', $buyerId)
            ->where('type', 'received')
            ->where('payment_date', '<', $startDate)
            ->sum('amount');

        $hasPriorDirReceipts = BuyerPayment::where('buyer_id', $buyerId)
            ->where('type', 'received')
            ->where('receipt_number', 'like', 'DIR-%')
            ->where('payment_date', '<', $startDate)
            ->exists();

        if (!$hasPriorDirReceipts) {
            $directSalesPaidBefore = (float) MilkSale::where('buyer_id', $buyerId)
                ->where('sale_date', '<', $startDate)
                ->sum('paid_amount');
            $receivedBefore += $directSalesPaidBefore;
        }

        $refundsBefore = (float) BuyerPayment::where('buyer_id', $buyerId)
            ->where('type', 'paid')
            ->where('payment_date', '<', $startDate)
            ->sum('amount');

        $previousBalance = round(max(0, $salesBefore - $receivedBefore + $refundsBefore), 2);

        $discountAmount = 0.00;
        $otherCharges = 0.00;
        $netReceivable = round(max(0, $previousBalance + $milkAmount + $otherCharges - $cashReceived - $discountAmount), 2);

        // Detailed sales breakdown
        $breakdown = $sales->map(function ($s) {
            return [
                'id' => $s->id,
                'date' => $s->sale_date->format('Y-m-d'),
                'date_display' => $s->sale_date->format('d M Y'),
                'shift' => ucfirst($s->shift),
                'milk_type' => ucfirst($s->milk_type),
                'quantity_liters' => (float) $s->quantity_liters,
                'fat' => (float) ($s->fat_percentage ?? 0),
                'snf' => (float) ($s->snf_percentage ?? 0),
                'clr' => (float) ($s->clr_reading ?? 0),
                'rate' => (float) $s->rate_per_liter,
                'amount' => (float) $s->total_amount,
                'paid_amount' => (float) ($s->paid_amount ?? 0),
            ];
        })->toArray();

        return [
            'total_quantity' => $totalQuantity,
            'milk_amount' => $milkAmount,
            'cash_received' => $cashReceived,
            'discount_amount' => $discountAmount,
            'other_charges' => $otherCharges,
            'previous_balance' => $previousBalance,
            'net_receivable' => $netReceivable,
            'sales_breakdown' => $breakdown,
        ];
    }

    /**
     * Display Buyer Invoices with Pending & Generated Tabs (matching Farmer Invoices workflow)
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Preset 10-day cycles matching dairy workflow
        $cycles = [];
        for ($m = 0; $m < 4; $m++) {
            $mo = Carbon::today()->subMonths($m);
            $c3_start = $mo->copy()->day(21)->format('d M Y');
            $c3_end = $mo->copy()->endOfMonth()->format('d M Y');
            $c2_start = $mo->copy()->day(11)->format('d M Y');
            $c2_end = $mo->copy()->day(min(20, $mo->daysInMonth))->format('d M Y');
            $c1_start = $mo->copy()->startOfMonth()->format('d M Y');
            $c1_end = $mo->copy()->day(min(10, $mo->daysInMonth))->format('d M Y');

            $cycles["{$c3_start} - {$c3_end}"] = "{$c3_start} - {$c3_end}";
            $cycles["{$c2_start} - {$c2_end}"] = "{$c2_start} - {$c2_end}";
            $cycles["{$c1_start} - {$c1_end}"] = "{$c1_start} - {$c1_end}";
        }

        $cycleRange = $request->get('cycle_range');
        if ($cycleRange) {
            $parts = explode(' - ', $cycleRange);
            if (count($parts) === 2) {
                try {
                    $startDate = Carbon::parse($parts[0])->format('Y-m-d');
                    $endDate = Carbon::parse($parts[1])->format('Y-m-d');
                } catch (\Throwable $e) {}
            }
        }

        if (empty($startDate) || empty($endDate)) {
            $day = $today->day;
            if ($day <= 10) {
                $startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $today->copy()->day(min(10, $today->daysInMonth))->format('Y-m-d');
            } elseif ($day <= 20) {
                $startDate = $today->copy()->day(11)->format('Y-m-d');
                $endDate = $today->copy()->day(min(20, $today->daysInMonth))->format('Y-m-d');
            } else {
                $startDate = $today->copy()->day(21)->format('Y-m-d');
                $endDate = $today->copy()->endOfMonth()->format('Y-m-d');
            }
        }

        $activeCycleKey = Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y');
        if (!isset($cycles[$activeCycleKey])) {
            $cycles[$activeCycleKey] = $activeCycleKey . " (Custom)";
        }

        $buyerSearch = $request->get('buyer_search');
        $invoiceSearch = $request->get('invoice_search');
        $perPage = (int) $request->get('per_page', 50);

        $baseQuery = BuyerInvoice::whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate);

        if (!empty($buyerSearch)) {
            $baseQuery->whereHas('buyer', function ($q) use ($buyerSearch) {
                $q->where('name', 'like', "%{$buyerSearch}%")
                  ->orWhere('buyer_code', 'like', "%{$buyerSearch}%")
                  ->orWhere('phone', 'like', "%{$buyerSearch}%");
            });
        }

        if (!empty($invoiceSearch)) {
            $baseQuery->where('invoice_number', 'like', "%{$invoiceSearch}%");
        }

        // Clean up empty unedited pending draft invoices that have 0 milk sales
        BuyerInvoice::where('status', 'pending')
            ->where('is_edited', false)
            ->where('total_quantity', '<=', 0)
            ->delete();

        // Sync pending invoices with latest metrics
        $pendingDrafts = (clone $baseQuery)->where('status', 'pending')->where('is_edited', false)->get();
        foreach ($pendingDrafts as $draft) {
            $metrics = $this->calculateInvoiceMetrics((int) $draft->buyer_id, $startDate, $endDate);
            $draft->update($metrics);
        }

        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $generatedCount = (clone $baseQuery)->where('status', '!=', 'pending')->count();

        if ($request->filled('status_tab')) {
            $statusTab = $request->get('status_tab') === 'generated' ? 'generated' : 'pending';
        } else {
            $statusTab = $pendingCount > 0 ? 'pending' : 'generated';
        }

        $query = clone $baseQuery;
        if ($statusTab === 'pending') {
            $query->where('status', 'pending');
        } else {
            $query->where('status', '!=', 'pending');
        }

        $allTabIds = (clone $query)->orderBy('id', 'asc')->pluck('id')->toArray();
        $invoices = $query->with('buyer')->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        // System Totals for Table Summary Row
        $summaryQuery = clone $query;
        $totalQuantity = (float) $summaryQuery->sum('total_quantity');
        $totalMilkAmount = (float) $summaryQuery->sum('milk_amount');
        $totalCashReceived = (float) $summaryQuery->sum('cash_received');
        $totalDiscount = (float) $summaryQuery->sum('discount_amount');
        $totalPreviousBalance = (float) $summaryQuery->sum('previous_balance');
        $totalNetReceivable = (float) $summaryQuery->sum('net_receivable');

        $buyers = Buyer::where('status', 'active')->orderBy('name')->get();

        return view('buyers.invoices.index', compact(
            'invoices',
            'buyers',
            'startDate',
            'endDate',
            'buyerSearch',
            'invoiceSearch',
            'perPage',
            'statusTab',
            'pendingCount',
            'generatedCount',
            'allTabIds',
            'cycles',
            'activeCycleKey',
            'totalQuantity',
            'totalMilkAmount',
            'totalCashReceived',
            'totalDiscount',
            'totalPreviousBalance',
            'totalNetReceivable'
        ));
    }

    /**
     * Generate Projected Invoices for Buyers in Pending State for Review
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'buyer_id' => 'nullable|exists:buyers,id',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $buyerId = $validated['buyer_id'] ?? null;

        $buyersQuery = Buyer::where('status', 'active');
        if (!empty($buyerId)) {
            $buyersQuery->where('id', $buyerId);
        }
        $buyers = $buyersQuery->get();

        $generatedCount = 0;

        foreach ($buyers as $buyer) {
            // Check if finalized/generated invoice already exists
            $existingFinalized = BuyerInvoice::where('buyer_id', $buyer->id)
                ->where('status', '!=', 'pending')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('period_start', [$startDate, $endDate])
                      ->orWhereBetween('period_end', [$startDate, $endDate]);
                })
                ->exists();

            if ($existingFinalized) {
                continue;
            }

            // Calculate metrics
            $metrics = $this->calculateInvoiceMetrics($buyer->id, $startDate, $endDate);

            // Skip buyers with no milk sales in this period
            if ($metrics['total_quantity'] <= 0 && $metrics['milk_amount'] <= 0) {
                continue;
            }

            // Reuse existing pending draft or create new
            $targetInvoice = BuyerInvoice::where('buyer_id', $buyer->id)
                ->where('status', 'pending')
                ->where('period_start', $startDate)
                ->where('period_end', $endDate)
                ->first();

            if ($targetInvoice) {
                $targetInvoice->update($metrics);
            } else {
                $invNum = 'BINV-' . str_pad((BuyerInvoice::max('id') + 101), 4, '0', STR_PAD_LEFT);
                $targetInvoice = BuyerInvoice::create(array_merge($metrics, [
                    'buyer_id' => $buyer->id,
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'invoice_number' => $invNum,
                    'status' => 'pending',
                    'is_edited' => false,
                    'created_by' => Auth::id(),
                ]));
            }

            // Link matching milk sales
            if (Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
                MilkSale::where('buyer_id', $buyer->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->where(function ($q) use ($targetInvoice) {
                        $q->whereNull('buyer_invoice_id')
                          ->orWhere('buyer_invoice_id', $targetInvoice->id);
                    })
                    ->update([
                        'buyer_invoice_id' => $targetInvoice->id,
                    ]);
            }

            $generatedCount++;
        }

        AuditLog::log("Generated {$generatedCount} Pending Buyer Invoices", 'BuyerInvoice', null, [
            'period' => "{$startDate} to {$endDate}",
            'count' => $generatedCount
        ]);

        return redirect()->route('buyer-invoices.index', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status_tab' => 'pending',
        ])->with('success', "{$generatedCount} Pending Buyer Invoices ({$startDate} to {$endDate}) created for review & verification!");
    }

    /**
     * Return JSON data for Invoice Preview & 1-by-1 Review Modal
     */
    public function previewData(BuyerInvoice $invoice)
    {
        $invoice->load(['buyer', 'creator']);
        $buyer = $invoice->buyer;

        // Sales breakdown
        $sales = MilkSale::where('buyer_id', $invoice->buyer_id)
            ->whereBetween('sale_date', [$invoice->period_start, $invoice->period_end])
            ->orderBy('sale_date', 'asc')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'sale_date' => $s->sale_date->format('Y-m-d'),
                    'date_display' => $s->sale_date->format('d M Y'),
                    'shift' => ucfirst($s->shift),
                    'milk_type' => ucfirst($s->milk_type),
                    'quantity_liters' => (float) $s->quantity_liters,
                    'fat' => (float) ($s->fat_percentage ?? 0),
                    'snf' => (float) ($s->snf_percentage ?? 0),
                    'clr' => (float) ($s->clr_reading ?? 0),
                    'rate' => (float) $s->rate_per_liter,
                    'total_amount' => (float) $s->total_amount,
                    'paid_amount' => (float) ($s->paid_amount ?? 0),
                ];
            });

        // Payments list
        $payments = BuyerPayment::where('buyer_id', $invoice->buyer_id)
            ->whereBetween('payment_date', [$invoice->period_start, $invoice->period_end])
            ->latest('payment_date')
            ->get();

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'is_edited' => (bool) $invoice->is_edited,
            'period_start' => $invoice->period_start->format('Y-m-d'),
            'period_end' => $invoice->period_end->format('Y-m-d'),
            'period_start_display' => $invoice->period_start->format('d/m/Y'),
            'period_end_display' => $invoice->period_end->format('d/m/Y'),
            'buyer' => [
                'id' => $buyer->id,
                'name' => $buyer->name,
                'code' => $buyer->buyer_code,
                'phone' => $buyer->phone ?? '-',
                'address' => $buyer->address ?? '-',
                'current_balance' => (float) $buyer->current_balance,
            ],
            'sales' => $sales,
            'payments' => $payments,
            'totals' => [
                'total_quantity' => (float) $invoice->total_quantity,
                'milk_amount' => (float) $invoice->milk_amount,
                'cash_received' => (float) $invoice->cash_received,
                'discount_amount' => (float) $invoice->discount_amount,
                'other_charges' => (float) $invoice->other_charges,
                'previous_balance' => (float) $invoice->previous_balance,
                'net_receivable' => (float) $invoice->net_receivable,
            ],
        ]);
    }

    /**
     * Save & Verify Pending Buyer Invoice -> Move to Generated State
     */
    public function verifyAndSave(Request $request, BuyerInvoice $invoice)
    {
        if ($invoice->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only Pending invoices can be verified & modified.'], 403);
        }

        $validated = $request->validate([
            'discount_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'cash_received' => 'nullable|numeric|min:0',
            'previous_balance' => 'nullable|numeric|min:0',
        ]);

        $milkAmount = (float) $invoice->milk_amount;
        $discountAmount = round((float) ($validated['discount_amount'] ?? $invoice->discount_amount), 2);
        $otherCharges = round((float) ($validated['other_charges'] ?? $invoice->other_charges), 2);
        $cashReceived = round((float) ($validated['cash_received'] ?? $invoice->cash_received), 2);
        $previousBalance = round((float) ($validated['previous_balance'] ?? $invoice->previous_balance), 2);

        $netReceivable = round(max(0, $previousBalance + $milkAmount + $otherCharges - $cashReceived - $discountAmount), 2);

        $invoice->update([
            'discount_amount' => $discountAmount,
            'other_charges' => $otherCharges,
            'cash_received' => $cashReceived,
            'previous_balance' => $previousBalance,
            'net_receivable' => $netReceivable,
            'status' => 'generated',
            'is_edited' => true,
        ]);

        AuditLog::log('Verified & Saved Buyer Invoice', 'BuyerInvoice', $invoice->id, [
            'invoice_number' => $invoice->invoice_number,
            'net_receivable' => $netReceivable,
            'status' => 'generated',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} saved and moved to Generated state!",
                'invoice' => $invoice->fresh('buyer'),
            ]);
        }

        return redirect()->route('buyer-invoices.index', [
            'start_date' => $invoice->period_start->format('Y-m-d'),
            'end_date' => $invoice->period_end->format('Y-m-d'),
            'status_tab' => 'generated',
        ])->with('success', "Invoice #{$invoice->invoice_number} verified and generated successfully!");
    }

    /**
     * Bulk Approve Selected Pending Invoices to Generated State
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:buyer_invoices,id',
        ]);

        $invoices = BuyerInvoice::whereIn('id', $validated['invoice_ids'])->get();
        $count = 0;
        foreach ($invoices as $inv) {
            $inv->update([
                'status' => 'generated',
            ]);
            $count++;
        }

        AuditLog::log("Bulk Approved {$count} Pending Buyer Invoices to Generated", 'BuyerInvoice', null);

        return redirect()->back()->with('success', "{$count} Pending Invoices verified & moved to Generated state!");
    }

    /**
     * Delete pending invoice OR REVERT generated invoice back to pending
     */
    public function destroy(BuyerInvoice $invoice)
    {
        $invNum = $invoice->invoice_number;
        $buyerName = $invoice->buyer->name ?? 'Buyer';

        // Generated invoices are REVERTED to pending (never destroyed completely)
        if ($invoice->status !== 'pending') {
            $invoice->update([
                'status' => 'pending',
                'is_edited' => false,
            ]);

            // Unlink sales so collections are unlocked
            if (Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
                MilkSale::where('buyer_invoice_id', $invoice->id)->update([
                    'buyer_invoice_id' => null,
                ]);
            }

            AuditLog::log('Reverted Generated Buyer Invoice to Pending', 'BuyerInvoice', $invoice->id, ['number' => $invNum]);

            return redirect()->route('buyer-invoices.index', [
                'start_date' => $invoice->period_start->format('Y-m-d'),
                'end_date' => $invoice->period_end->format('Y-m-d'),
                'status_tab' => 'pending',
            ])->with('success', "जनरेटेड इनवॉइस #{$invNum} ({$buyerName}) को सफलतापूर्वक वापस Pending (Review & Verify) में भेज दिया गया है।");
        }

        // Pending draft invoice deletion
        if (Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
            MilkSale::where('buyer_invoice_id', $invoice->id)->update([
                'buyer_invoice_id' => null,
            ]);
        }

        $invoice->delete();
        AuditLog::log('Deleted Pending Buyer Invoice Draft', 'BuyerInvoice', null, ['number' => $invNum]);

        return redirect()->back()->with('success', "Pending draft invoice #{$invNum} deleted.");
    }

    /**
     * Bulk Delete or Revert selected invoices
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:buyer_invoices,id',
        ]);

        $invoices = BuyerInvoice::whereIn('id', $validated['invoice_ids'])->get();
        $revertedCount = 0;
        $deletedCount = 0;

        foreach ($invoices as $inv) {
            if ($inv->status !== 'pending') {
                $inv->update([
                    'status' => 'pending',
                    'is_edited' => false,
                ]);
                if (Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
                    MilkSale::where('buyer_invoice_id', $inv->id)->update([
                        'buyer_invoice_id' => null,
                    ]);
                }
                $revertedCount++;
            } else {
                if (Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
                    MilkSale::where('buyer_invoice_id', $inv->id)->update([
                        'buyer_invoice_id' => null,
                    ]);
                }
                $inv->delete();
                $deletedCount++;
            }
        }

        $msg = [];
        if ($revertedCount > 0) {
            $msg[] = "{$revertedCount} जनरेटेड इनवॉइस को वापस Pending में भेज दिया गया है।";
        }
        if ($deletedCount > 0) {
            $msg[] = "{$deletedCount} पेंडिंग इनवॉइस हटा दिए गए हैं।";
        }

        return redirect()->back()->with('success', implode(' ', $msg));
    }

    /**
     * Print Buyer Invoice Bill (Formatted Slip)
     */
    public function print(BuyerInvoice $invoice)
    {
        $invoice->load(['buyer', 'creator']);
        $buyer = $invoice->buyer;

        $sales = MilkSale::where('buyer_id', $buyer->id)
            ->whereBetween('sale_date', [$invoice->period_start, $invoice->period_end])
            ->orderBy('sale_date', 'asc')
            ->get();

        $payments = BuyerPayment::where('buyer_id', $buyer->id)
            ->whereBetween('payment_date', [$invoice->period_start, $invoice->period_end])
            ->orderBy('payment_date', 'asc')
            ->get();

        return view('buyers.invoices.print', compact('invoice', 'buyer', 'sales', 'payments'));
    }
}
