@extends('layouts.app')

@section('title', 'Product Booking')
@section('breadcrumb', 'Bookings')
@section('header_title', 'Advance Product Orders & Bookings')

@section('header_action')
    <button type="button" onclick="document.getElementById('bkgModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>+ New Product Booking</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL PRE-ORDERS BOOKED</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ $totalBookings }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">CONFIRMED PENDING DISPATCH</span>
            <p class="text-3xl font-black text-emerald-600 mt-1">{{ $confirmedCount }} Orders</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Booking #</th>
                        <th class="py-3 px-4">Delivery Date</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Product Booked</th>
                        <th class="py-3 px-4">Quantity</th>
                        <th class="py-3 px-4 text-right">Total Amount</th>
                        <th class="py-3 px-4 text-right">Advance Paid</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $b->booking_number }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800">{{ $b->delivery_date->format('d M Y') }}</span>
                                <span class="capitalize text-[11px] text-slate-400 block">{{ $b->shift }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-900">{{ $b->customer_name }}</p>
                                <span class="font-mono text-[11px] text-slate-400">{{ $b->customer_phone }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $b->product->name }}</td>
                            <td class="py-3.5 px-4 font-black">{{ $b->quantity }} {{ $b->product->unit }}</td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900">₹ {{ number_format($b->total_amount, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-emerald-600">₹ {{ number_format($b->advance_paid, 2) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    {{ $b->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No product bookings recorded yet. Click above to take advance festival/bulk orders (e.g. 50kg Paneer, 100L Ghee).</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="bkgModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Take Advance Product Booking</h3>
            <form action="{{ route('bookings.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" required placeholder="e.g. Verma Sweets / Amit" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="customer_phone" required placeholder="9876543210" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product *</label>
                    <select name="product_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                        @foreach($products as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->name }} (Retail: ₹{{ $pr->price }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                        <input type="number" step="0.5" name="quantity" required placeholder="10" class="w-full px-2 py-2 text-xs border border-slate-200 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Price / Unit *</label>
                        <input type="number" step="0.5" name="unit_price" required placeholder="320" class="w-full px-2 py-2 text-xs border border-slate-200 rounded-xl font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Advance (₹)</label>
                        <input type="number" step="50" name="advance_paid" placeholder="500" class="w-full px-2 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Date *</label>
                        <input type="date" name="delivery_date" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Shift</label>
                        <select name="shift" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                            <option value="morning">Morning</option>
                            <option value="evening">Evening</option>
                            <option value="any">Any Time</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Address / Notes</label>
                    <input type="text" name="delivery_address" placeholder="e.g. Shop 42, Main Market" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('bkgModal').classList.add('hidden')" class="px-3.5 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
