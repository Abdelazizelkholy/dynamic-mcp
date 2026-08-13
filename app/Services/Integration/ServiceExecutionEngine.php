<?php

namespace App\Services\Integration;

use App\Models\IntegrationGlobalBody;
use App\Models\IntegrationHeader;
use App\Models\IntegrationService;
use App\Models\IntegrationServiceHeader;
use App\Models\IntegrationServiceInput;
use App\Models\IntegrationServiceInputGroup;
use App\Models\IntegrationServiceResponse;
use App\Models\UserIntegration;
use Illuminate\Support\Facades\Http;

/**
 * Executes a single admin-defined IntegrationService call on behalf of a
 * connected UserIntegration: builds the URL (incl. {path} params), query,
 * body (incl. nested/array groups) and headers from the service's stored
 * definition + the caller's inputs, sends the HTTP request, and applies
 * IntegrationServiceResponse::output_filter_keys to the result.
 */
class ServiceExecutionEngine
{
    public function execute(IntegrationService $service, UserIntegration $userIntegration, array $inputData): array
    {
        $integration = $service->integration;

        [$path, $pathParamKeys] = $this->resolvePath($service->endpoint_path, $inputData);
        $baseUrl = rtrim($service->base_url_override ?: $integration->base_api_url, '/');
        $url = $baseUrl.'/'.ltrim($path, '/');

        $query = [];
        $body  = [];
        $headers = [];

        foreach ($service->standaloneInputs as $input) {
            if (in_array($input->key, $pathParamKeys, true)) {
                continue;
            }

            $value = $this->resolveInputValue($input, $inputData, $userIntegration);
            if ($value === null) {
                continue;
            }

            match ($input->key_type) {
                'query'  => $query[$input->key] = $value,
                'header' => $headers[$input->key] = $value,
                default  => $body[$input->key] = $value,
            };
        }

        foreach ($service->inputGroups as $group) {
            // Nested groups are only reachable through a parent's field_type=group
            // input (see resolveGroupItem) — skip them here to avoid double-adding
            // at the top level.
            if ($this->isNestedGroup($group)) {
                continue;
            }

            $value = $this->resolveGroup($group, $inputData[$group->key_name] ?? null, $userIntegration);
            if ($value !== null) {
                $body[$group->key_name] = $value;
            }
        }

        foreach (IntegrationGlobalBody::where('integration_id', $integration->id)->orderBy('order')->get() as $globalField) {
            $value = $this->resolveAdminOrCredential($globalField->require_from, $globalField->value, $userIntegration);
            if ($value !== null) {
                $body[$globalField->key] = $value;
            }
        }

        if ($service->inherit_global_headers) {
            foreach (IntegrationHeader::where('integration_id', $integration->id)->where('is_active', true)->orderBy('order')->get() as $header) {
                $resolved = $this->resolveHeaderSource($header->require_from, $header->value, $inputData, $userIntegration);
                $headers[$header->header_key] = $header->buildHeaderValue($resolved);
            }
        }

        $serviceHeaders = IntegrationServiceHeader::where('integration_service_id', $service->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->groupBy('header_key');

        foreach ($serviceHeaders as $headerKey => $group) {
            $first = $group->first();

            if ($first->type === 'basic_auth') {
                $username = $group->firstWhere('label', 'username');
                $password = $group->firstWhere('label', 'password');

                $user = $username ? $this->resolveHeaderSource($username->require_from, $username->value, $inputData, $userIntegration) : '';
                $pass = $password ? $this->resolveHeaderSource($password->require_from, $password->value, $inputData, $userIntegration) : '';

                $headers[$headerKey] = 'Basic '.base64_encode("{$user}:{$pass}");
                continue;
            }

            $resolved = $this->resolveHeaderSource($first->require_from, $first->value, $inputData, $userIntegration);
            $headers[$headerKey] = $first->buildHeaderValue($resolved);
        }

        $method = strtoupper($service->http_method);
        $request = Http::withHeaders($headers);

        $options = ['query' => $query];
        if (! in_array($method, ['GET', 'DELETE'], true)) {
            $options[$service->content_type === 'application/x-www-form-urlencoded' ? 'form_params' : 'json'] = $body;
        }

        $response = $request->send($method, $url, $options);
        $responseBody = $response->json() ?? [];

        $filterKeys = IntegrationServiceResponse::where('integration_service_id', $service->id)->value('output_filter_keys');

        return [
            'success'     => $response->successful(),
            'http_status' => $response->status(),
            'url'         => $url,
            'data'        => $filterKeys ? $this->filterResponse($responseBody, $filterKeys) : $responseBody,
            'raw'         => $responseBody,
        ];
    }

    // ── URL ────────────────────────────────────────────────────────────────────

    private function resolvePath(string $path, array $inputData): array
    {
        $keys = [];

        preg_match_all('/\{(\w+)\}/', $path, $matches);

        foreach ($matches[1] as $key) {
            $keys[] = $key;
            $path = str_replace('{'.$key.'}', (string) data_get($inputData, $key), $path);
        }

        return [$path, $keys];
    }

    // ── Groups ─────────────────────────────────────────────────────────────────

    private function isNestedGroup(IntegrationServiceInputGroup $group): bool
    {
        return IntegrationServiceInput::where('parent_group_id', $group->id)->exists();
    }

    private function resolveGroup(IntegrationServiceInputGroup $group, mixed $rawValue, UserIntegration $userIntegration): mixed
    {
        if (in_array($group->data_type, ['array', 'array_of_objects'], true)) {
            if (! is_array($rawValue)) {
                return null;
            }

            return array_map(
                fn ($item) => $this->resolveGroupItem($group, (array) $item, $userIntegration),
                $rawValue
            );
        }

        return $this->resolveGroupItem($group, (array) ($rawValue ?? []), $userIntegration);
    }

    private function resolveGroupItem(IntegrationServiceInputGroup $group, array $itemData, UserIntegration $userIntegration): array
    {
        $result = [];

        foreach ($group->inputs as $input) {
            if ($input->field_type === 'group' && $input->parent_group_id) {
                $nested = IntegrationServiceInputGroup::with('inputs')->find($input->parent_group_id);
                if ($nested) {
                    $value = $this->resolveGroup($nested, $itemData[$nested->key_name] ?? null, $userIntegration);
                    if ($value !== null) {
                        $result[$nested->key_name] = $value;
                    }
                }
                continue;
            }

            $value = $this->resolveInputValue($input, $itemData, $userIntegration);
            if ($value !== null) {
                $result[$input->key] = $value;
            }
        }

        return $result;
    }

    // ── Value resolution ──────────────────────────────────────────────────────

    private function resolveInputValue(IntegrationServiceInput $input, array $inputData, UserIntegration $userIntegration): mixed
    {
        return match ($input->require_from) {
            'admin'             => $input->value,
            'user_integration'  => data_get($userIntegration->credentials, $input->value ?: $input->key),
            default             => data_get($inputData, $input->key), // user, front, response, dependency_service
        };
    }

    private function resolveAdminOrCredential(string $requireFrom, ?string $value, UserIntegration $userIntegration): mixed
    {
        return match ($requireFrom) {
            'user_integration' => data_get($userIntegration->credentials, $value),
            default            => $value, // admin
        };
    }

    private function resolveHeaderSource(string $requireFrom, ?string $value, array $inputData, UserIntegration $userIntegration): ?string
    {
        return match ($requireFrom) {
            'user'             => data_get($inputData, $value),
            'user_integration' => data_get($userIntegration->credentials, $value),
            default            => $value, // admin
        };
    }

    // ── Response filtering ────────────────────────────────────────────────────

    private function filterResponse(array $response, array $filterKeys): array
    {
        $result = [];

        foreach ($filterKeys as $key) {
            $result[$key] = data_get($response, $key);
        }

        return $result;
    }
}
