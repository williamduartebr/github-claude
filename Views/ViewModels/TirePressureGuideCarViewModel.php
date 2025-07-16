<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * ViewModel para template de calibragem de pneus - Carros
 * 
 * Processa dados do TirePressureArticle para exibição no template tire_pressure_guide_car
 * Segue padrão dos ViewModels existentes (ReviewScheduleCarViewModel)
 */
class TirePressureGuideCarViewModel
{
    protected TirePressureArticle $article;
    protected array $processedData;

    public function __construct(TirePressureArticle $article)
    {
        $this->article = $article;
        $this->processedData = [];
        $this->processData();
    }

    /**
     * Processar dados do artigo
     */
    protected function processData(): void
    {
        // Dados básicos do veículo
        $this->processedData['vehicle_info'] = $this->processVehicleInfo();
        
        // Conteúdo estruturado do artigo
        $this->processedData['article_content'] = $this->processArticleContent();
        
        // Dados SEO e meta tags
        $this->processedData['seo'] = $this->processSeoData();
        
        // Dados estruturados (Schema.org)
        $this->processedData['structured_data'] = $this->buildStructuredData();
        
        // URLs e navegação
        $this->processedData['urls'] = $this->processUrls();
        
        // Dados específicos do template
        $this->processedData['template_data'] = $this->processTemplateSpecificData();
    }

    /**
     * Processar informações do veículo
     */
    protected function processVehicleInfo(): array
    {
        $vehicleData = $this->article->vehicle_data ?? [];
        
        return [
            'make' => $this->article->make,
            'model' => $this->article->model,
            'year' => $this->article->year,
            'full_name' => $this->article->vehicle_full_name,
            'tire_size' => $this->article->tire_size,
            'category' => $this->article->category ?? 'Carros',
            'vehicle_type' => 'car',
            'is_motorcycle' => false,
            
            // Pressões formatadas
            'pressure_display' => $this->article->pressure_display,
            'pressure_empty_display' => $vehicleData['pressure_empty_display'] ?? "{$this->article->pressure_empty_front}/{$this->article->pressure_empty_rear} PSI",
            'pressure_loaded_display' => $vehicleData['pressure_loaded_display'] ?? "{$this->article->pressure_max_front}/{$this->article->pressure_max_rear} PSI",
            
            // Imagem do veículo (se disponível)
            'image_url' => $vehicleData['image_url'] ?? $this->getDefaultCarImage(),
            'image_alt' => "Calibragem do pneu do {$this->article->vehicle_full_name}",
            
            // Identificadores
            'slug' => $this->article->slug,
            'wordpress_slug' => $this->article->wordpress_slug,
            'canonical_url' => $this->article->canonical_url ?? $this->article->getCanonicalUrl()
        ];
    }

    /**
     * Processar conteúdo estruturado do artigo
     */
    protected function processArticleContent(): array
    {
        $articleContent = $this->article->article_content ?? [];
        $sections = $articleContent['sections'] ?? [];
        
        return [
            // Seções principais
            'introduction' => $this->processSectionContent($sections['introduction'] ?? []),
            'middle_content' => $this->processSectionContent($sections['middle_content'] ?? []),
            'pressure_table' => $this->processPressureTable($sections['pressure_table'] ?? []),
            'how_to_calibrate' => $this->processHowToSteps($sections['how_to_calibrate'] ?? []),
            'maintenance_checklist' => $this->processChecklist($sections['maintenance_checklist'] ?? []),
            'faq' => $this->processFAQ($sections['faq'] ?? []),
            'conclusion' => $this->processSectionContent($sections['conclusion'] ?? []),
            
            // Elementos especiais
            'warnings' => $this->processWarnings($articleContent['warnings'] ?? []),
            'tips' => $this->processTips($articleContent['tips'] ?? []),
            
            // Metadata
            'metadata' => $articleContent['metadata'] ?? []
        ];
    }

    /**
     * Processar seção de conteúdo padrão
     */
    protected function processSectionContent(array $section): array
    {
        return [
            'title' => $section['title'] ?? '',
            'content' => $section['content'] ?? '',
            'type' => $section['type'] ?? 'text',
            'has_content' => !empty($section['content'])
        ];
    }

