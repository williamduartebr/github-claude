<?php

namespace Src\GenericArticleGenerator\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * GenerationClaudeApiService - Geração de Artigos via Anthropic API
 * 
 * @author Claude Sonnet 4.5
 * @version 2.1 - CORRIGIDO: Estrutura do bloco COMPARISON
 */
class NewGenerationClaudeApiService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    private const MODELS = [
        'standard' => [
            'id' => 'claude-3-7-sonnet-20250219',
            'max_tokens' => 12000,
            'temperature' => 0.2,
            'cost_multiplier' => 2.3,
            'timeout' => 180,
            'description' => 'Standard - Econômico e Eficiente'
        ],
        'intermediate' => [
            'id' => 'claude-sonnet-4-20250514',
            'max_tokens' => 12000,
            'temperature' => 0.15,
            'cost_multiplier' => 3.5,
            'timeout' => 240,
            'description' => 'Intermediate - Balanceado (Sonnet 4.0)'
        ],
        'premium' => [
            'id' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 12000,
            'temperature' => 0.1,
            'cost_multiplier' => 4.0,
            'timeout' => 300,
            'description' => 'Premium - Máxima Qualidade (Sonnet 4.5)'
        ]
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', env('ANTHROPIC_API_KEY'));
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Construir prompt para geração de artigo
     * 
     * ✅ CORRIGIDO: Estrutura do bloco COMPARISON agora é consistente
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

# ⚠️ ESTRUTURAS DETALHADAS POR TIPO DE BLOCO

## BLOCO: comparison (COMPARAÇÃO)

**ATENÇÃO CRÍTICA**: Este bloco tem estrutura RÍGIDA. Siga EXATAMENTE o formato abaixo.

### ✅ ESTRUTURA OBRIGATÓRIA:

```json
{
  "block_id": "comparison-001",
  "block_type": "comparison",
  "display_order": 5,
  "heading": "Título da Comparação (ex: 5W30 vs 5W40: Principais Diferenças)",
  "content": {
    "intro": "Texto introdutório explicando o que será comparado (1-2 frases)",
    "items": [
      {
        "aspect": "Nome do aspecto sendo comparado",
        "option_a": "Descrição completa da Opção A",
        "option_b": "Descrição completa da Opção B"
      }
    ],
    "conclusion": "Conclusão geral da comparação (1-2 frases)"
  }
}
```

### 📋 EXEMPLO REAL - Comparação de Óleos 5W30 vs 5W40:

```json
{
  "block_id": "comparison-001",
  "block_type": "comparison",
  "display_order": 5,
  "heading": "5W30 vs 5W40: Principais Diferenças",
  "content": {
    "intro": "Para entender melhor as implicações da mistura, vamos comparar as características principais desses dois tipos de óleo:",
    "items": [
      {
        "aspect": "Viscosidade a Frio",
        "option_a": "5W (mesma fluidez em baixas temperaturas)",
        "option_b": "5W (mesma fluidez em baixas temperaturas)"
      },
      {
        "aspect": "Viscosidade a Quente (100°C)",
        "option_a": "30 (menos viscoso, melhor economia)",
        "option_b": "40 (mais viscoso, melhor proteção)"
      },
      {
        "aspect": "Proteção do Motor",
        "option_a": "Boa proteção em condições normais",
        "option_b": "Excelente proteção em alta temperatura"
      },
      {
        "aspect": "Economia de Combustível",
        "option_a": "Melhor (menor atrito interno)",
        "option_b": "Menor (maior atrito, mais resistência)"
      },
      {
        "aspect": "Vedação de Motores Desgastados",
        "option_a": "Adequada para motores novos",
        "option_b": "Superior (ajuda vedar folgas maiores)"
      },
      {
        "aspect": "Aplicação Recomendada",
        "option_a": "Veículos modernos, uso urbano",
        "option_b": "Veículos de alta potência, uso severo"
      }
    ],
    "conclusion": "A principal diferença está na viscosidade a quente: o 5W30 é mais fluido (melhor economia), enquanto o 5W40 é mais viscoso (melhor proteção em altas temperaturas)."
  }
}
```

### ❌ ERROS COMUNS A EVITAR:

**NÃO FAÇA ISTO** (item por linha):
```json
{
  "items": [
    {"option_a": "5W", "option_b": "5W"},
    {"option_a": "30", "option_b": "40"}
  ]
}
```

**FAÇA ISTO** (aspecto completo):
```json
{
  "items": [
    {
      "aspect": "Viscosidade a Frio",
      "option_a": "5W (descrição completa)",
      "option_b": "5W (descrição completa)"
    }
  ]
}
```

### 📌 REGRAS RÍGIDAS:

1. **SEMPRE inclua o campo "aspect"** - NUNCA deixe vazio ou null
2. **"aspect" deve descrever O QUE está sendo comparado** (ex: "Viscosidade a Frio", "Custo", "Durabilidade")
3. **"option_a" e "option_b" devem ser descritivos e completos** (não apenas valores soltos)
4. **Mínimo 4 items, máximo 8 items** por comparação
5. **NUNCA repita o mesmo aspecto** em items diferentes
6. **"intro" e "conclusion" são obrigatórios**

### 🎯 VALIDAÇÃO AUTOMÁTICA:

Antes de retornar o JSON, verifique:
- [ ] Todos os "aspect" estão preenchidos?
- [ ] Nenhum "aspect" é null ou vazio?
- [ ] "option_a" e "option_b" têm descrições completas?
- [ ] Há entre 4-8 items?
- [ ] "intro" e "conclusion" existem?

## OUTROS BLOCOS (mantém estruturas existentes):

- **intro**: {text, highlight, context}
- **tldr**: {answer, key_points[]}
- **text**: {text, paragraphs[], emphasis}
- **table**: {intro, headers[], rows[], caption, conclusion}
- **list**: {intro, list_type, items[], conclusion}
- **alert**: {alert_type, title, message, details[], action}
- **steps**: {intro, steps[{number, title, description, details[], tip}], conclusion}
- **testimonial**: {quote, author, vehicle, context} OU {cases[]}
- **cost**: {intro, cost_items[], total_investment, savings[], roi, conclusion}
- **myth**: {intro, myths[{myth, reality, explanation, evidence}]}
- **faq**: {questions[{question, answer, related_topics[]}]}
- **decision**: {intro, scenarios[], conclusion}
- **timeline**: {intro, events[], conclusion}
- **conclusion**: {summary, key_takeaways[], final_thought, cta}

# IMPORTANTE

- Retorne APENAS o JSON (sem ```json, sem explicações)
- JSON deve ser válido (use aspas duplas, escape correto)
- Todos os campos obrigatórios devem estar presentes
- NUNCA deixe campos críticos como "aspect" vazios ou null

Gere o artigo agora.
PROMPT;
    }

    /**
     * Gerar artigo completo via API
     */
    public function generateArticle(array $params, string $model = 'standard'): array
    {
        $modelConfig = self::MODELS[$model] ?? self::MODELS['standard'];
        $startTime = microtime(true);

        try {
            $prompt = $this->buildPrompt($params);
            $response = $this->callApi($prompt, $modelConfig);
            $generatedJson = $this->processResponse($response, $params);
            $this->validateGeneratedJson($generatedJson);

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->recordStats($model, $executionTime, true, strlen(json_encode($generatedJson)));

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

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $model,
                'cost' => 0,
                'execution_time' => $executionTime
            ];
        }
    }

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

    private function processResponse(array $response, array $params): array
    {
        $content = $response['content'][0]['text'] ?? '';

        if (empty($content)) {
            throw new \Exception('API retornou conteúdo vazio');
        }

        $content = $this->cleanMarkdown($content);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido: ' . json_last_error_msg());
        }

        $json['category_id'] = $params['category_id'];
        $json['category_name'] = $params['category_name'];
        $json['category_slug'] = $params['category_slug'];
        $json['subcategory_id'] = $params['subcategory_id'];
        $json['subcategory_name'] = $params['subcategory_name'];
        $json['subcategory_slug'] = $params['subcategory_slug'];

        return $json;
    }

    private function cleanMarkdown(string $content): string
    {
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        return trim($content);
    }

    private function validateGeneratedJson(array $json): void
    {
        $requiredFields = ['title', 'slug', 'template', 'seo_data', 'metadata'];

        foreach ($requiredFields as $field) {
            if (!isset($json[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }

        if (empty($json['metadata']['content_blocks'])) {
            throw new \Exception('content_blocks vazio ou ausente');
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
            throw new \Exception('Número insuficiente de blocos (mínimo: 8)');
        }

        // ✅ NOVA VALIDAÇÃO: Verificar estrutura do bloco comparison
        $this->validateComparisonBlocks($blocks);
    }

    /**
     * ✅ NOVO: Validar estrutura específica do bloco comparison
     */
    private function validateComparisonBlocks(array $blocks): void
    {
        foreach ($blocks as $block) {
            if ($block['block_type'] !== 'comparison') {
                continue;
            }

            $items = $block['content']['items'] ?? [];
            
            if (empty($items)) {
                throw new \Exception('Bloco comparison sem items');
            }

            foreach ($items as $index => $item) {
                // Verificar se tem a estrutura correta
                if (empty($item['aspect'])) {
                    throw new \Exception("Bloco comparison item #{$index}: campo 'aspect' vazio ou ausente");
                }

                if (!isset($item['option_a']) || !isset($item['option_b'])) {
                    throw new \Exception("Bloco comparison item #{$index}: campos option_a/option_b ausentes");
                }
            }
        }
    }

    private function estimateTokens(array $json): int
    {
        return (int) ceil(strlen(json_encode($json)) / 4);
    }

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

    public function getModelConfig(string $model): ?array
    {
        return self::MODELS[$model] ?? null;
    }

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