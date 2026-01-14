<?php

/**
 * LIVEWIRE FORM ROUTES - REFACTORED
 * 
 * Best practices routes for Livewire-based trainee application forms.
 * 
 * ROUTE STRUCTURE:
 * ├── Welcome/Landing Page
 * │   ├── /WelcomeForm (primary)
 * │   └── /welcome (backwards compatible)
 * ├── Trainee Application Form
 * │   ├── /WelcomeForm/Form (primary)
 * │   └── /trainee-form (backwards compatible)
 * └── Form API Endpoints
 *     └── /WelcomeForm/Form/api/* (validation & data endpoints)
 * 
 * NOTE: Form submission is handled entirely by Livewire internally.
 * No separate POST routes are needed.
 * 
 * MIGRATION NOTES:
 * If migrating from vanilla JS:
 * - Old routes still work via backwards compatible aliases
 * - Old POST route no longer needed - Livewire handles it
 * - All validation is now reactive (real-time)
 * - File uploads handled by Livewire's WithFileUploads trait
 */

use App\Livewire\Welcome\WelcomeForm;
use App\Livewire\Trainee\TraineeForm;
use Illuminate\Support\Facades\Route;

// ========================================
// WELCOME/LANDING PAGE
// ========================================
Route::get('/welcome', WelcomeForm::class)->name('welcome');
Route::get('/WelcomeForm', WelcomeForm::class)->name('training.welcome');

// ========================================
// TRAINEE APPLICATION FORM
// ========================================
Route::get('/trainee-form', TraineeForm::class)->name('trainee.form');
Route::get('/WelcomeForm/Form', TraineeForm::class)->name('training.form');

/**
 * ALTERNATIVE NAMING CONVENTIONS:
 * 
 * If you prefer consistency with other routes, you could use:
 * Route::get('/applications/create', TraineeForm::class)->name('applications.create');
 * Route::get('/applications/welcome', WelcomeForm::class)->name('applications.welcome');
 * 
 * Or RESTful style:
 * Route::resource('applications', ApplicationController::class);
 * But keep the /WelcomeForm/* routes for backwards compatibility.
 */

