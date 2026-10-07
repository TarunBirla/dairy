@extends('layouts.app')

@section('title', 'Inventory & Stock Ledger')
@section('breadcrumb', 'Inventory')
@section('header_title', 'Stock Management & Inventory Transactions')

@section('header_action')
    <div class="flex gap-2">
        <button type="button" onclick="openTxModal('purchase_inward')" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
            <span>Buy / Stock Inward</span>
        </button>
        <button type="button" onclick="openTxModal('sale_outward')" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
            <span>Sales / Outward</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Sub-tabs: All Stock, Inward, Outward -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 bg-slate-100/80 p-1 rounded-xl">
            <a href="{{ route('inventory.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('type') ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                All Movements
            </a>
            <a href="{{ route('inventory.index', ['type' => 'inward']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('type') == 'inward' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Buy / Inward Stock
            </a>
            <a href="{{ route('inventory.index', ['type' => 'outward']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('type') == 'outward' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Sales / Outward Stock
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="boxes" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Product Catalog</span>
            </a>
            <a href="{{ route('inventory.bottles') }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="wine" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Bottle Tracking</span>
            </a>
        </div>
    </div>

    <!-- Low Stock Alert Banner -->
    @if($lowStockProducts->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900">Immediate Reorder Required</h4>
                    <p class="text-[11px] text-amber-700">The following products are at or below minimum threshold: <b>{{ $lowStockProducts->pluck('name')->join(', ') }}</b></p>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg">Top Up Stock</a>
        </div>
    @endif

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Complete Stock Ledger & Audit Trail</h3>
            <span class="text-xs text-slate-400">Showing {{ $transactions->total() }} transactions</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Product Name</th>
                        <th class="py-3 px-4">Transaction Type</th>
                        <th class="py-3 px-4">Quantity</th>
                        <th class="py-3 px-4">Balance After</th>
                        <th class="py-3 px-4">Recorded By</th>
                        <th class="py-3 px-4">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $tx->product ? $tx->product->name : 'Product' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                    {{ str_contains($tx->transaction_type, 'inward') ? 'bg-emerald-100 text-emerald-800' : (str_contains($tx->transaction_type, 'wastage') ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ str_replace('_', ' ', $tx->transaction_type) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-black {{ str_contains($tx->transaction_type, 'inward') ? 'text-emerald-700' : 'text-slate-800' }}">
                                {{ str_contains($tx->transaction_type, 'inward') ? '+' : '-' }}{{ $tx->quantity }} {{ $tx->product ? $tx->product->unit : '' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $tx->balance_after }} {{ $tx->product ? $tx->product->unit : '' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $tx->recorder ? $tx->recorder->name : 'System' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $tx->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No inventory transactions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Manual Transaction Modal -->
    <div id="txModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800" id="txModalTitle">Record Stock Movement</h3>
                <button type="button" onclick="document.getElementById('txModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            <form action="{{ route('inventory.transaction') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        @foreach($products as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->name }} (Available: {{ $pr->current_stock }} {{ $pr->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Movement Type *</label>
                    <select name="transaction_type" id="txTypeSelect" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="purchase_inward">Buy Product / Purchase Inward (+ Stock)</option>
                        <option value="production_inward">Production Inward (+ Stock)</option>
                        <option value="sale_outward">Sales Product / Outward (- Stock)</option>
                        <option value="wastage">Spoilage / Wastage (- Stock)</option>
                        <option value="adjustment">Stock Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                    <input type="number" step="0.1" name="quantity" required placeholder="e.g. 20" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Cost Price (₹)</label>
                    <input type="number" step="0.5" name="unit_cost" placeholder="Optional purchase rate" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Reason</label>
                    <input type="text" name="notes" placeholder="e.g. Purchase order #34 / Wholesale inward" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('txModal').classList.add('hidden')" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Record Entry</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openTxModal(type) {
    const modal = document.getElementById('txModal');
    const select = document.getElementById('txTypeSelect');
    const title = document.getElementById('txModalTitle');
    if (select && type) {
        select.value = type;
        if (type === 'purchase_inward') {
            title.innerText = 'Buy Product / Inward Stock';
        } else if (type === 'sale_outward') {
            title.innerText = 'Sales Product / Outward Stock';
        } else {
            title.innerText = 'Record Stock Movement';
        }
    }
    modal.classList.remove('hidden');
}
</script>
@endsection
