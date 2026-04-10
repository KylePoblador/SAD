<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::select('DESCRIBE orders');
foreach ($columns as $col) {
    echo $col->Field . ' - ' . $col->Type . PHP_EOL;
}
