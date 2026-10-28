<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Branch;
use App\Models\CollectionCenter;
use App\Models\RateChart;
use App\Models\FarmerAdvance;
use App\Models\FarmerLedger;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerController extends Controller
{
    /**
     * Farmers Listing & In-Page Modal Management
     */
    public function index(Request $request)
    {
        $query = Farmer::with(['collectionCenter', 'rateChart', 'route']);

        // Search Filter (English name, Hindi name, code, phone, village, vehicle)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_hi', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%");
            });
        }

        // Animal / Milk Classification Filter
        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }

        // Assigned Route Filter
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Assigned Vehicle Filter
        if ($request->filled('vehicle')) {
            $query->where('vehicle', 'like', "%{$request->vehicle}%");
        }

        // Center Filter
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Balance Filter
        if ($request->filled('balance_status')) {
            if ($request->balance_status === 'due') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->balance_status === 'advance') {
                $query->where('current_balance', '<', 0);
            } elseif ($request->balance_status === 'zero') {
                $query->where('current_balance', '=', 0);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'code_asc':
                $query->orderBy('farmer_code', 'asc');
                break;
            case 'code_desc':
                $query->orderBy('farmer_code', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'balance_desc':
                $query->orderBy('current_balance', 'desc');
                break;
            case 'balance_asc':
                $query->orderBy('current_balance', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $farmers = $query->paginate(15)->withQueryString();

        $totalFarmers = Farmer::count();
        $activeFarmers = Farmer::where('status', 'active')->count();
        $totalPayable = Farmer::where('current_balance', '>', 0)->sum('current_balance');

        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();

        $nextNum = Farmer::max('id') + 1;
        $nextCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $farmerWiseAdditions = \App\Services\CenterAdditionDeductionService::getFarmerWiseAdditions();
        $farmerWiseDeductions = \App\Services\CenterAdditionDeductionService::getFarmerWiseDeductions();
        $allFarmersAdditions = \App\Services\CenterAdditionDeductionService::getAllFarmersAdditions();
        $allFarmersDeductions = \App\Services\CenterAdditionDeductionService::getAllFarmersDeductions();

        return view('farmers.index', compact(
            'farmers',
            'totalFarmers',
            'activeFarmers',
            'totalPayable',
            'branches',
            'centers',
            'rateCharts',
            'routes',
            'nextCode',
            'farmerWiseAdditions',
            'farmerWiseDeductions',
            'allFarmersAdditions',
            'allFarmersDeductions'
        ));
    }

    /**
     * Fallback standalone create page
     */
    public function create()
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();
        $nextNum = Farmer::max('id') + 1;
        $nextCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $farmerWiseAdditions = \App\Services\CenterAdditionDeductionService::getFarmerWiseAdditions();
        $farmerWiseDeductions = \App\Services\CenterAdditionDeductionService::getFarmerWiseDeductions();
        $allFarmersAdditions = \App\Services\CenterAdditionDeductionService::getAllFarmersAdditions();
        $allFarmersDeductions = \App\Services\CenterAdditionDeductionService::getAllFarmersDeductions();

        return view('farmers.create', compact(
            'branches',
            'centers',
            'rateCharts',
            'routes',
            'nextCode',
            'farmerWiseAdditions',
            'farmerWiseDeductions',
            'allFarmersAdditions',
            'allFarmersDeductions'
        ));
    }

    /**
     * Store new farmer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code',
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vehicle' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'pricing_type' => 'nullable|in:rate_chart,fixed_rate',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'cow_milk_rate' => 'nullable|numeric|min:0',
            'buffalo_milk_rate' => 'nullable|numeric|min:0',
            'branch_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'confirm_account_number' => 'nullable|same:account_number',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'anamat' => 'nullable|numeric|min:0',
            'building_fund' => 'nullable|numeric|min:0',
            'installment' => 'nullable|numeric|min:0',
            'etc_amount' => 'nullable|numeric|min:0',
            'grant_amount' => 'nullable|numeric|min:0',
            'extra_rates' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['pricing_type'] = $validated['pricing_type'] ?? 'rate_chart';

        if ($validated['pricing_type'] === 'fixed_rate') {
            $validated['rate_chart_id'] = null;
        } elseif ($validated['pricing_type'] === 'rate_chart') {
            if (!empty($validated['rate_chart_id'])) {
                $rc = RateChart::find($validated['rate_chart_id']);
                if (!$rc || $rc->status !== 'active') {
                    return back()->withInput()->withErrors(['rate_chart_id' => 'The selected Rate Chart is inactive or invalid. Please select an active Rate Chart.']);
                }
            }
        }

        // Process dynamic farmer-wise additions & deductions
        $extraRates = [];
        if ($request->has('extra_rates') && is_array($request->input('extra_rates'))) {
            foreach ($request->input('extra_rates') as $k => $v) {
                $cleanK = trim($k);
                if ($cleanK !== '') {
                    $extraRates[$cleanK] = ($v !== '' && $v !== null) ? (float)$v : 0.0;
                }
            }
        }
        $validated['extra_rates'] = $extraRates;

        // Two-way sync with legacy columns if present
        $legacySync = [
            'anamat' => ['Anamat'],
            'building_fund' => ['Building fund', 'Building Fund'],
            'grant_amount' => ['Grant', 'Grant Amount'],
            'installment' => ['Installment'],
            'etc_amount' => ['Etc', 'Etc Amount'],
        ];
        foreach ($legacySync as $col => $catNames) {
            foreach ($catNames as $catName) {
                if (isset($extraRates[$catName])) {
                    $validated[$col] = $extraRates[$catName];
                    break;
                }
            }
        }

        // Photo file handling
        if ($request->hasFile('photo')) {
            $destPath = public_path('uploads/farmers');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'farmer_' . time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move($destPath, $filename);
            $validated['photo'] = 'uploads/farmers/' . $filename;
        }

        unset($validated['confirm_account_number']);

        // Safe defensive column filtering to avoid breaking if table migration is pending
        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer = Farmer::create($saveData);
        AuditLog::log('Created Farmer', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code, 'name' => $farmer->name]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$farmer->name} ({$farmer->farmer_code}) registered successfully!");
    }

    /**
     * View farmer passbook
     */
    public function show(Request $request, Farmer $farmer)
    {
        $farmer->load(['collectionCenter', 'rateChart', 'route', 'advances', 'settlements']);
        $collections = MilkCollection::where('farmer_id', $farmer->id)->latest()->take(20)->get();
        $ledgers = FarmerLedger::where('farmer_id', $farmer->id)->latest()->take(30)->get();
        $advances = FarmerAdvance::where('farmer_id', $farmer->id)->latest()->get();

        $totalMilkLiters = MilkCollection::where('farmer_id', $farmer->id)->sum('quantity_liters');
        $totalGrossEarned = MilkCollection::where('farmer_id', $farmer->id)->sum('net_amount');

        // Farmer Product Purchases (All, Paid, Unpaid)
        $farmerPurchases = collect();
        $products = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
                $farmerPurchases = \App\Models\ProductSale::with('product')
                    ->where(function ($q) use ($farmer) {
                        $q->where('farmer_id', $farmer->id);
                        if (!empty($farmer->farmer_code)) {
                            $q->orWhere('farmer_code', $farmer->farmer_code);
                        }
                    })
                    ->orderByDesc('sale_date')
                    ->orderByDesc('id')
                    ->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                $products = \App\Models\Product::orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }

        $totalPurchaseQty = (float) $farmerPurchases->sum('quantity');
        $totalPurchaseAmount = (float) $farmerPurchases->sum('total_amount');
        $totalPurchasePaid = (float) $farmerPurchases->sum('paid_amount');
        $totalPurchaseUnpaid = (float) $farmerPurchases->sum('remaining_amount');
        $paidPurchasesCount = $farmerPurchases->filter(fn($s) => $s->is_paid && (float) $s->remaining_amount <= 0)->count();
        $unpaidPurchasesCount = $farmerPurchases->filter(fn($s) => !$s->is_paid || (float) $s->remaining_amount > 0)->count();

        // Build comprehensive month-wise All Passbook Detail statement
        $passbookData = $this->buildPassbookStatement(
            $farmer,
            $request->input('passbook_from'),
            $request->input('passbook_to'),
            $request->input('passbook_month'),
            $request->input('passbook_type')
        );

        $mobileDairyData = [];
        try {
            $mobileDairyData = app(\App\Http\Controllers\MobileDairyController::class)->buildFarmerSuiteData($farmer, $request);
        } catch (\Throwable $e) {
            // Safe fallback
        }

        return view('farmers.show', array_merge(compact(
            'farmer',
            'collections',
            'ledgers',
            'advances',
            'totalMilkLiters',
            'totalGrossEarned',
            'farmerPurchases',
            'products',
            'totalPurchaseQty',
            'totalPurchaseAmount',
            'totalPurchasePaid',
            'totalPurchaseUnpaid',
            'paidPurchasesCount',
            'unpaidPurchasesCount',
            'mobileDairyData'
        ), $passbookData));
    }

    /**
     * Dedicated Print / PDF Statement page for Farmer Passbook
     */
    public function printPassbookStatement(Request $request, Farmer $farmer)
    {
        $farmer->load(['collectionCenter', 'rateChart', 'route']);
        $passbookData = $this->buildPassbookStatement(
            $farmer,
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('month'),
            $request->input('type')
        );

        return view('farmers.passbook_statement', array_merge([
            'farmer' => $farmer,
            'autoDownload' => $request->boolean('download'),
            'autoPrint' => $request->boolean('print'),
        ], $passbookData));
    }

    /**
     * Build unified, chronological, month-grouped passbook statement for a farmer.
     */
    protected function buildPassbookStatement(
        Farmer $farmer,
        ?string $fromDate = null,
        ?string $toDate = null,
        ?string $month = null,
        ?string $entryType = null
    ): array {
        $rawEntries = [];
        $seq = 0;

        // 1. Milk Collections (Credit +)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('milk_collections')) {
                $milkCols = MilkCollection::where('farmer_id', $farmer->id)
                    ->orderBy('collection_date')
                    ->orderBy('id')
                    ->get();
                foreach ($milkCols as $mc) {
                    $dt = $mc->collection_date ? $mc->collection_date->format('Y-m-d') : $mc->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'collection',
                        'badge' => 'Milk Supply',
                        'details' => "Milk Supply (" . ucfirst($mc->shift) . ") — {$mc->quantity_liters} Ltr (" . ucfirst($mc->milk_type) . ", FAT {$mc->fat}% / SNF {$mc->snf}%) @ ₹" . number_format((float) $mc->applied_rate, 2) . " [{$mc->receipt_number}]",
                        'debit' => 0.0,
                        'credit' => round((float) $mc->net_amount, 2),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 2. Product Purchases (Debit - for purchase bill, Credit + if paid)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
                $sales = \App\Models\ProductSale::where(function ($q) use ($farmer) {
                        $q->where('farmer_id', $farmer->id);
                        if (!empty($farmer->farmer_code)) {
                            $q->orWhere('farmer_code', $farmer->farmer_code);
                        }
                    })
                    ->orderBy('sale_date')
                    ->orderBy('id')
                    ->get();
                foreach ($sales as $s) {
                    $dt = $s->sale_date ? $s->sale_date->format('Y-m-d') : $s->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'purchase',
                        'badge' => 'Purchase',
                        'details' => "Product Purchase #{$s->sale_number} — {$s->product_name} ({$s->quantity} {$s->unit} @ ₹" . number_format((float) $s->rate, 2) . ")" . ($s->remarks ? " • {$s->remarks}" : ""),
                        'debit' => round((float) $s->total_amount, 2),
                        'credit' => 0.0,
                    ];

                    if ((float) $s->paid_amount > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'type' => 'purchase',
                            'badge' => 'Purchase Paid',
                            'details' => "Payment Received against Purchase #{$s->sale_number} ({$s->product_name}) via " . ($s->payment_mode ?: 'Cash'),
                            'debit' => 0.0,
                            'credit' => round((float) $s->paid_amount, 2),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Farmer Advances & Repayments
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_advances')) {
                $advs = FarmerAdvance::where('farmer_id', $farmer->id)
                    ->orderBy('advance_date')
                    ->orderBy('id')
                    ->get();
                foreach ($advs as $adv) {
                    $dt = $adv->advance_date ? $adv->advance_date->format('Y-m-d') : $adv->created_at->format('Y-m-d');
                    $voucherStr = !empty($adv->voucher_no) ? " (Voucher #{$adv->voucher_no})" : "";
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'advance',
                        'badge' => 'Advance',
                        'details' => "Advance / Loan Issued{$voucherStr} — " . ($adv->purpose ?: ($adv->notes ?: 'Cash Advance')),
                        'debit' => round((float) $adv->amount, 2),
                        'credit' => 0.0,
                    ];

                    // Repayments if any
                    $repaidAmount = (float) ($adv->paid_amount > 0 ? $adv->paid_amount : $adv->deducted_amount);
                    if ($repaidAmount > 0) {
                        $repDt = $adv->paid_date ? $adv->paid_date->format('Y-m-d') : $dt;
                        $rawEntries[] = [
                            'date' => $repDt,
                            'seq' => ++$seq,
                            'type' => 'advance',
                            'badge' => 'Advance Recovery',
                            'details' => "Advance Recovery / Repayment{$voucherStr}",
                            'debit' => 0.0,
                            'credit' => round($repaidAmount, 2),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 4. Farmer Deductions
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
                $deds = \App\Models\FarmerDeduction::where('farmer_id', $farmer->id)
                    ->orderBy('entry_date')
                    ->orderBy('id')
                    ->get();
                foreach ($deds as $ded) {
                    $dt = $ded->entry_date ? $ded->entry_date->format('Y-m-d') : $ded->created_at->format('Y-m-d');
                    $isCred = strtolower($ded->transaction_type ?? '') === 'credit';
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'deduction',
                        'badge' => 'Deduction',
                        'details' => "{$ded->type_label}" . ($ded->comments ? " — {$ded->comments}" : ""),
                        'debit' => $isCred ? 0.0 : round((float) $ded->amount, 2),
                        'credit' => $isCred ? round((float) $ded->amount, 2) : 0.0,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 5. Farmer Settlements
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_settlements')) {
                $settles = \App\Models\FarmerSettlement::where('farmer_id', $farmer->id)
                    ->orderBy('period_end')
                    ->orderBy('id')
                    ->get();
                foreach ($settles as $st) {
                    $dt = $st->settled_at
                        ? $st->settled_at->format('Y-m-d')
                        : ($st->period_end ? $st->period_end->format('Y-m-d') : $st->created_at->format('Y-m-d'));
                    $amt = (float) ($st->paid_amount > 0 ? $st->paid_amount : $st->net_payable);
                    $pStart = $st->period_start ? $st->period_start->format('d/m/Y') : '';
                    $pEnd = $st->period_end ? $st->period_end->format('d/m/Y') : '';
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'settlement',
                        'badge' => 'Settlement',
                        'details' => "Settlement Payout #{$st->settlement_number}" . ($pStart && $pEnd ? " ({$pStart} se {$pEnd} ke hafte me jma)" : "") . " via " . ucfirst($st->payment_mode ?? 'Cash/Bank'),
                        'debit' => round($amt, 2),
                        'credit' => 0.0,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 6. Standalone FarmerLedger entries (not already covered by reference_type)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_ledgers')) {
                $manualLedgers = FarmerLedger::where('farmer_id', $farmer->id)
                    ->where(function ($q) {
                        $q->whereNull('reference_type')
                          ->orWhereNotIn('reference_type', ['milk_collection', 'advance', 'settlement', 'product_sale', 'deduction']);
                    })
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->get();
                foreach ($manualLedgers as $ml) {
                    $dt = $ml->transaction_date ? $ml->transaction_date->format('Y-m-d') : $ml->created_at->format('Y-m-d');
                    $isCred = $ml->type === 'credit';
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'type' => 'ledger',
                        'badge' => 'Ledger Entry',
                        'details' => $ml->description ?: 'Passbook Adjustment',
                        'debit' => $isCred ? 0.0 : round((float) $ml->amount, 2),
                        'credit' => $isCred ? round((float) $ml->amount, 2) : 0.0,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // Sort chronologically by date ASC, seq ASC
        usort($rawEntries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['seq'] <=> $b['seq'];
            }
            return strcmp($a['date'], $b['date']);
        });

        // Collect available months for filter dropdown
        $availableMonths = [];
        foreach ($rawEntries as $re) {
            $ym = substr($re['date'], 0, 7);
            if (!isset($availableMonths[$ym])) {
                $availableMonths[$ym] = \Carbon\Carbon::parse($re['date'])->format('F Y');
            }
        }
        krsort($availableMonths);

        // Resolve effective date range from month or explicit from/to
        $effectiveFrom = $fromDate ?: null;
        $effectiveTo = $toDate ?: null;

        if (!empty($month) && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $mStart = \Carbon\Carbon::parse($month . '-01')->startOfMonth()->format('Y-m-d');
            $mEnd = \Carbon\Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d');
            if (!$effectiveFrom) $effectiveFrom = $mStart;
            if (!$effectiveTo) $effectiveTo = $mEnd;
        }

        // Calculate Opening Balance prior to $effectiveFrom and filter entries
        $openingBalance = 0.0;
        $filteredEntries = [];

        foreach ($rawEntries as $entry) {
            if ($effectiveFrom && $entry['date'] < $effectiveFrom) {
                $openingBalance = round($openingBalance + $entry['credit'] - $entry['debit'], 2);
                continue;
            }
            if ($effectiveTo && $entry['date'] > $effectiveTo) {
                continue;
            }
            if (!empty($entryType) && $entryType !== 'all' && $entry['type'] !== $entryType) {
                continue;
            }
            $filteredEntries[] = $entry;
        }

        // Compute running balance & group by month (YYYY-MM)
        $runningBalance = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $monthlyGroups = [];

        foreach ($filteredEntries as $idx => $entry) {
            $ym = substr($entry['date'], 0, 7);
            $carbonDate = \Carbon\Carbon::parse($entry['date']);

            if (!isset($monthlyGroups[$ym])) {
                $monthlyGroups[$ym] = [
                    'month_key' => $ym,
                    'month_name' => $carbonDate->format('F'),
                    'month_year' => $carbonDate->format('F Y'),
                    'first_date_full' => $carbonDate->format('d F Y'),
                    'opening_balance' => $runningBalance,
                    'entries' => [],
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                    'closing_balance' => $runningBalance,
                ];
            }

            $runningBalance = round($runningBalance + $entry['credit'] - $entry['debit'], 2);
            $totalDebit = round($totalDebit + $entry['debit'], 2);
            $totalCredit = round($totalCredit + $entry['credit'], 2);

            $entry['date_short'] = $carbonDate->format('d M');
            $entry['balance'] = $runningBalance;
            $entry['running_balance'] = $runningBalance;
            $filteredEntries[$idx] = $entry;

            $monthlyGroups[$ym]['entries'][] = $entry;
            $monthlyGroups[$ym]['total_debit'] = round($monthlyGroups[$ym]['total_debit'] + $entry['debit'], 2);
            $monthlyGroups[$ym]['total_credit'] = round($monthlyGroups[$ym]['total_credit'] + $entry['credit'], 2);
            $monthlyGroups[$ym]['closing_balance'] = $runningBalance;
        }

        $netBalance = round($openingBalance + $totalCredit - $totalDebit, 2);

        // Determine display start and end dates
        $startDateObj = $effectiveFrom
            ? \Carbon\Carbon::parse($effectiveFrom)
            : (!empty($filteredEntries) ? \Carbon\Carbon::parse($filteredEntries[0]['date']) : now()->startOfMonth());
        $endDateObj = $effectiveTo
            ? \Carbon\Carbon::parse($effectiveTo)
            : (!empty($filteredEntries) ? \Carbon\Carbon::parse($filteredEntries[count($filteredEntries) - 1]['date']) : now());

        return [
            'passbookEntries' => $filteredEntries,
            'passbookMonthlyGroups' => $monthlyGroups,
            'passbookAvailableMonths' => $availableMonths,
            'passbookOpeningBalance' => $openingBalance,
            'passbookTotalDebit' => $totalDebit,
            'passbookTotalCredit' => $totalCredit,
            'passbookNetBalance' => $netBalance,
            'passbookStartDateFormatted' => $startDateObj->format('d M Y'),
            'passbookEndDateFormatted' => $endDateObj->format('d M Y'),
            'passbookFrom' => $fromDate ?? '',
            'passbookTo' => $toDate ?? '',
            'passbookMonth' => $month ?? '',
            'passbookType' => $entryType ?? 'all',
        ];
    }

    /**
     * Fallback standalone edit page
     */
    public function edit(Farmer $farmer)
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();

        $farmerWiseAdditions = \App\Services\CenterAdditionDeductionService::getFarmerWiseAdditions();
        $farmerWiseDeductions = \App\Services\CenterAdditionDeductionService::getFarmerWiseDeductions();
        $allFarmersAdditions = \App\Services\CenterAdditionDeductionService::getAllFarmersAdditions();
        $allFarmersDeductions = \App\Services\CenterAdditionDeductionService::getAllFarmersDeductions();

        return view('farmers.edit', compact(
            'farmer',
            'branches',
            'centers',
            'rateCharts',
            'routes',
            'farmerWiseAdditions',
            'farmerWiseDeductions',
            'allFarmersAdditions',
            'allFarmersDeductions'
        ));
    }

    /**
     * Update farmer details
     */
    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code,' . $farmer->id,
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vehicle' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'pricing_type' => 'nullable|in:rate_chart,fixed_rate',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'cow_milk_rate' => 'nullable|numeric|min:0',
            'buffalo_milk_rate' => 'nullable|numeric|min:0',
            'branch_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'confirm_account_number' => 'nullable|same:account_number',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'anamat' => 'nullable|numeric|min:0',
            'building_fund' => 'nullable|numeric|min:0',
            'installment' => 'nullable|numeric|min:0',
            'etc_amount' => 'nullable|numeric|min:0',
            'grant_amount' => 'nullable|numeric|min:0',
            'extra_rates' => 'nullable|array',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $validated['pricing_type'] = $validated['pricing_type'] ?? ($farmer->pricing_type ?: 'rate_chart');

        if ($validated['pricing_type'] === 'fixed_rate') {
            $validated['rate_chart_id'] = null;
        } elseif ($validated['pricing_type'] === 'rate_chart') {
            if (!empty($validated['rate_chart_id'])) {
                $rc = RateChart::find($validated['rate_chart_id']);
                if (!$rc || $rc->status !== 'active') {
                    return back()->withInput()->withErrors(['rate_chart_id' => 'The selected Rate Chart is inactive or invalid. Please select an active Rate Chart.']);
                }
            }
        }

        // Process dynamic farmer-wise additions & deductions
        $extraRates = is_array($farmer->extra_rates) ? $farmer->extra_rates : [];
        if ($request->has('extra_rates') && is_array($request->input('extra_rates'))) {
            foreach ($request->input('extra_rates') as $k => $v) {
                $cleanK = trim($k);
                if ($cleanK !== '') {
                    $extraRates[$cleanK] = ($v !== '' && $v !== null) ? (float)$v : 0.0;
                }
            }
        }
        if ($request->filled('installment_deduction_type')) {
            $extraRates['installment_deduction_type'] = $request->input('installment_deduction_type');
        }
        if ($request->has('installment')) {
            $extraRates['Installment'] = (float) $request->input('installment', 0);
        }
        $validated['extra_rates'] = $extraRates;

        // Two-way sync with legacy columns if present
        $legacySync = [
            'anamat' => ['Anamat'],
            'building_fund' => ['Building fund', 'Building Fund'],
            'grant_amount' => ['Grant', 'Grant Amount'],
            'installment' => ['Installment'],
            'etc_amount' => ['Etc', 'Etc Amount'],
        ];
        foreach ($legacySync as $col => $catNames) {
            foreach ($catNames as $catName) {
                if (isset($extraRates[$catName])) {
                    $validated[$col] = $extraRates[$catName];
                    break;
                }
            }
        }

        if ($request->hasFile('photo')) {
            $destPath = public_path('uploads/farmers');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'farmer_' . time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move($destPath, $filename);
            $validated['photo'] = 'uploads/farmers/' . $filename;
        }

        unset($validated['confirm_account_number']);

        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer->update($saveData);
        AuditLog::log('Updated Farmer', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$farmer->name} updated successfully.");
    }

    /**
     * Delete farmer record
     */
    public function destroy(Farmer $farmer)
    {
        $name = $farmer->name;
        $code = $farmer->farmer_code;

        $farmer->delete();
        AuditLog::log('Deleted Farmer', 'Farmer', $farmer->id, ['code' => $code, 'name' => $name]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$name} ({$code}) deleted successfully.");
    }

    /**
     * Bulk Import Farmers from CSV/Excel
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = null;
                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    if (!array_filter($data)) continue;
                    if (!$header) {
                        $header = array_map(function ($h) {
                            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
                        }, $data);
                    } else {
                        $row = [];
                        foreach ($header as $i => $col) {
                            $row[$col] = isset($data[$i]) ? trim($data[$i]) : null;
                        }
                        $rows[] = $row;
                    }
                }
                fclose($handle);
            }
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                $header = null;
                foreach ($sheetData as $rowValues) {
                    $values = array_values($rowValues);
                    if (!array_filter($values)) continue;
                    if (!$header) {
                        $header = array_map(function ($h) {
                            return strtolower(trim($h));
                        }, $values);
                    } else {
                        $row = [];
                        foreach ($header as $i => $col) {
                            $row[$col] = isset($values[$i]) ? trim($values[$i]) : null;
                        }
                        $rows[] = $row;
                    }
                }
            } else {
                return back()->with('error', 'Excel format requires PhpSpreadsheet. Please upload a .CSV format file instead.');
            }
        } else {
            return back()->with('error', 'Invalid file type. Please upload a .csv, .xlsx, or .xls file.');
        }

        if (empty($rows)) {
            return back()->with('error', 'No valid farmer rows found in the uploaded file.');
        }

        $createdCount = 0;
        $updatedCount = 0;

        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
        } catch (\Throwable $e) {
            $tableColumns = [];
        }

        foreach ($rows as $r) {
            $name = $r['name'] ?? $r['farmer_name'] ?? $r['farmer name'] ?? null;
            if (!$name) continue;

            $code = $r['farmer_code'] ?? $r['code'] ?? null;
            if (!$code) {
                $nextNum = Farmer::max('id') + 1;
                $code = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            $farmerData = [
                'name' => $name,
                'name_hi' => $r['name_hi'] ?? $r['kisan_name'] ?? $r['hindi_name'] ?? null,
                'phone' => $r['phone'] ?? $r['phone_number'] ?? $r['mobile'] ?? null,
                'village' => $r['village'] ?? null,
                'address' => $r['address'] ?? null,
                'vehicle' => $r['vehicle'] ?? null,
                'animal_type' => in_array(strtolower($r['animal_type'] ?? ''), ['cow', 'buffalo', 'mixed']) ? strtolower($r['animal_type']) : 'cow',
                'cow_milk_rate' => is_numeric($r['cow_milk_rate'] ?? null) ? $r['cow_milk_rate'] : null,
                'buffalo_milk_rate' => is_numeric($r['buffalo_milk_rate'] ?? null) ? $r['buffalo_milk_rate'] : null,
                'branch_name' => $r['branch_name'] ?? $r['branch'] ?? null,
                'bank_name' => $r['bank_name'] ?? $r['bank'] ?? null,
                'account_number' => $r['account_number'] ?? $r['account'] ?? null,
                'ifsc_code' => $r['ifsc_code'] ?? $r['ifsc'] ?? null,
                'upi_id' => $r['upi_id'] ?? $r['upi'] ?? null,
                'anamat' => is_numeric($r['anamat'] ?? null) ? $r['anamat'] : 0,
                'building_fund' => is_numeric($r['building_fund'] ?? null) ? $r['building_fund'] : 0,
                'installment' => is_numeric($r['installment'] ?? null) ? $r['installment'] : 0,
                'etc_amount' => is_numeric($r['etc_amount'] ?? null) ? $r['etc_amount'] : 0,
                'grant_amount' => is_numeric($r['grant_amount'] ?? null) ? $r['grant_amount'] : 0,
                'status' => in_array(strtolower($r['status'] ?? ''), ['active', 'inactive', 'blocked']) ? strtolower($r['status']) : 'active',
            ];

            // Match route by name if provided
            if (!empty($r['route']) || !empty($r['assigned_route'])) {
                $routeName = $r['route'] ?? $r['assigned_route'];
                $routeObj = \App\Models\DeliveryRoute::where('name', 'like', "%{$routeName}%")->first();
                if ($routeObj) {
                    $farmerData['route_id'] = $routeObj->id;
                }
            }

            if (!empty($tableColumns)) {
                $filteredData = array_intersect_key($farmerData, array_flip($tableColumns));
            } else {
                $filteredData = $farmerData;
            }

            $existing = Farmer::where('farmer_code', $code)->first();
            if ($existing) {
                $existing->update($filteredData);
                $updatedCount++;
            } else {
                $filteredData['farmer_code'] = $code;
                Farmer::create($filteredData);
                $createdCount++;
            }
        }

        return redirect()->route('farmers.index')->with('success', "Bulk Import Complete: {$createdCount} farmers created, {$updatedCount} updated successfully.");
    }

    /**
     * Download Sample CSV Import Template
     */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="farmer_import_template.csv"',
        ];

        $columns = [
            'farmer_code', 'name', 'name_hi', 'phone', 'village', 'vehicle',
            'animal_type', 'assigned_route', 'bank_name', 'branch_name', 'account_number',
            'ifsc_code', 'anamat', 'building_fund', 'installment', 'etc_amount', 'grant_amount', 'status'
        ];

        $sample1 = [
            'FMR0001', 'Kunal Sharma', 'कुणाल शर्मा', '8596741236', 'Rampur', 'Van',
            'cow', 'Lowadih - Lalpur', 'State Bank of India', 'Main Branch', '30998877665',
            'SBIN0001234', '500.00', '100.00', '250.00', '0.00', '150.00', 'active'
        ];

        $sample2 = [
            'FMR0002', 'Arvind Garg', 'अरविंद गर्ग', '9876543210', 'Palasia', 'Car',
            'buffalo', 'Bombay - Goa', 'HDFC Bank', 'City Branch', '50100223344',
            'HDFC0000123', '1000.00', '200.00', '500.00', '50.00', '0.00', 'active'
        ];

        $callback = function () use ($columns, $sample1, $sample2) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($output, $columns);
            fputcsv($output, $sample1);
            fputcsv($output, $sample2);
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Issue cash advance
     */
    public function storeAdvance(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'purpose' => 'nullable|string',
            'advance_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $advance = FarmerAdvance::create([
            'farmer_id' => $farmer->id,
            'amount' => $validated['amount'],
            'advance_date' => $validated['advance_date'],
            'purpose' => $validated['purpose'] ?? 'General advance',
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Adjust farmer ledger
        $newBalance = $farmer->current_balance - $validated['amount'];
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['advance_date'],
            'type' => 'debit',
            'amount' => $validated['amount'],
            'balance' => $newBalance,
            'reference_type' => 'advance',
            'reference_id' => $advance->id,
            'description' => "Advance Issued: " . ($validated['purpose'] ?? 'Cash Advance'),
        ]);

        AuditLog::log('Issued Advance to Farmer', 'FarmerAdvance', $advance->id, ['amount' => $validated['amount']]);

        return back()->with('success', 'Advance of ₹' . number_format($validated['amount'], 2) . ' issued successfully.');
    }

    /**
     * Update farmer installment & deduction type preferences (Auto, DeductionOff, Fixed)
     */
    public function updateInstallment(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'installment' => 'nullable|numeric|min:0',
            'deduction_type' => 'required|in:Auto,DeductionOff,Fixed',
        ]);

        $extraRates = is_array($farmer->extra_rates) ? $farmer->extra_rates : [];
        $deductionType = $validated['deduction_type'];
        $amount = ($deductionType === 'Fixed') ? (float)($validated['installment'] ?? 0) : 0.0;

        $extraRates['installment_deduction_type'] = $deductionType;
        $extraRates['Installment'] = $amount;

        $farmer->update([
            'installment' => $amount,
            'extra_rates' => $extraRates,
        ]);

        AuditLog::log("Updated Installment preference for Farmer {$farmer->name}: Type={$deductionType}, Amount=₹{$amount}", 'Farmer', $farmer->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Installment configuration updated successfully.',
                'installment' => number_format($amount, 2),
                'installment_raw' => $amount,
                'deduction_type' => $deductionType,
                'display_text' => ($deductionType === 'Fixed') ? number_format($amount, 2) . ' ₹' : $deductionType,
            ]);
        }

        return back()->with('success', 'Installment preferences updated successfully.');
    }
}
