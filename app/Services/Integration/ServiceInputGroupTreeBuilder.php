<?php

namespace App\Services\Integration;

use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputGroupResource;
use App\Http\Resources\Admin\ServiceInput\IntegrationServiceInputResource;
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceInputGroup;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;

/**
 * Builds the same nested groups/inputs/nested_groups tree shape used by the
 * admin input-groups listing (IntegrationServiceInputGroupController::index()),
 * so any other endpoint describing a service's inputs (e.g. the runtime
 * `execute` endpoint) renders identically instead of drifting out of sync.
 */
class ServiceInputGroupTreeBuilder
{
    public function __construct(
        private readonly IntegrationServiceInputGroupRepositoryInterface $groupRepo,
        private readonly IntegrationServiceInputRepositoryInterface $inputRepo,
    ) {}

    /**
     * Top-level groups only (nested groups are embedded under their parent's
     * `nested_groups`, not repeated at the top level).
     */
    public function build(int $serviceId): array
    {
        $nestedGroupIds = IntegrationServiceInput::where('integration_service_id', $serviceId)
            ->whereNotNull('parent_group_id')
            ->pluck('parent_group_id')
            ->unique()
            ->toArray();

        return $this->groupRepo->allByService($serviceId)
            ->filter(fn ($g) => ! in_array($g->id, $nestedGroupIds))
            ->values()
            ->map(fn ($g) => $this->buildGroupStructure($g))
            ->all();
    }

    private function buildGroupStructure(IntegrationServiceInputGroup $group): array
    {
        $regular      = [];
        $nestedGroups = [];

        $inputs = $group->relationLoaded('inputs')
            ? $group->inputs
            : $this->inputRepo->byGroup($group->id);

        foreach ($inputs as $input) {
            if ($input->field_type === 'group' && $input->parent_group_id) {
                $nestedGroup = $this->groupRepo->find($input->parent_group_id);
                if ($nestedGroup) {
                    $nestedGroups[] = [
                        'input'        => new IntegrationServiceInputResource($input),
                        'nested_group' => $this->buildGroupStructure($nestedGroup),
                    ];
                }
            } else {
                $regular[] = new IntegrationServiceInputResource($input);
            }
        }

        return [
            'group'         => new IntegrationServiceInputGroupResource($group->unsetRelation('inputs')),
            'inputs'        => $regular,
            'nested_groups' => $nestedGroups,
        ];
    }
}
