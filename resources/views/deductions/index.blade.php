@extends('layouts.app')

@section('title', 'Deduction List')
@section('breadcrumb', 'Deductions')
@section('header_title', 'Farmer Deductions Management')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="upload" class="w-4 h-4 text-slate-500"></i>
            <span>Import Advance</span>
        </button>
        <button type="button" onclick="openAddDeductionModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Deduction</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Filter Bar matching Reference Screenshot 1 -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('deductions.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Searchable Deduction Type Filter -->
                <div class="relative" x-data="{
                    open: false,
                    search: '',
                    selectedVal: '{{ $deductionType }}',
                    categories: window.__DED_CATEGORIES__ || [],
                    get filtered() {
                        if (!this.search) return this.categories;
                        const s = this.search.toLowerCase();
                        return this.categories.filter(c => 
                            (c.name && c.name.toLowerCase().includes(s)) ||
                            (c.name_hi && c.name_hi.toLowerCase().includes(s)) ||
                            (c.code && c.code.toLowerCase().includes(s))
                        );
                    },
                    get selectedLabel() {
                        if (this.selectedVal === 'all' || !this.selectedVal) return 'All Types / Tap To Select';
                        const found = this.categories.find(c => c.code === this.selectedVal);
                        return found ? (found.name + (found.name_hi ? ' (' + found.name_hi + ')' : '')) : this.selectedVal;
                    },
                    select(code) {
                        this.selectedVal = code;
                        this.open = false;
                        this.search = '';
                        $refs.hiddenFilterType.value = code;
                        $refs.hiddenFilterType.form.submit();
                    }
                }" @click.outside="open = false">
                    <input type="hidden" name="deduction_type" x-ref="hiddenFilterType" :value="selectedVal">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Deduction Type :</label>
                        <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.filterSearchInp?.focus())" class="min-w-[190px] px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 hover:bg-white focus:bg-white focus:border-emerald-500 outline-none font-semibold text-slate-800 flex items-center justify-between gap-2 shadow-2xs">
                            <span x-text="selectedLabel" class="truncate"></span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </button>
                    </div>

                    <div x-show="open" x-transition class="absolute z-50 left-28 right-0 sm:w-64 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-2 max-h-60 overflow-y-auto" style="display: none;">
                        <input type="text" x-ref="filterSearchInp" x-model="search" placeholder="Search category..." class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-slate-50 focus:bg-white outline-none mb-1.5">
                        <div class="space-y-1">
                            <button type="button" @click="select('all')" class="w-full text-left px-2 py-1 text-xs rounded hover:bg-slate-100 text-slate-500 font-medium">
                                -- All Deduction Types --
                            </button>
                            <template x-for="cat in filtered" :key="cat.code">
                                <button type="button" @click="select(cat.code)" class="w-full text-left px-2 py-1.5 text-xs rounded hover:bg-emerald-50 hover:text-emerald-800 flex items-center justify-between transition">
                                    <span class="font-bold text-slate-800" x-text="cat.name"></span>
                                    <span class="text-[10px] text-slate-400" x-text="cat.name_hi" x-show="cat.name_hi"></span>
                                </button>
                            </template>
                            <div x-show="filtered.length === 0" class="text-center py-2 text-xs text-slate-400">
                                No categories found
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search farmer, code, phone..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3"></i>
                </div>

                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span>Search</span>
                </button>

                @if($deductionType !== 'all' || !empty($search))
                    <a href="{{ route('deductions.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline">
                        Reset
                    </a>
                @endif
            </div>

            <!-- Quick Action Buttons on right if on mobile -->
            <div class="flex items-center gap-2 sm:hidden w-full">
                <button type="button" onclick="openAddDeductionModal()" class="w-full py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center">
                    + Add Deduction
                </button>
            </div>
        </form>
    </div>

    <!-- Deduction Listing Table Card matching Reference Screenshot 1 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Card Header -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Deduction List</h3>
                <p class="text-xs text-slate-500">Overview of cattle feed, medical, and store purchases deducted from farmer balances.</p>
            </div>
            <div class="text-xs font-semibold text-slate-600">
                Total Net Deductions: <span class="font-mono font-bold text-rose-600">₹{{ number_format($netDeductionBalance, 2) }}</span>
            </div>
        </div>

        <!-- Table matching Reference Screenshot 1 -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-emerald-50/60 border-b border-emerald-100/80 text-[11px] font-bold text-emerald-950 uppercase tracking-wider">
                        <th class="py-3 px-4">Farmer Code</th>
                        <th class="py-3 px-4">Farmer Name</th>
                        <th class="py-3 px-4">Phone No</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($farmers as $farmer)
                        @php
                            $given = (float) $farmer->deductions->where('transaction_type', 'given')->sum('amount');
                            $received = (float) $farmer->deductions->where('transaction_type', 'received')->sum('amount');
                            $netAmount = max(0, $given - $received);

                            $advances = \App\Models\FarmerAdvance::where('farmer_id', $farmer->id)
                                ->where('status', '!=', 'recovered')
                                ->get();
                            $advancesSum = (float) $advances->sum(fn($adv) => (float) ($adv->principal_balance ?? ($adv->amount - $adv->paid_amount - $adv->deducted_amount)));

                            $walletBal = (float) ($farmer->wallet ? $farmer->wallet->balance : 0);
                            $totalFarmerDues = max($walletBal, round($advancesSum + $netAmount, 2));
                            $latestDeduction = $farmer->deductions->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <!-- Farmer Code -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $farmer->farmer_code }}
                            </td>

                            <!-- Farmer Name & Bilingual Name -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-blue-600 block">
                                    {{ $farmer->name }}
                                </span>
                                @if(!empty($farmer->name_hi))
                                    <span class="text-[11px] text-slate-500 font-normal block">
                                        {{ $farmer->name_hi }}
                                    </span>
                                @endif
                            </td>

                            <!-- Phone No -->
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $farmer->phone ?: '-' }}
                            </td>

                            <!-- Total Amount Badge synchronized with wallet dues -->
                            <td class="py-3.5 px-4">
                                @if($totalFarmerDues > 0)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            ₹{{ number_format($totalFarmerDues, 2) }}
                                        </span>
                                        @if($advancesSum > 0 && $netAmount > 0)
                                            <span class="text-[10px] text-slate-500 block mt-0.5 font-medium">
                                                (₹{{ number_format($advancesSum, 2) }} लोन + ₹{{ number_format($netAmount, 2) }} कटौती)
                                            </span>
                                        @elseif($advancesSum > 0 && $netAmount == 0)
                                            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">(Advance / Loan Dues)</span>
                                        @elseif($netAmount > 0)
                                            <span class="text-[10px] text-slate-500 block mt-0.5">(Deduction Balance)</span>
                                        @else
                                            <span class="text-[10px] text-slate-500 block mt-0.5">(Total Outstanding)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ₹0.00 (चुकता)
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons matching Reference (View - Yellow, Edit - Blue, Delete - Red) -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- View Details (Eye - Yellow button) -->
                                    <button type="button" 
                                            onclick="viewFarmerDeductions({{ $farmer->id }})" 
                                            title="View Details"
                                            class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Edit (Pencil - Blue button) -->
                                    @if($latestDeduction)
                                        <button type="button" 
                                                onclick="openEditDeductionModal({{ $latestDeduction->id }}, {{ $farmer->id }}, '{{ $latestDeduction->entry_date ? ($latestDeduction->entry_date instanceof \Carbon\Carbon ? $latestDeduction->entry_date->format('Y-m-d') : substr($latestDeduction->entry_date, 0, 10)) : date('Y-m-d') }}', '{{ $latestDeduction->deduction_type }}', '{{ $latestDeduction->transaction_type }}', '{{ $latestDeduction->amount }}', '{{ addslashes($latestDeduction->comments ?? '') }}')" 
                                                title="Edit Latest Deduction"
                                                class="w-7 h-7 rounded-lg bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition shadow-2xs">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="openAddDeductionModal({{ $farmer->id }})" 
                                                title="Add Deduction"
                                                class="w-7 h-7 rounded-lg bg-blue-400 text-white flex items-center justify-center transition opacity-60 hover:opacity-100">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif

                                    <!-- Delete (Trash - Red button) -->
                                    @if($latestDeduction)
                                        <form action="{{ route('deductions.destroy', $latestDeduction) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this deduction record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    title="Delete Deduction"
                                                    class="w-7 h-7 rounded-lg bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center transition shadow-2xs">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i data-lucide="minus-circle" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold">No farmer deduction records found.</p>
                                <button type="button" onclick="openAddDeductionModal()" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">+ Record new deduction</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer -->
        <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <div class="text-xs text-slate-500 font-medium">
                @if($farmers->total() > 0)
                    Showing {{ $farmers->firstItem() }} to {{ $farmers->lastItem() }} of {{ $farmers->total() }} farmers
                @else
                    Showing 0 entries
                @endif
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('deductions.index') }}" class="flex items-center gap-1.5 text-xs text-slate-500">
                    <input type="hidden" name="deduction_type" value="{{ $deductionType }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <span>Per page:</span>
                    <select name="per_page" onchange="this.form.submit()" class="px-2 py-1 text-xs border border-slate-200 rounded-lg bg-white">
                        <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>

                <div>
                    {{ $farmers->links() }}
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ================= MODAL: ADD DEDUCTION (MATCHING REFERENCE SCREENSHOT 2) ================= -->
<div id="addDeductionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Blue Header matching Screenshot 2 -->
        <div class="p-4 sm:p-5 bg-blue-600 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i data-lucide="minus-circle" class="w-5 h-5"></i>
                <span id="deductionModalTitle">Add Deduction</span>
            </h3>
            <button type="button" onclick="closeAddDeductionModal()" class="text-white/80 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form matching exact inputs in media_1791388483234.png -->
        <form id="deductionForm" action="{{ route('deductions.store') }}" method="POST" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
            @csrf
            <div id="formMethodContainer"></div>

            <!-- 1. Farmer Name (User icon) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                    Farmer Name *
                </label>
                <select name="farmer_id" id="field_farmer_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-medium">
                    <option value="">Select Farmer</option>
                    @foreach($allFarmers as $f)
                        <option value="{{ $f->id }}">
                            {{ $f->farmer_code }} - {{ $f->name }} @if(!empty($f->name_hi)) ({{ $f->name_hi }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Date (Calendar icon) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-blue-600"></i>
                    Date *
                </label>
                <input type="date" name="entry_date" id="field_entry_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-medium">
            </div>

            <!-- 3. Deduction Type (Searchable Dropdown from Categories, No Other Option) -->
            <div class="relative" x-data="{
                open: false,
                search: '',
                selectedCode: '',
                categories: window.__DED_CATEGORIES__ || [],
                get filtered() {
                    if (!this.search) return this.categories;
                    const s = this.search.toLowerCase();
                    return this.categories.filter(c => 
                        (c.name && c.name.toLowerCase().includes(s)) ||
                        (c.name_hi && c.name_hi.toLowerCase().includes(s)) ||
                        (c.code && c.code.toLowerCase().includes(s))
                    );
                },
                get selectedLabel() {
                    if (!this.selectedCode) return 'Select Deduction Type';
                    const found = this.categories.find(c => c.code === this.selectedCode);
                    return found ? (found.name + (found.name_hi ? ' (' + found.name_hi + ')' : '')) : this.selectedCode;
                },
                select(code) {
                    this.selectedCode = code;
                    this.open = false;
                    this.search = '';
                    document.getElementById('field_deduction_type').value = code;
                }
            }" @click.outside="open = false" @set-ded-type.window="selectedCode = $event.detail">
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-blue-600"></i>
                        Deduction Type *
                    </label>
                    <a href="{{ route('deduction-categories.index') }}" target="_blank" class="text-[10px] text-blue-600 hover:underline font-semibold">+ Manage Categories</a>
                </div>
                <input type="hidden" name="deduction_type" id="field_deduction_type" required :value="selectedCode">
                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.catModalSearchInp?.focus())" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none flex items-center justify-between text-left font-medium">
                    <span x-text="selectedLabel" :class="selectedCode ? 'text-slate-900 font-bold' : 'text-slate-400'"></span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                </button>

                <div x-show="open" x-transition class="absolute z-50 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg p-2 max-h-56 overflow-y-auto" style="display: none;">
                    <input type="text" x-ref="catModalSearchInp" x-model="search" placeholder="Search deduction category..." class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-slate-50 focus:bg-white outline-none mb-1.5">
                    <div class="space-y-1">
                        <template x-for="cat in filtered" :key="cat.code">
                            <button type="button" @click="select(cat.code)" class="w-full text-left px-2.5 py-1.5 text-xs rounded-lg hover:bg-emerald-50 hover:text-emerald-800 flex items-center justify-between transition">
                                <span class="font-bold text-slate-800" x-text="cat.name"></span>
                                <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="cat.name_hi" x-show="cat.name_hi"></span>
                            </button>
                        </template>
                        <div x-show="filtered.length === 0" class="text-center py-2 text-xs text-slate-400">
                            No categories found
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Transaction Type (Arrows icon: Given / Received Radio) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-blue-600"></i>
                    Transaction Type
                </label>
                <div class="flex items-center gap-6 p-2 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="transaction_type" value="given" checked class="text-blue-600 focus:ring-blue-500">
                        <span>Given (Deduction Debit)</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="transaction_type" value="received" class="text-blue-600 focus:ring-blue-500">
                        <span>Received (Repayment Credit)</span>
                    </label>
                </div>
            </div>

            <!-- 5. Amount (Currency icon) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                    <span class="font-bold text-blue-600">Rs</span>
                    Amount *
                </label>
                <input type="number" step="0.01" min="0.01" name="amount" id="field_amount" required placeholder="Enter Amount" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-bold text-slate-900">
            </div>

            <!-- 6. Comments (Message icon) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-blue-600"></i>
                    Comments
                </label>
                <textarea name="comments" id="field_comments" rows="3" placeholder="Enter Comments" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none resize-none"></textarea>
            </div>

            <!-- Modal Footer Buttons: Reset & Save -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="resetDeductionForm()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Reset
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: VIEW FARMER DEDUCTIONS BREAKDOWN ================= -->
<div id="viewDeductionsModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-extrabold text-slate-900" id="viewModalFarmerTitle">Farmer Deductions</h3>
                <p class="text-xs text-slate-500" id="viewModalFarmerSubtitle">Complete deduction and adjustment transaction history</p>
            </div>
            <button type="button" onclick="closeViewModal()" class="w-8 h-8 rounded-full bg-slate-200/60 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <!-- Active Wallet Dues Banner (if balance exists from Advance / Cattle Feed / Loans) -->
            <div id="viewModalWalletBanner" class="hidden"></div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Given</span>
                    <p class="text-sm font-black text-rose-600 font-mono mt-0.5" id="viewModalTotalGiven">₹0.00</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Received</span>
                    <p class="text-sm font-black text-emerald-600 font-mono mt-0.5" id="viewModalTotalReceived">₹0.00</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Net Balance</span>
                    <p class="text-sm font-black text-blue-700 font-mono mt-0.5" id="viewModalNetBalance">₹0.00</p>
                </div>
            </div>

            <!-- Table of Transactions -->
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Type</th>
                            <th class="py-2.5 px-3">Transaction</th>
                            <th class="py-2.5 px-3">Amount</th>
                            <th class="py-2.5 px-3">Comments</th>
                            <th class="py-2.5 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="viewModalTableBody" class="divide-y divide-slate-100">
                        <!-- Populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeViewModal()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL: IMPORT DEDUCTION (MATCHING REFERENCE SCREENSHOT 3) ================= -->
