<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Advance List - Print</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-6">
    <div class="max-w-5xl mx-auto space-y-4">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-300">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Farmer Advance & Loan Summary</h1>
                <p class="text-xs text-slate-500">Outgoing Supplier Loans, Deductions & Balances</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400">Printed: {{ now()->format('d M Y, h:i A') }}</span>
                <div class="no-print mt-2">
                    <button onclick="window.print()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold shadow">
                        Print List
                    </button>
                    <button onclick="window.close()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold ml-2">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Totals Strip -->
        <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 border border-slate-300 rounded text-center text-xs">
            <div>
                <span class="text-slate-500 font-bold uppercase text-[10px]">Total Advance</span>
                <p class="font-bold text-slate-900 text-sm mt-0.5">Rs. {{ number_format($totalAdvance, 2) }}</p>
            </div>
            <div>
                <span class="text-emerald-600 font-bold uppercase text-[10px]">Total Paid</span>
                <p class="font-bold text-emerald-800 text-sm mt-0.5">Rs. {{ number_format($totalPaid, 2) }}</p>
            </div>
            <div>
                <span class="text-rose-600 font-bold uppercase text-[10px]">Total Outstanding Balance</span>
                <p class="font-bold text-rose-700 text-sm mt-0.5">Rs. {{ number_format($totalBalance, 2) }}</p>
            </div>
        </div>

        <!-- Table matching reference -->
        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 border-b border-slate-300 font-bold text-slate-700">
                <tr>
                    <th class="p-2 border-r border-slate-300 text-center w-12">S.No</th>
                    <th class="p-2 border-r border-slate-300">Farmer Code</th>
                    <th class="p-2 border-r border-slate-300">Farmer Name</th>
                    <th class="p-2 border-r border-slate-300 text-right">Advance Amount</th>
                    <th class="p-2 border-r border-slate-300 text-right">Paid</th>
                    <th class="p-2 text-right">Total Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($farmers as $index => $farmer)
                    @php
                        $advTotal = (float) $farmer->advances->sum('amount');
                        $paidTotal = (float) $farmer->advances->sum(function($a) { return $a->total_paid; });
                        $balTotal = max(0, $advTotal - $paidTotal);
                    @endphp
                    <tr>
                        <td class="p-2 border-r border-slate-200 text-center">{{ $index + 1 }}</td>
                        <td class="p-2 border-r border-slate-200 font-mono font-bold">{{ $farmer->farmer_code }}</td>
                        <td class="p-2 border-r border-slate-200 font-semibold">{{ $farmer->name }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono">Rs. {{ number_format($advTotal, 2) }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono">Rs. {{ number_format($paidTotal, 2) }}</td>
                        <td class="p-2 text-right font-mono font-bold text-rose-700">Rs. {{ number_format($balTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-slate-400">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pt-4 border-t border-slate-200 flex justify-between text-[11px] text-slate-500">
            <span>Total Farmers: {{ $farmers->count() }}</span>
            <span>Dairy Management System Report</span>
        </div>
    </div>
</body>
</html>
