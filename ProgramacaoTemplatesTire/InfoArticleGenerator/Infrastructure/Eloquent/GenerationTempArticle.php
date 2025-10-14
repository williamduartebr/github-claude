<?php

namespace Src\InfoArticleGenerator\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * TempArticle - Staging para Geração de Artigos
 * 
 * FLUXO:
 * 1. Título inserido manualmente ou via command
 * 2. Claude API gera JSON completo
 * 3. Validação e ajustes
 * 4. Migração para Article (produção)
 * 
 * STATUS:
 * - pending: aguardando geração
 * - generating: em processo na API
 * - generated: JSON criado com sucesso
 * - validated: JSON validado
 * - published: migrado para Article
 * - failed: falha na geração
 * - retrying: tentando novamente
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class GenerationTempArticle extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'generation_temp_articles';
    protected $guarded = ['_id'];
    
    protected $casts = [
        'metadata' => 'array',
        'extracted_entities' => 'array',
        'seo_data' => 'array',
        'generated_json' => 'array',
        'validation_errors' => 'array',
        'generation_attempts' => 'array',
        'published_at' => 'datetime',
        'modified_at' => 'datetime',
        'generated_at' => 'datetime',
        'validated_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Artigos pendentes de geração
     */
    public function scopePending($query)
    {
        return $query->where('generation_status', 'pending')
                    ->whereNull('generated_at');
    }

    /**
     * Artigos por status de geração
     */
    public function scopeByGenerationStatus($query, string $status)
    {
        return $query->where('generation_status', $status);
    }

    /**
     * Artigos por prioridade
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('generation_priority', $priority);
    }

    /**
     * Artigos por categoria
     */
    public function scopeByCategory($query, string $categorySlug)
    {
        return $query->where('category_slug', $categorySlug);
    }

    /**
     * Artigos falhados que podem ser retentados
     */
    public function scopeRetryable($query)
    {
        return $query->where('generation_status', 'failed')
                    ->where('generation_retry_count', '<', 3);
    }

    /**
     * Artigos gerados com sucesso aguardando publicação
     */
    public function scopeReadyToPublish($query)
    {
        return $query->where('generation_status', 'validated')
                    ->whereNull('published_at');
    }

    // ============================================
    // MÉTODOS DE GERAÇÃO
    // ============================================

    /**
     * Marcar como gerando
     */
    public function markAsGenerating(string $model): void
    {
        $this->update([
            'generation_status' => 'generating',
            'generation_started_at' => now(),
            'generation_model_used' => $model,
            'generation_retry_count' => ($this->generation_retry_count ?? 0)
        ]);
    }

    /**
     * Marcar geração como sucesso
     */
    public function markAsGenerated(array $jsonData, string $model, float $cost): void
    {
        $attempts = $this->generation_attempts ?? [];
        $attempts[] = [
            'model' => $model,
            'status' => 'success',
            'cost' => $cost,
            'timestamp' => now()->toISOString()
        ];

        $this->update([
            'generation_status' => 'generated',
            'generated_json' => $jsonData,
            'generated_at' => now(),
            'generation_model_used' => $model,
            'generation_cost' => $cost,
            'generation_attempts' => $attempts
        ]);
    }

    /**
     * Marcar geração como falha
     */
    public function markAsFailed(string $error, string $model): void
    {
        $attempts = $this->generation_attempts ?? [];
        $attempts[] = [
            'model' => $model,
            'status' => 'failed',
            'error' => $error,
            'timestamp' => now()->toISOString()
        ];

        $this->update([
            'generation_status' => 'failed',
            'generation_error' => $error,
            'generation_retry_count' => ($this->generation_retry_count ?? 0) + 1,
            'generation_attempts' => $attempts,
            'generation_last_attempt_at' => now()
        ]);
    }

    /**
     * Marcar como validado
     */
    public function markAsValidated(): void
    {
        $this->update([
            'generation_status' => 'validated',
            'validated_at' => now(),
            'validation_errors' => null
        ]);
    }

    /**
     * Marcar como publicado
     */
    public function markAsPublished(string $articleId): void
    {
        $this->update([
            'generation_status' => 'published',
            'published_at' => now(),
            'published_article_id' => $articleId
        ]);
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Obter custo total de geração
     */
    public function getTotalGenerationCost(): float
    {
        $attempts = $this->generation_attempts ?? [];
        return array_sum(array_column($attempts, 'cost'));
    }

    /**
     * Verificar se pode retentar
     */
    public function canRetry(): bool
    {
        return ($this->generation_retry_count ?? 0) < 3 &&
               $this->generation_status === 'failed';
    }

    /**
     * Obter número de tentativas
     */
    public function getAttemptCount(): int
    {
        return count($this->generation_attempts ?? []);
    }

    /**
     * Obter último modelo usado
     */
    public function getLastModelUsed(): ?string
    {
        $attempts = $this->generation_attempts ?? [];
        return !empty($attempts) ? end($attempts)['model'] : null;
    }
}