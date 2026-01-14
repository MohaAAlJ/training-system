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
        return redirect('/home');
    }
    return redirect('/home/login');
});

// ========================================
// LIVEWIRE FORMS - Fully reactive components
// ========================================

// Welcome/Landing Page
Route::get('/WelcomeForm', WelcomeForm::class)->name('training.welcome');
Route::get('/welcome', WelcomeForm::class)->name('welcome'); // Backwards compatible alias

// Trainee Application Form (Livewire handles form submission internally)
Route::get('/WelcomeForm/Form', TraineeForm::class)->name('training.form');
Route::get('/trainee-form', TraineeForm::class)->name('trainee.form'); // Backwards compatible alias

// ========================================
// FILE DOWNLOADS
// ========================================
Route::get('/applications/{application}/absorption-paper', DownloadAbsorptionPaperController::class)
    ->middleware(['auth', 'can:downloadAbsorptionPaper,application'])
    ->name('applications.download-absorption');

// ========================================
// FORM API ENDPOINTS
// ========================================
// Protected with CSRF token verification + rate limiting (60 requests per minute)
Route::prefix('WelcomeForm/Form/api')
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
