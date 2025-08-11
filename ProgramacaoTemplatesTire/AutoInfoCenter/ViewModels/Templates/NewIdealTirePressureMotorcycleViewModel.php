<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;

class NewIdealTirePressureMotorcycleViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait;

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
        
        // Dados auxiliares usando o trait OTIMIZADO
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['pressure_specifications'] = $this->processPressureSpecifications();
        $this->processedData['tire_specs_embedded'] = $this->processTireSpecifications();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
        
        // Seções condicionais específicas para motos
        $this->processedData['sections_visible'] = $this->determineSectionsVisibility();
    }

    /**
     * Determina quais seções devem ser visíveis baseado nos flags
     */
    private function determineSectionsVisibility(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];

        return [
            'electric_features' => $vehicleInfo['is_electric'], // Moto elétrica
            'hybrid_modes' => false, // Motos híbridas são raras
            'tpms_section' => $vehicleInfo['has_tpms'], // TPMS em motos premium
            'spare_tire_section' => false, // Motos não têm estepe
            'oil_recommendations' => !$vehicleInfo['is_electric'] && !empty($vehicleInfo['recommended_oil']),
            'premium_features' => $vehicleInfo['is_premium'],
            'load_table_reference' => false, // Motos não usam tabela de carga
            'motorcycle_warnings' => true, // Sempre true para motos
            'sport_motorcycle_features' => $this->isSportMotorcycle(),
            'street_motorcycle_features' => $this->isStreetMotorcycle(),
            'critical_safety_banner' => true // Sempre visível para motos
        ];
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
        $premiumBrands = ['ducati', 'bmw', 'triumph', 'ktm', 'harley-davidson', 'kawasaki', 'yamaha', 'suzuki', 'honda'];

        return in_array($make, $premiumBrands);
    }

    /**
     * Obtém segmento da motocicleta
     */
    protected function getVehicleSegment(): string
    {
        $category = strtolower($this->article->extracted_entities['categoria'] ?? '');

        $segmentMap = [
            'naked' => 'Naked',
            'sport' => 'Esportiva',
            'touring' => 'Turismo',
            'adventure' => 'Adventure',
            'cruiser' => 'Cruiser',
            'street' => 'Street'
        ];

        return $segmentMap[$category] ?? 'Motocicleta';
    }

    /**
     * Processa especificações dos pneus para motocicletas OTIMIZADA
     */
    private function processMotorcycleTireSpecifications(array $specs): array
    {
        if (empty($specs)) {
            return $this->generateMotorcycleSpecsFromEmbeddedData();
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
     * Gera especificações de moto a partir de dados embarcados
     */
    private function generateMotorcycleSpecsFromEmbeddedData(): array
    {
        $tireSpecs = $this->processedData['tire_specs_embedded'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        if (empty($tireSpecs['tire_size'])) {
            return [];
        }

        $frontSize = $tireSpecs['front_tire_size'];
        $rearSize = $tireSpecs['rear_tire_size'];

        return [
            'front_tire' => [
                'size' => $frontSize,
                'load_index' => '',
                'speed_rating' => 'H',
                'construction' => 'Radial',
                'original_brands' => '',
                'alternative_brands' => $this->getRecommendedMotorcycleBrands()
            ],
            'rear_tire' => [
                'size' => $rearSize,
                'load_index' => '',
                'speed_rating' => 'H',
                'construction' => 'Radial',
                'original_brands' => '',
                'alternative_brands' => $this->getRecommendedMotorcycleBrands()
            ],
            'observation' => 'Use sempre pneus radiais com especificação adequada para motocicletas.'
        ];
    }

    /**
     * Obtém marcas recomendadas para motos
     */
    private function getRecommendedMotorcycleBrands(): array
    {
        return ['Michelin', 'Pirelli', 'Bridgestone', 'Continental', 'Dunlop'];
    }

    /**
     * Processa tabela de pressões específica para motocicletas OTIMIZADA
     */
    private function processMotorcyclePressureTable(array $table): array
    {
        if (empty($table)) {
            return $this->generateMotorcyclePressureFromEmbeddedData();
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
     * Gera tabela de pressões de moto a partir de dados embarcados
     */
    private function generateMotorcyclePressureFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        if (empty($pressureSpecs)) {
            return [];
        }

        $frontPsi = $pressureSpecs['pressure_empty_front'];
        $rearPsi = $pressureSpecs['pressure_empty_rear'];
        $frontLoaded = $pressureSpecs['pressure_max_front'] ?? $frontPsi;
        $rearLoaded = $pressureSpecs['pressure_max_rear'] ?? $rearPsi;

        $processed = [
            'official_pressures' => [
                'solo_rider' => [
                    'front' => $frontPsi . ' PSI',
                    'rear' => $rearPsi . ' PSI',
                    'observation' => 'Para piloto até 80kg + equipamentos'
                ],
                'with_passenger' => [
                    'front' => $frontLoaded . ' PSI',
                    'rear' => $rearLoaded . ' PSI',
                    'observation' => 'Piloto + garupa até peso máximo'
                ]
            ],
            'special_conditions' => $this->generateMotorcycleSpecialConditions($frontPsi, $rearPsi)
        ];

        return $processed;
    }

    /**
     * Gera condições especiais para motos
     */
    private function generateMotorcycleSpecialConditions(int $frontPsi, int $rearPsi): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $conditions = [];

        // Uso urbano
        $conditions[] = [
            'situation' => 'Uso urbano',
            'terrain' => 'Cidade/trânsito',
            'front_pressure' => $frontPsi . ' PSI',
            'rear_pressure' => $rearPsi . ' PSI',
            'ideal_temperature' => 'Pneus frios (manhã)',
            'observation' => 'Ideal para uso diário na cidade.',
            'situation_class' => 'moto-situation-urban',
            'icon_class' => 'home'
        ];

        // Viagem rodoviária
        $conditions[] = [
            'situation' => 'Viagem rodoviária',
            'terrain' => 'Rodovias',
            'front_pressure' => ($frontPsi + 2) . ' PSI',
            'rear_pressure' => ($rearPsi + 2) . ' PSI',
            'ideal_temperature' => 'Pneus frios',
            'observation' => 'Para viagens longas acima de 100 km/h.',
            'situation_class' => 'moto-situation-highway',
            'icon_class' => 'map'
        ];

        // Pilotagem esportiva (apenas para motos esportivas)
        if ($this->isSportMotorcycle()) {
            $conditions[] = [
                'situation' => 'Pilotagem esportiva',
                'terrain' => 'Curvas/montanha',
                'front_pressure' => ($frontPsi - 2) . ' PSI',
                'rear_pressure' => ($rearPsi - 2) . ' PSI',
                'ideal_temperature' => 'Conforme aquecimento',
                'observation' => 'Somente para pilotos experientes.',
                'situation_class' => 'moto-situation-sport',
                'icon_class' => 'zap'
            ];
        }

        // Chuva leve
        $conditions[] = [
            'situation' => 'Chuva leve',
            'terrain' => 'Piso molhado',
            'front_pressure' => ($frontPsi - 1) . ' PSI',
            'rear_pressure' => ($rearPsi - 1) . ' PSI',
            'ideal_temperature' => 'Pneus frios',
            'observation' => 'Apenas para pilotagem defensiva.',
            'situation_class' => 'moto-situation-rain',
            'icon_class' => 'cloud-rain'
        ];

        return $conditions;
    }

    /**
     * Processa localização das informações OTIMIZADA
     */
    private function processInformationLocation(array $location): array
    {
        if (empty($location)) {
            return $this->generateDefaultMotorcycleLocation();
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
     * Gera localização padrão para motos
     */
    private function generateDefaultMotorcycleLocation(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        return [
            'owner_manual' => [
                'location' => 'Seção Especificações Técnicas',
                'section' => 'Rodas e Pneus',
                'approximate_page' => 'Consulte índice do manual'
            ],
            'motorcycle_label' => [
                'main_location' => 'Braço da suspensão traseira (swing arm)',
                'alternative_locations' => [
                    'Próximo ao número do chassi (lado direito)',
                    'Manual do proprietário (compartimento sob o assento)',
                    'Etiqueta no chassi principal'
                ]
            ],
            'important_tip' => "A {$vehicleInfo['make']} {$vehicleInfo['model']} pode ter etiqueta com pressões em kgf/cm². Use conversão: 1 kgf/cm² ≈ 14,22 PSI.",
            'visual_guide' => $this->generateMotorcycleVisualGuide([])
        ];
    }

    /**
     * Processa tabela de conversão de unidades OTIMIZADA
     */
    private function processUnitConversion(array $conversion): array
    {
        if (empty($conversion)) {
            return $this->generateMotorcycleConversionFromEmbeddedData();
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
     * Gera conversão de unidades para motos a partir de dados embarcados
     */
    private function generateMotorcycleConversionFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        
        // Pressões típicas para motos baseadas nos dados embarcados
        $basePressures = array_filter([
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ]);

        if (empty($basePressures)) {
            $basePressures = [28, 30, 33, 36, 38, 42]; // Valores típicos para motos
        }

        $table = [];
        foreach (array_unique($basePressures) as $psi) {
            $table[] = [
                'psi' => $psi,
                'kgf_cm2' => round($psi / 14.22, 2),
                'bar' => round($psi / 14.5, 2),
                'is_recommended' => $this->isRecommendedMotorcyclePressure($psi),
                'highlight_class' => $this->getMotorcyclePressureHighlightClass($psi)
            ];
        }

        return [
            'conversion_table' => $table,
            'observation' => 'No Brasil, PSI é o padrão usado nos postos de combustível e calibradores para motocicletas.'
        ];
    }

    /**
     * Processa considerações especiais para motocicletas OTIMIZADA
     */
    private function processSpecialConsiderations(array $considerations): array
    {
        if (empty($considerations)) {
            return $this->generateMotorcycleConsiderationsFromEmbeddedData();
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
     * Gera considerações específicas para motos a partir de dados embarcados
     */
    private function generateMotorcycleConsiderationsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $considerations = [];

        // Temperatura
        $considerations[] = [
            'category' => 'temperatura',
            'title' => 'Impacto da Temperatura',
            'description' => 'Motocicletas são mais sensíveis a variações de temperatura devido ao peso leve.',
            'factors' => [
                'No calor brasileiro (35°C+), pressão pode aumentar 4-5 PSI',
                'Sempre calibre com pneus frios (manhã ou após 3h parado)',
                'Em dias de 40°C+, evite viagens longas no horário mais quente',
                'Pneus quentes podem mostrar até 6 PSI a mais que o real'
            ],
            'orientations' => [],
            'types' => [],
            'icon_class' => 'thermometer',
            'importance' => 'crítica'
        ];

        // Carga
        $considerations[] = [
            'category' => 'carga',
            'title' => 'Ajustes por Carga e Peso',
            'description' => 'O peso influencia significativamente a calibragem em motocicletas.',
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
                'title' => 'Segurança Máxima',
                'description' => 'Pressão correta é fundamental para estabilidade e frenagem em motocicletas.',
                'aspects' => [
                    'Aderência otimizada em curvas e frenagens',
                    'Estabilidade em altas velocidades',
                    'Redução do risco de derrapagem',
                    'Comportamento previsível da motocicleta'
                ],
                'financial_impact' => 'Previne acidentes que podem custar milhares em danos',
                'estimated_savings' => 'Valor inestimável - sua vida',
                'icon_class' => 'shield',
                'color_class' => 'red',
                'priority' => 'crítica'
            ],
            [
                'category' => 'performance',
                'title' => 'Performance Ideal',
                'description' => 'A motocicleta entrega todo seu potencial com pressões corretas.',
                'aspects' => [
                    'Melhor aceleração e retomadas',
                    'Curvas mais precisas e seguras',
                    'Frenagem mais eficiente',
                    'Aproveitamento total da potência'
                ],
                'financial_impact' => 'Melhora experiência de pilotagem',
                'estimated_savings' => 'Satisfação e prazer de pilotar',
                'icon_class' => 'zap',
                'color_class' => 'blue',
                'priority' => 'alta'
            ],
            [
                'category' => 'economia',
                'title' => 'Economia de Combustível',
                'description' => 'Pressão correta reduz consumo e custos operacionais.',
                'aspects' => [
                    'Redução de 10-15% no consumo com pressão ideal',
                    'Menos resistência ao rolamento',
                    'Motor trabalha com menos esforço',
                    'Maior autonomia por tanque'
                ],
                'financial_impact' => 'Economia direta no combustível',
                'estimated_savings' => 'R$ 200-400 por ano em combustível',
                'icon_class' => 'dollar-sign',
                'color_class' => 'green',
                'priority' => 'média'
            ],
            [
                'category' => 'durabilidade',
                'title' => 'Vida Útil dos Pneus',
                'description' => 'Pressão adequada maximiza durabilidade dos pneus.',
                'aspects' => [
                    'Desgaste uniforme e controlado',
                    'Vida útil 30% maior com calibragem correta',
                    'Menos trocas desnecessárias',
                    'Aproveitamento máximo do investimento'
                ],
                'financial_impact' => 'Economia na troca de pneus',
                'estimated_savings' => 'R$ 600-1200 por conjunto de pneus',
                'icon_class' => 'clock',
                'color_class' => 'purple',
                'priority' => 'média'
            ]
        ];
    }

    /**
     * Processa dicas de manutenção específicas para motos OTIMIZADA
     */
    private function processMotorcycleMaintenanceTips(array $tips): array
    {
        if (empty($tips) || !is_array($tips)) {
            return $this->generateMotorcycleMaintenanceFromEmbeddedData();
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
     * Gera dicas de manutenção específicas para motos a partir de dados embarcados
     */
    private function generateMotorcycleMaintenanceFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        
        return [
            [
                'category' => 'Verificação Semanal',
                'frequency' => 'A cada 7 dias ou 300km',
                'importance' => 'crítica',
                'items' => [
                    'Verificar pressão com pneus frios',
                    'Inspecionar desgaste visual dos pneus',
                    'Checar se válvulas estão bem fechadas',
                    'Observar rachaduras ou objetos cravados',
                    'Medir profundidade do sulco (mínimo 1,6mm)'
                ],
                'priority_level' => 5,
                'icon_class' => 'calendar',
                'color_class' => 'red'
            ],
            [
                'category' => 'Condições Ideais de Calibragem',
                'frequency' => 'A cada calibragem',
                'importance' => 'alta',
                'items' => [
                    'Sempre pela manhã ou pneus frios',
                    'Usar calibradores digitais quando possível',
                    'Ambiente com sombra e temperatura amena',
                    'Moto em superfície plana e nivelada',
                    'Verificar ambos os pneus no mesmo momento'
                ],
                'priority_level' => 4,
                'icon_class' => 'sun',
                'color_class' => 'orange'
            ],
            [
                'category' => 'Equipamentos Recomendados',
                'frequency' => 'Investimento único',
                'importance' => 'recomendada',
                'items' => [
                    'Calibrador digital portátil',
                    'Mini compressor 12V para emergências',
                    'Kit reparo tubeless de qualidade',
                    'Medidor de profundidade de sulco',
                    'Bomba manual de backup para viagens'
                ],
                'priority_level' => 2,
                'icon_class' => 'tool',
                'color_class' => 'blue'
            ]
        ];
    }

    /**
     * Processa alertas críticos para motocicletas OTIMIZADA
     */
    private function processCriticalAlerts(array $alerts): array
    {
        if (empty($alerts) || !is_array($alerts)) {
            return $this->generateMotorcycleCriticalAlertsFromEmbeddedData();
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
     * Gera alertas críticos específicos para motos a partir de dados embarcados
     */
    private function generateMotorcycleCriticalAlertsFromEmbeddedData(): array
    {
        return [
            [
                'type' => 'crítico',
                'title' => 'Verificação Semanal Obrigatória',
                'description' => 'Motocicletas perdem pressão mais rapidamente que carros.',
                'consequence' => 'Pneus com 5 PSI baixos podem causar instabilidade fatal a 80 km/h.',
                'severity_class' => 'moto-alert-critical',
                'icon_class' => 'alert-triangle',
                'border_class' => 'border-red-600',
                'is_critical' => true
            ],
            [
                'type' => 'importante',
                'title' => 'Nunca Calibrar com Pneus Quentes',
                'description' => 'Calibrar após pilotagem resulta em subcalibragem perigosa.',
                'consequence' => 'Quando esfriam, ficam muito baixos, causando risco de acidente.',
                'severity_class' => 'moto-alert-important',
                'icon_class' => 'alert-circle',
                'border_class' => 'border-orange-500',
                'is_critical' => false
            ],
            [
                'type' => 'atenção',
                'title' => 'Diferenças Dianteiro/Traseiro',
                'description' => 'Nunca use mesma pressão nos dois pneus.',
                'consequence' => 'Compromete estabilidade, frenagem e vida útil dos pneus.',
                'severity_class' => 'moto-alert-warning',
                'icon_class' => 'info',
                'border_class' => 'border-yellow-500',
                'is_critical' => false
            ]
        ];
    }

    /**
     * Processa procedimento de calibragem para motos OTIMIZADA
     */
    private function processCalibrationProcedure(array $procedure): array
    {
        if (empty($procedure['passos']) || !is_array($procedure['passos'])) {
            return $this->generateMotorcycleCalibrationProcedureFromEmbeddedData();
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
    private function isSportMotorcycle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $mainCategory = $vehicleInfo['main_category'] ?? '';
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($mainCategory, 'sport') || str_contains($type, 'sport') || str_contains($type, 'esportiva');
    }

    /**
     * Verifica se é moto street
     */
    private function isStreetMotorcycle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $mainCategory = $vehicleInfo['main_category'] ?? '';
        $type = strtolower($this->article->extracted_entities['categoria'] ?? '');
        
        return str_contains($mainCategory, 'street') || str_contains($type, 'street') || str_contains($type, 'urbana');
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
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $recommendedPressures = [
            $pressureSpecs['pressure_empty_front'] ?? 33,
            $pressureSpecs['pressure_empty_rear'] ?? 36
        ];
        
        return in_array((int)$psi, $recommendedPressures);
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
     * Processa dados SEO específicos para motocicletas OTIMIZADA
     */
    private function processSeoData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $seoData = $this->article->seo_data ?? [];

        $pressureDisplay = $pressureSpecs['pressure_display'] ?? '';

        return [
            'title' => $seoData['page_title'] ?? "Pressão Ideal para Pneus da {$vehicleInfo['full_name']} - Guia Completo",
            'meta_description' => $seoData['meta_description'] ?? "Pressões ideais para pneus da {$vehicleInfo['full_name']}. {$pressureDisplay}. Dicas específicas para motociclistas brasileiros.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "pressão ideal pneus {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Pressão Ideal para Pneus da {$vehicleInfo['full_name']} – Guia Completo",
            'h2_tags' => $seoData['h2_tags'] ?? [],
            'og_title' => $seoData['og_title'] ?? "Pressão Ideal para Pneus da {$vehicleInfo['full_name']} - Motocicleta",
            'og_description' => $seoData['og_description'] ?? "Guia específico para motociclistas: pressões ideais, dicas de segurança e calibragem da {$vehicleInfo['full_name']}.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org para motocicletas OTIMIZADA
     */
    private function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => "Pressão Ideal para Pneus da {$vehicleFullName}",
            'description' => "Guia específico de pressões ideais para a motocicleta {$vehicleFullName}, incluindo condições especiais e dicas de segurança.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/pressao-ideal-moto.jpg',
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

        if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
            $structuredData['mainEntity'] = [
                '@type' => 'Motorcycle',
                'name' => 'Pressão ideal para ' . $vehicleInfo['make'] . ' ' . $vehicleInfo['model'],
                'brand' => $vehicleInfo['make'],
                'model' => $vehicleInfo['model']
            ];

            if (!empty($vehicleInfo['year'])) {
                $structuredData['mainEntity']['modelDate'] = (string) $vehicleInfo['year'];
            }

            if (!empty($vehicleInfo['engine'])) {
                $structuredData['mainEntity']['engineDisplacement'] = $vehicleInfo['engine'];
            }

            if ($vehicleInfo['is_electric']) {
                $structuredData['mainEntity']['fuelType'] = 'Electric';
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