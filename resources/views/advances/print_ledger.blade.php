<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Ledger - {{ $farmer->farmer_code }} {{ $farmer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-8">
    <div class="max-w-4xl mx-auto space-y-4">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-300">
            <div>
                <h1 class="text-lg font-bold text-slate-900">
                    Advance Ledger - <span class="font-mono">{{ $farmer->farmer_code }}</span> {{ $farmer->name }}
                </h1>
                <p class="text-xs text-slate-500">Phone: {{ $farmer->phone ?: '—' }} • Village: {{ $farmer->village ?: '—' }}</p>
            </div>
            <div class="no-print">
                <button onclick="window.print()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold shadow">
                    Print Ledger
                </button>
                <button onclick="window.close()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold ml-2">
                    Close
                </button>
            </div>
        </div>

        <!-- Ledger Table matching media_1791384832203.png -->
        <table class="w-full text-left text-xs border border-slate-300">
            <thead class="bg-slate-100 border-b border-slate-300 font-bold text-slate-700">
                <tr>
                    <th class="p-2 border-r border-slate-300 text-center w-12">SrNo</th>
                    <th class="p-2 border-r border-slate-300">Date</th>
                    <th class="p-2 border-r border-slate-300">Voucher</th>
                    <th class="p-2 border-r border-slate-300">Remark</th>
                    <th class="p-2 border-r border-slate-300 text-right">Advance Amount</th>
                    <th class="p-2 border-r border-slate-300 text-right">Paid</th>
                    <th class="p-2 border-r border-slate-300">Paid Date</th>
                    <th class="p-2 border-r border-slate-300 text-right">Rate</th>
                    <th class="p-2 border-r border-slate-300 text-right">Principal Bal</th>
                    <th class="p-2 border-r border-slate-300 text-right">Interest Bal</th>
                    <th class="p-2 border-r border-slate-300 text-right">Total Balance</th>
                    <th class="p-2">Mode</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($advances as $index => $adv)
                    <tr>
                        <td class="p-2 border-r border-slate-200 text-center">{{ $index + 1 }}</td>
                        <td class="p-2 border-r border-slate-200 font-mono">{{ $adv->advance_date->format('d M Y') }}</td>
                        <td class="p-2 border-r border-slate-200 font-mono font-bold">{{ $adv->voucher_no ?: str_pad($adv->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td class="p-2 border-r border-slate-200">{{ $adv->notes ?: '—' }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono font-semibold">Rs. {{ number_format($adv->amount, 2) }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono text-emerald-700">Rs. {{ number_format($adv->total_paid, 2) }}</td>
                        <td class="p-2 border-r border-slate-200 font-mono">{{ $adv->paid_date ? $adv->paid_date->format('d M Y') : '-' }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono">{{ number_format($adv->interest_rate ?? 0, 2) }}%</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono">Rs. {{ number_format($adv->principal_balance, 2) }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono">Rs. {{ number_format($adv->interest_balance ?? 0, 2) }}</td>
                        <td class="p-2 border-r border-slate-200 text-right font-mono font-bold text-rose-700">Rs. {{ number_format($adv->total_balance, 2) }}</td>
                        <td class="p-2">{{ $adv->payment_mode ?: 'Cash' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-4 text-center text-slate-400">No advance records found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($advances->count() > 0)
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td class="p-2 text-center" colspan="4">Total</td>
                        <td class="p-2 text-right font-mono">Rs. {{ number_format($advances->sum('amount'), 2) }}</td>
                        <td class="p-2 text-right font-mono text-emerald-700">Rs. {{ number_format($advances->sum(function($a){ return $a->total_paid; }), 2) }}</td>
                        <td class="p-2">-</td>
                        <td class="p-2">-</td>
                        <td class="p-2 text-right font-mono">Rs. {{ number_format($advances->sum(function($a){ return $a->principal_balance; }), 2) }}</td>
                        <td class="p-2 text-right font-mono">Rs. {{ number_format($advances->sum('interest_balance'), 2) }}</td>
                        <td class="p-2 text-right font-mono text-rose-700">Rs. {{ number_format($advances->sum(function($a){ return $a->total_balance; }), 2) }}</td>
                        <td class="p-2">-</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="pt-4 border-t border-slate-200 flex justify-between text-[11px] text-slate-500">
            <span>Printed on {{ now()->format('d M Y, h:i A') }}</span>
            <span>Authorized Signatory ___________________</span>
        </div>
    </div>
</body>
</html>
