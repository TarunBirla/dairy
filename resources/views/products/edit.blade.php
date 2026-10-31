@extends('layouts.app')

@section('title', 'Edit ' . $product->name)
@section('breadcrumb', 'Edit Product')
@section('header_title', 'Products / ' . $product->name)

@section('content')
<div x-data="productEditForm()" class="max-w-3xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Product: {{ $product->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Update pricing, category, unit, and inventory specifications.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Back</a>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Product For Audience Selector -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product For *</label>
                <div class="grid grid-cols-3 gap-3">
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $product->name) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Code</label>
                    <input type="text" readonly value="{{ $product->code }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-500">
                </div>
            </div>

            <!-- Category & Unit Section (Searchable Dropdowns) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Category Searchable Dropdown + Add new -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Product Category <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <button type="button" @click="showCategoryModal = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                            <span>+ Add new</span>
                        </button>
                    </div>
                    <div class="relative w-full" @click.away="catDropdownOpen = false">
                        <input type="hidden" name="category_id" :value="selectedCategoryId">
                        <div 
                            @click="toggleCategoryDropdown()"
                            class="w-full min-h-[42px] px-3.5 py-2.5 text-xs border rounded-xl bg-white flex items-center justify-between cursor-pointer transition select-none"
                            :class="catDropdownOpen ? 'border-emerald-600 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <span class="truncate" :class="selectedCategoryId ? 'text-slate-800 font-medium' : 'text-slate-400'" x-text="selectedCategoryName || 'Select Category'"></span>
                            <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                <button 
                                    type="button" 
                                    x-show="selectedCategoryId" 
                                    @click.stop="clearCategory()" 
                                    title="Clear Category"
                                    class="text-slate-400 hover:text-rose-500 p-0.5 rounded transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-150" :class="catDropdownOpen ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Category Dropdown Panel -->
                        <div 
                            x-show="catDropdownOpen" 
                            x-cloak 
                            x-transition.opacity.duration.150ms
                            class="absolute left-0 right-0 top-full mt-1.5 z-[70] bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
                        >
                            <div class="p-2 border-b border-slate-100 bg-slate-50/60">
                                <div class="relative">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input 
                                        type="text" 
                                        x-ref="catSearchInput"
                                        x-model="catSearch" 
                                        @keydown.escape.stop="catDropdownOpen = false"
                                        @keydown.enter.prevent="selectFirstFilteredCategory()"
                                        placeholder="Search category..." 
                                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20"
                                    >
                                </div>
                            </div>
                            <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 py-1">
                                <div 
                                    @click="clearCategory()"
                                    class="px-3.5 py-2 text-xs cursor-pointer transition flex items-center justify-between text-slate-400 hover:bg-slate-50 hover:text-slate-600"
                                >
                                    <span>-- No Category (Optional) --</span>
                                </div>
                                <template x-for="cat in filteredCategories" :key="cat.id">
                                    <div 
                                        @click="selectCategory(cat)"
                                        class="px-3.5 py-2 text-xs cursor-pointer transition flex items-center justify-between"
                                        :class="String(selectedCategoryId) === String(cat.id) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-emerald-600 hover:text-white'"
                                    >
                                        <span x-text="cat.name"></span>
                                        <svg x-show="String(selectedCategoryId) === String(cat.id)" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </template>
                                <div x-show="filteredCategories.length === 0" class="px-3.5 py-3 text-center text-xs text-slate-400 space-y-1.5">
                                    <p>No category matching "<span class="font-semibold text-slate-600" x-text="catSearch"></span>"</p>
                                    <button 
                                        type="button" 
                                        @click="newCategoryName = catSearch; catDropdownOpen = false; showCategoryModal = true;"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-semibold text-[11px]"
                                    >
                                        <span>+ Add "<span x-text="catSearch"></span>" as new category</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit Searchable Dropdown + Add new -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Unit of Measure *</label>
                        <button type="button" @click="showUnitModal = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                            <span>+ Add new</span>
                        </button>
                    </div>
                    <div class="relative w-full" @click.away="unitDropdownOpen = false">
                        <input type="hidden" name="unit" :value="selectedUnit" required>
                        <div 
                            @click="toggleUnitDropdown()"
                            class="w-full min-h-[42px] px-3.5 py-2.5 text-xs border rounded-xl bg-white flex items-center justify-between cursor-pointer transition select-none"
                            :class="unitDropdownOpen ? 'border-emerald-600 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <span class="truncate" :class="selectedUnit ? 'text-slate-800 font-medium' : 'text-slate-400'" x-text="selectedUnitLabel || 'Select Unit'"></span>
                            <svg class="w-4 h-4 text-slate-400 shrink-0 ml-2 transition-transform duration-150" :class="unitDropdownOpen ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>

                        <!-- Unit Dropdown Panel -->
                        <div 
                            x-show="unitDropdownOpen" 
                            x-cloak 
                            x-transition.opacity.duration.150ms
                            class="absolute left-0 right-0 top-full mt-1.5 z-[70] bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden"
                        >
                            <div class="p-2 border-b border-slate-100 bg-slate-50/60">
                                <div class="relative">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input 
                                        type="text" 
                                        x-ref="unitSearchInput"
                                        x-model="unitSearch" 
                                        @keydown.escape.stop="unitDropdownOpen = false"
                                        @keydown.enter.prevent="selectFirstFilteredUnit()"
                                        placeholder="Search unit..." 
                                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20"
                                    >
                                </div>
                            </div>
                            <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 py-1">
                                <template x-for="u in filteredUnits" :key="u.value">
                                    <div 
                                        @click="selectUnit(u)"
                                        class="px-3.5 py-2 text-xs cursor-pointer transition flex items-center justify-between"
                                        :class="String(selectedUnit) === String(u.value) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-700 hover:bg-emerald-600 hover:text-white'"
                                    >
                                        <span x-text="u.label"></span>
                                        <svg x-show="String(selectedUnit) === String(u.value)" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </template>
                                <div x-show="filteredUnits.length === 0" class="px-3.5 py-3 text-center text-xs text-slate-400 space-y-1.5">
                                    <p>No unit matching "<span class="font-semibold text-slate-600" x-text="unitSearch"></span>"</p>
                                    <button 
                                        type="button" 
                                        @click="newUnitLabel = unitSearch; unitDropdownOpen = false; showUnitModal = true;"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 font-semibold text-[11px]"
                                    >
                                        <span>+ Add "<span x-text="unitSearch"></span>" as new unit</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Retail Price (₹) *</label>
                    <input type="number" step="0.5" name="price" required value="{{ old('price', $product->price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subscription Price (₹)</label>
                    <input type="number" step="0.5" name="subscription_price" value="{{ old('subscription_price', $product->subscription_price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cost Price (₹)</label>
                    <input type="number" step="0.5" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <!-- 1. Manage Stock (Matching media_1793424418858_58f87a3f.png & media_1793424448784_ee75137e.png) -->
            <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="boxes" class="w-4 h-4 text-emerald-600"></i>
                        <span>Manage Stock</span>
                    </h4>
                </div>

                <div class="flex items-center justify-between py-1">
                    <div>
                        <span class="text-xs font-semibold text-slate-800">Configure Stock</span>
                        <p class="text-[11px] text-slate-400">Enable stock tracking, current count and low stock alerts</p>
                    </div>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="manageStock = !manageStock" 
                                :class="manageStock ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="manageStock ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="manage_stock" :value="manageStock ? '1' : '0'">
                    </div>
                </div>

                <!-- Stock Quantities revealed when Configure Stock is ON -->
                <div x-show="manageStock" x-transition class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-200/60">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Current Stock Qty</label>
                        <input type="number" step="0.1" name="current_stock" placeholder="Enter Stock Qty" value="{{ old('current_stock', $product->current_stock) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alert Qty</label>
                        <input type="number" step="1" name="min_stock_alert" placeholder="Enter Alert Qty" value="{{ old('min_stock_alert', $product->min_stock_alert) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>
                </div>
            </div>

            <!-- 2. Counter Sales (Matching media_1793424418858_58f87a3f.png) -->
            <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="store" class="w-4 h-4 text-emerald-600"></i>
                        <span>Counter Sales</span>
                    </h4>
                </div>

                <div class="flex items-center justify-between py-1">
                    <div>
                        <span class="text-xs font-semibold text-slate-800">When enabled, this product will appear in counter sales.</span>
                        <p class="text-[11px] text-slate-400">Available at physical POS counters and dairy stores</p>
                    </div>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowCounterSales = !allowCounterSales" 
                                :class="allowCounterSales ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowCounterSales ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_counter_sales" :value="allowCounterSales ? '1' : '0'">
                    </div>
                </div>
            </div>

            <!-- 3. Customer App Settings (Matching media_1793424418858_58f87a3f.png & media_1793424509201_c12b440a.png) -->
            <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="smartphone" class="w-4 h-4 text-emerald-600"></i>
                        <span>Customer App Settings</span>
                    </h4>
                </div>

                <!-- Row 1: Show on Customer App -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Show this product on customer app</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="showOnCustomerApp = !showOnCustomerApp" 
                                :class="showOnCustomerApp ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="showOnCustomerApp ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="show_on_customer_app" :value="showOnCustomerApp ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 2: In Stock Availability -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Product is in Stock? (To show product Availability on the Customer app)</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="inStock = !inStock" 
                                :class="inStock ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="inStock ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="in_stock" :value="inStock ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 3: Allow One-Time Orders -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">For Estore: Allow One-Time Orders for This Product?</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowOnetimeOrders = !allowOnetimeOrders" 
                                :class="allowOnetimeOrders ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowOnetimeOrders ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_onetime_orders" :value="allowOnetimeOrders ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 4: Schedule Cut-Off Time -->
                <div class="py-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-800">Schedule the cut-off time for order booking from the customer app.</span>
                        <div class="flex items-center">
                            <button type="button" 
                                    @click="scheduleBookingCutoff = !scheduleBookingCutoff" 
                                    :class="scheduleBookingCutoff ? 'bg-emerald-600' : 'bg-slate-300'"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                    role="switch">
                                <span :class="scheduleBookingCutoff ? 'translate-x-5' : 'translate-x-0'" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <input type="hidden" name="schedule_booking_cutoff" :value="scheduleBookingCutoff ? '1' : '0'">
                        </div>
                    </div>

                    <!-- Revealed Cut-Off Inputs matching media_1793424509201_c12b440a.png -->
                    <div x-show="scheduleBookingCutoff" x-transition class="mt-3 p-3.5 bg-white rounded-xl border border-slate-200 space-y-3">
                        <!-- Morning -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <span class="sm:col-span-3 text-xs font-semibold text-slate-700">Morning</span>
                            <div class="sm:col-span-5">
                                <select name="booking_cutoff_morning_day" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                                    <option value="">Select day...</option>
                                    @foreach(['Same Day', '1 Day Before', '2 Days Before', '3 Days Before'] as $dayOpt)
                                        <option value="{{ $dayOpt }}" {{ old('booking_cutoff_morning_day', $product->booking_cutoff_morning_day) === $dayOpt ? 'selected' : '' }}>{{ $dayOpt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-4 flex items-center gap-2">
                                <label class="text-xs text-slate-500 shrink-0">Till Time</label>
                                <input type="time" name="booking_cutoff_morning_time" value="{{ old('booking_cutoff_morning_time', $product->booking_cutoff_morning_time) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            </div>
                        </div>

                        <!-- Evening -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <span class="sm:col-span-3 text-xs font-semibold text-slate-700">Evening</span>
                            <div class="sm:col-span-5">
                                <select name="booking_cutoff_evening_day" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                                    <option value="">Select day...</option>
                                    @foreach(['Same Day', '1 Day Before', '2 Days Before', '3 Days Before'] as $dayOpt)
                                        <option value="{{ $dayOpt }}" {{ old('booking_cutoff_evening_day', $product->booking_cutoff_evening_day) === $dayOpt ? 'selected' : '' }}>{{ $dayOpt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-4 flex items-center gap-2">
                                <label class="text-xs text-slate-500 shrink-0">Till Time</label>
                                <input type="time" name="booking_cutoff_evening_time" value="{{ old('booking_cutoff_evening_time', $product->booking_cutoff_evening_time) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Product Subscription Settings (Matching media_1793424537512_d35a12f0.png) -->
            <div class="p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="repeat" class="w-4 h-4 text-emerald-600"></i>
                        <span>Product Subscription Settings</span>
                    </h4>
                </div>

                <!-- Row 1: Can Subscribe -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Can customers subscribe to this product?</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowSubscription = !allowSubscription" 
                                :class="allowSubscription ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowSubscription ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_subscription" :value="allowSubscription ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 2: Can Change Subscription -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Can customers change the subscription to this product?</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowSubscriptionChange = !allowSubscriptionChange" 
                                :class="allowSubscriptionChange ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowSubscriptionChange ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_subscription_change" :value="allowSubscriptionChange ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 3: Allow Cancel Subscription -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Allow Customers to Cancel Their Subscriptions?</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowSubscriptionCancellation = !allowSubscriptionCancellation" 
                                :class="allowSubscriptionCancellation ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowSubscriptionCancellation ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_subscription_cancellation" :value="allowSubscriptionCancellation ? '1' : '0'">
                    </div>
                </div>

                <!-- Row 4: Allow Booking After Subscription -->
                <div class="flex items-center justify-between py-2 border-b border-slate-200/60">
                    <span class="text-xs font-semibold text-slate-800">Allow Product Booking After Subscription?</span>
                    <div class="flex items-center">
                        <button type="button" 
                                @click="allowBookingAfterSubscription = !allowBookingAfterSubscription" 
                                :class="allowBookingAfterSubscription ? 'bg-emerald-600' : 'bg-slate-300'"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" 
                                role="switch">
                            <span :class="allowBookingAfterSubscription ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="allow_booking_after_subscription" :value="allowBookingAfterSubscription ? '1' : '0'">
                    </div>
                </div>

                <!-- Cut-Off Timings for Subscriptions matching media_1793424537512_d35a12f0.png -->
                <div class="p-3.5 bg-white rounded-xl border border-slate-200 space-y-3">
                    <!-- Morning -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        <span class="sm:col-span-4 text-xs font-semibold text-slate-700">Morning Cut Off Time</span>
                        <div class="sm:col-span-4">
                            <select name="subscription_cutoff_morning_day" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                                <option value="">Select day...</option>
                                @foreach(['Same Day', '1 Day Before', '2 Days Before', '3 Days Before'] as $dayOpt)
                                    <option value="{{ $dayOpt }}" {{ old('subscription_cutoff_morning_day', $product->subscription_cutoff_morning_day) === $dayOpt ? 'selected' : '' }}>{{ $dayOpt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-4 flex items-center gap-2">
                            <label class="text-xs text-slate-500 shrink-0">Till Time</label>
                            <input type="time" name="subscription_cutoff_morning_time" value="{{ old('subscription_cutoff_morning_time', $product->subscription_cutoff_morning_time) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        </div>
                    </div>

                    <!-- Evening -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        <span class="sm:col-span-4 text-xs font-semibold text-slate-700">Evening Cut Off Time</span>
                        <div class="sm:col-span-4">
                            <select name="subscription_cutoff_evening_day" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                                <option value="">Select day...</option>
                                @foreach(['Same Day', '1 Day Before', '2 Days Before', '3 Days Before'] as $dayOpt)
                                    <option value="{{ $dayOpt }}" {{ old('subscription_cutoff_evening_day', $product->subscription_cutoff_evening_day) === $dayOpt ? 'selected' : '' }}>{{ $dayOpt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-4 flex items-center gap-2">
                            <label class="text-xs text-slate-500 shrink-0">Till Time</label>
                            <input type="time" name="subscription_cutoff_evening_time" value="{{ old('subscription_cutoff_evening_time', $product->subscription_cutoff_evening_time) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 italic pt-1 border-t border-slate-100">
                        *Note: Any change in product subscription via customer app will be immediate if cut-off timing is not mentioned.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Image <span class="text-slate-400 font-normal">(Optional)</span></label>
                <div class="flex items-center gap-3">
                    <div class="w-16 h-16 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden flex-shrink-0">
                        <template x-if="productImagePreview">
                            <img :src="productImagePreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!productImagePreview && currentProductImage">
                            <img :src="'/' + currentProductImage.replace(/^\//, '')" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!productImagePreview && !currentProductImage">
                            <i data-lucide="image" class="w-7 h-7 text-slate-300"></i>
                        </template>
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <input type="file" name="image" accept="image/*" @change="previewProductImage($event)" class="text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer w-full">
                        @if($product->image)
                            <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-600 cursor-pointer font-medium">
                                <input type="checkbox" name="remove_image" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                <span>Remove current image</span>
                            </label>
                        @endif
                        <p class="text-[11px] text-slate-400">PNG, JPG, WEBP up to 3MB</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description / Notes</label>
                <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2.5 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Update Product</button>
            </div>
        </form>
    </div>

    <!-- Category Modal (Elevated z-[80]) -->
    <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-[80] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCategoryModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="showCategoryModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showCategoryModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 relative z-10 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Add product category</h3>
                        <p class="text-xs text-slate-500 mt-1">Categories help group similar products on bills and customer catalog.</p>
                    </div>
                    <button type="button" @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category name *</label>
                        <input type="text" x-model="newCategoryName" @keydown.enter.prevent="saveCategory()" placeholder="e.g. Milk, Curd, Ghee" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <p x-show="categoryError" x-text="categoryError" class="text-rose-600 text-[11px] mt-1"></p>
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
    </div>

    <!-- Unit Modal (Elevated z-[80]) -->
    <div x-show="showUnitModal" x-cloak class="fixed inset-0 z-[80] overflow-y-auto" aria-labelledby="modal-unit" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUnitModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="showUnitModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showUnitModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 relative z-10 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-unit">Add unit of measure</h3>
                        <p class="text-xs text-slate-500 mt-1">Specify unit label and symbol for packaging or billing.</p>
                    </div>
                    <button type="button" @click="showUnitModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit name / label *</label>
                        <input type="text" x-model="newUnitLabel" @keydown.enter.prevent="saveUnit()" placeholder="e.g. 500ml Pouch, Jar, Can, Box" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <p x-show="unitError" x-text="unitError" class="text-rose-600 text-[11px] mt-1"></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-3">
                    <button type="button" @click="showUnitModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                    <button type="button" @click="saveUnit()" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save unit</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productEditForm() {
    return {
        selectedProductFor: '{{ old('product_for', $product->product_for ?? 'customer') }}',

        // Dynamic Switches matching screenshots
        manageStock: {{ old('manage_stock', $product->manage_stock ?? true) ? 'true' : 'false' }},
        allowCounterSales: {{ old('allow_counter_sales', $product->allow_counter_sales ?? true) ? 'true' : 'false' }},
        showOnCustomerApp: {{ old('show_on_customer_app', $product->show_on_customer_app ?? true) ? 'true' : 'false' }},
        inStock: {{ old('in_stock', $product->in_stock ?? true) ? 'true' : 'false' }},
        allowOnetimeOrders: {{ old('allow_onetime_orders', $product->allow_onetime_orders ?? true) ? 'true' : 'false' }},
        scheduleBookingCutoff: {{ old('schedule_booking_cutoff', $product->schedule_booking_cutoff ?? false) ? 'true' : 'false' }},
        allowSubscription: {{ old('allow_subscription', $product->allow_subscription ?? true) ? 'true' : 'false' }},
        allowSubscriptionChange: {{ old('allow_subscription_change', $product->allow_subscription_change ?? true) ? 'true' : 'false' }},
        allowSubscriptionCancellation: {{ old('allow_subscription_cancellation', $product->allow_subscription_cancellation ?? true) ? 'true' : 'false' }},
        allowBookingAfterSubscription: {{ old('allow_booking_after_subscription', $product->allow_booking_after_subscription ?? true) ? 'true' : 'false' }},
        showCategoryModal: false,
        showUnitModal: false,
        newCategoryName: '',
        categoryError: '',
        savingCategory: false,

        selectedCategoryId: '{{ old('category_id', $product->category_id ?? '') }}',
        catDropdownOpen: false,
        catSearch: '',
        categories: @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),

        newUnitLabel: '',
        unitError: '',
        selectedUnit: '{{ old('unit', $product->unit ?: 'liter') }}',
        unitDropdownOpen: false,
        unitSearch: '',
        units: [
            { value: 'liter', label: 'Liter (ltr)' },
            { value: 'kg', label: 'Kilogram (kg)' },
            { value: 'bottle', label: 'Bottle' },
            { value: 'piece', label: 'Piece / Bag' },
            { value: 'pack', label: 'Pack / Pouch' },
        ],

        currentProductImage: '{{ $product->image ? addslashes($product->image) : '' }}',
        productImagePreview: null,

        get filteredCategories() {
            const q = (this.catSearch || '').trim().toLowerCase();
            if (!q) return this.categories;
            return this.categories.filter(c => (c.name || '').toLowerCase().includes(q));
        },

        get selectedCategoryName() {
            if (!this.selectedCategoryId) return '';
            const found = this.categories.find(c => String(c.id) === String(this.selectedCategoryId));
            return found ? found.name : '';
        },

        get filteredUnits() {
            const q = (this.unitSearch || '').trim().toLowerCase();
            if (!q) return this.units;
            return this.units.filter(u => 
                (u.label || '').toLowerCase().includes(q) || 
                (u.value || '').toLowerCase().includes(q)
            );
        },

        get selectedUnitLabel() {
            if (!this.selectedUnit) return '';
            const found = this.units.find(u => String(u.value) === String(this.selectedUnit));
            return found ? found.label : this.selectedUnit;
        },

        toggleCategoryDropdown() {
            this.catDropdownOpen = !this.catDropdownOpen;
            if (this.catDropdownOpen) {
                this.unitDropdownOpen = false;
                this.catSearch = '';
                this.$nextTick(() => {
                    if (this.$refs.catSearchInput) this.$refs.catSearchInput.focus();
                });
            }
        },

        selectCategory(cat) {
            this.selectedCategoryId = String(cat.id);
            this.catDropdownOpen = false;
            this.catSearch = '';
        },

        clearCategory() {
            this.selectedCategoryId = '';
            this.catDropdownOpen = false;
            this.catSearch = '';
        },

        selectFirstFilteredCategory() {
            if (this.filteredCategories.length > 0) {
                this.selectCategory(this.filteredCategories[0]);
            }
        },

        toggleUnitDropdown() {
            this.unitDropdownOpen = !this.unitDropdownOpen;
            if (this.unitDropdownOpen) {
                this.catDropdownOpen = false;
                this.unitSearch = '';
                this.$nextTick(() => {
                    if (this.$refs.unitSearchInput) this.$refs.unitSearchInput.focus();
                });
            }
        },

        selectUnit(u) {
            this.selectedUnit = u.value;
            this.unitDropdownOpen = false;
            this.unitSearch = '';
        },

        selectFirstFilteredUnit() {
            if (this.filteredUnits.length > 0) {
                this.selectUnit(this.filteredUnits[0]);
            }
        },

        previewProductImage(e) {
            const file = e.target.files[0];
            this.productImagePreview = file ? URL.createObjectURL(file) : null;
        },

        init() {
            const currentUnit = '{{ old('unit', $product->unit) }}';
            if (currentUnit && !this.units.some(u => u.value === currentUnit)) {
                this.units.push({ value: currentUnit, label: currentUnit });
            }

            // Always sync categories from server so the list is guaranteed up-to-date
            fetch('{{ route('products.categories.ajax.get') }}')
                .then(r => r.json())
                .then(res => {
                    if (res.results && Array.isArray(res.results) && res.results.length > 0) {
                        res.results.forEach(item => {
                            if (!this.categories.some(c => String(c.id) === String(item.id))) {
                                this.categories.push({ id: item.id, name: item.text });
                            }
                        });
                    }
                })
                .catch(e => console.error('Categories sync error:', e));
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
                    body: JSON.stringify({ name: this.newCategoryName.trim() })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    const category = data.category;
                    if (!this.categories.some(c => String(c.id) === String(category.id))) {
                        this.categories.push({ id: category.id, name: category.name });
                    }
                    this.selectedCategoryId = String(category.id);
                    this.newCategoryName = '';
                    this.catDropdownOpen = false;
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

        saveUnit() {
            if (!this.newUnitLabel.trim()) {
                this.unitError = 'Please enter unit name.';
                return;
            }
            const label = this.newUnitLabel.trim();
            const val = label.toLowerCase().replace(/[^a-z0-9]/g, '_');

            if (!this.units.some(u => u.value === val)) {
                this.units.push({ value: val, label: label });
            }
            this.selectedUnit = val;

            this.newUnitLabel = '';
            this.unitError = '';
            this.unitDropdownOpen = false;
            this.showUnitModal = false;
        }
    }
}
</script>
@endpush
@endsection
