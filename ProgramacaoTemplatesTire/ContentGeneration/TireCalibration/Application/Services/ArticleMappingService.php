<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Carbon\Carbon;

/**
 * ArticleMappingService - MAPEAMENTO CORRIGIDO V3.0
 * 
 * CORREÇÃO CRÍTICA:
 * - TEMPLATE_MAPPING completo com TODAS as categorias motorcycle_*
 * - Prevenção do fallback incorreto para tire_calibration_car
 * - Validação rigorosa de mapeamento de templates
 * 
 * @author Claude Sonnet 4
 * @version 3.0 - Correção crítica do mapeamento de templates
 */
class ArticleMappingService
{
    /**
     * ✅ MAPEAMENTO COMPLETO - TODAS AS CATEGORIAS
     * 
     * CORREÇÃO: Adicionadas TODAS as categorias motorcycle_* identificadas no relatório
     */
    private const TEMPLATE_MAPPING = [
        // ===== MOTOCICLETAS - TODAS AS CATEGORIAS =====
        'motorcycle' => 'tire_calibration_motorcycle',
        'motorcycle_street' => 'tire_calibration_motorcycle',
        'motorcycle_sport' => 'tire_calibration_motorcycle',
        'motorcycle_naked' => 'tire_calibration_motorcycle',
        'motorcycle_scooter' => 'tire_calibration_motorcycle',
        'motorcycle_trail' => 'tire_calibration_motorcycle',           // ✅ ADICIONADO
        'motorcycle_adventure' => 'tire_calibration_motorcycle',       // ✅ ADICIONADO
        'motorcycle_electric' => 'tire_calibration_motorcycle',        // ✅ ADICIONADO
        'motorcycle_cruiser' => 'tire_calibration_motorcycle',         // ✅ ADICIONADO
        'motorcycle_custom' => 'tire_calibration_motorcycle',          // ✅ ADICIONADO
        'motorcycle_touring' => 'tire_calibration_motorcycle',         // ✅ ADICIONADO
        
        // ===== CARROS - TODAS AS CATEGORIAS =====
        'sedan' => 'tire_calibration_car',
        'hatch' => 'tire_calibration_car',
        'suv' => 'tire_calibration_car',
        'car_electric' => 'tire_calibration_car',
        'car_hybrid' => 'tire_calibration_car',
        'car_hatchback' => 'tire_calibration_car',                    // ✅ ADICIONADO
        'car_sedan' => 'tire_calibration_car',                        // ✅ ADICIONADO
        'car_sports' => 'tire_calibration_car',                       // ✅ ADICIONADO
        'van' => 'tire_calibration_car',                              // ✅ ADICIONADO
        'minivan' => 'tire_calibration_car',                          // ✅ ADICIONADO
        'suv_hybrid' => 'tire_calibration_car',                       // ✅ ADICIONADO
        'suv_electric' => 'tire_calibration_car',                     // ✅ ADICIONADO
        'sedan_electric' => 'tire_calibration_car',                   // ✅ ADICIONADO
        'hatch_electric' => 'tire_calibration_car',                   // ✅ ADICIONADO
        
        // ===== PICAPES E CAMINHÕES =====
        'pickup' => 'tire_calibration_pickup',
        'truck' => 'tire_calibration_pickup',
    ];

