@extends('layouts.app')

@section('title', 'Generate Settlement')
@section('breadcrumb', 'New Settlement')
@section('header_title', 'Farmer Settlement & Payout Generator')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" x-data="settlementForm()">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Calculate Period Milk Settlement</h3>
            <p class="text-xs text-slate-500">Prorate milk deliveries, adjust advances/deductions, and record payment.</p>
        </div>
        <a href="{{ route('settlements.index') }}" class="text-xs text-slate-500">Back</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('settlements.create') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-xl mb-6">
        <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Farmer *</label>
            <select name="farmer_id" onchange="this.form.submit()" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                <option value="">-- Choose Farmer --</option>
                @foreach($farmers as $f)
                    <option value="{{ $f->id }}" {{ $selectedFarmer && $selectedFarmer->id == $f->id ? 'selected' : '' }}>
                        {{ $f->farmer_code }} - {{ $f->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">From Date</label>
            <input type="date" name="start_date" value="{{ $periodStart }}" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-1">To Date</label>
            <div class="flex gap-2">
                <input type="date" name="end_date" value="{{ $periodEnd }}" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg">Load</button>
            </div>
        </div>
    </form>

    @if($selectedFarmer)
        <form action="{{ route('settlements.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="farmer_id" value="{{ $selectedFarmer->id }}">
            <input type="hidden" name="period_start" value="{{ $periodStart }}">
            <input type="hidden" name="period_end" value="{{ $periodEnd }}">

            <!-- Summary Box -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-emerald-50/50 rounded-xl border border-emerald-100">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Total Unsettled Liters</span>
                    <p class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalLiters, 1) }} Liters</p>
                    <span class="text-[11px] text-slate-500">{{ $collections->count() }} Collection Shifts</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Gross Milk Value</span>
                    <p class="text-xl font-black text-emerald-700 mt-1">₹ {{ number_format($grossAmount, 2) }}</p>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Pending Advances</span>
                    <p class="text-xl font-black text-amber-700 mt-1">₹ {{ number_format($pendingAdvance, 2) }}</p>
                </div>
            </div>

            <!-- Deductions & Net Payout -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deduct Advance (₹)</label>
                    <input type="number" step="10" name="advance_recovered" x-model.number="adv" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Add Incentive / Bonus (₹)</label>
                    <input type="number" step="10" name="bonus_amount" x-model.number="bonus" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Other Deductions (Feed/etc)</label>
                    <input type="number" step="10" name="deduction_amount" x-model.number="ded" @input="calc()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
            </div>

            <div class="p-4 bg-slate-900 text-white rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-slate-400">Calculated Net Settlement Payout</span>
                    <p class="text-2xl font-black text-emerald-400 mt-0.5">₹ <span x-text="net.toFixed(2)"></span></p>
                </div>
                <div class="text-right text-xs text-slate-300">
                    <p>Bank: {{ $selectedFarmer->bank_name ?? 'Cash Account' }}</p>
                    <p class="font-mono text-[11px]">{{ $selectedFarmer->upi_id ?? '' }}</p>
                </div>
            </div>

            <!-- Payment Mode -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Mode *</label>
                    <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI / GooglePay / PhonePe</option>
                        <option value="Bank Transfer">Bank Transfer (NEFT/IMPS)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No.</label>
                    <input type="text" name="payment_reference" placeholder="e.g. UTR / UPI Ref ID" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                <a href="{{ route('settlements.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">
                    Confirm & Disburse Settlement
                </button>
            </div>
        </form>
    @else
        <div class="py-12 text-center text-slate-400">
            <i data-lucide="user" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
            <p class="text-sm font-semibold text-slate-600">Please choose a farmer above to calculate settlement.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function settlementForm() {
        return {
            gross: {{ (float)$grossAmount }},
            adv: {{ (float)min($pendingAdvance, $grossAmount) }},
            bonus: 0,
            ded: 0,
            net: {{ (float)max(0, $grossAmount - min($pendingAdvance, $grossAmount)) }},

            calc() {
                this.net = Math.max(0, (this.gross || 0) + (this.bonus || 0) - (this.ded || 0) - (this.adv || 0));
            }
        }
    }
</script>
@endpush
@endsection
