@extends('layouts.app')

@section('title', 'Vehicle Management')
@section('breadcrumb', 'Vehicles')
@section('header_title', 'Vehicle & Driver Fleet Management')

@section('header_action')
    <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>+ Add Vehicle</span>
    </button>
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

    <!-- Top Filter Bar matching Screenshot 2 (media_1791389598973.png) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('vehicles.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search vehicle, driver, phone -->
                <div class="relative w-full sm:w-72">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search vehicle, driver, phone..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>

                <!-- Status Filter -->
                <div class="min-w-[140px]">
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Status</option>
                        <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="on_duty" {{ $status === 'on_duty' ? 'selected' : '' }}>On Duty</option>
                        <option value="maintenance" {{ $status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Route Filter -->
                <div class="min-w-[160px]">
                    <select name="route" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Routes</option>
                        @foreach($allAssignedRoutes as $r)
                            <option value="{{ $r }}" {{ $route === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    @if(!empty($search) || !empty($status) || !empty($route))
                        <a href="{{ route('vehicles.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Mobile Action Button -->
            <div class="sm:hidden w-full">
                <button type="button" onclick="openAddModal()" class="w-full py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center">
                    + Add Vehicle
                </button>
            </div>
        </form>
    </div>

    <!-- Vehicle Listing Table Card matching Screenshot 2 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Vehicle List</h3>
                <p class="text-[11px] text-slate-500">Fleet tracking for milk dispatch & delivery</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                Total: {{ $vehicles->total() }} Vehicles
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 text-center w-12">S.No</th>
                        <th class="py-2.5 px-3">Vehicle No</th>
                        <th class="py-2.5 px-3">Type</th>
                        <th class="py-2.5 px-3">Driver</th>
                        <th class="py-2.5 px-3">Phone</th>
                        <th class="py-2.5 px-3 text-right">Capacity (L)</th>
                        <th class="py-2.5 px-3 text-right">Kilometer</th>
                        <th class="py-2.5 px-3 text-right">Per KM Rate</th>
                        <th class="py-2.5 px-3">Assigned Route</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center min-w-[90px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($vehicles as $index => $v)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 text-center font-mono text-slate-400">
                                #{{ $vehicles->firstItem() + $index }}
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900 uppercase">
                                {{ $v->vehicle_number }}
                            </td>
                            <td class="py-2.5 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $v->vehicle_type }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">
                                {{ $v->driver_name ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 font-mono text-slate-600">
                                {{ $v->driver_phone ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-sky-800">
                                {{ number_format($v->capacity, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600">
                                {{ number_format($v->current_km, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600">
                                ₹{{ number_format($v->per_km_rate, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $v->assigned_route ?: ($v->route->name ?? '-') }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($v->status === 'available')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Available
                                    </span>
                                @elseif($v->status === 'on_duty')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        On Duty
                                    </span>
                                @elseif($v->status === 'maintenance')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Maintenance
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openEditModal({{ json_encode($v) }})" title="Edit" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form method="POST" action="{{ route('vehicles.destroy', $v->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete vehicle {{ $v->vehicle_number }}?');">
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
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <i data-lucide="truck" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No vehicles registered yet.</p>
                                <button type="button" onclick="openAddModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Vehicle Now</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL: ADD VEHICLE ================= -->
<div id="addVehicleModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">+ Add Vehicle</h3>
                    <p class="text-xs text-slate-500">Register new delivery vehicle in fleet</p>
                </div>
            </div>
            <button type="button" onclick="closeAddModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('vehicles.store') }}" class="mt-4 space-y-3.5 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="vehicle_number" required placeholder="e.g. UP123456789" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle Type <span class="text-rose-500">*</span></label>
                    <select name="vehicle_type" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <option value="van">Van</option>
                        <option value="car">Car</option>
                        <option value="tanker">Tanker</option>
                        <option value="auto">Auto</option>
                        <option value="bike">Bike</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Name</label>
                    <input type="text" name="driver_name" placeholder="Driver Full Name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Phone</label>
                    <input type="text" name="driver_phone" placeholder="10-digit mobile" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Capacity (L)</label>
                    <input type="number" step="0.01" name="capacity" placeholder="0.00" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kilometer</label>
                    <input type="number" step="0.01" name="current_km" placeholder="0.00" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Per KM Rate (₹)</label>
                    <input type="number" step="0.01" name="per_km_rate" placeholder="0.00" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Assigned Route</label>
                    <input type="text" name="assigned_route" placeholder="e.g. Lowadih - Lalpur" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <option value="available">Available</option>
                        <option value="on_duty">On Duty</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Save Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT VEHICLE ================= -->
<div id="editVehicleModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="editModalHeader">Edit Vehicle</h3>
                    <p class="text-xs text-slate-500">Update vehicle details</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editVehicleForm" method="POST" class="mt-4 space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle Number</label>
                    <input type="text" name="vehicle_number" id="editVehNumber" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none uppercase font-mono font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Vehicle Type</label>
                    <select name="vehicle_type" id="editVehType" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        <option value="van">Van</option>
                        <option value="car">Car</option>
                        <option value="tanker">Tanker</option>
                        <option value="auto">Auto</option>
                        <option value="bike">Bike</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Name</label>
                    <input type="text" name="driver_name" id="editDriverName" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Phone</label>
                    <input type="text" name="driver_phone" id="editDriverPhone" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Capacity (L)</label>
                    <input type="number" step="0.01" name="capacity" id="editCapacity" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kilometer</label>
                    <input type="number" step="0.01" name="current_km" id="editKm" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Per KM Rate (₹)</label>
                    <input type="number" step="0.01" name="per_km_rate" id="editPerKm" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Assigned Route</label>
                    <input type="text" name="assigned_route" id="editRoute" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="editStatus" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        <option value="available">Available</option>
                        <option value="on_duty">On Duty</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Update Vehicle</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addVehicleModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addVehicleModal').classList.add('hidden');
    }

    function openEditModal(v) {
        document.getElementById('editModalHeader').textContent = `Edit Vehicle ${v.vehicle_number}`;
        document.getElementById('editVehicleForm').action = `/vehicles/${v.id}`;
        document.getElementById('editVehNumber').value = v.vehicle_number;
        document.getElementById('editVehType').value = v.vehicle_type;
        document.getElementById('editDriverName').value = v.driver_name || '';
        document.getElementById('editDriverPhone').value = v.driver_phone || '';
        document.getElementById('editCapacity').value = v.capacity;
        document.getElementById('editKm').value = v.current_km;
        document.getElementById('editPerKm').value = v.per_km_rate;
        document.getElementById('editRoute').value = v.assigned_route || '';
        document.getElementById('editStatus').value = v.status;
        document.getElementById('editVehicleModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editVehicleModal').classList.add('hidden');
    }
</script>
@endpush
