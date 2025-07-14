<?php

namespace Src\ArticleGenerator\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ArticleCorrection extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'article_corrections';
    protected $guarded = ['_id'];
    
    /**
     * Atributos que devem ser convertidos em tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'correction_data' => 'array',
        'original_data' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status possíveis da correção
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';          // Novo status para casos onde não há necessidade
    const STATUS_NO_CHANGES = 'no_changes';   // Novo status para quando não há mudanças significativas

    /**
     * Tipos de correção disponíveis
     */
    const TYPE_INTRODUCTION_FIX = 'introduction_fix';
    const TYPE_SEO_FIX = 'seo_fix';
    const TYPE_CONTENT_ENHANCEMENT = 'content_enhancement';
    const TYPE_ENTITY_EXTRACTION = 'entity_extraction';
    const TYPE_PUNCTUATION_ANALYSIS = 'punctuation_analysis';
    const TYPE_BULK_PUNCTUATION_FIX = 'bulk_punctuation_fix';

    /**
     * Níveis de prioridade
     */
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NONE = 'none';

    /**
     * Obter correções pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Obter correções por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('correction_type', $type);
    }

    /**
     * Obter correções por slug do artigo
     */
    public function scopeByArticleSlug($query, $slug)
    {
        return $query->where('article_slug', $slug);
    }

    /**
     * Obter apenas análises que detectaram problemas
     */
    public function scopeNeedsCorrection($query)
    {
        return $query->where('correction_type', self::TYPE_PUNCTUATION_ANALYSIS)
            ->where('status', self::STATUS_COMPLETED)
            ->where('correction_data.needs_correction', true);
    }

    /**
     * Obter correções por prioridade
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('correction_data.correction_priority', $priority);
    }

    /**
     * Obter análises recentes (últimos X dias)
     */
    public function scopeRecentAnalysis($query, $days = 3)
    {
        return $query->where('correction_type', self::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Obter correções falhadas antigas
     */
    public function scopeOldFailed($query, $hours = 24)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('created_at', '<', now()->subHours($hours));
    }

    /**
     * Marcar como processando
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now()
        ]);
    }

    /**
     * Marcar como concluído
     */
    public function markAsCompleted($correctedData = null)
    {
        $updateData = [
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now()
        ];

        if ($correctedData) {
            $updateData['correction_data'] = $correctedData;
        }

        $this->update($updateData);
    }

    /**
     * Marcar como concluído sem mudanças
     */
    public function markAsNoChanges($reason = null)
    {
        $updateData = [
            'status' => self::STATUS_NO_CHANGES,
            'processed_at' => now(),
            'correction_data' => [
                'no_changes_needed' => true,
                'reason' => $reason ?? 'Nenhuma mudança significativa necessária',
                'analyzed_at' => now()->toISOString()
            ]
        ];

        $this->update($updateData);
    }

    /**
     * Marcar como pulado
     */
    public function markAsSkipped($reason = null)
    {
        $updateData = [
            'status' => self::STATUS_SKIPPED,
            'processed_at' => now()
        ];

        if ($reason) {
            $updateData['skip_reason'] = $reason;
        }

        $this->update($updateData);
    }

    /**
     * Marcar como falhou
     */
    public function markAsFailed($errorMessage = null)
    {
        $updateData = [
            'status' => self::STATUS_FAILED,
            'processed_at' => now()
        ];

        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }

        $this->update($updateData);
    }

    /**
     * Criar uma nova correção para um artigo
     */
    public static function createCorrection($articleSlug, $type, $originalData, $description = null)
    {
        return self::create([
            'article_slug' => $articleSlug,
            'correction_type' => $type,
            'original_data' => $originalData,
            'description' => $description,
            'status' => self::STATUS_PENDING,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Obter estatísticas de correções
     */
    public static function getStats()
    {
        return [
            'pending_analysis' => self::pending()->byType(self::TYPE_PUNCTUATION_ANALYSIS)->count(),
            'completed_analysis' => self::where('status', self::STATUS_COMPLETED)
                ->byType(self::TYPE_PUNCTUATION_ANALYSIS)->count(),
            'needs_correction' => self::needsCorrection()->count(),
            'pending_fixes' => self::pending()->byType(self::TYPE_INTRODUCTION_FIX)->count(),
            'completed_fixes' => self::where('status', self::STATUS_COMPLETED)
                ->byType(self::TYPE_INTRODUCTION_FIX)->count(),
            'no_changes' => self::where('status', self::STATUS_NO_CHANGES)->count(),
            'skipped' => self::where('status', self::STATUS_SKIPPED)->count(),
            'failed' => self::where('status', self::STATUS_FAILED)->count()
        ];
    }

    /**
     * Obter estatísticas detalhadas
     */
    public static function getDetailedStats()
    {
        $stats = self::getStats();
        
        // Estatísticas por período
        $today = self::whereDate('created_at', today())->count();
        $thisWeek = self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = self::whereMonth('created_at', now()->month)->count();

        // Taxa de sucesso
        $totalProcessed = $stats['completed_analysis'] + $stats['failed'] + $stats['no_changes'] + $stats['skipped'];
        $successRate = $totalProcessed > 0 ? round(($stats['completed_analysis'] / $totalProcessed) * 100, 2) : 0;

        // Taxa de problemas encontrados
        $problemRate = $stats['completed_analysis'] > 0 ? 
            round(($stats['needs_correction'] / $stats['completed_analysis']) * 100, 2) : 0;

        return array_merge($stats, [
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'success_rate' => $successRate,
            'problem_rate' => $problemRate,
            'total_processed' => $totalProcessed
        ]);
    }

    /**
     * Obter análises que precisam de correção agrupadas por prioridade
     */
    public static function getCorrectionQueue()
    {
        $analyses = self::needsCorrection()
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'high_priority' => $analyses->filter(function($analysis) {
                return ($analysis->correction_data['correction_priority'] ?? 'medium') === 'high';
            }),
            'medium_priority' => $analyses->filter(function($analysis) {
                return ($analysis->correction_data['correction_priority'] ?? 'medium') === 'medium';
            }),
            'low_priority' => $analyses->filter(function($analysis) {
                return ($analysis->correction_data['correction_priority'] ?? 'medium') === 'low';
            })
        ];
    }

    /**
     * Obter artigos que precisam de reanálise
     */
    public static function getArticlesNeedingReanalysis($days = 3, $limit = 100)
    {
        // Buscar análises antigas
        $oldAnalyses = self::where('correction_type', self::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '<', now()->subDays($days))
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->pluck('article_slug');

        return $oldAnalyses->unique()->values();
    }

    /**
     * Limpar análises falhadas antigas
     */
    public static function cleanOldFailedAnalyses($hours = 24)
    {
        return self::oldFailed($hours)->delete();
    }

    /**
     * Verificar se um artigo foi analisado recentemente
     */
    public static function wasRecentlyAnalyzed($articleSlug, $days = 3)
    {
        return self::where('article_slug', $articleSlug)
            ->where('correction_type', self::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '>=', now()->subDays($days))
            ->exists();
    }

    /**
     * Obter última análise de um artigo
     */
    public static function getLastAnalysis($articleSlug, $type = null)
    {
        $query = self::where('article_slug', $articleSlug);
        
        if ($type) {
            $query->where('correction_type', $type);
        }

        return $query->orderBy('created_at', 'desc')->first();
    }

    /**
     * Verificar se há correção pendente para um artigo
     */
    public static function hasPendingCorrection($articleSlug, $type = null)
    {
        $query = self::where('article_slug', $articleSlug)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
        
        if ($type) {
            $query->where('correction_type', $type);
        }

        return $query->exists();
    }

    /**
     * Obter estatísticas por template
     */
    public static function getStatsByTemplate($template)
    {
        $analysisIds = self::where('correction_type', self::TYPE_PUNCTUATION_ANALYSIS)
            ->get()
            ->filter(function($analysis) use ($template) {
                return ($analysis->original_data['template'] ?? '') === $template;
            })
            ->pluck('_id');

        if ($analysisIds->isEmpty()) {
            return [
                'total' => 0,
                'analyzed' => 0,
                'needs_correction' => 0,
                'problem_rate' => 0
            ];
        }

        $analyzed = $analysisIds->count();
        $needsCorrection = self::whereIn('_id', $analysisIds)
            ->where('correction_data.needs_correction', true)
            ->count();

        return [
            'total' => $analyzed,
            'analyzed' => $analyzed,
            'needs_correction' => $needsCorrection,
            'problem_rate' => $analyzed > 0 ? round(($needsCorrection / $analyzed) * 100, 2) : 0
        ];
    }

    /**
     * Resetar análise falhada para reprocessamento
     */
    public function resetForReprocessing()
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'processed_at' => null,
            'updated_at' => now()
        ]);
    }

    /**
     * Verificar se a análise é antiga e precisa de renovação
     */
    public function needsReanalysis($days = 3)
    {
        return $this->created_at->lt(now()->subDays($days));
    }

    /**
     * Obter resumo dos problemas encontrados
     */
    public function getProblemsSummary()
    {
        if (!isset($this->correction_data['problems_found'])) {
            return [];
        }

        return collect($this->correction_data['problems_found'])
            ->groupBy('type')
            ->map(function($problems, $type) {
                return [
                    'type' => $type,
                    'count' => $problems->count(),
                    'descriptions' => $problems->pluck('description')->unique()->values()
                ];
            });
    }
}