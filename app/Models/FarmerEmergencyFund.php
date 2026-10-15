<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmerEmergencyFund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmer_id',
        'request_no',
        'request_date',
        'amount',
        'reason',
        'status', // pending, approved, disbursed, rejected
        'approved_by',
        'disbursed_at',
        'recovered_amount',
        'payment_mode',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'disbursed_at' => 'datetime',
        'amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return (float) max(0, $this->amount - $this->recovered_amount);
    }

    public function getIsFullyRecoveredAttribute(): bool
    {
        return (float) $this->recovered_amount >= (float) $this->amount;
    }
}
