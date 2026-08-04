<?php

namespace App\Http\Resources\UserIntegration;

use Illuminate\Http\Resources\Json\JsonResource;

class UserIntegrationResource extends JsonResource
{
    // Deliberately excludes `credentials` (access/refresh tokens) from API output.
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'integration_id'    => $this->integration_id,
            'integration_name'  => $this->whenLoaded('integration', fn () => $this->integration->name),
            'status'            => $this->status,
            // Login data fetched from the provider via IntegrationAccountSetting::email_key
            // (see FetchUserIntegrationInfo), stored as `email` on user_integration_infos —
            // exposed here as `name` since that's what this value actually represents.
            'name'              => $this->whenLoaded('info', fn () => $this->info?->email),
            'is_expired'        => $this->isExpired(),
            'connected_at'      => $this->connected_at?->toISOString(),
            'expires_at'        => $this->expires_at?->toISOString(),
            'last_refreshed_at' => $this->last_refreshed_at?->toISOString(),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),
        ];
    }
}
