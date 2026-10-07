@extends('layouts.app')

@section('title', 'Audit Trail')
@section('breadcrumb', 'Audit Logs')
@section('header_title', 'Security & Operational Audit Trail')

@section('content')
<div class="space-y-6">

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">System Activity & Audit Log (Section 26 Specification)</h3>
            <p class="text-xs text-slate-500">Trace critical modifications: milk collections, rate overrides, payments, and master record edits.</p>
        </div>
        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-lg border border-emerald-200">
            Audit Protection Active
        </span>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Target Entity</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-mono">{{ $log->created_at->format('d M Y, h:i:s A') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $log->user ? $log->user->name : 'System / Guest' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-emerald-700">{{ $log->action }}</td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-700">
                                {{ $log->model_type ? $log->model_type . ' #' . $log->model_id : '—' }}
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-mono">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[10px]">
                                {{ $log->payload ? json_encode($log->payload) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">No audit events logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
