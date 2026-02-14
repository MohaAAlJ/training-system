<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to the command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run', ['--only-db' => true])
    ->dailyAt('10:00')
    ->runInBackground()
    ->name('database-backup');

Schedule::command('backup:clean')
    ->dailyAt('10:15')
    ->runInBackground()
    ->name('backup-cleanup');

// Telegram daily monitoring report
Schedule::command('telegram:daily-report')
    ->dailyAt(config('telegram.daily_report_time', '08:00'))
    ->runInBackground()
    ->name('telegram-daily-report')
    ->when(fn () => config('telegram.enabled') && config('telegram.daily_report'));
