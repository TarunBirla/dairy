@extends('layouts.app')

@section('title', 'Buyer Khata & Records')
@section('breadcrumb', 'Buyer Khata')
@section('header_title', 'Buyer Khata & Milk Records')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" onclick="openReceiveAmountModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>+ Receive Amount</span>
        </button>
        <button type="button" onclick="openPayAmountModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="minus-circle" class="w-4 h-4 text-rose-600"></i>
            <span>+ Pay Amount</span>
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

    <!-- Top Summary Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Receivable / Due</span>
                <div class="text-xl sm:text-2xl font-black font-mono text-emerald-700 mt-1">
                    ₹ {{ number_format($totalReceivable, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $selectedBuyer ? $selectedBuyer->name : 'All Buyers Combined' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Milk Sales (Debit)</span>
                <div class="text-xl sm:text-2xl font-black font-mono text-slate-900 mt-1">
                    ₹ {{ number_format($totalSalesAmount, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Total billed to buyers</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Received (Credit)</span>
                <div class="text-xl sm:text-2xl font-black font-mono text-amber-700 mt-1">
                    ₹ {{ number_format($totalReceivedAmount, 2) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">Total cash / UPI collected</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Filter Card matching Screenshot 5 (media_1791389718502.png) -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('buyers.khata') }}" class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-end gap-3 flex-1">
                <!-- Select Buyer -->
                <div class="min-w-[200px] flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Select Buyer</label>
                    <select name="buyer_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="">All Buyers (Consolidated)</option>
                        @foreach($buyers as $b)
                            <option value="{{ $b->id }}" {{ $buyerId == $b->id ? 'selected' : '' }}>
                                [{{ $b->buyer_code }}] {{ $b->name }} (Due: ₹{{ number_format($b->current_balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="min-w-[200px] flex-1 sm:flex-initial">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search Buyer</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search buyer name, code..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition h-[38px] flex items-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>
                    @if(!empty($buyerId) || !empty($search) || !empty($startDate))
                        <a href="{{ route('buyers.khata') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition h-[38px] flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Action Triggers -->
            <div class="flex items-center gap-2">
                @if($selectedBuyer)
                    <a href="{{ route('buyers.bill', $selectedBuyer->id) }}" target="_blank" class="px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5 h-[38px]">
                        <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Print Bill</span>
                    </a>
                @endif
                <button type="button" onclick="openReceiveAmountModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition h-[38px] flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Receive</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Khata Transactions Ledger Table matching Screenshot 5 -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                    Buyer Milk Records & Ledger
                </h3>
                <p class="text-[11px] text-slate-500">
                    {{ $selectedBuyer ? "Ledger statement for [{$selectedBuyer->buyer_code}] {$selectedBuyer->name}" : "Combined ledger across all buyers" }}
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-xl">
                Total Records: {{ count($transactions) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Buyer (Customer)</th>
                        <th class="py-2.5 px-3">Transaction Type</th>
                        <th class="py-2.5 px-3">Reference #</th>
                        <th class="py-2.5 px-3 text-right">Debit (Sale)</th>
                        <th class="py-2.5 px-3 text-right">Credit (Received)</th>
                        <th class="py-2.5 px-3">Payment Mode</th>
                        <th class="py-2.5 px-3">Comment / Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-medium text-slate-800">
                                {{ \Carbon\Carbon::parse($t->date)->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900 leading-tight">{{ $t->buyer->name ?? 'Unknown' }}</div>
                                <div class="text-[10px] font-mono text-slate-400">{{ $t->buyer->buyer_code ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($t->type === 'Milk Sale')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        Milk Sale
                                    </span>
                                @elseif($t->type === 'Received Amount')
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Received Amount
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Paid Amount
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[11px] font-bold text-slate-800">
                                {{ $t->reference }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $t->debit > 0 ? 'text-slate-900' : 'text-slate-300' }}">
                                {{ $t->debit > 0 ? '₹' . number_format($t->debit, 2) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $t->credit > 0 ? 'text-emerald-700' : 'text-slate-300' }}">
                                {{ $t->credit > 0 ? '₹' . number_format($t->credit, 2) : '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $t->payment_mode }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 max-w-[200px] truncate" title="{{ $t->comment }}">
                                {{ $t->comment }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="book-open" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold text-slate-500">No transactions found for this selection.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL: RECEIVE AMOUNT matching Screenshot 5 (media_1791389718502.png) ================= -->
<div id="receiveAmountModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-700">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Received Amount</h3>
                    <p class="text-xs text-slate-500">Record customer payment received</p>
                </div>
            </div>
            <button type="button" onclick="closeReceiveAmountModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('buyers.payments.store') }}" class="mt-4 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="type" value="received">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                <select name="buyer_id" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    <option value="">Select Customer...</option>
                    @foreach($buyers as $b)
                        <option value="{{ $b->id }}" {{ $buyerId == $b->id ? 'selected' : '' }}>
                            [{{ $b->buyer_code }}] {{ $b->name }} (Due: ₹{{ number_format($b->current_balance, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Date <span class="text-rose-500">*</span></label>
                <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" required placeholder="Enter Amount" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono font-bold text-slate-900 text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                <select name="payment_mode" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Comment</label>
                <input type="text" name="comment" placeholder="Enter Comment / Transaction Ref..." class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeReceiveAmountModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">Save Record</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: PAY AMOUNT ================= -->
<div id="payAmountModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-700">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pay Amount</h3>
                    <p class="text-xs text-slate-500">Record refund or advance paid to buyer</p>
                </div>
            </div>
            <button type="button" onclick="closePayAmountModal()" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('buyers.payments.store') }}" class="mt-4 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="type" value="paid">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                <select name="buyer_id" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-rose-500 outline-none font-medium">
                    <option value="">Select Customer...</option>
                    @foreach($buyers as $b)
                        <option value="{{ $b->id }}" {{ $buyerId == $b->id ? 'selected' : '' }}>
                            [{{ $b->buyer_code }}] {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Date <span class="text-rose-500">*</span></label>
                <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-rose-500 outline-none font-medium">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Amount <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" required placeholder="Enter Amount" class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-rose-500 outline-none font-mono font-bold text-slate-900 text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Payment Mode <span class="text-rose-500">*</span></label>
                <select name="payment_mode" required class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-rose-500 outline-none font-medium">
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Comment</label>
                <input type="text" name="comment" placeholder="Enter Comment / Reason..." class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-rose-500 outline-none">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closePayAmountModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs transition">Save Record</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openReceiveAmountModal() {
        document.getElementById('receiveAmountModal').classList.remove('hidden');
    }
    function closeReceiveAmountModal() {
        document.getElementById('receiveAmountModal').classList.add('hidden');
    }

    function openPayAmountModal() {
        document.getElementById('payAmountModal').classList.remove('hidden');
    }
    function closePayAmountModal() {
        document.getElementById('payAmountModal').classList.add('hidden');
    }
</script>
@endpush
