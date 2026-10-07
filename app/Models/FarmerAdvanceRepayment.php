<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmerAdvanceRepayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmer_advance_id',
        'farmer_id',
        'repayment_date',
        'amount',
        'payment_mode',
        'remark',
        'recorded_by',
    ];

    protected $casts = [
        'repayment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function advance()
    {
        return $this->belongsTo(FarmerAdvance::class, 'farmer_advance_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
