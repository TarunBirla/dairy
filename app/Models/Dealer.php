<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'details',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(ProductPurchase::class);
    }

    public function payments()
    {
        return $this->hasMany(DealerPayment::class);
    }

    /**
     * Total amount purchased from this dealer
     */
    public function getTotalPurchasedAttribute()
    {
        return (float) $this->purchases()->sum('total_amount');
    }

    /**
     * Total amount paid to this dealer across purchase receipts and payment entries
     */
    public function getTotalPaidAttribute()
    {
        $purchasePaid = (float) $this->purchases()->sum('paid_amount');
        $directPayments = (float) $this->payments()->sum('pay_amount');
        return $purchasePaid + $directPayments;
    }

    /**
     * Current outstanding dues payable to dealer
     */
    public function getDuesAmountAttribute()
    {
        $dues = $this->total_purchased - $this->total_paid;
        return max(0, $dues);
    }
}
