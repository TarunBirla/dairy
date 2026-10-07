<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\CollectionCenter;
use App\Models\PosCounter;
use App\Models\RateChart;
use App\Models\RateChartSlab;
use App\Models\Farmer;
use App\Models\MilkCollection;
use App\Models\FarmerAdvance;
use App\Models\FarmerSettlement;
use App\Models\FarmerLedger;
use App\Models\DeliveryRoute;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Subscription;
use App\Models\DailyDelivery;
use App\Models\CustomerLedger;
use App\Models\Category;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\BottleTracking;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\CounterCashbook;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\Notification;
use App\Models\SystemSetting;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::set('dairy_name', 'Simple Dairy', 'dairy');
        SystemSetting::set('tagline', 'Fresh, Pure & Natural Dairy Products', 'dairy');
        SystemSetting::set('owner_name', 'Narendra Malviya', 'dairy');
        SystemSetting::set('phone', '+91 98765 43210', 'dairy');
        SystemSetting::set('email', 'contact@simpledairy.com', 'dairy');
        SystemSetting::set('address', 'Plot 42, Dairy Processing Zone, Industrial Area', 'dairy');
        SystemSetting::set('currency', '₹', 'general');
        SystemSetting::set('morning_shift', '06:00 - 09:30', 'dairy');
        SystemSetting::set('evening_shift', '17:00 - 20:30', 'dairy');
        SystemSetting::set('whatsapp_enabled', '1', 'notification');
        SystemSetting::set('sms_enabled', '1', 'notification');

        // 2. Users (Roles & Hierarchy)
        $superAdmin = User::create([
            'name' => 'System SuperAdmin',
            'email' => 'superadmin@simpledairy.com',
            'phone' => '9000000001',
            'role' => User::ROLE_SUPER_ADMIN,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $dairyOwner = User::create([
            'name' => 'Narendra Malviya',
            'email' => 'admin@simpledairy.com',
            'phone' => '9000000002',
            'role' => User::ROLE_DAIRY_ADMIN,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $branchManager = User::create([
            'name' => 'Rajesh Sharma',
            'email' => 'branch@simpledairy.com',
            'phone' => '9000000003',
            'role' => User::ROLE_BRANCH_MANAGER,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $collectionOp = User::create([
            'name' => 'Dinesh Verma',
            'email' => 'operator@simpledairy.com',
            'phone' => '9000000004',
            'role' => User::ROLE_COLLECTION_OPERATOR,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $accountant = User::create([
            'name' => 'Amit Joshi',
            'email' => 'accountant@simpledairy.com',
            'phone' => '9000000005',
            'role' => User::ROLE_ACCOUNTANT,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $deliveryBoy = User::create([
            'name' => 'Vikram Singh',
            'email' => 'delivery@simpledairy.com',
            'phone' => '9000000006',
            'role' => User::ROLE_DELIVERY_BOY,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $salesOp = User::create([
            'name' => 'Pooja Tiwari',
            'email' => 'pos@simpledairy.com',
            'phone' => '9000000007',
            'role' => User::ROLE_SALES_OPERATOR,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $farmerUser = User::create([
            'name' => 'Ramesh Patel',
            'email' => 'farmer@simpledairy.com',
            'phone' => '9000000008',
            'role' => User::ROLE_FARMER,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $customerUser = User::create([
            'name' => 'Sunil Mehta',
            'email' => 'customer@simpledairy.com',
            'phone' => '9000000009',
            'role' => User::ROLE_CUSTOMER,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        // 3. Branches, Collection Centers & POS Counters
        $mainBranch = Branch::create([
            'name' => 'Central Dairy Hub & Plant',
            'code' => 'BR-001',
            'phone' => '+91 98765 43210',
            'email' => 'hub@simpledairy.com',
            'address' => 'Plot 42, Dairy Processing Zone',
            'city' => 'Indore',
            'manager_id' => $dairyOwner->id,
            'status' => 'active',
        ]);

        $northBranch = Branch::create([
            'name' => 'North Distribution Center',
            'code' => 'BR-002',
            'phone' => '+91 98765 43211',
            'email' => 'north@simpledairy.com',
            'address' => 'Station Road, North Zone',
            'city' => 'Indore',
            'manager_id' => $branchManager->id,
            'status' => 'active',
        ]);

        $center1 = CollectionCenter::create([
            'branch_id' => $mainBranch->id,
            'name' => 'Green Valley Mandi Center',
            'code' => 'CC-001',
            'location' => 'Village Green Valley, Sector 4',
            'morning_shift_time' => '06:00 - 09:30',
            'evening_shift_time' => '17:00 - 20:30',
            'operator_id' => $collectionOp->id,
            'status' => 'active',
        ]);

        $center2 = CollectionCenter::create([
            'branch_id' => $mainBranch->id,
            'name' => 'Kisan Seva Kendra Depot',
            'code' => 'CC-002',
            'location' => 'Main Chowk, Village Palasia',
            'morning_shift_time' => '06:00 - 09:30',
            'evening_shift_time' => '17:00 - 20:30',
            'operator_id' => $collectionOp->id,
            'status' => 'active',
        ]);

        $counter1 = PosCounter::create([
            'branch_id' => $mainBranch->id,
            'name' => 'Booth #1 Main Dairy Outlet',
            'code' => 'POS-001',
            'operator_id' => $salesOp->id,
            'status' => 'active',
        ]);

        // 4. Rate Charts
        $cowRateChart = RateChart::create([
            'name' => 'Standard Cow Milk Formula Chart (TS)',
            'milk_type' => 'cow',
            'calculation_type' => 'fat_snf_formula',
            'base_rate' => 38.00,
            'min_fat' => 3.2,
            'max_fat' => 5.5,
            'min_snf' => 8.2,
            'max_snf' => 9.5,
            'fat_factor' => 6.80,
            'snf_factor' => 4.10,
            'effective_date' => Carbon::now()->subMonths(3),
            'is_default' => true,
            'status' => 'active',
        ]);

        $buffaloRateChart = RateChart::create([
            'name' => 'Standard Buffalo Milk Chart',
            'milk_type' => 'buffalo',
            'calculation_type' => 'fat_snf_formula',
            'base_rate' => 55.00,
            'min_fat' => 6.0,
            'max_fat' => 10.0,
            'min_snf' => 8.8,
            'max_snf' => 10.0,
            'fat_factor' => 7.20,
            'snf_factor' => 4.50,
            'effective_date' => Carbon::now()->subMonths(3),
            'is_default' => true,
            'status' => 'active',
        ]);

        // Slabs for matrix chart fallback
        RateChartSlab::create([
            'rate_chart_id' => $cowRateChart->id,
            'fat_from' => 3.5,
            'fat_to' => 4.0,
            'snf_from' => 8.3,
            'snf_to' => 8.7,
            'rate' => 42.50,
        ]);

        // 5. Farmers Master
        $farmersData = [
            ['code' => 'FAR-101', 'name' => 'Ramesh Patel', 'phone' => '9826011111', 'village' => 'Palasia', 'animal' => 'cow', 'bank' => 'State Bank of India', 'acc' => '30891283712', 'ifsc' => 'SBIN0001234', 'upi' => 'ramesh@upi', 'balance' => 4250.00, 'user_id' => $farmerUser->id],
            ['code' => 'FAR-102', 'name' => 'Suresh Yadav', 'phone' => '9826022222', 'village' => 'Green Valley', 'animal' => 'buffalo', 'bank' => 'Bank of Baroda', 'acc' => '09281293847', 'ifsc' => 'BARB0INDORE', 'upi' => 'suresh@ybl', 'balance' => 8400.00, 'user_id' => null],
            ['code' => 'FAR-103', 'name' => 'Mukesh Sharma', 'phone' => '9826033333', 'village' => 'Pipaliya', 'animal' => 'cow', 'bank' => 'Punjab National Bank', 'acc' => '49281928374', 'ifsc' => 'PUNB0192800', 'upi' => 'mukesh@paytm', 'balance' => 3120.00, 'user_id' => null],
            ['code' => 'FAR-104', 'name' => 'Gopal Bhai', 'phone' => '9826044444', 'village' => 'Palasia', 'animal' => 'buffalo', 'bank' => 'HDFC Bank', 'acc' => '50100293847', 'ifsc' => 'HDFC0000241', 'upi' => 'gopal@okaxis', 'balance' => 12500.00, 'user_id' => null],
            ['code' => 'FAR-105', 'name' => 'Kailash Choudhary', 'phone' => '9826055555', 'village' => 'Bicholi', 'animal' => 'mixed', 'bank' => 'Central Bank', 'acc' => '21928374619', 'ifsc' => 'CBIN0281928', 'upi' => 'kailash@upi', 'balance' => 1890.00, 'user_id' => null],
            ['code' => 'FAR-106', 'name' => 'Jagdish Gurjar', 'phone' => '9826066666', 'village' => 'Green Valley', 'animal' => 'buffalo', 'bank' => 'Union Bank', 'acc' => '59281928374', 'ifsc' => 'UBIN0549281', 'upi' => 'jagdish@icici', 'balance' => 6700.00, 'user_id' => null],
        ];

        $farmers = [];
        foreach ($farmersData as $fd) {
            $farmers[] = Farmer::create([
                'farmer_code' => $fd['code'],
                'user_id' => $fd['user_id'],
                'branch_id' => $mainBranch->id,
                'collection_center_id' => $center1->id,
                'rate_chart_id' => ($fd['animal'] === 'buffalo') ? $buffaloRateChart->id : $cowRateChart->id,
                'name' => $fd['name'],
                'phone' => $fd['phone'],
                'village' => $fd['village'],
                'address' => 'Village ' . $fd['village'] . ', Tehsil Indore',
                'supplier_type' => 'farmer',
                'animal_type' => $fd['animal'],
                'bank_name' => $fd['bank'],
                'account_number' => $fd['acc'],
                'ifsc_code' => $fd['ifsc'],
                'upi_id' => $fd['upi'],
                'current_balance' => $fd['balance'],
                'status' => 'active',
            ]);
        }

        // 6. Milk Collection Entries (Today & Yesterday)
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $collectionEntries = [
            ['farmer' => $farmers[0], 'date' => $today, 'shift' => 'morning', 'milk' => 'cow', 'qty' => 15.5, 'fat' => 4.2, 'snf' => 8.6, 'clr' => 28.5],
            ['farmer' => $farmers[1], 'date' => $today, 'shift' => 'morning', 'milk' => 'buffalo', 'qty' => 22.0, 'fat' => 6.8, 'snf' => 9.2, 'clr' => 30.0],
            ['farmer' => $farmers[2], 'date' => $today, 'shift' => 'morning', 'milk' => 'cow', 'qty' => 12.0, 'fat' => 3.9, 'snf' => 8.4, 'clr' => 28.0],
            ['farmer' => $farmers[3], 'date' => $today, 'shift' => 'morning', 'milk' => 'buffalo', 'qty' => 28.5, 'fat' => 7.1, 'snf' => 9.4, 'clr' => 31.0],
            ['farmer' => $farmers[0], 'date' => $yesterday, 'shift' => 'evening', 'milk' => 'cow', 'qty' => 14.0, 'fat' => 4.1, 'snf' => 8.5, 'clr' => 28.0],
            ['farmer' => $farmers[1], 'date' => $yesterday, 'shift' => 'evening', 'milk' => 'buffalo', 'qty' => 20.0, 'fat' => 6.6, 'snf' => 9.1, 'clr' => 29.5],
            ['farmer' => $farmers[4], 'date' => $yesterday, 'shift' => 'morning', 'milk' => 'mixed', 'qty' => 18.0, 'fat' => 4.5, 'snf' => 8.7, 'clr' => 29.0],
        ];

        $entryCounter = 1001;
        foreach ($collectionEntries as $entry) {
            $chart = ($entry['milk'] === 'buffalo') ? $buffaloRateChart : $cowRateChart;
            $rate = $chart->calculateRate($entry['fat'], $entry['snf']);
            $gross = round($entry['qty'] * $rate, 2);
            $net = $gross;

            $col = MilkCollection::create([
                'receipt_number' => 'COL-' . ($entryCounter++),
                'farmer_id' => $entry['farmer']->id,
                'collection_center_id' => $center1->id,
                'operator_id' => $collectionOp->id,
                'collection_date' => $entry['date'],
                'shift' => $entry['shift'],
                'milk_type' => $entry['milk'],
                'quantity_liters' => $entry['qty'],
                'fat' => $entry['fat'],
                'snf' => $entry['snf'],
                'clr' => $entry['clr'],
                'calculated_rate' => $rate,
                'applied_rate' => $rate,
                'gross_amount' => $gross,
                'bonus' => 0.00,
                'deduction' => 0.00,
                'net_amount' => $net,
                'payment_status' => 'pending',
                'notes' => 'Good freshness, chilled immediately.',
            ]);

            FarmerLedger::create([
                'farmer_id' => $entry['farmer']->id,
                'transaction_date' => $entry['date'],
                'type' => 'credit',
                'amount' => $net,
                'balance' => $entry['farmer']->current_balance,
                'reference_type' => 'milk_collection',
                'reference_id' => $col->id,
                'description' => "Milk Collection {$entry['shift']} ({$entry['qty']} Ltr @ ₹{$rate}/Ltr)",
            ]);
        }

        // Farmer Advance
        FarmerAdvance::create([
            'farmer_id' => $farmers[0]->id,
            'amount' => 1500.00,
            'advance_date' => Carbon::now()->subDays(5),
            'purpose' => 'Cattle feed purchase advance',
            'deducted_amount' => 500.00,
            'status' => 'partially_deducted',
            'notes' => 'To be deducted in next 2 billing cycles',
        ]);

        // 7. Product Categories & Products (Matching Screenshot EXACTLY!)
        $catDairy = Category::create(['name' => 'Fresh Dairy', 'slug' => 'fresh-dairy', 'description' => 'Daily farm-fresh cow and buffalo milk products']);
        $catSweets = Category::create(['name' => 'Traditional Sweets & Mawa', 'slug' => 'sweets-mawa', 'description' => 'Pure khoya, mawa and dairy sweets']);
        $catFeed = Category::create(['name' => 'Cattle Feed & Supplements', 'slug' => 'cattle-feed', 'description' => 'Nutritional feed for dairy cattle']);

        $productsData = [
            [
                'name' => 'पनीर',
                'code' => 'PRD-001',
                'category_id' => $catDairy->id,
                'product_type' => 'paneer',
                'unit' => 'kg',
                'pack_size' => '1 kg',
                'price' => 300.00,
                'subscription_price' => 290.00,
                'cost_price' => 230.00,
                'current_stock' => 8.00,
                'min_stock_alert' => 5.00,
                'in_stock' => true,
                'description' => 'Fresh malai paneer made from pure whole milk',
            ],
            [
                'name' => 'घी',
                'code' => 'PRD-002',
                'category_id' => $catDairy->id,
                'product_type' => 'ghee',
                'unit' => 'kg',
                'pack_size' => '1 kg',
                'price' => 680.00,
                'subscription_price' => 660.00,
                'cost_price' => 520.00,
                'current_stock' => 0.00,
                'min_stock_alert' => 10.00,
                'in_stock' => false,
                'description' => '11/10/25 se 580 par kg special batch pure bilona ghee',
            ],
            [
                'name' => 'बर्फी मावा',
                'code' => 'PRD-003',
                'category_id' => $catSweets->id,
                'product_type' => 'custom',
                'unit' => 'kg',
                'pack_size' => '1 kg',
                'price' => 285.00,
                'subscription_price' => 275.00,
                'cost_price' => 210.00,
                'current_stock' => 0.00,
                'min_stock_alert' => 5.00,
                'in_stock' => false,
                'description' => 'शुद्ध मावा / Fresh sweet khoya mawa for traditional sweets',
            ],
            [
                'name' => 'सादा दूध',
                'code' => 'PRD-004',
                'category_id' => $catDairy->id,
                'product_type' => 'milk',
                'unit' => 'liter',
                'pack_size' => '1 Ltr',
                'price' => 50.00,
                'subscription_price' => 48.00,
                'cost_price' => 40.00,
                'current_stock' => 45.00,
                'min_stock_alert' => 20.00,
                'in_stock' => true,
                'description' => 'ताज़ा गाय का सादा दूध / Pure farm cow milk',
            ],
            [
                'name' => 'गोल्ड दूध',
                'code' => 'PRD-005',
                'category_id' => $catDairy->id,
                'product_type' => 'milk',
                'unit' => 'liter',
                'pack_size' => '1 Ltr',
                'price' => 60.00,
                'subscription_price' => 58.00,
                'cost_price' => 48.00,
                'current_stock' => 0.00,
                'min_stock_alert' => 20.00,
                'in_stock' => false,
                'description' => 'फुल क्रीम भैंस का दूध / Rich creamy buffalo gold milk',
            ],
            [
                'name' => 'khalli',
                'code' => 'PRD-006',
                'product_for' => 'farmer',
                'category_id' => $catFeed->id,
                'product_type' => 'feed',
                'unit' => 'piece',
                'pack_size' => '50 kg Bag',
                'price' => 2500.00,
                'subscription_price' => 2450.00,
                'cost_price' => 2100.00,
                'current_stock' => 12.00,
                'min_stock_alert' => 4.00,
                'in_stock' => true,
                'description' => 'प्रीमियम बिनौला / सरसों खल्ली बोरी (Cattle cake feed 50kg)',
            ],
            [
                'name' => 'Bottle milk 1 ltr',
                'code' => 'PRD-007',
                'category_id' => $catDairy->id,
                'product_type' => 'milk',
                'unit' => 'bottle',
                'pack_size' => '1 Ltr Glass Bottle',
                'price' => 60.00,
                'subscription_price' => 58.00,
                'cost_price' => 45.00,
                'current_stock' => 18.00,
                'min_stock_alert' => 10.00,
                'in_stock' => true,
                'description' => 'Pasteurized glass bottle packaged farm fresh milk',
            ],
            [
                'name' => 'मक्खन (Butter)',
                'code' => 'PRD-008',
                'category_id' => $catDairy->id,
                'product_type' => 'butter',
                'unit' => 'kg',
                'pack_size' => '500g',
                'price' => 420.00,
                'subscription_price' => 400.00,
                'cost_price' => 330.00,
                'current_stock' => 0.00,
                'min_stock_alert' => 5.00,
                'in_stock' => false,
                'description' => 'Farm-churned fresh white butter (Makhan)',
            ],
        ];

        $products = [];
        foreach ($productsData as $pd) {
            $prod = Product::create($pd);
            $products[] = $prod;

            if ($prod->current_stock > 0) {
                InventoryTransaction::create([
                    'product_id' => $prod->id,
                    'branch_id' => $mainBranch->id,
                    'transaction_type' => 'purchase_inward',
                    'quantity' => $prod->current_stock,
                    'unit_cost' => $prod->cost_price,
                    'balance_after' => $prod->current_stock,
                    'notes' => 'Opening stock setup',
                ]);
            }
        }

        // 8. Delivery Routes
        $route1 = DeliveryRoute::create([
            'branch_id' => $mainBranch->id,
            'name' => 'Route A - Scheme 54 & Vijay Nagar',
            'code' => 'RT-001',
            'area_name' => 'Vijay Nagar, Scheme 54, Bapat Square',
            'delivery_boy_id' => $deliveryBoy->id,
            'description' => 'Morning delivery run starting 05:30 AM',
            'status' => 'active',
        ]);

        $route2 = DeliveryRoute::create([
            'branch_id' => $mainBranch->id,
            'name' => 'Route B - Old Palasia & Manoramaganj',
            'code' => 'RT-002',
            'area_name' => 'Old Palasia, Geeta Bhawan, Navlakha',
            'delivery_boy_id' => $deliveryBoy->id,
            'description' => 'Morning delivery run starting 06:15 AM',
            'status' => 'active',
        ]);

        // 9. Customers & Addresses
        $customersData = [
            ['code' => 'CUST-201', 'name' => 'Sunil Mehta', 'phone' => '9827011111', 'locality' => 'Vijay Nagar', 'route' => $route1, 'cat' => 'household', 'seq' => 1, 'user' => $customerUser->id, 'balance' => 840.00],
            ['code' => 'CUST-202', 'name' => 'Dr. Ananya Roy', 'phone' => '9827022222', 'locality' => 'Scheme 54', 'route' => $route1, 'cat' => 'household', 'seq' => 2, 'user' => null, 'balance' => 1250.00],
            ['code' => 'CUST-203', 'name' => 'Hotel Shanti Palace', 'phone' => '9827033333', 'locality' => 'Bapat Square', 'route' => $route1, 'cat' => 'hotel', 'seq' => 3, 'user' => null, 'balance' => 4500.00],
            ['code' => 'CUST-204', 'name' => 'Pooja Agarwal', 'phone' => '9827044444', 'locality' => 'Old Palasia', 'route' => $route2, 'cat' => 'household', 'seq' => 1, 'user' => null, 'balance' => 0.00],
            ['code' => 'CUST-205', 'name' => 'Manoj Sweet Shop', 'phone' => '9827055555', 'locality' => 'Geeta Bhawan', 'route' => $route2, 'cat' => 'shop', 'seq' => 2, 'user' => null, 'balance' => 2800.00],
            ['code' => 'CUST-206', 'name' => 'Kavita Joshi', 'phone' => '9827066666', 'locality' => 'Scheme 54', 'route' => $route1, 'cat' => 'household', 'seq' => 4, 'user' => null, 'balance' => 420.00],
        ];

        $customers = [];
        foreach ($customersData as $cd) {
            $c = Customer::create([
                'customer_code' => $cd['code'],
                'user_id' => $cd['user'],
                'branch_id' => $mainBranch->id,
                'route_id' => $cd['route']->id,
                'name' => $cd['name'],
                'phone' => $cd['phone'],
                'email' => strtolower(str_replace(' ', '', $cd['name'])) . '@example.com',
                'address' => 'Flat 102, Royal Residency, ' . $cd['locality'],
                'locality' => $cd['locality'],
                'category' => $cd['cat'],
                'credit_limit' => 2000.00,
                'current_balance' => $cd['balance'],
                'delivery_sequence' => $cd['seq'],
                'delivery_instructions' => 'Ring bell once, leave at door pouch.',
                'status' => 'active',
            ]);

            CustomerAddress::create([
                'customer_id' => $c->id,
                'address_type' => 'home',
                'address_line' => 'Flat 102, Royal Residency, ' . $cd['locality'],
                'landmark' => 'Near City Garden',
                'pincode' => '452010',
                'is_default' => true,
            ]);

            BottleTracking::create([
                'customer_id' => $c->id,
                'issued_count' => 4,
                'returned_count' => 2,
                'broken_count' => 0,
                'deposit_rate_per_bottle' => 50.00,
                'total_deposit_amount' => 100.00,
                'balance_bottles' => 2,
                'notes' => 'Customer has 2 glass bottles on loan',
            ]);

            $customers[] = $c;
        }

        // 10. Subscriptions
        $subMilk = $products[3]; // सादा दूध (₹48 sub)
        $subBottle = $products[6]; // Bottle milk (₹58 sub)

        $sub1 = Subscription::create([
            'subscription_code' => 'SUB-101',
            'customer_id' => $customers[0]->id,
            'product_id' => $subBottle->id,
            'route_id' => $route1->id,
            'quantity' => 2.0,
            'frequency' => 'daily',
            'shift' => 'morning',
            'unit_price' => 58.00,
            'start_date' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        $sub2 = Subscription::create([
            'subscription_code' => 'SUB-102',
            'customer_id' => $customers[1]->id,
            'product_id' => $subMilk->id,
            'route_id' => $route1->id,
            'quantity' => 1.5,
            'frequency' => 'daily',
            'shift' => 'morning',
            'unit_price' => 48.00,
            'start_date' => Carbon::now()->subMonth(),
            'status' => 'active',
        ]);

        $sub3 = Subscription::create([
            'subscription_code' => 'SUB-103',
            'customer_id' => $customers[2]->id, // Hotel
            'product_id' => $subMilk->id,
            'route_id' => $route1->id,
            'quantity' => 10.0,
            'frequency' => 'daily',
            'shift' => 'morning',
            'unit_price' => 46.00,
            'start_date' => Carbon::now()->subMonths(3),
            'status' => 'active',
        ]);

        $sub4 = Subscription::create([
            'subscription_code' => 'SUB-104',
            'customer_id' => $customers[3]->id,
            'product_id' => $subBottle->id,
            'route_id' => $route2->id,
            'quantity' => 1.0,
            'frequency' => 'daily',
            'shift' => 'morning',
            'unit_price' => 58.00,
            'start_date' => Carbon::now()->subMonth(),
            'status' => 'paused',
            'pause_from' => Carbon::today(),
            'pause_until' => Carbon::today()->addDays(4),
        ]);

        // 11. Daily Deliveries (Today's Run)
        DailyDelivery::create([
            'delivery_date' => $today,
            'shift' => 'morning',
            'route_id' => $route1->id,
            'delivery_boy_id' => $deliveryBoy->id,
            'customer_id' => $customers[0]->id,
            'subscription_id' => $sub1->id,
            'product_id' => $subBottle->id,
            'quantity' => 2.0,
            'delivered_quantity' => 2.0,
            'unit_price' => 58.00,
            'total_amount' => 116.00,
            'status' => 'delivered',
            'cash_collected' => 116.00,
            'delivered_at' => Carbon::now()->subHours(2),
            'notes' => 'Left at doorstep. Cash collected.',
        ]);

        DailyDelivery::create([
            'delivery_date' => $today,
            'shift' => 'morning',
            'route_id' => $route1->id,
            'delivery_boy_id' => $deliveryBoy->id,
            'customer_id' => $customers[1]->id,
            'subscription_id' => $sub2->id,
            'product_id' => $subMilk->id,
            'quantity' => 1.5,
            'delivered_quantity' => 1.5,
            'unit_price' => 48.00,
            'total_amount' => 72.00,
            'status' => 'delivered',
            'cash_collected' => 0.00,
            'delivered_at' => Carbon::now()->subHours(1),
            'notes' => 'Monthly billing customer.',
        ]);

        DailyDelivery::create([
            'delivery_date' => $today,
            'shift' => 'morning',
            'route_id' => $route1->id,
            'delivery_boy_id' => $deliveryBoy->id,
            'customer_id' => $customers[2]->id,
            'subscription_id' => $sub3->id,
            'product_id' => $subMilk->id,
            'quantity' => 10.0,
            'delivered_quantity' => 0.0,
            'unit_price' => 46.00,
            'total_amount' => 460.00,
            'status' => 'pending',
            'cash_collected' => 0.00,
            'notes' => 'Bulk supply to hotel kitchen',
        ]);

        DailyDelivery::create([
            'delivery_date' => $today,
            'shift' => 'morning',
            'route_id' => $route2->id,
            'delivery_boy_id' => $deliveryBoy->id,
            'customer_id' => $customers[3]->id,
            'subscription_id' => $sub4->id,
            'product_id' => $subBottle->id,
            'quantity' => 1.0,
            'delivered_quantity' => 0.0,
            'unit_price' => 58.00,
            'total_amount' => 0.00,
            'status' => 'skipped',
            'failure_reason' => 'Customer on leave / Vacation pause',
        ]);

        // 12. POS Orders & Cashbook
        $pos1 = PosOrder::create([
            'order_number' => 'POS-202610-001',
            'branch_id' => $mainBranch->id,
            'counter_id' => $counter1->id,
            'customer_id' => null,
            'customer_name' => 'Walk-in (Shri Verma)',
            'customer_phone' => '9893012345',
            'subtotal' => 600.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'grand_total' => 600.00,
            'payment_mode' => 'upi',
            'payment_status' => 'paid',
            'operator_id' => $salesOp->id,
            'notes' => 'Paid via PhonePe QR',
        ]);

        PosOrderItem::create([
            'pos_order_id' => $pos1->id,
            'product_id' => $products[0]->id, // Paneer
            'quantity' => 2.0,
            'unit_price' => 300.00,
            'total_price' => 600.00,
        ]);

        CounterCashbook::create([
            'branch_id' => $mainBranch->id,
            'counter_id' => $counter1->id,
            'operator_id' => $salesOp->id,
            'entry_date' => $today,
            'opening_cash' => 2000.00,
            'cash_sales' => 3450.00,
            'cash_expenses' => 150.00,
            'closing_cash' => 5300.00,
            'actual_counted_cash' => 5300.00,
            'variance' => 0.00,
            'status' => 'open',
            'notes' => 'Today counter running smoothly',
        ]);

        // 13. Invoices & Payments
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-0901',
            'customer_id' => $customers[1]->id,
            'branch_id' => $mainBranch->id,
            'period_start' => Carbon::now()->subMonth()->startOfMonth(),
            'period_end' => Carbon::now()->subMonth()->endOfMonth(),
            'invoice_date' => Carbon::now()->startOfMonth(),
            'due_date' => Carbon::now()->startOfMonth()->addDays(10),
            'subtotal' => 1440.00,
            'tax_amount' => 0.00,
            'discount_amount' => 0.00,
            'previous_due' => 0.00,
            'total_amount' => 1440.00,
            'paid_amount' => 1440.00,
            'balance_due' => 0.00,
            'status' => 'paid',
            'billing_type' => 'subscription',
            'notes' => 'Monthly milk delivery invoice for September',
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $subMilk->id,
            'description' => 'सादा दूध (Standard Cow Milk) - 30 days @ 1.5 L/day',
            'quantity' => 45.0,
            'unit_price' => 48.00,
            'total_price' => 1440.00,
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-001',
            'customer_id' => $customers[1]->id,
            'invoice_id' => $inv1->id,
            'amount' => 1440.00,
            'payment_mode' => 'upi',
            'transaction_reference' => 'UPI/291823749/GooglePay',
            'payment_date' => Carbon::now()->subDays(4),
            'collected_by' => $dairyOwner->id,
            'notes' => 'Invoice full payment cleared',
        ]);

        // 14. Expense Categories & Expenses
        $expFeed = ExpenseCategory::create(['name' => 'Cattle Feed & Fodder', 'slug' => 'cattle-feed-exp', 'description' => 'Purchase of fodder, straw, khalli']);
        $expFuel = ExpenseCategory::create(['name' => 'Fuel & Transportation', 'slug' => 'fuel-transport', 'description' => 'Delivery bikes fuel, van diesel']);
        $expElectricity = ExpenseCategory::create(['name' => 'Electricity & Chilling Unit', 'slug' => 'electricity', 'description' => 'Bulk milk cooler (BMC) and plant power']);
        $expSalary = ExpenseCategory::create(['name' => 'Staff Salaries & Wages', 'slug' => 'salaries', 'description' => 'Delivery staff and plant labor']);
        $expPacking = ExpenseCategory::create(['name' => 'Packaging & Bottles', 'slug' => 'packaging', 'description' => 'Pouches, glass bottles, caps, crates']);

        Expense::create([
            'branch_id' => $mainBranch->id,
            'expense_category_id' => $expFuel->id,
            'amount' => 650.00,
            'expense_date' => $today,
            'payment_mode' => 'cash',
            'vendor_name' => 'Indian Oil Petrol Pump',
            'description' => 'Fuel for delivery bikes Route A & Route B',
            'recorded_by' => $accountant->id,
            'approved_by' => $dairyOwner->id,
            'status' => 'approved',
        ]);

        Expense::create([
            'branch_id' => $mainBranch->id,
            'expense_category_id' => $expElectricity->id,
            'amount' => 4200.00,
            'expense_date' => Carbon::now()->subDays(6),
            'payment_mode' => 'bank_transfer',
            'vendor_name' => 'MP Electricity Board',
            'description' => 'Chiller unit power bill for collection center',
            'recorded_by' => $accountant->id,
            'approved_by' => $dairyOwner->id,
            'status' => 'approved',
        ]);

        // 15. Support Tickets
        SupportTicket::create([
            'ticket_number' => 'TCK-2026-001',
            'customer_id' => $customers[0]->id,
            'category' => 'delivery',
            'subject' => 'Please deliver earlier before 6:30 AM',
            'description' => 'Need early delivery for school preparation.',
            'priority' => 'medium',
            'status' => 'in_progress',
            'assigned_to' => $deliveryBoy->id,
        ]);

        // 16. Notifications
        Notification::create([
            'user_id' => $farmers[0]->user_id,
            'title' => 'Milk Collection Receipt COL-1001',
            'message' => 'Morning shift milk recorded: 15.50 Ltr, FAT: 4.2%, SNF: 8.6%, Rate: ₹42.50, Amount: ₹658.75 credited to your ledger.',
            'channel' => 'whatsapp',
            'type' => 'success',
            'recipient_phone' => $farmers[0]->phone,
            'status' => 'sent',
        ]);

        Notification::create([
            'user_id' => $customers[0]->user->id,
            'title' => 'Morning Milk Delivered',
            'message' => 'Your 2.0 Ltr Bottle Milk was delivered at 06:15 AM by Vikram Singh.',
            'channel' => 'sms',
            'type' => 'info',
            'recipient_phone' => $customers[0]->phone,
            'status' => 'sent',
        ]);

        // 17. Customer Groups
        $vipGroup = \App\Models\CustomerGroup::firstOrCreate(['name' => 'VIP Customers'], [
            'slug' => 'vip-customers',
            'description' => 'Priority morning early delivery customers',
            'color' => '#10b981'
        ]);
        $commercialGroup = \App\Models\CustomerGroup::firstOrCreate(['name' => 'Commercial / Hotels'], [
            'slug' => 'commercial-hotels',
            'description' => 'Hotels, tea stalls and sweet shops',
            'color' => '#3b82f6'
        ]);
        if (isset($customers[0])) {
            $customers[0]->groups()->syncWithoutDetaching([$vipGroup->id]);
        }
        if (isset($customers[2])) {
            $customers[2]->groups()->syncWithoutDetaching([$commercialGroup->id]);
        }

        // 18. Website Settings & Banners
        $defaultSettings = [
            'hero_title' => 'Pure Farm Fresh Milk Delivered to Your Doorstep Every Morning',
            'hero_subtitle' => '100% natural, unprocessed, lab-tested cow & buffalo milk sourced directly from trusted village farmers.',
            'about_story' => 'Simple Dairy was founded with a single mission: to reconnect urban families with genuine farm milk and dairy products.',
            'phone' => '+91 98765 43210',
            'email' => 'contact@simpledairy.com',
            'address' => 'Plot 42, Dairy Processing Zone, Industrial Area, Indore, MP',
            'morning_time' => '05:30 AM - 08:30 AM',
            'evening_time' => '05:00 PM - 08:00 PM',
        ];
        foreach ($defaultSettings as $key => $val) {
            \App\Models\WebsiteSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        \App\Models\WebsiteBanner::firstOrCreate(['title' => '100% Shudh Desi Cow & Buffalo Milk'], [
            'subtitle' => 'Farm-fresh raw & pasteurized milk directly from local farmers at fair transparent rates.',
            'badge_text' => 'Direct From Verified Farmers',
            'cta_text' => 'Start Milk Subscription',
            'cta_url' => '/login',
            'bg_gradient' => 'from-emerald-900 via-teal-950 to-slate-950',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        \App\Models\WebsiteBanner::firstOrCreate(['title' => 'Fresh Bilona Ghee, Malai Paneer & Khoya'], [
            'subtitle' => 'Traditional bilona method churned pure ghee, daily fresh paneer & authentic sweets.',
            'badge_text' => 'Premium Quality Dairy',
            'cta_text' => 'Explore Product Catalogue',
            'cta_url' => '/products-catalogue',
            'bg_gradient' => 'from-blue-900 via-indigo-950 to-slate-950',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // 19. Milk Dispatch Sample
        \App\Models\MilkDispatch::create([
            'dispatch_number' => 'DSP-202610-001',
            'branch_id' => $mainBranch->id,
            'dispatch_date' => $today,
            'shift' => 'morning',
            'milk_type' => 'mixed',
            'quantity_liters' => 500.00,
            'fat' => 4.80,
            'snf' => 8.80,
            'temperature' => 4.20,
            'destination_type' => 'processing_plant',
            'destination_name' => 'Main Indore BMC Plant',
            'tanker_number' => 'MP-09-GA-4521',
            'driver_name' => 'Sukhdev Singh',
            'driver_phone' => '9827099887',
            'status' => 'dispatched',
        ]);
    }
}
