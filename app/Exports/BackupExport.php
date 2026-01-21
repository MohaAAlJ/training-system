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

        // We look specifically in the 'training-system' folder
        $appName = 'training-system';
        $files = $backupDisk->files($appName);

        // Fallback: Check root if folder logic fails
        if (empty($files)) {
             $files = $backupDisk->files('/');
        }

        $latestFile = collect($files)->sort()->last();

        // if (!$latestFile) {
        //     // This aborts nicely instead of crashing with TypeError
        //     abort(404, 'No backup file was created. Please check logs.');
        // }

        // 3. Return the file for download
        return response()->download($backupDisk->path($latestFile));
    }
}
