<?php

namespace App\Http\Controllers\Admin\Integration;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceInput\StoreInputRequest;
use App\Http\Requests\Admin\ServiceInput\UpdateInputRequest;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputResource;
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceInputGroup;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IntegrationServiceInputController extends Controller
{
    public function __construct(
        private readonly IntegrationServiceInputRepositoryInterface $repo
    ) {}

    // GET /integrations/{integrationId}/services/{serviceId}/inputs
    public function index(int $integrationId, int $serviceId): JsonResponse
    {
        $inputs = $this->repo->standaloneByService($serviceId);

        return ApiResponse::success(
            IntegrationServiceInputResource::collection($inputs),
            'Inputs retrieved successfully.'
        );
    }

    // GET /integrations/{integrationId}/services/{serviceId}/inputs/{id}
    public function show(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $input = $this->repo->find($id);

        if (! $input || $input->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input not found.', 404);
        }

        return ApiResponse::success(new IntegrationServiceInputResource($input));
    }

    // POST /integrations/{integrationId}/services/{serviceId}/inputs
    /*public function store(StoreInputRequest $request, int $integrationId, int $serviceId): JsonResponse
    {
        $created = [];

        foreach ($request->validated('inputs') as $index => $inputData) {
            $maxOrder = IntegrationServiceInput::where('integration_service_id', $serviceId)
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
    }*/

    public function sync(
        StoreInputRequest $request,
        int $integrationId,
        int $serviceId
    ): JsonResponse {

        DB::transaction(function () use ($serviceId, $request, &$created) {

            IntegrationServiceInput::where(
                'integration_service_id',
                $serviceId
            )->delete();

            $created = [];

            foreach ($request->validated('inputs') as $inputData) {

                $created[] = $this->repo->create([
                    ...$inputData,
                    'integration_service_id' => $serviceId,
                    'group_id' => null,
                ]);
            }
        });

        return ApiResponse::success(
            IntegrationServiceInputResource::collection(collect($created)),
            'Inputs synchronized successfully.'
        );
    }

    // PUT /integrations/{integrationId}/services/{serviceId}/inputs — array update
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

    // PUT /integrations/{integrationId}/services/{serviceId}/inputs/{id} — single update
    public function updateSingle(UpdateInputRequest $request, int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $input = $this->repo->find($id);

        if (! $input || $input->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input not found.', 404);
        }

        $updated = $this->repo->update($id, $request->validated());

        return ApiResponse::success(
            new IntegrationServiceInputResource($updated),
            'Input updated successfully.'
        );
    }

    // DELETE /integrations/{integrationId}/services/{serviceId}/inputs/{id}
    public function destroy(int $integrationId, int $serviceId, int $id): JsonResponse
    {
        $input = $this->repo->find($id);

        if (! $input || $input->integration_service_id !== $serviceId) {
            return ApiResponse::error('Input not found.', 404);
        }

        // If this input references a nested group → delete that group recursively
        if ($input->field_type === 'group' && $input->parent_group_id) {
            $this->deleteGroupRecursive($input->parent_group_id, $serviceId);
        }

        $this->repo->delete($id);

        return ApiResponse::success(null, 'Input deleted successfully.');
    }

    // POST /integrations/{integrationId}/services/{serviceId}/input-groups/{groupId}/inputs
    public function storeInGroup(StoreInputRequest $request, int $integrationId, int $serviceId, int $groupId): JsonResponse
    {
        $input = $this->repo->create(array_merge(
            $request->validated(),
            [
                'integration_service_id' => $serviceId,
                'group_id'               => $groupId,
            ]
        ));

        return ApiResponse::success(
            new IntegrationServiceInputResource($input),
            'Input added to group successfully.',
            201
        );
    }

    // ── Recursive group delete ─────────────────────────────────────────────────

    private function deleteGroupRecursive(int $groupId, int $serviceId): void
    {
        // 1. Get all inputs inside this group
        $inputs = IntegrationServiceInput::where('group_id', $groupId)->get();

        foreach ($inputs as $input) {
            // 2. If input is a nested group reference → recurse first
            if ($input->field_type === 'group' && $input->parent_group_id) {
                $this->deleteGroupRecursive($input->parent_group_id, $serviceId);
            }
            $input->delete();
        }

        // 3. Delete the group itself
        IntegrationServiceInputGroup::where('id', $groupId)
            ->where('integration_service_id', $serviceId)
            ->delete();
    }
}
