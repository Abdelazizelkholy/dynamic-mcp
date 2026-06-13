<?php


namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceInput\StoreInputRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateInputRequest;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputResource;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceInputController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceInputRepositoryInterface $repo
    )
    {
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/inputs
    // Returns standalone inputs only (not inside a group)
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $inputs = $this->repo->standaloneByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputResource::collection($inputs),
            'Inputs retrieved successfully.'
        );
    }

    // GET /admin/integrations/{integrationId}/services/{serviceId}/inputs/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $input = $this->repo->find($id);

        if (!$input || $input->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputResource($input));
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/inputs
    public function store(StoreInputRequest $request, int $integrationId, int $serviceId): JsonResponse
{
    $created = [];

    foreach ($request->validated('inputs') as $index => $inputData) {
        $maxOrder = \App\Models\IntegrationServiceInput::where('integration_service_id', $serviceId)
            ->whereNull('group_id')
            ->max('order') ?? 0;

        $created[] = $this->repo->create(array_merge($inputData, [
            'integration_service_id' => $serviceId,
            'group_id'               => null,
            'order'                  => $maxOrder + $index + 1,
        ]));
    }

    return ApiResponse::success(
        IntegrationServiceInputResource::collection(collect($created)),
        'Inputs created successfully.',
        201
    );
}

    // PUT /admin/integrations/{integrationId}/services/{serviceId}/inputs/{id}
    public function update(UpdateInputRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $updated = [];

        foreach ($request->validated('inputs') as $inputData) {
            $id    = $inputData['id'];
            $input = $this->repo->find($id);

            if (! $input || $input->integration_service_id !== $serviceId) {
                return ApiResponse::error("Input {$id} not found.", 404);
            }

            unset($inputData['id']);
            $updated[] = $this->repo->update($id, $inputData);
        }

        return ApiResponse::success(
            IntegrationServiceInputResource::collection(collect($updated)),
            'Inputs updated successfully.'
        );
    }

    // DELETE /admin/integrations/{integrationId}/services/{serviceId}/inputs/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $input = $this->repo->find($id);

        if (!$input || $input->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input not found.', 404);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Input deleted successfully.');
    }

    // POST /admin/integrations/{integrationId}/services/{serviceId}/input-groups/{groupId}/inputs
    // Add input inside a group
    public function storeInGroup(StoreInputRequest $request, int $integrationId, int $serviceId, int $groupId): JsonResponse
    {
        $input = $this->repo->create(array_merge(
            $request->validated(),
            [
                'integration_service_id' => $serviceId,
                'group_id' => $groupId,
            ]
        ));

        return ApiResponse::success(
            new IntegrationServiceInputResource($input),
            'Input added to group successfully.',
            201
        );
    }
}
