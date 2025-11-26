<?php

namespace Src\VehicleDataCenter\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleMake;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;

class VehicleMakeRepository implements VehicleMakeRepositoryInterface
{
    public function all(): Collection
    {
        return VehicleMake::with('models')->get();
    }

    public function findById(int $id): ?VehicleMake
    {
        return VehicleMake::with('models')->find($id);
    }

    public function findBySlug(string $slug): ?VehicleMake
    {
        return VehicleMake::with('models')->where('slug', $slug)->first();
    }

    public function create(array $data): VehicleMake
    {
        return VehicleMake::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $make = VehicleMake::find($id);
        if (!$make) {
            return false;
        }
        return $make->update($data);
    }

    public function delete(int $id): bool
    {
        $make = VehicleMake::find($id);
        if (!$make) {
            return false;
        }
        return $make->delete();
    }

    public function getActive(): Collection
    {
        return VehicleMake::active()->with('activeModels')->get();
    }

    public function getByType(string $type): Collection
    {
        return VehicleMake::byType($type)->active()->get();
    }
}
