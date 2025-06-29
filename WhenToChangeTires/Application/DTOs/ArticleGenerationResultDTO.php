<?php

namespace App\ContentGeneration\WhenToChangeTires\Application\DTOs;

class ArticleGenerationResultDTO
{
    public function __construct(
        public readonly int $totalProcessed,
        public readonly int $successful,
        public readonly int $failed,
        public readonly int $skipped,
        public readonly array $errors = [],
        public readonly array $createdSlugs = [],
        public readonly array $statistics = [],
        public readonly float $executionTime = 0.0,
        public readonly string $batchId = ''
    ) {}

    public function getSuccessRate(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }
        
        return round(($this->successful / $this->totalProcessed) * 100, 2);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function isSuccessful(): bool
    {
        return $this->failed === 0 && $this->successful > 0;
    }

    public function toArray(): array
    {
        return [
            'total_processed' => $this->totalProcessed,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'skipped' => $this->skipped,
            'success_rate' => $this->getSuccessRate(),
            'error_count' => $this->getErrorCount(),
            'errors' => $this->errors,
            'created_slugs' => $this->createdSlugs,
            'statistics' => $this->statistics,
            'execution_time' => $this->executionTime,
            'batch_id' => $this->batchId,
            'is_successful' => $this->isSuccessful()
        ];
    }
}
