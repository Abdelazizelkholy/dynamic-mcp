<?php


namespace App\Repositories\Eloquent;

use App\Models\IntegrationService;
use App\Repositories\IntegrationServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationServiceRepository implements IntegrationServiceRepositoryInterface
{
    public function __construct(private readonly IntegrationService $model)
    {
    }

    // ── Read ───────────────────────────────────────────────────────────────────

    public function allByIntegration(int $integrationId, ?string $search = null): Collection
    {
        return $this->model
            ->where('integration_id', $integrationId)
            ->when($search, fn($q) => $q->where('service_name', 'like', "%{$search}%"))
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationService
    {
        return $this->model->find($id);
    }

    // ── Write ──────────────────────────────────────────────────────────────────

    public function create(array $data): IntegrationService
    {
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        $data['order'] = $this->model
                ->where('integration_id', $data['integration_id'])
                ->max('order') + 1;

        $service = $this->model->create($data);

        if ($logo) {
            $service->addMedia($logo)->toMediaCollection('service_logo');
        }

        return $service->fresh();
    }

    public function update(int $id, array $data): IntegrationService
    {
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        $service = $this->model->findOrFail($id);
        $service->update($data);

        if ($logo) {
            $service->addMedia($logo)->toMediaCollection('service_logo');
        }

        return $service->fresh();
    }

    public function delete(int $id): bool
    {
        $service = $this->model->findOrFail($id);
        $service->clearMediaCollection('service_logo');

        return $service->delete();
    }

    public function toggleEnabled(int $id): IntegrationService
    {
        $service = $this->model->findOrFail($id);
        $service->update(['is_enabled' => !$service->is_enabled]);

        return $service->fresh();
    }
}
