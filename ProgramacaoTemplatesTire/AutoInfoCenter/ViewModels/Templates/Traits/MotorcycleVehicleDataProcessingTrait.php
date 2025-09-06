<?php

namespace Src\AutoInfoCenter\ViewModels\Templates\Traits;

use Illuminate\Support\Str;

/**
 * MotorcycleVehicleDataProcessingTrait
 * 
 * Trait específico para processamento de dados de motocicletas
 * Corrige problemas de detecção e categorização de motos
 * 
 * @version 1.0 - Correção para Motocicletas
 */
trait MotorcycleVehicleDataProcessingTrait
{
    /**
     * Processa informações básicas do veículo priorizando dados de motocicleta
     */
    private function processMotorcycleVehicleInfo(): array
    {
        // Usar dados diretos do TireCalibration primeiro
        $originalData = $this->article ?? null;
        $vehicleData = $originalData->vehicle_data ?? [];
        $vehicleInfo = $originalData->extracted_entities ?? [];

        return [
            'full_name' => $this->getMotorcycleFullName($originalData, $vehicleData, $vehicleInfo),
            'make' => $originalData->vehicle_make ?? $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '',
            'model' => $originalData->vehicle_model ?? $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '',
            'category' => $this->getMotorcycleCategory($originalData, $vehicleData, $vehicleInfo),
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'version' => $vehicleInfo['versao'] ?? '',
            'fuel' => $vehicleInfo['combustivel'] ?? '',
            'tire_size' => $this->getMotorcycleTireSize($originalData, $vehicleData),
            'image_url' => $this->getMotorcycleImageUrl($originalData, $vehicleData, $vehicleInfo),
            'slug' => $this->generateMotorcycleSlug($originalData, $vehicleData, $vehicleInfo),
            'is_premium' => $this->determineIfPremiumMotorcycle($originalData, $vehicleData, $vehicleInfo),
            'is_motorcycle' => true, // FORÇAR verdadeiro para template de moto
            'is_electric' => $this->detectElectricMotorcycle($originalData, $vehicleData, $vehicleInfo),
            'is_hybrid' => false, // Motos híbridas são raras
            'has_tpms' => $this->detectMotorcycleTPMS($originalData, $vehicleData),
            'segment' => $this->getMotorcycleSegment($originalData, $vehicleData, $vehicleInfo),
            'vehicle_type' => 'motorcycle', // FORÇAR tipo motocicleta
            'main_category' => $originalData->main_category ?? $vehicleData['main_category'] ?? '',
            'category_normalized' => $this->getMotorcycleCategoryNormalized($originalData),
            'recommended_oil' => $this->getMotorcycleRecommendedOil($vehicleData),
            'data_quality_score' => $vehicleData['data_quality_score'] ?? 8 // Motos geralmente têm dados mais específicos
        ];
    }

