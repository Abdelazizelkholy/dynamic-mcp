<?php


namespace App\Http\Resources\Admin\ServiceHeader;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceHeaderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'header_key' => $this->header_key,
            'require_from' => $this->require_from,
            'require_from_label' => $this->require_from_label,
            'value' => $this->value,
            'label' => $this->label,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