    /**
     * Mapear dados JSON do vehicle-data para estrutura completa de artigo
     */
    public function mapVehicleDataToArticle(array $vehicleData, TireCalibration $calibration): array
    {
        try {
            // ✅ VALIDAÇÃO CRÍTICA: Verificar mapeamento antes de processar
            $this->validateTemplateMapping($vehicleData, $calibration);
            
            // Determinar template baseado na categoria
            $template = $this->getTemplate($vehicleData['main_category'] ?? '');
            
            // ✅ LOG CRÍTICO: Rastrear mapeamentos para debug
            Log::info('ArticleMappingService: Template mapeado', [
                'vehicle' => $vehicleData['make'] . ' ' . $vehicleData['model'],
                'category' => $vehicleData['main_category'] ?? 'unknown',
                'vehicle_type' => $vehicleData['vehicle_type'] ?? 'unknown', 
                'template_mapped' => $template,
                'calibration_id' => $calibration->_id ?? 'unknown'
            ]);
            
            // Estrutura base do artigo (igual aos mocks)
            $article = [
                'title' => $this->generateTitle($vehicleData),
                'slug' => $this->generateSlug($vehicleData),
                'template' => $template,  // ✅ TEMPLATE CORRIGIDO
                'category_id' => 1,
                'category_name' => 'Calibragem de Pneus',
                'category_slug' => 'calibragem-pneus',
                'seo_data' => $this->generateSeoData($vehicleData),
                'vehicle_data' => $this->mapVehicleDataSection($vehicleData),
                'extracted_entities' => $this->generateExtractedEntities($vehicleData),
                'content' => $this->generateRichContent($vehicleData, $template),
                'formated_updated_at' => now()->format('d \d\e F \d\e Y'),
                'canonical_url' => $this->generateCanonicalUrl($vehicleData),
            ];

            // ✅ VALIDAÇÃO PÓS-MAPEAMENTO
            $this->validateMappedArticle($article, $vehicleData);

            Log::info('ArticleMappingService: Artigo mapeado com sucesso', [
                'vehicle' => $vehicleData['make'] . ' ' . $vehicleData['model'],
                'template' => $template,
                'title_length' => strlen($article['title']),
                'content_sections' => count($article['content'])
            ]);

            return $article;

        } catch (\Exception $e) {
            Log::error('ArticleMappingService: Erro no mapeamento', [
                'vehicle_data' => ($vehicleData['make'] ?? 'Unknown') . ' ' . ($vehicleData['model'] ?? ''),
                'category' => $vehicleData['main_category'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ VALIDAÇÃO CRÍTICA: Verificar se mapeamento está correto ANTES do processamento
     */
    private function validateTemplateMapping(array $vehicleData, TireCalibration $calibration): void
    {
        $category = $vehicleData['main_category'] ?? '';
        $vehicleType = $vehicleData['vehicle_type'] ?? '';
        
        // VALIDAÇÃO 1: Categoria deve existir no mapeamento
        if (!isset(self::TEMPLATE_MAPPING[$category]) && !empty($category)) {
            Log::warning('ArticleMappingService: Categoria não mapeada', [
                'category' => $category,
                'vehicle_type' => $vehicleType,
                'vehicle' => ($vehicleData['make'] ?? '') . ' ' . ($vehicleData['model'] ?? ''),
                'calibration_id' => $calibration->_id ?? 'unknown'
            ]);
        }

        // VALIDAÇÃO 2: Consistência entre vehicle_type e categoria
        $isMotorcycleCategory = str_starts_with($category, 'motorcycle');
        $isMotorcycleType = $vehicleType === 'motorcycle';
        
        if ($isMotorcycleCategory !== $isMotorcycleType) {
            Log::warning('ArticleMappingService: Inconsistência vehicle_type vs categoria', [
                'category' => $category,
                'vehicle_type' => $vehicleType,
                'is_motorcycle_category' => $isMotorcycleCategory,
                'is_motorcycle_type' => $isMotorcycleType,
                'vehicle' => ($vehicleData['make'] ?? '') . ' ' . ($vehicleData['model'] ?? ''),
                'calibration_id' => $calibration->_id ?? 'unknown'
            ]);
        }
    }

    /**
     * ✅ VALIDAÇÃO PÓS-MAPEAMENTO: Verificar se artigo foi mapeado corretamente
     */
    private function validateMappedArticle(array $article, array $vehicleData): void
    {
        $template = $article['template'];
        $vehicleType = $vehicleData['vehicle_type'] ?? '';
        $category = $vehicleData['main_category'] ?? '';

        // REGRA: Motocicletas NUNCA devem usar template de carro
        if ($vehicleType === 'motorcycle' && $template === 'tire_calibration_car') {
            $error = "ERRO CRÍTICO: Motocicleta mapeada com template de carro";
            Log::error('ArticleMappingService: ' . $error, [
                'vehicle_type' => $vehicleType,
                'category' => $category,
                'template' => $template,
                'vehicle' => ($vehicleData['make'] ?? '') . ' ' . ($vehicleData['model'] ?? '')
            ]);
            throw new \Exception($error);
        }

        // REGRA: Categorias motorcycle_* NUNCA devem usar template de carro
        if (str_starts_with($category, 'motorcycle') && $template === 'tire_calibration_car') {
            $error = "ERRO CRÍTICO: Categoria {$category} mapeada com template de carro";
            Log::error('ArticleMappingService: ' . $error, [
                'vehicle_type' => $vehicleType,
                'category' => $category,
                'template' => $template,
                'vehicle' => ($vehicleData['make'] ?? '') . ' ' . ($vehicleData['model'] ?? '')
            ]);
            throw new \Exception($error);
        }
    }

    /**
     * ✅ OBTER TEMPLATE COM FALLBACK INTELIGENTE
     */
    private function getTemplate(string $category): string
    {
        // 1. Mapeamento direto
        if (isset(self::TEMPLATE_MAPPING[$category])) {
            return self::TEMPLATE_MAPPING[$category];
        }

        // 2. Fallback inteligente baseado em prefixos
        if (str_starts_with($category, 'motorcycle')) {
            Log::info('ArticleMappingService: Fallback motorcycle para categoria não mapeada', [
                'category' => $category
            ]);
            return 'tire_calibration_motorcycle';
        }

        if (str_starts_with($category, 'pickup') || str_starts_with($category, 'truck')) {
            Log::info('ArticleMappingService: Fallback pickup para categoria não mapeada', [
                'category' => $category
            ]);
            return 'tire_calibration_pickup';
        }

        // 3. Fallback padrão (carros)
        Log::info('ArticleMappingService: Fallback car para categoria não mapeada', [
            'category' => $category
        ]);
        return 'tire_calibration_car';
    }

    /**
     * Gerar título do artigo (VERSION V2 - SEM ANO)
     */
    private function generateTitle(array $data): string
    {
        $make = $data['make'] ?? 'Veículo';
        $model = $data['model'] ?? '';
        
        if ($this->isMotorcycle($data)) {
            return "Calibragem do Pneu da {$make} {$model} – Guia Completo";
        }
        
        return "Calibragem do Pneu do {$make} {$model} – Guia Completo";
    }

    /**
     * Gerar slug do artigo (VERSION V2 - SEM ANO)
     */
    private function generateSlug(array $data): string
    {
        $make = Str::slug($data['make'] ?? '');
        $model = Str::slug($data['model'] ?? '');
        
        return "calibragem-pneu-{$make}-{$model}";
    }

    /**
     * Gerar dados SEO completos (VERSION V2 - SEM ANO)
     */
    private function generateSeoData(array $data): array
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $slug = $this->generateSlug($data);
        
        $pressureDisplay = $this->generatePressureDisplay($data);
        
        return [
            'page_title' => $this->generateTitle($data),
            'meta_description' => "Guia completo de calibragem dos pneus do {$make} {$model}. {$pressureDisplay}. Procedimento específico e dicas especializadas.",
            'h1' => $this->generateTitle($data),
            'primary_keyword' => "calibragem pneu {$make} {$model}",
            'secondary_keywords' => [
                "como calibrar pneu {$make} {$model}",
                "pressão pneu {$make}",
                "calibrar pneu {$model}",
                "procedimento calibragem {$make}"
            ],
            'og_title' => $this->generateTitle($data),
            'og_description' => "Procedimento completo de calibragem dos pneus do {$make} {$model}. Pressões específicas e dicas especializadas.",
            'canonical_url' => "https://mercadoveiculos.com.br/info/{$slug}"
        ];
    }

    /**
     * Mapear seção vehicle_data completa
     */
    private function mapVehicleDataSection(array $data): array
    {
        return [
            'make' => $data['make'] ?? '',
            'model' => $data['model'] ?? '',
            'tire_size' => $data['tire_size'] ?? '',
            'main_category' => $data['main_category'] ?? '',
            'vehicle_segment' => $this->getVehicleSegment($data),
            'vehicle_type' => $data['vehicle_type'] ?? 'car',
            'pressure_specifications' => $this->generatePressureSpecifications($data),
            'tire_specifications' => $this->generateTireSpecs($data),
            'vehicle_features' => $this->generateVehicleFeatures($data),
            'is_premium' => $this->isPremium($data),
            'has_tpms' => $data['has_tpms'] ?? false,
            'is_motorcycle' => $this->isMotorcycle($data),
            'is_electric' => $this->isElectric($data),
            'is_hybrid' => $this->isHybrid($data),
            'data_quality_score' => $data['data_quality_score'] ?? 8
        ];
    }

    /**
     * Gerar especificações de pressão
     */
    private function generatePressureSpecifications(array $data): array
    {
        $frontPressure = $data['pressure_empty_front'] ?? 32;
        $rearPressure = $data['pressure_empty_rear'] ?? 30;
        
        return [
            'pressure_empty_front' => $frontPressure,
            'pressure_empty_rear' => $rearPressure,
            'pressure_light_front' => $frontPressure,
            'pressure_light_rear' => $rearPressure,
            'pressure_max_front' => $frontPressure + 3,
            'pressure_max_rear' => $rearPressure + 3,
            'pressure_spare' => $data['pressure_spare'] ?? null,
            'pressure_display' => "Dianteiro: {$frontPressure} PSI / Traseiro: {$rearPressure} PSI",
            'empty_pressure_display' => "{$frontPressure}/{$rearPressure} PSI",
            'loaded_pressure_display' => ($frontPressure + 3) . "/" . ($rearPressure + 3) . " PSI"
        ];
    }

    /**
     * Gerar especificações de pneus
     */
    private function generateTireSpecs(array $data): array
    {
        return [
            'tire_size' => $data['tire_size'] ?? '',
            'recommended_brands' => [
                'Michelin',
                'Pirelli', 
                'Bridgestone',
                'Continental'
            ],
            'seasonal_recommendations' => $this->getSeasonalRecommendations($data)
        ];
    }

    /**
     * Gerar características do veículo
     */
    private function generateVehicleFeatures(array $data): array
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        
        return [
            'vehicle_full_name' => trim("{$make} {$model}"),
            'url_slug' => Str::slug("{$make}-{$model}"),
            'category_normalized' => $this->getCategoryNormalized($data),
            'recommended_oil' => $this->getRecommendedOil($data)
        ];
    }

    /**
     * Gerar entidades extraídas
     */
    private function generateExtractedEntities(array $data): array
    {
        return [
            'marca' => $data['make'] ?? '',
            'modelo' => $data['model'] ?? '',
            'categoria' => $this->getMainCategoryPortuguese($data),
            'motorizacao' => $this->getMotorization($data),
            'combustivel' => $this->getFuelType($data)
        ];
    }

    /**
     * Gerar URL canônica (VERSION V2 - SEM ANO)
     */
    private function generateCanonicalUrl(array $data): string
    {
        $slug = $this->generateSlug($data);
        return "https://mercadoveiculos.com.br/info/{$slug}";
    }

    /**
     * Gerar conteúdo rico baseado no template
     */
    private function generateRichContent(array $data, string $template): array
    {
        $baseContent = [
            'introducao' => $this->generateIntroduction($data),
            'perguntas_frequentes' => $this->generateFAQ($data),
            'consideracoes_finais' => $this->generateFinalConsiderations($data)
        ];

        // Adicionar seções específicas por template
        switch ($template) {
            case 'tire_calibration_motorcycle':
                return array_merge($baseContent, $this->generateMotorcycleContent($data));
            
            case 'tire_calibration_pickup':
                return array_merge($baseContent, $this->generatePickupContent($data));
            
            default: // tire_calibration_car
                return array_merge($baseContent, $this->generateCarContent($data));
        }
    }

    // ===== FUNÇÕES AUXILIARES =====

    private function isMotorcycle(array $data): bool
    {
        $category = $data['main_category'] ?? '';
        $vehicleType = $data['vehicle_type'] ?? '';
        return str_starts_with($category, 'motorcycle') || $vehicleType === 'motorcycle';
    }

    private function isElectric(array $data): bool
    {
        $category = $data['main_category'] ?? '';
        return str_contains($category, '_electric') || $category === 'car_electric';
    }

    private function isHybrid(array $data): bool
    {
        $category = $data['main_category'] ?? '';
        return str_contains($category, '_hybrid') || $category === 'car_hybrid';
    }

    private function isPremium(array $data): bool
    {
        $make = strtolower($data['make'] ?? '');
        $premiumBrands = ['mercedes-benz', 'bmw', 'audi', 'porsche', 'lexus', 'infiniti'];
        return in_array($make, $premiumBrands);
    }

    private function getVehicleSegment(array $data): string
    {
        if ($this->isMotorcycle($data)) return 'MOTO';
        
        $category = $data['main_category'] ?? '';
        return match(true) {
            str_contains($category, 'pickup') => 'PICAPE',
            str_contains($category, 'suv') => 'SUV',
            str_contains($category, 'sedan') => 'SEDAN',
            str_contains($category, 'hatch') => 'HATCH',
            default => 'CARRO'
        };
    }

    private function generatePressureDisplay(array $data): string
    {
        $front = $data['pressure_empty_front'] ?? 32;
        $rear = $data['pressure_empty_rear'] ?? 30;
        return "Dianteiro: {$front} PSI / Traseiro: {$rear} PSI";
    }

    private function getCategoryNormalized(array $data): string
    {
        $category = $data['main_category'] ?? '';
        
        return match($category) {
            'motorcycle', 'motorcycle_street' => 'Motocicleta',
            'motorcycle_sport' => 'Motocicleta Esportiva',
            'motorcycle_trail' => 'Motocicleta Trail',
            'motorcycle_adventure' => 'Motocicleta Adventure',
            'motorcycle_scooter' => 'Scooter',
            'sedan' => 'Sedan',
            'hatch' => 'Hatchback', 
            'suv' => 'SUV',
            'pickup' => 'Picape',
            default => 'Veículo'
        };
    }

    private function getMainCategoryPortuguese(array $data): string
    {
        if ($this->isMotorcycle($data)) return 'motocicleta';
        
        $category = $data['main_category'] ?? '';
        return match(true) {
            str_contains($category, 'pickup') => 'picape',
            str_contains($category, 'suv') => 'suv',
            default => 'automóvel'
        };
    }

    private function getMotorization(array $data): string
    {
        // Placeholder - pode ser implementado baseado nos dados disponíveis
        return $data['engine_displacement'] ?? '1.0L';
    }

    private function getFuelType(array $data): string
    {
        if ($this->isElectric($data)) return 'Elétrico';
        if ($this->isHybrid($data)) return 'Híbrido';
        return 'Gasolina';
    }

    private function getRecommendedOil(array $data): string
    {
        if ($this->isMotorcycle($data)) return '10W40 Sintético';
        if ($this->isPremium($data)) return '5W30 Full Sintético';
        return '10W40 Semissintético';
    }

    private function getSeasonalRecommendations(array $data): array
    {
        if ($this->isMotorcycle($data)) {
            return ['Michelin Pilot Street', 'Pirelli Diablo Rosso III'];
        }
        
        return ['Michelin Primacy 4', 'Continental Premium Contact 6'];
    }

    // ===== GERAÇÃO DE CONTEÚDO ESPECÍFICO =====

    private function generateIntroduction(array $data): string
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        
        if ($this->isMotorcycle($data)) {
            return "A calibragem correta dos pneus da sua {$make} {$model} é crucial para a segurança, desempenho e durabilidade. Esta motocicleta exige atenção especial à pressão dos pneus para aproveitar todo seu potencial com máxima segurança.";
        }
        
        return "A calibragem adequada dos pneus do seu {$make} {$model} é essencial para garantir segurança, economia de combustível e vida útil dos pneus. Este guia apresenta as especificações exatas e procedimentos recomendados.";
    }

    private function generateFAQ(array $data): array
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $pressureDisplay = $this->generatePressureDisplay($data);
        
        $baseFaq = [
            [
                'pergunta' => "Qual a pressão ideal do {$make} {$model} em PSI?",
                'resposta' => "Para o {$make} {$model}, use {$pressureDisplay} para uso normal. Sempre verifique com pneus frios para manter segurança e economia."
            ],
            [
                'pergunta' => "Com que frequência verificar a pressão?",
                'resposta' => $this->isMotorcycle($data) ? 
                    "Semanalmente é obrigatório para motocicletas. Verifique sempre com pneus frios." :
                    "Mensalmente é recomendado, mas semanalmente é ideal. Sempre com pneus frios."
            ]
        ];

        return $baseFaq;
    }

    private function generateFinalConsiderations(array $data): string
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $pressureDisplay = $this->generatePressureDisplay($data);
        
        if ($this->isMotorcycle($data)) {
            return "A {$make} {$model} merece cuidado especial na calibragem dos pneus. Em motos não há margem para erro - sua segurança depende diretamente da pressão correta. Mantenha {$pressureDisplay} e lembre-se: verificação semanal é obrigatória, sempre com pneus frios.";
        }
        
        return "Manter a calibragem correta do {$make} {$model} é um investimento em segurança e economia. Use sempre {$pressureDisplay} para uso normal e ajuste conforme a carga. A verificação regular é a chave para maximizar a vida útil dos pneus e o desempenho do veículo.";
    }

