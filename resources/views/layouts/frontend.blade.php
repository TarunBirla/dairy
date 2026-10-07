<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pure Farm Fresh Milk & Dairy Delivery') | {{ \App\Models\SystemSetting::get('dairy_name', 'DairyMaster') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#FAFAFA] text-slate-800 antialiased font-sans flex flex-col min-h-screen">

    <!-- Top Announcement Bar -->
    <div class="bg-emerald-950 text-emerald-300 text-xs py-2 px-4 text-center font-medium">
        <span>🥛 100% Raw A2 Cow & Buffalo Milk &bull; Morning & Evening Doorstep Delivery Before 7:00 AM!</span>
    </div>

    <!-- Navigation Header -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo with Cow / Farmer / Milk Brand Theme -->
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                    <!-- Cow / Milk Icon -->
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 8c.83 0 1.5.67 1.5 1.5v4c0 .83-.67 1.5-1.5 1.5H5c-.83 0-1.5-.67-1.5-1.5v-4C3.5 8.67 4.17 8 5 8h14z"/>
                        <path d="M7 15v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-4"/>
                        <path d="M14 15v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-4"/>
                        <circle cx="9" cy="11.5" r="1.5" fill="currentColor"/>
                        <circle cx="15" cy="11.5" r="1.5" fill="currentColor"/>
                        <path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-black text-slate-900 tracking-tight leading-none block">
                        {{ \App\Models\SystemSetting::get('dairy_name', 'DairyMaster') }}
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-600 block mt-0.5">Farm Fresh &bull; Pure Milk &bull; Daily Delivery</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition {{ request()->routeIs('home') ? 'text-emerald-600' : '' }}">Home</a>
                <a href="{{ route('products.frontend') }}" class="hover:text-emerald-600 transition {{ request()->routeIs('products.frontend') ? 'text-emerald-600' : '' }}">Our Products</a>
                <a href="{{ route('about') }}" class="hover:text-emerald-600 transition {{ request()->routeIs('about') ? 'text-emerald-600' : '' }}">About Farm</a>
                <a href="{{ route('contact') }}" class="hover:text-emerald-600 transition {{ request()->routeIs('contact') ? 'text-emerald-600' : '' }}">Contact Us</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Admin Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="log-in" class="w-4 h-4 text-emerald-400"></i>
                        <span>Login / Portal</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white pt-16 pb-12 mt-20 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold">
                            🥛
                        </div>
                        <span class="text-lg font-black tracking-tight">{{ \App\Models\SystemSetting::get('dairy_name', 'DairyMaster') }}</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pure non-adulterated milk collected directly from village farmers, tested on FAT & SNF analyzer, chilled, and delivered at your doorstep every morning.
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('products.frontend') }}" class="hover:text-white">All Products & Milk</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">About Our Dairy Farm</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact & Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-4">Products</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li>A2 Gir Cow Milk</li>
                        <li>Fresh Buffalo Milk</li>
                        <li>Desi Danedaar Ghee</li>
                        <li>Fresh Malai Paneer</li>
                        <li>Thick Cream Curd</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-4">Staff & Farmer Login</h4>
                    <p class="text-xs text-slate-400 mb-3">Access dairy operations, collection slips, POS billing, or route delivery boy portal.</p>
                    <a href="{{ route('login') }}" class="inline-block px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition">
                        Sign In to DairyMaster Portal &rarr;
                    </a>
                </div>
            </div>
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ \App\Models\SystemSetting::get('dairy_name', 'DairyMaster') }}. All rights reserved.</p>
                <p class="mt-2 sm:mt-0 font-medium text-emerald-500">Built with Laravel &bull; Real-time Milk Procure & Delivery System</p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
