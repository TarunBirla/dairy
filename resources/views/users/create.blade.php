@extends('layouts.app')

@section('title', 'Add Staff User')
@section('breadcrumb', 'Add User')
@section('header_title', 'Users / Create Staff Member')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-slate-900">Create Staff User</h3>
            <p class="text-xs text-slate-500">Assign role credentials and branch access.</p>
        </div>
        <a href="{{ route('users.index') }}" class="text-xs text-slate-500">Cancel</a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
            <input type="text" name="name" required placeholder="e.g. Rahul Sharma" value="{{ old('name') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
                <input type="email" name="email" required placeholder="staff@simpledairy.com" value="{{ old('email') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone</label>
                <input type="text" name="phone" placeholder="9876543210" value="{{ old('phone') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Role Assigned *</label>
                <select name="role" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="super_admin">Super Admin</option>
                    <option value="dairy_admin">Dairy Owner / Admin</option>
                    <option value="branch_manager">Branch Manager</option>
                    <option value="collection_operator">Collection Operator</option>
                    <option value="delivery_boy">Delivery Boy</option>
                    <option value="sales_operator">POS / Counter Sales</option>
                    <option value="accountant">Accountant</option>
                    <option value="inventory_manager">Inventory Manager</option>
                    <option value="support_staff">Support Staff</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
            <input type="password" name="password" required value="password" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-mono">
            <span class="text-[10px] text-slate-400 mt-0.5 block">Default placeholder password is 'password'.</span>
        </div>

        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <a href="{{ route('users.index') }}" class="px-4 py-2 text-xs text-slate-600">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">Create User</button>
        </div>
    </form>
</div>
@endsection
