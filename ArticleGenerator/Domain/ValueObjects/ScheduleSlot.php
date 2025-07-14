<?php

namespace Src\ArticleGenerator\Domain\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

class ScheduleSlot
{
    private Carbon $scheduledAt;
    private string $articleType;
    private bool $isPeakHour;
    private int $dayOfWeek;
    private ?string $originalCreatedAt;
    private ?string $originalPublishedAt;

    public function __construct(
        Carbon $scheduledAt,
        string $articleType,
        bool $isPeakHour = false,
        ?string $originalCreatedAt = null,
        ?string $originalPublishedAt = null
    ) {
        $this->validateScheduledTime($scheduledAt);
        $this->validateArticleType($articleType);
        
        $this->scheduledAt = $scheduledAt;
        $this->articleType = $articleType;
        $this->isPeakHour = $isPeakHour;
        $this->dayOfWeek = $scheduledAt->dayOfWeekIso;
        $this->originalCreatedAt = $originalCreatedAt;
        $this->originalPublishedAt = $originalPublishedAt;
    }

    public static function forImportedArticle(
        Carbon $scheduledAt,
        ?string $originalCreatedAt,
        ?string $originalPublishedAt,
        bool $isPeakHour = false
    ): self {
        return new self(
            $scheduledAt,
            'imported',
            $isPeakHour,
            $originalCreatedAt,
            $originalPublishedAt
        );
    }

    public static function forNewArticle(
        Carbon $scheduledAt,
        bool $isPeakHour = false
    ): self {
        return new self(
            $scheduledAt,
            'new',
            $isPeakHour
        );
    }

    public function getScheduledAt(): Carbon
    {
        return $this->scheduledAt;
    }

    public function getArticleType(): string
    {
        return $this->articleType;
    }

    public function isImported(): bool
    {
        return $this->articleType === 'imported';
    }

    public function isNew(): bool
    {
        return $this->articleType === 'new';
    }

    public function isPeakHour(): bool
    {
        return $this->isPeakHour;
    }

    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    public function isWorkingDay(): bool
    {
        return $this->dayOfWeek >= 1 && $this->dayOfWeek <= 5;
    }

    public function getHour(): int
    {
        return $this->scheduledAt->hour;
    }

    public function getOriginalCreatedAt(): ?string
    {
        return $this->originalCreatedAt;
    }

    public function getOriginalPublishedAt(): ?string
    {
        return $this->originalPublishedAt;
    }

    public function hasOriginalDates(): bool
    {
        return !is_null($this->originalCreatedAt) && !is_null($this->originalPublishedAt);
    }

    /**
     * Gera updated_at humanizado para artigos importados
     */
    public function generateHumanizedUpdatedAt(): Carbon
    {
        if ($this->isNew()) {
            return $this->scheduledAt;
        }

        // Para artigos importados, gera updated_at recente mas realista
        $daysAgo = rand(0, 7);
        $baseDate = Carbon::now()->subDays($daysAgo);
        
        // Garantir que seja em horário comercial
        $hour = rand(9, 17);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        
        return $baseDate->setTime($hour, $minute, $second);
    }

    /**
     * Retorna timestamp único para evitar duplicações
     */
    public function getUniqueTimestamp(): int
    {
        return $this->scheduledAt->timestamp;
    }

    /**
     * Retorna informações de debug
     */
    public function toArray(): array
    {
        return [
            'scheduled_at' => $this->scheduledAt->format('Y-m-d H:i:s'),
            'article_type' => $this->articleType,
            'is_peak_hour' => $this->isPeakHour,
            'day_of_week' => $this->dayOfWeek,
            'is_working_day' => $this->isWorkingDay(),
            'hour' => $this->getHour(),
            'original_created_at' => $this->originalCreatedAt,
            'original_published_at' => $this->originalPublishedAt,
            'has_original_dates' => $this->hasOriginalDates(),
        ];
    }

    private function validateScheduledTime(Carbon $scheduledAt): void
    {
        // Verificar se está em horário comercial (7h às 22h)
        $hour = $scheduledAt->hour;
        if ($hour < 7 || $hour > 22) {
            throw new InvalidArgumentException(
                "Horário de agendamento deve estar entre 07:00 e 22:00. Recebido: {$hour}:00"
            );
        }

        // Verificar se é dia útil
        $dayOfWeek = $scheduledAt->dayOfWeekIso;
        if ($dayOfWeek > 5) {
            throw new InvalidArgumentException(
                "Agendamento deve ser apenas em dias úteis (segunda a sexta). Recebido: {$dayOfWeek}"
            );
        }
    }

    private function validateArticleType(string $articleType): void
    {
        $validTypes = ['imported', 'new'];
        if (!in_array($articleType, $validTypes)) {
            throw new InvalidArgumentException(
                "Tipo de artigo deve ser 'imported' ou 'new'. Recebido: {$articleType}"
            );
        }
    }
}