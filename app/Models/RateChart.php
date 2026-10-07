<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'milk_type',
        'format',
        'type',
        'calculation_type',
        'starting_amount',
        'fixed_rate',
        'base_rate',
        'fat_steps',
        'snf_steps',
        'fat_rules',
        'snf_rules',
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
        'starting_amount' => 'decimal:2',
        'fixed_rate' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'min_fat' => 'decimal:2',
        'max_fat' => 'decimal:2',
        'min_snf' => 'decimal:2',
        'max_snf' => 'decimal:2',
        'fat_factor' => 'decimal:2',
        'snf_factor' => 'decimal:2',
        'fat_steps' => 'array',
        'snf_steps' => 'array',
        'fat_rules' => 'array',
        'snf_rules' => 'array',
    ];

    public function slabs()
    {
        return $this->hasMany(RateChartSlab::class);
    }

    public function farmers()
    {
        return $this->hasMany(Farmer::class);
    }

    public function collectionCenters()
    {
        return $this->hasMany(CollectionCenter::class);
    }

    public function corrections()
    {
        return $this->hasMany(RateCorrection::class);
    }

    public function calculateRate(float $fat, float $snf): float
    {
        // 1. Fixed Rate format
        if ($this->format === 'fixed_rate' || $this->calculation_type === 'flat') {
            $fixed = (float) ($this->fixed_rate > 0 ? $this->fixed_rate : $this->base_rate);
            return round($fixed, 2);
        }

        // 2. Matrix Slab logic
        if ($this->calculation_type === 'matrix' || $this->type === 'matrix_slab') {
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

        // 3. Step/Incremental or Formula logic
        $fatSteps = is_array($this->fat_steps) ? $this->fat_steps : (json_decode($this->fat_steps ?? '[]', true) ?: []);
        $snfSteps = is_array($this->snf_steps) ? $this->snf_steps : (json_decode($this->snf_steps ?? '[]', true) ?: []);
        $fatRules = is_array($this->fat_rules) ? $this->fat_rules : (json_decode($this->fat_rules ?? '[]', true) ?: []);
        $snfRules = is_array($this->snf_rules) ? $this->snf_rules : (json_decode($this->snf_rules ?? '[]', true) ?: []);

        $rate = 0.0;
        if (!empty($fatSteps) || !empty($snfSteps)) {
            $rate = (float) ($this->starting_amount > 0 ? $this->starting_amount : $this->base_rate);
            foreach ($fatSteps as $step) {
                if ($fat >= (float) ($step['step'] ?? 0)) {
                    $rate += (float) ($step['amount'] ?? 0);
                }
            }
            foreach ($snfSteps as $step) {
                if ($snf >= (float) ($step['step'] ?? 0)) {
                    $rate += (float) ($step['amount'] ?? 0);
                }
            }
        } else {
            // Standard Indian Dairy Formula: (Fat * fat_factor) + (SNF * snf_factor)
            $fatFactor = (float) ($this->fat_factor ?: 6.5);
            $snfFactor = (float) ($this->snf_factor ?: 4.2);
            $rate = ($fat * $fatFactor) + ($snf * $snfFactor);
        }

        // Apply FAT Bonus / Penalty
        if (!empty($fatRules)) {
            foreach ($fatRules as $rule) {
                $from = (float) ($rule['from'] ?? 0);
                $to = (float) ($rule['to'] ?? 999);
                $amt = (float) ($rule['amount'] ?? 0);
                if ($fat >= $from && $fat <= $to) {
                    if (($rule['type'] ?? 'bonus') === 'penalty') {
                        $rate -= $amt;
                    } else {
                        $rate += $amt;
                    }
                }
            }
        }

        // Apply SNF Bonus / Penalty
        if (!empty($snfRules)) {
            foreach ($snfRules as $rule) {
                $from = (float) ($rule['from'] ?? 0);
                $to = (float) ($rule['to'] ?? 999);
                $amt = (float) ($rule['amount'] ?? 0);
                if ($snf >= $from && $snf <= $to) {
                    if (($rule['type'] ?? 'bonus') === 'penalty') {
                        $rate -= $amt;
                    } else {
                        $rate += $amt;
                    }
                }
            }
        }

        return max(0.00, round($rate, 2));
    }
}
