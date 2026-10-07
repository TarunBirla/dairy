@extends('layouts.frontend')

@section('title', 'Our Products & Milk Catalogue')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <div class="border-b border-slate-200 pb-6">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Our Farm Products</h1>
        <p class="text-xs text-slate-500 mt-1">100% natural, preservative-free dairy products prepared fresh every day.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($products as $prod)
            <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between hover:border-emerald-300 transition">
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
                        <span class="text-xs text-slate-400 block">Rate / {{ $prod->unit }}</span>
                        <span class="text-lg font-black text-slate-900">₹ {{ number_format($prod->price, 2) }}</span>
                    </div>
                    <a href="{{ route('login') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                        Order Now
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        {{ $products->links() }}
    </div>

</div>
@endsection
