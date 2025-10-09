<?php

namespace Src\ContentGeneration\ReviewSchedule\Application\DTOs;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GeneratedArticleData
{
    private string $title;
    private string $slug;
    private array $vehicleInfo;
    private array $extractedEntities;
    private array $seoData;
    private array $content;
    private string $template;
    private string $status;
    private string $source;
    private string $domain;
    private array $metadata;
    private array $qualityMetrics;

    // Sistema de controle de variações SEO (reutilizando do ReviewScheduleArticle)
    private static array $usedSeoVariations = [];

    // Configurações de qualidade do conteúdo
    private const QUALITY_THRESHOLDS = [
        'min_introduction_length' => 100,
        'min_conclusion_length' => 80,
        'min_faq_count' => 3,
        'max_faq_count' => 8,
        'min_revision_count' => 4,
        'max_revision_count' => 8,
        'min_critical_parts' => 3,
        'max_critical_parts' => 8
    ];

    // Mapeamento CORRETO de templates por tipo de veículo
    private const TEMPLATE_MAPPING = [
        'car' => 'review_schedule_car',
        'motorcycle' => 'review_schedule_motorcycle',
        'electric' => 'review_schedule_electric',
        'hybrid' => 'review_schedule_hybrid'
    ];

    // Mapeamento de tipos para português CORRIGIDO
    private const VEHICLE_TYPE_PORTUGUESE = [
        'car' => 'carro',
        'motorcycle' => 'motocicleta',
        'electric' => 'elétrico',
        'hybrid' => 'híbrido'
    ];

    public function __construct(
        string $title,
        array $vehicleInfo,
        array $content,
        string $status = 'draft'
    ) {
        $this->title = $title;
        $this->vehicleInfo = $vehicleInfo;
        $this->content = $content;
        $this->status = $status;
        $this->source = 'intelligent_generator';
        $this->domain = 'review_schedule';

        // CORREÇÃO: Detectar tipo correto antes de gerar template
        $this->vehicleInfo = $this->enrichVehicleInfo($vehicleInfo);

        // Detectar template baseado no tipo CORRIGIDO de veículo
        $this->template = $this->detectTemplate($this->vehicleInfo);

        // Gerar componentes com sistema inteligente
        $this->slug = $this->generateIntelligentSlug($this->vehicleInfo);
        $this->extractedEntities = $this->extractEnhancedEntities($this->vehicleInfo);
        $this->seoData = $this->generateVariedSeoData($this->vehicleInfo);
        $this->metadata = $this->generateMetadata($this->vehicleInfo, $content);
        $this->qualityMetrics = $this->calculateQualityMetrics($content);

        // Log da criação para auditoria
        $this->logCreation();
    }

    /**
     * NOVO MÉTODO: Enriquecer dados do veículo com detecção correta de tipo
     */
    private function enrichVehicleInfo(array $vehicleInfo): array
    {
        // Detectar tipo correto usando a categoria
        $detectedType = $this->detectVehicleTypeFromCategory($vehicleInfo);

        // Sobrescrever o vehicle_type com o tipo detectado corretamente
        $vehicleInfo['vehicle_type'] = $detectedType;

        Log::info("Tipo detectado para veículo", [
            'make' => $vehicleInfo['make'] ?? '',
            'model' => $vehicleInfo['model'] ?? '',
            'category' => $vehicleInfo['category'] ?? '',
            'detected_type' => $detectedType,
            'template_will_be' => self::TEMPLATE_MAPPING[$detectedType] ?? 'review_schedule_car'
        ]);

        return $vehicleInfo;
    }

