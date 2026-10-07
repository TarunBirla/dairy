<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'branch_id',
        'counter_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'payment_mode',
        'payment_status',
        'operator_id',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function counter()
    {
        return $this->belongsTo(PosCounter::class, 'counter_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
