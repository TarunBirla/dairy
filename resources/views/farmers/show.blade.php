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
<div class="space-y-6" x-data="{ tab: 'collections', advanceModal: false }">

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
                <div class="flex flex-col gap-2">
                    <button type="button" @click="advanceModal = true" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs">
                        + Issue Advance
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
    <div class="flex border-b border-slate-200 space-x-6 text-xs font-semibold">
        <button @click="tab = 'collections'" :class="tab === 'collections' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Milk Supply History ({{ $collections->count() }})
        </button>
        <button @click="tab = 'ledger'" :class="tab === 'ledger' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Passbook Ledger ({{ $ledgers->count() }})
        </button>
        <button @click="tab = 'advances'" :class="tab === 'advances' ? 'border-b-2 border-emerald-600 text-emerald-700' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
            Advances & Loans ({{ $advances->count() }})
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

</div>
@endsection
