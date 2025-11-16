<?php

namespace Src\GenericArticleGenerator\Application\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * GenerationClaudeApiService v3.1 - ATUALIZADO PARA CLAUDE 4 (2025)
 * 
 * 🎯 NOVIDADES v3.1:
 * - Modelos atualizados: Haiku 4.5, Sonnet 4.5, Opus 4, Opus 4.1
 * - Custos ajustados para realidade 2025
 * - Modelo econômico (Haiku 4.5) como padrão
 * - Batch API e Prompt Caching ready
 * 
 * MODELOS DISPONÍVEIS (Janeiro 2025):
 * - claude-haiku-4-5: Econômico (1.0x) - Rápido e barato
 * - claude-sonnet-4-5: Balanceado (3.0x) - Melhor custo-benefício
 * - claude-opus-4: Avançado (10.0x) - Raciocínio profundo
 * - claude-opus-4-1: Premium (12.5x) - Máxima qualidade
 * 
 * @author Claude Sonnet 4.5
 * @version 3.1 - Modelos Claude 4 (2025)
 */
class GenerationClaudeApiService
{
	private const API_URL = 'https://api.anthropic.com/v1/messages';
	private const API_VERSION = '2023-06-01';

	// Configurações dos modelos
	private const MODELS = [

		// ========================================
		// TIER 1: ECONÔMICO (Default)
		// ========================================
		'standard_default' => [
			'id' => 'claude-haiku-4-5',  // 🆕 HAIKU 4.5 (Novo!)
			'max_tokens' => 15000,
			'temperature' => 0.2,
			'cost_multiplier' => 1.0,  // Base (mais barato)
			'timeout' => 120,
			'description' => 'Econômico - Haiku 4.5 (Rápido e Barato)'
		],

		// ========================================
		// TIER 2: BALANCEADO (Recomendado)
		// ========================================
		'standard' => [
			'id' => 'claude-sonnet-4-5-20250929',  // ✅ SONNET 4.5
			'max_tokens' => 15000,
			'temperature' => 0.1,
			'cost_multiplier' => 3.0,  // 3x mais caro que Haiku
			'timeout' => 300,
			'description' => 'Balanceado - Sonnet 4.5 (Melhor Custo-Benefício)'
		],

		// ========================================
		// TIER 3: QUALIDADE MÁXIMA
		// ========================================
		'intermediate' => [
			'id' => 'claude-opus-4-20250514',  // 🔥 OPUS 4 (não 4.1)
			'max_tokens' => 15000,
			'temperature' => 0.05,
			'cost_multiplier' => 10.0,  // 10x mais caro que Haiku
			'timeout' => 360,
			'description' => 'Avançado - Opus 4 (Raciocínio Complexo)'
		],

		// ========================================
		// TIER 4: MÁXIMO PODER
		// ========================================
		'premium' => [
			'id' => 'claude-opus-4-1-20250514',  // 🚀 OPUS 4.1 (Top)
			'max_tokens' => 15000,
			'temperature' => 0.05,
			'cost_multiplier' => 12.5,  // 12.5x mais caro que Haiku
			'timeout' => 360,
			'description' => 'Premium - Opus 4.1 (Máxima Qualidade)'
		]
	];
	private string $apiKey;

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
	 * @param array $params [title, category_name, subcategory_name]
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
			// Montar prompt RÍGIDO
			$prompt = $this->buildRigidPrompt($params);

			// Chamar API
			$response = $this->callApi($prompt, $modelConfig);

			// Processar resposta
			$generatedJson = $this->processResponse($response, $params);

			// Validar JSON
			$this->validateGeneratedJson($generatedJson);

