<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $invoice->invoice_number }} - {{ $farmer->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #000000;
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
            .invoice-page {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                page-break-inside: avoid !important;
            }
        }
        .bill-table {
            width: 100%;
            border: 1.5px solid #000000;
            border-collapse: collapse;
        }
        .bill-table th, .bill-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
        }
    </style>
</head>
<body class="py-4 px-2 sm:px-6">

    @php
        $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
        $billingInfoRaw = json_decode($settings['center_billing_info'] ?? '{}', true) ?: [];
        $dairyName = $billingInfoRaw['name'] ?? 'Shree Gopal Dudh Dairy Bediya';
        $dairyAddress = $billingInfoRaw['address'] ?? 'Bediya, Bediya, khargone';
        $dairyPhone = $billingInfoRaw['phone'] ?? '9753441810';
        $contactPerson = 'KAMLESH MALVIYA';

        $catBreakdown = $invoice->getCategoryDeductionsBreakdown();

        // Calculate total remaining debt and deposits
        $totalRemainingDebt = 0.0;
        $totalDeposits = 0.0;

        $deductionRows = [];
        foreach ($catBreakdown as $b) {
            $cName = $b['name'] ?? 'Deduction';
            $orig = (float) ($b['original_balance'] ?? 0);
            $ded = (float) ($b['deducted_amount'] ?? 0);
            $rem = (float) ($b['remaining_balance'] ?? 0);
            $isWallet = !empty($b['is_wallet']);
            $cLower = strtolower($cName);

            $isDeposit = str_contains($cLower, 'grocery') || str_contains($cLower, 'tds') || str_contains($cLower, 'deposit') || str_contains($cLower, 'anamat');

            if ($isDeposit) {
                $depAmt = $orig > 0 ? $orig : $ded;
                $deductionRows[] = [
                    'type' => $cName,
                    'total_balance' => $orig > 0 ? number_format($orig, 2) : '-',
                    'deduction' => number_format($ded, 2),
                    'balance' => '-',
                    'deposit' => $depAmt > 0 ? number_format($depAmt, 2) : '-',
                ];
                $totalDeposits += $depAmt;
            } else {
                $isDebt = $orig > 0 || str_contains($cLower, 'advance') || str_contains($cLower, 'loan') || str_contains($cLower, 'feed') || str_contains($cLower, 'food') || $isWallet;

                if ($isDebt && $orig > 0) {
                    $totalRemainingDebt += $rem;
                    $deductionRows[] = [
                        'type' => $cName,
                        'total_balance' => '-' . number_format($orig, 2),
                        'deduction' => number_format($ded, 2),
                        'balance' => $rem > 0 ? '-' . number_format($rem, 2) : '-',
                        'deposit' => '-',
                    ];
                } else {
                    $deductionRows[] = [
                        'type' => $cName,
                        'total_balance' => '-',
                        'deduction' => number_format($ded, 2),
                        'balance' => '-',
                        'deposit' => '-',
                    ];
                }
            }
        }

        if ((float)($farmer->anamat ?? 0) > 0 && !collect($deductionRows)->contains(fn($r) => str_contains(strtolower($r['type']), 'anamat'))) {
            $anamatVal = (float)$farmer->anamat;
            $deductionRows[] = [
                'type' => 'Deposit / Anamat',
                'total_balance' => number_format($anamatVal, 2),
                'deduction' => '0.00',
                'balance' => '-',
                'deposit' => number_format($anamatVal, 2),
            ];
            $totalDeposits += $anamatVal;
        }

        // Group collections by Shift
        $shiftGroups = $collections->groupBy(function($c) {
            return ucfirst(strtolower($c->shift));
        });

        $grandQuantity = (float) $invoice->total_quantity;
        $grandAmount = (float) $invoice->milk_amount;
        $grandAvgFat = $collections->count() > 0 ? (float) $collections->avg('fat') : 0.0;
        $grandAvgClr = $collections->count() > 0 ? (float) ($collections->avg('clr') ?: $collections->avg('snf')) : 0.0;
        $grandAvgRate = $grandQuantity > 0 ? ($grandAmount / $grandQuantity) : 0.0;
    @endphp

    <!-- Top Action Bar (hidden when printing) -->
    <div class="max-w-[210mm] mx-auto mb-3 flex items-center justify-between no-print">
        <a href="{{ route('farmer-invoices.show', $invoice->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-black bg-white border border-slate-300 px-3 py-1.5 rounded-lg shadow-xs transition">
            ← Back to Invoice View
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-black hover:bg-slate-800 px-4 py-1.5 rounded-lg shadow-xs transition cursor-pointer">
            🖨️ Print Receipt
        </button>
    </div>

    <!-- Main Printable Receipt Page (Matching reference media_1795106381953_1f928e0e.png) -->
    <div class="invoice-page max-w-[210mm] mx-auto bg-white p-5 rounded-xl border border-slate-300 shadow-sm text-black text-xs leading-normal">

        <!-- 1. Dairy Header -->
        <div class="flex items-start justify-between pb-2 mb-2">
            <!-- Left Logo -->
            <div class="w-28 shrink-0">
                <img src="{{ asset('logo.PNG') }}" alt="Logo" class="h-16 w-auto object-contain"
                     onerror="this.style.display='none'">
            </div>

            <!-- Center Dairy Details -->
            <div class="text-center flex-1 px-2">
                <h1 class="text-base font-extrabold uppercase tracking-wide text-black">{{ $dairyName }}</h1>
                <p class="text-xs text-black mt-0.5 font-medium">{{ $dairyAddress }}</p>
            </div>

            <!-- Right Bill No & Contact -->
            <div class="w-48 shrink-0 text-right text-[11px] font-semibold">
                <div>Bill No. <span class="font-bold">{{ $invoice->invoice_number }}</span></div>
                <div class="mt-0.5">{{ $contactPerson }}: <span class="font-mono">{{ $dairyPhone }}</span></div>
            </div>
        </div>

        <!-- 2. Farmer Name & Period Bar -->
        <div class="flex items-center justify-between font-bold text-[13px] border-t border-black pt-2 pb-1.5 mb-1">
            <div>
                {{ $farmer->farmer_code }} - {{ strtoupper($farmer->name) }}
            </div>
            <div>
                Date: {{ $invoice->period_start->format('d/m/Y') }} To {{ $invoice->period_end->format('d/m/Y') }}
            </div>
        </div>

        <!-- 3. Milk Collection Details Table (Shift Grouped) -->
        <div class="mb-3">
            @foreach($shiftGroups as $shiftName => $sCollections)
                @php
                    $sQty = (float) $sCollections->sum('quantity_liters');
                    $sAmount = (float) $sCollections->sum(function($c) {
                        return (float) $c->gross_amount > 0 ? (float) $c->gross_amount : ((float) $c->quantity_liters * (float) ($c->applied_rate ?: $c->calculated_rate ?: 0));
                    });
                    $sAvgFat = (float) $sCollections->avg('fat');
                    $sAvgClr = (float) ($sCollections->avg('clr') ?: $sCollections->avg('snf'));
                    $sAvgRate = $sQty > 0 ? ($sAmount / $sQty) : 0.0;
                @endphp

                <div class="text-center font-bold text-xs py-1">{{ $shiftName }}</div>

                <table class="bill-table text-[11px] mb-1">
                    <thead>
                        <tr class="font-bold text-center">
                            <th class="w-16">Date</th>
                            <th class="w-16">M</th>
                            <th class="text-right w-24">Liter</th>
                            <th class="text-right w-16">FAT</th>
                            <th class="text-right w-16">CLR</th>
                            <th class="text-right w-20">Rate</th>
                            <th class="text-right w-28">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sCollections as $c)
                            @php
                                $cMilk = (float) $c->gross_amount > 0 ? (float) $c->gross_amount : ((float) $c->quantity_liters * (float) ($c->applied_rate ?: $c->calculated_rate ?: 0));
                                $mCode = strtoupper(substr($c->milk_type, 0, 1)) . (strtolower($c->shift) === 'evening' ? '1' : '0');
                            @endphp
                            <tr>
                                <td class="text-center font-mono">{{ \Carbon\Carbon::parse($c->collection_date)->format('d') }}</td>
                                <td class="text-center font-bold">{{ $mCode }}</td>
                                <td class="text-right font-mono">{{ number_format($c->quantity_liters, 2) }}</td>
                                <td class="text-right font-mono">{{ number_format($c->fat ?? 0, 1) }}</td>
                                <td class="text-right font-mono">{{ number_format(($c->clr ?: $c->snf) ?? 0, 1) }}</td>
                                <td class="text-right font-mono">{{ number_format($c->applied_rate ?: $c->calculated_rate ?: 0, 2) }}</td>
                                <td class="text-right font-mono font-bold">{{ number_format($cMilk, 2) }}</td>
                            </tr>
                        @endforeach
                        <!-- Shift Subtotal Row -->
                        <tr class="font-bold">
                            <td colspan="2" class="text-center">Total</td>
                            <td class="text-right font-mono">{{ number_format($sQty, 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($sAvgFat, 1) }}</td>
                            <td class="text-right font-mono">{{ number_format($sAvgClr, 1) }}</td>
                            <td class="text-right font-mono">{{ number_format($sAvgRate, 2) }}</td>
                            <td class="text-right font-mono">{{ number_format($sAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach

            <!-- Grand Total Row (if more than 1 shift group or single summary) -->
            @if($shiftGroups->count() > 1)
                <table class="bill-table text-[11px] font-bold">
                    <tbody>
                        <tr>
                            <td colspan="2" class="text-center font-bold w-32 uppercase">Total</td>
                            <td class="text-right font-mono w-24">{{ number_format($grandQuantity, 2) }}</td>
                            <td class="text-right font-mono w-16">{{ number_format($grandAvgFat, 1) }}</td>
                            <td class="text-right font-mono w-16">{{ number_format($grandAvgClr, 1) }}</td>
                            <td class="text-right font-mono w-20">{{ number_format($grandAvgRate, 2) }}</td>
                            <td class="text-right font-mono w-28">{{ number_format($grandAmount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        <!-- 4. Deductions Table (Matching Mobile Dairy Screenshot) -->
        <div class="mb-3">
            <table class="bill-table text-[11px]">
                <thead>
                    <tr class="font-bold text-center">
                        <th class="text-left w-48">Deduction Type</th>
                        <th class="text-right w-28">Total Balance</th>
                        <th class="text-right w-24">Deduction</th>
                        <th class="text-right w-28">Balance</th>
                        <th class="text-right w-24">Deposit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductionRows as $row)
                        <tr>
                            <td class="font-medium text-left">{{ $row['type'] }}</td>
                            <td class="text-right font-mono">{{ $row['total_balance'] }}</td>
                            <td class="text-right font-mono font-bold">{{ $row['deduction'] }}</td>
                            <td class="text-right font-mono">{{ $row['balance'] }}</td>
                            <td class="text-right font-mono">{{ $row['deposit'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-2">No deductions recorded</td>
                        </tr>
                    @endforelse
                    <!-- Deductions Total Row -->
                    <tr class="font-bold">
                        <td class="text-left">Total</td>
                        <td class="text-right font-mono">-</td>
                        <td class="text-right font-mono">{{ number_format($invoice->total_deduction, 2) }}</td>
                        <td class="text-right font-mono">{{ $totalRemainingDebt > 0 ? '-' . number_format($totalRemainingDebt, 2) : '-' }}</td>
                        <td class="text-right font-mono">{{ $totalDeposits > 0 ? number_format($totalDeposits, 2) : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 5. Bottom Summary Single Box (Matching reference media_1795106381953_1f928e0e.png) -->
        <div class="border-[1.5px] border-black p-2 font-bold text-[12px] flex items-center justify-between mb-2">
            <div>Amount: <span class="font-mono font-extrabold">{{ number_format($invoice->total_amount, 2) }}</span></div>
            <div>Deduction: <span class="font-mono font-extrabold">{{ number_format($invoice->total_deduction, 2) }}</span></div>
            <div>Payment: <span class="font-mono font-extrabold">{{ number_format($invoice->net_payment, 2) }}</span></div>
            <div>Current Balance: <span class="font-mono font-extrabold">{{ $totalRemainingDebt > 0 ? '- ' . number_format($totalRemainingDebt, 2) : '0.00' }}</span></div>
        </div>

        <!-- 6. Footer Hindi Legal Notice -->
        <div class="text-center font-bold text-[11px] text-black pt-1">
            प्रिय दुग्ध प्रदायक बंधु अपने बिल को अच्छी तरह से जांच ले व 10 दिनों के अन्दर त्रुटि अगर हो तो दिखाकर निदान करे
        </div>

    </div>

</body>
</html>