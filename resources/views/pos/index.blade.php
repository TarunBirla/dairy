@extends('layouts.app')

@section('title', 'POS Counter Billing')
@section('breadcrumb', 'POS')
@section('header_title', 'Retail Counter & Booth POS Terminal')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('pos.hub') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Counter sales</span>
        </a>
        <a href="{{ route('pos.reports') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
            <span>Reports</span>
        </a>
        <a href="{{ route('pos.history') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i data-lucide="history" class="w-3.5 h-3.5"></i>
            <span>Today's Sales History</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="posApp()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Product Catalog Grid -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Search Bar & Category Filter Pills -->
            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs space-y-2.5">
                <div class="relative w-full">
                    <input 
                        type="text" 
                        x-model="productSearch" 
                        placeholder="Search products by name or code..." 
                        class="w-full pl-9 pr-8 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                    >
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <button type="button" x-show="productSearch" @click="productSearch = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-0.5">
                    <button 
                        type="button" 
                        @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    >
                        All Products
                    </button>
                    @foreach($categories as $cat)
                        <button 
                            type="button" 
                            @click="activeCategory = {{ $cat->id }}" 
                            :class="activeCategory === {{ $cat->id }} ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                            class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                        >
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Cards Grid with Product Images -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($products as $p)
                    <div 
                        x-show="(activeCategory === 'all' || activeCategory === {{ $p->category_id ?? 0 }}) && (!productSearch || '{{ strtolower(addslashes($p->name)) }}'.includes(productSearch.toLowerCase()) || '{{ strtolower(addslashes($p->code ?? '')) }}'.includes(productSearch.toLowerCase()))"
                        @click="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, '{{ $p->unit }}', '{{ !empty($p->image) ? asset($p->image) : '' }}')"
                        class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md cursor-pointer transition flex flex-col justify-between group overflow-hidden"
                    >
                        <div>
                            <!-- Product Image / Placeholder -->
                            <div class="w-full h-28 sm:h-32 rounded-xl overflow-hidden bg-slate-50 border border-slate-100 mb-2.5 flex items-center justify-center relative group-hover:border-emerald-200 transition">
                                @if(!empty($p->image))
                                    <img src="{{ asset($p->image) }}" alt="{{ $p->name }}" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('hidden');" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="hidden w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                        <i data-lucide="package" class="w-6 h-6"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                                        <i data-lucide="package" class="w-6 h-6"></i>
                                    </div>
                                @endif
                                @if($p->current_stock !== null && $p->current_stock > 0)
                                    <span class="absolute top-1.5 right-1.5 px-1.5 py-0.5 rounded-md text-[9px] font-bold bg-white/90 backdrop-blur-xs text-slate-700 shadow-2xs border border-slate-200/60">
                                        Stock: {{ (float)$p->current_stock }}
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition leading-tight line-clamp-2" title="{{ $p->name }}">{{ $p->name }}</h4>
                            <span class="text-[10px] text-slate-400 capitalize block mt-0.5">{{ $p->unit }}</span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-extrabold text-slate-900">₹{{ number_format($p->price, 0) }}</span>
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs group-hover:bg-emerald-600 group-hover:text-white group-hover:scale-110 transition">+</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right 1 Col: Billing Cart & Checkout matching Reference Screenshot -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between h-fit sticky top-20">
            <div>
                <!-- Top Dark Summary Header (Image Style: TOTAL • X ITEMS, Clear, ₹Amount) -->
                <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-sm mb-4">
                    <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider">
                        <span>TOTAL • <span x-text="cart.length"></span> ITEMS</span>
                        <button type="button" @click="clearCart()" class="text-slate-300 hover:text-white capitalize transition font-semibold">
                            Clear
                        </button>
                    </div>
                    <div class="flex items-baseline justify-between mt-2">
                        <div class="text-3xl font-black text-white">
                            ₹<span x-text="grandTotal().toFixed(0)"></span>
                        </div>
                        <template x-if="totalDiscount() > 0">
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-950/60 border border-emerald-800 px-2 py-0.5 rounded-lg">
                                Disc: -₹<span x-text="totalDiscount().toFixed(0)"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <form action="{{ route('pos.store') }}" method="POST" id="posForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="discount_amount" :value="totalDiscount().toFixed(2)">

                    <!-- Row: Payment Mode & Sale Date -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">PAYMENT</label>
                            <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-700">
                                <button type="button" 
                                        @click="payMode = 'cash'" 
                                        :class="payMode === 'cash' ? 'bg-white shadow-xs text-slate-900' : 'text-slate-500'" 
                                        class="py-1.5 rounded-lg text-center transition">
                                    Cash
                                </button>
                                <button type="button" 
                                        @click="payMode = 'upi'" 
                                        :class="payMode === 'upi' ? 'bg-white shadow-xs text-slate-900' : 'text-slate-500'" 
                                        class="py-1.5 rounded-lg text-center transition">
                                    UPI
                                </button>
                            </div>
                            <input type="hidden" name="payment_mode" :value="payMode">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">SALE DATE</label>
                            <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" class="w-full px-2.5 py-1.5 text-xs font-semibold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Searchable Walk-in Customer Dropdown matching user image -->
                    <div class="space-y-1.5">
                        <div class="relative" @click.outside="openCustomerDropdown = false">
                            <!-- Pill trigger button matching user design -->
                            <button type="button" 
                                    @click="openCustomerDropdown = !openCustomerDropdown; if (openCustomerDropdown) { $nextTick(() => { $refs.custSearchInput.focus(); }); }"
                                    class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold bg-white border border-slate-200 rounded-full shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-pointer transition text-left"
                                    :class="customerName ? 'text-slate-900 border-emerald-300' : 'text-slate-700'">
                                <div class="flex items-center gap-2 truncate pr-2">
                                    <span class="text-slate-400 shrink-0">👤</span>
                                    <span class="truncate font-semibold" x-text="selectedCustomerLabel"></span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 text-slate-400">
                                    <template x-if="customerName">
                                        <span role="button" 
                                              @click.stop="clearCustomerInputs()" 
                                              class="p-0.5 hover:text-rose-500 hover:bg-slate-100 rounded-full transition" 
                                              title="Clear walk-in customer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </span>
                                    </template>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openCustomerDropdown}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Dropdown Popover with Search Filter -->
                            <div x-show="openCustomerDropdown" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden text-xs">
                                
                                <!-- Search Input Box -->
                                <div class="p-2 border-b border-slate-100 bg-slate-50/80">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                        <input type="text"
                                               x-ref="custSearchInput"
                                               x-model="customerSearch"
                                               @keydown.escape="openCustomerDropdown = false"
                                               @keydown.enter.prevent="selectFirstMatch()"
                                               placeholder="Search walk-in customer or phone..."
                                               class="w-full pl-8 pr-7 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 font-medium placeholder-slate-400">
                                        <template x-if="customerSearch">
                                            <button type="button" @click="customerSearch = ''; $refs.custSearchInput.focus()" class="absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400 hover:text-slate-600">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Walk-in Customers Options List -->
                                <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 py-1">
                                    <!-- Option 1: Walk-in Default -->
                                    <div @click="selectCustomer(null)" 
                                         class="px-3.5 py-2 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition"
                                         :class="!customerName ? 'bg-emerald-50/80 font-bold text-emerald-900' : 'text-slate-700'">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" :class="!customerName ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                            <span>Walk-in Customer (नकद ग्राहक)</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-normal">Default</span>
                                    </div>

                                    <!-- Filtered Walk-in Customers -->
                                    <template x-for="(c, idx) in filteredCustomers" :key="idx">
                                        <div @click="selectCustomer(c)" 
                                             class="px-3.5 py-2 hover:bg-slate-50 cursor-pointer flex items-center justify-between transition"
                                             :class="isCustomerActive(c) ? 'bg-emerald-50/70 font-semibold text-emerald-900' : 'text-slate-700'">
                                            <div class="truncate pr-2">
                                                <div class="font-semibold text-slate-900 truncate" x-text="c.name"></div>
                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5" x-show="c.phone">
                                                    <span x-text="'📞 ' + c.phone"></span>
                                                </div>
                                            </div>
                                            <template x-if="isCustomerActive(c)">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Empty search result state -->
                                    <template x-if="customerSearch && filteredCustomers.length === 0">
                                        <div class="p-3 text-center">
                                            <p class="text-slate-400 text-xs mb-2">No walk-in customer found matching "<span class="font-bold text-slate-600" x-text="customerSearch"></span>"</p>
                                            <button type="button" 
                                                    @click="useSearchAsNewCustomer()"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs shadow-xs transition">
                                                <span>+ Use</span>
                                                <span class="font-semibold truncate max-w-[120px]" x-text="'&quot;' + customerSearch + '&quot;'"></span>
                                                <span>as Walk-in Name</span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Option to use current search query as custom customer even if partial matches exist -->
                                    <template x-if="customerSearch && filteredCustomers.length > 0 && !exactMatchFound">
                                        <div @click="useSearchAsNewCustomer()" 
                                             class="px-3.5 py-2 hover:bg-emerald-50 cursor-pointer flex items-center gap-2 text-emerald-700 font-semibold border-t border-dashed border-slate-200 bg-slate-50/50 transition">
                                            <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">+</span>
                                            <span class="truncate">Use "<span x-text="customerSearch"></span>" as new walk-in</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- (B) WALK-IN CUSTOMER (OPTIONAL) Collapsible Accordion -->
                        <div>
                            <button type="button" 
                                    @click="showCustomerInputs = !showCustomerInputs"
                                    class="w-full flex items-center justify-between px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/80 transition text-xs font-bold">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm">👤</span>
                                    <span class="uppercase tracking-wider text-[10px] font-black" :class="customerName ? 'text-emerald-700' : 'text-slate-600'">
                                        WALK-IN CUSTOMER (OPTIONAL)
                                    </span>
                                    <template x-if="customerName">
                                        <span class="ml-1 text-[10px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded-full truncate max-w-[120px]" x-text="customerName"></span>
                                    </template>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transition" :class="{'rotate-180': showCustomerInputs}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- 3 Inputs: Name, Phone, Email -->
                            <div x-show="showCustomerInputs" x-cloak class="mt-2 p-3 bg-slate-50/80 rounded-2xl border border-slate-200 space-y-2.5">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Walk-in Name (वॉक-इन ग्राहक का नाम)</label>
                                    <input type="text" 
                                           name="customer_name" 
                                           x-model="customerName" 
                                           placeholder="Enter walk-in customer name" 
                                           class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Phone (मोबाइल नंबर)</label>
                                        <input type="text" 
                                               name="customer_phone" 
                                               x-model="customerPhone" 
                                               placeholder="Enter phone" 
                                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Email (ईमेल)</label>
                                        <input type="email" 
                                               name="customer_email" 
                                               x-model="customerEmail" 
                                               placeholder="Enter email" 
                                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                    <span>Optional • Default: Walk-in (नकद ग्राहक)</span>
                                    <button type="button" @click="clearCustomerInputs()" class="text-rose-500 font-bold hover:underline">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items List matching User Reference (Images 1 & 2) -->
                    <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1 border-y border-slate-100 py-3">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="p-3.5 bg-white border border-slate-200/90 rounded-2xl text-xs shadow-2xs space-y-2.5 relative">
                                <!-- Top Row: Product Name, Unit Price & 3-Dots Button -->
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 flex-1 min-w-0 pr-1">
                                        <template x-if="item.image">
                                            <img :src="item.image" :alt="item.name" class="w-8 h-8 rounded-lg object-cover border border-slate-200/80 shrink-0">
                                        </template>
                                        <div class="min-w-0">
                                            <h4 class="font-extrabold text-slate-900 truncate text-sm leading-tight" x-text="item.name"></h4>
                                            <div class="text-[11px] text-slate-500 font-semibold tracking-wide flex items-center gap-1.5 mt-0.5">
                                                <span x-text="'₹' + item.price + '/' + (item.unit || 'KG').toUpperCase()"></span>
                                                <template x-if="item.price != item.original_price">
                                                    <span class="line-through text-slate-400 text-[10px]" x-text="'₹' + item.original_price"></span>
                                                </template>
                                                <template x-if="item.discount > 0">
                                                    <span class="text-emerald-700 font-bold text-[10px] bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-100" x-text="'-₹' + item.discount"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3 Dots Button & Popover Menu (Matching Image 2) -->
                                    <div class="relative shrink-0">
                                        <button 
                                            type="button" 
                                            @click.stop="toggleItemMenu(idx)" 
                                            class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center font-bold text-base tracking-widest transition"
                                            title="Item discount & price override"
                                        >
                                            <span class="leading-none pb-1 font-black text-sm">•••</span>
                                        </button>

                                        <!-- Dropdown Popup Menu (Image 2) -->
                                        <div 
                                            x-show="item.menuOpen" 
                                            @click.outside="item.menuOpen = false" 
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 top-8 w-60 bg-white border border-slate-200 rounded-2xl shadow-xl p-3.5 space-y-3 z-50 text-left"
                                            style="display: none;"
                                        >
                                            <!-- DISCOUNT (₹) -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                                    DISCOUNT (₹)
                                                </label>
                                                <input 
                                                    type="number" 
                                                    step="any" 
                                                    min="0" 
                                                    x-model.number="item.discount" 
                                                    placeholder="0" 
                                                    class="w-full px-3 py-1.5 text-right font-bold text-slate-900 text-xs rounded-xl border border-emerald-500 bg-white focus:ring-2 focus:ring-emerald-500/20 outline-none"
                                                >
                                            </div>

                                            <!-- PRICE OVERRIDE (₹) -->
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                                                    PRICE OVERRIDE (₹)
                                                </label>
                                                <input 
                                                    type="number" 
                                                    step="any" 
                                                    min="0" 
                                                    x-model.number="item.price" 
                                                    @input="onPriceOverrideChange(item)"
                                                    :placeholder="item.original_price" 
                                                    class="w-full px-3 py-1.5 text-right font-bold text-slate-900 text-xs rounded-xl border border-slate-200 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none"
                                                >
                                            </div>

                                            <!-- Total-price mode toggle -->
                                            <div class="flex items-center justify-between pt-1">
                                                <span class="text-xs font-semibold text-slate-600">Total-price mode</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input 
                                                        type="checkbox" 
                                                        x-model="item.total_price_mode" 
                                                        @change="onTotalPriceModeToggle(item)"
                                                        class="sr-only peer"
                                                    >
                                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                                </label>
                                            </div>

                                            <!-- Divider -->
                                            <div class="border-t border-slate-100 pt-1">
                                                <!-- Remove from cart -->
                                                <button 
                                                    type="button" 
                                                    @click="removeItem(idx)" 
                                                    class="w-full flex items-center gap-2 text-rose-600 hover:text-rose-700 font-semibold text-xs py-1 transition"
                                                >
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Remove from cart</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom Row: Stepper (Direct Typeable Qty Input!) & Item Total -->
                                <div class="flex items-center justify-between gap-3 pt-1 border-t border-slate-100/70">
                                    <!-- Stepper with editable input box (Image 1) -->
                                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs">
                                        <button 
                                            type="button" 
                                            @click="decreaseQty(idx)" 
                                            class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 font-bold text-slate-400 hover:text-slate-800 transition select-none text-base"
                                        >-</button>
                                        <input 
                                            type="number" 
                                            step="any" 
                                            min="0.01" 
                                            x-model.number="item.qty" 
                                            @input="onQtyChange(item)"
                                            class="w-14 h-8 text-center font-bold text-xs text-slate-900 border-x border-slate-200 bg-white focus:bg-emerald-50/50 focus:border-emerald-500 outline-none"
                                        >
                                        <button 
                                            type="button" 
                                            @click="increaseQty(idx)" 
                                            class="w-8 h-8 flex items-center justify-center hover:bg-slate-50 font-bold text-slate-400 hover:text-slate-800 transition select-none text-base"
                                        >+</button>
                                    </div>

                                    <!-- Item Total Amount (₹680 / ₹285) -->
                                    <div class="text-right">
                                        <span class="font-black text-slate-900 text-base" x-text="'₹' + itemTotal(item).toFixed(0)"></span>
                                    </div>
                                </div>

                                <!-- Hidden Inputs for Backend Form Submission -->
                                <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.id">
                                <input type="hidden" :name="'items[' + idx + '][unit_price]'" :value="item.price">
                                <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="py-6 text-center text-slate-400 text-xs">
                            Cart is empty. Tap products on the left to add items.
                        </div>
                    </div>

                    <!-- Cash & Change Calculator Section matching Image -->
                    <div class="space-y-2.5 pt-2" x-show="payMode === 'cash'">
                        <!-- CASH Input Box with Green Border & Reset Button -->
                        <div class="p-2 border-2 border-emerald-500 rounded-2xl bg-white flex items-center justify-between gap-2 shadow-2xs">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider pl-2">CASH</span>
                            <div class="flex items-center gap-1 flex-1 justify-end pr-2">
                                <span class="font-bold text-slate-500 text-sm">₹</span>
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       x-model.number="tenderedCash" 
                                       placeholder="0" 
                                       class="w-28 text-right font-black text-slate-900 text-base outline-none bg-transparent">
                            </div>
                            <button type="button" @click="tenderedCash = ''" class="text-xs font-bold text-slate-400 hover:text-slate-700 pr-2">Reset</button>
                        </div>

                        <!-- Quick Notes / Denominations -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                            <button type="button" @click="tenderedCash = 100" class="px-3 py-1 rounded-full text-xs font-bold border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 shadow-2xs transition">₹100</button>
                            <button type="button" @click="tenderedCash = 200" class="px-3 py-1 rounded-full text-xs font-bold border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 shadow-2xs transition">₹200</button>
                            <button type="button" @click="tenderedCash = 500" class="px-3 py-1 rounded-full text-xs font-bold border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 shadow-2xs transition">₹500</button>
                            <button type="button" @click="tenderedCash = 1000" class="px-3 py-1 rounded-full text-xs font-bold border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 shadow-2xs transition">₹1,000</button>
                            <button type="button" @click="tenderedCash = 2000" class="px-3 py-1 rounded-full text-xs font-bold border border-slate-200 bg-white hover:bg-emerald-50 hover:border-emerald-300 text-slate-700 shadow-2xs transition">₹2,000</button>
                        </div>

                        <!-- CHANGE Return Highlight Box -->
                        <div class="p-3 rounded-2xl flex items-center justify-between border transition"
                             :class="getChangeVal() < 0 ? 'bg-rose-50/80 border-rose-200' : 'bg-emerald-50/80 border-emerald-200'">
                            <span class="text-xs font-bold uppercase tracking-wider"
                                  :class="getChangeVal() < 0 ? 'text-rose-800' : 'text-emerald-800'">CHANGE</span>
                            <span class="text-2xl font-black"
                                  :class="getChangeVal() < 0 ? 'text-rose-600' : 'text-emerald-700'"
                                  x-text="getChangeFormatted()"></span>
                        </div>
                    </div>

                    <!-- Bottom Complete Sale Button (Image Style: Complete sale • ₹Total) -->
                    <button 
                        type="submit" 
                        :disabled="cart.length === 0"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-black text-sm rounded-2xl shadow-xs transition flex items-center justify-center gap-2 transform active:scale-95"
                    >
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span x-text="'Complete sale • ₹' + grandTotal().toFixed(0)"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Walk-in Counter Sales Table matching Requirement (C) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-bold text-slate-900">Walk-in Counter Sales / हालिया काउंटर बिक्री</h3>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Recent counter orders and customer receipts</p>
            </div>
            <a href="{{ route('pos.history') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs">
                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                <span>View Full Sales History &rarr;</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-5">Order #</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer Name / Phone</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Payment Mode</th>
                        <th class="py-3 px-4 text-right">Grand Total</th>
                        <th class="py-3 px-5 text-right">Receipt / Print</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Order # -->
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                {{ $order->order_number }}
                            </td>

                            <!-- Date & Time -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-700">{{ $order->created_at->format('d M Y') }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $order->created_at->format('h:i A') }}</span>
                            </td>

                            <!-- Customer Name / Phone -->
                            <td class="py-3.5 px-4">
                                @if($order->customer_name && $order->customer_name !== 'Walk-in Customer')
                                    <span class="font-bold text-slate-900 block">{{ $order->customer_name }}</span>
                                    @if($order->customer_phone)
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $order->customer_phone }}</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        Walk-in Customer
                                    </span>
                                @endif
                            </td>

                            <!-- Items -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($order->items as $item)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 text-[10px] font-medium border border-emerald-100">
                                            {{ $item->product->name ?? 'Item' }} &times; {{ (float)$item->quantity }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Payment Mode (Cash / UPI) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(strtolower($order->payment_mode) === 'cash')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        CASH
                                    </span>
                                @elseif(strtolower($order->payment_mode) === 'upi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                        UPI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                        {{ $order->payment_mode }}
                                    </span>
                                @endif
                            </td>

                            <!-- Grand Total -->
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                ₹{{ number_format($order->grand_total, 2) }}
                            </td>

                            <!-- Receipt / Print Action -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <a href="{{ route('pos.receipt', $order) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-emerald-600 text-slate-700 hover:text-white text-xs font-bold rounded-xl transition shadow-2xs">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    <span>Print Receipt</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                No walk-in counter sales recorded yet today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function posApp() {
        return {
            activeCategory: 'all',
            productSearch: '',
            payMode: 'cash',
            tenderedCash: '',
            openCustomerDropdown: false,
            customerSearch: '',
            showCustomerInputs: false,
            customerName: '',
            customerPhone: '',
            customerEmail: '',

            walkInList: [
                @foreach($walkInCustomers as $wc)
                {
                    name: '{{ addslashes($wc->customer_name) }}',
                    phone: '{{ addslashes($wc->customer_phone ?? "") }}',
                    email: '{{ addslashes($wc->customer_email ?? "") }}',
                },
                @endforeach
            ],

            get selectedCustomerLabel() {
                if (this.customerName) {
                    return this.customerName + (this.customerPhone ? ' (' + this.customerPhone + ')' : '');
                }
                return 'Walk-in Customer (नकद ग्राहक)';
            },

            get filteredCustomers() {
                if (!this.customerSearch || !this.customerSearch.trim()) {
                    return this.walkInList;
                }
                const q = this.customerSearch.toLowerCase().trim();
                return this.walkInList.filter(c => {
                    const nameMatch = (c.name || '').toLowerCase().includes(q);
                    const phoneMatch = (c.phone || '').toLowerCase().includes(q);
                    return nameMatch || phoneMatch;
                });
            },

            get exactMatchFound() {
                if (!this.customerSearch || !this.customerSearch.trim()) return false;
                const q = this.customerSearch.toLowerCase().trim();
                return this.walkInList.some(c => (c.name || '').toLowerCase() === q);
            },

            isCustomerActive(c) {
                if (this.customerName && this.customerName.toLowerCase() === (c.name || '').toLowerCase()) {
                    if (!c.phone || this.customerPhone === c.phone) return true;
                }
                return false;
            },

            selectCustomer(c) {
                if (!c) {
                    this.clearCustomerInputs();
                    return;
                }
                this.customerName = c.name || '';
                this.customerPhone = c.phone || '';
                this.customerEmail = c.email || '';
                this.customerSearch = '';
                this.openCustomerDropdown = false;
                this.showCustomerInputs = true;
            },

            useSearchAsNewCustomer() {
                this.customerName = (this.customerSearch || '').trim();
                this.customerPhone = '';
                this.customerEmail = '';
                this.showCustomerInputs = true;
                this.customerSearch = '';
                this.openCustomerDropdown = false;
            },

            selectFirstMatch() {
                if (this.filteredCustomers.length > 0) {
                    this.selectCustomer(this.filteredCustomers[0]);
                } else if (this.customerSearch.trim()) {
                    this.useSearchAsNewCustomer();
                }
            },

            clearCustomerInputs() {
                this.customerName = '';
                this.customerPhone = '';
                this.customerEmail = '';
                this.customerSearch = '';
                this.openCustomerDropdown = false;
            },

            cart: [
                @if($firstProduct = $products->first())
                { 
                    id: {{ $firstProduct->id }}, 
                    name: '{{ addslashes($firstProduct->name) }}', 
                    original_price: {{ (float)($firstProduct->price ?? 0) }},
                    price: {{ (float)($firstProduct->price ?? 0) }}, 
                    unit: '{{ $firstProduct->unit ?? "kg" }}', 
                    image: '{{ !empty($firstProduct->image) ? asset($firstProduct->image) : "" }}', 
                    qty: 1,
                    discount: 0,
                    total_price_mode: false,
                    menuOpen: false
                }
                @endif
            ],

            clearCart() {
                this.cart = [];
                this.tenderedCash = '';
            },

            addToCart(id, name, price, unit, image = '') {
                const existing = this.cart.find(item => item.id === id);
                if (existing) {
                    existing.qty = Math.round((parseFloat(existing.qty || 1) + 1) * 100) / 100;
                } else {
                    this.cart.push({
                        id,
                        name,
                        original_price: parseFloat(price) || 0,
                        price: parseFloat(price) || 0,
                        unit,
                        image,
                        qty: 1,
                        discount: 0,
                        total_price_mode: false,
                        menuOpen: false
                    });
                }
                if (window.lucide) {
                    setTimeout(() => lucide.createIcons(), 50);
                }
            },

            toggleItemMenu(idx) {
                const targetState = !this.cart[idx].menuOpen;
                this.cart.forEach((it, i) => {
                    it.menuOpen = false;
                });
                this.cart[idx].menuOpen = targetState;
                if (window.lucide) {
                    setTimeout(() => lucide.createIcons(), 50);
                }
            },

            increaseQty(idx) {
                const cur = parseFloat(this.cart[idx].qty) || 0;
                this.cart[idx].qty = Math.round((cur + 1) * 100) / 100;
                this.onQtyChange(this.cart[idx]);
            },

            decreaseQty(idx) {
                const cur = parseFloat(this.cart[idx].qty) || 0;
                if (cur > 1) {
                    this.cart[idx].qty = Math.round((cur - 1) * 100) / 100;
                    this.onQtyChange(this.cart[idx]);
                } else {
                    this.removeItem(idx);
                }
            },

            onQtyChange(item) {
                if (item.qty === '' || isNaN(item.qty)) return;
                if (item.total_price_mode && item.original_price > 0) {
                    // In total price mode, updating quantity adjusts or maintains total
                }
            },

            onPriceOverrideChange(item) {
                if (item.total_price_mode && item.original_price > 0) {
                    const totalAmt = parseFloat(item.price) || 0;
                    if (totalAmt > 0) {
                        item.qty = parseFloat((totalAmt / item.original_price).toFixed(3));
                    }
                }
            },

            onTotalPriceModeToggle(item) {
                if (item.total_price_mode) {
                    // Switch to total price mode
                    const currentTotal = (parseFloat(item.qty) || 1) * (parseFloat(item.price) || item.original_price);
                    item.price = Math.round(currentTotal);
                } else {
                    // Revert to original unit price
                    item.price = item.original_price;
                }
            },

            removeItem(idx) {
                this.cart.splice(idx, 1);
            },

            itemTotal(item) {
                const qty = parseFloat(item.qty) || 0;
                const price = parseFloat(item.price) || 0;
                const discount = parseFloat(item.discount) || 0;
                return Math.max(0, (qty * price) - discount);
            },

            subtotal() {
                return this.cart.reduce((sum, item) => {
                    const qty = parseFloat(item.qty) || 0;
                    const price = parseFloat(item.price) || 0;
                    return sum + (qty * price);
                }, 0);
            },

            totalDiscount() {
                return this.cart.reduce((sum, item) => {
                    const disc = parseFloat(item.discount) || 0;
                    return sum + disc;
                }, 0);
            },

            grandTotal() {
                return Math.max(0, this.subtotal() - this.totalDiscount());
            },

            getChangeVal() {
                if (this.tenderedCash === '' || isNaN(this.tenderedCash)) return 0;
                return Math.round(parseFloat(this.tenderedCash) - this.grandTotal());
            },

            getChangeFormatted() {
                if (this.tenderedCash === '' || isNaN(this.tenderedCash)) return '₹0';
                const diff = this.getChangeVal();
                if (diff < 0) {
                    return '- ₹' + Math.abs(diff);
                }
                return '₹' + diff;
            }
        }
    }
</script>
@endpush
@endsection
