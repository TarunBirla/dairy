@extends('layouts.app')

@section('title', 'Schedule a Product')
@section('breadcrumb', 'New Subscription')
@section('header_title', 'Subscriptions / Schedule a Product')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-xs" x-data="{ 
    selectedProductPrice: 50,
    stopOnDate: false,
    frequency: 'daily',
    weekDays: ['mon', 'tue', 'wed', 'thu', 'fri'],
    toggleWeekDay(day) {
        if (this.weekDays.includes(day)) {
            if (this.weekDays.length > 1) {
                this.weekDays = this.weekDays.filter(d => d !== day);
            }
        } else {
            this.weekDays.push(day);
        }
    }
}">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-black text-slate-900">Schedule a product</h3>
            <p class="text-xs text-slate-500 mt-0.5">Pick a product, the shift, quantity and when deliveries should start and repeat.</p>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('subscriptions.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer *</label>
            <select name="customer_id" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
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
                <select name="product_id" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (₹{{ $p->subscription_price ?? $p->price }}/{{ $p->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subscription Code *</label>
                <input type="text" name="subscription_code" value="{{ old('subscription_code', $nextCode) }}" readonly class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-600 font-mono font-bold">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity Per Delivery *</label>
                <input type="number" step="0.25" min="0.25" name="quantity" required placeholder="1.0" value="1.0" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Shift / Slot *</label>
                <select name="shift" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    <option value="morning">Morning Delivery</option>
                    <option value="evening">Evening Delivery</option>
                    <option value="both">Both (Morning & Evening)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price Charged (₹) *</label>
                <input type="number" step="0.5" name="unit_price" required placeholder="50.00" value="50.00" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-bold text-slate-800">
            </div>
        </div>

        <!-- Dates Row matching Simple Dairy (Start Date, End Date, Toggle) -->
        <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Start date *</label>
                    <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" :class="!stopOnDate ? 'text-slate-400' : 'text-slate-700'">End date</label>
                    <input type="date" name="end_date" :disabled="!stopOnDate" placeholder="DD/MM/YYYY" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none font-medium" :class="!stopOnDate ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-white text-slate-800'">
                </div>
            </div>

            <!-- Stop delivery on a specific date toggle switch matching Simple Dairy -->
            <div class="flex items-center justify-between pt-1">
                <div>
                    <h5 class="text-xs font-bold text-slate-800">Stop delivery on a specific date</h5>
                    <p class="text-[11px] text-slate-400 mt-0.5">Enable this to pick an end date for the schedule.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="stop_on_date" value="1" x-model="stopOnDate" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>
        </div>

        <!-- Repeat Selector matching Simple Dairy Images 3 & 4 -->
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Repeat *</label>
                <select name="frequency" x-model="frequency" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                    <option value="daily">Daily</option>
                    <option value="alternate_day">Alternate Days</option>
                    <option value="every_3rd_day">Every 3rd Days</option>
                    <option value="weekdays">Week Days</option>
                </select>
            </div>

            <!-- Choose Week Days Pills Selector matching Simple Dairy Image 3 -->
            <div x-show="frequency === 'weekdays'" x-cloak class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-2.5">
                <label class="block text-xs font-bold text-slate-700">Choose week days *</label>
                <div class="flex flex-wrap items-center gap-2">
                    <template x-for="day in [
                        { key: 'sun', label: 'Sun' },
                        { key: 'mon', label: 'Mon' },
                        { key: 'tue', label: 'Tue' },
                        { key: 'wed', label: 'Wed' },
                        { key: 'thu', label: 'Thu' },
                        { key: 'fri', label: 'Fri' },
                        { key: 'sat', label: 'Sat' }
                    ]" :key="day.key">
                        <button type="button" 
                                @click="toggleWeekDay(day.key)"
                                class="px-4 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                                :class="weekDays.includes(day.key) ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-2xs ring-1 ring-emerald-500/20' : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'">
                            <span x-text="day.label"></span>
                        </button>
                    </template>
                </div>
                <template x-for="d in weekDays" :key="d">
                    <input type="hidden" name="custom_days[]" :value="d">
                </template>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Route (Optional)</label>
            <select name="route_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white font-medium">
                <option value="">Auto from Customer Profile</option>
                @foreach($routes as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->area_name }})</option>
                @endforeach
            </select>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('subscriptions.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">Save</button>
        </div>
    </form>
</div>
@endsection
@endsection
