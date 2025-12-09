<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationFormController;

// Default route - redirect to admin
Route::get('/', function () {
    return redirect('/admin');
});

// Trainee application form (public landing)
Route::get('admin/form', [ApplicationFormController::class, 'showForm'])->name('training.form');
Route::post('admin/form', [ApplicationFormController::class, 'store'])
    ->name('training.form.store');

// Public form data endpoints (no auth)
Route::prefix('admin/form/api')->group(function () {
    Route::get('addresses', [ApplicationFormController::class, 'addresses']);
    Route::get('institutions', [ApplicationFormController::class, 'institutions']);
    Route::get('majors', [ApplicationFormController::class, 'majors']);
    Route::get('administratives', [ApplicationFormController::class, 'administratives']);
    Route::get('departments', [ApplicationFormController::class, 'departments']);
    Route::get('training-types', [ApplicationFormController::class, 'trainingTypes']);
});
