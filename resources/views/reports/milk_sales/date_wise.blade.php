@extends('layouts.app')

@section('title', 'Milk Sale Date Wise Report')
@section('breadcrumb', 'Reports -> Milk Sale Reports -> Date Wise')
@section('header_title', 'Milk Sale Date Wise Report')

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb Bar matching mobile dairy screenshot -->
    <div class="flex items-center justify-between pb-1">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#005c53] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Reports -> Milk Sale Reports -> Date Wise</span>
        </a>
    </div>

    <!-- Filter Form Bar matching Mobile Dairy Screenshot 1 & 4 -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.milk-sales.date-wise') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <!-- Centers -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Center :</label>
                <select name="center_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="">All Centers</option>
                    @foreach($centers as $c)
                        <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Start Date :</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">End Date :</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <!-- Milk Shift -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Shift :</label>
                <select name="shift" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="all" {{ request('shift', 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>

            <!-- Milk Type -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Type :</label>
                <select name="milk_type" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="all" {{ request('milk_type', 'all') === 'all' ? 'selected' : '' }}>All</option>
                    <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>Cow</option>
                    <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                </select>
            </div>

            <!-- Action Buttons: Preview & Download Excel -->
            <div class="flex items-center gap-2 pt-4">
                <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview</span>
                </button>
                <a href="{{ route('reports.milk-sales.date-wise', array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Download Excel</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Statistics Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Sales Qty</span>
            <p class="text-xl font-black text-slate-800 mt-0.5">{{ number_format($totalLiters, 2) }} <span class="text-xs font-semibold text-slate-500">Ltr</span></p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg FAT / Avg SNF</span>
            <p class="text-xl font-black text-slate-800 mt-0.5">{{ number_format($avgFat, 2) }}% / {{ number_format($avgSnf, 2) }}%</p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Sales Amount</span>
            <p class="text-xl font-black text-emerald-700 mt-0.5">₹ {{ number_format($totalAmount, 2) }}</p>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Balance Due</span>
            <p class="text-xl font-black text-rose-600 mt-0.5">₹ {{ number_format($totalBalance, 2) }}</p>
        </div>
    </div>

    <!-- Data Table matching Mobile Dairy Milk Sale Details View -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Milk Sale Records ({{ $sales->total() }})</h4>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition inline-flex items-center gap-1 shadow-2xs">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3 text-center w-12">SrNo</th>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Shift</th>
                        <th class="py-2.5 px-3">Buyer / Customer</th>
                        <th class="py-2.5 px-3">Milk Type</th>
                        <th class="py-2.5 px-3 text-right">Liters</th>
                        <th class="py-2.5 px-3 text-right">FAT %</th>
                        <th class="py-2.5 px-3 text-right">SNF %</th>
                        <th class="py-2.5 px-3 text-right">CLR</th>
                        <th class="py-2.5 px-3 text-right">Rate (₹)</th>
                        <th class="py-2.5 px-3 text-right">Total Amount (₹)</th>
                        <th class="py-2.5 px-3 text-right">Paid (₹)</th>
                        <th class="py-2.5 px-3 text-right">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sales as $idx => $s)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $sales->firstItem() + $idx }}</td>
                            <td class="py-2.5 px-3 font-mono font-medium text-slate-800">{{ $s->sale_date ? $s->sale_date->format('d/m/Y') : '-' }}</td>
                            <td class="py-2.5 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $s->shift === 'morning' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-indigo-50 text-indigo-800 border border-indigo-200' }}">
                                    {{ $s->shift }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $s->buyer->name ?? 'Walk-in / Cash' }}</div>
                                @if($s->buyer && $s->buyer->buyer_code)
                                    <span class="text-[10px] font-mono text-slate-400">Code: {{ $s->buyer->buyer_code }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $s->milk_type === 'buffalo' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ $s->milk_type }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">{{ number_format($s->quantity_liters, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($s->fat_percentage, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($s->snf_percentage, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($s->clr_reading, 1) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">₹ {{ number_format($s->rate_per_liter, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">₹ {{ number_format($s->total_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-600">₹ {{ number_format($s->paid_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $s->balance_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                ₹ {{ number_format($s->balance_amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <span>No milk sale records found for selected period and filters.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->count() > 0)
                    <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200 text-slate-900">
                        <tr>
                            <td colspan="5" class="py-2.5 px-3 text-center uppercase tracking-wider text-xs">Total Summary</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($totalLiters, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($avgFat, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($avgSnf, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">-</td>
                            <td class="py-2.5 px-3 text-right font-mono">-</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-700">₹ {{ number_format($totalAmount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-600">₹ {{ number_format($totalPaid, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-mono text-rose-600">₹ {{ number_format($totalBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-3.5 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
