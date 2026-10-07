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
        $dispatches = MilkDispatch::with(['route', 'deliveryBoy'])->latest()->paginate(15);
        $todayDispatched = MilkDispatch::whereDate('dispatch_date', Carbon::today())->sum('total_milk_quantity');
        $routes = DeliveryRoute::where('status', 'active')->get();
        $deliveryBoys = User::where('role', User::ROLE_DELIVERY_BOY)->get();

        return view('dispatch.index', compact('dispatches', 'todayDispatched', 'routes', 'deliveryBoys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dispatch_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'route_id' => 'required|exists:delivery_routes,id',
            'delivery_boy_id' => 'nullable|exists:users,id',
            'total_milk_quantity' => 'required|numeric|min:0.5',
            'vehicle_number' => 'nullable|string',
            'fat' => 'nullable|numeric|min:0|max:15',
            'snf' => 'nullable|numeric|min:0|max:15',
            'temperature' => 'nullable|numeric',
            'bottles_loaded' => 'nullable|integer',
            'crates_loaded' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $dispatchNumber = 'DSP-' . date('Ymd') . '-' . sprintf('%03d', MilkDispatch::whereDate('created_at', Carbon::today())->count() + 1);

        $dispatch = MilkDispatch::create(array_merge($validated, [
            'dispatch_number' => $dispatchNumber,
            'status' => 'in_transit',
            'dispatched_at' => Carbon::now()->toTimeString(),
            'created_by' => auth()->id(),
        ]));

        AuditLog::log('Created Milk Dispatch', 'MilkDispatch', $dispatch->id);

        return back()->with('success', "Milk Dispatch {$dispatchNumber} recorded and in-transit!");
    }
}
