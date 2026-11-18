@extends('layouts.app')

@section('title', 'Deduction Wallet - ' . $farmer->name)
@section('breadcrumb', 'Farmer Deduction Wallet')
@section('header_title', 'Farmer Deduction Wallet: ' . $farmer->name)

@section('header_action')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('wallets.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>All Wallets</span>
        </a>
        <a href="{{ route('farmer-invoices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
            <span>Invoices</span>
        </a>
        <form method="POST" action="{{ route('wallets.recalculate', $farmer->id) }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition" title="Recalculate balance from ledger">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Recalculate</span>
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    addModal: false,
    adjustModal: false,
    selectedCategory: 'Cattle Feed',
    addAmount: '',
    addRemarks: '',
    adjustType: 'credit',
    adjustAmount: '',
    adjustRemarks: ''
}">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Farmer Wallet Overview Card -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-lg border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-400/20 text-amber-300 border border-amber-400/30 flex items-center justify-center font-bold text-xl shrink-0">
                    {{ substr($farmer->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-white">{{ $farmer->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-white/10 border border-white/15 text-slate-200">
                            {{ $farmer->farmer_code }}
                        </span>
                    </div>
                    @if(!empty($farmer->name_hi))
                        <p class="text-xs text-slate-300 mt-0.5">{{ $farmer->name_hi }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                        @if($farmer->phone)
                            <span>Phone: <b class="text-slate-200">{{ $farmer->phone }}</b></span>
                        @endif
                        <span>Route: <b class="text-slate-200">{{ $farmer->route->name ?? 'Direct' }}</b></span>
                        <span>Bank: <b class="text-slate-200">{{ $farmer->bank_name ?: 'N/A' }}</b></span>
                        @if($farmer->account_number)
                            <span>A/C: <b class="font-mono text-slate-200">{{ $farmer->account_number }}</b></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Balance Badge & Action Buttons -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-4">
                <div>
                    <span class="text-[11px] font-medium text-slate-300 uppercase tracking-wider block">Current Outstanding Balance</span>
                    <span class="text-3xl font-black font-mono {{ $total_balance > 0 ? 'text-amber-300' : 'text-emerald-300' }} block mt-0.5">
                        ₹{{ number_format($total_balance, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400">
                        {{ $total_balance > 0 ? 'To be deducted in upcoming invoices' : 'All previous deductions cleared' }}
                    </span>
                </div>
                <div class="flex flex-col gap-2 shrink-0">
                    <button type="button" @click="addModal = true" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Add Deduction</span>
                    </button>
                    <button type="button" @click="adjustModal = true" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl border border-white/15 transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>Manual Adjust</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Category-Wise Breakdown Grid -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Category-Wise Outstanding Balances</h3>
            <span class="text-xs text-slate-400">{{ count($category_balances) }} Active Categories</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @forelse($category_balances as $catName => $catBal)
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 block truncate" title="{{ $catName }}">{{ $catName }}</span>
                        <span class="text-lg font-black font-mono {{ $catBal > 0 ? 'text-amber-600' : 'text-slate-400' }} block mt-1">
                            ₹{{ number_format($catBal, 2) }}
                        </span>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">Status</span>
                        @if($catBal > 0)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Due</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Nil</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-xs">
                    No active deduction categories recorded.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Complete Transaction Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
                    <i data-lucide="history" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Complete Wallet Transaction History</h3>
                    <p class="text-[11px] text-slate-500">Immutable ledger of all additions, invoice deductions, adjustments, and reversals</p>
                </div>
            </div>
            <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                {{ $transactions->total() }} Transactions
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                        <th class="py-3 px-3.5">Tx ID</th>
                        <th class="py-3 px-3.5">Date & Time</th>
                        <th class="py-3 px-3.5">Type</th>
                        <th class="py-3 px-3.5">Category</th>
                        <th class="py-3 px-3.5 text-right">Amount</th>
                        <th class="py-3 px-3.5 text-right">Before</th>
                        <th class="py-3 px-3.5 text-right">After</th>
                        <th class="py-3 px-3.5">Reference / Invoice</th>
                        <th class="py-3 px-3.5">Remarks / Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2 px-3.5 font-mono text-[11px] text-slate-400">#{{ $t->id }}</td>
                            <td class="py-2 px-3.5 font-mono text-[11px] text-slate-600">
                                {{ $t->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="py-2 px-3.5">
                                @if($t->transaction_type === 'credit')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        <span>Added (Credit)</span>
                                    </span>
                                @elseif($t->transaction_type === 'debit')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        <span>Deducted (Debit)</span>
                                    </span>
                                @elseif($t->transaction_type === 'reversal')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                        <span>Reversal</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-3.5 font-semibold text-slate-800">{{ $t->category }}</td>
                            <td class="py-2 px-3.5 text-right font-mono font-bold text-xs {{ $t->transaction_type === 'debit' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $t->transaction_type === 'debit' ? '-' : '+' }}₹{{ number_format($t->amount, 2) }}
                            </td>
                            <td class="py-2 px-3.5 text-right font-mono text-slate-500 text-[11px]">
                                ₹{{ number_format($t->balance_before, 2) }}
                            </td>
                            <td class="py-2 px-3.5 text-right font-mono font-bold text-slate-900 text-[11px]">
                                ₹{{ number_format($t->balance_after, 2) }}
                            </td>
                            <td class="py-2 px-3.5">
                                @if($t->invoice)
                                    <a href="{{ route('farmer-invoices.show', $t->invoice->id) }}" class="inline-flex items-center gap-1 font-mono text-xs font-bold text-sky-600 hover:text-sky-800">
                                        <i data-lucide="receipt" class="w-3 h-3"></i>
                                        <span>{{ $t->invoice->invoice_number }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">{{ $t->reference_type ? class_basename($t->reference_type) . ' #' . $t->reference_id : 'Manual / Wallet' }}</span>
                                @endif
                            </td>
                            <td class="py-2 px-3.5 text-slate-600 text-[11px]">
                                {{ $t->remarks ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                No wallet transactions recorded yet for this farmer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Add Deduction -->
    <div x-show="addModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="addModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150"
             @click.away="addModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Add Deduction to Wallet</h4>
                        <p class="text-[11px] text-slate-500">Farmer: {{ $farmer->name }} ({{ $farmer->farmer_code }})</p>
                    </div>
                </div>
                <button type="button" @click="addModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('wallets.add-deduction', $farmer->id) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deduction Category <span class="text-rose-500">*</span></label>
                    <select name="category" x-model="selectedCategory" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @foreach($availableCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deduction Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" x-model="addAmount" placeholder="e.g. 500.00" required
                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 font-mono font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Remarks / Note</label>
                    <textarea name="remarks" x-model="addRemarks" rows="2" placeholder="e.g. Cattle Feed supply, medical visit..."
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Add Deduction</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Manual Adjustment -->
    <div x-show="adjustModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="adjustModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150"
             @click.away="adjustModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Manual Wallet Adjustment</h4>
                        <p class="text-[11px] text-slate-500">Farmer: {{ $farmer->name }}</p>
                    </div>
                </div>
                <button type="button" @click="adjustModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('wallets.adjust', $farmer->id) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Adjustment Action <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-medium"
                               :class="adjustType === 'credit' ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="adjustment_type" value="credit" x-model="adjustType" class="text-amber-600">
                            <span>Increase Dues (Credit)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-medium"
                               :class="adjustType === 'debit' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="adjustment_type" value="debit" x-model="adjustType" class="text-emerald-600">
                            <span>Waive/Reduce (Debit)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500">
                        @foreach($availableCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Adjustment Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" x-model="adjustAmount" placeholder="e.g. 250.00" required
                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-mono font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Mandatory Audit Remarks <span class="text-rose-500">*</span></label>
                    <textarea name="remarks" x-model="adjustRemarks" rows="2" placeholder="Reason for adjustment..." required
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="adjustModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Apply Adjustment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection