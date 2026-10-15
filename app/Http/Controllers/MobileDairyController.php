<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use App\Models\Farmer;
use App\Models\MilkCollection;
use App\Models\FarmerAdvance;
use App\Models\FarmerAdvanceRepayment;
use App\Models\FarmerDeduction;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\FarmerEmergencyFund;
use App\Models\FarmerInvoice;
use App\Models\FarmerSettlement;
use App\Models\FarmerLedger;
use App\Models\RateChart;
use App\Models\CollectionCenter;
use App\Models\AuditLog;

class MobileDairyController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    /**
     * Resiliently ensure all required tables and columns exist.
     */
    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('farmer_emergency_funds')) {
                Schema::create('farmer_emergency_funds', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                    $table->string('request_no')->nullable()->unique();
                    $table->date('request_date');
                    $table->decimal('amount', 10, 2);
                    $table->text('reason')->nullable();
                    $table->string('status')->default('approved');
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->dateTime('disbursed_at')->nullable();
                    $table->decimal('recovered_amount', 10, 2)->default(0.00);
                    $table->string('payment_mode')->default('Cash');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }

            if (Schema::hasTable('farmer_deductions')) {
                if (!Schema::hasColumn('farmer_deductions', 'reference_no')) {
                    Schema::table('farmer_deductions', function (Blueprint $table) {
                        $table->string('reference_no')->nullable()->after('comments');
                        $table->string('status')->default('applied')->after('reference_no'); // applied, pending
                    });
                }
            }
        } catch (\Throwable $e) {
            // Ignore during construction
        }
    }

    /**
     * Standalone Mobile Dairy Hub / Farmer Switcher
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Farmer::with(['collectionCenter', 'route']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%");
            });
        }

        $farmers = $query->orderBy('name')->paginate(24)->withQueryString();
        $totalFarmers = Farmer::count();
        $activeFarmers = Farmer::where('status', 'active')->count();

        return view('mobile_dairy.index', compact('farmers', 'search', 'totalFarmers', 'activeFarmers'));
    }

    /**
     * Complete 8-Tab Mobile Dairy Suite for a single Farmer
     */
    public function show(Farmer $farmer, Request $request)
    {
        $activeTab = $request->input('tab', 'milk_entry');
        $data = $this->buildFarmerSuiteData($farmer, $request);

        return view('mobile_dairy.show', array_merge([
            'farmer' => $farmer,
            'activeTab' => $activeTab,
            'mobileDairyData' => $data,
        ], $data));
    }

    /**
     * Build all 8-Tab datasets and banner metrics for a farmer.
     */
    public function buildFarmerSuiteData(Farmer $farmer, Request $request): array
    {
        $farmer->load(['collectionCenter', 'rateChart', 'route']);

        // 1. BANNER METRICS
        $totalMilkQuantity = (float) MilkCollection::where('farmer_id', $farmer->id)->sum('quantity_liters');
        $currentPayableBalance = (float) $farmer->current_balance;

        // Outstanding loans
        $outstandingLoan = 0.00;
        if (Schema::hasTable('farmer_advances')) {
            $advances = FarmerAdvance::where('farmer_id', $farmer->id)->where('status', '!=', 'recovered')->get();
            $outstandingLoan = (float) $advances->sum(function ($a) {
                return (float) $a->principal_balance;
            });
        }

        // Product Credit Balance (Unpaid product purchases)
        $productCreditBalance = 0.00;
        if (Schema::hasTable('product_sales')) {
            $productCreditBalance = (float) ProductSale::where(function ($q) use ($farmer) {
                $q->where('farmer_id', $farmer->id);
                if (!empty($farmer->farmer_code)) {
                    $q->orWhere('farmer_code', $farmer->farmer_code);
                }
            })->sum('remaining_amount');
        }

        // Emergency Assistance Balance
        $emergencyBalance = 0.00;
        if (Schema::hasTable('farmer_emergency_funds')) {
            $emergencyFunds = FarmerEmergencyFund::where('farmer_id', $farmer->id)->get();
            $emergencyBalance = (float) $emergencyFunds->sum(function ($e) {
                return (float) $e->outstanding_amount;
            });
        }

        // Resolved Milk Rates
        $resolvedCowRate = $this->resolveMilkRate($farmer, 'cow', 4.0, 8.5, 28);
        $resolvedBuffaloRate = $this->resolveMilkRate($farmer, 'buffalo', 6.5, 9.0, 30);

        // TAB 1: MILK ENTRY DATA
        $recentCollections = MilkCollection::where('farmer_id', $farmer->id)
            ->latest('collection_date')
            ->latest('id')
            ->take(30)
            ->get();
        $todayMorningLitres = (float) MilkCollection::where('farmer_id', $farmer->id)
            ->whereDate('collection_date', Carbon::today())
            ->where('shift', 'morning')
            ->sum('quantity_liters');
        $todayEveningLitres = (float) MilkCollection::where('farmer_id', $farmer->id)
            ->whereDate('collection_date', Carbon::today())
            ->where('shift', 'evening')
            ->sum('quantity_liters');

        // TAB 2: LOANS & ADVANCES DATA
        $loans = collect();
        $totalLoanAmount = 0.00;
        $totalLoanRepaid = 0.00;
        if (Schema::hasTable('farmer_advances')) {
            $loans = FarmerAdvance::with('repayments')
                ->where('farmer_id', $farmer->id)
                ->latest('advance_date')
                ->get();
            $totalLoanAmount = (float) $loans->sum('amount');
            $totalLoanRepaid = (float) $loans->sum(function ($l) {
                return (float) $l->total_paid;
            });
        }

        // TAB 3: DEDUCTIONS DATA
        $deductions = collect();
        $totalDeductions = 0.00;
        $appliedDeductions = 0.00;
        $pendingDeductions = 0.00;
        if (Schema::hasTable('farmer_deductions')) {
            $deductions = FarmerDeduction::where('farmer_id', $farmer->id)
                ->latest('entry_date')
                ->get();
            $totalDeductions = (float) $deductions->where('transaction_type', 'given')->sum('amount');
            $appliedDeductions = (float) $deductions->where('transaction_type', 'given')->where('status', '!=', 'pending')->sum('amount');
            $pendingDeductions = (float) $deductions->where('transaction_type', 'given')->where('status', 'pending')->sum('amount');
        }

        // TAB 4: PRODUCT SALES DATA
        $products = collect();
        $farmerProductSales = collect();
        $totalProductPurchases = 0.00;
        $totalProductPaid = 0.00;
        if (Schema::hasTable('products')) {
            $products = Product::where('status', 1)->orWhere('in_stock', 1)->orderBy('name')->get();
        }
        if (Schema::hasTable('product_sales')) {
            $farmerProductSales = ProductSale::with('product')
                ->where(function ($q) use ($farmer) {
                    $q->where('farmer_id', $farmer->id);
                    if (!empty($farmer->farmer_code)) {
                        $q->orWhere('farmer_code', $farmer->farmer_code);
                    }
                })
                ->latest('sale_date')
                ->latest('id')
                ->get();
            $totalProductPurchases = (float) $farmerProductSales->sum('total_amount');
            $totalProductPaid = (float) $farmerProductSales->sum('paid_amount');
        }

        // TAB 5: EMERGENCY FUND DATA
        $emergencyList = collect();
        $totalEmergencyAmount = 0.00;
        $totalEmergencyRecovered = 0.00;
        if (Schema::hasTable('farmer_emergency_funds')) {
            $emergencyList = FarmerEmergencyFund::where('farmer_id', $farmer->id)
                ->latest('request_date')
                ->latest('id')
                ->get();
            $totalEmergencyAmount = (float) $emergencyList->sum('amount');
            $totalEmergencyRecovered = (float) $emergencyList->sum('recovered_amount');
        }

        // TAB 6: INVOICES & SETTLEMENTS
        $invoices = collect();
        if (Schema::hasTable('farmer_invoices')) {
            $invoices = FarmerInvoice::where('farmer_id', $farmer->id)
                ->latest('period_end')
                ->latest('id')
                ->get();
        }

        // TAB 7 & 8: STATEMENT & ALL PASSBOOK DATA (Chronological unified reconciliation)
        $fromDate = $request->input('stmt_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('stmt_to', Carbon::now()->format('Y-m-d'));
        $passbookFilterType = $request->input('passbook_type', 'all');

        $reconciledPassbook = $this->buildReconciledPassbook($farmer, $fromDate, $toDate, $passbookFilterType);

        return [
            'totalMilkQuantity' => $totalMilkQuantity,
            'currentPayableBalance' => $currentPayableBalance,
            'outstandingLoan' => $outstandingLoan,
            'productCreditBalance' => $productCreditBalance,
            'emergencyBalance' => $emergencyBalance,
            'resolvedCowRate' => $resolvedCowRate,
            'resolvedBuffaloRate' => $resolvedBuffaloRate,
            'recentCollections' => $recentCollections,
            'todayMorningLitres' => $todayMorningLitres,
            'todayEveningLitres' => $todayEveningLitres,
            'loans' => $loans,
            'totalLoanAmount' => $totalLoanAmount,
            'totalLoanRepaid' => $totalLoanRepaid,
            'deductions' => $deductions,
            'totalDeductions' => $totalDeductions,
            'appliedDeductions' => $appliedDeductions,
            'pendingDeductions' => $pendingDeductions,
            'products' => $products,
            'farmerProductSales' => $farmerProductSales,
            'totalProductPurchases' => $totalProductPurchases,
            'totalProductPaid' => $totalProductPaid,
            'emergencyList' => $emergencyList,
            'totalEmergencyAmount' => $totalEmergencyAmount,
            'totalEmergencyRecovered' => $totalEmergencyRecovered,
            'invoices' => $invoices,
            'stmtFrom' => $fromDate,
            'stmtTo' => $toDate,
            'passbookFilterType' => $passbookFilterType,
            'passbookEntries' => $reconciledPassbook['entries'],
            'openingBalance' => $reconciledPassbook['openingBalance'],
            'totalCredit' => $reconciledPassbook['totalCredit'],
            'totalDebit' => $reconciledPassbook['totalDebit'],
            'closingBalance' => $reconciledPassbook['closingBalance'],
        ];
    }

    /**
     * Resolve Milk Rate using active Rate Chart or Farmer Custom Override.
     */
    public function resolveMilkRate(Farmer $farmer, string $milkType, float $fat, float $snf, float $clr = 0): float
    {
        $milkType = strtolower($milkType);

        // 1. Farmer Fixed Rate Override
        if ($milkType === 'cow' && (float) $farmer->cow_milk_rate > 0) {
            return (float) $farmer->cow_milk_rate;
        }
        if ($milkType === 'buffalo' && (float) $farmer->buffalo_milk_rate > 0) {
            return (float) $farmer->buffalo_milk_rate;
        }
        if ((float) $farmer->custom_rate_override > 0) {
            return (float) $farmer->custom_rate_override;
        }

        // 2. Active Rate Chart
        $chart = null;
        if ($farmer->rate_chart_id) {
            $chart = RateChart::where('id', $farmer->rate_chart_id)->where('status', 'active')->first();
        }
        if (!$chart) {
            $chart = RateChart::where('milk_type', $milkType)->where('status', 'active')->first();
        }
        if (!$chart) {
            $chart = RateChart::where('status', 'active')->first();
        }

        if ($chart) {
            try {
                $calculated = $chart->calculateRate($fat, $snf, $clr);
                if ($calculated > 0) {
                    return (float) $calculated;
                }
            } catch (\Throwable $e) {
                // Ignore and try direct slab query
            }

            try {
                if (Schema::hasTable('rate_chart_slabs')) {
                    $slabQuery = DB::table('rate_chart_slabs')->where('rate_chart_id', $chart->id);
                    if (Schema::hasColumn('rate_chart_slabs', 'fat_from')) {
                        $slabQuery->where('fat_from', '<=', $fat)->where('fat_to', '>=', $fat);
                    } elseif (Schema::hasColumn('rate_chart_slabs', 'fat_min')) {
                        $slabQuery->where('fat_min', '<=', $fat)->where('fat_max', '>=', $fat);
                    }

                    if (Schema::hasColumn('rate_chart_slabs', 'snf_from')) {
                        $slabQuery->where('snf_from', '<=', $snf)->where('snf_to', '>=', $snf);
                    } elseif (Schema::hasColumn('rate_chart_slabs', 'snf_min')) {
                        $slabQuery->where('snf_min', '<=', $snf)->where('snf_max', '>=', $snf);
                    }

                    $slab = $slabQuery->first();
                    if ($slab && isset($slab->rate) && (float) $slab->rate > 0) {
                        return (float) $slab->rate;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore and fall through to fallback formula
            }
        }

        // 3. Fallback Standard Dairy Formula
        if ($milkType === 'buffalo') {
            return round(($fat * 7.5) + ($snf * 4.2), 2);
        }
        return round(($fat * 6.8) + ($snf * 3.8), 2);
    }

    /**
     * Live AJAX Rate Calculation for Milk Entry Form
     */
    public function calculateRate(Request $request)
    {
        $farmer = Farmer::findOrFail($request->farmer_id);
        $milkType = strtolower($request->milk_type ?: $farmer->animal_type ?: 'cow');
        $quantity = (float) $request->quantity;
        $fat = (float) $request->fat;
        $snf = (float) $request->snf;
        $clr = (float) $request->clr;

        if ($snf <= 0 && $clr > 0 && $fat > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        }

        $rate = $this->resolveMilkRate($farmer, $milkType, $fat, $snf, $clr);
        $amount = round($quantity * $rate, 2);

        return response()->json([
            'success' => true,
            'rate' => $rate,
            'snf' => $snf,
            'amount' => $amount,
        ]);
    }

    /**
     * Store Daily Milk Entry (Duplicate prevention, ledger credit, audit log)
     */
    public function storeMilkEntry(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat' => 'required|numeric|min:0.5|max:20',
            'snf' => 'nullable|numeric|min:0|max:20',
            'clr' => 'nullable|numeric|min:0|max:50',
            'rate' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'allow_duplicate' => 'nullable|boolean',
        ]);

        $date = $validated['collection_date'];
        $shift = $validated['shift'];

        // Duplicate entry check
        if (empty($validated['allow_duplicate'])) {
            $existing = MilkCollection::where('farmer_id', $farmer->id)
                ->whereDate('collection_date', $date)
                ->where('shift', $shift)
                ->first();

            if ($existing) {
                return back()->withInput()->with('error', "Duplicate Entry Alert: {$farmer->name} already has a milk collection entry for {$shift} on {$date} (Receipt #{$existing->receipt_number}).");
            }
        }

        $fat = (float) $validated['fat'];
        $clr = (float) ($validated['clr'] ?? 0);
        $snf = (float) ($validated['snf'] ?? 0);
        if ($snf <= 0 && $clr > 0 && $fat > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        }

        $calcRate = $this->resolveMilkRate($farmer, $validated['milk_type'], $fat, $snf, $clr);
        $appliedRate = (!empty($validated['rate']) && (float) $validated['rate'] > 0) ? (float) $validated['rate'] : $calcRate;
        $qty = (float) $validated['quantity_liters'];
        $netAmount = round($qty * $appliedRate, 2);

        $receiptNumber = 'MC-' . strtoupper(substr($validated['shift'], 0, 1)) . '-' . date('ymd', strtotime($date)) . '-' . str_pad((MilkCollection::max('id') + 1), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $collection = MilkCollection::create([
                'receipt_number' => $receiptNumber,
                'farmer_id' => $farmer->id,
                'collection_center_id' => $farmer->collection_center_id,
                'collection_date' => $date,
                'shift' => $shift,
                'milk_type' => $validated['milk_type'],
                'quantity_liters' => $qty,
                'fat' => $fat,
                'snf' => $snf,
                'clr' => $clr > 0 ? $clr : null,
                'calculated_rate' => $calcRate,
                'applied_rate' => $appliedRate,
                'gross_amount' => $netAmount,
                'bonus' => 0.00,
                'deduction' => 0.00,
                'net_amount' => $netAmount,
                'payment_status' => 'pending',
                'notes' => $validated['remarks'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            // Adjust Farmer Ledger & Current Balance
            $newBalance = round((float) $farmer->current_balance + $netAmount, 2);
            $farmer->update(['current_balance' => $newBalance]);

            FarmerLedger::create([
                'farmer_id' => $farmer->id,
                'transaction_date' => $date,
                'type' => 'credit',
                'amount' => $netAmount,
                'balance' => $newBalance,
                'reference_type' => 'milk_collection',
                'reference_id' => $collection->id,
                'description' => "Milk Supply (" . ucfirst($shift) . ") {$qty} Ltr @ ₹{$appliedRate}/Ltr [{$receiptNumber}]",
            ]);

            AuditLog::log('Created Mobile Dairy Milk Entry', 'MilkCollection', $collection->id, [
                'farmer' => $farmer->name,
                'receipt' => $receiptNumber,
                'amount' => $netAmount,
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'milk_entry'])
                             ->with('success', "Milk entry recorded successfully! Receipt #{$receiptNumber} — ₹{$netAmount}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', "Failed to record milk entry: " . $e->getMessage());
        }
    }

    /**
     * Correct/Update an existing Milk Entry with Authorization Audit
     */
    public function updateMilkEntry(Request $request, MilkCollection $collection)
    {
        $validated = $request->validate([
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat' => 'required|numeric|min:0.5|max:20',
            'snf' => 'nullable|numeric|min:0|max:20',
            'clr' => 'nullable|numeric|min:0|max:50',
            'applied_rate' => 'required|numeric|min:0',
            'correction_reason' => 'required|string|max:500',
        ]);

        $farmer = $collection->farmer;
        $oldNet = (float) $collection->net_amount;

        $qty = (float) $validated['quantity_liters'];
        $rate = (float) $validated['applied_rate'];
        $newNet = round($qty * $rate, 2);
        $diff = round($newNet - $oldNet, 2);

        DB::beginTransaction();
        try {
            $collection->update([
                'quantity_liters' => $qty,
                'fat' => (float) $validated['fat'],
                'snf' => (float) ($validated['snf'] ?? $collection->snf),
                'clr' => !empty($validated['clr']) ? (float) $validated['clr'] : $collection->clr,
                'applied_rate' => $rate,
                'gross_amount' => $newNet,
                'net_amount' => $newNet,
                'notes' => ($collection->notes ? $collection->notes . " | " : "") . "Corrected: " . $validated['correction_reason'],
            ]);

            // Adjust Farmer Balance
            $newFarmerBalance = round((float) $farmer->current_balance + $diff, 2);
            $farmer->update(['current_balance' => $newFarmerBalance]);

            // Adjust Ledger
            $ledger = FarmerLedger::where('reference_type', 'milk_collection')->where('reference_id', $collection->id)->first();
            if ($ledger) {
                $ledger->update([
                    'amount' => $newNet,
                    'balance' => $newFarmerBalance,
                    'description' => "Milk Supply (" . ucfirst($collection->shift) . ") {$qty} Ltr @ ₹{$rate}/Ltr [Corrected: {$validated['correction_reason']}]",
                ]);
            }

            AuditLog::log('Corrected Milk Collection Entry', 'MilkCollection', $collection->id, [
                'farmer' => $farmer->name,
                'old_amount' => $oldNet,
                'new_amount' => $newNet,
                'reason' => $validated['correction_reason'],
                'authorized_by' => Auth::user()->name ?? 'Admin',
            ]);

            DB::commit();

            return back()->with('success', "Milk entry #{$collection->receipt_number} corrected successfully! Difference: ₹{$diff}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to correct entry: " . $e->getMessage());
        }
    }

    /**
     * Give a new Loan / Advance to Farmer
     */
    public function storeLoan(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'advance_date' => 'required|date',
            'purpose' => 'required|string|max:255',
            'voucher_no' => 'nullable|string|max:50',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Cheque',
            'notes' => 'nullable|string|max:500',
        ]);

        $voucher = !empty($validated['voucher_no']) ? $validated['voucher_no'] : 'LN-' . str_pad((FarmerAdvance::max('id') + 1), 4, '0', STR_PAD_LEFT);
        $amount = (float) $validated['amount'];

        DB::beginTransaction();
        try {
            $loan = FarmerAdvance::create([
                'farmer_id' => $farmer->id,
                'advance_date' => $validated['advance_date'],
                'voucher_no' => $voucher,
                'amount' => $amount,
                'interest_rate' => (float) ($validated['interest_rate'] ?? 0),
                'purpose' => $validated['purpose'],
                'payment_mode' => $validated['payment_mode'] ?? 'Cash',
                'status' => 'pending',
                'deducted_amount' => 0.00,
                'paid_amount' => 0.00,
                'interest_balance' => 0.00,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Adjust ledger balance
            $newBalance = round((float) $farmer->current_balance - $amount, 2);
            $farmer->update(['current_balance' => $newBalance]);

            FarmerLedger::create([
                'farmer_id' => $farmer->id,
                'transaction_date' => $validated['advance_date'],
                'type' => 'debit',
                'amount' => $amount,
                'balance' => $newBalance,
                'reference_type' => 'advance',
                'reference_id' => $loan->id,
                'description' => "Loan/Advance Issued [Voucher #{$voucher}]: {$validated['purpose']}",
            ]);

            AuditLog::log('Issued Farmer Loan/Advance', 'FarmerAdvance', $loan->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'voucher' => $voucher,
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'loan'])
                             ->with('success', "Loan/Advance of ₹{$amount} issued successfully (Voucher #{$voucher})!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to issue loan: " . $e->getMessage());
        }
    }

    /**
     * Record Loan Repayment
     */
    public function storeLoanRepayment(Request $request, FarmerAdvance $loan)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'repayment_date' => 'required|date',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Milk Deduction',
            'remark' => 'nullable|string|max:500',
        ]);

        $farmer = $loan->farmer;
        $repayAmount = (float) $validated['amount'];
        $remainingPrincipal = (float) $loan->principal_balance;

        if ($repayAmount > $remainingPrincipal + 0.01) {
            return back()->with('error', "Repayment amount (₹{$repayAmount}) cannot exceed outstanding principal (₹{$remainingPrincipal}).");
        }

        DB::beginTransaction();
        try {
            $repayment = FarmerAdvanceRepayment::create([
                'farmer_advance_id' => $loan->id,
                'farmer_id' => $farmer->id,
                'repayment_date' => $validated['repayment_date'],
                'amount' => $repayAmount,
                'payment_mode' => $validated['payment_mode'] ?? 'Cash',
                'remark' => $validated['remark'] ?? "Loan Repayment for Voucher #{$loan->voucher_no}",
                'recorded_by' => Auth::id(),
            ]);

            $newPaid = round((float) $loan->paid_amount + $repayAmount, 2);
            $newStatus = ($newPaid >= (float) $loan->amount) ? 'recovered' : 'partially_recovered';
            $loan->update([
                'paid_amount' => $newPaid,
                'paid_date' => $validated['repayment_date'],
                'status' => $newStatus,
            ]);

            // Adjust ledger balance if repaid via Cash/Bank
            if ($validated['payment_mode'] !== 'Milk Deduction') {
                $newBalance = round((float) $farmer->current_balance + $repayAmount, 2);
                $farmer->update(['current_balance' => $newBalance]);

                FarmerLedger::create([
                    'farmer_id' => $farmer->id,
                    'transaction_date' => $validated['repayment_date'],
                    'type' => 'credit',
                    'amount' => $repayAmount,
                    'balance' => $newBalance,
                    'reference_type' => 'advance_repayment',
                    'reference_id' => $repayment->id,
                    'description' => "Loan Repayment Received for Voucher #{$loan->voucher_no} ({$validated['payment_mode']})",
                ]);
            }

            AuditLog::log('Recorded Loan Repayment', 'FarmerAdvanceRepayment', $repayment->id, [
                'farmer' => $farmer->name,
                'amount' => $repayAmount,
                'voucher' => $loan->voucher_no,
            ]);

            DB::commit();

            return back()->with('success', "Repayment of ₹{$repayAmount} recorded successfully!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to record repayment: " . $e->getMessage());
        }
    }

    /**
     * Add a Farmer Deduction (Cattle feed, medicine, veterinary, etc.)
     */
    public function storeDeduction(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'deduction_type' => 'required|string',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric|min:0.5',
            'comments' => 'nullable|string|max:500',
            'reference_no' => 'nullable|string|max:50',
            'status' => 'nullable|in:applied,pending',
        ]);

        $amount = (float) $validated['amount'];
        $status = $validated['status'] ?? 'applied';

        DB::beginTransaction();
        try {
            $deduction = FarmerDeduction::create([
                'farmer_id' => $farmer->id,
                'entry_date' => $validated['entry_date'],
                'deduction_type' => $validated['deduction_type'],
                'transaction_type' => 'given',
                'amount' => $amount,
                'comments' => $validated['comments'] ?? null,
                'reference_no' => $validated['reference_no'] ?? ('DED-' . time()),
                'status' => $status,
                'created_by' => Auth::id(),
            ]);

            // Adjust farmer balance if applied
            if ($status === 'applied') {
                $newBalance = round((float) $farmer->current_balance - $amount, 2);
                $farmer->update(['current_balance' => $newBalance]);

                FarmerLedger::create([
                    'farmer_id' => $farmer->id,
                    'transaction_date' => $validated['entry_date'],
                    'type' => 'debit',
                    'amount' => $amount,
                    'balance' => $newBalance,
                    'reference_type' => 'deduction',
                    'reference_id' => $deduction->id,
                    'description' => "Deduction: " . ($deduction->type_label ?? $validated['deduction_type']) . ($deduction->comments ? " — {$deduction->comments}" : ""),
                ]);
            }

            AuditLog::log('Created Farmer Deduction', 'FarmerDeduction', $deduction->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'type' => $validated['deduction_type'],
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'deduction'])
                             ->with('success', "Deduction of ₹{$amount} recorded successfully!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to record deduction: " . $e->getMessage());
        }
    }

    /**
     * Record Product Sale to Farmer (Cattle feed, mineral mixture, etc.)
     */
    public function storeProductSale(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sale_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'payment_mode' => 'required|string|in:Cash,UPI,Bank Transfer,Ledger',
            'is_paid' => 'required|in:0,1',
            'remarks' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (float) $validated['quantity'];
        $rate = (float) $validated['rate'];
        $total = round($qty * $rate, 2);
        $isPaid = (bool) $validated['is_paid'];
        $paidAmount = $isPaid ? $total : 0.00;
        $remainingAmount = round($total - $paidAmount, 2);

        // Check stock
        if ((float) $product->current_stock < $qty) {
            return back()->withInput()->with('error', "Insufficient stock for {$product->name}. In stock: {$product->current_stock}");
        }

        DB::beginTransaction();
        try {
            $saleNumber = 'SL-' . date('Ymd') . '-' . str_pad((ProductSale::max('id') + 1), 4, '0', STR_PAD_LEFT);

            $sale = ProductSale::create([
                'sale_number' => $saleNumber,
                'sale_date' => $validated['sale_date'],
                'farmer_id' => $farmer->id,
                'farmer_code' => $farmer->farmer_code,
                'farmer_name' => $farmer->name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit' => $product->unit ?: 'Bag',
                'quantity' => $qty,
                'rate' => $rate,
                'total_amount' => $total,
                'is_paid' => $isPaid,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_mode' => $validated['payment_mode'],
                'remarks' => $validated['remarks'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            // Deduct stock
            $newStock = max(0, (float) $product->current_stock - $qty);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);

            // If credit / ledger sale, update farmer balance
            if (!$isPaid || $validated['payment_mode'] === 'Ledger') {
                $newBalance = round((float) $farmer->current_balance - $remainingAmount, 2);
                $farmer->update(['current_balance' => $newBalance]);

                FarmerLedger::create([
                    'farmer_id' => $farmer->id,
                    'transaction_date' => $validated['sale_date'],
                    'type' => 'debit',
                    'amount' => $remainingAmount,
                    'balance' => $newBalance,
                    'reference_type' => 'product_sale',
                    'reference_id' => $sale->id,
                    'description' => "Product Purchase #{$saleNumber}: {$product->name} ({$qty} {$product->unit} @ ₹{$rate})",
                ]);
            }

            AuditLog::log('Created Mobile Dairy Product Sale', 'ProductSale', $sale->id, [
                'farmer' => $farmer->name,
                'product' => $product->name,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'product'])
                             ->with('success', "Product sale recorded successfully! Bill #{$saleNumber} — ₹{$total}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to record product sale: " . $e->getMessage());
        }
    }

    /**
     * Create Emergency Fund Assistance Request or Disbursement
     */
    public function storeEmergencyFund(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'request_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'status' => 'required|in:approved,disbursed,pending',
            'payment_mode' => 'nullable|string|in:Cash,Bank Transfer,UPI',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $validated['amount'];
        $reqNo = 'EMG-' . date('ymd') . '-' . str_pad((FarmerEmergencyFund::max('id') + 1), 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $fund = FarmerEmergencyFund::create([
                'farmer_id' => $farmer->id,
                'request_no' => $reqNo,
                'request_date' => $validated['request_date'],
                'amount' => $amount,
                'reason' => $validated['reason'],
                'status' => $validated['status'],
                'approved_by' => Auth::id(),
                'disbursed_at' => $validated['status'] === 'disbursed' ? now() : null,
                'recovered_amount' => 0.00,
                'payment_mode' => $validated['payment_mode'] ?? 'Cash',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Note: By dairy cooperative accounting rules, emergency funds are kept separate from standard loans
            // and do not debit the milk procurement ledger unless linked to a settlement.

            AuditLog::log('Created Emergency Fund Request', 'FarmerEmergencyFund', $fund->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'request_no' => $reqNo,
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'emergency_fund'])
                             ->with('success', "Emergency assistance request #{$reqNo} recorded successfully (₹{$amount})!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to record emergency fund: " . $e->getMessage());
        }
    }

    /**
     * Record Recovery for an Emergency Fund Assistance
     */
    public function recordEmergencyRecovery(Request $request, FarmerEmergencyFund $emergencyFund)
    {
        $validated = $request->validate([
            'recovered_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $recovery = (float) $validated['recovered_amount'];
        $outstanding = (float) $emergencyFund->outstanding_amount;

        if ($recovery > $outstanding + 0.01) {
            return back()->with('error', "Recovery amount (₹{$recovery}) cannot exceed outstanding emergency balance (₹{$outstanding}).");
        }

        $newRecovered = round((float) $emergencyFund->recovered_amount + $recovery, 2);
        $emergencyFund->update([
            'recovered_amount' => $newRecovered,
            'notes' => ($emergencyFund->notes ? $emergencyFund->notes . " | " : "") . "Recovered ₹{$recovery} on " . date('d M Y'),
        ]);

        AuditLog::log('Recovered Emergency Fund Amount', 'FarmerEmergencyFund', $emergencyFund->id, [
            'recovered' => $recovery,
            'total_recovered' => $newRecovered,
        ]);

        return back()->with('success', "Emergency fund recovery of ₹{$recovery} recorded successfully!");
    }

    /**
     * Generate Period Settlement Invoice for Farmer (Cow/Buffalo breakdown, deductions, loans, purchases)
     */
    public function generateInvoice(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->get();

        $cowCollections = $collections->where('milk_type', 'cow');
        $buffCollections = $collections->where('milk_type', 'buffalo');

        $totalQty = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum('net_amount'), 2);

        // Deductions
        $feedDeduction = 0.00;
        $stationaryDeduction = 0.00;
        $loanRecovery = 0.00;
        $totalCredit = 0.00;

        if (Schema::hasTable('farmer_deductions')) {
            $deds = FarmerDeduction::where('farmer_id', $farmer->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->get();
            $feedDeduction = round((float) $deds->where('transaction_type', 'given')->where('deduction_type', 'cattle_feed')->sum('amount'), 2);
            $stationaryDeduction = round((float) $deds->where('transaction_type', 'given')->where('deduction_type', '!=', 'cattle_feed')->sum('amount'), 2);
            $totalCredit = round((float) $deds->where('transaction_type', 'received')->sum('amount'), 2);
        }

        // Product purchases due
        if (Schema::hasTable('product_sales')) {
            $unpaidSales = ProductSale::where('farmer_id', $farmer->id)
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->sum('remaining_amount');
            $feedDeduction = round($feedDeduction + (float) $unpaidSales, 2);
        }

        // Loan recovery
        if (Schema::hasTable('farmer_advances')) {
            $advs = FarmerAdvance::where('farmer_id', $farmer->id)
                ->whereBetween('advance_date', [$startDate, $endDate])
                ->where('status', '!=', 'recovered')
                ->get();
            $loanRecovery = round((float) $advs->sum(fn($a) => $a->principal_balance), 2);
        }

        $totalDeduction = round($feedDeduction + $stationaryDeduction + $loanRecovery, 2);
        $totalAmount = round($milkAmount + $totalCredit, 2);
        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);
        $netPayment = round(max(0, $totalAmount - $totalDeduction), 2);

        $invNum = 'INV-' . date('Ymd') . '-' . str_pad((FarmerInvoice::max('id') + 101), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $invoice = FarmerInvoice::create([
                'invoice_number' => $invNum,
                'farmer_id' => $farmer->id,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'total_quantity' => $totalQty,
                'milk_amount' => $milkAmount,
                'stationary_deduction' => $stationaryDeduction,
                'feed_deduction' => $feedDeduction,
                'advance_deduction' => $loanRecovery,
                'other_deduction' => 0.00,
                'total_deduction' => $totalDeduction,
                'total_credit' => $totalCredit,
                'total_amount' => $totalAmount,
                'previous_balance' => $previousBalance,
                'net_payment' => $netPayment,
                'status' => 'pending', // Generating an invoice does not automatically mark as paid
                'is_edited' => false,
                'created_by' => Auth::id(),
            ]);

            AuditLog::log('Generated Farmer Settlement Invoice', 'FarmerInvoice', $invoice->id, [
                'farmer' => $farmer->name,
                'invoice' => $invNum,
                'net_payment' => $netPayment,
            ]);

            DB::commit();

            return redirect()->route('farmers.mobile-dairy', ['farmer' => $farmer->id, 'tab' => 'invoice'])
                             ->with('success', "Settlement Invoice #{$invNum} generated successfully! Net Payable: ₹{$netPayment}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to generate invoice: " . $e->getMessage());
        }
    }

    /**
     * Mark an Invoice / Settlement as Paid (with payment method & reference)
     */
    public function markInvoicePaid(Request $request, FarmerInvoice $invoice)
    {
        $validated = $request->validate([
            'payment_mode' => 'required|string|in:Bank Transfer,Cash,UPI,Cheque',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('error', "Invoice #{$invoice->invoice_number} is already marked as paid.");
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status' => 'paid',
                'payment_mode' => $validated['payment_mode'],
                'payment_reference' => $validated['payment_reference'] ?? null,
            ]);

            // Settle related milk collections
            MilkCollection::where('farmer_id', $invoice->farmer_id)
                ->whereBetween('collection_date', [$invoice->period_start, $invoice->period_end])
                ->update(['payment_status' => 'settled']);

            AuditLog::log('Marked Farmer Settlement Invoice as Paid', 'FarmerInvoice', $invoice->id, [
                'invoice' => $invoice->invoice_number,
                'amount' => $invoice->net_payment,
                'mode' => $validated['payment_mode'],
            ]);

            DB::commit();

            return back()->with('success', "Invoice #{$invoice->invoice_number} marked as PAID via {$validated['payment_mode']}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', "Failed to mark invoice as paid: " . $e->getMessage());
        }
    }

    /**
     * Printable Slip for Farmer Invoice
     */
    public function printSlip(FarmerInvoice $invoice)
    {
        $invoice->load(['farmer', 'creator']);
        $farmer = $invoice->farmer;
        $collections = $invoice->milk_collections;
        $deductions = $invoice->deductions_list;

        return view('mobile_dairy.invoice_slip', compact('invoice', 'farmer', 'collections', 'deductions'));
    }

    /**
     * Build unified, reconciled Passbook transactions across all source records.
     */
    protected function buildReconciledPassbook(Farmer $farmer, string $fromDate, string $toDate, string $filterType = 'all'): array
    {
        $rawEntries = [];
        $seq = 0;

        // 1. Milk Collections (Credit)
        try {
            $milkCols = MilkCollection::where('farmer_id', $farmer->id)
                ->orderBy('collection_date')
                ->orderBy('id')
                ->get();
            foreach ($milkCols as $c) {
                $dt = $c->collection_date ? $c->collection_date->format('Y-m-d') : $c->created_at->format('Y-m-d');
                $rawEntries[] = [
                    'date' => $dt,
                    'seq' => ++$seq,
                    'ref' => $c->receipt_number,
                    'type' => 'milk',
                    'type_label' => 'Milk Supply',
                    'description' => "Milk Supply (" . ucfirst($c->shift) . ") — {$c->quantity_liters} Ltr (" . ucfirst($c->milk_type) . ", FAT {$c->fat}% / SNF {$c->snf}%) @ ₹" . number_format((float) $c->applied_rate, 2),
                    'status' => ucfirst($c->payment_status ?: 'settled'),
                    'credit' => round((float) $c->net_amount, 2),
                    'debit' => 0.00,
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Loans / Advances (Debit for issue, Credit for repayment)
        try {
            if (Schema::hasTable('farmer_advances')) {
                $advs = FarmerAdvance::where('farmer_id', $farmer->id)->orderBy('advance_date')->get();
                foreach ($advs as $a) {
                    $dt = $a->advance_date ? $a->advance_date->format('Y-m-d') : $a->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $a->voucher_no ?: "ADV-{$a->id}",
                        'type' => 'loan',
                        'type_label' => 'Loan / Advance',
                        'description' => "Loan/Advance Issued: {$a->purpose}",
                        'status' => ucfirst($a->status),
                        'credit' => 0.00,
                        'debit' => round((float) $a->amount, 2),
                    ];

                    // Repayments
                    $paid = (float) $a->paid_amount;
                    if ($paid > 0) {
                        $pDate = $a->paid_date ? $a->paid_date->format('Y-m-d') : $dt;
                        $rawEntries[] = [
                            'date' => $pDate,
                            'seq' => ++$seq,
                            'ref' => "REP-{$a->voucher_no}",
                            'type' => 'loan',
                            'type_label' => 'Loan Repayment',
                            'description' => "Loan Repayment for Voucher #{$a->voucher_no}",
                            'status' => 'Received',
                            'credit' => $paid,
                            'debit' => 0.00,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Deductions (Debit)
        try {
            if (Schema::hasTable('farmer_deductions')) {
                $deds = FarmerDeduction::where('farmer_id', $farmer->id)->orderBy('entry_date')->get();
                foreach ($deds as $d) {
                    $dt = $d->entry_date ? $d->entry_date->format('Y-m-d') : $d->created_at->format('Y-m-d');
                    $isCred = strtolower($d->transaction_type ?? '') === 'received';
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $d->reference_no ?: "DED-{$d->id}",
                        'type' => 'deduction',
                        'type_label' => 'Deduction',
                        'description' => "Deduction: " . ($d->type_label ?? $d->deduction_type) . ($d->comments ? " — {$d->comments}" : ""),
                        'status' => ucfirst($d->status ?: 'applied'),
                        'credit' => $isCred ? round((float) $d->amount, 2) : 0.00,
                        'debit' => $isCred ? 0.00 : round((float) $d->amount, 2),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 4. Product Sales (Debit for sale, Credit for payment)
        try {
            if (Schema::hasTable('product_sales')) {
                $sales = ProductSale::where(function ($q) use ($farmer) {
                    $q->where('farmer_id', $farmer->id);
                    if (!empty($farmer->farmer_code)) $q->orWhere('farmer_code', $farmer->farmer_code);
                })->orderBy('sale_date')->get();

                foreach ($sales as $s) {
                    $dt = $s->sale_date ? $s->sale_date->format('Y-m-d') : $s->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $s->sale_number,
                        'type' => 'product',
                        'type_label' => 'Product Purchase',
                        'description' => "Product Purchase: {$s->product_name} ({$s->quantity} {$s->unit} @ ₹{$s->rate})",
                        'status' => $s->is_paid ? 'Paid' : 'Unpaid',
                        'credit' => 0.00,
                        'debit' => round((float) $s->total_amount, 2),
                    ];

                    if ((float) $s->paid_amount > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "RCPT-{$s->sale_number}",
                            'type' => 'product',
                            'type_label' => 'Product Payment',
                            'description' => "Payment for Product #{$s->sale_number} ({$s->payment_mode})",
                            'status' => 'Received',
                            'credit' => round((float) $s->paid_amount, 2),
                            'debit' => 0.00,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 5. Emergency Funds
        try {
            if (Schema::hasTable('farmer_emergency_funds')) {
                $efs = FarmerEmergencyFund::where('farmer_id', $farmer->id)->orderBy('request_date')->get();
                foreach ($efs as $ef) {
                    $dt = $ef->request_date ? $ef->request_date->format('Y-m-d') : $ef->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $ef->request_no,
                        'type' => 'emergency',
                        'type_label' => 'Emergency Assistance',
                        'description' => "Emergency Assistance: {$ef->reason}",
                        'status' => ucfirst($ef->status),
                        'credit' => 0.00,
                        'debit' => round((float) $ef->amount, 2),
                    ];
                    if ((float) $ef->recovered_amount > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "REC-{$ef->request_no}",
                            'type' => 'emergency',
                            'type_label' => 'Emergency Recovery',
                            'description' => "Emergency Assistance Recovery for #{$ef->request_no}",
                            'status' => 'Recovered',
                            'credit' => round((float) $ef->recovered_amount, 2),
                            'debit' => 0.00,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 6. Settlements (Debit for payout)
        try {
            if (Schema::hasTable('farmer_settlements')) {
                $stls = FarmerSettlement::where('farmer_id', $farmer->id)->orderBy('period_end')->get();
                foreach ($stls as $st) {
                    $dt = $st->settled_at ? $st->settled_at->format('Y-m-d') : ($st->period_end ? $st->period_end->format('Y-m-d') : $st->created_at->format('Y-m-d'));
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $st->settlement_number,
                        'type' => 'settlement',
                        'type_label' => 'Settlement Payout',
                        'description' => "Settlement Payout via " . ucfirst($st->payment_mode ?? 'Bank'),
                        'status' => 'Settled',
                        'credit' => 0.00,
                        'debit' => round((float) ($st->paid_amount ?: $st->net_payable), 2),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // Sort chronologically
        usort($rawEntries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return $a['seq'] <=> $b['seq'];
            }
            return strcmp($a['date'], $b['date']);
        });

        // Compute Opening Balance strictly prior to $fromDate
        $openingBalance = 0.00;
        $filtered = [];

        foreach ($rawEntries as $entry) {
            if ($entry['date'] < $fromDate) {
                $openingBalance = round($openingBalance + $entry['credit'] - $entry['debit'], 2);
                continue;
            }
            if ($entry['date'] > $toDate) {
                continue;
            }
            if ($filterType !== 'all' && $entry['type'] !== $filterType) {
                continue;
            }
            $filtered[] = $entry;
        }

        // Running balance for filtered entries
        $runningBalance = $openingBalance;
        $totalCredit = 0.00;
        $totalDebit = 0.00;

        foreach ($filtered as &$f) {
            $runningBalance = round($runningBalance + $f['credit'] - $f['debit'], 2);
            $totalCredit = round($totalCredit + $f['credit'], 2);
            $totalDebit = round($totalDebit + $f['debit'], 2);
            $f['running_balance'] = $runningBalance;
            $f['balance'] = $runningBalance;
        }

        return [
            'openingBalance' => $openingBalance,
            'entries' => $filtered,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'closingBalance' => $runningBalance,
        ];
    }
}
