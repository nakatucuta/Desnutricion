<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$lines = [];
$lines[] = 'started_at='.date('Y-m-d H:i:s');

try {
    $rows = DB::table('user_module_permissions')
        ->select('user_id', 'module_permission_id', 'granted_by_user_id', 'created_at', 'updated_at')
        ->orderByDesc('updated_at')
        ->limit(20)
        ->get();

    $lines[] = 'status=ok';
    foreach ($rows as $row) {
        $lines[] = sprintf(
            'row user_id=%s module_permission_id=%s granted_by=%s created_at=%s updated_at=%s',
            (string) $row->user_id,
            (string) $row->module_permission_id,
            (string) ($row->granted_by_user_id ?? 'null'),
            (string) ($row->created_at ?? 'null'),
            (string) ($row->updated_at ?? 'null')
        );
    }
} catch (Throwable $e) {
    $lines[] = 'status=error';
    $lines[] = 'error='.$e->getMessage();
}

$lines[] = 'finished_at='.date('Y-m-d H:i:s');

$output = implode(PHP_EOL, $lines).PHP_EOL;
file_put_contents(__DIR__.'/../storage/logs/user_permissions_probe.log', $output);
echo $output;
