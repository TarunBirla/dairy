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
                <span class="text-[11px] font-semibold text-blue-200 uppercase tracking-wider">Step 1: Header</span>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <!-- From Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">From Date *</label>
                    <input type="date" name="from_date" value="{{ old('from_date', $dispatch->from_date ? $dispatch->from_date->format('Y-m-d') : ($dispatch->dispatch_date ? $dispatch->dispatch_date->format('Y-m-d') : date('Y-m-d'))) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- From Shift -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">From Shift *</label>
                    <select name="from_shift" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Morning" {{ old('from_shift', $dispatch->from_shift ?? $dispatch->shift) == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ old('from_shift', $dispatch->from_shift ?? $dispatch->shift) == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">To Date *</label>
                    <input type="date" name="to_date" value="{{ old('to_date', $dispatch->to_date ? $dispatch->to_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- To Shift -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">To Shift *</label>
                    <select name="to_shift" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Morning" {{ old('to_shift', $dispatch->to_shift ?? $dispatch->shift) == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ old('to_shift', $dispatch->to_shift ?? $dispatch->shift) == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>

                <!-- Header Milk Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type *</label>
                    <select name="header_milk_type" id="header_milk_type" class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white" onchange="document.getElementById('milk_type').value = this.value">
                        <option value="Cow" {{ old('header_milk_type', $dispatch->milk_type) == 'Cow' ? 'selected' : '' }}>Cow</option>
                        <option value="Buffalo" {{ old('header_milk_type', $dispatch->milk_type) == 'Buffalo' ? 'selected' : '' }}>Buffalo</option>
                        <option value="Mixed" {{ old('header_milk_type', $dispatch->milk_type) == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                    </select>
                </div>

                <!-- Challan Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Challan Date *</label>
                    <input type="date" name="challan_date" value="{{ old('challan_date', $dispatch->challan_date ? $dispatch->challan_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- Can/Tanker Mode -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Can / Tanker *</label>
                    <select name="dispatch_type" id="dispatch_type" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Can" {{ old('dispatch_type', $dispatch->dispatch_type) == 'Can' ? 'selected' : '' }}>Can</option>
                        <option value="Tanker" {{ old('dispatch_type', $dispatch->dispatch_type) == 'Tanker' ? 'selected' : '' }}>Tanker</option>
                    </select>
                </div>

                <!-- Challan No -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Challan No. *</label>
                    <input type="text" name="challan_number" value="{{ old('challan_number', $dispatch->challan_number) }}" required class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                </div>

                <!-- Drop Location -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Drop Location / Destination</label>
                    <input type="text" name="drop_location" value="{{ old('drop_location', $dispatch->drop_location) }}" placeholder="e.g. Location 1 or Bombay - Goa" class="w-full px-3 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>
            </div>
        </div>

        <!-- CARD 2: Choose Items / Quality Parameters (Matching Screenshot 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-3.5 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-flask text-[#002e79]"></i> Choose Items & Quality Parameters
                </span>
                <span class="text-[11px] text-slate-400 font-medium">Automatic balance & amount calculations</span>
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

                <!-- Inputs Row 3: Acidity & Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Acidity %</label>
                        <input type="number" step="0.01" name="acidity" id="acidity" value="{{ old('acidity', $dispatch->acidity ?? 0.14) }}" placeholder="%" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $dispatch->amount ?? 0) }}" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-emerald-700">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4 flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="addItemToPreview()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check text-xs"></i> <span>Save Item</span>
                        </button>
                        <button type="button" onclick="addItemToPreview()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i> <span>+ Add Item</span>
                        </button>
                    </div>
                </div>

                <!-- Item Preview Table (Matching Screenshot 3 Grid) -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-emerald-50 text-[10px] uppercase font-bold text-slate-700 border-b border-emerald-100">
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
                            <tbody id="itemPreviewBody" class="divide-y divide-slate-100 bg-white">
                                <tr>
                                    <td class="py-3 px-3 font-semibold" id="prev_milk_type">{{ $dispatch->milk_type ?? 'Cow' }}</td>
                                    <td class="py-3 px-3" id="prev_quality">{{ $dispatch->milk_quality ?? 'Good' }}</td>
                                    <td class="py-3 px-3 font-mono" id="prev_type">{{ $dispatch->dispatch_type ?? 'Can' }}</td>
                                    <td class="py-3 px-3 font-bold text-[#002e79]" id="prev_qty">{{ number_format($dispatch->quantity_ltr > 0 ? $dispatch->quantity_ltr : $dispatch->total_milk_quantity, 2) }}</td>
                                    <td class="py-3 px-3 font-mono" id="prev_prevbal">{{ number_format($dispatch->prev_balance ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold" id="prev_balance_val">{{ number_format($dispatch->balance ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono text-rose-600" id="prev_loss">{{ number_format($dispatch->loss ?? 0, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold" id="prev_fat">{{ number_format($dispatch->fat ?? 3.5, 2) }}</td>
                                    <td class="py-3 px-3 font-mono font-bold" id="prev_snf">{{ number_format($dispatch->snf ?? 8.5, 2) }}</td>
                                    <td class="py-3 px-3 font-mono" id="prev_clr">{{ number_format($dispatch->clr ?? 28, 2) }}</td>
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
                        <select name="route_name" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                            <option value="">Select Route</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->name }}" {{ old('route_name', $dispatch->route_name ?? ($dispatch->route->name ?? '')) == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                            <option value="Bombay - Goa" {{ old('route_name', $dispatch->route_name) == 'Bombay - Goa' ? 'selected' : '' }}>Bombay - Goa</option>
                            <option value="sanawad - indoore" {{ old('route_name', $dispatch->route_name) == 'sanawad - indoore' ? 'selected' : '' }}>sanawad - indoore</option>
                            <option value="KHARGONE - MANGRIYA" {{ old('route_name', $dispatch->route_name) == 'KHARGONE - MANGRIYA' ? 'selected' : '' }}>KHARGONE - MANGRIYA</option>
                        </select>
                    </div>

                    <!-- Vehicle No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle No.</label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $dispatch->vehicle_number) }}" placeholder="e.g. JH0AB1123" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <!-- Vehicle In Time -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle In Time</label>
                        <input type="text" name="vehicle_in_time" value="{{ old('vehicle_in_time', $dispatch->vehicle_in_time) }}" placeholder="10:05 PM" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>

                    <!-- Vehicle Out Time -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vehicle Out Time</label>
                        <input type="text" name="vehicle_out_time" value="{{ old('vehicle_out_time', $dispatch->vehicle_out_time) }}" placeholder="10:06 PM" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>

                    <!-- New Seal No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">New Seal No.</label>
                        <input type="text" name="seal_number" value="{{ old('seal_number', $dispatch->seal_number) }}" placeholder="Seal #" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <!-- Chamber No -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Chamber No.</label>
                        <input type="text" name="chamber_number" value="{{ old('chamber_number', $dispatch->chamber_number) }}" placeholder="Chamber" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <!-- Headload Kms -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Headload Kms</label>
                        <input type="number" step="0.01" name="headload_kms" value="{{ old('headload_kms', $dispatch->headload_kms ?? 0) }}" placeholder="Km" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>

                    <!-- Difference -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Difference</label>
                        <input type="number" step="0.01" name="difference" value="{{ old('difference', $dispatch->difference ?? 0) }}" placeholder="Diff" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons matching Screenshot 3 -->
            <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-[#002e79] hover:bg-[#002765] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
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
    function calculateBalance() {
        const qty = parseFloat(document.getElementById('quantity_ltr').value) || 0;
        const prevBal = parseFloat(document.getElementById('prev_balance').value) || 0;
        document.getElementById('balance').value = (qty + prevBal).toFixed(2);
        addItemToPreview();
    }

    function addItemToPreview() {
        const milkType = document.getElementById('milk_type').value;
        const quality = document.getElementById('milk_quality').value;
        const dispatchType = document.getElementById('dispatch_type').value;
        const qty = parseFloat(document.getElementById('quantity_ltr').value) || 0;
        const prevBal = parseFloat(document.getElementById('prev_balance').value) || 0;
        const balance = parseFloat(document.getElementById('balance').value) || 0;
        const loss = parseFloat(document.getElementById('loss').value) || 0;
        const fat = parseFloat(document.getElementById('fat').value) || 0;
        const snf = parseFloat(document.getElementById('snf').value) || 0;
        const clr = parseFloat(document.getElementById('clr').value) || 0;

        document.getElementById('prev_milk_type').innerText = milkType;
        document.getElementById('prev_quality').innerText = quality;
        document.getElementById('prev_type').innerText = dispatchType;
        document.getElementById('prev_qty').innerText = qty.toFixed(2);
        document.getElementById('prev_prevbal').innerText = prevBal.toFixed(2);
        document.getElementById('prev_balance_val').innerText = balance.toFixed(2);
        document.getElementById('prev_loss').innerText = loss.toFixed(2);
        document.getElementById('prev_fat').innerText = fat.toFixed(2);
        document.getElementById('prev_snf').innerText = snf.toFixed(2);
        document.getElementById('prev_clr').innerText = clr.toFixed(2);
    }
</script>
@endsection
