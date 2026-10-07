<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\Branch;
use App\Models\CollectionCenter;
use App\Models\AuditLog;
use App\Models\Notification;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        $branches = Branch::with('manager')->get();
        $centers = CollectionCenter::with('operator')->get();
        $notifications = Notification::latest()->take(20)->get();

        return view('settings.index', compact('settings', 'branches', 'centers', 'notifications'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditLog::log('Updated System Settings', 'SystemSetting');
        return back()->with('success', 'Platform settings saved successfully.');
    }

    public function testNotification(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'channel' => 'required|in:sms,whatsapp',
            'message' => 'required|string',
        ]);

        Notification::create([
            'title' => 'Test Notification',
            'message' => $validated['message'],
            'channel' => $validated['channel'],
            'recipient_phone' => $validated['phone'],
            'status' => 'sent',
        ]);

        return back()->with('success', "Simulated {$validated['channel']} notification queued and sent to {$validated['phone']}!");
    }
}
