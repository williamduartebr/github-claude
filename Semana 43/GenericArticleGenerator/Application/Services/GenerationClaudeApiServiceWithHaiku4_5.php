<?php

namespace Src\GenericArticleGenerator\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * GenerationClaudeApiServiceWithHaiku4_5 - Geração de Artigos via Anthropic API
 * 
 * MODELOS DISPONÍVEIS (2025) - SEQUÊNCIA LÓGICA:
 * 
 * 1. claude-haiku-4-5-20251001 (PADRÃO)
 *    - Uso: Artigos padrão, conteúdo geral, prototipagem
 *    - Velocidade: Máxima (2-5s)
 *    - Custo: $0.80/MTok input, $4/MTok output
 *    - Multiplicador: 1.0x (baseline)
 * 
 * 2. claude-sonnet-4-5-20250929
 *    - Uso: Artigos complexos, análises profundas, SEO avançado
 *    - Velocidade: Rápida (5-15s)
 *    - Custo: $3/MTok input, $15/MTok output
 *    - Multiplicador: 4.0x (4x mais caro)
 * 
 * 3. claude-opus-4-1-20250805
 *    - Uso: Relatórios executivos, pesquisa aprofundada
 *    - Velocidade: Moderada (15-30s)
 *    - Custo: $15/MTok input, $75/MTok output
 *    - Multiplicador: 5.0x (5x mais caro)
 * 
 * RECOMENDAÇÃO: Use Haiku para 85-90% dos casos. Sonnet/Opus apenas quando necessário.
 * 
 * @author Claude Haiku 4.5
 * @version 2.1 - Otimizado para Haiku
 */
