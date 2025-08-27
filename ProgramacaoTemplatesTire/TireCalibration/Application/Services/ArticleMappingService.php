<?php

namespace Src\ContentGeneration\TireCalibration\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Carbon\Carbon;

/**
 * ArticleMappingService - Mapear dados JSON físicos para estrutura completa de artigos
 * 
 * Serviço responsável por:
 * - Ler dados JSON de database/vehicle-data/
 * - Mapear para estrutura idêntica aos mocks/articles/
 * - Gerar conteúdo rico baseado nos templates (car, motorcycle, pickup)
 * - Aplicar SEO, metadados e estrutura completa
 * 
 * BASEADO NOS EXEMPLOS DOS DOCUMENTOS:
 * - tire-calibration-honda-cg-160-2019.json (motorcycle)  
 * - tire-calibration-mercedes-eqa-2025.json (car)
 * - tire-calibration-peugeot-3008-2025.json (suv)
 * - tire-calibration-toyota-corolla-hybrid-2025.json (hybrid)
 * - tire-calibration-suzuki-gsx-s-1000-2023.json (sport motorcycle)
 * 
 * @author Claude Sonnet 4
 * @version 2.0
 */
class ArticleMappingService
{
    /**
     * Templates por categoria
     */
    private const TEMPLATE_MAPPING = [
        // Carros
        'sedan' => 'tire_calibration_car',
        'hatch' => 'tire_calibration_car',
        'suv' => 'tire_calibration_car',
        'car_electric' => 'tire_calibration_car',
        'car_hybrid' => 'tire_calibration_car',
        
        // Motocicletas  
        'motorcycle' => 'tire_calibration_motorcycle',
        'motorcycle_street' => 'tire_calibration_motorcycle',
        'motorcycle_sport' => 'tire_calibration_motorcycle',
        'motorcycle_naked' => 'tire_calibration_motorcycle',
        'motorcycle_scooter' => 'tire_calibration_motorcycle',
        
        // Picapes e caminhões
        'pickup' => 'tire_calibration_pickup',
        'truck' => 'tire_calibration_pickup',
    ];

