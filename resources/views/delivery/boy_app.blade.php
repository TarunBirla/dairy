@extends('layouts.app')

@section('title', 'Delivery Boy App')
@section('breadcrumb', 'Delivery App')
@section('header_title', 'Doorstep Delivery Operations')

@section('content')
<div class="max-w-xl mx-auto space-y-4" x-data="{ skipModal: false, activeDelivery: null, cashModal: false }">

    <!-- Mobile Header Card -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-200">Delivery Staff Portal</span>
                <h2 class="text-xl font-black mt-0.5">{{ $user->name }}</h2>
                <p class="text-xs text-emerald-100 mt-0.5">{{ date('l, d F Y') }} • {{ ucfirst($shift) }} Run</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center font-bold text-lg">
                <i data-lucide="bike" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Mini Stats -->
        <div class="grid grid-cols-4 gap-2 mt-5 pt-4 border-t border-white/20 text-center text-xs">
            <div>
                <span class="text-[10px] text-emerald-200 block uppercase">Total</span>
                <span class="text-lg font-bold">{{ $stats['total'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-emerald-200 block uppercase">Done</span>
                <span class="text-lg font-bold text-emerald-300">{{ $stats['delivered'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-emerald-200 block uppercase">Pending</span>
                <span class="text-lg font-bold text-amber-300">{{ $stats['pending'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-emerald-200 block uppercase">Cash ₹</span>
                <span class="text-lg font-bold">{{ number_format($stats['cash_total'], 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Stops List -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Assigned Delivery Stops</h3>
            <span class="text-xs text-slate-400">{{ $deliveries->where('status', 'pending')->count() }} Remaining</span>
        </div>

        @forelse($deliveries as $del)
            <div class="bg-white p-4 rounded-2xl border {{ $del->status === 'delivered' ? 'border-emerald-200 bg-emerald-50/20' : ($del->status === 'skipped' ? 'border-slate-200 opacity-60' : 'border-slate-200 shadow-xs') }} transition">
                
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold flex items-center justify-center">
                                {{ $loop->iteration }}
                            </span>
                            <h4 class="text-sm font-bold text-slate-900">{{ $del->customer->name }}</h4>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase 
                                {{ $del->status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : ($del->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                {{ $del->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>{{ $del->customer->address }}</span>
                        </p>
                        @if($del->customer->delivery_instructions)
                            <p class="text-[11px] text-amber-700 bg-amber-50 p-1.5 rounded-lg mt-2 flex items-center gap-1 font-medium">
                                <i data-lucide="info" class="w-3 h-3"></i>
                                <span>Note: {{ $del->customer->delivery_instructions }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Call Button -->
                    @if($del->customer->phone)
                        <a href="tel:{{ $del->customer->phone }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 flex items-center justify-center transition">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Product Badge -->
                <div class="mt-3 py-2 px-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800">
                        {{ $del->quantity }} {{ $del->product ? $del->product->unit : 'Ltr' }} - {{ $del->product ? $del->product->name : 'Milk' }}
                    </span>
                    <span class="font-bold text-emerald-700">₹ {{ number_format($del->total_amount, 2) }}</span>
                </div>

                <!-- Action Buttons -->
                @if($del->status === 'pending')
                    <div class="mt-3 grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                        <!-- Mark Skipped -->
                        <button 
                            type="button" 
                            @click="activeDelivery = {{ $del->id }}; skipModal = true;"
                            class="py-2 px-3 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center justify-center gap-1"
                        >
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Skip Stop</span>
                        </button>

                        <!-- Mark Delivered -->
                        <form action="{{ route('delivery.update-status', $del) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="delivered">
                            <button 
                                type="submit" 
                                class="w-full py-2 px-3 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center justify-center gap-1"
                            >
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Delivered</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-2 text-[11px] text-slate-400 text-right">
                        @if($del->status === 'delivered')
                            Delivered at {{ $del->delivered_at ? $del->delivered_at->format('h:i A') : 'earlier today' }}
                            @if($del->cash_collected > 0)
                                • Cash Collected: <b>₹{{ $del->cash_collected }}</b>
                            @endif
                        @elseif($del->failure_reason)
                            Reason: {{ $del->failure_reason }}
                        @endif
                    </div>
                @endif

            </div>
        @empty
            <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400">
                <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-2 text-emerald-400"></i>
                <p class="text-sm font-bold text-slate-800">No pending deliveries right now</p>
                <p class="text-xs text-slate-400 mt-1">All stops for this round are complete or no schedule generated.</p>
            </div>
        @endforelse
    </div>

    <!-- Skip Reason Modal -->
    <div x-show="skipModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="skipModal = false" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 mb-2">Select Reason for Skipping</h3>
            <form :action="'{{ url('delivery/update-status') }}/' + activeDelivery" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="status" value="skipped">
                <div>
                    <select name="failure_reason" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                        <option value="Customer Door Locked / Unavailable">Door Locked / Customer Unavailable</option>
                        <option value="Customer on Leave / Vacation">Customer on Vacation / Requested Skip</option>
                        <option value="Incorrect Address">Address Not Found</option>
                        <option value="Out of Stock">Product Shortage</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="skipModal = false" class="px-3 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg">Confirm Skip</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
