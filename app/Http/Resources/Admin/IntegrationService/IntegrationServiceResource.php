<?php


namespace App\Http\Resources\Admin\IntegrationService;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'integration_id'         => $this->integration_id,
            'service_name_en'        => $this->service_name_en,
            'service_name_ar'        => $this->service_name_ar,
            'http_method'            => $this->http_method,
            'content_type'           => $this->content_type,
            'endpoint_path'          => $this->endpoint_path,

            // ✅ Full URL
            'base_url'               => $this->base_url_override
                ?? $this->integration?->base_api_url,
            'base_url_override'      => $this->base_url_override,
            'full_endpoint_url'      => rtrim(
                    $this->base_url_override ?? $this->integration?->base_api_url,
                    '/'
                ) . '/' . ltrim($this->endpoint_path, '/'),

            'logo_url'               => $this->logo_url,
            'description_en'         => $this->description_en,
            'description_ar'         => $this->description_ar,
            'is_enabled'             => $this->is_enabled,
            'inherit_global_headers' => $this->inherit_global_headers,
            'long_term_execution'    => $this->long_term_execution,
            'response_example'       => $this->response_example,
            'dependency_service_ids' => $this->dependency_service_ids ?? [],
            'dependency_services'    => $this->formatDependencies(),
            'order'                  => $this->order,
            'created_at'             => $this->created_at?->toISOString(),
            'updated_at'             => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Resolve dependency IDs to minimal service objects for display.
     */
    private function formatDependencies(): array
    {
        if (empty($this->dependency_service_ids)) {
            return [];
        }

        return \App\Models\IntegrationService::whereIn('id', $this->dependency_service_ids)
            ->get(['id', 'service_name_en', 'service_name_ar', 'http_method'])
            ->map(fn($s) => [
                'id' => $s->id,
                'service_name_en' => $s->service_name_en,
                'service_name_ar' => $s->service_name_ar,
                'http_method' => $s->http_method,
            ])
            ->toArray();
    }
}