    /**
     * NOVO MÉTODO: Detectar tipo de veículo baseado na categoria
     */
    private function detectVehicleTypeFromCategory(array $vehicleInfo): string
    {
        $category = strtolower(trim($vehicleInfo['category'] ?? ''));

        // Mapping direto das categorias para tipos
        $categoryMapping = [
            // Automóveis convencionais
            'hatch' => 'car',
            'sedan' => 'car',
            'suv' => 'car',
            'pickup' => 'car',
            'van' => 'car',
            'minivan' => 'car',
            'coupe' => 'car',
            'wagon' => 'car',
            'convertible' => 'car',
            'car_sedan' => 'car',
            'car_hatchback' => 'car',
            'car_suv' => 'car',
            'car_pickup' => 'car',
            'car_sports' => 'car',

            // Híbridos
            'car_hybrid' => 'hybrid',
            'hybrid' => 'hybrid',
            'suv_hybrid' => 'hybrid',
            'suv_hibrido' => 'hybrid',

            // Elétricos
            'car_electric' => 'electric',
            'electric' => 'electric',
            'suv_electric' => 'electric',
            'hatch_electric' => 'electric',
            'sedan_electric' => 'electric',

            // Motocicletas convencionais
            'motorcycle_street' => 'motorcycle',
            'motorcycle_sport' => 'motorcycle',
            'motorcycle_trail' => 'motorcycle',
            'motorcycle_adventure' => 'motorcycle',
            'motorcycle_scooter' => 'motorcycle',
            'motorcycle_cruiser' => 'motorcycle',
            'motorcycle_touring' => 'motorcycle',
            'motorcycle_custom' => 'motorcycle',
            'motorcycle_naked' => 'motorcycle',
            'motorcycle_offroad' => 'motorcycle',

            // Motocicletas elétricas
            'motorcycle_electric' => 'electric',
            'moto_eletrica' => 'electric'
        ];

        // Verificação direta no mapeamento
        if (isset($categoryMapping[$category])) {
            return $categoryMapping[$category];
        }

        // Verificações por palavras-chave na categoria
        if (strpos($category, 'electric') !== false || strpos($category, 'eletric') !== false) {
            return 'electric';
        }

        if (strpos($category, 'hybrid') !== false || strpos($category, 'hibrido') !== false) {
            return 'hybrid';
        }

        if (strpos($category, 'motorcycle') !== false || strpos($category, 'moto') !== false) {
            return 'motorcycle';
        }

        // Verificação adicional por óleo recomendado para motos
        $recommendedOil = strtolower($vehicleInfo['recommended_oil'] ?? '');
        if (strpos($recommendedOil, '10w40') !== false || strpos($recommendedOil, '20w50') !== false) {
            return 'motorcycle';
        }

        // Verificação por padrão de pneu (motocicletas têm padrão específico)
        $tireSize = $vehicleInfo['tire_size'] ?? '';
        if (strpos($tireSize, 'dianteiro') !== false && strpos($tireSize, 'traseiro') !== false) {
            return 'motorcycle';
        }

        // Default para carro
        return 'car';
    }

    private function detectTemplate(array $vehicleInfo): string
    {
        $vehicleType = $vehicleInfo['vehicle_type'] ?? 'car';
        $template = self::TEMPLATE_MAPPING[$vehicleType] ?? self::TEMPLATE_MAPPING['car'];

        Log::info("Template detectado", [
            'vehicle_type' => $vehicleType,
            'template' => $template,
            'make_model' => ($vehicleInfo['make'] ?? '') . ' ' . ($vehicleInfo['model'] ?? '')
        ]);

        return $template;
    }

    private function generateIntelligentSlug(array $vehicleInfo): string
    {
        $make = $vehicleInfo['make'] ?? '';
        $model = $vehicleInfo['model'] ?? '';
        $year = $vehicleInfo['year'] ?? '';

        // Slug base
        $baseSlug = "{$make}-{$model}-{$year}";
        // Limpar e gerar slug final
        return Str::slug($baseSlug);
    }

    private function extractEnhancedEntities(array $vehicleInfo): array
    {
        $entities = [
            // Entidades básicas - CORRIGIDAS
            'marca' => $vehicleInfo['make'] ?? '',
            'modelo' => $vehicleInfo['model'] ?? '',
            'ano' => (string)($vehicleInfo['year'] ?? ''),
            'tipo_veiculo' => $this->getVehicleTypeInPortuguese($vehicleInfo['vehicle_type'] ?? 'car'),
            'categoria' => $vehicleInfo['subcategory'] ?? $vehicleInfo['category'] ?? '',

            // Entidades enriquecidas
            'motorizacao' => $vehicleInfo['extracted_engine'] ?? $vehicleInfo['engine'] ?? '',
            'combustivel' => $this->determineFuelType($vehicleInfo),
            'versao' => $vehicleInfo['extracted_version'] ?? $vehicleInfo['version'] ?? '',
            'segmento' => $vehicleInfo['segment'] ?? 'intermediario',
            'perfil_uso' => $vehicleInfo['usage_profile'] ?? 'geral',

            // Entidades específicas
            'pressao_pneu_dianteiro' => $vehicleInfo['pressure_empty_front'] ?? null,
            'pressao_pneu_traseiro' => $vehicleInfo['pressure_empty_rear'] ?? null,
            'oleo_recomendado' => $vehicleInfo['recommended_oil'] ?? '',
            'confianca_deteccao' => 'high' // Agora sempre high porque corrigimos a detecção
        ];

        // Remover entidades vazias
        return array_filter($entities, fn($value) => $value !== '' && $value !== null);
    }

