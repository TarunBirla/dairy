@extends('layouts.app')

@section('title', 'Buy Products')
@section('breadcrumb', 'Buy Products')
@section('header_title', 'Products / Buy Products (Purchases)')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Buy Product</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="buyProductManager()" @open-buy-product-modal.window="openCreateModal()">

    <!-- Navigation Tabs (Products vs Categories vs Buy Products) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            <span>All Products</span>
        </a>
        <a href="{{ route('products.categories.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('purchases.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1.5">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
            <span>Buy Products (Purchases)</span>
        </a>
    </div>

    <!-- Page Title & Subtitle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Buy Products (Purchase Entries)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Record incoming goods from food dealers, cattle feed suppliers, and manage inward inventory balances.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="sm:hidden inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Buy Product</span>
        </button>
    </div>

    <!-- 4 Stat Summary Cards matching theme -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Quantity -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <span>PURCHASE QUANTITY</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900">{{ number_format($totalQuantity, 2) }}</span>
                <span class="text-xs text-slate-400 font-medium ml-1">Total units</span>
            </div>
        </div>

        <!-- Total Purchase Amount -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-400/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                </div>
                <span>TOTAL AMOUNT</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-emerald-700">₹ {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <span>PAID AMOUNT</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-blue-700">₹ {{ number_format($totalPaid, 2) }}</span>
            </div>
        </div>

        <!-- Remaining Due -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
                <span>REMAINING DUES</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-amber-700">₹ {{ number_format($totalRemaining, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar matching theme -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search dealer, code, product..." 
                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                >
            </div>

            <!-- Shift Dropdown -->
            <div class="w-full sm:w-36">
                <select name="shift" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning Shift</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening Shift</option>
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

            @if(request('search') || request('shift') || request('date'))
                <a href="{{ route('purchases.index') }}" class="px-3 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Right action: Download / Print -->
        <div class="flex items-center gap-2 justify-end">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Print List</span>
            </button>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Add Purchase</span>
            </button>
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Shift</th>
                        <th class="py-3.5 px-4">Dealer Code</th>
                        <th class="py-3.5 px-4 min-w-[140px]">Dealer Name</th>
                        <th class="py-3.5 px-4 min-w-[140px]">Item Name</th>
                        <th class="py-3.5 px-4">UOM</th>
                        <th class="py-3.5 px-4 text-right">Quantity</th>
                        <th class="py-3.5 px-4 text-right">Purchase Rate</th>
                        <th class="py-3.5 px-4 text-right">Sale Rate</th>
                        <th class="py-3.5 px-4 text-right">Total Amount</th>
                        <th class="py-3.5 px-4 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $item)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- Date -->
                            <td class="py-4 px-4 font-mono font-medium text-slate-900 whitespace-nowrap">
                                {{ $item->purchase_date->format('d-m-Y') }}
                            </td>

                            <!-- Shift -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($item->shift === 'morning')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Morning
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Evening
                                    </span>
                                @endif
                            </td>

                            <!-- Dealer Code -->
                            <td class="py-4 px-4 font-mono font-bold text-slate-700">
                                {{ $item->dealer_code ?? ($item->dealer ? $item->dealer->code : '—') }}
                            </td>

                            <!-- Dealer Name -->
                            <td class="py-4 px-4 font-bold text-slate-900 capitalize">
                                {{ $item->dealer_name }}
                            </td>

                            <!-- Item Name -->
                            <td class="py-4 px-4 font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ $item->product_name }}</span>
                                </div>
                            </td>

                            <!-- UOM -->
                            <td class="py-4 px-4 text-slate-500 font-medium capitalize">
                                {{ $item->unit }}
                            </td>

                            <!-- Quantity -->
                            <td class="py-4 px-4 text-right font-mono font-bold text-slate-900">
                                {{ number_format($item->quantity, 2) }}
                            </td>

                            <!-- Purchase Rate -->
                            <td class="py-4 px-4 text-right font-mono text-slate-700">
                                ₹{{ number_format($item->rate, 2) }}
                            </td>

                            <!-- Sale Rate -->
                            <td class="py-4 px-4 text-right font-mono text-slate-500">
                                ₹{{ number_format($item->sale_rate ?? 0, 2) }}
                            </td>

                            <!-- Total Amount -->
                            <td class="py-4 px-4 text-right font-mono font-bold text-emerald-700 text-sm whitespace-nowrap">
                                ₹{{ number_format($item->total_amount, 2) }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right pr-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Print Slip -->
                                    <a 
                                        href="{{ route('purchases.print', $item) }}" 
                                        target="_blank"
                                        title="Print Voucher" 
                                        class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition"
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
                                        action="{{ route('purchases.destroy', $item) }}" 
                                        method="POST" 
                                        onsubmit="return confirm('Are you sure you want to delete this purchase entry #{{ $item->purchase_number }}? The stock will be adjusted.')" 
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
                                    <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">No Buy Product entries found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Click "+ Add Purchase" to record new incoming dairy stock.</p>
                                <button type="button" @click="openCreateModal()" class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition inline-flex items-center gap-1.5">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Record Purchase</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-white">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- UNIFIED ADD / EDIT BUY PRODUCT MODAL                                      -->
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
            @click="if (!showNewDealerModal) modalOpen = false"
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
            class="bg-white rounded-2xl max-w-3xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 relative z-10 my-8 max-h-[92vh] overflow-y-auto"
            id="buyProductModalWrapper"
        >
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span x-text="isEdit ? 'Edit Buy Product' : 'Add Buy Product'"></span>
                        <span x-show="isEdit" class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full" x-text="'#' + form.purchase_number"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span x-text="isEdit ? 'Update existing purchase details, pricing and quantity.' : 'Record a new product purchase entry from supplier / food dealer.'"></span>
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
            <form :action="isEdit ? '{{ url('purchases') }}/' + editId : '{{ route('purchases.store') }}'" method="POST" class="mt-5 space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Row 1: Purchase Date & Shift -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Purchase Date *</label>
                        <input 
                            type="date" 
                            name="purchase_date" 
                            x-model="form.purchase_date" 
                            required 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Shift *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-semibold transition"
                                   :class="form.shift === 'morning' ? 'bg-amber-50 border-amber-400 text-amber-800 ring-1 ring-amber-400' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="shift" value="morning" x-model="form.shift" class="sr-only">
                                <span>☀️ Morning</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-semibold transition"
                                   :class="form.shift === 'evening' ? 'bg-indigo-50 border-indigo-400 text-indigo-800 ring-1 ring-indigo-400' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                                <input type="radio" name="shift" value="evening" x-model="form.shift" class="sr-only">
                                <span>🌙 Evening</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Food Dealer & Dealer Code -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Food Dealer Select with + Add new -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Food Dealer / Supplier *</label>
                            <button type="button" @click="openDealerModal()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                                <span>+ Add Dealer</span>
                            </button>
                        </div>
                        <select 
                            name="dealer_id" 
                            id="purchase_dealer_select" 
                            x-model="form.dealer_id" 
                            @change="onDealerChange($event.target.value)" 
                            required
                            class="w-full text-xs px-3.5 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="">Select Food Dealer</option>
                            @foreach($dealers as $dealer)
                                <option value="{{ $dealer->id }}" data-code="{{ $dealer->code }}" data-name="{{ $dealer->name }}">
                                    {{ $dealer->name }} ({{ $dealer->code }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="dealer_name" x-model="form.dealer_name">
                    </div>

                    <!-- Food Dealer Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Dealer Code</label>
                        <input 
                            type="text" 
                            name="dealer_code" 
                            x-model="form.dealer_code" 
                            placeholder="e.g. 001" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 font-mono text-slate-700 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 3: Product & Unit of Measure -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Product -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Item *</label>
                        <select 
                            name="product_id" 
                            id="purchase_product_select" 
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
                                    data-cost="{{ $prod->cost_price }}"
                                    data-price="{{ $prod->price }}"
                                >
                                    {{ $prod->name }} ({{ $prod->unit }}) - Stock: {{ $prod->current_stock }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Unit of Measure -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit (UOM) *</label>
                        <input 
                            type="text" 
                            name="unit" 
                            x-model="form.unit" 
                            required 
                            placeholder="e.g. kg, liter" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none uppercase font-semibold"
                        >
                    </div>
                </div>

                <!-- Row 4: Quantity, Purchase Rate & Sale Rate -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Purchase Rate (₹) *</label>
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
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sale Rate (₹) <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="sale_rate" 
                            x-model.number="form.sale_rate" 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs text-slate-700 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Highlighted Total Amount Box matching reference -->
                <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Total Amount</span>
                        <p class="text-[11px] text-emerald-600 mt-0.5">
                            <span x-text="form.quantity || 0"></span> × ₹<span x-text="form.rate || 0"></span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-emerald-700 font-mono">₹ <span x-text="computedTotalAmount"></span></span>
                    </div>
                </div>

                <!-- Row 5: Paid, Remaining & Advance Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Paid Amount (₹)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="paid_amount" 
                            x-model.number="form.paid_amount" 
                            @input="recalculate()" 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs text-slate-800 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-semibold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Remaining Amount (₹)</label>
                        <input 
                            type="text" 
                            readonly 
                            :value="'₹ ' + computedRemainingAmount" 
                            class="w-full px-3.5 py-2.5 text-xs font-bold text-rose-600 border border-slate-200 rounded-xl bg-slate-50 focus:outline-none font-mono"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Advance Amount (₹)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="advance_amount" 
                            x-model.number="form.advance_amount" 
                            placeholder="0.00" 
                            class="w-full px-3.5 py-2.5 text-xs text-slate-700 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Row 6: Note / Remarks -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Note / Remarks</label>
                    <textarea 
                        name="note" 
                        x-model="form.note" 
                        rows="2" 
                        placeholder="Optional remarks, e.g. Paid cash, morning batch delivered to store..." 
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
                        <span x-text="isEdit ? 'Update Entry' : 'Save Entry'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- SUBMODAL: QUICK ADD DEALER                                                -->
    <!-- ========================================================================= -->
    <div 
        x-show="showNewDealerModal" 
        x-cloak 
        class="fixed inset-0 z-[80] overflow-y-auto flex items-center justify-center p-4" 
        role="dialog" 
        aria-modal="true"
    >
        <div 
            x-show="showNewDealerModal" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
            @click="showNewDealerModal = false"
        ></div>

        <div 
            x-show="showNewDealerModal" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative z-10 my-8"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add New Food Dealer</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Quickly register supplier or food dealer.</p>
                </div>
                <button type="button" @click="showNewDealerModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Dealer Name *</label>
                    <input 
                        type="text" 
                        x-model="newDealer.name" 
                        placeholder="e.g. Akshay Sharma, Ram Traders" 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Phone</label>
                    <input 
                        type="text" 
                        x-model="newDealer.phone" 
                        placeholder="e.g. 9876543210" 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"
                    >
                </div>

                <p x-show="dealerError" x-text="dealerError" class="text-rose-600 text-xs font-medium"></p>
            </div>

            <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-3">
                <button type="button" @click="showNewDealerModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                <button type="button" @click="saveNewDealer()" :disabled="savingDealer" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-xs transition">
                    <span x-show="!savingDealer">Save Dealer</span>
                    <span x-show="savingDealer">Saving...</span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function buyProductManager() {
    return {
        modalOpen: {{ $errors->any() ? 'true' : 'false' }},
        isEdit: false,
        editId: null,

        showNewDealerModal: false,
        savingDealer: false,
        dealerError: '',
        newDealer: {
            name: '',
            phone: ''
        },

        form: {
            purchase_number: '{{ $nextPurchaseNumber }}',
            purchase_date: '{{ date('Y-m-d') }}',
            shift: '{{ $defaultShift }}',
            dealer_id: '',
            dealer_code: '',
            dealer_name: '',
            product_id: '',
            unit: 'kg',
            quantity: '',
            rate: '',
            sale_rate: '',
            paid_amount: '',
            advance_amount: '',
            note: ''
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
            const paid = parseFloat(this.form.paid_amount) || 0;
            return Math.max(0, total - paid).toFixed(2);
        },

        recalculate() {
            // Trigger reactive getters
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
                purchase_number: item.purchase_number || '',
                purchase_date: item.purchase_date ? item.purchase_date.substring(0, 10) : '{{ date('Y-m-d') }}',
                shift: item.shift || 'morning',
                dealer_id: item.dealer_id || '',
                dealer_code: item.dealer_code || (item.dealer ? item.dealer.code : ''),
                dealer_name: item.dealer_name || '',
                product_id: item.product_id || '',
                unit: item.unit || 'kg',
                quantity: item.quantity || '',
                rate: item.rate || '',
                sale_rate: item.sale_rate || '',
                paid_amount: item.paid_amount || '',
                advance_amount: item.advance_amount || '',
                note: item.note || ''
            };
            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        resetForm() {
            this.form = {
                purchase_number: '{{ $nextPurchaseNumber }}',
                purchase_date: '{{ date('Y-m-d') }}',
                shift: '{{ $defaultShift }}',
                dealer_id: '',
                dealer_code: '',
                dealer_name: '',
                product_id: '',
                unit: 'kg',
                quantity: '',
                rate: '',
                sale_rate: '',
                paid_amount: '',
                advance_amount: '',
                note: ''
            };
        },

        onDealerChange(dealerId) {
            const select = document.getElementById('purchase_dealer_select');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                this.form.dealer_code = selectedOpt.getAttribute('data-code') || '';
                this.form.dealer_name = selectedOpt.getAttribute('data-name') || '';
            } else {
                this.form.dealer_code = '';
                this.form.dealer_name = '';
            }
        },

        onProductChange(productId) {
            const select = document.getElementById('purchase_product_select');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                this.form.unit = selectedOpt.getAttribute('data-unit') || 'kg';
                const cost = parseFloat(selectedOpt.getAttribute('data-cost')) || 0;
                const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                if (!this.form.rate && cost > 0) {
                    this.form.rate = cost;
                }
                if (!this.form.sale_rate && price > 0) {
                    this.form.sale_rate = price;
                }
            }
            this.recalculate();
        },

        openDealerModal() {
            this.newDealer = { name: '', phone: '' };
            this.dealerError = '';
            this.showNewDealerModal = true;
        },

        async saveNewDealer() {
            if (!this.newDealer.name.trim()) {
                this.dealerError = 'Dealer name is required.';
                return;
            }
            this.dealerError = '';
            this.savingDealer = true;

            try {
                const response = await fetch('{{ route('purchases.dealers.quick-create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newDealer)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    const d = data.dealer;
                    const select = document.getElementById('purchase_dealer_select');
                    const opt = new Option(`${d.name} (${d.code})`, d.id, true, true);
                    opt.setAttribute('data-code', d.code);
                    opt.setAttribute('data-name', d.name);
                    select.add(opt);

                    this.form.dealer_id = d.id;
                    this.form.dealer_code = d.code;
                    this.form.dealer_name = d.name;

                    this.showNewDealerModal = false;
                } else {
                    this.dealerError = data.message || 'Error saving dealer.';
                }
            } catch (e) {
                this.dealerError = 'Network error. Please try again.';
            } finally {
                this.savingDealer = false;
            }
        }
    };
}
</script>
@endpush
@endsection
