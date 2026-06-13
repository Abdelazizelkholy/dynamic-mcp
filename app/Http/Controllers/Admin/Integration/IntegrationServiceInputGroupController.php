<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceInput\StoreGroupRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateGroupRequest;
use App\Http\Requests\Admin\ServiceInput\StoreGroupWithInputsRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateGroupWithInputsRequest;
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

    // ── GET /integrations/{integrationId}/services/{serviceId}/input-groups ───
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $groups = $this->groupRepo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputGroupResource::collection($groups),
            'Input groups retrieved successfully.'
        );
    }

    // ── GET /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputGroupResource($group));
    }

    // ── POST /integrations/{integrationId}/services/{serviceId}/input-groups ──
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

    // ── POST /integrations/{integrationId}/services/{serviceId}/input-groups/with-inputs
    public function storeWithInputs(StoreGroupWithInputsRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $result = [];

        foreach ($request->validated('groups') as $groupData) {
            $result[] = $this->createGroupRecursive($groupData, $serviceId);
        }

        return ApiResponse::success($result, 'Groups with inputs created successfully.', 201);
    }

    // ── PUT /integrations/{integrationId}/services/{serviceId}/input-groups/with-inputs
    public function updateWithInputs(UpdateGroupWithInputsRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $result = [];

        foreach ($request->validated('groups') as $groupData) {
            $result[] = $this->updateGroupRecursive($groupData, $serviceId);
        }

        return ApiResponse::success($result, 'Groups with inputs updated successfully.');
    }

    // ── PUT /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
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

    // ── DELETE /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $this->groupRepo->delete($id);

        return ApiResponse::success(null, 'Input group deleted successfully.');
    }

    // ── Private: Create group + inputs recursively ─────────────────────────────

    private function createGroupRecursive(array $groupData, int $serviceId): array
    {
        $maxOrder = \App\Models\IntegrationServiceInputGroup::where('integration_service_id', $serviceId)
            ->max('order') ?? 0;

        // 1. Create the group
        $group = $this->groupRepo->create([
            'integration_service_id' => $serviceId,
            'key_name'               => $groupData['key_name'],
            'data_type'              => $groupData['data_type'],
            'order'                  => $maxOrder + 1,
        ]);

        $inputs  = [];
        $nested  = [];

        foreach ($groupData['inputs'] ?? [] as $index => $inputData) {

            // Nested group → recurse first, then save an input row as a reference
            if (($inputData['field_type'] ?? '') === 'group' && ! empty($inputData['group'])) {
                $nestedResult = $this->createGroupRecursive($inputData['group'], $serviceId);

                // Save input row referencing the nested group
                $nestedGroupId = $nestedResult['group']->resource->id ?? null;

                $inputRow = $this->inputRepo->create([
                    'integration_service_id' => $serviceId,
                    'group_id'               => $group->id,
                    'parent_group_id'        => $nestedGroupId,
                    'field_type'             => 'group',
                    'key'                    => $inputData['key'],
                    'key_type'               => $inputData['key_type'],
                    'validation'             => $inputData['validation'],
                    'require_from'           => $inputData['require_from'],
                    'order'                  => $index + 1,
                ]);

                $nested[] = [
                    'input'        => new IntegrationServiceInputResource($inputRow),
                    'nested_group' => $nestedResult,
                ];
                continue;
            }

            // Regular input
            $inputs[] = $this->inputRepo->create(array_merge(
                $this->filterInputData($inputData),
                [
                    'integration_service_id' => $serviceId,
                    'group_id'               => $group->id,
                    'order'                  => $index + 1,
                ]
            ));
        }

        return [
            'group'         => new IntegrationServiceInputGroupResource($group),
            'inputs'        => IntegrationServiceInputResource::collection(collect($inputs)),
            'nested_groups' => $nested,
        ];
    }

    // ── Private: Update group + inputs recursively ─────────────────────────────

    private function updateGroupRecursive(array $groupData, int $serviceId): array
    {
        // 1. Update the group itself
        $group = $this->groupRepo->update($groupData['id'], array_filter([
            'key_name'  => $groupData['key_name']  ?? null,
            'data_type' => $groupData['data_type'] ?? null,
        ]));

        $inputs = [];
        $nested = [];

        foreach ($groupData['inputs'] ?? [] as $index => $inputData) {

            // Nested group → recurse
            if (($inputData['field_type'] ?? '') === 'group' && ! empty($inputData['group'])) {
                $nestedGroupData = $inputData['group'];

                if (! empty($nestedGroupData['id'])) {
                    // Update existing nested group
                    $nestedResult = $this->updateGroupRecursive($nestedGroupData, $serviceId);
                } else {
                    // Create new nested group
                    $nestedResult = $this->createGroupRecursive($nestedGroupData, $serviceId);
                }

                // Update or create the input row referencing this nested group
                $nestedGroupId = $nestedResult['group']->resource->id ?? null;

                if (! empty($inputData['id'])) {
                    $inp = $this->inputRepo->find($inputData['id']);
                    if ($inp) {
                        $this->inputRepo->update($inp->id, [
                            'parent_group_id' => $nestedGroupId,
                            'key'             => $inputData['key'],
                            'order'           => $index + 1,
                        ]);
                    }
                } else {
                    $this->inputRepo->create([
                        'integration_service_id' => $serviceId,
                        'group_id'               => $group->id,
                        'parent_group_id'        => $nestedGroupId,
                        'field_type'             => 'group',
                        'key'                    => $inputData['key'],
                        'key_type'               => $inputData['key_type'],
                        'validation'             => $inputData['validation'],
                        'require_from'           => $inputData['require_from'],
                        'order'                  => $index + 1,
                    ]);
                }

                $nested[] = $nestedResult;
                continue;
            }

            if (! empty($inputData['id'])) {
                // Update existing input
                $inp = $this->inputRepo->find($inputData['id']);
                if ($inp) {
                    $id = $inputData['id'];
                    unset($inputData['id']);
                    $inputs[] = $this->inputRepo->update($id, $this->filterInputData($inputData));
                }
            } else {
                // Create new input inside existing group
                $inputs[] = $this->inputRepo->create(array_merge(
                    $this->filterInputData($inputData),
                    [
                        'integration_service_id' => $serviceId,
                        'group_id'               => $group->id,
                        'order'                  => $index + 1,
                    ]
                ));
            }
        }

        return [
            'group'         => new IntegrationServiceInputGroupResource($group),
            'inputs'        => IntegrationServiceInputResource::collection(collect($inputs)),
            'nested_groups' => $nested,
        ];
    }

    // ── Helper: filter only input columns ──────────────────────────────────────

    private function filterInputData(array $inputData): array
    {
        return array_filter(
            $inputData,
            fn($key) => in_array($key, [
                'field_type', 'key', 'placeholder', 'type', 'key_type',
                'validation', 'require_from', 'options', 'dynamic_service_id',
                'date_format',
            ]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