    /**
     * Processa especificações de pressão específicas para motocicletas
     */
    private function processMotorcyclePressureSpecifications(): array
    {
        $originalData = $this->article ?? null;
        $vehicleData = $originalData->vehicle_info ?? [];
        $pressureSpecs = $vehicleData['pressure_specifications'] ?? [];

        // Usar dados diretos do TireCalibration
        $directSpecs = $originalData->pressure_specifications ?? [];

        // Combinar dados, priorizando dados diretos
        $frontPressure = $directSpecs['empty_front'] ?? $pressureSpecs['pressure_empty_front'] ?? null;
        $rearPressure = $directSpecs['empty_rear'] ?? $pressureSpecs['pressure_empty_rear'] ?? null;
        $lightFront = $directSpecs['light_front'] ?? $pressureSpecs['pressure_light_front'] ?? $frontPressure;
        $lightRear = $directSpecs['light_rear'] ?? $pressureSpecs['pressure_light_rear'] ?? $rearPressure;

        if ($frontPressure === null && $rearPressure === null) {
            return [];
        }

        return [
            'pressure_empty_front' => $frontPressure,
            'pressure_empty_rear' => $rearPressure,
            'pressure_light_front' => $lightFront,
            'pressure_light_rear' => $lightRear,
            'pressure_max_front' => $pressureSpecs['pressure_max_front'] ?? ($frontPressure ? $frontPressure + 3 : null),
            'pressure_max_rear' => $pressureSpecs['pressure_max_rear'] ?? ($rearPressure ? $rearPressure + 3 : null),
            'pressure_spare' => null, // Motos raramente têm estepe
            'pressure_display' => $this->formatMotorcyclePressureDisplay($frontPressure, $rearPressure),
            'empty_pressure_display' => $this->formatMotorcyclePressureShort($frontPressure, $rearPressure),
            'loaded_pressure_display' => $this->formatMotorcyclePressureShort($lightFront, $lightRear),
            'has_spare_tire' => false, // Motos não têm estepe
            'is_electric_no_spare' => $this->detectElectricMotorcycle($originalData, $vehicleData, []),
            'pressure_range' => $this->calculateMotorcyclePressureRange($frontPressure, $rearPressure, $lightFront, $lightRear)
        ];
    }

    /**
     * Processa especificações de pneus específicas para motocicletas
     */
    private function processMotorcycleTireSpecifications(): array
    {
        $originalData = $this->article ?? null;
        $vehicleData = $originalData->vehicle_data ?? [];
        $tireSpecs = $vehicleData['tire_specifications'] ?? [];

        $tireSize = $this->getMotorcycleTireSize($originalData, $vehicleData);

        return [
            'tire_size' => $tireSize,
            'recommended_brands' => $this->getMotorcycleTireBrands($tireSpecs, $originalData),
            'seasonal_recommendations' => $this->getMotorcycleSeasonalTires($tireSpecs, $originalData),
            'is_motorcycle_size' => true,
            'front_tire_size' => $this->extractMotorcycleFrontTire($tireSize),
            'rear_tire_size' => $this->extractMotorcycleRearTire($tireSize),
            'tire_type' => $this->determineMotorcycleTireType($originalData),
            'compound_type' => $this->determineMotorcycleTireCompound($originalData)
        ];
    }

    /**
     * Obtém nome completo da motocicleta
     */
    private function getMotorcycleFullName($originalData, array $vehicleData, array $vehicleInfo): string
    {
        // Priorizar dados diretos
        if ($originalData && $originalData->vehicle_make && $originalData->vehicle_model) {
            return trim("{$originalData->vehicle_make} {$originalData->vehicle_model}");
        }

        if (!empty($vehicleData['vehicle_features']['vehicle_full_name'])) {
            return $vehicleData['vehicle_features']['vehicle_full_name'];
        }

        $make = $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '';
        $model = $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '';

        if (empty($make) || empty($model)) {
            return '';
        }

        return trim("{$make} {$model}");
    }

    /**
     * Obtém categoria específica da motocicleta
     */
    private function getMotorcycleCategory($originalData, array $vehicleData, array $vehicleInfo): string
    {
        // Usar main_category diretamente se for de moto
        $mainCategory = $originalData->main_category ?? $vehicleData['main_category'] ?? '';

        if (str_contains($mainCategory, 'motorcycle')) {
            return $mainCategory;
        }

        // Fallback para extracted_entities
        return $vehicleInfo['categoria'] ?? 'motorcycle';
    }

    /**
     * Obtém tamanho dos pneus da motocicleta
     */
    private function getMotorcycleTireSize($originalData, array $vehicleData): string
    {
        // Priorizar dados diretos
        if ($originalData && !empty($originalData->pressure_specifications['tire_size'])) {
            return $originalData->pressure_specifications['tire_size'];
        }

        return $vehicleData['tire_size'] ??
            $vehicleData['tire_specifications']['tire_size'] ?? '';
    }

