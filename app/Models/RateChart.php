<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'milk_type',
        'calculation_type',
        'base_rate',
        'min_fat',
        'max_fat',
        'min_snf',
        'max_snf',
        'fat_factor',
        'snf_factor',
        'effective_date',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'effective_date' => 'date',
        'base_rate' => 'decimal:2',
        'min_fat' => 'decimal:2',
        'max_fat' => 'decimal:2',
        'min_snf' => 'decimal:2',
        'max_snf' => 'decimal:2',
        'fat_factor' => 'decimal:2',
        'snf_factor' => 'decimal:2',
    ];

    public function slabs()
    {
        return $this->hasMany(RateChartSlab::class);
    }

    public function calculateRate(float $fat, float $snf): float
    {
        if ($this->calculation_type === 'matrix') {
            $slab = $this->slabs()
                ->where('fat_from', '<=', $fat)
                ->where('fat_to', '>=', $fat)
                ->where('snf_from', '<=', $snf)
                ->where('snf_to', '>=', $snf)
                ->first();

            if ($slab) {
                return (float) $slab->rate;
            }
        }

        if ($this->calculation_type === 'flat') {
            return (float) $this->base_rate;
        }

        // Standard Indian Dairy Formula: (Fat * fat_factor) + (SNF * snf_factor) OR Base + additions
        $rate = ($fat * (float)$this->fat_factor) + ($snf * (float)$this->snf_factor);
        return round($rate, 2);
    }
}
