<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BuyerInvoice extends Model
{
    use HasFactory;

    protected $table = 'buyer_invoices';

    protected $fillable = [
        'invoice_number',
        'buyer_id',
        'period_start',
        'period_end',
        'total_quantity',
        'milk_amount',
        'cash_received',
        'discount_amount',
        'other_charges',
        'previous_balance',
        'net_receivable',
        'sales_breakdown',
        'status',
        'is_edited',
        'payment_mode',
        'payment_reference',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_quantity' => 'decimal:2',
        'milk_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'net_receivable' => 'decimal:2',
        'is_edited' => 'boolean',
        'sales_breakdown' => 'array',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function milkSales(): HasMany
    {
        return $this->hasMany(MilkSale::class, 'buyer_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a buyer has a locked (finalized/generated) invoice on a given date.
     */
    public static function getLockingInvoice(int $buyerId, string $date): ?self
    {
        return self::where('buyer_id', $buyerId)
            ->where('status', '!=', 'pending')
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->first();
    }
}
