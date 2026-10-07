<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_code',
        'customer_id',
        'product_id',
        'route_id',
        'quantity',
        'frequency',
        'custom_days',
        'shift',
        'unit_price',
        'start_date',
        'end_date',
        'status',
        'pause_from',
        'pause_until',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'pause_from' => 'date',
        'pause_until' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function isPausedOn(\DateTimeInterface $date): bool
    {
        if ($this->status === 'paused') {
            return true;
        }

        if ($this->pause_from && $this->pause_until) {
            $formatted = $date->format('Y-m-d');
            return $formatted >= $this->pause_from->format('Y-m-d') && $formatted <= $this->pause_until->format('Y-m-d');
        }

        return false;
    }
}
