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
        'cow_rate_chart_id',
        'cow_rate_value',
        'buffalo_rate_mode',
        'buffalo_fixed_rate',
        'buffalo_rate_chart_id',
        'buffalo_rate_value',
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
        'cow_rate_value' => 'decimal:2',
        'buffalo_fixed_rate' => 'decimal:2',
        'buffalo_rate_value' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function cowRateChart()
    {
        return $this->belongsTo(RateChart::class, 'cow_rate_chart_id');
    }

    public function buffaloRateChart()
    {
        return $this->belongsTo(RateChart::class, 'buffalo_rate_chart_id');
    }

    public function sales()
    {
        return $this->hasMany(MilkSale::class);
    }

    public function payments()
    {
        return $this->hasMany(BuyerPayment::class);
    }

    public function invoices()
    {
        return $this->hasMany(BuyerInvoice::class, 'buyer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate balance for this buyer:
     * Net Due = Total Sales - Total Received Payments (deduplicated) + Refunds
     */
    public function recalculateBalance(): float
    {
        $totalSales = (float) $this->sales()->where('status', 'completed')->sum('total_amount');

        // Deduplicated received calculation:
        // When a milk sale records paid_amount, MilkSaleController generates a DIR- BuyerPayment.
        // If DIR- payments exist in payments table, then payments()->where('type', 'received') already includes them.
        $receivedKhata = (float) $this->payments()->where('type', 'received')->sum('amount');
        $hasDirReceipts = $this->payments()->where('receipt_number', 'like', 'DIR-%')->exists();

        if ($hasDirReceipts) {
            $totalReceived = $receivedKhata;
        } else {
            $directPaidOnSales = (float) $this->sales()->where('status', 'completed')->sum('paid_amount');
            $totalReceived = $directPaidOnSales + $receivedKhata;
        }

        $refundsPaid = (float) $this->payments()->where('type', 'paid')->sum('amount');

        $balance = round($totalSales - $totalReceived + $refundsPaid, 2);

        $this->update(['current_balance' => $balance]);
        return $balance;
    }
}
