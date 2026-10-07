<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Advance Statement</title>
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
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <div class="max-w-6xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('vehicles.advances.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Advances
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
            🖨️ Print Sheet
        </button>
    </div>

    <div class="max-w-6xl mx-auto bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-5">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
                <h1 class="text-xl font-black uppercase tracking-tight text-slate-900">SMART DAIRY - FLEET & VEHICLE ADVANCE STATEMENT</h1>
                <p class="text-xs text-slate-600 font-medium mt-0.5">Comprehensive Vehicle Advance Disbursement & Recovery Ledger</p>
            </div>
            <div class="text-right">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Statement Date</div>
                <div class="text-sm font-bold text-slate-900">{{ now()->format('d/m/Y h:i A') }}</div>
            </div>
        </div>

        <!-- KPI Strip -->
        <div class="grid grid-cols-3 gap-4 bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
            <div>
                <span class="text-slate-500 font-medium">Total Advance Issued:</span>
                <span class="font-mono font-bold text-slate-900 ml-1">Rs. {{ number_format($totalAdvance, 2) }}</span>
            </div>
            <div class="text-center">
                <span class="text-slate-500 font-medium">Total Paid / Recovered:</span>
                <span class="font-mono font-bold text-emerald-800 ml-1">Rs. {{ number_format($totalPaid, 2) }}</span>
            </div>
            <div class="text-right">
                <span class="text-slate-500 font-medium">Net Outstanding Balance:</span>
                <span class="font-mono font-black text-rose-700 ml-1">Rs. {{ number_format($totalBalance, 2) }}</span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-center w-10">#</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Voucher No</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Date</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Vehicle Number</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Driver Name</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-right">Advance (Rs.)</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-right">Paid (Rs.)</th>
                        <th class="py-2.5 px-3 border-r border-slate-200 text-right">Balance (Rs.)</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($advances as $idx => $a)
                        <tr>
                            <td class="py-2 px-3 border-r border-slate-100 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono font-bold text-slate-800">{{ $a->voucher_no }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono">{{ $a->advance_date->format('d/m/Y') }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-mono font-bold uppercase">{{ $a->vehicle->vehicle_number ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 font-semibold">{{ $a->driver->name ?? ($a->vehicle->driver_name ?? '-') }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 text-right font-mono font-bold text-slate-900">{{ number_format($a->amount, 2) }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 text-right font-mono text-emerald-700 font-bold">{{ number_format($a->paid_amount, 2) }}</td>
                            <td class="py-2 px-3 border-r border-slate-100 text-right font-mono font-black text-rose-700">{{ number_format($a->balance_amount, 2) }}</td>
                            <td class="py-2 px-3 text-center capitalize font-bold text-[10px] {{ $a->status === 'settled' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $a->status }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-slate-400">No records to display.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-2.5 px-3 border-r border-slate-200 uppercase text-[11px]">Grand Total</td>
                        <td class="py-2.5 px-3 border-r border-slate-200 text-right font-mono font-black text-slate-900">Rs. {{ number_format($totalAdvance, 2) }}</td>
                        <td class="py-2.5 px-3 border-r border-slate-200 text-right font-mono font-black text-emerald-800">Rs. {{ number_format($totalPaid, 2) }}</td>
                        <td class="py-2.5 px-3 border-r border-slate-200 text-right font-mono font-black text-rose-800">Rs. {{ number_format($totalBalance, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Approval Signatures -->
        <div class="grid grid-cols-3 gap-8 pt-10 text-xs border-t border-slate-200">
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Prepared By</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Fleet Accounts</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Checked & Verified</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Transport Supervisor</p>
            </div>
            <div class="text-center pt-8 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Approved By</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Authorized Signatory</p>
            </div>
        </div>

    </div>

</body>
</html>
