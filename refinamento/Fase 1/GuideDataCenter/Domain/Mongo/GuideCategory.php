<?php

namespace Src\GuideDataCenter\Domain\Mongo;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model GuideCategory - Categorias de guias
 * 
 * Organiza os guias em categorias temáticas como:
 * Óleo, Pneus, Calibragem, Revisões, etc.
 * 
 * @property string $_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property int $order
 * @property bool $active
 * @property array $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class GuideCategory extends Model
{
    /**
     * Conexão com MongoDB
     */
    protected $connection = 'mongodb';

    /**
     * Nome da collection no MongoDB
     */
    protected $table = 'guide_categories';

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
     * Cast de tipos para os campos
     */
    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'icon' => 'string',
        'order' => 'integer',
        'active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores padrão
     */
    protected $attributes = [
        'active' => true,
        'order' => 0,
        'metadata' => [],
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Guias desta categoria
     */
    public function guides()
    {
        return $this->hasMany(Guide::class, 'guide_category_id', '_id');
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope para categorias ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')
                     ->orderBy('name', 'asc');
    }

    /**
     * Scope por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para busca
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ====================================================================
    // MÉTODOS AUXILIARES
    // ====================================================================

    /**
     * Conta quantos guias esta categoria possui
     */
    public function getGuidesCount(): int
    {
        return $this->guides()->count();
    }

    /**
     * Retorna o ícone ou um padrão
     */
    public function getIconAttribute($value): string
    {
        return $value ?? 'fa-book';
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
