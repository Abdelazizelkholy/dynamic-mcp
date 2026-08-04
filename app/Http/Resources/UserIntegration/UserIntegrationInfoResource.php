<?php

namespace App\Http\Resources\UserIntegration;

use Illuminate\Http\Resources\Json\JsonResource;

class UserIntegrationInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'   => $this->id,
            // Stored in the `email` column on user_integration_infos, but exposed as
            // `name` since that's what this value actually represents.
            'name' => $this->email,
        ];
    }
}
