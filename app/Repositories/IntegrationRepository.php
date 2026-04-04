<?php

namespace App\Repositories;

use App\Models\Integration;

class IntegrationRepository implements IntegrationRepositoryInterface
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Integration::all();
    }

    public function find(int $id): ?Integration
    {
        return Integration::find($id);
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
