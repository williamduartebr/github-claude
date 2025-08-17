<?php

namespace Src\ContentGeneration\TirePressureGuide\Application\Services;

use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\Claude35SonnetService;

/**
 * Service para refinamento das 6 seções usando Claude 3.5 Sonnet
 * 
 * Responsável por:
 * - Construir prompts específicos por template
 * - Chamar Claude API
 * - Parsear respostas
 * - Validar conteúdo gerado
 */
class SectionRefinementService
{
    private Claude35SonnetService $claudeService;
    
    // Templates de estrutura esperada
    private const SECTION_STRUCTURES = [
        'ideal' => [
            'intro' => ['title', 'content', 'key_points'],
            'pressure_table' => ['specifications', 'conditions', 'notes'],
            'how_to_calibrate' => ['steps', 'tools_needed', 'time_required'],
            'middle_content' => ['benefits', 'warnings', 'maintenance_tips'],
            'faq' => ['questions'],
            'conclusion' => ['summary', 'call_to_action', 'related_links']
        ],
        'calibration' => [
            'intro' => ['title', 'content', 'preparation'],
            'pressure_table' => ['values', 'conditions', 'conversions'],
            'how_to_calibrate' => ['step_by_step', 'common_mistakes', 'pro_tips'],
            'middle_content' => ['equipment_guide', 'tpms_info', 'troubleshooting'],
            'faq' => ['questions'],
            'conclusion' => ['summary', 'next_steps', 'resources']
        ]
    ];

    public function __construct(Claude35SonnetService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    /**
     * Refinar todas as 6 seções de um artigo
     */
    public function refineArticleSections(TirePressureArticle $article): bool
    {
        try {
            Log::info("Iniciando refinamento de seções", [
                'article_id' => $article->_id,
                'vehicle' => $article->vehicle_data['vehicle_full_name'] ?? 'N/A',
                'template' => $article->template_type
            ]);

            // Marcar como em processamento
            $article->markAsProcessing();

            // Construir prompt baseado no template
            $prompt = $this->buildRefinementPrompt($article);

            // Chamar Claude API
            $response = $this->claudeService->generateContent($prompt, [
                'max_tokens' => 4000,
                'temperature' => 0.3,
                'model' => 'claude-3-5-sonnet-20240620'
            ]);

            if (!$response || !isset($response['content'])) {
                throw new \Exception("Resposta vazia da Claude API");
            }

            // Parsear resposta
            $sections = $this->parseClaudeResponse($response['content']);

            // Validar seções
            $validation = $this->validateSections($sections, $article->template_type);
            if (!$validation['valid']) {
                throw new \Exception("Validação falhou: " . implode(', ', $validation['errors']));
            }

            // Enriquecer seções com metadados
            $enrichedSections = $this->enrichSections($sections, $article);

            // Salvar seções no artigo
            $success = $article->updateAllSections($enrichedSections);

            if ($success) {
                // Calcular métricas de API
                $apiMetrics = [
                    'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                    'cost_estimate' => ($response['usage']['total_tokens'] ?? 0) * 0.00001 // ~$0.01 per 1K tokens
                ];

                // Marcar como refinado
                $article->markAsRefined($apiMetrics);

                // Calcular score de qualidade
                $qualityScore = $article->calculateSectionsQualityScore();

                Log::info("Refinamento concluído com sucesso", [
                    'article_id' => $article->_id,
                    'quality_score' => $qualityScore,
                    'tokens_used' => $apiMetrics['tokens_used']
                ]);

                return true;
            }

            throw new \Exception("Falha ao salvar seções");

        } catch (\Exception $e) {
            Log::error("Erro no refinamento", [
                'article_id' => $article->_id,
                'error' => $e->getMessage()
            ]);

            $article->markAsFailedRefinement($e->getMessage());
            return false;
        }
    }

    /**
     * Construir prompt para refinamento
     */
    private function buildRefinementPrompt(TirePressureArticle $article): string
    {
        $vehicleData = $article->vehicle_data;
        $template = $article->template_type;
        
        // Contexto específico por template
        $templateContext = $template === 'ideal' 
            ? $this->getIdealTemplateContext()
            : $this->getCalibrationTemplateContext();

        $prompt = "Você é um especialista em manutenção automotiva criando conteúdo sobre calibragem de pneus.

DADOS DO VEÍCULO:
- Nome completo: {$vehicleData['vehicle_full_name']}
- Categoria: {$vehicleData['category_normalized']}
- Pressão recomendada: {$vehicleData['pressure_display']}
- Pressão vazio: {$vehicleData['empty_pressure_display']}
- Pressão carregado: {$vehicleData['loaded_pressure_display']}
- Tamanho do pneu: {$vehicleData['tire_size']}
- Tem TPMS: " . ($vehicleData['has_tpms'] ? 'Sim' : 'Não') . "
- Veículo Premium: " . ($vehicleData['is_premium'] ? 'Sim' : 'Não') . "

TIPO DE ARTIGO: {$templateContext['focus']}

TAREFA: Gere conteúdo para as 6 seções abaixo, seguindo a estrutura JSON especificada.

SEÇÕES A GERAR:

1. sections_intro - {$templateContext['intro_guidance']}
   Estrutura: {title, content, key_points[]}

2. sections_pressure_table - {$templateContext['table_guidance']}
   Estrutura: {specifications{}, conditions[], notes[]}

3. sections_how_to_calibrate - {$templateContext['calibrate_guidance']}
   Estrutura: {steps[], tools_needed[], time_required}

4. sections_middle_content - {$templateContext['middle_guidance']}
   Estrutura: {benefits[], warnings[], maintenance_tips[]}

5. sections_faq - {$templateContext['faq_guidance']}
   Estrutura: {questions[{question, answer}]}

6. sections_conclusion - {$templateContext['conclusion_guidance']}
   Estrutura: {summary, call_to_action, related_links[]}

DIRETRIZES:
- Conteúdo específico para o {$vehicleData['vehicle_full_name']}
- Tom profissional mas acessível
- Informações práticas e acionáveis
- Incluir especificidades do veículo quando relevante
- FAQ com 5-7 perguntas relevantes
- Conclusão motivadora com CTA claro

RETORNE APENAS UM JSON VÁLIDO com as 6 seções:
{
  \"intro\": {...},
  \"pressure_table\": {...},
  \"how_to_calibrate\": {...},
  \"middle_content\": {...},
  \"faq\": {...},
  \"conclusion\": {...}
}";

        return $prompt;
    }

    /**
     * Contexto para template "ideal"
     */
    private function getIdealTemplateContext(): array
    {
        return [
            'focus' => 'Pressão Ideal dos Pneus',
            'intro_guidance' => 'Introdução focada na importância da pressão correta',
            'table_guidance' => 'Tabela técnica com especificações detalhadas',
            'calibrate_guidance' => 'Instruções básicas de verificação',
            'middle_guidance' => 'Benefícios da pressão correta, avisos de segurança',
            'faq_guidance' => 'Perguntas sobre pressão ideal, quando verificar, etc',
            'conclusion_guidance' => 'Reforçar importância e próximos passos'
        ];
    }

    /**
     * Contexto para template "calibration"
     */
    private function getCalibrationTemplateContext(): array
    {
        return [
            'focus' => 'Como Calibrar os Pneus',
            'intro_guidance' => 'Introdução focada no processo de calibragem',
            'table_guidance' => 'Valores de referência e conversões',
            'calibrate_guidance' => 'Passo a passo detalhado do processo',
            'middle_guidance' => 'Equipamentos, TPMS, solução de problemas',
            'faq_guidance' => 'Perguntas sobre ferramentas, frequência, erros comuns',
            'conclusion_guidance' => 'Resumo do processo e dicas finais'
        ];
    }

    /**
     * Parsear resposta do Claude
     */
    private function parseClaudeResponse(string $content): array
    {
        // Extrair JSON da resposta
        $jsonMatch = [];
        preg_match('/\{[\s\S]*\}/', $content, $jsonMatch);
        
        if (empty($jsonMatch)) {
            throw new \Exception("Não foi possível extrair JSON da resposta");
        }

        $json = $jsonMatch[0];
        $sections = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON: " . json_last_error_msg());
        }

        return $sections;
    }

