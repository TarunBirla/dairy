<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Bill - {{ $buyer->name }} ({{ $startDate }} to {{ $endDate }})</title>
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
            @page {
                size: A4;
                margin: 12mm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('buyers.khata', ['buyer_id' => $buyer->id]) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Khata
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
            🖨️ Print Statement Bill
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-6">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">SMART DAIRY MILK BILLING</h1>
                <p class="text-xs text-slate-600 font-medium mt-0.5">Commercial Milk Buyer Periodical Invoice Statement</p>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Statement Date</div>
                <div class="text-sm font-bold text-slate-900">{{ now()->format('d/m/Y') }}</div>
                <div class="text-[11px] text-slate-600 mt-1">Period: <span class="font-bold">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</span> to <span class="font-bold">{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- Buyer Info -->
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs grid grid-cols-2 gap-4">
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400">Buyer Information</div>
                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $buyer->name }}</div>
                <div class="text-slate-600 mt-1">Buyer Code: <span class="font-mono font-bold text-slate-800">{{ $buyer->buyer_code }}</span> | Phone: {{ $buyer->phone ?? 'N/A' }}</div>
                <div class="text-slate-600">Address: {{ $buyer->address ?: ($buyer->taluka ?: 'Local') }}</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-slate-400">Current Outstanding Balance</div>
                <div class="text-xl font-black font-mono text-emerald-800 mt-1">₹ {{ number_format($netReceivable, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Milk Type: <span class="font-bold capitalize">{{ $buyer->milk_type }}</span></div>
            </div>
        </div>

        <!-- Milk Sales Delivered -->
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">Milk Dispatched / Sold</div>
            <table class="w-full text-xs text-left border border-slate-200 border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2 px-2.5 border-r border-slate-200">Date</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Sale #</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Shift</th>
                        <th class="py-2 px-2.5 border-r border-slate-200">Type</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">Qty (L)</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">Rate (₹)</th>
                        <th class="py-2 px-2.5 border-r border-slate-200 text-right">Total (₹)</th>
                        <th class="py-2 px-2.5 text-right">Paid (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sales as $s)
                        <tr>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono">{{ $s->sale_date->format('d/m/Y') }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono text-[11px] font-bold">{{ $s->sale_number }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 capitalize">{{ $s->shift }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 capitalize">{{ $s->milk_type }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono font-bold">{{ number_format($s->quantity_liters, 2) }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono">{{ number_format($s->rate_per_liter, 2) }}</td>
                            <td class="py-1.5 px-2.5 border-r border-slate-100 text-right font-mono font-bold text-slate-900">₹{{ number_format($s->total_amount, 2) }}</td>
                            <td class="py-1.5 px-2.5 text-right font-mono font-medium text-emerald-700">₹{{ number_format($s->paid_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400">No milk sales in this billing period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-2 px-2.5 border-r border-slate-200 uppercase text-[11px]">Total Delivered</td>
                        <td class="py-2 px-2.5 border-r border-slate-200 text-right font-mono text-slate-900 font-black">{{ number_format($totalMilkLiters, 2) }} L</td>
                        <td class="border-r border-slate-200"></td>
                        <td class="py-2 px-2.5 border-r border-slate-200 text-right font-mono text-slate-900 font-black">₹{{ number_format($totalMilkAmount, 2) }}</td>
                        <td class="py-2 px-2.5 text-right font-mono text-emerald-800 font-black">₹{{ number_format($totalPaidOnSales, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payments Received -->
        @if($payments->isNotEmpty())
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2">Separate Payments Received</div>
                <table class="w-full text-xs text-left border border-slate-200 border-collapse">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                        <tr>
                            <th class="py-2 px-2.5 border-r border-slate-200">Date</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Receipt #</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Mode</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Comment / Remarks</th>
                            <th class="py-2 px-2.5 text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($payments as $p)
                            <tr>
                                <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono">{{ $p->payment_date->format('d/m/Y') }}</td>
                                <td class="py-1.5 px-2.5 border-r border-slate-100 font-mono font-bold">{{ $p->receipt_number }}</td>
                                <td class="py-1.5 px-2.5 border-r border-slate-100">{{ $p->payment_mode }}</td>
                                <td class="py-1.5 px-2.5 border-r border-slate-100 text-slate-500">{{ $p->comment ?? '-' }}</td>
                                <td class="py-1.5 px-2.5 text-right font-mono font-bold text-emerald-700">₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td colspan="4" class="py-2 px-2.5 border-r border-slate-200 uppercase text-[11px]">Total Separate Payments</td>
                            <td class="py-2 px-2.5 text-right font-mono text-emerald-800 font-black">₹{{ number_format($totalPaymentsReceived, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Statement Summary Box -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Total Billed</span>
                <div class="text-sm font-black font-mono text-slate-900 mt-0.5">₹{{ number_format($totalMilkAmount, 2) }}</div>
            </div>
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Paid on Sales</span>
                <div class="text-sm font-black font-mono text-emerald-700 mt-0.5">₹{{ number_format($totalPaidOnSales, 2) }}</div>
            </div>
            <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                <span class="text-[10px] font-bold uppercase text-slate-400">Khata Received</span>
                <div class="text-sm font-black font-mono text-emerald-700 mt-0.5">₹{{ number_format($totalPaymentsReceived, 2) }}</div>
            </div>
            <div class="p-2.5 bg-slate-900 text-white rounded-lg">
                <span class="text-[10px] font-bold uppercase opacity-80">Net Balance Due</span>
                <div class="text-base font-black font-mono mt-0.5">₹{{ number_format($netReceivable, 2) }}</div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 pt-8 text-xs border-t border-slate-200">
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Customer Signature</p>
                <p class="text-[11px] text-slate-500 mt-0.5">({{ $buyer->name }})</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Authorized Signatory</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Smart Dairy Sales Department</p>
            </div>
        </div>

    </div>

</body>
</html>
