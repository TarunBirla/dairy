<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Farmer;
use App\Models\FarmerAdvance;
use App\Models\FarmerDeduction;
use App\Models\FarmerInvoice;
use App\Models\FarmerWallet;
use App\Models\FarmerWalletTransaction;
use App\Models\ProductSale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FarmerWalletService
{
    /**
     * Ensure database schema tables and columns are created safely.
     */
    public static function ensureSchemaMigrated(): void
    {
        if (!Schema::hasTable('farmer_wallets')) {
            Schema::create('farmer_wallets', function ($table) {
                $table->id();
                $table->foreignId('farmer_id')->unique()->constrained('farmers')->cascadeOnDelete();
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('farmer_wallet_transactions')) {
            Schema::create('farmer_wallet_transactions', function ($table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('farmer_wallets')->cascadeOnDelete();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->foreignId('farmer_invoice_id')->nullable()->constrained('farmer_invoices')->nullOnDelete();
                $table->string('transaction_type'); // 'credit', 'debit', 'reversal'
                $table->string('category')->default('General');
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_before', 12, 2)->default(0.00);
                $table->decimal('balance_after', 12, 2)->default(0.00);
                $table->string('reference_type')->nullable();
                $table->string('reference_id')->nullable();
                $table->unsignedBigInteger('reversal_of_transaction_id')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['farmer_id', 'category']);
                $table->index(['wallet_id', 'created_at']);
                $table->index('farmer_invoice_id');
            });
        }

        if (Schema::hasTable('farmer_invoices')) {
            $cols = [
                'opening_wallet_balance' => 'ALTER TABLE `farmer_invoices` ADD COLUMN `opening_wallet_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00',
                'wallet_deducted_amount' => 'ALTER TABLE `farmer_invoices` ADD COLUMN `wallet_deducted_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00',
                'closing_wallet_balance' => 'ALTER TABLE `farmer_invoices` ADD COLUMN `closing_wallet_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00',
                'wallet_deductions_breakdown' => 'ALTER TABLE `farmer_invoices` ADD COLUMN `wallet_deductions_breakdown` LONGTEXT NULL',
            ];
            foreach ($cols as $col => $sql) {
                if (!Schema::hasColumn('farmer_invoices', $col)) {
                    try { DB::statement($sql); } catch (\Throwable $e) {}
                }
            }
        }

        if (Schema::hasTable('milk_collections')) {
            if (!Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
                try {
                    DB::statement('ALTER TABLE `milk_collections` ADD COLUMN `farmer_invoice_id` BIGINT UNSIGNED NULL AFTER `payment_status`');
                    DB::statement('ALTER TABLE `milk_collections` ADD INDEX `idx_mc_farmer_invoice_id` (`farmer_invoice_id`)');
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * Get or create the deduction wallet for a farmer.
     * Initializes existing outstanding balances if empty.
     */
    public static function getOrCreateWallet(Farmer|int $farmer): FarmerWallet
    {
        self::ensureSchemaMigrated();

        $farmerId = $farmer instanceof Farmer ? $farmer->id : (int) $farmer;
        $farmerModel = $farmer instanceof Farmer ? $farmer : Farmer::find($farmerId);

        $wallet = FarmerWallet::firstOrCreate(
            ['farmer_id' => $farmerId],
            ['balance' => 0.00]
        );

        // Seed wallet from un-invoiced deductions, loans, and store purchases if brand new
        if ($wallet->wasRecentlyCreated || (float) $wallet->balance === 0.0 && $wallet->transactions()->count() === 0) {
            self::seedInitialWalletOutstanding($farmerModel, $wallet);
        }

        return $wallet;
    }

    /**
     * Automatically populate initial outstanding balances into a newly created wallet.
     */
    protected static function seedInitialWalletOutstanding(?Farmer $farmer, FarmerWallet $wallet): void
    {
        if (!$farmer) return;

        DB::transaction(function () use ($farmer, $wallet) {
            $totalOutstanding = 0.0;
            $currentBalance = 0.0;

            // 1. Outstanding advances / loans (Avoid double seeding if transactions already exist)
            if (Schema::hasTable('farmer_advances')) {
                $hasAdvanceTx = FarmerWalletTransaction::where('wallet_id', $wallet->id)
                    ->where(function ($q) {
                        $q->where('category', 'like', '%Advance%')
                          ->orWhere('remarks', 'like', '%Advance%');
                    })->exists();

                if (!$hasAdvanceTx) {
                    $advances = FarmerAdvance::where('farmer_id', $farmer->id)
                        ->where('status', '!=', 'recovered')
                        ->get();

                    $loanSum = round((float) $advances->sum(fn($a) => (float) $a->principal_balance), 2);
                    if ($loanSum > 0) {
                        $before = $currentBalance;
                        $currentBalance += $loanSum;
                        FarmerWalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'farmer_id' => $farmer->id,
                            'transaction_type' => FarmerWalletTransaction::TYPE_CREDIT,
                            'category' => 'Advance / Loan',
                            'amount' => $loanSum,
                            'balance_before' => $before,
                            'balance_after' => $currentBalance,
                            'reference_type' => 'farmer_advances',
                            'reference_id' => null,
                            'remarks' => 'Opening Outstanding: Active advances / loan principal',
                            'created_by' => Auth::id(),
                        ]);
                        $totalOutstanding += $loanSum;
                    }
                }
            }

            // 2. Outstanding product / feed purchases
            if (Schema::hasTable('product_sales')) {
                $farmerCode = $farmer->farmer_code;
                $unpaidSales = ProductSale::where(function ($q) use ($farmer, $farmerCode) {
                    $q->where('farmer_id', $farmer->id);
                    if (!empty($farmerCode)) $q->orWhere('farmer_code', $farmerCode);
                })
                ->where('remaining_amount', '>', 0)
                ->get();

                $salesDue = round((float) $unpaidSales->sum('remaining_amount'), 2);
                if ($salesDue > 0) {
                    $before = $currentBalance;
                    $currentBalance += $salesDue;
                    FarmerWalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'farmer_id' => $farmer->id,
                        'transaction_type' => FarmerWalletTransaction::TYPE_CREDIT,
                        'category' => 'Cattle Feed',
                        'amount' => $salesDue,
                        'balance_before' => $before,
                        'balance_after' => $currentBalance,
                        'reference_type' => 'product_sales',
                        'reference_id' => null,
                        'remarks' => 'Opening Outstanding: Cattle feed & product store purchases',
                        'created_by' => Auth::id(),
                    ]);
                    $totalOutstanding += $salesDue;
                }
            }

            // 3. Outstanding given deductions in farmer_deductions
            if (Schema::hasTable('farmer_deductions')) {
                $deds = FarmerDeduction::where('farmer_id', $farmer->id)
                    ->where('transaction_type', 'given')
                    ->whereNotIn('deduction_type', ['cattle_feed', 'advance_recovery'])
                    ->get();

                $dedsSum = round((float) $deds->sum('amount'), 2);
                if ($dedsSum > 0) {
                    $before = $currentBalance;
                    $currentBalance += $dedsSum;
                    FarmerWalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'farmer_id' => $farmer->id,
                        'transaction_type' => FarmerWalletTransaction::TYPE_CREDIT,
                        'category' => 'Stationary',
                        'amount' => $dedsSum,
                        'balance_before' => $before,
                        'balance_after' => $currentBalance,
                        'reference_type' => 'farmer_deductions',
                        'reference_id' => null,
                        'remarks' => 'Opening Outstanding: Stationary & other deductions',
                        'created_by' => Auth::id(),
                    ]);
                    $totalOutstanding += $dedsSum;
                }
            }

            $wallet->update(['balance' => round($totalOutstanding, 2)]);
        });
    }

    /**
     * Clean up any duplicate 'Opening Outstanding' transactions for advances
     * when explicit 'Advance Issued' transactions already exist.
     */
    public static function cleanupDuplicateAdvanceTransactions(Farmer|int $farmer): void
    {
        $farmerId = $farmer instanceof Farmer ? $farmer->id : (int) $farmer;
        $wallet = FarmerWallet::where('farmer_id', $farmerId)->first();
        if (!$wallet) return;

        try {
            DB::transaction(function () use ($wallet, $farmerId) {
                $hasExplicitAdvance = FarmerWalletTransaction::where('wallet_id', $wallet->id)
                    ->where('transaction_type', FarmerWalletTransaction::TYPE_CREDIT)
                    ->where(function ($q) {
                        $q->where('remarks', 'like', '%Advance Issued%')
                          ->orWhere('reference_type', 'farmer_advances');
                    })
                    ->exists();

                if ($hasExplicitAdvance) {
                    $openingAdvances = FarmerWalletTransaction::where('wallet_id', $wallet->id)
                        ->where('transaction_type', FarmerWalletTransaction::TYPE_CREDIT)
                        ->where('category', 'like', '%Advance%')
                        ->where('remarks', 'like', '%Opening Outstanding%')
                        ->get();

                    if ($openingAdvances->count() > 0) {
                        $actualLoanSum = round((float) FarmerAdvance::where('farmer_id', $farmerId)
                            ->where('status', '!=', 'recovered')
                            ->sum('amount'), 2);

                        $explicitSum = round((float) FarmerWalletTransaction::where('wallet_id', $wallet->id)
                            ->where('transaction_type', FarmerWalletTransaction::TYPE_CREDIT)
                            ->where('category', 'like', '%Advance%')
                            ->where('remarks', 'like', '%Advance Issued%')
                            ->sum('amount'), 2);

                        if ($explicitSum >= $actualLoanSum && $actualLoanSum > 0) {
                            foreach ($openingAdvances as $op) {
                                $op->delete();
                            }
                            $wallet->recalculateBalance();
                        }
                    }
                }
            });
        } catch (\Throwable $e) {}
    }

    /**
     * Get comprehensive summary of a farmer's deduction wallet.
     */
    public static function getWalletSummary(Farmer|int $farmer): array
    {
        $farmerId = $farmer instanceof Farmer ? $farmer->id : (int) $farmer;
        self::cleanupDuplicateAdvanceTransactions($farmerId);

        $wallet = self::getOrCreateWallet($farmer);
        $farmerModel = $farmer instanceof Farmer ? $farmer : Farmer::find($farmerId);

        $categoryBalances = $wallet->getCategoryBalances();
        $totalBalance = (float) $wallet->balance;

        // If categories sum does not match, ensure wallet balance is re-synced
        $catSum = array_sum($categoryBalances);
        if (abs($catSum - $totalBalance) > 0.05 && $catSum > 0) {
            $wallet->recalculateBalance();
            $totalBalance = (float) $wallet->balance;
            $categoryBalances = $wallet->getCategoryBalances();
        }

        $recentTransactions = FarmerWalletTransaction::where('wallet_id', $wallet->id)
            ->with(['creator', 'invoice'])
            ->latest('id')
            ->limit(10)
            ->get();

        return [
            'wallet' => $wallet,
            'farmer' => $farmerModel,
            'total_balance' => $totalBalance,
            'category_balances' => $categoryBalances,
            'recent_transactions' => $recentTransactions,
            'categories_count' => count($categoryBalances),
        ];
    }

    /**
     * Add a new outstanding deduction to the farmer's wallet.
     * Transaction type: 'credit' (increases outstanding amount to be recovered).
     */
    public static function addDeduction(
        Farmer|int $farmer,
        string $category,
        float $amount,
        string $remarks = '',
        ?int $createdBy = null,
        ?string $referenceType = null,
        ?string $referenceId = null
    ): FarmerWalletTransaction {
        self::ensureSchemaMigrated();

        $amount = round(abs($amount), 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be greater than zero.');
        }

        $category = trim($category) ?: 'General';
        $createdBy = $createdBy ?: Auth::id();

        return DB::transaction(function () use ($farmer, $category, $amount, $remarks, $createdBy, $referenceType, $referenceId) {
            $wallet = self::getOrCreateWallet($farmer);

            // Row-level lock to prevent concurrency race conditions
            $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();
            $balanceBefore = (float) $lockedWallet->balance;
            $balanceAfter = round($balanceBefore + $amount, 2);

            $tx = FarmerWalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'farmer_id' => $lockedWallet->farmer_id,
                'transaction_type' => FarmerWalletTransaction::TYPE_CREDIT,
                'category' => $category,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType ?: 'manual_addition',
                'reference_id' => $referenceId,
                'remarks' => $remarks ?: "Added {$category} deduction of ₹" . number_format($amount, 2),
                'created_by' => $createdBy,
            ]);

            $lockedWallet->update(['balance' => $balanceAfter]);

            AuditLog::log("Added {$category} deduction ₹{$amount} to Farmer #{$lockedWallet->farmer_id} wallet", 'FarmerWallet', $lockedWallet->id, [
                'amount' => $amount,
                'category' => $category,
                'balance_after' => $balanceAfter,
            ]);

            return $tx;
        });
    }

    /**
     * Manual credit or debit adjustment to a farmer's wallet balance with reason.
     */
    public static function manualAdjustment(
        Farmer|int $farmer,
        string $adjustmentType, // 'credit' (increases outstanding) or 'debit' (reduces outstanding / waives)
        string $category,
        float $amount,
        string $remarks,
        ?int $createdBy = null
    ): FarmerWalletTransaction {
        self::ensureSchemaMigrated();

        $amount = round(abs($amount), 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Adjustment amount must be greater than zero.');
        }

        $adjustmentType = strtolower($adjustmentType) === 'debit' ? 'debit' : 'credit';
        $category = trim($category) ?: 'General';
        $createdBy = $createdBy ?: Auth::id();

        return DB::transaction(function () use ($farmer, $adjustmentType, $category, $amount, $remarks, $createdBy) {
            $wallet = self::getOrCreateWallet($farmer);

            $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();
            $balanceBefore = (float) $lockedWallet->balance;

            if ($adjustmentType === 'debit') {
                $balanceAfter = round(max(0, $balanceBefore - $amount), 2);
            } else {
                $balanceAfter = round($balanceBefore + $amount, 2);
            }

            $tx = FarmerWalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'farmer_id' => $lockedWallet->farmer_id,
                'transaction_type' => $adjustmentType,
                'category' => $category,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'remarks' => $remarks ?: "Manual {$adjustmentType} adjustment: {$category} ₹" . number_format($amount, 2),
                'created_by' => $createdBy,
            ]);

            $lockedWallet->update(['balance' => $balanceAfter]);

            AuditLog::log("Manual {$adjustmentType} adjustment of ₹{$amount} on Farmer #{$lockedWallet->farmer_id} wallet", 'FarmerWallet', $lockedWallet->id, [
                'type' => $adjustmentType,
                'amount' => $amount,
                'category' => $category,
                'balance_after' => $balanceAfter,
                'remarks' => $remarks,
            ]);

            return $tx;
        });
    }

    /**
     * Apply invoice deduction against the farmer's deduction wallet.
     * Supports partial deduction, carries forward remaining balance, and maintains complete transaction history.
     *
     * @param FarmerInvoice $invoice
     * @param array $categoryDeductions array of [category => amount_to_deduct]
     * @param int|null $createdBy
     * @return array ['opening_wallet_balance', 'wallet_deducted_amount', 'closing_wallet_balance', 'breakdown']
     */
    public static function applyInvoiceDeductions(
        FarmerInvoice $invoice,
        array $breakdownOrCategoryDeductions = [],
        ?int $createdBy = null
    ): array {
        self::ensureSchemaMigrated();

        $createdBy = $createdBy ?: Auth::id();

        return DB::transaction(function () use ($invoice, $breakdownOrCategoryDeductions, $createdBy) {
            // Remove any previous transactions for this specific invoice so ledger stays clean and non-spammy
            FarmerWalletTransaction::where('farmer_invoice_id', $invoice->id)->delete();

            $wallet = self::getOrCreateWallet($invoice->farmer_id);
            $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();

            $openingBalance = (float) $lockedWallet->balance;
            $runningBalance = $openingBalance;
            $totalDeducted = 0.0;
            $breakdown = [];

            // A. Deduct from FarmerAdvance (Advance / Loan)
            $advDeduction = (float) ($invoice->advance_deduction ?? 0);
            if ($advDeduction > 0 && Schema::hasTable('farmer_advances')) {
                $remAdv = $advDeduction;
                $activeAdvances = FarmerAdvance::where('farmer_id', $invoice->farmer_id)
                    ->where('status', '!=', 'recovered')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($activeAdvances as $adv) {
                    if ($remAdv <= 0) break;
                    $unpaid = (float) $adv->principal_balance;
                    if ($unpaid <= 0) continue;

                    $dedHere = round(min($remAdv, $unpaid), 2);
                    $newDeducted = round((float) $adv->deducted_amount + $dedHere, 2);
                    $adv->deducted_amount = $newDeducted;

                    if (round((float) $adv->amount - $newDeducted, 2) <= 0.01) {
                        $adv->status = 'recovered';
                        $adv->paid_date = \Carbon\Carbon::now();
                    }
                    $adv->save();

                    if (class_exists(\App\Models\FarmerAdvanceRepayment::class)) {
                        try {
                            \App\Models\FarmerAdvanceRepayment::create([
                                'farmer_advance_id' => $adv->id,
                                'farmer_id' => $invoice->farmer_id,
                                'repayment_date' => \Carbon\Carbon::now(),
                                'amount' => $dedHere,
                                'payment_mode' => 'Invoice Deduction',
                                'remark' => "Deducted in Invoice #{$invoice->invoice_number}",
                                'recorded_by' => $createdBy,
                            ]);
                        } catch (\Throwable $e) {}
                    }

                    $remAdv -= $dedHere;
                }
            }

            // B. Deduct from ProductSale (Cattle Feed / Food / Store Purchases)
            $feedDeduction = (float) ($invoice->feed_deduction ?? 0);
            if ($feedDeduction > 0 && Schema::hasTable('product_sales')) {
                $remFeed = $feedDeduction;
                $farmer = $invoice->farmer;
                $farmerCode = $farmer ? $farmer->farmer_code : null;

                $unpaidSales = ProductSale::where(function ($q) use ($invoice, $farmerCode) {
                    $q->where('farmer_id', $invoice->farmer_id);
                    if (!empty($farmerCode)) {
                        $q->orWhere('farmer_code', $farmerCode);
                    }
                })
                ->where('remaining_amount', '>', 0)
                ->orderBy('id', 'asc')
                ->get();

                foreach ($unpaidSales as $sale) {
                    if ($remFeed <= 0) break;
                    $saleRem = (float) $sale->remaining_amount;
                    if ($saleRem <= 0) continue;

                    $dedHere = round(min($remFeed, $saleRem), 2);
                    $sale->paid_amount = round((float) $sale->paid_amount + $dedHere, 2);
                    $newRem = round(max(0, $saleRem - $dedHere), 2);
                    $sale->remaining_amount = $newRem;
                    $sale->is_paid = ($newRem <= 0.01);
                    $sale->payment_mode = 'Invoice Deduction';
                    $sale->remarks = trim(($sale->remarks ?? '') . " [Invoice #{$invoice->invoice_number} Paid: ₹{$dedHere}]");
                    $sale->save();

                    $remFeed -= $dedHere;
                }
            }

            // C. Apply Wallet Category Debits & Recalculate Balance
            $isStructuredList = !empty($breakdownOrCategoryDeductions) && isset($breakdownOrCategoryDeductions[0]) && is_array($breakdownOrCategoryDeductions[0]);
            $totalOriginalFromBreakdown = 0.0;
            $totalRemainingFromBreakdown = 0.0;

            if ($isStructuredList) {
                foreach ($breakdownOrCategoryDeductions as $item) {
                    $rawCat = $item['category'] ?? ($item['name'] ?? 'Deduction');
                    $catName = trim(str_ireplace(['(Wallet)', '(Standard)', '(Fixed)'], '', $rawCat));
                    if (empty($catName)) $catName = 'General';
                    $origBal = (float) ($item['original_balance'] ?? 0);
                    $dedAmt = (float) ($item['deducted_amount'] ?? 0);
                    $remBal = (float) ($item['remaining_balance'] ?? max(0, $origBal - $dedAmt));

                    $totalOriginalFromBreakdown += $origBal;
                    $totalRemainingFromBreakdown += $remBal;

                    if ($dedAmt > 0) {
                        $balBefore = $runningBalance;
                        $runningBalance = round(max(0, $runningBalance - $dedAmt), 2);

                        FarmerWalletTransaction::create([
                            'wallet_id' => $lockedWallet->id,
                            'farmer_id' => $invoice->farmer_id,
                            'farmer_invoice_id' => $invoice->id,
                            'transaction_type' => FarmerWalletTransaction::TYPE_DEBIT,
                            'category' => $catName,
                            'amount' => $dedAmt,
                            'balance_before' => $balBefore,
                            'balance_after' => $runningBalance,
                            'reference_type' => 'farmer_invoice',
                            'reference_id' => (string) $invoice->id,
                            'remarks' => "Deducted in Invoice #{$invoice->invoice_number} ({$invoice->period_start->format('d/m/Y')} - {$invoice->period_end->format('d/m/Y')})",
                            'created_by' => $createdBy,
                        ]);

                        $totalDeducted += $dedAmt;
                    }

                    $breakdown[] = [
                        'key' => $item['key'] ?? \Illuminate\Support\Str::slug($catName, '_'),
                        'name' => $catName,
                        'category' => $catName,
                        'original_balance' => $origBal,
                        'deducted_amount' => $dedAmt,
                        'remaining_balance' => $remBal,
                    ];
                }
            } elseif (!empty($breakdownOrCategoryDeductions)) {
                foreach ($categoryDeductions as $catName => $requestedAmt) {
                    $requestedAmt = round(max(0, (float) $requestedAmt), 2);
                    if ($requestedAmt <= 0) continue;

                    // Match against actual wallet category balances
                    $targetCat = null;
                    $origBal = 0.0;
                    foreach ($categoryBalances as $cName => $cBal) {
                        if (
                            strcasecmp($cName, $catName) === 0 ||
                            strcasecmp(\Illuminate\Support\Str::slug($cName, '_'), \Illuminate\Support\Str::slug($catName, '_')) === 0 ||
                            strcasecmp('wallet_' . \Illuminate\Support\Str::slug($cName, '_'), \Illuminate\Support\Str::slug($catName, '_')) === 0 ||
                            strcasecmp(\Illuminate\Support\Str::slug($cName, '_'), str_replace('wallet_', '', \Illuminate\Support\Str::slug($catName, '_'))) === 0
                        ) {
                            $targetCat = $cName;
                            $origBal = (float) $cBal;
                            break;
                        }
                    }

                    // Only deduct if this category actually exists in the farmer's wallet (e.g. do not deduct center charges like management fee)
                    if (!$targetCat || $origBal <= 0) {
                        continue;
                    }

                    // Deduct up to original balance
                    $deductAmt = min($requestedAmt, $origBal);
                    if ($deductAmt <= 0) continue;

                    $balBefore = $runningBalance;
                    $runningBalance = round(max(0, $runningBalance - $deductAmt), 2);
                    $remBal = round(max(0, $origBal - $deductAmt), 2);

                    FarmerWalletTransaction::create([
                        'wallet_id' => $lockedWallet->id,
                        'farmer_id' => $invoice->farmer_id,
                        'farmer_invoice_id' => $invoice->id,
                        'transaction_type' => FarmerWalletTransaction::TYPE_DEBIT,
                        'category' => $targetCat,
                        'amount' => $deductAmt,
                        'balance_before' => $balBefore,
                        'balance_after' => $runningBalance,
                        'reference_type' => 'farmer_invoice',
                        'reference_id' => (string) $invoice->id,
                        'remarks' => "Deducted in Invoice #{$invoice->invoice_number} ({$invoice->period_start->format('d/m/Y')} - {$invoice->period_end->format('d/m/Y')})",
                        'created_by' => $createdBy,
                    ]);

                    $totalDeducted += $deductAmt;
                    $breakdown[] = [
                        'key' => \Illuminate\Support\Str::slug($targetCat, '_'),
                        'name' => $targetCat,
                        'category' => $targetCat,
                        'original_balance' => $origBal,
                        'deducted_amount' => $deductAmt,
                        'remaining_balance' => $remBal,
                    ];
                }
            } else {
                // Default: Deduct existing category balances up to invoice deduction amount or milk amount
                $maxDeductible = (float) $invoice->total_deduction ?: (float) $invoice->milk_amount;
                $remToDeduct = $maxDeductible;

                foreach ($categoryBalances as $catName => $catBal) {
                    if ($remToDeduct <= 0) break;

                    $deductAmt = round(min($catBal, $remToDeduct), 2);
                    if ($deductAmt <= 0) continue;

                    $balBefore = $runningBalance;
                    $runningBalance = round(max(0, $runningBalance - $deductAmt), 2);
                    $remBal = round(max(0, $catBal - $deductAmt), 2);

                    FarmerWalletTransaction::create([
                        'wallet_id' => $lockedWallet->id,
                        'farmer_id' => $invoice->farmer_id,
                        'farmer_invoice_id' => $invoice->id,
                        'transaction_type' => FarmerWalletTransaction::TYPE_DEBIT,
                        'category' => $catName,
                        'amount' => $deductAmt,
                        'balance_before' => $balBefore,
                        'balance_after' => $runningBalance,
                        'reference_type' => 'farmer_invoice',
                        'reference_id' => (string) $invoice->id,
                        'remarks' => "Deducted in Invoice #{$invoice->invoice_number}",
                        'created_by' => $createdBy,
                    ]);

                    $totalDeducted += $deductAmt;
                    $remToDeduct -= $deductAmt;

                    $breakdown[] = [
                        'key' => \Illuminate\Support\Str::slug($catName, '_'),
                        'name' => $catName,
                        'category' => $catName,
                        'original_balance' => $catBal,
                        'deducted_amount' => $deductAmt,
                        'remaining_balance' => $remBal,
                    ];
                }
            }

            // Determine accurate closing balance
            if ($isStructuredList && $totalOriginalFromBreakdown > 0) {
                $closingBalance = round(max(0, $totalRemainingFromBreakdown), 2);
                $finalOpening = round($totalOriginalFromBreakdown, 2);
            } else {
                $closingBalance = round(max(0, $openingBalance - $totalDeducted), 2);
                $finalOpening = round($openingBalance, 2);
            }

            // Update wallet balance in database
            $lockedWallet->update(['balance' => $closingBalance]);

            // Save wallet details onto the invoice record
            $invoice->update([
                'opening_wallet_balance' => $finalOpening,
                'wallet_deducted_amount' => round($totalDeducted, 2),
                'closing_wallet_balance' => $closingBalance,
                'previous_balance' => $closingBalance,
                'wallet_deductions_breakdown' => $breakdown,
            ]);

            return [
                'opening_wallet_balance' => $finalOpening,
                'wallet_deducted_amount' => round($totalDeducted, 2),
                'closing_wallet_balance' => $closingBalance,
                'breakdown' => $breakdown,
            ];
        });
    }

    /**
     * Reverse all wallet deductions previously applied for an invoice.
     * Creates explicit reversal records with references and restores the wallet balance.
     * Never permanently deletes financial audit records.
     */
    public static function reverseInvoiceDeductions(
        FarmerInvoice $invoice,
        string $reason = 'Invoice deleted / modified',
        ?int $createdBy = null
    ): void {
        self::ensureSchemaMigrated();

        $createdBy = $createdBy ?: Auth::id();

        DB::transaction(function () use ($invoice, $reason, $createdBy) {
            // A. Revert FarmerAdvance repayments
            if (Schema::hasTable('farmer_advances')) {
                $advDeducted = (float) ($invoice->advance_deduction ?? 0);
                if ($advDeducted > 0) {
                    $advances = FarmerAdvance::where('farmer_id', $invoice->farmer_id)
                        ->where('deducted_amount', '>', 0)
                        ->orderBy('id', 'desc')
                        ->get();

                    $remToRestore = $advDeducted;
                    foreach ($advances as $adv) {
                        if ($remToRestore <= 0) break;
                        $restore = round(min($remToRestore, (float) $adv->deducted_amount), 2);
                        $adv->deducted_amount = round(max(0, (float) $adv->deducted_amount - $restore), 2);
                        $adv->status = 'pending';
                        $adv->save();
                        $remToRestore -= $restore;
                    }

                    if (class_exists(\App\Models\FarmerAdvanceRepayment::class)) {
                        try {
                            \App\Models\FarmerAdvanceRepayment::where('farmer_id', $invoice->farmer_id)
                                ->where('remark', 'like', "%Invoice #{$invoice->invoice_number}%")
                                ->delete();
                        } catch (\Throwable $e) {}
                    }
                }
            }

            // B. Revert ProductSale payments
            if (Schema::hasTable('product_sales')) {
                $feedDeducted = (float) ($invoice->feed_deduction ?? 0);
                if ($feedDeducted > 0) {
                    $farmer = $invoice->farmer;
                    $farmerCode = $farmer ? $farmer->farmer_code : null;

                    $sales = ProductSale::where(function ($q) use ($invoice, $farmerCode) {
                        $q->where('farmer_id', $invoice->farmer_id);
                        if (!empty($farmerCode)) {
                            $q->orWhere('farmer_code', $farmerCode);
                        }
                    })
                    ->where('remarks', 'like', "%Invoice #{$invoice->invoice_number}%")
                    ->get();

                    if ($sales->isEmpty()) {
                        $sales = ProductSale::where(function ($q) use ($invoice, $farmerCode) {
                            $q->where('farmer_id', $invoice->farmer_id);
                            if (!empty($farmerCode)) {
                                $q->orWhere('farmer_code', $farmerCode);
                            }
                        })
                        ->where('paid_amount', '>', 0)
                        ->orderBy('id', 'desc')
                        ->get();
                    }

                    $remToRestore = $feedDeducted;
                    foreach ($sales as $sale) {
                        if ($remToRestore <= 0) break;
                        $restore = round(min($remToRestore, (float) $sale->paid_amount), 2);
                        $newPaid = round(max(0, (float) $sale->paid_amount - $restore), 2);
                        $sale->paid_amount = $newPaid;
                        $sale->remaining_amount = round(max(0, (float) $sale->total_amount - $newPaid), 2);
                        $sale->is_paid = false;
                        $sale->remarks = str_replace(" [Invoice #{$invoice->invoice_number} Paid: ₹{$restore}]", "", $sale->remarks ?? '');
                        $sale->save();
                        $remToRestore -= $restore;
                    }
                }
            }

            $debits = FarmerWalletTransaction::where('farmer_invoice_id', $invoice->id)
                ->where('transaction_type', FarmerWalletTransaction::TYPE_DEBIT)
                ->get();

            if ($debits->isEmpty()) {
                // Defensive fallback: If debits were not recorded but wallet_deducted_amount > 0 was tracked, restore balance directly
                $dedAmt = (float) ($invoice->wallet_deducted_amount ?? 0);
                if ($dedAmt > 0) {
                    $wallet = self::getOrCreateWallet($invoice->farmer_id);
                    $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();
                    $balBefore = (float) $lockedWallet->balance;
                    $balAfter = round($balBefore + $dedAmt, 2);

                    FarmerWalletTransaction::create([
                        'wallet_id' => $lockedWallet->id,
                        'farmer_id' => $invoice->farmer_id,
                        'farmer_invoice_id' => $invoice->id,
                        'transaction_type' => FarmerWalletTransaction::TYPE_REVERSAL,
                        'category' => 'General',
                        'amount' => $dedAmt,
                        'balance_before' => $balBefore,
                        'balance_after' => $balAfter,
                        'reference_type' => 'invoice_reversal',
                        'reference_id' => (string) $invoice->id,
                        'remarks' => "Reversal: {$reason} (Invoice #{$invoice->invoice_number})",
                        'created_by' => $createdBy,
                    ]);

                    $lockedWallet->update(['balance' => $balAfter]);
                }
                return;
            }

            $wallet = self::getOrCreateWallet($invoice->farmer_id);
            $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();
            $runningBalance = (float) $lockedWallet->balance;

            foreach ($debits as $deb) {
                // Check if already reversed
                $alreadyReversed = FarmerWalletTransaction::where('reversal_of_transaction_id', $deb->id)->exists();
                if ($alreadyReversed) {
                    continue;
                }

                $balBefore = $runningBalance;
                $runningBalance = round($runningBalance + (float) $deb->amount, 2);

                FarmerWalletTransaction::create([
                    'wallet_id' => $lockedWallet->id,
                    'farmer_id' => $invoice->farmer_id,
                    'farmer_invoice_id' => $invoice->id,
                    'transaction_type' => FarmerWalletTransaction::TYPE_REVERSAL,
                    'category' => $deb->category,
                    'amount' => (float) $deb->amount,
                    'balance_before' => $balBefore,
                    'balance_after' => $runningBalance,
                    'reference_type' => 'invoice_reversal',
                    'reference_id' => (string) $invoice->id,
                    'reversal_of_transaction_id' => $deb->id,
                    'remarks' => "Reversal: {$reason} (Invoice #{$invoice->invoice_number})",
                    'created_by' => $createdBy,
                ]);
            }

            $lockedWallet->update(['balance' => $runningBalance]);

            // Remove received entries recorded in farmer_deductions for this invoice
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
                \App\Models\FarmerDeduction::where('farmer_id', $invoice->farmer_id)
                    ->where('comments', 'like', "%Invoice #{$invoice->invoice_number}%")
                    ->delete();
            }

            AuditLog::log("Reversed wallet deductions for Invoice #{$invoice->invoice_number}", 'FarmerWallet', $lockedWallet->id, [
                'invoice_id' => $invoice->id,
                'reason' => $reason,
                'restored_balance' => $runningBalance,
            ]);
        });
    }
}
