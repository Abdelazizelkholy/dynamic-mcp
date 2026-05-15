<?php


namespace App\Repositories;

use App\Models\IntegrationServiceInput;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceInputRepositoryInterface
{
    // All standalone inputs for a service
    public function standaloneByService(int $serviceId): Collection;

    // All inputs inside a specific group
    public function byGroup(int $groupId): Collection;

    public function find(int $id): ?IntegrationServiceInput;

    public function create(array $data): IntegrationServiceInput;

    public function update(int $id, array $data): IntegrationServiceInput;

    public function delete(int $id): bool;
}
