<?php

namespace Src\VehicleDataCenter\Domain\Repositories;

interface VehicleSpecsRepositoryInterface
{
    public function findByVersionId(int $versionId);
    public function create(array $data);
    public function update(int $versionId, array $data): bool;
    public function delete(int $versionId): bool;
    public function getCompleteSpecs(int $versionId): array;
}
