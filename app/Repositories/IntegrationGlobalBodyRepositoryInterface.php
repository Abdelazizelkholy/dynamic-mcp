<?php

namespace App\Repositories;

use App\Models\IntegrationGlobalBody;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationGlobalBodyRepositoryInterface
{
    public function allByIntegration(int $integrationId): Collection;

    public function find(int $id): ?IntegrationGlobalBody;

    public function create(array $data): IntegrationGlobalBody;

    public function update(int $id, array $data): IntegrationGlobalBody;

    public function replaceAll(int $integrationId, array $bodies): Collection;

    public function delete(int $id): bool;
}
