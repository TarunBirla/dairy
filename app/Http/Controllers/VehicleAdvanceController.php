<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleAdvance;
use App\Models\VehicleAdvanceRepayment;
use App\Models\AuditLog;
use Carbon\Carbon;

class VehicleAdvanceController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('vehicle_advances')) {
                Schema::create('vehicle_advances', function (Blueprint $table) {
                    $table->id();
                    $table->string('voucher_no')->unique();
                    $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
                    $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
                    $table->date('advance_date');
                    $table->decimal('amount', 10, 2);
                    $table->decimal('interest_rate', 5, 2)->default(0.00);
                    $table->decimal('paid_amount', 10, 2)->default(0.00);
                    $table->decimal('balance_amount', 10, 2)->default(0.00);
                    $table->text('remarks')->nullable();
                    $table->string('status')->default('active');
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('vehicle_advance_repayments')) {
                Schema::create('vehicle_advance_repayments', function (Blueprint $table) {
                    $table->id();
                    $table->string('receipt_no')->unique();
                    $table->foreignId('vehicle_advance_id')->constrained('vehicle_advances')->cascadeOnDelete();
                    $table->date('repayment_date');
                    $table->decimal('amount', 10, 2);
                    $table->string('payment_mode')->default('Cash');
                    $table->text('remarks')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('vehicles') && !Schema::hasColumn('vehicles', 'driver_id')) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->unsignedBigInteger('driver_id')->nullable()->after('vehicle_type');
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Display Vehicle Advance Listing matching Screenshot 2 (media_1791389941427.png)
     */
    public function index(Request $request)
    {
        $vehicleId = $request->get('vehicle_id');
        $search = $request->get('search');

        $relations = ['driver', 'repayments'];
        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'driver_id')) {
            $relations[] = 'vehicle.driver';
        } else {
            $relations[] = 'vehicle';
        }
        $query = VehicleAdvance::with($relations);

        if (!empty($vehicleId) && $vehicleId !== 'all') {
            $query->where('vehicle_id', $vehicleId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function ($v) use ($search) {
                      $v->where('vehicle_number', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function ($d) use ($search) {
                      $d->where('name', 'like', "%{$search}%")
                        ->orWhere('driver_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $advances = $query->latest('id')->paginate(20)->withQueryString();

        // Calculate KPI Totals matching the 3 cards in Screenshot 2
        $summaryQuery = clone $query;
        $totalAdvance = (float) $summaryQuery->sum('amount');
        $totalPaid = (float) $summaryQuery->sum('paid_amount');
        $totalBalance = (float) $summaryQuery->sum('balance_amount');

        $vehicleQuery = Vehicle::query()->orderBy('vehicle_number');
        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'driver_id')) {
            $vehicleQuery->with('driver');
        }
        $vehicles = $vehicleQuery->get();
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();

        // Suggested Voucher No
        $nextVoucher = 'VADV-' . str_pad((VehicleAdvance::max('id') + 101), 4, '0', STR_PAD_LEFT);

        return view('vehicles.advances.index', compact(
            'advances',
            'vehicles',
            'drivers',
            'vehicleId',
            'search',
            'totalAdvance',
            'totalPaid',
            'totalBalance',
            'nextVoucher'
        ));
    }

    /**
     * Store new vehicle advance matching Screenshot 3 (media_1791389958966.png)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'voucher_no' => 'required|string|max:50|unique:vehicle_advances,voucher_no',
            'interest_rate' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $vehicle = Vehicle::find($validated['vehicle_id']);
        $driverId = $validated['driver_id'] ?? ($vehicle->driver_id ?? null);
        $amount = (float) $validated['amount'];

        $advance = VehicleAdvance::create([
            'voucher_no' => $validated['voucher_no'],
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $driverId,
            'advance_date' => $validated['advance_date'],
            'amount' => $amount,
            'interest_rate' => $validated['interest_rate'] ?? 0.00,
            'paid_amount' => 0.00,
            'balance_amount' => $amount,
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        AuditLog::log("Issued Vehicle Advance #{$advance->voucher_no} of ₹{$amount} to Vehicle {$vehicle->vehicle_number}", 'VehicleAdvance', $advance->id);

        return redirect()->route('vehicles.advances.index')->with('success', "Advance #{$advance->voucher_no} of ₹" . number_format($amount, 2) . " issued successfully!");
    }

    /**
     * Update vehicle advance
     */
    public function update(Request $request, VehicleAdvance $advance)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'advance_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'voucher_no' => "required|string|max:50|unique:vehicle_advances,voucher_no,{$advance->id}",
            'interest_rate' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $amount = (float) $validated['amount'];
        $paid = (float) $advance->paid_amount;
        $balance = max(0, $amount - $paid);

        $advance->update([
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $validated['driver_id'] ?? $advance->driver_id,
            'advance_date' => $validated['advance_date'],
            'amount' => $amount,
            'interest_rate' => $validated['interest_rate'] ?? 0.00,
            'balance_amount' => $balance,
            'remarks' => $validated['remarks'] ?? null,
            'status' => $balance <= 0 ? 'settled' : 'active',
        ]);

        AuditLog::log("Updated Vehicle Advance #{$advance->voucher_no}", 'VehicleAdvance', $advance->id);

        return redirect()->route('vehicles.advances.index')->with('success', "Advance #{$advance->voucher_no} updated successfully!");
    }

    /**
     * Record repayment received against advance matching Screenshot 2 "Receive" button
     */
    public function receiveRepayment(Request $request)
    {
        $validated = $request->validate([
            'vehicle_advance_id' => 'required|exists:vehicle_advances,id',
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $advance = VehicleAdvance::findOrFail($validated['vehicle_advance_id']);
        $receiptNo = 'VREC-' . str_pad((VehicleAdvanceRepayment::max('id') + 101), 4, '0', STR_PAD_LEFT);

        $repayment = VehicleAdvanceRepayment::create([
            'receipt_no' => $receiptNo,
            'vehicle_advance_id' => $advance->id,
            'repayment_date' => $validated['repayment_date'],
            'amount' => $validated['amount'],
            'payment_mode' => $validated['payment_mode'],
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Recalculate advance totals
        $advance->recalculate();

        AuditLog::log("Received Repayment #{$receiptNo} of ₹{$repayment->amount} on Advance #{$advance->voucher_no}", 'VehicleAdvanceRepayment', $repayment->id);

        return redirect()->route('vehicles.advances.index')->with('success', "Repayment #{$receiptNo} of ₹" . number_format($repayment->amount, 2) . " received successfully! New Balance: ₹" . number_format($advance->balance_amount, 2));
    }

    /**
     * Download Sample CSV template matching Screenshot 4
     */
    public function sampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="vehicle_advances_sample.csv"',
        ];

        $columns = ['Vehicle Number', 'Driver Code', 'Advance Date (YYYY-MM-DD)', 'Amount', 'Voucher No', 'Remarks'];
        $samples = [
            ['UP123456789', 'DRV001', date('Y-m-d'), '5000.00', 'VADV-101', 'Fuel and maintenance advance'],
            ['JH0AB1125', 'DRV002', date('Y-m-d'), '2500.00', 'VADV-102', 'Trip allowance'],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);
            foreach ($samples as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import CSV matching Screenshot 4 (media_1791389968940.png)
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Skip header

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (empty(array_filter($row))) continue;

            $vehNum = trim($row[0] ?? '');
            $drvCode = trim($row[1] ?? '');
            $date = trim($row[2] ?? date('Y-m-d'));
            $amount = (float) trim($row[3] ?? 0);
            $voucher = trim($row[4] ?? '');
            $remarks = trim($row[5] ?? 'Imported Advance');

            if (empty($vehNum) || $amount <= 0) {
                continue;
            }

            // Find vehicle
            $vehicle = Vehicle::where('vehicle_number', $vehNum)->first();
            if (!$vehicle) {
                $vehicle = Vehicle::create([
                    'vehicle_number' => $vehNum,
                    'vehicle_type' => 'van',
                    'status' => 'available',
                ]);
            }

            // Find driver if code given
            $driverId = null;
            if (!empty($drvCode)) {
                $driver = Driver::where('driver_code', $drvCode)->first();
                if ($driver) $driverId = $driver->id;
            }

            if (empty($voucher)) {
                $voucher = 'VADV-' . str_pad((VehicleAdvance::max('id') + 101), 4, '0', STR_PAD_LEFT);
            }

            VehicleAdvance::updateOrCreate(
                ['voucher_no' => $voucher],
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driverId ?? $vehicle->driver_id,
                    'advance_date' => $date,
                    'amount' => $amount,
                    'paid_amount' => 0.00,
                    'balance_amount' => $amount,
                    'remarks' => $remarks,
                    'status' => 'active',
                    'created_by' => Auth::id(),
                ]
            );

            $imported++;
        }

        fclose($handle);

        AuditLog::log("Imported {$imported} Vehicle Advances from CSV", 'VehicleAdvance', null);

        return redirect()->route('vehicles.advances.index')->with('success', "Successfully imported {$imported} vehicle advances!");
    }

    /**
     * Printable view for Vehicle Advances list matching "Print" button
     */
    public function print(Request $request)
    {
        $vehicleId = $request->get('vehicle_id');
        $relations = ['driver'];
        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'driver_id')) {
            $relations[] = 'vehicle.driver';
        } else {
            $relations[] = 'vehicle';
        }
        $query = VehicleAdvance::with($relations);

        if (!empty($vehicleId) && $vehicleId !== 'all') {
            $query->where('vehicle_id', $vehicleId);
        }

        $advances = $query->latest('id')->get();
        $totalAdvance = $advances->sum('amount');
        $totalPaid = $advances->sum('paid_amount');
        $totalBalance = $advances->sum('balance_amount');

        return view('vehicles.advances.print', compact('advances', 'totalAdvance', 'totalPaid', 'totalBalance'));
    }

    /**
     * Delete vehicle advance
     */
    public function destroy(VehicleAdvance $advance)
    {
        $voucher = $advance->voucher_no;
        $advance->delete();

        AuditLog::log("Deleted Vehicle Advance #{$voucher}", 'VehicleAdvance', null);

        return redirect()->route('vehicles.advances.index')->with('success', "Advance #{$voucher} deleted successfully.");
    }
}
