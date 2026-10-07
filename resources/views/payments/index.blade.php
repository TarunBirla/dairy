@extends('layouts.app')

@section('title', 'Payments Received')
@section('breadcrumb', 'Payments')
@section('header_title', 'Customer Payments & Reconciliation')

@section('header_action')
    <button type="button" onclick="document.getElementById('newPayModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Record Payment</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Payments Collected</span>
            <p class="text-3xl font-black text-slate-900 mt-1">₹ {{ number_format($totalCollected, 2) }}</p>
        </div>
        <button type="button" onclick="document.getElementById('newPayModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs">
            + Quick Payment Entry
        </button>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Receipt #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Customer Name</th>
                        <th class="py-3 px-4">Payment Mode</th>
                        <th class="py-3 px-4">Transaction Ref</th>
                        <th class="py-3 px-4 text-right">Amount (₹)</th>
                        <th class="py-3 px-4">Collected By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $p->payment_number }}</td>
                            <td class="py-3.5 px-4 font-mono">{{ $p->payment_date->format('d M Y') }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $p->customer) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                    {{ $p->customer->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $p->customer->customer_code }}</span>
                            </td>
                            <td class="py-3.5 px-4 uppercase font-bold text-emerald-700">{{ $p->payment_mode }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px]">{{ $p->transaction_reference ?? 'Cash Receipt' }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-sm text-slate-900">
                                ₹ {{ number_format($p->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $p->collector ? $p->collector->name : 'Staff' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No payment receipts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- Record Payment Modal -->
    <div id="newPayModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Record Customer Payment</h3>
                <button onclick="document.getElementById('newPayModal').classList.add('hidden')" class="text-slate-400">&times;</button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} (Due: ₹{{ number_format($c->current_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) *</label>
                    <input type="number" step="1" name="amount" required placeholder="e.g. 1500" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Mode *</label>
                    <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                        <option value="bank_transfer">Bank Transfer (NEFT/IMPS)</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reference / UTR</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Cash voucher" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('newPayModal').classList.add('hidden')" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
