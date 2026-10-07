<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAdvanceRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'vehicle_advance_id',
        'repayment_date',
        'amount',
        'payment_mode',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'repayment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function advance()
    {
        return $this->belongsTo(VehicleAdvance::class, 'vehicle_advance_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
