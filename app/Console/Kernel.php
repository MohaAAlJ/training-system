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
        // Backup database daily at 10:00 AM
        $schedule->command('backup:run', ['--only-db' => true])
            ->dailyAt('10:00')
            ->runInBackground()
            ->name('database-backup');

        // Clean old backups daily at 10:15 AM
        $schedule->command('backup:clean')
            ->dailyAt('10:15')
            ->runInBackground()
            ->name('backup-cleanup');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
