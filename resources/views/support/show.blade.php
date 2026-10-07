@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number)
@section('breadcrumb', 'Ticket')
@section('header_title', $ticket->ticket_number . ' - ' . $ticket->subject)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Ticket Summary Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100">
            <div>
                <span class="font-mono font-bold text-xs text-emerald-700">{{ $ticket->ticket_number }}</span>
                <h3 class="text-base font-bold text-slate-900 mt-1">{{ $ticket->subject }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Raised by <b>{{ $ticket->customer ? $ticket->customer->name : 'Staff / User' }}</b> • {{ $ticket->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                    {{ $ticket->status === 'open' ? 'bg-rose-100 text-rose-800' : ($ticket->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                    {{ str_replace('_', ' ', $ticket->status) }}
                </span>
            </div>
        </div>

        <div class="py-4 text-xs text-slate-700 leading-relaxed">
            {{ $ticket->description }}
        </div>
    </div>

    <!-- Replies Thread -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <h4 class="text-xs font-bold uppercase text-slate-400 tracking-wider">Responses & Staff Notes ({{ $ticket->replies->count() }})</h4>

        <div class="space-y-3">
            @forelse($ticket->replies as $reply)
                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50 text-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-slate-800">{{ $reply->user ? $reply->user->name : 'Staff' }}</span>
                        <span class="text-[10px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-700">{{ $reply->message }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-3 text-center">No replies on this ticket yet.</p>
            @endforelse
        </div>

        <!-- Reply Form -->
        <form action="{{ route('support.reply', $ticket) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Add Response or Internal Note</label>
                <textarea name="message" required rows="2" placeholder="Type resolution or customer note..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg"></textarea>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs">
                    <label class="font-semibold text-slate-600">Update Status:</label>
                    <select name="status" class="px-2 py-1 text-xs border border-slate-200 rounded-lg">
                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Send Reply</button>
            </div>
        </form>
    </div>

</div>
@endsection
