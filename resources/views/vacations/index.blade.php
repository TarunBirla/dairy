@extends('layouts.app')

@section('title', 'Vacation (leave) management')
@section('breadcrumb', 'Vacations')
@section('header_title', 'Dashboard / Vacations')

@section('content')
<div x-data="{ 
    modalOpen: false,
    cancelModalOpen: false,
    shift: 'full_day',
    fromDate: '{{ request('from_date', $today->format('Y-m-d')) }}',
    toDate: '{{ request('to_date', $today->format('Y-m-d')) }}',
    customerId: '{{ request('customer_id', '') }}'
}" class="max-w-7xl mx-auto space-y-6">

    <!-- Top Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs">
                    <i data-lucide="palmtree" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Vacation (leave) management</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Track customer vacations so deliveries are paused while they're away.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="cancelModalOpen = true" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-2xl border border-slate-200 shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="calendar-x" class="w-4 h-4 text-slate-400"></i>
                <span>Cancel leaves</span>
            </button>
            <button type="button" @click="modalOpen = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Schedule vacation</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards (Matching Screenshot 2 Exactly) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Days -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-range" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">TOTAL DAYS</span>
                <span class="text-2xl font-black text-slate-900 mt-0.5 block">{{ $totalDays }}</span>
                <span class="text-[11px] text-slate-400">across selected range</span>
            </div>
        </div>

        <!-- 2. Unique Customers -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">UNIQUE CUSTOMERS</span>
                <span class="text-2xl font-black text-slate-900 mt-0.5 block">{{ count(array_unique($uniqueCustomerIds)) }}</span>
                <span class="text-[11px] text-slate-400">{{ count(array_unique($uniqueCustomerIds)) }} customer{{ count(array_unique($uniqueCustomerIds)) === 1 ? '' : 's' }}</span>
            </div>
        </div>

        <!-- 3. On Vacation Today (Highlighted with skip warning) -->
        <div class="bg-white p-5 rounded-3xl border-2 border-amber-400/80 shadow-2xs flex items-center gap-4 relative overflow-hidden">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-off" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-black uppercase text-amber-700 tracking-wider block">ON VACATION TODAY</span>
                <span class="text-2xl font-black text-slate-900 mt-0.5 block">{{ $onVacationToday }}</span>
                <span class="text-[11px] font-bold text-amber-600">skip these deliveries</span>
            </div>
        </div>

        <!-- 4. Upcoming -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-clock" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block">UPCOMING</span>
                <span class="text-2xl font-black text-slate-900 mt-0.5 block">{{ $upcomingCount }}</span>
                <span class="text-[11px] text-slate-400">future-dated days</span>
            </div>
        </div>
    </div>

    <!-- Filter & Quick Range Bar (Matching Screenshot 2 Exactly) -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-2xs space-y-4">
        <!-- Quick Range Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider mr-1 flex items-center gap-1">
                <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>QUICK RANGE</span>
            </span>
            @foreach([
                'today' => 'Today',
                'tomorrow' => 'Tomorrow',
                'this_week' => 'This week',
                'this_month' => 'This month',
                'next_30_days' => 'Next 30 days',
            ] as $qrKey => $qrLabel)
                <a href="{{ route('vacations.index', array_merge(request()->except(['from_date', 'to_date', 'quick_range', 'page']), ['quick_range' => $qrKey])) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-bold border transition {{ $quickRange === $qrKey && !request()->filled('from_date') ? 'bg-emerald-50 text-emerald-800 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">
                    {{ $qrLabel }}
                </a>
            @endforeach
        </div>

        <!-- Date Range & Customer Filter Form -->
        <form method="GET" action="{{ route('vacations.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-2 border-t border-slate-100">
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From date</label>
                <input type="date" name="from_date" value="{{ $fromDate->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To date</label>
                <input type="date" name="to_date" value="{{ $toDate->format('Y-m-d') }}" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
            </div>
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Customer</label>
                <select name="customer_id" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    <option value="">All Customers</option>
                    @foreach($allCustomers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                    Filter
                </button>
                <a href="{{ route('vacations.index') }}" class="p-2 border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-xl transition" title="Reset Filters">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Main Grid: Left Side Table (8 cols) + Right Side Calendar (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Scheduled Vacations Table (Matching Screenshot 2) -->
        <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Scheduled vacations ({{ $vacations->total() }})</h3>
                </div>
                <span class="text-[11px] text-slate-400">Deliveries auto-skipped during leaves</span>
            </div>

            <!-- Vacations Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase text-slate-400 tracking-wider">
                            <th class="py-3 px-3">CUSTOMER</th>
                            <th class="py-3 px-3">VACATION DATE</th>
                            <th class="py-3 px-3">SHIFT</th>
                            <th class="py-3 px-3">REMARK</th>
                            <th class="py-3 px-3">ADDED BY</th>
                            <th class="py-3 px-3">CREATED ON</th>
                            <th class="py-3 px-3 text-right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($vacations as $v)
                            @php
                                $isToday = $v->coversDate($today);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <!-- Customer Column with Initials Avatar -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 font-black text-xs flex items-center justify-center shrink-0">
                                            {{ mb_substr($v->customer->name ?? 'C', 0, 2) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('customers.show', $v->customer_id) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition">
                                                {{ $v->customer->name }}
                                            </a>
                                            <span class="block text-[11px] text-slate-400 font-mono">{{ $v->customer->phone }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Vacation Date Badge -->
                                <td class="py-3 px-3">
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border text-[11px] font-bold {{ $isToday ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-slate-50 text-slate-700 border-slate-200' }}">
                                        <span>{{ $v->from_date->format('d M Y') }}</span>
                                        @if($v->from_date->toDateString() !== $v->to_date->toDateString())
                                            <span>&rarr; {{ $v->to_date->format('d M') }}</span>
                                        @endif
                                        @if($isToday)
                                            <span class="px-1.5 py-0.2 bg-amber-200 text-amber-900 text-[9px] font-black rounded uppercase">TODAY</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Shift Badge -->
                                <td class="py-3 px-3">
                                    @if($v->shift === 'morning')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Morning
                                        </span>
                                    @elseif($v->shift === 'evening')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            Evening
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Full day
                                        </span>
                                    @endif
                                </td>

                                <!-- Remark -->
                                <td class="py-3 px-3 font-medium text-slate-700">
                                    {{ $v->reason ?: '-' }}
                                </td>

                                <!-- Added By -->
                                <td class="py-3 px-3">
                                    <span class="text-slate-600 font-semibold">{{ $v->added_by ?: 'Admin' }}</span>
                                </td>

                                <!-- Created On -->
                                <td class="py-3 px-3 text-slate-400 text-[11px]">
                                    {{ $v->created_at->format('M d, h:i A') }}
                                </td>

                                <!-- Action (Cancel) -->
                                <td class="py-3 px-3 text-right">
                                    <form action="{{ route('vacations.destroy', $v) }}" method="POST" onsubmit="return confirm('Cancel this customer vacation leave and resume deliveries?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Cancel Vacation" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <i data-lucide="palmtree" class="w-6 h-6"></i>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-800">No vacations scheduled</h4>
                                    <p class="text-[11px] text-slate-400 max-w-xs mx-auto mt-0.5">There are no customer leaves scheduled for this selected range.</p>
                                    <button type="button" @click="modalOpen = true" class="mt-3 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                        + Schedule vacation
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($vacations->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $vacations->links() }}
                </div>
            @endif
        </div>

        <!-- Right: Interactive Vacation Calendar Widget (Matching Screenshot 2) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-2xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar-days" class="w-4 h-4 text-emerald-600"></i>
                    <h3 class="text-sm font-black text-slate-900">Vacation calendar</h3>
                </div>
                <span class="text-[10px] text-slate-400 font-semibold">Tap a date to filter</span>
            </div>

            <!-- Month Navigator Header -->
            @php
                $prevMonth = $calMonth->copy()->subMonth()->format('Y-m');
                $nextMonth = $calMonth->copy()->addMonth()->format('Y-m');
            @endphp
            <div class="flex items-center justify-between px-2">
                <a href="{{ route('vacations.index', array_merge(request()->query(), ['cal_month' => $prevMonth])) }}" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500 transition">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
                <span class="text-xs font-bold text-slate-800">{{ $calMonth->format('F Y') }}</span>
                <a href="{{ route('vacations.index', array_merge(request()->query(), ['cal_month' => $nextMonth])) }}" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500 transition">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Days of Week Header -->
            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 uppercase">
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
                <div>Sun</div>
            </div>

            <!-- Calendar Days Grid -->
            @php
                $startGrid = $calMonth->copy()->startOfMonth()->startOfWeek(Carbon\Carbon::MONDAY);
                $endGrid = $calMonth->copy()->endOfMonth()->endOfWeek(Carbon\Carbon::SUNDAY);
                $currDay = $startGrid->copy();
            @endphp
            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                @while($currDay->lte($endGrid))
                    @php
                        $ds = $currDay->toDateString();
                        $isCurrentMonth = $currDay->month === $calMonth->month;
                        $hasVacation = isset($datesWithVacation[$ds]) && $datesWithVacation[$ds] > 0;
                        $isTodayCell = $currDay->isToday();
                        $isSelectedDate = request('from_date') === $ds && request('to_date') === $ds;
                    @endphp
                    <div>
                        <a href="{{ route('vacations.index', ['from_date' => $ds, 'to_date' => $ds, 'cal_month' => $calMonth->format('Y-m')]) }}" 
                           class="w-8 h-8 mx-auto rounded-xl flex flex-col items-center justify-center transition relative text-[11px] font-bold cursor-pointer
                               {{ $isSelectedDate ? 'bg-slate-900 text-white shadow-xs' : 
                                  ($hasVacation ? 'bg-emerald-600 text-white font-black shadow-xs ring-2 ring-emerald-400/40' : 
                                  ($isTodayCell ? 'bg-amber-100 text-amber-900 border border-amber-300' : 
                                  ($isCurrentMonth ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-300 hover:bg-slate-50'))) }}">
                            <span>{{ $currDay->day }}</span>
                            @if($hasVacation && !$isSelectedDate)
                                <span class="w-1.5 h-1.5 rounded-full bg-white absolute bottom-1"></span>
                            @endif
                        </a>
                    </div>
                    @php $currDay->addDay(); @endphp
                @endwhile
            </div>

            <!-- Legend -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-medium px-1">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block"></span>
                    <span>Vacation leave</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
                    <span>Today</span>
                </span>
            </div>
        </div>

    </div>

    <!-- MODAL 1: SCHEDULE VACATION (Matching Screenshot 1 Exactly) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="modalOpen = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Schedule vacation</h3>
                        <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                            Pause deliveries for a customer over a date range. The vacation appears on the calendar and the customer's delivery sheet is auto-skipped.
                        </p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('vacations.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="shift" :value="shift">

                <!-- Date Range (From & To) -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Date range *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="relative">
                            <input type="date" name="from_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                        </div>
                        <div class="relative">
                            <input type="date" name="to_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                        </div>
                    </div>
                </div>

                <!-- Shift Pills (Morning, Evening, Full day) -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Shift *</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="shift = 'morning'" 
                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition text-center cursor-pointer"
                            :class="shift === 'morning' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'">
                            Morning
                        </button>
                        <button type="button" @click="shift = 'evening'" 
                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition text-center cursor-pointer"
                            :class="shift === 'evening' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'">
                            Evening
                        </button>
                        <button type="button" @click="shift = 'full_day'" 
                            class="py-2.5 px-3 rounded-xl text-xs font-bold border transition text-center cursor-pointer"
                            :class="shift === 'full_day' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'">
                            Full day
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Morning, evening, or the whole day.</p>
                </div>

                <!-- Customer Dropdown (Select...) -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Customer *</label>
                    <select name="customer_id" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                        <option value="">Select...</option>
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Reason / Remark -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Reason / Remark</label>
                    <input type="text" name="reason" placeholder="e.g. गांव में है / Out of town / Personal leave" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalOpen = false" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Schedule vacation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: CANCEL LEAVES RANGE -->
    <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="cancelModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-900">Cancel Vacation Leaves</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Cancel scheduled customer leaves in a date range to resume deliveries.</p>
                </div>
                <button type="button" @click="cancelModalOpen = false" class="text-slate-400 hover:text-slate-600 transition p-1 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('vacations.cancel-range') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">From date</label>
                        <input type="date" name="from_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">To date</label>
                        <input type="date" name="to_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Customer (Optional)</label>
                    <select name="customer_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                        <option value="">All Customers in range</option>
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="cancelModalOpen = false" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition cursor-pointer">
                        Close
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                        Cancel Leaves
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
