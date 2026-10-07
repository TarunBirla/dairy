@extends('layouts.app')

@section('title', 'Farmers Master')
@section('breadcrumb', 'Farmers')
@section('header_title', 'Farmer & Milk Supplier Management')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Register Farmer</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="farmerManager()" @open-farmer-modal.window="openCreateModal()">

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL FARMERS</span>
            <p class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalFarmers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE SUPPLIERS</span>
            <p class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $activeFarmers }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PAYABLE DUES</span>
            <p class="text-3xl font-extrabold text-indigo-700 mt-2">₹ {{ number_format($totalPayable, 2) }}</p>
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
                            placeholder="Name, code FAR-xxx, village, phone..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                        >
                    </div>
                </div>

                <!-- Animal Classification Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Animal Type</label>
                    <select name="animal_type" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Animals</option>
                        <option value="cow" {{ request('animal_type') === 'cow' ? 'selected' : '' }}>Cow Milk (गाय)</option>
                        <option value="buffalo" {{ request('animal_type') === 'buffalo' ? 'selected' : '' }}>Buffalo Milk (भैंस)</option>
                        <option value="mixed" {{ request('animal_type') === 'mixed' ? 'selected' : '' }}>Mixed Milk</option>
                    </select>
                </div>

                <!-- Collection Center Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Collection Center</label>
                    <select name="center_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 transition">
                        <option value="">All Centers</option>
                        @foreach($centers as $c)
                            <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
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

    <!-- Farmers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Registered Farmers & Suppliers</h3>
                <p class="text-xs text-slate-400">Total catalog of milk suppliers with banking information and live ledger balances</p>
            </div>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Farmer</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Farmer Name</th>
                        <th class="py-3 px-4">Village / Location</th>
                        <th class="py-3 px-4">Animal Type</th>
                        <th class="py-3 px-4">Bank / UPI</th>
                        <th class="py-3 px-4">Ledger Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6 w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($farmers as $farmer)
                        <tr class="hover:bg-slate-50/70 transition group">
                            <!-- Code -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $farmer->farmer_code }}
                            </td>

                            <!-- Farmer Name & Phone -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('farmers.show', $farmer) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition block">
                                    {{ $farmer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400 font-mono">{{ $farmer->phone ?? 'No phone' }}</span>
                            </td>

                            <!-- Village / Location -->
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $farmer->village ?? '—' }}
                                <span class="block text-[10px] text-slate-400">{{ $farmer->collectionCenter ? $farmer->collectionCenter->name : '' }}</span>
                            </td>

                            <!-- Animal Type -->
                            <td class="py-3.5 px-4 capitalize">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $farmer->animal_type === 'buffalo' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $farmer->animal_type === 'cow' ? '🐄 Cow' : ($farmer->animal_type === 'buffalo' ? '🐃 Buffalo' : '🥛 Mixed') }}
                                </span>
                            </td>

                            <!-- Bank / UPI -->
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                @if($farmer->account_number)
                                    <span class="font-semibold text-slate-700 block">{{ $farmer->bank_name ?: 'Bank Account' }}</span>
                                    <span class="block font-mono text-[10px] text-slate-400">A/C: {{ substr($farmer->account_number, -4) ? '••••' . substr($farmer->account_number, -4) : '' }}</span>
                                @elseif($farmer->upi_id)
                                    <span class="font-mono text-emerald-700 font-semibold">{{ $farmer->upi_id }}</span>
                                @else
                                    <span class="text-slate-400">Cash Mode</span>
                                @endif
                            </td>

                            <!-- Ledger Balance -->
                            <td class="py-3.5 px-4 font-extrabold text-sm {{ $farmer->current_balance > 0 ? 'text-indigo-700' : ($farmer->current_balance < 0 ? 'text-rose-600' : 'text-slate-600') }}">
                                ₹ {{ number_format($farmer->current_balance, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $farmer->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($farmer->status === 'blocked' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $farmer->status }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('farmers.show', $farmer) }}" title="View Passbook & Ledger" class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition">
                                        Passbook
                                    </a>
                                    
                                    <!-- Edit in popup modal on the same page -->
                                    <button type="button" 
                                            @click="openEditModal({
                                                id: {{ $farmer->id }},
                                                farmer_code: '{{ addslashes($farmer->farmer_code) }}',
                                                name: '{{ addslashes($farmer->name) }}',
                                                phone: '{{ addslashes($farmer->phone ?? '') }}',
                                                village: '{{ addslashes($farmer->village ?? '') }}',
                                                address: '{{ addslashes($farmer->address ?? '') }}',
                                                branch_id: {{ $farmer->branch_id ? $farmer->branch_id : 'null' }},
                                                collection_center_id: {{ $farmer->collection_center_id ? $farmer->collection_center_id : 'null' }},
                                                rate_chart_id: {{ $farmer->rate_chart_id ? $farmer->rate_chart_id : 'null' }},
                                                animal_type: '{{ $farmer->animal_type }}',
                                                bank_name: '{{ addslashes($farmer->bank_name ?? '') }}',
                                                account_number: '{{ addslashes($farmer->account_number ?? '') }}',
                                                ifsc_code: '{{ addslashes($farmer->ifsc_code ?? '') }}',
                                                upi_id: '{{ addslashes($farmer->upi_id ?? '') }}',
                                                status: '{{ $farmer->status }}'
                                            })"
                                            title="Edit Profile" 
                                            class="p-1.5 text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="user-x" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No farmers registered yet.</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ Register Farmer" to onboard milk producers.</p>
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
    <!-- IN-PAGE POPUP MODAL: REGISTER / EDIT FARMER                    -->
    <!-- Matching user screenshot media_1791375064514.png               -->
    <!-- ============================================================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="closeModal()">

            <!-- Modal Header matching screenshot -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="isEditMode ? 'Edit Farmer Profile' : 'Farmer Registration'"></h3>
                        <p class="text-xs text-slate-400">Capture supplier code, animal classification, banking, and quality rate rules.</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form matching screenshot fields -->
            <form :action="formAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Farmer Full Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Farmer Full Name *
                        </label>
                        <input type="text" name="name" x-model="form.name" required placeholder="e.g. Ramesh Patel"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Farmer Code -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Farmer Code *
                        </label>
                        <input type="text" name="farmer_code" x-model="form.farmer_code" required placeholder="e.g. FAR-107"
                               class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Mobile Number
                        </label>
                        <input type="text" name="phone" x-model="form.phone" placeholder="e.g. 9826011111"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Village / Tehsil -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Village / Tehsil
                        </label>
                        <input type="text" name="village" x-model="form.village" placeholder="e.g. Palasia / Green Valley"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    </div>

                    <!-- Animal Classification -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Animal Classification *
                        </label>
                        <select name="animal_type" x-model="form.animal_type" required
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="cow">Cow Milk (गाय)</option>
                            <option value="buffalo">Buffalo Milk (भैंस)</option>
                            <option value="mixed">Mixed Milk</option>
                        </select>
                    </div>

                    <!-- Collection Center -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Collection Center
                        </label>
                        <select name="collection_center_id" x-model="form.collection_center_id"
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">-- No Center --</option>
                            @foreach($centers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rate Chart Applied -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Rate Chart Applied
                        </label>
                        <select name="rate_chart_id" x-model="form.rate_chart_id"
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="">Default Quality Chart</option>
                            @foreach($rateCharts as $rc)
                                <option value="{{ $rc->id }}">{{ $rc->name }} ({{ ucfirst($rc->milk_type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status (Active / Inactive) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Status
                        </label>
                        <select name="status" x-model="form.status"
                                class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                </div>

                <!-- Settlement Bank & UPI Details Box matching screenshot -->
                <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200/80 space-y-3">
                    <h4 class="text-xs font-bold text-slate-800">Settlement Bank & UPI Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" x-model="form.bank_name" placeholder="e.g. State Bank of India"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Account Number</label>
                            <input type="text" name="account_number" x-model="form.account_number" placeholder="e.g. 30891283712"
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">IFSC Code</label>
                            <input type="text" name="ifsc_code" x-model="form.ifsc_code" placeholder="e.g. SBIN0001234"
                                   class="w-full px-3 py-1.5 text-xs font-mono uppercase border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">UPI ID</label>
                            <input type="text" name="upi_id" x-model="form.upi_id" placeholder="e.g. 9826011111@upi"
                                   class="w-full px-3 py-1.5 text-xs font-mono border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Full Postal Address -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Full Postal Address
                    </label>
                    <textarea name="address" x-model="form.address" rows="2" placeholder="Full residential village address"
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>

                <!-- Modal Actions matching screenshot -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-black rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span x-text="isEditMode ? 'Update Farmer' : 'Register Farmer'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function farmerManager() {
    return {
        showModal: false,
        isEditMode: false,
        formAction: '{{ route("farmers.store") }}',

        form: {
            id: null,
            farmer_code: '{{ $nextCode }}',
            name: '',
            phone: '',
            village: '',
            address: '',
            branch_id: null,
            collection_center_id: '{{ $centers->first()->id ?? "" }}',
            rate_chart_id: '',
            animal_type: 'cow',
            bank_name: '',
            account_number: '',
            ifsc_code: '',
            upi_id: '',
            status: 'active'
        },

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
            this.form = { ...item };
            this.showModal = true;
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
                farmer_code: '{{ $nextCode }}',
                name: '',
                phone: '',
                village: '',
                address: '',
                branch_id: null,
                collection_center_id: '{{ $centers->first()->id ?? "" }}',
                rate_chart_id: '',
                animal_type: 'cow',
                bank_name: '',
                account_number: '',
                ifsc_code: '',
                upi_id: '',
                status: 'active'
            };
        }
    };
}
</script>
@endsection
