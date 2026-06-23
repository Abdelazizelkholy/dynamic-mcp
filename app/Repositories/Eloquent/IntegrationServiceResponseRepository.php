<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceResponse;
use App\Repositories\IntegrationServiceResponseRepositoryInterface;

class IntegrationServiceResponseRepository implements IntegrationServiceResponseRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceResponse $model) {}

    public function findByService(int $serviceId): ?IntegrationServiceResponse
    {
        return $this->model->where('integration_service_id', $serviceId)->first();
    }

    /**
     * Create or update the response record for a service.
     */
    public function store(int $serviceId, array $data): IntegrationServiceResponse
    {
        return $this->model->updateOrCreate(
            ['integration_service_id' => $serviceId],
            $data
        );
    }

    public function updateFilterKeys(int $serviceId, array $outputFilterKeys): IntegrationServiceResponse
    {
        $record = $this->model->updateOrCreate(
            ['integration_service_id' => $serviceId],
            ['output_filter_keys' => $outputFilterKeys]
        );

        return $record->fresh();
    }
}
