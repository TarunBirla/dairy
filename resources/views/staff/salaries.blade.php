@extends('layouts.app')

@section('title', 'Staff Salary List')
@section('breadcrumb', 'Salaries')
@section('header_title', 'Staff / Monthly Salary Disbursement')

@section('header_action')
    <form action="{{ route('staff.salaries.generate') }}" method="POST" class="inline-block">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
            Generate Salaries for {{ date('M Y', strtotime($month.'-01')) }}
        </button>
    </form>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Month Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Payroll Month: {{ date('F Y', strtotime($month.'-01')) }}</h3>
            <p class="text-xs text-slate-500">Auto-calculates days present and deducts approved advances.</p>
        </div>
        <form method="GET" action="{{ route('staff.salaries') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" class="text-xs px-3 py-1.5 border border-slate-200 rounded-xl">
            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">Filter</button>
        </form>
    </div>

    <!-- Salaries Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Base Salary</th>
                        <th class="py-3 px-4">Advance Deduction</th>
                        <th class="py-3 px-4 font-bold text-slate-900">Net Payable</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salaries as $sal)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $sal->user->name ?? 'Staff' }}</td>
                            <td class="py-3.5 px-4 capitalize text-slate-500">{{ str_replace('_', ' ', $sal->user->role ?? '') }}</td>
                            <td class="py-3.5 px-4 font-mono">₹ {{ number_format($sal->base_salary, 2) }}</td>
                            <td class="py-3.5 px-4 font-mono text-rose-600">-₹ {{ number_format($sal->advance_deduction, 2) }}</td>
                            <td class="py-3.5 px-4 font-mono font-black text-sm text-emerald-700">₹ {{ number_format($sal->net_payable, 2) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    {{ $sal->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No salary records generated for this month. Click button above to generate.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
