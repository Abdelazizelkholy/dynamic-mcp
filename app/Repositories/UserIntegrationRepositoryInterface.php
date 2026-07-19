<?php

namespace App\Repositories;

use App\Models\UserIntegration;
use Illuminate\Database\Eloquent\Collection;

interface UserIntegrationRepositoryInterface
{
    public function allByUser(int $userId): Collection;

    public function find(int $id): ?UserIntegration;

    public function findByUserAndIntegration(int $userId, int $integrationId): ?UserIntegration;

    public function delete(int $id): bool;
}
