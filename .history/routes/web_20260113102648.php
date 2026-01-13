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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home');
    }
    return redirect('/Home/login');
});

// ========================================
// LIVEWIRE FORMS - Fully reactive components
// ========================================

// Welcome page (public landing)
Route::get('/WelcomeForm', WelcomeForm::class)->name('training.welcome');

// Trainee application form (public) - Livewire component
Route::get('/WelcomeForm/Form', TraineeForm::class)->name('training.form');

// Form submission endpoint
Route::post('/WelcomeForm/Form', [ApplicationFormController::class, 'store'])
    ->name('training.form.submit')
    ->middleware('throttle:60,1');

Route::get('/applications/{application}/absorption-paper', DownloadAbsorptionPaperController::class)
    ->middleware(['auth', 'can:downloadAbsorptionPaper,application'])
    ->name('applications.download-absorption');

// Public form data endpoints (protected with CSRF token verification + rate limiting)
Route::prefix('WelcomeForm/Form/api')->middleware('throttle:60,1')->group(function () {
    Route::get('address', [ApplicationFormController::class, 'address'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('institution', [ApplicationFormController::class, 'institution'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('major', [ApplicationFormController::class, 'major'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('major-college', [ApplicationFormController::class, 'majorCollege'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('administrative', [ApplicationFormController::class, 'administrative'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('department', [ApplicationFormController::class, 'department'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('section', [ApplicationFormController::class, 'section'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('training-type', [ApplicationFormController::class, 'trainingType'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('check-national-id', [ApplicationFormController::class, 'checkNationalId'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
    Route::get('check-existing-application', [ApplicationFormController::class, 'checkExistingApplication'])->middleware(\App\Http\Middleware\VerifyCsrfForApi::class);
});

Route::get('/test', function () {
    $s = Section::find(1);
    $s->capacity - Application::where('section_id', $s->id)->where('status', Application::STATUS_STARTED_TRAINING)->count();
});
