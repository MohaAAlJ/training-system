<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;

// Locale switcher
Route::post('/set-locale', function (Request $request) {
	$locale = $request->input('locale', 'id');
	if (in_array($locale, ['id', 'en', 'ar'])) {
		session(['locale' => $locale]);
		app()->setLocale($locale);
	}
	return back();
})->name('set-locale');

Route::get('/', [HomeController::class, 'index'])->name('home');
