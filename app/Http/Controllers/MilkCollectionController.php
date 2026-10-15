<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MilkCollection;
use App\Models\Farmer;
use App\Models\CollectionCenter;
use App\Models\RateChart;
use App\Models\FarmerLedger;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Carbon\Carbon;

class MilkCollectionController extends Controller
{
    /**
     * Build Collection & Rate configuration from SystemSetting (Center Information)
     */
    protected function getCollectionConfig(): array
    {
        $collectionType = SystemSetting::get('collection_type', 'FAT only');
        $milkTypeSetting = SystemSetting::get('milk_type', 'Cow, Buffalo');
        $shiftSetting = SystemSetting::get('collection_shift', 'Morning + Evening');
        $showPreviousCollection = SystemSetting::get('show_previous_collection', "Yesterday's both shift");
        $routeWiseCollection = SystemSetting::get('route_wise_collection', 'On');
        $rateChartStatus = SystemSetting::get('rate_chart_status', 'On');
        $farmerWiseRate = SystemSetting::get('farmer_wise_rate', 'As per Rate Chart');
        $shiftMorningBonus = (float) SystemSetting::get('shift_wise_morning', '0.00');
        $shiftEveningBonus = (float) SystemSetting::get('shift_wise_evening', '0.00');

        $weighingFormat = SystemSetting::get('weighing_scale_format', 'Automatic + Manual');
        $fatFormat = SystemSetting::get('fat_format', 'Automatic + Manual');
        $snfFormat = SystemSetting::get('snf_format', 'Automatic + Manual');
        $clrFormat = SystemSetting::get('clr_lacto_format', 'Automatic + Manual');

        $centerDetails = json_decode(SystemSetting::get('center_details', '{}'), true) ?: [];
        $centerVehicleRoute = (!empty($centerDetails['vehicle_route']) && trim($centerDetails['vehicle_route']) !== '-')
            ? trim($centerDetails['vehicle_route'])
            : null;

        $additionTypes = \App\Services\CenterAdditionDeductionService::getAdditionTypes();
        $deductionTypes = \App\Services\CenterAdditionDeductionService::getDeductionTypes();

        $perLiterAddition = 0.0;
        foreach ($additionTypes as $add) {
            if ($add['is_all_farmers'] && (str_contains(strtolower($add['unit'] ?? ''), 'ltr') || str_contains(strtolower($add['unit'] ?? ''), 'liter'))) {
                $perLiterAddition += (float) $add['amount'];
            }
        }

        $perLiterDeduction = 0.0;
        foreach ($deductionTypes as $ded) {
            if ($ded['is_all_farmers'] && (str_contains(strtolower($ded['unit'] ?? ''), 'ltr') || str_contains(strtolower($ded['unit'] ?? ''), 'liter'))) {
                $perLiterDeduction += (float) $ded['amount'];
            }
        }

        $showFat = in_array($collectionType, [
            'FAT only',
            'FAT + SNF',
            'FAT + CLR',
            'FAT + CLR + Auto SNF',
            'FAT + SNF + Auto CLR',
        ]);

        $showClr = in_array($collectionType, [
            'CLR only',
            'FAT + CLR',
            'FAT + CLR + Auto SNF',
            'FAT + SNF + Auto CLR',
        ]);

        $showSnf = in_array($collectionType, [
            'FAT + SNF',
            'FAT + CLR + Auto SNF',
            'FAT + SNF + Auto CLR',
        ]);

        $autoSnf = ($collectionType === 'FAT + CLR + Auto SNF');
        $autoClr = ($collectionType === 'FAT + SNF + Auto CLR');

        if ($milkTypeSetting === 'Cow') {
            $allowedMilkTypes = ['cow'];
            $defaultMilkType = 'cow';
        } elseif ($milkTypeSetting === 'Buffalo') {
            $allowedMilkTypes = ['buffalo'];
            $defaultMilkType = 'buffalo';
        } else {
            $allowedMilkTypes = ['cow', 'buffalo'];
            $defaultMilkType = 'cow';
        }

        $autoShift = (date('H') >= 14) ? 'evening' : 'morning';
        if ($shiftSetting === 'Morning') {
            $allowedShifts = ['morning'];
            $defaultShift = 'morning';
        } elseif ($shiftSetting === 'Evening') {
            $allowedShifts = ['evening'];
            $defaultShift = 'evening';
        } else {
            $allowedShifts = ['morning', 'evening'];
            $defaultShift = $autoShift;
        }

        return [
            'collection_type' => $collectionType,
            'milk_type_setting' => $milkTypeSetting,
            'allowed_milk_types' => $allowedMilkTypes,
            'default_milk_type' => $defaultMilkType,
            'collection_shift_setting' => $shiftSetting,
            'allowed_shifts' => $allowedShifts,
            'default_shift' => $defaultShift,
            'show_previous_collection' => $showPreviousCollection,
            'route_wise_collection' => $routeWiseCollection,
            'center_vehicle_route' => $centerVehicleRoute,
            'rate_chart_status' => $rateChartStatus,
            'farmer_wise_rate' => $farmerWiseRate,
            'shift_wise_morning' => $shiftMorningBonus,
            'shift_wise_evening' => $shiftEveningBonus,
            'weighing_scale_format' => $weighingFormat,
            'fat_format' => $fatFormat,
            'snf_format' => $snfFormat,
            'clr_lacto_format' => $clrFormat,
            'show_fat' => $showFat,
            'show_clr' => $showClr,
            'show_snf' => $showSnf,
            'auto_snf' => $autoSnf,
            'auto_clr' => $autoClr,
            'per_liter_addition' => round($perLiterAddition, 2),
            'per_liter_deduction' => round($perLiterDeduction, 2),
            'addition_types' => $additionTypes,
            'deduction_types' => $deductionTypes,
        ];
    }

