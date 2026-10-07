<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\Customer;
use App\Models\AuditLog;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with(['customer', 'assignee'])->latest()->paginate(15);
        $openCount = SupportTicket::where('status', 'open')->count();
        $inProgressCount = SupportTicket::where('status', 'in_progress')->count();

        return view('support.index', compact('tickets', 'openCount', 'inProgressCount'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('support.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'category' => 'required|in:delivery,billing,milk_quality,product,general',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = SupportTicket::create(array_merge($validated, [
            'ticket_number' => 'TCK-' . date('Ym') . '-' . sprintf('%03d', SupportTicket::max('id') + 1),
            'status' => 'open',
        ]));

        AuditLog::log('Created Support Ticket', 'SupportTicket', $ticket->id);

        return redirect()->route('support.show', $ticket)->with('success', "Ticket {$ticket->ticket_number} created.");
    }

    public function show(SupportTicket $support)
    {
        $support->load(['customer', 'replies.user', 'assignee']);
        return view('support.show', ['ticket' => $support]);
    }

    public function reply(Request $request, SupportTicket $support)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        TicketReply::create([
            'support_ticket_id' => $support->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        if (!empty($validated['status'])) {
            $support->update(['status' => $validated['status']]);
        }

        return back()->with('success', 'Reply submitted.');
    }
}
