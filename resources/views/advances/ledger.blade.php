@extends('layouts.app')

@section('title', 'Advance Ledger - ' . $farmer->farmer_code . ' ' . $farmer->name)
@section('breadcrumb', 'Advance Ledger')
@section('header_title', 'Advance Ledger - ' . $farmer->farmer_code . ' ' . $farmer->name)

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('advances.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back</span>
        </a>
        <button type="button" @click="showReceiveModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
            <span>$ Receive</span>
        </button>
        <a href="{{ route('advances.ledger.print', $farmer) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-xl transition">
            <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-600"></i>
            <span>Print</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showReceiveModal: false, showEditModal: false, editForm: {} }">

    <!-- Advance Ledger Card matching media_1791384832203.png -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header Strip matching reference -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('advances.index') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Back</span>
                </a>
                <h3 class="text-sm font-bold text-slate-900">
                    Advance Ledger - <span class="font-mono text-emerald-700">{{ $farmer->farmer_code }}</span> {{ $farmer->name }}
                </h3>
            </div>

            <div class="flex items-center gap-2">
                <!-- Search within ledger -->
                <form method="GET" action="{{ route('advances.ledger', $farmer) }}" class="flex items-center gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search voucher, notes..." 
                        class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                    >
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Search
                    </button>
                </form>

                <button type="button" @click="showReceiveModal = true" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center gap-1">
                    <span>$ Receive</span>
                </button>
                <a href="{{ route('advances.ledger.print', $farmer) }}" target="_blank" class="px-3.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Print</span>
                </a>
            </div>
        </div>

        <!-- Ledger Table matching exact columns in media_1791384832203.png -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center">SrNo</th>
                        <th class="py-3 px-3">Date</th>
                        <th class="py-3 px-3">Voucher</th>
                        <th class="py-3 px-3">Remark</th>
                        <th class="py-3 px-3">Advance Amount</th>
                        <th class="py-3 px-3">Paid</th>
                        <th class="py-3 px-3">Paid Date</th>
                        <th class="py-3 px-3">Interest Rate</th>
                        <th class="py-3 px-3">Principal Balance</th>
                        <th class="py-3 px-3">Interest Balance</th>
                        <th class="py-3 px-3">Total Balance</th>
                        <th class="py-3 px-3">Payment Mode</th>
                        <th class="py-3 px-3 text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($advances as $index => $adv)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- SrNo -->
                            <td class="py-3 px-3 text-center text-slate-500 font-medium">
                                {{ $index + 1 }}
                            </td>

                            <!-- Date (e.g. 08 Oct 2026) -->
                            <td class="py-3 px-3 font-mono text-slate-800">
                                {{ $adv->advance_date->format('d M Y') }}
                            </td>

                            <!-- Voucher (e.g. 001) -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                {{ $adv->voucher_no ?: str_pad($adv->id, 3, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Remark (e.g. -PAID BY DR) -->
                            <td class="py-3 px-3 text-slate-700">
                                {{ $adv->notes ?: '—' }}
                            </td>

                            <!-- Advance Amount (Rs. 500.00) -->
                            <td class="py-3 px-3 font-bold text-slate-900">
                                Rs. {{ number_format($adv->amount, 2) }}
                            </td>

                            <!-- Paid (Rs. 0.00) -->
                            <td class="py-3 px-3 font-semibold text-emerald-700">
                                Rs. {{ number_format($adv->total_paid, 2) }}
                            </td>

                            <!-- Paid Date (-) -->
                            <td class="py-3 px-3 text-slate-500">
                                {{ $adv->paid_date ? $adv->paid_date->format('d M Y') : '-' }}
                            </td>

                            <!-- Interest Rate (0.00%) -->
                            <td class="py-3 px-3 text-slate-600">
                                {{ number_format($adv->interest_rate ?? 0, 2) }}%
                            </td>

                            <!-- Principal Balance (Rs. 500.00) -->
                            <td class="py-3 px-3 font-bold text-slate-800">
                                Rs. {{ number_format($adv->principal_balance, 2) }}
                            </td>

                            <!-- Interest Balance (Rs. 0.00) -->
                            <td class="py-3 px-3 text-slate-500">
                                Rs. {{ number_format($adv->interest_balance ?? 0, 2) }}
                            </td>

                            <!-- Total Balance (in red: Rs. 500.00) -->
                            <td class="py-3 px-3 font-bold text-rose-600">
                                Rs. {{ number_format($adv->total_balance, 2) }}
                            </td>

                            <!-- Payment Mode (Cash) -->
                            <td class="py-3 px-3 text-slate-700">
                                {{ $adv->payment_mode ?: 'Cash' }}
                            </td>

                            <!-- Action (Edit Pencil & Delete Trash matching screenshot) -->
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            @click="editForm = {
                                                id: {{ $adv->id }},
                                                date: '{{ $adv->advance_date->format('Y-m-d') }}',
                                                voucher: '{{ $adv->voucher_no }}',
                                                amount: '{{ $adv->amount }}',
                                                interest: '{{ $adv->interest_rate }}',
                                                mode: '{{ $adv->payment_mode }}',
                                                remark: '{{ addslashes($adv->notes ?? '') }}'
                                            }; showEditModal = true"
                                            title="Edit Voucher" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('advances.destroy', $adv) }}" method="POST" class="inline" onsubmit="return confirm('Delete advance voucher #{{ $adv->voucher_no }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Delete Voucher" 
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-8 text-center text-slate-400">
                                No advance entries on record for this farmer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Summary Row matching exact Total line in media_1791384832203.png -->
                @if($advances->count() > 0)
                    <tfoot class="bg-slate-100/90 font-bold text-slate-900 border-t-2 border-slate-300">
                        <tr>
                            <td class="py-3 px-3 text-center">Total</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">Rs. {{ number_format($totalAdvance, 2) }}</td>
                            <td class="py-3 px-3 text-emerald-700">Rs. {{ number_format($totalPaid, 2) }}</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">Rs. {{ number_format($totalPrincipal, 2) }}</td>
                            <td class="py-3 px-3">Rs. {{ number_format($totalInterest, 2) }}</td>
                            <td class="py-3 px-3 text-rose-600">Rs. {{ number_format($totalBalance, 2) }}</td>
                            <td class="py-3 px-3">-</td>
                            <td class="py-3 px-3">-</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Modal: Receive Payment for this farmer -->
    <div x-show="showReceiveModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showReceiveModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showReceiveModal = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Receive Advance Payment - {{ $farmer->name }}</h3>
                <button type="button" @click="showReceiveModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('advances.receive') }}" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Select Advance Voucher *</label>
                    <select name="advance_id" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg">
                        @foreach($advances->where('total_balance', '>', 0) as $a)
                            <option value="{{ $a->id }}">
                                Voucher #{{ $a->voucher_no }} (Dues: Rs. {{ number_format($a->total_balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Receive Amount (Rs.) *</label>
                        <input type="number" step="0.01" min="0.01" name="receive_amount" required placeholder="Amount" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Payment Mode</label>
                        <select name="payment_mode" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" rows="2" placeholder="Remark / payment notes" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showReceiveModal = false" class="px-4 py-1.5 font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="px-5 py-1.5 font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Receive</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Voucher -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showEditModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showEditModal = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Edit Advance Voucher</h3>
                <button type="button" @click="showEditModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/advances/' + editForm.id" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" name="advance_date" x-model="editForm.date" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Voucher No</label>
                        <input type="text" name="voucher_no" x-model="editForm.voucher" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" x-model="editForm.amount" required class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="editForm.mode" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" x-model="editForm.interest" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" x-model="editForm.remark" rows="2" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-1.5 font-semibold text-slate-600">Cancel</button>
                    <button type="submit" class="px-5 py-1.5 font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
