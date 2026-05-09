<?php

namespace App\Http\Resources\Admin\Integration;

use App\Http\Resources\Admin\AuthStep\IntegrationAuthStepResource;
use App\Http\Resources\Admin\Header\IntegrationHeaderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'base_api_url' => $this->base_api_url,
            'documentation_url' => $this->documentation_url,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'publish' => $this->publish,
            'media_url' => $this->media_url,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'category' => 'Marketing',
            'enable' => $this->enable,
            'auth_steps' => IntegrationAuthStepResource::collection(
                $this->whenLoaded('authSteps')
            ),
            'headers'    => IntegrationHeaderResource::collection(
                $this->whenLoaded('headers')
            ),
        ];
    }
}
