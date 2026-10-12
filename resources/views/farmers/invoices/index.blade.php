@extends('layouts.app')

@section('title', 'Farmer Invoices')
@section('breadcrumb', 'Farmer Invoices')
@section('header_title', 'Farmer Invoices & Billing')

@section('header_action')
    <div class="flex flex-wrap items-center gap-2">
        <!-- Bank Payment Print Button -->
        <a href="{{ route('farmer-invoices.bank-payment-print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4 text-emerald-600"></i>
            <span>Bank Payment Print</span>
        </a>

        <!-- Download Excel / CSV Button -->
        <a href="{{ route('farmer-invoices.export-excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
            <span>Download Excel</span>
        </a>

        <!-- Generate Invoice Button -->
        <button type="button" onclick="openGenerateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Generate Invoice</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-5">

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

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('farmer-invoices.index') }}" class="flex flex-wrap items-end gap-3.5">
            <input type="hidden" name="status_tab" value="{{ $statusTab }}">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
            </div>

            <div class="min-w-[200px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search Farmer</label>
                <div class="relative">
                    <input type="text" name="farmer_search" value="{{ $farmerSearch }}" placeholder="Name, Code, Phone..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Invoice / Bill #</label>
                <div class="relative">
                    <input type="text" name="invoice_search" value="{{ $invoiceSearch }}" placeholder="BILL-0101..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px] cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('farmer-invoices.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Status Workflow Tabs: Pending Invoices vs Generated Invoices -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'farmer_search' => $farmerSearch, 'invoice_search' => $invoiceSearch, 'status_tab' => 'pending']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $statusTab === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>Pending Invoices (Review & Verify)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $statusTab === 'pending' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $pendingCount }}
                </span>
            </a>

            <a href="{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'farmer_search' => $farmerSearch, 'invoice_search' => $invoiceSearch, 'status_tab' => 'generated']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $statusTab === 'generated' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>Generated Invoices (Finalized)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $statusTab === 'generated' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $generatedCount }}
                </span>
            </a>
        </div>

        <!-- Quick Review All Button when invoices exist -->
        @if(count($allTabIds) > 0)
            <div class="flex items-center gap-2">
                <button type="button" onclick="openReviewAllInTab()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>
                        {{ $statusTab === 'pending' ? 'Review & Verify All Pending (1 / ' . count($allTabIds) . ')' : 'Review / Modify All (1 / ' . count($allTabIds) . ')' }}
                    </span>
                </button>
            </div>
        @endif
    </div>

    <!-- Bulk Selection Action Bar (Shown when 1 or more checkboxes are checked) -->
    <div id="bulkActionBar" class="hidden bg-slate-900 text-white p-3.5 px-5 rounded-2xl shadow-lg flex flex-wrap items-center justify-between gap-4 transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center" id="selectedCountBadge">0</span>
            <span class="text-xs font-semibold text-slate-200">Projected Invoice Selected: <b id="selectedCountText" class="text-white">0</b></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Step-by-Step 1-by-1 Review & Modify Selected -->
            <button type="button" onclick="openReviewSelected()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs">
                <i data-lucide="file-Edit" class="w-3.5 h-3.5"></i>
                <span>Review & Modify Selected (<span id="selectedReviewBtnCount">0</span>) One-by-One</span>
            </button>

            @if($statusTab === 'pending')
                <!-- Direct Bulk Approve to Generated -->
                <button type="button" onclick="submitBulkApprove()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                    <span>Approve & Generate Selected</span>
                </button>
            @endif

            <button type="button" onclick="printSelectedBankSheet()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Bank Payment Print</span>
            </button>
            <button type="button" onclick="submitBulkDelete()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete Selected</span>
            </button>
            <button type="button" onclick="deselectAll()" class="px-2.5 py-1.5 text-xs text-slate-400 hover:text-white transition cursor-pointer">
                Deselect
            </button>
        </div>
    </div>

    <!-- Farmer Invoices Grouped Table Card matching Reference Screenshot -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        @if($statusTab === 'pending')
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>List of Projected / Pending Invoices ({{ $pendingCount }})</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>List of Generated Invoices ({{ $generatedCount }})</span>
                        @endif
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Date Range: <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                    </p>
                </div>
            </div>

            <div class="text-xs font-bold text-slate-600">
                (Projected Invoice Selected: <span id="headerSelectedCounter" class="text-emerald-700">0</span>)
            </div>

            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <span>Showing {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <!-- Top Tier Header -->
                    <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3 text-center border-r border-slate-200 w-10">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                        </th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-slate-100">Farmer Info</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center bg-sky-50 text-sky-900">Milks</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-slate-200 text-center bg-rose-50 text-rose-900">Deduction</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-slate-200 text-center bg-amber-50 text-amber-900">Credit</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-slate-200 text-center bg-emerald-50 text-emerald-900">Payment</th>
                        <th class="py-2.5 px-3 text-center">Actions</th>
                    </tr>

                    <!-- Sub Tier Header -->
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                        <th class="py-2.5 px-3 text-center border-r border-slate-100">#</th>
                        <th class="py-2.5 px-3 border-r border-slate-100">Code</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 min-w-[140px]">Name</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-sky-50/50">Quantity (L)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">Stationary</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">Feed</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">In Hand Advance</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-100/50 font-bold text-rose-800">Total Deduction (A)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/20">Transport</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/20">Subsidy</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/20">Incentive</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/60 font-bold text-amber-800">Total Credit (B)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30">Milk Amount (C)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30 font-semibold text-slate-700">Total Amount (B+C)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-slate-50">Balance</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-100/60 font-bold text-emerald-900">Payment (B+C-A)</th>
                        <th class="py-2.5 px-3 text-center min-w-[145px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($invoices as $index => $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors group" id="row-{{ $inv->id }}">
                            <td class="py-3 px-3 text-center border-r border-slate-100">
                                <input type="checkbox" value="{{ $inv->id }}" onchange="handleRowSelect()" class="row-checkbox w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 font-mono text-[11px] font-bold text-slate-800">
                                {{ $inv->farmer->farmer_code ?? '-' }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100">
                                <div class="font-bold text-slate-900 leading-tight uppercase">{{ $inv->farmer->name ?? 'Unknown Farmer' }}</div>
                                @if(!empty($inv->farmer->phone))
                                    <div class="text-[10px] text-slate-400">{{ $inv->farmer->phone }}</div>
                                @endif
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[9px] text-slate-400 font-mono">{{ $inv->invoice_number }}</span>
                                    @if($inv->status === 'pending')
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Generated</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono bg-sky-50/30 font-semibold text-sky-900">
                                {{ number_format($inv->total_quantity, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->stationary_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->feed_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->advance_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono bg-rose-50/40 font-bold text-rose-700">
                                {{ number_format($inv->total_deduction, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->transport_credit ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->subsidy_credit ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                {{ number_format($inv->incentive_credit ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono bg-amber-50/40 font-bold text-amber-700">
                                {{ number_format($inv->total_credit, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-700">
                                {{ number_format($inv->milk_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono font-semibold text-slate-800">
                                {{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-500">
                                {{ number_format($inv->previous_balance, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono bg-emerald-50/60 font-black text-emerald-700 text-xs">
                                ₹{{ number_format($inv->net_payment, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Review & Modify Invoice Button (Single or Queue) -->
                                    <button type="button" onclick="openReviewQueue([{{ $inv->id }}])"
                                            title="{{ $inv->status === 'pending' ? 'Verify, Correct & Save Invoice' : 'Modify Invoice' }}"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold {{ $inv->status === 'pending' ? 'bg-amber-500 hover:bg-amber-600 text-white shadow-2xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} transition cursor-pointer">
                                        <i data-lucide="sliders-horizontal" class="w-3 h-3"></i>
                                        <span>{{ $inv->status === 'pending' ? 'Verify' : 'Modify' }}</span>
                                    </button>

                                    <!-- In-page Quick Preview Modal button -->
                                    <button type="button" onclick="openInvoicePreviewModal({{ $inv->id }})" title="Quick Bill Preview" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Direct Print Bill -->
                                    <a href="{{ route('farmer-invoices.print', $inv->id) }}" target="_blank" title="Print Bill" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('farmer-invoices.destroy', $inv->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete invoice #{{ $inv->invoice_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Invoice" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="file-question" class="w-8 h-8 text-slate-300"></i>
                                    @if($statusTab === 'pending')
                                        <p class="text-xs font-semibold text-slate-500">No pending invoices awaiting review for the selected period.</p>
                                        @if($generatedCount > 0)
                                            <a href="{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'status_tab' => 'generated']) }}"
                                               class="mt-1 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-xl">
                                                <span>View {{ $generatedCount }} Generated Invoices &rarr;</span>
                                            </a>
                                        @else
                                            <button type="button" onclick="openGenerateModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer">
                                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                <span>Generate Invoices Now</span>
                                            </button>
                                        @endif
                                    @else
                                        <p class="text-xs font-semibold text-slate-500">No generated invoices found for the selected period.</p>
                                        @if($pendingCount > 0)
                                            <a href="{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'status_tab' => 'pending']) }}"
                                               class="mt-1 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 text-white text-xs font-bold rounded-xl shadow-xs">
                                                <span>Review {{ $pendingCount }} Pending Invoices &rarr;</span>
                                            </a>
                                        @else
                                            <button type="button" onclick="openGenerateModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer">
                                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                <span>Generate Invoices Now</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Table Footer Totals Row -->
                @if($invoices->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-100 font-bold text-slate-800 border-t-2 border-slate-300 text-xs">
                            <td colspan="3" class="py-3 px-4 border-r border-slate-200 text-left font-black uppercase tracking-wider text-[11px]">
                                Total (All Filtered)
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono bg-sky-100/70 text-sky-900 font-black">
                                {{ number_format($totalQuantity, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-700">
                                {{ number_format($totalStationary, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-700">
                                {{ number_format($totalFeed, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-700">
                                {{ number_format($totalAdvance, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono bg-rose-100/70 font-black text-rose-800">
                                {{ number_format($totalDeduction, 2) }}
                            </td>
                            <td colspan="3" class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-500 text-[10px] uppercase">
                                Credits Sum &rarr;
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono bg-amber-100/70 font-black text-amber-800">
                                {{ number_format($totalCredit, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-800 font-black">
                                {{ number_format($totalMilkAmount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-900 font-black">
                                {{ number_format($totalAmount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono text-slate-600 font-bold">
                                {{ number_format($totalBalance, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-200 text-right font-mono bg-emerald-200/80 font-black text-emerald-900 text-sm">
                                ₹{{ number_format($totalPayment, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL 1: GENERATE INVOICES MODAL ================= -->
<div id="generateModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Generate Farmer Invoices</h3>
                    <p class="text-xs text-slate-500">Creates projected bills in Pending tab for review & verification</p>
                </div>
            </div>
            <button type="button" onclick="closeGenerateModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('farmer-invoices.generate') }}" class="mt-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Start Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" value="{{ $startDate }}" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">End Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="end_date" value="{{ $endDate }}" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Farmer Selection</label>
                <select name="farmer_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                    <option value="">All Active Farmers (Bulk Generation)</option>
                    @foreach($farmers as $f)
                        <option value="{{ $f->id }}">[{{ $f->farmer_code }}] {{ $f->name }} ({{ $f->phone ?? 'No Phone' }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Leave as "All Active Farmers" to prepare pending invoices for every farmer with activity in this period.</p>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/80 text-[11px] text-amber-800 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    <span>2-Step Verification Workflow:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-amber-900/90 pl-1">
                    <li>Invoices are first placed in the <b>Pending Invoices</b> tab</li>
                    <li>Select single or multiple invoices to inspect collections, additions & deductions</li>
                    <li>Make any needed corrections and click <b>Save Invoice</b> to move to <b>Generated</b> state</li>
                </ul>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeGenerateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Generate Pending Invoices</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: PENDING INVOICE 1-BY-1 REVIEW, CORRECTION & SAVE MODAL (Matching Reference Screenshots) ================= -->
<div id="invoiceReviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="bg-slate-50 rounded-2xl max-w-7xl w-full shadow-2xl border border-slate-200 max-h-[95vh] flex flex-col overflow-hidden">

        <!-- Top Navigation & Queue Header Bar (Matching reference media_1791776887935.png & media_1791776927135.png) -->
        <div class="bg-white px-5 py-3.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeReviewModal()" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-emerald-700 transition cursor-pointer">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Pending Invoice</span>
                </button>
                <span class="text-slate-300">|</span>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-slate-900" id="revFarmerHeader">
                        Farmer : —
                    </div>
                    <div class="text-[11px] font-semibold text-slate-500" id="revDateHeader">
                        Date : —
                    </div>
                </div>
            </div>

            <!-- Right Queue Stepper: < Invoices ( 1 / N ) > -->
            <div class="flex items-center gap-3">
                <div id="revToast" class="hidden px-3 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                    Saved!
                </div>
                <div class="inline-flex items-center gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button type="button" id="revPrevBtn" onclick="stepReviewQueue(-1)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 text-xs font-bold cursor-pointer">
                        &lsaquo;
                    </button>
                    <span class="px-2 text-xs font-extrabold text-slate-800" id="revQueueCounter">
                        Invoices ( 1 / 1 )
                    </span>
                    <button type="button" id="revNextBtn" onclick="stepReviewQueue(1)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 text-xs font-bold cursor-pointer">
                        &rsaquo;
                    </button>
                </div>
                <button type="button" onclick="closeReviewModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Main 2-Column Review & Edit Body -->
        <div class="p-4 sm:p-5 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- LEFT 7 COLUMNS: Collections Table & Cattle Feeds Table -->
            <div class="lg:col-span-7 space-y-5">

                <!-- 1. Collections Card -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 bg-slate-50/70">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-extrabold text-slate-800">Collections</span>
                            <div class="inline-flex rounded-lg bg-slate-200/70 p-0.5 text-[11px] font-bold">
                                <button type="button" onclick="setReviewShiftFilter('all')" id="revShiftAll" class="px-2 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs cursor-pointer">All</button>
                                <button type="button" onclick="setReviewShiftFilter('morning')" id="revShiftMorning" class="px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer">&#9728; Morn</button>
                                <button type="button" onclick="setReviewShiftFilter('evening')" id="revShiftEvening" class="px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer">&#9790; Even</button>
                            </div>
                        </div>

                        <div class="text-[11px] font-bold text-slate-600" id="revPresentShiftText">
                            Present Shift : 0/0
                        </div>

                        <a href="#" id="revEditCollectionsLink" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:text-sky-700">
                            <i data-lucide="pencil" class="w-3 h-3"></i>
                            <span>Edit</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto max-h-[290px]">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px] sticky top-0">
                                <tr>
                                    <th class="py-2 px-3 border-r border-slate-100">Date</th>
                                    <th class="py-2 px-3 border-r border-slate-100">Shift</th>
                                    <th class="py-2 px-3 border-r border-slate-100">Milk</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">Quantity</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">FAT</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">SNF</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">CLR</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">Rate</th>
                                    <th class="py-2 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="revCollectionsTbody" class="divide-y divide-slate-100 text-slate-700">
                                <tr><td colspan="9" class="py-6 text-center text-slate-400">Loading...</td></tr>
                            </tbody>
                            <tfoot id="revCollectionsTfoot" class="bg-slate-50 font-extrabold text-slate-900 border-t border-slate-200 text-xs sticky bottom-0">
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 2. Cattle Feeds / Purchases Card -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
                        <span class="text-xs font-extrabold text-slate-800">Cattle Feeds</span>
                        <a href="#" id="revEditFeedsLink" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:text-sky-700">
                            <i data-lucide="pencil" class="w-3 h-3"></i>
                            <span>Edit</span>
                        </a>
                    </div>
                    <div id="revCattleFeedsContainer" class="p-4 text-center text-slate-500 text-xs">
                        No Records Found.
                    </div>
                    <div class="px-4 py-2 bg-slate-50 border-t border-slate-200 text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Payment Received : <b id="revFeedPaymentReceived">0.00</b></span>
                        <span>Balance Due : <b id="revFeedBalanceDue" class="text-rose-600">0.00</b></span>
                    </div>
                </div>

            </div>

            <!-- RIGHT 5 COLUMNS: Additions, Deductions & Invoice Summary with Save Invoice Button -->
            <div class="lg:col-span-5 space-y-4">

                <!-- 1. Additions (Credits) Editable Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 bg-slate-50/70">
                        <span class="text-xs font-extrabold text-slate-800">Additions</span>
                    </div>
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/60 text-slate-500 font-bold border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="py-2 px-4 border-r border-slate-100">Addition Type</th>
                                <th class="py-2 px-4 text-right w-40">Additions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-semibold">
                            <tr>
                                <td class="py-2 px-4 border-r border-slate-100">Other Payment Cr</td>
                                <td class="py-1.5 px-3 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputOtherCredit" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2.5 py-1 text-xs font-mono font-bold text-sky-700 bg-sky-50/40 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border-r border-slate-100">Transport</td>
                                <td class="py-1.5 px-3 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputTransportCredit" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2.5 py-1 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border-r border-slate-100">Subsidy</td>
                                <td class="py-1.5 px-3 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputSubsidyCredit" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2.5 py-1 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border-r border-slate-100">Incentive</td>
                                <td class="py-1.5 px-3 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputIncentiveCredit" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2.5 py-1 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-extrabold text-slate-900 border-t border-slate-200 text-xs">
                            <tr>
                                <td class="py-2 px-4 border-r border-slate-100">Total</td>
                                <td class="py-2 px-4 text-right font-mono" id="revTotalAdditionsCell">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 2. Deductions Editable Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 bg-slate-50/70">
                        <span class="text-xs font-extrabold text-slate-800">Deductions</span>
                    </div>
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/60 text-slate-500 font-bold border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="py-2 px-3 border-r border-slate-100">Deduction Type</th>
                                <th class="py-2 px-3 border-r border-slate-100 text-right w-28">Deduction</th>
                                <th class="py-2 px-3 border-r border-slate-100 text-right">Balance</th>
                                <th class="py-2 px-3 text-right">Deposit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-semibold">
                            <tr>
                                <td class="py-2 px-3 border-r border-slate-100">Food (Feed)</td>
                                <td class="py-1.5 px-2 border-r border-slate-100 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputFeedDeduction" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2 py-1 text-xs font-mono font-bold text-sky-700 bg-sky-50/40 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-3 border-r border-slate-100 text-right font-mono text-slate-500" id="revFoodBalanceCell">-</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500">-</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3 border-r border-slate-100">Advance</td>
                                <td class="py-1.5 px-2 border-r border-slate-100 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputAdvanceDeduction" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2 py-1 text-xs font-mono font-bold text-sky-700 bg-sky-50/40 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-3 border-r border-slate-100 text-right font-mono text-slate-500">-</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500" id="revAdvanceDepositCell">-</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-3 border-r border-slate-100">Stationary / Other</td>
                                <td class="py-1.5 px-2 border-r border-slate-100 text-right">
                                    <input type="number" step="0.01" min="0" id="revInputStationaryDeduction" oninput="recalcReviewSummary()"
                                           class="w-full text-right px-2 py-1 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>
                                <td class="py-2 px-3 border-r border-slate-100 text-right font-mono text-slate-500">-</td>
                                <td class="py-2 px-3 text-right font-mono text-slate-500">-</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-extrabold text-slate-900 border-t border-slate-200 text-xs">
                            <tr>
                                <td class="py-2 px-3 border-r border-slate-100">Total</td>
                                <td class="py-2 px-3 border-r border-slate-100 text-right font-mono" id="revTotalDeductionsCell">0.00</td>
                                <td class="py-2 px-3 border-r border-slate-100 text-right font-mono" id="revTotalBalanceCell">0.00</td>
                                <td class="py-2 px-3 text-right font-mono" id="revTotalDepositCell">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- 3. Invoice Summary Card + Save Invoice Button (Matching reference media_1791776927135.png) -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
                    <h4 class="text-sm font-bold text-slate-800 mb-3">Invoice Summary</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <div class="sm:col-span-7 space-y-2 text-xs border-r border-slate-100 pr-4">
                            <div class="flex justify-between text-slate-600 font-semibold">
                                <span>Milk Amount</span>
                                <span class="font-mono font-bold text-slate-900" id="revSummaryMilkAmount">0.00</span>
                            </div>
                            <div class="flex justify-between text-slate-600 font-semibold">
                                <span>Additions</span>
                                <span class="font-mono font-bold text-slate-900" id="revSummaryAdditions">0.00</span>
                            </div>
                            <div class="flex justify-between text-slate-800 font-bold pt-1 border-t border-slate-100">
                                <span>Total Amount</span>
                                <span class="font-mono font-extrabold text-slate-900" id="revSummaryTotalAmount">0.00</span>
                            </div>
                            <div class="flex justify-between text-slate-600 font-semibold pt-1 border-t border-slate-100">
                                <span>Total Deduction</span>
                                <span class="font-mono font-bold text-rose-600" id="revSummaryTotalDeduction">0.00</span>
                            </div>
                            <div class="flex justify-between text-slate-600 font-semibold">
                                <span>Round Off</span>
                                <span class="font-mono font-bold text-indigo-600" id="revSummaryRoundOff">0.00</span>
                            </div>
                        </div>

                        <div class="sm:col-span-5 flex flex-col justify-between items-end gap-4">
                            <div class="w-full flex items-baseline justify-between border-b border-slate-100 pb-2">
                                <span class="text-xs font-bold text-emerald-700">Payment</span>
                                <span class="text-lg font-black text-emerald-600 font-mono" id="revSummaryNetPayment">₹0.00</span>
                            </div>

                            <button type="button" id="revSaveInvoiceBtn" onclick="saveAndVerifyCurrentInvoice()"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white hover:bg-emerald-600 text-emerald-700 hover:text-white border-2 border-emerald-600 text-xs font-extrabold rounded-xl shadow-xs transition cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span id="revSaveBtnText">Save Invoice</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL 3: IN-PAGE INVOICE PREVIEW MODAL ================= -->
<div id="invoicePreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-4xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 max-h-[92vh] flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="previewTitle">Farmer Bill Preview</h3>
                    <p class="text-xs text-slate-500" id="previewSubtitle">Invoice loading...</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="previewPrintBtn" onclick="printFromPreviewModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Bill</span>
                </button>
                <button type="button" onclick="closeInvoicePreviewModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Bill Content Body -->
        <div id="previewModalContent" class="overflow-y-auto py-4 space-y-5 flex-1 pr-1">
            <div class="flex items-center justify-center py-12 text-slate-400">
                <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-emerald-600 mr-2"></i>
                <span>Loading bill details...</span>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="pt-4 border-t border-slate-100 shrink-0 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">Generated by Smart Dairy ERP</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeInvoicePreviewModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">
                    Close
                </button>
                <button type="button" onclick="printFromPreviewModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Hidden Form -->
<form id="bulkDeleteForm" method="POST" action="{{ route('farmer-invoices.bulk-delete') }}" class="hidden">
    @csrf
    <div id="bulkDeleteInputs"></div>
</form>

<!-- Bulk Approve Hidden Form -->
<form id="bulkApproveForm" method="POST" action="{{ route('farmer-invoices.bulk-approve') }}" class="hidden">
    @csrf
    <input type="hidden" name="start_date" value="{{ $startDate }}">
    <input type="hidden" name="end_date" value="{{ $endDate }}">
    <div id="bulkApproveInputs"></div>
</form>

@endsection

@push('scripts')
<script>
    const allTabInvoiceIds = @json($allTabIds ?? []);
    const csrfToken = "{{ csrf_token() }}";

    let currentPreviewInvoiceId = null;

    // Review Queue State (for 1-by-1 single or multi-invoice verification & modification)
    let reviewQueueIds = [];
    let reviewQueueIndex = 0;
    let currentReviewData = null;
    let currentReviewShiftFilter = 'all';
    let hasSavedAnyInQueue = false;

    function openGenerateModal() {
        document.getElementById('generateModal').classList.remove('hidden');
    }
    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
    }

    // Toggle Select All Checkboxes
    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        handleRowSelect();
    }

    function deselectAll() {
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        handleRowSelect();
    }

    // Handle Individual Row Selection
    function handleRowSelect() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value, 10));
        const count = selected.length;
        const bar = document.getElementById('bulkActionBar');
        const countBadge = document.getElementById('selectedCountBadge');
        const countText = document.getElementById('selectedCountText');
        const reviewBtnCount = document.getElementById('selectedReviewBtnCount');
        const headerCounter = document.getElementById('headerSelectedCounter');

        if (headerCounter) headerCounter.textContent = count;

        if (count > 0) {
            bar.classList.remove('hidden');
            countBadge.textContent = count;
            countText.textContent = count;
            if (reviewBtnCount) reviewBtnCount.textContent = count;
        } else {
            bar.classList.add('hidden');
        }
    }

    // Open 1-by-1 Review Modal for Selected Checkboxes
    function openReviewSelected() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value, 10));
        if (selected.length === 0) {
            alert('Please select at least one invoice checkbox to review.');
            return;
        }
        openReviewQueue(selected);
    }

    // Open 1-by-1 Review Modal for All Invoices in Current Tab
    function openReviewAllInTab() {
        if (!allTabInvoiceIds || allTabInvoiceIds.length === 0) return;
        openReviewQueue(allTabInvoiceIds);
    }

    // Launch Review Queue with 1 or more Invoice IDs
    function openReviewQueue(idsArray) {
        if (!idsArray || idsArray.length === 0) return;
        reviewQueueIds = idsArray;
        reviewQueueIndex = 0;
        hasSavedAnyInQueue = false;
        document.getElementById('invoiceReviewModal').classList.remove('hidden');
        loadCurrentReviewInvoice();
    }

    function closeReviewModal() {
        document.getElementById('invoiceReviewModal').classList.add('hidden');
        if (hasSavedAnyInQueue) {
            window.location.reload();
        }
    }

    function stepReviewQueue(delta) {
        const nextIdx = reviewQueueIndex + delta;
        if (nextIdx < 0 || nextIdx >= reviewQueueIds.length) return;
        reviewQueueIndex = nextIdx;
        loadCurrentReviewInvoice();
    }

    function loadCurrentReviewInvoice() {
        const invoiceId = reviewQueueIds[reviewQueueIndex];
        const totalInQueue = reviewQueueIds.length;

        document.getElementById('revQueueCounter').textContent = `Invoices ( ${reviewQueueIndex + 1} / ${totalInQueue} )`;
        document.getElementById('revPrevBtn').disabled = (reviewQueueIndex <= 0);
        document.getElementById('revNextBtn').disabled = (reviewQueueIndex >= totalInQueue - 1);

        document.getElementById('revCollectionsTbody').innerHTML = `
            <tr><td colspan="9" class="py-8 text-center text-slate-400">Loading invoice details...</td></tr>
        `;

        fetch(`/farmer-invoices/preview/${invoiceId}`)
            .then(res => res.json())
            .then(data => {
                currentReviewData = data;
                currentReviewShiftFilter = 'all';
                updateShiftFilterButtonsUI();

                // Header
                document.getElementById('revFarmerHeader').textContent = `Farmer : ${data.farmer.code} - ${data.farmer.name}`;
                document.getElementById('revDateHeader').textContent = `Date : ${data.period_start_display} To ${data.period_end_display}`;
                document.getElementById('revPresentShiftText').textContent = `Present Shift : ${data.present_shifts}/${data.total_shifts}`;

                // Edit links
                document.getElementById('revEditCollectionsLink').href = `/collections?farmer_id=${data.farmer.id}&from_date=${data.period_start}&to_date=${data.period_end}`;
                document.getElementById('revEditFeedsLink').href = `/farmers/${data.farmer.id}?tab=purchases`;

                // Render Collections Table
                renderReviewCollectionsTable();

                // Render Cattle Feeds Table
                renderReviewCattleFeedsTable();

                // Populate Additions inputs
                document.getElementById('revInputOtherCredit').value = parseFloat(data.additions.other_credit || 0).toFixed(2);
                document.getElementById('revInputTransportCredit').value = parseFloat(data.additions.transport_credit || 0).toFixed(2);
                document.getElementById('revInputSubsidyCredit').value = parseFloat(data.additions.subsidy_credit || 0).toFixed(2);
                document.getElementById('revInputIncentiveCredit').value = parseFloat(data.additions.incentive_credit || 0).toFixed(2);

                // Populate Deductions inputs
                document.getElementById('revInputFeedDeduction').value = parseFloat(data.deduction_inputs.feed_deduction || 0).toFixed(2);
                document.getElementById('revInputAdvanceDeduction').value = parseFloat(data.deduction_inputs.advance_deduction || 0).toFixed(2);
                document.getElementById('revInputStationaryDeduction').value = parseFloat(data.deduction_inputs.stationary_deduction || 0).toFixed(2);

                document.getElementById('revFoodBalanceCell').textContent = data.feed_remaining_balance > 0 ? parseFloat(data.feed_remaining_balance).toFixed(2) : '-';
                document.getElementById('revAdvanceDepositCell').textContent = data.farmer.anamat > 0 ? parseFloat(data.farmer.anamat).toFixed(2) : '-';

                recalcReviewSummary();

                if (window.lucide) window.lucide.createIcons();
            });
    }

    function setReviewShiftFilter(shift) {
        currentReviewShiftFilter = shift;
        updateShiftFilterButtonsUI();
        renderReviewCollectionsTable();
    }

    function updateShiftFilterButtonsUI() {
        const btnAll = document.getElementById('revShiftAll');
        const btnMorn = document.getElementById('revShiftMorning');
        const btnEven = document.getElementById('revShiftEvening');

        [btnAll, btnMorn, btnEven].forEach(b => {
            b.className = 'px-2 py-0.5 rounded-md text-slate-600 hover:text-slate-900 cursor-pointer';
        });

        const activeBtn = currentReviewShiftFilter === 'morning' ? btnMorn : (currentReviewShiftFilter === 'evening' ? btnEven : btnAll);
        activeBtn.className = 'px-2 py-0.5 rounded-md bg-white text-slate-900 shadow-2xs cursor-pointer font-bold';
    }

    function renderReviewCollectionsTable() {
        if (!currentReviewData) return;
        const tbody = document.getElementById('revCollectionsTbody');
        const tfoot = document.getElementById('revCollectionsTfoot');

        const filtered = (currentReviewData.collections || []).filter(c => {
            if (currentReviewShiftFilter === 'all') return true;
            return (c.shift || '').toLowerCase() === currentReviewShiftFilter;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="py-8 text-center text-slate-400">No collection entries for this shift filter.</td></tr>`;
        } else {
            let html = '';
            filtered.forEach(c => {
                html += `
                    <tr class="hover:bg-slate-50">
                        <td class="py-2 px-3 border-r border-slate-100 font-mono font-semibold">${c.day_num}</td>
                        <td class="py-2 px-3 border-r border-slate-100">${c.shift}</td>
                        <td class="py-2 px-3 border-r border-slate-100">${c.milk_type}</td>
                        <td class="py-2 px-3 border-r border-slate-100 text-right font-mono font-bold">${parseFloat(c.quantity_liters).toFixed(2)}</td>
                        <td class="py-2 px-3 border-r border-slate-100 text-right font-mono text-emerald-700 font-bold">${parseFloat(c.fat).toFixed(1)}</td>
                        <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">${parseFloat(c.snf).toFixed(1)}</td>
                        <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">${parseFloat(c.clr).toFixed(1)}</td>
                        <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">${parseFloat(c.applied_rate).toFixed(2)}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">${parseFloat(c.net_amount).toFixed(2)}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        const t = currentReviewData.totals;
        tfoot.innerHTML = `
            <tr>
                <td class="py-2 px-3 border-r border-slate-200">Total</td>
                <td class="py-2 px-3 border-r border-slate-200">-</td>
                <td class="py-2 px-3 border-r border-slate-200">-</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono">${parseFloat(t.total_quantity).toFixed(2)}</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono">${parseFloat(t.avg_fat).toFixed(1)}</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono">${parseFloat(t.avg_snf).toFixed(1)}</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono">${parseFloat(t.avg_clr).toFixed(1)}</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono">${parseFloat(t.avg_rate).toFixed(2)}</td>
                <td class="py-2 px-3 text-right font-mono">${parseFloat(t.milk_amount).toFixed(2)}</td>
            </tr>
        `;
    }

    function renderReviewCattleFeedsTable() {
        if (!currentReviewData) return;
        const container = document.getElementById('revCattleFeedsContainer');
        document.getElementById('revFeedPaymentReceived').textContent = parseFloat(currentReviewData.feed_payment_received || 0).toFixed(2);
        document.getElementById('revFeedBalanceDue').textContent = parseFloat(currentReviewData.feed_remaining_balance || 0).toFixed(2);

        const feeds = currentReviewData.cattle_feeds || [];
        if (feeds.length === 0) {
            container.innerHTML = `<div class="py-6 text-center text-slate-500 font-semibold text-sm">No Records Found.</div>`;
            return;
        }

        let rows = '';
        feeds.forEach(f => {
            rows += `
                <tr class="border-b border-slate-100 text-left">
                    <td class="py-2 px-3 font-mono">${f.entry_date || '-'}</td>
                    <td class="py-2 px-3 font-semibold text-slate-800">${f.item_name}</td>
                    <td class="py-2 px-3 text-right font-mono">${parseFloat(f.quantity || 1).toFixed(2)} ${f.unit || ''}</td>
                    <td class="py-2 px-3 text-right font-mono">₹${parseFloat(f.amount || 0).toFixed(2)}</td>
                    <td class="py-2 px-3 text-right font-mono text-emerald-700">₹${parseFloat(f.paid_amount || 0).toFixed(2)}</td>
                    <td class="py-2 px-3 text-right font-mono font-bold text-rose-600">₹${parseFloat(f.remaining_amount || 0).toFixed(2)}</td>
                </tr>
            `;
        });

        container.innerHTML = `
            <div class="overflow-x-auto -m-4">
                <table class="w-full text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 text-[11px] text-left">
                        <tr>
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">Product / Item</th>
                            <th class="py-2 px-3 text-right">Qty</th>
                            <th class="py-2 px-3 text-right">Total</th>
                            <th class="py-2 px-3 text-right">Paid</th>
                            <th class="py-2 px-3 text-right">Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function recalcReviewSummary() {
        if (!currentReviewData) return;

        const milkAmount = parseFloat(currentReviewData.totals.milk_amount || 0);

        const otherCr = parseFloat(document.getElementById('revInputOtherCredit').value || 0);
        const transportCr = parseFloat(document.getElementById('revInputTransportCredit').value || 0);
        const subsidyCr = parseFloat(document.getElementById('revInputSubsidyCredit').value || 0);
        const incentiveCr = parseFloat(document.getElementById('revInputIncentiveCredit').value || 0);
        const totalAdditions = otherCr + transportCr + subsidyCr + incentiveCr;

        const feedDed = parseFloat(document.getElementById('revInputFeedDeduction').value || 0);
        const advDed = parseFloat(document.getElementById('revInputAdvanceDeduction').value || 0);
        const statDed = parseFloat(document.getElementById('revInputStationaryDeduction').value || 0);
        const totalDeductions = feedDed + advDed + statDed;

        const totalAmount = milkAmount + totalAdditions;
        const rawPayable = Math.max(0, totalAmount - totalDeductions);
        const roundedPayable = Math.round(rawPayable);
        const roundOff = roundedPayable - rawPayable;
        const remainingBalance = Math.max(0, totalDeductions - totalAmount);

        document.getElementById('revTotalAdditionsCell').textContent = totalAdditions.toFixed(2);
        document.getElementById('revTotalDeductionsCell').textContent = totalDeductions.toFixed(2);
        document.getElementById('revTotalBalanceCell').textContent = remainingBalance.toFixed(2);
        document.getElementById('revTotalDepositCell').textContent = parseFloat(currentReviewData.farmer.anamat || 0).toFixed(2);

        document.getElementById('revSummaryMilkAmount').textContent = milkAmount.toFixed(2);
        document.getElementById('revSummaryAdditions').textContent = totalAdditions.toFixed(2);
        document.getElementById('revSummaryTotalAmount').textContent = totalAmount.toFixed(2);
        document.getElementById('revSummaryTotalDeduction').textContent = totalDeductions.toFixed(2);
        document.getElementById('revSummaryRoundOff').textContent = (roundOff >= 0 ? '+' : '') + roundOff.toFixed(2);
        document.getElementById('revSummaryNetPayment').textContent = '₹' + roundedPayable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function saveAndVerifyCurrentInvoice() {
        if (!currentReviewData) return;

        const btn = document.getElementById('revSaveInvoiceBtn');
        const btnText = document.getElementById('revSaveBtnText');
        btn.disabled = true;
        btnText.textContent = 'Saving...';

        const payload = {
            other_credit: parseFloat(document.getElementById('revInputOtherCredit').value || 0),
            transport_credit: parseFloat(document.getElementById('revInputTransportCredit').value || 0),
            subsidy_credit: parseFloat(document.getElementById('revInputSubsidyCredit').value || 0),
            incentive_credit: parseFloat(document.getElementById('revInputIncentiveCredit').value || 0),
            feed_deduction: parseFloat(document.getElementById('revInputFeedDeduction').value || 0),
            advance_deduction: parseFloat(document.getElementById('revInputAdvanceDeduction').value || 0),
            stationary_deduction: parseFloat(document.getElementById('revInputStationaryDeduction').value || 0),
        };

        fetch(`/farmer-invoices/verify-save/${currentReviewData.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(resp => {
            btn.disabled = false;
            btnText.textContent = 'Save Invoice';
            hasSavedAnyInQueue = true;

            const toast = document.getElementById('revToast');
            if (reviewQueueIndex < reviewQueueIds.length - 1) {
                // Move automatically to the next invoice one-by-one!
                toast.textContent = `Saved #${currentReviewData.invoice_number}! Loading next (${reviewQueueIndex + 2}/${reviewQueueIds.length})...`;
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 1800);
                reviewQueueIndex++;
                loadCurrentReviewInvoice();
            } else {
                // All invoices in the selected queue have been verified & saved
                toast.textContent = `All ${reviewQueueIds.length} invoice(s) verified & moved to Generated state!`;
                toast.classList.remove('hidden');
                setTimeout(() => {
                    window.location.href = "{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'status_tab' => 'generated']) }}";
                }, 600);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btnText.textContent = 'Save Invoice';
            alert('Failed to save invoice. Please check values and try again.');
        });
    }

    // Print Bank Sheet for Selected Checkboxes
    function printSelectedBankSheet() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one invoice.');
            return;
        }
        const url = "{{ route('farmer-invoices.bank-payment-print') }}?selected_ids=" + encodeURIComponent(selected.join(',')) + "&start_date={{ $startDate }}&end_date={{ $endDate }}";
        window.open(url, '_blank');
    }

    // Bulk Approve Selected Pending Invoices
    function submitBulkApprove() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;

        const form = document.getElementById('bulkApproveForm');
        const inputsContainer = document.getElementById('bulkApproveInputs');
        inputsContainer.innerHTML = '';
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            inputsContainer.appendChild(input);
        });
        form.submit();
    }

    // Bulk Delete Action
    function submitBulkDelete() {
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return;
        if (!confirm('Are you sure you want to delete the ' + selected.length + ' selected invoices?')) return;

        const form = document.getElementById('bulkDeleteForm');
        const inputsContainer = document.getElementById('bulkDeleteInputs');
        inputsContainer.innerHTML = '';
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            inputsContainer.appendChild(input);
        });
        form.submit();
    }

    // ================= Quick In-Page Preview Modal =================
    function openInvoicePreviewModal(invoiceId) {
        currentPreviewInvoiceId = invoiceId;
        const modal = document.getElementById('invoicePreviewModal');
        const content = document.getElementById('previewModalContent');
        const title = document.getElementById('previewTitle');
        const subtitle = document.getElementById('previewSubtitle');

        modal.classList.remove('hidden');
        content.innerHTML = `
            <div class="flex items-center justify-center py-12 text-slate-400">
                <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-emerald-600 mr-2"></i>
                <span class="text-xs">Loading invoice details...</span>
            </div>
        `;
        if (window.lucide) window.lucide.createIcons();

        fetch(`/farmer-invoices/preview/${invoiceId}`)
            .then(res => res.json())
            .then(data => {
                title.textContent = `Invoice #${data.invoice_number} - ${data.farmer.name}`;
                subtitle.textContent = `Billing Period: ${data.period_start} to ${data.period_end}`;

                let collectionsRows = '';
                if (data.collections && data.collections.length > 0) {
                    data.collections.forEach(c => {
                        collectionsRows += `
                            <tr class="border-b border-slate-100 text-slate-700">
                                <td class="py-1.5 px-2 font-mono">${c.collection_date}</td>
                                <td class="py-1.5 px-2 capitalize">${c.shift || '-'}</td>
                                <td class="py-1.5 px-2 capitalize">${c.milk_type || '-'}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-semibold">${parseFloat(c.quantity_liters || 0).toFixed(2)}</td>
                                <td class="py-1.5 px-2 text-right font-mono">${parseFloat(c.fat || 0).toFixed(1)}</td>
                                <td class="py-1.5 px-2 text-right font-mono">${parseFloat(c.clr || 0).toFixed(1)} / ${parseFloat(c.snf || 0).toFixed(1)}</td>
                                <td class="py-1.5 px-2 text-right font-mono">₹${parseFloat(c.applied_rate || 0).toFixed(2)}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-bold text-slate-900">₹${parseFloat(c.net_amount || 0).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                } else {
                    collectionsRows = `<tr><td colspan="8" class="py-3 text-center text-slate-400 text-xs">No milk collection entries in this period</td></tr>`;
                }

                let deductionsRows = '';
                if (data.deductions && data.deductions.length > 0) {
                    data.deductions.forEach(d => {
                        const dDate = (d.entry_date || '-').toString().substring(0, 10);
                        const dRemark = d.comments || d.remarks || '-';
                        deductionsRows += `
                            <tr class="border-b border-slate-100 text-slate-700">
                                <td class="py-1.5 px-2 font-mono">${dDate}</td>
                                <td class="py-1.5 px-2 font-medium capitalize">${(d.deduction_type || 'Deduction').replace(/_/g, ' ')}</td>
                                <td class="py-1.5 px-2 text-slate-500">${dRemark}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-bold ${d.transaction_type === 'received' ? 'text-amber-700' : 'text-rose-700'}">
                                    ${d.transaction_type === 'received' ? '+ ₹' : '- ₹'}${parseFloat(d.amount || 0).toFixed(2)}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    deductionsRows = `<tr><td colspan="4" class="py-3 text-center text-slate-400 text-xs">No deductions or credits recorded</td></tr>`;
                }

                content.innerHTML = `
                    <div class="border border-slate-200 rounded-2xl p-4 sm:p-6 bg-white space-y-5 font-sans">
                        <div class="flex flex-wrap items-start justify-between pb-4 border-b border-slate-200 gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900 tracking-tight">DAIRY ERP BILLING</h2>
                                <p class="text-xs text-slate-500">Official Milk Procurement & Settlement Voucher</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded-lg">${data.invoice_number}</span>
                                <div class="text-[11px] text-slate-500 mt-1">Period: <span class="font-bold text-slate-700">${data.period_start}</span> to <span class="font-bold text-slate-700">${data.period_end}</span></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl text-xs border border-slate-100">
                            <div>
                                <div class="text-slate-400 font-bold uppercase text-[10px]">Farmer Information</div>
                                <div class="font-bold text-slate-900 text-sm mt-0.5">${data.farmer.name} ${data.farmer.name_hi ? ' (' + data.farmer.name_hi + ')' : ''}</div>
                                <div class="text-slate-600 mt-0.5">Code: <span class="font-bold font-mono text-slate-800">${data.farmer.code}</span> | Phone: ${data.farmer.phone || 'N/A'}</div>
                            </div>
                            <div>
                                <div class="text-slate-400 font-bold uppercase text-[10px]">Bank Transfer Details</div>
                                <div class="text-slate-700 mt-0.5">Bank: <span class="font-semibold text-slate-900">${data.farmer.bank_name || 'N/A'}</span></div>
                                <div class="text-slate-700">A/C No: <span class="font-mono font-bold text-slate-900">${data.farmer.account_number || 'N/A'}</span></div>
                                <div class="text-slate-700">IFSC: <span class="font-mono font-semibold text-slate-900">${data.farmer.ifsc_code || 'N/A'}</span></div>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                                <i data-lucide="milk" class="w-4 h-4 text-sky-600"></i>
                                <span>Milk Collections (${data.collections ? data.collections.length : 0} Entries)</span>
                            </div>
                            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                        <tr>
                                            <th class="py-2 px-2">Date</th>
                                            <th class="py-2 px-2">Shift</th>
                                            <th class="py-2 px-2">Milk</th>
                                            <th class="py-2 px-2 text-right">Liter</th>
                                            <th class="py-2 px-2 text-right">FAT %</th>
                                            <th class="py-2 px-2 text-right">CLR/SNF</th>
                                            <th class="py-2 px-2 text-right">Rate</th>
                                            <th class="py-2 px-2 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>${collectionsRows}</tbody>
                                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                        <tr>
                                            <td colspan="3" class="py-2 px-2">Total Milk</td>
                                            <td class="py-2 px-2 text-right font-mono font-black text-sky-900">${data.totals.total_quantity.toFixed(2)} L</td>
                                            <td colspan="3"></td>
                                            <td class="py-2 px-2 text-right font-mono font-black text-slate-900">₹${data.totals.milk_amount.toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                                <i data-lucide="minus-circle" class="w-4 h-4 text-rose-600"></i>
                                <span>Deductions & Deposits</span>
                            </div>
                            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200 text-[11px]">
                                        <tr>
                                            <th class="py-2 px-2">Date</th>
                                            <th class="py-2 px-2">Type</th>
                                            <th class="py-2 px-2">Remarks / Details</th>
                                            <th class="py-2 px-2 text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>${deductionsRows}</tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 text-xs">
                            <div class="p-2 bg-white rounded-xl border border-emerald-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Milk Amount (C)</span>
                                <div class="text-sm font-black text-slate-900 font-mono mt-0.5">₹${data.totals.milk_amount.toFixed(2)}</div>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-emerald-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Total Credit (B)</span>
                                <div class="text-sm font-black text-amber-700 font-mono mt-0.5">+ ₹${data.totals.total_credit.toFixed(2)}</div>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-emerald-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Deductions (A)</span>
                                <div class="text-sm font-black text-rose-700 font-mono mt-0.5">- ₹${data.totals.total_deduction.toFixed(2)}</div>
                            </div>
                            <div class="p-2 bg-emerald-600 text-white rounded-xl shadow-xs">
                                <span class="text-[10px] font-bold uppercase opacity-90">Net Payment</span>
                                <div class="text-base font-black font-mono mt-0.5">₹${data.totals.net_payment.toFixed(2)}</div>
                            </div>
                        </div>
                    </div>
                `;

                if (window.lucide) window.lucide.createIcons();
            });
    }

    function closeInvoicePreviewModal() {
        document.getElementById('invoicePreviewModal').classList.add('hidden');
    }

    function printFromPreviewModal() {
        if (currentPreviewInvoiceId) {
            window.open(`/farmer-invoices/print/${currentPreviewInvoiceId}`, '_blank');
        }
    }
</script>
@endpush
