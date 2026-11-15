@extends('layouts.app')

@section('title', 'Add Customer')
@section('breadcrumb', 'Add Customer')
@section('header_title', 'Dashboard / Customers / Add')

@section('content')
<div x-data="{ 
    activeTab: 'profile',
    selectedRoute: '{{ old('route_id', '') }}',
    stopDeliveryOnMinBalance: {{ old('stop_delivery_on_min_balance', 0) ? 'true' : 'false' }},
    billingPeriod: '{{ old('billing_period', 'monthly') }}',
    accountType: '{{ old('account_type', 'postpaid') }}',
    subStopOnDate: {{ old('sub_stop_on_date', 0) ? 'true' : 'false' }},
    subFrequency: '{{ old('sub_frequency', 'daily') }}',
    subWeekDays: {{ json_encode(old('sub_custom_days', ['mon', 'tue', 'wed', 'thu', 'fri'])) }},
    toggleWeekDay(day) {
        if (this.subWeekDays.includes(day)) {
            if (this.subWeekDays.length > 1) {
                this.subWeekDays = this.subWeekDays.filter(d => d !== day);
            }
        } else {
            this.subWeekDays.push(day);
        }
    },
    bottleCounts: {
        @foreach($allProducts as $p)
            '{{ $p->id }}': {{ old('bottles.'.$p->id, 0) }},
        @endforeach
    }
}" class="max-w-6xl mx-auto space-y-6">

    <!-- Page Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Add customer</h2>
            <p class="text-xs text-slate-500 mt-1">Configure customer profile, billing details, schedule deliveries, special rates, empty bottles, groups, or delivery person.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">
            &larr; Back to Customers
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
            <h4 class="text-xs font-bold text-rose-800 mb-1">Please fix the following errors:</h4>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('customers.store') }}" method="POST" id="createCustomerForm">
        @csrf

        <!-- Main Grid: Left Sidebar Navigation + Right Content Card -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Side Navigation Tabs (Matching Edit Screen Exactly) -->
            <div class="lg:col-span-4 bg-white p-3 rounded-3xl border border-slate-200/80 shadow-xs space-y-1.5">
                
                <!-- 1. Customer Profile -->
                <button type="button" @click="activeTab = 'profile'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'profile' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'profile' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'profile' ? 'text-emerald-950 font-black' : 'text-slate-800'">Customer Profile</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Basic detail of the customer</p>
                    </div>
                </button>

                <!-- 2. Customer Billing Details -->
                <button type="button" @click="activeTab = 'billing'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'billing' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'billing' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'billing' ? 'text-emerald-950 font-black' : 'text-slate-800'">Customer Billing Details</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Billing Period, Due Balance, Min Bala...</p>
                    </div>
                </button>

                <!-- 3. Schedule Product Delivery -->
                <button type="button" @click="activeTab = 'schedule'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'schedule' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'schedule' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'schedule' ? 'text-emerald-950 font-black' : 'text-slate-800'">Schedule Product Delivery</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Subscription of the products</p>
                    </div>
                </button>

                <!-- 4. New Rate of the Products (Special Rate) -->
                <button type="button" @click="activeTab = 'special_rate'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'special_rate' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'special_rate' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="percent" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'special_rate' ? 'text-emerald-950 font-black' : 'text-slate-800'">New Rate of the Products (Special Rate)</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Change the price of products to the ...</p>
                    </div>
                </button>

                <!-- 5. Opening Balance Bottle -->
                <button type="button" @click="activeTab = 'bottles'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'bottles' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'bottles' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="wine" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'bottles' ? 'text-emerald-950 font-black' : 'text-slate-800'">Opening Balance Bottle</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Set the number of empty bottle held ...</p>
                    </div>
                </button>

                <!-- 6. Groups -->
                <button type="button" @click="activeTab = 'groups'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'groups' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'groups' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'groups' ? 'text-emerald-950 font-black' : 'text-slate-800'">Groups</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Assign Customer to Groups.</p>
                    </div>
                </button>

                <!-- 7. Delivery Person -->
                <button type="button" @click="activeTab = 'delivery_person'" 
                    class="w-full text-left p-3.5 rounded-2xl transition flex items-start gap-3.5"
                    :class="activeTab === 'delivery_person' ? 'bg-emerald-50/60 border border-emerald-300/80 shadow-xs' : 'hover:bg-slate-50 border border-transparent'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                        :class="activeTab === 'delivery_person' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">
                        <i data-lucide="bike" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold" :class="activeTab === 'delivery_person' ? 'text-emerald-950 font-black' : 'text-slate-800'">Delivery Person</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">Assign delivery person</p>
                    </div>
                </button>

            </div>

            <!-- Right Side Dynamic Content (Tabs) -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs min-h-[480px]">

                <!-- TAB 1: PROFILE -->
                <div x-show="activeTab === 'profile'" x-cloak class="space-y-4">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Customer Profile</h3>
                        <p class="text-xs text-slate-500">Basic personal information, address, and status.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Sunil Mehta / Dr. Ananya Roy" value="{{ old('name') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Code *</label>
                            <input type="text" name="customer_code" required value="{{ old('customer_code', $nextCode) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-700 font-mono font-bold focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9827011111" value="{{ old('phone') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="customer@example.com" value="{{ old('email') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                <option value="household" {{ old('category') === 'household' ? 'selected' : '' }}>Household</option>
                                <option value="shop" {{ old('category') === 'shop' ? 'selected' : '' }}>Retail / Sweet Shop</option>
                                <option value="hotel" {{ old('category') === 'hotel' ? 'selected' : '' }}>Hotel / Restaurant</option>
                                <option value="institution" {{ old('category') === 'institution' ? 'selected' : '' }}>Institution</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Full Address *</label>
                        <input type="text" name="address" required placeholder="e.g. Flat 102, Royal Residency, Scheme 54" value="{{ old('address') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Locality / Landmark</label>
                            <input type="text" name="locality" placeholder="e.g. Scheme 54 / Vijay Nagar" value="{{ old('locality') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Instructions</label>
                            <input type="text" name="delivery_instructions" placeholder="e.g. Ring bell once, place in pouch" value="{{ old('delivery_instructions') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Cancel</a>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'billing'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Billing</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: BILLING DETAILS (Matching Simple Dairy Image 1) -->
                <div x-show="activeTab === 'billing'" x-cloak class="space-y-6">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-base font-bold text-slate-900">Customer Billing Details</h3>
                        <p class="text-xs text-slate-500">Billing Period, Due Balance, Min Balance</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">BILLING CYCLE</span>
                            <h4 class="text-sm font-bold text-slate-800">When and how to invoice</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Pick the billing period and whether this customer pays before or after consumption.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Billing period *</label>
                                <select name="billing_period" x-model="billingPeriod" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="monthly">Monthly(01-ME)</option>
                                    <option value="twice_a_month">Twice in a month(01-15, 16-ME)</option>
                                    <option value="daily">Daily (Every day)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Account type *</label>
                                <select name="account_type" x-model="accountType" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="postpaid">Postpaid</option>
                                    <option value="prepaid">Prepaid</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <!-- Left: Opening / Due Balance -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">DUE / OPENING BALANCE</label>
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</span>
                                    <input type="number" step="10" name="opening_balance" value="{{ old('opening_balance', 0) }}" placeholder="0" class="w-full pl-8 pr-3 py-2 text-base font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1.5">Once the first invoice is generated, this can't be changed.</p>
                            </div>

                            <!-- Right: Stop delivery toggle & minimum balance -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col justify-between">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-800 leading-snug">Stop delivery when minimum balance is exhausted</h5>
                                        <p class="text-[11px] text-slate-400 mt-1">When the customer's balance crosses the limit below, deliveries will pause automatically.</p>
                                    </div>
                                    <!-- Toggle Switch -->
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                                        <input type="checkbox" name="stop_delivery_on_min_balance" value="1" x-model="stopDeliveryOnMinBalance" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    </label>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-200/60" x-show="stopDeliveryOnMinBalance" x-cloak>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">MINIMUM BALANCE LIMIT (₹)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 font-bold text-xs">₹</span>
                                        <input type="number" step="50" name="credit_limit" value="{{ old('credit_limit', 1500) }}" placeholder="1500" class="w-full pl-8 pr-3 py-1.5 text-xs font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Deliveries will pause once customer dues cross this limit.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'profile'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'schedule'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Schedule</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: SCHEDULE PRODUCT DELIVERY (Matching Simple Dairy Images 3 & 4) -->
                <div x-show="activeTab === 'schedule'" x-cloak class="space-y-6">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-base font-bold text-slate-900">Schedule Product Delivery</h3>
                        <p class="text-xs text-slate-500">Subscription of the products</p>
                    </div>

                    <!-- Simple Dairy Styled Schedule Box -->
                    <div class="border border-slate-200/90 rounded-3xl p-6 bg-slate-50/40 space-y-5">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">DELIVERIES</span>
                            <h4 class="text-base font-black text-slate-900">Schedule a product</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Pick a product, the shift, quantity and when deliveries should start and repeat.</p>
                        </div>

                        <!-- Product, Quantity, Shift Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product *</label>
                                <select name="sub_product_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="">-- No initial schedule (Add later) --</option>
                                    @foreach($allProducts as $p)
                                        <option value="{{ $p->id }}" {{ old('sub_product_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} (₹{{ number_format($p->subscription_price ?? $p->price, 2) }}/{{ $p->unit ?? 'L' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Daily Quantity (Ltr / Qty) *</label>
                                <input type="number" step="0.25" min="0" name="sub_quantity" value="{{ old('sub_quantity', 1) }}" placeholder="1.0" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift *</label>
                                <select name="sub_shift" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="morning">Morning (सुबह)</option>
                                    <option value="evening">Evening (शाम)</option>
                                    <option value="both">Both (दोनों समय)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dates Row matching Simple Dairy (Start Date, End Date, Toggle) -->
                        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Start date *</label>
                                    <input type="date" name="sub_start_date" value="{{ old('sub_start_date', date('Y-m-d')) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1" :class="!subStopOnDate ? 'text-slate-400' : 'text-slate-700'">End date</label>
                                    <input type="date" name="sub_end_date" :disabled="!subStopOnDate" value="{{ old('sub_end_date') }}" placeholder="DD/MM/YYYY" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-medium" :class="!subStopOnDate ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800'">
                                </div>
                            </div>

                            <!-- Stop delivery on a specific date toggle switch matching Simple Dairy -->
                            <div class="flex items-center justify-between pt-1">
                                <div>
                                    <h5 class="text-xs font-bold text-slate-800">Stop delivery on a specific date</h5>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Enable this to pick an end date for the schedule.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="sub_stop_on_date" value="1" x-model="subStopOnDate" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Repeat Selector matching Simple Dairy Images 3 & 4 -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Repeat *</label>
                                <select name="sub_frequency" x-model="subFrequency" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="daily">Daily</option>
                                    <option value="alternate_day">Alternate Days</option>
                                    <option value="every_3rd_day">Every 3rd Days</option>
                                    <option value="weekdays">Week Days</option>
                                </select>
                            </div>

                            <!-- Choose Week Days Pills Selector matching Simple Dairy Image 3 -->
                            <div x-show="subFrequency === 'weekdays'" x-cloak class="p-4 bg-white rounded-2xl border border-slate-200/80 space-y-2.5">
                                <label class="block text-xs font-bold text-slate-700">Choose week days *</label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <template x-for="day in [
                                        { key: 'sun', label: 'Sun' },
                                        { key: 'mon', label: 'Mon' },
                                        { key: 'tue', label: 'Tue' },
                                        { key: 'wed', label: 'Wed' },
                                        { key: 'thu', label: 'Thu' },
                                        { key: 'fri', label: 'Fri' },
                                        { key: 'sat', label: 'Sat' }
                                    ]" :key="day.key">
                                        <button type="button" 
                                                @click="toggleWeekDay(day.key)"
                                                class="px-4 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                                :class="subWeekDays.includes(day.key) ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-2xs ring-1 ring-emerald-500/20' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'">
                                            <span x-text="day.label"></span>
                                        </button>
                                    </template>
                                </div>
                                <!-- Hidden inputs to submit custom_days array -->
                                <template x-for="d in subWeekDays" :key="d">
                                    <input type="hidden" name="sub_custom_days[]" :value="d">
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'billing'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'special_rate'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Special Rate</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: NEW RATE OF THE PRODUCTS (SPECIAL RATE) -->
                <div x-show="activeTab === 'special_rate'" x-cloak class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">New Rate of the Products (Special Rate)</h3>
                        <p class="text-xs text-slate-500">Change the price of products to the customer (Special Rate)</p>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">PRICING</span>
                        <h4 class="text-sm font-bold text-slate-800">Special rate for products (Optional)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Override the catalog price for this customer. Useful for bulk buyers or special clients.</p>
                    </div>

                    <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/50 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product</label>
                                <select name="special_product_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                    <option value="">-- No special rate (Use catalog price) --</option>
                                    @foreach($allProducts as $pr)
                                        <option value="{{ $pr->id }}" {{ old('special_product_id') == $pr->id ? 'selected' : '' }}>
                                            {{ $pr->name }} (Catalog: ₹{{ number_format($pr->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Special Price (₹)</label>
                                <input type="number" step="0.5" name="special_price" value="{{ old('special_price') }}" placeholder="e.g. 52.00" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl font-bold text-emerald-700 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'schedule'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'bottles'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Bottles</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: OPENING BALANCE BOTTLE -->
                <div x-show="activeTab === 'bottles'" x-cloak class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Opening Balance Bottle</h3>
                        <p class="text-xs text-slate-500">Set the number of empty bottle held by the customer (you can set only once)</p>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">OPENING BALANCE</span>
                        <h4 class="text-sm font-bold text-slate-800">Empty bottles to collect</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Set the initial empty bottles to be collected back from this customer.</p>
                    </div>

                    <div class="border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden">
                        @foreach($allProducts->take(6) as $p)
                            <div class="p-4 flex items-center justify-between bg-white hover:bg-slate-50/50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <i data-lucide="wine" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900">{{ $p->name }}</h5>
                                        <span class="text-[10px] text-slate-400">Locked after first dispatch</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="if (bottleCounts['{{ $p->id }}'] > 0) bottleCounts['{{ $p->id }}']--" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold">&minus;</button>
                                    <input type="number" :name="'bottles[{{ $p->id }}]'" x-model="bottleCounts['{{ $p->id }}']" class="w-14 text-center py-1 text-xs border border-slate-200 rounded-lg font-bold">
                                    <button type="button" @click="bottleCounts['{{ $p->id }}']++" class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold">&plus;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'special_rate'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'groups'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Groups</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 6: GROUPS -->
                <div x-show="activeTab === 'groups'" x-cloak class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Groups</h3>
                        <p class="text-xs text-slate-500">Assign Customer to Groups.</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">GROUPS</span>
                            <h4 class="text-sm font-bold text-slate-800">Assign customer to groups</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Groups are useful for bulk operations like sending an SMS or applying a rate change. You can pick more than one.</p>
                        </div>
                        <a href="{{ route('customers.groups.index') }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:underline shrink-0">
                            + Manage Groups
                        </a>
                    </div>

                    <div class="border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden">
                        @forelse($allGroups as $grp)
                            <label class="p-4 flex items-center gap-3 bg-white hover:bg-slate-50/50 cursor-pointer transition">
                                <input type="checkbox" name="group_ids[]" value="{{ $grp->id }}" 
                                    {{ in_array($grp->id, old('group_ids', [])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-emerald-700 bg-emerald-50">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800">{{ $grp->name }}</span>
                            </label>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs">
                                No groups created yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'bottles'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="activeTab = 'delivery_person'" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                                <span>Next: Delivery Person</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: DELIVERY PERSON -->
                <div x-show="activeTab === 'delivery_person'" x-cloak class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 mb-4">
                        <h3 class="text-sm font-bold text-slate-900">Delivery Person</h3>
                        <p class="text-xs text-slate-500">Assign delivery person</p>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">TEAM</span>
                        <h4 class="text-sm font-bold text-slate-800">Assign delivery persons</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Select the delivery person or route responsible for this customer.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="p-4 rounded-2xl border-2 flex items-center justify-between cursor-pointer transition"
                            :class="selectedRoute === '' ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 bg-white'">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="route_id" value="" x-model="selectedRoute" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <h5 class="text-xs font-bold text-slate-900">No specific route (Assign later)</h5>
                                    <p class="text-[11px] text-slate-400">Can be assigned anytime from customer details or routes</p>
                                </div>
                            </div>
                            <span x-show="selectedRoute === ''" class="text-xs font-bold text-emerald-700">Selected</span>
                        </label>

                        @foreach($routes as $rt)
                            <label class="p-4 rounded-2xl border-2 flex items-center justify-between cursor-pointer transition"
                                :class="selectedRoute == '{{ $rt->id }}' ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 bg-white'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="route_id" value="{{ $rt->id }}" x-model="selectedRoute" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">
                                        {{ mb_substr($rt->deliveryBoy ? $rt->deliveryBoy->name : $rt->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900">{{ $rt->deliveryBoy ? $rt->deliveryBoy->name : 'Route: ' . $rt->name }}</h5>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $rt->deliveryBoy->phone ?? 'N/A' }} &bull; Route: {{ $rt->name }} ({{ $rt->area_name }})</p>
                                    </div>
                                </div>
                                <span x-show="selectedRoute == '{{ $rt->id }}'" class="text-xs font-bold text-emerald-700">Selected</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="activeTab = 'groups'" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            <span>Previous</span>
                        </button>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save & Create Customer</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>
@endsection
