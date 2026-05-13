<?php


namespace App\Repositories;

use App\Models\IntegrationServiceParam;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceParamRepositoryInterface
{
    public function allByService(int $serviceId): Collection;

    public function find(int $id): ?IntegrationServiceParam;

    public function create(array $data): IntegrationServiceParam;

    public function update(int $id, array $data): IntegrationServiceParam;

    public function delete(int $id): bool;

    public function reorder(int $serviceId, array $orderedIds): bool;
}
