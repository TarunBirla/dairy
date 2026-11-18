@extends('layouts.app')

@section('title', 'Farmer Deduction Wallets')
@section('breadcrumb', 'Deduction Wallets')
@section('header_title', 'Farmer Deduction Wallets & Ledger')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('farmer-invoices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
            <span>Farmer Invoices</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    addModal: false, 
    adjustModal: false,
    selectedFarmerId: '', 
    selectedFarmerName: '',
    selectedCategory: 'Cattle Feed',
    addAmount: '',
    addRemarks: '',
    adjustType: 'credit',
    adjustAmount: '',
    adjustRemarks: '',
    openAddDeduction(id, name) {
        this.selectedFarmerId = id;
        this.selectedFarmerName = name;
        this.addAmount = '';
        this.addRemarks = '';
        this.addModal = true;
    },
    openAdjust(id, name) {
        this.selectedFarmerId = id;
        this.selectedFarmerName = name;
        this.adjustAmount = '';
        this.adjustRemarks = '';
        this.adjustType = 'credit';
        this.adjustModal = true;
    }
}">

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

    <!-- 3 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Outstanding Dues</span>
                <span class="text-2xl font-black font-mono text-amber-600 block mt-0.5">₹{{ number_format($totalOutstanding, 2) }}</span>
                <span class="text-[11px] text-slate-400">Recoverable across all farmers</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Farmers with Dues</span>
                <span class="text-2xl font-black font-mono text-slate-800 block mt-0.5">{{ $farmersWithDues }} / {{ $totalWalletsCount }}</span>
                <span class="text-[11px] text-slate-400">Farmers having non-zero balance</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Active Wallets</span>
                <span class="text-2xl font-black font-mono text-emerald-600 block mt-0.5">{{ $totalWalletsCount }}</span>
                <span class="text-[11px] text-slate-400">Auto-synchronized with billing</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('wallets.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-96">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by Farmer Name, Code, Phone..." 
                       class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50/50">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Search
                </button>
                @if(!empty($search))
                    <a href="{{ route('wallets.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Wallets Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Farmer Deduction Wallets</h3>
                    <p class="text-[11px] text-slate-500">{{ $farmers->total() }} Total Farmers</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Farmer Name</th>
                        <th class="py-3 px-4">Phone</th>
                        <th class="py-3 px-4">Center / Route</th>
                        <th class="py-3 px-4 text-right">Outstanding Wallet Balance</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($farmers as $f)
                        @php
                            $bal = (float) ($f->wallet->balance ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-800">{{ $f->farmer_code }}</td>
                            <td class="py-2.5 px-4">
                                <a href="{{ route('wallets.show', $f->id) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition">
                                    {{ $f->name }}
                                </a>
                                @if(!empty($f->name_hi))
                                    <span class="text-[11px] text-slate-400 block font-normal">{{ $f->name_hi }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 font-mono text-slate-600">{{ $f->phone ?? '-' }}</td>
                            <td class="py-2.5 px-4 text-slate-500">
                                {{ $f->collectionCenter->name ?? 'Main Center' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-black text-sm {{ $bal > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                ₹{{ number_format($bal, 2) }}
                            </td>
                            <td class="py-2.5 px-4 text-center">
                                @if($bal > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Outstanding Dues
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Cleared
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openAddDeduction('{{ $f->id }}', '{{ addslashes($f->name) }}')"
                                            title="Add Deduction" 
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg border border-amber-200 transition">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="openAdjust('{{ $f->id }}', '{{ addslashes($f->name) }}')"
                                            title="Manual Adjustment" 
                                            class="p-1.5 text-sky-600 hover:bg-sky-50 rounded-lg border border-sky-200 transition">
                                        <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <a href="{{ route('wallets.show', $f->id) }}" 
                                       title="View Full Ledger"
                                       class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition inline-flex items-center gap-1">
                                        <span>Ledger</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                No farmers found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($farmers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $farmers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal 1: Quick Add Deduction -->
    <div x-show="addModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="addModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150"
             @click.away="addModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Add Deduction to Wallet</h4>
                        <p class="text-[11px] text-slate-500" x-text="'Farmer: ' + selectedFarmerName"></p>
                    </div>
                </div>
                <button type="button" @click="addModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/wallets/' + selectedFarmerId + '/deductions'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deduction Category <span class="text-rose-500">*</span></label>
                    <select name="category" x-model="selectedCategory" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @foreach($availableCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Deduction Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" x-model="addAmount" placeholder="e.g. 500.00" required
                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 font-mono font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Remarks / Note</label>
                    <textarea name="remarks" x-model="addRemarks" rows="2" placeholder="e.g. 2 Bags Khal / Medical visit"
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Add Deduction</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Quick Adjustment -->
    <div x-show="adjustModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="adjustModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-150"
             @click.away="adjustModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Manual Wallet Adjustment</h4>
                        <p class="text-[11px] text-slate-500" x-text="'Farmer: ' + selectedFarmerName"></p>
                    </div>
                </div>
                <button type="button" @click="adjustModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/wallets/' + selectedFarmerId + '/adjust'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Adjustment Action <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-medium"
                               :class="adjustType === 'credit' ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="adjustment_type" value="credit" x-model="adjustType" class="text-amber-600">
                            <span>Increase Dues (Credit)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border rounded-xl cursor-pointer text-xs font-medium"
                               :class="adjustType === 'debit' ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="adjustment_type" value="debit" x-model="adjustType" class="text-emerald-600">
                            <span>Waive/Reduce (Debit)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Category <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500">
                        @foreach($availableCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Adjustment Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" x-model="adjustAmount" placeholder="e.g. 250.00" required
                           class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-mono font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Mandatory Audit Remarks <span class="text-rose-500">*</span></label>
                    <textarea name="remarks" x-model="adjustRemarks" rows="2" placeholder="Reason for manual adjustment..." required
                              class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="adjustModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Apply Adjustment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection