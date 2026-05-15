<?php


namespace App\Repositories;

use App\Models\IntegrationServiceInputGroup;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceInputGroupRepositoryInterface
{
    public function allByService(int $serviceId): Collection;

    public function find(int $id): ?IntegrationServiceInputGroup;

    public function create(array $data): IntegrationServiceInputGroup;

    public function update(int $id, array $data): IntegrationServiceInputGroup;

    public function delete(int $id): bool;
}
