@extends('layouts.app')

@section('title', 'Expenses')
@section('breadcrumb', 'Expenses')
@section('header_title', 'Business Expense Management')

@section('header_action')
    <button type="button" @click="$dispatch('open-expense-modal')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Expense</span>
    </button>
@endsection

@section('content')
<div x-data="expenseManager()" class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Total Expenses Recorded</span>
            <p class="text-3xl font-black text-rose-600 mt-1">₹ {{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.profit-loss') }}" class="px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold rounded-xl transition">
                View Profit & Loss &rarr;
            </a>
            <button type="button" @click="openExpenseModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                + New Expense
            </button>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Description / Purpose</th>
                        <th class="py-3 px-4">Vendor Name</th>
                        <th class="py-3 px-4">Mode</th>
                        <th class="py-3 px-4 text-right">Amount (₹)</th>
                        <th class="py-3 px-4">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $e)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono">{{ $e->expense_date->format('d M Y') }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $e->category->name }}</td>
                            <td class="py-3.5 px-4 text-slate-700">{{ $e->description }}</td>
                            <td class="py-3.5 px-4 text-slate-500">{{ $e->vendor_name ?? '—' }}</td>
                            <td class="py-3.5 px-4 uppercase font-bold text-slate-600">{{ $e->payment_mode }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-sm text-rose-600">
                                ₹ {{ number_format($e->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">{{ $e->recorder ? $e->recorder->name : 'Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No expenses recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- 1. Add Operating Expense Modal (Layer 50) -->
    <div x-show="showExpenseModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         @open-expense-modal.window="openExpenseModal()"
         aria-labelledby="modal-expense-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modal Backdrop with close on click -->
            <div x-show="showExpenseModal" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" 
                 @click="showExpenseModal = false">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content Card -->
            <div x-show="showExpenseModal" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                
                <div class="p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800" id="modal-expense-title">Add Operating Expense</h3>
                        <button type="button" @click="showExpenseModal = false" class="text-slate-400 hover:text-slate-600 p-1 text-lg leading-none">&times;</button>
                    </div>

                    <form action="{{ route('expenses.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700">Expense Category *</label>
                                <button type="button" 
                                        @click="openCategoryModal()" 
                                        class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition flex items-center gap-1">
                                    <span>+ Add new</span>
                                </button>
                            </div>
                            <select name="expense_category_id" x-model="selectedCategoryId" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition bg-white">
                                <option value="">Select Expense Category</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCategoryId"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) *</label>
                            <input type="number" step="1" name="amount" required placeholder="e.g. 500" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold text-rose-600 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Date *</label>
                                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Mode</label>
                                <select name="payment_mode" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Vendor / Payee</label>
                            <input type="text" name="vendor_name" placeholder="e.g. Indian Oil / Packaging Vendor" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Description *</label>
                            <textarea name="description" required rows="2" placeholder="e.g. Fuel for delivery bike route A" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showExpenseModal = false" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Inline Add Expense Category Modal (Layer 70 - Always on top!) -->
    <div x-show="showAddCategoryModal" 
         x-cloak 
         class="fixed inset-0 z-70 overflow-y-auto" 
         aria-labelledby="modal-category-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Distinct Darker Backdrop for Category Popup -->
            <div x-show="showAddCategoryModal" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" 
                 @click="closeCategoryModal()">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Category Card -->
            <div x-show="showAddCategoryModal" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 scale-95" 
                 x-transition:enter-end="opacity-100 scale-100" 
                 x-transition:leave="ease-in duration-150" 
                 x-transition:leave-start="opacity-100 scale-100" 
                 x-transition:leave-end="opacity-0 scale-95" 
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-category-title">Add expense category</h3>
                            <p class="text-xs text-slate-500 mt-1">Categories help segment operational costs for Profit & Loss reports.</p>
                        </div>
                        <button type="button" @click="closeCategoryModal()" class="text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category name *</label>
                            <input type="text" 
                                   x-ref="categoryInput" 
                                   x-model="newCategoryName" 
                                   @keydown.enter.prevent="saveCategory()" 
                                   placeholder="e.g. Fuel & Transport, Packaging, Electricity" 
                                   class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <p x-show="categoryError" x-text="categoryError" class="text-rose-600 text-[11px] mt-1"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2.5">
                        <button type="button" @click="closeCategoryModal()" class="px-4 py-2.5 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                        <button type="button" @click="saveCategory()" :disabled="savingCategory" class="px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <span x-show="!savingCategory">Save category</span>
                            <span x-show="savingCategory">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function expenseManager() {
    return {
        showExpenseModal: false,
        showAddCategoryModal: false,
        selectedCategoryId: '{{ $categories->first()?->id }}',
        categories: @json($categories),

        newCategoryName: '',
        categoryError: '',
        savingCategory: false,

        openExpenseModal() {
            this.showExpenseModal = true;
        },

        openCategoryModal() {
            this.categoryError = '';
            this.newCategoryName = '';
            this.showAddCategoryModal = true;
            this.$nextTick(() => {
                if (this.$refs.categoryInput) {
                    this.$refs.categoryInput.focus();
                }
            });
        },

        closeCategoryModal() {
            this.showAddCategoryModal = false;
            this.categoryError = '';
            this.newCategoryName = '';
            // Crucial: expense modal stays open in the background!
        },

        async saveCategory() {
            if (!this.newCategoryName.trim()) {
                this.categoryError = 'Please enter category name.';
                return;
            }
            this.categoryError = '';
            this.savingCategory = true;

            try {
                const response = await fetch('{{ route('expenses.categories.ajax') }}', {
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
                    this.showAddCategoryModal = false;
                    // Expense modal remains open with the new category automatically selected!
                } else {
                    this.categoryError = data.message || 'Error saving category.';
                }
            } catch (err) {
                this.categoryError = 'Network error. Please try again.';
            } finally {
                this.savingCategory = false;
            }
        }
    }
}
</script>
@endsection