    /**
     * MÉTODO CORRIGIDO: Converter tipo de veículo para português
     */
    private function getVehicleTypeInPortuguese(string $vehicleType): string
    {
        return self::VEHICLE_TYPE_PORTUGUESE[$vehicleType] ?? 'carro';
    }

    /**
     * NOVO MÉTODO: Determinar tipo de combustível baseado no tipo de veículo
     */
    private function determineFuelType(array $vehicleInfo): string
    {
        $vehicleType = $vehicleInfo['vehicle_type'] ?? 'car';

        // Para elétricos, sempre "elétrico"
        if ($vehicleType === 'electric') {
            return 'elétrico';
        }

        // Para híbridos, sempre "híbrido"
        if ($vehicleType === 'hybrid') {
            return 'híbrido';
        }

        // Para motocicletas, geralmente gasolina
        if ($vehicleType === 'motorcycle') {
            return 'gasolina';
        }

        // Para carros convencionais, usar o valor original ou flex como padrão
        return $vehicleInfo['extracted_fuel_type'] ?? $vehicleInfo['fuel_type'] ?? 'flex';
    }

    private function generateVariedSeoData(array $vehicleInfo): array
    {
        $make = $vehicleInfo['make'] ?? '';
        $model = $vehicleInfo['model'] ?? '';
        $year = $vehicleInfo['year'] ?? '';
        $vehicleType = $vehicleInfo['vehicle_type'] ?? 'car';

        // Variações de título baseadas no tipo
        $titleVariations = [
            'car' => [
                "Cronograma de Revisões do {$make} {$model} {$year}",
                "Manual de Revisões {$make} {$model} {$year} - Manutenção Programada",
                "Guia de Manutenção do {$make} {$model} {$year}"
            ],
            'motorcycle' => [
                "Cronograma de Revisões da {$make} {$model} {$year}",
                "Manual de Manutenção {$make} {$model} {$year}",
                "Guia de Revisões da Moto {$make} {$model} {$year}"
            ],
            'electric' => [
                "Manual de Revisões {$make} {$model} {$year} - Manutenção Programada",
                "Cronograma de Manutenção do {$make} {$model} {$year} Elétrico",
                "Guia de Cuidados do {$make} {$model} {$year} EV"
            ],
            'hybrid' => [
                "Cronograma de Revisões do {$make} {$model} {$year} Híbrido",
                "Manual de Manutenção {$make} {$model} {$year} HEV",
                "Guia de Revisões do {$make} {$model} {$year} Hybrid"
            ]
        ];

        $variations = $titleVariations[$vehicleType] ?? $titleVariations['car'];
        $selectedTitle = $variations[array_rand($variations)];

        return [
            'page_title' => $selectedTitle,
            'meta_description' => $this->generateMetaDescription($vehicleInfo),
            'url_slug' => "revisao-{$this->slug}",
            'h1' => "Cronograma de Revisões do {$make} {$model} {$year}",
            'h2_tags' => $this->generateH2Tags($vehicleType),
            'primary_keyword' => "cronograma revisões {$make} {$model} {$year}",
            'secondary_keywords' => $this->generateSecondaryKeywords($vehicleInfo),
            'meta_robots' => 'index,follow',
            'canonical_url' => $this->slug,
            'schema_type' => 'Article',
            'article_section' => 'Automotive',
            'target_audience' => 'proprietários de veículos'
        ];
    }

