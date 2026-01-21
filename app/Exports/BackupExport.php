<?php

namespace App\Exports;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupExport
{
    public function export(): BinaryFileResponse
    {
        // 1. Run the backup command
        Artisan::call('backup:run', ['--only-db' => true]);

        // 2. Find the latest backup file
        $backupDisk = \Illuminate\Support\Facades\Storage::disk('backup');

        // Backup package usually stores in a subfolder named after APP_NAME
        // We forced the name to 'training-system' in config/backup.php to avoid Arabic character issues
        $appName = 'training-system';

        $files = $backupDisk->files($appName);

        if (empty($files)) {
             // Fallback: check root directory if no subfolder
             $files = $backupDisk->files('/');
        }

        $latestFile = collect($files)->sort()->last();

        if (!$latestFile) {
            abort(404, 'No backup file found.');
        }

        // 3. Return the file for download
        return response()->download($backupDisk->path($latestFile));
    }
}
