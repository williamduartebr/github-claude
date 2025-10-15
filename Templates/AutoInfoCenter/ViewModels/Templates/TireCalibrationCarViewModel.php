<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\Traits\VehicleDataProcessingTrait;
use Src\AutoInfoCenter\ViewModels\Templates\Traits\GenericTermsValidationTrait;

class TireCalibrationCarViewModel extends TemplateViewModel
{
    use VehicleDataProcessingTrait,
        GenericTermsValidationTrait;

    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'tire_calibration_car';

    /**
     * Processa dados específicos do template
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

        // OTIMIZADA: Usar dados embarcados primeiro
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        $this->processedData['pressure_specifications'] = $this->processPressureSpecifications();
        $this->processedData['tire_specs_embedded'] = $this->processTireSpecificationsEmbedded();

        // NOVA LÓGICA: Processa tipo de equipamento de emergência
        $this->processedData['emergency_equipment'] = $this->processEmergencyEquipment();

        // Dados auxiliares
        $this->processedData['related_topics'] = $this->getRelatedTopics();
        $this->processedData['structured_data'] = $this->buildStructuredData();
        $this->processedData['seo_data'] = $this->processSeoData();
        $this->processedData['breadcrumbs'] = $this->getBreadcrumbs();
        $this->processedData['canonical_url'] = $this->getCanonicalUrl();
    }

    /**
     * 🔧 NOVA FUNÇÃO: Processa tipo de equipamento de emergência (estepe vs kit)
     */
    private function processEmergencyEquipment(): array
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        $sparePressure = $pressureSpecs['pressure_spare'] ?? 0;
        $hasSpareTire = $sparePressure > 0; // 🎯 LÓGICA PRINCIPAL
        $isElectric = $vehicleInfo['is_electric'] ?? false;
        $isHybrid = $vehicleInfo['is_hybrid'] ?? false;
        $isPremium = $vehicleInfo['is_premium'] ?? false;

