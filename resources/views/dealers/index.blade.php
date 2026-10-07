@extends('layouts.app')

@section('title', 'Product Dealers')
@section('breadcrumb', 'Dealers')
@section('header_title', 'Product Dealer Management')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('dealers.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="printer" class="w-4 h-4 text-amber-600"></i>
            <span>Print</span>
        </a>
        <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Add Dealer</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="dealerManager()" @open-dealer-modal.window="openCreateModal()">

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL DEALERS</span>
                <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalDealers) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Product & feed wholesale partners</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="store" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE DEALERS</span>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($activeDealers) }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">Active for purchase orders</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">DEALER PAYMENTS</span>
                <a href="{{ route('dealer-payments.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 hover:text-indigo-900 mt-3 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-200 transition">
                    <span>View Dues & Transactions</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Dealer List Card Matching Reference Layout -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header Banner & Filter Bar -->
        <div class="p-5 border-b border-slate-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Dealer List / डीलर सूची</h3>
                    <p class="text-xs text-slate-400">Manage all registered product & wholesale feed suppliers with banking details</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dealers.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-semibold rounded-lg shadow-xs transition">
                        <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Print</span>
                    </a>
                    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>+ Add Dealer</span>
                    </button>
                </div>
            </div>

            <!-- Search and Status Filter Row -->
            <form method="GET" action="{{ route('dealers.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                <!-- Search Input matching reference: Code, name, mobile, address, bank -->
                <div class="sm:col-span-6 md:col-span-7">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Code, name, mobile, address, bank..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Status Filter matching reference -->
                <div class="sm:col-span-3 md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Actions: Search, Clear, Print -->
                <div class="sm:col-span-3 md:col-span-3 flex items-end gap-2">
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    <a href="{{ route('dealers.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Clear
                    </a>
                    <a href="{{ route('dealers.print', request()->query()) }}" target="_blank" class="px-3 py-1.5 text-xs font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-lg transition flex items-center gap-1">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Print</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Matching Columns in media_1791384013733.png -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 tracking-wider">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center">S.No</th>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Mobile No</th>
                        <th class="py-3 px-4">Address</th>
                        <th class="py-3 px-4">Details</th>
                        <th class="py-3 px-4">Bank Name</th>
                        <th class="py-3 px-4">Account no</th>
                        <th class="py-3 px-4">IFSC Code</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center pr-6 w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dealers as $index => $dealer)
                        @php
                            $dealerViewData = [
                                'id' => $dealer->id,
                                'code' => $dealer->code,
                                'name' => $dealer->name,
                                'phone' => $dealer->phone ?? '',
                                'address' => $dealer->address ?? '',
                                'details' => $dealer->details ?? '',
                                'bank_name' => $dealer->bank_name ?? '',
                                'account_number' => $dealer->account_number ?? '',
                                'ifsc_code' => $dealer->ifsc_code ?? '',
                                'branch' => $dealer->branch ?? '',
                                'status' => $dealer->status,
                                'total_purchased' => $dealer->total_purchased,
                                'total_paid' => $dealer->total_paid,
                                'dues_amount' => $dealer->dues_amount,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- S.No -->
                            <td class="py-3 px-3 text-center text-slate-500 font-medium">
                                {{ $dealers->firstItem() + $index }}
                            </td>

                            <!-- Code -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-800 text-[11px] font-mono border border-slate-200">
                                    {{ $dealer->code }}
                                </span>
                            </td>

                            <!-- Name -->
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $dealer->name }}
                            </td>

                            <!-- Mobile No -->
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $dealer->phone ?: '—' }}
                            </td>

                            <!-- Address -->
                            <td class="py-3 px-4 text-slate-700 max-w-[130px] truncate" title="{{ $dealer->address }}">
                                {{ $dealer->address ?: '—' }}
                            </td>

                            <!-- Details -->
                            <td class="py-3 px-4 text-slate-500 max-w-[130px] truncate" title="{{ $dealer->details }}">
                                {{ $dealer->details ?: '—' }}
                            </td>

                            <!-- Bank Name -->
                            <td class="py-3 px-4 text-slate-700">
                                {{ $dealer->bank_name ?: '—' }}
                            </td>

                            <!-- Account no -->
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ $dealer->account_number ?: '—' }}
                            </td>

                            <!-- IFSC Code -->
                            <td class="py-3 px-4 font-mono uppercase text-slate-700">
                                {{ $dealer->ifsc_code ?: '—' }}
                            </td>

                            <!-- Branch -->
                            <td class="py-3 px-4 text-slate-700">
                                {{ $dealer->branch ?: '—' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $dealer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $dealer->status }}
                                </span>
                            </td>

                            <!-- Action: Print, View Eye, Edit Pencil, Delete Trash matching reference -->
                            <td class="py-3 px-4 text-center pr-6">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Print Slip Button -->
                                    <a href="{{ route('dealers.slip.print', $dealer) }}" target="_blank"
                                       title="Print Dealer Statement" 
                                       class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>

                                    <!-- View Details Modal Button -->
                                    <button type="button" 
                                            @click="openViewModal({{ json_encode($dealerViewData) }})"
                                            title="View Details" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 border border-sky-200 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Edit Modal Button -->
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($dealerViewData) }})"
                                            title="Edit Dealer" 
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('dealers.destroy', $dealer) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete dealer {{ addslashes($dealer->name) }} ({{ $dealer->code }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Delete Dealer" 
                                                class="w-7 h-7 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="store" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No product dealers found.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Add Dealer" to register product suppliers.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dealers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $dealers->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: FOOD / PRODUCT DEALER DETAILS             -->
    <!-- Matching Reference media_1791384024488.png in Our Theme        -->
    <!-- ============================================================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="closeModal()">

            <!-- Modal Header matching screenshot -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="isEditMode ? 'Edit Product Dealer Details' : 'Food Dealer Details / डीलर विवरण'"></h3>
                        <p class="text-xs text-slate-400">Capture supplier code, contact, address, and settlement bank credentials.</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form matching fields in media_1791384024488.png -->
            <form :action="formAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Row 1: S.No, Code, Name, Mobile No -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <!-- S.No -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">S.No</label>
                        <input type="text" x-model="form.serial_no" readonly placeholder="Enter S.No"
                               class="w-full px-3 py-1.5 text-xs bg-slate-100 text-slate-500 border border-slate-200 rounded-lg cursor-not-allowed">
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="form.code" required placeholder="Enter Code"
                               class="w-full px-3 py-1.5 text-xs font-mono font-bold bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required placeholder="Enter Name"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Mobile No -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Mobile No</label>
                        <input type="text" name="phone" x-model="form.phone" placeholder="Enter Mobile No"
                               class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Row 2: Address & Details -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Address</label>
                        <input type="text" name="address" x-model="form.address" placeholder="Enter Address"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Details</label>
                        <input type="text" name="details" x-model="form.details" placeholder="Enter Details"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- BANK DETAILS / बैंक विवरण matching reference -->
                <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center gap-1.5 text-emerald-800 font-bold text-xs uppercase tracking-wider pb-1 border-b border-slate-200">
                        <i data-lucide="landmark" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>BANK DETAILS / बैंक विवरण</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <!-- Branch -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Branch / शाखा</label>
                            <input type="text" name="branch" x-model="form.branch" placeholder="Enter Branch"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Bank Name -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bank Name / बैंक का नाम</label>
                            <input type="text" name="bank_name" x-model="form.bank_name" placeholder="Enter Bank Name"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Account No -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Account No / खाता संख्या</label>
                            <input type="text" name="account_number" x-model="form.account_number" placeholder="Enter Account Number"
                                   class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- IFSC Code -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">IFSC Code / आईएफएससी कोड</label>
                            <input type="text" name="ifsc_code" x-model="form.ifsc_code" placeholder="Enter IFSC Code"
                                   class="w-full px-3 py-1.5 text-xs font-mono uppercase bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Status</label>
                            <select name="status" x-model="form.status" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions matching reference: Reset & Save -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="resetForm()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Reset
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span x-text="isEditMode ? 'Update Dealer' : 'Save'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IN-PAGE POPUP MODAL: QUICK VIEW PROFILE                        -->
    <!-- ============================================================== -->
    <div x-show="showViewModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="showViewModal = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="showViewModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="viewData.name"></h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" x-text="'Code: ' + viewData.code"></span>
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
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Mobile No</span>
                        <p class="font-mono font-semibold text-slate-800 mt-0.5" x-text="viewData.phone || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Address</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.address || '—'"></p>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Details / Remarks</span>
                        <p class="font-medium text-slate-700 mt-0.5" x-text="viewData.details || '—'"></p>
                    </div>
                </div>

                <!-- Banking details -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Bank Name</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.bank_name || '—'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Branch</span>
                        <p class="font-semibold text-slate-800 mt-0.5" x-text="viewData.branch || '—'"></p>
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

                <!-- Financial Statement Summary -->
                <div class="grid grid-cols-3 gap-2 p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 text-center">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total Purchases</span>
                        <p class="font-bold text-slate-900 mt-0.5" x-text="'₹ ' + Number(viewData.total_purchased || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-emerald-600 uppercase">Total Paid</span>
                        <p class="font-bold text-emerald-700 mt-0.5" x-text="'₹ ' + Number(viewData.total_paid || 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-rose-600 uppercase">Current Dues</span>
                        <p class="font-bold text-rose-700 mt-0.5" x-text="'₹ ' + Number(viewData.dues_amount || 0).toFixed(2)"></p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-4">
                <a :href="'/dealers/' + viewData.id + '/print'" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-xs font-semibold transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>Print Statement</span>
                </a>
                <button type="button" @click="showViewModal = false" class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function dealerManager() {
    return {
        showModal: false,
        showViewModal: false,
        isEditMode: false,
        formAction: '{{ route("dealers.store") }}',

        form: {
            id: null,
            serial_no: '{{ $dealers->total() + 1 }}',
            code: '{{ $nextCode }}',
            name: '',
            phone: '',
            address: '',
            details: '',
            branch: '',
            bank_name: '',
            account_number: '',
            ifsc_code: '',
            status: 'active'
        },

        viewData: {},

        openCreateModal() {
            this.isEditMode = false;
            this.formAction = '{{ route("dealers.store") }}';
            this.resetForm();
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openEditModal(item) {
            this.isEditMode = true;
            this.formAction = `/dealers/${item.id}`;
            this.form = {
                id: item.id,
                serial_no: item.id,
                code: item.code || '',
                name: item.name || '',
                phone: item.phone || '',
                address: item.address || '',
                details: item.details || '',
                branch: item.branch || '',
                bank_name: item.bank_name || '',
                account_number: item.account_number || '',
                ifsc_code: item.ifsc_code || '',
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

        closeModal() {
            this.showModal = false;
        },

        resetForm() {
            this.form = {
                id: null,
                serial_no: '{{ $dealers->total() + 1 }}',
                code: '{{ $nextCode }}',
                name: '',
                phone: '',
                address: '',
                details: '',
                branch: '',
                bank_name: '',
                account_number: '',
                ifsc_code: '',
                status: 'active'
            };
        }
    };
}
</script>
@endsection