    private function generateMotorcycleContent(array $data): array
    {
        return [
            'especificacoes_por_versao' => $this->generateMotorcycleVersions($data),
            'tabela_carga_completa' => $this->generateMotorcycleLoadTable($data),
            'localizacao_etiqueta' => $this->generateMotorcycleEtiquetaInfo($data),
            'condicoes_especiais' => $this->generateMotorcycleSpecialConditions($data),
            'conversao_unidades' => $this->generateUnitConversion($data),
            'cuidados_recomendacoes' => $this->generateMotorcycleCareTips($data),
            'impacto_pressao' => $this->generatePressureImpact($data)
        ];
    }

    private function generateCarContent(array $data): array
    {
        return [
            'especificacoes_por_versao' => $this->generateCarVersions($data),
            'tabela_carga_completa' => $this->generateCarLoadTable($data),
            'localizacao_etiqueta' => $this->generateCarEtiquetaInfo($data),
            'condicoes_especiais' => $this->generateCarSpecialConditions($data),
            'conversao_unidades' => $this->generateUnitConversion($data),
            'cuidados_recomendacoes' => $this->generateCarCareTips($data),
            'impacto_pressao' => $this->generatePressureImpact($data)
        ];
    }

    private function generatePickupContent(array $data): array
    {
        return [
            'especificacoes_por_versao' => $this->generatePickupVersions($data),
            'tabela_carga_completa' => $this->generatePickupLoadTable($data),
            'localizacao_etiqueta' => $this->generateCarEtiquetaInfo($data),
            'condicoes_especiais' => $this->generatePickupSpecialConditions($data),
            'conversao_unidades' => $this->generateUnitConversion($data),
            'cuidados_recomendacoes' => $this->generatePickupCareTips($data),
            'impacto_pressao' => $this->generatePressureImpact($data)
        ];
    }

