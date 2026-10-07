@extends('layouts.app')

@section('title', 'Generate Monthly Invoice')
@section('breadcrumb', 'Generate Invoice')
@section('header_title', 'Invoices / Auto Generate Bill')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Generate Customer Delivery Bill</h3>
            <p class="text-xs text-slate-500">Calculates all completed milk and product deliveries for the period and creates an invoice.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Cancel</a>
    </div>

    <form action="{{ route('invoices.generate') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Select Customer *</label>
            <select name="customer_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                <option value="">-- Choose Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->customer_code }} - {{ $c->name }} ({{ $c->locality }} • Balance: ₹{{ number_format($c->current_balance, 2) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period Start *</label>
                <input type="date" name="period_start" required value="{{ $start }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period End *</label>
                <input type="date" name="period_end" required value="{{ $end }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Due Date *</label>
            <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+10 days')) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">
                Calculate & Generate Invoice
            </button>
        </div>
    </form>
</div>
@endsection