    /**
     * Resolve milk rate based on Collection Settings, Rate Chart Settings, Farmer Override & Charges
     */
    protected function resolveCollectionRate(
        float $fat,
        float $snf,
        float $clr,
        string $milkType,
        ?Farmer $farmer = null,
        ?int $centerId = null,
        string $shift = 'morning'
    ): array {
        $config = $this->getCollectionConfig();
        $collectionType = $config['collection_type'];

        // Normalize parameters according to active collection_type
        if (!$config['show_fat']) {
            $fat = 0.0;
        }
        if ($config['auto_snf'] && $fat > 0 && $clr > 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        } elseif ($config['auto_clr'] && $fat > 0 && $snf > 0) {
            $clr = round(($snf - (0.21 * $fat) - 0.36) * 4.0, 2);
        }
        if (!$config['show_snf']) {
            $snf = 0.0;
        }
        if (!$config['show_clr']) {
            $clr = 0.0;
        }

        $chartName = 'Standard Dairy Formula';
        $rate = 0.0;

        // 1. Check Farmer Custom Override according to farmer_wise_rate setting
        if ($farmer && $farmer->custom_rate_override > 0 && $config['farmer_wise_rate'] !== 'As per Rate Chart') {
            if ($config['farmer_wise_rate'] === 'Fat Wise' && $fat > 0) {
                $rate = round($fat * (float) $farmer->custom_rate_override, 2);
                $chartName = "Farmer Fat Wise (₹{$farmer->custom_rate_override}/FAT)";
            } else {
                $rate = round((float) $farmer->custom_rate_override, 2);
                $chartName = "Custom Farmer Rate (₹" . number_format($rate, 2) . ")";
            }
        } elseif ($farmer && $farmer->custom_rate_override > 0 && $config['rate_chart_status'] === 'Off') {
            $rate = round((float) $farmer->custom_rate_override, 2);
            $chartName = "Custom Farmer Rate (₹" . number_format($rate, 2) . ")";
        } else {
            // 2. Match RateChart according to MilkType + Farmer -> Center -> Format -> Default
            $chart = null;

            if ($config['rate_chart_status'] !== 'Off') {
                // Always prefer exact milk_type match first so Cow never picks a Buffalo chart
                if ($farmer && $farmer->rate_chart_id) {
                    $chart = RateChart::where('id', $farmer->rate_chart_id)
                        ->where('milk_type', $milkType)
                        ->where('status', 'active')
                        ->first();
                }

                if (!$chart && ($centerId || ($farmer && $farmer->collection_center_id))) {
                    $cid = $centerId ?: $farmer->collection_center_id;
                    $center = CollectionCenter::find($cid);
                    if ($center && $center->rate_chart_id) {
                        $chart = RateChart::where('id', $center->rate_chart_id)
                            ->where('milk_type', $milkType)
                            ->where('status', 'active')
                            ->first();
                    }
                }

                if (!$chart) {
                    // Map collection_type to preferred RateChart format
                    $preferredFormat = match ($collectionType) {
                        'FAT only' => 'fat_only',
                        'FAT + CLR', 'CLR only' => 'fat_clr',
                        'Liter Only' => 'fixed_rate',
                        default => 'fat_snf',
                    };

                    $chart = RateChart::where('milk_type', $milkType)
                        ->where('status', 'active')
                        ->where('format', $preferredFormat)
                        ->orderByDesc('is_default')
                        ->latest('id')
                        ->first();

                    if (!$chart) {
                        $chart = RateChart::where('milk_type', $milkType)
                            ->where('status', 'active')
                            ->orderByDesc('is_default')
                            ->latest('id')
                            ->first()
                            ?? RateChart::whereIn('milk_type', [$milkType, 'mixed', 'both'])
                                ->where('status', 'active')
                                ->orderByDesc('is_default')
                                ->latest('id')
                                ->first();
                    }
                }
            }

            if ($chart) {
                $rate = $chart->calculateRate($fat, $snf, $clr, $collectionType);
                $chartName = $chart->name;
                $chartId = $chart->id;
                $effectiveDate = $chart->effective_date ? $chart->effective_date->format('d/m/Y') : date('d/m/Y');

                $formatLabel = match ($chart->format) {
                    'fat_only' => 'FAT Only',
                    'fat_clr' => 'FAT + CLR',
                    'fixed_rate' => 'Fixed Rate',
                    default => 'FAT + SNF',
                };
                $typeLabel = match ($chart->type) {
                    'rate_per_kg' => 'Rate Per Kg',
                    'increase_per_point' => 'Increase / 0.1 Pt',
                    'matrix_slab' => 'Matrix Slab',
                    'flat' => 'Flat Rate',
                    default => ($chart->calculation_type === 'flat' ? 'Flat Rate' : 'Formula (FAT×' . ($chart->fat_factor ?: 6.5) . ' + SNF×' . ($chart->snf_factor ?: 4.2) . ')'),
                };
                $rateStructure = ($collectionType === 'Liter Only')
                    ? "Liter Only • Fixed ₹" . number_format($rate, 2) . "/Ltr"
                    : "{$formatLabel} • {$typeLabel}";

                $chartDetails = [
                    'id' => $chart->id,
                    'name' => $chart->name,
                    'milk_type' => ucfirst($chart->milk_type),
                    'format' => $formatLabel,
                    'type' => $typeLabel,
                    'effective_date' => $effectiveDate,
                    'base_rate' => (float) ($chart->fixed_rate > 0 ? $chart->fixed_rate : ($chart->starting_amount > 0 ? $chart->starting_amount : $chart->base_rate)),
                    'fat_factor' => (float) ($chart->fat_factor ?: 6.5),
                    'snf_factor' => (float) ($chart->snf_factor ?: 4.2),
                    'fat_steps' => is_array($chart->fat_steps) ? $chart->fat_steps : (json_decode($chart->fat_steps ?? '[]', true) ?: []),
                    'snf_steps' => is_array($chart->snf_steps) ? $chart->snf_steps : (json_decode($chart->snf_steps ?? '[]', true) ?: []),
                    'fat_rules' => is_array($chart->fat_rules) ? $chart->fat_rules : (json_decode($chart->fat_rules ?? '[]', true) ?: []),
                    'snf_rules' => is_array($chart->snf_rules) ? $chart->snf_rules : (json_decode($chart->snf_rules ?? '[]', true) ?: []),
                ];
            } else {
                // 3. Fallback formula according to collection_type and milk_type
                if ($collectionType === 'Liter Only') {
                    $rate = ($milkType === 'buffalo') ? 55.00 : 38.00;
                } elseif ($collectionType === 'FAT only') {
                    $rate = round($fat * ($milkType === 'buffalo' ? 10.50 : 9.50), 2);
                } elseif ($collectionType === 'CLR only') {
                    $rate = round($clr * ($milkType === 'buffalo' ? 1.85 : 1.45), 2);
                } else {
                    $effectiveSnf = $snf > 0 ? $snf : (($clr > 0 && $fat > 0) ? (($clr / 4.0) + (0.21 * $fat) + 0.36) : 8.5);
                    $rate = ($milkType === 'buffalo')
                        ? (($fat * 7.2) + ($effectiveSnf * 4.5))
                        : (($fat * 6.8) + ($effectiveSnf * 4.1));
                    $rate = round($rate, 2);
                }
                $chartName = ucfirst($milkType) . ' Collection Rate';
                $chartId = null;
                $effectiveDate = date('d/m/Y');
                $rateStructure = $collectionType . ' • Standard Rate';
                $chartDetails = null;
            }
        }

        if (!isset($effectiveDate)) {
            $effectiveDate = date('d/m/Y');
            $rateStructure = $collectionType . ' • Custom Farmer Override';
            $chartDetails = null;
        }

        // 4. Apply Shift-wise Rate Bonus from Rate Chart Settings
        $shiftBonus = ($shift === 'evening') ? $config['shift_wise_evening'] : $config['shift_wise_morning'];
        if ($shiftBonus != 0) {
            $rate = max(0.0, round($rate + $shiftBonus, 2));
            $sign = $shiftBonus > 0 ? '+' : '';
            $chartName .= " ({$sign}₹" . number_format($shiftBonus, 2) . " {$shift})";
        }

        return [
            'rate' => round($rate, 2),
            'fat' => $fat,
            'snf' => $snf,
            'clr' => $clr,
            'chart_id' => $chartId ?? null,
            'chart_name' => $chartName,
            'effective_date' => $effectiveDate,
            'rate_structure' => $rateStructure,
            'chart_details' => $chartDetails,
            'collection_type' => $collectionType,
            'per_liter_addition' => $config['per_liter_addition'],
            'per_liter_deduction' => $config['per_liter_deduction'],
        ];
    }

