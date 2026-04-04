<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'roles' => $this->getRoleNames(),
            'profile_picture' => $this->profile_picture_url,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
