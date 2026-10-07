<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BottleTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'issued_count',
        'returned_count',
        'broken_count',
        'deposit_rate_per_bottle',
        'total_deposit_amount',
        'balance_bottles',
        'notes',
    ];

    protected $casts = [
        'deposit_rate_per_bottle' => 'decimal:2',
        'total_deposit_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
