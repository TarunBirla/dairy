<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Branch;
use App\Models\CollectionCenter;
use App\Models\RateChart;
use App\Models\FarmerAdvance;
use App\Models\FarmerLedger;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerController extends Controller
{
    /**
     * Farmers Listing & In-Page Modal Management
     */
    public function index(Request $request)
    {
        $query = Farmer::with(['collectionCenter', 'rateChart', 'route']);

        // Search Filter (English name, Hindi name, code, phone, village, vehicle)
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

        // Animal / Milk Classification Filter
        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }

        // Assigned Route Filter
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Assigned Vehicle Filter
        if ($request->filled('vehicle')) {
            $query->where('vehicle', 'like', "%{$request->vehicle}%");
        }

        // Center Filter
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Balance Filter
        if ($request->filled('balance_status')) {
            if ($request->balance_status === 'due') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->balance_status === 'advance') {
                $query->where('current_balance', '<', 0);
            } elseif ($request->balance_status === 'zero') {
                $query->where('current_balance', '=', 0);
            }
        }

        // Sorting
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
                $query->latest();
                break;
        }

        $farmers = $query->paginate(15)->withQueryString();

        $totalFarmers = Farmer::count();
        $activeFarmers = Farmer::where('status', 'active')->count();
        $totalPayable = Farmer::where('current_balance', '>', 0)->sum('current_balance');

        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();

        $nextNum = Farmer::max('id') + 1;
        $nextCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('farmers.index', compact(
            'farmers',
            'totalFarmers',
            'activeFarmers',
            'totalPayable',
            'branches',
            'centers',
            'rateCharts',
            'routes',
            'nextCode'
        ));
    }

    /**
     * Fallback standalone create page
     */
    public function create()
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();
        $nextNum = Farmer::max('id') + 1;
        $nextCode = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        return view('farmers.create', compact('branches', 'centers', 'rateCharts', 'routes', 'nextCode'));
    }

    /**
     * Store new farmer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code',
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
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
            'confirm_account_number' => 'nullable|same:account_number',
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

        // Photo file handling
        if ($request->hasFile('photo')) {
            $destPath = public_path('uploads/farmers');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'farmer_' . time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move($destPath, $filename);
            $validated['photo'] = 'uploads/farmers/' . $filename;
        }

        unset($validated['confirm_account_number']);

        // Safe defensive column filtering to avoid breaking if table migration is pending
        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer = Farmer::create($saveData);
        AuditLog::log('Created Farmer', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code, 'name' => $farmer->name]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$farmer->name} ({$farmer->farmer_code}) registered successfully!");
    }

    /**
     * View farmer passbook
     */
    public function show(Farmer $farmer)
    {
        $farmer->load(['collectionCenter', 'rateChart', 'route', 'advances', 'settlements']);
        $collections = MilkCollection::where('farmer_id', $farmer->id)->latest()->take(20)->get();
        $ledgers = FarmerLedger::where('farmer_id', $farmer->id)->latest()->take(30)->get();
        $advances = FarmerAdvance::where('farmer_id', $farmer->id)->latest()->get();

        $totalMilkLiters = MilkCollection::where('farmer_id', $farmer->id)->sum('quantity_liters');
        $totalGrossEarned = MilkCollection::where('farmer_id', $farmer->id)->sum('net_amount');

        return view('farmers.show', compact('farmer', 'collections', 'ledgers', 'advances', 'totalMilkLiters', 'totalGrossEarned'));
    }

    /**
     * Fallback standalone edit page
     */
    public function edit(Farmer $farmer)
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();

        return view('farmers.edit', compact('farmer', 'branches', 'centers', 'rateCharts', 'routes'));
    }

    /**
     * Update farmer details
     */
    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code,' . $farmer->id,
            'name' => 'required|string|max:255',
            'name_hi' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
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
            'confirm_account_number' => 'nullable|same:account_number',
            'ifsc_code' => 'nullable|string|max:50',
            'upi_id' => 'nullable|string|max:100',
            'anamat' => 'nullable|numeric|min:0',
            'building_fund' => 'nullable|numeric|min:0',
            'installment' => 'nullable|numeric|min:0',
            'etc_amount' => 'nullable|numeric|min:0',
            'grant_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        if ($request->hasFile('photo')) {
            $destPath = public_path('uploads/farmers');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'farmer_' . time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move($destPath, $filename);
            $validated['photo'] = 'uploads/farmers/' . $filename;
        }

        unset($validated['confirm_account_number']);

        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
            $saveData = array_intersect_key($validated, array_flip($tableColumns));
        } catch (\Throwable $e) {
            $saveData = $validated;
        }

        $farmer->update($saveData);
        AuditLog::log('Updated Farmer', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$farmer->name} updated successfully.");
    }

    /**
     * Delete farmer record
     */
    public function destroy(Farmer $farmer)
    {
        $name = $farmer->name;
        $code = $farmer->farmer_code;

        $farmer->delete();
        AuditLog::log('Deleted Farmer', 'Farmer', $farmer->id, ['code' => $code, 'name' => $name]);

        return redirect()->route('farmers.index')->with('success', "Farmer {$name} ({$code}) deleted successfully.");
    }

    /**
     * Bulk Import Farmers from CSV/Excel
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = null;
                while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                    if (!array_filter($data)) continue;
                    if (!$header) {
                        $header = array_map(function ($h) {
                            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
                        }, $data);
                    } else {
                        $row = [];
                        foreach ($header as $i => $col) {
                            $row[$col] = isset($data[$i]) ? trim($data[$i]) : null;
                        }
                        $rows[] = $row;
                    }
                }
                fclose($handle);
            }
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                $header = null;
                foreach ($sheetData as $rowValues) {
                    $values = array_values($rowValues);
                    if (!array_filter($values)) continue;
                    if (!$header) {
                        $header = array_map(function ($h) {
                            return strtolower(trim($h));
                        }, $values);
                    } else {
                        $row = [];
                        foreach ($header as $i => $col) {
                            $row[$col] = isset($values[$i]) ? trim($values[$i]) : null;
                        }
                        $rows[] = $row;
                    }
                }
            } else {
                return back()->with('error', 'Excel format requires PhpSpreadsheet. Please upload a .CSV format file instead.');
            }
        } else {
            return back()->with('error', 'Invalid file type. Please upload a .csv, .xlsx, or .xls file.');
        }

        if (empty($rows)) {
            return back()->with('error', 'No valid farmer rows found in the uploaded file.');
        }

        $createdCount = 0;
        $updatedCount = 0;

        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('farmers');
        } catch (\Throwable $e) {
            $tableColumns = [];
        }

        foreach ($rows as $r) {
            $name = $r['name'] ?? $r['farmer_name'] ?? $r['farmer name'] ?? null;
            if (!$name) continue;

            $code = $r['farmer_code'] ?? $r['code'] ?? null;
            if (!$code) {
                $nextNum = Farmer::max('id') + 1;
                $code = 'FMR' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            $farmerData = [
                'name' => $name,
                'name_hi' => $r['name_hi'] ?? $r['kisan_name'] ?? $r['hindi_name'] ?? null,
                'phone' => $r['phone'] ?? $r['phone_number'] ?? $r['mobile'] ?? null,
                'village' => $r['village'] ?? null,
                'address' => $r['address'] ?? null,
                'vehicle' => $r['vehicle'] ?? null,
                'animal_type' => in_array(strtolower($r['animal_type'] ?? ''), ['cow', 'buffalo', 'mixed']) ? strtolower($r['animal_type']) : 'cow',
                'cow_milk_rate' => is_numeric($r['cow_milk_rate'] ?? null) ? $r['cow_milk_rate'] : null,
                'buffalo_milk_rate' => is_numeric($r['buffalo_milk_rate'] ?? null) ? $r['buffalo_milk_rate'] : null,
                'branch_name' => $r['branch_name'] ?? $r['branch'] ?? null,
                'bank_name' => $r['bank_name'] ?? $r['bank'] ?? null,
                'account_number' => $r['account_number'] ?? $r['account'] ?? null,
                'ifsc_code' => $r['ifsc_code'] ?? $r['ifsc'] ?? null,
                'upi_id' => $r['upi_id'] ?? $r['upi'] ?? null,
                'anamat' => is_numeric($r['anamat'] ?? null) ? $r['anamat'] : 0,
                'building_fund' => is_numeric($r['building_fund'] ?? null) ? $r['building_fund'] : 0,
                'installment' => is_numeric($r['installment'] ?? null) ? $r['installment'] : 0,
                'etc_amount' => is_numeric($r['etc_amount'] ?? null) ? $r['etc_amount'] : 0,
                'grant_amount' => is_numeric($r['grant_amount'] ?? null) ? $r['grant_amount'] : 0,
                'status' => in_array(strtolower($r['status'] ?? ''), ['active', 'inactive', 'blocked']) ? strtolower($r['status']) : 'active',
            ];

            // Match route by name if provided
            if (!empty($r['route']) || !empty($r['assigned_route'])) {
                $routeName = $r['route'] ?? $r['assigned_route'];
                $routeObj = \App\Models\DeliveryRoute::where('name', 'like', "%{$routeName}%")->first();
                if ($routeObj) {
                    $farmerData['route_id'] = $routeObj->id;
                }
            }

            if (!empty($tableColumns)) {
                $filteredData = array_intersect_key($farmerData, array_flip($tableColumns));
            } else {
                $filteredData = $farmerData;
            }

            $existing = Farmer::where('farmer_code', $code)->first();
            if ($existing) {
                $existing->update($filteredData);
                $updatedCount++;
            } else {
                $filteredData['farmer_code'] = $code;
                Farmer::create($filteredData);
                $createdCount++;
            }
        }

        return redirect()->route('farmers.index')->with('success', "Bulk Import Complete: {$createdCount} farmers created, {$updatedCount} updated successfully.");
    }

    /**
     * Download Sample CSV Import Template
     */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="farmer_import_template.csv"',
        ];

        $columns = [
            'farmer_code', 'name', 'name_hi', 'phone', 'village', 'vehicle',
            'animal_type', 'assigned_route', 'bank_name', 'branch_name', 'account_number',
            'ifsc_code', 'anamat', 'building_fund', 'installment', 'etc_amount', 'grant_amount', 'status'
        ];

        $sample1 = [
            'FMR0001', 'Kunal Sharma', 'कुणाल शर्मा', '8596741236', 'Rampur', 'Van',
            'cow', 'Lowadih - Lalpur', 'State Bank of India', 'Main Branch', '30998877665',
            'SBIN0001234', '500.00', '100.00', '250.00', '0.00', '150.00', 'active'
        ];

        $sample2 = [
            'FMR0002', 'Arvind Garg', 'अरविंद गर्ग', '9876543210', 'Palasia', 'Car',
            'buffalo', 'Bombay - Goa', 'HDFC Bank', 'City Branch', '50100223344',
            'HDFC0000123', '1000.00', '200.00', '500.00', '50.00', '0.00', 'active'
        ];

        $callback = function () use ($columns, $sample1, $sample2) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($output, $columns);
            fputcsv($output, $sample1);
            fputcsv($output, $sample2);
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Issue cash advance
     */
    public function storeAdvance(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'purpose' => 'nullable|string',
            'advance_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $advance = FarmerAdvance::create([
            'farmer_id' => $farmer->id,
            'amount' => $validated['amount'],
            'advance_date' => $validated['advance_date'],
            'purpose' => $validated['purpose'] ?? 'General advance',
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Adjust farmer ledger
        $newBalance = $farmer->current_balance - $validated['amount'];
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['advance_date'],
            'type' => 'debit',
            'amount' => $validated['amount'],
            'balance' => $newBalance,
            'reference_type' => 'advance',
            'reference_id' => $advance->id,
            'description' => "Advance Issued: " . ($validated['purpose'] ?? 'Cash Advance'),
        ]);

        AuditLog::log('Issued Advance to Farmer', 'FarmerAdvance', $advance->id, ['amount' => $validated['amount']]);

        return back()->with('success', 'Advance of ₹' . number_format($validated['amount'], 2) . ' issued successfully.');
    }
}