    // Implementar métodos específicos de geração de conteúdo...
    // (Os métodos generateMotorcycleVersions, generateCarVersions, etc. 
    //  podem ser implementados conforme necessário)

    private function generateMotorcycleVersions(array $data): array
    {
        return [
            [
                'versao' => 'Versão Base',
                'medida_pneus' => $data['tire_size'] ?? '',
                'indice_carga_velocidade' => '91V',
                'pressao_dianteiro_normal' => $data['pressure_empty_front'] ?? 28,
                'pressao_traseiro_normal' => $data['pressure_empty_rear'] ?? 28,
                'pressao_dianteiro_carregado' => ($data['pressure_empty_front'] ?? 28) + 3,
                'pressao_traseiro_carregado' => ($data['pressure_empty_rear'] ?? 28) + 3
            ]
        ];
    }

    private function generateCarVersions(array $data): array
    {
        return [
            [
                'versao' => 'Versão Base',
                'medida_pneus' => $data['tire_size'] ?? '',
                'indice_carga_velocidade' => '91V',
                'pressao_dianteiro_normal' => $data['pressure_empty_front'] ?? 32,
                'pressao_traseiro_normal' => $data['pressure_empty_rear'] ?? 30,
                'pressao_dianteiro_carregado' => ($data['pressure_empty_front'] ?? 32) + 3,
                'pressao_traseiro_carregado' => ($data['pressure_empty_rear'] ?? 30) + 3
            ]
        ];
    }

