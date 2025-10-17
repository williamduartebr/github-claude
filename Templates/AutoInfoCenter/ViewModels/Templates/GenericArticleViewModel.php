<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GenericArticleViewModel - ViewModel Universal para Artigos Genéricos
 * 
 * Sistema de blocos modulares que serve para QUALQUER tema:
 * - Óleo (100+ artigos)
 * - Velas (100 artigos)
 * - Câmbio (100+ artigos)
 * - Aditivo (100 artigos)
 * - Multimídia (100 artigos)
 * - Qualquer tema futuro
 * 
 * FILOSOFIA:
 * - Estrutura WordPress-like: blocos reutilizáveis
 * - 1 ViewModel serve infinitos temas
 * - 15 tipos de blocos cobrem 99% dos casos
 * - Fácil adicionar novos tipos sem quebrar existentes
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Refatorado para compatibilidade com JSONs reais
 */
class GenericArticleViewModel extends TemplateViewModel
{
    /**
     * Nome do template Blade
     */
    protected string $templateName = 'generic_article';

    /**
     * Tipos de blocos suportados
     */
    private const SUPPORTED_BLOCK_TYPES = [
        'intro',
        'tldr',
        'text',
        'table',
        'list',
        'alert',
        'comparison',
        'steps',
        'testimonial',
        'cost',
        'myth',
        'faq',
        'decision',
        'timeline',
        'conclusion'
    ];

