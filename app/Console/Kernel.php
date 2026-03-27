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
        $schedule->command('seguimientos:enviar-alertas')->everyTenMinutes();

        $schedule->command('ciclosvida:cache-refresh primera_infancia')
            ->dailyAt('02:15')
            ->withoutOverlapping();

        $schedule->command('ciclosvida:cache-refresh infancia')
            ->dailyAt('02:35')
            ->withoutOverlapping();

        $schedule->command('ciclosvida:cache-refresh adolescencia')
            ->dailyAt('02:55')
            ->withoutOverlapping();

        $schedule->command('ciclosvida:cache-refresh juventud')
            ->dailyAt('03:15')
            ->withoutOverlapping();

        $schedule->command('ciclosvida:cache-refresh adultez')
            ->dailyAt('03:35')
            ->withoutOverlapping();

        $schedule->command('ciclosvida:cache-refresh vejez')
            ->dailyAt('03:55')
            ->withoutOverlapping();
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
