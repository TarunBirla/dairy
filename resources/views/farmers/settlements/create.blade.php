@extends('layouts.app')

@section('title', 'Generate Settlement')
@section('breadcrumb', 'New Settlement')
@section('header_title', 'Farmer Settlement & Payout Generator')

@section('header_action')
    <a href="{{ route('settlements.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>All Settlements</span>
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-4" x-data="settlementForm()">

    <!-- Top Navigation Widget requested by user: ‹ settlements ( 1 / 6 ) › -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-3.5 rounded-2xl shadow-sm border border-slate-700/80 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-bold tracking-wide text-slate-200">Pending Settlement Cycle</span>
            <span class="text-[10px] text-slate-400 font-medium hidden sm:inline">(Excludes ₹0.00 balance farmers)</span>
        </div>

        <!-- Center ‹ settlements ( X / Y ) › Controls -->
        <div class="flex items-center gap-2 mx-auto sm:mx-0">
            @if($prevFarmer)
                <a href="{{ route('settlements.create', ['farmer_id' => $prevFarmer->id, 'start_date' => $periodStart, 'end_date' => $periodEnd]) }}" 
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-200 font-black text-lg transition border border-slate-700 shadow-xs"
                   title="Previous Farmer: {{ $prevFarmer->farmer_code }} - {{ $prevFarmer->name }}">
                    ‹
                </a>
            @else
                <button type="button" disabled 
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800/40 text-slate-600 font-black text-lg cursor-not-allowed border border-slate-800">
                    ‹
                </button>
            @endif

            <div class="px-4 py-1.5 bg-slate-800/90 rounded-xl text-xs font-extrabold tracking-wide text-emerald-300 border border-emerald-500/30 flex items-center gap-2 shadow-inner">
                <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>settlements ( {{ $totalFarmers > 0 ? $currentIndex : 0 }} / {{ $totalFarmers }} )</span>
            </div>

            @if($nextFarmer)
                <a href="{{ route('settlements.create', ['farmer_id' => $nextFarmer->id, 'start_date' => $periodStart, 'end_date' => $periodEnd]) }}" 
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-white text-slate-200 font-black text-lg transition border border-slate-700 shadow-xs"
                   title="Next Farmer: {{ $nextFarmer->farmer_code }} - {{ $nextFarmer->name }}">
                    ›
                </a>
            @else
                <button type="button" disabled 
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-800/40 text-slate-600 font-black text-lg cursor-not-allowed border border-slate-800">
                    ›
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($selectedFarmer)
                <span class="text-[11px] font-mono px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold">
                    {{ $selectedFarmer->farmer_code }}
                </span>
            @endif
        </div>
    </div>

    <!-- Main Settlement Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-5 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Calculate Period Milk Settlement</h3>
                <p class="text-xs text-slate-500 mt-0.5">Prorate milk deliveries, deduct advances & cattle feed, and record payment.</p>
            </div>
            @if($totalFarmers > 0)
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                    {{ $totalFarmers }} Farmers with Active Balance (+ / -)
                </span>
            @endif
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('settlements.create') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 bg-slate-50/80 rounded-xl mb-6 border border-slate-200/70">
            <div class="md:col-span-6">
                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                    Select Farmer <span class="text-rose-500">*</span> 
                    <span class="text-[10px] text-slate-400 font-normal">(Showing only non-zero balance)</span>
                </label>
                <select name="farmer_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    @if($farmers->isEmpty())
                        <option value="">-- No farmers with balance pending --</option>
                    @else
                        @foreach($farmers as $index => $f)
                            <option value="{{ $f->id }}" {{ $selectedFarmer && $selectedFarmer->id == $f->id ? 'selected' : '' }}>
                                [{{ $index + 1 }}/{{ $totalFarmers }}] {{ $f->farmer_code }} - {{ $f->name }} (₹ {{ number_format($f->current_balance, 2) }} {{ (float)$f->current_balance > 0 ? 'Payable' : 'Advance' }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-[11px] font-bold text-slate-700 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $periodStart }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[11px] font-bold text-slate-700 mb-1">To Date</label>
                <div class="flex gap-2">
                    <input type="date" name="end_date" value="{{ $periodEnd }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white font-medium text-slate-700 focus:ring-2 focus:ring-emerald-500">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs transition">
                        Load
                    </button>
                </div>
            </div>
        </form>

        @if($selectedFarmer)
            <!-- Farmer Profile & Balance Quick Summary -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 mb-5 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-800 font-black text-base shrink-0">
                        {{ substr($selectedFarmer->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-extrabold text-slate-900">{{ $selectedFarmer->name }}</h4>
                            <span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">{{ $selectedFarmer->farmer_code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Village: <strong class="text-slate-700">{{ $selectedFarmer->village ?: '—' }}</strong> • Phone: <strong class="text-slate-700">{{ $selectedFarmer->phone ?: '—' }}</strong> • Animal: <span class="capitalize font-semibold text-slate-700">{{ $selectedFarmer->animal_type ?: 'All' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Live Ledger Balance Pill -->
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Current Farmer Balance</span>
                    @if((float)$selectedFarmer->current_balance > 0)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-base font-black text-emerald-700">+ ₹ {{ number_format($selectedFarmer->current_balance, 2) }}</span>
                            <span class="text-[10px] font-bold text-emerald-700 uppercase bg-emerald-100 px-1.5 py-0.2 rounded">Payable</span>
                        </div>
                    @elseif((float)$selectedFarmer->current_balance < 0)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-rose-50 border border-rose-200 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span class="text-base font-black text-rose-700">- ₹ {{ number_format(abs($selectedFarmer->current_balance), 2) }}</span>
                            <span class="text-[10px] font-bold text-rose-700 uppercase bg-rose-100 px-1.5 py-0.2 rounded">Advance Due</span>
                        </div>
                    @else
                        <span class="text-sm font-bold text-slate-500">₹ 0.00 (Settled)</span>
                    @endif
                </div>
            </div>

            <form action="{{ route('settlements.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="farmer_id" value="{{ $selectedFarmer->id }}">
                <input type="hidden" name="period_start" value="{{ $periodStart }}">
                <input type="hidden" name="period_end" value="{{ $periodEnd }}">

                <!-- Milk Deliveries Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-emerald-50/60 rounded-xl border border-emerald-200/60">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Total Unsettled Milk</span>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalLiters, 1) }} <span class="text-xs font-semibold text-slate-600">Liters</span></p>
                        <span class="text-xs text-slate-500 mt-0.5 block font-medium">{{ $collections->count() }} Collection Shifts in Period</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Gross Milk Value</span>
                        <p class="text-2xl font-black text-emerald-700 mt-1">₹ {{ number_format($grossAmount, 2) }}</p>
                        <span class="text-xs text-emerald-600 mt-0.5 block font-medium">Calculated per rate chart</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Pending Advances</span>
                        <p class="text-2xl font-black text-amber-700 mt-1">₹ {{ number_format($pendingAdvance, 2) }}</p>
                        <span class="text-xs text-amber-600 mt-0.5 block font-medium">Eligible for recovery</span>
                    </div>
                </div>

                @if($collections->isEmpty())
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>No un-settled milk collections found between <strong>{{ \Carbon\Carbon::parse($periodStart)->format('d M') }}</strong> and <strong>{{ \Carbon\Carbon::parse($periodEnd)->format('d M Y') }}</strong>. You can adjust the date range above, or settle directly using the farmer's balance.</span>
                    </div>
                @endif

                <!-- Deductions & Incentives Adjustment -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Payout Adjustments</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deduct Advance (₹)</label>
                            <input type="number" step="1" name="advance_recovered" x-model.number="adv" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-900">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Max advance: ₹{{ number_format($pendingAdvance, 2) }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Add Incentive / Bonus (₹)</label>
                            <input type="number" step="1" name="bonus_amount" x-model.number="bonus" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-900">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Added to net payout</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Other Deductions (Feed / Fee)</label>
                            <input type="number" step="1" name="deduction_amount" x-model.number="ded" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-900">
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Subtracted from milk payout</span>
                        </div>
                    </div>
                </div>

                <!-- Calculated Net Payout Banner -->
                <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Calculated Net Settlement Payout</span>
                        <p class="text-3xl font-black text-emerald-400 mt-1">₹ <span x-text="net.toFixed(2)"></span></p>
                        <span class="text-[11px] text-slate-300 font-medium">Gross: ₹{{ number_format($grossAmount, 2) }} | Net will be credited to farmer</span>
                    </div>
                    <div class="text-right text-xs text-slate-300 bg-slate-800/80 p-3 rounded-xl border border-slate-700">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Disbursement Target:</span>
                        <p class="font-bold text-white mt-0.5">{{ $selectedFarmer->bank_name ?: 'Cash Account' }}</p>
                        <p class="font-mono text-[11px] text-slate-400 mt-0.5">{{ $selectedFarmer->account_number ? 'A/C: ' . $selectedFarmer->account_number : ($selectedFarmer->upi_id ? 'UPI: ' . $selectedFarmer->upi_id : 'Direct Cash') }}</p>
                    </div>
                </div>

                <!-- Payment Mode & Reference -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                        <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-800">
                            <option value="Cash">Cash Payment</option>
                            <option value="UPI">UPI / GooglePay / PhonePe</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT / IMPS / RTGS)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No.</label>
                        <input type="text" name="payment_reference" placeholder="e.g. UTR / UPI Ref ID / Cheque #" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Action Footer with Next / Disburse -->
                <div class="pt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('settlements.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Cancel
                        </a>
                        @if($nextFarmer)
                            <a href="{{ route('settlements.create', ['farmer_id' => $nextFarmer->id, 'start_date' => $periodStart, 'end_date' => $periodEnd]) }}" class="px-3 py-2 text-xs font-semibold text-slate-700 hover:text-emerald-700 flex items-center gap-1 transition">
                                <span>Skip to Next ({{ $currentIndex + 1 }}/{{ $totalFarmers }})</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Confirm & Disburse Settlement</span>
                    </button>
                </div>
            </form>
        @else
            <div class="py-16 text-center text-slate-400">
                <i data-lucide="check-check" class="w-12 h-12 mx-auto mb-3 text-emerald-400"></i>
                <h4 class="text-base font-bold text-slate-700">All Farmer Settlements Up to Date!</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">There are currently no active farmers with positive or negative pending balances. All 0.00 balances are settled.</p>
                <div class="mt-4">
                    <a href="{{ route('settlements.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition inline-flex items-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>View Settlement History</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    function settlementForm() {
        const grossAmount = {{ (float)$grossAmount }};
        const pendingAdv = {{ (float)$pendingAdvance }};
        const defaultAdv = Math.min(pendingAdv, grossAmount);

        return {
            gross: grossAmount,
            adv: defaultAdv > 0 ? defaultAdv : 0,
            bonus: 0,
            ded: 0,
            net: Math.max(0, grossAmount - (defaultAdv > 0 ? defaultAdv : 0)),

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
