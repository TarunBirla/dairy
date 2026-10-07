@extends('layouts.app')

@section('title', 'Customers Directory')
@section('breadcrumb', 'Customers')
@section('header_title', 'Customer & Client Accounts')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Customer</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="customerManager()" @open-customer-modal.window="openCreateModal()">

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL CUSTOMERS</span>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE SUBSCRIPTION USERS</span>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $activeCustomers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OUTSTANDING DUES</span>
            <p class="text-3xl font-extrabold text-amber-600 mt-2">₹ {{ number_format($totalDues, 2) }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('customers.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Customer</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Name, CUST-xxx, phone, locality..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Categories</option>
                        <option value="household" {{ request('category') === 'household' ? 'selected' : '' }}>Household (Retail)</option>
                        <option value="retail" {{ request('category') === 'retail' ? 'selected' : '' }}>Retail Shop</option>
                        <option value="hotel" {{ request('category') === 'hotel' ? 'selected' : '' }}>Hotel / Restaurant</option>
                        <option value="shop" {{ request('category') === 'shop' ? 'selected' : '' }}>Tea Shop / Vendor</option>
                        <option value="institution" {{ request('category') === 'institution' ? 'selected' : '' }}>Institution</option>
                    </select>
                </div>

                <!-- Route Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Delivery Route</label>
                    <select name="route_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Routes</option>
                        @foreach($routes as $r)
                            <option value="{{ $r->id }}" {{ request('route_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Add Milk Subscription</span>
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('customers.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Customer Accounts Directory</h3>
                <p class="text-xs text-slate-400">Retail clients, milk subscribers, routes, and billing ledger balances</p>
            </div>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Customer</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Locality / Route</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Balance Dues</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6 w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- Code -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $c->customer_code }}
                            </td>

                            <!-- Customer Name -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $c) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition block">
                                    {{ $c->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400 font-mono">{{ $c->phone }}</span>
                            </td>

                            <!-- Locality / Route -->
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $c->locality ?? '—' }}
                                <span class="block text-[10px] text-slate-400">{{ $c->route ? $c->route->name : 'No route' }}</span>
                            </td>

                            <!-- Category -->
                            <td class="py-3.5 px-4 capitalize">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $c->category }}
                                </span>
                            </td>

                            <!-- Balance Dues -->
                            <td class="py-3.5 px-4 font-extrabold text-sm {{ $c->current_balance > 0 ? 'text-amber-600' : 'text-slate-800' }}">
                                ₹ {{ number_format($c->current_balance, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $c->status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('customers.show', $c) }}" title="View Account Profile" class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">
                                        Account
                                    </a>

                                    <button type="button" 
                                            @click="openEditModal({
                                                id: {{ $c->id }},
                                                customer_code: '{{ addslashes($c->customer_code) }}',
                                                name: '{{ addslashes($c->name) }}',
                                                phone: '{{ addslashes($c->phone) }}',
                                                email: '{{ addslashes($c->email ?? '') }}',
                                                address: '{{ addslashes($c->address) }}',
                                                locality: '{{ addslashes($c->locality ?? '') }}',
                                                route_id: {{ $c->route_id ? $c->route_id : 'null' }},
                                                category: '{{ $c->category }}',
                                                credit_limit: {{ (float) ($c->credit_limit ?? 0) }},
                                                delivery_instructions: '{{ addslashes($c->delivery_instructions ?? '') }}',
                                                status: '{{ $c->status }}'
                                            })"
                                            title="Edit Customer" 
                                            class="p-1.5 text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="users" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No customers found.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Add Customer" to register clients.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: ADD / EDIT CUSTOMER                       -->
    <!-- ============================================================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="closeModal()">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="isEditMode ? 'Edit Customer Profile' : 'Register New Customer'"></h3>
                        <p class="text-xs text-slate-400">Capture customer profile, delivery address, routes, and billing limits</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form -->
            <form :action="formAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="from_index" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Customer Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Customer Full Name *
                        </label>
                        <input type="text" name="name" x-model="form.name" required placeholder="e.g. Rahul Sharma"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Customer Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Customer Code *
                        </label>
                        <input type="text" name="customer_code" x-model="form.customer_code" required placeholder="e.g. CUST-201"
                               class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Phone Number *
                        </label>
                        <input type="text" name="phone" x-model="form.phone" required placeholder="e.g. 9876543210"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Email Address
                        </label>
                        <input type="email" name="email" x-model="form.email" placeholder="e.g. client@example.com"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Account Category *
                        </label>
                        <select name="category" x-model="form.category" required
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="household">Household (Home Delivery)</option>
                            <option value="retail">Retail Shop</option>
                            <option value="hotel">Hotel / Restaurant</option>
                            <option value="shop">Tea Stall / Vendor</option>
                            <option value="institution">Institution / Corporate</option>
                        </select>
                    </div>

                    <!-- Delivery Route -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Delivery Route
                        </label>
                        <select name="route_id" x-model="form.route_id"
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">-- No Route Assigned --</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Locality -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Locality / Landmark
                        </label>
                        <input type="text" name="locality" x-model="form.locality" placeholder="e.g. Scheme No 54, Near Mandir"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Credit Limit -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Credit Limit (₹)
                        </label>
                        <input type="number" step="10" name="credit_limit" x-model.number="form.credit_limit" placeholder="0.00"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Account Status
                        </label>
                        <select name="status" x-model="form.status"
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>

                    <!-- Delivery Instructions -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Delivery Instructions
                        </label>
                        <input type="text" name="delivery_instructions" x-model="form.delivery_instructions" placeholder="e.g. Ring bell, leave outside door"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Full Delivery Address -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Full Delivery Address *
                    </label>
                    <textarea name="address" x-model="form.address" rows="2" required placeholder="House/Flat number, street address, city, pincode"
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span x-text="isEditMode ? 'Update Customer' : 'Add Customer'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function customerManager() {
    return {
        showModal: false,
        isEditMode: false,
        formAction: '{{ route("customers.store") }}',

        form: {
            id: null,
            customer_code: '{{ $nextCode }}',
            name: '',
            phone: '',
            email: '',
            address: '',
            locality: '',
            route_id: '',
            category: 'household',
            credit_limit: 0,
            delivery_instructions: '',
            status: 'active'
        },

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('open_create')) {
                this.openCreateModal();
            }
        },

        openCreateModal() {
            this.isEditMode = false;
            this.formAction = '{{ route("customers.store") }}';
            this.resetForm();
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openEditModal(item) {
            this.isEditMode = true;
            this.formAction = `/customers/${item.id}`;
            this.form = { ...item };
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeModal() {
            this.showModal = false;
        },

        resetForm() {
            this.form = {
                id: null,
                customer_code: '{{ $nextCode }}',
                name: '',
                phone: '',
                email: '',
                address: '',
                locality: '',
                route_id: '',
                category: 'household',
                credit_limit: 0,
                delivery_instructions: '',
                status: 'active'
            };
        }
    };
}
</script>
@endsection
