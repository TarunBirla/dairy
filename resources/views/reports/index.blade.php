@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('breadcrumb', 'Reports')
@section('header_title', 'Business Reports & Operational Analytics')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Collection Report Card -->
        <a href="{{ route('reports.collections') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md transition group block">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:bg-emerald-600 group-hover:text-white transition">
                <i data-lucide="milk" class="w-5 h-5"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition">Milk Procurement Report</h4>
            <p class="text-xs text-slate-500 mt-1">Shift-wise milk quantity, average FAT/SNF, quality metrics, and farmer payouts.</p>
            <span class="text-xs text-emerald-600 font-bold mt-4 block">View Report &rarr;</span>
        </a>

        <!-- Delivery Report Card -->
        <a href="{{ route('reports.deliveries') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md transition group block">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:bg-indigo-600 group-hover:text-white transition">
                <i data-lucide="bike" class="w-5 h-5"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-700 transition">Daily Delivery Report</h4>
            <p class="text-xs text-slate-500 mt-1">Route delivery fulfillment, skipped stops, reasons, and cash collections.</p>
            <span class="text-xs text-indigo-600 font-bold mt-4 block">View Report &rarr;</span>
        </a>

        <!-- Sales Report Card -->
        <a href="{{ route('reports.sales') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md transition group block">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-3 group-hover:bg-teal-600 group-hover:text-white transition">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition">Sales & Revenue Report</h4>
            <p class="text-xs text-slate-500 mt-1">Retail POS counter billing, itemized product volumes, and payment modes.</p>
            <span class="text-xs text-teal-600 font-bold mt-4 block">View Report &rarr;</span>
        </a>

        <!-- Outstanding Dues Report Card -->
        <a href="{{ route('reports.dues') }}" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 hover:shadow-md transition group block">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:bg-amber-600 group-hover:text-white transition">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900 group-hover:text-amber-700 transition">Outstanding & Dues Report</h4>
            <p class="text-xs text-slate-500 mt-1">Customer receivables ledger ageing and farmer unsettled procurement payables.</p>
            <span class="text-xs text-amber-600 font-bold mt-4 block">View Report &rarr;</span>
        </a>

    </div>

</div>
@endsection
