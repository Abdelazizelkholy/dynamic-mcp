<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationGlobalBody;
use App\Repositories\IntegrationGlobalBodyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationGlobalBodyRepository implements IntegrationGlobalBodyRepositoryInterface
{
    public function __construct(private readonly IntegrationGlobalBody $model) {}

    public function allByIntegration(int $integrationId): Collection
    {
        return $this->model
            ->where('integration_id', $integrationId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationGlobalBody
    {
        return $this->model->find($id);
    }

    public function create(array $data): IntegrationGlobalBody
    {
        return $this->model->create($data);
    }

    /**
     * Delete all existing bodies for this integration then insert new ones.
     * Used for both store() and update() — always a clean replace.
     */
    public function replaceAll(int $integrationId, array $bodies): Collection
    {
        // Delete existing
        $this->model->where('integration_id', $integrationId)->delete();

        // Insert new with order
        foreach ($bodies as $index => $bodyData) {
            $this->model->create(array_merge($bodyData, [
                'integration_id' => $integrationId,
                'order'          => $index + 1,
            ]));
        }

        return $this->allByIntegration($integrationId);
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
