<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceResponse\StoreServiceResponseRequest;
use App\Http\Requests\Admin\ServiceResponse\UpdateFilterKeysRequest;
use App\Repositories\IntegrationServiceResponseRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceResponseController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceResponseRepositoryInterface $repo
    ) {}

    // ── GET /integrations/{integrationId}/services/{serviceId}/response ────────
    // Returns the response record + flattened keys for the dropdown
    public function show(int $integrationId, int $serviceId): JsonResponse
    {
        $response = $this->repo->findByService($serviceId);

        if (! $response) {
            return ApiResponse::success([
                'response_example'   => null,
                'output_filter_keys' => [],
                'flatten'            => (object) [],
            ]);
        }

        return ApiResponse::success([
            'response_example'   => $response->response_example,
            'output_filter_keys' => $response->output_filter_keys ?? [],
            'flatten'            => (object) $response->flattenResponseExample(),
        ]);
    }

    // ── POST /integrations/{integrationId}/services/{serviceId}/response ───────
    // Store response_example + auto-generate output_filter_keys from flatten
    public function store(StoreServiceResponseRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $responseExample = $request->validated('response_example');

        // Store the response
        $record = $this->repo->store($serviceId, [
            'response_example' => $responseExample,
        ]);

        // Auto-generate output_filter_keys from flatten (all is_used = false by default)
        $flattened       = $record->flattenResponseExample();
        $outputFilterKeys = collect($flattened)->map(fn($value, $key) => [
            'key'     => $key,
            'is_used' => false,
        ])->values()->toArray();

        $record = $this->repo->updateFilterKeys($serviceId, $outputFilterKeys);

        return ApiResponse::success([
            'response_example'   => $record->response_example,
            'output_filter_keys' => $record->output_filter_keys,
            'flatten'            => (object) $record->flattenResponseExample(),
        ], 'Response saved successfully.', 201);
    }

    // ── PUT /integrations/{integrationId}/services/{serviceId}/response/filter-keys
    // Update is_used for output_filter_keys
    public function updateFilterKeys(UpdateFilterKeysRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $record = $this->repo->updateFilterKeys(
            $serviceId,
            $request->validated('output_filter_keys')
        );

        return ApiResponse::success([
            'response_example'   => $record->response_example,
            'output_filter_keys' => $record->output_filter_keys,
            'flatten'            => (object) $record->flattenResponseExample(),
        ], 'Filter keys updated successfully.');
    }

    // ── GET /integrations/{integrationId}/services/{serviceId}/response/flatten ─
    // Returns only the flattened keys (for dropdowns)
    public function flatten(int $integrationId, int $serviceId): JsonResponse
    {
        $response = $this->repo->findByService($serviceId);

        if (! $response || empty($response->response_example)) {
            return ApiResponse::success((object) [], 'No response example defined.');
        }

        return ApiResponse::success(
            (object) $response->flattenResponseExample(),
            'Flattened keys retrieved successfully.'
        );
    }
}
