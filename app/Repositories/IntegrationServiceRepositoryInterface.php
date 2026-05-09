<?php


namespace App\Repositories;

use App\Models\IntegrationService;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceRepositoryInterface
{
    public function allByIntegration(int $integrationId, ?string $search): Collection;

    public function find(int $id): ?IntegrationService;

    public function create(array $data): IntegrationService;

    public function update(int $id, array $data): IntegrationService;

    public function delete(int $id): bool;

    public function toggleEnabled(int $id): IntegrationService;
}
