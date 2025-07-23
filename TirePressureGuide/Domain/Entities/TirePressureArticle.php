<?php

namespace Src\ContentGeneration\TirePressureGuide\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * TirePressureArticle Model - VERSÃO SIMPLES PARA CORREÇÃO
 * 
 * AJUSTES APLICADOS:
 * - Adicionados campos para controle de correção do vehicle_data
 * - Métodos simples para verificação e marcação
 * - Scopes básicos para queries
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
        
        // Cross-linking e relacionamentos
        'sibling_article_id' => 'string',
        'cross_links' => 'array',
        'related_articles' => 'array',
        
        // Métricas avançadas
        'performance_metrics' => 'array',
        'quality_metrics' => 'array',
        'validation_results' => 'array',
        
        // Sistema de backup e versioning
        'content_versions' => 'array',
        'backup_data' => 'array',
        'last_backup_at' => 'datetime',
        
        // ✅ NOVOS CAMPOS PARA CORREÇÃO DO VEHICLE_DATA
        'vehicle_data_corrected_at' => 'datetime',
        'vehicle_data_version' => 'string',
        
        // Timestamps e controle
        'claude_last_enhanced_at' => 'datetime',
        'sections_last_refined_at' => 'datetime',
        'processed_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'quality_checked' => 'boolean',
        'pressure_light_front' => 'decimal:1',
        'pressure_light_rear' => 'decimal:1',
        'pressure_spare' => 'decimal:1',
        'category' => 'string',
        'batch_id' => 'string',
        'generation_status' => 'string',
        'template_type' => 'string',
        'blog_id' => 'integer',
        'blog_status' => 'string',
        'blog_published_time' => 'datetime',
        'blog_modified_time' => 'datetime',
        'blog_synced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // =======================================================================
    // MÉTODOS SIMPLES PARA CORREÇÃO DO VEHICLE_DATA
    // =======================================================================

    /**
     * Verificar se precisa correção
     */
    public function needsVehicleDataCorrection(): bool
    {
        return empty($this->vehicle_data_version) || $this->vehicle_data_version !== 'v2.1';
    }

    /**
     * Marcar como corrigido
     */
    public function markVehicleDataAsCorrected(array $correctedData): void
    {
        $this->vehicle_data = $correctedData;
        $this->vehicle_data_corrected_at = now();
        $this->vehicle_data_version = 'v2.1';
        
        // Atualizar campos derivados
        if (isset($correctedData['pressure_light_front'])) {
            $this->pressure_light_front = (string) $correctedData['pressure_light_front'];
        }
        
        if (isset($correctedData['pressure_light_rear'])) {
            $this->pressure_light_rear = (string) $correctedData['pressure_light_rear'];
        }
        
        if (isset($correctedData['pressure_spare'])) {
            $this->pressure_spare = (string) $correctedData['pressure_spare'];
        }
        
        $this->save();
    }

    // =======================================================================
    // SCOPES SIMPLES PARA QUERIES
    // =======================================================================

    /**
     * Artigos que precisam de correção
     */
    public function scopeNeedsVehicleDataCorrection($query)
    {
        return $query->where(function($q) {
            $q->whereNull('vehicle_data_version')
              ->orWhere('vehicle_data_version', '!=', 'v2.1');
        });
    }

    /**
     * Buscar por veículo específico
     */
    public function scopeByVehicle($query, string $make, string $model, int $year)
    {
        return $query->where('vehicle_data.make', $make)
                     ->where('vehicle_data.model', $model)
                     ->where('vehicle_data.year', $year);
    }

    // =======================================================================
    // MÉTODOS EXISTENTES MANTIDOS (SEM ALTERAÇÃO)
    // =======================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            $article->ensureRequiredFields();
            $article->autoGenerateSlugAndTitle();
            $article->initializeQualityMetrics();
        });

        static::saving(function ($article) {
            $article->validateTemplateType();
            $article->updateQualityMetrics();
        });

        static::updating(function ($article) {
            if ($article->isDirty(['article_content', 'sections_refined'])) {
                $article->createContentBackup();
            }
        });

        static::deleting(function ($article) {
            $article->clearRelatedCache();
        });
    }

    /**
     * Garantir campos obrigatórios
     */
    protected function ensureRequiredFields(): void
    {
        if (empty($this->template_type)) {
            $this->template_type = 'ideal';
        }

        if (empty($this->generation_status)) {
            $this->generation_status = 'pending';
        }

        if (empty($this->quality_checked)) {
            $this->quality_checked = false;
        }

        if (empty($this->sections_status)) {
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
     * Auto-gerar slug e título
     */
    protected function autoGenerateSlugAndTitle(): void
    {
        if (empty($this->slug) && !empty($this->vehicle_data)) {
            $vehicleData = $this->vehicle_data;
            $make = strtolower($vehicleData['make'] ?? '');
            $model = strtolower($vehicleData['model'] ?? '');
            $year = $vehicleData['year'] ?? '';

            if ($this->template_type === 'calibration') {
                $this->slug = "como-calibrar-pneus-{$make}-{$model}-{$year}";
                $this->title = "Como Calibrar Pneus {$vehicleData['make']} {$vehicleData['model']} {$year} - Passo a Passo";
            } else {
                $this->slug = "pressao-pneus-{$make}-{$model}-{$year}";
                $this->title = "Pressão Ideal dos Pneus {$vehicleData['make']} {$vehicleData['model']} {$year} - Guia Completo";
            }

            $this->slug = $this->slugify($this->slug);
        }
    }

    /**
     * Inicializar métricas de qualidade
     */
    protected function initializeQualityMetrics(): void
    {
        if (empty($this->quality_metrics)) {
            $this->quality_metrics = [
                'content_completeness' => 0,
                'structure_integrity' => 0,
                'seo_optimization' => 0,
                'template_compliance' => 0,
                'cross_linking_score' => 0,
                'overall_score' => 0,
                'calculated_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Validar tipo de template
     */
    protected function validateTemplateType(): void
    {
        $validTypes = ['ideal', 'calibration'];
        
        if (!in_array($this->template_type, $validTypes)) {
            $this->template_type = 'ideal';
        }
    }

    /**
     * Atualizar métricas de qualidade
     */
    protected function updateQualityMetrics(): void
    {
        if ($this->isDirty(['sections_intro', 'sections_pressure_table', 'sections_how_to_calibrate', 
                           'sections_middle_content', 'sections_faq', 'sections_conclusion']) 
            && $this->quality_checked !== false) {
            $this->calculateQualityMetrics();
        }
    }

    /**
     * Calcular métricas de qualidade
     */
    protected function calculateQualityMetrics(): void
    {
        $metrics = $this->quality_metrics ?? [];
        
        // Calcular completeness baseado nas seções
        $totalSections = 6;
        $completedSections = 0;
        
        $sections = ['sections_intro', 'sections_pressure_table', 'sections_how_to_calibrate', 
                    'sections_middle_content', 'sections_faq', 'sections_conclusion'];
        
        foreach ($sections as $section) {
            if (!empty($this->$section)) {
                $completedSections++;
            }
        }
        
        $metrics['content_completeness'] = round(($completedSections / $totalSections) * 100, 2);
        $metrics['calculated_at'] = now()->toISOString();
        
        $this->quality_metrics = $metrics;
    }

    /**
     * Criar backup do conteúdo
     */
    protected function createContentBackup(): void
    {
        $backup = [
            'timestamp' => now()->toISOString(),
            'article_content' => $this->getOriginal('article_content'),
            'sections_refined' => $this->getOriginal('sections_refined'),
            'content_score' => $this->content_score ?? null,
            'quality_metrics' => $this->getOriginal('quality_metrics'),
            'backup_reason' => 'content_update'
        ];

        $backups = $this->backup_data ?? [];
        $backups[] = $backup;

        // Manter apenas os últimos 5 backups
        if (count($backups) > 5) {
            $backups = array_slice($backups, -5);
        }

        $this->backup_data = $backups;
        $this->last_backup_at = now();
    }

    /**
     * Limpar cache relacionado
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
     * Criar slug otimizado
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
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n'
        ];

        return strtr($text, $unwanted);
    }

    // =======================================================================
    // MÉTODOS ESTÁTICOS MANTIDOS
    // =======================================================================

    /**
     * Criar índices MongoDB
     */
    public static function createIndexes(): void
    {
        $collection = (new static)->getCollection();
        
        // Índices básicos
        $collection->createIndex(['generation_status' => 1]);
        $collection->createIndex(['template_type' => 1]);
        $collection->createIndex(['batch_id' => 1]);
        
        // ✅ NOVOS ÍNDICES PARA CORREÇÃO
        $collection->createIndex(['vehicle_data_version' => 1]);
        $collection->createIndex(['vehicle_data_corrected_at' => 1]);
        
        // Índice composto para busca de veículos
        $collection->createIndex([
            'vehicle_data.make' => 1,
            'vehicle_data.model' => 1,
            'vehicle_data.year' => 1
        ]);
        
        // Índice para correções pendentes
        $collection->createIndex([
            'vehicle_data_version' => 1,
            'created_at' => 1
        ]);
    }

    /**
     * Marcar como gerado
     */
    public function markAsGenerated(): void
    {
        $this->generation_status = 'generated';
        $this->processed_at = now();
        
        if (!$this->performance_metrics) {
            $this->performance_metrics = [
                'generation_completed_at' => now()->toISOString(),
                'template_type' => $this->template_type
            ];
        }
        
        $this->save();
    }
}