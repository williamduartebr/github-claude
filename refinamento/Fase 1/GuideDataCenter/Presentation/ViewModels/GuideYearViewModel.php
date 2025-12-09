<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Presentation\ViewModels;

use Src\GuideDataCenter\Domain\Services\GuideVehicleIntegrationService;
use Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface;

/**
 * ViewModel para página de ano (lista versões)
 * 
 * Rota: /guias/{category}/{make}/{model}/{year}
 * View: guide-data-center::guide.year
 * Exemplo: /guias/oleo/toyota/corolla/2025
 * 
 * ✅ REFINADO V2: Remove TODOS os mocks, usa dados reais do MySQL
 */
class GuideYearViewModel
{
    private $category;
    private $make;
    private $model;
    private string $year;
    private GuideVehicleIntegrationService $vehicleIntegration;
    private VehicleVersionRepositoryInterface $versionRepository;

    public function __construct($category, $make, $model, string $year)
    {
        $this->category = $category;
        $this->make = $make;
        $this->model = $model;
        $this->year = $year;
        
        // Injetar serviços de integração
        $this->vehicleIntegration = app(GuideVehicleIntegrationService::class);
        $this->versionRepository = app(VehicleVersionRepositoryInterface::class);
    }

    public function getCategory(): array
    {
        return [
            'name' => $this->category->name ?? 'Óleo',
            'slug' => $this->category->slug ?? 'oleo',
        ];
    }

    public function getMake(): array
    {
        return [
            'name' => $this->make->name ?? 'Toyota',
            'slug' => $this->make->slug ?? 'toyota',
        ];
    }

    public function getModel(): array
    {
        return [
            'name' => $this->model->name ?? 'Corolla',
            'slug' => $this->model->slug ?? 'corolla',
        ];
    }

    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * ✅ REFINADO: Retorna versões disponíveis REAIS do MySQL para este ano
     * Remove array mockado hardcoded
     * 
     * @return array
     */
    public function getVersions(): array
    {
        $modelSlug = $this->model->slug ?? 'corolla';
        $makeSlug = $this->make->slug ?? 'toyota';
        $year = (int) $this->year;

        // Buscar versões REAIS do MySQL
        $versions = $this->versionRepository->getByYear($year);
        
        // Filtrar apenas versões deste modelo específico
        $filteredVersions = $versions->filter(function($version) use ($makeSlug, $modelSlug) {
            return $version->model->slug === $modelSlug 
                && $version->model->make->slug === $makeSlug;
        });

        // Se não houver versões, retornar array vazio
        if ($filteredVersions->isEmpty()) {
            return [];
        }

        // Mapear para formato esperado pela view
        return $filteredVersions->map(function($version) {
            return [
                'id' => $version->id,
                'version' => $version->name,
                'engine' => $version->engine_code ?? 'Motor não especificado',
                'fuel' => $this->translateFuelType($version->fuel_type),
                'transmission' => $this->translateTransmission($version->transmission),
                'url' => $this->buildUrl($version->slug),
            ];
        })->values()->toArray();
    }

