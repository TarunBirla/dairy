@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Overview')
@section('header_title', 'Dairy Operations Dashboard')

@section('header_action')
    <a href="{{ route('collections.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>New Milk Entry</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Today Collection Liters -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-400/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <span>TODAY COLLECTION</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="milk" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ number_format($todayCollectionLiters, 1) }}</span>
                    <span class="text-xs font-semibold text-slate-500">Liters</span>
                </div>
                <div class="mt-2 text-xs flex items-center gap-3 text-slate-500 border-t border-slate-100 pt-2">
                    <span>M: <b>{{ (float)$todayMorningLiters }} L</b></span>
                    <span>•</span>
                    <span>E: <b>{{ (float)$todayEveningLiters }} L</b></span>
                    <span>•</span>
                    <span class="text-emerald-700 font-bold">₹ {{ number_format($todayCollectionAmount, 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Today Deliveries -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <span>TODAY DELIVERIES</span>
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="bike" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $deliveryCompleted }} / {{ $deliveryTotal }}</span>
                    <span class="text-xs font-semibold text-slate-500">Stops</span>
                </div>
                <div class="mt-2 text-xs flex items-center gap-3 text-slate-500 border-t border-slate-100 pt-2">
                    <span class="text-emerald-600 font-semibold">Done: {{ $deliveryCompleted }}</span>
                    <span>•</span>
                    <span class="text-amber-600 font-semibold">Pending: {{ $deliveryPending }}</span>
                    <span>•</span>
                    <span class="text-slate-400">Skip: {{ $deliverySkipped }}</span>
                </div>
            </div>
        </div>

        <!-- Today Sales & Cash -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <span>TODAY POS SALES</span>
                <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-slate-400">₹</span>
                    <span class="text-3xl font-extrabold text-slate-900">{{ number_format($todaySales, 0) }}</span>
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-slate-500 border-t border-slate-100 pt-2">
                    <span>Expenses: <b>₹ {{ number_format($todayExpenses, 0) }}</b></span>
                    <a href="{{ route('pos.index') }}" class="text-emerald-600 font-semibold hover:underline">Open POS &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Outstanding Dues / Payables -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <span>FINANCIAL BALANCES</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Customer Dues (Receivable):</span>
                        <span class="font-bold text-amber-600">₹ {{ number_format($customerDues, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Farmer Dues (Payable):</span>
                        <span class="font-bold text-indigo-600">₹ {{ number_format($farmerPayable, 0) }}</span>
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-slate-400 border-t border-slate-100 pt-2">
                    {{ $totalCustomers }} Active Customers • {{ $totalFarmers }} Farmers
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Shortcuts Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center gap-3">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2">Quick Actions:</span>
        
        <a href="{{ route('collections.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition">
            <i data-lucide="milk" class="w-3.5 h-3.5 text-emerald-600"></i>
            <span>Record Milk</span>
        </a>

        <a href="{{ route('farmers.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition">
            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Add Farmer</span>
        </a>

        <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition">
            <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Add Customer</span>
        </a>

        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-xl transition">
            <i data-lucide="store" class="w-3.5 h-3.5 text-teal-600"></i>
            <span>POS Billing Counter</span>
        </a>

        <a href="{{ route('delivery.board') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition">
            <i data-lucide="bike" class="w-3.5 h-3.5 text-indigo-600"></i>
            <span>Daily Delivery Board</span>
        </a>

        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition">
            <i data-lucide="boxes" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Products & Stock ({{ $totalProducts }})</span>
        </a>
    </div>

    <!-- 2 Column Split: Recent Collections & Today Deliveries -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Milk Collections -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="milk" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Recent Milk Procurement</h3>
                    </div>
                    <a href="{{ route('collections.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">View All &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="text-[10px] uppercase font-bold text-slate-400 bg-slate-50">
                            <tr>
                                <th class="py-2 px-2.5">Farmer</th>
                                <th class="py-2 px-2.5">Shift</th>
                                <th class="py-2 px-2.5">Qty (L)</th>
                                <th class="py-2 px-2.5">Fat/SNF</th>
                                <th class="py-2 px-2.5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentCollections as $col)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-2.5 font-medium text-slate-900">
                                        {{ $col->farmer->name }}
                                        <span class="block text-[10px] text-slate-400">{{ $col->farmer->farmer_code }}</span>
                                    </td>
                                    <td class="py-2.5 px-2.5 capitalize">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $col->shift === 'morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $col->shift }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2.5 font-bold">{{ $col->quantity_liters }} L</td>
                                    <td class="py-2.5 px-2.5 text-slate-500">{{ $col->fat }}% / {{ $col->snf }}%</td>
                                    <td class="py-2.5 px-2.5 text-right font-bold text-emerald-700">₹ {{ number_format($col->net_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No collections recorded today</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 text-right">
                <a href="{{ route('collections.create') }}" class="text-xs text-emerald-600 font-semibold hover:underline">+ Record New Collection Entry</a>
            </div>
        </div>

        <!-- Today Delivery Runs -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="bike" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Today Delivery Operations</h3>
                    </div>
                    <a href="{{ route('delivery.board') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Route Board &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="text-[10px] uppercase font-bold text-slate-400 bg-slate-50">
                            <tr>
                                <th class="py-2 px-2.5">Customer</th>
                                <th class="py-2 px-2.5">Product</th>
                                <th class="py-2 px-2.5">Qty</th>
                                <th class="py-2 px-2.5">Status</th>
                                <th class="py-2 px-2.5 text-right">Cash Recv</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentDeliveries as $del)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-2.5 font-medium text-slate-900">
                                        {{ $del->customer->name }}
                                        <span class="block text-[10px] text-slate-400">{{ $del->customer->locality }}</span>
                                    </td>
                                    <td class="py-2.5 px-2.5 text-slate-700">{{ $del->product ? $del->product->name : 'Milk' }}</td>
                                    <td class="py-2.5 px-2.5 font-bold">{{ $del->quantity }}</td>
                                    <td class="py-2.5 px-2.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                            {{ $del->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($del->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                            {{ ucfirst($del->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2.5 text-right font-bold text-slate-800">
                                        {{ $del->cash_collected > 0 ? '₹ ' . number_format($del->cash_collected, 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">No delivery schedules generated today</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 text-right">
                <a href="{{ route('delivery.boy-app') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Open Delivery Boy Mobile Screen &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Low Stock Alert Banner (if any) -->
    @if($lowStockProducts->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900">Inventory Alert: {{ $lowStockProducts->count() }} Products Low or Out of Stock</h4>
                    <p class="text-[11px] text-amber-700">
                        {{ $lowStockProducts->pluck('name')->join(', ') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold px-3 py-1.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 shadow-2xs">Manage Stock</a>
        </div>
    @endif

</div>
@endsection
