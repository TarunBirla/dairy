@extends('layouts.app')

@section('title', 'Vehicle Advance List')
@section('breadcrumb', 'Vehicle Advances')
@section('header_title', 'Vehicle & Driver Advance Management')

@section('header_action')
    <div class="flex flex-wrap items-center gap-2">
        <!-- Receive Button -->
        <button type="button" onclick="openReceiveModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
            <span>Receive</span>
        </button>

        <!-- Add New Advance Button -->
        <button type="button" onclick="openAddAdvanceModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4 text-emerald-600"></i>
            <span>+ Add New Advance</span>
        </button>

        <!-- Import Advance Button -->
        <button type="button" onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="upload" class="w-4 h-4 text-emerald-600"></i>
            <span>Import Advance</span>
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

    <!-- Top Filter Bar matching Screenshot 2 (media_1791389941427.png) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('vehicles.advances.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Vehicle Selector -->
                <div class="min-w-[200px] flex-1 sm:flex-initial">
                    <select name="vehicle_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Vehicles</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ $vehicleId == $v->id ? 'selected' : '' }}>
                                {{ $v->vehicle_number }} - {{ ucfirst($v->vehicle_type) }} ({{ $v->driver_name ?? 'No Driver' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search input -->
                <div class="relative w-full sm:w-80">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search vehicle, driver, voucher..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Search</span>
                    </button>
                    @if(!empty($search) || (!empty($vehicleId) && $vehicleId !== 'all'))
                        <a href="{{ route('vehicles.advances.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Print Action Button -->
            <a href="{{ route('vehicles.advances.print', ['vehicle_id' => $vehicleId]) }}" target="_blank" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                <i data-lucide="printer" class="w-4 h-4 text-emerald-600"></i>
                <span>Print</span>
            </a>
        </form>
    </div>

    <!-- 3 Summary KPI Cards matching Screenshot 2 (media_1791389941427.png) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Advance -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-100">Total Advance</span>
                <div class="text-2xl font-black font-mono mt-1">
                    Rs. {{ number_format($totalAdvance, 2) }}
                </div>
                <p class="text-[11px] text-blue-200 mt-0.5">Total principal advances issued</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center">
                <i data-lucide="hand-coins" class="w-6 h-6 text-white"></i>
            </div>
        </div>

        <!-- Card 2: Total Paid -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Total Paid</span>
                <div class="text-2xl font-black font-mono mt-1">
                    Rs. {{ number_format($totalPaid, 2) }}
                </div>
                <p class="text-[11px] text-emerald-200 mt-0.5">Total recovered repayment amount</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center">
                <i data-lucide="check-check" class="w-6 h-6 text-white"></i>
            </div>
        </div>

        <!-- Card 3: Total Balance -->
        <div class="bg-gradient-to-br from-rose-600 to-red-700 text-white p-5 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-rose-100">Total Balance</span>
                <div class="text-2xl font-black font-mono mt-1">
                    Rs. {{ number_format($totalBalance, 2) }}
                </div>
                <p class="text-[11px] text-rose-200 mt-0.5">Net outstanding advance balance</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6 text-white"></i>
            </div>
        </div>
    </div>

    <!-- Vehicle Advance Listing Table matching Screenshot 2 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Vehicle Advance List</h3>
                <p class="text-[11px] text-slate-500">Advance disbursements, repayment recoveries, and balance</p>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                Total: {{ $advances->total() }} Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 text-center w-12">S.No</th>
                        <th class="py-2.5 px-3">Driver Code</th>
                        <th class="py-2.5 px-3">Vehicle Number</th>
                        <th class="py-2.5 px-3">Driver Name</th>
                        <th class="py-2.5 px-3 text-right">Advance Amount</th>
                        <th class="py-2.5 px-3 text-right">Paid</th>
                        <th class="py-2.5 px-3 text-right font-bold text-rose-700">Total Balance</th>
                        <th class="py-2.5 px-3 text-center min-w-[120px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($advances as $index => $adv)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 text-center font-mono text-slate-400">
                                {{ $advances->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] font-bold text-slate-800">
                                {{ $adv->driver->driver_code ?? ($adv->vehicle->driver_code ?? 'VEH' . str_pad($adv->vehicle_id, 4, '0', STR_PAD_LEFT)) }}
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900 uppercase">
                                {{ $adv->vehicle->vehicle_number ?? '-' }}
                                <div class="text-[10px] text-slate-400 font-normal">{{ $adv->voucher_no }} ({{ $adv->advance_date->format('d/m/Y') }})</div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900">{{ $adv->driver->name ?? ($adv->vehicle->driver_name ?? 'Driver') }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-800">
                                Rs. {{ number_format($adv->amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-emerald-700 font-semibold">
                                Rs. {{ number_format($adv->paid_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-rose-700 text-xs">
                                Rs. {{ number_format($adv->balance_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Receive Payment against this advance -->
                                    <button type="button" onclick="openReceiveForAdvance({{ json_encode($adv) }})" title="Receive Repayment" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition">
                                        <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Edit Advance -->
                                    <button type="button" onclick="openEditAdvanceModal({{ json_encode($adv) }})" title="Edit" class="p-1.5 text-slate-500 hover:text-sky-700 hover:bg-sky-50 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Delete Advance -->
                                    <form method="POST" action="{{ route('vehicles.advances.destroy', $adv->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete advance voucher {{ $adv->voucher_no }}?');">
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
                                <i data-lucide="hand-coins" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No vehicle advances recorded yet.</p>
                                <button type="button" onclick="openAddAdvanceModal()" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Issue Advance Now</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($advances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ================= MODAL 1: ADD NEW ADVANCE matching Screenshot 3 (media_1791389958966.png) ================= -->
<div id="addAdvanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Add New Advance</h3>
                    <p class="text-xs text-slate-500">Issue cash or fuel advance to vehicle</p>
                </div>
            </div>
            <button type="button" onclick="closeAddAdvanceModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('vehicles.advances.store') }}" class="mt-4 space-y-4 text-xs">
            @csrf

            <!-- Vehicle Selection -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Vehicle <span class="text-rose-500">*</span></label>
                <select name="vehicle_id" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    <option value="">Select Vehicle...</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">
                            {{ $v->vehicle_number }} - {{ ucfirst($v->vehicle_type) }} ({{ $v->driver_name ?? 'No Driver' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date & Amount -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="advance_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="Enter Amount" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-bold text-slate-900">
                </div>
            </div>

            <!-- Voucher & Interest Rate -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Voucher <span class="text-rose-500">*</span></label>
                    <input type="text" name="voucher_no" required value="{{ $nextVoucher }}" placeholder="e.g. VADV-101" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-bold uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Interest Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="interest_rate" value="0" placeholder="0" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono">
                </div>
            </div>

            <!-- Remark -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Remark</label>
                <textarea name="remarks" rows="2" placeholder="Fuel, maintenance, personal advance details..." class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none resize-none"></textarea>
            </div>

            <!-- Actions matching Screenshot 3 -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="reset" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Reset</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs transition">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: RECEIVE REPAYMENT matching Screenshot 2 "Receive" Button ================= -->
<div id="receiveRepaymentModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Receive Advance Repayment</h3>
                    <p class="text-xs text-slate-500">Record recovered payment from driver/vehicle</p>
                </div>
            </div>
            <button type="button" onclick="closeReceiveModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('vehicles.advances.repayment') }}" class="mt-4 space-y-4 text-xs">
            @csrf

            <!-- Select Advance -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Select Advance Voucher <span class="text-rose-500">*</span></label>
                <select name="vehicle_advance_id" id="repayAdvanceSelect" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    <option value="">Select Advance...</option>
                    @foreach($advances as $a)
                        @if($a->balance_amount > 0)
                            <option value="{{ $a->id }}">
                                {{ $a->voucher_no }} - {{ $a->vehicle->vehicle_number ?? 'Vehicle' }} (Bal: Rs. {{ number_format($a->balance_amount, 2) }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Date & Amount -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="repayment_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="Enter Amount" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-bold text-slate-900">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Payment Mode</label>
                <select name="payment_mode" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Salary Deduction">Salary Deduction</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Remarks</label>
                <input type="text" name="remarks" placeholder="Repayment remarks..." class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeReceiveModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Save Repayment</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 3: IMPORT ADVANCE matching Screenshot 4 (media_1791389968940.png) ================= -->
<div id="importAdvanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Import Advance</h3>
                    <p class="text-xs text-slate-500">Bulk upload advances from CSV</p>
                </div>
            </div>
            <button type="button" onclick="closeImportModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('vehicles.advances.import-csv') }}" enctype="multipart/form-data" class="mt-5 space-y-4 text-xs">
            @csrf

            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-emerald-500 transition">
                <input type="file" name="csv_file" id="csvFileInput" required accept=".csv,.txt" class="hidden" onchange="document.getElementById('fileNameDisplay').textContent = this.files[0]?.name || ''">
                
                <button type="button" onclick="document.getElementById('csvFileInput').click()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                    <span>+ Select File</span>
                </button>
                <div id="fileNameDisplay" class="mt-2 text-[11px] font-mono text-slate-700 font-bold"></div>
                <p class="text-[11px] text-slate-400 mt-1">Upload .csv or .txt file</p>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('vehicles.advances.sample-csv') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Download Sample</span>
                </a>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Import CSV</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 4: EDIT ADVANCE ================= -->
<div id="editAdvanceModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 flex items-center justify-center text-sky-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="editAdvanceHeader">Edit Advance</h3>
                    <p class="text-xs text-slate-500">Update advance voucher details</p>
                </div>
            </div>
            <button type="button" onclick="closeEditAdvanceModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editAdvanceForm" method="POST" class="mt-4 space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Vehicle</label>
                <select name="vehicle_id" id="editVehicleSelect" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->vehicle_number }} - {{ ucfirst($v->vehicle_type) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Date</label>
                    <input type="date" name="advance_date" id="editAdvanceDate" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Amount</label>
                    <input type="number" step="0.01" name="amount" id="editAmount" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono font-bold text-slate-900">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Voucher</label>
                    <input type="text" name="voucher_no" id="editVoucher" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono font-bold uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Interest Rate (%)</label>
                    <input type="number" step="0.01" name="interest_rate" id="editInterest" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-mono">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Remark</label>
                <textarea name="remarks" id="editRemarks" rows="2" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none resize-none"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditAdvanceModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Update Advance</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddAdvanceModal() {
        document.getElementById('addAdvanceModal').classList.remove('hidden');
    }
    function closeAddAdvanceModal() {
        document.getElementById('addAdvanceModal').classList.add('hidden');
    }

    function openReceiveModal() {
        document.getElementById('receiveRepaymentModal').classList.remove('hidden');
    }
    function closeReceiveModal() {
        document.getElementById('receiveRepaymentModal').classList.add('hidden');
    }

    function openReceiveForAdvance(adv) {
        openReceiveModal();
        const sel = document.getElementById('repayAdvanceSelect');
        if (sel) {
            sel.value = adv.id;
        }
    }

    function openImportModal() {
        document.getElementById('importAdvanceModal').classList.remove('hidden');
    }
    function closeImportModal() {
        document.getElementById('importAdvanceModal').classList.add('hidden');
    }

    function openEditAdvanceModal(adv) {
        document.getElementById('editAdvanceHeader').textContent = `Edit Advance #${adv.voucher_no}`;
        document.getElementById('editAdvanceForm').action = `/vehicles/advances/${adv.id}`;
        document.getElementById('editVehicleSelect').value = adv.vehicle_id;
        document.getElementById('editAdvanceDate').value = (adv.advance_date || '').split('T')[0];
        document.getElementById('editAmount').value = adv.amount;
        document.getElementById('editVoucher').value = adv.voucher_no;
        document.getElementById('editInterest').value = adv.interest_rate || 0;
        document.getElementById('editRemarks').value = adv.remarks || '';
        document.getElementById('editAdvanceModal').classList.remove('hidden');
    }
    function closeEditAdvanceModal() {
        document.getElementById('editAdvanceModal').classList.add('hidden');
    }
</script>
@endpush
