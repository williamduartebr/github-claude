<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class TirePressureGuideMotorcycleViewModel extends TemplateViewModel
{
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

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Especificações oficiais dos pneus
        $this->processedData['tire_specifications'] = $this->processTireSpecifications($content['especificacoes_oficiais'] ?? []);

        // Tabela de pressões por condição de uso (adaptada para motos)
        $this->processedData['pressure_table'] = $this->processPressureTable($content['tabela_pressoes'] ?? []);

        // Procedimento de calibragem específico para motos
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);

        // Considerações especiais para motocicletas
        $this->processedData['motorcycle_considerations'] = $this->processMotorcycleConsiderations($content['consideracoes_moto'] ?? []);

        // Impactos da calibragem específicos para motos
        $this->processedData['calibration_impacts'] = $this->processCalibrationImpacts($content['impactos_calibragem'] ?? []);

        // Dicas de manutenção para motocicletas
        $this->processedData['maintenance_tips'] = $this->processMaintenanceTips($content['dicas_manutencao'] ?? []);

        // Alertas de segurança específicos para motos
        $this->processedData['safety_alerts'] = $this->processSafetyAlerts($content['alertas_seguranca'] ?? []);

        // Dicas para diferentes tipos de pilotagem
        $this->processedData['riding_tips'] = $this->processRidingTips($content['dicas_pilotagem'] ?? []);

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
     * Processa especificações oficiais dos pneus para motocicletas
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
                'pressure_solo' => $specs['pneu_dianteiro']['pressao_solo'] ?? '',
                'pressure_garupa' => $specs['pneu_dianteiro']['pressao_garupa'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'max_capacity' => $specs['pneu_dianteiro']['capacidade_carga'] ?? '',
                'construction_type' => $specs['pneu_dianteiro']['tipo_construcao'] ?? ''
            ];
        }

        // Pneu traseiro
        if (!empty($specs['pneu_traseiro'])) {
            $processed['rear_tire'] = [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'pressure_solo' => $specs['pneu_traseiro']['pressao_solo'] ?? '',
                'pressure_garupa' => $specs['pneu_traseiro']['pressao_garupa'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'max_capacity' => $specs['pneu_traseiro']['capacidade_carga'] ?? '',
                'construction_type' => $specs['pneu_traseiro']['tipo_construcao'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Processa tabela de pressões por condição de uso para motos
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
                    'rider_type' => $condition['tipo_piloto'] ?? '',
                    'terrain' => $condition['terreno'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'temperature_note' => $condition['nota_temperatura'] ?? '',
                    'css_class' => $this->getMotorcycleSituationCssClass($condition['situacao'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem específico para motos
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
                    'safety_note' => $step['nota_seguranca'] ?? '',
                    'icon_class' => $this->getMotorcycleStepIconClass($step['numero'] ?? 1)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa considerações especiais para motocicletas
     */
    private function processMotorcycleConsiderations(array $considerations): array
    {
        if (empty($considerations) || !is_array($considerations)) {
            return [];
        }

        $processed = [];

        foreach ($considerations as $key => $consideration) {
            if (!empty($consideration['titulo'])) {
                $processed[] = [
                    'category' => $key,
                    'title' => $consideration['titulo'],
                    'description' => $consideration['descricao'] ?? '',
                    'items' => $consideration['itens'] ?? [],
                    'importance' => $consideration['importancia'] ?? 'media',
                    'icon_class' => $this->getConsiderationIconClass($key)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa dicas para diferentes tipos de pilotagem
     */
    private function processRidingTips(array $tips): array
    {
        if (empty($tips) || !is_array($tips)) {
            return [];
        }

        $processed = [];

        foreach ($tips as $tip) {
            if (!empty($tip['tipo_pilotagem'])) {
                $processed[] = [
                    'riding_type' => $tip['tipo_pilotagem'],
                    'front_pressure' => $tip['pressao_dianteira'] ?? '',
                    'rear_pressure' => $tip['pressao_traseira'] ?? '',
                    'adjustments' => $tip['ajustes'] ?? [],
                    'warnings' => $tip['avisos'] ?? [],
                    'icon_class' => $this->getRidingTypeIconClass($tip['tipo_pilotagem'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa impactos da calibragem específicos para motos
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
                'stability' => $impacts['sub_calibrado']['estabilidade'] ?? '',
                'braking' => $impacts['sub_calibrado']['frenagem'] ?? '',
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
                'grip' => $impacts['super_calibrado']['aderencia'] ?? '',
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
                'performance' => $impacts['calibragem_ideal']['performance'] ?? '',
                'durability' => $impacts['calibragem_ideal']['durabilidade'] ?? '',
                'severity_class' => 'optimal'
            ];
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção para motocicletas
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
                    'frequency' => $tip['frequencia'] ?? '',
                    'importance' => $tip['importancia'] ?? 'media',
                    'icon_class' => $this->getMotorcycleTipIconClass($tip['categoria'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa alertas de segurança específicos para motos
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
                    'motorcycle_specific' => $alert['especifico_moto'] ?? true,
                    'severity_class' => $this->getAlertSeverityClass($alert['tipo'] ?? 'info'),
                    'icon_class' => $this->getAlertIconClass($alert['tipo'] ?? 'info')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa informações do veículo (moto)
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
            'category' => $vehicleInfo['categoria'] ?? 'motocicleta',
            'engine' => $vehicleInfo['motorizacao'] ?? '',
            'displacement' => $vehicleInfo['motorizacao'] ?? '', // cilindrada geralmente está em motorizacao
            'type' => $vehicleInfo['categoria'] ?? '',
            'image_url' => $this->getMotorcycleImageUrl(),
            'is_sport' => $this->isSportMotorcycle(),
            'is_touring' => $this->isTouringMotorcycle(),
            'is_offroad' => $this->isOffroadMotorcycle()
        ];
    }

    /**
     * Obtém classe CSS para situação da tabela de pressões (moto)
     */
    private function getMotorcycleSituationCssClass(string $situation): string
    {
        $situation = strtolower($situation);
        
        if (str_contains($situation, 'urbano') || str_contains($situation, 'cidade')) {
            return 'moto-situation-urban';
        }
        
        if (str_contains($situation, 'estrada') || str_contains($situation, 'rodovia')) {
            return 'moto-situation-highway';
        }
        
        if (str_contains($situation, 'garupa') || str_contains($situation, 'passageiro')) {
            return 'moto-situation-passenger';
        }
        
        if (str_contains($situation, 'trilha') || str_contains($situation, 'off')) {
            return 'moto-situation-offroad';
        }
        
        return 'moto-situation-default';
    }

    /**
     * Obtém classe de ícone para passos do procedimento (moto)
     */
    private function getMotorcycleStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'thermometer',
            2 => 'book-open',
            3 => 'tool',
            4 => 'target',
            5 => 'check-circle'
        ];
        
        return $icons[$stepNumber] ?? 'circle';
    }

    /**
     * Obtém classe de ícone para considerações especiais
     */
    private function getConsiderationIconClass(string $category): string
    {
        $category = strtolower($category);
        
        $iconMap = [
            'temperatura' => 'thermometer',
            'carga' => 'package',
            'pilotagem' => 'zap',
            'terreno' => 'map',
            'velocidade' => 'trending-up'
        ];
        
        return $iconMap[$category] ?? 'info';
    }

    /**
     * Obtém classe de ícone para tipos de pilotagem
     */
    private function getRidingTypeIconClass(string $ridingType): string
    {
        $type = strtolower($ridingType);
        
        if (str_contains($type, 'urbano') || str_contains($type, 'cidade')) {
            return 'home';
        }
        
        if (str_contains($type, 'esportivo') || str_contains($type, 'sport')) {
            return 'zap';
        }
        
        if (str_contains($type, 'touring') || str_contains($type, 'viagem')) {
            return 'map';
        }
        
        if (str_contains($type, 'trilha') || str_contains($type, 'off')) {
            return 'mountain';
        }
        
        return 'navigation';
    }

    /**
     * Obtém classe de ícone para dicas de manutenção (moto)
     */
    private function getMotorcycleTipIconClass(string $category): string
    {
        $category = strtolower($category);
        
        if (str_contains($category, 'verificação') || str_contains($category, 'inspeção')) {
            return 'search';
        }
        
        if (str_contains($category, 'cuidados') || str_contains($category, 'proteção')) {
            return 'shield';
        }
        
        if (str_contains($category, 'manutenção') || str_contains($category, 'preventiva')) {
            return 'wrench';
        }
        
        if (str_contains($category, 'emergência') || str_contains($category, 'viagem')) {
            return 'alert-triangle';
        }
        
        return 'tool';
    }

    /**
     * Verifica se é moto esportiva
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isSportMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($category, 'esportiva') || 
               str_contains($type, 'sport') || 
               str_contains($type, 'esportiva');
    }

    /**
     * Verifica se é moto touring
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isTouringMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($category, 'touring') || 
               str_contains($type, 'touring') || 
               str_contains($type, 'viagem');
    }

    /**
     * Verifica se é moto off-road
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isOffroadMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($category, 'off') || 
               str_contains($type, 'trail') || 
               str_contains($type, 'enduro') ||
               str_contains($type, 'trilha');
    }

    /**
     * Obtém URL da imagem da motocicleta
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getMotorcycleImageUrl(): string
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $makeSlug = strtolower($vehicleInfo['marca'] ?? '');
        $modelSlug = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));
        $year = $vehicleInfo['ano'] ?? '';
        
        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/motorcycles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Processa dados SEO específicos para motocicletas
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $frontPressure = $this->processedData['tire_specifications']['front_tire']['pressure_solo'] ?? '';
        $rearPressure = $this->processedData['tire_specifications']['rear_tire']['pressure_solo'] ?? '';
        
        $pressureDisplay = $frontPressure && $rearPressure ? "{$frontPressure} (dianteira) / {$rearPressure} (traseira)" : '';
        
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        
        return [
            'title' => $this->article->title ?? "Calibragem do Pneu da {$vehicleFullName} - Pressão Ideal",
            'meta_description' => $this->article->meta_description ?? "Saiba a pressão ideal para calibrar os pneus da {$vehicleFullName}. Pressões recomendadas: {$pressureDisplay}. Guia completo com dicas de segurança e performance!",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "calibragem pneu {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'og_title' => "Calibragem do Pneu da {$vehicleFullName} - Guia para Motocicletas",
            'og_description' => "Pressões ideais, procedimento específico para motos e dicas importantes para calibrar os pneus da {$vehicleFullName}.",
            'og_image' => $this->processedData['vehicle_info']['image_url'],
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para motocicletas
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
            'name' => "Como Calibrar os Pneus da {$vehicleFullName}",
            'description' => "Guia completo sobre calibragem de pneus da {$vehicleFullName}, incluindo pressões recomendadas, procedimento específico para motocicletas e dicas de segurança.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? $this->getDefaultMotorcycleImage(),
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
            'totalTime' => 'PT10M',
            'prepTime' => 'PT3M',
            'performTime' => 'PT7M',
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
                    'name' => 'Compressor de ar portátil'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Calibrador digital'
                ],
                [
                    '@type' => 'HowToTool',
                    'name' => 'Manual do proprietário'
                ]
            ],
            'step' => $this->buildMotorcycleHowToSteps()
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleData['marca']) && !empty($vehicleData['modelo'])) {
            $structuredData['about'] = [
                '@type' => 'Motorcycle',
                'name' => 'Guia de calibragem para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
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
     * Constrói os passos do HowTo específicos para motocicletas
     */
    private function buildMotorcycleHowToSteps(): array
    {
        $steps = [];
        $stepNumber = 1;

        // Passos específicos para motocicletas
        $motorcycleSteps = [
            [
                'name' => 'Verificação de Segurança',
                'text' => 'Posicione a motocicleta em superfície plana, com motor frio há pelo menos 2 horas'
            ],
            [
                'name' => 'Consulta do Manual',
                'text' => 'Verifique as pressões recomendadas no manual do proprietário ou etiqueta na moto'
            ],
            [
                'name' => 'Preparação do Equipamento',
                'text' => 'Use calibrador digital específico para motos, com precisão de 0,1 PSI'
            ],
            [
                'name' => 'Medição da Pressão',
                'text' => 'Remova a tampa da válvula e meça a pressão rapidamente para evitar perda de ar'
            ],
            [
                'name' => 'Ajuste da Pressão',
                'text' => 'Calibre primeiro o pneu dianteiro, depois o traseiro, considerando piloto solo ou com garupa'
            ],
            [
                'name' => 'Verificação Final',
                'text' => 'Confira novamente as pressões e recoloque as tampas das válvulas'
            ]
        ];

        foreach ($motorcycleSteps as $step) {
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
     * Obtém URL canônica do artigo
     */
    private function getCanonicalUrl(): string
    {
        return $this->article->canonical_url ?? route('info.article.show', $this->article->slug);
    }

    /**
     * Obtém imagem padrão para motocicletas
     */
    private function getDefaultMotorcycleImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/calibragem-moto.jpg';
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