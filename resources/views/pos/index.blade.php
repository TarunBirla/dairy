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

        <!-- Right 1 Col: Billing Cart & Checkout -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between h-fit sticky top-20">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-600"></i>
                        <h3 class="text-sm font-bold text-slate-900">Current Bill</h3>
                    </div>
                    <button type="button" @click="cart = []" class="text-[11px] text-slate-400 hover:text-rose-600 font-semibold">Clear Cart</button>
                </div>

                <form action="{{ route('pos.store') }}" method="POST" id="posForm" class="mt-4 space-y-4">
                    @csrf

                    <!-- Customer Selector -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Customer (Walk-in or Registered)</label>
                        <select name="customer_id" x-model="customerId" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
                            <option value="">Walk-in Customer (नकद ग्राहक)</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Items List -->
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1 border-y border-slate-100 py-3">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="flex items-center justify-between text-xs py-1">
                                <div class="flex-1 truncate pr-2">
                                    <p class="font-bold text-slate-800 truncate" x-text="item.name"></p>
                                    <span class="text-[10px] text-slate-400" x-text="'₹' + item.price + ' / ' + item.unit"></span>
                                    <input type="hidden" :name="'items[' + idx + '][product_id]'" :value="item.id">
                                    <input type="hidden" :name="'items[' + idx + '][unit_price]'" :value="item.price">
                                    <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.qty">
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                                        <button type="button" @click="decreaseQty(idx)" class="px-2 py-0.5 hover:bg-slate-200 font-bold text-xs">-</button>
                                        <span class="px-2 font-bold text-xs" x-text="item.qty"></span>
                                        <button type="button" @click="increaseQty(idx)" class="px-2 py-0.5 hover:bg-slate-200 font-bold text-xs">+</button>
                                    </div>
                                    <span class="font-bold text-slate-900 w-14 text-right" x-text="'₹' + (item.qty * item.price).toFixed(0)"></span>
                                    <button type="button" @click="removeItem(idx)" class="text-slate-300 hover:text-rose-500 text-xs ml-1">&times;</button>
                                </div>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="py-6 text-center text-slate-400 text-xs">
                            Cart is empty. Tap products on the left to add items.
                        </div>
                    </div>

                    <!-- Payment Mode -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Payment Mode</label>
                        <div class="grid grid-cols-4 gap-1.5 text-xs">
                            <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer transition" :class="payMode === 'cash' ? 'bg-emerald-50 border-emerald-500 font-bold text-emerald-800' : 'text-slate-600'">
                                <input type="radio" name="payment_mode" value="cash" x-model="payMode" class="hidden">
                                <span>Cash</span>
                            </label>
                            <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer transition" :class="payMode === 'upi' ? 'bg-emerald-50 border-emerald-500 font-bold text-emerald-800' : 'text-slate-600'">
                                <input type="radio" name="payment_mode" value="upi" x-model="payMode" class="hidden">
                                <span>UPI</span>
                            </label>
                            <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer transition" :class="payMode === 'card' ? 'bg-emerald-50 border-emerald-500 font-bold text-emerald-800' : 'text-slate-600'">
                                <input type="radio" name="payment_mode" value="card" x-model="payMode" class="hidden">
                                <span>Card</span>
                            </label>
                            <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer transition" :class="payMode === 'credit' ? 'bg-emerald-50 border-emerald-500 font-bold text-emerald-800' : 'text-slate-600'">
                                <input type="radio" name="payment_mode" value="credit" x-model="payMode" class="hidden">
                                <span>Credit</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bill Totals -->
                    <div class="space-y-1.5 pt-2 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal:</span>
                            <span class="font-bold text-slate-800" x-text="'₹ ' + subtotal().toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                            <span class="text-sm font-bold text-slate-900">Grand Total:</span>
                            <span class="text-2xl font-black text-emerald-700" x-text="'₹ ' + subtotal().toFixed(2)"></span>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="cart.length === 0"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-black text-sm rounded-xl shadow-xs transition transform hover:scale-[1.01]"
                    >
                        Complete Sale & Print Bill &rarr;
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
            cart: [
                { id: {{ $products->first()->id ?? 1 }}, name: '{{ addslashes($products->first()->name ?? "Paneer") }}', price: {{ $products->first()->price ?? 300 }}, unit: '{{ $products->first()->unit ?? "kg" }}', qty: 1 }
            ],

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
            }
        }
    }
</script>
@endpush
@endsection
