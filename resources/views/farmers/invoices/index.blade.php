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
        <button type="button" onclick="openGenerateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Generate Invoice</span>
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

    <!-- Top Filter Bar matching Reference Screenshot 1 -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('farmer-invoices.index') }}" class="flex flex-wrap items-end gap-3.5">
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
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('farmer-invoices.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>

            <!-- Mobile Quick Buttons -->
            <div class="flex flex-wrap items-center gap-2 sm:hidden w-full pt-2 border-t border-slate-100">
                <button type="button" onclick="openGenerateModal()" class="flex-1 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center">
                    + Generate Invoice
                </button>
                <a href="{{ route('farmer-invoices.bank-payment-print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="py-2 px-3 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl">
                    Bank Print
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Selection Action Bar (Shown when any invoice is checked) -->
    <div id="bulkActionBar" class="hidden bg-slate-900 text-white p-3.5 px-5 rounded-2xl shadow-lg flex flex-wrap items-center justify-between gap-4 transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center" id="selectedCountBadge">0</span>
            <span class="text-xs font-medium text-slate-200"><span id="selectedCountText">0</span> invoices selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="printSelectedBankSheet()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Bank Payment Print</span>
            </button>
            <button type="button" onclick="submitBulkDelete()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete Selected</span>
            </button>
            <button type="button" onclick="deselectAll()" class="px-2.5 py-1.5 text-xs text-slate-400 hover:text-white transition">
                Deselect
            </button>
        </div>
    </div>

    <!-- Farmer Invoices Grouped Table Card matching Reference Screenshot 1 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Invoices List</h3>
                <p class="text-[11px] text-slate-500">Period: <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</span> to <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span> ({{ $invoices->total() }} Total)</p>
            </div>
            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <span>Showing {{ $invoices->firstItem() ?? 0 }}-{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <!-- Top Tier Header (Grouped Headers matching Screenshot 1) -->
                    <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3 text-center border-r border-slate-200 w-10">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        </th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-slate-100">Farmer Info</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center bg-sky-50 text-sky-900">Milks</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-slate-200 text-center bg-rose-50 text-rose-900">Deduction</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center bg-amber-50 text-amber-900">Credit</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-slate-200 text-center bg-emerald-50 text-emerald-900">Payment</th>
                        <th class="py-2.5 px-3 text-center">Actions</th>
                    </tr>

                    <!-- Sub Tier Header matching Screenshot 1 exactly -->
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                        <th class="py-2.5 px-3 text-center border-r border-slate-100">#</th>
                        <th class="py-2.5 px-3 border-r border-slate-100">Code</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 min-w-[140px]">Name</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-sky-50/50">Quantity (L)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">Stationary</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">Feed</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-50/30">In Hand Advance</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-rose-100/50 font-bold text-rose-800">Total Deduction (A)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/50 font-bold text-amber-800">Total Credit (B)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30">Milk Amount (C)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30 font-semibold text-slate-700">Total Amount (B+C)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-slate-50">Balance</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-100/60 font-bold text-emerald-900">Payment (B+C-A)</th>
                        <th class="py-2.5 px-3 text-center min-w-[120px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($invoices as $index => $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors group" id="row-{{ $inv->id }}">
                            <td class="py-3 px-3 text-center border-r border-slate-100">
                                <input type="checkbox" value="{{ $inv->id }}" onchange="handleRowSelect()" class="row-checkbox w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 font-mono text-[11px] font-bold text-slate-800">
                                {{ $inv->farmer->farmer_code ?? '-' }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100">
                                <div class="font-bold text-slate-900 leading-tight">{{ $inv->farmer->name ?? 'Unknown Farmer' }}</div>
                                @if(!empty($inv->farmer->phone))
                                    <div class="text-[10px] text-slate-400">{{ $inv->farmer->phone }}</div>
                                @endif
                                <div class="text-[9px] text-slate-400 font-mono">{{ $inv->invoice_number }}</div>
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
                                    <!-- In-page Quick Preview Modal button -->
                                    <button type="button" onclick="openInvoicePreviewModal({{ $inv->id }})" title="Quick Bill Preview" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Full Invoice Details Page -->
                                    <a href="{{ route('farmer-invoices.show', $inv->id) }}" title="View Invoice Details" class="p-1.5 text-slate-500 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Direct Print Bill -->
                                    <a href="{{ route('farmer-invoices.print', $inv->id) }}" target="_blank" title="Print Bill" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('farmer-invoices.destroy', $inv->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete invoice #{{ $inv->invoice_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Invoice" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="file-question" class="w-8 h-8 text-slate-300"></i>
                                    <p class="text-xs font-semibold text-slate-500">No invoices found for the selected period.</p>
                                    <button type="button" onclick="openGenerateModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Generate Now</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Table Footer Totals Row matching Screenshot 1 -->
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
                    <p class="text-xs text-slate-500">Calculate milk, deductions & generate period bills</p>
                </div>
            </div>
            <button type="button" onclick="closeGenerateModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
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
                <p class="text-[11px] text-slate-400 mt-1">Leave as "All Active Farmers" to calculate invoices for every farmer with milk collections in this period.</p>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/80 text-[11px] text-amber-800 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    <span>Automatic Calculation Breakdown:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-amber-900/90 pl-1">
                    <li>Milk Collections sum quantity and amount (C)</li>
                    <li>Cattle Feeds, Stationary & In-Hand Advances sum deductions (A)</li>
                    <li>Credits & deposits sum into Total Credit (B)</li>
                    <li>Net Payment = (Milk Amount C + Credit B) - Total Deductions A</li>
                </ul>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeGenerateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Generate Invoices Now</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: IN-PAGE INVOICE PREVIEW MODAL matching Screenshot 2 & 3 ================= -->
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
                <button type="button" id="previewPrintBtn" onclick="printFromPreviewModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Bill</span>
                </button>
                <button type="button" onclick="closeInvoicePreviewModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Bill Content Body matching media_1791388722646.png / media_1791388740424.png -->
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
                <button type="button" onclick="closeInvoicePreviewModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    Close
                </button>
                <button type="button" onclick="printFromPreviewModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
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

@endsection

@push('scripts')
<script>
    let currentPreviewInvoiceId = null;

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
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        const count = selected.length;
        const bar = document.getElementById('bulkActionBar');
        const countBadge = document.getElementById('selectedCountBadge');
        const countText = document.getElementById('selectedCountText');

        if (count > 0) {
            bar.classList.remove('hidden');
            countBadge.textContent = count;
            countText.textContent = count;
        } else {
            bar.classList.add('hidden');
        }
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

                // Build collections rows
                let collectionsRows = '';
                if (data.collections && data.collections.length > 0) {
                    data.collections.forEach(c => {
                        collectionsRows += `
                            <tr class="border-b border-slate-100 text-slate-700">
                                <td class="py-1.5 px-2">${c.collection_date || '-'}</td>
                                <td class="py-1.5 px-2 capitalize">${c.shift || '-'}</td>
                                <td class="py-1.5 px-2 capitalize">${c.milk_type || '-'}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-semibold">${parseFloat(c.quantity_liters).toFixed(2)}</td>
                                <td class="py-1.5 px-2 text-right font-mono">${parseFloat(c.fat_percentage).toFixed(1)}</td>
                                <td class="py-1.5 px-2 text-right font-mono">${c.clr_reading ? parseFloat(c.clr_reading).toFixed(1) : (c.snf_percentage ? parseFloat(c.snf_percentage).toFixed(1) : '-')}</td>
                                <td class="py-1.5 px-2 text-right font-mono">₹${parseFloat(c.rate_per_liter).toFixed(2)}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-bold text-slate-900">₹${parseFloat(c.net_amount).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                } else {
                    collectionsRows = `<tr><td colspan="8" class="py-3 text-center text-slate-400 text-xs">No milk collection entries in this period</td></tr>`;
                }

                // Build deductions rows
                let deductionsRows = '';
                if (data.deductions && data.deductions.length > 0) {
                    data.deductions.forEach(d => {
                        deductionsRows += `
                            <tr class="border-b border-slate-100 text-slate-700">
                                <td class="py-1.5 px-2">${d.entry_date || '-'}</td>
                                <td class="py-1.5 px-2 font-medium capitalize">${(d.deduction_type || 'Deduction').replace('_', ' ')}</td>
                                <td class="py-1.5 px-2 text-slate-500">${d.remarks || '-'}</td>
                                <td class="py-1.5 px-2 text-right font-mono font-bold ${d.transaction_type === 'received' ? 'text-amber-700' : 'text-rose-700'}">
                                    ${d.transaction_type === 'received' ? '+ ₹' : '- ₹'}${parseFloat(d.amount).toFixed(2)}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    deductionsRows = `<tr><td colspan="4" class="py-3 text-center text-slate-400 text-xs">No deductions or credits recorded</td></tr>`;
                }

                content.innerHTML = `
                    <div class="border border-slate-200 rounded-2xl p-4 sm:p-6 bg-white space-y-5 font-sans">
                        <!-- Invoice Header matching Screenshot 2/3 -->
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

                        <!-- Farmer & Bank Info Banner -->
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

                        <!-- Milk Collections Section -->
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
                                    <tbody>
                                        ${collectionsRows}
                                    </tbody>
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

                        <!-- Deductions Section -->
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
                                    <tbody>
                                        ${deductionsRows}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Bill Summary Box matching media_1791388722646.png -->
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
            })
            .catch(err => {
                content.innerHTML = `
                    <div class="p-8 text-center text-rose-500">
                        <i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-2"></i>
                        <p class="text-xs font-bold">Failed to load invoice preview. Please try again.</p>
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
