@extends('layouts.app')

@section('title', 'Milk Dispatched')
@section('breadcrumb', 'Dispatch')
@section('header_title', 'Delivery / Milk Outward & Dispatched')

@section('header_action')
    <button type="button" onclick="document.getElementById('dspModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
        <i data-lucide="truck" class="w-4 h-4"></i>
        <span>+ New Milk Dispatch</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Metrics -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase">Today's Total Milk Dispatched</span>
            <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($todayDispatched, 1) }} Liters</p>
        </div>
        <button type="button" onclick="document.getElementById('dspModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs">
            + Dispatch Vehicle / Van
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Dispatch #</th>
                        <th class="py-3 px-4">Date / Shift</th>
                        <th class="py-3 px-4">Route & Staff</th>
                        <th class="py-3 px-4">Vehicle</th>
                        <th class="py-3 px-4">Fat / SNF</th>
                        <th class="py-3 px-4">Crates / Bottles</th>
                        <th class="py-3 px-4 font-bold text-slate-900">Total Milk</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dispatches as $d)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $d->dispatch_number }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800">{{ $d->dispatch_date->format('d M') }}</span>
                                <span class="capitalize text-[11px] text-slate-400 block">{{ $d->shift }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-800">{{ $d->route->name ?? 'Route' }}</p>
                                <span class="text-[11px] text-slate-500">{{ $d->deliveryBoy->name ?? 'Unassigned' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">{{ $d->vehicle_number ?? '—' }}</td>
                            <td class="py-3.5 px-4 text-slate-700">
                                Fat: <b>{{ $d->fat ?? '—' }}%</b> &bull; SNF: <b>{{ $d->snf ?? '—' }}%</b>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $d->crates_loaded }} Crates ({{ $d->bottles_loaded }} Bottles)
                            </td>
                            <td class="py-3.5 px-4 font-mono font-black text-sm text-emerald-700">
                                {{ $d->total_milk_quantity }} Ltr
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                    {{ $d->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">No milk dispatches recorded. Click above to record morning/evening dispatch.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="dspModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Record Milk Dispatch</h3>
            <form action="{{ route('dispatch.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date *</label>
                        <input type="date" name="dispatch_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Shift *</label>
                        <select name="shift" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                            <option value="morning">Morning</option>
                            <option value="evening">Evening</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Route *</label>
                        <select name="route_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                            @foreach($routes as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Person</label>
                        <select name="delivery_boy_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                            <option value="">Unassigned</option>
                            @foreach($deliveryBoys as $db)
                                <option value="{{ $db->id }}">{{ $db->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Total Milk (Ltr) *</label>
                        <input type="number" step="0.5" name="total_milk_quantity" required placeholder="e.g. 150" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Vehicle / Van No</label>
                        <input type="text" name="vehicle_number" placeholder="MP-09-AB-1234" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Fat %</label>
                        <input type="number" step="0.1" name="fat" placeholder="4.5" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">SNF %</label>
                        <input type="number" step="0.1" name="snf" placeholder="8.5" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bottles</label>
                        <input type="number" name="bottles_loaded" placeholder="100" class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('dspModal').classList.add('hidden')" class="px-3.5 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl">Dispatch Milk</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