    /**
     * Compute Shift Summary (Cow, Buffalo, Total B+C) & Previous Collection (Yesterday + 5-Day Avg Fat)
     */
    protected function getShiftAndPreviousStats(string $date, string $shift, ?int $farmerId = null): array
    {
        try {
            $targetDate = Carbon::parse($date);
        } catch (\Throwable $e) {
            $targetDate = Carbon::today();
        }
        $dateStr = $targetDate->format('Y-m-d');
        $shift = in_array($shift, ['morning', 'evening']) ? $shift : 'morning';

        // 1. Shift Summary for Date & Shift
        $shiftBase = MilkCollection::whereDate('collection_date', $dateStr)->where('shift', $shift);

        $cowQuery = (clone $shiftBase)->where('milk_type', 'cow');
        $cowMembers = (clone $cowQuery)->distinct('farmer_id')->count('farmer_id');
        $cowMilk = round((float) (clone $cowQuery)->sum('quantity_liters'), 2);
        $cowFat = round((float) ((clone $cowQuery)->avg('fat') ?? 0), 1);
        $cowSnf = round((float) ((clone $cowQuery)->avg('snf') ?? 0), 1);
        $cowAmount = round((float) (clone $cowQuery)->sum('net_amount'), 2);

        $bufQuery = (clone $shiftBase)->where('milk_type', 'buffalo');
        $bufMembers = (clone $bufQuery)->distinct('farmer_id')->count('farmer_id');
        $bufMilk = round((float) (clone $bufQuery)->sum('quantity_liters'), 2);
        $bufFat = round((float) ((clone $bufQuery)->avg('fat') ?? 0), 1);
        $bufSnf = round((float) ((clone $bufQuery)->avg('snf') ?? 0), 1);
        $bufAmount = round((float) (clone $bufQuery)->sum('net_amount'), 2);

        $totalMembers = (clone $shiftBase)->distinct('farmer_id')->count('farmer_id');
        $totalMilk = round((float) (clone $shiftBase)->sum('quantity_liters'), 2);
        $totalFat = round((float) ((clone $shiftBase)->avg('fat') ?? 0), 1);
        $totalSnf = round((float) ((clone $shiftBase)->avg('snf') ?? 0), 1);
        $totalAmount = round((float) (clone $shiftBase)->sum('net_amount'), 2);
        $sampleNo = (clone $shiftBase)->count() + 1;

        // 2. Previous Collection (Yesterday Morning, Yesterday Evening, Previous Shift, 5-Day Avg Fat)
        $showPrevSetting = SystemSetting::get('show_previous_collection', "Yesterday's both shift");
        $yesterday = $targetDate->copy()->subDay();
        $yesterdayStr = $yesterday->format('Y-m-d');
        $fiveDaysStart = $targetDate->copy()->subDays(5)->format('Y-m-d');

        $prevShiftDateObj = ($shift === 'evening') ? $targetDate->copy() : $yesterday->copy();
        $prevShiftDateStr = $prevShiftDateObj->format('Y-m-d');
        $prevShiftType = ($shift === 'evening') ? 'morning' : 'evening';

        if ($farmerId) {
            $mCol = MilkCollection::where('farmer_id', $farmerId)
                ->whereDate('collection_date', $yesterdayStr)
                ->where('shift', 'morning')
                ->latest('id')
                ->first();
            $eCol = MilkCollection::where('farmer_id', $farmerId)
                ->whereDate('collection_date', $yesterdayStr)
                ->where('shift', 'evening')
                ->latest('id')
                ->first();
            $pCol = MilkCollection::where('farmer_id', $farmerId)
                ->whereDate('collection_date', $prevShiftDateStr)
                ->where('shift', $prevShiftType)
                ->latest('id')
                ->first();

            $morningText = $mCol
                ? number_format((float) $mCol->quantity_liters, 1) . ' L | FAT: ' . number_format((float) ($mCol->fat ?? 0), 1) . '% | CLR: ' . number_format((float) ($mCol->clr ?? 0), 1) . ' (₹' . number_format((float) $mCol->net_amount, 2) . ')'
                : 'No entry';
            $eveningText = $eCol
                ? number_format((float) $eCol->quantity_liters, 1) . ' L | FAT: ' . number_format((float) ($eCol->fat ?? 0), 1) . '% | CLR: ' . number_format((float) ($eCol->clr ?? 0), 1) . ' (₹' . number_format((float) $eCol->net_amount, 2) . ')'
                : 'No entry';
            $prevShiftText = $pCol
                ? number_format((float) $pCol->quantity_liters, 1) . ' L | FAT: ' . number_format((float) ($pCol->fat ?? 0), 1) . '% | CLR: ' . number_format((float) ($pCol->clr ?? 0), 1) . ' (₹' . number_format((float) $pCol->net_amount, 2) . ')'
                : 'No entry';

            $avgFat5Days = round((float) (MilkCollection::where('farmer_id', $farmerId)
                ->whereBetween('collection_date', [$fiveDaysStart, $dateStr])
                ->where('fat', '>', 0)
                ->avg('fat') ?? 0), 2);
            $avgClr5Days = round((float) (MilkCollection::where('farmer_id', $farmerId)
                ->whereBetween('collection_date', [$fiveDaysStart, $dateStr])
                ->where('clr', '>', 0)
                ->avg('clr') ?? 0), 1);
        } else {
            $mQuery = MilkCollection::whereDate('collection_date', $yesterdayStr)->where('shift', 'morning');
            $eQuery = MilkCollection::whereDate('collection_date', $yesterdayStr)->where('shift', 'evening');
            $pQuery = MilkCollection::whereDate('collection_date', $prevShiftDateStr)->where('shift', $prevShiftType);

            $mLtr = (float) (clone $mQuery)->sum('quantity_liters');
            $mFat = (float) ((clone $mQuery)->avg('fat') ?? 0);
            $mClr = (float) ((clone $mQuery)->avg('clr') ?? 0);
            $mAmt = (float) (clone $mQuery)->sum('net_amount');

            $eLtr = (float) (clone $eQuery)->sum('quantity_liters');
            $eFat = (float) ((clone $eQuery)->avg('fat') ?? 0);
            $eClr = (float) ((clone $eQuery)->avg('clr') ?? 0);
            $eAmt = (float) (clone $eQuery)->sum('net_amount');

            $pLtr = (float) (clone $pQuery)->sum('quantity_liters');
            $pFat = (float) ((clone $pQuery)->avg('fat') ?? 0);
            $pClr = (float) ((clone $pQuery)->avg('clr') ?? 0);
            $pAmt = (float) (clone $pQuery)->sum('net_amount');

            $morningText = $mLtr > 0
                ? number_format($mLtr, 1) . ' L | FAT: ' . number_format($mFat, 1) . '% | CLR: ' . number_format($mClr, 1) . ' (₹' . number_format($mAmt, 2) . ')'
                : 'No entry';
            $eveningText = $eLtr > 0
                ? number_format($eLtr, 1) . ' L | FAT: ' . number_format($eFat, 1) . '% | CLR: ' . number_format($eClr, 1) . ' (₹' . number_format($eAmt, 2) . ')'
                : 'No entry';
            $prevShiftText = $pLtr > 0
                ? number_format($pLtr, 1) . ' L | FAT: ' . number_format($pFat, 1) . '% | CLR: ' . number_format($pClr, 1) . ' (₹' . number_format($pAmt, 2) . ')'
                : 'No entry';

            $avgFat5Days = round((float) (MilkCollection::whereBetween('collection_date', [$fiveDaysStart, $dateStr])
                ->where('fat', '>', 0)
                ->avg('fat') ?? 0), 2);
            $avgClr5Days = round((float) (MilkCollection::whereBetween('collection_date', [$fiveDaysStart, $dateStr])
                ->where('clr', '>', 0)
                ->avg('clr') ?? 0), 1);
        }

        return [
            'sample_no' => $sampleNo,
            'shift_summary' => [
                'date_label' => $dateStr . ' - ' . ucfirst($shift),
                'cow' => [
                    'members' => $cowMembers,
                    'milk' => number_format($cowMilk, 2, '.', ''),
                    'avg_fat' => number_format($cowFat, 1, '.', ''),
                    'avg_snf' => number_format($cowSnf, 1, '.', ''),
                    'amount' => number_format($cowAmount, 2, '.', ''),
                ],
                'buffalo' => [
                    'members' => $bufMembers,
                    'milk' => number_format($bufMilk, 2, '.', ''),
                    'avg_fat' => number_format($bufFat, 1, '.', ''),
                    'avg_snf' => number_format($bufSnf, 1, '.', ''),
                    'amount' => number_format($bufAmount, 2, '.', ''),
                ],
                'total' => [
                    'members' => $totalMembers,
                    'milk' => number_format($totalMilk, 2, '.', ''),
                    'avg_fat' => number_format($totalFat, 1, '.', ''),
                    'avg_snf' => number_format($totalSnf, 1, '.', ''),
                    'amount' => number_format($totalAmount, 2, '.', ''),
                ],
            ],
            'previous_collection' => [
                'mode' => $showPrevSetting,
                'yesterday_date' => $yesterday->format('d/m/Y'),
                'morning' => $morningText,
                'evening' => $eveningText,
                'prev_shift_date' => $prevShiftDateObj->format('d/m/Y'),
                'prev_shift_type' => $prevShiftType,
                'prev_shift_text' => $prevShiftText,
                'avg_fat_5_days' => number_format($avgFat5Days, 2, '.', '') . '%',
                'avg_clr_5_days' => number_format($avgClr5Days, 1, '.', ''),
            ],
        ];
    }

