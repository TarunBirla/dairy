<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RateChart;
use App\Models\RateChartSlab;
use App\Models\RateCorrection;
use App\Models\Farmer;
use App\Models\Customer;
use App\Models\CollectionCenter;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class RateChartController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $category = $request->get('category', 'all');
        $milk = $request->get('milk', 'all');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 20);

        $relations = ['farmers'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('collection_centers', 'rate_chart_id')) {
            $relations[] = 'collectionCenters';
        }
        $query = RateChart::withCount($relations);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($category && $category !== 'all' && \Illuminate\Support\Facades\Schema::hasColumn('rate_charts', 'category')) {
            $query->where('category', $category);
        }

        if ($milk && $milk !== 'all') {
            $query->where('milk_type', $milk);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('milk_type', 'like', "%{$search}%");
            });
        }

        $rateCharts = $query->latest('id')->paginate($perPage)->withQueryString();

        // Data for Rate Correction modal and Assign modal
        $activeRateCharts = RateChart::where('status', 'active')->orderBy('name')->get();

        $farmerColumns = ['id', 'farmer_code', 'name'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('farmers', 'name_hi')) {
            $farmerColumns[] = 'name_hi';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('farmers', 'animal_type')) {
            $farmerColumns[] = 'animal_type';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('farmers', 'rate_chart_id')) {
            $farmerColumns[] = 'rate_chart_id';
        }
        $farmers = Farmer::where('status', 'active')->orderBy('name')->get($farmerColumns);

        $customerColumns = ['id', 'customer_code', 'name'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'rate_chart_id')) {
            $customerColumns[] = 'rate_chart_id';
        }
        $customers = Customer::where('status', 'active')->orderBy('name')->get($customerColumns);

        $centerColumns = ['id', 'code', 'name'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('collection_centers', 'rate_chart_id')) {
            $centerColumns[] = 'rate_chart_id';
        }
        $collectionCenters = CollectionCenter::where('status', 'active')->orderBy('name')->get($centerColumns);

        return view('rates.index', compact(
            'rateCharts',
            'activeRateCharts',
            'farmers',
            'customers',
            'collectionCenters',
            'status',
            'category',
            'milk',
            'search',
            'perPage'
        ));
    }

    public function create()
    {
        return view('rates.create');
    }

    public function store(Request $request)
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

        // Parse JSON steps and rules
        $validated['fat_steps'] = $this->parseJsonField($request->input('fat_steps'));
        $validated['snf_steps'] = $this->parseJsonField($request->input('snf_steps'));
        $validated['fat_rules'] = $this->parseJsonField($request->input('fat_rules'));
        $validated['snf_rules'] = $this->parseJsonField($request->input('snf_rules'));

        $validated['is_default'] = $request->boolean('is_default');
        if ($validated['is_default']) {
            RateChart::where('milk_type', $validated['milk_type'])
                ->where('category', $validated['category'])
                ->update(['is_default' => false]);
        }

        $chart = RateChart::create($validated);
        AuditLog::log('Created Rate Chart', 'RateChart', $chart->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "Rate chart {$chart->name} created successfully!", 'chart' => $chart]);
        }

        return redirect()->route('rates.index')->with('success', "Rate chart {$chart->name} created successfully!");
    }

    public function edit(RateChart $rate)
    {
        return view('rates.edit', ['chart' => $rate]);
    }

    public function update(Request $request, RateChart $rate)
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
            'status' => 'required|in:active,inactive',
            'fat_steps' => 'nullable',
            'snf_steps' => 'nullable',
            'fat_rules' => 'nullable',
            'snf_rules' => 'nullable',
        ]);

        $validated['category'] = $validated['category'] ?? $rate->category ?? 'collection';
        $validated['format'] = $validated['format'] ?? $rate->format ?? 'fat_clr';
        $validated['type'] = $validated['type'] ?? $rate->type ?? 'rate_per_kg';
        $validated['calculation_type'] = $validated['calculation_type'] ?? ($validated['type'] === 'matrix_slab' ? 'matrix' : ($validated['format'] === 'fixed_rate' ? 'flat' : ($validated['type'] === 'rate_per_kg' ? 'rate_per_kg' : 'fat_snf_formula')));
        $validated['starting_amount'] = (float) ($validated['starting_amount'] ?? $rate->starting_amount ?? 0);
        $validated['fixed_rate'] = (float) ($validated['fixed_rate'] ?? $rate->fixed_rate ?? 0);
        $validated['base_rate'] = (float) ($validated['base_rate'] ?? ($validated['starting_amount'] > 0 ? $validated['starting_amount'] : ($validated['fixed_rate'] > 0 ? $validated['fixed_rate'] : $rate->base_rate)));

        $validated['fat_steps'] = $this->parseJsonField($request->input('fat_steps'));
        $validated['snf_steps'] = $this->parseJsonField($request->input('snf_steps'));
        $validated['fat_rules'] = $this->parseJsonField($request->input('fat_rules'));
        $validated['snf_rules'] = $this->parseJsonField($request->input('snf_rules'));

        $validated['is_default'] = $request->boolean('is_default');
        if ($validated['is_default']) {
            RateChart::where('milk_type', $validated['milk_type'])
                ->where('category', $validated['category'])
                ->where('id', '!=', $rate->id)
                ->update(['is_default' => false]);
        }

        $rate->update($validated);
        AuditLog::log('Updated Rate Chart', 'RateChart', $rate->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "Rate chart {$rate->name} updated successfully!"]);
        }

        return redirect()->route('rates.index')->with('success', 'Rate chart updated successfully.');
    }

    public function destroy(RateChart $rate)
    {
        $name = $rate->name;
        // Unassign farmers and centers
        Farmer::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
        CollectionCenter::where('rate_chart_id', $rate->id)->update(['rate_chart_id' => null]);
        $rate->delete();

        AuditLog::log('Deleted Rate Chart', 'RateChart', $rate->id);
        return redirect()->route('rates.index')->with('success', "Rate chart '{$name}' deleted successfully.");
    }

    /**
     * Assign Rate Chart to Collection Centers and Farmers
     */
    public function assign(Request $request, RateChart $rate)
    {
        $validated = $request->validate([
            'farmer_ids' => 'nullable|array',
            'farmer_ids.*' => 'exists:farmers,id',
            'center_ids' => 'nullable|array',
            'center_ids.*' => 'exists:collection_centers,id',
        ]);

        $farmerIds = $validated['farmer_ids'] ?? [];
        $centerIds = $validated['center_ids'] ?? [];

        DB::transaction(function () use ($rate, $farmerIds, $centerIds) {
            // Assign selected farmers
            if (!empty($farmerIds)) {
                Farmer::whereIn('id', $farmerIds)->update(['rate_chart_id' => $rate->id]);
            }

            // Assign selected centers
            if (!empty($centerIds)) {
                CollectionCenter::whereIn('id', $centerIds)->update(['rate_chart_id' => $rate->id]);
            }
        });

        AuditLog::log("Assigned Rate Chart {$rate->name}", 'RateChart', $rate->id);

        return redirect()->route('rates.index')->with('success', "Rate chart '{$rate->name}' assigned to " . count($centerIds) . " center(s) and " . count($farmerIds) . " farmer(s).");
    }

    /**
     * Apply Rate Correction (Reference: Screenshot 5)
     * Recalculates historical milk collections according to selected rate chart,
     * date range, shift, and specified farmers/customers.
     */
    public function applyCorrection(Request $request)
    {
        $validated = $request->validate([
            'filter_type' => 'required|in:collection,milk_sale,chilling_center',
            'apply_to' => 'required|in:farmer,customer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'shift' => 'required|in:morning_evening,morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed,mix,all',
            'rate_chart_id' => 'required|exists:rate_charts,id',
            'selected_ids' => 'nullable|array',
        ]);

        $rateChart = RateChart::findOrFail($validated['rate_chart_id']);
        $selectedIds = $validated['selected_ids'] ?? [];
        // Filter out empty strings or zeros
        $selectedIds = array_filter($selectedIds, fn($val) => !empty($val));

        $recordsUpdated = 0;
        $totalDifference = 0.00;

        DB::transaction(function () use ($validated, $rateChart, $selectedIds, &$recordsUpdated, &$totalDifference) {
            if ($validated['apply_to'] === 'farmer') {
                $query = MilkCollection::with('farmer')
                    ->whereBetween('collection_date', [$validated['from_date'], $validated['to_date']]);

                // Shift filter
                if ($validated['shift'] === 'morning') {
                    $query->where('shift', 'morning');
                } elseif ($validated['shift'] === 'evening') {
                    $query->where('shift', 'evening');
                }

                // Milk type filter
                if ($validated['milk_type'] !== 'all') {
                    $milkTypeSearch = ($validated['milk_type'] === 'mix') ? 'mixed' : $validated['milk_type'];
                    $query->where('milk_type', $milkTypeSearch);
                }

                // People filter (if selected, filter to those farmers; if empty, applies to everyone!)
                if (!empty($selectedIds)) {
                    $query->whereIn('farmer_id', $selectedIds);
                }

                $collections = $query->get();

                foreach ($collections as $collection) {
                    $oldNet = (float) $collection->net_amount;
                    $newRate = $rateChart->calculateRate((float) $collection->fat, (float) $collection->snf);
                    $newGross = round((float) $collection->quantity_liters * $newRate, 2);
                    $newNet = round($newGross + (float) $collection->bonus - (float) $collection->deduction, 2);
                    $diff = round($newNet - $oldNet, 2);

                    $collection->calculated_rate = $newRate;
                    $collection->applied_rate = $newRate;
                    $collection->gross_amount = $newGross;
                    $collection->net_amount = $newNet;
                    $collection->notes = trim(($collection->notes ? $collection->notes . "\n" : '') . "Rate correction applied [Chart: {$rateChart->name}, Prev Rate: ₹{$collection->applied_rate} -> New: ₹{$newRate}]");
                    $collection->save();

                    // Adjust farmer current balance if ledger enabled
                    if ($collection->farmer && $diff != 0) {
                        $collection->farmer->increment('current_balance', $diff);
                    }

                    $totalDifference += $diff;
                    $recordsUpdated++;
                }
            }

            // Record correction entry log
            RateCorrection::create([
                'filter_type' => $validated['filter_type'],
                'apply_to' => $validated['apply_to'],
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date'],
                'shift' => $validated['shift'],
                'milk_type' => $validated['milk_type'],
                'rate_chart_id' => $rateChart->id,
                'selected_ids' => !empty($selectedIds) ? array_values($selectedIds) : null,
                'records_updated' => $recordsUpdated,
                'total_difference' => $totalDifference,
                'applied_by' => auth()->id(),
            ]);

            AuditLog::log(
                "Applied Rate Correction: {$rateChart->name} ({$recordsUpdated} records updated, diff: ₹{$totalDifference})",
                'RateCorrection',
                $rateChart->id
            );
        });

        $msg = "Rate correction applied successfully! {$recordsUpdated} milk collection record(s) updated using chart '{$rateChart->name}'. Net financial adjustment: ₹" . number_format($totalDifference, 2);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'records_updated' => $recordsUpdated,
                'total_difference' => $totalDifference,
            ]);
        }

        return redirect()->route('rates.index')->with('success', $msg);
    }

    /**
     * Helper to safely parse JSON or array
     */
    private function parseJsonField($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }
}
