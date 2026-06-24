<?php

namespace App\Repositories;

use App\Models\IntegrationServiceResponseView;
use Illuminate\Database\Eloquent\Collection;

interface IntegrationServiceResponseViewRepositoryInterface
{
    public function allByService(int $serviceId): Collection;

    public function store(int $serviceId, array $views): Collection;

    public function delete(int $serviceId): bool;
}
