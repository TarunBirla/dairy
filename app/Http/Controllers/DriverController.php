<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Driver;
use App\Models\AuditLog;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('drivers')) {
                Schema::create('drivers', function (Blueprint $table) {
                    $table->id();
                    $table->string('driver_code')->unique();
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('license_number')->nullable();
                    $table->string('aadhaar_card_no')->nullable();
                    $table->string('pan_card_no')->nullable();
                    $table->string('account_holder')->nullable();
                    $table->string('account_number')->nullable();
                    $table->string('bank_name')->nullable();
                    $table->string('ifsc_code')->nullable();
                    $table->string('bank_branch')->nullable();
                    $table->string('status')->default('active');
                    $table->text('address')->nullable();
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
     * Display Driver Listing matching Screenshot 1 (media_1791389924187.png)
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Driver::query();
        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'driver_id')) {
            $query->with('vehicles');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('driver_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('aadhaar_card_no', 'like', "%{$search}%")
                  ->orWhere('pan_card_no', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $drivers = $query->latest('id')->paginate(20)->withQueryString();

        // Next driver code
        $nextCode = 'DRV' . str_pad((Driver::max('id') + 1), 3, '0', STR_PAD_LEFT);

        return view('drivers.index', compact('drivers', 'search', 'status', 'nextCode'));
    }

    /**
     * Store a new driver matching fields in Screenshot 1
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_code' => 'required|string|max:50|unique:drivers,driver_code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'aadhaar_card_no' => 'nullable|string|max:50',
            'pan_card_no' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive',
            'address' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['created_by'] = Auth::id();

        $driver = Driver::create($validated);

        AuditLog::log("Registered Driver {$driver->name} ({$driver->driver_code})", 'Driver', $driver->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Driver {$driver->name} registered successfully!",
                'driver' => $driver,
            ]);
        }

        return redirect()->route('drivers.index')->with('success', "Driver {$driver->name} registered successfully!");
    }

    /**
     * Update driver details
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'driver_code' => "required|string|max:50|unique:drivers,driver_code,{$driver->id}",
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'aadhaar_card_no' => 'nullable|string|max:50',
            'pan_card_no' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive',
            'address' => 'nullable|string',
        ]);

        $driver->update($validated);

        AuditLog::log("Updated Driver {$driver->name}", 'Driver', $driver->id);

        return redirect()->route('drivers.index')->with('success', "Driver {$driver->name} updated successfully!");
    }

    /**
     * Delete driver
     */
    public function destroy(Driver $driver)
    {
        $name = $driver->name;
        $driver->delete();

        AuditLog::log("Deleted Driver {$name}", 'Driver', $driver->id);

        return redirect()->route('drivers.index')->with('success', "Driver {$name} deleted successfully.");
    }
}
