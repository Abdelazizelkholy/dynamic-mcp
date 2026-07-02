<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationService;
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceInputGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class McpToolsController extends Controller
{
    /**
     * GET /api/mcp/{integrationId}/tools
     */
    public function index(int $integrationId): JsonResponse
    {
        $integration = Integration::find($integrationId);

        if (! $integration) {
            return response()->json(['success' => false, 'message' => 'Integration not found.'], 404);
        }

        $services = IntegrationService::with([
            'standaloneInputs.dynamicService',
            'inputGroups.inputs.dynamicService',
        ])
            ->where('integration_id', $integrationId)
            ->where('is_enabled', true)
            ->orderBy('order')
            ->get();

        $tools = $services->map(fn($service) => $this->buildTool($service, $integration));

        $total      = $tools->count();
        $authorized = $tools->where('is_authorized', true)->count();

        return response()->json([
            'tools'              => $tools->values(),
            'stats'              => [
                'total'                  => $total,
                'authorized'             => $authorized,
                'unauthorized'           => $total - $authorized,
                'no_auth_required'       => $total,
                'requires_authorization' => 0,
            ],
            'integration_id'     => $integrationId,
            'integration_name'   => $integration->name,
            'integration_status' => $integration->enable ? 'active' : 'inactive',
            'success'            => true,
        ]);
    }

    // ── Build one tool ─────────────────────────────────────────────────────────

    private function buildTool(IntegrationService $service, Integration $integration): array
    {
        $baseUrl = rtrim(
                $service->base_url_override ?? $integration->base_api_url,
                '/'
            ) . 'McpToolsController.php/' . ltrim($service->endpoint_path, '/');

        // Build params URL segments
        $params = $service->params()->orderBy('order')->get();
        if ($params->isNotEmpty()) {
            $segments = $params->map(fn($p) => $p->type === 'static' ? $p->value : "[{$p->value}]")->implode('/');
            $baseUrl  = rtrim($baseUrl, '/') . 'McpToolsController.php/' . $segments;
        }

        $inputSchema = $this->buildInputSchema($service);

        return [
            'name'                => $integration->name . '-' . $service->service_name_en,
            'uuid'                => Str::random(8),
            'description'         => $service->description_en ?? '',
            'integration'         => $integration->name,
            'integration_id'      => $integration->id,
            'service_id'          => $service->id,
            'user_integration_id' => null,
            'is_authorized'       => true,
            'has_auth'            => false,
            'auth_status'         => 'no_auth_required',
            'inputSchema'         => $inputSchema,
            'metadata'            => [
                'method_type'            => strtolower($service->http_method),
                'base_url'               => $baseUrl,
                'content_type'           => $service->content_type,
                'requires_authorization' => false,
            ],
        ];
    }

    // ── Build inputSchema ──────────────────────────────────────────────────────

    private function buildInputSchema(IntegrationService $service): array
    {
        $properties = [];
        $required   = [];

        // 1. Standalone inputs
        $standaloneInputs = IntegrationServiceInput::with('dynamicService')
            ->where('integration_service_id', $service->id)
            ->whereNull('group_id')
            ->orderBy('order')
            ->get();

        foreach ($standaloneInputs as $input) {
            [$key, $prop] = $this->buildInputProperty($input);
            $properties[$key] = $prop;

            if ($input->validation === 'required') {
                $required[] = $key;
            }
        }

        // 2. Groups
        $groups = IntegrationServiceInputGroup::with(['inputs.dynamicService'])
            ->where('integration_service_id', $service->id)
            ->orderBy('order')
            ->get();

        foreach ($groups as $group) {
            $groupProp = $this->buildGroupProperty($group);
            $properties[$group->key_name] = $groupProp;
        }

        return [
            'type'       => 'object',
            'properties' => (object) $properties,
            'required'   => $required,
        ];
    }

    // ── Build single input property ────────────────────────────────────────────

    private function buildInputProperty(IntegrationServiceInput $input): array
    {
        $key  = $input->key;
        $prop = [
            'type'        => $this->mapFieldType($input->field_type),
            'description' => $input->key_type ?? 'body',
        ];

        // select → add values
        if ($input->field_type === 'select' && ! empty($input->options)) {
            $prop['values'] = $input->options;
        }

        // dynamic_select → add service_id
        if ($input->field_type === 'dynamic_select' && $input->dynamic_service_id) {
            $prop['service_id'] = $input->dynamic_service_id;
        }

        // date/datetime → add format
        if (in_array($input->field_type, ['date', 'datetime']) && $input->date_format) {
            $prop['format'] = $input->date_format;
        }

        return [$key, $prop];
    }

    // ── Build group property ───────────────────────────────────────────────────

    private function buildGroupProperty(IntegrationServiceInputGroup $group): array
    {
        $isArray = in_array($group->data_type, ['array', 'array_of_objects']);

        if ($isArray) {
            // Array of objects
            $itemProperties = [];
            $itemRequired   = [];

            foreach ($group->inputs as $input) {
                if ($input->field_type === 'group' && $input->parent_group_id) {
                    // Nested group
                    $nestedGroup = IntegrationServiceInputGroup::with('inputs.dynamicService')
                        ->find($input->parent_group_id);
                    if ($nestedGroup) {
                        $itemProperties[$nestedGroup->key_name] = $this->buildGroupProperty($nestedGroup);
                    }
                    continue;
                }

                [$key, $prop] = $this->buildInputProperty($input);
                $itemProperties[$key] = $prop;

                if ($input->validation === 'required') {
                    $itemRequired[] = $key;
                }
            }

            return [
                'type'        => 'array',
                'description' => "Group: {$group->key_name}",
                'items'       => [
                    'type'       => 'object',
                    'properties' => (object) $itemProperties,
                    'required'   => $itemRequired,
                ],
            ];

        } else {
            // Object
            $objProperties = [];
            $objRequired   = [];

            foreach ($group->inputs as $input) {
                if ($input->field_type === 'group' && $input->parent_group_id) {
                    $nestedGroup = IntegrationServiceInputGroup::with('inputs.dynamicService')
                        ->find($input->parent_group_id);
                    if ($nestedGroup) {
                        $objProperties[$nestedGroup->key_name] = $this->buildGroupProperty($nestedGroup);
                    }
                    continue;
                }

                [$key, $prop] = $this->buildInputProperty($input);
                $objProperties[$key] = $prop;

                if ($input->validation === 'required') {
                    $objRequired[] = $key;
                }
            }

            return [
                'type'        => 'object',
                'description' => "Group: {$group->key_name}",
                'properties'  => (object) $objProperties,
                'required'    => $objRequired,
            ];
        }
    }

    // ── Map field_type to JSON schema type ─────────────────────────────────────

    private function mapFieldType(string $fieldType): string
    {
        return match ($fieldType) {
            'boolean'        => 'boolean',
            'dynamic_select' => 'dynamic_select',
            'select'         => 'select',
            'file',
            'file_url',
            'files',
            'files_url'      => 'string',
            'date',
            'datetime'       => 'string',
            default          => 'string',
        };
    }
}
