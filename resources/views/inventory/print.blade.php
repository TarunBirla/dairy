<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Inventory Report - {{ config('app.name', 'Dairy MS') }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            color: #1e293b;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 11px;
        }
        .meta-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 11px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-size: 10px;
            text-transform: uppercase;
            color: #334155;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .in-stock {
            color: #047857;
            font-weight: bold;
        }
        .out-stock {
            color: #be123c;
            font-weight: bold;
        }
        .summary-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary-card {
            border: 1px solid #059669;
            background: #ecfdf5;
            padding: 10px 16px;
            border-radius: 6px;
            display: inline-block;
            text-align: right;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px dashed #64748b;
            text-align: center;
            font-size: 11px;
            color: #475569;
            padding-top: 5px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 16px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #059669; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Print Stock Sheet
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 8px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>{{ config('app.name', 'Dairy Management System') }}</h1>
        <p>Product Inventory & Valuation Audit Report</p>
    </div>

    <div class="meta-bar">
        <div>
            <strong>Report Date:</strong> {{ now()->format('d M Y, h:i A') }}
        </div>
        <div>
            <strong>Total Items:</strong> {{ $products->count() }} Products
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">S.No</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">Quantity (Inward)</th>
                <th class="text-right">Sold Product</th>
                <th class="text-right">Stock</th>
                <th class="text-right">Buy Rate (₹)</th>
                <th class="text-right">Sale Rate (₹)</th>
                <th class="text-right">Valuation (₹)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">
                        {{ $item->name }}
                        @if($item->code)
                            <span style="font-size: 9px; color: #64748b;">({{ $item->code }})</span>
                        @endif
                    </td>
                    <td>{{ $item->category ? $item->category->name : 'General' }}</td>
                    <td class="text-right">{{ number_format($item->inward_qty, 2) }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->sold_qty, 2) }} {{ $item->unit }}</td>
                    <td class="text-right {{ $item->current_stock > 0 ? 'in-stock' : 'out-stock' }}">
                        {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                    </td>
                    <td class="text-right">₹{{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                    <td class="text-right font-bold">₹{{ number_format($item->valuation, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #94a3b8;">No products found for this criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL INVENTORY VALUATION:</td>
                <td class="text-right">{{ number_format($products->sum('current_stock'), 2) }}</td>
                <td colspan="2"></td>
                <td class="text-right in-stock">₹{{ number_format($totalValuation, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="signature-line">
            Prepared By (Store Incharge)
        </div>
        <div class="signature-line">
            Verified By (Inventory Auditor)
        </div>
        <div class="signature-line">
            Authorized Signatory
        </div>
    </div>

</body>
</html>
