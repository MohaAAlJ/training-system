<?php

use App\Helpers\Constants;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationFormController;
use App\Models\Application;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DownloadAbsorptionPaperController;
use App\Livewire\Welcome\WelcomeForm;
use App\Livewire\Trainee\TraineeForm;

// ========================================
// ROOT REDIRECT
// ========================================
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home'); // Send to dashboard if logged in
    }
    return redirect('/welcome'); // Send to welcome form if not
});

// Welcome page (Livewire component)
Route::get('/welcome', WelcomeForm::class)
    ->middleware('throttle:60,1')
    ->name('training.welcome');

// Trainee Application Form (Livewire handles form submission internally)
Route::get('/welcome/form', TraineeForm::class)
    ->middleware('throttle:60,1')
    ->name('training.form');
// Route::get('/form', TraineeForm::class)->name('trainee.form'); // Backwards compatible alias

// ========================================
// FILE DOWNLOADS
// ========================================
Route::get('/applications/{application}/absorption-paper', DownloadAbsorptionPaperController::class)
    ->middleware(['auth', 'can:downloadAbsorptionPaper,application', 'throttle:60,1'])
    ->name('applications.download-absorption');

// ========================================
// FORM API ENDPOINTS
// ========================================
// Protected with CSRF token verification + rate limiting (60 requests per minute)
Route::prefix('welcome/form/api')
    ->middleware(['throttle:60,1', \App\Http\Middleware\VerifyCsrfForApi::class])
    ->group(function () {
        Route::get('address', [ApplicationFormController::class, 'address']);
        Route::get('institution', [ApplicationFormController::class, 'institution']);
        Route::get('major', [ApplicationFormController::class, 'major']);
        Route::get('major-college', [ApplicationFormController::class, 'majorCollege']);
        Route::get('administrative', [ApplicationFormController::class, 'administrative']);
        Route::get('department', [ApplicationFormController::class, 'department']);
        Route::get('section', [ApplicationFormController::class, 'section']);
        Route::get('training-type', [ApplicationFormController::class, 'trainingType']);
        Route::get('check-national-id', [ApplicationFormController::class, 'checkNationalId']);
        Route::get('check-existing-application', [ApplicationFormController::class, 'checkExistingApplication']);
    });

// ========================================
// BACKUP DOWNLOAD (Admin Only)
// ========================================
Route::get('/backup/download', function () {
    // Only allow authenticated admins
    if (!Auth::check() || !Auth::user()->isAdmin()) {
        abort(403, 'Unauthorized');
    }

    $exporter = new \App\Exports\BackupExport();
    return $exporter->export();
})->middleware(['auth'])->name('backup.download');

// TEMPORARY: Excel Template Download
Route::get('/excel-template', [\App\Http\Controllers\ExcelTemplateController::class, 'download'])
    ->middleware('throttle:20,1')
    ->name('excel-template.download');

// Temporary routes to test error page designs
Route::get('/test-error/{code?}', function ($code = 404) {
    if ($code == 500) {
        // Test 1: Log Channel
        \Illuminate\Support\Facades\Log::error("🔴 MANUAL TEST: This is a test log from /test-error/500");

        // Test 2: Exception Handler
        throw new \Exception("🔴 MANUAL TEST: This is a crash test!", 500);
    }

    if (!is_numeric($code) || $code < 400 || $code > 599) {
        abort(404);
    }
    abort((int)$code);
});

// Explicit 404 route
Route::get('/404', function () {
    abort(404);
})->name('error.404');

// ========================================
// TELEGRAM WEBHOOK
// ========================================
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle']);

// ========================================
// WHATSAPP WEBHOOK
// ========================================
Route::get('/whatsapp/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle']);

// Fallback route to catch all undefined URLs and show 404
Route::fallback(function () {
    abort(404);
});

