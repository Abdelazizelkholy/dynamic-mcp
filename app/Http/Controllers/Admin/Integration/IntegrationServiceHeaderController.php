<?php


namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceHeader\StoreServiceHeaderRequest;
use App\Http\Requests\Admin\ServiceHeader\UpdateServiceHeaderRequest;
use App\Http\Resources\Admin\ServiceHeader\IntegrationServiceHeaderResource;
use App\Repositories\IntegrationServiceHeaderRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceHeaderController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceHeaderRepositoryInterface $repo
    )
    {
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/headers
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $headers = $this->repo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceHeaderResource::collection($headers),
            'Service headers retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/headers/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (!$header || $header->integration_service_id !== $serviceId) {
            return ApiResponse::error('Service header not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceHeaderResource($header));
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/headers
    public function store(StoreServiceHeaderRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $header = $this->repo->create(array_merge(
            $request->validated(),
            ['integration_service_id' => $serviceId]
        ));

        return ApiResponse::success(
            new IntegrationServiceHeaderResource($header),
            'Service header created successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/services/{serviceId}/headers/{id}
    public function update(UpdateServiceHeaderRequest $request, int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (!$header || $header->integration_service_id !== $serviceId) {
            return ApiResponse::error('Service header not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceHeaderResource($updated),
            'Service header updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/services/{serviceId}/headers/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (!$header || $header->integration_service_id !== $serviceId) {
            return ApiResponse::error('Service header not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Service header deleted successfully.');
    }

    // PATCH /admin/integrations/{integrationId}/services/{serviceId}/headers/{id}/toggle
    public function toggle(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (!$header || $header->integration_service_id !== $serviceId) {
            return ApiResponse::error('Service header not found.', 404);
        }

        $updated = $this->repo->toggleActive($id);

        return ApiResponse::success(
            new IntegrationServiceHeaderResource($updated),
            'Service header status toggled.'
        );
    }
}
