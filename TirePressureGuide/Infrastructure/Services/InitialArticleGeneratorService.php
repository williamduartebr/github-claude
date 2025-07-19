<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Modified InitialArticleGeneratorService - agora gera no formato ideal_tire_pressure_car.json
 * 
 * PRINCIPAL MUDANÇA: 
 * - Conteúdo agora segue estrutura esperada pelos ViewModels
 * - Seções separadas para refinamento Claude ficam fora do 'content'
 * - Formato compatível com IdealTirePressureCarViewModel/MotorcycleViewModel
 */
class InitialArticleGeneratorService
{
    /**
     * Gerar artigo completo no novo formato
     */
    public function generateArticle(array $vehicleData, string $batchId): ?TirePressureArticle
    {
        try {
            // 1. Gerar conteúdo estruturado no formato dos ViewModels
            $structuredContent = $this->generateStructuredContent($vehicleData);
            
            // 2. Gerar seções separadas para refinamento Claude (fora do content)
            $separatedSections = $this->generateSeparatedSections($vehicleData);
            
            // 3. Criar artigo na base de dados
            $article = new TirePressureArticle();
            
            // Dados básicos do veículo
            $article->make = $vehicleData['make'];
            $article->model = $vehicleData['model'];
            $article->year = $vehicleData['year'];
            $article->tire_size = $vehicleData['tire_size'];
            $article->vehicle_data = $vehicleData;
            
            // Metadados e SEO
            $article->title = $this->generateTitle($vehicleData);
            $article->slug = $this->generateSlug($vehicleData);
            $article->wordpress_slug = $article->slug;
            $article->meta_description = $this->generateMetaDescription($vehicleData);
            $article->seo_keywords = $this->generateSeoKeywords($vehicleData);
            
            // NOVA ESTRUTURA: Conteúdo no formato dos ViewModels
            $article->article_content = $structuredContent;
            
            // URLs e template
            $article->template_used = $this->getTemplateForVehicle($vehicleData);
            $article->wordpress_url = $this->generateWordPressUrl($vehicleData);
            $article->canonical_url = $this->generateCanonicalUrl($vehicleData);
            
            // Pressões extraídas
            $article->pressure_light_front = $vehicleData['pressure_empty_front'] ?? 30.0;
            $article->pressure_light_rear = $vehicleData['pressure_empty_rear'] ?? 28.0;
            $article->pressure_spare = $vehicleData['pressure_spare'] ?? 32.0;
            
            // Categoria e batch
            $article->category = $vehicleData['main_category'] ?? 'Outros';
            $article->batch_id = $batchId;
            
            // Status inicial
            $article->generation_status = 'pending';
            $article->quality_checked = false;
            $article->content_score = $this->calculateContentScore($structuredContent);
            
            // SEÇÕES SEPARADAS PARA REFINAMENTO CLAUDE (Fora do content principal)
            $article->sections_intro = $separatedSections['intro'];
            $article->sections_pressure_table = $separatedSections['pressure_table'];
            $article->sections_how_to_calibrate = $separatedSections['how_to_calibrate'];
            $article->sections_middle_content = $separatedSections['middle_content'];
            $article->sections_faq = $separatedSections['faq'];
            $article->sections_conclusion = $separatedSections['conclusion'];
            
            // Inicializar status de refinamento das seções
            $article->sections_status = [
                'intro' => 'pending',
                'pressure_table' => 'pending', 
                'how_to_calibrate' => 'pending',
                'middle_content' => 'pending',
                'faq' => 'pending',
                'conclusion' => 'pending'
            ];
            
            $article->sections_scores = [
                'intro' => 6.0,
                'pressure_table' => 6.0,
                'how_to_calibrate' => 6.0,
                'middle_content' => 6.0,
                'faq' => 6.0,
                'conclusion' => 6.0
            ];
            
            // Salvar
            if ($article->save()) {
                // Marcar como gerado e quebrar em seções
                $article->markAsGenerated();
                
                Log::info("Artigo gerado com sucesso no novo formato", [
                    'vehicle' => $vehicleData['vehicle_identifier'],
                    'template' => $article->template_used,
                    'content_score' => $article->content_score,
                    'sections_count' => count($separatedSections)
                ]);
                
                return $article;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Erro ao gerar artigo", [
                'vehicle' => $vehicleData['vehicle_identifier'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Gerar conteúdo estruturado no formato esperado pelos ViewModels
     * 
     * NOVO FORMATO: Compatível com IdealTirePressureCarViewModel
     */
    protected function generateStructuredContent(array $vehicleData): array
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        
        if ($isMotorcycle) {
            return $this->generateMotorcycleContent($vehicleData);
        }
        
        return $this->generateCarContent($vehicleData);
    }
    
    /**
     * Gerar conteúdo para carros no formato ideal_tire_pressure_car.json
     */
    protected function generateCarContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $tireSize = $vehicleData['tire_size'];
        
        $frontPressure = $vehicleData['pressure_empty_front'] ?? 30;
        $rearPressure = $vehicleData['pressure_empty_rear'] ?? 28;
        $maxFrontPressure = $vehicleData['pressure_max_front'] ?? 36;
        $maxRearPressure = $vehicleData['pressure_max_rear'] ?? 34;
        
        return [
            'introducao' => "Para manter seu {$make} {$model} {$year} sempre em perfeitas condições de segurança e desempenho, a calibragem correta dos pneus é fundamental. A pressão adequada não apenas garante maior vida útil dos pneus, mas também melhora a economia de combustível e a estabilidade do veículo.",
            
            'especificacoes_pneus' => [
                'medida_original' => $tireSize,
                'medida_opcional' => '',
                'indice_carga' => $this->calculateLoadIndex($vehicleData),
                'indice_velocidade' => $this->getSpeedRating($vehicleData),
                'tipo_construcao' => 'Radial',
                'marca_original' => $this->getOriginalTireBrand($vehicleData)
            ],
            
            'tabela_pressoes' => [
                'versoes' => [
                    [
                        'nome_versao' => 'Todas as versões',
                        'motor' => $this->getEngineInfo($vehicleData),
                        'medida_pneu' => $tireSize,
                        'pressao_dianteira_normal' => "{$frontPressure} PSI",
                        'pressao_traseira_normal' => "{$rearPressure} PSI",
                        'pressao_dianteira_carregado' => "{$maxFrontPressure} PSI",
                        'pressao_traseira_carregado' => "{$maxRearPressure} PSI",
                        'observacao' => 'Pressões para uso padrão e com carga máxima'
                    ]
                ],
                'condicoes_uso' => [
                    [
                        'situacao' => 'Uso urbano normal',
                        'ocupantes' => '1-2 pessoas',
                        'bagagem' => 'Leve',
                        'ajuste_dianteira' => "{$frontPressure} PSI",
                        'ajuste_traseira' => "{$rearPressure} PSI",
                        'beneficios' => 'Maior conforto e economia de combustível'
                    ],
                    [
                        'situacao' => 'Família completa',
                        'ocupantes' => '4-5 pessoas',
                        'bagagem' => 'Moderada',
                        'ajuste_dianteira' => ($frontPressure + 2) . " PSI",
                        'ajuste_traseira' => ($rearPressure + 2) . " PSI",
                        'beneficios' => 'Compensação para peso adicional'
                    ],
                    [
                        'situacao' => 'Viagem com carga',
                        'ocupantes' => 'Variável',
                        'bagagem' => 'Pesada',
                        'ajuste_dianteira' => "{$maxFrontPressure} PSI",
                        'ajuste_traseira' => "{$maxRearPressure} PSI",
                        'beneficios' => 'Máxima segurança e estabilidade'
                    ]
                ]
            ],
            
            'conversao_unidades' => [
                'tabela_conversao' => [
                    ['psi' => '28', 'bar' => '1.9', 'kgf_cm2' => '1.9'],
                    ['psi' => '30', 'bar' => '2.1', 'kgf_cm2' => '2.1'],
                    ['psi' => '32', 'bar' => '2.2', 'kgf_cm2' => '2.2'],
                    ['psi' => '34', 'bar' => '2.3', 'kgf_cm2' => '2.3'],
                    ['psi' => '36', 'bar' => '2.5', 'kgf_cm2' => '2.5']
                ],
                'observacao' => 'Conversão aproximada. Use sempre a unidade especificada pelo fabricante.'
            ],
            
            'localizacao_etiqueta' => [
                'local_principal' => 'Soleira da porta do motorista',
                'local_alternativo' => 'Manual do proprietário',
                'informacoes_contidas' => [
                    'Pressões para diferentes cargas',
                    'Tamanho original dos pneus',
                    'Pressão do pneu sobressalente'
                ],
                'dicas_localizacao' => [
                    'Procure uma etiqueta adesiva na parte interna da porta',
                    'Algumas vezes está localizada na tampa do combustível',
                    'Consulte sempre o manual se não encontrar'
                ]
            ],
            
            'beneficios_calibragem' => [
                'seguranca' => [
                    'Maior aderência ao asfalto',
                    'Melhor desempenho em frenagens',
                    'Redução do risco de aquaplanagem',
                    'Maior estabilidade em curvas'
                ],
                'economia' => [
                    'Redução do consumo de combustível',
                    'Maior vida útil dos pneus',
                    'Menor desgaste da suspensão',
                    'Economia em manutenções'
                ],
                'desempenho' => [
                    'Melhor dirigibilidade',
                    'Maior conforto ao dirigir',
                    'Menor ruído interno',
                    'Resposta mais precisa da direção'
                ]
            ],
            
            'dicas_manutencao' => [
                'frequencia_calibragem' => 'A cada 15 dias ou antes de viagens longas',
                'horario_ideal' => 'Pela manhã, com pneus frios',
                'equipamento_recomendado' => 'Calibrador digital de qualidade',
                'cuidados_especiais' => [
                    'Verifique sempre o pneu sobressalente',
                    'Não se esqueça das tampas das válvulas',
                    'Inspecione visualmente os pneus regularmente',
                    'Faça rodízio dos pneus conforme recomendação'
                ],
                'sinais_pressao_incorreta' => [
                    'Desgaste irregular dos pneus',
                    'Aumento no consumo de combustível',
                    'Comportamento estranho da direção',
                    'Aviso do sistema TPMS aceso'
                ]
            ],
            
            'alertas_importantes' => [
                [
                    'tipo' => 'warning',
                    'titulo' => 'Nunca calibre com pneus quentes',
                    'descricao' => 'Pneus aquecidos podem mostrar pressão até 4 PSI superior ao real'
                ],
                [
                    'tipo' => 'info',
                    'titulo' => 'Calibre todos os pneus',
                    'descricao' => 'Não se esqueça do pneu sobressalente - ele também perde pressão com o tempo'
                ],
                [
                    'tipo' => 'danger',
                    'titulo' => 'Pressão muito baixa é perigosa',
                    'descricao' => 'Pode causar superaquecimento do pneu e até mesmo estouro em alta velocidade'
                ]
            ],
            
            'perguntas_frequentes' => [
                [
                    'question' => "Qual a pressão correta para meu {$make} {$model} {$year}?",
                    'answer' => "Para uso normal: dianteiros {$frontPressure} PSI, traseiros {$rearPressure} PSI. Com carga máxima: dianteiros {$maxFrontPressure} PSI, traseiros {$maxRearPressure} PSI."
                ],
                [
                    'question' => 'Com que frequência devo calibrar os pneus?',
                    'answer' => 'Recomenda-se calibrar a cada 15 dias ou antes de viagens longas. Pneus perdem pressão naturalmente, cerca de 1-2 PSI por mês.'
                ],
                [
                    'question' => 'Posso usar pressões diferentes das recomendadas?',
                    'answer' => 'Não é recomendado. As pressões são calculadas pelos engenheiros para garantir o melhor equilíbrio entre segurança, economia e desempenho.'
                ],
                [
                    'question' => 'O que fazer se o pneu estiver perdendo pressão rapidamente?',
                    'answer' => 'Procure imediatamente um borracheiro para verificar furos, problemas na válvula ou danos na roda. Nunca ignore perda rápida de pressão.'
                ]
            ],
            
            'consideracoes_finais' => "Manter a pressão correta nos pneus do seu {$make} {$model} {$year} é uma das manutenções mais simples e importantes que você pode fazer. Além de garantir sua segurança, você economiza combustível e prolonga a vida útil dos pneus. Lembre-se sempre de verificar a pressão com pneus frios e seguir as especificações do fabricante."
        ];
    }
    
    /**
     * Gerar seções separadas para refinamento Claude
     * 
     * IMPORTANTE: Estas seções ficam FORA do content principal
     * Serão refinadas pela API Claude na Etapa 2
     */
    protected function generateSeparatedSections(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        return [
            'intro' => [
                'title' => 'Introdução Personalizada',
                'content' => "Descubra a pressão ideal para os pneus do seu {$make} {$model} {$year}.",
                'target_tone' => 'educational_friendly',
                'min_words' => 80,
                'max_words' => 120,
                'status' => 'pending_refinement'
            ],
            
            'pressure_table' => [
                'title' => 'Tabela de Pressões Otimizada',
                'content' => [
                    'main_pressures' => $this->getMainPressures($vehicleData),
                    'usage_scenarios' => $this->getUsageScenarios($vehicleData)
                ],
                'target_tone' => 'technical_precise',
                'enhancement_focus' => 'data_accuracy',
                'status' => 'pending_refinement'
            ],
            
            'how_to_calibrate' => [
                'title' => 'Procedimento Específico do Veículo',
                'content' => $this->generateBasicCalibrationSteps($vehicleData),
                'target_tone' => 'instructional_clear',
                'vehicle_specific' => true,
                'min_steps' => 5,
                'max_steps' => 8,
                'status' => 'pending_refinement'
            ],
            
            'middle_content' => [
                'title' => 'Dicas e Alertas Importantes',
                'content' => $this->generateMiddleContent($vehicleData),
                'target_tone' => 'helpful_advisory',
                'enhancement_focus' => 'safety_tips',
                'status' => 'pending_refinement'
            ],
            
            'faq' => [
                'title' => 'FAQ Personalizada',
                'content' => $this->generateBasicFAQ($vehicleData),
                'target_tone' => 'conversational_helpful',
                'vehicle_specific' => true,
                'min_questions' => 4,
                'max_questions' => 7,
                'status' => 'pending_refinement'
            ],
            
            'conclusion' => [
                'title' => 'Conclusão e Call-to-Action',
                'content' => "Mantenha seu {$make} {$model} sempre seguro com a calibragem correta!",
                'target_tone' => 'encouraging_actionable',
                'include_cta' => true,
                'min_words' => 60,
                'max_words' => 100,
                'status' => 'pending_refinement'
            ]
        ];
    }
    
    /**
     * Determinar template baseado no tipo de veículo
     */
    public function getTemplateForVehicle(array $vehicleData): string
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        
        return $isMotorcycle ? 'ideal_tire_pressure_motorcycle' : 'ideal_tire_pressure_car';
    }
    
    /**
     * Calcular score de qualidade do conteúdo
     */
    public function calculateContentScore(array $content): float
    {
        $score = 5.0; // Base score
        
        // Verificar seções essenciais
        if (!empty($content['introducao'])) $score += 0.5;
        if (!empty($content['tabela_pressoes'])) $score += 1.0;
        if (!empty($content['perguntas_frequentes'])) $score += 0.5;
        if (!empty($content['consideracoes_finais'])) $score += 0.5;
        
        // Verificar qualidade da tabela de pressões
        if (!empty($content['tabela_pressoes']['versoes'])) $score += 0.5;
        if (!empty($content['tabela_pressoes']['condicoes_uso'])) $score += 0.5;
        
        return min(10.0, $score);
    }
    
    /**
     * Gerar conteúdo para motocicletas no formato ideal_tire_pressure_motorcycle.json
     */
    protected function generateMotorcycleContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $tireSize = $vehicleData['tire_size'];
        
        $frontPressure = $vehicleData['pressure_empty_front'] ?? 28;
        $rearPressure = $vehicleData['pressure_empty_rear'] ?? 26;
        $maxFrontPressure = $vehicleData['pressure_max_front'] ?? 32;
        $maxRearPressure = $vehicleData['pressure_max_rear'] ?? 30;
        
        return [
            'introducao' => "Para manter sua {$make} {$model} {$year} sempre em perfeitas condições de segurança e desempenho, a calibragem correta dos pneus é fundamental. Em motocicletas, a pressão adequada é ainda mais crítica, pois afeta diretamente a estabilidade e o controle.",
            
            'especificacoes_pneus' => [
                'pneu_dianteiro' => [
                    'medida_original' => $this->getMotorcycleFrontTireSize($tireSize),
                    'pressao_recomendada' => "{$frontPressure} PSI",
                    'pressao_maxima' => "{$maxFrontPressure} PSI"
                ],
                'pneu_traseiro' => [
                    'medida_original' => $this->getMotorcycleRearTireSize($tireSize),
                    'pressao_recomendada' => "{$rearPressure} PSI", 
                    'pressao_maxima' => "{$maxRearPressure} PSI"
                ],
                'tipo_construcao' => 'Radial',
                'indice_velocidade' => $this->getMotorcycleSpeedRating($vehicleData),
                'marca_original' => $this->getMotorcycleOriginalTireBrand($vehicleData)
            ],
            
            'tabela_pressoes' => [
                'uso_urbano' => [
                    'dianteiro' => "{$frontPressure} PSI",
                    'traseiro' => "{$rearPressure} PSI",
                    'observacao' => 'Ideal para uso diário na cidade'
                ],
                'uso_estrada' => [
                    'dianteiro' => ($frontPressure + 2) . " PSI",
                    'traseiro' => ($rearPressure + 2) . " PSI",
                    'observacao' => 'Para viagens longas e alta velocidade'
                ],
                'piloto_pesado' => [
                    'dianteiro' => ($frontPressure + 1) . " PSI",
                    'traseiro' => ($rearPressure + 3) . " PSI",
                    'observacao' => 'Acima de 90kg, ajustar pressão traseira'
                ],
                'com_carona' => [
                    'dianteiro' => ($frontPressure + 1) . " PSI",
                    'traseiro' => "{$maxRearPressure} PSI",
                    'observacao' => 'Essencial para estabilidade com carona'
                ]
            ],
            
            'conversao_unidades' => [
                'tabela_conversao' => [
                    ['psi' => '26', 'bar' => '1.8', 'kgf_cm2' => '1.8'],
                    ['psi' => '28', 'bar' => '1.9', 'kgf_cm2' => '1.9'],
                    ['psi' => '30', 'bar' => '2.1', 'kgf_cm2' => '2.1'],
                    ['psi' => '32', 'bar' => '2.2', 'kgf_cm2' => '2.2'],
                    ['psi' => '34', 'bar' => '2.3', 'kgf_cm2' => '2.3']
                ],
                'observacao' => 'Conversão aproximada. Motocicletas requerem precisão maior que carros.'
            ],
            
            'localizacao_informacoes' => [
                'local_principal' => 'Adesivo no chassi ou manual do proprietário',
                'local_alternativo' => 'Lateral do pneu (pressão máxima)',
                'informacoes_contidas' => [
                    'Pressões para diferentes condições',
                    'Tamanho original dos pneus',
                    'Carga máxima suportada'
                ],
                'dicas_localizacao' => [
                    'Em scooters, verifique sob o assento',
                    'Em motos naked, próximo ao monoshock',
                    'Manual sempre tem informações completas'
                ]
            ],
            
            'beneficios_calibragem' => [
                'seguranca' => [
                    'Maior estabilidade em curvas',
                    'Melhor aderência em frenagens',
                    'Redução do risco de derrapagem',
                    'Controle superior em alta velocidade'
                ],
                'economia' => [
                    'Redução do consumo de combustível',
                    'Maior vida útil dos pneus',
                    'Menor desgaste da suspensão',
                    'Economia em manutenções'
                ],
                'desempenho' => [
                    'Melhor manobrabilidade',
                    'Maior precisão na direção',
                    'Conforto superior do piloto',
                    'Resposta mais rápida do acelerador'
                ]
            ],
            
            'consideracoes_especiais' => [
                'temperatura_ambiente' => [
                    'Calibre com pneus frios sempre',
                    'Pressão aumenta 1-2 PSI com aquecimento',
                    'No calor excessivo, reduza 1 PSI'
                ],
                'tipo_pilotagem' => [
                    'Pilotagem esportiva: +2 PSI',
                    'Uso urbano leve: pressão normal',
                    'Viagens longas: +1-2 PSI'
                ],
                'peso_piloto' => [
                    'Até 70kg: pressão padrão',
                    '70-90kg: +1 PSI traseiro',
                    'Acima 90kg: +2-3 PSI traseiro'
                ]
            ],
            
            'dicas_manutencao' => [
                'frequencia_calibragem' => 'A cada 7 dias ou antes de viagens',
                'horario_ideal' => 'Pela manhã, antes da primeira viagem',
                'equipamento_recomendado' => 'Calibrador digital específico para motos',
                'cuidados_especiais' => [
                    'Verifique válvulas regularmente',
                    'Use tampas nas válvulas sempre',
                    'Inspecione pneus a cada calibragem',
                    'Monitore desgaste dos sulcos'
                ],
                'sinais_pressao_incorreta' => [
                    'Moto "pesada" para virar',
                    'Desgaste irregular dos pneus',
                    'Aumento no consumo',
                    'Instabilidade em curvas'
                ]
            ],
            
            'alertas_criticos' => [
                [
                    'tipo' => 'danger',
                    'titulo' => 'Nunca rode com pressão baixa',
                    'descricao' => 'Pressão baixa em motos pode causar perda total de controle e acidentes graves'
                ],
                [
                    'tipo' => 'warning',
                    'titulo' => 'Calibre sempre com pneus frios',
                    'descricao' => 'Pneus aquecidos mostram pressão até 3 PSI superior ao real'
                ],
                [
                    'tipo' => 'info',
                    'titulo' => 'Pressão traseira é mais crítica',
                    'descricao' => 'Pneu traseiro suporta mais peso e precisa de maior atenção'
                ]
            ],
            
            'procedimento_calibragem' => [
                'passo_1' => [
                    'titulo' => 'Preparação',
                    'descricao' => 'Deixe a moto esfriar por pelo menos 3 horas',
                    'dicas' => ['Prefira calibrar pela manhã', 'Evite após viagens longas']
                ],
                'passo_2' => [
                    'titulo' => 'Verificação inicial',
                    'descricao' => 'Inspecione visualmente os pneus',
                    'dicas' => ['Procure objetos estranhos', 'Verifique rachadura nas laterais']
                ],
                'passo_3' => [
                    'titulo' => 'Medição',
                    'descricao' => 'Use calibrador digital de qualidade',
                    'dicas' => ['Faça duas medições para confirmar', 'Anote os valores encontrados']
                ],
                'passo_4' => [
                    'titulo' => 'Ajuste',
                    'descricao' => 'Ajuste conforme tabela de pressões',
                    'dicas' => ['Considere peso do piloto', 'Ajuste tipo de uso previsto']
                ],
                'passo_5' => [
                    'titulo' => 'Finalização',
                    'descricao' => 'Coloque tampas nas válvulas',
                    'dicas' => ['Tampas evitam sujeira', 'Anote data da calibragem']
                ]
            ],
            
            'perguntas_frequentes' => [
                [
                    'question' => "Qual a pressão correta para minha {$make} {$model} {$year}?",
                    'answer' => "Para uso urbano normal: dianteiro {$frontPressure} PSI, traseiro {$rearPressure} PSI. Para viagens ou piloto pesado, consulte nossa tabela detalhada."
                ],
                [
                    'question' => 'Com que frequência devo calibrar os pneus da moto?',
                    'answer' => 'Recomenda-se calibrar semanalmente ou antes de viagens. Motocicletas perdem pressão mais rapidamente que carros.'
                ],
                [
                    'question' => 'Posso usar calibrador de posto de gasolina?',
                    'answer' => 'Pode, mas calibradores específicos para motos são mais precisos. A diferença de 1-2 PSI é significativa em motocicletas.'
                ],
                [
                    'question' => 'A pressão muda com o peso do piloto?',
                    'answer' => 'Sim, principalmente no pneu traseiro. Pilotos mais pesados precisam aumentar 2-3 PSI na traseira para compensar.'
                ],
                [
                    'question' => 'O que acontece se eu calibrar com pressão errada?',
                    'answer' => 'Pressão baixa causa instabilidade e desgaste rápido. Pressão alta reduz aderência e conforto. Ambos são perigosos.'
                ]
            ],
            
            'consideracoes_finais' => "Manter a pressão correta dos pneus da sua {$make} {$model} {$year} é questão de segurança vital. Diferente dos carros, em motocicletas uma pequena variação na pressão pode significar a diferença entre um passeio seguro e um acidente grave. Faça da calibragem um hábito semanal e sua moto sempre estará pronta para levá-lo com segurança a qualquer destino."
        ];
    }
    
