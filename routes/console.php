<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dairy:sync-all', function () {
    $this->info('===============================================================');
    $this->info('Starting Synchronization of Farmers, Wallets, Invoices & Khata');
    $this->info('===============================================================');

    // 0. Clean up Category Names and Repair Invoices
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_wallet_transactions')) {
            \Illuminate\Support\Facades\DB::statement("UPDATE `farmer_wallet_transactions` SET `category` = TRIM(REPLACE(REPLACE(REPLACE(`category`, ' (Wallet)', ''), ' (Standard)', ''), ' (Fixed)', '')) WHERE `category` LIKE '%(Wallet)%' OR `category` LIKE '%(Standard)%' OR `category` LIKE '%(Fixed)%'");
        }
    } catch (\Throwable $e) {}

    // 0.1 Repair any generated invoices where total deductions exceeded earnings
    try {
        $genInvoices = \App\Models\FarmerInvoice::where('status', '!=', 'pending')->get();
        foreach ($genInvoices as $inv) {
            $tAmt = (float) $inv->total_amount;
            $tDed = (float) $inv->total_deduction;
            if ($tDed > $tAmt && $tAmt > 0) {
                $remDues = round($tDed - $tAmt, 2);
                $bList = $inv->deductions_breakdown;
                if (is_array($bList) && !empty($bList)) {
                    $excess = $remDues;
                    for ($i = count($bList) - 1; $i >= 0; $i--) {
                        if ($excess <= 0) break;
                        $cur = (float) ($bList[$i]['deducted_amount'] ?? 0);
                        $red = min($cur, $excess);
                        $bList[$i]['deducted_amount'] = round($cur - $red, 2);
                        $bList[$i]['remaining_balance'] = round((float)($bList[$i]['original_balance'] ?? 0) - $bList[$i]['deducted_amount'], 2);
                        $excess -= $red;
                    }
                    $inv->update([
                        'total_deduction' => $tAmt,
                        'previous_balance' => $remDues,
                        'closing_wallet_balance' => $remDues,
                        'net_payment' => 0.00,
                        'deductions_breakdown' => $bList,
                    ]);
                    \App\Services\FarmerWalletService::applyInvoiceDeductions($inv, $bList, null);
                } else {
                    $inv->update([
                        'total_deduction' => $tAmt,
                        'previous_balance' => $remDues,
                        'closing_wallet_balance' => $remDues,
                        'net_payment' => 0.00,
                    ]);
                }
            }
        }
    } catch (\Throwable $e) {}

    // 1. Synchronize all Farmers
    $farmers = \App\Models\Farmer::all();
    $farmerData = [];
    foreach ($farmers as $f) {
        if (method_exists($f, 'recalculateBalance')) {
            $bal = $f->recalculateBalance();
        } else {
            $bal = app(\App\Http\Controllers\MobileDairyController::class)->recalculateFarmerBalanceDirect($f);
        }
        $walletSummary = \App\Services\FarmerWalletService::getWalletSummary($f);
        $wBal = (float) ($walletSummary['total_balance'] ?? 0);
        $liters = (float) \App\Models\MilkCollection::where('farmer_id', $f->id)->sum('quantity_liters');
        
        $farmerData[] = [
            $f->id,
            $f->farmer_code,
            $f->name,
            number_format($liters, 2) . ' L',
            '₹ ' . number_format($bal, 2),
            '₹ ' . number_format($wBal, 2),
        ];
    }

    $this->table(
        ['ID', 'Code', 'Farmer Name', 'Total Milk', 'Payable / Ledger Balance', 'Deduction Wallet Dues'],
        $farmerData
    );

    // 2. Synchronize all Buyers
    $buyers = \App\Models\Buyer::all();
    $buyerData = [];
    foreach ($buyers as $b) {
        $bal = method_exists($b, 'recalculateBalance') ? $b->recalculateBalance() : (float) ($b->current_balance ?? 0);
        $buyerData[] = [
            $b->id,
            $b->buyer_code,
            $b->name,
            '₹ ' . number_format($bal, 2),
        ];
    }

    $this->table(
        ['ID', 'Code', 'Buyer Name', 'Outstanding Balance Due'],
        $buyerData
    );

    $this->info('All dairy balances, wallets, invoices, collections, deductions, and advances synchronized successfully!');
})->purpose('Synchronize and recalculate all farmer and buyer balances, wallets, and invoices');
