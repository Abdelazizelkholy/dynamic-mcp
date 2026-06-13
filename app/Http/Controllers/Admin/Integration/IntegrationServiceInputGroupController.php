<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceInput\StoreGroupRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateGroupRequest;
use App\Http\Requests\Admin\ServiceInput\StoreGroupWithInputsRequest;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputGroupResource;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputResource;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use Illuminate\Http\JsonResponse;

class IntegrationServiceInputGroupController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceInputGroupRepositoryInterface $groupRepo,
        private readonly IntegrationServiceInputRepositoryInterface $inputRepo,
    ) {}

    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $groups = $this->groupRepo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputGroupResource::collection($groups),
            'Input groups retrieved successfully.'
        );
    }

    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputGroupResource($group));
    }

    public function store(StoreGroupRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $group = $this->groupRepo->create(array_merge(
            $request->validated(),
            ['integration_service_id' => $serviceId]
        ));

        return ApiResponse::success(
            new IntegrationServiceInputGroupResource($group),
            'Input group created successfully.',
            201
        );
    }

    public function storeWithInputs(StoreGroupWithInputsRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $result = [];

        foreach ($request->validated('groups') as $groupData) {
            $result[] = $this->createGroupRecursive($groupData, $serviceId);
        }

        return ApiResponse::success($result, 'Groups with inputs created successfully.', 201);
    }

    private function createGroupRecursive(array $groupData, int $serviceId): array
    {
        $maxOrder = \App\Models\IntegrationServiceInputGroup::where('integration_service_id', $serviceId)->max('order') ?? 0;

        $group = $this->groupRepo->create([
            'integration_service_id' => $serviceId,
            'key_name'               => $groupData['key_name'],
            'data_type'              => $groupData['data_type'],
            'order'                  => $maxOrder + 1,
        ]);

        $inputs = [];

        foreach ($groupData['inputs'] ?? [] as $index => $inputData) {
            if ($inputData['field_type'] === 'group' && ! empty($inputData['group'])) {
                $inputs[] = $this->createGroupRecursive($inputData['group'], $serviceId);
                continue;
            }

            $inputs[] = $this->inputRepo->create(array_merge($inputData, [
                'integration_service_id' => $serviceId,
                'group_id'               => $group->id,
                'order'                  => $index + 1,
            ]));
        }

        return [
            'group'  => new IntegrationServiceInputGroupResource($group),
            'inputs' => IntegrationServiceInputResource::collection(collect($inputs)),
        ];
    }

    public function update(UpdateGroupRequest $request, int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $updated = $this->groupRepo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceInputGroupResource($updated),
            'Input group updated successfully.'
        );
    }

    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $this->groupRepo->delete($id);

        return ApiResponse::success(null, 'Input group deleted successfully.');
    }
}
