@extends('layouts.app')

@section('title', 'Staff Management')
@section('breadcrumb', 'Staff')
@section('header_title', 'Staff Management & Team')

@section('header_action')
    <div class="flex gap-2">
        <a href="{{ route('staff.attendance') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
            Daily Attendance
        </a>
        <a href="{{ route('staff.advances') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
            Staff Advances
        </a>
        <a href="{{ route('staff.salaries') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
            Salary List
        </a>
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
            + Add Staff Member
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TOTAL TEAM MEMBERS</span>
            <p class="text-3xl font-black text-slate-900 mt-1">{{ $totalStaff }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TODAY'S ATTENDANCE</span>
            <p class="text-3xl font-black text-emerald-600 mt-1">{{ $todayPresent }} Present</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PENDING ADVANCES</span>
            <p class="text-3xl font-black text-amber-600 mt-1">₹ {{ number_format($pendingAdvances, 2) }}</p>
        </div>
    </div>

    <!-- Staff List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Staff Members & Roles</h3>
            <a href="{{ route('users.create') }}" class="text-xs text-emerald-600 font-bold hover:underline">+ New User Account</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Staff Name</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($staffMembers as $s)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ mb_substr($s->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p>{{ $s->name }}</p>
                                        <span class="text-[11px] text-slate-400 font-normal">{{ $s->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-bold capitalize text-slate-700">
                                {{ str_replace('_', ' ', $s->role) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">{{ $s->phone ?? '—' }}</td>
                            <td class="py-3.5 px-4">{{ $s->branch->name ?? 'All Dairy' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('staff.attendance', ['user_id' => $s->id]) }}" class="text-emerald-600 font-bold hover:underline mr-3">Attendance</a>
                                <a href="{{ route('staff.advances', ['user_id' => $s->id]) }}" class="text-amber-600 font-bold hover:underline">Advance</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($staffMembers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $staffMembers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
