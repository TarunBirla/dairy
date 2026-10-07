@extends('layouts.app')

@section('title', 'My Profile')
@section('breadcrumb', 'Profile')
@section('header_title', 'User Profile & Security')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center space-x-3">
        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-base">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900">{{ $user->name }}</h3>
            <p class="text-xs text-slate-500 capitalize">{{ str_replace('_', ' ', $user->role) }} • {{ $user->email }}</p>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Display Name *</label>
            <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
            <input type="email" readonly value="{{ $user->email }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 text-slate-400">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div class="pt-2 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-900 mb-2">Change Password</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">New Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm new password" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg">
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-end border-t border-slate-100">
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">
                Save Profile Changes
            </button>
        </div>
    </form>
</div>
@endsection
