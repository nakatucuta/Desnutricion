<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class DailyCicloVidaRefreshReport extends Command
{
    protected $signature = 'ciclosvida:daily-refresh-report
                            {--email= : Correo destino del reporte diario}';

    protected $description = 'Ejecuta todos los refresh de ciclos de vida una vez al dia y envia un TXT con resultados.';

    public function handle(): int
    {
        @set_time_limit(0);

        $timezone = (string) config('app.timezone', 'America/Bogota');
        $startedAt = now($timezone);
        $recipient = $this->resolveRecipient();

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Correo de destino invalido para reporte diario.');

            return self::FAILURE;
        }

        $jobs = [
            ['label' => 'cache-refresh primera_infancia', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'primera_infancia']],
            ['label' => 'cache-refresh infancia', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'infancia']],
            ['label' => 'cache-refresh adolescencia', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'adolescencia']],
            ['label' => 'cache-refresh juventud', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'juventud']],
            ['label' => 'cache-refresh adultez', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'adultez']],
            ['label' => 'cache-refresh vejez', 'command' => 'ciclosvida:cache-refresh', 'args' => ['course' => 'vejez']],
            ['label' => 'coverage-snapshots-refresh', 'command' => 'ciclosvida:coverage-snapshots-refresh', 'args' => ['--include-single-filters' => true]],
        ];

        $results = [];
        foreach ($jobs as $job) {
            $jobStartedAt = now($timezone);
            $this->line('Ejecutando: '.$job['label']);

            $timer = microtime(true);
            try {
                $exitCode = Artisan::call($job['command'], $job['args']);
                $output = trim((string) Artisan::output());
            } catch (\Throwable $e) {
                $exitCode = 1;
                $output = 'EXCEPTION: '.$e->getMessage();
            }

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

        $this->sendReportMail($recipient, $reportBody, basename($reportPath), $startedAt, $finishedAt);

        $hasFailures = collect($results)->contains(fn (array $row) => $row['exit_code'] !== 0);

        if ($hasFailures) {
            $this->warn('Ejecucion diaria finalizada con fallas. Revisa el TXT enviado.');

            return self::FAILURE;
        }

        $this->info('Ejecucion diaria completada y reporte enviado.');

        return self::SUCCESS;
    }

    protected function resolveRecipient(): string
    {
        $optionEmail = trim((string) $this->option('email'));
        if ($optionEmail !== '') {
            return $optionEmail;
        }

        return (string) config('monitoring.ciclosvida_refresh.report_to', 'jsuarez@epsianaswayuu.com');
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

    protected function persistReport(string $body, \Carbon\CarbonInterface $referenceDate): string
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
        \Carbon\CarbonInterface $startedAt,
        \Carbon\CarbonInterface $finishedAt
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
}
