<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyDelivery;
use App\Models\DeliveryRoute;
use App\Models\Subscription;
use App\Models\Customer;
use App\Models\User;
use App\Models\Branch;
use App\Models\CustomerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    public function routes()
    {
        $routes = DeliveryRoute::with(['deliveryBoy', 'branch'])->withCount('customers')->get();
        $deliveryBoys = User::where('role', User::ROLE_DELIVERY_BOY)->get();
        $branches = Branch::where('status', 'active')->get();

        return view('delivery.routes', compact('routes', 'deliveryBoys', 'branches'));
    }

    public function storeRoute(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:delivery_routes,code',
            'area_name' => 'required|string',
            'delivery_boy_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'description' => 'nullable|string',
        ]);

        $route = DeliveryRoute::create($validated);
        AuditLog::log('Created Delivery Route', 'DeliveryRoute', $route->id);

        return back()->with('success', "Delivery route {$route->name} created!");
    }

    public function storeDeliveryBoyAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $email = !empty($validated['email']) 
            ? $validated['email'] 
            : 'delivery.' . preg_replace('/[^0-9]/', '', $validated['phone']) . '@simpledairy.com';

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $email,
            'role' => User::ROLE_DELIVERY_BOY,
            'status' => 'active',
            'branch_id' => $validated['branch_id'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password'] ?? '123456'),
        ]);

        AuditLog::log('Created Delivery Boy via Quick Modal', 'User', $user->id);

        return response()->json([
            'success' => true,
            'delivery_boy' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
            'message' => "Delivery Boy {$user->name} created successfully!"
        ]);
    }

    public function board(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $shift = $request->get('shift', 'morning');
        $routeId = $request->get('route_id');

        $query = DailyDelivery::with(['customer', 'product', 'route', 'deliveryBoy'])
            ->whereDate('delivery_date', $date)
            ->where('shift', $shift);

        if ($routeId) {
            $query->where('route_id', $routeId);
        }

        $deliveries = $query->orderBy('route_id')->get();
        $routes = DeliveryRoute::where('status', 'active')->get();

        $stats = [
            'total' => $deliveries->count(),
            'delivered' => $deliveries->where('status', 'delivered')->count(),
            'pending' => $deliveries->where('status', 'pending')->count(),
            'skipped' => $deliveries->where('status', 'skipped')->count(),
            'cash_collected' => $deliveries->sum('cash_collected'),
        ];

        return view('delivery.board', compact('deliveries', 'routes', 'date', 'shift', 'routeId', 'stats'));
    }

    public function generateSchedule(Request $request)
    {
        $date = Carbon::parse($request->get('date', Carbon::today()));
        $shift = $request->get('shift', 'morning');

        $subscriptions = Subscription::where('status', 'active')->get();
        $generatedCount = 0;

        foreach ($subscriptions as $sub) {
            if ($sub->isPausedOn($date)) {
                continue;
            }

            // Check if already generated for this date and subscription
            $exists = DailyDelivery::where('subscription_id', $sub->id)
                ->whereDate('delivery_date', $date)
                ->where('shift', $shift)
                ->exists();

            if (!$exists) {
                DailyDelivery::create([
                    'delivery_date' => $date,
                    'shift' => $shift,
                    'route_id' => $sub->route_id,
                    'delivery_boy_id' => $sub->route ? $sub->route->delivery_boy_id : null,
                    'customer_id' => $sub->customer_id,
                    'subscription_id' => $sub->id,
                    'product_id' => $sub->product_id,
                    'quantity' => $sub->quantity,
                    'extra_quantity' => 0,
                    'delivered_quantity' => 0,
                    'unit_price' => $sub->unit_price,
                    'total_amount' => round($sub->quantity * $sub->unit_price, 2),
                    'status' => 'pending',
                ]);
                $generatedCount++;
            }
        }

        return back()->with('success', "Generated {$generatedCount} delivery stops for {$date->format('d M Y')} ({$shift} shift)!");
    }

    public function boyApp(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $shift = (date('H') >= 14) ? 'evening' : 'morning';

        $query = DailyDelivery::with(['customer', 'product', 'route'])
            ->whereDate('delivery_date', $today)
            ->where('shift', $shift);

        if ($user->role === User::ROLE_DELIVERY_BOY) {
            $query->where('delivery_boy_id', $user->id);
        }

        $deliveries = $query->get();

        $stats = [
            'total' => $deliveries->count(),
            'delivered' => $deliveries->where('status', 'delivered')->count(),
            'pending' => $deliveries->where('status', 'pending')->count(),
            'skipped' => $deliveries->where('status', 'skipped')->count(),
            'cash_total' => $deliveries->sum('cash_collected'),
        ];

        return view('delivery.boy_app', compact('deliveries', 'today', 'shift', 'stats', 'user'));
    }

    public function updateDeliveryStatus(Request $request, DailyDelivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:delivered,skipped,failed',
            'cash_collected' => 'nullable|numeric|min:0',
            'failure_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $status = $validated['status'];
        $cash = (float)($validated['cash_collected'] ?? 0);

        $delivery->update([
            'status' => $status,
            'delivered_quantity' => ($status === 'delivered') ? $delivery->quantity : 0,
            'cash_collected' => $cash,
            'failure_reason' => ($status !== 'delivered') ? ($validated['failure_reason'] ?? 'Customer unavailable') : null,
            'notes' => $validated['notes'] ?? null,
            'delivered_at' => ($status === 'delivered') ? Carbon::now() : null,
        ]);

        // If delivered and cash collected or ledger updated
        if ($status === 'delivered') {
            $customer = $delivery->customer;
            if ($customer) {
                // If customer is cash on delivery
                if ($cash > 0) {
                    CustomerLedger::create([
                        'customer_id' => $customer->id,
                        'transaction_date' => Carbon::today(),
                        'type' => 'credit',
                        'amount' => $cash,
                        'balance' => $customer->current_balance,
                        'reference_type' => 'delivery_cash',
                        'reference_id' => $delivery->id,
                        'description' => "Cash Collected upon Delivery #{$delivery->id}",
                    ]);
                }
            }
        }

        return back()->with('success', "Delivery marked as " . ucfirst($status) . "!");
    }
}
