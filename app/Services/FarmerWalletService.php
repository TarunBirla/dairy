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

            // 1. Outstanding advances / loans
            if (Schema::hasTable('farmer_advances')) {
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
     * Get comprehensive summary of a farmer's deduction wallet.
     */
    public static function getWalletSummary(Farmer|int $farmer): array
    {
        $wallet = self::getOrCreateWallet($farmer);
        $farmerId = $wallet->farmer_id;
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
        ?int $createdBy = null
    ): FarmerWalletTransaction {
        self::ensureSchemaMigrated();

        $amount = round(abs($amount), 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be greater than zero.');
        }

        $category = trim($category) ?: 'General';
        $createdBy = $createdBy ?: Auth::id();

        return DB::transaction(function () use ($farmer, $category, $amount, $remarks, $createdBy) {
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
                'reference_type' => 'manual_addition',
                'reference_id' => null,
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
        array $categoryDeductions = [],
        ?int $createdBy = null
    ): array {
        self::ensureSchemaMigrated();

        $createdBy = $createdBy ?: Auth::id();

        return DB::transaction(function () use ($invoice, $categoryDeductions, $createdBy) {
            // First, if this invoice already had prior deductions recorded in this wallet, reverse them safely
            self::reverseInvoiceDeductions($invoice, 'Re-calculating invoice deductions', $createdBy);

            $wallet = self::getOrCreateWallet($invoice->farmer_id);
            $lockedWallet = FarmerWallet::where('id', $wallet->id)->lockForUpdate()->first();

            $openingBalance = (float) $lockedWallet->balance;
            $categoryBalances = $lockedWallet->getCategoryBalances();

            $totalDeducted = 0.0;
            $runningBalance = $openingBalance;
            $breakdown = [];

            // If specific category deductions were supplied from the UI / controller
            if (!empty($categoryDeductions)) {
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

            $closingBalance = round(max(0, $openingBalance - $totalDeducted), 2);
            $lockedWallet->update(['balance' => $closingBalance]);

            // Save wallet details onto the invoice record
            $invoice->update([
                'opening_wallet_balance' => $openingBalance,
                'wallet_deducted_amount' => round($totalDeducted, 2),
                'closing_wallet_balance' => $closingBalance,
                'wallet_deductions_breakdown' => $breakdown,
            ]);

            // Clean up any historical sync in farmer_deductions to avoid circular additions
            if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
                \App\Models\FarmerDeduction::where('farmer_id', $invoice->farmer_id)
                    ->where('comments', 'like', "%Invoice #{$invoice->invoice_number}%")
                    ->delete();
            }

            return [
                'opening_wallet_balance' => $openingBalance,
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
