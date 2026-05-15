<?php


namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceInput\StoreGroupRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateGroupRequest;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputGroupResource;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceInputGroupController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceInputGroupRepositoryInterface $repo
    )
    {
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/input-groups
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $groups = $this->repo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputGroupResource::collection($groups),
            'Input groups retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->repo->find($id);

        if (!$group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputGroupResource($group));
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/input-groups
    public function store(StoreGroupRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $group = $this->repo->create(array_merge(
            $request->validated(),
            ['integration_service_id' => $serviceId]
        ));

        return ApiResponse::success(
            new IntegrationServiceInputGroupResource($group),
            'Input group created successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function update(UpdateGroupRequest $request, int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->repo->find($id);

        if (!$group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceInputGroupResource($updated),
            'Input group updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->repo->find($id);

        if (!$group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Input group deleted successfully.');
    }
}
