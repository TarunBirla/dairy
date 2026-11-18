@extends('layouts.app')

@section('title', 'Invoice Details - ' . $invoice->invoice_number)
@section('breadcrumb', 'Invoice Details')
@section('header_title', 'Invoice Details #' . $invoice->invoice_number)

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('farmer-invoices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Back to Invoices</span>
        </a>
        <a href="{{ route('farmer-invoices.print', $invoice->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Bill</span>
        </a>
        <form method="POST" action="{{ route('farmer-invoices.destroy', $invoice->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl shadow-xs transition">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Delete</span>
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="space-y-6">

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

    <!-- 1. Farmer Deduction Wallet Summary Banner (Requirement 1 & 2) -->
    @php
        $openingWallet = (float) ($invoice->opening_wallet_balance ?? ($walletSummary['total_balance'] ?? 0));
        $walletDeducted = (float) ($invoice->wallet_deducted_amount ?? $invoice->total_deduction ?? 0);
        $closingWallet = (float) ($invoice->closing_wallet_balance ?? max(0, $openingWallet - $walletDeducted));
        $walletCategories = $walletSummary['category_balances'] ?? [];
    @endphp
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-5 text-white shadow-md border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400/20 text-amber-300 border border-amber-400/30 flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-white">Farmer Deduction Wallet</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">Live Ledger</span>
                    </div>
                    <p class="text-xs text-slate-300 mt-0.5">Category-wise outstanding balance, deductions applied to this invoice, and carried-forward balance.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('wallets.show', $farmer->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold rounded-xl transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Open Full Wallet</span>
                </a>
            </div>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4">
            <div class="bg-white/5 border border-white/10 rounded-xl p-3.5">
                <span class="text-[11px] font-medium text-slate-300 uppercase tracking-wider block">Opening Wallet Balance</span>
                <span class="text-xl font-black font-mono text-amber-300 mt-1 block">₹{{ number_format($openingWallet, 2) }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Total dues before this invoice</span>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-xl p-3.5">
                <span class="text-[11px] font-medium text-slate-300 uppercase tracking-wider block">Deducted In This Invoice</span>
                <span class="text-xl font-black font-mono text-rose-300 mt-1 block">- ₹{{ number_format($walletDeducted, 2) }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Applied across categories</span>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-xl p-3.5">
                <span class="text-[11px] font-medium text-slate-300 uppercase tracking-wider block">Closing Wallet Balance</span>
                <span class="text-xl font-black font-mono text-emerald-300 mt-1 block">₹{{ number_format($closingWallet, 2) }}</span>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Carried forward to next bill</span>
            </div>
        </div>

        @if(count($walletCategories) > 0)
            <div class="pt-4 border-t border-white/10 mt-4">
                <span class="text-[11px] font-bold text-slate-300 uppercase tracking-wider block mb-2">Category-Wise Outstanding Balances:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($walletCategories as $wCat => $wBal)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-white/10 border border-white/15 text-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full {{ $wBal > 0 ? 'bg-amber-400' : 'bg-slate-400' }}"></span>
                            <span>{{ $wCat }}:</span>
                            <span class="font-mono font-bold {{ $wBal > 0 ? 'text-amber-300' : 'text-slate-400' }}">₹{{ number_format($wBal, 2) }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- 2-Column Layout matching Reference media_1791388765764.png -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: Collections & Deductions Breakdown -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Milk Collections with Shift Tabs & Add Missing Milk Entry -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden"
                 x-data="{ 
                     shiftFilter: 'all',
                     showAddModal: false,
                     entryDate: '{{ $invoice->period_start->format('Y-m-d') }}',
                     entryShift: 'morning',
                     entryType: 'cow',
                     entryQty: '',
                     entryFat: '4.0',
                     entryClr: '28',
                     entrySnf: '8.20',
                     recalcSnf() {
                         let clrVal = parseFloat(this.entryClr) || 0;
                         let fatVal = parseFloat(this.entryFat) || 0;
                         if (clrVal > 0 && fatVal > 0) {
                             this.entrySnf = ((clrVal / 4) + (0.2 * fatVal) + 0.36).toFixed(2);
                         }
                     }
                 }">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                            <i data-lucide="milk" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Milk Collections</h3>
                            <p class="text-[11px] text-slate-500">{{ count($collections) }} Collection Entries</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <!-- Shift Filter Tabs -->
                        <div class="flex items-center bg-slate-200/70 p-0.5 rounded-xl text-xs font-medium">
                            <button type="button" @click="shiftFilter = 'all'" :class="shiftFilter === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                                All
                            </button>
                            <button type="button" @click="shiftFilter = 'morning'" :class="shiftFilter === 'morning' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                                Morning
                            </button>
                            <button type="button" @click="shiftFilter = 'evening'" :class="shiftFilter === 'evening' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                                Evening
                            </button>
                        </div>

                        <!-- Add Missing Milk Entry Button (Requirement 4) -->
                        <button type="button" @click="showAddModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            <span>Add Missing Entry</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Shift</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3 text-right">Liter</th>
                                <th class="py-2.5 px-3 text-right">FAT %</th>
                                <th class="py-2.5 px-3 text-right">CLR / SNF</th>
                                <th class="py-2.5 px-3 text-right">Rate</th>
                                <th class="py-2.5 px-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($collections as $c)
                                <tr x-show="shiftFilter === 'all' || shiftFilter === '{{ strtolower($c->shift) }}'" class="hover:bg-slate-50/70">
                                    <td class="py-2 px-3 font-medium">{{ \Carbon\Carbon::parse($c->collection_date)->format('d M Y') }}</td>
                                    <td class="py-2 px-3 capitalize">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ strtolower($c->shift) === 'morning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                            {{ $c->shift }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 capitalize font-medium">{{ $c->milk_type }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-sky-900">{{ number_format($c->quantity_liters, 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono">{{ number_format($c->fat ?? $c->fat_percentage ?? 0, 1) }}%</td>
                                    <td class="py-2 px-3 text-right font-mono text-slate-500">{{ ($c->clr ?? $c->clr_reading) ? number_format($c->clr ?? $c->clr_reading, 1) : '-' }} / {{ ($c->snf ?? $c->snf_percentage) ? number_format($c->snf ?? $c->snf_percentage, 1) : '-' }}</td>
                                    <td class="py-2 px-3 text-right font-mono">₹{{ number_format($c->applied_rate ?? $c->calculated_rate ?? $c->rate_per_liter ?? 0, 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">₹{{ number_format($c->net_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-slate-400">No milk collection entries found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200 text-xs">
                            <tr>
                                <td colspan="3" class="py-2.5 px-3 uppercase tracking-wider text-slate-700">Total Milk Collections</td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-sky-900">{{ number_format($invoice->total_quantity, 2) }} L</td>
                                <td colspan="3"></td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Modal: Add Missing Milk Entry (Requirement 4) -->
                <div x-show="showAddModal" 
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                     @keydown.escape.window="showAddModal = false">
                    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative"
                         @click.away="showAddModal = false">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Add Missing Milk Entry</h4>
                                    <p class="text-[11px] text-slate-500">Add entry directly to Invoice #{{ $invoice->invoice_number }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showAddModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('farmer-invoices.add-milk-entry', $invoice->id) }}" class="mt-4 space-y-4">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Collection Date <span class="text-rose-500">*</span></label>
                                    <input type="date" name="collection_date" x-model="entryDate" required
                                           min="{{ $invoice->period_start->format('Y-m-d') }}"
                                           max="{{ $invoice->period_end->format('Y-m-d') }}"
                                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-mono">
                                    <span class="text-[10px] text-slate-400">Within billing period</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Shift <span class="text-rose-500">*</span></label>
                                    <select name="shift" x-model="entryShift" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500">
                                        <option value="morning">Morning</option>
                                        <option value="evening">Evening</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Milk Type <span class="text-rose-500">*</span></label>
                                    <select name="milk_type" x-model="entryType" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-semibold">
                                        <option value="cow">Cow</option>
                                        <option value="buffalo">Buffalo</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Quantity (Liters) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="quantity_liters" x-model="entryQty" placeholder="e.g. 15.50" required
                                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-mono font-bold">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-1">FAT (%) <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.1" min="1.0" max="15.0" name="fat" x-model="entryFat" @input="recalcSnf()" required
                                           class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-mono font-bold">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-1">CLR</label>
                                    <input type="number" step="0.1" min="10" max="50" name="clr" x-model="entryClr" @input="recalcSnf()"
                                           class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-mono">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 mb-1">SNF (%)</label>
                                    <input type="number" step="0.01" min="4.0" max="15.0" name="snf" x-model="entrySnf"
                                           class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-mono font-bold bg-white">
                                </div>
                            </div>

                            <div class="p-3 bg-sky-50 border border-sky-200 rounded-xl text-sky-800 text-xs">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="font-bold">Automatic Rate Chart Calculation</p>
                                        <p class="text-[11px] text-sky-700 mt-0.5">Rate & amount are calculated automatically from the historical Rate Chart for this date. The entry will be linked to this invoice and marked invoiced.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Save & Add to Invoice</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card 2: Deductions Breakdown matching Screenshot 4 & Category-Wise Requirements -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                            <i data-lucide="minus-circle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Deductions Breakdown (Category-Wise)</h3>
                            <p class="text-[11px] text-slate-500">Original Balance, Deducted Amount in this Invoice, and Remaining Balance</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                        Total Deducted: -₹{{ number_format($invoice->total_deduction, 2) }}
                    </span>
                </div>

                @php
                    $breakdown = $invoice->getCategoryDeductionsBreakdown();
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                <th class="py-2.5 px-3">Deduction Category</th>
                                <th class="py-2.5 px-3 text-right">Original Balance</th>
                                <th class="py-2.5 px-3 text-right">Deducted In Bill</th>
                                <th class="py-2.5 px-3 text-right">Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($breakdown as $b)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-2 px-3 font-semibold text-slate-800">{{ $b['name'] }}</td>
                                    <td class="py-2 px-3 text-right font-mono text-slate-600">₹{{ number_format($b['original_balance'], 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-rose-700">- ₹{{ number_format($b['deducted_amount'], 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-amber-700">₹{{ number_format($b['remaining_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">No deductions recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/80 font-bold border-t border-slate-200 text-xs">
                            <tr>
                                <td class="py-2.5 px-3">Total</td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-700">₹{{ number_format(collect($breakdown)->sum('original_balance'), 2) }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-rose-700">- ₹{{ number_format($invoice->total_deduction, 2) }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-amber-700">₹{{ number_format(collect($breakdown)->sum('remaining_balance'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Card 3: Cattle Feeds / Products Provided (if any) -->
            @if(count($cattleFeeds) > 0)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Cattle Feeds Issued</h3>
                                <p class="text-[11px] text-slate-500">Products supplied to farmer</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Item Details</th>
                                    <th class="py-2.5 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($cattleFeeds as $cf)
                                    <tr>
                                        <td class="py-2 px-3 font-medium">{{ \Carbon\Carbon::parse($cf->entry_date)->format('d M Y') }}</td>
                                        <td class="py-2 px-3 text-slate-600">{{ $cf->comments ?? $cf->remarks ?? 'Cattle Feed' }}</td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-rose-700">-₹{{ number_format($cf->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Farmer Profile & Invoice Financial Summary -->
        <div class="space-y-6">

            <!-- Farmer Profile Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">
                            {{ substr($farmer->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 leading-tight">{{ $farmer->name }}</h4>
                            <p class="text-[11px] text-slate-500">Farmer Code: <span class="font-mono font-bold text-slate-800">{{ $farmer->farmer_code }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('farmers.show', $farmer->id) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold">
                        View
                    </a>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Phone</span>
                        <span class="font-medium text-slate-800">{{ $farmer->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Bank Name</span>
                        <span class="font-medium text-slate-800">{{ $farmer->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Account Number</span>
                        <span class="font-mono font-bold text-slate-800">{{ $farmer->account_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>IFSC Code</span>
                        <span class="font-mono font-medium text-slate-800">{{ $farmer->ifsc_code ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Invoice Summary Card matching media_1791388765764.png -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Invoice Summary</h4>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-mono text-[10px] font-bold rounded-md">
                        {{ $invoice->invoice_number }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Period Start</span>
                        <span class="font-medium text-slate-800">{{ $invoice->period_start->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Period End</span>
                        <span class="font-medium text-slate-800">{{ $invoice->period_end->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Total Quantity</span>
                        <span class="font-mono font-bold text-sky-800">{{ number_format($invoice->total_quantity, 2) }} L</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-between text-slate-700">
                        <span>Milk Amount (C)</span>
                        <span class="font-mono font-bold text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-700">
                        <span>Total Credit (B)</span>
                        <span class="font-mono font-bold text-amber-700">+ ₹{{ number_format($invoice->total_credit, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-700">
                        <span>Stationary Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->stationary_deduction, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Feed Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->feed_deduction, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Advance Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->advance_deduction, 2) }}</span>
                    </div>
                    @if($invoice->other_deduction > 0)
                    <div class="flex justify-between text-slate-700">
                        <span>Other Charges / Deductions (Per Invoice)</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->other_deduction, 2) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between text-rose-700 font-semibold">
                        <span>Total Deductions (A)</span>
                        <span class="font-mono font-bold">- ₹{{ number_format($invoice->total_deduction, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Previous Balance</span>
                        <span class="font-mono">₹{{ number_format($invoice->previous_balance, 2) }}</span>
                    </div>

                    <div class="pt-3 border-t-2 border-slate-200 flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-900">Net Payment</span>
                        <span class="text-base font-black font-mono text-emerald-700">₹{{ number_format($invoice->net_payment, 2) }}</span>
                    </div>
                </div>

                <div class="pt-3">
                    <a href="{{ route('farmer-invoices.print', $invoice->id) }}" target="_blank" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Print Bill Receipt</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
