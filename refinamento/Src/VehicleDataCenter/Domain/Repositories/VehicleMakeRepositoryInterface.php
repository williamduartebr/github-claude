<?php

namespace Src\VehicleDataCenter\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;

interface VehicleMakeRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id): ?VehicleMake;
    public function findBySlug(string $slug): ?VehicleMake;
    public function create(array $data): VehicleMake;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActive(): Collection;
    public function getByType(string $type): Collection;
}