    /**
     * Obtém URL da imagem específica para motocicleta
     */
    private function getMotorcycleImageUrl($originalData, array $vehicleData, array $vehicleInfo): string
    {
        $make = $originalData->vehicle_make ?? $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '';
        $model = $originalData->vehicle_model ?? $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? '';

        $makeSlug = strtolower($make);
        $modelSlug = strtolower(str_replace(' ', '-', $model));

        // SEMPRE usar pasta de motocicletas
        $basePath = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/motorcycles";

        return "{$basePath}/{$makeSlug}-{$modelSlug}.jpg";
    }

    /**
     * Gera slug específico para motocicleta
     */
    private function generateMotorcycleSlug($originalData, array $vehicleData, array $vehicleInfo): string
    {
        if (!empty($vehicleData['vehicle_features']['url_slug'])) {
            return $vehicleData['vehicle_features']['url_slug'];
        }

        $make = strtolower($originalData->vehicle_make ?? $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '');
        $model = strtolower(str_replace(' ', '-', $originalData->vehicle_model ?? $vehicleData['model'] ?? $vehicleInfo['modelo'] ?? ''));

        return "{$make}-{$model}";
    }

    /**
     * Determina se é motocicleta premium
     */
    private function determineIfPremiumMotorcycle($originalData, array $vehicleData, array $vehicleInfo): bool
    {
        // Verificar dados diretos primeiro
        if (isset($originalData->vehicle_features['is_premium'])) {
            return (bool) $originalData->vehicle_features['is_premium'];
        }

        if (isset($vehicleData['is_premium'])) {
            return (bool) $vehicleData['is_premium'];
        }

        // Lógica para marcas premium de motos
        $make = strtolower($originalData->vehicle_make ?? $vehicleData['make'] ?? $vehicleInfo['marca'] ?? '');
        $premiumMotorcycleBrands = [
            'bmw',
            'ducati',
            'triumph',
            'harley-davidson',
            'harley davidson',
            'ktm',
            'aprilia',
            'mv agusta',
            'moto guzzi',
            'indian',
            'zero'
        ];

        if (in_array($make, $premiumMotorcycleBrands)) {
            return true;
        }

        // Verificar por categoria premium/sport
        $category = $originalData->main_category ?? '';
        if (str_contains($category, 'sport') || str_contains($category, 'premium')) {
            return true;
        }

        return false;
    }

    /**
     * Detecta se é motocicleta elétrica
     */
    private function detectElectricMotorcycle($originalData, array $vehicleData, array $vehicleInfo): bool
    {
        // Verificar dados diretos
        if (isset($originalData->vehicle_features['is_electric'])) {
            return (bool) $originalData->vehicle_features['is_electric'];
        }

        if (isset($vehicleData['is_electric'])) {
            return (bool) $vehicleData['is_electric'];
        }

        // Detectar por categoria
        $category = $originalData->main_category ?? '';
        if (str_contains($category, 'electric')) {
            return true;
        }

        // Detectar por marca
        $make = strtolower($originalData->vehicle_make ?? $vehicleInfo['marca'] ?? '');
        $electricBrands = ['zero', 'energica', 'lightning', 'sur-ron'];

        if (in_array($make, $electricBrands)) {
            return true;
        }

        // Detectar por combustível
        $fuel = strtolower($vehicleInfo['combustivel'] ?? '');
        return in_array($fuel, ['elétrico', 'electric', 'bateria']);
    }

    /**
     * Detecta se motocicleta tem TPMS
     */
    private function detectMotorcycleTPMS($originalData, array $vehicleData): bool
    {
        // Verificar dados diretos
        if (isset($originalData->vehicle_features['has_tpms'])) {
            return (bool) $originalData->vehicle_features['has_tpms'];
        }

        if (isset($vehicleData['has_tpms'])) {
            return (bool) $vehicleData['has_tpms'];
        }

        // Motos premium/elétricas podem ter TPMS
        $isPremium = $this->determineIfPremiumMotorcycle($originalData, $vehicleData, []);
        $isElectric = $this->detectElectricMotorcycle($originalData, $vehicleData, []);

        return $isPremium || $isElectric;
    }

