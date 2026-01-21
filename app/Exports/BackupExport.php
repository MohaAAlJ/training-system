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
        $files = $backupDisk->files(env('APP_NAME', 'Laravel'));
        $latestFile = collect($files)->sort()->last();

        // 3. Return the file for download
        return response()->download($backupDisk->path($latestFile));
    }
}
