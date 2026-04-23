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

        $schedule->command('seguimientos:enviar-alertas')
            ->everyTenMinutes()
            ->timezone($timezone);
        $schedule->command('profile:purge-email-changes')
            ->dailyAt('01:20')
            ->timezone($timezone)
            ->withoutOverlapping(60);

        // Ejecuta refresh recientes dos veces al dia y los deja escalonados para no saturar la base.
        $this->scheduleCicloVidaRefresh($schedule, 'primera_infancia', 2, 14, 15, $timezone);
        $this->scheduleCicloVidaRefresh($schedule, 'infancia', 2, 14, 35, $timezone);
        $this->scheduleCicloVidaRefresh($schedule, 'adolescencia', 2, 14, 55, $timezone);
        $this->scheduleCicloVidaRefresh($schedule, 'juventud', 3, 15, 15, $timezone);
        $this->scheduleCicloVidaRefresh($schedule, 'adultez', 3, 15, 35, $timezone);
        $this->scheduleCicloVidaRefresh($schedule, 'vejez', 3, 15, 55, $timezone);
    }

    protected function scheduleCicloVidaRefresh(
        Schedule $schedule,
        string $course,
        int $firstHour,
        int $secondHour,
        int $offset,
        string $timezone
    ): void {
        $schedule->command("ciclosvida:cache-refresh {$course}")
            ->twiceDailyAt($firstHour, $secondHour, $offset)
            ->timezone($timezone)
            ->withoutOverlapping(700);
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
