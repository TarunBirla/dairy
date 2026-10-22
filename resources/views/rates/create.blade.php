@extends('layouts.app')

@section('title', 'Add Rate Chart')
@section('breadcrumb', 'Add Rate Chart')
@section('header_title', 'Rate Charts / Add Rate Chart')

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

    <!-- Main Two-Column Layout matching Reference Image 1 & 2 -->
    <form id="rateChartForm" action="{{ route('rates.store') }}" method="POST">
        @csrf
        <input type="hidden" name="fat_steps" id="input_fat_steps" value="[]">
        <input type="hidden" name="snf_steps" id="input_snf_steps" value="[]">
        <input type="hidden" name="fat_rules" id="input_fat_rules" value="[]">
        <input type="hidden" name="snf_rules" id="input_snf_rules" value="[]">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Form Details & Steps (6 Cols) -->
            <div class="lg:col-span-6 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                
                <!-- Category -->
                <div class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700">Category :</label>
                    <select name="category" id="field_category" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="collection" selected>Collection</option>
                        <option value="milk_sale">Milk Sale</option>
                        <option value="chilling_center">Chilling Center</option>
                    </select>
                </div>

                <!-- Milk Type -->
                <div class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700">Milk Type:</label>
                    <select name="milk_type" id="field_milk_type" onchange="renderMatrixPreview()" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="cow" selected>Cow</option>
                        <option value="buffalo">Buffalo</option>
                        <option value="mixed">Mix</option>
                    </select>
                </div>

                <!-- Name -->
                <div class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700">Name:</label>
                    <input type="text" name="name" id="field_name" required placeholder="Cow Rate Chart" value="{{ old('name', 'Cow Rate Chart') }}" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium text-slate-800">
                </div>

                <!-- Format -->
                <div class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700">Format:</label>
                    <select name="format" id="field_format" onchange="handleFormatChange()" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="fat_clr" selected>FAT + CLR</option>
                        <option value="fat_snf">FAT + SNF</option>
                        <option value="fat_only">FAT Only</option>
                        <option value="clr_only">CLR Only</option>
                        <option value="fixed_rate">Fixed Rate</option>
                    </select>
                </div>

                <!-- Type -->
                <div class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700">Type:</label>
                    <select name="type" id="field_type" onchange="handleTypeChange()" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="rate_per_kg" selected>Rate per KG</option>
                        <option value="increase_per_point">Increase per fat/snf points</option>
                        <option value="matrix_slab">Matrix slab</option>
                        <option value="flat">Fixed Rate</option>
                    </select>
                </div>

                <!-- Dynamic Increment By Selector (CLR increment by / SNF increment by) -->
                <div id="increment_by_wrapper" class="flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700" id="label_increment_by">CLR increment by :</label>
                    <select id="field_increment_by" onchange="renderMatrixPreview()" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-medium">
                        <option value="1" selected>1</option>
                        <option value="0.5">0.5</option>
                        <option value="0.2">0.2</option>
                        <option value="0.1">0.1</option>
                    </select>
                </div>

                <!-- Starting amount (Shown when Type = increase_per_point or Format = fixed_rate) -->
                <div id="starting_amount_wrapper" class="hidden flex items-center justify-between text-xs font-medium">
                    <label class="w-36 font-bold text-slate-700" id="label_starting_amount">Starting amount: *</label>
                    <input type="number" step="0.01" name="starting_amount" id="field_starting_amount" oninput="renderMatrixPreview()" placeholder="0.00" value="35.00" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg bg-white focus:border-[#388e7e] outline-none font-bold text-slate-800">
                </div>

                <!-- 2x2 Steps Grid matching Reference Images 1 & 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2" id="steps_cards_container">
                    
                    <!-- 1. FAT steps Card -->
                    <div id="fat_steps_card" class="p-3 rounded-xl border border-slate-200 bg-white flex flex-col justify-between min-h-[110px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-950">FAT steps</span>
                            <button type="button" onclick="openStepModal('fat')" class="text-xs font-bold text-indigo-700 hover:text-indigo-900 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 2. CLR / SNF steps Card -->
                    <div id="secondary_steps_card" class="p-3 rounded-xl border border-slate-200 bg-white flex flex-col justify-between min-h-[110px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-950" id="card_secondary_steps_title">CLR steps</span>
                            <button type="button" onclick="openStepModal('secondary')" class="text-xs font-bold text-indigo-700 hover:text-indigo-900 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 3. Bonus / Penalty FAT Card -->
                    <div id="fat_rules_card" class="p-3 rounded-xl border border-slate-200 bg-white flex flex-col justify-between min-h-[110px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-950">Bonus / Penalty</span>
                            <button type="button" onclick="openRuleModal('fat')" class="text-xs font-bold text-indigo-700 hover:text-indigo-900 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                    <!-- 4. Bonus / Penalty CLR / SNF Card -->
                    <div id="secondary_rules_card" class="p-3 rounded-xl border border-slate-200 bg-white flex flex-col justify-between min-h-[110px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-indigo-950" id="card_secondary_rules_title">Bonus / Penalty</span>
                            <button type="button" onclick="openRuleModal('secondary')" class="text-xs font-bold text-indigo-700 hover:text-indigo-900 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>
                        </div>
                    </div>

                </div>

                <!-- Default Rule Checkbox -->
                <div class="pt-1 flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="is_default" value="1" class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                    <label for="is_default" class="text-xs font-medium text-slate-700 cursor-pointer">
                        Set as Default Rate Chart for this Milk Type & Category
                    </label>
                </div>

                <!-- Footer Action Buttons: Reset (grey) & Save (teal #388e7e) -->
                <div class="pt-3 flex items-center justify-center gap-6">
                    <button type="button" onclick="resetRateChartForm()" class="px-8 py-2 bg-[#cbd5e1] hover:bg-slate-400 text-slate-800 text-xs font-bold rounded-lg transition">
                        Reset
                    </button>
                    <button type="submit" class="px-9 py-2 bg-[#388e7e] hover:bg-[#2d7366] text-white text-xs font-bold rounded-lg transition shadow-xs">
                        Save
                    </button>
                </div>

            </div>

            <!-- Right Column: Live Rate Matrix (6 Cols) matching Reference Images 1 & 2 -->
            <div class="lg:col-span-6">
                
                <!-- Empty State matching Reference Image 1 Screen 1 -->
                <div id="matrixEmptyState" class="w-full bg-[#f8fafc] border border-slate-200/80 rounded-xl py-6 text-center text-slate-600 font-medium text-xs">
                    No Rate chart found.
                </div>

                <!-- Fixed Rate Flat Card View (When format is fixed_rate) -->
                <div id="fixedRateCard" class="hidden bg-white p-8 rounded-xl border border-slate-200 text-center space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-[#388e7e] flex items-center justify-center text-2xl font-black">
                        ₹
                    </div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Fixed Rate Value</h4>
                    <div id="fixedRateValue" class="text-3xl font-extrabold text-slate-900 font-mono">₹ 0.00</div>
                    <p class="text-xs text-slate-400">All procurement will be credited at this flat rate per litre.</p>
                </div>

                <!-- Live Matrix Table Wrapper matching Reference Image 1 Screen 4 & Image 2 -->
                <div id="matrixTableWrapper" class="w-full hidden bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                    <div class="overflow-auto max-h-[600px]">
                        <table class="w-full text-center border-collapse text-[11px]" id="matrixTable">
                            <thead id="matrixTableHead" class="bg-[#f8fafc]">
                                <!-- Dynamically generated -->
                            </thead>
                            <tbody id="matrixTableBody" class="font-normal text-slate-700 divide-y divide-slate-100">
                                <!-- Dynamically generated -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

<!-- ================= MODAL: FAT / CLR / SNF STEPS (MATCHING REFERENCE IMAGE 1 SCREEN 2 & 3) ================= -->
<div id="stepModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-2xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden">
        <!-- Mint / Teal Header matching screenshot 1, 2, 3: #98d8c8 -->
        <div class="p-3.5 bg-[#98d8c8] text-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900" id="stepModalTitle">FAT steps</h3>
            <button type="button" onclick="closeStepModal()" class="text-slate-700 hover:text-slate-950 transition font-bold text-base px-1">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <input type="hidden" id="step_edit_index" value="-1">

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700" id="step_from_label">FAT From :</label>
                <input type="number" step="0.1" id="step_input_from" placeholder="2" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700" id="step_to_label">FAT To :</label>
                <input type="number" step="0.1" id="step_input_to" placeholder="3" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700">Step Amount :</label>
                <input type="number" step="0.1" id="step_input_amount" placeholder="1" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 text-xs font-normal text-slate-700 cursor-pointer">
                    <input type="checkbox" id="step_add_another" class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                    <span>Save And Add Another</span>
                </label>
            </div>

            <!-- Buttons matching Screenshot: Cancel (grey) and Apply (#388e7e) -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeStepModal()" class="px-5 py-1.5 bg-[#e2e8f0] hover:bg-[#cbd5e1] text-slate-700 text-xs font-medium rounded-md transition">
                    Cancel
                </button>
                <button type="button" onclick="applyStepModal()" class="px-6 py-1.5 bg-[#388e7e] hover:bg-[#2d7366] text-white text-xs font-medium rounded-md transition shadow-xs">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: BONUS / PENALTY ================= -->
<div id="ruleModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-2xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden">
        <div class="p-3.5 bg-[#98d8c8] text-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900" id="ruleModalTitle">Bonus / Penalty</h3>
            <button type="button" onclick="closeRuleModal()" class="text-slate-700 hover:text-slate-950 transition font-bold text-base px-1">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <input type="hidden" id="rule_edit_index" value="-1">

            <div class="flex items-center gap-4 text-xs">
                <label class="font-medium text-slate-700">Type :</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-1.5 font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="bonus" checked class="text-[#388e7e] focus:ring-[#388e7e]">
                        <span>Bonus</span>
                    </label>
                    <label class="flex items-center gap-1.5 font-medium text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="penalty" class="text-rose-600 focus:ring-rose-500">
                        <span>Penalty</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700" id="rule_from_label">FAT From :</label>
                <input type="number" step="0.1" id="rule_input_from" placeholder="0.0" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700" id="rule_to_label">FAT To :</label>
                <input type="number" step="0.1" id="rule_input_to" placeholder="0.0" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="w-32 font-medium text-slate-700">Amount :</label>
                <input type="number" step="0.1" id="rule_input_amount" placeholder="0.0" class="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-md bg-white focus:border-[#388e7e] outline-none font-medium">
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 text-xs font-normal text-slate-700 cursor-pointer">
                    <input type="checkbox" id="rule_add_another" class="rounded text-[#388e7e] focus:ring-[#388e7e]">
                    <span>Save And Add Another</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeRuleModal()" class="px-5 py-1.5 bg-[#e2e8f0] hover:bg-[#cbd5e1] text-slate-700 text-xs font-medium rounded-md transition">
                    Cancel
                </button>
                <button type="button" onclick="applyRuleModal()" class="px-6 py-1.5 bg-[#388e7e] hover:bg-[#2d7366] text-white text-xs font-medium rounded-md transition shadow-xs">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // State arrays for Steps & Rules
    let fatSteps = [];
    let snfSteps = [];
    let fatRules = [];
    let snfRules = [];

    let currentStepTarget = 'fat'; // 'fat' or 'secondary'
    let currentRuleTarget = 'fat'; // 'fat' or 'secondary'
    let toastTimeout = null;

    // Toast popup matching Screenshot 1
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

    // Handle Format change (fat_clr, fat_snf, fat_only, clr_only, fixed_rate)
    function handleFormatChange() {
        const fmt = document.getElementById('field_format').value;

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
            // fat_clr or fat_snf
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

    // Handle Type change
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

    // Pure Rate Calculation Function
    function calcRate(fat, secondary) {
        const format = document.getElementById('field_format')?.value || 'fat_clr';
        const type = document.getElementById('field_type')?.value || 'rate_per_kg';
        const startAmt = parseFloat(document.getElementById('field_starting_amount')?.value) || 0;

        if (format === 'fixed_rate' || type === 'flat') {
            return Math.max(0, startAmt);
        }

        let rate = 0;

        if (type === 'rate_per_kg') {
            // Rate per KG calculation matching reference screenshots
            // e.g. at FAT 2.0, CLR 20.0 with 100 step amounts -> Rate = 22.00
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
            // Increase per point slab calculation
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

        // Apply Bonus / Penalty Rules
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

    // Modal Operations: Steps (Matching Screenshot 1 Screen 2 & 3)
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
                fromInp.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
            } else {
                fromInp.readOnly = false;
                fromInp.classList.remove('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
            }
        } else {
            // Adding a new step: if steps already exist, next from is pre-filled and readonly!
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
                fromInp.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
                toInp.value = '';
                amtInp.value = '';
            } else {
                fromInp.value = isFat ? '2' : (secTitle === 'CLR' ? '20' : '8.0');
                fromInp.readOnly = false;
                fromInp.classList.remove('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
                toInp.value = isFat ? '3' : (secTitle === 'CLR' ? '25' : '9.5');
                amtInp.value = '100';
            }
        }

        document.getElementById('step_add_another').checked = false;
        document.getElementById('stepModal').classList.remove('hidden');
        toInp.focus();
    }

    function closeStepModal() {
        document.getElementById('stepModal').classList.add('hidden');
    }

    // Apply Step Modal with Exact Validation requested by user & shown in Screenshot 1
    function applyStepModal() {
        const fromVal = parseFloat(document.getElementById('step_input_from').value);
        const toVal = parseFloat(document.getElementById('step_input_to').value);
        let amt = parseFloat(document.getElementById('step_input_amount').value);
        const editIdx = parseInt(document.getElementById('step_edit_index').value, 10);
        const isFat = currentStepTarget === 'fat';
        const secTitle = getSecondaryName();
        const type = document.getElementById('field_type').value;

        if (isNaN(fromVal) || isNaN(toVal) || isNaN(amt)) {
            showFailedToast('Please enter valid numeric values for From, To and Amount.');
            return;
        }

        // Exact Range Validation as specified in user request & Screenshot 1
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

        // If user typed 1 in Step Amount (as drawn in Mockup Image 1 Screen 2 & 3),
        // it means 100.00 as shown in Screen 4!
        if (type === 'rate_per_kg' && amt > 0 && amt <= 5) {
            amt = amt * 100;
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
            fromInp.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
            document.getElementById('step_input_to').value = '';
            document.getElementById('step_input_amount').value = '';
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

    // Render Steps in cards matching Reference Images 1 & 2
    function renderStepsUI(target) {
        const isFat = target === 'fat';
        const list = isFat ? fatSteps : snfSteps;
        const listWrap = document.getElementById(isFat ? 'list_fat_steps' : 'list_snf_steps');
        const hiddenInp = document.getElementById(isFat ? 'input_fat_steps' : 'input_snf_steps');

        hiddenInp.value = JSON.stringify(list);
        listWrap.innerHTML = '';

        if (list.length === 0) {
            listWrap.innerHTML = `<div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>`;
            return;
        }

        list.forEach((item, idx) => {
            const isLast = (idx === list.length - 1);
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between py-1.5 px-2 bg-slate-50/60 rounded-lg text-xs font-semibold text-slate-800';
            row.innerHTML = `
                <div class="font-medium text-slate-800 tracking-wide">
                    ${Number(item.from).toFixed(1)} -> ${Number(item.to).toFixed(1)}
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-800">
                        ${Number(item.amount).toFixed(2)}
                    </span>
                    ${isLast ? `
                        <button type="button" onclick="deleteStep('${isFat ? 'fat' : 'secondary'}', ${idx})" title="Delete step" class="text-slate-700 hover:text-rose-600 font-bold text-xs transition px-1">
                            ✕
                        </button>
                    ` : '<span class="w-3"></span>'}
                </div>
            `;
            listWrap.appendChild(row);
        });
    }

    // Modal Operations: Bonus / Penalty Rules
    function openRuleModal(target, editIdx = -1) {
        currentRuleTarget = target;
        const secTitle = getSecondaryName();
        document.getElementById('ruleModalTitle').innerText = 'Bonus / Penalty';
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
            listWrap.innerHTML = `<div class="text-[11px] text-slate-400 py-3 text-center empty-placeholder">Click on Add btn To add new step</div>`;
            return;
        }

        list.forEach((item, idx) => {
            const isBonus = item.type === 'bonus';
            const badge = document.createElement('div');
            badge.className = `flex items-center justify-between py-1.5 px-2 rounded-lg text-xs font-semibold ${isBonus ? 'bg-emerald-50 text-emerald-900' : 'bg-rose-50 text-rose-900'}`;
            badge.innerHTML = `
                <div class="flex items-center gap-1.5">
                    <span class="uppercase text-[10px]">${item.type}</span>
                    <span>${item.from} - ${item.to}</span>
                    <span>${isBonus ? '+' : '-'}${Number(item.amount).toFixed(2)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="deleteRule('${isFat ? 'fat' : 'secondary'}', ${idx})" class="text-rose-500 hover:text-rose-700 font-bold text-xs">
                        ✕
                    </button>
                </div>
            `;
            listWrap.appendChild(badge);
        });
    }

    // Live Matrix / Chart Rendering (Matches EXACTLY Image 1 Screen 4 & Image 2)
    function renderMatrixPreview() {
        const format = document.getElementById('field_format')?.value || 'fat_clr';
        const emptyState = document.getElementById('matrixEmptyState');
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
            return;
        }

        fixedCard.classList.add('hidden');

        // 2. CLR Only Format
        if (format === 'clr_only') {
            if (snfSteps.length === 0) {
                emptyState.classList.remove('hidden');
                tableWrapper.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            tableWrapper.classList.remove('hidden');

            let clrMin = Math.min(...snfSteps.map(s => parseFloat(s.from ?? s.step ?? 20.0)));
            let clrMax = Math.max(...snfSteps.map(s => parseFloat(s.to ?? 25.0)));
            if (isNaN(clrMin)) clrMin = 20.0;
            if (isNaN(clrMax) || clrMax <= clrMin) clrMax = clrMin + 5.0;

            const inc = parseFloat(document.getElementById('field_increment_by')?.value || '1');
            const incT = Math.round(inc * 10);
            const clrValues = [];
            let curC = Math.round(clrMin * 10);
            const maxCT = Math.round(clrMax * 10);
            while (curC <= maxCT && clrValues.length < 60) {
                clrValues.push(curC / 10);
                curC += incT;
            }

            thead.innerHTML = `
                <tr>
                    <th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">clr</th>
                    <th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">RATE</th>
                </tr>
            `;

            let tbodyHtml = '';
            clrValues.forEach(c => {
                const r = calcRate(0, c);
                tbodyHtml += `
                    <tr>
                        <td class="p-2 border border-slate-200 text-center text-slate-800 text-xs">${c.toFixed(1)}</td>
                        <td class="p-2 border border-slate-200 text-center text-slate-800 text-xs">${r.toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = tbodyHtml;
            return;
        }

        // 3. Formats requiring FAT (fat_only, fat_snf, fat_clr)
        // Check if FAT steps exist:
        if (fatSteps.length === 0) {
            emptyState.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            return;
        }

        // For fat_clr: BOTH FAT steps AND CLR steps must exist! (Image 1 Screen 1 & Screen 4)
        if (format === 'fat_clr' && snfSteps.length === 0) {
            emptyState.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        tableWrapper.classList.remove('hidden');

        // Derive FAT rows (step 0.1)
        let fatMin = Math.min(...fatSteps.map(s => parseFloat(s.from ?? s.step ?? 2.0)));
        let fatMax = Math.max(...fatSteps.map(s => parseFloat(s.to ?? 3.0)));
        if (isNaN(fatMin)) fatMin = 2.0;
        if (isNaN(fatMax) || fatMax < fatMin) fatMax = fatMin + 1.0;

        const fatValues = [];
        let curF = Math.round(fatMin * 10);
        const maxFT = Math.round(fatMax * 10);
        while (curF <= maxFT && fatValues.length < 80) {
            fatValues.push(curF / 10);
            curF += 1;
        }

        // 3a. FAT Only Format
        if (format === 'fat_only') {
            thead.innerHTML = `
                <tr>
                    <th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">fat</th>
                    <th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 text-center text-xs sticky top-0 z-10 w-1/2">RATE</th>
                </tr>
            `;

            let tbodyHtml = '';
            fatValues.forEach(f => {
                const r = calcRate(f, 0);
                tbodyHtml += `
                    <tr>
                        <td class="p-2 border border-slate-200 text-center text-slate-800 text-xs">${f.toFixed(1)}</td>
                        <td class="p-2 border border-slate-200 text-center text-slate-800 text-xs">${r.toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = tbodyHtml;
            return;
        }

        // 3b. FAT + CLR / FAT + SNF Format
        let secValues = [];
        const isFatSnf = format === 'fat_snf';

        if (snfSteps.length > 0) {
            let secMin = Math.min(...snfSteps.map(s => parseFloat(s.from ?? s.step ?? 20.0)));
            let secMax = Math.max(...snfSteps.map(s => parseFloat(s.to ?? 25.0)));
            if (isNaN(secMin)) secMin = isFatSnf ? 8.0 : 20.0;
            if (isNaN(secMax) || secMax < secMin) secMax = secMin + (isFatSnf ? 2.0 : 5.0);

            const inc = parseFloat(document.getElementById('field_increment_by')?.value || (isFatSnf ? '0.2' : '1'));
            const incT = Math.round(inc * 10);

            let curS = Math.round(secMin * 10);
            const maxST = Math.round(secMax * 10);
            while (curS <= maxST && secValues.length < 50) {
                secValues.push(curS / 10);
                curS += incT;
            }
        } else if (isFatSnf) {
            secValues = [0.0];
        }

        // Build Table Header (Exactly as Image 1 Screen 4 & Image 2)
        let thHtml = '<tr>';
        thHtml += `<th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 sticky left-0 top-0 z-20 text-xs text-center">${format}</th>`;

        secValues.forEach(s => {
            thHtml += `<th class="p-2 border border-slate-200 bg-[#f8fafc] font-normal text-slate-800 text-center sticky top-0 z-10 text-xs min-w-[50px]">${s.toFixed(1)}</th>`;
        });
        thHtml += '</tr>';
        thead.innerHTML = thHtml;

        // Build Table Body Rows (Exactly as Image 1 Screen 4 & Image 2)
        let tbodyHtml = '';
        fatValues.forEach(f => {
            tbodyHtml += '<tr>';
            tbodyHtml += `<td class="p-2 border border-slate-200 bg-white font-normal text-slate-800 text-center sticky left-0 z-10 text-xs">${f.toFixed(1)}</td>`;

            secValues.forEach(s => {
                const r = calcRate(f, s);
                tbodyHtml += `<td class="p-2 border border-slate-200 text-center text-slate-800 text-xs font-normal">
                    ${r.toFixed(2)}
                </td>`;
            });

            tbodyHtml += '</tr>';
        });
        tbody.innerHTML = tbodyHtml;
    }

    function resetRateChartForm() {
        document.getElementById('rateChartForm').reset();
        fatSteps = [];
        snfSteps = [];
        fatRules = [];
        snfRules = [];
        handleFormatChange();
    }

    // Auto-initialize on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        handleFormatChange();
    });
</script>
@endsection
