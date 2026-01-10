<?php

/**
 * LIVEWIRE FORM ROUTES
 * 
 * Routes for the refactored Livewire-based trainee application forms.
 * 
 * These routes replace the vanilla JavaScript form implementations with
 * Livewire 3 full-page components that handle all form logic reactively.
 * 
 * Usage:
 * - Add these routes to routes/web.php
 * - Ensure Livewire is properly configured in your Laravel app
 * - View routes: http://localhost/welcome and http://localhost/trainee-form
 */

use App\Livewire\Welcome\WelcomeForm;
use App\Livewire\Trainee\TraineeForm;
use Illuminate\Support\Facades\Route;

// Welcome/Landing Page - Introduces the system and guides users
Route::get('/welcome', WelcomeForm::class)->name('welcome');

// Main Trainee Application Form - Handles complete form submission
Route::get('/trainee-form', TraineeForm::class)->name('trainee.form');

// Alternative naming conventions:
// Route::get('/WelcomeForm/Welcome', WelcomeForm::class)->name('welcome.form');
// Route::get('/WelcomeForm/Form', TraineeForm::class)->name('trainee.application');
