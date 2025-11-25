<?php

declare(strict_types=1);

namespace Src\GuideDataCenter\Domain\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Model GuideCluster - Clusters de links internos entre guias
 *
 * Gerencia a malha de links internos que conectam guias relacionados
 * criando clusters temáticos e de navegação.
 *
 * @property string $_id
 * @property string $guide_id
 * @property string $make_slug
 * @property string $model_slug
 * @property string|null $year_range
 * @property string $cluster_type (super, category, related, year, generation)
 * @property array $links
 * @property array $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class GuideCluster extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'guide_clusters';

    protected $guarded = ['_id'];

    /**
     * Cast de tipos
     *
     * NOTA: Campos array (links, metadata) não precisam de cast explícito
     * no MongoDB - o driver laravel-mongodb já lida nativamente com arrays BSON.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ====================================================================
    // CONSTANTS - TIPOS DE CLUSTER
    // ====================================================================

    public const TYPE_SUPER = 'super';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_RELATED = 'related';
    public const TYPE_YEAR = 'year';
    public const TYPE_GENERATION = 'generation';
    public const TYPE_MOTOR = 'motor';
    public const TYPE_VERSION = 'version';

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Guia ao qual este cluster pertence
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
     * Scope por marca
     */
    public function scopeByMake($query, string $makeSlug)
    {
        return $query->where('make_slug', $makeSlug);
    }

    /**
     * Scope por modelo
     */
    public function scopeByModel($query, string $modelSlug)
    {
        return $query->where('model_slug', $modelSlug);
    }

    /**
     * Scope por tipo de cluster
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('cluster_type', $type);
    }

    /**
     * Scope para super clusters
     */
    public function scopeSuperClusters($query)
    {
        return $query->where('cluster_type', self::TYPE_SUPER);
    }

    /**
     * Scope por range de anos
     */
    public function scopeByYearRange($query, string $yearRange)
    {
        return $query->where('year_range', $yearRange);
    }

    // ====================================================================
    // MÉTODOS AUXILIARES
    // ====================================================================

    /**
     * Adiciona um link ao cluster
     */
    public function addLink(string $category, string $url, string $title, ?array $extra = []): void
    {
        $links = $this->links ?? [];

        $links[$category] = array_merge([
            'url' => $url,
            'title' => $title,
            'updated_at' => now()->toIso8601String(),
        ], $extra);

        $this->links = $links;
    }

    /**
     * Remove um link do cluster
     */
    public function removeLink(string $category): void
    {
        $links = $this->links ?? [];

        if (isset($links[$category])) {
            unset($links[$category]);
            $this->links = $links;
        }
    }

    /**
     * Retorna todos os links de uma categoria específica
     */
    public function getLinksByCategory(string $category): ?array
    {
        return $this->links[$category] ?? null;
    }

    /**
     * Verifica se possui link de determinada categoria
     */
    public function hasLinkForCategory(string $category): bool
    {
        return isset($this->links[$category]);
    }

    /**
     * Retorna total de links no cluster
     */
    public function getTotalLinks(): int
    {
        return count($this->links ?? []);
    }

    /**
     * Retorna array com todas as categorias de links
     */
    public function getCategories(): array
    {
        return array_keys($this->links ?? []);
    }

    /**
     * Valida se é um tipo de cluster válido
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::getAvailableTypes(), true);
    }

    /**
     * Retorna todos os tipos disponíveis
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_SUPER,
            self::TYPE_CATEGORY,
            self::TYPE_RELATED,
            self::TYPE_YEAR,
            self::TYPE_GENERATION,
            self::TYPE_MOTOR,
            self::TYPE_VERSION,
        ];
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return sprintf(
            'Cluster %s: %s/%s (%d links)',
            $this->cluster_type,
            $this->make_slug,
            $this->model_slug,
            $this->getTotalLinks()
        );
    }
}
