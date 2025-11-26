<?php

namespace Src\VehicleDataCenter\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface VehicleVersionRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id);
    public function findBySlug(string $makeSlug, string $modelSlug, int $year, string $versionSlug);
    public function create(array $data);
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByModel(int $modelId): Collection;
    public function getByYear(int $year): Collection;
    public function getByFuelType(string $fuelType): Collection;
}
