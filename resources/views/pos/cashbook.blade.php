@extends('layouts.app')

@section('title', 'Counter Cashbook')
@section('breadcrumb', 'Cashbook')
@section('header_title', 'Counter Cashbook & Day-end Reconciliation')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @php
        $savedDenoms = [];
        if (!empty($cashbook->denominations)) {
            $savedDenoms = is_array($cashbook->denominations) ? $cashbook->denominations : (json_decode($cashbook->denominations, true) ?: []);
        }
        $defaultCashSales = $cashbook ? $cashbook->cash_sales : ($todayCashSales ?? 0);
    @endphp

    <!-- Today's Cashbook Form -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" x-data="cashDenominationApp({!! json_encode($savedDenoms) !!}, {{ old('actual_counted_cash', $cashbook ? $cashbook->actual_counted_cash : '') ?: 'null' }})">
        <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Today's Counter Cashbook ({{ $today->format('d M Y') }})</h3>
                <p class="text-xs text-slate-500">Record physical counter drawer opening cash, tally daily collections, and calculate variance.</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $cashbook && $cashbook->status === 'closed' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $cashbook ? ucfirst($cashbook->status) : 'Open Drawer' }}
            </span>
        </div>

        <form action="{{ route('pos.cashbook.update') }}" method="POST" class="space-y-5">
            @csrf
            
            <!-- Opening Cash, Cash Sales, Cash Expenses -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Cash in Drawer (₹)</label>
                    <input type="number" step="any" name="opening_cash" x-model.number="openingCash" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Today Cash Counter Sales (₹)</label>
                    <input type="number" step="any" name="cash_sales" x-model.number="cashSales" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold text-emerald-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cash Paid Expenses (₹)</label>
                    <input type="number" step="any" name="cash_expenses" x-model.number="cashExpenses" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold text-rose-600">
                </div>
            </div>

            <!-- Expected Cash Summary -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600">Expected Closing Cash (Opening + Sales - Expenses):</span>
                <span class="font-mono font-black text-slate-900 text-sm">₹ <span x-text="expectedCash().toLocaleString('en-IN')"></span></span>
            </div>

            <!-- Physical Counted Cash & Notes Breakdown Section -->
            <div class="p-5 bg-gradient-to-br from-slate-50 to-emerald-50/20 rounded-2xl border border-slate-200/90 space-y-4">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-200/80">
                    <div class="flex-1">
                        <label class="block text-xs font-extrabold text-slate-900 mb-0.5 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Actual Physical Counted Cash (₹) *
                        </label>
                        <p class="text-[11px] text-slate-500">Count notes (500, 200, 100, 50, 20, 10) below to auto-calculate physical cash in drawer.</p>
                    </div>

                    <div class="w-full sm:w-60">
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 font-bold text-slate-500 text-sm">₹</span>
                            <input 
                                type="number" 
                                step="any" 
                                name="actual_counted_cash" 
                                x-model.number="actualCountedCash" 
                                required 
                                placeholder="0.00" 
                                class="w-full pl-8 pr-3 py-2 text-base border-2 border-emerald-500 rounded-xl font-black text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500/30 outline-none text-right"
                            >
                        </div>
                    </div>
                </div>

                <!-- Currency Denominations Breakdown (500, 200, 100, 50, 20, 10, Coins) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                            Denomination Breakdown / नोटों की संख्या (500, 200, 100, 50, 20, 10)
                        </span>
                        <button type="button" @click="resetDenominations()" class="text-[11px] font-bold text-slate-400 hover:text-rose-600 transition">
                            Clear Counts
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <!-- ₹500 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-stone-100 text-stone-800 border border-stone-200 shrink-0">
                                    ₹ 500
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[500]" 
                                    x-model.number="notes['500']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['500'] || 0) * 500).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- ₹200 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-amber-50 text-amber-900 border border-amber-200 shrink-0">
                                    ₹ 200
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[200]" 
                                    x-model.number="notes['200']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['200'] || 0) * 200).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- ₹100 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-indigo-50 text-indigo-900 border border-indigo-200 shrink-0">
                                    ₹ 100
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[100]" 
                                    x-model.number="notes['100']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['100'] || 0) * 100).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- ₹50 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-teal-50 text-teal-900 border border-teal-200 shrink-0">
                                    ₹ 50
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[50]" 
                                    x-model.number="notes['50']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['50'] || 0) * 50).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- ₹20 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-lime-50 text-lime-900 border border-lime-200 shrink-0">
                                    ₹ 20
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[20]" 
                                    x-model.number="notes['20']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['20'] || 0) * 20).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- ₹10 -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-14 py-1 rounded-lg text-center text-xs font-black bg-amber-100/70 text-amber-950 border border-amber-300 shrink-0">
                                    ₹ 10
                                </span>
                                <span class="text-slate-400 font-bold text-xs">&times;</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="1" 
                                    name="denominations[10]" 
                                    x-model.number="notes['10']" 
                                    @input="onDenomChange()" 
                                    placeholder="0" 
                                    class="w-16 px-2 py-1 text-center font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + ((notes['10'] || 0) * 10).toLocaleString('en-IN')"></span>
                            </div>
                        </div>

                        <!-- Coins / सिक्के -->
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs flex items-center justify-between gap-2 sm:col-span-2 lg:col-span-3 hover:border-emerald-300 transition">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="px-2.5 py-1 rounded-lg text-center text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                    Coins / सिक्के
                                </span>
                                <span class="text-slate-400 font-bold text-xs">= ₹</span>
                                <input 
                                    type="number" 
                                    min="0" 
                                    step="any" 
                                    name="denominations[coins]" 
                                    x-model.number="coinsAmount" 
                                    @input="onDenomChange()" 
                                    placeholder="Coins total amount" 
                                    class="w-40 px-3 py-1 font-bold text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none text-right"
                                >
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-slate-900" x-text="'₹ ' + (parseFloat(coinsAmount || 0)).toLocaleString('en-IN')"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Denomination Tally Summary & Variance -->
                    <div class="p-3.5 bg-white border border-emerald-200 rounded-xl flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-4 text-xs">
                            <span class="font-bold text-slate-700">
                                Total Notes: <span class="font-black text-emerald-700" x-text="totalNoteCount()"></span>
                            </span>
                            <span class="text-slate-300">|</span>
                            <span class="font-bold text-slate-700">
                                Counted Total: <span class="font-black text-emerald-700" x-text="'₹ ' + totalCalculatedCash().toLocaleString('en-IN')"></span>
                            </span>
                        </div>
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold"
                                  :class="getVariance() === 0 ? 'bg-emerald-100 text-emerald-800' : (getVariance() > 0 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800')"
                                  x-text="getVarianceText()"></span>
                        </div>
                    </div>
                </div>

                <!-- Cashier Notes -->
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cashier Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Counter closed with zero discrepancy" value="{{ $cashbook ? $cashbook->notes : '' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Save & Reconcile Cashbook</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Cashbooks History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h4 class="text-xs font-bold text-slate-800">Historical Cashbook Closures</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Opening (₹)</th>
                        <th class="py-2.5 px-4">Sales (₹)</th>
                        <th class="py-2.5 px-4">Expenses (₹)</th>
                        <th class="py-2.5 px-4">Closing Expected (₹)</th>
                        <th class="py-2.5 px-4">Actual Counted (₹)</th>
                        <th class="py-2.5 px-4">Variance (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentCashbooks as $cb)
                        @php
                            $cbDenoms = is_array($cb->denominations) ? $cb->denominations : (json_decode($cb->denominations, true) ?: []);
                            $denomParts = [];
                            foreach (['500', '200', '100', '50', '20', '10'] as $k) {
                                if (!empty($cbDenoms[$k])) {
                                    $denomParts[] = "₹{$k}×{$cbDenoms[$k]}";
                                }
                            }
                            if (!empty($cbDenoms['coins'])) {
                                $denomParts[] = "Coins: ₹{$cbDenoms['coins']}";
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $cb->entry_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">₹ {{ number_format($cb->opening_cash, 2) }}</td>
                            <td class="py-3 px-4 text-emerald-700 font-bold">₹ {{ number_format($cb->cash_sales, 2) }}</td>
                            <td class="py-3 px-4 text-rose-600">₹ {{ number_format($cb->cash_expenses, 2) }}</td>
                            <td class="py-3 px-4 font-bold">₹ {{ number_format($cb->closing_cash, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">₹ {{ number_format($cb->actual_counted_cash, 2) }}</span>
                                @if(!empty($denomParts))
                                    <span class="block text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ implode(', ', $denomParts) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold {{ $cb->variance == 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $cb->variance == 0 ? 'Exact (₹ 0)' : '₹ ' . number_format($cb->variance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">No previous cashbooks recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentCashbooks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $recentCashbooks->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    function cashDenominationApp(initialDenoms = {}, initialCounted = null) {
        return {
            openingCash: {{ old('opening_cash', $cashbook ? $cashbook->opening_cash : 2000) }},
            cashSales: {{ old('cash_sales', $defaultCashSales) }},
            cashExpenses: {{ old('cash_expenses', $cashbook ? $cashbook->cash_expenses : 0) }},
            actualCountedCash: initialCounted,

            notes: {
                '500': initialDenoms['500'] || '',
                '200': initialDenoms['200'] || '',
                '100': initialDenoms['100'] || '',
                '50': initialDenoms['50'] || '',
                '20': initialDenoms['20'] || '',
                '10': initialDenoms['10'] || '',
            },
            coinsAmount: initialDenoms['coins'] || '',

            init() {
                if (!this.actualCountedCash && this.totalCalculatedCash() > 0) {
                    this.actualCountedCash = this.totalCalculatedCash();
                }
            },

            expectedCash() {
                const op = parseFloat(this.openingCash) || 0;
                const sal = parseFloat(this.cashSales) || 0;
                const exp = parseFloat(this.cashExpenses) || 0;
                return Math.round((op + sal - exp) * 100) / 100;
            },

            totalNoteCount() {
                let count = 0;
                for (const k of ['500', '200', '100', '50', '20', '10']) {
                    count += parseInt(this.notes[k] || 0) || 0;
                }
                return count;
            },

            totalCalculatedCash() {
                let total = 0;
                for (const k of ['500', '200', '100', '50', '20', '10']) {
                    total += (parseInt(this.notes[k] || 0) || 0) * parseInt(k);
                }
                total += parseFloat(this.coinsAmount || 0) || 0;
                return Math.round(total * 100) / 100;
            },

            onDenomChange() {
                const total = this.totalCalculatedCash();
                this.actualCountedCash = total;
            },

            resetDenominations() {
                for (const k of ['500', '200', '100', '50', '20', '10']) {
                    this.notes[k] = '';
                }
                this.coinsAmount = '';
                this.actualCountedCash = 0;
            },

            getVariance() {
                const counted = parseFloat(this.actualCountedCash) || 0;
                const expected = this.expectedCash();
                return Math.round((counted - expected) * 100) / 100;
            },

            getVarianceText() {
                const v = this.getVariance();
                if (v === 0) return 'Exact Match (₹ 0)';
                if (v > 0) return 'Surplus / अतिरिक्त: +₹ ' + v.toLocaleString('en-IN');
                return 'Shortage / कमी: -₹ ' + Math.abs(v).toLocaleString('en-IN');
            }
        }
    }
</script>
@endpush
@endsection
