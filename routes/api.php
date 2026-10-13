<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProcurementApiController;

/*
|--------------------------------------------------------------------------
| SmartDairy API Routes (Bearer Token via Laravel Sanctum)
|--------------------------------------------------------------------------
|
| All Procurement & Farmers endpoints are protected with `auth:sanctum`
| Bearer Token authentication for Dairy Admin & authorized staff.
| Both `/api/v1/...` and `/api/...` prefixes are supported.
|
*/

$registerProcurementRoutes = function () {
    // 0. Authentication (Public Login + Protected Profile/Logout)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthApiController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthApiController::class, 'me']);
            Route::post('/logout', [AuthApiController::class, 'logout']);
        });
    });

    // Protected Procurement & Farmers Module APIs (Bearer Token Required)
    Route::middleware('auth:sanctum')->prefix('procurement')->group(function () {

        // 1. Collection History & Intake
        Route::get('/collections', [ProcurementApiController::class, 'collectionsIndex']);
        Route::get('/collections/meta', [ProcurementApiController::class, 'collectionsMeta']);
        Route::post('/collections/calculate-rate', [ProcurementApiController::class, 'collectionsCalculateRate']);
        Route::post('/collections', [ProcurementApiController::class, 'collectionsStore']);
        Route::get('/collections/{collection}', [ProcurementApiController::class, 'collectionsShow']);
        Route::put('/collections/{collection}', [ProcurementApiController::class, 'collectionsUpdate']);
        Route::delete('/collections/{collection}', [ProcurementApiController::class, 'collectionsDestroy']);

        // 2. Farmers Master
        Route::get('/farmers', [ProcurementApiController::class, 'farmersIndex']);
        Route::post('/farmers', [ProcurementApiController::class, 'farmersStore']);
        Route::get('/farmers/{farmer}', [ProcurementApiController::class, 'farmersShow']);
        Route::get('/farmers/{farmer}/passbook', [ProcurementApiController::class, 'farmersPassbook']);
        Route::put('/farmers/{farmer}', [ProcurementApiController::class, 'farmersUpdate']);
        Route::delete('/farmers/{farmer}', [ProcurementApiController::class, 'farmersDestroy']);

        // 3. Rate Charts (Fat/SNF)
        Route::get('/rate-charts', [ProcurementApiController::class, 'rateChartsIndex']);
        Route::post('/rate-charts', [ProcurementApiController::class, 'rateChartsStore']);
        Route::post('/rate-charts/apply-correction', [ProcurementApiController::class, 'rateChartsApplyCorrection']);
        Route::get('/rate-charts/{rate}', [ProcurementApiController::class, 'rateChartsShow']);
        Route::put('/rate-charts/{rate}', [ProcurementApiController::class, 'rateChartsUpdate']);
        Route::delete('/rate-charts/{rate}', [ProcurementApiController::class, 'rateChartsDestroy']);
        Route::post('/rate-charts/{rate}/assign', [ProcurementApiController::class, 'rateChartsAssign']);

        // 4. Farmer Settlements
        Route::get('/settlements', [ProcurementApiController::class, 'settlementsIndex']);
        Route::get('/settlements/preview', [ProcurementApiController::class, 'settlementsPreview']);
        Route::post('/settlements', [ProcurementApiController::class, 'settlementsStore']);
        Route::get('/settlements/{settlement}', [ProcurementApiController::class, 'settlementsShow']);
        Route::delete('/settlements/{settlement}', [ProcurementApiController::class, 'settlementsDestroy']);

        // 5. Advance & Loans
        Route::get('/advances', [ProcurementApiController::class, 'advancesIndex']);
        Route::post('/advances', [ProcurementApiController::class, 'advancesStore']);
        Route::post('/advances/receive', [ProcurementApiController::class, 'advancesReceive']);
        Route::get('/advances/ledger/{farmer}', [ProcurementApiController::class, 'advancesLedger']);
        Route::get('/advances/{advance}', [ProcurementApiController::class, 'advancesShow']);
        Route::put('/advances/{advance}', [ProcurementApiController::class, 'advancesUpdate']);
        Route::delete('/advances/{advance}', [ProcurementApiController::class, 'advancesDestroy']);

        // 6. Deductions
        Route::get('/deductions', [ProcurementApiController::class, 'deductionsIndex']);
        Route::post('/deductions', [ProcurementApiController::class, 'deductionsStore']);
        Route::get('/deductions/farmer/{farmer}', [ProcurementApiController::class, 'deductionsFarmerHistory']);
        Route::get('/deductions/{deduction}', [ProcurementApiController::class, 'deductionsShow']);
        Route::put('/deductions/{deduction}', [ProcurementApiController::class, 'deductionsUpdate']);
        Route::delete('/deductions/{deduction}', [ProcurementApiController::class, 'deductionsDestroy']);

        // 7. Farmer Invoices
        Route::get('/farmer-invoices', [ProcurementApiController::class, 'invoicesIndex']);
        Route::post('/farmer-invoices/generate', [ProcurementApiController::class, 'invoicesGenerate']);
        Route::post('/farmer-invoices/bulk-approve', [ProcurementApiController::class, 'invoicesBulkApprove']);
        Route::post('/farmer-invoices/bulk-delete', [ProcurementApiController::class, 'invoicesBulkDelete']);
        Route::get('/farmer-invoices/{invoice}', [ProcurementApiController::class, 'invoicesShow']);
        Route::get('/farmer-invoices/{invoice}/preview', [ProcurementApiController::class, 'invoicesPreview']);
        Route::post('/farmer-invoices/{invoice}/verify-save', [ProcurementApiController::class, 'invoicesVerifyAndSave']);
        Route::delete('/farmer-invoices/{invoice}', [ProcurementApiController::class, 'invoicesDestroy']);
    });
};

// Versioned API (`/api/v1/...`)
Route::prefix('v1')->group($registerProcurementRoutes);

// Root alias (`/api/...`)
$registerProcurementRoutes();

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
