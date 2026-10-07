@extends('layouts.app')

@section('title', 'Profit & Loss Statement')
@section('breadcrumb', 'Profit & Loss')
@section('header_title', 'Business Profitability & Income Statement')

@section('header_action')
    <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Expenses</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Month Selector -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Period: {{ date('F Y', strtotime($month . '-01')) }}</h3>
            <p class="text-xs text-slate-500">Consolidated business revenue versus milk procurement costs & overheads.</p>
        </div>
        <form method="GET" action="{{ route('expenses.profit-loss') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" class="text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg">
            <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg">Filter</button>
        </form>
    </div>

    <!-- P&L Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL REVENUE (SALES)</span>
            <p class="text-3xl font-black text-slate-900 mt-2">₹ {{ number_format($totalRevenue, 2) }}</p>
            <span class="text-xs text-emerald-600 font-semibold block mt-1">POS Counter + Retail</span>
        </div>

        <!-- Costs -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL EXPENSES & PROCURE</span>
            <p class="text-3xl font-black text-rose-600 mt-2">₹ {{ number_format($totalCosts, 2) }}</p>
            <span class="text-xs text-slate-500 block mt-1">Procurement + Operations</span>
        </div>

        <!-- Net Profit -->
        <div class="bg-white p-5 rounded-2xl border-2 {{ $netProfit >= 0 ? 'border-emerald-400' : 'border-rose-400' }} shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ESTIMATED NET SURPLUS / (DEFICIT)</span>
            <p class="text-3xl font-black {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-2">
                {{ $netProfit >= 0 ? '+' : '-' }}₹ {{ number_format(abs($netProfit), 2) }}
            </p>
            <span class="text-xs font-semibold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} block mt-1">
                {{ $netProfit >= 0 ? 'Operating Profit' : 'Operating Deficit' }}
            </span>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h4 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">Financial Breakdown</h4>

        <div class="space-y-3 text-xs">
            <!-- Revenue section -->
            <div>
                <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-2">1. Operating Inflow (Revenue)</p>
                <div class="flex justify-between py-1.5 px-3 bg-slate-50 rounded-lg">
                    <span class="font-medium">Retail POS Counter Sales</span>
                    <span class="font-bold text-slate-900">₹ {{ number_format($posRevenue, 2) }}</span>
                </div>
            </div>

            <!-- Cost of Goods -->
            <div>
                <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-2 mt-4">2. Cost of Milk Procurement</p>
                <div class="flex justify-between py-1.5 px-3 bg-slate-50 rounded-lg">
                    <span class="font-medium">Farmer Milk Collections (Quality Rate Payouts)</span>
                    <span class="font-bold text-rose-600">₹ {{ number_format($milkProcurementCost, 2) }}</span>
                </div>
            </div>

            <!-- Operating Overheads -->
            <div>
                <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-2 mt-4">3. Operating Overhead Expenses</p>
                <div class="space-y-1.5">
                    @forelse($expenseBreakdown as $eb)
                        <div class="flex justify-between py-1.5 px-3 bg-slate-50 rounded-lg">
                            <span class="font-medium">{{ $eb->name }}</span>
                            <span class="font-semibold text-slate-800">₹ {{ number_format($eb->total, 2) }}</span>
                        </div>
                    @empty
                        <div class="text-slate-400 py-2 px-3">No categorized expenses for this month.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-between items-baseline">
            <span class="font-bold text-slate-900 text-sm">Net Operating Balance:</span>
            <span class="text-xl font-black {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                ₹ {{ number_format($netProfit, 2) }}
            </span>
        </div>
    </div>

</div>
@endsection
