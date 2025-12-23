<?php

namespace Src\GuideDataCenter\Domain\Mongo;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model GuideCategory - Categorias de guias (VERSÃO ATUALIZADA)
 * 
 * Organiza os guias em categorias temáticas como:
 * Óleo, Pneus, Calibragem, Revisões, etc.
 * 
 * NOVOS CAMPOS:
 * - icon_svg: Path SVG do ícone
 * - icon_bg_color: Cor de fundo (Tailwind class)
 * - icon_text_color: Cor do texto (Tailwind class)
 * - seo_info: Informações de SEO (JSON)
 * - info_sections: Seções informativas (JSON)
 * - display_order: Ordem de exibição
 * 
 * @property string $_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $icon_svg
 * @property string|null $icon_bg_color
 * @property string|null $icon_text_color
 * @property int $order
 * @property int $display_order
 * @property bool $is_active
 * @property array|null $seo_info
 * @property array|null $info_sections
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
        'singular_name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'icon' => 'string',
        'icon_svg' => 'string',
        'icon_bg_color' => 'string',
        'icon_text_color' => 'string',
        'order' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'seo_info' => 'array',
        'info_sections' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores padrão
     */
    protected $attributes = [
        'is_active' => true,
        'order' => 0,
        'display_order' => 0,
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
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')
                     ->orderBy('order', 'asc')
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
     * Retorna informações de SEO
     */
    public function getSeoInfo(): ?array
    {
        return $this->seo_info;
    }

    /**
     * Retorna seções informativas
     */
    public function getInfoSections(): ?array
    {
        return $this->info_sections;
    }

    /**
     * Retorna título SEO
     */
    public function getSeoTitle(): ?string
    {
        return $this->seo_info['title'] ?? null;
    }

    /**
     * Retorna descrição SEO
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo_info['description'] ?? null;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}