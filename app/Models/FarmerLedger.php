<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'transaction_date',
        'type', // credit, debit
        'amount',
        'balance',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
}
