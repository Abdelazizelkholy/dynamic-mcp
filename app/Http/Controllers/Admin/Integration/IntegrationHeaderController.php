<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Header\StoreHeaderRequest;
use App\Http\Requests\Admin\Header\UpdateHeaderRequest;
use App\Http\Resources\Admin\Header\IntegrationHeaderResource;
use App\Repositories\IntegrationHeaderRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationHeaderController extends Controller
{
    public function __construct(
        private readonly IntegrationHeaderRepositoryInterface $repo
    ) {}

    // GET /admin/integrations/{integrationId}/headers
    public function index(int $integrationId): JsonResponse
    {
        $headers = $this->repo->allByIntegration($integrationId);

        return ApiResponse::success(
            IntegrationHeaderResource::collection($headers),
            'Headers retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/headers/{id}
    public function show(int $integrationId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (! $header || $header->integration_id !== $integrationId) {
            return ApiResponse::error('Header not found.', 404);
        }

        return ApiResponse::success(new IntegrationHeaderResource($header));
    }

    // POST /admin/integrations/{integrationId}/headers
    public function store(StoreHeaderRequest $request, int $integrationId): JsonResponse
    {
        $created = [];

        foreach ($request->validated('headers') as $headerData) {
            $created[] = $this->repo->create(array_merge(
                $headerData,
                ['integration_id' => $integrationId]
            ));
        }

        return ApiResponse::success(
            IntegrationHeaderResource::collection(collect($created)),
            'Headers created successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/headers/{id}
    // update() — remove all existing then insert new
    public function update(UpdateHeaderRequest $request, int $integrationId): JsonResponse
    {
        // 1. Delete all existing headers for this integration
        \App\Models\IntegrationHeader::where('integration_id', $integrationId)->delete();

        // 2. Insert new headers
        $created = [];

        foreach ($request->validated('headers') as $headerData) {
            $created[] = $this->repo->create(array_merge(
                $headerData,
                ['integration_id' => $integrationId]
            ));
        }

        return ApiResponse::success(
            IntegrationHeaderResource::collection(collect($created)),
            'Headers updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/headers/{id}
    public function destroy(int $integrationId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (! $header || $header->integration_id !== $integrationId) {
            return ApiResponse::error('Header not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Header deleted successfully.');
    }

    // PATCH /admin/integrations/{integrationId}/headers/{id}/toggle
    public function toggle(int $integrationId, int $id): JsonResponse
    {
        $header = $this->repo->find($id);

        if (! $header || $header->integration_id !== $integrationId) {
            return ApiResponse::error('Header not found.', 404);
        }

        $updated = $this->repo->toggleActive($id);

        return ApiResponse::success(
            new IntegrationHeaderResource($updated),
            'Header status toggled.'
        );
    }
}