    private function generatePickupVersions(array $data): array
    {
        return $this->generateCarVersions($data); // Similar aos carros
    }

    private function generateMotorcycleLoadTable(array $data): array
    {
        return [
            'titulo' => 'Pressões para Carga Completa',
            'descricao' => 'Valores recomendados quando a motocicleta estiver com garupa e bagagem',
            'condicoes' => [
                [
                    'versao' => 'Versão Base',
                    'ocupantes' => '2 pessoas',
                    'bagagem' => 'Bagageiro carregado',
                    'pressao_dianteira' => (($data['pressure_empty_front'] ?? 28) + 3) . ' PSI',
                    'pressao_traseira' => (($data['pressure_empty_rear'] ?? 28) + 3) . ' PSI',
                    'observacao' => 'Ideal para viagens com garupa'
                ]
            ]
        ];
    }

    private function generateCarLoadTable(array $data): array
    {
        return [
            'titulo' => 'Pressões para Carga Completa',
            'descricao' => 'Valores recomendados quando o veículo estiver com 5 passageiros e bagagem',
            'condicoes' => [
                [
                    'versao' => 'Versão Base',
                    'ocupantes' => '5 pessoas',
                    'bagagem' => 'Porta-malas cheio',
                    'pressao_dianteira' => (($data['pressure_empty_front'] ?? 32) + 3) . ' PSI',
                    'pressao_traseira' => (($data['pressure_empty_rear'] ?? 30) + 3) . ' PSI',
                    'observacao' => 'Ideal para viagens familiares'
                ]
            ]
        ];
    }

