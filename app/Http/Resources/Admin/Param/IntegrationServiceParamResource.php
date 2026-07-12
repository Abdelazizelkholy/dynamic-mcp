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
            'type' => $this->type,             // static | user_integration | params
            'type_label' => $this->type_label,       // Static | User Integration | From Input
            'value' => $this->value,            // "test" or "Authorization"
            'input_id' => $this->input_id,        // linked input id when type = params, null otherwise
            'url_segment' => $this->toUrlSegment(),   // "test" or "[Authorization]" or "{key}"
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
