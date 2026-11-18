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
     */
    public function getCategoryBalances(): array
    {
        $breakdown = [];

        $raw = FarmerWalletTransaction::where('wallet_id', $this->id)
            ->select(
                'category',
                DB::raw("SUM(CASE WHEN transaction_type = 'credit' THEN amount WHEN transaction_type = 'debit' THEN -amount WHEN transaction_type = 'reversal' THEN amount ELSE 0 END) as net_balance")
            )
            ->groupBy('category')
            ->having('net_balance', '>', 0)
            ->orderBy('net_balance', 'desc')
            ->get();

        foreach ($raw as $row) {
            $breakdown[$row->category] = round((float) $row->net_balance, 2);
        }

        return $breakdown;
    }

    /**
     * Recalculate and update the wallet balance from transactions ledger.
     */
    public function recalculateBalance(): float
    {
        $net = (float) FarmerWalletTransaction::where('wallet_id', $this->id)
            ->sum(DB::raw("CASE WHEN transaction_type = 'credit' THEN amount WHEN transaction_type = 'debit' THEN -amount WHEN transaction_type = 'reversal' THEN amount ELSE 0 END"));

        $net = max(0, round($net, 2));
        $this->update(['balance' => $net]);

        return $net;
    }
}
