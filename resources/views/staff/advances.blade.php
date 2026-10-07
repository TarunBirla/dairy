@extends('layouts.app')

@section('title', 'Staff Advances')
@section('breadcrumb', 'Advances')
@section('header_title', 'Staff / Salary Advance Records')

@section('header_action')
    <button type="button" onclick="document.getElementById('advModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
        + Record Advance Payment
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Total Advance Banner -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Approved Advances Given</span>
            <p class="text-3xl font-black text-amber-600 mt-1">₹ {{ number_format($totalAdvances, 2) }}</p>
        </div>
        <button type="button" onclick="document.getElementById('advModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs">
            + New Advance Entry
        </button>
    </div>

    <!-- Advances Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4">Reason / Notes</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($advances as $a)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono">{{ $a->advance_date->format('d M Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $a->user->name ?? 'Staff' }}</td>
                            <td class="py-3.5 px-4 uppercase font-bold text-slate-600">{{ $a->payment_mode }}</td>
                            <td class="py-3.5 px-4 text-slate-500">{{ $a->reason ?? '—' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $a->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $a->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-sm text-amber-600">
                                ₹ {{ number_format($a->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No staff advances recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="advModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Record Staff Advance</h3>
            <form action="{{ route('staff.advances.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Staff Member *</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ str_replace('_', ' ', $s->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) *</label>
                    <input type="number" step="50" name="amount" required placeholder="e.g. 2000" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold text-amber-600">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date *</label>
                        <input type="date" name="advance_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mode</label>
                        <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Notes</label>
                    <input type="text" name="reason" placeholder="e.g. Festival advance" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('advModal').classList.add('hidden')" class="px-3.5 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl">Save Advance</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
