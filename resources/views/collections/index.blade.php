@extends('layouts.app')

@section('title', 'Milk Collection History')
@section('breadcrumb', 'Collection History')
@section('header_title', 'Procurement / Milk Collections')

@section('header_action')
    <a href="{{ route('collections.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>New Collection</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Procured</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($todayTotalLiters, 1) }} <span class="text-xs font-medium text-slate-400">Liters</span></p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Net Payout</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">₹ {{ number_format($todayTotalAmount, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average FAT</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($avgFat, 1) }}%</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average SNF</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($avgSnf, 1) }}%</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('collections.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Collection Date</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Shift</label>
                <select name="shift" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Milk Type</label>
                <select name="milk_type" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Milk Types</option>
                    <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>Cow</option>
                    <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                    <option value="mixed" {{ request('milk_type') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Center</label>
                <select name="center_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Centers</option>
                    @foreach($centers as $c)
                        <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs">Filter</button>
                <a href="{{ route('collections.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    <!-- Collection Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Receipt</th>
                        <th class="py-3 px-4">Date & Shift</th>
                        <th class="py-3 px-4">Farmer / Code</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Qty (L)</th>
                        <th class="py-3 px-4">Fat / SNF / CLR</th>
                        <th class="py-3 px-4">Rate (₹)</th>
                        <th class="py-3 px-4 text-right">Net Amount</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ $col->receipt_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-900 block">{{ $col->collection_date->format('d M Y') }}</span>
                                <span class="text-[10px] capitalize px-1.5 py-0.5 rounded font-bold {{ $col->shift === 'morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $col->shift }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('farmers.show', $col->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                    {{ $col->farmer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">{{ $col->farmer->farmer_code }} • {{ $col->farmer->village }}</span>
                            </td>
                            <td class="py-3.5 px-4 capitalize font-medium">
                                {{ $col->milk_type }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                {{ $col->quantity_liters }} L
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                <span class="font-semibold text-slate-700">{{ $col->fat }}%</span> / 
                                <span class="font-semibold text-slate-700">{{ $col->snf }}%</span>
                                @if($col->clr)
                                    <span class="text-[10px] text-slate-400 block">CLR: {{ $col->clr }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                ₹ {{ number_format($col->applied_rate, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-sm text-emerald-700">
                                ₹ {{ number_format($col->net_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('collections.slip', $col) }}" target="_blank" title="Print Slip" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    <span>Slip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                No collection entries found for selected criteria.
                            </td>
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
