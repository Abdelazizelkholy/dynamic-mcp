<?php

namespace App\Repositories;

interface IntegrationAuthStepRepositoryInterface
{
    public function allByIntegration(int $integrationId): \Illuminate\Database\Eloquent\Collection;

    public function find(int $id): ?\App\Models\IntegrationAuthStep;

    public function create(array $data): \App\Models\IntegrationAuthStep;

    public function update(int $id, array $data): \App\Models\IntegrationAuthStep;

    public function delete(int $id): bool;

    public function reorder(int $integrationId, array $orderedIds): bool;

    public function toggleActive(int $id): \App\Models\IntegrationAuthStep;
}
