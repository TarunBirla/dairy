@extends('layouts.app')

@section('title', 'Sales Report')
@section('breadcrumb', 'Sales Report')
@section('header_title', 'Retail & Counter Sales Analytics')

@section('header_action')
    <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
        <span>Print Report</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div class="pt-4">
                <button type="submit" class="px-4 py-1.5 bg-teal-600 text-white font-bold text-xs rounded-lg shadow-2xs">Generate</button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Billed POS Sales</span>
            <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalSales, 2) }}</p>
        </div>
        <span class="text-xs text-slate-500 font-medium">Billed across counters & walk-in retail</span>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Payment Mode</th>
                        <th class="py-3 px-4 text-right">Grand Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $o)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold">{{ $o->order_number }}</td>
                            <td class="py-3.5 px-4 font-mono">{{ $o->created_at->format('d M Y, h:i A') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $o->customer_name }}</td>
                            <td class="py-3.5 px-4 uppercase font-bold text-emerald-700">{{ $o->payment_mode }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-sm text-slate-900">
                                ₹ {{ number_format($o->grand_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No sales transactions found.</td>
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
