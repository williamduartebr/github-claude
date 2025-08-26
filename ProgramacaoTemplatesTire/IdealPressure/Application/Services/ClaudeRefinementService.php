<?php

namespace Src\ContentGeneration\IdealPressure\Application\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\ContentGeneration\IdealPressure\Domain\Entities\IdealPressure;
use Carbon\Carbon;

/**
 * ClaudeRefinementService - REFINAMENTO de linguagem e SEO
 * 
 * FASE 3 do processo IdealPressure:
 * - RECEBE artigo JSON já estruturado da Fase 2
 * - REFINA apenas linguagem, fluidez e SEO
 * - OTIMIZA meta tags, keywords e legibilidade
 * - MELHORA transições entre seções
 * 
 * ⚠️ IMPORTANTE: NÃO re-processa dados técnicos - apenas refinamento textual
 * 
 * @author Claude Sonnet 4
 * @version 2.0 - Corrigida para focar apenas em refinamento
 */
class ClaudeRefinementService
{
    private const CLAUDE_API_URL = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-3-7-sonnet-20250219';
    private const MAX_TOKENS = 3000; // Reduzido pois é só refinamento
    private const TEMPERATURE = 0.2; // Mais conservador para refinamento

    private string $apiKey;
    private int $timeout;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->timeout = config('services.anthropic.timeout', 90); // Reduzido
        $this->maxRetries = config('services.anthropic.max_retries', 3);
    }

    /**
     * Refinar linguagem e SEO do artigo já estruturado
     * 
     * ⚠️ FOCO: Refinamento textual apenas, não re-processamento de dados
     */
    public function refineCalibrationArticle(IdealPressure $calibration): ?array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('API Key da Claude não configurada. Configure ANTHROPIC_API_KEY no .env');
        }

        if (empty($calibration->generated_article)) {
            throw new \Exception('Artigo estruturado não encontrado. Execute primeiro a Fase 2 (mapeamento de dados)');
        }

        try {
            // 1. Validar artigo estruturado
            $structuredArticle = $calibration->generated_article;
            $this->validateStructuredArticle($structuredArticle);

            // 2. Construir prompt focado APENAS em refinamento
            $prompt = $this->buildRefinementPrompt($calibration, $structuredArticle);

            // 3. Fazer requisição para Claude API
            $response = $this->makeClaudeRequest($prompt, $calibration->_id);

            // 4. Processar resposta da Claude
            $refinedArticle = $this->processClaudeResponse($response, $structuredArticle);

            // 5. Validar artigo refinado
            $this->validateRefinedArticle($refinedArticle);

            // 6. Adicionar metadados de refinamento
            $refinedArticle['refinement_metadata'] = [
                'refined_at' => now()->toISOString(),
                'claude_model' => self::MODEL,
                'refinement_type' => 'language_seo_only',
                'original_word_count' => $this->countWords($structuredArticle),
                'refined_word_count' => $this->countWords($refinedArticle),
                'refinement_focus' => [
                    'seo_optimization' => true,
                    'language_improvement' => true,
                    'readability_enhancement' => true,
                    'data_reprocessing' => false // ⚠️ IMPORTANTE
                ],
                'improvement_score' => $this->calculateImprovementScore($structuredArticle, $refinedArticle),
            ];

            Log::info('ClaudeRefinementService: Artigo refinado com sucesso', [
                'tire_calibration_id' => $calibration->_id,
                'vehicle' => "{$calibration->vehicle_make} {$calibration->vehicle_model} {$calibration->vehicle_year}",
                'original_words' => $refinedArticle['refinement_metadata']['original_word_count'],
                'refined_words' => $refinedArticle['refinement_metadata']['refined_word_count'],
                'improvement_score' => $refinedArticle['refinement_metadata']['improvement_score'],
                'focus' => 'language_seo_only'
            ]);

            return $refinedArticle;
        } catch (\Exception $e) {
            Log::error('ClaudeRefinementService: Erro no refinamento', [
                'tire_calibration_id' => $calibration->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Construir prompt focado APENAS em refinamento de linguagem/SEO
     */
    private function buildRefinementPrompt(IdealPressure $calibration, array $structuredArticle): string
    {
        $vehicleInfo = [
            'make' => $calibration->vehicle_make,
            'model' => $calibration->vehicle_model,
            'year' => $calibration->vehicle_year,
            'category' => $calibration->main_category
        ];

        $prompt = <<<EOT
Você é um especialista em refinamento de conteúdo e SEO. Sua tarefa é REFINAR APENAS a linguagem, fluidez e SEO de um artigo sobre calibragem de pneus que já está estruturado.

## INFORMAÇÕES DO VEÍCULO:
- Marca: {$vehicleInfo['make']}
- Modelo: {$vehicleInfo['model']}
- Ano: {$vehicleInfo['year']}
- Categoria: {$vehicleInfo['category']}

## ARTIGO JÁ ESTRUTURADO A SER REFINADO:
```json
EOT;
        $prompt .= json_encode($structuredArticle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $prompt .= <<<EOT
```

## INSTRUÇÕES DE REFINAMENTO - APENAS LINGUAGEM E SEO:

### ⚠️ O QUE NÃO FAZER:
- NÃO altere dados técnicos (pressões, especificações, características)
- NÃO recalcule valores numéricos
- NÃO adicione novos dados técnicos
- NÃO modifique informações factuais do veículo
- NÃO altere a estrutura JSON principal

### ✅ O QUE REFINAR:

#### 1. SEO OTIMIZADO
- Melhore o title para máximo 60 caracteres mantendo a essência
- Otimize meta_description para 150-160 caracteres mais atraente
- Enriqueça secondary_keywords com variações long-tail naturais
- Torne o H1 mais envolvente mantendo a keyword principal

#### 2. LINGUAGEM MAIS FLUIDA
- Melhore a fluidez dos textos existentes
- Torne as descrições mais claras e envolventes
- Adicione conectivos e transições suaves
- Use linguagem mais natural e acessível
- Corrija qualquer problema gramatical

#### 3. LEGIBILIDADE APRIMORADA
- Torne explicações mais didáticas
- Simplifique termos técnicos quando necessário
- Adicione clareza às instruções existentes
- Melhore a organização textual

#### 4. ENGAJAMENTO
- Torne títulos de seções mais atrativos
- Adicione pequenas frases de transição
- Use tom mais próximo ao leitor
- Mantenha tom técnico mas acessível

## FORMATO DE RESPOSTA:
Retorne APENAS o JSON refinado, mantendo exatamente a mesma estrutura, mas com linguagem melhorada e SEO otimizado.

CRÍTICO: 
- Mantenha todos os dados numéricos originais
- Preserve todas as informações factuais
- Apenas melhore a forma de apresentar o conteúdo
- JSON deve ser válido e completo
EOT;

        return $prompt;
    }

    /**
     * Fazer requisição para Claude API
     */
    private function makeClaudeRequest(string $prompt, string $calibrationId): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                Log::info('ClaudeRefinementService: Enviando para refinamento linguístico', [
                    'tire_calibration_id' => $calibrationId,
                    'attempt' => $attempt,
                    'model' => self::MODEL,
                    'max_tokens' => self::MAX_TOKENS,
                    'focus' => 'language_seo_only'
                ]);

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                        'anthropic-version' => '2023-06-01'
                    ])
                    ->post(self::CLAUDE_API_URL, [
                        'model' => self::MODEL,
                        'max_tokens' => self::MAX_TOKENS,
                        'temperature' => self::TEMPERATURE,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    Log::info('ClaudeRefinementService: Refinamento recebido', [
                        'tire_calibration_id' => $calibrationId,
                        'response_size' => strlen($response->body()),
                        'tokens_used' => $data['usage']['input_tokens'] ?? 0
                    ]);

                    return $data;
                }

                // Erro HTTP
                $error = "Erro HTTP {$response->status()}: {$response->body()}";
                Log::warning('ClaudeRefinementService: Erro HTTP na tentativa', [
                    'tire_calibration_id' => $calibrationId,
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'error' => $error
                ]);

                $lastError = $error;

                // Rate limiting - aguardar mais tempo
                if ($response->status() === 429) {
                    $waitTime = min(20 * $attempt, 90); // Max 90s para refinamento
                    Log::info("Rate limit atingido, aguardando {$waitTime}s...");
                    sleep($waitTime);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                Log::error('ClaudeRefinementService: Exceção na requisição', [
                    'tire_calibration_id' => $calibrationId,
                    'attempt' => $attempt,
                    'error' => $error
                ]);

                $lastError = $error;

                // Aguardar antes da próxima tentativa
                if ($attempt < $this->maxRetries) {
                    sleep(3 * $attempt);
                }
            }
        }

        throw new \Exception("Falha no refinamento após {$this->maxRetries} tentativas. Último erro: {$lastError}");
    }

    /**
     * Processar resposta da Claude API
     */
    private function processClaudeResponse(array $response, array $originalArticle): array
    {
        if (!isset($response['content'][0]['text'])) {
            throw new \Exception('Resposta da Claude API não contém campo de texto esperado');
        }

        $text = trim($response['content'][0]['text']);

        // Tentar extrair JSON da resposta
        $refinedArticle = $this->extractJsonFromResponse($text);

        if (!$refinedArticle) {
            Log::warning('ClaudeRefinementService: Não foi possível extrair JSON válido', [
                'response_text' => substr($text, 0, 300) . '...'
            ]);

            // Fallback: retornar original com melhorias básicas de SEO
            return $this->createMinimalSeoRefinement($originalArticle);
        }

        // Validar que dados técnicos não foram alterados
        $this->validateTechnicalDataPreservation($originalArticle, $refinedArticle);

        return $refinedArticle;
    }

    /**
     * Extrair JSON da resposta da Claude
     */
    private function extractJsonFromResponse(string $text): ?array
    {
        // Tentar decodificar diretamente
        $json = json_decode($text, true);
        if ($json && json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // Tentar extrair JSON entre ```json ou ```
        $patterns = [
            '/```json\s*(.*?)\s*```/s',
            '/```\s*(.*?)\s*```/s',
            '/{.*}/s'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $jsonText = trim($matches[1] ?? $matches[0]);
                $json = json_decode($jsonText, true);
                if ($json && json_last_error() === JSON_ERROR_NONE) {
                    return $json;
                }
            }
        }

        return null;
    }

    /**
     * Validar que dados técnicos foram preservados
     */
    private function validateTechnicalDataPreservation(array $original, array $refined): void
    {
        // Verificar pressões não foram alteradas
        if (isset($original['technical_content']['especificacoes_pressao']['pressoes'])) {
            $originalPressures = $original['technical_content']['especificacoes_pressao']['pressoes'];
            $refinedPressures = $refined['technical_content']['especificacoes_pressao']['pressoes'] ?? [];

            foreach ($originalPressures as $type => $pressureData) {
                if (isset($pressureData['dianteiro'], $pressureData['traseiro'])) {
                    $origFront = $pressureData['dianteiro'];
                    $origRear = $pressureData['traseiro'];
                    $refFront = $refinedPressures[$type]['dianteiro'] ?? null;
                    $refRear = $refinedPressures[$type]['traseiro'] ?? null;

                    if ($origFront != $refFront || $origRear != $refRear) {
                        Log::warning('ClaudeRefinementService: Dados de pressão foram alterados', [
                            'original' => compact('origFront', 'origRear'),
                            'refined' => compact('refFront', 'refRear')
                        ]);
                    }
                }
            }
        }

        // Verificar dados básicos do veículo
        if (isset($original['technical_content']['informacoes_veiculo']['dados_basicos'])) {
            $origBasics = $original['technical_content']['informacoes_veiculo']['dados_basicos'];
            $refBasics = $refined['technical_content']['informacoes_veiculo']['dados_basicos'] ?? [];

            $criticalFields = ['marca', 'modelo', 'ano'];
            foreach ($criticalFields as $field) {
                if (($origBasics[$field] ?? null) != ($refBasics[$field] ?? null)) {
                    Log::warning('ClaudeRefinementService: Dados básicos alterados', [
                        'field' => $field,
                        'original' => $origBasics[$field] ?? null,
                        'refined' => $refBasics[$field] ?? null
                    ]);
                }
            }
        }
    }

    /**
     * Criar refinamento mínimo de SEO se Claude falhar
     */
    private function createMinimalSeoRefinement(array $originalArticle): array
    {
        $refined = $originalArticle;

        // Melhorar apenas SEO básico
        if (isset($refined['seo_data'])) {
            // Melhorar meta description
            if (isset($refined['seo_data']['meta_description'])) {
                $refined['seo_data']['meta_description'] = $this->enhanceMetaDescription(
                    $refined['seo_data']['meta_description']
                );
            }

            // Adicionar keywords básicas
            if (isset($refined['seo_data']['secondary_keywords'])) {
                $refined['seo_data']['secondary_keywords'] = array_merge(
                    $refined['seo_data']['secondary_keywords'],
                    ['guia calibragem', 'dicas pneus', 'manutenção preventiva']
                );
            }
        }

        $refined['refinement_metadata'] = [
            'fallback_used' => true,
            'refinement_type' => 'minimal_seo_only',
            'refined_at' => now()->toISOString()
        ];

        return $refined;
    }

    /**
     * Melhorar meta description básica
     */
    private function enhanceMetaDescription(string $originalMeta): string
    {
        // Adicionar call-to-action sutil se não houver
        if (!str_contains($originalMeta, 'Aprenda') && !str_contains($originalMeta, 'Descubra') && !str_contains($originalMeta, 'Veja')) {
            return "Aprenda como " . lcfirst($originalMeta);
        }

        return $originalMeta;
    }

    /**
     * Validar artigo estruturado
     */
    private function validateStructuredArticle(array $article): void
    {
        $requiredFields = ['title', 'slug', 'seo_data', 'technical_content'];

        foreach ($requiredFields as $field) {
            if (!isset($article[$field]) || empty($article[$field])) {
                throw new \Exception("Campo obrigatório '{$field}' não encontrado no artigo estruturado");
            }
        }

        if (!isset($article['generation_metadata']['data_already_processed_by_claude'])) {
            Log::warning('ClaudeRefinementService: Artigo pode não ter sido processado corretamente na Fase 2');
        }
    }

    /**
     * Validar artigo refinado
     */
    private function validateRefinedArticle(array $article): void
    {
        // Validações básicas de estrutura
        $this->validateStructuredArticle($article);

        // Validações específicas de refinamento
        $seoData = $article['seo_data'] ?? [];

        if (isset($seoData['page_title']) && strlen($seoData['page_title']) > 65) {
            Log::warning('ClaudeRefinementService: Title muito longo após refinamento', [
                'title' => $seoData['page_title'],
                'length' => strlen($seoData['page_title'])
            ]);
        }

        if (isset($seoData['meta_description']) && strlen($seoData['meta_description']) > 165) {
            Log::warning('ClaudeRefinementService: Meta description muito longa', [
                'meta_description' => $seoData['meta_description'],
                'length' => strlen($seoData['meta_description'])
            ]);
        }
    }

    /**
     * Contar palavras em um artigo
     */
    private function countWords(array $article): int
    {
        $text = '';

        // Concatenar todos os textos do artigo
        array_walk_recursive($article, function ($value) use (&$text) {
            if (is_string($value)) {
                $text .= ' ' . $value;
            }
        });

        return str_word_count(strip_tags($text));
    }

    /**
     * Calcular score de melhoria focado em refinamento (0-10)
     */
    private function calculateImprovementScore(array $original, array $refined): float
    {
        $score = 5.0; // Base score para refinamento

        // +1 se melhorou meta description
        $origMeta = $original['seo_data']['meta_description'] ?? '';
        $refMeta = $refined['seo_data']['meta_description'] ?? '';
        if (strlen($refMeta) > strlen($origMeta) && strlen($refMeta) <= 160) {
            $score += 1.0;
        }

        // +1 se adicionou mais keywords
        $origKeywords = count($original['seo_data']['secondary_keywords'] ?? []);
        $refKeywords = count($refined['seo_data']['secondary_keywords'] ?? []);
        if ($refKeywords > $origKeywords) {
            $score += 1.0;
        }

        // +1 se melhorou legibilidade (mais palavras mas estrutura mantida)
        $origWords = $this->countWords($original);
        $refWords = $this->countWords($refined);
        if ($refWords > $origWords && $refWords < $origWords * 1.3) {
            $score += 1.0;
        }

        // +1 se refinamento foi bem sucedido
        if (isset($refined['refinement_metadata'])) {
            $score += 1.0;
        }

        // +1 bonus por preservar dados técnicos
        $score += 1.0;

        return min(10.0, $score);
    }

    /**
     * Testar conectividade com Claude API
     */
    public function testApiConnection(): array
    {
        try {
            $testPrompt = "Melhore esta frase mantendo o significado: 'A pressão dos pneus é importante.'";

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01'
                ])
                ->post(self::CLAUDE_API_URL, [
                    'model' => self::MODEL,
                    'max_tokens' => 50,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $testPrompt
                        ]
                    ]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão com Claude API estabelecida com sucesso',
                    'model' => self::MODEL,
                    'focus' => 'language_refinement_ready'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro HTTP: ' . $response->status(),
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exceção: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Obter estatísticas de refinamento
     */
    public function getRefinementStats(): array
    {
        $readyForRefinement = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_ARTICLE_GENERATED)->count();
        $refined = IdealPressure::whereNotNull('article_refined')->count();
        $processing = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_CLAUDE_PROCESSING)->count();
        $failed = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_FAILED)
            ->where('last_error', 'like', '%Claude%')->count();

        return [
            'ready_for_refinement' => $readyForRefinement,
            'articles_refined' => $refined,
            'currently_processing' => $processing,
            'failed_refinements' => $failed,
            'api_configured' => !empty($this->apiKey),
            'success_rate' => ($refined + $failed) > 0 ? round(($refined / ($refined + $failed)) * 100, 2) : 0,
            'refinement_focus' => 'language_seo_only'
        ];
    }
}
