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
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\DealerPaymentController;
use App\Http\Controllers\FarmerAdvanceController;
use App\Http\Controllers\FarmerDeductionController;
use App\Http\Controllers\FarmerInvoiceController;
use App\Http\Controllers\MilkSaleController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleAdvanceController;

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

// Dedicated 1-Click Route to Add All Dispatch Columns directly to MySQL Database
Route::get('/migrate-dispatch', function () {
    $results = [];
    $errors = [];

    // Columns schema definitions
    $columnsToAdd = [
        'from_date' => "ALTER TABLE `milk_dispatches` ADD COLUMN `from_date` date NULL AFTER `dispatch_number`",
        'from_shift' => "ALTER TABLE `milk_dispatches` ADD COLUMN `from_shift` varchar(20) NOT NULL DEFAULT 'Morning' AFTER `from_date`",
        'to_date' => "ALTER TABLE `milk_dispatches` ADD COLUMN `to_date` date NULL AFTER `from_shift`",
        'to_shift' => "ALTER TABLE `milk_dispatches` ADD COLUMN `to_shift` varchar(20) NOT NULL DEFAULT 'Morning' AFTER `to_date`",
        'challan_date' => "ALTER TABLE `milk_dispatches` ADD COLUMN `challan_date` date NULL AFTER `to_shift`",
        'challan_number' => "ALTER TABLE `milk_dispatches` ADD COLUMN `challan_number` varchar(50) NULL AFTER `challan_date`",
        'dispatch_type' => "ALTER TABLE `milk_dispatches` ADD COLUMN `dispatch_type` varchar(30) NOT NULL DEFAULT 'Can' AFTER `challan_number`",
        'drop_location' => "ALTER TABLE `milk_dispatches` ADD COLUMN `drop_location` varchar(191) NULL AFTER `dispatch_type`",
        'milk_type' => "ALTER TABLE `milk_dispatches` ADD COLUMN `milk_type` varchar(30) NOT NULL DEFAULT 'Cow' AFTER `drop_location`",
        'purchase_qty' => "ALTER TABLE `milk_dispatches` ADD COLUMN `purchase_qty` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `milk_type`",
        'milk_quality' => "ALTER TABLE `milk_dispatches` ADD COLUMN `milk_quality` varchar(30) NOT NULL DEFAULT 'Good' AFTER `purchase_qty`",
        'quantity_ltr' => "ALTER TABLE `milk_dispatches` ADD COLUMN `quantity_ltr` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `milk_quality`",
        'prev_balance' => "ALTER TABLE `milk_dispatches` ADD COLUMN `prev_balance` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `quantity_ltr`",
        'balance' => "ALTER TABLE `milk_dispatches` ADD COLUMN `balance` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `prev_balance`",
        'loss' => "ALTER TABLE `milk_dispatches` ADD COLUMN `loss` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `balance`",
        'clr' => "ALTER TABLE `milk_dispatches` ADD COLUMN `clr` decimal(6,2) NULL AFTER `snf`",
        'can_number' => "ALTER TABLE `milk_dispatches` ADD COLUMN `can_number` varchar(50) NULL AFTER `clr`",
        'acidity' => "ALTER TABLE `milk_dispatches` ADD COLUMN `acidity` decimal(5,2) NULL AFTER `can_number`",
        'amount' => "ALTER TABLE `milk_dispatches` ADD COLUMN `amount` decimal(12,2) NOT NULL DEFAULT '0.00' AFTER `acidity`",
        'route_name' => "ALTER TABLE `milk_dispatches` ADD COLUMN `route_name` varchar(191) NULL AFTER `amount`",
        'vehicle_in_time' => "ALTER TABLE `milk_dispatches` ADD COLUMN `vehicle_in_time` varchar(20) NULL AFTER `route_name`",
        'vehicle_out_time' => "ALTER TABLE `milk_dispatches` ADD COLUMN `vehicle_out_time` varchar(20) NULL AFTER `vehicle_in_time`",
        'seal_number' => "ALTER TABLE `milk_dispatches` ADD COLUMN `seal_number` varchar(50) NULL AFTER `vehicle_out_time`",
        'chamber_number' => "ALTER TABLE `milk_dispatches` ADD COLUMN `chamber_number` varchar(50) NULL AFTER `seal_number`",
        'headload_kms' => "ALTER TABLE `milk_dispatches` ADD COLUMN `headload_kms` decimal(8,2) NOT NULL DEFAULT '0.00' AFTER `chamber_number`",
        'difference' => "ALTER TABLE `milk_dispatches` ADD COLUMN `difference` decimal(10,2) NOT NULL DEFAULT '0.00' AFTER `headload_kms`",
    ];

    try {
        // Ensure table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('milk_dispatches')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $results[] = "Created base table 'milk_dispatches' via artisan migrate.";
        }

        // Check each column and add if missing
        foreach ($columnsToAdd as $colName => $sql) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('milk_dispatches', $colName)) {
                \Illuminate\Support\Facades\DB::statement($sql);
                $results[] = "✅ Added column: `{$colName}`";
            } else {
                $results[] = "ℹ️ Column `{$colName}` already exists.";
            }
        }

        // Run artisan migration as well
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $artisanOut = trim(\Illuminate\Support\Facades\Artisan::output());
        if ($artisanOut) {
            $results[] = "🚀 Artisan output: " . $artisanOut;
        }

        // Clear view & route cache
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $results[] = "🧹 Cache cleared successfully.";

        $currentCols = \Illuminate\Support\Facades\Schema::getColumnListing('milk_dispatches');

        return response()->json([
            'status' => 'success',
            'message' => 'All dispatch parameters added to MySQL milk_dispatches table successfully!',
            'actions_performed' => $results,
            'total_columns' => count($currentCols),
            'columns_in_table' => $currentCols,
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'partial_actions' => $results,
        ], 500, [], JSON_PRETTY_PRINT);
    }
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
        Route::get('/sample-template', [FarmerController::class, 'sampleTemplate'])->name('sample-template');
        Route::post('/bulk-import', [FarmerController::class, 'bulkImport'])->name('bulk-import');
        Route::get('/{farmer}', [FarmerController::class, 'show'])->name('show');
        Route::get('/{farmer}/edit', [FarmerController::class, 'edit'])->name('edit');
        Route::put('/{farmer}', [FarmerController::class, 'update'])->name('update');
        Route::delete('/{farmer}', [FarmerController::class, 'destroy'])->name('destroy');
        Route::post('/{farmer}/advance', [FarmerController::class, 'storeAdvance'])->name('advance.store');
    });

    // Farmer Advances & Loan Management (Matching Reference Media)
    Route::prefix('advances')->name('advances.')->group(function () {
        Route::get('/', [FarmerAdvanceController::class, 'index'])->name('index');
        Route::post('/', [FarmerAdvanceController::class, 'store'])->name('store');
        Route::put('/{advance}', [FarmerAdvanceController::class, 'update'])->name('update');
        Route::delete('/{advance}', [FarmerAdvanceController::class, 'destroy'])->name('destroy');
        Route::post('/receive', [FarmerAdvanceController::class, 'receive'])->name('receive');
        Route::get('/ledger/{farmer}', [FarmerAdvanceController::class, 'ledger'])->name('ledger');
        Route::get('/ledger/{farmer}/print', [FarmerAdvanceController::class, 'printLedger'])->name('ledger.print');
        Route::get('/print', [FarmerAdvanceController::class, 'printList'])->name('print');
        Route::post('/import', [FarmerAdvanceController::class, 'import'])->name('import');
        Route::get('/sample-template', [FarmerAdvanceController::class, 'sampleTemplate'])->name('sample-template');
    });
    Route::get('/advance-mgmt', function() {
        return redirect()->route('advances.index');
    });

    // Farmer Deductions (Matching Reference Media)
    Route::prefix('deductions')->name('deductions.')->group(function () {
        Route::get('/', [FarmerDeductionController::class, 'index'])->name('index');
        Route::post('/', [FarmerDeductionController::class, 'store'])->name('store');
        Route::put('/{deduction}', [FarmerDeductionController::class, 'update'])->name('update');
        Route::delete('/{deduction}', [FarmerDeductionController::class, 'destroy'])->name('destroy');
        Route::get('/farmer/{farmer}', [FarmerDeductionController::class, 'farmerDeductions'])->name('farmer');
        Route::post('/import', [FarmerDeductionController::class, 'import'])->name('import');
        Route::get('/sample-template', [FarmerDeductionController::class, 'sampleTemplate'])->name('sample-template');
    });

    // Farmer Invoices (Matching Reference Media)
    Route::prefix('farmer-invoices')->name('farmer-invoices.')->group(function () {
        Route::get('/', [FarmerInvoiceController::class, 'index'])->name('index');
        Route::post('/generate', [FarmerInvoiceController::class, 'generate'])->name('generate');
        Route::get('/preview/{invoice}', [FarmerInvoiceController::class, 'previewData'])->name('preview');
        Route::get('/print/{invoice}', [FarmerInvoiceController::class, 'print'])->name('print');
        Route::get('/bank-payment-print', [FarmerInvoiceController::class, 'bankPaymentPrint'])->name('bank-payment-print');
        Route::get('/export-excel', [FarmerInvoiceController::class, 'exportExcel'])->name('export-excel');
        Route::post('/bulk-delete', [FarmerInvoiceController::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/{invoice}', [FarmerInvoiceController::class, 'show'])->name('show');
        Route::delete('/{invoice}', [FarmerInvoiceController::class, 'destroy'])->name('destroy');
    });
    Route::get('/invoice', function() {
        return redirect()->route('farmer-invoices.index');
    });

    // Milk Sales & Commercial Buyers (Matching media_1791389566806.png)
    Route::prefix('milk-sales')->name('milk-sales.')->group(function () {
        Route::get('/', [MilkSaleController::class, 'index'])->name('index');
        Route::post('/', [MilkSaleController::class, 'store'])->name('store');
        Route::put('/{milkSale}', [MilkSaleController::class, 'update'])->name('update');
        Route::delete('/{milkSale}', [MilkSaleController::class, 'destroy'])->name('destroy');
        Route::get('/{milkSale}/slip', [MilkSaleController::class, 'slip'])->name('slip');
        Route::get('/buyer-rates/{buyer}', [MilkSaleController::class, 'getBuyerRates'])->name('buyer-rates');
    });
    Route::get('/milk_sale', function () {
        return redirect()->route('milk-sales.index');
    });

    // Buyers (Commercial Customers & Khata - Matching media_1791389622008.png & media_1791389718502.png)
    Route::prefix('buyers')->name('buyers.')->group(function () {
        Route::get('/', [BuyerController::class, 'index'])->name('index');
        Route::post('/', [BuyerController::class, 'store'])->name('store');
        Route::put('/{buyer}', [BuyerController::class, 'update'])->name('update');
        Route::post('/{buyer}/toggle-status', [BuyerController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{buyer}', [BuyerController::class, 'destroy'])->name('destroy');
        Route::get('/khata', [BuyerController::class, 'khata'])->name('khata');
        Route::post('/payments', [BuyerController::class, 'storePayment'])->name('payments.store');
        Route::get('/{buyer}/bill', [BuyerController::class, 'bill'])->name('bill');
    });
    Route::get('/buyer_khata', function () {
        return redirect()->route('buyers.khata');
    });

    // Drivers Management (Matching media_1791389924187.png)
    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index');
        Route::post('/', [DriverController::class, 'store'])->name('store');
        Route::put('/{driver}', [DriverController::class, 'update'])->name('update');
        Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('destroy');
    });
    Route::get('/add_driver', function () {
        return redirect()->route('drivers.index');
    });

    // Vehicles Management (Matching media_1791389598973.png)
    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');

        // Vehicle Advances (Matching media_1791389941427.png)
        Route::prefix('advances')->name('advances.')->group(function () {
            Route::get('/', [VehicleAdvanceController::class, 'index'])->name('index');
            Route::post('/', [VehicleAdvanceController::class, 'store'])->name('store');
            Route::put('/{advance}', [VehicleAdvanceController::class, 'update'])->name('update');
            Route::delete('/{advance}', [VehicleAdvanceController::class, 'destroy'])->name('destroy');
            Route::post('/repayment', [VehicleAdvanceController::class, 'receiveRepayment'])->name('repayment');
            Route::get('/sample-csv', [VehicleAdvanceController::class, 'sampleCsv'])->name('sample-csv');
            Route::post('/import-csv', [VehicleAdvanceController::class, 'importCsv'])->name('import-csv');
            Route::get('/print', [VehicleAdvanceController::class, 'print'])->name('print');
        });
    });
    Route::get('/add_vehicle', function () {
        return redirect()->route('vehicles.index');
    });
    Route::get('/vehicle_advance', function () {
        return redirect()->route('vehicles.advances.index');
    });

    // Milk Procurement & Collection
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [MilkCollectionController::class, 'index'])->name('index');
        Route::get('/create', [MilkCollectionController::class, 'create'])->name('create');
        Route::post('/', [MilkCollectionController::class, 'store'])->name('store');
        Route::put('/{collection}', [MilkCollectionController::class, 'update'])->name('update');
        Route::delete('/{collection}', [MilkCollectionController::class, 'destroy'])->name('destroy');
        Route::post('/calc-rate', [MilkCollectionController::class, 'calculateRate'])->name('calc-rate');
        Route::get('/slip/{collection}', [MilkCollectionController::class, 'slip'])->name('slip');
    });

    // Rate Charts & Rate Correction
    Route::post('rates/apply-correction', [RateChartController::class, 'applyCorrection'])->name('rates.apply-correction');
    Route::post('rates/{rate}/assign', [RateChartController::class, 'assign'])->name('rates.assign');
    Route::resource('rates', RateChartController::class)->except(['show']);

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

    // Buy Products (Purchases & Stock Inward)
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases/{purchase}/print', [PurchaseController::class, 'printSlip'])->name('purchases.print');
    Route::post('purchases/dealers/quick-create', [PurchaseController::class, 'quickCreateDealer'])->name('purchases.dealers.quick-create');

    // Product Dealers & Dealer Payments (Matching Reference media)
    Route::prefix('dealers')->name('dealers.')->group(function () {
        Route::get('/', [DealerController::class, 'index'])->name('index');
        Route::post('/', [DealerController::class, 'store'])->name('store');
        Route::get('/print', [DealerController::class, 'printList'])->name('print');
        Route::get('/{dealer}/print', [DealerController::class, 'printSlip'])->name('slip.print');
        Route::put('/{dealer}', [DealerController::class, 'update'])->name('update');
        Route::delete('/{dealer}', [DealerController::class, 'destroy'])->name('destroy');
    });
    Route::get('/food_dealer', function() {
        return redirect()->route('dealers.index');
    });

    Route::prefix('dealer-payments')->name('dealer-payments.')->group(function () {
        Route::get('/', [DealerPaymentController::class, 'index'])->name('index');
        Route::post('/', [DealerPaymentController::class, 'store'])->name('store');
        Route::get('/dues/{dealer}', [DealerPaymentController::class, 'getDues'])->name('dues');
        Route::get('/transactions/{dealer}', [DealerPaymentController::class, 'transactions'])->name('transactions');
        Route::get('/{dealerPayment}/print', [DealerPaymentController::class, 'printSlip'])->name('print');
    });
    Route::get('/food_dealer_payment', function() {
        return redirect()->route('dealer-payments.index');
    });

    // Sales Products (Product Sales to Farmers)
    Route::resource('product-sales', ProductSaleController::class);
    Route::get('product-sales/{product_sale}/print', [ProductSaleController::class, 'printSlip'])->name('product-sales.print');

    // Inventory Ledger & Bottle Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::put('/stock/{product}', [InventoryController::class, 'updateStock'])->name('stock.update');
        Route::get('/stock/{product}/details', [InventoryController::class, 'stockDetails'])->name('stock.details');
        Route::get('/stock/print', [InventoryController::class, 'printStock'])->name('stock.print');
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
        Route::get('/create', [MilkDispatchController::class, 'create'])->name('create');
        Route::post('/', [MilkDispatchController::class, 'store'])->name('store');
        Route::get('/{dispatch}/edit', [MilkDispatchController::class, 'edit'])->name('edit');
        Route::put('/{dispatch}', [MilkDispatchController::class, 'update'])->name('update');
        Route::delete('/{dispatch}', [MilkDispatchController::class, 'destroy'])->name('destroy');
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
