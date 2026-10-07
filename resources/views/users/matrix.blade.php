@extends('layouts.app')

@section('title', 'Role & Permission Matrix')
@section('breadcrumb', 'Permission Matrix')
@section('header_title', 'Role & Permission Authorization Matrix')

@section('header_action')
    <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
        <i data-lucide="users" class="w-3.5 h-3.5"></i>
        <span>Manage Users</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <h3 class="text-sm font-bold text-slate-900">Platform RBAC Matrix (Section 4 Specification)</h3>
        <p class="text-xs text-slate-500 mt-0.5">Granular access rights mapped across Super Admin, Dairy Admin, Branch Manager, Collection Operator, Delivery Staff, and Customers.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="py-3 px-4 min-w-[150px]">Module / Domain</th>
                        <th class="py-3 px-3 text-center">Super Admin</th>
                        <th class="py-3 px-3 text-center">Dairy Admin</th>
                        <th class="py-3 px-3 text-center">Branch / Center</th>
                        <th class="py-3 px-3 text-center">Collection Op.</th>
                        <th class="py-3 px-3 text-center">Accountant</th>
                        <th class="py-3 px-3 text-center">Delivery Mgr</th>
                        <th class="py-3 px-3 text-center">Delivery Boy</th>
                        <th class="py-3 px-3 text-center">Customer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($matrix as $module => $roles)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-bold text-slate-900 bg-slate-50/30">{{ $module }}</td>
                            
                            @foreach(['super_admin', 'dairy_admin', 'branch_manager', 'collection_operator', 'accountant', 'delivery_manager', 'delivery_boy', 'customer'] as $roleKey)
                                <td class="py-3.5 px-3 text-center">
                                    @php
                                        $val = $roles[$roleKey] ?? 'None';
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $val === 'Full' ? 'bg-emerald-100 text-emerald-800' : 
                                           ($val === 'View' ? 'bg-blue-50 text-blue-700' : 
                                           ($val === 'None' ? 'bg-slate-50 text-slate-400' : 'bg-amber-50 text-amber-800')) }}">
                                        {{ $val }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
