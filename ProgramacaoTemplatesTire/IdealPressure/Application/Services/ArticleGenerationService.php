<?php

namespace Src\ContentGeneration\IdealPressure\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\IdealPressure\Domain\Entities\IdealPressure;
use Carbon\Carbon;

/**
 * ArticleGenerationService - MAPEAMENTO de dados VehicleData para JSON estruturado
 * 
 * FASE 2 do processo IdealPressure:
 * - MAPEIA dados já processados do VehicleData (não gera conteúdo novo)
 * - ESTRUTURA em formato JSON igual aos mocks/articles/*.json
 * - ORGANIZA dados técnicos já validados pela Claude 3.7 Sonnet
 * - ADICIONA metadados SEO básicos e estrutura de artigo
 * 
 * ⚠️ IMPORTANTE: NÃO gera conteúdo técnico - apenas mapeia dados existentes
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Corrigida para focar em mapeamento
 */
class ArticleGenerationService
{
    /**
     * Templates por categoria de veículo
     */
    private const TEMPLATE_MAPPING = [
        'motorcycle' => 'tire_calibration_motorcycle',
        'motorcycle_street' => 'tire_calibration_motorcycle',
        'motorcycle_scooter' => 'tire_calibration_motorcycle',
        'pickup' => 'tire_calibration_pickup',
        'truck' => 'tire_calibration_pickup',
        'car_electric' => 'tire_calibration_electric',
        'suv' => 'tire_calibration_car',
        'hatch' => 'tire_calibration_car',
        'sedan' => 'tire_calibration_car',
    ];

    /**
     * Categorias normalizadas para exibição
     */
    private const CATEGORY_DISPLAY = [
        'motorcycle' => 'Motocicletas',
        'motorcycle_street' => 'Motocicletas Street',
        'motorcycle_scooter' => 'Scooters',
        'pickup' => 'Picapes',
        'truck' => 'Caminhões',
        'car_electric' => 'Carros Elétricos',
        'suv' => 'SUVs',
        'hatch' => 'Hatchbacks',
        'sedan' => 'Sedans',
    ];