    /**
     * Processar tabela de pressões para carros
     */
    protected function processPressureTable(array $tableSection): array
    {
        $tableContent = $tableSection['content'] ?? [];
        
        $defaultTable = [
            'headers' => ['Situação do Veículo', 'Dianteiros (psi)', 'Traseiros (psi)', 'Observações'],
            'rows' => [
                ['Veículo vazio', $this->article->pressure_empty_front, $this->article->pressure_empty_rear, 'Ideal para uso diário'],
                ['Com carga leve', $this->article->pressure_light_front ?? ($this->article->pressure_empty_front + 2), $this->article->pressure_light_rear ?? ($this->article->pressure_empty_rear + 2), 'Recomendado para viagens curtas'],
                ['Com carga máxima', $this->article->pressure_max_front ?? ($this->article->pressure_empty_front + 6), $this->article->pressure_max_rear ?? ($this->article->pressure_empty_rear + 6), 'Essencial para segurança'],
                ['Estepe', $this->article->pressure_spare ?? 35, '–', 'Verifique a cada 15 dias']
            ]
        ];
        
        return [
            'title' => $tableSection['title'] ?? "Qual a Pressão Ideal para o {$this->article->vehicle_full_name}?",
            'headers' => $tableContent['headers'] ?? $defaultTable['headers'],
            'rows' => $tableContent['rows'] ?? $defaultTable['rows'],
            'has_spare' => !empty($this->article->pressure_spare),
            'note' => 'Sempre calibre com os pneus frios, antes de rodar mais de 2 km.'
        ];
    }

    /**
     * Processar passos de calibragem
     */
    protected function processHowToSteps(array $howToSection): array
    {
        $defaultSteps = [
            'Consulte a pressão recomendada no manual do veículo ou na etiqueta da porta do motorista.',
            'Use um calibrador digital confiável para verificar a pressão atual.',
            'Ajuste a pressão conforme a tabela acima, considerando a carga do veículo.',
            'Verifique todos os pneus, incluindo o estepe.',
            'Recoloque as tampas das válvulas para evitar entrada de sujeira.'
        ];
        
        $steps = $howToSection['content'] ?? $defaultSteps;
        
        return [
            'title' => $howToSection['title'] ?? "Como Calibrar os Pneus do {$this->article->make} {$this->article->model}",
            'steps' => $steps,
            'total_steps' => count($steps)
        ];
    }

    /**
     * Processar checklist de manutenção
     */
    protected function processChecklist(array $checklistSection): array
    {
        $defaultChecklist = [
            'Faça a calibragem a cada 15 dias ou antes de viagens longas.',
            'Inspecione os pneus visualmente para cortes, bolhas ou desgaste irregular.',
            'Realize alinhamento e balanceamento a cada 10.000 km.',
            'Considere o rodízio dos pneus para desgaste uniforme.',
            'Verifique a profundidade dos sulcos (mínimo legal é 1,6mm).'
        ];
        
        $items = $checklistSection['content'] ?? $defaultChecklist;
        
        return [
            'title' => $checklistSection['title'] ?? 'Checklist de Manutenção dos Pneus',
            'items' => $items,
            'total_items' => count($items)
        ];
    }

    /**
     * Processar FAQ
     */
    protected function processFAQ(array $faqSection): array
    {
        $defaultFAQ = [
            [
                'question' => 'Com que frequência devo calibrar os pneus do meu carro?',
                'answer' => 'Recomenda-se calibrar os pneus pelo menos a cada 15 dias ou antes de viagens longas.'
            ],
            [
                'question' => 'Posso usar pressões diferentes das recomendadas?',
                'answer' => 'Não é recomendado. As pressões foram calculadas pelos engenheiros para garantir segurança e desempenho ideal.'
            ],
            [
                'question' => "A pressão dos pneus do {$this->article->make} {$this->article->model} afeta o consumo de combustível?",
                'answer' => 'Sim, pneus com baixa pressão podem aumentar o consumo em até 10% devido à maior resistência ao rolamento.'
            ]
        ];
        
        $faqItems = $faqSection['content'] ?? $defaultFAQ;
        
        return [
            'title' => $faqSection['title'] ?? 'Perguntas Frequentes',
            'items' => $faqItems,
            'total_items' => count($faqItems)
        ];
    }

    /**
     * Processar avisos
     */
    protected function processWarnings(array $warnings): array
    {
        return array_map(function ($warning) {
            return [
                'content' => $warning['content'] ?? '',
                'type' => $warning['type'] ?? 'warning',
                'css_class' => 'warning-box alert-warning'
            ];
        }, $warnings);
    }

    /**
     * Processar dicas
     */
    protected function processTips(array $tips): array
    {
        return array_map(function ($tip) {
            return [
                'content' => $tip['content'] ?? '',
                'type' => $tip['type'] ?? 'tip',
                'css_class' => 'tip-box alert-info'
            ];
        }, $tips);
    }

