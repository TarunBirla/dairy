@extends('layouts.app')

@section('title', 'Products')
@section('breadcrumb', 'Products')
@section('header_title', 'Products / Product list')

@section('header_action')
    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add product</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ quickModalOpen: false, quickProduct: null }">

    <!-- Page Title & Subtitle matching screenshot -->
    <div>
        <h2 class="text-xl font-bold text-slate-900">Products</h2>
        <p class="text-xs text-slate-500 mt-0.5">Manage your product catalog, prices, and stock availability.</p>
    </div>

    <!-- 3 Stat Summary Cards matching screenshot -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Total Products Card -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-400/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                </div>
                <span>TOTAL PRODUCTS</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalProducts }}</span>
            </div>
        </div>

        <!-- In Stock Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                </div>
                <span>IN STOCK</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $inStockCount }}</span>
            </div>
        </div>

        <!-- Out of Stock Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                </div>
                <span>OUT OF STOCK</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-amber-700">{{ $outOfStockCount }}</span>
            </div>
        </div>
    </div>

    <!-- Search & Download Filter Bar matching screenshot -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('products.index') }}" class="w-full sm:w-96 flex items-center relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search product, category, description..." 
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
            >
        </form>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition shadow-2xs">
                <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Download</span>
            </button>
        </div>
    </div>

    <!-- Products Data Table matching screenshot exactly! -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Product</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4 min-w-[240px]">Description</th>
                        <th class="py-3.5 px-4 text-center">Stock</th>
                        <th class="py-3.5 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $index => $product)
                        <tr class="hover:bg-slate-50/80 transition group" id="product-row-{{ $product->id }}">
                            <!-- # -->
                            <td class="py-4 px-4 text-center font-medium text-slate-400">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Product -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 flex-shrink-0">
                                        <i data-lucide="package" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm leading-tight">{{ $product->name }}</p>
                                        <span class="text-[11px] text-slate-400 font-medium">
                                            {{ $product->category ? $product->category->name : $product->product_type }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="py-4 px-4 font-bold text-slate-900 text-sm whitespace-nowrap">
                                ₹ {{ number_format($product->price, 0) }}
                            </td>

                            <!-- Unit -->
                            <td class="py-4 px-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $product->unit }}
                            </td>

                            <!-- Description -->
                            <td class="py-4 px-4 text-slate-500 text-xs">
                                {{ $product->description ? $product->description : '—' }}
                            </td>

                            <!-- Stock (Badge / Toggle Switch) -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($product->current_stock > 0)
                                        <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                                            {{ (float)$product->current_stock }} {{ $product->unit }}
                                        </span>
                                    @endif
                                    
                                    <!-- Toggle Switch matching screenshot -->
                                    <button 
                                        type="button" 
                                        onclick="toggleStockStatus({{ $product->id }})"
                                        id="toggle-btn-{{ $product->id }}"
                                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $product->in_stock ? 'bg-emerald-500' : 'bg-slate-200' }}"
                                        role="switch" 
                                        aria-checked="{{ $product->in_stock ? 'true' : 'false' }}"
                                    >
                                        <span 
                                            id="toggle-dot-{{ $product->id }}"
                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $product->in_stock ? 'translate-x-4' : 'translate-x-0' }}"
                                        ></span>
                                    </button>
                                </div>
                            </td>

                            <!-- Actions matching screenshot (Edit, Delete, + Button) -->
                            <td class="py-4 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Edit Icon -->
                                    <a href="{{ route('products.edit', $product) }}" title="Edit Product" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Delete Icon -->
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Product" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>

                                    <!-- Quick Stock + Button matching green circular + button in screenshot -->
                                    <button 
                                        type="button" 
                                        @click="quickProduct = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', unit: '{{ $product->unit }}' }; quickModalOpen = true;"
                                        title="Quick Stock In"
                                        class="w-7 h-7 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shadow-xs transition transform hover:scale-105"
                                    >
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">No products found</p>
                                <p class="text-xs text-slate-400 mt-1">Get started by creating your first dairy product.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Stock In Modal -->
    <div x-show="quickModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="quickModalOpen = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Quick Stock In</h3>
                <button @click="quickModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'{{ url('products') }}/' + (quickProduct ? quickProduct.id : '') + '/quick-stock'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product</label>
                    <p class="text-sm font-bold text-emerald-700" x-text="quickProduct ? quickProduct.name : ''"></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Add Quantity</label>
                    <div class="relative">
                        <input type="number" step="0.1" name="quantity" required placeholder="e.g. 10" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <span class="absolute right-3 top-2 text-xs font-semibold text-slate-400" x-text="quickProduct ? quickProduct.unit : ''"></span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Morning production batch" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="quickModalOpen = false" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Add to Stock</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function toggleStockStatus(productId) {
        fetch(`{{ url('products') }}/${productId}/toggle-stock`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById(`toggle-btn-${productId}`);
                const dot = document.getElementById(`toggle-dot-${productId}`);
                if (data.in_stock) {
                    btn.classList.remove('bg-slate-200');
                    btn.classList.add('bg-emerald-500');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-4');
                } else {
                    btn.classList.remove('bg-emerald-500');
                    btn.classList.add('bg-slate-200');
                    dot.classList.remove('translate-x-4');
                    dot.classList.add('translate-x-0');
                }
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
@endsection
