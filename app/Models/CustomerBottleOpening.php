<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBottleOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'opening_count',
        'is_locked',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
