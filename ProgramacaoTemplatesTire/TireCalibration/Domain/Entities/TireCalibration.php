<?php

namespace Src\ContentGeneration\TireCalibration\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Src\VehicleData\Domain\Entities\VehicleData;
use Carbon\Carbon;

/**
 * TireCalibration Model - ATUALIZADO para separar Script vs Claude API
 * 
 * FASE 2: Script gera estrutura técnica (generated_article string)
 * FASE 3: Claude API enriquece com linguagem contextualizada (claude_enhancements)
 * 
 * @property string $generated_article         // String estruturada (Fase 2 - script)
 * @property array $article_refined           // JSON final refinado (Fase 3 - Claude)
 * @property array $claude_enhancements       // Melhorias específicas Claude API
 * @property array $claude_processing_history // Histórico processamento Claude
 * @property float $claude_improvement_score  // Score melhoria 0-10
 * @property int $claude_api_calls           // Contagem calls API
 * @property Carbon $last_claude_processing  // Último processamento Claude
 */
class TireCalibration extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'tire_calibrations';
    protected $guarded = ['_id'];

    protected $casts = [
        // Campos originais
        'blog_modified_time' => 'datetime',
        'blog_published_time' => 'datetime',

        // Dados mapeados do VehicleData
        'vehicle_basic_data' => 'array',
        'pressure_specifications' => 'array',
        'vehicle_features' => 'array',

        // Timestamps de processamento
        'article_generated_at' => 'datetime',
        'claude_processing_started_at' => 'datetime',
        'claude_completed_at' => 'datetime',
        'last_claude_processing' => 'datetime',

        // Artigos - AJUSTADO
        'article_refined' => 'array',            // JSON final (Claude API)
        'claude_enhancements' => 'array',        // Melhorias Claude específicas

        // Métricas Claude API
        'claude_processing_history' => 'array',
        'claude_improvement_score' => 'float',
        'claude_api_calls' => 'integer',

        // Métricas gerais
        'data_completeness_score' => 'float',
        'content_quality_score' => 'float',
        'seo_score' => 'float',
        'processing_attempts' => 'integer',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes de fase (mantidas)
    const PHASE_PENDING = 'pending';
    const PHASE_ARTICLE_GENERATED = 'article_generated';
    const PHASE_CLAUDE_PROCESSING = 'claude_processing';
    const PHASE_CLAUDE_COMPLETED = 'claude_completed';
    const PHASE_COMPLETED = 'completed';
    const PHASE_FAILED = 'failed';

    // =======================================================================
    // MÉTODOS ESPECÍFICOS PARA CLAUDE API
    // =======================================================================

    /**
     * Marcar início do processamento Claude com dados específicos
     */
    public function startClaudeProcessing(): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_CLAUDE_PROCESSING,
            'claude_processing_started_at' => now(),
            'processing_attempts' => $this->processing_attempts + 1,
            'claude_api_calls' => $this->claude_api_calls + 1,
        ]);
    }

    /**
     * Finalizar processamento Claude com enhancements específicos
     */
    public function completeClaudeProcessing(array $claudeEnhancements, array $finalRefinedArticle): void
    {
        // Histórico de processamento
        $processingHistory = $this->claude_processing_history ?? [];
        $processingHistory[] = [
            'processed_at' => now()->toISOString(),
            'model_used' => 'claude-3-7-sonnet-20250219',
            'improvement_areas' => array_keys($claudeEnhancements),
            'api_calls_in_session' => 1,
            'processing_time_seconds' => $this->calculateProcessingTime(),
        ];

        $this->update([
            'enrichment_phase' => self::PHASE_CLAUDE_COMPLETED,
            'claude_completed_at' => now(),
            'last_claude_processing' => now(),
            
            // Dados Claude específicos
            'claude_enhancements' => $claudeEnhancements,
            'article_refined' => $finalRefinedArticle,
            'claude_processing_history' => $processingHistory,
            'claude_improvement_score' => $this->calculateClaudeImprovementScore($claudeEnhancements),
            
            // Atualizar scores gerais
            'content_quality_score' => $this->calculateContentQuality($finalRefinedArticle),
            'seo_score' => $this->calculateSeoScore($finalRefinedArticle),
            'last_error' => null,
        ]);
    }

    /**
     * Verificar se precisa de refinamento Claude
     */
    public function needsClaudeRefinement(): bool
    {
        return $this->enrichment_phase === self::PHASE_ARTICLE_GENERATED 
            && !empty($this->generated_article)
            && empty($this->claude_enhancements);
    }

    /**
     * Obter áreas que precisam de refinamento Claude
     */
    public function getAreasForClaudeRefinement(): array
    {
        $areas = [];

        // Sempre refinar introdução e considerações finais
        $areas[] = 'introducao';
        $areas[] = 'consideracoes_finais';
        $areas[] = 'perguntas_frequentes';

        // Áreas específicas por categoria
        if (str_contains($this->main_category, 'motorcycle')) {
            $areas[] = 'alertas_criticos';
            $areas[] = 'procedimento_calibragem';
        } elseif ($this->main_category === 'car_electric') {
            $areas[] = 'condicoes_especiais';
            $areas[] = 'impacto_pressao';
        } elseif ($this->main_category === 'pickup') {
            $areas[] = 'especificacoes_carga';
            $areas[] = 'cuidados_recomendacoes';
        }

        return $areas;
    }

    /**
     * Calcular score de melhoria Claude (0-10)
     */
    private function calculateClaudeImprovementScore(array $enhancements): float
    {
        $score = 5.0; // Base

        // +1 para cada área refinada
        $score += min(3.0, count($enhancements) * 0.5);

        // +2 se refinação incluiu contexto específico
        if (isset($enhancements['introducao']) && strlen($enhancements['introducao']) > 200) {
            $score += 1.0;
        }

        // +1 se incluiu perguntas contextuais
        if (isset($enhancements['perguntas_frequentes']) && count($enhancements['perguntas_frequentes']) >= 4) {
            $score += 1.0;
        }

        // +1 se considerações finais foram personalizadas
        if (isset($enhancements['consideracoes_finais']) && strlen($enhancements['consideracoes_finais']) > 150) {
            $score += 1.0;
        }

        return min(10.0, round($score, 2));
    }

    /**
     * Calcular tempo de processamento Claude
     */
    private function calculateProcessingTime(): int
    {
        if (!$this->claude_processing_started_at) {
            return 0;
        }

        return now()->diffInSeconds($this->claude_processing_started_at);
    }

    /**
     * Obter conteúdo final (script + Claude enhancements)
     */
    public function getFinalContent(): array
    {
        // Se Claude foi executado, usar article_refined
        if (!empty($this->article_refined)) {
            return $this->article_refined;
        }

        // Caso contrário, usar generated_article + enhancements se disponível
        if (!empty($this->generated_article)) {
            $baseArticle = json_decode($this->generated_article, true);
            
            if (!empty($this->claude_enhancements)) {
                // Merge dos enhancements Claude com estrutura base
                return $this->mergeClaudeEnhancements($baseArticle, $this->claude_enhancements);
            }
            
            return $baseArticle;
        }

        return [];
    }

    /**
     * Merge enhancements Claude com artigo base
     */
    private function mergeClaudeEnhancements(array $baseArticle, array $enhancements): array
    {
        if (isset($enhancements['introducao'])) {
            $baseArticle['content']['introducao'] = $enhancements['introducao'];
        }

        if (isset($enhancements['consideracoes_finais'])) {
            $baseArticle['content']['consideracoes_finais'] = $enhancements['consideracoes_finais'];
        }

        if (isset($enhancements['perguntas_frequentes'])) {
            $baseArticle['content']['perguntas_frequentes'] = $enhancements['perguntas_frequentes'];
        }

        // Adicionar outras áreas conforme necessário
        foreach ($enhancements as $area => $content) {
            if (!in_array($area, ['introducao', 'consideracoes_finais', 'perguntas_frequentes'])) {
                $baseArticle['content'][$area] = $content;
            }
        }

        // Adicionar metadados Claude
        $baseArticle['claude_metadata'] = [
            'enhanced_at' => $this->last_claude_processing?->toISOString(),
            'enhancement_score' => $this->claude_improvement_score,
            'enhanced_areas' => array_keys($enhancements),
        ];

        return $baseArticle;
    }

    // Manter outros métodos existentes...
    protected function calculateContentQuality(array $article): float
    {
        // Implementação existente
        return 8.0;
    }

    protected function calculateSeoScore(array $article): float
    {
        // Implementação existente  
        return 8.0;
    }

    public static function getProcessingStats(): array
    {
        $total = self::count();
        $pending = self::pending()->count();
        $readyForClaude = self::readyForClaudeRefinement()->count();
        $processingClaude = self::processingByClaude()->count();
        $completed = self::completed()->count();
        $failed = self::failed()->count();
        
        // Stats específicas Claude
        $claudeProcessed = self::whereNotNull('claude_enhancements')->count();
        $totalApiCalls = self::sum('claude_api_calls');
        $avgImprovementScore = self::whereNotNull('claude_improvement_score')->avg('claude_improvement_score');

        return [
            'total' => $total,
            'pending' => $pending,
            'pending_claude' => $readyForClaude,
            'processing_claude' => $processingClaude,
            'completed' => $completed,
            'failed' => $failed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'success_rate' => $total > 0 ? round((($completed + $readyForClaude + $processingClaude) / $total) * 100, 2) : 0,
            
            // Stats Claude específicas
            'claude_processed' => $claudeProcessed,
            'total_api_calls' => $totalApiCalls,
            'avg_improvement_score' => round($avgImprovementScore ?? 0, 2),
            'claude_success_rate' => $readyForClaude > 0 ? round(($claudeProcessed / $readyForClaude) * 100, 2) : 0,
        ];
    }

    // Scopes mantidos...
    public function scopePending($query) 
    {
        return $query->where('enrichment_phase', self::PHASE_PENDING);
    }

    public function scopeReadyForClaudeRefinement($query)
    {
        return $query->where('enrichment_phase', self::PHASE_ARTICLE_GENERATED);
    }

    public function scopeProcessingByClaude($query)
    {
        return $query->where('enrichment_phase', self::PHASE_CLAUDE_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('enrichment_phase', [
            self::PHASE_CLAUDE_COMPLETED,
            self::PHASE_COMPLETED
        ]);
    }

    public function scopeFailed($query)
    {
        return $query->where('enrichment_phase', self::PHASE_FAILED);
    }

    /**
     * ✅ MÉTODO NOVO: Marcar como falhou com mensagem de erro
     * 
     * Adicione este método à classe TireCalibration
     */
    public function markFailed(string $errorMessage): void
    {
        // Histórico de erro
        $processingHistory = $this->claude_processing_history ?? [];
        $processingHistory[] = [
            'failed_at' => now()->toISOString(),
            'error_message' => $errorMessage,
            'processing_attempt' => $this->processing_attempts ?? 0,
            'phase_when_failed' => $this->enrichment_phase,
        ];

        $this->update([
            'enrichment_phase' => self::PHASE_FAILED,
            'last_error' => $errorMessage,
            'failed_at' => now(),
            'claude_processing_history' => $processingHistory,
        ]);
    }

    /**
     * ✅ MÉTODO UTILITÁRIO: Verificar se pode tentar novamente
     */
    public function canRetryProcessing(): bool
    {
        // Se falhou, permitir retry após 1 hora
        if ($this->enrichment_phase === self::PHASE_FAILED) {
            $failedAt = $this->failed_at ?? $this->updated_at;
            return $failedAt->diffInHours(now()) >= 1;
        }

        // Se está processando há mais de 30 minutos, considerar travado
        if ($this->enrichment_phase === self::PHASE_CLAUDE_PROCESSING) {
            $startedAt = $this->claude_processing_started_at ?? $this->updated_at;
            return $startedAt->diffInMinutes(now()) >= 30;
        }

        return false;
    }

    /**
     * ✅ MÉTODO UTILITÁRIO: Reset para retry
     */
    public function resetForRetry(): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_ARTICLE_GENERATED,
            'claude_processing_started_at' => null,
            'last_error' => null,
            'processing_attempts' => ($this->processing_attempts ?? 0) + 1,
        ]);
    }
}