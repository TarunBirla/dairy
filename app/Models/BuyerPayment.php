<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'buyer_id',
        'milk_sale_id',
        'payment_date',
        'type',
        'amount',
        'payment_mode',
        'transaction_reference',
        'comment',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function sale()
    {
        return $this->belongsTo(MilkSale::class, 'milk_sale_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
