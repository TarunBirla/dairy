<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_number',
        'purchase_date',
        'shift',
        'dealer_id',
        'dealer_code',
        'dealer_name',
        'product_id',
        'product_name',
        'unit',
        'quantity',
        'rate',
        'sale_rate',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'advance_amount',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'sale_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
    ];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
