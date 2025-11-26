<?php

namespace Src\VehicleDataCenter\Domain\Services;

use Illuminate\Support\Str;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleMongoRepositoryInterface;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleEngineSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleTireSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleFluidSpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleBatterySpec;
use Src\VehicleDataCenter\Domain\Eloquent\VehicleDimensionsSpec;

class VehicleIngestionService
{
    public function __construct(
        private VehicleMakeRepositoryInterface $makeRepository,
        private VehicleModelRepositoryInterface $modelRepository,
        private VehicleVersionRepositoryInterface $versionRepository,
        private VehicleSpecsRepositoryInterface $specsRepository,
        private VehicleMongoRepositoryInterface $mongoRepository
    ) {}

    public function ingestVehicleData(array $payload): array
    {
        \DB::beginTransaction();
        try {
            // 1. Validar payload
            $validated = $this->validatePayload($payload);

            // 2. Criar/Atualizar Make
            $make = $this->createOrUpdateMake($validated['make']);

            // 3. Criar/Atualizar Model
            $model = $this->createOrUpdateModel($make->id, $validated['model']);

            // 4. Criar/Atualizar Version
            $version = $this->createOrUpdateVersion($model->id, $validated['version']);

            // 5. Criar/Atualizar Specs MySQL
            $this->createOrUpdateSpecs($version->id, $validated['specs'] ?? []);

            // 6. Criar/Atualizar Documento MongoDB
            $this->createOrUpdateMongoDocument($version, $validated);

            \DB::commit();

            return [
                'success' => true,
                'version_id' => $version->id,
                'message' => 'Vehicle data ingested successfully'
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function validatePayload(array $payload): array
    {
        // Validação básica
        if (!isset($payload['make']['name']) || !isset($payload['model']['name']) || !isset($payload['version']['name'])) {
            throw new \InvalidArgumentException('Missing required fields: make, model, or version name');
        }

        return $payload;
    }

    private function createOrUpdateMake(array $makeData): mixed
    {
        $slug = Str::slug($makeData['name']);
        $make = $this->makeRepository->findBySlug($slug);

        if (!$make) {
            return $this->makeRepository->create([
                'name' => $makeData['name'],
                'slug' => $slug,
                'logo_url' => $makeData['logo_url'] ?? null,
                'country_origin' => $makeData['country_origin'] ?? null,
                'type' => $makeData['type'] ?? 'car',
                'is_active' => true,
                'metadata' => $makeData['metadata'] ?? null,
            ]);
        }

        return $make;
    }

    private function createOrUpdateModel(int $makeId, array $modelData): mixed
    {
        $slug = Str::slug($modelData['name']);
        $model = $this->modelRepository->findBySlug(
            $this->makeRepository->findById($makeId)->slug,
            $slug
        );

        if (!$model) {
            return $this->modelRepository->create([
                'make_id' => $makeId,
                'name' => $modelData['name'],
                'slug' => $slug,
                'year_start' => $modelData['year_start'] ?? null,
                'year_end' => $modelData['year_end'] ?? null,
                'category' => $modelData['category'] ?? 'sedan',
                'is_active' => true,
                'metadata' => $modelData['metadata'] ?? null,
            ]);
        }

        return $model;
    }

    private function createOrUpdateVersion(int $modelId, array $versionData): mixed
    {
        $slug = Str::slug($versionData['name']);

        $version = $this->versionRepository->create([
            'model_id' => $modelId,
            'name' => $versionData['name'],
            'slug' => $slug,
            'year' => $versionData['year'],
            'engine_code' => $versionData['engine_code'] ?? null,
            'fuel_type' => $versionData['fuel_type'] ?? null,
            'transmission' => $versionData['transmission'] ?? null,
            'price_msrp' => $versionData['price_msrp'] ?? null,
            'is_active' => true,
            'metadata' => $versionData['metadata'] ?? null,
        ]);

        return $version;
    }

    private function createOrUpdateSpecs(int $versionId, array $specsData): void
    {
        // General Specs
        if (isset($specsData['general'])) {
            $this->specsRepository->create(array_merge(
                ['version_id' => $versionId],
                $specsData['general']
            ));
        }

        // Engine Specs
        if (isset($specsData['engine'])) {
            VehicleEngineSpec::create(array_merge(
                ['version_id' => $versionId],
                $specsData['engine']
            ));
        }

        // Tire Specs
        if (isset($specsData['tires'])) {
            VehicleTireSpec::create(array_merge(
                ['version_id' => $versionId],
                $specsData['tires']
            ));
        }

        // Fluid Specs
        if (isset($specsData['fluids'])) {
            VehicleFluidSpec::create(array_merge(
                ['version_id' => $versionId],
                $specsData['fluids']
            ));
        }

        // Battery Specs
        if (isset($specsData['battery'])) {
            VehicleBatterySpec::create(array_merge(
                ['version_id' => $versionId],
                $specsData['battery']
            ));
        }

        // Dimensions Specs
        if (isset($specsData['dimensions'])) {
            VehicleDimensionsSpec::create(array_merge(
                ['version_id' => $versionId],
                $specsData['dimensions']
            ));
        }
    }

    private function createOrUpdateMongoDocument($version, array $payload): void
    {
        $make = $version->model->make;
        $model = $version->model;

        $this->mongoRepository->createDocument([
            'version_id' => $version->id,
            'make_slug' => $make->slug,
            'model_slug' => $model->slug,
            'version_slug' => $version->slug,
            'year' => $version->year,
            'full_name' => "{$make->name} {$model->name} {$version->name} {$version->year}",
            'payload' => $payload,
            'enriched_data' => [],
            'metadata' => [
                'source' => $payload['source'] ?? 'manual',
                'ingested_at' => now()->toIso8601String()
            ]
        ]);
    }
}