    /**
     * Processar dados SEO
     */
    protected function processSeoData(): array
    {
        $vehicleFullName = $this->article->vehicle_full_name;
        $pressureDisplay = $this->processedData['vehicle_info']['pressure_empty_display'] ?? '';
        
        return [
            'title' => $this->article->title ?? "Calibragem do Pneu do {$vehicleFullName}",
            'meta_description' => $this->article->meta_description ?? "Saiba a pressão ideal para calibrar os pneus do {$vehicleFullName}. Pressões: {$pressureDisplay}. Veja dicas e tabela completa para segurança e economia!",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "calibragem pneu {$this->article->make} {$this->article->model} {$this->article->year}",
            'canonical_url' => $this->article->canonical_url ?? $this->article->getCanonicalUrl(),
            'og_title' => "Calibragem do Pneu do {$vehicleFullName} - Pressão Ideal",
            'og_description' => "Guia completo de calibragem para {$vehicleFullName}. Tabela de pressões, dicas e manutenção.",
            'og_image' => $this->processedData['vehicle_info']['image_url'] ?? $this->getDefaultCarImage(),
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Construir dados estruturados Schema.org
     */
    protected function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $imageDefault = $this->getDefaultCarImage();
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => "Como Calibrar os Pneus do {$this->article->vehicle_full_name}",
            'description' => "Guia completo sobre calibragem de pneus do {$this->article->vehicle_full_name}, incluindo pressões recomendadas e dicas de manutenção.",
            'image' => [
                '@type' => 'ImageObject',
                'url' => $vehicleInfo['image_url'] ?? $imageDefault,
                'width' => 1200,
                'height' => 630
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'url' => 'https://mercadoveiculos.com'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Mercado Veículos',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/logos/default_share_image.jpg',
                ],
            ],
            'totalTime' => 'PT15M',
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'BRL',
                'value' => '5-15'
            ],
            'supply' => [
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Calibrador de pneus'
                ],
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Manual do veículo'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Medidor de pressão digital'
                ]
            ],
            'step' => $this->buildHowToSteps(),
            'about' => [
                '@type' => 'Vehicle',
                'name' => $this->article->vehicle_full_name,
                'brand' => $this->article->make,
                'model' => $this->article->model,
                'vehicleModelDate' => $this->article->year
            ],
            'mainEntity' => [
                '@type' => 'FAQPage',
                'mainEntity' => $this->buildFAQStructuredData()
            ]
        ];
    }

    /**
     * Construir passos do HowTo para dados estruturados
     */
    protected function buildHowToSteps(): array
    {
        $steps = [];
        $stepNumber = 1;
        $canonicalUrl = $this->article->canonical_url ?? '';

        $stepContents = [
            'Consulte a pressão recomendada no manual do veículo ou na etiqueta da porta do motorista',
            'Use um calibrador digital confiável para verificar a pressão atual dos pneus',
            'Ajuste a pressão conforme a tabela de pressões, considerando a carga do veículo',
            'Verifique todos os pneus, incluindo o estepe se disponível',
            'Recoloque as tampas das válvulas para evitar entrada de sujeira'
        ];

        foreach ($stepContents as $content) {
            $currentStepNumber = $stepNumber;
            $steps[] = [
                '@type' => 'HowToStep',
                'position' => $stepNumber,
                'name' => "Passo {$currentStepNumber}",
                'text' => $content,
                'url' => $canonicalUrl . "#passo-{$currentStepNumber}"
            ];
            $stepNumber++;
        }

        return $steps;
    }

    /**
     * Construir FAQ para dados estruturados
     */
    protected function buildFAQStructuredData(): array
    {
        $faqData = $this->processedData['article_content']['faq']['items'] ?? [];
        $structuredFAQ = [];

        foreach ($faqData as $faq) {
            $structuredFAQ[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }

        return $structuredFAQ;
    }

    /**
     * Processar URLs
     */
    protected function processUrls(): array
    {
        return [
            'canonical' => $this->article->canonical_url ?? $this->article->getCanonicalUrl(),
            'wordpress' => $this->article->wordpress_url ?? $this->article->getWordPressUrl(),
            'amp' => $this->article->amp_url ?? null,
            'share_facebook' => $this->generateShareUrl('facebook'),
            'share_twitter' => $this->generateShareUrl('twitter'),
            'share_whatsapp' => $this->generateShareUrl('whatsapp')
        ];
    }

    /**
     * Processar dados específicos do template
     */
    protected function processTemplateSpecificData(): array
    {
        return [
            'template_name' => 'tire_pressure_guide_car',
            'template_version' => '1.0',
            'vehicle_icon' => '🚗',
            'pressure_unit' => 'PSI',
            'show_spare_tire' => !empty($this->article->pressure_spare),
            'calibration_frequency' => '15 dias',
            'calibration_frequency_long_trips' => 'antes de viagens longas',
            'min_tread_depth' => '1.6mm',
            'alignment_frequency' => '10.000 km',
            'rotation_recommended' => true,
            'estimated_fuel_savings' => 'até 10%',
            'safety_benefits' => [
                'Melhor aderência em curvas',
                'Frenagem mais eficiente',
                'Redução do risco de aquaplanagem',
                'Maior estabilidade em alta velocidade'
            ],
            'economic_benefits' => [
                'Economia de combustível',
                'Maior vida útil dos pneus',
                'Redução do desgaste da suspensão',
                'Menor custo de manutenção'
            ]
        ];
    }

    /**
     * Gerar URL de compartilhamento
     */
    protected function generateShareUrl(string $platform): string
    {
        $url = urlencode($this->article->canonical_url ?? $this->article->getCanonicalUrl());
        $title = urlencode($this->article->title ?? '');
        $text = urlencode("Guia de calibragem para {$this->article->vehicle_full_name}");

        switch ($platform) {
            case 'facebook':
                return "https://www.facebook.com/sharer/sharer.php?u={$url}";
            case 'twitter':
                return "https://twitter.com/intent/tweet?url={$url}&text={$text}";
            case 'whatsapp':
                return "https://wa.me/?text={$text}%20{$url}";
            default:
                return '';
        }
    }

    /**
     * Obter imagem padrão para carros
     */
    protected function getDefaultCarImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/images/tire-pressure-car-default.jpg';
    }

    /**
     * Obter breadcrumbs para navegação
     */
    public function getBreadcrumbs(): array
    {
        return [
            [
                'title' => 'Home',
                'url' => '/',
                'position' => 1
            ],
            [
                'title' => 'Info Center',
                'url' => '/info',
                'position' => 2
            ],
            [
                'title' => 'Calibragem de Pneus',
                'url' => '/info/categorias/calibragem-pneus',
                'position' => 3
            ],
            [
                'title' => $this->article->vehicle_full_name,
                'url' => '',
                'position' => 4
            ]
        ];
    }

    /**
     * Obter dados para meta tags específicas do template
     */
    public function getMetaTags(): array
    {
        $vehicleFullName = $this->article->vehicle_full_name;
        $pressureDisplay = $this->processedData['vehicle_info']['pressure_empty_display'];

        return [
            'title' => "Calibragem do Pneu do {$vehicleFullName} - Pressão Ideal",
            'description' => "Guia completo sobre calibragem de pneus do {$vehicleFullName}. Pressões recomendadas ({$pressureDisplay}), tabela completa, dicas de manutenção e economia de combustível.",
            'keywords' => implode(', ', [
                "calibragem pneu {$vehicleFullName}",
                "pressão pneu {$this->article->make} {$this->article->model}",
                "manutenção automotiva",
                "economia combustível",
                "segurança veicular"
            ]),
            'robots' => 'index, follow',
            'author' => 'Equipe Mercado Veículos',
            'article:published_time' => $this->article->created_at?->toISOString(),
            'article:modified_time' => $this->article->updated_at?->toISOString(),
            'article:section' => 'Calibragem de Pneus',
            'article:tag' => implode(', ', $this->article->seo_keywords ?? [])
        ];
    }

    /**
     * Verificar se dados estão completos
     */
    public function isDataComplete(): bool
    {
        return !empty($this->article->make) &&
               !empty($this->article->model) &&
               !empty($this->article->year) &&
               !empty($this->article->pressure_empty_front) &&
               !empty($this->article->pressure_empty_rear) &&
               !empty($this->article->article_content);
    }

    /**
     * Obter nível de qualidade do conteúdo
     */
    public function getQualityLevel(): string
    {
        $score = $this->article->content_score ?? 5.0;
        
        if ($score >= 8.5) return 'excellent';
        if ($score >= 7.0) return 'good';
        if ($score >= 5.5) return 'average';
        if ($score >= 4.0) return 'poor';
        return 'very_poor';
    }

    /**
     * Obter status de refinamento Claude
     */
    public function getClaudeStatus(): array
    {
        return [
            'status' => $this->article->claude_status,
            'enhancement_count' => $this->article->claude_enhancement_count ?? 0,
            'last_enhanced' => $this->article->claude_last_enhanced_at,
            'can_be_enhanced' => $this->article->canBeEnhancedByClaude()
        ];
    }

    /**
     * Getter mágico para acessar dados processados
     */
    public function __get(string $property)
    {
        return $this->processedData[$property] ?? null;
    }

    /**
     * Verificar se propriedade existe
     */
    public function __isset(string $property): bool
    {
        return isset($this->processedData[$property]);
    }

    /**
     * Obter todos os dados processados
     */
    public function toArray(): array
    {
        return $this->processedData;
    }

    /**
     * Obter artigo original
     */
    public function getArticle(): TirePressureArticle
    {
        return $this->article;
    }
}