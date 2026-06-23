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
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceInputGroup;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IntegrationServiceInputGroupController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceInputGroupRepositoryInterface $groupRepo,
        private readonly IntegrationServiceInputRepositoryInterface $inputRepo,
    ) {}

    // GET /integrations/{integrationId}/services/{serviceId}/input-groups
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $groups = $this->groupRepo->allByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputGroupResource::collection($groups),
            'Input groups retrieved successfully.'
        );
    }

    // GET /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputGroupResource($group));
    }

    // POST /integrations/{integrationId}/services/{serviceId}/input-groups
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

    // POST /integrations/{integrationId}/services/{serviceId}/input-groups/with-inputs
    /*public function storeWithInputs(StoreGroupWithInputsRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        // 1. Delete all existing groups and inputs for this service
        $existingGroups = IntegrationServiceInputGroup::where('integration_service_id', $serviceId)->get();

        foreach ($existingGroups as $group) {
            $this->deleteGroupRecursive($group->id, $serviceId);
        }

        // Also delete any remaining standalone inputs
        IntegrationServiceInput::where('integration_service_id', $serviceId)->delete();

        // 2. Insert fresh
        $result = [];

        foreach ($request->input('groups', []) as $groupData) {
            $result[] = $this->createGroupRecursive($groupData, $serviceId);
        }

        return ApiResponse::success($result, 'Groups with inputs saved successfully.', 201);
    }*/

    public function storeWithInputs(Request $request, int $integrationId, int $serviceId): JsonResponse
    {
        $data = $request->json()->all() ?: $request->all();

        $validator = Validator::make($data, [
            'groups'                               => ['required', 'array', 'min:1'],
            'groups.*.key_name'                    => ['required', 'string', 'max:255'],
            'groups.*.data_type'                   => ['required', 'in:object,array_of_objects,array'],
            'groups.*.inputs'                      => ['nullable', 'array'],
            'groups.*.inputs.*.field_type'         => ['nullable', 'in:input,select,dynamic_select,boolean,group,file,file_url,files,files_url,date,datetime'],
            'groups.*.inputs.*.key'                => ['nullable', 'string', 'max:255'],
            'groups.*.inputs.*.placeholder'        => ['nullable', 'string'],
            'groups.*.inputs.*.type'               => ['nullable', 'in:params,input'],
            'groups.*.inputs.*.key_type'           => ['nullable', 'in:body,headers'],
            'groups.*.inputs.*.validation'         => ['nullable', 'in:required,nullable'],
            'groups.*.inputs.*.require_from'       => ['nullable', 'in:admin,user,front,response,user_integration,dependency_service'],
            'groups.*.inputs.*.label'              => ['nullable', 'string', 'max:255'],
            'groups.*.inputs.*.options'            => ['nullable', 'array'],
            'groups.*.inputs.*.options.*'          => ['string'],
            'groups.*.inputs.*.dynamic_service_id' => ['nullable', 'integer', 'exists:integration_services,id'],
            'groups.*.inputs.*.date_format'        => ['nullable', 'string', 'max:50'],
            'groups.*.inputs.*.group'              => ['nullable', 'array'],
            'groups.*.inputs.*.group.key_name'     => ['nullable', 'string'],
            'groups.*.inputs.*.group.data_type'    => ['nullable', 'in:object,array_of_objects,array'],
            'groups.*.inputs.*.group.inputs'       => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        $groups = $data['groups'];

        DB::transaction(function () use ($serviceId, $groups, &$result) {
            $existingGroups = IntegrationServiceInputGroup::where('integration_service_id', $serviceId)->get();
            foreach ($existingGroups as $group) {
                $this->deleteGroupRecursive($group->id, $serviceId);
            }
            IntegrationServiceInput::where('integration_service_id', $serviceId)->delete();

            // 2. Insert fresh
            $result = [];
            foreach ($groups as $groupData) {
                $result[] = $this->createGroupRecursive($groupData, $serviceId);
            }
        });

        return ApiResponse::success($result, 'Groups with inputs saved successfully.', 201);
    }

    // PUT /integrations/{integrationId}/services/{serviceId}/input-groups/with-inputs
    public function updateWithInputs(UpdateGroupWithInputsRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $result = [];

        foreach ($request->validated('groups') as $groupData) {
            $result[] = $this->updateGroupRecursive($groupData, $serviceId);
        }

        return ApiResponse::success($result, 'Groups with inputs updated successfully.');
    }

    // PUT /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
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

    // DELETE /integrations/{integrationId}/services/{serviceId}/input-groups/{id}
    // Deletes group + all nested inputs and groups recursively
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $group = $this->groupRepo->find($id);

        if (! $group || $group->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input group not found.', 404);
        }

        $this->deleteGroupRecursive($id, $serviceId);

        return ApiResponse::success(null, 'Input group and all nested groups deleted successfully.');
    }

    // ── Recursive: Create ──────────────────────────────────────────────────────

    private function createGroupRecursive(array $groupData, int $serviceId): array
    {
        $maxOrder = IntegrationServiceInputGroup::where('integration_service_id', $serviceId)
            ->max('order') ?? 0;

        $group = $this->groupRepo->create([
            'integration_service_id' => $serviceId,
            'key_name'               => $groupData['key_name'],
            'data_type'              => $groupData['data_type'],
            'order'                  => $maxOrder + 1,
        ]);

        $inputs = [];
        $nested = [];

        foreach ($groupData['inputs'] ?? [] as $index => $inputData) {

            if (! empty($inputData['group'])) {
                $nestedResult  = $this->createGroupRecursive($inputData['group'], $serviceId);
                $nestedGroupId = $nestedResult['group']->resource->id ?? null;

                $inputRow = $this->inputRepo->create([
                    'integration_service_id' => $serviceId,
                    'group_id'               => $group->id,
                    'parent_group_id'        => $nestedGroupId,
                    'field_type'             => 'group',
                    'key'                    => $inputData['key'] ?? $inputData['group']['key_name'] ?? null,
                    'key_type'               => $inputData['key_type'] ?? 'body',
                    'validation'             => $inputData['validation'] ?? 'required',
                    'require_from'           => $inputData['require_from'] ?? 'user',
                    'order'                  => $index + 1,
                ]);

                $nested[] = [
                    'input'        => new IntegrationServiceInputResource($inputRow),
                    'nested_group' => $nestedResult,
                ];
                continue;
            }

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

    // ── Recursive: Update ──────────────────────────────────────────────────────

    private function updateGroupRecursive(array $groupData, int $serviceId): array
    {
        $group = $this->groupRepo->update($groupData['id'], array_filter([
            'key_name'  => $groupData['key_name']  ?? null,
            'data_type' => $groupData['data_type'] ?? null,
        ]));

        $inputs = [];
        $nested = [];

        foreach ($groupData['inputs'] ?? [] as $index => $inputData) {

            if (! empty($inputData['group'])) {
                $nestedGroupData = $inputData['group'];

                $nestedResult = ! empty($nestedGroupData['id'])
                    ? $this->updateGroupRecursive($nestedGroupData, $serviceId)
                    : $this->createGroupRecursive($nestedGroupData, $serviceId);

                $nestedGroupId = $nestedResult['group']->resource->id ?? null;

                if (! empty($inputData['id'])) {
                    $inp = $this->inputRepo->find($inputData['id']);
                    if ($inp) {
                        $this->inputRepo->update($inp->id, [
                            'parent_group_id' => $nestedGroupId,
                            'key'             => $inputData['key'] ?? $inp->key,
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
                $inp = $this->inputRepo->find($inputData['id']);
                if ($inp) {
                    $id = $inputData['id'];
                    unset($inputData['id']);
                    $inputs[] = $this->inputRepo->update($id, $this->filterInputData($inputData));
                }
            } else {
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

    // ── Recursive: Delete ──────────────────────────────────────────────────────

    private function deleteGroupRecursive(int $groupId, int $serviceId): void
    {
        // 1. Get all inputs inside this group
        $inputs = IntegrationServiceInput::where('group_id', $groupId)->get();

        foreach ($inputs as $input) {
            // 2. If input references a nested group → recurse into it first
            if ($input->field_type === 'group' && $input->parent_group_id) {
                $this->deleteGroupRecursive($input->parent_group_id, $serviceId);
            }
            // 3. Delete the input
            $input->delete();
        }

        // 4. Delete the group itself
        IntegrationServiceInputGroup::where('id', $groupId)
            ->where('integration_service_id', $serviceId)
            ->delete();
    }

    // ── Helper ─────────────────────────────────────────────────────────────────

    private function filterInputData(array $inputData): array
    {
        return array_filter(
            $inputData,
            fn($key) => in_array($key, [
                'field_type', 'key', 'placeholder', 'type', 'key_type',
                'validation', 'require_from', 'options', 'dynamic_service_id',
                'date_format', 'label',
            ]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
