<?php

namespace Src\VehicleDataCenter\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface VehicleModelRepositoryInterface
{
    public function all(): Collection;
    public function findById(int $id);
    public function findBySlug(string $makeSlug, string $modelSlug);
    public function create(array $data);
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByMake(int $makeId): Collection;
    public function getByCategory(string $category): Collection;
}
