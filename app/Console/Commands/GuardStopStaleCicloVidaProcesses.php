<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class GuardStopStaleCicloVidaProcesses extends Command
{
    protected $signature = 'ciclosvida:guard-stop-stale
                            {--max-age-minutes=480 : Edad maxima permitida para proceso de refresh (minutos)}
                            {--dry-run : Solo reporta, no mata procesos}';

    protected $description = 'Detiene procesos stale de refresh de ciclos de vida para evitar interferencias operativas.';

    public function handle(): int
    {
        $maxAgeMinutes = max(30, (int) $this->option('max-age-minutes'));
        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();
        $targets = [
            'ciclosvida:daily-refresh-report',
            'ciclosvida:cache-refresh',
            'ciclosvida:coverage-snapshots-refresh',
        ];

        $processes = $this->listPhpProcesses();
        $killed = 0;
        $found = 0;

        foreach ($processes as $proc) {
            $pid = (int) ($proc['pid'] ?? 0);
            $cmd = (string) ($proc['command'] ?? '');
            $createdAt = $this->parseCreationDate((string) ($proc['created_at'] ?? ''));
            if ($pid <= 0 || $createdAt === null) {
                continue;
            }
            if ($pid === getmypid()) {
                continue;
            }

            $matchesTarget = false;
            foreach ($targets as $needle) {
                if (stripos($cmd, $needle) !== false) {
                    $matchesTarget = true;
                    break;
                }
            }
            if (!$matchesTarget) {
                continue;
            }

            $found++;
            $ageMinutes = $createdAt->diffInMinutes($now);
            if ($ageMinutes < $maxAgeMinutes) {
                continue;
            }

            $message = "Proceso stale detectado PID={$pid} edad_min={$ageMinutes}";
            $this->warn($message);
            Log::warning('ciclosvida.guard_stop_stale.detected', [
                'pid' => $pid,
                'age_minutes' => $ageMinutes,
                'command' => $cmd,
                'dry_run' => $dryRun,
            ]);

            if ($dryRun) {
                continue;
            }

            if ($this->terminatePid($pid)) {
                $killed++;
                $this->info("Proceso PID={$pid} finalizado.");
                Log::warning('ciclosvida.guard_stop_stale.killed', [
                    'pid' => $pid,
                    'age_minutes' => $ageMinutes,
                    'command' => $cmd,
                ]);
            } else {
                $this->error("No se pudo finalizar PID={$pid}.");
            }
        }

        $this->cleanupStaleLock($maxAgeMinutes, $dryRun);
        $this->info("Guardia finalizada. Procesos objetivo encontrados={$found}, finalizados={$killed}.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{pid:int,command:string,created_at:string}>
     */
    protected function listPhpProcesses(): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return [];
        }

        $ps = <<<'PS'
Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" |
ForEach-Object {
    [PSCustomObject]@{
        ProcessId = $_.ProcessId
        CommandLine = $_.CommandLine
        CreationDate = if ($_.CreationDate) { $_.CreationDate.ToString('o') } else { $null }
    }
} | ConvertTo-Json -Compress
PS;

        $shell = File::exists('C:\Program Files\PowerShell\7\pwsh.exe')
            ? 'C:\Program Files\PowerShell\7\pwsh.exe'
            : 'powershell.exe';

        $process = new Process([$shell, '-NoProfile', '-Command', $ps], base_path(), null, null, 30);
        $process->run();
        if (!$process->isSuccessful()) {
            return [];
        }

        $decoded = json_decode((string) $process->getOutput(), true);
        if (!is_array($decoded)) {
            return [];
        }

        $items = isset($decoded['ProcessId']) ? [$decoded] : $decoded;
        $rows = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $row = [
                'pid' => (int) ($item['ProcessId'] ?? 0),
                'command' => (string) ($item['CommandLine'] ?? ''),
                'created_at' => (string) ($item['CreationDate'] ?? ''),
            ];
            if ($row['pid'] > 0) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    protected function parseCreationDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function terminatePid(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        $exitCode = 1;
        if (DIRECTORY_SEPARATOR === '\\') {
            @exec('taskkill /PID '.(int) $pid.' /T /F', $out, $exitCode);
            return $exitCode === 0;
        }

        @exec('kill -9 '.(int) $pid, $out, $exitCode);
        return $exitCode === 0;
    }

    protected function cleanupStaleLock(int $maxAgeMinutes, bool $dryRun): void
    {
        $lockPath = storage_path('app/reports/ciclosvida/daily-refresh.lock');
        if (!File::exists($lockPath)) {
            return;
        }

        $raw = json_decode((string) File::get($lockPath), true);
        $data = is_array($raw) ? $raw : [];
        $heartbeat = (string) ($data['last_heartbeat'] ?? $data['started_at'] ?? '');
        if ($heartbeat === '') {
            return;
        }

        try {
            $last = Carbon::parse($heartbeat);
        } catch (\Throwable $e) {
            return;
        }

        if ($last->addMinutes($maxAgeMinutes)->isFuture()) {
            return;
        }

        if ($dryRun) {
            $this->warn("Lock stale detectado en {$lockPath} (dry-run, no se elimina).");
            return;
        }

        File::delete($lockPath);
        $this->warn("Lock stale eliminado: {$lockPath}");
        Log::warning('ciclosvida.guard_stop_stale.lock_deleted', ['lock_path' => $lockPath, 'lock' => $data]);
    }
}
