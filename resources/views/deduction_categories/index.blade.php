@extends('layouts.app')

@section('title', 'Deductions Category')
@section('breadcrumb', 'Deductions / Categories')
@section('header_title', 'Deduction Categories Management')

@section('header_action')
    <button type="button" onclick="openAddCategoryModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>+ Add Category</span>
    </button>
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

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL CATEGORIES</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Deduction item classifications</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <i data-lucide="tags" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE CATEGORIES</span>
                <p class="text-2xl font-black text-emerald-700 mt-1">{{ $activeCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Available in entry dropdowns</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">INACTIVE CATEGORIES</span>
                <p class="text-2xl font-black text-slate-500 mt-1">{{ $inactiveCount }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Disabled / Archived</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
                <i data-lucide="pause-circle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('deduction-categories.index') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative w-full sm:w-72">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search category, code, description..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                </div>

                <div class="min-w-[130px]">
                    <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="all">All Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    <span>Search</span>
                </button>

                @if(!empty($search) || $status !== 'all')
                    <a href="{{ route('deduction-categories.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline">
                        Reset
                    </a>
                @endif
            </div>

            <div>
                <a href="{{ route('deductions.index') }}" class="px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-2xs transition inline-flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Back to Deductions</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Deduction Categories / कटौती श्रेणियां</h3>
                <p class="text-xs text-slate-500">Categories used to classify farmer deductions in Collections and Deductions module.</p>
            </div>
            <span class="text-xs text-slate-500 font-medium">
                Total: <strong>{{ $categories->total() }}</strong> categories
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-emerald-50/60 border-b border-emerald-100/80 text-[11px] font-bold text-emerald-950 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Hindi Name / विवरण</th>
                        <th class="py-3 px-4">Code / Identifier</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($categories as $index => $cat)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium">
                                {{ $categories->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ $cat->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">
                                {{ $cat->name_hi ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[11px] bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $cat->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $cat->description ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($cat->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick="openEditCategoryModal({{ json_encode($cat) }})" 
                                            title="Edit Category"
                                            class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 flex items-center justify-center transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('deduction-categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete category {{ addslashes($cat->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                title="Delete Category"
                                                class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 flex items-center justify-center transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="tag" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-semibold">No deduction categories found.</p>
                                <button type="button" onclick="openAddCategoryModal()" class="mt-2 text-xs font-bold text-emerald-600 hover:underline">+ Add new category</button>
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

</div>

<!-- ================= MODAL: ADD / EDIT CATEGORY ================= -->
<div id="categoryModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="p-4 sm:p-5 bg-emerald-600 text-white flex items-center justify-between">
            <h3 class="text-sm font-bold flex items-center gap-2">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span id="categoryModalTitle">Add Deduction Category</span>
            </h3>
            <button type="button" onclick="closeCategoryModal()" class="text-white/80 hover:text-white transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="categoryForm" action="{{ route('deduction-categories.store') }}" method="POST" class="p-5 sm:p-6 space-y-4 text-xs">
            @csrf
            <div id="categoryMethodContainer"></div>

            <!-- Name -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Category Name (English) *
                </label>
                <input type="text" name="name" id="cat_field_name" required placeholder="e.g. Cattle Feed, Medicine..." class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-semibold text-slate-900">
            </div>

            <!-- Hindi Name -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Hindi Name / विवरण (वैकल्पिक)
                </label>
                <input type="text" name="name_hi" id="cat_field_name_hi" placeholder="e.g. पशु आहार, दवाई, डॉक्टर शुल्क..." class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium text-slate-800">
            </div>

            <!-- Code / Identifier -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Category Code / Identifier
                    <span class="text-[10px] text-slate-400 font-normal">(Leave empty to auto-generate)</span>
                </label>
                <input type="text" name="code" id="cat_field_code" placeholder="e.g. cattle_feed, medicine" class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-mono text-slate-700">
            </div>

            <!-- Status -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Status *
                </label>
                <select name="status" id="cat_field_status" required class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-semibold">
                    <option value="active">Active (सक्रिय - Show in dropdowns)</option>
                    <option value="inactive">Inactive (निष्क्रिय - Hide)</option>
                </select>
            </div>

            <!-- Description -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">
                    Description / विवरण
                </label>
                <textarea name="description" id="cat_field_description" rows="2" placeholder="Optional details about this deduction category..." class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none resize-none"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" id="catSubmitBtn" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddCategoryModal() {
        document.getElementById('categoryModalTitle').textContent = 'Add Deduction Category';
        document.getElementById('catSubmitBtn').textContent = 'Save Category';
        const form = document.getElementById('categoryForm');
        form.action = "{{ route('deduction-categories.store') }}";
        document.getElementById('categoryMethodContainer').innerHTML = '';

        document.getElementById('cat_field_name').value = '';
        document.getElementById('cat_field_name_hi').value = '';
        document.getElementById('cat_field_code').value = '';
        document.getElementById('cat_field_status').value = 'active';
        document.getElementById('cat_field_description').value = '';

        document.getElementById('categoryModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function openEditCategoryModal(cat) {
        document.getElementById('categoryModalTitle').textContent = `Edit Category: ${cat.name}`;
        document.getElementById('catSubmitBtn').textContent = 'Update Category';
        const form = document.getElementById('categoryForm');
        form.action = `/deduction-categories/${cat.id}`;
        document.getElementById('categoryMethodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('cat_field_name').value = cat.name || '';
        document.getElementById('cat_field_name_hi').value = cat.name_hi || '';
        document.getElementById('cat_field_code').value = cat.code || '';
        document.getElementById('cat_field_status').value = cat.status || 'active';
        document.getElementById('cat_field_description').value = cat.description || '';

        document.getElementById('categoryModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('categoryModal')) closeCategoryModal();
    });
</script>
@endpush
