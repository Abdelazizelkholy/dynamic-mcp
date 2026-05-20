<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EnumController;
use App\Http\Controllers\Admin\Integration\AuthStepFlattenController;
use App\Http\Controllers\Admin\Integration\IntegrationHeaderController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceHeaderController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceInputController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceInputGroupController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceParamController;
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
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout');
    });
});
Route::get('enums', [EnumController::class, 'index']);

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
            Route::put('{id}/change-status', 'changeStatus')->middleware('permission:integration_update');
        });

        // ── Auth Steps ──────────────────────────────────────────────────────
        Route::prefix('{integrationId}/auth-steps')
            ->group(function () {

                Route::get('flatten-response', AuthStepFlattenController::class);

                Route::controller(IntegrationAuthStepController::class)->group(function () {
                    Route::get('/',             'index');
                    Route::post('/',            'store');
                    Route::post('reorder',      'reorder');
                    Route::get('{id}',          'show');     // ← this was catching "flatten-response"
                    Route::put('{id}',          'update');
                    Route::delete('{id}',       'destroy');
                    Route::patch('{id}/toggle', 'toggle');
                });

            });


        Route::prefix('{integrationId}/headers')
            ->controller(IntegrationHeaderController::class)
            ->group(function () {
                Route::get('/',             'index')  ->middleware('permission:integration_read');
                Route::post('/',            'store')  ->middleware('permission:integration_create');
                Route::get('{id}',          'show')   ->middleware('permission:integration_read');
                Route::put('{id}',          'update') ->middleware('permission:integration_update');
                Route::delete('{id}',       'destroy')->middleware('permission:integration_delete');
                Route::patch('{id}/toggle', 'toggle') ->middleware('permission:integration_update');
            });

        Route::prefix('{integrationId}/services')
            ->controller(IntegrationServiceController::class)
            ->group(function () {
                Route::get('/',                      'index');
                Route::post('/',                     'store');
                Route::get('available-dependencies', 'availableDependencies');
                Route::get('{serviceId}',            'show');
                Route::put('{serviceId}',            'update');
                Route::delete('{serviceId}',         'destroy');
                Route::patch('{serviceId}/toggle',   'toggle');

                // ── Params — nested inside services ──────────────────────────────
                Route::prefix('{serviceId}/params')
                    ->controller(IntegrationServiceParamController::class)
                    ->group(function () {
                        Route::get('/',        'index');
                        Route::post('/',       'store');
                        Route::post('reorder', 'reorder');
                        Route::get('{id}',     'show');
                        Route::put('{id}',     'update');
                        Route::delete('{id}',  'destroy');
                    });

                Route::prefix('{serviceId}/headers')
                    ->controller(IntegrationServiceHeaderController::class)
                    ->group(function () {
                        Route::get('/',             'index');
                        Route::post('/',            'store');
                        Route::get('{id}',          'show');
                        Route::put('{id}',          'update');
                        Route::delete('{id}',       'destroy');
                        Route::patch('{id}/toggle', 'toggle');
                    });

                Route::prefix('{serviceId}/input-groups')
                    ->controller(IntegrationServiceInputGroupController::class)
                    ->group(function () {
                        Route::get('/',    'index');
                        Route::post('/',   'store');
                        Route::get('{id}', 'show');
                        Route::put('{id}', 'update');
                        Route::delete('{id}', 'destroy');

                        // Inputs inside a group
                        Route::post('{id}/inputs', [IntegrationServiceInputController::class, 'storeInGroup']);
                    });

                // Standalone Inputs (not inside a group)
                Route::prefix('{serviceId}/inputs')
                    ->controller(IntegrationServiceInputController::class)
                    ->group(function () {
                        Route::get('/',       'index');
                        Route::post('/',      'store');
                        Route::get('{id}',    'show');
                        Route::put('{id}',    'update');
                        Route::delete('{id}', 'destroy');
                    });


            });



    });

});
