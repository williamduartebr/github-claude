<?php

namespace Src\ContentGeneration\TirePressureGuide\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * TirePressureArticle Model - ATUALIZADA PARA FASE 2
 * 
 * NOVOS CAMPOS ADICIONADOS:
 * - Controle de processamento em lotes
 * - Status de refinamento por seção
 * - Flags para rate limiting
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

        // Monitoramento e tracking
        'last_validated_at' => 'datetime',
        'processed_at' => 'datetime',
        'claude_last_enhanced_at' => 'datetime',
        'sections_last_refined_at' => 'datetime',

        // Blog sync
        'blog_id' => 'integer',
        'blog_synced' => 'boolean',
        'blog_modified_time' => 'datetime',
        'blog_published_time' => 'datetime',

        // Flags de controle
        'quality_checked' => 'boolean',
        'is_premium' => 'boolean',
        'has_tpms' => 'boolean',
        'is_motorcycle' => 'boolean',

        // NOVOS CAMPOS PARA FASE 2
        'refinement_batch_id' => 'string',
        'refinement_batch_position' => 'integer',
        'refinement_started_at' => 'datetime',
        'refinement_completed_at' => 'datetime',
        'refinement_attempts' => 'integer',
        'refinement_errors' => 'array',
        'sections_refinement_version' => 'string',
        'refinement_status' => 'string', // pending, processing, completed, failed
        'refinement_priority' => 'integer',
        'claude_api_model' => 'string',
        'claude_api_tokens_used' => 'integer',
        'claude_api_cost_estimate' => 'float',
        
        // Controle de qualidade por seção
        'sections_quality_scores' => 'array',
        'sections_word_counts' => 'array',
        'sections_validation_status' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'processed_at',
        'last_validated_at',
        'last_backup_at',
        'claude_last_enhanced_at',
        'sections_last_refined_at',
        'blog_modified_time',
        'blog_published_time',
        'vehicle_data_corrected_at',
        'refinement_started_at',
        'refinement_completed_at',
    ];

    // =======================================================================
    // SCOPES PARA FASE 2
    // =======================================================================

    /**
     * Artigos prontos para refinamento (vehicle_data corrigido)
     */
    public function scopeReadyForRefinement($query)
    {
        return $query->where('vehicle_data_version', 'v3.1')
                    ->where('generation_status', 'generated')
                    ->whereNull('sections_refinement_version');
    }

    /**
     * Artigos em um batch específico
     */
    public function scopeInBatch($query, string $batchId)
    {
        return $query->where('refinement_batch_id', $batchId)
                    ->orderBy('refinement_batch_position', 'asc');
    }

    /**
     * Artigos pendentes de refinamento
     */
    public function scopePendingRefinement($query)
    {
        return $query->where('refinement_status', 'pending')
                    ->orWhereNull('refinement_status');
    }

    /**
     * Artigos com falha no refinamento
     */
    public function scopeFailedRefinement($query)
    {
        return $query->where('refinement_status', 'failed')
                    ->where('refinement_attempts', '<', 3);
    }

    /**
     * Filtrar por template type
     */
    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template_type', $template);
    }

    /**
     * Filtrar por marca
     */
    public function scopeByMake($query, string $make)
    {
        return $query->where('vehicle_data.make', $make);
    }

    // =======================================================================
    // MÉTODOS PARA CONTROLE DE REFINAMENTO
    // =======================================================================

    /**
     * Marcar artigo como em processamento
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'refinement_status' => 'processing',
            'refinement_started_at' => now(),
            'refinement_attempts' => ($this->refinement_attempts ?? 0) + 1
        ]);
    }

    /**
     * Marcar artigo como refinado com sucesso
     */
    public function markAsRefined(array $apiMetrics = []): void
    {
        $updateData = [
            'refinement_status' => 'completed',
            'refinement_completed_at' => now(),
            'sections_refinement_version' => 'v2.0',
            'sections_last_refined_at' => now(),
            'claude_api_model' => 'claude-3-5-sonnet-20240620'
        ];

        if (!empty($apiMetrics)) {
            $updateData['claude_api_tokens_used'] = $apiMetrics['tokens_used'] ?? 0;
            $updateData['claude_api_cost_estimate'] = $apiMetrics['cost_estimate'] ?? 0;
        }

        $this->update($updateData);
    }

    /**
     * Marcar artigo com falha no refinamento
     */
    public function markAsFailedRefinement(string $error): void
    {
        $errors = $this->refinement_errors ?? [];
        $errors[] = [
            'timestamp' => now()->toISOString(),
            'error' => $error,
            'attempt' => $this->refinement_attempts ?? 1
        ];

        $this->update([
            'refinement_status' => 'failed',
            'refinement_errors' => $errors
        ]);
    }

    /**
     * Atualizar todas as 6 seções de uma vez
     */
    public function updateAllSections(array $sections): bool
    {
        try {
            $updateData = [];
            
            // Mapear seções com validação
            $sectionKeys = [
                'intro', 
                'pressure_table', 
                'how_to_calibrate', 
                'middle_content', 
                'faq', 
                'conclusion'
            ];

            foreach ($sectionKeys as $key) {
                if (isset($sections[$key])) {
                    $updateData["sections_{$key}"] = $sections[$key];
                }
            }

            // Atualizar status das seções
            $sectionsStatus = [];
            foreach ($sectionKeys as $key) {
                $sectionsStatus[$key] = isset($sections[$key]) ? 'completed' : 'pending';
            }
            $updateData['sections_status'] = $sectionsStatus;

            // Calcular word counts
            $wordCounts = [];
            foreach ($sectionKeys as $key) {
                if (isset($sections[$key])) {
                    $content = is_array($sections[$key]) ? 
                              json_encode($sections[$key]) : 
                              (string)$sections[$key];
                    $wordCounts[$key] = str_word_count($content);
                }
            }
            $updateData['sections_word_counts'] = $wordCounts;

            // Atualizar timestamp
            $updateData['sections_last_refined_at'] = now();

            return $this->update($updateData);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar seções", [
                'article_id' => $this->_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar se todas as seções foram refinadas
     */
    public function isFullyRefined(): bool
    {
        $requiredSections = [
            'sections_intro',
            'sections_pressure_table',
            'sections_how_to_calibrate',
            'sections_middle_content',
            'sections_faq',
            'sections_conclusion'
        ];

        foreach ($requiredSections as $section) {
            if (empty($this->$section)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calcular score de qualidade das seções
     */
    public function calculateSectionsQualityScore(): float
    {
        $scores = [];
        $weights = [
            'intro' => 0.15,
            'pressure_table' => 0.20,
            'how_to_calibrate' => 0.25,
            'middle_content' => 0.20,
            'faq' => 0.10,
            'conclusion' => 0.10
        ];

        foreach ($weights as $section => $weight) {
            $sectionKey = "sections_{$section}";
            if (!empty($this->$sectionKey)) {
                // Score baseado em presença e tamanho
                $content = is_array($this->$sectionKey) ? 
                          json_encode($this->$sectionKey) : 
                          (string)$this->$sectionKey;
                
                $wordCount = str_word_count($content);
                $score = min(10, $wordCount / 50); // 500 palavras = score 10
                $scores[$section] = $score * $weight;
            } else {
                $scores[$section] = 0;
            }
        }

        $totalScore = array_sum($scores) * 10; // Escala 0-10
        
        // Salvar scores individuais
        $this->update([
            'sections_quality_scores' => $scores,
            'sections_scores.overall' => round($totalScore, 2)
        ]);

        return $totalScore;
    }

    // =======================================================================
    // MÉTODOS DE BATCH
    // =======================================================================

    /**
     * Criar novo batch de refinamento
     */
    public static function createRefinementBatch(
        int $size = 100, 
        array $filters = []
    ): ?string {
        try {
            $batchId = 'ref_batch_' . now()->format('Ymd_His') . '_' . \Str::random(6);
            
            // Buscar artigos elegíveis
            $query = self::readyForRefinement()
                        ->whereNull('refinement_batch_id');

            // Aplicar filtros
            if (!empty($filters['template'])) {
                $query->byTemplate($filters['template']);
            }
            if (!empty($filters['make'])) {
                $query->byMake($filters['make']);
            }
            if (!empty($filters['priority'])) {
                $query->where('refinement_priority', '>=', $filters['priority']);
            }

            // Pegar artigos e atribuir ao batch
            $articles = $query->limit($size)->get();
            
            if ($articles->isEmpty()) {
                return null;
            }

            foreach ($articles as $index => $article) {
                $article->update([
                    'refinement_batch_id' => $batchId,
                    'refinement_batch_position' => $index + 1,
                    'refinement_status' => 'pending'
                ]);
            }

            Log::info("Batch de refinamento criado", [
                'batch_id' => $batchId,
                'size' => $articles->count()
            ]);

            return $batchId;

        } catch (\Exception $e) {
            Log::error("Erro ao criar batch", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Obter próximo artigo do batch para processar
     */
    public static function getNextFromBatch(string $batchId): ?self
    {
        return self::inBatch($batchId)
                   ->pendingRefinement()
                   ->first();
    }

    /**
     * Obter estatísticas do batch
     */
    public static function getBatchStats(string $batchId): array
    {
        $total = self::where('refinement_batch_id', $batchId)->count();
        $completed = self::where('refinement_batch_id', $batchId)
                         ->where('refinement_status', 'completed')
                         ->count();
        $failed = self::where('refinement_batch_id', $batchId)
                     ->where('refinement_status', 'failed')
                     ->count();
        $processing = self::where('refinement_batch_id', $batchId)
                          ->where('refinement_status', 'processing')
                          ->count();
        $pending = self::where('refinement_batch_id', $batchId)
                       ->where('refinement_status', 'pending')
                       ->count();

        return [
            'batch_id' => $batchId,
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'processing' => $processing,
            'pending' => $pending,
            'progress_percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'estimated_cost' => $total * 0.04, // ~$0.04 por artigo
            'estimated_tokens' => $total * 4000 // ~4000 tokens por artigo
        ];
    }
}