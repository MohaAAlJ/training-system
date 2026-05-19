<?php

declare(strict_types=1);

use App\Http\Controllers\DownloadAbsorptionPaperController;
use App\Http\Controllers\DownloadTraineeFilesController;
use App\Http\Controllers\DownloadExportController;
use App\Http\Controllers\ExcelTemplateController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Livewire\Trainee\TraineeForm;
use App\Livewire\Welcome\WelcomeForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


// Override Filament's export download route to auto-delete the file after download
Route::get('/filament/exports/{export}/download', DownloadExportController::class)
    ->name('filament.exports.download')
    ->middleware(['web', 'signed:relative']);


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// =========================================================================
// PUBLIC ROUTES & GUEST REDIRECTS
// =========================================================================

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home'); // Send to dashboard if logged in
    }
    return redirect('/welcome'); // Send to welcome form if not
});

// Trainee Application Flow
Route::group(['middleware' => 'throttle:60,1'], function () {
    Route::get('/welcome', WelcomeForm::class)->name('training.welcome');
    Route::get('/welcome/form', TraineeForm::class)->name('training.form');
});

// =========================================================================
// AUTHENTICATED ROUTES (Training Management)
// =========================================================================

Route::middleware(['auth', 'throttle:60,1'])->group(function () {

    // Application Downloads
    Route::get('/applications/{application}/absorption-paper', DownloadAbsorptionPaperController::class)
        ->middleware('can:downloadAbsorptionPaper,application')
        ->name('applications.download-absorption');

    Route::get('/applications/{application}/download-trainee-files', DownloadTraineeFilesController::class)
        ->name('applications.download-trainee-files');

    // Administrative Tools
    Route::get('/backup/download', function () {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        return (new \App\Exports\BackupExport())->export();
    })->name('backup.download');

    // Excel Utilities
    Route::get('/excel-template', [ExcelTemplateController::class, 'download'])
        ->name('excel-template.download');
});

// =========================================================================
// EXTERNAL WEBHOOKS (Stateless / Manual CSFR Handling)
// =========================================================================

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook', [WhatsAppWebhookController::class, 'handle']);
});

// =========================================================================
// SYSTEM, DEBUGGING & FALLBACKS
// =========================================================================

// Error Page Testing (Only in local/debug environments usually, but kept for manual testing as requested)
Route::get('/test-error/{code?}', function ($code = 404) {
    if ($code == 500) {
        Log::error("🔴 MANUAL TEST: This is a test log from /test-error/500");
        throw new Exception("🔴 MANUAL TEST: This is a crash test!", 500);
    }

    if (!is_numeric($code) || $code < 400 || $code > 599) {
        abort(404);
    }
    abort((int)$code);
});

Route::get('/session-expired', fn() => response()->view('errors.419', [], 419))->name('session.expired');

Route::get('/404', fn() => abort(404))->name('error.404');

Route::fallback(fn() => abort(404));
