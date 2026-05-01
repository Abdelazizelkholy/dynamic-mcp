<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationHeader;
use App\Repositories\IntegrationHeaderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationHeaderRepository implements IntegrationHeaderRepositoryInterface
{
    public function __construct(private readonly IntegrationHeader $model) {}

    public function allByIntegration(int $integrationId): Collection
    {
        return $this->model
            ->where('integration_id', $integrationId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationHeader
    {
        return $this->model->find($id);
    }

    public function create(array $data): IntegrationHeader
    {
        $data['order'] = $this->model
                ->where('integration_id', $data['integration_id'])
                ->max('order') + 1;

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationHeader
    {
        $header = $this->model->findOrFail($id);
        $header->update($data);

        return $header->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function toggleActive(int $id): IntegrationHeader
    {
        $header = $this->model->findOrFail($id);
        $header->update(['is_active' => ! $header->is_active]);

        return $header->fresh();
    }
}
