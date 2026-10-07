@extends('layouts.app')

@section('title', 'Customer - ' . $customer->name)
@section('breadcrumb', 'Customer Profile')
@section('header_title', $customer->customer_code . ' - ' . $customer->name)

@section('header_action')
    <div class="flex gap-2">
        <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs rounded-lg border border-emerald-200 transition">
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
            <span>Add Subscription</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ tab: 'subs', payModal: false }">

    <!-- Profile & Ledger Overview -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-800 font-extrabold flex items-center justify-center text-xl shadow-xs">
                {{ strtoupper(substr($customer->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900">{{ $customer->name }}</h2>
                    <span class="font-mono text-xs px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-700">{{ $customer->customer_code }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase capitalize bg-slate-100 text-slate-700">
                        {{ $customer->category }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                    <span>Phone: <b>{{ $customer->phone }}</b></span>
                    <span>Route: <b>{{ $customer->route ? $customer->route->name : 'Unassigned' }}</b></span>
                    <span>Locality: <b>{{ $customer->locality }}</b></span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">{{ $customer->address }}</p>
            </div>
        </div>

        <!-- Ledger Balance & Quick Payment -->
        <div class="bg-amber-50/70 border border-amber-200/80 p-4 rounded-xl flex items-center justify-between sm:justify-end gap-6">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">Outstanding Balance Due</span>
                <span class="text-2xl font-black {{ $customer->current_balance > 0 ? 'text-amber-700' : 'text-slate-800' }}">
                    ₹ {{ number_format($customer->current_balance, 2) }}
                </span>
                <span class="text-[10px] text-slate-400 block">Credit Limit: ₹ {{ number_format($customer->credit_limit, 2) }}</span>
            </div>
            <div>
                <button type="button" @click="payModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    + Collect Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Bottle Tracking Snapshot -->
    @if($customer->bottleTracking)
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="wine" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Glass Bottles Deposit Balance</h4>
                    <p class="text-[11px] text-slate-400">Customer currently holds <b>{{ $customer->bottleTracking->balance_bottles }} Bottles</b> (Issued: {{ $customer->bottleTracking->issued_count }}, Returned: {{ $customer->bottleTracking->returned_count }})</p>
                </div>
            </div>
            <a href="{{ route('inventory.bottles') }}" class="text-xs text-teal-700 font-semibold hover:underline">Manage Bottles &rarr;</a>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 space-x-6 text-xs font-semibold">
        <button @click="tab = 'subs'" :class="tab === 'subs' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Active Subscriptions ({{ $customer->subscriptions->count() }})
        </button>
        <button @click="tab = 'deliveries'" :class="tab === 'deliveries' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Delivery History ({{ $recentDeliveries->count() }})
        </button>
        <button @click="tab = 'ledger'" :class="tab === 'ledger' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Ledger & Dues ({{ $ledgers->count() }})
        </button>
        <button @click="tab = 'invoices'" :class="tab === 'invoices' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Invoices & Bills ({{ $customer->invoices->count() }})
        </button>
    </div>

    <!-- TAB 1: Subscriptions -->
    <div x-show="tab === 'subs'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800">Recurring Milk Delivery Plans</h4>
            <a href="{{ route('subscriptions.create', ['customer_id' => $customer->id]) }}" class="text-xs text-emerald-600 font-bold hover:underline">+ New Plan</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($customer->subscriptions as $sub)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                            <i data-lucide="milk" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-slate-900">{{ $sub->product->name }}</h5>
                            <p class="text-xs text-slate-500">
                                <b>{{ $sub->quantity }} {{ $sub->product->unit }}</b> • {{ ucfirst($sub->frequency) }} • {{ ucfirst($sub->shift) }} shift @ ₹{{ $sub->unit_price }}/{{ $sub->product->unit }}
                            </p>
                            @if($sub->status === 'paused' && $sub->pause_from)
                                <span class="text-[10px] text-amber-700 font-semibold block mt-0.5">
                                    Vacation pause: {{ $sub->pause_from->format('d M') }} to {{ $sub->pause_until->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $sub->status }}
                        </span>
                        <form action="{{ route('subscriptions.toggle-pause', $sub) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1 text-xs border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg font-semibold">
                                {{ $sub->status === 'active' ? 'Pause Vacation' : 'Resume Plan' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    No active subscriptions. Click above to add a milk delivery plan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: Deliveries -->
    <div x-show="tab === 'deliveries'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Delivery Date</th>
                        <th class="py-2.5 px-4">Product</th>
                        <th class="py-2.5 px-4">Qty</th>
                        <th class="py-2.5 px-4">Amount (₹)</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4">Cash Recv</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentDeliveries as $d)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $d->delivery_date->format('d M Y') }} ({{ ucfirst($d->shift) }})</td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $d->product ? $d->product->name : 'Milk' }}</td>
                            <td class="py-3 px-4 font-bold">{{ $d->quantity }}</td>
                            <td class="py-3 px-4">₹ {{ number_format($d->total_amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $d->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold">{{ $d->cash_collected > 0 ? '₹ ' . number_format($d->cash_collected, 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No delivery logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: Ledger -->
    <div x-show="tab === 'ledger'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Description</th>
                        <th class="py-2.5 px-4 text-right">Debit / Billed (₹)</th>
                        <th class="py-2.5 px-4 text-right">Credit / Paid (₹)</th>
                        <th class="py-2.5 px-4 text-right">Balance Due (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledgers as $l)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $l->transaction_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">{{ $l->description }}</td>
                            <td class="py-3 px-4 text-right font-bold text-amber-700">
                                {{ $l->type === 'debit' ? '₹ ' . number_format($l->amount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                {{ $l->type === 'credit' ? '₹ ' . number_format($l->amount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">
                                ₹ {{ number_format($l->balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No customer ledger entries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: Invoices -->
    <div x-show="tab === 'invoices'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Invoice #</th>
                        <th class="py-2.5 px-4">Period</th>
                        <th class="py-2.5 px-4">Total Amount</th>
                        <th class="py-2.5 px-4">Paid</th>
                        <th class="py-2.5 px-4">Balance</th>
                        <th class="py-2.5 px-4">Status</th>
                        <th class="py-2.5 px-4 text-center">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customer->invoices as $inv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4">{{ $inv->period_start->format('d M') }} - {{ $inv->period_end->format('d M Y') }}</td>
                            <td class="py-3 px-4 font-bold">₹ {{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-3 px-4 font-semibold text-emerald-700">₹ {{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="py-3 px-4 font-bold text-amber-700">₹ {{ number_format($inv->balance_due, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $inv->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('invoices.show', $inv) }}" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">Invoice</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No invoices generated for this customer yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Collect Payment Modal -->
    <div x-show="payModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="payModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Collect Customer Payment</h3>
                <button @click="payModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) *</label>
                    <input type="number" step="1" name="amount" required value="{{ $customer->current_balance }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Mode *</label>
                    <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Debit / Credit Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ref / UTR Number</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Cash receipt" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="payModal = false" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
