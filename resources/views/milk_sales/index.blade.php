@extends('layouts.app')

@section('title', 'Milk Sale')
@section('breadcrumb', 'Milk Sale')
@section('header_title', 'Milk Sale & Outward Delivery')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" onclick="openAddVehicleModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="truck" class="w-4 h-4 text-emerald-600"></i>
            <span>+ Add Vehicle</span>
        </button>
        <button type="button" onclick="openAddBuyerModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="user-plus" class="w-4 h-4 text-emerald-600"></i>
            <span>+ Add Buyer</span>
        </button>
        <a href="{{ route('buyers.khata') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>Buyer Khata</span>
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

    <!-- ================= 1. TOP CARD: MILK SALE ENTRY FORM matching media_1791389566806.png ================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" id="saleFormCard">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Milk Sale Entry</h3>
                    <p class="text-[11px] text-slate-500">Record direct bulk or commercial milk sale</p>
                </div>
            </div>
            <button type="button" onclick="openAddVehicleModal()" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>+ Add Vehicle</span>
            </button>
        </div>

        <form method="POST" action="{{ route('milk-sales.store') }}" id="milkSaleForm" class="p-4 sm:p-6 space-y-5">
            @csrf

            <!-- Row 1: Sale Date, Shift, Milk Type -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Sale Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sale Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="sale_date" id="saleDateInput" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-800">
                </div>

                <!-- Shift Selection (Toggle Buttons) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Shift <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="morning" checked class="peer sr-only" onchange="updateShiftStyle(this)">
                            <div class="shift-btn peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-xs py-2 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Morning
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="evening" class="peer sr-only" onchange="updateShiftStyle(this)">
                            <div class="shift-btn peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-xs py-2 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Evening
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Milk Type Selection (Toggle Buttons) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Milk Type <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="milk_type" value="cow" checked class="peer sr-only" onchange="handleMilkTypeChange(this)">
                            <div class="milk-btn peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-xs py-2 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Cow
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="milk_type" value="buffalo" class="peer sr-only" onchange="handleMilkTypeChange(this)">
                            <div class="milk-btn peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:shadow-xs py-2 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Buffalo
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Row 2: Customer, Milk Qty, FAT, CLR, SNF, Rate -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Customer / Buyer Dropdown -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700">Customer (Buyer) <span class="text-rose-500">*</span></label>
                        <button type="button" onclick="openAddBuyerModal()" class="text-[10px] text-emerald-600 hover:text-emerald-700 font-bold">
                            + New Buyer
                        </button>
                    </div>
                    <select name="buyer_id" id="buyerSelect" required onchange="onBuyerSelected(this)" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium text-slate-800">
                        <option value="">Select Customer...</option>
                        @foreach($buyers as $b)
                            <option value="{{ $b->id }}" 
                                    data-code="{{ $b->buyer_code }}"
                                    data-phone="{{ $b->phone }}"
                                    data-cow-rate="{{ $b->cow_fixed_rate }}"
                                    data-buffalo-rate="{{ $b->buffalo_fixed_rate }}"
                                    data-balance="{{ $b->current_balance }}">
                                [{{ $b->buyer_code }}] {{ $b->name }} (Bal: ₹{{ number_format($b->current_balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Milk Quantity (Liters) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Milk Qty. (Ltr.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity_liters" id="quantityInput" placeholder="0.00" required oninput="calculateTotal()" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-bold text-slate-900">
                </div>

                <!-- FAT (%) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">FAT (%)</label>
                    <input type="number" step="0.1" min="0" max="20" name="fat_percentage" id="fatInput" placeholder="0.00" oninput="calculateSnf()" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <!-- CLR -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">CLR</label>
                    <input type="number" step="0.5" min="0" max="50" name="clr_reading" id="clrInput" placeholder="0.00" oninput="calculateSnf()" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <!-- SNF (%) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">SNF (%)</label>
                    <input type="number" step="0.01" min="0" max="20" name="snf_percentage" id="snfInput" placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>
            </div>

            <!-- Row 3: Rate, Paid, Balance, Description, Vehicle -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Rate (₹/Ltr.) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Rate (₹/Ltr.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="rate_per_liter" id="rateInput" placeholder="0.00" required oninput="calculateTotal()" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-bold text-slate-900">
                </div>

                <!-- Paid Amount -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Paid (₹)</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="paidInput" placeholder="0.00" oninput="calculateBalance()" class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                </div>

                <!-- Balance Amount (Computed) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Balance (₹)</label>
                    <input type="number" step="0.01" name="balance_amount" id="balanceInput" placeholder="0.00" readonly class="w-full px-3.5 py-2.5 text-xs font-mono border border-slate-200 rounded-xl bg-slate-100 outline-none font-bold text-rose-700">
                </div>

                <!-- Description / Payment Notes -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Description / Payment Note</label>
                    <input type="text" name="description" id="descInput" placeholder="Cash, UPI Ref #, delivery remarks..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <!-- Bottom Row: Vehicle Selector & Total Amount Display & Submit Buttons -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                
                <!-- Vehicle Selector -->
                <div class="flex items-center gap-3">
                    <div class="min-w-[220px]">
                        <select name="vehicle_id" id="vehicleSelect" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-medium">
                            <option value="">No vehicle selected</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->vehicle_number }} - {{ ucfirst($v->vehicle_type) }} ({{ $v->driver_name ?? 'No Driver' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="openAddVehicleModal()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Vehicle</span>
                    </button>
                </div>

                <!-- Live Total Amount & Actions -->
                <div class="flex items-center gap-5">
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Amount</span>
                        <div class="text-xl sm:text-2xl font-black font-mono text-emerald-700 leading-tight">
                            ₹ <span id="totalAmountDisplay">0.00</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="reset" onclick="resetSaleForm()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Save Sale</span>
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- ================= 2. BOTTOM CARD: SALE LIST matching media_1791389566806.png ================= -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('milk-sales.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:border-emerald-500 outline-none font-medium">
                </div>

                <div class="flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $toDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:border-emerald-500 outline-none font-medium">
                </div>

                <div class="min-w-[180px]">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Customer</label>
                    <select name="customer_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Customers</option>
                        @foreach($buyers as $b)
                            <option value="{{ $b->id }}" {{ $customerId == $b->id ? 'selected' : '' }}>[{{ $b->buyer_code }}] {{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[180px] flex-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Code, name, phone, vehicle..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:border-emerald-500 outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition h-[38px] flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Go</span>
                    </button>
                    <a href="{{ route('milk-sales.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Listing matching media_1791389566806.png -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3">Code</th>
                        <th class="py-2.5 px-3 min-w-[140px]">Customer Name</th>
                        <th class="py-2.5 px-3">Vehicle</th>
                        <th class="py-2.5 px-3">Shift</th>
                        <th class="py-2.5 px-3">Milk Type</th>
                        <th class="py-2.5 px-3 text-right">Liters</th>
                        <th class="py-2.5 px-3 text-right">FAT</th>
                        <th class="py-2.5 px-3 text-right">SNF</th>
                        <th class="py-2.5 px-3 text-right">CLR</th>
                        <th class="py-2.5 px-3 text-right">Rate</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                        <th class="py-2.5 px-3">Description</th>
                        <th class="py-2.5 px-3 text-center min-w-[100px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($sales as $s)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono text-[11px] font-bold text-slate-800">
                                {{ $s->buyer->buyer_code ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900 leading-tight">{{ $s->buyer->name ?? 'Unknown' }}</div>
                                @if(!empty($s->buyer->phone))
                                    <div class="text-[10px] text-slate-400">{{ $s->buyer->phone }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                @if($s->vehicle)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $s->vehicle->vehicle_number }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $s->shift === 'morning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                    {{ $s->shift }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $s->milk_type === 'cow' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $s->milk_type }} Milk
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($s->quantity_liters, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600">
                                {{ number_format($s->fat_percentage, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600">
                                {{ number_format($s->snf_percentage, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600">
                                {{ number_format($s->clr_reading, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">
                                ₹{{ number_format($s->rate_per_liter, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700">
                                ₹{{ number_format($s->total_amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-[140px] truncate" title="{{ $s->description }}">
                                {{ $s->description ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Print Slip Button -->
                                    <a href="{{ route('milk-sales.slip', $s->id) }}" target="_blank" title="Print Slip" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Edit Button -->
                                    <button type="button" onclick="openEditSaleModal({{ json_encode($s) }})" title="Edit" class="p-1.5 text-slate-500 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('milk-sales.destroy', $s->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete milk sale #{{ $s->sale_number }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="py-12 text-center text-slate-400">
                                <i data-lucide="shopping-cart" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No milk sales recorded for the selected period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-100 font-bold text-slate-800 border-t-2 border-slate-300 text-xs">
                            <td colspan="5" class="py-3 px-3 uppercase tracking-wider text-[11px]">
                                Total (Current Page / Filtered)
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-black text-slate-900">
                                {{ number_format($totalLiters, 2) }} L
                            </td>
                            <td colspan="4"></td>
                            <td class="py-3 px-3 text-right font-mono font-black text-emerald-800 text-sm">
                                ₹{{ number_format($totalAmount, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL: QUICK ADD VEHICLE ================= -->
<div id="quickAddVehicleModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">+ Add Vehicle</h3>
                    <p class="text-xs text-slate-500">Register delivery vehicle</p>
                </div>
            </div>
            <button type="button" onclick="closeAddVehicleModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="quickVehicleForm" onsubmit="submitQuickVehicle(event)" class="mt-4 space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Vehicle Number <span class="text-rose-500">*</span></label>
                <input type="text" name="vehicle_number" required placeholder="e.g. JH0AB1125" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono font-bold">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle Type <span class="text-rose-500">*</span></label>
                    <select name="vehicle_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <option value="van">Van</option>
                        <option value="car">Car</option>
                        <option value="tanker">Tanker</option>
                        <option value="auto">Auto</option>
                        <option value="bike">Bike</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Capacity (Liters)</label>
                    <input type="number" step="0.01" name="capacity" placeholder="e.g. 50" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Name</label>
                    <input type="text" name="driver_name" placeholder="Full name" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Phone</label>
                    <input type="text" name="driver_phone" placeholder="10-digit phone" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Assigned Route</label>
                <input type="text" name="assigned_route" placeholder="e.g. Main Market Route" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddVehicleModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs">Save Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: QUICK ADD BUYER ================= -->
<div id="quickAddBuyerModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">+ Add Milk Buyer</h3>
                    <p class="text-xs text-slate-500">Commercial customer profile</p>
                </div>
            </div>
            <button type="button" onclick="closeAddBuyerModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="quickBuyerForm" onsubmit="submitQuickBuyer(event)" class="mt-4 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Buyer Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="buyer_code" required value="BUY{{ rand(100, 999) }}" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-bold uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Buyer Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Full Name" class="w-full px-3.5 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="10-digit mobile" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Milk Type <span class="text-rose-500">*</span></label>
                    <select name="milk_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <option value="both">Both (Cow & Buffalo)</option>
                        <option value="cow">Cow Milk</option>
                        <option value="buffalo">Buffalo Milk</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cow Fixed Rate (₹)</label>
                    <input type="number" step="0.01" name="cow_fixed_rate" placeholder="0.00" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Buffalo Fixed Rate (₹)</label>
                    <input type="number" step="0.01" name="buffalo_fixed_rate" placeholder="0.00" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Address / Village</label>
                <input type="text" name="address" placeholder="Address, area, village..." class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddBuyerModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs">Save Buyer</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT MILK SALE ================= -->
<div id="editMilkSaleModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="editModalTitle">Edit Milk Sale</h3>
                    <p class="text-xs text-slate-500">Update sale transaction details</p>
                </div>
            </div>
            <button type="button" onclick="closeEditSaleModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editSaleForm" method="POST" class="mt-4 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Sale Date</label>
                    <input type="date" name="sale_date" id="editSaleDate" required class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Shift</label>
                    <select name="shift" id="editShift" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        <option value="morning">Morning</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Customer / Buyer</label>
                    <select name="buyer_id" id="editBuyerId" required class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        @foreach($buyers as $b)
                            <option value="{{ $b->id }}">[{{ $b->buyer_code }}] {{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Milk Type</label>
                    <select name="milk_type" id="editMilkType" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        <option value="cow">Cow</option>
                        <option value="buffalo">Buffalo</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Quantity (L)</label>
                    <input type="number" step="0.01" name="quantity_liters" id="editQty" required oninput="recalcEditTotal()" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rate (₹)</label>
                    <input type="number" step="0.01" name="rate_per_liter" id="editRate" required oninput="recalcEditTotal()" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Total (₹)</label>
                    <input type="text" id="editTotal" readonly class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-100 outline-none font-bold text-emerald-700">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">FAT (%)</label>
                    <input type="number" step="0.1" name="fat_percentage" id="editFat" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">CLR</label>
                    <input type="number" step="0.5" name="clr_reading" id="editClr" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">SNF (%)</label>
                    <input type="number" step="0.01" name="snf_percentage" id="editSnf" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Paid (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" id="editPaid" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle</label>
                    <select name="vehicle_id" id="editVehicleId" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                        <option value="">None</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->vehicle_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Description</label>
                <input type="text" name="description" id="editDesc" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditSaleModal()" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-xs">Update Sale</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Live Calculation Logic for Top Sale Entry Form
    function calculateTotal() {
        const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
        const rate = parseFloat(document.getElementById('rateInput').value) || 0;
        const total = (qty * rate).toFixed(2);
        document.getElementById('totalAmountDisplay').textContent = total;
        calculateBalance();
    }

    function calculateBalance() {
        const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
        const rate = parseFloat(document.getElementById('rateInput').value) || 0;
        const total = qty * rate;
        const paid = parseFloat(document.getElementById('paidInput').value) || 0;
        const balance = Math.max(0, (total - paid)).toFixed(2);
        document.getElementById('balanceInput').value = balance;
    }

    // Auto-calculate SNF from CLR and FAT: ISI Formula: (CLR/4) + (0.21 * FAT) + 0.36
    function calculateSnf() {
        const fat = parseFloat(document.getElementById('fatInput').value) || 0;
        const clr = parseFloat(document.getElementById('clrInput').value) || 0;
        if (clr > 0) {
            const snf = ((clr / 4) + (0.21 * fat) + 0.36).toFixed(2);
            document.getElementById('snfInput').value = snf;
        }
    }

    // When buyer selected, auto-populate fixed rate based on selected milk type
    function onBuyerSelected(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const milkType = document.querySelector('input[name="milk_type"]:checked').value;
        const cowRate = parseFloat(selectedOption.getAttribute('data-cow-rate')) || 0;
        const buffaloRate = parseFloat(selectedOption.getAttribute('data-buffalo-rate')) || 0;

        const rateInput = document.getElementById('rateInput');
        if (milkType === 'cow' && cowRate > 0) {
            rateInput.value = cowRate;
        } else if (milkType === 'buffalo' && buffaloRate > 0) {
            rateInput.value = buffaloRate;
        }
        calculateTotal();
    }

    function handleMilkTypeChange(radio) {
        const select = document.getElementById('buyerSelect');
        onBuyerSelected(select);
    }

    function resetSaleForm() {
        document.getElementById('totalAmountDisplay').textContent = '0.00';
        document.getElementById('balanceInput').value = '';
    }

    // Modals
    function openAddVehicleModal() {
        document.getElementById('quickAddVehicleModal').classList.remove('hidden');
    }
    function closeAddVehicleModal() {
        document.getElementById('quickAddVehicleModal').classList.add('hidden');
    }

    function openAddBuyerModal() {
        document.getElementById('quickAddBuyerModal').classList.remove('hidden');
    }
    function closeAddBuyerModal() {
        document.getElementById('quickAddBuyerModal').classList.add('hidden');
    }

    // Submit Quick Vehicle via AJAX
    function submitQuickVehicle(e) {
        e.preventDefault();
        const form = document.getElementById('quickVehicleForm');
        const formData = new FormData(form);

        fetch("{{ route('vehicles.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add to vehicle dropdown
                const vSelect = document.getElementById('vehicleSelect');
                const opt = document.createElement('option');
                opt.value = data.vehicle.id;
                opt.textContent = `${data.vehicle.vehicle_number} - ${data.vehicle.vehicle_type} (${data.vehicle.driver_name || 'No Driver'})`;
                opt.selected = true;
                vSelect.appendChild(opt);
                closeAddVehicleModal();
                form.reset();
                alert(data.message);
            }
        })
        .catch(err => {
            alert('Failed to add vehicle. Please check inputs.');
        });
    }

    // Submit Quick Buyer via AJAX
    function submitQuickBuyer(e) {
        e.preventDefault();
        const form = document.getElementById('quickBuyerForm');
        const formData = new FormData(form);

        fetch("{{ route('buyers.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add to buyer dropdown
                const bSelect = document.getElementById('buyerSelect');
                const opt = document.createElement('option');
                opt.value = data.buyer.id;
                opt.setAttribute('data-cow-rate', data.buyer.cow_fixed_rate);
                opt.setAttribute('data-buffalo-rate', data.buyer.buffalo_fixed_rate);
                opt.textContent = `[${data.buyer.buyer_code}] ${data.buyer.name} (Bal: ₹0.00)`;
                opt.selected = true;
                bSelect.appendChild(opt);
                closeAddBuyerModal();
                form.reset();
                onBuyerSelected(bSelect);
                alert(data.message);
            }
        })
        .catch(err => {
            alert('Failed to register buyer. Please check inputs.');
        });
    }

    // Edit Sale Modal
    function openEditSaleModal(sale) {
        document.getElementById('editModalTitle').textContent = `Edit Milk Sale #${sale.sale_number}`;
        document.getElementById('editSaleForm').action = `/milk-sales/${sale.id}`;
        document.getElementById('editSaleDate').value = (sale.sale_date || '').split('T')[0];
        document.getElementById('editShift').value = sale.shift;
        document.getElementById('editBuyerId').value = sale.buyer_id;
        document.getElementById('editMilkType').value = sale.milk_type;
        document.getElementById('editQty').value = sale.quantity_liters;
        document.getElementById('editRate').value = sale.rate_per_liter;
        document.getElementById('editFat').value = sale.fat_percentage;
        document.getElementById('editClr').value = sale.clr_reading;
        document.getElementById('editSnf').value = sale.snf_percentage;
        document.getElementById('editPaid').value = sale.paid_amount;
        document.getElementById('editVehicleId').value = sale.vehicle_id || '';
        document.getElementById('editDesc').value = sale.description || '';
        recalcEditTotal();
        document.getElementById('editMilkSaleModal').classList.remove('hidden');
    }

    function closeEditSaleModal() {
        document.getElementById('editMilkSaleModal').classList.add('hidden');
    }

    function recalcEditTotal() {
        const qty = parseFloat(document.getElementById('editQty').value) || 0;
        const rate = parseFloat(document.getElementById('editRate').value) || 0;
        document.getElementById('editTotal').value = '₹' + (qty * rate).toFixed(2);
    }
</script>
@endpush
