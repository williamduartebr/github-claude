<?php

namespace Src\TirePressureCorrection\Domain\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class TirePressureCorrection extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'tire_pressure_corrections';
    protected $guarded = ['_id'];
    
    /**
     * Atributos que devem ser convertidos em tipos nativos
     */
    protected $casts = [
        'original_pressures' => 'array',
        'corrected_pressures' => 'array',
        'fields_updated' => 'array',
        'claude_response' => 'array',
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
    const STATUS_SKIPPED = 'skipped';
    const STATUS_NO_CHANGES = 'no_changes';

    /**
     * Tipos de correção
     */
    const CORRECTION_TYPE_MANUAL = 'manual';
    const CORRECTION_TYPE_CLAUDE_API = 'claude_api';
    const CORRECTION_TYPE_AUTOMATED = 'automated';

    /**
     * Obter correções pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Obter correções processadas
     */
    public function scopeProcessed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_COMPLETED, 
            self::STATUS_NO_CHANGES
        ]);
    }

    /**
     * Obter correções por artigo
     */
    public function scopeByArticle($query, $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    /**
     * Obter última correção de um artigo
     */
    public static function getLastCorrectionForArticle($articleId)
    {
        return self::byArticle($articleId)
            ->orderBy('created_at', 'desc')
            ->first();
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
    public function markAsCompleted(array $correctedPressures, array $fieldsUpdated)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'corrected_pressures' => $correctedPressures,
            'fields_updated' => $fieldsUpdated,
            'processed_at' => now()
        ]);
    }

    /**
     * Marcar como sem mudanças
     */
    public function markAsNoChanges($reason = null)
    {
        $this->update([
            'status' => self::STATUS_NO_CHANGES,
            'processed_at' => now(),
            'error_message' => $reason ?? 'Pressões já estão corretas'
        ]);
    }

    /**
     * Marcar como falhou
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now()
        ]);
    }

    /**
     * Criar correção para um artigo
     */
    public static function createForArticle($article, $correctionType = self::CORRECTION_TYPE_AUTOMATED)
    {
        $extractedEntities = data_get($article, 'extracted_entities', []);
        $vehicleData = data_get($article, 'content.vehicle_data', []);
        
        return self::create([
            'article_id' => $article->_id,
            'article_slug' => $article->slug,
            'vehicle_name' => sprintf(
                '%s %s %s',
                data_get($extractedEntities, 'marca', ''),
                data_get($extractedEntities, 'modelo', ''),
                data_get($extractedEntities, 'ano', '')
            ),
            'original_pressures' => [
                'empty_front' => $article->pressure_empty_front ?? null,
                'empty_rear' => $article->pressure_empty_rear ?? null,
                'light_front' => $article->pressure_light_front ?? null,
                'light_rear' => $article->pressure_light_rear ?? null,
                'vehicle_data' => data_get($vehicleData, 'pressures', []),
                'display' => data_get($vehicleData, 'pressure_display', ''),
                'loaded_display' => data_get($vehicleData, 'pressure_loaded_display', ''),
            ],
            'correction_type' => $correctionType,
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
            'pending' => self::pending()->count(),
            'processing' => self::where('status', self::STATUS_PROCESSING)->count(),
            'completed' => self::where('status', self::STATUS_COMPLETED)->count(),
            'no_changes' => self::where('status', self::STATUS_NO_CHANGES)->count(),
            'failed' => self::where('status', self::STATUS_FAILED)->count(),
            'total' => self::count(),
        ];
    }

    /**
     * Obter estatísticas detalhadas por período
     */
    public static function getDetailedStats($days = 7)
    {
        $stats = self::getStats();
        
        // Correções recentes
        $recentCorrections = self::where('created_at', '>=', now()->subDays($days))
            ->get()
            ->groupBy(function($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map->count();

        // Taxa de sucesso
        $totalProcessed = $stats['completed'] + $stats['no_changes'] + $stats['failed'];
        $successRate = $totalProcessed > 0 
            ? round((($stats['completed'] + $stats['no_changes']) / $totalProcessed) * 100, 2) 
            : 0;

        return array_merge($stats, [
            'recent_by_day' => $recentCorrections,
            'success_rate' => $successRate,
            'average_per_day' => $recentCorrections->avg() ?? 0,
        ]);
    }

    /**
     * Verificar se artigo foi corrigido recentemente
     */
    public static function wasRecentlyCorrected($articleId, $hours = 24)
    {
        return self::byArticle($articleId)
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_NO_CHANGES])
            ->where('processed_at', '>=', now()->subHours($hours))
            ->exists();
    }

    /**
     * Obter artigos que precisam de correção
     */
    public static function getArticlesNeedingCorrection($limit = 100)
    {
        // Buscar IDs de artigos já processados nas últimas 24 horas
        $recentlyProcessed = self::where('processed_at', '>=', now()->subHours(24))
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_NO_CHANGES])
            ->pluck('article_id');

        // Retornar query para artigos que precisam correção
        return \Src\AutoInfoCenter\Domain\Eloquent\Article::query()
            ->where('template', 'when_to_change_tires')
            ->whereNotIn('_id', $recentlyProcessed)
            ->orderBy('updated_at', 'desc')
            ->limit($limit);
    }

    /**
     * Limpar correções antigas
     */
    public static function cleanOldCorrections($days = 30)
    {
        return self::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_NO_CHANGES])
            ->delete();
    }

    /**
     * Obter resumo da correção
     */
    public function getCorrectionSummary()
    {
        return [
            'vehicle' => $this->vehicle_name,
            'status' => $this->status,
            'original' => [
                'empty' => sprintf(
                    '%s/%s PSI',
                    $this->original_pressures['empty_front'] ?? 'N/A',
                    $this->original_pressures['empty_rear'] ?? 'N/A'
                ),
                'loaded' => sprintf(
                    '%s/%s PSI',
                    $this->original_pressures['light_front'] ?? 'N/A',
                    $this->original_pressures['light_rear'] ?? 'N/A'
                ),
            ],
            'corrected' => $this->status === self::STATUS_COMPLETED ? [
                'empty' => sprintf(
                    '%s/%s PSI',
                    $this->corrected_pressures['empty_front'] ?? 'N/A',
                    $this->corrected_pressures['empty_rear'] ?? 'N/A'
                ),
                'loaded' => sprintf(
                    '%s/%s PSI',
                    $this->corrected_pressures['light_front'] ?? 'N/A',
                    $this->corrected_pressures['light_rear'] ?? 'N/A'
                ),
            ] : null,
            'fields_updated' => $this->fields_updated ?? [],
            'processed_at' => $this->processed_at?->format('d/m/Y H:i:s'),
        ];
    }
}