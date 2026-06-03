<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class DailyCicloVidaRefreshReport extends Command
{
    protected $signature = 'ciclosvida:daily-refresh-report
                            {--email= : Correo destino del reporte diario}
                            {--step-timeout=1800 : Timeout maximo por subproceso (segundos)}
                            {--max-runtime-minutes=480 : Tiempo maximo permitido del proceso completo (minutos)}';

    protected $description = 'Ejecuta todos los refresh de ciclos de vida una vez al dia y envia un TXT con resultados.';

    public function handle(): int
    {
        @set_time_limit(0);

        $timezone = (string) config('app.timezone', 'America/Bogota');
        $startedAt = now($timezone);
        $recipient = $this->resolveRecipient();
        $stepTimeout = max(60, (int) $this->option('step-timeout'));
        $maxRuntimeMinutes = max(60, (int) $this->option('max-runtime-minutes'));
        $lockPath = $this->lockPath();

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Correo de destino invalido para reporte diario.');

            return self::FAILURE;
        }

        if (!$this->acquireLock($lockPath, $startedAt, $maxRuntimeMinutes)) {
            return self::FAILURE;
        }

        $jobs = [
            ['label' => 'cache-refresh primera_infancia incremental', 'tokens' => ['ciclosvida:cache-refresh', 'primera_infancia', '--incremental']],
            ['label' => 'cache-refresh infancia incremental', 'tokens' => ['ciclosvida:cache-refresh', 'infancia', '--incremental']],
            ['label' => 'cache-refresh adolescencia incremental', 'tokens' => ['ciclosvida:cache-refresh', 'adolescencia', '--incremental']],
            ['label' => 'cache-refresh juventud incremental', 'tokens' => ['ciclosvida:cache-refresh', 'juventud', '--incremental']],
            ['label' => 'cache-refresh adultez incremental', 'tokens' => ['ciclosvida:cache-refresh', 'adultez', '--incremental']],
            ['label' => 'cache-refresh vejez incremental', 'tokens' => ['ciclosvida:cache-refresh', 'vejez', '--incremental']],
            ['label' => 'coverage-snapshots-refresh', 'tokens' => ['ciclosvida:coverage-snapshots-refresh', '--include-single-filters']],
        ];

        $results = [];
        $mailSent = false;
        $mailError = null;
        try {
            foreach ($jobs as $job) {
                $this->touchLock($lockPath, ['current_job' => (string) $job['label'], 'last_heartbeat' => now($timezone)->toDateTimeString()]);
                $jobStartedAt = now($timezone);
                $this->line('Ejecutando: '.$job['label']);

                $timer = microtime(true);
                [$exitCode, $output] = $this->runJobInSubprocess((array) $job['tokens'], $stepTimeout);

                $durationSeconds = (int) round(microtime(true) - $timer);
                $status = $exitCode === 0 ? 'SUCCESS' : 'FAILURE';
                $output = $this->normalizeOutput($output);

                $results[] = [
                    'label' => $job['label'],
                    'status' => $status,
                    'exit_code' => $exitCode,
                    'started_at' => $jobStartedAt->toDateTimeString(),
                    'ended_at' => now($timezone)->toDateTimeString(),
                    'duration_seconds' => $durationSeconds,
                    'output' => $output,
                ];
            }

            $finishedAt = now($timezone);
            $reportBody = $this->buildReport($results, $startedAt->toDateTimeString(), $finishedAt->toDateTimeString(), $timezone);
            $reportPath = $this->persistReport($reportBody, $startedAt);

            try {
                $this->sendReportMail($recipient, $reportBody, basename($reportPath), $startedAt, $finishedAt);
                $mailSent = true;
            } catch (\Throwable $mailException) {
                $mailError = $mailException->getMessage();
                Log::error('ciclosvida.daily_refresh_report.mail_failed', [
                    'recipient' => $recipient,
                    'error' => $mailError,
                ]);
            }

            $hasFailures = collect($results)->contains(fn (array $row) => $row['exit_code'] !== 0);
            if (!$mailSent) {
                $hasFailures = true;
            }

            if ($hasFailures) {
                $this->warn('Ejecucion diaria finalizada con fallas. Revisa el TXT local en storage/app/reports/ciclosvida.');
                if ($mailError !== null) {
                    $this->warn('Fallo envio de correo: '.$mailError);
                }

                return self::FAILURE;
            }

            $this->info('Ejecucion diaria completada y reporte enviado.');

            return self::SUCCESS;
        } finally {
            $this->releaseLock($lockPath);
        }
    }

    protected function resolveRecipient(): string
    {
        $optionEmail = trim((string) $this->option('email'));
        if ($optionEmail !== '') {
            return $optionEmail;
        }

        return (string) config('monitoring.ciclosvida_refresh.report_to', 'jsuarez@epsianaswayuu.com');
    }

    /**
     * @param array<int, string> $tokens
     * @return array{0:int,1:string}
     */
    protected function runJobInSubprocess(array $tokens, int $timeoutSeconds): array
    {
        $command = array_merge([PHP_BINARY, 'artisan'], $tokens);
        $process = new Process($command, base_path(), null, null, $timeoutSeconds);
        $process->setIdleTimeout($timeoutSeconds);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            $mode = $e->isGeneralTimeout() ? 'TOTAL' : 'IDLE';
            return [124, "TIMEOUT {$mode}: ".$e->getMessage()];
        } catch (\Throwable $e) {
            return [1, 'EXCEPTION: '.$e->getMessage()];
        }

        $exitCode = (int) ($process->getExitCode() ?? 1);
        $output = trim($process->getOutput().PHP_EOL.$process->getErrorOutput());

        return [$exitCode, $output];
    }

    protected function normalizeOutput(string $output): string
    {
        if ($output === '') {
            return '(sin salida)';
        }

        $maxLen = 2000;
        if (strlen($output) <= $maxLen) {
            return $output;
        }

        return substr($output, 0, $maxLen).' ... [truncado]';
    }

    protected function buildReport(array $results, string $startedAt, string $finishedAt, string $timezone): string
    {
        $total = count($results);
        $ok = count(array_filter($results, fn (array $row) => $row['exit_code'] === 0));
        $failed = $total - $ok;

        $lines = [
            'REPORTE DIARIO REFRESH CICLOS DE VIDA',
            'Aplicacion: '.(string) config('app.name'),
            'URL: '.(string) config('app.url'),
            'Zona horaria: '.$timezone,
            'Inicio: '.$startedAt,
            'Fin: '.$finishedAt,
            'Total procesos: '.$total,
            'Exitosos: '.$ok,
            'Fallidos: '.$failed,
            str_repeat('=', 78),
        ];

        foreach ($results as $index => $row) {
            $lines[] = '';
            $lines[] = ($index + 1).'. '.$row['label'];
            $lines[] = 'Estado: '.$row['status'];
            $lines[] = 'Exit code: '.$row['exit_code'];
            $lines[] = 'Inicio: '.$row['started_at'];
            $lines[] = 'Fin: '.$row['ended_at'];
            $lines[] = 'Duracion (s): '.$row['duration_seconds'];
            $lines[] = 'Salida:';
            $lines[] = $row['output'];
            $lines[] = str_repeat('-', 78);
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    protected function persistReport(string $body, CarbonInterface $referenceDate): string
    {
        $dir = storage_path('app/reports/ciclosvida');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $fileName = 'ciclosvida-refresh-'.$referenceDate->toDateString().'.txt';
        $path = $dir.DIRECTORY_SEPARATOR.$fileName;
        File::put($path, $body);

        return $path;
    }

    protected function sendReportMail(
        string $recipient,
        string $reportBody,
        string $reportFileName,
        CarbonInterface $startedAt,
        CarbonInterface $finishedAt
    ): void {
        $subject = '[CICLOSVIDA] Reporte diario refresh '.config('app.name').' '.$startedAt->toDateString();
        $summary = implode(PHP_EOL, [
            'Adjunto encontraras el TXT con el estado de ejecucion diario de los refresh de ciclos de vida.',
            '',
            'Inicio: '.$startedAt->toDateTimeString(),
            'Fin: '.$finishedAt->toDateTimeString(),
        ]);

        Mail::raw($summary, function ($message) use ($recipient, $subject, $reportBody, $reportFileName) {
            $message->to($recipient)->subject($subject);
            $message->attachData($reportBody, $reportFileName, ['mime' => 'text/plain']);
        });
    }

    protected function lockPath(): string
    {
        return storage_path('app/reports/ciclosvida/daily-refresh.lock');
    }

    protected function acquireLock(string $lockPath, CarbonInterface $startedAt, int $maxRuntimeMinutes): bool
    {
        $dir = dirname($lockPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (!File::exists($lockPath)) {
            $this->writeLock($lockPath, [
                'pid' => getmypid(),
                'started_at' => $startedAt->toDateTimeString(),
                'last_heartbeat' => $startedAt->toDateTimeString(),
                'host' => gethostname(),
            ]);

            return true;
        }

        $raw = json_decode((string) File::get($lockPath), true);
        $data = is_array($raw) ? $raw : [];
        $heartbeat = (string) ($data['last_heartbeat'] ?? $data['started_at'] ?? '');
        $stale = true;

        if ($heartbeat !== '') {
            try {
                $last = Carbon::parse($heartbeat);
                $stale = $last->addMinutes($maxRuntimeMinutes)->isPast();
            } catch (\Throwable $e) {
                $stale = true;
            }
        }

        if (!$stale) {
            $this->error('Ya existe un refresh diario activo (lock vigente). Se cancela para evitar superposicion.');
            return false;
        }

        $stalePid = (int) ($data['pid'] ?? 0);
        if ($stalePid > 0) {
            $killed = $this->terminatePid($stalePid);
            Log::warning('ciclosvida.daily_refresh_report.stale_lock', [
                'lock_path' => $lockPath,
                'stale_pid' => $stalePid,
                'killed' => $killed,
                'prev_lock' => $data,
            ]);
        }

        $this->writeLock($lockPath, [
            'pid' => getmypid(),
            'started_at' => $startedAt->toDateTimeString(),
            'last_heartbeat' => $startedAt->toDateTimeString(),
            'host' => gethostname(),
            'recovered_from_stale' => true,
        ]);

        return true;
    }

    /**
     * @param array<string, mixed> $append
     */
    protected function touchLock(string $lockPath, array $append): void
    {
        $data = [];
        if (File::exists($lockPath)) {
            $raw = json_decode((string) File::get($lockPath), true);
            $data = is_array($raw) ? $raw : [];
        }

        foreach ($append as $key => $value) {
            $data[(string) $key] = $value;
        }

        $this->writeLock($lockPath, $data);
    }

    protected function releaseLock(string $lockPath): void
    {
        if (File::exists($lockPath)) {
            File::delete($lockPath);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function writeLock(string $lockPath, array $data): void
    {
        File::put($lockPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function terminatePid(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }

        $exitCode = 1;
        if ($this->isWindows()) {
            @exec('taskkill /PID '.(int) $pid.' /T /F', $out, $exitCode);
            return $exitCode === 0;
        }

        @exec('kill -9 '.(int) $pid, $out, $exitCode);
        return $exitCode === 0;
    }

    protected function isWindows(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
}
