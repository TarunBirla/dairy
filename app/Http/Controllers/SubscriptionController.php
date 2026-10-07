<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DeliveryRoute;
use App\Models\AuditLog;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['customer', 'product', 'route']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $subscriptions = $query->latest()->paginate(15)->withQueryString();
        $totalActive = Subscription::where('status', 'active')->count();
        $totalPaused = Subscription::where('status', 'paused')->count();

        return view('subscriptions.index', compact('subscriptions', 'totalActive', 'totalPaused'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $routes = DeliveryRoute::where('status', 'active')->get();
        $selectedCustomerId = $request->customer_id;
        $nextCode = 'SUB-' . (Subscription::max('id') + 101);

        return view('subscriptions.create', compact('customers', 'products', 'routes', 'selectedCustomerId', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subscription_code' => 'required|string|unique:subscriptions,subscription_code',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'quantity' => 'required|numeric|min:0.25',
            'frequency' => 'required|in:daily,alternate_day,custom_days',
            'shift' => 'required|in:morning,evening,both',
            'unit_price' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        if (empty($validated['route_id'])) {
            $validated['route_id'] = $customer->route_id;
        }

        $sub = Subscription::create($validated);
        AuditLog::log('Created Subscription', 'Subscription', $sub->id);

        return redirect()->route('subscriptions.index')->with('success', "Subscription {$sub->subscription_code} activated for {$customer->name}!");
    }

    public function togglePause(Request $request, Subscription $subscription)
    {
        if ($subscription->status === 'active') {
            $validated = $request->validate([
                'pause_from' => 'required|date',
                'pause_until' => 'required|date|after_or_equal:pause_from',
            ]);

            $subscription->update([
                'status' => 'paused',
                'pause_from' => $validated['pause_from'],
                'pause_until' => $validated['pause_until'],
            ]);
            $msg = 'Subscription paused for vacation successfully.';
        } else {
            $subscription->update([
                'status' => 'active',
                'pause_from' => null,
                'pause_until' => null,
            ]);
            $msg = 'Subscription resumed successfully.';
        }

        AuditLog::log('Toggled Subscription Status', 'Subscription', $subscription->id, ['status' => $subscription->status]);
        return back()->with('success', $msg);
    }
}
