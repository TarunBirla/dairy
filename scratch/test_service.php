<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CenterAdditionDeductionService;
use App\Models\Farmer;

echo "--- Farmer Wise Additions ---\n";
print_r(CenterAdditionDeductionService::getFarmerWiseAdditions());

echo "\n--- Farmer Wise Deductions ---\n";
print_r(CenterAdditionDeductionService::getFarmerWiseDeductions());

echo "\n--- All Farmers Additions ---\n";
print_r(CenterAdditionDeductionService::getAllFarmersAdditions());

echo "\n--- All Farmers Deductions ---\n";
print_r(CenterAdditionDeductionService::getAllFarmersDeductions());

$farmer = Farmer::first();
echo "\n--- Collection Calculation Test (Farmer: {$farmer->name}, 10 Liters, Gross ₹400) ---\n";
$calc = CenterAdditionDeductionService::calculateForCollection($farmer, 10.0, 400.0);
print_r($calc);
