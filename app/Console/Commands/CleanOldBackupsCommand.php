<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanOldBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-old-backups {--days=10 : Delete backups older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete database backup files older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $days = $this->option('days');
        $this->info("Cleaning backups older than {$days} days...");

        try {
            $disk = Storage::disk('private');
            $backupPath = 'backups';
            $files = $disk->files($backupPath);

            $deletedCount = 0;
            $now = now();

            foreach ($files as $file) {
                // Skip .gitkeep
                if (str_ends_with($file, '.gitkeep')) {
                    continue;
                }

                // Extract date from filename: backup_training-system_2026-01-22_08-04-09.sql
                // Format: backup_[dbname]_YYYY-MM-DD_HH-mm-ss.sql
                if (preg_match('/(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})/', $file, $matches)) {
                    $fileDate = Carbon::createFromFormat(
                        'Y-m-d H-i-s',
                        "{$matches[1]}-{$matches[2]}-{$matches[3]} {$matches[4]}-{$matches[5]}-{$matches[6]}"
                    );

                    $ageInDays = $now->diffInDays($fileDate);

                    if ($ageInDays >= $days) {
                        $disk->delete($file);
                        $this->line("✓ Deleted: {$file} ({$ageInDays} days old)");
                        $deletedCount++;
                    }
                }
            }

            $this->info("✓ Cleanup completed. Deleted {$deletedCount} file(s).");
        } catch (\Exception $e) {
            $this->error("✗ Cleanup failed: {$e->getMessage()}");
            return;
        }
    }
}
