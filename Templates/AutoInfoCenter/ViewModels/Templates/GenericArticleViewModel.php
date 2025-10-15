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
 * ESTRUTURA JSON ESPERADA:
 * {
 *   "article_metadata": {...},
 *   "content_blocks": [
 *     {
 *       "block_id": "intro",
 *       "block_type": "intro",
 *       "display_order": 1,
 *       "heading": "Título (opcional)",
 *       "content": {...}
 *     }
 *   ],
 *   "metadata": {...}
 * }
 * 
 * @author Claude Sonnet 4 - Engenheiro de Software Elite
 * @version 1.0 - Universal Generic Article System
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
     *核心 DO SISTEMA: Processa array de blocos modulares
     * 
     * @return void
     */
    private function processContentBlocks(): void
    {
        $metadata = $this->article->metadata ?? [];
        $contentBlocks = $metadata['content_blocks'] ?? [];

        if (empty($contentBlocks)) {
            // Fallback: tentar estrutura antiga
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
            'paragraphs' => $content['paragraphs'] ?? [],
            'emphasis' => $content['emphasis'] ?? null
        ];
    }

    /**
     * Processar bloco TABLE (Tabela)
     */
    private function processTableBlock(array $content): array
    {
        return [
            'description' => $content['description'] ?? null,
            'headers' => $content['headers'] ?? [],
            'rows' => $content['rows'] ?? [],
            'footer' => $content['footer'] ?? null
        ];
    }

    /**
     * Processar bloco LIST (Lista)
     */
    private function processListBlock(array $content): array
    {
        return [
            'list_style' => $content['list_style'] ?? 'bullet', // bullet | numbered | checklist
            'items' => $content['items'] ?? []
        ];
    }

    /**
     * Processar bloco ALERT (Alerta)
     */
    private function processAlertBlock(array $content): array
    {
        $alertType = $content['alert_type'] ?? 'info';
        
        return [
            'alert_type' => $alertType,
            'alert_type_label' => self::ALERT_TYPES[$alertType] ?? 'Informação',
            'title' => $content['title'] ?? null,
            'message' => $content['message'] ?? ''
        ];
    }

    /**
     * Processar bloco COMPARISON (Comparação)
     */
    private function processComparisonBlock(array $content): array
    {
        $items = $content['items'] ?? [];
        
        foreach ($items as &$item) {
            $item['pros'] = $item['pros'] ?? [];
            $item['cons'] = $item['cons'] ?? [];
            $item['conclusion'] = $item['conclusion'] ?? null;
        }
        
        return ['items' => $items];
    }

    /**
     * Processar bloco STEPS (Passo a passo)
     */
    private function processStepsBlock(array $content): array
    {
        $steps = $content['steps'] ?? [];
        
        foreach ($steps as &$step) {
            $step['number'] = $step['number'] ?? 0;
            $step['title'] = $step['title'] ?? '';
            $step['description'] = $step['description'] ?? '';
            $step['details'] = $step['details'] ?? [];
            $step['tip'] = $step['tip'] ?? null;
        }
        
        return ['steps' => $steps];
    }

    /**
     * Processar bloco TESTIMONIAL (Depoimentos)
     */
    private function processTestimonialBlock(array $content): array
    {
        $cases = $content['cases'] ?? [];
        
        foreach ($cases as &$case) {
            $case['user'] = $case['user'] ?? 'Anônimo';
            $case['situation'] = $case['situation'] ?? '';
            $case['result'] = $case['result'] ?? '';
            $case['observation'] = $case['observation'] ?? null;
        }
        
        return ['cases' => $cases];
    }

    /**
     * Processar bloco COST (Análise de custo)
     */
    private function processCostBlock(array $content): array
    {
        $scenarios = $content['scenarios'] ?? [];
        
        foreach ($scenarios as &$scenario) {
            $scenario['option'] = $scenario['option'] ?? '';
            $scenario['cost'] = $scenario['cost'] ?? '';
            $scenario['duration'] = $scenario['duration'] ?? null;
            $scenario['recommendation'] = $scenario['recommendation'] ?? '';
            $scenario['savings'] = $scenario['savings'] ?? null;
        }
        
        return ['scenarios' => $scenarios];
    }

    /**
     * Processar bloco MYTH (Mito vs Realidade)
     */
    private function processMythBlock(array $content): array
    {
        $items = $content['items'] ?? [];
        
        foreach ($items as &$item) {
            $reality = $item['reality'] ?? 'partial';
            
            $item['myth'] = $item['myth'] ?? '';
            $item['reality'] = $reality;
            $item['reality_label'] = $this->getRealityLabel($reality);
            $item['explanation'] = $item['explanation'] ?? '';
        }
        
        return ['items' => $items];
    }

    /**
     * Processar bloco FAQ (Perguntas Frequentes)
     */
    private function processFaqBlock(array $content): array
    {
        $questions = $content['questions'] ?? [];
        
        foreach ($questions as &$faq) {
            $faq['question'] = $faq['question'] ?? '';
            $faq['answer'] = $faq['answer'] ?? '';
            $faq['related_topics'] = $faq['related_topics'] ?? [];
        }
        
        return ['questions' => $questions];
    }

    /**
     * Processar bloco DECISION (Matriz de decisão)
     */
    private function processDecisionBlock(array $content): array
    {
        $scenarios = $content['scenarios'] ?? [];
        
        foreach ($scenarios as &$scenario) {
            $urgency = $scenario['urgency'] ?? 'medium';
            
            $scenario['condition'] = $scenario['condition'] ?? '';
            $scenario['action'] = $scenario['action'] ?? '';
            $scenario['urgency'] = $urgency;
            $scenario['urgency_label'] = self::URGENCY_LEVELS[$urgency] ?? 'Média';
            $scenario['reason'] = $scenario['reason'] ?? null;
        }
        
        return ['scenarios' => $scenarios];
    }

    /**
     * Processar bloco TIMELINE (Linha do tempo)
     */
    private function processTimelineBlock(array $content): array
    {
        $events = $content['events'] ?? [];
        
        foreach ($events as &$event) {
            $event['milestone'] = $event['milestone'] ?? '';
            $event['action'] = $event['action'] ?? '';
            $event['description'] = $event['description'] ?? null;
        }
        
        return ['events' => $events];
    }

    /**
     * Processar bloco CONCLUSION (Conclusão)
     */
    private function processConclusionBlock(array $content): array
    {
        return [
            'summary' => $content['summary'] ?? '',
            'key_takeaway' => $content['key_takeaway'] ?? null,
            'cta' => $content['cta'] ?? null
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
            'true' => '✅ Verdade',
            'false' => '❌ Mito',
            'partial' => '⚠️ Parcialmente Verdade',
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
        return $this->processedData['seo_data']['canonical_url'] ?? $this->getCanonicalUrl();
    }
}