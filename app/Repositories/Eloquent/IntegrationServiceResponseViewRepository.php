<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceResponseView;
use App\Repositories\IntegrationServiceResponseViewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceResponseViewRepository implements IntegrationServiceResponseViewRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceResponseView $model) {}

    public function allByService(int $serviceId): Collection
    {
        return $this->model
            ->where('integration_service_id', $serviceId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Delete all existing then insert fresh — same pattern as headers/inputs
     */
    public function store(int $serviceId, array $views): Collection
    {
        // Delete existing
        $this->model->where('integration_service_id', $serviceId)->delete();

        // Insert fresh
        foreach ($views as $index => $view) {
            $this->model->create([
                'integration_service_id' => $serviceId,
                'key'                    => $view['key'],
                'data_type'              => $view['data_type'],
                'order'                  => $index + 1,
            ]);
        }

        return $this->allByService($serviceId);
    }

    public function delete(int $serviceId): bool
    {
        return (bool) $this->model->where('integration_service_id', $serviceId)->delete();
    }
}
