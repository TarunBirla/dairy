@extends('layouts.app')

@section('title', 'Product Stock Management')
@section('breadcrumb', 'Inventory')
@section('header_title', 'Stock Management & Product Inventory')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.stock.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
            <span>Print Stock Sheet</span>
        </a>
        <button type="button" onclick="openTxModal('purchase_inward')" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Movement</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="stockManager()">

    <!-- Level 1: Products Group Navigation Tabs -->
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
        <a href="{{ route('product-sales.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
            <span>Sales Products</span>
        </a>
        <a href="{{ route('inventory.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1.5 whitespace-nowrap">
            <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
            <span>Product Stock</span>
        </a>
    </div>

    <!-- Level 2: Sub-tabs (Stock List vs Movement Ledger vs Bottles) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl w-fit">
            <a href="{{ route('inventory.index', ['tab' => 'stock-list']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ $activeTab === 'stock-list' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                <span>Stock List</span>
            </a>
            <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ $activeTab === 'movements' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                <span>Movement Ledger</span>
            </a>
            <a href="{{ route('inventory.bottles') }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 text-slate-600 hover:text-slate-900">
                <i data-lucide="wine" class="w-3.5 h-3.5"></i>
                <span>Bottle Tracking</span>
            </a>
        </div>

        <div class="text-xs text-slate-500">
            <span>Last Updated: <b>{{ now()->format('d M, h:i A') }}</b></span>
        </div>
    </div>

    <!-- Stat Summary Cards matching emerald/slate theme -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Total Products Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold tracking-wider uppercase">
                <span>Total Products</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-2xl font-black text-slate-900">{{ $totalProductsCount }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Active Catalog Items</span>
            </div>
        </div>

        <!-- Total Inward Quantity Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold tracking-wider uppercase">
                <span>Quantity (Inward)</span>
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-2xl font-black text-sky-700">{{ number_format($totalInwardOverall, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Purchased / Added</span>
            </div>
        </div>

        <!-- Total Sold Quantity Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold tracking-wider uppercase">
                <span>Sold Product</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-2xl font-black text-amber-700">{{ number_format($totalSoldQty, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Farmers & POS Sales</span>
            </div>
        </div>

        <!-- Total Stock Valuation Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold tracking-wider uppercase">
                <span>Stock Valuation</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-2xl font-black text-emerald-700">₹{{ number_format($totalStockValuation, 2) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ number_format($totalCurrentStock, 2) }} Units in Stock</span>
            </div>
        </div>

        <!-- Low Stock / Out of Stock Alert Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-[11px] font-bold tracking-wider uppercase">
                <span>Low / Out of Stock</span>
                <div class="w-7 h-7 rounded-lg {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2.5">
                <span class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $lowStockCount }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Need Restocking</span>
            </div>
        </div>
    </div>

    @if($activeTab === 'stock-list')
        <!-- ============================================================== -->
        <!-- TAB 1: STOCK LIST (MATCHING REFERENCE UI)                      -->
        <!-- ============================================================== -->

        <!-- Filters Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
            <form action="{{ route('inventory.index') }}" method="GET" class="space-y-3">
                <input type="hidden" name="tab" value="stock-list">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <!-- Search Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Product</label>
                        <div class="relative">
                            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or code..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Category</label>
                        <select name="category_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stock Status Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Stock Status</label>
                        <select name="stock_status" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">All Stock Status</option>
                            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (> 0)</option>
                            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (≤ Alert Limit)</option>
                            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock / Zero (≤ 0)</option>
                            <option value="negative" {{ request('stock_status') === 'negative' ? 'selected' : '' }}>Negative Stock (< 0)</option>
                        </select>
                    </div>

                    <!-- Product Audience Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Product For</label>
                        <select name="product_for" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">All Audiences</option>
                            <option value="farmer" {{ request('product_for') === 'farmer' ? 'selected' : '' }}>Farmer</option>
                            <option value="customer" {{ request('product_for') === 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="both" {{ request('product_for') === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Sort By</label>
                        <select name="sort" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Product Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Product Name (Z-A)</option>
                            <option value="stock_desc" {{ request('sort') === 'stock_desc' ? 'selected' : '' }}>Highest Stock First</option>
                            <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>Lowest Stock First</option>
                            <option value="rate_desc" {{ request('sort') === 'rate_desc' ? 'selected' : '' }}>Highest Sale Rate</option>
                            <option value="buy_desc" {{ request('sort') === 'buy_desc' ? 'selected' : '' }}>Highest Buy Rate</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('inventory.index', ['tab' => 'stock-list']) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset Filters
                    </a>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Stock List Table Card matching screenshot columns -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Stock List</h3>
                        <p class="text-[11px] text-slate-400">Inventory items with inward total, sold count, net stock balance, and rates</p>
                    </div>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                    Showing {{ $stockProducts->count() }} of {{ $stockProducts->total() }} items
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th class="py-3 px-4 text-center w-14">S.No</th>
                            <th class="py-3 px-4">
                                <span class="inline-flex items-center gap-1">
                                    Product Name
                                    <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-center">Quantity</th>
                            <th class="py-3 px-4 text-center">Sold Product</th>
                            <th class="py-3 px-4 text-center">Stock</th>
                            <th class="py-3 px-4 text-center">Buy Rate</th>
                            <th class="py-3 px-4 text-center">Sale Rate</th>
                            <th class="py-3 px-4 text-center w-28">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stockProducts as $index => $item)
                            <tr class="hover:bg-slate-50/60 transition group">
                                <!-- S.No -->
                                <td class="py-3 px-4 text-center font-semibold text-slate-500">
                                    {{ $stockProducts->firstItem() + $index }}
                                </td>

                                <!-- Product Name -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <a href="javascript:void(0)" @click="openViewModal({{ $item->id }})" class="font-bold text-slate-900 hover:text-emerald-600 transition block">
                                                {{ $item->name }}
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if($item->code)
                                                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.2 rounded">{{ $item->code }}</span>
                                                @endif
                                                @if($item->category)
                                                    <span class="text-[10px] text-slate-500">{{ $item->category->name }}</span>
                                                @endif
                                                <span class="text-[10px] text-slate-400">• {{ $item->unit }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Quantity (Total Inward) -->
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ number_format($item->inward_qty, 2) }}
                                </td>

                                <!-- Sold Product (Total Sold) -->
                                <td class="py-3 px-4 text-center font-bold text-slate-700">
                                    {{ number_format($item->sold_qty, 2) }}
                                </td>

                                <!-- Stock (Balance) -->
                                <td class="py-3 px-4 text-center">
                                    @if($item->stock_balance > 0)
                                        <span class="font-bold text-emerald-600">
                                            {{ number_format($item->stock_balance, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-700">
                                            {{ number_format($item->stock_balance, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Buy Rate -->
                                <td class="py-3 px-4 text-center font-medium text-slate-700">
                                    Rs. {{ number_format($item->cost_price, 2) }}
                                </td>

                                <!-- Sale Rate (Highlighted in red/bold like screenshot) -->
                                <td class="py-3 px-4 text-center font-bold text-rose-600">
                                    Rs. {{ number_format($item->price, 2) }}
                                </td>

                                <!-- Action Buttons (Edit + View) -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Stock Item Button -->
                                        <button type="button" 
                                                @click="openEditModal({
                                                    id: {{ $item->id }},
                                                    name: '{{ addslashes($item->name) }}',
                                                    code: '{{ addslashes($item->code ?? '') }}',
                                                    unit: '{{ addslashes($item->unit) }}',
                                                    dealer_id: {{ $item->dealer_id ? $item->dealer_id : 'null' }},
                                                    dealer_code: '{{ addslashes($item->dealer_code ?? '') }}',
                                                    dealer_name: '{{ addslashes($item->dealer_name ?? '') }}',
                                                    quantity: {{ (float) $item->inward_qty }},
                                                    sold: {{ (float) $item->sold_qty }},
                                                    stock: {{ (float) $item->stock_balance }},
                                                    buy_rate: {{ (float) $item->cost_price }},
                                                    sale_rate: {{ (float) $item->price }},
                                                    paid_amount: {{ (float) $item->paid_amount }}
                                                })"
                                                title="Edit Stock Item"
                                                class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition shadow-xs">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- View Details Button -->
                                        <button type="button" 
                                                @click="openViewModal({{ $item->id }})"
                                                title="View Stock Details"
                                                class="w-7 h-7 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 flex items-center justify-center transition shadow-xs">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                            <i data-lucide="package-search" class="w-6 h-6"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-600">No stock items found</span>
                                        <p class="text-xs text-slate-400 mt-0.5">Try adjusting your search criteria or add new inventory.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stockProducts->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $stockProducts->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- ============================================================== -->
        <!-- TAB 2: MOVEMENT LEDGER (AUDIT TRAIL)                           -->
        <!-- ============================================================== -->

        <!-- Movements Filter -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
            <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="movements">

                <div class="min-w-[160px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Movement Type</label>
                    <select name="tx_type" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">All Movement Types</option>
                        <option value="inward" {{ request('tx_type') === 'inward' ? 'selected' : '' }}>Inward Stock (+)</option>
                        <option value="outward" {{ request('tx_type') === 'outward' ? 'selected' : '' }}>Outward Stock (-)</option>
                        <option value="adjustment" {{ request('tx_type') === 'adjustment' ? 'selected' : '' }}>Adjustments</option>
                        <option value="wastage" {{ request('tx_type') === 'wastage' ? 'selected' : '' }}>Wastage / Spoilage</option>
                    </select>
                </div>

                <div class="min-w-[180px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Filter by Product</label>
                    <select name="tx_product_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">All Products</option>
                        @foreach($allActiveProducts as $pr)
                            <option value="{{ $pr->id }}" {{ request('tx_product_id') == $pr->id ? 'selected' : '' }}>
                                {{ $pr->name }} ({{ $pr->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 pt-5">
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition">
                        Filter Movements
                    </button>
                    <a href="{{ route('inventory.index', ['tab' => 'movements']) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Complete Stock Ledger & Audit Trail</h3>
                <span class="text-xs text-slate-400">Showing {{ $transactions->total() }} transactions</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4">Product Name</th>
                            <th class="py-3 px-4">Transaction Type</th>
                            <th class="py-3 px-4">Quantity</th>
                            <th class="py-3 px-4">Balance After</th>
                            <th class="py-3 px-4">Recorded By</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-mono">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $tx->product ? $tx->product->name : 'Product' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                        {{ str_contains($tx->transaction_type, 'inward') ? 'bg-emerald-100 text-emerald-800' : (str_contains($tx->transaction_type, 'wastage') ? 'bg-rose-100 text-rose-800' : (str_contains($tx->transaction_type, 'adjustment') ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                                        {{ str_replace('_', ' ', $tx->transaction_type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-black {{ str_contains($tx->transaction_type, 'inward') ? 'text-emerald-700' : 'text-slate-800' }}">
                                    {{ str_contains($tx->transaction_type, 'inward') ? '+' : '-' }}{{ $tx->quantity }} {{ $tx->product ? $tx->product->unit : '' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $tx->balance_after }} {{ $tx->product ? $tx->product->unit : '' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $tx->recorder ? $tx->recorder->name : 'System' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                    {{ $tx->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">No inventory transactions recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- EDIT STOCK ITEM MODAL (MATCHING REFERENCE SCREENSHOT)          -->
    <!-- ============================================================== -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @keydown.escape.window="closeEditModal()">

        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 relative my-8"
             @click.away="closeEditModal()">

            <!-- Modal Header matching existing theme -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Edit Stock Item</h3>
                        <p class="text-xs text-slate-400">Update item pricing, inward balance, dealer, and stock quantities</p>
                    </div>
                </div>
                <button type="button" @click="closeEditModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="editFormAction" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Dealer Selection & Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Dealer / Supplier
                        </label>
                        <select name="dealer_id" id="edit_dealer_select" x-model="editItem.dealer_id" @change="onDealerChange($event)" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">No Associated Dealer</option>
                            @foreach($dealers as $dl)
                                <option value="{{ $dl->id }}" data-code="{{ $dl->code }}" data-name="{{ $dl->name }}">
                                    {{ $dl->name }} ({{ $dl->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Dealer Code
                        </label>
                        <input type="text" name="dealer_code" x-model="editItem.dealer_code" placeholder="e.g. 001"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Product Name (Read-only display) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Product Name
                        </label>
                        <div class="flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                            <div>
                                <span class="text-xs font-bold text-slate-900" x-text="editItem.name"></span>
                                <span class="text-[11px] text-slate-400 block font-mono" x-text="'Code: ' + (editItem.code || 'N/A') + ' • Unit: ' + editItem.unit"></span>
                            </div>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold uppercase">Locked</span>
                        </div>
                    </div>

                    <!-- Quantity (Purchased / Inward) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Quantity (Total Inward) *
                        </label>
                        <input type="number" step="0.01" name="inward_quantity" x-model.number="editItem.quantity" @input="recalcStock()" required
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Total purchased/received units</span>
                    </div>

                    <!-- Sold Product -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Sold Product
                        </label>
                        <input type="number" step="0.01" name="sold_quantity" x-model.number="editItem.sold" @input="recalcStock()"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Outflow across farmers & sales</span>
                    </div>

                    <!-- Stock Balance (Auto calculated or directly adjustable) -->
                    <div class="sm:col-span-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-800">
                                Current Stock Balance *
                            </label>
                            <span class="text-[11px] font-mono" :class="editItem.stock > 0 ? 'text-emerald-700 font-bold' : 'text-rose-700 font-bold'">
                                Formula: Quantity (<span x-text="editItem.quantity || 0"></span>) - Sold (<span x-text="editItem.sold || 0"></span>) = <span x-text="editItem.stock"></span>
                            </span>
                        </div>
                        <input type="number" step="0.01" name="current_stock" x-model.number="editItem.stock" required
                               class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg font-black focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                               :class="editItem.stock > 0 ? 'text-emerald-800' : 'text-rose-700'">
                    </div>

                    <!-- Buy Rate (Cost Price) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Buy Rate (₹ Purchase Rate) *
                        </label>
                        <input type="number" step="0.01" name="cost_price" x-model.number="editItem.buy_rate" required
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Sale Rate (Selling Price) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Sale Rate (₹ Selling Price) *
                        </label>
                        <input type="number" step="0.01" name="price" x-model.number="editItem.sale_rate" required
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold text-rose-600 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Paid Amount to Supplier -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Paid Amount (₹ to Dealer)
                        </label>
                        <input type="number" step="0.01" name="paid_amount" x-model.number="editItem.paid_amount"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Reason / Adjustment Notes -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Adjustment Reason / Audit Notes
                        </label>
                        <input type="text" name="notes" placeholder="e.g. Physical inventory reconciliation / Rate revision"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="closeEditModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- VIEW DETAILS MODAL (EYE ICON)                                  -->
    <!-- ============================================================== -->
    <div x-show="showViewModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @keydown.escape.window="closeViewModal()">

        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 relative my-8"
             @click.away="closeViewModal()">

            <!-- View Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="viewData.product?.name || 'Product Details'"></h3>
                        <p class="text-xs text-slate-400 font-mono" x-text="'Code: ' + (viewData.product?.code || 'N/A') + ' • Category: ' + (viewData.product?.category || 'General')"></p>
                    </div>
                </div>
                <button type="button" @click="closeViewModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <template x-if="isLoadingView">
                <div class="py-12 flex flex-col items-center justify-center text-slate-400">
                    <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-emerald-600 mb-2"></i>
                    <span class="text-xs font-semibold">Loading stock ledger details...</span>
                </div>
            </template>

            <template x-if="!isLoadingView && viewData.product">
                <div class="mt-4 space-y-4">
                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Quantity (Inward)</span>
                            <span class="text-base font-black text-slate-900 mt-1 block" x-text="Number(viewData.product.inward_quantity).toFixed(2) + ' ' + viewData.product.unit"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Sold</span>
                            <span class="text-base font-black text-slate-900 mt-1 block" x-text="Number(viewData.product.total_sold).toFixed(2) + ' ' + viewData.product.unit"></span>
                            <span class="text-[9px] text-slate-400 block" x-text="'Farmer: ' + viewData.product.farmer_sold + ' | POS: ' + viewData.product.pos_sold"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Current Stock</span>
                            <span class="text-base font-black mt-1 block" 
                                  :class="viewData.product.current_stock > 0 ? 'text-emerald-700' : 'text-rose-600'"
                                  x-text="Number(viewData.product.current_stock).toFixed(2) + ' ' + viewData.product.unit"></span>
                        </div>
                        <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Stock Valuation</span>
                            <span class="text-base font-black text-emerald-800 mt-1 block" x-text="'₹' + Number(viewData.product.stock_value).toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Pricing & Rates -->
                    <div class="grid grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Buy Rate (Purchase Cost):</span>
                            <span class="text-sm font-bold text-slate-800" x-text="'₹' + Number(viewData.product.cost_price).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Sale Rate (Selling Price):</span>
                            <span class="text-sm font-bold text-rose-600" x-text="'₹' + Number(viewData.product.price).toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Recent Movements for this Product</h4>
                        <div class="max-h-48 overflow-y-auto border border-slate-200/80 rounded-xl divide-y divide-slate-100">
                            <template x-for="tx in viewData.recent_transactions" :key="tx.id">
                                <div class="p-2.5 flex items-center justify-between text-xs hover:bg-slate-50">
                                    <div>
                                        <span class="font-semibold text-slate-800 uppercase text-[10px] px-1.5 py-0.5 rounded"
                                              :class="tx.transaction_type.includes('inward') ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'"
                                              x-text="tx.transaction_type.replace('_', ' ')"></span>
                                        <span class="text-[10px] text-slate-400 ml-2" x-text="new Date(tx.created_at).toLocaleDateString() + ' ' + new Date(tx.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold" :class="tx.transaction_type.includes('inward') ? 'text-emerald-700' : 'text-slate-800'"
                                              x-text="(tx.transaction_type.includes('inward') ? '+' : '-') + Number(tx.quantity).toFixed(2)"></span>
                                        <span class="text-[10px] text-slate-400 block" x-text="'Balance: ' + Number(tx.balance_after).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!viewData.recent_transactions || viewData.recent_transactions.length === 0">
                                <div class="p-4 text-center text-xs text-slate-400">No recent movement transactions.</div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100">
                        <button type="button" @click="closeViewModal()" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Quick Movement Modal (Preserved from original implementation) -->
    <div id="txModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800" id="txModalTitle">Record Stock Movement</h3>
                <button type="button" onclick="document.getElementById('txModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            <form action="{{ route('inventory.transaction') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        @foreach($allActiveProducts as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->name }} (Available: {{ $pr->current_stock }} {{ $pr->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Movement Type *</label>
                    <select name="transaction_type" id="txTypeSelect" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="purchase_inward">Buy Product / Purchase Inward (+ Stock)</option>
                        <option value="production_inward">Production Inward (+ Stock)</option>
                        <option value="sale_outward">Sales Product / Outward (- Stock)</option>
                        <option value="wastage">Spoilage / Wastage (- Stock)</option>
                        <option value="adjustment">Stock Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                    <input type="number" step="0.1" name="quantity" required placeholder="e.g. 20" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Cost Price (₹)</label>
                    <input type="number" step="0.5" name="unit_cost" placeholder="Optional purchase rate" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Reason</label>
                    <input type="text" name="notes" placeholder="e.g. Purchase order #34 / Wholesale inward" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('txModal').classList.add('hidden')" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Record Entry</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function stockManager() {
    return {
        showEditModal: false,
        showViewModal: false,
        isLoadingView: false,
        editFormAction: '',
        editItem: {
            id: null,
            name: '',
            code: '',
            unit: 'kg',
            dealer_id: null,
            dealer_code: '',
            dealer_name: '',
            quantity: 0,
            sold: 0,
            stock: 0,
            buy_rate: 0,
            sale_rate: 0,
            paid_amount: 0
        },
        viewData: {
            product: null,
            recent_purchases: [],
            recent_sales: [],
            recent_transactions: []
        },

        openEditModal(item) {
            this.editItem = { ...item };
            this.editFormAction = `/inventory/stock/${item.id}`;
            this.showEditModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeEditModal() {
            this.showEditModal = false;
        },

        onDealerChange(event) {
            const selectedOpt = event.target.selectedOptions[0];
            if (selectedOpt && selectedOpt.dataset.code) {
                this.editItem.dealer_code = selectedOpt.dataset.code;
            } else {
                this.editItem.dealer_code = '';
            }
        },

        recalcStock() {
            const qty = parseFloat(this.editItem.quantity) || 0;
            const sold = parseFloat(this.editItem.sold) || 0;
            this.editItem.stock = parseFloat((qty - sold).toFixed(2));
        },

        async openViewModal(productId) {
            this.showViewModal = true;
            this.isLoadingView = true;
            try {
                const response = await fetch(`/inventory/stock/${productId}/details`);
                const data = await response.json();
                if (data.success) {
                    this.viewData = data;
                }
            } catch (err) {
                console.error('Error fetching stock details:', err);
            } finally {
                this.isLoadingView = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        closeViewModal() {
            this.showViewModal = false;
            this.viewData = { product: null, recent_purchases: [], recent_sales: [], recent_transactions: [] };
        }
    };
}

function openTxModal(type) {
    const modal = document.getElementById('txModal');
    const select = document.getElementById('txTypeSelect');
    const title = document.getElementById('txModalTitle');
    if (select && type) {
        select.value = type;
        if (type === 'purchase_inward') {
            title.innerText = 'Buy Product / Inward Stock';
        } else if (type === 'sale_outward') {
            title.innerText = 'Sales Product / Outward Stock';
        } else {
            title.innerText = 'Record Stock Movement';
        }
    }
    if (modal) {
        modal.classList.remove('hidden');
    }
}
</script>
@endsection
