<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GenericArticleViewModel - ViewModel Universal para Artigos Genéricos
 * 
 * @author Claude Sonnet 4.5
 * @version 3.0 - CORRIGIDO: processMythBlock() agora aceita 'reality' OU 'verdict'
 */
class GenericArticleViewModel extends TemplateViewModel
{
    protected string $templateName = 'generic_article';

    private const SUPPORTED_BLOCK_TYPES = [
        'intro', 'tldr', 'text', 'table', 'list', 'alert',
        'comparison', 'steps', 'testimonial', 'cost', 'myth',
        'faq', 'decision', 'timeline', 'conclusion'
    ];

    private const URGENCY_LEVELS = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica'
    ];

    private const ALERT_TYPES = [
        'info' => 'Informação',
        'warning' => 'Aviso',
        'danger' => 'Perigo',
        'success' => 'Sucesso'
    ];

    public function __construct(Article $article)
    {
        parent::__construct($article);
        $this->templateName = 'generic_article';
    }

    protected function processTemplateSpecificData(): void
    {
        $this->processArticleMetadata();
        $this->processContentBlocks();
        $this->processGenericSeoData();
        $this->processStructuredData();
        $this->processedData['breadcrumbs'] = $this->buildBreadcrumbs();
    }

    private function processArticleMetadata(): void
    {
        $metadata = $this->article->metadata ?? [];
        $articleMetadata = $metadata['article_metadata'] ?? [];

        $this->processedData['article_topic'] = $articleMetadata['article_topic'] ?? 'general';
        $this->processedData['article_category'] = $articleMetadata['article_category'] ?? 'guide';
        
        $generalMetadata = $metadata['metadata'] ?? [];
        $this->processedData['reading_time'] = $generalMetadata['reading_time'] ?? $this->estimateReadingTime();
        $this->processedData['word_count'] = $generalMetadata['word_count'] ?? 0;
        $this->processedData['difficulty'] = $generalMetadata['difficulty'] ?? 'básico';
        $this->processedData['experience_based'] = $generalMetadata['experience_based'] ?? false;
        // $this->processedData['related_articles'] = $generalMetadata['related_articles'] ?? []; // Desativado temporariamente
        $this->processedData['related_articles'] = [];
    }

    private function processContentBlocks(): void
    {
        $content = $this->article->content ?? [];
        $contentBlocks = $content['blocks'] ?? [];

        if (empty($contentBlocks)) {
            $contentBlocks = $this->convertLegacyContent();
        }

        usort($contentBlocks, function($a, $b) {
            return ($a['display_order'] ?? 0) <=> ($b['display_order'] ?? 0);
        });

        $processedBlocks = [];
        foreach ($contentBlocks as $block) {
            $processedBlock = $this->processContentBlock($block);
            if ($processedBlock !== null) {
                $processedBlocks[] = $processedBlock;
            }
        }

        $this->processedData['content_blocks'] = $processedBlocks;
    }

    private function processContentBlock(array $block): ?array
    {
        $blockType = $block['block_type'] ?? '';

        if (!in_array($blockType, self::SUPPORTED_BLOCK_TYPES)) {
            Log::warning("Tipo de bloco não suportado: {$blockType}", [
                'article_id' => $this->article->id,
                'block' => $block
            ]);
            return null;
        }

        $processedBlock = [
            'block_id' => $block['block_id'] ?? Str::slug($blockType . '-' . uniqid()),
            'block_type' => $blockType,
            'heading' => $block['heading'] ?? null,
            'subheading' => $block['subheading'] ?? null,
            'content' => $this->processBlockContent($blockType, $block['content'] ?? [])
        ];

        return $processedBlock;
    }

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

    private function processIntroBlock(array $content): array
    {
        return [
            'text' => $content['text'] ?? '',
            'highlight' => $content['highlight'] ?? null,
            'context' => $content['context'] ?? null
        ];
    }

    private function processTldrBlock(array $content): array
    {
        return [
            'answer' => $content['answer'] ?? '',
            'key_points' => $content['key_points'] ?? []
        ];
    }

    private function processTextBlock(array $content): array
    {
        return [
            'text' => $content['text'] ?? null,
            'paragraphs' => $content['paragraphs'] ?? [],
            'emphasis' => $content['emphasis'] ?? null
        ];
    }

    private function processTableBlock(array $content): array
    {
        $tableData = $content['table'] ?? $content;
        
        return [
            'intro' => $content['intro'] ?? null,
            'headers' => $tableData['headers'] ?? [],
            'rows' => $tableData['rows'] ?? [],
            'caption' => $content['caption'] ?? null,
            'footer' => $content['footer'] ?? null,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    private function processListBlock(array $content): array
    {
        return [
            'intro' => $content['intro'] ?? null,
            'list_type' => $content['list_type'] ?? 'numbered',
            'items' => $content['items'] ?? [],
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    private function processAlertBlock(array $content): array
    {
        $alertType = $content['alert_type'] ?? 'info';
        
        return [
            'alert_type' => $alertType,
            'alert_label' => self::ALERT_TYPES[$alertType] ?? 'Informação',
            'message' => $content['message'] ?? '',
            'details' => $content['details'] ?? null,
            'action' => $content['action'] ?? null
        ];
    }

    /**
     * ✅ CORRIGIDO v3.0: Processar bloco COMPARISON com normalização inteligente
     */
    private function processComparisonBlock(array $content): array
    {
        $items = $content['items'] ?? [];
        
        if (empty($items)) {
            Log::warning('Bloco comparison vazio', [
                'article_id' => $this->article->id
            ]);
            return [
                'intro' => $content['intro'] ?? null,
                'items' => [],
                'conclusion' => $content['conclusion'] ?? null
            ];
        }

        $firstItem = $items[0] ?? [];
        $format = $this->detectComparisonFormat($firstItem);

        Log::info('Formato de comparison detectado', [
            'article_id' => $this->article->id,
            'format' => $format,
            'total_items' => count($items)
        ]);

        $processedItems = match($format) {
            'aspect_based' => $this->normalizeAspectBasedComparison($items),
            'pros_cons' => $this->normalizeProsConsComparison($items),
            'broken' => $this->fixBrokenComparison($items, $content),
            default => $this->normalizeAspectBasedComparison($items)
        };

        return [
            'intro' => $content['intro'] ?? null,
            'items' => $processedItems,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * Detectar formato do bloco comparison
     * 
     * v3.1: Detecta 'name' além de 'title' para pros/cons (Claude usa 'name' nos JSONs)
     */
    private function detectComparisonFormat(array $firstItem): string
    {
        // Formato correto: tem aspect + option_a + option_b
        if (!empty($firstItem['aspect']) && 
            (isset($firstItem['option_a']) || isset($firstItem['option_b']))) {
            return 'aspect_based';
        }

        // Formato pros/cons: tem (name OU title) + (pros/cons/features)
        if ((!empty($firstItem['name']) || !empty($firstItem['title'])) && 
            (isset($firstItem['pros']) || isset($firstItem['cons']) || isset($firstItem['features']))) {
            return 'pros_cons';
        }

        // Formato quebrado: tem option_a/option_b mas sem aspect
        if (isset($firstItem['option_a']) || isset($firstItem['option_b'])) {
            return 'broken';
        }

        return 'unknown';
    }

    private function normalizeAspectBasedComparison(array $items): array
    {
        $normalized = [];
        
        foreach ($items as $item) {
            $normalized[] = [
                'aspect' => $item['aspect'] ?? 'Aspecto não definido',
                'option_a' => $item['option_a'] ?? '',
                'option_b' => $item['option_b'] ?? ''
            ];
        }
        
        return $normalized;
    }

    /**
     * ✅ CORRIGIDO v3.1: Preserva estrutura pros/cons ao invés de converter
     * 
     * O blade comparison.blade.php detecta automaticamente o formato pros/cons
     * via campos 'name' + 'pros'/'cons', portanto NÃO devemos converter para aspect_based!
     */
    private function normalizeProsConsComparison(array $items): array
    {
        $normalized = [];
        
        foreach ($items as $index => $item) {
            // Preserva estrutura original, apenas garante campos obrigatórios
            $normalized[] = [
                'name' => $item['name'] ?? $item['title'] ?? "Opção " . ($index + 1),
                'features' => $item['features'] ?? [],
                'pros' => $item['pros'] ?? [],
                'cons' => $item['cons'] ?? [],
                'best_for' => $item['best_for'] ?? null,
                'cost' => $item['cost'] ?? null,
                'conclusion' => $item['conclusion'] ?? null
            ];
        }
        
        return $normalized;
    }

    private function fixBrokenComparison(array $items, array $content): array
    {
        Log::warning('Comparison com formato quebrado detectado', [
            'article_id' => $this->article->id,
            'items_count' => count($items)
        ]);
        
        $normalized = [];
        
        foreach ($items as $index => $item) {
            $normalized[] = [
                'aspect' => "Aspecto " . ($index + 1),
                'option_a' => $item['option_a'] ?? '',
                'option_b' => $item['option_b'] ?? ''
            ];
        }
        
        return $normalized;
    }

    private function processStepsBlock(array $content): array
    {
        $steps = $content['steps'] ?? [];
        
        $processedSteps = [];
        foreach ($steps as $step) {
            $processedSteps[] = [
                'step_number' => $step['step_number'] ?? count($processedSteps) + 1,
                'title' => $step['title'] ?? '',
                'description' => $step['description'] ?? '',
                'tip' => $step['tip'] ?? null,
                'warning' => $step['warning'] ?? null
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'steps' => $processedSteps,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    private function processTestimonialBlock(array $content): array
    {
        if (!empty($content['cases'])) {
            return [
                'cases' => array_map(function($case) {
                    return [
                        'user' => $case['user'] ?? '',
                        'situation' => $case['situation'] ?? '',
                        'result' => $case['result'] ?? '',
                        'observation' => $case['observation'] ?? null
                    ];
                }, $content['cases'])
            ];
        }
        
        return [
            'quote' => $content['quote'] ?? '',
            'author' => $content['author'] ?? '',
            'vehicle' => $content['vehicle'] ?? null,
            'context' => $content['context'] ?? null
        ];
    }

    private function processCostBlock(array $content): array
    {
        $costItems = $content['cost_items'] ?? [];
        
        $processedItems = [];
        foreach ($costItems as $item) {
            if (!empty($item['scenario'])) {
                $processedItems[] = [
                    'scenario' => $item['scenario'],
                    'items' => $item['items'] ?? []
                ];
            } else {
                $processedItems[] = [
                    'item' => $item['item'] ?? '',
                    'cost' => $item['cost'] ?? '',
                    'notes' => $item['notes'] ?? null
                ];
            }
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'cost_items' => $processedItems,
            'savings' => $content['savings'] ?? [],
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

    /**
     * ✅ CORRIGIDO v3.0: Processar bloco MYTH com suporte a 'reality' OU 'verdict'
     * 
     * Estrutura esperada (v3.0):
     * {
     *   "intro": "...",
     *   "myths": [
     *     {
     *       "myth": "Afirmação popular",
     *       "reality": "VERDADEIRO | MITO | PARCIALMENTE VERDADEIRO",  // ← CAMPO OBRIGATÓRIO
     *       "explanation": "Explicação técnica",
     *       "evidence": "Evidências do teste (opcional)"
     *     }
     *   ]
     * }
     * 
     * FALLBACKS (aceita variações antigas):
     * - "verdict" → "reality" (campo legado)
     * - "statement" → "myth" (campo legado)
     */
    private function processMythBlock(array $content): array
    {
        $myths = $content['myths'] ?? $content['items'] ?? [];
        
        if (empty($myths)) {
            Log::warning('Bloco myth sem conteúdo', [
                'article_id' => $this->article->id
            ]);
            return [
                'intro' => $content['intro'] ?? null,
                'myths' => []
            ];
        }
        
        $processedMyths = [];
        foreach ($myths as $myth) {
            // ✅ ACEITA TANTO 'reality' (novo) QUANTO 'verdict' (legado)
            $reality = $myth['reality'] ?? $myth['verdict'] ?? 'PARCIALMENTE VERDADEIRO';
            
            // ✅ ACEITA TANTO 'myth' (novo) QUANTO 'statement' (legado)
            $statement = $myth['myth'] ?? $myth['statement'] ?? '';
            
            if (empty($statement)) {
                Log::warning('Myth sem afirmação', [
                    'article_id' => $this->article->id,
                    'myth_data' => $myth
                ]);
                continue;
            }
            
            $processedMyths[] = [
                'myth' => $statement,
                'reality' => strtoupper(trim($reality)),
                'explanation' => $myth['explanation'] ?? '',
                'evidence' => $myth['evidence'] ?? null
            ];
        }
        
        if (empty($processedMyths)) {
            Log::warning('Nenhum myth válido após processamento', [
                'article_id' => $this->article->id
            ]);
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'myths' => $processedMyths
        ];
    }

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
        
        return [
            'intro' => $content['intro'] ?? 'Respondemos as dúvidas mais comuns:',
            'questions' => $processedQuestions
        ];
    }

    private function processDecisionBlock(array $content): array
    {
        $scenarios = $content['scenarios'] ?? [];
        
        $processedScenarios = [];
        foreach ($scenarios as $scenario) {
            if (!empty($scenario['title']) && !empty($scenario['points'])) {
                $processedScenarios[] = [
                    'title' => $scenario['title'],
                    'points' => $scenario['points']
                ];
                continue;
            }
            
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

    private function processTimelineBlock(array $content): array
    {
        $events = $content['events'] ?? [];
        
        $processedEvents = [];
        foreach ($events as $event) {
            $processedEvents[] = [
                'time' => $event['time'] ?? $event['milestone'] ?? '',
                'title' => $event['title'] ?? '',
                'description' => $event['description'] ?? $event['action'] ?? '',
                'priority' => $event['priority'] ?? 'medium'
            ];
        }
        
        return [
            'intro' => $content['intro'] ?? null,
            'events' => $processedEvents,
            'conclusion' => $content['conclusion'] ?? null
        ];
    }

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

        $this->addFaqSchema();
    }

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

    private function convertLegacyContent(): array
    {
        return [];
    }

    private function estimateReadingTime(): int
    {
        $wordCount = $this->processedData['word_count'] ?? 2500;
        return (int) ceil($wordCount / 200);
    }

    private function getCanonicalUrl(): string
    {
        return route('info.article.show', $this->article->slug);
    }

    private function getDefaultOgImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/info-center/images/default/generic.png';
    }
}
