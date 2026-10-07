@extends('layouts.app')

@section('title', 'POS Sales History')
@section('breadcrumb', 'POS History')
@section('header_title', 'POS Sales History & Invoices')

@section('header_action')
    <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="store" class="w-4 h-4"></i>
        <span>Back to POS Terminal</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Today's Counter Revenue</span>
            <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($todaySales, 2) }}</p>
        </div>
        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs">
            + New Counter Bill
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Items Summary</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4 text-right">Grand Total</th>
                        <th class="py-3 px-4 text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $o->order_number }}</td>
                            <td class="py-3.5 px-4">{{ $o->created_at->format('d M Y, h:i A') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $o->customer_name }}</td>
                            <td class="py-3.5 px-4">
                                @foreach($o->items as $it)
                                    <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-[11px] font-medium mr-1 mb-1">
                                        {{ $it->product ? $it->product->name : 'Item' }} &times; {{ $it->quantity }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-3.5 px-4 capitalize font-semibold text-emerald-700">
                                {{ $o->payment_mode }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-sm text-slate-900">
                                ₹ {{ number_format($o->grand_total, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('pos.receipt', $o) }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                    Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No POS orders recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
