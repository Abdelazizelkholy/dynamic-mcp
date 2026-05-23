<?php

namespace App\Http\Resources\Admin\GlobalBody;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationGlobalBodyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'integration_id'     => $this->integration_id,
            'key'                => $this->key,
            'require_from'       => $this->require_from,
            'require_from_label' => $this->require_from_label,
            'value'              => $this->value,
            'label'              => $this->label,
            'description'        => $this->description,
            'order'              => $this->order,
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
        ];
    }
}
