<?php

namespace App\Repositories;

use App\Models\IntegrationHeader;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationHeaderRepositoryInterface
{
    public function allByIntegration(int $integrationId): Collection;

    public function find(int $id): ?IntegrationHeader;

    public function create(array $data): IntegrationHeader;

    public function update(int $id, array $data): IntegrationHeader;

    public function delete(int $id): bool;

    public function toggleActive(int $id): IntegrationHeader;
}
