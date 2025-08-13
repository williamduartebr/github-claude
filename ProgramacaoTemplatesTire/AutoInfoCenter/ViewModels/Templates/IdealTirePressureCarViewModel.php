<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;

class IdealTirePressureCarViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait;

    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'ideal_tire_pressure_car';

    /**
     * Processa dados específicos do template de pressão ideal para carros
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications_by_version'] = $this->processTireSpecificationsByVersion($content['especificacoes_por_versao'] ?? []);
        $this->processedData['full_load_table'] = $this->processFullLoadTable($content['tabela_carga_completa'] ?? []);
        $this->processedData['label_location'] = $this->processLabelLocation($content['localizacao_etiqueta'] ?? []);
        $this->processedData['special_conditions'] = $this->processSpecialConditions($content['condicoes_especiais'] ?? []);
        $this->processedData['unit_conversion'] = $this->processUnitConversion($content['conversao_unidades'] ?? []);
        $this->processedData['care_recommendations'] = $this->processCareRecommendations($content['cuidados_recomendacoes'] ?? []);
        $this->processedData['pressure_impact'] = $this->processPressureImpact($content['impacto_pressao'] ?? []);
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';
        
        // OTIMIZADA: Usar dados embarcados primeiro
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['pressure_specifications'] = $this->processPressureSpecifications();
        $this->processedData['tire_specs_embedded'] = $this->processTireSpecificationsEmbedded();
        
        // Dados auxiliares
        $this->processedData['related_topics'] = $this->getRelatedTopics();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
    }

    /**
     * Determina o tipo de veículo para construção da URL da imagem
     */
    protected function getVehicleTypeForImage(): string
    {
        return 'vehicles';
    }

    /**
     * Verifica se é veículo premium
     */
    protected function isPremiumVehicle(): bool
    {
        $make = strtolower($this->article->extracted_entities['marca'] ?? '');
        $premiumBrands = ['audi', 'bmw', 'mercedes', 'mercedes-benz', 'lexus', 'volvo', 'porsche', 'jaguar', 'land rover', 'infiniti', 'acura', 'cadillac', 'tesla'];

        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém segmento do veículo
     */
    protected function getVehicleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');

        return match($category) {
            'suv' => 'SUVs',
            'sedan' => 'Sedans',
            'hatch' => 'Hatches',
            'pickup' => 'Pick-ups',
            'coupe' => 'Coupés',
            'conversivel' => 'Conversíveis',
            'wagon' => 'Station Wagons',
            'minivan' => 'Minivans',
            default => 'Automóveis'
        };
    }

    /**
     * Processa especificações dos pneus por versão OTIMIZADA
     */
    private function processTireSpecificationsByVersion(array $specs): array
    {
        if (empty($specs)) {
            return $this->generateSpecsFromEmbeddedData();
        }

        $processed = [];

        foreach ($specs as $spec) {
            if (!empty($spec['versao'])) {
                $processed[] = [
                    'version' => $spec['versao'],
                    'tire_size' => $spec['medida_pneus'] ?? '',
                    'load_speed_index' => $spec['indice_carga_velocidade'] ?? '',
                    'front_normal' => $spec['pressao_dianteiro_normal'] ?? '',
                    'rear_normal' => $spec['pressao_traseiro_normal'] ?? '',
                    'front_loaded' => $spec['pressao_dianteiro_carregado'] ?? '',
                    'rear_loaded' => $spec['pressao_traseiro_carregado'] ?? '',
                    'css_class' => $this->getVersionCssClass($spec['versao'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera especificações a partir de dados embarcados
     */
    private function generateSpecsFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $tireSpecs = $this->processedData['tire_specs_embedded'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        if (empty($pressureSpecs) || empty($tireSpecs['tire_size'])) {
            return [];
        }

        return [
            [
                'version' => $vehicleInfo['version'] ?: 'Versão Principal',
                'tire_size' => $tireSpecs['tire_size'],
                'load_speed_index' => '',
                'front_normal' => ($pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                'rear_normal' => ($pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                'front_loaded' => ($pressureSpecs['pressure_max_front'] ?? '') . ' PSI',
                'rear_loaded' => ($pressureSpecs['pressure_max_rear'] ?? '') . ' PSI',
                'css_class' => 'bg-white'
            ]
        ];
    }

    /**
     * Processa tabela de carga completa OTIMIZADA
     */
    private function processFullLoadTable(array $table): array
    {
        if (empty($table)) {
            return $this->generateLoadTableFromEmbeddedData();
        }

        $processed = [
            'title' => $table['titulo'] ?? 'Tabela de Carga Completa',
            'description' => $table['descricao'] ?? '',
            'conditions' => []
        ];

        if (!empty($table['condicoes']) && is_array($table['condicoes'])) {
            foreach ($table['condicoes'] as $condition) {
                $processed['conditions'][] = [
                    'version' => $condition['versao'] ?? '',
                    'occupants' => $condition['ocupantes'] ?? '',
                    'luggage' => $condition['bagagem'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'css_class' => $this->getLoadConditionCssClass($condition['ocupantes'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera tabela de carga a partir de dados embarcados
     */
    private function generateLoadTableFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        if (empty($pressureSpecs)) {
            return [];
        }

        return [
            'title' => 'Pressões para Diferentes Condições de Carga',
            'description' => 'Use estas pressões conforme a ocupação e bagagem do veículo.',
            'conditions' => [
                [
                    'version' => 'Uso Normal',
                    'occupants' => '1-2 pessoas',
                    'luggage' => 'Bagagem leve',
                    'front_pressure' => ($pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                    'observation' => 'Uso urbano e rodoviário',
                    'css_class' => 'bg-green-50 border-green-200'
                ],
                [
                    'version' => 'Carga Média',
                    'occupants' => '3-4 pessoas',
                    'luggage' => 'Bagagem moderada',
                    'front_pressure' => ($pressureSpecs['pressure_light_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_light_rear'] ?? $pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                    'observation' => 'Família com bagagem',
                    'css_class' => 'bg-yellow-50 border-yellow-200'
                ],
                [
                    'version' => 'Carga Completa',
                    'occupants' => '4-5 pessoas',
                    'luggage' => 'Porta-malas cheio',
                    'front_pressure' => ($pressureSpecs['pressure_max_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_max_rear'] ?? '') . ' PSI',
                    'observation' => $vehicleInfo['is_electric'] ? 'Peso da bateria considerado' : 'Ideal para viagens',
                    'css_class' => 'bg-blue-50 border-blue-200'
                ]
            ]
        ];
    }

    /**
     * Processa localização da etiqueta OTIMIZADA
     */
    private function processLabelLocation(array $location): array
    {
        if (empty($location)) {
            return $this->generateLabelLocationFromEmbeddedData();
        }

        $processed = [
            'main_location' => $location['localizacao_principal'] ?? '',
            'alternative_locations' => $location['localizacoes_alternativas'] ?? [],
            'description' => $location['descricao'] ?? '',
            'visual_guide' => $location['guia_visual'] ?? [],
            'note' => $location['observacao'] ?? ''
        ];

        return $processed;
    }

    /**
     * Gera localização da etiqueta a partir de dados embarcados
     */
    private function generateLabelLocationFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            'main_location' => 'Porta do motorista (coluna B)',
            'alternative_locations' => [
                'Porta-luvas',
                'Manual do proprietário',
                'Aplicativo da montadora',
                'Site oficial da marca'
            ],
            'description' => 'A etiqueta oficial está localizada na coluna central da porta do motorista, próximo à fechadura.',
            'visual_guide' => [
                'Abra completamente a porta do motorista',
                'Procure na coluna central (pilar B)',
                'Etiqueta branca com informações em português',
                'Contém pressões para uso normal e carga completa'
            ],
            'note' => 'Alguns veículos premium possuem as informações também no painel digital.'
        ];
    }

    /**
     * Processa condições especiais de uso OTIMIZADA
     */
    private function processSpecialConditions(array $conditions): array
    {
        if (empty($conditions)) {
            return $this->generateConditionsFromEmbeddedData();
        }

        $processed = [];

        foreach ($conditions as $condition) {
            if (!empty($condition['condicao'])) {
                $processed[] = [
                    'condition' => $condition['condicao'],
                    'recommended_adjustment' => $condition['ajuste_recomendado'] ?? '',
                    'application' => $condition['aplicacao'] ?? '',
                    'justification' => $condition['justificativa'] ?? '',
                    'icon_class' => $this->getConditionIconClass($condition['condicao']),
                    'has_load_table_reference' => $this->hasLoadTableReference($condition['ajuste_recomendado'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera condições especiais de dados embarcados
     */
    private function generateConditionsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $conditions = [];

        // Condição para viagens
        $conditions[] = [
            'condition' => 'Viagens em Rodovias',
            'recommended_adjustment' => '+2 PSI',
            'application' => 'Velocidades sustentadas acima de 110 km/h',
            'justification' => 'Compensa o aquecimento dos pneus em altas velocidades.',
            'icon_class' => 'trending-up',
            'has_load_table_reference' => false
        ];

        // Condição para carga máxima
        if ($this->hasLoadTableData()) {
            $conditions[] = [
                'condition' => 'Carga Máxima',
                'recommended_adjustment' => 'Ver tabela carga completa',
                'application' => '4 ou 5 passageiros e bagagem',
                'justification' => 'Utilize sempre os valores da coluna carga completa para manter estabilidade.',
                'icon_class' => 'package',
                'has_load_table_reference' => true
            ];
        }

        // Condição específica para elétricos
        if ($vehicleInfo['is_electric'] ?? false) {
            $conditions[] = [
                'condition' => 'Modo Eco (Elétrico)',
                'recommended_adjustment' => '+1 PSI',
                'application' => 'Para maximizar autonomia da bateria',
                'justification' => 'Reduz resistência ao rolamento, aumentando eficiência energética.',
                'icon_class' => 'battery',
                'has_load_table_reference' => false
            ];
        }

        // Condição para pneus novos
        $conditions[] = [
            'condition' => 'Pneus Novos',
            'recommended_adjustment' => 'Pressão padrão',
            'application' => 'Primeiros 1000 km',
            'justification' => 'Permita o amaciamento natural sem sobrepressão.',
            'icon_class' => 'refresh-cw',
            'has_load_table_reference' => false
        ];

        return $conditions;
    }

    /**
     * Processa conversão de unidades OTIMIZADA
     */
    private function processUnitConversion(array $conversion): array
    {
        if (empty($conversion)) {
            return $this->generateUnitConversionFromEmbeddedData();
        }

        $processed = [
            'conversion_table' => [],
            'reference_pressure' => $conversion['pressao_referencia'] ?? '',
            'observation' => $conversion['observacao'] ?? ''
        ];

        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? '',
                    'is_recommended' => $this->isRecommendedPressure($row['psi'] ?? ''),
                    'highlight_class' => $this->getPressureHighlightClass($row['psi'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera conversão de unidades a partir de dados embarcados
     */
    private function generateUnitConversionFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        
        $pressures = array_filter([
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ]);

        if (empty($pressures)) {
            return [];
        }

        $conversionTable = [];
        $uniquePressures = array_unique($pressures);
        sort($uniquePressures);

        foreach ($uniquePressures as $psi) {
            $conversionTable[] = [
                'psi' => $psi,
                'kgf_cm2' => round($psi * 0.070307, 2),
                'bar' => round($psi * 0.0689476, 2),
                'is_recommended' => true,
                'highlight_class' => 'highlight-pressure'
            ];
        }

        return [
            'conversion_table' => $conversionTable,
            'reference_pressure' => $pressureSpecs['pressure_display'] ?? '',
            'observation' => 'PSI é a unidade padrão no Brasil. Conversões aproximadas.'
        ];
    }

    /**
     * Processa cuidados e recomendações OTIMIZADA
     */
    private function processCareRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return $this->generateCareRecommendationsFromEmbeddedData();
        }

        $processed = [];

        foreach ($recommendations as $category => $recommendation) {
            if (!empty($recommendation['titulo'])) {
                $processed[] = [
                    'category' => $category,
                    'title' => $recommendation['titulo'],
                    'description' => $recommendation['descricao'] ?? '',
                    'frequency' => $recommendation['frequencia'] ?? '',
                    'procedures' => $recommendation['procedimentos'] ?? [],
                    'tools_needed' => $recommendation['ferramentas_necessarias'] ?? [],
                    'safety_tips' => $recommendation['dicas_seguranca'] ?? [],
                    'icon_class' => $this->getCareRecommendationIconClass($category),
                    'color_class' => $this->getCareRecommendationColorClass($category)
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera cuidados e recomendações a partir de dados embarcados
     */
    private function generateCareRecommendationsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            [
                'category' => 'verificacao_mensal',
                'title' => 'Verificação Mensal',
                'description' => 'Rotina básica de manutenção preventiva.',
                'frequency' => 'Mensal ou a cada 1.000 km',
                'procedures' => [
                    'Verificar pressão com pneus frios',
                    'Inspecionar visualmente os pneus',
                    'Verificar profundidade dos sulcos',
                    'Observar desgaste irregular',
                    'Incluir o estepe na verificação'
                ],
                'tools_needed' => ['Calibrador', 'Moedas para medição de sulco'],
                'safety_tips' => [
                    'Sempre verificar com pneus frios',
                    'Usar calibrador confiável',
                    'Verificar todas as rodas, incluindo estepe'
                ],
                'icon_class' => 'calendar',
                'color_class' => 'from-blue-100 to-blue-200'
            ],
            [
                'category' => 'cuidados_especiais',
                'title' => 'Cuidados Especiais',
                'description' => 'Atenção extra para maximizar segurança e durabilidade.',
                'frequency' => 'Conforme necessidade',
                'procedures' => [
                    'Calibrar antes de viagens longas',
                    'Ajustar pressão conforme carga',
                    'Verificar após mudanças bruscas de temperatura',
                    $vehicleInfo['has_tpms'] ? 'Monitorar alertas do TPMS' : 'Atenção redobrada sem TPMS',
                    $vehicleInfo['is_electric'] ? 'Verificar pressão para máxima autonomia' : 'Otimizar para economia de combustível'
                ],
                'tools_needed' => ['Calibrador digital', 'Compressor portátil'],
                'safety_tips' => [
                    'Nunca exceder pressões máximas',
                    'Atenção especial em pneus run-flat',
                    'Verificar mais frequentemente no verão'
                ],
                'icon_class' => 'shield',
                'color_class' => 'from-green-100 to-green-200'
            ]
        ];
    }

    /**
     * Processa impacto da pressão OTIMIZADA
     */
    private function processPressureImpact(array $impact): array
    {
        if (empty($impact)) {
            return $this->generateImpactFromEmbeddedData();
        }

        $processed = [];

        foreach ($impact as $key => $impactData) {
            if (!empty($impactData['tipo'])) {
                $processed[] = [
                    'type' => $key,
                    'title' => $impactData['titulo'] ?? '',
                    'items' => $impactData['items'] ?? $impactData['beneficios'] ?? [],
                    'color' => $this->getImpactColor($key),
                    'icon_class' => $this->getImpactIconClass($key),
                    'css_class' => $this->getImpactCssClass($key)
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera impacto específico baseado nos dados embarcados
     */
    private function generateImpactFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        $impacts = [
            [
                'type' => 'subcalibrado',
                'title' => 'Pneu Subcalibrado',
                'items' => [
                    'Maior consumo de combustível (+10% a 15%)',
                    'Desgaste acelerado nas bordas',
                    'Menor estabilidade em curvas',
                    'Alto risco de estouro no calor brasileiro'
                ],
                'color' => 'red',
                'icon_class' => 'minus',
                'css_class' => 'from-red-100 to-red-200'
            ],
            [
                'type' => 'ideal',
                'title' => 'Calibragem Correta (PSI)',
                'items' => [
                    $vehicleInfo['is_electric'] ? 'Autonomia otimizada da bateria' : 'Consumo otimizado de combustível',
                    'Desgaste uniforme e vida útil máxima',
                    'Aderência e comportamento previsíveis',
                    'Distâncias de frenagem otimizadas'
                ],
                'color' => 'green',
                'icon_class' => 'check',
                'css_class' => 'from-green-100 to-green-200'
            ],
            [
                'type' => 'sobrecalibrado',
                'title' => 'Pneu Sobrecalibrado',
                'items' => [
                    'Desgaste excessivo no centro',
                    'Menor área de contato com o solo',
                    'Redução na aderência em piso molhado',
                    'Maior rigidez, reduzindo o conforto'
                ],
                'color' => 'amber',
                'icon_class' => 'alert-triangle',
                'css_class' => 'from-amber-100 to-amber-200'
            ]
        ];

        return $impacts;
    }

    /**
     * Obtém tópicos relacionados OTIMIZADA
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        $topics = [];

        // Tópicos gerais de manutenção
        $topics[] = [
            'title' => 'Troca de Óleo para ' . $vehicleInfo['make'],
            'url' => '/info/troca-oleo-' . strtolower($vehicleInfo['make']),
            'description' => 'Intervalos e especificações para troca de óleo'
        ];

        // Tópicos específicos por tipo de veículo
        if ($vehicleInfo['is_electric'] ?? false) {
            $topics[] = [
                'title' => 'Manutenção de Carros Elétricos',
                'url' => '/info/manutencao-carros-eletricos',
                'description' => 'Cuidados específicos para veículos elétricos'
            ];
        }

        if ($vehicleInfo['has_tpms'] ?? false) {
            $topics[] = [
                'title' => 'Como Funciona o Sistema TPMS',
                'url' => '/info/sistema-tpms-monitoramento-pressao',
                'description' => 'Entenda o sistema de monitoramento de pressão'
            ];
        }

        // Tópicos por categoria
        $segment = $vehicleInfo['segment'] ?? '';
        if (str_contains(strtolower($segment), 'suv')) {
            $topics[] = [
                'title' => 'Pneus para SUVs - Guia Completo',
                'url' => '/info/pneus-suvs-guia-completo',
                'description' => 'Escolha e manutenção de pneus para SUVs'
            ];
        }

        $topics[] = [
            'title' => 'Quando Trocar os Pneus',
            'url' => '/info/quando-trocar-pneus',
            'description' => 'Sinais de que é hora de trocar os pneus'
        ];

        return $topics;
    }

    /**
     * Métodos auxiliares para classes CSS e ícones
     */

    private function getVersionCssClass(string $version): string
    {
        $lowercaseVersion = strtolower($version);
        
        if (str_contains($lowercaseVersion, 'sport') || str_contains($lowercaseVersion, 'gts')) {
            return 'bg-red-50 border-red-200';
        }
        
        if (str_contains($lowercaseVersion, 'luxury') || str_contains($lowercaseVersion, 'premium')) {
            return 'bg-purple-50 border-purple-200';
        }
        
        return 'bg-gray-50 border-gray-200';
    }

    private function getLoadConditionCssClass(string $occupants): string
    {
        if (str_contains($occupants, '1-2')) {
            return 'bg-green-50 border-green-200';
        }
        
        if (str_contains($occupants, '3-4')) {
            return 'bg-yellow-50 border-yellow-200';
        }
        
        return 'bg-blue-50 border-blue-200';
    }

    private function getConditionIconClass(string $condition): string
    {
        $lowercaseCondition = strtolower($condition);
        
        if (str_contains($lowercaseCondition, 'viagem') || str_contains($lowercaseCondition, 'rodovia')) {
            return 'trending-up';
        }
        
        if (str_contains($lowercaseCondition, 'carga')) {
            return 'package';
        }
        
        if (str_contains($lowercaseCondition, 'elétrico') || str_contains($lowercaseCondition, 'eco')) {
            return 'battery';
        }
        
        if (str_contains($lowercaseCondition, 'novo')) {
            return 'refresh-cw';
        }
        
        return 'settings';
    }

    private function hasLoadTableReference(string $adjustment): bool
    {
        return str_contains(strtolower($adjustment), 'tabela');
    }

    private function hasLoadTableData(): bool
    {
        return !empty($this->processedData['full_load_table']['conditions']);
    }

    private function isRecommendedPressure(string $psi): bool
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $recommendedPressures = [
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ];

        return in_array((int)$psi, array_filter($recommendedPressures));
    }

    private function getPressureHighlightClass(string $psi): string
    {
        return $this->isRecommendedPressure($psi) ? 'highlight-pressure' : '';
    }

    private function getCareRecommendationIconClass(string $category): string
    {
        $iconMap = [
            'verificacao_mensal' => 'calendar',
            'cuidados_especiais' => 'shield',
            'ferramentas' => 'tool',
            'seguranca' => 'alert-triangle'
        ];

        return $iconMap[$category] ?? 'wrench';
    }

    private function getCareRecommendationColorClass(string $category): string
    {
        $colorMap = [
            'verificacao_mensal' => 'from-blue-100 to-blue-200',
            'cuidados_especiais' => 'from-green-100 to-green-200',
            'ferramentas' => 'from-purple-100 to-purple-200',
            'seguranca' => 'from-red-100 to-red-200'
        ];

        return $colorMap[$category] ?? 'from-gray-100 to-gray-200';
    }

    private function getImpactColor(string $type): string
    {
        $colorMap = [
            'subcalibrado' => 'red',
            'ideal' => 'green',
            'sobrecalibrado' => 'amber',
            'correto' => 'green'
        ];

        return $colorMap[$type] ?? 'gray';
    }

    private function getImpactIconClass(string $type): string
    {
        $iconMap = [
            'subcalibrado' => 'minus',
            'ideal' => 'check',
            'sobrecalibrado' => 'alert-triangle',
            'correto' => 'check'
        ];

        return $iconMap[$type] ?? 'info';
    }

    private function getImpactCssClass(string $type): string
    {
        $color = $this->getImpactColor($type);
        return "from-{$color}-100 to-{$color}-200";
    }

    /**
     * Processa dados SEO específicos OTIMIZADA
     */
    private function processSeoData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $seoData = $this->article->seo_data ?? [];

        $pressureDisplay = $pressureSpecs['pressure_display'] ?? '';
        
        return [
            'title' => $seoData['page_title'] ?? "Pressão Ideal para Pneus do {$vehicleInfo['full_name']} – Tabela Completa",
            'meta_description' => $seoData['meta_description'] ?? "Tabela completa de pressão dos pneus do {$vehicleInfo['full_name']}. {$pressureDisplay}. Conversões e dicas de calibragem para o Brasil.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "pressão ideal pneus {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Pressão Ideal para Pneus do {$vehicleInfo['full_name']} – Tabela Completa",
            'h2_tags' => $seoData['h2_tags'] ?? [
                'Especificações dos Pneus Originais por Versão',
                'Tabela de Pressão dos Pneus (PSI - Padrão Brasileiro)',
                'Tabela de Carga Completa',
                'Localização da Etiqueta de Pressão',
                'Ajustes para Condições Especiais',
                'Conversão de Unidades - PSI (Padrão Brasileiro)',
                'Cuidados e Recomendações',
                'Impacto da Pressão no Desempenho',
                'Perguntas Frequentes'
            ],
            'og_title' => $seoData['og_title'] ?? "Pressão Ideal para Pneus do {$vehicleInfo['full_name']} – Tabela Oficial",
            'og_description' => $seoData['og_description'] ?? "Tabela completa com pressões oficiais em PSI para {$vehicleInfo['full_name']}. {$pressureDisplay}.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org OTIMIZADA
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => "Pressão Ideal para Pneus do {$vehicleFullName}",
            'description' => "Tabela completa de pressões ideais para os pneus do {$vehicleFullName}, incluindo todas as versões e condições de uso.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/vehicles/default-car.jpg',
                'width' => 1200,
                'height' => 630
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'url' => 'https://mercadoveiculos.com.br'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos.png'
                ]
            ],
            'datePublished' => $this->article->created_at?->toISOString(),
            'dateModified' => $this->article->updated_at?->toISOString(),
            'about' => [
                '@type' => 'Thing',
                'name' => 'Calibragem de Pneus',
                'description' => 'Pressões ideais para pneus automotivos'
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCanonicalUrl()
            ]
        ];

        if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
            $vehicleType = $vehicleInfo['is_electric'] ? 'Vehicle' : 'Car';
            
            $structuredData['mainEntity'] = [
                '@type' => $vehicleType,
                'name' => 'Pressão ideal para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $vehicleInfo['make']
                ],
                'model' => $vehicleInfo['model']
            ];

            if (!empty($vehicleInfo['year'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleInfo['year'];
            }

            if ($vehicleInfo['is_electric']) {
                $structuredData['mainEntity']['fuelType'] = 'Electric';
            } elseif ($vehicleInfo['is_hybrid']) {
                $structuredData['mainEntity']['fuelType'] = 'Hybrid';
            } elseif (!empty($vehicleInfo['fuel'])) {
                $structuredData['mainEntity']['fuelType'] = $vehicleInfo['fuel'];
            }
        }

        // Adiciona informações específicas sobre pressão
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        if (!empty($pressureSpecs['pressure_display'])) {
            $structuredData['mentions'] = [
                '@type' => 'Thing',
                'name' => 'Pressão dos Pneus',
                'description' => $pressureSpecs['pressure_display']
            ];
        }

        // Adiciona informações sobre TPMS se disponível
        if ($vehicleInfo['has_tpms'] ?? false) {
            $structuredData['mentions'][] = [
                '@type' => 'Thing',
                'name' => 'Sistema TPMS',
                'description' => 'Sistema de Monitoramento de Pressão dos Pneus'
            ];
        }

        return $structuredData;
    }

    /**
     * Verifica se é veículo elétrico
     */
    private function isElectricVehicle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_electric'] ?? false;
    }

    /**
     * Verifica se é veículo híbrido
     */
    private function isHybridVehicle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_hybrid'] ?? false;
    }

    /**
     * Verifica se tem sistema TPMS
     */
    public function hasTpmsSystem(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['has_tpms'] ?? false;
    }

    /**
     * Verifica se é veículo premium
     */
    private function isPremiumVehicleFromData(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_premium'] ?? false;
    }

    /**
     * Obtém categoria do veículo
     */
    private function getVehicleCategory(): string
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['category'] ?? '';
    }

    /**
     * Obtém segmento do veículo
     */
    private function getVehicleSegmentFromData(): string
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['segment'] ?? '';
    }

    /**
     * Verifica se tem estepe
     */
    private function hasSpareTire(): bool
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        return $pressureSpecs['has_spare_tire'] ?? false;
    }

    /**
     * Obtém pressão do estepe
     */
    private function getSpareTirePressure(): ?int
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        return $pressureSpecs['pressure_spare'] ?? null;
    }

    /**
     * Gera alertas específicos para carros elétricos
     */
    private function generateElectricVehicleAlerts(): array
    {
        if (!$this->isElectricVehicle()) {
            return [];
        }

        return [
            [
                'type' => 'info',
                'title' => 'Veículo Elétrico - Pressão Otimizada',
                'description' => 'Pressão correta maximiza a autonomia da bateria.',
                'items' => [
                    'Cada PSI incorreto reduz autonomia',
                    'Verificar pressão semanalmente',
                    'Considerar peso extra da bateria',
                    'Usar modo Eco quando disponível'
                ]
            ]
        ];
    }

    /**
     * Gera alertas específicos para sistema TPMS
     */
    private function generateTpmsAlerts(): array
    {
        if (!$this->hasTpmsSystem()) {
            return [];
        }

        return [
            [
                'type' => 'info',
                'title' => 'Sistema TPMS Ativo',
                'description' => 'Seu veículo monitora a pressão automaticamente.',
                'items' => [
                    'Alertas aparecem no painel',
                    'Não substitui verificação manual',
                    'Reset pode ser necessário após calibragem',
                    'Consulte manual para procedimentos'
                ]
            ]
        ];
    }

    /**
     * Verifica se propriedade existe
     */
    public function __isset(string $property): bool
    {
        return isset($this->processedData[$property]);
    }

    /**
     * Obter propriedade específica
     */
    public function __get(string $property)
    {
        return $this->processedData[$property] ?? null;
    }

    /**
     * Obter todos os dados processados
     */
    public function toArray(): array
    {
        return $this->processedData;
    }
  
}