    private function generatePickupLoadTable(array $data): array
    {
        return [
            'titulo' => 'Pressões para Carga Completa',
            'descricao' => 'Valores recomendados para diferentes condições de carga da picape',
            'condicoes' => [
                [
                    'versao' => 'Versão Base',
                    'ocupantes' => '5 pessoas',
                    'bagagem' => 'Caçamba carregada',
                    'pressao_dianteira' => (($data['pressure_empty_front'] ?? 35) + 5) . ' PSI',
                    'pressao_traseira' => (($data['pressure_empty_rear'] ?? 35) + 8) . ' PSI',
                    'observacao' => 'Para carga pesada na caçamba'
                ]
            ]
        ];
    }

    private function generateMotorcycleEtiquetaInfo(array $data): array
    {
        return [
            'local_principal' => 'Corrente da suspensão traseira ou manual do proprietário',
            'descricao' => 'Em motocicletas, a informação geralmente está no manual ou próximo à suspensão.',
            'locais_alternativos' => [
                'Manual do proprietário na seção "Especificações Técnicas"',
                'Etiqueta no braço da suspensão traseira',
                'Display digital (se disponível)'
            ],
            'observacao' => 'Consulte sempre o manual para valores oficiais.'
        ];
    }

    private function generateCarEtiquetaInfo(array $data): array
    {
        return [
            'local_principal' => 'Coluna da porta do motorista',
            'descricao' => 'A etiqueta oficial de pressão está na coluna da porta do motorista, visível quando a porta está aberta.',
            'locais_alternativos' => [
                'Manual do proprietário na seção "Especificações Técnicas"',
                'Display digital do painel (se disponível)',
                'Tampa do tanque de combustível'
            ],
            'observacao' => 'Use sempre os valores oficiais da etiqueta como referência.'
        ];
    }

