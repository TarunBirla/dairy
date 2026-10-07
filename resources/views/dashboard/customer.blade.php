@extends('layouts.app')

@section('title', 'Customer Portal')
@section('breadcrumb', 'Customer')
@section('header_title', 'My Daily Milk & Orders')

@section('content')
<div class="space-y-6">

    <!-- Customer Welcome Card -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Customer Home</span>
            <h2 class="text-2xl font-black mt-1">Hello, {{ $customer->name }}!</h2>
            <p class="text-xs text-emerald-100 mt-1">Doorstep Address: <b>{{ $customer->address }}</b></p>
        </div>
        <div class="bg-white/10 backdrop-blur-xs p-4 rounded-2xl border border-white/20 text-right">
            <span class="text-[10px] uppercase font-bold text-emerald-200 block">Current Outstanding Bill</span>
            <span class="text-3xl font-black block mt-0.5">₹ {{ number_format($customer->current_balance, 2) }}</span>
            <span class="text-[11px] text-emerald-100 block">Payable via UPI or to delivery boy</span>
        </div>
    </div>

    <!-- Today's Delivery Status Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-900">Today's Delivery Status</h4>
                @if($todayDelivery)
                    <p class="text-xs text-slate-500">
                        {{ $todayDelivery->quantity }} {{ $todayDelivery->product ? $todayDelivery->product->unit : 'Ltr' }} - {{ $todayDelivery->product ? $todayDelivery->product->name : 'Milk' }}
                    </p>
                @else
                    <p class="text-xs text-slate-500">No scheduled delivery for today.</p>
                @endif
            </div>
        </div>
        <div>
            @if($todayDelivery)
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                    {{ $todayDelivery->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ ucfirst($todayDelivery->status) }}
                </span>
            @endif
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
        <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-3">My Milk Subscriptions</h3>
        <div class="space-y-3">
            @forelse($mySubscriptions as $sub)
                <div class="p-4 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <h5 class="font-bold text-slate-900 text-sm">{{ $sub->product->name }}</h5>
                        <p class="text-slate-500 mt-0.5">
                            <b>{{ $sub->quantity }} {{ $sub->product->unit }}</b> • {{ ucfirst($sub->frequency) }} • {{ ucfirst($sub->shift) }} shift @ ₹{{ $sub->unit_price }}/unit
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $sub->status }}
                        </span>
                        <form action="{{ route('subscriptions.toggle-pause', $sub) }}" method="POST">
                            @csrf
                            <input type="hidden" name="pause_from" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="pause_until" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                            <button type="submit" class="px-3 py-1 bg-white border border-slate-200 rounded-lg font-semibold hover:bg-slate-100">
                                {{ $sub->status === 'active' ? 'Pause Vacation' : 'Resume Plan' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-4">No active subscriptions.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
