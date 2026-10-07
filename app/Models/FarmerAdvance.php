<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'amount',
        'advance_date',
        'purpose',
        'deducted_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}
