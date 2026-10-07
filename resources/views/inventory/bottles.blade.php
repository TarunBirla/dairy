@extends('layouts.app')

@section('title', 'Bottle & Crate Tracking')
@section('breadcrumb', 'Bottles')
@section('header_title', 'Glass Bottle & Container Tracking')

@section('header_action')
    <button type="button" onclick="document.getElementById('bottleModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Record Bottle Return / Issue</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL ISSUED</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalIssued }} Bottles</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL RETURNED</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $totalReturned }} Bottles</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BROKEN / LOSS</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $totalBroken }} Bottles</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">OUTSTANDING WITH CLIENTS</span>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $totalBalance }} Bottles</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Customer Bottle Deposit Ledgers</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Locality</th>
                        <th class="py-3 px-4">Issued Count</th>
                        <th class="py-3 px-4">Returned Count</th>
                        <th class="py-3 px-4">Broken</th>
                        <th class="py-3 px-4 font-bold text-slate-900">Current Balance</th>
                        <th class="py-3 px-4 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bottles as $b)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $b->customer) }}" class="hover:text-emerald-700">
                                    {{ $b->customer->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $b->customer->customer_code }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $b->customer->locality }}</td>
                            <td class="py-3.5 px-4">{{ $b->issued_count }}</td>
                            <td class="py-3.5 px-4 text-emerald-600 font-semibold">{{ $b->returned_count }}</td>
                            <td class="py-3.5 px-4 text-rose-600">{{ $b->broken_count }}</td>
                            <td class="py-3.5 px-4 font-black text-sm text-amber-700">
                                {{ $b->balance_bottles }} Bottles
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                <button type="button" onclick="document.getElementById('bottleModal').classList.remove('hidden')" class="px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No bottle tracking records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Update Bottle Modal -->
    <div id="bottleModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Record Bottle Movement</h3>
                <button onclick="document.getElementById('bottleModal').classList.add('hidden')" class="text-slate-400">&times;</button>
            </div>
            <form action="{{ route('inventory.bottles.update') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->locality }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Action Type *</label>
                    <select name="action_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="return">Received Empty Bottle Return (- Outstanding)</option>
                        <option value="issue">Issued Extra Bottles (+ Outstanding)</option>
                        <option value="breakage">Bottle Damaged / Broken</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bottle Count *</label>
                    <input type="number" step="1" name="count" required value="1" min="1" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('bottleModal').classList.add('hidden')" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Save Record</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
