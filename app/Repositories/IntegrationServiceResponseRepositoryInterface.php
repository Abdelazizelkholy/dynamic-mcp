<?php

namespace App\Repositories;

use App\Models\IntegrationServiceResponse;

interface IntegrationServiceResponseRepositoryInterface
{
    public function findByService(int $serviceId): ?IntegrationServiceResponse;

    public function store(int $serviceId, array $data): IntegrationServiceResponse;

    public function updateFilterKeys(int $serviceId, array $outputFilterKeys): IntegrationServiceResponse;
}
