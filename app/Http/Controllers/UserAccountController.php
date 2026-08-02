<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\User\UpdateAccountRequest;
use App\Http\Resources\Admin\User\UserResource;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Self-service account management for the logged-in end-user (Sanctum),
 * as opposed to Admin\UserController which lets an admin manage other users.
 */
class UserAccountController extends Controller
{
    public function __construct(private readonly UserRepositoryInterface $repo) {}

    // GET /account
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    // PUT /account
    public function update(UpdateAccountRequest $request): JsonResponse
    {
        $user = $this->repo->update($request->user()->id, $request->validated());

        return ApiResponse::success(new UserResource($user), 'Account updated successfully.');
    }

    // DELETE /account
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $this->repo->delete($user->id);

        return ApiResponse::success(null, 'Account deleted successfully.');
    }
}