    private function generateMotorcycleSpecialConditions(array $data): array
    {
        return [
            [
                'condicao' => 'Uso Off-road',
                'ajuste_recomendado' => '-2 a -3 PSI',
                'aplicacao' => 'Trilhas, terra batida, areia',
                'justificativa' => 'Maior área de contato melhora aderência em terrenos irregulares.'
            ],
            [
                'condicao' => 'Garupa',
                'ajuste_recomendado' => '+3 PSI traseiro',
                'aplicacao' => 'Pilotando com passageiro',
                'justificativa' => 'Compensa peso adicional mantendo estabilidade.'
            ],
            [
                'condicao' => 'Viagens Longas',
                'ajuste_recomendado' => '+2 PSI',
                'aplicacao' => 'Rodovias, alta velocidade',
                'justificativa' => 'Compensa aquecimento em velocidades sustentadas.'
            ]
        ];
    }

    private function generateCarSpecialConditions(array $data): array
    {
        return [
            [
                'condicao' => 'Viagens Longas',
                'ajuste_recomendado' => '+3 PSI',
                'aplicacao' => 'Rodovias, velocidades sustentadas acima de 120 km/h',
                'justificativa' => 'Compensa o aquecimento dos pneus em altas velocidades.'
            ],
            [
                'condicao' => 'Carga Máxima',
                'ajuste_recomendado' => 'Ver tabela carga completa',
                'aplicacao' => '5 passageiros + bagagem completa',
                'justificativa' => 'Mantém dirigibilidade e segurança com carga total.'
            ],
            [
                'condicao' => 'Uso Urbano',
                'ajuste_recomendado' => 'Pressão padrão',
                'aplicacao' => 'Cidade, baixas velocidades, conforto máximo',
                'justificativa' => 'Pressão ideal para conforto e economia urbana.'
            ]
        ];
    }

    private function generatePickupSpecialConditions(array $data): array
    {
        return [
            [
                'condicao' => 'Carga na Caçamba',
                'ajuste_recomendado' => '+5 PSI traseiro',
                'aplicacao' => 'Transporte de materiais pesados',
                'justificativa' => 'Suporta peso adicional sem comprometer dirigibilidade.'
            ],
            [
                'condicao' => 'Reboque',
                'ajuste_recomendado' => '+3 PSI',
                'aplicacao' => 'Puxando trailer ou reboque',
                'justificativa' => 'Compensa carga adicional e mantém estabilidade.'
            ],
            [
                'condicao' => 'Terrenos Irregulares',
                'ajuste_recomendado' => '-2 PSI',
                'aplicacao' => 'Terra, cascalho, trilhas leves',
                'justificativa' => 'Maior área de contato melhora tração.'
            ]
        ];
    }

    private function generateMotorcycleCareTips(array $data): array
    {
        return [
            [
                'categoria' => 'Verificação Semanal',
                'descricao' => 'Para motocicletas, verificação semanal é obrigatória devido aos pneus menores.'
            ],
            [
                'categoria' => 'Pneus Frios',
                'descricao' => 'Espere pelo menos 30 minutos após parar antes de calibrar.'
            ],
            [
                'categoria' => 'Calibradores Precisos',
                'descricao' => 'Use calibradores digitais - a margem de erro em motos é crítica.'
            ],
            [
                'categoria' => 'Inspeção Visual',
                'descricao' => 'Verifique desgaste, cortes e objetos cravados a cada calibragem.'
            ]
        ];
    }

