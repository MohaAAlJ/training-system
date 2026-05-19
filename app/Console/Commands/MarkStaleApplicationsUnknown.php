<?php

namespace App\Console\Commands;

use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkStaleApplicationsUnknown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:mark-stale-unknown {--days=7 : The number of days to consider stale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark applications that have been in NEW status for more than X days as UNKNOWN.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 7;
        }

        $this->info("Starting stale application update (older than {$days} days)...");

        $staleDate = now()->subDays($days);

        // Mass update is efficient
        $count = Application::where('status', Application::STATUS_NEW)
            ->where('updated_at', '<=', $staleDate)
            ->update(['status' => Application::STATUS_UNKNOWN]); // 9

        if ($count > 0) {
            $this->info("Marked {$count} applications as UNKNOWN.");
            Log::info("Applications Cleanup: Marked {$count} applications as UNKNOWN (Status: NEW, Older than {$days} days)");
        } else {
            $this->info('No stale applications found.');
        }
    }
}