    // ===== MÉTODOS AUXILIARES PARA MOTOCICLETAS =====
    
    /**
     * Obter tamanho do pneu dianteiro da moto
     */
    protected function getMotorcycleFrontTireSize(string $tireSize): string
    {
        // Se tireSize contém "/" é formato "dianteiro/traseiro"
        if (str_contains($tireSize, '/')) {
            $parts = explode('/', $tireSize);
            return trim($parts[0]);
        }
        
        // Tamanhos comuns de pneu dianteiro para motos
        $frontTireMap = [
            '125cc' => '80/100-18',
            '150cc' => '90/90-19', 
            '250cc' => '110/70-17',
            '300cc' => '110/70-17',
            '600cc' => '120/70-17',
            '1000cc' => '120/70-17'
        ];
        
        // Tentar deduzir pela cilindrada no modelo
        foreach ($frontTireMap as $cc => $tire) {
            if (str_contains(strtolower($tireSize), str_replace('cc', '', $cc))) {
                return $tire;
            }
        }
        
        return $tireSize ?: '110/70-17'; // Padrão
    }
    
    /**
     * Obter tamanho do pneu traseiro da moto
     */
    protected function getMotorcycleRearTireSize(string $tireSize): string
    {
        // Se tireSize contém "/" é formato "dianteiro/traseiro"
        if (str_contains($tireSize, '/')) {
            $parts = explode('/', $tireSize);
            return isset($parts[1]) ? trim($parts[1]) : trim($parts[0]);
        }
        
        // Tamanhos comuns de pneu traseiro para motos
        $rearTireMap = [
            '125cc' => '90/90-18',
            '150cc' => '100/90-18',
            '250cc' => '140/70-17', 
            '300cc' => '140/70-17',
            '600cc' => '180/55-17',
            '1000cc' => '190/50-17'
        ];
        
        // Tentar deduzir pela cilindrada no modelo
        foreach ($rearTireMap as $cc => $tire) {
            if (str_contains(strtolower($tireSize), str_replace('cc', '', $cc))) {
                return $tire;
            }
        }
        
        return $tireSize ?: '140/70-17'; // Padrão
    }
    
