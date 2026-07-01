<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EnumController;
use App\Http\Controllers\Admin\Integration\AuthStepFlattenController;
use App\Http\Controllers\Admin\Integration\IntegrationAccountSettingController;
use App\Http\Controllers\Admin\Integration\IntegrationGlobalBodyController;
use App\Http\Controllers\Admin\Integration\IntegrationHeaderController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceHeaderController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceInputController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceInputGroupController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceParamController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceResponseController;
use App\Http\Controllers\Admin\Integration\IntegrationServiceResponseViewController;
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
    Route::post('login', 'login');
    Route::post('send-otp', 'sendOtp');
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
        Route::get('/', 'index')->middleware('permission:user_read');
        Route::post('/', 'store')->middleware('permission:user_create');
        Route::get('{id}', 'show')->middleware('permission:user_read');
        Route::put('{id}', 'update')->middleware('permission:user_update');
        Route::delete('{id}', 'destroy')->middleware('permission:user_delete');
    });

    // ── Integrations ────────────────────────────────────────────────────────
    Route::prefix('integrations')->group(function () {

        Route::controller(IntegrationController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:integration_read');
            Route::post('/', 'store')->middleware('permission:integration_create');
            Route::get('{id}', 'show')->middleware('permission:integration_read');
            Route::put('{id}', 'update')->middleware('permission:integration_update');
            Route::delete('{id}', 'destroy')->middleware('permission:integration_delete');
            Route::put('{id}/change-status', 'changeStatus')->middleware('permission:integration_update');
        });

        // ── Auth Steps ──────────────────────────────────────────────────────
        Route::prefix('{integrationId}/auth-steps')
            ->group(function () {

                Route::get('flatten-response', AuthStepFlattenController::class);

                Route::controller(IntegrationAuthStepController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::post('/', 'store');
                    Route::post('reorder', 'reorder');
                    Route::get('{id}', 'show');     // ← this was catching "flatten-response"
                    Route::put('{id}', 'update');
                    Route::delete('{id}', 'destroy');
                    Route::patch('{id}/toggle', 'toggle');
                });

            });


        Route::prefix('{integrationId}/headers')
            ->controller(IntegrationHeaderController::class)
            ->group(function () {
                Route::get('/', 'index')->middleware('permission:integration_read');
                Route::post('/', 'store')->middleware('permission:integration_create');
                Route::get('{id}', 'show')->middleware('permission:integration_read');
                Route::put('/', 'update')->middleware('permission:integration_update');
                Route::delete('{id}', 'destroy')->middleware('permission:integration_delete');
                Route::patch('{id}/toggle', 'toggle')->middleware('permission:integration_update');
            });


        Route::prefix('{integrationId}/global-body')
            ->controller(IntegrationGlobalBodyController::class)
            ->group(function () {
                Route::get('/', 'index')->middleware('permission:integration_read');
                Route::post('/', 'store')->middleware('permission:integration_create');
                Route::put('{id}', 'update')->middleware('permission:integration_update'); // ← {id} added
                Route::delete('{id}', 'destroy')->middleware('permission:integration_delete');
            });

        Route::prefix('{integrationId}/account-settings')
            ->controller(IntegrationAccountSettingController::class)
            ->group(function () {
                Route::get('/',  'show');
                Route::put('/',  'update'); // تحديث أو إنشاء في حال عدم الوجود
            });


        Route::prefix('{integrationId}/services')
            ->group(function () {

                Route::controller(IntegrationServiceController::class)->group(function () {
                    Route::get('/',                      'index');
                    Route::post('/',                     'store');
                    Route::get('available-dependencies', 'availableDependencies');
                    Route::get('{serviceId}',            'show');
                    Route::put('{serviceId}',            'update');
                    Route::delete('{serviceId}',         'destroy');
                    Route::patch('{serviceId}/toggle',   'toggle');
                });

                // ── Params ──────────────────────────────────────────────────────
                Route::prefix('{serviceId}/params')
                    ->controller(IntegrationServiceParamController::class)
                    ->group(function () {
                        Route::get('/',        'index');
                        Route::post('/',       'store');
                        Route::post('reorder', 'reorder');
                        Route::get('{id}',     'show');
                        Route::delete('{id}',  'destroy');
                    });

                // ── Headers ──────────────────────────────────────────────────────
                Route::prefix('{serviceId}/headers')
                    ->controller(IntegrationServiceHeaderController::class)
                    ->group(function () {
                        Route::get('/',             'index');
                        Route::post('/',            'store');
                        Route::put('/',             'update');
                        Route::get('{id}',          'show');
                        Route::delete('{id}',       'destroy');
                        Route::patch('{id}/toggle', 'toggle');
                    });

                // ── Input Groups with-inputs ← BEFORE {serviceId}/input-groups/{id} ──
            //    Route::post('{serviceId}/input-groups/with-inputs', [IntegrationServiceInputGroupController::class, 'storeWithInputs']);
           //     Route::put('{serviceId}/input-groups/with-inputs',  [IntegrationServiceInputGroupController::class, 'updateWithInputs']);

                // ── Input Groups ─────────────────────────────────────────────────
                Route::prefix('{serviceId}/input-groups')
                    ->controller(IntegrationServiceInputGroupController::class)
                    ->group(function () {
                        Route::get('/',       'index');
                        Route::post('/',      'store');


                        Route::post('with-inputs', 'storeWithInputs');
                        Route::put('with-inputs',  'updateWithInputs');

                        Route::get('{id}',    'show');
                        Route::put('{id}',    'update');
                        Route::delete('{id}', 'destroy');

                        Route::post('{id}/inputs', [IntegrationServiceInputController::class, 'storeInGroup']);
                    });

                // ── Standalone Inputs ← PUT '/' BEFORE PUT '{id}' ────────────────
                Route::prefix('{serviceId}/inputs')
                    ->controller(IntegrationServiceInputController::class)
                    ->group(function () {
                        Route::get('/',       'index');
                        Route::post('/',      'sync');
                        Route::put('/',       'update');   // ← array update بدون id
                        Route::get('{id}',    'show');
                        Route::put('{id}',    'updateSingle'); // ← single update بـ id
                        Route::delete('{id}', 'destroy');
                    });

                Route::prefix('{serviceId}/response')
                    ->controller(IntegrationServiceResponseController::class)
                    ->group(function () {
                        Route::get('/',            'show');
                        Route::post('/',           'store');
                        Route::put('filter-keys',  'updateFilterKeys');
                        Route::get('flatten',      'flatten');
                    });


                Route::prefix('{serviceId}/response-view')
                    ->controller(IntegrationServiceResponseViewController::class)
                    ->group(function () {
                        Route::get('/',  'index');
                        Route::post('/', 'store');    // delete all + insert fresh
                        Route::delete('/', 'destroy');
                    });

            });




    });

});
