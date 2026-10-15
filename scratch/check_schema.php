<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('farmers');
echo "Columns in farmers table:\n" . implode("\n", $cols) . "\n";
