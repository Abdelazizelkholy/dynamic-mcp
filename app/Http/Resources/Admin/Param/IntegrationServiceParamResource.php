<?php


namespace App\Http\Resources\Admin\Param;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceParamResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'type' => $this->type,             // static | user_integration
            'type_label' => $this->type_label,       // Static | User Integration
            'value' => $this->value,            // "test" or "Authorization"
            'url_segment' => $this->toUrlSegment(),   // "test" or "[Authorization]"
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
