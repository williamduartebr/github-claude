<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Illuminate\Support\Str;

class WhenToChangeTiresViewModel extends TemplateViewModel
{
    /**
     * Nome do template a ser utilizado
     */
    protected string $templateName = 'when_to_change_tires';

    /**
     * Processa dados específicos do template de quando trocar pneus
     */
    protected function processTemplateSpecificData(): void
    {
        // Extrai dados específicos do conteúdo do artigo
        $content = $this->article->content;

        // Introdução
        $this->processedData['introduction'] = $content['introducao'] ?? '';

        // Sintomas de desgaste dos pneus
        $this->processedData['wear_symptoms'] = $this->processWearSymptoms($content['sintomas_desgaste'] ?? []);

        // Fatores que afetam a durabilidade
        $this->processedData['durability_factors'] = $this->processDurabilityFactors($content['fatores_durabilidade'] ?? []);

        // Cronograma de verificação
        $this->processedData['verification_schedule'] = $this->processVerificationSchedule($content['cronograma_verificacao'] ?? []);

        // Tipos de pneus e quilometragem esperada
        $this->processedData['tire_types'] = $this->processTireTypes($content['tipos_pneus'] ?? []);

        // Sinais críticos para substituição
        $this->processedData['critical_signs'] = $this->processCriticalSigns($content['sinais_criticos'] ?? []);

        // Manutenção preventiva
        $this->processedData['preventive_maintenance'] = $this->processPreventiveMaintenance($content['manutencao_preventiva'] ?? []);

        // Procedimento de verificação
        $this->processedData['verification_procedure'] = $this->processVerificationProcedure($content['procedimento_verificacao'] ?? []);

        // Perguntas frequentes
        $this->processedData['faq'] = $content['perguntas_frequentes'] ?? [];

        // Considerações finais
        $this->processedData['final_considerations'] = $content['consideracoes_finais'] ?? '';

        // Dados do veículo específicos
        $this->processedData['vehicle_data'] = $this->processVehicleData($content['vehicle_data'] ?? []);

        // Nome completo do veículo
        $this->processedData['vehicle_full_name'] = $this->getVehicleFullName();

        // Dados estruturados para SEO
        $this->processStructuredDataForSEO();
    }

