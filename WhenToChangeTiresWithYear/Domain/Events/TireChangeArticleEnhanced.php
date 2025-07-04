<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Entities\TireChangeArticle;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class TireChangeArticleEnhanced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TireChangeArticle $article,
        public readonly array $enhancementData = [],
        public readonly array $performanceMetrics = []
    ) {}

    /**
     * Obter dados do evento para logging
     */
    public function getEventData(): array
    {
        return [
            'event_type' => 'article_enhanced',
            'article_id' => $this->article->id,
            'vehicle_identifier' => $this->article->make . ' ' . $this->article->model . ' ' . $this->article->year,
            'enhancement_count' => $this->article->claude_enhancement_count,
            'enhancement_type' => $this->enhancementData['type'] ?? 'unknown',
            'enhancement_sections' => $this->enhancementData['sections'] ?? [],
            'previous_score' => $this->enhancementData['previous_score'] ?? null,
            'new_score' => $this->article->content_score,
            'improvement' => $this->calculateImprovement(),
            'claude_model' => $this->enhancementData['claude_model'] ?? null,
            'tokens_used' => $this->enhancementData['tokens_used'] ?? null,
            'processing_time' => $this->performanceMetrics['processing_time'] ?? null,
            'enhanced_at' => $this->article->claude_last_enhanced_at?->toISOString(),
            'batch_id' => $this->article->batch_id
        ];
    }

    /**
     * Calcular melhoria de score
     */
    public function calculateImprovement(): ?float
    {
        $previousScore = $this->enhancementData['previous_score'] ?? null;
        $newScore = $this->article->content_score;

        if ($previousScore && $newScore) {
            return round($newScore - $previousScore, 2);
        }

        return null;
    }

    /**
     * Verificar se enhancement foi bem-sucedido
     */
    public function isSuccessful(): bool
    {
        $improvement = $this->calculateImprovement();
        return $improvement !== null && $improvement > 0;
    }
}