    /**
     * Obtém segmento específico da motocicleta
     */
    private function getMotorcycleSegment($originalData, array $vehicleData, array $vehicleInfo): string
    {
        $mainCategory = $originalData->main_category ?? '';

        return match ($mainCategory) {
            'motorcycle_street' => 'Motocicletas Street',
            'motorcycle_sport' => 'Motocicletas Esportivas',
            'motorcycle_adventure' => 'Motocicletas Adventure',
            'motorcycle_trail' => 'Motocicletas Trail',
            'motorcycle_scooter' => 'Scooters',
            'motorcycle_cruiser' => 'Motocicletas Cruiser',
            'motorcycle_touring' => 'Motocicletas Touring',
            'motorcycle_electric' => 'Motocicletas Elétricas',
            'motorcycle_custom' => 'Motocicletas Customizadas',
            default => 'Motocicletas'
        };
    }

    /**
     * Obtém categoria normalizada da motocicleta
     */
    private function getMotorcycleCategoryNormalized($originalData): string
    {
        $mainCategory = $originalData->main_category ?? '';

        return match ($mainCategory) {
            'motorcycle_street' => 'Motocicletas Street',
            'motorcycle_sport' => 'Motocicletas Esportivas',
            'motorcycle_adventure' => 'Motocicletas Adventure',
            'motorcycle_trail' => 'Motocicletas Trail',
            'motorcycle_scooter' => 'Scooters',
            'motorcycle_cruiser' => 'Motocicletas Cruiser',
            'motorcycle_touring' => 'Motocicletas Touring',
            'motorcycle_electric' => 'Motocicletas Elétricas',
            'motorcycle_custom' => 'Motocicletas Customizadas',
            default => 'Motocicletas'
        };
    }

    /**
     * Obtém óleo recomendado para motocicleta
     */
    private function getMotorcycleRecommendedOil(array $vehicleData): string
    {
        $recommendedOil = $vehicleData['vehicle_features']['recommended_oil'] ?? '';

        if (!empty($recommendedOil)) {
            return $recommendedOil;
        }

        // Fallback para motocicletas
        return '10W40 Sintético para Motocicletas';
    }

    /**
     * Formata exibição de pressão para motocicletas
     */
    private function formatMotorcyclePressureDisplay(?int $front, ?int $rear): string
    {
        if ($front === null || $rear === null) {
            return 'Consulte manual do proprietário';
        }

        return "Dianteiro: {$front} PSI / Traseiro: {$rear} PSI";
    }

    /**
     * Formata exibição curta de pressão
     */
    private function formatMotorcyclePressureShort(?int $front, ?int $rear): string
    {
        if ($front === null || $rear === null) {
            return 'N/A';
        }

        return "{$front}/{$rear} PSI";
    }

    /**
     * Calcula faixa de pressão para motocicletas
     */
    private function calculateMotorcyclePressureRange(?int $front, ?int $rear, ?int $lightFront, ?int $lightRear): string
    {
        $pressures = array_filter([$front, $rear, $lightFront, $lightRear]);

        if (empty($pressures)) {
            return '';
        }

        $min = min($pressures);
        $max = max($pressures);

        return $min === $max ? "{$min} PSI" : "{$min}-{$max} PSI";
    }

    /**
     * Obtém marcas de pneus específicas para motocicletas
     */
    private function getMotorcycleTireBrands(array $tireSpecs, $originalData): array
    {
        $brands = $tireSpecs['recommended_brands'] ?? [];

        if (!empty($brands)) {
            return $brands;
        }

        // Marcas específicas para motos
        return ['Michelin', 'Pirelli', 'Bridgestone', 'Continental', 'Dunlop', 'Metzeler'];
    }

