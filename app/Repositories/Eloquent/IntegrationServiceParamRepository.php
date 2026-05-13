<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationServiceParam;
use App\Repositories\IntegrationServiceParamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceParamRepository implements IntegrationServiceParamRepositoryInterface
{
    public function __construct(private readonly IntegrationServiceParam $model) {}

    // ── Read ───────────────────────────────────────────────────────────────────

    public function allByService(int $serviceId): Collection
    {
        return $this->model
            ->where('integration_service_id', $serviceId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationServiceParam
    {
        return $this->model->find($id);
    }

    // ── Write ──────────────────────────────────────────────────────────────────

    public function create(array $data): IntegrationServiceParam
    {
        $data['order'] = $this->model
                ->where('integration_service_id', $data['integration_service_id'])
                ->max('order') + 1;

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationServiceParam
    {
        $param = $this->model->findOrFail($id);
        $param->update($data);

        return $param->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    // ── Reorder ────────────────────────────────────────────────────────────────

    public function reorder(int $serviceId, array $orderedIds): bool
    {
        foreach ($orderedIds as $position => $paramId) {
            $this->model
                ->where('id', $paramId)
                ->where('integration_service_id', $serviceId)
                ->update(['order' => $position + 1]);
        }

        return true;
    }
}
