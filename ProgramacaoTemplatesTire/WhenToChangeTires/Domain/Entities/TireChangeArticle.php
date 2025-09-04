<?php

namespace Src\ContentGeneration\WhenToChangeTires\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;

class TireChangeArticle extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'tire_change_articles';
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
        // Dados do veículo
        'make',
        'model',
        'year',
        'tire_size',
        'vehicle_data',

        // Conteúdo do artigo
        'title',
        'slug',
        'article_content',
        'template_used',

        // SEO
        'meta_description',
        'seo_keywords',
        'wordpress_url',
        'amp_url',
        'canonical_url',

        // Status e controle
        'generation_status',

        // Claude API (Etapa 2)
        'claude_enhancements',
        'claude_last_enhanced_at',
        'claude_enhancement_count',

        // Dados técnicos dos pneus
        'pressure_empty_front',
        'pressure_empty_rear',
        'pressure_light_front',
        'pressure_light_rear',
        'pressure_max_front',
        'pressure_max_rear',
        'pressure_spare',

        // Classificações
        'category',
        'recommended_oil',

        // Qualidade
        'quality_checked',
        'quality_issues',
        'content_score',

        // Controle de lotes
        'batch_id',
        'processed_at',

        // Campos do blog
        'blog_id',
        'blog_status',
        'blog_published_time',
        'blog_modified_time',
        'blog_synced',

        // Timestamps
        'created_at',
        'updated_at'
    ];

    // Scopes para filtragem
    public function scopePendingGeneration($query)
    {
        return $query->where('generation_status', 'pending');
    }

    public function scopeGenerated($query)
    {
        return $query->where('generation_status', 'generated');
    }

    public function scopeReadyForClaude($query)
    {
        return $query->where('generation_status', 'generated');
    }

    public function scopeClaudeEnhanced($query)
    {
        return $query->where('generation_status', 'claude_enhanced');
    }

    public function scopeByBatch($query, string $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeByVehicle($query, string $make, string $model, int $year)
    {
        return $query->where('make', $make)
            ->where('model', $model)
            ->where('year', $year);
    }

    public function scopeByMake($query, string $make)
    {
        return $query->where('make', $make);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByVehicleType($query, string $vehicleType)
    {
        return $query->where('vehicle_data.vehicle_type', $vehicleType);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeHighQuality($query)
    {
        return $query->where('content_score', '>=', 8.0);
    }

    public function scopeNeedsImprovement($query)
    {
        return $query->where('content_score', '<', 6.0);
    }

    // Métodos de Status
    public function markAsGenerated(): void
    {
        $this->update([
            'generation_status' => 'generated',
            'processed_at' => now()
        ]);
    }

    public function markAsClaudeEnhanced(array $enhancementData = []): void
    {
        $enhancements = $this->claude_enhancements ?? [];
        $enhancements[] = array_merge($enhancementData, [
            'enhanced_at' => now()->toISOString()
        ]);

        $this->update([
            'generation_status' => 'claude_enhanced',
            'claude_enhancements' => $enhancements,
            'claude_last_enhanced_at' => now(),
            'claude_enhancement_count' => ($this->claude_enhancement_count ?? 0) + 1
        ]);
    }

    public function markAsReadyForTransfer(): void
    {
        $this->update(['generation_status' => 'ready_for_transfer']);
    }

    public function markAsTransferred(): void
    {
        $this->update(['generation_status' => 'transferred']);
    }

    public function markAsPublished(): void
    {
        $this->update(['generation_status' => 'published']);
    }

    public function markAsError(array $errorDetails = []): void
    {
        $issues = $this->quality_issues ?? [];
        $issues[] = array_merge($errorDetails, [
            'error_at' => now()->toISOString()
        ]);

        $this->update([
            'generation_status' => 'error',
            'quality_issues' => $issues
        ]);
    }

    // Getters e Helpers
    public function getVehicleIdentifier(): string
    {
        return "{$this->make} {$this->model} {$this->year}";
    }

    public function getVehicleFullNameAttribute(): string
    {
        return $this->getVehicleIdentifier();
    }

    public function getWordPressSlug(): string
    {
        return "quando-trocar-pneus-{$this->slug}";
    }

    public function canBeEnhancedByClaude(): bool
    {
        return in_array($this->generation_status, ['generated', 'claude_enhanced'])
            && ($this->claude_enhancement_count ?? 0) < 5; // Máximo 5 refinamentos
    }

    public function hasValidContent(): bool
    {
        return !empty($this->article_content)
            && !empty($this->title)
            && !empty($this->wordpress_url);
    }

    public function getContentWordCount(): int
    {
        if (empty($this->article_content)) {
            return 0;
        }

        $content = $this->article_content;
        $wordCount = 0;

        if (is_array($content)) {
            $wordCount = $this->countWordsInArray($content);
        } else {
            $wordCount = str_word_count(strip_tags($content));
        }

        return $wordCount;
    }

    public function getEstimatedReadingTimeAttribute(): int
    {
        $wordCount = $this->getContentWordCount();
        return max(1, ceil($wordCount / 200)); // 200 words per minute
    }

    public function getLastClaudeEnhancement(): ?array
    {
        $enhancements = $this->claude_enhancements ?? [];
        return end($enhancements) ?: null;
    }

    public function getPressureDisplayAttribute(): string
    {
        return "{$this->pressure_empty_front}/{$this->pressure_empty_rear} PSI";
    }

    public function getPressureLoadedDisplayAttribute(): string
    {
        return "{$this->pressure_light_front}/{$this->pressure_light_rear} PSI";
    }

    public function getVehicleTypeAttribute(): string
    {
        $vehicleData = $this->vehicle_data ?? [];
        return $vehicleData['vehicle_type'] ?? 'car';
    }

    public function getMainCategoryAttribute(): string
    {
        $vehicleData = $this->vehicle_data ?? [];
        return $vehicleData['main_category'] ?? $this->category;
    }

    public function isMotorcycle(): bool
    {
        return str_contains($this->category, 'motorcycle');
    }

    public function isElectric(): bool
    {
        return str_contains($this->category, 'electric');
    }

    public function isHybrid(): bool
    {
        return str_contains($this->category, 'hybrid');
    }

    // Método auxiliar para contar palavras em arrays
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

    // Métodos para compatibilidade com estatísticas
    public static function getStatusDistribution(): array
    {
        $result = self::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$generation_status',
                        'count' => ['$sum' => 1]
                    ]
                ]
            ])->toArray();
        });

        // Converter para formato compatível
        $distribution = [];
        foreach ($result as $item) {
            $distribution[$item['_id']] = $item['count'];
        }
        return $distribution;
    }

    public static function getStatsByMake(): array
    {
        return self::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$make',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ],
                [
                    '$limit' => 10
                ]
            ])->toArray();
        });
    }

    public static function getStatsByCategory(): array
    {
        return self::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$category',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['count' => -1]
                ]
            ])->toArray();
        });
    }

    public static function getAverageContentScore(): float
    {
        $result = self::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => null,
                        'avgScore' => ['$avg' => '$content_score']
                    ]
                ]
            ])->toArray();
        });

        return $result[0]['avgScore'] ?? 0.0;
    }
}