    /**
     * Validar seções geradas
     */
    private function validateSections(array $sections, string $templateType): array
    {
        $errors = [];
        $requiredSections = ['intro', 'pressure_table', 'how_to_calibrate', 'middle_content', 'faq', 'conclusion'];

        // Verificar presença das seções
        foreach ($requiredSections as $section) {
            if (!isset($sections[$section]) || empty($sections[$section])) {
                $errors[] = "Seção '{$section}' ausente ou vazia";
            }
        }

        // Validar estrutura específica
        if (isset($sections['faq']['questions'])) {
            $questionCount = count($sections['faq']['questions']);
            if ($questionCount < 3) {
                $errors[] = "FAQ deve ter pelo menos 3 perguntas (encontradas: {$questionCount})";
            }
        }

        // Validar conteúdo mínimo
        foreach ($sections as $key => $section) {
            if (is_array($section)) {
                $content = json_encode($section);
                if (strlen($content) < 100) {
                    $errors[] = "Seção '{$key}' muito curta";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Enriquecer seções com metadados
     */
    private function enrichSections(array $sections, TirePressureArticle $article): array
    {
        $enriched = [];

        foreach ($sections as $key => $content) {
            $enriched[$key] = [
                'content' => $content,
                'metadata' => [
                    'generated_at' => now()->toISOString(),
                    'template_type' => $article->template_type,
                    'vehicle' => $article->vehicle_data['vehicle_full_name'] ?? 'N/A',
                    'word_count' => str_word_count(json_encode($content))
                ]
            ];
        }

        return $enriched;
    }

    /**
     * Processar batch de artigos
     */
    public function processBatch(string $batchId, int $limit = 10): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // Buscar artigos do batch
            $articles = TirePressureArticle::inBatch($batchId)
                                          ->pendingRefinement()
                                          ->limit($limit)
                                          ->get();

            if ($articles->isEmpty()) {
                Log::info("Nenhum artigo pendente no batch {$batchId}");
                return $results;
            }

            foreach ($articles as $article) {
                $results['processed']++;

                // Rate limiting: aguardar 60 segundos entre requisições
                if ($results['processed'] > 1) {
                    Log::info("Rate limiting: aguardando 60 segundos...");
                    sleep(60);
                }

                // Processar artigo
                $success = $this->refineArticleSections($article);

                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'article_id' => $article->_id,
                        'vehicle' => $article->vehicle_data['vehicle_full_name'] ?? 'N/A'
                    ];
                }

                // Log de progresso
                Log::info("Progresso do batch", [
                    'batch_id' => $batchId,
                    'processed' => $results['processed'],
                    'success' => $results['success'],
                    'failed' => $results['failed']
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Erro ao processar batch", [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }
}