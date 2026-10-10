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
    /**
     * Milk Collections Listing & Quick Modal Dashboard
     */
    public function index(Request $request)
    {
        $query = MilkCollection::with(['farmer', 'collectionCenter', 'operator']);

        // Date Filter
        if ($request->filled('date')) {
            $query->whereDate('collection_date', $request->date);
        } else {
            // Default to today
            $query->whereDate('collection_date', Carbon::today());
        }

        // Shift Filter
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        // Center Filter
        if ($request->filled('center_id')) {
            $query->where('collection_center_id', $request->center_id);
        }

        // Milk Type Filter
        if ($request->filled('milk_type')) {
            $query->where('milk_type', $request->milk_type);
        }

        // Farmer Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('farmer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $collections = $query->latest()->paginate(20)->withQueryString();

        // Metrics Summary on Filtered Scope
        $todayTotalLiters = (clone $query)->sum('quantity_liters');
        $todayTotalAmount = (clone $query)->sum('net_amount');
        $avgFat = (clone $query)->avg('fat') ?? 0;
        $avgSnf = (clone $query)->avg('snf') ?? 0;

        // Master Data for Filters & In-Page Collection Popup Modal
        $centers = CollectionCenter::where('status', 'active')->get();
        if ($centers->isEmpty()) {
            $centers = CollectionCenter::all();
        }
        $farmers = Farmer::where(function($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'ACTIVE')
              ->orWhereNull('status');
        })->orderBy('farmer_code')->get();

        if ($farmers->isEmpty()) {
            $farmers = Farmer::orderBy('farmer_code')->get();
        }
        $rateCharts = RateChart::where('status', 'active')->get();
        $currentShift = (date('H') >= 14) ? 'evening' : 'morning';
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        return view('collections.index', compact(
            'collections',
            'todayTotalLiters',
            'todayTotalAmount',
            'avgFat',
            'avgSnf',
            'centers',
            'farmers',
            'rateCharts',
            'currentShift',
            'today',
            'nextReceipt'
        ));
    }

    /**
     * Dedicated create page (redirects to modal or standalone)
     */
    public function create()
    {
        $farmers = Farmer::where('status', 'active')->orderBy('farmer_code')->get();
        $centers = CollectionCenter::where('status', 'active')->get();
        if ($centers->isEmpty()) {
            $centers = CollectionCenter::all();
        }
        $rateCharts = RateChart::where('status', 'active')->get();
        $currentShift = (date('H') >= 14) ? 'evening' : 'morning';
        $today = Carbon::today()->format('Y-m-d');
        $nextReceipt = 'COL-' . (MilkCollection::max('id') + 1001);

        return view('collections.create', compact('farmers', 'centers', 'rateCharts', 'currentShift', 'today', 'nextReceipt'));
    }

    /**
     * Live Rate Calculation API
     */
    public function calculateRate(Request $request)
    {
        $fat = (float) $request->get('fat', 4.0);
        $snf = (float) $request->get('snf', 8.5);
        $milkType = $request->get('milk_type', 'cow');
        $farmerId = $request->get('farmer_id');

        $farmer = $farmerId ? Farmer::find($farmerId) : null;
        $chartName = 'Standard Dairy Formula';
        
        if ($farmer && $farmer->custom_rate_override) {
            $rate = (float) $farmer->custom_rate_override;
            $chartName = "Custom Farmer Override (₹{$rate})";
        } else {
            $chart = null;
            if ($farmer && $farmer->rate_chart_id) {
                $chart = RateChart::where('id', $farmer->rate_chart_id)->where('status', 'active')->first();
            }
            if (!$chart) {
                $chart = RateChart::where('milk_type', $milkType)->where('status', 'active')->first()
                         ?? RateChart::where('is_default', true)->first();
            }
            if ($chart) {
                $rate = $chart->calculateRate($fat, $snf);
                $chartName = $chart->name . " (₹" . number_format($rate, 2) . ")";
            } else {
                // Fallback standard formula
                $rate = ($milkType === 'buffalo') ? (($fat * 7.2) + ($snf * 4.5)) : (($fat * 6.8) + ($snf * 4.1));
                $rate = round($rate, 2);
                $chartName = ucfirst($milkType) . " Formula (₹" . number_format($rate, 2) . ")";
            }
        }

        return response()->json([
            'rate' => $rate,
            'fat' => $fat,
            'snf' => $snf,
            'chart_name' => $chartName,
        ]);
    }

    /**
     * Store new collection entry
     */
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
            'applied_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $farmer = Farmer::findOrFail($validated['farmer_id']);

        // Determine rate
        if (!empty($validated['applied_rate']) && (float) $validated['applied_rate'] > 0) {
            $appliedRate = (float) $validated['applied_rate'];
            $calcRate = $appliedRate;
        } else {
            $chart = null;
            if ($farmer && $farmer->rate_chart_id) {
                $chart = RateChart::where('id', $farmer->rate_chart_id)->where('status', 'active')->first();
            }
            if (!$chart) {
                $chart = RateChart::where('milk_type', $validated['milk_type'])->where('status', 'active')->first()
                         ?? RateChart::where('is_default', true)->first();
            }
            $calcRate = $chart ? $chart->calculateRate($validated['fat'], $validated['snf']) : 40.0;
            $appliedRate = $farmer->custom_rate_override ? (float) $farmer->custom_rate_override : $calcRate;
        }

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
        $newBalance = (float) $farmer->current_balance + $net;
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

        // Notification
        if ($farmer->user_id) {
            Notification::create([
                'user_id' => $farmer->user_id,
                'title' => "Milk Slip: {$receiptNumber}",
                'message' => "Milk Recorded: {$validated['quantity_liters']} Ltr ({$validated['milk_type']}), FAT: {$validated['fat']}%, SNF: {$validated['snf']}%, Rate: ₹{$appliedRate}, Net: ₹{$net} credited to your ledger.",
                'channel' => 'whatsapp',
                'type' => 'success',
                'recipient_phone' => $farmer->phone,
                'status' => 'sent',
            ]);
        }

        AuditLog::log('Recorded Milk Collection', 'MilkCollection', $collection->id, [
            'liters' => $validated['quantity_liters'],
            'rate' => $appliedRate,
            'net' => $net,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            $collection->load(['farmer', 'collectionCenter']);
            return response()->json([
                'success' => true,
                'message' => "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}",
                'collection' => $collection,
                'next_receipt' => 'COL-' . (MilkCollection::max('id') + 1001),
                'slip_url' => route('collections.slip', $collection),
            ]);
        }

        if ($request->has('print_slip') && $request->print_slip) {
            return redirect()->route('collections.slip', $collection)->with('success', "Collection {$receiptNumber} saved! Net Amount: ₹{$net}");
        }

        return redirect()->route('collections.index')->with('success', "Collection {$receiptNumber} saved successfully! Amount: ₹{$net}");
    }

    /**
     * Update an existing collection entry
     */
    public function update(Request $request, MilkCollection $collection)
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
            'applied_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldFarmer = $collection->farmer;
        $oldNet = (float) $collection->net_amount;

        $newFarmer = Farmer::findOrFail($validated['farmer_id']);

        // Determine rate
        if (!empty($validated['applied_rate']) && (float) $validated['applied_rate'] > 0) {
            $appliedRate = (float) $validated['applied_rate'];
            $calcRate = $appliedRate;
        } else {
            $chart = null;
            if ($newFarmer && $newFarmer->rate_chart_id) {
                $chart = RateChart::where('id', $newFarmer->rate_chart_id)->where('status', 'active')->first();
            }
            if (!$chart) {
                $chart = RateChart::where('milk_type', $validated['milk_type'])->where('status', 'active')->first()
                         ?? RateChart::where('is_default', true)->first();
            }
            $calcRate = $chart ? $chart->calculateRate($validated['fat'], $validated['snf']) : 40.0;
            $appliedRate = $newFarmer->custom_rate_override ? (float) $newFarmer->custom_rate_override : $calcRate;
        }

        $gross = round($validated['quantity_liters'] * $appliedRate, 2);
        $bonus = (float) ($validated['bonus'] ?? 0);
        $deduction = (float) ($validated['deduction'] ?? 0);
        $newNet = round($gross + $bonus - $deduction, 2);

        // Revert old net from old farmer balance
        if ($oldFarmer) {
            $oldFarmer->update(['current_balance' => (float) $oldFarmer->current_balance - $oldNet]);
        }

        // Update collection
        $collection->update([
            'farmer_id' => $newFarmer->id,
            'collection_center_id' => $validated['collection_center_id'] ?? $newFarmer->collection_center_id,
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
            'net_amount' => $newNet,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Apply new net to new farmer balance
        $newBalance = (float) $newFarmer->current_balance + $newNet;
        $newFarmer->update(['current_balance' => $newBalance]);

        // Update or create ledger record
        $ledger = FarmerLedger::where('reference_type', 'milk_collection')->where('reference_id', $collection->id)->first();
        if ($ledger) {
            $ledger->update([
                'farmer_id' => $newFarmer->id,
                'transaction_date' => $validated['collection_date'],
                'amount' => $newNet,
                'balance' => $newBalance,
                'description' => "Milk Collection {$validated['shift']} ({$validated['quantity_liters']} Ltr @ ₹{$appliedRate}/Ltr) [Updated]",
            ]);
        } else {
            FarmerLedger::create([
                'farmer_id' => $newFarmer->id,
                'transaction_date' => $validated['collection_date'],
                'type' => 'credit',
                'amount' => $newNet,
                'balance' => $newBalance,
                'reference_type' => 'milk_collection',
                'reference_id' => $collection->id,
                'description' => "Milk Collection {$validated['shift']} ({$validated['quantity_liters']} Ltr @ ₹{$appliedRate}/Ltr) [Updated]",
            ]);
        }

        AuditLog::log('Updated Milk Collection', 'MilkCollection', $collection->id);

        if ($request->ajax() || $request->wantsJson()) {
            $collection->load(['farmer', 'collectionCenter']);
            return response()->json([
                'success' => true,
                'message' => "Collection {$collection->receipt_number} updated successfully! Amount: ₹{$newNet}",
                'collection' => $collection,
                'slip_url' => route('collections.slip', $collection),
            ]);
        }

        if ($request->has('print_slip') && $request->print_slip) {
            return redirect()->route('collections.slip', $collection)->with('success', "Collection {$collection->receipt_number} updated! Amount: ₹{$newNet}");
        }

        return redirect()->route('collections.index')->with('success', "Collection {$collection->receipt_number} updated successfully! Amount: ₹{$newNet}");
    }

    /**
     * Delete a collection entry
     */
    public function destroy(MilkCollection $collection)
    {
        $farmer = $collection->farmer;
        $net = (float) $collection->net_amount;

        if ($farmer) {
            $farmer->decrement('current_balance', $net);
        }

        FarmerLedger::where('reference_type', 'milk_collection')
            ->where('reference_id', $collection->id)
            ->delete();

        $receipt = $collection->receipt_number;
        $collection->delete();

        AuditLog::log('Deleted Milk Collection', 'MilkCollection', $collection->id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Milk collection {$receipt} deleted successfully.",
            ]);
        }

        return redirect()->route('collections.index')->with('success', "Milk collection {$receipt} deleted successfully.");
    }

    /**
     * View Printable Slip
     */
    public function slip(MilkCollection $collection)
    {
        $collection->load(['farmer', 'collectionCenter', 'operator']);
        return view('collections.slip', compact('collection'));
    }
}
