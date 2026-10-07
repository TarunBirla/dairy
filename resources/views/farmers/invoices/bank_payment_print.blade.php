<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Payment Disbursement Sheet ({{ $startDate }} to {{ $endDate }})</title>
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
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <!-- Top Action Toolbar -->
    <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('farmer-invoices.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
                🖨️ Print Sheet (Landscape)
            </button>
        </div>
    </div>

    <!-- Main Bank Sheet Document -->
    <div class="max-w-6xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm print-shadow-none space-y-5">

        <!-- Sheet Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">SMART DAIRY - FARMER BANK DISBURSEMENT SHEET</h1>
                <p class="text-xs text-slate-600 font-medium mt-0.5">Direct Bank Account Transfer / NEFT / RTGS Milk Payment Advice</p>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Billing Cycle</div>
                <div class="text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Generated: {{ now()->format('d/m/Y h:i A') }}</div>
            </div>
        </div>

        <!-- Summary Strip -->
        <div class="grid grid-cols-3 gap-4 bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
            <div>
                <span class="text-slate-500 font-medium">Total Farmers:</span>
                <span class="font-bold text-slate-900 ml-1">{{ $invoices->count() }} Beneficiaries</span>
            </div>
            <div class="text-center">
                <span class="text-slate-500 font-medium">Payment Mode:</span>
                <span class="font-bold text-slate-900 ml-1">Direct Bank Credit</span>
            </div>
            <div class="text-right">
                <span class="text-slate-500 font-medium">Total Disbursement:</span>
                <span class="font-mono font-black text-emerald-800 text-sm ml-1">₹{{ number_format($totalPayment, 2) }}</span>
            </div>
        </div>

        <!-- Beneficiary Bank Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center w-10">#</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Code</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 min-w-[140px]">Farmer Beneficiary</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Bank Name</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Account Number</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">IFSC Code</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-right">Milk (L)</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-right">Net Amount (₹)</th>
                        <th class="py-2.5 px-3 text-center min-w-[100px]">Sign / Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($invoices as $idx => $inv)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 px-3 border-r border-slate-100 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono font-bold text-slate-900">{{ $inv->farmer->farmer_code ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-bold text-slate-900">
                                {{ $inv->farmer->name ?? 'N/A' }}
                                @if(!empty($inv->farmer->phone))
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $inv->farmer->phone }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-3 border-r border-slate-100 text-slate-700">{{ $inv->farmer->bank_name ?? 'N/A' }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono font-bold text-slate-900">{{ $inv->farmer->account_number ?? 'N/A' }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono font-medium text-slate-700">{{ $inv->farmer->ifsc_code ?? 'N/A' }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 text-right font-mono">{{ number_format($inv->total_quantity, 2) }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 text-right font-mono font-bold text-slate-900">₹{{ number_format($inv->net_payment, 2) }}</td>
                            <td class="py-2 px-3 text-center text-slate-400 text-[10px]">
                                {{ $inv->farmer->account_number ? 'Ready' : 'No Bank Details' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">No invoices found for bank disbursement.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300 text-xs">
                    <tr>
                        <td colspan="6" class="py-3 px-3 border-r border-slate-200 uppercase tracking-wider text-[11px]">Grand Total Disbursement</td>
                        <td class="py-3 px-3 border-r border-slate-200 text-right font-mono">{{ number_format($invoices->sum('total_quantity'), 2) }}</td>
                        <td class="py-3 px-3 border-r border-slate-200 text-right font-mono font-black text-emerald-900 text-sm">₹{{ number_format($totalPayment, 2) }}</td>
                        <td class="py-3 px-3 text-center"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Approval Signatures -->
        <div class="grid grid-cols-3 gap-8 pt-10 text-xs border-t border-slate-200">
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Prepared By</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Accounts Clerk</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Checked & Verified</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Dairy Manager</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Approved & Transferred</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Bank Officer / Authorized Signatory</p>
            </div>
        </div>

    </div>

</body>
</html>
