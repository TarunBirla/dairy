<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Bill {{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .slip-box { border: none !important; box-shadow: none !important; max-width: 80mm !important; }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Actions (No Print) -->
    <div class="no-print max-w-sm w-full mb-4 flex items-center justify-between">
        <a href="{{ route('pos.index') }}" class="text-xs text-slate-500 hover:text-slate-800 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to POS</span>
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm flex items-center gap-1.5">
            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
            <span>Print Receipt</span>
        </button>
    </div>

    <!-- Thermal Receipt -->
    <div class="slip-box bg-white max-w-sm w-full p-6 rounded-2xl shadow-md border border-slate-200 text-slate-800 font-mono text-xs">
        
        <!-- Header -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <h1 class="text-base font-black uppercase text-slate-900 tracking-wider">
                {{ \App\Models\SystemSetting::get('dairy_name', 'SIMPLE DAIRY') }}
            </h1>
            <p class="text-[10px] text-slate-500">{{ \App\Models\SystemSetting::get('address') }}</p>
            <p class="text-[10px] text-slate-500">Ph: {{ \App\Models\SystemSetting::get('phone') }}</p>
            <div class="mt-2 inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold">
                RETAIL COUNTER INVOICE
            </div>
        </div>

        <!-- Meta -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Bill No:</span>
                <span class="font-bold">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Date & Time:</span>
                <span>{{ $order->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Customer:</span>
                <span class="font-bold">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Payment Mode:</span>
                <span class="uppercase font-bold text-emerald-700">{{ $order->payment_mode }}</span>
            </div>
        </div>

        <!-- Line Items -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-2">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-[10px] text-slate-400 border-b border-slate-100 pb-1">
                        <th>ITEM</th>
                        <th class="text-center">QTY</th>
                        <th class="text-right">PRICE</th>
                        <th class="text-right">AMT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-1.5 font-bold">{{ $item->product ? $item->product->name : 'Product' }}</td>
                            <td class="py-1.5 text-center">{{ $item->quantity }}</td>
                            <td class="py-1.5 text-right">{{ number_format($item->unit_price, 0) }}</td>
                            <td class="py-1.5 text-right font-bold">{{ number_format($item->total_price, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Subtotal:</span>
                <span>₹ {{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Discount:</span>
                    <span>- ₹ {{ number_format($order->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between items-baseline pt-1">
                <span class="font-black text-xs text-slate-900">GRAND TOTAL:</span>
                <span class="text-xl font-black text-slate-900">₹ {{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 text-center space-y-1 text-[10px] text-slate-400">
            <p>Billed by: {{ $order->operator ? $order->operator->name : 'Counter Staff' }}</p>
            <p>Thank you! Visit again.</p>
            <p class="font-bold text-slate-600">*** KEEP RECEIPT FOR EXCHANGES ***</p>
        </div>

    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
