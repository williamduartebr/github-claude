<?php

namespace Src\GenericArticleGenerator\Application\Services;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * GenerationClaudeApiService - Geração de Artigos via Anthropic API
 * 
 * MODELOS DISPONÍVEIS (2025):
 * - claude-3-7-sonnet-20250219: Standard (econômico, rápido) - 2.3x
 * - claude-sonnet-4-20250514: Intermediate (balanceado) - 3.5x
 * - claude-sonnet-4-5-20250929: Premium (máxima qualidade) - 4.0x
 * 
 * FLUXO DE GERAÇÃO:
 * 1. Recebe título + categoria
 * 2. Monta prompt específico
 * 3. Chama API Anthropic
 * 4. Valida e retorna JSON
 * 5. Registra custo e performance
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Atualizado para modelos 2025
 */
class GenerationClaudeApiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    // Configurações dos modelos atualizados
    private const MODELS = [
        'standard' => [
            'id' => 'claude-3-7-sonnet-20250219',
            'max_tokens' => 10000,
            'temperature' => 0.2,
            'cost_multiplier' => 2.3,
            'timeout' => 180,
            'description' => 'Standard - Econômico e Eficiente'
        ],
        'intermediate' => [
            'id' => 'claude-sonnet-4-20250514',
            'max_tokens' => 10000,
            'temperature' => 0.15,
            'cost_multiplier' => 3.5,
            'timeout' => 240,
            'description' => 'Intermediate - Balanceado (Sonnet 4.0)'
        ],
        'premium' => [
            'id' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 10000,
            'temperature' => 0.1,
            'cost_multiplier' => 4.0,
            'timeout' => 300,
            'description' => 'Premium - Máxima Qualidade (Sonnet 4.5)'
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
     * @param string $model standard|intermediate|premium
     * @return array
     */
    public function generateArticle(array $params, string $model = 'standard'): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Claude API Key não configurada');
        }

        $modelConfig = self::MODELS[$model] ?? self::MODELS['standard'];
        $startTime = microtime(true);

        try {
            // Montar prompt
            $prompt = $this->buildPrompt($params);

            // Chamar API
            $response = $this->callApi($prompt, $modelConfig);

            // Processar resposta
            $generatedJson = $this->processResponse($response, $params);

            // Validar JSON
            $this->validateGeneratedJson($generatedJson);

            // Registrar estatísticas
            $executionTime = round(microtime(true) - $startTime, 2);
            $this->recordStats($model, $executionTime, true, strlen(json_encode($generatedJson)));

            Log::info('Claude API: Artigo gerado com sucesso', [
                'model' => $modelConfig['id'],
                'model_key' => $model,
                'title' => $params['title'],
                'execution_time' => $executionTime,
                'cost_multiplier' => $modelConfig['cost_multiplier']
            ]);

            return [
                'success' => true,
                'json' => $generatedJson,
                'model' => $model,
                'cost' => $modelConfig['cost_multiplier'],
                'execution_time' => $executionTime,
                'tokens_estimated' => $this->estimateTokens($generatedJson)
            ];
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $this->recordStats($model, $executionTime, false, 0);

            Log::error('Claude API: Erro na geração', [
                'model' => $modelConfig['id'],
                'model_key' => $model,
                'title' => $params['title'] ?? 'unknown',
                'error' => $e->getMessage(),
                'execution_time' => $executionTime
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $model,
                'cost' => 0,
                'execution_time' => $executionTime
            ];
        }
    }

    /**
     * Construir prompt para geração de artigo (OTIMIZADO - SEM CAMPOS REDUNDANTES)
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
          "context": "Contexto brasileiro APENAS se relevante (deixe vazio se não for necessário)"
        }
      },
      
      {
        "block_id": "tldr-001",
        "block_type": "tldr",
        "display_order": 2,
        "heading": "Resposta Rápida",
        "content": {
          "answer": "Resposta direta em 2-3 linhas",
          "key_points": [
            "Ponto 1 com números concretos",
            "Ponto 2 com custo/economia",
            "Ponto 3 com benefício",
            "Ponto 4 acionável",
            "Ponto 5 prático"
          ]
        }
      }
    ]
  },
  
  "formated_updated_at": "15 de janeiro de 2025",
  "canonical_url": "https://mercadoveiculos.com.br/info/slug-do-artigo"
}
```

# INSTRUÇÕES CRÍTICAS

## 1. TIPOS DE BLOCOS DISPONÍVEIS (use 10-15 blocos):

intro, tldr (OBRIGATÓRIOS), text, comparison, table, testimonial, steps, cost, decision, alert, myth, list, timeline, faq (OBRIGATÓRIO), conclusion (OBRIGATÓRIO)

## 2. CONTEÚDO

- 2.500-3.500 palavras (10-13 min leitura)
- Números concretos: R$, km, %, anos
- Casos reais: nomes fictícios brasileiros + veículo + ano
- Tabelas: dados brasileiros (R$, marcas locais)
- FAQs: 5 perguntas estratégicas com respostas práticas
- Linguagem: casual mas profissional, brasileiro

## 3. SEO

- Keyword no título + ano atual (2025)
- Meta description: benefício claro, 150-160 chars
- Secondary keywords: 4-5 termos relacionados
- Related articles: 3 slugs relacionados

## 4. QUALIDADE

- Experiência real: baseado em casos práticos
- Dados verificáveis: preços, durações, estatísticas
- Acionável: leitor sabe exatamente o que fazer
- Sem fluff: direto ao ponto, zero enrolação

# IMPORTANTE

- Retorne APENAS o JSON (sem ```json, sem explicações)
- JSON deve ser válido (use aspas duplas, escape correto)
- Todos os campos obrigatórios devem estar presentes

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
        // Extrair conteúdo
        $content = $response['content'][0]['text'] ?? '';

        if (empty($content)) {
            throw new \Exception('API retornou conteúdo vazio');
        }

        // Limpar markdown se houver
        $content = $this->cleanMarkdown($content);

        // Decodificar JSON
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido retornado pela API: ' . json_last_error_msg());
        }

        // Adicionar campos do banco (não vem da API)
        $json['category_id'] = $params['category_id'];
        $json['category_name'] = $params['category_name'];
        $json['category_slug'] = $params['category_slug'];
        $json['subcategory_id'] = $params['subcategory_id'];
        $json['subcategory_name'] = $params['subcategory_name'];
        $json['subcategory_slug'] = $params['subcategory_slug'];

        return $json;
    }

    /**
     * Limpar markdown do JSON
     */
    private function cleanMarkdown(string $content): string
    {
        // Remover ```json e ``` se houver
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        return trim($content);
    }

    /**
     * Validar JSON gerado
     */
    private function validateGeneratedJson(array $json): void
    {
        $requiredFields = [
            'title',
            'slug',
            'template',
            'seo_data',
            'metadata'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($json[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }

        // Validar content_blocks
        if (empty($json['metadata']['content_blocks'])) {
            throw new \Exception('content_blocks vazio ou ausente');
        }

        $blocks = $json['metadata']['content_blocks'];
        $blockTypes = array_column($blocks, 'block_type');

        // Verificar blocos obrigatórios
        $requiredBlocks = ['intro', 'tldr', 'faq', 'conclusion'];
        foreach ($requiredBlocks as $required) {
            if (!in_array($required, $blockTypes)) {
                throw new \Exception("Bloco obrigatório ausente: {$required}");
            }
        }

        // Validar quantidade mínima de blocos
        if (count($blocks) < 8) {
            throw new \Exception('Número insuficiente de blocos (mínimo: 8)');
        }
    }

    /**
     * Estimar tokens (aproximado)
     */
    private function estimateTokens(array $json): int
    {
        $jsonString = json_encode($json);
        return (int) ceil(strlen($jsonString) / 4); // Aproximação: 1 token ≈ 4 chars
    }

    /**
     * Registrar estatísticas
     */
    private function recordStats(string $model, float $executionTime, bool $success, int $size): void
    {
        $key = "claude_api_stats_{$model}_" . now()->format('Y-m-d');

        $stats = Cache::get($key, [
            'model' => $model,
            'date' => now()->format('Y-m-d'),
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_execution_time' => 0,
            'total_size' => 0
        ]);

        $stats['total_calls']++;
        $stats['total_execution_time'] += $executionTime;
        $stats['total_size'] += $size;

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
     * Listar modelos disponíveis
     */
    public function getAvailableModels(): array
    {
        return array_map(function ($config, $key) {
            return [
                'key' => $key,
                'id' => $config['id'],
                'description' => $config['description'],
                'cost_multiplier' => $config['cost_multiplier']
            ];
        }, self::MODELS, array_keys(self::MODELS));
    }
}
