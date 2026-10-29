@extends('layouts.app')

@section('title', 'POS Counter Billing')
@section('breadcrumb', 'POS')
@section('header_title', 'Retail Counter & Booth POS Terminal')

@section('header_action')
    <a href="{{ route('pos.history') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="history" class="w-3.5 h-3.5"></i>
        <span>Today's Sales History</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="posApp()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Product Catalog Grid -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Category Filter Pills -->
            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-2 overflow-x-auto">
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

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($products as $p)
                    <div 
                        x-show="activeCategory === 'all' || activeCategory === {{ $p->category_id ?? 0 }}"
                        @click="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, '{{ $p->unit }}')"
                        class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md cursor-pointer transition flex flex-col justify-between group"
                    >
                        <div>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2 group-hover:bg-emerald-600 group-hover:text-white transition">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 transition leading-tight">{{ $p->name }}</h4>
                            <span class="text-[10px] text-slate-400 capitalize">{{ $p->unit }}</span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-extrabold text-slate-900">₹{{ number_format($p->price, 0) }}</span>
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs group-hover:scale-110 transition">+</span>
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
                    <div class="text-3xl font-black text-white mt-2">
                        ₹<span x-text="subtotal().toFixed(0)"></span>
                    </div>
                </div>

                <form action="{{ route('pos.store') }}" method="POST" id="posForm" class="space-y-4">
                    @csrf

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

                    <!-- Customer Selector (Compact) -->
                    <div>
                        <select name="customer_id" x-model="customerId" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium text-slate-700">
                            <option value="">Walk-in Customer (नकद ग्राहक)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Items List matching Screenshot Item Cards -->
                    <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1 border-y border-slate-100 py-3">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="p-3 bg-white border border-slate-200 rounded-2xl flex items-center justify-between text-xs shadow-2xs">
                                <div class="flex-1 truncate pr-2">
                                    <p class="font-bold text-slate-900 truncate" x-text="item.name"></p>
                                    <span class="text-[11px] text-slate-500 font-medium" x-text="'₹' + item.price + '/' + (item.unit || 'unit')"></span>
                                    <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.id">
                                    <input type="hidden" :name="'items[' + idx + '][unit_price]'" :value="item.price">
                                    <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
                                </div>
                                <div class="flex items-center gap-3">
                                    <!-- Stepper [-] qty [+] -->
                                    <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                        <button type="button" @click="decreaseQty(idx)" class="w-7 h-7 flex items-center justify-center hover:bg-slate-200 font-bold text-slate-600 transition">-</button>
                                        <span class="w-6 text-center font-bold text-xs text-slate-900" x-text="item.qty"></span>
                                        <button type="button" @click="increaseQty(idx)" class="w-7 h-7 flex items-center justify-center hover:bg-slate-200 font-bold text-slate-600 transition">+</button>
                                    </div>
                                    <!-- Item Total -->
                                    <span class="font-black text-slate-900 text-sm min-w-[50px] text-right" x-text="'₹' + (item.qty * item.price).toFixed(0)"></span>
                                    <button type="button" @click="removeItem(idx)" class="text-slate-300 hover:text-rose-500 text-sm ml-1">&times;</button>
                                </div>
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
                        <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-2xl flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">CHANGE</span>
                            <span class="text-2xl font-black text-emerald-700" x-text="'₹' + getChange()"></span>
                        </div>
                    </div>

                    <!-- Bottom Complete Sale Button (Image Style: Complete sale • ₹Total) -->
                    <button 
                        type="submit" 
                        :disabled="cart.length === 0"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-black text-sm rounded-2xl shadow-xs transition flex items-center justify-center gap-2 transform active:scale-95"
                    >
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span x-text="'Complete sale • ₹' + subtotal().toFixed(0)"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function posApp() {
        return {
            activeCategory: 'all',
            customerId: '',
            payMode: 'cash',
            tenderedCash: '',
            cart: [
                { id: {{ $products->first()->id ?? 1 }}, name: '{{ addslashes($products->first()->name ?? "Paneer") }}', price: {{ $products->first()->price ?? 300 }}, unit: '{{ $products->first()->unit ?? "kg" }}', qty: 1 }
            ],

            clearCart() {
                this.cart = [];
                this.tenderedCash = '';
            },

            addToCart(id, name, price, unit) {
                const existing = this.cart.find(item => item.id === id);
                if (existing) {
                    existing.qty += 1;
                } else {
                    this.cart.push({ id, name, price, unit, qty: 1 });
                }
            },

            increaseQty(idx) {
                this.cart[idx].qty += 1;
            },

            decreaseQty(idx) {
                if (this.cart[idx].qty > 1) {
                    this.cart[idx].qty -= 1;
                } else {
                    this.removeItem(idx);
                }
            },

            removeItem(idx) {
                this.cart.splice(idx, 1);
            },

            subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.qty * item.price), 0);
            },

            getChange() {
                if (!this.tenderedCash || isNaN(this.tenderedCash)) return '0';
                const change = parseFloat(this.tenderedCash) - this.subtotal();
                return change > 0 ? change.toFixed(0) : '0';
            }
        }
    }
</script>
@endpush
@endsection
