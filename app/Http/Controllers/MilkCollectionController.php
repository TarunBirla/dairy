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
        $rateChartStatus = SystemSetting::get('rate_chart_status', 'On');
        $farmerWiseRate = SystemSetting::get('farmer_wise_rate', 'As per Rate Chart');
        $shiftMorningBonus = (float) SystemSetting::get('shift_wise_morning', '0.00');
        $shiftEveningBonus = (float) SystemSetting::get('shift_wise_evening', '0.00');

        $weighingFormat = SystemSetting::get('weighing_scale_format', 'Automatic + Manual');
        $fatFormat = SystemSetting::get('fat_format', 'Automatic + Manual');
        $snfFormat = SystemSetting::get('snf_format', 'Automatic + Manual');
        $clrFormat = SystemSetting::get('clr_lacto_format', 'Automatic + Manual');

        $additionTypes = json_decode(SystemSetting::get('center_addition_types', '[]'), true) ?: [];
        $deductionTypes = json_decode(SystemSetting::get('center_deduction_types', '[]'), true) ?: [];

        $perLiterAddition = 0.0;
        foreach ($additionTypes as $add) {
            $perLiterAddition += (float) ($add['rate'] ?? 0);
        }

        $perLiterDeduction = 0.0;
        foreach ($deductionTypes as $ded) {
            if (($ded['enabled'] ?? true) !== false) {
                $perLiterDeduction += (float) ($ded['rate'] ?? 0);
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
        if ($farmer && $farmer->custom_rate_override > 0) {
            if ($config['farmer_wise_rate'] === 'Fat Wise' && $fat > 0) {
                $rate = round($fat * (float) $farmer->custom_rate_override, 2);
                $chartName = "Farmer Fat Wise (₹{$farmer->custom_rate_override}/FAT)";
            } else {
                $rate = round((float) $farmer->custom_rate_override, 2);
                $chartName = "Custom Farmer Rate (₹" . number_format($rate, 2) . ")";
            }
        } else {
            // 2. Match RateChart according to Farmer -> Center -> MilkType & Format -> Default
            $chart = null;

            if ($farmer && $farmer->rate_chart_id) {
                $chart = RateChart::where('id', $farmer->rate_chart_id)->where('status', 'active')->first();
            }

            if (!$chart && ($centerId || ($farmer && $farmer->collection_center_id))) {
                $cid = $centerId ?: $farmer->collection_center_id;
                $center = CollectionCenter::find($cid);
                if ($center && $center->rate_chart_id) {
                    $chart = RateChart::where('id', $center->rate_chart_id)->where('status', 'active')->first();
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

                $chart = RateChart::whereIn('milk_type', [$milkType, 'both'])
                    ->where('status', 'active')
                    ->where('format', $preferredFormat)
                    ->first();

                if (!$chart) {
                    $chart = RateChart::whereIn('milk_type', [$milkType, 'both'])
                        ->where('status', 'active')
                        ->first()
                        ?? RateChart::where('is_default', true)->where('status', 'active')->first()
                        ?? RateChart::where('status', 'active')->first();
                }
            }

            if ($chart) {
                $rate = $chart->calculateRate($fat, $snf, $clr, $collectionType);
                $chartName = $chart->name;
            } else {
                // 3. Fallback formula according to collection_type
                if ($collectionType === 'Liter Only') {
                    $rate = ($milkType === 'buffalo') ? 55.00 : 40.00;
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
                $chartName = ucfirst($milkType) . ' (' . $collectionType . ')';
            }
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
            'chart_name' => $chartName,
            'collection_type' => $collectionType,
            'per_liter_addition' => $config['per_liter_addition'],
            'per_liter_deduction' => $config['per_liter_deduction'],
        ];
    }

    /**
     * Milk Collections Listing & Quick Modal Dashboard
     */
    public function index(Request $request)
    {
        $query = MilkCollection::with(['farmer', 'collectionCenter', 'operator']);

        // Date Filter
        if ($request->filled('date')) {
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

        // Milk Type Filter
        if ($request->filled('milk_type')) {
            $query->where('milk_type', $request->milk_type);
        }

        // Farmer Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('farmer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $collections = $query->latest()->paginate(20)->withQueryString();

        // Metrics Summary on Filtered Scope
        $todayTotalLiters = (clone $query)->sum('quantity_liters');
        $todayTotalAmount = (clone $query)->sum('net_amount');
        $avgFat = (clone $query)->avg('fat') ?? 0;
        $avgSnf = (clone $query)->avg('snf') ?? 0;

        // Master Data for Filters & In-Page Collection Popup Modal
        $centers = CollectionCenter::where('status', 'active')->get();
        if ($centers->isEmpty()) {
            $centers = CollectionCenter::all();
        }
        $farmers = Farmer::orderBy('farmer_code')->get();
        $rateCharts = RateChart::where('status', 'active')->get();
        $collectionConfig = $this->getCollectionConfig();
        $currentShift = $collectionConfig['default_shift'];
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        return view('collections.index', compact(
            'collections',
            'todayTotalLiters',
            'todayTotalAmount',
            'avgFat',
            'avgSnf',
            'centers',
            'farmers',
            'rateCharts',
            'currentShift',
            'today',
            'nextReceipt',
            'collectionConfig'
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
     * Live Rate Calculation API
     */
    public function calculateRate(Request $request)
    {
        $fat = (float) $request->get('fat', 0);
        $snf = (float) $request->get('snf', 0);
        $clr = (float) $request->get('clr', 0);
        $milkType = $request->get('milk_type', 'cow');
        $shift = $request->get('shift', 'morning');
        $farmerId = $request->get('farmer_id');
        $centerId = $request->get('collection_center_id') ? (int) $request->get('collection_center_id') : null;

        $farmer = $farmerId ? Farmer::find($farmerId) : null;

        $resolved = $this->resolveCollectionRate($fat, $snf, $clr, $milkType, $farmer, $centerId, $shift);

        return response()->json($resolved);
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

        // Include center per-liter additions/deductions if bonus/deduction not manually overridden
        $bonus = isset($validated['bonus']) && $validated['bonus'] !== ''
            ? (float) $validated['bonus']
            : round($qty * $config['per_liter_addition'], 2);

        $deduction = isset($validated['deduction']) && $validated['deduction'] !== ''
            ? (float) $validated['deduction']
            : round($qty * $config['per_liter_deduction'], 2);

        $net = round($gross + $bonus - $deduction, 2);

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
            return response()->json([
                'success' => true,
                'message' => "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}",
                'collection' => $collection,
                'next_receipt' => 'COL-' . (MilkCollection::max('id') + 1001),
                'slip_url' => route('collections.slip', $collection),
            ]);
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
        $bonus = (float) ($validated['bonus'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $newNet = round($gross + $bonus - $deduction, 2);

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
            return response()->json([
                'success' => true,
                'message' => "Collection {$collection->receipt_number} updated successfully! Amount: ₹{$newNet}",
                'collection' => $collection,
                'slip_url' => route('collections.slip', $collection),
            ]);
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
            return response()->json([
                'success' => true,
                'message' => "Milk collection {$receipt} deleted successfully.",
            ]);
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
