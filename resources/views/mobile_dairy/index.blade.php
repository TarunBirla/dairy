@extends('layouts.app')

@section('title', 'Mobile Dairy Software Hub')
@section('breadcrumb', 'Mobile Dairy Software')
@section('header_title', 'Mobile Dairy Management')

@section('header_action')
    <a href="{{ route('farmers.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        <span>Add New Farmer</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Hero / Software Header Card -->
    <div class="bg-gradient-to-r from-emerald-900 via-slate-900 to-teal-950 text-white rounded-2xl p-6 shadow-lg border border-slate-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    <span>Professional Mobile Dairy Edition</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-white">Farmer Management & Accounting Suite</h2>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Dedicated 8-tab operations workflow for daily milk collection, advances & loans, deductions, cattle feed & product sales, emergency assistance, period invoice settlements, and verified financial passbooks.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-slate-800/80 px-4 py-3 rounded-xl border border-slate-700 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Farmers</span>
                    <span class="text-xl font-black text-emerald-400">{{ number_format($totalFarmers) }}</span>
                </div>
                <div class="bg-slate-800/80 px-4 py-3 rounded-xl border border-slate-700 text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Active Members</span>
                    <span class="text-xl font-black text-sky-400">{{ number_format($activeFarmers) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Farmer Search & Select Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('mobile-dairy.index') }}" class="w-full sm:w-96 flex items-center gap-2">
            <div class="relative w-full">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by Farmer Code, Name, Phone, Village..." class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50/50">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('mobile-dairy.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition">
                    Clear
                </a>
            @endif
        </form>

        <span class="text-xs text-slate-500 font-medium">Click on any farmer below to open their full 8-in-1 Mobile Dairy software suite.</span>
    </div>

    <!-- Farmers Card Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($farmers as $fmr)
            <div class="bg-white rounded-2xl border border-slate-200/80 hover:border-emerald-500/50 hover:shadow-md transition p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 font-black text-sm shrink-0">
                                {{ substr($fmr->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug">{{ $fmr->name }}</h4>
                                <span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">{{ $fmr->farmer_code }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $fmr->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $fmr->status }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-500 space-y-1 pt-1">
                        <div class="flex items-center justify-between">
                            <span>Phone:</span>
                            <span class="font-semibold text-slate-700">{{ $fmr->phone ?: '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Village:</span>
                            <span class="font-semibold text-slate-700">{{ $fmr->village ?: '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Animal:</span>
                            <span class="font-semibold text-slate-700 capitalize">{{ $fmr->animal_type }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Ledger Balance:</span>
                        <span class="text-xs font-black {{ (float)$fmr->current_balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            ₹ {{ number_format($fmr->current_balance, 2) }}
                        </span>
                    </div>
                </div>

                <div class="pt-4 mt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('mobile-dairy.farmer', $fmr->id) }}" class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center gap-1.5">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                        <span>Open Mobile Dairy</span>
                    </a>
                    <a href="{{ route('farmers.show', $fmr->id) }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition" title="View Full Profile">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200 text-slate-400">
                <i data-lucide="users" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                <p class="text-sm font-semibold">No farmers matched your search filter.</p>
                <a href="{{ route('mobile-dairy.index') }}" class="text-xs text-emerald-600 font-bold hover:underline mt-2 inline-block">Reset Filters</a>
            </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $farmers->links() }}
    </div>
</div>
@endsection
