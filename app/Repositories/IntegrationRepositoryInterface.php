<?php

namespace App\Repositories;

use App\Models\Integration;

interface IntegrationRepositoryInterface
{
    public function all();
    public function find(int $id): ?Integration;
    public function create(array $data): Integration;
    public function update(int $id, array $data): ?Integration;
    public function delete(int $id): bool;
}
