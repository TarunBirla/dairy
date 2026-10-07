<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no',
        'vehicle_id',
        'driver_id',
        'advance_date',
        'amount',
        'interest_rate',
        'paid_amount',
        'balance_amount',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function repayments()
    {
        return $this->hasMany(VehicleAdvanceRepayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate paid and balance amounts from repayments
     */
    public function recalculate(): void
    {
        $paid = (float) $this->repayments()->sum('amount');
        $balance = max(0, (float) $this->amount - $paid);
        $status = $balance <= 0 ? 'settled' : 'active';

        $this->update([
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'status' => $status,
        ]);
    }
}
