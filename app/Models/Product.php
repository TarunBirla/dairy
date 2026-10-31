<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'code',
        'product_for',
        'product_type',
        'unit',
        'pack_size',
        'price',
        'subscription_price',
        'cost_price',
        'tax_percent',
        'current_stock',
        'min_stock_alert',
        'manage_stock',
        'allow_counter_sales',
        'show_on_customer_app',
        'in_stock',
        'allow_onetime_orders',
        'schedule_booking_cutoff',
        'booking_cutoff_morning_day',
        'booking_cutoff_morning_time',
        'booking_cutoff_evening_day',
        'booking_cutoff_evening_time',
        'allow_subscription',
        'allow_subscription_change',
        'allow_subscription_cancellation',
        'allow_booking_after_subscription',
        'subscription_cutoff_morning_day',
        'subscription_cutoff_morning_time',
        'subscription_cutoff_evening_day',
        'subscription_cutoff_evening_time',
        'image',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subscription_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'min_stock_alert' => 'decimal:2',
        'manage_stock' => 'boolean',
        'allow_counter_sales' => 'boolean',
        'show_on_customer_app' => 'boolean',
        'in_stock' => 'boolean',
        'allow_onetime_orders' => 'boolean',
        'schedule_booking_cutoff' => 'boolean',
        'allow_subscription' => 'boolean',
        'allow_subscription_change' => 'boolean',
        'allow_subscription_cancellation' => 'boolean',
        'allow_booking_after_subscription' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock_alert;
    }
}
