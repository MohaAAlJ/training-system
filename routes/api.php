<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\MohApplicationController;

Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [ApiAuthController::class, 'login']);

    // Protected MOH Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/moh/applications', MohApplicationController::class);
    });
});
