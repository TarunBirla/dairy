<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print { .no-print { display: none !important; } body { background: white !important; padding: 0 !important; } }
    </style>
</head>
<body class="bg-slate-100 p-6 flex flex-col items-center min-h-screen">

    <div class="no-print max-w-2xl w-full mb-4 flex items-center justify-between">
        <a href="{{ route('invoices.index') }}" class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back</span>
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print Invoice</span>
            </button>
        </div>
    </div>

    <div class="bg-white max-w-2xl w-full p-8 rounded-2xl shadow-md border border-slate-200 text-xs text-slate-700">
        <!-- Brand & Title -->
        <div class="border-b border-slate-200 pb-5 mb-5 flex justify-between items-start">
            <div>
                <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ \App\Models\SystemSetting::get('dairy_name', 'SIMPLE DAIRY') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ \App\Models\SystemSetting::get('tagline', 'Fresh, Pure & Natural Dairy Products') }}</p>
                <p class="text-[11px] text-slate-400 mt-1">{{ \App\Models\SystemSetting::get('address') }}</p>
                <p class="text-[11px] text-slate-400">Phone: {{ \App\Models\SystemSetting::get('phone') }}</p>
            </div>
            <div class="text-right">
                <div class="inline-block bg-slate-100 text-slate-800 px-3 py-1 rounded-lg font-black text-sm uppercase">
                    TAX INVOICE
                </div>
                <p class="font-mono font-bold text-sm text-slate-900 mt-2">{{ $invoice->invoice_number }}</p>
                <p class="text-[11px] text-slate-500">Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
                <p class="text-[11px] text-amber-700 font-semibold">Due: {{ $invoice->due_date->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Customer & Billing Meta -->
        <div class="grid grid-cols-2 gap-4 py-3 bg-slate-50 p-4 rounded-xl mb-5">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Billed To:</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $invoice->customer->name }}</p>
                <p class="text-[11px] text-slate-500 font-mono">{{ $invoice->customer->customer_code }} • Ph: {{ $invoice->customer->phone }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $invoice->customer->address }}</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Billing Period:</span>
                <p class="font-bold text-slate-800 mt-0.5">{{ $invoice->period_start->format('d M Y') }} to {{ $invoice->period_end->format('d M Y') }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Payment Status: <b class="uppercase font-bold {{ $invoice->status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $invoice->status }}</b></p>
            </div>
        </div>

        <!-- Line Items -->
        <div class="mb-5">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-slate-200 text-[10px] uppercase text-slate-400">
                    <tr>
                        <th class="py-2.5">DESCRIPTION</th>
                        <th class="py-2.5 text-center">DELIVERED QTY</th>
                        <th class="py-2.5 text-right">RATE (₹)</th>
                        <th class="py-2.5 text-right">TOTAL (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3 font-semibold text-slate-900">{{ $item->description }}</td>
                            <td class="py-3 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="py-3 text-right">₹ {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 text-right font-bold text-slate-900">₹ {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Previous Due -->
        <div class="border-t border-slate-200 pt-3 space-y-1.5 max-w-xs ml-auto text-xs">
            <div class="flex justify-between">
                <span>Current Deliveries Subtotal:</span>
                <span class="font-bold">₹ {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->previous_due > 0)
                <div class="flex justify-between text-amber-700 font-semibold">
                    <span>Previous Outstanding Due:</span>
                    <span>₹ {{ number_format($invoice->previous_due, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                <span class="font-black text-sm text-slate-900">NET AMOUNT DUE:</span>
                <span class="text-xl font-black text-slate-900">₹ {{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-emerald-700">
                <span>Amount Paid:</span>
                <span class="font-bold">₹ {{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between font-black text-amber-700 border-t border-slate-100 pt-1">
                <span>Remaining Balance:</span>
                <span>₹ {{ number_format($invoice->balance_due, 2) }}</span>
            </div>
        </div>

        <!-- Payment Instructions & Bank -->
        <div class="mt-8 pt-4 border-t border-slate-200 grid grid-cols-2 gap-4 text-[11px] text-slate-500">
            <div>
                <p class="font-bold text-slate-800">Payment Modes Accepted:</p>
                <p>UPI ID: dairy@upi • Cash to Delivery Boy</p>
                <p>Google Pay / PhonePe accepted upon delivery</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-slate-800">For Queries / Support:</p>
                <p>Call: {{ \App\Models\SystemSetting::get('phone') }}</p>
                <p>Thank you for choosing pure milk!</p>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
