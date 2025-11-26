<?php

namespace Src\VehicleDataCenter\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleVersion;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;

class VehicleVersionRepository implements VehicleVersionRepositoryInterface
{
    public function all(): Collection
    {
        return VehicleVersion::with(['model.make', 'specs'])->get();
    }

    public function findById(int $id)
    {
        return VehicleVersion::with([
            'model.make',
            'specs',
            'engineSpecs',
            'tireSpecs',
            'fluidSpecs',
            'batterySpecs',
            'dimensionsSpecs'
        ])->find($id);
    }

    public function findBySlug(string $makeSlug, string $modelSlug, int $year, string $versionSlug)
    {
        return VehicleVersion::whereHas('model', function ($query) use ($makeSlug, $modelSlug) {
            $query->where('slug', $modelSlug)
                ->whereHas('make', function ($q) use ($makeSlug) {
                    $q->where('slug', $makeSlug);
                });
        })->where('slug', $versionSlug)
            ->where('year', $year)
            ->with([
                'model.make',
                'specs',
                'engineSpecs',
                'tireSpecs',
                'fluidSpecs',
                'batterySpecs',
                'dimensionsSpecs'
            ])
            ->first();
    }

    public function create(array $data)
    {
        return VehicleVersion::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $version = VehicleVersion::find($id);
        if (!$version) {
            return false;
        }
        return $version->update($data);
    }

    public function delete(int $id): bool
    {
        $version = VehicleVersion::find($id);
        if (!$version) {
            return false;
        }
        return $version->delete();
    }

    public function getByModel(int $modelId): Collection
    {
        return VehicleVersion::where('model_id', $modelId)->active()->get();
    }

    public function getByYear(int $year): Collection
    {
        return VehicleVersion::byYear($year)->active()->get();
    }

    public function getByFuelType(string $fuelType): Collection
    {
        return VehicleVersion::byFuelType($fuelType)->active()->get();
    }
}
