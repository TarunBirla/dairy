@extends('layouts.app')

@section('title', 'Milk Sale Customer Wise Report')
@section('breadcrumb', 'Reports -> Milk Sale Reports -> Customer Wise')
@section('header_title', 'Milk Sale Customer Wise Report')

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb Bar -->
    <div class="flex items-center justify-between pb-1">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#005c53] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Reports -> Milk Sale Reports -> Customer Wise</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.milk-sales.customer-wise') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <!-- Customer / Buyer -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Customer / Buyer :</label>
                <select name="buyer_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="">All Customers</option>
                    @foreach($buyers as $b)
                        <option value="{{ $b->id }}" {{ request('buyer_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->buyer_code }} - {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <!-- Milk Shift -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Shift :</label>
                <select name="shift" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="all" {{ request('shift', 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-4">
                <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview</span>
                </button>
                <a href="{{ route('reports.milk-sales.customer-wise', array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Download Excel</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Milk Sales</span>
            <p class="text-xl font-black text-slate-800 mt-0.5">{{ number_format($totalLiters, 2) }} <span class="text-xs font-semibold text-slate-500">Ltr</span></p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Sales Billed</span>
            <p class="text-xl font-black text-emerald-700 mt-0.5">₹ {{ number_format($totalAmount, 2) }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Paid</span>
            <p class="text-xl font-black text-emerald-600 mt-0.5">₹ {{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Remaining Balance</span>
            <p class="text-xl font-black text-rose-600 mt-0.5">₹ {{ number_format($totalBalance, 2) }}</p>
        </div>
    </div>

    <!-- Customer Grouped Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Customer-wise Milk Sales Breakdown</h4>
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition inline-flex items-center gap-1 shadow-2xs">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3">Customer / Buyer</th>
                        <th class="py-2.5 px-3">Phone</th>
                        <th class="py-2.5 px-3 text-right">Total Entries</th>
                        <th class="py-2.5 px-3 text-right">Total Liters</th>
                        <th class="py-2.5 px-3 text-right">Avg Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total Billed (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total Paid (₹)</th>
                        <th class="py-2.5 px-3 text-right">Balance Due (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($customerSales as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900 block">{{ $item['name'] }}</span>
                                <span class="text-[10px] font-mono text-slate-400">Code: {{ $item['code'] }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-600">{{ $item['phone'] ?: '-' }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ $item['entries_count'] }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">{{ number_format($item['liters'], 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">₹ {{ number_format($item['avg_rate'], 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">₹ {{ number_format($item['total_amount'], 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-600">₹ {{ number_format($item['paid_amount'], 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $item['balance_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                ₹ {{ number_format($item['balance_amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No customer sales records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
