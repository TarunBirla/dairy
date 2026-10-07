<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Farmer;
use App\Models\FarmerDeduction;
use App\Models\FarmerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerDeductionController extends Controller
{
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Resiliently ensure farmer_deductions table exists on the database
     */
    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('farmer_deductions')) {
                Schema::create('farmer_deductions', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                    $table->date('entry_date');
                    $table->string('deduction_type')->default('cattle_feed');
                    $table->string('transaction_type')->default('given'); // given, received
                    $table->decimal('amount', 10, 2);
                    $table->text('comments')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Display Deduction List matching Screenshot 1 (media_1791388469555.png)
     */
    public function index(Request $request)
    {
        $deductionType = $request->get('deduction_type', 'all');
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 15);

        // Fetch farmers with their deductions
        $farmersQuery = Farmer::where('status', 'active');

        if (!empty($search)) {
            $farmersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($deductionType && $deductionType !== 'all') {
            $farmersQuery->whereHas('deductions', function ($q) use ($deductionType) {
                $q->where('deduction_type', $deductionType);
            });
        }

        // Include relations
        $farmersQuery->with(['deductions' => function ($q) use ($deductionType) {
            if ($deductionType && $deductionType !== 'all') {
                $q->where('deduction_type', $deductionType);
            }
            $q->latest('entry_date');
        }]);

        $farmers = $farmersQuery->orderBy('name')->paginate($perPage)->withQueryString();

        // Calculate totals across system
        $totalGiven = 0;
        $totalReceived = 0;
        if (Schema::hasTable('farmer_deductions')) {
            $totalGiven = (float) FarmerDeduction::where('transaction_type', 'given')->sum('amount');
            $totalReceived = (float) FarmerDeduction::where('transaction_type', 'received')->sum('amount');
        }
        $netDeductionBalance = $totalGiven - $totalReceived;

        // All active farmers for dropdown modal
        $farmerColumns = ['id', 'farmer_code', 'name', 'phone'];
        if (Schema::hasColumn('farmers', 'name_hi')) {
            $farmerColumns[] = 'name_hi';
        }
        $allFarmers = Farmer::where('status', 'active')->orderBy('name')->get($farmerColumns);

        $deductionTypesList = [
            'cattle_feed' => 'Cattle Feed (पशु आहार)',
            'medicine' => 'Medicine (दवाई)',
            'ghee_butter' => 'Ghee / Butter',
            'doctor_fee' => 'Doctor Fee',
            'insurance' => 'Insurance',
            'advance_recovery' => 'Advance Recovery',
            'store_purchase' => 'Store Purchase',
            'equipment' => 'Equipment / बर्तन',
            'other' => 'Other (अन्य)',
        ];

        return view('deductions.index', compact(
            'farmers',
            'allFarmers',
            'deductionType',
            'search',
            'perPage',
            'totalGiven',
            'totalReceived',
            'netDeductionBalance',
            'deductionTypesList'
        ));
    }

    /**
     * Store a new deduction entry (matching Screenshot 2: media_1791388483234.png)
     */
    public function store(Request $request)
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

        // Adjust farmer ledger balance:
        // 'given' is a deduction (debit on farmer), 'received' is repayment (credit)
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

        AuditLog::log('Recorded Farmer Deduction', 'FarmerDeduction', $deduction->id, [
            'farmer' => $farmer->name,
            'type' => $deduction->deduction_type,
            'amount' => $deduction->amount,
            'mode' => $deduction->transaction_type,
        ]);

        return redirect()->route('deductions.index')->with('success', "Deduction of ₹ " . number_format($deduction->amount, 2) . " for {$farmer->name} recorded successfully!");
    }

    /**
     * Update an existing deduction
     */
    public function update(Request $request, FarmerDeduction $deduction)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'deduction_type' => 'required|string|max:100',
            'transaction_type' => 'required|in:given,received',
            'amount' => 'required|numeric|min:0.01',
            'comments' => 'nullable|string|max:1000',
        ]);

        $farmer = $deduction->farmer;

        // Revert old effect
        $oldAmount = (float) $deduction->amount;
        $revertedBalance = $deduction->transaction_type === 'given' 
            ? ($farmer->current_balance + $oldAmount) 
            : ($farmer->current_balance - $oldAmount);

        // Apply new effect
        $newAmount = (float) $validated['amount'];
        $finalBalance = $validated['transaction_type'] === 'given' 
            ? ($revertedBalance - $newAmount) 
            : ($revertedBalance + $newAmount);

        $farmer->update(['current_balance' => $finalBalance]);

        $deduction->update($validated);

        AuditLog::log('Updated Farmer Deduction', 'FarmerDeduction', $deduction->id);

        return redirect()->back()->with('success', "Deduction record updated successfully!");
    }

    /**
     * Delete a deduction entry
     */
    public function destroy(FarmerDeduction $deduction)
    {
        $farmer = $deduction->farmer;
        if ($farmer) {
            $amount = (float) $deduction->amount;
            $restoredBalance = $deduction->transaction_type === 'given' 
                ? ($farmer->current_balance + $amount) 
                : ($farmer->current_balance - $amount);
            $farmer->update(['current_balance' => $restoredBalance]);
        }

        $deduction->delete();

        AuditLog::log('Deleted Farmer Deduction', 'FarmerDeduction', $deduction->id);

        return redirect()->back()->with('success', "Deduction record deleted successfully.");
    }

    /**
     * Get farmer deduction history (for View modal)
     */
    public function farmerDeductions(Farmer $farmer)
    {
        $deductions = FarmerDeduction::where('farmer_id', $farmer->id)
            ->latest('entry_date')
            ->get();

        $totalGiven = (float) $deductions->where('transaction_type', 'given')->sum('amount');
        $totalReceived = (float) $deductions->where('transaction_type', 'received')->sum('amount');
        $netTotal = $totalGiven - $totalReceived;

        return response()->json([
            'farmer' => [
                'id' => $farmer->id,
                'name' => $farmer->name,
                'name_hi' => $farmer->name_hi ?? '',
                'code' => $farmer->farmer_code,
                'phone' => $farmer->phone ?? '',
            ],
            'total_given' => $totalGiven,
            'total_received' => $totalReceived,
            'net_total' => $netTotal,
            'deductions' => $deductions,
        ]);
    }

    /**
     * Import Deductions via CSV (matching Screenshot 3: media_1791388496598.png)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $count = 0;

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

                    $farmerCode = $row['farmer_code'] ?? $row['code'] ?? null;
                    $farmer = Farmer::where('farmer_code', $farmerCode)->first();
                    if (!$farmer && !empty($row['farmer_name'])) {
                        $farmer = Farmer::where('name', 'like', "%{$row['farmer_name']}%")->first();
                    }

                    if ($farmer && !empty($row['amount']) && is_numeric($row['amount'])) {
                        $date = !empty($row['date']) ? Carbon::parse($row['date'])->format('Y-m-d') : now()->format('Y-m-d');
                        $type = $row['deduction_type'] ?? $row['type'] ?? 'cattle_feed';
                        $transType = strtolower($row['transaction_type'] ?? $row['trans_type'] ?? 'given');
                        if (!in_array($transType, ['given', 'received'])) {
                            $transType = 'given';
                        }
                        $amount = (float) $row['amount'];
                        $comments = $row['comments'] ?? $row['remark'] ?? 'Bulk Imported Deduction';

                        $deduction = FarmerDeduction::create([
                            'farmer_id' => $farmer->id,
                            'entry_date' => $date,
                            'deduction_type' => $type,
                            'transaction_type' => $transType,
                            'amount' => $amount,
                            'comments' => $comments,
                            'created_by' => Auth::id(),
                        ]);

                        // Adjust farmer balance
                        $newBal = $transType === 'given' ? ($farmer->current_balance - $amount) : ($farmer->current_balance + $amount);
                        $farmer->update(['current_balance' => $newBal]);

                        FarmerLedger::create([
                            'farmer_id' => $farmer->id,
                            'transaction_date' => $date,
                            'type' => $transType === 'given' ? 'debit' : 'credit',
                            'amount' => $amount,
                            'balance' => $newBal,
                            'reference_type' => 'deduction',
                            'reference_id' => $deduction->id,
                            'description' => "Imported Deduction ({$type} - {$transType}): {$comments}",
                        ]);

                        $count++;
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->route('deductions.index')->with('success', "Import Complete: Successfully imported {$count} deduction records!");
    }

    /**
     * Download sample CSV template for deduction import
     */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="farmer_deduction_sample.csv"',
        ];

        $columns = ['farmer_code', 'farmer_name', 'date', 'deduction_type', 'transaction_type', 'amount', 'comments'];
        $sample1 = ['FMR002', 'Arvind gorg', '2026-10-07', 'cattle_feed', 'given', '3080.00', 'Cattle Feed 2 Bags'];
        $sample2 = ['002', 'asim kumar', '2026-10-06', 'medicine', 'given', '6000.00', 'Vaccination and medical treatment'];
        $sample3 = ['FMR0003', 'asim kumar', '2026-10-05', 'cattle_feed', 'received', '1500.00', 'Cash payment for cattle feed'];

        $callback = function () use ($columns, $sample1, $sample2, $sample3) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);
            fputcsv($output, $sample1);
            fputcsv($output, $sample2);
            fputcsv($output, $sample3);
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
