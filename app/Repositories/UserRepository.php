<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return User::all();
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        $user = User::create($data);

        if (!empty($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['profile_picture']) && $data['profile_picture'] instanceof \Illuminate\Http\UploadedFile) {
            $user->addMedia($data['profile_picture'])->toMediaCollection('profile_picture');
        }

        return $user;
    }

    public function update(int $id, array $data): ?User
    {
        $user = $this->find($id);
        if (!$user) return null;

        if(isset($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['profile_picture']) && $data['profile_picture'] instanceof \Illuminate\Http\UploadedFile) {
            $user->clearMediaCollection('profile_picture');
            $user->addMedia($data['profile_picture'])->toMediaCollection('profile_picture');
        }

        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) return false;

        return $user->delete();
    }
}