    /**
     * ✅ NOVO: Retorna especificações técnicas do veículo para este ano
     * Usa GuideVehicleIntegrationService::getVehicleSpecs()
     * 
     * @return array|null
     */
    public function getSpecs(): ?array
    {
        $makeSlug = $this->make->slug ?? 'toyota';
        $modelSlug = $this->model->slug ?? 'corolla';
        $year = (int) $this->year;

        // Buscar especificações REAIS do MySQL
        $vehicleSpecs = $this->vehicleIntegration->getVehicleSpecs(
            $makeSlug, 
            $modelSlug, 
            $year
        );

        if (!$vehicleSpecs) {
            return null;
        }

        // Retornar specs formatadas
        return [
            'engine' => [
                'code' => $vehicleSpecs->engine_code ?? 'N/A',
                'displacement' => $vehicleSpecs->engineSpecs->displacement ?? null,
                'cylinders' => $vehicleSpecs->engineSpecs->cylinders ?? null,
                'valves' => $vehicleSpecs->engineSpecs->valves ?? null,
                'aspiration' => $vehicleSpecs->engineSpecs->aspiration ?? null,
            ],
            'performance' => [
                'power_hp' => $vehicleSpecs->specs->power_hp ?? null,
                'power_rpm' => $vehicleSpecs->specs->power_rpm ?? null,
                'torque_nm' => $vehicleSpecs->specs->torque_nm ?? null,
                'torque_rpm' => $vehicleSpecs->specs->torque_rpm ?? null,
            ],
            'fluids' => [
                'oil_type' => $vehicleSpecs->fluidSpecs->engine_oil_type ?? null,
                'oil_capacity' => $vehicleSpecs->fluidSpecs->engine_oil_capacity ?? null,
                'coolant_capacity' => $vehicleSpecs->fluidSpecs->coolant_capacity ?? null,
                'brake_fluid_type' => $vehicleSpecs->fluidSpecs->brake_fluid_type ?? null,
            ],
            'tires' => [
                'front_size' => $vehicleSpecs->tireSpecs->front_tire_size ?? null,
                'rear_size' => $vehicleSpecs->tireSpecs->rear_tire_size ?? null,
                'pressure_front' => $vehicleSpecs->tireSpecs->recommended_pressure_front ?? null,
                'pressure_rear' => $vehicleSpecs->tireSpecs->recommended_pressure_rear ?? null,
            ],
            'battery' => [
                'type' => $vehicleSpecs->batterySpecs->type ?? null,
                'capacity' => $vehicleSpecs->batterySpecs->capacity_ah ?? null,
            ],
        ];
    }

    public function getStats(): array
    {
        $versions = $this->getVersions();

        return [
            'total_versions' => count($versions),
        ];
    }

    public function getComplementaryCategories(): array
    {
        $all = [
            ['name' => 'Calibragem', 'slug' => 'calibragem'],
            ['name' => 'Pneus', 'slug' => 'pneus'],
            ['name' => 'Bateria', 'slug' => 'bateria'],
            ['name' => 'Correia', 'slug' => 'correia'],
            ['name' => 'Fluidos', 'slug' => 'fluidos'],
            ['name' => 'Revisão', 'slug' => 'revisao'],
        ];

        $currentSlug = $this->category->slug ?? 'oleo';
        return array_filter($all, fn($cat) => $cat['slug'] !== $currentSlug);
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            'title' => "{$category['name']} {$make['name']} {$model['name']} {$this->year} – Todas as versões",
            'description' => "Guias de {$category['name']} para {$make['name']} {$model['name']} {$this->year}. Escolha a versão do seu veículo.",
            'canonical' => "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}/{$this->year}",
            'og_image' => "/images/og/{$model['slug']}-{$this->year}.jpg",
        ];
    }

    public function getBreadcrumbs(): array
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return [
            ['name' => 'Início', 'url' => route('home')],
            ['name' => 'Guias', 'url' => route('guide.index')],
            ['name' => $category['name'], 'url' => route('guide.category', $category['slug'])],
            ['name' => $make['name'], 'url' => route('guide.category.make', ['category' => $category['slug'], 'make' => $make['slug']])],
            ['name' => $model['name'], 'url' => route('guide.category.make.model', ['category' => $category['slug'], 'make' => $make['slug'], 'model' => $model['slug']])],
            ['name' => $this->year, 'url' => null],
        ];
    }

    private function buildUrl(string $versionSlug): string
    {
        $category = $this->getCategory();
        $make = $this->getMake();
        $model = $this->getModel();

        return "/guias/{$category['slug']}/{$make['slug']}/{$model['slug']}/{$this->year}/{$versionSlug}";
    }

    /**
     * Traduz tipo de combustível para português
     */
    private function translateFuelType(?string $fuelType): string
    {
        $translations = [
            'gasoline' => 'Gasolina',
            'diesel' => 'Diesel',
            'ethanol' => 'Etanol',
            'flex' => 'Flex',
            'electric' => 'Elétrico',
            'hybrid' => 'Híbrido',
            'plugin_hybrid' => 'Híbrido Plug-in',
            'cng' => 'GNV',
        ];

        return $translations[$fuelType] ?? 'N/A';
    }

    /**
     * Traduz tipo de transmissão para português
     */
    private function translateTransmission(?string $transmission): string
    {
        $translations = [
            'manual' => 'Manual',
            'automatic' => 'Automático',
            'cvt' => 'CVT',
            'dct' => 'DCT',
            'amt' => 'AMT',
        ];

        return $translations[$transmission] ?? 'N/A';
    }
}