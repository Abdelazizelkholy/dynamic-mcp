<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Integration\IntegrationController;
use App\Http\Controllers\Admin\Integration\IntegrationAuthStepController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth (public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login',          'login');
    Route::post('send-otp',       'sendOtp');
    Route::post('reset-password', 'resetPassword');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ── Users ───────────────────────────────────────────────────────────────
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/',       'index')->middleware('permission:user_read');
        Route::post('/',      'store')->middleware('permission:user_create');
        Route::get('{id}',    'show')->middleware('permission:user_read');
        Route::put('{id}',    'update')->middleware('permission:user_update');
        Route::delete('{id}', 'destroy')->middleware('permission:user_delete');
    });

    // ── Integrations ────────────────────────────────────────────────────────
    Route::prefix('integrations')->group(function () {

        Route::controller(IntegrationController::class)->group(function () {
            Route::get('/',       'index')->middleware('permission:integration_read');
            Route::post('/',      'store')->middleware('permission:integration_create');
            Route::get('{id}',    'show')->middleware('permission:integration_read');
            Route::put('{id}',    'update')->middleware('permission:integration_update');
            Route::delete('{id}', 'destroy')->middleware('permission:integration_delete');
        });

        // ── Auth Steps ──────────────────────────────────────────────────────
        Route::prefix('{integrationId}/auth-steps')
            ->controller(IntegrationAuthStepController::class)
            ->group(function () {
                Route::get('/',             'index')->middleware('permission:integration_auth_read');
                Route::post('/',            'store')->middleware('permission:integration_auth_create');
                Route::post('reorder',      'reorder')->middleware('permission:integration_auth_update');
                Route::get('{id}',          'show')->middleware('permission:integration_auth_read');
                Route::put('{id}',          'update')->middleware('permission:integration_auth_update');
                Route::delete('{id}',       'destroy')->middleware('permission:integration_auth_delete');
                Route::patch('{id}/toggle', 'toggle')->middleware('permission:integration_auth_update');
            });
    });

});
