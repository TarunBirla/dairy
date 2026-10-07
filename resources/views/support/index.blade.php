@extends('layouts.app')

@section('title', 'Support Tickets')
@section('breadcrumb', 'Support')
@section('header_title', 'Customer & Farmer Support Tickets')

@section('header_action')
    <a href="{{ route('support.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Raise New Ticket</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">OPEN TICKETS</span>
            <p class="text-3xl font-black text-rose-600 mt-1">{{ $openCount }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase">IN PROGRESS / INVESTIGATING</span>
            <p class="text-3xl font-black text-amber-600 mt-1">{{ $inProgressCount }}</p>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Ticket #</th>
                        <th class="py-3 px-4">Customer / Farmer</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $t)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ $t->ticket_number }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $t->customer ? $t->customer->name : ($t->farmer ? $t->farmer->name : 'General User') }}
                            </td>
                            <td class="py-3.5 px-4 capitalize font-medium text-slate-600">{{ $t->category }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $t->subject }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $t->priority === 'urgent' ? 'bg-rose-100 text-rose-800' : ($t->priority === 'high' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $t->priority }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $t->status === 'open' ? 'bg-rose-100 text-rose-800' : ($t->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ str_replace('_', ' ', $t->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('support.show', $t) }}" class="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg">
                                    View Thread
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">No support tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
