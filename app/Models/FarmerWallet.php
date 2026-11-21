<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class FarmerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FarmerWalletTransaction::class, 'wallet_id')->orderBy('id', 'desc');
    }

    /**
     * Get category-wise outstanding balance breakdown.
     * Credits increase outstanding; Debits decrease outstanding.
     * Cleans and unifies category names (strips legacy '(Wallet)', '(Standard)').
     */
    public function getCategoryBalances(): array
    {
        $breakdown = [];

        try {
            $raw = FarmerWalletTransaction::where('wallet_id', $this->id)
                ->select(
                    DB::raw("TRIM(REPLACE(REPLACE(REPLACE(category, ' (Wallet)', ''), ' (Standard)', ''), ' (Fixed)', '')) as clean_category"),
                    DB::raw("SUM(CASE WHEN transaction_type = 'credit' THEN amount WHEN transaction_type = 'debit' THEN -amount WHEN transaction_type = 'reversal' THEN amount ELSE 0 END) as net_balance")
                )
                ->groupBy(DB::raw("TRIM(REPLACE(REPLACE(REPLACE(category, ' (Wallet)', ''), ' (Standard)', ''), ' (Fixed)', ''))"))
                ->get();

            foreach ($raw as $row) {
                $bal = round((float) $row->net_balance, 2);
                if ($bal > 0.009) {
                    $cat = trim($row->clean_category ?: 'General');
                    $breakdown[$cat] = $bal;
                }
            }
        } catch (\Throwable $e) {
            // Bulletproof in-memory fallback
            $txs = FarmerWalletTransaction::where('wallet_id', $this->id)->get();
            $running = [];
            foreach ($txs as $tx) {
                $cleanCat = trim(str_replace([' (Wallet)', ' (Standard)', ' (Fixed)'], '', $tx->category ?: 'General'));
                if (!isset($running[$cleanCat])) $running[$cleanCat] = 0.0;
                if ($tx->transaction_type === 'credit' || $tx->transaction_type === 'reversal') {
                    $running[$cleanCat] += (float) $tx->amount;
                } elseif ($tx->transaction_type === 'debit') {
                    $running[$cleanCat] -= (float) $tx->amount;
                }
            }
            foreach ($running as $cat => $amt) {
                $amt = round($amt, 2);
                if ($amt > 0.009) {
                    $breakdown[$cat] = $amt;
                }
            }
        }

        return $breakdown;
    }

    /**
     * Recalculate and update the wallet balance from transactions ledger.
     */
    public function recalculateBalance(): float
    {
        $breakdown = $this->getCategoryBalances();
        $net = array_sum($breakdown);

        if ($net <= 0) {
            $rawNet = (float) FarmerWalletTransaction::where('wallet_id', $this->id)
                ->sum(DB::raw("CASE WHEN transaction_type = 'credit' THEN amount WHEN transaction_type = 'debit' THEN -amount WHEN transaction_type = 'reversal' THEN amount ELSE 0 END"));
            $net = max(0, round($rawNet, 2));
        } else {
            $net = round($net, 2);
        }

        $this->update(['balance' => $net]);

        return $net;
    }
}
