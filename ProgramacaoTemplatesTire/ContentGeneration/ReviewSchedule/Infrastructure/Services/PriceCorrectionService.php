<?php

namespace Src\ContentGeneration\ReviewSchedule\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class PriceCorrectionService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * Cria correções para uma lista de slugs
     */
    public function createCorrectionsForSlugs(array $slugs)
    {
        $results = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($slugs as $slug) {
            try {
                $correction = $this->createCorrection($slug);

                if ($correction) {
                    $results['created']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Erro ao criar correção para {$slug}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * 🎯 CORREÇÃO PRINCIPAL: Uma correção por artigo - DEFINITIVA
     * Se já foi corrigido uma vez, NUNCA mais será corrigido novamente
     */
    protected function createCorrection($articleSlug)
    {
        // Buscar o artigo
        $article = Article::where('slug', $articleSlug)
            ->where('category_slug', 'revisoes-programadas')
            ->first();

        if (!$article) {
            return false;
        }

        // 🔐 REGRA DEFINITIVA: Se já existe QUALQUER correção para este artigo, PULAR
        $anyExistingCorrection = ArticleCorrection::where('article_slug', $articleSlug)
            ->where('correction_type', 'price_correction')
            ->exists(); // ← Qualquer status - pending, processing, completed, failed

        if ($anyExistingCorrection) {
            // Log::debug("Artigo {$articleSlug} já possui correção (pulando)");
            return false;
        }

        // Extrair dados mínimos necessários
        $originalData = [
            'title' => $article->title,
            'vehicle_data' => $article->extracted_entities,
            'current_content' => $article->content
        ];

        // Criar correção ÚNICA
        $correction = ArticleCorrection::createCorrection(
            $articleSlug,
            'price_correction',
            $originalData,
            'Correção ÚNICA de preços via Claude API - mercado 2024/2025'
        );

        Log::info("Correção ÚNICA criada para artigo: {$articleSlug}");
        return $correction;
    }

    /**
     * 🧹 Limpar todas as duplicatas e manter apenas UMA correção por artigo
     */
    public function cleanAllDuplicates()
    {
        Log::info("🧹 Iniciando limpeza de correções duplicadas...");

        $results = [
            'articles_analyzed' => 0,
            'duplicates_found' => 0,
            'corrections_removed' => 0,
            'articles_cleaned' => []
        ];

        // Buscar todos os article_slug que têm mais de uma correção
        $articleSlugs = ArticleCorrection::where('correction_type', 'price_correction')
            ->select('article_slug')
            ->groupBy('article_slug')
            ->havingRaw('count(*) > 1')
            ->pluck('article_slug');

        $results['articles_analyzed'] = $articleSlugs->count();

        foreach ($articleSlugs as $slug) {
            $corrections = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', 'price_correction')
                ->orderBy('created_at', 'desc') // Mais recente primeiro
                ->get();

            if ($corrections->count() > 1) {
                $results['duplicates_found']++;

                // Manter APENAS a primeira (mais recente)
                $keepFirst = $corrections->first();
                $duplicatesToDelete = $corrections->skip(1);

                Log::info("📦 Artigo {$slug}: {$corrections->count()} correções encontradas, mantendo ID {$keepFirst->_id}");

                // Deletar duplicatas
                foreach ($duplicatesToDelete as $duplicate) {
                    $duplicate->delete();
                    $results['corrections_removed']++;
                }

                $results['articles_cleaned'][] = $slug;
            }
        }

        Log::info("✅ Limpeza concluída", $results);
        return $results;
    }

    /**
     * 🎯 BUSCAR APENAS ARTIGOS QUE NUNCA FORAM CORRIGIDOS
     */
    public function getAllArticleSlugs($limit = 1000)
    {
        // Buscar slugs que JÁ foram corrigidos
        $alreadyCorrected = ArticleCorrection::where('correction_type', 'price_correction')
            ->distinct('article_slug')
            ->pluck('article_slug')
            ->toArray();

        // Buscar artigos que NUNCA foram corrigidos
        $query = Article::where('category_slug', 'revisoes-programadas');

        if (!empty($alreadyCorrected)) {
            $query->whereNotIn('slug', $alreadyCorrected);
        }

        $slugs = $query->limit($limit)->pluck('slug')->toArray();

        Log::info("📊 Artigos para correção: " . count($slugs) . " (já corrigidos: " . count($alreadyCorrected) . ")");

        return $slugs;
    }

    /**
     * Processa correção usando Claude API
     */
    public function processPriceCorrection(ArticleCorrection $correction)
    {
        try {
            $correction->markAsProcessing();

            $article = Article::where('slug', $correction->article_slug)->first();
            if (!$article) {
                $correction->markAsFailed("Artigo não encontrado");
                return false;
            }

            // Prompt simplificado - deixa Claude decidir tudo
            $prompt = $this->createSimplifiedPrompt($article);

            $response = Http::timeout(120)->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 2500,
                'temperature' => 0.1,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => "Você é especialista em manutenção automotiva no Brasil. Analise os preços de revisões e atualize com valores realistas para 2024/2025, considerando marca, modelo, ano e tipo de veículo. Retorne apenas JSON válido."
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->extractJsonFromResponse($content);

                if ($correctedData && $this->applyCorrections($article, $correctedData)) {
                    $this->clearArticleCache($correction->article_slug);
                    $correction->markAsCompleted($correctedData);
                    Log::info("✅ Correção DEFINITIVA aplicada em: {$correction->article_slug}");
                    return true;
                }
            }

            $correction->markAsFailed("Falha na API: " . $response->body());
            return false;
        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar correção: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpa o cache do artigo após correção
     */
    protected function clearArticleCache(string $slug): void
    {
        $cacheKeys = [
            "article_view_{$slug}",
            "article_amp_view_{$slug}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::info("Cache limpo para artigo: {$slug}", ['keys' => $cacheKeys]);
    }

    /**
     * Prompt simplificado - deixa Claude fazer toda a análise
     */
    protected function createSimplifiedPrompt($article)
    {
        $vehicleData = $article->extracted_entities;
        $content = $article->content;

        $brand = $vehicleData['marca'] ?? 'N/A';
        $model = $vehicleData['modelo'] ?? 'N/A';
        $year = $vehicleData['ano'] ?? 'N/A';

        $currentPricesJson = json_encode([
            'visao_geral_revisoes' => $content['visao_geral_revisoes'] ?? [],
            'cronograma_detalhado' => $content['cronograma_detalhado'] ?? []
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<EOT
Atualize os preços de revisões para o mercado brasileiro 2024/2025.

**VEÍCULO:**
- Marca: {$brand}
- Modelo: {$model}  
- Ano: {$year}

**PREÇOS ATUAIS:**
```json
{$currentPricesJson}
```

**INSTRUÇÕES:**
1. Analise se este veículo é popular, intermediário ou premium
2. Considere que os preços devem refletir o mercado atual (inflação, custos de peças/mão de obra)
3. Mantenha progressão lógica entre revisões (1ª < 2ª < 3ª...)
4. Use faixas realistas: Popular R$300-1300, Intermediário R$400-1500, Premium R$500-2000

**RETORNE APENAS ESTE JSON:**
```json
{
  "needs_update": true|false,
  "reason": "breve explicação se precisa atualizar",
  "corrected_prices": {
    "visao_geral_revisoes": [...],
    "cronograma_detalhado": [...]
  }
}
```

Se os preços já estão adequados, retorne "needs_update": false.
EOT;
    }

    /**
     * Extrai JSON da resposta do Claude
     */
    protected function extractJsonFromResponse($content)
    {
        // Limpar markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        // Extrair JSON
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false) {
            $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $result = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Aplica correções no artigo
     */
    protected function applyCorrections(Article $article, $correctedData)
    {
        // Se Claude diz que não precisa atualizar, respeitar
        if (!($correctedData['needs_update'] ?? true)) {
            Log::info("Claude determinou que {$article->slug} não precisa de atualização: " . ($correctedData['reason'] ?? ''));
            return true;
        }

        if (!isset($correctedData['corrected_prices'])) {
            return false;
        }

        $content = $article->content;
        $updated = false;

        // Atualizar seções se fornecidas
        if (!empty($correctedData['corrected_prices']['visao_geral_revisoes'])) {
            $content['visao_geral_revisoes'] = $correctedData['corrected_prices']['visao_geral_revisoes'];
            $updated = true;
        }

        if (!empty($correctedData['corrected_prices']['cronograma_detalhado'])) {
            $content['cronograma_detalhado'] = $correctedData['corrected_prices']['cronograma_detalhado'];
            $updated = true;
        }

        if ($updated) {
            $article->update([
                'content' => $content,
                'updated_at' => now()
            ]);
            return true;
        }

        return false;
    }

    /**
     * Processa todas as correções pendentes
     */
    public function processAllPendingCorrections($limit = 50)
    {
        $corrections = ArticleCorrection::where('correction_type', 'price_correction')
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->limit($limit)
            ->get();

        $results = ['processed' => 0, 'successful' => 0, 'failed' => 0];

        foreach ($corrections as $correction) {
            $results['processed']++;

            if ($this->processPriceCorrection($correction)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            // Pausa para evitar rate limit
            sleep(2);
        }

        return $results;
    }

    /**
     * Estatísticas das correções
     */
    /**
     * Estatísticas das correções - CORRIGIDO
     */
    public function getStats()
    {
        $pending = ArticleCorrection::where('correction_type', 'price_correction')
            ->where('status', ArticleCorrection::STATUS_PENDING)->count();

        $processing = ArticleCorrection::where('correction_type', 'price_correction')
            ->where('status', ArticleCorrection::STATUS_PROCESSING)->count();

        $completed = ArticleCorrection::where('correction_type', 'price_correction')
            ->where('status', ArticleCorrection::STATUS_COMPLETED)->count();

        $failed = ArticleCorrection::where('correction_type', 'price_correction')
            ->where('status', ArticleCorrection::STATUS_FAILED)->count();

        // 🔧 CORREÇÃO: Total correto
        $total = $pending + $processing + $completed + $failed;

        return [
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'total' => $total, // ← Correto agora
        ];
    }
}
