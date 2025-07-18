<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // expire à minuit
        $schedule->command('contracts:expire')->dailyAt('00:00');

        // active à minuit
        $schedule->command('contracts:activate')->dailyAt('00:00');

        // factures le 1er de chaque mois
        $schedule->command('generate:invoices')
            ->monthlyOn(1, '00:00')
            ->appendOutputTo(storage_path('logs/generation_factures.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
