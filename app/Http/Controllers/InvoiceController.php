<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\CustomerLedger;
use App\Models\AuditLog;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();
        $totalInvoiced = Invoice::sum('total_amount');
        $totalCollected = Invoice::sum('paid_amount');
        $totalPending = Invoice::sum('balance_due');
        $customers = Customer::where('status', 'active')->get();

        return view('invoices.index', compact('invoices', 'totalInvoiced', 'totalCollected', 'totalPending', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $start = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $nextInv = 'INV-' . date('Ym') . '-' . sprintf('%03d', Invoice::max('id') + 1);

        return view('invoices.create', compact('customers', 'start', 'end', 'nextInv'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'due_date' => 'required|date',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        // Gather delivered items for customer during period
        $deliveries = DailyDelivery::where('customer_id', $customer->id)
            ->whereBetween('delivery_date', [$validated['period_start'], $validated['period_end']])
            ->where('status', 'delivered')
            ->with('product')
            ->get();

        $subtotal = $deliveries->sum('total_amount');
        if ($subtotal == 0) {
            return back()->withErrors(['customer_id' => 'No delivered milk or products found for this customer in selected period.']);
        }

        $prevDue = $customer->current_balance;
        $total = $subtotal + $prevDue;
        $invNum = 'INV-' . date('Ym') . '-' . sprintf('%03d', Invoice::max('id') + 1);

        $invoice = Invoice::create([
            'invoice_number' => $invNum,
            'customer_id' => $customer->id,
            'branch_id' => $customer->branch_id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'invoice_date' => Carbon::today(),
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'previous_due' => $prevDue,
            'total_amount' => $total,
            'paid_amount' => 0.00,
            'balance_due' => $total,
            'status' => 'unpaid',
            'billing_type' => 'subscription',
        ]);

        // Group deliveries by product
        $grouped = $deliveries->groupBy('product_id');
        foreach ($grouped as $prodId => $items) {
            $prod = $items->first()->product;
            $qty = $items->sum('delivered_quantity');
            $itemTotal = $items->sum('total_amount');
            $unitPrice = $qty > 0 ? round($itemTotal / $qty, 2) : 0;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $prodId,
                'description' => "{$prod->name} ({$items->count()} Deliveries)",
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
            ]);
        }

        // Add to customer ledger & dues
        $newBalance = $customer->current_balance + $subtotal;
        $customer->update(['current_balance' => $newBalance]);

        CustomerLedger::create([
            'customer_id' => $customer->id,
            'transaction_date' => Carbon::today(),
            'type' => 'debit',
            'amount' => $subtotal,
            'balance' => $newBalance,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'description' => "Milk Invoice #{$invoice->invoice_number} ({$validated['period_start']} to {$validated['period_end']})",
        ]);

        AuditLog::log('Generated Invoice', 'Invoice', $invoice->id, ['amount' => $total]);

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} generated for ₹" . number_format($total, 2));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        return view('invoices.show', compact('invoice'));
    }
}
