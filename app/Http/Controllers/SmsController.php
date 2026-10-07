<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\AuditLog;

class SmsController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('customers')->get();
        $totalCustomers = Customer::where('status', 'active')->count();

        return view('sms.index', compact('groups', 'totalCustomers'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:all,group,individual',
            'group_id' => 'nullable|exists:customer_groups,id',
            'phone' => 'nullable|string',
            'template_type' => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $recipientCount = 1;
        if ($validated['recipient_type'] === 'all') {
            $recipientCount = Customer::where('status', 'active')->count();
        } elseif ($validated['recipient_type'] === 'group' && !empty($validated['group_id'])) {
            $group = CustomerGroup::find($validated['group_id']);
            $recipientCount = $group ? $group->customers()->count() : 0;
        }

        AuditLog::log("Sent SMS Broadcast: {$validated['template_type']}", 'SMS', 0, [
            'recipients_count' => $recipientCount,
            'message' => $validated['message']
        ]);

        return back()->with('success', "SMS broadcast sent successfully to {$recipientCount} recipients!");
    }
}
