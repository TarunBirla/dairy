@extends('layouts.app')

@section('title', 'Add Customer')
@section('breadcrumb', 'Add Customer')
@section('header_title', 'Customers / Register New')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Add New Customer</h3>
            <p class="text-xs text-slate-500">Record customer contact, delivery route assignment, and billing rules.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name *</label>
                <input type="text" name="name" required placeholder="e.g. Sunil Mehta / Dr. Ananya Roy" value="{{ old('name') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Code *</label>
                <input type="text" name="customer_code" required value="{{ old('customer_code', $nextCode) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 text-slate-600 font-mono font-bold focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number *</label>
                <input type="text" name="phone" required placeholder="e.g. 9827011111" value="{{ old('phone') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" placeholder="e.g. customer@example.com" value="{{ old('email') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                <select name="category" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    <option value="household">Household</option>
                    <option value="shop">Retail / Sweet Shop</option>
                    <option value="hotel">Hotel / Restaurant</option>
                    <option value="institution">Office / Institution</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned Delivery Route</label>
                <select name="route_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Select Route</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->area_name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Credit Limit (₹)</label>
                <input type="number" step="100" name="credit_limit" placeholder="1000" value="{{ old('credit_limit', 1500) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Locality / Sector</label>
                <input type="text" name="locality" placeholder="e.g. Scheme 54 / Vijay Nagar" value="{{ old('locality') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Instructions</label>
                <input type="text" name="delivery_instructions" placeholder="e.g. Ring bell once, place in porch pouch" value="{{ old('delivery_instructions') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Doorstep Delivery Address *</label>
            <textarea name="address" required rows="2" placeholder="e.g. Flat 102, Royal Residency, Scheme 54" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">{{ old('address') }}</textarea>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('customers.index') }}" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Save Customer</button>
        </div>
    </form>
</div>
@endsection
