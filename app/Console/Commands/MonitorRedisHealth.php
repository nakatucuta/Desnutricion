<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class MonitorRedisHealth extends Command
{
    protected $signature = 'redis:health-check
                            {--dry-run : Ejecuta chequeos sin enviar correo ni guardar estado}
                            {--force-email : Fuerza envio de correo de prueba aun si esta saludable}';

    protected $description = 'Monitorea Redis (conexion + cache) y emite alertas por correo ante regresiones.';

    public function handle(): int
    {
        $now = now();
        $dryRun = (bool) $this->option('dry-run');
        $forceEmail = (bool) $this->option('force-email');
        $reminderMinutes = max(5, (int) config('monitoring.redis_health.reminder_minutes', 30));
        $state = $this->loadState();

        $probe = $this->runProbe();
        $healthy = $probe['healthy'];
        $issues = $probe['issues'];
        $details = $probe['details'];

        $statusText = $healthy ? 'healthy' : 'failed';
        $lastStatus = (string) ($state['last_status'] ?? 'unknown');

        $sendAlert = false;
        $alertKind = null;

        if ($forceEmail) {
            $sendAlert = true;
            $alertKind = $healthy ? 'forced_ok' : 'forced_failed';
        } elseif (!$healthy) {
            if ($lastStatus !== 'failed') {
                $sendAlert = true;
                $alertKind = 'failed';
            } elseif ($this->shouldSendReminder($state, $now, $reminderMinutes)) {
                $sendAlert = true;
                $alertKind = 'failed_reminder';
            }
        } elseif ($lastStatus === 'failed') {
            $sendAlert = true;
            $alertKind = 'recovered';
        }

        $logData = [
            'status' => $statusText,
            'issues' => $issues,
            'details' => $details,
            'force_email' => $forceEmail,
            'dry_run' => $dryRun,
            'alert_kind' => $alertKind,
        ];
        $this->appendHealthLog($logData);

        if ($dryRun) {
            $this->line('DRY RUN: no se enviaron correos ni se guardo estado.');
            $this->line('Status: '.$statusText);
            if ($issues !== []) {
                $this->warn('Issues: '.implode(' | ', $issues));
            }

            return $healthy ? self::SUCCESS : self::FAILURE;
        }

        if ($sendAlert) {
            $sent = $this->sendAlertEmail((string) $alertKind, $issues, $details, $now);
            if (!$sent) {
                Log::warning('redis_health_alert_skipped_no_recipients', ['alert_kind' => $alertKind]);
            }
        }

        $newState = [
            'last_status' => $statusText,
            'last_checked_at' => $now->toIso8601String(),
            'last_error' => $issues[0] ?? null,
            'last_alert_kind' => $alertKind,
            'last_alert_at' => $state['last_alert_at'] ?? null,
            'last_recovery_at' => $state['last_recovery_at'] ?? null,
        ];

        if ($sendAlert) {
            if (str_starts_with((string) $alertKind, 'failed')) {
                $newState['last_alert_at'] = $now->toIso8601String();
            }
            if ($alertKind === 'recovered') {
                $newState['last_recovery_at'] = $now->toIso8601String();
            }
        }

        $this->saveState($newState);

        if ($healthy) {
            $this->info('Redis health OK');

            return self::SUCCESS;
        }

        $this->error('Redis health FAILED: '.implode(' | ', $issues));

        return self::FAILURE;
    }

    protected function runProbe(): array
    {
        $issues = [];
        $details = [];

        $cacheDefault = (string) config('cache.default');
        $sessionDriver = (string) config('session.driver');
        $redisClient = (string) config('database.redis.client');

        $details['cache_default'] = $cacheDefault;
        $details['session_driver'] = $sessionDriver;
        $details['redis_client'] = $redisClient;

        if ($cacheDefault !== 'redis') {
            $issues[] = "cache.default={$cacheDefault} (se esperaba redis)";
        }
        if ($sessionDriver !== 'redis') {
            $issues[] = "session.driver={$sessionDriver} (se esperaba redis)";
        }

        try {
            $pong = Redis::connection()->ping();
            $details['redis_ping_raw'] = is_scalar($pong) ? (string) $pong : gettype($pong);

            if ($pong === true) {
                $details['redis_ping_ok'] = true;
            } else {
                $normalized = strtoupper(trim((string) $pong, "+ \t\n\r\0\x0B"));
                $details['redis_ping_ok'] = $normalized === 'PONG';
                if (!$details['redis_ping_ok']) {
                    $issues[] = "respuesta inesperada en redis ping: {$details['redis_ping_raw']}";
                }
            }
        } catch (\Throwable $e) {
            $issues[] = 'excepcion en redis ping: '.$e->getMessage();
            $details['redis_ping_ok'] = false;
        }

        $probeKey = 'redis_health_probe:'.bin2hex(random_bytes(8));
        $probeValue = bin2hex(random_bytes(8));
        try {
            Cache::put($probeKey, $probeValue, 60);
            $read = (string) Cache::get($probeKey, '');
            Cache::forget($probeKey);

            $details['cache_probe_write'] = true;
            $details['cache_probe_read_match'] = hash_equals($probeValue, $read);

            if (!$details['cache_probe_read_match']) {
                $issues[] = 'la verificacion de cache no coincide';
            }
        } catch (\Throwable $e) {
            $issues[] = 'excepcion en verificacion de cache: '.$e->getMessage();
            $details['cache_probe_write'] = false;
            $details['cache_probe_read_match'] = false;
        }

        return [
            'healthy' => $issues === [],
            'issues' => $issues,
            'details' => $details,
        ];
    }

    protected function shouldSendReminder(array $state, Carbon $now, int $reminderMinutes): bool
    {
        $lastAlertAt = $state['last_alert_at'] ?? null;
        if (!is_string($lastAlertAt) || trim($lastAlertAt) === '') {
            return true;
        }

        try {
            $last = Carbon::parse($lastAlertAt);
        } catch (\Throwable $e) {
            return true;
        }

        return $last->addMinutes($reminderMinutes)->lessThanOrEqualTo($now);
    }

    protected function sendAlertEmail(string $kind, array $issues, array $details, Carbon $now): bool
    {
        $to = $this->parseEmails((string) config('monitoring.redis_health.alert_to', ''));
        $cc = $this->parseEmails((string) config('monitoring.redis_health.alert_cc', ''));

        if ($to === []) {
            return false;
        }

        $subjectPrefix = match ($kind) {
            'recovered' => '[RECUPERADO]',
            'failed', 'failed_reminder' => '[ALERTA]',
            'forced_ok', 'forced_failed' => '[PRUEBA]',
            default => '[INFO]',
        };
        $subject = "{$subjectPrefix} Monitoreo Redis - ".config('app.name');

        $issueText = $issues === [] ? 'Sin problemas detectados.' : implode(PHP_EOL, array_map(fn (string $i) => '- '.$i, $issues));
        $detailRows = [
            'Cache por defecto: '.($details['cache_default'] ?? 'n/d'),
            'Driver de sesion: '.($details['session_driver'] ?? 'n/d'),
            'Cliente Redis: '.($details['redis_client'] ?? 'n/d'),
            'Ping Redis OK: '.((($details['redis_ping_ok'] ?? false) === true) ? 'SI' : 'NO'),
            'Lectura/escritura cache OK: '.((($details['cache_probe_read_match'] ?? false) === true) ? 'SI' : 'NO'),
        ];
        $detailText = implode(PHP_EOL, array_map(fn (string $row) => '- '.$row, $detailRows));

        $body = implode(PHP_EOL, [
            "Fecha y hora: {$now->toDateTimeString()}",
            "Aplicacion: ".config('app.name'),
            "URL: ".config('app.url'),
            "Tipo de alerta: ".$this->kindToSpanish($kind),
            '',
            'Problemas:',
            $issueText,
            '',
            'Detalles:',
            $detailText ?: '{}',
        ]);

        Mail::raw($body, function ($message) use ($to, $cc, $subject) {
            $message->to($to)->subject($subject);
            if ($cc !== []) {
                $message->cc($cc);
            }
        });

        return true;
    }

    protected function kindToSpanish(string $kind): string
    {
        return match ($kind) {
            'failed' => 'Redis con falla',
            'failed_reminder' => 'Redis con falla (recordatorio)',
            'recovered' => 'Redis recuperado',
            'forced_ok' => 'Prueba manual (estado saludable)',
            'forced_failed' => 'Prueba manual (estado con falla)',
            default => 'Informativo',
        };
    }

    protected function parseEmails(string $raw): array
    {
        $parts = preg_split('/[;,]+/', $raw) ?: [];
        $emails = [];
        foreach ($parts as $part) {
            $email = trim((string) $part);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = strtolower($email);
            }
        }

        return array_values(array_unique($emails));
    }

    protected function loadState(): array
    {
        $path = storage_path('app/monitor/redis-health-state.json');
        if (!File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function saveState(array $state): void
    {
        $path = storage_path('app/monitor/redis-health-state.json');
        $dir = dirname($path);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $payload = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put($path, $payload !== false ? $payload : '{}');
    }

    protected function appendHealthLog(array $data): void
    {
        $path = storage_path('logs/redis-health.log');
        $dir = dirname($path);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $line = '['.now()->toDateTimeString().'] '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::append($path, $line.PHP_EOL);

        if (($data['status'] ?? null) === 'failed') {
            Log::warning('redis_health_failed', $data);
        }
    }
}
