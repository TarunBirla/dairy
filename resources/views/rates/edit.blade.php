@extends('layouts.app')

@section('title', 'Edit ' . $chart->name)
@section('breadcrumb', 'Edit Rate Chart')
@section('header_title', 'Rate Charts / ' . $chart->name)

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Edit Rate Chart: {{ $chart->name }}</h3>
            <p class="text-xs text-slate-500">Update formula rules and quality parameters.</p>
        </div>
        <a href="{{ route('rates.index') }}" class="text-xs text-slate-500">Back</a>
    </div>

    <form action="{{ route('rates.update', $chart) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Chart Name *</label>
            <input type="text" name="name" required value="{{ old('name', $chart->name) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Milk Type *</label>
                <select name="milk_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="cow" {{ $chart->milk_type === 'cow' ? 'selected' : '' }}>Cow Milk</option>
                    <option value="buffalo" {{ $chart->milk_type === 'buffalo' ? 'selected' : '' }}>Buffalo Milk</option>
                    <option value="mixed" {{ $chart->milk_type === 'mixed' ? 'selected' : '' }}>Mixed Milk</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pricing Logic *</label>
                <select name="calculation_type" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="fat_snf_formula" {{ $chart->calculation_type === 'fat_snf_formula' ? 'selected' : '' }}>Formula: (FAT &times; X) + (SNF &times; Y)</option>
                    <option value="matrix" {{ $chart->calculation_type === 'matrix' ? 'selected' : '' }}>Matrix Slabs (Grid)</option>
                    <option value="flat" {{ $chart->calculation_type === 'flat' ? 'selected' : '' }}>Flat Base Rate</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Base Rate (₹) *</label>
                <input type="number" step="0.5" name="base_rate" required value="{{ old('base_rate', $chart->base_rate) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-bold">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">FAT Factor Multiplier (₹) *</label>
                <input type="number" step="0.1" name="fat_factor" required value="{{ old('fat_factor', $chart->fat_factor) }}" class="w-full px-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">SNF Factor Multiplier (₹) *</label>
                <input type="number" step="0.1" name="snf_factor" required value="{{ old('snf_factor', $chart->snf_factor) }}" class="w-full px-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Min FAT %</label>
                <input type="number" step="0.1" name="min_fat" value="{{ old('min_fat', $chart->min_fat) }}" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max FAT %</label>
                <input type="number" step="0.1" name="max_fat" value="{{ old('max_fat', $chart->max_fat) }}" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Min SNF %</label>
                <input type="number" step="0.1" name="min_snf" value="{{ old('min_snf', $chart->min_snf) }}" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Max SNF %</label>
                <input type="number" step="0.1" name="max_snf" value="{{ old('max_snf', $chart->max_snf) }}" required class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Effective Date *</label>
                <input type="date" name="effective_date" value="{{ old('effective_date', $chart->effective_date ? $chart->effective_date->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                <select name="status" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="active" {{ $chart->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $chart->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('rates.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Update Chart</button>
        </div>
    </form>
</div>
@endsection