    /**
     * Processa dados estruturados para SEO específicos do template de troca de pneus
     * MÉTODO CORRIGIDO: Usa extracted_entities, valida dados e usa Article para consistência
     */
    private function processStructuredDataForSEO(): void
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];
        $content = $this->article->content;

        // Imagem padrão baseada no tipo de veículo
        $vehicleType = $vehicleInfo['tipo_veiculo'] ?? '';
        if (!empty($vehicleType)) {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/trocar-pneus-{$vehicleType}.png";
        } else {
            $imageDefault = "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/when_to_change_tires.png";
        }

        // CORREÇÃO: Estrutura base do schema usando Article (consistente com outros ViewModels)
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article', // ✅ Mudou de HowTo para Article (mais seguro)
            'name' => $this->article->title,
            'headline' => $this->article->title,
            'description' => $content['introducao'] ?? 'Guia completo sobre quando trocar os pneus do seu veículo',
            'datePublished' => $this->article->created_at->utc()->toAtomString(),
            'dateModified' => $this->article->updated_at->utc()->toAtomString(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->article->author['name'] ?? 'Equipe Editorial',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg',
                ],
            ],
            'image' => [
                '@type' => 'ImageObject',
                'url' => $this->processedData['vehicle_data']['image_url'] ?? $imageDefault,
                'width' => 1200,
                'height' => 630
            ],
        ];

        // VALIDAÇÃO: Só adiciona dados do veículo se marca E modelo existirem
        if (!empty($vehicleInfo['marca']) && !empty($vehicleInfo['modelo'])) {
            // Determina o tipo baseado no tipo_veiculo
            $vehicleSchemaType = ($vehicleInfo['tipo_veiculo'] ?? '') === 'motorcycle' ? 'Motorcycle' : 'Vehicle';

            $structuredData['about'] = [
                '@type' => $vehicleSchemaType,
                'name' => 'Quando trocar pneus do ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
                'brand' => $vehicleInfo['marca'], // marca → brand
                'model' => $vehicleInfo['modelo'], // modelo → model
            ];

            // Adiciona ano se existir
            if (!empty($vehicleInfo['ano'])) {
                $structuredData['about']['vehicleModelDate'] = (string) $vehicleInfo['ano']; // ano → vehicleModelDate
            }

            // Adiciona motorização se existir
            if (!empty($vehicleInfo['motorizacao'])) {
                $structuredData['about']['vehicleEngine'] = [
                    '@type' => 'EngineSpecification',
                    'engineDisplacement' => $vehicleInfo['motorizacao']
                ];
            }

            // Propriedades adicionais
            $additionalProperties = [];

            // Adiciona versão se existir e não for "Todas"
            if (!empty($vehicleInfo['versao']) && $vehicleInfo['versao'] !== 'Todas') {
                $additionalProperties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Version',
                    'value' => $vehicleInfo['versao']
                ];
            }

            // Adiciona combustível se existir
            if (!empty($vehicleInfo['combustivel'])) {
                $additionalProperties[] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Fuel Type',
                    'value' => ucfirst($vehicleInfo['combustivel'])
                ];
            }

            // Só adiciona additionalProperty se houver propriedades
            if (!empty($additionalProperties)) {
                $structuredData['about']['additionalProperty'] = $additionalProperties;
            }

            // ✅ NOVO: Adiciona HowTo como mainEntity quando há dados do veículo
            $structuredData['mainEntity'] = [
                '@type' => 'HowTo',
                'name' => 'Como Verificar Quando Trocar os Pneus do ' . $vehicleInfo['marca'] . ' ' . $vehicleInfo['modelo'],
                'description' => 'Guia passo a passo para verificar sinais de desgaste e determinar o momento ideal para trocar os pneus',
                'totalTime' => 'PT15M',
                'estimatedCost' => [
                    '@type' => 'MonetaryAmount',
                    'currency' => 'BRL',
                    'value' => '300-1200'
                ],
                'supply' => [
                    [
                        '@type' => 'HowToSupply',
                        'name' => 'Calibrador de pneus'
                    ],
                    [
                        '@type' => 'HowToSupply',
                        'name' => 'Moeda para teste de profundidade'
                    ],
                    [
                        '@type' => 'HowToSupply',
                        'name' => 'Lanterna'
                    ]
                ],
                'tool' => [
                    [
                        '@type' => 'HowToTool',
                        'name' => 'Medidor de pressão'
                    ]
                ],
                'step' => $this->buildHowToSteps()
            ];
        }

        $this->processedData['structured_data'] = $structuredData;

        // URLs canônica e alternativas
        $this->processedData['canonical_url'] = route('info.article.show', $this->article->slug);

        // Breadcrumbs
        $this->processedData['breadcrumbs'] = [
            [
                'name' => 'Início',
                'url' => url('/'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->article->category_name ?? ''),
                'url' => route('info.category.show', $this->article->category_slug ?? ''),
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
     * Processa sintomas de desgaste dos pneus
     */
    private function processWearSymptoms(array $symptoms): array
    {
        $processed = [];

        foreach ($symptoms as $key => $symptom) {
            if (is_array($symptom) && !empty($symptom['titulo'])) {
                $processed[] = [
                    'key' => $key,
                    'title' => $symptom['titulo'],
                    'description' => $symptom['descricao'] ?? '',
                    'severity' => $symptom['severidade'] ?? 'media',
                    'action' => $symptom['acao'] ?? '',
                    'severity_class' => $this->getSeverityClass($symptom['severidade'] ?? 'media')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa fatores que afetam a durabilidade
     */
    private function processDurabilityFactors(array $factors): array
    {
        $processed = [];

        foreach ($factors as $key => $factor) {
            if (is_array($factor) && !empty($factor['titulo'])) {
                $impact = $factor['impacto_negativo'] ?? $factor['impacto_positivo'] ?? '';
                $isPositive = isset($factor['impacto_positivo']);

                $processed[] = [
                    'key' => $key,
                    'title' => $factor['titulo'],
                    'description' => $factor['descricao'] ?? '',
                    'impact' => $impact,
                    'is_positive' => $isPositive,
                    'impact_class' => $isPositive ? 'positive' : 'negative',
                    'recommendation' => $factor['recomendacao'] ?? $factor['dica'] ?? $factor['beneficio'] ?? '',
                    'pressure_recommendation' => $factor['pressao_recomendada'] ?? ''
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa cronograma de verificação
     */
    private function processVerificationSchedule(array $schedule): array
    {
        $processed = [];

        foreach ($schedule as $key => $item) {
            if (is_array($item) && !empty($item['titulo'])) {
                $processed[] = [
                    'key' => $key,
                    'title' => $item['titulo'],
                    'description' => $item['descricao'] ?? '',
                    'importance' => $item['importancia'] ?? 'media',
                    'importance_class' => $this->getImportanceClass($item['importancia'] ?? 'media')
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa tipos de pneus
     */
    private function processTireTypes(array $types): array
    {
        $processed = [];

        foreach ($types as $key => $type) {
            if (is_array($type) && !empty($type['tipo'])) {
                $processed[] = [
                    'key' => $key,
                    'type' => $type['tipo'],
                    'expected_mileage' => $type['quilometragem_esperada'] ?? '',
                    'application' => $type['aplicacao'] ?? '',
                    'observations' => $type['observacoes'] ?? ''
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa sinais críticos para substituição
     */
    private function processCriticalSigns(array $signs): array
    {
        $processed = [];

        foreach ($signs as $key => $sign) {
            if (is_array($sign) && !empty($sign['titulo'])) {
                $processed[] = [
                    'key' => $key,
                    'title' => $sign['titulo'],
                    'legal_limit' => $sign['limite_legal'] ?? '',
                    'recommended_limit' => $sign['limite_recomendado'] ?? '',
                    'test' => $sign['teste'] ?? '',
                    'action' => $sign['acao'] ?? '',
                    'types' => $sign['tipos'] ?? [],
                    'patterns' => $sign['padroes'] ?? []
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa manutenção preventiva
     */
    private function processPreventiveMaintenance(array $maintenance): array
    {
        $processed = [];

        // Processar verificação de pressão
        if (!empty($maintenance['verificacao_pressao'])) {
            $processed['verificacao_pressao'] = $maintenance['verificacao_pressao'];
        }

        // Processar rodízio
        if (!empty($maintenance['rodizio'])) {
            $processed['rodizio'] = $maintenance['rodizio'];
        }

        // Processar alinhamento e balanceamento
        if (!empty($maintenance['alinhamento_balanceamento'])) {
            $processed['alinhamento_balanceamento'] = $maintenance['alinhamento_balanceamento'];
        }

        // Processar cuidados gerais
        if (!empty($maintenance['cuidados_gerais']) && is_array($maintenance['cuidados_gerais'])) {
            $processed['cuidados_gerais'] = $maintenance['cuidados_gerais'];
        }

        // Manter compatibilidade com estrutura antiga (tasks)
        foreach ($maintenance as $key => $item) {
            if (!in_array($key, ['verificacao_pressao', 'rodizio', 'alinhamento_balanceamento', 'cuidados_gerais'])) {
                if (is_array($item) && !empty($item['frequencia'])) {
                    $processed['tasks'][] = [
                        'key' => $key,
                        'frequency' => $item['frequencia'],
                        'moment' => $item['momento'] ?? '',
                        'pattern' => $item['padrao'] ?? '',
                        'benefit' => $item['beneficio'] ?? '',
                        'tolerance' => $item['tolerancia'] ?? '',
                        'signs' => $item['sinais'] ?? '',
                        'importance' => $item['importancia'] ?? ''
                    ];
                }
            }
        }

        // Manter compatibilidade com general_care antiga
        if (empty($processed['cuidados_gerais']) && !empty($maintenance['general_care'])) {
            $processed['general_care'] = $maintenance['general_care'];
        }

        return $processed;
    }

    /**
     * Processa procedimento de verificação
     */
    private function processVerificationProcedure(array $procedure): array
    {
        $processed = [];

        foreach ($procedure as $key => $step) {
            if (is_array($step) && !empty($step['titulo'])) {
                $processed[] = [
                    'key' => $key,
                    'title' => $step['titulo'],
                    'steps' => $step['passos'] ?? [],
                    'pressures' => $step['pressoes_recomendadas'] ?? [],
                    'tolerance' => $step['tolerancia'] ?? '',
                    'verify' => $step['verificar'] ?? [],
                    'procedure' => $step['procedimento'] ?? []
                ];
            }
        }

        return $processed;
    }

    /**
     * Processa dados específicos do veículo
     */
    private function processVehicleData(array $vehicleData): array
    {
        return [
            'vehicle_name' => $vehicleData['vehicle_name'] ?? '',
            'vehicle_brand' => $vehicleData['vehicle_brand'] ?? '',
            'vehicle_model' => $vehicleData['vehicle_model'] ?? '',
            'vehicle_year' => $vehicleData['vehicle_year'] ?? '',
            'vehicle_category' => $vehicleData['vehicle_category'] ?? '',
            'vehicle_type' => $vehicleData['vehicle_type'] ?? '',
            'tire_size' => $vehicleData['tire_size'] ?? '',
            'pressures' => $vehicleData['pressures'] ?? [],
            'pressure_display' => $vehicleData['pressure_display'] ?? '',
            'pressure_loaded_display' => $vehicleData['pressure_loaded_display'] ?? '',
            'recommended_oil' => $vehicleData['recommended_oil'] ?? '',
            'is_motorcycle' => $vehicleData['is_motorcycle'] ?? false,
            'is_electric' => $vehicleData['is_electric'] ?? false,
            'is_hybrid' => $vehicleData['is_hybrid'] ?? false,
            'image_url' => $vehicleData['image_url'] ?? '',
            'slug' => $vehicleData['slug'] ?? '',
            'canonical_url' => $vehicleData['canonical_url'] ?? ''
        ];
    }

    /**
     * Constrói os passos do HowTo para schema
     */
    private function buildHowToSteps(): array
    {
        $steps = [];
        $stepNumber = 1;

        // Passo 1: Verificação visual
        $steps[] = [
            '@type' => 'HowToStep',
            'position' => $stepNumber++,
            'name' => 'Verificação Visual dos Pneus',
            'text' => 'Examine visualmente os pneus em busca de desgaste irregular, cortes, bolhas ou objetos encravados',
            'url' => $this->processedData['canonical_url'] ?? '#verificacao-visual'
        ];

        // Passo 2: Medição de profundidade
        $steps[] = [
            '@type' => 'HowToStep',
            'position' => $stepNumber++,
            'name' => 'Medição da Profundidade dos Sulcos',
            'text' => 'Use uma moeda para verificar se a profundidade dos sulcos está acima do limite legal de 1,6mm',
            'url' => $this->processedData['canonical_url'] ?? '#medicao-profundidade'
        ];

        // Passo 3: Verificação de pressão
        $steps[] = [
            '@type' => 'HowToStep',
            'position' => $stepNumber++,
            'name' => 'Verificação da Pressão',
            'text' => 'Verifique a pressão dos pneus com calibrador, sempre com pneus frios',
            'url' => $this->processedData['canonical_url'] ?? '#verificacao-pressao'
        ];

        // Passo 4: Avaliação geral
        $steps[] = [
            '@type' => 'HowToStep',
            'position' => $stepNumber++,
            'name' => 'Avaliação da Necessidade de Troca',
            'text' => 'Com base nos sinais identificados, determine se é necessário trocar os pneus',
            'url' => $this->processedData['canonical_url'] ?? '#avaliacao-troca'
        ];

        return $steps;
    }

    /**
     * Obtém classe CSS baseada na severidade
     */
    private function getSeverityClass(string $severity): string
    {
        return match ($severity) {
            'alta', 'critica' => 'severity-high',
            'media' => 'severity-medium',
            'baixa' => 'severity-low',
            default => 'severity-medium'
        };
    }

    /**
     * Obtém classe CSS baseada na importância
     */
    private function getImportanceClass(string $importance): string
    {
        return match ($importance) {
            'alta', 'essencial', 'obrigatória', 'crítica' => 'importance-high',
            'media', 'recomendada' => 'importance-medium',
            'baixa' => 'importance-low',
            default => 'importance-medium'
        };
    }

    /**
     * Retorna o nome completo do veículo
     * MÉTODO CORRIGIDO: Usa extracted_entities ao invés de vehicle_info
     */
    private function getVehicleFullName(): string
    {
        // CORREÇÃO: Usar extracted_entities ao invés de vehicle_info
        $vehicleInfo = $this->article->extracted_entities ?? [];

        // Validação básica - retorna vazio se não tiver marca ou modelo
        if (empty($vehicleInfo['marca']) || empty($vehicleInfo['modelo'])) {
            return '';
        }

        return sprintf(
            '%s %s %s',
            $vehicleInfo['marca'] ?? '',
            $vehicleInfo['modelo'] ?? '',
            $vehicleInfo['ano'] ?? ''
        );
    }

    /**
     * Obtém dados específicos para breadcrumbs
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'Info Center', 'url' => '/info'],
            ['title' => 'Quando Trocar Pneus', 'url' => route('info.category.show', 'quando-trocar-pneus')],
            ['title' => $this->getVehicleFullName(), 'url' => '']
        ];
    }

    /**
     * Obtém dados para meta tags específicas do template
     */
    public function getMetaTags(): array
    {
        $vehicleFullName = $this->getVehicleFullName();
        $pressureDisplay = $this->processedData['vehicle_data']['pressure_display'] ?? '';

        return [
            'title' => "Quando Trocar os Pneus do {$vehicleFullName} - Guia Completo",
            'description' => "Guia completo sobre quando trocar os pneus do {$vehicleFullName}. Sinais de desgaste, pressões recomendadas ({$pressureDisplay}), cronograma de verificação e dicas de manutenção.",
            'keywords' => implode(', ', [
                "quando trocar pneus {$vehicleFullName}",
                "sinais desgaste pneus",
                "pressão pneus",
                "manutenção pneus",
                "cronograma verificação pneus"
            ])
        ];
    }
}
