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

    public function centerInformation(Request $request)
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        $center = CollectionCenter::with('operator')->first();
        
        // Fetch users who have roles in the center or system
        $centerUsers = \App\Models\User::whereIn('role', [
            \App\Models\User::ROLE_SUPER_ADMIN,
            \App\Models\User::ROLE_DAIRY_ADMIN,
            \App\Models\User::ROLE_BRANCH_MANAGER,
            \App\Models\User::ROLE_COLLECTION_MANAGER,
            \App\Models\User::ROLE_COLLECTION_OPERATOR,
        ])->get();

        // If no center users found, fallback to active staff or users
        if ($centerUsers->isEmpty()) {
            $centerUsers = \App\Models\User::take(10)->get();
        }

        // Active tab, defaults to 'collection'
        $activeTab = $request->query('tab', 'collection');

        // Parse settings stored as JSON
        $printSetting = json_decode($settings['collection_print_settings'] ?? '{}', true);
        $bonusPenalty = json_decode($settings['bonus_penalty_settings'] ?? '{}', true);
        $smsSettings = json_decode($settings['sms_settings'] ?? '{}', true);
        $invoicePrintSetting = json_decode($settings['invoice_print_settings'] ?? '{}', true);
        $paymentRegisterSetting = json_decode($settings['payment_register_print_settings'] ?? '{}', true);

        return view('settings.center_information', compact(
            'settings',
            'center',
            'centerUsers',
            'activeTab',
            'printSetting',
            'bonusPenalty',
            'smsSettings',
            'invoicePrintSetting',
            'paymentRegisterSetting'
        ));
    }

    public function saveCenterSetting(Request $request)
    {
        try {
            $data = $request->except(['_token', 'setting_key']);
            $settingKey = $request->input('setting_key');

            if ($settingKey) {
                // Save as json or single string based on request
                if ($request->has('setting_value')) {
                    SystemSetting::set($settingKey, $request->input('setting_value'), 'collection_setting');
                } else {
                    SystemSetting::set($settingKey, json_encode($data), 'collection_setting');
                }
            } else {
                // Bulk key-value save
                foreach ($request->except('_token') as $key => $val) {
                    if (is_array($val)) {
                        SystemSetting::set($key, json_encode($val), 'collection_setting');
                    } else {
                        SystemSetting::set($key, $val, 'collection_setting');
                    }
                }
            }

            try {
                AuditLog::log('Updated Center Settings', 'SystemSetting');
            } catch (\Throwable $e) {
                \Log::warning('AuditLog warning: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully!'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error saving center setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addCenterUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'mobile' => 'required|string|max:20',
            'role' => 'required|string',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:4',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'phone' => $validated['mobile'],
            'email' => $validated['email'] ?? strtolower(str_replace(' ', '', $validated['name'])) . rand(100, 999) . '@dairy.local',
            'role' => $validated['role'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password'] ?? '123456'),
            'status' => 'active',
        ]);

        AuditLog::log("Added Center User: {$user->name}", 'User', $user->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Center user added successfully!',
                'user' => $user
            ]);
        }

        return back()->with('success', 'Center user added successfully!');
    }
}
