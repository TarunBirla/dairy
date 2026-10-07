<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'entry_date',
        'deduction_type',
        'transaction_type',
        'amount',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        $types = [
            'cattle_feed' => 'Cattle Feed (पशु आहार)',
            'medicine' => 'Medicine (दवाई)',
            'ghee_butter' => 'Ghee / Butter',
            'doctor_fee' => 'Doctor Fee',
            'insurance' => 'Insurance',
            'advance_recovery' => 'Advance Recovery',
            'store_purchase' => 'Store Purchase',
            'equipment' => 'Equipment / बर्तन',
            'other' => 'Other (अन्य)',
        ];

        return $types[$this->deduction_type] ?? ucfirst(str_replace('_', ' ', $this->deduction_type));
    }
}
