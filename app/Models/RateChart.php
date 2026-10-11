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

    public function calculateRate(float $fat = 0, float $snf = 0, float $clr = 0, ?string $collectionType = null): float
    {
        // Auto-derive SNF or CLR when one is provided and the other is 0
        if ($snf <= 0 && $clr > 0 && $fat > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        } elseif ($clr <= 0 && $snf > 0 && $fat > 0) {
            $clr = round(($snf - (0.21 * $fat) - 0.36) * 4.0, 2);
        }

        // 1. Fixed Rate format or Liter Only collection mode
        if ($this->format === 'fixed_rate' || $this->calculation_type === 'flat' || $collectionType === 'Liter Only') {
            $fixed = (float) ($this->fixed_rate > 0 ? $this->fixed_rate : ($this->starting_amount > 0 ? $this->starting_amount : $this->base_rate));
            return round($fixed > 0 ? $fixed : 40.0, 2);
        }

        // 2. Matrix Slab logic
        if ($this->calculation_type === 'matrix' || $this->type === 'matrix_slab') {
            $slabQuery = $this->slabs();
            if ($fat > 0) {
                $slabQuery->where('fat_from', '<=', $fat)->where('fat_to', '>=', $fat);
            }
            if ($snf > 0 && $collectionType !== 'FAT only') {
                $slabQuery->where('snf_from', '<=', $snf)->where('snf_to', '>=', $snf);
            }
            $slab = $slabQuery->first();

            if ($slab) {
                return round((float) $slab->rate, 2);
            }
        }

        // 3. Step/Incremental or Formula logic
        $fatSteps = is_array($this->fat_steps) ? $this->fat_steps : (json_decode($this->fat_steps ?? '[]', true) ?: []);
        $snfSteps = is_array($this->snf_steps) ? $this->snf_steps : (json_decode($this->snf_steps ?? '[]', true) ?: []);
        $fatRules = is_array($this->fat_rules) ? $this->fat_rules : (json_decode($this->fat_rules ?? '[]', true) ?: []);
        $snfRules = is_array($this->snf_rules) ? $this->snf_rules : (json_decode($this->snf_rules ?? '[]', true) ?: []);

        // Determine whether secondary steps/rules use CLR or SNF
        $firstSnfFrom = !empty($snfSteps) ? (float) ($snfSteps[0]['from'] ?? $snfSteps[0]['step'] ?? 0) : 0;
        $usesClrForSecondary = ($this->format === 'fat_clr' || $this->format === 'clr_only' || $firstSnfFrom >= 15 || in_array($collectionType, ['FAT + CLR', 'CLR only']));
        $secondaryVal = $usesClrForSecondary ? ($clr > 0 ? $clr : $snf) : ($snf > 0 ? $snf : $clr);

        // Determine if only FAT or only CLR is active based on collection setting
        $isFatOnlyMode = ($collectionType === 'FAT only' || $this->format === 'fat_only');
        $isClrOnlyMode = ($collectionType === 'CLR only' || $this->format === 'clr_only');

        $rate = 0.0;
        if ($this->type === 'rate_per_kg') {
            $fatRate = 0.0;
            if (!$isClrOnlyMode && !empty($fatSteps) && $fat > 0) {
                foreach ($fatSteps as $step) {
                    $from = (float) ($step['from'] ?? $step['step'] ?? 0);
                    $to = (float) ($step['to'] ?? 999);
                    if ($fat >= $from && $fat <= $to) {
                        $amt = (float) ($step['amount'] ?? 0);
                        $fatRate = $amt / 100.0;
                        break;
                    }
                }
                if ($fatRate == 0.0 && !empty($fatSteps[0]['amount'])) {
                    $fatRate = ((float) $fatSteps[0]['amount']) / 100.0;
                }
            }

            $secondaryRate = 0.0;
            if (!$isFatOnlyMode && $secondaryVal > 0) {
                $stepsToSearch = !empty($snfSteps) ? $snfSteps : ($isClrOnlyMode ? $fatSteps : []);
                foreach ($stepsToSearch as $step) {
                    $from = (float) ($step['from'] ?? $step['step'] ?? 0);
                    $to = (float) ($step['to'] ?? 999);
                    if ($secondaryVal >= $from && $secondaryVal <= $to) {
                        $amt = (float) ($step['amount'] ?? 0);
                        $secondaryRate = $amt / 100.0;
                        break;
                    }
                }
                if ($secondaryRate == 0.0 && !empty($stepsToSearch[0]['amount'])) {
                    $secondaryRate = ((float) $stepsToSearch[0]['amount']) / 100.0;
                }
            }

            if ($isClrOnlyMode) {
                $rate = $clr * $secondaryRate;
            } elseif ($isFatOnlyMode) {
                $rate = $fat * $fatRate;
            } else {
                $rate = ($fat * $fatRate) + ($secondaryVal * $secondaryRate);
            }
        } elseif (!empty($fatSteps) || !empty($snfSteps)) {
            $rate = (float) ($this->starting_amount > 0 ? $this->starting_amount : $this->base_rate);

            if (!$isClrOnlyMode && $fat > 0) {
                foreach ($fatSteps as $step) {
                    if (isset($step['from']) && isset($step['to'])) {
                        $from = (float) $step['from'];
                        $to = (float) $step['to'];
                        $amt = (float) ($step['amount'] ?? 0);
                        if ($fat > $from) {
                            $pts = min($fat, $to) - $from;
                            $rate += $pts * ($amt / 0.1);
                        }
                    } elseif ($fat >= (float) ($step['step'] ?? $step['from'] ?? 0)) {
                        $rate += (float) ($step['amount'] ?? 0);
                    }
                }
            }

            if (!$isFatOnlyMode && $secondaryVal > 0) {
                $stepsToSearch = !empty($snfSteps) ? $snfSteps : ($isClrOnlyMode ? $fatSteps : []);
                foreach ($stepsToSearch as $step) {
                    if (isset($step['from']) && isset($step['to'])) {
                        $from = (float) $step['from'];
                        $to = (float) $step['to'];
                        $amt = (float) ($step['amount'] ?? 0);
                        if ($secondaryVal > $from) {
                            $pts = min($secondaryVal, $to) - $from;
                            $rate += $pts * ($amt / 0.1);
                        }
                    } elseif ($secondaryVal >= (float) ($step['step'] ?? $step['from'] ?? 0)) {
                        $rate += (float) ($step['amount'] ?? 0);
                    }
                }
            }
        } else {
            // Standard Indian Dairy Formula fallback
            $fatFactor = (float) ($this->fat_factor ?: 6.5);
            $snfFactor = (float) ($this->snf_factor ?: 4.2);

            if ($isClrOnlyMode) {
                $rate = $clr * 1.45;
            } elseif ($isFatOnlyMode) {
                // If SNF is not used in FAT only mode, scale by base_rate/min_fat if available or fatFactor
                if ($this->base_rate > 0 && $this->min_fat > 0) {
                    $rate = ($fat / (float) $this->min_fat) * (float) $this->base_rate;
                } else {
                    $rate = $fat * ($fatFactor > 7 ? $fatFactor : 10.0);
                }
            } elseif ($collectionType === 'FAT + CLR' && $snf <= 0) {
                $derivedSnf = ($clr > 0 && $fat > 0) ? (($clr / 4.0) + (0.21 * $fat) + 0.36) : 8.5;
                $rate = ($fat * $fatFactor) + ($derivedSnf * $snfFactor);
            } else {
                $rate = ($fat * $fatFactor) + ($snf * $snfFactor);
            }
        }

        // Apply FAT Bonus / Penalty
        if (!$isClrOnlyMode && !empty($fatRules) && $fat > 0) {
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

        // Apply SNF / CLR Bonus / Penalty
        if (!$isFatOnlyMode && !empty($snfRules) && $secondaryVal > 0) {
            foreach ($snfRules as $rule) {
                $from = (float) ($rule['from'] ?? 0);
                $to = (float) ($rule['to'] ?? 999);
                $amt = (float) ($rule['amount'] ?? 0);
                if ($secondaryVal >= $from && $secondaryVal <= $to) {
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
