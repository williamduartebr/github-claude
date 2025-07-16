<?php

namespace Src\ContentGeneration\TirePressureGuide\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TirePressureArticle
 * 
 * Model principal para artigos de calibragem de pneus
 * Sistema em duas etapas: Geração inicial + Refinamento Claude
 * 
 * @package Src\ContentGeneration\TirePressureGuide\Domain\Entities
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
        'claude_last_enhanced_at' => 'datetime',
        'processed_at' => 'datetime',
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

        // Conteúdo do artigo (estruturado)
        'title',
        'slug',
        'wordpress_slug', // Padrão: calibragem-pneu-[marca]-[modelo]-[ano]
        'article_content',
        'template_used',

        // SEO e URLs
        'meta_description',
        'seo_keywords',
        'wordpress_url',
        'amp_url',
        'canonical_url',

        // Status de geração (Etapas 1 e 2)
        'generation_status', // pending, generated, claude_enhanced, published

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

    /**
     * Definir índices para MongoDB
     * Executar via migration separada
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
        
        // Índice único para slug WordPress
        $collection->createIndex(['wordpress_slug' => 1], ['unique' => true, 'name' => 'unique_wordpress_slug_tire_pressure']);
        
        // Índices compostos para performance
        $collection->createIndex(['make' => 1, 'model' => 1, 'year' => 1], ['name' => 'vehicle_composite_index']);
        $collection->createIndex(['generation_status' => 1, 'created_at' => 1], ['name' => 'status_created_index']);
        $collection->createIndex(['generation_status' => 1, 'claude_enhancement_count' => 1], ['name' => 'claude_ready_index']);
        
        // Índices para dados aninhados (MongoDB feature)
        $collection->createIndex(['vehicle_data.vehicle_type' => 1]);
        $collection->createIndex(['vehicle_data.main_category' => 1]);
        $collection->createIndex(['vehicle_data.is_motorcycle' => 1]);
    }

    // =======================================================================
    // SCOPES PARA FILTRAGEM (ETAPAS 1 E 2)
    // =======================================================================

    /**
     * Scope: Artigos pendentes para geração inicial (Etapa 1)
     */
    public function scopePendingGeneration($query)
    {
        return $query->where('generation_status', 'pending');
    }

    /**
     * Scope: Artigos já gerados na etapa inicial
     */
    public function scopeGenerated($query)
    {
        return $query->where('generation_status', 'generated');
    }

    /**
     * Scope: Artigos prontos para refinamento Claude (Etapa 2)
     */
    public function scopeReadyForClaude($query)
    {
        return $query->where('generation_status', 'generated')
                    ->where(function($q) {
                        $q->whereNull('claude_enhancement_count')
                          ->orWhere('claude_enhancement_count', '<', 3); // Máximo 3 refinamentos
                    });
    }

    /**
     * Scope: Artigos já refinados pelo Claude
     */
    public function scopeClaudeEnhanced($query)
    {
        return $query->where('generation_status', 'claude_enhanced');
    }

    /**
     * Scope: Artigos publicados
     */
    public function scopePublished($query)
    {
        return $query->where('generation_status', 'published');
    }

    /**
     * Scope: Filtrar por marca
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('make', $make);
    }

    /**
     * Scope: Filtrar por tipo de veículo
     */
    public function scopeByVehicleType($query, string $type)
    {
        return $query->where('vehicle_data.vehicle_type', $type);
    }

    /**
     * Scope: Filtrar por faixa de anos
     */
    public function scopeByYearRange($query, int $yearFrom = null, int $yearTo = null)
    {
        if ($yearFrom) {
            $query->where('year', '>=', $yearFrom);
        }
        
        if ($yearTo) {
            $query->where('year', '<=', $yearTo);
        }
        
        return $query;
    }

    /**
     * Scope: Filtrar por lote
     */
    public function scopeByBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    // =======================================================================
    // ACCESSORS E MUTATORS
    // =======================================================================

    /**
     * Acessor: Verificar se é motocicleta
     */
    public function getIsMotorcycleAttribute(): bool
    {
        return $this->vehicle_data['is_motorcycle'] ?? false;
    }

    /**
     * Acessor: Obter nome completo do veículo
     */
    public function getVehicleFullNameAttribute(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    /**
     * Acessor: Obter pressão para exibição
     */
    public function getPressureDisplayAttribute(): string
    {
        if ($this->is_motorcycle) {
            return "Dianteiro: {$this->pressure_empty_front} PSI / Traseiro: {$this->pressure_empty_rear} PSI";
        }
        
        return "Dianteiros: {$this->pressure_empty_front} PSI / Traseiros: {$this->pressure_empty_rear} PSI";
    }

    /**
     * Acessor: Status de refinamento Claude
     */
    public function getClaudeStatusAttribute(): string
    {
        if ($this->generation_status === 'claude_enhanced') {
            return 'Refinado pelo Claude';
        }
        
        if ($this->generation_status === 'generated' && $this->claude_enhancement_count > 0) {
            return "Parcialmente refinado ({$this->claude_enhancement_count}x)";
        }
        
        if ($this->generation_status === 'generated') {
            return 'Pronto para refinamento';
        }
        
        return 'Aguardando geração';
    }

    /**
     * Mutator: Gerar slug WordPress automaticamente
     */
    public function setWordpressSlugAttribute($value)
    {
        if (!$value && $this->make && $this->model && $this->year) {
            $this->attributes['wordpress_slug'] = $this->generateWordPressSlug();
        } else {
            $this->attributes['wordpress_slug'] = $value;
        }
    }

    // =======================================================================
    // MÉTODOS DE NEGÓCIO
    // =======================================================================

    /**
     * Gerar slug compatível com WordPress
     * Formato: calibragem-pneu-[marca]-[modelo]-[ano]
     */
    public function generateWordPressSlug(): string
    {
        $make = $this->slugify($this->make);
        $model = $this->slugify($this->model);
        $year = $this->year;
        
        return "calibragem-pneu-{$make}-{$model}-{$year}";
    }

    /**
     * Converter string para slug
     */
    private function slugify(string $text): string
    {
        // Remover acentos
        $text = $this->removeAccents($text);
        
        // Converter para minúsculas
        $text = strtolower($text);
        
        // Remover caracteres especiais e substituir por hífen
        $text = preg_replace('/[^a-z0-9\-_]/', '-', $text);
        
        // Remover hífens múltiplos
        $text = preg_replace('/-+/', '-', $text);
        
        // Remover hífens do início e fim
        return trim($text, '-');
    }

    /**
     * Remover acentos
     */
    private function removeAccents(string $text): string
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

    /**
     * Marcar como gerado (Etapa 1 concluída)
     */
    public function markAsGenerated(): void
    {
        $this->generation_status = 'generated';
        $this->processed_at = now();
        $this->save();
    }

    /**
     * Marcar como refinado pelo Claude (Etapa 2 concluída)
     */
    public function markAsClaudeEnhanced(): void
    {
        $this->generation_status = 'claude_enhanced';
        $this->claude_last_enhanced_at = now();
        $this->claude_enhancement_count = ($this->claude_enhancement_count ?? 0) + 1;
        $this->save();
    }

    /**
     * Adicionar refinamento Claude ao histórico
     */
    public function addClaudeEnhancement(string $section, string $originalContent, string $enhancedContent, array $metadata = []): void
    {
        $enhancements = $this->claude_enhancements ?? [];
        
        $enhancements[] = [
            'timestamp' => now()->toISOString(),
            'section' => $section,
            'original_content' => $originalContent,
            'enhanced_content' => $enhancedContent,
            'metadata' => $metadata
        ];
        
        $this->claude_enhancements = $enhancements;
        $this->save();
    }

    /**
     * Verificar se pode ser refinado pelo Claude
     */
    public function canBeEnhancedByClaude(): bool
    {
        return $this->generation_status === 'generated' && 
               ($this->claude_enhancement_count ?? 0) < 3;
    }

    /**
     * Obter URL completa do WordPress
     */
    public function getWordPressUrl(): string
    {
        return $this->wordpress_url ?? "https://mercadoveiculos.com/info/{$this->wordpress_slug}/";
    }

    /**
     * Obter URL canônica
     */
    public function getCanonicalUrl(): string
    {
        return $this->canonical_url ?? $this->getWordPressUrl();
    }

    // =======================================================================
    // ESTATÍSTICAS E RELATÓRIOS
    // =======================================================================

    /**
     * Estatísticas gerais do sistema
     */
    public static function getGenerationStatistics(): array
    {
        $total = static::count();
        $pending = static::where('generation_status', 'pending')->count();
        $generated = static::where('generation_status', 'generated')->count();
        $enhanced = static::where('generation_status', 'claude_enhanced')->count();
        $published = static::where('generation_status', 'published')->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'generated' => $generated,
            'claude_enhanced' => $enhanced,
            'published' => $published,
            'ready_for_claude' => static::readyForClaude()->count()
        ];
    }

    /**
     * Relatório por marca
     */
    public static function getStatisticsByMake(): Collection
    {
        return static::raw(function($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$make',
                        'total' => ['$sum' => 1],
                        'generated' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$generation_status', 'generated']], 
                                    1, 
                                    0
                                ]
                            ]
                        ],
                        'claude_enhanced' => [
                            '$sum' => [
                                '$cond' => [
                                    ['$eq' => ['$generation_status', 'claude_enhanced']], 
                                    1, 
                                    0
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    '$sort' => ['total' => -1]
                ]
            ]);
        });
    }

    /**
     * toString para debug
     */
    public function __toString(): string
    {
        return "TirePressureArticle[{$this->vehicle_full_name}] - Status: {$this->generation_status}";
    }
}