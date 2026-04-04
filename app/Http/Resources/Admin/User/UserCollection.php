<?php

namespace App\Http\Resources\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->count(),
            ],
        ];
    }
}
