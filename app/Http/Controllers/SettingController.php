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

        // Center Details (Mobile Dairy Parity)
        $centerDetailsRaw = json_decode($settings['center_details'] ?? '{}', true) ?: [];
        $centerDetails = array_merge([
            'unique_id' => $center->code ?? 'JC4217',
            'name_mother_tongue' => 'श्री गोपाल दूध डेयरी बेड़िया',
            'name_english' => $center->name ?? 'Shree Gopal Dudh Dairy Bediya',
            'village' => 'Bediya',
            'taluka' => 'Bediya',
            'district' => 'khargone',
            'state' => 'Madhya Pradesh',
            'status' => 'Active',
            'address_mother_tongue' => 'Bediya',
            'address_english' => 'Bediya, Bediya, Madhya Pradesh',
            'chilling_center' => '-',
            'vehicle_route' => '-',
            'block' => '-',
        ], $centerDetailsRaw);

        $billingInfoRaw = json_decode($settings['center_billing_info'] ?? '{}', true) ?: [];
        $billingInfo = array_merge([
            'name' => 'SHREE GOPAL DUDH DAIRY',
            'address' => '265, BHAIJI BHAWAN, BUS STAND, BEDIYA, Khargone,Madhya Pradesh, 451113',
            'pincode' => '451113',
            'phone' => '9753672436',
            'email' => 'drk.malviya@gmail.com',
            'gstin' => '23BAZPN5618R1ZN',
        ], $billingInfoRaw);

        $subscriptionInfoRaw = json_decode($settings['center_subscription_info'] ?? '{}', true) ?: [];
        $subscriptionInfo = array_merge([
            'plan' => 'Advance Plan - One Year - 3200',
            'period' => '05 Apr 2026 To Apr 5, 2027',
            'status' => 'Active',
        ], $subscriptionInfoRaw);

        $locationInfoRaw = json_decode($settings['center_location_info'] ?? '{}', true) ?: [];
        $locationInfo = array_merge([
            'latitude' => '22.04613211165056',
            'longitude' => '76.03453326970339',
        ], $locationInfoRaw);

        $additionTypes = json_decode($settings['center_addition_types'] ?? '[]', true);
        if (empty($additionTypes)) {
            $additionTypes = [
                ['name' => 'Incentive', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => '/ltr'],
                ['name' => 'Incentive Day', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-'],
                ['name' => 'Subsidy', 'value' => '1.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '1.00', 'unit' => '/ltr'],
                ['name' => 'Transport', 'value' => '1.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '1.00', 'unit' => '/ltr'],
                ['name' => 'Other Payment Cr', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-'],
                ['name' => 'Commission', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr'],
            ];
        }

        $deductionTypes = json_decode($settings['center_deduction_types'] ?? '[]', true);
        if (empty($deductionTypes)) {
            $deductionTypes = [
                ['name' => 'Anamat', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Food', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Grocery', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Etc', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Building fund', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Grant', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Transport-Dr', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Management Charges C', 'value' => '0.00/Invoice', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/Invoice', 'auto_reset' => false],
                ['name' => 'DPMCU', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Penalty', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Doctor', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Contribution', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Diesel Expenses', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Stationary', 'value' => 'Farmer Wise', 'farmer_type' => 'farmer_wise', 'amount' => '0', 'unit' => 'Farmer Wise', 'auto_reset' => false],
                ['name' => 'Handling', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'Management Charges', 'value' => '0.00/ltr', 'farmer_type' => 'all_farmers', 'amount' => '0.00', 'unit' => '/ltr', 'auto_reset' => false],
                ['name' => 'TDS SEC 194Q', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Milk Sale', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Rate Difference', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Other Payment', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Management Other', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Round Off', 'value' => 'Round down', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => 'Round down', 'auto_reset' => false],
                ['name' => 'Bank Andvance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'Fixed Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
                ['name' => 'In Hand Advance', 'value' => '-', 'farmer_type' => 'all_farmers', 'amount' => '0', 'unit' => '-', 'auto_reset' => false],
            ];
        }

        $routes = \App\Models\DeliveryRoute::orderBy('name')->get();

        return view('settings.center_information', compact(
            'settings',
            'center',
            'centerUsers',
            'activeTab',
            'printSetting',
            'bonusPenalty',
            'smsSettings',
            'invoicePrintSetting',
            'paymentRegisterSetting',
            'centerDetails',
            'billingInfo',
            'subscriptionInfo',
            'locationInfo',
            'additionTypes',
            'deductionTypes',
            'routes'
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
                    if ($settingKey === 'center_details' && !empty($data['vehicle_route']) && trim($data['vehicle_route']) !== '-') {
                        $rName = trim($data['vehicle_route']);
                        $exists = \App\Models\DeliveryRoute::where('name', $rName)->exists();
                        if (!$exists) {
                            \App\Models\DeliveryRoute::create([
                                'name' => $rName,
                                'code' => 'RT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $rName), 0, 4)) . rand(10, 99),
                                'area_name' => $data['address_english'] ?? 'Center Route',
                                'status' => 'active',
                            ]);
                        }
                    }
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
