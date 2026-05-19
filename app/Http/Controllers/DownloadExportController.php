<?php

namespace App\Http\Controllers;

use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadExportController extends Controller
{
    public function __invoke(Request $request, Export $export): StreamedResponse
    {
        // Same auth logic as Filament's built-in DownloadExport controller
        abort_unless(auth(
            $request->hasValidSignature(absolute: false)
                ? $request->query('authGuard')
                : null,
        )->check(), 401);

        $user = auth(
            $request->hasValidSignature(absolute: false)
                ? $request->query('authGuard')
                : null,
        )->user();

        $exportPolicy = Gate::getPolicyFor($export::class);

        if (filled($exportPolicy) && method_exists($exportPolicy, 'view')) {
            Gate::forUser($user)->authorize('view', Arr::wrap($export));
        } else {
            abort_unless($export->user()->is($user), 403);
        }

        $format = ExportFormat::tryFrom($request->query('format'));
        abort_unless($format !== null, 404);

        // Get disk & folder before streaming
        $disk   = Storage::disk($export->file_disk);
        $folder = $export->getFileDirectory();

        // Serve the file using Filament's downloader
        $response = $format->getDownloader()($export);

        // After the response is streamed to the browser, delete the folder and DB record
        $response->headers->set('Connection', 'close');

        register_shutdown_function(function () use ($disk, $folder, $export) {
            try {
                if ($disk->exists($folder)) {
                    $disk->deleteDirectory($folder);
                }
                $export->delete();
            } catch (\Throwable) {
                // Silently fail — don't break the download
            }
        });

        return $response;
    }
}
