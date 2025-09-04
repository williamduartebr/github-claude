<?php

namespace Src\ContentGeneration\WhenToChangeTires\Domain\Events;

use Src\ContentGeneration\WhenToChangeTires\Domain\Entities\TireChangeArticle;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class TireChangeArticleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly TireChangeArticle $article,
        public readonly array $generationMetrics = []
    ) {}

    /**
     * Obter dados do evento para logging
     */
    public function getEventData(): array
    {
        return [
            'event_type' => 'article_created',
            'article_id' => $this->article->id,
            'vehicle_identifier' => $this->article->make . ' ' . $this->article->model . ' ' . $this->article->year,
            'slug' => $this->article->slug,
            'batch_id' => $this->article->batch_id,
            'content_score' => $this->article->content_score,
            'word_count' => $this->getWordCount(),
            'generation_metrics' => $this->generationMetrics,
            'created_at' => $this->article->created_at?->toISOString(),
            'vehicle_type' => $this->getVehicleType(),
            'category' => $this->article->category
        ];
    }

    /**
     * Obter contagem de palavras
     */
    protected function getWordCount(): int
    {
        if (empty($this->article->article_content)) {
            return 0;
        }

        $content = is_string($this->article->article_content)
            ? $this->article->article_content
            : json_encode($this->article->article_content);

        return str_word_count(strip_tags($content));
    }

    /**
     * Determinar tipo de veículo
     */
    protected function getVehicleType(): string
    {
        if (str_contains($this->article->category, 'motorcycle')) {
            return 'motorcycle';
        }

        if (str_contains($this->article->category, 'electric')) {
            return 'electric';
        }

        if (str_contains($this->article->category, 'hybrid')) {
            return 'hybrid';
        }

        return 'car';
    }
}
