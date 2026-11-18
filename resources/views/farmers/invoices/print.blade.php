<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} - {{ $farmer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
        }
        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                margin: 0 auto !important;
                padding: 6mm !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="py-6 px-3 sm:px-6">

    @php
        $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
        $billingInfoRaw = json_decode($settings['center_billing_info'] ?? '{}', true) ?: [];
        $dairyName = $billingInfoRaw['name'] ?? 'SHREE GOPAL DUDH DAIRY';
        $dairyAddress = $billingInfoRaw['address'] ?? '265, BHAIJI BHAWAN, BUS STAND, BEDIYA, Khargone, Madhya Pradesh 451113';
        $dairyPhone = $billingInfoRaw['phone'] ?? '9753672436';
        $dairyEmail = $billingInfoRaw['email'] ?? 'drk.malviya@gmail.com';
        $dairyGstin = $billingInfoRaw['gstin'] ?? '23BAZPN5618R1ZN';

        $openingWallet = (float) ($invoice->opening_wallet_balance ?? 0);
        $walletDeducted = (float) ($invoice->wallet_deducted_amount ?? $invoice->total_deduction ?? 0);
        $closingWallet = (float) ($invoice->closing_wallet_balance ?? max(0, $openingWallet - $walletDeducted));
        $catBreakdown = $invoice->getCategoryDeductionsBreakdown();

        $additionsList = [];
        if ((float)$invoice->transport_credit > 0) $additionsList[] = ['name' => 'Transport Subsidy', 'amt' => (float)$invoice->transport_credit];
        if ((float)$invoice->subsidy_credit > 0) $additionsList[] = ['name' => 'Govt / Dairy Subsidy', 'amt' => (float)$invoice->subsidy_credit];
        if ((float)$invoice->incentive_credit > 0) $additionsList[] = ['name' => 'Quality Incentive', 'amt' => (float)$invoice->incentive_credit];
        if ((float)$invoice->other_credit > 0) $additionsList[] = ['name' => 'Other Additions', 'amt' => (float)$invoice->other_credit];
    @endphp

    <!-- Top Action Bar (hidden when printing) -->
    <div class="max-w-[210mm] mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('farmer-invoices.show', $invoice->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs transition">
            ← Back to Invoice View
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-xl shadow-xs transition">
                🖨️ Print Single-Page A4 Invoice
            </button>
        </div>
    </div>

    <!-- Main Single Page A4 Portrait Voucher Container -->
    <div class="page-container max-w-[210mm] mx-auto bg-white border border-slate-300 rounded-2xl p-5 shadow-sm space-y-3 text-[11px] leading-tight">

        <!-- 1. Dairy Header & Invoice Title -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-2.5">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-base shrink-0 shadow-xs">
                    🐄
                </div>
                <div>
                    <h1 class="text-base font-black uppercase tracking-tight text-slate-900 leading-none">{{ $dairyName }}</h1>
                    <p class="text-[10px] text-slate-600 mt-1 font-medium">{{ $dairyAddress }}</p>
                    <div class="flex flex-wrap gap-x-3 text-[9.5px] text-slate-500 mt-0.5 font-medium">
                        <span>Tel: <b class="text-slate-700">{{ $dairyPhone }}</b></span>
                        <span>Email: <b class="text-slate-700">{{ $dairyEmail }}</b></span>
                        @if(!empty($dairyGstin))
                            <span>GSTIN: <b class="font-mono text-slate-700">{{ $dairyGstin }}</b></span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right shrink-0">
                <div class="inline-block px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider rounded-md">
                    Milk Purchase Bill
                </div>
                <div class="text-xs font-black font-mono text-slate-900 mt-1">#{{ $invoice->invoice_number }}</div>
                <div class="text-[9.5px] text-slate-500 mt-0.5">Date: <span class="font-bold text-slate-700">{{ $invoice->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>

        <!-- 2. Farmer Info & Billing Cycle Banner -->
        <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[10.5px]">
            <div>
                <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Farmer Details</span>
                <div class="font-bold text-slate-900 text-xs mt-0.5">
                    {{ $farmer->name }}
                    @if(!empty($farmer->name_hi)) <span class="text-slate-600 font-normal">({{ $farmer->name_hi }})</span> @endif
                </div>
                <div class="text-slate-600 text-[10px] mt-0.5">
                    Code: <span class="font-mono font-bold text-slate-900">{{ $farmer->farmer_code }}</span>
                    @if($farmer->phone) | Mob: <span class="font-medium text-slate-800">{{ $farmer->phone }}</span> @endif
                </div>
                <div class="text-slate-500 text-[9.5px]">Route: {{ $farmer->route->name ?? 'Direct' }} | Center: {{ $farmer->collectionCenter->name ?? 'Main Center' }}</div>
            </div>
            <div class="border-l border-slate-200 pl-3">
                <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">Settlement & Bank Details</span>
                <div class="text-[10px] text-slate-700 mt-0.5">
                    Bank: <b class="text-slate-900">{{ $farmer->bank_name ?: 'Cash Account' }}</b>
                    @if($farmer->account_number) | A/C: <b class="font-mono text-slate-900">{{ $farmer->account_number }}</b> @endif
                </div>
                <div class="text-[10px] text-slate-700">
                    IFSC: <b class="font-mono text-slate-900">{{ $farmer->ifsc_code ?: '-' }}</b>
                </div>
                <div class="text-[10px] font-medium text-slate-700 mt-0.5">
                    Billing Period: <b class="text-emerald-800">{{ $invoice->period_start->format('d/m/Y') }}</b> to <b class="text-emerald-800">{{ $invoice->period_end->format('d/m/Y') }}</b>
                </div>
            </div>
        </div>

        <!-- 3. Milk Procurement Collections Table -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-800">1. Milk Collection Details</span>
                <span class="text-[9.5px] text-slate-500 font-medium">{{ count($collections) }} Entries Recorded</span>
            </div>
            <table class="w-full text-left border border-slate-200 border-collapse text-[10px]">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 text-[9.5px]">
                    <tr>
                        <th class="py-1 px-2 border-r border-slate-200">Date</th>
                        <th class="py-1 px-1.5 border-r border-slate-200">Shift</th>
                        <th class="py-1 px-1.5 border-r border-slate-200">Type</th>
                        <th class="py-1 px-2 border-r border-slate-200 text-right">Qty (L)</th>
                        <th class="py-1 px-1.5 border-r border-slate-200 text-right">FAT %</th>
                        <th class="py-1 px-1.5 border-r border-slate-200 text-right">CLR / SNF</th>
                        <th class="py-1 px-2 border-r border-slate-200 text-right">Rate (₹)</th>
                        <th class="py-1 px-2 text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($collections as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="py-1 px-2 border-r border-slate-100 font-mono">{{ \Carbon\Carbon::parse($c->collection_date)->format('d/m/Y') }}</td>
                            <td class="py-1 px-1.5 border-r border-slate-100 capitalize font-medium">{{ $c->shift }}</td>
                            <td class="py-1 px-1.5 border-r border-slate-100 capitalize">{{ $c->milk_type }}</td>
                            <td class="py-1 px-2 border-r border-slate-100 text-right font-mono font-bold text-slate-900">{{ number_format($c->quantity_liters, 2) }}</td>
                            <td class="py-1 px-1.5 border-r border-slate-100 text-right font-mono">{{ number_format($c->fat ?? $c->fat_percentage ?? 0, 1) }}</td>
                            <td class="py-1 px-1.5 border-r border-slate-100 text-right font-mono text-slate-500">{{ ($c->clr ?? $c->clr_reading) ? number_format($c->clr ?? $c->clr_reading, 1) : '-' }} / {{ ($c->snf ?? $c->snf_percentage) ? number_format($c->snf ?? $c->snf_percentage, 1) : '-' }}</td>
                            <td class="py-1 px-2 border-r border-slate-100 text-right font-mono">₹{{ number_format($c->applied_rate ?? $c->calculated_rate ?? $c->rate_per_liter ?? 0, 2) }}</td>
                            <td class="py-1 px-2 text-right font-mono font-bold text-slate-900">₹{{ number_format($c->net_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-2 text-center text-slate-400">No collection entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300 text-[10px]">
                    <tr>
                        <td colspan="3" class="py-1 px-2 border-r border-slate-200 uppercase tracking-wider">Total Milk Procurement</td>
                        <td class="py-1 px-2 border-r border-slate-200 text-right font-mono font-black text-slate-900">{{ number_format($invoice->total_quantity, 2) }} L</td>
                        <td colspan="3" class="border-r border-slate-200"></td>
                        <td class="py-1 px-2 text-right font-mono font-black text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- 4. Two Columns: Category Deductions & Additions -->
        <div class="grid grid-cols-2 gap-3">
            
            <!-- Category-Wise Deductions Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-slate-100 px-2.5 py-1 font-bold text-[9.5px] uppercase tracking-wider text-slate-700 border-b border-slate-200 flex justify-between">
                    <span>2. Deductions (Category-Wise)</span>
                    <span class="text-rose-700 font-mono">-₹{{ number_format($invoice->total_deduction, 2) }}</span>
                </div>
                <table class="w-full text-left text-[9.5px]">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-1 px-2 border-r border-slate-200">Category</th>
                            <th class="py-1 px-1.5 border-r border-slate-200 text-right">Opening</th>
                            <th class="py-1 px-1.5 border-r border-slate-200 text-right text-rose-700">Deducted</th>
                            <th class="py-1 px-1.5 text-right text-amber-700">Closing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($catBreakdown as $b)
                            <tr>
                                <td class="py-0.5 px-2 border-r border-slate-100 font-medium">{{ $b['name'] }}</td>
                                <td class="py-0.5 px-1.5 border-r border-slate-100 text-right font-mono">₹{{ number_format($b['original_balance'], 2) }}</td>
                                <td class="py-0.5 px-1.5 border-r border-slate-100 text-right font-mono font-bold text-rose-700">-₹{{ number_format($b['deducted_amount'], 2) }}</td>
                                <td class="py-0.5 px-1.5 text-right font-mono font-bold text-amber-700">₹{{ number_format($b['remaining_balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-1 text-center text-slate-400">No deductions for this billing period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-[9.5px]">
                        <tr>
                            <td class="py-1 px-2 border-r border-slate-200">Total</td>
                            <td class="py-1 px-1.5 border-r border-slate-200 text-right font-mono">₹{{ number_format(collect($catBreakdown)->sum('original_balance'), 2) }}</td>
                            <td class="py-1 px-1.5 border-r border-slate-200 text-right font-mono font-black text-rose-700">-₹{{ number_format($invoice->total_deduction, 2) }}</td>
                            <td class="py-1 px-1.5 text-right font-mono font-bold text-amber-700">₹{{ number_format(collect($catBreakdown)->sum('remaining_balance'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Additions / Credits Breakdown -->
            <div class="border border-slate-200 rounded-xl overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="bg-slate-100 px-2.5 py-1 font-bold text-[9.5px] uppercase tracking-wider text-slate-700 border-b border-slate-200 flex justify-between">
                        <span>3. Additions & Subsidies</span>
                        <span class="text-amber-700 font-mono">+₹{{ number_format($invoice->total_credit, 2) }}</span>
                    </div>
                    <table class="w-full text-left text-[9.5px]">
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($additionsList as $add)
                                <tr>
                                    <td class="py-1 px-2.5 font-medium">{{ $add['name'] }}</td>
                                    <td class="py-1 px-2.5 text-right font-mono font-bold text-amber-700">+₹{{ number_format($add['amt'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-2 text-center text-slate-400">No extra subsidies or bonuses for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Farmer Deduction Wallet Strip -->
                <div class="bg-amber-50/70 border-t border-amber-200 p-2 text-[10px]">
                    <div class="flex items-center justify-between font-bold text-slate-800 mb-1">
                        <span class="flex items-center gap-1 text-amber-900">
                            <span>💼 Farmer Deduction Wallet:</span>
                        </span>
                        <span class="font-mono text-emerald-800">Remaining Balance: ₹{{ number_format($closingWallet, 2) }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-1 text-[9px] text-slate-600 bg-white p-1 rounded border border-amber-200">
                        <div>Opening: <b class="font-mono text-slate-800">₹{{ number_format($openingWallet, 2) }}</b></div>
                        <div>Deducted: <b class="font-mono text-rose-700">-₹{{ number_format($walletDeducted, 2) }}</b></div>
                        <div>Closing: <b class="font-mono text-emerald-700">₹{{ number_format($closingWallet, 2) }}</b></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 5. Final Financial Settlement & Payable Box -->
        <div class="grid grid-cols-4 gap-2 bg-slate-900 text-white rounded-xl p-2.5 text-[10.5px]">
            <div class="bg-white/10 rounded-lg p-2">
                <span class="text-[9px] font-semibold text-slate-300 uppercase block tracking-wider">Milk Amount (C)</span>
                <span class="text-sm font-black font-mono mt-0.5 block">₹{{ number_format($invoice->milk_amount, 2) }}</span>
            </div>
            <div class="bg-white/10 rounded-lg p-2">
                <span class="text-[9px] font-semibold text-slate-300 uppercase block tracking-wider">Total Credit (B)</span>
                <span class="text-sm font-black font-mono text-amber-300 mt-0.5 block">+₹{{ number_format($invoice->total_credit, 2) }}</span>
            </div>
            <div class="bg-white/10 rounded-lg p-2">
                <span class="text-[9px] font-semibold text-slate-300 uppercase block tracking-wider">Total Deductions (A)</span>
                <span class="text-sm font-black font-mono text-rose-300 mt-0.5 block">-₹{{ number_format($invoice->total_deduction, 2) }}</span>
            </div>
            <div class="bg-emerald-600 rounded-lg p-2">
                <span class="text-[9px] font-black uppercase text-emerald-100 block tracking-wider">Net Payable Amount</span>
                <span class="text-base font-black font-mono text-white mt-0.5 block">₹{{ number_format($invoice->net_payment, 2) }}</span>
            </div>
        </div>

        <!-- 6. Payment Status & Authorizations -->
        <div class="grid grid-cols-2 gap-8 pt-3 text-[10px] border-t border-slate-200 items-end">
            <div class="text-center pt-5 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Farmer Signature / Thumb Impression</p>
                <p class="text-[9px] text-slate-500 mt-0.5">({{ $farmer->name }} - {{ $farmer->farmer_code }})</p>
            </div>
            <div class="text-center pt-5 border-t border-dashed border-slate-400">
                <p class="font-bold text-slate-800">Authorized Signatory & Seal</p>
                <p class="text-[9px] text-slate-500 mt-0.5">{{ $dairyName }}</p>
            </div>
        </div>

    </div>

</body>
</html>