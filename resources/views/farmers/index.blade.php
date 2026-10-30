@extends('layouts.app')

@section('title', 'Farmers Master')
@section('breadcrumb', 'Farmers')
@section('header_title', 'Farmer & Milk Supplier Management')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" @click="openBulkModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
            <span>Add Bulk Farmer</span>
        </button>
        <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Farmer</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="farmerManager()" @open-farmer-modal.window="openCreateModal()">

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL FARMERS</span>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalFarmers) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Registered milk suppliers</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE SUPPLIERS</span>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($activeFarmers) }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Eligible for daily collections</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PAYABLE DUES</span>
                <p class="text-3xl font-extrabold text-indigo-700 mt-1">₹ {{ number_format($totalPayable, 2) }}</p>
                <p class="text-[11px] text-indigo-500 font-medium mt-0.5">Live supplier ledger balance</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Comprehensive Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
        <form method="GET" action="{{ route('farmers.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search Farmer</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Name, Hindi name, code, phone, village..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Animal / Milk Classification Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Milk Type</label>
                    <select name="animal_type" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Milk Types</option>
                        <option value="cow" {{ request('animal_type') === 'cow' ? 'selected' : '' }}>Cow Milk (गाय)</option>
                        <option value="buffalo" {{ request('animal_type') === 'buffalo' ? 'selected' : '' }}>Buffalo Milk (भैंस)</option>
                        <option value="mixed" {{ request('animal_type') === 'mixed' ? 'selected' : '' }}>Mixed Milk</option>
                    </select>
                </div>

                <!-- Assigned Route Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Assigned Route</label>
                    <select name="route_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Routes</option>
                        @foreach($routes as $rt)
                            <option value="{{ $rt->id }}" {{ request('route_id') == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <!-- Ledger Balance Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Balance</label>
                    <select name="balance_status" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Balances</option>
                        <option value="due" {{ request('balance_status') === 'due' ? 'selected' : '' }}>Payable Due (> 0)</option>
                        <option value="advance" {{ request('balance_status') === 'advance' ? 'selected' : '' }}>Advance (< 0)</option>
                        <option value="zero" {{ request('balance_status') === 'zero' ? 'selected' : '' }}>Zero Balance (= 0)</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons & Quick Links -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <a href="{{ route('settlements.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                        <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Generate Settlement</span>
                    </a>
                    <a href="{{ route('farmers.sample-template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg transition">
                        <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Download Template</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('farmers.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Reset
                    </a>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Farmers Table Matching Reference Layout -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Farmer List / पंजीकृत किसान सूची</h3>
                <p class="text-xs text-slate-400">All registered dairy farmers, assigned routes, vehicles, banking, and active balances</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openBulkModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold rounded-lg shadow-xs transition">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Add Bulk Farmer</span>
                </button>
                <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ Add Farmer</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center">S.no</th>
                        <th class="py-3 px-4">Farmer Code</th>
                        <th class="py-3 px-4">Farmer Name (English)</th>
                        <th class="py-3 px-4">किसान नाम (Hindi)</th>
                        <th class="py-3 px-4">Phone Number</th>
                        <th class="py-3 px-4">Village</th>
                        <th class="py-3 px-4">Milk Type</th>
                        <th class="py-3 px-4">Assigned Vehicle</th>
                        <th class="py-3 px-4">Assigned Route</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center pr-6 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($farmers as $index => $farmer)
                        @php
                            $farmerViewData = [
                                'id' => $farmer->id,
                                'farmer_code' => $farmer->farmer_code,
                                'name' => $farmer->name,
                                'name_hi' => $farmer->name_hi ?? '',
                                'photo_url' => $farmer->photo_url,
                                'phone' => $farmer->phone ?? '',
                                'village' => $farmer->village ?? '',
                                'address' => $farmer->address ?? '',
                                'vehicle' => $farmer->vehicle ?? '',
                                'route_name' => $farmer->route ? $farmer->route->name : '',
                                'route_id' => $farmer->route_id,
                                'branch_id' => $farmer->branch_id,
                                'branch_name' => $farmer->branch_name ?? '',
                                'collection_center_id' => $farmer->collection_center_id,
                                'pricing_type' => $farmer->pricing_type ?? ($farmer->rate_chart_id ? 'rate_chart' : ($farmer->cow_milk_rate > 0 || $farmer->buffalo_milk_rate > 0 ? 'fixed_rate' : 'rate_chart')),
                                'rate_chart_id' => $farmer->rate_chart_id,
                                'animal_type' => $farmer->animal_type,
                                'cow_milk_rate' => $farmer->cow_milk_rate,
                                'buffalo_milk_rate' => $farmer->buffalo_milk_rate,
                                'bank_name' => $farmer->bank_name ?? '',
                                'account_number' => $farmer->account_number ?? '',
                                'ifsc_code' => $farmer->ifsc_code ?? '',
                                'upi_id' => $farmer->upi_id ?? '',
                                'anamat' => $farmer->anamat ?? 0,
                                'building_fund' => $farmer->building_fund ?? 0,
                                'installment' => $farmer->installment ?? 0,
                                'installment_deduction_type' => $farmer->getInstallmentDeductionType(),
                                'etc_amount' => $farmer->etc_amount ?? 0,
                                'grant_amount' => $farmer->grant_amount ?? 0,
                                'extra_rates' => $farmer->extra_rates ?: [],
                                'current_balance' => $farmer->current_balance ?? 0,
                                'status' => $farmer->status,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- S.No -->
                            <td class="py-3 px-3 text-center text-slate-500 font-medium">
                                {{ $farmers->firstItem() + $index }}
                            </td>

                            <!-- Farmer Code -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-800 text-[11px] font-mono border border-slate-200">
                                    {{ $farmer->farmer_code }}
                                </span>
                            </td>

                            <!-- Farmer Name (English) -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $farmer->photo_url }}" alt="{{ $farmer->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                                    <div>
                                        <a href="{{ route('farmers.show', $farmer) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                            {{ $farmer->name }}
                                        </a>
                                        @if($farmer->collectionCenter)
                                            <span class="block text-[10px] text-slate-400">{{ $farmer->collectionCenter->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- किसान नाम (Hindi) -->
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $farmer->name_hi ?: '—' }}
                            </td>

                            <!-- Phone Number -->
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $farmer->phone ?: '—' }}
                            </td>

                            <!-- Village -->
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $farmer->village ?: '—' }}
                            </td>

                            <!-- Milk Type & Pricing Scheme -->
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    <div>
                                        @if($farmer->animal_type === 'cow')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                🐄 Cow (CM)
                                            </span>
                                        @elseif($farmer->animal_type === 'buffalo')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                                🐃 Buffalo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                🥛 Mixed
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        @if(($farmer->pricing_type ?? '') === 'fixed_rate' || (($farmer->cow_milk_rate > 0 || $farmer->buffalo_milk_rate > 0) && !$farmer->rate_chart_id))
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Fixed Rate: Cow ₹{{ $farmer->cow_milk_rate ?? 0 }}, Buffalo ₹{{ $farmer->buffalo_milk_rate ?? 0 }}">
                                                💵 Fixed: ₹{{ $farmer->animal_type === 'buffalo' ? ($farmer->buffalo_milk_rate ?: $farmer->cow_milk_rate ?: 0) : ($farmer->cow_milk_rate ?: $farmer->buffalo_milk_rate ?: 0) }}/L
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200" title="{{ $farmer->rateChart ? $farmer->rateChart->name : 'Standard Quality Chart' }}">
                                                📊 {{ $farmer->rateChart ? \Illuminate\Support\Str::limit($farmer->rateChart->name, 13) : 'Std Chart' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Assigned Vehicle -->
                            <td class="py-3 px-4">
                                @if($farmer->vehicle)
                                    <span class="inline-flex items-center gap-1 text-slate-700 font-medium">
                                        <i data-lucide="truck" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>{{ ucfirst($farmer->vehicle) }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Assigned Route -->
                            <td class="py-3 px-4">
                                @if($farmer->route)
                                    <span class="font-medium text-slate-700 block truncate max-w-[140px]" title="{{ $farmer->route->name }}">
                                        {{ $farmer->route->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $farmer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($farmer->status === 'blocked' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $farmer->status }}
                                </span>
                            </td>

                            <!-- Actions Matching Reference (View eye, Edit pencil, Delete trash) -->
                            <td class="py-3 px-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- View Modal Button -->
                                    <button type="button" 
                                            @click="openViewModal({{ json_encode($farmerViewData) }})"
                                            title="View Details" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Edit Modal Button -->
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($farmerViewData) }})"
                                            title="Edit Farmer" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('farmers.destroy', $farmer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete farmer {{ addslashes($farmer->name) }} ({{ $farmer->farmer_code }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Delete Farmer" 
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="user-x" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No farmers found matching filters.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Add Farmer" to onboard new milk producers or clear filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($farmers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $farmers->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: ADD / EDIT FARMER (4 CLEAN SECTIONS)       -->
    <!-- Matching Reference media_1791383754777.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl border border-slate-100 relative my-6 max-h-[90vh] overflow-y-auto"
             @click.away="closeModal()">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 sticky -top-6 bg-white z-10 pt-1">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="isEditMode ? 'Edit Farmer Registration / किसान विवरण संपादित करें' : 'Farmer Registration Form / किसान पंजीकरण फॉर्म'"></h3>
                        <p class="text-xs text-slate-400">Manage personal details, vehicle, route, milk rate chart, bank details, and financial funds.</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form with 4 Organized Sections -->
            <form :action="formAction" method="POST" enctype="multipart/form-data" class="mt-4 space-y-6">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- SECTION 1: PERSONAL DETAILS / व्यक्तिगत जानकारी -->
                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                        <i data-lucide="user" class="w-4 h-4 text-emerald-600"></i>
                        <span>PERSONAL DETAILS / व्यक्तिगत जानकारी</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-1">
                        <!-- Farmer Code -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Farmer Code / किसान कोड <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="farmer_code" x-model="form.farmer_code" required placeholder="e.g. FMR0023"
                                   class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Farmer Image Upload -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Farmer Image / किसान फोटो
                            </label>
                            <input type="file" name="photo" accept="image/*"
                                   class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 bg-white border border-slate-200 rounded-lg">
                        </div>

                        <!-- First Name (English) -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                First Name (English) / नाम (अंग्रेजी) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="form.name" required placeholder="e.g. Kunal Sharma"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- पहला नाम (हिंदी) -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                पहला नाम (हिंदी) / Hindi Name
                            </label>
                            <input type="text" name="name_hi" x-model="form.name_hi" placeholder="e.g. कुणाल शर्मा"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Mobile Number / मोबाइल नंबर
                            </label>
                            <input type="text" name="phone" x-model="form.phone" placeholder="e.g. 9876543210"
                                   class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Village -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Village / गाँव
                            </label>
                            <input type="text" name="village" x-model="form.village" placeholder="e.g. Rampur / Palasia"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Vehicle -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Vehicle / वाहन
                            </label>
                            <select name="vehicle" x-model="form.vehicle" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="">Select Vehicle</option>
                                <option value="Van">Van</option>
                                <option value="Car">Car</option>
                                <option value="Tanker">Tanker</option>
                                <option value="Bike">Bike</option>
                                <option value="Tractor">Tractor</option>
                                <option value="Auto">Auto / Loader</option>
                                <option value="Other">Other / अन्य</option>
                            </select>
                        </div>

                        <!-- Assign Route -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Assign Route / मार्ग असाइन करें
                            </label>
                            <select name="route_id" x-model="form.route_id" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="">Select Route</option>
                                @foreach($routes as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }} ({{ $rt->code ?? 'RT-'.$rt->id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: MILK DETAILS & RATES / दूध एवं दर विवरण -->
                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                        <i data-lucide="milk" class="w-4 h-4 text-emerald-600"></i>
                        <span>MILK DETAILS & PRICING / दूध एवं दर विवरण</span>
                    </div>

                    <!-- Pricing Scheme Selector -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs">
                        <label class="block text-[11px] font-bold text-slate-800 mb-2">
                            Pricing Type / दर निर्धारण का प्रकार <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <label class="flex items-center gap-2.5 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                                   :class="form.pricing_type === 'rate_chart' ? 'bg-emerald-50 border-emerald-500 text-emerald-950 font-bold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600'">
                                <input type="radio" name="pricing_type" value="rate_chart" x-model="form.pricing_type" class="text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                                <div>
                                    <span class="block font-bold">📊 Rate Chart (दर चार्ट)</span>
                                    <span class="text-[9.5px] text-slate-400 font-normal">FAT / SNF Quality Formula</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                                   :class="form.pricing_type === 'fixed_rate' ? 'bg-emerald-50 border-emerald-500 text-emerald-950 font-bold shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600'">
                                <input type="radio" name="pricing_type" value="fixed_rate" x-model="form.pricing_type" class="text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
                                <div>
                                    <span class="block font-bold">💵 Fixed Rate (निश्चित दर)</span>
                                    <span class="text-[9.5px] text-slate-400 font-normal">Direct Per-Liter Price</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <!-- Milk Type -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Milk Type / दूध का प्रकार <span class="text-rose-500">*</span>
                            </label>
                            <select name="animal_type" x-model="form.animal_type" required class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="cow">Cow Milk (गाय का दूध)</option>
                                <option value="buffalo">Buffalo Milk (भैंस का दूध)</option>
                                <option value="mixed">Mixed Milk (मिश्रित दूध)</option>
                            </select>
                        </div>

                        <!-- If Rate Chart: Rate Chart Applied -->
                        <div class="sm:col-span-2" x-show="form.pricing_type === 'rate_chart'">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Rate Chart Applied / लागू दर चार्ट
                            </label>
                            <select name="rate_chart_id" x-model="form.rate_chart_id" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none font-semibold">
                                <option value="">-- Standard Quality Chart (मानक दर चार्ट) --</option>
                                @foreach($rateCharts as $rc)
                                    <option value="{{ $rc->id }}">{{ $rc->name }} ({{ ucfirst($rc->milk_type) }} • ₹{{ number_format((float)$rc->fixed_rate, 2) }}/L)</option>
                                @endforeach
                            </select>
                            <span class="text-[9.5px] text-slate-400 mt-0.5 block">Only active rate charts are listed.</span>
                        </div>

                        <!-- If Fixed Rate: Cow & Buffalo Milk Rates -->
                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-2" x-show="form.pricing_type === 'fixed_rate'">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Cow Milk Rate (₹/L) / गाय दर <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="cow_milk_rate" x-model="form.cow_milk_rate" placeholder="e.g. 60.00"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Buffalo Milk Rate (₹/L) / भैंस दर <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="buffalo_milk_rate" x-model="form.buffalo_milk_rate" placeholder="e.g. 70.00"
                                       class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none font-semibold">
                            </div>
                        </div>

                        <!-- Collection Center -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Collection Center / संकलन केंद्र
                            </label>
                            <select name="collection_center_id" x-model="form.collection_center_id" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Main Center --</option>
                                @foreach($centers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: BANK DETAILS / बैंक विवरण -->
                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                        <i data-lucide="landmark" class="w-4 h-4 text-emerald-600"></i>
                        <span>BANK DETAILS / बैंक विवरण</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-1">
                        <!-- Branch -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Branch / शाखा
                            </label>
                            <input type="text" name="branch_name" x-model="form.branch_name" placeholder="Enter Branch"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Bank Name -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Bank Name / बैंक का नाम
                            </label>
                            <input type="text" name="bank_name" x-model="form.bank_name" placeholder="Enter Bank Name"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Account Number -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Account Number / खाता संख्या
                            </label>
                            <input type="text" name="account_number" x-model="form.account_number" placeholder="Enter Account Number"
                                   class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Confirm Account Number -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Confirm Account Number / खाता संख्या पुष्टि
                            </label>
                            <input type="text" name="confirm_account_number" x-model="form.confirm_account_number" placeholder="Re-enter Account Number"
                                   class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- IFSC Code -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                IFSC Code / आईएफएससी कोड
                            </label>
                            <input type="text" name="ifsc_code" x-model="form.ifsc_code" placeholder="Enter IFSC Code"
                                   class="w-full px-3 py-1.5 text-xs font-mono uppercase bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                Status / स्थिति
                            </label>
                            <select name="status" x-model="form.status" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="active">Active (सक्रिय)</option>
                                <option value="inactive">Inactive (निष्क्रिय)</option>
                                <option value="blocked">Blocked (अवरुद्ध)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: EXTRA INFORMATION (DYNAMIC ADDITIONS & DEDUCTIONS) / अतिरिक्त जानकारी -->
                <div class="bg-slate-50/60 rounded-xl p-4 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                            <i data-lucide="sliders" class="w-4 h-4 text-emerald-600"></i>
                            <span>EXTRA INFORMATION / अतिरिक्त जानकारी (Dynamic Additions & Deductions)</span>
                        </div>
                        <a href="{{ route('settings.center-information') }}" target="_blank" class="text-[10px] text-emerald-700 hover:underline flex items-center gap-1 font-semibold">
                            <i data-lucide="settings" class="w-3 h-3"></i>
                            <span>Center Settings</span>
                        </a>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Only categories configured as <strong>"Farmer Wise"</strong> in Center Information are shown below. "All Farmers" rates are applied automatically.
                    </p>

                    @if(!empty($farmerWiseAdditions))
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Farmer-Wise Additions (+):</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($farmerWiseAdditions as $add)
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                            {{ $add['name'] }} (₹ {{ $add['unit'] ? '/ ' . ltrim($add['unit'], '/') : '' }})
                                        </label>
                                        <input type="number" step="0.01" min="0" 
                                               name="extra_rates[{{ $add['name'] }}]" 
                                               x-model="form.extra_rates['{{ $add['name'] }}']" 
                                               placeholder="0.00"
                                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none font-semibold">
                                        <span class="text-[9px] text-slate-400">Applied per {{ ltrim($add['unit'] ?? 'ltr', '/') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($farmerWiseDeductions))
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/60">
                            <span class="text-[10px] font-bold text-rose-800 uppercase tracking-wider block">Farmer-Wise Deductions (-):</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($farmerWiseDeductions as $ded)
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                            {{ $ded['name'] }} (₹ {{ $ded['unit'] ? '/ ' . ltrim($ded['unit'], '/') : '' }})
                                        </label>
                                        <input type="number" step="0.01" min="0" 
                                               name="extra_rates[{{ $ded['name'] }}]" 
                                               x-model="form.extra_rates['{{ $ded['name'] }}']" 
                                               placeholder="0.00"
                                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none font-semibold">
                                        <span class="text-[9px] text-slate-400">Applied as {{ $ded['unit'] ?? 'fixed' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(empty($farmerWiseAdditions) && empty($farmerWiseDeductions))
                        <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                            All addition and deduction types are configured as "All Farmers". No individual inputs required.
                        </div>
                    @endif

                    <!-- Central All-Farmers Transparency Notice -->
                    @if(!empty($allFarmersAdditions) || !empty($allFarmersDeductions))
                        <div class="p-2.5 bg-slate-100 rounded-lg text-[10px] text-slate-600 flex flex-wrap gap-1.5 items-center">
                            <span class="font-bold text-slate-700">Center-Wide (All Farmers):</span>
                            @foreach($allFarmersAdditions as $afa)
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded font-bold">
                                    + {{ $afa['name'] }}: ₹{{ $afa['amount'] }}{{ $afa['unit'] ?? '/ltr' }}
                                </span>
                            @endforeach
                            @foreach($allFarmersDeductions as $afd)
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded font-bold">
                                    - {{ $afd['name'] }}: ₹{{ $afd['amount'] }}{{ $afd['unit'] ?? '/ltr' }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Installment Deduction Preference (Image 4 Style) -->
                    <div class="pt-2 border-t border-slate-200/60">
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Installment :</label>
                                <div @click="openFarmerIndexInstallmentModal()" 
                                     class="inline-flex items-center gap-2.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 hover:border-teal-500 rounded-lg text-xs font-mono font-bold text-slate-900 shadow-2xs cursor-pointer transition"
                                     title="Click to configure Installment">
                                    <span x-text="form.installment_deduction_type === 'Fixed' ? Number(form.installment || 0).toFixed(2) + ' ₹' : (form.installment_deduction_type || 'Auto')"></span>
                                    <span class="text-sky-500 hover:text-sky-700 transition" title="Edit Installment">
                                        <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-400">Configure loan / advance repayment deduction on settlements</span>
                        </div>

                        <!-- Hidden inputs for form submit -->
                        <input type="hidden" name="installment" :value="form.installment">
                        <input type="hidden" name="installment_deduction_type" :value="form.installment_deduction_type">
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="resetForm()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Reset Form
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span x-text="isEditMode ? 'Update Farmer' : 'Save Farmer'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: BULK EXCEL / CSV UPLOAD                   -->
    <!-- Matching Reference media_1791383776893.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showBulkModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showBulkModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showBulkModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Upload Excel Sheet / बल्क किसान अपलोड</h3>
                </div>
                <button type="button" @click="showBulkModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Upload Box matching reference screenshot -->
            <form action="{{ route('farmers.bulk-import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div class="p-6 border-2 border-dashed border-emerald-200 rounded-2xl bg-emerald-50/30 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="cloud-upload" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800">Upload Farmer Excel File</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Supported Format : .xlsx / .xls / .csv</p>
                    </div>

                    <div class="max-w-xs mx-auto">
                        <input type="file" name="file" required accept=".csv,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                               class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 bg-white border border-slate-200 rounded-lg p-1">
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('farmers.sample-template') }}" class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 underline">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Download Sample CSV Template</span>
                        </a>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="showBulkModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload & Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: QUICK VIEW FARMER PROFILE                 -->
    <!-- ============================================================== -->
    <div x-show="showViewModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showViewModal = false">

        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showViewModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <img :src="viewData.photo_url" alt="" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-2xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-slate-900" x-text="viewData.name"></h3>
                            <span class="text-xs text-slate-500 font-medium" x-show="viewData.name_hi" x-text="'(' + viewData.name_hi + ')'"></span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" x-text="viewData.farmer_code"></span>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full"
                                  :class="viewData.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                                  x-text="viewData.status"></span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="showViewModal = false" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Profile Details Grid -->
            <div class="mt-4 space-y-4 text-xs">
                <!-- Contact & Route Info -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Phone</span>
                        <p class="font-mono font-semibold text-slate-800 mt-0.5" x-text="viewData.phone || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Village</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.village || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Assigned Route</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.route_name || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Vehicle</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.vehicle || '—'"></p>
                    </div>
                </div>

                <!-- Milk & Rate Details -->
                <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Milk Type</span>
                        <p class="font-bold text-emerald-700 capitalize mt-0.5" x-text="viewData.animal_type"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Cow Milk Rate</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.cow_milk_rate ? '₹ ' + viewData.cow_milk_rate + '/L' : 'As per Rate Chart'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Buffalo Milk Rate</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.buffalo_milk_rate ? '₹ ' + viewData.buffalo_milk_rate + '/L' : 'As per Rate Chart'"></p>
                    </div>
                </div>

                <!-- Bank Info -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Bank Name</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.bank_name || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Branch</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.branch_name || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Account Number</span>
                        <p class="font-mono font-semibold text-slate-800 mt-0.5" x-text="viewData.account_number || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">IFSC Code</span>
                        <p class="font-mono font-semibold text-slate-800 mt-0.5" x-text="viewData.ifsc_code || '—'"></p>
                    </div>
                </div>

                <!-- Financial Ledger & Funds -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 p-3 bg-emerald-50/40 rounded-xl border border-emerald-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Anamat</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="'₹ ' + Number(viewData.anamat || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Building Fund</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="'₹ ' + Number(viewData.building_fund || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Installment</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="'₹ ' + Number(viewData.installment || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Etc Amount</span>
                        <p class="font-bold text-slate-800 mt-0.5" x-text="'₹ ' + Number(viewData.etc_amount || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Grant Amount</span>
                        <p class="font-bold text-emerald-700 mt-0.5" x-text="'₹ ' + Number(viewData.grant_amount || 0).toFixed(2)"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-4">
                <a :href="'/farmers/' + viewData.id" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold transition">
                    <i data-lucide="book-open" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Open Full Passbook & Collections</span>
                </a>
                <button type="button" @click="showViewModal = false" class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Extra Information Modal (Matching Images 1, 2, 3) -->
    <div x-show="showInstallmentModal" 
         x-cloak 
         class="fixed inset-0 z-70 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl border border-slate-200 overflow-hidden" 
             @click.away="closeFarmerIndexInstallmentModal()">
            <!-- Header: #98d8c8 mint/teal -->
            <div class="px-5 py-3.5 flex items-center justify-between" style="background-color: #98d8c8;">
                <h3 class="text-sm font-bold text-slate-800 tracking-wide">Extra Information</h3>
                <button type="button" @click="closeFarmerIndexInstallmentModal()" class="text-slate-600 hover:text-slate-900 font-bold text-lg leading-none cursor-pointer">
                    &times;
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4 text-xs">
                <div class="flex items-center justify-between gap-4">
                    <span class="font-bold text-slate-700">Name :</span>
                    <span class="font-bold text-slate-900">Installment</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="font-bold text-slate-700">Deduction Type :</label>
                    <select x-model="installmentModalType" class="w-36 px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-medium text-slate-800 outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="Auto">Auto</option>
                        <option value="DeductionOff">DeductionOff</option>
                        <option value="Fixed">Fixed</option>
                    </select>
                </div>

                <div x-show="installmentModalType === 'Fixed'" class="flex items-center justify-between gap-4">
                    <label class="font-bold text-slate-700">Amount :</label>
                    <input type="number" step="0.01" min="0" x-model="installmentModalAmount" placeholder="0.00" class="w-36 px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-mono font-bold text-slate-900 outline-none focus:ring-1 focus:ring-teal-500">
                </div>
            </div>

            <!-- Footer Buttons: No (grey) and Save (teal #388e7e) -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" @click="closeFarmerIndexInstallmentModal()" class="px-4 py-1.5 bg-slate-300 hover:bg-slate-400 text-slate-800 font-bold text-xs rounded-lg transition cursor-pointer">
                    No
                </button>
                <button type="button" @click="saveFarmerIndexInstallmentModal()" class="px-5 py-1.5 text-white font-bold text-xs rounded-lg shadow-xs transition cursor-pointer" style="background-color: #388e7e;">
                    Save
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function farmerManager() {
    return {
        showModal: false,
        showBulkModal: false,
        showViewModal: false,
        showInstallmentModal: false,
        installmentModalType: 'Auto',
        installmentModalAmount: '0.00',
        isEditMode: false,
        formAction: '{{ route("farmers.store") }}',

        form: {
            id: null,
            farmer_code: '{{ $nextCode }}',
            name: '',
            name_hi: '',
            phone: '',
            village: '',
            address: '',
            vehicle: '',
            route_id: '',
            branch_id: null,
            collection_center_id: '{{ $centers->first()->id ?? "" }}',
            pricing_type: 'rate_chart',
            rate_chart_id: '',
            animal_type: 'cow',
            cow_milk_rate: '',
            buffalo_milk_rate: '',
            branch_name: '',
            bank_name: '',
            account_number: '',
            confirm_account_number: '',
            ifsc_code: '',
            upi_id: '',
            anamat: '0.00',
            building_fund: '0.00',
            installment: '0.00',
            installment_deduction_type: 'Auto',
            etc_amount: '0.00',
            grant_amount: '0.00',
            extra_rates: {},
            status: 'active'
        },

        viewData: {},

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('open_create')) {
                this.openCreateModal();
            }
        },

        openCreateModal() {
            this.isEditMode = false;
            this.formAction = '{{ route("farmers.store") }}';
            this.resetForm();
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openEditModal(item) {
            this.isEditMode = true;
            this.formAction = `/farmers/${item.id}`;
            const extra = { ...(item.extra_rates || {}) };
            if (item.building_fund && !extra['Building fund']) extra['Building fund'] = item.building_fund;
            if (item.grant_amount && !extra['Grant']) extra['Grant'] = item.grant_amount;
            if (item.anamat && !extra['Anamat']) extra['Anamat'] = item.anamat;

            this.form = {
                id: item.id,
                farmer_code: item.farmer_code || '',
                name: item.name || '',
                name_hi: item.name_hi || '',
                phone: item.phone || '',
                village: item.village || '',
                address: item.address || '',
                vehicle: item.vehicle || '',
                route_id: item.route_id || '',
                branch_id: item.branch_id || null,
                collection_center_id: item.collection_center_id || '',
                pricing_type: item.pricing_type || (item.cow_milk_rate > 0 || item.buffalo_milk_rate > 0 ? 'fixed_rate' : 'rate_chart'),
                rate_chart_id: item.rate_chart_id || '',
                animal_type: item.animal_type || 'cow',
                cow_milk_rate: item.cow_milk_rate || '',
                buffalo_milk_rate: item.buffalo_milk_rate || '',
                branch_name: item.branch_name || '',
                bank_name: item.bank_name || '',
                account_number: item.account_number || '',
                confirm_account_number: item.account_number || '',
                ifsc_code: item.ifsc_code || '',
                upi_id: item.upi_id || '',
                anamat: item.anamat || '0.00',
                building_fund: item.building_fund || '0.00',
                installment: item.installment || '0.00',
                installment_deduction_type: item.installment_deduction_type || 'Auto',
                etc_amount: item.etc_amount || '0.00',
                grant_amount: item.grant_amount || '0.00',
                extra_rates: extra,
                status: item.status || 'active'
            };
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openViewModal(item) {
            this.viewData = { ...item };
            this.showViewModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openBulkModal() {
            this.showBulkModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        closeModal() {
            this.showModal = false;
        },

        openFarmerIndexInstallmentModal() {
            this.installmentModalType = this.form.installment_deduction_type || 'Auto';
            this.installmentModalAmount = parseFloat(this.form.installment) > 0 ? parseFloat(this.form.installment).toFixed(2) : '0.00';
            this.showInstallmentModal = true;
        },

        closeFarmerIndexInstallmentModal() {
            this.showInstallmentModal = false;
        },

        saveFarmerIndexInstallmentModal() {
            this.form.installment_deduction_type = this.installmentModalType;
            this.form.installment = (this.installmentModalType === 'Fixed') ? (parseFloat(this.installmentModalAmount) || 0).toFixed(2) : '0.00';

            // If editing an existing farmer, also sync immediately with DB via AJAX
            if (this.isEditMode && this.form.id) {
                fetch(`/farmers/${this.form.id}/installment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        deduction_type: this.form.installment_deduction_type,
                        installment: this.form.installment
                    })
                }).catch(err => console.log('Notice: auto-sync installment:', err));
            }

            this.showInstallmentModal = false;
        },

        resetForm() {
            this.form = {
                id: null,
                farmer_code: '{{ $nextCode }}',
                name: '',
                name_hi: '',
                phone: '',
                village: '',
                address: '',
                vehicle: '',
                route_id: '',
                branch_id: null,
                collection_center_id: '{{ $centers->first()->id ?? "" }}',
                pricing_type: 'rate_chart',
                rate_chart_id: '',
                animal_type: 'cow',
                cow_milk_rate: '',
                buffalo_milk_rate: '',
                branch_name: '',
                bank_name: '',
                account_number: '',
                confirm_account_number: '',
                ifsc_code: '',
                upi_id: '',
                anamat: '0.00',
                building_fund: '0.00',
                installment: '0.00',
                installment_deduction_type: 'Auto',
                etc_amount: '0.00',
                grant_amount: '0.00',
                extra_rates: {},
                status: 'active'
            };
        }
    };
}
</script>
@endsection
