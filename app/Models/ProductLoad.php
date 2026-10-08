<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_type',
        'date',
        'delivery_person_id',
        'delivery_person_name',
        'delivery_person_phone',
        'shift',
        'remark',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ProductLoadItem::class, 'product_load_id');
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