class GenerationClaudeApiServiceWithHaiku4_5
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    // Configurações dos modelos 2025 - Sequência Lógica
    private const MODELS = [
        'haiku' => [
            'id' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 8000,
            'temperature' => 0.3,
            'cost_multiplier' => 1.0,
            'timeout' => 120,
            'tier' => 'economy',
            'description' => '⚡ Haiku 4.5 - Rápido & Econômico (PADRÃO)',
            'input_cost' => 0.80,
            'output_cost' => 4.0,
            'use_case' => 'Artigos padrão, conteúdo geral, prototipagem'
        ],
        'sonnet' => [
            'id' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 10000,
            'temperature' => 0.2,
            'cost_multiplier' => 4.0,
            'timeout' => 180,
            'tier' => 'premium',
            'description' => '🎯 Sonnet 4.5 - Balanceado & Inteligente',
            'input_cost' => 3.0,
            'output_cost' => 15.0,
            'use_case' => 'Artigos complexos, análises profundas, SEO avançado'
        ],
        'opus' => [
            'id' => 'claude-opus-4-1-20250805',
            'max_tokens' => 12000,
            'temperature' => 0.1,
            'cost_multiplier' => 5.0,
            'timeout' => 300,
            'tier' => 'enterprise',
            'description' => '🚀 Opus 4.1 - Máxima Qualidade',
            'input_cost' => 15.0,
            'output_cost' => 75.0,
            'use_case' => 'Relatórios executivos, pesquisa aprofundada'
        ]
    ];

    private string $apiKey;
    private array $stats = [];

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', env('ANTHROPIC_API_KEY'));
    }

    /**
     * Verificar se API está configurada
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Gerar artigo completo via API
     * 
     * @param array $params [title, category_name, subcategory_name, category_slug, subcategory_slug]
     * @param string $model haiku|sonnet|opus (padrão: haiku)
     * @return array
     */
    public function generateArticle(array $params, string $model = 'haiku'): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Claude API Key não configurada');
        }

        // Validar modelo
        if (!isset(self::MODELS[$model])) {
            Log::warning("Modelo '{$model}' não encontrado. Usando 'haiku' como fallback");
            $model = 'haiku';
        }

        $modelConfig = self::MODELS[$model];
        $startTime = microtime(true);

        try {
            // Montar prompt
            $prompt = $this->buildPrompt($params);

            // Chamar API
            $response = $this->callApi($prompt, $modelConfig);

            // Extrair token usage
            $inputTokens = $response['usage']['input_tokens'] ?? 0;
            $outputTokens = $response['usage']['output_tokens'] ?? 0;

            // Processar resposta
            $generatedJson = $this->processResponse($response, $params);

            // Validar JSON
            $this->validateGeneratedJson($generatedJson);

            // Calcular custo real
            $realCost = $this->calculateCost($inputTokens, $outputTokens, $modelConfig);

            // Registrar estatísticas
            $executionTime = round(microtime(true) - $startTime, 2);
            $this->recordStats($model, $executionTime, true, strlen(json_encode($generatedJson)), $inputTokens, $outputTokens, $realCost);

            Log::info('Claude API: Artigo gerado com sucesso', [
                'model' => $modelConfig['id'],
                'model_key' => $model,
                'title' => $params['title'],
                'execution_time' => $executionTime,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'real_cost_usd' => number_format($realCost, 4),
                'cost_multiplier' => $modelConfig['cost_multiplier']
            ]);

            return [
                'success' => true,
                'json' => $generatedJson,
                'model' => $model,
                'model_id' => $modelConfig['id'],
                'cost_multiplier' => $modelConfig['cost_multiplier'],
                'real_cost_usd' => number_format($realCost, 4),
                'execution_time' => $executionTime,
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                    'total' => $inputTokens + $outputTokens
                ]
            ];
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $this->recordStats($model, $executionTime, false, 0, 0, 0, 0);

            Log::error('Claude API: Erro na geração', [
                'model' => $modelConfig['id'] ?? 'unknown',
                'model_key' => $model,
                'title' => $params['title'] ?? 'unknown',
                'error' => $e->getMessage(),
                'execution_time' => $executionTime
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $model,
                'cost_multiplier' => 0,
                'real_cost_usd' => '0.0000',
                'execution_time' => $executionTime
            ];
        }
    }

    /**
     * Construir prompt para geração de artigo
     */
    private function buildPrompt(array $params): string
    {
        $title = $params['title'];
        $categoryName = $params['category_name'];
        $subcategoryName = $params['subcategory_name'];

        return <<<PROMPT
Você é um especialista em criar artigos técnicos automotivos para o mercado brasileiro.

# TAREFA
Gere um artigo completo em formato JSON baseado no título fornecido.

# TÍTULO DO ARTIGO
"{$title}"

# CONTEXTO
Categoria: {$categoryName}
Subcategoria: {$subcategoryName}

# ESTRUTURA JSON OBRIGATÓRIA

Você DEVE retornar APENAS um JSON válido (sem markdown, sem explicações) com esta estrutura:

```json
{
  "title": "título do artigo",
  "slug": "slug-do-artigo",
  "template": "generic_article",
  
  "seo_data": {
    "page_title": "Título SEO com ano [2025]",
    "meta_description": "150-160 caracteres persuasivos",
    "h1": "H1 otimizado",
    "primary_keyword": "palavra-chave principal",
    "secondary_keywords": ["kw1", "kw2", "kw3", "kw4"],
    "canonical_url": "https://mercadoveiculos.com.br/info/slug-do-artigo",
    "og_title": "Título para redes sociais",
    "og_description": "Descrição para redes sociais",
    "og_image": "https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/categoria.png"
  },

  "metadata": {
    "article_metadata": {
      "article_topic": "oil|spark_plug|transmission|suspension|etc",
      "article_category": "guide|comparison|troubleshooting|experience"
    },
    "metadata": {
      "author": "Equipe Mercado Veículos",
      "created_at": "2025-01-15",
      "updated_at": "2025-01-15",
      "word_count": 2500,
      "reading_time": 10,
      "difficulty": "básico|intermediário|avançado",
      "experience_based": true,
      "related_articles": ["slug1", "slug2", "slug3"]
    },
    
    "content_blocks": [
      {
        "block_id": "intro-001",
        "block_type": "intro",
        "display_order": 1,
        "content": {
          "text": "Texto introdutório de 3-4 frases...",
          "highlight": "Frase de destaque impactante",
          "context": "Contexto brasileiro se relevante (deixe vazio se não)"
        }
      },
      
      {
        "block_id": "tldr-001",
        "block_type": "tldr",
        "display_order": 2,
        "heading": "Resposta Rápida",
        "content": {
          "answer": "Resposta direta em 2-3 linhas",
          "key_points": ["Ponto 1", "Ponto 2", "Ponto 3", "Ponto 4", "Ponto 5"]
        }
      }
    ]
  },
  
  "formated_updated_at": "15 de janeiro de 2025",
  "canonical_url": "https://mercadoveiculos.com.br/info/slug-do-artigo"
}
```

# INSTRUÇÕES CRÍTICAS

## 1. TIPOS DE BLOCOS (use 10-15):
intro, tldr (OBRIGATÓRIOS), text, comparison, table, testimonial, steps, cost, decision, alert, myth, list, timeline, faq (OBRIGATÓRIO), conclusion (OBRIGATÓRIO)

## 2. CONTEÚDO
- 2.500-3.500 palavras
- Números concretos: R\$, km, %, anos
- Casos reais com nomes fictícios brasileiros
- Tabelas com dados brasileiros
- FAQs: 5 perguntas estratégicas
- Linguagem: casual mas profissional

## 3. QUALIDADE
- Experiência real baseada em casos práticos
- Dados verificáveis: preços, durações, estatísticas
- Acionável: leitor sabe exatamente o que fazer
- Zero fluff, direto ao ponto

# IMPORTANTE
- Retorne APENAS o JSON (sem \`\`\`json, sem explicações)
- JSON deve ser válido (aspas duplas, escapes corretos)
- Todos os campos obrigatórios presentes

Gere o artigo agora.
PROMPT;
    }

    /**
     * Chamar API Anthropic
     */
    private function callApi(string $prompt, array $modelConfig): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])
            ->timeout($modelConfig['timeout'])
            ->post(self::API_URL, [
                'model' => $modelConfig['id'],
                'max_tokens' => $modelConfig['max_tokens'],
                'temperature' => $modelConfig['temperature'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('API Error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Processar resposta da API
     */
    private function processResponse(array $response, array $params): array
    {
        $content = $response['content'][0]['text'] ?? '';

        if (empty($content)) {
            throw new \Exception('API retornou conteúdo vazio');
        }

        // Limpar markdown
        $content = $this->cleanMarkdown($content);

        // Decodificar JSON
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Limpar markdown do JSON
     */
    private function cleanMarkdown(string $content): string
    {
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        return trim($content);
    }

    /**
     * Validar JSON gerado
     */
    private function validateGeneratedJson(array $json): void
    {
        $requiredFields = ['title', 'slug', 'template', 'seo_data', 'metadata'];

        foreach ($requiredFields as $field) {
            if (!isset($json[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }

        if (empty($json['metadata']['content_blocks'])) {
            throw new \Exception('content_blocks vazio');
        }

        $blocks = $json['metadata']['content_blocks'];
        $blockTypes = array_column($blocks, 'block_type');

        $requiredBlocks = ['intro', 'tldr', 'faq', 'conclusion'];
        foreach ($requiredBlocks as $required) {
            if (!in_array($required, $blockTypes)) {
                throw new \Exception("Bloco obrigatório ausente: {$required}");
            }
        }

        if (count($blocks) < 8) {
            throw new \Exception('Mínimo 8 blocos obrigatório');
        }
    }

    /**
     * Calcular custo real em USD
     */
    private function calculateCost(int $inputTokens, int $outputTokens, array $modelConfig): float
    {
        $inputCost = ($inputTokens / 1_000_000) * $modelConfig['input_cost'];
        $outputCost = ($outputTokens / 1_000_000) * $modelConfig['output_cost'];
        return $inputCost + $outputCost;
    }

    /**
     * Registrar estatísticas
     */
    private function recordStats(string $model, float $executionTime, bool $success, int $size, int $inputTokens, int $outputTokens, float $cost): void
    {
        $key = "claude_api_stats_{$model}_" . now()->format('Y-m-d');

        $stats = Cache::get($key, [
            'model' => $model,
            'date' => now()->format('Y-m-d'),
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_execution_time' => 0,
            'total_size' => 0,
            'total_input_tokens' => 0,
            'total_output_tokens' => 0,
            'total_cost_usd' => 0
        ]);

        $stats['total_calls']++;
        $stats['total_execution_time'] += $executionTime;
        $stats['total_size'] += $size;
        $stats['total_input_tokens'] += $inputTokens;
        $stats['total_output_tokens'] += $outputTokens;
        $stats['total_cost_usd'] += $cost;

        if ($success) {
            $stats['successful_calls']++;
        } else {
            $stats['failed_calls']++;
        }

        Cache::put($key, $stats, now()->addDays(7));
    }

    /**
     * Obter estatísticas
     */
    public function getStats(?string $model = null, ?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        $models = $model ? [$model] : array_keys(self::MODELS);

        $allStats = [];
        foreach ($models as $m) {
            $key = "claude_api_stats_{$m}_{$date}";
            $stats = Cache::get($key);
            if ($stats) {
                $allStats[$m] = $stats;
            }
        }

        return $allStats;
    }

    /**
     * Obter configuração de modelo
     */
    public function getModelConfig(string $model): ?array
    {
        return self::MODELS[$model] ?? null;
    }

    /**
     * Listar modelos disponíveis com informações completas
     */
    public function getAvailableModels(): array
    {
        return array_map(function ($config, $key) {
            return [
                'key' => $key,
                'id' => $config['id'],
                'tier' => $config['tier'],
                'description' => $config['description'],
                'cost_multiplier' => $config['cost_multiplier'],
                'input_cost_per_1m_tokens' => '$' . $config['input_cost'],
                'output_cost_per_1m_tokens' => '$' . $config['output_cost'],
                'use_case' => $config['use_case'],
                'timeout' => $config['timeout'] . 's'
            ];
        }, self::MODELS, array_keys(self::MODELS));
    }

    /**
     * Recomendar modelo baseado em cenário
     */
    public function recommendModel(string $scenario = 'standard'): array
    {
        $recommendations = [
            'standard' => 'haiku',      // Artigos padrão
            'complex' => 'sonnet',       // Análises profundas
            'enterprise' => 'opus',      // Relatórios executivos
            'budget' => 'haiku',         // Máxima economia
            'balanced' => 'sonnet',      // Melhor custo-benefício
            'quality' => 'opus'          // Máxima qualidade
        ];

        $model = $recommendations[$scenario] ?? 'haiku';
        return [
            'recommended_model' => $model,
            'config' => self::MODELS[$model],
            'scenario' => $scenario
        ];
    }
}