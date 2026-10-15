<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settlement Slip - {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .slip-card { border: none !important; box-shadow: none !important; }
        }
        @page {
            size: auto;
            margin: 10mm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 text-xs font-sans p-4 sm:p-8">

    <div class="max-w-2xl mx-auto space-y-4">
        <!-- Action Toolbar -->
        <div class="no-print flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
            <a href="{{ url()->previous() }}" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 font-bold text-slate-600 text-xs">
                ← Back
            </a>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">
                    🖨️ Print Settlement Slip
                </button>
            </div>
        </div>

        <!-- The Slip Card -->
        <div class="slip-card bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-5">
            <!-- Header: Dairy Branding -->
            <div class="text-center pb-4 border-b border-dashed border-slate-300">
                <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase">{{ config('app.name', 'Mobile Dairy Management') }}</h1>
                <p class="text-[11px] text-slate-500 font-medium">Milk Collection Center & Cooperative Society</p>
                <div class="mt-2 inline-block px-3 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] uppercase tracking-wider">
                    Official Farmer Milk Settlement Invoice
                </div>
            </div>

            <!-- Invoice & Farmer Details Grid -->
            <div class="grid grid-cols-2 gap-4 text-xs pb-4 border-b border-slate-200">
                <div class="space-y-1">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Farmer Information</span>
                    <span class="text-sm font-extrabold text-slate-900 block">{{ $farmer->name }}</span>
                    <span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded inline-block">Code: {{ $farmer->farmer_code }}</span>
                    <span class="text-[11px] text-slate-600 block">Village: {{ $farmer->village ?: '—' }} | Center: {{ $farmer->collectionCenter->name ?? 'Main' }}</span>
                    <span class="text-[11px] text-slate-600 block">Phone: {{ $farmer->phone ?: '—' }}</span>
                </div>
                <div class="space-y-1 text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Settlement Details</span>
                    <span class="font-mono text-xs font-bold text-slate-800 block">Invoice #: {{ $invoice->invoice_number }}</span>
                    <span class="text-[11px] text-slate-700 block">
                        Period: <b>{{ \Carbon\Carbon::parse($invoice->from_date)->format('d M') }}</b> to <b>{{ \Carbon\Carbon::parse($invoice->to_date)->format('d M Y') }}</b>
                    </span>
                    <span class="text-[11px] text-slate-600 block">Generated: {{ $invoice->created_at->format('d M Y, h:i A') }}</span>
                    <span class="mt-1 inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        Status: {{ $invoice->status }}
                    </span>
                </div>
            </div>

            <!-- 1. Eligible Milk Supply Breakdown -->
            <div class="space-y-2">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">1. Milk Collection Earnings</h4>
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase border-y border-slate-200">
                        <tr>
                            <th class="py-1.5 px-2">Type</th>
                            <th class="py-1.5 px-2 text-right">Quantity (Litres)</th>
                            <th class="py-1.5 px-2 text-right">Avg Rate (₹)</th>
                            <th class="py-1.5 px-2 text-right">Gross Milk (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if((float)($cowLiters ?? 0) > 0 || (float)($cowAmount ?? 0) > 0)
                            <tr>
                                <td class="py-1.5 px-2 font-bold text-slate-700">Cow Milk</td>
                                <td class="py-1.5 px-2 text-right font-semibold">{{ number_format($cowLiters ?? 0, 2) }} L</td>
                                <td class="py-1.5 px-2 text-right">₹ {{ $cowLiters > 0 ? number_format($cowAmount / $cowLiters, 2) : '0.00' }}</td>
                                <td class="py-1.5 px-2 text-right font-bold text-slate-900">₹ {{ number_format($cowAmount ?? 0, 2) }}</td>
                            </tr>
                        @endif
                        @if((float)($buffaloLiters ?? 0) > 0 || (float)($buffaloAmount ?? 0) > 0)
                            <tr>
                                <td class="py-1.5 px-2 font-bold text-slate-700">Buffalo Milk</td>
                                <td class="py-1.5 px-2 text-right font-semibold">{{ number_format($buffaloLiters ?? 0, 2) }} L</td>
                                <td class="py-1.5 px-2 text-right">₹ {{ $buffaloLiters > 0 ? number_format($buffaloAmount / $buffaloLiters, 2) : '0.00' }}</td>
                                <td class="py-1.5 px-2 text-right font-bold text-slate-900">₹ {{ number_format($buffaloAmount ?? 0, 2) }}</td>
                            </tr>
                        @endif
                        @if(empty($cowLiters) && empty($buffaloLiters))
                            <tr>
                                <td class="py-1.5 px-2 font-bold text-slate-700">Total Milk Collection</td>
                                <td class="py-1.5 px-2 text-right font-semibold">{{ number_format($invoice->total_liters ?? $invoice->milk_liters, 2) }} L</td>
                                <td class="py-1.5 px-2 text-right">—</td>
                                <td class="py-1.5 px-2 text-right font-bold text-slate-900">₹ {{ number_format($invoice->milk_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="bg-slate-50 font-bold border-t border-slate-200">
                            <td class="py-1.5 px-2">Total Milk Earnings</td>
                            <td class="py-1.5 px-2 text-right">{{ number_format($invoice->total_liters ?? $invoice->milk_liters, 2) }} L</td>
                            <td class="py-1.5 px-2 text-right"></td>
                            <td class="py-1.5 px-2 text-right text-emerald-700 font-black">₹ {{ number_format($invoice->milk_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 2. Deductions & Recoveries Breakdown -->
            <div class="space-y-2 pt-2">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">2. Recoveries & Approved Deductions</h4>
                <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 text-xs">
                    <div class="flex items-center justify-between p-2">
                        <span>Advance / Loan Recovery</span>
                        <span class="font-bold text-rose-600">- ₹ {{ number_format($invoice->advance_deduction ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2">
                        <span>Cattle Feed / Product Sales Deduction</span>
                        <span class="font-bold text-rose-600">- ₹ {{ number_format($invoice->cattle_feed_deduction ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2">
                        <span>General / Other Approved Charges</span>
                        <span class="font-bold text-rose-600">- ₹ {{ number_format($invoice->other_deduction ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2 bg-slate-50 font-bold">
                        <span>Total Deductions & Recoveries</span>
                        <span class="font-black text-rose-700">- ₹ {{ number_format($invoice->total_deductions, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Final Net Calculation Box -->
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 p-4 rounded-xl border border-emerald-200 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span>Eligible Milk Earnings:</span>
                    <span class="font-bold">₹ {{ number_format($invoice->milk_amount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span>Less Applied Deductions:</span>
                    <span class="font-bold text-rose-600">- ₹ {{ number_format($invoice->total_deductions, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-emerald-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-emerald-800 tracking-wider block">Net Payable to Farmer</span>
                        <span class="text-xs text-slate-500">Credited to farmer passbook</span>
                    </div>
                    <span class="text-2xl font-black text-emerald-800">₹ {{ number_format($invoice->net_amount, 2) }}</span>
                </div>
            </div>

            <!-- Signatures Strip -->
            <div class="pt-10 grid grid-cols-2 gap-8 text-center text-xs text-slate-500">
                <div class="border-t border-slate-300 pt-2">
                    <span class="block font-semibold">Farmer Signature / Thumb</span>
                </div>
                <div class="border-t border-slate-300 pt-2">
                    <span class="block font-semibold">Dairy Secretary / Authorized Signatory</span>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-400 pt-2">
                Computer-generated settlement slip | Mobile Dairy Software Pro Edition
            </div>
        </div>
    </div>

</body>
</html>
