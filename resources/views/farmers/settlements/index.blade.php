@extends('layouts.app')

@section('title', 'Farmer Settlements')
@section('breadcrumb', 'Settlements')
@section('header_title', 'Farmer Settlements & Payouts')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('settlements.index', ['tab' => 'pending']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 {{ $tab === 'pending' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>Pending Payouts ({{ $pendingCount }})</span>
        </a>
        <a href="{{ route('settlements.index', ['tab' => 'settled']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 {{ $tab === 'settled' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }} text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Settled History ({{ $settledCount }})</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-medium shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800 text-xs font-medium shadow-xs">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Top Navigation Tabs -->
    <div class="flex items-center gap-3 border-b border-slate-200 pb-3">
        <a href="{{ route('settlements.index', ['tab' => 'pending', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $tab === 'pending' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i data-lucide="hourglass" class="w-4 h-4"></i>
            <span>Pending Payouts (Ready to Settle)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'pending' ? 'bg-emerald-800 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $pendingCount }}</span>
        </a>
        <a href="{{ route('settlements.index', ['tab' => 'settled', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search]) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition {{ $tab === 'settled' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span>Settled Payouts History</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'settled' ? 'bg-emerald-800 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $settledCount }}</span>
        </a>
    </div>

    @if($tab === 'pending')
        <!-- ==================== PENDING INVOICES (READY FOR SETTLEMENT) ==================== -->

        <!-- Pending Metrics & Bulk Settle Trigger -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pending Payout Amount (देना है)</span>
                <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalPendingPayable, 2) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $pendingInvoices->total() }} Invoices waiting for payout</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="openSettleModalBtn" onclick="handleBulkSettleClick()"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Settle Selected Payouts (<span id="btnSelectedCount">0</span>)</span>
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('settlements.index') }}" class="flex flex-wrap items-end gap-3.5">
                <input type="hidden" name="tab" value="pending">

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Period</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Period</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
                </div>

                <div class="min-w-[240px] flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search Farmer / Bill #</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Farmer Name, Code, Bill #..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px] cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filter</span>
                    </button>
                    <a href="{{ route('settlements.index', ['tab' => 'pending']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Selection Summary Bar -->
        <div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3 px-4 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 font-bold text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Select All (इस पेज के सभी चुने)</span>
                </label>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600">Selected: <strong id="selectionSummaryCount" class="text-emerald-700">0</strong> invoices</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600">Selected Payout: <strong id="selectionSummaryAmount" class="text-emerald-700 font-bold">₹ 0.00</strong></span>
            </div>
            <div>
                <button type="button" onclick="handleBulkSettleClick()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Settle Selected</span>
                </button>
            </div>
        </div>

        <!-- Pending Invoices Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">#</th>
                            <th class="py-3 px-4">Farmer Details</th>
                            <th class="py-3 px-4">Bill & Period</th>
                            <th class="py-3 px-4 text-right">Milk Liters</th>
                            <th class="py-3 px-4 text-right">Gross Amount</th>
                            <th class="py-3 px-4 text-right">Deductions</th>
                            <th class="py-3 px-4 text-right font-black text-emerald-700 bg-emerald-50/50">Payable (देना है)</th>
                            <th class="py-3 px-4 text-right font-black text-rose-700">Remaining Bal (शेष)</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingInvoices as $inv)
                            @php
                                $remBal = (float) ($inv->previous_balance ?? $inv->closing_wallet_balance ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition" id="row-inv-{{ $inv->id }}">
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox"
                                           class="invoice-checkbox w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                           value="{{ $inv->id }}"
                                           data-amount="{{ $inv->net_payment }}"
                                           data-farmer="{{ $inv->farmer->name ?? 'Farmer' }}"
                                           onchange="handleRowCheckboxChange()">
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $inv->farmer->name ?? 'Farmer #' . $inv->farmer_id }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $inv->farmer->farmer_code ?? '-' }} • {{ $inv->farmer->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-slate-800">#{{ $inv->invoice_number }}</span>
                                    <span class="block text-[11px] text-slate-400">
                                        {{ $inv->period_start ? $inv->period_start->format('d M') : '-' }} to {{ $inv->period_end ? $inv->period_end->format('d M Y') : '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-800">
                                    {{ number_format($inv->total_quantity, 2) }} L
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-700">
                                    ₹ {{ number_format($inv->total_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-rose-600">
                                    {{ $inv->total_deduction > 0 ? '- ₹ ' . number_format($inv->total_deduction, 2) : '₹ 0.00' }}
                                </td>
                                <td class="py-3.5 px-4 text-right bg-emerald-50/50">
                                    <span class="inline-block px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-black text-sm">
                                        ₹ {{ number_format($inv->net_payment, 2) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span class="font-bold {{ $remBal > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                        ₹ {{ number_format($remBal, 2) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            onclick="openSingleSettlement({{ $inv->id }}, '{{ addslashes($inv->farmer->name ?? 'Farmer') }}', {{ $inv->net_payment }})"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Settle</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-400"></i>
                                        <p class="font-semibold text-slate-600">सभी इनवॉइस सेटल हो चुकी हैं!</p>
                                        <p class="text-xs text-slate-400">कोई भी पेंडिंग इनवॉइस भुगतान बाकी नहीं है।</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pendingInvoices->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $pendingInvoices->links() }}
                </div>
            @endif
        </div>

    @else
        <!-- ==================== SETTLED PAYOUTS HISTORY ==================== -->

        <!-- Settled Metrics -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Settlements Paid (कुल सेटल भुगतान)</span>
                <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalSettledAmount, 2) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $settlements->total() }} Payouts recorded</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('settlements.index', ['tab' => 'pending']) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>+ Settle Pending Payouts</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('settlements.index') }}" class="flex flex-wrap items-end gap-3.5">
                <input type="hidden" name="tab" value="settled">

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
                </div>

                <div class="min-w-[220px] flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search Farmer / Settlement #</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Farmer Name, Code, SETTLE-..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Payment Mode</label>
                    <select name="payment_mode" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-700">
                        <option value="">All Modes</option>
                        <option value="Cash" {{ $paymentMode === 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Bank Transfer" {{ $paymentMode === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="UPI" {{ $paymentMode === 'UPI' ? 'selected' : '' }}>UPI</option>
                        <option value="Cheque" {{ $paymentMode === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px] cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filter</span>
                    </button>
                    <a href="{{ route('settlements.index', ['tab' => 'settled']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Settled History Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Settlement #</th>
                            <th class="py-3 px-4">Farmer</th>
                            <th class="py-3 px-4">Billing Period</th>
                            <th class="py-3 px-4">Total Liters</th>
                            <th class="py-3 px-4">Gross Value</th>
                            <th class="py-3 px-4">Deductions</th>
                            <th class="py-3 px-4 text-right font-black text-emerald-700">Net Paid</th>
                            <th class="py-3 px-4">Payment Mode & Ref</th>
                            <th class="py-3 px-4 text-center">Receipt</th>
                            <th class="py-3 px-4 text-center">Revert / Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($settlements as $s)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    {{ $s->settlement_number }}
                                    <span class="block text-[10px] text-slate-400 font-sans">{{ $s->settled_at ? $s->settled_at->format('d M Y, h:i A') : '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($s->farmer)
                                        <a href="{{ route('farmers.show', $s->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                            {{ $s->farmer->name }}
                                        </a>
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $s->farmer->farmer_code }} • {{ $s->farmer->village ?? '-' }}</span>
                                    @else
                                        <span class="font-bold text-slate-700">Farmer #{{ $s->farmer_id }}</span>
                                        <span class="block text-[10px] text-slate-400 italic">Farmer Removed</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $s->period_start ? $s->period_start->format('d M') : '-' }} to {{ $s->period_end ? $s->period_end->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold">{{ number_format($s->total_liters, 2) }} L</td>
                                <td class="py-3.5 px-4">₹ {{ number_format($s->gross_amount, 2) }}</td>
                                <td class="py-3.5 px-4 text-rose-600 font-semibold">
                                    {{ ($s->deduction_amount > 0 || $s->advance_recovered > 0) ? '- ₹ ' . number_format($s->deduction_amount + $s->advance_recovered, 2) : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-sm text-emerald-700">
                                    ₹ {{ number_format($s->paid_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $s->payment_mode === 'Cash' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $s->payment_mode ?? 'Cash' }}
                                    </span>
                                    @if($s->payment_reference)
                                        <span class="block text-[10px] font-mono text-slate-500 mt-0.5">{{ $s->payment_reference }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('settlements.show', $s) }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg inline-flex items-center gap-1">
                                        <i data-lucide="file-text" class="w-3 h-3"></i>
                                        <span>Voucher</span>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('settlements.destroy', $s) }}" method="POST" class="inline"
                                          onsubmit="return confirm('क्या आप वाकई यह सेटलमेंट (#{{ $s->settlement_number }}) डिलीट करना चाहते हैं?\n\nइसे डिलीट करने पर संबंधित इनवॉइस वापस Unsettled (Generated) स्थिति में आ जाएगी, जिसे आप पुनः एडिट या डिलीट कर सकेंगे।');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition inline-flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-slate-400">कोई सेटलमेंट रिकॉर्ड नहीं मिला।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($settlements->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $settlements->links() }}
                </div>
            @endif
        </div>
    @endif

</div>

<!-- ==================== SETTLEMENT POPUP MODAL ==================== -->
<div id="settlementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5 text-emerald-400"></i>
                <h3 class="font-bold text-sm">किसान भुगतान सेटलमेंट (Farmer Payout Settlement)</h3>
            </div>
            <button type="button" onclick="closeSettlementModal()" class="text-slate-400 hover:text-white transition cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="settlementForm" method="POST" action="{{ route('settlements.settle-invoices') }}" class="p-6 space-y-5">
            @csrf
            <!-- Container for dynamic hidden invoice IDs -->
            <div id="modalInvoiceIdsContainer"></div>

            <!-- Payout Summary Banner -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <span class="text-[11px] uppercase font-bold text-emerald-700 block">चयनित इनवॉइस (Selected)</span>
                    <span id="modalSelectedCount" class="text-base font-bold text-slate-800">0 किसान</span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] uppercase font-bold text-emerald-700 block">कुल भुगतान राशि (Total Payout)</span>
                    <span id="modalSelectedAmount" class="text-xl font-black text-emerald-700">₹ 0.00</span>
                </div>
            </div>

            <!-- Settlement Date -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">भुगतान दिनांक (Settlement Date)</label>
                <input type="date" name="settlement_date" value="{{ date('Y-m-d') }}"
                       class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-800">
            </div>

            <!-- Payment Mode -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">भुगतान का माध्यम (Payment Mode) <span class="text-rose-500">*</span></label>
                <select name="payment_mode" id="modalPaymentMode" onchange="handlePaymentModeChange(this.value)"
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-800">
                    <option value="Cash" selected>Cash (नकद भुगतान)</option>
                    <option value="UPI">UPI (Google Pay / PhonePe / Paytm)</option>
                    <option value="Bank Transfer">Bank Transfer (NEFT / RTGS / IMPS)</option>
                    <option value="Cheque">Cheque (चेक)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Cash चुनने पर ट्रांजैक्शन आईडी डालने की आवश्यकता नहीं होगी।</p>
            </div>

            <!-- Dynamic Transaction ID (HIDDEN when Cash, SHOWN when UPI or Bank Transfer or Cheque) -->
            <div id="txnRefGroup" class="hidden">
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1.5">
                    ट्रांजैक्शन आईडी / UTR / चेक नं. (Transaction ID / Reference) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="payment_reference" id="paymentReferenceInput"
                           placeholder="उदा. UTR9283748291 या UPI Ref Number"
                           class="w-full pl-9 pr-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-mono text-slate-800">
                    <i data-lucide="hash" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeSettlementModal()"
                        class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    रद्द करें (Cancel)
                </button>
                <button type="submit" id="confirmSettleSubmitBtn"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>भुगतान सेटल करें (Confirm & Settle)</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update summary and checkboxes
    function recalculateSelection() {
        const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
        const count = checkboxes.length;
        let totalAmount = 0;

        checkboxes.forEach(cb => {
            const amt = parseFloat(cb.getAttribute('data-amount')) || 0;
            totalAmount += amt;
        });

        // Update badges
        const countElem = document.getElementById('selectionSummaryCount');
        if (countElem) countElem.textContent = count;

        const amtElem = document.getElementById('selectionSummaryAmount');
        if (amtElem) amtElem.textContent = '₹ ' + totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const btnCount = document.getElementById('btnSelectedCount');
        if (btnCount) btnCount.textContent = count;

        // Header checkbox state
        const allCheckboxes = document.querySelectorAll('.invoice-checkbox');
        const selectAllCb = document.getElementById('selectAllCheckbox');
        if (selectAllCb && allCheckboxes.length > 0) {
            selectAllCb.checked = (count === allCheckboxes.length);
            selectAllCb.indeterminate = (count > 0 && count < allCheckboxes.length);
        }
    }

    function toggleSelectAll(masterCb) {
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCb.checked;
        });
        recalculateSelection();
    }

    function handleRowCheckboxChange() {
        recalculateSelection();
    }

    function openSingleSettlement(invoiceId, farmerName, amount) {
        // Uncheck all others
        const allCheckboxes = document.querySelectorAll('.invoice-checkbox');
        allCheckboxes.forEach(cb => cb.checked = false);

        // Check only target invoice
        const targetCb = document.querySelector(`.invoice-checkbox[value="${invoiceId}"]`);
        if (targetCb) targetCb.checked = true;

        recalculateSelection();
        openSettlementModal([invoiceId], 1, amount);
    }

    function handleBulkSettleClick() {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('कृपया सेटल करने के लिए कम से कम एक इनवॉइस का चयन करें।');
            return;
        }

        const ids = [];
        let total = 0;
        checkedBoxes.forEach(cb => {
            ids.push(cb.value);
            total += parseFloat(cb.getAttribute('data-amount')) || 0;
        });

        openSettlementModal(ids, checkedBoxes.length, total);
    }

    function openSettlementModal(ids, count, totalAmount) {
        const container = document.getElementById('modalInvoiceIdsContainer');
        container.innerHTML = '';

        ids.forEach(id => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'invoice_ids[]';
            hidden.value = id;
            container.appendChild(hidden);
        });

        document.getElementById('modalSelectedCount').textContent = `${count} किसान`;
        document.getElementById('modalSelectedAmount').textContent = '₹ ' + totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Reset to Cash by default
        const modeSelect = document.getElementById('modalPaymentMode');
        if (modeSelect) {
            modeSelect.value = 'Cash';
            handlePaymentModeChange('Cash');
        }

        const modal = document.getElementById('settlementModal');
        modal.classList.remove('hidden');
    }

    function closeSettlementModal() {
        const modal = document.getElementById('settlementModal');
        modal.classList.add('hidden');
    }

    // Dynamic Transaction ID: HIDDEN for Cash, VISIBLE for UPI / Bank / Cheque
    function handlePaymentModeChange(mode) {
        const txnGroup = document.getElementById('txnRefGroup');
        const txnInput = document.getElementById('paymentReferenceInput');

        if (mode === 'Cash') {
            txnGroup.classList.add('hidden');
            txnInput.value = '';
            txnInput.removeAttribute('required');
        } else {
            txnGroup.classList.remove('hidden');
            txnInput.setAttribute('required', 'required');
            txnInput.focus();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSettlementModal();
        }
    });

    // Close on backdrop click
    document.getElementById('settlementModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeSettlementModal();
        }
    });
</script>
@endsection
