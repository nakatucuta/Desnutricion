<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lines = [];
$lines[] = 'started_at='.date('Y-m-d H:i:s');

try {
    $row = Illuminate\Support\Facades\DB::selectOne('SELECT 1 AS ok');
    $lines[] = 'status=ok';
    $lines[] = 'db_ok='.(string) ($row->ok ?? '1');
} catch (Throwable $e) {
    $lines[] = 'status=error';
    $lines[] = 'error='.$e->getMessage();
}

$lines[] = 'finished_at='.date('Y-m-d H:i:s');

$output = implode(PHP_EOL, $lines).PHP_EOL;
file_put_contents(__DIR__.'/../storage/logs/sql_cli_probe.log', $output);
echo $output;
