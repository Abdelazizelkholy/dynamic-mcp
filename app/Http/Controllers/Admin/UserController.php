<?php

namespace App\Http\Controllers\Admin;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\User\UserCollection;
use App\Http\Resources\Admin\User\UserResource;
use App\Repositories\UserRepositoryInterface;

class UserController extends Controller
{
    public function __construct(private UserRepositoryInterface $userRepo) {}

    public function index()
    {
        $users = $this->userRepo->all();
        return ApiResponse::success(new UserCollection($users));
    }

    public function store(StoreUserRequest $request)
    {
        dd(1);
        $user = $this->userRepo->create($request->validated());
        return ApiResponse::success(new UserResource($user), 'User created successfully');
    }

    public function show($id)
    {
        $user = $this->userRepo->find($id);
        if (!$user) return ApiResponse::error('User not found', 404);

        return ApiResponse::success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userRepo->update($id, $request->validated());
        if (!$user) return ApiResponse::error('User not found', 404);

        return ApiResponse::success(new UserResource($user), 'User updated successfully');
    }

    public function destroy($id)
    {
        $deleted = $this->userRepo->delete($id);
        if (!$deleted) return ApiResponse::error('User not found', 404);

        return ApiResponse::success(null, 'User deleted successfully');
    }
}
