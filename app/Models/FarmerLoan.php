<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'principal_amount',
        'installments_count',
        'installment_amount',
        'total_repaid',
        'remaining_amount',
        'start_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'principal_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}
