<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;

class TirePressureGuideCarViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait;

    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_pressure_guide_car';

    /**
     * Processa dados específicos do template de guia de calibragem para carros
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications'] = $this->processTireSpecifications($content['especificacoes_oficiais'] ?? []);
        $this->processedData['pressure_table'] = $this->processPressureTable($content['tabela_pressoes'] ?? []);
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);
        $this->processedData['tpms_system'] = $this->processTpmsSystem($content['sistema_tpms'] ?? []);
        $this->processedData['calibration_impacts'] = $this->processCalibrationImpacts($content['impactos_calibragem'] ?? []);
        $this->processedData['maintenance_tips'] = $this->processMaintenanceTips($content['dicas_manutencao'] ?? []);
        $this->processedData['safety_alerts'] = $this->processSafetyAlerts($content['alertas_seguranca'] ?? []);
        $this->processedData['usage_recommendations'] = $this->processUsageRecommendations($content['recomendacoes_uso'] ?? []);
        $this->processedData['impact_comparison'] = $this->processImpactComparison($content['comparativo_impacto'] ?? []);
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Dados auxiliares usando o trait
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
        $this->processedData['related_topics'] = $this->getRelatedTopics();
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
        $premiumBrands = ['audi', 'bmw', 'mercedes', 'lexus', 'volvo', 'porsche'];

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
     * Estende informações do veículo com dados específicos de carros
     */
    protected function extendVehicleInfo(array $baseInfo, array $vehicleInfo): array
    {
        return array_merge($baseInfo, [
            'is_electric' => $this->isElectricVehicle(),
            'is_hybrid' => $this->isHybridVehicle()
        ]);
    }

    /**
     * Processa especificações oficiais dos pneus
     */
    private function processTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [
            'versions' => []
        ];

        if (!empty($specs['pneu_estepe'])) {
            $processed['spare_tire'] = [
                'size' => $specs['pneu_estepe']['medida'] ?? '',
                'pressure' => $specs['pneu_estepe']['pressao_recomendada'] ?? '',
                'max_speed' => $specs['pneu_estepe']['velocidade_maxima'] ?? '',
                'max_distance' => $specs['pneu_estepe']['distancia_maxima'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Processa tabela de pressões por condição de uso
     */
    private function processPressureTable(array $table): array
    {
        if (empty($table['condicoes_uso']) || !is_array($table['condicoes_uso'])) {
            return [];
        }

        $processed = [];

        foreach ($table['condicoes_uso'] as $condition) {
            if (!empty($condition['situacao'])) {
                $processed[] = [
                    'condition' => $condition['situacao'],
                    'occupants' => $condition['ocupantes'] ?? '',
                    'luggage' => $condition['bagagem'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'css_class' => $this->getConditionCssClass($condition['situacao'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem passo a passo
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure['passos']) || !is_array($procedure['passos'])) {
            return [];
        }

        $processed = [];

        foreach ($procedure['passos'] as $step) {
            if (!empty($step['titulo'])) {
                $processed[] = [
                    'number' => $step['numero'] ?? 1,
                    'title' => $step['titulo'],
                    'description' => $step['descricao'] ?? '',
                    'tips' => $step['dicas'] ?? [],
                    'icon_class' => $this->getStepIconClass($step['numero'] ?? 1)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa informações do sistema TPMS
     */
    private function processTpmsSystem(array $tpms): array
    {
        if (empty($tpms)) {
            return ['has_tpms' => false];
        }

        return [
            'has_tpms' => $tpms['tem_tpms'] ?? false,
            'type' => $tpms['tipo'] ?? '',
            'features' => $tpms['características'] ?? [],
            'reset_procedure' => $tpms['procedimento_reset'] ?? [],
            'benefits' => $this->getTpmsBenefits()
        ];
    }

    /**
     * Processa recomendações de uso específicas
     */
    private function processUsageRecommendations(array $recommendations): array
    {
        if (empty($recommendations) || !is_array($recommendations)) {
            return [];
        }

        $processed = [];

        foreach ($recommendations as $rec) {
            if (!empty($rec['categoria'])) {
                $processed[] = [
                    'category' => $rec['categoria'],
                    'recommended_pressure' => $rec['pressao_recomendada'] ?? '',
                    'description' => $rec['descricao'] ?? '',
                    'technical_tip' => $rec['dica_tecnica'] ?? '',
                    'icon_class' => $this->getUsageIconClass($rec['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa comparativo de impacto por calibragem
     */
    private function processImpactComparison(array $comparison): array
    {
        if (empty($comparison)) {
            return [];
        }

        return [
            'under_inflated' => [
                'stability' => $comparison['subcalibrado_menos20']['estabilidade'] ?? 0,
                'braking' => $comparison['subcalibrado_menos20']['frenagem'] ?? 0,
                'consumption' => $comparison['subcalibrado_menos20']['consumo'] ?? 0,
                'wear' => $comparison['subcalibrado_menos20']['desgaste'] ?? 0
            ],
            'ideal' => [
                'stability' => $comparison['calibragem_ideal']['estabilidade'] ?? 100,
                'braking' => $comparison['calibragem_ideal']['frenagem'] ?? 100,
                'consumption' => $comparison['calibragem_ideal']['consumo'] ?? 0,
                'wear' => $comparison['calibragem_ideal']['desgaste'] ?? 0
            ],
            'over_inflated' => [
                'stability' => $comparison['sobrecalibrado_mais20']['estabilidade'] ?? 0,
                'braking' => $comparison['sobrecalibrado_mais20']['frenagem'] ?? 0,
                'consumption' => $comparison['sobrecalibrado_mais20']['consumo'] ?? 0,
                'wear' => $comparison['sobrecalibrado_mais20']['desgaste'] ?? 0
            ]
        ];
    }

    /**
     * Processa impactos da calibragem
     */
    private function processCalibrationImpacts(array $impacts): array
    {
        $processed = [];

        if (!empty($impacts['sub_calibrado'])) {
            $processed['under_inflated'] = [
                'fuel_consumption' => $impacts['sub_calibrado']['consumo'] ?? '',
                'wear_pattern' => $impacts['sub_calibrado']['desgaste'] ?? '',
                'handling' => $impacts['sub_calibrado']['dirigibilidade'] ?? '',
                'aquaplaning_risk' => $impacts['sub_calibrado']['aquaplanagem'] ?? '',
                'temperature' => $impacts['sub_calibrado']['temperatura'] ?? '',
                'severity_class' => 'high-risk'
            ];
        }

        if (!empty($impacts['super_calibrado'])) {
            $processed['over_inflated'] = [
                'fuel_consumption' => $impacts['super_calibrado']['consumo'] ?? '',
                'wear_pattern' => $impacts['super_calibrado']['desgaste'] ?? '',
                'handling' => $impacts['super_calibrado']['dirigibilidade'] ?? '',
                'comfort' => $impacts['super_calibrado']['conforto'] ?? '',
                'puncture_risk' => $impacts['super_calibrado']['furos'] ?? '',
                'severity_class' => 'medium-risk'
            ];
        }

        if (!empty($impacts['calibragem_ideal'])) {
            $processed['ideal_calibration'] = [
                'fuel_consumption' => $impacts['calibragem_ideal']['consumo'] ?? '',
                'wear_pattern' => $impacts['calibragem_ideal']['desgaste'] ?? '',
                'handling' => $impacts['calibragem_ideal']['dirigibilidade'] ?? '',
                'safety' => $impacts['calibragem_ideal']['seguranca'] ?? '',
                'durability' => $impacts['calibragem_ideal']['durabilidade'] ?? '',
                'severity_class' => 'optimal'
            ];
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção
     */
    private function processMaintenanceTips(array $tips): array
    {
        if (empty($tips) || !is_array($tips)) {
            return [];
        }

        $processed = [];

        foreach ($tips as $tip) {
            if (!empty($tip['categoria']) && !empty($tip['itens'])) {
                $processed[] = [
                    'category' => $tip['categoria'],
                    'items' => $tip['itens'],
                    'icon_class' => $this->getTipIconClass($tip['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa alertas de segurança
     */
    private function processSafetyAlerts(array $alerts): array
    {
        if (empty($alerts) || !is_array($alerts)) {
            return [];
        }

        $processed = [];

        foreach ($alerts as $alert) {
            if (!empty($alert['titulo'])) {
                $processed[] = [
                    'type' => $alert['tipo'] ?? 'info',
                    'title' => $alert['titulo'],
                    'description' => $alert['descricao'] ?? '',
                    'consequences' => $alert['consequencias'] ?? '',
                    'immediate_action' => $alert['acao_imediata'] ?? '',
                    'severity_class' => $this->getAlertSeverityClass($alert['tipo'] ?? 'info'),
                    'icon_class' => $this->getAlertIconClass($alert['tipo'] ?? 'info')
                ];
            }
        }

        return $processed;
    }

    /**
     * Obtém tópicos relacionados
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower($vehicleInfo['modelo'] ?? '');

        return [
            [
                'title' => "Melhores Pneus para {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
                'description' => 'Descubra os pneus ideais para seu veículo',
                'url' => "/info/pneus-recomendados/{$make}-{$model}-{$vehicleInfo['ano']}/"
            ],
            [
                'title' => "Sistema TPMS da {$vehicleInfo['marca']}",
                'description' => 'Entenda o funcionamento do sistema de monitoramento',
                'url' => "/info/manutencao/sistema-tpms-{$make}/"
            ],
            [
                'title' => "Como Economizar Combustível",
                'description' => 'Dicas para reduzir o consumo do seu veículo',
                'url' => "/info/economia/combustivel-{$make}-{$model}-{$vehicleInfo['ano']}/"
            ],
            [
                'title' => "Manutenção da Suspensão",
                'description' => 'Guia completo de manutenção preventiva',
                'url' => "/info/manutencao/suspensao-{$make}-{$model}-{$vehicleInfo['ano']}/"
            ]
        ];
    }

    /**
     * Verifica se é veículo elétrico
     */
    private function isElectricVehicle(): bool
    {
        $fuel = $this->article->extracted_entities['combustivel'] ?? '';
        return in_array(strtolower($fuel), ['elétrico', 'electric', 'eletrico']);
    }

    /**
     * Verifica se é veículo híbrido
     */
    private function isHybridVehicle(): bool
    {
        $fuel = $this->article->extracted_entities['combustivel'] ?? '';
        return str_contains(strtolower($fuel), 'híbrido') || str_contains(strtolower($fuel), 'hibrido');
    }

    /**
     * Obtém classe CSS para condição da tabela de pressões
     */
    private function getConditionCssClass(string $condition): string
    {
        $condition = strtolower($condition);

        if (str_contains($condition, 'normal') || str_contains($condition, 'urbano')) {
            return 'bg-white';
        }

        if (str_contains($condition, 'completa') || str_contains($condition, '5 pessoas')) {
            return 'bg-gray-50';
        }

        if (str_contains($condition, 'viagem') || str_contains($condition, 'rodoviária')) {
            return 'bg-white';
        }

        if (str_contains($condition, 'máxima') || str_contains($condition, 'bagageiro')) {
            return 'bg-gray-50';
        }

        return 'bg-white';
    }

    /**
     * Obtém classe de ícone para categoria de uso
     */
    private function getUsageIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'urbano')) {
            return 'building';
        }

        if (str_contains($category, 'rodovia')) {
            return 'info';
        }

        if (str_contains($category, 'família')) {
            return 'users';
        }

        if (str_contains($category, 'carga') || str_contains($category, 'porta-malas')) {
            return 'package';
        }

        return 'car';
    }

    /**
     * Obtém classe de ícone para passos do procedimento
     */
    private function getStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'thermometer',
            2 => 'map-pin',
            3 => 'tool',
            4 => 'check-circle'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    /**
     * Obtém benefícios do sistema TPMS
     */
    private function getTpmsBenefits(): array
    {
        return [
            'Detecção automática de perda de pressão',
            'Alerta em tempo real no painel',
            'Maior segurança em viagens',
            'Prevenção de desgaste irregular',
            'Economia de combustível'
        ];
    }

    /**
     * Obtém classe de ícone para dicas de manutenção
     */
    private function getTipIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'frequência') || str_contains($category, 'verificação')) {
            return 'clock';
        }

        if (str_contains($category, 'equipamento')) {
            return 'tool';
        }

        if (str_contains($category, 'cuidados') || str_contains($category, 'especiais')) {
            return 'shield';
        }

        return 'info';
    }

    /**
     * Obtém classe de severidade para alertas
     */
    private function getAlertSeverityClass(string $type): string
    {
        $severityMap = [
            'crítico' => 'border-red-500 bg-red-50',
            'alto' => 'border-orange-500 bg-orange-50',
            'atenção' => 'border-yellow-500 bg-yellow-50',
            'info' => 'border-blue-500 bg-blue-50'
        ];

        return $severityMap[strtolower($type)] ?? 'border-blue-500 bg-blue-50';
    }

    /**
     * Obtém classe de ícone para alertas
     */
    private function getAlertIconClass(string $type): string
    {
        $iconMap = [
            'crítico' => 'alert-triangle text-red-500',
            'alto' => 'alert-circle text-orange-500',
            'atenção' => 'info text-yellow-500',
            'info' => 'help-circle text-blue-500'
        ];

        return $iconMap[strtolower($type)] ?? 'help-circle text-blue-500';
    }

    /**
     * Processa dados SEO específicos para carros
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $seoData = $this->article->seo_data ?? [];

        return [
            'title' => $seoData['page_title'] ?? "Calibragem de Pneus {$vehicleFullName} - Guia Completo | Mercado Veículos",
            'meta_description' => $seoData['meta_description'] ?? "Guia completo e oficial sobre calibragem de pneus para {$vehicleFullName}. Pressões ideais, recomendações do fabricante e dicas de segurança.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "calibragem pneu {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Calibragem de Pneus do {$vehicleFullName}",
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => "Calibragem de Pneus {$vehicleFullName} - Guia Completo",
            'og_description' => "Guia completo e oficial sobre calibragem de pneus para {$vehicleFullName}. Pressões ideais, recomendações do fabricante e dicas de segurança.",
            'og_image' => "https://mercadoveiculos.com/images/{$vehicleInfo['marca']}-{$vehicleInfo['modelo']}-{$vehicleInfo['ano']}.jpg",
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $vehicleFullName = $vehicleInfo['full_name'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'name' => "Calibragem de Pneus do {$vehicleFullName}",
            'description' => "Guia completo sobre calibragem de pneus do {$vehicleFullName}, incluindo pressões recomendadas, procedimento passo a passo e dicas de manutenção.",
            'vehicleEngine' => $vehicleFullName,
            'category' => 'Manutenção Automotiva',
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-carro.jpg',
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
            'dateModified' => $this->article->updated_at?->toISOString()
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
     * Obter todos os dados processados
     */
    public function toArray(): array
    {
        return $this->processedData;
    }
}
