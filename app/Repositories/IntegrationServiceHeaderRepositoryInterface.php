<?php


namespace App\Repositories;

use App\Models\IntegrationServiceHeader;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceHeaderRepositoryInterface
{
    public function allByService(int $serviceId): Collection;

    public function find(int $id): ?IntegrationServiceHeader;

    public function create(array $data): IntegrationServiceHeader;

    public function update(int $id, array $data): IntegrationServiceHeader;

    public function delete(int $id): bool;

    public function toggleActive(int $id): IntegrationServiceHeader;
}
