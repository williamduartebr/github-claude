<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;

class TirePressureGuideMotorcycleViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait;

    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_pressure_guide_motorcycle';

    /**
     * Processa dados específicos do template de guia de calibragem para motocicletas
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications'] = $this->processMotorcycleTireSpecifications($content['especificacoes_oficiais'] ?? []);
        $this->processedData['pressure_table'] = $this->processMotorcyclePressureTable($content['tabela_pressoes'] ?? []);
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);
        $this->processedData['usage_recommendations'] = $this->processUsageRecommendations($content['recomendacoes_uso'] ?? []);
        $this->processedData['impact_comparison'] = $this->processImpactComparison($content['comparativo_impacto'] ?? []);
        $this->processedData['alternative_tires'] = $this->processAlternativeTires($content['pneus_alternativos'] ?? []);
        $this->processedData['required_equipment'] = $this->processRequiredEquipment($content['equipamentos_necessarios'] ?? []);
        $this->processedData['special_care'] = $this->processSpecialCare($content['cuidados_especiais'] ?? []);
        $this->processedData['problem_signs'] = $this->processProblemSigns($content['sinais_problemas'] ?? []);
        $this->processedData['safety_alerts'] = $this->processSafetyAlerts($content['alertas_seguranca'] ?? []);
        $this->processedData['maintenance_tips'] = $this->processMaintenanceTips($content['dicas_manutencao'] ?? []);
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
        return 'motorcycles';
    }

    /**
     * Verifica se é motocicleta premium
     */
    protected function isPremiumVehicle(): bool
    {
        $make = strtolower($this->article->extracted_entities['marca'] ?? '');
        $model = strtolower($this->article->extracted_entities['modelo'] ?? '');
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        $premiumBrands = ['ducati', 'bmw', 'ktm', 'triumph', 'harley', 'harley-davidson'];
        $premiumCategories = ['sport', 'adventure', 'touring', 'premium'];
        
        return in_array($make, $premiumBrands) || 
               in_array($category, $premiumCategories) ||
               str_contains($model, '1000') ||
               str_contains($model, '800') ||
               str_contains($model, 'gsx') ||
               str_contains($model, 'mt');
    }

    /**
     * Obtém segmento da motocicleta
     */
    protected function getVehicleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        $model = strtolower($this->article->extracted_entities['modelo'] ?? '');
        
        if (str_contains($category, 'sport') || str_contains($model, 'gsx') || str_contains($model, 'r1') || str_contains($model, 'cbr')) {
            return 'Esportiva';
        }
        
        if (str_contains($category, 'naked') || str_contains($model, 'mt') || str_contains($model, 'cb') || str_contains($model, 'z')) {
            return 'Naked';
        }
        
        if (str_contains($category, 'street') || str_contains($model, 'cg') || str_contains($model, 'fazer') || str_contains($model, 'factor')) {
            return 'Street';
        }
        
        if (str_contains($category, 'adventure') || str_contains($model, 'tenere') || str_contains($model, 'vstrom')) {
            return 'Adventure';
        }
        
        if (str_contains($category, 'touring') || str_contains($model, 'goldwing')) {
            return 'Touring';
        }
        
        return 'Motocicleta';
    }

    /**
     * Processa especificações dos pneus para motocicletas
     */
    private function processMotorcycleTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [
            'title' => 'Especificações Técnicas dos Pneus Originais',
            'description' => 'Informações oficiais dos pneus de fábrica e características técnicas para motocicletas.',
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
            'is_sport' => $this->isSportMotorcycle(),
            'engine_power' => $this->getEnginePower()
        ];

        return $processed;
    }

    /**
     * Processa tabela de pressões para motocicletas
     */
    private function processMotorcyclePressureTable(array $table): array
    {
        if (empty($table['condicoes_uso'])) {
            return [];
        }

        $processed = [
            'title' => 'Tabela de Pressões por Condição de Uso',
            'description' => 'Pressões recomendadas para diferentes situações de pilotagem.',
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
                'css_class' => $this->getMotorcycleConditionCssClass($condition['situacao'] ?? ''),
                'icon_class' => $this->getMotorcycleConditionIconClass($condition['situacao'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem para motocicletas
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure['passos'])) {
            return [];
        }

        $processed = [
            'title' => 'Procedimento Completo de Calibragem para Motocicletas',
            'description' => 'Passo a passo específico para calibrar corretamente os pneus de motocicletas.',
            'steps' => []
        ];

        foreach ($procedure['passos'] as $step) {
            $processed['steps'][] = [
                'number' => $step['numero'] ?? 1,
                'title' => $step['titulo'] ?? '',
                'description' => $step['descricao'] ?? '',
                'tips' => $step['dicas'] ?? [],
                'icon_class' => $this->getMotorcycleStepIconClass($step['numero'] ?? 1),
                'css_class' => $this->getMotorcycleStepCssClass($step['numero'] ?? 1),
                'safety_note' => $this->getStepSafetyNote($step['numero'] ?? 1)
            ];
        }

        return $processed;
    }

    /**
     * Processa recomendações de uso para motocicletas
     */
    private function processUsageRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return [];
        }

        $processed = [
            'title' => 'Recomendações por Estilo de Pilotagem',
            'description' => 'Ajustes específicos para diferentes tipos de pilotagem e uso da motocicleta.',
            'categories' => []
        ];

        foreach ($recommendations as $rec) {
            $processed['categories'][] = [
                'category' => $rec['categoria'] ?? '',
                'recommended_pressure' => $rec['pressao_recomendada'] ?? '',
                'description' => $rec['descricao'] ?? '',
                'technical_tip' => $rec['dica_tecnica'] ?? '',
                'verification_frequency' => $rec['frequencia_verificacao'] ?? '',
                'icon_class' => $this->getMotorcycleUsageIconClass($rec['categoria'] ?? ''),
                'css_class' => $this->getMotorcycleUsageCssClass($rec['categoria'] ?? ''),
                'safety_level' => $this->getUsageSafetyLevel($rec['categoria'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa comparativo de impactos para motocicletas
     */
    private function processImpactComparison(array $comparison): array
    {
        if (empty($comparison)) {
            return [];
        }

        return [
            'title' => 'Comparativo de Impactos na Pilotagem',
            'description' => 'Como diferentes pressões afetam pilotagem, segurança e performance.',
            'scenarios' => [
                'low_pressure' => $this->processImpactScenario($comparison['pressao_baixa'] ?? [], 'baixa'),
                'ideal_pressure' => $this->processImpactScenario($comparison['pressao_ideal'] ?? [], 'ideal'),
                'high_pressure' => $this->processImpactScenario($comparison['pressao_alta'] ?? [], 'alta')
            ],
            'safety_warnings' => $comparison['avisos_seguranca'] ?? []
        ];
    }

    /**
     * Processa cenário de impacto
     */
    private function processImpactScenario(array $scenario, string $type): array
    {
        if (empty($scenario)) {
            return [];
        }

        return [
            'title' => $scenario['titulo'] ?? ucfirst($type) . ' Pressão',
            'description' => $scenario['descricao'] ?? '',
            'effects' => $scenario['efeitos'] ?? [],
            'consequences' => $scenario['consequencias'] ?? [],
            'css_class' => $this->getImpactScenarioClass($type),
            'icon' => $this->getImpactScenarioIcon($type)
        ];
    }

    /**
     * Processa pneus alternativos para motocicletas
     */
    private function processAlternativeTires(array $alternatives): array
    {
        if (empty($alternatives)) {
            return [];
        }

        return [
            'title' => 'Pneus Alternativos para Motocicletas',
            'description' => 'Opções de pneus compatíveis para diferentes estilos de pilotagem.',
            'categories' => [
                'sport' => $alternatives['sport'] ?? [],
                'touring' => $alternatives['touring'] ?? [],
                'street' => $alternatives['street'] ?? [],
                'premium' => $alternatives['premium'] ?? [],
                'budget' => $alternatives['budget'] ?? [],
                'seasonal' => $alternatives['seasonal_recommendations'] ?? []
            ],
            'note' => 'Para motocicletas, NUNCA misture marcas entre dianteiro e traseiro. Sempre mantenha as especificações originais.',
            'compatibility_warning' => $this->getCompatibilityWarning()
        ];
    }

    /**
     * Processa equipamentos necessários para motocicletas
     */
    private function processRequiredEquipment(array $equipment): array
    {
        if (empty($equipment)) {
            return [];
        }

        $processed = [
            'title' => 'Equipamentos Específicos para Motocicletas',
            'description' => 'Ferramentas essenciais para calibragem segura e precisa.',
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
                'icon_class' => $this->getMotorcycleEquipmentIconClass($item['item'] ?? ''),
                'motorcycle_specific' => $this->isMotorcycleSpecificEquipment($item['item'] ?? '')
            ];
        }

        return $processed;
    }

    /**
     * Processa cuidados especiais para motocicletas
     */
    private function processSpecialCare(array $care): array
    {
        if (empty($care)) {
            return [];
        }

        $processed = [
            'title' => 'Cuidados Específicos para Motocicletas',
            'description' => 'Cuidados adicionais necessários para manter segurança e performance.',
            'categories' => []
        ];

        foreach ($care as $category) {
            $processed['categories'][] = [
                'category' => $category['categoria'] ?? '',
                'title' => $category['titulo'] ?? '',
                'description' => $category['descricao'] ?? '',
                'care_items' => $category['itens'] ?? [],
                'frequency' => $category['frequencia'] ?? '',
                'icon_class' => $this->getCareIconClass($category['categoria'] ?? ''),
                'css_class' => $this->getCareCssClass($category['categoria'] ?? ''),
                'priority' => $category['prioridade'] ?? 'medium'
            ];
        }

        return $processed;
    }

    /**
     * Processa sinais de problemas
     */
    private function processProblemSigns(array $signs): array
    {
        if (empty($signs)) {
            return [];
        }

        $processed = [
            'title' => 'Sinais de Problemas nos Pneus',
            'description' => 'Identifique rapidamente problemas relacionados à pressão inadequada.',
            'warning_signs' => []
        ];

        foreach ($signs as $sign) {
            $processed['warning_signs'][] = [
                'symptom' => $sign['sintoma'] ?? '',
                'description' => $sign['descricao'] ?? '',
                'possible_causes' => $sign['causas_possiveis'] ?? [],
                'solutions' => $sign['solucoes'] ?? [],
                'urgency' => $sign['urgencia'] ?? 'medium',
                'icon_class' => $this->getProblemIconClass($sign['sintoma'] ?? ''),
                'css_class' => $this->getProblemCssClass($sign['urgencia'] ?? 'medium')
            ];
        }

        return $processed;
    }

    /**
     * Processa alertas de segurança para motocicletas
     */
    private function processSafetyAlerts(array $alerts): array
    {
        if (empty($alerts)) {
            return [];
        }

        $processed = [
            'title' => 'Alertas Críticos de Segurança para Motociclistas',
            'description' => 'Situações que requerem atenção imediata para sua segurança na pilotagem.',
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
                'css_class' => $this->getAlertCssClass($alert['tipo'] ?? 'warning'),
                'motorcycle_specific' => true
            ];
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção para motocicletas
     */
    private function processMaintenanceTips(array $tips): array
    {
        if (empty($tips)) {
            return [];
        }

        $processed = [
            'title' => 'Manutenção Preventiva dos Pneus',
            'description' => 'Cuidados regulares para prolongar a vida útil e manter a segurança.',
            'categories' => []
        ];

        foreach ($tips as $tip) {
            $processed['categories'][] = [
                'category' => $tip['categoria'] ?? '',
                'title' => $tip['titulo'] ?? '',
                'description' => $tip['descricao'] ?? '',
                'tips' => $tip['dicas'] ?? [],
                'frequency' => $tip['frequencia'] ?? '',
                'icon_class' => $this->getMaintenanceIconClass($tip['categoria'] ?? ''),
                'css_class' => $this->getMaintenanceCssClass($tip['categoria'] ?? ''),
                'difficulty' => $tip['dificuldade'] ?? 'facil'
            ];
        }

        return $processed;
    }

    /**
     * Processa dados SEO específicos para guia de motocicletas
     */
    private function processSeoData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $seoData = $this->article->seo_data ?? [];

        return [
            'title' => $seoData['page_title'] ?? "Como Calibrar Pneus da {$vehicleInfo['full_name']} - Guia para Motociclistas",
            'meta_description' => $seoData['meta_description'] ?? "Guia completo para calibrar pneus da {$vehicleInfo['full_name']}. Procedimento passo a passo, tabela de pressões e dicas de segurança para motociclistas.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "como calibrar pneus {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Como Calibrar Pneus da {$vehicleInfo['full_name']} – Guia para Motociclistas",
            'h2_tags' => $seoData['h2_tags'] ?? [
                'Especificações Técnicas dos Pneus Originais',
                'Tabela de Pressões por Condição de Uso',
                'Procedimento Completo de Calibragem',
                'Recomendações por Estilo de Pilotagem',
                'Comparativo de Impactos na Pilotagem',
                'Pneus Alternativos para Motocicletas',
                'Equipamentos Específicos para Motocicletas',
                'Cuidados Específicos para Motocicletas',
                'Sinais de Problemas nos Pneus',
                'Alertas Críticos de Segurança',
                'Manutenção Preventiva dos Pneus',
                'Perguntas Frequentes'
            ],
            'og_title' => $seoData['og_title'] ?? "Guia: Como Calibrar Pneus da {$vehicleInfo['full_name']}",
            'og_description' => $seoData['og_description'] ?? "Aprenda o procedimento correto para calibrar os pneus da sua {$vehicleInfo['full_name']}. Guia específico para motociclistas.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para motocicletas
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => "Como Calibrar Pneus da {$vehicleFullName}",
            'description' => "Guia passo a passo para calibrar corretamente os pneus da {$vehicleFullName}, incluindo procedimentos específicos para motocicletas, tabelas de pressão e dicas de segurança.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-moto.jpg',
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
                    'name' => 'Calibrador específico para motocicletas'
                ],
                [
                    '@type' => 'HowToSupply', 
                    'name' => 'Compressor portátil'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Medidor de pressão digital'
                ],
                [
                    '@type' => 'HowToTool',
                    'name' => 'Cavalete central ou lateral'
                ]
            ],
            'totalTime' => 'PT10M',
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
     * Processa tópicos relacionados para motocicletas
     */
    private function getRelatedTopics(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        $topics = [];

        // Tópicos específicos por marca
        $topics[] = [
            'title' => 'Manutenção da ' . $vehicleInfo['make'],
            'url' => '/info/manutencao-' . strtolower($vehicleInfo['make']),
            'description' => 'Guia completo de manutenção para motocicletas ' . $vehicleInfo['make']
        ];

        // Tópicos por categoria de motocicleta
        if ($this->isSportMotorcycle()) {
            $topics[] = [
                'title' => 'Pneus Esportivos para Track Day',
                'url' => '/info/pneus-esportivos-track-day',
                'description' => 'Escolha e calibragem para uso esportivo em pista'
            ];
        }

        $topics[] = [
            'title' => 'Manutenção Preventiva de Motocicletas',
            'url' => '/info/manutencao-preventiva-motos',
            'description' => 'Checklist completo de manutenção para motocicletas'
        ];

        $topics[] = [
            'title' => 'Equipamentos de Segurança para Motociclistas',
            'url' => '/info/equipamentos-seguranca-motociclistas',
            'description' => 'Guia completo de EPIs e equipamentos essenciais'
        ];

        return $topics;
    }

    // Métodos auxiliares específicos para motocicletas

    private function isSportMotorcycle(): bool
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        $model = strtolower($this->article->extracted_entities['modelo'] ?? '');
        
        return str_contains($category, 'sport') || 
               str_contains($model, 'gsx') || 
               str_contains($model, 'r1') || 
               str_contains($model, 'cbr') ||
               str_contains($model, '1000');
    }

    private function getEnginePower(): string
    {
        $displacement = $this->article->extracted_entities['motorizacao'] ?? '';
        
        if (str_contains($displacement, '1000')) {
            return 'Alta (150cv+)';
        } elseif (str_contains($displacement, '600')) {
            return 'Média-Alta (90-120cv)';
        } elseif (str_contains($displacement, '300')) {
            return 'Média (40-50cv)';
        } elseif (str_contains($displacement, '160')) {
            return 'Baixa (15-20cv)';
        }
        
        return 'Não especificada';
    }

    private function getMotorcycleConditionCssClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'urbano') || str_contains($situation, 'diário')) {
            return 'bg-green-50 border-green-200';
        }
        
        if (str_contains($situation, 'garupa') || str_contains($situation, 'passageiro')) {
            return 'bg-blue-50 border-blue-200';
        }
        
        if (str_contains($situation, 'esportiv') || str_contains($situation, 'track')) {
            return 'bg-red-50 border-red-200';
        }
        
        if (str_contains($situation, 'viagem') || str_contains($situation, 'rodoviário')) {
            return 'bg-purple-50 border-purple-200';
        }
        
        return 'bg-gray-50 border-gray-200';
    }

    private function getMotorcycleConditionIconClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'urbano')) {
            return 'building';
        }
        
        if (str_contains($situation, 'garupa')) {
            return 'users';
        }
        
        if (str_contains($situation, 'esportiv')) {
            return 'zap';
        }
        
        if (str_contains($situation, 'viagem')) {
            return 'map';
        }
        
        return 'motorcycle';
    }

    private function getMotorcycleStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'settings',
            2 => 'search',
            3 => 'tool',
            4 => 'check-circle'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    private function getMotorcycleStepCssClass(int $stepNumber): string
    {
        $classes = [
            1 => 'step-preparation',
            2 => 'step-verification',
            3 => 'step-calibration',
            4 => 'step-completion'
        ];

        return $classes[$stepNumber] ?? 'step-default';
    }

    private function getStepSafetyNote(int $stepNumber): string
    {
        $notes = [
            1 => 'Sempre use cavalete central quando disponível',
            2 => 'Verifique com pneus frios (moto parada há 3+ horas)',
            3 => 'Nunca exceda a pressão máxima especificada',
            4 => 'Teste em local seguro antes de pegar o trânsito'
        ];

        return $notes[$stepNumber] ?? '';
    }

    private function getMotorcycleUsageIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'urbano')) {
            return 'building';
        }
        
        if (str_contains($category, 'rodoviário')) {
            return 'truck';
        }
        
        if (str_contains($category, 'esportiv')) {
            return 'zap';
        }
        
        if (str_contains($category, 'track')) {
            return 'target';
        }
        
        return 'motorcycle';
    }

    private function getMotorcycleUsageCssClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'esportiv') || str_contains($category, 'track')) {
            return 'bg-red-50 border-red-200';
        }
        
        if (str_contains($category, 'urbano')) {
            return 'bg-green-50 border-green-200';
        }
        
        if (str_contains($category, 'rodoviário')) {
            return 'bg-blue-50 border-blue-200';
        }
        
        return 'bg-gray-50 border-gray-200';
    }

    private function getUsageSafetyLevel(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'track') || str_contains($category, 'esportiv')) {
            return 'alto';
        }
        
        if (str_contains($category, 'rodoviário')) {
            return 'medio';
        }
        
        return 'baixo';
    }

    private function getImpactScenarioClass(string $type): string
    {
        $classes = [
            'baixa' => 'bg-red-50 border-red-200',
            'ideal' => 'bg-green-50 border-green-200',
            'alta' => 'bg-yellow-50 border-yellow-200'
        ];

        return $classes[$type] ?? 'bg-gray-50 border-gray-200';
    }

    private function getImpactScenarioIcon(string $type): string
    {
        $icons = [
            'baixa' => '⚠️',
            'ideal' => '✅',
            'alta' => '⚡'
        ];

        return $icons[$type] ?? 'ℹ️';
    }

    private function getCompatibilityWarning(): string
    {
        if ($this->isSportMotorcycle()) {
            return 'Para motocicletas esportivas, use APENAS pneus com especificação Z ou W. Nunca misture compostos diferentes.';
        }
        
        return 'Mantenha sempre a mesma marca e modelo entre dianteiro e traseiro para motocicletas.';
    }

    private function getMotorcycleEquipmentIconClass(string $item): string
    {
        $item = strtolower($item);
        
        if (str_contains($item, 'calibrador')) {
            return 'gauge';
        }
        
        if (str_contains($item, 'compressor')) {
            return 'wind';
        }
        
        if (str_contains($item, 'cavalete')) {
            return 'settings';
        }
        
        if (str_contains($item, 'luva')) {
            return 'hand';
        }
        
        return 'tool';
    }

    private function isMotorcycleSpecificEquipment(string $item): bool
    {
        $item = strtolower($item);
        
        return str_contains($item, 'cavalete') || 
               str_contains($item, 'moto') ||
               str_contains($item, 'motocicleta');
    }

    private function getCareIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'limpeza')) {
            return 'droplets';
        }
        
        if (str_contains($category, 'inspeção')) {
            return 'eye';
        }
        
        if (str_contains($category, 'armazenamento')) {
            return 'archive';
        }
        
        return 'shield';
    }

    private function getCareCssClass(string $category): string
    {
        return 'bg-blue-50 border-blue-200';
    }

    private function getProblemIconClass(string $symptom): string
    {
        $symptom = strtolower($symptom);
        
        if (str_contains($symptom, 'desgaste')) {
            return 'trending-down';
        }
        
        if (str_contains($symptom, 'vibração')) {
            return 'activity';
        }
        
        if (str_contains($symptom, 'ruído')) {
            return 'volume-2';
        }
        
        return 'alert-triangle';
    }

    private function getProblemCssClass(string $urgency): string
    {
        $urgencyMap = [
            'alta' => 'bg-red-50 border-red-500',
            'media' => 'bg-yellow-50 border-yellow-400',
            'baixa' => 'bg-blue-50 border-blue-400'
        ];

        return $urgencyMap[strtolower($urgency)] ?? 'bg-gray-50 border-gray-400';
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

    private function getMaintenanceIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'verificação')) {
            return 'search';
        }
        
        if (str_contains($category, 'limpeza')) {
            return 'droplets';
        }
        
        if (str_contains($category, 'rotação')) {
            return 'rotate-cw';
        }
        
        return 'tool';
    }

    private function getMaintenanceCssClass(string $category): string
    {
        return 'bg-green-50 border-green-200';
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