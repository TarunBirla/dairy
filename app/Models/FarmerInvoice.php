<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'farmer_id',
        'period_start',
        'period_end',
        'total_quantity',
        'milk_amount',
        'stationary_deduction',
        'feed_deduction',
        'advance_deduction',
        'other_deduction',
        'total_deduction',
        'total_credit',
        'total_amount',
        'previous_balance',
        'net_payment',
        'status',
        'payment_mode',
        'payment_reference',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_quantity' => 'decimal:2',
        'milk_amount' => 'decimal:2',
        'stationary_deduction' => 'decimal:2',
        'feed_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'net_payment' => 'decimal:2',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get milk collection records within invoice billing period
     */
    public function getMilkCollectionsAttribute()
    {
        return MilkCollection::where('farmer_id', $this->farmer_id)
            ->whereBetween('collection_date', [$this->period_start, $this->period_end])
            ->orderBy('collection_date', 'asc')
            ->orderBy('shift', 'asc')
            ->get();
    }

    /**
     * Get deductions within invoice billing period
     */
    public function getDeductionsListAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
            return FarmerDeduction::where('farmer_id', $this->farmer_id)
                ->whereBetween('entry_date', [$this->period_start, $this->period_end])
                ->latest('entry_date')
                ->get();
        }
        return collect();
    }

    /**
     * Get cattle feed purchases within invoice period
     */
    public function getCattleFeedsListAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
            return FarmerDeduction::where('farmer_id', $this->farmer_id)
                ->where('deduction_type', 'cattle_feed')
                ->whereBetween('entry_date', [$this->period_start, $this->period_end])
                ->latest('entry_date')
                ->get();
        }
        return collect();
    }
}
