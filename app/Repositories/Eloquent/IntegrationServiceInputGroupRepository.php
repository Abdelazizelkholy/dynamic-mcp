<?php


namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceInputGroup;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceInputGroupRepository implements IntegrationServiceInputGroupRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceInputGroup $model)
    {
    }

    public function allByService(int $serviceId): Collection
    {
        return $this->model
            ->with('inputs')   // eager load child inputs
            ->where('integration_service_id', $serviceId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationServiceInputGroup
    {
        return $this->model->with('inputs')->find($id);
    }

    public function create(array $data): IntegrationServiceInputGroup
    {
        $data['order'] = $this->model
                ->where('integration_service_id', $data['integration_service_id'])
                ->max('order') + 1;

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationServiceInputGroup
    {
        $group = $this->model->findOrFail($id);
        $group->update($data);

        return $group->fresh('inputs');
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
