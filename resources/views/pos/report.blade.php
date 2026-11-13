@extends('layouts.app')

@section('title', 'Counter Sales Report')
@section('breadcrumb', 'Counter sales / Report')
@section('header_title', 'Counter sales report')

@section('content')
<div class="space-y-5" x-data="counterSalesReport()">

    <!-- Top Breadcrumb -->
    <div class="text-xs text-slate-400 font-medium">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
        <span class="mx-1">/</span>
        <a href="{{ route('pos.hub') }}" class="hover:text-slate-600 transition">Counter sales</a>
        <span class="mx-1">/</span>
        <span class="text-slate-700 font-semibold">Report</span>
    </div>

    <!-- Header Title & Subtitle matching Screenshot -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Counter sales report</h1>
                <p class="text-xs text-slate-500 mt-0.5">Filter and review every counter sale by date range and delivery person.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('pos.terminal') }}" class="px-4 py-2 bg-[#15803d] hover:bg-[#166534] text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-xs">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Start a sale</span>
            </a>
            <a href="{{ route('pos.hub') }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="layout-grid" class="w-4 h-4 text-slate-500"></i>
                <span>Hub Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Metric Summary Cards Box matching Screenshot 7 -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs grid grid-cols-1 sm:grid-cols-2 gap-6">
        
        <!-- Total Amount -->
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="text-lg font-black">Σ</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL AMOUNT</span>
                <span class="text-3xl font-black text-emerald-700">₹ {{ number_format($totalAmount, 0) }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $dateRangeText }}</p>
            </div>
        </div>

        <!-- Total Sales -->
        <div class="flex items-center gap-4 border-t sm:border-t-0 sm:border-l border-slate-100 pt-4 sm:pt-0 sm:pl-6">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL SALES</span>
                <span class="text-3xl font-black text-slate-900">{{ $totalSalesCount }}</span>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">across the selected filters</p>
            </div>
        </div>

    </div>

    <!-- Filter Collapsible Panel -->
    <div x-show="filterOpen" x-transition class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('pos.reports') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date :</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date :</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Payment Mode :</label>
                <select name="payment_mode" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white text-xs outline-none">
                    <option value="all">All Modes</option>
                    <option value="cash" {{ request('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ request('payment_mode') === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="card" {{ request('payment_mode') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="credit" {{ request('payment_mode') === 'credit' ? 'selected' : '' }}>Credit (Dues)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-[#005c53] hover:bg-[#004740] text-white font-bold rounded-lg transition text-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Apply Filter</span>
                </button>
                <a href="{{ route('pos.reports') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-lg transition text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Container Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Action Sub-header Bar matching Screenshot 7 -->
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs font-semibold text-slate-600">
                <span>{{ $orders->count() }} sales on this page</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="summaryModalOpen = true" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>View summary</span>
                </button>

                <button type="button" @click="filterOpen = !filterOpen" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Filter</span>
                </button>

                <a href="{{ route('pos.reports', array_merge(request()->query(), ['export' => 'csv'])) }}" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Download</span>
                </a>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-5">DATE · TIME</th>
                        <th class="py-3.5 px-5">PAYMENT</th>
                        <th class="py-3.5 px-5">ITEMS</th>
                        <th class="py-3.5 px-5 text-right">AMOUNT</th>
                        <th class="py-3.5 px-5 text-center w-24">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $ord)
                        @php
                            $firstItem = $ord->items->first();
                            $firstItemName = $firstItem && $firstItem->product ? $firstItem->product->name : 'Walk-in Item';
                            $firstItemQty = $firstItem ? ((float)$firstItem->quantity . ' ' . ($firstItem->product->unit ?? 'PC')) : '';
                            $moreCount = $ord->items->count() - 1;

                            $badgeClass = match(strtolower($ord->payment_mode)) {
                                'cash' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'upi' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'card' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'credit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Date & Time -->
                            <td class="py-4 px-5 whitespace-nowrap">
                                <div class="flex items-center gap-2.5 text-slate-800">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="font-medium font-mono text-slate-700">
                                        {{ $ord->created_at->format('d M, Y h:i A') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Payment Badge Pill -->
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold uppercase border {{ $badgeClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span>{{ $ord->payment_mode }}</span>
                                </span>
                            </td>

                            <!-- Items Breakdown -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-slate-800 font-bold">
                                        {{ $firstItemName }}
                                    </span>
                                    @if($firstItemQty)
                                        <span class="text-slate-400">·</span>
                                        <span class="text-slate-600 text-[11px] font-mono">{{ $firstItemQty }}</span>
                                    @endif

                                    @if($moreCount > 0)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            +{{ $moreCount }} more
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Amount -->
                            <td class="py-4 px-5 text-right whitespace-nowrap font-black font-mono text-slate-900 text-sm">
                                ₹ {{ number_format($ord->grand_total, 0) }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Info receipt view button -->
                                    <button 
                                        type="button" 
                                        @click="showOrderDetails({{ json_encode($ord->load(['items.product', 'counter', 'operator'])) }})" 
                                        class="w-7 h-7 rounded-full text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 flex items-center justify-center transition"
                                        title="View Sale Details"
                                    >
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Order button -->
                                    <form action="{{ route('pos.orders.destroy', $ord) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale #{{ $ord->order_number }}? Product inventory will be restored.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="w-7 h-7 rounded-full text-rose-500 hover:text-rose-700 hover:bg-rose-50 flex items-center justify-center transition"
                                            title="Delete Sale"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <span>No counter sales found for the selected criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Pagination matching Screenshot 7 -->
        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <div>
                @if($orders->total() > 0)
                    <span>{{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}</span>
                @else
                    <span>0 of 0</span>
                @endif
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        </div>

    </div>

    <!-- Sale Detail Modal -->
    <div 
        x-show="detailModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="detailModalOpen = false" 
            class="bg-white w-full max-w-lg rounded-2xl border border-slate-200 shadow-2xl overflow-hidden p-6 space-y-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900" x-text="'Bill #' + (activeOrder ? activeOrder.order_number : '')"></h3>
                        <p class="text-[11px] text-slate-400" x-text="activeOrder ? activeOrder.created_at : ''"></p>
                    </div>
                </div>
                <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <template x-if="activeOrder">
                <div class="space-y-4 text-xs">
                    <!-- Customer Details -->
                    <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Customer</span>
                            <span class="font-bold text-slate-800" x-text="activeOrder.customer_name || 'Walk-in Customer'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Phone</span>
                            <span class="font-mono text-slate-700" x-text="activeOrder.customer_phone || 'N/A'"></span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="py-2 px-3">Item</th>
                                    <th class="py-2 px-3 text-center">Qty</th>
                                    <th class="py-2 px-3 text-right">Rate</th>
                                    <th class="py-2 px-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in activeOrder.items" :key="item.id">
                                    <tr>
                                        <td class="py-2 px-3 font-semibold text-slate-800" x-text="item.product ? item.product.name : 'Item'"></td>
                                        <td class="py-2 px-3 text-center font-mono" x-text="item.quantity"></td>
                                        <td class="py-2 px-3 text-right font-mono" x-text="'₹' + item.unit_price"></td>
                                        <td class="py-2 px-3 text-right font-bold text-slate-900 font-mono" x-text="'₹' + item.total_price"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Strip -->
                    <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-1.5 font-medium">
                        <div class="flex justify-between items-center text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-mono font-bold" x-text="'₹' + activeOrder.subtotal"></span>
                        </div>
                        <div x-show="parseFloat(activeOrder.discount_amount) > 0" class="flex justify-between items-center text-rose-600">
                            <span>Discount:</span>
                            <span class="font-mono font-bold" x-text="'- ₹' + activeOrder.discount_amount"></span>
                        </div>
                        <div class="flex justify-between items-center border-t border-emerald-200/80 pt-1 text-sm font-black text-emerald-900">
                            <span>Grand Total:</span>
                            <span class="font-mono" x-text="'₹ ' + activeOrder.grand_total"></span>
                        </div>
                        <div class="flex justify-between items-center pt-1 text-xs text-slate-600">
                            <span>Payment Mode:</span>
                            <span class="font-bold uppercase text-slate-800" x-text="activeOrder.payment_mode"></span>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <a :href="'{{ url('pos/receipt') }}/' + activeOrder.id" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center gap-1.5">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Print Thermal Slip</span>
                        </a>
                        <button type="button" @click="detailModalOpen = false" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

@push('scripts')
<script>
function counterSalesReport() {
    return {
        filterOpen: false,
        summaryModalOpen: false,
        detailModalOpen: false,
        activeOrder: null,

        showOrderDetails(order) {
            this.activeOrder = order;
            this.detailModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        }
    };
}
</script>
@endpush
@endsection