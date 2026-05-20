<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $timezone = (string) config('app.timezone', 'America/Bogota');
        $backupWindowStart = '02:50';
        $backupWindowEnd = '04:30';

        $schedule->command('seguimientos:enviar-alertas')
            ->everyTenMinutes()
            ->timezone($timezone)
            ->unlessBetween($backupWindowStart, $backupWindowEnd)
            ->withoutOverlapping(30)
            ->runInBackground();
        $schedule->command('profile:purge-email-changes')
            ->dailyAt('01:20')
            ->timezone($timezone)
            ->withoutOverlapping(60);
        $schedule->command('redis:health-check')
            ->everyFiveMinutes()
            ->timezone($timezone)
            ->withoutOverlapping(4)
            ->unlessBetween($backupWindowStart, $backupWindowEnd)
            ->runInBackground();

        // Ejecuta todos los refresh de ciclos de vida una sola vez al dia (6:00 PM).
        $schedule->command('ciclosvida:daily-refresh-report')
            ->dailyAt('18:00')
            ->timezone($timezone)
            ->withoutOverlapping(1400);

        // Corta procesos stale de ciclos de vida antes de la copia de seguridad empresarial.
        $schedule->command('ciclosvida:guard-stop-stale --max-age-minutes=480')
            ->dailyAt('02:40')
            ->timezone($timezone)
            ->withoutOverlapping(30);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