			// 🆕 VALIDAR E CORRIGIR ESTRUTURA DE BLOCOS
			$generatedJson = $this->validateAndFixBlockStructures($generatedJson);

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
	 * 🆕 PROMPT EXTREMAMENTE RÍGIDO COM ESTRUTURA EXATA DE CADA BLOCO
	 */
	private function buildRigidPrompt(array $params): string
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

# ⚠️ ATENÇÃO CRÍTICA: ESTRUTURA EXATA DOS BLOCOS

Você DEVE seguir EXATAMENTE as estruturas de blocos definidas abaixo. 
NÃO invente chaves diferentes. NÃO omita campos obrigatórios.
Use EXATAMENTE os nomes de chaves especificados.

---

## 📋 ESTRUTURA JSON OBRIGATÓRIA (RAIZ)

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
      // BLOCOS DETALHADOS ABAIXO
    ]
  },
  
  "formated_updated_at": "15 de janeiro de 2025",
  "canonical_url": "https://mercadoveiculos.com.br/info/slug-do-artigo"
}
```

---

## 🎯 ESTRUTURA EXATA DE CADA TIPO DE BLOCO

### 1️⃣ BLOCO: intro (OBRIGATÓRIO - Sempre o primeiro)

```json
{
  "block_id": "intro-001",
  "block_type": "intro",
  "display_order": 1,
  "content": {
    "text": "Texto introdutório de 3-4 frases que apresenta o problema/tema do artigo",
    "highlight": "Frase de destaque impactante com número/dado relevante",
    "context": ""
  }
}
```

**REGRAS:**
- `text`: string obrigatória (3-4 frases)
- `highlight`: string obrigatória (1 frase impactante)
- `context`: string VAZIA por padrão (preencher APENAS se houver informação exclusiva do Brasil)

---

### 2️⃣ BLOCO: tldr (OBRIGATÓRIO - Sempre o segundo)

```json
{
  "block_id": "tldr-001",
  "block_type": "tldr",
  "display_order": 2,
  "heading": "Resposta Rápida",
  "content": {
    "answer": "Resposta direta e objetiva em 2-3 linhas sobre a pergunta principal do artigo",
    "key_points": [
      "Ponto 1 com número concreto (ex: 30% de economia)",
      "Ponto 2 com custo/benefício (ex: R$ 150 economizados/ano)",
      "Ponto 3 com ação prática (ex: Trocar a cada 10.000km)",
      "Ponto 4 acionável",
      "Ponto 5 com dado técnico"
    ]
  }
}
```

**REGRAS:**
- `answer`: string obrigatória (2-3 linhas diretas)
- `key_points`: array com exatamente 5 strings
- Cada ponto deve ter números concretos (R$, %, km, anos)

---

### 3️⃣ BLOCO: text

```json
{
  "block_id": "text-001",
  "block_type": "text",
  "display_order": 3,
  "heading": "Título da Seção",
  "subheading": "Subtítulo Opcional",
  "content": {
    "text": "Texto corrido com múltiplos parágrafos separados por \\n\\n",
    "paragraphs": [],
    "emphasis": "Frase importante destacada (opcional)"
  }
}
```

**REGRAS:**
- Use `text` (string única com \\n\\n) OU `paragraphs` (array de strings)
- `emphasis`: opcional, use para destacar informação crítica

---

### 4️⃣ BLOCO: list

```json
{
  "block_id": "list-001",
  "block_type": "list",
  "display_order": 4,
  "heading": "Título da Lista",
  "content": {
    "intro": "Texto introdutório explicando a lista (opcional)",
    "list_type": "numbered",
    "items": [
      "**Item 1**: Descrição completa do primeiro item",
      "**Item 2**: Descrição completa do segundo item",
      "**Item 3**: Descrição completa do terceiro item"
    ],
    "conclusion": "Texto de conclusão da lista (opcional)"
  }
}
```

**REGRAS:**
- `list_type`: "numbered" | "bullet" | "checklist"
- `items`: array de strings (use **negrito** para destacar)
- Mínimo 3 items, máximo 10

---

### 5️⃣ BLOCO: comparison

```json
{
  "block_id": "comparison-001",
  "block_type": "comparison",
  "display_order": 5,
  "heading": "Comparação: X vs Y",
  "content": {
    "intro": "Texto explicando a comparação",
    "items": [
      {
        "name": "Opção A (ex: Óleo Mineral)",
        "pros": [
          "Vantagem 1 com dado concreto",
          "Vantagem 2 com custo/benefício"
        ],
        "cons": [
          "Desvantagem 1",
          "Desvantagem 2"
        ],
        "best_for": "Para quem roda menos de 10.000km/ano",
        "cost": "R$ 80 - R$ 120"
      },
      {
        "name": "Opção B (ex: Óleo Sintético)",
        "pros": ["Vantagem 1", "Vantagem 2"],
        "cons": ["Desvantagem 1"],
        "best_for": "Para uso intenso e alta quilometragem",
        "cost": "R$ 250 - R$ 400"
      }
    ],
    "conclusion": "Resumo da comparação com recomendação"
  }
}
```

---

### 6️⃣ BLOCO: table

```json
{
  "block_id": "table-001",
  "block_type": "table",
  "display_order": 6,
  "heading": "Título da Tabela",
  "content": {
    "intro": "Texto introdutório (opcional)",
    "headers": ["Coluna 1", "Coluna 2", "Coluna 3"],
    "rows": [
      ["Dado 1.1", "Dado 1.2", "Dado 1.3"],
      ["Dado 2.1", "Dado 2.2", "Dado 2.3"]
    ],
    "caption": "Legenda da tabela (opcional)",
    "footer": "Nota de rodapé (opcional)",
    "conclusion": "Conclusão após tabela (opcional)"
  }
}
```

**REGRAS:**
- `headers`: array de strings (nomes das colunas)
- `rows`: array de arrays (cada linha é um array)
- Número de itens em cada row DEVE ser igual ao número de headers

---

### 7️⃣ BLOCO: testimonial

```json
{
  "block_id": "testimonial-001",
  "block_type": "testimonial",
  "display_order": 7,
  "heading": "Experiência Real: Título do Caso",
  "content": {
    "quote": "Depoimento completo do usuário em 3-5 linhas",
    "author": "Nome Fictício Brasileiro, idade, Cidade-UF",
    "vehicle": "Marca Modelo Versão Ano (ex: Toyota Corolla XEi 2020)",
    "context": "Contexto do caso: como participou do teste, duração, condições"
  }
}
```

**REGRAS:**
- Use nomes brasileiros fictícios mas realistas
- `vehicle`: sempre incluir marca + modelo + versão + ano
- `context`: explicar como o dado foi coletado

---

### 8️⃣ BLOCO: steps

```json
{
  "block_id": "steps-001",
  "block_type": "steps",
  "display_order": 8,
  "heading": "Como Fazer: Passo a Passo",
  "content": {
    "intro": "Texto introdutório (opcional)",
    "steps": [
      {
        "step_number": 1,
        "title": "Título do Passo 1",
        "description": "Descrição detalhada do que fazer",
        "tip": "Dica profissional (opcional)",
        "warning": "Aviso importante (opcional)"
      },
      {
        "step_number": 2,
        "title": "Título do Passo 2",
        "description": "Descrição detalhada",
        "tip": "",
        "warning": ""
      }
    ],
    "conclusion": "Conclusão do tutorial"
  }
}
```

---

### 9️⃣ BLOCO: cost

```json
{
  "block_id": "cost-001",
  "block_type": "cost",
  "display_order": 9,
  "heading": "Análise de Custos",
  "content": {
    "intro": "Texto introdutório",
    "cost_items": [
      {
        "item": "Item 1 (ex: Troca de óleo sintético)",
        "cost": "R$ 250 - R$ 400",
        "notes": "Observações sobre o custo"
      },
      {
        "item": "Item 2",
        "cost": "R$ 80 - R$ 150",
        "notes": "Detalhes adicionais"
      }
    ],
    "savings": [
      {
        "description": "Economia 1",
        "amount": "R$ 300/ano",
        "calculation": "Como chegamos nesse valor"
      }
    ],
    "conclusion": "Resumo da análise de custos"
  }
}
```

---

### 🔟 BLOCO: decision

```json
{
  "block_id": "decision-001",
  "block_type": "decision",
  "display_order": 10,
  "heading": "Quando Vale a Pena?",
  "content": {
    "intro": "Texto introdutório",
    "scenarios": [
      {
        "title": "Vale a pena para você se:",
        "points": [
          "Condição 1",
          "Condição 2",
          "Condição 3"
        ]
      },
      {
        "title": "Pode não compensar se:",
        "points": [
          "Condição 1",
          "Condição 2"
        ]
      }
    ],
    "conclusion": "Recomendação final"
  }
}
```

---

### 1️⃣1️⃣ BLOCO: alert

```json
{
  "block_id": "alert-001",
  "block_type": "alert",
  "display_order": 11,
  "heading": "⚠️ Atenção Importante",
  "content": {
    "alert_type": "warning",
    "message": "Mensagem do alerta em 2-3 linhas",
    "details": "Detalhes adicionais (opcional)",
    "action": "O que fazer (opcional)"
  }
}
```

**REGRAS:**
- `alert_type`: "warning" | "danger" | "info" | "success"

---

### 1️⃣2️⃣ BLOCO: myth

```json
{
  "block_id": "myth-001",
  "block_type": "myth",
  "display_order": 12,
  "heading": "Mitos e Verdades",
  "content": {
    "intro": "Texto introdutório explicando o contexto dos mitos",
    "myths": [
      {
        "myth": "Afirmação popular comum (o que as pessoas acreditam)",
        "reality": "MITO",
        "explanation": "Explicação técnica detalhada do porquê é mito/verdade com dados concretos",
        "evidence": "Evidências de teste realizado ou fonte confiável (opcional)"
      },
      {
        "myth": "Segunda afirmação popular",
        "reality": "VERDADEIRO",
        "explanation": "Explicação técnica com números e fatos"
      },
      {
        "myth": "Terceira afirmação com nuances",
        "reality": "PARCIALMENTE VERDADEIRO",
        "explanation": "Explicação mostrando em que condições é verdade e em quais é mito",
        "evidence": "Dados do teste que comprovam (opcional)"
      }
    ]
  }
}
```

**REGRAS CRÍTICAS:**
- Use EXATAMENTE a chave `myths` (array de objetos)
- Cada objeto deve ter a chave `myth` (não `statement`)
- Cada objeto deve ter a chave `reality` (não `verdict`)
- `reality`: APENAS os valores "VERDADEIRO" | "MITO" | "PARCIALMENTE VERDADEIRO"
- `evidence` é opcional mas recomendado para dar credibilidade
- Mínimo 3 mitos, máximo 5

---

### 1️⃣3️⃣ BLOCO: timeline

```json
{
  "block_id": "timeline-001",
  "block_type": "timeline",
  "display_order": 13,
  "heading": "Cronograma de Manutenção",
  "content": {
    "intro": "Texto introdutório",
    "events": [
      {
        "time": "0 - 5.000 km",
        "title": "Primeira Revisão",
        "description": "O que fazer nesse período",
        "priority": "high"
      },
      {
        "time": "5.000 - 10.000 km",
        "title": "Segunda Revisão",
        "description": "Ações necessárias",
        "priority": "medium"
      }
    ]
  }
}
```

---

### 1️⃣4️⃣ BLOCO: faq (OBRIGATÓRIO - Penúltimo bloco)

```json
{
  "block_id": "faq-001",
  "block_type": "faq",
  "display_order": 14,
  "heading": "Perguntas Frequentes",
  "content": {
    "intro": "Respondemos as dúvidas mais comuns:",
    "questions": [
      {
        "question": "Pergunta 1 em formato de dúvida real?",
        "answer": "Resposta objetiva e completa em 2-4 linhas com dados concretos"
      },
      {
        "question": "Pergunta 2?",
        "answer": "Resposta prática"
      },
      {
        "question": "Pergunta 3?",
        "answer": "Resposta técnica"
      },
      {
        "question": "Pergunta 4?",
        "answer": "Resposta acionável"
      },
      {
        "question": "Pergunta 5?",
        "answer": "Resposta final"
      }
    ]
  }
}
```

**REGRAS:**
- Exatamente 5 perguntas
- Perguntas devem ser reais e estratégicas para SEO

---

### 1️⃣5️⃣ BLOCO: conclusion (OBRIGATÓRIO - Último bloco)

```json
{
  "block_id": "conclusion-001",
  "block_type": "conclusion",
  "display_order": 15,
  "heading": "Conclusão: Vale a Pena?",
  "content": {
    "summary": "Resumo do artigo em 2-3 linhas",
    "key_takeaways": [
      "Aprendizado 1",
      "Aprendizado 2",
      "Aprendizado 3"
    ],
    "final_thought": "Pensamento final e recomendação prática",
    "cta": "Call-to-action (opcional)"
  }
}
```

**REGRAS:**
- `key_takeaways`: 3-5 aprendizados principais
- `final_thought`: recomendação clara e acionável

---

## 📝 INSTRUÇÕES FINAIS

1. **BLOCOS OBRIGATÓRIOS** (devem estar SEMPRE):
   - intro (display_order: 1)
   - tldr (display_order: 2)
   - faq (penúltimo)
   - conclusion (último)

2. **TOTAL DE BLOCOS**: 10-15 blocos

3. **CONTEÚDO**:
   - 2.500-3.500 palavras (10-13 min leitura)
   - Números concretos: R$, km, %, anos
   - Casos reais: nomes brasileiros + veículo + ano
   - Linguagem: casual mas profissional

4. **SEO**:
   - Keyword no título + ano (2025)
   - Meta description: 150-160 chars
   - 4-5 secondary keywords
   - 3 related articles (slugs)

5. **FORMATO DA RESPOSTA**:
   - Retorne APENAS o JSON (sem ```json, sem explicações)
   - JSON válido (aspas duplas, escape correto)
   - Todos os campos obrigatórios presentes

