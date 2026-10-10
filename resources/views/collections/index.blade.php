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
<div class="space-y-2.5 w-full max-w-full" x-data="milkCollectionManager()" x-init="init()">

    <!-- Ultra-Slim KPI Metrics Bar (~32px) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400">Procured</span>
            <span class="text-sm font-black text-slate-900 leading-none">
                <span x-text="metrics.totalLiters">{{ number_format($todayTotalLiters, 1) }}</span> <span class="text-[10px] text-slate-400 font-semibold">L</span>
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400">Net Payout</span>
            <span class="text-sm font-black text-emerald-600 leading-none">
                ₹ <span x-text="metrics.totalAmount">{{ number_format($todayTotalAmount, 2) }}</span>
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400">Avg FAT</span>
            <span class="text-sm font-black text-slate-800 leading-none">
                <span x-text="metrics.avgFat">{{ number_format($avgFat, 1) }}</span>%
            </span>
        </div>
        <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <span class="text-[10px] uppercase font-bold text-slate-400">Avg SNF</span>
            <span class="text-sm font-black text-slate-800 leading-none">
                <span x-text="metrics.avgSnf">{{ number_format($avgSnf, 1) }}</span>%
            </span>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 1-ROW ULTRA COMPACT MILK INTAKE FORM (NO POPUP, ZERO GAP)      -->
    <!-- ============================================================== -->
    <div id="collection-entry-section" 
         class="bg-white rounded-xl border shadow-xs overflow-visible transition-all duration-200 w-full max-w-full"
         :class="isEditMode ? 'border-amber-400 ring-2 ring-amber-400/20' : 'border-slate-200/80'">

        <!-- Line 1: Quick Context Bar (Date, Shift, Milk Type, Center, Notes Toggle) -->
        <div class="px-3 py-1.5 bg-slate-50/80 border-b border-slate-200/70 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Receipt Badge -->
                <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold shrink-0"
                      :class="isEditMode ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                      x-text="form.receipt_number || '{{ $nextReceipt }}'"></span>

                <!-- Date -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-500">Date:</span>
                    <input type="date" 
                           x-model="form.collection_date" 
                           required
                           class="text-xs font-semibold px-2 py-0.5 border border-slate-300 rounded-md bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Shift Pills -->
                <div class="flex items-center gap-1 border-l border-slate-200 pl-2">
                    <button type="button" 
                            @click="form.shift = 'morning'; triggerRateCalculation();"
                            :class="form.shift === 'morning' ? 'bg-amber-400 text-slate-900 font-bold shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                            class="px-2 py-0.5 text-xs rounded-md transition flex items-center gap-1">
                        <span>☀️ Morning</span>
                    </button>
                    <button type="button" 
                            @click="form.shift = 'evening'; triggerRateCalculation();"
                            :class="form.shift === 'evening' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                            class="px-2 py-0.5 text-xs rounded-md transition flex items-center gap-1">
                        <span>🌙 Evening</span>
                    </button>
                </div>

                <!-- Milk Type Pills -->
                <div class="flex items-center gap-1 border-l border-slate-200 pl-2">
                    <span class="text-[11px] font-semibold text-slate-500 mr-0.5">Milk:</span>
                    <button type="button" 
                            @click="form.milk_type = 'cow'; triggerRateCalculation();"
                            :class="form.milk_type === 'cow' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                            class="px-2 py-0.5 text-xs rounded-md transition flex items-center gap-1">
                        <span>🐄 Cow</span>
                    </button>
                    <button type="button" 
                            @click="form.milk_type = 'buffalo'; triggerRateCalculation();"
                            :class="form.milk_type === 'buffalo' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                            class="px-2 py-0.5 text-xs rounded-md transition flex items-center gap-1">
                        <span>🐃 Buffalo</span>
                    </button>
                </div>
            </div>

            <!-- Right Controls: Center + Notes toggle -->
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 w-36 sm:w-44 relative" id="center_select_col">
                    <span class="text-[11px] font-semibold text-slate-500 shrink-0">Center:</span>
                    <select id="collection_center_select" x-model="form.collection_center_id" class="text-xs border border-slate-300 rounded-md bg-white w-full py-1 px-1.5 font-medium text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        @foreach($centers as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes / Remarks toggle -->
                <button type="button" 
                        @click="showAdvanced = !showAdvanced" 
                        :class="showAdvanced ? 'bg-slate-200 text-slate-800' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100'"
                        class="px-2 py-0.5 text-[11px] font-medium rounded-md transition flex items-center gap-1">
                    <i data-lucide="sliders" class="w-3 h-3"></i>
                    <span>Notes</span>
                </button>

                <template x-if="isEditMode">
                    <button type="button" @click="cancelEdit()" class="px-2 py-0.5 text-[11px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-md transition">
                        Cancel Edit
                    </button>
                </template>
            </div>
        </div>

        <!-- Line 2: All Inputs in ONE SINGLE RESPONSIVE ROW (Customer Code, Liter, FAT, CLR, SNF, Rate, Net, Actions) -->
        <form @submit.prevent="submitCollection(false)" class="p-2.5 sm:p-3 space-y-1.5">
            <div class="grid grid-cols-12 gap-2 items-start">
                
                <!-- Customer / Farmer Code (Fast Code Input + Searchable Dropdown) -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-3 relative" id="farmer_select_col" @click.away="farmerDropdownOpen = false">
                    <div class="flex items-center justify-between mb-0.5">
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Farmer Code / Name *</label>
                        <span class="text-[9px] text-emerald-600 font-semibold" title="Type numeric code (e.g. 101 or 105) and press Enter">⚡ Fast Code or Search</span>
                    </div>
                    
                    <div class="flex items-center gap-1.5 relative">
                        <!-- Quick Code Input (e.g. 101 or 105) -->
                        <div class="w-16 shrink-0">
                            <input type="text" 
                                   id="quick_farmer_code_input" 
                                   placeholder="Code#" 
                                   x-model="quickFarmerCode" 
                                   @keydown.enter.prevent="lookupFarmerByCode()" 
                                   @blur="lookupFarmerByCode()" 
                                   title="Enter numeric code (e.g. 101 or 105) and press Enter"
                                   class="w-full px-1.5 py-1.5 text-xs font-black text-emerald-800 bg-emerald-50/70 border border-emerald-300 rounded-lg text-center placeholder-emerald-400 focus:bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                        </div>

                        <!-- Searchable Dropdown Input with Live Filter -->
                        <div class="flex-1 min-w-0 relative">
                            <div class="relative">
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
                                       class="w-full pl-2 pr-6 py-1.5 text-xs font-bold text-slate-800 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition">
                                
                                <button type="button" 
                                        @click="farmerDropdownOpen = !farmerDropdownOpen; if (farmerDropdownOpen) document.getElementById('farmer_search_input').focus();"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="farmerDropdownOpen ? 'rotate-180 text-emerald-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
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
                                         class="px-2.5 py-2 cursor-pointer transition flex items-center justify-between text-xs">
                                        <div class="min-w-0 pr-2">
                                            <div class="truncate flex items-center gap-1.5">
                                                <span class="font-mono text-emerald-700 font-extrabold" x-text="farmer.farmer_code || ('FAR-' + farmer.id)"></span>
                                                <span class="font-bold text-slate-900" x-text="farmer.name"></span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate mt-0.5 flex items-center gap-2">
                                                <span x-show="farmer.phone" x-text="farmer.phone"></span>
                                                <span x-show="farmer.village" x-text="'• ' + farmer.village"></span>
                                            </div>
                                        </div>
                                        <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-bold shrink-0" 
                                              :class="farmer.animal_type === 'buffalo' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'"
                                              x-text="farmer.animal_type || 'cow'"></span>
                                    </div>
                                </template>

                                <div x-show="filteredFarmers.length === 0" class="p-3 text-center text-xs text-slate-400">
                                    No farmer matches "<span class="font-semibold text-slate-600" x-text="farmerSearchText"></span>"
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden native input to bind farmer_id -->
                    <input type="hidden" name="farmer_id" :value="form.farmer_id">

                    <!-- Farmer details badge underneath -->
                    <div class="text-[11px] font-bold text-emerald-700 truncate mt-0.5 flex items-center justify-between" x-show="selectedFarmer.name" x-cloak>
                        <span class="truncate">
                            <span class="font-extrabold" x-text="selectedFarmer.code || ('#' + quickFarmerCode)"></span>
                            <span x-text="' • ' + selectedFarmer.name + (selectedFarmer.phone ? ' (' + selectedFarmer.phone + ')' : '')"></span>
                            <span class="text-slate-400 font-normal" x-show="selectedFarmer.village" x-text="' • ' + selectedFarmer.village"></span>
                        </span>
                        <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold shrink-0 ml-1" x-text="selectedFarmer.animal || 'Cow'"></span>
                    </div>
                </div>

                <!-- Liter (Qty) (1.2 cols) -->
                <div class="col-span-4 sm:col-span-3 lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5 uppercase tracking-wider">Liter *</label>
                    <input type="number" 
                           id="collection_quantity_input"
                           step="0.01" 
                           min="0.1"
                           x-model.number="form.quantity_liters" 
                           @input="recalculateTotals()" 
                           required 
                           placeholder="10.0"
                           class="w-full px-2 py-1.5 text-xs font-black text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- FAT % (1 col) -->
                <div class="col-span-4 sm:col-span-3 lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5 uppercase tracking-wider">FAT % *</label>
                    <input type="number" 
                           step="0.1" 
                           min="1" 
                           max="15"
                           x-model.number="form.fat" 
                           @input="onFatOrClrChange()" 
                           required 
                           placeholder="4.0"
                           class="w-full px-2 py-1.5 text-xs font-black text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- CLR (1 col) -->
                <div class="col-span-4 sm:col-span-3 lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5 uppercase tracking-wider">CLR *</label>
                    <input type="number" 
                           step="0.5" 
                           min="10" 
                           max="40"
                           x-model.number="form.clr" 
                           @input="onFatOrClrChange()" 
                           required 
                           placeholder="28"
                           class="w-full px-2 py-1.5 text-xs font-black text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- SNF % (1 col) -->
                <div class="col-span-4 sm:col-span-3 lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5 uppercase tracking-wider">SNF % *</label>
                    <input type="number" 
                           step="0.01" 
                           min="4" 
                           max="15"
                           x-model.number="form.snf" 
                           @input="triggerRateCalculation()" 
                           required 
                           placeholder="8.5"
                           class="w-full px-2 py-1.5 text-xs font-black text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                </div>

                <!-- Rate (₹/L) (1.2 cols) -->
                <div class="col-span-4 sm:col-span-3 lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-700 mb-0.5 uppercase tracking-wider">Rate *</label>
                    <input type="number" 
                           step="0.01" 
                           min="0"
                           x-model.number="form.applied_rate" 
                           @input="recalculateTotals()" 
                           required
                           class="w-full px-2 py-1.5 text-xs font-black text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none transition">
                    <div class="text-[9px] text-slate-400 font-mono truncate mt-0.5" x-text="rateChartFeedback"></div>
                </div>

                <!-- Net Farmer Amount (2 cols) -->
                <div class="col-span-8 sm:col-span-6 lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 mb-0.5 uppercase tracking-wider">Net Amount</label>
                    <div class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-200/90 rounded-lg flex items-center justify-between">
                        <span class="text-[10px] text-emerald-800 font-semibold" x-text="(form.quantity_liters || 0) + 'L'"></span>
                        <span class="text-sm font-black text-emerald-700">₹ <span x-text="netAmountFormatted">0.00</span></span>
                    </div>
                </div>

                <!-- Action Buttons: Reset, Save, Print (1.8 cols) -->
                <div class="col-span-4 sm:col-span-6 lg:col-span-2 flex items-center justify-end gap-1 pt-4 sm:pt-4">
                    <!-- Reset -->
                    <button type="button" 
                            @click="resetForm()" 
                            title="Reset Form"
                            class="p-2 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition shrink-0">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Save Collection -->
                    <button type="button" 
                            @click="submitCollection(false)" 
                            :disabled="isSubmitting"
                            class="flex-1 py-1.5 px-2.5 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 disabled:opacity-50 rounded-lg shadow-2xs transition flex items-center justify-center gap-1">
                        <span x-show="!isSubmitting" class="flex items-center gap-1">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span x-text="isEditMode ? 'Update' : 'Save'"></span>
                        </span>
                        <span x-show="isSubmitting" x-cloak class="flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Saving</span>
                        </span>
                    </button>

                    <!-- Save & Print Slip -->
                    <button type="button" 
                            @click="submitCollection(true)" 
                            :disabled="isSubmitting"
                            title="Save & Print Slip"
                            class="p-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-lg shadow-2xs transition shrink-0">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <!-- Collapsible Optional Notes & Adjustments -->
            <div x-show="showAdvanced" x-cloak class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Bonus (₹)</label>
                    <input type="number" step="0.5" x-model.number="form.bonus" @input="recalculateTotals()" placeholder="0.00"
                           class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Deduction (₹)</label>
                    <input type="number" step="0.5" x-model.number="form.deduction" @input="recalculateTotals()" placeholder="0.00"
                           class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Remarks</label>
                    <input type="text" x-model="form.notes" placeholder="Sample quality note..."
                           class="w-full px-2 py-1 text-xs border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- COLLECTIONS HISTORY TABLE CARD (WITH INTEGRATED INLINE FILTER)  -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden w-full max-w-full">
        
        <!-- Integrated Header with Inline Filter (No separate filter card!) -->
        <div class="px-3.5 py-2 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 bg-slate-50/50">
            <div class="flex items-center gap-2 shrink-0">
                <h3 class="text-xs font-bold text-slate-900">Collections History</h3>
                <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-slate-200/80 text-slate-700 font-bold">{{ $collections->total() }}</span>
            </div>

            <!-- Compact Inline Search & Filters -->
            <form method="GET" action="{{ route('collections.index') }}" class="flex flex-wrap items-center gap-1.5 text-xs">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmer/code..." 
                           class="pl-6 pr-2 py-1 text-xs border border-slate-200 rounded-md w-32 sm:w-40 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                    <i data-lucide="search" class="w-3 h-3 text-slate-400 absolute left-1.5 top-1/2 -translate-y-1/2"></i>
                </div>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" 
                       class="px-2 py-1 text-xs border border-slate-200 rounded-md focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
                <select name="shift" class="px-2 py-1 text-xs border border-slate-200 rounded-md bg-white">
                    <option value="">Shift: All</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
                <select name="milk_type" class="px-2 py-1 text-xs border border-slate-200 rounded-md bg-white">
                    <option value="">Milk: All</option>
                    <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>Cow</option>
                    <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                </select>
                <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-md shadow-2xs transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'shift', 'milk_type', 'center_id']) || request('date') != date('Y-m-d'))
                    <a href="{{ route('collections.index') }}" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs rounded-md transition">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table Container with Horizontal Scroll restricted INSIDE table only -->
        <div class="w-full max-w-full overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-xs text-slate-600" id="collections-table">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-3">Receipt</th>
                        <th class="py-2.5 px-3">Date & Shift</th>
                        <th class="py-2.5 px-3">Farmer / Code</th>
                        <th class="py-2.5 px-3">Type</th>
                        <th class="py-2.5 px-3 text-center">Qty (L)</th>
                        <th class="py-2.5 px-3 text-center">Fat / SNF / CLR</th>
                        <th class="py-2.5 px-3 text-right">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Net Amount</th>
                        <th class="py-2.5 px-3 text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody id="collections-table-body" class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr id="row-col-{{ $col->id }}" class="hover:bg-slate-50/60 transition group">
                            <!-- Receipt -->
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800">
                                {{ $col->receipt_number }}
                            </td>

                            <!-- Date & Shift -->
                            <td class="py-2.5 px-3">
                                <span class="font-medium text-slate-900 block">{{ $col->collection_date ? $col->collection_date->format('d M Y') : '—' }}</span>
                                <span class="text-[10px] capitalize px-1.5 py-0.2 rounded font-bold {{ $col->shift === 'morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $col->shift }}
                                </span>
                            </td>

                            <!-- Farmer -->
                            <td class="py-2.5 px-3">
                                <a href="{{ route('farmers.show', $col->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                    {{ $col->farmer->name ?? 'N/A' }}
                                </a>
                                <span class="block text-[10px] text-slate-400">
                                    {{ $col->farmer->farmer_code ?? '' }} • {{ $col->farmer->village ?? 'Local' }}
                                </span>
                            </td>

                            <!-- Type -->
                            <td class="py-2.5 px-3 capitalize font-medium">
                                <span class="inline-flex items-center gap-1 font-semibold {{ $col->milk_type === 'cow' ? 'text-amber-700' : 'text-slate-800' }}">
                                    {{ $col->milk_type === 'cow' ? '🐄 Cow' : ($col->milk_type === 'buffalo' ? '🐃 Buffalo' : '🥛 Mixed') }}
                                </span>
                            </td>

                            <!-- Quantity -->
                            <td class="py-2.5 px-3 text-center font-extrabold text-slate-900">
                                {{ number_format($col->quantity_liters, 2) }} L
                            </td>

                            <!-- FAT / SNF / CLR -->
                            <td class="py-2.5 px-3 text-center">
                                <span class="font-semibold text-slate-700">{{ number_format($col->fat, 1) }}%</span> / 
                                <span class="font-semibold text-slate-700">{{ number_format($col->snf, 1) }}%</span>
                                @if($col->clr)
                                    <span class="text-[9px] text-slate-400 block font-mono">CLR: {{ $col->clr }}</span>
                                @endif
                            </td>

                            <!-- Rate -->
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-800">
                                ₹ {{ number_format($col->applied_rate, 2) }}
                            </td>

                            <!-- Net Amount -->
                            <td class="py-2.5 px-3 text-right font-extrabold text-sm text-emerald-700">
                                ₹ {{ number_format($col->net_amount, 2) }}
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Print Slip Button -->
                                    <a href="{{ route('collections.slip', $col) }}" target="_blank" title="Print Slip" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="printer" class="w-3 h-3"></i>
                                    </a>

                                    <!-- Edit Collection Button (Loads into top embedded form!) -->
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
                                            class="w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="edit-3" class="w-3 h-3"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            @click="deleteCollection({{ $col->id }}, '{{ $col->receipt_number }}')"
                                            title="Delete" 
                                            class="w-6 h-6 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-collections-row">
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-1.5">
                                        <i data-lucide="milk" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600">No collection entries found</span>
                                    <p class="text-[11px] text-slate-400">Use the quick intake form above to record milk from farmers.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-3 border-t border-slate-100">
                {{ $collections->links() }}
            </div>
        @endif
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

