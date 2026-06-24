<?php

namespace App\Http\Resources\Admin\ResponseView;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceResponseViewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'key'                    => $this->key,
            'data_type'              => $this->data_type,
            'data_type_label'        => $this->data_type_label,
            'order'                  => $this->order,
            'created_at'             => $this->created_at?->toISOString(),
            'updated_at'             => $this->updated_at?->toISOString(),
        ];
    }
}
