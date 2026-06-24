<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResponseView\StoreResponseViewRequest;
use App\Http\Resources\Admin\ResponseView\IntegrationServiceResponseViewResource;
use App\Repositories\IntegrationServiceResponseViewRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceResponseViewController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceResponseViewRepositoryInterface $repo
    ) {}

    // GET /integrations/{integrationId}/services/{serviceId}/response-view
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $views = $this->repo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceResponseViewResource::collection($views),
            'Response views retrieved successfully.'
        );
    }

    // POST /integrations/{integrationId}/services/{serviceId}/response-view
    // Delete all then insert fresh
    public function store(StoreResponseViewRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $views = $this->repo->store($serviceId, $request->validated('views'));

        return ApiResponse::success(
            IntegrationServiceResponseViewResource::collection($views),
            'Response views saved successfully.',
            201
        );
    }

    // DELETE /integrations/{integrationId}/services/{serviceId}/response-view
    public function destroy(int $integrationId, int $serviceId): JsonResponse
    {
        $this->repo->delete($serviceId);

        return ApiResponse::success(null, 'Response views deleted successfully.');
    }
}
