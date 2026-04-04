<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {


    Route::post('login', 'login');

    // OTP flow
    Route::post('send-otp', 'sendOtp');
    Route::post('reset-password', 'resetPassword');

});
