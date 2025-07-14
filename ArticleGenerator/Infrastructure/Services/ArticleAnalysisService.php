<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class ArticleAnalysisService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * Analisa um artigo em busca de problemas de pontuação na introdução
     */
    public function analyzeArticlePunctuation($articleSlug, $forceReanalyze = false)
    {
        try {
            $article = Article::where('slug', $articleSlug)->first();

            if (!$article) {
                throw new \Exception("Artigo não encontrado: {$articleSlug}");
            }

            // Verificar se já existe análise recente (últimos 3 dias) se não forçar reanálise
            if (!$forceReanalyze) {
                $recentAnalysis = ArticleCorrection::where('article_slug', $articleSlug)
                    ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                    ->where('created_at', '>=', now()->subDays(3))
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($recentAnalysis) {
                    Log::info("Análise recente existe para o artigo: {$articleSlug} (criada em: {$recentAnalysis->created_at})");
                    return $recentAnalysis;
                }
            }

            // Verificar se já existe análise pendente
            $existingAnalysis = ArticleCorrection::where('article_slug', $articleSlug)
                ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->whereIn('status', [ArticleCorrection::STATUS_PENDING, ArticleCorrection::STATUS_PROCESSING])
                ->first();

            if ($existingAnalysis) {
                Log::info("Análise pendente já existe para o artigo: {$articleSlug}");
                return $existingAnalysis;
            }

            $introducao = $article->content['introducao'] ?? '';

            if (empty($introducao)) {
                Log::info("Artigo sem introdução: {$articleSlug}");
                
                // Criar registro indicando que não há introdução
                $noIntroAnalysis = ArticleCorrection::create([
                    'article_slug' => $articleSlug,
                    'correction_type' => ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS,
                    'status' => ArticleCorrection::STATUS_COMPLETED,
                    'original_data' => [
                        'title' => $article->title ?? '',
                        'template' => $article->template ?? '',
                        'introducao' => '',
                        'analysis_result' => 'no_introduction'
                    ],
                    'correction_data' => [
                        'needs_correction' => false,
                        'confidence_level' => 'high',
                        'correction_priority' => 'none',
                        'problems_found' => [],
                        'analysis_result' => 'no_introduction_found'
                    ],
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $noIntroAnalysis;
            }

            // Análise básica local primeiro
            $localAnalysis = $this->performLocalPunctuationAnalysis($introducao);

            // Se análise local detectar problemas, criar análise para Claude
            if ($localAnalysis['has_issues']) {
                $analysisData = [
                    'title' => $article->title,
                    'introducao' => $introducao,
                    'local_analysis' => $localAnalysis,
                    'article_id' => $article->_id,
                    'template' => $article->template ?? 'unknown',
                    'analysis_version' => '2.0', // Versão para tracking de melhorias
                    'force_reanalyze' => $forceReanalyze
                ];

                $correction = ArticleCorrection::createCorrection(
                    $articleSlug,
                    ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS,
                    $analysisData,
                    'Análise de problemas de pontuação na introdução detectados via análise local v2.0'
                );

                Log::info("Análise criada para artigo: {$articleSlug}, problemas detectados: " . implode(', ', $localAnalysis['issues']));
                return $correction;
            }

            // Se não há problemas, criar registro limpo
            $cleanAnalysis = $this->createCleanAnalysisRecord($articleSlug, $article, $localAnalysis);
            Log::info("Nenhum problema de pontuação detectado localmente: {$articleSlug}");
            return $cleanAnalysis;

        } catch (\Exception $e) {
            Log::error("Erro ao analisar artigo {$articleSlug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria registro de análise limpa (sem problemas)
     */
    protected function createCleanAnalysisRecord($articleSlug, $article, $localAnalysis)
    {
        return ArticleCorrection::create([
            'article_slug' => $articleSlug,
            'correction_type' => ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS,
            'status' => ArticleCorrection::STATUS_COMPLETED,
            'original_data' => [
                'title' => $article->title ?? '',
                'template' => $article->template ?? '',
                'introducao' => $article->content['introducao'] ?? '',
                'local_analysis' => $localAnalysis,
                'analysis_version' => '2.0'
            ],
            'correction_data' => [
                'needs_correction' => false,
                'confidence_level' => 'high',
                'correction_priority' => 'none',
                'problems_found' => [],
                'analysis_result' => 'clean_punctuation'
            ],
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Análise local básica de problemas de pontuação
     */
    protected function performLocalPunctuationAnalysis($text)
    {
        $issues = [];
        $hasIssues = false;

        // Padrão 1: Ponto seguido de palavra com maiúscula e "e" (ex: "segurança. Economia e")
        if (preg_match('/\.\s+[A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][a-záéíóúâêîôûàèìòùãõç]+\s+e\s+/', $text)) {
            $issues[] = 'ponto_antes_enumeracao';
            $hasIssues = true;
        }

        // Padrão 2: Ponto seguido de palavra com maiúscula e "ou" (ex: "urbano. Estradas ou")
        if (preg_match('/\.\s+[A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][a-záéíóúâêîôûàèìòùãõç]+\s+ou\s+/', $text)) {
            $issues[] = 'ponto_antes_ou';
            $hasIssues = true;
        }

        // Padrão 3: Fragmentos com preposições (ex: "motor. Com", "anos. Para")
        if (preg_match('/\.\s+(Com|Para|Seguindo|Considerando|Levando|Incluindo|Além|Através|Sobre|Durante|Entre)\s+/', $text)) {
            $issues[] = 'fragmento_preposicao';
            $hasIssues = true;
        }

        // Padrão 4: Verbo após ponto inadequado (ex: "anos. é essencial")
        if (preg_match('/[a-záéíóúâêîôûàèìòùãõç]+\.\s+(é|são|foi|foram|será|serão|pode|podem|deve|devem)\s+/', $text)) {
            $issues[] = 'verbo_apos_ponto';
            $hasIssues = true;
        }

        // Padrão 5: Maiúscula inadequada após parênteses (ex: "). A escolha")
        if (preg_match('/\)\.\s+[A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ]/', $text)) {
            $issues[] = 'maiuscula_apos_parenteses';
            $hasIssues = true;
        }

        // Padrão 6: Frases muito curtas que quebram o fluxo
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $shortSentences = 0;
        $veryShortSentences = 0;

        foreach ($sentences as $sentence) {
            $wordCount = str_word_count(trim($sentence, '.!?'));
            if ($wordCount < 5) {
                $shortSentences++;
                if ($wordCount < 3) {
                    $veryShortSentences++;
                }
            }
        }

        // Se há muitos fragmentos curtos, é problemático
        if ($shortSentences > 2 || $veryShortSentences > 0) {
            $issues[] = 'muitos_fragmentos';
            $hasIssues = true;
        }

        // Padrão 7: Pontos seguidos de palavras conectivas
        if (preg_match('/\.\s+(Mas|Porém|Entretanto|Contudo|Todavia|Embora|Mesmo|Ainda)\s+/', $text)) {
            $issues[] = 'conectivos_apos_ponto';
            $hasIssues = true;
        }

        return [
            'has_issues' => $hasIssues,
            'issues' => $issues,
            'issue_count' => count($issues),
            'text_length' => strlen($text),
            'sentence_count' => count($sentences),
            'short_sentences' => $shortSentences,
            'very_short_sentences' => $veryShortSentences,
            'analysis_timestamp' => now()->toISOString()
        ];
    }

    /**
     * Processa análise usando Claude API para confirmar problemas
     */
    public function processAnalysisWithClaude(ArticleCorrection $correction)
    {
        try {
            $correction->markAsProcessing();

            $originalData = $correction->original_data;
            $introducao = $originalData['introducao'];

            Log::info("Processando análise Claude para: {$correction->article_slug}");

            $prompt = $this->createAnalysisPrompt($originalData);

            // Chamar Claude API
            $response = Http::timeout(90)->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 1000,
                'temperature' => 0.1,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => "Você é um especialista em análise de texto e pontuação em português brasileiro. Analise textos identificando problemas específicos de pontuação e formatação. Seja preciso e objetivo em sua análise."
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $analysisResult = $this->processApiResponse($content);

                if ($analysisResult) {
                    // Se Claude confirmar problemas, criar correção
                    if ($analysisResult['needs_correction']) {
                        $this->createCorrectionFromAnalysis($correction, $analysisResult);
                    }

                    $correction->markAsCompleted($analysisResult);
                    Log::info("Análise Claude concluída para: {$correction->article_slug}");
                    return true;
                } else {
                    $correction->markAsFailed("Resposta da API inválida ou não processável");
                    return false;
                }
            }

            // Tratar diferentes tipos de erro da API
            $errorMessage = $this->parseApiError($response);
            $correction->markAsFailed("Falha na API Claude: " . $errorMessage);
            return false;

        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar análise Claude: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse de erros da API
     */
    protected function parseApiError($response)
    {
        $statusCode = $response->status();
        $body = $response->json();

        switch ($statusCode) {
            case 429:
                return "Rate limit excedido - muitas requisições";
            case 401:
                return "API key inválida ou expirada";
            case 400:
                return "Requisição inválida: " . ($body['error']['message'] ?? 'formato incorreto');
            case 500:
                return "Erro interno da API Claude";
            default:
                return "HTTP {$statusCode}: " . ($body['error']['message'] ?? $response->body());
        }
    }

    /**
     * Cria prompt para análise de pontuação
     */
    protected function createAnalysisPrompt($originalData)
    {
        $title = $originalData['title'];
        $introducao = $originalData['introducao'];
        $localIssues = implode(', ', $originalData['local_analysis']['issues']);

        return <<<EOT
Analise o seguinte texto de introdução de um artigo automotivo em busca de problemas de pontuação:

**TÍTULO:** {$title}
**TEXTO:** {$introducao}

**PROBLEMAS DETECTADOS LOCALMENTE:** {$localIssues}

Verifique especificamente:
1. Pontos inadequados no meio de enumerações (ex: "segurança. Economia e desempenho")
2. Fragmentos de frases após pontos (ex: "motor. Com tecnologia avançada")
3. Verbos soltos após pontos (ex: "anos. é essencial")
4. Maiúsculas inadequadas após vírgulas ou parênteses
5. Frases muito curtas que deveriam ser conectadas
6. Conectivos após pontos que deveriam usar vírgulas

Retorne APENAS um JSON com esta estrutura:

{
  "needs_correction": true/false,
  "confidence_level": "high/medium/low",
  "problems_found": [
    {
      "type": "tipo_do_problema",
      "description": "descrição do problema",
      "text_fragment": "fragmento do texto com problema"
    }
  ],
  "correction_priority": "high/medium/low",
  "estimated_complexity": "simple/moderate/complex"
}
EOT;
    }

    /**
     * Processa resposta da API Claude
     */
    protected function processApiResponse($content)
    {
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false) {
            $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $result = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                return $result;
            }

            Log::error("Erro decodificando JSON de análise: " . json_last_error_msg());
            Log::error("Conteúdo JSON problemático: " . $jsonContent);
        }

        Log::error("Não foi possível extrair JSON da resposta da API");
        Log::error("Conteúdo completo da resposta: " . $content);
        return null;
    }

    /**
     * Cria correção baseada na análise
     */
    protected function createCorrectionFromAnalysis(ArticleCorrection $analysis, $analysisResult)
    {
        if (!$analysisResult['needs_correction']) {
            return null;
        }

        $article = Article::where('slug', $analysis->article_slug)->first();
        if (!$article) {
            Log::error("Artigo não encontrado para criar correção: {$analysis->article_slug}");
            return null;
        }

        // Verificar se já existe correção pendente
        $existingCorrection = ArticleCorrection::where('article_slug', $analysis->article_slug)
            ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
            ->whereIn('status', [ArticleCorrection::STATUS_PENDING, ArticleCorrection::STATUS_PROCESSING])
            ->first();

        if ($existingCorrection) {
            Log::info("Correção já existe para: {$analysis->article_slug}");
            return $existingCorrection;
        }

        try {
            $correctionData = [
                'title' => $article->title,
                'introducao' => $article->content['introducao'] ?? '',
                'seo_data' => [
                    'page_title' => $article->seo_data['page_title'] ?? '',
                    'meta_description' => $article->seo_data['meta_description'] ?? ''
                ],
                'analysis_result' => $analysisResult,
                'analysis_id' => $analysis->_id,
                'created_from_analysis_v2' => true
            ];

            $correction = ArticleCorrection::createCorrection(
                $analysis->article_slug,
                ArticleCorrection::TYPE_INTRODUCTION_FIX,
                $correctionData,
                "Correção criada automaticamente baseada na análise ID: {$analysis->_id} (v2.0)"
            );

            Log::info("Correção criada baseada em análise para: {$analysis->article_slug}, ID: {$correction->_id}");
            return $correction;

        } catch (\Exception $e) {
            Log::error("Erro ao criar correção baseada em análise para {$analysis->article_slug}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Analisa múltiplos artigos
     */
    public function analyzeMultipleArticles(array $slugs, $forceReanalyze = false)
    {
        $results = [];

        foreach ($slugs as $slug) {
            $result = $this->analyzeArticlePunctuation($slug, $forceReanalyze);
            $results[$slug] = [
                'analyzed' => $result !== false,
                'needs_correction' => $result !== null && isset($result->correction_data['needs_correction']) 
                    ? $result->correction_data['needs_correction'] : false,
                'correction_id' => $result ? $result->_id : null,
                'was_reanalyzed' => $forceReanalyze
            ];
        }

        return $results;
    }

    /**
     * Verifica se um artigo precisa de reanálise (mais de 3 dias)
     */
    public function shouldReanalyze($articleSlug)
    {
        $lastAnalysis = ArticleCorrection::where('article_slug', $articleSlug)
            ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastAnalysis) {
            return true; // Nunca foi analisado
        }

        // Se foi analisado há mais de 3 dias
        return $lastAnalysis->created_at->lt(now()->subDays(3));
    }

    /**
     * Obtém artigos que precisam de reanálise
     */
    public function getArticlesNeedingReanalysis($limit = 50)
    {
        // Buscar artigos que foram analisados há mais de 3 dias
        $oldAnalyses = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '<', now()->subDays(3))
            ->groupBy('article_slug')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->pluck('article_slug');

        return $oldAnalyses->toArray();
    }
}