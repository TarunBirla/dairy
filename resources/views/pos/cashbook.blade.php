@extends('layouts.app')

@section('title', 'Counter Cashbook')
@section('breadcrumb', 'Cashbook')
@section('header_title', 'Counter Cashbook & Day-end Reconciliation')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Today's Cashbook Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Today's Counter Cashbook ({{ $today->format('d M Y') }})</h3>
                <p class="text-xs text-slate-500">Record physical counter drawer opening cash, tally daily collections, and calculate variance.</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $cashbook && $cashbook->status === 'closed' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $cashbook ? ucfirst($cashbook->status) : 'Open Drawer' }}
            </span>
        </div>

        <form action="{{ route('pos.cashbook.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Cash in Drawer (₹)</label>
                    <input type="number" step="10" name="opening_cash" value="{{ old('opening_cash', $cashbook ? $cashbook->opening_cash : 2000) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Today Cash Counter Sales (₹)</label>
                    <input type="number" step="10" name="cash_sales" value="{{ old('cash_sales', $cashbook ? $cashbook->cash_sales : 3450) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold text-emerald-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cash Paid Expenses (₹)</label>
                    <input type="number" step="10" name="cash_expenses" value="{{ old('cash_expenses', $cashbook ? $cashbook->cash_expenses : 150) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold text-rose-600">
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Actual Physical Counted Cash (₹) *</label>
                    <input type="number" step="10" name="actual_counted_cash" value="{{ old('actual_counted_cash', $cashbook ? $cashbook->actual_counted_cash : 5300) }}" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg font-black text-slate-900 focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Count notes & coins physically in cash drawer before closing.</span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cashier Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Counter closed with zero discrepancy" value="{{ $cashbook ? $cashbook->notes : '' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs">
                    Save & Reconcile Cashbook
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Cashbooks History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h4 class="text-xs font-bold text-slate-800">Historical Cashbook Closures</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Opening (₹)</th>
                        <th class="py-2.5 px-4">Sales (₹)</th>
                        <th class="py-2.5 px-4">Expenses (₹)</th>
                        <th class="py-2.5 px-4">Closing Expected (₹)</th>
                        <th class="py-2.5 px-4">Actual Counted (₹)</th>
                        <th class="py-2.5 px-4">Variance (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentCashbooks as $cb)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $cb->entry_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">₹ {{ number_format($cb->opening_cash, 2) }}</td>
                            <td class="py-3 px-4 text-emerald-700 font-bold">₹ {{ number_format($cb->cash_sales, 2) }}</td>
                            <td class="py-3 px-4 text-rose-600">₹ {{ number_format($cb->cash_expenses, 2) }}</td>
                            <td class="py-3 px-4 font-bold">₹ {{ number_format($cb->closing_cash, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">₹ {{ number_format($cb->actual_counted_cash, 2) }}</td>
                            <td class="py-3 px-4 font-bold {{ $cb->variance == 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $cb->variance == 0 ? 'Exact (₹ 0)' : '₹ ' . number_format($cb->variance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No previous cashbooks recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
