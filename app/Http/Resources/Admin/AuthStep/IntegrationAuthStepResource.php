<?php

namespace App\Http\Resources\Admin\AuthStep;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class IntegrationAuthStepResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'integration_id'    => $this->integration_id,
            'name'              => $this->name,
            'step_type'         => $this->step_type,           // login_callback | set_credentials | refresh_token
            'step_type_label'   => $this->stepTypeLabel(),
            'auth_type'         => $this->auth_type,           // call | redirect
            'http_method'       => $this->http_method,
            'base_endpoint_url' => $this->base_endpoint_url,
            'inputs'            => $this->normalizeInputs(),    // fields admin/user must fill
            'outputs'           => $this->normalizeOutputs(),   // keys returned in response
            'response_example'  => $this->response_example,    // shown in UI code block
            'order'             => $this->order,
            'is_active'         => $this->is_active,
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }

    private function stepTypeLabel(): string
    {
        return match ($this->step_type) {
            'login_callback'  => 'Login & Callback',
            'set_credentials' => 'Set Credentials',
            'refresh_token'   => 'Refresh Access Token',
            default           => $this->step_type,
        };
    }

    /**
     * Always returns a list of { key, label, type, require_from, value? } objects.
     * Normalizes legacy rows stored as a flat "key => value" object.
     */
    private function normalizeInputs(): array
    {
        $inputs = $this->inputs ?? [];

        if (empty($inputs)) {
            return [];
        }

        if (array_is_list($inputs)) {
            return $inputs;
        }

        return collect($inputs)
            ->map(fn ($value, $key) => [
                'key'          => $key,
                'label'        => Str::headline($key),
                'type'         => 'body',
                'require_from' => 'admin',
                'value'        => $value !== 'string' ? $value : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Always returns a flat list of output key strings.
     * Normalizes legacy rows stored as a "key => key" object.
     */
    private function normalizeOutputs(): array
    {
        $outputs = $this->outputs ?? [];

        if (empty($outputs)) {
            return [];
        }

        return array_is_list($outputs) ? $outputs : array_keys($outputs);
    }
}
