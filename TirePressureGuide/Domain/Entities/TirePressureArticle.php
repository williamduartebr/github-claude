<?php

namespace Src\ContentGeneration\TirePressureGuide\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * FIXED V3 TirePressureArticle Model - DUAL TEMPLATE SUPPORT
 * 
 * CORREÇÕES APLICADAS:
 * - Corrigido calculateOrphanPriorityScore (método static)
 * - Corrigido generateDualTemplateRecommendations (método static) 
 * - Corrigido analyzeDualTemplateTrends (método static)
 * - Removido código duplicado/incompleto
 * - Validação e estrutura completa
 */
class TirePressureArticle extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'tire_pressure_articles';
    protected $guarded = ['_id'];

    protected $casts = [
        'vehicle_data' => 'array',
        'article_content' => 'array',
        'seo_keywords' => 'array',
        'claude_enhancements' => 'array',
        'quality_issues' => 'array',
        
        // Seções separadas para refinamento Claude
        'sections_intro' => 'array',
        'sections_pressure_table' => 'array',
        'sections_how_to_calibrate' => 'array',
        'sections_middle_content' => 'array',
        'sections_faq' => 'array',
        'sections_conclusion' => 'array',
        'sections_refined' => 'array',
        'sections_scores' => 'array',
        'sections_status' => 'array',
        
        // NOVO V3: Cross-linking e relacionamentos
        'sibling_article_id' => 'string',
        'cross_links' => 'array',
        'related_articles' => 'array',
        
        // NOVO V3: Métricas avançadas
        'performance_metrics' => 'array',
        'quality_metrics' => 'array',
        'validation_results' => 'array',
        
        // NOVO V3: Sistema de backup e versioning
        'content_versions' => 'array',
        'backup_data' => 'array',
        'last_backup_at' => 'datetime',
        
        // Timestamps e controle
        'claude_last_enhanced_at' => 'datetime',
        'sections_last_refined_at' => 'datetime',
        'processed_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'quality_checked' => 'boolean',
        'pressure_light_front' => 'decimal:1',
        'pressure_light_rear' => 'decimal:1',
        'pressure_spare' => 'decimal:1',
        'content_score' => 'decimal:2',
        'blog_published_time' => 'datetime',
        'blog_modified_time' => 'datetime',
        'blog_synced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $fillable = [
        // Dados básicos do veículo
        'make',
        'model',
        'year',
        'tire_size',
        'vehicle_data',

        // TEMPLATE TYPE - Campo principal para dual template
        'template_type', // 'ideal' ou 'calibration'

        // Conteúdo do artigo (estruturado)
        'title',
        'slug',
        'wordpress_slug',
        'article_content',
        'template_used',

        // Seções separadas para refinamento Claude (Etapa 2)
        'sections_intro',
        'sections_pressure_table',
        'sections_how_to_calibrate',
        'sections_middle_content',
        'sections_faq',
        'sections_conclusion',
        
        // Controle de refinamento por seção
        'sections_refined',
        'sections_scores',
        'sections_status',
        'sections_last_refined_at',

        // NOVO V3: Cross-linking e relacionamentos
        'sibling_article_id',
        'cross_links',
        'related_articles',

        // NOVO V3: Métricas e validação
        'performance_metrics',
        'quality_metrics',
        'validation_results',
        'last_validated_at',

        // NOVO V3: Sistema de backup
        'content_versions',
        'backup_data',
        'last_backup_at',

        // SEO e URLs
        'meta_description',
        'seo_keywords',
        'wordpress_url',
        'amp_url',
        'canonical_url',

        // Status de geração (Etapas 1 e 2)
        'generation_status',

        // Claude API Enhancement (Etapa 2)
        'claude_enhancements',
        'claude_last_enhanced_at',
        'claude_enhancement_count',

        // Dados técnicos de pressão dos pneus
        'pressure_empty_front',
        'pressure_empty_rear',
        'pressure_light_front',
        'pressure_light_rear',
        'pressure_max_front',
        'pressure_max_rear',
        'pressure_spare',

        // Classificação e categoria
        'category',

        // Controle de qualidade
        'quality_checked',
        'quality_issues',
        'content_score',

        // Controle de lotes e processamento
        'batch_id',
        'processed_at',

        // Integração com blog WordPress
        'blog_id',
        'blog_status',
        'blog_published_time',
        'blog_modified_time',
        'blog_synced',

        // Timestamps padrão
        'created_at',
        'updated_at'
    ];

    // =======================================================================
    // BOOT E EVENTOS DO MODEL
    // =======================================================================

    protected static function boot()
    {
        parent::boot();

        // Auto-gerar campos ao criar
        static::creating(function ($article) {
            $article->ensureRequiredFields();
            $article->autoGenerateSlugAndTitle();
            $article->initializeQualityMetrics();
        });

        // Auto-atualizar campos ao salvar
        static::saving(function ($article) {
            $article->validateTemplateType();
            $article->updateQualityMetrics();
        });

        // Manter backup ao atualizar conteúdo importante
        static::updating(function ($article) {
            if ($article->isDirty(['article_content', 'sections_refined'])) {
                $article->createContentBackup();
            }
        });

        // Limpar cache ao deletar
        static::deleting(function ($article) {
            $article->clearRelatedCache();
        });
    }

    /**
     * Definir índices otimizados para MongoDB incluindo template_type
     */
    public static function createIndexes(): void
    {
        $collection = (new static)->getCollection();
        
        // Índices básicos
        $collection->createIndex(['make' => 1]);
        $collection->createIndex(['model' => 1]); 
        $collection->createIndex(['year' => 1]);
        $collection->createIndex(['generation_status' => 1]);
        $collection->createIndex(['batch_id' => 1]);
        
        // PRINCIPAL: Índice para template_type
        $collection->createIndex(['template_type' => 1]);
        
        // Índice único para slug WordPress INCLUINDO template_type
        $collection->createIndex(
            ['wordpress_slug' => 1, 'template_type' => 1], 
            ['unique' => true, 'name' => 'unique_wordpress_slug_template_tire_pressure_v3']
        );
        
        // Índices compostos otimizados para dual template
        $collection->createIndex(
            ['make' => 1, 'model' => 1, 'year' => 1, 'template_type' => 1], 
            ['name' => 'vehicle_template_composite_v3']
        );
        $collection->createIndex(
            ['generation_status' => 1, 'template_type' => 1, 'created_at' => 1], 
            ['name' => 'status_template_created_v3']
        );
        $collection->createIndex(
            ['template_type' => 1, 'quality_checked' => 1, 'content_score' => 1], 
            ['name' => 'quality_template_score_v3']
        );
        
        // NOVO V3: Índices para cross-linking
        $collection->createIndex(['sibling_article_id' => 1]);
        $collection->createIndex(['cross_links.article_id' => 1]);
        
        // NOVO V3: Índices para performance
        $collection->createIndex(['last_validated_at' => 1]);
        $collection->createIndex(['template_type' => 1, 'blog_synced' => 1]);
        
        // Índices para dados aninhados
        $collection->createIndex(['vehicle_data.vehicle_type' => 1]);
        $collection->createIndex(['vehicle_data.main_category' => 1]);
        $collection->createIndex(['vehicle_data.is_motorcycle' => 1]);
        
        // NOVO V3: Índices para métricas
        $collection->createIndex(['quality_metrics.overall_score' => 1]);
        $collection->createIndex(['performance_metrics.generation_time_ms' => 1]);
    }

    // =======================================================================
    // MÉTODOS PRINCIPAIS PARA DUAL TEMPLATE SUPPORT
    // =======================================================================

    /**
     * Gerar slug compatível com WordPress baseado no template_type
     */
    public function generateWordPressSlug(): string
    {
        $make = $this->slugify($this->make);
        $model = $this->slugify($this->model);
        $year = $this->year;
        $templateType = $this->template_type ?? 'ideal';
        
        $slug = match($templateType) {
            'ideal' => "pressao-ideal-pneu-{$make}-{$model}-{$year}",
            'calibration' => "calibragem-pneu-{$make}-{$model}-{$year}",
            default => "pressao-ideal-pneu-{$make}-{$model}-{$year}"
        };

        return $this->ensureSlugUniqueness($slug);
    }

    /**
     * Gerar título baseado no template_type
     */
    public function generateTitleByTemplate(): string
    {
        $make = $this->make;
        $model = $this->model;
        $year = $this->year;
        $templateType = $this->template_type ?? 'ideal';
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        
        $vehicleType = $isMotorcycle ? 'Moto' : '';
        
        return match($templateType) {
            'ideal' => "Pressão Ideal dos Pneus{$vehicleType} - {$make} {$model} {$year}",
            'calibration' => "Calibragem dos Pneus{$vehicleType} - {$make} {$model} {$year} | Guia Completo",
            default => "Pneus {$make} {$model} {$year}"
        };
    }

    /**
     * Gerar meta description otimizada baseada no template_type
     */
    public function generateMetaDescriptionByTemplate(): string
    {
        $make = $this->make;
        $model = $this->model;
        $year = $this->year;
        $frontPressure = $this->pressure_light_front ?? $this->pressure_empty_front ?? 30;
        $rearPressure = $this->pressure_light_rear ?? $this->pressure_empty_rear ?? 28;
        $templateType = $this->template_type ?? 'ideal';
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        
        $vehicleType = $isMotorcycle ? 'moto' : 'carro';
        
        return match($templateType) {
            'ideal' => "✓ Pressão ideal dos pneus do {$make} {$model} {$year}: {$frontPressure}/{$rearPressure} PSI. Tabela completa, especificações técnicas e dicas para máxima economia de combustível no seu {$vehicleType}.",
            'calibration' => "✓ Como calibrar pneus do {$make} {$model} {$year}: guia passo a passo, equipamentos recomendados, sistema TPMS e troubleshooting. Pressões corretas: {$frontPressure}/{$rearPressure} PSI para seu {$vehicleType}.",
            default => "Informações completas sobre pneus do {$make} {$model} {$year}."
        };
    }

    /**
     * Obter URL completa do WordPress
     */
    public function getWordPressUrl(): string
    {
        if ($this->wordpress_url) {
            return $this->wordpress_url;
        }
        
        $slug = $this->generateWordPressSlug();
        $baseUrl = config('tire_pressure.wordpress_base_url', 'https://mercadoveiculos.com');
        return "{$baseUrl}/info/{$slug}/";
    }

    /**
     * Obter template name baseado no tipo de veículo e template_type
     */
    public function getTemplateNameByType(): string
    {
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        $templateType = $this->template_type ?? 'ideal';
        
        return match([$templateType, $isMotorcycle]) {
            ['ideal', true] => 'ideal_tire_pressure_motorcycle',
            ['ideal', false] => 'ideal_tire_pressure_car',
            ['calibration', true] => 'tire_pressure_guide_motorcycle',
            ['calibration', false] => 'tire_pressure_guide_car',
            default => 'ideal_tire_pressure_car'
        };
    }

    // =======================================================================
    // SCOPES AVANÇADOS PARA DUAL TEMPLATE V3
    // =======================================================================

    /**
     * Scope: Filtrar por template type
     */
    public function scopeByTemplateType($query, string $templateType)
    {
        $validTypes = ['ideal', 'calibration'];
        if (!in_array($templateType, $validTypes)) {
            throw new \InvalidArgumentException("Template type inválido: {$templateType}");
        }
        
        return $query->where('template_type', $templateType);
    }

    /**
     * Scope: Artigos do template IDEAL
     */
    public function scopeIdealTemplate($query, array $filters = [])
    {
        $query = $query->where('template_type', 'ideal');
        return $this->applyAdvancedFilters($query, $filters);
    }

    /**
     * Scope: Artigos do template CALIBRATION
     */
    public function scopeCalibrationTemplate($query, array $filters = [])
    {
        $query = $query->where('template_type', 'calibration');
        return $this->applyAdvancedFilters($query, $filters);
    }

    /**
     * Scope: Artigos prontos para refinamento Claude
     */
    public function scopeReadyForClaudeByTemplate($query, string $templateType = null, array $options = [])
    {
        $query = $query->where('generation_status', 'generated')
                      ->where(function($q) {
                          $q->whereNull('claude_enhancement_count')
                            ->orWhere('claude_enhancement_count', '<', 3);
                      });
        
        if ($templateType) {
            $query->where('template_type', $templateType);
        }
        
        // Filtros adicionais
        if (isset($options['min_content_score'])) {
            $query->where('content_score', '>=', $options['min_content_score']);
        }
        
        if (isset($options['exclude_low_quality'])) {
            $query->where('quality_checked', true)
                  ->where(function($q) {
                      $q->whereNull('quality_issues')
                        ->orWhereRaw('JSON_LENGTH(quality_issues) = 0');
                  });
        }
        
        return $query;
    }

    /**
     * Scope: Artigos órfãos (sem template irmão)
     */
    public function scopeOrphanedTemplates($query, string $missingTemplate = null)
    {
        if ($missingTemplate) {
            $existingTemplate = $missingTemplate === 'ideal' ? 'calibration' : 'ideal';
            return $query->where('template_type', $existingTemplate)
                         ->whereNull('sibling_article_id');
        }
        
        return $query->whereNull('sibling_article_id');
    }

    /**
     * Scope: Artigos com cross-links
     */
    public function scopeWithCrossLinks($query)
    {
        return $query->whereNotNull('sibling_article_id')
                     ->orWhereNotNull('cross_links');
    }

    /**
     * Scope: Artigos de alta qualidade
     */
    public function scopeHighQuality($query, float $minScore = 8.0)
    {
        return $query->where('content_score', '>=', $minScore)
                     ->where('quality_checked', true)
                     ->where(function($q) {
                         $q->whereNull('quality_issues')
                           ->orWhereRaw('JSON_LENGTH(quality_issues) = 0');
                     });
    }

    // =======================================================================
    // ACCESSORS E MUTATORS
    // =======================================================================

    /**
     * Accessor: Nome completo do veículo incluindo template
     */
    public function getVehicleFullNameWithTemplateAttribute(): string
    {
        $baseName = "{$this->make} {$this->model} {$this->year}";
        $templateType = $this->template_type ?? 'ideal';
        
        $templateNames = [
            'ideal' => 'Pressão Ideal',
            'calibration' => 'Calibragem'
        ];
        
        $templateName = $templateNames[$templateType] ?? $templateType;
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        $vehicleType = $isMotorcycle ? ' (Moto)' : '';
        
        return "{$baseName}{$vehicleType} - {$templateName}";
    }

    /**
     * Accessor: Status detalhado do template
     */
    public function getTemplateStatusDetailedAttribute(): array
    {
        $templateType = $this->template_type ?? 'ideal';
        $status = $this->generation_status ?? 'pending';
        
        $statusMap = [
            'pending' => ['text' => 'Aguardando geração', 'color' => 'gray'],
            'generated' => ['text' => 'Gerado', 'color' => 'blue'],
            'claude_enhanced' => ['text' => 'Refinado', 'color' => 'green'],
            'published' => ['text' => 'Publicado', 'color' => 'purple']
        ];
        
        $statusInfo = $statusMap[$status] ?? ['text' => $status, 'color' => 'gray'];
        
        return [
            'template_type' => $templateType,
            'status' => $status,
            'status_text' => $statusInfo['text'],
            'status_color' => $statusInfo['color'],
            'content_score' => $this->content_score ?? 0,
            'quality_checked' => $this->quality_checked ?? false,
            'has_sibling' => !is_null($this->sibling_article_id),
            'cross_links_count' => count($this->cross_links ?? [])
        ];
    }

    /**
     * Mutator: Auto-gerar slug WordPress
     */
    public function setWordpressSlugAttribute($value)
    {
        if (!$value && $this->make && $this->model && $this->year && $this->template_type) {
            $this->attributes['wordpress_slug'] = $this->generateWordPressSlug();
        } else {
            $this->attributes['wordpress_slug'] = $value;
        }
    }

    /**
     * Mutator: Auto-gerar título
     */
    public function setTitleAttribute($value)
    {
        if (!$value && $this->make && $this->model && $this->year && $this->template_type) {
            $this->attributes['title'] = $this->generateTitleByTemplate();
        } else {
            $this->attributes['title'] = $value;
        }
    }

    /**
     * Mutator: Template type com validação
     */
    public function setTemplateTypeAttribute($value)
    {
        $validTypes = ['ideal', 'calibration'];
        
        if ($value && !in_array($value, $validTypes)) {
            throw new \InvalidArgumentException("Template type inválido: {$value}. Válidos: " . implode(', ', $validTypes));
        }
        
        $this->attributes['template_type'] = $value;
        
        // Auto-atualizar campos dependentes
        if ($value && $this->make && $this->model && $this->year) {
            $this->attributes['slug'] = $this->generateWordPressSlug();
            $this->attributes['title'] = $this->generateTitleByTemplate();
            $this->attributes['template_used'] = $this->getTemplateNameByType();
        }
    }

    // =======================================================================
    // SISTEMA DE CROSS-LINKING
    // =======================================================================

    /**
     * Encontrar e conectar artigo irmão automaticamente
     */
    public function findAndConnectSiblingArticle(): ?TirePressureArticle
    {
        if ($this->sibling_article_id) {
            return static::find($this->sibling_article_id);
        }

        $currentTemplate = $this->template_type;
        $targetTemplate = $currentTemplate === 'ideal' ? 'calibration' : 'ideal';
        
        $sibling = static::where('make', $this->make)
                        ->where('model', $this->model)
                        ->where('year', $this->year)
                        ->where('template_type', $targetTemplate)
                        ->first();

        if ($sibling) {
            $this->connectSiblingArticle($sibling);
        }

        return $sibling;
    }

    /**
     * Conectar artigos irmãos bidirecionalmente
     */
    public function connectSiblingArticle(TirePressureArticle $sibling): void
    {
        if ($this->template_type === $sibling->template_type) {
            throw new \InvalidArgumentException("Não é possível conectar artigos do mesmo template");
        }

        // Conectar bidirecionalmente
        $this->sibling_article_id = $sibling->_id;
        $sibling->sibling_article_id = $this->_id;

        // Adicionar cross-links
        $this->addCrossLink($sibling, 'sibling');
        $sibling->addCrossLink($this, 'sibling');

        $this->save();
        $sibling->save();

        Log::info("Artigos irmãos conectados", [
            'article_1' => $this->_id,
            'template_1' => $this->template_type,
            'article_2' => $sibling->_id,
            'template_2' => $sibling->template_type,
            'vehicle' => "{$this->make} {$this->model} {$this->year}"
        ]);
    }

    /**
     * Adicionar cross-link
     */
    public function addCrossLink(TirePressureArticle $targetArticle, string $linkType = 'related'): void
    {
        $crossLinks = $this->cross_links ?? [];
        
        $linkData = [
            'article_id' => $targetArticle->_id,
            'link_type' => $linkType,
            'template_type' => $targetArticle->template_type,
            'title' => $targetArticle->title,
            'url' => $targetArticle->getWordPressUrl(),
            'created_at' => now()->toISOString()
        ];

        // Verificar se link já existe
        $exists = collect($crossLinks)->contains(function($link) use ($targetArticle, $linkType) {
            return $link['article_id'] === $targetArticle->_id && $link['link_type'] === $linkType;
        });

        if (!$exists) {
            $crossLinks[] = $linkData;
            $this->cross_links = $crossLinks;
        }
    }

    /**
     * Obter URL do artigo irmão
     */
    public function getSiblingArticleUrl(): ?string
    {
        if (!$this->sibling_article_id) {
            return null;
        }

        $sibling = Cache::remember(
            "sibling_url_{$this->_id}",
            60 * 60, // 1 hora
            fn() => static::find($this->sibling_article_id)
        );

        return $sibling ? $sibling->getWordPressUrl() : null;
    }

    // =======================================================================
    // MÉTRICAS DE QUALIDADE
    // =======================================================================

    /**
     * Calcular métricas de qualidade completas
     */
    public function calculateQualityMetrics(): array
    {
        $metrics = [
            'content_completeness' => $this->calculateContentCompleteness(),
            'structure_integrity' => $this->calculateStructureIntegrity(),
            'seo_optimization' => $this->calculateSeoOptimization(),
            'template_compliance' => $this->calculateTemplateCompliance(),
            'cross_linking_score' => $this->calculateLinkingScore(),
            'overall_score' => 0,
            'calculated_at' => now()->toISOString()
        ];

        // Score geral (média ponderada)
        $weights = [
            'content_completeness' => 0.3,
            'structure_integrity' => 0.25,
            'seo_optimization' => 0.2,
            'template_compliance' => 0.15,
            'cross_linking_score' => 0.1
        ];

        $weightedSum = 0;
        foreach ($weights as $metric => $weight) {
            $weightedSum += ($metrics[$metric] ?? 0) * $weight;
        }

        $metrics['overall_score'] = round($weightedSum, 2);
        
        // Salvar métricas
        $this->quality_metrics = $metrics;
        $this->last_validated_at = now();
        
        return $metrics;
    }

    /**
     * Calcular completude do conteúdo
     */
    protected function calculateContentCompleteness(): float
    {
        $templateType = $this->template_type ?? 'ideal';
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        $expectedSections = $this->getExpectedSectionsByTemplate($templateType, $isMotorcycle);
        $content = $this->article_content ?? [];
        
        $presentSections = 0;
        $totalWeight = 0;
        $weightedPresent = 0;

        // Pesos diferentes para seções críticas
        $sectionWeights = [
            'introducao' => 1.2,
            'tabela_pressoes' => 1.5,
            'pressure_table' => 1.5,
            'calibration_procedure' => 1.3,
            'perguntas_frequentes' => 1.1,
            'consideracoes_finais' => 1.0
        ];

        foreach ($expectedSections as $section => $type) {
            $weight = $sectionWeights[$section] ?? 1.0;
            $totalWeight += $weight;
            
            if (isset($content[$section]) && !empty($content[$section])) {
                $presentSections++;
                $weightedPresent += $weight;
            }
        }

        return $totalWeight > 0 ? round(($weightedPresent / $totalWeight) * 10, 2) : 0;
    }

    /**
     * Calcular integridade estrutural
     */
    protected function calculateStructureIntegrity(): float
    {
        $integrity = $this->validateSectionsIntegrityByTemplate();
        
        $score = 0;
        $score += ($integrity['completion_percentage'] / 100) * 4; // 40% do score
        $score += ($integrity['quality_score'] / 10) * 4; // 40% do score
        $score += empty($integrity['issues']) ? 2 : 0; // 20% do score
        
        return round($score, 2);
    }

    /**
     * Calcular otimização SEO
     */
    protected function calculateSeoOptimization(): float
    {
        $score = 0;
        
        // Título
        if ($this->title) {
            $titleLength = strlen($this->title);
            if ($titleLength >= 40 && $titleLength <= 60) {
                $score += 2;
            } elseif ($titleLength >= 30 && $titleLength <= 70) {
                $score += 1.5;
            } else {
                $score += 0.5;
            }
        }

        // Meta description
        if ($this->meta_description) {
            $metaLength = strlen($this->meta_description);
            if ($metaLength >= 140 && $metaLength <= 160) {
                $score += 2;
            } elseif ($metaLength >= 120 && $metaLength <= 180) {
                $score += 1.5;
            } else {
                $score += 0.5;
            }
        }

        // Keywords
        $keywords = $this->seo_keywords ?? [];
        if (count($keywords) >= 3 && count($keywords) <= 10) {
            $score += 2;
        } elseif (count($keywords) > 0) {
            $score += 1;
        }

        // Slug SEO-friendly
        if ($this->slug && strlen($this->slug) > 10 && strpos($this->slug, $this->template_type) !== false) {
            $score += 1.5;
        }

        // URL canônica
        if ($this->canonical_url) {
            $score += 1;
        }

        return round($score, 2);
    }

    /**
     * Calcular compliance com template
     */
    protected function calculateTemplateCompliance(): float
    {
        $compatibility = $this->validateViewModelCompatibility();
        
        $score = ($compatibility['compatibility_score'] / 100) * 8; // 80% baseado na compatibilidade
        
        // Bonus por seções opcionais
        if ($compatibility['optional_sections_present'] > 0) {
            $score += min(2, $compatibility['optional_sections_present'] * 0.5);
        }

        return round($score, 2);
    }

    /**
     * Calcular score de linking
     */
    protected function calculateLinkingScore(): float
    {
        $score = 0;
        
        // Artigo irmão
        if ($this->sibling_article_id) {
            $score += 4; // 40% do score por ter artigo irmão
        }

        // Cross-links
        $crossLinksCount = count($this->cross_links ?? []);
        $score += min(3, $crossLinksCount * 0.5); // Até 30% por cross-links

        // Artigos relacionados
        $relatedCount = count($this->related_articles ?? []);
        $score += min(3, $relatedCount * 0.3); // Até 30% por artigos relacionados

        return round($score, 2);
    }

    /**
     * Calcular score geral de qualidade
     */
    protected function calculateOverallQualityScore(): float
    {
        $metrics = $this->quality_metrics ?? $this->calculateQualityMetrics();
        
        return $metrics['overall_score'] ?? 0;
    }

    // =======================================================================
    // VALIDAÇÃO ESPECÍFICA POR TEMPLATE
    // =======================================================================

    /**
     * Validar integridade das seções baseado no template_type
     */
    public function validateSectionsIntegrityByTemplate(): array
    {
        $templateType = $this->template_type ?? 'ideal';
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        
        $validation = [
            'is_valid' => true,
            'template_type' => $templateType,
            'vehicle_type' => $isMotorcycle ? 'motorcycle' : 'car',
            'issues' => [],
            'warnings' => [],
            'sections_analysis' => [],
            'completion_percentage' => 0,
            'quality_score' => 0
        ];

        $expectedSections = $this->getExpectedSectionsByTemplate($templateType, $isMotorcycle);
        $presentSections = 0;
        $totalQualityScore = 0;

        foreach ($expectedSections as $section => $expectedType) {
            $sectionField = "sections_{$section}";
            $content = $this->$sectionField;
            
            $sectionAnalysis = [
                'present' => !empty($content),
                'type_valid' => false,
                'content_score' => 0,
                'issues' => [],
                'warnings' => []
            ];

            if (empty($content)) {
                $validation['issues'][] = "Seção '{$section}' está ausente para template '{$templateType}'";
                $sectionAnalysis['content_score'] = 0;
            } else {
                $presentSections++;
                $sectionAnalysis['present'] = true;
                
                // Validar tipo
                if ($expectedType === 'array' && is_array($content)) {
                    $sectionAnalysis['type_valid'] = true;
                } elseif ($expectedType === 'string' && is_string($content)) {
                    $sectionAnalysis['type_valid'] = true;
                }
                
                // Validações específicas por seção e template
                $sectionScore = $this->validateSpecificSectionByTemplate($section, $content, $templateType, $sectionAnalysis);
                $sectionAnalysis['content_score'] = $sectionScore;
                $totalQualityScore += $sectionScore;
            }

            $validation['sections_analysis'][$section] = $sectionAnalysis;
        }

        $validation['completion_percentage'] = round(($presentSections / count($expectedSections)) * 100, 2);
        $validation['quality_score'] = $presentSections > 0 ? round($totalQualityScore / $presentSections, 2) : 0;
        $validation['is_valid'] = empty($validation['issues']) && $validation['completion_percentage'] >= 80;

        return $validation;
    }

    /**
     * Validar seção específica com scoring
     */
    protected function validateSpecificSectionByTemplate(string $section, $content, string $templateType, array &$sectionAnalysis): float
    {
        $score = 5.0; // Score base

        switch ($section) {
            case 'intro':
                if (isset($content['content'])) {
                    $length = strlen($content['content']);
                    if ($length < 50) {
                        $sectionAnalysis['issues'][] = "Introdução muito curta (< 50 caracteres)";
                        $score -= 2.0;
                    } elseif ($length > 300) {
                        $score += 1.0;
                    }
                    
                    // Verificar menção do veículo
                    $vehicleName = "{$this->make} {$this->model}";
                    if (stripos($content['content'], $vehicleName) !== false) {
                        $score += 0.5;
                    }
                }
                break;
                
            case 'pressure_table':
                if ($templateType === 'ideal') {
                    if (isset($content['content']['versoes']) && is_array($content['content']['versoes'])) {
                        $score += count($content['content']['versoes']) * 0.5;
                    } else {
                        $sectionAnalysis['issues'][] = "Tabela de pressão ideal sem versões definidas";
                        $score -= 2.0;
                    }
                } elseif ($templateType === 'calibration') {
                    if (isset($content['content']['usage_scenarios']) && is_array($content['content']['usage_scenarios'])) {
                        $scenarios = count($content['content']['usage_scenarios']);
                        if ($scenarios >= 3) {
                            $score += 1.0;
                        } elseif ($scenarios < 2) {
                            $sectionAnalysis['issues'][] = "Cenários de calibragem insuficientes (< 2)";
                            $score -= 1.5;
                        }
                    }
                }
                break;
                
            case 'how_to_calibrate':
                if (is_array($content['content'])) {
                    $steps = count($content['content']);
                    $expectedSteps = $templateType === 'calibration' ? 5 : 3;
                    
                    if ($steps >= $expectedSteps) {
                        $score += ($steps - $expectedSteps) * 0.2;
                    } else {
                        $sectionAnalysis['issues'][] = "Passos insuficientes para {$templateType} (< {$expectedSteps})";
                        $score -= 1.0;
                    }
                }
                break;
                
            case 'faq':
                if (isset($content['content']) && is_array($content['content'])) {
                    $questions = count($content['content']);
                    $minQuestions = $templateType === 'calibration' ? 4 : 3;
                    
                    if ($questions >= $minQuestions) {
                        $score += ($questions - $minQuestions) * 0.3;
                    } else {
                        $sectionAnalysis['issues'][] = "FAQ insuficiente para {$templateType} (< {$minQuestions} perguntas)";
                        $score -= 1.0;
                    }
                    
                    // Verificar qualidade das perguntas
                    foreach ($content['content'] as $faq) {
                        if (isset($faq['question']) && isset($faq['answer'])) {
                            if (strlen($faq['answer']) > 100) {
                                $score += 0.1;
                            }
                        }
                    }
                }
                break;
        }

        return max(0, min(10, $score)); // Score entre 0 e 10
    }

    /**
     * Verificar compatibilidade completa com ViewModel
     */
    public function validateViewModelCompatibility(): array
    {
        $templateType = $this->template_type ?? 'ideal';
        $isMotorcycle = $this->vehicle_data['is_motorcycle'] ?? false;
        $content = $this->article_content ?? [];
        
        $compatibility = [
            'is_compatible' => true,
            'template_type' => $templateType,
            'vehicle_type' => $isMotorcycle ? 'motorcycle' : 'car',
            'view_model' => $this->getTemplateNameByType(),
            'required_sections_present' => 0,
            'optional_sections_present' => 0,
            'missing_sections' => [],
            'extra_sections' => [],
            'structure_issues' => [],
            'compatibility_score' => 0,
            'recommendations' => []
        ];

        $expectedSections = $this->getExpectedSectionsByTemplate($templateType, $isMotorcycle);
        $optionalSections = $this->getOptionalSectionsByTemplate($templateType, $isMotorcycle);
        
        // Verificar seções obrigatórias
        foreach ($expectedSections as $section => $expectedType) {
            if (!isset($content[$section])) {
                $compatibility['missing_sections'][] = $section;
                $compatibility['is_compatible'] = false;
            } elseif (empty($content[$section])) {
                $compatibility['structure_issues'][] = "Seção '{$section}' está vazia";
                $compatibility['is_compatible'] = false;
            } else {
                $compatibility['required_sections_present']++;
                
                // Validar tipo específico
                if ($expectedType === 'array' && !is_array($content[$section])) {
                    $compatibility['structure_issues'][] = "Seção '{$section}' deveria ser array, encontrado " . gettype($content[$section]);
                } elseif ($expectedType === 'string' && !is_string($content[$section])) {
                    $compatibility['structure_issues'][] = "Seção '{$section}' deveria ser string, encontrado " . gettype($content[$section]);
                }
            }
        }
        
        // Verificar seções opcionais
        foreach ($optionalSections as $section => $expectedType) {
            if (isset($content[$section]) && !empty($content[$section])) {
                $compatibility['optional_sections_present']++;
            }
        }
        
        // Identificar seções extras
        $allExpectedSections = array_merge(array_keys($expectedSections), array_keys($optionalSections));
        foreach (array_keys($content) as $section) {
            if (!in_array($section, $allExpectedSections)) {
                $compatibility['extra_sections'][] = $section;
            }
        }

        // Calcular score de compatibilidade
        $totalExpected = count($expectedSections);
        $compatibility['compatibility_score'] = $totalExpected > 0 
            ? round(($compatibility['required_sections_present'] / $totalExpected) * 100, 2) 
            : 0;

        // Gerar recomendações
        if (!empty($compatibility['missing_sections'])) {
            $compatibility['recommendations'][] = "Adicionar seções obrigatórias: " . implode(', ', $compatibility['missing_sections']);
        }
        
        if ($compatibility['compatibility_score'] < 100 && $compatibility['compatibility_score'] > 80) {
            $compatibility['recommendations'][] = "Compatibilidade boa mas não perfeita. Verificar seções em falta.";
        } elseif ($compatibility['compatibility_score'] <= 80) {
            $compatibility['recommendations'][] = "Compatibilidade baixa. Revisar estrutura do template.";
        }
        
        if (!empty($compatibility['extra_sections'])) {
            $compatibility['recommendations'][] = "Seções extras detectadas: " . implode(', ', $compatibility['extra_sections']);
        }

        return $compatibility;
    }

    /**
     * Obter seções esperadas baseado no template e tipo de veículo
     */
    protected function getExpectedSectionsByTemplate(string $templateType, bool $isMotorcycle): array
    {
        if ($isMotorcycle) {
            // Para motocicletas
            if ($templateType === 'ideal') {
                return [
                    'introducao' => 'string',
                    'especificacoes_pneus' => 'array',
                    'tabela_pressoes' => 'array',
                    'conversao_unidades' => 'array',
                    'beneficios_calibragem' => 'array',
                    'perguntas_frequentes' => 'array',
                    'consideracoes_finais' => 'string'
                ];
            } else {
                return [
                    'introducao' => 'string',
                    'tire_specifications' => 'array',
                    'pressure_table' => 'array',
                    'calibration_procedure' => 'array',
                    'equipment_guide' => 'array',
                    'safety_considerations' => 'array',
                    'perguntas_frequentes' => 'array',
                    'consideracoes_finais' => 'string'
                ];
            }
        } else {
            // Para carros
            if ($templateType === 'ideal') {
                return [
                    'introducao' => 'string',
                    'especificacoes_pneus' => 'array',
                    'tabela_pressoes' => 'array',
                    'conversao_unidades' => 'array',
                    'localizacao_etiqueta' => 'array',
                    'beneficios_calibragem' => 'array',
                    'dicas_manutencao' => 'array',
                    'alertas_importantes' => 'array',
                    'perguntas_frequentes' => 'array',
                    'consideracoes_finais' => 'string'
                ];
            } else {
                return [
                    'introducao' => 'string',
                    'tire_specifications' => 'array',
                    'pressure_table' => 'array',
                    'calibration_procedure' => 'array',
                    'equipment_guide' => 'array',
                    'tpms_system' => 'array',
                    'maintenance_schedule' => 'array',
                    'troubleshooting' => 'array',
                    'safety_considerations' => 'array',
                    'cost_considerations' => 'array',
                    'perguntas_frequentes' => 'array',
                    'consideracoes_finais' => 'string'
                ];
            }
        }
    }

    /**
     * Obter seções opcionais por template
     */
    protected function getOptionalSectionsByTemplate(string $templateType, bool $isMotorcycle): array
    {
        if ($templateType === 'ideal') {
            return [
                'comparacao_marcas' => 'array',
                'dicas_economia' => 'array',
                'manutencao_sazonal' => 'array'
            ];
        } else {
            return [
                'advanced_techniques' => 'array',
                'professional_tips' => 'array',
                'emergency_procedures' => 'array',
                'seasonal_adjustments' => 'array'
            ];
        }
    }

    // =======================================================================
    // SISTEMA DE BACKUP E VERSIONING
    // =======================================================================

    /**
     * Criar backup do conteúdo antes de alterações importantes
     */
    protected function createContentBackup(): void
    {
        try {
            $backupData = [
                'timestamp' => now()->toISOString(),
                'article_content' => $this->getOriginal('article_content'),
                'sections_refined' => $this->getOriginal('sections_refined'),
                'content_score' => $this->getOriginal('content_score'),
                'quality_metrics' => $this->getOriginal('quality_metrics'),
                'backup_reason' => 'content_update'
            ];

            $existingBackups = $this->backup_data ?? [];
            $existingBackups[] = $backupData;

            // Manter apenas os últimos 5 backups
            if (count($existingBackups) > 5) {
                $existingBackups = array_slice($existingBackups, -5);
            }

            $this->backup_data = $existingBackups;
            $this->last_backup_at = now();

            Log::debug("Backup de conteúdo criado", [
                'article_id' => $this->_id,
                'backup_count' => count($existingBackups)
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao criar backup de conteúdo", [
                'article_id' => $this->_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Restaurar backup específico
     */
    public function restoreBackup(int $backupIndex = 0): bool
    {
        try {
            $backups = $this->backup_data ?? [];
            
            if (!isset($backups[$backupIndex])) {
                throw new \InvalidArgumentException("Backup index {$backupIndex} não encontrado");
            }

            $backup = $backups[$backupIndex];
            
            // Criar backup do estado atual antes de restaurar
            $this->createContentBackup();
            
            // Restaurar dados
            $this->article_content = $backup['article_content'];
            $this->sections_refined = $backup['sections_refined'] ?? [];
            $this->content_score = $backup['content_score'] ?? 0;
            
            // Recalcular métricas
            $this->calculateQualityMetrics();
            
            $this->save();

            Log::info("Backup restaurado com sucesso", [
                'article_id' => $this->_id,
                'backup_index' => $backupIndex,
                'backup_timestamp' => $backup['timestamp']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erro ao restaurar backup", [
                'article_id' => $this->_id,
                'backup_index' => $backupIndex,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    // =======================================================================
    // ESTATÍSTICAS E RELATÓRIOS AVANÇADOS
    // =======================================================================

    /**
     * Estatísticas completas por template
     */
    public static function getAdvancedGenerationStatistics(): array
    {
        $stats = [
            'overview' => [
                'total_articles' => static::count(),
                'total_vehicles' => static::selectRaw('COUNT(DISTINCT CONCAT(make, model, year)) as count')->first()->count ?? 0,
                'templates_distribution' => [],
                'quality_distribution' => [],
                'last_updated' => now()->toISOString()
            ],
            'by_template' => [],
            'cross_linking' => [
                'articles_with_siblings' => 0,
                'orphaned_articles' => 0,
                'cross_links_total' => 0,
                'linking_coverage' => 0
            ],
            'quality_metrics' => [
                'average_content_score' => 0,
                'articles_high_quality' => 0,
                'articles_need_improvement' => 0,
                'validation_coverage' => 0
            ]
        ];

        try {
            // Distribuição por template
            $templateStats = static::selectRaw('
                template_type,
                generation_status,
                COUNT(*) as count,
                AVG(content_score) as avg_score,
                AVG(CASE WHEN quality_checked = true THEN 1 ELSE 0 END) as quality_rate
            ')->groupBy(['template_type', 'generation_status'])->get();

            foreach ($templateStats as $stat) {
                $template = $stat->template_type ?: 'undefined';
                $status = $stat->generation_status;
                
                if (!isset($stats['by_template'][$template])) {
                    $stats['by_template'][$template] = [
                        'total' => 0,
                        'by_status' => [],
                        'avg_content_score' => 0,
                        'quality_rate' => 0
                    ];
                }
                
                $stats['by_template'][$template]['total'] += $stat->count;
                $stats['by_template'][$template]['by_status'][$status] = $stat->count;
                $stats['by_template'][$template]['avg_content_score'] = round($stat->avg_score ?? 0, 2);
                $stats['by_template'][$template]['quality_rate'] = round(($stat->quality_rate ?? 0) * 100, 2);
            }

            // Cross-linking statistics
            $stats['cross_linking']['articles_with_siblings'] = static::whereNotNull('sibling_article_id')->count();
            $stats['cross_linking']['orphaned_articles'] = static::whereNull('sibling_article_id')->count();

            // Quality metrics
            $qualityData = static::whereNotNull('content_score')->get(['content_score', 'quality_checked']);
            if ($qualityData->isNotEmpty()) {
                $stats['quality_metrics']['average_content_score'] = round($qualityData->avg('content_score'), 2);
                $stats['quality_metrics']['articles_high_quality'] = $qualityData->where('content_score', '>=', 8)->count();
                $stats['quality_metrics']['articles_need_improvement'] = $qualityData->where('content_score', '<', 6)->count();
                $stats['quality_metrics']['validation_coverage'] = round(($qualityData->where('quality_checked', true)->count() / $qualityData->count()) * 100, 2);
            }

        } catch (\Exception $e) {
            Log::error("Erro ao calcular estatísticas avançadas", [
                'error' => $e->getMessage()
            ]);
            
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }

    /**
     * Relatório de integridade dual template
     */
    public static function getComprehensiveDualTemplateReport(): array
    {
        $report = [
            'summary' => [
                'total_unique_vehicles' => 0,
                'vehicles_with_both_templates' => 0,
                'vehicles_with_only_ideal' => 0,
                'vehicles_with_only_calibration' => 0,
                'integrity_score' => 0,
                'cross_linking_coverage' => 0
            ],
            'detailed_analysis' => [
                'orphaned_templates' => [],
                'quality_comparison' => []
            ],
            'recommendations' => []
        ];

        try {
            // Análise de veículos únicos
            $vehicles = static::selectRaw('make, model, year')
                             ->groupBy(['make', 'model', 'year'])
                             ->get();

            $report['summary']['total_unique_vehicles'] = $vehicles->count();

            foreach ($vehicles as $vehicle) {
                $articles = static::where('make', $vehicle->make)
                                 ->where('model', $vehicle->model)
                                 ->where('year', $vehicle->year)
                                 ->get();

                $templates = $articles->pluck('template_type')->unique()->filter()->toArray();
                $templateCount = count($templates);

                if ($templateCount === 2) {
                    $report['summary']['vehicles_with_both_templates']++;
                } elseif ($templateCount === 1) {
                    $template = $templates[0];
                    $article = $articles->first();
                    
                    if ($template === 'ideal') {
                        $report['summary']['vehicles_with_only_ideal']++;
                    } elseif ($template === 'calibration') {
                        $report['summary']['vehicles_with_only_calibration']++;
                    }
                    
                    $report['detailed_analysis']['orphaned_templates'][] = [
                        'vehicle' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
                        'existing_template' => $template,
                        'missing_template' => $template === 'ideal' ? 'calibration' : 'ideal',
                        'article_id' => $article->_id,
                        'content_score' => $article->content_score ?? 0,
                        'generation_status' => $article->generation_status
                    ];
                }
            }

            // Calcular scores
            if ($report['summary']['total_unique_vehicles'] > 0) {
                $report['summary']['integrity_score'] = round(
                    ($report['summary']['vehicles_with_both_templates'] / $report['summary']['total_unique_vehicles']) * 100,
                    2
                );
            }

            // Cross-linking coverage
            $articlesWithSiblings = static::whereNotNull('sibling_article_id')->count();
            $totalArticles = static::count();
            if ($totalArticles > 0) {
                $report['summary']['cross_linking_coverage'] = round(($articlesWithSiblings / $totalArticles) * 100, 2);
            }

            // Gerar recomendações
            $report['recommendations'] = static::generateRecommendationsForReport($report);

        } catch (\Exception $e) {
            Log::error("Erro no relatório de integridade dual template", [
                'error' => $e->getMessage()
            ]);
            
            $report['error'] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Gerar recomendações para o relatório
     */
    protected static function generateRecommendationsForReport(array $report): array
    {
        $recommendations = [];
        $summary = $report['summary'];
        $orphanCount = count($report['detailed_analysis']['orphaned_templates']);

        if ($summary['integrity_score'] < 50) {
            $recommendations[] = [
                'priority' => 'critical',
                'action' => 'Executar geração massiva de templates complementares',
                'description' => "Integridade muito baixa ({$summary['integrity_score']}%). Focar na geração do template em falta."
            ];
        }

        if ($orphanCount > 10) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'Gerar templates para artigos órfãos prioritários',
                'description' => "Processar os {$orphanCount} artigos órfãos, começando pelos de maior prioridade."
            ];
        }

        if ($summary['cross_linking_coverage'] < 30) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'Implementar sistema de cross-linking automático',
                'description' => "Cobertura de cross-linking baixa ({$summary['cross_linking_coverage']}%). Conectar artigos irmãos existentes."
            ];
        }

        return $recommendations;
    }

    // =======================================================================
    // MÉTODOS UTILITÁRIOS E HELPERS
    // =======================================================================

    /**
     * Aplicar filtros avançados a queries
     */
    protected function applyAdvancedFilters($query, array $filters)
    {
        if (isset($filters['min_content_score'])) {
            $query->where('content_score', '>=', $filters['min_content_score']);
        }

        if (isset($filters['generation_status'])) {
            $query->whereIn('generation_status', (array) $filters['generation_status']);
        }

        if (isset($filters['has_sibling'])) {
            if ($filters['has_sibling']) {
                $query->whereNotNull('sibling_article_id');
            } else {
                $query->whereNull('sibling_article_id');
            }
        }

        if (isset($filters['quality_checked'])) {
            $query->where('quality_checked', $filters['quality_checked']);
        }

        return $query;
    }

    /**
     * Garantir unicidade do slug
     */
    protected function ensureSlugUniqueness(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;
        
        while (static::where('slug', $slug)
                    ->where('template_type', $this->template_type)
                    ->where('_id', '!=', $this->_id ?? null)
                    ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Garantir campos obrigatórios no boot
     */
    protected function ensureRequiredFields(): void
    {
        if (!$this->template_type) {
            $this->template_type = 'ideal';
        }
        
        if (!$this->generation_status) {
            $this->generation_status = 'pending';
        }
        
        if (!$this->quality_checked) {
            $this->quality_checked = false;
        }
    }

    /**
     * Auto-gerar slug e título no boot
     */
    protected function autoGenerateSlugAndTitle(): void
    {
        if (!$this->slug && $this->make && $this->model && $this->year && $this->template_type) {
            $this->slug = $this->generateWordPressSlug();
            $this->wordpress_slug = $this->slug;
        }
        
        if (!$this->title && $this->make && $this->model && $this->year && $this->template_type) {
            $this->title = $this->generateTitleByTemplate();
        }
        
        if (!$this->template_used && $this->template_type) {
            $this->template_used = $this->getTemplateNameByType();
        }
    }

    /**
     * Inicializar métricas de qualidade no boot
     */
    protected function initializeQualityMetrics(): void
    {
        if (!$this->quality_metrics) {
            $this->quality_metrics = [
                'initialized_at' => now()->toISOString(),
                'version' => '3.0',
                'needs_calculation' => true
            ];
        }
        
        if (!$this->sections_status) {
            $this->sections_status = [
                'intro' => 'pending',
                'pressure_table' => 'pending',
                'how_to_calibrate' => 'pending',
                'middle_content' => 'pending',
                'faq' => 'pending',
                'conclusion' => 'pending'
            ];
        }
    }

   /**
     * Validar template type no boot
     */
    protected function validateTemplateType(): void
    {
        if ($this->template_type && !in_array($this->template_type, ['ideal', 'calibration'])) {
            throw new \InvalidArgumentException("Template type inválido: {$this->template_type}");
        }
    }

    /**
     * Atualizar métricas de qualidade no save
     */
    protected function updateQualityMetrics(): void
    {
        if ($this->isDirty(['article_content', 'sections_refined']) || 
            ($this->quality_metrics['needs_calculation'] ?? false)) {
            $this->calculateQualityMetrics();
        }
    }

    /**
     * Limpar cache relacionado ao deletar
     */
    protected function clearRelatedCache(): void
    {
        $cacheKeys = [
            "article_quality_{$this->_id}",
            "sibling_url_{$this->_id}",
            "cross_links_{$this->_id}",
            "template_stats_{$this->template_type}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Criar slug otimizado para SEO
     */
    protected function slugify(string $text): string
    {
        $text = $this->removeAccents($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Remover acentos
     */
    protected function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ñ' => 'N', 'Ç' => 'C',
        ];

        return strtr($text, $unwanted);
    }

    // =======================================================================
    // MÉTODOS HERDADOS E COMPATIBILIDADE (mantidos)
    // =======================================================================

    /**
     * Marcar como gerado e quebrar em seções
     */
    public function markAsGenerated(): void
    {
        $this->generation_status = 'generated';
        $this->processed_at = now();
        
        // Registrar métricas de performance
        if (!$this->performance_metrics) {
            $this->performance_metrics = [
                'generation_completed_at' => now()->toISOString(),
                'template_type' => $this->template_type
            ];
        }
        
        $this->save();
        $this->breakIntoSections();
        
        // Tentar conectar com artigo irmão automaticamente
        $this->findAndConnectSiblingArticle();
    }

    /**
     * Quebrar article_content em seções separadas
     */
    public function breakIntoSections(): void
    {
        $content = $this->article_content ?? [];
        
        // Extrair seções baseado no template type
        if ($this->template_type === 'ideal') {
            $this->sections_intro = $content['introducao'] ?? null;
            $this->sections_pressure_table = $content['tabela_pressoes'] ?? null;
            $this->sections_how_to_calibrate = $content['dicas_manutencao'] ?? null;
            $this->sections_middle_content = [
                'beneficios_calibragem' => $content['beneficios_calibragem'] ?? null,
                'alertas_importantes' => $content['alertas_importantes'] ?? null,
                'conversao_unidades' => $content['conversao_unidades'] ?? null
            ];
            $this->sections_faq = $content['perguntas_frequentes'] ?? null;
            $this->sections_conclusion = $content['consideracoes_finais'] ?? null;
        } else {
            $this->sections_intro = $content['introducao'] ?? null;
            $this->sections_pressure_table = $content['pressure_table'] ?? null;
            $this->sections_how_to_calibrate = $content['calibration_procedure'] ?? null;
            $this->sections_middle_content = [
                'equipment_guide' => $content['equipment_guide'] ?? null,
                'tpms_system' => $content['tpms_system'] ?? null,
                'troubleshooting' => $content['troubleshooting'] ?? null
            ];
            $this->sections_faq = $content['perguntas_frequentes'] ?? null;
            $this->sections_conclusion = $content['consideracoes_finais'] ?? null;
        }
        
        // Inicializar controles de refinamento
        $this->sections_refined = [];
        $this->sections_scores = [
            'intro' => $this->calculateSectionScore('intro'),
            'pressure_table' => $this->calculateSectionScore('pressure_table'),
            'how_to_calibrate' => $this->calculateSectionScore('how_to_calibrate'),
            'middle_content' => $this->calculateSectionScore('middle_content'),
            'faq' => $this->calculateSectionScore('faq'),
            'conclusion' => $this->calculateSectionScore('conclusion')
        ];
        
        $this->save();
    }

    /**
     * Calcular score de seção
     */
    public function calculateSectionScore(string $sectionName): float
    {
        $sectionField = "sections_{$sectionName}";
        $content = $this->$sectionField;
        
        if (empty($content)) {
            return 0.0;
        }
        
        $score = 6.0; // Base score
        
        // Análise básica de conteúdo
        if (is_array($content)) {
            $score += count($content) * 0.2;
        } elseif (is_string($content)) {
            $score += strlen($content) > 100 ? 1.0 : 0.5;
        }
        
        // Score específico por template
        if ($this->template_type === 'calibration' && in_array($sectionName, ['how_to_calibrate', 'middle_content'])) {
            $score += 1.0; // Bonus para seções críticas do template calibration
        }
        
        return min(10.0, $score);
    }

    /**
     * Obter progresso das seções
     */
    public function getSectionsProgress(): array
    {
        $totalSections = 6;
        $refinedSections = count($this->sections_refined ?? []);
        
        return [
            'total_sections' => $totalSections,
            'refined_sections' => $refinedSections,
            'progress_percentage' => round(($refinedSections / $totalSections) * 100, 1),
            'sections_status' => $this->sections_status ?? [],
            'sections_scores' => $this->sections_scores ?? [],
            'is_complete' => $refinedSections >= $totalSections,
            'template_type' => $this->template_type,
            'quality_score' => $this->calculateOverallQualityScore()
        ];
    }

    /**
     * Refinar seção específica
     */
    public function refineSection(string $sectionName, array $refinedContent, float $score = null): bool
    {
        try {
            $sectionField = "sections_{$sectionName}";
            
            if (!property_exists($this, $sectionField)) {
                throw new \InvalidArgumentException("Seção inválida: {$sectionName}");
            }

            // Atualizar conteúdo refinado
            $this->$sectionField = $refinedContent;

            // Atualizar arrays de controle
            $sectionsRefined = $this->sections_refined ?? [];
            $sectionsScores = $this->sections_scores ?? [];
            $sectionsStatus = $this->sections_status ?? [];

            $sectionsRefined[] = $sectionName;
            $sectionsScores[$sectionName] = $score ?? $this->calculateSectionScore($sectionName);
            $sectionsStatus[$sectionName] = 'refined';

            $this->sections_refined = array_unique($sectionsRefined);
            $this->sections_scores = $sectionsScores;
            $this->sections_status = $sectionsStatus;
            $this->sections_last_refined_at = now();

            // Recalcular score geral se todas as seções foram refinadas
            if (count($this->sections_refined) >= 6) {
                $this->generation_status = 'claude_enhanced';
                $this->claude_last_enhanced_at = now();
                $this->claude_enhancement_count = ($this->claude_enhancement_count ?? 0) + 1;
                
                // Recalcular métricas de qualidade
                $this->calculateQualityMetrics();
            }

            $this->save();

            Log::info("Seção refinada com sucesso", [
                'article_id' => $this->_id,
                'section' => $sectionName,
                'template_type' => $this->template_type,
                'score' => $sectionsScores[$sectionName],
                'progress' => count($this->sections_refined) . '/6'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Erro ao refinar seção", [
                'article_id' => $this->_id,
                'section' => $sectionName,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Verificar se artigo tem template irmão
     */
    public function hasBothTemplates(): bool
    {
        $vehicleArticles = static::where('make', $this->make)
                                ->where('model', $this->model)
                                ->where('year', $this->year)
                                ->pluck('template_type');
        
        return $vehicleArticles->contains('ideal') && $vehicleArticles->contains('calibration');
    }

    /**
     * Obter estatísticas do par de artigos (mesmo veículo)
     */
    public function getVehiclePairStatistics(): array
    {
        $articles = static::where('make', $this->make)
                         ->where('model', $this->model)
                         ->where('year', $this->year)
                         ->get();

        $stats = [
            'total_articles' => $articles->count(),
            'templates_present' => $articles->pluck('template_type')->unique()->toArray(),
            'generation_status' => $articles->pluck('generation_status')->unique()->toArray(),
            'both_generated' => $articles->where('generation_status', '!=', 'pending')->count() === 2,
            'both_enhanced' => $articles->where('generation_status', 'claude_enhanced')->count() === 2,
            'both_published' => $articles->where('generation_status', 'published')->count() === 2
        ];

        return $stats;
    }

    /**
     * Clonar artigo para template diferente
     */
    public function cloneForTemplate(string $targetTemplate): ?TirePressureArticle
    {
        if ($this->template_type === $targetTemplate) {
            return null; // Não pode clonar para o mesmo template
        }

        $clone = new static();
        
        // Copiar dados básicos
        $clone->fill($this->toArray());
        
        // Limpar IDs e campos únicos
        unset($clone->_id);
        $clone->template_type = $targetTemplate;
        $clone->slug = null; // Será regenerado
        $clone->wordpress_slug = null; // Será regenerado
        $clone->title = null; // Será regenerado
        $clone->generation_status = 'pending';
        $clone->processed_at = null;
        
        // Limpar dados de refinamento Claude
        $clone->claude_enhancements = null;
        $clone->claude_last_enhanced_at = null;
        $clone->claude_enhancement_count = 0;
        $clone->sections_refined = [];
        $clone->sections_status = [
            'intro' => 'pending',
            'pressure_table' => 'pending',
            'how_to_calibrate' => 'pending',
            'middle_content' => 'pending',
            'faq' => 'pending',
            'conclusion' => 'pending'
        ];

        return $clone;
    }

    /**
     * Obter estatísticas de geração por template
     */
    public static function getGenerationStatisticsByTemplate(): array
    {
        try {
            $stats = [
                'total' => static::count(),
                'by_template' => [],
                'by_status' => [],
                'template_completion_rates' => []
            ];

            // Estatísticas por template
            $templateStats = static::selectRaw('
                template_type,
                generation_status,
                COUNT(*) as count
            ')->groupBy(['template_type', 'generation_status'])->get();

            foreach ($templateStats as $stat) {
                $template = $stat->template_type ?: 'undefined';
                $status = $stat->generation_status;
                
                if (!isset($stats['by_template'][$template])) {
                    $stats['by_template'][$template] = [
                        'total' => 0,
                        'pending' => 0,
                        'generated' => 0,
                        'claude_enhanced' => 0,
                        'published' => 0
                    ];
                }
                
                $stats['by_template'][$template]['total'] += $stat->count;
                $stats['by_template'][$template][$status] = $stat->count;
            }

            // Taxa de conclusão por template
            foreach ($stats['by_template'] as $template => $data) {
                if ($data['total'] > 0) {
                    $completed = $data['claude_enhanced'] + $data['published'];
                    $stats['template_completion_rates'][$template] = round(($completed / $data['total']) * 100, 2);
                }
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error("Erro ao obter estatísticas de template", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total' => 0,
                'by_template' => [],
                'by_status' => [],
                'template_completion_rates' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar integridade de dual templates
     */
    public static function validateDualTemplateIntegrity(): array
    {
        $report = [
            'vehicles_with_both_templates' => 0,
            'vehicles_with_only_ideal' => 0,
            'vehicles_with_only_calibration' => 0,
            'total_unique_vehicles' => 0,
            'orphaned_templates' => [],
            'integrity_score' => 0,
            'integrity_issues' => [],
            'recommendations' => [],
            'cross_linking_opportunities' => 0
        ];

        try {
            // Obter todos os veículos únicos
            $vehicles = static::selectRaw('DISTINCT make, model, year')
                             ->get();
            
            $report['total_unique_vehicles'] = $vehicles->count();

            foreach ($vehicles as $vehicle) {
                $articles = static::where('make', $vehicle->make)
                                 ->where('model', $vehicle->model)
                                 ->where('year', $vehicle->year)
                                 ->get();
                
                $templates = $articles->pluck('template_type')->unique()->filter()->toArray();
                $templateCount = count($templates);
                
                if ($templateCount === 2) {
                    $report['vehicles_with_both_templates']++;
                    $report['cross_linking_opportunities']++;
                } elseif ($templateCount === 1) {
                    $template = $templates[0];
                    if ($template === 'ideal') {
                        $report['vehicles_with_only_ideal']++;
                        $report['orphaned_templates'][] = [
                            'vehicle' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
                            'existing_template' => 'ideal',
                            'missing_template' => 'calibration',
                            'articles_count' => $articles->count()
                        ];
                    } elseif ($template === 'calibration') {
                        $report['vehicles_with_only_calibration']++;
                        $report['orphaned_templates'][] = [
                            'vehicle' => "{$vehicle->make} {$vehicle->model} {$vehicle->year}",
                            'existing_template' => 'calibration',
                            'missing_template' => 'ideal',
                            'articles_count' => $articles->count()
                        ];
                    }
                } else {
                    // Caso raro: veículo sem templates válidos ou mais de 2
                    $report['integrity_issues'][] = "Veículo {$vehicle->make} {$vehicle->model} {$vehicle->year} com configuração inválida de templates: " . implode(', ', $templates);
                }
            }

            // Calcular score de integridade
            if ($report['total_unique_vehicles'] > 0) {
                $report['integrity_score'] = round(
                    ($report['vehicles_with_both_templates'] / $report['total_unique_vehicles']) * 100, 
                    2
                );
            }

            // Gerar recomendações
            $orphanCount = count($report['orphaned_templates']);
            if ($orphanCount > 0) {
                $report['recommendations'][] = "Existem {$orphanCount} veículos com apenas um template. Considerar gerar o template complementar.";
                
                if ($report['vehicles_with_only_ideal'] > $report['vehicles_with_only_calibration']) {
                    $report['recommendations'][] = "Foco na geração de templates 'calibration' (mais veículos têm apenas 'ideal').";
                } elseif ($report['vehicles_with_only_calibration'] > $report['vehicles_with_only_ideal']) {
                    $report['recommendations'][] = "Foco na geração de templates 'ideal' (mais veículos têm apenas 'calibration').";
                }
            }
            
            if ($report['cross_linking_opportunities'] > 0) {
                $report['recommendations'][] = "Implementar cross-linking entre {$report['cross_linking_opportunities']} pares de artigos.";
                $report['recommendations'][] = "Comando sugerido: php artisan tire-pressure-guide:create-cross-links";
            }

            if ($report['integrity_score'] >= 90) {
                $report['recommendations'][] = "Excelente integridade dual template ({$report['integrity_score']}%). Sistema funcionando adequadamente.";
            } elseif ($report['integrity_score'] >= 70) {
                $report['recommendations'][] = "Boa integridade dual template ({$report['integrity_score']}%). Algumas oportunidades de melhoria.";
            } else {
                $report['recommendations'][] = "Integridade dual template baixa ({$report['integrity_score']}%). Revisar estratégia de geração.";
            }

        } catch (\Exception $e) {
            $report['integrity_issues'][] = "Erro na validação de integridade: " . $e->getMessage();
            
            Log::error("Erro na validação de integridade dual template", [
                'error' => $e->getMessage()
            ]);
        }

        return $report;
    }

    /**
     * toString otimizado com informações do template
     */
    public function __toString(): string
    {
        $template = $this->template_type ?? 'undefined';
        $progress = $this->getSectionsProgress();
        $qualityScore = $this->calculateOverallQualityScore();
        $hasSibling = $this->sibling_article_id ? ' [+Sibling]' : ' [Orphan]';
        
        return "TirePressureArticle[{$this->vehicle_full_name_with_template}] - Status: {$this->generation_status} - Seções: {$progress['progress_percentage']}% - Qualidade: {$qualityScore}/10{$hasSibling}";
    }
}
