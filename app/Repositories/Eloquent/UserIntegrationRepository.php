<?php

namespace App\Repositories\Eloquent;

use App\Models\UserIntegration;
use App\Repositories\UserIntegrationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserIntegrationRepository implements UserIntegrationRepositoryInterface
{
    public function __construct(private readonly UserIntegration $model) {}

    public function allByUser(int $userId): Collection
    {
        return $this->model
            ->with(['integration:id,name,category', 'info'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function find(int $id): ?UserIntegration
    {
        return $this->model->with(['integration:id,name,category', 'info'])->find($id);
    }

    public function findByUserAndIntegration(int $userId, int $integrationId): ?UserIntegration
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('integration_id', $integrationId)
            ->first();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
