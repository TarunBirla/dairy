@extends('layouts.app')

@section('title', 'Products')
@section('breadcrumb', 'Products')
@section('header_title', 'Products / Product list')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add product</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="productListingPage()" @open-add-product.window="openCreateModal()">

    <!-- Navigation Tabs (Products vs Categories) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1.5">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            <span>All Products</span>
        </a>
        <a href="{{ route('products.categories.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Categories</span>
        </a>
    </div>

    <!-- Page Title & Subtitle matching screenshot -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Products</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage your product catalog, prices, and stock availability.</p>
        </div>
        <button type="button" @click="openCreateModal()" class="sm:hidden inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add product</span>
        </button>
    </div>

    <!-- 3 Stat Summary Cards matching screenshot -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Total Products Card -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-400/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                </div>
                <span>TOTAL PRODUCTS</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalProducts }}</span>
            </div>
        </div>

        <!-- In Stock Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                </div>
                <span>IN STOCK</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $inStockCount }}</span>
            </div>
        </div>

        <!-- Out of Stock Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                </div>
                <span>OUT OF STOCK</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-amber-700">{{ $outOfStockCount }}</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar matching application theme -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Search Input -->
        <form method="GET" action="{{ route('products.index') }}" class="w-full md:w-80 flex items-center relative">
            @if(request('product_for'))
                <input type="hidden" name="product_for" value="{{ request('product_for') }}">
            @endif
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            @if(request('stock_status'))
                <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
            @endif
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search product, category, description..." 
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
            >
        </form>

        <!-- Product For Filter (Themed UI) -->
        <form id="productForFilterForm" method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-2.5 text-xs">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if(request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            @if(request('stock_status'))
                <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
            @endif

            @php
                $currentProductFor = request('product_for', 'all');
            @endphp

            <span class="font-bold text-slate-600 uppercase tracking-wider text-[11px] mr-1 flex items-center gap-1.5">
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Product For:</span>
            </span>

            <div class="inline-flex items-center gap-3.5 bg-slate-50 border border-slate-200 px-3.5 py-1.5 rounded-xl">
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none group">
                    <input type="radio" name="product_for" value="all" {{ ($currentProductFor === 'all' || !$currentProductFor) ? 'checked' : '' }} onchange="this.form.submit()" class="w-3.5 h-3.5 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                    <span class="text-xs transition {{ ($currentProductFor === 'all' || !$currentProductFor) ? 'font-bold text-emerald-700' : 'text-slate-600 group-hover:text-slate-900' }}">All</span>
                </label>

                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none group">
                    <input type="radio" name="product_for" value="farmer" {{ $currentProductFor === 'farmer' ? 'checked' : '' }} onchange="this.form.submit()" class="w-3.5 h-3.5 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                    <span class="text-xs transition {{ $currentProductFor === 'farmer' ? 'font-bold text-emerald-700' : 'text-slate-600 group-hover:text-slate-900' }}">Farmer</span>
                </label>

                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none group">
                    <input type="radio" name="product_for" value="customer" {{ $currentProductFor === 'customer' ? 'checked' : '' }} onchange="this.form.submit()" class="w-3.5 h-3.5 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                    <span class="text-xs transition {{ $currentProductFor === 'customer' ? 'font-bold text-emerald-700' : 'text-slate-600 group-hover:text-slate-900' }}">Customer</span>
                </label>

                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none group">
                    <input type="radio" name="product_for" value="both" {{ $currentProductFor === 'both' ? 'checked' : '' }} onchange="this.form.submit()" class="w-3.5 h-3.5 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                    <span class="text-xs transition {{ $currentProductFor === 'both' ? 'font-bold text-emerald-700' : 'text-slate-600 group-hover:text-slate-900' }}">Both</span>
                </label>
            </div>
        </form>

        <div class="flex items-center gap-2 justify-end">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs">
                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Download</span>
            </button>
        </div>
    </div>

    <!-- Products Data Table matching application theme -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Product</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4 min-w-[240px]">Description</th>
                        <th class="py-3.5 px-4 text-center">Stock</th>
                        <th class="py-3.5 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $index => $product)
                        <tr class="hover:bg-slate-50/80 transition group" id="product-row-{{ $product->id }}">
                            <!-- # -->
                            <td class="py-4 px-4 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Product -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 flex-shrink-0">
                                        <i data-lucide="package" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-slate-900 text-sm leading-tight">{{ $product->name }}</p>
                                            @if(($product->product_for ?? 'customer') === 'farmer')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Farmer
                                                </span>
                                            @elseif(($product->product_for ?? 'customer') === 'both')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Both
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Customer
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400 font-medium">
                                            {{ $product->category ? $product->category->name : $product->product_type }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="py-4 px-4 font-bold text-slate-900 text-sm whitespace-nowrap">
                                ₹ {{ number_format($product->price, 0) }}
                            </td>

                            <!-- Unit -->
                            <td class="py-4 px-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $product->unit }}
                            </td>

                            <!-- Description -->
                            <td class="py-4 px-4 text-slate-500 text-xs">
                                {{ $product->description ? $product->description : '—' }}
                            </td>

                            <!-- Stock (Badge / Toggle Switch) -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($product->current_stock > 0)
                                        <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                                            {{ (float)$product->current_stock }} {{ $product->unit }}
                                        </span>
                                    @endif
                                    
                                    <!-- Toggle Switch matching screenshot -->
                                    <button 
                                        type="button" 
                                        onclick="toggleStockStatus({{ $product->id }})"
                                        id="toggle-btn-{{ $product->id }}"
                                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $product->in_stock ? 'bg-emerald-500' : 'bg-slate-200' }}"
                                        role="switch" 
                                        aria-checked="{{ $product->in_stock ? 'true' : 'false' }}"
                                    >
                                        <span 
                                            id="toggle-dot-{{ $product->id }}"
                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $product->in_stock ? 'translate-x-4' : 'translate-x-0' }}"
                                        ></span>
                                    </button>
                                </div>
                            </td>

                            <!-- Actions matching screenshot (Edit, Delete, + Button) -->
                            <td class="py-4 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Edit Icon -->
                                    <a href="{{ route('products.edit', $product) }}" title="Edit Product" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Delete Icon -->
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Product" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    <!-- Quick Stock + Button matching green circular + button in screenshot -->
                                    <button 
                                        type="button" 
                                        @click="quickProduct = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', unit: '{{ $product->unit }}' }; quickModalOpen = true;"
                                        title="Quick Stock In"
                                        class="w-7 h-7 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-xs transition transform hover:scale-105"
                                    >
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">No products found</p>
                                <p class="text-xs text-slate-400 mt-1">Get started by creating your first dairy product.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- ================= ADD PRODUCT MODAL POPUP ================= -->
    <div 
        x-show="createModalOpen" 
        x-cloak 
        id="addProductModalWrapper"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        aria-labelledby="add-product-title" 
        role="dialog" 
        aria-modal="true"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            @click.away="if (!showCategoryModal && !showUnitModal) createModalOpen = false"
            x-show="createModalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="bg-white rounded-2xl max-w-2xl sm:max-w-3xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-6 max-h-[92vh] overflow-y-auto relative"
        >
            <!-- Modal Header -->
            <div class="border-b border-slate-100 pb-4 mb-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="add-product-title">Add New Product</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Configure product details, pack size, pricing, and initial stock.</p>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-xs text-slate-400 hover:text-slate-600 font-medium px-2 py-1 rounded-lg hover:bg-slate-100 transition">Cancel</button>
            </div>

            <!-- Validation Errors Banner -->
            @if($errors->any())
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Add Product Form -->
            <form action="{{ route('products.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Product For Audience Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product For *</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label class="relative flex items-center justify-center gap-2 px-3 py-2.5 border rounded-xl cursor-pointer text-xs font-semibold transition"
                               :class="selectedProductFor === 'farmer' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 ring-1 ring-emerald-500 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" name="product_for" value="farmer" x-model="selectedProductFor" class="sr-only">
                            <i data-lucide="tractor" class="w-4 h-4"></i>
                            <span>Farmer</span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 px-3 py-2.5 border rounded-xl cursor-pointer text-xs font-semibold transition"
                               :class="selectedProductFor === 'customer' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-1 ring-blue-500 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" name="product_for" value="customer" x-model="selectedProductFor" class="sr-only">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Customer</span>
                        </label>
                        <label class="relative flex items-center justify-center gap-2 px-3 py-2.5 border rounded-xl cursor-pointer text-xs font-semibold transition"
                               :class="selectedProductFor === 'both' ? 'bg-purple-50 border-purple-500 text-purple-700 ring-1 ring-purple-500 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                            <input type="radio" name="product_for" value="both" x-model="selectedProductFor" class="sr-only">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Both</span>
                        </label>
                    </div>
                </div>

                <!-- Product Name & Code -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Name *</label>
                        <input type="text" name="name" required placeholder="e.g. सादा दूध / Fresh Paneer / Cow Milk" value="{{ old('name') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Code / SKU *</label>
                        <input type="text" name="code" required value="{{ old('code', $nextCode ?? 'PRD-001') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-600 focus:outline-none">
                    </div>
                </div>

                <!-- Category & Unit Section with Select2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Category with Select2 + Add new -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Product Category <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <button type="button" @click="openCategorySubmodal()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                                <span>+ Add new</span>
                            </button>
                        </div>
                        <div class="w-full">
                            <select 
                                name="category_id" 
                                id="product_category_select" 
                                class="w-full text-xs"
                                data-placeholder="Select Category"
                            >
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Unit of Measure with Select2 + Add new -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Unit of Measure *</label>
                            <button type="button" @click="openUnitSubmodal()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                                <span>+ Add new</span>
                            </button>
                        </div>
                        <div class="w-full">
                            <select 
                                name="unit" 
                                id="product_unit_select" 
                                required 
                                class="w-full text-xs"
                                data-placeholder="Select Unit"
                            >
                                <option value="liter" {{ old('unit', 'liter') == 'liter' ? 'selected' : '' }}>Liter (ltr)</option>
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>Bottle</option>
                                <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>Piece / Bag</option>
                                <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack / Pouch</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Prices -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Retail Price (₹) *</label>
                        <input type="number" step="0.5" name="price" required placeholder="50.00" value="{{ old('price') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subscription Price (₹)</label>
                        <input type="number" step="0.5" name="subscription_price" placeholder="48.00" value="{{ old('subscription_price') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cost Price (₹)</label>
                        <input type="number" step="0.5" name="cost_price" placeholder="40.00" value="{{ old('cost_price') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Stock & Alert Threshold -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Initial Opening Stock</label>
                        <input type="number" step="0.1" name="current_stock" placeholder="0" value="{{ old('current_stock', 0) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Low Stock Alert Threshold</label>
                        <input type="number" step="1" name="min_stock_alert" placeholder="5" value="{{ old('min_stock_alert', 5) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Description / Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description / Notes</label>
                    <textarea name="description" rows="2" placeholder="e.g. Pure cow milk from daily morning procurement" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">{{ old('description') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= SUBMODAL: ADD CATEGORY (Elevated z-[80]) ================= -->
    <div 
        x-show="showCategoryModal" 
        x-cloak 
        class="fixed inset-0 z-[80] overflow-y-auto flex items-center justify-center p-4" 
        aria-labelledby="modal-category-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Distinct Backdrop -->
        <div 
            x-show="showCategoryModal" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
            @click="showCategoryModal = false"
        ></div>

        <!-- Modal Dialog -->
        <div 
            x-show="showCategoryModal" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative z-10 my-8"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="modal-category-title">Add product category</h3>
                    <p class="text-xs text-slate-500 mt-1">Categories help group similar products on bills and catalogue.</p>
                </div>
                <button type="button" @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category name *</label>
                    <input 
                        type="text" 
                        x-model="newCategoryName" 
                        id="new_category_input"
                        @keydown.enter.prevent="saveCategory()" 
                        placeholder="e.g. Milk, Curd, Ghee, Sweets" 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                    >
                    <p x-show="categoryError" x-text="categoryError" class="text-rose-600 text-[11px] mt-1"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea 
                        x-model="newCategoryDescription" 
                        rows="2" 
                        placeholder="Brief details about products in this category..." 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-3">
                <button type="button" @click="showCategoryModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                <button type="button" @click="saveCategory()" :disabled="savingCategory" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <span x-show="!savingCategory">Save category</span>
                    <span x-show="savingCategory">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================= SUBMODAL: ADD UNIT (Elevated z-[80]) ================= -->
    <div 
        x-show="showUnitModal" 
        x-cloak 
        class="fixed inset-0 z-[80] overflow-y-auto flex items-center justify-center p-4" 
        aria-labelledby="modal-unit-title" 
        role="dialog" 
        aria-modal="true"
    >
        <div 
            x-show="showUnitModal" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
            @click="showUnitModal = false"
        ></div>

        <div 
            x-show="showUnitModal" 
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative z-10 my-8"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="modal-unit-title">Add unit of measure</h3>
                    <p class="text-xs text-slate-500 mt-1">Specify unit label and symbol for packaging or billing.</p>
                </div>
                <button type="button" @click="showUnitModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit name / label *</label>
                    <input 
                        type="text" 
                        x-model="newUnitLabel" 
                        id="new_unit_input"
                        @keydown.enter.prevent="saveUnit()" 
                        placeholder="e.g. 500ml Pouch, Jar, Can, Box" 
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"
                    >
                    <p x-show="unitError" x-text="unitError" class="text-rose-600 text-[11px] mt-1"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-3">
                <button type="button" @click="showUnitModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                <button type="button" @click="saveUnit()" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save unit</button>
            </div>
        </div>
    </div>

    <!-- Quick Stock In Modal -->
    <div x-show="quickModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="quickModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Quick Stock In</h3>
                <button @click="quickModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'{{ url('products') }}/' + (quickProduct ? quickProduct.id : '') + '/quick-stock'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product</label>
                    <p class="text-sm font-bold text-emerald-700" x-text="quickProduct ? quickProduct.name : ''"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Add Quantity</label>
                    <div class="relative">
                        <input type="number" step="0.1" name="quantity" required placeholder="e.g. 10" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <span class="absolute right-3 top-2 text-xs font-semibold text-slate-400" x-text="quickProduct ? quickProduct.unit : ''"></span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Morning production batch" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="quickModalOpen = false" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Add to Stock</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function productListingPage() {
        return {
            createModalOpen: {{ (request('open_create') || $errors->any()) ? 'true' : 'false' }},
            quickModalOpen: false,
            quickProduct: null,
            selectedProductFor: '{{ old('product_for', 'customer') }}',

            showCategoryModal: false,
            showUnitModal: false,
            newCategoryName: '',
            newCategoryDescription: '',
            categoryError: '',
            savingCategory: false,

            newUnitLabel: '',
            unitError: '',

            init() {
                if (this.createModalOpen) {
                    this.initModalSelect2();
                }
            },

            openCreateModal() {
                this.createModalOpen = true;
                this.initModalSelect2();
            },

            initModalSelect2() {
                setTimeout(() => {
                    if (window.lucide) window.lucide.createIcons();
                    
                    if (window.jQuery && jQuery.fn.select2) {
                        const $modalParent = $('#addProductModalWrapper');

                        // Initialize Category Select2
                        const $catSelect = $('#product_category_select');
                        if ($catSelect.length) {
                            if ($catSelect.hasClass('select2-hidden-accessible')) {
                                $catSelect.select2('destroy');
                            }
                            
                            $catSelect.select2({
                                width: '100%',
                                dropdownParent: $modalParent,
                                placeholder: 'Select Category',
                                allowClear: true
                            });

                            // If options list is empty or only placeholder, fetch dynamically from server!
                            if ($catSelect.find('option').length <= 1) {
                                fetch('{{ route('products.categories.ajax.get') }}')
                                    .then(r => r.json())
                                    .then(res => {
                                        if (res.results && res.results.length) {
                                            res.results.forEach(item => {
                                                if ($catSelect.find("option[value='" + item.id + "']").length === 0) {
                                                    $catSelect.append(new Option(item.text, item.id, false, false));
                                                }
                                            });
                                            $catSelect.trigger('change.select2');
                                        }
                                    })
                                    .catch(e => console.error('Categories load error:', e));
                            }
                        }

                        // Initialize Unit Select2
                        const $unitSelect = $('#product_unit_select');
                        if ($unitSelect.length) {
                            if ($unitSelect.hasClass('select2-hidden-accessible')) {
                                $unitSelect.select2('destroy');
                            }
                            $unitSelect.select2({
                                width: '100%',
                                dropdownParent: $modalParent,
                                placeholder: 'Select Unit'
                            });
                        }
                    }
                }, 100);
            },

            openCategorySubmodal() {
                this.newCategoryName = '';
                this.newCategoryDescription = '';
                this.categoryError = '';
                this.showCategoryModal = true;
                this.$nextTick(() => {
                    const inp = document.getElementById('new_category_input');
                    if (inp) inp.focus();
                });
            },

            async saveCategory() {
                if (!this.newCategoryName.trim()) {
                    this.categoryError = 'Please enter a category name.';
                    return;
                }
                this.categoryError = '';
                this.savingCategory = true;

                try {
                    const response = await fetch('{{ route('products.categories.ajax') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            name: this.newCategoryName.trim(),
                            description: this.newCategoryDescription.trim()
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        const category = data.category;
                        
                        // Dynamically update the Select2 Category dropdown!
                        if (window.jQuery) {
                            const $catSelect = $('#product_category_select');
                            
                            // Check if option already exists
                            if ($catSelect.find("option[value='" + category.id + "']").length === 0) {
                                const newOption = new Option(category.name, category.id, true, true);
                                $catSelect.append(newOption);
                            }
                            // Select it and trigger change
                            $catSelect.val(category.id).trigger('change');
                        }

                        this.newCategoryName = '';
                        this.newCategoryDescription = '';
                        this.showCategoryModal = false;
                    } else {
                        this.categoryError = data.message || 'Error saving category.';
                    }
                } catch (err) {
                    this.categoryError = 'Network error. Please try again.';
                } finally {
                    this.savingCategory = false;
                }
            },

            openUnitSubmodal() {
                this.newUnitLabel = '';
                this.unitError = '';
                this.showUnitModal = true;
                this.$nextTick(() => {
                    const inp = document.getElementById('new_unit_input');
                    if (inp) inp.focus();
                });
            },

            saveUnit() {
                if (!this.newUnitLabel.trim()) {
                    this.unitError = 'Please enter unit name.';
                    return;
                }
                const label = this.newUnitLabel.trim();
                const val = label.toLowerCase().replace(/[^a-z0-9]/g, '_');

                if (window.jQuery) {
                    const $unitSelect = $('#product_unit_select');
                    if ($unitSelect.find("option[value='" + val + "']").length === 0) {
                        const newOption = new Option(label, val, true, true);
                        $unitSelect.append(newOption);
                    }
                    $unitSelect.val(val).trigger('change');
                }

                this.newUnitLabel = '';
                this.unitError = '';
                this.showUnitModal = false;
            }
        };
    }

    function toggleStockStatus(productId) {
        fetch(`{{ url('products') }}/${productId}/toggle-stock`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById(`toggle-btn-${productId}`);
                const dot = document.getElementById(`toggle-dot-${productId}`);
                if (data.in_stock) {
                    btn.classList.remove('bg-slate-200');
                    btn.classList.add('bg-emerald-500');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-4');
                } else {
                    btn.classList.remove('bg-emerald-500');
                    btn.classList.add('bg-slate-200');
                    dot.classList.remove('translate-x-4');
                    dot.classList.add('translate-x-0');
                }
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
@endsection
