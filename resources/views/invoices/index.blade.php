@extends('layouts.app')

@section('title', 'Invoices & Customer Bills')
@section('breadcrumb', 'Invoices')
@section('header_title', 'Billing & Customer Invoices')

@section('header_action')
    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Generate Invoice</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL INVOICED</span>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">₹ {{ number_format($totalInvoiced, 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PAYMENTS COLLECTED</span>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">₹ {{ number_format($totalCollected, 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PENDING OUTSTANDING</span>
            <p class="text-3xl font-extrabold text-amber-600 mt-2">₹ {{ number_format($totalPending, 2) }}</p>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Billing Period</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Paid Amount</th>
                        <th class="py-3 px-4">Due Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Voucher</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $inv->customer) }}" class="font-bold text-emerald-700 hover:underline">
                                    {{ $inv->customer->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $inv->customer->locality }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $inv->period_start->format('d M') }} to {{ $inv->period_end->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">₹ {{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-700">₹ {{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="py-3.5 px-4 font-black {{ $inv->balance_due > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                ₹ {{ number_format($inv->balance_due, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'unpaid' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('invoices.show', $inv) }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                    View Bill
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No invoices generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
