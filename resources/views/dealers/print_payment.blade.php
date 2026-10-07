<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $dealerPayment->dealer_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-8">
    <div class="max-w-xl mx-auto border border-slate-200 rounded-2xl p-6 space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-900">DEALER PAYMENT VOUCHER</h2>
                <p class="text-xs text-slate-500">Voucher #: <span class="font-mono font-bold">PAY-{{ str_pad($dealerPayment->id, 5, '0', STR_PAD_LEFT) }}</span></p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="px-3 py-1 bg-emerald-600 text-white rounded text-xs font-semibold">Print Voucher</button>
            </div>
        </div>

        <!-- Voucher Details -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Paid To</span>
                <span class="font-bold text-slate-900 text-sm">{{ $dealerPayment->dealer_name }}</span>
                <span class="block text-slate-500 font-mono text-[11px]">Code: {{ $dealerPayment->dealer_code ?: '—' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Payment Date</span>
                <span class="font-semibold text-slate-900">{{ $dealerPayment->payment_date->format('d M Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Payment Mode</span>
                <span class="font-semibold text-slate-800">{{ $dealerPayment->payment_mode }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Recorded By</span>
                <span class="text-slate-700">{{ $dealerPayment->recorder ? $dealerPayment->recorder->name : 'System' }}</span>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl space-y-2 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Dues Before Payment:</span>
                <span class="font-mono font-semibold">₹ {{ number_format($dealerPayment->dues_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-emerald-800 text-sm font-bold border-y border-emerald-200 py-1.5">
                <span>Amount Paid:</span>
                <span class="font-mono">₹ {{ number_format($dealerPayment->pay_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-700">
                <span>Remaining Balance / Dues:</span>
                <span class="font-mono font-bold text-indigo-700">₹ {{ number_format($dealerPayment->remaining_dues, 2) }}</span>
            </div>
        </div>

        @if($dealerPayment->comment)
            <div class="text-xs text-slate-600">
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Notes / Comment</span>
                <p class="mt-0.5 bg-slate-50 p-2.5 rounded-lg border border-slate-100">{{ $dealerPayment->comment }}</p>
            </div>
        @endif

        <!-- Signature lines -->
        <div class="pt-8 flex justify-between text-xs text-slate-400 border-t border-slate-100">
            <div>
                <div class="w-32 border-b border-slate-300 mb-1"></div>
                <span>Receiver's Signature</span>
            </div>
            <div class="text-right">
                <div class="w-32 border-b border-slate-300 mb-1 ml-auto"></div>
                <span>Authorized Signatory</span>
            </div>
        </div>
    </div>
</body>
</html>
