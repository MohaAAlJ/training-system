<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\User;
use App\Services\Telegram\TelegramMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SendTelegramDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily statistics report to Telegram';

    /**
     * Execute the console command.
     */
    public function handle(TelegramMonitorService $telegram): int
    {
        $this->info('Generating daily report...');

        $today = Carbon::today();

        // Gather statistics
        $stats = [
            'new_applications' => Application::whereDate('created_at', $today)->count(),
            'finished_training' => Application::where('status', Application::STATUS_ENDED_TRAINING)
                ->whereDate('updated_at', $today)
                ->count(),
            // Estimating online users via active sessions in the last 2 hours
            // This requires the 'sessions' table to be active and populated
            'online_users' => \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', now()->subHours(24)->timestamp)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),
            'errors_count' => Cache::get('telegram_errors_today', 0),
        ];

        // Send report
        $success = $telegram->sendDailyReport($stats);

        if ($success) {
            $this->info('Daily report sent successfully!');

            // Reset daily error counter
            Cache::forget('telegram_errors_today');

            return Command::SUCCESS;
        }

        $this->error('Failed to send daily report');
        return Command::FAILURE;
    }
}
