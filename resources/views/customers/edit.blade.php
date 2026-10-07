@extends('layouts.app')

@section('title', 'Edit ' . $customer->name)
@section('breadcrumb', 'Edit Customer')
@section('header_title', 'Dashboard / Customers / Edit')

@section('content')
<div x-data="{ 
    activeTab: '{{ request('tab', 'profile') }}',
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

            <!-- TAB 2: BILLING DETAILS -->
            <div x-show="activeTab === 'billing'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h3 class="text-sm font-bold text-slate-900">Customer Billing Details</h3>
                    <p class="text-xs text-slate-500">Configure ledger balances, credit limit, and payment terms.</p>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="active_tab" value="billing">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">CURRENT OUTSTANDING BALANCE</span>
                            <p class="text-2xl font-black {{ $customer->current_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                                ₹ {{ number_format($customer->current_balance, 2) }}
                            </p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">CREDIT LIMIT ALLOWED</span>
                            <p class="text-2xl font-black text-slate-900 mt-1">
                                ₹ {{ number_format($customer->credit_limit, 2) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Credit Limit (₹)</label>
                        <input type="number" step="50" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                        <div class="flex gap-2">
                            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Save Billing</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 3: SCHEDULE PRODUCT DELIVERY (Matching Screenshot 5) -->
            <div x-show="activeTab === 'schedule'" x-cloak>
                <div class="border-b border-slate-100 pb-4 mb-5">
                    <h3 class="text-sm font-bold text-slate-900">Schedule Product Delivery</h3>
                    <p class="text-xs text-slate-500">Subscription of the products</p>
                </div>

                <div class="mb-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">DELIVERIES</span>
                    <h4 class="text-sm font-bold text-slate-800">Scheduled product delivery</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Set which products are delivered to this customer and on which days. Customers don't see unscheduled products in their order list.</p>
                </div>

                <div class="mb-5">
                    <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        <span>+ Schedule product</span>
                    </a>
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
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        <span class="font-bold text-emerald-600 uppercase">{{ $sub->status }}</span>
                                        <span>{{ $sub->start_date->format('d M Y') }} &rarr; {{ $sub->end_date ? $sub->end_date->format('d M Y') : 'No end date' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            No scheduled products active for this customer yet. Click above to schedule daily milk.
                        </div>
                    @endforelse
                </div>

                <div class="pt-8 mt-6 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Back</a>
                    <div class="flex gap-2">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-2xl transition">Customers list</a>
                        <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition">Schedule special rate</a>
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
