@extends('layouts.app')

@section('title', 'Outstanding Dues & Payables')
@section('breadcrumb', 'Dues Report')
@section('header_title', 'Receivables & Payables Ageing Report')

@section('header_action')
    <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
        <span>Print Report</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL CUSTOMER DUES (RECEIVABLE)</span>
                <p class="text-3xl font-black text-amber-600 mt-1">₹ {{ number_format($totalCustomerDues, 2) }}</p>
                <span class="text-xs text-slate-400 mt-0.5 block">Money to collect from milk deliveries</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL FARMER PAYABLE (LIABILITY)</span>
                <p class="text-3xl font-black text-indigo-700 mt-1">₹ {{ number_format($totalFarmerPayable, 2) }}</p>
                <span class="text-xs text-slate-400 mt-0.5 block">Money owed to milk supplying farmers</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- 2 Column Split -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Customers with Dues -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h4 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-3">Customer Receivables List</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Customer</th>
                            <th class="py-2.5 px-3">Locality</th>
                            <th class="py-2.5 px-3 text-right">Balance Due (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customersWithDues as $c)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-3">
                                    <a href="{{ route('customers.show', $c) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                        {{ $c->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $c->customer_code }} • {{ $c->phone }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-500">{{ $c->locality }}</td>
                                <td class="py-3 px-3 text-right font-black text-amber-600 text-sm">
                                    ₹ {{ number_format($c->current_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">All customers have cleared their dues!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Farmers with Payables -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <h4 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-3">Farmer Payables List</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-3">Farmer</th>
                            <th class="py-2.5 px-3">Village</th>
                            <th class="py-2.5 px-3 text-right">Payable Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($farmersWithPayable as $f)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-3">
                                    <a href="{{ route('farmers.show', $f) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                        {{ $f->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $f->farmer_code }} • {{ $f->phone }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-500">{{ $f->village }}</td>
                                <td class="py-3 px-3 text-right font-black text-indigo-700 text-sm">
                                    ₹ {{ number_format($f->current_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400">All farmer settlements are up to date!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
