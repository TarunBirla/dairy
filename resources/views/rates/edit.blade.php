@extends('layouts.app')

@section('title', 'Edit Rate Chart')
@section('breadcrumb', 'Edit Rate Chart')
@section('header_title', 'Rate Charts / Edit: ' . $chart->name)

@section('content')
<div class="space-y-6">

    <!-- Top Toast Notification matching Reference Image 1 -->
    <div id="toastNotification" class="fixed top-5 right-5 z-[100] max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-rose-200 p-4 flex items-start gap-3 transition-all duration-300 transform hidden">
        <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l-6 6m0-6l6 6" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-xs font-black text-rose-600 leading-tight">Failed</h4>
            <p id="toastMessage" class="text-xs text-slate-700 mt-1 font-medium leading-relaxed"></p>
        </div>
        <button type="button" onclick="hideToast()" class="text-slate-400 hover:text-slate-600 p-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Top Breadcrumb / Title Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Edit Rate Chart: {{ $chart->name }}</h2>
            <p class="text-xs text-slate-500">Modify procurement rate formula, incremental steps, and quality incentives.</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('rates.destroy', $chart) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this rate chart? Assigned farmers will be unlinked.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl transition border border-rose-200">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Delete Chart</span>
                </button>
            </form>
            <a href="{{ route('rates.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition shadow-xs">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Rate Charts</span>
            </a>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <form id="rateChartForm" action="{{ route('rates.update', $chart) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="fat_steps" id="input_fat_steps" value="{{ json_encode($chart->fat_steps ?? []) }}">
        <input type="hidden" name="snf_steps" id="input_snf_steps" value="{{ json_encode($chart->snf_steps ?? []) }}">
        <input type="hidden" name="fat_rules" id="input_fat_rules" value="{{ json_encode($chart->fat_rules ?? []) }}">
        <input type="hidden" name="snf_rules" id="input_snf_rules" value="{{ json_encode($chart->snf_rules ?? []) }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Form Details & Steps (6 Cols) -->
            <div class="lg:col-span-6 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                
                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category :</label>
                    <select name="category" id="field_category" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="collection" {{ $chart->category === 'collection' ? 'selected' : '' }}>Collection</option>
                        <option value="milk_sale" {{ $chart->category === 'milk_sale' ? 'selected' : '' }}>Milk Sale</option>
                        <option value="chilling_center" {{ $chart->category === 'chilling_center' ? 'selected' : '' }}>Chilling Center</option>
                    </select>
                </div>

                <!-- Milk Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type: *</label>
                    <select name="milk_type" id="field_milk_type" onchange="renderMatrixPreview()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="cow" {{ $chart->milk_type === 'cow' ? 'selected' : '' }}>Cow</option>
                        <option value="buffalo" {{ $chart->milk_type === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                        <option value="mixed" {{ $chart->milk_type === 'mixed' ? 'selected' : '' }}>Mix</option>
                    </select>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Name: *</label>
                    <input type="text" name="name" id="field_name" required value="{{ old('name', $chart->name) }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] focus:ring-1 focus:ring-[#388e7e] outline-none font-medium">
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Format: *</label>
                    <select name="format" id="field_format" onchange="handleFormatChange()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="fat_clr" {{ ($chart->format ?? 'fat_clr') === 'fat_clr' ? 'selected' : '' }}>FAT + CLR</option>
                        <option value="fat_snf" {{ ($chart->format ?? '') === 'fat_snf' ? 'selected' : '' }}>FAT + SNF</option>
                        <option value="fat_only" {{ ($chart->format ?? '') === 'fat_only' ? 'selected' : '' }}>FAT Only</option>
                        <option value="clr_only" {{ ($chart->format ?? '') === 'clr_only' ? 'selected' : '' }}>CLR Only</option>
                        <option value="fixed_rate" {{ ($chart->format ?? '') === 'fixed_rate' ? 'selected' : '' }}>Fixed Rate</option>
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Type: *</label>
                    <select name="type" id="field_type" onchange="handleTypeChange()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="rate_per_kg" {{ ($chart->type ?? 'rate_per_kg') === 'rate_per_kg' ? 'selected' : '' }}>Rate per KG</option>
                        <option value="increase_per_point" {{ ($chart->type ?? '') === 'increase_per_point' ? 'selected' : '' }}>Increase per fat/snf points</option>
                        <option value="matrix_slab" {{ ($chart->type ?? '') === 'matrix_slab' ? 'selected' : '' }}>Matrix slab</option>
                        <option value="flat" {{ ($chart->type ?? '') === 'flat' ? 'selected' : '' }}>Fixed Rate</option>
                    </select>
                </div>

                <!-- Dynamic Increment By Selector (CLR increment by / SNF increment by) -->
                <div id="increment_by_wrapper">
                    <label class="block text-xs font-bold text-slate-700 mb-1" id="label_increment_by">CLR increment by :</label>
                    <select id="field_increment_by" onchange="renderMatrixPreview()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="1" selected>1</option>
                        <option value="0.5">0.5</option>
                        <option value="0.2">0.2</option>
                        <option value="0.1">0.1</option>
                    </select>
                </div>

                <!-- Starting amount (Shown when Type = increase_per_point or Format = fixed_rate) -->
                <div id="starting_amount_wrapper" class="{{ in_array($chart->format ?? '', ['fixed_rate']) || in_array($chart->type ?? '', ['increase_per_point', 'flat']) ? '' : 'hidden' }}">
                    <label class="block text-xs font-bold text-slate-700 mb-1" id="label_starting_amount">
                        {{ ($chart->format ?? '') === 'fixed_rate' || ($chart->type ?? '') === 'flat' ? 'Fixed Rate (₹/L) : *' : 'Starting amount: *' }}
                    </label>
                    <input type="number" step="0.01" name="starting_amount" id="field_starting_amount" oninput="renderMatrixPreview()" value="{{ old('starting_amount', $chart->starting_amount ?? $chart->fixed_rate ?? $chart->base_rate) }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold text-slate-800">
                </div>

                <!-- 2x2 Steps Grid matching Reference Screenshots -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2" id="steps_cards_container">
                    
                    <!-- 1. FAT steps Card -->
                    <div id="fat_steps_card" class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">FAT steps</span>
                            <button type="button" onclick="openStepModal('fat')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 2. CLR / SNF steps Card -->
                    <div id="secondary_steps_card" class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800" id="card_secondary_steps_title">CLR steps</span>
                            <button type="button" onclick="openStepModal('secondary')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 3. Bonus / Penalty FAT Card -->
                    <div id="fat_rules_card" class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">Bonus / Penalty FAT</span>
                            <button type="button" onclick="openRuleModal('fat')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 4. Bonus / Penalty CLR / SNF Card -->
                    <div id="secondary_rules_card" class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800" id="card_secondary_rules_title">Bonus / Penalty CLR</span>
                            <button type="button" onclick="openRuleModal('secondary')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                </div>

                <!-- Default Rule Checkbox -->
                <div class="pt-2 flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="is_default" value="1" {{ $chart->is_default ? 'checked' : '' }} class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                    <label for="is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Set as Default Rate Chart for this Milk Type & Category
                    </label>
                </div>

                <!-- Footer Action Buttons: Reset & Save matching Reference Image 2 -->
                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <button type="button" onclick="resetRateChartForm()" class="px-6 py-2.5 bg-[#cbd5e1] hover:bg-slate-400 text-slate-800 text-xs font-bold rounded-xl transition shadow-xs">
                        Reset
                    </button>
                    <button type="submit" class="px-7 py-2.5 bg-[#388e7e] hover:bg-[#2e7568] text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Save
                    </button>
                </div>

            </div>

            <!-- Right Column: Live Rate Matrix & Richmond Quality Test (6 Cols) -->
            <div class="lg:col-span-6 space-y-4">
                
                <!-- 1. Live Matrix Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col min-h-[500px]">
                    
                    <!-- Header Bar matching screenshot: e.g. "fat_clr" -->
                    <div class="p-3.5 bg-slate-100/90 border-b border-slate-200/80 flex items-center justify-center relative">
                        <h3 class="text-xs font-black tracking-wider text-slate-800 uppercase" id="matrix_format_title">
                            {{ $chart->format ?? 'fat_clr' }}
                        </h3>
                    </div>

                    <!-- Inner Table Container -->
                    <div id="matrixPreviewContainer" class="flex-1 p-3 overflow-auto flex flex-col justify-start">
                        
                        <!-- Fixed Rate Flat Card View (When format is fixed_rate) -->
                        <div id="fixedRateCard" class="hidden p-8 text-center my-auto space-y-3">
                            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-[#388e7e] flex items-center justify-center text-2xl font-black">
                                ₹
                            </div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Fixed Rate Value</h4>
                            <div id="fixedRateValue" class="text-3xl font-extrabold text-slate-900 font-mono">₹ 0.00</div>
                            <p class="text-xs text-slate-400">All procurement will be credited at this flat rate per litre.</p>
                        </div>

                        <!-- Empty State Before Any Steps -->
                        <div id="matrixEmptyState" class="text-center py-20 text-slate-400 my-auto">
                            <i data-lucide="table" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                            <p class="text-xs font-medium" id="matrix_empty_message">Add FAT & CLR steps to generate live rate chart</p>
                        </div>

                        <!-- Live Table Wrapper -->
                        <div id="matrixTableWrapper" class="w-full hidden">
                            <div class="overflow-auto max-h-[500px] border border-slate-200 rounded-xl shadow-2xs">
                                <table class="w-full text-center border-collapse text-[11px]" id="matrixTable">
                                    <thead id="matrixTableHead">
                                        <!-- Dynamically generated -->
                                    </thead>
                                    <tbody id="matrixTableBody" class="font-medium text-slate-700">
                                        <!-- Dynamically generated -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="pt-2 px-1 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                <span>Rates in ₹ per litre</span>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Bonus</span>
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Penalty</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 2. Richmond SNF & Rate Calculator Helper Card -->
                <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3" id="richmondCardWrapper">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-emerald-50 text-[#388e7e] flex items-center justify-center font-bold text-xs">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800">Richmond Quality Test & Rate Preview</h4>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Auto-highlights cell</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">CLR (LR) :</label>
                            <input type="number" step="0.1" id="richmond_clr" value="28" oninput="calcRichmondSNF()" placeholder="28.0" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">FAT % :</label>
                            <input type="number" step="0.1" id="richmond_fat" value="4.0" oninput="calcRichmondSNF()" placeholder="4.0" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Temp (°C) :</label>
                            <input type="number" step="0.5" id="richmond_temp" value="27" oninput="calcRichmondSNF()" placeholder="27" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 pt-1">
                        <div class="p-2.5 bg-blue-50/70 border border-blue-100 rounded-2xl flex flex-col justify-between">
                            <span class="text-[10px] font-semibold text-blue-700 uppercase tracking-wider">Calculated SNF</span>
                            <span id="richmond_snf_result" class="text-base font-extrabold text-blue-900 font-mono mt-0.5">8.44%</span>
                        </div>
                        <div class="p-2.5 bg-emerald-50/70 border border-emerald-100 rounded-2xl flex flex-col justify-between">
                            <span class="text-[10px] font-semibold text-emerald-700 uppercase tracking-wider">Estimated Rate</span>
                            <span id="richmond_rate_result" class="text-base font-extrabold text-emerald-900 font-mono mt-0.5">₹42.50 / L</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

<!-- ================= MODAL: FAT / CLR / SNF STEPS (REFERENCE SCREENSHOT 1, 3, 4) ================= -->
<div id="stepModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Mint / Teal Header matching screenshot 1, 3, 4: #98d8c8 -->
        <div class="p-4 bg-[#98d8c8] text-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900" id="stepModalTitle">FAT steps</h3>
            <button type="button" onclick="closeStepModal()" class="text-slate-700 hover:text-slate-950 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <input type="hidden" id="step_edit_index" value="-1">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="step_from_label">FAT From :</label>
                <input type="number" step="0.1" id="step_input_from" placeholder="e.g. 2.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="step_to_label">FAT To :</label>
                <input type="number" step="0.1" id="step_input_to" placeholder="e.g. 3.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Step Amount :</label>
                <input type="number" step="0.1" id="step_input_amount" placeholder="e.g. 100" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <label class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer pt-1">
                <input type="checkbox" id="step_add_another" class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                <span>Save And Add Another</span>
            </label>

            <!-- Buttons matching Screenshot: Cancel (grey) and Apply (#388e7e) -->
            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeStepModal()" class="px-5 py-2 bg-[#e2e8f0] hover:bg-[#cbd5e1] text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </button>
                <button type="button" onclick="applyStepModal()" class="px-6 py-2 bg-[#388e7e] hover:bg-[#2d7366] text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: BONUS / PENALTY ================= -->
<div id="ruleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Mint / Teal Header matching screenshot -->
        <div class="p-4 bg-[#98d8c8] text-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900" id="ruleModalTitle">Bonus / Penalty FAT</h3>
            <button type="button" onclick="closeRuleModal()" class="text-slate-700 hover:text-slate-950 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <input type="hidden" id="rule_edit_index" value="-1">

            <!-- Type Radio buttons -->
            <div class="flex items-center gap-4">
                <label class="text-xs font-bold text-slate-700">Type :</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="bonus" checked class="text-[#388e7e] focus:ring-[#388e7e]">
                        <span>Bonus</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="penalty" class="text-rose-600 focus:ring-rose-500">
                        <span>Penalty</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="rule_from_label">FAT From :</label>
                <input type="number" step="0.1" id="rule_input_from" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="rule_to_label">FAT To :</label>
                <input type="number" step="0.1" id="rule_input_to" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Amount :</label>
                <input type="number" step="0.1" id="rule_input_amount" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-[#388e7e] outline-none font-bold">
            </div>

            <label class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer pt-1">
                <input type="checkbox" id="rule_add_another" class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                <span>Save And Add Another</span>
            </label>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeRuleModal()" class="px-5 py-2 bg-[#e2e8f0] hover:bg-[#cbd5e1] text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </button>
                <button type="button" onclick="applyRuleModal()" class="px-6 py-2 bg-[#388e7e] hover:bg-[#2d7366] text-white text-xs font-bold rounded-xl transition shadow-xs">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize state with existing chart data
    let fatSteps = @json($chart->fat_steps ?? []);
    let snfSteps = @json($chart->snf_steps ?? []);
    let fatRules = @json($chart->fat_rules ?? []);
    let snfRules = @json($chart->snf_rules ?? []);

    if (!Array.isArray(fatSteps)) fatSteps = [];
    if (!Array.isArray(snfSteps)) snfSteps = [];
    if (!Array.isArray(fatRules)) fatRules = [];
    if (!Array.isArray(snfRules)) snfRules = [];

    // Normalize legacy steps
    fatSteps = fatSteps.map(s => ({
        from: parseFloat(s.from !== undefined ? s.from : s.step),
        to: parseFloat(s.to !== undefined ? s.to : (s.step !== undefined ? s.step + 1 : 4.0)),
        amount: parseFloat(s.amount || 0),
        step: parseFloat(s.step !== undefined ? s.step : s.from)
    }));

    snfSteps = snfSteps.map(s => ({
        from: parseFloat(s.from !== undefined ? s.from : s.step),
        to: parseFloat(s.to !== undefined ? s.to : (s.step !== undefined ? s.step + 1 : 30.0)),
        amount: parseFloat(s.amount || 0),
        step: parseFloat(s.step !== undefined ? s.step : s.from)
    }));

    let currentStepTarget = 'fat';
    let currentRuleTarget = 'fat';
    let toastTimeout = null;

    // Toast function matching Screenshot 1
    function showFailedToast(message) {
        const toast = document.getElementById('toastNotification');
        const msgEl = document.getElementById('toastMessage');
        if (!toast || !msgEl) {
            alert(message);
            return;
        }
        msgEl.innerText = message;
        toast.classList.remove('hidden');
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            hideToast();
        }, 4500);
    }

    function hideToast() {
        const toast = document.getElementById('toastNotification');
        if (toast) toast.classList.add('hidden');
    }

    function getSecondaryName() {
        const fmt = document.getElementById('field_format')?.value || 'fat_clr';
        return fmt === 'fat_snf' ? 'SNF' : 'CLR';
    }

    function handleFormatChange() {
        const fmt = document.getElementById('field_format').value;
        const matrixTitle = document.getElementById('matrix_format_title');
        if (matrixTitle) {
            if (fmt === 'fat_only') matrixTitle.innerText = 'fat';
            else if (fmt === 'clr_only') matrixTitle.innerText = 'clr';
            else matrixTitle.innerText = fmt;
        }

        const fatStepsCard = document.getElementById('fat_steps_card');
        const secStepsCard = document.getElementById('secondary_steps_card');
        const fatRulesCard = document.getElementById('fat_rules_card');
        const secRulesCard = document.getElementById('secondary_rules_card');
        const incWrapper = document.getElementById('increment_by_wrapper');
        const incLabel = document.getElementById('label_increment_by');
        const incSelect = document.getElementById('field_increment_by');
        const startAmtWrap = document.getElementById('starting_amount_wrapper');
        const stepsCardsContainer = document.getElementById('steps_cards_container');

        const secTitle = getSecondaryName();
        document.getElementById('card_secondary_steps_title').innerText = `${secTitle} steps`;
        document.getElementById('card_secondary_rules_title').innerText = `Bonus / Penalty ${secTitle}`;

        if (fmt === 'fixed_rate') {
            stepsCardsContainer.classList.add('hidden');
            if (incWrapper) incWrapper.classList.add('hidden');
            if (startAmtWrap) {
                startAmtWrap.classList.remove('hidden');
                document.getElementById('label_starting_amount').innerText = 'Fixed Rate (₹/L) : *';
            }
        } else if (fmt === 'fat_only') {
            stepsCardsContainer.classList.remove('hidden');
            if (fatStepsCard) fatStepsCard.classList.remove('hidden');
            if (fatRulesCard) fatRulesCard.classList.remove('hidden');
            if (secStepsCard) secStepsCard.classList.add('hidden');
            if (secRulesCard) secRulesCard.classList.add('hidden');
            if (incWrapper) incWrapper.classList.add('hidden');
            handleTypeChange();
        } else if (fmt === 'clr_only') {
            stepsCardsContainer.classList.remove('hidden');
            if (fatStepsCard) fatStepsCard.classList.add('hidden');
            if (fatRulesCard) fatRulesCard.classList.add('hidden');
            if (secStepsCard) secStepsCard.classList.remove('hidden');
            if (secRulesCard) secRulesCard.classList.remove('hidden');
            if (incWrapper) incWrapper.classList.remove('hidden');
            if (incLabel) incLabel.innerText = 'CLR increment by :';
            incSelect.value = '1';
            handleTypeChange();
        } else {
            stepsCardsContainer.classList.remove('hidden');
            if (fatStepsCard) fatStepsCard.classList.remove('hidden');
            if (fatRulesCard) fatRulesCard.classList.remove('hidden');
            if (secStepsCard) secStepsCard.classList.remove('hidden');
            if (secRulesCard) secRulesCard.classList.remove('hidden');
            if (incWrapper) incWrapper.classList.remove('hidden');
            if (incLabel) incLabel.innerText = `${secTitle} increment by :`;

            if (fmt === 'fat_clr') {
                incSelect.value = '1';
            } else {
                incSelect.value = '0.2';
            }
            handleTypeChange();
        }

        renderStepsUI('fat');
        renderStepsUI('secondary');
        renderRulesUI('fat');
        renderRulesUI('secondary');
        renderMatrixPreview();
    }

    function handleTypeChange() {
        const type = document.getElementById('field_type').value;
        const fmt = document.getElementById('field_format').value;
        const startAmtWrap = document.getElementById('starting_amount_wrapper');
        const startLabel = document.getElementById('label_starting_amount');

        if (fmt === 'fixed_rate' || type === 'flat') {
            startAmtWrap.classList.remove('hidden');
            startLabel.innerText = 'Fixed Rate (₹/L) : *';
        } else if (type === 'increase_per_point') {
            startAmtWrap.classList.remove('hidden');
            startLabel.innerText = 'Starting amount: *';
        } else {
            startAmtWrap.classList.add('hidden');
        }
        renderMatrixPreview();
    }

    function calcRate(fat, secondary) {
        const format = document.getElementById('field_format')?.value || 'fat_clr';
        const type = document.getElementById('field_type')?.value || 'rate_per_kg';
        const startAmt = parseFloat(document.getElementById('field_starting_amount')?.value) || 0;

        if (format === 'fixed_rate' || type === 'flat') {
            return Math.max(0, startAmt);
        }

        let rate = 0;

        if (type === 'rate_per_kg') {
            let fatRate = 0;
            if (format !== 'clr_only' && fatSteps.length > 0) {
                for (let s of fatSteps) {
                    const fFrom = parseFloat(s.from ?? s.step ?? 0);
                    const fTo = parseFloat(s.to ?? 999);
                    if (fat >= fFrom && fat <= fTo) {
                        fatRate = (parseFloat(s.amount) || 0) / 100;
                        break;
                    }
                }
                if (fatRate === 0 && fatSteps.length > 0) {
                    fatRate = (parseFloat(fatSteps[0].amount) || 0) / 100;
                }
            }

            let secondaryRate = 0;
            if (format !== 'fat_only' && snfSteps.length > 0) {
                for (let s of snfSteps) {
                    const sFrom = parseFloat(s.from ?? s.step ?? 0);
                    const sTo = parseFloat(s.to ?? 999);
                    if (secondary >= sFrom && secondary <= sTo) {
                        secondaryRate = (parseFloat(s.amount) || 0) / 100;
                        break;
                    }
                }
                if (secondaryRate === 0 && snfSteps.length > 0) {
                    secondaryRate = (parseFloat(snfSteps[0].amount) || 0) / 100;
                }
            }

            if (format === 'clr_only') {
                rate = secondary * secondaryRate;
            } else if (format === 'fat_only') {
                rate = fat * fatRate;
            } else {
                rate = (fat * fatRate) + (secondary * secondaryRate);
            }
        } else {
            function calcAxisAddition(val, steps) {
                if (!steps || steps.length === 0) return 0;
                const sorted = [...steps].sort((a, b) => (a.from ?? a.step) - (b.from ?? b.step));
                const baseVal = sorted[0].from ?? sorted[0].step;
                const valT = Math.round(val * 10);
                const baseT = Math.round(baseVal * 10);
                if (valT < baseT) return 0;

                let add = 0;
                for (let i = 0; i < sorted.length; i++) {
                    const curT = Math.round((sorted[i].from ?? sorted[i].step) * 10);
                    const nextT = (i + 1 < sorted.length) ? Math.round((sorted[i + 1].from ?? sorted[i + 1].step) * 10) : Infinity;
                    if (valT > curT) {
                        const slabUpper = Math.min(valT, nextT);
                        const pts = slabUpper - curT;
                        if (pts > 0) add += pts * (sorted[i].amount / 0.1);
                    }
                }
                return add;
            }

            let fatAdd = (format === 'clr_only') ? 0 : calcAxisAddition(fat, fatSteps);
            let secAdd = (format === 'fat_only') ? 0 : calcAxisAddition(secondary, snfSteps);
            rate = startAmt + fatAdd + secAdd;
        }

        if (format !== 'clr_only') {
            fatRules.forEach(r => {
                if (fat >= r.from && fat <= r.to) {
                    rate += (r.type === 'bonus' ? r.amount : -r.amount);
                }
            });
        }

        if (format !== 'fat_only') {
            snfRules.forEach(r => {
                if (secondary >= r.from && secondary <= r.to) {
                    rate += (r.type === 'bonus' ? r.amount : -r.amount);
                }
            });
        }

        return Math.max(0, Math.round(rate * 100) / 100);
    }

    function openStepModal(target, editIdx = -1) {
        currentStepTarget = target;
        const isFat = target === 'fat';
        const secTitle = getSecondaryName();
        const titleText = isFat ? 'FAT steps' : `${secTitle} steps`;
        document.getElementById('stepModalTitle').innerText = titleText;
        document.getElementById('step_from_label').innerText = `${isFat ? 'FAT' : secTitle} From :`;
        document.getElementById('step_to_label').innerText = `${isFat ? 'FAT' : secTitle} To :`;
        document.getElementById('step_edit_index').value = editIdx;

        const list = isFat ? fatSteps : snfSteps;
        const fromInp = document.getElementById('step_input_from');
        const toInp = document.getElementById('step_input_to');
        const amtInp = document.getElementById('step_input_amount');

        if (editIdx >= 0 && list[editIdx]) {
            const item = list[editIdx];
            fromInp.value = item.from;
            toInp.value = item.to;
            amtInp.value = item.amount;
            if (editIdx > 0) {
                fromInp.readOnly = true;
                fromInp.classList.add('bg-slate-200', 'text-slate-600', 'cursor-not-allowed');
            } else {
                fromInp.readOnly = false;
                fromInp.classList.remove('bg-slate-200', 'text-slate-600', 'cursor-not-allowed');
            }
        } else {
            if (list.length > 0) {
                const last = list[list.length - 1];
                let nextFrom;
                if (isFat || secTitle === 'SNF') {
                    nextFrom = (Math.round((last.to + 0.1) * 10) / 10).toFixed(1);
                } else {
                    nextFrom = Math.round(last.to + 1);
                }
                fromInp.value = nextFrom;
                fromInp.readOnly = true;
                fromInp.classList.add('bg-slate-200', 'text-slate-600', 'cursor-not-allowed');
                toInp.value = '0.0';
                amtInp.value = '0.0';
            } else {
                fromInp.value = isFat ? '2.0' : (secTitle === 'CLR' ? '20.0' : '8.0');
                fromInp.readOnly = false;
                fromInp.classList.remove('bg-slate-200', 'text-slate-600', 'cursor-not-allowed');
                toInp.value = isFat ? '3.0' : (secTitle === 'CLR' ? '30.0' : '9.5');
                amtInp.value = isFat ? '100' : (secTitle === 'CLR' ? '10' : '50');
            }
        }

        document.getElementById('step_add_another').checked = false;
        document.getElementById('stepModal').classList.remove('hidden');
        toInp.focus();
    }

    function closeStepModal() {
        document.getElementById('stepModal').classList.add('hidden');
    }

    function applyStepModal() {
        const fromVal = parseFloat(document.getElementById('step_input_from').value);
        const toVal = parseFloat(document.getElementById('step_input_to').value);
        const amt = parseFloat(document.getElementById('step_input_amount').value);
        const editIdx = parseInt(document.getElementById('step_edit_index').value, 10);
        const isFat = currentStepTarget === 'fat';
        const secTitle = getSecondaryName();

        if (isNaN(fromVal) || isNaN(toVal) || isNaN(amt)) {
            showFailedToast('Please enter valid numeric values for From, To and Amount.');
            return;
        }

        if (isFat) {
            if (fromVal < 2.0 || fromVal > 15.0 || toVal < 2.0 || toVal > 15.0) {
                showFailedToast('Step must be between 2.0 to 15.0');
                return;
            }
        } else if (secTitle === 'CLR') {
            if (fromVal < 20.0 || fromVal > 32.0 || toVal < 20.0 || toVal > 32.0) {
                showFailedToast('Step must be between 20.0 to 32.0');
                return;
            }
        } else {
            if (fromVal < 5.0 || fromVal > 15.0 || toVal < 5.0 || toVal > 15.0) {
                showFailedToast('Step must be between 5.0 to 15.0');
                return;
            }
        }

        if (fromVal > toVal) {
            showFailedToast('Step To must be greater than or equal to Step From');
            return;
        }

        if (amt < 0) {
            showFailedToast('Step Amount must be positive');
            return;
        }

        const list = isFat ? fatSteps : snfSteps;
        const newObj = { from: fromVal, to: toVal, amount: amt, step: fromVal };

        if (editIdx >= 0 && editIdx < list.length) {
            list[editIdx] = newObj;
        } else {
            list.push(newObj);
        }

        renderStepsUI(currentStepTarget);
        renderMatrixPreview();

        if (document.getElementById('step_add_another').checked) {
            document.getElementById('step_edit_index').value = -1;
            let nextFrom;
            if (isFat || secTitle === 'SNF') {
                nextFrom = (Math.round((toVal + 0.1) * 10) / 10).toFixed(1);
            } else {
                nextFrom = Math.round(toVal + 1);
            }
            const fromInp = document.getElementById('step_input_from');
            fromInp.value = nextFrom;
            fromInp.readOnly = true;
            fromInp.classList.add('bg-slate-200', 'text-slate-600', 'cursor-not-allowed');
            document.getElementById('step_input_to').value = '0.0';
            document.getElementById('step_input_amount').value = '0.0';
            document.getElementById('step_input_to').focus();
        } else {
            closeStepModal();
        }
    }

    function deleteStep(target, index) {
        if (target === 'fat') {
            fatSteps.splice(index, 1);
        } else {
            snfSteps.splice(index, 1);
        }
        renderStepsUI(target);
        renderMatrixPreview();
    }

    function renderStepsUI(target) {
        const isFat = target === 'fat';
        const list = isFat ? fatSteps : snfSteps;
        const listWrap = document.getElementById(isFat ? 'list_fat_steps' : 'list_snf_steps');
        const hiddenInp = document.getElementById(isFat ? 'input_fat_steps' : 'input_snf_steps');

        hiddenInp.value = JSON.stringify(list);
        listWrap.innerHTML = '';

        if (list.length === 0) {
            listWrap.innerHTML = `<div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>`;
            return;
        }

        list.forEach((item, idx) => {
            const isLast = (idx === list.length - 1);
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-2.5 bg-white border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 shadow-2xs hover:border-slate-300 transition';
            row.innerHTML = `
                <div class="font-bold text-slate-800 tracking-wide">
                    ${Number(item.from).toFixed(1)} -> ${Number(item.to).toFixed(1)}
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-800">
                        ${Number(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </span>
                    ${isLast ? `
                        <button type="button" onclick="deleteStep('${isFat ? 'fat' : 'secondary'}', ${idx})" title="Delete step" class="text-slate-400 hover:text-rose-600 font-extrabold text-sm transition px-1">
                            ✕
                        </button>
                    ` : '<span class="w-3"></span>'}
                </div>
            `;
            listWrap.appendChild(row);
        });

        if (window.lucide) lucide.createIcons();
    }

    function openRuleModal(target, editIdx = -1) {
        currentRuleTarget = target;
        const secTitle = getSecondaryName();
        const titleText = (target === 'fat') ? 'Bonus / Penalty FAT' : `Bonus / Penalty ${secTitle}`;
        document.getElementById('ruleModalTitle').innerText = titleText;
        document.getElementById('rule_from_label').innerText = `${target === 'fat' ? 'FAT' : secTitle} From :`;
        document.getElementById('rule_to_label').innerText = `${target === 'fat' ? 'FAT' : secTitle} To :`;
        document.getElementById('rule_edit_index').value = editIdx;

        const list = (target === 'fat') ? fatRules : snfRules;
        if (editIdx >= 0 && list[editIdx]) {
            const r = list[editIdx];
            document.querySelector(`input[name="rule_type_radio"][value="${r.type}"]`).checked = true;
            document.getElementById('rule_input_from').value = r.from;
            document.getElementById('rule_input_to').value = r.to;
            document.getElementById('rule_input_amount').value = r.amount;
        } else {
            document.querySelector('input[name="rule_type_radio"][value="bonus"]').checked = true;
            document.getElementById('rule_input_from').value = '';
            document.getElementById('rule_input_to').value = '';
            document.getElementById('rule_input_amount').value = '';
        }

        document.getElementById('rule_add_another').checked = false;
        document.getElementById('ruleModal').classList.remove('hidden');
    }

    function closeRuleModal() {
        document.getElementById('ruleModal').classList.add('hidden');
    }

    function applyRuleModal() {
        const fromVal = parseFloat(document.getElementById('rule_input_from').value);
        const toVal = parseFloat(document.getElementById('rule_input_to').value);
        const amt = parseFloat(document.getElementById('rule_input_amount').value);
        const type = document.querySelector('input[name="rule_type_radio"]:checked').value;
        const editIdx = parseInt(document.getElementById('rule_edit_index').value, 10);

        if (isNaN(fromVal) || isNaN(toVal) || isNaN(amt)) {
            showFailedToast('Please enter valid numeric From, To, and Amount values.');
            return;
        }
        if (fromVal > toVal) {
            showFailedToast('"From" value cannot be greater than "To" value.');
            return;
        }

        const list = (currentRuleTarget === 'fat') ? fatRules : snfRules;
        const newObj = { type: type, from: fromVal, to: toVal, amount: amt };

        if (editIdx >= 0 && editIdx < list.length) {
            list[editIdx] = newObj;
        } else {
            list.push(newObj);
        }

        renderRulesUI(currentRuleTarget);
        renderMatrixPreview();

        if (document.getElementById('rule_add_another').checked) {
            document.getElementById('rule_edit_index').value = -1;
            document.getElementById('rule_input_from').value = '';
            document.getElementById('rule_input_to').value = '';
            document.getElementById('rule_input_amount').value = '';
            document.getElementById('rule_input_from').focus();
        } else {
            closeRuleModal();
        }
    }

    function deleteRule(target, index) {
        if (target === 'fat') {
            fatRules.splice(index, 1);
        } else {
            snfRules.splice(index, 1);
        }
        renderRulesUI(target);
        renderMatrixPreview();
    }

    function renderRulesUI(target) {
        const isFat = target === 'fat';
        const list = isFat ? fatRules : snfRules;
        const listWrap = document.getElementById(isFat ? 'list_fat_rules' : 'list_snf_rules');
        const hiddenInp = document.getElementById(isFat ? 'input_fat_rules' : 'input_snf_rules');

        hiddenInp.value = JSON.stringify(list);
        listWrap.innerHTML = '';

        if (list.length === 0) {
            listWrap.innerHTML = `<div class="text-[11px] text-purple-700/70 italic py-4 text-center empty-placeholder">Click on Add btn To add new step</div>`;
            return;
        }

        list.forEach((item, idx) => {
            const isBonus = item.type === 'bonus';
            const badge = document.createElement('div');
            badge.className = `flex items-center justify-between p-2 rounded-xl text-xs font-semibold shadow-2xs border ${isBonus ? 'bg-emerald-50/70 border-emerald-200/80 text-emerald-900' : 'bg-rose-50/70 border-rose-200/80 text-rose-900'}`;
            badge.innerHTML = `
                <div class="flex items-center gap-1.5 font-bold">
                    <span class="uppercase text-[10px]">${item.type}</span>
                    <span class="opacity-40">|</span>
                    <span>${item.from} - ${item.to}</span>
                    <span class="opacity-40">|</span>
                    <span>${isBonus ? '+' : '-'}${Number(item.amount).toFixed(2)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openRuleModal('${isFat ? 'fat' : 'secondary'}', ${idx})" class="font-bold text-xs underline cursor-pointer text-slate-700 hover:text-slate-900">
                        Edit
                    </button>
                    <button type="button" onclick="deleteRule('${isFat ? 'fat' : 'secondary'}', ${idx})" class="text-rose-500 hover:text-rose-700 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            `;
            listWrap.appendChild(badge);
        });

        if (window.lucide) lucide.createIcons();
    }

    function renderMatrixPreview() {
        const format = document.getElementById('field_format')?.value || 'fat_clr';
        const emptyState = document.getElementById('matrixEmptyState');
        const emptyMsg = document.getElementById('matrix_empty_message');
        const tableWrapper = document.getElementById('matrixTableWrapper');
        const fixedCard = document.getElementById('fixedRateCard');
        const thead = document.getElementById('matrixTableHead');
        const tbody = document.getElementById('matrixTableBody');

        if (!thead || !tbody) return;

        // 1. Fixed Rate Format
        if (format === 'fixed_rate') {
            emptyState.classList.add('hidden');
            tableWrapper.classList.add('hidden');
            fixedCard.classList.remove('hidden');
            const amt = parseFloat(document.getElementById('field_starting_amount')?.value) || 0;
            document.getElementById('fixedRateValue').innerText = '₹ ' + amt.toFixed(2);
            calcRichmondSNF();
            return;
        }

        fixedCard.classList.add('hidden');

        // 2. CLR Only Format (Image 5 Panel 4)
        if (format === 'clr_only') {
            if (snfSteps.length === 0) {
                if (emptyMsg) emptyMsg.innerText = 'Add CLR steps to generate live rate chart';
                emptyState.classList.remove('hidden');
                tableWrapper.classList.add('hidden');
                calcRichmondSNF();
                return;
            }

            emptyState.classList.add('hidden');
            tableWrapper.classList.remove('hidden');

            let clrMin = Math.min(...snfSteps.map(s => parseFloat(s.from ?? s.step ?? 20.0)));
            let clrMax = Math.max(...snfSteps.map(s => parseFloat(s.to ?? 30.0)));
            if (isNaN(clrMin)) clrMin = 20.0;
            if (isNaN(clrMax) || clrMax <= clrMin) clrMax = clrMin + 10.0;

            const inc = parseFloat(document.getElementById('field_increment_by')?.value || '1');
            const incT = Math.round(inc * 10);
            const clrValues = [];
            let curC = Math.round(clrMin * 10);
            const maxCT = Math.round(clrMax * 10);
            while (curC <= maxCT && clrValues.length < 50) {
                clrValues.push(curC / 10);
                curC += incT;
            }

            thead.innerHTML = `
                <tr>
                    <th class="p-2.5 border border-slate-200 bg-slate-100 font-extrabold text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">clr</th>
                    <th class="p-2.5 border border-slate-200 bg-slate-100 font-extrabold text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">RATE</th>
                </tr>
            `;

            let tbodyHtml = '';
            clrValues.forEach(c => {
                const r = calcRate(0, c);
                tbodyHtml += `
                    <tr class="hover:bg-slate-50">
                        <td class="p-2.5 border border-slate-200 text-center font-bold text-slate-800 text-xs">${c.toFixed(1)}</td>
                        <td class="matrix-cell p-2.5 border border-slate-200 text-center font-bold text-slate-800 text-xs hover:bg-emerald-50">${r.toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = tbodyHtml;
            calcRichmondSNF();
            return;
        }

        // 3. Formats requiring FAT (fat_only, fat_snf, fat_clr)
        if (fatSteps.length === 0) {
            if (emptyMsg) emptyMsg.innerText = `Add FAT ${format !== 'fat_only' ? '& ' + getSecondaryName() + ' ' : ''}steps to generate live rate chart`;
            emptyState.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            calcRichmondSNF();
            return;
        }

        emptyState.classList.add('hidden');
        tableWrapper.classList.remove('hidden');

        // Derive FAT rows
        let fatMin = Math.min(...fatSteps.map(s => parseFloat(s.from ?? s.step ?? 2.0)));
        let fatMax = Math.max(...fatSteps.map(s => parseFloat(s.to ?? 4.0)));
        if (isNaN(fatMin)) fatMin = 2.0;
        if (isNaN(fatMax) || fatMax <= fatMin) fatMax = fatMin + 2.0;

        const fatValues = [];
        let curF = Math.round(fatMin * 10);
        const maxFT = Math.round(fatMax * 10);
        while (curF <= maxFT && fatValues.length < 60) {
            fatValues.push(curF / 10);
            curF += 1; // 0.1 increment for FAT rows
        }

        // 3a. FAT Only Format (Image 5 Panel 3)
        if (format === 'fat_only') {
            thead.innerHTML = `
                <tr>
                    <th class="p-2.5 border border-slate-200 bg-slate-100 font-extrabold text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">fat</th>
                    <th class="p-2.5 border border-slate-200 bg-slate-100 font-extrabold text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">RATE</th>
                </tr>
            `;

            let tbodyHtml = '';
            fatValues.forEach(f => {
                const r = calcRate(f, 0);
                tbodyHtml += `
                    <tr class="hover:bg-slate-50">
                        <td class="p-2.5 border border-slate-200 text-center font-bold text-slate-800 text-xs">${f.toFixed(1)}</td>
                        <td class="matrix-cell p-2.5 border border-slate-200 text-center font-bold text-slate-800 text-xs hover:bg-emerald-50">${r.toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = tbodyHtml;
            calcRichmondSNF();
            return;
        }

        // 3b. FAT + SNF Format (Image 5 Panel 2) or FAT + CLR Format (Image 5 Panel 1)
        let secValues = [];
        const isFatSnf = format === 'fat_snf';

        if (snfSteps.length > 0) {
            let secMin = Math.min(...snfSteps.map(s => parseFloat(s.from ?? s.step ?? 20.0)));
            let secMax = Math.max(...snfSteps.map(s => parseFloat(s.to ?? 30.0)));
            if (isNaN(secMin)) secMin = isFatSnf ? 8.0 : 20.0;
            if (isNaN(secMax) || secMax <= secMin) secMax = secMin + (isFatSnf ? 2.0 : 10.0);

            const inc = parseFloat(document.getElementById('field_increment_by')?.value || (isFatSnf ? '0.2' : '1'));
            const incT = Math.round(inc * 10);

            let curS = Math.round(secMin * 10);
            const maxST = Math.round(secMax * 10);
            while (curS <= maxST && secValues.length < 30) {
                secValues.push(curS / 10);
                curS += incT;
            }
        } else if (isFatSnf) {
            secValues = [0.0];
        }

        let thHtml = '<tr>';
        thHtml += `<th class="p-2 border border-slate-200 bg-slate-100 font-extrabold text-slate-800 sticky left-0 top-0 z-20 text-[11px]">${format}</th>`;

        if (secValues.length === 0) {
            thHtml += `<th class="p-2 border border-slate-200 bg-slate-50 font-medium text-slate-400 italic text-center sticky top-0 z-10 text-[11px]">Add ${getSecondaryName()} steps to view rates</th>`;
        } else {
            secValues.forEach(s => {
                thHtml += `<th class="p-2 border border-slate-200 bg-slate-50 font-bold text-slate-800 text-center sticky top-0 z-10 text-[11px] min-w-[50px]">${s.toFixed(1)}</th>`;
            });
        }
        thHtml += '</tr>';
        thead.innerHTML = thHtml;

        let tbodyHtml = '';
        fatValues.forEach(f => {
            tbodyHtml += '<tr class="hover:bg-slate-50/70">';
            tbodyHtml += `<td class="p-2 border border-slate-200 bg-slate-50 font-bold text-slate-800 text-center sticky left-0 z-10 text-xs">${f.toFixed(1)}</td>`;

            if (secValues.length === 0) {
                tbodyHtml += `<td class="p-2 border border-slate-200 text-center text-slate-300 text-xs">-</td>`;
            } else {
                secValues.forEach(s => {
                    const r = calcRate(f, s);

                    let badges = '';
                    fatRules.forEach(rule => {
                        if (f >= rule.from && f <= rule.to) {
                            badges += `<span title="FAT: ${rule.type} ${rule.type === 'bonus' ? '+' : '-'}${rule.amount}" class="inline-block w-1.5 h-1.5 rounded-full ${rule.type === 'bonus' ? 'bg-emerald-500' : 'bg-rose-500'}"></span>`;
                        }
                    });
                    snfRules.forEach(rule => {
                        if (s >= rule.from && s <= rule.to) {
                            badges += `<span title="${getSecondaryName()}: ${rule.type} ${rule.type === 'bonus' ? '+' : '-'}${rule.amount}" class="inline-block w-1.5 h-1.5 rounded-full ${rule.type === 'bonus' ? 'bg-emerald-500' : 'bg-rose-500'}"></span>`;
                        }
                    });

                    tbodyHtml += `<td data-fat="${f}" data-sec="${s}" class="matrix-cell p-2 border border-slate-200 text-center font-bold text-slate-800 hover:bg-emerald-50/70 transition cursor-default text-xs relative">
                        <div>${r.toFixed(2)}</div>
                        ${badges ? `<div class="flex items-center justify-center gap-0.5 mt-0.5">${badges}</div>` : ''}
                    </td>`;
                });
            }

            tbodyHtml += '</tr>';
        });
        tbody.innerHTML = tbodyHtml;

        calcRichmondSNF();
    }

    function calcRichmondSNF() {
        const clr = parseFloat(document.getElementById('richmond_clr')?.value) || 0;
        const fat = parseFloat(document.getElementById('richmond_fat')?.value) || 0;
        const temp = parseFloat(document.getElementById('richmond_temp')?.value) || 27;

        const correctedClr = clr + (0.2 * (temp - 27));
        const snf = (correctedClr / 4) + (0.25 * fat) + 0.44;
        const calculatedSnf = Math.max(0, Math.round(snf * 100) / 100);

        const snfResultEl = document.getElementById('richmond_snf_result');
        const rateResultEl = document.getElementById('richmond_rate_result');

        if (snfResultEl) snfResultEl.innerText = `${calculatedSnf.toFixed(2)}%`;

        const fmt = document.getElementById('field_format')?.value || 'fat_clr';
        let secVal = (fmt === 'fat_clr' || fmt === 'clr_only') ? clr : calculatedSnf;
        const estimatedRate = calcRate(fat, secVal);

        if (rateResultEl) rateResultEl.innerText = `₹${estimatedRate.toFixed(2)} / L`;

        document.querySelectorAll('.matrix-cell').forEach(c => {
            c.classList.remove('ring-2', 'ring-[#388e7e]', 'bg-emerald-100', 'font-black');
        });

        const cells = Array.from(document.querySelectorAll('.matrix-cell[data-fat]'));
        if (cells.length === 0) return;

        let closestCell = null;
        let minDiff = Infinity;
        cells.forEach(c => {
            const cellFat = parseFloat(c.getAttribute('data-fat'));
            const cellSec = parseFloat(c.getAttribute('data-sec'));
            const diff = Math.abs(cellFat - fat) * 2 + Math.abs(cellSec - secVal);
            if (diff < minDiff) {
                minDiff = diff;
                closestCell = c;
            }
        });

        if (closestCell) {
            closestCell.classList.add('ring-2', 'ring-[#388e7e]', 'bg-emerald-100', 'font-black');
        }
    }

    function resetRateChartForm() {
        if (confirm('Are you sure you want to reset all fields and steps?')) {
            document.getElementById('rateChartForm').reset();
            fatSteps = [];
            snfSteps = [];
            fatRules = [];
            snfRules = [];
            handleFormatChange();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleFormatChange();
    });
</script>
@endsection
