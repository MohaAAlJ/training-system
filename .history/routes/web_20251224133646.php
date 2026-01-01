<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationFormController;
use App\Models\Sections;
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

// Public form data endpoints (no auth)
Route::prefix('WelcomeForm/Form/api')->group(function () {
    Route::get('addresses', [ApplicationFormController::class, 'addresses']);
    Route::get('institutions', [ApplicationFormController::class, 'institutions']);
    Route::get('majors', [ApplicationFormController::class, 'majors']);
    Route::get('major-colleges', [ApplicationFormController::class, 'majorColleges']);
    Route::get('administratives', [ApplicationFormController::class, 'administratives']);
    Route::get('departments', [ApplicationFormController::class, 'departments']);
    Route::get('sections', [ApplicationFormController::class, 'sections']);
    Route::get('training-types', [ApplicationFormController::class, 'trainingTypes']);
    Route::get('check-national-id', [ApplicationFormController::class, 'checkNationalId']);
});

Route::get('/test', function(){
    $s = Sections::find(3);
    $app = Applica
    dd($s->getCapacityStats());
});