<?php

namespace App\Http\Resources\Admin\Integration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class IntegrationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public $collects = IntegrationCollection::class;

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
