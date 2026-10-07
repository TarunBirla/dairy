<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_number',
        'farmer_id',
        'period_start',
        'period_end',
        'total_liters',
        'gross_amount',
        'bonus_amount',
        'deduction_amount',
        'advance_recovered',
        'net_payable',
        'paid_amount',
        'payment_mode',
        'payment_reference',
        'status',
        'settled_by',
        'settled_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'settled_at' => 'datetime',
        'total_liters' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'advance_recovered' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function settler()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }
}
