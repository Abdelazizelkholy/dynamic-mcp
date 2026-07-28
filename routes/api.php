<?php

use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserIntegrationController;
use Illuminate\Support\Facades\Route;

Route::get('test2' , function(){
    echo "test";
});

// Public, non-admin login — returns the user (incl. api_key) + a bearer token.
Route::post('login', [UserAuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| User Integrations — runtime auth-step execution on behalf of a user
|--------------------------------------------------------------------------
*/
Route::prefix('user-integrations')->group(function () {

    // Public — the provider (e.g. Salla) redirects the merchant's browser here directly,
    // with no bearer token attached, so this must sit outside auth:sanctum.
    Route::get('{integrationId}/callback', [UserIntegrationController::class, 'callback']);

    // Authenticated via `api-key` header instead of Authorization: Bearer.
    // `connect` also doubles as the refresh entrypoint: if the user is already
    // connected to this integration, it re-runs the refresh_token step instead.
    Route::middleware('api-key')->group(function () {
        Route::post('{integrationId}/connect', [UserIntegrationController::class, 'connect']);
        Route::get('services/{serviceId}/execute', [UserIntegrationController::class, 'execute']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserIntegrationController::class, 'index']);
        Route::get('{id}', [UserIntegrationController::class, 'show']);
        Route::delete('{id}', [UserIntegrationController::class, 'destroy']);
    });
});
