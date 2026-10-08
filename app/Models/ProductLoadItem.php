<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLoadItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_load_id',
        'product_id',
        'product_name',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function load()
    {
        return $this->belongsTo(ProductLoad::class, 'product_load_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
