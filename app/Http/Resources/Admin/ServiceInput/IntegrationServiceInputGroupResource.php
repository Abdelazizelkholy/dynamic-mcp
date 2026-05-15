<?php


namespace App\Http\Resources\Admin\ServiceInput;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceInputGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'key_name' => $this->key_name,
            'data_type' => $this->data_type,
            'data_type_label' => $this->data_type_label,
            'order' => $this->order,

            // Child inputs — loaded with group
            'inputs' => IntegrationServiceInputResource::collection(
                $this->whenLoaded('inputs')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
