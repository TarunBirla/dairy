<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_code',
        'name',
        'phone',
        'email',
        'milk_type',
        'cow_rate_mode',
        'cow_fixed_rate',
        'buffalo_rate_mode',
        'buffalo_fixed_rate',
        'address',
        'taluka',
        'district',
        'details',
        'current_balance',
        'status',
        'created_by',
    ];

    protected $casts = [
        'cow_fixed_rate' => 'decimal:2',
        'buffalo_fixed_rate' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(MilkSale::class);
    }

    public function payments()
    {
        return $this->hasMany(BuyerPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate balance for this buyer:
     * Balance = Sum(total_amount of sales) - Sum(paid_amount of sales) - Sum(received payments) + Sum(paid refunds)
     */
    public function recalculateBalance(): float
    {
        $totalSales = (float) $this->sales()->where('status', 'completed')->sum('total_amount');
        $directPaidOnSales = (float) $this->sales()->where('status', 'completed')->sum('paid_amount');
        $receivedKhata = (float) $this->payments()->where('type', 'received')->sum('amount');
        $refundsPaid = (float) $this->payments()->where('type', 'paid')->sum('amount');

        // Net due by buyer = Total Sales - (Direct Paid on Sales + Separate Khata Received) + Refunds
        $balance = $totalSales - ($directPaidOnSales + $receivedKhata) + $refundsPaid;

        $this->update(['current_balance' => $balance]);
        return $balance;
    }
}
