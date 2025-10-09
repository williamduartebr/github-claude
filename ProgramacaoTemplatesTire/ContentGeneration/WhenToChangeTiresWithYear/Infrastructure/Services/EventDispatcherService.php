<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events\TireChangeArticleCreated;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events\TireChangeArticleEnhanced;
use Illuminate\Support\Facades\Event;

class EventDispatcherService
{
    /**
     * Disparar evento de artigo criado
     */
    public function dispatchArticleCreated(
        TireChangeArticle $article,
        array $generationMetrics = []
    ): void {
        Event::dispatch(new TireChangeArticleCreated($article, $generationMetrics));
    }

    /**
     * Disparar evento de artigo refinado
     */
    public function dispatchArticleEnhanced(
        TireChangeArticle $article,
        array $enhancementData = [],
        array $performanceMetrics = []
    ): void {
        Event::dispatch(new TireChangeArticleEnhanced($article, $enhancementData, $performanceMetrics));
    }

    /**
     * Disparar múltiplos eventos em lote
     */
    public function dispatchBatchCreated(array $articles, string $batchId): void
    {
        foreach ($articles as $article) {
            $this->dispatchArticleCreated($article, ['batch_id' => $batchId]);
        }
    }

    /**
     * Obter métricas atuais dos eventos
     */
    public function getCurrentMetrics(): array
    {
        return [
            'created_today' => \Cache::get('tire_articles_created_today_' . now()->format('Y-m-d'), 0),
            'enhanced_today' => \Cache::get('tire_articles_enhanced_today_' . now()->format('Y-m-d'), 0),
            'last_created' => \Cache::get('tire_articles_last_created'),
            'average_improvements' => $this->getAverageImprovements(),
            'tokens_used_today' => \Cache::get('claude_tokens_used_today_' . now()->format('Y-m-d'), 0),
        ];
    }

    /**
     * Obter média de melhorias dos refinamentos
     */
    protected function getAverageImprovements(): ?float
    {
        $improvements = \Cache::get('tire_articles_score_improvements', []);

        if (empty($improvements)) {
            return null;
        }

        return round(array_sum($improvements) / count($improvements), 2);
    }
}
