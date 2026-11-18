<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmerWalletTransaction extends Model
{
    use HasFactory;

    const TYPE_CREDIT = 'credit';     // Increases outstanding balance
    const TYPE_DEBIT = 'debit';       // Decreases outstanding balance (e.g. invoice deduction)
    const TYPE_REVERSAL = 'reversal'; // Reverses a prior debit or credit

    protected $fillable = [
        'wallet_id',
        'farmer_id',
        'farmer_invoice_id',
        'transaction_type',
        'category',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'reversal_of_transaction_id',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(FarmerWallet::class, 'wallet_id');
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FarmerInvoice::class, 'farmer_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function originalTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_transaction_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_transaction_id');
    }

    public function scopeCredits($query)
    {
        return $query->where('transaction_type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('transaction_type', self::TYPE_DEBIT);
    }

    public function scopeReversals($query)
    {
        return $query->where('transaction_type', self::TYPE_REVERSAL);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
