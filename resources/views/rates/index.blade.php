@extends('layouts.app')

@section('title', 'Rate Charts')
@section('breadcrumb', 'Rate Charts')
@section('header_title', 'Milk Quality Pricing & Rate Charts')

@section('header_action')
    <a href="{{ route('rates.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Create Rate Chart</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Configured Milk Rate Formulas</h3>
            <p class="text-xs text-slate-500">Automate milk procurement pricing based on FAT % and SNF % parameters.</p>
        </div>
        <a href="{{ route('rates.create') }}" class="px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs">
            + New Rate Rule
        </a>
    </div>

    <!-- Rate Charts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rateCharts as $rc)
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase capitalize {{ $rc->milk_type === 'buffalo' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $rc->milk_type }} Milk
                        </span>
                        @if($rc->is_default)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">
                                Default Rule
                            </span>
                        @endif
                    </div>

                    <h4 class="text-base font-bold text-slate-900">{{ $rc->name }}</h4>
                    <p class="text-xs text-slate-400 mt-1 capitalize font-medium">Calculation Type: <b>{{ str_replace('_', ' ', $rc->calculation_type) }}</b></p>

                    <!-- Formula Details -->
                    <div class="mt-4 p-3 bg-slate-50 rounded-xl space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Formula Rule:</span>
                            <span class="font-mono font-bold text-slate-900">
                                (FAT &times; {{ $rc->fat_factor }}) + (SNF &times; {{ $rc->snf_factor }})
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Valid FAT Range:</span>
                            <span class="font-bold text-slate-800">{{ $rc->min_fat }}% - {{ $rc->max_fat }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Valid SNF Range:</span>
                            <span class="font-bold text-slate-800">{{ $rc->min_snf }}% - {{ $rc->max_snf }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Base Benchmark Rate:</span>
                            <span class="font-black text-emerald-700">₹ {{ number_format($rc->base_rate, 2) }} / L</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Effective: {{ $rc->effective_date ? $rc->effective_date->format('d M Y') : 'Active' }}</span>
                    <a href="{{ route('rates.edit', $rc) }}" class="font-bold text-emerald-600 hover:underline">
                        Edit Chart &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-10 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                No rate charts configured yet. Click above to add your first milk rate rule.
            </div>
        @endforelse
    </div>

</div>
@endsection
