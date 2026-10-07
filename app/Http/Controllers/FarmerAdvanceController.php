<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Farmer;
use App\Models\FarmerAdvance;
use App\Models\FarmerAdvanceRepayment;
use App\Models\FarmerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class FarmerAdvanceController extends Controller
{
    public function __construct()
    {
        $this->ensureSchemaAndDefaults();
    }

    /**
     * Ensure database columns and sample advances exist.
     */
    private function ensureSchemaAndDefaults(): void
    {
        try {
            if (Schema::hasTable('farmer_advances')) {
                Schema::table('farmer_advances', function (Blueprint $table) {
                    if (!Schema::hasColumn('farmer_advances', 'voucher_no')) {
                        $table->string('voucher_no')->nullable()->after('advance_date');
                    }
                    if (!Schema::hasColumn('farmer_advances', 'interest_rate')) {
                        $table->decimal('interest_rate', 5, 2)->default(0.00)->after('amount');
                    }
                    if (!Schema::hasColumn('farmer_advances', 'paid_amount')) {
                        $table->decimal('paid_amount', 10, 2)->default(0.00)->after('deducted_amount');
                    }
                    if (!Schema::hasColumn('farmer_advances', 'paid_date')) {
                        $table->date('paid_date')->nullable()->after('paid_amount');
                    }
                    if (!Schema::hasColumn('farmer_advances', 'interest_balance')) {
                        $table->decimal('interest_balance', 10, 2)->default(0.00)->after('paid_date');
                    }
                    if (!Schema::hasColumn('farmer_advances', 'payment_mode')) {
                        $table->string('payment_mode')->default('Cash')->after('interest_balance');
                    }
                });

                if (Schema::hasColumn('farmer_advances', 'deleted_at')) {
                    \DB::table('farmer_advances')->whereNotNull('deleted_at')->update(['deleted_at' => null]);
                }
            }

            if (!Schema::hasTable('farmer_advance_repayments')) {
                Schema::create('farmer_advance_repayments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('farmer_advance_id')->constrained('farmer_advances')->cascadeOnDelete();
                    $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                    $table->date('repayment_date');
                    $table->decimal('amount', 10, 2);
                    $table->string('payment_mode')->default('Cash');
                    $table->text('remark')->nullable();
                    $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Display Farmer Advance List (matching reference image media_1791384816027.png & media_1791384906809.png).
     */
    public function index(Request $request)
    {
        $farmersQuery = Farmer::with(['advances' => function ($q) {
            $q->latest('advance_date');
        }]);

        // Filter by specific farmer
        if ($request->filled('farmer_id')) {
            $farmersQuery->where('id', $request->farmer_id);
        }

        // Search farmer, phone, voucher
        if ($request->filled('search')) {
            $search = trim($request->search);
            $farmersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('advances', function ($advQ) use ($search) {
                      $advQ->where('voucher_no', 'like', "%{$search}%")
                           ->orWhere('notes', 'like', "%{$search}%");
                  });
            });
        }

        $allFarmers = Farmer::orderBy('name')->get();
        $farmers = $farmersQuery->orderBy('name')->paginate(15)->withQueryString();

        // Calculate summary cards across all advances
        $totalAdvance = (float) FarmerAdvance::sum('amount');
        $totalPaid = (float) (FarmerAdvance::sum('paid_amount') ?: FarmerAdvance::sum('deducted_amount'));
        $totalBalance = max(0, $totalAdvance - $totalPaid);

        // Next sequential voucher number
        $nextVoucherNum = (FarmerAdvance::max('id') ?: 0) + 1;
        $nextVoucher = str_pad($nextVoucherNum, 3, '0', STR_PAD_LEFT);

        // Active advances with outstanding balance for "Receive Payment" modal
        $activeAdvances = FarmerAdvance::with('farmer')
            ->where('status', '!=', 'recovered')
            ->orderBy('advance_date', 'desc')
            ->get()
            ->filter(function ($adv) {
                return $adv->total_balance > 0;
            });

        return view('advances.index', compact(
            'farmers',
            'allFarmers',
            'totalAdvance',
            'totalPaid',
            'totalBalance',
            'nextVoucher',
            'activeAdvances'
        ));
    }

    /**
     * Display individual farmer Advance Ledger (matching reference image media_1791384832203.png).
     */
    public function ledger(Request $request, Farmer $farmer)
    {
        $query = FarmerAdvance::with('repayments')
            ->where('farmer_id', $farmer->id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('payment_mode', 'like', "%{$search}%");
            });
        }

        $advances = $query->orderBy('advance_date', 'desc')->get();

        $totalAdvance = (float) $advances->sum('amount');
        $totalPaid = (float) $advances->sum(function ($a) {
            return $a->total_paid;
        });
        $totalPrincipal = (float) $advances->sum(function ($a) {
            return $a->principal_balance;
        });
        $totalInterest = (float) $advances->sum('interest_balance');
        $totalBalance = (float) $advances->sum(function ($a) {
            return $a->total_balance;
        });

        return view('advances.ledger', compact(
            'farmer',
            'advances',
            'totalAdvance',
            'totalPaid',
            'totalPrincipal',
            'totalInterest',
            'totalBalance'
        ));
    }

    /**
     * Store new farmer advance (matching Add New Advance modal in media_1791384816027.png).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'voucher_no' => 'nullable|string|max:50',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'payment_mode' => 'nullable|string|in:Cash,UPI,Bank Transfer,Cheque',
            'remark' => 'nullable|string|max:500',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        $voucher = $validated['voucher_no'] ?: str_pad((FarmerAdvance::max('id') + 1), 3, '0', STR_PAD_LEFT);

        $advance = FarmerAdvance::create([
            'farmer_id' => $farmer->id,
            'advance_date' => $validated['advance_date'],
            'amount' => $validated['amount'],
            'voucher_no' => $voucher,
            'interest_rate' => $validated['interest_rate'] ?? 0.00,
            'payment_mode' => $validated['payment_mode'] ?? 'Cash',
            'purpose' => $validated['remark'] ?? 'Advance Loan Issue',
            'notes' => $validated['remark'] ?? '-PAID BY DR',
            'status' => 'pending',
            'deducted_amount' => 0.00,
            'paid_amount' => 0.00,
            'interest_balance' => 0.00,
        ]);

        // Adjust farmer current balance & create ledger debit record
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
            'description' => "Advance Issued (Voucher #{$voucher}): " . ($validated['remark'] ?? 'Cash Advance'),
        ]);

        AuditLog::log('Issued Farmer Advance', 'FarmerAdvance', $advance->id, [
            'farmer' => $farmer->name,
            'amount' => $advance->amount,
            'voucher' => $voucher,
        ]);

        return redirect()->route('advances.index')->with('success', "Advance of ₹ " . number_format($advance->amount, 2) . " (Voucher #{$voucher}) issued to {$farmer->name} successfully!");
    }

    /**
     * Update an existing advance.
     */
    public function update(Request $request, FarmerAdvance $advance)
    {
        $validated = $request->validate([
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'voucher_no' => 'nullable|string|max:50',
            'interest_rate' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $oldAmount = $advance->amount;
        $diff = $validated['amount'] - $oldAmount;

        $advance->update([
            'advance_date' => $validated['advance_date'],
            'amount' => $validated['amount'],
            'voucher_no' => $validated['voucher_no'] ?? $advance->voucher_no,
            'interest_rate' => $validated['interest_rate'] ?? 0.00,
            'payment_mode' => $validated['payment_mode'] ?? $advance->payment_mode,
            'notes' => $validated['remark'] ?? $advance->notes,
        ]);

        // Update farmer balance if amount changed
        if ($diff != 0) {
            $farmer = $advance->farmer;
            $farmer->update(['current_balance' => $farmer->current_balance - $diff]);
        }

        AuditLog::log('Updated Farmer Advance', 'FarmerAdvance', $advance->id);

        return redirect()->back()->with('success', "Advance #{$advance->voucher_no} updated successfully!");
    }

    /**
     * Delete an advance record.
     */
    public function destroy(FarmerAdvance $advance)
    {
        $farmer = $advance->farmer;
        $unpaid = $advance->principal_balance;

        // Restore balance for remaining advance
        if ($unpaid > 0 && $farmer) {
            $farmer->update(['current_balance' => $farmer->current_balance + $unpaid]);
        }

        $voucher = $advance->voucher_no;
        $advance->delete();

        AuditLog::log('Deleted Farmer Advance', 'FarmerAdvance', $advance->id, ['voucher' => $voucher]);

        return redirect()->back()->with('success', "Advance record #{$voucher} deleted successfully.");
    }

    /**
     * Receive Advance Payment (matching Receive Advance Payment modal in media_1791384875810.png).
     */
    public function receive(Request $request)
    {
        $validated = $request->validate([
            'advance_id' => 'required|exists:farmer_advances,id',
            'receive_amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|in:Cash,UPI,Bank Transfer,Cheque',
            'remark' => 'nullable|string|max:500',
            'repayment_date' => 'nullable|date',
        ]);

        $advance = FarmerAdvance::findOrFail($validated['advance_id']);
        $farmer = $advance->farmer;
        $repayDate = $validated['repayment_date'] ?: now()->format('Y-m-d');
        $received = $validated['receive_amount'];

        // Record Repayment Entry
        $repayment = FarmerAdvanceRepayment::create([
            'farmer_advance_id' => $advance->id,
            'farmer_id' => $farmer->id,
            'repayment_date' => $repayDate,
            'amount' => $received,
            'payment_mode' => $validated['payment_mode'],
            'remark' => $validated['remark'] ?? 'Advance recovered from farmer',
            'recorded_by' => Auth::id(),
        ]);

        // Update advance paid amounts
        $newPaid = (float) $advance->paid_amount + $received;
        $status = $newPaid >= $advance->amount ? 'recovered' : 'partially_deducted';

        $advance->update([
            'paid_amount' => $newPaid,
            'deducted_amount' => $newPaid,
            'paid_date' => $repayDate,
            'status' => $status,
        ]);

        // Update farmer balance (repayment increases credit balance)
        $newBalance = $farmer->current_balance + $received;
        $farmer->update(['current_balance' => $newBalance]);

        // Record in Farmer Ledger
        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $repayDate,
            'type' => 'credit',
            'amount' => $received,
            'balance' => $newBalance,
            'reference_type' => 'advance_repayment',
            'reference_id' => $repayment->id,
            'description' => "Advance Received Back (Voucher #{$advance->voucher_no}): " . ($validated['remark'] ?? 'Payment Received'),
        ]);

        AuditLog::log('Received Advance Repayment', 'FarmerAdvanceRepayment', $repayment->id, [
            'farmer' => $farmer->name,
            'amount' => $received,
            'voucher' => $advance->voucher_no,
        ]);

        return redirect()->back()->with('success', "Payment of ₹ " . number_format($received, 2) . " received successfully for Voucher #{$advance->voucher_no} ({$farmer->name})!");
    }

    /**
     * Import advances in bulk from CSV (matching Import Advance modal in media_1791384860249.png).
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
                        $voucher = $row['voucher'] ?? $row['voucher_no'] ?? str_pad((FarmerAdvance::max('id') + 1), 3, '0', STR_PAD_LEFT);
                        $date = !empty($row['date']) ? Carbon::parse($row['date'])->format('Y-m-d') : now()->format('Y-m-d');
                        $amount = (float) $row['amount'];

                        $adv = FarmerAdvance::create([
                            'farmer_id' => $farmer->id,
                            'advance_date' => $date,
                            'amount' => $amount,
                            'voucher_no' => $voucher,
                            'interest_rate' => is_numeric($row['interest_rate'] ?? null) ? $row['interest_rate'] : 0.00,
                            'payment_mode' => $row['payment_mode'] ?? 'Cash',
                            'purpose' => $row['remark'] ?? 'Imported Advance',
                            'notes' => $row['remark'] ?? '-PAID BY DR',
                            'status' => 'pending',
                        ]);

                        $farmer->update(['current_balance' => $farmer->current_balance - $amount]);
                        $count++;
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->route('advances.index')->with('success', "Import Complete: Successfully imported {$count} advances!");
    }

    /**
     * Download sample CSV template for advance import.
     */
    public function sampleTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="farmer_advance_import_sample.csv"',
        ];

        $columns = ['farmer_code', 'farmer_name', 'date', 'voucher', 'amount', 'interest_rate', 'payment_mode', 'remark'];
        $sample1 = ['002', 'asim kumar', '2026-10-08', '001', '500.00', '0.00', 'Cash', '-PAID BY DR'];
        $sample2 = ['FMR002', 'Arvind gorg', '2026-10-07', '002', '5000.00', '0.00', 'UPI', 'Emergency Advance'];

        $callback = function () use ($columns, $sample1, $sample2) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns);
            fputcsv($output, $sample1);
            fputcsv($output, $sample2);
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print entire advance listing.
     */
    public function printList(Request $request)
    {
        $farmers = Farmer::with('advances')->get();
        $totalAdvance = (float) FarmerAdvance::sum('amount');
        $totalPaid = (float) (FarmerAdvance::sum('paid_amount') ?: FarmerAdvance::sum('deducted_amount'));
        $totalBalance = max(0, $totalAdvance - $totalPaid);

        return view('advances.print_list', compact('farmers', 'totalAdvance', 'totalPaid', 'totalBalance'));
    }

    /**
     * Print single farmer advance ledger.
     */
    public function printLedger(Farmer $farmer)
    {
        $advances = FarmerAdvance::where('farmer_id', $farmer->id)->orderBy('advance_date', 'desc')->get();
        return view('advances.print_ledger', compact('farmer', 'advances'));
    }
}
