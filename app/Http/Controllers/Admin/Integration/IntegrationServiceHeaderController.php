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

    public function store(StoreServiceHeaderRequest $request, int $integrationId, int $serviceId): JsonResponse
{
    // Delete existing then insert new
    \App\Models\IntegrationServiceHeader::where('integration_service_id', $serviceId)->delete();

    $created = [];
    foreach ($request->validated('headers') as $headerData) {
        $created[] = $this->repo->create(array_merge(
            $headerData,
            ['integration_service_id' => $serviceId]
        ));
    }

    return ApiResponse::success(
        IntegrationServiceHeaderResource::collection(collect($created)),
        'Service headers saved successfully.',
        201
    );
}

public function update(UpdateServiceHeaderRequest $request, int $integrationId, int $serviceId): JsonResponse
{
    \App\Models\IntegrationServiceHeader::where('integration_service_id', $serviceId)->delete();

    $updated = [];
    foreach ($request->validated('headers') as $headerData) {
        $updated[] = $this->repo->create(array_merge(
            $headerData,
            ['integration_service_id' => $serviceId]
        ));
    }

    return ApiResponse::success(
        IntegrationServiceHeaderResource::collection(collect($updated)),
        'Service headers updated successfully.'
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
