<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Sale Slip - {{ $milkSale->sale_number }}</title>
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
            .print-border {
                border-color: #000000 !important;
            }
            @page {
                size: 80mm auto;
                margin: 4mm;
            }
        }
    </style>
</head>
<body class="py-6 px-4">

    <!-- Top Action Toolbar -->
    <div class="max-w-md mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('milk-sales.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3 py-1.5 rounded-xl transition">
            ← Back
        </a>
        <button onclick="window.print()" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5">
            🖨️ Print Slip
        </button>
    </div>

    <!-- Printable Slip Card -->
    <div class="max-w-md mx-auto bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4 text-xs">
        
        <!-- Dairy Header -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <h1 class="text-base font-black uppercase tracking-tight text-slate-900">SMART DAIRY</h1>
            <p class="text-[10px] text-slate-500 font-medium">Milk Sale & Dispatch Voucher</p>
        </div>

        <!-- Voucher Info -->
        <div class="flex justify-between items-center text-[11px]">
            <div>
                <span class="text-slate-400">Slip No:</span>
                <span class="font-mono font-bold text-slate-900">{{ $milkSale->sale_number }}</span>
            </div>
            <div>
                <span class="text-slate-400">Date:</span>
                <span class="font-bold text-slate-800">{{ $milkSale->sale_date->format('d/m/Y') }} ({{ ucfirst($milkSale->shift) }})</span>
            </div>
        </div>

        <!-- Customer & Vehicle Info -->
        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-[11px] space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Customer:</span>
                <span class="font-bold text-slate-900">[{{ $milkSale->buyer->buyer_code }}] {{ $milkSale->buyer->name }}</span>
            </div>
            @if(!empty($milkSale->buyer->phone))
                <div class="flex justify-between">
                    <span class="text-slate-500">Phone:</span>
                    <span class="text-slate-700">{{ $milkSale->buyer->phone }}</span>
                </div>
            @endif
            @if($milkSale->vehicle)
                <div class="flex justify-between">
                    <span class="text-slate-500">Vehicle:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $milkSale->vehicle->vehicle_number }} ({{ $milkSale->vehicle->driver_name ?? 'Driver' }})</span>
                </div>
            @endif
        </div>

        <!-- Milk Quality & Rate Breakdown Table -->
        <table class="w-full text-left border border-slate-200 border-collapse text-[11px]">
            <tr class="bg-slate-50 font-bold border-b border-slate-200">
                <th class="p-1.5 border-r border-slate-200">Item</th>
                <th class="p-1.5 border-r border-slate-200 text-right">Qty (L)</th>
                <th class="p-1.5 border-r border-slate-200 text-right">Rate (₹)</th>
                <th class="p-1.5 text-right">Amount (₹)</th>
            </tr>
            <tr>
                <td class="p-1.5 border-r border-slate-100 capitalize font-medium">{{ $milkSale->milk_type }} Milk</td>
                <td class="p-1.5 border-r border-slate-100 text-right font-mono font-bold">{{ number_format($milkSale->quantity_liters, 2) }}</td>
                <td class="p-1.5 border-r border-slate-100 text-right font-mono">{{ number_format($milkSale->rate_per_liter, 2) }}</td>
                <td class="p-1.5 text-right font-mono font-bold text-slate-900">{{ number_format($milkSale->total_amount, 2) }}</td>
            </tr>
            @if($milkSale->fat_percentage > 0 || $milkSale->snf_percentage > 0)
                <tr class="text-[10px] text-slate-500 bg-slate-50/50">
                    <td colspan="4" class="p-1.5">
                        FAT: <span class="font-bold text-slate-700">{{ number_format($milkSale->fat_percentage, 1) }}%</span> | 
                        SNF: <span class="font-bold text-slate-700">{{ number_format($milkSale->snf_percentage, 1) }}%</span> | 
                        CLR: <span class="font-bold text-slate-700">{{ number_format($milkSale->clr_reading, 1) }}</span>
                    </td>
                </tr>
            @endif
        </table>

        <!-- Settlement Summary -->
        <div class="space-y-1.5 pt-1 text-[11px]">
            <div class="flex justify-between font-bold text-slate-800">
                <span>Total Amount:</span>
                <span class="font-mono text-sm text-emerald-800">₹{{ number_format($milkSale->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Paid Amount:</span>
                <span class="font-mono">₹{{ number_format($milkSale->paid_amount, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-rose-700 pt-1 border-t border-slate-100">
                <span>Balance Due:</span>
                <span class="font-mono">₹{{ number_format($milkSale->balance_amount, 2) }}</span>
            </div>
            @if(!empty($milkSale->description))
                <div class="text-[10px] text-slate-400 italic pt-1">
                    Note: {{ $milkSale->description }}
                </div>
            @endif
        </div>

        <!-- Footer / Sign -->
        <div class="pt-6 border-t border-dashed border-slate-300 flex justify-between text-[10px] text-slate-500">
            <div>Customer Sign</div>
            <div>Authorized Sign</div>
        </div>

    </div>

</body>
</html>
