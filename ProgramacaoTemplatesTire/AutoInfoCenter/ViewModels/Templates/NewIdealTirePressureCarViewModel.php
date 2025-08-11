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
        $this->processedData['related_topics'] = $this->getRelatedTopics();
        
        // Dados auxiliares usando o trait OTIMIZADO
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['pressure_specifications'] = $this->processPressureSpecifications();
        $this->processedData['tire_specs_embedded'] = $this->processTireSpecifications();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
        
        // Seções condicionais baseadas em flags
        $this->processedData['sections_visible'] = $this->determineSectionsVisibility();
    }

    /**
     * Determina quais seções devem ser visíveis baseado nos flags
     */
    private function determineSectionsVisibility(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $pressureSpecs = $this->processedData['pressure_specifications'];

        return [
            'electric_features' => $vehicleInfo['is_electric'],
            'hybrid_modes' => $vehicleInfo['is_hybrid'],
            'tpms_section' => $vehicleInfo['has_tpms'],
            'spare_tire_section' => $pressureSpecs['has_spare_tire'] ?? false,
            'oil_recommendations' => !$vehicleInfo['is_electric'] && !empty($vehicleInfo['recommended_oil']),
            'premium_features' => $vehicleInfo['is_premium'],
            'load_table_reference' => $this->hasLoadTableData(),
            'motorcycle_warnings' => false // Para carros sempre false
        ];
    }

    /**
     * Verifica se tem dados de tabela de carga
     */
    private function hasLoadTableData(): bool
    {
        return !empty($this->processedData['full_load_table']['conditions']);
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
        $premiumBrands = ['audi', 'bmw', 'mercedes', 'mercedes-benz', 'lexus', 'volvo', 'porsche', 'tesla', 'peugeot'];

        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém segmento do veículo
     */
    protected function getVehicleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');

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
     * Processa tabela de carga completa OTIMIZADA
     */
    private function processFullLoadTable(array $table): array
    {
        if (empty($table)) {
            // Tenta gerar da dados embarcados se não tem dados de conteúdo
            return $this->generateLoadTableFromEmbeddedData();
        }

        $processed = [
            'title' => $table['titulo'] ?? 'Pressões para Carga Completa',
            'description' => $table['descricao'] ?? 'Valores recomendados para veículo carregado',
            'conditions' => []
        ];

        if (!empty($table['condicoes']) && is_array($table['condicoes'])) {
            foreach ($table['condicoes'] as $condition) {
                if (!empty($condition['versao'])) {
                    $processed['conditions'][] = [
                        'version' => $condition['versao'],
                        'occupants' => $condition['ocupantes'] ?? '',
                        'luggage' => $condition['bagagem'] ?? '',
                        'front_pressure' => $condition['pressao_dianteira'] ?? '',
                        'rear_pressure' => $condition['pressao_traseira'] ?? '',
                        'observation' => $condition['observacao'] ?? '',
                        'css_class' => $this->getLoadConditionCssClass($condition['versao'])
                    ];
                }
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

        if (empty($pressureSpecs) || empty($pressureSpecs['loaded_pressure_display'])) {
            return [];
        }

        return [
            'title' => 'Pressões para Carga Completa',
            'description' => 'Valores recomendados quando o veículo estiver com lotação máxima e bagagem',
            'conditions' => [
                [
                    'version' => 'Todas as versões',
                    'occupants' => '4-5 pessoas',
                    'luggage' => 'Porta-malas cheio',
                    'front_pressure' => $pressureSpecs['pressure_max_front'] . ' PSI' ?? '',
                    'rear_pressure' => $pressureSpecs['pressure_max_rear'] . ' PSI' ?? '',
                    'observation' => $vehicleInfo['is_electric'] ? 'Peso da bateria considerado' : 'Ideal para viagens',
                    'css_class' => 'bg-blue-50 border-blue-200'
                ]
            ]
        ];
    }

    /**
     * Processa especificações dos pneus por versão OTIMIZADA
     */
    private function processTireSpecificationsByVersion(array $specs): array
    {
        if (empty($specs)) {
            // Gera da dados embarcados
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
            'recommended_adjustment' => '+3 PSI',
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
        if ($vehicleInfo['is_electric']) {
            $conditions[] = [
                'condition' => 'Modo ECO Elétrico',
                'recommended_adjustment' => '+2 PSI',
                'application' => 'Para maximizar autonomia da bateria',
                'justification' => 'Reduz resistência ao rolamento, aumentando eficiência energética.',
                'icon_class' => 'dollar-sign',
                'has_load_table_reference' => false
            ];
        }

        return $conditions;
    }

    /**
     * Processa localização da etiqueta OTIMIZADA
     */
    private function processLabelLocation(array $location): array
    {
        if (empty($location)) {
            return $this->generateDefaultLabelLocation();
        }

        return [
            'main_location' => $location['local_principal'] ?? '',
            'description' => $location['descricao'] ?? '',
            'alternative_locations' => $location['locais_alternativos'] ?? [],
            'note' => $location['observacao'] ?? '',
            'visual_guide' => $this->generateVisualGuide($location)
        ];
    }

    /**
     * Gera localização padrão da etiqueta
     */
    private function generateDefaultLabelLocation(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            'main_location' => 'Coluna da porta do motorista',
            'description' => "A etiqueta oficial de pressão dos pneus do {$vehicleInfo['full_name']} está localizada na coluna da porta do motorista, visível quando a porta está aberta.",
            'alternative_locations' => [
                'Manual do proprietário na seção "Especificações Técnicas"',
                'Tampa do tanque de combustível (alguns casos)',
                'Porta-luvas (alguns casos)'
            ],
            'note' => 'Sempre utilize os valores oficiais da etiqueta em PSI como referência principal.',
            'visual_guide' => $this->generateVisualGuide([])
        ];
    }

    /**
     * Processa tabela de conversão de unidades OTIMIZADA
     */
    private function processUnitConversion(array $conversion): array
    {
        if (empty($conversion)) {
            return $this->generateConversionFromEmbeddedData();
        }

        $processed = [
            'conversion_table' => [],
            'formulas' => [],
            'note' => $conversion['observacao'] ?? ''
        ];

        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? ''
                ];
            }
        }

        if (!empty($conversion['formulas'])) {
            $processed['formulas'] = [
                'psi_para_kgf' => $conversion['formulas']['psi_para_kgf'] ?? '',
                'kgf_para_psi' => $conversion['formulas']['kgf_para_psi'] ?? '',
                'psi_para_bar' => $conversion['formulas']['psi_para_bar'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Gera tabela de conversão a partir de dados embarcados
     */
    private function generateConversionFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        
        // Pressões típicas baseadas nos dados embarcados
        $basePressures = array_filter([
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ]);

        if (empty($basePressures)) {
            $basePressures = [28, 30, 32, 35, 38]; // Valores padrão
        }

        $table = [];
        foreach (array_unique($basePressures) as $psi) {
            $table[] = [
                'psi' => $psi,
                'kgf_cm2' => round($psi / 14.22, 2),
                'bar' => round($psi / 14.5, 2)
            ];
        }

        return [
            'conversion_table' => $table,
            'formulas' => [
                'psi_para_kgf' => 'kgf/cm² = PSI ÷ 14,22',
                'kgf_para_psi' => 'PSI = kgf/cm² × 14,22',
                'psi_para_bar' => 'Bar = PSI ÷ 14,5'
            ],
            'note' => 'No Brasil, PSI é o padrão usado nos postos de combustível e calibradores.'
        ];
    }

    /**
     * Processa cuidados e recomendações OTIMIZADA
     */
    private function processCareRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return $this->generateCareFromEmbeddedData();
        }

        $processed = [];

        foreach ($recommendations as $rec) {
            if (!empty($rec['categoria'])) {
                $processed[] = [
                    'category' => $rec['categoria'],
                    'description' => $rec['descricao'] ?? '',
                    'icon_class' => $this->getCareIconClass($rec['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera cuidados específicos baseados nos dados embarcados
     */
    private function generateCareFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $cares = [];

        // Verificação padrão
        $cares[] = [
            'category' => 'Verificação Mensal',
            'description' => 'Verifique a pressão dos pneus pelo menos uma vez por mês e sempre antes de viagens longas.',
            'icon_class' => 'clock'
        ];

        // Pneus frios
        $cares[] = [
            'category' => 'Pneus Frios',
            'description' => 'Calibre sempre com os pneus frios, preferencialmente pela manhã.',
            'icon_class' => 'thermometer'
        ];

        // Cuidados específicos para elétricos
        if ($vehicleInfo['is_electric']) {
            $cares[] = [
                'category' => 'Veículos Elétricos',
                'description' => 'Mantenha pressão otimizada para maximizar autonomia da bateria. Verificação quinzenal recomendada.',
                'icon_class' => 'zap'
            ];
        }

        // TPMS se disponível
        if ($vehicleInfo['has_tpms']) {
            $cares[] = [
                'category' => 'Sistema TPMS',
                'description' => 'Após calibragem, aguarde alguns quilômetros para o sistema TPMS se recalibrar automaticamente.',
                'icon_class' => 'tool'
            ];
        }

        // Rodízio
        $cares[] = [
            'category' => 'Rodízio de Pneus',
            'description' => 'Faça o rodízio a cada 10.000 km. Após o rodízio, ajuste a pressão conforme a nova posição.',
            'icon_class' => 'rotate-cw'
        ];

        return $cares;
    }

    /**
     * Processa impacto da pressão no desempenho
     */
    private function processPressureImpact(array $impact): array
    {
        if (empty($impact)) {
            return $this->generateImpactFromEmbeddedData();
        }

        $processed = [];

        foreach ($impact as $key => $impactData) {
            if (!empty($impactData['titulo'])) {
                $processed[] = [
                    'type' => $key,
                    'title' => $impactData['titulo'],
                    'items' => $impactData['problemas'] ?? $impactData['beneficios'] ?? [],
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
        $make = strtolower($vehicleInfo['make']);
        $model = strtolower(str_replace(' ', '-', $vehicleInfo['model']));
        $year = $vehicleInfo['year'];

        $topics = [
            [
                'title' => "Melhores Pneus para {$vehicleInfo['make']} {$vehicleInfo['model']} {$year}",
                'description' => 'Descubra os pneus ideais para seu veículo',
                'url' => "/info/pneus-recomendados/{$make}-{$model}-{$year}/"
            ],
            [
                'title' => "Guia de Rodízio de Pneus do {$vehicleInfo['make']} {$vehicleInfo['model']}",
                'description' => 'Como fazer o rodízio correto dos pneus',
                'url' => "/info/rodizio-pneus/{$make}-{$model}-{$year}/"
            ]
        ];

        // Tópico específico para elétricos
        if ($vehicleInfo['is_electric']) {
            $topics[] = [
                'title' => "Eficiência Energética do {$vehicleInfo['make']} {$vehicleInfo['model']} {$year}",
                'description' => 'Dicas para maximizar autonomia da bateria',
                'url' => "/info/eficiencia-eletrico/{$make}-{$model}-{$year}/"
            ];
        } else {
            $topics[] = [
                'title' => "Consumo Real do {$vehicleInfo['make']} {$vehicleInfo['model']} {$year}",
                'description' => 'Dados reais de consumo de combustível',
                'url' => "/info/consumo/{$make}-{$model}-{$year}/"
            ];
        }

        $topics[] = [
            'title' => "Cronograma de Revisões do {$vehicleInfo['make']} {$vehicleInfo['model']}",
            'description' => 'Plano de manutenção preventiva',
            'url' => "/info/revisoes/{$make}-{$model}-{$year}/"
        ];

        return $topics;
    }

    /**
     * Verifica se a condição referencia a tabela de carga
     */
    private function hasLoadTableReference(string $adjustment): bool
    {
        return str_contains(strtolower($adjustment), 'tabela') && 
               str_contains(strtolower($adjustment), 'carga');
    }

    /**
     * Obtém classe CSS para condições de carga
     */
    private function getLoadConditionCssClass(string $version): string
    {
        $cleanVersion = strtolower($version);
        
        if (str_contains($cleanVersion, 'mpi')) {
            return 'bg-blue-50 border-blue-200';
        }
        
        if (str_contains($cleanVersion, 'gts')) {
            return 'bg-red-50 border-red-200';
        }
        
        return 'bg-green-50 border-green-200';
    }

    /**
     * Obtém classe CSS para versão do veículo
     */
    private function getVersionCssClass(string $version): string
    {
        return ($this->getVersionIndex($version) % 2 === 0) ? 'bg-white' : 'bg-gray-50';
    }

    /**
     * Obtém índice da versão para CSS alternado
     */
    private function getVersionIndex(string $version): int
    {
        static $versionIndex = 0;
        return $versionIndex++;
    }

    /**
     * Obtém classe de ícone para condição especial
     */
    private function getConditionIconClass(string $condition): string
    {
        $condition = strtolower($condition);

        if (str_contains($condition, 'rodovia') || str_contains($condition, 'viagem')) {
            return 'trending-up';
        }

        if (str_contains($condition, 'carga') || str_contains($condition, 'máxima')) {
            return 'package';
        }

        if (str_contains($condition, 'econômica') || str_contains($condition, 'economia') || str_contains($condition, 'eco')) {
            return 'dollar-sign';
        }

        return 'info';
    }

    /**
     * Gera guia visual para localização
     */
    private function generateVisualGuide(array $location): array
    {
        return [
            'main_step' => [
                'title' => 'Localização Principal',
                'description' => $location['local_principal'] ?? 'Coluna da porta do motorista',
                'icon' => 'map-pin'
            ],
            'verification_steps' => [
                'Abra a porta do motorista',
                'Olhe na coluna da porta',
                'Procure por etiqueta branca com tabela de pressões'
            ]
        ];
    }

    /**
     * Obtém classe de ícone para cuidados
     */
    private function getCareIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'verificação') || str_contains($category, 'mensal')) {
            return 'clock';
        }

        if (str_contains($category, 'frios') || str_contains($category, 'temperatura')) {
            return 'thermometer';
        }

        if (str_contains($category, 'calibradores') || str_contains($category, 'equipamento') || str_contains($category, 'tpms')) {
            return 'tool';
        }

        if (str_contains($category, 'elétrico') || str_contains($category, 'bateria')) {
            return 'zap';
        }

        if (str_contains($category, 'calor') || str_contains($category, 'sol')) {
            return 'sun';
        }

        if (str_contains($category, 'chuva') || str_contains($category, 'poeira')) {
            return 'cloud-rain';
        }

        if (str_contains($category, 'rodízio')) {
            return 'rotate-cw';
        }

        return 'info';
    }

    /**
     * Obtém cor para tipo de impacto
     */
    private function getImpactColor(string $type): string
    {
        $colorMap = [
            'subcalibrado' => 'red',
            'ideal' => 'green',
            'sobrecalibrado' => 'amber'
        ];

        return $colorMap[$type] ?? 'gray';
    }

    /**
     * Obtém classe de ícone para impacto
     */
    private function getImpactIconClass(string $type): string
    {
        $iconMap = [
            'subcalibrado' => 'minus',
            'ideal' => 'check',
            'sobrecalibrado' => 'alert-triangle'
        ];

        return $iconMap[$type] ?? 'info';
    }

    /**
     * Obtém classe CSS para impacto
     */
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
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => "Pressão Ideal para Pneus do {$vehicleInfo['full_name']} – Tabela Oficial",
            'og_description' => "Tabela completa com pressões oficiais em PSI para {$vehicleInfo['full_name']}. {$pressureDisplay}.",
            'og_image' => $vehicleInfo['image_url'] ?? '',
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
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/pressao-ideal-carro.jpg',
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
            ]
        ];

        if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
            $vehicleType = $vehicleInfo['is_electric'] ? 'Vehicle' : 'Car';
            
            $structuredData['mainEntity'] = [
                '@type' => $vehicleType,
                'name' => 'Pressão ideal para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
                'brand' => $vehicleInfo['make'],
                'model' => $vehicleInfo['model']
            ];

            if (!empty($vehicleInfo['year'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleInfo['year'];
            }

            if ($vehicleInfo['is_electric']) {
                $structuredData['mainEntity']['fuelType'] = 'Electric';
            } elseif ($vehicleInfo['is_hybrid']) {
                $structuredData['mainEntity']['fuelType'] = 'Hybrid';
            }
        }

        return $structuredData;
    }

    /**
     * Verifica se propriedade existe
     */
    public function __isset(string $property): bool
    {
        return isset($this->processedData[$property]);
    }

    /**
     * Obter todos os dados processados
     */
    public function toArray(): array
    {
        return $this->processedData;
    }
}