    private function generateMetaDescription(array $vehicleInfo): string
    {
        $make = $vehicleInfo['make'] ?? '';
        $model = $vehicleInfo['model'] ?? '';
        $year = $vehicleInfo['year'] ?? '';
        $vehicleType = $vehicleInfo['vehicle_type'] ?? 'car';

        $descriptions = [
            'car' => "Cronograma oficial de revisões {$make} {$model} {$year} com intervalos, procedimentos e estimativas de custo para cada manutenção.",
            'motorcycle' => "Cronograma completo de revisões da {$make} {$model} {$year} com intervalos, custos e procedimentos de manutenção.",
            'electric' => "Tudo sobre manutenção do {$make} {$model} {$year}: cronograma de revisões, custos estimados e cuidados preventivos.",
            'hybrid' => "Guia completo de manutenção do {$make} {$model} {$year} híbrido: cronograma, custos e cuidados especiais."
        ];

        return $descriptions[$vehicleType] ?? $descriptions['car'];
    }

    private function generateH2Tags(string $vehicleType): array
    {
        $baseTags = [
            "Cronograma de Revisões Programadas",
            "Revisões Detalhadas por Intervalo"
        ];

        $specificTags = [
            'car' => [
                "Manutenção Preventiva Entre Revisões",
                "Componentes Críticos",
                "Garantia e Recomendações Adicionais"
            ],
            'motorcycle' => [
                "Manutenção Preventiva Recomendada",
                "Componentes Críticos",
                "Dicas de Conservação"
            ],
            'electric' => [
                "Manutenção Preventiva Recomendada",
                "Componentes Críticos",
                "Informações de Garantia"
            ],
            'hybrid' => [
                "Manutenção Preventiva Especial",
                "Sistemas Críticos",
                "Garantia e Cuidados Especiais"
            ]
        ];

        return array_merge($baseTags, $specificTags[$vehicleType] ?? $specificTags['car']);
    }

    private function generateSecondaryKeywords(array $vehicleInfo): array
    {
        $make = strtolower($vehicleInfo['make'] ?? '');
        $model = strtolower($vehicleInfo['model'] ?? '');
        $vehicleType = $vehicleInfo['vehicle_type'] ?? 'car';

        $baseKeywords = [
            "revisão {$make} {$model}",
            "manutenção {$make} {$model}",
            "cronograma manutenção {$make}"
        ];

        $typeSpecificKeywords = [
            'motorcycle' => ["intervalos revisão {$model}", "manutenção moto {$make}"],
            'electric' => ["manutenção elétrico {$make}", "cuidados {$model} ev"],
            'hybrid' => ["manutenção híbrido {$make}", "revisão {$model} hybrid"],
            'car' => ["intervalos revisão {$model}"]
        ];

        return array_merge(
            $baseKeywords,
            $typeSpecificKeywords[$vehicleType] ?? $typeSpecificKeywords['car']
        );
    }

    private function generateMetadata(array $vehicleInfo, array $content): array
    {
        return [
            'content_metrics' => [
                'word_count' => $this->calculateWordCount($content),
                'section_count' => count($content),
                'revision_count' => count($content['detailed_schedule'] ?? [])
            ],
            'vehicle_characteristics' => [
                'type' => $vehicleInfo['vehicle_type'] ?? 'car',
                'segment' => $vehicleInfo['segment'] ?? 'unknown',
                'fuel_type' => $vehicleInfo['extracted_fuel_type'] ?? 'unknown',
                'detection_confidence' => 'high', // Sempre high agora
                'is_premium' => ($vehicleInfo['segment'] ?? '') === 'premium'
            ],
            'generation_info' => [
                'template_used' => $this->template,
                'generation_timestamp' => now()->toISOString(),
                'source_system' => $this->source,
                'content_version' => '2.0'
            ],
            'seo_indicators' => [
                'target_keywords_count' => count($this->seoData['secondary_keywords'] ?? []),
                'h2_count' => count($this->seoData['h2_tags'] ?? []),
                'has_schema' => isset($this->seoData['schema_type']),
                'is_indexable' => ($this->seoData['meta_robots'] ?? '') !== 'noindex'
            ]
        ];
    }

    private function calculateWordCount(array $content): int
    {
        $totalWords = 0;

        foreach ($content as $section) {
            if (is_string($section)) {
                $totalWords += str_word_count(strip_tags($section));
            } elseif (is_array($section)) {
                $totalWords += $this->countWordsInArray($section);
            }
        }

        return $totalWords;
    }

    private function countWordsInArray(array $data): int
    {
        $wordCount = 0;

        foreach ($data as $item) {
            if (is_string($item)) {
                $wordCount += str_word_count(strip_tags($item));
            } elseif (is_array($item)) {
                $wordCount += $this->countWordsInArray($item);
            }
        }

        return $wordCount;
    }

