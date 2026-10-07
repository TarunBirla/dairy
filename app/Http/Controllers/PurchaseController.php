<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ProductPurchase;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->ensureSchemaAndDefaults();
    }

    /**
     * Safety check to ensure tables and sample dealers exist.
     */
    private function ensureSchemaAndDefaults(): void
    {
        try {
            if (!Schema::hasTable('dealers')) {
                Schema::create('dealers', function (Blueprint $table) {
                    $table->id();
                    $table->string('code')->unique();
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->text('address')->nullable();
                    $table->string('status')->default('active');
                    $table->timestamps();
                    $table->softDeletes();
                });
            }

            if (!Schema::hasTable('product_purchases')) {
                Schema::create('product_purchases', function (Blueprint $table) {
                    $table->id();
                    $table->string('purchase_number')->unique();
                    $table->date('purchase_date');
                    $table->string('shift')->default('morning');
                    $table->foreignId('dealer_id')->nullable()->constrained('dealers')->nullOnDelete();
                    $table->string('dealer_code')->nullable();
                    $table->string('dealer_name');
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                    $table->string('product_name');
                    $table->string('unit')->default('kg');
                    $table->decimal('quantity', 10, 2);
                    $table->decimal('rate', 10, 2);
                    $table->decimal('sale_rate', 10, 2)->nullable();
                    $table->decimal('total_amount', 12, 2);
                    $table->decimal('paid_amount', 12, 2)->default(0.00);
                    $table->decimal('remaining_amount', 12, 2)->default(0.00);
                    $table->decimal('advance_amount', 12, 2)->default(0.00);
                    $table->text('note')->nullable();
                    $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }

            // Seed default dealers matching reference if empty
            if (Dealer::count() === 0) {
                Dealer::firstOrCreate(['code' => '001'], ['name' => 'akshay', 'phone' => '9876543210']);
                Dealer::firstOrCreate(['code' => '002'], ['name' => 'anish', 'phone' => '9876543211']);
                Dealer::firstOrCreate(['code' => '003'], ['name' => 'Akarsh', 'phone' => '9876543212']);
                Dealer::firstOrCreate(['code' => '004'], ['name' => 'Asim Kumar', 'phone' => '9876543213']);
                Dealer::firstOrCreate(['code' => '005'], ['name' => 'Kailash Agro', 'phone' => '9876543214']);
            }
        } catch (\Throwable $e) {
            // Ignore in constructor if running migrations or no DB connection
        }
    }

    /**
     * Display listing of all Buy Products (Purchases).
     */
    public function index(Request $request)
    {
        $query = ProductPurchase::with(['product', 'dealer', 'recorder']);

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('dealer_name', 'like', "%{$search}%")
                  ->orWhere('dealer_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('purchase_number', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Shift filter
        if ($request->filled('shift') && in_array($request->shift, ['morning', 'evening'])) {
            $query->where('shift', $request->shift);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('purchase_date', $request->date);
        }

        // Dealer filter
        if ($request->filled('dealer_id')) {
            $query->where('dealer_id', $request->dealer_id);
        }

        // Product filter
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Calculate summary metrics on filtered dataset
        $totalQuantity = (clone $query)->sum('quantity');
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalRemaining = (clone $query)->sum('remaining_amount');

        // Paginate results
        $purchases = $query->orderBy('purchase_date', 'desc')
                           ->orderBy('id', 'desc')
                           ->paginate(15)
                           ->withQueryString();

        // Data for Add/Edit Modal
        $dealers = Dealer::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();

        // Current shift helper (Morning before 2 PM, Evening 2 PM onward)
        $currentHour = (int) now()->format('H');
        $defaultShift = ($currentHour >= 14) ? 'evening' : 'morning';

        // Next purchase voucher code
        $nextPurchaseNumber = 'PUR-' . date('ymd') . '-' . sprintf('%03d', (ProductPurchase::count() + 1));

        return view('purchases.index', [
            'purchases' => $purchases,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'dealers' => $dealers,
            'products' => $products,
            'defaultShift' => $defaultShift,
            'nextPurchaseNumber' => $nextPurchaseNumber,
        ]);
    }

    /**
     * Store a newly created Buy Product entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'dealer_id' => 'nullable|exists:dealers,id',
            'dealer_name' => 'nullable|string|max:255',
            'dealer_code' => 'nullable|string|max:50',
            'product_id' => 'required|exists:products,id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        // Resolve Dealer
        $dealer = null;
        if (!empty($validated['dealer_id'])) {
            $dealer = Dealer::find($validated['dealer_id']);
        }

        $dealerName = $dealer ? $dealer->name : ($validated['dealer_name'] ?? 'Direct Supplier');
        $dealerCode = $dealer ? $dealer->code : ($validated['dealer_code'] ?? null);

        // Resolve Product
        $product = Product::findOrFail($validated['product_id']);
        $productName = $product->name;
        $unit = $validated['unit'] ?: ($product->unit ?: 'kg');

        $quantity = (float) $validated['quantity'];
        $rate = (float) $validated['rate'];
        $saleRate = !empty($validated['sale_rate']) ? (float) $validated['sale_rate'] : ($product->price ?? 0);
        $totalAmount = round($quantity * $rate, 2);

        $paidAmount = !empty($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.00;
        $remainingAmount = max(0.00, round($totalAmount - $paidAmount, 2));
        $advanceAmount = !empty($validated['advance_amount']) ? (float) $validated['advance_amount'] : 0.00;

        // Generate Unique Voucher Number
        $purchaseNumber = 'PUR-' . date('ymd') . '-' . sprintf('%03d', (ProductPurchase::count() + 1));

        $purchase = ProductPurchase::create([
            'purchase_number' => $purchaseNumber,
            'purchase_date' => $validated['purchase_date'],
            'shift' => $validated['shift'],
            'dealer_id' => $dealer ? $dealer->id : null,
            'dealer_code' => $dealerCode,
            'dealer_name' => $dealerName,
            'product_id' => $product->id,
            'product_name' => $productName,
            'unit' => $unit,
            'quantity' => $quantity,
            'rate' => $rate,
            'sale_rate' => $saleRate,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'advance_amount' => $advanceAmount,
            'note' => $validated['note'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        // Automatically update Product Stock
        $newStock = (float) $product->current_stock + $quantity;
        $product->update([
            'current_stock' => $newStock,
            'in_stock' => $newStock > 0,
            'cost_price' => $rate, // Keep latest purchase cost
        ]);

        // Record Inventory Transaction
        InventoryTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => 'purchase_inward',
            'quantity' => $quantity,
            'unit_cost' => $rate,
            'balance_after' => $newStock,
            'reference_type' => 'product_purchase',
            'reference_id' => $purchase->id,
            'recorded_by' => Auth::id(),
            'notes' => "Buy Product #{$purchaseNumber} from {$dealerName} ({$validated['shift']} shift)",
        ]);

        AuditLog::log('Recorded Buy Product Entry', 'ProductPurchase', $purchase->id, [
            'voucher' => $purchaseNumber,
            'product' => $productName,
            'quantity' => $quantity,
            'amount' => $totalAmount,
        ]);

        return redirect()->route('purchases.index')
                         ->with('success', "Buy Product entry #{$purchaseNumber} for {$productName} (₹{$totalAmount}) saved successfully!");
    }

    /**
     * Show details of a single purchase (JSON or view).
     */
    public function show(ProductPurchase $purchase)
    {
        $purchase->load(['product', 'dealer', 'recorder']);
        return response()->json([
            'success' => true,
            'purchase' => $purchase,
        ]);
    }

    /**
     * Update an existing Buy Product entry.
     */
    public function update(Request $request, ProductPurchase $purchase)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'dealer_id' => 'nullable|exists:dealers,id',
            'dealer_name' => 'nullable|string|max:255',
            'dealer_code' => 'nullable|string|max:50',
            'product_id' => 'required|exists:products,id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        $dealer = null;
        if (!empty($validated['dealer_id'])) {
            $dealer = Dealer::find($validated['dealer_id']);
        }

        $dealerName = $dealer ? $dealer->name : ($validated['dealer_name'] ?? $purchase->dealer_name);
        $dealerCode = $dealer ? $dealer->code : ($validated['dealer_code'] ?? $purchase->dealer_code);

        $product = Product::findOrFail($validated['product_id']);
        $productName = $product->name;
        $unit = $validated['unit'] ?: ($product->unit ?: 'kg');

        $newQuantity = (float) $validated['quantity'];
        $oldQuantity = (float) $purchase->quantity;
        $quantityDiff = $newQuantity - $oldQuantity;

        $rate = (float) $validated['rate'];
        $saleRate = !empty($validated['sale_rate']) ? (float) $validated['sale_rate'] : ($purchase->sale_rate ?? 0);
        $totalAmount = round($newQuantity * $rate, 2);

        $paidAmount = !empty($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.00;
        $remainingAmount = max(0.00, round($totalAmount - $paidAmount, 2));
        $advanceAmount = !empty($validated['advance_amount']) ? (float) $validated['advance_amount'] : 0.00;

        // If product was changed or quantity changed, adjust stock
        if ($purchase->product_id == $product->id) {
            $newStock = max(0, (float) $product->current_stock + $quantityDiff);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);
        } else {
            // Revert old product stock
            $oldProduct = Product::find($purchase->product_id);
            if ($oldProduct) {
                $oldProductStock = max(0, (float) $oldProduct->current_stock - $oldQuantity);
                $oldProduct->update([
                    'current_stock' => $oldProductStock,
                    'in_stock' => $oldProductStock > 0,
                ]);
            }
            // Add to new product stock
            $newProductStock = (float) $product->current_stock + $newQuantity;
            $product->update([
                'current_stock' => $newProductStock,
                'in_stock' => $newProductStock > 0,
            ]);
        }

        $purchase->update([
            'purchase_date' => $validated['purchase_date'],
            'shift' => $validated['shift'],
            'dealer_id' => $dealer ? $dealer->id : null,
            'dealer_code' => $dealerCode,
            'dealer_name' => $dealerName,
            'product_id' => $product->id,
            'product_name' => $productName,
            'unit' => $unit,
            'quantity' => $newQuantity,
            'rate' => $rate,
            'sale_rate' => $saleRate,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'advance_amount' => $advanceAmount,
            'note' => $validated['note'] ?? null,
        ]);

        AuditLog::log('Updated Buy Product Entry', 'ProductPurchase', $purchase->id, [
            'voucher' => $purchase->purchase_number,
            'product' => $productName,
            'quantity' => $newQuantity,
            'amount' => $totalAmount,
        ]);

        return redirect()->route('purchases.index')
                         ->with('success', "Buy Product entry #{$purchase->purchase_number} updated successfully!");
    }

    /**
     * Remove the specified Buy Product entry and roll back inventory.
     */
    public function destroy(ProductPurchase $purchase)
    {
        $product = Product::find($purchase->product_id);
        if ($product) {
            $newStock = max(0, (float) $product->current_stock - (float) $purchase->quantity);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);
        }

        $voucherNumber = $purchase->purchase_number;
        $purchase->delete();

        AuditLog::log('Deleted Buy Product Entry', 'ProductPurchase', $purchase->id, [
            'voucher' => $voucherNumber,
        ]);

        return redirect()->route('purchases.index')
                         ->with('success', "Buy Product entry #{$voucherNumber} deleted and inventory adjusted successfully.");
    }

    /**
     * Print slip / receipt for a purchase entry.
     */
    public function printSlip(ProductPurchase $purchase)
    {
        $purchase->load(['product', 'dealer', 'recorder']);
        return view('purchases.print', compact('purchase'));
    }

    /**
     * Quick create dealer via AJAX.
     */
    public function quickCreateDealer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $nextCode = sprintf('%03d', (Dealer::max('id') ?? 0) + 1);

        $dealer = Dealer::create([
            'code' => $nextCode,
            'name' => trim($validated['name']),
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'dealer' => $dealer,
            'message' => "Dealer {$dealer->name} ({$dealer->code}) registered successfully!",
        ]);
    }
}
