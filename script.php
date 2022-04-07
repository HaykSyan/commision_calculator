<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

$reader = new App\Reader($argv[1]);
$data = $reader->load();

$user = new App\User($data);
$commissionFee = $user->calculate();

foreach ($commissionFee as $one) {
    echo $one.PHP_EOL;
}