    /**
     * MAPEAR dados do VehicleData para artigo JSON estruturado
     * 
     * ⚠️ FOCO: Mapear dados existentes, NÃO gerar conteúdo novo
     */
    public function generateCalibrationArticle(IdealPressure $calibration): array
    {
        try {
            // Validar pré-requisitos
            $this->validateCalibrationData($calibration);

            // Extrair dados já processados do VehicleData
            $vehicleData = $this->extractProcessedVehicleData($calibration);
            $pressureData = $this->extractProcessedPressureData($calibration);
            $technicalSpecs = $this->extractTechnicalSpecs($calibration);

            // Determinar template
            $templateType = $this->getTemplateType($calibration->main_category);

            // MAPEAR para estrutura de artigo JSON
            $article = [
                // Estrutura base
                'title' => $this->generateTitle($vehicleData),
                'slug' => $this->generateSlug($vehicleData),
                'template' => $templateType,
                'category_id' => 1,
                'category_name' => 'Calibragem de Pneus',
                'category_slug' => 'calibragem-pneus',

                // MAPEAR dados SEO básicos
                'seo_data' => $this->mapSeoData($vehicleData, $pressureData),

                // MAPEAR conteúdo técnico já processado
                'technical_content' => $this->mapTechnicalContent($vehicleData, $pressureData, $technicalSpecs),

                // MAPEAR benefícios baseados em dados reais
                'benefits_content' => $this->mapBenefitsContent($vehicleData, $technicalSpecs),

                // MAPEAR dicas baseadas na categoria
                'maintenance_tips' => $this->mapMaintenanceTips($vehicleData),

                // MAPEAR alertas baseados em especificações
                'critical_alerts' => $this->mapCriticalAlerts($vehicleData, $pressureData),

                // Metadados de mapeamento
                'generation_metadata' => [
                    'mapped_at' => now()->toISOString(),
                    'template_used' => $templateType,
                    'source_data_quality_score' => $calibration->data_completeness_score ?? 0,
                    'vehicle_data_id' => $calibration->vehicle_data_id,
                    'mapping_method' => 'vehicle_data_structured',
                    'data_already_processed_by_claude' => true,
                ]
            ];

            // Adicionar contagem de palavras
            $article['generation_metadata']['word_count'] = $this->countArticleWords($article);

            Log::info('ArticleGenerationService: Dados mapeados com sucesso', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => "{$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}",
                'template' => $templateType,
                'word_count' => $article['generation_metadata']['word_count'],
                'source_quality' => $calibration->data_completeness_score
            ]);

            return $article;
        } catch (\Exception $e) {
            Log::error('ArticleGenerationService: Erro no mapeamento do artigo', [
                'tire_calibration_id' => $calibration->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Validar dados necessários para mapeamento
     */
    private function validateCalibrationData(IdealPressure $calibration): void
    {
        if (empty($calibration->vehicle_make) || empty($calibration->vehicle_model) || empty($calibration->vehicle_year)) {
            throw new \Exception('Dados básicos do veículo não disponíveis. Execute primeiro a Fase 1 (mapeamento)');
        }

        if (empty($calibration->main_category)) {
            throw new \Exception('Categoria principal do veículo não definida');
        }

        if ($calibration->enrichment_phase !== IdealPressure::PHASE_VEHICLE_ENRICHED) {
            throw new \Exception("IdealPressure não está na fase correta. Fase atual: {$calibration->enrichment_phase}");
        }

        if (empty($calibration->vehicle_basic_data) && empty($calibration->pressure_specifications)) {
            throw new \Exception('Dados do VehicleData não foram mapeados corretamente');
        }
    }

    /**
     * Extrair dados processados do veículo (já validados pela Claude)
     */
    private function extractProcessedVehicleData(IdealPressure $calibration): array
    {
        $basicData = $calibration->vehicle_basic_data ?? [];

        return [
            'make' => $calibration->vehicle_make,
            'model' => $calibration->vehicle_model,
            'year' => $calibration->vehicle_year,
            'category' => $calibration->main_category,
            'category_display' => self::CATEGORY_DISPLAY[$calibration->main_category] ?? 'Veículo',
            'full_name' => "{$calibration->vehicle_make} {$calibration->vehicle_model} {$calibration->vehicle_year}",

            // Características já processadas
            'tire_size' => $basicData['tire_size'] ?? 'Não especificado',
            'vehicle_segment' => $basicData['vehicle_segment'] ?? null,
            'is_premium' => $basicData['is_premium'] ?? false,
            'has_tpms' => $basicData['has_tpms'] ?? false,
            'is_motorcycle' => $basicData['is_motorcycle'] ?? false,
            'is_electric' => $basicData['is_electric'] ?? false,
            'is_hybrid' => $basicData['is_hybrid'] ?? false,
        ];
    }

    /**
     * Extrair especificações de pressão já processadas
     */
    private function extractProcessedPressureData(IdealPressure $calibration): array
    {
        $pressureSpecs = $calibration->pressure_specifications ?? [];

        // Usar dados já processados ou fallbacks seguros
        return [
            'pressure_light_front' => $pressureSpecs['pressure_light_front'] ?? $pressureSpecs['pressure_empty_front'] ?? null,
            'pressure_light_rear' => $pressureSpecs['pressure_light_rear'] ?? $pressureSpecs['pressure_empty_rear'] ?? null,
            'pressure_max_front' => $pressureSpecs['pressure_max_front'] ?? null,
            'pressure_max_rear' => $pressureSpecs['pressure_max_rear'] ?? null,
            'pressure_spare' => $pressureSpecs['pressure_spare'] ?? null,
            'pressure_display' => $pressureSpecs['pressure_display'] ?? null,
        ];
    }

    /**
     * Extrair especificações técnicas já processadas
     */
    private function extractTechnicalSpecs(IdealPressure $calibration): array
    {
        $features = $calibration->vehicle_features ?? [];

        return [
            'engine_data' => $features['engine_data'] ?? [],
            'transmission_data' => $features['transmission_data'] ?? [],
            'fuel_data' => $features['fuel_data'] ?? [],
            'dimensions' => $features['dimensions'] ?? [],
            'technical_specs' => $features['technical_specs'] ?? [],
        ];
    }

    /**
     * Obter tipo de template baseado na categoria
     */
    private function getTemplateType(string $category): string
    {
        return self::TEMPLATE_MAPPING[$category] ?? 'tire_calibration_car';
    }

    /**
     * Gerar título otimizado
     */
    private function generateTitle(array $vehicleData): string
    {
        return "Calibragem do Pneu da {$vehicleData['full_name']} – Guia Completo";
    }

    /**
     * Gerar slug otimizado
     */
    private function generateSlug(array $vehicleData): string
    {
        $make = Str::slug($vehicleData['make']);
        $model = Str::slug($vehicleData['model']);
        $year = $vehicleData['year'];

        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * MAPEAR dados SEO básicos (não gerar conteúdo complexo)
     */
    private function mapSeoData(array $vehicleData, array $pressureData): array
    {
        $fullName = $vehicleData['full_name'];
        $categoryDisplay = $vehicleData['category_display'];

        // Usar pressões reais ou fallbacks
        $frontPressure = $pressureData['pressure_light_front'] ?? '32';
        $rearPressure = $pressureData['pressure_light_rear'] ?? '30';

        // Palavra-chave primária simples
        $primaryKeyword = strtolower("calibragem pneu {$vehicleData['make']} {$vehicleData['model']} {$vehicleData['year']}");

        return [
            'page_title' => "Calibragem do Pneu da {$fullName} – Guia Completo",
            'meta_description' => "Guia completo de calibragem dos pneus da {$fullName}. Dianteiros: {$frontPressure} PSI / Traseiros: {$rearPressure} PSI. Procedimento para {$categoryDisplay}.",
            'h1' => "Calibragem do Pneu da {$fullName} – Guia Completo",
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => $this->generateBasicSecondaryKeywords($vehicleData),
            'og_title' => "Calibragem do Pneu da {$fullName} – Guia Oficial",
            'og_description' => "Procedimento completo: Dianteiros {$frontPressure} PSI / Traseiros {$rearPressure} PSI para {$fullName}.",
        ];
    }

    /**
     * Gerar keywords secundárias básicas
     */
    private function generateBasicSecondaryKeywords(array $vehicleData): array
    {
        $make = strtolower($vehicleData['make']);
        $model = strtolower($vehicleData['model']);
        $year = $vehicleData['year'];

        return [
            "como calibrar pneu {$make} {$model}",
            "calibragem {$make} {$model} {$year}",
            "pressão pneu {$make} {$model}",
            "procedimento calibragem {$make}",
        ];
    }

    /**
     * MAPEAR conteúdo técnico já processado do VehicleData
     */
    private function mapTechnicalContent(array $vehicleData, array $pressureData, array $technicalSpecs): array
    {
        return [
            'especificacoes_pressao' => $this->mapPressureSpecifications($pressureData, $vehicleData),
            'informacoes_veiculo' => $this->mapVehicleInformation($vehicleData, $technicalSpecs),
            'caracteristicas_tecnicas' => $this->mapTechnicalCharacteristics($technicalSpecs),
            'consideracoes_categoria' => $this->mapCategoryConsiderations($vehicleData),
        ];
    }

    /**
     * MAPEAR especificações de pressão já processadas
     */
    private function mapPressureSpecifications(array $pressureData, array $vehicleData): array
    {
        $frontLight = $pressureData['pressure_light_front'] ?? null;
        $rearLight = $pressureData['pressure_light_rear'] ?? null;
        $frontMax = $pressureData['pressure_max_front'] ?? ($frontLight ? $frontLight + 4 : null);
        $rearMax = $pressureData['pressure_max_rear'] ?? ($rearLight ? $rearLight + 4 : null);
        $spare = $pressureData['pressure_spare'] ?? null;

        $specs = [
            'titulo' => 'Especificações de Pressão dos Pneus',
        ];

        // Mapear pressões se disponíveis
        if ($frontLight && $rearLight) {
            $specs['pressoes'] = [
                'carga_leve' => [
                    'dianteiro' => $frontLight,
                    'traseiro' => $rearLight,
                    'descricao' => 'Para uso urbano e carga normal'
                ]
            ];

            if ($frontMax && $rearMax) {
                $specs['pressoes']['carga_maxima'] = [
                    'dianteiro' => $frontMax,
                    'traseiro' => $rearMax,
                    'descricao' => 'Para carga completa e viagens longas'
                ];
            }

            $specs['resumo'] = "Dianteiros: {$frontLight} PSI / Traseiros: {$rearLight} PSI";
        } else {
            $specs['observacao'] = 'Especificações de pressão não disponíveis nos dados do veículo';
        }

        // Adicionar info do TPMS se disponível
        if ($vehicleData['has_tpms']) {
            $specs['sistema_tpms'] = [
                'disponivel' => true,
                'descricao' => 'Veículo equipado com sistema de monitoramento de pressão'
            ];
        }

        return $specs;
    }

    /**
     * MAPEAR informações do veículo já processadas
     */
    private function mapVehicleInformation(array $vehicleData, array $technicalSpecs): array
    {
        $info = [
            'titulo' => 'Informações do Veículo',
            'dados_basicos' => [
                'marca' => $vehicleData['make'],
                'modelo' => $vehicleData['model'],
                'ano' => $vehicleData['year'],
                'categoria' => $vehicleData['category_display'],
                'tamanho_pneu' => $vehicleData['tire_size'],
            ]
        ];

        // Adicionar características especiais se disponíveis
        $features = [];
        if ($vehicleData['is_premium']) $features[] = 'Veículo premium';
        if ($vehicleData['has_tpms']) $features[] = 'Sistema TPMS';
        if ($vehicleData['is_electric']) $features[] = 'Motorização elétrica';
        if ($vehicleData['is_hybrid']) $features[] = 'Sistema híbrido';

        if (!empty($features)) {
            $info['caracteristicas_especiais'] = $features;
        }

        return $info;
    }

    /**
     * MAPEAR características técnicas já processadas
     */
    private function mapTechnicalCharacteristics(array $technicalSpecs): array
    {
        $characteristics = [
            'titulo' => 'Características Técnicas'
        ];

        // Mapear dados do motor se disponíveis
        if (!empty($technicalSpecs['engine_data'])) {
            $engineData = $technicalSpecs['engine_data'];
            $characteristics['motor'] = array_filter([
                'tipo' => $engineData['engine_type'] ?? null,
                'cilindrada' => $engineData['displacement'] ?? null,
                'potencia' => $engineData['horsepower'] ?? null,
                'combustivel' => $engineData['fuel_type'] ?? null,
            ]);
        }

        // Mapear dados de combustível se disponíveis
        if (!empty($technicalSpecs['fuel_data'])) {
            $fuelData = $technicalSpecs['fuel_data'];
            $characteristics['consumo'] = array_filter([
                'cidade' => $fuelData['consumption_city'] ?? null,
                'estrada' => $fuelData['consumption_highway'] ?? null,
                'tanque' => $fuelData['fuel_tank_capacity'] ?? null,
            ]);
        }

        // Mapear dimensões se disponíveis
        if (!empty($technicalSpecs['dimensions'])) {
            $dimensions = $technicalSpecs['dimensions'];
            $characteristics['dimensoes'] = array_filter([
                'comprimento' => $dimensions['length'] ?? null,
                'largura' => $dimensions['width'] ?? null,
                'altura' => $dimensions['height'] ?? null,
            ]);
        }

        return $characteristics;
    }

    /**
     * MAPEAR considerações específicas por categoria
     */
    private function mapCategoryConsiderations(array $vehicleData): array
    {
        $category = $vehicleData['category'];

        return match ($category) {
            'motorcycle', 'motorcycle_street', 'motorcycle_scooter' => [
                'titulo' => 'Considerações para Motocicletas',
                'seguranca' => 'Pressão correta é crítica para estabilidade e segurança',
                'verificacao' => 'Verificação mais frequente recomendada (semanal)',
                'diferencial' => 'Equilíbrio entre dianteiro e traseiro é essencial'
            ],
            'car_electric' => [
                'titulo' => 'Considerações para Veículos Elétricos',
                'autonomia' => 'Pressão correta otimiza a autonomia da bateria',
                'eficiencia' => 'Impacto direto na eficiência energética',
                'regeneracao' => 'Melhora a eficácia da frenagem regenerativa'
            ],
            'pickup' => [
                'titulo' => 'Considerações para Picapes',
                'carga_variavel' => 'Ajustar pressão conforme carga transportada',
                'uso_profissional' => 'Pressão adequada essencial para trabalho pesado',
                'versatilidade' => 'Diferentes pressões para diferentes usos'
            ],
            default => [
                'titulo' => 'Considerações Gerais',
                'importancia' => 'Pressão correta garante segurança e economia',
                'manutencao' => 'Verificação quinzenal recomendada',
                'beneficios' => 'Múltiplos benefícios com procedimento simples'
            ]
        };
    }

    /**
     * MAPEAR benefícios baseados em dados reais
     */
    private function mapBenefitsContent(array $vehicleData, array $technicalSpecs): array
    {
        return [
            'titulo' => 'Benefícios da Calibragem Correta',
            'seguranca' => [
                'titulo' => 'Segurança',
                'itens' => [
                    'Melhor aderência e estabilidade',
                    'Frenagem mais eficiente',
                    'Redução do risco de acidentes'
                ]
            ],
            'economia' => [
                'titulo' => 'Economia',
                'itens' => [
                    'Redução no consumo de combustível',
                    'Maior durabilidade dos pneus',
                    'Menos manutenções necessárias'
                ]
            ],
            'performance' => [
                'titulo' => 'Performance',
                'itens' => [
                    'Direção mais precisa',
                    'Conforto de rodagem',
                    'Melhor resposta do veículo'
                ]
            ]
        ];
    }

    /**
     * MAPEAR dicas de manutenção baseadas na categoria
     */
    private function mapMaintenanceTips(array $vehicleData): array
    {
        $category = $vehicleData['category'];
        $frequency = str_contains($category, 'motorcycle') ? 'semanal' : 'quinzenal';

        return [
            'titulo' => 'Dicas de Manutenção',
            'verificacao_periodica' => [
                'frequencia' => "Verificação {$frequency}",
                'melhor_horario' => 'Pela manhã, com pneus frios',
                'procedimento' => 'Medir todos os pneus com calibrador confiável'
            ],
            'equipamentos' => [
                'essencial' => 'Calibrador de pressão digital',
                'recomendado' => 'Compressor portátil 12V',
                'emergencia' => 'Kit de reparo básico'
            ],
            'cuidados' => [
                'temperatura' => 'Sempre calibrar com pneus frios',
                'precisao' => 'Usar equipamento calibrado',
                'registro' => 'Anotar datas e valores para controle'
            ]
        ];
    }

    /**
     * MAPEAR alertas críticos baseados nas especificações
     */
    private function mapCriticalAlerts(array $vehicleData, array $pressureData): array
    {
        $alerts = [
            'titulo' => 'Alertas Importantes',
            'seguranca_critica' => [
                'titulo' => 'Segurança Crítica',
                'pressao_baixa' => 'Nunca rode com pressão muito abaixo da recomendada',
                'pressao_alta' => 'Pressão excessiva também compromete a segurança',
            ],
            'manutencao' => [
                'titulo' => 'Manutenção',
                'negligencia' => 'Pressão incorreta é a principal causa de problemas com pneus',
                'verificacao' => 'Verificação regular evita problemas maiores',
            ]
        ];

        // Alertas específicos por categoria
        if (str_contains($vehicleData['category'], 'motorcycle')) {
            $alerts['motocicleta'] = [
                'titulo' => 'CRÍTICO - Motocicletas',
                'risco' => 'Pressão incorreta em motos pode ser fatal',
                'cuidado' => 'Atenção especial à estabilidade e frenagem'
            ];
        }

        if ($vehicleData['category'] === 'car_electric') {
            $alerts['veiculo_eletrico'] = [
                'titulo' => 'Veículos Elétricos',
                'autonomia' => 'Pressão incorreta reduz significativamente a autonomia',
                'eficiencia' => 'Impacto direto na eficiência energética'
            ];
        }

        return $alerts;
    }

    /**
     * Contar palavras do artigo mapeado
     */
    private function countArticleWords(array $article): int
    {
        $text = '';
        array_walk_recursive($article, function ($value) use (&$text) {
            if (is_string($value)) {
                $text .= ' ' . $value;
            }
        });

        return str_word_count(strip_tags($text));
    }

    /**
     * Validar artigo mapeado
     */
    public function validateMappedArticle(array $article): array
    {
        $issues = [];
        $score = 0;
        $maxScore = 10;

        // Validar estrutura básica
        $requiredFields = ['title', 'slug', 'seo_data', 'technical_content'];
        foreach ($requiredFields as $field) {
            if (isset($article[$field]) && !empty($article[$field])) {
                $score += 2;
            } else {
                $issues[] = "Campo obrigatório '{$field}' ausente ou vazio";
            }
        }

        // Validar SEO básico
        $seoData = $article['seo_data'] ?? [];
        if (isset($seoData['primary_keyword']) && !empty($seoData['primary_keyword'])) {
            $score += 1;
        } else {
            $issues[] = "Primary keyword não definida";
        }

        // Validar mapeamento de dados
        if (
            isset($article['generation_metadata']['source_data_quality_score']) &&
            $article['generation_metadata']['source_data_quality_score'] > 5
        ) {
            $score += 1;
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'mapping_quality_score' => round(($score / $maxScore) * 10, 2),
            'word_count' => $this->countArticleWords($article),
            'data_source_quality' => $article['generation_metadata']['source_data_quality_score'] ?? 0
        ];
    }

    /**
     * Obter estatísticas de mapeamento
     */
    public function getMappingStats(): array
    {
        $total = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_VEHICLE_ENRICHED)->count();
        $mapped = IdealPressure::whereNotNull('generated_article')->count();
        $avgQuality = IdealPressure::whereNotNull('content_quality_score')->avg('content_quality_score') ?? 0;

        return [
            'ready_for_mapping' => $total,
            'articles_mapped' => $mapped,
            'mapping_success_rate' => $total > 0 ? round(($mapped / $total) * 100, 2) : 0,
            'avg_mapping_quality' => round($avgQuality, 2),
            'templates_available' => count(self::TEMPLATE_MAPPING),
            'categories_supported' => count(self::CATEGORY_DISPLAY)
        ];
    }
}
