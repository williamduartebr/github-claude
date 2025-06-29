<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Services;

use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\VehicleData;
use App\ContentGeneration\WhenToChangeTires\Domain\ValueObjects\TireChangeContent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemplateBasedContentServiceWithYear
{
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

        // 4. Entidades extraídas
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
     * Gerar título do artigo
     */
    protected function generateTitle(VehicleData $vehicle): string
    {
        $templates = [
            "Quando Trocar os Pneus do {make} {model} {year} - Guia Completo",
            "Pneus do {make} {model} {year}: Sinais e Momento da Troca",
            "Troca de Pneus {make} {model} {year}: Manual Técnico",
            "{make} {model} {year}: Quando Substituir os Pneus"
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
     * Gerar introdução compatível com o template
     */
    protected function generateIntroduction(VehicleData $vehicle): string
    {
        if ($vehicle->isMotorcycle()) {
            return "Identificar o momento certo para trocar os pneus da sua {$vehicle->make} {$vehicle->model} {$vehicle->year} é essencial para garantir segurança máxima na pilotagem. " .
                   "Em motocicletas, os pneus são responsáveis por 100% da estabilidade e aderência, tornando fundamental conhecer os sinais de desgaste e os intervalos recomendados. " .
                   "Este guia apresenta sintomas específicos, cronograma de verificação e dicas práticas para que você saiba exatamente quando substituir os pneus da sua motocicleta, " .
                   "garantindo performance e segurança em todas as condições de pilotagem.";
        }

        $categoryText = match($vehicle->getMainCategory()) {
            'suv' => 'SUV versátil que demanda atenção especial com os pneus devido às diferentes condições de uso',
            'sedan' => 'sedan que combina conforto e economia, características que dependem diretamente da condição dos pneus',
            'hatchback' => 'hatchback urbano que precisa de pneus em perfeitas condições para máxima segurança e economia',
            'pickup' => 'pickup robusta que exige pneus adequados tanto para trabalho quanto para uso familiar',
            default => 'veículo que merece cuidados adequados com os pneus'
        };

        return "Identificar o momento certo para trocar os pneus do seu {$vehicle->make} {$vehicle->model} {$vehicle->year} é essencial para garantir segurança, desempenho e economia. " .
               "Este {$categoryText}. " .
               "Os pneus são o único ponto de contato com o solo e influenciam diretamente a frenagem, estabilidade e consumo de combustível. " .
               "Este guia apresenta os sinais de desgaste, prazos recomendados, cronograma de verificação e dicas práticas para que você saiba exatamente quando substituir os pneus do seu veículo.";
    }

    /**
     * Gerar sintomas de desgaste (compatível com template)
     */
    protected function generateWearSymptoms(VehicleData $vehicle): array
    {
        $baseSymptoms = [
            'vibracao_direcao' => [
                'titulo' => 'Vibração na Direção',
                'descricao' => 'Volante tremula ou vibra, especialmente em velocidades mais altas',
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
                'descricao' => 'Deslizamento em curvas ou menor tração em piso molhado',
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

        if ($vehicle->isMotorcycle()) {
            $baseSymptoms['instabilidade_pilotagem'] = [
                'titulo' => 'Instabilidade na Pilotagem',
                'descricao' => 'Dificuldade para manter a motocicleta estável em linha reta ou curvas',
                'severidade' => 'critica',
                'acao' => 'Parar uso imediatamente e verificar pneus'
            ];
        }

        return $baseSymptoms;
    }

    /**
     * Gerar fatores que afetam durabilidade (compatível com template)
     */
    protected function generateDurabilityFactors(VehicleData $vehicle): array
    {
        return [
            'calibragem_inadequada' => [
                'titulo' => 'Calibragem Inadequada',
                'impacto_negativo' => '-30%',
                'descricao' => 'Pressão incorreta causa desgaste prematuro e irregular',
                'pressao_recomendada' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI para o {$vehicle->make} {$vehicle->model}"
            ],
            'conducao_agressiva' => [
                'titulo' => 'Condução Agressiva',
                'impacto_negativo' => '-40%',
                'descricao' => 'Acelerações e frenagens bruscas reduzem drasticamente a vida útil',
                'recomendacao' => 'Mantenha condução suave e progressiva'
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
                'descricao' => 'Calibragem regular, rodízio a cada 10.000 km, alinhamento adequado',
                'beneficio' => 'Aumenta significativamente a vida útil dos pneus'
            ]
        ];
    }

    /**
     * Gerar cronograma de verificação (compatível com template)
     */
    protected function generateInspectionSchedule(VehicleData $vehicle): array
    {
        if ($vehicle->isMotorcycle()) {
            return [
                'semanal' => [
                    'titulo' => 'Verificação Semanal',
                    'descricao' => 'Pressão dos pneus e inspeção visual básica antes de sair',
                    'importancia' => 'crítica'
                ],
                'mensal' => [
                    'titulo' => 'Inspeção Mensal',
                    'descricao' => 'Verificação detalhada de desgaste, rachaduras e objetos presos',
                    'importancia' => 'alta'
                ],
                'revisao' => [
                    'titulo' => 'A cada revisão (5.000 km)',
                    'descricao' => 'Avaliação profissional da condição dos pneus e necessidade de troca',
                    'importancia' => 'essencial'
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
     * Gerar tipos de pneus com quilometragem esperada (compatível com template)
     */
    protected function generateTireTypes(VehicleData $vehicle): array
    {
        if ($vehicle->isMotorcycle()) {
            return [
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
                ],
                'trail' => [
                    'tipo' => 'Trail/Adventure',
                    'quilometragem_esperada' => '12.000 - 20.000 km',
                    'aplicacao' => 'Uso misto on/off road',
                    'observacoes' => 'Versatilidade para diferentes terrenos'
                ]
            ];
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

        // Adicionar All-Terrain para SUVs
        if (in_array($vehicle->getMainCategory(), ['suv', 'pickup'])) {
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
     * Gerar sinais críticos para substituição imediata
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
     * Gerar manutenção preventiva
     */
    protected function generatePreventiveMaintenance(VehicleData $vehicle): array
    {
        return [
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
    }

    /**
     * Gerar procedimento de verificação detalhado
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
                    'Dirigir em baixa velocidade prestando atenção a vibrações',
                    'Verificar se veículo puxa para algum lado',
                    'Observar ruídos anormais durante rolamento',
                    'Testar frenagem em local seguro'
                ]
            ]
        ];
    }

    /**
     * Gerar FAQ
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
                'resposta' => "Idealmente, troque sempre em pares (dianteiros ou traseiros) para manter o equilíbrio do veículo. Em emergências, pode trocar apenas um, mas substitua o par o quanto antes."
            ]
        ];

        if ($vehicle->isMotorcycle()) {
            $faq[] = [
                'pergunta' => "Posso usar pneus de carro na motocicleta?",
                'resposta' => "Jamais! Motocicletas exigem pneus específicos com construção, compostos e desenhos adequados às características de duas rodas."
            ];
        }

        if ($vehicle->recommendedOil) {
            $faq[] = [
                'pergunta' => "Qual óleo usar no {$vehicle->make} {$vehicle->model}?",
                'resposta' => "Use {$vehicle->recommendedOil} conforme especificação do fabricante para manter a garantia e proteger adequadamente o motor."
            ];
        }

        return $faq;
    }

    /**
     * Gerar considerações finais
     */
    protected function generateFinalConsiderations(VehicleData $vehicle): string
    {
        $conclusion = "Manter os pneus do seu {$vehicle->make} {$vehicle->model} {$vehicle->year} em perfeitas condições é investir em segurança, economia e desempenho. ";
        $conclusion .= "A verificação regular das pressões ({$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI), ";
        $conclusion .= "o acompanhamento do desgaste e a troca no momento adequado são práticas essenciais para qualquer proprietário responsável. ";
        
        if ($vehicle->isMotorcycle()) {
            $conclusion .= "Em motocicletas, essa atenção é ainda mais crítica, pois os pneus são responsáveis por toda a estabilidade e segurança. ";
        }
        
        $conclusion .= "Lembre-se: pneus em bom estado não apenas protegem vidas, mas também proporcionam melhor experiência de condução, ";
        $conclusion .= "economia de combustível e menor impacto ambiental. Invista na manutenção preventiva e desfrute de um veículo sempre seguro e eficiente.";

        return $conclusion;
    }

    /**
     * Gerar entidades extraídas
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
            'pressao_recomendada' => "{$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI",
            'oleo_recomendado' => $vehicle->recommendedOil ?? 'Não aplicável'
        ];
    }

    /**
     * Gerar dados SEO compatíveis com o template
     */
    protected function generateSeoData(VehicleData $vehicle, string $title): array
    {
        return [
            'page_title' => $title,
            'meta_description' => "Guia completo sobre quando trocar os pneus do {$vehicle->make} {$vehicle->model} {$vehicle->year}. Sinais de desgaste, pressões recomendadas ({$vehicle->pressureEmptyFront}/{$vehicle->pressureEmptyRear} PSI), cronograma de verificação e dicas de manutenção.",
            'url_slug' => $this->generateSlug($vehicle),
            'canonical_url' => "https://mercadoveiculos.com/info/" . $this->generateSlug($vehicle),
            'h1' => "Quando Trocar os Pneus do {$vehicle->make} {$vehicle->model} {$vehicle->year}",
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
                "pneus {$vehicle->make} {$vehicle->model} {$vehicle->year}",
                "sinais desgaste pneus {$vehicle->make}",
                "pressão pneus {$vehicle->model}",
                "manutenção pneus {$vehicle->make}",
                "troca pneus {$vehicle->year}",
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
     * Gerar metadados específicos para o template
     */
    protected function generateMetadata(VehicleData $vehicle): array
    {
        $wordCount = 1800; // Estimativa realista baseada no template
        
        return [
            'original_clicks' => 0,
            'original_category' => 'Manutenção e Cuidados',
            'original_subcategory' => 'Pneus e Rodas',
            'word_count' => $wordCount,
            'reading_time' => max(1, (int) ceil($wordCount / 200)),
            'article_tone' => 'técnico-informativo',
            'published_date' => now()->format('Y-m-d'),
            'updated_date' => now()->format('d \d\e F \d\e Y'), // Formato brasileiro
            'related_content' => $this->generateRelatedContentSuggestions($vehicle),
            'schema_type' => 'TechArticle',
            'vehicle_engine' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
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
     * Gerar tags
     */
    protected function generateTags(VehicleData $vehicle): array
    {
        $tags = [
            "quando trocar pneus {$vehicle->make} {$vehicle->model}",
            "pneus {$vehicle->make}",
            "manutenção pneus",
            "pressão pneus",
            "desgaste pneus",
            $vehicle->make,
            $vehicle->model,
            $vehicle->getMainCategory()
        ];

        if ($vehicle->isMotorcycle()) {
            $tags[] = 'Motocicleta';
            $tags[] = 'Pneus moto';
        } else {
            $tags[] = 'Carro';
            $tags[] = 'Automóvel';
        }

        $tags[] = $vehicle->tireSize;
        
        return $tags;
    }

    /**
     * Gerar tópicos relacionados
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
            'title' => "Manutenção Preventiva do {$vehicle->make} {$vehicle->model}",
            'slug' => "manutencao-preventiva-" . Str::slug("{$vehicle->make}-{$vehicle->model}"),
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
     * Gerar informações do veículo
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
            'tire_size' => $vehicle->tireSize,
            'recommended_oil' => $vehicle->recommendedOil
        ];
    }

    /**
     * Gerar dados de filtro
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
     * Adicionar dados específicos do veículo para o template
     */
    protected function generateVehicleTemplateData(VehicleData $vehicle): array
    {
        return [
            // Dados principais do veículo
            'vehicle_name' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
            'vehicle_brand' => $vehicle->make,
            'vehicle_model' => $vehicle->model,
            'vehicle_year' => $vehicle->year,
            'vehicle_category' => $vehicle->getMainCategory(),
            'vehicle_type' => $vehicle->getVehicleType(),
            
            // Especificações técnicas
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
            
            // Informações adicionais
            'recommended_oil' => $vehicle->recommendedOil,
            'is_motorcycle' => $vehicle->isMotorcycle(),
            'is_electric' => $vehicle->isElectric(),
            'is_hybrid' => $vehicle->isHybrid(),
            
            // Dados para URLs e imagens
            'image_url' => "https://mercadoveiculos.com/images/" . strtolower($vehicle->make) . "-" . strtolower($vehicle->model) . "-{$vehicle->year}.jpg",
            'slug' => $this->generateSlug($vehicle),
            'canonical_url' => "https://mercadoveiculos.com/info/" . $this->generateSlug($vehicle)
        ];
    }

    /**
     * Gerar sugestões de conteúdo relacionado
     */
    protected function generateRelatedContentSuggestions(VehicleData $vehicle): array
    {
        $suggestions = [];

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

        if (!$vehicle->isElectric()) {
            $suggestions[] = [
                'title' => "Como Economizar Combustível no {$vehicle->make} {$vehicle->model}",
                'slug' => "como-economizar-combustivel-" . Str::slug("{$vehicle->make}-{$vehicle->model}"),
                'icon' => 'gas-pump'
            ];
        }

        return $suggestions;
    }

    /**
     * Substituir placeholders nos templates
     */
    protected function replacePlaceholders(string $template, VehicleData $vehicle): string
    {
        $replacements = [
            '{make}' => $vehicle->make,
            '{model}' => $vehicle->model,
            '{year}' => $vehicle->year,
            '{tire_size}' => $vehicle->tireSize,
            '{category}' => $vehicle->getMainCategory()
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
