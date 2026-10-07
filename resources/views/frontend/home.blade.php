@extends('layouts.frontend')

@section('title', 'Every Litre Counted. Every Farmer Paid on Time | Gopal Dairy')

@section('content')
<div class="space-y-20 pb-16">

    <!-- HERO SECTION (MATCHING SCREENSHOT) -->
    <section class="relative overflow-hidden bg-gradient-to-b from-[#f0f5ff] via-[#f8faff] to-white pt-10 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Hero Content -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-[#002e79] text-xs font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-[#002e79] animate-pulse"></span>
                        <span>India's Most Trusted Dairy OS</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-6xl font-black text-slate-900 tracking-tight leading-[1.15]">
                        Every Litre Counted.<br>
                        Every Farmer <span class="text-[#c25e16]">Paid on Time.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-xl">
                        Smart computerized milk collection, real-time FAT/SNF automated rate calculations, farmer advances, route delivery boy tracking, and daily customer doorstep milk subscriptions.
                    </p>

                    <!-- Feature check pills -->
                    <div class="flex flex-wrap gap-3 text-xs font-semibold text-slate-700">
                        <span class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Automatic Fat/SNF Analyzer
                        </span>
                        <span class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Instant Thermal Print & SMS
                        </span>
                        <span class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg shadow-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Route Delivery Tracking
                        </span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="pt-2 flex flex-wrap items-center gap-4">
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-[#c25e16] hover:bg-[#a94f10] text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-orange-900/20 transition flex items-center gap-2">
                            <span>Get Started Now</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-[#002e79] hover:bg-[#00235b] text-white font-extrabold text-sm rounded-2xl shadow-xl shadow-blue-900/20 transition flex items-center gap-2">
                            <i class="fa-solid fa-laptop"></i>
                            <span>Live Demo Portal</span>
                        </a>
                    </div>
                </div>

                <!-- Right Hero Image / Dashboard Mockup Preview -->
                <div class="lg:col-span-5 relative">
                    <!-- Soft Background Glow -->
                    <div class="absolute -top-10 -right-10 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-amber-400/20 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative bg-slate-900 rounded-3xl p-4 sm:p-5 shadow-2xl border border-slate-800 text-white">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="ml-2 font-mono text-[11px] text-slate-400">DairyMaster OS v2.4</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[10px]">● Live Sync</span>
                        </div>

                        <!-- Mini Dashboard Screen -->
                        <div class="mt-4 space-y-4">
                            <!-- Stats Cards Row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-800/80 p-3 rounded-2xl border border-slate-700/60">
                                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Today Morning Milk</span>
                                    <span class="text-xl font-black text-amber-400">1,420.50 Ltr</span>
                                    <span class="text-[10px] text-emerald-400 block mt-0.5">+8.4% vs Yesterday</span>
                                </div>
                                <div class="bg-slate-800/80 p-3 rounded-2xl border border-slate-700/60">
                                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Active Deliveries</span>
                                    <span class="text-xl font-black text-white">384 Pkts</span>
                                    <span class="text-[10px] text-blue-400 block mt-0.5">98.2% Delivered</span>
                                </div>
                            </div>

                            <!-- Bar Graph Simulation -->
                            <div class="bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-bold text-slate-300">Fat & SNF Quality Variance</span>
                                    <span class="text-[10px] font-mono text-amber-400">Avg Fat: 6.8%</span>
                                </div>
                                <div class="h-24 flex items-end gap-2 pt-2">
                                    <div class="w-full bg-[#002e79] rounded-t-lg h-[65%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#c25e16] rounded-t-lg h-[85%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#002e79] rounded-t-lg h-[50%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#c25e16] rounded-t-lg h-[92%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#002e79] rounded-t-lg h-[78%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#c25e16] rounded-t-lg h-[60%] hover:opacity-90 transition"></div>
                                    <div class="w-full bg-[#002e79] rounded-t-lg h-[100%] hover:opacity-90 transition"></div>
                                </div>
                            </div>

                            <!-- Live Ticket / Collection Notification -->
                            <div class="p-3 bg-slate-800/90 rounded-2xl border border-slate-700 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                        COL
                                    </div>
                                    <div>
                                        <p class="font-bold text-white text-xs">Farmer Ramesh Patel (Far-101)</p>
                                        <p class="text-[10px] text-slate-400">15.5 Ltr Cow Milk @ ₹42.50/Ltr credited</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 font-bold rounded-lg text-[10px]">Paid</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 10 CORE PILLARS GRID (EXACTLY MATCHING SCREENSHOT) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-black uppercase tracking-widest text-[#002e79]">Complete End-to-End Capabilities</span>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight mt-1">Built For Every Dimension of Dairy Operations</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 sm:gap-6">
            <!-- 1. Milk Procurement -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🥛
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Milk Procurement</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Morning/Evening shifts, Fat/SNF/CLR testing, auto rate chart & slips.</p>
            </div>

            <!-- 2. Farmer Management -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    👨‍🌾
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Farmer Management</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Digital passbook, advances, cattle feed deduction & bank settlements.</p>
            </div>

            <!-- 3. Smart Deliveries -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🚚
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Smart Deliveries</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Route sequence, delivery boy app tracking, cash & empty bottle balance.</p>
            </div>

            <!-- 4. Customer Subscriptions -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    📅
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Customer Subscriptions</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Daily/alternate calendar schedules, vacation pause & monthly billing.</p>
            </div>

            <!-- 5. Processing & Plant -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🏭
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Processing & Plant</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Chilling BMC units, paneer, ghee & mawa conversion batch tracking.</p>
            </div>

            <!-- 6. Route Management -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🗺️
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Route Management</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Sequence optimization, milk crates, customer geo-pins & delivery proofs.</p>
            </div>

            <!-- 7. Milk Dispatches -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🚛
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Milk Dispatches</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Tanker outbound logistics, plant temperature dips & bulk receipts.</p>
            </div>

            <!-- 8. Staff Management -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    👥
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Staff Management</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Daily attendance, advance loans, monthly salary payroll & roles.</p>
            </div>

            <!-- 9. Accounts & Auditing -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    📊
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Accounts & Auditing</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Daybook, operating expenses, counter POS cash register reconciliation.</p>
            </div>

            <!-- 10. Online Ordering -->
            <div class="p-5 bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:border-[#002e79] hover:shadow-md transition text-center flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-2xl mb-3 shadow-inner">
                    🛍️
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Online Ordering</h3>
                <p class="text-[11px] text-slate-500 mt-1 leading-snug">Pre-order booking for festive ghee, mawa, paneer & fresh milk bags.</p>
            </div>
        </div>
    </section>

    <!-- PROCESS FLOW DIAGRAM SECTION (MATCHING SCREENSHOT CIRCULAR PIPELINE) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-50/80 via-white to-amber-50/80 rounded-3xl p-8 sm:p-12 border border-slate-200">
            <div class="text-center max-w-xl mx-auto mb-10">
                <span class="text-xs font-black uppercase tracking-wider text-[#002e79]">Integrated Farm-to-Consumer Ecosystem</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">How Gopal Dairy Platform Works</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
                <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-100 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#002e79] flex items-center justify-center font-black text-base mb-3">1</div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Village Milk Inward</h4>
                    <p class="text-xs text-slate-500 mt-1">Farmers bring milk. Analyzers calculate FAT & SNF in seconds and print digital slips.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-100 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-[#c25e16] flex items-center justify-center font-black text-base mb-3">2</div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Chilling & Processing</h4>
                    <p class="text-xs text-slate-500 mt-1">Milk is chilled at 4°C, packaged in hygienic bottles or converted to bilona ghee & paneer.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-100 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-[#002e79] flex items-center justify-center font-black text-base mb-3">3</div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Route Delivery Dispatch</h4>
                    <p class="text-xs text-slate-500 mt-1">Delivery boys take optimized customer routes and record delivery confirmations.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-100 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-[#c25e16] flex items-center justify-center font-black text-base mb-3">4</div>
                    <h4 class="font-extrabold text-slate-900 text-sm">Reconciliation & Bills</h4>
                    <p class="text-xs text-slate-500 mt-1">Automated customer invoices, WhatsApp receipts, and farmer settlement payouts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3 BUSINESS SEGMENTS CARDS (MATCHING SCREENSHOT) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Complete Dairy Software for <span class="text-[#c25e16]">Every Dairy Business</span></h2>
            <p class="text-sm text-slate-500 mt-2">Tailored workflows designed specifically for your exact operations.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1: Dairy Farms -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:border-[#002e79] transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-xl mb-4 font-bold">
                        🚜
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">Dedicated Dairy Farms</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Manage your own cattle breeding, milking cycles, fodder feeds, morning yields, and direct doorstep city supply.
                    </p>
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-xs font-semibold text-slate-700">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Cattle Yield History</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Daily Fodder Expense</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Direct Client Subscriptions</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Bottle Deposit Tracking</div>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#002e79] hover:underline flex items-center gap-1">
                        Explore Dairy Farm Features &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 2: Collection Centers -->
            <div class="bg-white rounded-3xl p-8 border-2 border-[#002e79] shadow-lg relative flex flex-col justify-between">
                <span class="absolute -top-3 right-6 bg-[#002e79] text-white text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                    Most Popular
                </span>
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-xl mb-4 font-bold">
                        ⚖️
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">For Milk Collection Centers</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Equipped for Mandi and Village Depots. Handles hundreds of farmers in minutes with automatic ultrasonic analyzers.
                    </p>
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-xs font-semibold text-slate-700">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Shift-wise Milk Registers</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Instant Rate Chart Slab Matrix</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Farmer Advance Deductions</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> One-Click Bank Payout Sheets</div>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#c25e16] hover:underline flex items-center gap-1">
                        Explore Collection Center OS &rarr;
                    </a>
                </div>
            </div>

            <!-- Card 3: Large Dairy Enterprises -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:border-[#002e79] transition flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-xl mb-4 font-bold">
                        🏢
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-2">For Large Dairy Enterprises</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-6">
                        Multi-branch support, BMC chilling hubs, retail counter outlets (POS), tanker dispatch, and staff payrolls.
                    </p>
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-xs font-semibold text-slate-700">
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Multi-Branch Management</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> BMC Tanker Logistics & Dips</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> POS Cashbook & Daybook</div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-600"></i> Complete Audit Trail</div>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-[#002e79] hover:underline flex items-center gap-1">
                        Explore Enterprise Setup &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MOBILE APP / MILK DELIVERY FEATURES (MATCHING SCREENSHOT) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Download Our App For <span class="text-[#c25e16]">Easy Milk Delivery</span></h2>
            <p class="text-sm text-slate-500 mt-2">Smooth mobile experience for both customers and route delivery boys.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Features List -->
            <div class="lg:col-span-7 space-y-6">
                <!-- 1. Easy Ordering -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#c25e16] flex items-center justify-center shrink-0 text-xl font-bold">
                        📱
                    </div>
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Easy Milk Ordering</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Choose pure cow, buffalo milk, fresh bilona ghee or paneer in single taps with online or cash payments.</p>
                    </div>
                </div>

                <!-- 2. Set Daily Delivery -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#002e79] flex items-center justify-center shrink-0 text-xl font-bold">
                        ⏱️
                    </div>
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Set Daily Delivery Time</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Get fresh morning milk before 7:00 AM consistently with doorstep ringing instructions.</p>
                    </div>
                </div>

                <!-- 3. Real-time Delivery Tracking -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#c25e16] flex items-center justify-center shrink-0 text-xl font-bold">
                        📍
                    </div>
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Real-Time Milk Tracking</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Track when delivery boy starts your route and receive immediate SMS/WhatsApp confirmation.</p>
                    </div>
                </div>

                <!-- 4. Subscription Management -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#002e79] flex items-center justify-center shrink-0 text-xl font-bold">
                        🗓️
                    </div>
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900">Vacation Pause & Calendar</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Going out of town? Pause milk supply anytime without losing advance wallet money.</p>
                    </div>
                </div>
            </div>

            <!-- Right Phone Mockup -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="w-72 bg-slate-900 p-4 rounded-[42px] shadow-2xl border-4 border-slate-800">
                    <div class="w-24 h-4 bg-slate-800 rounded-full mx-auto mb-4"></div>
                    <div class="bg-white rounded-[32px] p-4 text-slate-900 min-h-[460px] flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center pb-3 border-b border-slate-100 text-xs">
                                <span class="font-bold text-[#002e79]">Gopal Dairy App</span>
                                <span class="text-emerald-600 font-bold">Active</span>
                            </div>

                            <!-- Subscription Card -->
                            <div class="mt-4 p-3.5 bg-blue-50/70 rounded-2xl border border-blue-100">
                                <span class="text-[10px] text-slate-500 font-bold uppercase">My Daily Plan</span>
                                <h5 class="text-sm font-black text-[#002e79] mt-0.5">2 Ltr Pure Buffalo Milk</h5>
                                <p class="text-[11px] text-slate-600 mt-1">Morning: 06:15 AM &bull; ₹58/Ltr</p>
                                <div class="mt-3 flex gap-2">
                                    <button class="px-2.5 py-1 bg-white border border-slate-200 text-[10px] font-bold rounded-lg text-slate-700">Pause</button>
                                    <button class="px-2.5 py-1 bg-[#002e79] text-white text-[10px] font-bold rounded-lg">+ Add Qty</button>
                                </div>
                            </div>

                            <!-- Deliveries List -->
                            <div class="mt-4 space-y-2 text-xs">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Recent Drops</span>
                                <div class="p-2.5 bg-slate-50 rounded-xl flex justify-between items-center">
                                    <span>Today 06:12 AM</span>
                                    <span class="text-emerald-600 font-bold text-[11px]">✓ Delivered</span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-xl flex justify-between items-center">
                                    <span>Yesterday 06:15 AM</span>
                                    <span class="text-emerald-600 font-bold text-[11px]">✓ Delivered</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button class="w-full py-2.5 bg-[#c25e16] text-white text-xs font-bold rounded-xl shadow-xs">
                                Recharge Milk Wallet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT / INQUIRY SECTION (MATCHING SCREENSHOT FORM) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-[#fff7f0] to-[#f4f7ff] rounded-3xl p-8 sm:p-14 border border-stone-200">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Details & Illustration -->
                <div class="lg:col-span-5 space-y-6">
                    <span class="text-xs font-black uppercase tracking-wider text-[#002e79]">We are here to help</span>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Get In Touch With Our Team</h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Looking for fresh milk daily doorstep supply, setting up a collection center in your village, or requesting a software demo? Send us a quick note.
                    </p>

                    <div class="space-y-3 text-xs text-slate-700">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-[#002e79] flex items-center justify-center font-bold">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <span class="font-bold">+91 98765 43210 / 0731-2918234</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-[#c25e16] flex items-center justify-center font-bold">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <span class="font-bold">support@gopaldairy.com</span>
                        </div>
                    </div>
                </div>

                <!-- Right Form (Matching Screenshot) -->
                <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-black text-slate-900 mb-4">Fill Up The Form</h3>
                    <form action="#" method="POST" onsubmit="alert('Thank you! Our support executive will call you within 15 minutes.'); return false;" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Name *</label>
                                <input type="text" required placeholder="e.g. Narendra Malviya" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone *</label>
                                <input type="text" required placeholder="9876543210" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                            <input type="email" placeholder="narendra@example.com" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Your Message or Inquiry *</label>
                            <textarea rows="3" required placeholder="Tell us your milk requirement, locality, or dairy inquiry..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <input type="checkbox" id="consent" required class="rounded border-slate-300">
                            <label for="consent">I consent to receive milk delivery updates via SMS/WhatsApp.</label>
                        </div>

                        <button type="submit" class="w-full py-3.5 bg-[#c25e16] hover:bg-[#a94f10] text-white font-extrabold text-xs rounded-xl shadow-md shadow-orange-900/20 transition">
                            Submit Details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
