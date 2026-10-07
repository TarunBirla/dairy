@extends('layouts.app')

@section('title', 'Invoice Details - ' . $invoice->invoice_number)
@section('breadcrumb', 'Invoice Details')
@section('header_title', 'Invoice Details #' . $invoice->invoice_number)

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('farmer-invoices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Back to Invoices</span>
        </a>
        <a href="{{ route('farmer-invoices.print', $invoice->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Bill</span>
        </a>
        <form method="POST" action="{{ route('farmer-invoices.destroy', $invoice->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl shadow-xs transition">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Delete</span>
            </button>
        </form>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- 2-Column Layout matching Reference media_1791388765764.png -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Columns: Collections & Deductions Breakdown -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Milk Collections with Shift Tabs -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-data="{ shiftFilter: 'all' }">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                            <i data-lucide="milk" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Milk Collections</h3>
                            <p class="text-[11px] text-slate-500">{{ count($collections) }} Collection Entries</p>
                        </div>
                    </div>

                    <!-- Shift Filter Tabs -->
                    <div class="flex items-center bg-slate-200/70 p-0.5 rounded-xl text-xs font-medium">
                        <button type="button" @click="shiftFilter = 'all'" :class="shiftFilter === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                            All
                        </button>
                        <button type="button" @click="shiftFilter = 'morning'" :class="shiftFilter === 'morning' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                            Morning
                        </button>
                        <button type="button" @click="shiftFilter = 'evening'" :class="shiftFilter === 'evening' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1 rounded-lg transition">
                            Evening
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Shift</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3 text-right">Liter</th>
                                <th class="py-2.5 px-3 text-right">FAT %</th>
                                <th class="py-2.5 px-3 text-right">CLR / SNF</th>
                                <th class="py-2.5 px-3 text-right">Rate</th>
                                <th class="py-2.5 px-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($collections as $c)
                                <tr x-show="shiftFilter === 'all' || shiftFilter === '{{ strtolower($c->shift) }}'" class="hover:bg-slate-50/70">
                                    <td class="py-2 px-3 font-medium">{{ \Carbon\Carbon::parse($c->collection_date)->format('d M Y') }}</td>
                                    <td class="py-2 px-3 capitalize">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ strtolower($c->shift) === 'morning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                            {{ $c->shift }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 capitalize font-medium">{{ $c->milk_type }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-sky-900">{{ number_format($c->quantity_liters, 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono">{{ number_format($c->fat_percentage, 1) }}%</td>
                                    <td class="py-2 px-3 text-right font-mono text-slate-500">{{ $c->clr_reading ? number_format($c->clr_reading, 1) : ($c->snf_percentage ? number_format($c->snf_percentage, 1) : '-') }}</td>
                                    <td class="py-2 px-3 text-right font-mono">₹{{ number_format($c->rate_per_liter, 2) }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">₹{{ number_format($c->net_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-slate-400">No milk collection entries found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200 text-xs">
                            <tr>
                                <td colspan="3" class="py-2.5 px-3 uppercase tracking-wider text-slate-700">Total Milk Collections</td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-sky-900">{{ number_format($invoice->total_quantity, 2) }} L</td>
                                <td colspan="3"></td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Card 2: Deductions Breakdown matching Screenshot 4 -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                            <i data-lucide="minus-circle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Deductions Breakdown</h3>
                            <p class="text-[11px] text-slate-500">Stationary, Cattle Feed, Loan & In-Hand Advances</p>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                        Total: -₹{{ number_format($invoice->total_deduction, 2) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Type</th>
                                <th class="py-2.5 px-3">Remarks / Description</th>
                                <th class="py-2.5 px-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($deductions as $d)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-2 px-3 font-medium">{{ \Carbon\Carbon::parse($d->entry_date)->format('d M Y') }}</td>
                                    <td class="py-2 px-3 capitalize font-semibold text-slate-800">{{ str_replace('_', ' ', $d->deduction_type) }}</td>
                                    <td class="py-2 px-3 text-slate-500">{{ $d->remarks ?? '-' }}</td>
                                    <td class="py-2 px-3 text-right font-mono font-bold {{ $d->transaction_type === 'received' ? 'text-amber-700' : 'text-rose-700' }}">
                                        {{ $d->transaction_type === 'received' ? '+ ₹' : '- ₹' }}{{ number_format($d->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">No deductions recorded for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 3: Cattle Feeds / Products Provided (if any) -->
            @if(count($cattleFeeds) > 0)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                                <i data-lucide="package" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Cattle Feeds Issued</h3>
                                <p class="text-[11px] text-slate-500">Products supplied to farmer</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Item Details</th>
                                    <th class="py-2.5 px-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($cattleFeeds as $cf)
                                    <tr>
                                        <td class="py-2 px-3 font-medium">{{ \Carbon\Carbon::parse($cf->entry_date)->format('d M Y') }}</td>
                                        <td class="py-2 px-3 text-slate-600">{{ $cf->remarks ?? 'Cattle Feed' }}</td>
                                        <td class="py-2 px-3 text-right font-mono font-bold text-rose-700">-₹{{ number_format($cf->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Farmer Profile & Invoice Financial Summary -->
        <div class="space-y-6">

            <!-- Farmer Profile Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm">
                            {{ substr($farmer->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900 leading-tight">{{ $farmer->name }}</h4>
                            <p class="text-[11px] text-slate-500">Farmer Code: <span class="font-mono font-bold text-slate-800">{{ $farmer->farmer_code }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('farmers.show', $farmer->id) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold">
                        View
                    </a>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Phone</span>
                        <span class="font-medium text-slate-800">{{ $farmer->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Bank Name</span>
                        <span class="font-medium text-slate-800">{{ $farmer->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Account Number</span>
                        <span class="font-mono font-bold text-slate-800">{{ $farmer->account_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>IFSC Code</span>
                        <span class="font-mono font-medium text-slate-800">{{ $farmer->ifsc_code ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Invoice Summary Card matching media_1791388765764.png -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Invoice Summary</h4>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-mono text-[10px] font-bold rounded-md">
                        {{ $invoice->invoice_number }}
                    </span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Period Start</span>
                        <span class="font-medium text-slate-800">{{ $invoice->period_start->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Period End</span>
                        <span class="font-medium text-slate-800">{{ $invoice->period_end->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Total Quantity</span>
                        <span class="font-mono font-bold text-sky-800">{{ number_format($invoice->total_quantity, 2) }} L</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-between text-slate-700">
                        <span>Milk Amount (C)</span>
                        <span class="font-mono font-bold text-slate-900">₹{{ number_format($invoice->milk_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-700">
                        <span>Total Credit (B)</span>
                        <span class="font-mono font-bold text-amber-700">+ ₹{{ number_format($invoice->total_credit, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-700">
                        <span>Stationary Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->stationary_deduction, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Feed Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->feed_deduction, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-700">
                        <span>Advance Deduction</span>
                        <span class="font-mono text-slate-600">- ₹{{ number_format($invoice->advance_deduction, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-rose-700 font-semibold">
                        <span>Total Deductions (A)</span>
                        <span class="font-mono font-bold">- ₹{{ number_format($invoice->total_deduction, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Previous Balance</span>
                        <span class="font-mono">₹{{ number_format($invoice->previous_balance, 2) }}</span>
                    </div>

                    <div class="pt-3 border-t-2 border-slate-200 flex justify-between items-center text-sm">
                        <span class="font-bold text-slate-900">Net Payment</span>
                        <span class="text-base font-black font-mono text-emerald-700">₹{{ number_format($invoice->net_payment, 2) }}</span>
                    </div>
                </div>

                <div class="pt-3">
                    <a href="{{ route('farmer-invoices.print', $invoice->id) }}" target="_blank" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Print Bill Receipt</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
