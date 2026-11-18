@extends('layouts.app')

@section('title', 'Farmer Settlements')
@section('breadcrumb', 'Settlements')
@section('header_title', 'Farmer Settlements & Payouts')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('settlements.bulk') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>Bulk Payout</span>
        </a>
        <a href="{{ route('settlements.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>New Settlement</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Settlements Paid</span>
            <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalSettledAmount, 2) }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settlements.bulk') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>+ Run Bulk Payout</span>
            </a>
            <a href="{{ route('settlements.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs inline-flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Run Period Payout</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: From Date, To Date, Search Farmer / Settlement #, Payment Mode -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('settlements.index') }}" class="flex flex-wrap items-end gap-3.5">
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
                <a href="{{ route('settlements.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Settlements Table -->
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
                                @if($s->farmer)
                                    <a href="{{ route('farmers.show', $s->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                        {{ $s->farmer->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $s->farmer->farmer_code }}</span>
                                @else
                                    <span class="font-bold text-slate-700">Farmer #{{ $s->farmer_id }}</span>
                                    <span class="block text-[10px] text-slate-400 italic">Farmer Removed / Deleted</span>
                                @endif
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
                            <td colspan="9" class="py-8 text-center text-slate-400">No settlement payouts processed yet.</td>
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

</div>
@endsection