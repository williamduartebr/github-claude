<?php

namespace Src\ContentGeneration\ReviewSchedule\Infrastructure\Services;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;


class ArticleIntroductionCorrectionService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * 🎯 Cria correções para introdução e considerações finais
     */
    public function createCorrectionsForSlugs(array $slugs): array
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
                Log::error("Erro ao criar correção de introdução para {$slug}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * 🔐 REGRA: Uma correção por artigo - evita duplicatas
     */
    protected function createCorrection(string $articleSlug): ?ArticleCorrection
    {
        // Buscar artigo
        $article = Article::where('slug', $articleSlug)
            ->where('category_slug', 'revisoes-programadas')
            ->first();

        if (!$article) {
            return null;
        }

        // Verificar se já existe correção deste tipo
        $existingCorrection = ArticleCorrection::where('article_slug', $articleSlug)
            ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->exists();

        if ($existingCorrection) {
            Log::debug("Artigo {$articleSlug} já possui correção de conteúdo (pulando)");
            return null;
        }

        // Extrair dados necessários
        $originalData = [
            'title' => $article->title,
            'template' => $article->template ?? 'review_schedule',
            'vehicle_data' => $article->extracted_entities,
            'current_content' => [
                'introducao' => $article->content['introducao'] ?? '',
                'consideracoes_finais' => $article->content['consideracoes_finais'] ?? ''
            ]
        ];

        // Criar correção única
        $correction = ArticleCorrection::createCorrection(
            $articleSlug,
            ArticleCorrection::TYPE_CONTENT_ENHANCEMENT,
            $originalData,
            'Correção humanizada de introdução e considerações finais via Claude API'
        );

        Log::info("Correção de conteúdo criada para: {$articleSlug}");
        return $correction;
    }

    /**
     * 🤖 Processa correção usando Claude API com prompt humanizado
     */
    public function processIntroductionCorrection(ArticleCorrection $correction): bool
    {
        try {
            $correction->markAsProcessing();

            $article = Article::where('slug', $correction->article_slug)->first();
            if (!$article) {
                $correction->markAsFailed("Artigo não encontrado");
                return false;
            }

            // Prompt humanizado para Claude
            $prompt = $this->createHumanizedPrompt($article);

            $response = Http::timeout(120)->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 3000,
                'temperature' => 0.3, // Mais criativo para variação natural
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => "Você é um redator automotivo experiente no Brasil. Crie textos naturais, humanizados e envolventes que conectem com o leitor. Evite linguagem robótica ou repetitiva. Retorne apenas JSON válido."
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->extractJsonFromResponse($content);

                if ($correctedData && $this->applyCorrections($article, $correctedData)) {
                    $this->clearArticleCache($correction->article_slug);
                    $correction->markAsCompleted($correctedData);
                    Log::info("✅ Correção de conteúdo aplicada em: {$correction->article_slug}");
                    return true;
                }
            }

            $correction->markAsFailed("Falha na API: " . $response->body());
            return false;
        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar correção de conteúdo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📝 Prompt humanizado focado em engajamento
     */
    protected function createHumanizedPrompt(Article $article): string
    {
        $vehicleData = $article->extracted_entities;
        $content = $article->content;

        $brand = $vehicleData['marca'] ?? 'N/A';
        $model = $vehicleData['modelo'] ?? 'N/A';
        $year = $vehicleData['ano'] ?? 'N/A';
        $vehicleType = $vehicleData['tipo_veiculo'] ?? 'veículo';

        $currentIntro = $content['introducao'] ?? '';
        $currentConclusion = $content['consideracoes_finais'] ?? '';

        $vehicleTypeText = $vehicleType === 'motocicleta' ? 'motocicleta' : 'veículo';

        return <<<EOT
Reescreva a introdução e considerações finais para um cronograma de revisões, tornando o texto mais envolvente e humanizado.

**VEÍCULO:**
- Marca: {$brand}
- Modelo: {$model}
- Ano: {$year}
- Tipo: {$vehicleTypeText}

**CONTEÚDO ATUAL:**

**Introdução:**
"{$currentIntro}"

**Considerações Finais:**
"{$currentConclusion}"

**DIRETRIZES PARA REESCRITA:**

**Para a Introdução:**
- Conecte emocionalmente com o proprietário do {$vehicleTypeText}
- Enfatize a importância da manutenção preventiva
- Use linguagem acessível mas técnica
- Mencione segurança, economia e durabilidade
- 2-3 frases impactantes

**Para as Considerações Finais:**
- Reforce o valor do investimento em manutenção
- Destaque benefícios a longo prazo
- Inspire confiança na decisão de manter o cronograma
- Tom positivo e motivador
- 2-3 frases conclusivas

**VARIAÇÕES ESPERADAS:**
- Evite frases genéricas como "é importante manter"
- Use verbos de ação: "garante", "preserva", "protege"
- Inclua benefícios específicos: economia, segurança, valor de revenda
- Linguagem natural, como se fosse um especialista conversando

**RETORNE APENAS ESTE JSON:**
```json
{
  "needs_update": true|false,
  "reason": "explicação breve se precisa atualizar",
  "corrected_content": {
    "introducao": "nova introdução mais envolvente",
    "consideracoes_finais": "novas considerações finais motivadoras"
  }
}
```

Se o conteúdo já está excelente, retorne "needs_update": false.
EOT;
    }

    /**
     * 📊 Busca artigos que nunca foram corrigidos
     */
    public function getAllArticleSlugs(int $limit = 1000): array
    {
        try {
            // Slugs já corrigidos
            $alreadyCorrected = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->distinct('article_slug')
                ->pluck('article_slug')
                ->toArray();

            // Artigos nunca corrigidos
            $query = Article::where('category_slug', 'revisoes-programadas');

            if (!empty($alreadyCorrected)) {
                $query->whereNotIn('slug', $alreadyCorrected);
            }

            $slugs = $query->limit($limit)->pluck('slug')->toArray();

            $alreadyCount = count($alreadyCorrected);
            $availableCount = count($slugs);

            Log::info("📊 Artigos para correção de conteúdo: {$availableCount} (já corrigidos: {$alreadyCount})");

            return $slugs;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar slugs para correção: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🧹 Limpar duplicatas
     */
    public function cleanAllDuplicates(): array
    {
        Log::info("🧹 Iniciando limpeza de correções de conteúdo duplicadas...");

        $results = [
            'articles_analyzed' => 0,
            'duplicates_found' => 0,
            'corrections_removed' => 0,
            'articles_cleaned' => []
        ];

        try {
            $articleSlugs = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->select('article_slug')
                ->groupBy('article_slug')
                ->havingRaw('count(*) > 1')
                ->pluck('article_slug');

            $results['articles_analyzed'] = $articleSlugs->count();

            foreach ($articleSlugs as $slug) {
                $corrections = ArticleCorrection::where('article_slug', $slug)
                    ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($corrections->count() > 1) {
                    $results['duplicates_found']++;
                    $keepFirst = $corrections->first();
                    $duplicatesToDelete = $corrections->skip(1);

                    foreach ($duplicatesToDelete as $duplicate) {
                        $duplicate->delete();
                        $results['corrections_removed']++;
                    }

                    $results['articles_cleaned'][] = $slug;
                }
            }

            Log::info("✅ Limpeza de conteúdo concluída", $results);
            return $results;
        } catch (\Exception $e) {
            Log::error("Erro na limpeza de duplicatas: " . $e->getMessage());
            return $results;
        }
    }

    /**
     * ⚡ Processa todas as correções pendentes
     */
    public function processAllPendingCorrections(int $limit = 10): array
    {
        $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->limit($limit)
            ->get();

        $results = ['processed' => 0, 'successful' => 0, 'failed' => 0];

        foreach ($corrections as $correction) {
            $results['processed']++;

            if ($this->processIntroductionCorrection($correction)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            // Pausa humanizada (simula tempo de análise)
            sleep(rand(3, 8));
        }

        return $results;
    }

    /**
     * 📈 Estatísticas das correções
     */
    public function getStats(): array
    {
        $pending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->where('status', ArticleCorrection::STATUS_PENDING)->count();

        $processing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->where('status', ArticleCorrection::STATUS_PROCESSING)->count();

        $completed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)->count();

        $failed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
            ->where('status', ArticleCorrection::STATUS_FAILED)->count();

        $total = $pending + $processing + $completed + $failed;

        return [
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'total' => $total,
        ];
    }

    /**
     * 🗑️ Limpa cache do artigo
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
     * 🔍 Extrai JSON da resposta do Claude
     */
    protected function extractJsonFromResponse(string $content): ?array
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
     * ✏️ Aplica correções no artigo
     */
    protected function applyCorrections(Article $article, array $correctedData): bool
    {
        try {
            // Se Claude determina que não precisa atualizar
            if (!($correctedData['needs_update'] ?? true)) {
                Log::info("Claude determinou que {$article->slug} não precisa de atualização de conteúdo: " . ($correctedData['reason'] ?? ''));
                return true;
            }

            if (!isset($correctedData['corrected_content'])) {
                return false;
            }

            $content = $article->content;
            $updated = false;

            // Atualizar introdução
            if (!empty($correctedData['corrected_content']['introducao'])) {
                $content['introducao'] = $correctedData['corrected_content']['introducao'];
                $updated = true;
            }

            // Atualizar considerações finais
            if (!empty($correctedData['corrected_content']['consideracoes_finais'])) {
                $content['consideracoes_finais'] = $correctedData['corrected_content']['consideracoes_finais'];
                $updated = true;
            }

            if ($updated) {
                // ⏰ Timestamp humanizado considerando timezone do servidor
                $humanizedTimestamp = $this->generateHumanizedTimestamp();

                $article->update([
                    'content' => $content,
                    'updated_at' => $humanizedTimestamp
                ]);

                Log::info("Conteúdo atualizado para {$article->slug} com timestamp humanizado: {$humanizedTimestamp}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Erro ao aplicar correções em {$article->slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🎭 Gera timestamp humanizado que nunca parece automático
     * Considera timezone do servidor (America/Sao_Paulo) mas salva como UTC no MongoDB
     */
    private function generateHumanizedTimestamp(): string
    {
        // Pegar data/hora atual do servidor (já está em America/Sao_Paulo)
        $now = now();

        // Diminuir 4 horas
        $now->subHours(4);

        // Variação aleatória: -3min a +3min
        $minutesVariation = rand(-3, 3);
        $secondsVariation = rand(-59, 59);

        // Aplicar variação
        $humanizedTime = $now->copy()
            ->addMinutes($minutesVariation)
            ->addSeconds($secondsVariation);

        // Microsegundos aleatórios para parecer ainda mais natural
        $microseconds = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Converter para UTC (como MongoDB espera) e formatar
        $utcTime = $humanizedTime->utc();
        $humanizedTimestamp = $utcTime->format('Y-m-d\TH:i:s.') . $microseconds . 'Z';

        // Log para debug (opcional - remover em produção)
        Log::debug("Timestamp humanizado gerado", [
            'server_time' => now()->format('Y-m-d H:i:s T'),
            'adjusted_time' => $now->format('Y-m-d H:i:s T'),
            'variation_minutes' => $minutesVariation,
            'variation_seconds' => $secondsVariation,
            'final_utc' => $humanizedTimestamp,
            'final_local' => $humanizedTime->format('Y-m-d H:i:s T')
        ]);

        return $humanizedTimestamp;
    }
}
