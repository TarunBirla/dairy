<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Farmer;

class CenterAdditionDeductionService
{
    /**
     * Get all configured addition types from Center Information with default fallbacks.
     *
     * @return array
     */
    public static function getAdditionTypes(): array
    {
        $raw = SystemSetting::get('center_addition_types');
        $items = json_decode($raw ?? '[]', true) ?: [];

        if (empty($items)) {
            $items = [
                ['name' => 'Incentive', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => '/ltr'],
                ['name' => 'Incentive Day', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-'],
                ['name' => 'Subsidy', 'value' => '1.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '1.00', 'unit' => '/ltr'],
                ['name' => 'Transport', 'value' => '1.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '1.00', 'unit' => '/ltr'],
                ['name' => 'Other Payment Cr', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-'],
                ['name' => 'Commission', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr'],
            ];
        }

        return array_map([self::class, 'normalizeItem'], $items);
    }

    /**
     * Get all configured deduction types from Center Information with default fallbacks.
     *
     * @return array
     */
    public static function getDeductionTypes(): array
    {
        $raw = SystemSetting::get('center_deduction_types');
        $items = json_decode($raw ?? '[]', true) ?: [];

        if (empty($items)) {
            $items = [
                ['name' => 'Anamat', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Food', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Grocery', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Etc', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Building fund', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Grant', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Transport-Dr', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Management Charges C', 'value' => '0.00/Invoice', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/Invoice', 'auto_reset' => false],
                ['name' => 'DPMCU', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Penalty', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Doctor', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Contribution', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Diesel Expenses', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Stationary', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Handling', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Management Charges', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'TDS SEC 194Q', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Milk Sale', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Rate Difference', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Other Payment', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Management Other', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Round Off', 'value' => 'Round down', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => 'Round down', 'auto_reset' => false],
                ['name' => 'Bank Andvance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Fixed Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'In Hand Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
            ];
        }

        return array_map([self::class, 'normalizeItem'], $items);
    }

    /**
     * Normalize an addition or deduction item structure.
     */
    protected static function normalizeItem(array $item): array
    {
        $name = trim($item['name'] ?? '');
        $val = trim($item['value'] ?? '');
        $type = trim($item['farmer_type'] ?? '');

        // Determine if farmer_wise
        $isFarmerWise = ($type === 'farmer_wise') || (strcasecmp($val, 'Farmer Wise') === 0) || (strcasecmp($type, 'farmer-wise') === 0);
        $farmerType = $isFarmerWise ? 'farmer_wise' : 'all_farmers';

        // Extract amount
        $amount = (float) ($item['amount'] ?? 0);
        if ($amount == 0 && !empty($val) && !$isFarmerWise && $val !== '-') {
            $numOnly = preg_replace('/[^0-9.]/', '', $val);
            if ($numOnly !== '') {
                $amount = (float) $numOnly;
            }
        }

        // Determine unit
        $unit = trim($item['unit'] ?? '');
        if (empty($unit) || $unit === '-' || $unit === 'Farmer Wise') {
            if (str_contains($val, '/ltr') || str_contains($val, '/liter')) {
                $unit = '/ltr';
            } elseif (str_contains($val, '/Invoice') || str_contains($val, '/invoice')) {
                $unit = '/Invoice';
            } elseif (str_contains($val, '%')) {
                $unit = '%';
            } else {
                $lowerName = strtolower($name);
                if (str_contains($lowerName, 'incentive') || str_contains($lowerName, 'subsidy') || str_contains($lowerName, 'transport') || str_contains($lowerName, 'commission') || str_contains($lowerName, 'anamat')) {
                    $unit = '/ltr';
                } else {
                    $unit = 'Fixed';
                }
            }
        }

        return [
            'name' => $name,
            'farmer_type' => $farmerType,
            'is_farmer_wise' => $isFarmerWise,
            'is_all_farmers' => !$isFarmerWise,
            'amount' => $amount,
            'unit' => $unit,
            'value' => $val ?: ($isFarmerWise ? 'Farmer Wise' : ($amount > 0 ? "{$amount}{$unit}" : '-')),
            'auto_reset' => !empty($item['auto_reset']),
        ];
    }

    /**
     * Get only addition types configured as Farmer Wise.
     *
     * @return array
     */
    public static function getFarmerWiseAdditions(): array
    {
        return array_values(array_filter(self::getAdditionTypes(), fn($item) => $item['is_farmer_wise']));
    }

    /**
     * Get only deduction types configured as Farmer Wise.
     *
     * @return array
     */
    public static function getFarmerWiseDeductions(): array
    {
        return array_values(array_filter(self::getDeductionTypes(), fn($item) => $item['is_farmer_wise']));
    }

    /**
     * Get only addition types configured for All Farmers with active amount > 0.
     *
     * @return array
     */
    public static function getAllFarmersAdditions(): array
    {
        return array_values(array_filter(self::getAdditionTypes(), fn($item) => $item['is_all_farmers'] && $item['amount'] > 0));
    }

    /**
     * Get only deduction types configured for All Farmers with active amount > 0.
     *
     * @return array
     */
    public static function getAllFarmersDeductions(): array
    {
        return array_values(array_filter(self::getDeductionTypes(), fn($item) => $item['is_all_farmers'] && $item['amount'] > 0));
    }

    /**
     * Calculate all additions and deductions for a collection entry.
     *
     * @param Farmer|null $farmer
     * @param float $liters
     * @param float $grossAmount
     * @return array
     */
    public static function calculateForCollection(?Farmer $farmer, float $liters, float $grossAmount = 0.0): array
    {
        $additionBreakdown = [];
        $totalAdditions = 0.0;

        foreach (self::getAdditionTypes() as $item) {
            $rate = 0.0;
            if ($item['is_farmer_wise']) {
                $rate = $farmer ? $farmer->getFarmerWiseRate($item['name']) : 0.0;
            } else {
                $rate = (float) $item['amount'];
            }

            if ($rate <= 0) {
                continue;
            }

            $lineAmount = self::computeAmount($rate, $item['unit'], $item['name'], $liters, $grossAmount);
            if ($lineAmount > 0) {
                $additionBreakdown[] = [
                    'name' => $item['name'],
                    'type' => $item['farmer_type'],
                    'rate' => $rate,
                    'unit' => $item['unit'],
                    'amount' => $lineAmount,
                ];
                $totalAdditions += $lineAmount;
            }
        }

        $deductionBreakdown = [];
        $totalDeductions = 0.0;

        foreach (self::getDeductionTypes() as $item) {
            $rate = 0.0;
            if ($item['is_farmer_wise']) {
                $rate = $farmer ? $farmer->getFarmerWiseRate($item['name']) : 0.0;
            } else {
                $rate = (float) $item['amount'];
            }

            if ($rate <= 0) {
                continue;
            }

            $lineAmount = self::computeAmount($rate, $item['unit'], $item['name'], $liters, $grossAmount);
            if ($lineAmount > 0) {
                $deductionBreakdown[] = [
                    'name' => $item['name'],
                    'type' => $item['farmer_type'],
                    'rate' => $rate,
                    'unit' => $item['unit'],
                    'amount' => $lineAmount,
                ];
                $totalDeductions += $lineAmount;
            }
        }

        $totalAdditions = round($totalAdditions, 2);
        $totalDeductions = round($totalDeductions, 2);
        $netAmount = max(0, round($grossAmount + $totalAdditions - $totalDeductions, 2));

        return [
            'additions' => $additionBreakdown,
            'total_additions' => $totalAdditions,
            'deductions' => $deductionBreakdown,
            'total_deductions' => $totalDeductions,
            'gross_amount' => round($grossAmount, 2),
            'net_amount' => $netAmount,
        ];
    }

    /**
     * Compute total rupee amount based on unit type and category name.
     */
    public static function computeAmount(float $rate, string $unit, string $name, float $liters, float $grossAmount = 0.0): float
    {
        $u = strtolower(trim($unit));
        $n = strtolower(trim($name));

        if (str_contains($u, '%') || str_contains($u, 'percent')) {
            return round(($rate / 100) * $grossAmount, 2);
        }

        if (str_contains($u, 'invoice') || str_contains($u, 'fixed') || ($u === 'farmer wise' && (str_contains($n, 'fund') || str_contains($n, 'grant') || str_contains($n, 'fee') || str_contains($n, 'stationary') || str_contains($n, 'advance') || str_contains($n, 'food') || str_contains($n, 'grocery')))) {
            return round($rate, 2);
        }

        if (str_contains($u, 'ltr') || str_contains($u, 'liter') || str_contains($n, 'incentive') || str_contains($n, 'subsidy') || str_contains($n, 'transport') || str_contains($n, 'commission') || str_contains($n, 'anamat')) {
            return round($rate * $liters, 2);
        }

        // If unit doesn't mention liter and is not empty, treat as fixed
        if (!empty($u) && !str_contains($u, 'ltr') && !str_contains($u, 'liter')) {
            return round($rate, 2);
        }

        return round($rate * $liters, 2);
    }
}
