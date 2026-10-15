@extends('layouts.app')

@section('title', 'Mobile Dairy Software - ' . $farmer->name . ' (' . $farmer->farmer_code . ')')
@section('breadcrumb', 'Mobile Dairy Software')
@section('header_title', 'Mobile Dairy Suite: ' . $farmer->name)

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('farmers.show', $farmer->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Farmer Profile</span>
        </a>
        <a href="{{ route('mobile-dairy.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Switch Farmer</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Notice Bar for Mobile Dairy Software -->
    <div class="bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 rounded-2xl flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                <i data-lucide="smartphone" class="w-4 h-4"></i>
            </span>
            <div>
                <h4 class="text-xs font-black text-emerald-950 uppercase tracking-wider">Mobile Dairy Management Software</h4>
                <p class="text-[11px] text-emerald-800">Complete cooperative milk collection, advance/loan ledger, deductions, feed sales, emergency funds, period invoices & passbooks.</p>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-2">
            <span class="text-[11px] font-bold text-emerald-800">Active Farmer:</span>
            <span class="text-xs font-extrabold text-emerald-950 font-mono bg-white px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs">
                {{ $farmer->farmer_code }} - {{ $farmer->name }}
            </span>
        </div>
    </div>

    <!-- The 8-in-1 Mobile Dairy Suite Partial -->
    @include('farmers.partials.mobile_dairy_suite', [
        'farmer' => $farmer,
        'suite' => $mobileDairyData ?? []
    ])
</div>
@endsection
