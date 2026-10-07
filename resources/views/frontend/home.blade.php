@extends('layouts.frontend')

@section('title', 'Pure Farm Fresh Milk & Dairy Products Delivery')

@section('content')
<div class="space-y-16">

    <!-- Hero Banner Slider -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        @if($banners->count() > 0)
            @php $hero = $banners->first(); @endphp
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r {{ $hero->bg_gradient ?? 'from-emerald-950 via-slate-900 to-emerald-900' }} text-white p-8 sm:p-16 shadow-2xl flex flex-col justify-between min-h-[460px]">
                <div class="max-w-2xl space-y-4">
                    @if($hero->badge_text)
                        <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-xs font-bold tracking-wide uppercase">
                            {{ $hero->badge_text }}
                        </span>
                    @endif
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        {{ $hero->title }}
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $hero->subtitle ?? 'Farm fresh raw milk tested on automatic ultrasonic analyzers, chilled at 4°C, and delivered directly to your home every morning.' }}
                    </p>
                    <div class="pt-4 flex flex-wrap items-center gap-3">
                        <a href="{{ $hero->cta_url ?? route('products.frontend') }}" class="px-7 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg shadow-emerald-500/30 transition">
                            {{ $hero->cta_text ?? 'Order Fresh Milk' }}
                        </a>
                        <a href="{{ route('about') }}" class="px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-sm rounded-2xl backdrop-blur-xs transition">
                            Learn More About Farm &rarr;
                        </a>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-white/10 flex flex-wrap gap-8 text-xs font-bold text-slate-300">
                    <span class="flex items-center gap-2">🥛 Zero Water Adulteration</span>
                    <span class="flex items-center gap-2">🚜 250+ Village Farmers Network</span>
                    <span class="flex items-center gap-2">⚡ Delivery Daily Before 7:00 AM</span>
                </div>
            </div>
        @else
            <!-- Default Fallback Banner -->
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-emerald-950 via-slate-900 to-emerald-900 text-white p-8 sm:p-16 shadow-2xl min-h-[420px] flex flex-col justify-between">
                <div class="max-w-2xl space-y-4">
                    <span class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-xs font-bold tracking-wide uppercase">
                        100% PURE & DIRECT FROM FARMERS
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        Fresh Cow & Buffalo Milk Delivered to Your Doorstep.
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        Say goodbye to packet milk chemicals. Experience real village cow milk with natural fat and creaminess.
                    </p>
                    <div class="pt-4 flex flex-wrap gap-3">
                        <a href="{{ route('products.frontend') }}" class="px-7 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg transition">
                            Explore Products
                        </a>
                        <a href="{{ route('login') }}" class="px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold text-sm rounded-2xl transition">
                            Customer / Staff Login
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- Why Choose Us / Features -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-10">
            <h2 class="text-2xl font-black text-slate-900">Why Families Trust Our Milk</h2>
            <p class="text-xs text-slate-500 mt-1">Direct farm to glass pipeline with zero processing chemicals.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Lab Tested Every Morning</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Each batch is tested for FAT, SNF, CLR, and chemical adulterants before chilling and bottle packaging.</p>
            </div>
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="sun" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Guaranteed Morning Delivery</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Dedicated delivery boys deliver sanitized glass bottles at your doorstep before 7:00 AM daily.</p>
            </div>
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="heart-handshake" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Empowering Local Farmers</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Fair rates with transparent computerized FAT/SNF rate slips directly credited to farmer bank accounts.</p>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Fresh Farm Catalogue</h2>
                <p class="text-xs text-slate-500 mt-0.5">Order one-time or start a daily morning subscription.</p>
            </div>
            <a href="{{ route('products.frontend') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                View All Products &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $prod)
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition">
                    <div>
                        <div class="w-full h-36 rounded-2xl bg-emerald-50/50 text-emerald-600 flex items-center justify-center mb-4">
                            <i data-lucide="package" class="w-12 h-12 text-emerald-500"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            {{ $prod->category->name ?? 'Dairy Fresh' }}
                        </span>
                        <h4 class="text-base font-bold text-slate-900 mt-2">{{ $prod->name }}</h4>
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $prod->description ?? 'Pure natural farm product.' }}</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-400 block">Price / {{ $prod->unit }}</span>
                            <span class="text-lg font-black text-slate-900">₹ {{ number_format($prod->price, 2) }}</span>
                        </div>
                        <a href="{{ route('login') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            Subscribe
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