    /**
     * Milk Collections Listing & Quick Modal Dashboard
     */
    public function index(Request $request)
    {
        $query = MilkCollection::with(['farmer.route', 'collectionCenter', 'operator']);

        // Date or Date Range Filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('collection_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('collection_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('collection_date', '<=', $request->to_date);
        } elseif ($request->filled('date')) {
            $query->whereDate('collection_date', $request->date);
        } else {
            // Default to today
            $query->whereDate('collection_date', Carbon::today());
        }

        // Shift Filter
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        // Center Filter
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }

        // Route Filter
        if ($request->filled('route_id')) {
            $query->whereHas('farmer', function ($q) use ($request) {
                $q->where('route_id', $request->route_id);
            });
        }

        // Milk Type Filter
        if ($request->filled('milk_type')) {
            $query->where('milk_type', $request->milk_type);
        }

        // Farmer Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($outer) use ($search) {
                $outer->where('receipt_number', 'like', "%{$search}%")
                      ->orWhereHas('farmer', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('farmer_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                      });
            });
        }

        // CSV Export Support
        if ($request->get('export') === 'csv') {
            $rows = (clone $query)->latest()->get();
            $filename = 'milk_collections_' . date('Y_m_d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Sample/Receipt', 'Date', 'Shift', 'Farmer Code', 'Farmer Name', 'Milk Type', 'Ltr', 'FAT (%)', 'CLR', 'SNF (%)', 'Rate (Rs)', 'Amount (Rs)']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->receipt_number,
                        $r->collection_date ? $r->collection_date->format('d-m-Y') : '',
                        ucfirst($r->shift),
                        $r->farmer->farmer_code ?? '',
                        $r->farmer->name ?? '',
                        ucfirst($r->milk_type),
                        $r->quantity_liters,
                        $r->fat,
                        $r->clr ?? '',
                        $r->snf,
                        $r->applied_rate,
                        $r->net_amount,
                    ]);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

        $collections = $query->latest()->paginate(20)->withQueryString();

        // Metrics Summary on Filtered Scope
        $todayTotalLiters = (clone $query)->sum('quantity_liters');
        $todayTotalAmount = (clone $query)->sum('net_amount');
        $avgFat = (clone $query)->avg('fat') ?? 0;
        $avgSnf = (clone $query)->avg('snf') ?? 0;

        // Master Data for Filters & In-Page Collection Dashboard
        $centers = CollectionCenter::where('status', 'active')->get();
        if ($centers->isEmpty()) {
            $centers = CollectionCenter::all();
        }
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();
        $farmers = Farmer::with('route')->orderBy('farmer_code')->get();
        $rateCharts = RateChart::where('status', 'active')->get();
        $collectionConfig = $this->getCollectionConfig();
        $currentShift = $collectionConfig['default_shift'];
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        $liveStats = $this->getShiftAndPreviousStats($today, $currentShift, null);
        $initialRateInfo = $this->resolveCollectionRate(
            4.0,
            8.5,
            28.0,
            $collectionConfig['default_milk_type'],
            null,
            $centers->first()->id ?? null,
            $currentShift
        );

        return view('collections.index', compact(
            'collections',
            'todayTotalLiters',
            'todayTotalAmount',
            'avgFat',
            'avgSnf',
            'centers',
            'routes',
            'farmers',
            'rateCharts',
            'currentShift',
            'today',
            'nextReceipt',
            'collectionConfig',
            'liveStats',
            'initialRateInfo'
        ));
    }

    /**
     * Dedicated create page (redirects to modal or standalone)
     */
    public function create()
    {
        $farmers = Farmer::where('status', 'active')->orderBy('farmer_code')->get();
        $centers = CollectionCenter::where('status', 'active')->get();
        if ($centers->isEmpty()) {
            $centers = CollectionCenter::all();
        }
        $rateCharts = RateChart::where('status', 'active')->get();
        $collectionConfig = $this->getCollectionConfig();
        $currentShift = $collectionConfig['default_shift'];
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        return view('collections.create', compact('farmers', 'centers', 'rateCharts', 'currentShift', 'today', 'nextReceipt', 'collectionConfig'));
    }

    /**
     * Live Rate & Shift Summary Calculation API
     */
    public function calculateRate(Request $request)
    {
        $fat = (float) $request->get('fat', 0);
        $snf = (float) $request->get('snf', 0);
        $clr = (float) $request->get('clr', 0);
        $milkType = $request->get('milk_type', 'cow');
        $shift = $request->get('shift', 'morning');
        $collectionDate = $request->get('collection_date', Carbon::today()->format('Y-m-d'));
        $farmerId = $request->get('farmer_id') ? (int) $request->get('farmer_id') : null;
        $centerId = $request->get('collection_center_id') ? (int) $request->get('collection_center_id') : null;

        $farmer = $farmerId ? Farmer::find($farmerId) : null;

        $resolved = $this->resolveCollectionRate($fat, $snf, $clr, $milkType, $farmer, $centerId, $shift);
        $stats = $this->getShiftAndPreviousStats($collectionDate, $shift, $farmerId);

        $qty = (float) $request->get('quantity_liters', 0);
        $rate = (float) ($resolved['rate'] ?? 0);
        $gross = round($qty * $rate, 2);
        $charges = \App\Services\CenterAdditionDeductionService::calculateForCollection($farmer, $qty, $gross);

        $bonus = $charges['total_additions'];
        $deduction = $charges['total_deductions'];
        $net = max(0, round($gross + $bonus - $deduction, 2));

        return response()->json(array_merge($resolved, $stats, [
            'charges' => $charges,
            'suggested_bonus' => $bonus,
            'suggested_deduction' => $deduction,
            'net_amount' => $net,
        ]));
    }

    /**
     * Store new collection entry
     */
    public function store(Request $request)
    {
        $config = $this->getCollectionConfig();

        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat' => $config['show_fat'] ? 'required|numeric|min:0.5|max:20' : 'nullable|numeric|min:0|max:20',
            'clr' => ($config['show_clr'] && !$config['auto_clr']) ? 'required|numeric|min:5|max:50' : 'nullable|numeric|min:0|max:50',
            'snf' => ($config['show_snf'] && !$config['auto_snf']) ? 'required|numeric|min:2|max:20' : 'nullable|numeric|min:0|max:20',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'applied_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);
        $centerId = !empty($validated['collection_center_id']) ? (int) $validated['collection_center_id'] : $farmer->collection_center_id;

        $fat = (float) ($validated['fat'] ?? 0);
        $snf = (float) ($validated['snf'] ?? 0);
        $clr = isset($validated['clr']) && $validated['clr'] !== '' ? (float) $validated['clr'] : 0.0;

        $resolved = $this->resolveCollectionRate($fat, $snf, $clr, $validated['milk_type'], $farmer, $centerId, $validated['shift']);
        $calcRate = $resolved['rate'];
        $fat = $resolved['fat'];
        $snf = $resolved['snf'];
        $clr = $resolved['clr'];

        if (!empty($validated['applied_rate']) && (float) $validated['applied_rate'] > 0) {
            $appliedRate = (float) $validated['applied_rate'];
        } else {
            $appliedRate = $calcRate;
        }

        $qty = (float) $validated['quantity_liters'];
        $gross = round($qty * $appliedRate, 2);

        $charges = \App\Services\CenterAdditionDeductionService::calculateForCollection($farmer, $qty, $gross);

        // Include center additions/deductions (all farmers + farmer wise) if bonus/deduction not manually overridden
        $bonus = isset($validated['bonus']) && $validated['bonus'] !== ''
            ? (float) $validated['bonus']
            : $charges['total_additions'];

        $deduction = isset($validated['deduction']) && $validated['deduction'] !== ''
            ? (float) $validated['deduction']
            : $charges['total_deductions'];

        $net = max(0, round($gross + $bonus - $deduction, 2));

        $receiptNumber = 'COL-' . (MilkCollection::max('id') + 1001);

        $collection = MilkCollection::create([
            'receipt_number' => $receiptNumber,
            'farmer_id' => $farmer->id,
            'collection_center_id' => $centerId,
            'operator_id' => Auth::id(),
            'collection_date' => $validated['collection_date'],
            'shift' => $validated['shift'],
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $qty,
            'fat' => $fat,
            'snf' => $snf,
            'clr' => $clr > 0 ? $clr : null,
            'calculated_rate' => $calcRate,
            'applied_rate' => $appliedRate,
            'gross_amount' => $gross,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_amount' => $net,
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update farmer balance and ledger
        $newBalance = (float) $farmer->current_balance + $net;
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['collection_date'],
            'type' => 'credit',
            'amount' => $net,
            'balance' => $newBalance,
            'reference_type' => 'milk_collection',
            'reference_id' => $collection->id,
            'description' => "Milk Collection {$validated['shift']} ({$qty} Ltr @ ₹{$appliedRate}/Ltr)",
        ]);

        // Notification
        if ($farmer->user_id) {
            Notification::create([
                'user_id' => $farmer->user_id,
                'title' => "Milk Slip: {$receiptNumber}",
                'message' => "Milk Recorded: {$qty} Ltr ({$validated['milk_type']}), FAT: {$fat}%, SNF: {$snf}%, Rate: ₹{$appliedRate}, Net: ₹{$net} credited to your ledger.",
                'channel' => 'whatsapp',
                'type' => 'success',
                'recipient_phone' => $farmer->phone,
                'status' => 'sent',
            ]);
        }

        AuditLog::log('Recorded Milk Collection', 'MilkCollection', $collection->id, [
            'liters' => $qty,
            'rate' => $appliedRate,
            'net' => $net,
            'collection_type' => $config['collection_type'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            $collection->load(['farmer', 'collectionCenter']);
            $stats = $this->getShiftAndPreviousStats($validated['collection_date'], $validated['shift'], null);
            return response()->json(array_merge([
                'success' => true,
                'message' => "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}",
                'collection' => $collection,
                'next_receipt' => 'COL-' . (MilkCollection::max('id') + 1001),
                'slip_url' => route('collections.slip', $collection),
            ], $stats));
        }

        if ($request->has('print_slip') && $request->print_slip) {
            return redirect()->route('collections.slip', $collection)->with('success', "Collection {$receiptNumber} saved! Net Amount: ₹{$net}");
        }

        return redirect()->route('collections.index')->with('success', "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}");
    }

    /**
     * Update an existing collection entry
     */
    public function update(Request $request, MilkCollection $collection)
    {
        $config = $this->getCollectionConfig();

        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat' => $config['show_fat'] ? 'required|numeric|min:0.5|max:20' : 'nullable|numeric|min:0|max:20',
            'clr' => ($config['show_clr'] && !$config['auto_clr']) ? 'required|numeric|min:5|max:50' : 'nullable|numeric|min:0|max:50',
            'snf' => ($config['show_snf'] && !$config['auto_snf']) ? 'required|numeric|min:2|max:20' : 'nullable|numeric|min:0|max:20',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'applied_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldFarmer = $collection->farmer;
        $oldNet = (float) $collection->net_amount;

        $newFarmer = Farmer::findOrFail($validated['farmer_id']);
        $centerId = !empty($validated['collection_center_id']) ? (int) $validated['collection_center_id'] : $newFarmer->collection_center_id;

        $fat = (float) ($validated['fat'] ?? 0);
        $snf = (float) ($validated['snf'] ?? 0);
        $clr = isset($validated['clr']) && $validated['clr'] !== '' ? (float) $validated['clr'] : 0.0;

        $resolved = $this->resolveCollectionRate($fat, $snf, $clr, $validated['milk_type'], $newFarmer, $centerId, $validated['shift']);
        $calcRate = $resolved['rate'];
        $fat = $resolved['fat'];
        $snf = $resolved['snf'];
        $clr = $resolved['clr'];

        if (!empty($validated['applied_rate']) && (float) $validated['applied_rate'] > 0) {
            $appliedRate = (float) $validated['applied_rate'];
        } else {
            $appliedRate = $calcRate;
        }

        $qty = (float) $validated['quantity_liters'];
        $gross = round($qty * $appliedRate, 2);
        $charges = \App\Services\CenterAdditionDeductionService::calculateForCollection($newFarmer, $qty, $gross);
        $bonus = isset($validated['bonus']) && $validated['bonus'] !== ''
            ? (float) $validated['bonus']
            : $charges['total_additions'];
        $deduction = isset($validated['deduction']) && $validated['deduction'] !== ''
            ? (float) $validated['deduction']
            : $charges['total_deductions'];
        $newNet = max(0, round($gross + $bonus - $deduction, 2));

        // Revert old net from old farmer balance
        if ($oldFarmer) {
            $oldFarmer->update(['current_balance' => (float) $oldFarmer->current_balance - $oldNet]);
        }

        // Update collection
        $collection->update([
            'farmer_id' => $newFarmer->id,
            'collection_center_id' => $centerId,
            'collection_date' => $validated['collection_date'],
            'shift' => $validated['shift'],
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $qty,
            'fat' => $fat,
            'snf' => $snf,
            'clr' => $clr > 0 ? $clr : null,
            'calculated_rate' => $calcRate,
            'applied_rate' => $appliedRate,
            'gross_amount' => $gross,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_amount' => $newNet,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Apply new net to new farmer balance
        $newBalance = (float) $newFarmer->current_balance + $newNet;
        $newFarmer->update(['current_balance' => $newBalance]);

        // Update or create ledger record
        $ledger = FarmerLedger::where('reference_type', 'milk_collection')->where('reference_id', $collection->id)->first();
        if ($ledger) {
            $ledger->update([
                'farmer_id' => $newFarmer->id,
                'transaction_date' => $validated['collection_date'],
                'amount' => $newNet,
                'balance' => $newBalance,
                'description' => "Milk Collection {$validated['shift']} ({$qty} Ltr @ ₹{$appliedRate}/Ltr) [Updated]",
            ]);
        } else {
            FarmerLedger::create([
                'farmer_id' => $newFarmer->id,
                'transaction_date' => $validated['collection_date'],
                'type' => 'credit',
                'amount' => $newNet,
                'balance' => $newBalance,
                'reference_type' => 'milk_collection',
                'reference_id' => $collection->id,
                'description' => "Milk Collection {$validated['shift']} ({$qty} Ltr @ ₹{$appliedRate}/Ltr) [Updated]",
            ]);
        }

        AuditLog::log('Updated Milk Collection', 'MilkCollection', $collection->id);

        if ($request->ajax() || $request->wantsJson()) {
            $collection->load(['farmer', 'collectionCenter']);
            $stats = $this->getShiftAndPreviousStats($validated['collection_date'], $validated['shift'], null);
            return response()->json(array_merge([
                'success' => true,
                'message' => "Collection {$collection->receipt_number} updated successfully! Amount: ₹{$newNet}",
                'collection' => $collection,
                'slip_url' => route('collections.slip', $collection),
            ], $stats));
        }

        if ($request->has('print_slip') && $request->print_slip) {
            return redirect()->route('collections.slip', $collection)->with('success', "Collection {$collection->receipt_number} updated! Amount: ₹{$newNet}");
        }

        return redirect()->route('collections.index')->with('success', "Collection {$collection->receipt_number} updated successfully! Amount: ₹{$newNet}");
    }

    /**
     * Delete a collection entry
     */
    public function destroy(MilkCollection $collection)
    {
        $farmer = $collection->farmer;
        $net = (float) $collection->net_amount;
        $colDate = $collection->collection_date ? $collection->collection_date->format('Y-m-d') : date('Y-m-d');
        $colShift = $collection->shift ?: 'morning';

        if ($farmer) {
            $farmer->decrement('current_balance', $net);
        }

        FarmerLedger::where('reference_type', 'milk_collection')
            ->where('reference_id', $collection->id)
            ->delete();

        $receipt = $collection->receipt_number;
        $collection->delete();

        AuditLog::log('Deleted Milk Collection', 'MilkCollection', $collection->id);

        if (request()->ajax() || request()->wantsJson()) {
            $stats = $this->getShiftAndPreviousStats($colDate, $colShift, null);
            return response()->json(array_merge([
                'success' => true,
                'message' => "Milk collection {$receipt} deleted successfully.",
            ], $stats));
        }

        return redirect()->route('collections.index')->with('success', "Milk collection {$receipt} deleted successfully.");
    }

    /**
     * View Printable Slip
     */
    public function slip(MilkCollection $collection)
    {
        $collection->load(['farmer', 'collectionCenter', 'operator']);
        return view('collections.slip', compact('collection'));
    }
}
