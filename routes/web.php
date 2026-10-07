<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\MilkCollectionController;
use App\Http\Controllers\RateChartController;
use App\Http\Controllers\FarmerSettlementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\MilkDispatchController;
use App\Http\Controllers\ProductBookingController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\AuditLogController;

// Public Website Pages
Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/about', [WebsiteController::class, 'about'])->name('about');
Route::get('/products-catalogue', [WebsiteController::class, 'products'])->name('products.frontend');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('contact');

// Public / Guest Auth Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::get('demo-login/{user}', [AuthController::class, 'demoLogin'])->name('login.demo');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// One-Click Live Database Setup Route (Migrate + Seed + Diagnostics)
Route::match(['get', 'post'], '/setup-database', function (\Illuminate\Http\Request $request) {
    // If action is requested or auto-run
    $run = $request->query('run', false);
    $wipe = $request->query('wipe', false);

    $logs = [];
    $status = 'idle';

    if ($run) {
        try {
            ini_set('max_execution_time', '300');
            ini_set('memory_limit', '512M');

            // Step 1: Check Connection
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $logs[] = "✅ Database connection established successfully with database: " . config('database.connections.mysql.database');

            // Step 2: Wipe or Migrate
            if ($wipe) {
                \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);
                $logs[] = "🔄 Tables wiped & freshly created:\n" . trim(\Illuminate\Support\Facades\Artisan::output());
            } else {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $logs[] = "🔄 Migrations executed:\n" . trim(\Illuminate\Support\Facades\Artisan::output());
            }

            // Step 3: Run Database Seeders
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            $logs[] = "🌱 Database seeded with all initial data:\n" . trim(\Illuminate\Support\Facades\Artisan::output());

            // Step 4: Clear & optimize cache
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $logs[] = "🧹 Application cache cleared successfully.";

            $status = 'success';
        } catch (\Throwable $e) {
            $logs[] = "❌ Error: " . $e->getMessage();
            $status = 'error';
        }
    }

    $tablesCount = 0;
    $usersCount = 0;
    $dbConnected = false;
    $dbError = null;

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbConnected = true;
        $dbName = config('database.connections.mysql.database');
        $rawTables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $tablesCount = count($rawTables);
        if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
            $usersCount = \App\Models\User::count();
        }
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }

    return view('install_db', compact('status', 'logs', 'tablesCount', 'usersCount', 'dbConnected', 'dbError', 'run'));
})->name('setup.database');
Route::get('/install-db', function () {
    return redirect()->route('setup.database', ['run' => 1]);
});
Route::get('/clear-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    } catch (\Throwable $e) {}
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    return redirect()->route('products.index')->with('success', 'Database updated, caches & OPcache cleared successfully!');
});


// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard & Profile
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Farmers Master & Passbook
    Route::prefix('farmers')->name('farmers.')->group(function () {
        Route::get('/', [FarmerController::class, 'index'])->name('index');
        Route::get('/create', [FarmerController::class, 'create'])->name('create');
        Route::post('/', [FarmerController::class, 'store'])->name('store');
        Route::get('/{farmer}', [FarmerController::class, 'show'])->name('show');
        Route::get('/{farmer}/edit', [FarmerController::class, 'edit'])->name('edit');
        Route::put('/{farmer}', [FarmerController::class, 'update'])->name('update');
        Route::post('/{farmer}/advance', [FarmerController::class, 'storeAdvance'])->name('advance.store');
    });

    // Milk Procurement & Collection
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [MilkCollectionController::class, 'index'])->name('index');
        Route::get('/create', [MilkCollectionController::class, 'create'])->name('create');
        Route::post('/', [MilkCollectionController::class, 'store'])->name('store');
        Route::post('/calc-rate', [MilkCollectionController::class, 'calculateRate'])->name('calc-rate');
        Route::get('/slip/{collection}', [MilkCollectionController::class, 'slip'])->name('slip');
    });

    // Rate Charts
    Route::resource('rates', RateChartController::class)->except(['show', 'destroy']);

    // Farmer Settlements
    Route::prefix('settlements')->name('settlements.')->group(function () {
        Route::get('/', [FarmerSettlementController::class, 'index'])->name('index');
        Route::get('/create', [FarmerSettlementController::class, 'create'])->name('create');
        Route::post('/', [FarmerSettlementController::class, 'store'])->name('store');
        Route::get('/{settlement}', [FarmerSettlementController::class, 'show'])->name('show');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/groups/manage', [CustomerGroupController::class, 'index'])->name('groups.index');
        Route::post('/groups/manage', [CustomerGroupController::class, 'store'])->name('groups.store');
        Route::delete('/groups/manage/{group}', [CustomerGroupController::class, 'destroy'])->name('groups.destroy');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    });

    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/create', [SubscriptionController::class, 'create'])->name('create');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::post('/{subscription}/toggle-pause', [SubscriptionController::class, 'togglePause'])->name('toggle-pause');
    });

    // Delivery & Route Operations
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/routes', [DeliveryController::class, 'routes'])->name('routes');
        Route::post('/routes', [DeliveryController::class, 'storeRoute'])->name('routes.store');
        Route::post('/boys/ajax', [DeliveryController::class, 'storeDeliveryBoyAjax'])->name('boys.ajax');
        Route::get('/board', [DeliveryController::class, 'board'])->name('board');
        Route::post('/generate-schedule', [DeliveryController::class, 'generateSchedule'])->name('generate-schedule');
        Route::get('/boy-app', [DeliveryController::class, 'boyApp'])->name('boy-app');
        Route::post('/update-status/{delivery}', [DeliveryController::class, 'updateDeliveryStatus'])->name('update-status');
    });

    // Products Catalogue & Stock
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        
        // Category Management
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('/categories/ajax', [CategoryController::class, 'listAjax'])->name('categories.ajax.get');
        Route::post('/categories/ajax', [CategoryController::class, 'storeAjax'])->name('categories.ajax');

        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::post('/{product}/toggle-stock', [ProductController::class, 'toggleStock'])->name('toggle-stock');
        Route::post('/{product}/quick-stock', [ProductController::class, 'quickStockAdd'])->name('quick-stock');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Inventory Ledger & Bottle Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/transaction', [InventoryController::class, 'storeTransaction'])->name('transaction');
        Route::get('/bottles', [InventoryController::class, 'bottles'])->name('bottles');
        Route::post('/bottles', [InventoryController::class, 'updateBottles'])->name('bottles.update');
    });

    // POS & Counter Billing
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/', [PosController::class, 'store'])->name('store');
        Route::get('/receipt/{order}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/history', [PosController::class, 'history'])->name('history');
        Route::get('/cashbook', [PosController::class, 'cashbook'])->name('cashbook');
        Route::post('/cashbook', [PosController::class, 'updateCashbook'])->name('cashbook.update');
    });

    // Invoices & Billing
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/generate', [InvoiceController::class, 'generate'])->name('generate');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    });

    // Customer Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
    });

    // Expenses & Profit/Loss
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::post('/categories/ajax', [ExpenseController::class, 'storeCategoryAjax'])->name('categories.ajax');
        Route::get('/profit-loss', [ExpenseController::class, 'profitLoss'])->name('profit-loss');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/collections', [ReportController::class, 'collections'])->name('collections');
        Route::get('/deliveries', [ReportController::class, 'deliveries'])->name('deliveries');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/dues', [ReportController::class, 'dues'])->name('dues');
    });

    // Users & Roles & Permission Matrix
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/matrix', [UserController::class, 'matrix'])->name('matrix');
    });

    // Settings & Simulator
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
        Route::post('/test-notification', [SettingController::class, 'testNotification'])->name('test-notification');
    });

    // Support Tickets
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{support}', [SupportTicketController::class, 'show'])->name('show');
        Route::post('/{support}/reply', [SupportTicketController::class, 'reply'])->name('reply');
    });

    // Milk Dispatched (Outward Delivery)
    Route::prefix('dispatch')->name('dispatch.')->group(function () {
        Route::get('/', [MilkDispatchController::class, 'index'])->name('index');
        Route::post('/', [MilkDispatchController::class, 'store'])->name('store');
    });

    // Product Pre-orders & Bookings
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [ProductBookingController::class, 'index'])->name('index');
        Route::post('/', [ProductBookingController::class, 'store'])->name('store');
    });

    // SMS & Notifications
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [SmsController::class, 'index'])->name('index');
        Route::post('/send', [SmsController::class, 'send'])->name('send');
    });

    // Staff Management
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/attendance', [StaffController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [StaffController::class, 'saveAttendance'])->name('attendance.save');
        Route::get('/advances', [StaffController::class, 'advances'])->name('advances');
        Route::post('/advances', [StaffController::class, 'storeAdvance'])->name('advances.store');
        Route::get('/salaries', [StaffController::class, 'salaries'])->name('salaries');
        Route::post('/salaries/generate', [StaffController::class, 'generateSalary'])->name('salaries.generate');
    });

    // Website CMS (Super Admin)
    Route::prefix('admin/website')->name('admin.website.')->group(function () {
        Route::get('/banners', [WebsiteController::class, 'banners'])->name('banners');
        Route::post('/banners', [WebsiteController::class, 'storeBanner'])->name('banners.store');
        Route::post('/banners/{banner}/toggle', [WebsiteController::class, 'toggleBanner'])->name('banners.toggle');
        Route::post('/settings', [WebsiteController::class, 'updateSettings'])->name('settings');
    });

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});
