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
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Deduction Type :</label>
                    <select name="deduction_type" onchange="this.form.submit()" class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="all">Tap To Select</option>
                        @foreach($deductionTypesList as $key => $label)
                            <option value="{{ $key }}" {{ $deductionType === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
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
                            $netAmount = $given - $received;
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

                            <!-- Total Amount Badge matching reference (e.g. -3080.00 in green/badge) -->
                            <td class="py-3.5 px-4">
                                @if($netAmount > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-700 text-white">
                                        -{{ number_format($netAmount, 2) }}
                                    </span>
                                @elseif($netAmount < 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-600 text-white">
                                        +{{ number_format(abs($netAmount), 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-200 text-slate-700">
                                        0.00
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
                                                onclick="openEditDeductionModal({{ $latestDeduction->id }}, {{ $farmer->id }}, '{{ $latestDeduction->entry_date->format('Y-m-d') }}', '{{ $latestDeduction->deduction_type }}', '{{ $latestDeduction->transaction_type }}', '{{ $latestDeduction->amount }}', '{{ addslashes($latestDeduction->comments ?? '') }}')" 
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

            <!-- 3. Deduction Type (Tag icon) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                    <i data-lucide="tag" class="w-3.5 h-3.5 text-blue-600"></i>
                    Deduction Type *
                </label>
                <select name="deduction_type" id="field_deduction_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-medium">
                    <option value="">Select Type</option>
                    @foreach($deductionTypesList as $k => $lbl)
                        <option value="{{ $k }}">{{ $lbl }}</option>
                    @endforeach
                </select>
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
    }

    // View Details Modal Controls
    function viewFarmerDeductions(farmerId) {
        document.getElementById('viewModalTableBody').innerHTML = '<tr><td colspan="6" class="p-4 text-center text-slate-400">Loading deduction records...</td></tr>';
        document.getElementById('viewDeductionsModal').classList.remove('hidden');

        fetch(`/deductions/farmer/${farmerId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('viewModalFarmerTitle').textContent = `Deductions - ${data.farmer.code} ${data.farmer.name}`;
                document.getElementById('viewModalFarmerSubtitle').textContent = `Phone: ${data.farmer.phone || 'N/A'}`;
                document.getElementById('viewModalTotalGiven').textContent = `₹${data.total_given.toFixed(2)}`;
                document.getElementById('viewModalTotalReceived').textContent = `₹${data.total_received.toFixed(2)}`;
                document.getElementById('viewModalNetBalance').textContent = `₹${data.net_total.toFixed(2)}`;

                const tbody = document.getElementById('viewModalTableBody');
                if (data.deductions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-slate-400">No deduction records found for this farmer.</td></tr>';
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
                            <div class="inline-flex gap-1">
                                <button type="button" onclick="closeViewModal(); openEditDeductionModal(${item.id}, ${item.farmer_id}, '${item.entry_date.split('T')[0]}', '${item.deduction_type}', '${item.transaction_type}', '${item.amount}', '${escape(item.comments || '')}')" class="text-blue-600 hover:underline font-bold text-[11px]">Edit</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                document.getElementById('viewModalTableBody').innerHTML = '<tr><td colspan="6" class="p-4 text-center text-rose-500">Failed to load deduction records.</td></tr>';
            });

        if (window.lucide) lucide.createIcons();
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
