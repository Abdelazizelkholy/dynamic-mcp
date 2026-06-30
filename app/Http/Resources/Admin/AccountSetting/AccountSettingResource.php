<?php


namespace App\Http\Resources\Admin\AccountSetting;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'integration_id'   => $this->integration_id,
            'base_url'         => $this->base_url,
            'http_method'      => $this->http_method,
            'email_key'        => $this->email_key,
            'response_example' => $this->response_example ?? [],
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
