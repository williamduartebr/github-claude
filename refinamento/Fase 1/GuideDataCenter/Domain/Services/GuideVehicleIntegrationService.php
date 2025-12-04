<?php

namespace Src\GuideDataCenter\Domain\Services;

use Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;

/**
 * Serviço de integração: GuideDataCenter → VehicleDataCenter
 * Permite que guias validem e busquem dados de veículos do MySQL
 */
class GuideVehicleIntegrationService
{
    public function __construct(
        private VehicleMakeRepositoryInterface $makeRepository,
        private VehicleModelRepositoryInterface $modelRepository,
        private VehicleVersionRepositoryInterface $versionRepository
    ) {}

    /**
     * Valida se um veículo existe no banco MySQL
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int|null $year
     * @return bool
     */
    public function validateVehicle(
        string $makeSlug, 
        string $modelSlug, 
        ?int $year = null
    ): bool {
        $make = $this->makeRepository->findBySlug($makeSlug);
        
        if (!$make) {
            return false;
        }

        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        
        if (!$model) {
            return false;
        }

        if ($year) {
            $versions = $this->versionRepository->getByYear($year);
            $hasVersion = $versions->contains(function($version) use ($model) {
                return $version->model_id === $model->id;
            });
            return $hasVersion;
        }

        return true;
    }

    /**
     * Busca especificações de um veículo
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @return object|null
     */
    public function getVehicleSpecs(
        string $makeSlug,
        string $modelSlug,
        int $year
    ): ?object {
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        
        if (!$model) {
            return null;
        }

        // Buscar versões do modelo para o ano específico
        $versions = $this->versionRepository->getByModel($model->id);
        $version = $versions->where('year', $year)->first();

        if (!$version) {
            return null;
        }

        // Eager load relationships
        $version->load([
            'specs',
            'engineSpecs',
            'tireSpecs',
            'fluidSpecs',
            'batterySpecs',
            'dimensionsSpecs'
        ]);

        return $version;
    }

    /**
     * Busca dados formatados de um veículo para criar guias
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @param int $year
     * @return array|null
     */
    public function getVehicleDataForGuide(
        string $makeSlug,
        string $modelSlug,
        int $year
    ): ?array {
        $version = $this->getVehicleSpecs($makeSlug, $modelSlug, $year);

        if (!$version) {
            return null;
        }

        return [
            'make' => $version->model->make->name,
            'make_slug' => $version->model->make->slug,
            'model' => $version->model->name,
            'model_slug' => $version->model->slug,
            'version' => $version->name,
            'version_slug' => $version->slug,
            'year' => $version->year,
            'engine' => $version->engine_code ?? 'N/A',
            'fuel' => $version->fuel_type ?? 'N/A',
            'transmission' => $version->transmission ?? 'N/A',
            'specs' => [
                'power_hp' => $version->specs->power_hp ?? null,
                'power_rpm' => $version->specs->power_rpm ?? null,
                'torque_nm' => $version->specs->torque_nm ?? null,
                'torque_rpm' => $version->specs->torque_rpm ?? null,
                'engine_displacement' => $version->engineSpecs->displacement ?? null,
                'cylinders' => $version->engineSpecs->cylinders ?? null,
                'valves' => $version->engineSpecs->valves ?? null,
                'oil_type' => $version->fluidSpecs->engine_oil_type ?? null,
                'oil_capacity' => $version->fluidSpecs->engine_oil_capacity ?? null,
                'tire_front' => $version->tireSpecs->front_tire_size ?? null,
                'tire_rear' => $version->tireSpecs->rear_tire_size ?? null,
                'tire_pressure_front' => $version->tireSpecs->recommended_pressure_front ?? null,
                'tire_pressure_rear' => $version->tireSpecs->recommended_pressure_rear ?? null,
                'battery_type' => $version->batterySpecs->type ?? null,
                'battery_capacity' => $version->batterySpecs->capacity_ah ?? null,
            ]
        ];
    }

    /**
     * Lista veículos disponíveis (para criar guias)
     * 
     * @return array
     */
    public function getAvailableVehicles(): array
    {
        $makes = $this->makeRepository->getActive();
        
        return $makes->map(function($make) {
            $models = $this->modelRepository->getByMake($make->id);
            
            return [
                'make' => $make->name,
                'make_slug' => $make->slug,
                'models' => $models->map(function($model) {
                    $versions = $this->versionRepository->getByModel($model->id);
                    
                    return [
                        'model' => $model->name,
                        'model_slug' => $model->slug,
                        'year_start' => $versions->min('year') ?? $model->year_start,
                        'year_end' => $versions->max('year') ?? ($model->year_end ?? date('Y')),
                        'versions_count' => $versions->count()
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Valida dados de veículo antes de criar guia
     * 
     * @param array $data
     * @return array Lista de erros (vazio se válido)
     */
    public function validateGuideVehicleData(array $data): array
    {
        $errors = [];

        if (empty($data['make_slug']) || empty($data['model_slug'])) {
            $errors[] = "make_slug and model_slug are required";
            return $errors;
        }

        if (!$this->validateVehicle($data['make_slug'], $data['model_slug'])) {
            $errors[] = "Vehicle not found: {$data['make_slug']}/{$data['model_slug']}";
        }

        if (isset($data['year_start'])) {
            $year = (int) $data['year_start'];
            if ($year < 1900 || $year > (date('Y') + 1)) {
                $errors[] = "Invalid year: {$year}";
            }
        }

        return $errors;
    }

    /**
     * Busca marcas disponíveis
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableMakes(): \Illuminate\Support\Collection
    {
        return $this->makeRepository->getActive();
    }

    /**
     * Busca modelos de uma marca
     * 
     * @param string $makeSlug
     * @return \Illuminate\Support\Collection
     */
    public function getModelsByMake(string $makeSlug): \Illuminate\Support\Collection
    {
        $make = $this->makeRepository->findBySlug($makeSlug);
        
        if (!$make) {
            return collect();
        }

        return $this->modelRepository->getByMake($make->id);
    }

    /**
     * Busca anos disponíveis para um modelo
     * 
     * @param string $makeSlug
     * @param string $modelSlug
     * @return array
     */
    public function getAvailableYears(string $makeSlug, string $modelSlug): array
    {
        $model = $this->modelRepository->findBySlug($makeSlug, $modelSlug);
        
        if (!$model) {
            return [];
        }

        $versions = $this->versionRepository->getByModel($model->id);
        
        return $versions->pluck('year')->unique()->sort()->values()->toArray();
    }
}