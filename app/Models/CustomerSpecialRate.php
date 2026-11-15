<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSpecialRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'special_price',
        'rate_type',
        'delta_amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'special_price' => 'decimal:2',
        'delta_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get computed effective rate.
     * If variable: catalog_price + delta_amount
     * If fixed: special_price
     */
    public function getEffectiveRateAttribute(): float
    {
        if ($this->rate_type === 'variable' && $this->product) {
            return (float)max(0, $this->product->price + ($this->delta_amount ?? 0));
        }

        return (float)($this->special_price ?? 0);
    }
}
