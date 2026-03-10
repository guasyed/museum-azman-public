<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";

$exitCode = $kernel->call('storage:link');

echo $kernel->output();

echo "Exit code: ".$exitCode;

echo "</pre>";