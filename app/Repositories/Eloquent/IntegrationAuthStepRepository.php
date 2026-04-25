<?php

namespace App\Repositories\Eloquent;

use App\Models\IntegrationAuthStep;
use App\Repositories\IntegrationAuthStepRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IntegrationAuthStepRepository implements IntegrationAuthStepRepositoryInterface
{
    public function __construct(private readonly IntegrationAuthStep $model) {}

    // ── Read ───────────────────────────────────────────────────────────────────

    public function allByIntegration(int $integrationId): Collection
    {
        return $this->model
            ->where('integration_id', $integrationId)
            ->orderBy('order')
            ->get();
    }

    public function find(int $id): ?IntegrationAuthStep
    {
        return $this->model->find($id);
    }


    public function create(array $data): IntegrationAuthStep
    {
        // Auto-assign order: last position + 1
        $data['order'] = $this->model
                ->where('integration_id', $data['integration_id'])
                ->max('order') + 1;

        return $this->model->create($data);
    }

    public function update(int $id, array $data): IntegrationAuthStep
    {
        $step = $this->findOrFail($id);
        $step->update($data);

        return $step->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }


    /**
     * Accepts an ordered list of step IDs and reassigns `order` values.
     * Example: reorder(1, [3, 1, 2]) → step 3 gets order=1, step 1 gets order=2 …
     */
    public function reorder(int $integrationId, array $orderedIds): bool
    {
        foreach ($orderedIds as $position => $stepId) {
            $this->model
                ->where('id', $stepId)
                ->where('integration_id', $integrationId)
                ->update(['order' => $position + 1]);
        }

        return true;
    }

    public function toggleActive(int $id): IntegrationAuthStep
    {
        $step = $this->findOrFail($id);
        $step->update(['is_active' => ! $step->is_active]);

        return $step->fresh();
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private function findOrFail(int $id): IntegrationAuthStep
    {
        return $this->model->findOrFail($id);
    }
}
