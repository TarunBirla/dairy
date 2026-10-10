@extends('layouts.app')

@section('title', 'Add Rate Chart')
@section('breadcrumb', 'Add Rate Chart')
@section('header_title', 'Rate Charts / Add Rate Chart')

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb / Title Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Add Rate Chart</h2>
            <p class="text-xs text-slate-500">Configure procurement rate formula, incremental steps, and quality incentives.</p>
        </div>
        <a href="{{ route('rates.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition shadow-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Rate Charts</span>
        </a>
    </div>

    <!-- Main Two-Column Layout matching Reference Screenshot 2 -->
    <form id="rateChartForm" action="{{ route('rates.store') }}" method="POST">
        @csrf
        <input type="hidden" name="fat_steps" id="input_fat_steps" value="[]">
        <input type="hidden" name="snf_steps" id="input_snf_steps" value="[]">
        <input type="hidden" name="fat_rules" id="input_fat_rules" value="[]">
        <input type="hidden" name="snf_rules" id="input_snf_rules" value="[]">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Form Details & Steps (7 Cols) -->
            <div class="lg:col-span-7 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Name: *</label>
                    <input type="text" name="name" id="field_name" required placeholder="e.g. 100 or Cow Collection Standard" value="{{ old('name') }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category :</label>
                    <select name="category" id="field_category" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="collection" selected>Collection</option>
                        <option value="milk_sale">Milk Sale</option>
                        <option value="chilling_center">Chilling Center</option>
                    </select>
                </div>

                <!-- Milk Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type: *</label>
                    <select name="milk_type" id="field_milk_type" onchange="renderMatrixPreview()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="cow" selected>Cow</option>
                        <option value="buffalo">Buffalo</option>
                        <option value="mixed">Mix</option>
                    </select>
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Format: *</label>
                    <select name="format" id="field_format" onchange="handleFormatChange()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="fat_snf" selected>FAT + SNF</option>
                        <option value="fat_only">FAT Only</option>
                        <option value="fixed_rate">Fixed Rate</option>
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Type: *</label>
                    <select name="type" id="field_type" onchange="renderMatrixPreview()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="increase_per_point" selected>Increase per fat/snf points</option>
                        <option value="matrix_slab">Matrix slab</option>
                        <option value="flat">Fixed Rate</option>
                    </select>
                </div>

                <!-- Starting amount -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Starting amount: *</label>
                    <input type="number" step="0.01" name="starting_amount" id="field_starting_amount" oninput="renderMatrixPreview()" placeholder="0.00" value="35.00" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-bold text-emerald-800">
                </div>

                <!-- 2x2 Steps Grid matching Reference Screenshot 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    
                    <!-- 1. FAT steps -->
                    <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">FAT steps</span>
                            <button type="button" onclick="openStepModal('fat')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>
                        </div>
                    </div>

                    <!-- 2. SNF steps -->
                    <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">SNF steps</span>
                            <button type="button" onclick="openStepModal('snf')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_steps" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>
                        </div>
                    </div>

                    <!-- 3. Bonus / Penalty FAT -->
                    <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">Bonus / Penalty FAT</span>
                            <button type="button" onclick="openRuleModal('fat')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_fat_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>
                        </div>
                    </div>

                    <!-- 4. Bonus / Penalty SNF -->
                    <div class="p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/50 flex flex-col justify-between min-h-[120px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">Bonus / Penalty SNF</span>
                            <button type="button" onclick="openRuleModal('snf')" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                + Add
                            </button>
                        </div>
                        <div id="list_snf_rules" class="space-y-1.5 flex-1">
                            <div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>
                        </div>
                    </div>

                </div>

                <!-- Default Rule Checkbox -->
                <div class="pt-2 flex items-center gap-2">
                    <input type="checkbox" name="is_default" id="is_default" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <label for="is_default" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Set as Default Rate Chart for this Milk Type & Category
                    </label>
                </div>

                <!-- Footer Buttons: Reset & Save matching Reference -->
                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <button type="button" onclick="resetRateChartForm()" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Reset
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Save
                    </button>
                </div>

            </div>

            <!-- Right Column: Live Matrix Preview & Richmond Calculator (5 Cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <!-- 1. Live Matrix Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col min-h-[460px]">
                    <div class="p-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-xs font-black tracking-wider uppercase text-slate-800" id="matrix_format_title">fat_snf</h3>
                        <span class="text-[10px] text-slate-400 font-medium">Live Rate Matrix</span>
                    </div>

                    <!-- Interval Selectors Above Table -->
                    <div id="matrixIntervalControls" class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1.5">
                            <label for="select_fat_interval" class="text-[11px] font-bold text-slate-600">FAT interval:</label>
                            <select id="select_fat_interval" onchange="renderMatrixPreview()" class="px-2 py-1 text-[11px] font-semibold border border-slate-200 rounded-lg bg-white outline-none focus:border-blue-500">
                                <option value="0.1">0.1</option>
                                <option value="0.2">0.2</option>
                                <option value="0.5" selected>0.5</option>
                            </select>
                        </div>
                        <div id="snf_interval_wrapper" class="flex items-center gap-1.5">
                            <label for="select_snf_interval" class="text-[11px] font-bold text-slate-600">SNF interval:</label>
                            <select id="select_snf_interval" onchange="renderMatrixPreview()" class="px-2 py-1 text-[11px] font-semibold border border-slate-200 rounded-lg bg-white outline-none focus:border-blue-500">
                                <option value="0.1">0.1</option>
                                <option value="0.2" selected>0.2</option>
                                <option value="0.5">0.5</option>
                            </select>
                        </div>
                    </div>

                    <div id="matrixPreviewContainer" class="flex-1 p-3 overflow-auto flex items-center justify-center">
                        <div id="matrixEmptyState" class="text-center py-16 text-slate-400">
                            <i data-lucide="table" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                            <p class="text-xs font-medium">Add FAT & SNF steps to preview</p>
                        </div>

                        <div id="matrixTableWrapper" class="w-full hidden">
                            <div class="overflow-auto max-h-[440px] relative border border-slate-200 rounded-xl shadow-2xs">
                                <table class="w-full text-center border-collapse text-[11px]" id="matrixTable">
                                    <!-- Populated dynamically by JS -->
                                </table>
                            </div>
                            <div class="pt-2 px-1 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                <span>Rates in Rs per litre</span>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Bonus</span>
                                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Penalty</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Richmond SNF Helper Card -->
                <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800">Richmond SNF & Rate Calculator</h4>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Quick Quality Test</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 text-xs">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">CLR :</label>
                            <input type="number" step="0.1" id="richmond_clr" value="28" oninput="calcRichmondSNF()" placeholder="28.0" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">FAT % :</label>
                            <input type="number" step="0.1" id="richmond_fat" value="4.0" oninput="calcRichmondSNF()" placeholder="4.0" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Temp (°C) :</label>
                            <input type="number" step="0.5" id="richmond_temp" value="27" oninput="calcRichmondSNF()" placeholder="27" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
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

<!-- ================= MODAL: FAT / SNF STEPS (REFERENCE SCREENSHOT 2) ================= -->
<div id="stepModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Blue Header matching screenshot -->
        <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
            <h3 class="text-xs font-bold" id="stepModalTitle">SNF steps</h3>
            <button type="button" onclick="closeStepModal()" class="text-white/80 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Step :</label>
                <input type="number" step="0.1" id="step_input_value" placeholder="e.g. 6.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Amount :</label>
                <input type="number" step="0.1" id="step_input_amount" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
            </div>

            <label class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer pt-1">
                <input type="checkbox" id="step_add_another" class="rounded text-blue-600 focus:ring-blue-500">
                <span>Save And Add Another</span>
            </label>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeStepModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="button" onclick="applyStepModal()" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: BONUS / PENALTY (REFERENCE SCREENSHOTS 3 & 4) ================= -->
<div id="ruleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Blue Header matching screenshot -->
        <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
            <h3 class="text-xs font-bold" id="ruleModalTitle">Bonus / Penalty FAT</h3>
            <button type="button" onclick="closeRuleModal()" class="text-white/80 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Type Radio buttons -->
            <div class="flex items-center gap-4">
                <label class="text-xs font-bold text-slate-700">Type :</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="bonus" checked class="text-blue-600 focus:ring-blue-500">
                        <span>Bonus</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="rule_type_radio" value="penalty" class="text-blue-600 focus:ring-blue-500">
                        <span>Penalty</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="rule_from_label">FAT From :</label>
                <input type="number" step="0.1" id="rule_input_from" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1" id="rule_to_label">FAT To :</label>
                <input type="number" step="0.1" id="rule_input_to" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Amount :</label>
                <input type="number" step="0.1" id="rule_input_amount" placeholder="0.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold">
            </div>

            <label class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer pt-1">
                <input type="checkbox" id="rule_add_another" class="rounded text-blue-600 focus:ring-blue-500">
                <span>Save And Add Another</span>
            </label>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeRuleModal()" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="button" onclick="applyRuleModal()" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">Apply</button>
            </div>
        </div>
    </div>
</div>

<script>
    // In-memory state for steps & rules
    let fatSteps = [];
    let snfSteps = [];
    let fatRules = [];
    let snfRules = [];

    let currentStepTarget = 'fat'; // 'fat' or 'snf'
    let editingStepIndex = -1;

    let currentRuleTarget = 'fat'; // 'fat' or 'snf'
    let editingRuleIndex = -1;

    /**
     * Pure function to calculate rate per litre for a given (fat, snf).
     * Applies incremental point step additions and bonus/penalty rules.
     */
    function calcRate(fat, snf) {
        const formatSelect = document.getElementById('field_format');
        const format = formatSelect ? formatSelect.value : 'fat_snf';
        const typeSelect = document.getElementById('field_type');
        const type = typeSelect ? typeSelect.value : 'increase_per_point';
        const startingAmount = parseFloat(document.getElementById('field_starting_amount').value) || 0;

        if (format === 'fixed_rate' || type === 'flat') {
            return Math.max(0, Math.round(startingAmount * 100) / 100);
        }

        let fatAdd = 0;
        let snfAdd = 0;

        const fat10 = Math.round(fat * 10);
        const snf10 = Math.round(snf * 10);

        // 1. FAT Slab Increments
        if (fatSteps.length > 0) {
            const sortedFat = [...fatSteps].sort((a, b) => a.step - b.step);
            const baseFat10 = Math.round(sortedFat[0].step * 10);

            if (fat10 > baseFat10) {
                for (let i = 0; i < sortedFat.length; i++) {
                    const cur10 = Math.round(sortedFat[i].step * 10);
                    if (fat10 <= cur10) break;

                    const next10 = (i + 1 < sortedFat.length) ? Math.round(sortedFat[i + 1].step * 10) : Infinity;
                    const end10 = Math.min(fat10, next10);
                    const points10 = end10 - cur10;

                    if (points10 > 0) {
                        fatAdd += points10 * sortedFat[i].amount;
                    }
                }
            }
        }

        // 2. SNF Slab Increments (only when format is fat_snf)
        if (format === 'fat_snf' && snfSteps.length > 0) {
            const sortedSnf = [...snfSteps].sort((a, b) => a.step - b.step);
            const baseSnf10 = Math.round(sortedSnf[0].step * 10);

            if (snf10 > baseSnf10) {
                for (let i = 0; i < sortedSnf.length; i++) {
                    const cur10 = Math.round(sortedSnf[i].step * 10);
                    if (snf10 <= cur10) break;

                    const next10 = (i + 1 < sortedSnf.length) ? Math.round(sortedSnf[i + 1].step * 10) : Infinity;
                    const end10 = Math.min(snf10, next10);
                    const points10 = end10 - cur10;

                    if (points10 > 0) {
                        snfAdd += points10 * sortedSnf[i].amount;
                    }
                }
            }
        }

        let rate = startingAmount + fatAdd + snfAdd;

        // 3. Bonus / Penalty FAT Rules
        fatRules.forEach(r => {
            const from10 = Math.round(r.from * 10);
            const to10 = Math.round(r.to * 10);
            if (fat10 >= from10 && fat10 <= to10) {
                rate += (r.type === 'penalty' ? -r.amount : r.amount);
            }
        });

        // 4. Bonus / Penalty SNF Rules (only when format is fat_snf)
        if (format === 'fat_snf') {
            snfRules.forEach(r => {
                const from10 = Math.round(r.from * 10);
                const to10 = Math.round(r.to * 10);
                if (snf10 >= from10 && snf10 <= to10) {
                    rate += (r.type === 'penalty' ? -r.amount : r.amount);
                }
            });
        }

        return Math.max(0, Math.round(rate * 100) / 100);
    }

    // Modal: Step Management (FAT / SNF)
    function openStepModal(target, editIndex = -1) {
        currentStepTarget = target;
        editingStepIndex = editIndex;
        const title = target.toUpperCase() + ' steps';
        document.getElementById('stepModalTitle').textContent = title;

        if (editIndex >= 0) {
            const list = target === 'fat' ? fatSteps : snfSteps;
            const item = list[editIndex];
            document.getElementById('step_input_value').value = item.step;
            document.getElementById('step_input_amount').value = item.amount;
        } else {
            document.getElementById('step_input_value').value = '';
            document.getElementById('step_input_amount').value = '';
        }
        document.getElementById('step_add_another').checked = false;
        document.getElementById('stepModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeStepModal() {
        document.getElementById('stepModal').classList.add('hidden');
    }

    function applyStepModal() {
        const val = parseFloat(document.getElementById('step_input_value').value);
        const amt = parseFloat(document.getElementById('step_input_amount').value);

        if (isNaN(val) || isNaN(amt)) {
            alert('Please enter valid Step and Amount values.');
            return;
        }

        const list = currentStepTarget === 'fat' ? fatSteps : snfSteps;
        const val10 = Math.round(val * 10);

        // Prevent duplicate step values in the same list (update instead of adding a duplicate)
        const existingIdx = list.findIndex((item, idx) => {
            if (editingStepIndex >= 0 && idx === editingStepIndex) return false;
            return Math.round(item.step * 10) === val10;
        });

        if (existingIdx >= 0) {
            list[existingIdx].amount = amt;
            if (editingStepIndex >= 0 && editingStepIndex !== existingIdx) {
                list.splice(editingStepIndex, 1);
            }
        } else if (editingStepIndex >= 0) {
            list[editingStepIndex] = { step: val, amount: amt };
        } else {
            list.push({ step: val, amount: amt });
        }

        // Sort ascending by step
        list.sort((a, b) => a.step - b.step);

        renderStepsUI(currentStepTarget);
        renderMatrixPreview();

        if (document.getElementById('step_add_another').checked) {
            editingStepIndex = -1;
            document.getElementById('step_input_value').value = '';
            document.getElementById('step_input_amount').value = '';
            document.getElementById('step_input_value').focus();
        } else {
            closeStepModal();
        }
    }

    function renderStepsUI(target) {
        const list = target === 'fat' ? fatSteps : snfSteps;
        const container = document.getElementById(target === 'fat' ? 'list_fat_steps' : 'list_snf_steps');
        const hiddenInput = document.getElementById(target === 'fat' ? 'input_fat_steps' : 'input_snf_steps');

        hiddenInput.value = JSON.stringify(list);

        if (list.length === 0) {
            container.innerHTML = `<div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>`;
            return;
        }

        container.innerHTML = list.map((item, idx) => `
            <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-slate-200/90 text-xs shadow-2xs">
                <span class="font-bold text-slate-800">${item.step.toFixed(1)} &rarr; <span class="font-mono text-emerald-700">₹${item.amount.toFixed(2)}</span> <span class="text-[10px] text-slate-400 font-normal">/ 0.1 pt</span></span>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="openStepModal('${target}', ${idx})" class="text-[11px] font-bold text-blue-600 hover:underline">Edit</button>
                    <button type="button" onclick="deleteStep('${target}', ${idx})" class="text-[11px] font-bold text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            </div>
        `).join('');
    }

    function deleteStep(target, idx) {
        const list = target === 'fat' ? fatSteps : snfSteps;
        list.splice(idx, 1);
        renderStepsUI(target);
        renderMatrixPreview();
    }

    // Modal: Rule Management (Bonus / Penalty)
    function openRuleModal(target, editIndex = -1) {
        currentRuleTarget = target;
        editingRuleIndex = editIndex;
        const title = `Bonus / Penalty ${target.toUpperCase()}`;
        document.getElementById('ruleModalTitle').textContent = title;
        document.getElementById('rule_from_label').textContent = `${target.toUpperCase()} From :`;
        document.getElementById('rule_to_label').textContent = `${target.toUpperCase()} To :`;

        if (editIndex >= 0) {
            const list = target === 'fat' ? fatRules : snfRules;
            const item = list[editIndex];
            const radio = document.querySelector(`input[name="rule_type_radio"][value="${item.type}"]`);
            if (radio) radio.checked = true;
            document.getElementById('rule_input_from').value = item.from;
            document.getElementById('rule_input_to').value = item.to;
            document.getElementById('rule_input_amount').value = item.amount;
        } else {
            document.querySelector(`input[name="rule_type_radio"][value="bonus"]`).checked = true;
            document.getElementById('rule_input_from').value = '';
            document.getElementById('rule_input_to').value = '';
            document.getElementById('rule_input_amount').value = '';
        }
        document.getElementById('rule_add_another').checked = false;
        document.getElementById('ruleModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeRuleModal() {
        document.getElementById('ruleModal').classList.add('hidden');
    }

    function applyRuleModal() {
        const type = document.querySelector('input[name="rule_type_radio"]:checked').value;
        const from = parseFloat(document.getElementById('rule_input_from').value);
        const to = parseFloat(document.getElementById('rule_input_to').value);
        const amt = parseFloat(document.getElementById('rule_input_amount').value);

        if (isNaN(from) || isNaN(to) || isNaN(amt)) {
            alert('Please enter valid From, To, and Amount values.');
            return;
        }

        // Validation: from must be <= to
        if (from > to) {
            alert('"From" value must be less than or equal to "To" value.');
            return;
        }

        const list = currentRuleTarget === 'fat' ? fatRules : snfRules;
        const item = { type, from, to, amount: amt };

        if (editingRuleIndex >= 0) {
            list[editingRuleIndex] = item;
        } else {
            list.push(item);
        }

        renderRulesUI(currentRuleTarget);
        renderMatrixPreview();

        if (document.getElementById('rule_add_another').checked) {
            editingRuleIndex = -1;
            document.getElementById('rule_input_from').value = '';
            document.getElementById('rule_input_to').value = '';
            document.getElementById('rule_input_amount').value = '';
            document.getElementById('rule_input_from').focus();
        } else {
            closeRuleModal();
        }
    }

    function renderRulesUI(target) {
        const list = target === 'fat' ? fatRules : snfRules;
        const container = document.getElementById(target === 'fat' ? 'list_fat_rules' : 'list_snf_rules');
        const hiddenInput = document.getElementById(target === 'fat' ? 'input_fat_rules' : 'input_snf_rules');

        hiddenInput.value = JSON.stringify(list);

        if (list.length === 0) {
            container.innerHTML = `<div class="text-[11px] text-slate-400 italic py-4 text-center empty-placeholder">Click on Add btn</div>`;
            return;
        }

        container.innerHTML = list.map((item, idx) => `
            <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-slate-200/90 text-xs shadow-2xs">
                <div class="text-[11px]">
                    <span class="inline-block px-1.5 py-0.5 rounded font-bold uppercase text-[9px] ${item.type === 'bonus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">${item.type}</span>
                    <span class="font-bold text-slate-700 ml-1">${item.from.toFixed(1)} - ${item.to.toFixed(1)}</span>
                    <span class="font-mono text-slate-900 ml-1">&rarr; ₹${item.amount.toFixed(2)}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="openRuleModal('${target}', ${idx})" class="text-[11px] font-bold text-blue-600 hover:underline">Edit</button>
                    <button type="button" onclick="deleteRule('${target}', ${idx})" class="text-[11px] font-bold text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            </div>
        `).join('');
    }

    function deleteRule(target, idx) {
        const list = target === 'fat' ? fatRules : snfRules;
        list.splice(idx, 1);
        renderRulesUI(target);
        renderMatrixPreview();
    }

    /**
     * Helper to get min-max heatmap cell background color.
     */
    function getHeatmapBg(rate, minRate, maxRate) {
        if (minRate >= maxRate) return '';
        const ratio = Math.max(0, Math.min(1, (rate - minRate) / (maxRate - minRate)));
        // Interpolate from light red/amber (254, 235, 235) to light emerald (220, 252, 231)
        const r = Math.round(254 + (220 - 254) * ratio);
        const g = Math.round(235 + (252 - 235) * ratio);
        const b = Math.round(235 + (231 - 235) * ratio);
        return `background-color: rgba(${r}, ${g}, ${b}, 0.55);`;
    }

    /**
     * Helper to get bonus/penalty indicators and tooltip for a specific cell.
     */
    function getCellBadgeAndTooltip(fat, snf) {
        const formatSelect = document.getElementById('field_format');
        const format = formatSelect ? formatSelect.value : 'fat_snf';
        const fat10 = Math.round(fat * 10);
        const snf10 = Math.round(snf * 10);
        let hasBonus = false;
        let hasPenalty = false;
        const tips = [];

        fatRules.forEach(r => {
            const from10 = Math.round(r.from * 10);
            const to10 = Math.round(r.to * 10);
            if (fat10 >= from10 && fat10 <= to10) {
                if (r.type === 'bonus') hasBonus = true;
                if (r.type === 'penalty') hasPenalty = true;
                tips.push(`FAT ${r.type}: ₹${r.amount.toFixed(2)} (${r.from}-${r.to})`);
            }
        });

        if (format === 'fat_snf') {
            snfRules.forEach(r => {
                const from10 = Math.round(r.from * 10);
                const to10 = Math.round(r.to * 10);
                if (snf10 >= from10 && snf10 <= to10) {
                    if (r.type === 'bonus') hasBonus = true;
                    if (r.type === 'penalty') hasPenalty = true;
                    tips.push(`SNF ${r.type}: ₹${r.amount.toFixed(2)} (${r.from}-${r.to})`);
                }
            });
        }

        let dots = '';
        if (hasBonus) {
            dots += '<span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 ml-1 align-middle" title="Bonus rule applied"></span>';
        }
        if (hasPenalty) {
            dots += '<span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 ml-1 align-middle" title="Penalty rule applied"></span>';
        }

        return {
            dots,
            tooltip: tips.length > 0 ? tips.join('; ') : ''
        };
    }

    /**
     * Dynamic Matrix Preview Generator
     */
    function renderMatrixPreview() {
        const emptyState = document.getElementById('matrixEmptyState');
        const tableWrapper = document.getElementById('matrixTableWrapper');
        const matrixTable = document.getElementById('matrixTable');
        const formatTitle = document.getElementById('matrix_format_title');
        const intervalControls = document.getElementById('matrixIntervalControls');
        const snfIntervalWrapper = document.getElementById('snf_interval_wrapper');

        const baseAmount = parseFloat(document.getElementById('field_starting_amount').value) || 0;
        const formatSelect = document.getElementById('field_format');
        const format = formatSelect ? formatSelect.value : 'fat_snf';
        const typeSelect = document.getElementById('field_type');
        const type = typeSelect ? typeSelect.value : 'increase_per_point';

        if (formatTitle) {
            formatTitle.textContent = format;
        }

        // Format: Fixed Rate or Type: Flat
        if (format === 'fixed_rate' || type === 'flat') {
            emptyState.classList.add('hidden');
            tableWrapper.classList.remove('hidden');
            if (intervalControls) intervalControls.classList.add('hidden');
            matrixTable.innerHTML = `
                <div class="py-14 text-center">
                    <span class="text-xs font-bold text-slate-500 block mb-1">Fixed Flat Rate Configured</span>
                    <span class="text-3xl font-black text-emerald-700 font-mono">₹${baseAmount.toFixed(2)} / L</span>
                </div>
            `;
            calcRichmondSNF();
            return;
        }

        if (intervalControls) {
            intervalControls.classList.remove('hidden');
            if (snfIntervalWrapper) {
                if (format === 'fat_only') {
                    snfIntervalWrapper.classList.add('hidden');
                } else {
                    snfIntervalWrapper.classList.remove('hidden');
                }
            }
        }

        // Empty state only when there are no steps AND starting amount is 0
        if (fatSteps.length === 0 && snfSteps.length === 0 && baseAmount === 0) {
            emptyState.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            calcRichmondSNF();
            return;
        }

        emptyState.classList.add('hidden');
        tableWrapper.classList.remove('hidden');

        // Derive FAT rows: from BASE FAT to (last FAT step + 1.0)
        let baseFat = 3.0;
        let maxFat = 6.0;
        if (fatSteps.length > 0) {
            const sortedFat = [...fatSteps].sort((a, b) => a.step - b.step);
            baseFat = sortedFat[0].step;
            maxFat = sortedFat[sortedFat.length - 1].step + 1.0;
        }

        // Derive SNF columns: from BASE SNF to (last SNF step + 1.0)
        let baseSnf = 8.0;
        let maxSnf = 9.5;
        if (snfSteps.length > 0) {
            const sortedSnf = [...snfSteps].sort((a, b) => a.step - b.step);
            baseSnf = sortedSnf[0].step;
            maxSnf = sortedSnf[sortedSnf.length - 1].step + 1.0;
        }

        // Read intervals
        const fatIntervalSelect = document.getElementById('select_fat_interval');
        const fatInterval = fatIntervalSelect ? parseFloat(fatIntervalSelect.value) || 0.5 : 0.5;

        const snfIntervalSelect = document.getElementById('select_snf_interval');
        const snfInterval = snfIntervalSelect ? parseFloat(snfIntervalSelect.value) || 0.2 : 0.2;

        // Generate FAT axis (cap at 40 rows)
        const fatValues = [];
        let curFat10 = Math.round(baseFat * 10);
        const maxFat10 = Math.round(maxFat * 10);
        const stepFat10 = Math.max(1, Math.round(fatInterval * 10));

        while (curFat10 <= maxFat10 && fatValues.length < 40) {
            fatValues.push(curFat10 / 10);
            curFat10 += stepFat10;
        }
        if (fatValues.length === 0) fatValues.push(baseFat);

        // If Format is FAT Only
        if (format === 'fat_only') {
            // Find min/max rates for single column heatmap
            let minRate = Infinity;
            let maxRate = -Infinity;
            fatValues.forEach(f => {
                const r = calcRate(f, 0);
                if (r < minRate) minRate = r;
                if (r > maxRate) maxRate = r;
            });

            let html = `<thead><tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-200">
                <th class="p-2 border border-slate-200 sticky top-0 left-0 z-30 bg-slate-200 font-extrabold text-[10px]">FAT %</th>
                <th class="p-2 border border-slate-200 sticky top-0 z-20 bg-slate-100 font-bold text-slate-700">Rate (₹ / L)</th>
            </tr></thead><tbody>`;

            fatValues.forEach(f => {
                const r = calcRate(f, 0);
                const bgStyle = getHeatmapBg(r, minRate, maxRate);
                const { dots, tooltip } = getCellBadgeAndTooltip(f, 0);
                html += `<tr class="hover:bg-blue-50/40">
                    <td class="p-2 border border-slate-200 sticky left-0 z-10 bg-slate-100 font-bold text-slate-800">${f.toFixed(1)}</td>
                    <td data-fat="${f.toFixed(1)}" data-snf="0.0" class="p-2 border border-slate-200 font-mono font-bold text-slate-800 transition-all" style="${bgStyle}" title="${tooltip}">
                        ${r.toFixed(2)}${dots}
                    </td>
                </tr>`;
            });
            html += `</tbody>`;
            matrixTable.innerHTML = html;
            calcRichmondSNF();
            return;
        }

        // Format: FAT + SNF
        // Generate SNF axis (cap at 15 columns)
        const snfValues = [];
        let curSnf10 = Math.round(baseSnf * 10);
        const maxSnf10 = Math.round(maxSnf * 10);
        const stepSnf10 = Math.max(1, Math.round(snfInterval * 10));

        while (curSnf10 <= maxSnf10 && snfValues.length < 15) {
            snfValues.push(curSnf10 / 10);
            curSnf10 += stepSnf10;
        }
        if (snfValues.length === 0) snfValues.push(baseSnf);

        // Find min and max rates across the matrix for heatmap calculation
        let minRate = Infinity;
        let maxRate = -Infinity;
        fatValues.forEach(f => {
            snfValues.forEach(s => {
                const r = calcRate(f, s);
                if (r < minRate) minRate = r;
                if (r > maxRate) maxRate = r;
            });
        });

        // Build Sticky Table
        let html = `<thead><tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-200">`;
        html += `<th class="p-2 border border-slate-200 sticky top-0 left-0 z-30 bg-slate-200 font-extrabold text-slate-800 text-[10px] whitespace-nowrap">FAT \\ SNF</th>`;
        snfValues.forEach(s => {
            html += `<th class="p-2 border border-slate-200 sticky top-0 z-20 bg-slate-100 font-bold text-slate-700 whitespace-nowrap">${s.toFixed(1)}</th>`;
        });
        html += `</tr></thead><tbody>`;

        fatValues.forEach(f => {
            html += `<tr class="hover:bg-blue-50/40">`;
            html += `<td class="p-2 border border-slate-200 sticky left-0 z-10 bg-slate-100 font-bold text-slate-800 whitespace-nowrap">${f.toFixed(1)}</td>`;
            snfValues.forEach(s => {
                const r = calcRate(f, s);
                const bgStyle = getHeatmapBg(r, minRate, maxRate);
                const { dots, tooltip } = getCellBadgeAndTooltip(f, s);
                html += `<td data-fat="${f.toFixed(1)}" data-snf="${s.toFixed(1)}" class="p-2 border border-slate-200 font-mono font-bold text-slate-800 whitespace-nowrap transition-all" style="${bgStyle}" title="${tooltip}">
                    ${r.toFixed(2)}${dots}
                </td>`;
            });
            html += `</tr>`;
        });
        html += `</tbody>`;

        matrixTable.innerHTML = html;
        calcRichmondSNF();
    }

    /**
     * Richmond SNF Helper Formula:
     * Corrected CLR = CLR + 0.2 * (temp - 27)
     * SNF % = (Corrected CLR / 4) + (0.25 * FAT) + 0.44
     */
    function calcRichmondSNF() {
        const clrInput = document.getElementById('richmond_clr');
        const fatInput = document.getElementById('richmond_fat');
        const tempInput = document.getElementById('richmond_temp');

        if (!clrInput || !fatInput || !tempInput) return;

        const clr = parseFloat(clrInput.value) || 0;
        const fat = parseFloat(fatInput.value) || 0;
        const temp = parseFloat(tempInput.value) || 27;

        const correctedCLR = clr + (0.2 * (temp - 27));
        const snf = (correctedCLR / 4) + (0.25 * fat) + 0.44;
        const roundedSNF = Math.round(snf * 100) / 100;

        const rate = calcRate(fat, roundedSNF);

        const snfDisplay = document.getElementById('richmond_snf_result');
        const rateDisplay = document.getElementById('richmond_rate_result');

        if (snfDisplay) snfDisplay.textContent = roundedSNF.toFixed(2) + '%';
        if (rateDisplay) rateDisplay.textContent = '₹' + rate.toFixed(2) + ' / L';

        highlightMatrixCell(fat, roundedSNF);
    }

    /**
     * Highlights the matrix cell closest to the Richmond test parameters.
     */
    function highlightMatrixCell(targetFat, targetSnf) {
        document.querySelectorAll('.richmond-highlight').forEach(el => {
            el.classList.remove('richmond-highlight', 'ring-2', 'ring-blue-600', 'bg-blue-100', 'font-black');
        });

        if (targetFat <= 0 || targetSnf <= 0) return;

        const cells = document.querySelectorAll('td[data-fat][data-snf]');
        if (!cells.length) return;

        let closestCell = null;
        let minDiff = Infinity;

        cells.forEach(c => {
            const cellFat = parseFloat(c.getAttribute('data-fat'));
            const cellSnf = parseFloat(c.getAttribute('data-snf'));
            const diff = Math.abs(cellFat - targetFat) * 2 + Math.abs(cellSnf - targetSnf);
            if (diff < minDiff) {
                minDiff = diff;
                closestCell = c;
            }
        });

        if (closestCell) {
            closestCell.classList.add('richmond-highlight', 'ring-2', 'ring-blue-600', 'bg-blue-100', 'font-black');
        }
    }

    function resetRateChartForm() {
        if (confirm('Are you sure you want to reset all fields and steps?')) {
            document.getElementById('rateChartForm').reset();
            fatSteps = [];
            snfSteps = [];
            fatRules = [];
            snfRules = [];
            renderStepsUI('fat');
            renderStepsUI('snf');
            renderRulesUI('fat');
            renderRulesUI('snf');
            renderMatrixPreview();
        }
    }

    /**
     * Quick console self-test checking the 3 worked examples from specification
     */
    function runRateSelfTest() {
        const savedStarting = document.getElementById('field_starting_amount').value;
        const savedFatSteps = JSON.parse(JSON.stringify(fatSteps));
        const savedSnfSteps = JSON.parse(JSON.stringify(snfSteps));
        const savedFatRules = JSON.parse(JSON.stringify(fatRules));
        const savedSnfRules = JSON.parse(JSON.stringify(snfRules));

        // Setup worked example test conditions:
        // Starting amount = 35, FAT steps: [3.0 -> 0.60, 5.0 -> 0.80], SNF steps: [8.0 -> 0.30]
        document.getElementById('field_starting_amount').value = '35';
        fatSteps = [{ step: 3.0, amount: 0.60 }, { step: 5.0, amount: 0.80 }];
        snfSteps = [{ step: 8.0, amount: 0.30 }];
        fatRules = [];
        snfRules = [];

        const t1 = calcRate(4.0, 8.5); // Expected: 42.50
        const t2 = calcRate(3.5, 8.5); // Expected: 39.50
        const t3 = calcRate(5.5, 9.0); // Expected: 54.00

        const pass1 = Math.abs(t1 - 42.50) < 0.01;
        const pass2 = Math.abs(t2 - 39.50) < 0.01;
        const pass3 = Math.abs(t3 - 54.00) < 0.01;

        console.log(`%c[Rate Engine Self-Test]`, 'font-weight:bold;color:#2563eb;');
        console.log(`  Test 1 (Fat 4.0, SNF 8.5): got ${t1.toFixed(2)}, expected 42.50 -> ${pass1 ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`  Test 2 (Fat 3.5, SNF 8.5): got ${t2.toFixed(2)}, expected 39.50 -> ${pass2 ? '✅ PASS' : '❌ FAIL'}`);
        console.log(`  Test 3 (Fat 5.5, SNF 9.0): got ${t3.toFixed(2)}, expected 54.00 -> ${pass3 ? '✅ PASS' : '❌ FAIL'}`);

        // Restore original state
        document.getElementById('field_starting_amount').value = savedStarting;
        fatSteps = savedFatSteps;
        snfSteps = savedSnfSteps;
        fatRules = savedFatRules;
        snfRules = savedSnfRules;
    }

    // Initialize preview and self-test on load
    document.addEventListener('DOMContentLoaded', function() {
        runRateSelfTest();
        renderMatrixPreview();
    });
</script>
@endsection
