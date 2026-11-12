<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MobileDairyController;
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
use App\Models\SystemSetting;

class MobileDairyApiController extends Controller
{
    protected MobileDairyController $webController;

    public function __construct()
    {
        $this->webController = new MobileDairyController();
    }

    /**
     * Resolve Farmer safely by ID or Farmer Code
     */
    protected function resolveFarmer($id): ?Farmer
    {
        if (is_numeric($id)) {
            $farmer = Farmer::find((int) $id);
            if ($farmer) return $farmer;
        }
        return Farmer::where('farmer_code', $id)->first();
    }

    /**
     * 1. GET /api/v1/procurement/mobile-dairy/farmers
     * Standalone Mobile Dairy Hub / Farmer Switcher list with KPIs
     */
    public function farmersHub(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $query = Farmer::with(['collectionCenter', 'route']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->input('per_page', 24), 1), 100);
        $farmers = $query->orderBy('name')->paginate($perPage);

        $totalFarmers = Farmer::count();
        $activeFarmers = Farmer::where('status', 'active')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Mobile Dairy farmers fetched successfully',
            'data' => [
                'total_farmers' => $totalFarmers,
                'active_farmers' => $activeFarmers,
                'search' => $search,
                'farmers' => $farmers->items(),
            ],
            'meta' => [
                'current_page' => $farmers->currentPage(),
                'per_page' => $farmers->perPage(),
                'total' => $farmers->total(),
                'last_page' => $farmers->lastPage(),
            ],
        ]);
    }

    /**
     * 2. GET /api/v1/procurement/mobile-dairy/farmer/{farmer}
     * Complete 8-Tab Mobile Dairy Suite data for a single farmer
     */
    public function farmerSuite(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Farmer not found with ID/Code: ' . $farmerId,
            ], 404);
        }

        $suiteData = $this->webController->buildFarmerSuiteData($farmer, $request);

        // Map collections and objects to clean arrays for mobile JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Mobile Dairy Suite data loaded successfully',
            'data' => [
                'farmer' => $farmer,
                'banner' => [
                    'total_milk_quantity' => $suiteData['totalMilkQuantity'],
                    'current_payable_balance' => $suiteData['currentPayableBalance'],
                    'outstanding_loan' => $suiteData['outstandingLoan'],
                    'product_credit_balance' => $suiteData['productCreditBalance'],
                    'emergency_balance' => $suiteData['emergencyBalance'],
                    'resolved_cow_rate' => $suiteData['resolvedCowRate'],
                    'resolved_buffalo_rate' => $suiteData['resolvedBuffaloRate'],
                ],
                'milk_entry' => [
                    'today_morning_litres' => $suiteData['todayMorningLitres'],
                    'today_evening_litres' => $suiteData['todayEveningLitres'],
                    'recent_collections' => $suiteData['recentCollections'],
                ],
                'loans' => [
                    'total_loan_amount' => $suiteData['totalLoanAmount'],
                    'total_loan_repaid' => $suiteData['totalLoanRepaid'],
                    'outstanding_balance' => $suiteData['outstandingLoan'],
                    'list' => $suiteData['loans'],
                ],
                'deductions' => [
                    'total_deductions' => $suiteData['totalDeductions'],
                    'applied_deductions' => $suiteData['appliedDeductions'],
                    'pending_deductions' => $suiteData['pendingDeductions'],
                    'list' => $suiteData['deductions'],
                ],
                'products' => [
                    'total_product_purchases' => $suiteData['totalProductPurchases'],
                    'total_product_paid' => $suiteData['totalProductPaid'],
                    'credit_balance' => $suiteData['productCreditBalance'],
                    'catalog' => $suiteData['products'],
                    'sales_history' => $suiteData['farmerProductSales'],
                ],
                'emergency' => [
                    'total_emergency_amount' => $suiteData['totalEmergencyAmount'],
                    'total_emergency_recovered' => $suiteData['totalEmergencyRecovered'],
                    'emergency_balance' => $suiteData['emergencyBalance'],
                    'list' => $suiteData['emergencyList'],
                ],
                'invoices' => [
                    'list' => $suiteData['invoices'],
                ],
                'statement' => [
                    'stmt_from' => $suiteData['stmtFrom'],
                    'stmt_to' => $suiteData['stmtTo'],
                    'opening_balance' => $suiteData['openingBalance'],
                    'total_credit' => $suiteData['totalCredit'],
                    'total_debit' => $suiteData['totalDebit'],
                    'closing_balance' => $suiteData['closingBalance'],
                ],
                'passbook' => [
                    'filter_type' => $suiteData['passbookFilterType'],
                    'opening_balance' => $suiteData['openingBalance'],
                    'total_credit' => $suiteData['totalCredit'],
                    'total_debit' => $suiteData['totalDebit'],
                    'closing_balance' => $suiteData['closingBalance'],
                    'entries' => $suiteData['passbookEntries'],
                ],
            ],
        ]);
    }

    /**
     * 3. POST /api/v1/procurement/mobile-dairy/calc-rate
     * Live Rate Calculation for Mobile Milk Entry
     */
    public function calculateRate(Request $request)
    {
        $request->validate([
            'farmer_id' => 'required',
            'milk_type' => 'nullable|string',
            'quantity' => 'nullable|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'snf' => 'nullable|numeric|min:0',
            'clr' => 'nullable|numeric|min:0',
        ]);

        $farmer = $this->resolveFarmer($request->farmer_id);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

        $milkType = strtolower($request->milk_type ?: $farmer->animal_type ?: 'cow');
        $quantity = (float) $request->quantity;
        $fat = (float) $request->fat;
        $snf = (float) $request->snf;
        $clr = (float) $request->clr;

        if ($snf <= 0 && $clr > 0 && $fat > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        }

        $rate = $this->webController->resolveMilkRate($farmer, $milkType, $fat, $snf, $clr);
        $amount = round($quantity * $rate, 2);

        return response()->json([
            'status' => 'success',
            'rate' => $rate,
            'snf' => $snf,
            'quantity' => $quantity,
            'amount' => $amount,
        ]);
    }

    /**
     * 4. POST /api/v1/procurement/mobile-dairy/milk-entry/{farmer}
     * Store Daily Milk Intake for Farmer
     */
    public function storeMilkEntry(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

        $validated = $request->validate([
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening,Morning,Evening',
            'milk_type' => 'required|in:cow,buffalo,mixed,Cow,Buffalo,Mixed',
            'quantity_liters' => 'required|numeric|min:0.01',
            'fat' => 'required|numeric|min:0.5|max:20',
            'snf' => 'nullable|numeric|min:0|max:20',
            'clr' => 'nullable|numeric|min:0|max:50',
            'rate' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'allow_duplicate' => 'nullable|boolean',
        ]);

        $shift = strtolower($validated['shift']);
        $milkType = strtolower($validated['milk_type']);
        $date = $validated['collection_date'];

        // Duplicate check
        if (empty($validated['allow_duplicate'])) {
            $existing = MilkCollection::where('farmer_id', $farmer->id)
                ->whereDate('collection_date', $date)
                ->where('shift', $shift)
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Duplicate Entry Alert: {$farmer->name} already has a milk collection entry for {$shift} on {$date} (Receipt #{$existing->receipt_number}).",
                    'existing_receipt' => $existing->receipt_number,
                ], 422);
            }
        }

        $fat = (float) $validated['fat'];
        $clr = (float) ($validated['clr'] ?? 0);
        $snf = (float) ($validated['snf'] ?? 0);
        if ($snf <= 0 && $clr > 0 && $fat > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        }

        $calcRate = $this->webController->resolveMilkRate($farmer, $milkType, $fat, $snf, $clr);
        $appliedRate = (!empty($validated['rate']) && (float) $validated['rate'] > 0) ? (float) $validated['rate'] : $calcRate;
        $qty = (float) $validated['quantity_liters'];
        $grossAmount = round($qty * $appliedRate, 2);

        $bonus = 0.00;
        $deduction = 0.00;
        try {
            $charges = \App\Services\CenterAdditionDeductionService::calculateForCollection($farmer, $qty, $grossAmount);
            $bonus = $charges['total_additions'] ?? 0.00;
            $deduction = $charges['total_deductions'] ?? 0.00;
        } catch (\Throwable $e) {}

        $netAmount = max(0, round($grossAmount + $bonus - $deduction, 2));

        $receiptNumber = 'MC-' . strtoupper(substr($shift, 0, 1)) . '-' . date('ymd', strtotime($date)) . '-' . str_pad((MilkCollection::max('id') + 1), 4, '0', STR_PAD_LEFT);

        $isFixedPricing = ($farmer->pricing_type === 'fixed_rate') || 
            (empty($farmer->pricing_type) && ($farmer->cow_milk_rate > 0 || $farmer->buffalo_milk_rate > 0) && !$farmer->rate_chart_id);
        $pricingType = $isFixedPricing ? 'fixed_rate' : 'rate_chart';
        $chartId = null;
        if (!$isFixedPricing) {
            if ($farmer->rate_chart_id) {
                $chkChart = RateChart::where('id', $farmer->rate_chart_id)->where('status', 'active')->first();
                if ($chkChart) $chartId = $chkChart->id;
            }
            if (!$chartId) {
                $chkChart = RateChart::where('milk_type', $milkType)->where('status', 'active')->first()
                    ?? RateChart::where('status', 'active')->first();
                $chartId = $chkChart?->id;
            }
        }

        DB::beginTransaction();
        try {
            $collection = MilkCollection::create([
                'receipt_number' => $receiptNumber,
                'farmer_id' => $farmer->id,
                'collection_center_id' => $farmer->collection_center_id,
                'pricing_type' => $pricingType,
                'rate_chart_id' => $chartId,
                'collection_date' => $date,
                'shift' => $shift,
                'milk_type' => $milkType,
                'quantity_liters' => $qty,
                'fat' => $fat,
                'snf' => $snf,
                'clr' => $clr > 0 ? $clr : null,
                'calculated_rate' => $calcRate,
                'applied_rate' => $appliedRate,
                'gross_amount' => $grossAmount,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_amount' => $netAmount,
                'payment_status' => 'pending',
                'notes' => $validated['remarks'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

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

            AuditLog::log('Created Mobile Dairy Milk Entry via API', 'MilkCollection', $collection->id, [
                'farmer' => $farmer->name,
                'receipt' => $receiptNumber,
                'amount' => $netAmount,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Milk entry recorded successfully! Receipt #{$receiptNumber} — ₹{$netAmount}",
                'data' => [
                    'collection' => $collection,
                    'farmer_balance' => $newBalance,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record milk entry: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 5. PUT /api/v1/procurement/mobile-dairy/milk-entry/{collection}
     * Update/Correct an existing milk collection record
     */
    public function updateMilkEntry(Request $request, $collectionId)
    {
        $collection = MilkCollection::find($collectionId);
        if (!$collection) {
            return response()->json(['status' => 'error', 'message' => 'Collection not found'], 404);
        }

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

            $newFarmerBalance = round((float) $farmer->current_balance + $diff, 2);
            $farmer->update(['current_balance' => $newFarmerBalance]);

            $ledger = FarmerLedger::where('reference_type', 'milk_collection')->where('reference_id', $collection->id)->first();
            if ($ledger) {
                $ledger->update([
                    'amount' => $newNet,
                    'balance' => $newFarmerBalance,
                    'description' => "Milk Supply (" . ucfirst($collection->shift) . ") {$qty} Ltr @ ₹{$rate}/Ltr [Corrected: {$validated['correction_reason']}]",
                ]);
            }

            AuditLog::log('Corrected Milk Collection Entry via API', 'MilkCollection', $collection->id, [
                'farmer' => $farmer->name,
                'old_amount' => $oldNet,
                'new_amount' => $newNet,
                'reason' => $validated['correction_reason'],
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Milk entry #{$collection->receipt_number} corrected successfully! Difference: ₹{$diff}",
                'data' => [
                    'collection' => $collection,
                    'farmer_balance' => $newFarmerBalance,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to correct milk entry: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 6. POST /api/v1/procurement/mobile-dairy/loans/{farmer}
     * Issue a new Loan/Advance to Farmer
     */
    public function storeLoan(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

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

            AuditLog::log('Issued Farmer Loan/Advance via API', 'FarmerAdvance', $loan->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'voucher' => $voucher,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Loan/Advance of ₹{$amount} issued successfully (Voucher #{$voucher})!",
                'data' => [
                    'loan' => $loan,
                    'farmer_balance' => $newBalance,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to issue loan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 7. POST /api/v1/procurement/mobile-dairy/loans/{loan}/repayment
     * Record Loan Repayment Voucher
     */
    public function storeLoanRepayment(Request $request, $loanId)
    {
        $loan = FarmerAdvance::with('farmer')->find($loanId);
        if (!$loan) {
            return response()->json(['status' => 'error', 'message' => 'Loan record not found'], 404);
        }

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
            return response()->json([
                'status' => 'error',
                'message' => "Repayment amount (₹{$repayAmount}) cannot exceed outstanding principal (₹{$remainingPrincipal}).",
            ], 422);
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

            if (($validated['payment_mode'] ?? '') !== 'Milk Deduction') {
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

            AuditLog::log('Recorded Loan Repayment via API', 'FarmerAdvanceRepayment', $repayment->id, [
                'farmer' => $farmer->name,
                'amount' => $repayAmount,
                'voucher' => $loan->voucher_no,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Repayment of ₹{$repayAmount} recorded successfully!",
                'data' => [
                    'repayment' => $repayment,
                    'loan' => $loan->fresh(),
                    'farmer_balance' => $farmer->current_balance,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record repayment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 8. POST /api/v1/procurement/mobile-dairy/deductions/{farmer}
     * Add Farmer Deduction (Feed, doctor fee, loan, etc.)
     */
    public function storeDeduction(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

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

            AuditLog::log('Created Farmer Deduction via API', 'FarmerDeduction', $deduction->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'type' => $validated['deduction_type'],
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Deduction of ₹{$amount} recorded successfully!",
                'data' => [
                    'deduction' => $deduction,
                    'farmer_balance' => $farmer->current_balance,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record deduction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 9. GET /api/v1/procurement/mobile-dairy/products
     * Available Product Catalog for Store Sales
     */
    public function products(Request $request)
    {
        $products = Product::where('status', 1)->orWhere('in_stock', 1)->orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * 10. POST /api/v1/procurement/mobile-dairy/products/{farmer}
     * Record Cattle Feed or Product Sale to Farmer
     */
    public function storeProductSale(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sale_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
            'is_paid' => 'required|in:0,1,true,false',
            'remarks' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (float) $validated['quantity'];
        $rate = (float) $validated['rate'];
        $total = round($qty * $rate, 2);
        $isPaid = filter_var($validated['is_paid'], FILTER_VALIDATE_BOOLEAN);
        $paidAmount = $isPaid ? $total : 0.00;
        $remainingAmount = round($total - $paidAmount, 2);

        if ((float) $product->current_stock < $qty) {
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient stock for {$product->name}. Current in stock: {$product->current_stock}",
            ], 422);
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

            $newStock = max(0, (float) $product->current_stock - $qty);
            $product->update([
                'current_stock' => $newStock,
                'in_stock' => $newStock > 0,
            ]);

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

            AuditLog::log('Created Mobile Dairy Product Sale via API', 'ProductSale', $sale->id, [
                'farmer' => $farmer->name,
                'product' => $product->name,
                'total' => $total,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Product sale recorded successfully! Bill #{$saleNumber} — ₹{$total}",
                'data' => [
                    'sale' => $sale,
                    'farmer_balance' => $farmer->current_balance,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record product sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 11. POST /api/v1/procurement/mobile-dairy/emergency/{farmer}
     * Disburse or record Emergency Assistance Fund
     */
    public function storeEmergencyFund(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

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

            AuditLog::log('Created Emergency Fund Request via API', 'FarmerEmergencyFund', $fund->id, [
                'farmer' => $farmer->name,
                'amount' => $amount,
                'request_no' => $reqNo,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Emergency assistance request #{$reqNo} recorded successfully (₹{$amount})!",
                'data' => $fund,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record emergency fund: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 12. POST /api/v1/procurement/mobile-dairy/emergency/{emergencyFund}/recovery
     * Record Recovery for an Emergency Fund Assistance
     */
    public function recordEmergencyRecovery(Request $request, $emergencyFundId)
    {
        $emergencyFund = FarmerEmergencyFund::find($emergencyFundId);
        if (!$emergencyFund) {
            return response()->json(['status' => 'error', 'message' => 'Emergency fund record not found'], 404);
        }

        $validated = $request->validate([
            'recovered_amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $recovery = (float) $validated['recovered_amount'];
        $outstanding = (float) $emergencyFund->outstanding_amount;

        if ($recovery > $outstanding + 0.01) {
            return response()->json([
                'status' => 'error',
                'message' => "Recovery amount (₹{$recovery}) cannot exceed outstanding emergency balance (₹{$outstanding}).",
            ], 422);
        }

        $newRecovered = round((float) $emergencyFund->recovered_amount + $recovery, 2);
        $emergencyFund->update([
            'recovered_amount' => $newRecovered,
            'notes' => ($emergencyFund->notes ? $emergencyFund->notes . " | " : "") . "Recovered ₹{$recovery} on " . date('d M Y'),
        ]);

        AuditLog::log('Recovered Emergency Fund Amount via API', 'FarmerEmergencyFund', $emergencyFund->id, [
            'recovered' => $recovery,
            'total_recovered' => $newRecovered,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Emergency fund recovery of ₹{$recovery} recorded successfully!",
            'data' => $emergencyFund->fresh(),
        ]);
    }

    /**
     * 13. POST /api/v1/procurement/mobile-dairy/invoices/{farmer}
     * Generate Period Settlement Invoice for Farmer
     */
    public function generateInvoice(Request $request, $farmerId)
    {
        $farmer = $this->resolveFarmer($farmerId);
        if (!$farmer) {
            return response()->json(['status' => 'error', 'message' => 'Farmer not found'], 404);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->get();

        $totalQty = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum('net_amount'), 2);

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

        if (Schema::hasTable('product_sales')) {
            $unpaidSales = ProductSale::where('farmer_id', $farmer->id)
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->sum('remaining_amount');
            $feedDeduction = round($feedDeduction + (float) $unpaidSales, 2);
        }

        if (Schema::hasTable('farmer_advances')) {
            $advs = FarmerAdvance::where('farmer_id', $farmer->id)
                ->whereBetween('advance_date', [$startDate, $endDate])
                ->where('status', '!=', 'recovered')
                ->get();
            $loanRecovery = round((float) $advs->sum(fn($a) => $a->principal_balance), 2);
        }

        // Dynamically wire Addition Types configured in Center Information
        $extraAdditions = 0.00;
        try {
            $settingRow = SystemSetting::where('key', 'center_addition_types')->first();
            if ($settingRow && !empty($settingRow->value)) {
                $additionTypes = json_decode($settingRow->value, true);
                if (is_array($additionTypes)) {
                    foreach ($additionTypes as $at) {
                        $val = trim($at['value'] ?? '');
                        if (str_ends_with($val, '/ltr')) {
                            $ratePerLtr = (float) str_replace('/ltr', '', $val);
                            $extraAdditions += round($totalQty * $ratePerLtr, 2);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        $totalCredit = round($totalCredit + $extraAdditions, 2);
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
                'status' => 'pending',
                'is_edited' => false,
                'created_by' => Auth::id(),
            ]);

            AuditLog::log('Generated Farmer Settlement Invoice via API', 'FarmerInvoice', $invoice->id, [
                'farmer' => $farmer->name,
                'invoice' => $invNum,
                'net_payment' => $netPayment,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Settlement Invoice #{$invNum} generated successfully! Net Payable: ₹{$netPayment}",
                'data' => $invoice,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 14. POST /api/v1/procurement/mobile-dairy/invoices/{invoice}/mark-paid
     * Mark an Invoice as Paid with Payment Mode & Reference
     */
    public function markInvoicePaid(Request $request, $invoiceId)
    {
        $invoice = FarmerInvoice::find($invoiceId);
        if (!$invoice) {
            return response()->json(['status' => 'error', 'message' => 'Invoice not found'], 404);
        }

        $validated = $request->validate([
            'payment_mode' => 'required|string|in:Bank Transfer,Cash,UPI,Cheque',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        if ($invoice->status === 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => "Invoice #{$invoice->invoice_number} is already marked as paid.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status' => 'paid',
                'payment_mode' => $validated['payment_mode'],
                'payment_reference' => $validated['payment_reference'] ?? null,
            ]);

            MilkCollection::where('farmer_id', $invoice->farmer_id)
                ->whereBetween('collection_date', [$invoice->period_start, $invoice->period_end])
                ->update(['payment_status' => 'settled']);

            AuditLog::log('Marked Farmer Settlement Invoice as Paid via API', 'FarmerInvoice', $invoice->id, [
                'invoice' => $invoice->invoice_number,
                'amount' => $invoice->net_payment,
                'mode' => $validated['payment_mode'],
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Invoice #{$invoice->invoice_number} marked as PAID via {$validated['payment_mode']}!",
                'data' => $invoice->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark invoice as paid: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 15. GET /api/v1/procurement/mobile-dairy/invoices/{invoice}/slip
     * Complete itemized invoice slip data for printable display
     */
    public function invoiceSlip(Request $request, $invoiceId)
    {
        $invoice = FarmerInvoice::with(['farmer', 'creator'])->find($invoiceId);
        if (!$invoice) {
            return response()->json(['status' => 'error', 'message' => 'Invoice not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'invoice' => $invoice,
                'farmer' => $invoice->farmer,
                'collections' => $invoice->milk_collections,
                'deductions' => $invoice->deductions_list,
                'dairy_center' => [
                    'name' => config('app.name', 'Mobile Dairy Management'),
                    'center_name' => $invoice->farmer->collectionCenter?->name ?? 'Main Procurement Center',
                    'contact' => $invoice->farmer->collectionCenter?->contact_number ?? '',
                ],
            ],
        ]);
    }
}
