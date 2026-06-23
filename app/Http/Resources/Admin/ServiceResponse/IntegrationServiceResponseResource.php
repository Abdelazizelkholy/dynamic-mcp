<?php

namespace App\Http\Resources\Admin\ServiceResponse;

use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationServiceResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'integration_service_id' => $this->integration_service_id,
            'response_example'       => $this->response_example,
            'output_filter_keys'     => $this->output_filter_keys ?? [],
            'created_at'             => $this->created_at?->toISOString(),
            'updated_at'             => $this->updated_at?->toISOString(),
        ];
    }
}