Gere o artigo agora seguindo EXATAMENTE as estruturas definidas acima.
PROMPT;
	}

	/**
	 * 🆕 VALIDAR E CORRIGIR ESTRUTURA DE BLOCOS
	 */
	private function validateAndFixBlockStructures(array $json): array
	{
		if (empty($json['metadata']['content_blocks'])) {
			return $json;
		}

		$blocks = $json['metadata']['content_blocks'];
		$fixedBlocks = [];

		foreach ($blocks as $block) {
			$blockType = $block['block_type'] ?? 'unknown';

			try {
				$fixedBlock = $this->fixBlockStructure($block, $blockType);
				$fixedBlocks[] = $fixedBlock;
			} catch (\Exception $e) {
				Log::warning("Bloco {$blockType} com estrutura inválida, mantendo original", [
					'block_id' => $block['block_id'] ?? 'unknown',
					'error' => $e->getMessage()
				]);
				$fixedBlocks[] = $block; // Mantém original se não conseguir corrigir
			}
		}

		$json['metadata']['content_blocks'] = $fixedBlocks;

		return $json;
	}

	/**
	 * 🆕 CORRIGIR ESTRUTURA DE UM BLOCO ESPECÍFICO
	 */
	private function fixBlockStructure(array $block, string $blockType): array
	{
		// Validações básicas
		if (empty($block['content'])) {
			throw new \Exception("Campo 'content' ausente no bloco {$blockType}");
		}

		// Correções específicas por tipo de bloco
		switch ($blockType) {
			case 'intro':
				return $this->fixIntroBlock($block);

			case 'tldr':
				return $this->fixTldrBlock($block);

			case 'text':
				return $this->fixTextBlock($block);

			case 'list':
				return $this->fixListBlock($block);

			case 'comparison':
				return $this->fixComparisonBlock($block);

			case 'table':
				return $this->fixTableBlock($block);

			case 'testimonial':
				return $this->fixTestimonialBlock($block);

			case 'cost':
				return $this->fixCostBlock($block);

			case 'decision':
				return $this->fixDecisionBlock($block);

			case 'faq':
				return $this->fixFaqBlock($block);

			case 'conclusion':
				return $this->fixConclusionBlock($block);

			default:
				return $block; // Outros blocos mantém estrutura original
		}
	}

	/**
	 * Corrigir bloco INTRO
	 */
	private function fixIntroBlock(array $block): array
	{
		$content = $block['content'];

		// Garantir chaves obrigatórias
		$block['content'] = [
			'text' => $content['text'] ?? $content['intro'] ?? '',
			'highlight' => $content['highlight'] ?? $content['emphasis'] ?? '',
			'context' => $content['context'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco TLDR
	 */
	private function fixTldrBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'answer' => $content['answer'] ?? $content['summary'] ?? '',
			'key_points' => $content['key_points'] ?? $content['points'] ?? []
		];

		// Garantir exatamente 5 key_points
		if (count($block['content']['key_points']) > 5) {
			$block['content']['key_points'] = array_slice($block['content']['key_points'], 0, 5);
		}

		return $block;
	}

	/**
	 * Corrigir bloco TEXT
	 */
	private function fixTextBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'text' => $content['text'] ?? implode("\n\n", $content['paragraphs'] ?? []),
			'paragraphs' => $content['paragraphs'] ?? [],
			'emphasis' => $content['emphasis'] ?? $content['highlight'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco LIST
	 */
	private function fixListBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'intro' => $content['intro'] ?? $content['introduction'] ?? '',
			'list_type' => $content['list_type'] ?? 'numbered',
			'items' => $content['items'] ?? [],
			'conclusion' => $content['conclusion'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco COMPARISON
	 */
	private function fixComparisonBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'intro' => $content['intro'] ?? '',
			'items' => $content['items'] ?? $content['options'] ?? [],
			'conclusion' => $content['conclusion'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco TABLE
	 */
	private function fixTableBlock(array $block): array
	{
		$content = $block['content'];

		// Suporta estrutura direta OU aninhada em "table"
		$tableData = $content['table'] ?? $content;

		$block['content'] = [
			'intro' => $content['intro'] ?? '',
			'headers' => $tableData['headers'] ?? [],
			'rows' => $tableData['rows'] ?? [],
			'caption' => $content['caption'] ?? '',
			'footer' => $content['footer'] ?? '',
			'conclusion' => $content['conclusion'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco TESTIMONIAL
	 */
	private function fixTestimonialBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'quote' => $content['quote'] ?? $content['testimonial'] ?? '',
			'author' => $content['author'] ?? $content['user'] ?? '',
			'vehicle' => $content['vehicle'] ?? $content['car'] ?? '',
			'context' => $content['context'] ?? $content['situation'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco COST
	 */
	private function fixCostBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'intro' => $content['intro'] ?? '',
			'cost_items' => $content['cost_items'] ?? $content['items'] ?? [],
			'savings' => $content['savings'] ?? [],
			'conclusion' => $content['conclusion'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco DECISION
	 */
	private function fixDecisionBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'intro' => $content['intro'] ?? '',
			'scenarios' => $content['scenarios'] ?? $content['conditions'] ?? [],
			'conclusion' => $content['conclusion'] ?? ''
		];

		return $block;
	}

	/**
	 * Corrigir bloco MYTH
	 */
	private function fixMythBlock(array $block): array
	{
		$content = $block['content'];

		// Corrigir array principal: 'items' → 'myths'
		$myths = $content['myths'] ?? $content['items'] ?? [];

		$fixedMyths = [];
		foreach ($myths as $myth) {
			$fixedMyths[] = [
				// 'statement' → 'myth'
				'myth' => $myth['myth'] ?? $myth['statement'] ?? '',
				// 'verdict' → 'reality'
				'reality' => strtoupper(trim($myth['reality'] ?? $myth['verdict'] ?? 'MITO')),
				'explanation' => $myth['explanation'] ?? '',
				'evidence' => $myth['evidence'] ?? null
			];
		}

		$block['content'] = [
			'intro' => $content['intro'] ?? null,
			'myths' => $fixedMyths
		];

		return $block;
	}

	/**
	 * Corrigir bloco FAQ
	 */
	private function fixFaqBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'intro' => $content['intro'] ?? 'Respondemos as dúvidas mais comuns:',
			'questions' => $content['questions'] ?? $content['faqs'] ?? []
		];

		return $block;
	}

	/**
	 * Corrigir bloco CONCLUSION
	 */
	private function fixConclusionBlock(array $block): array
	{
		$content = $block['content'];

		$block['content'] = [
			'summary' => $content['summary'] ?? '',
			'key_takeaways' => $content['key_takeaways'] ?? $content['takeaways'] ?? [],
			'final_thought' => $content['final_thought'] ?? $content['recommendation'] ?? '',
			'cta' => $content['cta'] ?? $content['call_to_action'] ?? ''
		];

		return $block;
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

		// Limpar markdown se houver
		$content = $this->cleanMarkdown($content);

		// Decodificar JSON
		$json = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \Exception('JSON inválido retornado pela API: ' . json_last_error_msg());
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
		return (int) ceil(strlen($jsonString) / 4);
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
