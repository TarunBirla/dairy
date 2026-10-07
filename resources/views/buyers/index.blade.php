@extends('layouts.app')

@section('title', 'Buyer List')
@section('breadcrumb', 'Buyers')
@section('header_title', 'Milk Buyers Management')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('buyers.khata') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="book-open" class="w-4 h-4 text-emerald-600"></i>
            <span>Buyer Khata</span>
        </a>
        <button type="button" onclick="openAddBuyerModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>+ Add Buyer</span>
        </button>
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

    <!-- Top Filter Bar matching Screenshot 3 (media_1791389622008.png) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('buyers.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search by name or phone -->
                <div class="relative w-full sm:w-80">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, code or phone..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>

                <!-- Status Filter (On / Off) -->
                <div class="min-w-[130px]">
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>On (Active)</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Off (Inactive)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    @if(!empty($search) || !empty($status))
                        <a href="{{ route('buyers.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Mobile Action Button -->
            <div class="sm:hidden w-full">
                <button type="button" onclick="openAddBuyerModal()" class="w-full py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl text-center">
                    + Add Buyer
                </button>
            </div>
        </form>
    </div>

    <!-- Buyer Listing Table Card matching Screenshot 3 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Buyer List</h3>
                <p class="text-[11px] text-slate-500">Commercial milk customers and wholesale buyers</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                Total: {{ $buyers->total() }} Buyers
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 text-center w-12">S.No</th>
                        <th class="py-2.5 px-3">Buyer Name</th>
                        <th class="py-2.5 px-3">Milk Type</th>
                        <th class="py-2.5 px-3">Village / Address</th>
                        <th class="py-2.5 px-3">Contact No</th>
                        <th class="py-2.5 px-3 text-right">Balance Due</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center min-w-[120px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($buyers as $index => $b)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">
                                #{{ $buyers->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900 leading-tight">{{ $b->name }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $b->buyer_code }}</div>
                            </td>
                            <td class="py-3 px-3 capitalize">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $b->milk_type === 'cow' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($b->milk_type === 'buffalo' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                    {{ $b->milk_type }} Milk
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $b->address ?: ($b->taluka ?: ($b->district ?: '-')) }}
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-700">
                                {{ $b->phone ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold {{ $b->current_balance > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                ₹{{ number_format($b->current_balance, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <form method="POST" action="{{ route('buyers.toggle-status', $b->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Click to toggle status" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold transition {{ $b->status === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                        {{ $b->status === 'active' ? 'On' : 'Off' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Khata Ledger Button -->
                                    <a href="{{ route('buyers.khata', ['buyer_id' => $b->id]) }}" title="View Khata / Ledger" class="p-1.5 text-slate-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition">
                                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Bill / Statement Button -->
                                    <a href="{{ route('buyers.bill', $b->id) }}" title="View Period Bill" class="p-1.5 text-slate-500 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Edit Button -->
                                    <button type="button" onclick="openEditBuyerModal({{ json_encode($b) }})" title="Edit" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('buyers.destroy', $b->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete buyer {{ $b->name }}?');">
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
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="users" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No buyers found.</p>
                                <button type="button" onclick="openAddBuyerModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Add Buyer Now</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($buyers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $buyers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL: ADD BUYER matching Screenshot 4 (media_1791389678004.png) ================= -->
<div id="addBuyerModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">+ Add Buyer</h3>
                    <p class="text-xs text-slate-500">Commercial buyer profile & milk pricing</p>
                </div>
            </div>
            <button type="button" onclick="closeAddBuyerModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('buyers.store') }}" class="mt-5 space-y-5 text-xs">
            @csrf

            <!-- Section 1: Personal Details -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Personal Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buyer Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="buyer_code" required value="{{ $nextCode }}" placeholder="Enter Buyer Code" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none uppercase font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buyer Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Enter Full Name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="Enter 10-Digit Mobile Number" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <!-- Section 2: Milk Details matching Screenshot 4 -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Milk Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Milk Type <span class="text-rose-500">*</span></label>
                        <select name="milk_type" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                            <option value="both">Both (Cow & Buffalo)</option>
                            <option value="cow">Cow Milk</option>
                            <option value="buffalo">Buffalo Milk</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Cow Milk Rate Mode</label>
                        <select name="cow_rate_mode" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            <option value="fixed">Fixed Rate</option>
                            <option value="rate_chart">As per rate chart</option>
                            <option value="manual">Manual Rate Entry</option>
                            <option value="penalty">Rate Chart - Penalty</option>
                            <option value="bonus">Rate Chart - Bonus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Cow Fixed Rate (₹)</label>
                        <input type="number" step="0.01" name="cow_fixed_rate" placeholder="0.00" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buffalo Milk Rate Mode</label>
                        <select name="buffalo_rate_mode" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            <option value="fixed">Fixed Rate</option>
                            <option value="rate_chart">As per rate chart</option>
                            <option value="manual">Manual Rate Entry</option>
                            <option value="penalty">Rate Chart - Penalty</option>
                            <option value="bonus">Rate Chart - Bonus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buffalo Fixed Rate (₹)</label>
                        <input type="number" step="0.01" name="buffalo_fixed_rate" placeholder="0.00" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono">
                    </div>
                </div>
            </div>

            <!-- Section 3: Address Details matching Screenshot 4 -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Address Details</h4>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Address</label>
                    <input type="text" name="address" placeholder="House/Street info" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Taluka</label>
                        <input type="text" name="taluka" placeholder="Sub-district / Taluka" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">District</label>
                        <input type="text" name="district" placeholder="District Name" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Details</label>
                    <input type="text" name="details" placeholder="Additional landmark or routing details" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <!-- Section 4: Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status <span class="text-rose-500">*</span></label>
                    <select name="status" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="active">On (Active)</option>
                        <option value="inactive">Off (Inactive)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddBuyerModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Save Buyer</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: EDIT BUYER ================= -->
<div id="editBuyerModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="editModalHeader">Edit Buyer</h3>
                    <p class="text-xs text-slate-500">Update commercial buyer profile</p>
                </div>
            </div>
            <button type="button" onclick="closeEditBuyerModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editBuyerForm" method="POST" class="mt-5 space-y-5 text-xs">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Personal Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buyer Code</label>
                        <input type="text" name="buyer_code" id="editBuyerCode" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none uppercase font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buyer Name</label>
                        <input type="text" name="name" id="editBuyerName" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="editBuyerPhone" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Milk Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Milk Type</label>
                        <select name="milk_type" id="editBuyerMilkType" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                            <option value="both">Both (Cow & Buffalo)</option>
                            <option value="cow">Cow Milk</option>
                            <option value="buffalo">Buffalo Milk</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Cow Rate Mode</label>
                        <select name="cow_rate_mode" id="editCowRateMode" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                            <option value="fixed">Fixed Rate</option>
                            <option value="rate_chart">As per rate chart</option>
                            <option value="manual">Manual Rate Entry</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Cow Fixed Rate (₹)</label>
                        <input type="number" step="0.01" name="cow_fixed_rate" id="editCowFixedRate" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buffalo Rate Mode</label>
                        <select name="buffalo_rate_mode" id="editBuffaloRateMode" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                            <option value="fixed">Fixed Rate</option>
                            <option value="rate_chart">As per rate chart</option>
                            <option value="manual">Manual Rate Entry</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Buffalo Fixed Rate (₹)</label>
                        <input type="number" step="0.01" name="buffalo_fixed_rate" id="editBuffaloFixedRate" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono">
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-1 border-b border-slate-100">Address Details</h4>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Address</label>
                    <input type="text" name="address" id="editAddress" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Taluka</label>
                        <input type="text" name="taluka" id="editTaluka" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">District</label>
                        <input type="text" name="district" id="editDistrict" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Details</label>
                    <input type="text" name="details" id="editDetails" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="editStatus" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                        <option value="active">On (Active)</option>
                        <option value="inactive">Off (Inactive)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditBuyerModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Update Buyer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddBuyerModal() {
        document.getElementById('addBuyerModal').classList.remove('hidden');
    }
    function closeAddBuyerModal() {
        document.getElementById('addBuyerModal').classList.add('hidden');
    }

    function openEditBuyerModal(b) {
        document.getElementById('editModalHeader').textContent = `Edit Buyer ${b.name}`;
        document.getElementById('editBuyerForm').action = `/buyers/${b.id}`;
        document.getElementById('editBuyerCode').value = b.buyer_code;
        document.getElementById('editBuyerName').value = b.name;
        document.getElementById('editBuyerPhone').value = b.phone || '';
        document.getElementById('editBuyerMilkType').value = b.milk_type;
        document.getElementById('editCowRateMode').value = b.cow_rate_mode;
        document.getElementById('editCowFixedRate').value = b.cow_fixed_rate;
        document.getElementById('editBuffaloRateMode').value = b.buffalo_rate_mode;
        document.getElementById('editBuffaloFixedRate').value = b.buffalo_fixed_rate;
        document.getElementById('editAddress').value = b.address || '';
        document.getElementById('editTaluka').value = b.taluka || '';
        document.getElementById('editDistrict').value = b.district || '';
        document.getElementById('editDetails').value = b.details || '';
        document.getElementById('editStatus').value = b.status;
        document.getElementById('editBuyerModal').classList.remove('hidden');
    }
    function closeEditBuyerModal() {
        document.getElementById('editBuyerModal').classList.add('hidden');
    }
</script>
@endpush
