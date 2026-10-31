@extends('layouts.app')

@section('title', 'Milk Collection History')
@section('breadcrumb', 'Collection History')
@section('header_title', 'Procurement / Milk Collections')

@section('header_action')
    <button type="button" 
            onclick="document.getElementById('collection-entry-section').scrollIntoView({behavior: 'smooth'}); setTimeout(function() { const qIn = document.getElementById('quick_farmer_code_input'); if (qIn) { qIn.focus(); qIn.select(); } else { const sIn = document.getElementById('farmer_search_input'); if (sIn) sIn.focus(); } }, 200);" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
        <span>Quick Intake</span>
    </button>
@endsection

@section('content')
<div class="space-y-3 w-full max-w-full" x-data="{ currentTopTab: '{{ $activeTab ?? 'collections' }}' }">

    <!-- Top 3 Navigation Tabs: Collections, Milk Sale, Product Sale -->
    <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-2xl w-fit border border-slate-200/80 shadow-2xs">
        <button type="button" 
                @click="currentTopTab = 'collections'" 
                :class="currentTopTab === 'collections' ? 'bg-white text-emerald-800 shadow-xs font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2">
            <i data-lucide="milk" class="w-4 h-4 text-emerald-600"></i>
            <span>Collections</span>
        </button>

        <button type="button" 
                @click="currentTopTab = 'milk_sales'" 
                :class="currentTopTab === 'milk_sales' ? 'bg-white text-emerald-800 shadow-xs font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2">
            <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-600"></i>
            <span>Milk Sale</span>
        </button>

        <button type="button" 
                @click="currentTopTab = 'product_sales'" 
                :class="currentTopTab === 'product_sales' ? 'bg-white text-emerald-800 shadow-xs font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4 text-indigo-600"></i>
            <span>Product Sale</span>
        </button>

        <button type="button" 
                @click="currentTopTab = 'deductions'" 
                :class="currentTopTab === 'deductions' ? 'bg-white text-emerald-800 shadow-xs font-bold border border-slate-200/60' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition inline-flex items-center gap-2">
            <i data-lucide="receipt" class="w-4 h-4 text-rose-600"></i>
            <span>Deductions</span>
        </button>
    </div>

    <!-- TAB 1: COLLECTIONS -->
    <div x-show="currentTopTab === 'collections'" class="space-y-2 w-full max-w-full" x-data="milkCollectionManager()" x-init="init()">

    <!-- Ultra-Slim KPI Metrics Bar -->
    <!-- <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Procured</span>
            <span class="text-xs font-black text-slate-900 leading-none">
                <span x-text="metrics.totalLiters">{{ number_format($todayTotalLiters, 1) }}</span> <span class="text-[10px] text-slate-400 font-semibold">L</span>
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Net Payout</span>
            <span class="text-xs font-black text-emerald-600 leading-none">
                ₹ <span x-text="metrics.totalAmount">{{ number_format($todayTotalAmount, 2) }}</span>
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Avg FAT</span>
            <span class="text-xs font-black text-slate-800 leading-none">
                <span x-text="metrics.avgFat">{{ number_format($avgFat, 1) }}</span>%
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Avg SNF</span>
            <span class="text-xs font-black text-slate-800 leading-none">
                <span x-text="metrics.avgSnf">{{ number_format($avgSnf, 1) }}</span>%
            </span>
        </div>
    </div> -->

    <!-- ========================================================================= -->
    <!-- MAIN 2-COLUMN WORKSPACE: COMPACT LEFT INTAKE FORM + UNIFIED RIGHT SUMMARY  -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-2 items-stretch">

        <!-- LEFT COLUMN (8 cols): COMPACT MILK COLLECTION ENTRY CARD -->
        <div id="collection-entry-section" 
             class="lg:col-span-8 bg-white rounded-xl border shadow-xs overflow-visible transition-all duration-200 flex flex-col justify-between"
             :class="isEditMode ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-slate-200/80'">

            <div>
                <!-- Slim Sub-Header Bar: Receipt, Mode Badge, Center & Notes Toggle -->
                <div class="px-3 py-1 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-center justify-between gap-1.5 text-[11px] rounded-t-xl">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded font-mono text-[10px] font-bold border shrink-0"
                              :class="isEditMode ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-800 border-slate-200'"
                              x-text="form.receipt_number || '{{ $nextReceipt }}'"></span>

                        <a href="{{ route('settings.center-information') }}"
                           title="Click to change Collection Type in Center Information Settings"
                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold hover:bg-emerald-100 transition">
                            <i data-lucide="settings-2" class="w-2.5 h-2.5 text-emerald-600"></i>
                            <span>Mode: {{ $collectionConfig['collection_type'] }}</span>
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        <input type="hidden" id="collection_center_select" x-model="form.collection_center_id">

                        @if(($collectionConfig['route_wise_collection'] ?? 'On') === 'On')
                        <div x-show="config.route_wise_collection === 'On'" class="flex items-center gap-1 relative" id="route_select_col">
                            <span class="text-[10px] font-semibold text-slate-500 shrink-0">Route:</span>
                            <select id="collection_route_select"
                                    x-model="selectedRouteId"
                                    class="text-[10px] border border-slate-200 rounded bg-white w-32 sm:w-40 py-0.5 px-1.5 font-semibold text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="">All Routes {{ !empty($collectionConfig['center_vehicle_route']) ? '(' . $collectionConfig['center_vehicle_route'] . ')' : '' }}</option>
                                @foreach($routes ?? [] as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('delivery.routes') }}"
                               title="Add / Manage Routes"
                               class="w-5 h-5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center text-[10px] font-bold shrink-0 transition">
                                +
                            </a>
                        </div>
                        @endif

                        <button type="button" 
                                @click="showAdvanced = !showAdvanced" 
                                :class="showAdvanced ? 'bg-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'"
                                class="px-1.5 py-0.5 text-[10px] font-semibold rounded transition flex items-center gap-1 border border-transparent">
                            <i data-lucide="sliders" class="w-2.5 h-2.5"></i>
                            <span>Notes</span>
                        </button>

                        <template x-if="isEditMode">
                            <button type="button" @click="cancelEdit()" class="px-1.5 py-0.5 text-[10px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded transition">
                                Cancel Edit
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Main Form Body (Compact Vertical Spacing) -->
                <form @submit.prevent="submitCollection(false)" class="px-3 py-2 space-y-1.5">

                    <!-- ROW 1: COLLECTION DATE | SHIFT | MILK TYPE -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pb-1.5 border-b border-slate-100">
                        <!-- Collection Date -->
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Collection Date</label>
                            <input type="date" 
                                   x-model="form.collection_date" 
                                   @change="triggerRateCalculation()"
                                   required
                                   class="w-full h-7 px-2 text-[11px] font-bold text-slate-800 border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- Shift -->
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Shift</label>
                            <div class="flex items-center h-7 rounded-md border border-slate-200 overflow-hidden bg-slate-50 p-0.5 gap-1">
                                @if(in_array('morning', $collectionConfig['allowed_shifts']))
                                <button type="button" 
                                        @click="form.shift = 'morning'; triggerRateCalculation();"
                                        :class="form.shift === 'morning' ? 'bg-amber-50 text-amber-900 border border-amber-300 font-bold shadow-2xs' : 'text-slate-600 hover:bg-white border border-transparent'"
                                        class="flex-1 h-full px-2 text-[11px] rounded transition flex items-center justify-center gap-1 whitespace-nowrap">
                                    <span>☀️</span>
                                    <span>Morning</span>
                                </button>
                                @endif
                                @if(in_array('evening', $collectionConfig['allowed_shifts']))
                                <button type="button" 
                                        @click="form.shift = 'evening'; triggerRateCalculation();"
                                        :class="form.shift === 'evening' ? 'bg-indigo-50 text-indigo-900 border border-indigo-300 font-bold shadow-2xs' : 'text-slate-600 hover:bg-white border border-transparent'"
                                        class="flex-1 h-full px-2 text-[11px] rounded transition flex items-center justify-center gap-1 whitespace-nowrap">
                                    <span>🌙</span>
                                    <span>Evening</span>
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Milk Type -->
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Milk Type</label>
                            <div class="flex items-center h-7 rounded-md border border-slate-200 overflow-hidden bg-slate-50 p-0.5 gap-1">
                                @if(in_array('cow', $collectionConfig['allowed_milk_types']))
                                <button type="button" 
                                        @click="form.milk_type = 'cow'; triggerRateCalculation();"
                                        :class="form.milk_type === 'cow' ? 'bg-amber-50 text-amber-800 border border-amber-300 font-bold shadow-2xs' : 'text-slate-600 hover:bg-white border border-transparent'"
                                        class="flex-1 h-full px-2 text-[11px] rounded transition flex items-center justify-center gap-1 whitespace-nowrap">
                                    <span>🐄</span>
                                    <span>Cow (CM)</span>
                                </button>
                                @endif
                                @if(in_array('buffalo', $collectionConfig['allowed_milk_types']))
                                <button type="button" 
                                        @click="form.milk_type = 'buffalo'; triggerRateCalculation();"
                                        :class="form.milk_type === 'buffalo' ? 'bg-indigo-50 text-indigo-800 border border-indigo-300 font-bold shadow-2xs' : 'text-slate-600 hover:bg-white border border-transparent'"
                                        class="flex-1 h-full px-2 text-[11px] rounded transition flex items-center justify-center gap-1 whitespace-nowrap">
                                    <span>🐃</span>
                                    <span>Buffalo</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ROW 2 (MIDDLE ROW): FARMER CODE | FARMER NAME | SAMPLE NO. (AUTO) -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-end" id="farmer_select_col" @click.away="farmerDropdownOpen = false">
                        <!-- Farmer Code (4 cols) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Farmer Code *</label>
                            <div class="flex items-stretch h-7">
                                <input type="text" 
                                       id="quick_farmer_code_input" 
                                       placeholder="e.g. 001 or 101" 
                                       x-model="quickFarmerCode" 
                                       @keydown.enter.prevent="lookupFarmerByCode()" 
                                       @blur="lookupFarmerByCode()" 
                                       class="w-full px-2.5 text-[11px] font-bold text-slate-900 bg-slate-50 focus:bg-white border border-r-0 border-slate-200 rounded-l-md focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                                <button type="button"
                                        @click="lookupFarmerByCode()"
                                        title="Search Farmer Code"
                                        class="px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-r-md flex items-center justify-center transition shrink-0">
                                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Farmer Name (Searchable Dropdown, 5 cols) -->
                        <div class="sm:col-span-6 relative">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Farmer Name *</label>
                            <div class="flex items-stretch h-7 relative">
                                <input type="text" 
                                       id="farmer_search_input"
                                       placeholder="Search Name, Code, Phone..."
                                       x-model="farmerSearchText"
                                       @focus="farmerDropdownOpen = true"
                                       @click="farmerDropdownOpen = true"
                                       @input="farmerDropdownOpen = true; highlightedIndex = 0;"
                                       @keydown.arrow-down.prevent="highlightNextFarmer()"
                                       @keydown.arrow-up.prevent="highlightPrevFarmer()"
                                       @keydown.enter.prevent="selectHighlightedFarmer()"
                                       @keydown.escape="farmerDropdownOpen = false"
                                       autocomplete="off"
                                       class="w-full px-2.5 text-[11px] font-bold text-slate-800 border border-r-0 border-slate-200 rounded-l-md bg-slate-50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                                <button type="button" 
                                        @click="farmerDropdownOpen = !farmerDropdownOpen; if (farmerDropdownOpen) document.getElementById('farmer_search_input').focus();"
                                        class="px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-r-md flex items-center justify-center transition shrink-0">
                                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>

                            <!-- Live Dropdown List of Farmers -->
                            <div x-show="farmerDropdownOpen" 
                                 x-cloak
                                 class="absolute left-0 right-0 top-full mt-1 z-[999999] bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100">
                                <template x-for="(farmer, idx) in filteredFarmers" :key="farmer.id">
                                    <div @click="chooseFarmer(farmer)"
                                         @mouseenter="highlightedIndex = idx"
                                         :class="highlightedIndex === idx ? 'bg-emerald-50 text-emerald-950 font-semibold' : 'hover:bg-slate-50 text-slate-800'"
                                         class="px-3 py-1.5 cursor-pointer transition flex items-center justify-between text-xs">
                                        <div class="min-w-0 pr-2">
                                            <div class="truncate flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono text-[10px] text-slate-800 font-bold" x-text="farmer.farmer_code || ('FAR-' + farmer.id)"></span>
                                                <span class="font-bold text-slate-900" x-text="farmer.name"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate flex items-center gap-2">
                                                <span x-show="farmer.phone" x-text="farmer.phone"></span>
                                                <span x-show="farmer.village" x-text="'• ' + farmer.village"></span>
                                                <span x-show="config.route_wise_collection === 'On' && farmer.route && farmer.route.name"
                                                      class="text-emerald-600 font-semibold"
                                                      x-text="'• Route: ' + (farmer.route ? farmer.route.name : '')"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="text-[10px] px-2 py-0.5 rounded font-bold border whitespace-nowrap" 
                                                  :class="farmer.animal_type === 'buffalo' ? 'bg-indigo-50 text-indigo-800 border-indigo-200' : 'bg-amber-50 text-amber-800 border-amber-200'"
                                                  x-text="farmer.animal_type === 'buffalo' ? '🐃 Buffalo' : '🐄 Cow (CM)'"></span>
                                            <span class="text-[9.5px] px-1.5 py-0.5 rounded font-bold border"
                                                  :class="(farmer.pricing_type === 'fixed_rate' || ((farmer.cow_milk_rate > 0 || farmer.buffalo_milk_rate > 0) && !farmer.rate_chart_id)) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                                  x-text="(farmer.pricing_type === 'fixed_rate' || ((farmer.cow_milk_rate > 0 || farmer.buffalo_milk_rate > 0) && !farmer.rate_chart_id)) ? '💵 Fixed' : '📊 Chart'"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredFarmers.length === 0" class="p-3 text-center text-xs text-slate-400">
                                    No farmer matches "<span class="font-semibold text-slate-600" x-text="farmerSearchText"></span>"
                                </div>
                            </div>

                            <input type="hidden" name="farmer_id" :value="form.farmer_id">
                        </div>

                        <!-- Sample No. (Auto, 3 cols) -->
                        <div class="sm:col-span-3">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Sample No. (Auto)</label>
                            <div class="h-7 px-2.5 bg-slate-100 border border-slate-200 rounded-md flex items-center justify-center font-mono font-bold text-[11px] text-slate-800" x-text="sampleNo"></div>
                        </div>
                    </div>

                    <!-- ROW 3: MILK QTY (LTR.) | FAT (%) | CLR | SNF (%) | RATE (₹/LTR.) | TOTAL AMOUNT (WIDER) -->
                    <div class="flex flex-wrap sm:flex-nowrap items-end gap-2">
                        <!-- Milk Qty (Ltr.) -->
                        <div class="w-[47%] sm:flex-1 min-w-[85px]">
                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider">Milk Qty (Ltr.) *</label>
                            <input type="number" 
                                   id="collection_quantity_input"
                                   step="0.01" 
                                   min="0.1"
                                   x-model.number="form.quantity_liters" 
                                   @input="onQuantityChange()" 
                                   required 
                                   placeholder="0.00"
                                   class="w-full h-7 px-2.5 text-[11px] font-bold text-slate-900 border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- FAT (%) -->
                        <div x-show="config.show_fat" class="w-[47%] sm:flex-1 min-w-[75px]">
                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider">FAT (%) *</label>
                            <input type="number" 
                                   id="collection_fat_input"
                                   step="0.1" 
                                   min="0.5" 
                                   max="20"
                                   x-model.number="form.fat" 
                                   @input="onFatChange()" 
                                   :required="config.show_fat"
                                   placeholder="4.0"
                                   class="w-full h-7 px-2.5 text-[11px] font-bold text-slate-900 border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- CLR -->
                        <div x-show="config.show_clr" class="w-[47%] sm:flex-1 min-w-[75px]">
                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider"
                                   x-text="config.auto_clr ? 'CLR (Auto)' : 'CLR *'"></label>
                            <input type="number" 
                                   id="collection_clr_input"
                                   step="0.5" 
                                   min="5" 
                                   max="50"
                                   x-model.number="form.clr" 
                                   @input="onClrChange()" 
                                   :readonly="config.auto_clr"
                                   :required="config.show_clr && !config.auto_clr"
                                   placeholder="28"
                                   :class="config.auto_clr ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white text-slate-900'"
                                   class="w-full h-7 px-2.5 text-[11px] font-bold border border-slate-200 rounded-md focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- SNF (%) -->
                        <div x-show="config.show_snf" class="w-[47%] sm:flex-1 min-w-[75px]">
                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider"
                                   x-text="config.auto_snf ? 'SNF (%) (Auto)' : 'SNF (%) *'"></label>
                            <input type="number" 
                                   id="collection_snf_input"
                                   step="0.01" 
                                   min="2" 
                                   max="20"
                                   x-model.number="form.snf" 
                                   @input="onSnfChange()" 
                                   :readonly="config.auto_snf"
                                   :required="config.show_snf && !config.auto_snf"
                                   placeholder="8.5"
                                   :class="config.auto_snf ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-slate-50 focus:bg-white text-slate-900'"
                                   class="w-full h-7 px-2.5 text-[11px] font-bold border border-slate-200 rounded-md focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- RATE (₹/LTR.) -->
                        <div class="w-[47%] sm:flex-1 min-w-[90px]">
                            <label class="block text-[9px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider">Rate (₹/Ltr.) <span class="text-[8px] text-slate-400 font-normal">(Auto)</span></label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0"
                                   x-model.number="form.applied_rate" 
                                   @input="recalculateTotals()" 
                                   required
                                   class="w-full h-7 px-2.5 text-[11px] font-bold text-slate-900 border border-slate-200 rounded-md bg-slate-100 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- Total Amount (Wider / Longer Box) -->
                        <div class="w-full sm:w-52 sm:min-w-[190px]">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Total Amount</label>
                            <div class="h-7 px-3 rounded-md bg-emerald-50/80 border border-emerald-200 flex items-center justify-between text-sm font-extrabold text-emerald-700 leading-none">
                                <span>₹</span>
                                <span x-text="netAmountFormatted">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Optional Notes & Adjustments -->
                    <div x-show="showAdvanced" x-cloak class="pt-1.5 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-600 mb-0.5">
                                Bonus / Additions (₹)
                                <span x-show="config.per_liter_addition > 0" class="text-emerald-600 font-bold" x-text="'(+₹' + config.per_liter_addition + '/L)'"></span>
                            </label>
                            <input type="number" step="0.5" x-model.number="form.bonus" @input="recalculateTotals()" placeholder="0.00"
                                   class="w-full h-7 px-2 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-600 mb-0.5">
                                Deduction / Charges (₹)
                                <span x-show="config.per_liter_deduction > 0" class="text-rose-600 font-bold" x-text="'(-₹' + config.per_liter_deduction + '/L)'"></span>
                            </label>
                            <input type="number" step="0.5" x-model.number="form.deduction" @input="recalculateTotals()" placeholder="0.00"
                                   class="w-full h-7 px-2 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-semibold text-slate-600 mb-0.5">Remarks</label>
                            <input type="text" x-model="form.notes" placeholder="Sample quality note..."
                                   class="w-full h-7 px-2 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- ROW 4 (BOTTOM ROW): + ADD CAN (LEFT) & SAVE / RESET / PRINT BUTTONS (RIGHT) -->
                    <div class="pt-1.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <!-- Left: + Add Can & Can Details Inline -->
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" 
                                    @click="addCan()"
                                    class="h-7 inline-flex items-center gap-1 px-2.5 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-[11px] font-semibold shadow-2xs transition">
                                <i data-lucide="plus" class="w-3 h-3 text-emerald-600"></i>
                                <span>+ Add Can</span>
                            </button>

                            <span x-show="cans.length === 0" class="text-[11px] text-slate-400">
                                Can Details: <span class="text-slate-500">No cans added.</span>
                            </span>

                            <div x-show="cans.length > 0" x-cloak class="flex flex-wrap items-center gap-1.5">
                                <template x-for="(can, cIdx) in cans" :key="cIdx">
                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-50 border border-slate-200 text-[11px]">
                                        <span class="font-bold text-slate-700" x-text="'Can #' + (cIdx + 1) + ':'"></span>
                                        <input type="number" step="0.1" min="0.1" x-model.number="can.liters" @input="syncCansToQuantity()" placeholder="Ltr" class="w-14 px-1.5 py-0.5 text-[11px] font-bold bg-white border border-slate-300 rounded focus:outline-none">
                                        <span class="text-[10px] text-slate-500">L</span>
                                        <button type="button" @click="removeCan(cIdx)" class="text-rose-500 hover:text-rose-700 font-bold px-0.5">×</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Right: Reset, Save & Print, Save -->
                        <div class="flex items-center gap-1.5 ml-auto">
                            <button type="button" 
                                    @click="resetForm()" 
                                    class="h-7 px-3 text-[11px] font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-md transition">
                                Reset
                            </button>

                            <button type="button" 
                                    @click="submitCollection(true)" 
                                    :disabled="isSubmitting"
                                    title="Save & Print Slip"
                                    class="h-7 px-3 text-[11px] font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 disabled:opacity-50 rounded-md transition flex items-center gap-1">
                                <i data-lucide="printer" class="w-3 h-3 text-emerald-600"></i>
                                <span>Save & Print</span>
                            </button>

                            <button type="button" 
                                    @click="submitCollection(false)" 
                                    :disabled="isSubmitting"
                                    class="h-7 px-3.5 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-md shadow-xs transition flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3" x-show="!isSubmitting"></i>
                                <span x-show="!isSubmitting" x-text="isEditMode ? 'Update Entry' : 'Save Entry'"></span>
                                <span x-show="isSubmitting" x-cloak>Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Integrated Footer Bar: Previous Collection (Controlled by Center Information 'Show Previous Collection' Setting) -->
            @if(($collectionConfig['show_previous_collection'] ?? "Yesterday's both shift") !== 'None')
            <div x-show="(previousCollection.mode || config.show_previous_collection) !== 'None'"
                 class="px-3 py-1 bg-slate-50/80 border-t border-slate-100 rounded-b-xl flex flex-wrap items-center justify-between gap-1.5 text-[11px]">
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-slate-700">Previous Collection:</span>
                    <template x-if="(previousCollection.mode || config.show_previous_collection) === 'Previous Shift'">
                        <span class="text-slate-500">Previous Shift (<span class="font-bold text-slate-800 font-mono" x-text="previousCollection.prev_shift_date || previousCollection.yesterday_date"></span>)</span>
                    </template>
                    <template x-if="(previousCollection.mode || config.show_previous_collection) !== 'Previous Shift'">
                        <span class="text-slate-500">Yesterday (<span class="font-bold text-slate-800 font-mono" x-text="previousCollection.yesterday_date"></span>)</span>
                    </template>
                    <span x-show="selectedFarmer.name" x-cloak class="px-1.5 py-0.5 rounded bg-white text-slate-800 border border-slate-200 text-[10px] font-mono font-bold"
                          x-text="selectedFarmer.code + ' • ' + selectedFarmer.name"></span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 text-[10px]">
                    <!-- Mode: Previous Shift Only -->
                    <template x-if="(previousCollection.mode || config.show_previous_collection) === 'Previous Shift'">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded border"
                              :class="previousCollection.prev_shift_type === 'morning' ? 'bg-amber-50 border-amber-200/80 text-amber-900' : 'bg-indigo-50 border-indigo-200/80 text-indigo-900'">
                            <span x-text="previousCollection.prev_shift_type === 'morning' ? '☀️' : '🌙'"></span>
                            <span class="font-bold capitalize" x-text="(previousCollection.prev_shift_type || 'Previous') + ':'"></span>
                            <span class="font-semibold" x-text="previousCollection.prev_shift_text || 'No entry'"></span>
                        </span>
                    </template>

                    <!-- Mode: Yesterday's both shift -->
                    <template x-if="(previousCollection.mode || config.show_previous_collection) !== 'Previous Shift'">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200/80 text-amber-900">
                                <span>☀️</span>
                                <span class="font-bold">Morning:</span>
                                <span class="font-semibold" x-text="previousCollection.morning">No entry</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-indigo-50 border border-indigo-200/80 text-indigo-900">
                                <span>🌙</span>
                                <span class="font-bold">Evening:</span>
                                <span class="font-semibold" x-text="previousCollection.evening">No entry</span>
                            </span>
                        </div>
                    </template>

                    <span class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-700">
                        Avg Fat (5d): <span class="font-bold text-slate-900" x-text="previousCollection.avg_fat_5_days">0.00%</span>
                        <span class="text-slate-300 mx-0.5">|</span>
                        Avg CLR (5d): <span class="font-bold text-slate-900" x-text="previousCollection.avg_clr_5_days || '0.0'">0.0</span>
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT COLUMN (4 cols): UNIFIED SHIFT SUMMARY + ACTIVE RATE CHART CARD -->
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-2.5 py-1 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                    <h4 class="text-[11px] font-bold text-slate-900" x-text="'Shift Summary (' + shiftSummary.date_label + ')'">
                        Shift Summary ({{ $liveStats['shift_summary']['date_label'] }})
                    </h4>
                    <i data-lucide="bar-chart-3" class="w-3 h-3 text-slate-400"></i>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px] text-slate-600">
                        <thead class="bg-slate-50/60 border-b border-slate-200/80 text-[9px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-1 px-2">Type</th>
                                <th class="py-1 px-1 text-center">Mem</th>
                                <th class="py-1 px-1 text-right">Milk (L)</th>
                                <th class="py-1 px-1 text-center">Fat</th>
                                <th class="py-1 px-1 text-center">SNF</th>
                                <th class="py-1 px-2 text-right">Amt (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[11px]">
                            <!-- Cow Row -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-1 px-2">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                                        🐄 Cow
                                    </span>
                                </td>
                                <td class="py-1 px-1 text-center font-semibold text-slate-700" x-text="shiftSummary.cow.members">0</td>
                                <td class="py-1 px-1 text-right font-semibold text-slate-800" x-text="shiftSummary.cow.milk">0.00</td>
                                <td class="py-1 px-1 text-center text-slate-600" x-text="shiftSummary.cow.avg_fat + '%'">0.0%</td>
                                <td class="py-1 px-1 text-center text-slate-600" x-text="shiftSummary.cow.avg_snf + '%'">0.0%</td>
                                <td class="py-1 px-2 text-right font-bold text-slate-900" x-text="shiftSummary.cow.amount">0.00</td>
                            </tr>
                            <!-- Buffalo Row -->
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-1 px-2">
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 whitespace-nowrap">
                                        🐃 Buff
                                    </span>
                                </td>
                                <td class="py-1 px-1 text-center font-semibold text-slate-700" x-text="shiftSummary.buffalo.members">0</td>
                                <td class="py-1 px-1 text-right font-semibold text-slate-800" x-text="shiftSummary.buffalo.milk">0.00</td>
                                <td class="py-1 px-1 text-center text-slate-600" x-text="shiftSummary.buffalo.avg_fat + '%'">0.0%</td>
                                <td class="py-1 px-1 text-center text-slate-600" x-text="shiftSummary.buffalo.avg_snf + '%'">0.0%</td>
                                <td class="py-1 px-2 text-right font-bold text-slate-900" x-text="shiftSummary.buffalo.amount">0.00</td>
                            </tr>
                            <!-- Total (B+C) Row -->
                            <tr class="bg-slate-50/90 font-bold">
                                <td class="py-1 px-2 text-slate-900 whitespace-nowrap">
                                    Total <span class="text-[9px] font-semibold text-slate-500">(B+C)</span>
                                </td>
                                <td class="py-1 px-1 text-center text-slate-900" x-text="shiftSummary.total.members">0</td>
                                <td class="py-1 px-1 text-right text-emerald-700 font-extrabold" x-text="shiftSummary.total.milk">0.00</td>
                                <td class="py-1 px-1 text-center text-slate-700" x-text="shiftSummary.total.avg_fat + '%'">0.0%</td>
                                <td class="py-1 px-1 text-center text-slate-700" x-text="shiftSummary.total.avg_snf + '%'">0.0%</td>
                                <td class="py-1 px-2 text-right text-emerald-700 font-extrabold" x-text="shiftSummary.total.amount">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Integrated Active Rate Chart Strip -->
            <div class="px-2.5 py-1.5 bg-slate-50/90 border-t border-slate-200/80 flex items-center justify-between gap-2 text-[10px]">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1 truncate">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 shrink-0"></span>
                        <span class="text-slate-500 font-semibold shrink-0">Rate Chart:</span>
                        <span class="font-bold text-slate-900 truncate" x-text="activeChart.name"></span>
                    </div>
                    <div class="flex items-center gap-2 text-[9px] text-slate-500 truncate pl-2.5">
                        <span>Structure: <strong class="text-emerald-700" x-text="activeChart.rate_structure"></strong></span>
                        <span>•</span>
                        <span>From: <strong class="text-slate-700 font-mono" x-text="activeChart.effective_date"></strong></span>
                    </div>
                </div>
                <button type="button"
                        @click="rateChartModalOpen = true"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold shrink-0 transition">
                    <i data-lucide="eye" class="w-3 h-3 text-emerald-600"></i>
                    <span>View Chart</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MILK DETAILS / COLLECTIONS TABLE CARD WITH INTEGRATED COMPACT FILTERS     -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden w-full max-w-full">
        
        <!-- White Card Header with Integrated Compact Filters (Matching Farmer List Theme) -->
        <div class="px-3 py-1.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 shrink-0">
                <h3 class="text-xs font-bold text-slate-900">
                    Milk Details / दूध संकलन सूची
                </h3>
                <span class="px-2 py-0.5 text-[10px] rounded-full bg-slate-100 text-slate-700 border border-slate-200 font-mono font-bold">{{ $collections->total() }}</span>
            </div>

            <!-- Compact Inline Filters inside Card Header -->
            <form method="GET" action="{{ route('collections.index') }}" class="flex flex-wrap items-center gap-1 text-xs">
                <div class="relative">
                    <i data-lucide="search" class="w-3 h-3 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search farmer, code..."
                           class="h-7 pl-6 pr-2 text-[11px] bg-slate-50 border border-slate-200 rounded-md text-slate-700 placeholder-slate-400 w-36 sm:w-40 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                <input type="date"
                       name="from_date"
                       value="{{ request('from_date') }}"
                       title="From Date"
                       class="h-7 px-1.5 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">

                <input type="date"
                       name="to_date"
                       value="{{ request('to_date') }}"
                       title="To Date"
                       class="h-7 px-1.5 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">

                <select name="shift" class="h-7 px-1.5 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>☀️ Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>🌙 Evening</option>
                </select>

                <select name="milk_type" class="h-7 px-1.5 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    <option value="">All Milk Types</option>
                    <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>🐄 Cow (CM)</option>
                    <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>🐃 Buffalo</option>
                </select>

                @if(($collectionConfig['route_wise_collection'] ?? 'On') === 'On' && isset($routes) && $routes->count() > 0)
                <select name="route_id" class="h-7 px-1.5 text-[11px] border border-slate-200 rounded-md bg-slate-50 focus:bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                    <option value="">All Routes</option>
                    @foreach($routes as $rt)
                        <option value="{{ $rt->id }}" {{ (string) request('route_id') === (string) $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="h-7 px-2.5 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-2xs transition flex items-center gap-1">
                    <i data-lucide="filter" class="w-3 h-3"></i>
                    <span>Apply</span>
                </button>

                @if(request()->anyFilled(['search', 'shift', 'milk_type', 'center_id', 'route_id', 'from_date', 'to_date']))
                    <a href="{{ route('collections.index') }}" class="h-7 px-2 text-[11px] font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-md flex items-center transition">
                        Reset
                    </a>
                @endif

                <button type="submit" name="export" value="csv" class="h-7 inline-flex items-center gap-1 px-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-[11px] font-semibold rounded-md transition">
                    <i data-lucide="download" class="w-3 h-3 text-emerald-600"></i>
                    <span>CSV</span>
                </button>
            </form>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600" id="collections-table">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-1.5 px-2.5 w-16 text-center">Sample No.</th>
                        <th class="py-1.5 px-2.5">Farmer Code</th>
                        <th class="py-1.5 px-2.5">Farmer Name (English)</th>
                        <th class="py-1.5 px-2.5">Shift</th>
                        <th class="py-1.5 px-2.5">Milk Type</th>
                        <th class="py-1.5 px-2.5 text-right">Milk (Ltr.)</th>
                        <th class="py-1.5 px-2.5 text-center">FAT (%)</th>
                        <th class="py-1.5 px-2.5 text-center">CLR</th>
                        <th class="py-1.5 px-2.5 text-center">SNF (%)</th>
                        <th class="py-1.5 px-2.5 text-right">Rate (₹)</th>
                        <th class="py-1.5 px-2.5 text-right">Amount (₹)</th>
                        <th class="py-1.5 px-2.5 text-center pr-3 w-24">Action</th>
                    </tr>
                </thead>
                <tbody id="collections-table-body" class="divide-y divide-slate-100">
                    @forelse($collections as $idx => $col)
                        <tr id="row-col-{{ $col->id }}" class="hover:bg-slate-50/70 transition group">
                            <!-- Sample No -->
                            <td class="py-1.5 px-2.5 text-center text-slate-500 font-medium" title="{{ $col->receipt_number }}">
                                {{ $collections->total() - (($collections->currentPage() - 1) * $collections->perPage()) - $idx }}
                            </td>

                            <!-- Farmer Code -->
                            <td class="py-1.5 px-2.5 font-mono font-bold text-slate-900">
                                <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-800 text-[11px] font-mono border border-slate-200">
                                    {{ $col->farmer->farmer_code ?? '—' }}
                                </span>
                            </td>

                            <!-- Farmer Name -->
                            <td class="py-1.5 px-2.5">
                                <div class="flex items-center gap-2">
                                    @if($col->farmer && $col->farmer->photo_url)
                                        <img src="{{ $col->farmer->photo_url }}" alt="{{ $col->farmer->name }}" class="w-5 h-5 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                                    @endif
                                    <div class="truncate">
                                        <a href="{{ $col->farmer ? route('farmers.show', $col->farmer) : '#' }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                            {{ $col->farmer->name ?? 'N/A' }}
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <!-- Shift -->
                            <td class="py-1.5 px-2.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $col->shift === 'morning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-800 border border-indigo-200' }}">
                                    {{ $col->shift === 'morning' ? '☀️ Morning' : '🌙 Evening' }}
                                </span>
                            </td>

                            <!-- Milk Type (With Cow & Buffalo Icons matching Farmer List!) -->
                            <td class="py-1.5 px-2.5 whitespace-nowrap">
                                @if($col->milk_type === 'cow')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        🐄 Cow (CM)
                                    </span>
                                @elseif($col->milk_type === 'buffalo')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                        🐃 Buffalo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        🥛 Mixed
                                    </span>
                                @endif
                            </td>

                            <!-- Quantity (Ltr.) -->
                            <td class="py-1.5 px-2.5 text-right font-bold text-slate-900 font-mono">
                                {{ number_format($col->quantity_liters, 2) }}
                            </td>

                            <!-- FAT (%) -->
                            <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">
                                {{ number_format($col->fat, 2) }}
                            </td>

                            <!-- CLR -->
                            <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">
                                {{ $col->clr ? number_format($col->clr, 2) : '0.00' }}
                            </td>

                            <!-- SNF (%) -->
                            <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">
                                {{ number_format($col->snf, 2) }}
                            </td>

                            <!-- Rate (₹) & Pricing Scheme -->
                            <td class="py-1.5 px-2.5 text-right font-mono font-semibold text-slate-800">
                                <div>₹{{ number_format($col->applied_rate, 2) }}</div>
                                @if($col->pricing_type === 'fixed_rate')
                                    <span class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1 py-0.2 rounded border border-amber-200 inline-block">Fixed</span>
                                @elseif($col->rateChart)
                                    <span class="text-[9px] font-medium text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-200 truncate max-w-[85px] inline-block" title="{{ $col->rateChart->name }}">
                                        {{ \Illuminate\Support\Str::limit($col->rateChart->name, 10) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Amount (₹) -->
                            <td class="py-1.5 px-2.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($col->net_amount, 2) }}
                            </td>

                            <!-- Action Buttons (Matching Farmer List soft pastel rounded-lg buttons) -->
                            <td class="py-1.5 px-2.5 text-center pr-3">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- View / Print Slip Button -->
                                    <a href="{{ route('collections.slip', $col) }}" 
                                       target="_blank" 
                                       title="View / Print Slip" 
                                       class="w-6 h-6 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>

                                    <!-- Edit Collection Button -->
                                    <button type="button" 
                                            @click="startEditCollection({
                                                id: {{ $col->id }},
                                                receipt_number: '{{ $col->receipt_number }}',
                                                farmer_id: {{ $col->farmer_id }},
                                                farmer_code: '{{ addslashes($col->farmer->farmer_code ?? '') }}',
                                                farmer_name: '{{ addslashes($col->farmer->name ?? '') }}',
                                                farmer_phone: '{{ addslashes($col->farmer->phone ?? '') }}',
                                                farmer_village: '{{ addslashes($col->farmer->village ?? '') }}',
                                                collection_center_id: {{ $col->collection_center_id ?? 'null' }},
                                                collection_date: '{{ $col->collection_date ? $col->collection_date->format('Y-m-d') : date('Y-m-d') }}',
                                                shift: '{{ $col->shift }}',
                                                milk_type: '{{ $col->milk_type }}',
                                                quantity_liters: {{ (float) $col->quantity_liters }},
                                                fat: {{ (float) $col->fat }},
                                                snf: {{ (float) $col->snf }},
                                                clr: {{ (float) ($col->clr ?? 28) }},
                                                bonus: {{ (float) ($col->bonus ?? 0) }},
                                                deduction: {{ (float) ($col->deduction ?? 0) }},
                                                applied_rate: {{ (float) $col->applied_rate }},
                                                notes: '{{ addslashes($col->notes ?? '') }}'
                                            })"
                                            title="Edit Collection"
                                            class="w-6 h-6 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            @click="deleteCollection({{ $col->id }}, '{{ $col->receipt_number }}')"
                                            title="Delete Collection" 
                                            class="w-6 h-6 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-collections-row">
                            <td colspan="12" class="py-6 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-xs font-semibold text-slate-600">No collection entries found</span>
                                    <p class="text-[11px] text-slate-400">Use the intake form above to record milk from farmers.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-2.5 border-t border-slate-100">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW ACTIVE RATE CHART MODAL                                              -->
    <!-- ========================================================================= -->
    <div x-show="rateChartModalOpen" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         @keydown.escape.window="rateChartModalOpen = false">
        <div @click.away="rateChartModalOpen = false" 
             class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <h3 class="text-sm font-extrabold text-slate-900" x-text="activeChart.name || 'Active Rate Chart'"></h3>
                </div>
                <button type="button" @click="rateChartModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Milk Type</span>
                        <span class="font-bold text-slate-800 capitalize" x-text="form.milk_type"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Collection Mode</span>
                        <span class="font-bold text-blue-700" x-text="config.collection_type"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Effective From</span>
                        <span class="font-bold text-slate-800" x-text="activeChart.effective_date"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Rate Structure</span>
                        <span class="font-bold text-emerald-700" x-text="activeChart.rate_structure"></span>
                    </div>
                </div>

                <template x-if="activeChart.details">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-200">
                            <span class="font-semibold text-emerald-900">Base / Fixed Rate</span>
                            <span class="font-black text-emerald-700 text-sm" x-text="'₹ ' + Number(activeChart.details.base_rate || form.applied_rate || 0).toFixed(2) + ' / Ltr'"></span>
                        </div>

                        <template x-if="activeChart.details.fat_steps && activeChart.details.fat_steps.length > 0">
                            <div>
                                <h4 class="font-bold text-slate-700 mb-1.5">FAT Rate Steps</h4>
                                <table class="w-full text-left border border-slate-200 rounded-lg overflow-hidden">
                                    <thead class="bg-slate-100 text-[10px] uppercase text-slate-600">
                                        <tr>
                                            <th class="py-1.5 px-2.5">From</th>
                                            <th class="py-1.5 px-2.5">To</th>
                                            <th class="py-1.5 px-2.5 text-right">Rate / Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(st, sIdx) in activeChart.details.fat_steps" :key="sIdx">
                                            <tr>
                                                <td class="py-1.5 px-2.5" x-text="st.from ?? st.step ?? '-'"></td>
                                                <td class="py-1.5 px-2.5" x-text="st.to ?? '-'"></td>
                                                <td class="py-1.5 px-2.5 text-right font-bold text-slate-800" x-text="'₹ ' + (st.amount ?? 0)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="activeChart.details.snf_steps && activeChart.details.snf_steps.length > 0">
                            <div>
                                <h4 class="font-bold text-slate-700 mb-1.5">SNF / CLR Rate Steps</h4>
                                <table class="w-full text-left border border-slate-200 rounded-lg overflow-hidden">
                                    <thead class="bg-slate-100 text-[10px] uppercase text-slate-600">
                                        <tr>
                                            <th class="py-1.5 px-2.5">From</th>
                                            <th class="py-1.5 px-2.5">To</th>
                                            <th class="py-1.5 px-2.5 text-right">Rate / Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(st, sIdx) in activeChart.details.snf_steps" :key="sIdx">
                                            <tr>
                                                <td class="py-1.5 px-2.5" x-text="st.from ?? st.step ?? '-'"></td>
                                                <td class="py-1.5 px-2.5" x-text="st.to ?? '-'"></td>
                                                <td class="py-1.5 px-2.5 text-right font-bold text-slate-800" x-text="'₹ ' + (st.amount ?? 0)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ route('rates.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline">
                    All Rate Charts
                </a>
                <div class="flex items-center gap-2">
                    <template x-if="activeChartId">
                        <a :href="'/rates/' + activeChartId + '/edit'" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                            Edit Rate Chart
                        </a>
                    </template>
                    <button type="button" @click="rateChartModalOpen = false" class="px-3.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Live Toast Notifications -->
    <div x-show="toast.show" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="fixed bottom-5 right-5 z-50 max-w-sm p-3 rounded-xl shadow-xl flex items-center gap-2.5 border text-xs font-semibold backdrop-blur-md"
         :class="toast.type === 'success' ? 'bg-emerald-950/90 text-emerald-100 border-emerald-600/50' : 'bg-rose-950/90 text-rose-100 border-rose-600/50'">
        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
             :class="toast.type === 'success' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300'">
            <i :data-lucide="toast.type === 'success' ? 'check' : 'alert-circle'" class="w-3.5 h-3.5"></i>
        </div>
        <div class="flex-1" x-html="toast.message"></div>
        <button type="button" @click="toast.show = false" class="text-slate-400 hover:text-white transition">
            <i data-lucide="x" class="w-3 h-3"></i>
        </button>
    </div>

    </div>
    <!-- END TAB 1 -->

    <!-- TAB 2: MILK SALE -->
    <div x-show="currentTopTab === 'milk_sales'" x-cloak class="space-y-4 w-full">
        @include('collections.partials.milk_sales_tab')
    </div>

    <!-- TAB 3: PRODUCT SALE -->
    <div x-show="currentTopTab === 'product_sales'" x-cloak class="space-y-4 w-full">
        @include('collections.partials.product_sales_tab')
    </div>

    <!-- TAB 4: DEDUCTIONS -->
    <div x-show="currentTopTab === 'deductions'" x-cloak class="space-y-4 w-full">
        @include('collections.partials.deductions_tab')
    </div>

</div>

@push('scripts')
<script>
function milkCollectionManager() {
    return {
        isEditMode: false,
        isSubmitting: false,
        showAdvanced: false,
        rateChartModalOpen: false,
        activeChartId: @json($initialRateInfo['chart_id'] ?? null),
        rateChartFeedback: '{{ addslashes($initialRateInfo['chart_name'] ?? 'Rate Chart') }} = ₹{{ number_format($initialRateInfo['rate'] ?? 42.50, 2) }}/L',
        config: @json($collectionConfig),
        sampleNo: {{ $liveStats['sample_no'] ?? 1 }},
        cans: [],

        shiftSummary: @json($liveStats['shift_summary']),
        previousCollection: @json($liveStats['previous_collection']),

        activeChart: {
            name: @json($initialRateInfo['chart_name'] ?? 'Cow Collection Rate'),
            effective_date: @json($initialRateInfo['effective_date'] ?? date('d/m/Y')),
            rate_structure: @json($initialRateInfo['rate_structure'] ?? 'Standard Rate'),
            details: @json($initialRateInfo['chart_details'] ?? null)
        },
        
        toast: {
            show: false,
            message: '',
            type: 'success',
            timeout: null
        },

        metrics: {
            totalLiters: '{{ number_format($todayTotalLiters, 1) }}',
            totalAmount: '{{ number_format($todayTotalAmount, 2) }}',
            avgFat: '{{ number_format($avgFat, 1) }}',
            avgSnf: '{{ number_format($avgSnf, 1) }}'
        },

        allFarmers: @json($farmers),
        selectedRouteId: '',
        quickFarmerCode: '',
        farmerSearchText: '',
        farmerDropdownOpen: false,
        highlightedIndex: 0,

        selectedFarmer: {
            id: '',
            code: '',
            name: '',
            phone: '',
            village: '',
            animal: '{{ $collectionConfig['default_milk_type'] }}'
        },

        form: {
            id: null,
            receipt_number: '{{ $nextReceipt }}',
            farmer_id: '',
            collection_center_id: '{{ $centers->first()->id ?? "" }}',
            collection_date: '{{ $today }}',
            shift: '{{ $currentShift }}',
            milk_type: '{{ $collectionConfig['default_milk_type'] }}',
            quantity_liters: '',
            fat: 4.0,
            clr: 28.0,
            snf: 8.5,
            bonus: 0,
            deduction: 0,
            applied_rate: {{ (float) ($initialRateInfo['rate'] ?? 38.00) }},
            notes: ''
        },

        init() {
            const self = this;
            this.$nextTick(() => {
                if (self.config.auto_snf) {
                    self.calculateSnfFromClr();
                } else if (self.config.auto_clr) {
                    self.calculateClrFromSnf();
                }
                self.applyPerLiterCharges();
                self.triggerRateCalculation();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        addCan() {
            this.cans.push({ liters: '' });
        },

        removeCan(idx) {
            this.cans.splice(idx, 1);
            this.syncCansToQuantity();
        },

        syncCansToQuantity() {
            if (this.cans.length === 0) return;
            const total = this.cans.reduce((acc, c) => acc + (parseFloat(c.liters) || 0), 0);
            if (total > 0) {
                this.form.quantity_liters = parseFloat(total.toFixed(2));
                this.onQuantityChange();
            }
        },

        get filteredFarmers() {
            let list = this.allFarmers;
            if (this.config.route_wise_collection === 'On' && this.selectedRouteId) {
                list = list.filter(f => String(f.route_id || '') === String(this.selectedRouteId));
            }
            if (!this.farmerSearchText || this.farmerSearchText.trim() === '') {
                return list;
            }
            const q = this.farmerSearchText.toLowerCase().trim();
            return list.filter(f => {
                const code = (f.farmer_code || '').toLowerCase();
                const num = code.replace(/\D/g, '') || String(f.id);
                const name = (f.name || '').toLowerCase();
                const phone = (f.phone || '').toLowerCase();
                const village = (f.village || '').toLowerCase();
                return code.indexOf(q) > -1 || 
                       num.indexOf(q) > -1 || 
                       name.indexOf(q) > -1 || 
                       phone.indexOf(q) > -1 || 
                       village.indexOf(q) > -1;
            });
        },

        highlightNextFarmer() {
            if (this.highlightedIndex < this.filteredFarmers.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevFarmer() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlightedFarmer() {
            if (this.filteredFarmers.length > 0 && this.filteredFarmers[this.highlightedIndex]) {
                this.chooseFarmer(this.filteredFarmers[this.highlightedIndex]);
            }
        },

        chooseFarmer(farmer) {
            if (!farmer) return;
            this.form.farmer_id = farmer.id;
            const code = farmer.farmer_code || ('FAR-' + farmer.id);
            const numCode = code.replace(/\D/g, '') || String(farmer.id);
            this.quickFarmerCode = numCode.padStart(3, '0');
            this.farmerSearchText = farmer.name;
            this.selectedFarmer = {
                id: farmer.id,
                code: code,
                name: farmer.name,
                phone: farmer.phone || '',
                village: farmer.village || '',
                animal: farmer.animal_type || 'cow'
            };
            if (this.config.route_wise_collection === 'On' && farmer.route_id) {
                this.selectedRouteId = String(farmer.route_id);
            }
            if (farmer.animal_type && this.config.allowed_milk_types.includes(farmer.animal_type)) {
                this.form.milk_type = farmer.animal_type;
            }
            this.farmerDropdownOpen = false;
            this.applyPerLiterCharges();
            this.triggerRateCalculation();
            this.$nextTick(() => {
                const qInput = document.getElementById('collection_quantity_input');
                if (qInput) qInput.focus();
            });
        },

        lookupFarmerByCode() {
            var raw = (this.quickFarmerCode || '').toString().trim();
            if (!raw) return;

            var numOnly = raw.replace(/\D/g, '').replace(/^0+/, '') || '0';
            var found = null;

            found = this.allFarmers.find(f => {
                var code = (f.farmer_code || '').trim();
                var num = (code.replace(/\D/g, '') || String(f.id)).replace(/^0+/, '') || '0';
                return num === numOnly || code.toLowerCase() === raw.toLowerCase() || String(f.id) === raw;
            });

            if (!found && numOnly) {
                found = this.allFarmers.find(f => {
                    var code = (f.farmer_code || '').trim();
                    var num = code.replace(/\D/g, '') || String(f.id);
                    return num.indexOf(numOnly) > -1 || code.toLowerCase().indexOf(raw.toLowerCase()) > -1;
                });
            }

            if (found) {
                this.chooseFarmer(found);
            } else {
                this.showToast('Farmer code "' + raw + '" not found.', 'error');
            }
        },

        get netAmountFormatted() {
            const qty = parseFloat(this.form.quantity_liters) || 0;
            const rate = parseFloat(this.form.applied_rate) || 0;
            const bonus = parseFloat(this.form.bonus) || 0;
            const deduction = parseFloat(this.form.deduction) || 0;
            const total = (qty * rate) + bonus - deduction;
            return total.toFixed(2);
        },

        showToast(message, type = 'success') {
            const self = this;
            if (this.toast.timeout) clearTimeout(this.toast.timeout);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
            this.toast.timeout = setTimeout(() => {
                self.toast.show = false;
            }, 4500);
        },

        onQuantityChange() {
            this.applyPerLiterCharges();
            this.recalculateTotals();
            this.triggerRateCalculation();
        },

        applyPerLiterCharges() {
            const qty = parseFloat(this.form.quantity_liters) || 0;
            const gross = (parseFloat(this.form.applied_rate) || 0) * qty;
            const farmer = this.allFarmers.find(f => String(f.id) === String(this.form.farmer_id));

            let totalAdd = 0;
            let totalDed = 0;

            if (Array.isArray(this.config.addition_types) && this.config.addition_types.length > 0) {
                this.config.addition_types.forEach(add => {
                    let rate = 0;
                    if (add.is_all_farmers) {
                        rate = parseFloat(add.amount) || 0;
                    } else if (farmer) {
                        let fRates = farmer.extra_rates || {};
                        let key = add.name || '';
                        let lKey = key.toLowerCase().trim();
                        if (typeof fRates[key] !== 'undefined') {
                            rate = parseFloat(fRates[key]) || 0;
                        } else if (typeof fRates[lKey] !== 'undefined') {
                            rate = parseFloat(fRates[lKey]) || 0;
                        }
                    }

                    if (rate > 0) {
                        const unit = (add.unit || '').toLowerCase();
                        if (unit.includes('ltr') || unit.includes('liter')) {
                            totalAdd += qty * rate;
                        } else if (unit.includes('%')) {
                            totalAdd += (gross * rate) / 100;
                        } else {
                            totalAdd += rate;
                        }
                    }
                });
            } else if (this.config.per_liter_addition > 0) {
                totalAdd += qty * this.config.per_liter_addition;
            }

            if (Array.isArray(this.config.deduction_types) && this.config.deduction_types.length > 0) {
                this.config.deduction_types.forEach(ded => {
                    let rate = 0;
                    if (ded.is_all_farmers) {
                        rate = parseFloat(ded.amount) || 0;
                    } else if (farmer) {
                        let fRates = farmer.extra_rates || {};
                        let key = ded.name || '';
                        let lKey = key.toLowerCase().trim();
                        if (typeof fRates[key] !== 'undefined') {
                            rate = parseFloat(fRates[key]) || 0;
                        } else if (typeof fRates[lKey] !== 'undefined') {
                            rate = parseFloat(fRates[lKey]) || 0;
                        } else if (lKey.includes('building') && typeof farmer.building_fund !== 'undefined') {
                            rate = parseFloat(farmer.building_fund) || 0;
                        } else if (lKey.includes('grant') && typeof farmer.grant_amount !== 'undefined') {
                            rate = parseFloat(farmer.grant_amount) || 0;
                        } else if (lKey.includes('installment') && typeof farmer.installment !== 'undefined') {
                            rate = parseFloat(farmer.installment) || 0;
                        }
                    }

                    if (rate > 0) {
                        const unit = (ded.unit || '').toLowerCase();
                        if (unit.includes('ltr') || unit.includes('liter')) {
                            totalDed += qty * rate;
                        } else if (unit.includes('%')) {
                            totalDed += (gross * rate) / 100;
                        } else {
                            totalDed += rate;
                        }
                    }
                });
            } else if (this.config.per_liter_deduction > 0) {
                totalDed += qty * this.config.per_liter_deduction;
            }

            this.form.bonus = parseFloat(totalAdd.toFixed(2));
            this.form.deduction = parseFloat(totalDed.toFixed(2));
        },

        onFatChange() {
            if (this.config.auto_snf) {
                this.calculateSnfFromClr();
            } else if (this.config.auto_clr) {
                this.calculateClrFromSnf();
            }
            this.triggerRateCalculation();
        },

        onClrChange() {
            if (this.config.auto_snf) {
                this.calculateSnfFromClr();
            }
            this.triggerRateCalculation();
        },

        onSnfChange() {
            if (this.config.auto_clr) {
                this.calculateClrFromSnf();
            }
            this.triggerRateCalculation();
        },

        calculateSnfFromClr() {
            const clr = parseFloat(this.form.clr) || 0;
            const fat = parseFloat(this.form.fat) || 0;
            if (clr > 0 && fat > 0) {
                const calculatedSnf = (clr / 4) + (0.21 * fat) + 0.36;
                this.form.snf = parseFloat(calculatedSnf.toFixed(2));
            }
        },

        calculateClrFromSnf() {
            const snf = parseFloat(this.form.snf) || 0;
            const fat = parseFloat(this.form.fat) || 0;
            if (snf > 0 && fat > 0) {
                const calculatedClr = Math.max(0, (snf - (0.21 * fat) - 0.36) * 4);
                this.form.clr = parseFloat(calculatedClr.toFixed(1));
            }
        },

        recalculateTotals() {
            // Evaluated reactively via netAmountFormatted
        },

        applyLiveStatsResponse(data) {
            if (!data) return;
            if (data.shift_summary) {
                this.shiftSummary = data.shift_summary;
            }
            if (data.previous_collection) {
                this.previousCollection = data.previous_collection;
            }
            if (typeof data.sample_no !== 'undefined') {
                this.sampleNo = data.sample_no;
            }
        },

        triggerRateCalculation() {
            const fat = this.config.show_fat ? (parseFloat(this.form.fat) || 0) : 0;
            const snf = this.config.show_snf ? (parseFloat(this.form.snf) || 0) : 0;
            const clr = this.config.show_clr ? (parseFloat(this.form.clr) || 0) : 0;
            const milkType = this.form.milk_type || this.config.default_milk_type || 'cow';
            const shift = this.form.shift || 'morning';
            const collectionDate = this.form.collection_date || '{{ $today }}';
            const farmerId = this.form.farmer_id || null;
            const centerId = this.form.collection_center_id || null;
            const qty = parseFloat(this.form.quantity_liters) || 0;

            fetch('{{ route("collections.calc-rate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fat: fat,
                    snf: snf,
                    clr: clr,
                    milk_type: milkType,
                    shift: shift,
                    collection_date: collectionDate,
                    farmer_id: farmerId,
                    collection_center_id: centerId,
                    quantity_liters: qty
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && typeof data.rate !== 'undefined') {
                    this.form.applied_rate = parseFloat(data.rate);
                    this.activeChartId = data.chart_id || null;
                    this.activeChart = {
                        name: data.chart_name || 'Standard Rate Chart',
                        effective_date: data.effective_date || '{{ date("d/m/Y") }}',
                        rate_structure: data.rate_structure || this.config.collection_type,
                        details: data.chart_details || null
                    };
                    if (this.config.auto_snf && data.snf > 0) {
                        this.form.snf = parseFloat(data.snf);
                    }
                    if (this.config.auto_clr && data.clr > 0) {
                        this.form.clr = parseFloat(data.clr);
                    }
                    if (data.charges) {
                        this.form.bonus = parseFloat(data.suggested_bonus || 0);
                        this.form.deduction = parseFloat(data.suggested_deduction || 0);
                    }
                    this.rateChartFeedback = (data.chart_name || 'Rate Chart') + ' = ₹' + Number(data.rate).toFixed(2) + '/L';
                    this.applyLiveStatsResponse(data);
                }
            })
            .catch(() => {
                let fallbackRate = 38.0;
                if (this.config.collection_type === 'FAT only') {
                    fallbackRate = fat * (milkType === 'buffalo' ? 10.5 : 9.5);
                } else if (this.config.collection_type === 'CLR only') {
                    fallbackRate = clr * (milkType === 'buffalo' ? 1.85 : 1.45);
                } else if (milkType === 'buffalo') {
                    fallbackRate = 55.0;
                }
                this.form.applied_rate = parseFloat(fallbackRate.toFixed(2));
            });
        },

        startEditCollection(item) {
            this.isEditMode = true;
            this.form = {
                id: item.id,
                receipt_number: item.receipt_number,
                farmer_id: item.farmer_id,
                collection_center_id: item.collection_center_id || '{{ $centers->first()->id ?? "" }}',
                collection_date: item.collection_date,
                shift: item.shift,
                milk_type: item.milk_type,
                quantity_liters: item.quantity_liters,
                fat: item.fat,
                clr: item.clr || 28.0,
                snf: item.snf,
                bonus: item.bonus || 0,
                deduction: item.deduction || 0,
                applied_rate: item.applied_rate,
                notes: item.notes || ''
            };

            const f = this.allFarmers.find(x => x.id == item.farmer_id);
            if (f) {
                this.chooseFarmer(f);
            }

            document.getElementById('collection-entry-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
                this.triggerRateCalculation();
                const qtyInput = document.getElementById('collection_quantity_input');
                if (qtyInput) qtyInput.focus();
            });
        },

        cancelEdit() {
            this.isEditMode = false;
            this.resetForm();
            this.showToast('Edit mode cancelled', 'success');
        },

        resetForm() {
            this.isEditMode = false;
            this.cans = [];
            this.form = {
                id: null,
                receipt_number: '{{ $nextReceipt }}',
                farmer_id: '',
                collection_center_id: this.form.collection_center_id || '{{ $centers->first()->id ?? "" }}',
                collection_date: '{{ $today }}',
                shift: '{{ $currentShift }}',
                milk_type: this.config.default_milk_type || 'cow',
                quantity_liters: '',
                fat: 4.0,
                clr: 28.0,
                snf: 8.5,
                bonus: 0,
                deduction: 0,
                applied_rate: 38.00,
                notes: ''
            };
            this.quickFarmerCode = '';
            this.farmerSearchText = '';
            this.farmerDropdownOpen = false;
            this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: this.config.default_milk_type || 'cow' };
            if (this.config.auto_snf) {
                this.calculateSnfFromClr();
            } else if (this.config.auto_clr) {
                this.calculateClrFromSnf();
            }
            this.applyPerLiterCharges();
            this.triggerRateCalculation();
        },

        resetEntryInputsForNext() {
            this.form.id = null;
            this.form.farmer_id = '';
            this.form.quantity_liters = '';
            this.form.notes = '';
            this.form.bonus = 0;
            this.form.deduction = 0;
            this.cans = [];
            this.quickFarmerCode = '';
            this.farmerSearchText = '';
            this.farmerDropdownOpen = false;
            this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: this.config.default_milk_type || 'cow' };
            this.$nextTick(() => {
                const codeIn = document.getElementById('quick_farmer_code_input');
                if (codeIn) {
                    codeIn.focus();
                } else {
                    const searchIn = document.getElementById('farmer_search_input');
                    if (searchIn) searchIn.focus();
                }
            });
        },

        submitCollection(printSlip = false) {
            if (!this.form.farmer_id) {
                this.showToast('Please select or enter a Farmer Code first.', 'error');
                const codeIn = document.getElementById('quick_farmer_code_input');
                if (codeIn) {
                    codeIn.focus();
                } else {
                    const searchIn = document.getElementById('farmer_search_input');
                    if (searchIn) searchIn.focus();
                    this.farmerDropdownOpen = true;
                }
                return;
            }
            if (!this.form.quantity_liters || parseFloat(this.form.quantity_liters) <= 0) {
                this.showToast('Please enter a valid milk quantity in liters.', 'error');
                document.getElementById('collection_quantity_input').focus();
                return;
            }
            if (this.config.show_fat && (!this.form.fat || parseFloat(this.form.fat) <= 0)) {
                this.showToast('Please enter a valid FAT %.', 'error');
                const fatIn = document.getElementById('collection_fat_input');
                if (fatIn) fatIn.focus();
                return;
            }
            if (this.config.show_clr && !this.config.auto_clr && (!this.form.clr || parseFloat(this.form.clr) <= 0)) {
                this.showToast('Please enter a valid CLR reading.', 'error');
                const clrIn = document.getElementById('collection_clr_input');
                if (clrIn) clrIn.focus();
                return;
            }
            if (this.config.show_snf && !this.config.auto_snf && (!this.form.snf || parseFloat(this.form.snf) <= 0)) {
                this.showToast('Please enter a valid SNF %.', 'error');
                const snfIn = document.getElementById('collection_snf_input');
                if (snfIn) snfIn.focus();
                return;
            }

            this.isSubmitting = true;
            const url = this.isEditMode ? `/collections/${this.form.id}` : '{{ route("collections.store") }}';
            
            const payload = {
                farmer_id: this.form.farmer_id,
                collection_center_id: this.form.collection_center_id || $('#collection_center_select').val() || null,
                collection_date: this.form.collection_date,
                shift: this.form.shift,
                milk_type: this.form.milk_type,
                quantity_liters: this.form.quantity_liters,
                fat: this.config.show_fat ? this.form.fat : 0,
                snf: this.config.show_snf ? this.form.snf : 0,
                clr: this.config.show_clr ? this.form.clr : 0,
                bonus: this.form.bonus || 0,
                deduction: this.form.deduction || 0,
                applied_rate: this.form.applied_rate,
                notes: this.form.notes || ''
            };

            if (this.isEditMode) {
                payload._method = 'PUT';
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) {
                    let errMsg = data.message || 'Error occurred while saving entry.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then(data => {
                this.isSubmitting = false;
                this.showToast(data.message || 'Collection saved successfully!', 'success');
                this.applyLiveStatsResponse(data);

                if (printSlip && data.slip_url) {
                    window.open(data.slip_url, '_blank');
                }

                if (this.isEditMode) {
                    this.updateTableRow(data.collection);
                    this.isEditMode = false;
                    this.resetForm();
                } else {
                    this.prependTableRow(data.collection);
                    if (data.next_receipt) {
                        this.form.receipt_number = data.next_receipt;
                    }
                    this.resetEntryInputsForNext();
                }

                this.recalculateSummaryMetrics();
            })
            .catch(err => {
                this.isSubmitting = false;
                this.showToast(err.message || 'Failed to save collection.', 'error');
            });
        },

        deleteCollection(id, receipt) {
            if (!confirm(`Delete collection receipt ${receipt}? This will reverse the farmer balance.`)) {
                return;
            }

            fetch(`/collections/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Failed to delete record.');
                return data;
            })
            .then(data => {
                this.showToast(data.message || `Receipt ${receipt} deleted successfully.`, 'success');
                this.applyLiveStatsResponse(data);
                const row = document.getElementById(`row-col-${id}`);
                if (row) row.remove();
                this.recalculateSummaryMetrics();
            })
            .catch(err => {
                this.showToast(err.message || 'Failed to delete record.', 'error');
            });
        },

        prependTableRow(col) {
            const noRow = document.getElementById('no-collections-row');
            if (noRow) noRow.remove();

            const tbody = document.getElementById('collections-table-body');
            if (!tbody) return;

            const tr = document.createElement('tr');
            tr.id = `row-col-${col.id}`;
            tr.className = 'hover:bg-slate-50/70 transition group bg-emerald-50/30';
            tr.innerHTML = this.buildRowHtml(col);
            tbody.insertBefore(tr, tbody.firstChild);

            if (window.lucide) window.lucide.createIcons();
        },

        updateTableRow(col) {
            const tr = document.getElementById(`row-col-${col.id}`);
            if (tr) {
                tr.innerHTML = this.buildRowHtml(col);
                tr.classList.add('bg-amber-50/40');
                if (window.lucide) window.lucide.createIcons();
            }
        },

        buildRowHtml(col) {
            const farmerName = (col.farmer ? col.farmer.name : 'N/A');
            const farmerCode = (col.farmer ? col.farmer.farmer_code : '—');
            const farmerVillage = (col.farmer ? (col.farmer.village || 'Main Center') : 'Main Center');
            const centerName = (col.collection_center && col.collection_center.name) ? col.collection_center.name : farmerVillage;
            const photoUrl = (col.farmer && col.farmer.photo_url) ? col.farmer.photo_url : `https://ui-avatars.com/api/?name=${encodeURIComponent(farmerName)}&background=0d9488&color=fff&bold=true`;

            const shiftBadge = col.shift === 'morning'
                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">☀️ Morning</span>`
                : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">🌙 Evening</span>`;

            let milkTypeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">🐄 Cow (CM)</span>`;
            if (col.milk_type === 'buffalo') {
                milkTypeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">🐃 Buffalo</span>`;
            } else if (col.milk_type && col.milk_type !== 'cow') {
                milkTypeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">🥛 Mixed</span>`;
            }

            const safeDataJson = JSON.stringify({
                id: col.id,
                receipt_number: col.receipt_number,
                farmer_id: col.farmer_id,
                farmer_code: farmerCode,
                farmer_name: farmerName,
                farmer_phone: col.farmer ? (col.farmer.phone || '') : '',
                farmer_village: farmerVillage,
                collection_center_id: col.collection_center_id,
                collection_date: col.collection_date ? col.collection_date.substring(0, 10) : '{{ date("Y-m-d") }}',
                shift: col.shift,
                milk_type: col.milk_type,
                quantity_liters: parseFloat(col.quantity_liters),
                fat: parseFloat(col.fat),
                snf: parseFloat(col.snf),
                clr: parseFloat(col.clr || 28),
                bonus: parseFloat(col.bonus || 0),
                deduction: parseFloat(col.deduction || 0),
                applied_rate: parseFloat(col.applied_rate),
                notes: col.notes || ''
            }).replace(/"/g, '&quot;');

            const rowCount = document.querySelectorAll('#collections-table-body tr:not(#no-collections-row)').length + 1;

            return `
                <td class="py-1.5 px-2.5 text-center text-slate-500 font-medium">${rowCount}</td>
                <td class="py-1.5 px-2.5 font-mono font-bold text-slate-900">
                    <span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-800 text-[11px] font-mono border border-slate-200">${farmerCode}</span>
                </td>
                <td class="py-1.5 px-2.5">
                    <div class="flex items-center gap-2">
                        <img src="${photoUrl}" alt="${farmerName}" class="w-5 h-5 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                        <div class="truncate">
                            <a href="/farmers/${col.farmer_id}" class="font-bold text-slate-900 hover:text-emerald-700 transition">${farmerName}</a>
                        </div>
                    </div>
                </td>
                <td class="py-1.5 px-2.5 whitespace-nowrap">${shiftBadge}</td>
                <td class="py-1.5 px-2.5 whitespace-nowrap">${milkTypeBadge}</td>
                <td class="py-1.5 px-2.5 text-right font-bold text-slate-900 font-mono">${parseFloat(col.quantity_liters).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">${parseFloat(col.fat).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">${parseFloat(col.clr || 0).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-center font-mono text-slate-700">${parseFloat(col.snf).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-right font-mono font-semibold text-slate-800">${parseFloat(col.applied_rate).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-right font-mono font-bold text-slate-900">${parseFloat(col.net_amount).toFixed(2)}</td>
                <td class="py-1.5 px-2.5 text-center pr-3">
                    <div class="flex items-center justify-center gap-1">
                        <a href="/collections/slip/${col.id}" target="_blank" title="View / Print Slip" class="w-6 h-6 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        </a>
                        <button type="button" 
                                onclick='Alpine.$data(document.querySelector("[x-data]")).startEditCollection(${safeDataJson})'
                                title="Edit Collection"
                                class="w-6 h-6 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" 
                                onclick='Alpine.$data(document.querySelector("[x-data]")).deleteCollection(${col.id}, "${col.receipt_number}")'
                                title="Delete Collection" 
                                class="w-6 h-6 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>
            `;
        },

        recalculateSummaryMetrics() {
            let totalLiters = 0;
            let totalAmount = 0;
            let totalFat = 0;
            let totalSnf = 0;
            let count = 0;

            const rows = document.querySelectorAll('#collections-table-body tr:not(#no-collections-row)');
            rows.forEach(r => {
                const cells = r.querySelectorAll('td');
                if (cells.length >= 11) {
                    const qty = parseFloat(cells[5].innerText.trim()) || 0;
                    const fat = parseFloat(cells[6].innerText.trim()) || 0;
                    const snf = parseFloat(cells[8].innerText.trim()) || 0;
                    const net = parseFloat(cells[10].innerText.replace(/,/g, '').trim()) || 0;

                    totalLiters += qty;
                    totalAmount += net;
                    totalFat += fat;
                    totalSnf += snf;
                    count++;
                }
            });

            if (count > 0) {
                this.metrics.totalLiters = totalLiters.toFixed(1);
                this.metrics.totalAmount = totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                this.metrics.avgFat = (totalFat / count).toFixed(1);
                this.metrics.avgSnf = (totalSnf / count).toFixed(1);
            }
        }
    };
}
</script>
@endpush
@endsection
