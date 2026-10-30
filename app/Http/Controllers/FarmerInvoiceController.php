<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Farmer;
use App\Models\FarmerInvoice;
use App\Models\MilkCollection;
use App\Models\FarmerDeduction;
use App\Models\FarmerAdvance;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerInvoiceController extends Controller
{
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Resiliently ensure farmer_invoices table exists on the database
     * and clean up any duplicate constructor-seeded sample advances.
     */
    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('farmer_invoices')) {
                Schema::create('farmer_invoices', function (Blueprint $table) {
                    $table->id();
                    $table->string('invoice_number')->unique();
                    $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                    $table->date('period_start');
                    $table->date('period_end');
                    $table->decimal('total_quantity', 10, 2)->default(0.00);
                    $table->decimal('milk_amount', 10, 2)->default(0.00);
                    $table->decimal('stationary_deduction', 10, 2)->default(0.00);
                    $table->decimal('feed_deduction', 10, 2)->default(0.00);
                    $table->decimal('advance_deduction', 10, 2)->default(0.00);
                    $table->decimal('other_deduction', 10, 2)->default(0.00);
                    $table->decimal('total_deduction', 10, 2)->default(0.00);
                    $table->decimal('other_credit', 10, 2)->default(0.00);
                    $table->decimal('transport_credit', 10, 2)->default(0.00);
                    $table->decimal('subsidy_credit', 10, 2)->default(0.00);
                    $table->decimal('incentive_credit', 10, 2)->default(0.00);
                    $table->decimal('total_credit', 10, 2)->default(0.00);
                    $table->decimal('total_amount', 10, 2)->default(0.00);
                    $table->decimal('round_off', 10, 2)->default(0.00);
                    $table->decimal('previous_balance', 10, 2)->default(0.00);
                    $table->decimal('net_payment', 10, 2)->default(0.00);
                    $table->string('status')->default('pending');
                    $table->boolean('is_edited')->default(false);
                    $table->string('payment_mode')->nullable()->default('Bank Transfer');
                    $table->string('payment_reference')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            } else {
                $newCols = [
                    'other_credit' => "ALTER TABLE `farmer_invoices` ADD COLUMN `other_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_deduction`",
                    'transport_credit' => "ALTER TABLE `farmer_invoices` ADD COLUMN `transport_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `other_credit`",
                    'subsidy_credit' => "ALTER TABLE `farmer_invoices` ADD COLUMN `subsidy_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `transport_credit`",
                    'incentive_credit' => "ALTER TABLE `farmer_invoices` ADD COLUMN `incentive_credit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `subsidy_credit`",
                    'round_off' => "ALTER TABLE `farmer_invoices` ADD COLUMN `round_off` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`",
                    'is_edited' => "ALTER TABLE `farmer_invoices` ADD COLUMN `is_edited` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`",
                    'deductions_breakdown' => "ALTER TABLE `farmer_invoices` ADD COLUMN `deductions_breakdown` LONGTEXT NULL AFTER `other_deduction`",
                ];
                foreach ($newCols as $col => $sql) {
                    if (!Schema::hasColumn('farmer_invoices', $col)) {
                        try {
                            \DB::statement($sql);
                        } catch (\Throwable $ex) {}
                    }
                }
            }

            if (Schema::hasTable('farmer_advances')) {
                $seededPurposes = [
                    '-PAID BY DR',
                    'Emergency Advance',
                    'Cattle Feed loan',
                    'Seasonal Festival Advance',
                    'Full Cleared Loan',
                ];
                \DB::table('farmer_advances')
                    ->whereIn('voucher_no', ['001', '002', '003', '004', '005'])
                    ->whereIn('purpose', $seededPurposes)
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Calculate accurate period billing figures for a farmer.
     */
    private function calculateInvoiceMetrics(int $farmerId, string $startDate, string $endDate): array
    {
        $farmer = Farmer::find($farmerId);

        $collections = MilkCollection::where('farmer_id', $farmerId)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->get();

        $totalQuantity = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum(function ($c) {
            $net = (float) $c->net_amount;
            if ($net > 0) {
                return $net;
            }
            $rate = (float) ($c->applied_rate ?: $c->calculated_rate ?: 0);
            return round((float) $c->quantity_liters * $rate, 2);
        }), 2);

        $feedDeduction = 0.00;
        $stationaryDeduction = 0.00;
        $advanceFromDeductions = 0.00;
        $otherCredit = 0.00;
        $transportCredit = 0.00;
        $subsidyCredit = 0.00;
        $incentiveCredit = 0.00;

        if (Schema::hasTable('farmer_deductions')) {
            $deductions = FarmerDeduction::where('farmer_id', $farmerId)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->get();

            $given = $deductions->where('transaction_type', 'given');
            $feedDeduction = round((float) $given->where('deduction_type', 'cattle_feed')->sum('amount'), 2);
            $advanceFromDeductions = round((float) $given->where('deduction_type', 'advance_recovery')->sum('amount'), 2);
            $stationaryDeduction = round((float) $given->whereNotIn('deduction_type', ['cattle_feed', 'advance_recovery'])->sum('amount'), 2);
            $otherCredit = round((float) $deductions->where('transaction_type', 'received')->sum('amount'), 2);
        }

        // Dynamically wire Addition Types configured in Center Information (All Farmers + Farmer Wise)
        try {
            $existingBonusSum = (float) $collections->sum('bonus');
            if ($existingBonusSum <= 0 && $totalQuantity > 0) {
                $rawGross = round((float) $collections->sum(function ($c) {
                    $rate = (float) ($c->applied_rate ?: $c->calculated_rate ?: 0);
                    return (float) $c->quantity_liters * $rate;
                }), 2);
                $charges = \App\Services\CenterAdditionDeductionService::calculateForCollection($farmer, $totalQuantity, $rawGross);
                foreach ($charges['additions'] as $add) {
                    $name = strtolower($add['name'] ?? '');
                    $amt = (float) ($add['amount'] ?? 0);
                    if ($amt <= 0) continue;
                    if (str_contains($name, 'transport')) {
                        $transportCredit += $amt;
                    } elseif (str_contains($name, 'subsidy')) {
                        $subsidyCredit += $amt;
                    } elseif (str_contains($name, 'incentive')) {
                        $incentiveCredit += $amt;
                    } else {
                        $otherCredit += $amt;
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Include unpaid product purchases (ProductSale) in feedDeduction
        if (Schema::hasTable('product_sales')) {
            $farmerCode = $farmer ? $farmer->farmer_code : null;
            $unpaidSalesDue = (float) \App\Models\ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                    $q->where('farmer_id', $farmerId);
                    if (!empty($farmerCode)) {
                        $q->orWhere('farmer_code', $farmerCode);
                    }
                })
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->sum('remaining_amount');
            $feedDeduction = round($feedDeduction + $unpaidSalesDue, 2);
        }

        $advanceFromLoans = 0.00;
        $totalLoanPrincipal = 0.00;
        if (Schema::hasTable('farmer_advances')) {
            $advances = FarmerAdvance::where('farmer_id', $farmerId)
                ->where('status', '!=', 'recovered')
                ->get();

            $totalLoanPrincipal = round((float) $advances->sum(function ($adv) {
                return (float) $adv->principal_balance;
            }), 2);

            // Installment preference
            $instType = $farmer ? $farmer->getInstallmentDeductionType() : 'Auto';
            $instAmount = $farmer ? (float) $farmer->getInstallmentAmount() : 0.0;

            if ($instType === 'DeductionOff') {
                $advanceFromLoans = 0.00;
            } elseif ($instType === 'Fixed' && $instAmount > 0) {
                $advanceFromLoans = min($instAmount, $totalLoanPrincipal);
            } else {
                // Auto: full outstanding or up to milk amount
                $advanceFromLoans = $totalLoanPrincipal;
            }
        }

        $advanceDeduction = round($advanceFromLoans + $advanceFromDeductions, 2);
        $otherDeduction = 0.00;

        // Compile category-wise deductions breakdown
        $deductionsBreakdown = [];
        if ($stationaryDeduction > 0) {
            $deductionsBreakdown[] = [
                'name' => 'Stationary',
                'category' => 'Stationary',
                'original_balance' => $stationaryDeduction,
                'deducted_amount' => $stationaryDeduction,
                'remaining_balance' => 0.00,
            ];
        }
        if ($feedDeduction > 0) {
            $deductionsBreakdown[] = [
                'name' => 'Food / Feed',
                'category' => 'Feed',
                'original_balance' => $feedDeduction,
                'deducted_amount' => $feedDeduction,
                'remaining_balance' => 0.00,
            ];
        }
        if ($advanceDeduction > 0 || $totalLoanPrincipal > 0) {
            $origAdv = max($totalLoanPrincipal, $advanceDeduction);
            $dedAdv = min($advanceDeduction, $origAdv);
            $remAdv = max(0, $origAdv - $dedAdv);
            $deductionsBreakdown[] = [
                'name' => 'Advance / Loan',
                'category' => 'Advance',
                'original_balance' => $origAdv,
                'deducted_amount' => $dedAdv,
                'remaining_balance' => $remAdv,
            ];
        }

        $totalCredit = round($otherCredit + $transportCredit + $subsidyCredit + $incentiveCredit, 2);

        // A = Stationary + Feed + In Hand Advance
        $totalDeduction = round($stationaryDeduction + $feedDeduction + $advanceDeduction, 2);

        // B + C = Total Credit (B) + Milk Amount (C)
        $totalAmount = round($totalCredit + $milkAmount, 2);

        // Balance = Remaining unpaid deduction if A > (B + C), else 0.00
        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);

        // Payment (B+C-A) = Net payable amount if (B + C) >= A, else 0.00
        $rawPayable = round(max(0, $totalAmount - $totalDeduction), 2);
        $roundOff = 0.00;
        $netPayment = $rawPayable;

        return [
            'total_quantity' => $totalQuantity,
            'milk_amount' => $milkAmount,
            'stationary_deduction' => $stationaryDeduction,
            'feed_deduction' => $feedDeduction,
            'advance_deduction' => $advanceDeduction,
            'other_deduction' => $otherDeduction,
            'deductions_breakdown' => $deductionsBreakdown,
            'total_deduction' => $totalDeduction,
            'other_credit' => $otherCredit,
            'transport_credit' => $transportCredit,
            'subsidy_credit' => $subsidyCredit,
            'incentive_credit' => $incentiveCredit,
            'total_credit' => $totalCredit,
            'total_amount' => $totalAmount,
            'round_off' => $roundOff,
            'previous_balance' => $previousBalance,
            'net_payment' => $netPayment,
        ];
    }

    /**
     * Synchronize a FarmerInvoice record with live collections, deductions, and advances
     * ONLY if it has not been manually edited/verified by admin.
     */
    private function syncInvoice(FarmerInvoice $invoice): FarmerInvoice
    {
        if ($invoice->is_edited) {
            return $invoice;
        }

        $start = $invoice->period_start instanceof Carbon
            ? $invoice->period_start->format('Y-m-d')
            : Carbon::parse($invoice->period_start)->format('Y-m-d');
        $end = $invoice->period_end instanceof Carbon
            ? $invoice->period_end->format('Y-m-d')
            : Carbon::parse($invoice->period_end)->format('Y-m-d');

        $metrics = $this->calculateInvoiceMetrics((int) $invoice->farmer_id, $start, $end);

        $needsUpdate = false;
        foreach ($metrics as $key => $val) {
            if (round((float) ($invoice->{$key} ?? 0), 2) !== round((float) $val, 2)) {
                $needsUpdate = true;
                break;
            }
        }

        if ($needsUpdate) {
            $invoice->update($metrics);
        }

        return $invoice;
    }

    /**
     * Display Farmer Invoice Listing with Pending Invoices & Generated Invoices Tabs
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $farmerSearch = $request->get('farmer_search');
        $invoiceSearch = $request->get('invoice_search');
        $perPage = (int) $request->get('per_page', 50);

        $baseQuery = FarmerInvoice::with('farmer');

        if ($startDate && $endDate) {
            $baseQuery->whereDate('period_start', '>=', $startDate)
                      ->whereDate('period_end', '<=', $endDate);
        }

        if (!empty($farmerSearch)) {
            $baseQuery->whereHas('farmer', function ($q) use ($farmerSearch) {
                $q->where('name', 'like', "%{$farmerSearch}%")
                  ->orWhere('farmer_code', 'like', "%{$farmerSearch}%")
                  ->orWhere('phone', 'like', "%{$farmerSearch}%");
            });
        }

        if (!empty($invoiceSearch)) {
            $baseQuery->where('invoice_number', 'like', "%{$invoiceSearch}%");
        }

        // Deduplicate overlapping invoices for the same farmer & period_start (keep latest)
        $matchingInvoices = (clone $baseQuery)->orderBy('id', 'desc')->get();
        $seenFarmerPeriod = [];
        foreach ($matchingInvoices as $inv) {
            $key = $inv->farmer_id . '_' . ($inv->period_start instanceof Carbon ? $inv->period_start->format('Y-m-d') : $inv->period_start);
            if (isset($seenFarmerPeriod[$key])) {
                $inv->delete();
                continue;
            }
            $seenFarmerPeriod[$key] = true;
            $this->syncInvoice($inv);
        }

        $pendingCount = (clone $baseQuery)->where('status', 'pending')->count();
        $generatedCount = (clone $baseQuery)->where('status', '!=', 'pending')->count();

        // Default to 'pending' tab if explicitly requested or if pending invoices exist; otherwise 'generated'
        if ($request->filled('status_tab')) {
            $statusTab = $request->get('status_tab') === 'generated' ? 'generated' : 'pending';
        } else {
            $statusTab = $pendingCount > 0 ? 'pending' : 'generated';
        }

        $query = clone $baseQuery;
        if ($statusTab === 'pending') {
            $query->where('status', 'pending');
        } else {
            $query->where('status', '!=', 'pending');
        }

        $allTabIds = (clone $query)->orderBy('id', 'asc')->pluck('id')->toArray();

        $invoices = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        // Calculate System Totals for Table Summary Row
        $summaryQuery = clone $query;
        $totalQuantity = (float) $summaryQuery->sum('total_quantity');
        $totalStationary = (float) $summaryQuery->sum('stationary_deduction');
        $totalFeed = (float) $summaryQuery->sum('feed_deduction');
        $totalAdvance = (float) $summaryQuery->sum('advance_deduction');
        $totalDeduction = (float) $summaryQuery->sum('total_deduction');
        $totalCredit = (float) $summaryQuery->sum('total_credit');
        $totalMilkAmount = (float) $summaryQuery->sum('milk_amount');
        $totalAmount = (float) $summaryQuery->sum('total_amount');
        $totalBalance = (float) $summaryQuery->sum('previous_balance');
        $totalPayment = (float) $summaryQuery->sum('net_payment');

        // All active farmers for generate filter
        $farmers = Farmer::where('status', 'active')->orderBy('name')->get();

        return view('farmers.invoices.index', compact(
            'invoices',
            'farmers',
            'startDate',
            'endDate',
            'farmerSearch',
            'invoiceSearch',
            'perPage',
            'statusTab',
            'pendingCount',
            'generatedCount',
            'allTabIds',
            'totalQuantity',
            'totalStationary',
            'totalFeed',
            'totalAdvance',
            'totalDeduction',
            'totalCredit',
            'totalMilkAmount',
            'totalAmount',
            'totalBalance',
            'totalPayment'
        ));
    }

    /**
     * Generate Invoices for period -> Pushes first into 'pending' tab section for admin review & correction
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'farmer_id' => 'nullable|exists:farmers,id',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $farmersQuery = Farmer::where('status', 'active');
        if (!empty($validated['farmer_id'])) {
            $farmersQuery->where('id', $validated['farmer_id']);
        }

        $farmers = $farmersQuery->get();
        $generatedCount = 0;

        foreach ($farmers as $farmer) {
            $metrics = $this->calculateInvoiceMetrics((int) $farmer->id, $startDate, $endDate);

            // Skip farmers with no activity at all
            if ($metrics['total_quantity'] == 0 && $metrics['milk_amount'] == 0 && $metrics['total_deduction'] == 0 && $metrics['total_credit'] == 0) {
                continue;
            }

            // Find existing invoice for same farmer & period_start to avoid duplicate overlapping bills
            $existing = FarmerInvoice::where('farmer_id', $farmer->id)
                ->whereDate('period_start', $startDate)
                ->orderBy('id', 'desc')
                ->first();

            if ($existing) {
                $existing->update(array_merge($metrics, [
                    'period_end' => $endDate,
                    'status' => 'pending',
                    'is_edited' => false,
                    'created_by' => Auth::id(),
                ]));
                FarmerInvoice::where('farmer_id', $farmer->id)
                    ->whereDate('period_start', $startDate)
                    ->where('id', '!=', $existing->id)
                    ->delete();
            } else {
                $invNum = 'BILL-' . str_pad((FarmerInvoice::max('id') + 101), 4, '0', STR_PAD_LEFT);
                FarmerInvoice::create(array_merge($metrics, [
                    'farmer_id' => $farmer->id,
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'invoice_number' => $invNum,
                    'status' => 'pending',
                    'is_edited' => false,
                    'created_by' => Auth::id(),
                ]));
            }

            $generatedCount++;
        }

        AuditLog::log("Generated {$generatedCount} Pending Farmer Invoices", 'FarmerInvoice', null, [
            'period' => "{$startDate} to {$endDate}",
            'count' => $generatedCount
        ]);

        return redirect()->route('farmer-invoices.index', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status_tab' => 'pending',
        ])->with('success', "Prepared {$generatedCount} Pending Invoices ({$startDate} to {$endDate}). Review, modify if needed, and click 'Save Invoice' to move them to Generated state!");
    }

    /**
     * Display Invoice Details full page
     */
    public function show(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load(['farmer', 'creator']);
        $farmer = $invoice->farmer;

        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;
        $cattleFeeds = $invoice->cattle_feeds_list;

        return view('farmers.invoices.show', compact(
            'invoice',
            'farmer',
            'collections',
            'deductions',
            'cattleFeeds'
        ));
    }

    /**
     * Return JSON data for Invoice Preview & 1-by-1 Pending Invoice Review/Correction Modal
     */
    public function previewData(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load('farmer');
        $farmer = $invoice->farmer;

        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;
        $cattleFeeds = $invoice->cattle_feeds_list;

        $daysInPeriod = max(1, $invoice->period_start->diffInDays($invoice->period_end) + 1);
        $totalPossibleShifts = $daysInPeriod * 2;
        $presentShifts = $collections->count();

        $avgFat = $collections->count() > 0 ? round((float) $collections->avg('fat'), 2) : 0.0;
        $avgSnf = $collections->count() > 0 ? round((float) $collections->avg('snf'), 2) : 0.0;
        $avgClr = $collections->count() > 0 ? round((float) $collections->avg('clr'), 2) : 0.0;
        $avgRate = (float) $invoice->total_quantity > 0
            ? round((float) $invoice->milk_amount / (float) $invoice->total_quantity, 2)
            : 0.0;

        $feedPaymentReceived = round((float) $cattleFeeds->sum('paid_amount'), 2);
        $feedRemainingBalance = round((float) $cattleFeeds->sum('remaining_amount'), 2);

        // Fetch dynamic category breakdown
        $categoriesBreakdown = $invoice->getCategoryDeductionsBreakdown();

        $formattedCollections = $collections->map(function ($c) {
            $dt = $c->collection_date instanceof Carbon ? $c->collection_date : Carbon::parse($c->collection_date);
            return [
                'id' => $c->id,
                'collection_date' => $dt->format('Y-m-d'),
                'day_num' => $dt->format('d'),
                'date_display' => $dt->format('d M Y'),
                'shift' => ucfirst($c->shift),
                'milk_type' => ucfirst($c->milk_type),
                'quantity_liters' => (float) $c->quantity_liters,
                'fat' => (float) ($c->fat ?? 0),
                'snf' => (float) ($c->snf ?? 0),
                'clr' => (float) ($c->clr ?? 0),
                'applied_rate' => (float) ($c->applied_rate ?: $c->calculated_rate ?: 0),
                'net_amount' => (float) $c->net_amount,
            ];
        });

        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'status' => $invoice->status,
            'is_edited' => (bool) $invoice->is_edited,
            'period_start' => $invoice->period_start->format('Y-m-d'),
            'period_end' => $invoice->period_end->format('Y-m-d'),
            'period_start_display' => $invoice->period_start->format('d/m/Y'),
            'period_end_display' => $invoice->period_end->format('d/m/Y'),
            'present_shifts' => $presentShifts,
            'total_shifts' => $totalPossibleShifts,
            'farmer' => [
                'id' => $farmer->id,
                'name' => $farmer->name,
                'name_hi' => $farmer->name_hi ?? '',
                'code' => $farmer->farmer_code,
                'phone' => $farmer->phone ?? '',
                'bank_name' => $farmer->bank_name ?? 'N/A',
                'account_number' => $farmer->account_number ?? 'N/A',
                'ifsc_code' => $farmer->ifsc_code ?? 'N/A',
                'anamat' => (float) ($farmer->anamat ?? 0),
            ],
            'collections' => $formattedCollections,
            'cattle_feeds' => $cattleFeeds->values(),
            'feed_payment_received' => $feedPaymentReceived,
            'feed_remaining_balance' => $feedRemainingBalance,
            'deductions' => $deductions->values(),
            'categories_breakdown' => $categoriesBreakdown,
            'additions' => [
                'other_credit' => (float) ($invoice->other_credit ?? 0),
                'transport_credit' => (float) ($invoice->transport_credit ?? 0),
                'subsidy_credit' => (float) ($invoice->subsidy_credit ?? 0),
                'incentive_credit' => (float) ($invoice->incentive_credit ?? 0),
                'total_credit' => (float) ($invoice->total_credit ?? 0),
            ],
            'deduction_inputs' => [
                'stationary_deduction' => (float) ($invoice->stationary_deduction ?? 0),
                'feed_deduction' => (float) ($invoice->feed_deduction ?? 0),
                'advance_deduction' => (float) ($invoice->advance_deduction ?? 0),
                'total_deduction' => (float) ($invoice->total_deduction ?? 0),
            ],
            'totals' => [
                'total_quantity' => (float) $invoice->total_quantity,
                'avg_fat' => $avgFat,
                'avg_snf' => $avgSnf,
                'avg_clr' => $avgClr,
                'avg_rate' => $avgRate,
                'milk_amount' => (float) $invoice->milk_amount,
                'total_credit' => (float) $invoice->total_credit,
                'total_amount' => (float) $invoice->total_amount,
                'total_deduction' => (float) $invoice->total_deduction,
                'round_off' => (float) ($invoice->round_off ?? 0),
                'previous_balance' => (float) $invoice->previous_balance,
                'net_payment' => (float) $invoice->net_payment,
            ]
        ]);
    }

    /**
     * Save Corrections & Verify Invoice (Moves invoice from 'pending' to 'generated')
     */
    public function verifyAndSave(Request $request, FarmerInvoice $invoice)
    {
        $validated = $request->validate([
            'other_credit' => 'nullable|numeric|min:0',
            'transport_credit' => 'nullable|numeric|min:0',
            'subsidy_credit' => 'nullable|numeric|min:0',
            'incentive_credit' => 'nullable|numeric|min:0',
            'stationary_deduction' => 'nullable|numeric|min:0',
            'feed_deduction' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'category_deductions' => 'nullable|array',
        ]);

        $otherCredit = round((float) ($validated['other_credit'] ?? 0), 2);
        $transportCredit = round((float) ($validated['transport_credit'] ?? 0), 2);
        $subsidyCredit = round((float) ($validated['subsidy_credit'] ?? 0), 2);
        $incentiveCredit = round((float) ($validated['incentive_credit'] ?? 0), 2);
        $totalCredit = round($otherCredit + $transportCredit + $subsidyCredit + $incentiveCredit, 2);

        $stationaryDeduction = round((float) ($validated['stationary_deduction'] ?? 0), 2);
        $feedDeduction = round((float) ($validated['feed_deduction'] ?? 0), 2);
        $advanceDeduction = round((float) ($validated['advance_deduction'] ?? 0), 2);

        // Process category-wise deductions if sent from UI
        $breakdown = $invoice->getCategoryDeductionsBreakdown();
        if ($request->has('category_deductions') && is_array($request->input('category_deductions'))) {
            $catInputs = $request->input('category_deductions');
            $customTotalDed = 0.0;
            $newBreakdown = [];

            foreach ($breakdown as $bItem) {
                $cKey = $bItem['key'] ?? $bItem['name'];
                $dedAmt = isset($catInputs[$cKey]) ? round((float)$catInputs[$cKey], 2) : (float)$bItem['deducted_amount'];
                // Never deduct more than original outstanding balance
                $origBal = (float)$bItem['original_balance'];
                if ($origBal > 0) {
                    $dedAmt = min($dedAmt, $origBal);
                }
                $dedAmt = max(0, $dedAmt);
                $remBal = max(0, $origBal - $dedAmt);

                $newBreakdown[] = [
                    'key' => $cKey,
                    'name' => $bItem['name'],
                    'category' => $bItem['category'],
                    'original_balance' => $origBal,
                    'deducted_amount' => $dedAmt,
                    'remaining_balance' => $remBal,
                ];
                $customTotalDed += $dedAmt;

                // Sync with individual category columns
                if ($cKey === 'advance' || str_contains(strtolower($cKey), 'advance') || str_contains(strtolower($cKey), 'loan')) {
                    $advanceDeduction = $dedAmt;
                } elseif ($cKey === 'feed' || str_contains(strtolower($cKey), 'feed') || str_contains(strtolower($cKey), 'food')) {
                    $feedDeduction = $dedAmt;
                } elseif ($cKey === 'stationary' || str_contains(strtolower($cKey), 'stationary')) {
                    $stationaryDeduction = $dedAmt;
                }
            }
            $breakdown = $newBreakdown;
            $totalDeduction = round($customTotalDed, 2);
        } else {
            $totalDeduction = round($stationaryDeduction + $feedDeduction + $advanceDeduction, 2);
        }

        $milkAmount = round((float) $invoice->milk_amount, 2);
        $totalAmount = round($milkAmount + $totalCredit, 2);

        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);
        $rawPayable = round(max(0, $totalAmount - $totalDeduction), 2);

        if (isset($validated['round_off']) && $validated['round_off'] !== null && $validated['round_off'] !== '') {
            $roundOff = round((float) $validated['round_off'], 2);
        } else {
            $roundOff = round(round($rawPayable) - $rawPayable, 2);
        }

        $netPayment = round(max(0, $rawPayable + $roundOff), 2);

        $invoice->update([
            'other_credit' => $otherCredit,
            'transport_credit' => $transportCredit,
            'subsidy_credit' => $subsidyCredit,
            'incentive_credit' => $incentiveCredit,
            'total_credit' => $totalCredit,
            'stationary_deduction' => $stationaryDeduction,
            'feed_deduction' => $feedDeduction,
            'advance_deduction' => $advanceDeduction,
            'deductions_breakdown' => $breakdown,
            'total_deduction' => $totalDeduction,
            'total_amount' => $totalAmount,
            'round_off' => $roundOff,
            'previous_balance' => $previousBalance,
            'net_payment' => $netPayment,
            'status' => 'generated',
            'is_edited' => true,
        ]);

        AuditLog::log('Verified & Saved Farmer Invoice', 'FarmerInvoice', $invoice->id, [
            'invoice_number' => $invoice->invoice_number,
            'net_payment' => $netPayment,
            'status' => 'generated',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} saved and moved to Generated state!",
                'invoice' => $invoice->fresh('farmer'),
            ]);
        }

        return redirect()->route('farmer-invoices.index', [
            'start_date' => $invoice->period_start->format('Y-m-d'),
            'end_date' => $invoice->period_end->format('Y-m-d'),
            'status_tab' => 'generated',
        ])->with('success', "Invoice #{$invoice->invoice_number} verified and generated successfully!");
    }

    /**
     * Bulk Approve Selected Pending Invoices to Generated State
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:farmer_invoices,id',
        ]);

        $count = FarmerInvoice::whereIn('id', $validated['invoice_ids'])->update([
            'status' => 'generated',
        ]);

        AuditLog::log("Bulk Approved {$count} Pending Invoices to Generated", 'FarmerInvoice', null);

        return redirect()->route('farmer-invoices.index', [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status_tab' => 'generated',
        ])->with('success', "Successfully approved and generated {$count} selected invoices!");
    }

    /**
     * Printable view of single invoice matching Screenshot 2 & 3
     */
    public function print(FarmerInvoice $invoice)
    {
        $this->syncInvoice($invoice);
        $invoice->load('farmer');
        $farmer = $invoice->farmer;
        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;

        return view('farmers.invoices.print', compact('invoice', 'farmer', 'collections', 'deductions'));
    }

    /**
     * Bank Payment Print View matching "Bank Payment Print" button in Screenshot 1
     */
    public function bankPaymentPrint(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $query = FarmerInvoice::with('farmer')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate);

        if ($request->filled('selected_ids')) {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        }

        $invoices = $query->get()->map(function ($inv) {
            return $this->syncInvoice($inv);
        });
        $totalPayment = $invoices->sum('net_payment');

        return view('farmers.invoices.bank_payment_print', compact('invoices', 'startDate', 'endDate', 'totalPayment'));
    }

    /**
     * Export Excel / CSV matching "Download Excel" button in Screenshot 1
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $invoices = FarmerInvoice::with('farmer')
            ->whereDate('period_start', '>=', $startDate)
            ->whereDate('period_end', '<=', $endDate)
            ->get()
            ->map(function ($inv) {
                return $this->syncInvoice($inv);
            });

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"farmer_invoices_{$startDate}_to_{$endDate}.csv\"",
        ];

        $columns = [
            'Farmer Code',
            'Farmer Name',
            'Milk Quantity (L)',
            'Stationary',
            'Feed',
            'In Hand Advance',
            'Total Deduction (A)',
            'Total Credit (B)',
            'Milk Amount (C)',
            'Total Amount (B+C)',
            'Balance',
            'Payment (B+C-A)',
        ];

        $callback = function () use ($columns, $invoices) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);

            foreach ($invoices as $inv) {
                fputcsv($output, [
                    $inv->farmer->farmer_code ?? '-',
                    $inv->farmer->name ?? '-',
                    number_format($inv->total_quantity, 2, '.', ''),
                    number_format($inv->stationary_deduction, 2, '.', ''),
                    number_format($inv->feed_deduction, 2, '.', ''),
                    number_format($inv->advance_deduction, 2, '.', ''),
                    number_format($inv->total_deduction, 2, '.', ''),
                    number_format($inv->total_credit, 2, '.', ''),
                    number_format($inv->milk_amount, 2, '.', ''),
                    number_format($inv->total_amount, 2, '.', ''),
                    number_format($inv->previous_balance, 2, '.', ''),
                    number_format($inv->net_payment, 2, '.', ''),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete an invoice
     */
    public function destroy(FarmerInvoice $invoice)
    {
        $invNum = $invoice->invoice_number;
        $invoice->delete();

        AuditLog::log('Deleted Farmer Invoice', 'FarmerInvoice', $invoice->id, ['number' => $invNum]);

        return redirect()->route('farmer-invoices.index')->with('success', "Invoice #{$invNum} deleted successfully.");
    }

    /**
     * Bulk Delete Invoices
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:farmer_invoices,id',
        ]);

        $count = FarmerInvoice::whereIn('id', $validated['invoice_ids'])->delete();

        AuditLog::log("Bulk Deleted {$count} Farmer Invoices", 'FarmerInvoice', null);

        return redirect()->route('farmer-invoices.index')->with('success', "Successfully deleted {$count} selected invoices.");
    }
}
