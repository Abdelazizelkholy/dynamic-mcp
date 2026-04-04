<?php

use App\Http\Resources\Admin\Integration\IntegrationCollection;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    // OTP flow
    Route::post('send-otp', 'sendOtp');
    Route::post('reset-password', 'resetPassword');

});

Route::middleware(['auth:sanctum'])->prefix('users')->controller(UserController::class)->group(function () {

    Route::get('/', 'index')->middleware('permission:user_read');
    Route::post('/', 'store')->middleware('permission:user_create');
    Route::get('{id}', 'show')->middleware('permission:user_read');
    Route::put('{id}', 'update')->middleware('permission:user_update');
    Route::delete('{id}', 'destroy')->middleware('permission:user_delete');

});


Route::middleware(['auth:sanctum'])->prefix('integrations')->controller(IntegrationCollection::class)->group(function () {

    Route::get('/', 'index')->middleware('permission:integration_read');
    Route::post('/', 'store')->middleware('permission:integration_create');
    Route::get('{id}', 'show')->middleware('permission:integration_read');
    Route::put('{id}', 'update')->middleware('permission:integration_update');
    Route::delete('{id}', 'destroy')->middleware('permission:integration_delete');

});
