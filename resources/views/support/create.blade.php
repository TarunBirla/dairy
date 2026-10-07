@extends('layouts.app')

@section('title', 'Raise Ticket')
@section('breadcrumb', 'New Ticket')
@section('header_title', 'Support / Raise Issue')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Raise Support Ticket</h3>
            <p class="text-xs text-slate-500">Record customer complaint, delivery discrepancy, or billing issue.</p>
        </div>
        <a href="{{ route('support.index') }}" class="text-xs text-slate-500">Cancel</a>
    </div>

    <form action="{{ route('support.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Customer (Optional)</label>
            <select name="customer_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                <option value="">General Issue</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->customer_code }})</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                <select name="category" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="delivery">Delivery Timing / Missed Delivery</option>
                    <option value="billing">Billing & Payment Issue</option>
                    <option value="milk_quality">Milk Freshness / Quality</option>
                    <option value="product">Product Packaging</option>
                    <option value="general">Other General Enquiry</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Priority *</label>
                <select name="priority" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Subject *</label>
            <input type="text" name="subject" required placeholder="e.g. Morning delivery arrived 30 mins late" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Detailed Description *</label>
            <textarea name="description" required rows="3" placeholder="Provide complete context..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg"></textarea>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('support.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Submit Ticket</button>
        </div>
    </form>
</div>
@endsection
