<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Statement - {{ $dealer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-8">
    <div class="max-w-2xl mx-auto border border-slate-200 rounded-2xl p-6 space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-900">DEALER PROFILE & STATEMENT</h2>
                <p class="text-xs text-slate-500">Code: <span class="font-mono font-bold">{{ $dealer->code }}</span></p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="px-3 py-1 bg-emerald-600 text-white rounded text-xs font-semibold">Print</button>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Dealer Name</span>
                <span class="font-bold text-slate-900 text-sm">{{ $dealer->name }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Contact</span>
                <span class="font-mono">{{ $dealer->phone ?: '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Address</span>
                <span>{{ $dealer->address ?: '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Details</span>
                <span>{{ $dealer->details ?: '—' }}</span>
            </div>
        </div>

        <!-- Banking Information -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs">
            <h4 class="font-bold text-slate-800 text-[11px] uppercase">Bank Details</h4>
            <div class="grid grid-cols-2 gap-2">
                <div>Bank: <b>{{ $dealer->bank_name ?: '—' }}</b></div>
                <div>Branch: <b>{{ $dealer->branch ?: '—' }}</b></div>
                <div>A/C: <b class="font-mono">{{ $dealer->account_number ?: '—' }}</b></div>
                <div>IFSC: <b class="font-mono uppercase">{{ $dealer->ifsc_code ?: '—' }}</b></div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-3 gap-3 text-center text-xs">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[10px] font-bold">Total Purchases</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">₹ {{ number_format($dealer->total_purchased, 2) }}</span>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                <span class="text-emerald-600 block text-[10px] font-bold">Total Paid</span>
                <span class="font-bold text-emerald-800 text-sm mt-0.5 block">₹ {{ number_format($dealer->total_paid, 2) }}</span>
            </div>
            <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                <span class="text-rose-600 block text-[10px] font-bold">Current Dues</span>
                <span class="font-bold text-rose-800 text-sm mt-0.5 block">₹ {{ number_format($dealer->dues_amount, 2) }}</span>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 text-right text-[11px] text-slate-400">
            Generated on {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>
</body>
</html>
