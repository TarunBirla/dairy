<!-- ============================================================== -->
<!-- SHARED MODALS FOR DELIVERY & BOY APP                           -->
<!-- ============================================================== -->

<!-- 1. CHANGE PRODUCT / EDIT QUANTITY MODAL -->
<div x-show="editProductModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div @click.away="editProductModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Change Product / Edit Quantity</h3>
                <p class="text-xs text-slate-400" x-text="editItem.customer_name"></p>
            </div>
            <button type="button" @click="editProductModal = false" class="w-7 h-7 rounded-full text-slate-400 hover:text-slate-600 flex items-center justify-center hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Select Product / उत्पाद चुनें</label>
                <select x-model="editItem.product_id" @change="onProductSelectChange()"
                        class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    <template x-for="p in productsList" :key="p.id">
                        <option :value="p.id" x-text="p.name + ' (₹' + (p.subscription_price || p.price) + '/' + p.unit + ')'" :selected="p.id == editItem.product_id"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Quantity / मात्रा</label>
                <div class="flex items-center gap-2">
                    <button type="button" @click="adjustEditQty(-0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">-</button>
                    <input type="number" step="0.1" min="0.1" x-model.number="editItem.quantity" @input="recalcEditTotal()"
                           class="w-full text-center text-sm font-black px-3 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
                    <button type="button" @click="adjustEditQty(0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">+</button>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Rate / Unit</span>
                    <span class="font-extrabold text-slate-800">₹ <span x-text="editItem.unit_price"></span></span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Amount</span>
                    <span class="text-base font-black text-emerald-700">₹ <span x-text="editItem.total_amount"></span></span>
                </div>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between gap-2 border-t border-slate-100">
            <button type="button" @click="deleteDeliveryItem(editItem.id)" class="px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition flex items-center gap-1">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Remove</span>
            </button>
            <div class="flex items-center gap-2">
                <button type="button" @click="editProductModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </button>
                <button type="button" @click="saveProductChange()" :disabled="isSavingProduct"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <span x-text="isSavingProduct ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. ADD MORE PRODUCTS MODAL -->
<div x-show="addProductModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div @click.away="addProductModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Add Product to Delivery</h3>
                <p class="text-xs text-slate-400" x-text="newItem.customer_name"></p>
            </div>
            <button type="button" @click="addProductModal = false" class="w-7 h-7 rounded-full text-slate-400 hover:text-slate-600 flex items-center justify-center hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Select Product / उत्पाद चुनें</label>
                <select x-model="newItem.product_id" @change="onNewProductSelectChange()"
                        class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    <template x-for="p in productsList" :key="p.id">
                        <option :value="p.id" x-text="p.name + ' (₹' + (p.subscription_price || p.price) + '/' + p.unit + ')'"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Quantity / मात्रा</label>
                <div class="flex items-center gap-2">
                    <button type="button" @click="adjustNewQty(-0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">-</button>
                    <input type="number" step="0.1" min="0.1" x-model.number="newItem.quantity" @input="recalcNewTotal()"
                           class="w-full text-center text-sm font-black px-3 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
                    <button type="button" @click="adjustNewQty(0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">+</button>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Rate / Unit</span>
                    <span class="font-extrabold text-slate-800">₹ <span x-text="newItem.unit_price"></span></span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Total Amount</span>
                    <span class="text-base font-black text-emerald-700">₹ <span x-text="newItem.total_amount"></span></span>
                </div>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button type="button" @click="addProductModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </button>
            <button type="button" @click="saveNewProduct()" :disabled="isAddingProduct"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                <span x-text="isAddingProduct ? 'Adding...' : 'Add to Delivery'"></span>
            </button>
        </div>
    </div>
</div>

<!-- 3. RECEIVE CASH MODAL -->
<div x-show="cashModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div @click.away="cashModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Receive Cash Payment</h3>
                <p class="text-xs text-slate-400" x-text="cashData.customer_name"></p>
            </div>
            <button type="button" @click="cashModal = false" class="w-7 h-7 rounded-full text-slate-400 hover:text-slate-600 flex items-center justify-center hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-3 bg-rose-50 border border-rose-200/80 rounded-2xl flex items-center justify-between">
            <span class="text-xs font-bold text-rose-800">Current Due:</span>
            <span class="text-base font-black text-rose-700">₹ <span x-text="formatNumber(cashData.current_balance)"></span></span>
        </div>

        <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quick Presets</label>
            <div class="grid grid-cols-4 gap-1.5">
                <button type="button" @click="cashData.amount = cashData.current_balance" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">Full Due</button>
                <button type="button" @click="cashData.amount = 500" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">₹500</button>
                <button type="button" @click="cashData.amount = 1000" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">₹1,000</button>
                <button type="button" @click="cashData.amount = 2000" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">₹2,000</button>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Amount Received (₹) <span class="text-rose-500">*</span></label>
            <input type="number" step="1" min="1" x-model.number="cashData.amount" placeholder="Enter amount"
                   class="w-full text-base font-black px-3.5 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Payment Mode</label>
            <select x-model="cashData.payment_method" class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                <option value="cash">Cash (नकद)</option>
                <option value="upi">UPI / PhonePe / GPay</option>
                <option value="bank">Bank Transfer</option>
            </select>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button type="button" @click="cashModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
            <button type="button" @click="submitCashPayment()" :disabled="isSubmittingCash || !cashData.amount"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition">
                <span x-text="isSubmittingCash ? 'Recording...' : 'Record Payment'"></span>
            </button>
        </div>
    </div>
</div>

<!-- 4. SKIP STOP MODAL -->
<div x-show="skipModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div @click.away="skipModal = false" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Skip Delivery Stop</h3>
                <p class="text-xs text-slate-400" x-text="skipData.customer_name"></p>
            </div>
            <button type="button" @click="skipModal = false" class="w-7 h-7 rounded-full text-slate-400 hover:text-slate-600 flex items-center justify-center hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Skipping <span class="text-rose-500">*</span></label>
            <select x-model="skipData.reason" class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                <option value="Customer Door Locked / Unavailable">Door Locked / Customer Unavailable (दरवाजा बंद)</option>
                <option value="Customer on Vacation / Leave">Customer on Leave / Requested Skip (छुट्टी पर)</option>
                <option value="Customer Refused Today">Refused Today (आज लेने से मना किया)</option>
                <option value="Out of Stock / Shortage">Shortage of Milk / Product (दूध समाप्त)</option>
                <option value="Incorrect Address">Address Not Found (पता नहीं मिला)</option>
            </select>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button type="button" @click="skipModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
            <button type="button" @click="submitSkipStop()" :disabled="isSubmittingSkip"
                    class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition">
                <span x-text="isSubmittingSkip ? 'Skipping...' : 'Confirm Skip'"></span>
            </button>
        </div>
    </div>
</div>
