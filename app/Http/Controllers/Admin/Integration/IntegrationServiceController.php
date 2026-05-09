<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Service\IndexServiceRequest;
use App\Http\Requests\Admin\Service\StoreServiceRequest;
use App\Http\Requests\Admin\Service\UpdateServiceRequest;
use App\Http\Resources\Admin\IntegrationService\IntegrationServiceResource;
use App\Models\IntegrationService;
use App\Repositories\IntegrationServiceRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceRepositoryInterface $repo
    )
    {
    }

    // GET /admin/integrations/{integrationId}/services?search=orders
    public function index(IndexServiceRequest $request, int $integrationId): JsonResponse
    {
        $services = $this->repo->allByIntegration($integrationId, $request->search);

        return ApiResponse::success(
            IntegrationServiceResource::collection($services),
            'Services retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/services/{id}
    public function show(int $integrationId, int $id): JsonResponse
    {
        $service = $this->repo->find($id);

        if (!$service || $service->integration_id !== $integrationId) {
            return ApiResponse::error('Service not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceResource($service));
    }

    // POST /admin/integrations/{integrationId}/services
    public function store(StoreServiceRequest $request, int $integrationId): JsonResponse
    {
        $service = $this->repo->create(array_merge(
            $request->validated(),
            ['integration_id' => $integrationId]
        ));

        return ApiResponse::success(
            new IntegrationServiceResource($service),
            'Service created successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/services/{id}
    public function update(UpdateServiceRequest $request, int $integrationId, int $id): JsonResponse
    {
        $service = $this->repo->find($id);

        if (!$service || $service->integration_id !== $integrationId) {
            return ApiResponse::error('Service not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceResource($updated),
            'Service updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/services/{id}
    public function destroy(int $integrationId, int $id): JsonResponse
    {
        $service = $this->repo->find($id);

        if (!$service || $service->integration_id !== $integrationId) {
            return ApiResponse::error('Service not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Service deleted successfully.');
    }

    // PATCH /admin/integrations/{integrationId}/services/{id}/toggle
    public function toggle(int $integrationId, int $id): JsonResponse
    {
        $service = $this->repo->find($id);

        if (!$service || $service->integration_id !== $integrationId) {
            return ApiResponse::error('Service not found.', 404);
        }

        $updated = $this->repo->toggleEnabled($id);

        return ApiResponse::success(
            new IntegrationServiceResource($updated),
            'Service status toggled.'
        );
    }

    /**
     * GET /admin/integrations/{integrationId}/services/available-dependencies
     * Returns all services in this integration for the dependency multi-select.
     * Excludes the current service if {excludeId} is passed as query param.
     */
    public function availableDependencies(int $integrationId): JsonResponse
    {
        $excludeId = request()->query('exclude_id');

        $services = IntegrationService::where('integration_id', $integrationId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('order')
            ->get(['id', 'service_name', 'http_method', 'endpoint_path']);

        return ApiResponse::success(
            $services->map(fn($s) => [
                'id' => $s->id,
                'service_name' => $s->service_name,
                'http_method' => $s->http_method,
                'endpoint_path' => $s->endpoint_path,
            ]),
            'Available dependency services retrieved.'
        );
    }
}
