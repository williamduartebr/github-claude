<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * FIXED InitialArticleGeneratorService - CORREÇÃO DO ERRO vehicle_identifier
 * 
 * PROBLEMA CORRIGIDO:
 * ✅ Removido acesso a 'vehicle_identifier' inexistente
 * ✅ Criado identificador a partir de make/model/year
 * ✅ Validação robusta de dados de entrada
 * ✅ Tratamento de valores padrão para campos ausentes
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
            // ✅ CORREÇÃO: Criar vehicle_identifier a partir dos dados disponíveis
            $vehicleIdentifier = $this->createVehicleIdentifier($vehicleData);

            Log::debug("🚀 Iniciando geração de artigo", [
                'vehicle' => $vehicleIdentifier,
                'template_type' => $templateType,
                'batch_id' => $batchId
            ]);

            // 1. Validar template type
            if (!in_array($templateType, ['ideal', 'calibration'])) {
                throw new \Exception("Template type inválido: {$templateType}. Use 'ideal' ou 'calibration'");
            }

            // 2. Validar dados mínimos do veículo
            $this->validateVehicleData($vehicleData, $vehicleIdentifier);

            // 3. Enriquecer dados com defaults se necessário
            $vehicleData = $this->enrichVehicleDataWithDefaults($vehicleData);

            // 4. Gerar conteúdo estruturado baseado no template type
            $structuredContent = $this->generateStructuredContent($vehicleData, $templateType);

            // 5. Gerar seções separadas para refinamento Claude
            $separatedSections = $this->generateSeparatedSections($vehicleData, $templateType);

            // 6. Criar artigo na base de dados
            $article = new TirePressureArticle();

            // Dados básicos do veículo
            $article->make = $vehicleData['make'];
            $article->model = $vehicleData['model'];
            $article->year = $vehicleData['year'];
            $article->tire_size = $vehicleData['tire_size'];
            $article->vehicle_data = $vehicleData;

            // Template type para diferenciar artigos
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
            $article->pressure_light_front = $vehicleData['pressure_light_front'] ?? 30.0;
            $article->pressure_light_rear = $vehicleData['pressure_light_rear'] ?? 28.0;
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

                Log::info("✅ Artigo gerado com sucesso", [
                    'vehicle' => $vehicleIdentifier,
                    'template_type' => $templateType,
                    'template_used' => $article->template_used,
                    'content_score' => $article->content_score,
                    'slug' => $article->slug,
                    'article_id' => $article->_id
                ]);

                return $article;
            } else {
                Log::error("❌ Falha ao salvar artigo no banco", [
                    'vehicle' => $vehicleIdentifier,
                    'template_type' => $templateType
                ]);
                return null;
            }
        } catch (\Exception $e) {
            $vehicleIdentifier = $this->createVehicleIdentifier($vehicleData);

            Log::error("❌ Erro ao gerar artigo", [
                'vehicle' => $vehicleIdentifier,
                'template_type' => $templateType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * ✅ NOVO: Criar identificador do veículo a partir dos dados disponíveis
     */
    protected function createVehicleIdentifier(array $vehicleData): string
    {
        $make = $vehicleData['make'] ?? 'Unknown';
        $model = $vehicleData['model'] ?? 'Unknown';
        $year = $vehicleData['year'] ?? 'Unknown';

        return "{$make} {$model} {$year}";
    }

    /**
     * ✅ NOVO: Validar dados mínimos do veículo
     */
    protected function validateVehicleData(array $vehicleData, string $vehicleIdentifier): void
    {
        $requiredFields = ['make', 'model'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($vehicleData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \Exception("Campos obrigatórios ausentes para {$vehicleIdentifier}: " . implode(', ', $missingFields));
        }
    }

    /**
     * ✅ NOVO: Enriquecer dados com valores padrão se necessário
     */
    protected function enrichVehicleDataWithDefaults(array $vehicleData): array
    {
        $defaults = [
            'year' => 2020,
            'tire_size' => '185/65 R15',
            'pressure_light_front' => 30.0,
            'pressure_light_rear' => 28.0,
            'pressure_empty_front' => 30,
            'pressure_empty_rear' => 28,
            'pressure_max_front' => 36,
            'pressure_max_rear' => 34,
            'pressure_spare' => 32.0,
            'main_category' => 'hatchbacks',
            'is_motorcycle' => false,
            'vehicle_type' => 'car'
        ];

        foreach ($defaults as $field => $defaultValue) {
            if (!isset($vehicleData[$field]) || empty($vehicleData[$field])) {
                $vehicleData[$field] = $defaultValue;
            }
        }

        return $vehicleData;
    }

    /**
     * Gerar conteúdo estruturado baseado no template
     */
    protected function generateStructuredContent(array $vehicleData, string $templateType): array
    {
        $vehicleIdentifier = $this->createVehicleIdentifier($vehicleData);

        if ($templateType === 'ideal') {
            return $this->generateIdealPressureContent($vehicleData);
        } else {
            return $this->generateCalibrationContent($vehicleData);
        }
    }

    /**
     * Gerar conteúdo para template "ideal"
     */
    protected function generateIdealPressureContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];
        $tireSize = $vehicleData['tire_size'];

        return [
            'introduction' => [
                'title' => "Pressão Ideal dos Pneus do {$make} {$model} {$year}",
                'content' => "Descubra a pressão ideal dos pneus para o seu {$make} {$model} {$year} e mantenha seu veículo sempre seguro e econômico.",
                'key_points' => [
                    "Especificações originais de fábrica",
                    "Pressão recomendada para diferentes condições",
                    "Dicas de manutenção preventiva"
                ]
            ],
            'pressure_specifications' => [
                'tire_size' => $tireSize,
                'front_pressure' => $vehicleData['pressure_light_front'] ?? 30,
                'rear_pressure' => $vehicleData['pressure_light_rear'] ?? 28,
                'spare_pressure' => $vehicleData['pressure_spare'] ?? 32,
                'conditions' => 'Veículo com carga normal'
            ],
            'benefits' => [
                'safety' => 'Maior segurança e estabilidade',
                'economy' => 'Redução no consumo de combustível',
                'durability' => 'Maior vida útil dos pneus',
                'performance' => 'Melhor performance de direção'
            ],
            'maintenance_tips' => [
                'frequency' => 'Verificar pressão semanalmente',
                'temperature' => 'Medir com pneus frios',
                'tools' => 'Usar calibrador de qualidade',
                'inspection' => 'Verificar desgaste regularmente'
            ]
        ];
    }

    /**
     * Gerar conteúdo para template "calibration"
     */
    protected function generateCalibrationContent(array $vehicleData): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        return [
            'guide_introduction' => [
                'title' => "Como Calibrar os Pneus do {$make} {$model} {$year}",
                'content' => "Guia completo passo a passo para calibrar corretamente os pneus do seu {$make} {$model} {$year}.",
                'preparation' => [
                    "Pneus frios (veículo parado por pelo menos 3 horas)",
                    "Calibrador de pneus confiável",
                    "Manual do proprietário para referência"
                ]
            ],
            'step_by_step' => [
                'step_1' => [
                    'title' => 'Preparação',
                    'description' => 'Estacione em local seguro e aguarde os pneus esfriarem',
                    'time' => '5 minutos'
                ],
                'step_2' => [
                    'title' => 'Verificação',
                    'description' => 'Remova a tampa da válvula e verifique a pressão atual',
                    'time' => '2 minutos por pneu'
                ],
                'step_3' => [
                    'title' => 'Calibragem',
                    'description' => 'Ajuste para a pressão recomendada pelo fabricante',
                    'time' => '3 minutos por pneu'
                ],
                'step_4' => [
                    'title' => 'Verificação Final',
                    'description' => 'Confirme as pressões e recoloque as tampas',
                    'time' => '2 minutos'
                ]
            ],
            'pressure_values' => [
                'empty_load' => [
                    'front' => $vehicleData['pressure_empty_front'] ?? 30,
                    'rear' => $vehicleData['pressure_empty_rear'] ?? 28
                ],
                'full_load' => [
                    'front' => $vehicleData['pressure_max_front'] ?? 36,
                    'rear' => $vehicleData['pressure_max_rear'] ?? 34
                ],
                'spare' => $vehicleData['pressure_spare'] ?? 32
            ],
            'common_mistakes' => [
                'hot_tires' => 'Calibrar com pneus quentes',
                'incorrect_pressure' => 'Usar pressão incorreta para a carga',
                'poor_equipment' => 'Usar calibrador descalibrado',
                'irregular_check' => 'Não verificar regularmente'
            ]
        ];
    }

    /**
     * Gerar seções separadas para refinamento Claude
     */
    protected function generateSeparatedSections(array $vehicleData, string $templateType): array
    {
        $vehicleIdentifier = $this->createVehicleIdentifier($vehicleData);

        $sections = [
            'intro' => $this->generateIntroSection($vehicleData, $templateType),
            'pressure_table' => $this->generatePressureTableSection($vehicleData, $templateType),
            'how_to_calibrate' => $this->generateHowToCalibrateSection($vehicleData, $templateType),
            'middle_content' => $this->generateMiddleContentSection($vehicleData, $templateType),
            'faq' => $this->generateFaqSection($vehicleData, $templateType),
            'conclusion' => $this->generateConclusionSection($vehicleData, $templateType)
        ];

        return $sections;
    }

    /**
     * Gerar seção de introdução
     */
    protected function generateIntroSection(array $vehicleData, string $templateType): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        if ($templateType === 'ideal') {
            return [
                'content' => "A pressão ideal dos pneus do {$make} {$model} {$year} é fundamental para garantir segurança, economia de combustível e durabilidade dos pneus. Este guia apresenta as especificações exatas recomendadas pelo fabricante.",
                'keywords' => ['pressão ideal', 'pneus', $make, $model, $year],
                'tone' => 'informativo',
                'length' => 'medium'
            ];
        } else {
            return [
                'content' => "Aprender a calibrar corretamente os pneus do seu {$make} {$model} {$year} é uma habilidade essencial para todo motorista. Este guia passo a passo mostra como fazer a calibragem de forma segura e eficiente.",
                'keywords' => ['calibrar pneus', 'como calibrar', $make, $model, $year],
                'tone' => 'didático',
                'length' => 'medium'
            ];
        }
    }

    /**
     * Gerar seção de tabela de pressões
     */
    protected function generatePressureTableSection(array $vehicleData, string $templateType): array
    {
        return [
            'table_data' => [
                'tire_size' => $vehicleData['tire_size'],
                'pressures' => [
                    'front_empty' => $vehicleData['pressure_empty_front'] ?? 30,
                    'rear_empty' => $vehicleData['pressure_empty_rear'] ?? 28,
                    'front_loaded' => $vehicleData['pressure_max_front'] ?? 36,
                    'rear_loaded' => $vehicleData['pressure_max_rear'] ?? 34,
                    'spare' => $vehicleData['pressure_spare'] ?? 32
                ]
            ],
            'table_format' => 'responsive',
            'units' => 'PSI',
            'note' => 'Pressões medidas com pneus frios'
        ];
    }

    /**
     * Gerar seção como calibrar
     */
    protected function generateHowToCalibrateSection(array $vehicleData, string $templateType): array
    {
        return [
            'steps' => [
                '1' => 'Estacione o veículo em local seguro e plano',
                '2' => 'Aguarde pelo menos 3 horas para os pneus esfriarem',
                '3' => 'Remova a tampa da válvula do pneu',
                '4' => 'Conecte o calibrador e verifique a pressão atual',
                '5' => 'Ajuste para a pressão recomendada',
                '6' => 'Recoloque a tampa da válvula',
                '7' => 'Repita o processo para todos os pneus'
            ],
            'tools_needed' => ['Calibrador de pneus', 'Compressor (se necessário)'],
            'time_required' => '15-20 minutos',
            'difficulty' => 'Fácil'
        ];
    }

    /**
     * Gerar seção de conteúdo meio
     */
    protected function generateMiddleContentSection(array $vehicleData, string $templateType): array
    {
        return [
            'tips' => [
                'Verifique a pressão dos pneus semanalmente',
                'Sempre meça com pneus frios',
                'Não esqueça do estepe',
                'Use equipamentos calibrados'
            ],
            'warnings' => [
                'Pneus com pressão baixa podem causar acidentes',
                'Pressão alta demais reduz a aderência',
                'Verificação irregular compromete a segurança'
            ],
            'maintenance_checklist' => [
                'Pressão dos pneus',
                'Desgaste da banda de rodagem',
                'Alinhamento e balanceamento',
                'Rotação dos pneus'
            ]
        ];
    }

    /**
     * Gerar seção FAQ
     */
    protected function generateFaqSection(array $vehicleData, string $templateType): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];

        return [
            'questions' => [
                [
                    'question' => "Com que frequência devo verificar a pressão dos pneus do {$make} {$model}?",
                    'answer' => 'Recomenda-se verificar semanalmente ou antes de viagens longas.'
                ],
                [
                    'question' => 'Posso calibrar com pneus quentes?',
                    'answer' => 'Não é recomendado. Sempre calibre com pneus frios para maior precisão.'
                ],
                [
                    'question' => 'O que acontece se usar pressão incorreta?',
                    'answer' => 'Pode causar desgaste irregular, maior consumo de combustível e comprometer a segurança.'
                ]
            ],
            'category' => 'manutenção_preventiva'
        ];
    }

    /**
     * Gerar seção de conclusão
     */
    protected function generateConclusionSection(array $vehicleData, string $templateType): array
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];

        return [
            'summary' => "Manter a pressão correta dos pneus do seu {$make} {$model} é fundamental para sua segurança e economia.",
            'call_to_action' => [
                'primary' => 'Verifique agora a pressão dos seus pneus',
                'secondary' => 'Consulte sempre o manual do proprietário'
            ],
            'related_topics' => [
                'Balanceamento de rodas',
                'Alinhamento de direção',
                'Rotação de pneus'
            ]
        ];
    }

    // Métodos auxiliares (simplificados)
    protected function generateTitle(array $vehicleData, string $templateType): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        if ($templateType === 'ideal') {
            return "Pressão Ideal dos Pneus {$make} {$model} {$year} - Guia Completo";
        } else {
            return "Como Calibrar Pneus {$make} {$model} {$year} - Passo a Passo";
        }
    }

    protected function generateSlug(array $vehicleData, string $templateType): string
    {
        $make = Str::slug($vehicleData['make']);
        $model = Str::slug($vehicleData['model']);
        $year = $vehicleData['year'];

        if ($templateType === 'ideal') {
            return "pressao-pneus-{$make}-{$model}-{$year}";
        } else {
            return "como-calibrar-pneus-{$make}-{$model}-{$year}";
        }
    }

    protected function generateMetaDescription(array $vehicleData, string $templateType): string
    {
        $make = $vehicleData['make'];
        $model = $vehicleData['model'];
        $year = $vehicleData['year'];

        if ($templateType === 'ideal') {
            return "Descubra a pressão ideal dos pneus do {$make} {$model} {$year}. Especificações do fabricante, dicas de manutenção e muito mais.";
        } else {
            return "Aprenda como calibrar os pneus do {$make} {$model} {$year} com nosso guia passo a passo. Dicas profissionais e segurança garantida.";
        }
    }

    protected function generateSeoKeywords(array $vehicleData, string $templateType): array
    {
        $make = strtolower($vehicleData['make']);
        $model = strtolower($vehicleData['model']);
        $year = $vehicleData['year'];

        if ($templateType === 'ideal') {
            return [
                "pressão ideal pneus {$make} {$model}",
                "pneus {$make} {$model} {$year}",
                "pressão recomendada {$make}",
                "calibragem {$make} {$model}"
            ];
        } else {
            return [
                "como calibrar pneus {$make} {$model}",
                "calibragem {$make} {$model} {$year}",
                "passo a passo calibrar pneus",
                "tutorial calibragem {$make}"
            ];
        }
    }

    protected function getTemplateForVehicle(array $vehicleData, string $templateType): string
    {
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;

        if ($isMotorcycle) {
            return $templateType === 'ideal' ? 'IdealTirePressureMotorcycleViewModel' : 'TirePressureGuideMotorcycleViewModel';
        } else {
            return $templateType === 'ideal' ? 'IdealTirePressureCarViewModel' : 'TirePressureGuideCarViewModel';
        }
    }

    protected function generateWordPressUrl(array $vehicleData, string $templateType): string
    {
        $slug = $this->generateSlug($vehicleData, $templateType);
        if ($templateType === 'ideal') {
            return $slug;
        }

        $slug = str_replace('como-calibrar-pneus', 'calibragem-pneu', $slug);
        return $slug;
    }

    protected function generateCanonicalUrl(array $vehicleData, string $templateType): string
    {
        return $this->generateWordPressUrl($vehicleData, $templateType);
    }

    protected function calculateContentScore(array $content): float
    {
        // Cálculo básico baseado na completude do conteúdo
        $score = 6.0; // Base

        if (!empty($content)) {
            $score += 1.0; // Conteúdo presente
        }

        return min(10.0, $score);
    }
}
