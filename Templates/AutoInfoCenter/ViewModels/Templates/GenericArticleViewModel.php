<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GenericArticleViewModel - ViewModel Universal para Artigos Genéricos
 * 
 * @author Claude Sonnet 4.5
 * @version 2.1 - CORRIGIDO: Normalização do bloco COMPARISON
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
        $this->processedData['related_articles'] = $generalMetadata['related_articles'] ?? [];
    }

    private function processContentBlocks(): void
    {
        $metadata = $this->article->metadata ?? [];
        $contentBlocks = $metadata['content_blocks'] ?? [];

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

    /**
     * ✅ CORRIGIDO: Processar bloco COMPARISON com normalização inteligente
     * 
     * Suporta 2 formatos de entrada e SEMPRE retorna formato normalizado:
     * 
     * FORMATO 1 (Correto - do Prompt):
     * {
     *   "intro": "...",
     *   "items": [
     *     {
     *       "aspect": "Viscosidade a Frio",
     *       "option_a": "5W (descrição completa)",
     *       "option_b": "5W (descrição completa)"
     *     }
     *   ],
     *   "conclusion": "..."
     * }
     * 
     * FORMATO 2 (Legado - inconsistente):
     * {
     *   "intro": "...",
     *   "items": [
     *     {
     *       "title": "Título",
     *       "features": [...],
     *       "pros": [...],
     *       "cons": [...],
     *       "conclusion": "..."
     *     }
     *   ]
     * }
     * 
     * FORMATO 3 (Bugado - sem aspect):
     * {
     *   "items": [
     *     {"option_a": "5W", "option_b": "5W"},
     *     {"option_a": "30", "option_b": "40"}
     *   ]
     * }
     * 
     * ⚠️ ESTE MÉTODO FAZ NORMALIZAÇÃO AUTOMÁTICA ⚠️
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

        // Detectar formato dos items
        $firstItem = $items[0] ?? [];
        $format = $this->detectComparisonFormat($firstItem);

        Log::info('Formato de comparison detectado', [
            'article_id' => $this->article->id,
            'format' => $format,
            'total_items' => count($items)
        ]);

        // Processar items de acordo com o formato
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
     */
    private function detectComparisonFormat(array $firstItem): string
    {
        // Formato correto: tem aspect + option_a + option_b
        if (!empty($firstItem['aspect']) && 
            (isset($firstItem['option_a']) || isset($firstItem['option_b']))) {
            return 'aspect_based';
        }

        // Formato legado: tem title + pros/cons/features
        if (!empty($firstItem['title']) && 
            (isset($firstItem['pros']) || isset($firstItem['cons']) || isset($firstItem['features']))) {
            return 'pros_cons';
        }

        // Formato quebrado: tem option_a/option_b mas sem aspect
        if ((isset($firstItem['option_a']) || isset($firstItem['option_b'])) && 
            empty($firstItem['aspect'])) {
            return 'broken';
        }

        return 'unknown';
    }

    /**
     * Normalizar comparison baseada em aspectos (FORMATO CORRETO)
     */
    private function normalizeAspectBasedComparison(array $items): array
    {
        $normalized = [];

        foreach ($items as $index => $item) {
            $aspect = $item['aspect'] ?? null;

            // Se aspect estiver vazio, tentar inferir ou pular
            if (empty($aspect)) {
                Log::warning('Item de comparison sem aspect', [
                    'article_id' => $this->article->id,
                    'item_index' => $index,
                    'item' => $item
                ]);
                
                // Tentar inferir aspect baseado no conteúdo
                $aspect = $this->inferAspect($item, $index);
            }

            $normalized[] = [
                'aspect' => $aspect,
                'option_a' => $item['option_a'] ?? '',
                'option_b' => $item['option_b'] ?? '',
                'features' => [],
                'pros' => [],
                'cons' => [],
                'conclusion' => null
            ];
        }

        return $normalized;
    }

    /**
     * Normalizar comparison de prós e contras (FORMATO LEGADO)
     */
    private function normalizeProsConsComparison(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $normalized[] = [
                'aspect' => null, // Não tem aspect neste formato
                'option_a' => null,
                'option_b' => null,
                'title' => $item['title'] ?? $item['label'] ?? '',
                'features' => $item['features'] ?? [],
                'pros' => $item['pros'] ?? [],
                'cons' => $item['cons'] ?? [],
                'conclusion' => $item['conclusion'] ?? null
            ];
        }

        return $normalized;
    }

    /**
     * ✅ CORRIGIR FORMATO QUEBRADO (sem aspect)
     * 
     * Este é o caso do bug reportado:
     * [
     *   {"option_a": "5W", "option_b": "5W"},
     *   {"option_a": "30", "option_b": "40"}
     * ]
     */
    private function fixBrokenComparison(array $items, array $fullContent): array
    {
        Log::warning('Detectado bloco comparison com formato quebrado - aplicando correção automática', [
            'article_id' => $this->article->id,
            'total_items' => count($items)
        ]);

        $normalized = [];
        $aspectMap = $this->getDefaultAspectMap();

        foreach ($items as $index => $item) {
            // Tentar inferir aspect baseado na posição e conteúdo
            $inferredAspect = $aspectMap[$index] ?? "Característica " . ($index + 1);

            // Se conseguir inferir melhor baseado no conteúdo, usar
            $betterAspect = $this->inferAspectFromContent(
                $item['option_a'] ?? '', 
                $item['option_b'] ?? ''
            );

            if (!empty($betterAspect)) {
                $inferredAspect = $betterAspect;
            }

            $normalized[] = [
                'aspect' => $inferredAspect,
                'option_a' => $item['option_a'] ?? '',
                'option_b' => $item['option_b'] ?? '',
                'features' => [],
                'pros' => [],
                'cons' => [],
                'conclusion' => null
            ];
        }

        return $normalized;
    }

    /**
     * Inferir aspect baseado no índice (fallback inteligente)
     */
    private function inferAspect(array $item, int $index): string
    {
        $optionA = $item['option_a'] ?? '';
        $optionB = $item['option_b'] ?? '';

        // Tentar inferir do conteúdo primeiro
        $inferred = $this->inferAspectFromContent($optionA, $optionB);
        if (!empty($inferred)) {
            return $inferred;
        }

        // Fallback: usar mapa padrão
        $defaultMap = $this->getDefaultAspectMap();
        return $defaultMap[$index] ?? "Aspecto " . ($index + 1);
    }

    /**
     * Inferir aspect analisando o conteúdo das opções
     */
    private function inferAspectFromContent(string $optionA, string $optionB): ?string
    {
        $combined = strtolower($optionA . ' ' . $optionB);

        // Palavras-chave que indicam aspectos específicos
        $keywords = [
            'viscosidade' => 'Viscosidade',
            'temperatura' => 'Temperatura',
            'economia' => 'Economia de Combustível',
            'proteção' => 'Proteção do Motor',
            'vedação' => 'Vedação',
            'durabilidade' => 'Durabilidade',
            'custo' => 'Custo',
            'preço' => 'Preço',
            'performance' => 'Performance',
            'aplicação' => 'Aplicação',
            'recomendação' => 'Recomendação',
            'intervalo' => 'Intervalo de Troca',
            'km' => 'Quilometragem',
            'potência' => 'Potência',
            'consumo' => 'Consumo',
            'atrito' => 'Atrito',
            'fluidez' => 'Fluidez',
        ];

        foreach ($keywords as $keyword => $aspect) {
            if (str_contains($combined, $keyword)) {
                return $aspect;
            }
        }

        return null;
    }

    /**
     * Mapa padrão de aspectos por posição (para casos de óleo)
     */
    private function getDefaultAspectMap(): array
    {
        return [
            0 => 'Viscosidade a Frio',
            1 => 'Viscosidade a Quente',
            2 => 'Proteção do Motor',
            3 => 'Economia de Combustível',
            4 => 'Vedação e Desgaste',
            5 => 'Aplicação Recomendada',
            6 => 'Custo-Benefício',
            7 => 'Durabilidade',
        ];
    }

    // ========================================
    // OUTROS MÉTODOS DO VIEWMODEL (mantidos)
    // ========================================

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
            'text' => $content['text'] ?? '',
            'paragraphs' => $content['paragraphs'] ?? [],
            'emphasis' => $content['emphasis'] ?? null
        ];
    }

    private function processTableBlock(array $content): array
    {
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

    private function processTestimonialBlock(array $content): array
    {
        if (!empty($content['quote'])) {
            return [
                'quote' => $content['quote'] ?? '',
                'author' => $content['author'] ?? 'Anônimo',
                'vehicle' => $content['vehicle'] ?? null,
                'context' => $content['context'] ?? null
            ];
        }
        
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

    private function processCostBlock(array $content): array
    {
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
        
        $scenarios = $content['scenarios'] ?? [];
        $processedScenarios = [];
        
        foreach ($scenarios as $scenario) {
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

    private function processMythBlock(array $content): array
    {
        $myths = $content['myths'] ?? [];
        
        $processedMyths = [];
        foreach ($myths as $myth) {
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

    private function getRealityLabel(string $reality): string
    {
        return match($reality) {
            'true' => '✅ Verdadeiro',
            'false' => '❌ Mito',
            'partial' => '⚠️ Parcialmente Verdadeiro',
            default => 'Não definido'
        };
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