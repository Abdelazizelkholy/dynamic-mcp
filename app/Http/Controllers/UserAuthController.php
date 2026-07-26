<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\User\LoginRequest;
use App\Http\Resources\Admin\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Public, non-admin login — unlike Admin\AuthController::login(), this does
 * NOT require the `admin` role. Returns the user (incl. their api_key, used
 * by the `api-key` header auth on endpoints like connect/execute) + a
 * Sanctum bearer token.
 */
class UserAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('user_token')->plainTextToken;

        return ApiResponse::success([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Login successful');
    }
}
