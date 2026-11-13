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

            if (Schema::hasTable('product_sales') && !Schema::hasColumn('product_sales', 'group_sale_number')) {
                Schema::table('product_sales', function (Blueprint $table) {
                    $table->string('group_sale_number')->nullable()->after('sale_number');
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Dedicated live migration endpoint to ensure columns/tables are up to date.
     */
    public function runMigration()
    {
        try {
            $this->ensureSchema();
            return response()->json([
                'status' => 'success',
                'message' => 'Product sales database updated successfully. Multi-product sale parameter group_sale_number is ready.',
                'timestamp' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
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
     * Store newly created sale entry (supports single product as well as multiple products for a farmer).
     */
    public function store(Request $request)
    {
        $this->ensureSchema();

        $saleDate = $request->input('sale_date', now()->format('Y-m-d'));
        $paymentMode = $request->input('payment_mode', $request->input('payment_method', 'Ledger'));
        if (strcasecmp($paymentMode, 'Credit') === 0) {
            $paymentMode = 'Ledger';
        }

        // Support both itemized multiple products and single product submission
        $rawItems = $request->input('items');
        if (is_string($rawItems)) {
            $rawItems = json_decode($rawItems, true);
        }

        $items = [];
        if (is_array($rawItems) && count($rawItems) > 0) {
            foreach ($rawItems as $itm) {
                if (!empty($itm['product_id']) && (float)($itm['quantity'] ?? 0) > 0) {
                    $items[] = [
                        'product_id' => $itm['product_id'],
                        'unit' => $itm['unit'] ?? 'kg',
                        'quantity' => (float)$itm['quantity'],
                        'rate' => (float)($itm['rate'] ?? 0),
                    ];
                }
            }
        }

        // Fallback to single product fields if items array not provided
        if (empty($items) && $request->filled('product_id')) {
            $rate = $request->input('rate', $request->input('unit_price', 0));
            $items[] = [
                'product_id' => $request->input('product_id'),
                'unit' => $request->input('unit', 'kg'),
                'quantity' => (float)$request->input('quantity', 1),
                'rate' => (float)$rate,
            ];
        }

        if (empty($items)) {
            return back()->withInput()->withErrors([
                'product_id' => 'Please add at least one product with valid quantity to record a sale.'
            ]);
        }

        // Validate Farmer
        $farmer = null;
        if ($request->filled('farmer_id')) {
            $farmer = Farmer::find($request->farmer_id);
        }
        $farmerName = $farmer ? $farmer->name : ($request->input('farmer_name') ?: 'Direct Farmer');
        $farmerCode = $farmer ? $farmer->farmer_code : ($request->input('farmer_code') ?: null);

        // Pre-validate stock and load products
        $preparedItems = [];
        $grandTotalAmount = 0.00;

        foreach ($items as $idx => $itm) {
            $product = Product::find($itm['product_id']);
            if (!$product) {
                return back()->withInput()->withErrors([
                    'product_id' => "Product (ID #{$itm['product_id']}) not found."
                ]);
            }

            if ($product->current_stock < $itm['quantity']) {
                return back()->withInput()->withErrors([
                    'quantity' => "Insufficient stock for {$product->name}. Available: {$product->current_stock} {$product->unit}, requested: {$itm['quantity']}"
                ]);
            }

            $lineTotal = round($itm['quantity'] * $itm['rate'], 2);
            $grandTotalAmount += $lineTotal;

            $preparedItems[] = [
                'product' => $product,
                'unit' => $itm['unit'] ?: ($product->unit ?: 'kg'),
                'quantity' => $itm['quantity'],
                'rate' => $itm['rate'],
                'total_amount' => $lineTotal,
            ];
        }

        // Payment logic
        $isPaid = $request->input('is_paid', 1) == 1;
        $totalPaidInput = $isPaid ? (float) $request->input('paid_amount', $grandTotalAmount) : 0.00;
        $totalPaidAmount = min($totalPaidInput, $grandTotalAmount);
        $totalRemainingAmount = round(max(0, $grandTotalAmount - $totalPaidAmount), 2);

        // Base sale number
        $baseSaleNumber = 'SAL-' . date('ymd') . '-' . sprintf('%03d', (ProductSale::count() + 1));
        $createdSales = [];
        $itemCount = count($preparedItems);

        // Track distribution of paid amount across multiple items
        $remainingPaidToAllocate = $totalPaidAmount;

        foreach ($preparedItems as $index => $itemData) {
            $product = $itemData['product'];
            $lineTotal = $itemData['total_amount'];

            // Suffix sale number if multiple items to ensure uniqueness
            $saleNumber = ($itemCount > 1) ? "{$baseSaleNumber}-" . ($index + 1) : $baseSaleNumber;

            if ($isPaid) {
                if ($index === $itemCount - 1) {
                    $itemPaid = round($remainingPaidToAllocate, 2);
                } else {
                    $prop = ($grandTotalAmount > 0) ? ($lineTotal / $grandTotalAmount) : 0;
                    $itemPaid = round(min($remainingPaidToAllocate, $totalPaidAmount * $prop), 2);
                    $remainingPaidToAllocate -= $itemPaid;
                }
            } else {
                $itemPaid = 0.00;
            }

            $itemRemaining = round(max(0, $lineTotal - $itemPaid), 2);

            $sale = ProductSale::create([
                'sale_number' => $saleNumber,
                'group_sale_number' => ($itemCount > 1) ? $baseSaleNumber : null,
                'sale_date' => $saleDate,
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_code' => $farmerCode,
                'farmer_name' => $farmerName,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit' => $itemData['unit'],
                'quantity' => $itemData['quantity'],
                'rate' => $itemData['rate'],
                'total_amount' => $lineTotal,
                'is_paid' => ($itemRemaining <= 0),
                'paid_amount' => $itemPaid,
                'remaining_amount' => $itemRemaining,
                'payment_mode' => $paymentMode,
                'remarks' => $request->input('remarks'),
                'recorded_by' => Auth::id(),
            ]);

            // Deduct stock
            $newStock = max(0, (float) $product->current_stock - $itemData['quantity']);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);

            // Inventory transaction
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'sale_outward',
                'quantity' => $itemData['quantity'],
                'unit_cost' => $itemData['rate'],
                'balance_after' => $newStock,
                'reference_type' => 'product_sale',
                'reference_id' => $sale->id,
                'recorded_by' => Auth::id(),
                'notes' => "Sale #{$saleNumber} to {$farmerName} ({$paymentMode})",
            ]);

            $createdSales[] = $sale;
        }

        AuditLog::log('Created Product Sale(s)', 'ProductSale', $createdSales[0]->id, [
            'base_sale_number' => $baseSaleNumber,
            'items_count' => $itemCount,
            'farmer' => $farmerName,
            'grand_total' => $grandTotalAmount,
        ]);

        $successMsg = ($itemCount > 1)
            ? "Successfully recorded sale of {$itemCount} products for {$farmerName} (Total ₹" . number_format($grandTotalAmount, 2) . ") under #{$baseSaleNumber}!"
            : "Sale #{$baseSaleNumber} for {$preparedItems[0]['product']->name} (₹" . number_format($grandTotalAmount, 2) . ") recorded successfully!";

        if ($request->filled('redirect_to_farmer')) {
            return redirect()->route('farmers.show', ['farmer' => $request->redirect_to_farmer, 'tab' => 'purchases'])
                             ->with('success', $successMsg);
        }

        if ($request->input('redirect_to') === 'collections') {
            return redirect()->route('collections.index', ['tab' => 'product_sales'])
                             ->with('success', $successMsg);
        }

        return redirect()->route('product-sales.index')
                         ->with('success', $successMsg);
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

    /**
     * Quick-mark an unpaid or partially paid purchase as fully Paid.
     */
    public function markPaid(Request $request, ProductSale $productSale)
    {
        $productSale->update([
            'is_paid' => true,
            'paid_amount' => $productSale->total_amount,
            'remaining_amount' => 0,
            'payment_mode' => $request->input('payment_mode', $productSale->payment_mode ?: 'Cash'),
        ]);

        AuditLog::log('Marked Product Purchase as Paid', 'ProductSale', $productSale->id, [
            'sale_number' => $productSale->sale_number,
            'farmer' => $productSale->farmer_name,
            'total_amount' => $productSale->total_amount,
        ]);

        if ($request->filled('farmer_id')) {
            return redirect()->route('farmers.show', ['farmer' => $request->farmer_id, 'tab' => 'purchases'])
                             ->with('success', "Purchase Bill #{$productSale->sale_number} marked as Paid!");
        }

        return back()->with('success', "Purchase Bill #{$productSale->sale_number} marked as Paid!");
    }
}
