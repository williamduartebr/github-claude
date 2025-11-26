<?php

namespace Src\GuideDataCenter\Domain\Mongo;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model GuideSeo - Gerenciamento de SEO dos guias
 * 
 * Armazena todas as informações de SEO, meta tags, schema.org
 * e otimizações para cada guia.
 * 
 * @property string $_id
 * @property string $guide_id
 * @property string $slug
 * @property string $title
 * @property string $h1
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $primary_keyword
 * @property array $secondary_keywords
 * @property string|null $canonical_url
 * @property array $schema_org
 * @property array $open_graph
 * @property array $twitter_card
 * @property int|null $word_count
 * @property float|null $readability_score
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GuideSeo extends Model
{
    /**
     * Conexão com MongoDB
     */
    protected $connection = 'mongodb';

    /**
     * Nome da collection no MongoDB
     */
    protected $table = 'guide_seo';

    /**
     * Campos protegidos contra mass assignment
     */
    protected $guarded = ['_id'];

    /**
     * Campos que devem ser tratados como datas
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Cast de tipos
     */
    protected $casts = [
        'guide_id' => 'string',
        'slug' => 'string',
        'title' => 'string',
        'h1' => 'string',
        'meta_description' => 'string',
        'meta_keywords' => 'string',
        'primary_keyword' => 'string',
        'secondary_keywords' => 'array',
        'canonical_url' => 'string',
        'schema_org' => 'array',
        'open_graph' => 'array',
        'twitter_card' => 'array',
        'word_count' => 'integer',
        'readability_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores padrão
     */
    protected $attributes = [
        'secondary_keywords' => [],
        'schema_org' => [],
        'open_graph' => [],
        'twitter_card' => [],
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Guia relacionado
     */
    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id', '_id');
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope por guia
     */
    public function scopeByGuide($query, string $guideId)
    {
        return $query->where('guide_id', $guideId);
    }

    /**
     * Scope por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope por palavra-chave primária
     */
    public function scopeByPrimaryKeyword($query, string $keyword)
    {
        return $query->where('primary_keyword', $keyword);
    }

    /**
     * Scope para buscar por palavra-chave secundária
     */
    public function scopeBySecondaryKeyword($query, string $keyword)
    {
        return $query->where('secondary_keywords', $keyword);
    }

    /**
     * Scope para SEOs com boa legibilidade
     */
    public function scopeGoodReadability($query, float $minScore = 60.0)
    {
        return $query->where('readability_score', '>=', $minScore);
    }

    // ====================================================================
    // MÉTODOS AUXILIARES
    // ====================================================================

    /**
     * Adiciona uma palavra-chave secundária
     */
    public function addSecondaryKeyword(string $keyword): void
    {
        $keywords = $this->secondary_keywords ?? [];
        
        if (!in_array($keyword, $keywords)) {
            $keywords[] = $keyword;
            $this->secondary_keywords = $keywords;
        }
    }

    /**
     * Remove uma palavra-chave secundária
     */
    public function removeSecondaryKeyword(string $keyword): void
    {
        $keywords = $this->secondary_keywords ?? [];
        
        $this->secondary_keywords = array_values(
            array_filter($keywords, fn($k) => $k !== $keyword)
        );
    }

    /**
     * Gera schema.org do tipo TechnicalArticle
     */
    public function generateTechnicalArticleSchema(Guide $guide): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'TechnicalArticle',
            'headline' => $this->h1 ?? $this->title,
            'description' => $this->meta_description,
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'GuideDataCenter'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'GuideDataCenter'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('app.url') . '/logo.png',
                ],
            ],
            'datePublished' => $guide->created_at->toIso8601String(),
            'dateModified' => $guide->updated_at->toIso8601String(),
            'mainEntityOfPage' => $this->canonical_url ?? $guide->url,
            'keywords' => implode(', ', array_merge(
                [$this->primary_keyword],
                $this->secondary_keywords ?? []
            )),
            'about' => [
                '@type' => 'Vehicle',
                'name' => $guide->make . ' ' . $guide->model,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $guide->make,
                ],
                'model' => $guide->model,
                'vehicleModelDate' => $guide->year_start,
            ],
        ];
    }

    /**
     * Gera Open Graph tags
     */
    public function generateOpenGraphTags(Guide $guide): array
    {
        return [
            'og:type' => 'article',
            'og:title' => $this->title,
            'og:description' => $this->meta_description,
            'og:url' => $this->canonical_url ?? $guide->url,
            'og:site_name' => config('app.name'),
            'article:published_time' => $guide->created_at->toIso8601String(),
            'article:modified_time' => $guide->updated_at->toIso8601String(),
            'article:tag' => implode(', ', $this->secondary_keywords ?? []),
        ];
    }

    /**
     * Gera Twitter Card tags
     */
    public function generateTwitterCardTags(): array
    {
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->title,
            'twitter:description' => $this->meta_description,
        ];
    }

    /**
     * Calcula score de otimização SEO (0-100)
     */
    public function calculateSeoScore(): float
    {
        $score = 0;
        $maxScore = 100;

        // Title (20 pontos)
        if ($this->title && strlen($this->title) >= 30 && strlen($this->title) <= 60) {
            $score += 20;
        } elseif ($this->title) {
            $score += 10;
        }

        // Meta description (20 pontos)
        if ($this->meta_description && strlen($this->meta_description) >= 120 && strlen($this->meta_description) <= 160) {
            $score += 20;
        } elseif ($this->meta_description) {
            $score += 10;
        }

        // H1 (15 pontos)
        if ($this->h1 && strlen($this->h1) >= 20) {
            $score += 15;
        } elseif ($this->h1) {
            $score += 8;
        }

        // Primary keyword (15 pontos)
        if ($this->primary_keyword) {
            $score += 15;
        }

        // Secondary keywords (10 pontos)
        $keywordCount = count($this->secondary_keywords ?? []);
        if ($keywordCount >= 3 && $keywordCount <= 5) {
            $score += 10;
        } elseif ($keywordCount > 0) {
            $score += 5;
        }

        // Schema.org (10 pontos)
        if (!empty($this->schema_org)) {
            $score += 10;
        }

        // Canonical URL (5 pontos)
        if ($this->canonical_url) {
            $score += 5;
        }

        // Word count (5 pontos)
        if ($this->word_count && $this->word_count >= 300) {
            $score += 5;
        }

        return round(($score / $maxScore) * 100, 2);
    }

    /**
     * Valida se o SEO está completo
     */
    public function isComplete(): bool
    {
        return !empty($this->title) &&
               !empty($this->h1) &&
               !empty($this->meta_description) &&
               !empty($this->primary_keyword);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->title ?? $this->slug ?? 'Guide SEO';
    }
}
