<?php

namespace Src\ArticleGenerator\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class ArticleCorrectionService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
    }

    /**
     * Corrige introdução e dados SEO de um artigo
     */
    public function fixIntroductionAndSeo($articleSlug)
    {
        try {
            // Buscar o artigo
            $article = Article::where('slug', $articleSlug)->first();

            if (!$article) {
                throw new \Exception("Artigo não encontrado: {$articleSlug}");
            }

            // Verificar se já existe correção pendente
            $existingCorrection = ArticleCorrection::where('article_slug', $articleSlug)
                ->where('correction_type', ArticleCorrection::TYPE_INTRODUCTION_FIX)
                ->whereIn('status', [ArticleCorrection::STATUS_PENDING, ArticleCorrection::STATUS_PROCESSING])
                ->first();

            if ($existingCorrection) {
                Log::info("Correção já existe para o artigo: {$articleSlug}");
                return false;
            }

            // Extrair dados originais problemáticos
            $originalData = [
                'title' => $article->title,
                'introducao' => $article->content['introducao'] ?? '',
                'seo_data' => [
                    'page_title' => $article->seo_data['page_title'] ?? '',
                    'meta_description' => $article->seo_data['meta_description'] ?? ''
                ]
            ];

            // Criar registro de correção
            $correction = ArticleCorrection::createCorrection(
                $articleSlug,
                ArticleCorrection::TYPE_INTRODUCTION_FIX,
                $originalData,
                'Correção de introdução mal formatada e dados SEO via Claude API'
            );

            Log::info("Correção criada para artigo: {$articleSlug}, ID: {$correction->_id}");

            return $correction;
        } catch (\Exception $e) {
            Log::error("Erro ao criar correção para {$articleSlug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Processa uma correção usando Claude API
     */
    public function processCorrection(ArticleCorrection $correction)
    {
        try {
            $correction->markAsProcessing();

            // Buscar o artigo
            $article = Article::where('slug', $correction->article_slug)->first();

            if (!$article) {
                $correction->markAsFailed("Artigo não encontrado");
                return false;
            }

            Log::info("Processando correção para: {$correction->article_slug}");

            // Gerar prompt para correção
            $prompt = $this->createCorrectionPrompt($correction->original_data, $article);

            // Chamar Claude API
            $response = Http::timeout(120)->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2000,
                'temperature' => 0.3,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => "Você é um editor experiente especializado em conteúdo automotivo brasileiro. Seu trabalho é corrigir textos mal formatados, tornando-os fluidos e profissionais, mantendo todas as informações técnicas. Escreva sempre com pontuação natural e correta. Retorne apenas JSON com as correções solicitadas."
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
                $correctedData = $this->processApiResponse($content);

                if ($correctedData) {
                    // Aplicar as correções no artigo
                    $this->applyCorrections($article, $correctedData);

                    // Marcar correção como concluída
                    $correction->markAsCompleted($correctedData);

                    Log::info("Correção aplicada com sucesso para: {$correction->article_slug}");
                    return true;
                }
            }

            $correction->markAsFailed("Falha na API Claude: " . $response->body());
            return false;
        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("Erro ao processar correção: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria prompt para correção de introdução e SEO
     */
    protected function createCorrectionPrompt($originalData, $article)
    {
        $title = $originalData['title'];
        $introducao = $originalData['introducao'];
        $pageTitle = $originalData['seo_data']['page_title'];
        $metaDescription = $originalData['seo_data']['meta_description'];

        $brand = $article->extracted_entities['marca'] ?? '';
        $model = $article->extracted_entities['modelo'] ?? '';

        return <<<EOT
        
IMPORTANTE SOBRE PONTUAÇÃO: Escreva textos fluidos e naturais. NÃO use pontos no meio de frases. Use vírgulas para separar ideias relacionadas. 

Exemplos CORRETOS:
- "especificações técnicas, capacidades e intervalos"
- "segurança, economia e desempenho"
- "Como veículo de uso misto (urbano e estrada), a escolha correta"

Exemplos ERRADOS:
- "especificações técnicas. Capacidades e intervalos"
- "segurança. Economia e desempenho"
- "Como veículo de uso misto (urbano e estrada). A escolha correta"

Preciso que você corrija os seguintes elementos de um artigo sobre óleo recomendado que foram mal formatados:

**TÍTULO ATUAL:** {$title}
**INTRODUÇÃO ATUAL:** {$introducao}
**PAGE TITLE ATUAL:** {$pageTitle}
**META DESCRIPTION ATUAL:** {$metaDescription}

**MARCA:** {$brand}
**MODELO:** {$model}

PROBLEMAS IDENTIFICADOS:
1. A introdução tem texto mal formatado com frases interrompidas
2. Possíveis duplicações ou texto fora de lugar
3. SEO titles podem estar inconsistentes
4. Pontuação inadequada com pontos no meio de frases

CORREÇÕES NECESSÁRIAS:
- Corrija a introdução mantendo o mesmo conteúdo técnico, mas com texto fluido e profissional
- Use pontuação natural: vírgulas para separar ideias relacionadas, pontos apenas no final de frases completas
- Ajuste o título se necessário para melhor clareza
- Corrija page_title e meta_description para serem consistentes e otimizados

Retorne APENAS um JSON com esta estrutura:

{
  "corrected_title": "título corrigido se necessário",
  "corrected_introducao": "introdução corrigida e fluida com pontuação natural",
  "corrected_seo": {
    "page_title": "page title otimizado",
    "meta_description": "meta description clara e atrativa"
  }
}
EOT;
    }

    /**
     * Processa resposta da API Claude
     */
    protected function processApiResponse($content)
    {
        // Limpar formatação markdown se presente
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        // Extrair JSON
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');

        if ($firstBrace !== false && $lastBrace !== false) {
            $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $result = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($result)) {
                return $result;
            }

            Log::error("Erro decodificando JSON de correção: " . json_last_error_msg());
        }

        return null;
    }

    /**
     * Aplica as correções no artigo
     */
    protected function applyCorrections(Article $article, $correctedData)
    {
        $updateData = [];

        // Corrigir título se fornecido
        if (!empty($correctedData['corrected_title'])) {
            $updateData['title'] = $correctedData['corrected_title'];
        }

        // Corrigir introdução
        if (!empty($correctedData['corrected_introducao'])) {
            $content = $article->content;
            $content['introducao'] = $correctedData['corrected_introducao'];
            $updateData['content'] = $content;
        }

        // Corrigir dados SEO
        if (!empty($correctedData['corrected_seo'])) {
            $seoData = $article->seo_data;

            if (!empty($correctedData['corrected_seo']['page_title'])) {
                $seoData['page_title'] = $correctedData['corrected_seo']['page_title'];
            }

            if (!empty($correctedData['corrected_seo']['meta_description'])) {
                $seoData['meta_description'] = $correctedData['corrected_seo']['meta_description'];
            }

            $updateData['seo_data'] = $seoData;
        }

        // Atualizar timestamp
        $updateData['updated_at'] = now();

        // Aplicar as correções
        $article->update($updateData);

        // Limpar cache do artigo após aplicar correções
        $this->clearArticleCache($article->slug);

        Log::info("Correções aplicadas no artigo: {$article->slug}");
    }

    /**
     * Limpa o cache do artigo após correções
     */
    protected function clearArticleCache($slug)
    {
        try {
            // Cache keys baseados no controller fornecido
            $cacheKeys = [
                "article_view_{$slug}",     // Cache da versão normal
                "article_amp_view_{$slug}"  // Cache da versão AMP
            ];

            foreach ($cacheKeys as $cacheKey) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                Log::info("Cache limpo: {$cacheKey}");
            }

            Log::info("Cache do artigo '{$slug}' limpo com sucesso após correções");
        } catch (\Exception $e) {
            Log::warning("Erro ao limpar cache do artigo '{$slug}': " . $e->getMessage());
        }
    }

    /**
     * Corrige múltiplos artigos por slug
     */
    public function fixMultipleArticles(array $slugs)
    {
        $results = [];

        foreach ($slugs as $slug) {
            $result = $this->fixIntroductionAndSeo($slug);
            $results[$slug] = $result !== false;
        }

        return $results;
    }
}
