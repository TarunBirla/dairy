<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Buyer;
use App\Models\BuyerPayment;
use App\Models\MilkSale;
use App\Models\AuditLog;
use Carbon\Carbon;

class BuyerController extends Controller
{
    public function __construct()
    {
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            if (!Schema::hasTable('buyers')) {
                Schema::create('buyers', function (Blueprint $table) {
                    $table->id();
                    $table->string('buyer_code')->unique();
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->string('milk_type')->default('both');
                    $table->string('cow_rate_mode')->default('fixed');
                    $table->decimal('cow_fixed_rate', 10, 2)->default(0.00);
                    $table->string('buffalo_rate_mode')->default('fixed');
                    $table->decimal('buffalo_fixed_rate', 10, 2)->default(0.00);
                    $table->text('address')->nullable();
                    $table->string('taluka')->nullable();
                    $table->string('district')->nullable();
                    $table->text('details')->nullable();
                    $table->decimal('current_balance', 12, 2)->default(0.00);
                    $table->string('status')->default('active');
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('buyer_payments')) {
                Schema::create('buyer_payments', function (Blueprint $table) {
                    $table->id();
                    $table->string('receipt_number')->unique();
                    $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                    $table->foreignId('milk_sale_id')->nullable();
                    $table->date('payment_date');
                    $table->string('type')->default('received');
                    $table->decimal('amount', 12, 2);
                    $table->string('payment_mode')->default('Cash');
                    $table->string('transaction_reference')->nullable();
                    $table->text('comment')->nullable();
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Display Buyer Listing matching Screenshot 3 (media_1791389622008.png)
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = Buyer::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('buyer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $buyers = $query->latest('id')->paginate(20)->withQueryString();

        // Generate next suggested buyer code
        $nextCode = 'BUY' . str_pad((Buyer::max('id') + 1), 3, '0', STR_PAD_LEFT);

        return view('buyers.index', compact('buyers', 'search', 'status', 'nextCode'));
    }

    /**
     * Store a new buyer matching fields in Screenshot 4 (media_1791389678004.png)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buyer_code' => 'required|string|max:50|unique:buyers,buyer_code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'milk_type' => 'required|string|in:cow,buffalo,both',
            'cow_rate_mode' => 'nullable|string',
            'cow_fixed_rate' => 'nullable|numeric|min:0',
            'buffalo_rate_mode' => 'nullable|string',
            'buffalo_fixed_rate' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'taluka' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'details' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['cow_rate_mode'] = $validated['cow_rate_mode'] ?? 'fixed';
        $validated['cow_fixed_rate'] = $validated['cow_fixed_rate'] ?? 0;
        $validated['buffalo_rate_mode'] = $validated['buffalo_rate_mode'] ?? 'fixed';
        $validated['buffalo_fixed_rate'] = $validated['buffalo_fixed_rate'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['created_by'] = Auth::id();

        $buyer = Buyer::create($validated);

        AuditLog::log("Registered Milk Buyer {$buyer->name} ({$buyer->buyer_code})", 'Buyer', $buyer->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Buyer {$buyer->name} created successfully!",
                'buyer' => $buyer,
            ]);
        }

        return redirect()->route('buyers.index')->with('success', "Buyer {$buyer->name} ({$buyer->buyer_code}) registered successfully!");
    }

    /**
     * Update an existing buyer
     */
    public function update(Request $request, Buyer $buyer)
    {
        $validated = $request->validate([
            'buyer_code' => "required|string|max:50|unique:buyers,buyer_code,{$buyer->id}",
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'milk_type' => 'required|string|in:cow,buffalo,both',
            'cow_rate_mode' => 'nullable|string',
            'cow_fixed_rate' => 'nullable|numeric|min:0',
            'buffalo_rate_mode' => 'nullable|string',
            'buffalo_fixed_rate' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'taluka' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'details' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $buyer->update($validated);

        AuditLog::log("Updated Buyer {$buyer->name} ({$buyer->buyer_code})", 'Buyer', $buyer->id);

        return redirect()->route('buyers.index')->with('success', "Buyer {$buyer->name} updated successfully!");
    }

    /**
     * Toggle buyer active/inactive status
     */
    public function toggleStatus(Buyer $buyer)
    {
        $newStatus = $buyer->status === 'active' ? 'inactive' : 'active';
        $buyer->update(['status' => $newStatus]);

        AuditLog::log("Toggled Buyer {$buyer->name} status to {$newStatus}", 'Buyer', $buyer->id);

        return redirect()->back()->with('success', "Buyer status updated to {$newStatus}.");
    }

    /**
     * Delete buyer
     */
    public function destroy(Buyer $buyer)
    {
        $name = $buyer->name;
        $buyer->delete();

        AuditLog::log("Deleted Buyer {$name}", 'Buyer', $buyer->id);

        return redirect()->route('buyers.index')->with('success', "Buyer {$name} deleted successfully.");
    }

    /**
     * Buyer Khata / Account Ledger matching Screenshot 5 (media_1791389718502.png)
     */
    public function khata(Request $request)
    {
        $buyerId = $request->get('buyer_id');
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $buyers = Buyer::orderBy('name')->get();
        $selectedBuyer = null;

        if (!empty($buyerId)) {
            $selectedBuyer = Buyer::find($buyerId);
        }

        // Fetch Transactions: both milk sales (debit) and payments (credit/debit)
        $transactions = collect();

        $salesQuery = MilkSale::with('buyer');
        $paymentsQuery = BuyerPayment::with('buyer');

        if (!empty($buyerId)) {
            $salesQuery->where('buyer_id', $buyerId);
            $paymentsQuery->where('buyer_id', $buyerId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $salesQuery->whereBetween('sale_date', [$startDate, $endDate]);
            $paymentsQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }

        if (!empty($search)) {
            $salesQuery->whereHas('buyer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('buyer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
            $paymentsQuery->whereHas('buyer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('buyer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sales = $salesQuery->get()->map(function ($s) {
            return (object) [
                'id' => 'sale-' . $s->id,
                'date' => $s->sale_date,
                'buyer' => $s->buyer,
                'type' => 'Milk Sale',
                'reference' => $s->sale_number,
                'debit' => (float) $s->total_amount, // buyer owes dairy
                'credit' => (float) $s->paid_amount,  // paid at sale
                'net_effect' => (float) ($s->total_amount - $s->paid_amount),
                'payment_mode' => $s->payment_mode ?? 'Cash',
                'comment' => $s->description ?: "Sale: {$s->quantity_liters}L {$s->milk_type} (Shift: {$s->shift})",
                'created_at' => $s->created_at,
            ];
        });

        $payments = $paymentsQuery->get()->map(function ($p) {
            $isReceived = $p->type === 'received';
            return (object) [
                'id' => 'pay-' . $p->id,
                'date' => $p->payment_date,
                'buyer' => $p->buyer,
                'type' => $isReceived ? 'Received Amount' : 'Paid Amount',
                'reference' => $p->receipt_number,
                'debit' => $isReceived ? 0.00 : (float) $p->amount,
                'credit' => $isReceived ? (float) $p->amount : 0.00,
                'net_effect' => $isReceived ? -(float) $p->amount : (float) $p->amount,
                'payment_mode' => $p->payment_mode,
                'comment' => $p->comment ?: ($isReceived ? 'Amount received from buyer' : 'Amount refunded/paid to buyer'),
                'created_at' => $p->created_at,
            ];
        });

        // Merge and sort chronologically
        $transactions = $sales->concat($payments)->sortByDesc('date')->values();

        // Calculate Totals
        $totalReceivable = $selectedBuyer 
            ? (float) $selectedBuyer->current_balance 
            : (float) Buyer::sum('current_balance');

        $totalSalesAmount = (float) $sales->sum('debit');
        $totalReceivedAmount = (float) $sales->sum('credit') + (float) $payments->where('type', 'Received Amount')->sum('credit');

        return view('buyers.khata', compact(
            'buyers',
            'selectedBuyer',
            'transactions',
            'buyerId',
            'search',
            'startDate',
            'endDate',
            'totalReceivable',
            'totalSalesAmount',
            'totalReceivedAmount'
        ));
    }

    /**
     * Store Received / Paid Amount matching Screenshot 5 Modal
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'payment_date' => 'required|date',
            'type' => 'required|in:received,paid',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $receiptNum = ($validated['type'] === 'received' ? 'REC-' : 'PAY-') . str_pad((BuyerPayment::max('id') + 101), 4, '0', STR_PAD_LEFT);

        $payment = BuyerPayment::create([
            'receipt_number' => $receiptNum,
            'buyer_id' => $validated['buyer_id'],
            'payment_date' => $validated['payment_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_mode' => $validated['payment_mode'],
            'comment' => $validated['comment'] ?? null,
            'created_by' => Auth::id(),
        ]);

        // Recalculate Buyer Balance
        $buyer = Buyer::find($validated['buyer_id']);
        $buyer->recalculateBalance();

        AuditLog::log("Recorded {$payment->type} payment #{$receiptNum} of ₹{$payment->amount} for Buyer {$buyer->name}", 'BuyerPayment', $payment->id);

        return redirect()->back()->with('success', "Payment #{$receiptNum} of ₹" . number_format($payment->amount, 2) . " saved successfully!");
    }

    /**
     * Buyer Bill / Statement View
     */
    public function bill(Request $request, Buyer $buyer)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $sales = MilkSale::where('buyer_id', $buyer->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->latest('sale_date')
            ->get();

        $payments = BuyerPayment::where('buyer_id', $buyer->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->latest('payment_date')
            ->get();

        $totalMilkLiters = $sales->sum('quantity_liters');
        $totalMilkAmount = $sales->sum('total_amount');
        $totalPaidOnSales = $sales->sum('paid_amount');
        $totalPaymentsReceived = $payments->where('type', 'received')->sum('amount');
        $netReceivable = $buyer->current_balance;

        return view('buyers.bill', compact(
            'buyer',
            'sales',
            'payments',
            'startDate',
            'endDate',
            'totalMilkLiters',
            'totalMilkAmount',
            'totalPaidOnSales',
            'totalPaymentsReceived',
            'netReceivable'
        ));
    }
}
