<?php

namespace Src\VehicleDataCenter\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleModel;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;

class VehicleModelRepository implements VehicleModelRepositoryInterface
{
    public function all(): Collection
    {
        return VehicleModel::with(['make', 'versions'])->get();
    }

    public function findById(int $id)
    {
        return VehicleModel::with(['make', 'versions'])->find($id);
    }

    public function findBySlug(string $makeSlug, string $modelSlug)
    {
        return VehicleModel::whereHas('make', function ($query) use ($makeSlug) {
            $query->where('slug', $makeSlug);
        })->where('slug', $modelSlug)->with(['make', 'versions'])->first();
    }

    public function create(array $data)
    {
        return VehicleModel::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = VehicleModel::find($id);
        if (!$model) {
            return false;
        }
        return $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = VehicleModel::find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    public function getByMake(int $makeId): Collection
    {
        return VehicleModel::where('make_id', $makeId)->active()->get();
    }

    public function getByCategory(string $category): Collection
    {
        return VehicleModel::byCategory($category)->active()->get();
    }
}
