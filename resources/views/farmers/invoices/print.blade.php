<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $invoice->invoice_number }} - {{ $farmer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border-color: #000000 !important;
            }
            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <!-- Top Action Bar (hidden in print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('farmer-invoices.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
                🖨️ Print Bill
            </button>
        </div>
    </div>

    <!-- Main Printable Bill Sheet matching media_1791388722646.png / media_1791388740424.png -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm print-shadow-none space-y-6">

        <!-- Dairy Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">SMART DAIRY MILK PROCURING</h1>
                <p class="text-xs text-slate-600 font-medium mt-0.5">Automated Milk Collection & Farmer Settlement Voucher</p>
                <p class="text-[11px] text-slate-500">Contact: +91 98765 43210 | Email: support@smartdairy.com</p>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Bill Number</div>
                <div class="text-base font-black font-mono text-emerald-800">{{ $invoice->invoice_number }}</div>
                <div class="text-[11px] text-slate-600 mt-1">Date: <span class="font-bold">{{ $invoice->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- Farmer & Billing Info Banner -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400">Farmer Details</div>
                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $farmer->name }} {{ $farmer->name_hi ? ' (' . $farmer->name_hi . ')' : '' }}</div>
                <div class="text-slate-600 mt-1">Code: <span class="font-mono font-bold text-slate-800">{{ $farmer->farmer_code }}</span> | Phone: {{ $farmer->phone ?? 'N/A' }}</div>
                <div class="text-slate-600">Address: {{ $farmer->address ?? 'Local Village' }}</div>
            </div>
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400">Payment & Bank Details</div>
                <div class="text-slate-700 mt-0.5">Bank: <span class="font-semibold text-slate-900">{{ $farmer->bank_name ?? 'N/A' }}</span></div>
                <div class="text-slate-700">A/C No: <span class="font-mono font-bold text-slate-900">{{ $farmer->account_number ?? 'N/A' }}</span></div>
                <div class="text-slate-700">IFSC Code: <span class="font-mono font-semibold text-slate-900">{{ $farmer->ifsc_code ?? 'N/A' }}</span></div>
                <div class="text-slate-700 font-medium">Billing Period: <span class="font-bold text-slate-900">{{ $invoice->period_start->format('d/m/Y') }}</span> to <span class="font-bold text-slate-900">{{ $invoice->period_end->format('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- Milk Collections Table matching Screenshot 2/3 -->
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">1. Milk Procurement Collections</div>
            <table class="w-full text-xs text-left border border-slate-200 border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2 px-2.5 border-r border-slate-200">Date</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Shift</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Milk</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">Liter</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">FAT %</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">CLR / SNF</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">Rate (₹)</th>
                        <th class="py-2 px-2.5 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($collections as $c)
                        <tr>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono">{{ \Carbon\Carbon::parse($c->collection_date)->format('d/m/Y') }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 capitalize">{{ $c->shift }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 capitalize">{{ $c->milk_type }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono font-bold text-slate-900">{{ number_format($c->quantity_liters, 2) }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono">{{ number_format($c->fat_percentage, 1) }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono">{{ $c->clr_reading ? number_format($c->clr_reading, 1) : ($c->snf_percentage ? number_format($c->snf_percentage, 1) : '-') }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono">{{ number_format($c->rate_per_liter, 2) }}</td>
                            <td class="py-1.5 px-2.5 text-right font-mono font-bold text-slate-900">{{ number_format($c->net_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-3 text-center text-slate-400">No milk collection entries in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="3" class="py-2 px-2.5 border-r border-slate-200 uppercase tracking-wider text-[11px]">Total Milk Collection</td>
                        <td class="py-2 px-2.5 border-r border-slate-200 text-right font-mono font-black text-slate-900">{{ number_format($invoice->total_quantity, 2) }} L</td>
                        <td colspan="3" class="border-r border-slate-200"></td>
                        <td class="py-2 px-2.5 text-right font-mono font-black text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Deductions Table -->
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">2. Deductions & Adjustments</div>
            <table class="w-full text-xs text-left border border-slate-200 border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2 px-2.5 border-r border-slate-200">Date</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Deduction Type</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Description / Remarks</th>
                        <th class="py-2 px-2.5 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($deductions as $d)
                        <tr>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono">{{ \Carbon\Carbon::parse($d->entry_date)->format('d/m/Y') }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 capitalize font-medium">{{ str_replace('_', ' ', $d->deduction_type) }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-slate-500">{{ $d->remarks ?? '-' }}</td>
                            <td class="py-1.5 px-2.5 text-right font-mono font-bold {{ $d->transaction_type === 'received' ? 'text-amber-700' : 'text-rose-700' }}">
                                {{ $d->transaction_type === 'received' ? '+ ₹' : '- ₹' }}{{ number_format($d->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-center text-slate-400">No deductions recorded for this billing cycle.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="3" class="py-2 px-2.5 border-r border-slate-200 uppercase tracking-wider text-[11px]">Total Deductions</td>
                        <td class="py-2 px-2.5 text-right font-mono font-black text-rose-700">- ₹{{ number_format($invoice->total_deduction, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Financial Settlement Calculation Box matching Screenshot 2/3 -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Milk Amount (C)</span>
                <div class="text-sm font-black font-mono text-slate-900 mt-0.5">₹{{ number_format($invoice->milk_amount, 2) }}</div>
            </div>
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Total Credit (B)</span>
                <div class="text-sm font-black font-mono text-amber-700 mt-0.5">+ ₹{{ number_format($invoice->total_credit, 2) }}</div>
            </div>
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Total Deduction (A)</span>
                <div class="text-sm font-black font-mono text-rose-700 mt-0.5">- ₹{{ number_format($invoice->total_deduction, 2) }}</div>
            </div>
            <div class="p-2.5 bg-slate-900 text-white rounded-lg">
                <span class="text-[10px] font-bold uppercase opacity-80">Net Payable Amount</span>
                <div class="text-base font-black font-mono mt-0.5">₹{{ number_format($invoice->net_payment, 2) }}</div>
            </div>
        </div>

        <!-- Authorization & Signatures -->
        <div class="grid grid-cols-2 gap-8 pt-10 text-xs border-t border-slate-200">
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Farmer Signature / Thumbprint</p>
                <p class="text-[11px] text-slate-500 mt-0.5">({{ $farmer->name }})</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Authorized Signatory</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Smart Dairy Procurement Branch</p>
            </div>
        </div>

    </div>

</body>
</html>
