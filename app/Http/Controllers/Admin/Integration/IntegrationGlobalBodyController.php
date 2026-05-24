<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GlobalBody\StoreGlobalBodyRequest;
use App\Http\Requests\Admin\GlobalBody\UpdateGlobalBodyRequest;
use App\Http\Resources\Admin\GlobalBody\IntegrationGlobalBodyResource;
use App\Repositories\IntegrationGlobalBodyRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationGlobalBodyController extends Controller
{
    public function __construct(
        private readonly IntegrationGlobalBodyRepositoryInterface $repo
    ) {}

    // GET /admin/integrations/{integrationId}/global-body
    public function index(int $integrationId): JsonResponse
    {
        $body = $this->repo->allByIntegration($integrationId);

        return ApiResponse::success(
            IntegrationGlobalBodyResource::collection($body),
            'Global body retrieved successfully.'
        );
    }

    // POST /admin/integrations/{integrationId}/global-body
    // Deletes existing and inserts new — clean replace
    public function store(StoreGlobalBodyRequest $request, int $integrationId): JsonResponse
    {
        $created = [];

        foreach ($request->validated('body') as $index => $bodyData) {
            // Get current max order and increment
            $maxOrder = \App\Models\IntegrationGlobalBody::where('integration_id', $integrationId)
                ->max('order') ?? 0;

            $created[] = $this->repo->create(array_merge($bodyData, [
                'integration_id' => $integrationId,
                'order'          => $maxOrder + $index + 1,
            ]));
        }

        return ApiResponse::success(
            IntegrationGlobalBodyResource::collection(collect($created)),
            'Global body added successfully.',
            201
        );
    }

    // PUT /admin/integrations/{integrationId}/global-body
    // Same as store — delete all then insert new
    // PUT /admin/integrations/{integrationId}/global-body/{id}
    public function update(UpdateGlobalBodyRequest $request, int $integrationId, int $id): JsonResponse
    {
        $body = $this->repo->find($id);

        if (! $body || $body->integration_id !== $integrationId) {
            return ApiResponse::error('Global body not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationGlobalBodyResource($updated),
            'Global body updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/global-body/{id}
    public function destroy(int $integrationId, int $id): JsonResponse
    {
        $body = $this->repo->find($id);

        if (! $body || $body->integration_id !== $integrationId) {
            return ApiResponse::error('Global body not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Global body deleted successfully.');
    }
}
