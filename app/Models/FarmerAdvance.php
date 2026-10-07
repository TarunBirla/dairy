<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'amount',
        'advance_date',
        'voucher_no',
        'purpose',
        'deducted_amount',
        'paid_amount',
        'paid_date',
        'interest_rate',
        'interest_balance',
        'payment_mode',
        'status',
        'notes',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'interest_balance' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function repayments()
    {
        return $this->hasMany(FarmerAdvanceRepayment::class);
    }

    public function getTotalPaidAttribute()
    {
        $paid = (float) $this->paid_amount;
        $deducted = (float) $this->deducted_amount;
        $repayments = (float) $this->repayments()->sum('amount');
        return max($paid, $deducted, $repayments);
    }

    public function getPrincipalBalanceAttribute()
    {
        return max(0, (float) $this->amount - $this->total_paid);
    }

    public function getTotalBalanceAttribute()
    {
        return $this->principal_balance + (float) $this->interest_balance;
    }
}
