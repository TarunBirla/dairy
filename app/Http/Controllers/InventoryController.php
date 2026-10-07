<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\ProductPurchase;
use App\Models\ProductSale;
use App\Models\PosOrderItem;
use App\Models\BottleTracking;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\AuditLog;

class InventoryController extends Controller
{
    /**
     * Stock Management & Inventory Ledger Dashboard
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'stock-list'); // 'stock-list' or 'movements'

        // 1. Fetch Master Data for Filters & Popups
        $categories = Category::orderBy('name', 'asc')->get();
        $dealers = Schema::hasTable('dealers') 
            ? Dealer::where('status', 'active')->orderBy('name', 'asc')->get() 
            : collect([]);
        $allActiveProducts = Product::where('status', 'active')->orderBy('name', 'asc')->get();

        // 2. Query Products for Stock List Table
        $stockQuery = Product::with('category')->where('status', 'active');

        // Filter: Search (Product Name or Code)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $stockQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter: Category
        if ($request->filled('category_id')) {
            $stockQuery->where('category_id', $request->category_id);
        }

        // Filter: Target Audience (product_for)
        if ($request->filled('product_for') && in_array($request->product_for, ['farmer', 'customer', 'both'])) {
            $stockQuery->where(function ($q) use ($request) {
                $q->where('product_for', $request->product_for)
                  ->orWhere('product_for', 'both');
            });
        }

        // Filter: Stock Status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $stockQuery->where('current_stock', '>', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $stockQuery->whereColumn('current_stock', '<=', 'min_stock_alert')
                           ->where('current_stock', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $stockQuery->where('current_stock', '<=', 0);
            } elseif ($request->stock_status === 'negative') {
                $stockQuery->where('current_stock', '<', 0);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        switch ($sort) {
            case 'name_desc':
                $stockQuery->orderBy('name', 'desc');
                break;
            case 'stock_asc':
                $stockQuery->orderBy('current_stock', 'asc');
                break;
            case 'stock_desc':
                $stockQuery->orderBy('current_stock', 'desc');
                break;
            case 'buy_desc':
                $stockQuery->orderBy('cost_price', 'desc');
                break;
            case 'rate_desc':
                $stockQuery->orderBy('price', 'desc');
                break;
            default:
                $stockQuery->orderBy('name', 'asc');
                break;
        }

        $stockProducts = $stockQuery->paginate(15)->withQueryString();

        // 3. Aggregate Inward, Sold, and Dealer Info for Paginated Products
        $productIds = $stockProducts->pluck('id')->toArray();

        // Purchase Data (ProductPurchases)
        $purchaseData = collect([]);
        if (Schema::hasTable('product_purchases') && !empty($productIds)) {
            $purchaseData = ProductPurchase::whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as qty, SUM(paid_amount) as paid, MAX(dealer_id) as latest_dealer_id, MAX(dealer_code) as latest_dealer_code, MAX(dealer_name) as latest_dealer_name')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');
        }

        // Manual Inward Transactions (without purchase reference)
        $inwardTxData = collect([]);
        if (!empty($productIds)) {
            $inwardTxData = InventoryTransaction::whereIn('product_id', $productIds)
                ->whereIn('transaction_type', ['purchase_inward', 'production_inward'])
                ->whereNull('reference_type')
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');
        }

        // Farmer Sales (ProductSales)
        $farmerSalesData = collect([]);
        if (Schema::hasTable('product_sales') && !empty($productIds)) {
            $farmerSalesData = ProductSale::whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');
        }

        // POS Counter Sales (PosOrderItems)
        $posSalesData = collect([]);
        if (Schema::hasTable('pos_order_items') && !empty($productIds)) {
            $posSalesData = PosOrderItem::whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');
        }

        // Manual Outward Transactions
        $outwardTxData = collect([]);
        if (!empty($productIds)) {
            $outwardTxData = InventoryTransaction::whereIn('product_id', $productIds)
                ->whereIn('transaction_type', ['sale_outward', 'wastage'])
                ->whereNull('reference_type')
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');
        }

        // Transform collection to attach calculated fields
        $stockProducts->getCollection()->transform(function ($product) use (
            $purchaseData, 
            $inwardTxData, 
            $farmerSalesData, 
            $posSalesData, 
            $outwardTxData, 
            $dealers
        ) {
            $purchasedQty = (float) ($purchaseData[$product->id]->qty ?? 0) + (float) ($inwardTxData[$product->id] ?? 0);
            $farmerSold = (float) ($farmerSalesData[$product->id] ?? 0);
            $posSold = (float) ($posSalesData[$product->id] ?? 0);
            $outwardTx = (float) ($outwardTxData[$product->id] ?? 0);
            $totalSold = $farmerSold + $posSold + $outwardTx;

            // Inward logic: if no purchases recorded yet, fallback to current_stock + totalSold
            // so Quantity - Sold Product = Stock is always mathematically sound
            if ($purchasedQty <= 0 && $product->current_stock > 0) {
                $inwardQty = (float) $product->current_stock + $totalSold;
            } else {
                $inwardQty = $purchasedQty;
            }

            $currentStock = (float) $product->current_stock;

            // Dealer information
            $latestDealerId = $purchaseData[$product->id]->latest_dealer_id ?? null;
            $dealerCode = $purchaseData[$product->id]->latest_dealer_code ?? ($dealers->first()->code ?? '001');
            $dealerName = $purchaseData[$product->id]->latest_dealer_name ?? ($dealers->first()->name ?? 'Primary Dealer');
            $paidAmount = (float) ($purchaseData[$product->id]->paid ?? 0);

            $product->inward_qty = $inwardQty;
            $product->sold_qty = $totalSold;
            $product->farmer_sold_qty = $farmerSold;
            $product->pos_sold_qty = $posSold;
            $product->stock_balance = $currentStock;
            $product->dealer_id = $latestDealerId;
            $product->dealer_code = $dealerCode;
            $product->dealer_name = $dealerName;
            $product->paid_amount = $paidAmount;
            $product->stock_valuation = max(0, $currentStock) * (float) $product->cost_price;
            $product->sale_valuation = max(0, $currentStock) * (float) $product->price;

            return $product;
        });

        // 4. Global Summary Metrics for Stat Cards
        $totalProductsCount = Product::where('status', 'active')->count();
        $totalCurrentStock = (float) Product::where('status', 'active')->sum('current_stock');
        
        $totalPurchasedQty = 0;
        if (Schema::hasTable('product_purchases')) {
            $totalPurchasedQty += (float) ProductPurchase::sum('quantity');
        }
        $totalPurchasedQty += (float) InventoryTransaction::whereIn('transaction_type', ['purchase_inward', 'production_inward'])
            ->whereNull('reference_type')->sum('quantity');

        $totalSoldQty = 0;
        if (Schema::hasTable('product_sales')) {
            $totalSoldQty += (float) ProductSale::sum('quantity');
        }
        if (Schema::hasTable('pos_order_items')) {
            $totalSoldQty += (float) PosOrderItem::sum('quantity');
        }
        $totalSoldQty += (float) InventoryTransaction::whereIn('transaction_type', ['sale_outward', 'wastage'])
            ->whereNull('reference_type')->sum('quantity');

        $totalInwardOverall = max($totalPurchasedQty, $totalCurrentStock + $totalSoldQty);

        $totalStockValuation = (float) (Product::where('status', 'active')
            ->where('current_stock', '>', 0)
            ->selectRaw('SUM(current_stock * cost_price) as val')
            ->value('val') ?? 0);

        $lowStockCount = Product::where('status', 'active')
            ->where(function ($q) {
                $q->whereColumn('current_stock', '<=', 'min_stock_alert')
                  ->orWhere('current_stock', '<=', 0);
            })->count();

        // 5. Stock Movements / Audit Ledger Data
        $txQuery = InventoryTransaction::with(['product', 'recorder']);

        if ($request->filled('tx_type')) {
            if ($request->tx_type === 'inward') {
                $txQuery->whereIn('transaction_type', ['purchase_inward', 'production_inward']);
            } elseif ($request->tx_type === 'outward') {
                $txQuery->whereIn('transaction_type', ['sale_outward', 'wastage']);
            } else {
                $txQuery->where('transaction_type', $request->tx_type);
            }
        }

        if ($request->filled('tx_product_id')) {
            $txQuery->where('product_id', $request->tx_product_id);
        }

        $transactions = $txQuery->latest()->paginate(20, ['*'], 'tx_page')->withQueryString();
        $lowStockProducts = Product::where('status', 'active')->where('current_stock', '<=', 5)->get();
        $branches = Branch::where('status', 'active')->get();

        return view('inventory.index', [
            'activeTab' => $activeTab,
            'stockProducts' => $stockProducts,
            'totalProductsCount' => $totalProductsCount,
            'totalInwardOverall' => $totalInwardOverall,
            'totalSoldQty' => $totalSoldQty,
            'totalCurrentStock' => $totalCurrentStock,
            'totalStockValuation' => $totalStockValuation,
            'lowStockCount' => $lowStockCount,
            'categories' => $categories,
            'dealers' => $dealers,
            'allActiveProducts' => $allActiveProducts,
            'transactions' => $transactions,
            'lowStockProducts' => $lowStockProducts,
            'branches' => $branches,
        ]);
    }

    /**
     * Update Stock Item (Rates, Current Stock, Dealer) - Matches Reference Modal
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'cost_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric',
            'dealer_id' => 'nullable|exists:dealers,id',
            'dealer_code' => 'nullable|string|max:50',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStock = (float) $product->current_stock;
        $newStock = (float) $validated['current_stock'];
        $stockDiff = $newStock - $oldStock;

        // Update Product Pricing and Stock
        $product->update([
            'cost_price' => $validated['cost_price'],
            'price' => $validated['price'],
            'current_stock' => $newStock,
            'in_stock' => $newStock > 0,
        ]);

        // If stock was adjusted, record an adjustment transaction for audit trail
        if (abs($stockDiff) > 0.001) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'adjustment',
                'quantity' => abs($stockDiff),
                'unit_cost' => $validated['cost_price'],
                'balance_after' => $newStock,
                'recorded_by' => Auth::id(),
                'notes' => $validated['notes'] ?? ("Manual stock adjustment: {$oldStock} -> {$newStock} by " . (Auth::user()->name ?? 'Admin')),
            ]);
        }

        // If dealer or paid amount was updated, update or link latest purchase
        if (Schema::hasTable('product_purchases')) {
            $latestPurchase = ProductPurchase::where('product_id', $product->id)->latest()->first();
            if ($latestPurchase) {
                $updates = [];
                if (!empty($validated['dealer_id'])) {
                    $dealer = Dealer::find($validated['dealer_id']);
                    if ($dealer) {
                        $updates['dealer_id'] = $dealer->id;
                        $updates['dealer_code'] = $dealer->code;
                        $updates['dealer_name'] = $dealer->name;
                    }
                } elseif (!empty($validated['dealer_code'])) {
                    $updates['dealer_code'] = $validated['dealer_code'];
                }

                if (isset($validated['paid_amount'])) {
                    $updates['paid_amount'] = (float) $validated['paid_amount'];
                    $updates['remaining_amount'] = max(0, (float) $latestPurchase->total_amount - (float) $validated['paid_amount']);
                }

                if (!empty($updates)) {
                    $latestPurchase->update($updates);
                }
            }
        }

        AuditLog::log('Updated Stock Item Details', 'Product', $product->id);

        return back()->with('success', "Stock item updated successfully for {$product->name}.");
    }

    /**
     * Get Product Stock Details via JSON for View Modal
     */
    public function stockDetails($id)
    {
        $product = Product::with('category')->findOrFail($id);

        $purchases = Schema::hasTable('product_purchases')
            ? ProductPurchase::where('product_id', $product->id)->latest()->take(5)->get()
            : collect([]);

        $sales = Schema::hasTable('product_sales')
            ? ProductSale::where('product_id', $product->id)->latest()->take(5)->get()
            : collect([]);

        $recentTransactions = InventoryTransaction::where('product_id', $product->id)
            ->latest()
            ->take(8)
            ->get();

        $totalPurchased = Schema::hasTable('product_purchases')
            ? (float) ProductPurchase::where('product_id', $product->id)->sum('quantity')
            : 0;
        $totalPaid = Schema::hasTable('product_purchases')
            ? (float) ProductPurchase::where('product_id', $product->id)->sum('paid_amount')
            : 0;

        $farmerSold = Schema::hasTable('product_sales')
            ? (float) ProductSale::where('product_id', $product->id)->sum('quantity')
            : 0;
        $posSold = Schema::hasTable('pos_order_items')
            ? (float) PosOrderItem::where('product_id', $product->id)->sum('quantity')
            : 0;
        $totalSold = $farmerSold + $posSold;

        $inward = max($totalPurchased, (float) $product->current_stock + $totalSold);

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ? $product->category->name : 'Uncategorized',
                'product_for' => ucfirst($product->product_for ?? 'both'),
                'unit' => $product->unit,
                'cost_price' => (float) $product->cost_price,
                'price' => (float) $product->price,
                'current_stock' => (float) $product->current_stock,
                'min_stock_alert' => (float) $product->min_stock_alert,
                'inward_quantity' => $inward,
                'total_sold' => $totalSold,
                'farmer_sold' => $farmerSold,
                'pos_sold' => $posSold,
                'stock_value' => max(0, (float) $product->current_stock) * (float) $product->cost_price,
                'sales_value' => max(0, (float) $product->current_stock) * (float) $product->price,
                'total_paid' => $totalPaid,
            ],
            'recent_purchases' => $purchases,
            'recent_sales' => $sales,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    /**
     * Printable Stock Valuation & Inventory Sheet
     */
    public function printStock(Request $request)
    {
        $stockQuery = Product::with('category')->where('status', 'active');

        if ($request->filled('category_id')) {
            $stockQuery->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $stockQuery->where('current_stock', '>', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $stockQuery->whereColumn('current_stock', '<=', 'min_stock_alert')->where('current_stock', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $stockQuery->where('current_stock', '<=', 0);
            }
        }

        $products = $stockQuery->orderBy('name', 'asc')->get();

        $productIds = $products->pluck('id')->toArray();
        $purchaseData = Schema::hasTable('product_purchases') && !empty($productIds)
            ? ProductPurchase::whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')->pluck('qty', 'product_id')
            : collect([]);

        $salesData = Schema::hasTable('product_sales') && !empty($productIds)
            ? ProductSale::whereIn('product_id', $productIds)
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->groupBy('product_id')->pluck('qty', 'product_id')
            : collect([]);

        $totalValuation = 0;
        foreach ($products as $p) {
            $sold = (float) ($salesData[$p->id] ?? 0);
            $inward = (float) ($purchaseData[$p->id] ?? 0);
            if ($inward <= 0 && $p->current_stock > 0) {
                $inward = (float) $p->current_stock + $sold;
            }
            $p->inward_qty = $inward;
            $p->sold_qty = $sold;
            $val = max(0, (float) $p->current_stock) * (float) $p->cost_price;
            $p->valuation = $val;
            $totalValuation += $val;
        }

        return view('inventory.print', compact('products', 'totalValuation'));
    }

    /**
     * Record Inward / Outward / Adjustment Transaction
     */
    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|in:purchase_inward,production_inward,sale_outward,wastage,adjustment',
            'quantity' => 'required|numeric|min:0.1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $isInward = in_array($validated['transaction_type'], ['purchase_inward', 'production_inward']);

        if (!$isInward && $validated['transaction_type'] !== 'adjustment' && $product->current_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock. Available: {$product->current_stock} {$product->unit}"]);
        }

        $newStock = $isInward
            ? (float) $product->current_stock + (float) $validated['quantity']
            : (float) $product->current_stock - (float) $validated['quantity'];

        $product->update([
            'current_stock' => $newStock,
            'in_stock' => $newStock > 0,
        ]);

        $tx = InventoryTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => $validated['transaction_type'],
            'quantity' => $validated['quantity'],
            'unit_cost' => $validated['unit_cost'] ?? $product->cost_price,
            'balance_after' => $newStock,
            'recorded_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('Recorded Inventory Transaction', 'InventoryTransaction', $tx->id);

        return back()->with('success', "Stock transaction recorded. Updated {$product->name} stock to {$newStock} {$product->unit}");
    }

