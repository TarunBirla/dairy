<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'slip_number',
        'farmer_id',
        'collection_center_id',
        'operator_id',
        'collected_by',
        'collection_date',
        'shift',
        'milk_type',
        'quantity_liters',
        'fat',
        'fat_percentage',
        'snf',
        'snf_percentage',
        'clr',
        'calculated_rate',
        'applied_rate',
        'gross_amount',
        'bonus',
        'bonus_amount',
        'deduction',
        'deduction_amount',
        'net_amount',
        'payment_status',
        'notes',
        'deduction_reason',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'quantity_liters' => 'decimal:2',
        'fat' => 'decimal:2',
        'snf' => 'decimal:2',
        'clr' => 'decimal:2',
        'calculated_rate' => 'decimal:2',
        'applied_rate' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    protected $appends = [
        'slip_number',
        'fat_percentage',
        'snf_percentage',
        'bonus_amount',
        'deduction_amount',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->receipt_number)) {
                $nextId = (static::max('id') ?? 0) + 1001;
                $model->receipt_number = 'COL-' . $nextId;
            }
        });
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function collectionCenter()
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Backward-compatible alias for operator relationship
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    public function getSlipNumberAttribute()
    {
        return $this->attributes['receipt_number'] ?? null;
    }

    public function setSlipNumberAttribute($value)
    {
        $this->attributes['receipt_number'] = $value;
    }

    public function getFatPercentageAttribute()
    {
        return $this->attributes['fat'] ?? null;
    }

    public function setFatPercentageAttribute($value)
    {
        $this->attributes['fat'] = $value;
    }

    public function getSnfPercentageAttribute()
    {
        return $this->attributes['snf'] ?? null;
    }

    public function setSnfPercentageAttribute($value)
    {
        $this->attributes['snf'] = $value;
    }

    public function getBonusAmountAttribute()
    {
        return $this->attributes['bonus'] ?? 0;
    }

    public function setBonusAmountAttribute($value)
    {
        $this->attributes['bonus'] = $value;
    }

    public function getDeductionAmountAttribute()
    {
        return $this->attributes['deduction'] ?? 0;
    }

    public function setDeductionAmountAttribute($value)
    {
        $this->attributes['deduction'] = $value;
    }

    public function setCollectedByAttribute($value)
    {
        $this->attributes['operator_id'] = $value;
    }

    public function setDeductionReasonAttribute($value)
    {
        $this->attributes['notes'] = $value;
    }
}
