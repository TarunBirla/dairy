@extends('layouts.app')

@section('title', 'Customers Directory')
@section('breadcrumb', 'Customers')
@section('header_title', 'Customer & Client Accounts')

@section('header_action')
    <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Customer</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL CUSTOMERS</span>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE SUBSCRIPTION USERS</span>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $activeCustomers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OUTSTANDING DUES</span>
            <p class="text-3xl font-extrabold text-amber-600 mt-2">₹ {{ number_format($totalDues, 2) }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('customers.index') }}" class="w-full sm:w-96 flex items-center relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search name, CUST-xxx, phone, locality..." 
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
            >
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Add Milk Subscription</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Locality / Route</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Balance Dues</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $c->customer_code }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $c) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                    {{ $c->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">{{ $c->phone }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $c->locality }}
                                <span class="block text-[10px] text-slate-400">{{ $c->route ? $c->route->name : 'Unassigned' }}</span>
                            </td>
                            <td class="py-3.5 px-4 capitalize font-medium">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $c->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-sm {{ $c->current_balance > 0 ? 'text-amber-600' : 'text-slate-700' }}">
                                ₹ {{ number_format($c->current_balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $c->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('customers.show', $c) }}" class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                        View Profile
                                    </a>
                                    <a href="{{ route('customers.edit', $c) }}" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">No customers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
