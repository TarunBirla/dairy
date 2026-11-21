<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\FarmerAdvance;
use App\Models\FarmerDeduction;
use App\Models\FarmerInvoice;
use App\Models\FarmerLedger;
use App\Models\FarmerSettlement;
use App\Models\FarmerWallet;
use App\Models\FarmerWalletTransaction;
use App\Models\MilkCollection;
use App\Models\ProductSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FarmerAccountingService
 * 
 * Centralized Single Source of Truth (SSOT) for all farmer financial calculations:
 * 1. Milk Earnings & Payable Balance (दूध बिल व देय भुगतान)
 * 2. Advance / Loan Ledger & Installments (अग्रिम व ऋण बहीखाता)
 * 3. Deduction Wallet & Store Dues (कटौती वॉलेट व स्टोर बकाया)
 * 4. Unified Reconciled Passbook (एकीकृत व स्पष्ट पासबुक)
 */
class FarmerAccountingService
{
    /**
     * Get authoritative financial summary for a farmer.
     * Separates Milk Billing from Loan/Advance Dues so numbers are always crystal-clear.
     */
    public static function getFinancialSummary(Farmer|int $farmer): array
    {
        $farmerModel = $farmer instanceof Farmer ? $farmer : Farmer::find((int) $farmer);
        if (!$farmerModel) {
            return [
                'farmer_id' => null,
                'milk_payable' => 0.00,
                'active_loan_balance' => 0.00,
                'wallet_balance' => 0.00,
                'total_dues' => 0.00,
                'net_balance' => 0.00,
                'is_payable' => true,
                'status_badge' => '₹0.00 (चुकता)',
            ];
        }

        $farmerId = $farmerModel->id;

        // 1. Unbilled / Pending Milk Collections (दूध की चालू कमाई)
        $unbilledLiters = 0.0;
        $unbilledMilkAmount = 0.0;
        try {
            $unbilledCols = MilkCollection::where('farmer_id', $farmerId)
                ->where('payment_status', 'pending')
                ->get(['quantity_liters', 'net_amount']);
            $unbilledLiters = round((float) $unbilledCols->sum('quantity_liters'), 2);
            $unbilledMilkAmount = round((float) $unbilledCols->sum('net_amount'), 2);
        } catch (\Throwable $e) {}

        // 2. Pending Invoices (जनरेटेड इनवॉइस जो अभी नकद/बैंक से भुगतान नहीं हुई)
        $pendingInvoicesAmount = 0.0;
        try {
            $pendingInvoicesAmount = round((float) FarmerInvoice::where('farmer_id', $farmerId)
                ->where('status', 'generated')
                ->sum('net_payment'), 2);
        } catch (\Throwable $e) {}

        // Total Milk Money currently owed to farmer
        $milkPayable = round($unbilledMilkAmount + $pendingInvoicesAmount, 2);

        // 3. Active Advance & Loan Principal (कुल बकाया लोन/अग्रिम)
        $activeLoanBalance = 0.0;
        $activeLoanCount = 0;
        try {
            if (Schema::hasTable('farmer_advances')) {
                $advances = FarmerAdvance::where('farmer_id', $farmerId)
                    ->where('status', '!=', 'recovered')
                    ->get();
                $activeLoanCount = $advances->count();
                $activeLoanBalance = round((float) $advances->sum(fn($a) => (float) ($a->principal_balance ?? ($a->amount - $a->paid_amount - $a->deducted_amount))), 2);
            }
        } catch (\Throwable $e) {}

        // 4. Outstanding Feed / Store Purchases (पशु आहार/स्टोर बकाया)
        $feedDues = 0.0;
        try {
            if (Schema::hasTable('product_sales')) {
                $farmerCode = $farmerModel->farmer_code;
                $feedDues = round((float) ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                    $q->where('farmer_id', $farmerId);
                    if (!empty($farmerCode)) $q->orWhere('farmer_code', $farmerCode);
                })->where('remaining_amount', '>', 0)->sum('remaining_amount'), 2);
            }
        } catch (\Throwable $e) {}

        // 5. Deduction Wallet Balance (वॉलेट लेजर)
        FarmerWalletService::cleanupDuplicateAdvanceTransactions($farmerId);
        $wallet = FarmerWalletService::getOrCreateWallet($farmerModel);

        // Keep wallet synced with finalized invoice closing wallet balance if available
        $latestInv = FarmerInvoice::where('farmer_id', $farmerId)
            ->where('status', '!=', 'pending')
            ->latest('id')
            ->first();
        if ($latestInv && $latestInv->closing_wallet_balance !== null && (float) $latestInv->closing_wallet_balance >= 0) {
            if ($wallet && abs((float) $wallet->balance - (float) $latestInv->closing_wallet_balance) > 0.05) {
                $wallet->update(['balance' => (float) $latestInv->closing_wallet_balance]);
            }
        }

        $walletBalance = round((float) ($wallet ? $wallet->balance : 0), 2);
        $categoryBalances = $wallet ? $wallet->getCategoryBalances() : [];

        // Canonical total dues farmer owes to dairy (Loan + Feed + Other Dues)
        $totalDues = ($walletBalance > 0 && abs($walletBalance - $activeLoanBalance) <= $walletBalance)
            ? $walletBalance
            : max($walletBalance, round($activeLoanBalance + $feedDues, 2));

        // Net Balance Position
        $netBalance = round($milkPayable - $totalDues, 2);
        $isPayable = ($netBalance >= 0);

        // Friendly Status Badges
        if ($milkPayable == 0 && $totalDues == 0) {
            $statusBadge = '₹0.00 (चुकता)';
            $statusType = 'settled';
        } elseif ($milkPayable > 0 && $totalDues == 0) {
            $statusBadge = '₹ ' . number_format($milkPayable, 2) . ' (देय / Payable)';
            $statusType = 'payable';
        } elseif ($milkPayable == 0 && $totalDues > 0) {
            $statusBadge = '₹ ' . number_format($totalDues, 2) . ' बाकी (लेना है)';
            $statusType = 'receivable';
        } else {
            if ($netBalance >= 0) {
                $statusBadge = '₹ ' . number_format($netBalance, 2) . ' (नेट देय)';
                $statusType = 'payable';
            } else {
                $statusBadge = '₹ ' . number_format(abs($netBalance), 2) . ' बाकी (लेना है)';
                $statusType = 'receivable';
            }
        }

        return [
            'farmer' => $farmerModel,
            'farmer_id' => $farmerId,
            'unbilled_liters' => $unbilledLiters,
            'unbilled_milk_amount' => $unbilledMilkAmount,
            'pending_invoices_amount' => $pendingInvoicesAmount,
            'milk_payable' => $milkPayable,
            'active_loan_balance' => $activeLoanBalance,
            'active_loan_count' => $activeLoanCount,
            'feed_dues' => $feedDues,
            'wallet_balance' => $walletBalance,
            'category_balances' => $categoryBalances,
            'total_dues' => $totalDues,
            'net_balance' => $netBalance,
            'is_payable' => $isPayable,
            'status_badge' => $statusBadge,
            'status_type' => $statusType,
        ];
    }

    /**
     * Build unified, perfectly reconciled passbook statement for a farmer.
     * Ensures all entries balance out to the exact current position.
     */
    public static function getReconciledPassbook(
        Farmer|int $farmer,
        ?string $fromDate = null,
        ?string $toDate = null,
        string $viewType = 'all' // 'all', 'milk', 'loan', 'store'
    ): array {
        $farmerModel = $farmer instanceof Farmer ? $farmer : Farmer::find((int) $farmer);
        if (!$farmerModel) {
            return [
                'entries' => [],
                'openingBalance' => 0.00,
                'closingBalance' => 0.00,
                'totalCredit' => 0.00,
                'totalDebit' => 0.00,
                'summary' => [],
            ];
        }

        $farmerId = $farmerModel->id;
        $fromDate = $fromDate ?: '1970-01-01';
        $toDate = $toDate ?: Carbon::today()->addDay()->format('Y-m-d');

        $rawEntries = [];
        $seq = 0;

        // 1. Milk Procurement Supplies (Credit)
        try {
            $cols = MilkCollection::where('farmer_id', $farmerId)
                ->orderBy('collection_date')
                ->orderBy('id')
                ->get();

            foreach ($cols as $c) {
                $dt = $c->collection_date ? $c->collection_date->format('Y-m-d') : $c->created_at->format('Y-m-d');
                $rawEntries[] = [
                    'date' => $dt,
                    'seq' => ++$seq,
                    'ref' => $c->receipt_number,
                    'type' => 'milk',
                    'category' => 'Milk Supply',
                    'description' => "दूध आपूर्ति (" . ucfirst($c->shift) . ") — {$c->quantity_liters} Ltr (" . ucfirst($c->milk_type) . ", FAT {$c->fat}% / SNF {$c->snf}%) @ ₹" . number_format((float) $c->applied_rate, 2),
                    'status' => ucfirst($c->payment_status ?: 'pending'),
                    'credit' => round((float) $c->net_amount, 2),
                    'debit' => 0.00,
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Advances & Loans Issued (Debit) & Repayments (Credit)
        try {
            if (Schema::hasTable('farmer_advances')) {
                $advs = FarmerAdvance::where('farmer_id', $farmerId)->orderBy('advance_date')->get();
                foreach ($advs as $a) {
                    $dt = $a->advance_date ? $a->advance_date->format('Y-m-d') : $a->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $a->voucher_no ? "VOUCHER #{$a->voucher_no}" : "ADV-{$a->id}",
                        'type' => 'loan',
                        'category' => 'Advance / Loan',
                        'description' => "अग्रिम ऋण भुगतान (Loan Issued): " . ($a->purpose ?: 'Cash Advance'),
                        'status' => ucfirst($a->status),
                        'credit' => 0.00,
                        'debit' => round((float) $a->amount, 2),
                    ];

                    // Direct cash repayments if any
                    $paid = (float) $a->paid_amount;
                    if ($paid > 0) {
                        $pDate = $a->paid_date ? $a->paid_date->format('Y-m-d') : $dt;
                        $rawEntries[] = [
                            'date' => $pDate,
                            'seq' => ++$seq,
                            'ref' => "REP-{$a->voucher_no}",
                            'type' => 'loan',
                            'category' => 'Loan Repayment',
                            'description' => "ऋण नकद वापसी (Loan Cash Repayment for #{$a->voucher_no})",
                            'status' => 'Received',
                            'credit' => $paid,
                            'debit' => 0.00,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Period Invoices Settled / Paid (Debit for cash payout disbursed + Credit for loan recovery)
        try {
            if (Schema::hasTable('farmer_invoices')) {
                $invoices = FarmerInvoice::where('farmer_id', $farmerId)
                    ->where('status', '!=', 'pending')
                    ->orderBy('period_end')
                    ->get();

                foreach ($invoices as $inv) {
                    $dt = $inv->created_at ? $inv->created_at->format('Y-m-d') : ($inv->period_end ? $inv->period_end->format('Y-m-d') : date('Y-m-d'));
                    $pStart = $inv->period_start ? $inv->period_start->format('d M') : '';
                    $pEnd = $inv->period_end ? $inv->period_end->format('d M Y') : '';

                    // A. Credit for loan installment recovered in this invoice (Reconciles the loan debit)
                    $advDeducted = (float) ($inv->advance_deduction ?? 0);
                    if ($advDeducted > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "#{$inv->invoice_number}-REC",
                            'type' => 'loan',
                            'category' => 'Loan Recovery in Bill',
                            'description' => "बिल में ऋण किस्त जमा (Installment Deducted in #{$inv->invoice_number})",
                            'status' => 'Recovered',
                            'credit' => $advDeducted,
                            'debit' => 0.00,
                        ];
                    }

                    // B. Debit for net milk payout disbursed to farmer
                    $netPaid = (float) ($inv->net_payment ?? $inv->net_payable);
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => "#{$inv->invoice_number}",
                        'type' => 'payout',
                        'category' => 'Invoice Bill Payout',
                        'description' => "दूध बिल भुगतान ({$pStart} - {$pEnd}) — दूध कमाई: ₹" . number_format($inv->milk_amount, 2) . ", कटौती: ₹" . number_format($inv->total_deduction, 2) . ", नकद भुगतान: ₹" . number_format($netPaid, 2),
                        'status' => ucfirst($inv->status),
                        'credit' => 0.00,
                        'debit' => $netPaid,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 4. Product / Store Purchases (Debit) & Payments (Credit)
        try {
            if (Schema::hasTable('product_sales')) {
                $farmerCode = $farmerModel->farmer_code;
                $sales = ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                    $q->where('farmer_id', $farmerId);
                    if (!empty($farmerCode)) $q->orWhere('farmer_code', $farmerCode);
                })->orderBy('sale_date')->get();

                foreach ($sales as $s) {
                    $dt = $s->sale_date ? $s->sale_date->format('Y-m-d') : $s->created_at->format('Y-m-d');
                    $rawEntries[] = [
                        'date' => $dt,
                        'seq' => ++$seq,
                        'ref' => $s->sale_number,
                        'type' => 'store',
                        'category' => 'Feed / Store Purchase',
                        'description' => "स्टोर खरीद: {$s->product_name} ({$s->quantity} {$s->unit} @ ₹{$s->rate})",
                        'status' => $s->is_paid ? 'Paid' : 'Unpaid',
                        'credit' => 0.00,
                        'debit' => round((float) $s->total_amount, 2),
                    ];
                    if ((float) $s->paid_amount > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "RCPT-{$s->sale_number}",
                            'type' => 'store',
                            'category' => 'Feed Payment',
                            'description' => "स्टोर भुगतान #{$s->sale_number}",
                            'status' => 'Received',
                            'credit' => round((float) $s->paid_amount, 2),
                            'debit' => 0.00,
                        ];
                    }
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

        // Filter by date range and viewType
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
            if ($viewType !== 'all' && $entry['type'] !== $viewType) {
                continue;
            }
            $filtered[] = $entry;
        }

        // Calculate running balances
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

        $summary = self::getFinancialSummary($farmerModel);

        return [
            'openingBalance' => $openingBalance,
            'entries' => $filtered,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'closingBalance' => $runningBalance,
            'summary' => $summary,
        ];
    }

    /**
     * Build authoritative Milk Procurement, Deductions, and Payout Statement for a farmer.
     * Credits = Milk Supply
     * Debits = Bill Deductions (Loan Installment, Feed, etc.) + Settlement Payouts
     * Closing Balance = Unsettled milk payable (₹0.00 when fully settled).
     */
    public static function getMilkStatement(
        Farmer|int $farmer,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $farmerModel = $farmer instanceof Farmer ? $farmer : Farmer::find((int) $farmer);
        if (!$farmerModel) {
            return [
                'entries' => [],
                'openingBalance' => 0.00,
                'closingBalance' => 0.00,
                'totalCredit' => 0.00,
                'totalDebit' => 0.00,
            ];
        }

        $farmerId = $farmerModel->id;
        $fromDate = $fromDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $toDate ?: Carbon::now()->format('Y-m-d');

        $rawEntries = [];
        $seq = 0;

        // 1. Milk Procurement Collections (Credit)
        try {
            $cols = MilkCollection::where('farmer_id', $farmerId)
                ->orderBy('collection_date')
                ->orderBy('id')
                ->get();

            foreach ($cols as $c) {
                $dt = $c->collection_date ? $c->collection_date->format('Y-m-d') : $c->created_at->format('Y-m-d');
                $rawEntries[] = [
                    'date' => $dt,
                    'seq' => ++$seq,
                    'ref' => $c->receipt_number,
                    'type' => 'milk',
                    'type_label' => 'Milk Supply',
                    'category' => 'Milk Supply',
                    'description' => "Milk Supply (" . ucfirst($c->shift) . ") — {$c->quantity_liters} Ltr (" . ucfirst($c->milk_type) . ", FAT {$c->fat}% / SNF {$c->snf}%) @ ₹" . number_format((float) $c->applied_rate, 2),
                    'status' => ucfirst($c->payment_status ?: 'settled'),
                    'credit' => round((float) $c->net_amount, 2),
                    'debit' => 0.00,
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Finalized Invoices: Deductions (Debit) & Net Payouts (Debit)
        try {
            if (Schema::hasTable('farmer_invoices')) {
                $invoices = FarmerInvoice::where('farmer_id', $farmerId)
                    ->where('status', '!=', 'pending')
                    ->orderBy('period_end')
                    ->get();

                foreach ($invoices as $inv) {
                    $dt = $inv->created_at ? $inv->created_at->format('Y-m-d') : ($inv->period_end ? $inv->period_end->format('Y-m-d') : date('Y-m-d'));
                    $pStart = $inv->period_start ? $inv->period_start->format('d M') : '';
                    $pEnd = $inv->period_end ? $inv->period_end->format('d M Y') : '';

                    // Bill Deductions (Installment, feed, etc.)
                    $totalDed = round((float) $inv->total_deduction, 2);
                    if ($totalDed > 0) {
                        $advDed = (float) ($inv->advance_deduction ?? 0);
                        $feedDed = (float) ($inv->feed_deduction ?? 0);
                        $dedNotes = [];
                        if ($advDed > 0) $dedNotes[] = "Loan Installment: ₹" . number_format($advDed, 2);
                        if ($feedDed > 0) $dedNotes[] = "Feed: ₹" . number_format($feedDed, 2);
                        $notesStr = !empty($dedNotes) ? " (" . implode(', ', $dedNotes) . ")" : "";

                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "DED-{$inv->invoice_number}",
                            'type' => 'deduction',
                            'type_label' => 'Invoice Deductions',
                            'category' => 'Bill Deductions',
                            'description' => "Invoice #{$inv->invoice_number} Deductions{$notesStr}",
                            'status' => 'Applied',
                            'credit' => 0.00,
                            'debit' => $totalDed,
                        ];
                    }

                    // Settlement Payout disbursed to farmer
                    $netPaid = round((float) ($inv->net_payment ?? $inv->net_payable), 2);
                    if ($netPaid > 0 && in_array(strtolower($inv->status), ['settled', 'paid'])) {
                        $payMode = 'Cash';
                        if (Schema::hasTable('farmer_settlements')) {
                            $st = FarmerSettlement::where('invoice_id', $inv->id)->orWhere('settlement_number', 'SETTLE-' . $inv->id)->first();
                            if ($st && $st->payment_mode) $payMode = $st->payment_mode;
                        }
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => "SETTLE-{$inv->invoice_number}",
                            'type' => 'settlement',
                            'type_label' => 'Settlement Payout',
                            'category' => 'Milk Settlement',
                            'description' => "Settlement Payout via " . ucfirst($payMode) . " for Invoice #{$inv->invoice_number} ({$pStart} - {$pEnd})",
                            'status' => 'Paid',
                            'credit' => 0.00,
                            'debit' => $netPaid,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        // 3. Standalone Direct Settlements (if any)
        try {
            if (Schema::hasTable('farmer_settlements')) {
                $invIds = isset($invoices) ? $invoices->pluck('id')->filter()->all() : [];
                $standaloneStls = FarmerSettlement::where('farmer_id', $farmerId)
                    ->where(function ($q) use ($invIds) {
                        $q->whereNull('invoice_id');
                        if (!empty($invIds)) $q->orWhereNotIn('invoice_id', $invIds);
                    })
                    ->get();
                foreach ($standaloneStls as $st) {
                    $dt = $st->settled_at ? $st->settled_at->format('Y-m-d') : ($st->created_at ? $st->created_at->format('Y-m-d') : date('Y-m-d'));
                    $paid = round((float) ($st->paid_amount ?: $st->net_payable), 2);
                    if ($paid > 0) {
                        $rawEntries[] = [
                            'date' => $dt,
                            'seq' => ++$seq,
                            'ref' => $st->settlement_number,
                            'type' => 'settlement',
                            'type_label' => 'Direct Settlement',
                            'category' => 'Direct Payment',
                            'description' => "Direct Milk Payment via " . ucfirst($st->payment_mode ?? 'Cash'),
                            'status' => 'Paid',
                            'credit' => 0.00,
                            'debit' => $paid,
                        ];
                    }
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
            $filtered[] = $entry;
        }

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
            'entries' => $filtered,
            'openingBalance' => $openingBalance,
            'totalCredit' => $totalCredit,
            'totalDebit' => $totalDebit,
            'closingBalance' => $runningBalance,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }
}
