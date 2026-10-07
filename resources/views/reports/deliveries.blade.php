@extends('layouts.app')

@section('title', 'Delivery Report')
@section('breadcrumb', 'Delivery Report')
@section('header_title', 'Route Fulfillment & Delivery Analytics')

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
        <form method="GET" action="{{ route('reports.deliveries') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div class="pt-4">
                <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white font-bold text-xs rounded-lg shadow-2xs">Generate</button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Fulfilled Stops</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $totalDelivered }} Stops</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Skipped / Exceptions</span>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $totalSkipped }} Stops</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Cash Collected at Doorstep</span>
            <p class="text-2xl font-black text-slate-900 mt-1">₹ {{ number_format($totalCash, 2) }}</p>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Date & Shift</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Locality</th>
                        <th class="py-3 px-4">Product Delivered</th>
                        <th class="py-3 px-4">Delivery Staff</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Cash Received (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $d)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $d->delivery_date->format('d M Y') }} ({{ ucfirst($d->shift) }})</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $d->customer->name }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $d->customer->locality }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $d->quantity }} {{ $d->product ? $d->product->unit : 'Ltr' }} • {{ $d->product ? $d->product->name : 'Milk' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $d->deliveryBoy ? $d->deliveryBoy->name : 'Unassigned' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $d->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $d->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                {{ $d->cash_collected > 0 ? '₹ ' . number_format($d->cash_collected, 2) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No delivery entries for this date window.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
