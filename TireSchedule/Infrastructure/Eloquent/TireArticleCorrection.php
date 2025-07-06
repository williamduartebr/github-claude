<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class TireArticleCorrection extends Model
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
    const TYPE_TIRE_PRESSURE_FIX = 'tire_pressure_fix';     // 🚗 Correção de pressões e conteúdo
    const TYPE_TITLE_YEAR_FIX = 'title_year_fix';           // 🆕 NOVO: Correção de títulos com ano

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
     * Limpar análises falhadas antigas
     */
    public static function cleanOldFailedAnalyses($hours = 24)
    {
        return self::oldFailed($hours)->delete();
    }

    /**
     * 🆕 Resetar correções travadas em processamento
     */
    public static function resetStuckProcessing($hours = 6)
    {
        return self::stuckProcessing($hours)->update([
            'status' => self::STATUS_PENDING,
            'updated_at' => now()
        ]);
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
     * 🆕 Verificar se um artigo de pneu foi corrigido recentemente
     */
    public static function wasTireRecentlyCorrected($articleSlug, $days = 7)
    {
        return self::where('article_slug', $articleSlug)
            ->where('correction_type', self::TYPE_TIRE_PRESSURE_FIX)
            ->where('created_at', '>=', now()->subDays($days))
            ->exists();
    }

    /**
     * 🆕 Verificar se um artigo teve título/ano corrigido recentemente
     */
    public static function wasTitleYearRecentlyCorrected($articleSlug, $days = 7)
    {
        return self::where('article_slug', $articleSlug)
            ->where('correction_type', self::TYPE_TITLE_YEAR_FIX)
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
     * 🆕 Obter estatísticas de pneus por domínio
     */
    public static function getTireStatsByDomain($domain = 'when_to_change_tires')
    {
        $corrections = self::where('correction_type', self::TYPE_TIRE_PRESSURE_FIX)
            ->get()
            ->filter(function($correction) use ($domain) {
                return ($correction->original_data['domain'] ?? '') === $domain;
            });

        $total = $corrections->count();
        $completed = $corrections->where('status', self::STATUS_COMPLETED)->count();
        $failed = $corrections->where('status', self::STATUS_FAILED)->count();
        $pending = $corrections->where('status', self::STATUS_PENDING)->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => ($completed + $failed) > 0 ? 
                round(($completed / ($completed + $failed)) * 100, 2) : 0
        ];
    }

    /**
     * 🆕 Obter estatísticas de título/ano por domínio
     */
    public static function getTitleYearStatsByDomain($domain = 'when_to_change_tires')
    {
        $corrections = self::where('correction_type', self::TYPE_TITLE_YEAR_FIX)
            ->get()
            ->filter(function($correction) use ($domain) {
                return ($correction->original_data['domain'] ?? '') === $domain;
            });

        $total = $corrections->count();
        $completed = $corrections->where('status', self::STATUS_COMPLETED)->count();
        $failed = $corrections->where('status', self::STATUS_FAILED)->count();
        $pending = $corrections->where('status', self::STATUS_PENDING)->count();

        // Estatísticas específicas de atualizações
        $titleUpdates = $corrections->filter(function($correction) {
            return $correction->status === self::STATUS_COMPLETED && 
                   ($correction->correction_data['title_updated'] ?? false);
        })->count();

        $metaUpdates = $corrections->filter(function($correction) {
            return $correction->status === self::STATUS_COMPLETED && 
                   ($correction->correction_data['meta_updated'] ?? false);
        })->count();

        $faqUpdates = $corrections->filter(function($correction) {
            return $correction->status === self::STATUS_COMPLETED && 
                   ($correction->correction_data['faq_updated'] ?? false);
        })->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => ($completed + $failed) > 0 ? 
                round(($completed / ($completed + $failed)) * 100, 2) : 0,
            'title_updates' => $titleUpdates,
            'meta_updates' => $metaUpdates,
            'faq_updates' => $faqUpdates
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
     * 🆕 Verificar se a correção de pneu é antiga
     */
    public function needsTireRecheck($days = 30)
    {
        return $this->created_at->lt(now()->subDays($days));
    }

    /**
     * 🆕 Verificar se a correção de título/ano é antiga
     */
    public function needsTitleYearRecheck($days = 60)
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

    /**
     * 🆕 Obter resumo das correções de pneu aplicadas
     */
    public function getTireCorrectionsSummary()
    {
        if (!isset($this->correction_data['corrected_pressures'])) {
            return [];
        }

        $originalPressures = $this->original_data['current_pressures'] ?? [];
        $correctedPressures = $this->correction_data['corrected_pressures'] ?? [];

        $changes = [];
        foreach ($correctedPressures as $key => $newValue) {
            $oldValue = $originalPressures[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'changed' => true
                ];
            }
        }

        return [
            'pressure_changes' => $changes,
            'content_updated' => isset($this->correction_data['corrected_content']),
            'needs_update' => $this->correction_data['needs_update'] ?? false,
            'correction_reason' => $this->correction_data['reason'] ?? null
        ];
    }

    /**
     * 🆕 Obter resumo das correções de título/ano aplicadas
     */
    public function getTitleYearCorrectionsSummary()
    {
        if (!isset($this->correction_data)) {
            return [];
        }

        $data = $this->correction_data;

        $changes = [];

        // Verificar mudanças no título
        if ($data['title_updated'] ?? false) {
            $oldTitle = $this->original_data['current_seo']['page_title'] ?? '';
            $newTitle = $data['corrected_seo']['page_title'] ?? '';
            $changes['title'] = [
                'old' => $oldTitle,
                'new' => $newTitle,
                'updated' => true
            ];
        }

        // Verificar mudanças na meta description
        if ($data['meta_updated'] ?? false) {
            $oldMeta = $this->original_data['current_seo']['meta_description'] ?? '';
            $newMeta = $data['corrected_seo']['meta_description'] ?? '';
            $changes['meta_description'] = [
                'old' => substr($oldMeta, 0, 100) . '...',
                'new' => substr($newMeta, 0, 100) . '...',
                'updated' => true
            ];
        }

        // Verificar mudanças nas FAQs
        if ($data['faq_updated'] ?? false) {
            $oldFaqCount = count($this->original_data['current_content']['perguntas_frequentes'] ?? []);
            $newFaqCount = count($data['corrected_content']['perguntas_frequentes'] ?? []);
            $changes['faqs'] = [
                'old_count' => $oldFaqCount,
                'new_count' => $newFaqCount,
                'updated' => true
            ];
        }

        return [
            'changes' => $changes,
            'needs_update' => $data['needs_update'] ?? false,
            'correction_reason' => $data['reason'] ?? null,
            'title_updated' => $data['title_updated'] ?? false,
            'meta_updated' => $data['meta_updated'] ?? false,
            'faq_updated' => $data['faq_updated'] ?? false
        ];
    }

    /**
     * 🆕 Obter todas as correções de pneus com detalhes
     */
    public static function getAllTireCorrectionsWithDetails($limit = 100)
    {
        return self::where('correction_type', self::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', self::STATUS_COMPLETED)
            ->orderBy('processed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($correction) {
                return [
                    'id' => $correction->_id,
                    'slug' => $correction->article_slug,
                    'vehicle_name' => $correction->original_data['vehicle_data']['vehicle_name'] ?? 'N/A',
                    'vehicle_year' => $correction->original_data['vehicle_data']['vehicle_year'] ?? 'N/A',
                    'processed_at' => $correction->processed_at,
                    'summary' => $correction->getTireCorrectionsSummary()
                ];
            });
    }

    /**
     * 🆕 Obter todas as correções de título/ano com detalhes
     */
    public static function getAllTitleYearCorrectionsWithDetails($limit = 100)
    {
        return self::where('correction_type', self::TYPE_TITLE_YEAR_FIX)
            ->where('status', self::STATUS_COMPLETED)
            ->orderBy('processed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($correction) {
                return [
                    'id' => $correction->_id,
                    'slug' => $correction->article_slug,
                    'vehicle_name' => $correction->original_data['vehicle_data']['vehicle_name'] ?? 'N/A',
                    'vehicle_year' => $correction->original_data['vehicle_data']['vehicle_year'] ?? 'N/A',
                    'processed_at' => $correction->processed_at,
                    'summary' => $correction->getTitleYearCorrectionsSummary()
                ];
            });
    }

    /**
     * 🆕 Obter estatísticas consolidadas por tipo de correção
     */
    public static function getConsolidatedStats()
    {
        $tireStats = self::getTireStats();
        $titleYearStats = self::getTitleYearStats();
        $generalStats = self::getStats();

        return [
            'tire_corrections' => $tireStats,
            'title_year_corrections' => $titleYearStats,
            'general_stats' => $generalStats,
            'total_corrections' => $tireStats['total'] + $titleYearStats['total'],
            'overall_success_rate' => [
                'tire' => $tireStats['success_rate'],
                'title_year' => $titleYearStats['success_rate']
            ],
            'generated_at' => now()->toISOString()
        ];
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
}