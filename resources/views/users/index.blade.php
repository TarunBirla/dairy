@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumb', 'Users')
@section('header_title', 'Staff Accounts & Roles')

@section('header_action')
    <div class="flex gap-2">
        <a href="{{ route('users.matrix') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Permission Matrix</span>
        </a>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add User</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Contact</th>
                        <th class="py-3 px-4">System Role</th>
                        <th class="py-3 px-4">Branch</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Quick Test Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 leading-tight">{{ $u->name }}</p>
                                        <span class="text-[11px] text-slate-400">{{ $u->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-mono">{{ $u->phone ?? '—' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role_badge_class }}">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">{{ $u->branch ? $u->branch->name : 'All Dairy' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $u->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6">
                                <a href="{{ route('login.demo', $u) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg">
                                    <span>Switch As User</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
