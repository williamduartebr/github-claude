<?php

namespace Src\AutoInfoCenter\ViewModels\Templates\Traits;

use Illuminate\Support\Str;

trait VehicleDataProcessingTrait
{
    /**
     * Processa informações básicas do veículo usando dados embarcados
     */
    private function processVehicleInfo(): array
    {
        $vehicleData = $this->article->vehicle_data ?? [];
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'full_name' => $this->getVehicleFullName($vehicleData, $vehicleInfo),
            'make' => $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '',
            'model' => $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleData['year'] ?? $vehicleInfo['ano'] ?? '',
            'category' => $this->getVehicleCategory($vehicleData, $vehicleInfo),
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'version' => $vehicleInfo['versao'] ?? '',
            'fuel' => $vehicleInfo['combustivel'] ?? '',
            'tire_size' => $vehicleData['tire_size'] ?? '',
            'image_url' => $this->getVehicleImageUrl($vehicleData, $vehicleInfo),
            'slug' => $this->generateSlug($vehicleData, $vehicleInfo),
            'is_premium' => $vehicleData['is_premium'] ?? $this->isPremiumVehicle(),
            'is_motorcycle' => $vehicleData['is_motorcycle'] ?? false,
            'is_electric' => $vehicleData['is_electric'] ?? false,
            'is_hybrid' => $vehicleData['is_hybrid'] ?? false,
            'has_tpms' => $vehicleData['has_tpms'] ?? false,
            'segment' => $this->getVehicleSegment($vehicleData, $vehicleInfo),
            'vehicle_type' => $vehicleData['vehicle_type'] ?? 'car',
            'main_category' => $vehicleData['main_category'] ?? '',
            'category_normalized' => $vehicleData['vehicle_features']['category_normalized'] ?? $this->getVehicleSegment($vehicleData, $vehicleInfo),
            'recommended_oil' => $vehicleData['vehicle_features']['recommended_oil'] ?? '',
            'data_quality_score' => $vehicleData['data_quality_score'] ?? 5
        ];
    }

    /**
     * Processa especificações de pressão usando dados embarcados
     */
    private function processPressureSpecifications(): array
    {
        $vehicleData = $this->article->vehicle_data ?? [];
        $pressureSpecs = $vehicleData['pressure_specifications'] ?? [];

        if (empty($pressureSpecs)) {
            return [];
        }

        return [
            'pressure_empty_front' => $pressureSpecs['pressure_empty_front'] ?? null,
            'pressure_empty_rear' => $pressureSpecs['pressure_empty_rear'] ?? null,
            'pressure_light_front' => $pressureSpecs['pressure_light_front'] ?? null,
            'pressure_light_rear' => $pressureSpecs['pressure_light_rear'] ?? null,
            'pressure_max_front' => $pressureSpecs['pressure_max_front'] ?? null,
            'pressure_max_rear' => $pressureSpecs['pressure_max_rear'] ?? null,
            'pressure_spare' => $pressureSpecs['pressure_spare'] ?? null,
            'pressure_display' => $pressureSpecs['pressure_display'] ?? '',
            'empty_pressure_display' => $pressureSpecs['empty_pressure_display'] ?? '',
            'loaded_pressure_display' => $pressureSpecs['loaded_pressure_display'] ?? '',
            'has_spare_tire' => $this->hasSparetire($pressureSpecs),
            'is_electric_no_spare' => $this->isElectricNoSpare($vehicleData, $pressureSpecs),
            'pressure_range' => $this->calculatePressureRange($pressureSpecs)
        ];
    }

    /**
     * Processa especificações de pneus usando dados embarcados
     */
    private function processTireSpecifications(): array
    {
        $vehicleData = $this->article->vehicle_data ?? [];
        $tireSpecs = $vehicleData['tire_specifications'] ?? [];

        return [
            'tire_size' => $vehicleData['tire_size'] ?? $tireSpecs['tire_size'] ?? '',
            'recommended_brands' => $tireSpecs['recommended_brands'] ?? [],
            'seasonal_recommendations' => $tireSpecs['seasonal_recommendations'] ?? [],
            'is_motorcycle_size' => $this->isMotorcycleTireSize($vehicleData['tire_size'] ?? ''),
            'front_tire_size' => $this->extractFrontTireSize($vehicleData['tire_size'] ?? ''),
            'rear_tire_size' => $this->extractRearTireSize($vehicleData['tire_size'] ?? '')
        ];
    }

    /**
     * Obtém nome completo do veículo
     */
    private function getVehicleFullName(array $vehicleData, array $vehicleInfo): string
    {
        if (!empty($vehicleData['vehicle_features']['vehicle_full_name'])) {
            return $vehicleData['vehicle_features']['vehicle_full_name'];
        }

        $make = $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '';
        $model = $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '';
        $year = $vehicleData['year'] ?? $vehicleInfo['ano'] ?? '';

        if (empty($make) || empty($model)) {
            return '';
        }

        return trim("{$make} {$model} {$year}");
    }

    /**
     * Obtém categoria do veículo
     */
    private function getVehicleCategory(array $vehicleData, array $vehicleInfo): string
    {
        return $vehicleData['main_category'] ?? $vehicleInfo['categoria'] ?? '';
    }

    /**
     * Obtém URL da imagem do veículo usando dados embarcados
     */
    private function getVehicleImageUrl(array $vehicleData, array $vehicleInfo): string
    {
        $make = $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '';
        $model = $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '';
        $year = $vehicleData['year'] ?? $vehicleInfo['ano'] ?? '';

        $makeSlug = strtolower($make);
        $modelSlug = strtolower(str_replace(' ', '-', $model));

        $vehicleType = $this->getVehicleTypeForImage($vehicleData);
        $basePath = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/{$vehicleType}";

        return "{$basePath}/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Gera slug baseado nos dados embarcados
     */
    private function generateSlug(array $vehicleData, array $vehicleInfo): string
    {
        if (!empty($vehicleData['vehicle_features']['url_slug'])) {
            return $vehicleData['vehicle_features']['url_slug'];
        }

        $make = strtolower($vehicleData['make'] ?? $vehicleInfo['marca'] ?? '');
        $model = strtolower(str_replace(' ', '-', $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? ''));

        return "{$make}-{$model}";
    }

    /**
     * Determina tipo de veículo para imagem baseado em dados embarcados
     */
    private function getVehicleTypeForImage(array $vehicleData): string
    {
        if ($vehicleData['is_motorcycle'] ?? false) {
            return 'motorcycles';
        }

        $vehicleType = $vehicleData['vehicle_type'] ?? 'car';
        
        return match($vehicleType) {
            'motorcycle' => 'motorcycles',
            'suv' => 'vehicles',
            'car' => 'vehicles',
            default => 'vehicles'
        };
    }

    /**
     * Obtém segmento do veículo usando dados embarcados
     */
    private function getVehicleSegment(array $vehicleData, array $vehicleInfo): string
    {
        // Usa dados embarcados primeiro
        if (!empty($vehicleData['vehicle_features']['category_normalized'])) {
            return $vehicleData['vehicle_features']['category_normalized'];
        }

        if (!empty($vehicleData['vehicle_segment'])) {
            return $this->mapVehicleSegment($vehicleData['vehicle_segment']);
        }

        // Fallback para lógica anterior
        $category = strtolower($vehicleInfo['categoria'] ?? '');
        
        if ($vehicleData['is_motorcycle'] ?? false) {
            return $this->getMotorcycleSegment($vehicleData['main_category'] ?? '');
        }

        $segmentMap = [
            'hatches' => 'Hatchback Compacto',
            'sedan' => 'Sedan Médio',
            'suv' => 'SUV',
            'pickup' => 'Picape',
            'coupe' => 'Cupê'
        ];

        return $segmentMap[$category] ?? 'Automóvel';
    }

    /**
     * Mapeia segmento do veículo
     */
    private function mapVehicleSegment(string $segment): string
    {
        return match($segment) {
            'A' => 'Compacto',
            'B' => 'Subcompacto',
            'C' => 'Médio',
            'D' => 'Grande',
            'E' => 'Executivo',
            'F' => 'Luxo',
            'MOTO' => 'Motocicleta',
            default => $segment
        };
    }

    /**
     * Obtém segmento de motocicleta
     */
    private function getMotorcycleSegment(string $mainCategory): string
    {
        return match($mainCategory) {
            'motorcycle_sport' => 'Motocicleta Esportiva',
            'motorcycle_street' => 'Motocicleta Street',
            'motorcycle_adventure' => 'Motocicleta Adventure',
            'motorcycle_touring' => 'Motocicleta Touring',
            'motorcycle_cruiser' => 'Motocicleta Cruiser',
            'motorcycle_naked' => 'Motocicleta Naked',
            default => 'Motocicleta'
        };
    }

    /**
     * Verifica se tem pneu sobressalente
     */
    private function hasSpareType(array $pressureSpecs): bool
    {
        $spare = $pressureSpecs['pressure_spare'] ?? null;
        return $spare !== null && $spare > 0;
    }

    /**
     * Verifica se é elétrico sem estepe
     */
    private function isElectricNoSpare(array $vehicleData, array $pressureSpecs): bool
    {
        $isElectric = $vehicleData['is_electric'] ?? false;
        $spare = $pressureSpecs['pressure_spare'] ?? null;
        
        return $isElectric && ($spare === null || $spare === 0);
    }

    /**
     * Calcula faixa de pressão
     */
    private function calculatePressureRange(array $pressureSpecs): string
    {
        $pressures = array_filter([
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ]);

        if (empty($pressures)) {
            return '';
        }

        $min = min($pressures);
        $max = max($pressures);

        return $min === $max ? "{$min} PSI" : "{$min}-{$max} PSI";
    }

    /**
     * Verifica se é medida de pneu de moto
     */
    private function isMotorcycleTireSize(string $tireSize): bool
    {
        return str_contains(strtoupper($tireSize), 'DIANTEIRO') || 
               str_contains(strtoupper($tireSize), 'TRASEIRO') ||
               str_contains($tireSize, '/') && strlen($tireSize) < 20;
    }

    /**
     * Extrai medida do pneu dianteiro (para motos)
     */
    private function extractFrontTireSize(string $tireSize): string
    {
        if (str_contains(strtoupper($tireSize), 'DIANTEIRO')) {
            preg_match('/(.+?)\s*\(DIANTEIRO\)/', $tireSize, $matches);
            return trim($matches[1] ?? '');
        }

        // Para motos com formato "120/70ZR17 / 190/50ZR17"
        if (str_contains($tireSize, '/') && str_contains($tireSize, ' ')) {
            $parts = explode(' ', $tireSize);
            return trim($parts[0] ?? '');
        }

        return $tireSize;
    }

    /**
     * Extrai medida do pneu traseiro (para motos)
     */
    private function extractRearTireSize(string $tireSize): string
    {
        if (str_contains(strtoupper($tireSize), 'TRASEIRO')) {
            preg_match('/(.+?)\s*\(TRASEIRO\)/', $tireSize, $matches);
            return trim($matches[1] ?? '');
        }

        // Para motos com formato "120/70ZR17 / 190/50ZR17"
        if (str_contains($tireSize, '/') && str_contains($tireSize, ' ')) {
            $parts = explode(' ', $tireSize);
            return trim($parts[2] ?? $parts[1] ?? '');
        }

        return $tireSize;
    }

    /**
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? route('info.article.show', $this->article->slug);
    }

    /**
     * Processa breadcrumbs para navegação
     */
    private function getBreadcrumbs(): array
    {
        return [
            [
                'name' => 'Início',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->article->category_name ?? 'Informações'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'informacoes'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => route('info.article.show', $this->article->slug),
                'position' => 4
            ],
        ];
    }

    /**
     * Verifica se é veículo premium usando dados embarcados
     */
    private function isPremiumVehicleFromData(): bool
    {
        $vehicleData = $this->article->vehicle_data ?? [];
        
        if (isset($vehicleData['is_premium'])) {
            return $vehicleData['is_premium'];
        }

        // Fallback para lógica anterior
        return $this->isPremiumVehicle();
    }

    // Métodos abstratos que devem ser implementados nas ViewModels específicas
    abstract protected function getVehicleTypeForImage(): string;
    abstract protected function isPremiumVehicle(): bool;
    abstract protected function getVehicleSegment(): string;
}