<?php


namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Param\ReorderParamRequest;
use App\Http\Requests\Admin\Param\StoreParamRequest;
use App\Http\Requests\Admin\Param\UpdateParamRequest;
use App\Http\Resources\Admin\Param\IntegrationServiceParamResource;
use App\Repositories\IntegrationServiceParamRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceParamController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceParamRepositoryInterface $repo
    )
    {
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/params
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $params = $this->repo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceParamResource::collection($params),
            'Params retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/params/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $param = $this->repo->find($id);

        if (!$param || $param->integration_service_id !== $serviceId) {
            return ApiResponse::error('Param not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceParamResource($param));
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/params
    public function store(StoreParamRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $param = $this->repo->create(array_merge(
            $request->validated(),
            ['integration_service_id' => $serviceId]
        ));

        return ApiResponse::success(
            new IntegrationServiceParamResource($param),
            'Param created successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/services/{serviceId}/params/{id}
    public function update(UpdateParamRequest $request, int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $param = $this->repo->find($id);

        if (!$param || $param->integration_service_id !== $serviceId) {
            return ApiResponse::error('Param not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceParamResource($updated),
            'Param updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/services/{serviceId}/params/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $param = $this->repo->find($id);

        if (!$param || $param->integration_service_id !== $serviceId) {
            return ApiResponse::error('Param not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Param deleted successfully.');
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/params/reorder
    public function reorder(ReorderParamRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $this->repo->reorder($serviceId, $request->validated('ordered_ids'));

        $params = $this->repo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceParamResource::collection($params),
            'Params reordered successfully.'
        );
    }
}
