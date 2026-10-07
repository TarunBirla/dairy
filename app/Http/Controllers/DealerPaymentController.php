<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Dealer;
use App\Models\DealerPayment;
use App\Models\ProductPurchase;
use App\Models\AuditLog;
use Carbon\Carbon;

class DealerPaymentController extends Controller
{
    public function __construct()
    {
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('dealer_payments')) {
                Schema::create('dealer_payments', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
                    $table->string('dealer_code')->nullable();
                    $table->string('dealer_name');
                    $table->date('payment_date');
                    $table->decimal('dues_amount', 12, 2)->default(0.00);
                    $table->decimal('pay_amount', 12, 2)->default(0.00);
                    $table->decimal('remaining_dues', 12, 2)->default(0.00);
                    $table->string('payment_mode')->default('UPI');
                    $table->text('comment')->nullable();
                    $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Display Dealer Transaction List (matching reference image media_1791384054293.png).
     */
    public function index(Request $request)
    {
        $dealersQuery = Dealer::with(['purchases', 'payments']);

        // Filter by Dealer Name / Code
        if ($request->filled('dealer_name')) {
            $search = trim($request->dealer_name);
            $dealersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $dealers = $dealersQuery->orderBy('name', 'asc')->get();

        // Calculate Dealer Summary metrics for the table
        $dealerTransactions = $dealers->map(function ($dealer) use ($request) {
            $purchasesQuery = $dealer->purchases();
            $paymentsQuery = $dealer->payments();

            if ($request->filled('date')) {
                $purchasesQuery->whereDate('purchase_date', '<=', $request->date);
                $paymentsQuery->whereDate('payment_date', '<=', $request->date);
            }

            $totalAmount = (float) $purchasesQuery->sum('total_amount');
            $purchasePaid = (float) $purchasesQuery->sum('paid_amount');
            $directPayments = (float) $paymentsQuery->sum('pay_amount');
            $payAmount = $purchasePaid + $directPayments;
            $duesAmount = max(0, $totalAmount - $payAmount);

            return (object) [
                'dealer_id' => $dealer->id,
                'dealer_code' => $dealer->code,
                'dealer_name' => $dealer->name,
                'phone' => $dealer->phone,
                'total_amount' => $totalAmount,
                'pay_amount' => $payAmount,
                'dues_amount' => $duesAmount,
            ];
        });

        // Filter out zero-transaction dealers if desired, or keep all dealers
        $allDealers = Dealer::where('status', 'active')->orderBy('name')->get();

        // Recent Payments Log
        $recentPaymentsQuery = DealerPayment::with(['dealer', 'recorder'])->latest('payment_date');
        if ($request->filled('date')) {
            $recentPaymentsQuery->whereDate('payment_date', $request->date);
        }
        if ($request->filled('dealer_name')) {
            $recentPaymentsQuery->where('dealer_name', 'like', "%{$request->dealer_name}%");
        }
        $recentPayments = $recentPaymentsQuery->take(20)->get();

        return view('dealers.payments', compact('dealerTransactions', 'allDealers', 'recentPayments'));
    }

    /**
     * Record dealer pay entry (matching reference modal media_1791384063890.png).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dealer_id' => 'required|exists:dealers,id',
            'payment_date' => 'required|date',
            'pay_amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|in:UPI,Cash,Bank Transfer,Cheque,NEFT/RTGS',
            'comment' => 'nullable|string|max:500',
        ]);

        $dealer = Dealer::findOrFail($validated['dealer_id']);

        // Calculate live dues before payment
        $currentDues = $dealer->dues_amount;
        $remainingDues = max(0, $currentDues - $validated['pay_amount']);

        $payment = DealerPayment::create([
            'dealer_id' => $dealer->id,
            'dealer_code' => $dealer->code,
            'dealer_name' => $dealer->name,
            'payment_date' => $validated['payment_date'],
            'dues_amount' => $currentDues,
            'pay_amount' => $validated['pay_amount'],
            'remaining_dues' => $remainingDues,
            'payment_mode' => $validated['payment_mode'],
            'comment' => $validated['comment'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        AuditLog::log('Recorded Dealer Payment', 'DealerPayment', $payment->id, [
            'dealer' => $dealer->name,
            'pay_amount' => $payment->pay_amount,
            'mode' => $payment->payment_mode,
        ]);

        return redirect()->route('dealer-payments.index')->with('success', "Payment of ₹ " . number_format($payment->pay_amount, 2) . " to {$dealer->name} recorded successfully!");
    }

    /**
     * AJAX endpoint to return live dues for modal auto-population.
     */
    public function getDues(Dealer $dealer)
    {
        return response()->json([
            'dealer_id' => $dealer->id,
            'dealer_name' => $dealer->name,
            'dealer_code' => $dealer->code,
            'total_purchased' => $dealer->total_purchased,
            'total_paid' => $dealer->total_paid,
            'dues_amount' => $dealer->dues_amount,
        ]);
    }

    /**
     * AJAX endpoint to view transaction details of a dealer.
     */
    public function transactions(Dealer $dealer)
    {
        $purchases = ProductPurchase::where('dealer_id', $dealer->id)->latest('purchase_date')->get();
        $payments = DealerPayment::where('dealer_id', $dealer->id)->latest('payment_date')->get();

        return response()->json([
            'dealer' => $dealer,
            'total_purchased' => $dealer->total_purchased,
            'total_paid' => $dealer->total_paid,
            'dues_amount' => $dealer->dues_amount,
            'purchases' => $purchases,
            'payments' => $payments,
        ]);
    }

    /**
     * Printable payment receipt slip.
     */
    public function printSlip(DealerPayment $dealerPayment)
    {
        $dealerPayment->load(['dealer', 'recorder']);
        return view('dealers.print_payment', compact('dealerPayment'));
    }
}
