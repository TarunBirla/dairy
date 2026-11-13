<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
    public function __construct()
    {
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        try {
            if (Schema::hasTable('pos_orders') && !Schema::hasColumn('pos_orders', 'customer_email')) {
                Schema::table('pos_orders', function (Blueprint $table) {
                    $table->string('customer_email')->nullable()->after('customer_phone');
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    public function index(Request $request)
    {
        return $this->hub($request);
    }

    public function terminal(Request $request)
    {
        $products = Product::where('status', 'active')->where('in_stock', true)->get();
        $categories = Category::all();
        $customers = Customer::where('status', 'active')->get();
        $counters = PosCounter::where('status', 'active')->get();
        $recentOrders = PosOrder::with('items.product')->latest()->take(15)->get();

        // Unique Walk-in customers previously entered in POS (only those who were entered)
        try {
            $walkInCustomers = PosOrder::whereNotNull('customer_name')
                ->where('customer_name', '!=', 'Walk-in Customer')
                ->where('customer_name', '!=', '')
                ->select('customer_name', 'customer_phone', 'customer_email')
                ->distinct()
                ->orderBy('customer_name')
                ->get();
        } catch (\Throwable $e) {
            $walkInCustomers = collect();
        }

        return view('pos.index', compact('products', 'categories', 'customers', 'counters', 'recentOrders', 'walkInCustomers'));
    }

    /**
     * Counter Sales Hub Dashboard matching screenshot (Overall Sales & Product Mix analytics)
     */
    public function hub(Request $request)
    {
        // 1. Overall Sales
        $salesFilter = $request->input('sales_filter', 'last_7_days');
        $chartLabels = [];
        $chartValues = [];

        if ($salesFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $periodOrders = PosOrder::whereBetween('created_at', [$startDate, $endDate])->get();
            $overallSalesTotal = $periodOrders->sum('grand_total');

            $daysInMonth = Carbon::now()->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dt = Carbon::now()->day($d);
                $chartLabels[] = $dt->format('d M');
                $chartValues[] = (float) $periodOrders->filter(function ($o) use ($dt) {
                    return $o->created_at->isSameDay($dt);
                })->sum('grand_total');
            }
        } elseif ($salesFilter === 'last_6_months') {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $periodOrders = PosOrder::where('created_at', '>=', $startDate)->get();
            $overallSalesTotal = $periodOrders->sum('grand_total');

            for ($m = 5; $m >= 0; $m--) {
                $dt = Carbon::now()->subMonths($m);
                $chartLabels[] = $dt->format('M Y');
                $chartValues[] = (float) $periodOrders->filter(function ($o) use ($dt) {
                    return $o->created_at->format('Y-m') === $dt->format('Y-m');
                })->sum('grand_total');
            }
        } elseif ($salesFilter === 'year_2026') {
            $year = 2026;
            $periodOrders = PosOrder::whereYear('created_at', $year)->get();
            $overallSalesTotal = $periodOrders->sum('grand_total');

            for ($m = 1; $m <= 12; $m++) {
                $dt = Carbon::create($year, $m, 1);
                $chartLabels[] = $dt->format('M');
                $chartValues[] = (float) $periodOrders->filter(function ($o) use ($year, $m) {
                    return (int)$o->created_at->year === $year && (int)$o->created_at->month === $m;
                })->sum('grand_total');
            }
        } else { // default 'last_7_days'
            $salesFilter = 'last_7_days';
            $startDate = Carbon::today()->subDays(6)->startOfDay();
            $periodOrders = PosOrder::where('created_at', '>=', $startDate)->get();
            $overallSalesTotal = $periodOrders->sum('grand_total');

            for ($i = 6; $i >= 0; $i--) {
                $dt = Carbon::today()->subDays($i);
                $chartLabels[] = $dt->format('d M');
                $chartValues[] = (float) $periodOrders->filter(function ($o) use ($dt) {
                    return $o->created_at->isSameDay($dt);
                })->sum('grand_total');
            }
        }

        // 2. Product Mix
        $mixFilter = $request->input('mix_filter', 'today');
        $mixQuery = PosOrderItem::with('product')
            ->whereHas('order', function ($q) use ($mixFilter) {
                if ($mixFilter === 'yesterday') {
                    $q->whereDate('created_at', Carbon::yesterday());
                } elseif ($mixFilter === 'this_week') {
                    $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($mixFilter === 'last_week') {
                    $q->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                } elseif ($mixFilter === 'this_month') {
                    $q->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                } elseif ($mixFilter === 'last_3_months') {
                    $q->where('created_at', '>=', Carbon::now()->subMonths(3)->startOfDay());
                } elseif ($mixFilter === 'last_6_months') {
                    $q->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfDay());
                } elseif ($mixFilter === 'year_2026') {
                    $q->whereYear('created_at', 2026);
                } else { // 'today'
                    $q->whereDate('created_at', Carbon::today());
                }
            });

        $orderItems = $mixQuery->get();
        $mixTotalAmount = $orderItems->sum('total_price');

        // Group by product
        $productGroups = $orderItems->groupBy(function ($item) {
            return $item->product ? $item->product->name : 'Other Item';
        })->map(function ($items, $name) {
            return [
                'name' => $name,
                'amount' => $items->sum('total_price'),
                'quantity' => $items->sum('quantity'),
            ];
        })->sortByDesc('amount');

        $topProductText = '';
        if ($productGroups->count() > 0 && $mixTotalAmount > 0) {
            $top = $productGroups->first();
            $pct = round(($top['amount'] / $mixTotalAmount) * 100, 1);
            $topProductText = "Top: {$top['name']} · {$pct}%";
        }

        // Palette matching screenshot
        $colors = ['#38bdf8', '#1e40af', '#67e8f9', '#a5f3fc', '#14b8a6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];
        $donutLabels = [];
        $donutValues = [];
        $donutColors = [];
        $mixLegend = [];

        $cIdx = 0;
        foreach ($productGroups as $pg) {
            $c = $colors[$cIdx % count($colors)];
            $donutLabels[] = $pg['name'];
            $donutValues[] = (float) $pg['amount'];
            $donutColors[] = $c;
            $mixLegend[] = [
                'name' => $pg['name'],
                'amount' => $pg['amount'],
                'color' => $c,
            ];
            $cIdx++;
        }

        return view('pos.hub', compact(
            'salesFilter',
            'overallSalesTotal',
            'chartLabels',
            'chartValues',
            'mixFilter',
            'mixTotalAmount',
            'topProductText',
            'donutLabels',
            'donutValues',
            'donutColors',
            'mixLegend'
        ));
    }

    /**
     * Counter Sales Report matching screenshot
     */
    public function reports(Request $request)
    {
        $query = PosOrder::with(['items.product', 'operator', 'counter'])->latest();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($request->filled('payment_mode') && $request->payment_mode !== 'all') {
            $query->where('payment_mode', $request->payment_mode);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%");
            });
        }

        if ($request->input('export') === 'csv') {
            return $this->exportReportsCsv($query->get());
        }

        $totalAmount = (clone $query)->sum('grand_total');
        $totalSalesCount = (clone $query)->count();

        $orders = $query->paginate(20)->withQueryString();

        $dateRangeText = ($startDate && $endDate)
            ? Carbon::parse($startDate)->format('d M') . ' – ' . Carbon::parse($endDate)->format('d M Y')
            : (Carbon::today()->format('d M') . ' – ' . Carbon::today()->format('d M Y'));

        return view('pos.report', compact('orders', 'totalAmount', 'totalSalesCount', 'dateRangeText', 'startDate', 'endDate'));
    }

    /**
     * Delete an order and restore stock
     */
    public function destroy(PosOrder $order)
    {
        // Restore inventory stock
        foreach ($order->items as $item) {
            $prod = Product::find($item->product_id);
            if ($prod) {
                $newStock = $prod->current_stock + $item->quantity;
                $prod->update([
                    'current_stock' => $newStock,
                    'in_stock' => true,
                ]);

                InventoryTransaction::create([
                    'product_id' => $prod->id,
                    'transaction_type' => 'adjustment_add',
                    'quantity' => $item->quantity,
                    'balance_after' => $newStock,
                    'reference_type' => 'pos_order_cancelled',
                    'reference_id' => $order->id,
                    'recorded_by' => Auth::id(),
                    'notes' => "Stock restored upon deleting POS bill #{$order->order_number}",
                ]);
            }
        }

        // If on credit, adjust customer balance
        if ($order->payment_mode === 'credit' && $order->customer_id) {
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $customer->decrement('current_balance', $order->grand_total);
            }
        }

        AuditLog::log('Deleted POS Order', 'PosOrder', $order->id, ['amount' => $order->grand_total]);

        $order->items()->delete();
        $order->delete();

        return back()->with('success', "Counter sale #{$order->order_number} deleted and product stock restored successfully.");
    }

    protected function exportReportsCsv($orders)
    {
        $filename = 'counter_sales_report_' . date('Ymd_His') . '.csv';
        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['Order Number', 'Date & Time', 'Customer', 'Phone', 'Payment Mode', 'Items', 'Subtotal', 'Discount', 'Total Amount']);
            foreach ($orders as $o) {
                $itemsStr = $o->items->map(function ($it) {
                    return ($it->product ? $it->product->name : 'Item') . ' (' . (float)$it->quantity . ' @ ₹' . $it->unit_price . ')';
                })->implode('; ');
                fputcsv($handle, [
                    $o->order_number,
                    $o->created_at->format('Y-m-d H:i:s'),
                    $o->customer_name,
                    $o->customer_phone,
                    strtoupper($o->payment_mode),
                    $itemsStr,
                    $o->subtotal,
                    $o->discount_amount,
                    $o->grand_total,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
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
        $customerName = $customer ? $customer->name : (!empty($validated['customer_name']) ? trim($validated['customer_name']) : 'Walk-in Customer');
        $customerPhone = $customer ? $customer->phone : (!empty($validated['customer_phone']) ? trim($validated['customer_phone']) : null);
        $customerEmail = $customer ? $customer->email : (!empty($validated['customer_email']) ? trim($validated['customer_email']) : null);

        $order = PosOrder::create([
            'order_number' => $orderNumber,
            'branch_id' => $customer ? $customer->branch_id : null,
            'counter_id' => $validated['counter_id'] ?? null,
            'customer_id' => $customer ? $customer->id : null,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
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
