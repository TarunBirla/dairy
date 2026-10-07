<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_date',
        'shift',
        'route_id',
        'delivery_boy_id',
        'customer_id',
        'subscription_id',
        'product_id',
        'quantity',
        'extra_quantity',
        'delivered_quantity',
        'unit_price',
        'total_amount',
        'status',
        'failure_reason',
        'cash_collected',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'quantity' => 'decimal:2',
        'extra_quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cash_collected' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }
}
