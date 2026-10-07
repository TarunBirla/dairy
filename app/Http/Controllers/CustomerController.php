<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryRoute;
use App\Models\Branch;
use App\Models\BottleTracking;
use App\Models\CustomerLedger;
use App\Models\AuditLog;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['route', 'branch']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $totalDues = Customer::sum('current_balance');
        $routes = DeliveryRoute::where('status', 'active')->get();

        return view('customers.index', compact('customers', 'totalCustomers', 'activeCustomers', 'totalDues', 'routes'));
    }

    public function create()
    {
        $routes = DeliveryRoute::where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        $nextCode = 'CUST-' . (Customer::max('id') + 201);

        return view('customers.create', compact('routes', 'branches', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'locality' => 'nullable|string',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'category' => 'required|in:household,retail,hotel,shop,institution',
            'credit_limit' => 'nullable|numeric|min:0',
            'delivery_instructions' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        // Create default address
        CustomerAddress::create([
            'customer_id' => $customer->id,
            'address_type' => 'home',
            'address_line' => $customer->address,
            'landmark' => $customer->locality,
            'is_default' => true,
        ]);

        // Init bottle tracking
        BottleTracking::create([
            'customer_id' => $customer->id,
            'issued_count' => 0,
            'returned_count' => 0,
            'balance_bottles' => 0,
        ]);

        AuditLog::log('Created Customer', 'Customer', $customer->id);

        return redirect()->route('customers.show', $customer)->with('success', "Customer {$customer->name} added successfully!");
    }

    public function show(Customer $customer)
    {
        $customer->load(['route', 'subscriptions.product', 'dailyDeliveries.product', 'invoices', 'payments', 'ledgers', 'bottleTracking']);
        $recentDeliveries = $customer->dailyDeliveries()->latest()->take(15)->get();
        $ledgers = CustomerLedger::where('customer_id', $customer->id)->latest()->take(25)->get();

        return view('customers.show', compact('customer', 'recentDeliveries', 'ledgers'));
    }

    public function edit(Customer $customer)
    {
        $customer->load(['route', 'groups', 'specialRates.product', 'bottleOpenings.product', 'subscriptions.product']);
        $routes = DeliveryRoute::where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        $allGroups = \App\Models\CustomerGroup::all();
        $allProducts = \App\Models\Product::where('status', 'active')->get();
        $deliveryBoys = \App\Models\User::where('role', \App\Models\User::ROLE_DELIVERY_BOY)->get();

        return view('customers.edit', compact(
            'customer',
            'routes',
            'branches',
            'allGroups',
            'allProducts',
            'deliveryBoys'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $tab = $request->get('active_tab', 'profile');

        if ($tab === 'profile') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email',
                'address' => 'required|string',
                'locality' => 'nullable|string',
                'route_id' => 'nullable|exists:delivery_routes,id',
                'category' => 'required|in:household,retail,hotel,shop,institution',
                'delivery_instructions' => 'nullable|string',
                'status' => 'required|in:active,paused,inactive,blocked',
            ]);
            $customer->update($validated);
        } elseif ($tab === 'billing') {
            $validated = $request->validate([
                'credit_limit' => 'nullable|numeric|min:0',
                'billing_period' => 'nullable|string',
            ]);
            $customer->update([
                'credit_limit' => $validated['credit_limit'] ?? 0,
            ]);
        } elseif ($tab === 'groups') {
            $groupIds = $request->input('group_ids', []);
            $customer->groups()->sync($groupIds);
        } elseif ($tab === 'delivery_person') {
            if ($request->filled('route_id')) {
                $customer->update(['route_id' => $request->route_id]);
            }
        } elseif ($tab === 'special_rate') {
            if ($request->filled('product_id') && $request->filled('special_price')) {
                \App\Models\CustomerSpecialRate::updateOrCreate(
                    ['customer_id' => $customer->id, 'product_id' => $request->product_id],
                    ['special_price' => $request->special_price, 'is_active' => true]
                );
            }
        } elseif ($tab === 'bottles') {
            $bottles = $request->input('bottles', []);
            foreach ($bottles as $prodId => $count) {
                \App\Models\CustomerBottleOpening::updateOrCreate(
                    ['customer_id' => $customer->id, 'product_id' => $prodId],
                    ['opening_count' => (int)$count, 'is_locked' => true]
                );
            }
        }

        AuditLog::log("Updated Customer Section: {$tab}", 'Customer', $customer->id);

        return redirect()->route('customers.edit', ['customer' => $customer, 'tab' => $tab])
            ->with('success', 'Changes saved successfully.');
    }
}
