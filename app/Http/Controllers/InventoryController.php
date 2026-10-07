<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\BottleTracking;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\AuditLog;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'recorder']);

        if ($request->filled('type')) {
            if ($request->type === 'inward') {
                $query->whereIn('transaction_type', ['purchase_inward', 'production_inward']);
            } elseif ($request->type === 'outward') {
                $query->whereIn('transaction_type', ['sale_outward', 'wastage']);
            } else {
                $query->where('transaction_type', $request->type);
            }
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();
        $products = Product::where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        $lowStockProducts = Product::where('current_stock', '<=', 5)->get();

        return view('inventory.index', compact('transactions', 'products', 'branches', 'lowStockProducts'));
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|in:purchase_inward,production_inward,sale_outward,wastage,adjustment',
            'quantity' => 'required|numeric|min:0.1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $isInward = in_array($validated['transaction_type'], ['purchase_inward', 'production_inward']);

        if (!$isInward && $product->current_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock. Available: {$product->current_stock} {$product->unit}"]);
        }

        $newStock = $isInward
            ? $product->current_stock + $validated['quantity']
            : $product->current_stock - $validated['quantity'];

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
