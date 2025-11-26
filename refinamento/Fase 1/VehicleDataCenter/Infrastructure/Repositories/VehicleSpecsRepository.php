<?php

namespace Src\VehicleDataCenter\Infrastructure\Repositories;

use Src\VehicleDataCenter\Domain\Eloquent\VehicleSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleEngineSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleTireSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleFluidSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleBatterySpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleDimensionsSpec;
use Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface;

class VehicleSpecsRepository implements VehicleSpecsRepositoryInterface
{
    public function findByVersionId(int $versionId)
    {
        return VehicleSpec::where('version_id', $versionId)->first();
    }

    public function create(array $data)
    {
        return VehicleSpec::create($data);
    }

    public function update(int $versionId, array $data): bool
    {
        $spec = VehicleSpec::where('version_id', $versionId)->first();
        if (!$spec) {
            return false;
        }
        return $spec->update($data);
    }

    public function delete(int $versionId): bool
    {
        $spec = VehicleSpec::where('version_id', $versionId)->first();
        if (!$spec) {
            return false;
        }
        return $spec->delete();
    }

    public function getCompleteSpecs(int $versionId): array
    {
        $specs = VehicleSpec::where('version_id', $versionId)->first();
        $engineSpecs = VehicleEngineSpec::where('version_id', $versionId)->first();
        $tireSpecs = VehicleTireSpec::where('version_id', $versionId)->first();
        $fluidSpecs = VehicleFluidSpec::where('version_id', $versionId)->first();
        $batterySpecs = VehicleBatterySpec::where('version_id', $versionId)->first();
        $dimensionsSpecs = VehicleDimensionsSpec::where('version_id', $versionId)->first();

        return [
            'general' => $specs ? $specs->toArray() : null,
            'engine' => $engineSpecs ? $engineSpecs->toArray() : null,
            'tires' => $tireSpecs ? $tireSpecs->toArray() : null,
            'fluids' => $fluidSpecs ? $fluidSpecs->toArray() : null,
            'battery' => $batterySpecs ? $batterySpecs->toArray() : null,
            'dimensions' => $dimensionsSpecs ? $dimensionsSpecs->toArray() : null,
        ];
    }
}
