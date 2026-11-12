@extends('layouts.app')

@section('title', 'Reports')
@section('breadcrumb', 'Reports')
@section('header_title', 'Reports & Analytics')

@section('content')
<div class="space-y-6">

    <!-- Top Summary Banner -->
    <div class="bg-gradient-to-r from-[#005c53] to-[#042f2e] text-white p-6 rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-6 h-6 text-emerald-300"></i>
                <h2 class="text-xl font-black tracking-tight">Reports & Analytical Statements</h2>
            </div>
            <p class="text-xs text-emerald-100/80 mt-1 max-w-2xl">
                Comprehensive operational, accounting, dairy procurement, cattle feed, advance ledgers, and sales reports for Gopal Dudh Dairy.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.collections.date-wise') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl backdrop-blur-xs border border-white/20 transition flex items-center gap-1.5">
                <i data-lucide="milk" class="w-4 h-4 text-emerald-300"></i>
                <span>Quick Collection Report</span>
            </a>
            <a href="{{ route('reports.milk-sales.date-wise') }}" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>Milk Sale Reports</span>
            </a>
        </div>
    </div>

    <!-- Reports Hub Grid matching Mobile Dairy UI (8 Main Categories) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
        
        <!-- 1. Collection Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <i data-lucide="milk" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Collection Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.collections.date-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-900">Date Wise</span>
                </a>
                <a href="{{ route('reports.collections.farmer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-900">Farmer Wise</span>
                </a>
                <a href="{{ route('reports.collections.farmer-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-900">Farmer Wise Summary</span>
                </a>
                <a href="{{ route('reports.collections.center-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-900">Center Wise Summary</span>
                </a>
                <a href="{{ route('reports.collections.window') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-900">Collection Window Report</span>
                </a>
            </div>
        </div>

        <!-- 2. Bill Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Bill Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.bills.date-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-900">Date Wise</span>
                </a>
                <a href="{{ route('reports.bills.farmer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-900">Farmer Wise</span>
                </a>
                <a href="{{ route('reports.bills.farmer-bills') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-900">Farmer Wise Bills</span>
                </a>
                <a href="{{ route('reports.bills.payment-register') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-900">Payment Register</span>
                </a>
                <a href="{{ route('reports.bills.bank-file') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-indigo-500 hover:bg-indigo-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-900">Bank Payment File</span>
                </a>
            </div>
        </div>

        <!-- 3. Cattle Feed Sale Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                    <i data-lucide="wheat" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Cattle Feed Sale Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.cattle-feed.sales.date-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-500 hover:bg-amber-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-900">Date Wise</span>
                </a>
                <a href="{{ route('reports.cattle-feed.sales.farmer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-500 hover:bg-amber-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-900">Farmer Wise</span>
                </a>
                <a href="{{ route('reports.cattle-feed.sales.summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-500 hover:bg-amber-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-900">Sale Summary</span>
                </a>
                <a href="{{ route('reports.cattle-feed.sales.register') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-amber-500 hover:bg-amber-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-amber-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-900">Food Sale Register</span>
                </a>
            </div>
        </div>

        <!-- 4. Cattle Feed Purchase Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-700 flex items-center justify-center font-bold">
                    <i data-lucide="package-search" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Cattle Feed Purchase Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.cattle-feed.purchases.stock-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-orange-500 hover:bg-orange-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-orange-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-orange-900">Stock Summary</span>
                </a>
                <a href="{{ route('reports.cattle-feed.purchases.stock-purchase') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-orange-500 hover:bg-orange-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-orange-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-orange-900">Food Stock Purchase Report</span>
                </a>
                <a href="{{ route('reports.cattle-feed.purchases.dealer-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-orange-500 hover:bg-orange-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-orange-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-orange-900">Dealer Payment Summary</span>
                </a>
                <a href="{{ route('reports.cattle-feed.purchases.profit-loss') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-orange-500 hover:bg-orange-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-orange-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-orange-900">Food Wise Profit Loss</span>
                </a>
            </div>
        </div>

        <!-- 5. Milk Sale Reports (Primary focus requested) -->
        <div class="p-6 bg-emerald-50/20">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center font-bold">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-teal-900 uppercase tracking-wider">Milk Sale Reports</h3>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 font-bold">Commercial Buyers & Sales</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.milk-sales.date-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl bg-white border border-teal-200 hover:border-teal-500 hover:bg-teal-50/60 shadow-2xs transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-teal-500 group-hover:text-teal-700 transition"></i>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-teal-950">Date Wise</span>
                </a>
                <a href="{{ route('reports.milk-sales.customer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl bg-white border border-teal-200 hover:border-teal-500 hover:bg-teal-50/60 shadow-2xs transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-teal-500 group-hover:text-teal-700 transition"></i>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-teal-950">Customer Wise</span>
                </a>
                <a href="{{ route('reports.milk-sales.sale-bill-customer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl bg-white border border-teal-200 hover:border-teal-500 hover:bg-teal-50/60 shadow-2xs transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-teal-500 group-hover:text-teal-700 transition"></i>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-teal-950">Sale Bill Customer Wise</span>
                </a>
                <a href="{{ route('reports.milk-sales.customer-khata') }}" class="group flex items-center gap-2 p-2.5 rounded-xl bg-white border border-teal-200 hover:border-teal-500 hover:bg-teal-50/60 shadow-2xs transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-teal-500 group-hover:text-teal-700 transition"></i>
                    <span class="text-xs font-bold text-slate-800 group-hover:text-teal-950">Customer Khata</span>
                </a>
            </div>
        </div>

        <!-- 6. Advance Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold">
                    <i data-lucide="hand-coins" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Advance Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.advances.farmer-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-900">Farmer Wise</span>
                </a>
                <a href="{{ route('reports.advances.farmer-advance-wise') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-900">Farmer Advance Wise</span>
                </a>
                <a href="{{ route('reports.advances.loan-report') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-900">Loan Report</span>
                </a>
                <a href="{{ route('reports.advances.center-loan-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-900">Center Loan Summary</span>
                </a>
                <a href="{{ route('reports.advances.farmer-loan-summary') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-900">Farmer Loan Summary</span>
                </a>
            </div>
        </div>

        <!-- 7. Master Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center font-bold">
                    <i data-lucide="contact-2" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Master Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.masters.farmer-list') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-purple-500 hover:bg-purple-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-900">Farmer List</span>
                </a>
                <a href="{{ route('reports.masters.milk-sale-farmer-list') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-purple-500 hover:bg-purple-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-900">Milk Sale Farmer List</span>
                </a>
                <a href="{{ route('reports.masters.food-dealer-list') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-purple-500 hover:bg-purple-50/50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-purple-600 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-900">Food Dealer List</span>
                </a>
            </div>
        </div>

        <!-- 8. Other Reports -->
        <div class="p-6">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Other Reports</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 pt-1">
                <a href="{{ route('reports.others.passbook') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-400 hover:bg-slate-50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-800 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Passbook</span>
                </a>
                <a href="{{ route('reports.others.milk-sale-purchase') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-400 hover:bg-slate-50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-800 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Milk Sale Purchase Report</span>
                </a>
                <a href="{{ route('reports.others.milk-profit-loss') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-400 hover:bg-slate-50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-800 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Milk Profit Loss</span>
                </a>
                <a href="{{ route('reports.others.dairy-expense') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-400 hover:bg-slate-50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-800 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Dairy Expense</span>
                </a>
                <a href="{{ route('reports.others.incentive-calculator') }}" class="group flex items-center gap-2 p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-400 hover:bg-slate-50 transition">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-800 transition"></i>
                    <span class="text-xs font-semibold text-slate-700 group-hover:text-slate-900">Incentive Calculator</span>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
