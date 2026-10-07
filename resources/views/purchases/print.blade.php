<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Voucher #{{ $purchase->purchase_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 flex flex-col items-center justify-start text-slate-800 font-sans">

    <!-- Action Toolbar (Hidden in print) -->
    <div class="no-print w-full max-w-xl mb-4 flex items-center justify-between">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            ← Back to List
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
            🖨️ Print Slip
        </button>
    </div>

    <!-- Printable Voucher Card -->
    <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg border border-slate-200 p-6 sm:p-8">
        
        <!-- Header -->
        <div class="text-center border-b border-slate-200 pb-4">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">GOPAL DUDH DAIRY</h1>
            <p class="text-xs text-slate-500 mt-0.5">Fresh Milk Procurements & Dairy Products Supplies</p>
            <div class="mt-2 inline-block px-3 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold rounded-full uppercase tracking-wider">
                BUY PRODUCT / PURCHASE VOUCHER
            </div>
        </div>

        <!-- Meta Details -->
        <div class="grid grid-cols-2 gap-4 my-5 text-xs">
            <div class="space-y-1">
                <p><span class="text-slate-400">Voucher No:</span> <strong class="font-mono text-slate-900">{{ $purchase->purchase_number }}</strong></p>
                <p><span class="text-slate-400">Date:</span> <strong class="text-slate-900">{{ $purchase->purchase_date->format('d M Y') }}</strong></p>
                <p><span class="text-slate-400">Shift:</span> <span class="capitalize font-semibold text-slate-900">{{ $purchase->shift }} Shift</span></p>
            </div>
            <div class="space-y-1 text-right">
                <p><span class="text-slate-400">Dealer Code:</span> <strong class="font-mono text-slate-900">{{ $purchase->dealer_code ?? '—' }}</strong></p>
                <p><span class="text-slate-400">Dealer Name:</span> <strong class="text-slate-900">{{ $purchase->dealer_name }}</strong></p>
                @if($purchase->dealer && $purchase->dealer->phone)
                    <p><span class="text-slate-400">Contact:</span> <span class="text-slate-700">{{ $purchase->dealer->phone }}</span></p>
                @endif
            </div>
        </div>

        <!-- Product Particulars Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-5">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3">Item / Product</th>
                        <th class="py-2.5 px-3 text-center">Unit</th>
                        <th class="py-2.5 px-3 text-right">Qty</th>
                        <th class="py-2.5 px-3 text-right">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-3 font-semibold text-slate-900">
                            {{ $purchase->product_name }}
                        </td>
                        <td class="py-3 px-3 text-center text-slate-500">
                            {{ $purchase->unit }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                            {{ number_format($purchase->quantity, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-700">
                            {{ number_format($purchase->rate, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 text-sm">
                            ₹ {{ number_format($purchase->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 mb-5 space-y-2 text-xs">
            <div class="flex justify-between items-center text-slate-600">
                <span>Subtotal Amount:</span>
                <span class="font-mono font-bold text-slate-900">₹ {{ number_format($purchase->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span>Paid Amount:</span>
                <span class="font-mono font-semibold text-emerald-600">₹ {{ number_format($purchase->paid_amount, 2) }}</span>
            </div>
            @if($purchase->advance_amount > 0)
                <div class="flex justify-between items-center text-slate-600">
                    <span>Advance Adjusted:</span>
                    <span class="font-mono text-indigo-600">₹ {{ number_format($purchase->advance_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between items-center border-t border-slate-200 pt-2 text-sm font-black">
                <span class="text-slate-900">Remaining Balance:</span>
                <span class="font-mono text-rose-600">₹ {{ number_format($purchase->remaining_amount, 2) }}</span>
            </div>
        </div>

        @if($purchase->note)
            <div class="mb-6 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                <strong class="text-slate-800">Note / Remarks:</strong> {{ $purchase->note }}
            </div>
        @endif

        <!-- Signatures Footer -->
        <div class="pt-8 border-t border-dashed border-slate-300 grid grid-cols-2 gap-4 text-center text-xs text-slate-500">
            <div>
                <div class="border-b border-slate-400 w-32 mx-auto mb-1"></div>
                <span>Supplier / Dealer Signature</span>
            </div>
            <div>
                <div class="border-b border-slate-400 w-32 mx-auto mb-1"></div>
                <span>Authorized Signatory</span>
            </div>
        </div>

        <p class="text-center text-[10px] text-slate-400 mt-6 font-mono">Printed on {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

</body>
</html>
