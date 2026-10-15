<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CenterAdditionDeductionService;
use App\Models\Farmer;

$farmer = Farmer::first();
$farmer->extra_rates = [
    'Incentive' => 3.0,
    'Building fund' => 50.0,
    'Grant' => 100.0,
];
$farmer->save();

$farmer->refresh();
echo "Saved extra_rates for {$farmer->name}:\n";
print_r($farmer->extra_rates);

$calc = CenterAdditionDeductionService::calculateForCollection($farmer, 10.0, 400.0);
echo "\n--- Recalculated with Farmer Wise (10 Liters, Gross ₹400) ---\n";
print_r($calc);
