@extends('layouts.app')

@section('title', 'Edit ' . $product->name)
@section('breadcrumb', 'Edit Product')
@section('header_title', 'Products / ' . $product->name)

@section('content')
<div x-data="productEditForm()" class="max-w-3xl mx-auto">
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Product: {{ $product->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Update pricing, category, unit, and inventory specifications.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Back</a>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $product->name) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Product Code</label>
                    <input type="text" readonly value="{{ $product->code }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-500">
                </div>
            </div>

            <!-- Category & Unit Section (Product Type Removed) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Category with + Add new -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Product Category <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <button type="button" @click="showCategoryModal = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                            <span>+ Add new</span>
                        </button>
                    </div>
                    <select name="category_id" x-model="selectedCategoryId" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition bg-white">
                        <option value="">Select Category</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCategoryId"></option>
                        </template>
                    </select>
                </div>

                <!-- Unit with + Add new -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Unit of Measure *</label>
                        <button type="button" @click="showUnitModal = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                            <span>+ Add new</span>
                        </button>
                    </div>
                    <select name="unit" x-model="selectedUnit" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition bg-white">
                        <template x-for="u in units" :key="u.value">
                            <option :value="u.value" x-text="u.label" :selected="u.value == selectedUnit"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Retail Price (₹) *</label>
                    <input type="number" step="0.5" name="price" required value="{{ old('price', $product->price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subscription Price (₹)</label>
                    <input type="number" step="0.5" name="subscription_price" value="{{ old('subscription_price', $product->subscription_price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cost Price (₹)</label>
                    <input type="number" step="0.5" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description / Notes</label>
                <textarea name="description" rows="2" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2.5 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Update Product</button>
            </div>
        </form>
    </div>

    <!-- Category Modal -->
    <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCategoryModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showCategoryModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showCategoryModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-title">Add product category</h3>
                            <p class="text-xs text-slate-500 mt-1">Categories help group similar products on bills and the customer app.</p>
                        </div>
                        <button type="button" @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category name *</label>
                            <input type="text" x-model="newCategoryName" @keydown.enter.prevent="saveCategory()" placeholder="e.g. Milk, Curd, Ghee" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <p x-show="categoryError" x-text="categoryError" class="text-rose-600 text-[11px] mt-1"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2.5">
                        <button type="button" @click="showCategoryModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                        <button type="button" @click="saveCategory()" :disabled="savingCategory" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <span x-show="!savingCategory">Save category</span>
                            <span x-show="savingCategory">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Modal -->
    <div x-show="showUnitModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-unit" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUnitModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showUnitModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showUnitModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-unit">Add unit of measure</h3>
                            <p class="text-xs text-slate-500 mt-1">Specify unit label and symbol for packaging or billing.</p>
                        </div>
                        <button type="button" @click="showUnitModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit name / label *</label>
                            <input type="text" x-model="newUnitLabel" @keydown.enter.prevent="saveUnit()" placeholder="e.g. 500ml Pouch, Jar, Can, Box" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <p x-show="unitError" x-text="unitError" class="text-rose-600 text-[11px] mt-1"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2.5">
                        <button type="button" @click="showUnitModal = false" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                        <button type="button" @click="saveUnit()" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save unit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productEditForm() {
    return {
        showCategoryModal: false,
        showUnitModal: false,
        newCategoryName: '',
        categoryError: '',
        savingCategory: false,
        selectedCategoryId: '{{ old('category_id', $product->category_id) }}',
        categories: @json($categories),

        newUnitLabel: '',
        unitError: '',
        selectedUnit: '{{ old('unit', $product->unit) }}',
        units: [
            { value: 'liter', label: 'Liter (ltr)' },
            { value: 'kg', label: 'Kilogram (kg)' },
            { value: 'bottle', label: 'Bottle' },
            { value: 'piece', label: 'Piece / Bag' },
            { value: 'pack', label: 'Pack / Pouch' },
        ],

        init() {
            // If current unit not in default list, add it
            const current = '{{ $product->unit }}';
            if (current && !this.units.some(u => u.value === current)) {
                this.units.push({ value: current, label: current });
            }
        },

        async saveCategory() {
            if (!this.newCategoryName.trim()) {
                this.categoryError = 'Please enter a category name.';
                return;
            }
            this.categoryError = '';
            this.savingCategory = true;

            try {
                const response = await fetch('{{ route('products.categories.ajax') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: this.newCategoryName.trim() })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    const exists = this.categories.find(c => c.id == data.category.id);
                    if (!exists) {
                        this.categories.push(data.category);
                    }
                    this.selectedCategoryId = data.category.id;
                    this.newCategoryName = '';
                    this.showCategoryModal = false;
                } else {
                    this.categoryError = data.message || 'Error saving category.';
                }
            } catch (err) {
                this.categoryError = 'Network error. Please try again.';
            } finally {
                this.savingCategory = false;
            }
        },

        saveUnit() {
            if (!this.newUnitLabel.trim()) {
                this.unitError = 'Please enter unit name.';
                return;
            }
            const clean = this.newUnitLabel.trim();
            const val = clean.toLowerCase().replace(/[^a-z0-9]/g, '_');
            const exists = this.units.find(u => u.value === val);
            if (!exists) {
                this.units.push({ value: val, label: clean });
            }
            this.selectedUnit = val;
            this.newUnitLabel = '';
            this.unitError = '';
            this.showUnitModal = false;
        }
    }
}
</script>
@endsection
