@extends('layouts.app')

@section('title', 'Counter Sales')
@section('breadcrumb', 'Counter sales')
@section('header_title', 'Counter sales')

@section('content')
<div class="space-y-5">

    <!-- Top Breadcrumb -->
    <div class="text-xs text-slate-400 font-medium">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-700 font-semibold">Counter sales</span>
    </div>

    <!-- Header Title & Subtitle matching Screenshot -->
    <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Counter sales</h1>
            <p class="text-xs text-slate-500 mt-0.5">Quick walk-in sales at your dairy counter — record, track, and analyse.</p>
        </div>
    </div>

    <!-- Ready to ring up a sale? Green Banner Card -->
    <div class="bg-white rounded-2xl border border-emerald-200/90 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Ready to ring up a sale?</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tap products to build a cart, take cash or UPI, done in seconds.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('pos.reports') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition flex items-center gap-2 shadow-2xs">
                <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                <span>View reports</span>
            </a>
            <a href="{{ route('pos.terminal') }}" class="px-5 py-2.5 bg-[#15803d] hover:bg-[#166534] text-white font-bold text-xs rounded-xl transition flex items-center gap-2 shadow-xs">
                <span>Start a sale</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Free for a limited time strip -->
    <div class="bg-amber-50/50 border border-amber-200/70 rounded-xl px-4 py-2.5 text-xs flex flex-wrap items-center justify-between gap-2 text-amber-900/90">
        <div class="flex items-center gap-2 font-medium">
            <span class="inline-flex items-center gap-1 text-amber-700 font-bold">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Free for a limited time</span>
            </span>
            <span class="text-slate-400">|</span>
            <span class="text-slate-600">Questions or feedback? We're a call away.</span>
        </div>
        <a href="tel:7879311015" class="inline-flex items-center gap-1.5 font-bold text-slate-800 hover:text-emerald-700 transition">
            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>7879311015</span>
        </a>
    </div>

    <!-- Analytics Section: Overall Sales & Product Mix -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- 1. Overall Sales Line Chart Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Overall sales</h4>
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">
                                {{ str_replace('_', ' ', strtoupper($salesFilter)) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('pos.reports') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 flex items-center gap-1 transition">
                        <span>View all</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Big Amount Display -->
                <div class="mt-4">
                    <span class="text-3xl font-black text-slate-900">₹{{ number_format($overallSalesTotal, 0) }}</span>
                </div>

                <!-- Filter Tabs -->
                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                    <a href="{{ route('pos.hub', array_merge(request()->query(), ['sales_filter' => 'last_7_days'])) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition border {{ $salesFilter === 'last_7_days' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        Last 7 Days
                    </a>
                    <a href="{{ route('pos.hub', array_merge(request()->query(), ['sales_filter' => 'this_month'])) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition border {{ $salesFilter === 'this_month' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        This Month
                    </a>
                    <a href="{{ route('pos.hub', array_merge(request()->query(), ['sales_filter' => 'last_6_months'])) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition border {{ $salesFilter === 'last_6_months' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        Last 6 Months
                    </a>
                    <a href="{{ route('pos.hub', array_merge(request()->query(), ['sales_filter' => 'year_2026'])) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition border {{ $salesFilter === 'year_2026' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        Year 2026
                    </a>
                </div>
            </div>

            <!-- Line Chart Canvas -->
            <div class="mt-6 pt-2 h-64 w-full">
                <canvas id="overallSalesChart"></canvas>
            </div>
        </div>

        <!-- 2. Product Mix Donut Chart Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Product mix</h4>
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">
                                {{ str_replace('_', ' ', strtoupper($mixFilter)) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('pos.reports') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 flex items-center gap-1 transition">
                        <span>View all</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <!-- Big Amount Display & Top Item Tag -->
                <div class="mt-4 flex flex-wrap items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900">₹{{ number_format($mixTotalAmount, 0) }}</span>
                    @if($topProductText)
                        <span class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $topProductText }}
                        </span>
                    @endif
                </div>

                <!-- Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 mt-3">
                    @php
                        $filters = [
                            'today' => 'Today',
                            'yesterday' => 'Yesterday',
                            'this_week' => 'This Week',
                            'last_week' => 'Last Week',
                            'this_month' => 'This Month',
                            'last_3_months' => 'Last 3 Months',
                            'last_6_months' => 'Last 6 Months',
                            'year_2026' => 'Year 2026',
                        ];
                    @endphp
                    @foreach($filters as $k => $lbl)
                        <a href="{{ route('pos.hub', array_merge(request()->query(), ['mix_filter' => $k])) }}"
                           class="px-2.5 py-0.5 rounded-full text-xs font-semibold transition border {{ $mixFilter === $k ? 'bg-sky-50 text-sky-800 border-sky-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Donut Chart & Legend -->
            <div class="mt-6 flex flex-col items-center">
                <div class="h-48 w-48 relative">
                    <canvas id="productMixChart"></canvas>
                </div>

                <!-- Legend below donut matching screenshot -->
                @if(count($mixLegend) > 0)
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mt-4 pt-3 border-t border-slate-100 text-xs">
                        @foreach($mixLegend as $leg)
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-xs shrink-0" style="background-color: {{ $leg['color'] }};"></span>
                                <span class="text-slate-700 font-medium">{{ $leg['name'] }}</span>
                                <span class="font-bold text-slate-900">₹{{ number_format($leg['amount'], 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-xs text-slate-400 text-center">
                        No product mix data recorded for {{ str_replace('_', ' ', $mixFilter) }}.
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Overall Sales Line Chart
    const lineCtx = document.getElementById('overallSalesChart');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Sales (₹)',
                    data: {!! json_encode($chartValues) !!},
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0284c7',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹ ' + Number(context.raw).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748b',
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Product Mix Donut Chart
    const donutCtx = document.getElementById('productMixChart');
    if (donutCtx) {
        const donutLabels = {!! json_encode($donutLabels) !!};
        const donutValues = {!! json_encode($donutValues) !!};
        const donutColors = {!! json_encode($donutColors) !!};

        if (donutValues.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutValues,
                        backgroundColor: donutColors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ₹ ' + Number(context.raw).toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush
@endsection