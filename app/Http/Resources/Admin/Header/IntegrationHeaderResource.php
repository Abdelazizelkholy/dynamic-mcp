<?php

namespace App\Http\Resources\Admin\Header;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationHeaderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'integration_id' => $this->integration_id,
            'type'           => $this->type,              // normal | bearer | basic_auth
            'type_label'     => $this->typeLabel(),
            'header_key'     => $this->header_key,        // e.g. Authorization
            'require_from'   => $this->require_from,      // admin | user_integration
            'value'          => $this->value,             // static value or output key name
            'label'          => $this->label,
            'description'    => $this->description,
            'is_active'      => $this->is_active,
            'order'          => $this->order,
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }

    private function typeLabel(): string
    {
        return match ($this->type) {
            'bearer'     => 'Bearer',
            'basic_auth' => 'Basic Auth',
            default      => 'Normal',
        };
    }
}
