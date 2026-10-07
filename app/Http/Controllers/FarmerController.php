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
    public function index(Request $request)
    {
        $query = Farmer::with(['collectionCenter', 'rateChart']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%");
            });
        }

        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $farmers = $query->latest()->paginate(15)->withQueryString();
        $totalFarmers = Farmer::count();
        $activeFarmers = Farmer::where('status', 'active')->count();
        $totalPayable = Farmer::sum('current_balance');

        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();

        return view('farmers.index', compact('farmers', 'totalFarmers', 'activeFarmers', 'totalPayable', 'branches', 'centers', 'rateCharts'));
    }

    public function create()
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();
        $nextCode = 'FAR-' . (Farmer::max('id') + 101);

        return view('farmers.create', compact('branches', 'centers', 'rateCharts', 'nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_code' => 'required|string|unique:farmers,farmer_code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'upi_id' => 'nullable|string',
        ]);

        $farmer = Farmer::create($validated);
        AuditLog::log('Created Farmer', 'Farmer', $farmer->id, ['code' => $farmer->farmer_code]);

        return redirect()->route('farmers.show', $farmer)->with('success', "Farmer {$farmer->name} registered successfully!");
    }

    public function show(Farmer $farmer)
    {
        $farmer->load(['collectionCenter', 'rateChart', 'advances', 'settlements']);
        $collections = MilkCollection::where('farmer_id', $farmer->id)->latest()->take(20)->get();
        $ledgers = FarmerLedger::where('farmer_id', $farmer->id)->latest()->take(30)->get();
        $advances = FarmerAdvance::where('farmer_id', $farmer->id)->latest()->get();

        $totalMilkLiters = MilkCollection::where('farmer_id', $farmer->id)->sum('quantity_liters');
        $totalGrossEarned = MilkCollection::where('farmer_id', $farmer->id)->sum('net_amount');

        return view('farmers.show', compact('farmer', 'collections', 'ledgers', 'advances', 'totalMilkLiters', 'totalGrossEarned'));
    }

    public function edit(Farmer $farmer)
    {
        $branches = Branch::all();
        $centers = CollectionCenter::all();
        $rateCharts = RateChart::where('status', 'active')->get();

        return view('farmers.edit', compact('farmer', 'branches', 'centers', 'rateCharts'));
    }

    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'village' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'rate_chart_id' => 'nullable|exists:rate_charts,id',
            'animal_type' => 'required|in:cow,buffalo,mixed',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'upi_id' => 'nullable|string',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $farmer->update($validated);
        AuditLog::log('Updated Farmer', 'Farmer', $farmer->id);

        return redirect()->route('farmers.show', $farmer)->with('success', 'Farmer updated successfully.');
    }

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
