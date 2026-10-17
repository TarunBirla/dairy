@extends('layouts.app')

@section('title', 'Rate Charts')
@section('breadcrumb', 'Rate Charts')
@section('header_title', 'Milk Rate Charts & Formulas')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" onclick="openRateCorrectionModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-emerald-600"></i>
            <span>Rate Correction</span>
        </button>
        <a href="{{ route('rates.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Rate Chart</span>
        </a>
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

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Filter Bar matching Reference -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('rates.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-700">Status :</label>
                <select name="status" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-700">Category :</label>
                <select name="category" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All</option>
                    <option value="collection" {{ $category === 'collection' ? 'selected' : '' }}>Collection</option>
                    <option value="milk_sale" {{ $category === 'milk_sale' ? 'selected' : '' }}>Milk Sale</option>
                    <option value="chilling_center" {{ $category === 'chilling_center' ? 'selected' : '' }}>Chilling Center</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-700">Milk :</label>
                <select name="milk" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <option value="all" {{ $milk === 'all' ? 'selected' : '' }}>All</option>
                    <option value="cow" {{ $milk === 'cow' ? 'selected' : '' }}>Cow</option>
                    <option value="buffalo" {{ $milk === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                    <option value="mixed" {{ $milk === 'mixed' ? 'selected' : '' }}>Mix</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Search</span>
            </button>

            @if($status !== 'all' || $category !== 'all' || $milk !== 'all' || !empty($search))
                <a href="{{ route('rates.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Rate Charts Listing Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Table Top Header & Search -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Rate Charts</h3>
                <p class="text-xs text-slate-500">Configure procurement and selling rates based on FAT/SNF formulas or slabs.</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('rates.index') }}" class="relative w-full sm:w-64">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="hidden" name="milk" value="{{ $milk }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search rate charts..." class="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-200 rounded-xl bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </form>
            </div>
        </div>

        <!-- Table matching Reference Screenshot 1 -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-emerald-50/60 border-b border-emerald-100/80 text-[11px] font-bold text-emerald-950 uppercase tracking-wider">
                        <th class="py-3 px-4">S.No</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Milk Type</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Fixed Rate</th>
                        <th class="py-3 px-4">Assigned To</th>
                        <th class="py-3 px-4">Created At</th>
                        <th class="py-3 px-4">Updated At</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($rateCharts as $index => $rc)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-500">
                                {{ $rateCharts->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rc->category === 'collection')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        Collection
                                    </span>
                                @elseif($rc->category === 'milk_sale')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-blue-100 text-blue-800">
                                        Milk Sale
                                    </span>
                                @elseif($rc->category === 'chilling_center')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-purple-100 text-purple-800">
                                        Chilling Center
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 capitalize">
                                        {{ str_replace('_', ' ', $rc->category) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-semibold capitalize">
                                @if($rc->milk_type === 'cow')
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                        <i data-lucide="check" class="w-3 h-3"></i> Cow
                                    </span>
                                @elseif($rc->milk_type === 'buffalo')
                                    <span class="inline-flex items-center gap-1 text-indigo-700 font-bold">
                                        <i data-lucide="check" class="w-3 h-3"></i> Buffalo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-amber-700 font-bold">
                                        <i data-lucide="check" class="w-3 h-3"></i> Mix
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $rc->name }}</div>
                                @if($rc->is_default)
                                    <span class="inline-block mt-0.5 text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                        DEFAULT
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                ₹{{ number_format((float)$rc->fixed_rate, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] space-y-0.5">
                                    <div class="font-medium text-slate-600">
                                        Center - <span class="font-bold text-slate-900">{{ $rc->collection_centers_count ?? 0 }}</span>
                                    </div>
                                    <button type="button" 
                                            onclick="viewAssignedFarmers({{ $rc->id }}, '{{ addslashes($rc->name) }}')"
                                            class="inline-flex items-center gap-1 font-medium text-slate-600 hover:text-emerald-700 transition group/btn"
                                            title="Click to view all farmers assigned to this rate chart">
                                        <span>Farmer -</span>
                                        <span class="font-bold text-emerald-700 underline underline-offset-2 decoration-emerald-300 group-hover/btn:text-emerald-800">{{ $rc->farmers_count ?? 0 }}</span>
                                        <i data-lucide="external-link" class="w-3 h-3 text-emerald-500 opacity-70 group-hover/btn:opacity-100"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $rc->created_at ? $rc->created_at->format('d/m/Y h:i:s A') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $rc->updated_at ? $rc->updated_at->format('d/m/Y h:i:s A') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('rates.edit', $rc) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold rounded-lg transition shadow-xs">
                                        Edit
                                    </a>
                                    @if($rc->status === 'active')
                                        <button type="button" onclick="openAssignModal({{ $rc->id }}, '{{ addslashes($rc->name) }}')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition shadow-xs">
                                            Assign
                                        </button>
                                    @else
                                        <button type="button" onclick="alert('This Rate Chart is inactive. Please activate it first before assigning it to farmers.')" class="px-2.5 py-1 bg-slate-200 text-slate-400 cursor-not-allowed text-[11px] font-bold rounded-lg transition" title="Inactive rate charts cannot be assigned">
                                            Assign
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="layers" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold">No rate charts found matching the criteria.</p>
                                <a href="{{ route('rates.create') }}" class="mt-2 inline-block text-xs font-bold text-emerald-600 hover:underline">+ Create new rate chart</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer -->
        <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <div class="text-xs text-slate-500 font-medium">
                @if($rateCharts->total() > 0)
                    Showing {{ $rateCharts->firstItem() }} to {{ $rateCharts->lastItem() }} of {{ $rateCharts->total() }} entries
                @else
                    Showing 0 entries
                @endif
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('rates.index') }}" class="flex items-center gap-1.5 text-xs text-slate-500">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="hidden" name="milk" value="{{ $milk }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <span>Per page:</span>
                    <select name="per_page" onchange="this.form.submit()" class="px-2 py-1 text-xs border border-slate-200 rounded-lg bg-white">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </form>

                <div>
                    {{ $rateCharts->links() }}
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ================= RATE CORRECTION MODAL (REFERENCE SCREENSHOT 5) ================= -->
<div id="rateCorrectionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-start justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="sliders-horizontal" class="w-5 h-5 text-emerald-600"></i>
                    Rate Correction
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Choose the rate chart and the farmers or customers it should apply to.
                </p>
            </div>
            <button type="button" onclick="closeRateCorrectionModal()" class="w-8 h-8 rounded-full bg-slate-200/60 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="rateCorrectionForm" action="{{ route('rates.apply-correction') }}" method="POST" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">
            @csrf

            <!-- Form Row 1: Filter, Apply to, From date, To date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select filter</label>
                    <select name="filter_type" id="rc_filter_type" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="collection" selected>Collection</option>
                        <option value="milk_sale">Milk Sale</option>
                        <option value="chilling_center">Chilling Center</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Apply to</label>
                    <select name="apply_to" id="rc_apply_to" onchange="toggleCorrectionPeopleType(this.value)" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="farmer" selected>Farmer</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">From date</label>
                    <input type="date" name="from_date" id="rc_from_date" value="{{ date('Y-m-01') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">To date</label>
                    <input type="date" name="to_date" id="rc_to_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>
            </div>

            <!-- Form Row 2: Shift -->
            <div class="space-y-1.5">
                <div class="w-full sm:w-1/3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Shift</label>
                    <select name="shift" id="rc_shift" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="morning_evening" selected>Morning + Evening</option>
                        <option value="morning">Morning</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>
                <p class="text-[11px] text-slate-400 italic">
                    From date, To date and Shift set the correction period. They do not filter the Farmer or Customer list.
                </p>
            </div>

            <!-- Form Row 3: Milk type, Rate chart name -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk type</label>
                    <select name="milk_type" id="rc_milk_type" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                        <option value="cow" selected>Cow</option>
                        <option value="buffalo">Buffalo</option>
                        <option value="mix">Mix</option>
                        <option value="all">All Milk Types</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate chart name *</label>
                    <select name="rate_chart_id" id="rc_rate_chart_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-semibold text-emerald-800">
                        <option value="">Select rate chart</option>
                        @foreach($activeRateCharts as $arc)
                            <option value="{{ $arc->id }}" data-milk="{{ $arc->milk_type }}">
                                {{ $arc->name }} ({{ ucfirst($arc->milk_type) }} - {{ ucfirst(str_replace('_', ' ', $arc->category)) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- People Selection Section (Farmers / Customers) matching Reference Screenshot 5 -->
            <div class="border-t border-slate-100 pt-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h4 id="peopleSectionTitle" class="text-xs font-bold text-slate-900">Farmers</h4>
                        <p class="text-[11px] text-slate-500">
                            All added people are shown. Leave all unchecked to apply the selected chart to everyone.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" id="personSearchInput" onkeyup="filterCorrectionPeople()" placeholder="Filter people..." class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg outline-none w-36">
                        <button type="button" onclick="selectAllCorrectionPeople(true)" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition border border-slate-200">
                            Select all
                        </button>
                        <button type="button" onclick="selectAllCorrectionPeople(false)" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition border border-slate-200">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Farmers Grid -->
                <div id="farmersGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-2 bg-slate-50/60 rounded-2xl border border-slate-100">
                    @forelse($farmers as $fmr)
                        <label class="person-item flex items-start gap-2.5 p-2.5 bg-white hover:bg-emerald-50/50 rounded-xl border border-slate-200/80 cursor-pointer transition shadow-2xs select-none">
                            <input type="checkbox" name="selected_ids[]" value="{{ $fmr->id }}" onchange="updatePersonCount()" class="rc-person-cb rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                            <div class="text-[11px] leading-snug">
                                <span class="font-bold text-slate-900 block truncate">
                                    {{ $fmr->name }} @if(!empty($fmr->name_hi)) - ({{ $fmr->name_hi }}) @endif
                                </span>
                                <span class="font-mono text-[10px] text-slate-500">
                                    [{{ $fmr->farmer_code }}]
                                </span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                            No active farmers found.
                        </div>
                    @endforelse
                </div>

                <!-- Customers Grid (Hidden by default, shown when apply_to == 'customer') -->
                <div id="customersGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto p-2 bg-slate-50/60 rounded-2xl border border-slate-100 hidden">
                    @forelse($customers as $cust)
                        <label class="person-item flex items-start gap-2.5 p-2.5 bg-white hover:bg-emerald-50/50 rounded-xl border border-slate-200/80 cursor-pointer transition shadow-2xs select-none">
                            <input type="checkbox" name="selected_ids[]" value="{{ $cust->id }}" onchange="updatePersonCount()" class="rc-person-cb rounded text-emerald-600 focus:ring-emerald-500 mt-0.5" disabled>
                            <div class="text-[11px] leading-snug">
                                <span class="font-bold text-slate-900 block truncate">
                                    {{ $cust->name }}
                                </span>
                                <span class="font-mono text-[10px] text-slate-500">
                                    [{{ $cust->customer_code }}]
                                </span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                            No active customers found.
                        </div>
                    @endforelse
                </div>

                <!-- Bottom Summary & Selection Status -->
                <div class="pt-2 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span id="selectedCountText" class="italic">
                        No specific person selected: applies to everyone
                    </span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeRateCorrectionModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                    Cancel
                </button>
                <button type="submit" id="btnApplyCorrection" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Apply rate correction</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= ASSIGN RATE CHART MODAL ================= -->
<div id="assignModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 flex items-start justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5 text-blue-600"></i>
                    Assign Rate Chart
                </h3>
                <p class="text-xs text-slate-500 mt-0.5" id="assignModalSubtitle">
                    Assign this rate chart to collection centers and farmers.
                </p>
            </div>
            <button type="button" onclick="closeAssignModal()" class="w-8 h-8 rounded-full bg-slate-200/60 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="assignForm" method="POST" action="" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">
            @csrf

            <!-- Centers Selection -->
            <div>
                <h4 class="text-xs font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                    <i data-lucide="building" class="w-3.5 h-3.5 text-blue-600"></i>
                    Collection Centers
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200">
                    @forelse($collectionCenters as $center)
                        <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200/80 cursor-pointer text-xs">
                            <input type="checkbox" name="center_ids[]" value="{{ $center->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $center->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $center->code }}</span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-2 text-center py-3 text-xs text-slate-400">No centers available</div>
                    @endforelse
                </div>
            </div>

            <!-- Farmers Selection -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600"></i>
                        Farmers
                    </h4>
                    <div class="flex gap-2">
                        <button type="button" onclick="toggleAllAssignFarmers(true)" class="text-[11px] text-blue-600 hover:underline">Select all</button>
                        <button type="button" onclick="toggleAllAssignFarmers(false)" class="text-[11px] text-slate-500 hover:underline">Clear</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-2 bg-slate-50 rounded-xl border border-slate-200" id="assignFarmersList">
                    @forelse($farmers as $fmr)
                        <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200/80 cursor-pointer text-xs">
                            <input type="checkbox" name="farmer_ids[]" value="{{ $fmr->id }}" class="assign-farmer-cb rounded text-emerald-600 focus:ring-emerald-500">
                            <div class="truncate">
                                <span class="font-bold text-slate-900 block truncate">{{ $fmr->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">[{{ $fmr->farmer_code }}]</span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-2 text-center py-3 text-xs text-slate-400">No farmers available</div>
                    @endforelse
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeAssignModal()" class="px-4 py-2 text-xs font-semibold text-slate-600">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= ASSIGNED FARMERS LIST MODAL ================= -->
<div id="assignedFarmersModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-start justify-between bg-slate-50/70">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-emerald-600"></i>
                    <span>Assigned Farmers</span>
                    <span id="afChartBadge" class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold"></span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Farmers currently using this rate chart for milk collection calculations.
                </p>
            </div>
            <button type="button" onclick="closeAssignedFarmersModal()" class="w-8 h-8 rounded-full bg-slate-200/60 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Filter bar -->
        <div class="p-4 border-b border-slate-100 bg-white flex items-center justify-between gap-3">
            <div class="relative flex-1">
                <input type="text" id="afSearchInput" onkeyup="filterAssignedFarmers()" placeholder="Search by name, code or village..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
            <button type="button" id="afAssignMoreBtn" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5 shrink-0">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                <span>+ Assign More</span>
            </button>
        </div>

        <!-- Farmers List Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-5" id="afContainer">
            <div id="afLoading" class="py-12 text-center text-slate-400">
                <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto text-emerald-600 mb-2"></i>
                <p class="text-xs font-semibold">Loading assigned farmers...</p>
            </div>
            <div id="afEmpty" class="py-12 text-center text-slate-400 hidden">
                <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                <p class="text-xs font-semibold text-slate-600">No farmers are assigned to this rate chart yet.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Click "+ Assign More" to assign farmers to this chart.</p>
            </div>
            <div id="afList" class="divide-y divide-slate-100">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <span id="afCountText" class="text-xs font-semibold text-slate-600">0 farmer(s)</span>
            <button type="button" onclick="closeAssignedFarmersModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-xl transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    // Rate Correction Modal Controls
    function openRateCorrectionModal() {
        document.getElementById('rateCorrectionModal').classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    function closeRateCorrectionModal() {
        document.getElementById('rateCorrectionModal').classList.add('hidden');
    }

    function toggleCorrectionPeopleType(type) {
        const title = document.getElementById('peopleSectionTitle');
        const farmersGrid = document.getElementById('farmersGrid');
        const customersGrid = document.getElementById('customersGrid');

        if (type === 'customer') {
            title.textContent = 'Customers';
            farmersGrid.classList.add('hidden');
            customersGrid.classList.remove('hidden');
            farmersGrid.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = true);
            customersGrid.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = false);
        } else {
            title.textContent = 'Farmers';
            customersGrid.classList.add('hidden');
            farmersGrid.classList.remove('hidden');
            customersGrid.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = true);
            farmersGrid.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = false);
        }
        updatePersonCount();
    }

    function selectAllCorrectionPeople(select) {
        const activeGrid = document.getElementById('rc_apply_to').value === 'customer' 
            ? document.getElementById('customersGrid') 
            : document.getElementById('farmersGrid');
        
        const checkboxes = activeGrid.querySelectorAll('.rc-person-cb:not(:disabled)');
        checkboxes.forEach(cb => {
            const parent = cb.closest('.person-item');
            if (parent && parent.style.display !== 'none') {
                cb.checked = select;
            }
        });
        updatePersonCount();
    }

    function updatePersonCount() {
        const activeGrid = document.getElementById('rc_apply_to').value === 'customer' 
            ? document.getElementById('customersGrid') 
            : document.getElementById('farmersGrid');
        
        const checkedCount = activeGrid.querySelectorAll('.rc-person-cb:checked:not(:disabled)').length;
        const text = document.getElementById('selectedCountText');

        if (checkedCount === 0) {
            text.textContent = 'No specific person selected: applies to everyone';
            text.classList.add('italic');
            text.classList.remove('text-emerald-700', 'font-bold');
        } else {
            text.textContent = checkedCount + ' person(s) selected';
            text.classList.remove('italic');
            text.classList.add('text-emerald-700', 'font-bold');
        }
    }

    function filterCorrectionPeople() {
        const search = document.getElementById('personSearchInput').value.toLowerCase();
        const activeGrid = document.getElementById('rc_apply_to').value === 'customer' 
            ? document.getElementById('customersGrid') 
            : document.getElementById('farmersGrid');
        
        const items = activeGrid.querySelectorAll('.person-item');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? 'flex' : 'none';
        });
    }

    // Assign Modal Controls
    function openAssignModal(rateId, rateName) {
        const form = document.getElementById('assignForm');
        form.action = `/rates/${rateId}/assign`;
        document.getElementById('assignModalSubtitle').textContent = `Assign chart "${rateName}" to collection centers and farmers.`;
        
        // Reset checkboxes first
        document.querySelectorAll('.assign-farmer-cb').forEach(cb => cb.checked = false);

        // Fetch already assigned farmers to pre-check
        fetch(`/rates/${rateId}/assigned-farmers`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.farmers) {
                    const assignedIds = new Set(data.farmers.map(f => String(f.id)));
                    document.querySelectorAll('.assign-farmer-cb').forEach(cb => {
                        if (assignedIds.has(String(cb.value))) {
                            cb.checked = true;
                        }
                    });
                }
            })
            .catch(() => {});

        document.getElementById('assignModal').classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    function toggleAllAssignFarmers(select) {
        document.querySelectorAll('.assign-farmer-cb').forEach(cb => cb.checked = select);
    }

    // Assigned Farmers Modal Controls
    let currentActiveRateId = null;
    let currentActiveRateName = '';

    function viewAssignedFarmers(rateId, rateName) {
        currentActiveRateId = rateId;
        currentActiveRateName = rateName;
        document.getElementById('afChartBadge').textContent = rateName;
        document.getElementById('afSearchInput').value = '';
        document.getElementById('afAssignMoreBtn').onclick = function() {
            closeAssignedFarmersModal();
            openAssignModal(rateId, rateName);
        };

        document.getElementById('assignedFarmersModal').classList.remove('hidden');
        document.getElementById('afLoading').classList.remove('hidden');
        document.getElementById('afEmpty').classList.add('hidden');
        document.getElementById('afList').innerHTML = '';
        document.getElementById('afCountText').textContent = 'Loading...';
        if (window.lucide) { lucide.createIcons(); }

        fetch(`/rates/${rateId}/assigned-farmers`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('afLoading').classList.add('hidden');
                if (data.success && data.farmers && data.farmers.length > 0) {
                    renderAssignedFarmers(data.farmers, rateId);
                } else {
                    document.getElementById('afEmpty').classList.remove('hidden');
                    document.getElementById('afCountText').textContent = '0 farmers';
                }
            })
            .catch(err => {
                document.getElementById('afLoading').classList.add('hidden');
                document.getElementById('afList').innerHTML = '<div class="py-6 text-center text-xs text-rose-600 font-semibold">Failed to load assigned farmers.</div>';
            });
    }

    function renderAssignedFarmers(farmers, rateId) {
        const list = document.getElementById('afList');
        list.innerHTML = '';
        document.getElementById('afCountText').textContent = `${farmers.length} farmer(s) assigned`;

        farmers.forEach(f => {
            const item = document.createElement('div');
            item.className = 'af-item py-3 px-2 flex items-center justify-between gap-3 hover:bg-slate-50/80 rounded-xl transition';
            item.setAttribute('data-search', `${f.name} ${f.name_hi || ''} ${f.farmer_code} ${f.village || ''}`.toLowerCase());
            item.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center shrink-0 text-xs border border-emerald-100">
                        ${(f.name || 'F').charAt(0).toUpperCase()}
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-xs truncate">${f.name}</span>
                            ${f.name_hi ? `<span class="text-[11px] text-slate-500 font-medium truncate">(${f.name_hi})</span>` : ''}
                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-mono rounded border border-slate-200">${f.farmer_code}</span>
                        </div>
                        <div class="text-[10px] text-slate-400 flex items-center gap-3 mt-0.5">
                            ${f.phone ? `<span>📞 ${f.phone}</span>` : ''}
                            ${f.village ? `<span>🏡 ${f.village}</span>` : ''}
                            ${f.animal_type ? `<span class="capitalize">🥛 ${f.animal_type}</span>` : ''}
                        </div>
                    </div>
                </div>
                <button type="button" onclick="unassignFarmer(${rateId}, ${f.id}, '${escapeJs(f.name)}')" class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 rounded-lg transition shrink-0">
                    Unassign
                </button>
            `;
            list.appendChild(item);
        });
        if (window.lucide) { lucide.createIcons(); }
    }

    function filterAssignedFarmers() {
        const q = document.getElementById('afSearchInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('.af-item');
        let visible = 0;
        items.forEach(it => {
            const text = it.getAttribute('data-search') || '';
            if (!q || text.includes(q)) {
                it.style.display = 'flex';
                visible++;
            } else {
                it.style.display = 'none';
            }
        });
        document.getElementById('afCountText').textContent = `${visible} farmer(s) shown`;
    }

    function unassignFarmer(rateId, farmerId, farmerName) {
        if (!confirm(`Are you sure you want to unassign "${farmerName}" from this rate chart?`)) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_token"]')?.value;

        fetch(`/rates/${rateId}/unassign/${farmerId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                viewAssignedFarmers(rateId, currentActiveRateName);
            } else {
                alert(data.message || 'Failed to unassign farmer.');
            }
        })
        .catch(() => alert('Network error occurred while unassigning farmer.'));
    }

    function closeAssignedFarmersModal() {
        document.getElementById('assignedFarmersModal').classList.add('hidden');
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'");
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const rcModal = document.getElementById('rateCorrectionModal');
        const asModal = document.getElementById('assignModal');
        const afModal = document.getElementById('assignedFarmersModal');
        if (e.target === rcModal) closeRateCorrectionModal();
        if (e.target === asModal) closeAssignModal();
        if (e.target === afModal) closeAssignedFarmersModal();
    });
</script>
@endsection
