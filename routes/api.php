<?php

use App\Http\Controllers\UserIntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('test2' , function(){
    echo "test";
});

/*
|--------------------------------------------------------------------------
| User Integrations — runtime auth-step execution on behalf of a user
|--------------------------------------------------------------------------
*/
Route::prefix('user-integrations')->group(function () {

    // Public — the provider (e.g. Salla) redirects the merchant's browser here directly,
    // with no bearer token attached, so this must sit outside auth:sanctum.
    Route::get('{integrationId}/callback', [UserIntegrationController::class, 'callback']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserIntegrationController::class, 'index']);
        Route::get('services/{serviceId}/status', [UserIntegrationController::class, 'status']);
        Route::get('{id}', [UserIntegrationController::class, 'show']);
        Route::post('{integrationId}/connect', [UserIntegrationController::class, 'connect']);
        Route::post('{id}/refresh', [UserIntegrationController::class, 'refresh']);
        Route::delete('{id}', [UserIntegrationController::class, 'destroy']);
    });
});
