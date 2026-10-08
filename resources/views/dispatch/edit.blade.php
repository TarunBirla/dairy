@extends('layouts.app')

@section('title', 'Edit Dispatch')
@section('breadcrumb', 'Dispatch / Edit')
@section('header_title', 'Update Milk Dispatch Outward')

@section('header_action')
    <a href="{{ route('dispatch.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
        <i class="fa-solid fa-list-ul text-xs"></i>
        <span>Dispatch List</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dispatch.update', $dispatch->id) }}" method="POST" id="dispatchForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- CARD 1: Header / Schedule Details (Matching Screenshot 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="bg-gradient-to-r from-[#002e79] to-[#001f52] px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-pen-to-square text-amber-400 text-base"></i>
                    <h3 class="text-sm font-bold tracking-wide">Edit Dispatch Challan #{{ $dispatch->challan_number ?? $dispatch->dispatch_number }}</h3>
                </div>
                <a href="{{ route('dispatch.index') }}" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-list-ul text-xs"></i>
                    <span>Dispatch List</span>
                </a>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <!-- From Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">From Date *</label>
                    <input type="date" name="from_date" id="from_date" value="{{ old('from_date', $dispatch->from_date ? $dispatch->from_date->format('Y-m-d') : ($dispatch->dispatch_date ? $dispatch->dispatch_date->format('Y-m-d') : date('Y-m-d'))) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- From Shift -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">From Shift *</label>
                    <select name="from_shift" id="from_shift" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Morning" {{ old('from_shift', $dispatch->from_shift ?? $dispatch->shift) == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ old('from_shift', $dispatch->from_shift ?? $dispatch->shift) == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">To Date *</label>
                    <input type="date" name="to_date" id="to_date" value="{{ old('to_date', $dispatch->to_date ? $dispatch->to_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- To Shift -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">To Shift *</label>
                    <select name="to_shift" id="to_shift" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Morning" {{ old('to_shift', $dispatch->to_shift ?? $dispatch->shift) == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ old('to_shift', $dispatch->to_shift ?? $dispatch->shift) == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>

                <!-- Milk Type in Header -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type *</label>
                    <select name="header_milk_type" id="header_milk_type" class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white" onchange="syncMilkType(this.value)">
                        <option value="Cow" {{ old('header_milk_type', $dispatch->milk_type) == 'Cow' ? 'selected' : '' }}>Cow</option>
                        <option value="Buffalo" {{ old('header_milk_type', $dispatch->milk_type) == 'Buffalo' ? 'selected' : '' }}>Buffalo</option>
                        <option value="Mixed" {{ old('header_milk_type', $dispatch->milk_type) == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                    </select>
                </div>

                <!-- Challan Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Challan Date *</label>
                    <input type="date" name="challan_date" id="challan_date" value="{{ old('challan_date', $dispatch->challan_date ? $dispatch->challan_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- Can/Tanker Mode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Can / Tanker *</label>
                    <select name="dispatch_type" id="dispatch_type" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white" onchange="syncDispatchType(this.value)">
                        <option value="Can" {{ old('dispatch_type', $dispatch->dispatch_type) == 'Can' ? 'selected' : '' }}>Can</option>
                        <option value="Tanker" {{ old('dispatch_type', $dispatch->dispatch_type) == 'Tanker' ? 'selected' : '' }}>Tanker</option>
                    </select>
                </div>

                <!-- Challan No -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Challan No. *</label>
                    <input type="text" name="challan_number" id="challan_number" value="{{ old('challan_number', $dispatch->challan_number) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                </div>

                <!-- Drop Location -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Drop Location</label>
                    <input type="text" name="drop_location" id="drop_location" list="drop_location_list" value="{{ old('drop_location', $dispatch->drop_location) }}" placeholder="Select route / destination" class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    <datalist id="drop_location_list">
                        <option value="Bombay - Goa">
                        <option value="sanawat - indoore">
                        <option value="KHARGONE - MANGRIYA">
                        <option value="Location 1">
                        <option value="None">
                        @foreach($routes as $r)
                            <option value="{{ $r->name }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
        </div>

        <!-- CARD 2: Choose Items (Matching Screenshot 1, 2 & 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-3.5 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-flask text-[#002e79]"></i> Choose Items:
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Automatic balance & quantity calculations</span>
            </div>

            <div class="p-6 space-y-6">
                <!-- Inputs Row 1 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type *</label>
                        <select name="milk_type" id="milk_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                            <option value="Cow" {{ old('milk_type', $dispatch->milk_type) == 'Cow' ? 'selected' : '' }}>Cow</option>
                            <option value="Buffalo" {{ old('milk_type', $dispatch->milk_type) == 'Buffalo' ? 'selected' : '' }}>Buffalo</option>
                            <option value="Mixed" {{ old('milk_type', $dispatch->milk_type) == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Purchase Qty (Ltr)</label>
                        <input type="number" step="0.01" name="purchase_qty" id="purchase_qty" value="{{ old('purchase_qty', $dispatch->purchase_qty ?? 0) }}" placeholder="Qty in Ltr" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Milk Quality *</label>
                        <select name="milk_quality" id="milk_quality" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                            <option value="Good" {{ old('milk_quality', $dispatch->milk_quality) == 'Good' ? 'selected' : '' }}>Good</option>
                            <option value="Standard" {{ old('milk_quality', $dispatch->milk_quality) == 'Standard' ? 'selected' : '' }}>Standard</option>
                            <option value="Average" {{ old('milk_quality', $dispatch->milk_quality) == 'Average' ? 'selected' : '' }}>Average</option>
                            <option value="Sour" {{ old('milk_quality', $dispatch->milk_quality) == 'Sour' ? 'selected' : '' }}>Sour</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (Ltr) *</label>
                        <input type="number" step="0.01" name="quantity_ltr" id="quantity_ltr" value="{{ old('quantity_ltr', $dispatch->quantity_ltr > 0 ? $dispatch->quantity_ltr : $dispatch->total_milk_quantity) }}" required placeholder="Liters" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-[#002e79]" oninput="calculateBalance()">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Prev. Balance</label>
                        <input type="number" step="0.01" name="prev_balance" id="prev_balance" value="{{ old('prev_balance', $dispatch->prev_balance ?? 0) }}" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono" oninput="calculateBalance()">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Balance</label>
                        <input type="number" step="0.01" name="balance" id="balance" value="{{ old('balance', $dispatch->balance ?? 0) }}" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-slate-800">
                    </div>
                </div>

                <!-- Inputs Row 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Loss (Ltr)</label>
                        <input type="number" step="0.01" name="loss" id="loss" value="{{ old('loss', $dispatch->loss ?? 0) }}" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono text-rose-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">FAT %</label>
                        <input type="number" step="0.01" name="fat" id="fat" value="{{ old('fat', $dispatch->fat ?? 3.50) }}" placeholder="3.5" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SNF %</label>
                        <input type="number" step="0.01" name="snf" id="snf" value="{{ old('snf', $dispatch->snf ?? 8.50) }}" placeholder="8.5" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">CLR</label>
                        <input type="number" step="0.01" name="clr" id="clr" value="{{ old('clr', $dispatch->clr ?? 28.00) }}" placeholder="CLR" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Can No.</label>
                        <input type="text" name="can_number" id="can_number" value="{{ old('can_number', $dispatch->can_number) }}" placeholder="Can #" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Temp (°C)</label>
                        <input type="number" step="0.1" name="temperature" id="temperature" value="{{ old('temperature', $dispatch->temperature ?? 4.0) }}" placeholder="Temp" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>
                </div>

                <!-- Inputs Row 3: Acidity & Amount + 4 Action Buttons matching Screenshot 1 & 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Acidity %</label>
                        <input type="number" step="0.01" name="acidity" id="acidity" value="{{ old('acidity', $dispatch->acidity ?? 0.14) }}" placeholder="%" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $dispatch->amount ?? 0) }}" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-emerald-700">
                    </div>

                    <!-- 4 Action Buttons: Save, + Add Item, Edit, Delete -->
                    <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center justify-end gap-2.5 pt-2">
                        <!-- Save (Green) -->
                        <button type="button" onclick="saveItemToTable()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-check text-xs"></i> <span>Save</span>
                        </button>
                        <!-- + Add Item (Yellow) -->
                        <button type="button" onclick="addItemToTable(true)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i> <span>+ Add Item</span>
                        </button>
                        <!-- Edit (Blue) -->
                        <button type="button" onclick="editCurrentItem()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-xs"></i> <span>Edit</span>
                        </button>
                        <!-- Delete (Red) -->
                        <button type="button" onclick="deleteItemRow()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-trash text-xs"></i> <span>Delete</span>
                        </button>
                    </div>
                </div>

                <!-- Toast feedback alert -->
                <div id="itemNotification" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span id="itemNotificationText">Item saved successfully!</span>
                    </span>
                    <button type="button" onclick="document.getElementById('itemNotification').classList.add('hidden')" class="text-emerald-700 hover:text-emerald-900 font-bold">&times;</button>
                </div>

                <!-- Item Preview Table (Matching Screenshot 3 Grid) -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-[#e8f5e9] text-[10px] uppercase font-bold text-slate-700 border-b border-emerald-200">
                                <tr>
                                    <th class="py-2.5 px-3">Milk Type</th>
                                    <th class="py-2.5 px-3">Quality Type</th>
                                    <th class="py-2.5 px-3">Dispatch Type</th>
                                    <th class="py-2.5 px-3">Qty</th>
                                    <th class="py-2.5 px-3">Prev. Balance</th>
                                    <th class="py-2.5 px-3">Balance</th>
                                    <th class="py-2.5 px-3">Loss</th>
                                    <th class="py-2.5 px-3">FAT</th>
                                    <th class="py-2.5 px-3">SNF</th>
                                    <th class="py-2.5 px-3">CLR</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="divide-y divide-slate-100 bg-white">
                                <tr id="activeItemRow" class="bg-blue-50/30 hover:bg-blue-50/60 cursor-pointer transition" onclick="editCurrentItem()">
                                    <td class="py-3 px-3 font-bold text-slate-800" id="td_milk_type">{{ $dispatch->milk_type ?? 'Cow' }}</td>
                                    <td class="py-3 px-3 font-semibold text-emerald-700" id="td_quality">{{ $dispatch->milk_quality ?? 'Good' }}</td>
                                    <td class="py-3 px-3 font-mono" id="td_dispatch_type">{{ $dispatch->dispatch_type ?? 'Can' }}</td>
                                    <td class="py-3 px-3 font-bold text-[#002e79] font-mono" id="td_qty">{{ number_format($dispatch->quantity_ltr > 0 ? $dispatch->quantity_ltr : $dispatch->total_milk_quantity, 2) }}</td>
                                    <td class="py-3 px-3 font-mono" id="td_prev_bal">{{ number_format($dispatch->prev_balance ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold text-slate-800" id="td_balance">{{ number_format($dispatch->balance ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono text-rose-600" id="td_loss">{{ number_format($dispatch->loss ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold text-slate-700" id="td_fat">{{ number_format($dispatch->fat ?? 3.5, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold text-slate-700" id="td_snf">{{ number_format($dispatch->snf ?? 8.5, 2) }}</td>
                                    <td class="py-3 px-3 font-mono" id="td_clr">{{ number_format($dispatch->clr ?? 28, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Route, Vehicle & Logistics (Matching Screenshot 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-3.5 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-truck text-[#002e79]"></i> Vehicle, Timing & Seal Tracking
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Logistics inspection parameters</span>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Route Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Route Name</label>
                        <input type="text" name="route_name" id="route_name" list="route_name_list" value="{{ old('route_name', $dispatch->route_name ?? ($dispatch->route->name ?? 'Bombay - Goa')) }}" placeholder="Select route" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <datalist id="route_name_list">
                            <option value="Bombay - Goa">
                            <option value="sanawat - indoore">
                            <option value="KHARGONE - MANGRIYA">
                            @foreach($routes as $r)
                                <option value="{{ $r->name }}">
                            @endforeach
                        </datalist>
                    </div>

                    <!-- Vehicle No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle No.</label>
                        <input type="text" name="vehicle_number" id="vehicle_number" list="vehicle_list" value="{{ old('vehicle_number', $dispatch->vehicle_number ?? 'JH0AB1123') }}" placeholder="Select vehicle" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                        <datalist id="vehicle_list">
                            <option value="JH0AB1123">
                            <option value="MP09AB1234">
                            <option value="MP09GA4521">
                        </datalist>
                    </div>

                    <!-- Vehicle In Time -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle In Time</label>
                        <input type="text" name="vehicle_in_time" id="vehicle_in_time" value="{{ old('vehicle_in_time', $dispatch->vehicle_in_time ?? '10:05 PM') }}" placeholder="10:05 PM" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>

                    <!-- Vehicle Out Time -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Out Time</label>
                        <input type="text" name="vehicle_out_time" id="vehicle_out_time" value="{{ old('vehicle_out_time', $dispatch->vehicle_out_time ?? '10:06 PM') }}" placeholder="10:06 PM" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>

                    <!-- New Seal No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">New Seal No.</label>
                        <input type="text" name="seal_number" id="seal_number" value="{{ old('seal_number', $dispatch->seal_number ?? '5') }}" placeholder="Seal #" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <!-- Chamber No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Chamber No.</label>
                        <input type="text" name="chamber_number" id="chamber_number" value="{{ old('chamber_number', $dispatch->chamber_number ?? '515') }}" placeholder="Chamber" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <!-- Headload Kms -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Headload Kms</label>
                        <input type="number" step="0.01" name="headload_kms" id="headload_kms" value="{{ old('headload_kms', $dispatch->headload_kms ?? 5.00) }}" placeholder="Km" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <!-- Difference -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Difference</label>
                        <input type="number" step="0.01" name="difference" id="difference" value="{{ old('difference', $dispatch->difference ?? 55.00) }}" placeholder="Diff" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons matching Screenshot 3 -->
            <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-[#002e79] hover:bg-[#002765] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-lock text-xs"></i>
                    <span>Update Dispatch</span>
                </button>
                <a href="{{ route('dispatch.index') }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition">
                    Close
                </a>
            </div>
        </div>
    </form>

</div>

<script>
    let isItemAdded = true;

    function syncMilkType(val) {
        document.getElementById('milk_type').value = val;
        addItemToTable(false);
    }

    function syncDispatchType(val) {
        addItemToTable(false);
    }

    function calculateBalance() {
        const qty = parseFloat(document.getElementById('quantity_ltr').value) || 0;
        const prevBal = parseFloat(document.getElementById('prev_balance').value) || 0;
        document.getElementById('balance').value = (qty + prevBal).toFixed(2);
        addItemToTable(false);
    }

    function showItemToast(msg) {
        const notify = document.getElementById('itemNotification');
        const txt = document.getElementById('itemNotificationText');
        if (notify && txt) {
            txt.innerText = msg;
            notify.classList.remove('hidden');
            notify.classList.add('flex');
            setTimeout(() => {
                notify.classList.add('hidden');
                notify.classList.remove('flex');
            }, 4000);
        }
    }

    function addItemToTable(showMessage = true) {
        const qty = parseFloat(document.getElementById('quantity_ltr').value);
        if (isNaN(qty) || qty <= 0) {
            alert('Please enter a valid Quantity (Ltr) before adding the item.');
            document.getElementById('quantity_ltr').focus();
            return;
        }

        const milkType = document.getElementById('milk_type').value || 'Cow';
        const quality = document.getElementById('milk_quality').value || 'Good';
        const dispatchType = document.getElementById('dispatch_type').value || 'Can';
        const prevBal = parseFloat(document.getElementById('prev_balance').value) || 0;
        const balance = parseFloat(document.getElementById('balance').value) || (qty + prevBal);
        const loss = parseFloat(document.getElementById('loss').value) || 0;
        const fat = parseFloat(document.getElementById('fat').value) || 0;
        const snf = parseFloat(document.getElementById('snf').value) || 0;
        const clr = parseFloat(document.getElementById('clr').value) || 0;

        const body = document.getElementById('itemsTableBody');
        body.innerHTML = `
            <tr id="activeItemRow" class="bg-blue-50/30 hover:bg-blue-50/60 cursor-pointer transition" onclick="editCurrentItem()">
                <td class="py-3 px-3 font-bold text-slate-800" id="td_milk_type">${milkType}</td>
                <td class="py-3 px-3 font-semibold text-emerald-700" id="td_quality">${quality}</td>
                <td class="py-3 px-3 font-mono" id="td_dispatch_type">${dispatchType}</td>
                <td class="py-3 px-3 font-bold text-[#002e79] font-mono" id="td_qty">${qty.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono" id="td_prev_bal">${prevBal.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono font-bold text-slate-800" id="td_balance">${balance.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono text-rose-600" id="td_loss">${loss.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono font-bold text-slate-700" id="td_fat">${fat.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono font-bold text-slate-700" id="td_snf">${snf.toFixed(2)}</td>
                <td class="py-3 px-3 font-mono" id="td_clr">${clr.toFixed(2)}</td>
            </tr>
        `;
        isItemAdded = true;
        if (showMessage) {
            showItemToast('Item updated successfully in dispatch table!');
        }
    }

    function saveItemToTable() {
        addItemToTable(true);
    }

    function editCurrentItem() {
        document.getElementById('quantity_ltr').focus();
        document.getElementById('quantity_ltr').select();
        showItemToast('Editing item. Update inputs and click Save or + Add Item.');
    }

    function deleteItemRow() {
        const body = document.getElementById('itemsTableBody');
        body.innerHTML = `
            <tr id="emptyTableMsg">
                <td colspan="10" class="py-6 text-center text-slate-400 font-medium">No dispatch items added yet</td>
            </tr>
        `;
        isItemAdded = false;
        showItemToast('Item removed from table.');
    }

    document.getElementById('dispatchForm').addEventListener('submit', function(e) {
        if (!isItemAdded) {
            addItemToTable(false);
        }
    });
</script>
@endsection
