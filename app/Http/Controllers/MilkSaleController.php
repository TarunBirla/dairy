<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\MilkSale;
use App\Models\Buyer;
use App\Models\Vehicle;
use App\Models\BuyerPayment;
use App\Models\RateChart;
use App\Models\RateChartSlab;
use App\Models\AuditLog;
use Carbon\Carbon;

class MilkSaleController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('milk_sales')) {
                Schema::create('milk_sales', function (Blueprint $table) {
                    $table->id();
                    $table->string('sale_number')->unique();
                    $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                    $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                    $table->date('sale_date');
                    $table->string('shift')->default('morning');
                    $table->string('milk_type')->default('cow');
                    $table->decimal('quantity_liters', 10, 2);
                    $table->decimal('fat_percentage', 4, 2)->default(0.00);
                    $table->decimal('snf_percentage', 4, 2)->default(0.00);
                    $table->decimal('clr_reading', 5, 2)->default(0.00);
                    $table->decimal('rate_per_liter', 10, 2);
                    $table->decimal('total_amount', 12, 2);
                    $table->decimal('paid_amount', 12, 2)->default(0.00);
                    $table->decimal('balance_amount', 12, 2)->default(0.00);
                    $table->string('payment_mode')->nullable()->default('Cash');
                    $table->text('description')->nullable();
                    $table->string('status')->default('completed');
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Display Milk Sale Page matching Screenshot 1 (media_1791389566806.png)
     */
    public function index(Request $request)
    {
        $fromDate = $request->get('from_date', Carbon::today()->format('Y-m-d'));
        $toDate = $request->get('to_date', Carbon::today()->format('Y-m-d'));
        $customerId = $request->get('customer_id');
        $search = $request->get('search');
        $shift = $request->get('shift');

        $query = MilkSale::with(['buyer', 'vehicle']);

        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        if (!empty($customerId) && $customerId !== 'all') {
            $query->where('buyer_id', $customerId);
        }

        if (!empty($shift) && $shift !== 'all') {
            $query->where('shift', $shift);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($b) use ($search) {
                      $b->where('name', 'like', "%{$search}%")
                        ->orWhere('buyer_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function ($v) use ($search) {
                      $v->where('vehicle_number', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%");
                  });
            });
        }

        $sales = $query->latest('id')->paginate(30)->withQueryString();

        // Calculate Totals for the Summary Row
        $summaryQuery = clone $query;
        $totalLiters = (float) $summaryQuery->sum('quantity_liters');
        $totalAmount = (float) $summaryQuery->sum('total_amount');
        $totalPaid = (float) $summaryQuery->sum('paid_amount');
        $totalBalance = (float) $summaryQuery->sum('balance_amount');

        // Data for dropdowns
        $buyers = Buyer::where('status', 'active')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'available')->orWhere('status', 'on_duty')->orderBy('vehicle_number')->get();

        return view('milk_sales.index', compact(
            'sales',
            'buyers',
            'vehicles',
            'fromDate',
            'toDate',
            'customerId',
            'search',
            'shift',
            'totalLiters',
            'totalAmount',
            'totalPaid',
            'totalBalance'
        ));
    }

