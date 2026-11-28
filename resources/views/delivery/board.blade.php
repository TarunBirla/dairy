@extends('layouts.app')

@section('title', 'Delivery Entries | Simple Dairy')
@section('breadcrumb', 'Delivery Entries')
@section('header_title', 'Delivery entries')

@section('content')
<div x-data="deliveryManager()" x-init="init()" class="space-y-4">

    <!-- ============================================================== -->
    <!-- TOP ROSTER CARD (Matching Simple Dairy Reference Screenshot)   -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 space-y-4">
        
        <!-- Top Row: Date Box + Shift + Today's Roster & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            <!-- Left: Calendar Box & Date Navigation -->
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1 shadow-xs">
                    <button type="button" @click="navigateDate(-1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 flex items-center justify-center transition">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <div class="px-3 text-center cursor-pointer" @click="shiftDateModal = true">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            {{ \Carbon\Carbon::parse($date)->format('D') }}
                        </span>
                        <span class="text-base font-extrabold text-slate-900 leading-none">
                            {{ \Carbon\Carbon::parse($date)->format('d M') }}
                        </span>
                    </div>
                    <button type="button" @click="navigateDate(1)" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 flex items-center justify-center transition">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Shift Badge -->
                <div class="px-3 py-1.5 rounded-xl border {{ $shift === 'morning' ? 'bg-amber-50/70 border-amber-200 text-amber-800' : 'bg-indigo-50/70 border-indigo-200 text-indigo-800' }} text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                    @if($shift === 'morning')
                        <i data-lucide="sun" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Morning Shift</span>
                    @else
                        <i data-lucide="moon" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Evening Shift</span>
                    @endif
                </div>
            </div>

            <!-- Right: Today's Roster Progress & Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-left sm:text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">TODAY'S ROSTER</span>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">
                        <span class="text-emerald-600" x-text="deliveredCount"></span>
                        <span class="text-slate-300">/</span>
                        <span x-text="totalCount"></span>
                        <span class="text-xs font-semibold text-slate-400 ml-1">DELIVERED</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="refreshData()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="rotate-cw" class="w-3.5 h-3.5" :class="{'animate-spin': isRefreshing}"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>

                    <button type="button" @click="shiftDateModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Change shift / date</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Multi-segment Progress Bar (Delivered / Pending / On Leave) -->
        <div class="space-y-1.5">
            <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden flex">
                <div class="bg-emerald-500 transition-all duration-300" :style="'width: ' + deliveredPercent + '%'"></div>
                <div class="bg-amber-400 transition-all duration-300" :style="'width: ' + pendingPercent + '%'"></div>
                <div class="bg-rose-400 transition-all duration-300" :style="'width: ' + leavePercent + '%'"></div>
            </div>

            <div class="flex items-center flex-wrap gap-4 text-xs font-bold text-slate-600 pt-1">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span x-text="deliveredCount"></span>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase">Delivered</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <span x-text="pendingCount"></span>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase">Pending</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                    <span x-text="onLeaveCount"></span>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase">On Leave / Skip</span>
                </div>
                <div class="ml-auto text-xs font-semibold text-slate-500 hidden sm:block">
                    Total Volume: <b class="text-slate-800">{{ number_format($stats['total_quantity'], 1) }} Ltr</b> • Cash: <b class="text-emerald-700">₹{{ number_format($stats['cash_collected'], 0) }}</b>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- SEARCH & FILTER TABS BAR (Matching Simple Dairy Reference)     -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-3 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Search Input -->
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="searchQuery" placeholder="Search name, phone or address..."
                       class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-medium">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <!-- Status Filter Tabs: [All] [Pending] [Delivered] [On Leave] -->
            <div class="flex items-center bg-slate-100/80 p-1 rounded-xl gap-1 shrink-0 overflow-x-auto">
                <button type="button" @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-white text-slate-900 shadow-xs font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3 py-1.5 text-xs rounded-lg transition whitespace-nowrap">
                    All <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-600'" x-text="totalCount"></span>
                </button>
                <button type="button" @click="activeTab = 'pending'"
                        :class="activeTab === 'pending' ? 'bg-white text-amber-900 shadow-xs font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3 py-1.5 text-xs rounded-lg transition whitespace-nowrap">
                    Pending <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'pending' ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-600'" x-text="pendingCount"></span>
                </button>
                <button type="button" @click="activeTab = 'delivered'"
                        :class="activeTab === 'delivered' ? 'bg-white text-emerald-900 shadow-xs font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3 py-1.5 text-xs rounded-lg transition whitespace-nowrap">
                    Delivered <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'delivered' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'" x-text="deliveredCount"></span>
                </button>
                <button type="button" @click="activeTab = 'on_leave'"
                        :class="activeTab === 'on_leave' ? 'bg-white text-rose-900 shadow-xs font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                        class="px-3 py-1.5 text-xs rounded-lg transition whitespace-nowrap">
                    On leave <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="activeTab === 'on_leave' ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-600'" x-text="onLeaveCount"></span>
                </button>
            </div>

            <!-- More Filters Button -->
            <button type="button" @click="showMoreFilters = !showMoreFilters" 
                    :class="showMoreFilters || activeRoute || activeGroup ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-white text-slate-700 border-slate-200'"
                    class="px-3 py-2 border rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 shadow-xs">
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                <span>More filters</span>
                <span x-show="activeRoute || activeGroup" class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </button>
        </div>

        <!-- Collapsible More Filters (Route, Group, Delivery Person) -->
        <div x-show="showMoreFilters" x-collapse class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Filter by Route</label>
                <select x-model="activeRoute" class="w-full text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white outline-none">
                    <option value="">All Routes (सभी मार्ग)</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" {{ $routeId == $r->id ? 'selected' : '' }}>{{ $r->name }} ({{ $r->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Filter by Group</label>
                <select x-model="activeGroup" class="w-full text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg bg-slate-50 focus:bg-white outline-none">
                    <option value="">All Groups (सभी समूह)</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" @click="activeRoute = ''; activeGroup = ''; searchQuery = '';" class="w-full py-1.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-lg transition">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- FLOATING MULTI-SELECT BULK ACTIONS BAR                         -->
    <!-- ============================================================== -->
    <div x-show="selectedCustomerIds.length > 0" x-transition
         class="sticky top-4 z-30 bg-slate-900 text-white rounded-2xl p-3 px-5 shadow-2xl flex items-center justify-between gap-4 border border-slate-800">
        <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-extrabold flex items-center justify-center" x-text="selectedCustomerIds.length"></span>
            <span class="text-xs font-bold">Customers Selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="selectedCustomerIds = []" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition">
                Deselect All
            </button>
            <button type="button" @click="bulkDeliverSelected()" :disabled="isBulkDelivering"
                    class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                <span x-text="isBulkDelivering ? 'Delivering...' : 'Mark as Delivered'"></span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- CUSTOMER ROSTER LIST / TABLE (DESKTOP & MOBILE RESPONSIVE)     -->
    <!-- ============================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- Table Header -->
        <div class="p-3.5 px-5 bg-slate-50/70 border-b border-slate-200/80 flex items-center justify-between text-xs font-bold text-slate-500">
            <div class="flex items-center gap-3">
                <input type="checkbox" @change="toggleSelectAll($event.target.checked)"
                       :checked="isAllSelected"
                       class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                <span class="uppercase tracking-wider font-extrabold text-[11px] text-slate-700">
                    <span x-text="filteredCustomers.length"></span> CUSTOMERS
                </span>
            </div>
            <div class="hidden sm:flex items-center gap-8 pr-12 text-[10px] uppercase tracking-wider text-slate-400">
                <span>Products & Quantity</span>
                <span>Deliver Action</span>
            </div>
        </div>

        <!-- Customer Rows -->
        <div class="divide-y divide-slate-100">
            <template x-for="c in filteredCustomers" :key="c.id">
                <div class="p-4 sm:p-5 hover:bg-slate-50/60 transition flex flex-col md:flex-row md:items-center justify-between gap-4"
                     :class="{'bg-emerald-50/20': c.status === 'delivered', 'bg-rose-50/20': c.status === 'on_leave' || c.status === 'skipped'}">
                    
                    <!-- Left: Checkbox + Avatar + Customer Name & Meta -->
                    <div class="flex items-start gap-3.5 min-w-[280px] max-w-sm">
                        <input type="checkbox" :value="c.id" x-model="selectedCustomerIds"
                               class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 mt-1">

                        <!-- Initials Circle -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-xs shrink-0 select-none shadow-xs"
                             :class="c.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : (c.status === 'on_leave' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700')"
                             x-text="getInitials(c.name)">
                        </div>

                        <!-- Name & Due Balance & Details -->
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-slate-900 hover:text-emerald-700 cursor-pointer" 
                                    @click="openCustomerDetails(c)" x-text="c.name"></h4>
                                
                                <!-- Status Badge -->
                                <template x-if="c.status === 'delivered'">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Delivered
                                    </span>
                                </template>
                                <template x-if="c.status === 'on_leave'">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                        On Leave
                                    </span>
                                </template>
                                <template x-if="c.status === 'skipped'">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full bg-slate-200 text-slate-700">
                                        Skipped
                                    </span>
                                </template>
                                <template x-if="c.status === 'pending'">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                        Pending
                                    </span>
                                </template>
                            </div>

                            <!-- Phone + Locality / Route -->
                            <div class="flex items-center flex-wrap gap-2 text-xs text-slate-500">
                                <span class="font-mono text-[11px] text-slate-400" x-text="'#' + c.code"></span>
                                <span class="font-medium" x-text="c.phone"></span>
                                <template x-if="c.locality || c.route_name">
                                    <span class="text-slate-300">•</span>
                                </template>
                                <span class="text-slate-600 font-medium truncate max-w-[150px]" x-text="c.locality || c.route_name"></span>
                            </div>

                            <!-- Due Amount Badge -->
                            <div class="pt-0.5 flex items-center gap-2">
                                <template x-if="c.current_balance > 0">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">
                                        <span>₹</span><span x-text="formatNumber(c.current_balance)"></span>
                                        <span class="uppercase text-[9px] text-rose-500">due</span>
                                    </span>
                                </template>
                                <template x-if="c.current_balance <= 0">
                                    <span class="text-[11px] font-semibold text-emerald-600">
                                        ₹0.00 dues (क्लियर)
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Middle: Products & Quantities with Pencil Edit (User's Key Feature) -->
                    <div class="flex-1 md:px-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <template x-for="item in c.items" :key="item.id">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-slate-200/90 rounded-xl hover:bg-slate-100/70 transition shadow-xs">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                    <span class="text-xs font-bold text-slate-800" x-text="item.product_name"></span>
                                    
                                    <!-- Quantity with Pencil Edit Button -->
                                    <button type="button" @click="openEditProductModal(c, item)"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-xs font-black text-slate-900 hover:border-emerald-500 hover:text-emerald-700 transition"
                                            title="Click to edit quantity or change product">
                                        <span x-text="item.quantity + ' ' + (item.product_unit || 'Ltr')"></span>
                                        <i data-lucide="pencil" class="w-3 h-3 text-emerald-600"></i>
                                    </button>
                                </div>
                            </template>

                            <!-- Add More Products Button -->
                            <button type="button" @click="openAddProductModal(c)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-dashed border-emerald-400 hover:border-emerald-600 bg-emerald-50/50 hover:bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold transition">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Add More Products</span>
                            </button>
                        </div>

                        <!-- Vacation / Failure Note if any -->
                        <template x-if="c.failure_reason">
                            <p class="text-[11px] text-rose-700 bg-rose-50/80 px-2 py-1 rounded-lg mt-2 inline-flex items-center gap-1 font-semibold border border-rose-200/60">
                                <i data-lucide="info" class="w-3 h-3 text-rose-500"></i>
                                <span x-text="c.failure_reason"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Right: Quick Actions [Receive Cash] [Deliver] [...] -->
                    <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                        
                        <!-- Receive Cash Button -->
                        <button type="button" @click="openCashModal(c)"
                                class="px-3 py-2 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                            <i data-lucide="banknote" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span class="hidden sm:inline">Receive Cash</span>
                        </button>

                        <!-- Deliver Button / Delivered Status Pill -->
                        <template x-if="c.status === 'delivered'">
                            <div class="flex items-center gap-1">
                                <span class="px-3.5 py-2 bg-emerald-100/80 text-emerald-800 rounded-xl text-xs font-extrabold flex items-center gap-1.5 border border-emerald-200/60">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Delivered</span>
                                    <span class="text-[10px] text-emerald-600 font-normal" x-text="c.delivered_at || ''"></span>
                                </span>
                                <button type="button" @click="undoDelivery(c)" title="Undo Delivery"
                                        class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </template>

                        <template x-if="c.status !== 'delivered'">
                            <button type="button" @click="markCustomerDelivered(c)"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white rounded-xl text-xs font-black shadow-xs transition flex items-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>TAP TO DELIVER ✓</span>
                            </button>
                        </template>

                        <!-- Three dots / dropdown options -->
                        <div class="relative" x-data="{ openMenu: false }">
                            <button type="button" @click="openMenu = !openMenu" 
                                    class="w-8 h-8 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                            </button>
                            <div x-show="openMenu" @click.away="openMenu = false" x-cloak
                                 class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-xl border border-slate-100 p-1.5 z-20 text-xs font-semibold">
                                <button type="button" @click="openSkipModal(c); openMenu = false;" class="w-full text-left px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                    <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                    <span>Skip Stop</span>
                                </button>
                                <template x-if="c.phone">
                                    <a :href="'tel:' + c.phone" class="w-full text-left px-3 py-1.5 rounded-lg text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                        <span>Call Customer</span>
                                    </a>
                                </template>
                                <template x-if="c.phone">
                                    <a :href="'https://wa.me/91' + c.phone.replace(/[^0-9]/g, '') + '?text=Namaste ' + encodeURIComponent(c.name) + ', Gopal Dudh Dairy se aapka doodh deliver ho gaya hai.'" target="_blank"
                                       class="w-full text-left px-3 py-1.5 rounded-lg text-emerald-700 hover:bg-emerald-50 flex items-center gap-2">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>WhatsApp Note</span>
                                    </a>
                                </template>
                                <a :href="'/customers/' + c.id" class="w-full text-left px-3 py-1.5 rounded-lg text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    <span>View Profile / Ledger</span>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </template>

            <!-- Empty State -->
            <template x-if="filteredCustomers.length === 0">
                <div class="py-16 text-center text-slate-400">
                    <i data-lucide="truck" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-700">No delivery entries match this filter</p>
                    <p class="text-xs text-slate-400 mt-1">Try switching tabs or resetting your search.</p>
                </div>
            </template>
        </div>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: PICK SHIFT & DATE (Matches Simple Dairy Screenshot)   -->
    <!-- ============================================================== -->
    <div x-show="shiftDateModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="shiftDateModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Pick shift & date</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Choose which delivery shift you'd like to enter. You can also scope to a delivery person or group.</p>
                </div>
                <button type="button" @click="shiftDateModal = false" class="w-8 h-8 rounded-full text-slate-400 hover:text-slate-600 flex items-center justify-center hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('delivery.board') }}" class="space-y-4">
                <!-- Delivery Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Delivery date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ $date }}" required
                           class="w-full px-3.5 py-2 text-xs font-bold border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Entries are allowed from one year back up to next month.</p>
                </div>

                <!-- Shift: Segmented Buttons -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Shift <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="morning" {{ $shift === 'morning' ? 'checked' : '' }} class="sr-only peer">
                            <div class="p-2.5 rounded-xl border border-slate-200 text-center font-bold text-xs peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-800 transition flex items-center justify-center gap-1.5">
                                <i data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                                <span>Morning</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="evening" {{ $shift === 'evening' ? 'checked' : '' }} class="sr-only peer">
                            <div class="p-2.5 rounded-xl border border-slate-200 text-center font-bold text-xs peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-800 transition flex items-center justify-center gap-1.5">
                                <i data-lucide="moon" class="w-4 h-4 text-indigo-500"></i>
                                <span>Evening</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Toggle: Restrict to a group -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2" x-data="{ restrictGroup: {{ $groupId ? 'true' : 'false' }} }">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Restrict to a group</span>
                            <p class="text-[10px] text-slate-400">Only show customers belonging to the selected group.</p>
                        </div>
                        <input type="checkbox" x-model="restrictGroup" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    </label>
                    <div x-show="restrictGroup" x-cloak class="pt-2">
                        <select name="group_id" class="w-full text-xs font-bold px-3 py-1.5 border border-slate-200 rounded-xl bg-white outline-none">
                            <option value="">-- Select Customer Group --</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ $groupId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Toggle: Make entries for a delivery person -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2" x-data="{ restrictBoy: {{ $deliveryBoyId ? 'true' : 'false' }} }">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Make entries for a delivery person</span>
                            <p class="text-[10px] text-slate-400">Pick a specific delivery person to mark entries for.</p>
                        </div>
                        <input type="checkbox" x-model="restrictBoy" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    </label>
                    <div x-show="restrictBoy" x-cloak class="pt-2">
                        <select name="delivery_boy_id" class="w-full text-xs font-bold px-3 py-1.5 border border-slate-200 rounded-xl bg-white outline-none">
                            <option value="">-- Select Delivery Person --</option>
                            @foreach($deliveryBoys as $db)
                                <option value="{{ $db->id }}" {{ $deliveryBoyId == $db->id ? 'selected' : '' }}>{{ $db->name }} ({{ $db->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="shiftDateModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        Continue
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: PRODUCT CHANGE / EDIT QUANTITY (Key User Request)     -->
    <!-- ============================================================== -->
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
                <!-- Choose Product -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Product / उत्पाद चुनें</label>
                    <select x-model="editItem.product_id" @change="onProductSelectChange()"
                            class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <template x-for="p in productsList" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' (₹' + (p.subscription_price || p.price) + '/' + p.unit + ')'" :selected="p.id == editItem.product_id"></option>
                        </template>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity / मात्रा</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="adjustEditQty(-0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">-</button>
                        <input type="number" step="0.1" min="0.1" x-model.number="editItem.quantity" @input="recalcEditTotal()"
                               class="w-full text-center text-sm font-black px-3 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
                        <button type="button" @click="adjustEditQty(0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">+</button>
                    </div>
                </div>

                <!-- Unit Price & Total Preview -->
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

            <!-- Modal Footer Buttons -->
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

    <!-- ============================================================== -->
    <!-- MODAL 3: ADD MORE PRODUCTS (Key User Request)                  -->
    <!-- ============================================================== -->
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
                <!-- Choose Product -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Product / उत्पाद चुनें</label>
                    <select x-model="newItem.product_id" @change="onNewProductSelectChange()"
                            class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <template x-for="p in productsList" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' (₹' + (p.subscription_price || p.price) + '/' + p.unit + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity / मात्रा</label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="adjustNewQty(-0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">-</button>
                        <input type="number" step="0.1" min="0.1" x-model.number="newItem.quantity" @input="recalcNewTotal()"
                               class="w-full text-center text-sm font-black px-3 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
                        <button type="button" @click="adjustNewQty(0.5)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 flex items-center justify-center">+</button>
                    </div>
                </div>

                <!-- Unit Price & Total Preview -->
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

            <!-- Modal Footer Buttons -->
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

    <!-- ============================================================== -->
    <!-- MODAL 4: RECEIVE CASH (Doorstep Payment)                       -->
    <!-- ============================================================== -->
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

            <!-- Due Balance Callout -->
            <div class="p-3 bg-rose-50 border border-rose-200/80 rounded-2xl flex items-center justify-between">
                <span class="text-xs font-bold text-rose-800">Current Due Balance:</span>
                <span class="text-base font-black text-rose-700">₹ <span x-text="formatNumber(cashData.current_balance)"></span></span>
            </div>

            <!-- Quick Presets -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quick Amount Presets</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <button type="button" @click="cashData.amount = cashData.current_balance" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">
                        Full Due
                    </button>
                    <button type="button" @click="cashData.amount = 500" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">
                        ₹500
                    </button>
                    <button type="button" @click="cashData.amount = 1000" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">
                        ₹1,000
                    </button>
                    <button type="button" @click="cashData.amount = 2000" class="py-1 px-2 text-xs font-extrabold bg-slate-100 hover:bg-emerald-100 text-slate-700 hover:text-emerald-800 rounded-lg transition">
                        ₹2,000
                    </button>
                </div>
            </div>

            <!-- Amount Input -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Amount Received (₹) <span class="text-rose-500">*</span></label>
                <input type="number" step="1" min="1" x-model.number="cashData.amount" placeholder="Enter amount"
                       class="w-full text-base font-black px-3.5 py-2 border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Mode</label>
                <select x-model="cashData.payment_method" class="w-full text-xs font-bold px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 outline-none">
                    <option value="cash">Cash (नकद)</option>
                    <option value="upi">UPI / PhonePe / GPay</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Receipt Note (Optional)</label>
                <input type="text" x-model="cashData.notes" placeholder="e.g. Received by driver"
                       class="w-full text-xs px-3 py-1.5 border border-slate-200 rounded-xl outline-none">
            </div>

            <!-- Submit -->
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" @click="cashModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </button>
                <button type="button" @click="submitCashPayment()" :disabled="isSubmittingCash || !cashData.amount"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition">
                    <span x-text="isSubmittingCash ? 'Recording...' : 'Record Payment'"></span>
                </button>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 5: SKIP STOP (With Reason Selection)                     -->
    <!-- ============================================================== -->
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
                <button type="button" @click="skipModal = false" class="px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </button>
                <button type="button" @click="submitSkipStop()" :disabled="isSubmittingSkip"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold rounded-xl shadow-xs transition">
                    <span x-text="isSubmittingSkip ? 'Skipping...' : 'Confirm Skip'"></span>
                </button>
            </div>

        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- ALPINE.JS COMPONENT (LIVE CLIENT LOGIC)                        -->
<!-- ============================================================== -->
<script>
function deliveryManager() {
    return {
        customers: @json($customersData),
        productsList: @json($products),
        currentDate: '{{ $date }}',
        currentShift: '{{ $shift }}',

        searchQuery: '',
        activeTab: 'all',
        activeRoute: '{{ $routeId }}',
        activeGroup: '{{ $groupId }}',
        showMoreFilters: false,
        shiftDateModal: false,

        selectedCustomerIds: [],
        isBulkDelivering: false,
        isRefreshing: false,

        // Edit Product Modal State
        editProductModal: false,
        isSavingProduct: false,
        editItem: {
            id: null,
            customer_id: null,
            customer_name: '',
            product_id: null,
            quantity: 1,
            unit_price: 0,
            total_amount: 0,
        },

        // Add Product Modal State
        addProductModal: false,
        isAddingProduct: false,
        newItem: {
            customer_id: null,
            customer_name: '',
            product_id: null,
            quantity: 1,
            unit_price: 0,
            total_amount: 0,
        },

        // Cash Modal State
        cashModal: false,
        isSubmittingCash: false,
        cashData: {
            customer_id: null,
            customer_name: '',
            current_balance: 0,
            amount: 0,
            payment_method: 'cash',
            notes: '',
        },

        // Skip Modal State
        skipModal: false,
        isSubmittingSkip: false,
        skipData: {
            customer_id: null,
            customer_name: '',
            reason: 'Customer Door Locked / Unavailable',
        },

        init() {
            if (window.lucide) window.lucide.createIcons();
        },

        get filteredCustomers() {
            let list = this.customers;

            // Route filter
            if (this.activeRoute) {
                list = list.filter(c => String(c.route_id) === String(this.activeRoute));
            }

            // Tab filter
            if (this.activeTab !== 'all') {
                list = list.filter(c => c.status === this.activeTab);
            }

            // Search query
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(c => 
                    (c.name && c.name.toLowerCase().includes(q)) ||
                    (c.phone && c.phone.includes(q)) ||
                    (c.code && c.code.toLowerCase().includes(q)) ||
                    (c.address && c.address.toLowerCase().includes(q)) ||
                    (c.locality && c.locality.toLowerCase().includes(q)) ||
                    (c.group_names && c.group_names.toLowerCase().includes(q))
                );
            }

            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });

            return list;
        },

        get totalCount() {
            return this.customers.length;
        },

        get deliveredCount() {
            return this.customers.filter(c => c.status === 'delivered').length;
        },

        get pendingCount() {
            return this.customers.filter(c => c.status === 'pending').length;
        },

        get onLeaveCount() {
            return this.customers.filter(c => c.status === 'on_leave' || c.status === 'skipped').length;
        },

        get deliveredPercent() {
            return this.totalCount > 0 ? (this.deliveredCount / this.totalCount) * 100 : 0;
        },

        get pendingPercent() {
            return this.totalCount > 0 ? (this.pendingCount / this.totalCount) * 100 : 0;
        },

        get leavePercent() {
            return this.totalCount > 0 ? (this.onLeaveCount / this.totalCount) * 100 : 0;
        },

        get isAllSelected() {
            return this.filteredCustomers.length > 0 && this.selectedCustomerIds.length === this.filteredCustomers.length;
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedCustomerIds = this.filteredCustomers.map(c => c.id);
            } else {
                this.selectedCustomerIds = [];
            }
        },

        getInitials(name) {
            if (!name) return 'CU';
            const parts = name.trim().split(' ');
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        },

        formatNumber(val) {
            return Number(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        navigateDate(offset) {
            const cur = new Date(this.currentDate);
            cur.setDate(cur.getDate() + offset);
            const y = cur.getFullYear();
            const m = String(cur.getMonth() + 1).padStart(2, '0');
            const d = String(cur.getDate()).padStart(2, '0');
            window.location.href = `{{ route('delivery.board') }}?date=${y}-${m}-${d}&shift=${this.currentShift}`;
        },

        async refreshData() {
            this.isRefreshing = true;
            try {
                const res = await fetch(`{{ route('delivery.board') }}?date=${this.currentDate}&shift=${this.currentShift}&ajax=1`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.customers = data.customers;
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isRefreshing = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        /* ------------------------------------------------------------- */
        /* PRODUCT CHANGE / EDIT QUANTITY LOGIC                          */
        /* ------------------------------------------------------------- */
        openEditProductModal(customer, item) {
            this.editItem = {
                id: item.id,
                customer_id: customer.id,
                customer_name: customer.name,
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                total_amount: item.total_amount,
            };
            this.editProductModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        onProductSelectChange() {
            const found = this.productsList.find(p => String(p.id) === String(this.editItem.product_id));
            if (found) {
                this.editItem.unit_price = Number(found.subscription_price || found.price || 0);
                this.recalcEditTotal();
            }
        },

        adjustEditQty(delta) {
            const next = Math.max(0.1, Number((this.editItem.quantity + delta).toFixed(2)));
            this.editItem.quantity = next;
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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: this.editItem.product_id,
                        quantity: this.editItem.quantity,
                        unit_price: this.editItem.unit_price,
                    })
                });

                const data = await res.json();
                if (data.success) {
                    // Update in local state
                    const c = this.customers.find(x => x.id === this.editItem.customer_id);
                    if (c) {
                        const itemIdx = c.items.findIndex(i => i.id === this.editItem.id);
                        if (itemIdx >= 0) {
                            c.items[itemIdx] = data.item;
                        }
                    }
                    this.editProductModal = false;
                } else {
                    alert(data.message || 'Error updating product');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while updating product');
            } finally {
                this.isSavingProduct = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async deleteDeliveryItem(itemId) {
            if (!confirm('Remove this product item from delivery?')) return;
            try {
                const res = await fetch(`/delivery/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.editItem.customer_id);
                    if (c) {
                        c.items = c.items.filter(i => i.id !== itemId);
                    }
                    this.editProductModal = false;
                }
            } catch (err) {
                console.error(err);
            }
        },

        /* ------------------------------------------------------------- */
        /* ADD MORE PRODUCTS LOGIC                                       */
        /* ------------------------------------------------------------- */
        openAddProductModal(customer) {
            const firstProduct = this.productsList[0] || {};
            this.newItem = {
                customer_id: customer.id,
                customer_name: customer.name,
                product_id: firstProduct.id || null,
                quantity: 1,
                unit_price: Number(firstProduct.subscription_price || firstProduct.price || 0),
                total_amount: Number(firstProduct.subscription_price || firstProduct.price || 0),
            };
            this.addProductModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        onNewProductSelectChange() {
            const found = this.productsList.find(p => String(p.id) === String(this.newItem.product_id));
            if (found) {
                this.newItem.unit_price = Number(found.subscription_price || found.price || 0);
                this.recalcNewTotal();
            }
        },

        adjustNewQty(delta) {
            const next = Math.max(0.1, Number((this.newItem.quantity + delta).toFixed(2)));
            this.newItem.quantity = next;
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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_id: this.newItem.customer_id,
                        delivery_date: this.currentDate,
                        shift: this.currentShift,
                        product_id: this.newItem.product_id,
                        quantity: this.newItem.quantity,
                        unit_price: this.newItem.unit_price,
                    })
                });

                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.newItem.customer_id);
                    if (c) {
                        c.items.push(data.item);
                    }
                    this.addProductModal = false;
                } else {
                    alert(data.message || 'Error adding product');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while adding product');
            } finally {
                this.isAddingProduct = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        /* ------------------------------------------------------------- */
        /* TAP TO DELIVER & UNDO LOGIC                                   */
        /* ------------------------------------------------------------- */
        async markCustomerDelivered(customer) {
            try {
                const res = await fetch(`/delivery/deliver-customer/${customer.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date: this.currentDate,
                        shift: this.currentShift
                    })
                });

                const data = await res.json();
                if (data.success) {
                    customer.status = 'delivered';
                    customer.delivered_at = data.delivered_at || 'Just now';
                    customer.items.forEach(i => i.status = 'delivered');
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async undoDelivery(customer) {
            try {
                const res = await fetch(`/delivery/undo-deliver/${customer.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date: this.currentDate,
                        shift: this.currentShift
                    })
                });

                const data = await res.json();
                if (data.success) {
                    customer.status = 'pending';
                    customer.delivered_at = null;
                    customer.items.forEach(i => i.status = 'pending');
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async bulkDeliverSelected() {
            if (this.selectedCustomerIds.length === 0) return;
            this.isBulkDelivering = true;
            try {
                const res = await fetch(`{{ route('delivery.bulk-deliver') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_ids: this.selectedCustomerIds,
                        date: this.currentDate,
                        shift: this.currentShift
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.customers.forEach(c => {
                        if (this.selectedCustomerIds.includes(c.id)) {
                            c.status = 'delivered';
                            c.delivered_at = data.delivered_at || 'Just now';
                            c.items.forEach(i => i.status = 'delivered');
                        }
                    });
                    this.selectedCustomerIds = [];
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isBulkDelivering = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        /* ------------------------------------------------------------- */
        /* RECEIVE CASH LOGIC                                            */
        /* ------------------------------------------------------------- */
        openCashModal(customer) {
            this.cashData = {
                customer_id: customer.id,
                customer_name: customer.name,
                current_balance: customer.current_balance,
                amount: customer.current_balance > 0 ? customer.current_balance : '',
                payment_method: 'cash',
                notes: '',
            };
            this.cashModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async submitCashPayment() {
            this.isSubmittingCash = true;
            try {
                const res = await fetch(`{{ route('delivery.receive-cash') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer_id: this.cashData.customer_id,
                        amount: this.cashData.amount,
                        payment_method: this.cashData.payment_method,
                        notes: this.cashData.notes,
                        date: this.currentDate,
                    })
                });

                const data = await res.json();
                if (data.success) {
                    const c = this.customers.find(x => x.id === this.cashData.customer_id);
                    if (c) {
                        c.current_balance = data.new_balance;
                    }
                    this.cashModal = false;
                } else {
                    alert(data.message || 'Error recording payment');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error');
            } finally {
                this.isSubmittingCash = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        /* ------------------------------------------------------------- */
        /* SKIP STOP LOGIC                                               */
        /* ------------------------------------------------------------- */
        openSkipModal(customer) {
            this.skipData = {
                customer_id: customer.id,
                customer_name: customer.name,
                reason: 'Customer Door Locked / Unavailable',
            };
            this.skipModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async submitSkipStop() {
            this.isSubmittingSkip = true;
            try {
                const res = await fetch(`/delivery/skip-customer/${this.skipData.customer_id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date: this.currentDate,
                        shift: this.currentShift,
                        reason: this.skipData.reason
                    })
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
            } catch (err) {
                console.error(err);
            } finally {
                this.isSubmittingSkip = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        openCustomerDetails(customer) {
            window.location.href = `/customers/${customer.id}`;
        }
    };
}
</script>
@endsection
