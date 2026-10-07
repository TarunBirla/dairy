@extends('layouts.app')

@section('title', 'Farmer Advance List')
@section('breadcrumb', 'Advance & Loans')
@section('header_title', 'Farmer Advance & Loan Management')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" @click="openReceiveModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
            <span>Receive</span>
        </button>
        <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4 text-emerald-600"></i>
            <span>+ Add New Advance</span>
        </button>
        <button type="button" @click="showImportModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="upload" class="w-4 h-4 text-slate-500"></i>
            <span>Import Advance</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="advanceManager()">

    <!-- Stat Summary Cards matching media_1791384816027.png -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Advance -->
        <div class="bg-gradient-to-r from-blue-50/70 to-blue-100/30 p-5 rounded-2xl border border-blue-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">TOTAL ADVANCE</span>
                <p class="text-3xl font-extrabold text-blue-900 mt-1">Rs. {{ number_format($totalAdvance, 2) }}</p>
                <p class="text-[11px] text-blue-500 mt-0.5">Total outgoing loans & advances</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100/80 text-blue-700 flex items-center justify-center">
                <i data-lucide="hand-coins" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-gradient-to-r from-emerald-50/70 to-emerald-100/30 p-5 rounded-2xl border border-emerald-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">TOTAL PAID</span>
                <p class="text-3xl font-extrabold text-emerald-800 mt-1">Rs. {{ number_format($totalPaid, 2) }}</p>
                <p class="text-[11px] text-emerald-600 mt-0.5">Recovered from milk or cash payments</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100/80 text-emerald-700 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Balance -->
        <div class="bg-gradient-to-r from-rose-50/70 to-rose-100/30 p-5 rounded-2xl border border-rose-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">TOTAL BALANCE</span>
                <p class="text-3xl font-extrabold text-rose-700 mt-1">Rs. {{ number_format($totalBalance, 2) }}</p>
                <p class="text-[11px] text-rose-500 mt-0.5">Outstanding advance dues</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-100/80 text-rose-700 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Farmer Advance List Card Matching Reference Layout media_1791384816027.png -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header Banner & Filter Bar -->
        <div class="p-5 border-b border-slate-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Farmer Advance List / किसान अग्रिम सूची</h3>
                    <p class="text-xs text-slate-400">Overview of all active outgoing advances, repayments, and live balance per supplier</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="openReceiveModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                        <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                        <span>Receive</span>
                    </button>
                    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold rounded-lg shadow-xs transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>+ Add New Advance</span>
                    </button>
                    <button type="button" @click="showImportModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition">
                        <i data-lucide="upload" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Import Advance</span>
                    </button>
                </div>
            </div>

            <!-- Filter Bar: All Farmers, Search input, Search, Reset, Print -->
            <form method="GET" action="{{ route('advances.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                <!-- Farmer Dropdown matching reference -->
                <div class="sm:col-span-4 md:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Farmer Filter</label>
                    <select name="farmer_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Farmers</option>
                        @foreach($allFarmers as $f)
                            <option value="{{ $f->id }}" {{ request('farmer_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->farmer_code }} - {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input: Search farmer, phone, voucher... -->
                <div class="sm:col-span-5 md:col-span-6">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search farmer, phone, voucher..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Buttons: Search, Reset, Print matching reference -->
                <div class="sm:col-span-3 md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    <a href="{{ route('advances.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                    <a href="{{ route('advances.print', request()->query()) }}" target="_blank" class="px-3 py-1.5 text-xs font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-lg transition flex items-center gap-1">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Print</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Matching Columns in media_1791384816027.png & media_1791384906809.png -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center">S.No</th>
                        <th class="py-3 px-4">Farmer Code</th>
                        <th class="py-3 px-4">Farmer Name</th>
                        <th class="py-3 px-4">Advance Amount</th>
                        <th class="py-3 px-4">Paid</th>
                        <th class="py-3 px-4">Total Balance</th>
                        <th class="py-3 px-4 text-center pr-6 w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($farmers as $index => $farmer)
                        @php
                            $farmerAdvTotal = (float) $farmer->advances->sum('amount');
                            $farmerPaidTotal = (float) $farmer->advances->sum(function($a) { return $a->total_paid; });
                            $farmerBalTotal = max(0, $farmerAdvTotal - $farmerPaidTotal);
                        @endphp
                        <!-- Main Farmer Row -->
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- S.No -->
                            <td class="py-3.5 px-3 text-center text-slate-500 font-medium">
                                {{ $farmers->firstItem() + $index }}
                            </td>

                            <!-- Farmer Code matching screenshot -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-800 text-[11px] font-mono border border-slate-200">
                                    {{ $farmer->farmer_code }}
                                </span>
                            </td>

                            <!-- Farmer Name matching screenshot -->
                            <td class="py-3.5 px-4 font-bold text-emerald-800">
                                <a href="{{ route('advances.ledger', $farmer) }}" class="hover:underline">
                                    {{ $farmer->name }}
                                </a>
                                @if($farmer->name_hi)
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $farmer->name_hi }})</span>
                                @endif
                            </td>

                            <!-- Advance Amount -->
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                Rs. {{ number_format($farmerAdvTotal, 2) }}
                            </td>

                            <!-- Paid -->
                            <td class="py-3.5 px-4 font-bold text-emerald-700">
                                Rs. {{ number_format($farmerPaidTotal, 2) }}
                            </td>

                            <!-- Total Balance in red matching screenshot -->
                            <td class="py-3.5 px-4 font-bold text-rose-600">
                                Rs. {{ number_format($farmerBalTotal, 2) }}
                            </td>

                            <!-- Action: Eye accordion toggle & Ledger button matching screenshot -->
                            <td class="py-3.5 px-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Eye icon: Expands inline sub-table (Image 5) -->
                                    <button type="button" 
                                            @click="toggleExpand({{ $farmer->id }})"
                                            title="View Vouchers Details" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Ledger button matching reference screenshot -->
                                    <a href="{{ route('advances.ledger', $farmer) }}"
                                       title="Advance Ledger" 
                                       class="px-2.5 py-1 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white font-bold text-[11px] shadow-xs transition">
                                        Ledger
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Accordion Sub-table Row matching media_1791384906809.png -->
                        <tr x-show="expandedRows.includes({{ $farmer->id }})" x-cloak class="bg-slate-50/80">
                            <td colspan="7" class="p-3 pl-12 pr-6">
                                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-2xs">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-100/70 text-[10px] uppercase font-bold text-slate-500 border-b border-slate-200">
                                            <tr>
                                                <th class="py-2 px-3">Date</th>
                                                <th class="py-2 px-3">Voucher</th>
                                                <th class="py-2 px-3">Amount</th>
                                                <th class="py-2 px-3">Paid</th>
                                                <th class="py-2 px-3">Balance</th>
                                                <th class="py-2 px-3 text-center w-28">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($farmer->advances as $adv)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="py-2 px-3 font-mono text-slate-700">
                                                        {{ $adv->advance_date->format('Y-m-d') }}
                                                    </td>
                                                    <td class="py-2 px-3 font-mono font-bold text-slate-800">
                                                        {{ $adv->voucher_no ?: str_pad($adv->id, 3, '0', STR_PAD_LEFT) }}
                                                    </td>
                                                    <td class="py-2 px-3 font-bold text-slate-900">
                                                        Rs. {{ number_format($adv->amount, 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 font-semibold text-emerald-700">
                                                        Rs. {{ number_format($adv->total_paid, 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 font-bold text-rose-600">
                                                        Rs. {{ number_format($adv->total_balance, 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-center">
                                                        <div class="flex items-center justify-center gap-1">
                                                            <!-- Edit Button -->
                                                            <button type="button" 
                                                                    @click="openEditModal({
                                                                        id: {{ $adv->id }},
                                                                        date: '{{ $adv->advance_date->format('Y-m-d') }}',
                                                                        voucher: '{{ $adv->voucher_no }}',
                                                                        amount: '{{ $adv->amount }}',
                                                                        interest: '{{ $adv->interest_rate }}',
                                                                        mode: '{{ $adv->payment_mode }}',
                                                                        remark: '{{ addslashes($adv->notes ?? '') }}'
                                                                    })"
                                                                    title="Edit Voucher"
                                                                    class="w-6 h-6 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center">
                                                                <i data-lucide="edit-3" class="w-3 h-3"></i>
                                                            </button>

                                                            <!-- Delete Button -->
                                                            <form action="{{ route('advances.destroy', $adv) }}" method="POST" class="inline" onsubmit="return confirm('Delete advance voucher #{{ $adv->voucher_no }}?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" title="Delete" class="w-6 h-6 rounded bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center">
                                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-3 text-center text-slate-400">No advance vouchers on record.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="hand-coins" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No farmer advances found.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Add New Advance" to issue advances to suppliers.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($farmers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $farmers->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: ADD NEW ADVANCE                           -->
    <!-- Matching Reference media_1791384816027.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showCreateModal = false">

        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showCreateModal = false">

            <!-- Modal Header matching screenshot -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Add New Advance / नया अग्रिम जोड़ें</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form matching exact fields in media_1791384816027.png -->
            <form action="{{ route('advances.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <!-- Row 1: Farmer, Date, Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Farmer -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Farmer <span class="text-rose-500">*</span></label>
                        <select name="farmer_id" x-model="form.farmer_id" required class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Select Farmer</option>
                            @foreach($allFarmers as $f)
                                <option value="{{ $f->id }}">{{ $f->farmer_code }} - {{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="advance_date" x-model="form.advance_date" required
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" x-model="form.amount" required placeholder="Enter Amount"
                               class="w-full px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Row 2: Voucher, Interest Rate, Remark -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Voucher -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Voucher</label>
                        <input type="text" name="voucher_no" x-model="form.voucher_no" placeholder="Voucher No"
                               class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Interest Rate -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate" x-model="form.interest_rate" placeholder="0"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Payment Mode -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="form.payment_mode" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <!-- Remark / Textarea matching screenshot -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" x-model="form.remark" rows="2" placeholder="Enter Remark e.g. -PAID BY DR"
                              class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <!-- Modal Actions matching reference: Reset & Save -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="resetForm()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Reset
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
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
    <!-- IN-PAGE POPUP MODAL: RECEIVE ADVANCE PAYMENT                   -->
    <!-- Matching Reference media_1791384875810.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showReceiveModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showReceiveModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showReceiveModal = false">

            <!-- Modal Header matching screenshot -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Receive Advance Payment / अग्रिम भुगतान प्राप्त करें</h3>
                </div>
                <button type="button" @click="showReceiveModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form matching exact fields in media_1791384875810.png -->
            <form action="{{ route('advances.receive') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <!-- Row 1: Advance & Receive Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Advance Selection -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Advance <span class="text-rose-500">*</span></label>
                        <select name="advance_id" x-model="receiveForm.advance_id" required class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Select Advance</option>
                            @foreach($activeAdvances as $adv)
                                <option value="{{ $adv->id }}">
                                    {{ $adv->farmer->name }} (Voucher #{{ $adv->voucher_no }} - Dues: ₹{{ number_format($adv->total_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Receive Amount -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Receive Amount <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="receive_amount" x-model="receiveForm.receive_amount" required placeholder="Enter Receive Amount"
                               class="w-full px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Row 2: Payment Mode & Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Payment Mode -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                        <select name="payment_mode" x-model="receiveForm.payment_mode" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <!-- Repayment Date -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="repayment_date" x-model="receiveForm.repayment_date"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Remark -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" x-model="receiveForm.remark" rows="2" placeholder="Enter Remark / payment notes"
                              class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <!-- Modal Actions matching reference: Cancel & Receive -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showReceiveModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Receive</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: IMPORT ADVANCE                            -->
    <!-- Matching Reference media_1791384860249.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showImportModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showImportModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showImportModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Import Advance / बल्क अग्रिम आयात</h3>
                </div>
                <button type="button" @click="showImportModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form matching media_1791384860249.png -->
            <form action="{{ route('advances.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-6 border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/30 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Select Advance CSV File</h4>
                    
                    <div class="max-w-xs mx-auto">
                        <input type="file" name="file" required accept=".csv,text/csv"
                               class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 bg-white border border-slate-200 rounded-lg p-1">
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('advances.sample-template') }}" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 underline">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Download Sample</span>
                        </a>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload & Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: EDIT ADVANCE                              -->
    <!-- ============================================================== -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showEditModal = false">

        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showEditModal = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Edit Advance Voucher</h3>
                <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/advances/' + editForm.id" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="advance_date" x-model="editForm.date" required class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Voucher No</label>
                        <input type="text" name="voucher_no" x-model="editForm.voucher" class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-white border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" x-model="editForm.amount" required class="w-full px-3 py-1.5 text-xs font-bold bg-white border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="editForm.mode" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" x-model="editForm.interest" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" x-model="editForm.remark" rows="2" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">
                        Update Advance
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function advanceManager() {
    return {
        showCreateModal: false,
        showReceiveModal: false,
        showImportModal: false,
        showEditModal: false,

        expandedRows: [],

        form: {
            farmer_id: '',
            advance_date: new Date().toISOString().split('T')[0],
            amount: '',
            voucher_no: '{{ $nextVoucher }}',
            interest_rate: '0',
            payment_mode: 'Cash',
            remark: '-PAID BY DR'
        },

        receiveForm: {
            advance_id: '',
            receive_amount: '',
            payment_mode: 'Cash',
            repayment_date: new Date().toISOString().split('T')[0],
            remark: 'Advance payment received'
        },

        editForm: {
            id: null,
            date: '',
            voucher: '',
            amount: '',
            interest: '',
            mode: 'Cash',
            remark: ''
        },

        toggleExpand(farmerId) {
            if (this.expandedRows.includes(farmerId)) {
                this.expandedRows = this.expandedRows.filter(id => id !== farmerId);
            } else {
                this.expandedRows.push(farmerId);
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openCreateModal() {
            this.resetForm();
            this.showCreateModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openReceiveModal() {
            this.showReceiveModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openEditModal(adv) {
            this.editForm = { ...adv };
            this.showEditModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        resetForm() {
            this.form = {
                farmer_id: '',
                advance_date: new Date().toISOString().split('T')[0],
                amount: '',
                voucher_no: '{{ $nextVoucher }}',
                interest_rate: '0',
                payment_mode: 'Cash',
                remark: '-PAID BY DR'
            };
        }
    };
}
</script>
@endsection
