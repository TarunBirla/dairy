@extends('layouts.app')

@section('title', 'Farmer Passbook - ' . $farmer->name)
@section('breadcrumb', 'Farmer Profile')
@section('header_title', $farmer->farmer_code . ' - ' . $farmer->name)

@section('header_action')
    <a href="{{ route('settlements.create', ['farmer_id' => $farmer->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="receipt" class="w-4 h-4"></i>
        <span>Settle Dues</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    tab: '{{ request('tab', 'collections') }}',
    advanceModal: false,
    purchaseModal: false,
    purchaseFilter: 'all',
    purchaseSearch: '',
    selectedProductId: '',
    purchaseUnit: 'Bag',
    purchaseQty: 1,
    purchaseRate: 0,
    purchaseIsPaid: '0',
    purchasePaymentMode: 'Ledger',
    get purchaseTotal() {
        return ((parseFloat(this.purchaseQty) || 0) * (parseFloat(this.purchaseRate) || 0)).toFixed(2);
    },
    onSelectProduct(e) {
        const opt = e.target.selectedOptions[0];
        if (opt && opt.value) {
            this.purchaseRate = parseFloat(opt.dataset.price || 0);
            this.purchaseUnit = opt.dataset.unit || 'Bag';
        }
    }
}">

    <!-- Top Profile & Balance Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center space-x-4">
                <img src="{{ $farmer->photo_url }}" alt="{{ $farmer->name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-xs shrink-0">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $farmer->name }}</h2>
                        @if($farmer->name_hi)
                            <span class="text-base font-semibold text-slate-500">({{ $farmer->name_hi }})</span>
                        @endif
                        <span class="font-mono text-xs px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $farmer->farmer_code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $farmer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $farmer->status }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                        <span>Phone: <b>{{ $farmer->phone ?? '—' }}</b></span>
                        <span>Village: <b>{{ $farmer->village ?? '—' }}</b></span>
                        <span>Route: <b>{{ $farmer->route ? $farmer->route->name : '—' }}</b></span>
                        <span>Vehicle: <b>{{ $farmer->vehicle ? ucfirst($farmer->vehicle) : '—' }}</b></span>
                        <span>Milk: <b class="capitalize">{{ $farmer->animal_type }}</b></span>
                        @if($farmer->cow_milk_rate)
                            <span>Cow Rate: <b>₹ {{ $farmer->cow_milk_rate }}/L</b></span>
                        @endif
                        @if($farmer->buffalo_milk_rate)
                            <span>Buffalo Rate: <b>₹ {{ $farmer->buffalo_milk_rate }}/L</b></span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ledger Balance & Quick Action -->
            <div class="bg-emerald-50/60 border border-emerald-200/80 p-4 rounded-xl flex items-center justify-between sm:justify-end gap-6">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Current Ledger Balance</span>
                    <span class="text-2xl font-black text-slate-900">₹ {{ number_format($farmer->current_balance, 2) }}</span>
                    <span class="text-[10px] text-emerald-700 font-semibold block">Payable by Dairy</span>
                </div>
                <div class="flex flex-col gap-1.5">
                    <button type="button" @click="advanceModal = true" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
                        + Issue Advance
                    </button>
                    <button type="button" @click="tab = 'purchases'; purchaseModal = true" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                        + Add Purchase
                    </button>
                </div>
            </div>
        </div>

        <!-- Bank & Financial Ledger Funds Strip -->
        <div class="pt-3 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-xs">
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Bank Details</span>
                <span class="font-semibold text-slate-800 truncate block">{{ $farmer->bank_name ?: '—' }}</span>
                <span class="text-[10px] font-mono text-slate-500 block">{{ $farmer->account_number ? 'A/C: ' . $farmer->account_number : '' }}</span>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Anamat (Deposit)</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">₹ {{ number_format($farmer->anamat ?? 0, 2) }}</span>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Building Fund</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">₹ {{ number_format($farmer->building_fund ?? 0, 2) }}</span>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Installment</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">₹ {{ number_format($farmer->installment ?? 0, 2) }}</span>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/70">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Etc Deduction</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">₹ {{ number_format($farmer->etc_amount ?? 0, 2) }}</span>
            </div>
            <div class="bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-200/70">
                <span class="text-[10px] font-bold text-emerald-600 uppercase block">Grant / Subsidy</span>
                <span class="font-bold text-emerald-800 text-sm mt-0.5 block">₹ {{ number_format($farmer->grant_amount ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap border-b border-slate-200 gap-x-6 gap-y-2 text-xs font-semibold">
        <button @click="tab = 'collections'" :class="tab === 'collections' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition cursor-pointer">
            Milk Supply History ({{ $collections->count() }})
        </button>
        <button @click="tab = 'ledger'" :class="tab === 'ledger' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition cursor-pointer">
            Passbook Ledger ({{ $ledgers->count() }})
        </button>
        <button @click="tab = 'advances'" :class="tab === 'advances' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition cursor-pointer">
            Advances & Loans ({{ $advances->count() }})
        </button>
        <button @click="tab = 'purchases'" :class="tab === 'purchases' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition flex items-center gap-1.5 cursor-pointer">
            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
            <span>Farmer Purchases ({{ $farmerPurchases->count() }})</span>
            @if($totalPurchaseUnpaid > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700 border border-rose-200">
                    Unpaid: ₹{{ number_format($totalPurchaseUnpaid, 0) }}
                </span>
            @endif
        </button>
        <button @click="tab = 'passbook'" :class="tab === 'passbook' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition flex items-center gap-1.5 cursor-pointer">
            <i data-lucide="book-open-check" class="w-3.5 h-3.5"></i>
            <span>All Passbook Detail ({{ count($passbookEntries) }})</span>
        </button>
        <button @click="tab = 'mobile_dairy'" :class="tab === 'mobile_dairy' ? 'border-b-2 border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-lg' : 'text-slate-500 hover:text-slate-700'" class="pb-3 px-2.5 transition flex items-center gap-1.5 cursor-pointer font-bold text-emerald-800">
            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-emerald-600"></i>
            <span>Mobile Dairy (8-in-1 Suite)</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-600 text-white font-extrabold tracking-wider uppercase">Pro</span>
        </button>
    </div>

    <!-- TAB 1: Milk Collections -->
    <div x-show="tab === 'collections'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800">Recent Procurement Entries</h4>
            <span class="text-xs text-slate-500">Lifetime Liters: <b>{{ number_format($totalMilkLiters, 1) }} L</b> • Total: <b>₹ {{ number_format($totalGrossEarned, 2) }}</b></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Receipt</th>
                        <th class="py-2.5 px-4">Date & Shift</th>
                        <th class="py-2.5 px-4">Liters</th>
                        <th class="py-2.5 px-4">FAT / SNF</th>
                        <th class="py-2.5 px-4">Rate (₹)</th>
                        <th class="py-2.5 px-4 text-right">Net Amount</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono font-bold">{{ $col->receipt_number }}</td>
                            <td class="py-3 px-4">
                                {{ $col->collection_date->format('d M Y') }}
                                <span class="capitalize text-[10px] text-slate-400 font-semibold block">{{ $col->shift }}</span>
                            </td>
                            <td class="py-3 px-4 font-bold">{{ $col->quantity_liters }} L</td>
                            <td class="py-3 px-4">{{ $col->fat }}% / {{ $col->snf }}%</td>
                            <td class="py-3 px-4">₹ {{ number_format($col->applied_rate, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700">₹ {{ number_format($col->net_amount, 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $col->payment_status === 'settled' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($col->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No milk supply records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: Financial Ledger -->
    <div x-show="tab === 'ledger'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h4 class="text-xs font-bold text-slate-800">Passbook Ledger Statement</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Description</th>
                        <th class="py-2.5 px-4 text-right">Credit (₹)</th>
                        <th class="py-2.5 px-4 text-right">Debit (₹)</th>
                        <th class="py-2.5 px-4 text-right">Running Balance (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledgers as $l)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $l->transaction_date->format('d M Y') }}</td>
                            <td class="py-3 px-4">{{ $l->description }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600">
                                {{ $l->type === 'credit' ? '₹ ' . number_format($l->amount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-rose-600">
                                {{ $l->type === 'debit' ? '₹ ' . number_format($l->amount, 2) : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">
                                ₹ {{ number_format($l->balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No ledger transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: Advances -->
    <div x-show="tab === 'advances'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800">Issued Advances & Repayments</h4>
            <button type="button" @click="advanceModal = true" class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg">+ Issue Advance</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-2.5 px-4">Date</th>
                        <th class="py-2.5 px-4">Amount</th>
                        <th class="py-2.5 px-4">Purpose</th>
                        <th class="py-2.5 px-4">Deducted</th>
                        <th class="py-2.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($advances as $adv)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $adv->advance_date->format('d M Y') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">₹ {{ number_format($adv->amount, 2) }}</td>
                            <td class="py-3 px-4">{{ $adv->purpose ?? 'General' }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-600">₹ {{ number_format($adv->deducted_amount, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize
                                    {{ $adv->status === 'recovered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ str_replace('_', ' ', $adv->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No advances issued.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: Farmer Purchases (All / Paid / Unpaid) -->
    <div x-show="tab === 'purchases'" x-cloak class="space-y-4">
        <!-- Summary KPI Strip -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Total Purchases</span>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-xl font-black text-slate-900">{{ $farmerPurchases->count() }} Bills</span>
                    <span class="text-xs font-bold text-slate-500">Qty: {{ number_format($totalPurchaseQty, 2) }}</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Total Purchase Value</span>
                <span class="text-xl font-black text-slate-900 mt-1 block">₹ {{ number_format($totalPurchaseAmount, 2) }}</span>
            </div>
            <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-700 uppercase">Total Paid</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">{{ $paidPurchasesCount }} Paid</span>
                </div>
                <span class="text-xl font-black text-emerald-800 mt-1 block">₹ {{ number_format($totalPurchasePaid, 2) }}</span>
            </div>
            <div class="bg-rose-50/60 p-4 rounded-2xl border border-rose-200/80 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-rose-700 uppercase">Unpaid / Balance Due</span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">{{ $unpaidPurchasesCount }} Unpaid</span>
                </div>
                <span class="text-xl font-black text-rose-700 mt-1 block">₹ {{ number_format($totalPurchaseUnpaid, 2) }}</span>
            </div>
        </div>

        <!-- Purchases Listing Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-xs font-bold text-slate-800 mr-2">Farmer Product Purchases (Cattle Feed / Ghee / Store Items)</h4>
                    <!-- Filter Pills: All / Paid / Unpaid -->
                    <div class="inline-flex rounded-lg bg-slate-100 p-0.5 border border-slate-200/70 text-[11px] font-bold">
                        <button type="button" @click="purchaseFilter = 'all'"
                                :class="purchaseFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition cursor-pointer">
                            All ({{ $farmerPurchases->count() }})
                        </button>
                        <button type="button" @click="purchaseFilter = 'paid'"
                                :class="purchaseFilter === 'paid' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition cursor-pointer">
                            Paid ({{ $paidPurchasesCount }})
                        </button>
                        <button type="button" @click="purchaseFilter = 'unpaid'"
                                :class="purchaseFilter === 'unpaid' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition cursor-pointer">
                            Unpaid ({{ $unpaidPurchasesCount }})
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" x-model="purchaseSearch" placeholder="Search bill or product..."
                               class="w-48 pl-7 pr-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2"></i>
                    </div>
                    <button type="button" @click="purchaseModal = true"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>New Purchase</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-4">Bill #</th>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Product / Item</th>
                            <th class="py-2.5 px-4 text-right">Qty</th>
                            <th class="py-2.5 px-4 text-right">Rate (₹)</th>
                            <th class="py-2.5 px-4 text-right">Total (₹)</th>
                            <th class="py-2.5 px-4 text-right">Paid (₹)</th>
                            <th class="py-2.5 px-4 text-right">Balance Due (₹)</th>
                            <th class="py-2.5 px-4 text-center">Payment Mode</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                            <th class="py-2.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($farmerPurchases as $sale)
                            @php
                                $isFullyPaid = $sale->is_paid && (float) $sale->remaining_amount <= 0;
                                $isPartial = (float) $sale->paid_amount > 0 && (float) $sale->remaining_amount > 0;
                                $filterType = $isFullyPaid ? 'paid' : 'unpaid';
                                $searchBlob = strtolower($sale->sale_number . ' ' . $sale->product_name . ' ' . $sale->payment_mode . ' ' . ($sale->remarks ?? ''));
                            @endphp
                            <tr x-show="(purchaseFilter === 'all' || purchaseFilter === '{{ $filterType }}') && (!purchaseSearch || {{ json_encode($searchBlob) }}.includes(purchaseSearch.toLowerCase()))"
                                class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $sale->sale_number }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $sale->sale_date ? $sale->sale_date->format('d M Y') : '—' }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $sale->product_name }}</span>
                                    @if($sale->remarks)
                                        <span class="text-[10px] text-slate-400 block">{{ $sale->remarks }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800">
                                    {{ number_format((float) $sale->quantity, 2) }} <span class="text-[10px] font-semibold text-slate-400">{{ $sale->unit }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono">₹ {{ number_format((float) $sale->rate, 2) }}</td>
                                <td class="py-3 px-4 text-right font-extrabold text-slate-900">₹ {{ number_format((float) $sale->total_amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">₹ {{ number_format((float) $sale->paid_amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ (float) $sale->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ₹ {{ number_format((float) $sale->remaining_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px] border border-slate-200">
                                        {{ $sale->payment_mode ?? 'Ledger' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($isFullyPaid)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Paid
                                        </span>
                                    @elseif($isPartial)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Partial Due
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Unpaid
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        @if(!$isFullyPaid)
                                            <form action="{{ route('product-sales.mark-paid', $sale->id) }}" method="POST" class="inline" onsubmit="return confirm('Mark Bill #{{ $sale->sale_number }} (₹{{ number_format((float) $sale->remaining_amount, 2) }}) as Paid?');">
                                                @csrf
                                                <input type="hidden" name="farmer_id" value="{{ $farmer->id }}">
                                                <button type="submit" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold shadow-2xs transition cursor-pointer" title="Mark Paid">
                                                    Mark Paid
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('product-sales.print', $sale->id) }}" target="_blank"
                                           class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Print Receipt">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-10 text-center text-slate-400">
                                    No product purchases recorded for this farmer yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($farmerPurchases->count() > 0)
                        <tfoot class="bg-slate-50/90 border-t border-slate-200 text-xs font-extrabold text-slate-900">
                            <tr>
                                <td colspan="3" class="py-2.5 px-4 text-right uppercase text-[10px] text-slate-500">Grand Total</td>
                                <td class="py-2.5 px-4 text-right">{{ number_format($totalPurchaseQty, 2) }}</td>
                                <td class="py-2.5 px-4"></td>
                                <td class="py-2.5 px-4 text-right">₹ {{ number_format($totalPurchaseAmount, 2) }}</td>
                                <td class="py-2.5 px-4 text-right text-emerald-700">₹ {{ number_format($totalPurchasePaid, 2) }}</td>
                                <td class="py-2.5 px-4 text-right text-rose-700">₹ {{ number_format($totalPurchaseUnpaid, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: All Passbook Detail (Month-wise Full Statement + Date Filter + Print & PDF) -->
    <div x-show="tab === 'passbook'" x-cloak class="space-y-4">
        <!-- Filter & Export Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            <form action="{{ route('farmers.show', $farmer) }}" method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="passbook">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">From Date</label>
                    <input type="date" name="passbook_from" value="{{ $passbookFrom }}"
                           class="px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">To Date</label>
                    <input type="date" name="passbook_to" value="{{ $passbookTo }}"
                           class="px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Month Wise</label>
                    <select name="passbook_month" class="px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700 bg-white">
                        <option value="">All Months</option>
                        @foreach($passbookAvailableMonths as $ymKey => $ymLabel)
                            <option value="{{ $ymKey }}" {{ $passbookMonth === $ymKey ? 'selected' : '' }}>{{ $ymLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Entry Type</label>
                    <select name="passbook_type" class="px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-700 bg-white">
                        <option value="all" {{ $passbookType === 'all' ? 'selected' : '' }}>All Transactions</option>
                        <option value="collection" {{ $passbookType === 'collection' ? 'selected' : '' }}>Milk Supply (+)</option>
                        <option value="purchase" {{ $passbookType === 'purchase' ? 'selected' : '' }}>Product Purchases (-)</option>
                        <option value="advance" {{ $passbookType === 'advance' ? 'selected' : '' }}>Advances & Loans (-)</option>
                        <option value="deduction" {{ $passbookType === 'deduction' ? 'selected' : '' }}>Deductions (-)</option>
                        <option value="settlement" {{ $passbookType === 'settlement' ? 'selected' : '' }}>Settlements (-)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-2xs transition cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filter</span>
                    </button>
                    @if($passbookFrom || $passbookTo || $passbookMonth || ($passbookType && $passbookType !== 'all'))
                        <a href="{{ route('farmers.show', ['farmer' => $farmer->id, 'tab' => 'passbook']) }}"
                           class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Print & Download PDF Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('farmers.passbook.statement', ['farmer' => $farmer->id, 'from_date' => $passbookFrom, 'to_date' => $passbookTo, 'month' => $passbookMonth, 'type' => $passbookType, 'download' => 1]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i>
                    <span>Download PDF</span>
                </a>
                <a href="{{ route('farmers.passbook.statement', ['farmer' => $farmer->id, 'from_date' => $passbookFrom, 'to_date' => $passbookTo, 'month' => $passbookMonth, 'type' => $passbookType, 'print' => 1]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-xs transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Statement</span>
                </a>
            </div>
        </div>

        <!-- On-Screen Passbook Statement Sheet (Reference Statement Layout) -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/90 shadow-xs space-y-5">
            <!-- Statement Title & Date Range -->
            <div class="text-center space-y-1">
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                    {{ $farmer->name }} Statement
                </h3>
                <p class="text-xs sm:text-sm font-semibold text-slate-600">
                    ({{ $passbookStartDateFormatted }} - {{ $passbookEndDateFormatted }})
                </p>
            </div>

            <!-- 4-Column Summary Box (Opening Balance | Total Debit(-) | Total Credit(+) | Net Balance) -->
            <div class="border border-slate-300 rounded-xl p-4 sm:p-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:border-r lg:border-slate-200 lg:pr-4">
                    <span class="text-xs font-semibold text-slate-600 block">Opening Balance</span>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-base sm:text-lg font-extrabold {{ $passbookOpeningBalance < 0 ? 'text-rose-600' : ($passbookOpeningBalance > 0 ? 'text-emerald-700' : 'text-slate-900') }}">
                            ₹{{ number_format(abs($passbookOpeningBalance), 2) }}
                        </span>
                        @if($passbookOpeningBalance != 0)
                            <span class="text-xs font-semibold text-slate-500">{{ $passbookOpeningBalance < 0 ? 'Dr' : 'Cr' }}</span>
                        @endif
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">(on {{ $passbookStartDateFormatted }})</span>
                </div>

                <div class="lg:border-r lg:border-slate-200 lg:pr-4">
                    <span class="text-xs font-semibold text-slate-600 block">Total Debit(-)</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900 mt-1.5 block">
                        ₹{{ number_format($passbookTotalDebit, 2) }}
                    </span>
                </div>

                <div class="lg:border-r lg:border-slate-200 lg:pr-4">
                    <span class="text-xs font-semibold text-slate-600 block">Total Credit(+)</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900 mt-1.5 block">
                        ₹{{ number_format($passbookTotalCredit, 2) }}
                    </span>
                </div>

                <div>
                    <span class="text-xs font-semibold text-slate-600 block">Net Balance</span>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-base sm:text-lg font-extrabold {{ $passbookNetBalance < 0 ? 'text-rose-600' : ($passbookNetBalance > 0 ? 'text-emerald-700' : 'text-slate-900') }}">
                            ₹{{ number_format(abs($passbookNetBalance), 2) }}
                        </span>
                        @if($passbookNetBalance != 0)
                            <span class="text-xs font-semibold text-slate-500">{{ $passbookNetBalance < 0 ? 'Dr' : 'Cr' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Entries Count Label -->
            <div class="text-xs text-slate-700">
                No. of Entries: <b class="font-extrabold text-slate-900">{{ count($passbookEntries) }} (Date Range)</b>
            </div>

            <!-- Month-Wise Statement Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse border border-slate-300">
                    <thead>
                        <tr class="bg-slate-50 text-slate-900 font-bold">
                            <th class="py-2.5 px-3.5 border border-slate-300 w-24">Date</th>
                            <th class="py-2.5 px-3.5 border border-slate-300">Details</th>
                            <th class="py-2.5 px-3.5 border border-slate-300 text-right w-32">Debit(-)</th>
                            <th class="py-2.5 px-3.5 border border-slate-300 text-right w-32">Credit(+)</th>
                            <th class="py-2.5 px-3.5 border border-slate-300 text-right w-36">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $mIndex = 0; @endphp
                        @forelse($passbookMonthlyGroups as $ym => $mGroup)
                            @php $mIndex++; @endphp
                            <!-- Month Section Header Row -->
                            <tr class="bg-white border-t-2 border-b-2 border-slate-500">
                                @if($mIndex === 1)
                                    <td colspan="3" class="py-2.5 px-3.5 font-extrabold text-slate-900 text-xs border border-slate-300">
                                        {{ $mGroup['first_date_full'] }}
                                    </td>
                                    <td colspan="2" class="py-2.5 px-3.5 text-right text-xs text-slate-600 border border-slate-300">
                                        (Opening Balance:
                                        <span class="font-bold {{ $mGroup['opening_balance'] < 0 ? 'text-rose-600' : ($mGroup['opening_balance'] > 0 ? 'text-emerald-700' : 'text-slate-900') }}">
                                            {{ number_format(abs($mGroup['opening_balance']), 2) }})
                                        </span>
                                        @if($mGroup['opening_balance'] != 0)
                                            <span class="text-[11px] font-semibold text-slate-500">{{ $mGroup['opening_balance'] < 0 ? 'Dr' : 'Cr' }}</span>
                                        @endif
                                    </td>
                                @else
                                    <td colspan="5" class="py-2.5 px-3.5 font-extrabold text-slate-900 text-xs border border-slate-300">
                                        {{ $mGroup['month_year'] }}
                                    </td>
                                @endif
                            </tr>

                            <!-- Entries within this Month -->
                            @foreach($mGroup['entries'] as $row)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-3.5 font-bold text-slate-800 bg-slate-50/50 border border-slate-300 whitespace-nowrap">
                                        {{ $row['date_short'] }}
                                    </td>
                                    <td class="py-3 px-3.5 text-slate-700 border border-slate-300 leading-relaxed">
                                        {{ $row['details'] }}
                                    </td>
                                    <td class="py-3 px-3.5 text-right font-bold text-slate-900 bg-rose-50/40 border border-slate-300 whitespace-nowrap">
                                        {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}
                                    </td>
                                    <td class="py-3 px-3.5 text-right font-bold text-slate-900 bg-emerald-50/50 border border-slate-300 whitespace-nowrap">
                                        {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}
                                    </td>
                                    <td class="py-3 px-3.5 text-right font-bold bg-slate-50/40 border border-slate-300 whitespace-nowrap">
                                        @if(abs($row['balance']) < 0.005)
                                            <span class="text-slate-900">0.00</span>
                                        @else
                                            <span class="{{ $row['balance'] < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                                                {{ number_format(abs($row['balance']), 2) }}
                                            </span>
                                            <span class="text-[10px] font-semibold text-slate-500 ml-0.5">{{ $row['balance'] < 0 ? 'Dr' : 'Cr' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Month Subtotal Row -->
                            <tr class="bg-white font-extrabold text-slate-900 border-t-2 border-b-2 border-slate-500">
                                <td colspan="2" class="py-2.5 px-3.5 border border-slate-300">
                                    {{ $mGroup['month_name'] }} Total
                                </td>
                                <td class="py-2.5 px-3.5 text-right border border-slate-300">
                                    {{ number_format($mGroup['total_debit'], 2) }}
                                </td>
                                <td class="py-2.5 px-3.5 text-right border border-slate-300">
                                    {{ number_format($mGroup['total_credit'], 2) }}
                                </td>
                                <td class="py-2.5 px-3.5 border border-slate-300"></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400 border border-slate-300">
                                    No passbook transactions found for the selected filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 6: Mobile Dairy Software (8-in-1 Suite) -->
    <div x-show="tab === 'mobile_dairy'" x-cloak class="space-y-6">
        @include('farmers.partials.mobile_dairy_suite', [
            'farmer' => $farmer,
            'suite' => $mobileDairyData ?? []
        ])
    </div>

    <!-- Issue Advance Modal -->
    <div x-show="advanceModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="advanceModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Issue Farmer Advance</h3>
                <button @click="advanceModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('farmers.advance.store', $farmer) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Advance Amount (₹) *</label>
                    <input type="number" step="10" name="amount" required placeholder="e.g. 2000" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date</label>
                    <input type="date" name="advance_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Purpose / Reason</label>
                    <input type="text" name="purpose" placeholder="e.g. Feed purchase / emergency" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="advanceModal = false" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Issue Advance</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Farmer Purchase Modal -->
    <div x-show="purchaseModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="purchaseModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Record Farmer Product Purchase</h3>
                    <p class="text-[11px] text-slate-500">{{ $farmer->farmer_code }} — {{ $farmer->name }}</p>
                </div>
                <button @click="purchaseModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>
            <form action="{{ route('product-sales.store') }}" method="POST" class="mt-4 space-y-3.5">
                @csrf
                <input type="hidden" name="farmer_id" value="{{ $farmer->id }}">
                <input type="hidden" name="farmer_code" value="{{ $farmer->farmer_code }}">
                <input type="hidden" name="farmer_name" value="{{ $farmer->name }}">
                <input type="hidden" name="redirect_to_farmer" value="{{ $farmer->id }}">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Purchase Date *</label>
                        <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Status *</label>
                        <select name="is_paid" x-model="purchaseIsPaid" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                            <option value="0">Unpaid (Credit / Due)</option>
                            <option value="1">Paid Immediately</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product / Feed *</label>
                    <select name="product_id" x-model="selectedProductId" @change="onSelectProduct($event)" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price ?? $prod->price ?? 0 }}" data-unit="{{ $prod->unit ?? 'Bag' }}">
                                {{ $prod->name }} (Stock: {{ $prod->current_stock }} {{ $prod->unit ?? '' }} • ₹{{ number_format((float)($prod->selling_price ?? $prod->price ?? 0), 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" x-model="purchaseQty" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Unit *</label>
                        <input type="text" name="unit" x-model="purchaseUnit" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rate (₹) *</label>
                        <input type="number" step="0.01" min="0" name="rate" x-model="purchaseRate" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center bg-slate-50 p-3 rounded-xl border border-slate-200/70">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Bill Amount</span>
                        <span class="text-lg font-black text-slate-900">₹ <span x-text="purchaseTotal">0.00</span></span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Payment Mode</label>
                        <select name="payment_mode" x-model="purchasePaymentMode" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 bg-white rounded-lg font-semibold">
                            <option value="Ledger">Farmer Ledger</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks</label>
                    <input type="text" name="remarks" placeholder="Optional note..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="purchaseModal = false" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
