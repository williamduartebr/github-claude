<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Listeners;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events\TireChangeArticleEnhanced;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LogArticleEnhanced
{
    /**
     * Handle do evento de artigo refinado
     */
    public function handle(TireChangeArticleEnhanced $event): void
    {
        $eventData = $event->getEventData();

        // Log principal
        Log::info('Artigo de pneus refinado pelo Claude', $eventData);

        // Log específico para canal Claude
        if (config('when-to-change-tires.logging.separate_file')) {
            Log::channel('claude-enhancements')->info('Article Enhanced', $eventData);
        }

        // Atualizar métricas de refinamento
        $this->updateEnhancementMetrics($event);

        // Log de performance
        $this->logPerformanceMetrics($eventData);

        // Alertas se necessário
        $this->checkQualityAlerts($event);
    }

    /**
     * Atualizar métricas de refinamento
     */
    protected function updateEnhancementMetrics(TireChangeArticleEnhanced $event): void
    {
        try {
            // Contador de refinamentos diários
            $todayKey = 'tire_articles_enhanced_today_' . now()->format('Y-m-d');
            Cache::increment($todayKey, 1);
            Cache::put($todayKey, Cache::get($todayKey, 0), now()->endOfDay());

            // Métricas por tipo de enhancement
            $enhancementType = $event->enhancementData['type'] ?? 'unknown';
            $typeKey = 'tire_articles_enhanced_' . $enhancementType;
            Cache::increment($typeKey, 1);

            // Tracking de melhorias de score
            $improvement = $event->calculateImprovement();
            if ($improvement !== null) {
                $improvementKey = 'tire_articles_score_improvements';
                $improvements = Cache::get($improvementKey, []);
                $improvements[] = $improvement;

                // Manter apenas últimos 50 valores
                if (count($improvements) > 50) {
                    $improvements = array_slice($improvements, -50);
                }

                Cache::put($improvementKey, $improvements, 86400);
            }
        } catch (\Exception $e) {
            Log::warning('Erro atualizando métricas de refinamento: ' . $e->getMessage());
        }
    }

    /**
     * Log de métricas de performance
     */
    protected function logPerformanceMetrics(array $eventData): void
    {
        $performanceLog = [
            'metric_type' => 'claude_enhancement_performance',
            'timestamp' => now()->toISOString(),
            'article_id' => $eventData['article_id'],
            'enhancement_type' => $eventData['enhancement_type'],
            'processing_time' => $eventData['processing_time'],
            'tokens_used' => $eventData['tokens_used'],
            'score_improvement' => $eventData['improvement'],
            'claude_model' => $eventData['claude_model']
        ];

        Log::info('CLAUDE_PERFORMANCE_METRICS', $performanceLog);
    }

    /**
     * Verificar alertas de qualidade
     */
    protected function checkQualityAlerts(TireChangeArticleEnhanced $event): void
    {
        try {
            $improvement = $event->calculateImprovement();

            // Alerta se houve piora significativa
            if ($improvement !== null && $improvement < -0.5) {
                Log::warning('Refinamento Claude resultou em piora de qualidade', [
                    'article_id' => $event->article->id,
                    'vehicle' => $event->article->make . ' ' . $event->article->model,
                    'score_degradation' => $improvement,
                    'enhancement_type' => $event->enhancementData['type'] ?? 'unknown'
                ]);
            }

            // Alerta se melhoria foi muito pequena
            if ($improvement !== null && $improvement > 0 && $improvement < 0.1) {
                Log::notice('Refinamento Claude teve impacto mínimo', [
                    'article_id' => $event->article->id,
                    'minimal_improvement' => $improvement
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Erro verificando alertas de qualidade: ' . $e->getMessage());
        }
    }
}