        if ($hasSpareTire) {
            return $this->processSpareTireData($sparePressure, $vehicleInfo);
        } else {
            return $this->processRepairKitData($isElectric, $isHybrid, $isPremium, $vehicleInfo);
        }
    }

    /**
     * 🛞 Processa dados do pneu estepe (quando pressure_spare > 0)
     */
    private function processSpareTireData(int $sparePressure, array $vehicleInfo): array
    {
        $spareType = $this->determineSpareTireType($sparePressure);

        return [
            'type' => 'spare_tire',
            'has_spare' => true,
            'pressure' => $sparePressure,
            'spare_type' => $spareType,
            'spare_type_name' => $this->getSpareTireTypeName($spareType),
            'max_speed' => $this->getMaxSpeedForSpare($spareType),
            'max_distance' => $this->getMaxDistanceForSpare($spareType),
            'recommendations' => $this->getSpareTireRecommendations(),
            'verification_frequency' => $this->getSpareVerificationFrequency($spareType),
            'storage_tips' => $this->getSpareStorageTips(),
            'replacement_interval' => $this->getSpareReplacementInterval($spareType)
        ];
    }

    /**
     * 🧰 Processa dados do kit de reparo (quando pressure_spare = 0)
     */
    private function processRepairKitData(bool $isElectric, bool $isHybrid, bool $isPremium, array $vehicleInfo): array
    {
        $normalPressure = $this->processedData['pressure_specifications']['pressure_empty_front'] ?? 35;

        return [
            'type' => 'repair_kit',
            'has_spare' => false,
            'kit_components' => [
                'sealant' => [
                    'name' => 'Selante para Pneus',
                    'description' => 'Para furos até 4mm de diâmetro',
                    'limitations' => 'Não funciona em furos laterais ou rasgos'
                ],
                'compressor' => [
                    'name' => 'Compressor 12V',
                    'description' => 'Portátil para inflagem',
                    'power_source' => 'Tomada 12V do veículo'
                ]
            ],
            'max_speed' => 80, // km/h
            'max_distance' => 150, // km
            'normal_pressure' => $normalPressure,
            'limitations' => [
                'Reparo temporário apenas',
                'Não funciona em furos laterais',
                'Não funciona em rasgos grandes',
                'Pneu deve ser substituído após uso',
                'Não usar em pneus run-flat danificados'
            ],
            'procedure' => $this->getRepairKitProcedure($normalPressure),
            'safety_warnings' => $this->getRepairKitSafetyWarnings(),
            'emergency_contacts' => $isPremium ? $this->getPremiumAssistanceInfo($vehicleInfo) : [],

            // 🔋 Benefícios específicos por tipo de veículo
            'electric_benefits' => $isElectric ? [
                'Mais espaço para bateria (até 50L extras)',
                'Menor peso total do veículo (-15kg)',
                'Maior autonomia elétrica',
                'Melhor distribuição de peso'
            ] : [],

            // 🔄 Benefícios para híbridos
            'hybrid_benefits' => $isHybrid ? [
                'Otimização do espaço para bateria híbrida',
                'Menor peso melhora eficiência do sistema',
                'Mais espaço no porta-malas'
            ] : [],

            'why_no_spare' => $this->getWhyNoSpareExplanation($isElectric, $isHybrid, $isPremium)
        ];
    }

    /**
     * 🎯 Determina tipo do pneu estepe baseado na pressão
     */
    private function determineSpareTireType(int $pressure): string
    {
        if ($pressure >= 50) {
            return 'temporary'; // Temporário (donut) - alta pressão
        } elseif ($pressure >= 35) {
            return 'compact'; // Compacto - pressão moderada
        } else {
            return 'full_size'; // Tamanho original - pressão normal
        }
    }

    /**
     * 📛 Nome amigável do tipo de estepe
     */
    private function getSpareTireTypeName(string $type): string
    {
        return match ($type) {
            'temporary' => 'Estepe Temporário (Donut)',
            'compact' => 'Estepe Compacto',
            'full_size' => 'Estepe Tamanho Original',
            default => 'Estepe Temporário'
        };
    }

    /**
     * 🚗 Velocidade máxima para cada tipo de estepe
     */
    private function getMaxSpeedForSpare(string $type): int
    {
        return match ($type) {
            'temporary' => 80,  // km/h - muito restritivo
            'compact' => 100,   // km/h - moderadamente restritivo  
            'full_size' => 120, // km/h - menos restritivo
            default => 80
        };
    }

    /**
     * 📏 Distância máxima para cada tipo de estepe
     */
    private function getMaxDistanceForSpare(string $type): int
    {
        return match ($type) {
            'temporary' => 80,   // km - muito limitado
            'compact' => 200,    // km - moderadamente limitado
            'full_size' => 999,  // km - sem limite prático
            default => 80
        };
    }

    /**
     * 📝 Recomendações para manutenção do estepe
     */
    private function getSpareTireRecommendations(): array
    {
        return [
            'Verificar pressão mensalmente',
            'Inspecionar visualmente a cada 3 meses',
            'Verificar fixação e ferramentas',
            'Limpar área de armazenamento',
            'Testar macaco e ferramentas semestralmente'
        ];
    }

    /**
     * 🕐 Frequência de verificação do estepe
     */
    private function getSpareVerificationFrequency(string $type): string
    {
        return match ($type) {
            'temporary' => 'Quinzenal (perde pressão mais rápido)',
            'compact' => 'Mensal',
            'full_size' => 'Mensal',
            default => 'Mensal'
        };
    }

    /**
     * 📦 Dicas de armazenamento do estepe
     */
    private function getSpareStorageTips(): array
    {
        return [
            'Evitar exposição ao sol direto',
            'Não colocar objetos pesados sobre ele',
            'Manter área seca e ventilada',
            'Verificar se está bem fixado',
            'Proteger de produtos químicos'
        ];
    }

    /**
     * 🔄 Intervalo de substituição do estepe
     */
    private function getSpareReplacementInterval(string $type): string
    {
        return match ($type) {
            'temporary' => '6-8 anos (mesmo sem uso)',
            'compact' => '8-10 anos',
            'full_size' => '10-12 anos',
            default => '6-8 anos'
        };
    }

    /**
     * 📋 Procedimento detalhado do kit de reparo
     */
    private function getRepairKitProcedure(int $normalPressure): array
    {
        return [
            'Pare em local seguro e sinalize o veículo',
            'Localize o furo e remova objeto (se visível)',
            'Conecte o tubo do selante à válvula do pneu',
            'Injete todo o conteúdo do selante',
            'Conecte o compressor à tomada 12V',
            "Infle até a pressão normal ({$normalPressure} PSI)",
            'Dirija por 5km para distribuir o selante',
            'Verifique pressão novamente',
            'Dirija até borracharia (máx. 80km/h, 150km)'
        ];
    }

    /**
     * ⚠️ Avisos de segurança para kit de reparo
     */
    private function getRepairKitSafetyWarnings(): array
    {
        return [
            'Não usar em pneus run-flat danificados',
            'Não funciona com furos maiores que 4mm',
            'Não reparar furos na lateral do pneu',
            'Não exceder 80 km/h após reparo',
            'Informar borracheiro sobre uso do selante',
            'Substituir pneu o mais rápido possível'
        ];
    }

    /**
     * 🆘 Informações de assistência premium
     */
    private function getPremiumAssistanceInfo(array $vehicleInfo): array
    {
        $make = $vehicleInfo['make'] ?? 'Montadora';

        return [
            'service_name' => "{$make} Assistência 24h",
            'coverage' => 'Reboque até concessionária mais próxima',
            'phone' => 'Consulte manual do proprietário',
            'availability' => '24h por dia, 7 dias por semana',
            'included_services' => [
                'Reboque gratuito (até 150km)',
                'Pneu de cortesia (se disponível)',
                'Borracharia móvel (em algumas regiões)',
                'Chaveiro 24h',
                'Auxílio em pane seca'
            ],
            'app_support' => "Aplicativo {$make} Connect disponível"
        ];
    }

    /**
     * 💡 Explica por que o veículo não tem estepe
     */
    private function getWhyNoSpareExplanation(bool $isElectric, bool $isHybrid, bool $isPremium): array
    {
        $reasons = [];

        if ($isElectric) {
            $reasons[] = [
                'title' => '🔋 Prioridade para Bateria',
                'description' => 'Espaço dedicado para bateria de maior capacidade, aumentando autonomia.'
            ];
            $reasons[] = [
                'title' => '⚖️ Redução de Peso',
                'description' => 'Menos peso = maior eficiência energética e autonomia.'
            ];
        }

        if ($isHybrid) {
            $reasons[] = [
                'title' => '🔄 Sistema Híbrido Complexo',
                'description' => 'Espaço otimizado para bateria híbrida e componentes elétricos.'
            ];
        }

        if ($isPremium) {
            $reasons[] = [
                'title' => '🛠️ Assistência Premium',
                'description' => 'Assistência 24h substitui necessidade de estepe.'
            ];
            $reasons[] = [
                'title' => '🎯 Design Moderno',
                'description' => 'Mais espaço útil no porta-malas para bagagens.'
            ];
        }

        if (empty($reasons)) {
            $reasons[] = [
                'title' => '🚗 Tendência Moderna',
                'description' => 'Muitos veículos modernos priorizam eficiência e espaço.'
            ];
        }

        return $reasons;
    }

    /**
     * Processa tabela de carga completa
     * Rejeita termos genéricos e usa dados embarcados como fallback
     */
    private function processFullLoadTable(array $table): array
    {
        if (empty($table) || $this->hasGenericVersionTerms($table)) {
            return $this->generateLoadTableFromEmbeddedData();
        }

        $processed = [
            'title' => $table['titulo'] ?? 'Tabela de Carga Completa',
            'description' => $table['descricao'] ?? '',
            'conditions' => []
        ];

        if (!empty($table['condicoes']) && is_array($table['condicoes'])) {
            foreach ($table['condicoes'] as $condition) {
                $version = $condition['versao'] ?? '';

                if (!empty($version) && !$this->containsGenericTerms($version)) {
                    $processed['conditions'][] = [
                        'version' => $version,
                        'occupants' => $condition['ocupantes'] ?? '',
                        'luggage' => $condition['bagagem'] ?? '',
                        'front_pressure' => $condition['pressao_dianteira'] ?? '',
                        'rear_pressure' => $condition['pressao_traseira'] ?? '',
                        'observation' => $condition['observacao'] ?? '',
                        'css_class' => $this->getLoadConditionCssClass($condition['ocupantes'] ?? '')
                    ];
                }
            }
        }

        return empty($processed['conditions'])
            ? $this->generateLoadTableFromEmbeddedData()
            : $processed;
    }
   

    /**
     * Processa especificações dos pneus por versão
     * Rejeita termos genéricos retornando array vazio
     */
    private function processTireSpecificationsByVersion(array $specs): array
    {
        if (empty($specs)) {
            return [];
        }

        // Verificar se há termos genéricos na lista
        foreach ($specs as $spec) {
            $version = $spec['versao'] ?? '';
            if (!empty($version) && $this->containsGenericTerms($version)) {
                return []; // Retorna vazio se encontrar termo genérico
            }
        }

        $processed = [];

        foreach ($specs as $spec) {
            $version = $spec['versao'] ?? '';
            if (!empty($version)) {
                // Lógica para medida dos pneus - suporta múltiplas estruturas
                $tireSize = '';
                $frontTire = '';
                $rearTire = '';
                $hasDifferentSizes = false;

                if (!empty($spec['medida_pneus'])) {
                    // Estrutura Hilux: campo único
                    $tireSize = $spec['medida_pneus'];
                    $frontTire = $spec['medida_pneus'];
                    $rearTire = $spec['medida_pneus'];
                } elseif (!empty($spec['pneu_dianteiro']) || !empty($spec['pneu_traseiro'])) {
                    // Estrutura Frontier: campos separados
                    $frontTire = $spec['pneu_dianteiro'] ?? '';
                    $rearTire = $spec['pneu_traseiro'] ?? '';
                    $hasDifferentSizes = $frontTire !== $rearTire;
                    $tireSize = $hasDifferentSizes ? "{$frontTire} (Diant.) / {$rearTire} (Tras.)" : $frontTire;
                }

                // Lógica para pressões - LIMPA PSI DUPLICADO
                $frontNormal = $this->cleanPressureValue($spec['pressao_dianteiro_normal'] ?? $spec['pressao_dianteira'] ?? '');
                $rearNormal = $this->cleanPressureValue($spec['pressao_traseiro_normal'] ?? $spec['pressao_traseira'] ?? '');
                $frontLoaded = $this->cleanPressureValue($spec['pressao_dianteiro_carregado'] ?? $spec['pressao_dianteira_carregado'] ?? $frontNormal);
                $rearLoaded = $this->cleanPressureValue($spec['pressao_traseiro_carregado'] ?? $spec['pressao_traseira_carregado'] ?? $rearNormal);

                $processed[] = [
                    'version' => $version,
                    'motor' => $spec['motor'] ?? '',
                    'potencia' => $spec['potencia'] ?? '',
                    'transmissao' => $spec['transmissao'] ?? '',
                    'tracao' => $spec['tracao'] ?? '',
                    'tire_size' => $tireSize,
                    'front_tire_size' => $frontTire,
                    'rear_tire_size' => $rearTire,
                    'load_speed_index' => $spec['indice_carga_velocidade'] ?? '',
                    'front_normal' => $frontNormal,
                    'rear_normal' => $rearNormal,
                    'front_loaded' => $frontLoaded,
                    'rear_loaded' => $rearLoaded,
                    'css_class' => $this->getVersionCssClass($version),
                    'has_different_tire_sizes' => $hasDifferentSizes
                ];
            }
        }

        return $processed;
    }

    /**
     * Remove "psi" ou "PSI" dos valores de pressão para evitar duplicação
     */
    private function cleanPressureValue($value): string
    {
        if (empty($value)) {
            return '';
        }

        // Convert to string se for numérico
        $cleanValue = (string) $value;

        // Remove variações de "psi" (case insensitive) e espaços extras
        $cleanValue = preg_replace('/\s*(psi|PSI)\s*$/i', '', $cleanValue);

        // Remove espaços extras
        $cleanValue = trim($cleanValue);

        return $cleanValue;
    }


    /**
     * 📊 Gera tabela de carga a partir de dados embarcados
     * Este método já existe, mas mantendo referência para clareza
     */
    private function generateLoadTableFromEmbeddedData(): array
    {
        $pressureSpecs = $this->processPressureSpecifications();
        $vehicleInfo = $this->processVehicleInfo();

        if (empty($pressureSpecs)) {
            return [];
        }

        return [
            'title' => 'Pressões para Diferentes Condições de Carga',
            'description' => 'Use estas pressões conforme a ocupação e bagagem do veículo.',
            'conditions' => [
                [
                    'version' => 'Uso Normal',
                    'occupants' => '1-2 pessoas',
                    'luggage' => 'Bagagem leve',
                    'front_pressure' => ($pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                    'observation' => 'Uso urbano e rodoviário',
                    'css_class' => 'bg-green-50 border-green-200'
                ],
                [
                    'version' => 'Carga Média',
                    'occupants' => '3-4 pessoas',
                    'luggage' => 'Bagagem moderada',
                    'front_pressure' => ($pressureSpecs['pressure_light_front'] ?? $pressureSpecs['pressure_empty_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_light_rear'] ?? $pressureSpecs['pressure_empty_rear'] ?? '') . ' PSI',
                    'observation' => 'Família com bagagem',
                    'css_class' => 'bg-yellow-50 border-yellow-200'
                ],
                [
                    'version' => 'Carga Completa',
                    'occupants' => '4-5 pessoas',
                    'luggage' => 'Porta-malas cheio',
                    'front_pressure' => ($pressureSpecs['pressure_max_front'] ?? '') . ' PSI',
                    'rear_pressure' => ($pressureSpecs['pressure_max_rear'] ?? '') . ' PSI',
                    'observation' => $vehicleInfo['is_electric'] ? 'Peso da bateria considerado' : 'Ideal para viagens',
                    'css_class' => 'bg-blue-50 border-blue-200'
                ]
            ]
        ];
    }

    /**
     * Processa localização da etiqueta OTIMIZADA
     */
    private function processLabelLocation(array $location): array
    {
        if (empty($location)) {
            return $this->generateLabelLocationFromEmbeddedData();
        }

        $processed = [
            'main_location' => $location['local_principal'] ?? '',
            'alternative_locations' => $location['locais_alternativos'] ?? [],
            'description' => $location['descricao'] ?? '',
            'note' => $location['observacao'] ?? ''
        ];

        return $processed;
    }

    /**
     * Gera localização da etiqueta a partir de dados embarcados
     */
    private function generateLabelLocationFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        return [
            'main_location' => 'Porta do motorista (coluna B)',
            'alternative_locations' => [
                'Porta-luvas',
                'Manual do proprietário',
                'Aplicativo da montadora',
                'Site oficial da marca'
            ],
            'description' => 'A etiqueta oficial está localizada na coluna central da porta do motorista, próximo à fechadura.',
            'visual_guide' => [
                'Abra completamente a porta do motorista',
                'Procure na coluna central (pilar B)',
                'Etiqueta branca com informações em português',
                'Contém pressões para uso normal e carga completa'
            ],
            'note' => 'Alguns veículos premium possuem as informações também no painel digital.'
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
            'recommended_adjustment' => '+2 PSI',
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
        if ($vehicleInfo['is_electric'] ?? false) {
            $conditions[] = [
                'condition' => 'Modo Eco (Elétrico)',
                'recommended_adjustment' => '+1 PSI',
                'application' => 'Para maximizar autonomia da bateria',
                'justification' => 'Reduz resistência ao rolamento, aumentando eficiência energética.',
                'icon_class' => 'battery',
                'has_load_table_reference' => false
            ];
        }

        // Condição para pneus novos
        $conditions[] = [
            'condition' => 'Pneus Novos',
            'recommended_adjustment' => 'Pressão padrão',
            'application' => 'Primeiros 1000 km',
            'justification' => 'Permita o amaciamento natural sem sobrepressão.',
            'icon_class' => 'refresh-cw',
            'has_load_table_reference' => false
        ];

        return $conditions;
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
            'reference_pressure' => $conversion['pressao_referencia'] ?? '',
            'observation' => $conversion['observacao'] ?? ''
        ];

        if (!empty($conversion['tabela_conversao']) && is_array($conversion['tabela_conversao'])) {
            foreach ($conversion['tabela_conversao'] as $row) {
                $processed['conversion_table'][] = [
                    'psi' => $row['psi'] ?? '',
                    'kgf_cm2' => $row['kgf_cm2'] ?? '',
                    'bar' => $row['bar'] ?? '',
                    'is_recommended' => $this->isRecommendedPressure($row['psi'] ?? ''),
                    'highlight_class' => $this->getPressureHighlightClass($row['psi'] ?? '')
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

        $pressureSpecs = $this->processPressureSpecifications();

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
            'reference_pressure' => $pressureSpecs['pressure_display'] ?? '',
            'observation' => 'PSI é a unidade padrão no Brasil. Conversões aproximadas.'
        ];
    }

    /**
     * Processa cuidados e recomendações OTIMIZADA
     */
    private function processCareRecommendations(array $recommendations): array
    {
        if (empty($recommendations)) {
            return $this->generateCareRecommendationsFromEmbeddedData();
        }

        $processed = [];

        foreach ($recommendations as $category => $recommendation) {
            if (!empty($recommendation['titulo'])) {
                $processed[] = [
                    'category' => $category,
                    'title' => $recommendation['titulo'],
                    'description' => $recommendation['descricao'] ?? '',
                    'frequency' => $recommendation['frequencia'] ?? '',
                    'procedures' => $recommendation['procedimentos'] ?? [],
                    'tools_needed' => $recommendation['ferramentas_necessarias'] ?? [],
                    'safety_tips' => $recommendation['dicas_seguranca'] ?? [],
                    'icon_class' => $this->getCareRecommendationIconClass($category),
                    'color_class' => $this->getCareRecommendationColorClass($category)
                ];
            }
        }

        return $processed;
    }

    /**
     * Gera cuidados e recomendações a partir de dados embarcados
     */
    private function generateCareRecommendationsFromEmbeddedData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];

        return [
            [
                'category' => 'verificacao_mensal',
                'title' => 'Verificação Mensal',
                'description' => 'Rotina básica de manutenção preventiva.',
                'frequency' => 'Mensal ou a cada 1.000 km',
                'procedures' => [
                    'Verificar pressão com pneus frios',
                    'Inspecionar visualmente os pneus',
                    'Verificar profundidade dos sulcos',
                    'Observar desgaste irregular',
                    'Incluir o estepe na verificação'
                ],
                'tools_needed' => ['Calibrador', 'Moedas para medição de sulco'],
                'safety_tips' => [
                    'Sempre verificar com pneus frios',
                    'Usar calibrador confiável',
                    'Verificar todas as rodas, incluindo estepe'
                ],
                'icon_class' => 'calendar',
                'color_class' => 'from-blue-100 to-blue-200'
            ],
            [
                'category' => 'cuidados_especiais',
                'title' => 'Cuidados Especiais',
                'description' => 'Atenção extra para maximizar segurança e durabilidade.',
                'frequency' => 'Conforme necessidade',
                'procedures' => [
                    'Calibrar antes de viagens longas',
                    'Ajustar pressão conforme carga',
                    'Verificar após mudanças bruscas de temperatura',
                    $vehicleInfo['has_tpms'] ? 'Monitorar alertas do TPMS' : 'Atenção redobrada sem TPMS',
                    $vehicleInfo['is_electric'] ? 'Verificar pressão para máxima autonomia' : 'Otimizar para economia de combustível'
                ],
                'tools_needed' => ['Calibrador digital', 'Compressor portátil'],
                'safety_tips' => [
                    'Nunca exceder pressões máximas',
                    'Atenção especial em pneus run-flat',
                    'Verificar mais frequentemente no verão'
                ],
                'icon_class' => 'shield',
                'color_class' => 'from-green-100 to-green-200'
            ]
        ];
    }

    /**
     * Processa impacto da pressão OTIMIZADA
     */
    private function processPressureImpact(array $impact): array
    {
        if (empty($impact)) {
            return $this->generateImpactFromEmbeddedData();
        }

        $processed = [];

        foreach ($impact as $key => $impactData) {
            if (!empty($impactData['tipo'])) {
                $processed[] = [
                    'type' => $key,
                    'title' => $impactData['titulo'] ?? '',
                    'items' => $impactData['items'] ?? $impactData['beneficios'] ?? [],
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

        $topics = [];

        // Tópicos gerais de manutenção
        $topics[] = [
            'title' => 'Troca de Óleo para ' . $vehicleInfo['make'],
            'url' => '/info/troca-oleo-' . strtolower($vehicleInfo['make']),
            'description' => 'Intervalos e especificações para troca de óleo'
        ];

        // Tópicos específicos por tipo de veículo
        if ($vehicleInfo['is_electric'] ?? false) {
            $topics[] = [
                'title' => 'Manutenção de Carros Elétricos',
                'url' => '/info/manutencao-carros-eletricos',
                'description' => 'Cuidados específicos para veículos elétricos'
            ];
        }

        if ($vehicleInfo['has_tpms'] ?? false) {
            $topics[] = [
                'title' => 'Como Funciona o Sistema TPMS',
                'url' => '/info/sistema-tpms-monitoramento-pressao',
                'description' => 'Entenda o sistema de monitoramento de pressão'
            ];
        }

        // Tópicos por categoria
        $segment = $vehicleInfo['segment'] ?? '';
        if (str_contains(strtolower($segment), 'suv')) {
            $topics[] = [
                'title' => 'Pneus para SUVs - Guia Completo',
                'url' => '/info/pneus-suvs-guia-completo',
                'description' => 'Escolha e manutenção de pneus para SUVs'
            ];
        }

        $topics[] = [
            'title' => 'Quando Trocar os Pneus',
            'url' => '/info/quando-trocar-pneus',
            'description' => 'Sinais de que é hora de trocar os pneus'
        ];

        return $topics;
    }

    /**
     * Métodos auxiliares para classes CSS e ícones
     */

    private function getVersionCssClass(string $version): string
    {
        $lowercaseVersion = strtolower($version);

        if (str_contains($lowercaseVersion, 'sport') || str_contains($lowercaseVersion, 'gts')) {
            return 'bg-red-50 border-red-200';
        }

        if (str_contains($lowercaseVersion, 'luxury') || str_contains($lowercaseVersion, 'premium')) {
            return 'bg-purple-50 border-purple-200';
        }

        return 'bg-gray-50 border-gray-200';
    }

    private function getLoadConditionCssClass(string $occupants): string
    {
        if (str_contains($occupants, '1-2')) {
            return 'bg-green-50 border-green-200';
        }

        if (str_contains($occupants, '3-4')) {
            return 'bg-yellow-50 border-yellow-200';
        }

        return 'bg-blue-50 border-blue-200';
    }

    private function getConditionIconClass(string $condition): string
    {
        $lowercaseCondition = strtolower($condition);

        if (str_contains($lowercaseCondition, 'viagem') || str_contains($lowercaseCondition, 'rodovia')) {
            return 'trending-up';
        }

        if (str_contains($lowercaseCondition, 'carga')) {
            return 'package';
        }

        if (str_contains($lowercaseCondition, 'elétrico') || str_contains($lowercaseCondition, 'eco')) {
            return 'battery';
        }

        if (str_contains($lowercaseCondition, 'novo')) {
            return 'refresh-cw';
        }

        return 'settings';
    }

    private function hasLoadTableReference(string $adjustment): bool
    {
        return str_contains(strtolower($adjustment), 'tabela');
    }

    private function hasLoadTableData(): bool
    {
        return !empty($this->processedData['full_load_table']['conditions']);
    }

    private function isRecommendedPressure(string $psi): bool
    {
        $pressureSpecs = $this->processPressureSpecifications();

        $recommendedPressures = [
            $pressureSpecs['pressure_empty_front'] ?? null,
            $pressureSpecs['pressure_empty_rear'] ?? null,
            $pressureSpecs['pressure_max_front'] ?? null,
            $pressureSpecs['pressure_max_rear'] ?? null
        ];

        return in_array((int)$psi, array_filter($recommendedPressures));
    }

    private function getPressureHighlightClass(string $psi): string
    {
        return $this->isRecommendedPressure($psi) ? 'highlight-pressure' : '';
    }

    private function getCareRecommendationIconClass(string $category): string
    {
        $iconMap = [
            'verificacao_mensal' => 'calendar',
            'cuidados_especiais' => 'shield',
            'ferramentas' => 'tool',
            'seguranca' => 'alert-triangle'
        ];

        return $iconMap[$category] ?? 'wrench';
    }

    private function getCareRecommendationColorClass(string $category): string
    {
        $colorMap = [
            'verificacao_mensal' => 'from-blue-100 to-blue-200',
            'cuidados_especiais' => 'from-green-100 to-green-200',
            'ferramentas' => 'from-purple-100 to-purple-200',
            'seguranca' => 'from-red-100 to-red-200'
        ];

        return $colorMap[$category] ?? 'from-gray-100 to-gray-200';
    }

    private function getImpactColor(string $type): string
    {
        $colorMap = [
            'subcalibrado' => 'red',
            'ideal' => 'green',
            'sobrecalibrado' => 'amber',
            'correto' => 'green'
        ];

        return $colorMap[$type] ?? 'gray';
    }

    private function getImpactIconClass(string $type): string
    {
        $iconMap = [
            'subcalibrado' => 'minus',
            'ideal' => 'check',
            'sobrecalibrado' => 'alert-triangle',
            'correto' => 'check'
        ];

        return $iconMap[$type] ?? 'info';
    }

    private function getImpactCssClass(string $type): string
    {
        $color = $this->getImpactColor($type);
        return "from-{$color}-100 to-{$color}-200";
    }

    /**
     * Sobrescreve dados de SEO para foco em "calibragem" para automóveis
     */
    protected function processSeoData(): array
    {

        $pressureSpecs = $this->processPressureSpecifications();
        $vehicleInfo = $this->processVehicleInfo();

        $seoData = $this->article->seo_data ?? [];

        $pressureDisplay = $pressureSpecs['pressure_display'] ?? '';

        return [
            'title' => $seoData['page_title'] ?? "Calibragem do Pneu do {$vehicleInfo['full_name']} – Guia Completo",
            'meta_description' => $seoData['meta_description'] ?? "Guia completo de calibragem dos pneus do {$vehicleInfo['full_name']}. {$pressureDisplay}. Procedimento passo-a-passo e dicas para o Brasil.",
            'keywords' => $seoData['secondary_keywords'] ?? [],
            'focus_keyword' => $seoData['primary_keyword'] ?? "calibragem pneu {$vehicleInfo['make']} {$vehicleInfo['model']} {$vehicleInfo['year']}",
            'canonical_url' => $this->getCanonicalUrl(),
            'h1' => $seoData['h1'] ?? "Calibragem do Pneu do {$vehicleInfo['full_name']} – Guia Completo",
            'h2_tags' => $seoData['h2_tags'] ?? [
                'Especificações dos Pneus Originais por Versão',
                'Procedimento de Calibragem (PSI - Padrão Brasileiro)',
                'Tabela de Pressões por Condição de Uso',
                'Localização da Etiqueta de Pressão',
                'Ajustes para Condições Especiais',
                'Conversão de Unidades - PSI (Padrão Brasileiro)',
                'Cuidados e Recomendações de Calibragem',
                'Impacto da Calibragem no Desempenho',
                'Perguntas Frequentes sobre Calibragem'
            ],
            'og_title' => $seoData['og_title'] ?? "Calibragem do Pneu do {$vehicleInfo['full_name']} – Guia Oficial",
            'og_description' => $seoData['og_description'] ?? "Procedimento completo de calibragem dos pneus do {$vehicleInfo['full_name']}. {$pressureDisplay}.",
            'og_image' => $seoData['og_image'] ?? $vehicleInfo['image_url'] ?? '',
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Constrói dados estruturados Schema.org focado em calibragem
     */
    protected function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        $vehicleFullName = $vehicleInfo['full_name'];

        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'name' => "Calibragem do Pneu do {$vehicleFullName}",
            'description' => "Guia específico de calibragem dos pneus do {$vehicleFullName}, incluindo procedimento passo-a-passo e pressões por condição de uso.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/tire-calibration.png',
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
                'name' => 'Calibragem de Pneus de Automóvel',
                'description' => 'Procedimentos específicos de calibragem para automóveis'
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCanonicalUrl()
            ]
        ];

        if (!empty($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
            $vehicleType = $vehicleInfo['is_electric'] ? 'Vehicle' : 'Car';

            $structuredData['mainEntity'] = [
                '@type' => $vehicleType,
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

            if ($vehicleInfo['is_electric']) {
                $structuredData['mainEntity']['fuelType'] = 'Electric';
            } elseif ($vehicleInfo['is_hybrid']) {
                $structuredData['mainEntity']['fuelType'] = 'Hybrid';
            } elseif (!empty($vehicleInfo['fuel'])) {
                $structuredData['mainEntity']['fuelType'] = $vehicleInfo['fuel'];
            }
        }

        // Adiciona informações específicas sobre calibragem
        $pressureSpecs = $this->processPressureSpecifications();

        if (!empty($pressureSpecs)) {
            $structuredData['mainEntity']['maintenanceSchedule'] = [
                '@type' => 'MaintenanceSchedule',
                'name' => 'Calibragem de Pneus',
                'description' => 'Pressões recomendadas para calibragem dos pneus',
                'frequency' => 'Monthly' // Automóveis requerem verificação mensal
            ];
        }

        return $structuredData;
    }


    /**
     * Verifica se é veículo elétrico
     */
    private function isElectricVehicle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_electric'] ?? false;
    }

    /**
     * Verifica se é veículo híbrido
     */
    private function isHybridVehicle(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_hybrid'] ?? false;
    }

    /**
     * Verifica se tem sistema TPMS
     */
    public function hasTpmsSystem(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['has_tpms'] ?? false;
    }

    /**
     * Verifica se é veículo premium
     */
    private function isPremiumVehicleFromData(): bool
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['is_premium'] ?? false;
    }

    /**
     * Obtém categoria do veículo
     */
    private function getVehicleCategory(): string
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['category'] ?? '';
    }

    /**
     * Obtém segmento do veículo
     */
    private function getVehicleSegmentFromData(): string
    {
        $vehicleInfo = $this->processedData['vehicle_info'] ?? [];
        return $vehicleInfo['segment'] ?? '';
    }

    /**
     * Verifica se tem estepe
     */
    private function hasSpareTire(): bool
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        return $pressureSpecs['has_spare_tire'] ?? false;
    }

    /**
     * Obtém pressão do estepe
     */
    private function getSpareTirePressure(): ?int
    {
        $pressureSpecs = $this->processedData['pressure_specifications'] ?? [];
        return $pressureSpecs['pressure_spare'] ?? null;
    }

    /**
     * Gera alertas específicos para carros elétricos
     */
    private function generateElectricVehicleAlerts(): array
    {
        if (!$this->isElectricVehicle()) {
            return [];
        }

        return [
            [
                'type' => 'info',
                'title' => 'Veículo Elétrico - Pressão Otimizada',
                'description' => 'Pressão correta maximiza a autonomia da bateria.',
                'items' => [
                    'Cada PSI incorreto reduz autonomia',
                    'Verificar pressão semanalmente',
                    'Considerar peso extra da bateria',
                    'Usar modo Eco quando disponível'
                ]
            ]
        ];
    }

    /**
     * Gera alertas específicos para sistema TPMS
     */
    private function generateTpmsAlerts(): array
    {
        if (!$this->hasTpmsSystem()) {
            return [];
        }

        return [
            [
                'type' => 'info',
                'title' => 'Sistema TPMS Ativo',
                'description' => 'Seu veículo monitora a pressão automaticamente.',
                'items' => [
                    'Alertas aparecem no painel',
                    'Não substitui verificação manual',
                    'Reset pode ser necessário após calibragem',
                    'Consulte manual para procedimentos'
                ]
            ]
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
