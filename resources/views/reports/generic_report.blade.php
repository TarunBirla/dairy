@extends('layouts.app')

@section('title', $title)
@section('breadcrumb', 'Reports -> ' . $category . ' -> ' . $title)
@section('header_title', $title)

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb Bar matching mobile dairy screenshot -->
    <div class="flex items-center justify-between pb-1">
        <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#005c53] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Reports -> {{ $category }} -> {{ $title }}</span>
        </a>
    </div>

    <!-- Filter Form Bar matching Mobile Dairy Screenshot 1, 2, 3, 4 -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 text-xs">
            @if(isset($hasCenterFilter) && $hasCenterFilter)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Center :</label>
                    <select name="center_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="">Select Centers</option>
                        @foreach($centers ?? [] as $c)
                            <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Start Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
            </div>

            @if(isset($hasShiftFilter) && $hasShiftFilter)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Shift :</label>
                    <select name="shift" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="all" {{ request('shift', 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                        <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>
            @endif

            @if(isset($hasMilkTypeFilter) && $hasMilkTypeFilter)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Type :</label>
                    <select name="milk_type" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="all" {{ request('milk_type', 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>Cow</option>
                        <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                    </select>
                </div>
            @endif

            @if(isset($farmers) && count($farmers) > 0)
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Farmer :</label>
                    <select name="farmer_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="">All Farmers</option>
                        @foreach($farmers as $f)
                            <option value="{{ $f->id }}" {{ request('farmer_id') == $f->id ? 'selected' : '' }}>{{ $f->farmer_code }} - {{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex items-center gap-2 pt-4">
                <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                    <span>Preview</span>
                </button>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    <span>Download Excel</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Metrics Cards (if available) -->
    @if(isset($summaryCards) && count($summaryCards) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($summaryCards as $sc)
                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $sc['label'] }}</span>
                    <p class="text-xl font-black {{ $sc['color'] ?? 'text-slate-900' }} mt-0.5">{{ $sc['value'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ $title }} Records</h4>
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition inline-flex items-center gap-1 shadow-2xs">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        @foreach($columns as $col)
                            <th class="py-2.5 px-3 {{ isset($col['align']) ? 'text-' . $col['align'] : '' }}">{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($rows as $idx => $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            @foreach($columns as $col)
                                <td class="py-2.5 px-3 {{ isset($col['align']) ? 'text-' . $col['align'] : '' }} {{ $col['class'] ?? '' }}">
                                    {!! $r[$col['key']] ?? '-' !!}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="py-8 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <span>No records found for the selected criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($paginator) && $paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
            <div class="p-3.5 border-t border-slate-100">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
