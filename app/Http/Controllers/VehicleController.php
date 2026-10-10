<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Vehicle;
use App\Models\DeliveryRoute;
use App\Models\AuditLog;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('vehicles')) {
                Schema::create('vehicles', function (Blueprint $table) {
                    $table->id();
                    $table->string('vehicle_number')->unique();
                    $table->string('vehicle_type')->default('van');
                    $table->unsignedBigInteger('driver_id')->nullable();
                    $table->string('driver_name')->nullable();
                    $table->string('driver_phone')->nullable();
                    $table->decimal('capacity', 10, 2)->default(0.00);
                    $table->decimal('current_km', 10, 2)->default(0.00);
                    $table->decimal('per_km_rate', 10, 2)->default(0.00);
                    $table->string('assigned_route')->nullable();
                    $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
                    $table->string('status')->default('available');
                    $table->text('notes')->nullable();
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
     * Display Vehicle Listing matching Screenshot 2 (media_1791389598973.png)
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $route = $request->get('route');

        $query = Vehicle::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('driver_phone', 'like', "%{$search}%")
                  ->orWhere('vehicle_type', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($route) && $route !== 'all') {
            $query->where('assigned_route', $route);
        }

        $vehicles = $query->latest('id')->paginate(20)->withQueryString();
        $routes = DeliveryRoute::where('status', 'active')->get();
        $allAssignedRoutes = Vehicle::whereNotNull('assigned_route')->distinct()->pluck('assigned_route');

        return view('vehicles.index', compact('vehicles', 'routes', 'allAssignedRoutes', 'search', 'status', 'route'));
    }

    /**
     * Store a new vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50|unique:vehicles,vehicle_number',
            'vehicle_type' => 'required|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'capacity' => 'nullable|numeric|min:0',
            'current_km' => 'nullable|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'assigned_route' => 'nullable|string|max:150',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'status' => 'nullable|string|in:available,on_duty,maintenance,inactive',
            'notes' => 'nullable|string',
        ]);

        $validated['capacity'] = $validated['capacity'] ?? 0;
        $validated['current_km'] = $validated['current_km'] ?? 0;
        $validated['per_km_rate'] = $validated['per_km_rate'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'available';
        $validated['created_by'] = Auth::id();

        $vehicle = Vehicle::create($validated);

        AuditLog::log("Added Vehicle {$vehicle->vehicle_number}", 'Vehicle', $vehicle->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Vehicle {$vehicle->vehicle_number} added successfully!",
                'vehicle' => $vehicle,
            ]);
        }

        return redirect()->route('vehicles.index')->with('success', "Vehicle {$vehicle->vehicle_number} added successfully!");
    }

    /**
     * Update vehicle details
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'vehicle_number' => "required|string|max:50|unique:vehicles,vehicle_number,{$vehicle->id}",
            'vehicle_type' => 'required|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:20',
            'capacity' => 'nullable|numeric|min:0',
            'current_km' => 'nullable|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'assigned_route' => 'nullable|string|max:150',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'status' => 'nullable|string|in:available,on_duty,maintenance,inactive',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        AuditLog::log("Updated Vehicle {$vehicle->vehicle_number}", 'Vehicle', $vehicle->id);

        return redirect()->route('vehicles.index')->with('success', "Vehicle {$vehicle->vehicle_number} updated successfully!");
    }

    /**
     * Delete a vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $number = $vehicle->vehicle_number;
        $vehicle->delete();

        AuditLog::log("Deleted Vehicle {$number}", 'Vehicle', $vehicle->id);

        return redirect()->route('vehicles.index')->with('success', "Vehicle {$number} deleted successfully.");
    }
}
