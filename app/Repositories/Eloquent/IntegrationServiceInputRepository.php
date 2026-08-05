<?php


namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceInput;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceInputRepository implements IntegrationServiceInputRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceInput $model)
    {
    }

public function standaloneByService(int $serviceId): Collection
{
    return $this->model
        ->with('dynamicService:id,service_name_en,service_name_ar')
        ->where('integration_service_id', $serviceId)
        ->whereNull('group_id')
        ->orderBy('order')
        ->get();
}

public function byGroup(int $groupId): Collection
{
    return $this->model
        ->with('dynamicService:id,service_name_en,service_name_ar')
        ->where('group_id', $groupId)
        ->orderBy('order')
        ->get();
}

public function find(int $id): ?IntegrationServiceInput
{
    return $this->model
        ->with('dynamicService:id,service_name_en,service_name_ar')
        ->find($id);
}

    public function create(array $data): IntegrationServiceInput
    {
        $data['order'] = $this->model
                ->where('integration_service_id', $data['integration_service_id'])
                ->when(
                    isset($data['group_id']),
                    fn($q) => $q->where('group_id', $data['group_id']),
                    fn($q) => $q->whereNull('group_id')
                )
                ->max('order') + 1;

        // label is never a free-standing string — it must always mirror `field_type`.
        $data['label'] = $data['field_type'];

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationServiceInput
    {
        $input = $this->model->findOrFail($id);

        // label is never a free-standing string — it must always mirror `field_type`.
        $data['label'] = $data['field_type'] ?? $input->field_type;

        $input->update($data);

        return $input->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

}