    private function generateCarCareTips(array $data): array
    {
        return [
            [
                'categoria' => 'Verificação Mensal',
                'descricao' => 'Verifique a pressão dos pneus pelo menos uma vez por mês e sempre antes de viagens longas.'
            ],
            [
                'categoria' => 'Pneus Frios',
                'descricao' => 'Calibre sempre com os pneus frios, preferencialmente pela manhã ou após 3 horas parado.'
            ],
            [
                'categoria' => 'Calibradores Confiáveis',
                'descricao' => 'Use calibradores digitais quando possível. Equipamentos analógicos podem ter margem de erro.'
            ],
            [
                'categoria' => 'Rodízio de Pneus',
                'descricao' => 'Faça o rodízio a cada 10.000 km. Após o rodízio, ajuste a pressão conforme a nova posição.'
            ]
        ];
    }

    private function generatePickupCareTips(array $data): array
    {
        return [
            [
                'categoria' => 'Verificação Quinzenal',
                'descricao' => 'Picapes exigem verificação mais frequente devido ao uso variado.'
            ],
            [
                'categoria' => 'Pneus Traseiros',
                'descricao' => 'Ajuste a pressão traseira conforme a carga transportada.'
            ],
            [
                'categoria' => 'Uso Misto',
                'descricao' => 'Adapte a pressão ao tipo de terreno: mais para asfalto, menos para terra.'
            ],
            [
                'categoria' => 'Desgaste Irregular',
                'descricao' => 'Monitore o desgaste - use diferencial indica problemas de alinhamento.'
            ]
        ];
    }

    private function generateUnitConversion(array $data): array
    {
        $frontPsi = $data['pressure_empty_front'] ?? 32;
        $rearPsi = $data['pressure_empty_rear'] ?? 30;
        
        return [
            'tabela_conversao' => [
                [
                    'psi' => (string) $frontPsi,
                    'kgf_cm2' => number_format($frontPsi / 14.22, 2),
                    'bar' => number_format($frontPsi / 14.5, 2)
                ],
                [
                    'psi' => (string) $rearPsi,
                    'kgf_cm2' => number_format($rearPsi / 14.22, 2),
                    'bar' => number_format($rearPsi / 14.5, 2)
                ],
                [
                    'psi' => (string) ($frontPsi + 3),
                    'kgf_cm2' => number_format(($frontPsi + 3) / 14.22, 2),
                    'bar' => number_format(($frontPsi + 3) / 14.5, 2)
                ],
                [
                    'psi' => '60',
                    'kgf_cm2' => '4,22',
                    'bar' => '4,14'
                ]
            ],
            'formulas' => [
                'psi_para_kgf' => 'kgf/cm² = PSI ÷ 14,22',
                'kgf_para_psi' => 'PSI = kgf/cm² × 14,22',
                'psi_para_bar' => 'Bar = PSI ÷ 14,5'
            ],
            'observacao' => 'No Brasil, PSI é o padrão usado nos postos de combustível.'
        ];
    }

    private function generatePressureImpact(array $data): array
    {
        $frontPsi = $data['pressure_empty_front'] ?? 32;
        $rearPsi = $data['pressure_empty_rear'] ?? 30;
        $pressureDisplay = "{$frontPsi}/{$rearPsi} PSI";
        
        return [
            'subcalibrado' => [
                'titulo' => 'Pneu Subcalibrado',
                'problemas' => [
                    'Maior consumo de combustível (+10% a 15%)',
                    'Desgaste acelerado nas bordas',
                    'Menor estabilidade em curvas',
                    'Alto risco de estouro no calor brasileiro'
                ]
            ],
            'ideal' => [
                'titulo' => "Calibragem Correta ({$pressureDisplay})",
                'beneficios' => [
                    'Consumo otimizado de combustível',
                    'Desgaste uniforme e vida útil máxima',
                    'Aderência e comportamento previsíveis',
                    'Distâncias de frenagem otimizadas'
                ]
            ],
            'sobrecalibrado' => [
                'titulo' => 'Pneu Sobrecalibrado',
                'problemas' => [
                    'Desgaste excessivo no centro',
                    'Menor área de contato com o solo',
                    'Redução na aderência em piso molhado',
                    'Maior rigidez, reduzindo o conforto'
                ]
            ]
        ];
    }
}