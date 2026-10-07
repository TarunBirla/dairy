@extends('layouts.frontend')

@section('title', 'About Our Dairy Farm & Mission')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 space-y-12">

    <div class="text-center space-y-3">
        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold uppercase tracking-wider">
            Our Journey & Roots
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
            Bridging Village Farmers Directly with City Families
        </h1>
        <p class="text-sm text-slate-500 max-w-2xl mx-auto leading-relaxed">
            We started with a simple belief: milk should reach your kitchen within hours of milking, untreated by preservatives or chemical homogenization.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-xs space-y-6 text-sm text-slate-600 leading-relaxed">
        <h3 class="text-lg font-bold text-slate-900">Pure Milk from Happy Cattle</h3>
        <p>
            Our dairy network brings together hundreds of progressive dairy farmers. Cows and buffaloes are grass-fed and nurtured in clean village pastures. We reject synthetic hormones and prioritize cattle wellness above all.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
            <div class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>🔬 Automated Quality Analyzers</span>
                </h4>
                <p class="text-xs text-slate-500 mt-1">Every drop is verified for purity, natural fat content, and solids-not-fat before loading into our cold-chain vans.</p>
            </div>
            <div class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <span>🌱 Eco-Friendly Glass Bottles</span>
                </h4>
                <p class="text-xs text-slate-500 mt-1">We eliminate single-use plastics by packaging fresh milk in sterilized glass bottles that keep milk chilled and pure.</p>
            </div>
        </div>
    </div>

</div>
@endsection
