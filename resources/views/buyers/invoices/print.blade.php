<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bill #{{ $invoice->invoice_number }} - {{ $buyer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
        }
        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                margin: 0 auto !important;
                padding: 6mm !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="py-6 px-3 sm:px-6">

    @php
        $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
        $billingInfoRaw = json_decode($settings['center_billing_info'] ?? '{}', true) ?: [];
        $dairyName = $billingInfoRaw['name'] ?? 'SHREE GOPAL DUDH DAIRY';
        $dairyAddress = $billingInfoRaw['address'] ?? '265, BHAIJI BHAWAN, BUS STAND, BEDIYA, Khargone, Madhya Pradesh 451113';
        $dairyPhone = $billingInfoRaw['phone'] ?? '9753672436';
        $dairyEmail = $billingInfoRaw['email'] ?? 'drk.malviya@gmail.com';
        $dairyGstin = $billingInfoRaw['gstin'] ?? '23BAZPN5618R1ZN';
    @endphp

    <!-- Top Action Bar (hidden when printing) -->
    <div class="max-w-[210mm] mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('buyer-invoices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Customer Bills
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
                🖨️ Print Customer Bill (A4)
            </button>
        </div>
    </div>

    <!-- Main Voucher Container -->
    <div class="page-container max-w-[210mm] mx-auto bg-white border border-slate-300 rounded-2xl p-5 shadow-sm space-y-3 text-[11px] leading-tight">

        <!-- 1. Dairy Header & Invoice Title -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-2.5">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-base shrink-0 shadow-xs">
                    🥛
                </div>
                <div>
                    <h1 class="text-base font-black uppercase tracking-tight text-slate-900 leading-none">{{ $dairyName }}</h1>
                    <p class="text-[10px] text-slate-600 mt-1 font-medium">{{ $dairyAddress }}</p>
                    <div class="flex flex-wrap gap-x-3 text-[9.5px] text-slate-500 mt-0.5 font-medium">
                        <span>Tel: <b class="text-slate-700">{{ $dairyPhone }}</b></span>
                        <span>Email: <b class="text-slate-700">{{ $dairyEmail }}</b></span>
                        @if(!empty($dairyGstin))
                            <span>GSTIN: <b class="font-mono text-slate-700">{{ $dairyGstin }}</b></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-black text-[11px] uppercase tracking-wider">
                    Customer Bill / Invoices
                </span>
                <div class="font-mono font-extrabold text-sm text-slate-900 mt-1">#{{ $invoice->invoice_number }}</div>
                <div class="text-[9.5px] text-slate-500">Date: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- 2. Customer & Billing Period Info Row -->
        <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
            <div>
                <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-500 block">Customer (Buyer) Details</span>
                <div class="font-bold text-xs text-slate-900 mt-0.5 uppercase">{{ $buyer->name }}</div>
                <div class="text-[10px] text-slate-600 mt-0.5">Code: <b class="font-mono text-slate-800">{{ $buyer->buyer_code }}</b></div>
                @if(!empty($buyer->phone))
                    <div class="text-[10px] text-slate-600">Phone: <b class="text-slate-800">{{ $buyer->phone }}</b></div>
                @endif
                @if(!empty($buyer->address))
                    <div class="text-[9.5px] text-slate-500 mt-0.5">{{ $buyer->address }}</div>
                @endif
            </div>

            <div class="text-right">
                <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-500 block">Billing Period</span>
                <div class="font-mono font-extrabold text-xs text-slate-900 mt-0.5">
                    {{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}
                </div>
                <div class="text-[10px] text-slate-600 mt-1">
                    Status: <b class="uppercase font-bold {{ $invoice->status === 'generated' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $invoice->status }}</b>
                </div>
            </div>
        </div>

        <!-- 3. Daily Milk Sales Delivery Breakdown -->
        <div>
            <div class="font-bold text-slate-800 text-[10.5px] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                <span>Milk Deliveries / Sales Record</span>
                <span class="text-[9.5px] text-slate-500 font-normal">Total Entries: {{ $sales->count() }}</span>
            </div>

            <table class="w-full text-left border-collapse border border-slate-200 text-[10px]">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-1.5 px-2 border border-slate-200 text-center w-8">#</th>
                        <th class="py-1.5 px-2.5 border border-slate-200">Date</th>
                        <th class="py-1.5 px-2 border border-slate-200 text-center">Shift</th>
                        <th class="py-1.5 px-2.5 border border-slate-200">Milk Type</th>
                        <th class="py-1.5 px-2.5 border border-slate-200 text-right">Liters</th>
                        <th class="py-1.5 px-2 border border-slate-200 text-right">FAT%</th>
                        <th class="py-1.5 px-2 border border-slate-200 text-right">Rate</th>
                        <th class="py-1.5 px-2.5 border border-slate-200 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sales as $idx => $s)
                        <tr>
                            <td class="py-1.5 px-2 border border-slate-200 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-1.5 px-2.5 border border-slate-200 font-medium">{{ \Carbon\Carbon::parse($s->sale_date)->format('d M Y') }}</td>
                            <td class="py-1.5 px-2 border border-slate-200 text-center uppercase text-[9px] font-bold">{{ $s->shift }}</td>
                            <td class="py-1.5 px-2.5 border border-slate-200 font-semibold">{{ ucfirst($s->milk_type) }}</td>
                            <td class="py-1.5 px-2.5 border border-slate-200 text-right font-mono font-bold text-slate-900">{{ number_format($s->quantity_liters, 2) }}</td>
                            <td class="py-1.5 px-2 border border-slate-200 text-right font-mono">{{ number_format($s->fat_percentage ?? 0, 1) }}</td>
                            <td class="py-1.5 px-2 border border-slate-200 text-right font-mono">₹{{ number_format($s->rate_per_liter, 2) }}</td>
                            <td class="py-1.5 px-2.5 border border-slate-200 text-right font-mono font-bold text-slate-900">₹{{ number_format($s->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400 italic">No milk deliveries recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-extrabold text-slate-900 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-1.5 px-2.5 text-right uppercase border border-slate-200">Total Milk Sales:</td>
                        <td class="py-1.5 px-2.5 text-right font-mono border border-slate-200">{{ number_format($invoice->total_quantity, 2) }} L</td>
                        <td colspan="2" class="border border-slate-200"></td>
                        <td class="py-1.5 px-2.5 text-right font-mono border border-slate-200">₹{{ number_format($invoice->milk_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- 4. Final Financial Summary Box -->
        <div class="grid grid-cols-2 gap-3 pt-2">
            <!-- Left: Payments Received in Period -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-500 block">Payments Received in Cycle</span>
                @if($payments->count() > 0)
                    <div class="space-y-1 text-[10px]">
                        @foreach($payments as $p)
                            <div class="flex justify-between items-center border-b border-slate-200/60 pb-1">
                                <span>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M') }} ({{ $p->receipt_number }}) - {{ $p->payment_mode }}</span>
                                <b class="font-mono text-amber-800">₹{{ number_format($p->amount, 2) }}</b>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[10px] text-slate-400 italic">Direct cash payments on sale: ₹{{ number_format($invoice->cash_received, 2) }}</p>
                @endif
            </div>

            <!-- Right: Billing Calculation Box -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5 text-[10.5px]">
                <div class="flex justify-between">
                    <span>Current Milk Bill (A):</span>
                    <b class="font-mono">₹{{ number_format($invoice->milk_amount, 2) }}</b>
                </div>
                <div class="flex justify-between">
                    <span>Previous Outstanding Due (B):</span>
                    <b class="font-mono">₹{{ number_format($invoice->previous_balance, 2) }}</b>
                </div>
                @if((float)$invoice->other_charges > 0)
                    <div class="flex justify-between">
                        <span>Other Charges (C):</span>
                        <b class="font-mono">₹{{ number_format($invoice->other_charges, 2) }}</b>
                    </div>
                @endif
                <div class="flex justify-between text-amber-800">
                    <span>Less: Cash / Payments Received (D):</span>
                    <b class="font-mono">- ₹{{ number_format($invoice->cash_received, 2) }}</b>
                </div>
                @if((float)$invoice->discount_amount > 0)
                    <div class="flex justify-between text-slate-500">
                        <span>Less: Discount Allowed (E):</span>
                        <b class="font-mono">- ₹{{ number_format($invoice->discount_amount, 2) }}</b>
                    </div>
                @endif
                <div class="pt-2 border-t-2 border-slate-900 flex justify-between items-center text-xs font-black text-slate-900">
                    <span>NET BALANCE DUE (RECEIVABLE):</span>
                    <span class="font-mono text-sm text-emerald-800">₹{{ number_format($invoice->net_receivable, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- 5. Signatures Footer -->
        <div class="pt-8 grid grid-cols-2 gap-6 text-[10px] text-slate-600">
            <div class="text-left">
                <div class="border-t border-slate-400 pt-1 w-44 font-semibold uppercase">Customer's Signature</div>
            </div>
            <div class="text-right flex flex-col items-end">
                <div class="border-t border-slate-400 pt-1 w-44 font-semibold uppercase">Authorized Signatory<br><span class="text-[9px] text-slate-400">For {{ $dairyName }}</span></div>
            </div>
        </div>

    </div>

</body>
</html>
