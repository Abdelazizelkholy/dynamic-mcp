<?php

namespace App\Repositories\Eloquent;

use App\Models\Integration;
use App\Repositories\IntegrationRepositoryInterface;

class IntegrationRepository implements IntegrationRepositoryInterface
{
    public function all(string $search = null, int $perPage = 15)
    {
        return Integration::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function find(int $id , array $relations = [] ): ?Integration
    {
        return Integration::with($relations)->find($id);
    }


    public function create(array $data): Integration
    {
        $integration = Integration::create($data);

        if (isset($data['integration_media'])) {
            $integration->addMedia($data['integration_media'])->toMediaCollection('integration_media');
        }

        return $integration;
    }

    public function update(int $id, array $data): ?Integration
    {
        $integration = $this->find($id);
        if (!$integration) return null;

        $integration->update($data);

        if (isset($data['integration_media'])) {
            $integration->clearMediaCollection('integration_media');
            $integration->addMedia($data['integration_media'])->toMediaCollection('integration_media');
        }

        return $integration;
    }

    public function delete(int $id): bool
    {
        $integration = $this->find($id);
        if (!$integration) return false;

        return $integration->delete();
    }
}
