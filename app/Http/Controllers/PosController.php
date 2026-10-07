<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PosCounter;
use App\Models\CounterCashbook;
use App\Models\InventoryTransaction;
use App\Models\CustomerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('status', 'active')->where('in_stock', true)->get();
        $categories = Category::all();
        $customers = Customer::where('status', 'active')->get();
        $counters = PosCounter::where('status', 'active')->get();
        $recentOrders = PosOrder::with('items.product')->latest()->take(5)->get();

        return view('pos.index', compact('products', 'categories', 'customers', 'counters', 'recentOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'payment_mode' => 'required|in:cash,upi,card,credit',
            'counter_id' => 'nullable|exists:pos_counters,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']);
        }

        $discount = (float)($validated['discount_amount'] ?? 0);
        $grandTotal = max(0, $subtotal - $discount);

        $orderNumber = 'POS-' . date('Ymd') . '-' . sprintf('%03d', PosOrder::whereDate('created_at', Carbon::today())->count() + 1);

        $customer = !empty($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;
        $customerName = $customer ? $customer->name : ($validated['customer_name'] ?? 'Walk-in Customer');
        $customerPhone = $customer ? $customer->phone : ($validated['customer_phone'] ?? null);

        $order = PosOrder::create([
            'order_number' => $orderNumber,
            'branch_id' => $customer ? $customer->branch_id : null,
            'counter_id' => $validated['counter_id'] ?? null,
            'customer_id' => $customer ? $customer->id : null,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => 0.00,
            'grand_total' => $grandTotal,
            'payment_mode' => $validated['payment_mode'],
            'payment_status' => ($validated['payment_mode'] === 'credit') ? 'credit' : 'paid',
            'operator_id' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $itemTotal = round($item['quantity'] * $item['unit_price'], 2);
            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $itemTotal,
            ]);

            // Deduct inventory
            $prod = Product::find($item['product_id']);
            if ($prod) {
                $newStock = max(0, $prod->current_stock - $item['quantity']);
                $prod->update([
                    'current_stock' => $newStock,
                    'in_stock' => $newStock > 0,
                ]);

                InventoryTransaction::create([
                    'product_id' => $prod->id,
                    'transaction_type' => 'sale_outward',
                    'quantity' => $item['quantity'],
                    'balance_after' => $newStock,
                    'reference_type' => 'pos_order',
                    'reference_id' => $order->id,
                    'recorded_by' => Auth::id(),
                    'notes' => "Sold at POS counter #{$order->order_number}",
                ]);
            }
        }

        // If customer purchased on credit, update customer dues
        if ($validated['payment_mode'] === 'credit' && $customer) {
            $newBal = $customer->current_balance + $grandTotal;
            $customer->update(['current_balance' => $newBal]);

            CustomerLedger::create([
                'customer_id' => $customer->id,
                'transaction_date' => Carbon::today(),
                'type' => 'debit',
                'amount' => $grandTotal,
                'balance' => $newBal,
                'reference_type' => 'pos_credit_sale',
                'reference_id' => $order->id,
                'description' => "POS Counter Bill on Credit {$orderNumber}",
            ]);
        }

        AuditLog::log('Generated POS Bill', 'PosOrder', $order->id, ['amount' => $grandTotal]);

        return redirect()->route('pos.receipt', $order)->with('success', "Sale {$orderNumber} completed!");
    }

    public function receipt(PosOrder $order)
    {
        $order->load(['items.product', 'counter', 'operator']);
        return view('pos.receipt', compact('order'));
    }

    public function history()
    {
        $orders = PosOrder::with(['items.product', 'operator'])->latest()->paginate(20);
        $todaySales = PosOrder::whereDate('created_at', Carbon::today())->sum('grand_total');
        return view('pos.history', compact('orders', 'todaySales'));
    }

    public function cashbook(Request $request)
    {
        $today = Carbon::today();
        $cashbook = CounterCashbook::whereDate('entry_date', $today)->first();
        $counters = PosCounter::all();
        $recentCashbooks = CounterCashbook::latest()->paginate(15);

        return view('pos.cashbook', compact('cashbook', 'counters', 'recentCashbooks', 'today'));
    }

    public function updateCashbook(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'nullable|numeric|min:0',
            'cash_sales' => 'nullable|numeric|min:0',
            'cash_expenses' => 'nullable|numeric|min:0',
            'actual_counted_cash' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $today = Carbon::today();
        $closing = ($validated['opening_cash'] ?? 0) + ($validated['cash_sales'] ?? 0) - ($validated['cash_expenses'] ?? 0);
        $counted = (float)($validated['actual_counted_cash'] ?? $closing);
        $variance = $counted - $closing;

        CounterCashbook::updateOrCreate(
            ['entry_date' => $today],
            [
                'opening_cash' => $validated['opening_cash'] ?? 0,
                'cash_sales' => $validated['cash_sales'] ?? 0,
                'cash_expenses' => $validated['cash_expenses'] ?? 0,
                'closing_cash' => $closing,
                'actual_counted_cash' => $counted,
                'variance' => $variance,
                'status' => 'closed',
                'notes' => $validated['notes'] ?? null,
                'operator_id' => Auth::id(),
            ]
        );

        return back()->with('success', 'Counter cashbook saved and closed.');
    }
}
