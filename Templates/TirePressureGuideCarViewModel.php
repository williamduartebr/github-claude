<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class TirePressureGuideCarViewModel extends TemplateViewModel
{
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

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Especificações oficiais dos pneus
        $this->processedData['tire_specifications'] = $this->processTireSpecifications($content['especificacoes_oficiais'] ?? []);

        // Tabela de pressões por condição de uso
        $this->processedData['pressure_table'] = $this->processPressureTable($content['tabela_pressoes'] ?? []);

        // Procedimento de calibragem passo a passo
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);

        // Sistema TPMS (Tire Pressure Monitoring System)
        $this->processedData['tpms_system'] = $this->processTpmsSystem($content['sistema_tpms'] ?? []);

        // Impactos da calibragem incorreta vs ideal
        $this->processedData['calibration_impacts'] = $this->processCalibrationImpacts($content['impactos_calibragem'] ?? []);

        // Dicas de manutenção e cuidados
        $this->processedData['maintenance_tips'] = $this->processMaintenanceTips($content['dicas_manutencao'] ?? []);

        // Alertas de segurança importantes
        $this->processedData['safety_alerts'] = $this->processSafetyAlerts($content['alertas_seguranca'] ?? []);

        // Perguntas frequentes
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];

        // Considerações finais
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Informações do veículo formatadas
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();

        // Dados estruturados para SEO
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
    }

    /**
     * Processa especificações oficiais dos pneus
     */
    private function processTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [];

        // Pneu dianteiro
        if (!empty($specs['pneu_dianteiro'])) {
            $processed['front_tire'] = [
                'size' => $specs['pneu_dianteiro']['medida_original'] ?? '',
                'pressure_empty' => $specs['pneu_dianteiro']['pressao_vazio'] ?? '',
                'pressure_loaded' => $specs['pneu_dianteiro']['pressao_carregado'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'max_capacity' => $specs['pneu_dianteiro']['capacidade_maxima'] ?? ''
            ];
        }

        // Pneu traseiro
        if (!empty($specs['pneu_traseiro'])) {
            $processed['rear_tire'] = [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'pressure_empty' => $specs['pneu_traseiro']['pressao_vazio'] ?? '',
                'pressure_loaded' => $specs['pneu_traseiro']['pressao_carregado'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'max_capacity' => $specs['pneu_traseiro']['capacidade_maxima'] ?? ''
            ];
        }

        // Estepe
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
                    'situation' => $condition['situacao'],
                    'occupants' => $condition['ocupantes'] ?? '',
                    'luggage' => $condition['bagagem'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'css_class' => $this->getSituationCssClass($condition['situacao'])
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
     * Processa impactos da calibragem
     */
    private function processCalibrationImpacts(array $impacts): array
    {
        $processed = [];

        // Sub-calibrado
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

        // Super-calibrado
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

        // Calibragem ideal
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
                    'severity_class' => $this->getAlertSeverityClass($alert['tipo'] ?? 'info'),
                    'icon_class' => $this->getAlertIconClass($alert['tipo'] ?? 'info')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa informações do veículo
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function processVehicleInfo(): array
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        
        return [
            'full_name' => $this->getVehicleFullName(),
            'make' => $vehicleInfo['marca'] ?? '',
            'model' => $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleInfo['ano'] ?? '',
            'category' => $vehicleInfo['categoria'] ?? '',
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'fuel' => $vehicleInfo['combustivel'] ?? '',
            'image_url' => $this->getVehicleImageUrl(),
            'is_electric' => $this->isElectricVehicle(),
            'is_hybrid' => $this->isHybridVehicle()
        ];
    }

    /**
     * Obtém nome completo do veículo
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getVehicleFullName(): string
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        
        $make = $vehicleInfo['marca'] ?? '';
        $model = $vehicleInfo['modelo'] ?? '';
        $year = $vehicleInfo['ano'] ?? '';
        
        return trim("{$make} {$model} {$year}");
    }

    /**
     * Verifica se é veículo elétrico
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isElectricVehicle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $fuel = $this->article->extracted_entities['combustivel'] ?? '';
        return in_array(strtolower($fuel), ['elétrico', 'electric', 'eletrico']);
    }

    /**
     * Verifica se é veículo híbrido
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isHybridVehicle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $fuel = $this->article->extracted_entities['combustivel'] ?? '';
        return str_contains(strtolower($fuel), 'híbrido') || str_contains(strtolower($fuel), 'hibrido');
    }

    /**
     * Obtém URL da imagem do veículo
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getVehicleImageUrl(): string
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $makeSlug = strtolower($vehicleInfo['marca'] ?? '');
        $modelSlug = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));
        $year = $vehicleInfo['ano'] ?? '';
        
        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/vehicles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Obtém classe CSS para situação da tabela de pressões
     */
    private function getSituationCssClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'normal') || str_contains($situation, 'urbano')) {
            return 'situation-normal';
        }
        
        if (str_contains($situation, 'família') || str_contains($situation, 'completa')) {
            return 'situation-family';
        }
        
        if (str_contains($situation, 'viagem') || str_contains($situation, 'longa')) {
            return 'situation-travel';
        }
        
        if (str_contains($situation, 'rodovia') || str_contains($situation, 'velocidade')) {
            return 'situation-highway';
        }
        
        return 'situation-default';
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
            4 => 'list-ordered'
        ];
        
        return $icons[$stepNumber] ?? 'check-circle';
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
        
        if (str_contains($category, 'verificação') || str_contains($category, 'regular')) {
            return 'clock';
        }
        
        if (str_contains($category, 'cuidados') || str_contains($category, 'especiais')) {
            return 'shield';
        }
        
        if (str_contains($category, 'manutenção') || str_contains($category, 'preventiva')) {
            return 'wrench';
        }
        
        return 'info';
    }

    /**
     * Obtém classe de severidade para alertas
     */
    private function getAlertSeverityClass(string $type): string
    {
        $severityMap = [
            'crítico' => 'alert-critical',
            'importante' => 'alert-important', 
            'atenção' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        return $severityMap[strtolower($type)] ?? 'alert-info';
    }

    /**
     * Obtém classe de ícone para alertas
     */
    private function getAlertIconClass(string $type): string
    {
        $iconMap = [
            'crítico' => 'alert-triangle',
            'importante' => 'alert-circle',
            'atenção' => 'info',
            'info' => 'help-circle'
        ];
        
        return $iconMap[strtolower($type)] ?? 'help-circle';
    }

    /**
     * Processa dados SEO específicos para carros
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $frontPressure = $this->processedData['tire_specifications']['front_tire']['pressure_empty'] ?? '';
        $rearPressure = $this->processedData['tire_specifications']['rear_tire']['pressure_empty'] ?? '';
        
        $pressureDisplay = $frontPressure && $rearPressure ? "{$frontPressure} (dianteira) / {$rearPressure} (traseira)" : '';
        
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        
        return [
            'title' => $this->article->title ?? "Calibragem do Pneu do {$vehicleFullName} - Pressão Ideal",
            'meta_description' => $this->article->meta_description ?? "Saiba a pressão ideal para calibrar os pneus do {$vehicleFullName}. Pressões recomendadas: {$pressureDisplay}. Guia completo com dicas de segurança e economia!",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "calibragem pneu {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'og_title' => "Calibragem do Pneu do {$vehicleFullName} - Guia Completo",
            'og_description' => "Pressões ideais, procedimento passo a passo e dicas importantes para calibrar os pneus do {$vehicleFullName}.",
            'og_image' => $this->processedData['vehicle_info']['image_url'],
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $vehicleFullName = $vehicleInfo['full_name'];
        
        // CORREÇÃO: Usar extracted_entities para dados do schema
        $vehicleData = $this->article->extracted_entities ?? [];
        
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => "Como Calibrar os Pneus do {$vehicleFullName}",
            'description' => "Guia completo sobre calibragem de pneus do {$vehicleFullName}, incluindo pressões recomendadas, procedimento passo a passo e dicas de manutenção.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? $this->getDefaultCarImage(),
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
            'totalTime' => 'PT15M',
            'prepTime' => 'PT5M',
            'performTime' => 'PT10M',
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'BRL',
                'value' => '0'
            ],
            'supply' => [
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Calibrador digital de pneus'
                ],
                [
                    '@type' => 'HowToSupply', 
                    'name' => 'Compressor de ar'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Calibrador digital'
                ]
            ],
            'step' => $this->buildHowToSteps()
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleData['marca']) && !empty($vehicleData['modelo'])) {
            // Determina o tipo baseado no tipo_veiculo
            $vehicleSchemaType = ($vehicleData['tipo_veiculo'] ?? '') === 'motocicleta' ? 'Motorcycle' : 'Car';
            
            $structuredData['about'] = [
                '@type' => $vehicleSchemaType,
                'brand' => $vehicleData['marca'],
                'model' => $vehicleData['modelo']
            ];

            // Adiciona ano se existir
            if (!empty($vehicleData['ano'])) {
                $structuredData['about']['modelDate'] = (string) $vehicleData['ano'];
            }
        }

        return $structuredData;
    }

    /**
     * Constrói os passos do HowTo para schema
     */
    private function buildHowToSteps(): array
    {
        $steps = [];
        $stepNumber = 1;

        // Passos básicos do procedimento
        $basicSteps = [
            [
                'name' => 'Verificação Inicial',
                'text' => 'Verifique a pressão sempre com pneus frios, preferencialmente pela manhã antes de usar o veículo'
            ],
            [
                'name' => 'Localização da Etiqueta',
                'text' => 'Encontre a etiqueta com as pressões recomendadas na soleira da porta do motorista'
            ],
            [
                'name' => 'Preparação do Equipamento',
                'text' => 'Use um calibrador digital de qualidade para maior precisão na medição'
            ],
            [
                'name' => 'Calibragem dos Pneus',
                'text' => 'Calibre na sequência: dianteiro esquerdo, direito, traseiro esquerdo, direito'
            ],
            [
                'name' => 'Verificação Final',
                'text' => 'Confira se todas as pressões estão corretas e não esqueça do estepe'
            ]
        ];

        foreach ($basicSteps as $step) {
            $steps[] = [
                '@type' => 'HowToStep',
                'position' => $stepNumber++,
                'name' => $step['name'],
                'text' => $step['text'],
                'url' => $this->getCanonicalUrl() . '#' . Str::slug($step['name'])
            ];
        }

        return $steps;
    }

    /**
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? route('info.article.show', $this->article->slug);
    }

    /**
     * Obtém imagem padrão para carros
     */
    private function getDefaultCarImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-carro.jpg';
    }

    /**
     * Processa breadcrumbs para navegação
     */
    public function getBreadcrumbs(): array
    {
        return [
            [
                'name' => 'Home',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Info Center',
                'url' => route('info.home'),
                'position' => 2
            ],
            [
                'name' => $this->article->category_name ?? 'Calibragem de Pneus',
                'url' => route('info.category.show', $this->article->category_slug ?? 'calibragem-pneus'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => null,
                'position' => 4
            ],
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