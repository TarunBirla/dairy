<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Farmer;
use App\Models\FarmerWallet;
use App\Models\FarmerWalletTransaction;
use App\Services\FarmerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FarmerWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all farmer deduction wallets with search, filters, and KPIs.
     */
    public function index(Request $request)
    {
        if (Gate::denies('wallet.view')) {
            abort(403, 'Unauthorized access to farmer deduction wallets.');
        }

        FarmerWalletService::ensureSchemaMigrated();

        $search = trim($request->get('search', ''));
        $category = trim($request->get('category', ''));
        $perPage = (int) $request->get('per_page', 25);

        $query = Farmer::where('status', 'active')->with(['wallet', 'collectionCenter']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('farmer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $farmers = $query->orderBy('farmer_code')->paginate($perPage)->withQueryString();

        // Ensure wallet exists for displayed farmers & clean up any duplicates
        foreach ($farmers as $f) {
            FarmerWalletService::cleanupDuplicateAdvanceTransactions($f);
            if (!$f->wallet) {
                $f->setRelation('wallet', FarmerWalletService::getOrCreateWallet($f));
            } else {
                $latestInv = \App\Models\FarmerInvoice::where('farmer_id', $f->id)
                    ->where('status', '!=', 'pending')
                    ->latest('period_end')
                    ->latest('id')
                    ->first();
                if ($latestInv && $latestInv->closing_wallet_balance !== null && (float) $latestInv->closing_wallet_balance > 0) {
                    if (abs((float) $f->wallet->balance - (float) $latestInv->closing_wallet_balance) > 0.05) {
                        $f->wallet->update(['balance' => (float) $latestInv->closing_wallet_balance]);
                    }
                } else {
                    $f->wallet->recalculateBalance();
                }
            }
        }

        // Metrics
        $totalWalletsCount = Farmer::where('status', 'active')->count();
        $totalOutstanding = (float) FarmerWallet::sum('balance');
        $farmersWithDues = FarmerWallet::where('balance', '>', 0)->count();

        // Standard categories
        $availableCategories = [
            'Cattle Feed',
            'Advance / Loan',
            'Doctor Fee',
            'Stationary',
            'Management Charges',
            'Medicine',
            'Store Purchase',
            'Equipment',
            'General Adjustment',
            'Other',
        ];

        return view('farmers.wallets.index', compact(
            'farmers',
            'totalWalletsCount',
            'totalOutstanding',
            'farmersWithDues',
            'availableCategories',
            'search',
            'category'
        ));
    }

    /**
     * Dedicated Farmer Wallet Dashboard with category breakdown & transaction ledger.
     */
    public function show(Farmer $farmer)
    {
        if (Gate::denies('wallet.view', $farmer)) {
            abort(403, 'Unauthorized access to farmer deduction wallet.');
        }

        FarmerWalletService::ensureSchemaMigrated();
        $summary = FarmerWalletService::getWalletSummary($farmer);

        $wallet = $summary['wallet'];
        $transactions = FarmerWalletTransaction::where('wallet_id', $wallet->id)
            ->with(['creator', 'invoice'])
            ->orderBy('id', 'desc')
            ->paginate(50);

        $availableCategories = [
            'Cattle Feed',
            'Advance / Loan',
            'Doctor Fee',
            'Stationary',
            'Management Charges',
            'Medicine',
            'Store Purchase',
            'Equipment',
            'General Adjustment',
            'Other',
        ];

        return view('farmers.wallets.show', array_merge($summary, [
            'farmer' => $farmer,
            'transactions' => $transactions,
            'availableCategories' => $availableCategories,
        ]));
    }

    /**
     * Add a new outstanding deduction to a farmer's wallet.
     */
    public function addDeduction(Request $request, Farmer $farmer)
    {
        if (Gate::denies('wallet.add')) {
            abort(403, 'Unauthorized to add deduction balance.');
        }

        $validated = $request->validate([
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $tx = FarmerWalletService::addDeduction(
                $farmer,
                $validated['category'],
                (float) $validated['amount'],
                $validated['remarks'] ?? '',
                Auth::id()
            );

            return back()->with('success', "Added ₹" . number_format($tx->amount, 2) . " {$tx->category} deduction to {$farmer->name}'s wallet. New balance: ₹" . number_format($tx->balance_after, 2));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to add deduction: ' . $e->getMessage());
        }
    }

    /**
     * Manual balance adjustment (Credit or Debit) with remarks.
     */
    public function adjust(Request $request, Farmer $farmer)
    {
        if (Gate::denies('wallet.edit')) {
            abort(403, 'Unauthorized to adjust wallet balance.');
        }

        $validated = $request->validate([
            'adjustment_type' => 'required|in:credit,debit',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'required|string|min:3|max:500',
        ]);

        try {
            $tx = FarmerWalletService::manualAdjustment(
                $farmer,
                $validated['adjustment_type'],
                $validated['category'],
                (float) $validated['amount'],
                $validated['remarks'],
                Auth::id()
            );

            $actionText = $tx->transaction_type === 'debit' ? 'Reduced/Waived' : 'Added';
            return back()->with('success', "{$actionText} ₹" . number_format($tx->amount, 2) . " on {$farmer->name}'s wallet. New balance: ₹" . number_format($tx->balance_after, 2));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to adjust wallet balance: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate wallet balance from transaction history.
     */
    public function recalculate(Farmer $farmer)
    {
        if (Gate::denies('wallet.edit')) {
            abort(403, 'Unauthorized.');
        }

        $wallet = FarmerWalletService::getOrCreateWallet($farmer);
        $newBal = $wallet->recalculateBalance();

        return back()->with('success', "Wallet balance for {$farmer->name} re-calculated successfully: ₹" . number_format($newBal, 2));
    }
}
