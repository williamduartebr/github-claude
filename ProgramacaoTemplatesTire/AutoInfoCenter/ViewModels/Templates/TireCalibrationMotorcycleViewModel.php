<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;

class TireCalibrationMotorcycleViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait;

    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_calibration_motorcycle';

    /**
     * Processa dados específicos do template de pressão ideal para motocicletas
     */
    protected function processTemplateSpecificData(): void
    {
        $content = $this->article->content;

        // dd($content);

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
        
        // OTIMIZADA: Usar dados embarcados primeiro
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['pressure_specifications'] = $this->processPressureSpecifications();
        $this->processedData['tire_specs_embedded'] = $this->processTireSpecificationsEmbedded();
        
        // Dados auxiliares
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
    }

    /**
     * Processa especificações dos pneus específicas para motocicletas OTIMIZADA
     */
    private function processMotorcycleTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return $this->generateTireSpecsFromEmbeddedData();
        }

        $processed = [];

        foreach ($specs as $spec) {
            if (!empty($spec['posicao'])) {
                $processed[$spec['posicao']] = [
                    'position' => $spec['posicao'],
                    'tire_size' => $spec['medida'] ?? '',
                    'load_speed_index' => $spec['indice_carga_velocidade'] ?? '',
                    'recommended_brands' => $spec['marcas_recomendadas'] ?? [],
                    'average_price' => $spec['preco_medio'] ?? '',
                    'durability_km' => $spec['durabilidade_km'] ?? ''
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera especificações de pneus a partir de dados embarcados
     */
    private function generateTireSpecsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $tireSpecs = $this->processedData['tire_specs_embedded'] ?? [];
        
        if (empty($tireSpecs['tire_size'])) {
            return [];
        }

        return [
            'front_tire' => [
                'position' => 'Dianteiro',
                'tire_size' => $tireSpecs['front_tire_size'] ?: $tireSpecs['tire_size'],
                'load_speed_index' => '',
                'recommended_brands' => $tireSpecs['recommended_brands'] ?? [],
                'average_price' => '',
                'durability_km' => $vehicleInfo['is_premium'] ? '15.000-20.000 km' : '12.000-15.000 km'
            ],
            'rear_tire' => [
                'position' => 'Traseiro', 
                'tire_size' => $tireSpecs['rear_tire_size'] ?: $tireSpecs['tire_size'],
                'load_speed_index' => '',
                'recommended_brands' => $tireSpecs['recommended_brands'] ?? [],
                'average_price' => '',
                'durability_km' => $vehicleInfo['is_premium'] ? '12.000-15.000 km' : '10.000-12.000 km'
            ]
        ];
    }

    /**
     * Processa tabela de pressões para motocicletas CORRIGIDA
     */
    private function processMotorcyclePressureTable(array $table): array
    {
        if (empty($table)) {
            return $this->generatePressureTableFromEmbeddedData();
        }

        $processed = [
            'official_pressures' => [],
            'conditional_adjustments' => [],
            'special_conditions' => []
        ];

        // ✅ CORRIGIDO: Usar 'dianteira' e 'traseira' (como nos mocks)
        if (!empty($table['pressoes_oficiais'])) {
            foreach ($table['pressoes_oficiais'] as $condition => $pressures) {
                $processed['official_pressures'][$condition] = [
                    'condition' => $condition,
                    'front' => $pressures['dianteira'] ?? '',  // ✅ CORRIGIDO
                    'rear' => $pressures['traseira'] ?? '',    // ✅ CORRIGIDO
                    'observation' => $pressures['observacao'] ?? ''
                ];
            }
        }

        // ✅ ADICIONADO: Processar condições especiais
        if (!empty($table['condicoes_especiais'])) {
            foreach ($table['condicoes_especiais'] as $condition) {
                $processed['special_conditions'][] = [
                    'situation' => $condition['situacao'] ?? '',
                    'terrain' => $condition['terreno'] ?? '',
                    'front_pressure' => $condition['pressao_dianteira'] ?? '',
                    'rear_pressure' => $condition['pressao_traseira'] ?? '',
                    'ideal_temperature' => $condition['temperatura_ideal'] ?? '',
                    'observation' => $condition['observacao'] ?? '',
                    'icon_class' => $this->getConditionIconClass($condition['terreno'] ?? '')
                ];
            }
        }

        // Processar ajustes condicionais (se existir)
        if (!empty($table['ajustes_condicionais'])) {
            foreach ($table['ajustes_condicionais'] as $adjustment) {
                $processed['conditional_adjustments'][] = [
                    'situation' => $adjustment['situacao'] ?? '',
                    'front_adjustment' => $adjustment['ajuste_dianteiro'] ?? '',
                    'rear_adjustment' => $adjustment['ajuste_traseiro'] ?? '',
                    'justification' => $adjustment['justificativa'] ?? ''
                ];
            }
        }

        return $processed;
    }

    /**
     * Obtém classe de ícone baseada no terreno/condição
     */
    private function getConditionIconClass(string $terrain): string
    {
        $iconMap = [
            'Cidade/trânsito' => 'home',
            'Estradas municipais' => 'map',
            'Rodovias' => 'map',
            'Curvas/montanha' => 'zap',
            'Pista/autódromo' => 'zap',
            'Piso molhado' => 'cloud-rain',
            'Chuva' => 'cloud-rain',
            'Uso geral' => 'user',
            'Delivery/trabalho' => 'package'
        ];

        return $iconMap[$terrain] ?? 'map';
    }

    /**
     * Gera tabela de pressões a partir de dados embarcados
     */
    private function generatePressureTableFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        if (empty($pressureSpecs)) {
            return [];
        }

        return [
            'official_pressures' => [
                'solo_rider' => [
                    'condition' => 'Piloto Solo',
                    'front' => ($pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear' => ($pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                    'observation' => 'Uso urbano e rodoviário normal'
                ],
                'with_passenger' => [
                    'condition' => 'Piloto + Garupa',
                    'front' => ($pressureSpecs['pressure_max_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear' => ($pressureSpecs['pressure_max_rear'] ?? '') . ' PSI',
                    'observation' => 'Distribuição adequada do peso'
                ]
            ],
            'conditional_adjustments' => [
                [
                    'situation' => 'Viagem Longa',
                    'front_adjustment' => '+1 PSI',
                    'rear_adjustment' => '+1 PSI',
                    'justification' => 'Melhora estabilidade em altas velocidades'
                ],
                [
                    'situation' => 'Uso Esportivo',
                    'front_adjustment' => '-1 PSI',
                    'rear_adjustment' => '0 PSI',
                    'justification' => 'Aumenta área de contato (apenas para experientes)'
                ]
            ]
        ];
    }

    /**
     * Processa localização das informações OTIMIZADA
     */
    private function processInformationLocation(array $location): array
    {
        if (empty($location)) {
            return $this->generateInformationLocationFromEmbeddedData();
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
     * Gera localização das informações a partir de dados embarcados
     */
    private function generateInformationLocationFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            'owner_manual' => [
                'location' => 'Manual do Proprietário',
                'section' => 'Especificações Técnicas',
                'approximate_page' => 'Páginas 15-20 (aproximadamente)'
            ],
            'motorcycle_label' => [
                'main_location' => 'Coluna da direção ou chassi próximo ao motor',
                'alternative_locations' => [
                    'Embaixo do banco',
                    'Lado direito do chassi',
                    'Manual do proprietário'
                ]
            ],
            'important_tip' => 'Em motocicletas, as informações podem estar em locais menos visíveis. Consulte sempre o manual.',
            'visual_guide' => []
        ];
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
            'observation' => 'PSI é a unidade padrão no Brasil para motocicletas.'
        ];
    }

    /**
     * Processa considerações especiais para motocicletas OTIMIZADA
     */
    private function processSpecialConsiderations(array $considerations): array
    {
        if (empty($considerations)) {
            return $this->generateSpecialConsiderationsFromEmbeddedData();
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
                    'icon_class' => $this->getMotorcycleConsiderationIconClass($key),
                    'importance' => $consideration['importancia'] ?? 'média'
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera considerações especiais a partir de dados embarcados
     */
    private function generateSpecialConsiderationsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $considerations = [];

        // Peso do piloto
        $considerations[] = [
            'category' => 'peso_piloto',
            'title' => 'Peso do Piloto e Garupa',
            'description' => 'Ajustes necessários conforme o peso total.',
            'factors' => [],
            'orientations' => [],
            'types' => [
                'Piloto leve (≤65kg): reduzir 1 PSI no traseiro',
                'Piloto médio (66-85kg): usar pressões padrão',
                'Piloto pesado (≥86kg): aumentar 2 PSI no traseiro',
                'Com garupa: sempre usar pressões para "piloto + garupa"',
                'Bagagem pesada: considerar +1 PSI no traseiro'
            ],
            'icon_class' => 'package',
            'importance' => 'alta'
        ];

        // Estilo de pilotagem
        $considerations[] = [
            'category' => 'estilo_pilotagem',
            'title' => 'Estilo de Pilotagem',
            'description' => 'Ajustes conforme o tipo de uso da motocicleta.',
            'factors' => [],
            'orientations' => [
                'Pilotagem urbana defensiva: usar pressões padrão',
                $this->isSportMotorcycle() ? 'Pilotagem esportiva: reduzir 2 PSI (somente experientes)' : '',
                'Viagens longas: aumentar 2 PSI para estabilidade',
                'Track day: consultar instrutor especializado'
            ],
            'types' => [],
            'icon_class' => 'target',
            'importance' => 'média'
        ];

        // Condições climáticas
        $considerations[] = [
            'category' => 'condicoes_clima',
            'title' => 'Condições Climáticas',
            'description' => 'Ajustes para diferentes condições de tempo.',
            'factors' => [],
            'orientations' => [
                'Chuva: manter pressões exatas para máxima aderência',
                'Calor extremo: verificar pressão pela manhã',
                'Frio: aumentar ligeiramente após aquecimento',
                'Pista molhada: nunca reduzir pressão'
            ],
            'types' => [],
            'icon_class' => 'sun',
            'importance' => 'alta'
        ];

        // Remove entradas vazias
        $considerations = array_filter($considerations, function($item) {
            return !empty($item['orientations']) || !empty($item['factors']) || !empty($item['types']);
        });

        return $considerations;
    }

    /**
     * Processa benefícios da calibragem específicos para motos OTIMIZADA
     */
    private function processMotorcycleCalibrationBenefits(array $benefits): array
    {
        if (empty($benefits)) {
            return $this->generateMotorcycleBenefitsFromEmbeddedData();
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
     * Gera benefícios específicos para motos a partir de dados embarcados
     */
    private function generateMotorcycleBenefitsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            [
                'category' => 'seguranca',
                'title' => 'Segurança Crítica',
                'description' => 'Em motocicletas, pressão incorreta pode ser fatal.',
                'aspects' => [
                    'Estabilidade em curvas mantida',
                    'Distância de frenagem otimizada',
                    'Aderência máxima em emergências',
                    'Comportamento previsível da moto'
                ],
                'financial_impact' => 'Prevenção de acidentes',
                'estimated_savings' => 'Inestimável',
                'icon_class' => 'shield',
                'color_class' => 'from-red-100 to-red-200',
                'priority' => 'crítica'
            ],
            [
                'category' => 'economia',
                'title' => 'Economia de Combustível',
                'description' => 'Pressão correta reduz consumo significativamente.',
                'aspects' => [
                    'Redução de 5-10% no consumo',
                    'Menor resistência ao rolamento',
                    'Motor trabalha com menos esforço',
                    'Economia mensal perceptível'
                ],
                'financial_impact' => 'R$ 30-50/mês',
                'estimated_savings' => 'R$ 360-600/ano',
                'icon_class' => 'dollar-sign',
                'color_class' => 'from-green-100 to-green-200',
                'priority' => 'alta'
            ],
            [
                'category' => 'durabilidade',
                'title' => 'Vida Útil dos Pneus',
                'description' => 'Pneus duram até 40% mais com pressão correta.',
                'aspects' => [
                    'Desgaste uniforme garantido',
                    'Evita deformações permanentes',
                    'Melhor distribuição de calor',
                    'Aproveitamento máximo do composto'
                ],
                'financial_impact' => 'R$ 400-800/troca',
                'estimated_savings' => 'R$ 800-1600/ano',
                'icon_class' => 'clock',
                'color_class' => 'from-blue-100 to-blue-200',
                'priority' => 'alta'
            ]
        ];
    }

    /**
     * Processa dicas de manutenção específicas para motos OTIMIZADA
     */
    private function processMotorcycleMaintenanceTips(array $tips): array
    {
        if (empty($tips)) {
            return $this->generateMaintenanceTipsFromEmbeddedData();
        }

        $processed = [];

        foreach ($tips as $tip) {
            if (!empty($tip['categoria'])) {
                $processed[] = [
                    'category' => $tip['categoria'],
                    'frequency' => $tip['frequencia'] ?? '',
                    'procedures' => $tip['procedimentos'] ?? [],
                    'tools_needed' => $tip['ferramentas_necessarias'] ?? [],
                    'safety_warnings' => $tip['alertas_seguranca'] ?? [],
                    'icon_class' => $this->getMotorcycleMaintenanceIconClass($tip['categoria']),
                    'difficulty_level' => $tip['nivel_dificuldade'] ?? 'básico'
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera dicas de manutenção a partir de dados embarcados
     */
    private function generateMaintenanceTipsFromEmbeddedData(): array
    {
        return [
            [
                'category' => 'Verificação Semanal',
                'frequency' => 'A cada 7 dias ou 500 km',
                'procedures' => [
                    'Verificar pressão com pneus frios',
                    'Inspecionar visualmente os pneus',
                    'Verificar profundidade dos sulcos',
                    'Observar desgaste irregular'
                ],
                'tools_needed' => ['Calibrador', 'Compressor'],
                'safety_warnings' => ['Nunca verificar com pneus quentes'],
                'icon_class' => 'calendar',
                'difficulty_level' => 'básico'
            ],
            [
                'category' => 'Cuidados Especiais',
                'frequency' => 'Conforme necessidade',
                'procedures' => [
                    'Calibrar antes de viagens longas',
                    'Ajustar para carga extra',
                    'Verificar após mudanças de temperatura',
                    'Reavaliar após trocar pneus'
                ],
                'tools_needed' => ['Calibrador digital', 'Termômetro'],
                'safety_warnings' => ['Respeitar limites de carga da moto'],
                'icon_class' => 'tool',
                'difficulty_level' => 'intermediário'
            ]
        ];
    }

    /**
     * Processa alertas críticos para motocicletas OTIMIZADA
     */
    private function processCriticalAlerts(array $alerts): array
    {
        if (empty($alerts)) {
            return $this->generateCriticalAlertsFromEmbeddedData();
        }

        $processed = [];

        foreach ($alerts as $alert) {
            if (!empty($alert['tipo'])) {
                $processed[] = [
                    'type' => $alert['tipo'],
                    'title' => $alert['titulo'] ?? '',
                    'description' => $alert['descricao'] ?? '',
                    'consequence' => $alert['consequencia'] ?? '',
                    'immediate_action' => $alert['acao_imediata'] ?? '',
                    'icon_class' => $this->getMotorcycleAlertIconClass($alert['tipo']),
                    'severity_class' => $this->getMotorcycleAlertSeverityClass($alert['tipo']),
                    'border_class' => $this->getMotorcycleAlertBorderClass($alert['tipo'])
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera alertas críticos a partir de dados embarcados
     */
    private function generateCriticalAlertsFromEmbeddedData(): array
    {
        return [
            [
                'type' => 'crítico',
                'title' => 'Pressão Baixa - Risco Fatal',
                'description' => 'Pneus subcalibrados podem causar perda de controle em curvas.',
                'consequence' => 'Acidente grave ou fatal',
                'immediate_action' => 'Pare imediatamente e calibre',
                'icon_class' => 'alert-triangle',
                'severity_class' => 'critical',
                'border_class' => 'border-red-600'
            ],
            [
                'type' => 'importante',
                'title' => 'Verificação com Pneus Quentes',
                'description' => 'Nunca calibre ou verifique pressão com pneus aquecidos.',
                'consequence' => 'Leituras incorretas e ajustes perigosos',
                'immediate_action' => 'Aguarde 3h antes de verificar',
                'icon_class' => 'thermometer',
                'severity_class' => 'important',
                'border_class' => 'border-orange-500'
            ],
            [
                'type' => 'atenção',
                'title' => 'Diferença Entre Dianteiro e Traseiro',
                'description' => 'Pressões muito diferentes podem desbalancear a moto.',
                'consequence' => 'Comportamento imprevisível',
                'immediate_action' => 'Seguir especificações do fabricante',
                'icon_class' => 'balance-scale',
                'severity_class' => 'warning',
                'border_class' => 'border-yellow-500'
            ]
        ];
    }

    /**
     * Processa procedimento de calibragem OTIMIZADA
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure)) {
            return $this->generateMotorcycleCalibrationProcedureFromEmbeddedData();
        }

        $processed = [];

        foreach ($procedure as $step) {
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
     * Gera procedimento de calibragem específico para motos a partir de dados embarcados
     */
    private function generateMotorcycleCalibrationProcedureFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        
        return [
            [
                'number' => 1,
                'title' => 'Preparação',
                'description' => 'Verifique sempre com pneus frios',
                'details' => [
                    'Moto parada há pelo menos 3 horas',
                    'Ou menos de 2 km rodados',
                    'Preferencialmente pela manhã',
                    'Em local com sombra'
                ],
                'icon_class' => 'settings',
                'step_class' => 'step-preparation'
            ],
            [
                'number' => 2,
                'title' => 'Verificação',
                'description' => 'Use calibrador confiável',
                'details' => [
                    'Prefira calibradores digitais',
                    'Retire a tampa da válvula',
                    'Encaixe firmemente o calibrador',
                    'Anote a pressão atual'
                ],
                'icon_class' => 'search',
                'step_class' => 'step-verification'
            ],
            [
                'number' => 3,
                'title' => 'Ajuste',
                'description' => 'Calibre conforme necessidade',
                'details' => [
                    'Dianteiro: ' . ($pressureSpecs['pressure_empty_front'] ?? '33') . ' PSI (uso normal)',
                    'Traseiro: ' . ($pressureSpecs['pressure_empty_rear'] ?? '36') . ' PSI (uso normal)',
                    'Ajuste conforme condições especiais',
                    'Recoloque as tampas das válvulas'
                ],
                'icon_class' => 'tool',
                'step_class' => 'step-adjustment'
            ]
        ];
    }

    /**
     * Verifica se é moto esportiva
     */
    public function isSportMotorcycle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $mainCategory = $vehicleInfo['main_category'] ?? '';
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($mainCategory, 'sport') || $type === 'sport';
    }

    /**
     * Verifica se é pressão recomendada para moto
     */
    private function isRecommendedMotorcyclePressure(string $psi): bool
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

    /**
     * Obtém classe de destaque para pressão de moto
     */
    private function getMotorcyclePressureHighlightClass(string $psi): string
    {
        return $this->isRecommendedMotorcyclePressure($psi) ? 'highlight-pressure' : '';
    }

    /**
     * Obtém classe de ícone para considerações
     */
    private function getMotorcycleConsiderationIconClass(string $category): string
    {
        $iconMap = [
            'peso_piloto' => 'package',
            'estilo_pilotagem' => 'target',
            'condicoes_clima' => 'sun',
            'tipo_pneu' => 'tool',
            'temperatura' => 'thermometer',
            'carga' => 'package'
        ];

        return $iconMap[$category] ?? 'info';
    }

    /**
     * Obtém classe de ícone para benefícios
     */
    private function getMotorcycleBenefitIconClass(string $category): string
    {
        $iconMap = [
            'seguranca' => 'shield',
            'economia' => 'dollar-sign',
            'durabilidade' => 'clock',
            'performance' => 'zap',
            'conforto' => 'heart'
        ];

        return $iconMap[$category] ?? 'star';
    }

    /**
     * Obtém classe de cor para benefícios
     */
    private function getMotorcycleBenefitColorClass(string $category): string
    {
        $colorMap = [
            'seguranca' => 'from-red-100 to-red-200',
            'economia' => 'from-green-100 to-green-200',
            'durabilidade' => 'from-blue-100 to-blue-200',
            'performance' => 'from-purple-100 to-purple-200',
            'conforto' => 'from-pink-100 to-pink-200'
        ];

        return $colorMap[$category] ?? 'from-gray-100 to-gray-200';
    }

    /**
     * Obtém prioridade do benefício
     */
    private function getMotorcycleBenefitPriority(string $category): string
    {
        $priorityMap = [
            'seguranca' => 'crítica',
            'economia' => 'alta',
            'durabilidade' => 'alta',
            'performance' => 'média',
            'conforto' => 'baixa'
        ];

        return $priorityMap[$category] ?? 'média';
    }

    /**
     * Obtém classe de ícone para manutenção
     */
    private function getMotorcycleMaintenanceIconClass(string $category): string
    {
        $iconMap = [
            'Verificação Semanal' => 'calendar',
            'Cuidados Especiais' => 'tool',
            'Limpeza' => 'droplet',
            'Inspeção Visual' => 'eye',
            'Emergência' => 'alert-triangle'
        ];

        return $iconMap[$category] ?? 'wrench';
    }

    /**
     * Obtém classe de ícone para alertas
     */
    private function getMotorcycleAlertIconClass(string $type): string
    {
        $iconMap = [
            'crítico' => 'alert-triangle',
            'importante' => 'alert-circle',
            'atenção' => 'info',
            'dica' => 'lightbulb'
        ];

        return $iconMap[strtolower($type)] ?? 'help-circle';
    }

    /**
     * Obtém classe de severidade para alertas
     */
    private function getMotorcycleAlertSeverityClass(string $type): string
    {
        $severityMap = [
            'crítico' => 'critical',
            'importante' => 'important',
            'atenção' => 'warning',
            'dica' => 'info'
        ];

        return $severityMap[strtolower($type)] ?? 'info';
    }

    /**
     * Obtém classe de borda para alertas
     */
    private function getMotorcycleAlertBorderClass(string $type): string
    {
        $borderMap = [
            'crítico' => 'border-red-600',
            'importante' => 'border-orange-500',
            'atenção' => 'border-yellow-500',
            'dica' => 'border-blue-500'
        ];

        return $borderMap[strtolower($type)] ?? 'border-gray-500';
    }

    /**
     * Obtém classe de ícone para passos
     */
    private function getMotorcycleStepIconClass(int $stepNumber): string
    {
        $icons = [
            1 => 'settings',
            2 => 'search',
            3 => 'tool',
            4 => 'check'
        ];

        return $icons[$stepNumber] ?? 'circle';
    }

    /**
     * Obtém classe do passo
     */
    private function getMotorcycleStepClass(int $stepNumber): string
    {
        $classes = [
            1 => 'step-preparation',
            2 => 'step-verification',
            3 => 'step-adjustment',
            4 => 'step-completion'
        ];

        return $classes[$stepNumber] ?? 'step-default';
    }

    /**
     * Gera guia visual para motocicleta
     */
    private function generateMotorcycleVisualGuide(array $location): array
    {
        return [
            'images' => [
                'label_location' => 'motorcycle-label-location.jpg',
                'pressure_check' => 'motorcycle-pressure-check.jpg',
                'valve_position' => 'motorcycle-valve-position.jpg'
            ],
            'descriptions' => [
                'Localização da etiqueta na motocicleta',
                'Posição correta para verificar pressão',
                'Acesso à válvula dos pneus'
            ]
        ];
    }

    /**
     * Sobrescreve dados de SEO para foco em "calibragem" para motocicletas
     */
    protected function processSeoData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $seoData = $this->article->seo_data ?? [];

        $pressureDisplay = $pressureSpecs['pressure_display'] ?? '';
        
        return [
            'title' => $seoData['page_title'] ?? "Calibragem do Pneu da {$vehicleInfo['full_name']} – Guia Completo para Motociclistas",
            'meta_description' => $seoData['meta_description'] ?? "Guia específico de calibragem dos pneus da {$vehicleInfo['full_name']}. {$pressureDisplay}. Procedimento seguro e dicas críticas para motociclistas.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "calibragem pneu {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Calibragem do Pneu da {$vehicleInfo['full_name']} – Guia Completo para Motociclistas",
            'h2_tags' => $seoData['h2_tags'] ?? [
                'Especificações dos Pneus Originais',
                'Procedimento de Calibragem para Motocicletas (PSI)',
                'Tabela de Pressões: Solo vs Garupa',
                'Ajustes para Condições Especiais de Pilotagem',
                'Conversão de Unidades para Motociclistas',
                'Cuidados Específicos na Calibragem de Motos',
                'Alertas Críticos de Segurança',
                'Procedimento Correto de Calibragem',
                'Perguntas Frequentes sobre Calibragem'
            ],
            'og_title' => $seoData['og_title'] ?? "Calibragem do Pneu da {$vehicleInfo['full_name']} - Motocicleta",
            'og_description' => $seoData['og_description'] ?? "Guia específico para motociclistas: procedimento de calibragem, pressões ideais e dicas de segurança para {$vehicleInfo['full_name']}.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para calibragem de motocicletas
     */
    protected function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => "Calibragem do Pneu da {$vehicleFullName}",
            'description' => "Guia específico de calibragem dos pneus da {$vehicleFullName}, incluindo procedimentos de segurança e ajustes para piloto solo e garupa.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/vehicles/default-motorcycle.jpg',
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
                'name' => 'Calibragem de Pneus de Motocicleta',
                'description' => 'Procedimentos específicos de calibragem para motocicletas'
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCanonicalUrl()
            ]
        ];

        if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
            $structuredData['mainEntity'] = [
                '@type' => 'Motorcycle',
                'name' => 'Calibragem de pneus para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $vehicleInfo['make']
                ],
                'model' => $vehicleInfo['model']
            ];

            if (!empty($vehicleInfo['year'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleInfo['year'];
            }

            if (!empty($vehicleInfo['engine_displacement'])) {
                $structuredData['mainEntity']['engineDisplacement'] = $vehicleInfo['engine_displacement'];
            }

            if (!empty($vehicleInfo['fuel'])) {
                $structuredData['mainEntity']['fuelType'] = $vehicleInfo['fuel'];
            }
        }

        // Adiciona informações específicas sobre calibragem de motocicletas
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        if (!empty($pressureSpecs)) {
            $structuredData['mainEntity']['maintenanceSchedule'] = [
                '@type' => 'MaintenanceSchedule',
                'name' => 'Calibragem de Pneus de Motocicleta',
                'description' => 'Pressões recomendadas para calibragem dos pneus da motocicleta',
                'frequency' => 'Weekly' // Motocicletas requerem verificação semanal
            ];
        }

        return $structuredData;
    }

    /**
     * Processa tópicos relacionados OTIMIZADA
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

        // Tópicos específicos por categoria
        if ($this->isSportMotorcycle()) {
            $topics[] = [
                'title' => 'Pneus Esportivos para Track Day',
                'url' => '/info/pneus-esportivos-track-day',
                'description' => 'Escolha e calibragem para uso esportivo'
            ];
        }

        $topics[] = [
            'title' => 'Manutenção Preventiva de Motocicletas',
            'url' => '/info/manutencao-preventiva-motos',
            'description' => 'Checklist completo de manutenção'
        ];

        return $topics;
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