<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class TitleYearCorrectionService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * 🎯 Cria correções para artigos de pneus (títulos e ano)
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
                Log::error("Erro ao criar correção de título/ano para {$slug}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * 🔐 REGRA: Uma correção por artigo - evita duplicatas
     */
    protected function createCorrection(string $articleSlug): ?ArticleCorrection
    {
        // Buscar artigo temporário de pneus
        $tempArticle = TempArticle::where('slug', $articleSlug)
            ->where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->first();

        if (!$tempArticle) {
            return null;
        }

        // Verificar se já existe correção deste tipo
        $existingCorrection = ArticleCorrection::where('article_slug', $articleSlug)
            ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->exists();

        if ($existingCorrection) {
            Log::debug("Artigo {$articleSlug} já possui correção de título/ano (pulando)");
            return null;
        }

        // Extrair dados necessários
        $originalData = [
            'title' => $tempArticle->title,
            'template' => $tempArticle->template ?? 'when_to_change_tires',
            'domain' => $tempArticle->domain,
            'vehicle_data' => $tempArticle->vehicle_data ?? [],
            'current_seo' => [
                'page_title' => $tempArticle->seo_data['page_title'] ?? '',
                'meta_description' => $tempArticle->seo_data['meta_description'] ?? ''
            ],
            'current_content' => [
                'perguntas_frequentes' => $tempArticle->content['perguntas_frequentes'] ?? []
            ]
        ];

        // Criar correção única
        $correction = ArticleCorrection::createCorrection(
            $articleSlug,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX,
            $originalData,
            'Correção de títulos, meta descriptions e FAQs com ano do veículo via Claude API'
        );

        Log::info("Correção de título/ano criada para: {$articleSlug}");
        return $correction;
    }

    /**
     * 🤖 Processa correção usando Claude API com prompt especializado
     */
    public function processTitleYearCorrection(ArticleCorrection $correction): bool
    {
        try {
            // ⏱️ Rate limiting - máximo 1 request por minuto
            $lastRequest = Cache::get('claude_title_year_last_request', 0);
            $timeSinceLastRequest = time() - $lastRequest;
            
            if ($timeSinceLastRequest < 60) {
                $waitTime = 60 - $timeSinceLastRequest;
                Log::info("⏸️ Aguardando {$waitTime}s para respeitar rate limit da Claude API (título/ano)");
                sleep($waitTime);
            }
            
            Cache::put('claude_title_year_last_request', time(), 300);

            $correction->markAsProcessing();

            $tempArticle = TempArticle::where('slug', $correction->article_slug)
                ->where('domain', 'when_to_change_tires')
                ->first();

            if (!$tempArticle) {
                $correction->markAsFailed("Artigo temporário não encontrado");
                return false;
            }

            // Prompt especializado para títulos e ano
            $prompt = $this->createTitleYearPrompt($tempArticle);

            $response = Http::retry(3, 2000) // 3 tentativas, 2s entre cada
                ->timeout(180) // 3 minutos
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])->post($this->apiUrl, [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 3000,
                    'temperature' => 0.3,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'system' => "Você é um especialista em SEO automotivo brasileiro. Crie títulos e descrições que incluam o ano do veículo de forma natural e atrativa. Sempre inclua o ano quando fornecido. Atualize FAQs para incluir referências específicas ao ano. Retorne apenas JSON válido."
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->extractJsonFromResponse($content);

                if ($correctedData && $this->applyTitleYearCorrections($tempArticle, $correctedData)) {
                    $this->clearArticleCache($correction->article_slug);
                    $correction->markAsCompleted($correctedData);
                    Log::info("✅ Correção de título/ano aplicada em: {$correction->article_slug}");
                    return true;
                }
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                // Se for rate limit (429), aguardar mais tempo
                if ($statusCode === 429) {
                    Log::warning("⚠️ Rate limit atingido na Claude API (título/ano), aguardando 5 minutos");
                    sleep(300); // 5 minutos
                }
                
                $correction->markAsFailed("Falha na API ({$statusCode}): " . $errorBody);
                return false;
            }

            $correction->markAsFailed("Falha na API: " . $response->body());
            return false;
        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar correção de título/ano: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📝 Prompt especializado para títulos, meta descriptions e FAQs
     */
    protected function createTitleYearPrompt(TempArticle $tempArticle): string
    {
        $vehicleData = $tempArticle->vehicle_data ?? [];
        $seoData = $tempArticle->seo_data ?? [];
        $content = $tempArticle->content ?? [];

        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
        $vehicleModel = $vehicleData['vehicle_model'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';
        $vehicleCategory = $vehicleData['vehicle_category'] ?? 'veículo';

        $currentPageTitle = $seoData['page_title'] ?? '';
        $currentMetaDescription = $seoData['meta_description'] ?? '';
        $currentFaqs = $content['perguntas_frequentes'] ?? [];

        // Converter FAQs para texto mais legível
        $faqsText = '';
        if (is_array($currentFaqs)) {
            $faqsText = json_encode($currentFaqs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return <<<EOT
Atualize o título da página, meta description e perguntas frequentes para incluir o ano do veículo de forma natural e otimizada para SEO.

**VEÍCULO:**
- Nome: {$vehicleName}
- Marca: {$vehicleBrand}
- Modelo: {$vehicleModel}
- Ano: {$vehicleYear}
- Categoria: {$vehicleCategory}

**CONTEÚDO ATUAL:**

**Título da página atual:**
"{$currentPageTitle}"

**Meta description atual:**
"{$currentMetaDescription}"

**Perguntas frequentes atuais:**
{$faqsText}

**DIRETRIZES PARA ATUALIZAÇÃO:**

**Para o Título da Página (page_title):**
- SEMPRE incluir o ano {$vehicleYear} se fornecido
- Manter foco na palavra-chave principal "quando trocar pneus"
- Incluir marca e modelo: {$vehicleBrand} {$vehicleModel}
- Máximo 60 caracteres para SEO
- Formato sugerido: "Quando Trocar Pneus {$vehicleBrand} {$vehicleModel} {$vehicleYear}: Guia Completo"

**Para a Meta Description:**
- SEMPRE incluir o ano {$vehicleYear} se fornecido
- Incluir pressões recomendadas se disponíveis
- Mencionar "sinais de desgaste", "manutenção" e "segurança"
- Entre 150-160 caracteres
- Call-to-action natural sobre manutenção
- Foco em benefícios: economia, segurança, durabilidade

**Para as Perguntas Frequentes:**
- Atualizar referências genéricas para incluir "{$vehicleBrand} {$vehicleModel} {$vehicleYear}"
- Manter o conteúdo técnico mas personalizar para o veículo específico
- Se houver pergunta sobre frequência de verificação, mencionar o modelo e ano
- Se houver pergunta sobre medida de pneu, especificar para o {$vehicleYear}
- Manter estrutura pergunta/resposta

**CRITÉRIOS DE QUALIDADE:**
- Ano OBRIGATÓRIO nos títulos e meta description
- Linguagem natural, não robotizada
- Foco em SEO local brasileiro
- Evitar repetições excessivas do ano
- Manter tom informativo e confiável

**RETORNE APENAS ESTE JSON:**
```json
{
  "needs_update": true|false,
  "reason": "explicação breve se precisa atualizar",
  "title_updated": true|false,
  "meta_updated": true|false,
  "faq_updated": true|false,
  "corrected_seo": {
    "page_title": "novo título com ano",
    "meta_description": "nova meta description com ano"
  },
  "corrected_content": {
    "perguntas_frequentes": [
      {
        "pergunta": "pergunta atualizada com referência ao veículo específico",
        "resposta": "resposta atualizada com {$vehicleBrand} {$vehicleModel} {$vehicleYear}"
      }
    ]
  }
}
```

Se o conteúdo já está perfeito com o ano incluído, retorne "needs_update": false.
Se apenas alguns campos precisam de atualização, marque apenas os campos específicos como true.
EOT;
    }

    /**
     * 📊 Busca artigos que nunca foram corrigidos (título/ano)
     */
    public function getAllTireArticleSlugs(int $limit = 1000): array
    {
        try {
            // Slugs já corrigidos para título/ano
            $alreadyCorrected = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->distinct('article_slug')
                ->pluck('article_slug')
                ->toArray();

            // Artigos de pneus nunca corrigidos para título/ano
            $query = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft');

            if (!empty($alreadyCorrected)) {
                $query->whereNotIn('slug', $alreadyCorrected);
            }

            $slugs = $query->limit($limit)->pluck('slug')->toArray();

            $alreadyCount = count($alreadyCorrected);
            $availableCount = count($slugs);

            Log::info("📊 Artigos de pneus para correção de título/ano: {$availableCount} (já corrigidos: {$alreadyCount})");

            return $slugs;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar slugs de pneus para correção de título/ano: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🧹 Limpar duplicatas
     */
    public function cleanAllDuplicates(): array
    {
        Log::info("🧹 Iniciando limpeza de correções de título/ano duplicadas...");

        $results = [
            'articles_analyzed' => 0,
            'duplicates_found' => 0,
            'corrections_removed' => 0,
            'articles_cleaned' => []
        ];

        try {
            $articleSlugs = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->select('article_slug')
                ->groupBy('article_slug')
                ->havingRaw('count(*) > 1')
                ->pluck('article_slug');

            $results['articles_analyzed'] = $articleSlugs->count();

            foreach ($articleSlugs as $slug) {
                $corrections = ArticleCorrection::where('article_slug', $slug)
                    ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
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

            Log::info("✅ Limpeza de título/ano concluída", $results);
            return $results;
        } catch (\Exception $e) {
            Log::error("Erro na limpeza de duplicatas de título/ano: " . $e->getMessage());
            return $results;
        }
    }

    /**
     * ⚡ Processa todas as correções pendentes
     */
    public function processAllPendingCorrections(int $limit = 1): array
    {
        $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->limit($limit)
            ->get();

        $results = [
            'processed' => 0, 
            'successful' => 0, 
            'failed' => 0,
            'details' => [
                'titles_updated' => 0,
                'metas_updated' => 0,
                'faqs_updated' => 0
            ]
        ];

        foreach ($corrections as $correction) {
            $results['processed']++;

            if ($this->processTitleYearCorrection($correction)) {
                $results['successful']++;
                
                // Contar detalhes das atualizações
                $fresh = $correction->fresh();
                if ($fresh && isset($fresh->correction_data)) {
                    $data = $fresh->correction_data;
                    
                    if ($data['title_updated'] ?? false) {
                        $results['details']['titles_updated']++;
                    }
                    if ($data['meta_updated'] ?? false) {
                        $results['details']['metas_updated']++;
                    }
                    if ($data['faq_updated'] ?? false) {
                        $results['details']['faqs_updated']++;
                    }
                }
            } else {
                $results['failed']++;
            }

            // ⏸️ Pausa mais longa para API Claude (2-3 minutos entre requests)
            if ($results['processed'] < count($corrections)) {
                $pauseTime = rand(120, 180); // 2-3 minutos
                Log::info("⏸️ Pausa de {$pauseTime}s antes do próximo request para Claude API (título/ano)");
                sleep($pauseTime);
            }
        }

        return $results;
    }

    /**
     * 📈 Estatísticas das correções
     */
    public function getStats(): array
    {
        $pending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)->count();

        $processing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_PROCESSING)->count();

        $completed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)->count();

        $failed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
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
            "temp_article_view_{$slug}",
            "temp_article_amp_view_{$slug}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        Log::info("Cache limpo para artigo de título/ano: {$slug}", ['keys' => $cacheKeys]);
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
     * ✏️ Aplica correções no artigo temporário
     */
    protected function applyTitleYearCorrections(TempArticle $tempArticle, array $correctedData): bool
    {
        try {
            // Se Claude determina que não precisa atualizar
            if (!($correctedData['needs_update'] ?? true)) {
                Log::info("Claude determinou que {$tempArticle->slug} não precisa de atualização de título/ano: " . ($correctedData['reason'] ?? ''));
                return true;
            }

            $updated = false;
            $content = $tempArticle->content ?? [];
            $seoData = $tempArticle->seo_data ?? [];

            // 📝 Atualizar SEO data (page_title e meta_description)
            if (isset($correctedData['corrected_seo'])) {
                if (!empty($correctedData['corrected_seo']['page_title'])) {
                    $seoData['page_title'] = $correctedData['corrected_seo']['page_title'];
                    $updated = true;
                }

                if (!empty($correctedData['corrected_seo']['meta_description'])) {
                    $seoData['meta_description'] = $correctedData['corrected_seo']['meta_description'];
                    $updated = true;
                }
            }

            // ❓ Atualizar perguntas frequentes
            if (isset($correctedData['corrected_content']['perguntas_frequentes'])) {
                $content['perguntas_frequentes'] = $correctedData['corrected_content']['perguntas_frequentes'];
                $updated = true;
            }

            if ($updated) {
                // ⏰ Timestamp humanizado
                $humanizedTimestamp = $this->generateHumanizedTimestamp();

                $tempArticle->update([
                    'content' => $content,
                    'seo_data' => $seoData,
                    'updated_at' => $humanizedTimestamp
                ]);

                Log::info("Artigo de título/ano atualizado para {$tempArticle->slug} com timestamp humanizado: {$humanizedTimestamp}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Erro ao aplicar correções de título/ano em {$tempArticle->slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 🎭 Gera timestamp humanizado
     */
    private function generateHumanizedTimestamp(): string
    {
        $now = now();
        $now->subHours(4);

        $minutesVariation = rand(-3, 3);
        $secondsVariation = rand(-59, 59);

        $humanizedTime = $now->copy()
            ->addMinutes($minutesVariation)
            ->addSeconds($secondsVariation);

        $microseconds = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        $utcTime = $humanizedTime->utc();
        $humanizedTimestamp = $utcTime->format('Y-m-d\TH:i:s.') . $microseconds . 'Z';

        return $humanizedTimestamp;
    }
}