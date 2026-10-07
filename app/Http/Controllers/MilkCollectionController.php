<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MilkCollection;
use App\Models\Farmer;
use App\Models\CollectionCenter;
use App\Models\RateChart;
use App\Models\FarmerLedger;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;

class MilkCollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = MilkCollection::with(['farmer', 'collectionCenter', 'operator']);

        if ($request->filled('date')) {
            $query->whereDate('collection_date', $request->date);
        } else {
            // Default to today
            $query->whereDate('collection_date', Carbon::today());
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }

        if ($request->filled('milk_type')) {
            $query->where('milk_type', $request->milk_type);
        }

        $collections = $query->latest()->paginate(20)->withQueryString();

        $todayTotalLiters = (clone $query)->sum('quantity_liters');
        $todayTotalAmount = (clone $query)->sum('net_amount');
        $avgFat = (clone $query)->avg('fat') ?? 0;
        $avgSnf = (clone $query)->avg('snf') ?? 0;

        $centers = CollectionCenter::all();

        return view('collections.index', compact('collections', 'todayTotalLiters', 'todayTotalAmount', 'avgFat', 'avgSnf', 'centers'));
    }

    public function create()
    {
        $farmers = Farmer::where('status', 'active')->orderBy('name')->get();
        $centers = CollectionCenter::where('status', 'active')->get();
        $rateCharts = RateChart::where('status', 'active')->get();
        $currentShift = (date('H') >= 15) ? 'evening' : 'morning';
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        return view('collections.create', compact('farmers', 'centers', 'rateCharts', 'currentShift', 'today', 'nextReceipt'));
    }

    public function calculateRate(Request $request)
    {
        $fat = (float) $request->get('fat', 4.0);
        $snf = (float) $request->get('snf', 8.5);
        $milkType = $request->get('milk_type', 'cow');
        $farmerId = $request->get('farmer_id');

        $farmer = $farmerId ? Farmer::find($farmerId) : null;
        if ($farmer && $farmer->custom_rate_override) {
            $rate = (float) $farmer->custom_rate_override;
        } else {
            $chart = RateChart::where('milk_type', $milkType)->where('status', 'active')->first()
                     ?? RateChart::where('is_default', true)->first();
            $rate = $chart ? $chart->calculateRate($fat, $snf) : 40.0;
        }

        return response()->json([
            'rate' => $rate,
            'fat' => $fat,
            'snf' => $snf,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'collection_center_id' => 'nullable|exists:collection_centers,id',
            'collection_date' => 'required|date',
            'shift' => 'required|in:morning,evening',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat' => 'required|numeric|min:1|max:15',
            'snf' => 'required|numeric|min:4|max:15',
            'clr' => 'nullable|numeric',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        // Determine rate
        $chart = RateChart::where('milk_type', $validated['milk_type'])->where('status', 'active')->first()
                 ?? RateChart::where('is_default', true)->first();
        $calcRate = $chart ? $chart->calculateRate($validated['fat'], $validated['snf']) : 40.0;
        $appliedRate = $farmer->custom_rate_override ? (float) $farmer->custom_rate_override : $calcRate;

        $gross = round($validated['quantity_liters'] * $appliedRate, 2);
        $bonus = (float) ($validated['bonus'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $net = round($gross + $bonus - $deduction, 2);

        $receiptNumber = 'COL-' . (MilkCollection::max('id') + 1001);

        $collection = MilkCollection::create([
            'receipt_number' => $receiptNumber,
            'farmer_id' => $farmer->id,
            'collection_center_id' => $validated['collection_center_id'] ?? $farmer->collection_center_id,
            'operator_id' => Auth::id(),
            'collection_date' => $validated['collection_date'],
            'shift' => $validated['shift'],
            'milk_type' => $validated['milk_type'],
            'quantity_liters' => $validated['quantity_liters'],
            'fat' => $validated['fat'],
            'snf' => $validated['snf'],
            'clr' => $validated['clr'] ?? null,
            'calculated_rate' => $calcRate,
            'applied_rate' => $appliedRate,
            'gross_amount' => $gross,
            'bonus' => $bonus,
            'deduction' => $deduction,
            'net_amount' => $net,
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update farmer balance and ledger
        $newBalance = $farmer->current_balance + $net;
        $farmer->update(['current_balance' => $newBalance]);

        FarmerLedger::create([
            'farmer_id' => $farmer->id,
            'transaction_date' => $validated['collection_date'],
            'type' => 'credit',
            'amount' => $net,
            'balance' => $newBalance,
            'reference_type' => 'milk_collection',
            'reference_id' => $collection->id,
            'description' => "Milk Collection {$validated['shift']} ({$validated['quantity_liters']} Ltr @ ₹{$appliedRate}/Ltr)",
        ]);

        // Create SMS/WhatsApp simulated notification
        Notification::create([
            'user_id' => $farmer->user_id,
            'title' => "Milk Slip: {$receiptNumber}",
            'message' => "Milk Recorded: {$validated['quantity_liters']} Ltr ({$validated['milk_type']}), FAT: {$validated['fat']}%, SNF: {$validated['snf']}%, Rate: ₹{$appliedRate}, Net: ₹{$net} credited to your ledger.",
            'channel' => 'whatsapp',
            'type' => 'success',
            'recipient_phone' => $farmer->phone,
            'status' => 'sent',
        ]);

        AuditLog::log('Recorded Milk Collection', 'MilkCollection', $collection->id, [
            'liters' => $validated['quantity_liters'],
            'rate' => $appliedRate,
            'net' => $net,
        ]);

        return redirect()->route('collections.slip', $collection)->with('success', "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}");
    }

    public function slip(MilkCollection $collection)
    {
        $collection->load(['farmer', 'collectionCenter', 'operator']);
        return view('collections.slip', compact('collection'));
    }
}
