@extends('layouts.app')

@section('title', 'New Milk Subscription')
@section('breadcrumb', 'New Subscription')
@section('header_title', 'Subscriptions / Create Plan')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" x-data="{ selectedProductPrice: 50 }">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Create Recurring Milk Subscription</h3>
            <p class="text-xs text-slate-500">Assign product, daily quantity, delivery round, and starting date.</p>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('subscriptions.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
            <select name="customer_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Select Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->customer_code }} - {{ $c->name }} ({{ $c->locality }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Product Master *</label>
                <select name="product_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (₹{{ $p->subscription_price ?? $p->price }}/{{ $p->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subscription Code *</label>
                <input type="text" name="subscription_code" value="{{ old('subscription_code', $nextCode) }}" readonly class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 text-slate-600 font-mono font-bold">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity Per Delivery *</label>
                <input type="number" step="0.5" name="quantity" required placeholder="1.0" value="1.0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Frequency *</label>
                <select name="frequency" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="daily">Daily (Everyday)</option>
                    <option value="alternate_day">Alternate Days</option>
                    <option value="custom_days">Weekdays Only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift / Slot *</label>
                <select name="shift" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="morning">Morning Delivery</option>
                    <option value="evening">Evening Delivery</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price Charged (₹) *</label>
                <input type="number" step="0.5" name="unit_price" required placeholder="48.00" value="50.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Route</label>
                <select name="route_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="">Auto from Customer Profile</option>
                    @foreach($routes as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->area_name }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date *</label>
                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">End Date (Optional)</label>
                <input type="date" name="end_date" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('subscriptions.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Activate Subscription</button>
        </div>
    </form>
</div>
@endsection
