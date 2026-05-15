<?php


namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceHeader;
use App\Repositories\IntegrationServiceHeaderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceHeaderRepository implements IntegrationServiceHeaderRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceHeader $model)
    {
    }

    public function allByService(int $serviceId): Collection
    {
        return $this->model
            ->where('integration_service_id', $serviceId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationServiceHeader
    {
        return $this->model->find($id);
    }

    public function create(array $data): IntegrationServiceHeader
    {
        $data['order'] = $this->model
                ->where('integration_service_id', $data['integration_service_id'])
                ->max('order') + 1;

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationServiceHeader
    {
        $header = $this->model->findOrFail($id);
        $header->update($data);

        return $header->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleActive(int $id): IntegrationServiceHeader
    {
        $header = $this->model->findOrFail($id);
        $header->update(['is_active' => !$header->is_active]);

        return $header->fresh();
    }
}
