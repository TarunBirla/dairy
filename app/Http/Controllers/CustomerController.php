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
        $branches = Branch::where('status', 'active')->get();
        $nextCode = 'CUST-' . (Customer::max('id') + 201);

        return view('customers.index', compact('customers', 'totalCustomers', 'activeCustomers', 'totalDues', 'routes', 'branches', 'nextCode'));
    }

    private function ensureSchemaMigrated()
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
                \Illuminate\Support\Facades\Schema::table('customers', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customers', 'billing_period')) {
                        $table->string('billing_period', 50)->default('monthly')->after('category');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customers', 'account_type')) {
                        $table->string('account_type', 50)->default('postpaid')->after('billing_period');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customers', 'stop_delivery_on_min_balance')) {
                        $table->boolean('stop_delivery_on_min_balance')->default(false)->after('credit_limit');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customers', 'min_balance')) {
                        $table->decimal('min_balance', 10, 2)->default(0.00)->after('stop_delivery_on_min_balance');
                    }
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('subscriptions')) {
                \Illuminate\Support\Facades\Schema::table('subscriptions', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('subscriptions', 'custom_days')) {
                        $table->json('custom_days')->nullable()->after('frequency');
                    }
                });
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('customer_special_rates')) {
                \Illuminate\Support\Facades\Schema::table('customer_special_rates', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customer_special_rates', 'rate_type')) {
                        $table->string('rate_type', 20)->default('fixed')->after('special_price');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('customer_special_rates', 'delta_amount')) {
                        $table->decimal('delta_amount', 10, 2)->default(0.00)->after('rate_type');
                    }
                });
            }
        } catch (\Throwable $e) {
            // Ignore if columns exist or database driver handles it
        }
    }

    public function create()
    {
        $this->ensureSchemaMigrated();
        $routes = DeliveryRoute::with('deliveryBoy')->where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        $nextCode = 'CUST-' . (Customer::max('id') + 201);
        $allGroups = \App\Models\CustomerGroup::all();
        $allProducts = \App\Models\Product::where('status', 'active')->get();
        $bottleProducts = $allProducts->filter(fn($p) => $p->isBottleProduct());
        $deliveryBoys = \App\Models\User::where('role', \App\Models\User::ROLE_DELIVERY_BOY)->get();

        return view('customers.create', compact(
            'routes',
            'branches',
            'nextCode',
            'allGroups',
            'allProducts',
            'bottleProducts',
            'deliveryBoys'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureSchemaMigrated();
        $validated = $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'locality' => 'nullable|string',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'category' => 'required|in:household,retail,hotel,shop,institution',
            'billing_period' => 'nullable|string',
            'account_type' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric',
            'min_balance' => 'nullable|numeric',
            'stop_delivery_on_min_balance' => 'nullable|boolean',
            'delivery_instructions' => 'nullable|string',
            'status' => 'nullable|in:active,paused,inactive,blocked',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['billing_period'] = $request->input('billing_period', 'monthly');
        $validated['account_type'] = $request->input('account_type', 'postpaid');
        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['min_balance'] = $request->input('min_balance', 0) ?? 0;
        $validated['stop_delivery_on_min_balance'] = $request->boolean('stop_delivery_on_min_balance');
        $validated['current_balance'] = $request->input('opening_balance', 0) ?? 0;

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

        // Opening Balance in Ledger if any
        if (!empty($validated['current_balance']) && (float)$validated['current_balance'] != 0) {
            CustomerLedger::create([
                'customer_id' => $customer->id,
                'transaction_type' => 'opening_balance',
                'amount' => abs($validated['current_balance']),
                'balance' => $validated['current_balance'],
                'description' => 'Opening balance entry',
                'date' => now()->toDateString(),
            ]);
        }

        // Groups sync
        if ($request->has('group_ids')) {
            $customer->groups()->sync($request->input('group_ids', []));
        }

        // Special rate
        if ($request->filled('special_product_id')) {
            $spProduct = \App\Models\Product::find($request->special_product_id);
            if ($spProduct) {
                $rateType = $request->input('special_rate_type', 'variable');
                $delta = (float)$request->input('special_delta_amount', 0);
                $fixedPrice = (float)$request->input('special_price', $spProduct->price);
                $specialPrice = ($rateType === 'variable') ? max(0, $spProduct->price + $delta) : $fixedPrice;

                \App\Models\CustomerSpecialRate::create([
                    'customer_id' => $customer->id,
                    'product_id' => $spProduct->id,
                    'rate_type' => $rateType,
                    'delta_amount' => $delta,
                    'special_price' => $specialPrice,
                    'is_active' => true,
                ]);
            }
        }

        // Opening bottle counts
        if ($request->has('bottles')) {
            foreach ($request->input('bottles', []) as $prodId => $count) {
                if ((int)$count > 0) {
                    \App\Models\CustomerBottleOpening::create([
                        'customer_id' => $customer->id,
                        'product_id' => $prodId,
                        'opening_count' => (int)$count,
                        'is_locked' => true,
                    ]);
                }
            }
        }

        // Schedule Product Delivery (Multiple Products Subscriptions)
        if ($request->has('schedules') && is_array($request->schedules)) {
            foreach ($request->schedules as $sched) {
                if (!empty($sched['product_id']) && !empty($sched['quantity']) && (float)$sched['quantity'] > 0) {
                    $prod = \App\Models\Product::find($sched['product_id']);
                    if ($prod) {
                        $subCode = 'SUB-' . strtoupper(uniqid());
                        $stopOnDate = !empty($sched['stop_on_date']);
                        $endDate = ($stopOnDate && !empty($sched['end_date'])) ? $sched['end_date'] : null;
                        $frequency = $sched['frequency'] ?? 'daily';
                        $customDays = in_array($frequency, ['weekdays', 'custom_days']) ? ($sched['custom_days'] ?? ['mon','tue','wed','thu','fri']) : null;
                        $shift = $sched['shift'] ?? 'morning';
                        $unitPrice = !empty($sched['unit_price']) ? (float)$sched['unit_price'] : ($prod->subscription_price ?? $prod->price);

                        \App\Models\Subscription::create([
                            'subscription_code' => $subCode,
                            'customer_id' => $customer->id,
                            'product_id' => $prod->id,
                            'route_id' => $customer->route_id,
                            'quantity' => $sched['quantity'],
                            'frequency' => $frequency,
                            'custom_days' => $customDays,
                            'shift' => $shift,
                            'unit_price' => $unitPrice,
                            'start_date' => $sched['start_date'] ?? now()->toDateString(),
                            'end_date' => $endDate,
                            'status' => 'active',
                        ]);
                    }
                }
            }
        } elseif ($request->filled('sub_product_id') && $request->filled('sub_quantity') && (float)$request->sub_quantity > 0) {
            // Single schedule fallback
            $prod = \App\Models\Product::find($request->sub_product_id);
            if ($prod) {
                $subCode = 'SUB-' . strtoupper(uniqid());
                $stopOnDate = $request->boolean('sub_stop_on_date');
                $endDate = ($stopOnDate && $request->filled('sub_end_date')) ? $request->input('sub_end_date') : null;
                $frequency = $request->input('sub_frequency', 'daily');
                $customDays = in_array($frequency, ['weekdays', 'custom_days']) ? $request->input('sub_custom_days', ['mon','tue','wed','thu','fri']) : null;

                \App\Models\Subscription::create([
                    'subscription_code' => $subCode,
                    'customer_id' => $customer->id,
                    'product_id' => $prod->id,
                    'route_id' => $customer->route_id,
                    'quantity' => $request->sub_quantity,
                    'frequency' => $frequency,
                    'custom_days' => $customDays,
                    'shift' => $request->input('sub_shift', 'morning'),
                    'unit_price' => $prod->subscription_price ?? $prod->price,
                    'start_date' => $request->input('sub_start_date', now()->toDateString()),
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);
            }
        }

        AuditLog::log('Created Customer', 'Customer', $customer->id);

        return redirect()->route('customers.index')->with('success', "Customer {$customer->name} ({$customer->customer_code}) registered successfully!");
    }

    public function show(Customer $customer)
    {
        $customer->load(['route', 'subscriptions.product', 'dailyDeliveries.product', 'invoices', 'payments', 'ledgers', 'bottleTracking', 'vacations']);
        $recentDeliveries = $customer->dailyDeliveries()->latest()->take(15)->get();
        $ledgers = CustomerLedger::where('customer_id', $customer->id)->latest()->take(25)->get();

        return view('customers.show', compact('customer', 'recentDeliveries', 'ledgers'));
    }

    public function edit(Customer $customer)
    {
        $this->ensureSchemaMigrated();
        $customer->load(['route', 'groups', 'specialRates.product', 'bottleOpenings.product', 'subscriptions.product']);
        $routes = DeliveryRoute::where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->get();
        $allGroups = \App\Models\CustomerGroup::all();
        $allProducts = \App\Models\Product::where('status', 'active')->get();
        $bottleProducts = $allProducts->filter(function($p) use ($customer) {
            return $p->isBottleProduct() || $customer->bottleOpenings->pluck('product_id')->contains($p->id);
        });
        $deliveryBoys = \App\Models\User::where('role', \App\Models\User::ROLE_DELIVERY_BOY)->get();

        return view('customers.edit', compact(
            'customer',
            'routes',
            'branches',
            'allGroups',
            'allProducts',
            'bottleProducts',
            'deliveryBoys'
        ));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->ensureSchemaMigrated();
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
                'account_type' => 'nullable|string',
                'min_balance' => 'nullable|numeric',
                'stop_delivery_on_min_balance' => 'nullable|boolean',
            ]);
            $customer->update([
                'credit_limit' => $validated['credit_limit'] ?? 0,
                'billing_period' => $request->input('billing_period', 'monthly'),
                'account_type' => $request->input('account_type', 'postpaid'),
                'min_balance' => $request->input('min_balance', 0) ?? 0,
                'stop_delivery_on_min_balance' => $request->boolean('stop_delivery_on_min_balance'),
            ]);
        } elseif ($tab === 'schedule') {
            if ($request->filled('sub_product_id') && $request->filled('sub_quantity') && (float)$request->sub_quantity > 0) {
                $prod = \App\Models\Product::find($request->sub_product_id);
                if ($prod) {
                    $subCode = 'SUB-' . strtoupper(uniqid());
                    $stopOnDate = $request->boolean('sub_stop_on_date');
                    $endDate = ($stopOnDate && $request->filled('sub_end_date')) ? $request->input('sub_end_date') : null;
                    $frequency = $request->input('sub_frequency', 'daily');
                    $customDays = in_array($frequency, ['weekdays', 'custom_days']) ? $request->input('sub_custom_days', ['mon','tue','wed','thu','fri']) : null;

                    \App\Models\Subscription::create([
                        'subscription_code' => $subCode,
                        'customer_id' => $customer->id,
                        'product_id' => $prod->id,
                        'route_id' => $customer->route_id,
                        'quantity' => $request->sub_quantity,
                        'frequency' => $frequency,
                        'custom_days' => $customDays,
                        'shift' => $request->input('sub_shift', 'morning'),
                        'unit_price' => $prod->subscription_price ?? $prod->price,
                        'start_date' => $request->input('sub_start_date', now()->toDateString()),
                        'end_date' => $endDate,
                        'status' => 'active',
                    ]);
                }
            }
        } elseif ($tab === 'groups') {
            $groupIds = $request->input('group_ids', []);
            $customer->groups()->sync($groupIds);
        } elseif ($tab === 'delivery_person') {
            if ($request->filled('route_id')) {
                $customer->update(['route_id' => $request->route_id]);
            }
        } elseif ($tab === 'special_rate') {
            if ($request->filled('product_id')) {
                $prod = \App\Models\Product::findOrFail($request->product_id);
                $rateType = $request->input('rate_type', 'variable');
                $delta = (float)$request->input('delta_amount', 0);
                $fixedPrice = (float)$request->input('special_price', $prod->price);
                $specialPrice = ($rateType === 'variable') ? max(0, $prod->price + $delta) : $fixedPrice;

                \App\Models\CustomerSpecialRate::updateOrCreate(
                    ['customer_id' => $customer->id, 'product_id' => $prod->id],
                    [
                        'rate_type' => $rateType,
                        'delta_amount' => $delta,
                        'special_price' => $specialPrice,
                        'is_active' => true,
                    ]
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

        if ($request->has('from_index') && $request->from_index) {
            return redirect()->route('customers.index')->with('success', "Customer {$customer->name} updated successfully.");
        }

        return redirect()->route('customers.edit', ['customer' => $customer, 'tab' => $tab])
            ->with('success', 'Changes saved successfully.');
    }

    public function deleteSpecialRate(Customer $customer, \App\Models\CustomerSpecialRate $specialRate)
    {
        if ($specialRate->customer_id === $customer->id) {
            $specialRate->delete();
            AuditLog::log("Deleted Special Rate for Product #{$specialRate->product_id}", 'Customer', $customer->id);
        }

        return redirect()->route('customers.edit', ['customer' => $customer, 'tab' => 'special_rate'])
            ->with('success', 'Special rate removed successfully.');
    }
}
