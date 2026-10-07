@extends('layouts.app')

@section('title', 'Drivers List')
@section('breadcrumb', 'Drivers')
@section('header_title', 'Driver & Chauffeur Management')

@section('header_action')
    <button type="button" onclick="openAddDriverModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>+ Add Driver</span>
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

    <!-- Top Filter Bar matching Screenshot 1 (media_1791389924187.png) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('drivers.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search input -->
                <div class="relative w-full sm:w-80">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search driver, phone, license, aadhaar..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>

                <!-- Status Filter -->
                <div class="min-w-[130px]">
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    @if(!empty($search) || !empty($status))
                        <a href="{{ route('drivers.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Mobile Action Button -->
            <div class="sm:hidden w-full">
                <button type="button" onclick="openAddDriverModal()" class="w-full py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center">
                    + Add Driver
                </button>
            </div>
        </form>
    </div>

    <!-- Drivers Table Listing -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Drivers List</h3>
                <p class="text-[11px] text-slate-500">Fleet drivers, licenses, and bank disbursement records</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                Total: {{ $drivers->total() }} Drivers
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 text-center w-12">S.No</th>
                        <th class="py-2.5 px-3">Driver Name</th>
                        <th class="py-2.5 px-3">Phone</th>
                        <th class="py-2.5 px-3">License No</th>
                        <th class="py-2.5 px-3">Aadhaar / PAN</th>
                        <th class="py-2.5 px-3 min-w-[180px]">Bank Details</th>
                        <th class="py-2.5 px-3">Address</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center min-w-[90px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($drivers as $index => $d)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">
                                #{{ $drivers->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900 leading-tight">{{ $d->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $d->driver_code }}</div>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-800">
                                {{ $d->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-600 uppercase">
                                {{ $d->license_number ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-[11px]">
                                @if(!empty($d->aadhaar_card_no))
                                    <div>UID: <span class="font-mono text-slate-800">{{ $d->aadhaar_card_no }}</span></div>
                                @endif
                                @if(!empty($d->pan_card_no))
                                    <div>PAN: <span class="font-mono text-slate-800 uppercase">{{ $d->pan_card_no }}</span></div>
                                @endif
                                @if(empty($d->aadhaar_card_no) && empty($d->pan_card_no))
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-[11px]">
                                @if(!empty($d->account_number))
                                    <div class="font-semibold text-slate-900">{{ $d->bank_name ?? 'Bank' }}</div>
                                    <div class="text-slate-500">A/C: <span class="font-mono text-slate-800 font-bold">{{ $d->account_number }}</span></div>
                                    <div class="text-[10px] text-slate-400">IFSC: {{ $d->ifsc_code ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400 text-[10px]">No Bank Info</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600 text-[11px] max-w-[150px] truncate" title="{{ $d->address }}">
                                {{ $d->address ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $d->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openEditDriverModal({{ json_encode($d) }})" title="Edit" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form method="POST" action="{{ route('drivers.destroy', $d->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete driver {{ $d->name }}?');">
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
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No drivers registered yet.</p>
                                <button type="button" onclick="openAddDriverModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Driver Now</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($drivers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL: ADD DRIVER matching Screenshot 1 (media_1791389924187.png) ================= -->
<div id="addDriverModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add Driver</h3>
                    <p class="text-xs text-slate-500">Register new fleet driver & chauffeur</p>
                </div>
            </div>
            <button type="button" onclick="closeAddDriverModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('drivers.store') }}" class="mt-5 space-y-4 text-xs">
            @csrf

            <!-- Row 1: Driver Name, Phone Number, License Number -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Enter Driver Name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="Enter Phone Number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">License Number</label>
                    <input type="text" name="license_number" placeholder="Enter License Number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono font-medium">
                </div>
            </div>

            <!-- Row 2: Driver Code (hidden or auto), Aadhaar Card No, PAN Card No, Account Holder -->
            <input type="hidden" name="driver_code" value="{{ $nextCode }}">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Aadhaar Card No</label>
                    <input type="text" name="aadhaar_card_no" placeholder="Enter Aadhaar No" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">PAN Card No</label>
                    <input type="text" name="pan_card_no" placeholder="Enter PAN No" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Holder</label>
                    <input type="text" name="account_holder" placeholder="Enter Account Holder" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <!-- Row 3: Account Number, Bank Name, IFSC Code -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" placeholder="Enter Account Number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" placeholder="Enter Bank Name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">IFSC Code</label>
                    <input type="text" name="ifsc_code" placeholder="Enter IFSC Code" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono">
                </div>
            </div>

            <!-- Row 4: Bank Branch, Status, Address -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bank Branch</label>
                    <input type="text" name="bank_branch" placeholder="Enter Branch" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Address</label>
                    <textarea name="address" rows="1" placeholder="Enter Address" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none resize-none"></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="reset" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Reset</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Save Driver</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT DRIVER ================= -->
<div id="editDriverModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="editDriverHeader">Edit Driver</h3>
                    <p class="text-xs text-slate-500">Update driver profile and bank records</p>
                </div>
            </div>
            <button type="button" onclick="closeEditDriverModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editDriverForm" method="POST" class="mt-5 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <input type="hidden" name="driver_code" id="editDriverCode">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Driver Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="editName" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="editPhone" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">License Number</label>
                    <input type="text" name="license_number" id="editLicense" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none uppercase font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Aadhaar Card No</label>
                    <input type="text" name="aadhaar_card_no" id="editAadhaar" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">PAN Card No</label>
                    <input type="text" name="pan_card_no" id="editPan" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none uppercase font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Holder</label>
                    <input type="text" name="account_holder" id="editAccHolder" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Account Number</label>
                    <input type="text" name="account_number" id="editAccNumber" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" id="editBankName" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">IFSC Code</label>
                    <input type="text" name="ifsc_code" id="editIfsc" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none uppercase font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Bank Branch</label>
                    <input type="text" name="bank_branch" id="editBranch" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="editStatus" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Address</label>
                    <textarea name="address" id="editAddress" rows="1" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none resize-none"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditDriverModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Update Driver</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddDriverModal() {
        document.getElementById('addDriverModal').classList.remove('hidden');
    }
    function closeAddDriverModal() {
        document.getElementById('addDriverModal').classList.add('hidden');
    }

    function openEditDriverModal(d) {
        document.getElementById('editDriverHeader').textContent = `Edit Driver ${d.name}`;
        document.getElementById('editDriverForm').action = `/drivers/${d.id}`;
        document.getElementById('editDriverCode').value = d.driver_code;
        document.getElementById('editName').value = d.name;
        document.getElementById('editPhone').value = d.phone || '';
        document.getElementById('editLicense').value = d.license_number || '';
        document.getElementById('editAadhaar').value = d.aadhaar_card_no || '';
        document.getElementById('editPan').value = d.pan_card_no || '';
        document.getElementById('editAccHolder').value = d.account_holder || '';
        document.getElementById('editAccNumber').value = d.account_number || '';
        document.getElementById('editBankName').value = d.bank_name || '';
        document.getElementById('editIfsc').value = d.ifsc_code || '';
        document.getElementById('editBranch').value = d.bank_branch || '';
        document.getElementById('editStatus').value = d.status;
        document.getElementById('editAddress').value = d.address || '';
        document.getElementById('editDriverModal').classList.remove('hidden');
    }
    function closeEditDriverModal() {
        document.getElementById('editDriverModal').classList.add('hidden');
    }
</script>
@endpush
