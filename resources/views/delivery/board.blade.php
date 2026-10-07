@extends('layouts.app')

@section('title', 'Daily Delivery Board')
@section('breadcrumb', 'Delivery Board')
@section('header_title', 'Daily Dispatch & Route Delivery Board')

@section('header_action')
    <form action="{{ route('delivery.generate-schedule') }}" method="POST" class="inline">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="shift" value="{{ $shift }}">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            <span>Generate Today's Schedule</span>
        </button>
    </form>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Total Stops</span>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Delivered</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Skipped / Leave</span>
            <p class="text-2xl font-black text-slate-400 mt-1">{{ $stats['skipped'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Cash Collected</span>
            <p class="text-2xl font-black text-teal-700 mt-1">₹ {{ number_format($stats['cash_collected'], 0) }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('delivery.board') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <input type="date" name="date" value="{{ $date }}" class="text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg">
            </div>
            <div>
                <select name="shift" class="text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg">
                    <option value="morning" {{ $shift === 'morning' ? 'selected' : '' }}>Morning Shift</option>
                    <option value="evening" {{ $shift === 'evening' ? 'selected' : '' }}>Evening Shift</option>
                </select>
            </div>
            <div>
                <select name="route_id" class="text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg">
                    <option value="">All Routes</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}" {{ $routeId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-3.5 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg">Filter</button>
        </form>

        <a href="{{ route('delivery.boy-app') }}" class="text-xs text-indigo-600 font-bold hover:underline flex items-center gap-1">
            <i data-lucide="bike" class="w-4 h-4"></i>
            <span>Switch to Delivery Boy Mobile App &rarr;</span>
        </a>
    </div>

    <!-- Delivery Stops Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4"># Stop</th>
                        <th class="py-3 px-4">Customer Details</th>
                        <th class="py-3 px-4">Route / Delivery Staff</th>
                        <th class="py-3 px-4">Product & Quantity</th>
                        <th class="py-3 px-4">Total (₹)</th>
                        <th class="py-3 px-4">Delivery Status</th>
                        <th class="py-3 px-4 text-right pr-6">Quick Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $del)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $del->customer) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                    {{ $del->customer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">{{ $del->customer->phone }} • {{ $del->customer->address }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $del->route ? $del->route->name : 'Direct' }}
                                <span class="block text-[10px] text-slate-400">{{ $del->deliveryBoy ? $del->deliveryBoy->name : 'Unassigned' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $del->quantity }} {{ $del->product ? $del->product->unit : 'Ltr' }}
                                <span class="block text-[11px] font-normal text-slate-500">{{ $del->product ? $del->product->name : 'Milk' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                ₹ {{ number_format($del->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $del->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($del->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $del->status }}
                                </span>
                                @if($del->failure_reason)
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $del->failure_reason }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                @if($del->status === 'pending')
                                    <form action="{{ route('delivery.update-status', $del) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg shadow-2xs">
                                            Mark Delivered
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 font-semibold">{{ ucfirst($del->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">No delivery schedule generated for this day/shift.</p>
                                <p class="text-xs text-slate-400 mt-1">Click "Generate Today's Schedule" above to auto-create stops from active subscriptions.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
