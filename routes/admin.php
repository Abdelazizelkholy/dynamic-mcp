<?php

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

    Route::get('/', 'index')->middleware('permission:user-read');
    Route::post('/', 'store')->middleware('permission:user-create');
    Route::get('{id}', 'show')->middleware('permission:user-view');
    Route::put('{id}', 'update')->middleware('permission:user-update');
    Route::delete('{id}', 'destroy')->middleware('permission:user-delete');

});
