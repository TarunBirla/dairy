@extends('layouts.app')

@section('title', 'Delivery Mobile App | Simple Dairy')
@section('breadcrumb', 'Delivery App')
@section('header_title', 'Delivery App (Doorstep Run)')

@section('content')
<div class="max-w-md mx-auto space-y-3 pb-12" x-data="deliveryBoyApp()" x-init="init()">

    <!-- ============================================================== -->
    <!-- MOBILE TOP GREEN APP BAR (Matching Simple Dairy Screenshot 2)  -->
    <!-- ============================================================== -->
    <div class="bg-emerald-700 text-white rounded-2xl p-3.5 shadow-md space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('delivery.board') }}" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition" title="Back to Delivery Board">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-white"></i>
                </a>
                <span class="text-base font-black tracking-wide">Delivery</span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Shift Dropdown -->
                <div class="relative" x-data="{ shiftOpen: false }">
                    <button type="button" @click="shiftOpen = !shiftOpen" 
                            class="px-3 py-1 rounded-full bg-white/20 hover:bg-white/30 text-white text-xs font-extrabold flex items-center gap-1.5 transition">
                        @if($shift === 'morning')
                            <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-300"></i>
                            <span>Morning</span>
                        @else
                            <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-200"></i>
                            <span>Evening</span>
                        @endif
                        <i data-lucide="chevron-down" class="w-3 h-3 text-white/80"></i>
                    </button>
                    <div x-show="shiftOpen" @click.away="shiftOpen = false" x-cloak
                         class="absolute right-0 mt-1 w-32 bg-white rounded-xl shadow-xl border border-slate-100 p-1 z-30 text-xs text-slate-800 font-bold">
                        <a href="{{ route('delivery.boy-app', ['date' => $date, 'shift' => 'morning']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-emerald-50 text-slate-800">
                            <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Morning</span>
                        </a>
                        <a href="{{ route('delivery.boy-app', ['date' => $date, 'shift' => 'evening']) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-indigo-50 text-slate-800">
                            <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>Evening</span>
                        </a>
                    </div>
                </div>

                <!-- WhatsApp Launcher -->
                <a href="https://web.whatsapp.com" target="_blank" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
                    <i data-lucide="message-circle" class="w-4 h-4 text-emerald-200"></i>
                </a>
            </div>
        </div>

        <!-- Search Bar with Filter Icon -->
        <div class="relative">
            <input type="text" x-model="searchQuery" placeholder="Search..."
                   class="w-full pl-9 pr-9 py-2 text-xs text-slate-900 bg-white rounded-xl outline-none shadow-inner placeholder-slate-400 font-medium">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <button type="button" @click="filterOpen = !filterOpen" class="w-7 h-7 absolute right-1 top-1/2 -translate-y-1/2 flex items-center justify-center text-slate-500 hover:text-slate-800">
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <!-- Action Row: [Select] [Refresh] -->
        <div class="flex items-center justify-between text-xs pt-1">
            <button type="button" @click="toggleSelectMode()"
                    class="px-4 py-1.5 bg-white text-emerald-900 rounded-xl font-bold shadow-xs flex items-center gap-1.5 transition">
                <i data-lucide="check-square" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span x-text="selectMode ? 'Cancel Select' : 'Select'"></span>
            </button>

            <button type="button" @click="refreshData()" class="px-4 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-bold flex items-center gap-1.5 transition">
                <i data-lucide="rotate-cw" class="w-3.5 h-3.5" :class="{'animate-spin': isRefreshing}"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Filter Panel -->
    <div x-show="filterOpen" x-collapse class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs space-y-2 text-xs">
        <span class="block text-[10px] font-bold text-slate-400 uppercase">Filter Status</span>
        <div class="grid grid-cols-4 gap-1.5">
            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'" class="py-1 rounded-lg font-bold text-center">All</button>
            <button type="button" @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-700'" class="py-1 rounded-lg font-bold text-center">Pending</button>
            <button type="button" @click="activeTab = 'delivered'" :class="activeTab === 'delivered' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'" class="py-1 rounded-lg font-bold text-center">Done</button>
            <button type="button" @click="activeTab = 'on_leave'" :class="activeTab === 'on_leave' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-700'" class="py-1 rounded-lg font-bold text-center">Leave</button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- STATS CARD (Matching Screenshot 2: 57 TOTAL | 2 DONE | 55 LEFT) -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-3">
        <div class="grid grid-cols-3 divide-x divide-slate-100 text-center">
            <div>
                <span class="text-xl font-black text-slate-900 block leading-tight" x-text="totalCount"></span>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">TOTAL</span>
            </div>
            <div>
                <span class="text-xl font-black text-emerald-600 block leading-tight" x-text="deliveredCount"></span>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">DONE</span>
            </div>
            <div>
                <span class="text-xl font-black text-rose-500 block leading-tight" x-text="pendingCount"></span>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">LEFT</span>
            </div>
        </div>
    </div>

    <!-- Multi-select Bar on Mobile -->
    <div x-show="selectMode && selectedCustomerIds.length > 0" x-transition
         class="bg-slate-900 text-white rounded-2xl p-3 px-4 shadow-xl flex items-center justify-between">
        <span class="text-xs font-bold"><span x-text="selectedCustomerIds.length"></span> Selected</span>
        <button type="button" @click="bulkDeliverSelected()" :disabled="isBulkDelivering"
                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold rounded-xl transition flex items-center gap-1">
            <i data-lucide="check" class="w-3.5 h-3.5"></i>
            <span>Deliver Selected</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- CUSTOMER DELIVERY CARDS (Matching Screenshot 2 Exact Layout)   -->
    <!-- ============================================================== -->
    <div class="space-y-3">
        <template x-for="c in filteredCustomers" :key="c.id">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 space-y-3.5 transition"
                 :class="{'border-emerald-300 bg-emerald-50/10': c.status === 'delivered', 'border-rose-200 bg-rose-50/10': c.status === 'on_leave'}">
                
                <!-- Card Header: Avatar | Name | Due Badge | ⋮ Menu -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <template x-if="selectMode">
                            <input type="checkbox" :value="c.id" x-model="selectedCustomerIds" class="w-4 h-4 rounded text-emerald-600">
                        </template>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-xs shrink-0 select-none"
                             :class="c.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'"
                             x-text="getInitials(c.name)">
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900" x-text="c.name"></h4>
                            <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-0.5">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                <span class="truncate max-w-[150px]" x-text="c.locality || c.address || 'Area 0'"></span>
                            </div>
                            <span class="inline-block mt-1 px-2 py-0.2 text-[9px] font-extrabold uppercase rounded-full"
                                  :class="c.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : (c.status === 'on_leave' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600')"
                                  x-text="c.status"></span>
                        </div>
                    </div>

                    <div class="text-right flex items-center gap-2">
                        <div>
                            <template x-if="c.current_balance > 0">
                                <div>
                                    <span class="text-sm font-black text-rose-600">₹ <span x-text="formatNumber(c.current_balance)"></span></span>
                                    <span class="block text-[9px] font-extrabold text-rose-400 uppercase">DUE</span>
                                </div>
                            </template>
                            <template x-if="c.current_balance <= 0">
                                <span class="text-xs font-semibold text-emerald-600">₹0 DUE</span>
                            </template>
                        </div>

                        <!-- Menu Button -->
                        <div class="relative" x-data="{ openMenu: false }">
                            <button type="button" @click="openMenu = !openMenu" class="w-7 h-7 rounded-lg hover:bg-slate-100 text-slate-400 flex items-center justify-center">
                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                            </button>
                            <div x-show="openMenu" @click.away="openMenu = false" x-cloak
                                 class="absolute right-0 mt-1 w-36 bg-white rounded-xl shadow-xl border border-slate-100 p-1 z-30 text-xs font-semibold">
                                <button type="button" @click="openSkipModal(c); openMenu = false;" class="w-full text-left px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50">Skip Stop</button>
                                <template x-if="c.phone">
                                    <a :href="'tel:' + c.phone" class="block px-3 py-1.5 rounded-lg text-slate-700 hover:bg-slate-50">Call</a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Section: 🛍️ [Product Name] [0.5 litre ✏️] -->
                <div class="space-y-2 pt-1 border-t border-slate-100">
                    <template x-for="item in c.items" :key="item.id">
                        <div class="flex items-center justify-between bg-slate-50 p-2.5 px-3 rounded-xl border border-slate-100 text-xs">
                            <div class="flex items-center gap-2 font-bold text-slate-800">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span x-text="item.product_name"></span>
                            </div>

                            <!-- Qty with Pencil (Opens Product Change Modal) -->
                            <button type="button" @click="openEditProductModal(c, item)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-black text-slate-900 shadow-2xs hover:border-emerald-500">
                                <span x-text="item.quantity + ' ' + (item.product_unit || 'litre')"></span>
                                <i data-lucide="pencil" class="w-3 h-3 text-emerald-600"></i>
                            </button>
                        </div>
                    </template>

                    <!-- (+) Add More Products Link -->
                    <button type="button" @click="openAddProductModal(c)"
                            class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5 pt-1 pl-1">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>Add More Products</span>
                    </button>
                </div>

                <!-- Footer Action Buttons: [RECEIVE CASH] [TAP TO DELIVER ✓] -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openCashModal(c)"
                            class="py-2.5 px-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-extrabold rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <i data-lucide="banknote" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>RECEIVE CASH</span>
                    </button>

                    <template x-if="c.status === 'delivered'">
                        <button type="button" @click="undoDelivery(c)"
                                class="py-2.5 px-3 bg-emerald-100 text-emerald-800 text-xs font-black rounded-xl border border-emerald-200 flex items-center justify-center gap-1">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>DELIVERED ✓</span>
                        </button>
                    </template>
                    <template x-if="c.status !== 'delivered'">
                        <button type="button" @click="markCustomerDelivered(c)"
                                class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-black rounded-xl shadow-xs flex items-center justify-center gap-1 transition">
                            <span>TAP TO DELIVER ✓</span>
                        </button>
                    </template>
                </div>

            </div>
        </template>
    </div>

    <!-- Modals for Product Change, Add Product, Receive Cash, Skip Stop -->
    <!-- (Same robust modals as board.blade.php) -->
    @include('delivery.partials.modals')

