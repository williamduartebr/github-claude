<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Domain\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Model Guide - Representa um guia completo de veículo
 *
 * Responsável por armazenar guias de manutenção, especificações técnicas
 * e informações detalhadas sobre veículos específicos.
 *
 * @property string $_id
 * @property string $guide_category_id
 * @property int|null $vehicle_make_id ✅ ADICIONADO
 * @property int|null $vehicle_model_id ✅ ADICIONADO
 * @property int|null $vehicle_version_id ✅ ADICIONADO
 * @property string $make
 * @property string $make_slug
 * @property string|null $make_logo_url
 * @property string $model
 * @property string $model_slug
 * @property string|null $version
 * @property string|null $version_slug ✅ ADICIONADO
 * @property string|null $motor
 * @property string|null $fuel
 * @property int|null $year_start
 * @property int|null $year_end
 * @property string $template
 * @property string $slug
 * @property string $url
 * @property array $payload
 * @property array $seo
 * @property array $links_internal
 * @property array $links_related
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Guide extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'guides';

    protected $guarded = ['_id'];

    /**
     * Cast de tipos para os campos
     *
     * NOTA: Campos array (payload, seo, links_internal, links_related)
     * não precisam de cast explícito no MongoDB - o driver laravel-mongodb
     * já lida nativamente com arrays BSON.
     */
    protected $casts = [
        // ✅ ADICIONADO - FKs para MySQL
        'vehicle_make_id' => 'integer',
        'vehicle_model_id' => 'integer',
        'vehicle_version_id' => 'integer',

        // Existentes
        'year_start' => 'integer',
        'year_end' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Relacionamento com categoria do guia
     */
    public function category()
    {
        return $this->belongsTo(GuideCategory::class, 'guide_category_id', '_id');
    }

    /**
     * Relacionamento com SEO
     */
    public function guideSeo()
    {
        return $this->hasOne(GuideSeo::class, 'guide_id', '_id');
    }

    /**
     * Relacionamento com clusters
     */
    public function clusters()
    {
        return $this->hasMany(GuideCluster::class, 'guide_id', '_id');
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope para filtrar por marca
     */
    public function scopeByMake($query, string $makeSlug)
    {
        return $query->where('make_slug', $makeSlug);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeByModel($query, string $modelSlug)
    {
        return $query->where('model_slug', $modelSlug);
    }

    /**
     * Scope para filtrar por categoria
     */
    public function scopeByCategory($query, string $categoryId)
    {
        return $query->where('guide_category_id', $categoryId);
    }

    /**
     * Scope para filtrar por ano
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where(function ($q) use ($year) {
            $q->where('year_start', '<=', $year)
                ->where(function ($q2) use ($year) {
                    $q2->where('year_end', '>=', $year)
                        ->orWhereNull('year_end');
                });
        });
    }

    /**
     * Scope para filtrar por template
     */
    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }

    /**
     * Scope para filtrar por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para buscar por termo
     */
    public function scopeSearch($query, string $term)
    {
        $term = strtolower($term);

        return $query->where(function ($q) use ($term) {
            $q->where('make', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('version', 'like', "%{$term}%")
                ->orWhere('payload.title', 'like', "%{$term}%");
        });
    }

    /**
     * Scope para guias publicados/ativos
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('slug')
            ->whereNotNull('url');
    }

    /**
     * ✅ ADICIONADO - Scope para filtrar por vehicle_make_id
     */
    public function scopeByVehicleMakeId($query, int $makeId)
    {
        return $query->where('vehicle_make_id', $makeId);
    }

    /**
     * ✅ ADICIONADO - Scope para filtrar por vehicle_model_id
     */
    public function scopeByVehicleModelId($query, int $modelId)
    {
        return $query->where('vehicle_model_id', $modelId);
    }

    /**
     * ✅ ADICIONADO - Scope para filtrar por vehicle_version_id
     */
    public function scopeByVehicleVersionId($query, int $versionId)
    {
        return $query->where('vehicle_version_id', $versionId);
    }

    // ====================================================================
    // ACCESSORS
    // ====================================================================

    /**
     * Retorna o título completo do guia
     */
    public function getFullTitleAttribute(): string
    {
        $parts = array_filter([
            $this->payload['title'] ?? null,
            $this->make,
            $this->model,
            $this->version,
            $this->year_range_text,
        ]);

        return implode(' - ', $parts);
    }

    /**
     * Retorna o range de anos formatado
     */
    public function getYearRangeTextAttribute(): ?string
    {
        if (! $this->year_start) {
            return null;
        }

        if (! $this->year_end) {
            return (string) $this->year_start . '+';
        }

        if ($this->year_start === $this->year_end) {
            return (string) $this->year_start;
        }

        return $this->year_start . '-' . $this->year_end;
    }

    // ====================================================================
    // MÉTODOS AUXILIARES
    // ====================================================================

    /**
     * Verifica se o guia aplica-se a um ano específico
     */
    public function appliesForYear(int $year): bool
    {
        if (! $this->year_start) {
            return true;
        }

        if ($year < $this->year_start) {
            return false;
        }

        if ($this->year_end && $year > $this->year_end) {
            return false;
        }

        return true;
    }

    /**
     * Retorna a URL completa do guia
     */
    public function getFullUrl(): string
    {
        return $this->url ?? '';
    }

    /**
     * Adiciona um link interno ao cluster
     */
    public function addInternalLink(string $type, string $url, string $title): void
    {
        $links = $this->links_internal ?? [];

        $links[$type] = [
            'url' => $url,
            'title' => $title,
            'updated_at' => now()->toIso8601String(),
        ];

        $this->links_internal = $links;
    }

    /**
     * Adiciona um link relacionado
     */
    public function addRelatedLink(string $url, string $title, ?string $description = null): void
    {
        $links = $this->links_related ?? [];

        $links[] = [
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'added_at' => now()->toIso8601String(),
        ];

        $this->links_related = $links;
    }

    /**
     * Retorna string para exibição
     */
    public function __toString(): string
    {
        return $this->full_title ?? $this->slug ?? 'Guide';
    }
}
