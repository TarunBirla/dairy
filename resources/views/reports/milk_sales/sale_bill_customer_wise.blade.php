@extends('layouts.app')

@section('title', 'Sale Bill Customer Wise Report')
@section('breadcrumb', 'Reports -> Milk Sale Reports -> Sale Bill Customer Wise')
@section('header_title', 'Sale Bill Customer Wise Report')

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb Bar -->
    <div class="flex items-center justify-between pb-1">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#005c53] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Reports -> Milk Sale Reports -> Sale Bill Customer Wise</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.milk-sales.sale-bill-customer-wise') }}" class="flex flex-wrap items-center gap-3 text-xs">
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

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Bill Date From :</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Bill Date To :</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <div class="flex items-center gap-2 pt-4">
                <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview</span>
                </button>
                <a href="{{ route('reports.milk-sales.sale-bill-customer-wise', array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Download Excel</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Bills Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Customer Sale Invoices & Delivery Slips</h4>
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition inline-flex items-center gap-1 shadow-2xs">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3">Bill / Slip No</th>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Customer Name</th>
                        <th class="py-2.5 px-3">Shift & Milk</th>
                        <th class="py-2.5 px-3 text-right">Liters</th>
                        <th class="py-2.5 px-3 text-right">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total Bill (₹)</th>
                        <th class="py-2.5 px-3 text-right">Paid (₹)</th>
                        <th class="py-2.5 px-3 text-right">Balance (₹)</th>
                        <th class="py-2.5 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($bills as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $b->sale_number }}</td>
                            <td class="py-2.5 px-3 font-mono">{{ $b->sale_date ? $b->sale_date->format('d/m/Y') : '-' }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">{{ $b->buyer->name ?? 'Walk-in Retail' }}</td>
                            <td class="py-2.5 px-3 capitalize text-slate-600">{{ $b->shift }} ({{ $b->milk_type }})</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800">{{ number_format($b->quantity_liters, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">₹ {{ number_format($b->rate_per_liter, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">₹ {{ number_format($b->total_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-600">₹ {{ number_format($b->paid_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $b->balance_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                ₹ {{ number_format($b->balance_amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <a href="{{ route('milk-sales.slip', $b->id) }}" target="_blank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition inline-flex items-center gap-1">
                                    <i data-lucide="receipt" class="w-3 h-3 text-slate-500"></i>
                                    <span>Slip</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">No customer sale bills found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="p-3.5 border-t border-slate-100">
                {{ $bills->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
