@extends('layouts.app')

@section('title', 'Edit ' . $farmer->name)
@section('breadcrumb', 'Edit Farmer')
@section('header_title', 'Farmers / Edit: ' . $farmer->name)

@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Edit Farmer Profile</h3>
            <p class="text-xs text-slate-500 mt-0.5">Update supplier details, animal classification, banking, and quality rate rules.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('farmers.show', $farmer) }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Cancel</a>
            <a href="{{ route('farmers.index') }}" class="text-xs text-emerald-600 font-bold hover:underline">All Farmers</a>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('farmers.update', $farmer) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Farmer Full Name *</label>
                <input type="text" name="name" required value="{{ old('name', $farmer->name) }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Farmer Code</label>
                <input type="text" readonly value="{{ $farmer->farmer_code }}" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-slate-50 text-slate-500 font-mono font-bold focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mobile Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $farmer->phone) }}" placeholder="e.g. 9826011111" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Village / Tehsil</label>
                <input type="text" name="village" value="{{ old('village', $farmer->village) }}" placeholder="e.g. Palasia / Green Valley" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Animal Classification *</label>
                <select name="animal_type" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white transition">
                    <option value="cow" {{ old('animal_type', $farmer->animal_type) === 'cow' ? 'selected' : '' }}>Cow Milk (गाय)</option>
                    <option value="buffalo" {{ old('animal_type', $farmer->animal_type) === 'buffalo' ? 'selected' : '' }}>Buffalo Milk (भैंस)</option>
                    <option value="mixed" {{ old('animal_type', $farmer->animal_type) === 'mixed' ? 'selected' : '' }}>Mixed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Collection Center</label>
                <select name="collection_center_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white transition">
                    <option value="">Main Center</option>
                    @foreach($centers as $c)
                        <option value="{{ $c->id }}" {{ old('collection_center_id', $farmer->collection_center_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rate Chart Applied</label>
                <select name="rate_chart_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white transition">
                    <option value="">Default Quality Chart</option>
                    @foreach($rateCharts as $rc)
                        <option value="{{ $rc->id }}" {{ old('rate_chart_id', $farmer->rate_chart_id) == $rc->id ? 'selected' : '' }}>{{ $rc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Branch</label>
                <select name="branch_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white transition">
                    <option value="">All Dairy / Main Branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ old('branch_id', $farmer->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Farmer Account Status *</label>
                <select name="status" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none bg-white transition">
                    <option value="active" {{ old('status', $farmer->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $farmer->status) === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                    <option value="blocked" {{ old('status', $farmer->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
        </div>

        <div class="p-4 sm:p-5 bg-slate-50/70 rounded-2xl border border-slate-200/80 space-y-3.5">
            <h4 class="text-xs font-bold text-slate-800">Settlement Bank & UPI Details</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $farmer->bank_name) }}" placeholder="e.g. State Bank of India" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $farmer->account_number) }}" placeholder="e.g. 30891283712" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $farmer->ifsc_code) }}" placeholder="e.g. SBIN0001234" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-mono uppercase">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">UPI ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $farmer->upi_id) }}" placeholder="e.g. 9826011111@upi" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Postal Address</label>
            <textarea name="address" rows="2" placeholder="Full residential village address" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">{{ old('address', $farmer->address) }}</textarea>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <a href="{{ route('farmers.show', $farmer) }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Back</a>
            <div class="flex gap-2">
                <a href="{{ route('farmers.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Update Farmer Profile</button>
            </div>
        </div>
    </form>
</div>
@endsection
