@extends('layouts.app')

@section('title', 'Buyer Invoices & Customer Bill')
@section('breadcrumb', 'Customer Bill')
@section('header_title', 'Buyer Invoices & Customer Billing')

@section('header_action')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('buyers.khata') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="book-open" class="w-4 h-4 text-emerald-600"></i>
            <span>Buyer Khata</span>
        </a>
        <a href="{{ route('milk-sales.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-600"></i>
            <span>Milk Sales</span>
        </a>
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
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
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
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <form method="GET" action="{{ route('buyer-invoices.index') }}" id="filterForm" class="flex flex-wrap items-end gap-3.5">
            <input type="hidden" name="status_tab" value="{{ $statusTab }}">

            <!-- 10-day Cycle Presets Dropdown -->
            <div class="min-w-[220px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Billing Cycle</label>
                <select name="cycle_range" onchange="document.getElementById('filterForm').submit()" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-semibold text-slate-800">
                    @foreach($cycles as $key => $label)
                        <option value="{{ $key }}" {{ $activeCycleKey === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
            </div>

            <div class="min-w-[200px] flex-1 sm:flex-initial">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search Buyer</label>
                <div class="relative">
                    <input type="text" name="buyer_search" value="{{ $buyerSearch }}" placeholder="Name, Code, Phone..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Invoice / Bill #</label>
                <div class="relative">
                    <input type="text" name="invoice_search" value="{{ $invoiceSearch }}" placeholder="BINV-0101..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="hash" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px] cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('buyer-invoices.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Status Workflow Tabs: Pending Invoices vs Generated Invoices -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('buyer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'buyer_search' => $buyerSearch, 'invoice_search' => $invoiceSearch, 'status_tab' => 'pending']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $statusTab === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <span>Pending Invoices (Review & Verify)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $statusTab === 'pending' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $pendingCount }}
                </span>
            </a>

            <a href="{{ route('buyer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate, 'buyer_search' => $buyerSearch, 'invoice_search' => $invoiceSearch, 'status_tab' => 'generated']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $statusTab === 'generated' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>Generated Invoices (Finalized)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $statusTab === 'generated' ? 'bg-white text-emerald-700' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $generatedCount }}
                </span>
            </a>
        </div>

        <!-- Quick Review All Button when invoices exist (Pending tab only) -->
        @if(count($allTabIds) > 0 && $statusTab === 'pending')
            <div class="flex items-center gap-2">
                <button type="button" onclick="openReviewAllInTab()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Review & Verify All Pending (1 / {{ count($allTabIds) }})</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Bulk Selection Action Bar -->
    <div id="bulkActionBar" class="hidden bg-slate-900 text-white p-3.5 px-5 rounded-2xl shadow-lg flex flex-wrap items-center justify-between gap-4 transition-all duration-200">
        <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center" id="selectedCountBadge">0</span>
            <span class="text-xs font-semibold text-slate-200">Selected: <b id="selectedCountText" class="text-white">0</b></span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($statusTab === 'pending')
                <button type="button" onclick="openReviewSelected()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-xs">
                    <i data-lucide="file-edit" class="w-3.5 h-3.5"></i>
                    <span>Review Selected (<span id="selectedReviewBtnCount">0</span>) One-by-One</span>
                </button>

                <button type="button" onclick="submitBulkApprove()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                    <span>Approve & Generate Selected</span>
                </button>
            @endif

            <button type="button" onclick="submitBulkDelete()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                <i data-lucide="{{ $statusTab === 'pending' ? 'trash-2' : 'undo-2' }}" class="w-3.5 h-3.5"></i>
                <span>{{ $statusTab === 'pending' ? 'Delete Selected' : 'Delete (Revert to Pending) Selected' }}</span>
            </button>
            <button type="button" onclick="deselectAll()" class="px-2.5 py-1.5 text-xs text-slate-400 hover:text-white transition cursor-pointer">
                Deselect
            </button>
        </div>
    </div>

    <!-- Main Buyer Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        @if($statusTab === 'pending')
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>List of Projected / Pending Buyer Invoices ({{ $pendingCount }})</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>List of Generated Buyer Invoices ({{ $generatedCount }})</span>
                        @endif
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Date Range: <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                    </p>
                </div>
            </div>

            <div class="text-xs font-bold text-slate-600">
                (Buyer Invoices Selected: <span id="headerSelectedCounter" class="text-emerald-700">0</span>)
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
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-slate-100">Buyer Info</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center bg-sky-50 text-sky-900">Milks</th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-emerald-50 text-emerald-900">Sales (Debit)</th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-amber-50 text-amber-900">Paid / Received (Credit)</th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-slate-200 text-center bg-indigo-50 text-indigo-900">Outstanding Balance</th>
                        <th class="py-2.5 px-3 text-center">Actions</th>
                    </tr>

                    <!-- Sub Tier Header -->
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                        <th class="py-2.5 px-3 text-center border-r border-slate-100">#</th>
                        <th class="py-2.5 px-3 border-r border-slate-100">Code</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 min-w-[140px]">Buyer Name</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-sky-50/50">Quantity (L)</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30">Milk Amount</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-emerald-50/30">Other Charges</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/30">Cash Received</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-amber-50/30">Discount</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-slate-50">Opening Due</th>
                        <th class="py-2.5 px-3 border-r border-slate-100 text-right bg-indigo-100/60 font-bold text-indigo-900">Net Due (Receivable)</th>
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
                                {{ $inv->buyer->buyer_code ?? '-' }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100">
                                <div class="font-bold text-slate-900 leading-tight uppercase">{{ $inv->buyer->name ?? 'Unknown Buyer' }}</div>
                                @if(!empty($inv->buyer->phone))
                                    <div class="text-[10px] text-slate-400">{{ $inv->buyer->phone }}</div>
                                @endif
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="font-mono text-[10px] text-slate-500 font-bold">#{{ $inv->invoice_number }}</span>
                                    @if($inv->status === 'pending')
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Generated</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono font-bold text-sky-800 bg-sky-50/20">
                                {{ number_format($inv->total_quantity, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-emerald-700 font-semibold bg-emerald-50/10">
                                ₹{{ number_format($inv->milk_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-600">
                                ₹{{ number_format($inv->other_charges, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-amber-700 font-semibold bg-amber-50/10">
                                ₹{{ number_format($inv->cash_received, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-500">
                                ₹{{ number_format($inv->discount_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono text-slate-700 font-medium">
                                ₹{{ number_format($inv->previous_balance, 2) }}
                            </td>
                            <td class="py-3 px-3 border-r border-slate-100 text-right font-mono font-bold text-indigo-900 bg-indigo-50/40">
                                ₹{{ number_format($inv->net_receivable, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($statusTab === 'pending')
                                        <!-- Step-by-Step Verify Button -->
                                        <button type="button" onclick="openReviewModalForSingle({{ $inv->id }})"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold rounded-lg transition shadow-2xs cursor-pointer flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>Verify</span>
                                        </button>
                                    @endif

                                    <!-- Quick Preview Modal Button -->
                                    <button type="button" onclick="openQuickPreviewModal({{ $inv->id }})"
                                            title="Quick View"
                                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Printable Bill Slip -->
                                    <a href="{{ route('buyer-invoices.print', $inv->id) }}" target="_blank"
                                       title="Print Slip"
                                       class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>

                                    @if($statusTab === 'generated')
                                        <!-- Revert to Pending Action (Amber Undo Button) -->
                                        <form method="POST" action="{{ route('buyer-invoices.destroy', $inv->id) }}" onsubmit="return confirm('क्या आप इस जनरेटेड इनवॉइस को वापस Pending (Review & Verify) में भेजना चाहते हैं?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Revert to Pending" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition cursor-pointer">
                                                <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Delete Pending Draft -->
                                        <form method="POST" action="{{ route('buyer-invoices.destroy', $inv->id) }}" onsubmit="return confirm('Delete this pending draft invoice?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Draft" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition cursor-pointer">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <i data-lucide="receipt" class="w-6 h-6"></i>
                                    </div>
                                    <div class="text-xs font-bold text-slate-700">No {{ $statusTab === 'pending' ? 'Pending' : 'Generated' }} Buyer Invoices Found</div>
                                    <p class="text-[11px] text-slate-400">Click "Generate Invoice" above to create projected bills for buyers with milk sales.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($invoices->count() > 0)
                    <tfoot class="bg-slate-100 text-slate-900 font-extrabold border-t-2 border-slate-300 text-xs">
                        <tr>
                            <td colspan="3" class="py-3 px-3 text-right uppercase tracking-wider text-[11px] border-r border-slate-200">
                                Total (All Filtered):
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-sky-900 border-r border-slate-200 bg-sky-100/50">
                                {{ number_format($totalQuantity, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-emerald-900 border-r border-slate-200 bg-emerald-100/40">
                                ₹{{ number_format($totalMilkAmount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700 border-r border-slate-200">
                                ₹0.00
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-amber-900 border-r border-slate-200 bg-amber-100/40">
                                ₹{{ number_format($totalCashReceived, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-600 border-r border-slate-200">
                                ₹{{ number_format($totalDiscount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-800 border-r border-slate-200">
                                ₹{{ number_format($totalPreviousBalance, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-indigo-950 border-r border-slate-200 bg-indigo-100/60 font-black">
                                ₹{{ number_format($totalNetReceivable, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100 flex items-center justify-between">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ================= MODAL 1: GENERATE INVOICES MODAL ================= -->
<div id="generateModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-emerald-600"></i>
                <span>Generate Projected Buyer Invoices</span>
            </h3>
            <button type="button" onclick="closeGenerateModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('buyer-invoices.generate') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Billing Cycle Range :</label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-[10px] text-slate-400">Start Date</span>
                        <input type="date" name="start_date" value="{{ $startDate }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 outline-none focus:bg-white focus:border-emerald-500">
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400">End Date</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 outline-none focus:bg-white focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Buyer :</label>
                <select name="buyer_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 outline-none focus:bg-white focus:border-emerald-500 font-medium">
                    <option value="">All Buyers (With Milk Sales in Period)</option>
                    @foreach($buyers as $b)
                        <option value="{{ $b->id }}">{{ $b->buyer_code }} - {{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] leading-relaxed">
                <p><b>Note:</b> Draft pending invoices will be generated for review. No final bills are locked until you verify and save them.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeGenerateModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Generate Pending Invoices</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: 1-BY-1 REVIEW, EDIT & SAVE QUEUE MODAL ================= -->
<div id="buyerReviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="bg-slate-50 rounded-2xl max-w-6xl w-full shadow-2xl border border-slate-200 max-h-[95vh] flex flex-col overflow-hidden">

        <!-- Top Header & Stepper -->
        <div class="bg-white px-5 py-3.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeBuyerReviewModal()" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-emerald-700 transition cursor-pointer">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back</span>
                </button>
                <span class="text-slate-300">|</span>
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-slate-900" id="bRevHeaderName">
                        Buyer : —
                    </div>
                    <div class="text-[11px] font-semibold text-slate-500" id="bRevHeaderDate">
                        Period : —
                    </div>
                </div>
            </div>

            <!-- Stepper: < Invoices ( 1 / N ) > -->
            <div class="flex items-center gap-3">
                <div id="bRevToast" class="hidden px-3 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                    Saved!
                </div>
                <div class="inline-flex items-center gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button type="button" id="bRevPrevBtn" onclick="stepBuyerReviewQueue(-1)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 text-xs font-bold cursor-pointer">
                        &lsaquo;
                    </button>
                    <span class="px-2 text-xs font-extrabold text-slate-800" id="bRevCounter">
                        Invoices ( 1 / 1 )
                    </span>
                    <button type="button" id="bRevNextBtn" onclick="stepBuyerReviewQueue(1)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40 text-xs font-bold cursor-pointer">
                        &rsaquo;
                    </button>
                </div>
                <button type="button" onclick="closeBuyerReviewModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- 2-Column Body -->
        <div class="p-4 sm:p-5 overflow-y-auto flex-1 grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- LEFT 7 COLUMNS: Daily Milk Sales Breakdown & Payments List -->
            <div class="lg:col-span-7 space-y-4">

                <!-- 1. Daily Milk Sales Table -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
                        <span class="text-xs font-extrabold text-slate-800">Milk Sales In Period</span>
                        <span class="text-[11px] font-bold text-slate-600" id="bRevSalesCountText">Total Records: 0</span>
                    </div>

                    <div class="overflow-x-auto max-h-[300px]">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 text-[11px] sticky top-0">
                                <tr>
                                    <th class="py-2 px-3 border-r border-slate-100">Date</th>
                                    <th class="py-2 px-3 border-r border-slate-100">Shift</th>
                                    <th class="py-2 px-3 border-r border-slate-100">Milk</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">Liters</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">FAT</th>
                                    <th class="py-2 px-3 border-r border-slate-100 text-right">Rate</th>
                                    <th class="py-2 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="bRevSalesTbody" class="divide-y divide-slate-100 text-slate-700">
                                <tr><td colspan="7" class="py-6 text-center text-slate-400">Loading...</td></tr>
                            </tbody>
                            <tfoot id="bRevSalesTfoot" class="bg-slate-50 font-extrabold text-slate-900 border-t border-slate-200 text-xs sticky bottom-0">
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 2. Received Payments in Period -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-4 py-2.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
                        <span class="text-xs font-extrabold text-slate-800">Payments / Cash Received In Period</span>
                        <span class="text-[11px] font-bold text-amber-700" id="bRevPaymentsTotalText">Total: ₹0.00</span>
                    </div>
                    <div id="bRevPaymentsContainer" class="p-3 text-center text-slate-500 text-xs">
                        No separate payments found.
                    </div>
                </div>

            </div>

            <!-- RIGHT 5 COLUMNS: Adjustments & Net Receivable Summary -->
            <div class="lg:col-span-5 space-y-4">

                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 space-y-3">
                    <div class="text-xs font-extrabold text-slate-800 border-b border-slate-100 pb-2 flex items-center justify-between">
                        <span>Invoice Adjustments</span>
                        <span class="text-[10px] text-slate-400">Review & Correct</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Previous Balance (Opening Due)</label>
                        <input type="number" step="0.01" min="0" id="bRevInputPrevBal" oninput="recalcBuyerSummary()"
                               class="w-full text-right px-3 py-1.5 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Other Charges (Delivery / Packing)</label>
                        <input type="number" step="0.01" min="0" id="bRevInputOtherCharges" oninput="recalcBuyerSummary()"
                               class="w-full text-right px-3 py-1.5 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Cash Received During Period</label>
                        <input type="number" step="0.01" min="0" id="bRevInputCashReceived" oninput="recalcBuyerSummary()"
                               class="w-full text-right px-3 py-1.5 text-xs font-mono font-bold text-amber-800 bg-amber-50/40 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Discount Amount</label>
                        <input type="number" step="0.01" min="0" id="bRevInputDiscount" oninput="recalcBuyerSummary()"
                               class="w-full text-right px-3 py-1.5 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Final Net Payable / Due Card -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 space-y-3">
                    <div class="text-xs font-extrabold text-slate-800 border-b border-slate-100 pb-2">
                        Billing Summary
                    </div>

                    <div class="space-y-1.5 text-xs font-medium text-slate-600">
                        <div class="flex items-center justify-between">
                            <span>Milk Sales Amount (A):</span>
                            <span class="font-mono font-bold text-slate-900" id="bRevSumMilkAmount">₹0.00</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Previous Balance (B):</span>
                            <span class="font-mono font-bold text-slate-700" id="bRevSumPrevBal">₹0.00</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Other Charges (C):</span>
                            <span class="font-mono font-bold text-slate-700" id="bRevSumOtherCharges">₹0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-amber-700">
                            <span>Less: Cash Received (D):</span>
                            <span class="font-mono font-bold" id="bRevSumCashReceived">- ₹0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Less: Discount (E):</span>
                            <span class="font-mono font-bold" id="bRevSumDiscount">- ₹0.00</span>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-sm font-black text-indigo-950 bg-indigo-50/50 p-2 rounded-lg">
                            <span>Net Due (Receivable):</span>
                            <span class="font-mono text-base text-indigo-700" id="bRevSumNetReceivable">₹0.00</span>
                        </div>
                    </div>

                    <!-- Direct Action Buttons matching Farmer Invoice -->
                    <div class="pt-2 space-y-2">
                        <button type="button" onclick="saveAndVerifyBuyerInvoice()" id="bRevSaveBtn"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Save Invoice & Finalize</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- ================= MODAL 3: QUICK VIEW MODAL ================= -->
<div id="quickPreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900" id="qpTitle">Buyer Invoice Details</h3>
                <p class="text-xs text-slate-500" id="qpSubtitle">—</p>
            </div>
            <button type="button" onclick="closeQuickPreviewModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div id="qpBody" class="space-y-4 text-xs">
            <!-- Dynamic Content -->
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeQuickPreviewModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Bulk Action Hidden Forms -->
<form id="bulkApproveForm" method="POST" action="{{ route('buyer-invoices.bulk-approve') }}" class="hidden">
    @csrf
    <div id="bulkApproveInputs"></div>
</form>

<form id="bulkDeleteForm" method="POST" action="{{ route('buyer-invoices.bulk-delete') }}" class="hidden">
    @csrf
    <div id="bulkDeleteInputs"></div>
</form>

@endsection

@push('scripts')
<script>
    // Global Tab Invoice IDs for 1-by-1 Queue Stepper
    let allTabIds = @json($allTabIds);
    let currentReviewQueue = [];
    let currentQueueIndex = 0;
    let activeInvoiceData = null;

    // Checkbox and Bulk Action Logic
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    }

    function handleRowSelect() {
        const selected = getSelectedIds();
        const count = selected.length;
        const bar = document.getElementById('bulkActionBar');
        const countBadge = document.getElementById('selectedCountBadge');
        const countText = document.getElementById('selectedCountText');
        const btnCount = document.getElementById('selectedReviewBtnCount');
        const headerCount = document.getElementById('headerSelectedCounter');

        if (headerCount) headerCount.innerText = count;

        if (count > 0) {
            bar.classList.remove('hidden');
            if (countBadge) countBadge.innerText = count;
            if (countText) countText.innerText = count;
            if (btnCount) btnCount.innerText = count;
        } else {
            bar.classList.add('hidden');
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) selectAll.checked = false;
        }
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
        handleRowSelect();
    }

    function deselectAll() {
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        handleRowSelect();
    }

    function submitBulkApprove() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        if (!confirm(`Are you sure you want to approve & finalize ${ids.length} selected invoices?`)) return;

        const container = document.getElementById('bulkApproveInputs');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('bulkApproveForm').submit();
    }

    function submitBulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        const isPending = '{{ $statusTab }}' === 'pending';
        const msg = isPending
            ? `Delete ${ids.length} selected pending draft invoices?`
            : `Revert ${ids.length} selected generated invoices back to Pending (Review & Verify)?`;

        if (!confirm(msg)) return;

        const container = document.getElementById('bulkDeleteInputs');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('bulkDeleteForm').submit();
    }

    // Modal 1: Generate Modal
    function openGenerateModal() {
        document.getElementById('generateModal').classList.remove('hidden');
    }
    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
    }

    // Modal 2: 1-by-1 Review Queue Modal
    function openReviewAllInTab() {
        if (!allTabIds || allTabIds.length === 0) return;
        currentReviewQueue = [...allTabIds];
        currentQueueIndex = 0;
        openReviewModalAtIndex(0);
    }

    function openReviewSelected() {
        const ids = getSelectedIds().map(x => parseInt(x));
        if (ids.length === 0) return;
        currentReviewQueue = ids;
        currentQueueIndex = 0;
        openReviewModalAtIndex(0);
    }

    function openReviewModalForSingle(id) {
        currentReviewQueue = [parseInt(id)];
        currentQueueIndex = 0;
        openReviewModalAtIndex(0);
    }

    function openReviewModalAtIndex(index) {
        if (index < 0 || index >= currentReviewQueue.length) return;
        currentQueueIndex = index;
        const invoiceId = currentReviewQueue[index];

        document.getElementById('buyerReviewModal').classList.remove('hidden');
        loadInvoiceReviewData(invoiceId);
    }

    function stepBuyerReviewQueue(delta) {
        const newIndex = currentQueueIndex + delta;
        if (newIndex >= 0 && newIndex < currentReviewQueue.length) {
            openReviewModalAtIndex(newIndex);
        }
    }

    function closeBuyerReviewModal() {
        document.getElementById('buyerReviewModal').classList.add('hidden');
        activeInvoiceData = null;
    }

    async function loadInvoiceReviewData(invoiceId) {
        // Update Stepper Counter
        const totalInQueue = currentReviewQueue.length;
        document.getElementById('bRevCounter').innerText = `Invoices ( ${currentQueueIndex + 1} / ${totalInQueue} )`;
        document.getElementById('bRevPrevBtn').disabled = (currentQueueIndex <= 0);
        document.getElementById('bRevNextBtn').disabled = (currentQueueIndex >= totalInQueue - 1);

        // Clear tables
        document.getElementById('bRevSalesTbody').innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">Loading invoice data...</td></tr>';
        document.getElementById('bRevPaymentsContainer').innerHTML = 'Loading...';

        try {
            const res = await fetch(`{{ url('buyer-invoices/preview') }}/${invoiceId}`);
            if (!res.ok) throw new Error('Failed to load invoice');
            const data = await res.json();
            activeInvoiceData = data;

            // Header info
            document.getElementById('bRevHeaderName').innerText = `Buyer : ${data.buyer.code} - ${data.buyer.name} (${data.buyer.phone || ''})`;
            document.getElementById('bRevHeaderDate').innerText = `Period : ${data.period_start_display} to ${data.period_end_display} | Bill #${data.invoice_number}`;

            // Render Sales Table
            renderSalesTable(data.sales || []);

            // Render Payments
            renderPayments(data.payments || []);

            // Fill Adjustments Inputs
            document.getElementById('bRevInputPrevBal').value = parseFloat(data.totals.previous_balance || 0).toFixed(2);
            document.getElementById('bRevInputOtherCharges').value = parseFloat(data.totals.other_charges || 0).toFixed(2);
            document.getElementById('bRevInputCashReceived').value = parseFloat(data.totals.cash_received || 0).toFixed(2);
            document.getElementById('bRevInputDiscount').value = parseFloat(data.totals.discount_amount || 0).toFixed(2);

            recalcBuyerSummary();
            if (window.lucide) lucide.createIcons();

        } catch (e) {
            console.error(e);
            alert('Error loading invoice details.');
        }
    }

    function renderSalesTable(sales) {
        const tbody = document.getElementById('bRevSalesTbody');
        const tfoot = document.getElementById('bRevSalesTfoot');
        document.getElementById('bRevSalesCountText').innerText = `Total Records: ${sales.length}`;

        if (sales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-400">No milk sales entries in this period.</td></tr>';
            tfoot.innerHTML = '';
            return;
        }

        let totalLiters = 0;
        let totalAmount = 0;
        let rowsHtml = '';

        sales.forEach(s => {
            totalLiters += parseFloat(s.quantity_liters);
            totalAmount += parseFloat(s.total_amount);

            rowsHtml += `
                <tr class="hover:bg-slate-50/70">
                    <td class="py-2 px-3 border-r border-slate-100 font-medium">${s.date_display}</td>
                    <td class="py-2 px-3 border-r border-slate-100"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${s.shift === 'Morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'}">${s.shift}</span></td>
                    <td class="py-2 px-3 border-r border-slate-100 font-semibold">${s.milk_type}</td>
                    <td class="py-2 px-3 border-r border-slate-100 text-right font-mono font-bold text-sky-800">${parseFloat(s.quantity_liters).toFixed(2)}</td>
                    <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">${parseFloat(s.fat).toFixed(1)}</td>
                    <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">₹${parseFloat(s.rate).toFixed(2)}</td>
                    <td class="py-2 px-3 text-right font-mono font-bold text-emerald-700">₹${parseFloat(s.total_amount).toFixed(2)}</td>
                </tr>
            `;
        });

        tbody.innerHTML = rowsHtml;
        tfoot.innerHTML = `
            <tr>
                <td colspan="3" class="py-2 px-3 border-r border-slate-200 uppercase text-[10px] text-right">Total:</td>
                <td class="py-2 px-3 border-r border-slate-200 text-right font-mono text-sky-900">${totalLiters.toFixed(2)} L</td>
                <td colspan="2" class="py-2 px-3 border-r border-slate-200"></td>
                <td class="py-2 px-3 text-right font-mono text-emerald-900">₹${totalAmount.toFixed(2)}</td>
            </tr>
        `;
    }

    function renderPayments(payments) {
        const container = document.getElementById('bRevPaymentsContainer');
        const totalText = document.getElementById('bRevPaymentsTotalText');

        if (!payments || payments.length === 0) {
            container.innerHTML = '<div class="text-slate-400 py-2">No separate Khata payment receipts found in this period.</div>';
            totalText.innerText = 'Total: ₹0.00';
            return;
        }

        let totalPay = 0;
        let html = '<div class="space-y-1.5 max-h-[160px] overflow-y-auto">';
        payments.forEach(p => {
            const amt = parseFloat(p.amount);
            totalPay += amt;
            html += `
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-slate-800 text-[11px]">${p.receipt_number || 'REC'}</span>
                        <span class="text-[10px] text-slate-400">${p.payment_date}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold">${p.payment_mode || 'Cash'}</span>
                    </div>
                    <span class="font-mono font-bold text-amber-700 text-xs">₹${amt.toFixed(2)}</span>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
        totalText.innerText = `Total: ₹${totalPay.toFixed(2)}`;
    }

    function recalcBuyerSummary() {
        if (!activeInvoiceData) return;

        const milkAmount = parseFloat(activeInvoiceData.totals.milk_amount || 0);
        const prevBal = parseFloat(document.getElementById('bRevInputPrevBal').value || 0);
        const otherCharges = parseFloat(document.getElementById('bRevInputOtherCharges').value || 0);
        const cashReceived = parseFloat(document.getElementById('bRevInputCashReceived').value || 0);
        const discount = parseFloat(document.getElementById('bRevInputDiscount').value || 0);

        const netReceivable = Math.max(0, (prevBal + milkAmount + otherCharges) - (cashReceived + discount));

        document.getElementById('bRevSumMilkAmount').innerText = `₹${milkAmount.toFixed(2)}`;
        document.getElementById('bRevSumPrevBal').innerText = `₹${prevBal.toFixed(2)}`;
        document.getElementById('bRevSumOtherCharges').innerText = `₹${otherCharges.toFixed(2)}`;
        document.getElementById('bRevSumCashReceived').innerText = `- ₹${cashReceived.toFixed(2)}`;
        document.getElementById('bRevSumDiscount').innerText = `- ₹${discount.toFixed(2)}`;
        document.getElementById('bRevSumNetReceivable').innerText = `₹${netReceivable.toFixed(2)}`;
    }

    async function saveAndVerifyBuyerInvoice() {
        if (!activeInvoiceData) return;
        const btn = document.getElementById('bRevSaveBtn');
        btn.disabled = true;
        btn.innerText = 'Saving...';

        const prevBal = parseFloat(document.getElementById('bRevInputPrevBal').value || 0);
        const otherCharges = parseFloat(document.getElementById('bRevInputOtherCharges').value || 0);
        const cashReceived = parseFloat(document.getElementById('bRevInputCashReceived').value || 0);
        const discount = parseFloat(document.getElementById('bRevInputDiscount').value || 0);

        try {
            const res = await fetch(`{{ url('buyer-invoices/verify-save') }}/${activeInvoiceData.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    previous_balance: prevBal,
                    other_charges: otherCharges,
                    cash_received: cashReceived,
                    discount_amount: discount
                })
            });

            const result = await res.json();
            if (result.success) {
                // Show toast
                const toast = document.getElementById('bRevToast');
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 2000);

                // If more items in queue, step to next
                if (currentQueueIndex < currentReviewQueue.length - 1) {
                    stepBuyerReviewQueue(1);
                } else {
                    // Completed queue
                    window.location.reload();
                }
            } else {
                alert(result.message || 'Error saving invoice');
            }
        } catch (e) {
            console.error(e);
            alert('Error saving invoice');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Save Invoice & Finalize</span>';
            if (window.lucide) lucide.createIcons();
        }
    }

    // Modal 3: Quick Preview Modal
    async function openQuickPreviewModal(invoiceId) {
        document.getElementById('quickPreviewModal').classList.remove('hidden');
        const qpBody = document.getElementById('qpBody');
        qpBody.innerHTML = '<div class="py-8 text-center text-slate-400">Loading invoice details...</div>';

        try {
            const res = await fetch(`{{ url('buyer-invoices/preview') }}/${invoiceId}`);
            if (!res.ok) throw new Error('Failed to load invoice');
            const data = await res.json();

            document.getElementById('qpTitle').innerText = `Invoice #${data.invoice_number} - ${data.buyer.name}`;
            document.getElementById('qpSubtitle').innerText = `Code: ${data.buyer.code} | Period: ${data.period_start_display} to ${data.period_end_display}`;

            let html = `
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total Liters</div>
                        <div class="text-base font-extrabold font-mono text-sky-800">${parseFloat(data.totals.total_quantity).toFixed(2)} L</div>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                        <div class="text-[10px] uppercase font-bold text-emerald-700">Milk Amount</div>
                        <div class="text-base font-extrabold font-mono text-emerald-800">₹${parseFloat(data.totals.milk_amount).toFixed(2)}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                        <div class="text-[10px] uppercase font-bold text-amber-700">Cash Received</div>
                        <div class="text-base font-extrabold font-mono text-amber-800">₹${parseFloat(data.totals.cash_received).toFixed(2)}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200">
                        <div class="text-[10px] uppercase font-bold text-indigo-700">Net Due (Receivable)</div>
                        <div class="text-base font-extrabold font-mono text-indigo-900">₹${parseFloat(data.totals.net_receivable).toFixed(2)}</div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1 font-medium">
                    <div class="flex justify-between"><span>Previous Balance Due:</span><b class="font-mono">₹${parseFloat(data.totals.previous_balance).toFixed(2)}</b></div>
                    <div class="flex justify-between"><span>Other Charges:</span><b class="font-mono">₹${parseFloat(data.totals.other_charges).toFixed(2)}</b></div>
                    <div class="flex justify-between"><span>Discount Allowed:</span><b class="font-mono">₹${parseFloat(data.totals.discount_amount).toFixed(2)}</b></div>
                    <div class="flex justify-between"><span>Current Status:</span><b class="uppercase text-[11px] ${data.status === 'pending' ? 'text-amber-700' : 'text-emerald-700'}">${data.status}</b></div>
                </div>
            `;

            qpBody.innerHTML = html;
        } catch (e) {
            console.error(e);
            qpBody.innerHTML = '<div class="py-4 text-center text-rose-500">Error loading invoice details.</div>';
        }
    }

    function closeQuickPreviewModal() {
        document.getElementById('quickPreviewModal').classList.add('hidden');
    }
</script>
@endpush
