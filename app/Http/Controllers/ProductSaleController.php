<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ProductSale;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;

class ProductSaleController extends Controller
{
    public function __construct()
    {
        $this->ensureSchema();
    }

    /**
     * Ensure table exists safely.
     */
    private function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('product_sales')) {
                Schema::create('product_sales', function (Blueprint $table) {
                    $table->id();
                    $table->string('sale_number')->unique();
                    $table->date('sale_date');
                    $table->foreignId('farmer_id')->nullable()->constrained('farmers')->nullOnDelete();
                    $table->string('farmer_code')->nullable();
                    $table->string('farmer_name');
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                    $table->string('product_name');
                    $table->string('unit')->default('kg');
                    $table->decimal('quantity', 10, 2);
                    $table->decimal('rate', 10, 2);
                    $table->decimal('total_amount', 12, 2);
                    $table->boolean('is_paid')->default(true);
                    $table->decimal('paid_amount', 12, 2)->default(0.00);
                    $table->decimal('remaining_amount', 12, 2)->default(0.00);
                    $table->string('payment_mode')->default('Cash');
                    $table->text('remarks')->nullable();
                    $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Display listing of Sales Products.
     */
    public function index(Request $request)
    {
        $query = ProductSale::with(['product', 'farmer', 'recorder']);

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('farmer_name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('sale_number', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        // Farmer filter
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        // Product filter
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Payment Mode filter
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('sale_date', $request->date);
        }

        // Summary calculations
        $totalQuantity = (clone $query)->sum('quantity');
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalRemaining = (clone $query)->sum('remaining_amount');

        // Paginate
        $sales = $query->orderBy('sale_date', 'desc')
                       ->orderBy('id', 'desc')
                       ->paginate(15)
                       ->withQueryString();

        $farmers = Farmer::where('status', 'active')->orderBy('name', 'asc')->get();
        $products = Product::where('status', 'active')->orderBy('name', 'asc')->get();

        $nextSaleNumber = 'SAL-' . date('ymd') . '-' . sprintf('%03d', (ProductSale::count() + 1));

        return view('product_sales.index', [
            'sales' => $sales,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'farmers' => $farmers,
            'products' => $products,
            'nextSaleNumber' => $nextSaleNumber,
        ]);
    }

    /**
     * Store newly created sale entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'farmer_id' => 'nullable|exists:farmers,id',
            'farmer_name' => 'nullable|string|max:255',
            'farmer_code' => 'nullable|string|max:50',
            'product_id' => 'required|exists:products,id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'is_paid' => 'required|in:0,1',
            'paid_amount' => 'nullable|numeric',
            'payment_mode' => 'required|string|in:Cash,UPI,Bank Transfer,Ledger',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = (float) $validated['quantity'];
        $rate = (float) $validated['rate'];
        $totalAmount = round($quantity * $rate, 2);

        // Check stock availability
        if ($product->current_stock < $quantity) {
            return back()->withInput()->withErrors([
                'quantity' => "Insufficient stock for {$product->name}. Available: {$product->current_stock} {$product->unit}"
            ]);
        }

        // Resolve Farmer
        $farmer = null;
        if (!empty($validated['farmer_id'])) {
            $farmer = Farmer::find($validated['farmer_id']);
        }
        $farmerName = $farmer ? $farmer->name : ($validated['farmer_name'] ?? 'Direct Farmer');
        $farmerCode = $farmer ? $farmer->farmer_code : ($validated['farmer_code'] ?? null);

        $isPaid = (bool) $validated['is_paid'];
        $paidAmount = $isPaid ? (float) ($validated['paid_amount'] ?? $totalAmount) : 0.00;
        $remainingAmount = round($totalAmount - $paidAmount, 2);

        $saleNumber = 'SAL-' . date('ymd') . '-' . sprintf('%03d', (ProductSale::count() + 1));

        $sale = ProductSale::create([
            'sale_number' => $saleNumber,
            'sale_date' => $validated['sale_date'],
            'farmer_id' => $farmer ? $farmer->id : null,
            'farmer_code' => $farmerCode,
            'farmer_name' => $farmerName,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $validated['unit'] ?: ($product->unit ?: 'kg'),
            'quantity' => $quantity,
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'is_paid' => $isPaid,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_mode' => $validated['payment_mode'],
            'remarks' => $validated['remarks'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        // Reduce Product Stock
        $newStock = max(0, (float) $product->current_stock - $quantity);
        $product->update([
            'current_stock' => $newStock,
            'in_stock' => $newStock > 0,
        ]);

        // Record Inventory Transaction
        InventoryTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => 'sale_outward',
            'quantity' => $quantity,
            'unit_cost' => $rate,
            'balance_after' => $newStock,
            'reference_type' => 'product_sale',
            'reference_id' => $sale->id,
            'recorded_by' => Auth::id(),
            'notes' => "Sales Product #{$saleNumber} to {$farmerName} ({$validated['payment_mode']})",
        ]);

        AuditLog::log('Created Sales Product Entry', 'ProductSale', $sale->id, [
            'sale_number' => $saleNumber,
            'farmer' => $farmerName,
            'product' => $product->name,
            'quantity' => $quantity,
            'total' => $totalAmount,
        ]);

        return redirect()->route('product-sales.index')
                         ->with('success', "Sale #{$saleNumber} for {$product->name} (₹{$totalAmount}) recorded successfully!");
    }

    /**
     * Show single sale details (JSON).
     */
    public function show(ProductSale $productSale)
    {
        $productSale->load(['product', 'farmer', 'recorder']);
        return response()->json([
            'success' => true,
            'sale' => $productSale,
        ]);
    }

    /**
     * Update an existing product sale.
     */
    public function update(Request $request, ProductSale $productSale)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'farmer_id' => 'nullable|exists:farmers,id',
            'farmer_name' => 'nullable|string|max:255',
            'farmer_code' => 'nullable|string|max:50',
            'product_id' => 'required|exists:products,id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'is_paid' => 'required|in:0,1',
            'paid_amount' => 'nullable|numeric',
            'payment_mode' => 'required|string|in:Cash,UPI,Bank Transfer,Ledger',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $newQuantity = (float) $validated['quantity'];
        $oldQuantity = (float) $productSale->quantity;
        $rate = (float) $validated['rate'];
        $totalAmount = round($newQuantity * $rate, 2);

        // Manage inventory stock delta
        if ($productSale->product_id == $product->id) {
            $diff = $newQuantity - $oldQuantity;
            if ($diff > 0 && $product->current_stock < $diff) {
                return back()->withInput()->withErrors([
                    'quantity' => "Insufficient stock for {$product->name}. Additional needed: {$diff}, available: {$product->current_stock}"
                ]);
            }
            $newStock = max(0, (float) $product->current_stock - $diff);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);
        } else {
            // Revert old product stock
            $oldProduct = Product::find($productSale->product_id);
            if ($oldProduct) {
                $oldStock = (float) $oldProduct->current_stock + $oldQuantity;
                $oldProduct->update([
                    'current_stock' => $oldStock,
                    'in_stock' => $oldStock > 0,
                ]);
            }
            // Check & deduct new product stock
            if ($product->current_stock < $newQuantity) {
                return back()->withInput()->withErrors([
                    'quantity' => "Insufficient stock for {$product->name}. Needed: {$newQuantity}, available: {$product->current_stock}"
                ]);
            }
            $newStock = max(0, (float) $product->current_stock - $newQuantity);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);
        }

        // Resolve Farmer
        $farmer = null;
        if (!empty($validated['farmer_id'])) {
            $farmer = Farmer::find($validated['farmer_id']);
        }
        $farmerName = $farmer ? $farmer->name : ($validated['farmer_name'] ?? $productSale->farmer_name);
        $farmerCode = $farmer ? $farmer->farmer_code : ($validated['farmer_code'] ?? $productSale->farmer_code);

        $isPaid = (bool) $validated['is_paid'];
        $paidAmount = $isPaid ? (float) ($validated['paid_amount'] ?? $totalAmount) : 0.00;
        $remainingAmount = round($totalAmount - $paidAmount, 2);

        $productSale->update([
            'sale_date' => $validated['sale_date'],
            'farmer_id' => $farmer ? $farmer->id : null,
            'farmer_code' => $farmerCode,
            'farmer_name' => $farmerName,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit' => $validated['unit'] ?: ($product->unit ?: 'kg'),
            'quantity' => $newQuantity,
            'rate' => $rate,
            'total_amount' => $totalAmount,
            'is_paid' => $isPaid,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_mode' => $validated['payment_mode'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditLog::log('Updated Sales Product Entry', 'ProductSale', $productSale->id, [
            'sale_number' => $productSale->sale_number,
            'farmer' => $farmerName,
            'product' => $product->name,
            'quantity' => $newQuantity,
            'total' => $totalAmount,
        ]);

        return redirect()->route('product-sales.index')
                         ->with('success', "Sale #{$productSale->sale_number} updated successfully!");
    }

    /**
     * Delete sale entry & restore inventory.
     */
    public function destroy(ProductSale $productSale)
    {
        $product = Product::find($productSale->product_id);
        if ($product) {
            $restoredStock = (float) $product->current_stock + (float) $productSale->quantity;
            $product->update([
                'current_stock' => $restoredStock,
                'in_stock' => $restoredStock > 0,
            ]);
        }

        $saleNum = $productSale->sale_number;
        $productSale->delete();

        AuditLog::log('Deleted Sales Product Entry', 'ProductSale', $productSale->id, [
            'sale_number' => $saleNum,
        ]);

        return redirect()->route('product-sales.index')
                         ->with('success', "Sale #{$saleNum} deleted and stock restored successfully.");
    }

    /**
     * Printable slip view for a product sale.
     */
    public function printSlip(ProductSale $productSale)
    {
        $productSale->load(['product', 'farmer', 'recorder']);
        return view('product_sales.print', ['sale' => $productSale]);
    }
}
