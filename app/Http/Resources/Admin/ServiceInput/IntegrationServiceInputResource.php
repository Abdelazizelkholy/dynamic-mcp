<?php


namespace App\Http\Resources\Admin\ServiceInput;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceInputResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'group_id' => $this->group_id,
            'is_standalone' => $this->isStandalone(),

            'field_type' => $this->field_type,
            'field_type_label' => $this->field_type_label,

            'key' => $this->key,
            'placeholder' => $this->placeholder,
            'type' => $this->type,
            'key_type' => $this->key_type,
            'validation' => $this->validation,
            'require_from' => $this->require_from,

            // select
            'options' => $this->options ?? [],

            // dynamic_select
            'dynamic_service_id' => $this->dynamic_service_id,
            'dynamic_service' => $this->when(
                $this->relationLoaded('dynamicService') && $this->dynamicService,
                fn() => [
                    'id' => $this->dynamicService->id,
                    'service_name' => $this->dynamicService->service_name,
                ]
            ),

            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