    /**
     * Mapear dados JSON do vehicle-data para estrutura completa de artigo
     */
    public function mapVehicleDataToArticle(array $vehicleData, TireCalibration $calibration): array
    {
        try {
            // Determinar template baseado na categoria
            $template = $this->getTemplate($vehicleData['main_category'] ?? $calibration->main_category);
            
            // Estrutura base do artigo (igual aos mocks)
            $article = [
                'title' => $this->generateTitle($vehicleData),
                'slug' => $this->generateSlug($vehicleData),
                'template' => $template,
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

            Log::info('ArticleMappingService: Artigo mapeado com sucesso', [
                'vehicle' => $vehicleData['make'] . ' ' . $vehicleData['model'],
                'template' => $template,
                'title_length' => strlen($article['title']),
                'content_sections' => count($article['content'])
            ]);

            return $article;

        } catch (\Exception $e) {
            Log::error('ArticleMappingService: Erro no mapeamento', [
                'vehicle_data' => $vehicleData['make'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obter template baseado na categoria
     */
    private function getTemplate(string $category): string
    {
        return self::TEMPLATE_MAPPING[$category] ?? 'tire_calibration_car';
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
        $fullName = trim("{$make} {$model}");
        
        $pressureDisplay = $this->getPressureDisplay($data);
        
        $title = $this->generateTitle($data);
        $primaryKeyword = strtolower("calibragem pneu {$make} {$model}");

        return [
            'page_title' => $title,
            'meta_description' => "Guia completo de calibragem dos pneus do {$fullName}. {$pressureDisplay}. Procedimento específico e dicas especializadas.",
            'h1' => $title,
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => [
                "como calibrar pneu {$make} {$model}",
                "pressão pneu {$make}",
                "calibrar pneu {$model}",
                "procedimento calibragem {$make}"
            ],
            'og_title' => $title,
            'og_description' => "Procedimento completo de calibragem dos pneus do {$fullName}. Pressões específicas e dicas especializadas.",
            'canonical_url' => $this->generateCanonicalUrl($data)
        ];
    }

    /**
     * Mapear seção vehicle_data (VERSION V2 - SEM ANO)
     */
    private function mapVehicleDataSection(array $data): array
    {
        return [
            'make' => $data['make'] ?? '',
            'model' => $data['model'] ?? '',
            'tire_size' => $data['tire_size'] ?? '',
            'main_category' => $data['main_category'] ?? 'car',
            'vehicle_segment' => $this->getVehicleSegment($data),
            'vehicle_type' => $this->getVehicleType($data),
            'pressure_specifications' => $this->mapPressureSpecs($data),
            'tire_specifications' => $this->mapTireSpecs($data),
            'vehicle_features' => $this->mapVehicleFeatures($data),
            'is_premium' => $this->isPremium($data),
            'has_tpms' => $data['has_tpms'] ?? false,
            'is_motorcycle' => $this->isMotorcycle($data),
            'is_electric' => $this->isElectric($data),
            'is_hybrid' => $this->isHybrid($data),
            'data_quality_score' => (int) ($data['data_quality_score'] ?? 8)
        ];
    }

    /**
     * Mapear especificações de pressão
     */
    private function mapPressureSpecs(array $data): array
    {
        $pressureFront = (int) ($data['pressure_empty_front'] ?? 32);
        $pressureRear = (int) ($data['pressure_empty_rear'] ?? 32);
        
        return [
            'pressure_empty_front' => $pressureFront,
            'pressure_empty_rear' => $pressureRear,
            'pressure_light_front' => $pressureFront,
            'pressure_light_rear' => $pressureRear,
            'pressure_max_front' => $pressureFront + 3,
            'pressure_max_rear' => $pressureRear + 3,
            'pressure_spare' => $this->isMotorcycle($data) ? null : 60,
            'pressure_display' => $this->getPressureDisplay($data, $pressureFront, $pressureRear),
            'empty_pressure_display' => "{$pressureFront}/{$pressureRear} PSI",
            'loaded_pressure_display' => ($pressureFront + 3) . "/" . ($pressureRear + 3) . " PSI"
        ];
    }

    /**
     * Mapear especificações de pneus
     */
    private function mapTireSpecs(array $data): array
    {
        return [
            'tire_size' => $data['tire_size'] ?? '',
            'recommended_brands' => $this->getRecommendedBrands($data),
            'seasonal_recommendations' => $this->getSeasonalRecommendations($data)
        ];
    }

    /**
     * Mapear características do veículo (VERSION V2 - SEM ANO)
     */
    private function mapVehicleFeatures(array $data): array
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
        return str_contains($category, 'motorcycle');
    }

    private function isElectric(array $data): bool
    {
        return ($data['main_category'] ?? '') === 'car_electric';
    }

    private function isHybrid(array $data): bool
    {
        return ($data['main_category'] ?? '') === 'car_hybrid';
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
        $segmentMap = [
            'hatch' => 'B',
            'sedan' => 'C', 
            'suv' => 'D',
            'pickup' => 'F',
            'truck' => 'F'
        ];
        
        return $segmentMap[$category] ?? 'C';
    }

    private function getVehicleType(array $data): string
    {
        if ($this->isMotorcycle($data)) return 'motorcycle';
        
        $category = $data['main_category'] ?? '';
        return in_array($category, ['pickup', 'truck']) ? $category : 'car';
    }

    private function getPressureDisplay(array $data, ?int $front = null, ?int $rear = null): string
    {
        $front = $front ?? (int) ($data['pressure_empty_front'] ?? 32);
        $rear = $rear ?? (int) ($data['pressure_empty_rear'] ?? 32);
        
        return $this->isMotorcycle($data) 
            ? "Dianteiro: {$front} PSI / Traseiro: {$rear} PSI"
            : "Dianteiros: {$front} PSI / Traseiros: {$rear} PSI";
    }

    private function getRecommendedBrands(array $data): array
    {
        if ($this->isMotorcycle($data)) {
            return ['Michelin', 'Pirelli', 'Bridgestone', 'Continental'];
        }
        
        return ['Continental', 'Michelin', 'Bridgestone', 'Goodyear'];
    }

    private function getSeasonalRecommendations(array $data): array
    {
        if ($this->isMotorcycle($data)) {
            return ['Michelin Pilot Street', 'Pirelli Diablo Rosso III'];
        }
        
        if ($this->isElectric($data)) {
            return ['Continental EcoContact 6', 'Michelin e-Primacy'];
        }
        
        return ['Continental PremiumContact 6', 'Michelin Energy XM2+'];
    }

    private function getCategoryNormalized(array $data): string
    {
        if ($this->isMotorcycle($data)) {
            return 'Motocicleta';
        }
        
        if ($this->isElectric($data)) {
            return 'SUV Elétrico Premium';
        }
        
        if ($this->isHybrid($data)) {
            return 'Sedan Híbrido Flex';
        }
        
        $category = $data['main_category'] ?? '';
        $categoryMap = [
            'sedan' => 'Sedan',
            'hatch' => 'Hatchback',
            'suv' => 'SUV Premium',
            'pickup' => 'Picape',
            'truck' => 'Caminhão'
        ];
        
        return $categoryMap[$category] ?? 'Automóvel';
    }

    private function getRecommendedOil(array $data): string
    {
        if ($this->isMotorcycle($data)) {
            return '10W40 Sintético';
        }
        
        if ($this->isElectric($data)) {
            return 'N/A';
        }
        
        return '5W30 Sintético';
    }

    private function getMainCategoryPortuguese(array $data): string
    {
        $category = $data['main_category'] ?? '';
        $categoryMap = [
            'sedan' => 'sedan',
            'hatch' => 'hatches',
            'suv' => 'suv',
            'pickup' => 'pickup',
            'motorcycle' => 'naked',
            'motorcycle_street' => 'street',
            'motorcycle_sport' => 'sport',
            'motorcycle_naked' => 'naked'
        ];
        
        return $categoryMap[$category] ?? 'automóvel';
    }

    private function getMotorization(array $data): string
    {
        if ($this->isElectric($data)) return 'Elétrico';
        if ($this->isHybrid($data)) return 'Híbrido Flex';
        if ($this->isMotorcycle($data)) return '321cc'; // Exemplo padrão
        
        return '1.6 Turbo'; // Exemplo padrão para carros
    }

    private function getFuelType(array $data): string
    {
        if ($this->isElectric($data)) return 'Elétrico';
        if ($this->isHybrid($data)) return 'Híbrido Flex';
        if ($this->isMotorcycle($data)) return 'Gasolina';
        
        return 'Flex';
    }

    // ===== GERAÇÃO DE CONTEÚDO POR TEMPLATE =====

    private function generateIntroduction(array $data): string
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $fullName = trim("{$make} {$model}");
        
        if ($this->isMotorcycle($data)) {
            return "A calibragem correta dos pneus da sua {$fullName} é crucial para a segurança, desempenho e durabilidade. Esta motocicleta exige atenção especial à pressão dos pneus para aproveitar todo seu potencial com máxima segurança.";
        }
        
        return "Manter os pneus do seu {$fullName} com a pressão correta é fundamental para garantir segurança, economia de combustível e prolongar a vida útil dos pneus.";
    }

    private function generateFAQ(array $data): array
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $fullName = trim("{$make} {$model}");
        $pressureDisplay = $this->getPressureDisplay($data);
        
        return [
            [
                'pergunta' => "Qual a pressão ideal do {$fullName} em PSI?",
                'resposta' => "Para o {$fullName}, use {$pressureDisplay} para uso normal. Sempre verifique com pneus frios para manter segurança e economia."
            ],
            [
                'pergunta' => "Com que frequência verificar a pressão?",
                'resposta' => $this->isMotorcycle($data) 
                    ? "Semanalmente é obrigatório para motocicletas. Verifique sempre com pneus frios."
                    : "Mensalmente é recomendado, e sempre antes de viagens longas."
            ]
        ];
    }

    private function generateFinalConsiderations(array $data): string
    {
        $make = $data['make'] ?? '';
        $model = $data['model'] ?? '';
        $fullName = trim("{$make} {$model}");
        $pressureDisplay = $this->getPressureDisplay($data);
        
        if ($this->isMotorcycle($data)) {
            return "A {$fullName} merece cuidado especial na calibragem dos pneus. Em motos não há margem para erro - sua segurança depende diretamente da pressão correta. Mantenha {$pressureDisplay} e lembre-se: verificação semanal é obrigatória, sempre com pneus frios.";
        }
        
        return "O {$fullName} recompensa o cuidado adequado com os pneus. Manter a pressão correta ({$pressureDisplay}) garante economia, segurança e durabilidade. A verificação regular é simples e evita problemas maiores.";
    }

    private function generateCarContent(array $data): array
    {
        $pressureFront = (int) ($data['pressure_empty_front'] ?? 32);
        $pressureRear = (int) ($data['pressure_empty_rear'] ?? 32);
        
        return [
            'especificacoes_por_versao' => [
                [
                    'versao' => 'Versão Base',
                    'medida_pneus' => $data['tire_size'] ?? '205/55 R16',
                    'indice_carga_velocidade' => '91V',
                    'pressao_dianteiro_normal' => $pressureFront,
                    'pressao_traseiro_normal' => $pressureRear,
                    'pressao_dianteiro_carregado' => $pressureFront + 3,
                    'pressao_traseiro_carregado' => $pressureRear + 3
                ]
            ],
            
            'tabela_carga_completa' => [
                'titulo' => 'Pressões para Carga Completa',
                'descricao' => 'Valores recomendados quando o veículo estiver com 5 passageiros e bagagem',
                'condicoes' => [
                    [
                        'versao' => 'Versão Base',
                        'ocupantes' => '5 pessoas',
                        'bagagem' => 'Porta-malas cheio',
                        'pressao_dianteira' => ($pressureFront + 3) . ' PSI',
                        'pressao_traseira' => ($pressureRear + 3) . ' PSI',
                        'observacao' => 'Ideal para viagens familiares'
                    ]
                ]
            ],
            
            'localizacao_etiqueta' => [
                'local_principal' => 'Coluna da porta do motorista',
                'descricao' => 'A etiqueta oficial de pressão está na coluna da porta do motorista, visível quando a porta está aberta.',
                'locais_alternativos' => [
                    'Manual do proprietário na seção "Especificações Técnicas"',
                    'Display digital do painel (se disponível)',
                    'Tampa do tanque de combustível'
                ],
                'observacao' => 'Use sempre os valores oficiais da etiqueta como referência.'
            ],
            
            'condicoes_especiais' => [
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
            ],
            
            'conversao_unidades' => [
                'tabela_conversao' => [
                    [
                        'psi' => (string) $pressureRear,
                        'kgf_cm2' => number_format($pressureRear / 14.22, 2),
                        'bar' => number_format($pressureRear / 14.5, 2)
                    ],
                    [
                        'psi' => (string) $pressureFront,
                        'kgf_cm2' => number_format($pressureFront / 14.22, 2),
                        'bar' => number_format($pressureFront / 14.5, 2)
                    ],
                    [
                        'psi' => (string) ($pressureFront + 3),
                        'kgf_cm2' => number_format(($pressureFront + 3) / 14.22, 2),
                        'bar' => number_format(($pressureFront + 3) / 14.5, 2)
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
            ],
            
            'cuidados_recomendacoes' => [
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
            ],
            
            'impacto_pressao' => [
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
                    'titulo' => 'Calibragem Correta (' . $pressureFront . '/' . $pressureRear . ' PSI)',
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
            ]
        ];
    }

    private function generateMotorcycleContent(array $data): array
    {
        $tireSizes = explode(' ', $data['tire_size'] ?? '120/70-17 140/70-17');
        $pressureFront = (int) ($data['pressure_empty_front'] ?? 33);
        $pressureRear = (int) ($data['pressure_empty_rear'] ?? 36);
        
        return [
            'especificacoes_pneus' => [
                'pneu_dianteiro' => [
                    'medida_original' => $tireSizes[0] ?? '120/70-17',
                    'indice_carga' => '54',
                    'indice_velocidade' => 'H',
                    'tipo_construcao' => 'Radial',
                    'marca_original' => 'Dunlop',
                    'alternativas_recomendadas' => [
                        'Michelin Pilot Street',
                        'Pirelli Diablo Rosso III',
                        'Continental ContiMotion'
                    ]
                ],
                'pneu_traseiro' => [
                    'medida_original' => $tireSizes[1] ?? '140/70-17',
                    'indice_carga' => '66',
                    'indice_velocidade' => 'H',
                    'tipo_construcao' => 'Radial',
                    'marca_original' => 'Dunlop',
                    'alternativas_recomendadas' => [
                        'Michelin Pilot Street',
                        'Pirelli Diablo Rosso III',
                        'Continental ContiMotion'
                    ]
                ],
                'observacao' => 'Use sempre pneus radiais com especificação adequada para motocicletas.'
            ],
            
            'tabela_pressoes' => [
                'pressoes_oficiais' => [
                    'piloto_solo' => [
                        'dianteira' => $pressureFront . ' PSI',
                        'traseira' => $pressureRear . ' PSI',
                        'observacao' => 'Para piloto até 80kg + equipamentos'
                    ],
                    'piloto_garupa' => [
                        'dianteira' => ($pressureFront + 2) . ' PSI',
                        'traseira' => ($pressureRear + 2) . ' PSI',
                        'observacao' => 'Piloto + garupa até peso máximo'
                    ]
                ],
                'condicoes_especiais' => [
                    [
                        'situacao' => 'Uso urbano',
                        'terreno' => 'Cidade/trânsito',
                        'pressao_dianteira' => $pressureFront . ' PSI',
                        'pressao_traseira' => $pressureRear . ' PSI',
                        'temperatura_ideal' => 'Pneus frios (manhã)',
                        'observacao' => 'Ideal para uso diário na cidade.'
                    ],
                    [
                        'situacao' => 'Viagem rodoviária',
                        'terreno' => 'Rodovias',
                        'pressao_dianteira' => ($pressureFront + 2) . ' PSI',
                        'pressao_traseira' => ($pressureRear + 2) . ' PSI',
                        'temperatura_ideal' => 'Pneus frios',
                        'observacao' => 'Para viagens longas acima de 100 km/h.'
                    ]
                ]
            ],
            
            'localizacao_informacoes' => [
                'manual_proprietario' => [
                    'localizacao' => 'Seção Especificações Técnicas',
                    'secao' => 'Rodas e Pneus',
                    'pagina_aproximada' => 'Consulte índice do manual'
                ],
                'etiqueta_moto' => [
                    'localizacao_principal' => 'Braço da suspensão traseira (swing arm)',
                    'localizacoes_alternativas' => [
                        'Próximo ao número do chassi (lado direito)',
                        'Manual do proprietário (compartimento sob o assento)',
                        'Etiqueta no chassi principal'
                    ]
                ],
                'dica_importante' => 'Use sempre PSI como referência padrão brasileiro.'
            ],
            
            'conversao_unidades' => [
                'tabela_conversao' => [
                    [
                        'psi' => (string) ($pressureFront - 2),
                        'kgf_cm2' => number_format(($pressureFront - 2) / 14.22, 2),
                        'bar' => number_format(($pressureFront - 2) / 14.5, 2),
                        'is_recommended' => false
                    ],
                    [
                        'psi' => (string) $pressureFront,
                        'kgf_cm2' => number_format($pressureFront / 14.22, 2),
                        'bar' => number_format($pressureFront / 14.5, 2),
                        'is_recommended' => true
                    ],
                    [
                        'psi' => (string) $pressureRear,
                        'kgf_cm2' => number_format($pressureRear / 14.22, 2),
                        'bar' => number_format($pressureRear / 14.5, 2),
                        'is_recommended' => true
                    ],
                    [
                        'psi' => (string) ($pressureRear + 2),
                        'kgf_cm2' => number_format(($pressureRear + 2) / 14.22, 2),
                        'bar' => number_format(($pressureRear + 2) / 14.5, 2),
                        'is_recommended' => false
                    ]
                ],
                'observacao' => 'Para motocicleta, a precisão na calibragem é ainda mais crítica.'
            ],
            
            'consideracoes_especiais' => [
                [
                    'categoria' => 'temperatura',
                    'titulo' => 'Impacto da Temperatura',
                    'descricao' => 'Motocicletas são mais sensíveis a variações de temperatura devido ao peso leve.',
                    'fatores' => [
                        'No calor brasileiro (35°C+), pressão pode aumentar 4-5 PSI',
                        'Sempre calibre com pneus frios (manhã ou após 3h parado)',
                        'Pneus quentes podem mostrar até 6 PSI a mais que o real'
                    ],
                    'importancia' => 'crítica'
                ],
                [
                    'categoria' => 'carga',
                    'titulo' => 'Ajustes por Carga e Peso',
                    'descricao' => 'O peso influencia significativamente a calibragem em motocicletas.',
                    'tipos' => [
                        'Piloto leve (≤65kg): reduzir 1 PSI no traseiro',
                        'Piloto médio (66-85kg): usar pressões padrão',
                        'Piloto pesado (≥86kg): aumentar 2 PSI no traseiro',
                        'Com garupa: sempre usar pressões para "piloto + garupa"'
                    ],
                    'importancia' => 'alta'
                ]
            ],
            
            'beneficios_calibragem' => [
                [
                    'categoria' => 'seguranca',
                    'titulo' => 'Segurança Máxima',
                    'descricao' => 'Pressão correta é fundamental para estabilidade e frenagem em motocicletas.',
                    'aspectos' => [
                        'Aderência otimizada em curvas e frenagens',
                        'Estabilidade em altas velocidades',
                        'Redução do risco de derrapagem',
                        'Comportamento previsível da motocicleta'
                    ],
                    'economia_estimada' => 'Valor inestimável - sua vida',
                    'prioridade' => 'crítica'
                ]
            ],
            
            'dicas_manutencao' => [
                [
                    'categoria' => 'Verificação Semanal',
                    'frequencia' => 'A cada 7 dias ou 300km',
                    'importancia' => 'crítica',
                    'itens' => [
                        'Verificar pressão com pneus frios',
                        'Inspecionar desgaste visual dos pneus',
                        'Checar se válvulas estão bem fechadas',
                        'Observar rachaduras ou objetos cravados',
                        'Medir profundidade do sulco (mínimo 1,6mm)'
                    ]
                ]
            ],
            
            'alertas_criticos' => [
                [
                    'tipo' => 'crítico',
                    'titulo' => 'Verificação Semanal Obrigatória',
                    'descricao' => 'Motocicletas perdem pressão mais rapidamente que carros.',
                    'consequencia' => 'Pneus com 5 PSI baixos podem causar instabilidade fatal a 80 km/h.'
                ],
                [
                    'tipo' => 'importante',
                    'titulo' => 'Nunca Calibrar com Pneus Quentes',
                    'descricao' => 'Calibrar após pilotagem resulta em subcalibragem perigosa.',
                    'consequencia' => 'Quando esfriam, ficam muito baixos, causando risco de acidente.'
                ]
            ],
            
            'procedimento_calibragem' => [
                'passos' => [
                    [
                        'numero' => 1,
                        'titulo' => 'Preparação',
                        'descricao' => 'Verifique sempre com pneus frios',
                        'detalhes' => [
                            'Moto parada há pelo menos 3 horas',
                            'Ou menos de 2 km rodados',
                            'Preferencialmente pela manhã',
                            'Em local com sombra'
                        ]
                    ],
                    [
                        'numero' => 2,
                        'titulo' => 'Verificação',
                        'descricao' => 'Use calibrador confiável',
                        'detalhes' => [
                            'Prefira calibradores digitais',
                            'Retire a tampa da válvula',
                            'Encaixe firmemente o calibrador',
                            'Anote a pressão atual'
                        ]
                    ],
                    [
                        'numero' => 3,
                        'titulo' => 'Ajuste',
                        'descricao' => 'Calibre conforme necessidade',
                        'detalhes' => [
                            'Dianteiro: ' . $pressureFront . ' PSI (uso normal)',
                            'Traseiro: ' . $pressureRear . ' PSI (uso normal)',
                            'Ajuste conforme condições especiais',
                            'Recoloque as tampas das válvulas'
                        ]
                    ]
                ]
            ]
        ];
    }

    private function generatePickupContent(array $data): array
    {
        $pressureFront = (int) ($data['pressure_empty_front'] ?? 35);
        $pressureRear = (int) ($data['pressure_empty_rear'] ?? 35);
        
        return [
            'especificacoes_carga' => [
                'uso_normal' => [
                    'pressao_dianteira' => $pressureFront,
                    'pressao_traseira' => $pressureRear
                ],
                'carga_maxima' => [
                    'pressao_dianteira' => $pressureFront + 5,
                    'pressao_traseira' => $pressureRear + 10
                ]
            ]
        ];
    }
}