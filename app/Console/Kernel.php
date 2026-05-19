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
        // Backup database daily at 10:00 AM using custom PHP-based backup (no mysqldump required)
        $schedule->command('app:database-backup')
            ->dailyAt('10:00')
            ->runInBackground()
            ->name('database-backup');

        // Clean old backups every 10 days (delete files older than 10 days)
        $schedule->command('app:clean-old-backups')
            ->everyTenDays()
            ->at('10:15')
            ->runInBackground()
            ->name('backup-cleanup');

        // Automatically end training for expired applications
        $schedule->command('app:end-training')->dailyAt('00:01');

        // Send WhatsApp reminders X days before end
        $schedule->command('app:check-training-end-dates')->dailyAt('09:00');

        // Telegram daily monitoring report
        $schedule->command('telegram:daily-report')
            ->dailyAt(config('telegram.daily_report_time', '08:00'))
            ->runInBackground()
            ->name('telegram-daily-report')
            ->when(fn () => config('telegram.enabled') && config('telegram.daily_report'));

        // Mark stale applications as unknown daily
        $schedule->command('applications:mark-stale-unknown', ['--days' => 7])
            ->dailyAt('02:00')
            ->runInBackground();
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
