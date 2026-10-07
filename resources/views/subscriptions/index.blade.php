@extends('layouts.app')

@section('title', 'Milk Subscriptions')
@section('breadcrumb', 'Subscriptions')
@section('header_title', 'Recurring Milk Delivery Subscriptions')

@section('header_action')
    <a href="{{ route('subscriptions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>New Subscription</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ACTIVE MILK PLANS</span>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalActive }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">VACATION / PAUSED PLANS</span>
                <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $totalPaused }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="pause-circle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Sub Code</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Product Plan</th>
                        <th class="py-3 px-4">Qty / Day</th>
                        <th class="py-3 px-4">Frequency / Shift</th>
                        <th class="py-3 px-4">Unit Rate</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $sub->subscription_code }}</td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $sub->customer) }}" class="font-bold text-slate-900 hover:text-emerald-700">
                                    {{ $sub->customer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">{{ $sub->customer->locality }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $sub->product->name }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-sm text-slate-900">
                                {{ $sub->quantity }} {{ $sub->product->unit }}
                            </td>
                            <td class="py-3.5 px-4 capitalize font-medium text-slate-700">
                                {{ $sub->frequency }} • {{ $sub->shift }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                ₹ {{ number_format($sub->unit_price, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $sub->status }}
                                </span>
                                @if($sub->status === 'paused' && $sub->pause_from)
                                    <span class="block text-[10px] text-slate-400 mt-0.5">Resume: {{ $sub->pause_until->format('d M') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                <form action="{{ route('subscriptions.toggle-pause', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    @if($sub->status === 'active')
                                        <input type="hidden" name="pause_from" value="{{ date('Y-m-d') }}">
                                        <input type="hidden" name="pause_until" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg">
                                            Pause
                                        </button>
                                    @else
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                            Resume
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">No subscriptions created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
