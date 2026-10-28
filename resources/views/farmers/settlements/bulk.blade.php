@extends('layouts.app')

@section('title', 'Bulk Settlement Payout')
@section('breadcrumb', 'Bulk Settlements')
@section('header_title', 'Bulk Farmer Settlement & Payout Generator')

@section('content')
<div class="max-w-4xl mx-auto space-y-4" x-data="bulkSettlementManager()">

    <!-- Top Period Filter & Navigation -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('settlements.bulk') }}" class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-700">Billing Period:</span>
            <input type="date" name="start_date" value="{{ $periodStart }}" class="px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 font-medium">
            <span class="text-xs text-slate-400">to</span>
            <input type="date" name="end_date" value="{{ $periodEnd }}" class="px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 font-medium">
            <input type="hidden" name="index" value="0">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition">
                Load Period
            </button>
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('settlements.create') }}" class="text-xs font-semibold text-emerald-700 hover:underline">
                Single Farmer Payout
            </a>
            <span class="text-slate-300">•</span>
            <a href="{{ route('settlements.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-700">
                Settlements List
            </a>
        </div>
    </div>

    <!-- Stepper Navigation Bar: ‹ settlements ( 1 / 8 ) › -->
    <div class="bg-slate-900 text-white px-5 py-4 rounded-2xl shadow-sm flex items-center justify-between">
        <!-- Prev Button ‹ -->
        @if($currentIndex > 0)
            <a href="{{ route('settlements.bulk', ['start_date' => $periodStart, 'end_date' => $periodEnd, 'index' => $currentIndex - 1]) }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-emerald-600 text-white font-bold text-2xl transition shadow-sm"
               title="Previous Farmer">
                ‹
            </a>
        @else
            <button disabled class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800/40 text-slate-600 font-bold text-2xl cursor-not-allowed">
                ‹
            </button>
        @endif

        <!-- Center Stepper Info -->
        <div class="text-center">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Bulk Payout Queue</span>
            <h2 class="text-xl font-black tracking-wide text-white flex items-center justify-center gap-2">
                <span>‹</span>
                <span>settlements ( {{ $totalCount > 0 ? $currentIndex + 1 : 0 }} / {{ $totalCount }} )</span>
                <span>›</span>
            </h2>
            <p class="text-[11px] text-slate-400 mt-0.5">
                @if($totalCount > 0)
                    Showing {{ $currentIndex + 1 }} of {{ $totalCount }} farmers with non-zero unsettled collections
                @else
                    No pending farmers in this date range
                @endif
            </p>
        </div>

        <!-- Next Button › -->
        @if($currentIndex < $totalCount - 1)
            <a href="{{ route('settlements.bulk', ['start_date' => $periodStart, 'end_date' => $periodEnd, 'index' => $currentIndex + 1]) }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-emerald-600 text-white font-bold text-2xl transition shadow-sm"
               title="Next Farmer">
                ›
            </a>
        @else
            <button disabled class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800/40 text-slate-600 font-bold text-2xl cursor-not-allowed">
                ›
            </button>
        @endif
    </div>

    @if($selectedFarmer)
        <!-- Current Farmer Settlement Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-5">

            <!-- Farmer Header Info & Jump Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center font-bold text-base">
                        {{ substr($selectedFarmer->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900">{{ $selectedFarmer->name }}</h3>
                            <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $selectedFarmer->farmer_code }}
                            </span>
                            @if($selectedFarmer->name_hi)
                                <span class="text-xs text-slate-400">({{ $selectedFarmer->name_hi }})</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Phone: {{ $selectedFarmer->phone ?: 'N/A' }} • Route: {{ $selectedFarmer->route ? $selectedFarmer->route->name : 'Center Direct' }}
                        </p>
                    </div>
                </div>

                <!-- Jump Dropdown -->
                <div class="flex items-center gap-2">
                    <label class="text-[11px] font-bold text-slate-500">Jump to:</label>
                    <select onchange="window.location.href = this.value" class="px-3 py-1.5 text-xs font-semibold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        @foreach($farmers as $idx => $f)
                            <option value="{{ route('settlements.bulk', ['start_date' => $periodStart, 'end_date' => $periodEnd, 'index' => $idx]) }}" {{ $idx == $currentIndex ? 'selected' : '' }}>
                                #{{ $idx + 1 }}: {{ $f->farmer_code }} - {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Settlement Form -->
            <form action="{{ route('settlements.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="farmer_id" value="{{ $selectedFarmer->id }}">
                <input type="hidden" name="period_start" value="{{ $periodStart }}">
                <input type="hidden" name="period_end" value="{{ $periodEnd }}">
                <input type="hidden" name="bulk_mode" value="1">
                <input type="hidden" name="bulk_index" value="{{ $currentIndex }}">

                <!-- Summary Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Total Unsettled Liters</span>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalLiters, 2) }} <span class="text-sm font-semibold text-slate-500">Liters</span></p>
                        <span class="text-[11px] text-slate-500">{{ $collections->count() }} Collection Shifts</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Gross Milk Value</span>
                        <p class="text-2xl font-black text-emerald-700 mt-1">₹ {{ number_format($grossAmount, 2) }}</p>
                        <span class="text-[11px] text-slate-500">{{ $totalLiters > 0 ? '₹' . number_format($grossAmount / $totalLiters, 2) . '/Ltr avg' : '—' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Pending Advances</span>
                        <p class="text-2xl font-black text-amber-700 mt-1">₹ {{ number_format($pendingAdvance, 2) }}</p>
                        <span class="text-[11px] text-slate-500">Installment: ₹ {{ number_format((float)($selectedFarmer->installment ?? 0), 2) }}</span>
                    </div>
                </div>

                <!-- Deductions & Bonus Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Deduct Advance (₹)
                            @if($defaultAdvanceRecovered > 0)
                                <span class="text-[10px] text-emerald-600 font-semibold">(Auto configured)</span>
                            @endif
                        </label>
                        <input type="number" step="0.01" min="0" name="advance_recovered" x-model.number="adv" @input="calc()" placeholder="0.00"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Add Incentive / Bonus (₹)</label>
                        <input type="number" step="0.01" min="0" name="bonus_amount" x-model.number="bonus" @input="calc()" placeholder="0.00"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Other Deductions (Feed/etc)</label>
                        <input type="number" step="0.01" min="0" name="deduction_amount" x-model.number="ded" @input="calc()" placeholder="0.00"
                               class="w-full px-3.5 py-2.5 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <!-- Net Payable Highlight Banner -->
                <div class="p-5 bg-slate-900 text-white rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-md">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Calculated Net Settlement Payout</span>
                        <p class="text-3xl font-black text-emerald-400 mt-1">
                            ₹ <span x-text="net.toFixed(2)"></span>
                        </p>
                    </div>
                    <div class="text-left sm:text-right text-xs text-slate-300 space-y-0.5">
                        <p><span class="text-slate-400">Bank:</span> {{ $selectedFarmer->bank_name ?: 'Cash Account' }}</p>
                        @if($selectedFarmer->account_number)
                            <p class="font-mono text-[11px]"><span class="text-slate-400">A/C:</span> {{ $selectedFarmer->account_number }}</p>
                        @endif
                        @if($selectedFarmer->upi_id)
                            <p class="font-mono text-[11px] text-emerald-300"><span class="text-slate-400">UPI:</span> {{ $selectedFarmer->upi_id }}</p>
                        @endif
                    </div>
                </div>

                <!-- Payment Mode & Reference -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Payment Mode <span class="text-rose-500">*</span></label>
                        <select name="payment_mode" required class="w-full px-3.5 py-2.5 text-xs font-semibold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI / GooglePay / PhonePe</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Transaction Ref / Cheque No.</label>
                        <input type="text" name="payment_reference" placeholder="e.g. UTR / UPI Ref ID / Cheque #"
                               class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                    <div>
                        @if($currentIndex < $totalCount - 1)
                            <a href="{{ route('settlements.bulk', ['start_date' => $periodStart, 'end_date' => $periodEnd, 'index' => $currentIndex + 1]) }}"
                               class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition inline-flex items-center gap-1.5">
                                <span>Skip / Next Farmer</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('settlements.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-700">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition inline-flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Confirm & Disburse Settlement</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-xs text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto">
                <i data-lucide="check-check" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">All Settlements Disbursed!</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    There are no pending milk collections with non-zero liters between <strong>{{ date('d M Y', strtotime($periodStart)) }}</strong> and <strong>{{ date('d M Y', strtotime($periodEnd)) }}</strong>.
                </p>
            </div>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('settlements.index') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    View Settlements History
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function bulkSettlementManager() {
        return {
            gross: {{ (float)$grossAmount }},
            adv: {{ (float)$defaultAdvanceRecovered }},
            bonus: 0,
            ded: 0,
            net: {{ (float)max(0, $grossAmount - $defaultAdvanceRecovered) }},

            calc() {
                const g = parseFloat(this.gross) || 0;
                const b = parseFloat(this.bonus) || 0;
                const d = parseFloat(this.ded) || 0;
                const a = parseFloat(this.adv) || 0;
                this.net = Math.max(0, g + b - d - a);
            }
        }
    }
</script>
@endpush
@endsection
