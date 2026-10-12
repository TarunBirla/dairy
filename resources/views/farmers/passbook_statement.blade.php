<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $farmer->name }} ({{ $farmer->farmer_code }}) - Passbook Statement</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 24px 16px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .toolbar {
            max-width: 900px;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }
        .btn-outline {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }
        .btn-outline:hover {
            background: #f8fafc;
        }
        .btn-primary {
            background: #059669;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #047857;
        }
        .btn-dark {
            background: #0f172a;
            color: #ffffff;
        }
        .btn-dark:hover {
            background: #1e293b;
        }

        .statement-sheet {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        }
        .statement-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .statement-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .statement-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 6px;
        }
        .statement-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Summary Box matching reference image */
        .summary-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 18px 20px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .summary-col {
            padding-right: 12px;
            border-right: 1px solid #e2e8f0;
        }
        .summary-col:last-child {
            border-right: none;
            padding-right: 0;
        }
        .summary-label {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        .summary-val {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .summary-sub {
            font-size: 11px;
            color: #475569;
            margin-top: 6px;
        }
        .text-dr {
            color: #c24158;
        }
        .text-cr {
            color: #059669;
        }
        .tag-dr {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            margin-left: 2px;
        }

        .entries-count {
            font-size: 12.5px;
            color: #334155;
            margin-bottom: 10px;
        }
        .entries-count b {
            font-weight: 800;
            color: #0f172a;
        }

        /* Statement Table matching reference image */
        table.stmt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            border: 1px solid #cbd5e1;
        }
        table.stmt-table thead th {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        table.stmt-table thead th.num {
            text-align: right;
        }
        table.stmt-table td {
            padding: 10px 12px;
            border: 1px solid #94a3b8;
            vertical-align: middle;
            color: #1e293b;
        }
        .row-month-header td {
            background: #ffffff;
            font-weight: 700;
            font-size: 13px;
            color: #0f172a;
            padding: 11px 12px;
            border-top: 1.5px solid #475569;
            border-bottom: 1.5px solid #475569;
        }
        .cell-date {
            width: 90px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            background: #fafafa;
        }
        .cell-details {
            color: #334155;
            line-height: 1.45;
        }
        .cell-debit {
            width: 125px;
            text-align: right;
            font-weight: 600;
            background: #fdf2f4;
            color: #0f172a;
            white-space: nowrap;
        }
        .cell-credit {
            width: 125px;
            text-align: right;
            font-weight: 600;
            background: #eef8f3;
            color: #0f172a;
            white-space: nowrap;
        }
        .cell-balance {
            width: 145px;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
            background: #fafafa;
        }
        .row-month-total td {
            background: #ffffff;
            font-weight: 700;
            color: #0f172a;
            padding: 10px 12px;
            border-top: 1.5px solid #475569;
            border-bottom: 1.5px solid #475569;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .statement-sheet {
                box-shadow: none;
                padding: 12px 16px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar (Hidden when printing) -->
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <a href="{{ route('farmers.show', ['farmer' => $farmer->id, 'tab' => 'passbook', 'passbook_from' => $passbookFrom, 'passbook_to' => $passbookTo, 'passbook_month' => $passbookMonth, 'passbook_type' => $passbookType]) }}" class="btn btn-outline">
                &larr; Back to Passbook
            </a>
            <form action="{{ route('farmers.passbook.statement', $farmer) }}" method="GET" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                <input type="date" name="from_date" value="{{ $passbookFrom }}" style="padding:6px 8px; font-size:11px; border:1px solid #cbd5e1; border-radius:6px;">
                <span style="font-size:11px; color:#64748b;">to</span>
                <input type="date" name="to_date" value="{{ $passbookTo }}" style="padding:6px 8px; font-size:11px; border:1px solid #cbd5e1; border-radius:6px;">
                <select name="month" style="padding:6px 8px; font-size:11px; border:1px solid #cbd5e1; border-radius:6px;">
                    <option value="">All Months</option>
                    @foreach($passbookAvailableMonths as $ymKey => $ymLabel)
                        <option value="{{ $ymKey }}" {{ $passbookMonth === $ymKey ? 'selected' : '' }}>{{ $ymLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline" style="padding:6px 10px;">Apply Filter</button>
            </form>
        </div>
        <div class="toolbar-actions">
            <button type="button" onclick="downloadStatementPdf()" class="btn btn-primary">
                Download PDF
            </button>
            <button type="button" onclick="window.print()" class="btn btn-dark">
                Print Statement
            </button>
        </div>
    </div>

    <!-- Printable / PDF Statement Sheet -->
    <div id="printable-statement-area" class="statement-sheet">
        <div class="statement-header">
            <div class="statement-title">{{ $farmer->name }} Statement</div>
            <div class="statement-subtitle">({{ $passbookStartDateFormatted }} - {{ $passbookEndDateFormatted }})</div>
            <div class="statement-meta">
                Code: <b>{{ $farmer->farmer_code }}</b>
                @if($farmer->village) • Village: <b>{{ $farmer->village }}</b> @endif
                @if($farmer->phone) • Phone: <b>{{ $farmer->phone }}</b> @endif
            </div>
        </div>

        <!-- 4-Column Summary Box -->
        <div class="summary-box">
            <div class="summary-col">
                <div class="summary-label">Opening Balance</div>
                <div class="summary-val {{ $passbookOpeningBalance < 0 ? 'text-dr' : ($passbookOpeningBalance > 0 ? 'text-cr' : '') }}">
                    ₹{{ number_format(abs($passbookOpeningBalance), 2) }}
                    @if($passbookOpeningBalance != 0)
                        <span class="tag-dr">{{ $passbookOpeningBalance < 0 ? 'Dr' : 'Cr' }}</span>
                    @endif
                </div>
                <div class="summary-sub">(on {{ $passbookStartDateFormatted }})</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Total Debit(-)</div>
                <div class="summary-val">₹{{ number_format($passbookTotalDebit, 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Total Credit(+)</div>
                <div class="summary-val">₹{{ number_format($passbookTotalCredit, 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Net Balance</div>
                <div class="summary-val {{ $passbookNetBalance < 0 ? 'text-dr' : ($passbookNetBalance > 0 ? 'text-cr' : '') }}">
                    ₹{{ number_format(abs($passbookNetBalance), 2) }}
                    @if($passbookNetBalance != 0)
                        <span class="tag-dr">{{ $passbookNetBalance < 0 ? 'Dr' : 'Cr' }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="entries-count">
            No. of Entries: <b>{{ count($passbookEntries) }} (Date Range)</b>
        </div>

        <table class="stmt-table">
            <thead>
                <tr>
                    <th style="width: 90px;">Date</th>
                    <th>Details</th>
                    <th class="num" style="width: 125px;">Debit(-)</th>
                    <th class="num" style="width: 125px;">Credit(+)</th>
                    <th class="num" style="width: 145px;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $monthIdx = 0; @endphp
                @forelse($passbookMonthlyGroups as $ym => $mGroup)
                    @php $monthIdx++; @endphp
                    <!-- Month Section Header Row -->
                    <tr class="row-month-header">
                        @if($monthIdx === 1)
                            <td colspan="3">{{ $mGroup['first_date_full'] }}</td>
                            <td colspan="2" style="text-align: right; font-weight: 500; font-size: 12px; color: #475569;">
                                (Opening Balance:
                                <span class="{{ $mGroup['opening_balance'] < 0 ? 'text-dr' : ($mGroup['opening_balance'] > 0 ? 'text-cr' : '') }}" style="font-weight:700;">
                                    {{ number_format(abs($mGroup['opening_balance']), 2) }})
                                </span>
                                @if($mGroup['opening_balance'] != 0)
                                    <span class="tag-dr">{{ $mGroup['opening_balance'] < 0 ? 'Dr' : 'Cr' }}</span>
                                @endif
                            </td>
                        @else
                            <td colspan="5">{{ $mGroup['month_year'] }}</td>
                        @endif
                    </tr>

                    <!-- Month Entries -->
                    @foreach($mGroup['entries'] as $row)
                        <tr>
                            <td class="cell-date">{{ $row['date_short'] }}</td>
                            <td class="cell-details">{{ $row['details'] }}</td>
                            <td class="cell-debit">
                                {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}
                            </td>
                            <td class="cell-credit">
                                {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}
                            </td>
                            <td class="cell-balance">
                                @if(abs($row['balance']) < 0.005)
                                    <span>0.00</span>
                                @else
                                    <span class="{{ $row['balance'] < 0 ? 'text-dr' : 'text-cr' }}">
                                        {{ number_format(abs($row['balance']), 2) }}
                                    </span>
                                    <span class="tag-dr">{{ $row['balance'] < 0 ? 'Dr' : 'Cr' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <!-- Month Subtotal Row -->
                    <tr class="row-month-total">
                        <td colspan="2">{{ $mGroup['month_name'] }} Total</td>
                        <td style="text-align: right;">{{ number_format($mGroup['total_debit'], 2) }}</td>
                        <td style="text-align: right;">{{ number_format($mGroup['total_credit'], 2) }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 32px; color: #94a3b8;">
                            No passbook entries found for the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function downloadStatementPdf() {
            const element = document.getElementById('printable-statement-area');
            const filename = "{{ preg_replace('/[^A-Za-z0-9_\-]/', '_', $farmer->farmer_code . '_' . $farmer->name) }}_Statement.pdf";
            const opt = {
                margin: [8, 8, 8, 8],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        window.addEventListener('DOMContentLoaded', function () {
            @if(!empty($autoDownload))
                setTimeout(function () {
                    downloadStatementPdf();
                }, 400);
            @elseif(!empty($autoPrint))
                setTimeout(function () {
                    window.print();
                }, 300);
            @endif
        });
    </script>
</body>
</html>
