@extends('layouts.app')

@section('title', 'Farmer Dashboard')
@section('breadcrumb', 'Farmer Portal')
@section('header_title', 'My Milk Supply Passbook')

@section('content')
<div class="space-y-6">

    <!-- Farmer Welcome Card -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Kisan / Supplier Portal</span>
            <h2 class="text-2xl font-black mt-1">Namaste, {{ $farmer->name }}!</h2>
            <p class="text-xs text-emerald-100 mt-1">Farmer Code: <b>{{ $farmer->farmer_code }}</b> • Village: <b>{{ $farmer->village }}</b> • Animal: <b class="capitalize">{{ $farmer->animal_type }}</b></p>
        </div>
        <div class="bg-white/10 backdrop-blur-xs p-4 rounded-2xl border border-white/20 text-right">
            <span class="text-[10px] uppercase font-bold text-emerald-200 block">Current Ledger Balance</span>
            <span class="text-3xl font-black block mt-0.5">₹ {{ number_format($farmer->current_balance, 2) }}</span>
            <span class="text-[11px] text-emerald-100 block">Amount to be received in next settlement</span>
        </div>
    </div>

    <!-- 2 Columns: Recent Collections & Financial Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Milk Collections -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-3">Recent Milk Supplies (Daily Readings)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Qty (L)</th>
                            <th class="py-2.5 px-3">FAT / SNF</th>
                            <th class="py-2.5 px-3 text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($myCollections as $mc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 px-3 font-mono">{{ $mc->collection_date->format('d M') }}</td>
                                <td class="py-2.5 px-3 capitalize font-bold {{ $mc->shift === 'morning' ? 'text-amber-600' : 'text-indigo-600' }}">{{ $mc->shift }}</td>
                                <td class="py-2.5 px-3 font-bold">{{ $mc->quantity_liters }} L</td>
                                <td class="py-2.5 px-3">{{ $mc->fat }}% / {{ $mc->snf }}%</td>
                                <td class="py-2.5 px-3 text-right font-black text-emerald-700">₹ {{ number_format($mc->net_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">No milk records yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ledger Statement -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-3">Passbook Entries & Advances</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Details</th>
                            <th class="py-2.5 px-3 text-right">Credit / Debit (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($myLedgers as $ml)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2.5 px-3 font-mono">{{ $ml->transaction_date->format('d M') }}</td>
                                <td class="py-2.5 px-3 text-slate-700">{{ $ml->description }}</td>
                                <td class="py-2.5 px-3 text-right font-bold {{ $ml->type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $ml->type === 'credit' ? '+' : '-' }}₹ {{ number_format($ml->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">No transactions recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
