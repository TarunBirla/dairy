<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['customer', 'invoice', 'collector'])->latest()->paginate(20);
        $totalCollected = Payment::sum('amount');
        $customers = Customer::where('status', 'active')->get();

        return view('payments.index', compact('payments', 'totalCollected', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,upi,card,bank_transfer',
            'transaction_reference' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $amount = (float)$validated['amount'];
        $payNum = 'PAY-' . date('Ymd') . '-' . sprintf('%03d', Payment::whereDate('created_at', Carbon::today())->count() + 1);

        $payment = Payment::create([
            'payment_number' => $payNum,
            'customer_id' => $customer->id,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'amount' => $amount,
            'payment_mode' => $validated['payment_mode'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'payment_date' => $validated['payment_date'],
            'collected_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // If tied to an invoice, update invoice paid amount and status
        if (!empty($validated['invoice_id'])) {
            $invoice = Invoice::find($validated['invoice_id']);
            $newPaid = $invoice->paid_amount + $amount;
            $balanceDue = max(0, $invoice->total_amount - $newPaid);
            $status = ($balanceDue <= 0) ? 'paid' : 'partial';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $balanceDue,
                'status' => $status,
            ]);
        }

        // Adjust customer dues & create ledger entry
        $newCustBalance = max(0, $customer->current_balance - $amount);
        $customer->update(['current_balance' => $newCustBalance]);

        CustomerLedger::create([
            'customer_id' => $customer->id,
            'transaction_date' => $validated['payment_date'],
            'type' => 'credit',
            'amount' => $amount,
            'balance' => $newCustBalance,
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'description' => "Payment received ({$validated['payment_mode']}) ref: {$payNum}",
        ]);

        AuditLog::log('Received Customer Payment', 'Payment', $payment->id, ['amount' => $amount]);

        return back()->with('success', "Payment of ₹" . number_format($amount, 2) . " received successfully! Receipt #{$payNum}");
    }
}
