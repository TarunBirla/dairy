@extends('layouts.app')

@section('title', 'Create Rate Chart')
@section('breadcrumb', 'New Rate Chart')
@section('header_title', 'Rate Charts / Add Quality Formula')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Configure Milk Rate Chart</h3>
            <p class="text-xs text-slate-500">Define TS (Total Solids) / Fat & SNF rate calculation multipliers.</p>
        </div>
        <a href="{{ route('rates.index') }}" class="text-xs text-slate-500">Cancel</a>
    </div>

    <form action="{{ route('rates.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Chart Name *</label>
            <input type="text" name="name" required placeholder="e.g. Standard Cow Milk Formula Chart" value="{{ old('name') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Milk Type *</label>
                <select name="milk_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="cow">Cow Milk</option>
                    <option value="buffalo">Buffalo Milk</option>
                    <option value="mixed">Mixed Milk</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pricing Logic *</label>
                <select name="calculation_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="fat_snf_formula">Formula: (FAT &times; X) + (SNF &times; Y)</option>
                    <option value="matrix">Matrix Slabs (Grid)</option>
                    <option value="flat">Flat Base Rate</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Base Rate (₹) *</label>
                <input type="number" step="0.5" name="base_rate" required placeholder="38.00" value="38.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">FAT Factor Multiplier (₹ / Fat Unit) *</label>
                <input type="number" step="0.1" name="fat_factor" required placeholder="6.80" value="6.80" class="w-full px-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-lg">
                <span class="text-[10px] text-slate-500 mt-1 block">e.g. 4.0 Fat &times; 6.8 = ₹ 27.20</span>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">SNF Factor Multiplier (₹ / SNF Unit) *</label>
                <input type="number" step="0.1" name="snf_factor" required placeholder="4.10" value="4.10" class="w-full px-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-lg">
                <span class="text-[10px] text-slate-500 mt-1 block">e.g. 8.5 SNF &times; 4.1 = ₹ 34.85</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Min FAT %</label>
                <input type="number" step="0.1" name="min_fat" value="3.0" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max FAT %</label>
                <input type="number" step="0.1" name="max_fat" value="6.0" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Min SNF %</label>
                <input type="number" step="0.1" name="min_snf" value="8.0" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max SNF %</label>
                <input type="number" step="0.1" name="max_snf" value="10.0" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Effective Starting Date *</label>
                <input type="date" name="effective_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
            <div class="flex items-center pt-6">
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" checked class="rounded text-emerald-600">
                    <span>Set as Default Chart for this Milk Type</span>
                </label>
            </div>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('rates.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Save Rate Chart</button>
        </div>
    </form>
</div>
@endsection