@push('scripts')
<script>
function milkCollectionManager() {
    return {
        isEditMode: false,
        isSubmitting: false,
        showAdvanced: false,
        rateChartFeedback: 'Rate Chart = ₹42.50/L',
        
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
            animal: 'cow'
        },

        form: {
            id: null,
            receipt_number: '{{ $nextReceipt }}',
            farmer_id: '',
            collection_center_id: '{{ $centers->first()->id ?? "" }}',
            collection_date: '{{ $today }}',
            shift: '{{ $currentShift }}',
            milk_type: 'cow',
            quantity_liters: 10.0,
            fat: 4.0,
            clr: 28.0,
            snf: 8.5,
            bonus: 0,
            deduction: 0,
            applied_rate: 42.50,
            notes: ''
        },

        init() {
            const self = this;
            this.$nextTick(() => {
                self.calculateSnfFromClr();
                self.triggerRateCalculation();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        get filteredFarmers() {
            if (!this.farmerSearchText || this.farmerSearchText.trim() === '') {
                return this.allFarmers;
            }
            const q = this.farmerSearchText.toLowerCase().trim();
            return this.allFarmers.filter(f => {
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
            this.quickFarmerCode = numCode;
            this.farmerSearchText = code + ' - ' + farmer.name;
            this.selectedFarmer = {
                id: farmer.id,
                code: code,
                name: farmer.name,
                phone: farmer.phone || '',
                village: farmer.village || '',
                animal: farmer.animal_type || 'cow'
            };
            if (farmer.animal_type && (farmer.animal_type === 'cow' || farmer.animal_type === 'buffalo')) {
                this.form.milk_type = farmer.animal_type;
            }
            this.farmerDropdownOpen = false;
            this.triggerRateCalculation();
            this.$nextTick(() => {
                const qInput = document.getElementById('collection_quantity_input');
                if (qInput) qInput.focus();
            });
        },

        lookupFarmerByCode() {
            var raw = (this.quickFarmerCode || '').toString().trim();
            if (!raw) return;

            var numOnly = raw.replace(/\D/g, '');
            var found = null;

            // 1. Exact match on number or code or id
            found = this.allFarmers.find(f => {
                var code = (f.farmer_code || '').trim();
                var num = code.replace(/\D/g, '') || String(f.id);
                return num === raw || code.toLowerCase() === raw.toLowerCase() || String(f.id) === raw;
            });

            // 2. Partial match if single digit
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

        onFatOrClrChange() {
            this.calculateSnfFromClr();
            this.triggerRateCalculation();
        },

        calculateSnfFromClr() {
            const clr = parseFloat(this.form.clr) || 0;
            const fat = parseFloat(this.form.fat) || 0;
            if (clr > 0 && fat > 0) {
                // Indian Dairy Standard Formula: SNF = (CLR / 4) + (0.21 * FAT) + 0.36
                const calculatedSnf = (clr / 4) + (0.21 * fat) + 0.36;
                this.form.snf = parseFloat(calculatedSnf.toFixed(2));
            }
        },

        recalculateTotals() {
            // Evaluated reactively via netAmountFormatted
        },

        triggerRateCalculation() {
            const fat = parseFloat(this.form.fat) || 4.0;
            const snf = parseFloat(this.form.snf) || 8.5;
            const milkType = this.form.milk_type || 'cow';
            const farmerId = this.form.farmer_id || null;

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
                    milk_type: milkType,
                    farmer_id: farmerId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.rate) {
                    this.form.applied_rate = parseFloat(data.rate);
                    this.rateChartFeedback = (data.chart_name || 'Rate Chart') + ' = ₹' + Number(data.rate).toFixed(2) + '/L';
                }
            })
            .catch(() => {
                let fallbackRate = 40.0;
                if (milkType === 'buffalo') {
                    fallbackRate = (fat * 7.2) + (snf * 4.5);
                } else {
                    fallbackRate = (fat * 6.8) + (snf * 4.1);
                }
                this.form.applied_rate = parseFloat(fallbackRate.toFixed(2));
                this.rateChartFeedback = (milkType.charAt(0).toUpperCase() + milkType.slice(1)) + ' Formula = ₹' + this.form.applied_rate + '/L';
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
                clr: item.clr,
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
            this.form = {
                id: null,
                receipt_number: '{{ $nextReceipt }}',
                farmer_id: '',
                collection_center_id: this.form.collection_center_id || '{{ $centers->first()->id ?? "" }}',
                collection_date: '{{ $today }}',
                shift: '{{ $currentShift }}',
                milk_type: 'cow',
                quantity_liters: 10.0,
                fat: 4.0,
                clr: 28.0,
                snf: 8.5,
                bonus: 0,
                deduction: 0,
                applied_rate: 42.50,
                notes: ''
            };
            this.quickFarmerCode = '';
            this.farmerSearchText = '';
            this.farmerDropdownOpen = false;
            this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: 'cow' };
            this.calculateSnfFromClr();
            this.triggerRateCalculation();
        },

        resetEntryInputsForNext() {
            this.form.id = null;
            this.form.farmer_id = '';
            this.form.quantity_liters = '';
            this.form.notes = '';
            this.form.bonus = 0;
            this.form.deduction = 0;
            this.quickFarmerCode = '';
            this.farmerSearchText = '';
            this.farmerDropdownOpen = false;
            this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: 'cow' };
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
                this.showToast('Please select or enter a Customer / Farmer Code first.', 'error');
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
            if (!this.form.fat || parseFloat(this.form.fat) <= 0) {
                this.showToast('Please enter a valid FAT %.', 'error');
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
                fat: this.form.fat,
                snf: this.form.snf,
                clr: this.form.clr,
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
            tr.className = 'hover:bg-slate-50/60 transition group bg-emerald-50/30';
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
            const farmerCode = (col.farmer ? col.farmer.farmer_code : '');
            const farmerVillage = (col.farmer ? (col.farmer.village || 'Local') : 'Local');
            const dateStr = col.collection_date ? new Date(col.collection_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';
            const shiftBadgeClass = col.shift === 'morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800';
            const milkTypeIcon = col.milk_type === 'cow' ? '🐄 Cow' : (col.milk_type === 'buffalo' ? '🐃 Buffalo' : '🥛 Mixed');
            const milkTypeClass = col.milk_type === 'cow' ? 'text-amber-700' : 'text-slate-800';
            const clrBadge = col.clr ? `<span class="text-[9px] text-slate-400 block font-mono">CLR: ${col.clr}</span>` : '';

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

            return `
                <td class="py-2.5 px-3 font-mono font-bold text-slate-800">${col.receipt_number}</td>
                <td class="py-2.5 px-3">
                    <span class="font-medium text-slate-900 block">${dateStr}</span>
                    <span class="text-[10px] capitalize px-1.5 py-0.2 rounded font-bold ${shiftBadgeClass}">${col.shift}</span>
                </td>
                <td class="py-2.5 px-3">
                    <a href="/farmers/${col.farmer_id}" class="font-bold text-emerald-700 hover:underline">${farmerName}</a>
                    <span class="block text-[10px] text-slate-400">${farmerCode} • ${farmerVillage}</span>
                </td>
                <td class="py-2.5 px-3 capitalize font-medium">
                    <span class="inline-flex items-center gap-1 font-semibold ${milkTypeClass}">${milkTypeIcon}</span>
                </td>
                <td class="py-2.5 px-3 text-center font-extrabold text-slate-900">${parseFloat(col.quantity_liters).toFixed(2)} L</td>
                <td class="py-2.5 px-3 text-center">
                    <span class="font-semibold text-slate-700">${parseFloat(col.fat).toFixed(1)}%</span> / 
                    <span class="font-semibold text-slate-700">${parseFloat(col.snf).toFixed(1)}%</span>
                    ${clrBadge}
                </td>
                <td class="py-2.5 px-3 text-right font-semibold text-slate-800">₹ ${parseFloat(col.applied_rate).toFixed(2)}</td>
                <td class="py-2.5 px-3 text-right font-extrabold text-sm text-emerald-700">₹ ${parseFloat(col.net_amount).toFixed(2)}</td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <a href="/collections/slip/${col.id}" target="_blank" title="Print Slip" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-2xs">
                            <i data-lucide="printer" class="w-3 h-3"></i>
                        </a>
                        <button type="button" 
                                onclick='Alpine.$data(document.querySelector("[x-data]")).startEditCollection(${safeDataJson})'
                                title="Edit Collection"
                                class="w-6 h-6 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition shadow-2xs">
                            <i data-lucide="edit-3" class="w-3 h-3"></i>
                        </button>
                        <button type="button" 
                                onclick='Alpine.$data(document.querySelector("[x-data]")).deleteCollection(${col.id}, "${col.receipt_number}")'
                                title="Delete" 
                                class="w-6 h-6 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition shadow-2xs">
                            <i data-lucide="trash-2" class="w-3 h-3"></i>
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
                if (cells.length >= 8) {
                    const qty = parseFloat(cells[4].innerText.replace('L', '').trim()) || 0;
                    const fatSnfText = cells[5].innerText;
                    const parts = fatSnfText.split('/');
                    const fat = parts[0] ? (parseFloat(parts[0].replace('%', '').trim()) || 0) : 0;
                    const snf = parts[1] ? (parseFloat(parts[1].replace('%', '').trim()) || 0) : 0;
                    const net = parseFloat(cells[7].innerText.replace('₹', '').replace(/,/g, '').trim()) || 0;

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
