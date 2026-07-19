<?php

namespace App\Http\Resources\UserIntegration;

use Illuminate\Http\Resources\Json\JsonResource;

class UserIntegrationInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'email' => $this->email,
        ];
    }
}
