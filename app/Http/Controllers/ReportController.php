<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MilkCollection;
use App\Models\MilkSale;
use App\Models\Buyer;
use App\Models\BuyerPayment;
use App\Models\Farmer;
use App\Models\FarmerAdvance;
use App\Models\FarmerLoan;
use App\Models\FarmerDeduction;
use App\Models\FarmerInvoice;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\ProductPurchase;
use App\Models\Dealer;
use App\Models\DealerPayment;
use App\Models\CollectionCenter;
use App\Models\Customer;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Main Reports Hub Dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /* =========================================================================
     * 1. MILK SALE REPORTS (PRIMARY)
     * ========================================================================= */

    public function milkSalesDateWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $centers = CollectionCenter::all();

        $query = MilkSale::with('buyer')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('milk_type') && $request->milk_type !== 'all') {
            $query->where('milk_type', $request->milk_type);
        }

        if ($request->filled('center_id')) {
            // Filter by center if buyer belongs to center or milk sale has center
        }

        if ($request->get('export') === 'excel') {
            return $this->exportCsv('milk_sales_date_wise.csv', [
                'SrNo', 'Date', 'Shift', 'Buyer', 'Milk Type', 'Liters', 'FAT %', 'SNF %', 'CLR', 'Rate', 'Total Amount', 'Paid Amount', 'Balance'
            ], $query->get()->map(function ($s, $idx) {
                return [
                    $idx + 1,
                    $s->sale_date ? $s->sale_date->format('d/m/Y') : '',
                    ucfirst($s->shift),
                    $s->buyer->name ?? 'Walk-in',
                    ucfirst($s->milk_type),
                    $s->quantity_liters,
                    $s->fat_percentage,
                    $s->snf_percentage,
                    $s->clr_reading,
                    $s->rate_per_liter,
                    $s->total_amount,
                    $s->paid_amount,
                    $s->balance_amount,
                ];
            }));
        }

        $totalLiters = (clone $query)->sum('quantity_liters');
        $totalAmount = (clone $query)->sum('total_amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalBalance = (clone $query)->sum('balance_amount');
        $avgFat = (clone $query)->avg('fat_percentage') ?: 0;
        $avgSnf = (clone $query)->avg('snf_percentage') ?: 0;

        $sales = $query->latest('sale_date')->paginate(20)->withQueryString();

        return view('reports.milk_sales.date_wise', compact(
            'sales', 'centers', 'startDate', 'endDate',
            'totalLiters', 'totalAmount', 'totalPaid', 'totalBalance', 'avgFat', 'avgSnf'
        ));
    }

    public function milkSalesCustomerWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $buyers = Buyer::orderBy('name')->get();

        $query = MilkSale::with('buyer')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }

        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where('shift', $request->shift);
        }

        $allSales = $query->get();

        // Group by buyer
        $grouped = $allSales->groupBy('buyer_id');
        $customerSales = [];

        foreach ($grouped as $buyerId => $items) {
            $buyer = $items->first()->buyer;
            $liters = (float) $items->sum('quantity_liters');
            $totAmt = (float) $items->sum('total_amount');
            $paidAmt = (float) $items->sum('paid_amount');
            $balAmt = (float) $items->sum('balance_amount');

            $customerSales[] = [
                'id' => $buyerId,
                'name' => $buyer->name ?? 'Walk-in / Cash',
                'code' => $buyer->buyer_code ?? '-',
                'phone' => $buyer->phone ?? '-',
                'entries_count' => $items->count(),
                'liters' => $liters,
                'avg_rate' => $liters > 0 ? ($totAmt / $liters) : 0,
                'total_amount' => $totAmt,
                'paid_amount' => $paidAmt,
                'balance_amount' => $balAmt,
            ];
        }

        $totalLiters = (float) $allSales->sum('quantity_liters');
        $totalAmount = (float) $allSales->sum('total_amount');
        $totalPaid = (float) $allSales->sum('paid_amount');
        $totalBalance = (float) $allSales->sum('balance_amount');

        if ($request->get('export') === 'excel') {
            return $this->exportCsv('milk_sales_customer_wise.csv', [
                'Customer Code', 'Customer Name', 'Phone', 'Entries Count', 'Total Liters', 'Avg Rate', 'Total Billed', 'Total Paid', 'Balance Due'
            ], collect($customerSales)->map(function ($c) {
                return [
                    $c['code'], $c['name'], $c['phone'], $c['entries_count'], $c['liters'],
                    number_format($c['avg_rate'], 2), $c['total_amount'], $c['paid_amount'], $c['balance_amount']
                ];
            }));
        }

        return view('reports.milk_sales.customer_wise', compact(
            'customerSales', 'buyers', 'startDate', 'endDate',
            'totalLiters', 'totalAmount', 'totalPaid', 'totalBalance'
        ));
    }

    public function milkSalesSaleBillCustomerWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $buyers = Buyer::orderBy('name')->get();

        $query = MilkSale::with('buyer')
            ->whereBetween('sale_date', [$startDate, $endDate]);

        if ($request->filled('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }

        if ($request->get('export') === 'excel') {
            return $this->exportCsv('customer_sale_bills.csv', [
                'Bill No', 'Date', 'Customer', 'Shift', 'Milk Type', 'Liters', 'Rate', 'Total Bill', 'Paid', 'Balance'
            ], $query->get()->map(function ($b) {
                return [
                    $b->sale_number,
                    $b->sale_date ? $b->sale_date->format('d/m/Y') : '',
                    $b->buyer->name ?? 'Walk-in',
                    ucfirst($b->shift),
                    ucfirst($b->milk_type),
                    $b->quantity_liters,
                    $b->rate_per_liter,
                    $b->total_amount,
                    $b->paid_amount,
                    $b->balance_amount,
                ];
            }));
        }

        $bills = $query->latest('sale_date')->paginate(20)->withQueryString();

        return view('reports.milk_sales.sale_bill_customer_wise', compact('bills', 'buyers', 'startDate', 'endDate'));
    }

    public function milkSalesCustomerKhata(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $buyers = Buyer::orderBy('name')->get();

        $selectedBuyer = null;
        $transactions = [];

        if ($request->filled('buyer_id')) {
            $selectedBuyer = Buyer::find($request->buyer_id);

            if ($selectedBuyer) {
                // Fetch sales (debits)
                $sales = MilkSale::where('buyer_id', $selectedBuyer->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->get();

                // Fetch payments (credits)
                $payments = BuyerPayment::where('buyer_id', $selectedBuyer->id)
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->get();

                $all = collect();

                foreach ($sales as $s) {
                    $all->push([
                        'date' => $s->sale_date ? $s->sale_date->format('d/m/Y') : '',
                        'raw_date' => $s->sale_date ? $s->sale_date->format('Y-m-d') : '',
                        'ref' => $s->sale_number,
                        'description' => "Milk Sale (" . ucfirst($s->shift) . " " . ucfirst($s->milk_type) . " " . number_format($s->quantity_liters, 2) . "L @ ₹" . number_format($s->rate_per_liter, 2) . ")",
                        'debit' => (float) $s->total_amount,
                        'credit' => (float) $s->paid_amount,
                    ]);
                }

                foreach ($payments as $p) {
                    $all->push([
                        'date' => $p->payment_date ? Carbon::parse($p->payment_date)->format('d/m/Y') : '',
                        'raw_date' => $p->payment_date ? Carbon::parse($p->payment_date)->format('Y-m-d') : '',
                        'ref' => 'PAY-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                        'description' => "Payment Received (" . ucfirst($p->payment_mode ?? 'Cash') . ") - " . ($p->notes ?? ''),
                        'debit' => 0.0,
                        'credit' => (float) $p->amount,
                    ]);
                }

                $running = 0.0;
                $sorted = $all->sortBy('raw_date');

                foreach ($sorted as $item) {
                    $running += ($item['debit'] - $item['credit']);
                    $item['balance'] = $running;
                    $transactions[] = $item;
                }
            }
        }

        if ($request->get('export') === 'excel' && $selectedBuyer) {
            return $this->exportCsv("khata_{$selectedBuyer->buyer_code}.csv", [
                'Date', 'Ref / Voucher', 'Description', 'Debit (Billed)', 'Credit (Paid)', 'Running Balance'
            ], collect($transactions)->map(function ($t) {
                return [$t['date'], $t['ref'], $t['description'], $t['debit'], $t['credit'], $t['balance']];
            }));
        }

        return view('reports.milk_sales.customer_khata', compact(
            'buyers', 'selectedBuyer', 'transactions', 'startDate', 'endDate'
        ));
    }

    /* =========================================================================
     * 2. COLLECTION REPORTS
     * ========================================================================= */

    public function collectionsDateWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $centers = CollectionCenter::all();

        $query = MilkCollection::with(['farmer', 'collectionCenter'])
            ->whereBetween('collection_date', [$startDate, $endDate]);

        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('milk_type') && $request->milk_type !== 'all') {
            $query->where('milk_type', $request->milk_type);
        }

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        $paginator = $query->latest('collection_date')->paginate(20)->withQueryString();

        $rows = collect($paginator->items())->map(function ($c, $idx) use ($paginator) {
            return [
                'sr_no' => $paginator->firstItem() + $idx,
                'date' => $c->collection_date ? Carbon::parse($c->collection_date)->format('d/m/Y') : '-',
                'shift' => ucfirst($c->shift),
                'farmer' => '<b>' . ($c->farmer->name ?? 'N/A') . '</b><br><span class="text-[10px] text-slate-400 font-mono">' . ($c->farmer->farmer_code ?? '') . '</span>',
                'milk_type' => ucfirst($c->milk_type),
                'liters' => number_format($c->quantity_liters, 2),
                'fat' => number_format($c->fat, 2) . '%',
                'snf' => number_format($c->snf, 2) . '%',
                'clr' => number_format($c->clr, 1),
                'rate' => '₹ ' . number_format($c->rate, 2),
                'amount' => '<b class="text-emerald-700">₹ ' . number_format($c->net_amount, 2) . '</b>',
            ];
        });

        $columns = [
            ['key' => 'sr_no', 'label' => 'SrNo', 'align' => 'center'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'shift', 'label' => 'Shift'],
            ['key' => 'farmer', 'label' => 'Farmer Name & Code'],
            ['key' => 'milk_type', 'label' => 'Milk Type'],
            ['key' => 'liters', 'label' => 'Qty (Ltr)', 'align' => 'right'],
            ['key' => 'fat', 'label' => 'FAT', 'align' => 'right'],
            ['key' => 'snf', 'label' => 'SNF', 'align' => 'right'],
            ['key' => 'clr', 'label' => 'CLR', 'align' => 'right'],
            ['key' => 'rate', 'label' => 'Rate', 'align' => 'right'],
            ['key' => 'amount', 'label' => 'Net Amount', 'align' => 'right'],
        ];

        $summaryCards = [
            ['label' => 'Total Liters Procured', 'value' => number_format((clone $query)->sum('quantity_liters'), 2) . ' Ltr', 'color' => 'text-slate-900'],
            ['label' => 'Average FAT / SNF', 'value' => number_format((clone $query)->avg('fat') ?: 0, 2) . '% / ' . number_format((clone $query)->avg('snf') ?: 0, 2) . '%', 'color' => 'text-slate-900'],
            ['label' => 'Total Farmer Payout', 'value' => '₹ ' . number_format((clone $query)->sum('net_amount'), 2), 'color' => 'text-emerald-700'],
            ['label' => 'Total Collection Records', 'value' => (clone $query)->count(), 'color' => 'text-blue-700'],
        ];

        return view('reports.generic_report', [
            'title' => 'Date Wise Collection Report',
            'category' => 'Collection Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hasCenterFilter' => true,
            'hasShiftFilter' => true,
            'hasMilkTypeFilter' => true,
            'centers' => $centers,
            'farmers' => Farmer::orderBy('name')->get(),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $paginator,
            'summaryCards' => $summaryCards,
        ]);
    }

    public function collectionsFarmerWise(Request $request)
    {
        return $this->collectionsDateWise($request);
    }

    public function collectionsFarmerSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(10)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $query = MilkCollection::with('farmer')
            ->whereBetween('collection_date', [$startDate, $endDate]);

        $grouped = $query->get()->groupBy('farmer_id');
        $rows = [];

        foreach ($grouped as $farmerId => $items) {
            $farmer = $items->first()->farmer;
            $liters = $items->sum('quantity_liters');
            $amt = $items->sum('net_amount');
            $avgFat = $items->avg('fat') ?: 0;
            $avgSnf = $items->avg('snf') ?: 0;

            $rows[] = [
                'code' => $farmer->farmer_code ?? '-',
                'name' => '<b>' . ($farmer->name ?? 'N/A') . '</b>',
                'phone' => $farmer->phone ?? '-',
                'shifts_count' => $items->count(),
                'liters' => number_format($liters, 2),
                'fat' => number_format($avgFat, 2) . '%',
                'snf' => number_format($avgSnf, 2) . '%',
                'amount' => '<b class="text-emerald-700">₹ ' . number_format($amt, 2) . '</b>',
            ];
        }

        $columns = [
            ['key' => 'code', 'label' => 'Farmer Code'],
            ['key' => 'name', 'label' => 'Farmer Name'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'shifts_count', 'label' => 'Shifts', 'align' => 'right'],
            ['key' => 'liters', 'label' => 'Total Milk (Ltr)', 'align' => 'right'],
            ['key' => 'fat', 'label' => 'Avg FAT', 'align' => 'right'],
            ['key' => 'snf', 'label' => 'Avg SNF', 'align' => 'right'],
            ['key' => 'amount', 'label' => 'Total Payout', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Farmer Wise Summary Report',
            'category' => 'Collection Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hasCenterFilter' => true,
            'hasShiftFilter' => false,
            'hasMilkTypeFilter' => false,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => null,
            'summaryCards' => [
                ['label' => 'Farmers Active', 'value' => count($rows), 'color' => 'text-slate-900'],
                ['label' => 'Total Milk Procured', 'value' => number_format(collect($rows)->sum('liters'), 2) . ' Ltr', 'color' => 'text-slate-900'],
            ],
        ]);
    }

    public function collectionsCenterSummary(Request $request)
    {
        return $this->collectionsFarmerSummary($request);
    }

    public function collectionsWindow(Request $request)
    {
        return $this->collectionsDateWise($request);
    }

    /* =========================================================================
     * 3. BILL REPORTS
     * ========================================================================= */

    public function billsDateWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $invoices = FarmerInvoice::with('farmer')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->latest()
            ->paginate(20);

        $rows = collect($invoices->items())->map(function ($inv) {
            return [
                'invoice_no' => $inv->invoice_number,
                'farmer' => ($inv->farmer->farmer_code ?? '') . ' - ' . ($inv->farmer->name ?? ''),
                'period' => Carbon::parse($inv->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($inv->end_date)->format('d/m/Y'),
                'milk_total' => '₹ ' . number_format($inv->milk_amount, 2),
                'deductions' => '₹ ' . number_format($inv->deductions_amount, 2),
                'net_payable' => '<b class="text-emerald-700">₹ ' . number_format($inv->net_payable, 2) . '</b>',
                'status' => ucfirst($inv->status),
            ];
        });

        $columns = [
            ['key' => 'invoice_no', 'label' => 'Bill No'],
            ['key' => 'farmer', 'label' => 'Farmer'],
            ['key' => 'period', 'label' => 'Billing Period'],
            ['key' => 'milk_total', 'label' => 'Milk Total', 'align' => 'right'],
            ['key' => 'deductions', 'label' => 'Deductions', 'align' => 'right'],
            ['key' => 'net_payable', 'label' => 'Net Payable', 'align' => 'right'],
            ['key' => 'status', 'label' => 'Status'],
        ];

        return view('reports.generic_report', [
            'title' => 'Date Wise Bill Report',
            'category' => 'Bill Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $invoices,
        ]);
    }

    public function billsFarmerWise(Request $request)
    {
        return $this->billsDateWise($request);
    }

    public function billsFarmerBills(Request $request)
    {
        return $this->billsDateWise($request);
    }

    public function billsPaymentRegister(Request $request)
    {
        return $this->billsDateWise($request);
    }

    public function billsBankFile(Request $request)
    {
        return $this->billsDateWise($request);
    }

    /* =========================================================================
     * 4. CATTLE FEED SALE & PURCHASE REPORTS
     * ========================================================================= */

    public function feedSalesDateWise(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $sales = ProductSale::with(['farmer', 'product'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->latest()
            ->paginate(20);

        $rows = collect($sales->items())->map(function ($s) {
            return [
                'bill_no' => $s->invoice_no ?? ('FS-' . $s->id),
                'date' => $s->sale_date ? Carbon::parse($s->sale_date)->format('d/m/Y') : '-',
                'farmer' => $s->farmer ? ($s->farmer->farmer_code . ' - ' . $s->farmer->name) : 'Retail Customer',
                'product' => $s->product->name ?? 'Cattle Feed',
                'quantity' => $s->quantity . ' ' . ($s->product->unit ?? 'Kg'),
                'rate' => '₹ ' . number_format($s->rate, 2),
                'total' => '<b class="text-emerald-700">₹ ' . number_format($s->total_amount, 2) . '</b>',
                'payment_mode' => ucfirst($s->payment_mode ?? 'Deduction'),
            ];
        });

        $columns = [
            ['key' => 'bill_no', 'label' => 'Bill No'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'farmer', 'label' => 'Farmer / Customer'],
            ['key' => 'product', 'label' => 'Feed / Product'],
            ['key' => 'quantity', 'label' => 'Quantity', 'align' => 'right'],
            ['key' => 'rate', 'label' => 'Rate', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total Amount', 'align' => 'right'],
            ['key' => 'payment_mode', 'label' => 'Mode'],
        ];

        return view('reports.generic_report', [
            'title' => 'Cattle Feed Sale Date Wise',
            'category' => 'Cattle Feed Sale Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $sales,
        ]);
    }

    public function feedSalesFarmerWise(Request $request)
    {
        return $this->feedSalesDateWise($request);
    }

    public function feedSalesSummary(Request $request)
    {
        return $this->feedSalesDateWise($request);
    }

    public function feedSalesRegister(Request $request)
    {
        return $this->feedSalesDateWise($request);
    }

    public function feedStockSummary(Request $request)
    {
        $products = Product::where('status', 'active')->get();

        $rows = $products->map(function ($p) {
            return [
                'name' => '<b>' . $p->name . '</b>',
                'category' => $p->category->name ?? 'General',
                'unit' => $p->unit ?? 'Bag',
                'stock' => '<b class="text-slate-900">' . number_format($p->current_stock ?? 0, 2) . '</b>',
                'purchase_rate' => '₹ ' . number_format($p->purchase_price ?? 0, 2),
                'sale_rate' => '₹ ' . number_format($p->selling_price ?? 0, 2),
                'stock_value' => '<b class="text-emerald-700">₹ ' . number_format(($p->current_stock ?? 0) * ($p->purchase_price ?? 0), 2) . '</b>',
            ];
        });

        $columns = [
            ['key' => 'name', 'label' => 'Product / Feed'],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'unit', 'label' => 'Unit'],
            ['key' => 'stock', 'label' => 'In Stock', 'align' => 'right'],
            ['key' => 'purchase_rate', 'label' => 'Purchase Price', 'align' => 'right'],
            ['key' => 'sale_rate', 'label' => 'Selling Price', 'align' => 'right'],
            ['key' => 'stock_value', 'label' => 'Stock Valuation', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Cattle Feed Stock Summary',
            'category' => 'Cattle Feed Purchase Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => null,
        ]);
    }

    public function feedStockPurchase(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $purchases = ProductPurchase::with(['dealer', 'product'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->latest()
            ->paginate(20);

        $rows = collect($purchases->items())->map(function ($p) {
            return [
                'bill_no' => $p->invoice_number ?? ('PUR-' . $p->id),
                'date' => $p->purchase_date ? Carbon::parse($p->purchase_date)->format('d/m/Y') : '-',
                'dealer' => $p->dealer->name ?? 'Wholesale Dealer',
                'product' => $p->product->name ?? 'Cattle Feed',
                'quantity' => $p->quantity . ' ' . ($p->product->unit ?? 'Bag'),
                'rate' => '₹ ' . number_format($p->rate, 2),
                'total' => '<b class="text-emerald-700">₹ ' . number_format($p->total_amount, 2) . '</b>',
                'paid' => '₹ ' . number_format($p->paid_amount ?? 0, 2),
            ];
        });

        $columns = [
            ['key' => 'bill_no', 'label' => 'Invoice #'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'dealer', 'label' => 'Dealer / Vendor'],
            ['key' => 'product', 'label' => 'Product'],
            ['key' => 'quantity', 'label' => 'Qty', 'align' => 'right'],
            ['key' => 'rate', 'label' => 'Rate', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Paid', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Food Stock Purchase Report',
            'category' => 'Cattle Feed Purchase Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $purchases,
        ]);
    }

    public function feedDealerSummary(Request $request)
    {
        $dealers = Dealer::with('payments')->get();

        $rows = $dealers->map(function ($d) {
            return [
                'name' => '<b>' . $d->name . '</b>',
                'phone' => $d->phone ?? '-',
                'company' => $d->company_name ?? '-',
                'total_purchases' => '₹ ' . number_format($d->total_purchases ?? 0, 2),
                'total_paid' => '₹ ' . number_format($d->total_paid ?? 0, 2),
                'balance' => '<b class="text-rose-600">₹ ' . number_format($d->current_balance ?? 0, 2) . '</b>',
            ];
        });

        $columns = [
            ['key' => 'name', 'label' => 'Dealer Name'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'company', 'label' => 'Company'],
            ['key' => 'total_purchases', 'label' => 'Total Purchases', 'align' => 'right'],
            ['key' => 'total_paid', 'label' => 'Total Paid', 'align' => 'right'],
            ['key' => 'balance', 'label' => 'Payable Balance', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Dealer Payment Summary',
            'category' => 'Cattle Feed Purchase Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => null,
        ]);
    }

    public function feedProfitLoss(Request $request)
    {
        return $this->feedStockSummary($request);
    }

    /* =========================================================================
     * 5. ADVANCE & LOAN REPORTS
     * ========================================================================= */

    public function advancesFarmerWise(Request $request)
    {
        $farmers = Farmer::with('advances')->has('advances')->paginate(20);

        $rows = collect($farmers->items())->map(function ($f) {
            $totalAdv = $f->advances->sum('amount');
            $totalPaid = $f->advances->sum('deducted_amount');
            $bal = max(0, $totalAdv - $totalPaid);

            return [
                'code' => $f->farmer_code,
                'name' => '<b>' . $f->name . '</b>',
                'phone' => $f->phone ?: '-',
                'total_advance' => '₹ ' . number_format($totalAdv, 2),
                'total_paid' => '₹ ' . number_format($totalPaid, 2),
                'balance' => '<b class="text-rose-600">₹ ' . number_format($bal, 2) . '</b>',
            ];
        });

        $columns = [
            ['key' => 'code', 'label' => 'Farmer Code'],
            ['key' => 'name', 'label' => 'Farmer Name'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'total_advance', 'label' => 'Total Advance', 'align' => 'right'],
            ['key' => 'total_paid', 'label' => 'Total Recovered', 'align' => 'right'],
            ['key' => 'balance', 'label' => 'Outstanding Balance', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Farmer Wise Advance Report',
            'category' => 'Advance Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $farmers,
        ]);
    }

    public function advancesFarmerAdvanceWise(Request $request)
    {
        $advances = FarmerAdvance::with('farmer')->latest('advance_date')->paginate(20);

        $rows = collect($advances->items())->map(function ($adv) {
            return [
                'voucher' => $adv->voucher_no ?: str_pad($adv->id, 3, '0', STR_PAD_LEFT),
                'date' => $adv->advance_date ? Carbon::parse($adv->advance_date)->format('d/m/Y') : '-',
                'farmer' => ($adv->farmer->farmer_code ?? '') . ' - ' . ($adv->farmer->name ?? ''),
                'amount' => '₹ ' . number_format($adv->amount, 2),
                'paid' => '₹ ' . number_format($adv->total_paid ?? $adv->deducted_amount, 2),
                'balance' => '<b class="text-rose-600">₹ ' . number_format($adv->total_balance, 2) . '</b>',
                'mode' => ucfirst($adv->payment_mode ?? 'Cash'),
                'notes' => $adv->notes ?: '-',
            ];
        });

        $columns = [
            ['key' => 'voucher', 'label' => 'Voucher #'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'farmer', 'label' => 'Farmer'],
            ['key' => 'amount', 'label' => 'Advance Amount', 'align' => 'right'],
            ['key' => 'paid', 'label' => 'Recovered', 'align' => 'right'],
            ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
            ['key' => 'mode', 'label' => 'Mode'],
            ['key' => 'notes', 'label' => 'Notes'],
        ];

        return view('reports.generic_report', [
            'title' => 'Farmer Advance Wise Detailed Report',
            'category' => 'Advance Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $advances,
        ]);
    }

    public function advancesLoanReport(Request $request)
    {
        return $this->advancesFarmerAdvanceWise($request);
    }

    public function advancesCenterLoanSummary(Request $request)
    {
        return $this->advancesFarmerWise($request);
    }

    public function advancesFarmerLoanSummary(Request $request)
    {
        return $this->advancesFarmerWise($request);
    }

    /* =========================================================================
     * 6. MASTER REPORTS
     * ========================================================================= */

    public function masterFarmerList(Request $request)
    {
        $farmers = Farmer::orderBy('farmer_code')->paginate(25);

        $rows = collect($farmers->items())->map(function ($f) {
            return [
                'code' => $f->farmer_code,
                'name' => '<b>' . $f->name . '</b>',
                'name_hi' => $f->name_hi ?: '-',
                'phone' => $f->phone ?: '-',
                'animal' => ucfirst($f->animal_type ?? 'Cow'),
                'village' => $f->village ?: '-',
                'balance' => '<b class="' . ($f->current_balance < 0 ? 'text-rose-600' : 'text-emerald-700') . '">₹ ' . number_format($f->current_balance, 2) . '</b>',
                'status' => ucfirst($f->status ?? 'Active'),
            ];
        });

        $columns = [
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'name', 'label' => 'Name (English)'],
            ['key' => 'name_hi', 'label' => 'Name (Hindi)'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'animal', 'label' => 'Default Cattle'],
            ['key' => 'village', 'label' => 'Village'],
            ['key' => 'balance', 'label' => 'Ledger Balance', 'align' => 'right'],
            ['key' => 'status', 'label' => 'Status'],
        ];

        return view('reports.generic_report', [
            'title' => 'Master Farmer List',
            'category' => 'Master Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $farmers,
        ]);
    }

    public function masterMilkSaleFarmerList(Request $request)
    {
        $buyers = Buyer::orderBy('name')->paginate(25);

        $rows = collect($buyers->items())->map(function ($b) {
            return [
                'code' => $b->buyer_code,
                'name' => '<b>' . $b->name . '</b>',
                'phone' => $b->phone ?: '-',
                'address' => $b->address ?: '-',
                'cow_rate' => '₹ ' . number_format($b->cow_fixed_rate ?? 0, 2),
                'buffalo_rate' => '₹ ' . number_format($b->buffalo_fixed_rate ?? 0, 2),
                'balance' => '<b class="' . ($b->current_balance > 0 ? 'text-rose-600' : 'text-emerald-700') . '">₹ ' . number_format($b->current_balance, 2) . '</b>',
            ];
        });

        $columns = [
            ['key' => 'code', 'label' => 'Buyer Code'],
            ['key' => 'name', 'label' => 'Buyer / Customer Name'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'address', 'label' => 'Address'],
            ['key' => 'cow_rate', 'label' => 'Cow Rate', 'align' => 'right'],
            ['key' => 'buffalo_rate', 'label' => 'Buff Rate', 'align' => 'right'],
            ['key' => 'balance', 'label' => 'Balance Due', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Milk Sale Buyer / Customer Master List',
            'category' => 'Master Reports',
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $buyers,
        ]);
    }

    public function masterFoodDealerList(Request $request)
    {
        return $this->feedDealerSummary($request);
    }

    /* =========================================================================
     * 7. OTHER REPORTS
     * ========================================================================= */

    public function otherPassbook(Request $request)
    {
        return $this->advancesFarmerWise($request);
    }

    public function otherMilkSalePurchase(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $procuredLiters = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('quantity_liters');
        $procuredAmt = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('net_amount');

        $soldLiters = MilkSale::whereBetween('sale_date', [$startDate, $endDate])->sum('quantity_liters');
        $soldAmt = MilkSale::whereBetween('sale_date', [$startDate, $endDate])->sum('total_amount');

        $rows = [
            [
                'metric' => '<b>Milk Procurement (From Farmers)</b>',
                'volume' => number_format($procuredLiters, 2) . ' Liters',
                'avg_rate' => $procuredLiters > 0 ? ('₹ ' . number_format($procuredAmt / $procuredLiters, 2)) : '-',
                'amount' => '<b class="text-rose-600">₹ ' . number_format($procuredAmt, 2) . '</b>',
            ],
            [
                'metric' => '<b>Milk Sales (To Commercial Buyers)</b>',
                'volume' => number_format($soldLiters, 2) . ' Liters',
                'avg_rate' => $soldLiters > 0 ? ('₹ ' . number_format($soldAmt / $soldLiters, 2)) : '-',
                'amount' => '<b class="text-emerald-700">₹ ' . number_format($soldAmt, 2) . '</b>',
            ],
            [
                'metric' => '<b>Net Difference / Margin</b>',
                'volume' => number_format($soldLiters - $procuredLiters, 2) . ' Liters',
                'avg_rate' => '-',
                'amount' => '<b class="' . (($soldAmt - $procuredAmt) >= 0 ? 'text-emerald-700' : 'text-rose-600') . '">₹ ' . number_format($soldAmt - $procuredAmt, 2) . '</b>',
            ],
        ];

        $columns = [
            ['key' => 'metric', 'label' => 'Procurement vs Sale Comparison'],
            ['key' => 'volume', 'label' => 'Total Liters', 'align' => 'right'],
            ['key' => 'avg_rate', 'label' => 'Avg Rate / Liter', 'align' => 'right'],
            ['key' => 'amount', 'label' => 'Total Amount (₹)', 'align' => 'right'],
        ];

        return view('reports.generic_report', [
            'title' => 'Milk Sale Purchase Comparative Report',
            'category' => 'Other Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => null,
        ]);
    }

    public function otherMilkProfitLoss(Request $request)
    {
        return $this->otherMilkSalePurchase($request);
    }

    public function otherDairyExpense(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->latest('expense_date')
            ->paginate(20);

        $rows = collect($expenses->items())->map(function ($e) {
            return [
                'date' => $e->expense_date ? Carbon::parse($e->expense_date)->format('d/m/Y') : '-',
                'category' => $e->category->name ?? 'General',
                'title' => '<b>' . $e->title . '</b>',
                'amount' => '<b class="text-rose-600">₹ ' . number_format($e->amount, 2) . '</b>',
                'mode' => ucfirst($e->payment_mode ?? 'Cash'),
                'notes' => $e->description ?: '-',
            ];
        });

        $columns = [
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'title', 'label' => 'Expense Description'],
            ['key' => 'amount', 'label' => 'Amount', 'align' => 'right'],
            ['key' => 'mode', 'label' => 'Payment Mode'],
            ['key' => 'notes', 'label' => 'Notes'],
        ];

        return view('reports.generic_report', [
            'title' => 'Dairy Expenses Register',
            'category' => 'Other Reports',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'columns' => $columns,
            'rows' => $rows,
            'paginator' => $expenses,
        ]);
    }

    public function otherIncentiveCalculator(Request $request)
    {
        return $this->collectionsFarmerSummary($request);
    }

    /* =========================================================================
     * CSV Export Helper
     * ========================================================================= */
    protected function exportCsv(string $filename, array $headers, $data)
    {
        $callback = function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, is_array($row) ? $row : (array) $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
