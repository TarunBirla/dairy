@extends('layouts.app')

@section('title', 'Dealer Payments')
@section('breadcrumb', 'Dealers Payment')
@section('header_title', 'Product Dealer Payment & Dues')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('dealers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Dealers Master</span>
        </a>
        <button type="button" @click="openPayModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add dealer Pay Entry</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="dealerPaymentManager()">

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
            $sumTotalPurchases = $dealerTransactions->sum('total_amount');
            $sumTotalPaid = $dealerTransactions->sum('pay_amount');
            $sumTotalDues = $dealerTransactions->sum('dues_amount');
        @endphp
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PURCHASES</span>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">₹ {{ number_format($sumTotalPurchases, 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">All products & feed bills</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="boxes" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PAID AMOUNT</span>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">₹ {{ number_format($sumTotalPaid, 2) }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Cleared payments to dealers</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL OUTSTANDING DUES</span>
                <p class="text-3xl font-extrabold text-rose-600 mt-1">₹ {{ number_format($sumTotalDues, 2) }}</p>
                <p class="text-[11px] text-rose-500 font-medium mt-0.5">Pending dealer settlement</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Dealer Transaction List Card Matching Reference Layout media_1791384054293.png -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header Banner & Filter Bar -->
        <div class="p-5 border-b border-slate-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Dealer Transaction List / डीलर लेन-देन सूची</h3>
                    <p class="text-xs text-slate-400">Track total orders, payments made, and live outstanding dues per product dealer</p>
                </div>
                <button type="button" @click="openPayModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ Add dealer Pay Entry</span>
                </button>
            </div>

            <!-- Filter Bar matching reference: Dealer Name, Date, Search, Reset -->
            <form method="GET" action="{{ route('dealer-payments.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                <!-- Dealer Name Filter -->
                <div class="sm:col-span-5 md:col-span-5">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Dealer Name</label>
                    <input 
                        type="text" 
                        name="dealer_name" 
                        value="{{ request('dealer_name') }}"
                        placeholder="Type or select dealer" 
                        list="dealers_list"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                    >
                    <datalist id="dealers_list">
                        @foreach($allDealers as $d)
                            <option value="{{ $d->name }}">{{ $d->code }} - {{ $d->name }}</option>
                        @endforeach
                    </datalist>
                </div>

                <!-- Date Filter -->
                <div class="sm:col-span-4 md:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Date</label>
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ request('date') }}"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                    >
                </div>

                <!-- Action Buttons: Search & Reset matching reference -->
                <div class="sm:col-span-3 md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    <a href="{{ route('dealer-payments.index') }}" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Matching Columns in media_1791384054293.png -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-16 text-center">S.No</th>
                        <th class="py-3 px-4">Dealer Name</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Pay Amt</th>
                        <th class="py-3 px-4">Dues Amt</th>
                        <th class="py-3 px-4 text-center pr-6 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dealerTransactions as $index => $row)
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- S.No -->
                            <td class="py-3.5 px-4 text-center text-slate-500 font-medium">
                                {{ $index + 1 }}
                            </td>

                            <!-- Dealer Name matching screenshot -->
                            <td class="py-3.5 px-4 font-bold text-emerald-800">
                                <a href="javascript:void(0)" @click="viewDealerLedger({{ $row->dealer_id }})" class="hover:underline flex items-center gap-1.5">
                                    <span>{{ $row->dealer_name }}</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200">{{ $row->dealer_code }}</span>
                                </a>
                            </td>

                            <!-- Total Amount matching screenshot: Rs. 42650.00 -->
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                Rs. {{ number_format($row->total_amount, 2) }}
                            </td>

                            <!-- Pay Amt matching screenshot: in distinct color Rs. 600.00 -->
                            <td class="py-3.5 px-4 font-bold text-rose-600">
                                Rs. {{ number_format($row->pay_amount, 2) }}
                            </td>

                            <!-- Dues Amt matching screenshot: Rs. 42050.00 -->
                            <td class="py-3.5 px-4 font-bold {{ $row->dues_amount > 0 ? 'text-indigo-700' : 'text-slate-600' }}">
                                Rs. {{ number_format($row->dues_amount, 2) }}
                            </td>

                            <!-- Action: View Eye & List Ledger Icon matching screenshot -->
                            <td class="py-3.5 px-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Eye Button: Quick Pay / View -->
                                    <button type="button" 
                                            @click="quickPayForDealer({{ $row->dealer_id }}, '{{ addslashes($row->dealer_name) }}', {{ $row->dues_amount }})"
                                            title="Pay Dealer Dues" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- List Ledger Icon Button: View Purchases and History -->
                                    <button type="button" 
                                            @click="viewDealerLedger({{ $row->dealer_id }})"
                                            title="Dealer Purchase & Payment History" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 transition">
                                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="receipt" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No dealer transactions found.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Add dealer Pay Entry" to record payment entries.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Recorded Payments Log -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-900">Recent Payment Entries Log</h4>
                <p class="text-xs text-slate-400">History of payments paid out to product suppliers</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                Total Logs: {{ $recentPayments->count() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Dealer</th>
                        <th class="py-3 px-4">Dues Before</th>
                        <th class="py-3 px-4">Pay Amount</th>
                        <th class="py-3 px-4">Remaining Dues</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Comment</th>
                        <th class="py-3 px-4 text-right pr-6">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentPayments as $pay)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $pay->payment_date->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $pay->dealer_name }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                ₹ {{ number_format($pay->dues_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-700">
                                ₹ {{ number_format($pay->pay_amount, 2) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-indigo-700">
                                ₹ {{ number_format($pay->remaining_dues, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $pay->payment_mode }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-[150px] truncate" title="{{ $pay->comment }}">
                                {{ $pay->comment ?: '—' }}
                            </td>
                            <td class="py-3 px-4 text-right pr-6">
                                <a href="{{ route('dealer-payments.print', $pay) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition">
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                    <span>Slip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                No payment vouchers issued yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: ADD DEALER TRANSACTION                    -->
    <!-- Matching Reference media_1791384063890.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showPayModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showPayModal = false">

        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showPayModal = false">

            <!-- Modal Header matching reference -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Add Dealer Transaction / डीलर भुगतान प्रविष्टि</h3>
                </div>
                <button type="button" @click="showPayModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form matching exact fields in media_1791384063890.png -->
            <form action="{{ route('dealer-payments.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <!-- Row 1: Date, Dealer Name, Dues -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Date -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="payment_date" x-model="payForm.payment_date" required
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Dealer Name -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Dealer Name <span class="text-rose-500">*</span></label>
                        <select name="dealer_id" x-model="payForm.dealer_id" @change="onDealerChange($event.target.value)" required
                                class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Type or select dealer</option>
                            @foreach($allDealers as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dues (Live Auto Display) -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Dues</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rs.</span>
                            <input type="text" x-model="payForm.dues" readonly
                                   class="w-full pl-9 pr-3 py-1.5 text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 rounded-lg cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Pay Amount, Comment, Payment Mode -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Pay Amount -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pay Amount <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rs.</span>
                            <input type="number" step="0.01" min="0.01" name="pay_amount" x-model="payForm.pay_amount" required placeholder="Enter Pay Amount"
                                   class="w-full pl-9 pr-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Comment -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Comment</label>
                        <input type="text" name="comment" x-model="payForm.comment" placeholder="Enter Comment"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Payment Mode -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                        <select name="payment_mode" x-model="payForm.payment_mode" required
                                class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="UPI">UPI</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="NEFT/RTGS">NEFT / RTGS</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Actions matching reference: Reset & Save -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="resetPayForm()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Reset
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showPayModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Save</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: DEALER PURCHASES & PAYMENT LEDGER         -->
    <!-- ============================================================== -->
    <div x-show="showLedgerModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showLedgerModal = false">

        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 relative my-6 max-h-[85vh] overflow-y-auto"
             @click.away="showLedgerModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 sticky -top-6 bg-white z-10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="list" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="ledgerData.dealer ? ledgerData.dealer.name + ' - Statement & Ledger' : 'Dealer Statement'"></h3>
                        <p class="text-xs text-slate-400">Complete log of product purchases, voucher settlements, and outstanding balance.</p>
                    </div>
                </div>
                <button type="button" @click="showLedgerModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Statement Summary Cards -->
            <div class="grid grid-cols-3 gap-3 my-4">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Purchases</span>
                    <p class="font-extrabold text-slate-900 mt-0.5" x-text="'₹ ' + Number(ledgerData.total_purchased || 0).toFixed(2)"></p>
                </div>
                <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 text-center">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase">Paid</span>
                    <p class="font-extrabold text-emerald-700 mt-0.5" x-text="'₹ ' + Number(ledgerData.total_paid || 0).toFixed(2)"></p>
                </div>
                <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-100 text-center">
                    <span class="text-[10px] font-bold text-rose-600 uppercase">Current Dues</span>
                    <p class="font-extrabold text-rose-700 mt-0.5" x-text="'₹ ' + Number(ledgerData.dues_amount || 0).toFixed(2)"></p>
                </div>
            </div>

            <!-- Ledger Tabs or Sections -->
            <div class="space-y-4 text-xs">
                <div>
                    <h5 class="font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Recent Purchases From Dealer</span>
                    </h5>
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="py-2 px-3">Date</th>
                                    <th class="py-2 px-3">Purchase #</th>
                                    <th class="py-2 px-3">Product</th>
                                    <th class="py-2 px-3">Qty</th>
                                    <th class="py-2 px-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in (ledgerData.purchases || [])" :key="item.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-mono text-slate-600" x-text="item.purchase_date"></td>
                                        <td class="py-2 px-3 font-mono font-bold text-slate-800" x-text="item.purchase_number"></td>
                                        <td class="py-2 px-3 text-slate-800" x-text="item.product_name"></td>
                                        <td class="py-2 px-3 text-slate-600" x-text="item.quantity + ' ' + (item.unit || '')"></td>
                                        <td class="py-2 px-3 text-right font-bold text-slate-900" x-text="'₹ ' + Number(item.total_amount).toFixed(2)"></td>
                                    </tr>
                                </template>
                                <tr x-show="!(ledgerData.purchases && ledgerData.purchases.length)">
                                    <td colspan="5" class="py-4 text-center text-slate-400">No purchases found for this dealer.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h5 class="font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Payments Paid Out</span>
                    </h5>
                    <div class="border border-slate-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="py-2 px-3">Date</th>
                                    <th class="py-2 px-3">Mode</th>
                                    <th class="py-2 px-3">Notes</th>
                                    <th class="py-2 px-3 text-right">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="pay in (ledgerData.payments || [])" :key="pay.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 font-mono text-slate-600" x-text="pay.payment_date"></td>
                                        <td class="py-2 px-3 text-slate-700" x-text="pay.payment_mode"></td>
                                        <td class="py-2 px-3 text-slate-500" x-text="pay.comment || '—'"></td>
                                        <td class="py-2 px-3 text-right font-bold text-emerald-700" x-text="'₹ ' + Number(pay.pay_amount).toFixed(2)"></td>
                                    </tr>
                                </template>
                                <tr x-show="!(ledgerData.payments && ledgerData.payments.length)">
                                    <td colspan="4" class="py-4 text-center text-slate-400">No payment entries recorded yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end pt-4 border-t border-slate-100 mt-4">
                <button type="button" @click="showLedgerModal = false" class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function dealerPaymentManager() {
    return {
        showPayModal: false,
        showLedgerModal: false,

        payForm: {
            payment_date: new Date().toISOString().split('T')[0],
            dealer_id: '',
            dues: '0.00',
            pay_amount: '',
            comment: '',
            payment_mode: 'UPI'
        },

        ledgerData: {},

        openPayModal() {
            this.resetPayForm();
            this.showPayModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        quickPayForDealer(dealerId, dealerName, duesAmount) {
            this.payForm.dealer_id = dealerId;
            this.payForm.dues = Number(duesAmount).toFixed(2);
            this.payForm.pay_amount = duesAmount > 0 ? Number(duesAmount).toFixed(2) : '';
            this.showPayModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        onDealerChange(dealerId) {
            if (!dealerId) {
                this.payForm.dues = '0.00';
                return;
            }
            fetch(`/dealer-payments/dues/${dealerId}`)
                .then(res => res.json())
                .then(data => {
                    this.payForm.dues = Number(data.dues_amount || 0).toFixed(2);
                })
                .catch(err => {
                    console.error('Error fetching dealer dues:', err);
                });
        },

        viewDealerLedger(dealerId) {
            fetch(`/dealer-payments/transactions/${dealerId}`)
                .then(res => res.json())
                .then(data => {
                    this.ledgerData = data;
                    this.showLedgerModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                })
                .catch(err => {
                    console.error('Error fetching ledger details:', err);
                });
        },

        resetPayForm() {
            this.payForm = {
                payment_date: new Date().toISOString().split('T')[0],
                dealer_id: '',
                dues: '0.00',
                pay_amount: '',
                comment: '',
                payment_mode: 'UPI'
            };
        }
    };
}
</script>
@endsection
