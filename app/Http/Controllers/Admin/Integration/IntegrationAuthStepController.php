<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthStep\ReorderAuthStepsRequest;
use App\Http\Requests\Admin\AuthStep\StoreAuthStepRequest;
use App\Http\Requests\Admin\AuthStep\UpdateAuthStepRequest;
use App\Http\Resources\Admin\AuthStep\IntegrationAuthStepResource;
use App\Repositories\IntegrationAuthStepRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationAuthStepController extends Controller
{
    public function __construct(
        private readonly IntegrationAuthStepRepositoryInterface $repo
    ) {}

    // ── GET /admin/integrations/{integrationId}/auth-steps ─────────────────────
    public function index(int $integrationId): JsonResponse
    {
        $steps = $this->repo->allByIntegration($integrationId);

        return ApiResponse::success(
            IntegrationAuthStepResource::collection($steps),
            'Auth steps retrieved successfully.'
        );
    }

    // ── GET /admin/integrations/{integrationId}/auth-steps/{id} ───────────────
    public function show(int $integrationId, int $id): JsonResponse
    {
        $step = $this->repo->find($id);

        if (! $step || $step->integration_id !== $integrationId) {
            return ApiResponse::error('Auth step not found.', 404);
        }

        return ApiResponse::success(new IntegrationAuthStepResource($step));
    }

    // ── POST /admin/integrations/{integrationId}/auth-steps ───────────────────
    public function store(StoreAuthStepRequest $request, int $integrationId): JsonResponse
    {
        $step = $this->repo->create(array_merge(
            $request->validated(),
            ['integration_id' => $integrationId]
        ));

        return ApiResponse::success(
            new IntegrationAuthStepResource($step),
            'Auth step created successfully.',
            201
        );
    }

    // ── PUT /admin/integrations/{integrationId}/auth-steps/{id} ───────────────
    public function update(UpdateAuthStepRequest $request, int $integrationId, int $id): JsonResponse
    {
        $step = $this->repo->find($id);

        if (! $step || $step->integration_id !== $integrationId) {
            return ApiResponse::error('Auth step not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationAuthStepResource($updated),
            'Auth step updated successfully.'
        );
    }

    // ── DELETE /admin/integrations/{integrationId}/auth-steps/{id} ────────────
    public function destroy(int $integrationId, int $id): JsonResponse
    {
        $step = $this->repo->find($id);

        if (! $step || $step->integration_id !== $integrationId) {
            return ApiResponse::error('Auth step not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Auth step deleted successfully.');
    }

    // ── PATCH /admin/integrations/{integrationId}/auth-steps/{id}/toggle ──────
    public function toggle(int $integrationId, int $id): JsonResponse
    {
        $step = $this->repo->find($id);

        if (! $step || $step->integration_id !== $integrationId) {
            return ApiResponse::error('Auth step not found.', 404);
        }

        $updated = $this->repo->toggleActive($id);

        return ApiResponse::success(
            new IntegrationAuthStepResource($updated),
            'Auth step status toggled.'
        );
    }

    // ── POST /admin/integrations/{integrationId}/auth-steps/reorder ───────────
    public function reorder(ReorderAuthStepsRequest $request, int $integrationId): JsonResponse
    {
        $this->repo->reorder($integrationId, $request->validated('ordered_ids'));

        $steps = $this->repo->allByIntegration($integrationId);

        return ApiResponse::success(
            IntegrationAuthStepResource::collection($steps),
            'Auth steps reordered successfully.'
        );
    }
}