    /**
     * Níveis de urgência suportados
     */
    private const URGENCY_LEVELS = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica'
    ];

    /**
     * Tipos de alerta suportados
     */
    private const ALERT_TYPES = [
        'info' => 'Informação',
        'warning' => 'Aviso',
        'danger' => 'Perigo',
        'success' => 'Sucesso'
    ];

    /**
     * Constructor
     */
    public function __construct(Article $article)
    {
        parent::__construct($article);
        $this->templateName = 'generic_article';
    }

    /**
     * Processar dados específicos do template genérico
     * 
     * @return void
     */
    protected function processTemplateSpecificData(): void
    {
        // 1. Processar metadata do artigo
        $this->processArticleMetadata();

        // 2. Processar blocos de conteúdo
        $this->processContentBlocks();

        // 3. Processar SEO específico
        $this->processGenericSeoData();

        // 4. Processar structured data (Schema.org)
        $this->processStructuredData();

        // 5. Breadcrumbs
        $this->processedData['breadcrumbs'] = $this->buildBreadcrumbs();
    }

    /**
     * Processar metadata do artigo
     * 
     * @return void
     */
    private function processArticleMetadata(): void
    {
        $metadata = $this->article->metadata ?? [];
        $articleMetadata = $metadata['article_metadata'] ?? [];

        $this->processedData['article_topic'] = $articleMetadata['article_topic'] ?? 'general';
        $this->processedData['article_category'] = $articleMetadata['article_category'] ?? 'guide';
        
        // Metadata adicional
        $generalMetadata = $metadata['metadata'] ?? [];
        $this->processedData['reading_time'] = $generalMetadata['reading_time'] ?? $this->estimateReadingTime();
        $this->processedData['word_count'] = $generalMetadata['word_count'] ?? 0;
        $this->processedData['difficulty'] = $generalMetadata['difficulty'] ?? 'básico';
        $this->processedData['experience_based'] = $generalMetadata['experience_based'] ?? false;
        $this->processedData['related_articles'] = $generalMetadata['related_articles'] ?? [];
    }

    /**
     * Processar blocos de conteúdo
     * 
     * CORE DO SISTEMA: Processa array de blocos modulares
     * 
     * @return void
     */
    private function processContentBlocks(): void
    {
        $metadata = $this->article->metadata ?? [];
        $contentBlocks = $metadata['content_blocks'] ?? [];

        if (empty($contentBlocks)) {
            $contentBlocks = $this->convertLegacyContent();
        }

        // Ordenar blocos por display_order
        usort($contentBlocks, function($a, $b) {
            return ($a['display_order'] ?? 0) <=> ($b['display_order'] ?? 0);
        });

        // Processar cada bloco
        $processedBlocks = [];
        foreach ($contentBlocks as $block) {
            $processedBlock = $this->processContentBlock($block);
            if ($processedBlock !== null) {
                $processedBlocks[] = $processedBlock;
            }
        }

        $this->processedData['content_blocks'] = $processedBlocks;
    }

    /**
     * Processar um bloco individual de conteúdo
     * 
     * @param array $block
     * @return array|null
     */
    private function processContentBlock(array $block): ?array
    {
        $blockType = $block['block_type'] ?? '';

        // Validar tipo de bloco
        if (!in_array($blockType, self::SUPPORTED_BLOCK_TYPES)) {
            Log::warning("Tipo de bloco não suportado: {$blockType}", [
                'article_id' => $this->article->id,
                'block' => $block
            ]);
            return null;
        }

        // Estrutura base do bloco processado
        $processedBlock = [
            'block_id' => $block['block_id'] ?? Str::slug($blockType . '-' . uniqid()),
            'block_type' => $blockType,
            'heading' => $block['heading'] ?? null,
            'subheading' => $block['subheading'] ?? null,
            'content' => $this->processBlockContent($blockType, $block['content'] ?? [])
        ];

        return $processedBlock;
    }

    /**
     * Processar conteúdo específico por tipo de bloco
     * 
     * @param string $blockType
     * @param array $content
     * @return array
     */
    private function processBlockContent(string $blockType, array $content): array
    {
        return match($blockType) {
            'intro' => $this->processIntroBlock($content),
            'tldr' => $this->processTldrBlock($content),
            'text' => $this->processTextBlock($content),
            'table' => $this->processTableBlock($content),
            'list' => $this->processListBlock($content),
            'alert' => $this->processAlertBlock($content),
            'comparison' => $this->processComparisonBlock($content),
            'steps' => $this->processStepsBlock($content),
            'testimonial' => $this->processTestimonialBlock($content),
            'cost' => $this->processCostBlock($content),
            'myth' => $this->processMythBlock($content),
            'faq' => $this->processFaqBlock($content),
            'decision' => $this->processDecisionBlock($content),
            'timeline' => $this->processTimelineBlock($content),
            'conclusion' => $this->processConclusionBlock($content),
            default => $content
        };
    }

    /**
     * Processar bloco INTRO
     */
    private function processIntroBlock(array $content): array
    {
        return [
            'text' => $content['text'] ?? '',
            'highlight' => $content['highlight'] ?? null,
            'context' => $content['context'] ?? null
        ];
    }

    /**
     * Processar bloco TLDR (Resposta Rápida)
     */
    private function processTldrBlock(array $content): array
    {
        return [
            'answer' => $content['answer'] ?? '',
            'key_points' => $content['key_points'] ?? []
        ];
    }

    /**
     * Processar bloco TEXT (Texto simples)
     */
    private function processTextBlock(array $content): array
    {
        return [
            'text' => $content['text'] ?? '',
            'paragraphs' => $content['paragraphs'] ?? [],
            'emphasis' => $content['emphasis'] ?? null
        ];
    }

    /**
     * Processar bloco TABLE (Tabela)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "table": {
     *     "headers": [...],
     *     "rows": [...]
     *   },
     *   "caption": "Legenda",
     *   "conclusion": "Conclusão"
     * }
     */
    private function processTableBlock(array $content): array
    {
        // Suporta ambas estruturas: direta ou aninhada em "table"
        $tableData = $content['table'] ?? $content;
        
        return [
            'intro' => $content['intro'] ?? null,
            'description' => $content['description'] ?? null,
            'headers' => $tableData['headers'] ?? [],
            'rows' => $tableData['rows'] ?? [],
            'caption' => $content['caption'] ?? null,
            'footer' => $content['footer'] ?? null,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco LIST (Lista)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "list_type": "ordered | bullet | checklist",
     *   "items": [
     *     {"title": "...", "description": "..."}
     *     ou
     *     "String simples"
     *   ],
     *   "conclusion": "Texto de conclusão"
     * }
     */
    private function processListBlock(array $content): array
    {
        return [
            'intro' => $content['intro'] ?? null,
            'list_type' => $content['list_type'] ?? 'bullet',
            'list_style' => $content['list_style'] ?? $content['list_type'] ?? 'bullet',
            'items' => $content['items'] ?? [],
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco ALERT (Alerta)
     * 
     * Estrutura esperada:
     * {
     *   "alert_type": "info | warning | danger | success",
     *   "title": "Título",
     *   "message": "Mensagem",
     *   "details": ["item1", "item2"],
     *   "action": "Ação recomendada",
     *   "recommendation": "Recomendação"
     * }
     */
    private function processAlertBlock(array $content): array
    {
        $alertType = $content['alert_type'] ?? 'info';
        
        return [
            'alert_type' => $alertType,
            'alert_type_label' => self::ALERT_TYPES[$alertType] ?? 'Informação',
            'title' => $content['title'] ?? null,
            'message' => $content['message'] ?? '',
            'details' => $content['details'] ?? [],
            'action' => $content['action'] ?? null,
            'recommendation' => $content['recommendation'] ?? null
        ];
    }

    /**
     * Processar bloco COMPARISON (Comparação)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "items": [
     *     {
     *       "title": "Opção A",
     *       "aspect": "Aspecto comparado",
     *       "option_a": "Descrição A",
     *       "option_b": "Descrição B",
     *       "features": [...],
     *       "pros": [...],
     *       "cons": [...],
     *       "conclusion": "..."
     *     }
     *   ],
     *   "conclusion": "Conclusão geral"
     * }
     */
    private function processComparisonBlock(array $content): array
    {
        $items = $content['items'] ?? [];
        
        $processedItems = [];
        foreach ($items as $item) {
            $processedItems[] = [
                'title' => $item['title'] ?? $item['label'] ?? '',
                'aspect' => $item['aspect'] ?? null,
                'option_a' => $item['option_a'] ?? null,
                'option_b' => $item['option_b'] ?? null,
                'features' => $item['features'] ?? [],
                'pros' => $item['pros'] ?? [],
                'cons' => $item['cons'] ?? [],
                'conclusion' => $item['conclusion'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'items' => $processedItems,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco STEPS (Passo a passo)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "steps": [
     *     {
     *       "number": 1,
     *       "title": "...",
     *       "description": "...",
     *       "details": [...],
     *       "tip": "..."
     *     }
     *   ],
     *   "conclusion": "Conclusão"
     * }
     */
    private function processStepsBlock(array $content): array
    {
        $steps = $content['steps'] ?? [];
        
        $processedSteps = [];
        foreach ($steps as $index => $step) {
            $processedSteps[] = [
                'number' => $step['number'] ?? ($index + 1),
                'title' => $step['title'] ?? '',
                'description' => $step['description'] ?? '',
                'details' => $step['details'] ?? [],
                'tip' => $step['tip'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'steps' => $processedSteps,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco TESTIMONIAL (Depoimentos)
     * 
     * Estrutura esperada:
     * {
     *   "quote": "Depoimento textual",
     *   "author": "Nome do autor",
     *   "vehicle": "Veículo usado",
     *   "context": "Contexto adicional",
     *   
     *   OU (estrutura alternativa):
     *   
     *   "cases": [
     *     {
     *       "user": "Nome",
     *       "situation": "...",
     *       "result": "...",
     *       "observation": "..."
     *     }
     *   ]
     * }
     */
    private function processTestimonialBlock(array $content): array
    {
        // Estrutura simples (quote)
        if (!empty($content['quote'])) {
            return [
                'quote' => $content['quote'] ?? '',
                'author' => $content['author'] ?? 'Anônimo',
                'vehicle' => $content['vehicle'] ?? null,
                'context' => $content['context'] ?? null
            ];
        }
        
        // Estrutura de cases múltiplos
        $cases = $content['cases'] ?? [];
        $processedCases = [];
        
        foreach ($cases as $case) {
            $processedCases[] = [
                'user' => $case['user'] ?? 'Anônimo',
                'situation' => $case['situation'] ?? '',
                'result' => $case['result'] ?? '',
                'observation' => $case['observation'] ?? null
            ];
        }
        
        return ['cases' => $processedCases];
    }

    /**
     * Processar bloco COST (Análise de custo)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "cost_items": [
     *     {
     *       "item": "Descrição",
     *       "cost": "R$ 100",
     *       "notes": "Observações"
     *     }
     *   ],
     *   "total_investment": "R$ 500",
     *   "savings": [
     *     {
     *       "description": "...",
     *       "amount": "R$ 200",
     *       "calculation": "..."
     *     }
     *   ],
     *   "total_savings": "R$ 1000",
     *   "roi": "150%",
     *   "payback_period": "6 meses",
     *   "break_even": "10.000 km",
     *   "conclusion": "Conclusão",
     *   
     *   OU (estrutura alternativa - scenarios):
     *   
     *   "scenarios": [
     *     {
     *       "option": "Opção A",
     *       "cost": "R$ 200",
     *       "duration": "...",
     *       "recommendation": "...",
     *       "savings": "..."
     *     }
     *   ]
     * }
     */
    private function processCostBlock(array $content): array
    {
        // Estrutura completa de análise financeira
        if (!empty($content['cost_items']) || !empty($content['savings'])) {
            return [
                'intro' => $content['intro'] ?? null,
                'cost_items' => $content['cost_items'] ?? [],
                'total_investment' => $content['total_investment'] ?? null,
                'savings' => $content['savings'] ?? [],
                'total_savings' => $content['total_savings'] ?? null,
                'roi' => $content['roi'] ?? null,
                'payback_period' => $content['payback_period'] ?? null,
                'break_even' => $content['break_even'] ?? null,
                'conclusion' => $content['conclusion'] ?? null
            ];
        }
        
        // Estrutura alternativa: scenarios
        $scenarios = $content['scenarios'] ?? [];
        $processedScenarios = [];
        
        foreach ($scenarios as $scenario) {
            // Suporta "scenario" ou "items" dentro de scenario
            if (is_string($scenario)) {
                $processedScenarios[] = ['description' => $scenario];
                continue;
            }
            
            $processedScenarios[] = [
                'scenario' => $scenario['scenario'] ?? null,
                'option' => $scenario['option'] ?? '',
                'cost' => $scenario['cost'] ?? '',
                'duration' => $scenario['duration'] ?? null,
                'recommendation' => $scenario['recommendation'] ?? '',
                'savings' => $scenario['savings'] ?? null,
                'items' => $scenario['items'] ?? []
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'scenarios' => $processedScenarios,
            'savings' => $content['savings'] ?? null,
            'break_even' => $content['break_even'] ?? null,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco MYTH (Mito vs Realidade)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "myths": [
     *     {
     *       "myth": "Afirmação popular",
     *       "reality": "VERDADEIRO | FALSO | PARCIALMENTE VERDADEIRO | MITO",
     *       "explanation": "Explicação técnica",
     *       "evidence": "Evidências do teste"
     *     }
     *   ]
     * }
     */
    private function processMythBlock(array $content): array
    {
        $myths = $content['myths'] ?? [];
        
        $processedMyths = [];
        foreach ($myths as $myth) {
            // Normalizar campo reality
            $realityRaw = strtoupper(trim($myth['reality'] ?? 'PARCIALMENTE VERDADEIRO'));
            
            $reality = match(true) {
                $realityRaw === 'VERDADEIRO' => 'true',
                $realityRaw === 'VERDADE' => 'true',
                str_contains($realityRaw, 'VERDADEIRO') && !str_contains($realityRaw, 'PARCIAL') => 'true',
                $realityRaw === 'FALSO' => 'false',
                $realityRaw === 'MITO' => 'false',
                str_contains($realityRaw, 'FALSO') => 'false',
                default => 'partial'
            };
            
            $processedMyths[] = [
                'myth' => $myth['myth'] ?? '',
                'reality' => $reality,
                'reality_label' => $this->getRealityLabel($reality),
                'explanation' => $myth['explanation'] ?? '',
                'evidence' => $myth['evidence'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'myths' => $processedMyths
        ];
    }

    /**
     * Processar bloco FAQ (Perguntas Frequentes)
     */
    private function processFaqBlock(array $content): array
    {
        $questions = $content['questions'] ?? [];
        
        $processedQuestions = [];
        foreach ($questions as $faq) {
            $processedQuestions[] = [
                'question' => $faq['question'] ?? '',
                'answer' => $faq['answer'] ?? '',
                'related_topics' => $faq['related_topics'] ?? []
            ];
        }
        
        return ['questions' => $processedQuestions];
    }

    /**
     * Processar bloco DECISION (Matriz de decisão)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "scenarios": [
     *     {
     *       "title": "Vale a pena se:",
     *       "points": ["ponto 1", "ponto 2"],
     *       
     *       OU
     *       
     *       "condition": "Se X",
     *       "action": "Faça Y",
     *       "urgency": "low | medium | high | critical",
     *       "reason": "Porque..."
     *     }
     *   ],
     *   "conclusion": "Conclusão"
     * }
     */
    private function processDecisionBlock(array $content): array
    {
        $scenarios = $content['scenarios'] ?? [];
        
        $processedScenarios = [];
        foreach ($scenarios as $scenario) {
            // Estrutura com title + points
            if (!empty($scenario['title']) && !empty($scenario['points'])) {
                $processedScenarios[] = [
                    'title' => $scenario['title'],
                    'points' => $scenario['points']
                ];
                continue;
            }
            
            // Estrutura com condition + action + urgency
            $urgency = $scenario['urgency'] ?? 'medium';
            
            $processedScenarios[] = [
                'condition' => $scenario['condition'] ?? '',
                'action' => $scenario['action'] ?? '',
                'urgency' => $urgency,
                'urgency_label' => self::URGENCY_LEVELS[$urgency] ?? 'Média',
                'reason' => $scenario['reason'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'scenarios' => $processedScenarios,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco TIMELINE (Linha do tempo)
     * 
     * Estrutura esperada:
     * {
     *   "intro": "Texto introdutório",
     *   "events": [
     *     {
     *       "milestone": "0km",
     *       "date": "Janeiro 2025",
     *       "title": "Título do evento",
     *       "action": "Ação realizada",
     *       "description": "Descrição"
     *     }
     *   ],
     *   "conclusion": "Conclusão"
     * }
     */
    private function processTimelineBlock(array $content): array
    {
        $events = $content['events'] ?? [];
        
        $processedEvents = [];
        foreach ($events as $event) {
            $processedEvents[] = [
                'milestone' => $event['milestone'] ?? '',
                'date' => $event['date'] ?? null,
                'title' => $event['title'] ?? '',
                'action' => $event['action'] ?? '',
                'description' => $event['description'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'events' => $processedEvents,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Processar bloco CONCLUSION (Conclusão)
     * 
     * Estrutura esperada:
     * {
     *   "summary": "Resumo",
     *   "key_takeaways": ["ponto 1", "ponto 2"],
     *   "key_takeaway": "Principal aprendizado",
     *   "final_thought": "Pensamento final",
     *   "cta": "Call to action",
     *   "call_to_action": "Call to action alternativo"
     * }
     */
    private function processConclusionBlock(array $content): array
    {
        return [
            'summary' => $content['summary'] ?? '',
            'key_takeaways' => $content['key_takeaways'] ?? [],
            'key_takeaway' => $content['key_takeaway'] ?? null,
            'final_thought' => $content['final_thought'] ?? null,
            'cta' => $content['cta'] ?? $content['call_to_action'] ?? null
        ];
    }

    /**
     * Processar dados SEO genéricos
     * 
     * @return void
     */
    private function processGenericSeoData(): void
    {
        $seoData = $this->article->seo_data ?? [];

        $this->processedData['seo_data'] = [
            'page_title' => $seoData['page_title'] ?? $this->processedData['title'],
            'meta_description' => $seoData['meta_description'] ?? '',
            'h1' => $seoData['h1'] ?? $this->processedData['title'],
            'primary_keyword' => $seoData['primary_keyword'] ?? '',
            'secondary_keywords' => $seoData['secondary_keywords'] ?? [],
            'canonical_url' => $seoData['canonical_url'] ?? $this->getCanonicalUrl(),
            'og_title' => $seoData['og_title'] ?? $seoData['page_title'] ?? $this->processedData['title'],
            'og_description' => $seoData['og_description'] ?? $seoData['meta_description'] ?? '',
            'og_image' => $seoData['og_image'] ?? $this->getDefaultOgImage(),
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Processar structured data (Schema.org)
     * 
     * @return void
     */
    private function processStructuredData(): void
    {
        $this->processedData['structured_data'] = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->processedData['seo_data']['h1'],
            'description' => $this->processedData['seo_data']['meta_description'],
            'image' => $this->processedData['seo_data']['og_image'],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'url' => 'https://mercadoveiculos.com.br'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.amazonaws.com/statics/logos/logo-mercadoveiculos.png'
                ]
            ],
            'datePublished' => $this->article->created_at?->toISOString(),
            'dateModified' => $this->article->updated_at?->toISOString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCanonicalUrl()
            ]
        ];

        // Adicionar FAQPage schema se houver FAQs
        $this->addFaqSchema();
    }

    /**
     * Adicionar FAQPage schema se houver blocos FAQ
     * 
     * @return void
     */
    private function addFaqSchema(): void
    {
        $faqBlocks = array_filter($this->processedData['content_blocks'] ?? [], function($block) {
            return $block['block_type'] === 'faq';
        });

        if (empty($faqBlocks)) {
            return;
        }

        $faqEntities = [];
        foreach ($faqBlocks as $faqBlock) {
            foreach ($faqBlock['content']['questions'] ?? [] as $faq) {
                $faqEntities[] = [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer']
                    ]
                ];
            }
        }

        if (!empty($faqEntities)) {
            $this->processedData['faq_schema'] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities
            ];
        }
    }

    /**
     * Construir breadcrumbs
     * 
     * @return array
     */
    private function buildBreadcrumbs(): array
    {
        return [
            [
                'name' => 'Início',
                'url' => route('home'),
                'position' => 1
            ],
            [
                'name' => 'Informações',
                'url' => route('info.category.index'),
                'position' => 2
            ],
            [
                'name' => Str::title($this->processedData['category']['name'] ?? 'Informações'),
                'url' => route('info.category.show', $this->processedData['category']['slug'] ?? 'informacoes'),
                'position' => 3
            ],
            [
                'name' => $this->processedData['title'],
                'url' => route('info.article.show', $this->article->slug),
                'position' => 4
            ],
        ];
    }

    /**
     * Converter conteúdo legado para formato de blocos
     * 
     * COMPATIBILIDADE: Converte estruturas antigas para novo formato
     * 
     * @return array
     */
    private function convertLegacyContent(): array
    {
        // Implementar conversão de estruturas antigas se necessário
        // Por enquanto, retorna array vazio
        return [];
    }

    /**
     * Obter label de realidade para mitos
     * 
     * @param string $reality
     * @return string
     */
    private function getRealityLabel(string $reality): string
    {
        return match($reality) {
            'true' => '✅ Verdadeiro',
            'false' => '❌ Mito',
            'partial' => '⚠️ Parcialmente Verdadeiro',
            default => 'Não definido'
        };
    }

    /**
     * Estimar tempo de leitura baseado em word_count
     * 
     * @return int
     */
    private function estimateReadingTime(): int
    {
        $wordCount = $this->processedData['word_count'] ?? 0;
        
        if ($wordCount === 0) {
            return 5; // Default
        }

        // Média 200 palavras por minuto em português
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Obter imagem OG padrão baseada no tópico
     * 
     * @return string
     */
    private function getDefaultOgImage(): string
    {
        $topic = $this->processedData['article_topic'] ?? 'general';
        
        $imageMap = [
            'oil' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/motor-oil.png',
            'spark_plug' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/spark-plug.png',
            'transmission' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/transmission.png',
            'coolant' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/coolant.png',
            'multimedia' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/multimedia.png',
        ];

        return $imageMap[$topic] ?? 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/generic-article.png';
    }

    /**
     * Obter breadcrumbs processados
     * 
     * @return array
     */
    public function getBreadcrumbs(): array
    {
        return $this->processedData['breadcrumbs'] ?? [];
    }

    /**
     * Obter URL canônica
     * 
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return route('info.article.show', $this->article->slug);
    }
}