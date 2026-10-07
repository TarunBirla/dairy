@extends('layouts.app')

@section('title', 'Farmer Settlements')
@section('breadcrumb', 'Settlements')
@section('header_title', 'Farmer Settlements & Payouts')

@section('header_action')
    <a href="{{ route('settlements.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>New Settlement</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Settlements Paid</span>
            <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalSettledAmount, 2) }}</p>
        </div>
        <a href="{{ route('settlements.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs">
            + Run Period Payout
        </a>
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
