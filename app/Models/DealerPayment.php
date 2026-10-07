<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dealer_id',
        'dealer_code',
        'dealer_name',
        'payment_date',
        'dues_amount',
        'pay_amount',
        'remaining_dues',
        'payment_mode',
        'comment',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'dues_amount' => 'decimal:2',
        'pay_amount' => 'decimal:2',
        'remaining_dues' => 'decimal:2',
    ];

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
