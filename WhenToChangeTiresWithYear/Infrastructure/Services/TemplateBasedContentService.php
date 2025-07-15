<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects\VehicleData;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\ValueObjects\TireChangeContent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemplateBasedContentService
{
    /**
     * Templates de introdução com variação
     */
    /**
     * Templates de introdução com variação (CORRIGIDO - COM YEAR)
     */
    private array $introductionTemplates = [
        'default' => [
            "Identificar o momento certo para trocar os pneus do seu {make} {model} {year} é essencial para garantir segurança, desempenho e economia. Este {category_text} precisa de pneus em perfeitas condições para máxima segurança e economia. Os pneus são o único ponto de contato com o solo e influenciam diretamente a frenagem, estabilidade e consumo de combustível. Este guia apresenta os sinais de desgaste, prazos recomendados, cronograma de verificação e dicas práticas para que você saiba exatamente quando substituir os pneus do seu veículo.",

            "Saber quando trocar os pneus do {make} {model} {year} é fundamental para manter a segurança e o desempenho do seu {category}. Pneus desgastados não apenas comprometem a dirigibilidade, mas também aumentam o consumo de combustível e o risco de acidentes. Neste guia completo, você aprenderá a identificar os principais sinais de que os pneus precisam ser substituídos, conhecerá os fatores que afetam sua durabilidade e descobrirá como criar um cronograma eficiente de verificação e manutenção.",

            "Os pneus do seu {make} {model} {year} são componentes críticos que demandam atenção constante. Como único elemento de contato entre o veículo e o pavimento, eles influenciam diretamente a segurança, economia e conforto de condução. Este manual técnico apresenta critérios objetivos para avaliar quando é necessário substituir os pneus, considerando as especificidades do seu {category} e fornecendo um cronograma prático de verificações preventivas."
        ],
        'motorcycle' => [
            "A segurança em motocicletas depende criticamente do estado dos pneus. Na sua {make} {model} {year}, essa responsabilidade é ainda maior, pois os pneus são fundamentais para estabilidade, frenagem e manobrabilidade. Este guia especializado apresenta os sinais específicos de desgaste em motocicletas, fatores únicos que afetam a durabilidade dos pneus de duas rodas e um cronograma de verificação adaptado às necessidades dos motociclistas.",

            "Para motociclistas, conhecer o estado dos pneus é questão de vida ou morte. Sua {make} {model} {year} exige pneus em condições perfeitas para garantir estabilidade em curvas, frenagem eficiente e aderência em diferentes condições de piso. Este manual técnico específico para motocicletas apresenta critérios detalhados para avaliação do desgaste, sinais críticos de substituição e procedimentos de verificação adequados para duas rodas."
        ]
    ];

    /**
     * Templates de conclusão
     */
    private array $conclusionTemplates = [
        'default' => [
            "Manter os pneus do seu {make} {model} em perfeitas condições é investir em segurança, economia e desempenho. A verificação regular das pressões ({pressure_display}), o acompanhamento do desgaste e a troca no momento adequado são práticas essenciais para qualquer proprietário responsável. Lembre-se: pneus em bom estado não apenas protegem vidas, mas também proporcionam melhor experiência de condução, economia de combustível e menor impacto ambiental. Invista na manutenção preventiva e desfrute de um veículo sempre seguro e eficiente.",

            "A manutenção adequada dos pneus do {make} {model} vai além da simples verificação visual. Seguir o cronograma de inspeções, manter as pressões corretas ({pressure_display}) e estar atento aos sinais de desgaste são investimentos que se pagam em segurança e economia. Pneus bem conservados reduzem o consumo de combustível, proporcionam melhor aderência e aumentam a vida útil de outros componentes da suspensão. Faça da verificação dos pneus um hábito regular e mantenha seu {category} sempre em condições ideais de uso.",

            "O investimento em pneus de qualidade e sua manutenção adequada reflete diretamente na segurança e economia do seu {make} {model}. Estabelecer uma rotina de verificações, respeitar as pressões recomendadas ({pressure_display}) e trocar os pneus no momento certo são atitudes que demonstram responsabilidade e cuidado com o patrimônio. Além dos benefícios imediatos de segurança, essa prática contribui para um trânsito mais seguro para todos e para a preservação do meio ambiente através da redução do consumo de combustível."
        ],
        'motorcycle' => [
            "Para motociclistas, a manutenção dos pneus é ainda mais crítica que em automóveis. Sua {make} {model} depende integralmente da condição dos pneus para estabilidade e segurança. Verificações frequentes das pressões ({pressure_display}), inspeção regular do desgaste e substituição no momento adequado são práticas que podem salvar vidas. Pneus em perfeitas condições não apenas garantem sua segurança, mas também proporcionam maior prazer de pilotagem e economia operacional. Nunca comprometa sua segurança: mantenha os pneus sempre em estado perfeito.",

            "Em motocicletas como a {make} {model}, os pneus são literalmente a diferença entre a segurança e o perigo. A responsabilidade de manter pressões adequadas ({pressure_display}), verificar desgastes e substituir no momento certo recai inteiramente sobre o piloto. Essa atenção constante aos pneus não é apenas manutenção preventiva, é um investimento na sua própria vida e na dos demais usuários da via. Desenvolva o hábito de verificar os pneus antes de cada viagem e nunca ignore sinais de desgaste ou danos."
        ]
    ];

    /**
     * Gerar conteúdo JSON estruturado para artigo de quando trocar pneus
     */
    public function generateTireChangeArticle(VehicleData $vehicle): TireChangeContent
    {
        Log::info("Gerando artigo estruturado para: {$vehicle->getVehicleIdentifier()}");

        // 1. Dados básicos do artigo
        $title = $this->generateTitle($vehicle);
        $slug = $this->generateSlug($vehicle);
        $template = 'when_to_change_tires';

        // 2. Gerar conteúdo estruturado (compatível com template)
        $content = $this->generateContentStructure($vehicle);

        // 3. Adicionar dados específicos do template
        $content['vehicle_data'] = $this->generateVehicleTemplateData($vehicle);

        // 4. Entidades extraídas (REMOVIDO óleo desnecessário)
        $extractedEntities = $this->generateExtractedEntities($vehicle);

        // 5. Dados SEO (compatível com template)
        $seoData = $this->generateSeoData($vehicle, $title);

        // 6. Metadados (compatível com template)
        $metadata = $this->generateMetadata($vehicle);

        // 7. Tags
        $tags = $this->generateTags($vehicle);

        // 8. Tópicos relacionados
        $relatedTopics = $this->generateRelatedTopics($vehicle);

        // 9. Informações do veículo
        $vehicleInfo = $this->generateVehicleInfo($vehicle);

        // 10. Dados de filtro
        $filterData = $this->generateFilterData($vehicle);

        return new TireChangeContent(
            title: $title,
            slug: $slug,
            template: $template,
            content: $content,
            extractedEntities: $extractedEntities,
            seoData: $seoData,
            metadata: $metadata,
            tags: $tags,
            relatedTopics: $relatedTopics,
            vehicleInfo: $vehicleInfo,
            filterData: $filterData
        );
    }

    /**
     * Gerar título do artigo com variação (CORRIGIDO - SEMPRE COM YEAR)
     */
    protected function generateTitle(VehicleData $vehicle): string
    {
        $templates = [
            "Quando Trocar os Pneus do {make} {model} {year} - Guia Completo",
            "Pneus do {make} {model} {year}: Sinais e Momento da Troca",
            "Troca de Pneus {make} {model} {year}: Manual Técnico",
            "{make} {model} {year}: Quando Substituir os Pneus",
            "Guia de Manutenção: Pneus do {make} {model} {year}"
        ];

        $template = $templates[array_rand($templates)];
        return $this->replacePlaceholders($template, $vehicle);
    }

    /**
     * Gerar slug
     */
    protected function generateSlug(VehicleData $vehicle): string
    {
        return Str::slug("quando-trocar-pneus-{$vehicle->make}-{$vehicle->model}-{$vehicle->year}");
    }

    /**
     * Gerar estrutura de conteúdo completa compatível com Template_Quando_Trocar_os_Pneus.blade.php
     */
    protected function generateContentStructure(VehicleData $vehicle): array
    {
        return [
            'introducao' => $this->generateIntroduction($vehicle),
            'sintomas_desgaste' => $this->generateWearSymptoms($vehicle),
            'fatores_durabilidade' => $this->generateDurabilityFactors($vehicle),
            'cronograma_verificacao' => $this->generateInspectionSchedule($vehicle),
            'tipos_pneus' => $this->generateTireTypes($vehicle),
            'sinais_criticos' => $this->generateCriticalSigns($vehicle),
            'manutencao_preventiva' => $this->generatePreventiveMaintenance($vehicle),
            'procedimento_verificacao' => $this->generateInspectionProcedure($vehicle),
            'perguntas_frequentes' => $this->generateFAQ($vehicle),
            'consideracoes_finais' => $this->generateFinalConsiderations($vehicle)
        ];
    }

    /**
     * Gerar introdução com variação (MELHORADO)
     */
    protected function generateIntroduction(VehicleData $vehicle): string
    {
        $category = $vehicle->isMotorcycle() ? 'motorcycle' : 'default';
        $templates = $this->introductionTemplates[$category];
        $template = $templates[array_rand($templates)];

        // Adicionar texto específico da categoria
        $categoryText = $this->getCategoryDescription($vehicle);

        $replacements = [
            '{make}' => $vehicle->make,
            '{model}' => $vehicle->model,
            '{category}' => $vehicle->getMainCategory(),
            '{category_text}' => $categoryText
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Obter descrição específica da categoria
     */
    private function getCategoryDescription(VehicleData $vehicle): string
    {
        if ($vehicle->isMotorcycle()) {
            return match ($vehicle->getMainCategory()) {
                'adventure' => 'adventure versátil que enfrenta diferentes terrenos',
                'sport' => 'esportiva que exige máxima aderência e performance',
                'touring' => 'touring confortável para longas distâncias',
                'naked' => 'naked urbana para uso diário',
                default => 'motocicleta que demanda cuidados especiais'
            };
        }

        return match ($vehicle->getMainCategory()) {
            'suv' => 'SUV versátil que demanda atenção especial com os pneus devido às diferentes condições de uso',
            'sedan' => 'sedan que combina conforto e economia, características que dependem diretamente da condição dos pneus',
            'hatchback' => 'hatchback urbano que precisa de pneus em perfeitas condições para máxima segurança e economia',
            'pickup' => 'pickup robusta que exige pneus adequados tanto para trabalho quanto para uso familiar',
            'crossover' => 'crossover moderno que une versatilidade e eficiência',
            default => 'veículo que merece cuidados adequados com os pneus'
        };
    }

    /**
     * Gerar sintomas de desgaste específicos (MELHORADO)
     */
    protected function generateWearSymptoms(VehicleData $vehicle): array
    {
        $baseSymptoms = [
            'vibracao_direcao' => [
                'titulo' => $vehicle->isMotorcycle() ? 'Vibração no Guidão' : 'Vibração na Direção',
                'descricao' => $vehicle->isMotorcycle()
                    ? 'Guidão vibra ou tremula, especialmente em velocidades mais altas ou durante frenagem'
                    : 'Volante tremula ou vibra, especialmente em velocidades mais altas',
                'severidade' => 'alta',
                'acao' => 'Verificar balanceamento e possível desgaste irregular'
            ],
            'ruido_rolamento' => [
                'titulo' => 'Ruído Excessivo de Rolamento',
                'descricao' => 'Som alto ou zunido constante vindo dos pneus durante a condução',
                'severidade' => 'media',
                'acao' => 'Inspecionar padrão de desgaste e estado dos pneus'
            ],
            'reducao_aderencia' => [
                'titulo' => 'Redução da Aderência',
                'descricao' => $vehicle->isMotorcycle()
                    ? 'Perda de tração em curvas ou menor aderência em piso molhado, comprometendo a estabilidade'
                    : 'Deslizamento em curvas ou menor tração em piso molhado',
                'severidade' => 'alta',
                'acao' => 'Substituição imediata recomendada'
            ],
            'aumento_consumo' => [
                'titulo' => 'Aumento do Consumo de Combustível',
                'descricao' => 'Maior gasto de combustível devido à resistência de rolamento aumentada',
                'severidade' => 'media',
                'acao' => 'Verificar pressão e condição dos pneus'
            ]
        ];

        // Sintomas específicos para motocicletas
        if ($vehicle->isMotorcycle()) {
            $baseSymptoms['instabilidade_pilotagem'] = [
                'titulo' => 'Instabilidade na Pilotagem',
                'descricao' => 'Dificuldade para manter a motocicleta estável em linha reta ou curvas',
                'severidade' => 'critica',
                'acao' => 'Parar uso imediatamente e verificar pneus'
            ];

            $baseSymptoms['formato_quadrado'] = [
                'titulo' => 'Formato Quadrado no Pneu Traseiro',
                'descricao' => 'Pneu traseiro desenvolve formato quadrado por uso urbano excessivo',
                'severidade' => 'media',
                'acao' => 'Considerar troca e variar estilo de pilotagem'
            ];
        }

        return $baseSymptoms;
    }

    /**
     * Gerar fatores que afetam durabilidade (MANTIDO mas melhorado)
     */
    protected function generateDurabilityFactors(VehicleData $vehicle): array
    {
        return [
            'calibragem_inadequada' => [
                'titulo' => 'Calibragem Inadequada',
                'impacto_negativo' => '-30%',
                'descricao' => 'Pressão incorreta causa desgaste prematuro e irregular',
                'pressao_recomendada' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI para o {$vehicle->make} {$vehicle->model}",
                'recomendacao' => $vehicle->isMotorcycle() ? 'Verificar pressão semanalmente' : 'Verificar pressão mensalmente'
            ],
            'conducao_agressiva' => [
                'titulo' => $vehicle->isMotorcycle() ? 'Pilotagem Agressiva' : 'Condução Agressiva',
                'impacto_negativo' => '-40%',
                'descricao' => $vehicle->isMotorcycle()
                    ? 'Acelerações bruscas, frenagens severas e inclinações excessivas reduzem drasticamente a vida útil'
                    : 'Acelerações e frenagens bruscas reduzem drasticamente a vida útil',
                'recomendacao' => $vehicle->isMotorcycle()
                    ? 'Mantenha pilotagem suave e progressiva'
                    : 'Mantenha condução suave e progressiva'
            ],
            'condicoes_adversas' => [
                'titulo' => 'Condições Adversas das Vias',
                'impacto_negativo' => '-30%',
                'descricao' => 'Vias não pavimentadas, com buracos ou irregularidades severas',
                'dica' => 'Evite obstáculos e reduza velocidade em vias ruins'
            ],
            'manutencao_adequada' => [
                'titulo' => 'Manutenção Adequada',
                'impacto_positivo' => '+20%',
                'descricao' => $vehicle->isMotorcycle()
                    ? 'Calibragem regular, verificação de desgaste, limpeza adequada'
                    : 'Calibragem regular, rodízio a cada 10.000 km, alinhamento adequado',
                'beneficio' => 'Aumenta significativamente a vida útil dos pneus'
            ]
        ];
    }

    /**
     * Gerar cronograma de verificação (MELHORADO)
     */
    protected function generateInspectionSchedule(VehicleData $vehicle): array
    {
        if ($vehicle->isMotorcycle()) {
            return [
                'semanal' => [
                    'titulo' => 'Verificação Semanal',
                    'descricao' => 'Pressão dos pneus e inspeção visual básica antes de longas saídas',
                    'importancia' => 'crítica'
                ],
                'quinzenal' => [
                    'titulo' => 'Inspeção Quinzenal',
                    'descricao' => 'Verificação detalhada de desgaste, rachaduras e objetos presos',
                    'importancia' => 'alta'
                ],
                'revisao' => [
                    'titulo' => 'A cada revisão (5.000 km)',
                    'descricao' => "Avaliação profissional da condição dos pneus durante a revisão programada da {$vehicle->make} {$vehicle->model}",
                    'importancia' => 'essencial'
                ],
                'antes_viagens' => [
                    'titulo' => 'Antes de Viagens Longas',
                    'descricao' => 'Verificação completa incluindo verificação de kit de reparo',
                    'importancia' => 'obrigatória'
                ]
            ];
        }

        return [
            'quinzenal' => [
                'titulo' => 'Verificação Quinzenal',
                'descricao' => 'Pressão dos pneus e inspeção visual para detectar problemas iniciais',
                'importancia' => 'alta'
            ],
            'revisao' => [
                'titulo' => 'A cada revisão (10.000 km)',
                'descricao' => "Verificação da profundidade dos sulcos e condição geral dos pneus durante a revisão programada do {$vehicle->make} {$vehicle->model}",
                'importancia' => 'essencial'
            ],
            'semestral' => [
                'titulo' => 'A cada 6 meses',
                'descricao' => 'Inspeção visual completa, verificando desgastes irregulares e danos na estrutura',
                'importancia' => 'recomendada'
            ],
            'antes_viagens' => [
                'titulo' => 'Antes de Viagens Longas',
                'descricao' => 'Verificação completa incluindo pneu sobressalente e kit de ferramentas',
                'importancia' => 'obrigatória'
            ]
        ];
    }

    /**
     * Gerar tipos de pneus com quilometragem esperada (MELHORADO)
     */
    protected function generateTireTypes(VehicleData $vehicle): array
    {
        if ($vehicle->isMotorcycle()) {
            $types = [
                'original_oem' => [
                    'tipo' => 'Original (OEM)',
                    'quilometragem_esperada' => '15.000 - 25.000 km',
                    'aplicacao' => 'Todos os modelos de fábrica',
                    'observacoes' => 'Balanceiam aderência, durabilidade e custo'
                ],
                'esportivo' => [
                    'tipo' => 'Esportivo',
                    'quilometragem_esperada' => '8.000 - 15.000 km',
                    'aplicacao' => 'Uso esportivo e track days',
                    'observacoes' => 'Máxima aderência, menor durabilidade'
                ],
                'touring' => [
                    'tipo' => 'Touring',
                    'quilometragem_esperada' => '20.000 - 30.000 km',
                    'aplicacao' => 'Viagens longas e uso rodoviário',
                    'observacoes' => 'Maior durabilidade e conforto'
                ]
            ];

            // Adicionar Trail para Adventure
            if (
                str_contains(strtolower($vehicle->getMainCategory()), 'adventure') ||
                str_contains(strtolower($vehicle->model), 'adventure')
            ) {
                $types['trail'] = [
                    'tipo' => 'Trail/Adventure',
                    'quilometragem_esperada' => '12.000 - 20.000 km',
                    'aplicacao' => 'Uso misto on/off road',
                    'observacoes' => 'Versatilidade para diferentes terrenos'
                ];
            }

            return $types;
        }

        $carTypes = [
            'original_oem' => [
                'tipo' => 'Original (OEM)',
                'quilometragem_esperada' => '50.000 - 60.000 km',
                'aplicacao' => 'Todos os modelos de fábrica',
                'observacoes' => 'Balanceiam conforto, durabilidade e desempenho'
            ],
            'premium_touring' => [
                'tipo' => 'Premium Touring',
                'quilometragem_esperada' => '60.000 - 80.000 km',
                'aplicacao' => 'Versões topo de linha e híbridas',
                'observacoes' => 'Maior conforto e durabilidade, custo elevado'
            ],
            'performance' => [
                'tipo' => 'Performance',
                'quilometragem_esperada' => '30.000 - 40.000 km',
                'aplicacao' => 'Customização/Upgrade esportivo',
                'observacoes' => 'Maior aderência, menor vida útil'
            ]
        ];

        // Adicionar All-Terrain para SUVs e Pickups
        if (in_array($vehicle->getMainCategory(), ['suv', 'pickup', 'crossover'])) {
            $carTypes['all_terrain'] = [
                'tipo' => 'All-Terrain',
                'quilometragem_esperada' => '40.000 - 50.000 km',
                'aplicacao' => 'Modelos Adventure/Off-road',
                'observacoes' => 'Maior tração off-road, menor durabilidade no asfalto'
            ];
        }

        return $carTypes;
    }

    /**
     * Gerar sinais críticos para substituição imediata (MELHORADO)
     */
    protected function generateCriticalSigns(VehicleData $vehicle): array
    {
        $criticalSigns = [
            'profundidade_sulco' => [
                'titulo' => 'Profundidade dos Sulcos',
                'limite_legal' => $vehicle->isMotorcycle() ? '1,0mm (dianteiro) / 1,6mm (traseiro)' : '1,6mm',
                'limite_recomendado' => $vehicle->isMotorcycle() ? '2,0mm (dianteiro) / 3,0mm (traseiro)' : '3,0mm',
                'teste' => 'Use moeda para verificar profundidade',
                'acao' => 'Substituição obrigatória ao atingir limite legal'
            ],
            'danos_estruturais' => [
                'titulo' => 'Danos Estruturais',
                'tipos' => [
                    'Bolhas ou deformações na lateral',
                    'Cortes profundos na banda de rodagem',
                    'Rachaduras visíveis na borracha',
                    'Cordas aparentes'
                ],
                'acao' => 'Substituição imediata, não rode com estes danos'
            ],
            'desgaste_irregular' => [
                'titulo' => 'Desgaste Irregular',
                'padroes' => [
                    'Bordas mais desgastadas (pressão baixa)',
                    'Centro mais desgastado (pressão alta)',
                    'Um lado mais desgastado (desalinhamento)',
                    'Desgaste ondulado (suspensão)'
                ],
                'acao' => 'Corrigir causa e considerar substituição'
            ]
        ];

        if ($vehicle->isMotorcycle()) {
            $criticalSigns['sinais_especificos_moto'] = [
                'titulo' => 'Sinais Específicos de Motocicletas',
                'tipos' => [
                    'Formato quadrado no pneu traseiro',
                    'Perda de aderência em curvas',
                    'Instabilidade em linha reta',
                    'Dificuldade para inclinar nas curvas'
                ],
                'acao' => 'Atenção redobrada - segurança crítica'
            ];
        }

        return $criticalSigns;
    }

    /**
     * Gerar manutenção preventiva (AJUSTADO)
     */
    protected function generatePreventiveMaintenance(VehicleData $vehicle): array
    {
        $maintenance = [
            'verificacao_pressao' => [
                'frequencia' => $vehicle->isMotorcycle() ? 'Semanalmente ou antes de cada saída' : 'Mensalmente',
                'momento' => 'Sempre com pneus frios',
                'tolerancia' => '±2 PSI da pressão recomendada'
            ],
            'rodizio' => [
                'frequencia' => $vehicle->isMotorcycle() ? 'Não aplicável' : 'A cada 10.000 km',
                'padrao' => $vehicle->isMotorcycle() ? 'Troque dianteiro e traseiro separadamente' : 'Siga padrão cruzado ou paralelo',
                'beneficio' => 'Garante desgaste uniforme e maior durabilidade'
            ],
            'alinhamento_balanceamento' => [
                'frequencia' => 'A cada 20.000 km ou quando necessário',
                'sinais' => 'Desgaste irregular, vibração, veículo puxando para um lado',
                'importancia' => 'Evita desgaste prematuro e melhora dirigibilidade'
            ],
            'cuidados_gerais' => [
                'Evite freadas e acelerações bruscas',
                'Respeite limites de velocidade',
                'Evite obstáculos e buracos',
                'Mantenha suspensão em bom estado',
                'Proteja da exposição solar excessiva'
            ]
        ];

        if ($vehicle->isMotorcycle()) {
            $maintenance['cuidados_gerais'][] = 'Evite derrapagens e wheeling';
            $maintenance['cuidados_gerais'][] = 'Cuidado especial em piso molhado';
            $maintenance['cuidados_gerais'][] = 'Limpe os pneus após uso off-road';
        }

        return $maintenance;
    }

    /**
     * Gerar procedimento de verificação detalhado (MANTIDO)
     */
    protected function generateInspectionProcedure(VehicleData $vehicle): array
    {
        return [
            'preparacao' => [
                'titulo' => 'Preparação para Inspeção',
                'passos' => [
                    'Estacione em local plano e bem iluminado',
                    'Aguarde pneus esfriarem (pelo menos 3 horas parado)',
                    'Tenha em mãos: calibrador, moeda, lanterna'
                ]
            ],
            'verificacao_pressao' => [
                'titulo' => 'Verificação da Pressão',
                'pressoes_recomendadas' => [
                    'vazio' => "{$vehicle->pressureEmptyFront} PSI (dianteiro) / {$vehicle->pressureEmptyRear} PSI (traseiro)",
                    'com_carga' => "{$vehicle->pressureLightFront} PSI (dianteiro) / {$vehicle->pressureLightRear} PSI (traseiro)"
                ],
                'tolerancia' => '±2 PSI da pressão recomendada'
            ],
            'inspecao_visual' => [
                'titulo' => 'Inspeção Visual',
                'verificar' => [
                    'Profundidade dos sulcos (usar moeda)',
                    'Desgaste uniforme em toda superfície',
                    'Ausência de cortes, bolhas ou rachaduras',
                    'Objetos presos entre sulcos',
                    'Estado da válvula e tampa'
                ]
            ],
            'teste_funcional' => [
                'titulo' => 'Teste Funcional',
                'procedimento' => [
                    $vehicle->isMotorcycle()
                        ? 'Pilote em baixa velocidade prestando atenção a vibrações'
                        : 'Dirigir em baixa velocidade prestando atenção a vibrações',
                    'Verificar se veículo puxa para algum lado',
                    'Observar ruídos anormais durante rolamento',
                    'Testar frenagem em local seguro'
                ]
            ]
        ];
    }

    /**
     * Gerar FAQ (MELHORADO - SEM ÓLEO)
     */
    protected function generateFAQ(VehicleData $vehicle): array
    {
        $faq = [
            [
                'pergunta' => "Posso usar medida diferente no {$vehicle->make} {$vehicle->model}?",
                'resposta' => "Não é recomendado. Use sempre a medida original {$vehicle->tireSize} para manter as características de segurança, economia e desempenho especificadas pelo fabricante."
            ],
            [
                'pergunta' => "Com que frequência devo verificar a pressão?",
                'resposta' => $vehicle->isMotorcycle()
                    ? "Em motocicletas, verifique semanalmente ou antes de cada saída. Use as pressões recomendadas: {$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI."
                    : "Verifique mensalmente e antes de viagens. Para o {$vehicle->make} {$vehicle->model}, mantenha {$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI."
            ],
            [
                'pergunta' => "É seguro trocar apenas um pneu?",
                'resposta' => $vehicle->isMotorcycle()
                    ? "Em motocicletas, é altamente recomendado trocar sempre em pares ou individualmente conforme desgaste. Mantenha sempre pneus da mesma marca e modelo para garantir comportamento uniforme."
                    : "Idealmente, troque sempre em pares (dianteiros ou traseiros) para manter o equilíbrio do veículo. Em emergências, pode trocar apenas um, mas substitua o par o quanto antes."
            ]
        ];

        if ($vehicle->isMotorcycle()) {
            $faq[] = [
                'pergunta' => "Posso usar pneus de carro na motocicleta?",
                'resposta' => "Jamais! Motocicletas exigem pneus específicos com construção, compostos e desenhos adequados às características de duas rodas."
            ];

            $faq[] = [
                'pergunta' => "Como evitar o formato quadrado no pneu traseiro?",
                'resposta' => "Varie o estilo de pilotagem, evite apenas uso urbano, faça curvas ocasionalmente e mantenha a pressão correta. O formato quadrado é comum em uso urbano excessivo."
            ];
        } else {
            $faq[] = [
                'pergunta' => "Quando fazer o rodízio dos pneus?",
                'resposta' => "Faça o rodízio a cada 10.000 km seguindo o padrão cruzado (dianteiro direito vai para traseiro esquerdo e vice-versa) para garantir desgaste uniforme."
            ];
        }

        // REMOVIDO FAQ sobre óleo - isso vai para template específico de óleo

        return $faq;
    }

    /**
     * Gerar considerações finais com variação (MELHORADO)
     */
    protected function generateFinalConsiderations(VehicleData $vehicle): string
    {
        $category = $vehicle->isMotorcycle() ? 'motorcycle' : 'default';
        $templates = $this->conclusionTemplates[$category];
        $template = $templates[array_rand($templates)];

        $replacements = [
            '{make}' => $vehicle->make,
            '{model}' => $vehicle->model,
            '{category}' => $vehicle->getMainCategory(),
            '{pressure_display}' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI"
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Gerar entidades extraídas (REMOVIDO óleo desnecessário)
     */
    protected function generateExtractedEntities(VehicleData $vehicle): array
    {
        return [
            'marca' => $vehicle->make,
            'modelo' => $vehicle->model,
            'ano' => (string) $vehicle->year,
            'tipo_veiculo' => $vehicle->getVehicleType(),
            'categoria' => $vehicle->getMainCategory(),
            'medida_pneus' => $vehicle->tireSize,
            'pressao_recomendada' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI"
            // REMOVIDO: 'oleo_recomendado' - não faz parte do template de pneus
        ];
    }

    /**
     * Gerar dados SEO compatíveis com o template (MANTIDO)
     */
    protected function generateSeoData(VehicleData $vehicle, string $title): array
    {
        return [
            'page_title' => $title,
            'meta_description' => "Guia completo sobre quando trocar os pneus do {$vehicle->make} {$vehicle->model}. Sinais de desgaste, pressões recomendadas ({$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI), cronograma de verificação e dicas de manutenção.",
            'url_slug' => $this->generateSlug($vehicle),
            'canonical_url' => "https://mercadoveiculos.com/info/" . $this->generateSlug($vehicle),
            'h1' => "Quando Trocar os Pneus do {$vehicle->make} {$vehicle->model}",
            'h2_tags' => [
                'Sintomas de Pneus que Precisam de Substituição',
                'Fatores que Afetam a Durabilidade dos Pneus',
                'Cronograma de Verificação e Manutenção',
                'Tipos de Pneus e Quilometragem Esperada',
                'Sinais Críticos para Substituição Imediata',
                'Procedimento de Verificação dos Pneus',
                'Perguntas Frequentes',
                'Considerações Finais'
            ],
            'primary_keyword' => "quando trocar pneus {$vehicle->make} {$vehicle->model}",
            'secondary_keywords' => [
                "pneus {$vehicle->make} {$vehicle->model}",
                "sinais desgaste pneus {$vehicle->make}",
                "pressão pneus {$vehicle->model}",
                "manutenção pneus {$vehicle->make}",
                "troca pneus",
                "cronograma verificação pneus",
                "durabilidade pneus {$vehicle->getMainCategory()}"
            ],
            'related_topics' => [
                'manutenção preventiva',
                'economia combustível',
                'segurança veicular',
                'pressão pneus',
                'tipos de pneus',
                'desgaste irregular'
            ]
        ];
    }

    /**
     * Gerar metadados específicos para o template (MELHORADO)
     */
    protected function generateMetadata(VehicleData $vehicle): array
    {
        $wordCount = rand(1700, 2100); // Variação realista

        return [
            'original_clicks' => 0,
            'original_category' => 'Manutenção e Cuidados',
            'original_subcategory' => 'Pneus e Rodas',
            'word_count' => $wordCount,
            'reading_time' => max(1, (int) ceil($wordCount / 200)),
            'article_tone' => 'técnico-informativo',
            'published_date' => now()->format('Y-m-d'),
            'updated_date' => now()->format('d \d\e F \d\e Y'),
            'related_content' => $this->generateRelatedContentSuggestions($vehicle),
            'schema_type' => 'TechArticle',
            'vehicle_engine' => "{$vehicle->make} {$vehicle->model}",
            'category_schema' => 'Manutenção Automotiva',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'Info Center', 'url' => '/info'],
                ['title' => 'Quando Trocar Pneus', 'url' => '/info/quando-trocar-pneus'],
                ['title' => "{$vehicle->make} {$vehicle->model}", 'url' => '']
            ]
        ];
    }

    /**
     * Gerar tags (MELHORADO)
     */
    protected function generateTags(VehicleData $vehicle): array
    {
        $tags = [
            "quando trocar pneus {$vehicle->make} {$vehicle->model}",
            "pneus {$vehicle->make}",
            "manutenção pneus",
            "pressão pneus",
            "desgaste pneus",
            "troca pneus",
            $vehicle->make,
            $vehicle->model,
            $vehicle->getMainCategory(),
            $vehicle->tireSize
        ];

        if ($vehicle->isMotorcycle()) {
            $tags[] = 'motocicleta';
            $tags[] = 'pneus moto';
            $tags[] = 'segurança moto';
        } else {
            $tags[] = 'carro';
            $tags[] = 'automóvel';
            $tags[] = 'pneus carro';
        }

        return array_filter($tags);
    }

    /**
     * Gerar tópicos relacionados (CORRIGIDO - COM YEAR)
     */
    protected function generateRelatedTopics(VehicleData $vehicle): array
    {
        $related = [];

        if ($vehicle->recommendedOil) {
            $related[] = [
                'title' => "Óleo Recomendado para {$vehicle->make} {$vehicle->model} {$vehicle->year}",
                'slug' => "oleo-recomendado-para-" . Str::slug("{$vehicle->make}-{$vehicle->model}-{$vehicle->year}"),
                'icon' => 'oil-can'
            ];
        }

        $related[] = [
            'title' => "Manutenção Preventiva do {$vehicle->make} {$vehicle->model} {$vehicle->year}",
            'slug' => "manutencao-preventiva-" . Str::slug("{$vehicle->make}-{$vehicle->model}-{$vehicle->year}"),
            'icon' => 'wrench'
        ];

        $related[] = [
            'title' => "Pressão dos Pneus: Guia Completo",
            'slug' => "pressao-pneus-guia-completo",
            'icon' => 'gauge'
        ];

        return $related;
    }
    /**
     * Gerar informações do veículo (LIMPO)
     */
    protected function generateVehicleInfo(VehicleData $vehicle): array
    {
        return [
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => (string) $vehicle->year,
            'category' => $vehicle->getMainCategory(),
            'vehicle_type' => $vehicle->getVehicleType(),
            'make_slug' => Str::slug($vehicle->make),
            'tire_size' => $vehicle->tireSize
            // REMOVIDO: recommended_oil (vai para template específico)
        ];
    }

    /**
     * Gerar dados de filtro (LIMPO)
     */
    protected function generateFilterData(VehicleData $vehicle): array
    {
        return [
            'marca' => $vehicle->make,
            'modelo' => $vehicle->model,
            'ano' => (string) $vehicle->year,
            'categoria' => $vehicle->getMainCategory(),
            'tipo_veiculo' => $vehicle->getVehicleType(),
            'marca_slug' => Str::slug($vehicle->make),
            'medida_pneus' => $vehicle->tireSize
        ];
    }

    /**
     * Adicionar dados específicos do veículo para o template (FOCO EM PNEUS)
     */
    protected function generateVehicleTemplateData(VehicleData $vehicle): array
    {
        return [
            // Dados principais do veículo
            'vehicle_name' => "{$vehicle->make} {$vehicle->model}",
            'vehicle_brand' => $vehicle->make,
            'vehicle_model' => $vehicle->model,
            'vehicle_year' => $vehicle->year,
            'vehicle_category' => $vehicle->getMainCategory(),
            'vehicle_type' => $vehicle->getVehicleType(),

            // Especificações técnicas DOS PNEUS
            'tire_size' => $vehicle->tireSize,
            'pressures' => [
                'empty_front' => $vehicle->pressureEmptyFront,
                'empty_rear' => $vehicle->pressureEmptyRear,
                'loaded_front' => $vehicle->pressureLightFront,
                'loaded_rear' => $vehicle->pressureLightRear,
                'max_front' => $vehicle->pressureMaxFront,
                'max_rear' => $vehicle->pressureMaxRear,
                'spare' => $vehicle->pressureSpare
            ],

            // Dados para formatação no template
            'pressure_display' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI",
            'pressure_loaded_display' => "{$vehicle->pressureLightFront}/{$vehicle->pressureLightRear} PSI",

            // Informações do tipo de veículo
            'is_motorcycle' => $vehicle->isMotorcycle(),
            'is_electric' => $vehicle->isElectric(),
            'is_hybrid' => $vehicle->isHybrid(),

            // Dados para URLs e imagens
            'image_url' => "https://mercadoveiculos.com/images/" . strtolower($vehicle->make) . "-" . strtolower($vehicle->model) . ".jpg",
            'slug' => $this->generateSlug($vehicle),
            'canonical_url' => "https://mercadoveiculos.com/info/" . $this->generateSlug($vehicle)

            // REMOVIDO: recommended_oil (não pertence ao template de pneus)
        ];
    }

    /**
     * Gerar sugestões de conteúdo relacionado (FOCO EM PNEUS)
     */
    protected function generateRelatedContentSuggestions(VehicleData $vehicle): array
    {
        $suggestions = [
            [
                'title' => "Pressão Correta dos Pneus: Guia Técnico",
                'slug' => "pressao-correta-pneus-guia-tecnico",
                'icon' => 'gauge'
            ],
            [
                'title' => "Rodízio de Pneus: Como e Quando Fazer",
                'slug' => "rodizio-pneus-como-quando-fazer",
                'icon' => 'rotate'
            ]
        ];

        // Adicionar específicos do veículo apenas se relevante
        if ($vehicle->recommendedOil) {
            $suggestions[] = [
                'title' => "Óleo Recomendado para {$vehicle->make} {$vehicle->model}: Guia Completo",
                'slug' => "oleo-recomendado-para-" . Str::slug("{$vehicle->make}-{$vehicle->model}"),
                'icon' => 'oil-can'
            ];
        }

        $suggestions[] = [
            'title' => "Manutenção Preventiva do {$vehicle->make} {$vehicle->model}: Checklist Completo",
            'slug' => "manutencao-preventiva-" . Str::slug("{$vehicle->make}-{$vehicle->model}"),
            'icon' => 'wrench'
        ];

        return $suggestions;
    }

    /**
     * Substituir placeholders nos templates (MANTIDO)
     */
    protected function replacePlaceholders(string $template, VehicleData $vehicle): string
    {
        $replacements = [
            '{make}' => $vehicle->make,
            '{model}' => $vehicle->model,
            '{year}' => $vehicle->year,  // ADICIONADO
            '{tire_size}' => $vehicle->tireSize,
            '{category}' => $vehicle->getMainCategory(),
            '{category_text}' => $vehicle->isMotorcycle() ? 'motocicleta' : 'veículo',
            '{pressure_display}' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI"
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
