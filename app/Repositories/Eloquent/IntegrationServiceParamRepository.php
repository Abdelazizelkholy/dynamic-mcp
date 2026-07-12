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

    public function create(array $data): \Illuminate\Database\Eloquent\Collection
    {
        // type = params rows are owned by IntegrationServiceInput::syncParam() —
        // never wipe or recreate them here, only static/user_integration params.
        $this->model
            ->where('integration_service_id', $data['integration_service_id'])
            ->where('type', '!=', 'params')
            ->delete();

        foreach ($data['params'] as $index => $param) {
            if (($param['type'] ?? null) === 'params') {
                continue;
            }

            $this->model->create([
                'integration_service_id' => $data['integration_service_id'],
                'type'                   => $param['type'],
                'value'                  => $param['value'] ?? null,
                'order'                  => $index + 1,
            ]);
        }

        return $this->allByService($data['integration_service_id']);
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
