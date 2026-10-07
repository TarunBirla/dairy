<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Voucher {{ $settlement->settlement_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print { .no-print { display: none !important; } body { background: white !important; padding: 0 !important; } }
    </style>
</head>
<body class="bg-slate-100 p-6 flex flex-col items-center min-h-screen">

    <div class="no-print max-w-xl w-full mb-4 flex items-center justify-between">
        <a href="{{ route('settlements.index') }}" class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back</span>
        </a>
        <button onclick="window.print()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm flex items-center gap-1.5">
            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
            <span>Print Voucher</span>
        </button>
    </div>

    <div class="bg-white max-w-xl w-full p-8 rounded-2xl shadow-md border border-slate-200 text-xs text-slate-700">
        <!-- Header -->
        <div class="border-b border-slate-200 pb-4 mb-4 flex justify-between items-start">
            <div>
                <h1 class="text-lg font-black text-slate-900 uppercase">{{ \App\Models\SystemSetting::get('dairy_name', 'SIMPLE DAIRY') }}</h1>
                <p class="text-[11px] text-slate-400">Farmer Milk Procurement Payout Voucher</p>
                <p class="text-[11px] text-slate-500 mt-1">{{ \App\Models\SystemSetting::get('address') }}</p>
            </div>
            <div class="text-right">
                <span class="font-mono font-bold text-sm text-emerald-700">{{ $settlement->settlement_number }}</span>
                <p class="text-[11px] text-slate-400 mt-0.5">Date: {{ $settlement->settled_at->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Farmer & Period -->
        <div class="grid grid-cols-2 gap-4 py-3 bg-slate-50 p-4 rounded-xl mb-4">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Farmer Details:</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $settlement->farmer->name }}</p>
                <p class="text-[11px] text-slate-500 font-mono">{{ $settlement->farmer->farmer_code }} • Village {{ $settlement->farmer->village }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Settlement Period:</span>
                <p class="font-bold text-slate-800 mt-0.5">{{ $settlement->period_start->format('d M Y') }} to {{ $settlement->period_end->format('d M Y') }}</p>
                <p class="text-[11px] text-emerald-600 font-semibold">Status: PAID ({{ $settlement->payment_mode }})</p>
            </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="space-y-2 py-3 border-y border-slate-200">
            <div class="flex justify-between">
                <span>Total Milk Supplied:</span>
                <span class="font-bold text-slate-900">{{ $settlement->total_liters }} Liters</span>
            </div>
            <div class="flex justify-between">
                <span>Gross Milk Procurement Value:</span>
                <span class="font-bold">₹ {{ number_format($settlement->gross_amount, 2) }}</span>
            </div>
            @if($settlement->bonus_amount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Quality Incentive / Bonus:</span>
                    <span class="font-bold">+ ₹ {{ number_format($settlement->bonus_amount, 2) }}</span>
                </div>
            @endif
            @if($settlement->advance_recovered > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Less: Advance Recovered:</span>
                    <span class="font-bold">- ₹ {{ number_format($settlement->advance_recovered, 2) }}</span>
                </div>
            @endif
            @if($settlement->deduction_amount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Less: Other Deductions:</span>
                    <span class="font-bold">- ₹ {{ number_format($settlement->deduction_amount, 2) }}</span>
                </div>
            @endif
        </div>

        <!-- Net Paid -->
        <div class="py-4 flex justify-between items-baseline border-b border-slate-200">
            <span class="font-bold text-sm text-slate-900">NET SETTLEMENT PAID:</span>
            <span class="text-2xl font-black text-emerald-700">₹ {{ number_format($settlement->paid_amount, 2) }}</span>
        </div>

        <!-- Signatures -->
        <div class="pt-8 grid grid-cols-2 gap-8 text-center text-[11px] text-slate-500">
            <div class="border-t border-slate-300 pt-2">
                Authorized Signatory (Dairy)
            </div>
            <div class="border-t border-slate-300 pt-2">
                Farmer Receiver Signature
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
