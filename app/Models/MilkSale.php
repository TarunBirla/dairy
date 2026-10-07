<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'buyer_id',
        'vehicle_id',
        'sale_date',
        'shift',
        'milk_type',
        'quantity_liters',
        'fat_percentage',
        'snf_percentage',
        'clr_reading',
        'rate_per_liter',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_mode',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity_liters' => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'snf_percentage' => 'decimal:2',
        'clr_reading' => 'decimal:2',
        'rate_per_liter' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(BuyerPayment::class);
    }
}
