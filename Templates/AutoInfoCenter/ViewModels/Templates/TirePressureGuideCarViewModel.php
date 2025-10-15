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
        $this->processedData['required_equipment'] = $this->processRequiredEquipment($content['equipamentos_necessarios'] ?? []);
        $this->processedData['alternative_tires'] = $this->processAlternativeTires($content['pneus_alternativos'] ?? []);
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
     * Processa especificações dos pneus com dados das mocks
     */
    private function processTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [
            'title' => 'Especificações Técnicas dos Pneus Originais',
            'description' => 'Informações oficiais dos pneus de fábrica e características técnicas.',
            'front_tire' => [
                'size' => $specs['pneu_dianteiro']['medida_original'] ?? '',
                'type' => $specs['pneu_dianteiro']['tipo'] ?? '',
                'brand' => $specs['pneu_dianteiro']['marca_original'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'recommended_pressure' => $specs['pneu_dianteiro']['pressao_recomendada'] ?? '',
                'max_pressure' => $specs['pneu_dianteiro']['pressao_maxima'] ?? '',
                'characteristics' => $specs['pneu_dianteiro']['caracteristicas'] ?? ''
            ],
            'rear_tire' => [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'type' => $specs['pneu_traseiro']['tipo'] ?? '',
                'brand' => $specs['pneu_traseiro']['marca_original'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'recommended_pressure' => $specs['pneu_traseiro']['pressao_recomendada'] ?? '',
                'max_pressure' => $specs['pneu_traseiro']['pressao_maxima'] ?? '',
                'characteristics' => $specs['pneu_traseiro']['caracteristicas'] ?? ''
            ],
            'note' => $specs['observacao'] ?? '',
            'has_tpms' => $this->processedData['vehicle_info']['has_tpms'] ?? false
        ];

        return $processed;
    }

    /**
     * Processa tabela de pressões por condições de uso
     */
    private function processPressureTable(array $table): array
    {
        if (empty($table['condicoes_uso'])) {
            return [];
        }

        $processed = [
            'title' => 'Tabela de Pressões por Condição de Uso',
            'description' => 'Pressões recomendadas para diferentes situações de uso do veículo.',
            'conditions' => []
        ];

        foreach ($table['condicoes_uso'] as $condition) {
            $processed['conditions'][] = [
                'situation' => $condition['situacao'] ?? '',
                'occupants' => $condition['ocupantes'] ?? '',
                'luggage' => $condition['bagagem'] ?? '',
                'front_pressure' => $condition['pressao_dianteira'] ?? '',
                'rear_pressure' => $condition['pressao_traseira'] ?? '',
                'note' => $condition['observacao'] ?? '',
                'css_class' => $this->getConditionCssClass($condition['situacao'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure['passos'])) {
            return [];
        }

        $processed = [
            'title' => 'Procedimento Completo de Calibragem',
            'description' => 'Passo a passo detalhado para calibrar corretamente os pneus.',
            'steps' => []
        ];

        foreach ($procedure['passos'] as $step) {
            $processed['steps'][] = [
                'number' => $step['numero'] ?? 1,
                'title' => $step['titulo'] ?? '',
                'description' => $step['descricao'] ?? '',
                'tips' => $step['dicas'] ?? [],
                'icon_class' => $this->getStepIconClass($step['numero'] ?? 1),
                'css_class' => $this->getStepCssClass($step['numero'] ?? 1)
            ];
        }

        return $processed;
    }

    /**
     * Processa sistema TPMS
     */
    private function processTpmsSystem(array $tpms): array
    {
        if (empty($tpms)) {
            return [];
        }

        return [
            'has_system' => $tpms['possui_sistema'] ?? false,
            'title' => $tpms['titulo'] ?? 'Sistema TPMS',
            'description' => $tpms['descricao'] ?? '',
            'benefits' => $tpms['beneficios'] ?? [],
            'calibration_tips' => $tpms['dicas_calibragem'] ?? [],
            'reset_procedure' => $tpms['procedimento_reset'] ?? ''
        ];
    }

    /**
     * Processa impactos da calibragem
     */
    private function processCalibrationImpacts(array $impacts): array
    {
        if (empty($impacts)) {
            return [];
        }

        $processed = [
            'title' => 'Impactos da Calibragem no Desempenho',
            'description' => 'Como a pressão dos pneus afeta consumo, segurança e durabilidade.',
            'categories' => []
        ];

        foreach ($impacts as $category => $data) {
            $processed['categories'][] = [
                'name' => $category,
                'title' => $data['titulo'] ?? ucfirst($category),
                'description' => $data['descricao'] ?? '',
                'benefits' => $data['beneficios'] ?? [],
                'risks' => $data['riscos'] ?? [],
                'icon_class' => $this->getCategoryIconClass($category),
                'css_class' => $this->getCategoryCssClass($category)
            ];
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção
     */
    private function processMaintenanceTips(array $tips): array
    {
        if (empty($tips)) {
            return [];
        }

        $processed = [
            'title' => 'Dicas de Manutenção e Cuidados',
            'description' => 'Cuidados essenciais para prolongar a vida útil dos pneus.',
            'categories' => []
        ];

        foreach ($tips as $tip) {
            $processed['categories'][] = [
                'category' => $tip['categoria'] ?? '',
                'title' => $tip['titulo'] ?? '',
                'description' => $tip['descricao'] ?? '',
                'tips' => $tip['dicas'] ?? [],
                'frequency' => $tip['frequencia'] ?? '',
                'icon_class' => $this->getTipIconClass($tip['categoria'] ?? ''),
                'css_class' => $this->getTipCssClass($tip['categoria'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa alertas de segurança
     */
    private function processSafetyAlerts(array $alerts): array
    {
        if (empty($alerts)) {
            return [];
        }

        $processed = [
            'title' => 'Alertas Críticos de Segurança',
            'description' => 'Situações que requerem atenção imediata para sua segurança.',
            'alerts' => []
        ];

        foreach ($alerts as $alert) {
            $processed['alerts'][] = [
                'type' => $alert['tipo'] ?? 'warning',
                'title' => $alert['titulo'] ?? '',
                'description' => $alert['descricao'] ?? '',
                'consequences' => $alert['consequencias'] ?? [],
                'actions' => $alert['acoes'] ?? [],
                'urgency' => $alert['urgencia'] ?? 'medium',
                'icon_class' => $this->getAlertIconClass($alert['tipo'] ?? 'warning'),
                'css_class' => $this->getAlertCssClass($alert['tipo'] ?? 'warning')
            ];
        }

        return $processed;
    }

    /**
     * Processa recomendações de uso
     */
    private function processUsageRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return [];
        }

        $processed = [
            'title' => 'Recomendações por Tipo de Uso',
            'description' => 'Ajustes específicos para diferentes situações de condução.',
            'categories' => []
        ];

        foreach ($recommendations as $rec) {
            $processed['categories'][] = [
                'category' => $rec['categoria'] ?? '',
                'recommended_pressure' => $rec['pressao_recomendada'] ?? '',
                'description' => $rec['descricao'] ?? '',
                'technical_tip' => $rec['dica_tecnica'] ?? '',
                'verification_frequency' => $rec['frequencia_verificacao'] ?? '',
                'icon_class' => $this->getUsageIconClass($rec['categoria'] ?? ''),
                'css_class' => $this->getUsageCssClass($rec['categoria'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa comparativo de impactos
     */
    private function processImpactComparison(array $comparison): array
    {
        if (empty($comparison)) {
            return [];
        }

        return [
            'title' => 'Comparativo de Impactos',
            'description' => 'Diferenças entre pressão baixa, ideal e alta.',
            'scenarios' => [
                'low_pressure' => $comparison['pressao_baixa'] ?? [],
                'ideal_pressure' => $comparison['pressao_ideal'] ?? [],
                'high_pressure' => $comparison['pressao_alta'] ?? []
            ],
            'estimated_savings' => $comparison['economia_estimada'] ?? []
        ];
    }

    /**
     * Processa equipamentos necessários
     */
    private function processRequiredEquipment(array $equipment): array
    {
        if (empty($equipment)) {
            return [];
        }

        $processed = [
            'title' => 'Equipamentos Necessários',
            'description' => 'Ferramentas essenciais para calibragem adequada.',
            'items' => []
        ];

        foreach ($equipment as $item) {
            $processed['items'][] = [
                'name' => $item['item'] ?? '',
                'importance' => $item['importancia'] ?? '',
                'description' => $item['descricao'] ?? '',
                'tips' => $item['dicas'] ?? [],
                'estimated_price' => $item['preco_estimado'] ?? '',
                'css_class' => $this->getImportanceClass($item['importancia'] ?? ''),
                'icon_class' => $this->getEquipmentIconClass($item['item'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa pneus alternativos
     */
    private function processAlternativeTires(array $alternatives): array
    {
        if (empty($alternatives)) {
            return [];
        }

        return [
            'title' => 'Pneus Alternativos Recomendados',
            'description' => 'Opções de pneus compatíveis para diferentes necessidades.',
            'categories' => [
                'premium' => $alternatives['premium'] ?? [],
                'performance' => $alternatives['performance'] ?? [],
                'budget' => $alternatives['budget'] ?? [],
                'seasonal' => $alternatives['seasonal_recommendations'] ?? []
            ],
            'note' => 'Sempre mantenha o mesmo tamanho e índices de carga/velocidade.'
        ];
    }

    /**
     * Processa dados SEO específicos para guia de carros
     */
    private function processSeoData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $seoData = $this->article->seo_data ?? [];

        return [
            'title' => $seoData['page_title'] ?? "Como Calibrar Pneus do {$vehicleInfo['full_name']} - Guia Completo",
            'meta_description' => $seoData['meta_description'] ?? "Guia completo para calibrar pneus do {$vehicleInfo['full_name']}. Procedimento passo a passo, tabela de pressões e dicas de segurança.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "como calibrar pneus {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Como Calibrar Pneus do {$vehicleInfo['full_name']} – Guia Completo",
            'h2_tags' => $seoData['h2_tags'] ?? [
                'Especificações Técnicas dos Pneus Originais',
                'Tabela de Pressões por Condição de Uso',
                'Procedimento Completo de Calibragem',
                'Sistema TPMS - Monitoramento Automático',
                'Impactos da Calibragem no Desempenho',
                'Dicas de Manutenção e Cuidados',
                'Alertas Críticos de Segurança',
                'Recomendações por Tipo de Uso',
                'Equipamentos Necessários',
                'Pneus Alternativos Recomendados',
                'Perguntas Frequentes'
            ],
            'og_title' => $seoData['og_title'] ?? "Guia: Como Calibrar Pneus do {$vehicleInfo['full_name']}",
            'og_description' => $seoData['og_description'] ?? "Aprenda o procedimento correto para calibrar os pneus do seu {$vehicleInfo['full_name']}. Guia completo com tabelas e dicas profissionais.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => "Como Calibrar Pneus do {$vehicleFullName}",
            'description' => "Guia passo a passo para calibrar corretamente os pneus do {$vehicleFullName}, incluindo procedimentos, tabelas de pressão e dicas de segurança.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-carro.jpg',
                'width' => 1200,
                'height' => 630
            ],
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'BRL',
                'value' => '0'
            ],
            'supply' => [
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Calibrador de pneus'
                ],
                [
                    '@type' => 'HowToSupply', 
                    'name' => 'Compressor de ar'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Medidor de pressão digital'
                ]
            ],
            'totalTime' => 'PT15M',
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
     * Processa tópicos relacionados
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        $topics = [];

        // Tópicos específicos por marca
        $topics[] = [
            'title' => 'Manutenção do ' . $vehicleInfo['make'],
            'url' => '/info/manutencao-' . strtolower($vehicleInfo['make']),
            'description' => 'Guia completo de manutenção para veículos ' . $vehicleInfo['make']
        ];

        // Tópicos por tipo de veículo
        if ($this->processedData['vehicle_info']['is_electric'] ?? false) {
            $topics[] = [
                'title' => 'Cuidados com Carros Elétricos',
                'url' => '/info/cuidados-carros-eletricos',
                'description' => 'Manutenção específica para veículos elétricos'
            ];
        }

        if ($this->processedData['vehicle_info']['has_tpms'] ?? false) {
            $topics[] = [
                'title' => 'Como Funciona o Sistema TPMS',
                'url' => '/info/sistema-tpms-monitoramento-pressao',
                'description' => 'Entenda o sistema de monitoramento de pressão'
            ];
        }

        $topics[] = [
            'title' => 'Quando Trocar os Pneus',
            'url' => '/info/quando-trocar-pneus',
            'description' => 'Sinais de que é hora de trocar os pneus'
        ];

        return $topics;
    }

    // Métodos auxiliares para classes CSS e ícones

    private function getConditionCssClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'normal') || str_contains($situation, 'diário')) {
            return 'bg-green-50 border-green-200';
        }
        
        if (str_contains($situation, 'carga') || str_contains($situation, 'viagem')) {
            return 'bg-blue-50 border-blue-200';
        }
        
        if (str_contains($situation, 'esportiv') || str_contains($situation, 'alta velocidade')) {
            return 'bg-red-50 border-red-200';
        }
        
        return 'bg-gray-50 border-gray-200';
    }

    private function getStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'settings',
            2 => 'search',
            3 => 'tool',
            4 => 'check-circle'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    private function getStepCssClass(int $stepNumber): string
    {
        $classes = [
            1 => 'step-preparation',
            2 => 'step-verification', 
            3 => 'step-calibration',
            4 => 'step-completion'
        ];

        return $classes[$stepNumber] ?? 'step-default';
    }

    private function getCategoryIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'consumo') || str_contains($category, 'economia')) {
            return 'trending-down';
        }
        
        if (str_contains($category, 'segurança') || str_contains($category, 'seguranca')) {
            return 'shield';
        }
        
        if (str_contains($category, 'conforto')) {
            return 'smile';
        }
        
        if (str_contains($category, 'durabilidade')) {
            return 'clock';
        }
        
        return 'info';
    }

    private function getCategoryCssClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'economia')) {
            return 'bg-green-50 border-green-200';
        }
        
        if (str_contains($category, 'segurança')) {
            return 'bg-red-50 border-red-200';
        }
        
        if (str_contains($category, 'conforto')) {
            return 'bg-blue-50 border-blue-200';
        }
        
        return 'bg-gray-50 border-gray-200';
    }

    private function getTipIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'verificação') || str_contains($category, 'inspeção')) {
            return 'eye';
        }
        
        if (str_contains($category, 'limpeza')) {
            return 'droplets';
        }
        
        if (str_contains($category, 'armazenamento')) {
            return 'archive';
        }
        
        return 'tool';
    }

    private function getTipCssClass(string $category): string
    {
        return 'bg-blue-50 border-blue-200';
    }

    private function getAlertIconClass(string $type): string
    {
        $type = strtolower($type);
        
        if ($type === 'critico' || $type === 'critical') {
            return 'alert-triangle';
        }
        
        if ($type === 'warning' || $type === 'aviso') {
            return 'alert-circle';
        }
        
        return 'info';
    }

    private function getAlertCssClass(string $type): string
    {
        $type = strtolower($type);
        
        if ($type === 'critico' || $type === 'critical') {
            return 'bg-red-50 border-red-500 text-red-800';
        }
        
        if ($type === 'warning' || $type === 'aviso') {
            return 'bg-yellow-50 border-yellow-400 text-yellow-800';
        }
        
        return 'bg-blue-50 border-blue-400 text-blue-800';
    }

    private function getUsageIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'urbano') || str_contains($category, 'cidade')) {
            return 'building';
        }
        
        if (str_contains($category, 'rodoviário') || str_contains($category, 'estrada')) {
            return 'truck';
        }
        
        if (str_contains($category, 'esportiv')) {
            return 'zap';
        }
        
        if (str_contains($category, 'eco')) {
            return 'leaf';
        }
        
        return 'car';
    }

    private function getUsageCssClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'esportiv')) {
            return 'bg-red-50 border-red-200';
        }
        
        if (str_contains($category, 'eco')) {
            return 'bg-green-50 border-green-200';
        }
        
        return 'bg-blue-50 border-blue-200';
    }

    private function getImportanceClass(string $importance): string
    {
        $importanceMap = [
            'essencial' => 'text-red-600 font-bold',
            'muito importante' => 'text-orange-600 font-semibold',
            'importante' => 'text-yellow-600 font-medium',
            'recomendado' => 'text-green-600',
            'opcional' => 'text-gray-600'
        ];

        return $importanceMap[strtolower($importance)] ?? 'text-gray-600';
    }

    private function getEquipmentIconClass(string $item): string
    {
        $item = strtolower($item);
        
        if (str_contains($item, 'calibrador') || str_contains($item, 'medidor')) {
            return 'gauge';
        }
        
        if (str_contains($item, 'compressor')) {
            return 'wind';
        }
        
        if (str_contains($item, 'lanterna') || str_contains($item, 'luz')) {
            return 'flashlight';
        }
        
        if (str_contains($item, 'luva')) {
            return 'hand';
        }
        
        return 'tool';
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