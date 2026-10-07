@extends('layouts.app')

@section('title', 'Sales Products')
@section('breadcrumb', 'Sales Products')
@section('header_title', 'Products / Farmer Product Sales')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Sale Product</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="saleProductManager()" @open-sale-product-modal.window="openCreateModal()">

    <!-- Navigation Tabs (Products vs Categories vs Buy Products vs Sales Products) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 overflow-x-auto">
        <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            <span>All Products</span>
        </a>
        <a href="{{ route('products.categories.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('purchases.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
            <span>Buy Products</span>
        </a>
        <a href="{{ route('product-sales.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
            <span>Sales Products (Farmer Sales)</span>
        </a>
        <a href="{{ route('inventory.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
            <span>Product Stock</span>
        </a>
    </div>

    <!-- Page Title & Subtitle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Farmer Product Sales</h2>
            <p class="text-xs text-slate-500 mt-0.5">Sell cattle feed, khali, ghee, and dairy items to farmers and track ledger balances.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="sm:hidden inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Sale Product</span>
        </button>
    </div>

    <!-- 4 Stat Summary Cards matching theme -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Quantity -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                </div>
                <span>TOTAL SOLD QTY</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900">{{ number_format($totalQuantity, 2) }}</span>
                <span class="text-xs text-slate-400 font-medium ml-1">Units</span>
            </div>
        </div>

        <!-- Total Sales Amount -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-400/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                </div>
                <span>TOTAL SALES AMOUNT</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-emerald-700">₹ {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>

        <!-- Total Paid / Received -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <span>PAID / RECEIVED</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-blue-700">₹ {{ number_format($totalPaid, 2) }}</span>
            </div>
        </div>

        <!-- Total Remaining Balance / Dues -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
                <span>REMAINING / DUES</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black {{ $totalRemaining > 0 ? 'text-amber-700' : 'text-slate-900' }}">
                    ₹ {{ number_format($totalRemaining, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar matching theme -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('product-sales.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search farmer, code, phone, product..." 
                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                >
            </div>

            <!-- Payment Mode Filter -->
            <div class="w-full sm:w-36">
                <select name="payment_mode" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Payments</option>
                    <option value="Cash" {{ request('payment_mode') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="UPI" {{ request('payment_mode') === 'UPI' ? 'selected' : '' }}>UPI</option>
                    <option value="Bank Transfer" {{ request('payment_mode') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Ledger" {{ request('payment_mode') === 'Ledger' ? 'selected' : '' }}>Ledger / Credit</option>
                </select>
            </div>

            <!-- Date Picker -->
            <div class="w-full sm:w-40">
                <input 
                    type="date" 
                    name="date" 
                    value="{{ request('date') }}"
                    onchange="this.form.submit()"
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
            </div>

            <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition">
                Filter
            </button>

            @if(request('search') || request('payment_mode') || request('date'))
                <a href="{{ route('product-sales.index') }}" class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Right action: Download / Add Sale -->
        <div class="flex items-center gap-2 justify-end">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Print List</span>
            </button>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Sale Product</span>
            </button>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Farmer Code</th>
                        <th class="py-3.5 px-4 min-w-[140px]">Farmer Name</th>
                        <th class="py-3.5 px-4 min-w-[140px]">Product Name</th>
                        <th class="py-3.5 px-4 text-right">Quantity</th>
                        <th class="py-3.5 px-4 text-right">Rate</th>
                        <th class="py-3.5 px-4 text-right">Total Amount</th>
                        <th class="py-3.5 px-4 text-right">Paid Amount</th>
                        <th class="py-3.5 px-4 text-right">Remaining Amount</th>
                        <th class="py-3.5 px-4 text-center">Payment</th>
                        <th class="py-3.5 px-4 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $item)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- Date -->
                            <td class="py-4 px-4 font-mono font-medium text-slate-900 whitespace-nowrap">
                                {{ $item->sale_date->format('d-m-Y') }}
                            </td>

                            <!-- Farmer Code -->
                            <td class="py-4 px-4 font-mono font-bold text-slate-700 whitespace-nowrap">
                                {{ $item->farmer_code ?? ($item->farmer ? $item->farmer->farmer_code : '—') }}
                            </td>

                            <!-- Farmer Name -->
                            <td class="py-4 px-4 font-bold text-slate-900 capitalize">
                                {{ $item->farmer_name }}
                            </td>

                            <!-- Product Name -->
                            <td class="py-4 px-4 font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ $item->product_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $item->unit }})</span>
                                </div>
                            </td>

                            <!-- Quantity -->
                            <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                {{ number_format($item->quantity, 2) }}
                            </td>

                            <!-- Rate -->
                            <td class="py-4 px-4 text-right font-mono text-slate-700">
                                ₹{{ number_format($item->rate, 2) }}
                            </td>

                            <!-- Total Amount -->
                            <td class="py-4 px-4 text-right font-mono font-bold text-emerald-700 text-sm whitespace-nowrap">
                                ₹{{ number_format($item->total_amount, 2) }}
                            </td>

                            <!-- Paid Amount -->
                            <td class="py-4 px-4 text-right font-mono font-semibold text-blue-700 whitespace-nowrap">
                                ₹{{ number_format($item->paid_amount, 2) }}
                            </td>

                            <!-- Remaining Amount -->
                            <td class="py-4 px-4 text-right font-mono font-semibold whitespace-nowrap {{ $item->remaining_amount > 0 ? 'text-amber-700' : 'text-slate-500' }}">
                                ₹{{ number_format($item->remaining_amount, 2) }}
                            </td>

                            <!-- Payment Mode Badge -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($item->payment_mode === 'Cash')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Cash
                                    </span>
                                @elseif($item->payment_mode === 'UPI')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                        UPI
                                    </span>
                                @elseif($item->payment_mode === 'Bank Transfer')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Bank
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Ledger
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- View Details Modal Button -->
                                    <button 
                                        type="button" 
                                        @click="openViewModal({{ json_encode($item) }})" 
                                        title="View Details" 
                                        class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition"
                                    >
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Print Slip Link -->
                                    <a 
                                        href="{{ route('product-sales.print', $item) }}" 
                                        target="_blank" 
                                        title="Print Receipt" 
                                        class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition"
                                    >
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>

                                    <!-- Edit Button (Unified Modal) -->
                                    <button 
                                        type="button" 
                                        @click="openEditModal({{ json_encode($item) }})" 
                                        title="Edit Entry" 
                                        class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white flex items-center justify-center transition"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form 
                                        action="{{ route('product-sales.destroy', $item) }}" 
                                        method="POST" 
                                        onsubmit="return confirm('Are you sure you want to delete this sale entry #{{ $item->sale_number }}? The stock will be restored.')" 
                                        class="inline-block"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            title="Delete Entry" 
                                            class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3 text-slate-300">
                                    <i data-lucide="trending-up" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">No Sales Product entries found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Click "+ Sale Product" to record items sold to farmers.</p>
                                <button type="button" @click="openCreateModal()" class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition inline-flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Record Sale</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-white">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- UNIFIED ADD / EDIT SALE PRODUCT MODAL                                     -->
    <!-- ========================================================================= -->
    <div 
        x-show="modalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Modal Backdrop -->
        <div 
            x-show="modalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-150" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
            @click="modalOpen = false"
        ></div>

        <!-- Modal Box -->
        <div 
            x-show="modalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
            x-transition:leave="ease-in duration-150" 
            x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
            x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
            class="bg-white rounded-2xl max-w-2xl sm:max-w-3xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 relative z-10 my-8 max-h-[92vh] overflow-y-auto"
        >
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span x-text="isEdit ? 'Edit Sale Product' : 'Sale Product'"></span>
                        <span x-show="isEdit" class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full" x-text="'#' + form.sale_number"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span x-text="isEdit ? 'Update existing product sale record.' : 'Sell dairy products or cattle feed to registered farmers.'"></span>
                    </p>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Validation Errors Banner -->
            @if($errors->any())
                <div class="mt-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Modal Form -->
            <form :action="isEdit ? '{{ url('product-sales') }}/' + editId : '{{ route('product-sales.store') }}'" method="POST" class="mt-5 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Sale Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sale Date *</label>
                    <input 
                        type="date" 
                        name="sale_date" 
                        x-model="form.sale_date" 
                        required 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                    >
                </div>

                <!-- Row 1: Farmer & Farmer Code -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Farmer *</label>
                        <select 
                            name="farmer_id" 
                            id="sale_farmer_select" 
                            x-model="form.farmer_id" 
                            @change="onFarmerChange($event.target.value)" 
                            required 
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Select Farmer</option>
                            @foreach($farmers as $farmer)
                                <option value="{{ $farmer->id }}" data-code="{{ $farmer->farmer_code }}" data-name="{{ $farmer->name }}">
                                    {{ $farmer->farmer_code }} - {{ $farmer->name }} ({{ $farmer->village ?? 'Village' }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="farmer_name" x-model="form.farmer_name">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Farmer Code</label>
                        <input 
                            type="text" 
                            name="farmer_code" 
                            x-model="form.farmer_code" 
                            placeholder="e.g. FMR001" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 font-mono text-slate-700 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 2: Product, Unit & Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Product -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product *</label>
                        <select 
                            name="product_id" 
                            id="sale_product_select" 
                            x-model="form.product_id" 
                            @change="onProductChange($event.target.value)" 
                            required 
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Select Product</option>
                            @foreach($products as $prod)
                                <option 
                                    value="{{ $prod->id }}" 
                                    data-name="{{ $prod->name }}" 
                                    data-unit="{{ $prod->unit }}" 
                                    data-price="{{ $prod->price }}"
                                    data-stock="{{ $prod->current_stock }}"
                                >
                                    {{ $prod->name }} (Stock: {{ $prod->current_stock }} {{ $prod->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Unit -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit (UOM)</label>
                        <input 
                            type="text" 
                            name="unit" 
                            x-model="form.unit" 
                            required 
                            placeholder="e.g. kg, liter" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-semibold uppercase"
                        >
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Quantity *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="quantity" 
                            x-model.number="form.quantity" 
                            @input="recalculate()" 
                            required 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs font-bold text-slate-900 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 3: Rate, Total Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rate (₹) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="rate" 
                            x-model.number="form.rate" 
                            @input="recalculate()" 
                            required 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs font-bold text-slate-900 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Total Amount (₹)</label>
                        <input 
                            type="text" 
                            readonly 
                            :value="'₹ ' + computedTotalAmount" 
                            class="w-full px-3.5 py-2.5 text-xs font-black text-emerald-700 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none font-mono text-sm"
                        >
                    </div>
                </div>

                <!-- Row 4: Paid Amount? (Yes / No Toggle) matching reference -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paid Amount?</label>
                    <div class="inline-flex rounded-xl p-1 bg-slate-100 border border-slate-200 text-xs">
                        <button 
                            type="button" 
                            @click="form.is_paid = 0; form.paid_amount = 0; recalculate()" 
                            class="px-5 py-2 rounded-lg font-bold transition"
                            :class="form.is_paid == 0 ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        >
                            No (Credit / Due)
                        </button>
                        <button 
                            type="button" 
                            @click="form.is_paid = 1; if (!form.paid_amount) form.paid_amount = parseFloat(computedTotalAmount); recalculate()" 
                            class="px-5 py-2 rounded-lg font-bold transition"
                            :class="form.is_paid == 1 ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        >
                            Yes (Paid)
                        </button>
                    </div>
                    <input type="hidden" name="is_paid" :value="form.is_paid">
                </div>

                <!-- Row 5: Paid Amount & Payment Mode (shown if Paid Amount? = Yes) -->
                <div x-show="form.is_paid == 1" class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paid Amount (₹) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="paid_amount" 
                            x-model.number="form.paid_amount" 
                            @input="recalculate()" 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs font-bold text-slate-900 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Mode *</label>
                        <select 
                            name="payment_mode" 
                            x-model="form.payment_mode" 
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Ledger">Farmer Ledger / Deduct from Milk</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden fallback for payment_mode if credit -->
                <template x-if="form.is_paid == 0">
                    <input type="hidden" name="payment_mode" value="Ledger">
                </template>

                <!-- Row 6: Remaining Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Remaining Amount (₹)</label>
                    <input 
                        type="text" 
                        readonly 
                        :value="'₹ ' + computedRemainingAmount" 
                        class="w-full px-3.5 py-2.5 text-xs font-bold text-slate-800 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none font-mono"
                    >
                </div>

                <!-- Row 7: Remarks / Note -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Remarks</label>
                    <textarea 
                        name="remarks" 
                        x-model="form.remarks" 
                        rows="2" 
                        placeholder="Optional remarks, e.g. Sold 5 bags of khali to farmer..." 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="button" @click="resetForm()" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">
                        Reset
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span x-text="isEdit ? 'Update Sale' : 'Save Sale'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW SALE PRODUCT DETAILS MODAL                                           -->
    <!-- ========================================================================= -->
    <div 
        x-show="viewModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
        role="dialog" 
        aria-modal="true"
    >
        <div 
            x-show="viewModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
            @click="viewModalOpen = false"
        ></div>

        <div 
            x-show="viewModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
            class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative z-10 my-8"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">View Sale Product</h3>
                    <p class="text-xs font-mono text-emerald-700 mt-0.5" x-text="viewItem ? viewItem.sale_number : ''"></p>
                </div>
                <button type="button" @click="viewModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <template x-if="viewItem">
                <div class="mt-4 space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-xl">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Farmer</span>
                            <span class="font-bold text-slate-900" x-text="viewItem.farmer_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Farmer Code</span>
                            <span class="font-mono font-bold text-slate-800" x-text="viewItem.farmer_code || '—'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 p-3 bg-slate-50 rounded-xl">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Product</span>
                            <span class="font-bold text-slate-900" x-text="viewItem.product_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Quantity</span>
                            <span class="font-mono font-bold text-slate-900" x-text="viewItem.quantity + ' ' + viewItem.unit"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">Rate</span>
                            <span class="font-mono text-slate-800" x-text="'₹' + viewItem.rate"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-800">Total Amount:</span>
                            <span class="font-mono font-bold text-emerald-900 text-sm" x-text="'₹' + viewItem.total_amount"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Paid Amount:</span>
                            <span class="font-mono font-semibold text-blue-700" x-text="'₹' + viewItem.paid_amount"></span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Payment Mode:</span>
                            <span class="font-semibold text-slate-800" x-text="viewItem.payment_mode"></span>
                        </div>
                        <div class="flex justify-between items-center border-t border-emerald-200/60 pt-1 font-bold">
                            <span class="text-slate-800">Remaining Due:</span>
                            <span class="font-mono text-amber-800" x-text="'₹' + viewItem.remaining_amount"></span>
                        </div>
                    </div>

                    <div x-show="viewItem.remarks" class="p-3 bg-slate-50 rounded-xl text-slate-600">
                        <strong class="text-slate-800">Remarks:</strong>
                        <span x-text="viewItem.remarks"></span>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                        <a :href="'{{ url('product-sales') }}/' + viewItem.id + '/print'" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            <span>Print Voucher</span>
                        </a>
                        <button type="button" @click="viewModalOpen = false" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

@push('scripts')
<script>
function saleProductManager() {
    return {
        modalOpen: {{ $errors->any() ? 'true' : 'false' }},
        viewModalOpen: false,
        viewItem: null,
        isEdit: false,
        editId: null,

        form: {
            sale_number: '{{ $nextSaleNumber }}',
            sale_date: '{{ date('Y-m-d') }}',
            farmer_id: '',
            farmer_code: '',
            farmer_name: '',
            product_id: '',
            unit: 'kg',
            quantity: '',
            rate: '',
            is_paid: 1,
            paid_amount: '',
            payment_mode: 'Cash',
            remarks: ''
        },

        init() {
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        get computedTotalAmount() {
            const qty = parseFloat(this.form.quantity) || 0;
            const rate = parseFloat(this.form.rate) || 0;
            return (qty * rate).toFixed(2);
        },

        get computedRemainingAmount() {
            const total = parseFloat(this.computedTotalAmount) || 0;
            const paid = this.form.is_paid == 1 ? (parseFloat(this.form.paid_amount) || 0) : 0;
            return (total - paid).toFixed(2);
        },

        recalculate() {
            // Reactive triggers
        },

        openCreateModal() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openEditModal(item) {
            this.isEdit = true;
            this.editId = item.id;
            this.form = {
                sale_number: item.sale_number || '',
                sale_date: item.sale_date ? item.sale_date.substring(0, 10) : '{{ date('Y-m-d') }}',
                farmer_id: item.farmer_id || '',
                farmer_code: item.farmer_code || (item.farmer ? item.farmer.farmer_code : ''),
                farmer_name: item.farmer_name || '',
                product_id: item.product_id || '',
                unit: item.unit || 'kg',
                quantity: item.quantity || '',
                rate: item.rate || '',
                is_paid: item.is_paid ? 1 : 0,
                paid_amount: item.paid_amount || '',
                payment_mode: item.payment_mode || 'Cash',
                remarks: item.remarks || ''
            };
            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openViewModal(item) {
            this.viewItem = item;
            this.viewModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        resetForm() {
            this.form = {
                sale_number: '{{ $nextSaleNumber }}',
                sale_date: '{{ date('Y-m-d') }}',
                farmer_id: '',
                farmer_code: '',
                farmer_name: '',
                product_id: '',
                unit: 'kg',
                quantity: '',
                rate: '',
                is_paid: 1,
                paid_amount: '',
                payment_mode: 'Cash',
                remarks: ''
            };
        },

        onFarmerChange(farmerId) {
            const select = document.getElementById('sale_farmer_select');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                this.form.farmer_code = selectedOpt.getAttribute('data-code') || '';
                this.form.farmer_name = selectedOpt.getAttribute('data-name') || '';
            } else {
                this.form.farmer_code = '';
                this.form.farmer_name = '';
            }
        },

        onProductChange(productId) {
            const select = document.getElementById('sale_product_select');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                this.form.unit = selectedOpt.getAttribute('data-unit') || 'kg';
                const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                if (!this.form.rate && price > 0) {
                    this.form.rate = price;
                }
            }
            this.recalculate();
        }
    };
}
</script>
@endpush
@endsection