    /**
     * Store Milk Sale Entry matching Screenshot 1 Top Form
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'buyer_id' => 'required|exists:buyers,id',
            'quantity_liters' => 'required|numeric|min:0.01',
            'fat_percentage' => 'nullable|numeric|min:0|max:20',
            'clr_reading' => 'nullable|numeric|min:0|max:50',
            'snf_percentage' => 'nullable|numeric|min:0|max:20',
            'rate_per_liter' => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'payment_mode' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity_liters'];
        $rate = (float) $validated['rate_per_liter'];
        $totalAmount = round($quantity * $rate, 2);
        $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.00;
        $balanceAmount = max(0, round($totalAmount - $paidAmount, 2));

        $saleNumber = 'SALE-' . str_pad((MilkSale::max('id') + 101), 5, '0', STR_PAD_LEFT);

        $sale = MilkSale::create([
            'sale_number' => $saleNumber,
            'buyer_id' => $validated['buyer_id'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'shift' => $validated['shift'],
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $quantity,
            'fat_percentage' => $validated['fat_percentage'] ?? 0.00,
            'clr_reading' => $validated['clr_reading'] ?? 0.00,
            'snf_percentage' => $validated['snf_percentage'] ?? 0.00,
            'rate_per_liter' => $rate,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'payment_mode' => $validated['payment_mode'] ?? 'Cash',
            'description' => $validated['description'] ?? null,
            'status' => 'completed',
            'created_by' => Auth::id(),
        ]);

        // If paid amount was given directly, optionally create receipt record
        if ($paidAmount > 0) {
            BuyerPayment::create([
                'receipt_number' => 'DIR-' . str_pad((BuyerPayment::max('id') + 101), 4, '0', STR_PAD_LEFT),
                'buyer_id' => $validated['buyer_id'],
                'milk_sale_id' => $sale->id,
                'payment_date' => $validated['sale_date'],
                'type' => 'received',
                'amount' => $paidAmount,
                'payment_mode' => $validated['payment_mode'] ?? 'Cash',
                'comment' => "Direct payment on Milk Sale #{$saleNumber}",
                'created_by' => Auth::id(),
            ]);
        }

        // Update Buyer's Current Balance
        $buyer = Buyer::find($validated['buyer_id']);
        $buyer->recalculateBalance();

        AuditLog::log("Created Milk Sale #{$saleNumber} ({$quantity}L @ ₹{$rate}) for Buyer {$buyer->name}", 'MilkSale', $sale->id);

        return redirect()->route('milk-sales.index')->with('success', "Milk sale #{$saleNumber} saved successfully! Total: ₹" . number_format($totalAmount, 2));
    }

    /**
     * Update an existing milk sale
     */
    public function update(Request $request, MilkSale $milkSale)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'buyer_id' => 'required|exists:buyers,id',
            'quantity_liters' => 'required|numeric|min:0.01',
            'fat_percentage' => 'nullable|numeric|min:0|max:20',
            'clr_reading' => 'nullable|numeric|min:0|max:50',
            'snf_percentage' => 'nullable|numeric|min:0|max:20',
            'rate_per_liter' => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'payment_mode' => 'nullable|string',
        ]);

        $quantity = (float) $validated['quantity_liters'];
        $rate = (float) $validated['rate_per_liter'];
        $totalAmount = round($quantity * $rate, 2);
        $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.00;
        $balanceAmount = max(0, round($totalAmount - $paidAmount, 2));

        $previousBuyerId = $milkSale->buyer_id;

        $milkSale->update([
            'buyer_id' => $validated['buyer_id'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'shift' => $validated['shift'],
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $quantity,
            'fat_percentage' => $validated['fat_percentage'] ?? 0.00,
            'clr_reading' => $validated['clr_reading'] ?? 0.00,
            'snf_percentage' => $validated['snf_percentage'] ?? 0.00,
            'rate_per_liter' => $rate,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'payment_mode' => $validated['payment_mode'] ?? 'Cash',
            'description' => $validated['description'] ?? null,
        ]);

        // Recalculate balance for current and previous buyer if changed
        $buyer = Buyer::find($validated['buyer_id']);
        $buyer->recalculateBalance();

        if ($previousBuyerId != $buyer->id) {
            $oldBuyer = Buyer::find($previousBuyerId);
            if ($oldBuyer) $oldBuyer->recalculateBalance();
        }

        AuditLog::log("Updated Milk Sale #{$milkSale->sale_number}", 'MilkSale', $milkSale->id);

        return redirect()->route('milk-sales.index')->with('success', "Milk sale #{$milkSale->sale_number} updated successfully!");
    }

    /**
     * Delete a milk sale
     */
    public function destroy(MilkSale $milkSale)
    {
        $saleNumber = $milkSale->sale_number;
        $buyerId = $milkSale->buyer_id;

        $milkSale->delete();

        $buyer = Buyer::find($buyerId);
        if ($buyer) {
            $buyer->recalculateBalance();
        }

        AuditLog::log("Deleted Milk Sale #{$saleNumber}", 'MilkSale', null);

        return redirect()->route('milk-sales.index')->with('success', "Milk sale #{$saleNumber} deleted successfully.");
    }

    /**
     * Printable slip for a single milk sale
     */
    public function slip(MilkSale $milkSale)
    {
        $milkSale->load(['buyer', 'vehicle']);
        return view('milk_sales.slip', compact('milkSale'));
    }

    /**
     * Helper JSON API: Get Buyer Rates and current balance
     */
    public function getBuyerRates(Buyer $buyer)
    {
        return response()->json([
            'id' => $buyer->id,
            'name' => $buyer->name,
            'buyer_code' => $buyer->buyer_code,
            'phone' => $buyer->phone,
            'milk_type' => $buyer->milk_type,
            'cow_rate_mode' => $buyer->cow_rate_mode,
            'cow_fixed_rate' => (float) $buyer->cow_fixed_rate,
            'buffalo_rate_mode' => $buyer->buffalo_rate_mode,
            'buffalo_fixed_rate' => (float) $buyer->buffalo_fixed_rate,
            'current_balance' => (float) $buyer->current_balance,
        ]);
    }

    /**
     * Customer Bill (Milk Sale Invoices - Matching Image 1)
     */
    public function invoices(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Cycle Range string parameter if provided e.g. "21 Aug 2026 - 31 Aug 2026"
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

        // If no range selected, default to current 10-day cycle
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

        $search = $request->get('search');

        // Build list of 10-day cycle presets
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

        $activeCycleKey = Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y');
        if (!isset($cycles[$activeCycleKey])) {
            $cycles[$activeCycleKey] = $activeCycleKey . " (Custom)";
        }

        $buyersQuery = Buyer::query();
        if (!empty($search)) {
            $buyersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('buyer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $buyers = $buyersQuery->orderBy('buyer_code')->get();

        $invoices = collect();
        foreach ($buyers as $b) {
            $salesPeriod = MilkSale::where('buyer_id', $b->id)
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->get();
            $liters = (float) $salesPeriod->sum('quantity_liters');
            $amount = (float) $salesPeriod->sum('total_amount');
            $directPaid = (float) $salesPeriod->sum('paid_amount');

            $khataReceived = (float) BuyerPayment::where('buyer_id', $b->id)
                ->where('type', 'received')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');
            $cashReceived = $directPaid + $khataReceived;

            // Opening Balance before startDate
            $salesBefore = (float) MilkSale::where('buyer_id', $b->id)
                ->where('sale_date', '<', $startDate)
                ->sum('total_amount');
            $paidBefore = (float) MilkSale::where('buyer_id', $b->id)
                ->where('sale_date', '<', $startDate)
                ->sum('paid_amount');
            $khataBefore = (float) BuyerPayment::where('buyer_id', $b->id)
                ->where('type', 'received')
                ->where('payment_date', '<', $startDate)
                ->sum('amount');
            $refundsBefore = (float) BuyerPayment::where('buyer_id', $b->id)
                ->where('type', 'paid')
                ->where('payment_date', '<', $startDate)
                ->sum('amount');

            $prevAmount = ($salesBefore - ($paidBefore + $khataBefore)) + $refundsBefore;
            $closingBalance = ($prevAmount + $amount) - $cashReceived;

            // Include if there's any activity or non-zero balance
            if ($liters > 0 || abs($prevAmount) > 0.01 || $cashReceived > 0 || !empty($search)) {
                $invoices->push((object) [
                    'buyer' => $b,
                    'code' => $b->buyer_code,
                    'name' => $b->name,
                    'liters' => $liters,
                    'amount' => $amount,
                    'cash_received' => $cashReceived,
                    'prev_amount' => $prevAmount,
                    'total_amount' => max(0, $closingBalance),
                    'closing_balance' => $closingBalance,
                ]);
            }
        }

        $totals = (object) [
            'liters' => (float) $invoices->sum('liters'),
            'amount' => (float) $invoices->sum('amount'),
            'cash_received' => (float) $invoices->sum('cash_received'),
            'prev_amount' => (float) $invoices->sum('prev_amount'),
            'total_amount' => (float) $invoices->sum('total_amount'),
        ];

        return view('milk_sales.invoices', compact(
            'invoices',
            'totals',
            'startDate',
            'endDate',
            'activeCycleKey',
            'cycles',
            'search'
        ));
    }

    /**
     * Milk Sale Rate Chart (Matching Image 2)
     */
    public function rateChart(Request $request)
    {
        $milkType = $request->get('milk_type', 'cow');
        if (!in_array($milkType, ['cow', 'buffalo', 'mixed'])) {
            $milkType = 'cow';
        }

        // 1. Try to find rate chart specifically configured for milk_sale
        $chart = RateChart::where('milk_type', $milkType)
            ->where('category', 'milk_sale')
            ->where('status', 'active')
            ->first();

        // 2. Fallback: check if there is an active chart marked default for this milk_type
        if (!$chart) {
            $chart = RateChart::where('milk_type', $milkType)
                ->where('status', 'active')
                ->where('is_default', true)
                ->first();
        }

        // 3. Fallback: any active rate chart for this milk_type
        if (!$chart) {
            $chart = RateChart::where('milk_type', $milkType)
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        $allCharts = RateChart::where('status', 'active')->orderBy('name')->get();

        return view('milk_sales.rate_chart', compact(
            'chart',
            'milkType',
            'allCharts'
        ));
    }

    /**
     * Set / Assign default Rate Chart for Milk Sale
     */
    public function setDefaultRateChart(Request $request)
    {
        $validated = $request->validate([
            'rate_chart_id' => 'required|exists:rate_charts,id',
            'milk_type' => 'required|in:cow,buffalo,mixed',
        ]);

        $chart = RateChart::findOrFail($validated['rate_chart_id']);

        RateChart::where('milk_type', $validated['milk_type'])
            ->where('category', 'milk_sale')
            ->update(['is_default' => false]);

        $chart->update([
            'category' => 'milk_sale',
            'is_default' => true,
        ]);

        AuditLog::log("Set Rate Chart {$chart->name} as active for Milk Sale ({$validated['milk_type']})", 'RateChart', $chart->id);

        return redirect()->route('milk-sales.rate-chart', ['milk_type' => $validated['milk_type']])
            ->with('success', "Rate Chart '{$chart->name}' applied successfully for " . ucfirst($validated['milk_type']) . " Milk Sale!");
    }

    /**
     * Dynamic Rate Calculation API for Milk Sale Entry Form
     */
    public function calculateRateApi(Request $request)
    {
        $buyerId = $request->get('buyer_id');
        $milkType = $request->get('milk_type', 'cow');
        $fat = (float) $request->get('fat', 0);
        $clr = (float) $request->get('clr', 0);
        $snf = (float) $request->get('snf', 0);

        if ($snf <= 0 && $clr > 0) {
            $snf = round(($clr / 4) + (0.21 * $fat) + 0.36, 2);
        }

        // Check if buyer has fixed rate
        if ($buyerId) {
            $buyer = Buyer::find($buyerId);
            if ($buyer) {
                if ($milkType === 'cow' && $buyer->cow_fixed_rate > 0) {
                    return response()->json(['rate' => (float) $buyer->cow_fixed_rate, 'source' => 'buyer_fixed']);
                }
                if ($milkType === 'buffalo' && $buyer->buffalo_fixed_rate > 0) {
                    return response()->json(['rate' => (float) $buyer->buffalo_fixed_rate, 'source' => 'buyer_fixed']);
                }
            }
        }

        // Check Milk Sale Rate Chart
        $chart = RateChart::where('milk_type', $milkType)
            ->where('category', 'milk_sale')
            ->where('status', 'active')
            ->first();

        if (!$chart) {
            $chart = RateChart::where('milk_type', $milkType)
                ->where('status', 'active')
                ->where('is_default', true)
                ->first();
        }

        if ($chart) {
            $secondary = ($chart->format === 'fat_clr') ? $clr : $snf;
            $rate = $chart->calculateRate($fat, $secondary);
            return response()->json(['rate' => $rate, 'source' => 'rate_chart', 'chart_name' => $chart->name]);
        }

        return response()->json(['rate' => 0.00, 'source' => 'none']);
    }
}
