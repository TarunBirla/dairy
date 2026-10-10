@extends('layouts.frontend')

@section('title', 'Pure Farm Dairy Products & Fresh Milk | Gopal Dairy')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">

    <!-- Title & Filter Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-[#002e79] text-xs font-black uppercase tracking-wider mb-2">
                100% Direct From Farm
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Our Farm Fresh Products</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Hygienically packaged, preservative-free dairy goods prepared fresh every morning.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-semibold">Total Catalogue:</span>
            <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-[#002e79] shadow-xs">{{ $products->total() }} Products</span>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($products as $prod)
            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs flex flex-col justify-between hover:border-[#002e79] hover:shadow-md transition group">
                <div>
                    <!-- Product Placeholder / Badge -->
                    <div class="w-full h-40 rounded-2xl bg-gradient-to-tr from-slate-50 to-blue-50/50 flex flex-col items-center justify-center mb-4 relative overflow-hidden group-hover:scale-[1.02] transition">
                        @if(!empty($prod->image))
                            <img src="{{ asset($prod->image) }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl">
                                @if(str_contains(strtolower($prod->name), 'ghee') || str_contains($prod->name, 'घी'))
                                    🧈
                                @elseif(str_contains(strtolower($prod->name), 'paneer') || str_contains($prod->name, 'पनीर'))
                                    🧀
                                @elseif(str_contains(strtolower($prod->name), 'feed') || str_contains($prod->name, 'khalli'))
                                    🌾
                                @elseif(str_contains(strtolower($prod->name), 'bottle'))
                                    🍶
                                @else
                                    🥛
                                @endif
                            </span>
                        @endif
                        @if(!$prod->in_stock)
                            <span class="absolute top-3 right-3 px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-md z-10">Out of Stock</span>
                        @else
                            <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-md z-10">In Stock</span>
                        @endif
                    </div>

                    <span class="text-[10px] font-black uppercase tracking-wider text-[#002e79] bg-blue-50 px-2.5 py-1 rounded-md">
                        {{ $prod->category->name ?? 'Fresh Dairy' }}
                    </span>
                    <h4 class="text-base font-extrabold text-slate-900 mt-2.5 group-hover:text-[#002e79] transition">{{ $prod->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $prod->description ?? 'Pure natural farm product.' }}</p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Per {{ $prod->unit }}</span>
                        <span class="text-lg font-black text-slate-900">₹ {{ number_format($prod->price, 2) }}</span>
                    </div>
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-[#002e79] hover:bg-[#00235b] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                        <span>Order Now</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-4 text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200">
                <p class="text-slate-400 text-sm">No products available in catalogue at the moment.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $products->links() }}
    </div>

</div>
@endsection
