@extends('layouts.app')

@section('title', 'Farmer Settlements')
@section('breadcrumb', 'Settlements')
@section('header_title', 'Farmer Settlements & Payouts')

@section('header_action')
    <a href="{{ route('settlements.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="calculator" class="w-4 h-4"></i>
        <span>Run Settlement Cycle ({{ $totalEligibleCount > 0 ? '1 / ' . $totalEligibleCount : '0' }})</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pending Farmers Count -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pending Settlements</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalEligibleCount }} Farmers</p>
                <span class="text-xs text-slate-400 mt-0.5 block">Active balance (+ or -)</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Total Payable (+) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Payable to Farmers (+)</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">₹ {{ number_format($totalPayable, 2) }}</p>
                <span class="text-xs text-emerald-600 mt-0.5 block font-medium">Dairy liability for milk</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Total Advances Due (-) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Advances Due (-)</span>
                <p class="text-2xl font-black text-rose-600 mt-1">₹ {{ number_format($totalAdvanceDue, 2) }}</p>
                <span class="text-xs text-rose-600 mt-0.5 block font-medium">Recoverable advances</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Total Settled History -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Settled History</span>
                <p class="text-2xl font-black text-slate-900 mt-1">₹ {{ number_format($totalSettledAmount, 2) }}</p>
                <span class="text-xs text-slate-400 mt-0.5 block">Disbursed to date</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i data-lucide="badge-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <a href="{{ route('settlements.index', ['tab' => 'pending']) }}" 
           class="px-4 py-2.5 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab !== 'history' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>Pending Settlements ({{ $totalEligibleCount }})</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab !== 'history' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                Active
            </span>
        </a>
        <a href="{{ route('settlements.index', ['tab' => 'history']) }}" 
           class="px-4 py-2.5 text-xs font-bold border-b-2 transition flex items-center gap-2 {{ $activeTab === 'history' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-lg' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="history" class="w-4 h-4"></i>
            <span>Settlement History ({{ $settlements->total() }})</span>
        </a>
    </div>

    @if($activeTab !== 'history')
        <!-- TAB 1: PENDING SETTLEMENTS (Excludes 00 balance farmers) -->
        <div class="space-y-4">
            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
                <form method="GET" action="{{ route('settlements.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <input type="hidden" name="tab" value="pending">
                    
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, code, phone, village..." class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 focus:ring-1 focus:ring-emerald-500">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    </div>

                    <!-- Type Filter -->
                    <select name="balance_type" onchange="this.form.submit()" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-700">
                        <option value="all" {{ $balanceType === 'all' ? 'selected' : '' }}>All (+ & -) Balances</option>
                        <option value="payable" {{ $balanceType === 'payable' ? 'selected' : '' }}>Only Payable (+)</option>
                        <option value="advance" {{ $balanceType === 'advance' ? 'selected' : '' }}>Only Advance Due (-)</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
                        Filter
                    </button>

                    @if(!empty($search) || $balanceType !== 'all')
                        <a href="{{ route('settlements.index', ['tab' => 'pending']) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                    <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <i data-lucide="check" class="w-3 h-3"></i> 0.00 Balances Hidden
                    </span>
                    <span>Showing {{ $pendingFarmers->count() }} of {{ $totalEligibleCount }} eligible</span>
                </div>
            </div>

            <!-- Pending Farmers Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">#</th>
                                <th class="py-3 px-4">Farmer Details</th>
                                <th class="py-3 px-4">Village & Contact</th>
                                <th class="py-3 px-4">Animal Type</th>
                                <th class="py-3 px-4 text-right">Ledger Balance (₹)</th>
                                <th class="py-3 px-4">Bank / Payment Details</th>
                                <th class="py-3 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pendingFarmers as $idx => $f)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-400 text-center">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold flex items-center justify-center shrink-0">
                                                {{ substr($f->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('farmers.show', $f) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                                    {{ $f->name }}
                                                </a>
                                                <span class="block font-mono text-[10px] text-emerald-800 font-bold">{{ $f->farmer_code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-medium text-slate-800">{{ $f->village ?: '—' }}</p>
                                        <span class="text-[10px] text-slate-400">{{ $f->phone ?: 'No Phone' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 capitalize font-medium text-slate-700">
                                        {{ $f->animal_type ?: 'All' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if((float)$f->current_balance > 0)
                                            <div class="inline-flex flex-col items-end">
                                                <span class="font-black text-sm text-emerald-600 font-mono">
                                                    + ₹ {{ number_format($f->current_balance, 2) }}
                                                </span>
                                                <span class="text-[9px] font-bold uppercase text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                                    Payable
                                                </span>
                                            </div>
                                        @elseif((float)$f->current_balance < 0)
                                            <div class="inline-flex flex-col items-end">
                                                <span class="font-black text-sm text-rose-600 font-mono">
                                                    - ₹ {{ number_format(abs($f->current_balance), 2) }}
                                                </span>
                                                <span class="text-[9px] font-bold uppercase text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">
                                                    Advance Due
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500">
                                        <p class="font-semibold text-slate-700">{{ $f->bank_name ?: 'Cash Account' }}</p>
                                        <span class="text-[10px] font-mono text-slate-400 block">
                                            {{ $f->account_number ? 'A/C: ' . $f->account_number : ($f->upi_id ? 'UPI: ' . $f->upi_id : 'Direct Cash') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="{{ route('settlements.create', ['farmer_id' => $f->id]) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition">
                                            <span>Settle</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-400"></i>
                                        <p class="text-xs font-semibold text-slate-600">No pending farmers match the filter.</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Farmers with ₹0.00 balance are automatically hidden.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <!-- TAB 2: SETTLEMENT HISTORY (Disbursed Settlements) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Processed Settlement Vouchers</h4>
                    <p class="text-[11px] text-slate-400">History of all finalized and paid farmer settlements</p>
                </div>
                <span class="text-xs font-bold text-slate-500">Total: {{ $settlements->total() }} Records</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Settlement #</th>
                            <th class="py-3 px-4">Farmer</th>
                            <th class="py-3 px-4">Billing Period</th>
                            <th class="py-3 px-4">Total Liters</th>
                            <th class="py-3 px-4">Gross Value</th>
                            <th class="py-3 px-4">Adv Deducted</th>
                            <th class="py-3 px-4 text-right">Net Paid</th>
                            <th class="py-3 px-4">Payment Mode</th>
                            <th class="py-3 px-4 text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($settlements as $s)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $s->settlement_number }}</td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('farmers.show', $s->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                        {{ $s->farmer->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $s->farmer->farmer_code }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $s->period_start->format('d M') }} to {{ $s->period_end->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold">{{ $s->total_liters }} L</td>
                                <td class="py-3.5 px-4">₹ {{ number_format($s->gross_amount, 2) }}</td>
                                <td class="py-3.5 px-4 text-rose-600 font-semibold">
                                    {{ $s->advance_recovered > 0 ? '- ₹ ' . number_format($s->advance_recovered, 2) : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-sm text-emerald-700">
                                    ₹ {{ number_format($s->paid_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 capitalize font-medium">
                                    {{ $s->payment_mode }}
                                    @if($s->payment_reference)
                                        <span class="block text-[10px] text-slate-400">{{ $s->payment_reference }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('settlements.show', $s) }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                        Voucher
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold text-slate-600">No settlement payouts processed yet.</p>
                                    <a href="{{ route('settlements.create') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:underline">
                                        <span>Create First Settlement</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </td>
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
@endsection