    /**
     * Bottle Tracking View
     */
    public function bottles()
    {
        $bottles = BottleTracking::with('customer')->get();
        $totalIssued = $bottles->sum('issued_count');
        $totalReturned = $bottles->sum('returned_count');
        $totalBroken = $bottles->sum('broken_count');
        $totalBalance = $bottles->sum('balance_bottles');
        $customers = Customer::where('status', 'active')->get();

        return view('inventory.bottles', compact('bottles', 'totalIssued', 'totalReturned', 'totalBroken', 'totalBalance', 'customers'));
    }

    /**
     * Update Bottle Tracking Entry
     */
    public function updateBottles(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'action_type' => 'required|in:issue,return,breakage',
            'count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $tracking = BottleTracking::firstOrCreate(['customer_id' => $validated['customer_id']]);

        if ($validated['action_type'] === 'issue') {
            $tracking->issued_count += $validated['count'];
            $tracking->balance_bottles += $validated['count'];
        } elseif ($validated['action_type'] === 'return') {
            $tracking->returned_count += $validated['count'];
            $tracking->balance_bottles = max(0, $tracking->balance_bottles - $validated['count']);
        } elseif ($validated['action_type'] === 'breakage') {
            $tracking->broken_count += $validated['count'];
            $tracking->balance_bottles = max(0, $tracking->balance_bottles - $validated['count']);
        }

        $tracking->save();
        AuditLog::log('Updated Bottle Tracking', 'BottleTracking', $tracking->id);

        return back()->with('success', "Bottle record updated for customer.");
    }
}
