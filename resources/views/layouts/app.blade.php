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
        .select2-container:not(.select2-container--open) {
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
        #collection-entry-section .select2-selection--single {
            height: 32px !important;
            padding: 2px 8px !important;
            font-size: 12px !important;
            border-radius: 0.5rem !important;
        }
        #collection-entry-section .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 0 !important;
            font-size: 12px !important;
        }
        #collection-entry-section .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
            right: 6px !important;
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
        .select2-results {
            max-height: 250px !important;
            overflow-y: auto !important;
        }
        .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
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

        /* Smart Sidebar Collapse & Expand (Desktop >= 1024px) */
        @media (min-width: 1024px) {
            #app-sidebar {
                width: 16rem; /* 256px expanded */
                transition: width 200ms cubic-bezier(0.4, 0, 0.2, 1), transform 200ms cubic-bezier(0.4, 0, 0.2, 1), box-shadow 200ms ease;
            }
            #app-main-content {
                padding-left: 16rem; /* 256px expanded */
                transition: padding-left 200ms cubic-bezier(0.4, 0, 0.2, 1);
            }
            #app-sidebar nav#sidebarNav a,
            #app-sidebar nav#sidebarNav button {
                white-space: nowrap;
                overflow: hidden;
            }
            #app-sidebar nav#sidebarNav svg,
            #app-sidebar nav#sidebarNav i[data-lucide] {
                flex-shrink: 0;
            }

            /* Collapsed & Not Hovered State (72px = 4.5rem) */
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar {
                width: 4.5rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-main-content {
                padding-left: 4.5rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-brand-link,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-ext-link {
                display: none !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-header {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-search-container {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarSearch {
                padding-left: 0 !important;
                padding-right: 0 !important;
                color: transparent !important;
                cursor: pointer;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarSearch::placeholder {
                color: transparent !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-search-icon {
                left: 50% !important;
                transform: translateX(-50%) !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav {
                padding-left: 0.625rem !important;
                padding-right: 0.625rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .menu-group > p {
                display: none !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .menu-group + .menu-group {
                border-top: 1px solid #f1f5f9;
                padding-top: 0.5rem;
                margin-top: 0.5rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group a,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group button {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group a > span,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group button > div > span,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group button > svg,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group button > i[data-lucide="chevron-down"] {
                display: none !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar #sidebarNav .menu-group [x-data] > div[x-show] {
                display: none !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-user-details,
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-logout-form {
                display: none !important;
            }
            html.sidebar-collapsed:not(.sidebar-hovered) #app-sidebar .sidebar-user-footer > div {
                justify-content: center !important;
            }
        }

        /* Universal Table Column Sorting (ASC / DESC) */
        th.sd-sortable-th {
            cursor: pointer !important;
            user-select: none !important;
            transition: color 0.15s ease, background-color 0.15s ease;
        }
        th.sd-sortable-th:hover {
            color: #002e79 !important;
            background-color: rgba(0, 46, 121, 0.04) !important;
        }
        th.sd-sortable-th[data-sort-dir="asc"],
        th.sd-sortable-th[data-sort-dir="desc"] {
            color: #002e79 !important;
            background-color: rgba(0, 46, 121, 0.06) !important;
        }
        .sd-sort-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 4px;
            font-size: 10px;
            line-height: 1;
            opacity: 0.45;
            vertical-align: middle;
            transition: opacity 0.15s ease, transform 0.15s ease, color 0.15s ease;
        }
        th.sd-sortable-th:hover .sd-sort-icon {
            opacity: 0.85;
            color: #002e79;
        }
        th.sd-sortable-th[data-sort-dir="asc"] .sd-sort-icon,
        th.sd-sortable-th[data-sort-dir="desc"] .sd-sort-icon {
            opacity: 1;
            color: #002e79;
            font-weight: 700;
        }
    </style>

    <script>
        // Restore saved sidebar preference before body renders to prevent flickering
        (function() {
            try {
                if (localStorage.getItem('smartdairy_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();

        window.smartSidebarState = function() {
            let initialCollapsed = false;
            try {
                initialCollapsed = localStorage.getItem('smartdairy_sidebar_collapsed') === 'true';
            } catch (e) {}

            return {
                isSidebarCollapsed: initialCollapsed,
                isSidebarHovered: false,
                mobileMenuOpen: false,

                get isSidebarExpanded() {
                    return !this.isSidebarCollapsed || this.isSidebarHovered;
                },

                get sidebarOpen() {
                    return this.isSidebarExpanded;
                },

                initSidebar() {
                    this.syncHtmlClasses();
                    this.applyAccessibleLabels();
                },

                toggleSidebar() {
                    this.isSidebarCollapsed = !this.isSidebarCollapsed;
                    this.isSidebarHovered = false;
                    try {
                        localStorage.setItem('smartdairy_sidebar_collapsed', this.isSidebarCollapsed ? 'true' : 'false');
                    } catch (e) {}
                    this.syncHtmlClasses();
                    this.updateCollapsedTooltips();
                },

                handleSidebarMouseEnter() {
                    if (window.innerWidth >= 1024 && this.isSidebarCollapsed) {
                        this.isSidebarHovered = true;
                        this.syncHtmlClasses();
                        this.updateCollapsedTooltips();
                    }
                },

                handleSidebarMouseLeave() {
                    if (this.isSidebarHovered) {
                        this.isSidebarHovered = false;
                        this.syncHtmlClasses();
                        this.updateCollapsedTooltips();
                    }
                },

                syncHtmlClasses() {
                    const root = document.documentElement;
                    root.classList.toggle('sidebar-collapsed', this.isSidebarCollapsed);
                    root.classList.toggle('sidebar-hovered', this.isSidebarCollapsed && this.isSidebarHovered);
                },

                applyAccessibleLabels() {
                    const items = document.querySelectorAll('#sidebarNav a, #sidebarNav button');
                    items.forEach(el => {
                        const labelEl = el.querySelector('span');
                        const labelText = labelEl ? labelEl.textContent.trim() : el.textContent.trim();
                        if (labelText) {
                            el.setAttribute('aria-label', labelText);
                            el.dataset.menuLabel = labelText;
                        }
                    });
                    this.updateCollapsedTooltips();
                },

                updateCollapsedTooltips() {
                    const showNativeTooltip = this.isSidebarCollapsed && !this.isSidebarHovered;
                    const items = document.querySelectorAll('#sidebarNav a, #sidebarNav button');
                    items.forEach(el => {
                        const label = el.dataset.menuLabel;
                        if (!label) return;
                        if (showNativeTooltip) {
                            el.setAttribute('title', label);
                        } else {
                            el.removeAttribute('title');
                        }
                    });
                }
            };
        };
    </script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex overflow-x-hidden w-full max-w-full"
      style="font-family: 'Plus Jakarta Sans', sans-serif;"
      x-data="smartSidebarState()"
      x-init="initSidebar()">

    <!-- Sidebar -->
    <aside 
        id="app-sidebar"
        @mouseenter="handleSidebarMouseEnter()"
        @mouseleave="handleSidebarMouseLeave()"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transition-all duration-200 lg:translate-x-0"
        :class="[
            mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            isSidebarExpanded ? 'lg:w-64' : 'lg:w-[72px]',
            (isSidebarCollapsed && isSidebarHovered) ? 'lg:shadow-xl' : ''
        ]"
    >
        <!-- Brand / User Header matching screenshot -->
        <div class="sidebar-header p-3.5 border-b border-slate-100 flex items-center justify-between gap-1">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-link flex items-center space-x-2.5 min-w-0">
                <img src="{{ asset('logo.PNG') }}" alt="Gopal Dairy" class="h-9 w-auto object-contain max-w-[130px] shrink-0">
                <div class="truncate">
                    <span class="text-[10px] text-[#002e79] font-black block uppercase tracking-wider truncate">
                        {{ auth()->user()->role ? ucfirst(str_replace('_', ' ', auth()->user()->role)) : 'Owner' }}
                    </span>
                </div>
            </a>

            <div class="flex items-center gap-1 shrink-0">
                <a href="{{ route('home') }}" target="_blank" title="View Public Website" aria-label="View Public Website" class="sidebar-ext-link text-slate-400 hover:text-[#002e79] p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                </a>

                <!-- Desktop Collapse / Expand Toggle Button -->
                <button type="button"
                        @click.stop="toggleSidebar()"
                        :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        :aria-label="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        :aria-expanded="isSidebarExpanded ? 'true' : 'false'"
                        class="hidden lg:inline-flex items-center justify-center w-7 h-7 rounded-lg border transition cursor-pointer"
                        :class="isSidebarCollapsed ? 'bg-emerald-50 text-[#002e79] border-emerald-200 hover:bg-emerald-100' : 'text-slate-400 hover:text-[#002e79] border-transparent hover:border-slate-200 hover:bg-slate-100'">
                    <span x-show="!isSidebarCollapsed" class="inline-flex items-center justify-center">
                        <i data-lucide="panel-left-close" class="w-4 h-4"></i>
                    </span>
                    <span x-show="isSidebarCollapsed" x-cloak class="inline-flex items-center justify-center">
                        <i data-lucide="panel-left-open" class="w-4 h-4"></i>
                    </span>
                </button>

                <button type="button" @click="mobileMenuOpen = false" aria-label="Close mobile menu" class="lg:hidden text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Search menu box -->
        <div class="sidebar-search-container px-3 pt-3">
            <div class="relative">
                <i data-lucide="search" class="sidebar-search-icon w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none transition-all duration-150"></i>
                <input 
                    type="text" 
                    id="sidebarSearch"
                    placeholder="Search menu..." 
                    aria-label="Search menu"
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
                    <!-- <a href="{{ route('collections.index', ['open_create' => 1]) }}" 
                       @if(request()->routeIs('collections.index')) @click.prevent="window.dispatchEvent(new CustomEvent('open-collection-modal'))" @endif
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ (request('open_create') || request()->routeIs('collections.create')) ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>New Milk Collection</span>
                    </a> -->
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
                    <a href="{{ route('advances.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('advances.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="hand-coins" class="w-4 h-4 {{ request()->routeIs('advances.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Advance & Loans</span>
                    </a>
                    <a href="{{ route('deductions.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('deductions.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="minus-circle" class="w-4 h-4 {{ request()->routeIs('deductions.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Deductions</span>
                    </a>
                    <a href="{{ route('farmer-invoices.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('farmer-invoices.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="file-text" class="w-4 h-4 {{ request()->routeIs('farmer-invoices.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Farmer Invoices</span>
                    </a>
                </div>
            </div>

            <!-- MILK SALE & COMMERCIAL BUYERS (Matching Reference media_1791389566806.png) -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Milk Sales & Buyers</p>
                <div class="space-y-0.5">
                    <a href="{{ route('milk-sales.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('milk-sales.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="shopping-cart" class="w-4 h-4 {{ request()->routeIs('milk-sales.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Milk Sale</span>
                    </a>
                    <a href="{{ route('buyers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('buyers.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="users-round" class="w-4 h-4 {{ request()->routeIs('buyers.index') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Buyers (Customers)</span>
                    </a>
                    <a href="{{ route('buyers.khata') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('buyers.khata*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="book-open" class="w-4 h-4 {{ request()->routeIs('buyers.khata*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Buyer Khata</span>
                    </a>
                </div>
            </div>

            <!-- DRIVERS & FLEET VEHICLES (Matching Reference media_1791389924187.png & media_1791389941427.png) -->
            <div class="menu-group">
                <p class="px-2 pb-1 text-[10px] font-bold tracking-wider uppercase text-slate-400">Driver / Vehicles</p>
                <div class="space-y-0.5">
                    <a href="{{ route('drivers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('drivers.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="user-check" class="w-4 h-4 {{ request()->routeIs('drivers.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Add Driver</span>
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('vehicles.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="truck" class="w-4 h-4 {{ request()->routeIs('vehicles.index') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Add Vehicle</span>
                    </a>
                    <a href="{{ route('vehicles.advances.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('vehicles.advances.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="hand-coins" class="w-4 h-4 {{ request()->routeIs('vehicles.advances.*') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Vehicle Advance</span>
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

                    <!-- Product Dealer Dropdown (Dealers & Dealers Payment) -->
                    <div x-data="{ open: {{ (request()->routeIs('dealers.*') || request()->routeIs('dealer-payments.*')) ? 'true' : 'false' }} }" class="space-y-0.5">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition {{ (request()->routeIs('dealers.*') || request()->routeIs('dealer-payments.*')) ? 'bg-emerald-50/70 text-emerald-800 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="store" class="w-4 h-4 {{ (request()->routeIs('dealers.*') || request()->routeIs('dealer-payments.*')) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                <span>Product Dealer</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-cloak class="pl-7 pr-1 py-1 space-y-0.5 border-l-2 border-emerald-100 ml-3">
                            <!-- Dealers List -->
                            <a href="{{ route('dealers.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('dealers.*') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Dealers</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-emerald-50 text-emerald-600 font-semibold rounded">Master</span>
                            </a>

                            <!-- Dealers Payment -->
                            <a href="{{ route('dealer-payments.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('dealer-payments.*') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span>Dealers Payment</span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-indigo-50 text-indigo-600 font-semibold rounded">Dues</span>
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
                    <!-- Load / Unload (Counter Sale & Delivery Sale) -->
                    <div x-data="{ open: {{ request()->routeIs('load-unload.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                        <button type="button" @click="open = !open" 
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('load-unload.*') ? 'bg-blue-50 text-[#002e79] font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="package-plus" class="w-4 h-4 text-[#002e79]"></i>
                                <span>Load / Unload</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-[#002e79]' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-cloak class="pl-7 pr-1 py-1 space-y-0.5 border-l-2 border-blue-200 ml-3">
                            <a href="{{ route('load-unload.counter-sale') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ (request('type') === 'counter_sale' || request()->routeIs('load-unload.counter-sale')) ? 'text-[#002e79] font-bold bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-store text-[10px] text-blue-600"></i> Counter Sale
                                </span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-blue-100/70 text-[#002e79] font-bold rounded">Load</span>
                            </a>
                            <a href="{{ route('load-unload.delivery-sale') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ (request('type') === 'delivery_sale' || request()->routeIs('load-unload.delivery-sale')) ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck-ramp-box text-[10px] text-emerald-600"></i> Delivery Sale
                                </span>
                                <span class="text-[9px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold rounded">Stock</span>
                            </a>
                        </div>
                    </div>

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
                    <!-- Dairy Settings & Center Information -->
                    <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }" class="space-y-0.5">
                        <button type="button" @click="open = !open" 
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition {{ request()->routeIs('settings.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="settings" class="w-4 h-4 text-emerald-600"></i>
                                <span>Settings</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-700' : ''"></i>
                        </button>
                        
                        <div x-show="open" x-cloak class="pl-7 pr-1 py-1 space-y-0.5 border-l-2 border-emerald-200 ml-3">
                            <a href="{{ route('settings.center-information') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('settings.center-information') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[6px] text-emerald-500"></i> Center Information
                                </span>
                            </a>
                            <a href="{{ route('settings.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition text-[11px] {{ request()->routeIs('settings.index') ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[6px] text-slate-400"></i> General Settings
                                </span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('audit.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('audit.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <i data-lucide="activity" class="w-4 h-4 text-slate-400"></i>
                        <span>Audit Trail</span>
                    </a>
                </div>
            </div>

        </nav>

        <!-- Current User Footer & Logout -->
        <div class="sidebar-user-footer p-3 border-t border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 truncate">
                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0"
                         title="{{ auth()->user()->name }} ({{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }})">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="sidebar-user-details truncate">
                        <p class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="sidebar-logout-form shrink-0">
                    @csrf
                    <button type="submit" title="Sign out" aria-label="Sign out" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-md hover:bg-rose-50 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile Backdrop Overlay -->
    <div x-show="mobileMenuOpen" 
         x-cloak 
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-2xs lg:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <div id="app-main-content"
         class="flex-1 lg:pl-64 flex flex-col min-w-0 max-w-full min-h-screen overflow-x-hidden transition-[padding] duration-200"
         :class="isSidebarExpanded ? 'lg:pl-64' : 'lg:pl-[72px]'">
        
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-6 py-2.5 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button type="button" @click="mobileMenuOpen = true" aria-label="Open mobile menu" class="lg:hidden text-slate-500 hover:text-slate-700 p-1">
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
        <main class="flex-1 p-3 sm:p-5 lg:p-6 min-w-0 max-w-full overflow-x-hidden">
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

                    var modalParent = $this.closest('.fixed, .modal');
                    if (modalParent.length) {
                        config.dropdownParent = modalParent;
                    }

                    $this.select2(config);
                });
            }
        };

        $(document).ready(function() {
            window.initSelect2('.select2');
            if (typeof window.initSortableTables === 'function') {
                window.initSortableTables();
            }
        });

        // Universal Table Column Sorting (ASC / DESC) across all tables
        (function() {
            const NON_SORTABLE_HEADERS = new Set([
                '', 'action', 'actions', 'print', 'slip', 'photo', 'image', 'qr', 'options', 'manage', 'view', 'edit', 'delete'
            ]);

            function cleanCellText(td) {
                if (!td) return '';
                const explicit = td.getAttribute('data-sort-value');
                if (explicit !== null && explicit !== undefined) {
                    return explicit.trim();
                }

                const clone = td.cloneNode(true);
                clone.querySelectorAll('script, style, template, [x-cloak]').forEach(el => el.remove());

                const fullText = (clone.textContent || '').replace(/\s+/g, ' ').trim();
                // Strip 1-2 letter avatar initial circles only when accompanied by a name/label
                clone.querySelectorAll('.rounded-full').forEach(el => {
                    const txt = (el.textContent || '').trim();
                    if (/^[A-Za-z]{1,2}$/.test(txt) && fullText.length > txt.length) {
                        el.remove();
                    }
                });

                let raw = (clone.textContent || '').replace(/\s+/g, ' ').trim();
                // Strip leading emojis/symbols (e.g. ☀️, 🌙, 🐄, 🐃, 🥛, •)
                raw = raw.replace(/^[^\w\d₹\-\+\(\.]+\s*/u, '').trim();
                return raw;
            }

            function parseSortToken(val) {
                if (!val || val === '—' || val === '-' || val.toLowerCase() === 'n/a') {
                    return { type: 'empty', value: null };
                }

                // 1. Check DD/MM/YYYY or DD-MM-YYYY (with optional time)
                const dmyMatch = val.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?:\s+(\d{1,2}):(\d{2})(?:\s*(AM|PM))?)?/i);
                if (dmyMatch) {
                    const day = parseInt(dmyMatch[1], 10);
                    const month = parseInt(dmyMatch[2], 10) - 1;
                    const year = parseInt(dmyMatch[3], 10);
                    let hours = dmyMatch[4] ? parseInt(dmyMatch[4], 10) : 0;
                    const mins = dmyMatch[5] ? parseInt(dmyMatch[5], 10) : 0;
                    const ampm = (dmyMatch[6] || '').toUpperCase();
                    if (ampm === 'PM' && hours < 12) hours += 12;
                    if (ampm === 'AM' && hours === 12) hours = 0;
                    const ts = new Date(year, month, day, hours, mins).getTime();
                    if (!isNaN(ts)) {
                        return { type: 'number', value: ts };
                    }
                }

                // 2. Check YYYY-MM-DD
                if (/^\d{4}-\d{2}-\d{2}/.test(val)) {
                    const ts = Date.parse(val);
                    if (!isNaN(ts)) {
                        return { type: 'number', value: ts };
                    }
                }

                // 3. Check Date with month name e.g. "12 Oct 2026" or "12 Oct, 2026"
                if (/^\d{1,2}\s+[A-Za-z]{3},?\s+\d{4}/.test(val)) {
                    const ts = Date.parse(val);
                    if (!isNaN(ts)) {
                        return { type: 'number', value: ts };
                    }
                }

                // 4. Check numeric / currency / percentage / quantity (e.g., "₹ 2,746.80", "10.00 L", "4.20%", "-450.00")
                const numCleaned = val
                    .replace(/₹|Rs\.?|INR|,/gi, '')
                    .replace(/\b(ltr\.?|liters?|l|kg|gm|ml|pcs|nos|days?|hrs?|min)\b/gi, '')
                    .replace(/[%+]/g, '')
                    .replace(/\/[a-zA-Z]+/g, '')
                    .trim();

                if (/^-?\d+(\.\d+)?$/.test(numCleaned)) {
                    const num = parseFloat(numCleaned);
                    if (!isNaN(num)) {
                        return { type: 'number', value: num };
                    }
                }

                // 5. Fallback to natural string comparison (handles FAR-101, FAR-104, names, etc.)
                return { type: 'string', value: val.toLowerCase() };
            }

            function isCompanionDetailRow(tr) {
                const firstTd = tr.querySelector('td');
                const hasColspan = firstTd && parseInt(firstTd.getAttribute('colspan') || '1', 10) > 1;
                if (!hasColspan) return false;
                return tr.hasAttribute('x-show') ||
                       tr.hasAttribute('x-cloak') ||
                       tr.style.display === 'none' ||
                       (tr.id && (tr.id.includes('detail') || tr.id.includes('edit')));
            }

            function isPinnedBottomRow(tr) {
                const firstTd = tr.querySelector('td');
                if (!firstTd) return true;
                const colspan = parseInt(firstTd.getAttribute('colspan') || '1', 10);
                if (colspan > 1 && !isCompanionDetailRow(tr)) {
                    return true;
                }
                const firstText = (firstTd.innerText || firstTd.textContent || '').trim().toLowerCase();
                if (/^(total|grand total|sub\s*total)\b/.test(firstText)) {
                    return true;
                }
                return false;
            }

            function sortTableByColumn(table, th, colIndex) {
                const tbody = table.tBodies && table.tBodies[0];
                if (!tbody) return;

                const currentDir = th.getAttribute('data-sort-dir');
                const nextDir = currentDir === 'asc' ? 'desc' : 'asc';

                // Reset all headers in this thead
                const thead = th.closest('thead');
                if (thead) {
                    thead.querySelectorAll('th.sd-sortable-th').forEach(header => {
                        header.removeAttribute('data-sort-dir');
                        const icon = header.querySelector('.sd-sort-icon');
                        if (icon) icon.textContent = '⇅';
                    });
                }

                th.setAttribute('data-sort-dir', nextDir);
                const activeIcon = th.querySelector('.sd-sort-icon');
                if (activeIcon) {
                    activeIcon.textContent = nextDir === 'asc' ? '▲' : '▼';
                }

                // Group rows: keep companion detail rows attached to their main row, and keep pinned rows at the bottom
                const allRows = Array.from(tbody.querySelectorAll(':scope > tr'));
                if (allRows.length <= 1) return;

                const sortableGroups = [];
                const pinnedRows = [];

                for (let i = 0; i < allRows.length; i++) {
                    const row = allRows[i];
                    if (isCompanionDetailRow(row)) {
                        if (sortableGroups.length > 0) {
                            sortableGroups[sortableGroups.length - 1].rows.push(row);
                        } else {
                            pinnedRows.push(row);
                        }
                    } else if (isPinnedBottomRow(row)) {
                        pinnedRows.push(row);
                    } else {
                        const cell = row.children[colIndex];
                        const rawVal = cleanCellText(cell);
                        const token = parseSortToken(rawVal);
                        sortableGroups.push({
                            rows: [row],
                            token: token,
                            raw: rawVal,
                            origIndex: i
                        });
                    }
                }

                if (sortableGroups.length <= 1) return;

                sortableGroups.sort((a, b) => {
                    // Always push empty cells to the bottom
                    if (a.token.type === 'empty' && b.token.type === 'empty') return a.origIndex - b.origIndex;
                    if (a.token.type === 'empty') return 1;
                    if (b.token.type === 'empty') return -1;

                    let cmp = 0;
                    if (a.token.type === 'number' && b.token.type === 'number') {
                        cmp = a.token.value - b.token.value;
                    } else {
                        cmp = String(a.raw).localeCompare(String(b.raw), undefined, { numeric: true, sensitivity: 'base' });
                    }

                    if (cmp === 0) {
                        return a.origIndex - b.origIndex;
                    }
                    return nextDir === 'asc' ? cmp : -cmp;
                });

                const frag = document.createDocumentFragment();
                sortableGroups.forEach(group => {
                    group.rows.forEach(r => frag.appendChild(r));
                });
                pinnedRows.forEach(r => frag.appendChild(r));
                tbody.appendChild(frag);
            }

            window.initSortableTables = function(root = document) {
                const tables = root.querySelectorAll('table');
                tables.forEach(table => {
                    if (table.getAttribute('data-no-sort') === 'true') return;
                    const thead = table.querySelector('thead');
                    const tbody = table.querySelector('tbody');
                    if (!thead || !tbody) return;

                    const headerRows = thead.querySelectorAll('tr');
                    if (!headerRows.length) return;
                    const headerRow = headerRows[headerRows.length - 1];
                    const ths = Array.from(headerRow.children).filter(el => el.tagName === 'TH');

                    ths.forEach((th, idx) => {
                        if (th.classList.contains('sd-sortable-th') || th.getAttribute('data-no-sort') === 'true') {
                            return;
                        }
                        if (th.querySelector('input, select, button')) {
                            return;
                        }
                        const label = (th.innerText || th.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
                        if (NON_SORTABLE_HEADERS.has(label)) {
                            return;
                        }

                        th.classList.add('sd-sortable-th');
                        th.setAttribute('data-col-index', idx);
                        if (!th.getAttribute('title')) {
                            th.setAttribute('title', 'Click to sort Asc / Desc');
                        }

                        const icon = document.createElement('span');
                        icon.className = 'sd-sort-icon';
                        icon.setAttribute('aria-hidden', 'true');
                        icon.textContent = '⇅';
                        th.appendChild(icon);
                    });
                });
            };

            // Delegated click listener for all sortable table headers
            document.addEventListener('click', function(e) {
                const th = e.target.closest('th.sd-sortable-th');
                if (!th) return;
                if (e.target.closest('a, button, input, select')) return;

                const table = th.closest('table');
                if (!table) return;

                const tr = th.parentElement;
                const ths = Array.from(tr.children).filter(el => el.tagName === 'TH');
                const colIndex = ths.indexOf(th);
                if (colIndex === -1) return;

                sortTableByColumn(table, th, colIndex);
            });

            // Observe DOM changes so dynamically added tables also become sortable
            document.addEventListener('DOMContentLoaded', function() {
                window.initSortableTables();
                const mainContent = document.getElementById('app-main-content') || document.body;
                if (window.MutationObserver && mainContent) {
                    let timer = null;
                    const observer = new MutationObserver(() => {
                        if (timer) clearTimeout(timer);
                        timer = setTimeout(() => window.initSortableTables(mainContent), 150);
                    });
                    observer.observe(mainContent, { childList: true, subtree: true });
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
