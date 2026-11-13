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

    @if(isset($subtitle) && $subtitle)
        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-400"></i>
            <span>{{ $subtitle }}</span>
        </div>
    @endif

    <!-- Filter Form Bar matching Mobile Dairy Screenshots 1, 2, 3, 4 -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        
        {{-- CASE 1: Center Wise Summary (Screenshot 1) --}}
        @if(($filterType ?? '') === 'center_summary')
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 text-xs">
                <!-- Start Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Start Date :</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
                </div>

                <!-- Start Shift -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Start Shift :</label>
                    <select name="start_shift" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="morning" {{ request('start_shift', 'morning') === 'morning' ? 'selected' : '' }}>Morning</option>
                        <option value="evening" {{ request('start_shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                        <option value="all" {{ request('start_shift') === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">End Date :</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
                </div>

                <!-- End Shift -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">End Shift :</label>
                    <select name="end_shift" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="evening" {{ request('end_shift', 'evening') === 'evening' ? 'selected' : '' }}>Evening</option>
                        <option value="morning" {{ request('end_shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                        <option value="all" {{ request('end_shift') === 'all' ? 'selected' : '' }}>All</option>
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

                <!-- Milk Status -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Milk Status :</label>
                    <select name="milk_status" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="all" {{ request('milk_status', 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="accepted" {{ request('milk_status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('milk_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Date wise summary checkbox -->
                <div class="flex items-center gap-1.5 pt-4">
                    <input type="checkbox" id="date_wise_summary" name="date_wise_summary" value="1" {{ request('date_wise_summary') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                    <label for="date_wise_summary" class="text-xs font-semibold text-slate-700">Date wise summary</label>
                </div>

                <div class="flex items-center gap-2 pt-4 ml-auto">
                    <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <span>Preview</span>
                    </button>
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <span>Download Excel</span>
                    </a>
                </div>
            </form>

        {{-- CASE 2: Farmer Wise (Screenshot 2) --}}
        @elseif(($filterType ?? '') === 'farmer_wise')
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 text-xs" x-data="{ farmerMode: '{{ request('farmer_mode', 'all') }}' }">
                <!-- From Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
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

                <!-- Sort By -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Sort By :</label>
                    <select name="sort_by" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="date" {{ request('sort_by', 'date') === 'date' ? 'selected' : '' }}>Date</option>
                        <option value="farmer_code" {{ request('sort_by') === 'farmer_code' ? 'selected' : '' }}>Farmer Code</option>
                        <option value="farmer_name" {{ request('sort_by') === 'farmer_name' ? 'selected' : '' }}>Farmer Name</option>
                    </select>
                </div>

                <!-- Order By -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Order By :</label>
                    <select name="order_by" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="asc" {{ request('order_by', 'asc') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('order_by') === 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>

                <!-- Report Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Report Type :</label>
                    <select name="report_type" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="individual" {{ request('report_type', 'individual') === 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="summary" {{ request('report_type') === 'summary' ? 'selected' : '' }}>Summary</option>
                    </select>
                </div>

                <!-- Radio: All Farmers / Farmer Wise -->
                <div class="flex items-center gap-3 pt-4">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="farmer_mode" value="all" x-model="farmerMode" class="text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">All Farmers</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="farmer_mode" value="farmer_wise" x-model="farmerMode" class="text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Farmer Wise</span>
                    </label>
                </div>

                <!-- Conditional Farmer dropdown when Farmer Wise is selected -->
                <div x-show="farmerMode === 'farmer_wise'" class="pt-4">
                    <select name="farmer_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="">Select Farmer</option>
                        @foreach($farmers ?? [] as $f)
                            <option value="{{ $f->id }}" {{ request('farmer_id') == $f->id ? 'selected' : '' }}>{{ $f->farmer_code }} - {{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Checkboxes: Include Addition, Include Deduction -->
                <div class="flex items-center gap-3 pt-4">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="include_addition" value="1" {{ request('include_addition') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Include Addition</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="include_deduction" value="1" {{ request('include_deduction') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Include Deduction</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-4 ml-auto">
                    <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Preview</span>
                    </button>
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Download Excel</span>
                    </a>
                </div>
            </form>

        {{-- CASE 3: Farmer Wise Summary (Screenshot 3) --}}
        @elseif(($filterType ?? '') === 'farmer_summary')
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 text-xs" x-data="{ farmerMode: '{{ request('farmer_mode', 'all') }}' }">
                <!-- Center -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Center :</label>
                    <select name="center_id" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="">Select Centers</option>
                        @foreach($centers ?? [] as $c)
                            <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- From Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none font-medium">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
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

                <!-- Sort By -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Sort By :</label>
                    <select name="sort_by" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="date" {{ request('sort_by', 'date') === 'date' ? 'selected' : '' }}>Date</option>
                        <option value="farmer_code" {{ request('sort_by') === 'farmer_code' ? 'selected' : '' }}>Farmer Code</option>
                        <option value="farmer_name" {{ request('sort_by') === 'farmer_name' ? 'selected' : '' }}>Farmer Name</option>
                    </select>
                </div>

                <!-- Order By -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Order By :</label>
                    <select name="order_by" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="asc" {{ request('order_by', 'asc') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('order_by') === 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>

                <!-- Radio: All Farmers / Farmer Wise -->
                <div class="flex items-center gap-3 pt-4">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="farmer_mode" value="all" x-model="farmerMode" class="text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">All Farmers</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="farmer_mode" value="farmer_wise" x-model="farmerMode" class="text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Farmer Wise</span>
                    </label>
                </div>

                <!-- Checkboxes: Include Addition, Deduction, Milk Type Breakdown, Shift Type Breakdown -->
                <div class="flex flex-wrap items-center gap-3 pt-4">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="include_addition" value="1" {{ request('include_addition') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Include Addition</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="include_deduction" value="1" {{ request('include_deduction') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Include Deduction</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="milk_type_breakdown" value="1" {{ request('milk_type_breakdown') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Milk Type Breakdown</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="shift_type_breakdown" value="1" {{ request('shift_type_breakdown') ? 'checked' : '' }} class="rounded border-slate-300 text-[#005c53] focus:ring-[#005c53]">
                        <span class="text-xs font-semibold text-slate-700">Shift Type Breakdown</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-4 ml-auto">
                    <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Preview</span>
                    </button>
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Download Excel</span>
                    </a>
                </div>
            </form>

        {{-- CASE 4: Date Wise (Screenshot 4) --}}
        @elseif(($filterType ?? '') === 'date_wise')
            <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3 text-xs">
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

                <!-- Zero Type -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Zero Type :</label>
                    <select name="zero_type" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="non_zeros" {{ request('zero_type', 'non_zeros') === 'non_zeros' ? 'selected' : '' }}>Non Zeros</option>
                        <option value="all" {{ request('zero_type') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="only_zeros" {{ request('zero_type') === 'only_zeros' ? 'selected' : '' }}>Only Zeros</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Status :</label>
                    <select name="status" class="px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                        <option value="accepted" {{ request('status', 'accepted') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-4 ml-auto">
                    <button type="submit" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Preview</span>
                    </button>
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'excel'])) }}" class="px-4 py-1.5 bg-[#005c53] hover:bg-[#004740] text-white font-bold text-xs rounded-lg shadow-xs transition">
                        <span>Download Excel</span>
                    </a>
                </div>
            </form>

        {{-- Default generic filter form for all other reports --}}
        @else
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

                <div class="flex items-center gap-2 pt-4 ml-auto">
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
        @endif
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
