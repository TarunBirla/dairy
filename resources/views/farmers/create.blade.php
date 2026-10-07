@extends('layouts.app')

@section('title', 'Register Farmer')
@section('breadcrumb', 'Register Farmer')
@section('header_title', 'Farmers / New Registration')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Farmer Registration</h3>
            <p class="text-xs text-slate-500">Capture supplier code, animal classification, banking, and quality rate rules.</p>
        </div>
        <a href="{{ route('farmers.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('farmers.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Farmer Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Ramesh Patel" value="{{ old('name') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Farmer Code *</label>
                <input type="text" name="farmer_code" required value="{{ old('farmer_code', $nextCode) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 text-slate-600 font-mono font-bold focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number</label>
                <input type="text" name="phone" placeholder="e.g. 9826011111" value="{{ old('phone') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Village / Tehsil</label>
                <input type="text" name="village" placeholder="e.g. Palasia / Green Valley" value="{{ old('village') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Animal Classification *</label>
                <select name="animal_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    <option value="cow">Cow Milk (गाय)</option>
                    <option value="buffalo">Buffalo Milk (भैंस)</option>
                    <option value="mixed">Mixed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Collection Center</label>
                <select name="collection_center_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    @foreach($centers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Rate Chart Applied</label>
                <select name="rate_chart_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Default Quality Chart</option>
                    @foreach($rateCharts as $rc)
                        <option value="{{ $rc->id }}">{{ $rc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
            <h4 class="text-xs font-bold text-slate-800">Settlement Bank & UPI Details</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" placeholder="e.g. State Bank of India" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Account Number</label>
                    <input type="text" name="account_number" placeholder="e.g. 30891283712" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">IFSC Code</label>
                    <input type="text" name="ifsc_code" placeholder="e.g. SBIN0001234" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">UPI ID</label>
                    <input type="text" name="upi_id" placeholder="e.g. 9826011111@upi" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg bg-white">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Postal Address</label>
            <textarea name="address" rows="2" placeholder="Full residential village address" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('farmers.index') }}" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Register Farmer</button>
        </div>
    </form>
</div>
@endsection
