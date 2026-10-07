<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Receipt {{ $collection->receipt_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .slip-box { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 80mm !important; }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen p-4">

    <!-- Actions Bar (No Print) -->
    <div class="no-print max-w-sm w-full mb-4 flex items-center justify-between">
        <a href="{{ route('collections.index') }}" class="text-xs text-slate-500 hover:text-slate-800 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Back to Collections</span>
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm flex items-center gap-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print Slip</span>
            </button>
            <a href="https://api.whatsapp.com/send?phone=91{{ $collection->farmer->phone }}&text=Milk%20Slip%20{{ $collection->receipt_number }}%3A%20{{ $collection->quantity_liters }}L%20FAT%3A{{ $collection->fat }}%25%20Amount%3A%20INR%20{{ $collection->net_amount }}" target="_blank" class="px-3.5 py-1.5 bg-green-500 hover:bg-green-600 text-white font-bold text-xs rounded-lg shadow-sm flex items-center gap-1.5">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                <span>WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Thermal 80mm Printable Slip -->
    <div class="slip-box bg-white max-w-sm w-full p-6 rounded-2xl shadow-md border border-slate-200 text-slate-800 font-mono text-xs">
        
        <!-- Header -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <h1 class="text-base font-black uppercase text-slate-900 tracking-wider">
                {{ \App\Models\SystemSetting::get('dairy_name', 'SIMPLE DAIRY') }}
            </h1>
            <p class="text-[10px] text-slate-500">{{ \App\Models\SystemSetting::get('address', 'Dairy Processing Zone, Indore') }}</p>
            <p class="text-[10px] text-slate-500">Ph: {{ \App\Models\SystemSetting::get('phone', '+91 98765 43210') }}</p>
            <div class="mt-2 inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold">
                MILK PROCUREMENT SLIP
            </div>
        </div>

        <!-- Meta Details -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Receipt No:</span>
                <span class="font-bold">{{ $collection->receipt_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Date & Time:</span>
                <span>{{ $collection->collection_date->format('d/m/Y') }} • {{ ucfirst($collection->shift) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Center:</span>
                <span>{{ $collection->collectionCenter ? $collection->collectionCenter->name : 'Main Depot' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Farmer:</span>
                <span class="font-bold text-right">{{ $collection->farmer->farmer_code }} - {{ $collection->farmer->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Village:</span>
                <span>{{ $collection->farmer->village ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Milk & Quality Specs -->
        <div class="py-3 border-b border-dashed border-slate-300 space-y-1.5">
            <div class="flex justify-between">
                <span class="text-slate-500">Milk Type:</span>
                <span class="font-bold uppercase">{{ $collection->milk_type }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Milk Quantity:</span>
                <span class="font-black text-sm">{{ $collection->quantity_liters }} Liters</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">FAT / SNF:</span>
                <span class="font-bold">{{ $collection->fat }}% / {{ $collection->snf }}%</span>
            </div>
            @if($collection->clr)
                <div class="flex justify-between">
                    <span class="text-slate-500">CLR Reading:</span>
                    <span>{{ $collection->clr }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-slate-500">Rate / Liter:</span>
                <span class="font-bold">₹ {{ number_format($collection->applied_rate, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Gross Value:</span>
                <span>₹ {{ number_format($collection->gross_amount, 2) }}</span>
            </div>
            @if($collection->bonus > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>+ Bonus:</span>
                    <span>₹ {{ number_format($collection->bonus, 2) }}</span>
                </div>
            @endif
            @if($collection->deduction > 0)
                <div class="flex justify-between text-rose-600">
                    <span>- Deduction:</span>
                    <span>₹ {{ number_format($collection->deduction, 2) }}</span>
                </div>
            @endif
        </div>

        <!-- Total Payout -->
        <div class="py-3 border-b border-dashed border-slate-300">
            <div class="flex justify-between items-baseline">
                <span class="font-bold text-xs">NET AMOUNT:</span>
                <span class="text-lg font-black text-slate-900">₹ {{ number_format($collection->net_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                <span>Farmer Ledger Balance:</span>
                <span class="font-bold">₹ {{ number_format($collection->farmer->current_balance, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 text-center space-y-1 text-[10px] text-slate-400">
            <p>Operator: {{ $collection->operator ? $collection->operator->name : 'Station Operator' }}</p>
            <p>Thank you for supplying pure milk!</p>
            <p class="font-bold text-slate-600">*** COMPUTER GENERATED SLIP ***</p>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
