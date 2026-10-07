<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'farmer_id',
        'collection_center_id',
        'operator_id',
        'collection_date',
        'shift',
        'milk_type',
        'quantity_liters',
        'fat',
        'snf',
        'clr',
        'calculated_rate',
        'applied_rate',
        'gross_amount',
        'bonus',
        'deduction',
        'net_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'quantity_liters' => 'decimal:2',
        'fat' => 'decimal:2',
        'snf' => 'decimal:2',
        'clr' => 'decimal:2',
        'calculated_rate' => 'decimal:2',
        'applied_rate' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function collectionCenter()
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
