<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$add = App\Models\SystemSetting::where('key', 'center_addition_types')->value('value');
$ded = App\Models\SystemSetting::where('key', 'center_deduction_types')->value('value');

echo "center_addition_types:\n" . ($add ?: 'NULL') . "\n\n";
echo "center_deduction_types:\n" . ($ded ?: 'NULL') . "\n";
