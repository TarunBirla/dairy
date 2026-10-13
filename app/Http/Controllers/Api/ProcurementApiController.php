<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Farmer;
use App\Models\MilkCollection;
use App\Models\RateChart;
use App\Models\RateCorrection;
use App\Models\FarmerSettlement;
use App\Models\FarmerAdvance;
use App\Models\FarmerAdvanceRepayment;
use App\Models\FarmerDeduction;
use App\Models\FarmerInvoice;
use App\Models\FarmerLedger;
use App\Models\CollectionCenter;
use App\Models\DeliveryRoute;
use App\Models\Branch;
use App\Models\SystemSetting;
use App\Models\AuditLog;

class ProcurementApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. COLLECTION HISTORY & INTAKE (collections)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/collections
     * List milk collections with filters, KPIs, shift summary, and previous collection info.
     */
    public function collectionsIndex(Request $request)
    {
        $query = MilkCollection::with(['farmer', 'collectionCenter', 'collector']);

        if ($request->filled('date')) {
            $query->whereDate('collection_date', $request->date);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('collection_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where('shift', $request->shift);
        }
        if ($request->filled('milk_type')) {
            $query->where('milk_type', $request->milk_type);
        }
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }
        if ($request->filled('route_id')) {
            $routeId = (int) $request->route_id;
            $query->whereHas('farmer', function ($fq) use ($routeId) {
                $fq->where('route_id', $routeId);
            });
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('slip_number', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $collections = $query->latest('collection_date')->latest('id')->paginate($perPage);

        $today = $request->get('date', Carbon::today()->format('Y-m-d'));
        $todayShift = $request->get('shift');

        $shiftQuery = MilkCollection::whereDate('collection_date', $today);
        if ($todayShift && $todayShift !== 'all') {
            $shiftQuery->where('shift', $todayShift);
        }

        $shiftStats = [
            'date' => $today,
            'shift' => $todayShift ?: 'all',
            'total_entries' => (int) (clone $shiftQuery)->count(),
            'total_liters' => round((float) (clone $shiftQuery)->sum('quantity_liters'), 2),
            'total_amount' => round((float) (clone $shiftQuery)->sum('net_amount'), 2),
            'avg_fat' => round((float) ((clone $shiftQuery)->avg('fat_percentage') ?? 0), 2),
            'avg_snf' => round((float) ((clone $shiftQuery)->avg('snf_percentage') ?? 0), 2),
            'cow_liters' => round((float) (clone $shiftQuery)->where('milk_type', 'cow')->sum('quantity_liters'), 2),
            'buffalo_liters' => round((float) (clone $shiftQuery)->where('milk_type', 'buffalo')->sum('quantity_liters'), 2),
        ];

        $previousCollectionRecord = MilkCollection::with('farmer')->latest('collection_date')->latest('id')->first();
        $previousCollection = $previousCollectionRecord ? [
            'id' => $previousCollectionRecord->id,
            'slip_number' => $previousCollectionRecord->slip_number,
            'collection_date' => optional($previousCollectionRecord->collection_date)->format('Y-m-d'),
            'shift' => $previousCollectionRecord->shift,
            'farmer_id' => $previousCollectionRecord->farmer_id,
            'farmer_code' => $previousCollectionRecord->farmer->farmer_code ?? null,
            'farmer_name' => $previousCollectionRecord->farmer->name ?? null,
            'milk_type' => $previousCollectionRecord->milk_type,
            'quantity_liters' => (float) $previousCollectionRecord->quantity_liters,
            'fat_percentage' => (float) $previousCollectionRecord->fat_percentage,
            'clr' => (float) $previousCollectionRecord->clr,
            'snf_percentage' => (float) $previousCollectionRecord->snf_percentage,
            'applied_rate' => (float) $previousCollectionRecord->applied_rate,
            'net_amount' => (float) $previousCollectionRecord->net_amount,
        ] : null;

        return response()->json([
            'success' => true,
            'message' => 'Milk collections fetched successfully.',
            'shift_summary' => $shiftStats,
            'previous_collection' => $previousCollection,
            'data' => $collections->items(),
            'pagination' => [
                'current_page' => $collections->currentPage(),
                'per_page' => $collections->perPage(),
                'total' => $collections->total(),
                'last_page' => $collections->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/collections/meta
     * Return dropdown metadata (farmers, routes, centers, rate charts, settings) for collection screen.
     */
    public function collectionsMeta()
    {
        $farmers = Farmer::where('status', 'active')
            ->orderBy('farmer_code')
            ->get(['id', 'farmer_code', 'name', 'name_hi', 'phone', 'animal_type', 'collection_center_id', 'route_id', 'rate_chart_id', 'cow_milk_rate', 'buffalo_milk_rate', 'current_balance']);

        $routes = DeliveryRoute::orderBy('name')->get(['id', 'route_code', 'name', 'center_id', 'status']);
        $centers = CollectionCenter::where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']);
        $rateCharts = RateChart::where('status', 'active')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'farmers' => $farmers,
                'routes' => $routes,
                'collection_centers' => $centers,
                'rate_charts' => $rateCharts,
                'settings' => [
                    'route_wise_collection' => (string) SystemSetting::getValue('collection_route_wise', '1') === '1',
                    'show_previous_collection' => (string) SystemSetting::getValue('collection_previous_collection', '1') === '1',
                    'clr_enabled' => (string) SystemSetting::getValue('collection_clr_Option', '1') === '1',
                    'snf_formula' => SystemSetting::getValue('collection_snf_formula', 'ISI'),
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/collections/calculate-rate
     * Calculate milk rate, SNF, and total amount based on Fat/CLR/SNF and Farmer/RateChart.
     */
    public function collectionsCalculateRate(Request $request)
    {
        $milkType = $request->get('milk_type', 'cow');
        $fat = (float) $request->get('fat', 4.0);
        $clr = (float) $request->get('clr', 0);
        $snf = (float) $request->get('snf', 0);
        $liters = (float) $request->get('liters', $request->get('quantity_liters', 1.0));
        $farmerId = $request->get('farmer_id');

        if ($clr > 0 && $snf <= 0) {
            $snf = RateChart::clrToSnf($clr, $fat);
        } elseif ($snf <= 0) {
            $snf = 8.5;
        }

        $chart = null;
        $fixedFarmerRate = 0;

        if ($farmerId) {
            $farmer = Farmer::with('rateChart')->find($farmerId);
            if ($farmer) {
                if ($milkType === 'cow' && (float) $farmer->cow_milk_rate > 0) {
                    $fixedFarmerRate = (float) $farmer->cow_milk_rate;
                } elseif ($milkType === 'buffalo' && (float) $farmer->buffalo_milk_rate > 0) {
                    $fixedFarmerRate = (float) $farmer->buffalo_milk_rate;
                }
                if ($farmer->rateChart && $farmer->rateChart->status === 'active') {
                    $chart = $farmer->rateChart;
                }
            }
        }

        if ($fixedFarmerRate > 0) {
            $rate = round($fixedFarmerRate, 2);
            $gross = round($liters * $rate, 2);
            return response()->json([
                'success' => true,
                'data' => [
                    'rate' => $rate,
                    'snf' => round($snf, 2),
                    'gross_amount' => $gross,
                    'net_amount' => $gross,
                    'chart_name' => 'Farmer Fixed Rate',
                ],
            ]);
        }

        if (!$chart) {
            $chart = RateChart::where('milk_type', $milkType)
                ->where('status', 'active')
                ->orderByDesc('is_default')
                ->first();
        }

        if (!$chart) {
            $rate = round(($fat * 6.5) + ($snf * 4.2), 2);
        } else {
            $rate = $chart->calculateRate($fat, $snf);
        }

        $gross = round($liters * $rate, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'rate' => $rate,
                'snf' => round($snf, 2),
                'gross_amount' => $gross,
                'net_amount' => $gross,
                'chart_id' => $chart->id ?? null,
                'chart_name' => $chart ? $chart->name : 'Standard Formula',
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/collections
     * Create a new milk collection entry.
     */
    public function collectionsStore(Request $request)
    {
        $validated = $request->validate([
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'farmer_id' => 'required|exists:farmers,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat_percentage' => 'required|numeric|min:1|max:15',
            'clr' => 'nullable|numeric|min:0|max:45',
            'snf_percentage' => 'nullable|numeric|min:1|max:15',
            'applied_rate' => 'required|numeric|min:1',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'deduction_reason' => 'nullable|string',
            'sample_no' => 'nullable|string|max:50',
            'can_details' => 'nullable|string',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        $clr = (float) ($validated['clr'] ?? 0);
        $snf = (float) ($validated['snf_percentage'] ?? 0);
        if ($snf <= 0 && $clr > 0) {
            $snf = RateChart::clrToSnf($clr, (float) $validated['fat_percentage']);
        } elseif ($snf <= 0) {
            $snf = 8.5;
        }

        $liters = (float) $validated['quantity_liters'];
        $rate = (float) $validated['applied_rate'];
        $bonus = (float) ($validated['bonus_amount'] ?? 0);
        $deduction = (float) ($validated['deduction_amount'] ?? 0);

        $grossAmount = round($liters * $rate, 2);
        $netAmount = round($grossAmount + $bonus - $deduction, 2);

        $dateStr = Carbon::parse($validated['collection_date'])->format('Ymd');
        $shiftCode = $validated['shift'] === 'morning' ? 'M' : 'E';
        $nextId = (MilkCollection::max('id') ?? 0) + 1;
        $slipNumber = "SLP-{$dateStr}-{$shiftCode}-" . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $collectionData = [
            'slip_number' => $slipNumber,
            'collection_date' => $validated['collection_date'],
            'shift' => $validated['shift'],
            'farmer_id' => $farmer->id,
            'branch_id' => $farmer->branch_id ?? 1,
            'collection_center_id' => $validated['collection_center_id'] ?? $farmer->collection_center_id,
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $liters,
            'quantity_kg' => round($liters * 1.03, 2),
            'fat_percentage' => $validated['fat_percentage'],
            'clr' => $clr,
            'snf_percentage' => $snf,
            'water_percentage' => 0,
            'calculated_rate' => $rate,
            'applied_rate' => $rate,
            'gross_amount' => $grossAmount,
            'bonus_amount' => $bonus,
            'deduction_amount' => $deduction,
            'net_amount' => $netAmount,
            'deduction_reason' => $validated['deduction_reason'] ?? null,
            'is_manual_override' => false,
            'payment_status' => 'pending',
            'collected_by' => Auth::id(),
        ];

        if (Schema::hasColumn('milk_collections', 'sample_no')) {
            $collectionData['sample_no'] = $validated['sample_no'] ?? null;
        }
        if (Schema::hasColumn('milk_collections', 'can_details')) {
            $collectionData['can_details'] = $validated['can_details'] ?? null;
        }

        $collection = MilkCollection::create($collectionData);

        $newBalance = round((float) $farmer->current_balance + $netAmount, 2);
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['collection_date'],
            'type' => 'credit',
            'amount' => $netAmount,
            'balance' => $newBalance,
            'reference_type' => 'milk_collection',
            'reference_id' => $collection->id,
            'description' => ucfirst($validated['shift']) . " Milk Intake ({$liters} L @ ₹{$rate}) - Slip #{$slipNumber}",
        ]);

        AuditLog::log('Created Milk Collection (API)', 'MilkCollection', $collection->id, [
            'slip' => $slipNumber,
            'farmer' => $farmer->name,
            'liters' => $liters,
            'net' => $netAmount,
        ]);

        $collection->load(['farmer', 'collectionCenter']);

        return response()->json([
            'success' => true,
            'message' => "Collection Slip #{$slipNumber} saved for {$farmer->name} ({$liters} L = ₹" . number_format($netAmount, 2) . ")",
            'data' => $collection,
        ], 201);
    }

    /**
     * GET /api/v1/procurement/collections/{collection}
     * View single milk collection slip details.
     */
    public function collectionsShow(MilkCollection $collection)
    {
        $collection->load(['farmer', 'collectionCenter', 'collector']);
        return response()->json([
            'success' => true,
            'data' => $collection,
        ]);
    }

    /**
     * PUT /api/v1/procurement/collections/{collection}
     * Update an existing milk collection entry and sync farmer ledger balance.
     */
    public function collectionsUpdate(Request $request, MilkCollection $collection)
    {
        $validated = $request->validate([
            'collection_date' => 'nullable|date',
            'shift' => 'nullable|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat_percentage' => 'required|numeric|min:1|max:15',
            'clr' => 'nullable|numeric|min:0|max:45',
            'snf_percentage' => 'nullable|numeric|min:1|max:15',
            'applied_rate' => 'required|numeric|min:1',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'deduction_reason' => 'nullable|string',
        ]);

        $farmer = $collection->farmer;
        $oldNetAmount = (float) $collection->net_amount;

        $clr = (float) ($validated['clr'] ?? $collection->clr ?? 0);
        $snf = (float) ($validated['snf_percentage'] ?? 0);
        if ($snf <= 0 && $clr > 0) {
            $snf = RateChart::clrToSnf($clr, (float) $validated['fat_percentage']);
        } elseif ($snf <= 0) {
            $snf = (float) $collection->snf_percentage ?: 8.5;
        }

        $liters = (float) $validated['quantity_liters'];
        $rate = (float) $validated['applied_rate'];
        $bonus = (float) ($validated['bonus_amount'] ?? $collection->bonus_amount ?? 0);
        $deduction = (float) ($validated['deduction_amount'] ?? $collection->deduction_amount ?? 0);

        $grossAmount = round($liters * $rate, 2);
        $newNetAmount = round($grossAmount + $bonus - $deduction, 2);

        $collection->update([
            'collection_date' => $validated['collection_date'] ?? $collection->collection_date,
            'shift' => $validated['shift'] ?? $collection->shift,
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $liters,
            'quantity_kg' => round($liters * 1.03, 2),
            'fat_percentage' => $validated['fat_percentage'],
            'clr' => $clr,
            'snf_percentage' => $snf,
            'calculated_rate' => $rate,
            'applied_rate' => $rate,
            'gross_amount' => $grossAmount,
            'bonus_amount' => $bonus,
            'deduction_amount' => $deduction,
            'net_amount' => $newNetAmount,
            'deduction_reason' => $validated['deduction_reason'] ?? $collection->deduction_reason,
            'is_manual_override' => true,
        ]);

        if ($farmer) {
            $diff = round($newNetAmount - $oldNetAmount, 2);
            $newBalance = round((float) $farmer->current_balance + $diff, 2);
            $farmer->update(['current_balance' => $newBalance]);

            FarmerLedger::where('reference_type', 'milk_collection')
                ->where('reference_id', $collection->id)
                ->update([
                    'amount' => $newNetAmount,
                    'balance' => $newBalance,
                    'description' => ucfirst($collection->shift) . " Milk Intake ({$liters} L @ ₹{$rate}) - Slip #{$collection->slip_number} (Updated)",
                ]);
        }

        AuditLog::log('Updated Milk Collection (API)', 'MilkCollection', $collection->id, [
            'slip' => $collection->slip_number,
            'old_net' => $oldNetAmount,
            'new_net' => $newNetAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Collection Slip #{$collection->slip_number} updated successfully.",
            'data' => $collection->fresh(['farmer', 'collectionCenter']),
        ]);
    }

    /**
     * DELETE /api/v1/procurement/collections/{collection}
     * Delete a milk collection entry and revert farmer balance.
     */
    public function collectionsDestroy(MilkCollection $collection)
    {
        $slip = $collection->slip_number;
        $farmer = $collection->farmer;
        $netAmount = (float) $collection->net_amount;

        if ($farmer) {
            $newBalance = round((float) $farmer->current_balance - $netAmount, 2);
            $farmer->update(['current_balance' => $newBalance]);
        }

        FarmerLedger::where('reference_type', 'milk_collection')
            ->where('reference_id', $collection->id)
            ->delete();

        $collection->delete();

        AuditLog::log('Deleted Milk Collection (API)', 'MilkCollection', $collection->id, ['slip' => $slip]);

        return response()->json([
            'success' => true,
            'message' => "Collection Slip #{$slip} deleted successfully.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. FARMERS MASTER (farmers)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/farmers
     * List farmers with filters, sorting, and KPIs.
     */
    public function farmersIndex(Request $request)
    {
        $query = Farmer::with(['collectionCenter', 'rateChart', 'route']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_hi', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%");
            });
        }

        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('balance_status')) {
            if ($request->balance_status === 'due') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->balance_status === 'advance') {
                $query->where('current_balance', '<', 0);
            } elseif ($request->balance_status === 'zero') {
                $query->where('current_balance', '=', 0);
            }
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'code_asc':
                $query->orderBy('farmer_code', 'asc');
                break;
            case 'code_desc':
                $query->orderBy('farmer_code', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'balance_desc':
                $query->orderBy('current_balance', 'desc');
                break;
            case 'balance_asc':
                $query->orderBy('current_balance', 'asc');
                break;
            default:
                $query->latest('id');
                break;
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $farmers = $query->paginate($perPage);

        $nextNum = (Farmer::max('id') ?? 0) + 1;
        $nextCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'message' => 'Farmers fetched successfully.',
            'summary' => [
                'total_farmers' => Farmer::count(),
                'active_farmers' => Farmer::where('status', 'active')->count(),
                'inactive_farmers' => Farmer::where('status', '!=', 'active')->count(),
                'total_payable_balance' => round((float) Farmer::where('current_balance', '>', 0)->sum('current_balance'), 2),
                'next_farmer_code' => $nextCode,
            ],
            'data' => $farmers->items(),
            'pagination' => [
                'current_page' => $farmers->currentPage(),
                'per_page' => $farmers->perPage(),
                'total' => $farmers->total(),
                'last_page' => $farmers->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/farmers
     * Register a new farmer.
     */
    public function farmersStore(Request $request)
    {
        $nextNum = (Farmer::max('id') ?? 0) + 1;
        $defaultCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        if (!$request->filled('farmer_code')) {
            $request->merge(['farmer_code' => $defaultCode]);
        }

        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code',
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vehicle' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'cow_milk_rate' => 'nullable|numeric|min:0',
            'buffalo_milk_rate' => 'nullable|numeric|min:0',
            'branch_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'anamat' => 'nullable|numeric|min:0',
            'building_fund' => 'nullable|numeric|min:0',
            'installment' => 'nullable|numeric|min:0',
            'etc_amount' => 'nullable|numeric|min:0',
            'grant_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        try {
            $tableColumns = Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer = Farmer::create($saveData);
        AuditLog::log('Created Farmer (API)', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code, 'name' => $farmer->name]);

        return response()->json([
            'success' => true,
            'message' => "Farmer {$farmer->name} ({$farmer->farmer_code}) registered successfully.",
            'data' => $farmer->load(['collectionCenter', 'rateChart', 'route']),
        ], 201);
    }

    /**
     * GET /api/v1/procurement/farmers/{farmer}
     * Get single farmer profile with summary, recent collections, advances, deductions, and ledgers.
     */
    public function farmersShow(Farmer $farmer)
    {
        $farmer->load(['collectionCenter', 'rateChart', 'route', 'advances.repayments', 'settlements']);

        $recentCollections = MilkCollection::where('farmer_id', $farmer->id)->latest('collection_date')->latest('id')->take(30)->get();
        $recentLedgers = FarmerLedger::where('farmer_id', $farmer->id)->latest('transaction_date')->latest('id')->take(30)->get();
        $deductions = Schema::hasTable('farmer_deductions')
            ? FarmerDeduction::where('farmer_id', $farmer->id)->latest('entry_date')->get()
            : collect();

        $totalMilkLiters = round((float) MilkCollection::where('farmer_id', $farmer->id)->sum('quantity_liters'), 2);
        $totalGrossEarned = round((float) MilkCollection::where('farmer_id', $farmer->id)->sum('net_amount'), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'farmer' => $farmer,
                'kpis' => [
                    'total_milk_liters' => $totalMilkLiters,
                    'total_gross_earned' => $totalGrossEarned,
                    'current_balance' => (float) $farmer->current_balance,
                    'total_advances' => round((float) $farmer->advances->sum('amount'), 2),
                    'total_deductions_given' => round((float) $deductions->where('transaction_type', 'given')->sum('amount'), 2),
                ],
                'recent_collections' => $recentCollections,
                'recent_ledgers' => $recentLedgers,
                'deductions' => $deductions,
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/farmers/{farmer}/passbook
     * Get farmer passbook statement for a date range.
     */
    public function farmersPassbook(Request $request, Farmer $farmer)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->orderBy('collection_date')
            ->get();

        $advances = FarmerAdvance::where('farmer_id', $farmer->id)
            ->whereBetween('advance_date', [$startDate, $endDate])
            ->orderBy('advance_date')
            ->get();

        $deductions = Schema::hasTable('farmer_deductions')
            ? FarmerDeduction::where('farmer_id', $farmer->id)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->orderBy('entry_date')
                ->get()
            : collect();

        $settlements = FarmerSettlement::where('farmer_id', $farmer->id)
            ->whereBetween('period_start', [$startDate, $endDate])
            ->orderBy('period_start')
            ->get();

        $ledgers = FarmerLedger::where('farmer_id', $farmer->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'farmer' => [
                    'id' => $farmer->id,
                    'farmer_code' => $farmer->farmer_code,
                    'name' => $farmer->name,
                    'phone' => $farmer->phone,
                    'village' => $farmer->village,
                    'current_balance' => (float) $farmer->current_balance,
                ],
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_milk_liters' => round((float) $collections->sum('quantity_liters'), 2),
                    'total_milk_amount' => round((float) $collections->sum('net_amount'), 2),
                    'total_advances' => round((float) $advances->sum('amount'), 2),
                    'total_deductions' => round((float) $deductions->where('transaction_type', 'given')->sum('amount'), 2),
                    'total_settled' => round((float) $settlements->sum('paid_amount'), 2),
                ],
                'collections' => $collections,
                'advances' => $advances,
                'deductions' => $deductions,
                'settlements' => $settlements,
                'ledgers' => $ledgers,
            ],
        ]);
    }

    /**
     * PUT /api/v1/procurement/farmers/{farmer}
     * Update an existing farmer profile.
     */
    public function farmersUpdate(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'farmer_code' => 'sometimes|required|string|unique:farmers,farmer_code,' . $farmer->id,
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'vehicle' => 'nullable|string|max:100',
            'route_id' => 'nullable|integer',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'cow_milk_rate' => 'nullable|numeric|min:0',
            'buffalo_milk_rate' => 'nullable|numeric|min:0',
            'branch_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'anamat' => 'nullable|numeric|min:0',
            'building_fund' => 'nullable|numeric|min:0',
            'installment' => 'nullable|numeric|min:0',
            'etc_amount' => 'nullable|numeric|min:0',
            'grant_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,blocked',
        ]);

        try {
            $tableColumns = Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer->update($saveData);
        AuditLog::log('Updated Farmer (API)', 'Farmer', $farmer->id);

        return response()->json([
            'success' => true,
            'message' => "Farmer {$farmer->name} updated successfully.",
            'data' => $farmer->fresh(['collectionCenter', 'rateChart', 'route']),
        ]);
    }

    /**
     * DELETE /api/v1/procurement/farmers/{farmer}
     * Delete a farmer record.
     */
    public function farmersDestroy(Farmer $farmer)
    {
        $name = $farmer->name;
        $code = $farmer->farmer_code;
        $id = $farmer->id;
        $farmer->delete();

        AuditLog::log('Deleted Farmer (API)', 'Farmer', $id, ['code' => $code, 'name' => $name]);

        return response()->json([
            'success' => true,
            'message' => "Farmer {$name} ({$code}) deleted successfully.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. RATE CHARTS (FAT/SNF) (rate-charts)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/rate-charts
     * List all rate charts with filters.
     */
    public function rateChartsIndex(Request $request)
    {
        $relations = ['farmers'];
        if (Schema::hasColumn('collection_centers', 'rate_chart_id')) {
            $relations[] = 'collectionCenters';
        }
        $query = RateChart::withCount($relations);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('category') && $request->category !== 'all' && Schema::hasColumn('rate_charts', 'category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('milk_type') && $request->milk_type !== 'all') {
            $query->where('milk_type', $request->milk_type);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('milk_type', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $rateCharts = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Rate charts fetched successfully.',
            'data' => $rateCharts->items(),
            'pagination' => [
                'current_page' => $rateCharts->currentPage(),
                'per_page' => $rateCharts->perPage(),
                'total' => $rateCharts->total(),
                'last_page' => $rateCharts->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/rate-charts
     * Create a new Rate Chart.
     */
    public function rateChartsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|in:collection,milk_sale,chilling_center',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'format' => 'nullable|string|in:fat_snf,fat_clr,fat_only,fixed_rate',
            'type' => 'nullable|string|in:rate_per_kg,increase_per_point,matrix_slab,flat',
            'calculation_type' => 'nullable|string|in:fat_snf_formula,matrix,flat,rate_per_kg',
            'starting_amount' => 'nullable|numeric|min:0',
            'fixed_rate' => 'nullable|numeric|min:0',
            'base_rate' => 'nullable|numeric|min:0',
            'fat_factor' => 'nullable|numeric|min:0',
            'snf_factor' => 'nullable|numeric|min:0',
            'min_fat' => 'nullable|numeric|min:0',
            'max_fat' => 'nullable|numeric|min:0',
            'min_snf' => 'nullable|numeric|min:0',
            'max_snf' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
            'fat_steps' => 'nullable',
            'snf_steps' => 'nullable',
            'fat_rules' => 'nullable',
            'snf_rules' => 'nullable',
        ]);

        $validated['category'] = $validated['category'] ?? 'collection';
        $validated['format'] = $validated['format'] ?? 'fat_clr';
        $validated['type'] = $validated['type'] ?? 'rate_per_kg';
        $validated['calculation_type'] = $validated['calculation_type'] ?? ($validated['type'] === 'matrix_slab' ? 'matrix' : ($validated['format'] === 'fixed_rate' ? 'flat' : ($validated['type'] === 'rate_per_kg' ? 'rate_per_kg' : 'fat_snf_formula')));
        $validated['starting_amount'] = (float) ($validated['starting_amount'] ?? 0);
        $validated['fixed_rate'] = (float) ($validated['fixed_rate'] ?? 0);
        $validated['base_rate'] = (float) ($validated['base_rate'] ?? ($validated['starting_amount'] > 0 ? $validated['starting_amount'] : ($validated['fixed_rate'] > 0 ? $validated['fixed_rate'] : 35.00)));
        $validated['fat_factor'] = (float) ($validated['fat_factor'] ?? 6.50);
        $validated['snf_factor'] = (float) ($validated['snf_factor'] ?? 4.20);
        $validated['min_fat'] = (float) ($validated['min_fat'] ?? 3.0);
        $validated['max_fat'] = (float) ($validated['max_fat'] ?? 10.0);
        $validated['min_snf'] = (float) ($validated['min_snf'] ?? 8.0);
        $validated['max_snf'] = (float) ($validated['max_snf'] ?? 10.0);
        $validated['effective_date'] = $validated['effective_date'] ?? date('Y-m-d');
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['is_default'] = $request->boolean('is_default');

        foreach (['fat_steps', 'snf_steps', 'fat_rules', 'snf_rules'] as $field) {
            $val = $request->input($field);
            $validated[$field] = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
        }

        if ($validated['is_default']) {
            RateChart::where('milk_type', $validated['milk_type'])
                ->where('category', $validated['category'])
                ->update(['is_default' => false]);
        }

        $chart = RateChart::create($validated);
        AuditLog::log('Created Rate Chart (API)', 'RateChart', $chart->id);

        return response()->json([
            'success' => true,
            'message' => "Rate chart '{$chart->name}' created successfully.",
            'data' => $chart,
        ], 201);
    }

    /**
     * GET /api/v1/procurement/rate-charts/{rate}
     * View single Rate Chart with assigned farmers and centers.
     */
    public function rateChartsShow(RateChart $rate)
    {
        $rate->load(['farmers:id,farmer_code,name,phone,animal_type,rate_chart_id', 'slabs']);
        return response()->json([
            'success' => true,
            'data' => $rate,
        ]);
    }

    /**
     * PUT /api/v1/procurement/rate-charts/{rate}
     * Update an existing Rate Chart.
     */
    public function rateChartsUpdate(Request $request, RateChart $rate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|in:collection,milk_sale,chilling_center',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'format' => 'nullable|string|in:fat_snf,fat_clr,fat_only,fixed_rate',
            'type' => 'nullable|string|in:rate_per_kg,increase_per_point,matrix_slab,flat',
            'calculation_type' => 'nullable|string|in:fat_snf_formula,matrix,flat,rate_per_kg',
            'starting_amount' => 'nullable|numeric|min:0',
            'fixed_rate' => 'nullable|numeric|min:0',
            'base_rate' => 'nullable|numeric|min:0',
            'fat_factor' => 'nullable|numeric|min:0',
            'snf_factor' => 'nullable|numeric|min:0',
            'min_fat' => 'nullable|numeric|min:0',
            'max_fat' => 'nullable|numeric|min:0',
            'min_snf' => 'nullable|numeric|min:0',
            'max_snf' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive',
            'fat_steps' => 'nullable',
            'snf_steps' => 'nullable',
            'fat_rules' => 'nullable',
            'snf_rules' => 'nullable',
        ]);

        $validated['category'] = $validated['category'] ?? $rate->category ?? 'collection';
        $validated['format'] = $validated['format'] ?? $rate->format ?? 'fat_clr';
        $validated['type'] = $validated['type'] ?? $rate->type ?? 'rate_per_kg';
        $validated['status'] = $validated['status'] ?? $rate->status ?? 'active';

        foreach (['fat_steps', 'snf_steps', 'fat_rules', 'snf_rules'] as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                $validated[$field] = is_string($val) ? (json_decode($val, true) ?: []) : (is_array($val) ? $val : []);
            }
        }

        if ($request->has('is_default')) {
            $validated['is_default'] = $request->boolean('is_default');
            if ($validated['is_default']) {
                RateChart::where('milk_type', $validated['milk_type'])
                    ->where('category', $validated['category'])
                    ->where('id', '!=', $rate->id)
                    ->update(['is_default' => false]);
            }
        }

        $rate->update($validated);
        AuditLog::log('Updated Rate Chart (API)', 'RateChart', $rate->id);

        return response()->json([
            'success' => true,
            'message' => "Rate chart '{$rate->name}' updated successfully.",
            'data' => $rate->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/procurement/rate-charts/{rate}
     * Delete a Rate Chart and unassign farmers/centers.
     */
    public function rateChartsDestroy(RateChart $rate)
    {
        $name = $rate->name;
        $id = $rate->id;

        Farmer::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
        if (Schema::hasColumn('collection_centers', 'rate_chart_id')) {
            CollectionCenter::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
        }
        $rate->delete();

        AuditLog::log('Deleted Rate Chart (API)', 'RateChart', $id);

        return response()->json([
            'success' => true,
            'message' => "Rate chart '{$name}' deleted successfully.",
        ]);
    }

    /**
     * POST /api/v1/procurement/rate-charts/{rate}/assign
     * Assign Rate Chart to Farmers and/or Collection Centers.
     */
    public function rateChartsAssign(Request $request, RateChart $rate)
    {
        $validated = $request->validate([
            'farmer_ids' => 'nullable|array',
            'farmer_ids.*' => 'exists:farmers,id',
            'center_ids' => 'nullable|array',
            'center_ids.*' => 'exists:collection_centers,id',
        ]);

        Farmer::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
        if (!empty($validated['farmer_ids'])) {
            Farmer::whereIn('id', $validated['farmer_ids'])->update(['rate_chart_id' => $rate->id]);
        }

        if (Schema::hasColumn('collection_centers', 'rate_chart_id')) {
            CollectionCenter::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
            if (!empty($validated['center_ids'])) {
                CollectionCenter::whereIn('id', $validated['center_ids'])->update(['rate_chart_id' => $rate->id]);
            }
        }

        AuditLog::log('Assigned Rate Chart (API)', 'RateChart', $rate->id);

        return response()->json([
            'success' => true,
            'message' => "Rate chart '{$rate->name}' assigned successfully.",
            'data' => [
                'rate_chart_id' => $rate->id,
                'assigned_farmers_count' => count($validated['farmer_ids'] ?? []),
                'assigned_centers_count' => count($validated['center_ids'] ?? []),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/rate-charts/apply-correction
     * Apply historical rate correction across a date range and recalculate milk collections.
     */
    public function rateChartsApplyCorrection(Request $request)
    {
        $validated = $request->validate([
            'rate_chart_id' => 'required|exists:rate_charts,id',
            'category' => 'required|in:collection,milk_sale,chilling_center',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_shift' => 'required|in:morning,evening',
            'end_shift' => 'required|in:morning,evening',
            'farmer_ids' => 'nullable|array',
            'center_ids' => 'nullable|array',
        ]);

        $chart = RateChart::findOrFail($validated['rate_chart_id']);
        $updatedCount = 0;

        if ($validated['category'] === 'collection') {
            $query = MilkCollection::whereBetween('collection_date', [$validated['start_date'], $validated['end_date']])
                ->where('milk_type', $validated['milk_type']);

            if (!empty($validated['farmer_ids'])) {
                $query->whereIn('farmer_id', $validated['farmer_ids']);
            }
            if (!empty($validated['center_ids'])) {
                $query->whereIn('collection_center_id', $validated['center_ids']);
            }

            $collections = $query->get();
            foreach ($collections as $collection) {
                $newRate = $chart->calculateRate((float) $collection->fat_percentage, (float) $collection->snf_percentage);
                $newGross = round((float) $collection->quantity_liters * $newRate, 2);
                $newNet = round($newGross + (float) $collection->bonus_amount - (float) $collection->deduction_amount, 2);

                $collection->update([
                    'calculated_rate' => $newRate,
                    'applied_rate' => $newRate,
                    'gross_amount' => $newGross,
                    'net_amount' => $newNet,
                ]);
                $updatedCount++;
            }
        }

        if (Schema::hasTable('rate_corrections')) {
            RateCorrection::create([
                'rate_chart_id' => $chart->id,
                'category' => $validated['category'],
                'milk_type' => $validated['milk_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_shift' => $validated['start_shift'],
                'end_shift' => $validated['end_shift'],
                'farmer_ids' => $validated['farmer_ids'] ?? [],
                'center_ids' => $validated['center_ids'] ?? [],
                'records_updated' => $updatedCount,
                'applied_by' => Auth::id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Rate correction applied using '{$chart->name}'. {$updatedCount} collection record(s) recalculated.",
            'data' => [
                'rate_chart_id' => $chart->id,
                'records_updated' => $updatedCount,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. FARMER SETTLEMENTS (settlements)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/settlements
     * List farmer settlements with filters and summary.
     */
    public function settlementsIndex(Request $request)
    {
        $query = FarmerSettlement::with(['farmer', 'settler']);

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('period_start', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $settlements = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Farmer settlements fetched successfully.',
            'summary' => [
                'total_settlements' => FarmerSettlement::count(),
                'total_settled_amount' => round((float) FarmerSettlement::where('status', 'paid')->sum('paid_amount'), 2),
            ],
            'data' => $settlements->items(),
            'pagination' => [
                'current_page' => $settlements->currentPage(),
                'per_page' => $settlements->perPage(),
                'total' => $settlements->total(),
                'last_page' => $settlements->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/settlements/preview
     * Preview pending collections, advances, and net payable for a farmer before settling.
     */
    public function settlementsPreview(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);
        $periodStart = $validated['period_start'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $periodEnd = $validated['period_end'] ?? Carbon::today()->format('Y-m-d');

        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$periodStart, $periodEnd])
            ->where('payment_status', 'pending')
            ->orderBy('collection_date')
            ->get();

        $totalLiters = round((float) $collections->sum('quantity_liters'), 2);
        $grossAmount = round((float) $collections->sum('net_amount'), 2);
        $pendingAdvance = round((float) FarmerAdvance::where('farmer_id', $farmer->id)
            ->where('status', 'pending')
            ->sum('amount'), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'farmer' => [
                    'id' => $farmer->id,
                    'farmer_code' => $farmer->farmer_code,
                    'name' => $farmer->name,
                    'current_balance' => (float) $farmer->current_balance,
                ],
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'pending_collections_count' => $collections->count(),
                'total_liters' => $totalLiters,
                'gross_amount' => $grossAmount,
                'pending_advance' => $pendingAdvance,
                'estimated_net_payable' => round(max(0, $grossAmount - $pendingAdvance), 2),
                'collections' => $collections,
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/settlements
     * Process and record a farmer settlement.
     */
    public function settlementsStore(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'advance_recovered' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'required|in:Cash,UPI,Bank Transfer,Cheque',
            'payment_reference' => 'nullable|string',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);
        $collections = MilkCollection::where('farmer_id', $farmer->id)
            ->whereBetween('collection_date', [$validated['period_start'], $validated['period_end']])
            ->where('payment_status', 'pending')
            ->get();

        $totalLiters = round((float) $collections->sum('quantity_liters'), 2);
        $grossAmount = round((float) $collections->sum('net_amount'), 2);
        $advance = (float) ($validated['advance_recovered'] ?? 0);
        $bonus = (float) ($validated['bonus_amount'] ?? 0);
        $deduction = (float) ($validated['deduction_amount'] ?? 0);

        $netPayable = round($grossAmount + $bonus - $deduction - $advance, 2);
        $settlementNumber = 'SETTLE-' . ((FarmerSettlement::max('id') ?? 0) + 101);

        $settlement = FarmerSettlement::create([
            'settlement_number' => $settlementNumber,
            'farmer_id' => $farmer->id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'total_liters' => $totalLiters,
            'gross_amount' => $grossAmount,
            'bonus_amount' => $bonus,
            'deduction_amount' => $deduction,
            'advance_recovered' => $advance,
            'net_payable' => $netPayable,
            'paid_amount' => $netPayable,
            'payment_mode' => $validated['payment_mode'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'status' => 'paid',
            'settled_by' => Auth::id(),
            'settled_at' => Carbon::now(),
        ]);

        if ($collections->isNotEmpty()) {
            MilkCollection::whereIn('id', $collections->pluck('id'))->update(['payment_status' => 'settled']);
        }

        if ($advance > 0) {
            FarmerAdvance::where('farmer_id', $farmer->id)
                ->where('status', 'pending')
                ->take(1)
                ->update(['status' => 'recovered', 'deducted_amount' => $advance]);
        }

        $newBalance = max(0, (float) $farmer->current_balance - $grossAmount);
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => Carbon::today(),
            'type' => 'debit',
            'amount' => $netPayable,
            'balance' => $newBalance,
            'reference_type' => 'settlement',
            'reference_id' => $settlement->id,
            'description' => "Period Settlement {$settlementNumber} Paid via {$validated['payment_mode']}",
        ]);

        AuditLog::log('Generated Farmer Settlement (API)', 'FarmerSettlement', $settlement->id, ['net' => $netPayable]);

        return response()->json([
            'success' => true,
            'message' => "Settlement {$settlementNumber} for ₹" . number_format($netPayable, 2) . " processed successfully.",
            'data' => $settlement->load(['farmer', 'settler']),
        ], 201);
    }

    /**
     * GET /api/v1/procurement/settlements/{settlement}
     * View single farmer settlement details.
     */
    public function settlementsShow(FarmerSettlement $settlement)
    {
        $settlement->load(['farmer', 'settler']);
        return response()->json([
            'success' => true,
            'data' => $settlement,
        ]);
    }

    /**
     * DELETE /api/v1/procurement/settlements/{settlement}
     * Delete a farmer settlement record.
     */
    public function settlementsDestroy(FarmerSettlement $settlement)
    {
        $num = $settlement->settlement_number;
        $id = $settlement->id;
        $settlement->delete();

        AuditLog::log('Deleted Farmer Settlement (API)', 'FarmerSettlement', $id, ['settlement_number' => $num]);

        return response()->json([
            'success' => true,
            'message' => "Settlement {$num} deleted successfully.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. ADVANCE & LOANS (advances)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/advances
     * List farmer advances & loans with summary KPIs.
     */
    public function advancesIndex(Request $request)
    {
        $query = FarmerAdvance::with(['farmer', 'repayments']);

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('advance_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $advances = $query->latest('advance_date')->latest('id')->paginate($perPage);

        $totalAdvance = round((float) FarmerAdvance::sum('amount'), 2);
        $totalPaid = round((float) (FarmerAdvance::sum('paid_amount') ?: FarmerAdvance::sum('deducted_amount')), 2);
        $totalBalance = round(max(0, $totalAdvance - $totalPaid), 2);
        $nextVoucherNum = (FarmerAdvance::max('id') ?: 0) + 1;

        return response()->json([
            'success' => true,
            'message' => 'Farmer advances fetched successfully.',
            'summary' => [
                'total_advance_issued' => $totalAdvance,
                'total_paid_recovered' => $totalPaid,
                'total_outstanding_balance' => $totalBalance,
                'next_voucher_no' => str_pad($nextVoucherNum, 3, '0', STR_PAD_LEFT),
            ],
            'data' => $advances->items(),
            'pagination' => [
                'current_page' => $advances->currentPage(),
                'per_page' => $advances->perPage(),
                'total' => $advances->total(),
                'last_page' => $advances->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/advances
     * Issue a new advance/loan to a farmer.
     */
    public function advancesStore(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'voucher_no' => 'nullable|string|max:50',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Cheque',
            'purpose' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        $voucherNo = $validated['voucher_no'] ?? null;
        if (empty($voucherNo)) {
            $nextNum = (FarmerAdvance::max('id') ?: 0) + 1;
            $voucherNo = str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }

        $interestRate = (float) ($validated['interest_rate'] ?? 0);
        $amount = (float) $validated['amount'];
        $paymentMode = $validated['payment_mode'] ?? 'Cash';
        $notes = $validated['notes'] ?? $validated['purpose'] ?? "-PAID BY {$paymentMode}";

        $advance = FarmerAdvance::create([
            'farmer_id' => $farmer->id,
            'advance_date' => $validated['advance_date'],
            'voucher_no' => $voucherNo,
            'amount' => $amount,
            'interest_rate' => $interestRate,
            'deducted_amount' => 0.00,
            'paid_amount' => 0.00,
            'interest_balance' => 0.00,
            'payment_mode' => $paymentMode,
            'purpose' => $validated['purpose'] ?? 'Cash Advance',
            'status' => 'pending',
            'notes' => $notes,
            'issued_by' => Auth::id(),
        ]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['advance_date'],
            'type' => 'debit',
            'amount' => $amount,
            'balance' => $farmer->current_balance - $amount,
            'reference_type' => 'advance',
            'reference_id' => $advance->id,
            'description' => "Advance Voucher #{$voucherNo} ({$paymentMode}) - {$notes}",
        ]);

        $farmer->update([
            'current_balance' => $farmer->current_balance - $amount,
        ]);

        AuditLog::log('Issued Farmer Advance (API)', 'FarmerAdvance', $advance->id, [
            'farmer' => $farmer->name,
            'voucher' => $voucherNo,
            'amount' => $amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Advance Voucher #{$voucherNo} of ₹" . number_format($amount, 2) . " issued to {$farmer->name}.",
            'data' => $advance->load('farmer'),
        ], 201);
    }

    /**
     * GET /api/v1/procurement/advances/{advance}
     * View single advance details with repayments.
     */
    public function advancesShow(FarmerAdvance $advance)
    {
        $advance->load(['farmer', 'repayments']);
        return response()->json([
            'success' => true,
            'data' => $advance,
        ]);
    }

    /**
     * PUT /api/v1/procurement/advances/{advance}
     * Update an existing farmer advance.
     */
    public function advancesUpdate(Request $request, FarmerAdvance $advance)
    {
        $validated = $request->validate([
            'advance_date' => 'required|date',
            'voucher_no' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Cheque',
            'notes' => 'nullable|string|max:1000',
        ]);

        $amount = (float) $validated['amount'];
        $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : (float) $advance->paid_amount;
        $status = $paidAmount >= $amount ? 'recovered' : ($paidAmount > 0 ? 'partial' : 'pending');

        $advance->update([
            'advance_date' => $validated['advance_date'],
            'voucher_no' => $validated['voucher_no'] ?? $advance->voucher_no,
            'amount' => $amount,
            'interest_rate' => (float) ($validated['interest_rate'] ?? $advance->interest_rate),
            'paid_amount' => $paidAmount,
            'deducted_amount' => $paidAmount,
            'payment_mode' => $validated['payment_mode'] ?? $advance->payment_mode,
            'notes' => $validated['notes'] ?? $advance->notes,
            'status' => $status,
        ]);

        AuditLog::log('Updated Farmer Advance (API)', 'FarmerAdvance', $advance->id, [
            'voucher' => $advance->voucher_no,
            'amount' => $amount,
            'paid' => $paidAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Advance Voucher #{$advance->voucher_no} updated successfully.",
            'data' => $advance->fresh(['farmer', 'repayments']),
        ]);
    }

    /**
     * DELETE /api/v1/procurement/advances/{advance}
     * Delete an advance record.
     */
    public function advancesDestroy(FarmerAdvance $advance)
    {
        $voucher = $advance->voucher_no;
        $id = $advance->id;
        $advance->delete();

        AuditLog::log('Deleted Farmer Advance (API)', 'FarmerAdvance', $id, ['voucher' => $voucher]);

        return response()->json([
            'success' => true,
            'message' => "Advance Voucher #{$voucher} deleted successfully.",
        ]);
    }

    /**
     * POST /api/v1/procurement/advances/receive
     * Receive repayment against an active farmer advance.
     */
    public function advancesReceive(Request $request)
    {
        $validated = $request->validate([
            'farmer_advance_id' => 'required|exists:farmer_advances,id',
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Cheque,Milk Deduction',
            'remark' => 'nullable|string|max:500',
        ]);

        $advance = FarmerAdvance::with('farmer')->findOrFail($validated['farmer_advance_id']);
        $farmer = $advance->farmer;
        $payAmount = (float) $validated['amount'];

        $currentPaid = (float) ($advance->paid_amount ?: $advance->deducted_amount);
        $newPaid = $currentPaid + $payAmount;
        $status = $newPaid >= (float) $advance->amount ? 'recovered' : 'partial';

        $advance->update([
            'paid_amount' => $newPaid,
            'deducted_amount' => $newPaid,
            'paid_date' => $validated['repayment_date'],
            'status' => $status,
        ]);

        $repayment = null;
        if (Schema::hasTable('farmer_advance_repayments')) {
            $repayment = FarmerAdvanceRepayment::create([
                'farmer_advance_id' => $advance->id,
                'farmer_id' => $farmer->id,
                'repayment_date' => $validated['repayment_date'],
                'amount' => $payAmount,
                'payment_mode' => $validated['payment_mode'] ?? 'Cash',
                'remark' => $validated['remark'] ?? 'Payment Received',
                'recorded_by' => Auth::id(),
            ]);
        }

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['repayment_date'],
            'type' => 'credit',
            'amount' => $payAmount,
            'balance' => $farmer->current_balance + $payAmount,
            'reference_type' => 'advance_repayment',
            'reference_id' => $advance->id,
            'description' => "Advance Repayment for Voucher #{$advance->voucher_no} (" . ($validated['payment_mode'] ?? 'Cash') . ")",
        ]);

        $farmer->update([
            'current_balance' => $farmer->current_balance + $payAmount,
        ]);

        AuditLog::log('Received Advance Payment (API)', 'FarmerAdvance', $advance->id, [
            'farmer' => $farmer->name,
            'voucher' => $advance->voucher_no,
            'received' => $payAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Payment of ₹" . number_format($payAmount, 2) . " received against Voucher #{$advance->voucher_no}.",
            'data' => [
                'advance' => $advance->fresh(['farmer', 'repayments']),
                'repayment' => $repayment,
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/advances/ledger/{farmer}
     * View complete advance ledger for a specific farmer.
     */
    public function advancesLedger(Farmer $farmer)
    {
        $advances = FarmerAdvance::with('repayments')
            ->where('farmer_id', $farmer->id)
            ->orderBy('advance_date', 'desc')
            ->get();

        $totalAdvance = round((float) $advances->sum('amount'), 2);
        $totalPaid = round((float) $advances->sum(fn($a) => $a->total_paid), 2);
        $totalPrincipal = round((float) $advances->sum(fn($a) => $a->principal_balance), 2);
        $totalInterest = round((float) $advances->sum('interest_balance'), 2);
        $totalBalance = round((float) $advances->sum(fn($a) => $a->total_balance), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'farmer' => [
                    'id' => $farmer->id,
                    'farmer_code' => $farmer->farmer_code,
                    'name' => $farmer->name,
                    'phone' => $farmer->phone,
                ],
                'summary' => [
                    'total_advance' => $totalAdvance,
                    'total_paid' => $totalPaid,
                    'principal_balance' => $totalPrincipal,
                    'interest_balance' => $totalInterest,
                    'total_balance' => $totalBalance,
                ],
                'advances' => $advances,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. DEDUCTIONS (deductions)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/v1/procurement/deductions
     * List farmer deductions with filters and summary totals.
     */
    public function deductionsIndex(Request $request)
    {
        $query = FarmerDeduction::with('farmer');

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->filled('deduction_type') && $request->deduction_type !== 'all') {
            $query->where('deduction_type', $request->deduction_type);
        }
        if ($request->filled('transaction_type') && $request->transaction_type !== 'all') {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('entry_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('comments', 'like', "%{$search}%")
                  ->orWhere('deduction_type', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->get('per_page', 25), 1), 200);
        $deductions = $query->latest('entry_date')->latest('id')->paginate($perPage);

        $totalGiven = round((float) FarmerDeduction::where('transaction_type', 'given')->sum('amount'), 2);
        $totalReceived = round((float) FarmerDeduction::where('transaction_type', 'received')->sum('amount'), 2);

        return response()->json([
            'success' => true,
            'message' => 'Farmer deductions fetched successfully.',
            'summary' => [
                'total_given' => $totalGiven,
                'total_received' => $totalReceived,
                'net_deduction_balance' => round($totalGiven - $totalReceived, 2),
            ],
            'deduction_types' => [
                'cattle_feed' => 'Cattle Feed (पशु आहार)',
                'medicine' => 'Medicine (दवाई)',
                'ghee_butter' => 'Ghee / Butter',
                'doctor_fee' => 'Doctor Fee',
                'insurance' => 'Insurance',
                'advance_recovery' => 'Advance Recovery',
                'store_purchase' => 'Store Purchase',
                'equipment' => 'Equipment / बर्तन',
                'other' => 'Other (अन्य)',
            ],
            'data' => $deductions->items(),
            'pagination' => [
                'current_page' => $deductions->currentPage(),
                'per_page' => $deductions->perPage(),
                'total' => $deductions->total(),
                'last_page' => $deductions->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/deductions
     * Record a new farmer deduction or credit entry.
     */
    public function deductionsStore(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'entry_date' => 'required|date',
            'deduction_type' => 'required|string|max:100',
            'transaction_type' => 'required|in:given,received',
            'amount' => 'required|numeric|min:0.01',
            'comments' => 'nullable|string|max:1000',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        $deduction = FarmerDeduction::create([
            'farmer_id' => $farmer->id,
            'entry_date' => $validated['entry_date'],
            'deduction_type' => $validated['deduction_type'],
            'transaction_type' => $validated['transaction_type'],
            'amount' => $validated['amount'],
            'comments' => $validated['comments'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $amount = (float) $validated['amount'];
        $isGiven = $validated['transaction_type'] === 'given';
        $newBalance = $isGiven ? ($farmer->current_balance - $amount) : ($farmer->current_balance + $amount);
        $farmer->update(['current_balance' => $newBalance]);

        $typeLabel = ucfirst(str_replace('_', ' ', $validated['deduction_type']));
        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['entry_date'],
            'type' => $isGiven ? 'debit' : 'credit',
            'amount' => $amount,
            'balance' => $newBalance,
            'reference_type' => 'deduction',
            'reference_id' => $deduction->id,
            'description' => "Deduction ({$typeLabel} - " . ucfirst($validated['transaction_type']) . "): " . ($validated['comments'] ?? ''),
        ]);

        AuditLog::log('Recorded Farmer Deduction (API)', 'FarmerDeduction', $deduction->id, [
            'farmer' => $farmer->name,
            'type' => $deduction->deduction_type,
            'amount' => $deduction->amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Deduction of ₹" . number_format($deduction->amount, 2) . " for {$farmer->name} recorded successfully.",
            'data' => $deduction->load('farmer'),
        ], 201);
    }

    /**
     * GET /api/v1/procurement/deductions/{deduction}
     * View single deduction record.
     */
    public function deductionsShow(FarmerDeduction $deduction)
    {
        $deduction->load('farmer');
        return response()->json([
            'success' => true,
            'data' => $deduction,
        ]);
    }

    /**
     * PUT /api/v1/procurement/deductions/{deduction}
     * Update an existing deduction record.
     */
    public function deductionsUpdate(Request $request, FarmerDeduction $deduction)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'deduction_type' => 'required|string|max:100',
            'transaction_type' => 'required|in:given,received',
            'amount' => 'required|numeric|min:0.01',
            'comments' => 'nullable|string|max:1000',
        ]);

        $farmer = $deduction->farmer;
        if ($farmer) {
            $oldAmount = (float) $deduction->amount;
            $revertedBalance = $deduction->transaction_type === 'given'
                ? ($farmer->current_balance + $oldAmount)
                : ($farmer->current_balance - $oldAmount);

            $newAmount = (float) $validated['amount'];
            $newBalance = $validated['transaction_type'] === 'given'
                ? ($revertedBalance - $newAmount)
                : ($revertedBalance + $newAmount);

            $farmer->update(['current_balance' => $newBalance]);
        }

        $deduction->update($validated);
        AuditLog::log('Updated Farmer Deduction (API)', 'FarmerDeduction', $deduction->id);

        return response()->json([
            'success' => true,
            'message' => 'Deduction updated successfully.',
            'data' => $deduction->fresh('farmer'),
        ]);
    }

    /**
     * DELETE /api/v1/procurement/deductions/{deduction}
     * Delete a deduction entry and revert farmer balance.
     */
    public function deductionsDestroy(FarmerDeduction $deduction)
    {
        $farmer = $deduction->farmer;
        if ($farmer) {
            $oldAmount = (float) $deduction->amount;
            $revertedBalance = $deduction->transaction_type === 'given'
                ? ($farmer->current_balance + $oldAmount)
                : ($farmer->current_balance - $oldAmount);
            $farmer->update(['current_balance' => $revertedBalance]);
        }

        FarmerLedger::where('reference_type', 'deduction')
            ->where('reference_id', $deduction->id)
            ->delete();

        $id = $deduction->id;
        $deduction->delete();

        AuditLog::log('Deleted Farmer Deduction (API)', 'FarmerDeduction', $id);

        return response()->json([
            'success' => true,
            'message' => 'Deduction entry deleted successfully.',
        ]);
    }

    /**
     * GET /api/v1/procurement/deductions/farmer/{farmer}
     * Get all deductions for a specific farmer.
     */
    public function deductionsFarmerHistory(Farmer $farmer)
    {
        $deductions = FarmerDeduction::where('farmer_id', $farmer->id)
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalGiven = round((float) $deductions->where('transaction_type', 'given')->sum('amount'), 2);
        $totalReceived = round((float) $deductions->where('transaction_type', 'received')->sum('amount'), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'farmer' => [
                    'id' => $farmer->id,
                    'farmer_code' => $farmer->farmer_code,
                    'name' => $farmer->name,
                    'phone' => $farmer->phone,
                ],
                'summary' => [
                    'total_given' => $totalGiven,
                    'total_received' => $totalReceived,
                    'net_balance' => round($totalGiven - $totalReceived, 2),
                ],
                'deductions' => $deductions,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. FARMER INVOICES & BILLING (farmer-invoices)
    |--------------------------------------------------------------------------
    */

    /**
     * Helper: Calculate accurate period billing figures for a farmer.
     */
    private function calculateInvoiceMetrics(int $farmerId, string $startDate, string $endDate): array
    {
        $farmer = Farmer::find($farmerId);

        $collections = MilkCollection::where('farmer_id', $farmerId)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->get();

        $totalQuantity = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum(function ($c) {
            $net = (float) $c->net_amount;
            if ($net > 0) {
                return $net;
            }
            $rate = (float) ($c->applied_rate ?: $c->calculated_rate ?: 0);
            return round((float) $c->quantity_liters * $rate, 2);
        }), 2);

        $feedDeduction = 0.00;
        $stationaryDeduction = 0.00;
        $advanceFromDeductions = 0.00;
        $otherCredit = 0.00;
        $transportCredit = 0.00;
        $subsidyCredit = 0.00;
        $incentiveCredit = 0.00;

        if (Schema::hasTable('farmer_deductions')) {
            $deductions = FarmerDeduction::where('farmer_id', $farmerId)
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->get();

            $given = $deductions->where('transaction_type', 'given');
            $feedDeduction = round((float) $given->where('deduction_type', 'cattle_feed')->sum('amount'), 2);
            $advanceFromDeductions = round((float) $given->where('deduction_type', 'advance_recovery')->sum('amount'), 2);
            $stationaryDeduction = round((float) $given->whereNotIn('deduction_type', ['cattle_feed', 'advance_recovery'])->sum('amount'), 2);
            $otherCredit = round((float) $deductions->where('transaction_type', 'received')->sum('amount'), 2);
        }

        if (Schema::hasTable('product_sales')) {
            $farmerCode = $farmer ? $farmer->farmer_code : null;
            $unpaidSalesDue = (float) \App\Models\ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                $q->where('farmer_id', $farmerId);
                if (!empty($farmerCode)) {
                    $q->orWhere('farmer_code', $farmerCode);
                }
            })
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->sum('remaining_amount');
            $feedDeduction = round($feedDeduction + $unpaidSalesDue, 2);
        }

        $advanceFromLoans = 0.00;
        if (Schema::hasTable('farmer_advances')) {
            $advances = FarmerAdvance::where('farmer_id', $farmerId)
                ->whereBetween('advance_date', [$startDate, $endDate])
                ->where('status', '!=', 'recovered')
                ->get();

            $advanceFromLoans = round((float) $advances->sum(function ($adv) {
                return (float) $adv->principal_balance;
            }), 2);
        }

        $advanceDeduction = round($advanceFromLoans + $advanceFromDeductions, 2);
        $otherDeduction = 0.00;
        $totalCredit = round($otherCredit + $transportCredit + $subsidyCredit + $incentiveCredit, 2);
        $totalDeduction = round($stationaryDeduction + $feedDeduction + $advanceDeduction, 2);
        $totalAmount = round($totalCredit + $milkAmount, 2);
        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);
        $rawPayable = round(max(0, $totalAmount - $totalDeduction), 2);
        $roundOff = 0.00;
        $netPayment = $rawPayable;

        return [
            'total_quantity' => $totalQuantity,
            'milk_amount' => $milkAmount,
            'stationary_deduction' => $stationaryDeduction,
            'feed_deduction' => $feedDeduction,
            'advance_deduction' => $advanceDeduction,
            'other_deduction' => $otherDeduction,
            'total_deduction' => $totalDeduction,
            'other_credit' => $otherCredit,
            'transport_credit' => $transportCredit,
            'subsidy_credit' => $subsidyCredit,
            'incentive_credit' => $incentiveCredit,
            'total_credit' => $totalCredit,
            'total_amount' => $totalAmount,
            'round_off' => $roundOff,
            'previous_balance' => $previousBalance,
            'net_payment' => $netPayment,
        ];
    }

    /**
     * GET /api/v1/procurement/farmer-invoices
     * List farmer invoices (pending & generated) with summary totals.
     */
    public function invoicesIndex(Request $request)
    {
        $query = FarmerInvoice::with('farmer');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('period_start', [$startDate, $endDate])
                  ->orWhereBetween('period_end', [$startDate, $endDate]);
            });
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('farmer', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('farmer_code', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min(max((int) $request->get('per_page', 50), 1), 200);
        $invoices = $query->orderBy('invoice_number', 'desc')->paginate($perPage);

        $allMatching = (clone $query)->get();

        return response()->json([
            'success' => true,
            'message' => 'Farmer invoices fetched successfully.',
            'summary' => [
                'total_invoices' => $allMatching->count(),
                'pending_count' => $allMatching->where('status', 'pending')->count(),
                'generated_count' => $allMatching->where('status', 'generated')->count(),
                'total_quantity' => round((float) $allMatching->sum('total_quantity'), 2),
                'total_milk_amount' => round((float) $allMatching->sum('milk_amount'), 2),
                'total_deduction' => round((float) $allMatching->sum('total_deduction'), 2),
                'total_credit' => round((float) $allMatching->sum('total_credit'), 2),
                'total_net_payment' => round((float) $allMatching->sum('net_payment'), 2),
            ],
            'data' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/procurement/farmer-invoices/generate
     * Generate or refresh pending invoices for active farmers for a given period.
     */
    public function invoicesGenerate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'farmer_ids' => 'nullable|array',
            'farmer_ids.*' => 'exists:farmers,id',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $farmersQuery = Farmer::where('status', 'active');
        if (!empty($validated['farmer_ids'])) {
            $farmersQuery->whereIn('id', $validated['farmer_ids']);
        }
        $farmers = $farmersQuery->get();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($farmers as $farmer) {
            $metrics = $this->calculateInvoiceMetrics($farmer->id, $startDate, $endDate);

            $existing = FarmerInvoice::where('farmer_id', $farmer->id)
                ->where('period_start', $startDate)
                ->where('period_end', $endDate)
                ->first();

            if ($existing) {
                if (!$existing->is_edited) {
                    $existing->update($metrics);
                    $updatedCount++;
                }
            } else {
                $nextNum = (FarmerInvoice::max('id') ?: 0) + 1;
                $invNo = str_pad($nextNum, 2, '0', STR_PAD_LEFT);
                while (FarmerInvoice::where('invoice_number', $invNo)->exists()) {
                    $nextNum++;
                    $invNo = str_pad($nextNum, 2, '0', STR_PAD_LEFT);
                }

                FarmerInvoice::create(array_merge([
                    'invoice_number' => $invNo,
                    'farmer_id' => $farmer->id,
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'status' => 'pending',
                    'is_edited' => false,
                    'payment_mode' => 'Bank Transfer',
                    'created_by' => Auth::id(),
                ], $metrics));

                $createdCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Invoices processed for period {$startDate} to {$endDate}: {$createdCount} created, {$updatedCount} refreshed.",
            'data' => [
                'period_start' => $startDate,
                'period_end' => $endDate,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/farmer-invoices/{invoice}
     * View single farmer invoice with breakdown of collections, deductions, and advances.
     */
    public function invoicesShow(FarmerInvoice $invoice)
    {
        $invoice->load('farmer');
        $start = Carbon::parse($invoice->period_start)->format('Y-m-d');
        $end = Carbon::parse($invoice->period_end)->format('Y-m-d');

        $collections = MilkCollection::where('farmer_id', $invoice->farmer_id)
            ->whereBetween('collection_date', [$start, $end])
            ->orderBy('collection_date')
            ->get();

        $deductions = Schema::hasTable('farmer_deductions')
            ? FarmerDeduction::where('farmer_id', $invoice->farmer_id)
                ->whereBetween('entry_date', [$start, $end])
                ->get()
            : collect();

        $advances = Schema::hasTable('farmer_advances')
            ? FarmerAdvance::where('farmer_id', $invoice->farmer_id)
                ->whereBetween('advance_date', [$start, $end])
                ->get()
            : collect();

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'collections' => $collections,
                'deductions' => $deductions,
                'advances' => $advances,
            ],
        ]);
    }

    /**
     * GET /api/v1/procurement/farmer-invoices/{invoice}/preview
     * Return JSON preview payload for invoice verification modal / slip.
     */
    public function invoicesPreview(FarmerInvoice $invoice)
    {
        $invoice->load('farmer');
        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * POST /api/v1/procurement/farmer-invoices/{invoice}/verify-save
     * Verify, adjust credits/deductions, and save/approve a farmer invoice.
     */
    public function invoicesVerifyAndSave(Request $request, FarmerInvoice $invoice)
    {
        $validated = $request->validate([
            'stationary_deduction' => 'nullable|numeric|min:0',
            'feed_deduction' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'other_credit' => 'nullable|numeric|min:0',
            'transport_credit' => 'nullable|numeric|min:0',
            'subsidy_credit' => 'nullable|numeric|min:0',
            'incentive_credit' => 'nullable|numeric|min:0',
            'round_off' => 'nullable|numeric',
            'status' => 'nullable|in:pending,generated',
            'payment_mode' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $stationary = (float) ($validated['stationary_deduction'] ?? $invoice->stationary_deduction);
        $feed = (float) ($validated['feed_deduction'] ?? $invoice->feed_deduction);
        $advance = (float) ($validated['advance_deduction'] ?? $invoice->advance_deduction);
        $otherDed = (float) ($validated['other_deduction'] ?? $invoice->other_deduction);

        $otherCred = (float) ($validated['other_credit'] ?? $invoice->other_credit);
        $transport = (float) ($validated['transport_credit'] ?? $invoice->transport_credit);
        $subsidy = (float) ($validated['subsidy_credit'] ?? $invoice->subsidy_credit);
        $incentive = (float) ($validated['incentive_credit'] ?? $invoice->incentive_credit);
        $roundOff = (float) ($validated['round_off'] ?? $invoice->round_off ?? 0);

        $milkAmount = (float) $invoice->milk_amount;
        $totalDeduction = round($stationary + $feed + $advance + $otherDed, 2);
        $totalCredit = round($otherCred + $transport + $subsidy + $incentive, 2);
        $totalAmount = round($milkAmount + $totalCredit, 2);
        $previousBalance = round(max(0, $totalDeduction - $totalAmount), 2);
        $netPayment = round(max(0, $totalAmount - $totalDeduction + $roundOff), 2);

        $invoice->update([
            'stationary_deduction' => $stationary,
            'feed_deduction' => $feed,
            'advance_deduction' => $advance,
            'other_deduction' => $otherDed,
            'total_deduction' => $totalDeduction,
            'other_credit' => $otherCred,
            'transport_credit' => $transport,
            'subsidy_credit' => $subsidy,
            'incentive_credit' => $incentive,
            'total_credit' => $totalCredit,
            'total_amount' => $totalAmount,
            'round_off' => $roundOff,
            'previous_balance' => $previousBalance,
            'net_payment' => $netPayment,
            'status' => $validated['status'] ?? 'generated',
            'is_edited' => true,
            'payment_mode' => $validated['payment_mode'] ?? $invoice->payment_mode,
            'payment_reference' => $validated['payment_reference'] ?? $invoice->payment_reference,
        ]);

        AuditLog::log('Verified & Saved Farmer Invoice (API)', 'FarmerInvoice', $invoice->id, [
            'invoice_number' => $invoice->invoice_number,
            'net_payment' => $netPayment,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Invoice #{$invoice->invoice_number} verified and saved successfully.",
            'data' => $invoice->fresh('farmer'),
        ]);
    }

    /**
     * POST /api/v1/procurement/farmer-invoices/bulk-approve
     * Approve multiple pending farmer invoices and mark them as generated.
     */
    public function invoicesBulkApprove(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:farmer_invoices,id',
        ]);

        $count = FarmerInvoice::whereIn('id', $validated['invoice_ids'])
            ->update(['status' => 'generated']);

        AuditLog::log('Bulk Approved Farmer Invoices (API)', 'FarmerInvoice', null, [
            'count' => $count,
            'ids' => $validated['invoice_ids'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} invoice(s) approved and moved to Generated Invoices.",
            'data' => ['approved_count' => $count],
        ]);
    }

    /**
     * POST /api/v1/procurement/farmer-invoices/bulk-delete
     * Delete multiple farmer invoices.
     */
    public function invoicesBulkDelete(Request $request)
    {
        $validated = $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:farmer_invoices,id',
        ]);

        $count = FarmerInvoice::whereIn('id', $validated['invoice_ids'])->delete();

        AuditLog::log('Bulk Deleted Farmer Invoices (API)', 'FarmerInvoice', null, [
            'count' => $count,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} invoice(s) deleted successfully.",
            'data' => ['deleted_count' => $count],
        ]);
    }

    /**
     * DELETE /api/v1/procurement/farmer-invoices/{invoice}
     * Delete a single farmer invoice.
     */
    public function invoicesDestroy(FarmerInvoice $invoice)
    {
        $num = $invoice->invoice_number;
        $id = $invoice->id;
        $invoice->delete();

        AuditLog::log('Deleted Farmer Invoice (API)', 'FarmerInvoice', $id, ['invoice_number' => $num]);

        return response()->json([
            'success' => true,
            'message' => "Invoice #{$num} deleted successfully.",
        ]);
    }
}
