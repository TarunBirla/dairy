<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer List - Print</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 p-6">
    <div class="max-w-6xl mx-auto space-y-4">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-300">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Registered Product Dealers List</h1>
                <p class="text-xs text-slate-500">Dairy Product & Wholesale Suppliers Directory</p>
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

        <!-- Table matching reference columns -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-300">
                <thead class="bg-slate-100 border-b border-slate-300 font-bold text-slate-700">
                    <tr>
                        <th class="p-2 border-r border-slate-300 text-center w-12">S.No</th>
                        <th class="p-2 border-r border-slate-300">Code</th>
                        <th class="p-2 border-r border-slate-300">Name</th>
                        <th class="p-2 border-r border-slate-300">Mobile No</th>
                        <th class="p-2 border-r border-slate-300">Address</th>
                        <th class="p-2 border-r border-slate-300">Details</th>
                        <th class="p-2 border-r border-slate-300">Bank Name</th>
                        <th class="p-2 border-r border-slate-300">Account no</th>
                        <th class="p-2 border-r border-slate-300">IFSC Code</th>
                        <th class="p-2 border-r border-slate-300">Branch</th>
                        <th class="p-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($dealers as $index => $dealer)
                        <tr>
                            <td class="p-2 border-r border-slate-200 text-center">{{ $index + 1 }}</td>
                            <td class="p-2 border-r border-slate-200 font-mono font-bold">{{ $dealer->code }}</td>
                            <td class="p-2 border-r border-slate-200 font-semibold">{{ $dealer->name }}</td>
                            <td class="p-2 border-r border-slate-200 font-mono">{{ $dealer->phone ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $dealer->address ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $dealer->details ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $dealer->bank_name ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200 font-mono">{{ $dealer->account_number ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200 font-mono uppercase">{{ $dealer->ifsc_code ?: '—' }}</td>
                            <td class="p-2 border-r border-slate-200">{{ $dealer->branch ?: '—' }}</td>
                            <td class="p-2 text-center capitalize">{{ $dealer->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-4 text-center text-slate-400">No dealers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-between text-[11px] text-slate-500">
            <span>Total Records: {{ $dealers->count() }}</span>
            <span>Generated from Dairy Management System</span>
        </div>
    </div>
</body>
</html>
