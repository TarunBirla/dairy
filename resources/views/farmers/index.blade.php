@extends('layouts.app')

@section('title', 'Farmers Master')
@section('breadcrumb', 'Farmers')
@section('header_title', 'Farmer & Milk Supplier Management')

@section('header_action')
    <a href="{{ route('farmers.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Register Farmer</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL FARMERS</span>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalFarmers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE SUPPLIERS</span>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $activeFarmers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PAYABLE DUES</span>
            <p class="text-3xl font-extrabold text-indigo-700 mt-2">₹ {{ number_format($totalPayable, 2) }}</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('farmers.index') }}" class="w-full sm:w-96 flex items-center relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search by name, code FAR-xxx, village..." 
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
            >
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('settlements.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Generate Settlement</span>
            </a>
        </div>
    </div>

    <!-- Farmers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Farmer Name</th>
                        <th class="py-3 px-4">Village / Location</th>
                        <th class="py-3 px-4">Animal Type</th>
                        <th class="py-3 px-4">Bank / UPI</th>
                        <th class="py-3 px-4">Ledger Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($farmers as $farmer)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $farmer->farmer_code }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('farmers.show', $farmer) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                    {{ $farmer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">{{ $farmer->phone ?? 'No phone' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $farmer->village ?? '—' }}
                                <span class="block text-[10px] text-slate-400">{{ $farmer->collectionCenter ? $farmer->collectionCenter->name : '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 capitalize">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $farmer->animal_type === 'buffalo' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $farmer->animal_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                @if($farmer->account_number)
                                    <span class="font-semibold text-slate-700">{{ $farmer->bank_name }}</span>
                                    <span class="block font-mono text-[10px]">A/C: {{ substr($farmer->account_number, -4) ? '••••' . substr($farmer->account_number, -4) : '' }}</span>
                                @elseif($farmer->upi_id)
                                    <span class="font-mono text-emerald-700">{{ $farmer->upi_id }}</span>
                                @else
                                    <span class="text-slate-400">Cash Mode</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-sm text-indigo-700">
                                ₹ {{ number_format($farmer->current_balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $farmer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $farmer->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('farmers.show', $farmer) }}" title="View Passbook & Ledger" class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                        Passbook
                                    </a>
                                    <a href="{{ route('farmers.edit', $farmer) }}" title="Edit Profile" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                No farmers registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($farmers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $farmers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