    private function calculateQualityMetrics(array $content): array
    {
        $metrics = [
            'content_completeness' => 0,
            'content_depth' => 0,
            'structural_quality' => 0,
            'overall_score' => 0,
            'issues' => []
        ];

        // Verificar completude do conteúdo
        $completenessScore = $this->calculateCompletenessScore($content);
        $metrics['content_completeness'] = $completenessScore['score'];
        $metrics['issues'] = array_merge($metrics['issues'], $completenessScore['issues']);

        // Verificar profundidade do conteúdo
        $depthScore = $this->calculateDepthScore($content);
        $metrics['content_depth'] = $depthScore['score'];
        $metrics['issues'] = array_merge($metrics['issues'], $depthScore['issues']);

        // Verificar qualidade estrutural
        $structuralScore = $this->calculateStructuralScore($content);
        $metrics['structural_quality'] = $structuralScore['score'];
        $metrics['issues'] = array_merge($metrics['issues'], $structuralScore['issues']);

        // Calcular score geral
        $metrics['overall_score'] = round(
            ($metrics['content_completeness'] + $metrics['content_depth'] + $metrics['structural_quality']) / 3
        );

        return $metrics;
    }

    private function calculateCompletenessScore(array $content): array
    {
        $score = 0;
        $issues = [];
        $requiredSections = ['introduction', 'detailed_schedule', 'faqs', 'conclusion'];

        foreach ($requiredSections as $section) {
            if (isset($content[$section]) && !empty($content[$section])) {
                $score += 25;
            } else {
                $issues[] = "Missing required section: {$section}";
            }
        }

        return ['score' => $score, 'issues' => $issues];
    }

    private function calculateDepthScore(array $content): array
    {
        $score = 0;
        $issues = [];

        // Verificar tamanho da introdução
        $introLength = strlen($content['introduction'] ?? '');
        if ($introLength >= self::QUALITY_THRESHOLDS['min_introduction_length']) {
            $score += 20;
        } else {
            $issues[] = "Introduction too short ({$introLength} chars, minimum " . self::QUALITY_THRESHOLDS['min_introduction_length'] . ")";
        }

        // Verificar tamanho da conclusão
        $conclusionLength = strlen($content['conclusion'] ?? '');
        if ($conclusionLength >= self::QUALITY_THRESHOLDS['min_conclusion_length']) {
            $score += 20;
        } else {
            $issues[] = "Conclusion too short ({$conclusionLength} chars, minimum " . self::QUALITY_THRESHOLDS['min_conclusion_length'] . ")";
        }

        // Verificar número de revisões
        $revisionCount = count($content['detailed_schedule'] ?? []);
        if ($revisionCount >= self::QUALITY_THRESHOLDS['min_revision_count']) {
            $score += 30;
        } else {
            $issues[] = "Too few revisions ({$revisionCount}, minimum " . self::QUALITY_THRESHOLDS['min_revision_count'] . ")";
        }

        // Verificar FAQs
        $faqCount = count($content['faqs'] ?? []);
        if ($faqCount >= self::QUALITY_THRESHOLDS['min_faq_count']) {
            $score += 30;
        } else {
            $issues[] = "Too few FAQs ({$faqCount}, minimum " . self::QUALITY_THRESHOLDS['min_faq_count'] . ")";
        }

        return ['score' => $score, 'issues' => $issues];
    }

    private function calculateStructuralScore(array $content): array
    {
        $score = 0;
        $issues = [];

        // Verificar estrutura das revisões detalhadas
        if (isset($content['detailed_schedule']) && is_array($content['detailed_schedule'])) {
            $validRevisions = 0;
            foreach ($content['detailed_schedule'] as $revision) {
                if (isset($revision['interval']) && isset($revision['services'])) {
                    $validRevisions++;
                }
            }

            if ($validRevisions >= 4) {
                $score += 40;
            } else {
                $issues[] = "Invalid revision structure ({$validRevisions} valid revisions)";
            }
        } else {
            $issues[] = "Missing or invalid detailed_schedule structure";
        }

        // Verificar estrutura dos FAQs
        if (isset($content['faqs']) && is_array($content['faqs'])) {
            $validFaqs = 0;
            foreach ($content['faqs'] as $faq) {
                if (isset($faq['question']) && isset($faq['answer'])) {
                    $validFaqs++;
                }
            }

            if ($validFaqs >= 3) {
                $score += 30;
            } else {
                $issues[] = "Invalid FAQ structure ({$validFaqs} valid FAQs)";
            }
        } else {
            $issues[] = "Missing or invalid FAQs structure";
        }

        // Verificar seções textuais
        $textSections = ['introduction', 'conclusion'];
        $validTextSections = 0;
        foreach ($textSections as $section) {
            if (isset($content[$section]) && is_string($content[$section]) && strlen($content[$section]) > 50) {
                $validTextSections++;
            }
        }

        if ($validTextSections >= 2) {
            $score += 30;
        } else {
            $issues[] = "Invalid text sections ({$validTextSections} valid sections)";
        }

        return ['score' => $score, 'issues' => $issues];
    }

