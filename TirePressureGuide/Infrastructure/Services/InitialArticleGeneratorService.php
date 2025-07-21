<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Modified InitialArticleGeneratorService - DUAL TEMPLATE SUPPORT
 * 
 * NOVA FUNCIONALIDADE: 
 * - Gera DOIS TIPOS de artigo com mesmo código base
 * - 'ideal' → formato IdealTirePressureCarViewModel
 * - 'calibration' → formato TirePressureGuideCarViewModel
 * - Detecção automática car vs motorcycle
 */
class InitialArticleGeneratorService
{
    /**
     * Gerar artigo completo com template específico
     * 
     * @param array $vehicleData
     * @param string $batchId
     * @param string $templateType 'ideal' ou 'calibration'
     */
    public function generateArticle(array $vehicleData, string $batchId, string $templateType = 'ideal'): ?TirePressureArticle
    {
        try {
            // 1. Validar template type
            if (!in_array($templateType, ['ideal', 'calibration'])) {
                throw new \Exception("Template type inválido: {$templateType}. Use 'ideal' ou 'calibration'");
            }

            // 2. Gerar conteúdo estruturado baseado no template type
            $structuredContent = $this->generateStructuredContent($vehicleData, $templateType);
            
            // 3. Gerar seções separadas para refinamento Claude
            $separatedSections = $this->generateSeparatedSections($vehicleData, $templateType);
            
            // 4. Criar artigo na base de dados
            $article = new TirePressureArticle();
            
            // Dados básicos do veículo
            $article->make = $vehicleData['make'];
            $article->model = $vehicleData['model'];
            $article->year = $vehicleData['year'];
            $article->tire_size = $vehicleData['tire_size'];
            $article->vehicle_data = $vehicleData;
            
            // NOVO: Template type para diferenciar artigos
            $article->template_type = $templateType;
            
            // Metadados e SEO baseados no template
            $article->title = $this->generateTitle($vehicleData, $templateType);
            $article->slug = $this->generateSlug($vehicleData, $templateType);
            $article->wordpress_slug = $article->slug;
            $article->meta_description = $this->generateMetaDescription($vehicleData, $templateType);
            $article->seo_keywords = $this->generateSeoKeywords($vehicleData, $templateType);
            
            // Conteúdo estruturado baseado no template
            $article->article_content = $structuredContent;
            
            // URLs e template
            $article->template_used = $this->getTemplateForVehicle($vehicleData, $templateType);
            $article->wordpress_url = $this->generateWordPressUrl($vehicleData, $templateType);
            $article->canonical_url = $this->generateCanonicalUrl($vehicleData, $templateType);
            
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
            
            // Seções separadas para refinamento Claude
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
                
                Log::info("Artigo gerado com sucesso - Template: {$templateType}", [
                    'vehicle' => $vehicleData['vehicle_identifier'],
                    'template_type' => $templateType,
                    'template_used' => $article->template_used,
                    'content_score' => $article->content_score,
                    'slug' => $article->slug
                ]);
                
                return $article;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Erro ao gerar artigo - Template: {$templateType}", [
                'vehicle' => $vehicleData['vehicle_identifier'] ?? 'unknown',
                'template_type' => $templateType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Gerar conteúdo estruturado baseado no template type
     */
    protected function generateStructuredContent(array $vehicleData, string $templateType): array
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        
        if ($templateType === 'ideal') {
            return $isMotorcycle 
                ? $this->generateIdealMotorcycleContent($vehicleData)
                : $this->generateIdealCarContent($vehicleData);
        } elseif ($templateType === 'calibration') {
            return $isMotorcycle
                ? $this->generateCalibrationMotorcycleContent($vehicleData)
                : $this->generateCalibrationCarContent($vehicleData);
        }
        
        throw new \Exception("Template type não suportado: {$templateType}");
    }
    
    /**
     * Gerar conteúdo IDEAL para carros (IdealTirePressureCarViewModel)
     */
    protected function generateIdealCarContent(array $vehicleData): array
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
            'introducao' => "Para manter seu {$make} {$model} {$year} sempre em perfeitas condições de segurança e desempenho, a pressão ideal dos pneus é fundamental. Conhecer os valores corretos garante maior economia de combustível e vida útil dos pneus.",
            
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
                        'observacao' => 'Pressões ideais para uso padrão e com carga máxima'
                    ]
                ],
                'condicoes_uso' => [
                    [
                        'situacao' => 'Uso urbano normal',
                        'ocupantes' => '1-2 pessoas',
                        'bagagem' => 'Leve',
                        'ajuste_dianteira' => "{$frontPressure} PSI",
                        'ajuste_traseira' => "{$rearPressure} PSI",
                        'beneficios' => 'Pressão ideal para economia e conforto'
                    ]
                ]
            ],
            
            'conversao_unidades' => $this->generateDefaultUnitConversion(),
            'localizacao_etiqueta' => $this->generateLocationInfo(),
            'beneficios_calibragem' => $this->generateBenefitsInfo(),
            'dicas_manutencao' => $this->generateMaintenanceTips(),
            'alertas_importantes' => $this->generateImportantAlerts(),
            'perguntas_frequentes' => $this->generateIdealFAQ($vehicleData),
            'consideracoes_finais' => "Manter a pressão ideal dos pneus do seu {$make} {$model} {$year} é essencial para máxima segurança, economia e desempenho. Verifique regularmente e siga sempre as especificações do fabricante."
        ];
    }
    
    /**
     * Gerar conteúdo CALIBRAGEM para carros (TirePressureGuideCarViewModel)
     */
    protected function generateCalibrationCarContent(array $vehicleData): array
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
            'introducao' => "Aprenda como calibrar corretamente os pneus do seu {$make} {$model} {$year}. Um procedimento simples que garante segurança, economia e maior vida útil dos pneus quando feito corretamente.",
            
            'tire_specifications' => [
                'original_size' => $tireSize,
                'alternative_size' => '',
                'load_index' => $this->calculateLoadIndex($vehicleData),
                'speed_rating' => $this->getSpeedRating($vehicleData),
                'construction_type' => 'Radial',
                'recommended_brands' => $this->getOriginalTireBrand($vehicleData),
                'tread_depth_new' => '8.0mm',
                'tread_depth_minimum' => '1.6mm'
            ],
            
            'pressure_table' => [
                'standard_conditions' => [
                    'front_empty' => "{$frontPressure} PSI",
                    'rear_empty' => "{$rearPressure} PSI",
                    'front_loaded' => "{$maxFrontPressure} PSI",
                    'rear_loaded' => "{$maxRearPressure} PSI",
                    'spare_tire' => ($vehicleData['pressure_spare'] ?? 32) . " PSI"
                ],
                'usage_scenarios' => [
                    [
                        'scenario' => 'Uso urbano diário',
                        'description' => '1-2 ocupantes, bagagem leve',
                        'front_pressure' => "{$frontPressure} PSI",
                        'rear_pressure' => "{$rearPressure} PSI",
                        'notes' => 'Ideal para economia de combustível'
                    ],
                    [
                        'scenario' => 'Viagem familiar',
                        'description' => 'Família completa, bagagens',
                        'front_pressure' => ($frontPressure + 2) . " PSI",
                        'rear_pressure' => ($rearPressure + 2) . " PSI",
                        'notes' => 'Ajuste para peso adicional'
                    ],
                    [
                        'scenario' => 'Carga máxima',
                        'description' => 'Veículo totalmente carregado',
                        'front_pressure' => "{$maxFrontPressure} PSI",
                        'rear_pressure' => "{$maxRearPressure} PSI",
                        'notes' => 'Máxima segurança e estabilidade'
                    ]
                ]
            ],
            
            'calibration_procedure' => [
                'preparation' => [
                    'step_1' => 'Aguarde pelo menos 3 horas após dirigir (pneus frios)',
                    'step_2' => 'Prepare calibrador digital de qualidade',
                    'step_3' => 'Tenha em mãos a especificação de pressão',
                    'step_4' => 'Inspecione visualmente os pneus antes de calibrar'
                ],
                'calibration_steps' => [
                    'step_1' => [
                        'title' => 'Remova a tampa da válvula',
                        'description' => 'Guarde em local seguro para não perder',
                        'tip' => 'Use sempre tampas originais ou compatíveis'
                    ],
                    'step_2' => [
                        'title' => 'Conecte o calibrador',
                        'description' => 'Pressione firmemente contra a válvula',
                        'tip' => 'Ouvir escape de ar é normal nos primeiros segundos'
                    ],
                    'step_3' => [
                        'title' => 'Leia a pressão atual',
                        'description' => 'Anote o valor mostrado no calibrador',
                        'tip' => 'Faça duas leituras para confirmar precisão'
                    ],
                    'step_4' => [
                        'title' => 'Ajuste se necessário',
                        'description' => 'Adicione ou retire ar conforme necessário',
                        'tip' => 'Pequenos ajustes são normais a cada calibragem'
                    ],
                    'step_5' => [
                        'title' => 'Recoloque a tampa',
                        'description' => 'Aperte bem para vedar a válvula',
                        'tip' => 'Tampa previne entrada de sujeira na válvula'
                    ]
                ],
                'post_calibration' => [
                    'verify_all_tires' => 'Verifique todos os pneus, incluindo sobressalente',
                    'visual_inspection' => 'Faça inspeção visual para detectar problemas',
                    'record_date' => 'Anote a data da calibragem para controle',
                    'test_drive' => 'Faça um teste rápido para verificar comportamento'
                ]
            ],
            
            'equipment_guide' => [
                'calibrator_types' => [
                    'digital' => 'Mais preciso, recomendado para uso frequente',
                    'analog' => 'Econômico, adequado para uso ocasional',
                    'gas_station' => 'Disponível em postos, verificar calibração'
                ],
                'recommended_features' => [
                    'precision' => 'Precisão de ±1 PSI',
                    'display' => 'Display de fácil leitura',
                    'auto_shutoff' => 'Desligamento automático para economia',
                    'units' => 'Suporte a PSI, BAR e kgf/cm²'
                ]
            ],
            
            'tpms_system' => [
                'description' => 'Sistema de Monitoramento de Pressão dos Pneus',
                'availability' => $this->getTPMSAvailability($vehicleData),
                'warning_triggers' => [
                    'low_pressure' => 'Pressão 25% abaixo do recomendado',
                    'high_pressure' => 'Pressão excessivamente alta',
                    'sensor_malfunction' => 'Falha no sensor individual'
                ],
                'reset_procedure' => [
                    'step_1' => 'Calibre todos os pneus na pressão correta',
                    'step_2' => 'Localize botão TPMS no painel ou menu',
                    'step_3' => 'Mantenha pressionado até luz piscar',
                    'step_4' => 'Dirija por 15 minutos para recalibração'
                ],
                'troubleshooting' => [
                    'light_stays_on' => 'Verifique pressão de todos os pneus novamente',
                    'intermittent_warning' => 'Possível sensor com bateria baixa',
                    'no_response' => 'Consulte manual ou procure assistência técnica'
                ]
            ],
            
            'maintenance_schedule' => [
                'frequency' => [
                    'weekly' => 'Inspeção visual básica',
                    'biweekly' => 'Verificação de pressão completa',
                    'monthly' => 'Calibragem e inspeção detalhada',
                    'seasonal' => 'Verificação completa incluindo alinhamento'
                ],
                'seasonal_adjustments' => [
                    'summer_heat' => 'Reduzir 1-2 PSI em dias muito quentes',
                    'winter_cold' => 'Adicionar 1-2 PSI em dias muito frios',
                    'temperature_rule' => 'Pressão varia ~1 PSI a cada 10°C'
                ]
            ],
            
            'troubleshooting' => [
                'common_problems' => [
                    'frequent_loss' => [
                        'symptom' => 'Pneu perde pressão rapidamente',
                        'causes' => ['Furo no pneu', 'Problema na válvula', 'Dano na roda'],
                        'solution' => 'Inspeção profissional imediata'
                    ],
                    'uneven_wear' => [
                        'symptom' => 'Desgaste irregular do pneu',
                        'causes' => ['Pressão incorreta', 'Desalinhamento', 'Suspensão'],
                        'solution' => 'Verificar pressão e alinhamento'
                    ],
                    'vibration' => [
                        'symptom' => 'Vibração no volante',
                        'causes' => ['Pressão desigual', 'Balanceamento', 'Deformação'],
                        'solution' => 'Calibragem e balanceamento'
                    ]
                ]
            ],
            
            'safety_considerations' => [
                'critical_safety' => [
                    'never_hot' => 'Nunca calibre com pneus aquecidos',
                    'spare_tire' => 'Mantenha sobressalente sempre calibrado',
                    'immediate_attention' => 'Investigue perda rápida de pressão',
                    'professional_help' => 'Procure ajuda para problemas complexos'
                ],
                'emergency_procedures' => [
                    'flat_tire' => 'Procedimento para pneu furado',
                    'pressure_loss' => 'Como agir com perda gradual',
                    'tpms_warning' => 'Resposta ao alerta do sistema'
                ]
            ],
            
            'cost_considerations' => [
                'maintenance_costs' => [
                    'calibrator_purchase' => 'R$ 50-200 para calibrador próprio',
                    'gas_station_service' => 'R$ 5-15 por calibragem',
                    'monthly_expense' => 'R$ 10-30 mensais para manutenção',
                    'tire_life_extension' => 'Até 30% mais vida útil com calibragem correta'
                ],
                'fuel_savings' => [
                    'proper_pressure' => 'Até 3% economia no combustível',
                    'yearly_savings' => 'R$ 200-500 anuais em combustível',
                    'performance_gains' => 'Melhor aceleração e frenagem'
                ]
            ],
            
            'perguntas_frequentes' => $this->generateCalibrationFAQ($vehicleData),
            
            'consideracoes_finais' => "A calibragem correta dos pneus do seu {$make} {$model} {$year} é uma responsabilidade que traz benefícios imediatos em segurança e economia. Seguindo este guia e mantendo uma rotina regular de verificação, você garante o melhor desempenho do seu veículo."
        ];
    }
    
    /**
     * Gerar seções separadas para refinamento Claude baseado no template
     */
    protected function generateSeparatedSections(array $vehicleData, string $templateType): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        $baseTitle = $templateType === 'ideal' ? 'Pressão Ideal' : 'Calibragem';
        
        return [
            'intro' => [
                'title' => "Introdução - {$baseTitle}",
                'content' => $templateType === 'ideal' 
                    ? "Descubra a pressão ideal para os pneus do seu {$make} {$model} {$year}."
                    : "Aprenda a calibrar corretamente os pneus do seu {$make} {$model} {$year}.",
                'target_tone' => 'educational_friendly',
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ],
            
            'pressure_table' => [
                'title' => $templateType === 'ideal' ? 'Pressões Ideais' : 'Tabela de Calibragem',
                'content' => [
                    'main_pressures' => $this->getMainPressures($vehicleData),
                    'usage_scenarios' => $this->getUsageScenarios($vehicleData)
                ],
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ],
            
            'how_to_calibrate' => [
                'title' => $templateType === 'ideal' ? 'Como Verificar' : 'Procedimento de Calibragem',
                'content' => $this->generateBasicCalibrationSteps($vehicleData, $templateType),
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ],
            
            'middle_content' => [
                'title' => 'Dicas e Alertas',
                'content' => $this->generateMiddleContent($vehicleData, $templateType),
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ],
            
            'faq' => [
                'title' => 'FAQ Personalizada',
                'content' => $templateType === 'ideal' 
                    ? $this->generateIdealFAQ($vehicleData)
                    : $this->generateCalibrationFAQ($vehicleData),
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ],
            
            'conclusion' => [
                'title' => 'Conclusão',
                'content' => $templateType === 'ideal'
                    ? "Mantenha sempre a pressão ideal no seu {$make} {$model} para máxima segurança!"
                    : "Calibre regularmente seu {$make} {$model} seguindo este guia completo!",
                'template_type' => $templateType,
                'status' => 'pending_refinement'
            ]
        ];
    }
    
    /**
     * Determinar template baseado no tipo de veículo e template type
     */
    public function getTemplateForVehicle(array $vehicleData, string $templateType = 'ideal'): string
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;
        
        if ($templateType === 'ideal') {
            return $isMotorcycle ? 'ideal_tire_pressure_motorcycle' : 'ideal_tire_pressure_car';
        } elseif ($templateType === 'calibration') {
            return $isMotorcycle ? 'tire_pressure_guide_motorcycle' : 'tire_pressure_guide_car';
        }
        
        return 'ideal_tire_pressure_car'; // Fallback
    }
    
    /**
     * Gerar título baseado no template type
     */
    protected function generateTitle(array $vehicleData, string $templateType): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        if ($templateType === 'ideal') {
            return "Pressão Ideal dos Pneus - {$make} {$model} {$year}";
        } elseif ($templateType === 'calibration') {
            return "Calibragem dos Pneus - {$make} {$model} {$year} | Guia Completo";
        }
        
        return "Pneus {$make} {$model} {$year}";
    }
    
    /**
     * Gerar slug baseado no template type
     */
    protected function generateSlug(array $vehicleData, string $templateType): string
    {
        $make = Str::slug($vehicleData['make']);
        $model = Str::slug($vehicleData['model']);
        $year = $vehicleData['year'];
        
        if ($templateType === 'ideal') {
            return "pressao-pneus-{$make}-{$model}-{$year}";
        } elseif ($templateType === 'calibration') {
            return "calibragem-pneu-{$make}-{$model}-{$year}";
        }
        
        return "pneu-{$make}-{$model}-{$year}";
    }
    
    /**
     * Gerar meta description baseado no template type
     */
    protected function generateMetaDescription(array $vehicleData, string $templateType): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $frontPressure = $vehicleData['pressure_empty_front'] ?? 30;
        $rearPressure = $vehicleData['pressure_empty_rear'] ?? 28;
        
        if ($templateType === 'ideal') {
            return "Pressão ideal dos pneus do {$make} {$model} {$year}: {$frontPressure}/{$rearPressure} PSI. Tabela completa, especificações e dicas para máxima economia.";
        } elseif ($templateType === 'calibration') {
            return "Como calibrar pneus do {$make} {$model} {$year}: guia passo a passo, equipamentos, TPMS e troubleshooting. Pressões: {$frontPressure}/{$rearPressure} PSI.";
        }
        
        return "Informações sobre pneus do {$make} {$model} {$year}.";
    }
    
    /**
     * Gerar palavras-chave SEO baseado no template type
     */
    protected function generateSeoKeywords(array $vehicleData, string $templateType): array
    {
        $make = strtolower($vehicleData['make']);
        $model = strtolower($vehicleData['model']);
        $year = $vehicleData['year'];
        
        $baseKeywords = [
            "pneu {$make} {$model} {$year}",
            "{$make} {$model}",
            "manutenção automotiva",
            "economia combustível",
            "segurança automotiva"
        ];
        
        if ($templateType === 'ideal') {
            return array_merge($baseKeywords, [
                "pressão ideal pneu {$make} {$model} {$year}",
                "pressão ideal pneu {$make}",
                "pressão pneu",
                "especificação pneu"
            ]);
        } elseif ($templateType === 'calibration') {
            return array_merge($baseKeywords, [
                "calibragem pneu {$make} {$model} {$year}",
                "como calibrar pneu {$make}",
                "calibragem pneu",
                "TPMS {$make}",
                "guia calibragem"
            ]);
        }
        
        return $baseKeywords;
    }
    
    // ===== MÉTODOS AUXILIARES ESPECÍFICOS POR TEMPLATE =====
    
    /**
     * Gerar FAQ para template IDEAL
     */
    protected function generateIdealFAQ(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $frontPressure = $vehicleData['pressure_empty_front'] ?? 30;
        $rearPressure = $vehicleData['pressure_empty_rear'] ?? 28;
        
        return [
            [
                'question' => "Qual a pressão ideal para o {$make} {$model} {$year}?",
                'answer' => "A pressão ideal é {$frontPressure} PSI nos pneus dianteiros e {$rearPressure} PSI nos traseiros para uso normal. Com carga total, aumente 4 PSI em cada pneu."
            ],
            [
                'question' => 'Por que manter a pressão ideal é importante?',
                'answer' => 'A pressão ideal garante máxima economia de combustível, maior vida útil dos pneus, melhor aderência e segurança na direção.'
            ],
            [
                'question' => 'Como saber se a pressão está ideal?',
                'answer' => 'Use um calibrador digital de qualidade e verifique sempre com pneus frios. Compare com os valores especificados pelo fabricante.'
            ],
            [
                'question' => 'A pressão ideal muda com a temperatura?',
                'answer' => 'Sim, a pressão varia aproximadamente 1 PSI para cada 10°C de diferença na temperatura ambiente.'
            ]
        ];
    }
    
    /**
     * Gerar FAQ para template CALIBRATION
     */
    protected function generateCalibrationFAQ(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        return [
            [
                'question' => "Como calibrar corretamente o {$make} {$model} {$year}?",
                'answer' => "Aguarde 3 horas após dirigir, use calibrador digital, verifique a pressão atual, ajuste conforme especificação e recoloque as tampas das válvulas."
            ],
            [
                'question' => 'Que tipo de calibrador devo usar?',
                'answer' => 'Prefira calibradores digitais com precisão de ±1 PSI. Evite calibradores muito baratos que podem ter medições imprecisas.'
            ],
            [
                'question' => 'Com que frequência devo calibrar?',
                'answer' => 'Verifique a cada 15 dias e sempre antes de viagens longas. Pneus perdem pressão naturalmente ao longo do tempo.'
            ],
            [
                'question' => 'O que fazer se o TPMS acender?',
                'answer' => 'Pare em local seguro, verifique visualmente os pneus, meça a pressão e calibre se necessário. Se persistir, procure assistência.'
            ],
            [
                'question' => 'Posso calibrar com pneus quentes?',
                'answer' => 'Não recomendado. Pneus aquecidos mostram pressão até 4 PSI superior ao real, levando a subcalibragem perigosa.'
            ]
        ];
    }
    
    /**
     * Gerar passos de calibragem baseado no template
     */
    protected function generateBasicCalibrationSteps(array $vehicleData, string $templateType): array
    {
        $make = $vehicleData['make'];
        
        if ($templateType === 'ideal') {
            return [
                'Verificar pressão sempre com pneus frios',
                "Localizar especificações do {$make} na porta do motorista",
                'Usar calibrador digital de qualidade',
                'Comparar com valores ideais recomendados',
                'Verificar também o pneu sobressalente',
                'Anotar data da verificação'
            ];
        } else {
            return [
                'Aguardar 3 horas após dirigir (pneus frios)',
                'Preparar calibrador digital e especificações',
                'Remover tampas das válvulas com cuidado',
                'Conectar calibrador e ler pressão atual',
                'Ajustar pressão conforme necessário',
                'Recolocar tampas e verificar sobressalente',
                'Testar sistema TPMS se disponível'
            ];
        }
    }
    
    /**
     * Gerar conteúdo do meio baseado no template
     */
    protected function generateMiddleContent(array $vehicleData, string $templateType): array
    {
        if ($templateType === 'ideal') {
            return [
                'benefits_tips' => [
                    'Economia de até 3% no combustível',
                    'Maior vida útil dos pneus',
                    'Melhor aderência e segurança',
                    'Menor desgaste da suspensão'
                ],
                'maintenance_tips' => [
                    'Verificar pressão quinzenalmente',
                    'Inspecionar desgaste regularmente',
                    'Manter sobressalente calibrado'
                ]
            ];
        } else {
            return [
                'equipment_tips' => [
                    'Calibrador digital é mais preciso',
                    'Evitar calibradores de posto sem manutenção',
                    'Ter calibrador próprio para emergências'
                ],
                'safety_tips' => [
                    'Nunca calibrar com pneus quentes',
                    'Verificar TPMS após calibragem',
                    'Investigar perda rápida de pressão'
                ]
            ];
        }
    }
    
    /**
     * Gerar conteúdo IDEAL para motocicletas
     */
    protected function generateIdealMotorcycleContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        return [
            'introducao' => "A pressão ideal dos pneus da sua {$make} {$model} {$year} é fundamental para segurança e performance. Em motocicletas, pequenas variações fazem grande diferença.",
            // ... estrutura específica para moto ideal
            'consideracoes_finais' => "Manter a pressão ideal na sua {$make} {$model} {$year} é questão de segurança vital. Verifique sempre antes de cada viagem."
        ];
    }
    
    /**
     * Gerar conteúdo CALIBRATION para motocicletas
     */
    protected function generateCalibrationMotorcycleContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        
        return [
            'introducao' => "Aprenda o procedimento correto para calibrar os pneus da sua {$make} {$model} {$year}. Segurança começa com calibragem adequada.",
            // ... estrutura específica para calibragem de moto
            'consideracoes_finais' => "A calibragem correta da {$make} {$model} {$year} deve ser feita com precisão e frequência. Sua vida depende disso."
        ];
    }
    
    // ===== MÉTODOS AUXILIARES GERAIS =====
    
    protected function generateDefaultUnitConversion(): array
    {
        return [
            'tabela_conversao' => [
                ['psi' => '28', 'bar' => '1.9', 'kgf_cm2' => '1.9'],
                ['psi' => '30', 'bar' => '2.1', 'kgf_cm2' => '2.1'],
                ['psi' => '32', 'bar' => '2.2', 'kgf_cm2' => '2.2'],
                ['psi' => '34', 'bar' => '2.3', 'kgf_cm2' => '2.3'],
                ['psi' => '36', 'bar' => '2.5', 'kgf_cm2' => '2.5']
            ],
            'observacao' => 'Conversão aproximada entre unidades de pressão.'
        ];
    }
    
    protected function generateLocationInfo(): array
    {
        return [
            'local_principal' => 'Soleira da porta do motorista',
            'local_alternativo' => 'Manual do proprietário',
            'informacoes_contidas' => [
                'Pressões para diferentes cargas',
                'Tamanho original dos pneus',
                'Pressão do sobressalente'
            ]
        ];
    }
    
    protected function generateBenefitsInfo(): array
    {
        return [
            'seguranca' => [
                'Maior aderência ao asfalto',
                'Melhor desempenho em frenagens',
                'Maior estabilidade em curvas'
            ],
            'economia' => [
                'Redução do consumo de combustível',
                'Maior vida útil dos pneus',
                'Menor desgaste da suspensão'
            ],
            'desempenho' => [
                'Melhor dirigibilidade',
                'Maior conforto ao dirigir',
                'Resposta mais precisa da direção'
            ]
        ];
    }
    
    protected function generateMaintenanceTips(): array
    {
        return [
            'frequencia_calibragem' => 'A cada 15 dias ou antes de viagens longas',
            'horario_ideal' => 'Pela manhã, com pneus frios',
            'equipamento_recomendado' => 'Calibrador digital de qualidade',
            'cuidados_especiais' => [
                'Verifique sempre o pneu sobressalente',
                'Use tampas nas válvulas',
                'Inspecione visualmente os pneus'
            ]
        ];
    }
    
    protected function generateImportantAlerts(): array
    {
        return [
            [
                'tipo' => 'warning',
                'titulo' => 'Nunca calibre com pneus quentes',
                'descricao' => 'Pneus aquecidos mostram pressão incorreta'
            ],
            [
                'tipo' => 'info',
                'titulo' => 'Verifique o sobressalente',
                'descricao' => 'Pneu sobressalente também perde pressão'
            ],
            [
                'tipo' => 'danger',
                'titulo' => 'Pressão baixa é perigosa',
                'descricao' => 'Pode causar aquecimento e estouro do pneu'
            ]
        ];
    }
    
    protected function getTPMSAvailability(array $vehicleData): string
    {
        $year = $vehicleData['year'] ?? 2020;
        return $year >= 2014 ? 'Disponível (obrigatório desde 2014)' : 'Não disponível';
    }
    
    protected function calculateLoadIndex(array $vehicleData): string
    {
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
        $year = $vehicleData['year'] ?? 2020;
        return $year >= 2018 ? 'H' : 'T';
    }
    
    protected function getOriginalTireBrand(array $vehicleData): string
    {
        $make = strtolower($vehicleData['make'] ?? '');
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
        $category = $vehicleData['main_category'] ?? 'hatch';
        $engineMap = [
            'hatch' => '1.0/1.6 Flex',
            'sedan' => '1.6/2.0 Flex',
            'suv' => '1.6/2.0 Flex',
            'pickup' => '2.8 Diesel'
        ];
        
        return $engineMap[strtolower($category)] ?? '1.6 Flex';
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
    
    protected function generateWordPressUrl(array $vehicleData, string $templateType): string
    {
        $slug = $this->generateSlug($vehicleData, $templateType);
        return "https://mercadoveiculos.com/info/{$slug}/";
    }
    
    protected function generateCanonicalUrl(array $vehicleData, string $templateType): string
    {
        return $this->generateWordPressUrl($vehicleData, $templateType);
    }
    
    /**
     * Calcular score de qualidade do conteúdo
     */
    public function calculateContentScore(array $content): float
    {
        $score = 5.0; // Base score
        
        // Verificar seções essenciais
        if (!empty($content['introducao'])) $score += 0.5;
        
        // Verificar seções específicas por template
        if (!empty($content['tabela_pressoes']) || !empty($content['pressure_table'])) $score += 1.0;
        if (!empty($content['perguntas_frequentes'])) $score += 0.5;
        if (!empty($content['consideracoes_finais'])) $score += 0.5;
        
        // Verificar qualidade específica
        if (!empty($content['calibration_procedure']) || !empty($content['especificacoes_pneus'])) $score += 0.5;
        if (!empty($content['tpms_system']) || !empty($content['beneficios_calibragem'])) $score += 0.5;
        
        return min(10.0, $score);
    }
}