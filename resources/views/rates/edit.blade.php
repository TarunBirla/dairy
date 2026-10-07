@extends('layouts.app')

@section('title', 'Edit Rate Chart')
@section('breadcrumb', 'Edit Rate Chart')
@section('header_title', 'Rate Charts / Edit: ' . $chart->name)

@section('content')
<div class="space-y-6">

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

    <!-- Main Two-Column Layout matching Reference Screenshot 2 -->
    <form id="rateChartForm" action="{{ route('rates.update', $chart) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="fat_steps" id="input_fat_steps" value="{{ json_encode($chart->fat_steps ?? []) }}">
        <input type="hidden" name="snf_steps" id="input_snf_steps" value="{{ json_encode($chart->snf_steps ?? []) }}">
        <input type="hidden" name="fat_rules" id="input_fat_rules" value="{{ json_encode($chart->fat_rules ?? []) }}">
        <input type="hidden" name="snf_rules" id="input_snf_rules" value="{{ json_encode($chart->snf_rules ?? []) }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Form Details & Steps (7 Cols) -->
            <div class="lg:col-span-7 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Name: *</label>
                    <input type="text" name="name" id="field_name" required value="{{ old('name', $chart->name) }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Category :</label>
                    <select name="category" id="field_category" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="collection" {{ $chart->category === 'collection' ? 'selected' : '' }}>Collection</option>
                        <option value="milk_sale" {{ $chart->category === 'milk_sale' ? 'selected' : '' }}>Milk Sale</option>
                        <option value="chilling_center" {{ $chart->category === 'chilling_center' ? 'selected' : '' }}>Chilling Center</option>
                    </select>
                </div>

                <!-- Milk Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type: *</label>
                    <select name="milk_type" id="field_milk_type" onchange="renderMatrixPreview()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="cow" {{ $chart->milk_type === 'cow' ? 'selected' : '' }}>Cow</option>
                        <option value="buffalo" {{ $chart->milk_type === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                        <option value="mixed" {{ $chart->milk_type === 'mixed' ? 'selected' : '' }}>Mix</option>
                    </select>
                </div>

                <!-- Format -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Format: *</label>
                    <select name="format" id="field_format" onchange="handleFormatChange()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="fat_snf" {{ ($chart->format ?? 'fat_snf') === 'fat_snf' ? 'selected' : '' }}>FAT + SNF</option>
                        <option value="fat_only" {{ ($chart->format ?? '') === 'fat_only' ? 'selected' : '' }}>FAT Only</option>
                        <option value="fixed_rate" {{ ($chart->format ?? '') === 'fixed_rate' ? 'selected' : '' }}>Fixed Rate</option>
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Type: *</label>
                    <select name="type" id="field_type" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="increase_per_point" {{ ($chart->type ?? 'increase_per_point') === 'increase_per_point' ? 'selected' : '' }}>Increase per fat/snf points</option>
                        <option value="matrix_slab" {{ ($chart->type ?? '') === 'matrix_slab' ? 'selected' : '' }}>Matrix slab</option>
                        <option value="flat" {{ ($chart->type ?? '') === 'flat' ? 'selected' : '' }}>Fixed Rate</option>
                    </select>
                </div>

                <!-- Starting amount -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Starting amount: *</label>
                    <input type="number" step="0.01" name="starting_amount" id="field_starting_amount" oninput="renderMatrixPreview()" value="{{ old('starting_amount', $chart->starting_amount ?? $chart->base_rate) }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-bold text-emerald-800">
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

                <!-- Status & Default -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50">
                            <option value="active" {{ $chart->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $chart->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_default" value="1" {{ $chart->is_default ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span>Default Rate Chart</span>
                        </label>
                    </div>
                </div>

                <!-- Footer Buttons: Save -->
                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <a href="{{ route('rates.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Update Rate Chart
                    </button>
                </div>

            </div>

            <!-- Right Column: Live Matrix Preview 'fat_snf' (5 Cols) -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col min-h-[460px]">
                <div class="p-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-black tracking-wider uppercase text-slate-800">fat_snf</h3>
                    <span class="text-[10px] text-slate-400 font-medium">Live Rate Matrix</span>
                </div>

                <div id="matrixPreviewContainer" class="flex-1 p-4 overflow-auto flex items-center justify-center">
                    <div id="matrixEmptyState" class="text-center py-16 text-slate-400">
                        <i data-lucide="table" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                        <p class="text-xs font-medium">Add FAT & SNF steps to preview</p>
                    </div>

                    <div id="matrixTableWrapper" class="w-full hidden">
                        <table class="w-full text-center border-collapse text-[11px]" id="matrixTable">
                            <!-- Populated dynamically by JS -->
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<!-- ================= MODAL: FAT / SNF STEPS ================= -->
<div id="stepModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
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

<!-- ================= MODAL: BONUS / PENALTY ================= -->
<div id="ruleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
            <h3 class="text-xs font-bold" id="ruleModalTitle">Bonus / Penalty FAT</h3>
            <button type="button" onclick="closeRuleModal()" class="text-white/80 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-5 space-y-4">
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
    // Initialize state with existing chart data
    let fatSteps = @json($chart->fat_steps ?? []);
    let snfSteps = @json($chart->snf_steps ?? []);
    let fatRules = @json($chart->fat_rules ?? []);
    let snfRules = @json($chart->snf_rules ?? []);

    if (!Array.isArray(fatSteps)) fatSteps = [];
    if (!Array.isArray(snfSteps)) snfSteps = [];
    if (!Array.isArray(fatRules)) fatRules = [];
    if (!Array.isArray(snfRules)) snfRules = [];

    let currentStepTarget = 'fat';
    let editingStepIndex = -1;

    let currentRuleTarget = 'fat';
    let editingRuleIndex = -1;

    function handleFormatChange() {
        renderMatrixPreview();
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
        const item = { step: val, amount: amt };

        if (editingStepIndex >= 0) {
            list[editingStepIndex] = item;
        } else {
            list.push(item);
        }

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
                <span class="font-bold text-slate-800">${item.step} &rarr; <span class="font-mono text-emerald-700">₹${item.amount.toFixed(2)}</span></span>
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
                    <span class="inline-block px-1.5 py-0.2 rounded font-bold uppercase text-[9px] ${item.type === 'bonus' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">${item.type}</span>
                    <span class="font-bold text-slate-700 ml-1">${item.from} - ${item.to}</span>
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

    // Dynamic Matrix Preview Generator
    function renderMatrixPreview() {
        const emptyState = document.getElementById('matrixEmptyState');
        const tableWrapper = document.getElementById('matrixTableWrapper');
        const matrixTable = document.getElementById('matrixTable');

        const baseAmount = parseFloat(document.getElementById('field_starting_amount').value) || 0;
        const format = document.getElementById('field_format').value;

        if (format === 'fixed_rate') {
            emptyState.classList.add('hidden');
            tableWrapper.classList.remove('hidden');
            matrixTable.innerHTML = `
                <div class="py-12 text-center">
                    <span class="text-xs font-bold text-slate-500 block mb-1">Fixed Flat Rate Configured</span>
                    <span class="text-2xl font-black text-emerald-700 font-mono">₹${baseAmount.toFixed(2)} / L</span>
                </div>
            `;
            return;
        }

        if (fatSteps.length === 0 && snfSteps.length === 0 && baseAmount === 0) {
            emptyState.classList.remove('hidden');
            tableWrapper.classList.add('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        tableWrapper.classList.remove('hidden');

        const sampleFats = [3.0, 3.5, 4.0, 4.5, 5.0, 5.5, 6.0];
        const sampleSnfs = [8.0, 8.2, 8.5, 8.8, 9.0, 9.2, 9.5];

        let html = '<thead><tr class="bg-emerald-50 text-emerald-950 font-bold border-b border-emerald-100">';
        html += '<th class="p-2 border border-slate-200 bg-slate-100">FAT \\ SNF</th>';
        sampleSnfs.forEach(snf => {
            html += `<th class="p-2 border border-slate-200">${snf.toFixed(1)}</th>`;
        });
        html += '</tr></thead><tbody>';

        sampleFats.forEach(fat => {
            html += `<tr class="hover:bg-slate-50"><td class="p-2 font-bold bg-slate-50 border border-slate-200">${fat.toFixed(1)}</td>`;
            sampleSnfs.forEach(snf => {
                let cellRate = baseAmount;

                if (fatSteps.length > 0 || snfSteps.length > 0) {
                    fatSteps.forEach(s => { if (fat >= s.step) cellRate += s.amount; });
                    snfSteps.forEach(s => { if (snf >= s.step) cellRate += s.amount; });
                } else {
                    cellRate = (fat * 6.5) + (snf * 4.2);
                }

                fatRules.forEach(r => {
                    if (fat >= r.from && fat <= r.to) {
                        cellRate += (r.type === 'penalty' ? -r.amount : r.amount);
                    }
                });

                snfRules.forEach(r => {
                    if (snf >= r.from && snf <= r.to) {
                        cellRate += (r.type === 'penalty' ? -r.amount : r.amount);
                    }
                });

                cellRate = Math.max(0, cellRate);
                html += `<td class="p-2 border border-slate-200 font-mono font-semibold text-slate-800">${cellRate.toFixed(2)}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody>';

        matrixTable.innerHTML = html;
    }

    // Initialize UI on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderStepsUI('fat');
        renderStepsUI('snf');
        renderRulesUI('fat');
        renderRulesUI('snf');
        renderMatrixPreview();
    });
</script>
@endsection
