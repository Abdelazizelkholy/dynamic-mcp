<?php

namespace App\Http\Controllers\Admin;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\SendOtpRequest;
use App\Mail\SendOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }


    public function sendOtp(SendOtpRequest $request)
    {
        $otp = rand(100000, 999999);

        PasswordResetOtp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]
        );

      //  Mail::to($request->email)->send(new SendOtpMail($otp));

      //  return ApiResponse::success(null, 'OTP sent to your email');

        return ApiResponse::success([
            'otp' => $otp,
        ], 'OTP sent to your email');
    }


    public function resetPassword(ResetPasswordRequest $request)
    {
        $record = PasswordResetOtp::byEmail($request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record) {
            return ApiResponse::error('Invalid OTP', 400);
        }

        if ($record->isExpired()) {
            return ApiResponse::error('OTP expired', 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $record->delete();

        return ApiResponse::success(null, 'Password reset successful');
    }

}
