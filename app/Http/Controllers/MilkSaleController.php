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
}
