<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$f = App\Models\Farmer::first();
$f->extra_rates = null;
$f->save();
echo "Ramesh Patel reset.\n";