    private function logCreation(): void
    {
        Log::info("GeneratedArticleData created", [
            'slug' => $this->slug,
            'template' => $this->template,
            'vehicle_type' => $this->vehicleInfo['vehicle_type'] ?? 'unknown',
            'vehicle' => $this->getVehicleIdentifier(),
            'quality_score' => $this->qualityMetrics['overall_score']
        ]);
    }

    private function getVehicleIdentifier(): string
    {
        return sprintf(
            '%s %s %s',
            $this->vehicleInfo['make'] ?? '',
            $this->vehicleInfo['model'] ?? '',
            $this->vehicleInfo['year'] ?? ''
        );
    }

    private function generateSearchTerms(): array
    {
        $terms = [];

        $terms[] = strtolower($this->vehicleInfo['make'] ?? '');
        $terms[] = strtolower($this->vehicleInfo['model'] ?? '');
        $terms[] = (string)($this->vehicleInfo['year'] ?? '');
        $terms[] = strtolower($this->vehicleInfo['vehicle_type'] ?? '');

        return array_filter($terms);
    }

    private function generateVehicleKey(): string
    {
        return sprintf(
            '%s_%s_%s',
            strtolower($this->vehicleInfo['make'] ?? ''),
            strtolower($this->vehicleInfo['model'] ?? ''),
            $this->vehicleInfo['year'] ?? ''
        );
    }

    private function generateContentHash(): string
    {
        return md5(serialize($this->content));
    }

    // Getters públicos
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getSlug(): string
    {
        return $this->slug;
    }
    public function getVehicleInfo(): array
    {
        return $this->vehicleInfo;
    }
    public function getExtractedEntities(): array
    {
        return $this->extractedEntities;
    }
    public function getSeoData(): array
    {
        return $this->seoData;
    }
    public function getContent(): array
    {
        return $this->content;
    }
    public function getTemplate(): string
    {
        return $this->template;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getSource(): string
    {
        return $this->source;
    }
    public function getDomain(): string
    {
        return $this->domain;
    }
    public function getMetadata(): array
    {
        return $this->metadata;
    }
    public function getQualityMetrics(): array
    {
        return $this->qualityMetrics;
    }

    public function publish(): void
    {
        $this->status = 'published';
        Log::info("Article published", [
            'slug' => $this->slug,
            'vehicle' => $this->getVehicleIdentifier(),
            'quality_score' => $this->qualityMetrics['overall_score']
        ]);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => "cronograma-revisoes-" . $this->slug,
            'new_slug' => "revisao-" . $this->slug,
            'vehicle_info' => $this->vehicleInfo,
            'extracted_entities' => $this->extractedEntities,
            'seo_data' => $this->seoData,
            'content' => $this->content,
            'template' => $this->template,
            'status' => $this->status,
            'source' => $this->source,
            'domain' => $this->domain,
            'metadata' => $this->metadata,
            'quality_metrics' => $this->qualityMetrics,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    public function toMongoDocument(): array
    {
        $document = $this->toArray();

        // Adicionar índices específicos para MongoDB
        $document['search_terms'] = $this->generateSearchTerms();
        $document['vehicle_key'] = $this->generateVehicleKey();
        $document['content_hash'] = $this->generateContentHash();

        return $document;
    }

    public function isHighQuality(): bool
    {
        return $this->qualityMetrics['overall_score'] >= 85;
    }

    public function getQualityIssues(): array
    {
        return $this->qualityMetrics['issues'] ?? [];
    }
}
