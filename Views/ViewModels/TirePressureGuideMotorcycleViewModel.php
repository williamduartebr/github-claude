<?php

namespace Src\AutoInfoCenter\ViewModels\Templates;

use Illuminate\Support\Str;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * ViewModel para template de calibragem de pneus - Motocicletas
 * 
 * Processa dados do TirePressureArticle para exibição no template tire_pressure_guide_motorcycle
 * Adaptado especificamente para motocicletas com suas particularidades
 */
class TirePressureGuideMotorcycleViewModel
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
        
        // Dados específicos do template para motos
        $this->processedData['template_data'] = $this->processMotorcycleSpecificData();
    }

    /**
     * Processar informações do veículo (motocicleta)
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
            'category' => $this->article->category ?? 'Motocicletas',
            'vehicle_type' => 'motorcycle',
            'is_motorcycle' => true,
            
            // Pressões formatadas para motos
            'pressure_display' => $this->article->pressure_display,
            'pressure_front_display' => "{$this->article->pressure_empty_front} PSI",
            'pressure_rear_display' => "{$this->article->pressure_empty_rear} PSI",
            'pressure_range_front' => $this->getPressureRange('front'),
            'pressure_range_rear' => $this->getPressureRange('rear'),
            
            // Imagem da motocicleta
            'image_url' => $vehicleData['image_url'] ?? $this->getDefaultMotorcycleImage(),
            'image_alt' => "Calibragem do pneu da {$this->article->vehicle_full_name}",
            
            // Identificadores
            'slug' => $this->article->slug,
            'wordpress_slug' => $this->article->wordpress_slug,
            'canonical_url' => $this->article->canonical_url ?? $this->article->getCanonicalUrl()
        ];
    }

    /**
     * Processar conteúdo estruturado específico para motos
     */
    protected function processArticleContent(): array
    {
        $articleContent = $this->article->article_content ?? [];
        $sections = $articleContent['sections'] ?? [];
        
        return [
            // Seções principais
            'introduction' => $this->processSectionContent($sections['introduction'] ?? []),
            'middle_content' => $this->processSectionContent($sections['middle_content'] ?? []),
            'pressure_table' => $this->processMotorcyclePressureTable($sections['pressure_table'] ?? []),
            'how_to_calibrate' => $this->processMotorcycleHowToSteps($sections['how_to_calibrate'] ?? []),
            'maintenance_checklist' => $this->processMotorcycleChecklist($sections['maintenance_checklist'] ?? []),
            'faq' => $this->processMotorcycleFAQ($sections['faq'] ?? []),
            'conclusion' => $this->processSectionContent($sections['conclusion'] ?? []),
            
            // Elementos especiais para motos
            'warnings' => $this->processMotorcycleWarnings($articleContent['warnings'] ?? []),
            'tips' => $this->processMotorcycleTips($articleContent['tips'] ?? []),
            'safety_alerts' => $this->processMotorcycleSafetyAlerts(),
            
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
     * Processar tabela de pressões específica para motocicletas
     */
    protected function processMotorcyclePressureTable(array $tableSection): array
    {
        $tableContent = $tableSection['content'] ?? [];
        
        $defaultTable = [
            'headers' => ['Pneu', 'Pressão Normal (psi)', 'Pressão Máxima (psi)', 'Observações'],
            'rows' => [
                ['Dianteiro', $this->article->pressure_empty_front, $this->article->pressure_max_front ?? ($this->article->pressure_empty_front + 4), 'Calibrar a frio'],
                ['Traseiro', $this->article->pressure_empty_rear, $this->article->pressure_max_rear ?? ($this->article->pressure_empty_rear + 4), 'Ajustar com garupa']
            ]
        ];
        
        return [
            'title' => $tableSection['title'] ?? "Qual a Pressão Ideal para a {$this->article->vehicle_full_name}?",
            'headers' => $tableContent['headers'] ?? $defaultTable['headers'],
            'rows' => $tableContent['rows'] ?? $defaultTable['rows'],
            'has_passenger_adjustment' => true,
            'note' => 'Sempre calibre com os pneus frios. Para pilotagem com garupa, aumente 2-3 PSI no pneu traseiro.',
            'cold_tire_emphasis' => 'IMPORTANTE: Meça sempre com pneus frios (moto parada há pelo menos 3 horas).'
        ];
    }

    /**
     * Processar passos de calibragem para motocicletas
     */
    protected function processMotorcycleHowToSteps(array $howToSection): array
    {
        $defaultSteps = [
            'Consulte a pressão recomendada no manual da sua motocicleta ou na etiqueta/adesivo da moto.',
            'Use um calibrador digital confiável para verificar a pressão atual.',
            'Ajuste a pressão conforme a tabela acima, considerando se você pilota sozinho ou com garupa.',
            'Nunca exceda os valores máximos recomendados pelo fabricante.',
            'Recoloque as tampas das válvulas para evitar entrada de sujeira e umidade.'
        ];
        
        $steps = $howToSection['content'] ?? $defaultSteps;
        
        return [
            'title' => $howToSection['title'] ?? "Como Calibrar os Pneus da {$this->article->make} {$this->article->model}",
            'steps' => $steps,
            'total_steps' => count($steps),
            'safety_reminder' => 'Lembre-se: pneus são o único ponto de contato da moto com o solo!'
        ];
    }

    /**
     * Processar checklist de manutenção para motocicletas
     */
    protected function processMotorcycleChecklist(array $checklistSection): array
    {
        $defaultChecklist = [
            'Faça a calibragem semanalmente e sempre antes de viagens longas.',
            'Inspecione os pneus visualmente para cortes, objetos encravados, bolhas ou desgaste irregular.',
            'Verifique a profundidade dos sulcos (mínimo legal é 1,6mm, mas para motos recomenda-se trocar antes de chegar a 2mm).',
            'Observe o padrão de desgaste - desgaste central indica pressão excessiva; desgaste nas laterais indica pressão baixa.',
            'Em motos, é recomendável trocar os dois pneus juntos para manter o equilíbrio.'
        ];
        
        $items = $checklistSection['content'] ?? $defaultChecklist;
        
        return [
            'title' => $checklistSection['title'] ?? 'Checklist de Manutenção dos Pneus',
            'items' => $items,
            'total_items' => count($items),
            'frequency_note' => 'Para motocicletas, verificações mais frequentes são essenciais para a segurança.'
        ];
    }

    /**
     * Processar FAQ específico para motocicletas
     */
    protected function processMotorcycleFAQ(array $faqSection): array
    {
        $defaultFAQ = [
            [
                'question' => 'Com que frequência devo calibrar os pneus da minha moto?',
                'answer' => 'Recomenda-se calibrar os pneus pelo menos a cada 7 dias para motos. Antes de viagens longas, é essencial verificar a pressão.'
            ],
            [
                'question' => 'Por que a pressão do pneu traseiro é diferente do dianteiro em motocicletas?',
                'answer' => 'O pneu traseiro suporta mais peso e transferência de força durante aceleração, exigindo uma pressão diferente para otimizar desempenho e segurança.'
            ],
            [
                'question' => "Como a temperatura afeta a pressão dos pneus da {$this->article->make} {$this->article->model}?",
                'answer' => 'A cada 10°C de aumento na temperatura ambiente, a pressão do pneu pode aumentar cerca de 1 PSI. Por isso, é importante calibrar os pneus frios e considerar as variações climáticas.'
            ],
            [
                'question' => 'Devo ajustar a pressão quando ando com garupa?',
                'answer' => 'Sim, quando andar com passageiro, aumente 2-3 PSI no pneu traseiro para compensar o peso adicional e manter a estabilidade.'
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
     * Processar avisos específicos para motocicletas
     */
    protected function processMotorcycleWarnings(array $warnings): array
    {
        $processedWarnings = array_map(function ($warning) {
            return [
                'content' => $warning['content'] ?? '',
                'type' => $warning['type'] ?? 'warning',
                'css_class' => 'warning-box alert-danger'
            ];
        }, $warnings);

        // Adicionar aviso padrão sobre segurança em motos
        $processedWarnings[] = [
            'content' => 'Atenção: Em motocicletas, pneus mal calibrados podem afetar drasticamente a estabilidade e aderência, comprometendo sua segurança.',
            'type' => 'safety',
            'css_class' => 'safety-warning alert-danger'
        ];

        return $processedWarnings;
    }

    /**
     * Processar dicas específicas para motocicletas
     */
    protected function processMotorcycleTips(array $tips): array
    {
        $processedTips = array_map(function ($tip) {
            return [
                'content' => $tip['content'] ?? '',
                'type' => $tip['type'] ?? 'tip',
                'css_class' => 'tip-box alert-info'
            ];
        }, $tips);

        // Adicionar dica específica para motos
        $processedTips[] = [
            'content' => 'Dica: Sempre verifique o manual do proprietário para as pressões específicas da sua motocicleta, especialmente para diferentes condições de uso.',
            'type' => 'motorcycle_tip',
            'css_class' => 'motorcycle-tip alert-primary'
        ];

        return $processedTips;
    }

    /**
     * Processar alertas de segurança específicos para motocicletas
     */
    protected function processMotorcycleSafetyAlerts(): array
    {
        return [
            [
                'icon' => '⚠️',
                'title' => 'Segurança Crítica',
                'content' => 'Pneus são fundamentais para a segurança em motocicletas. Verificações regulares podem salvar vidas.',
                'type' => 'critical'
            ],
            [
                'icon' => '🌡️',
                'title' => 'Temperatura dos Pneus',
                'content' => 'Após longas viagens, aguarde os pneus esfriarem antes de verificar a pressão.',
                'type' => 'temperature'
            ],
            [
                'icon' => '👥',
                'title' => 'Pilotagem com Garupa',
                'content' => 'Ajuste sempre a pressão traseira quando carregar passageiro.',
                'type' => 'passenger'
            ]
        ];
    }

    /**
     * Obter range de pressão para pneu específico
     */
    protected function getPressureRange(string $position): array
    {
        $empty = $position === 'front' ? $this->article->pressure_empty_front : $this->article->pressure_empty_rear;
        $max = $position === 'front' ? 
               ($this->article->pressure_max_front ?? $empty + 4) : 
               ($this->article->pressure_max_rear ?? $empty + 4);
        
        return [
            'min' => $empty,
            'max' => $max,
            'display' => "{$empty}-{$max} PSI"
        ];
    }

    /**
     * Processar dados SEO para motocicletas
     */
    protected function processSeoData(): array
    {
        $vehicleFullName = $this->article->vehicle_full_name;
        $pressureDisplay = $this->processedData['vehicle_info']['pressure_display'] ?? '';
        
        return [
            'title' => $this->article->title ?? "Calibragem do Pneu da {$vehicleFullName}",
            'meta_description' => $this->article->meta_description ?? "Saiba a pressão ideal para calibrar os pneus da sua {$vehicleFullName}. Pressões: {$pressureDisplay}. Veja dicas e tabela completa para segurança e performance!",
            'keywords' => $this->article->seo_keywords ?? [],
            'focus_keyword' => "calibragem pneu {$this->article->make} {$this->article->model} {$this->article->year}",
            'canonical_url' => $this->article->canonical_url ?? $this->article->getCanonicalUrl(),
            'og_title' => "Calibragem do Pneu da {$vehicleFullName} - Pressão Ideal",
            'og_description' => "Guia completo de calibragem para {$vehicleFullName}. Tabela de pressões, dicas de segurança e manutenção.",
            'og_image' => $this->processedData['vehicle_info']['image_url'] ?? $this->getDefaultMotorcycleImage(),
            'og_type' => 'article',
            'twitter_card' => 'summary_large_image'
        ];
    }

    /**
     * Construir dados estruturados Schema.org para motocicletas
     */
    protected function buildStructuredData(): array
    {
        $vehicleInfo = $this->processedData['vehicle_info'];
        $imageDefault = $this->getDefaultMotorcycleImage();
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => "Como Calibrar os Pneus da {$this->article->vehicle_full_name}",
            'description' => "Guia completo sobre calibragem de pneus da {$this->article->vehicle_full_name}, incluindo pressões recomendadas e dicas de segurança para motocicletas.",
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
            'totalTime' => 'PT10M',
            'estimatedCost' => [
                '@type' => 'MonetaryAmount',
                'currency' => 'BRL',
                'value' => '5-10'
            ],
            'supply' => [
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Calibrador de pneus'
                ],
                [
                    '@type' => 'HowToSupply',
                    'name' => 'Manual da motocicleta'
                ]
            ],
            'tool' => [
                [
                    '@type' => 'HowToTool',
                    'name' => 'Medidor de pressão digital'
                ]
            ],
            'step' => $this->buildMotorcycleHowToSteps(),
            'about' => [
                '@type' => 'Motorcycle',
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
     * Construir passos do HowTo específicos para motocicletas
     */
    protected function buildMotorcycleHowToSteps(): array
    {
        $steps = [];
        $stepNumber = 1;
        $canonicalUrl = $this->article->canonical_url ?? '';

        $stepContents = [
            'Consulte a pressão recomendada no manual da motocicleta ou na etiqueta do veículo',
            'Certifique-se de que os pneus estejam frios (moto parada há pelo menos 3 horas)',
            'Use um calibrador digital confiável para verificar a pressão atual',
            'Ajuste a pressão conforme recomendado, considerando se pilota sozinho ou com garupa',
            'Recoloque as tampas das válvulas para proteger contra sujeira e umidade'
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
     * Processar dados específicos para template de motocicletas
     */
    protected function processMotorcycleSpecificData(): array
    {
        return [
            'template_name' => 'tire_pressure_guide_motorcycle',
            'template_version' => '1.0',
            'vehicle_icon' => '🏍️',
            'pressure_unit' => 'PSI',
            'calibration_frequency' => '7 dias',
            'calibration_frequency_long_trips' => 'antes de viagens longas',
            'min_tread_depth' => '1.6mm',
            'recommended_replacement_depth' => '2.0mm',
            'tire_rotation_possible' => false,
            'passenger_adjustment_needed' => true,
            'passenger_adjustment_amount' => '2-3 PSI (traseiro)',
            'cold_tire_wait_time' => '3 horas',
            'estimated_performance_improvement' => 'até 15%',
            'safety_benefits' => [
                'Melhor aderência em curvas fechadas',
                'Frenagem mais eficiente e segura',
                'Maior estabilidade em alta velocidade',
                'Redução do risco de derrapagem',
                'Melhor controle em piso molhado'
            ],
            'economic_benefits' => [
                'Economia de combustível',
                'Maior vida útil dos pneus',
                'Redução do aquecimento excessivo',
                'Menor desgaste da suspensão'
            ],
            'motorcycle_specific_risks' => [
                'Perda de aderência em curvas',
                'Instabilidade em frenagens',
                'Aquecimento excessivo dos pneus',
                'Desgaste irregular e prematuro'
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
     * Obter imagem padrão para motocicletas
     */
    protected function getDefaultMotorcycleImage(): string
    {
        return 'https://mercadoveiculos.s3.us-east-1.amazonaws.com/statics/images/tire-pressure-motorcycle-default.jpg';
    }

    /**
     * Obter breadcrumbs específicos para motocicletas
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
                'title' => 'Motocicletas',
                'url' => '/info/categorias/calibragem-pneus/motocicletas',
                'position' => 4
            ],
            [
                'title' => $this->article->vehicle_full_name,
                'url' => '',
                'position' => 5
            ]
        ];
    }

    /**
     * Obter dados para meta tags específicas para motocicletas
     */
    public function getMetaTags(): array
    {
        $vehicleFullName = $this->article->vehicle_full_name;
        $pressureDisplay = $this->processedData['vehicle_info']['pressure_display'];

        return [
            'title' => "Calibragem do Pneu da {$vehicleFullName} - Pressão Ideal",
            'description' => "Guia completo sobre calibragem de pneus da {$vehicleFullName}. Pressões recomendadas ({$pressureDisplay}), dicas de segurança, ajustes para garupa e manutenção preventiva.",
            'keywords' => implode(', ', [
                "calibragem pneu {$vehicleFullName}",
                "pressão pneu {$this->article->make} {$this->article->model}",
                "calibragem moto {$this->article->make}",
                "segurança motocicleta",
                "manutenção moto",
                "pneu moto garupa"
            ]),
            'robots' => 'index, follow',
            'author' => 'Equipe Mercado Veículos',
            'article:published_time' => $this->article->created_at?->toISOString(),
            'article:modified_time' => $this->article->updated_at?->toISOString(),
            'article:section' => 'Calibragem de Pneus - Motocicletas',
            'article:tag' => implode(', ', $this->article->seo_keywords ?? [])
        ];
    }

    /**
     * Obter alertas de segurança críticos
     */
    public function getCriticalSafetyAlerts(): array
    {
        return [
            'Em motocicletas, pneus mal calibrados podem ser fatais.',
            'Verifique SEMPRE antes de viagens longas.',
            'Pressões diferentes entre dianteiro e traseiro são normais.',
            'Nunca calibre pneus quentes - espere esfriar.',
            'Com garupa, aumente a pressão traseira.'
        ];
    }

    /**
     * Verificar se dados estão completos para motocicletas
     */
    public function isDataComplete(): bool
    {
        return !empty($this->article->make) &&
               !empty($this->article->model) &&
               !empty($this->article->year) &&
               !empty($this->article->pressure_empty_front) &&
               !empty($this->article->pressure_empty_rear) &&
               !empty($this->article->article_content) &&
               $this->article->is_motorcycle === true;
    }

    /**
     * Obter recomendações específicas para motocicletas
     */
    public function getMotorcycleSpecificRecommendations(): array
    {
        return [
            'frequency' => 'Verificar semanalmente',
            'before_ride' => 'Sempre verificar antes de viagens longas',
            'cold_tires' => 'Medir apenas com pneus frios',
            'passenger_adjustment' => 'Ajustar pressão traseira com garupa',
            'replacement_time' => 'Trocar ambos os pneus juntos',
            'emergency_check' => 'Verificar após freadas bruscas ou impactos'
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