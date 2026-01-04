<?php

use App\Helpers\Constants;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationFormController;
use App\Models\Application;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return redirect('/admin/login');
});

// Welcome page (public landing)
Route::get('/WelcomeForm', [ApplicationFormController::class, 'showWelcome'])->name('training.welcome');

// Trainee application form (public)
Route::get('/WelcomeForm/Form', [ApplicationFormController::class, 'showForm'])->name('training.form');
Route::post('/WelcomeForm/Form', [ApplicationFormController::class, 'store'])
    ->name('training.form.store');

Route::get('/applications/{application}/absorption-paper', [DownloadAbsorptionPaperController::class, 'download'])
    ->name('applications.download_absorption_paper');

// Public form data endpoints (no auth)
Route::prefix('WelcomeForm/Form/api')->group(function () {
    Route::get('address', [ApplicationFormController::class, 'address']);
    Route::get('institution', [ApplicationFormController::class, 'institution']);
    Route::get('major', [ApplicationFormController::class, 'major']);
    Route::get('major-college', [ApplicationFormController::class, 'majorCollege']);
    Route::get('administrative', [ApplicationFormController::class, 'administrative']);
    Route::get('department', [ApplicationFormController::class, 'department']);
    Route::get('section', [ApplicationFormController::class, 'section']);
    Route::get('training-type', [ApplicationFormController::class, 'trainingType']);
    Route::get('check-national-id', [ApplicationFormController::class, 'checkNationalId']);
});

Route::get('/test', function () {
    $s = Section::find(1);
    $s->capacity - Application::where('section_id', $s->id)->where('status', Application::STATUS_STARTED_TRAINING)->count();
});
