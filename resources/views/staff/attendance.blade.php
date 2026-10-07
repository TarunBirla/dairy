@extends('layouts.app')

@section('title', 'Staff Attendance')
@section('breadcrumb', 'Attendance')
@section('header_title', 'Staff / Daily Attendance')

@section('header_action')
    <a href="{{ route('staff.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
        &larr; Back to Staff
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Date Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Attendance Date: {{ date('d M Y', strtotime($date)) }}</h3>
            <p class="text-xs text-slate-500">Mark Present, Absent, Half-Day, or Approved Leave for all dairy staff.</p>
        </div>
        <form method="GET" action="{{ route('staff.attendance') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date }}" class="text-xs px-3 py-1.5 border border-slate-200 rounded-xl">
            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">Filter</button>
        </form>
    </div>

    <!-- Attendance Form -->
    <form action="{{ route('staff.attendance.save') }}" method="POST">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-3 px-4">Staff Member</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4 text-center">Status Selection</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staff as $s)
                            @php
                                $att = $attendances->get($s->id);
                                $currentStatus = $att ? $att->status : 'present';
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $s->name }}
                                </td>
                                <td class="py-3.5 px-4 capitalize text-slate-500">
                                    {{ str_replace('_', ' ', $s->role) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-center gap-4">
                                        <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                            <input type="radio" name="status[{{ $s->id }}]" value="present" {{ $currentStatus === 'present' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-emerald-700">Present</span>
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                            <input type="radio" name="status[{{ $s->id }}]" value="half_day" {{ $currentStatus === 'half_day' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                                            <span class="text-amber-700">Half Day</span>
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs font-semibold cursor-pointer">
                                            <input type="radio" name="status[{{ $s->id }}]" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500">
                                            <span class="text-rose-700">Absent</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Save Attendance
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
