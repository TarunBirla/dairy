<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Voucher #{{ $sale->sale_number }}</title>
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
        <a href="{{ route('product-sales.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            ← Back to Sales
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
            🖨️ Print Receipt
        </button>
    </div>

    <!-- Printable Slip Card -->
    <div class="w-full max-w-xl bg-white rounded-2xl shadow-lg border border-slate-200 p-6 sm:p-8">
        
        <!-- Header -->
        <div class="text-center border-b border-slate-200 pb-4">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">GOPAL DUDH DAIRY</h1>
            <p class="text-xs text-slate-500 mt-0.5">Milk Collection Center & Cattle Feed Suppliers</p>
            <div class="mt-2 inline-block px-3 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold rounded-full uppercase tracking-wider">
                PRODUCT SALE RECEIPT / SLIP
            </div>
        </div>

        <!-- Meta Details -->
        <div class="grid grid-cols-2 gap-4 my-5 text-xs">
            <div class="space-y-1">
                <p><span class="text-slate-400">Bill No:</span> <strong class="font-mono text-slate-900">{{ $sale->sale_number }}</strong></p>
                <p><span class="text-slate-400">Date:</span> <strong class="text-slate-900">{{ $sale->sale_date->format('d M Y') }}</strong></p>
                <p><span class="text-slate-400">Payment:</span> <span class="font-semibold text-emerald-700">{{ $sale->payment_mode }}</span></p>
            </div>
            <div class="space-y-1 text-right">
                <p><span class="text-slate-400">Farmer Code:</span> <strong class="font-mono text-slate-900">{{ $sale->farmer_code ?? '—' }}</strong></p>
                <p><span class="text-slate-400">Farmer Name:</span> <strong class="text-slate-900 capitalize">{{ $sale->farmer_name }}</strong></p>
                @if($sale->farmer && $sale->farmer->phone)
                    <p><span class="text-slate-400">Phone:</span> <span class="text-slate-700">{{ $sale->farmer->phone }}</span></p>
                @endif
            </div>
        </div>

        <!-- Particulars Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden mb-5">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3">Item Name</th>
                        <th class="py-2.5 px-3 text-center">UOM</th>
                        <th class="py-2.5 px-3 text-right">Qty</th>
                        <th class="py-2.5 px-3 text-right">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-3 font-semibold text-slate-900">
                            {{ $sale->product_name }}
                        </td>
                        <td class="py-3 px-3 text-center text-slate-500 uppercase">
                            {{ $sale->unit }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                            {{ number_format($sale->quantity, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-700">
                            {{ number_format($sale->rate, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 text-sm">
                            ₹ {{ number_format($sale->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 mb-5 space-y-2 text-xs">
            <div class="flex justify-between items-center text-slate-600">
                <span>Total Amount:</span>
                <span class="font-mono font-bold text-slate-900">₹ {{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span>Paid Amount:</span>
                <span class="font-mono font-semibold text-emerald-600">₹ {{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center border-t border-slate-200 pt-2 text-sm font-black">
                <span class="text-slate-900">Remaining Balance / Due:</span>
                <span class="font-mono {{ $sale->remaining_amount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                    ₹ {{ number_format($sale->remaining_amount, 2) }}
                </span>
            </div>
        </div>

        @if($sale->remarks)
            <div class="mb-6 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600">
                <strong class="text-slate-800">Remarks:</strong> {{ $sale->remarks }}
            </div>
        @endif

        <!-- Signatures Footer -->
        <div class="pt-8 border-t border-dashed border-slate-300 grid grid-cols-2 gap-4 text-center text-xs text-slate-500">
            <div>
                <div class="border-b border-slate-400 w-32 mx-auto mb-1"></div>
                <span>Farmer / Buyer Signature</span>
            </div>
            <div>
                <div class="border-b border-slate-400 w-32 mx-auto mb-1"></div>
                <span>Authorized Signatory</span>
            </div>
        </div>

        <p class="text-center text-[10px] text-slate-400 mt-6 font-mono">Thank you for your business! • Printed: {{ now()->format('d-m-Y H:i') }}</p>
    </div>

</body>
</html>
