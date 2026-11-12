@extends('layouts.app')

@section('title', 'Customer Khata Statement')
@section('breadcrumb', 'Reports -> Milk Sale Reports -> Customer Khata')
@section('header_title', 'Customer Khata Statement')

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb Bar -->
    <div class="flex items-center justify-between pb-1">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#005c53] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Reports -> Milk Sale Reports -> Customer Khata</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.milk-sales.customer-khata') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Select Customer / Buyer * :</label>
                <select name="buyer_id" required class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none min-w-[220px]">
                    <option value="">-- Choose Buyer / Customer --</option>
                    @foreach($buyers as $b)
                        <option value="{{ $b->id }}" {{ (string) request('buyer_id') === (string) $b->id ? 'selected' : '' }}>
                            {{ $b->buyer_code }} - {{ $b->name }} (Bal: ₹{{ number_format($b->current_balance, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <div class="flex items-center gap-2 pt-4">
                <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview Khata</span>
                </button>
                @if($selectedBuyer)
                    <a href="{{ route('reports.milk-sales.customer-khata', array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                        <span>Download Excel</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($selectedBuyer)
        <!-- Buyer Profile Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold text-lg border border-teal-200">
                    <i data-lucide="user" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">{{ $selectedBuyer->name }}</h3>
                    <p class="text-xs text-slate-500 font-mono">Code: {{ $selectedBuyer->buyer_code }} &bull; Phone: {{ $selectedBuyer->phone ?: 'N/A' }} &bull; Address: {{ $selectedBuyer->address ?: 'N/A' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-400">Current Outstanding Balance</span>
                    <p class="text-xl font-black {{ $selectedBuyer->current_balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                        ₹ {{ number_format($selectedBuyer->current_balance, 2) }}
                    </p>
                </div>
                <button type="button" onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print Khata</span>
                </button>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-3.5 border-b border-slate-100 bg-slate-50/50">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Transaction Ledger (Debit / Credit Statement)</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Voucher / Bill No</th>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3 text-right">Debit / Billed (₹)</th>
                            <th class="py-2.5 px-3 text-right">Credit / Paid (₹)</th>
                            <th class="py-2.5 px-3 text-right">Running Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-3 font-mono">{{ $t['date'] }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $t['ref'] }}</td>
                                <td class="py-2.5 px-3">{{ $t['description'] }}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                    {{ $t['debit'] > 0 ? '₹ ' . number_format($t['debit'], 2) : '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                    {{ $t['credit'] > 0 ? '₹ ' . number_format($t['credit'], 2) : '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black {{ $t['balance'] > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                                    ₹ {{ number_format($t['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No transactions recorded for this customer in selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white p-12 text-center rounded-2xl border border-slate-200 shadow-xs">
            <i data-lucide="book-open" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
            <h4 class="text-sm font-bold text-slate-700">Select a Customer to view Khata</h4>
            <p class="text-xs text-slate-400 mt-1">Choose any buyer or commercial customer from the dropdown above and click "Preview Khata".</p>
        </div>
    @endif

</div>
@endsection
