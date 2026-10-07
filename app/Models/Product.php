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
        'in_stock',
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
        'in_stock' => 'boolean',
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