<div id="importDeductionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Blue Header matching Screenshot 3 -->
        <div class="p-4 bg-blue-600 text-white flex items-center justify-between">
            <h3 class="text-xs font-bold">Import Advance</h3>
            <button type="button" onclick="closeImportModal()" class="text-white/80 hover:text-white transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form with + Select File and Download Sample link matching Screenshot 3 -->
        <form action="{{ route('deductions.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            <div class="flex items-center justify-between gap-4">
                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span id="selectedFileNameDisplay">+ Select File</span>
                    <input type="file" name="file" accept=".csv,.txt" required class="hidden" onchange="handleFileSelect(this)">
                </label>

                <a href="{{ route('deductions.sample-template') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">
                    Download Sample
                </a>
            </div>

            <p class="text-[11px] text-slate-400">
                Supports CSV file with columns: <code>farmer_code</code>, <code>date</code>, <code>deduction_type</code>, <code>transaction_type</code>, <code>amount</code>, <code>comments</code>.
            </p>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.__DED_CATEGORIES__ = {!! json_encode($deductionCategories->map(fn($c) => ['code' => $c->code, 'name' => $c->name, 'name_hi' => $c->name_hi])) !!};

    // Add / Edit Modal Controls
    function openAddDeductionModal(defaultFarmerId = null) {
        document.getElementById('deductionModalTitle').textContent = 'Add Deduction';
        const form = document.getElementById('deductionForm');
        form.action = "{{ route('deductions.store') }}";
        document.getElementById('formMethodContainer').innerHTML = '';
        
        resetDeductionForm();
        if (defaultFarmerId) {
            document.getElementById('field_farmer_id').value = defaultFarmerId;
        }

        document.getElementById('addDeductionModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function openEditDeductionModal(id, farmerId, date, type, transType, amount, comments) {
        document.getElementById('deductionModalTitle').textContent = 'Edit Deduction';
        const form = document.getElementById('deductionForm');
        form.action = `/deductions/${id}`;
        document.getElementById('formMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('field_farmer_id').value = farmerId;
        document.getElementById('field_entry_date').value = date;
        
        document.getElementById('field_deduction_type').value = type;
        window.dispatchEvent(new CustomEvent('set-ded-type', { detail: type }));
        
        const radio = document.querySelector(`input[name="transaction_type"][value="${transType}"]`);
        if (radio) radio.checked = true;

        document.getElementById('field_amount').value = amount;
        document.getElementById('field_comments').value = comments;

        document.getElementById('addDeductionModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeAddDeductionModal() {
        document.getElementById('addDeductionModal').classList.add('hidden');
    }

    function resetDeductionForm() {
        document.getElementById('deductionForm').reset();
        document.getElementById('field_entry_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('field_deduction_type').value = '';
        window.dispatchEvent(new CustomEvent('set-ded-type', { detail: '' }));
    }

    // View Details Modal Controls
    function viewFarmerDeductions(farmerId) {
        document.getElementById('viewModalTableBody').innerHTML = '<tr><td colspan="6" class="p-4 text-center text-slate-400">Loading deduction records...</td></tr>';
        const walletBanner = document.getElementById('viewModalWalletBanner');
        if (walletBanner) {
            walletBanner.className = 'hidden';
            walletBanner.innerHTML = '';
        }
        document.getElementById('viewDeductionsModal').classList.remove('hidden');

        fetch(`/deductions/farmer/${farmerId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!data || !data.farmer) throw new Error('Invalid data');
                document.getElementById('viewModalFarmerTitle').textContent = `Deductions - ${data.farmer.code} ${data.farmer.name}`;
                document.getElementById('viewModalFarmerSubtitle').textContent = `Phone: ${data.farmer.phone || 'N/A'}`;
                document.getElementById('viewModalTotalGiven').textContent = `₹${(data.total_given || 0).toFixed(2)}`;
                document.getElementById('viewModalTotalReceived').textContent = `₹${(data.total_received || 0).toFixed(2)}`;
                document.getElementById('viewModalNetBalance').textContent = `₹${(data.net_total || 0).toFixed(2)}`;

                // Wallet dues banner
                if (walletBanner && (data.wallet_balance > 0 || data.advances_count > 0)) {
                    let catPills = '';
                    if (data.wallet_categories && Object.keys(data.wallet_categories).length > 0) {
                        catPills = Object.entries(data.wallet_categories)
                            .filter(([_, amt]) => parseFloat(amt) > 0)
                            .map(([cat, amt]) => `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200"><b>${cat}:</b>&nbsp;₹${parseFloat(amt).toFixed(2)}</span>`)
                            .join(' ');
                    }
                    if (!catPills && data.wallet_balance > 0) {
                        catPills = `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">Advance / Loan: ₹${data.wallet_balance.toFixed(2)}</span>`;
                    }

                    walletBanner.className = 'p-3.5 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs';
                    walletBanner.innerHTML = `
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-200 text-amber-900">Wallet Dues / बक़ाया</span>
                                <span class="font-mono font-bold text-amber-950 text-sm">₹${(data.wallet_balance || 0).toFixed(2)}</span>
                            </div>
                            <div class="text-[11px] text-amber-900/80 flex flex-wrap items-center gap-1.5">
                                <span>स्रोत (Source):</span>
                                ${catPills || '<span class="italic">Advance & Loan Account</span>'}
                            </div>
                        </div>
                        <a href="/wallets/${data.farmer.id}" target="_blank" class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                            <span>वॉलेट लेजर देखें</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    `;
                }

                const tbody = document.getElementById('viewModalTableBody');
                if (data.deductions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                <p class="font-bold text-xs text-slate-700">Deduction (कटौती) टेबल में कोई अलग से रिकॉर्ड नहीं मिला।</p>
                                ${data.wallet_balance > 0 ? `
                                    <p class="text-[11px] text-amber-800 mt-1.5 font-medium">
                                        👉 कुल बकाया <b>₹${data.wallet_balance.toFixed(2)}</b> किसान के <b>Advance / Loan (अग्रिम/लोन)</b> या <b>Cattle Feed/स्टोर</b> से आ रहा है।
                                    </p>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                    if (window.lucide) lucide.createIcons();
                    return;
                }

                tbody.innerHTML = data.deductions.map(item => `
                    <tr class="hover:bg-slate-50">
                        <td class="py-2 px-3 font-mono text-slate-600">${item.entry_date.split('T')[0]}</td>
                        <td class="py-2 px-3 capitalize font-semibold text-slate-800">${item.deduction_type.replace('_', ' ')}</td>
                        <td class="py-2 px-3">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase ${item.transaction_type === 'given' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'}">
                                ${item.transaction_type}
                            </span>
                        </td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">₹${parseFloat(item.amount).toFixed(2)}</td>
                        <td class="py-2 px-3 text-slate-500">${item.comments || '-'}</td>
                        <td class="py-2 px-3 text-center">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" onclick="closeViewModal(); openEditDeductionModal(${item.id}, ${item.farmer_id}, '${(item.entry_date || '').split('T')[0]}', '${item.deduction_type}', '${item.transaction_type}', '${item.amount}', '${escape(item.comments || '')}')" class="text-blue-600 hover:underline font-bold text-[11px]">Edit</button>
                                <form action="/deductions/${item.id}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this deduction entry?');">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-rose-600 hover:underline font-bold text-[11px]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                `).join('');

                if (window.lucide) lucide.createIcons();
            })
            .catch(err => {
                document.getElementById('viewModalTableBody').innerHTML = '<tr><td colspan="6" class="p-4 text-center text-rose-500">Failed to load deduction records.</td></tr>';
            });
    }

    function closeViewModal() {
        document.getElementById('viewDeductionsModal').classList.add('hidden');
    }

    // Import Modal Controls
    function openImportModal() {
        document.getElementById('importDeductionModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeImportModal() {
        document.getElementById('importDeductionModal').classList.add('hidden');
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            document.getElementById('selectedFileNameDisplay').textContent = input.files[0].name;
        }
    }

    // Backdrop click listeners
    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('addDeductionModal')) closeAddDeductionModal();
        if (e.target === document.getElementById('viewDeductionsModal')) closeViewModal();
        if (e.target === document.getElementById('importDeductionModal')) closeImportModal();
    });
</script>
@endsection
