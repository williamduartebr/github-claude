<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class TireCorrectionService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * 🎯 Cria correções para artigos de pneus
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
                Log::error("Erro ao criar correção de pneu para {$slug}: " . $e->getMessage());
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
            ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->exists();

        if ($existingCorrection) {
            Log::debug("Artigo {$articleSlug} já possui correção de pneu (pulando)");
            return null;
        }

        // Extrair dados necessários
        $originalData = [
            'title' => $tempArticle->title,
            'template' => $tempArticle->template ?? 'when_to_change_tires',
            'domain' => $tempArticle->domain,
            'vehicle_data' => $tempArticle->vehicle_data ?? [],
            'current_content' => [
                'introducao' => $tempArticle->content['introducao'] ?? '',
                'consideracoes_finais' => $tempArticle->content['consideracoes_finais'] ?? ''
            ],
            'current_pressures' => [
                'empty_front' => $tempArticle->vehicle_data['pressures']['empty_front'] ?? 0,
                'empty_rear' => $tempArticle->vehicle_data['pressures']['empty_rear'] ?? 0,
                'loaded_front' => $tempArticle->vehicle_data['pressures']['loaded_front'] ?? 0,
                'loaded_rear' => $tempArticle->vehicle_data['pressures']['loaded_rear'] ?? 0,
                'max_front' => $tempArticle->vehicle_data['pressures']['max_front'] ?? 0,
                'max_rear' => $tempArticle->vehicle_data['pressures']['max_rear'] ?? 0,
                'spare' => $tempArticle->vehicle_data['pressures']['spare'] ?? 0,
                'pressure_display' => $tempArticle->vehicle_data['pressure_display'] ?? '',
                'pressure_loaded_display' => $tempArticle->vehicle_data['pressure_loaded_display'] ?? ''
            ]
        ];

        // Criar correção única
        $correction = ArticleCorrection::createCorrection(
            $articleSlug,
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            $originalData,
            'Correção humanizada de pressões e conteúdo de artigos sobre pneus via Claude API'
        );

        Log::info("Correção de pneu criada para: {$articleSlug}");
        return $correction;
    }

    /**
     * 🤖 Processa correção usando Claude API com prompt humanizado
     */
    public function processTireCorrection(ArticleCorrection $correction): bool
    {
        try {
            // ⏱️ Rate limiting - máximo 1 request por minuto
            $lastRequest = Cache::get('claude_last_request', 0);
            $timeSinceLastRequest = time() - $lastRequest;
            
            if ($timeSinceLastRequest < 60) {
                $waitTime = 60 - $timeSinceLastRequest;
                Log::info("⏸️ Aguardando {$waitTime}s para respeitar rate limit da Claude API");
                sleep($waitTime);
            }
            
            Cache::put('claude_last_request', time(), 300);

            $correction->markAsProcessing();

            $tempArticle = TempArticle::where('slug', $correction->article_slug)
                ->where('domain', 'when_to_change_tires')
                ->first();

            if (!$tempArticle) {
                $correction->markAsFailed("Artigo temporário não encontrado");
                return false;
            }

            // Prompt humanizado para Claude
            $prompt = $this->createHumanizedPrompt($tempArticle);

            $response = Http::retry(3, 2000) // 3 tentativas, 2s entre cada
                ->timeout(180) // Aumentar para 3 minutos
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
                    'system' => "Você é um especialista automotivo brasileiro focado em pneus e segurança veicular. Crie textos naturais, informativos e que conectem com o proprietário do veículo. Sempre corrija pressões incorretas com valores realistas. Retorne apenas JSON válido."
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->extractJsonFromResponse($content);

                if ($correctedData && $this->applyCorrections($tempArticle, $correctedData)) {
                    $this->clearArticleCache($correction->article_slug);
                    $correction->markAsCompleted($correctedData);
                    Log::info("✅ Correção de pneu aplicada em: {$correction->article_slug}");
                    return true;
                }
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                // Se for rate limit (429), aguardar mais tempo
                if ($statusCode === 429) {
                    Log::warning("⚠️ Rate limit atingido na Claude API, aguardando 5 minutos");
                    sleep(300); // 5 minutos
                }
                
                $correction->markAsFailed("Falha na API ({$statusCode}): " . $errorBody);
                return false;
            }

            $correction->markAsFailed("Falha na API: " . $response->body());
            return false;
        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar correção de pneu: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📝 Prompt humanizado focado em pneus e pressões
     */
    protected function createHumanizedPrompt(TempArticle $tempArticle): string
    {
        $vehicleData = $tempArticle->vehicle_data ?? [];
        $content = $tempArticle->content ?? [];

        $vehicleName = $vehicleData['vehicle_name'] ?? 'N/A';
        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
        $vehicleModel = $vehicleData['vehicle_model'] ?? 'N/A';
        $vehicleYear = $vehicleData['vehicle_year'] ?? 'N/A';
        $vehicleCategory = $vehicleData['vehicle_category'] ?? 'veículo';
        $isMotorcycle = $vehicleData['is_motorcycle'] ?? false;

        $currentIntro = $content['introducao'] ?? '';
        $currentConclusion = $content['consideracoes_finais'] ?? '';

        // Pressões atuais
        $pressures = $vehicleData['pressures'] ?? [];
        $emptyFront = $pressures['empty_front'] ?? 0;
        $emptyRear = $pressures['empty_rear'] ?? 0;
        $loadedFront = $pressures['loaded_front'] ?? 0;
        $loadedRear = $pressures['loaded_rear'] ?? 0;
        $maxFront = $pressures['max_front'] ?? 0;
        $maxRear = $pressures['max_rear'] ?? 0;
        $spare = $pressures['spare'] ?? 0;
        $pressureDisplay = $vehicleData['pressure_display'] ?? '';
        $pressureLoadedDisplay = $vehicleData['pressure_loaded_display'] ?? '';

        $vehicleTypeText = $isMotorcycle ? 'motocicleta' : 'veículo';

        return <<<EOT
Reescreva a introdução e considerações finais para um guia sobre quando trocar pneus, e corrija as pressões dos pneus se necessário.

**VEÍCULO:**
- Nome: {$vehicleName}
- Marca: {$vehicleBrand}
- Modelo: {$vehicleModel}
- Ano: {$vehicleYear}
- Categoria: {$vehicleCategory}
- Tipo: {$vehicleTypeText}

**PRESSÕES ATUAIS DOS PNEUS:**
- Dianteiro vazio: {$emptyFront} PSI
- Traseiro vazio: {$emptyRear} PSI
- Dianteiro carregado: {$loadedFront} PSI
- Traseiro carregado: {$loadedRear} PSI
- Máximo dianteiro: {$maxFront} PSI
- Máximo traseiro: {$maxRear} PSI
- Estepe: {$spare} PSI
- Display atual: "{$pressureDisplay}"
- Display carregado: "{$pressureLoadedDisplay}"

**CONTEÚDO ATUAL:**

**Introdução:**
"{$currentIntro}"

**Considerações Finais:**
"{$currentConclusion}"

**DIRETRIZES PARA REESCRITA:**

**Para a Introdução:**
- Conecte emocionalmente com o proprietário do {$vehicleTypeText}
- Enfatize a importância da segurança dos pneus
- Use linguagem acessível mas técnica
- Mencione segurança, economia e durabilidade dos pneus
- 2-3 frases impactantes sobre a importância dos pneus

**Para as Considerações Finais:**
- Reforce o valor da manutenção preventiva dos pneus
- Destaque benefícios de pneus em bom estado
- Inspire confiança nas decisões de manutenção
- Tom positivo e motivador sobre segurança
- 2-3 frases conclusivas sobre responsabilidade na manutenção

**CORREÇÃO DE PRESSÕES:**
- Verifique se as pressões estão corretas para o tipo de {$vehicleTypeText}
- Para carros: normalmente entre 28-35 PSI
- Para motos: normalmente entre 28-36 PSI dependendo do tipo
- Se loaded_front e loaded_rear estão em 0, defina valores realistas (+2-4 PSI do vazio)
- Se pressure_loaded_display está "0/0 PSI", corrija para valores realistas
- Mantenha coerência entre todos os valores

**VARIAÇÕES ESPERADAS:**
- Evite frases genéricas como "é importante manter"
- Use verbos de ação: "garante", "preserva", "protege"
- Inclua benefícios específicos: aderência, frenagem, economia de combustível
- Linguagem natural, como se fosse um especialista conversando

**RETORNE APENAS ESTE JSON:**
```json
{
  "needs_update": true|false,
  "reason": "explicação breve se precisa atualizar",
  "corrected_content": {
    "introducao": "nova introdução mais envolvente sobre pneus",
    "consideracoes_finais": "novas considerações finais motivadoras sobre segurança"
  },
  "corrected_pressures": {
    "empty_front": valor_numerico,
    "empty_rear": valor_numerico,
    "loaded_front": valor_numerico,
    "loaded_rear": valor_numerico,
    "max_front": valor_numerico,
    "max_rear": valor_numerico,
    "spare": valor_numerico,
    "pressure_display": "X/Y PSI",
    "pressure_loaded_display": "X/Y PSI"
  }
}
```

Se o conteúdo e pressões já estão excelentes, retorne "needs_update": false.
EOT;
    }

    /**
     * 📊 Busca artigos que nunca foram corrigidos
     */
    public function getAllTireArticleSlugs(int $limit = 1000): array
    {
        try {
            // Slugs já corrigidos
            $alreadyCorrected = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->distinct('article_slug')
                ->pluck('article_slug')
                ->toArray();

            // Artigos de pneus nunca corrigidos
            $query = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft');

            if (!empty($alreadyCorrected)) {
                $query->whereNotIn('slug', $alreadyCorrected);
            }

            $slugs = $query->limit($limit)->pluck('slug')->toArray();

            $alreadyCount = count($alreadyCorrected);
            $availableCount = count($slugs);

            Log::info("📊 Artigos de pneus para correção: {$availableCount} (já corrigidos: {$alreadyCount})");

            return $slugs;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar slugs de pneus para correção: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🧹 Limpar duplicatas
     */
    public function cleanAllDuplicates(): array
    {
        Log::info("🧹 Iniciando limpeza de correções de pneus duplicadas...");

        $results = [
            'articles_analyzed' => 0,
            'duplicates_found' => 0,
            'corrections_removed' => 0,
            'articles_cleaned' => []
        ];

        try {
            $articleSlugs = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->select('article_slug')
                ->groupBy('article_slug')
                ->havingRaw('count(*) > 1')
                ->pluck('article_slug');

            $results['articles_analyzed'] = $articleSlugs->count();

            foreach ($articleSlugs as $slug) {
                $corrections = ArticleCorrection::where('article_slug', $slug)
                    ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
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

            Log::info("✅ Limpeza de pneus concluída", $results);
            return $results;
        } catch (\Exception $e) {
            Log::error("Erro na limpeza de duplicatas de pneus: " . $e->getMessage());
            return $results;
        }
    }

    /**
     * ⚡ Processa todas as correções pendentes
     */
    public function processAllPendingCorrections(int $limit = 1): array // Reduzir limit para 1
    {
        $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->limit($limit)
            ->get();

        $results = ['processed' => 0, 'successful' => 0, 'failed' => 0];

        foreach ($corrections as $correction) {
            $results['processed']++;

            if ($this->processTireCorrection($correction)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }

            // ⏸️ Pausa mais longa para API Claude (2-3 minutos entre requests)
            if ($results['processed'] < count($corrections)) {
                $pauseTime = rand(120, 180); // 2-3 minutos
                Log::info("⏸️ Pausa de {$pauseTime}s antes do próximo request para Claude API");
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
        $pending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)->count();

        $processing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PROCESSING)->count();

        $completed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)->count();

        $failed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
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

        Log::info("Cache limpo para artigo de pneu: {$slug}", ['keys' => $cacheKeys]);
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
    protected function applyCorrections(TempArticle $tempArticle, array $correctedData): bool
    {
        try {
            // Se Claude determina que não precisa atualizar
            if (!($correctedData['needs_update'] ?? true)) {
                Log::info("Claude determinou que {$tempArticle->slug} não precisa de atualização: " . ($correctedData['reason'] ?? ''));
                return true;
            }

            $updated = false;
            $content = $tempArticle->content ?? [];
            $vehicleData = $tempArticle->vehicle_data ?? [];

            // Atualizar conteúdo
            if (isset($correctedData['corrected_content'])) {
                if (!empty($correctedData['corrected_content']['introducao'])) {
                    $content['introducao'] = $correctedData['corrected_content']['introducao'];
                    $updated = true;
                }

                if (!empty($correctedData['corrected_content']['consideracoes_finais'])) {
                    $content['consideracoes_finais'] = $correctedData['corrected_content']['consideracoes_finais'];
                    $updated = true;
                }
            }

            // 🔧 CORREÇÃO DAS PRESSÕES - ATUALIZAÇÃO COMPLETA DA ESTRUTURA
            if (isset($correctedData['corrected_pressures'])) {
                // Atualizar vehicle_data principal (fora do content)
                if (!isset($vehicleData['pressures'])) {
                    $vehicleData['pressures'] = [];
                }

                foreach ($correctedData['corrected_pressures'] as $key => $value) {
                    if (in_array($key, ['pressure_display', 'pressure_loaded_display'])) {
                        // Atualizar displays diretamente no vehicle_data
                        $vehicleData[$key] = $value;
                        $updated = true;
                    } else {
                        // Atualizar pressões dentro de vehicle_data.pressures
                        $vehicleData['pressures'][$key] = $value;
                        $updated = true;
                    }
                }

                // 🆕 TAMBÉM ATUALIZAR NO CONTENT.VEHICLE_DATA (estrutura dentro do content)
                if (isset($content['vehicle_data'])) {
                    // Atualizar displays no content.vehicle_data
                    if (isset($correctedData['corrected_pressures']['pressure_display'])) {
                        $content['vehicle_data']['pressure_display'] = $correctedData['corrected_pressures']['pressure_display'];
                    }
                    if (isset($correctedData['corrected_pressures']['pressure_loaded_display'])) {
                        $content['vehicle_data']['pressure_loaded_display'] = $correctedData['corrected_pressures']['pressure_loaded_display'];
                    }

                    // Atualizar pressões no content.vehicle_data.pressures
                    if (!isset($content['vehicle_data']['pressures'])) {
                        $content['vehicle_data']['pressures'] = [];
                    }
                    
                    foreach ($correctedData['corrected_pressures'] as $key => $value) {
                        if (!in_array($key, ['pressure_display', 'pressure_loaded_display'])) {
                            $content['vehicle_data']['pressures'][$key] = $value;
                        }
                    }
                }

                // 🆕 ATUALIZAR TAMBÉM AS REFERÊNCIAS NO CONTEÚDO TEXTUAL
                if (isset($correctedData['corrected_pressures']['pressure_display'])) {
                    $newPressureDisplay = $correctedData['corrected_pressures']['pressure_display'];
                    $vehicleName = $vehicleData['vehicle_name'] ?? 'veículo';
                    
                    // Atualizar referências de pressão no texto de fatores_durabilidade
                    if (isset($content['fatores_durabilidade']['calibragem_inadequada']['pressao_recomendada'])) {
                        $content['fatores_durabilidade']['calibragem_inadequada']['pressao_recomendada'] = 
                            "{$newPressureDisplay} para o {$vehicleName}";
                    }

                    // Atualizar referências em perguntas_frequentes
                    if (isset($content['perguntas_frequentes']) && is_array($content['perguntas_frequentes'])) {
                        foreach ($content['perguntas_frequentes'] as $index => $pergunta) {
                            if (isset($pergunta['resposta']) && strpos($pergunta['resposta'], 'PSI') !== false) {
                                // Substituir padrões antigos de pressão pelo novo
                                $content['perguntas_frequentes'][$index]['resposta'] = preg_replace(
                                    '/\d+\/\d+\s*PSI/',
                                    $newPressureDisplay,
                                    $pergunta['resposta']
                                );
                            }
                        }
                    }

                    // Atualizar em procedimento_verificacao.verificacao_pressao
                    if (isset($content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['vazio'])) {
                        $pressureParts = explode('/', str_replace(' PSI', '', $newPressureDisplay));
                        if (count($pressureParts) === 2) {
                            $content['procedimento_verificacao']['verificacao_pressao']['pressoes_recomendadas']['vazio'] = 
                                "{$pressureParts[0]} PSI (dianteiro) / {$pressureParts[1]} PSI (traseiro)";
                        }
                    }
                }
            }

            if ($updated) {
                // ⏰ Timestamp humanizado
                $humanizedTimestamp = $this->generateHumanizedTimestamp();

                $tempArticle->update([
                    'content' => $content,
                    'vehicle_data' => $vehicleData,
                    'updated_at' => $humanizedTimestamp
                ]);

                Log::info("Artigo de pneu atualizado para {$tempArticle->slug} com timestamp humanizado: {$humanizedTimestamp}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Erro ao aplicar correções de pneu em {$tempArticle->slug}: " . $e->getMessage());
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