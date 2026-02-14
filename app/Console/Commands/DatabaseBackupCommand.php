<?php

namespace App\Console\Commands;

use App\Exports\BackupExport;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database using PHP (no mysqldump required)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting database backup...');

        try {
            $exporter = new BackupExport();
            $filename = $exporter->saveToStorage();
            
            $this->info("✓ Database backup created successfully: {$filename}");
        } catch (\Exception $e) {
            $this->error("✗ Backup failed: {$e->getMessage()}");
            return;
        }
    }
}
