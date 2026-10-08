<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MilkDispatch;
use App\Models\DeliveryRoute;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;
use Carbon\Carbon;

class MilkDispatchController extends Controller
{
    public function index(Request $request)
    {
        $query = MilkDispatch::query();

        if ($request->filled('from_date')) {
            $query->whereDate('from_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('to_date', '<=', $request->to_date);
        }
        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('from_shift', $request->shift)
                  ->orWhere('to_shift', $request->shift)
                  ->orWhere('shift', $request->shift);
            });
        }
        if ($request->filled('drop_location')) {
            $query->where('drop_location', 'like', '%' . $request->drop_location . '%');
        }

        $dispatches = $query->latest('id')->paginate(15)->withQueryString();

        // 4 KPI Summary Cards matching Screenshot
        $totalDispatchCount = (clone $query)->count();
        $totalQuantity = (clone $query)->sum('quantity_ltr');
        if ($totalQuantity == 0) {
            $totalQuantity = (clone $query)->sum('total_milk_quantity');
        }
        $totalAmount = (clone $query)->sum('amount');
        $totalVehicles = (clone $query)->whereNotNull('vehicle_number')->where('vehicle_number', '!=', '')->distinct('vehicle_number')->count('vehicle_number');
        if ($totalVehicles == 0 && $totalDispatchCount > 0) {
            $totalVehicles = 1;
        }

        $routes = DeliveryRoute::where('status', 'active')->get();

        return view('dispatch.index', compact(
            'dispatches',
            'totalDispatchCount',
            'totalQuantity',
            'totalAmount',
            'totalVehicles',
            'routes'
        ));
    }

    public function create()
    {
        $routes = DeliveryRoute::where('status', 'active')->get();
        $deliveryBoys = User::where('role', User::ROLE_DELIVERY_BOY)->get();
        $nextChallanNo = 'CH' . (MilkDispatch::count() + 101);

        return view('dispatch.create', compact('routes', 'deliveryBoys', 'nextChallanNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'from_shift' => 'required|string',
            'to_date' => 'required|date',
            'to_shift' => 'required|string',
            'challan_date' => 'required|date',
            'challan_number' => 'nullable|string|max:50',
            'dispatch_type' => 'required|string|max:30',
            'drop_location' => 'nullable|string|max:191',

            // Items
            'milk_type' => 'required|string|max:30',
            'purchase_qty' => 'nullable|numeric|min:0',
            'milk_quality' => 'nullable|string|max:30',
            'quantity_ltr' => 'required|numeric|min:0',
            'prev_balance' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'loss' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'snf' => 'nullable|numeric',
            'clr' => 'nullable|numeric',
            'can_number' => 'nullable|string|max:50',
            'temperature' => 'nullable|numeric',
            'acidity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',

            // Bottom Vehicle & Route
            'route_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_in_time' => 'nullable|string|max:20',
            'vehicle_out_time' => 'nullable|string|max:20',
            'seal_number' => 'nullable|string|max:50',
            'chamber_number' => 'nullable|string|max:50',
            'headload_kms' => 'nullable|numeric',
            'difference' => 'nullable|numeric',
        ]);

        $dispatchNumber = 'DSP-' . date('Ymd') . '-' . sprintf('%03d', MilkDispatch::whereDate('created_at', Carbon::today())->count() + 1);

        // Try 'completed', if DB is still old enum fallback to 'delivered'
        $statusVal = 'completed';
        try {
            $dispatch = MilkDispatch::create(array_merge($validated, [
                'dispatch_number' => $dispatchNumber,
                'dispatch_date' => $request->from_date,
                'shift' => strtolower($request->from_shift) === 'evening' ? 'evening' : 'morning',
                'total_milk_quantity' => $request->quantity_ltr,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]));
        } catch (\Throwable $e) {
            // Fallback for legacy enum ['prepared', 'in_transit', 'delivered', 'returned']
            if (str_contains($e->getMessage(), 'status')) {
                $dispatch = MilkDispatch::create(array_merge($validated, [
                    'dispatch_number' => $dispatchNumber,
                    'dispatch_date' => $request->from_date,
                    'shift' => strtolower($request->from_shift) === 'evening' ? 'evening' : 'morning',
                    'total_milk_quantity' => $request->quantity_ltr,
                    'status' => 'delivered',
                    'created_by' => auth()->id(),
                ]));
            } else {
                throw $e;
            }
        }

        AuditLog::log('Created Milk Dispatch', 'MilkDispatch', $dispatch->id);

        return redirect()->route('dispatch.index')->with('success', "Milk Dispatch challan #{$dispatch->challan_number} recorded successfully!");
    }

    public function edit(MilkDispatch $dispatch)
    {
        $routes = DeliveryRoute::where('status', 'active')->get();
        $deliveryBoys = User::where('role', User::ROLE_DELIVERY_BOY)->get();

        return view('dispatch.edit', compact('dispatch', 'routes', 'deliveryBoys'));
    }

    public function update(Request $request, MilkDispatch $dispatch)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'from_shift' => 'required|string',
            'to_date' => 'required|date',
            'to_shift' => 'required|string',
            'challan_date' => 'required|date',
            'challan_number' => 'nullable|string|max:50',
            'dispatch_type' => 'required|string|max:30',
            'drop_location' => 'nullable|string|max:191',

            // Items
            'milk_type' => 'required|string|max:30',
            'purchase_qty' => 'nullable|numeric|min:0',
            'milk_quality' => 'nullable|string|max:30',
            'quantity_ltr' => 'required|numeric|min:0',
            'prev_balance' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'loss' => 'nullable|numeric',
            'fat' => 'nullable|numeric',
            'snf' => 'nullable|numeric',
            'clr' => 'nullable|numeric',
            'can_number' => 'nullable|string|max:50',
            'temperature' => 'nullable|numeric',
            'acidity' => 'nullable|numeric',
            'amount' => 'nullable|numeric',

            // Bottom Vehicle & Route
            'route_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_in_time' => 'nullable|string|max:20',
            'vehicle_out_time' => 'nullable|string|max:20',
            'seal_number' => 'nullable|string|max:50',
            'chamber_number' => 'nullable|string|max:50',
            'headload_kms' => 'nullable|numeric',
            'difference' => 'nullable|numeric',
        ]);

        $dispatch->update(array_merge($validated, [
            'dispatch_date' => $request->from_date,
            'shift' => strtolower($request->from_shift) === 'evening' ? 'evening' : 'morning',
            'total_milk_quantity' => $request->quantity_ltr,
        ]));

        AuditLog::log('Updated Milk Dispatch', 'MilkDispatch', $dispatch->id);

        return redirect()->route('dispatch.index')->with('success', "Milk Dispatch challan #{$dispatch->challan_number} updated successfully!");
    }

    public function destroy(MilkDispatch $dispatch)
    {
        $challanNo = $dispatch->challan_number ?? $dispatch->dispatch_number;
        $dispatch->delete();

        AuditLog::log('Deleted Milk Dispatch', 'MilkDispatch', $dispatch->id);

        return redirect()->route('dispatch.index')->with('success', "Dispatch record #{$challanNo} deleted successfully.");
    }
}