    /**
     * Obter índice de velocidade para motos
     */
    protected function getMotorcycleSpeedRating(array $vehicleData): string
    {
        $year = $vehicleData['year'] ?? 2020;
        $category = strtolower($vehicleData['main_category'] ?? '');
        
        // Motos mais novas tendem a ter índices maiores
        if ($year >= 2020) {
            if (str_contains($category, 'sport') || str_contains($category, 'touring')) {
                return 'W'; // 270 km/h
            }
            return 'H'; // 210 km/h
        }
        
        return 'S'; // 180 km/h - padrão para motos mais antigas
    }
    
    /**
     * Obter marca original de pneus para motos
     */
    protected function getMotorcycleOriginalTireBrand(array $vehicleData): string
    {
        $make = strtolower($vehicleData['make'] ?? '');
        
        $brandMap = [
            'honda' => 'Dunlop, Michelin',
            'yamaha' => 'Bridgestone, Pirelli',
            'kawasaki' => 'Bridgestone, Dunlop',
            'suzuki' => 'Bridgestone, Dunlop',
            'bmw' => 'Metzeler, Continental',
            'ducati' => 'Pirelli, Metzeler'
        ];
        
        return $brandMap[$make] ?? 'Bridgestone, Pirelli';
    }
    
    protected function generateTitle(array $vehicleData): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        return "Pressão Ideal dos Pneus - {$make} {$model} {$year}";
    }
    
    protected function generateSlug(array $vehicleData): string
    {
        $make = Str::slug($vehicleData['make']);
        $model = Str::slug($vehicleData['model']);
        $year = $vehicleData['year'];
        
        return "pressao-ideal-pneu-{$make}-{$model}-{$year}";
    }
    
    protected function generateMetaDescription(array $vehicleData): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $frontPressure = $vehicleData['pressure_empty_front'] ?? 30;
        $rearPressure = $vehicleData['pressure_empty_rear'] ?? 28;
        
        return "Descubra a pressão ideal dos pneus do {$make} {$model} {$year}. Dianteiros: {$frontPressure} PSI, Traseiros: {$rearPressure} PSI. Guia completo com tabela e dicas de segurança.";
    }
    
    protected function generateSeoKeywords(array $vehicleData): array
    {
        $make = strtolower($vehicleData['make']);
        $model = strtolower($vehicleData['model']);
        $year = $vehicleData['year'];
        
        return [
            "pressão pneu {$make} {$model} {$year}",
            "calibragem {$make} {$model}",
            "pressão ideal pneu {$make}",
            "calibragem pneu carro",
            "manutenção automotiva",
            "economia combustível",
            "segurança automotiva"
        ];
    }
    
    protected function getMainPressures(array $vehicleData): array
    {
        return [
            'front_normal' => $vehicleData['pressure_empty_front'] ?? 30,
            'rear_normal' => $vehicleData['pressure_empty_rear'] ?? 28,
            'front_loaded' => $vehicleData['pressure_max_front'] ?? 36,
            'rear_loaded' => $vehicleData['pressure_max_rear'] ?? 34,
            'spare' => $vehicleData['pressure_spare'] ?? 32
        ];
    }
    
    protected function getUsageScenarios(array $vehicleData): array
    {
        $frontNormal = $vehicleData['pressure_empty_front'] ?? 30;
        $rearNormal = $vehicleData['pressure_empty_rear'] ?? 28;
        
        return [
            'urban' => ['front' => $frontNormal, 'rear' => $rearNormal],
            'family' => ['front' => $frontNormal + 2, 'rear' => $rearNormal + 2],
            'highway' => ['front' => $frontNormal + 2, 'rear' => $rearNormal + 1],
            'loaded' => ['front' => $vehicleData['pressure_max_front'] ?? 36, 'rear' => $vehicleData['pressure_max_rear'] ?? 34]
        ];
    }
    
    protected function generateBasicCalibrationSteps(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        
        return [
            'Verifique a pressão sempre com pneus frios',
            "Localize a etiqueta de pressões do {$make} na porta do motorista",
            'Use um calibrador digital de qualidade',
            'Ajuste conforme a carga do veículo',
            'Não se esqueça do pneu sobressalente',
            'Recoloque as tampas das válvulas'
        ];
    }
    
    protected function generateMiddleContent(array $vehicleData): array
    {
        return [
            'safety_tips' => [
                'Nunca calibre com pneus aquecidos',
                'Verifique sinais de desgaste irregular',
                'Pressão baixa pode causar aquecimento excessivo'
            ],
            'maintenance_tips' => [
                'Calibre a cada 15 dias',
                'Faça inspeção visual regular',
                'Considere o rodízio dos pneus'
            ]
        ];
    }
    
    protected function generateBasicFAQ(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        
        return [
            [
                'question' => "Qual a pressão recomendada para o {$make} {$model}?",
                'answer' => 'Varia conforme a carga e condições de uso. Consulte nossa tabela detalhada.'
            ],
            [
                'question' => 'Com que frequência calibrar?',
                'answer' => 'A cada 15 dias ou antes de viagens longas.'
            ]
        ];
    }
    
    // Métodos auxiliares para dados específicos do veículo
    protected function calculateLoadIndex(array $vehicleData): string
    {
        // Lógica simplificada baseada na categoria
        $category = $vehicleData['main_category'] ?? 'hatch';
        $loadIndexMap = [
            'hatch' => '82',
            'sedan' => '84', 
            'suv' => '86',
            'pickup' => '88'
        ];
        
        return $loadIndexMap[strtolower($category)] ?? '82';
    }
    
    protected function getSpeedRating(array $vehicleData): string
    {
        // Baseado no ano e categoria
        $year = $vehicleData['year'];
        return $year >= 2018 ? 'H' : 'T';
    }
    
    protected function getOriginalTireBrand(array $vehicleData): string
    {
        $make = strtolower($vehicleData['make']);
        $brandMap = [
            'toyota' => 'Dunlop, Bridgestone',
            'honda' => 'Michelin, Bridgestone', 
            'chevrolet' => 'Pirelli, Goodyear',
            'volkswagen' => 'Continental, Pirelli',
            'ford' => 'Goodyear, Pirelli'
        ];
        
        return $brandMap[$make] ?? 'Bridgestone, Pirelli';
    }
    
    protected function getEngineInfo(array $vehicleData): string
    {
        // Simplificado - poderia ser mais específico
        $category = $vehicleData['main_category'] ?? 'hatch';
        $engineMap = [
            'hatch' => '1.0/1.6 Flex',
            'sedan' => '1.6/2.0 Flex',
            'suv' => '1.6/2.0 Flex',
            'pickup' => '2.8 Diesel'
        ];
        
        return $engineMap[strtolower($category)] ?? '1.6 Flex';
    }
    
    protected function generateWordPressUrl(array $vehicleData): string
    {
        $slug = $this->generateSlug($vehicleData);
        return "https://mercadoveiculos.com/info/{$slug}/";
    }
    
    protected function generateCanonicalUrl(array $vehicleData): string
    {
        return $this->generateWordPressUrl($vehicleData);
    }
}   