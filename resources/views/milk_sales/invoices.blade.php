@extends('layouts.app')

@section('title', 'Milk Sale Invoices')
@section('breadcrumb', 'Customer Bill')
@section('header_title', 'Milk Sales / Customer Bill (Invoices)')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('milk-sales.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="shopping-cart" class="w-4 h-4 text-emerald-600"></i>
            <span>Milk Sales</span>
        </a>
        <a href="{{ route('buyers.khata') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="book-open" class="w-4 h-4 text-emerald-600"></i>
            <span>Buyer Khata</span>
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Report</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top Card: Title, Date Range Selector & Generate Button matching Screenshot 1 -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Milk Sale Invoices</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 font-mono font-bold">({{ count($invoices) }})</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Periodical milk billing summary & outstanding ledger for buyers</p>
                </div>
            </div>

            <!-- Quick Date Cycle Form matching Image 1 -->
            <form method="GET" action="{{ route('milk-sales.invoices') }}" id="invoiceCycleForm" class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-700">Date Range :</span>
                
                <div class="min-w-[240px]">
                    <select name="cycle_range" id="cycleRangeSelect" onchange="document.getElementById('invoiceCycleForm').submit()" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-semibold text-slate-800">
                        @foreach($cycles as $key => $label)
                            <option value="{{ $key }}" {{ $activeCycleKey === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Generate Invoice</span>
                </button>
            </form>
        </div>

        <!-- Custom Date Range Filter and Search Row -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
            <form method="GET" action="{{ route('milk-sales.invoices') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-slate-500 font-medium">Or Custom Dates:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg bg-slate-50 outline-none focus:bg-white focus:border-emerald-500 font-medium">
                <span class="text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg bg-slate-50 outline-none focus:bg-white focus:border-emerald-500 font-medium">
                <button type="submit" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    Apply
                </button>
            </form>

            <!-- Search Invoice Input matching Image 1 -->
            <div class="relative w-full sm:w-64">
                <input type="text" id="invoiceSearchInput" onkeyup="filterInvoiceTable()" placeholder="Search Invoice..." class="w-full pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
            </div>
        </div>

    </div>

    <!-- Main Invoices Table matching Reference Screenshot 1 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="invoiceTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-700 font-bold text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-3 w-10 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="rounded text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="py-3 px-3">
                            <div class="flex items-center gap-1 cursor-pointer">
                                <span>Code</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4">
                            <div class="flex items-center gap-1 cursor-pointer">
                                <span>Name</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 cursor-pointer">
                                <span>Liter</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 cursor-pointer">
                                <span>Amount</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 cursor-pointer">
                                <span>Cash Received</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 cursor-pointer">
                                <span>Prev Amount</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1 cursor-pointer">
                                <span>Total Amount</span>
                                <i data-lucide="arrow-up-down" class="w-3 h-3 text-slate-400"></i>
                            </div>
                        </th>
                        <th class="py-3 px-3 text-center no-print">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/70 transition-colors invoice-row" data-code="{{ strtolower($inv->code) }}" data-name="{{ strtolower($inv->name) }}">
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" class="row-checkbox rounded text-emerald-600 focus:ring-emerald-500" value="{{ $inv->buyer->id }}">
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                {{ $inv->code }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('buyers.khata', ['buyer_id' => $inv->buyer->id]) }}" class="hover:text-emerald-700 hover:underline">
                                    {{ $inv->name }}
                                </a>
                                @if($inv->buyer->phone)
                                    <div class="text-[10px] font-normal text-slate-400">{{ $inv->buyer->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                {{ number_format($inv->liters, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                ₹{{ number_format($inv->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                ₹{{ number_format($inv->cash_received, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold {{ $inv->prev_amount > 0 ? 'text-amber-700' : ($inv->prev_amount < 0 ? 'text-emerald-700' : 'text-slate-500') }}">
                                ₹{{ number_format($inv->prev_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-black {{ $inv->total_amount > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                                ₹{{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center no-print">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('buyers.bill', ['buyer' => $inv->buyer->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" title="Print Period Bill" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Bill</span>
                                    </a>
                                    <a href="{{ route('buyers.khata', ['buyer_id' => $inv->buyer->id]) }}" title="View Ledger" class="p-1 text-slate-400 hover:text-slate-700">
                                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-16 text-center text-slate-400">
                                <i data-lucide="receipt" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-bold text-slate-600">No milk sale invoices found for this period.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Select a different date range or record milk sales first.</p>
                                <a href="{{ route('milk-sales.index') }}" class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Record Milk Sale</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Summary Total Row matching Screenshot 1 -->
                @if(count($invoices) > 0)
                    <tfoot>
                        <tr class="bg-slate-100/90 border-t-2 border-slate-300 font-black text-slate-900 text-xs">
                            <td class="py-3 px-3 text-center font-bold text-slate-400">-</td>
                            <td class="py-3 px-3 uppercase tracking-wider text-slate-800">Total</td>
                            <td class="py-3 px-4 text-slate-500 font-bold">-</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900">
                                {{ number_format($totals->liters, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900">
                                ₹{{ number_format($totals->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-800">
                                ₹{{ number_format($totals->cash_received, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-800">
                                ₹{{ number_format($totals->prev_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900">
                                ₹{{ number_format($totals->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center no-print">
                                <span class="text-[10px] text-slate-500 font-mono">{{ count($invoices) }} Buyers</span>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Table Footer Pagination / Entry Count Info matching Image 1 -->
        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50">
            <div>
                Showing 1 to {{ count($invoices) }} of {{ count($invoices) }} entries
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-slate-400">Page 1</span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function toggleSelectAll(masterCheckbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
    }

    function filterInvoiceTable() {
        const query = (document.getElementById('invoiceSearchInput').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.invoice-row');
        rows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const name = row.getAttribute('data-name') || '';
            if (code.includes(query) || name.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
