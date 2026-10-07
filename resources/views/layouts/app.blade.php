<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DairyMaster') | {{ \App\Models\SystemSetting::get('dairy_name', 'Simple Dairy') }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["'Plus Jakarta Sans'", "sans-serif"],
                    },
                    fontWeight: {
                        extrabold: '700',
                        black: '700',
                    },
                    colors: {
                        brand: {
                            50:  '#eef4ff',
                            100: '#dbe8ff',
                            200: '#b8d0ff',
                            500: '#002e79',
                            600: '#002765',
                            700: '#001f52',
                            800: '#00183f',
                        },
                        emerald: {
                            50:  '#eef4ff',
                            100: '#dbe8ff',
                            200: '#b8d0ff',
                            300: '#8ab4f8',
                            400: '#4285f4',
                            500: '#002e79',
                            600: '#002765',
                            700: '#001f52',
                            800: '#00183f',
                            900: '#00112c',
                            950: '#000b1e',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- jQuery & Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Alpine.js & Lucide Icons -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .font-black, .font-extrabold {
            font-weight: 700 !important;
        }
        [x-cloak] { display: none !important; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 4px; }

        /* Select2 Custom Theme matching Plus Jakarta Sans and Tailwind */
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            height: 42px !important;
            padding: 6px 12px !important;
            font-size: 12px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            background-color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            line-height: 28px !important;
            padding-left: 0 !important;
            font-weight: 500 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 10px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #059669 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
            outline: none !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 12px !important;
            background-color: #ffffff !important;
            z-index: 999999 !important;
        }
        .select2-container--open {
            z-index: 999999 !important;
        }
        .select2-search--dropdown {
            padding: 8px !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            font-family: inherit !important;
            outline: none !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #059669 !important;
        }
        .select2-results__option {
            padding: 8px 12px !important;
            font-size: 12px !important;
            color: #334155 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            font-weight: 600 !important;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex" style="font-family: 'Plus Jakarta Sans', sans-serif;" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    <!-- Sidebar -->
    <aside 
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 lg:translate-x-0"
        :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        <!-- Brand / User Header matching screenshot -->
        <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                <img src="{{ asset('logo.PNG') }}" alt="Gopal Dairy" class="h-9 w-auto object-contain max-w-[130px]">
                <div class="truncate">
                    <span class="text-[10px] text-[#002e79] font-black block uppercase tracking-wider truncate">
                        {{ auth()->user()->role ? ucfirst(str_replace('_', ' ', auth()->user()->role)) : 'Owner' }}
                    </span>
                </div>
            </a>
            <a href="{{ route('home') }}" target="_blank" title="View Public Website" class="text-slate-400 hover:text-[#002e79] p-1">
                <i data-lucide="external-link" class="w-4 h-4"></i>
            </a>
            <button @click="mobileMenuOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Search menu box -->
        <div class="px-3 pt-3">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input 
                    type="text" 
                    id="sidebarSearch"
                    placeholder="Search menu..." 
                    class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-600 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white transition"
                    onkeyup="filterSidebar(this.value)"
                >
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-3 overflow-y-auto sidebar-scroll space-y-4 text-xs font-medium" id="sidebarNav">
            
            <!-- OVERVIEW -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Overview</p>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- MILK PROCUREMENT -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Procurement & Farmers</p>
                <div class="space-y-0.5">
                    <a href="{{ route('collections.index', ['open_create' => 1]) }}" 
                       @if(request()->routeIs('collections.index')) @click.prevent="window.dispatchEvent(new CustomEvent('open-collection-modal'))" @endif
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ (request('open_create') || request()->routeIs('collections.create')) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>New Milk Collection</span>
                    </a>
                    <a href="{{ route('collections.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('collections.index*') && !request()->routeIs('collections.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="milk" class="w-4 h-4 text-slate-400"></i>
                        <span>Collection History</span>
                    </a>
                    <a href="{{ route('farmers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('farmers.*') && !request()->routeIs('farmers.settlements*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                        <span>Farmers Master</span>
                    </a>
                    <a href="{{ route('rates.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('rates.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="calculator" class="w-4 h-4 text-slate-400"></i>
                        <span>Rate Charts (Fat/SNF)</span>
                    </a>
                    <a href="{{ route('settlements.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('settlements.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="receipt" class="w-4 h-4 text-slate-400"></i>
                        <span>Farmer Settlements</span>
                    </a>
                </div>
            </div>

            <!-- PRODUCTS & FINANCE -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Products & Finance</p>
                <div class="space-y-0.5">
                    <!-- Product Dropdown -->
                    <div x-data="{ open: {{ (request()->routeIs('products.*') || request()->routeIs('purchases.*') || request()->routeIs('product-sales.*') || request()->routeIs('inventory.*') || request()->routeIs('pos.history')) ? 'true' : 'false' }} }" class="space-y-0.5">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition {{ (request()->routeIs('products.*') || request()->routeIs('purchases.*') || request()->routeIs('product-sales.*') || request()->routeIs('inventory.*')) ? 'bg-emerald-50/70 text-emerald-800 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="boxes" class="w-4 h-4 {{ (request()->routeIs('products.*') || request()->routeIs('purchases.*') || request()->routeIs('product-sales.*') || request()->routeIs('inventory.*')) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Products</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-cloak class="pl-7 pr-1 py-1 space-y-0.5 border-l-2 border-emerald-100 ml-3">
                            <!-- All Products / List -->
                            <a href="{{ route('products.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('products.index') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>All Products</span>
                                <span class="text-[10px] px-1.5 py-0.2 bg-slate-100 rounded text-slate-500 font-normal">Catalog</span>
                            </a>

                            <!-- Add New Product -->
                            <a href="{{ route('products.index', ['open_create' => 1]) }}" 
                               @if(request()->routeIs('products.index')) @click.prevent="window.dispatchEvent(new CustomEvent('open-add-product'))" @endif
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ (request()->routeIs('products.create') || request('open_create')) ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>+ Add Product</span>
                            </a>

                            <!-- Categories Master -->
                            <a href="{{ route('products.categories.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('products.categories.*') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Categories</span>
                                <span class="text-[10px] px-1.5 py-0.2 bg-slate-100 rounded text-slate-500 font-normal">Master</span>
                            </a>

                            <!-- Buy Product (Purchase / Stock Inward) -->
                            <a href="{{ route('purchases.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('purchases.*') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Buy Product</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-emerald-50 text-emerald-600 font-semibold rounded">Purchase</span>
                            </a>

                            <!-- Sales Product (Sales to Farmers & Outward) -->
                            <a href="{{ route('product-sales.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('product-sales.*') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Sales Product</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-sky-50 text-sky-600 font-semibold rounded">Outward</span>
                            </a>

                            <!-- Product Stock (Full Inventory Ledger) -->
                            <a href="{{ route('inventory.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ (request()->routeIs('inventory.index') && !request()->filled('type')) ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Product Stock</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-amber-50 text-amber-700 font-semibold rounded">Ledger</span>
                            </a>

                            <!-- Bottle Management -->
                            <a href="{{ route('inventory.bottles') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('inventory.bottles') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Bottle Tracking</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('expenses.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="credit-card" class="w-4 h-4 text-slate-400"></i>
                        <span>Expenses</span>
                    </a>
                    <a href="{{ route('expenses.profit-loss') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('expenses.profit-loss') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="trending-up" class="w-4 h-4 text-slate-400"></i>
                        <span>Profit & Loss</span>
                    </a>
                </div>
            </div>

            <!-- SALES & CUSTOMERS -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Sales & Customers</p>
                <div class="space-y-0.5">
                    <a href="{{ route('pos.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('pos.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="store" class="w-4 h-4 text-emerald-600"></i>
                        <span>POS Counter Billing</span>
                    </a>
                    <a href="{{ route('pos.cashbook') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('pos.cashbook') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="wallet" class="w-4 h-4 text-slate-400"></i>
                        <span>Counter Cashbook</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('customers.index') || request()->routeIs('customers.show') || request()->routeIs('customers.edit') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="user-check" class="w-4 h-4 text-slate-400"></i>
                        <span>Customers List</span>
                    </a>
                    <!-- Customer Groups -->
                    <a href="{{ route('customers.groups.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('customers.groups.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="users-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Customer Groups</span>
                    </a>
                    <!-- Product Pre-order Booking -->
                    <a href="{{ route('bookings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('bookings.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-slate-400"></i>
                        <span>Product Booking</span>
                    </a>
                    <a href="{{ route('subscriptions.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('subscriptions.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        <span>Milk Subscriptions</span>
                    </a>
                    <a href="{{ route('invoices.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('invoices.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                        <span>Invoices</span>
                    </a>
                    <a href="{{ route('payments.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('payments.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="banknote" class="w-4 h-4 text-slate-400"></i>
                        <span>Payments Received</span>
                    </a>
                    <!-- SMS Broadcast -->
                    <a href="{{ route('sms.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('sms.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="message-square" class="w-4 h-4 text-emerald-600"></i>
                        <span>Send SMS Broadcast</span>
                    </a>
                </div>
            </div>

            <!-- DELIVERY OPERATIONS -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Delivery & Dispatch</p>
                <div class="space-y-0.5">
                    <!-- Milk Dispatched -->
                    <a href="{{ route('dispatch.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('dispatch.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="truck" class="w-4 h-4 text-emerald-600"></i>
                        <span>Milk Dispatched</span>
                    </a>
                    <a href="{{ route('delivery.board') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('delivery.board') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-400"></i>
                        <span>Daily Delivery Board</span>
                    </a>
                    <a href="{{ route('delivery.boy-app') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('delivery.boy-app') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="bike" class="w-4 h-4 text-emerald-600"></i>
                        <span>Delivery Boy App</span>
                    </a>
                    <a href="{{ route('delivery.routes') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('delivery.routes') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                        <span>Delivery Routes</span>
                    </a>
                </div>
            </div>

            <!-- STAFF MANAGEMENT -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Staff & HR Management</p>
                <div class="space-y-0.5">
                    <a href="{{ route('staff.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('staff.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                        <span>Staff Members</span>
                    </a>
                    <a href="{{ route('staff.attendance') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('staff.attendance') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="user-check" class="w-4 h-4 text-slate-400"></i>
                        <span>Staff Attendance</span>
                    </a>
                    <a href="{{ route('staff.advances') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('staff.advances') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="badge-indian-rupee" class="w-4 h-4 text-amber-500"></i>
                        <span>Staff Advances</span>
                    </a>
                    <a href="{{ route('staff.salaries') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('staff.salaries') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="wallet-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Staff Salary List</span>
                    </a>
                </div>
            </div>

            <!-- REPORTS & SYSTEM -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Reports & Super Admin</p>
                <div class="space-y-0.5">
                    <!-- Super Admin Website Banners & CMS -->
                    <a href="{{ route('admin.website.banners') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.website.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="layout-template" class="w-4 h-4 text-emerald-600"></i>
                        <span>Website CMS & Banners</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400"></i>
                        <span>Reports & Analytics</span>
                    </a>
                    <a href="{{ route('support.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('support.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="life-buoy" class="w-4 h-4 text-slate-400"></i>
                        <span>Support Tickets</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('users.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="shield-check" class="w-4 h-4 text-slate-400"></i>
                        <span>Users & Role Matrix</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                        <span>Dairy Settings</span>
                    </a>
                    <a href="{{ route('audit.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('audit.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="activity" class="w-4 h-4 text-slate-400"></i>
                        <span>Audit Trail</span>
                    </a>
                </div>
            </div>

        </nav>

        <!-- Current User Footer & Logout -->
        <div class="p-3 border-t border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 truncate">
                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign out" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-md hover:bg-rose-50 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-64 flex flex-col min-w-0 min-h-screen">
        
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="mobileMenuOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700 p-1">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div>
                    <nav class="flex text-[11px] text-slate-400 font-medium space-x-1.5">
                        <a href="{{ route('dashboard') }}" class="hover:text-slate-600">Dashboard</a>
                        <span>/</span>
                        <span class="text-slate-700 font-medium">@yield('breadcrumb', 'Overview')</span>
                    </nav>
                    <h2 class="text-lg font-bold text-slate-900 leading-tight">@yield('header_title', 'Dairy Platform')</h2>
                </div>
            </div>

            <!-- Topbar right actions matching screenshot -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                
                <!-- Quick Role Switcher (Convenient demo testing for all 13 roles) -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 border border-slate-200 transition">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span class="hidden sm:inline">Role Switch:</span>
                        <span class="font-bold text-emerald-700 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50 text-xs">
                        <p class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400">Quick Test Any Role</p>
                        @php
                            $switcherUsers = \App\Models\User::all();
                        @endphp
                        @foreach($switcherUsers as $u)
                            <a href="{{ route('login.demo', $u->id) }}" class="flex items-center justify-between px-3 py-2 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800">
                                <div>
                                    <p class="font-semibold">{{ $u->name }}</p>
                                    <span class="text-[10px] text-slate-400">{{ ucfirst(str_replace('_', ' ', $u->role)) }}</span>
                                </div>
                                @if(auth()->id() === $u->id)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Notifications icon with 99+ badge -->
                <a href="{{ route('settings.index') }}" title="Notifications" class="relative text-slate-400 hover:text-slate-600 p-1.5">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    <span class="absolute top-0 right-0 bg-rose-500 text-white text-[9px] font-bold px-1 rounded-full leading-tight">99+</span>
                </a>

                <!-- User profile badge pill matching screenshot -->
                <a href="{{ route('profile') }}" class="hidden sm:flex items-center space-x-2 pl-2 border-l border-slate-200">
                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[10px]">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-tight">{{ auth()->user()->name }}</span>
                </a>

                <!-- Header Action Slot (e.g. + Add Product green button) -->
                <div>
                    @yield('header_action')
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-900 text-xs px-4 py-3 rounded-xl shadow-sm">
                    <div class="flex items-center space-x-2 font-semibold mb-1">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        <span>Please fix the following:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Body -->
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        function filterSidebar(query) {
            query = query.toLowerCase();
            const groups = document.querySelectorAll('#sidebarNav .menu-group');
            groups.forEach(group => {
                const links = group.querySelectorAll('a');
                let hasMatch = false;
                links.forEach(link => {
                    const text = link.innerText.toLowerCase();
                    if (text.includes(query)) {
                        link.style.display = 'flex';
                        hasMatch = true;
                    } else {
                        link.style.display = 'none';
                    }
                });
                group.style.display = (hasMatch || query === '') ? 'block' : 'none';
            });
        }

        // Global Select2 Initializer with search + modal support
        window.initSelect2 = function(selector, options) {
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(selector || '.select2').each(function() {
                    var $this = $(this);
                    if ($this.hasClass('select2-hidden-accessible')) {
                        $this.select2('destroy');
                    }
                    var placeholderText = $this.attr('placeholder') || $this.data('placeholder') || ($this.find('option[value=""]').first().text().trim() || 'Select an option');
                    var hasEmptyOption = $this.find('option[value=""]').length > 0;
                    var config = Object.assign({
                        width: '100%',
                        placeholder: placeholderText,
                        allowClear: !$this.prop('required') && hasEmptyOption,
                    }, options || {});

                    if (modalParent.length) {
                        config.dropdownParent = modalParent;
                    }

                    $this.select2(config);
                });
            }
        };

        $(document).ready(function() {
            window.initSelect2('.select2');
        });
    </script>
    @stack('scripts')
</body>
</html>
