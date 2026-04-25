<?php

namespace App\Http\Resources\Admin\AuthStep;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'auth_type'         => $this->auth_type,           // oauth2 | api_key | basic | bearer | custom
            'http_method'       => $this->http_method,
            'base_endpoint_url' => $this->base_endpoint_url,
            'inputs'            => $this->inputs ?? [],         // fields admin/user must fill
            'outputs'           => $this->outputs ?? [],        // keys returned in response
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
}
