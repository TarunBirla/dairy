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
            'format' => 'nullable|string|in:fat_snf,fat_clr,fat_only,clr_only,fixed_rate',
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
            'format' => 'nullable|string|in:fat_snf,fat_clr,fat_only,clr_only,fixed_rate',
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
        $validated['status'] = $validated['status'] ?? $rate->status ?? 'active';
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

    public function setDefault(RateChart $rate)
    {
        $category = $rate->category ?? 'collection';
        RateChart::where('milk_type', $rate->milk_type)
            ->where('category', $category)
            ->where('id', '!=', $rate->id)
            ->update(['is_default' => false]);

        $rate->update(['is_default' => true]);
        AuditLog::log('Set Default Rate Chart', 'RateChart', $rate->id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Rate Chart '{$rate->name}' is now set as default."]);
        }

        return back()->with('success', "Rate Chart '{$rate->name}' is now set as default for " . ucfirst($rate->milk_type) . " milk.");
    }

    /**
     * Download sample CSV template for rate chart import.
     */
    public function sampleTemplate(Request $request)
    {
        $type = $request->get('format', 'matrix');
        $filename = ($type === 'columnar') 
            ? 'rate_chart_columnar_template.csv' 
            : (($type === 'fat_snf') ? 'rate_chart_fat_snf_matrix_template.csv' : 'rate_chart_fat_clr_matrix_template.csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'columnar') {
                fputcsv($handle, ['FAT', 'CLR', 'RATE']);
                for ($fat = 2.0; $fat <= 10.0; $fat += 0.5) {
                    for ($clr = 20.0; $clr <= 30.0; $clr += 1.0) {
                        $rate = round(10.0 + (($fat - 2.0) * 9.4) + (($clr - 20.0) * 0.5), 2);
                        fputcsv($handle, [number_format($fat, 1), number_format($clr, 1), number_format($rate, 2)]);
                    }
                }
            } elseif ($type === 'fat_snf') {
                // Matrix format: FAT x SNF
                $headerRow = ['FAT/SNF'];
                for ($s = 70; $s <= 100; $s += 5) {
                    $headerRow[] = number_format($s / 10.0, 1);
                }
                fputcsv($handle, $headerRow);

                for ($f = 20; $f <= 100; $f++) {
                    $fat = $f / 10.0;
                    $row = [number_format($fat, 1)];
                    for ($s = 70; $s <= 100; $s += 5) {
                        $snf = $s / 10.0;
                        $rate = round(15.0 + (($fat - 2.0) * 8.5) + (($snf - 7.0) * 2.0), 2);
                        $row[] = number_format($rate, 2);
                    }
                    fputcsv($handle, $row);
                }
            } else {
                // Matrix format: FAT x CLR (FAT 2.0 to 10.0, CLR 20.0 to 30.0)
                $headerRow = ['FAT/CLR'];
                for ($c = 20; $c <= 30; $c++) {
                    $headerRow[] = number_format($c, 1);
                }
                fputcsv($handle, $headerRow);

                for ($f = 20; $f <= 100; $f++) {
                    $fat = $f / 10.0;
                    $row = [number_format($fat, 1)];
                    for ($c = 20; $c <= 30; $c++) {
                        $clr = (float) $c;
                        $rate = round(10.0 + (($fat - 2.0) * 9.4) + (($clr - 20.0) * 0.3), 2);
                        $row[] = number_format($rate, 2);
                    }
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Rate Chart from Excel (.xlsx) or CSV file.
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:collection,milk_sale,chilling_center',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'format' => 'required|in:fat_clr,fat_snf,fat_only',
            'file' => 'required|file|max:15360',
            'is_default' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])) {
            return back()->with('error', 'Only CSV and Excel (.xlsx) files are supported.');
        }

        $rows = $this->parseRowsFromFile($file);
        if (empty($rows)) {
            return back()->with('error', 'The uploaded file is empty or could not be read.');
        }

        // Filter out completely empty rows
        $rows = array_values(array_filter($rows, function ($r) {
            return is_array($r) && count(array_filter($r, fn($v) => $v !== null && $v !== '')) > 0;
        }));

        if (count($rows) < 2) {
            return back()->with('error', 'The file must contain a header row and at least one data row.');
        }

        $header = $rows[0];
        $isDefault = $request->boolean('is_default');

        // Check if format is Matrix or Columnar
        $numNumericCols = 0;
        for ($i = 1; $i < count($header); $i++) {
            if (is_numeric(trim((string)$header[$i]))) {
                $numNumericCols++;
            }
        }
        $isMatrix = ($numNumericCols >= 2);

        // Create the RateChart record
        $chart = RateChart::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'milk_type' => $validated['milk_type'],
            'format' => $validated['format'],
            'type' => 'matrix_slab',
            'calculation_type' => 'matrix',
            'status' => 'active',
            'is_default' => $isDefault,
            'effective_date' => now()->toDateString(),
        ]);

        if ($isDefault) {
            RateChart::where('milk_type', $validated['milk_type'])
                ->where('category', $validated['category'])
                ->where('id', '!=', $chart->id)
                ->update(['is_default' => false]);
        }

        $slabs = [];
        $now = now();

        if ($isMatrix) {
            // Matrix format: columns 1..N are secondary (CLR / SNF), column 0 is FAT
            $secValues = [];
            for ($c = 1; $c < count($header); $c++) {
                $val = trim((string)$header[$c]);
                if (is_numeric($val)) {
                    $secValues[$c] = (float) $val;
                }
            }

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                $fatVal = trim((string)($row[0] ?? ''));
                if (!is_numeric($fatVal)) continue;
                $fat = (float) $fatVal;

                foreach ($secValues as $colIdx => $secVal) {
                    $rateVal = trim((string)($row[$colIdx] ?? ''));
                    if ($rateVal === '' || !is_numeric($rateVal)) continue;
                    $rate = (float) $rateVal;

                    $slabs[] = [
                        'rate_chart_id' => $chart->id,
                        'fat_from' => $fat,
                        'fat_to' => $fat,
                        'snf_from' => $secVal,
                        'snf_to' => $secVal,
                        'rate' => $rate,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        } else {
            // Columnar format: FAT, CLR/SNF, Rate
            $fatIdx = -1;
            $secIdx = -1;
            $rateIdx = -1;

            foreach ($header as $i => $h) {
                $low = strtolower(trim((string)$h));
                if (str_contains($low, 'fat') && $fatIdx === -1) {
                    $fatIdx = $i;
                } elseif ((str_contains($low, 'clr') || str_contains($low, 'snf') || str_contains($low, 'secondary')) && $secIdx === -1) {
                    $secIdx = $i;
                } elseif ((str_contains($low, 'rate') || str_contains($low, 'price') || str_contains($low, 'amount')) && $rateIdx === -1) {
                    $rateIdx = $i;
                }
            }

            if ($fatIdx === -1) $fatIdx = 0;
            if ($secIdx === -1) $secIdx = 1;
            if ($rateIdx === -1) $rateIdx = 2;

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                if (!isset($row[$rateIdx]) || !is_numeric(trim((string)$row[$rateIdx]))) continue;

                $fat = isset($row[$fatIdx]) && is_numeric(trim((string)$row[$fatIdx])) ? (float) trim((string)$row[$fatIdx]) : 0.0;
                $sec = isset($row[$secIdx]) && is_numeric(trim((string)$row[$secIdx])) ? (float) trim((string)$row[$secIdx]) : 0.0;
                $rate = (float) trim((string)$row[$rateIdx]);

                $slabs[] = [
                    'rate_chart_id' => $chart->id,
                    'fat_from' => $fat,
                    'fat_to' => $fat,
                    'snf_from' => $sec,
                    'snf_to' => $sec,
                    'rate' => $rate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (empty($slabs)) {
            $chart->delete();
            return back()->with('error', 'No valid rate slab data could be extracted from the file. Please check the sample template format.');
        }

        // Insert slabs in chunks of 500
        foreach (array_chunk($slabs, 500) as $chunk) {
            RateChartSlab::insert($chunk);
        }

        AuditLog::log("Imported Rate Chart {$chart->name} with " . count($slabs) . " rates from Excel/CSV", 'RateChart', $chart->id);

        return redirect()->route('rates.index')->with('success', "Rate Chart '{$chart->name}' imported successfully with " . count($slabs) . " slab rates!");
    }

    /**
     * Helper to read rows from CSV or XLSX files
     */
    private function parseRowsFromFile(\Illuminate\Http\UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'xlsx') {
            return $this->parseXlsxFile($file->getRealPath());
        }

        // CSV parsing
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($data = fgetcsv($handle, 3000, ',')) !== false) {
                if (count($data) === 1 && str_contains($data[0], ';')) {
                    $data = str_getcsv($data[0], ';');
                } elseif (count($data) === 1 && str_contains($data[0], "\t")) {
                    $data = str_getcsv($data[0], "\t");
                }
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Pure PHP XLSX Parser using ZipArchive and SimpleXML
     */
    private function parseXlsxFile(string $filePath): array
    {
        $rows = [];
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            // 1. Read shared strings
            $sharedStrings = [];
            $ssXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($ssXml) {
                $xml = simplexml_load_string($ssXml);
                if ($xml && isset($xml->si)) {
                    foreach ($xml->si as $si) {
                        $sharedStrings[] = (string) ($si->t ?? ($si->r ? $si->r->t : ''));
                    }
                }
            }

            // 2. Read first sheet
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml) {
                $xml = simplexml_load_string($sheetXml);
                if ($xml && isset($xml->sheetData->row)) {
                    foreach ($xml->sheetData->row as $r) {
                        $row = [];
                        foreach ($r->c as $c) {
                            $type = (string) $c['t'];
                            $val = (string) $c->v;
                            if ($type === 's' && isset($sharedStrings[(int) $val])) {
                                $val = $sharedStrings[(int) $val];
                            }
                            $row[] = trim($val);
                        }
                        $rows[] = $row;
                    }
                }
            }
            $zip->close();
        }
        return $rows;
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
        if ($rate->status !== 'active') {
            return back()->with('error', "Cannot assign inactive rate chart '{$rate->name}'. Please activate it first.");
        }

        $validated = $request->validate([
            'farmer_ids' => 'nullable|array',
            'farmer_ids.*' => 'exists:farmers,id',
            'center_ids' => 'nullable|array',
            'center_ids.*' => 'exists:collection_centers,id',
            'assign_all' => 'nullable',
            'shift' => 'nullable|string',
            'activate_mode' => 'nullable|string',
            'activate_date' => 'nullable|date',
            'activate_shift' => 'nullable|string',
        ]);

        $assignAll = filter_var($request->input('assign_all'), FILTER_VALIDATE_BOOLEAN) || $request->input('assign_all') === '1' || $request->input('assign_all') === 'true';
        $farmerIds = $validated['farmer_ids'] ?? [];
        $centerIds = $validated['center_ids'] ?? [];

        if ($assignAll) {
            $farmerIds = Farmer::where('status', 'active')->pluck('id')->toArray();
        }

        if (empty($farmerIds) && empty($centerIds)) {
            return back()->with('error', 'Please select at least one farmer or collection center to assign.');
        }

        DB::transaction(function () use ($rate, $farmerIds, $centerIds, $request) {
            // Update effective_date and shift on rate_chart if provided
            $updates = [];
            if ($request->filled('activate_mode')) {
                if ($request->activate_mode === 'later' && $request->filled('activate_date')) {
                    $updates['effective_date'] = $request->activate_date;
                } elseif ($request->activate_mode === 'now') {
                    $updates['effective_date'] = now()->toDateString();
                }
            }

            if ($request->filled('shift') && \Illuminate\Support\Facades\Schema::hasColumn('rate_charts', 'shift')) {
                $updates['shift'] = $request->shift;
            }

            if (!empty($updates)) {
                $rate->update($updates);
            }

            // Assign selected farmers
            if (!empty($farmerIds)) {
                Farmer::whereIn('id', $farmerIds)->update([
                    'rate_chart_id' => $rate->id,
                    'pricing_type' => 'rate_chart',
                ]);
            }

            // Assign selected centers
            if (!empty($centerIds)) {
                CollectionCenter::whereIn('id', $centerIds)->update(['rate_chart_id' => $rate->id]);
            }
        });

        $shiftLabel = $request->shift ?? 'Both';
        AuditLog::log("Assigned Rate Chart {$rate->name} (Shift: {$shiftLabel}, Mode: {$request->activate_mode})", 'RateChart', $rate->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Rate chart '{$rate->name}' assigned to " . count($centerIds) . " center(s) and " . count($farmerIds) . " farmer(s).",
                'assigned_farmers_count' => $rate->farmers()->count(),
            ]);
        }

        return redirect()->route('rates.index')->with('success', "Rate chart '{$rate->name}' assigned successfully to " . count($farmerIds) . " farmer(s)" . (count($centerIds) > 0 ? " and " . count($centerIds) . " center(s)." : "."));
    }

    /**
     * Get list of all farmers assigned to a particular Rate Chart
     */
    public function getAssignedFarmers(RateChart $rate)
    {
        $farmers = $rate->farmers()
            ->orderBy('farmer_code')
            ->orderBy('name')
            ->get([
                'id',
                'farmer_code',
                'name',
                'phone',
                'village',
                'animal_type',
                'pricing_type',
                'cow_milk_rate',
                'buffalo_milk_rate',
                'status',
            ]);

        return response()->json([
            'success' => true,
            'rate_chart' => [
                'id' => $rate->id,
                'name' => $rate->name,
                'milk_type' => $rate->milk_type,
                'status' => $rate->status,
                'fixed_rate' => (float) $rate->fixed_rate,
            ],
            'count' => $farmers->count(),
            'farmers' => $farmers,
        ]);
    }

    /**
     * Unassign a farmer from a Rate Chart
     */
    public function unassignFarmer(Request $request, RateChart $rate, Farmer $farmer)
    {
        if ($farmer->rate_chart_id === $rate->id) {
            $farmer->update(['rate_chart_id' => null]);
            AuditLog::log("Unassigned Farmer {$farmer->name} from Rate Chart {$rate->name}", 'RateChart', $rate->id);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Farmer '{$farmer->name}' has been unassigned from rate chart '{$rate->name}'.",
                'remaining_count' => $rate->farmers()->count(),
            ]);
        }

        return back()->with('success', "Farmer '{$farmer->name}' has been unassigned from rate chart '{$rate->name}'.");
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