    /**
     * Obtém recomendações sazonais específicas para motos
     */
    private function getMotorcycleSeasonalTires(array $tireSpecs, $originalData): array
    {
        $seasonal = $tireSpecs['seasonal_recommendations'] ?? [];

        if (!empty($seasonal)) {
            return $seasonal;
        }

        // Verificar se é esportiva
        $mainCategory = $originalData->main_category ?? '';

        if (str_contains($mainCategory, 'sport')) {
            return ['Pirelli Diablo Rosso IV', 'Michelin Power 5'];
        }

        if (str_contains($mainCategory, 'adventure')) {
            return ['Continental TKC 70', 'Michelin Anakee Adventure'];
        }

        if (str_contains($mainCategory, 'street')) {
            return ['Michelin Pilot Street', 'Pirelli MT 75'];
        }

        return ['Michelin City Pro', 'Pirelli MT 75'];
    }

    /**
     * Extrai tamanho do pneu dianteiro da motocicleta
     */
    private function extractMotorcycleFrontTire(string $tireSize): string
    {
        if (str_contains(strtoupper($tireSize), 'DIANTEIRO')) {
            // Formato: "120/70-17 (DIANTEIRO) 180/55-17 (TRASEIRO)"
            preg_match('/^([^(]+)\s*\(DIANTEIRO\)/', $tireSize, $matches);
            return trim($matches[1] ?? $tireSize);
        }

        // Se não tem indicação, assumir que é o primeiro tamanho
        $parts = explode(' ', $tireSize);
        return trim($parts[0] ?? $tireSize);
    }

    /**
     * Extrai tamanho do pneu traseiro da motocicleta
     */
    private function extractMotorcycleRearTire(string $tireSize): string
    {
        if (str_contains(strtoupper($tireSize), 'TRASEIRO')) {
            // Formato: "120/70-17 (DIANTEIRO) 180/55-17 (TRASEIRO)"
            preg_match('/\(TRASEIRO\)\s*([^)]+)/', $tireSize, $matches);
            if (!empty($matches[1])) {
                return trim($matches[1]);
            }

            // Tentar extrair após "TRASEIRO"
            preg_match('/.*TRASEIRO.*?(\d+\/\d+[^)]*)/i', $tireSize, $matches);
            return trim($matches[1] ?? $tireSize);
        }

        // Se tem dois tamanhos separados por espaço, pegar o segundo
        if (preg_match('/(\d+\/\d+[^\s]*)\s+(\d+\/\d+[^\s]*)/', $tireSize, $matches)) {
            return trim($matches[2]);
        }

        return $tireSize;
    }

    /**
     * Determina tipo de pneu da motocicleta
     */
    private function determineMotorcycleTireType($originalData): string
    {
        $mainCategory = $originalData->main_category ?? '';

        return match (true) {
            str_contains($mainCategory, 'sport') => 'Esportivo',
            str_contains($mainCategory, 'adventure') => 'Adventure/Trail',
            str_contains($mainCategory, 'trail') => 'Trail/Off-road',
            str_contains($mainCategory, 'street') => 'Street/Urbano',
            str_contains($mainCategory, 'scooter') => 'Scooter',
            str_contains($mainCategory, 'cruiser') => 'Cruiser',
            default => 'Street/Urbano'
        };
    }

    /**
     * Determina composto do pneu da motocicleta
     */
    private function determineMotorcycleTireCompound($originalData): string
    {
        $mainCategory = $originalData->main_category ?? '';

        return match (true) {
            str_contains($mainCategory, 'sport') => 'Composto macio (grip)',
            str_contains($mainCategory, 'adventure') => 'Composto dual (on/off)',
            str_contains($mainCategory, 'trail') => 'Composto off-road',
            default => 'Composto padrão'
        };
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
    private function getMotorcycleBreadcrumbs(): array
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
}