</div>

<script>
function deliveryBoyApp() {
    return {
        customers: @json($customersData),
        productsList: @json($products),
        currentDate: '{{ $date }}',
        currentShift: '{{ $shift }}',

        searchQuery: '',
        activeTab: 'all',
        filterOpen: false,
        selectMode: false,
        selectedCustomerIds: [],
        isBulkDelivering: false,
        isRefreshing: false,

        // Modals state
        editProductModal: false,
        isSavingProduct: false,
        editItem: { id: null, customer_id: null, customer_name: '', product_id: null, quantity: 1, unit_price: 0, total_amount: 0 },

        addProductModal: false,
        isAddingProduct: false,
        newItem: { customer_id: null, customer_name: '', product_id: null, quantity: 1, unit_price: 0, total_amount: 0 },

        cashModal: false,
        isSubmittingCash: false,
        cashData: { customer_id: null, customer_name: '', current_balance: 0, amount: 0, payment_method: 'cash', notes: '' },

        skipModal: false,
        isSubmittingSkip: false,
        skipData: { customer_id: null, customer_name: '', reason: 'Customer Door Locked / Unavailable' },

        init() {
            if (window.lucide) window.lucide.createIcons();
        },

        get filteredCustomers() {
            let list = this.customers;
            if (this.activeTab !== 'all') {
                list = list.filter(c => c.status === this.activeTab);
            }
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(c => (c.name && c.name.toLowerCase().includes(q)) || (c.phone && c.phone.includes(q)) || (c.locality && c.locality.toLowerCase().includes(q)));
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            return list;
        },

        get totalCount() { return this.customers.length; },
        get deliveredCount() { return this.customers.filter(c => c.status === 'delivered').length; },
        get pendingCount() { return this.customers.filter(c => c.status === 'pending').length; },

        toggleSelectMode() {
            this.selectMode = !this.selectMode;
            if (!this.selectMode) this.selectedCustomerIds = [];
        },

        getInitials(name) {
            if (!name) return 'CU';
            const parts = name.trim().split(' ');
            return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
        },

        formatNumber(val) {
            return Number(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        async refreshData() {
            this.isRefreshing = true;
            try {
                const res = await fetch(`{{ route('delivery.board') }}?date=${this.currentDate}&shift=${this.currentShift}&ajax=1`);
                const data = await res.json();
                if (data.success) this.customers = data.customers;
            } catch (err) { console.error(err); }
            finally { this.isRefreshing = false; }
        },

        openEditProductModal(c, item) {
            this.editItem = { id: item.id, customer_id: c.id, customer_name: c.name, product_id: item.product_id, quantity: item.quantity, unit_price: item.unit_price, total_amount: item.total_amount };
            this.editProductModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        onProductSelectChange() {
            const found = this.productsList.find(p => String(p.id) === String(this.editItem.product_id));
            if (found) {
                this.editItem.unit_price = Number(found.subscription_price || found.price || 0);
                this.recalcEditTotal();
            }
        },

        adjustEditQty(delta) {
            this.editItem.quantity = Math.max(0.1, Number((this.editItem.quantity + delta).toFixed(2)));
            this.recalcEditTotal();
        },

        recalcEditTotal() {
            this.editItem.total_amount = Number((this.editItem.quantity * this.editItem.unit_price).toFixed(2));
        },

        async saveProductChange() {
            this.isSavingProduct = true;
            try {
                const res = await fetch(`/delivery/items/${this.editItem.id}/update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ product_id: this.editItem.product_id, quantity: this.editItem.quantity, unit_price: this.editItem.unit_price })
                });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.editItem.customer_id);
                    if (c) {
                        const idx = c.items.findIndex(i => i.id === this.editItem.id);
                        if (idx >= 0) c.items[idx] = data.item;
                    }
                    this.editProductModal = false;
                }
            } catch (err) { console.error(err); }
            finally { this.isSavingProduct = false; }
        },

        async deleteDeliveryItem(itemId) {
            if (!confirm('Remove this product?')) return;
            try {
                const res = await fetch(`/delivery/items/${itemId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.editItem.customer_id);
                    if (c) c.items = c.items.filter(i => i.id !== itemId);
                    this.editProductModal = false;
                }
            } catch (err) { console.error(err); }
        },

        openAddProductModal(c) {
            const first = this.productsList[0] || {};
            this.newItem = { customer_id: c.id, customer_name: c.name, product_id: first.id, quantity: 1, unit_price: Number(first.subscription_price || first.price || 0), total_amount: Number(first.subscription_price || first.price || 0) };
            this.addProductModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        onNewProductSelectChange() {
            const found = this.productsList.find(p => String(p.id) === String(this.newItem.product_id));
            if (found) {
                this.newItem.unit_price = Number(found.subscription_price || found.price || 0);
                this.recalcNewTotal();
            }
        },

        adjustNewQty(delta) {
            this.newItem.quantity = Math.max(0.1, Number((this.newItem.quantity + delta).toFixed(2)));
            this.recalcNewTotal();
        },

        recalcNewTotal() {
            this.newItem.total_amount = Number((this.newItem.quantity * this.newItem.unit_price).toFixed(2));
        },

        async saveNewProduct() {
            this.isAddingProduct = true;
            try {
                const res = await fetch(`{{ route('delivery.items.add') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ customer_id: this.newItem.customer_id, delivery_date: this.currentDate, shift: this.currentShift, product_id: this.newItem.product_id, quantity: this.newItem.quantity, unit_price: this.newItem.unit_price })
                });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.newItem.customer_id);
                    if (c) c.items.push(data.item);
                    this.addProductModal = false;
                }
            } catch (err) { console.error(err); }
            finally { this.isAddingProduct = false; }
        },

        async markCustomerDelivered(c) {
            try {
                const res = await fetch(`/delivery/deliver-customer/${c.id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ date: this.currentDate, shift: this.currentShift })
                });
                const data = await res.json();
                if (data.success) {
                    c.status = 'delivered';
                    c.items.forEach(i => i.status = 'delivered');
                }
            } catch (err) { console.error(err); }
        },

        async undoDelivery(c) {
            try {
                const res = await fetch(`/delivery/undo-deliver/${c.id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ date: this.currentDate, shift: this.currentShift })
                });
                const data = await res.json();
                if (data.success) {
                    c.status = 'pending';
                    c.items.forEach(i => i.status = 'pending');
                }
            } catch (err) { console.error(err); }
        },

        async bulkDeliverSelected() {
            if (this.selectedCustomerIds.length === 0) return;
            this.isBulkDelivering = true;
            try {
                const res = await fetch(`{{ route('delivery.bulk-deliver') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ customer_ids: this.selectedCustomerIds, date: this.currentDate, shift: this.currentShift })
                });
                const data = await res.json();
                if (data.success) {
                    this.customers.forEach(c => {
                        if (this.selectedCustomerIds.includes(c.id)) {
                            c.status = 'delivered';
                            c.items.forEach(i => i.status = 'delivered');
                        }
                    });
                    this.selectedCustomerIds = [];
                    this.selectMode = false;
                }
            } catch (err) { console.error(err); }
            finally { this.isBulkDelivering = false; }
        },

        openCashModal(c) {
            this.cashData = { customer_id: c.id, customer_name: c.name, current_balance: c.current_balance, amount: c.current_balance > 0 ? c.current_balance : '', payment_method: 'cash', notes: '' };
            this.cashModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        async submitCashPayment() {
            this.isSubmittingCash = true;
            try {
                const res = await fetch(`{{ route('delivery.receive-cash') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ customer_id: this.cashData.customer_id, amount: this.cashData.amount, payment_method: this.cashData.payment_method, notes: this.cashData.notes, date: this.currentDate })
                });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.cashData.customer_id);
                    if (c) c.current_balance = data.new_balance;
                    this.cashModal = false;
                }
            } catch (err) { console.error(err); }
            finally { this.isSubmittingCash = false; }
        },

        openSkipModal(c) {
            this.skipData = { customer_id: c.id, customer_name: c.name, reason: 'Customer Door Locked / Unavailable' };
            this.skipModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        async submitSkipStop() {
            this.isSubmittingSkip = true;
            try {
                const res = await fetch(`/delivery/skip-customer/${this.skipData.customer_id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ date: this.currentDate, shift: this.currentShift, reason: this.skipData.reason })
                });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.skipData.customer_id);
                    if (c) {
                        c.status = 'skipped';
                        c.failure_reason = this.skipData.reason;
                    }
                    this.skipModal = false;
                }
            } catch (err) { console.error(err); }
            finally { this.isSubmittingSkip = false; }
        }
    };
}
</script>
@endsection
