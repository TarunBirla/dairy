@extends('layouts.app')

@section('title', 'Procurement Report')
@section('breadcrumb', 'Collection Report')
@section('header_title', 'Milk Procurement & Quality Analytics')

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
        <form method="GET" action="{{ route('reports.collections') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div class="pt-4">
                <button type="submit" class="px-4 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg shadow-2xs">Generate</button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Volume Procured</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalLiters, 1) }} Liters</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Farmer Payout</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">₹ {{ number_format($totalPayout, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average FAT %</span>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ number_format($avgFat, 1) }}%</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average SNF %</span>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ number_format($avgSnf, 1) }}%</p>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Receipt</th>
                        <th class="py-3 px-4">Date & Shift</th>
                        <th class="py-3 px-4">Farmer</th>
                        <th class="py-3 px-4">Milk Type</th>
                        <th class="py-3 px-4">Quantity (L)</th>
                        <th class="py-3 px-4">FAT / SNF</th>
                        <th class="py-3 px-4">Rate (₹)</th>
                        <th class="py-3 px-4 text-right">Net Value (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold">{{ $col->receipt_number }}</td>
                            <td class="py-3 px-4 font-mono">{{ $col->collection_date->format('d M Y') }} ({{ ucfirst($col->shift) }})</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $col->farmer->name }}</td>
                            <td class="py-3 px-4 capitalize">{{ $col->milk_type }}</td>
                            <td class="py-3 px-4 font-bold">{{ $col->quantity_liters }} L</td>
                            <td class="py-3 px-4">{{ $col->fat }}% / {{ $col->snf }}%</td>
                            <td class="py-3 px-4">₹ {{ number_format($col->applied_rate, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">₹ {{ number_format($col->net_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No records found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
