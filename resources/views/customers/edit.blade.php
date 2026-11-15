@extends('layouts.app')

@section('title', 'Edit ' . $customer->name)
@section('breadcrumb', 'Edit Customer')
@section('header_title', 'Dashboard / Customers / Edit')

@section('content')
<div x-data="{ 
    activeTab: '{{ request('tab', 'profile') }}',
    stopDeliveryOnMinBalance: {{ $customer->stop_delivery_on_min_balance ? 'true' : 'false' }},
    billingPeriod: '{{ old('billing_period', $customer->billing_period ?? 'monthly') }}',
    accountType: '{{ old('account_type', $customer->account_type ?? 'postpaid') }}',
    scheduleModalOpen: false,
    subStopOnDate: false,
    subFrequency: 'daily',
    subWeekDays: ['mon', 'tue', 'wed', 'thu', 'fri'],
    toggleWeekDay(day) {
        if (this.subWeekDays.includes(day)) {
            if (this.subWeekDays.length > 1) {
                this.subWeekDays = this.subWeekDays.filter(d => d !== day);
            }
        } else {
            this.subWeekDays.push(day);
        }
    },
    specialRateModal: false,
    newRateProductId: '',
    newRatePrice: '',
    bottleCounts: {
        @foreach($allProducts as $p)
            '{{ $p->id }}': {{ $customer->bottleOpenings->firstWhere('product_id', $p->id)?->opening_count ?? 0 }},
        @endforeach
    }
}" class="max-w-6xl mx-auto space-y-6">

    <!-- Page Title -->
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Edit customer</h2>
        <p class="text-xs text-slate-500 mt-1">Update any section to change profile, billing, deliveries, special rates, or team.</p>
    </div>

    <!-- Main Grid: Left Sidebar Navigation (280px) + Right Content Card -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Side Navigation Tabs (Matching Screenshot) -->
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

        <!-- Right Side Dynamic Content (Matching Exact Layouts in Screenshots) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs min-h-[480px]">

            <!-- TAB 1: PROFILE -->
            <div x-show="activeTab === 'profile'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-sm font-bold text-slate-900">Customer Profile</h3>
                    <p class="text-xs text-slate-500">Basic personal information, address, and status.</p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="profile">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name *</label>
                            <input type="text" name="name" required value="{{ old('name', $customer->name) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Code</label>
                            <input type="text" readonly value="{{ $customer->customer_code }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone *</label>
                            <input type="text" name="phone" required value="{{ old('phone', $customer->phone) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                <option value="household" {{ $customer->category === 'household' ? 'selected' : '' }}>Household</option>
                                <option value="shop" {{ $customer->category === 'shop' ? 'selected' : '' }}>Retail / Sweet Shop</option>
                                <option value="hotel" {{ $customer->category === 'hotel' ? 'selected' : '' }}>Hotel / Restaurant</option>
                                <option value="institution" {{ $customer->category === 'institution' ? 'selected' : '' }}>Institution</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                            <select name="status" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ $customer->status === 'paused' ? 'selected' : '' }}>Paused</option>
                                <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Full Address *</label>
                        <input type="text" name="address" required value="{{ old('address', $customer->address) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Locality / Landmark</label>
                        <input type="text" name="locality" value="{{ old('locality', $customer->locality) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save Profile</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 2: BILLING DETAILS (Matching Simple Dairy Image 1) -->
            <div x-show="activeTab === 'billing'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-base font-bold text-slate-900">Customer Billing Details</h3>
                    <p class="text-xs text-slate-500">Billing Period, Due Balance, Min Balance</p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="billing">

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
                            <!-- Left: Current Due Balance -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">DUE / OUTSTANDING BALANCE</label>
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</span>
                                    <input type="text" readonly value="{{ number_format($customer->current_balance, 2) }}" class="w-full pl-8 pr-3 py-2 text-base font-black border border-slate-200 rounded-xl bg-slate-100/70 text-slate-800 focus:outline-none">
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
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">MINIMUM BALANCE LIMIT / CREDIT LIMIT (₹)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500 font-bold text-xs">₹</span>
                                        <input type="number" step="50" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" placeholder="1500" class="w-full pl-8 pr-3 py-1.5 text-xs font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white">
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Deliveries will pause once customer dues cross this limit.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Update</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 3: SCHEDULE PRODUCT DELIVERY (Matching Simple Dairy Images 3 & 4) -->
            <div x-show="activeTab === 'schedule'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-base font-bold text-slate-900">Schedule Product Delivery</h3>
                    <p class="text-xs text-slate-500">Subscription of the products</p>
                </div>

                <div class="mb-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">DELIVERIES</span>
                    <h4 class="text-sm font-bold text-slate-800">Scheduled product delivery</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Set which products are delivered to this customer and on which days. Customers don't see unscheduled products in their order list.</p>
                </div>

                <div class="mb-5 flex items-center justify-between">
                    <button type="button" @click="scheduleModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                        <span>+ Schedule product</span>
                    </button>
                </div>

                <!-- Active Subscriptions Cards matching Screenshot 5 -->
                <div class="space-y-3">
                    @forelse($customer->subscriptions as $sub)
                        <div class="border-2 border-emerald-500 rounded-2xl p-4 bg-white flex items-center justify-between relative shadow-xs">
                            <div class="flex items-center gap-4">
                                <div class="border-r border-slate-200 pr-4">
                                    <span class="text-2xl font-black text-slate-900">{{ (float)$sub->quantity }}</span>
                                    <span class="text-[10px] font-bold uppercase text-slate-400 block -mt-1">{{ $sub->product->unit ?? 'LITER' }}</span>
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold text-slate-900">{{ $sub->product->name }}</h5>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                        <span class="capitalize flex items-center gap-1">
                                            <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                                            {{ $sub->shift }}
                                        </span>
                                        <span>&bull;</span>
                                        <span class="capitalize">{{ str_replace('_', ' ', $sub->frequency) }}</span>
                                        @if(in_array($sub->frequency, ['weekdays', 'custom_days']) && !empty($sub->custom_days))
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded uppercase">
                                                {{ implode(', ', (array)$sub->custom_days) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        <span class="font-bold text-emerald-600 uppercase">{{ $sub->status }}</span>
                                        <span>{{ $sub->start_date ? $sub->start_date->format('d M Y') : '' }} &rarr; {{ $sub->end_date ? $sub->end_date->format('d M Y') : 'No end date' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            No scheduled products active for this customer yet. Click "+ Schedule product" to schedule daily milk.
                        </div>
                    @endforelse
                </div>

                <div class="pt-8 mt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                    <div class="flex gap-2">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                        <button type="button" @click="scheduleModalOpen = true" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Schedule product</button>
                    </div>
                </div>
            </div>

            <!-- TAB 4: NEW RATE OF THE PRODUCTS (SPECIAL RATE) (Matching Screenshot 4) -->
            <div x-show="activeTab === 'special_rate'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-sm font-bold text-slate-900">New Rate of the Products (Special Rate)</h3>
                    <p class="text-xs text-slate-500">Change the price of products to the customer (Special Rate)</p>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">PRICING</span>
                        <h4 class="text-sm font-bold text-slate-800">Special rate for products</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Override the catalog price on a per-product basis just for this customer. Useful for VIPs or bulk buyers.</p>
                    </div>
                    <button type="button" @click="specialRateModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add special rate</span>
                    </button>
                </div>

                <!-- Special Rates List or Empty State -->
                @if($customer->specialRates->count() > 0)
                    <div class="space-y-3">
                        @foreach($customer->specialRates as $sr)
                            <div class="p-4 rounded-2xl border border-slate-200 flex items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-bold text-slate-900">{{ $sr->product->name }}</h5>
                                    <p class="text-xs text-slate-500">Catalog Price: ₹ {{ number_format($sr->product->price, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-black text-emerald-600">₹ {{ number_format($sr->special_price, 2) }}</span>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Special Rate</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="tag" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">No special rates yet</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">This customer is on catalog prices. Add a special rate to override the price of a specific product.</p>
                        <div class="mt-4">
                            <button type="button" @click="specialRateModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                + Add special rate
                            </button>
                        </div>
                    </div>
                @endif

                <div class="pt-8 mt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                    <a href="{{ route('customers.index') }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Customers list</a>
                </div>
            </div>

            <!-- TAB 5: OPENING BALANCE BOTTLE (Matching Screenshot 3) -->
            <div x-show="activeTab === 'bottles'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-sm font-bold text-slate-900">Opening Balance Bottle</h3>
                    <p class="text-xs text-slate-500">Set the number of empty bottle held by the customer (you can set only once)</p>
                </div>

                <div class="mb-5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">OPENING BALANCE</span>
                    <h4 class="text-sm font-bold text-slate-800">Empty bottles to collect</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Set the initial empty bottles to be collected back from this customer. You can set this only once per product.</p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="bottles">

                    <div class="border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden">
                        @foreach($allProducts->take(5) as $p)
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
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 6: GROUPS (Matching Screenshot 2) -->
            <div x-show="activeTab === 'groups'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-sm font-bold text-slate-900">Groups</h3>
                    <p class="text-xs text-slate-500">Assign Customer to Groups.</p>
                </div>

                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">GROUPS</span>
                        <h4 class="text-sm font-bold text-slate-800">Assign customer to groups</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Groups are useful for bulk operations like sending an SMS or applying a rate change. You can pick more than one.</p>
                    </div>
                    <a href="{{ route('customers.groups.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                        + Manage Groups
                    </a>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="groups">

                    <div class="border border-slate-200 rounded-2xl divide-y divide-slate-100 overflow-hidden">
                        @forelse($allGroups as $grp)
                            <label class="p-4 flex items-center gap-3 bg-white hover:bg-slate-50/50 cursor-pointer transition">
                                <input type="checkbox" name="group_ids[]" value="{{ $grp->id }}" 
                                    {{ $customer->groups->contains($grp->id) ? 'checked' : '' }}
                                    class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-emerald-700 bg-emerald-50">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-slate-800">{{ $grp->name }}</span>
                            </label>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs">
                                No groups created yet. <a href="{{ route('customers.groups.index') }}" class="text-emerald-600 font-bold underline">Create Group first &rarr;</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Assign</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 7: DELIVERY PERSON (Matching Screenshot 1) -->
            <div x-show="activeTab === 'delivery_person'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-sm font-bold text-slate-900">Delivery Person</h3>
                    <p class="text-xs text-slate-500">Assign delivery person</p>
                </div>

                <div class="mb-5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">TEAM</span>
                    <h4 class="text-sm font-bold text-slate-800">Assign delivery persons</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Tap each delivery person who should be allowed to deliver to this customer. You can pick more than one.</p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="delivery_person">

                    <!-- Delivery Boy Cards matching Screenshot 1 -->
                    <div class="space-y-3">
                        @foreach($routes as $rt)
                            @if($rt->deliveryBoy)
                                <label class="p-4 rounded-2xl border-2 {{ $customer->route_id == $rt->id ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 bg-white' }} flex items-center justify-between cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="route_id" value="{{ $rt->id }}" {{ $customer->route_id == $rt->id ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">
                                            {{ mb_substr($rt->deliveryBoy->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-900">{{ $rt->deliveryBoy->name }}</h5>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $rt->deliveryBoy->phone ?? '9009805828' }} &bull; Route: {{ $rt->name }}</p>
                                        </div>
                                    </div>
                                    @if($customer->route_id == $rt->id)
                                        <span class="text-xs font-bold text-emerald-700">Assigned</span>
                                    @endif
                                </label>
                            @endif
                        @endforeach
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Assign</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

    <!-- Schedule a product Modal (Matching Simple Dairy Images 3 & 4) -->
    <div x-show="scheduleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="scheduleModalOpen" @click="scheduleModalOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>

            <div x-show="scheduleModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl relative z-10 border border-slate-100">
                
                <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Schedule a product</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pick a product, the shift, quantity and when deliveries should start and repeat.</p>
                    </div>
                    <button type="button" @click="scheduleModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-full hover:bg-slate-100 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('subscriptions.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input type="hidden" name="subscription_code" value="SUB-{{ strtoupper(uniqid()) }}">
                    <input type="hidden" name="from_customer_edit" value="1">

                    <!-- Product, Quantity, Shift Row -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product *</label>
                            <select name="product_id" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                <option value="">-- Choose Product --</option>
                                @foreach($allProducts as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->name }} (₹{{ number_format($p->subscription_price ?? $p->price, 2) }}/{{ $p->unit ?? 'L' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity (Ltr / Qty) *</label>
                                <input type="number" step="0.25" min="0.25" name="quantity" value="1.0" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-bold text-slate-800">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift *</label>
                                <select name="shift" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                    <option value="morning">Morning (सुबह)</option>
                                    <option value="evening">Evening (शाम)</option>
                                    <option value="both">Both (दोनों समय)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price Charged (₹) *</label>
                            <input type="number" step="0.5" name="unit_price" value="50" required placeholder="50.00" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-bold text-slate-800">
                        </div>
                    </div>

                    <!-- Start Date, End Date, Toggle Box -->
                    <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Start date *</label>
                                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1" :class="!subStopOnDate ? 'text-slate-400' : 'text-slate-700'">End date</label>
                                <input type="date" name="end_date" :disabled="!subStopOnDate" placeholder="DD/MM/YYYY" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-medium" :class="!subStopOnDate ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800'">
                            </div>
                        </div>

                        <!-- Toggle Switch: Stop delivery on a specific date -->
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">Stop delivery on a specific date</h5>
                                <p class="text-[11px] text-slate-400">Enable this to pick an end date for the schedule.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" name="stop_on_date" value="1" x-model="subStopOnDate" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Repeat Selector & Weekdays -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Repeat *</label>
                            <select name="frequency" x-model="subFrequency" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                                <option value="daily">Daily</option>
                                <option value="alternate_day">Alternate Days</option>
                                <option value="every_3rd_day">Every 3rd Days</option>
                                <option value="weekdays">Week Days</option>
                            </select>
                        </div>

                        <!-- Choose Week Days Pills -->
                        <div x-show="subFrequency === 'weekdays'" x-cloak class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-2">
                            <label class="block text-xs font-bold text-slate-700">Choose week days *</label>
                            <div class="flex flex-wrap items-center gap-1.5">
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
                                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition cursor-pointer"
                                            :class="subWeekDays.includes(day.key) ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-2xs ring-1 ring-emerald-500/20' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'">
                                        <span x-text="day.label"></span>
                                    </button>
                                </template>
                            </div>
                            <template x-for="d in subWeekDays" :key="d">
                                <input type="hidden" name="custom_days[]" :value="d">
                            </template>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="scheduleModalOpen = false" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition cursor-pointer">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Special Rate Modal -->
    <div x-show="specialRateModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="specialRateModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Set Customer Special Rate</h3>
            <form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="active_tab" value="special_rate">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                        @foreach($allProducts as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->name }} (Catalog: ₹{{ $pr->price }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Special Price (₹) *</label>
                    <input type="number" step="0.5" name="special_price" required placeholder="e.g. 48.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold text-emerald-700">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="specialRateModal = false" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg">Save Rate</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
