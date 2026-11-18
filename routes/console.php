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

    // 1. Synchronize all Farmers
    $farmers = \App\Models\Farmer::all();
    $farmerData = [];
    foreach ($farmers as $f) {
        $bal = $f->recalculateBalance();
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
        $bal = $b->recalculateBalance();
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
