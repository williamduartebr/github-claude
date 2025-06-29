<?php

namespace App\ContentGeneration\WhenToChangeTires\Infrastructure\Listeners;

use App\ContentGeneration\WhenToChangeTires\Domain\Events\TireChangeArticleCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LogArticleCreated
{
    /**
     * Handle do evento de artigo criado
     */
    public function handle(TireChangeArticleCreated $event): void
    {
        $eventData = $event->getEventData();
        
        // Log principal
        Log::info('Artigo de pneus criado com sucesso', $eventData);
        
        // Log específico para o canal tire-articles (se configurado)
        if (config('when-to-change-tires.logging.separate_file')) {
            Log::channel('tire-articles')->info('Article Created', $eventData);
        }
        
        // Atualizar métricas em cache
        $this->updateMetrics($event);
        
        // Log estruturado para monitoramento
        $this->logStructuredMetrics($eventData);
    }

    /**
     * Atualizar métricas em cache
     */
    protected function updateMetrics(TireChangeArticleCreated $event): void
    {
        try {
            // Incrementar contador diário
            $todayKey = 'tire_articles_created_today_' . now()->format('Y-m-d');
            Cache::increment($todayKey, 1);
            Cache::put($todayKey, Cache::get($todayKey, 0), now()->endOfDay());
            
            // Incrementar contador por marca
            $makeKey = 'tire_articles_by_make_' . strtolower($event->article->make);
            Cache::increment($makeKey, 1);
            
            // Atualizar última criação
            Cache::put('tire_articles_last_created', now(), 86400);
            
            // Métricas de qualidade
            $qualityKey = 'tire_articles_quality_scores';
            $scores = Cache::get($qualityKey, []);
            $scores[] = $event->article->content_score;
            
            // Manter apenas últimos 100 scores
            if (count($scores) > 100) {
                $scores = array_slice($scores, -100);
            }
            
            Cache::put($qualityKey, $scores, 86400);
            
        } catch (\Exception $e) {
            Log::warning('Erro atualizando métricas de artigo criado: ' . $e->getMessage());
        }
    }

    /**
     * Log estruturado para ferramentas de monitoramento
     */
    protected function logStructuredMetrics(array $eventData): void
    {
        // Formato estruturado para parsing automático
        $structuredLog = [
            'metric_type' => 'tire_article_created',
            'timestamp' => now()->toISOString(),
            'article_id' => $eventData['article_id'],
            'vehicle_type' => $eventData['vehicle_type'],
            'category' => $eventData['category'],
            'make' => explode(' ', $eventData['vehicle_identifier'])[0],
            'content_score' => $eventData['content_score'],
            'word_count' => $eventData['word_count'],
            'batch_id' => $eventData['batch_id']
        ];

        Log::info('TIRE_ARTICLE_METRICS', $structuredLog);
    }
}
