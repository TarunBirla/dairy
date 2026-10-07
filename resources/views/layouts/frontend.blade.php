<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DairyMaster - Complete Dairy Management & Milk Delivery')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: {
                            800: '#00235b',
                            900: '#002e79', // Brand Primary #002e79
                            950: '#001a45',
                        },
                        amber: {
                            500: '#e07a2c',
                            600: '#c25e16',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .bg-navy-brand { background-color: #002e79; }
        .text-navy-brand { color: #002e79; }
        .border-navy-brand { border-color: #002e79; }
    </style>
</head>
<body class="bg-[#FBFBFE] text-slate-800 antialiased font-sans flex flex-col min-h-screen">

    <!-- Top Info Bar -->
    <div class="bg-[#002e79] text-white text-[12px] py-2 px-4 sm:px-8 flex justify-between items-center border-b border-blue-900/40">
        <div class="flex items-center gap-6">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-amber-400 text-xs"></i> +91 98765 43210</span>
            <span class="hidden sm:flex items-center gap-1.5"><i class="fa-solid fa-envelope text-amber-400 text-xs"></i> info@gopaldairy.com</span>
            <span class="hidden md:flex items-center gap-1.5"><i class="fa-solid fa-clock text-amber-400 text-xs"></i> Fresh Morning Dispatch: 5:00 AM - 8:30 AM</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
            <a href="{{ route('login') }}" class="hover:text-amber-300 transition flex items-center gap-1">
                <i class="fa-solid fa-circle-user text-amber-400"></i> Portal Login
            </a>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo Section with User's logo.PNG -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('logo.PNG') }}" alt="Gopal Dairy Logo" class="h-12 w-auto object-contain max-w-[180px] drop-shadow-xs group-hover:scale-102 transition">
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-[14px] font-bold text-slate-700">
                <a href="{{ route('home') }}" class="transition hover:text-[#002e79] {{ request()->routeIs('home') ? 'text-[#002e79] font-black border-b-2 border-[#002e79] pb-1' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="transition hover:text-[#002e79] {{ request()->routeIs('about') ? 'text-[#002e79] font-black border-b-2 border-[#002e79] pb-1' : '' }}">About Us</a>
                <a href="{{ route('products.frontend') }}" class="transition hover:text-[#002e79] {{ request()->routeIs('products.frontend') ? 'text-[#002e79] font-black border-b-2 border-[#002e79] pb-1' : '' }}">Products</a>
                <a href="{{ route('contact') }}" class="transition hover:text-[#002e79] {{ request()->routeIs('contact') ? 'text-[#002e79] font-black border-b-2 border-[#002e79] pb-1' : '' }}">Contact</a>
            </nav>

            <!-- CTA Buttons -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-[#002e79] hover:bg-[#00235b] text-white font-bold text-xs rounded-xl shadow-md shadow-blue-900/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-gauge-high text-amber-400"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2.5 bg-[#c25e16] hover:bg-[#a94f10] text-white font-bold text-xs rounded-xl shadow-md shadow-orange-900/20 transition flex items-center gap-2">
                        <span>Get Started</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Professional Footer Matching Screenshot -->
    <footer class="bg-[#1f1610] text-stone-300 pt-16 pb-8 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-10 pb-12 border-b border-stone-800 text-sm">
                <!-- Col 1: Brand & Logo -->
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-white/95 p-3 rounded-2xl inline-block max-w-[220px]">
                        <img src="{{ asset('logo.PNG') }}" alt="Gopal Dairy Logo" class="h-10 w-auto object-contain">
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed pr-6">
                        Complete dairy enterprise management & smart doorstep milk delivery solution. Empowering farmers with instant computerized FAT/SNF payouts and delivering pure, hygienic dairy to thousands of families.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-[#002e79] text-white flex items-center justify-center text-xs transition"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-[#002e79] text-white flex items-center justify-center text-xs transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-[#002e79] text-white flex items-center justify-center text-xs transition"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-stone-800 hover:bg-[#002e79] text-white flex items-center justify-center text-xs transition"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2.5 text-xs text-stone-400">
                        <li><a href="{{ route('home') }}" class="hover:text-amber-400 transition">Home</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-amber-400 transition">About Us</a></li>
                        <li><a href="{{ route('products.frontend') }}" class="hover:text-amber-400 transition">Products</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-amber-400 transition">Contact Us</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-amber-400 transition">Staff & Farmer Login</a></li>
                    </ul>
                </div>

                <!-- Col 3: Useful Links -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Our Services</h4>
                    <ul class="space-y-2.5 text-xs text-stone-400">
                        <li><a href="#" class="hover:text-amber-400 transition">Milk Collection Centers</a></li>
                        <li><a href="#" class="hover:text-amber-400 transition">Doorstep Delivery Routes</a></li>
                        <li><a href="#" class="hover:text-amber-400 transition">Daily Milk Subscriptions</a></li>
                        <li><a href="#" class="hover:text-amber-400 transition">Farmer Passbook & Advances</a></li>
                        <li><a href="#" class="hover:text-amber-400 transition">Privacy Policy & Terms</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & App -->
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Contact Info</h4>
                    <div class="space-y-2.5 text-xs text-stone-400">
                        <p class="flex items-start gap-2">
                            <i class="fa-solid fa-location-dot text-amber-500 mt-0.5"></i>
                            <span>Dairy Complex, Main Industrial Area, Indore, MP</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-phone text-amber-500"></i>
                            <span>+91 98765 43210</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-amber-500"></i>
                            <span>info@gopaldairy.com</span>
                        </p>
                    </div>

                    <div class="pt-5">
                        <span class="text-[11px] font-bold text-stone-300 block mb-2">Get Customer App</span>
                        <div class="bg-stone-900 border border-stone-800 rounded-xl p-2.5 flex items-center gap-3">
                            <i class="fa-brands fa-google-play text-2xl text-emerald-400"></i>
                            <div>
                                <span class="text-[9px] text-stone-400 uppercase block leading-tight">GET IT ON</span>
                                <span class="text-xs font-bold text-white leading-tight">Google Play</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500">
                <p>&copy; {{ date('Y') }} Gopal Dairy Management. All Rights Reserved.</p>
                <p class="mt-2 sm:mt-0 font-medium">Enterprise Dairy Management Platform</p>
            </div>
        </div>
    </footer>

</body>
</html>
