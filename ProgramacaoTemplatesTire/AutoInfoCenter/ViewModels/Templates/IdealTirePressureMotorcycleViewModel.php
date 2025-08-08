<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;

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

        $this->processedData['introduction'] = $content['introducao'] ?? '';
        $this->processedData['tire_specifications'] = $this->processMotorcycleTireSpecifications($content['especificacoes_pneus'] ?? []);
        $this->processedData['pressure_table'] = $this->processMotorcyclePressureTable($content['tabela_pressoes'] ?? []);
        $this->processedData['information_location'] = $this->processInformationLocation($content['localizacao_informacoes'] ?? []);
        $this->processedData['unit_conversion'] = $this->processUnitConversion($content['conversao_unidades'] ?? []);
        $this->processedData['special_considerations'] = $this->processSpecialConsiderations($content['consideracoes_especiais'] ?? []);
        $this->processedData['calibration_benefits'] = $this->processMotorcycleCalibrationBenefits($content['beneficios_calibragem'] ?? []);
        $this->processedData['maintenance_tips'] = $this->processMotorcycleMaintenanceTips($content['dicas_manutencao'] ?? []);
        $this->processedData['critical_alerts'] = $this->processCriticalAlerts($content['alertas_criticos'] ?? []);
        $this->processedData['calibration_procedure'] = $this->processCalibrationProcedure($content['procedimento_calibragem'] ?? []);
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';
        
        // Dados auxiliares
        $this->processedData['vehicle_info'] = $this->processMotorcycleVehicleInfo();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
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

        if (!empty($specs['pneu_dianteiro'])) {
            $processed['front_tire'] = [
                'size' => $specs['pneu_dianteiro']['medida_original'] ?? '',
                'load_index' => $specs['pneu_dianteiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_dianteiro']['indice_velocidade'] ?? '',
                'construction' => $specs['pneu_dianteiro']['tipo_construcao'] ?? '',
                'original_brands' => $specs['pneu_dianteiro']['marca_original'] ?? '',
                'alternative_brands' => $specs['pneu_dianteiro']['alternativas_recomendadas'] ?? []
            ];
        }

        if (!empty($specs['pneu_traseiro'])) {
            $processed['rear_tire'] = [
                'size' => $specs['pneu_traseiro']['medida_original'] ?? '',
                'load_index' => $specs['pneu_traseiro']['indice_carga'] ?? '',
                'speed_rating' => $specs['pneu_traseiro']['indice_velocidade'] ?? '',
                'construction' => $specs['pneu_traseiro']['tipo_construcao'] ?? '',
                'original_brands' => $specs['pneu_traseiro']['marca_original'] ?? '',
                'alternative_brands' => $specs['pneu_traseiro']['alternativas_recomendadas'] ?? []
            ];
        }

        $processed['observation'] = $specs['observacao'] ?? '';

        return $processed;
    }

    /**
     * Processa tabela de pressões específica para motocicletas
     */
    private function processMotorcyclePressureTable(array $table): array
    {
        if (empty($table)) {
            return [];
        }

        $processed = [
            'official_pressures' => [],
            'special_conditions' => []
        ];

        // Processa pressões oficiais
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

        // Processa condições especiais
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
     * Processa localização das informações
     */
    private function processInformationLocation(array $location): array
    {
        if (empty($location)) {
            return [];
        }

        $processed = [];

        if (!empty($location['manual_proprietario'])) {
            $processed['owner_manual'] = [
                'location' => $location['manual_proprietario']['localizacao'] ?? '',
                'section' => $location['manual_proprietario']['secao'] ?? '',
                'approximate_page' => $location['manual_proprietario']['pagina_aproximada'] ?? ''
            ];
        }

        if (!empty($location['etiqueta_moto'])) {
            $processed['motorcycle_label'] = [
                'main_location' => $location['etiqueta_moto']['localizacao_principal'] ?? '',
                'alternative_locations' => $location['etiqueta_moto']['localizacoes_alternativas'] ?? []
            ];
        }

        $processed['important_tip'] = $location['dica_importante'] ?? '';
        $processed['visual_guide'] = $this->generateMotorcycleVisualGuide($location);

        return $processed;
    }

    /**
     * Processa tabela de conversão de unidades
     */
    private function processUnitConversion(array $conversion): array
    {
        if (empty($conversion)) {
            return [];
        }

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
     */
    private function processMotorcycleVehicleInfo(): array
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

        return [
            'full_name' => $this->getMotorcycleFullName(),
            'make' => $vehicleInfo['marca'] ?? '',
            'model' => $vehicleInfo['modelo'] ?? '',
            'year' => $vehicleInfo['ano'] ?? '',
            'displacement' => $vehicleInfo['motorizacao'] ?? '',
            'type' => $vehicleInfo['categoria'] ?? '',
            'category' => 'motocicleta',
            'image_url' => $this->getMotorcycleImageUrl(),
            'is_sport' => $this->isSportMotorcycle(),
            'is_naked' => $this->isNakedMotorcycle(),
            'is_touring' => $this->isTouringMotorcycle(),
            'engine_size_category' => $this->getEngineSizeCategory(),
            'slug' => $this->generateSlug($vehicleInfo),
            'is_premium' => $this->isPremiumMotorcycle(),
            'segment' => $this->getMotorcycleSegment()
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

        if (str_contains($situation, 'pesado') || str_contains($situation, 'piloto')) {
            return 'moto-situation-weight';
        }

        return 'moto-situation-default';
    }

    /**
     * Obtém classe de ícone para situação da motocicleta
     */
    private function getMotorcycleSituationIconClass(string $situation): string
    {
        $situation = strtolower($situation);

        if (str_contains($situation, 'urbana') || str_contains($situation, 'cidade')) return 'home';
        if (str_contains($situation, 'rodoviária') || str_contains($situation, 'viagem')) return 'map';
        if (str_contains($situation, 'esportiva') || str_contains($situation, 'curva')) return 'zap';
        if (str_contains($situation, 'chuva') || str_contains($situation, 'molhado')) return 'cloud-rain';
        if (str_contains($situation, 'pesado') || str_contains($situation, 'piloto')) return 'user';

        return 'navigation';
    }

    /**
     * Verifica se é pressão recomendada para motocicleta
     */
    private function isRecommendedMotorcyclePressure(string $psi): bool
    {
        // Pressões padrão para MT-03: 33 PSI (dianteiro) e 36 PSI (traseiro)
        $recommendedPressures = ['33', '36'];
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
     */
    private function getMotorcycleFullName(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];

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
     */
    private function getMotorcycleImageUrl(): string
    {
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $makeSlug = strtolower($vehicleInfo['marca'] ?? '');
        $modelSlug = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));
        $year = $vehicleInfo['ano'] ?? '';

        return "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/motorcycles/{$makeSlug}-{$modelSlug}-{$year}.jpg";
    }

    /**
     * Verifica se é moto esportiva
     */
    private function isSportMotorcycle(): bool
    {
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'sport') || str_contains($type, 'esportiva');
    }

    /**
     * Verifica se é moto naked
     */
    private function isNakedMotorcycle(): bool
    {
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'naked') || str_contains($type, 'street');
    }

    /**
     * Verifica se é moto touring
     */
    private function isTouringMotorcycle(): bool
    {
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        return str_contains($type, 'touring') || str_contains($type, 'viagem');
    }

    /**
     * Verifica se é motocicleta premium
     */
    private function isPremiumMotorcycle(): bool
    {
        $make = strtolower($this->article->extracted_entities['marca'] ?? '');
        $premiumBrands = ['ducati', 'bmw', 'triumph', 'ktm', 'harley-davidson'];

        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém categoria do tamanho do motor
     */
    private function getEngineSizeCategory(): string
    {
        $motorizacao = $this->article->extracted_entities['motorizacao'] ?? '';

        preg_match('/(\d+)/', $motorizacao, $matches);
        $displacement = isset($matches[1]) ? intval($matches[1]) : 0;

        if ($displacement <= 150) return 'pequeno';
        if ($displacement <= 300) return 'médio';
        if ($displacement <= 600) return 'grande';
        if ($displacement <= 1000) return 'super';

        return 'premium';
    }

    /**
     * Obtém segmento da motocicleta
     */
    private function getMotorcycleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');

        $segmentMap = [
            'naked' => 'Naked',
            'sport' => 'Esportiva',
            'touring' => 'Turismo',
            'adventure' => 'Adventure',
            'cruiser' => 'Cruiser'
        ];

        return $segmentMap[$category] ?? 'Motocicleta';
    }

    /**
     * Gera slug baseado nos dados do veículo
     */
    private function generateSlug(array $vehicleInfo): string
    {
        $make = strtolower($vehicleInfo['marca'] ?? '');
        $model = strtolower(str_replace(' ', '-', $vehicleInfo['modelo'] ?? ''));

        return "{$make}-{$model}";
    }

    /**
     * Processa dados SEO específicos para motocicletas
     */
    private function processSeoData(): array
    {
        $vehicleFullName = $this->getMotorcycleFullName();
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $seoData = $this->article->seo_data ?? [];

        // Obtém pressões dos dados processados
        $frontPressure = $this->processedData['pressure_table']['official_pressures']['solo_rider']['front'] ?? '';
        $rearPressure = $this->processedData['pressure_table']['official_pressures']['solo_rider']['rear'] ?? '';
        $pressureDisplay = $frontPressure && $rearPressure ? "{$frontPressure}/{$rearPressure}" : '';

        return [
            'title' => $seoData['page_title'] ?? "Pressão Ideal para Pneus da {$vehicleFullName} - Guia Completo",
            'meta_description' => $seoData['meta_description'] ?? "Pressões ideais para pneus da {$vehicleFullName}. Solo/garupa: {$pressureDisplay}. Dicas específicas para motociclistas e tabela de conversão PSI.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "pressão ideal pneus {$vehicleInfo['marca']} {$vehicleInfo['modelo']} {$vehicleInfo['ano']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Pressão Ideal para Pneus da {$vehicleFullName} – Guia Completo",
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => $seoData['og_title'] ?? "Pressão Ideal para Pneus da {$vehicleFullName} - Motocicleta",
            'og_description' => $seoData['og_description'] ?? "Guia específico para motociclistas: pressões ideais, dicas de segurança e calibragem da {$vehicleFullName}.",
            'og_image' => $seoData['og_image'] ?? $this->processedData['vehicle_info']['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para motocicletas
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $vehicleFullName = $vehicleInfo['full_name'];
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

        if (!empty($vehicleData['marca']) && !empty($vehicleData['modelo'])) {
            $structuredData['mainEntity'] = [
                '@type' => 'Motorcycle',
                'name' => 'Pressão ideal para ' . $vehicleData['marca'] . ' ' . $vehicleData['modelo'],
                'brand' => $vehicleData['marca'],
                'model' => $vehicleData['modelo']
            ];

            if (!empty($vehicleData['ano'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleData['ano'];
            }

            if (!empty($vehicleData['motorizacao'])) {
                $structuredData['mainEntity']['engineDisplacement'] = $vehicleData['motorizacao'];
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
                'name' => 'Início',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->article->category_name ?? 'Calibragem de Pneus'),
                'url' => route('info.category.show', $this->article->category_slug ?? 'calibragem-pneus'),
                'position' => 3
            ],
            [
                'name' => $this->article->title,
                'url' => route('info.article.show', $this->article->slug),
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