<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class IdealTirePressureMotorcycleViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'ideal_tire_pressure_motorcycle';

    /**
     * Processa dados específicos do template de pressão ideal para motocicletas
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Especificações dos pneus
        $this->processedData['tire_specifications'] = $this->processMotorcycleTireSpecifications($content['especificacoes_pneus'] ?? []);

        // Tabela de pressões específica para motos
        $this->processedData['pressure_table'] = $this->processMotorcyclePressureTable($content['tabela_pressoes'] ?? []);

        // Tabela de conversão de unidades
        $this->processedData['unit_conversion'] = $this->processUnitConversion($content['conversao_unidades'] ?? []);

        // Localização das informações
        $this->processedData['information_location'] = $this->processInformationLocation($content['localizacao_informacoes'] ?? []);

        // Benefícios da calibragem específicos para motos
        $this->processedData['calibration_benefits'] = $this->processMotorcycleCalibrationBenefits($content['beneficios_calibragem'] ?? []);

        // Considerações especiais para motocicletas
        $this->processedData['special_considerations'] = $this->processSpecialConsiderations($content['consideracoes_especiais'] ?? []);

        // Dicas de manutenção específicas para motos
        $this->processedData['maintenance_tips'] = $this->processMotorcycleMaintenanceTips($content['dicas_manutencao'] ?? []);

        // Alertas críticos para motocicletas
        $this->processedData['critical_alerts'] = $this->processCriticalAlerts($content['alertas_criticos'] ?? []);

        // Procedimento de calibragem para motos
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);

        // Perguntas frequentes
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];

        // Considerações finais
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Informações do veículo formatadas
        $this->processedData['vehicle_info'] = $this->processMotorcycleVehicleInfo();

        // Dados estruturados para SEO
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
    }

    /**
     * Processa especificações dos pneus para motocicletas
     */
    private function processMotorcycleTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        $processed = [];

        // Pneu dianteiro
        if (!empty($specs['pneu_dianteiro'])) {
            $processed['front_tire'] = [
                'size' => $specs['pneu_dianteiro']['medida_original'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'construction' => $specs['pneu_dianteiro']['tipo_construcao'] ?? '',
                'original_brands' => $specs['pneu_dianteiro']['marca_original'] ?? ''
            ];
        }

        // Pneu traseiro
        if (!empty($specs['pneu_traseiro'])) {
            $processed['rear_tire'] = [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'construction' => $specs['pneu_traseiro']['tipo_construcao'] ?? '',
                'original_brands' => $specs['pneu_traseiro']['marca_original'] ?? ''
            ];
        }

        return $processed;
    }

    /**
     * Processa tabela de pressões específica para motocicletas
     */
    private function processMotorcyclePressureTable(array $table): array
    {
        $processed = [
            'official_pressures' => [],
            'special_conditions' => []
        ];

        // Pressões oficiais
        if (!empty($table['pressoes_oficiais'])) {
            $processed['official_pressures'] = [
                'solo_rider' => [
                    'front' => $table['pressoes_oficiais']['piloto_solo']['dianteira'] ?? '',
                    'rear' => $table['pressoes_oficiais']['piloto_solo']['traseira'] ?? '',
                    'observation' => $table['pressoes_oficiais']['piloto_solo']['observacao'] ?? ''
                ],
                'with_passenger' => [
                    'front' => $table['pressoes_oficiais']['piloto_garupa']['dianteira'] ?? '',
                    'rear' => $table['pressoes_oficiais']['piloto_garupa']['traseira'] ?? '',
                    'observation' => $table['pressoes_oficiais']['piloto_garupa']['observacao'] ?? ''
                ]
            ];
        }

        // Condições especiais
        if (!empty($table['condicoes_especiais']) && is_array($table['condicoes_especiais'])) {
            foreach ($table['condicoes_especiais'] as $condition) {
                if (!empty($condition['situacao'])) {
                    $processed['special_conditions'][] = [
                        'situation' => $condition['situacao'],
                        'terrain' => $condition['terreno'] ?? '',
                        'front_pressure' => $condition['pressao_dianteira'] ?? '',
                        'rear_pressure' => $condition['pressao_traseira'] ?? '',
                        'ideal_temperature' => $condition['temperatura_ideal'] ?? '',
                        'observation' => $condition['observacao'] ?? '',
                        'situation_class' => $this->getMotorcycleSituationCssClass($condition['situacao']),
                        'icon_class' => $this->getMotorcycleSituationIconClass($condition['situacao'])
                    ];
                }
            }
        }

        return $processed;
    }

    /**
     * Processa tabela de conversão de unidades
     */
    private function processUnitConversion(array $conversion): array
    {
        $processed = [
            'conversion_table' => [],
            'observation' => $conversion['observacao'] ?? ''
        ];

        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? '',
                    'is_recommended' => $this->isRecommendedMotorcyclePressure($row['psi'] ?? ''),
                    'highlight_class' => $this->getMotorcyclePressureHighlightClass($row['psi'] ?? '')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa localização das informações
     */
    private function processInformationLocation(array $location): array
    {
        if (empty($location)) {
            return [];
        }

        return [
            'owner_manual' => [
                'location' => $location['manual_proprietario']['localizacao'] ?? '',
                'section' => $location['manual_proprietario']['secao'] ?? '',
                'approximate_page' => $location['manual_proprietario']['pagina_aproximada'] ?? ''
            ],
            'motorcycle_label' => [
                'main_location' => $location['etiqueta_moto']['localizacao_principal'] ?? '',
                'alternative_locations' => $location['etiqueta_moto']['localizacoes_alternativas'] ?? []
            ],
            'important_tip' => $location['dica_importante'] ?? '',
            'visual_guide' => $this->generateMotorcycleVisualGuide($location)
        ];
    }

    /**
     * Processa benefícios da calibragem específicos para motos
     */
    private function processMotorcycleCalibrationBenefits(array $benefits): array
    {
        if (empty($benefits)) {
            return [];
        }

        $processed = [];

        foreach ($benefits as $key => $benefit) {
            if (!empty($benefit['titulo'])) {
                $processed[] = [
                    'category' => $key,
                    'title' => $benefit['titulo'],
                    'description' => $benefit['descricao'] ?? '',
                    'aspects' => $benefit['aspectos'] ?? [],
                    'financial_impact' => $benefit['impacto_financeiro'] ?? '',
                    'estimated_savings' => $benefit['economia_estimada'] ?? '',
                    'icon_class' => $this->getMotorcycleBenefitIconClass($key),
                    'color_class' => $this->getMotorcycleBenefitColorClass($key),
                    'priority' => $this->getMotorcycleBenefitPriority($key)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa considerações especiais para motocicletas
     */
    private function processSpecialConsiderations(array $considerations): array
    {
        if (empty($considerations)) {
            return [];
        }

        $processed = [];

        foreach ($considerations as $key => $consideration) {
            if (!empty($consideration['titulo'])) {
                $processed[] = [
                    'category' => $key,
                    'title' => $consideration['titulo'],
                    'description' => $consideration['descricao'] ?? '',
                    'factors' => $consideration['fatores'] ?? [],
                    'orientations' => $consideration['orientacoes'] ?? [],
                    'types' => $consideration['tipos'] ?? [],
                    'icon_class' => $this->getConsiderationIconClass($key),
                    'importance' => $this->getConsiderationImportance($key)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa dicas de manutenção específicas para motos
     */
    private function processMotorcycleMaintenanceTips(array $tips): array
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
                    'importance' => $tip['importancia'] ?? 'normal',
                    'priority_level' => $this->getMotorcycleTipPriorityLevel($tip['importancia'] ?? 'normal'),
                    'icon_class' => $this->getMotorcycleTipIconClass($tip['categoria']),
                    'color_class' => $this->getMotorcycleTipColorClass($tip['importancia'] ?? 'normal')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa alertas críticos para motocicletas
     */
    private function processCriticalAlerts(array $alerts): array
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
                    'consequence' => $alert['consequencia'] ?? '',
                    'severity_class' => $this->getMotorcycleAlertSeverityClass($alert['tipo'] ?? 'info'),
                    'icon_class' => $this->getMotorcycleAlertIconClass($alert['tipo'] ?? 'info'),
                    'border_class' => $this->getMotorcycleAlertBorderClass($alert['tipo'] ?? 'info'),
                    'is_critical' => strtolower($alert['tipo'] ?? '') === 'crítico'
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa procedimento de calibragem para motos
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
                    'details' => $step['detalhes'] ?? [],
                    'icon_class' => $this->getMotorcycleStepIconClass($step['numero'] ?? 1),
                    'step_class' => $this->getMotorcycleStepClass($step['numero'] ?? 1)
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa informações do veículo (motocicleta)
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function processMotorcycleVehicleInfo(): array
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'full_name' => $this->getMotorcycleFullName(),
            'make' => $vehicleInfo['marca'] ?? '',
            'model' => $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleInfo['ano'] ?? '',
            'displacement' => $vehicleInfo['motorizacao'] ?? '', // cilindrada geralmente em motorizacao
            'type' => $vehicleInfo['categoria'] ?? '',
            'category' => 'motocicleta',
            'image_url' => $this->getMotorcycleImageUrl(),
            'is_sport' => $this->isSportMotorcycle(),
            'is_naked' => $this->isNakedMotorcycle(),
            'is_touring' => $this->isTouringMotorcycle(),
            'engine_size_category' => $this->getEngineSizeCategory()
        ];
    }

    /**
     * Obtém classe CSS para situação da motocicleta
     */
    private function getMotorcycleSituationCssClass(string $situation): string
    {
        $situation = strtolower($situation);

        if (str_contains($situation, 'urbana') || str_contains($situation, 'cidade')) {
            return 'moto-situation-urban';
        }

        if (str_contains($situation, 'rodoviária') || str_contains($situation, 'viagem')) {
            return 'moto-situation-highway';
        }

        if (str_contains($situation, 'esportiva') || str_contains($situation, 'curva')) {
            return 'moto-situation-sport';
        }

        if (str_contains($situation, 'chuva') || str_contains($situation, 'molhado')) {
            return 'moto-situation-rain';
        }

        return 'moto-situation-default';
    }

    /**
     * Obtém classe de ícone para situação da motocicleta
     */
    private function getMotorcycleSituationIconClass(string $situation): string
    {
        $situation = strtolower($situation);

        if (str_contains($situation, 'urbana')) return 'home';
        if (str_contains($situation, 'rodoviária')) return 'map';
        if (str_contains($situation, 'esportiva')) return 'zap';
        if (str_contains($situation, 'chuva')) return 'cloud-rain';

        return 'navigation';
    }

    /**
     * Verifica se é pressão recomendada para motocicleta
     */
    private function isRecommendedMotorcyclePressure(string $psi): bool
    {
        $recommendedPressures = ['36', '42']; // Pressões padrão comuns para motos
        return in_array($psi, $recommendedPressures);
    }

    /**
     * Obtém classe de destaque para pressão
     */
    private function getMotorcyclePressureHighlightClass(string $psi): string
    {
        return $this->isRecommendedMotorcyclePressure($psi) ? 'highlight-recommended' : '';
    }

    /**
     * Gera guia visual para motocicleta
     */
    private function generateMotorcycleVisualGuide(array $location): array
    {
        return [
            'manual_access' => [
                'title' => 'Acesso ao Manual',
                'steps' => [
                    'Levante o assento da moto',
                    'Localize o compartimento de documentos',
                    'Consulte seção "Especificações Técnicas"'
                ]
            ],
            'label_search' => [
                'title' => 'Busca da Etiqueta',
                'steps' => [
                    'Olhe no braço da suspensão traseira',
                    'Verifique próximo ao número do chassi',
                    'Em caso de dúvida, consulte concessionária'
                ]
            ]
        ];
    }

    /**
     * Obtém classe de ícone para benefício da motocicleta
     */
    private function getMotorcycleBenefitIconClass(string $benefit): string
    {
        $iconMap = [
            'seguranca' => 'shield',
            'performance' => 'zap',
            'economia' => 'dollar-sign',
            'durabilidade' => 'clock'
        ];

        return $iconMap[$benefit] ?? 'check-circle';
    }

    /**
     * Obtém classe de cor para benefício da motocicleta
     */
    private function getMotorcycleBenefitColorClass(string $benefit): string
    {
        $colorMap = [
            'seguranca' => 'red',
            'performance' => 'blue',
            'economia' => 'green',
            'durabilidade' => 'purple'
        ];

        return $colorMap[$benefit] ?? 'gray';
    }

    /**
     * Obtém prioridade do benefício para motocicleta
     */
    private function getMotorcycleBenefitPriority(string $benefit): string
    {
        $priorityMap = [
            'seguranca' => 'crítica',
            'performance' => 'alta',
            'economia' => 'média',
            'durabilidade' => 'média'
        ];

        return $priorityMap[$benefit] ?? 'normal';
    }

    /**
     * Obtém classe de ícone para consideração
     */
    private function getConsiderationIconClass(string $category): string
    {
        $iconMap = [
            'temperatura' => 'thermometer',
            'carga' => 'package',
            'estilo_pilotagem' => 'target'
        ];

        return $iconMap[$category] ?? 'info';
    }

    /**
     * Obtém importância da consideração
     */
    private function getConsiderationImportance(string $category): string
    {
        $importanceMap = [
            'temperatura' => 'crítica',
            'carga' => 'alta',
            'estilo_pilotagem' => 'média'
        ];

        return $importanceMap[$category] ?? 'normal';
    }

    /**
     * Obtém nível de prioridade da dica para motocicleta
     */
    private function getMotorcycleTipPriorityLevel(string $importance): int
    {
        $levelMap = [
            'crítica' => 5,
            'alta' => 4,
            'média' => 3,
            'recomendada' => 2,
            'normal' => 1
        ];

        return $levelMap[strtolower($importance)] ?? 1;
    }

    /**
     * Obtém classe de ícone para dica da motocicleta
     */
    private function getMotorcycleTipIconClass(string $category): string
    {
        $category = strtolower($category);

        if (str_contains($category, 'verificação') || str_contains($category, 'semanal')) {
            return 'calendar';
        }

        if (str_contains($category, 'condições') || str_contains($category, 'ideais')) {
            return 'sun';
        }

        if (str_contains($category, 'equipamentos')) {
            return 'tool';
        }

        return 'info';
    }

    /**
     * Obtém classe de cor para dica da motocicleta
     */
    private function getMotorcycleTipColorClass(string $importance): string
    {
        $colorMap = [
            'crítica' => 'red',
            'alta' => 'orange',
            'média' => 'yellow',
            'recomendada' => 'blue',
            'normal' => 'gray'
        ];

        return $colorMap[strtolower($importance)] ?? 'gray';
    }

    /**
     * Obtém classe de severidade para alerta da motocicleta
     */
    private function getMotorcycleAlertSeverityClass(string $type): string
    {
        $severityMap = [
            'crítico' => 'moto-alert-critical',
            'importante' => 'moto-alert-important',
            'atenção' => 'moto-alert-warning'
        ];

        return $severityMap[strtolower($type)] ?? 'moto-alert-info';
    }

    /**
     * Obtém classe de ícone para alerta da motocicleta
     */
    private function getMotorcycleAlertIconClass(string $type): string
    {
        $iconMap = [
            'crítico' => 'alert-triangle',
            'importante' => 'alert-circle',
            'atenção' => 'info'
        ];

        return $iconMap[strtolower($type)] ?? 'help-circle';
    }

    /**
     * Obtém classe de borda para alerta da motocicleta
     */
    private function getMotorcycleAlertBorderClass(string $type): string
    {
        $borderMap = [
            'crítico' => 'border-red-600',
            'importante' => 'border-orange-500',
            'atenção' => 'border-yellow-500'
        ];

        return $borderMap[strtolower($type)] ?? 'border-blue-500';
    }

    /**
     * Obtém classe de ícone para passo da motocicleta
     */
    private function getMotorcycleStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'settings',
            2 => 'search',
            3 => 'tool'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    /**
     * Obtém classe do passo para motocicleta
     */
    private function getMotorcycleStepClass(int $stepNumber): string
    {
        $classes = [
            1 => 'step-preparation',
            2 => 'step-verification',
            3 => 'step-adjustment'
        ];

        return $classes[$stepNumber] ?? 'step-default';
    }

    /**
     * Obtém nome completo da motocicleta
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getMotorcycleFullName(): string
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];

        // Validação básica - retorna vazio se não tiver marca ou modelo
        if (empty($vehicleInfo['marca']) || empty($vehicleInfo['modelo'])) {
            return '';
        }

        $make = $vehicleInfo['marca'] ?? '';
        $model = $vehicleInfo['modelo'] ?? '';
        $year = $vehicleInfo['ano'] ?? '';

        return trim("{$make} {$model} {$year}");
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
     * Verifica se é moto esportiva
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isSportMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'sport') || str_contains($type, 'esportiva');
    }

    /**
     * Verifica se é moto naked
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isNakedMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'naked') || str_contains($type, 'street');
    }

    /**
     * Verifica se é moto touring
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function isTouringMotorcycle(): bool
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'touring') || str_contains($type, 'viagem');
    }

    /**
     * Obtém categoria do tamanho do motor
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getEngineSizeCategory(): string
    {
        // CORREÇÃO: Usar extracted_entities e extrair cilindrada da motorizacao
        $motorizacao = $this->article->extracted_entities['motorizacao'] ?? '';

        // Extrai números da motorização (ex: "650cc" -> 650)
        preg_match('/(\d+)/', $motorizacao, $matches);
        $displacement = isset($matches[1]) ? intval($matches[1]) : 0;

        if ($displacement <= 150) return 'pequeno';
        if ($displacement <= 300) return 'médio';
        if ($displacement <= 600) return 'grande';
        if ($displacement <= 1000) return 'super';

        return 'premium';
    }

    /**
     * Processa dados SEO específicos para motocicletas
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getMotorcycleFullName();
        $frontPressure = $this->processedData['pressure_table']['official_pressures']['solo_rider']['front'] ?? '';
        $rearPressure = $this->processedData['pressure_table']['official_pressures']['solo_rider']['rear'] ?? '';

        $pressureDisplay = $frontPressure && $rearPressure ? "{$frontPressure} (dianteira) / {$rearPressure} (traseira)" : '';

        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'title' => $this->article->title ?? "Pressão Ideal para Pneus da {$vehicleFullName} - Guia Completo",
            'meta_description' => $this->article->meta_description ?? "Pressões ideais para pneus da {$vehicleFullName}. Solo/garupa: {$pressureDisplay}. Dicas específicas para motociclistas e tabela de conversão PSI.",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "pressão ideal pneus {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'og_title' => "Pressão Ideal para Pneus da {$vehicleFullName} - Motocicleta",
            'og_description' => "Guia específico para motociclistas: pressões ideais, dicas de segurança e calibragem da {$vehicleFullName}.",
            'og_image' => $this->processedData['vehicle_info']['image_url'],
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para motocicletas
     * MÉTODO CORRIGIDO: Usa extracted_entities e valida dados antes de gerar schema
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
            'name' => "Pressão Ideal para Pneus da {$vehicleFullName}",
            'description' => "Guia específico de pressões ideais para a motocicleta {$vehicleFullName}, incluindo condições especiais e dicas de segurança.",
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
            'about' => [
                '@type' => 'Thing',
                'name' => 'Calibragem de Pneus para Motocicletas',
                'description' => 'Pressões ideais e manutenção de pneus para motocicletas'
            ]
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleData['marca']) && !empty($vehicleData['modelo'])) {
            $structuredData['mainEntity'] = [
                '@type' => 'Motorcycle',
                'name' => 'Pressão ideal para ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
                'brand' => $vehicleData['marca'],
                'model' => $vehicleData['modelo']
            ];

            // Adiciona ano se existir
            if (!empty($vehicleData['ano'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleData['ano'];
            }
        }

        return $structuredData;
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
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/pressao-ideal-moto.jpg';
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
