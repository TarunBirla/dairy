@extends('layouts.app')

@section('title', 'Categories')
@section('breadcrumb', 'Categories')
@section('header_title', 'Products / Categories')

@section('header_action')
    <button type="button" @click="openCreateCategoryModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Category</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="categoryManagementPage()">

    <!-- Navigation Tabs (Products vs Categories vs Buy Products) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            <span>All Products</span>
        </a>
        <a href="{{ route('products.categories.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition bg-emerald-600 text-white shadow-xs flex items-center gap-1.5">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('purchases.index') }}" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg transition text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1.5">
            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
            <span>Buy Products</span>
        </a>
    </div>

    <!-- Page Title & Subtitle -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Product Categories</h2>
            <p class="text-xs text-slate-500 mt-0.5">Group your dairy and retail products for catalog navigation and reporting.</p>
        </div>
        <button type="button" @click="openCreateCategoryModal()" class="sm:hidden inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Category</span>
        </button>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Total Categories Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                </div>
                <span>TOTAL CATEGORIES</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalCategories }}</span>
            </div>
        </div>

        <!-- Categorized Products Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold tracking-wide uppercase">
                <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                </div>
                <span>PRODUCTS CATEGORIZED</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-blue-600">{{ $totalProductsCategorized }}</span>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('products.categories.index') }}" class="w-full sm:w-96 flex items-center relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search category name, slug or description..." 
                class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
            >
        </form>

        <button type="button" @click="openCreateCategoryModal()" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Add Category</span>
        </button>
    </div>

    <!-- Categories Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/70 border-b border-slate-200/80 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4 min-w-[200px]">Category Name</th>
                        <th class="py-3.5 px-4">Slug</th>
                        <th class="py-3.5 px-4 min-w-[240px]">Description</th>
                        <th class="py-3.5 px-4 text-center">Products</th>
                        <th class="py-3.5 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $index => $category)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- # -->
                            <td class="py-4 px-4 text-center font-medium text-slate-400">
                                {{ $categories->firstItem() + $index }}
                            </td>

                            <!-- Category Name -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                        <i data-lucide="folder" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm leading-tight">{{ $category->name }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="py-4 px-4 text-slate-500 font-mono text-[11px]">
                                <span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600">
                                    {{ $category->slug }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-4 px-4 text-slate-500 text-xs">
                                {{ $category->description ?: '—' }}
                            </td>

                            <!-- Products Count -->
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 px-2.5 py-1 rounded-md transition">
                                    <i data-lucide="package" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $category->products_count }} {{ Str::plural('Product', $category->products_count) }}</span>
                                </a>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        @click="openEditCategoryModal({{ json_encode($category) }})"
                                        title="Edit Category" 
                                        class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('products.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Category" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="folder-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">No categories found</p>
                                <p class="text-xs text-slate-400 mt-1">Get started by creating your first product category.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- ================= ADD CATEGORY MODAL POPUP ================= -->
    <div 
        x-show="createCategoryModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        aria-labelledby="add-category-title" 
        role="dialog" 
        aria-modal="true"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            @click.away="createCategoryModalOpen = false"
            x-show="createCategoryModalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 my-6 relative"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="add-category-title">Add Product Category</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Categories help organize products for billing and catalogues.</p>
                </div>
                <button type="button" @click="createCategoryModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('products.categories.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Milk, Sweets, Cattle Feed" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Brief description of products in this category..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" @click="createCategoryModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= EDIT CATEGORY MODAL POPUP ================= -->
    <div 
        x-show="editCategoryModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        aria-labelledby="edit-category-title" 
        role="dialog" 
        aria-modal="true"
    >
        <div 
            @click.away="editCategoryModalOpen = false"
            x-show="editCategoryModalOpen"
            class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 my-6 relative"
        >
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="edit-category-title">Edit Product Category</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Modify category name and description.</p>
                </div>
                <button type="button" @click="editCategoryModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('products/categories') }}/' + (editingCategory ? editingCategory.id : '')" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category Name *</label>
                    <input type="text" name="name" x-model="editingCategoryName" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <textarea name="description" x-model="editingCategoryDescription" rows="3" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"></textarea>
                </div>

                <div class="pt-3 flex justify-end gap-2.5 border-t border-slate-100">
                    <button type="button" @click="editCategoryModalOpen = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Update Category</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function categoryManagementPage() {
        return {
            createCategoryModalOpen: false,
            editCategoryModalOpen: false,
            editingCategory: null,
            editingCategoryName: '',
            editingCategoryDescription: '',

            openCreateCategoryModal() {
                this.createCategoryModalOpen = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            openEditCategoryModal(cat) {
                this.editingCategory = cat;
                this.editingCategoryName = cat.name;
                this.editingCategoryDescription = cat.description || '';
                this.editCategoryModalOpen = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        };
    }
</script>
@endpush
@endsection
