<?php

namespace Src\ContentGeneration\IdealPressure\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Src\VehicleData\Domain\Entities\VehicleData;
use Carbon\Carbon;

/**
 * IdealPressure Model - CORRIGIDO para alinhar com migration
 * 
 * FASE 1+2: Mapeamento e Geração de artigos
 * FASE 3: Refinamento via Claude API
 * 
 * @property string $wordpress_url
 * @property Carbon $blog_modified_time
 * @property Carbon $blog_published_time
 * @property string $vehicle_make
 * @property string $vehicle_model
 * @property int $vehicle_year
 * @property string $vehicle_data_id
 * @property array $vehicle_basic_data
 * @property array $pressure_specifications
 * @property array $vehicle_features
 * @property string $main_category
 * @property string $enrichment_phase
 * @property Carbon $article_generated_at
 * @property Carbon $claude_processing_started_at
 * @property Carbon $claude_completed_at
 * @property array $generated_article
 * @property array $article_refined
 * @property int $processing_attempts
 * @property string $processing_priority
 * @property string $last_error
 * @property float $data_completeness_score
 * @property float $content_quality_score
 * @property float $seo_score
 */
class IdealPressure extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'ideal_pressures';
    protected $guarded = ['_id'];

    /**
     * Campos que devem ser convertidos em tipos nativos
     */
    protected $casts = [
        // Campos originais
        'blog_modified_time' => 'datetime',
        'blog_published_time' => 'datetime',

        // Dados mapeados do VehicleData
        'vehicle_basic_data' => 'array',
        'pressure_specifications' => 'array',
        'vehicle_features' => 'array',

        // Timestamps de processamento - CORRIGIDOS para alinhar com migration
        'article_generated_at' => 'datetime',
        'claude_processing_started_at' => 'datetime',
        'claude_completed_at' => 'datetime',

        // Artigos gerados
        'generated_article' => 'array',      // JSON completo Fase 1+2
        'article_refined' => 'array',        // JSON refinado Claude

        // Métricas de qualidade
        'data_completeness_score' => 'float', // 0-10
        'content_quality_score' => 'float',   // 0-10  
        'seo_score' => 'float',              // 0-10

        // Controle
        'processing_attempts' => 'integer',
        'vehicle_year' => 'integer',

        // Timestamps automáticos
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =======================================================================
    // CONSTANTES DE FASES - CORRIGIDAS para alinhar com migration
    // =======================================================================

    /**
     * Fases do enriquecimento - SIMPLIFICADAS
     */
    const PHASE_PENDING = 'pending';                    // Inicial - sem processamento
    const PHASE_ARTICLE_GENERATED = 'article_generated'; // Artigo JSON completo gerado (Fase 1+2)
    const PHASE_CLAUDE_PROCESSING = 'claude_processing'; // Claude processando refinamento
    const PHASE_CLAUDE_COMPLETED = 'claude_completed';   // Claude finalizou refinamento
    const PHASE_COMPLETED = 'completed';                 // Processo 100% concluído
    const PHASE_FAILED = 'failed';                      // Falhou em alguma etapa

    /**
     * Prioridades de processamento
     */
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    // =======================================================================
    // RELACIONAMENTOS
    // =======================================================================

    /**
     * Relacionamento com VehicleData (referência MongoDB)
     */
    public function vehicleData()
    {
        return $this->hasOne(VehicleData::class, '_id', 'vehicle_data_id');
    }

    // =======================================================================
    // SCOPES PARA CONSULTAS - CORRIGIDOS
    // =======================================================================

    /**
     * Registros pendentes para processamento
     */
    public function scopePending($query)
    {
        return $query->where('enrichment_phase', self::PHASE_PENDING);
    }

    /**
     * Registros prontos para refinamento Claude (Fase 3)
     */
    public function scopeReadyForClaudeRefinement($query)
    {
        return $query->where('enrichment_phase', self::PHASE_ARTICLE_GENERATED);
    }

    /**
     * Registros sendo processados pela Claude
     */
    public function scopeProcessingByClaude($query)
    {
        return $query->where('enrichment_phase', self::PHASE_CLAUDE_PROCESSING);
    }

    /**
     * Registros completamente processados
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('enrichment_phase', [
            self::PHASE_CLAUDE_COMPLETED,
            self::PHASE_COMPLETED
        ]);
    }

    /**
     * Registros que falharam no processamento
     */
    public function scopeFailed($query)
    {
        return $query->where('enrichment_phase', self::PHASE_FAILED);
    }

    /**
     * Filtro por marca de veículo
     */
    public function scopeByVehicleMake($query, string $make)
    {
        return $query->where('vehicle_make', $make);
    }

    /**
     * Filtro por categoria principal
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('main_category', $category);
    }

    /**
     * Filtro por ano do veículo
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('vehicle_year', $year);
    }

    // =======================================================================
    // MÉTODOS DE CONTROLE DE FASE - CORRIGIDOS
    // =======================================================================

    /**
     * Marcar artigo como gerado (Fase 1+2 concluída)
     */
    public function markArticleGenerated(array $generatedArticle): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_ARTICLE_GENERATED,
            'generated_article' => $generatedArticle,
            'article_generated_at' => now(),
            'content_quality_score' => $this->calculateContentQuality($generatedArticle),
            'seo_score' => $this->calculateSeoScore($generatedArticle),
            'processing_attempts' => $this->processing_attempts + 1,
            'last_error' => null
        ]);
    }

    /**
     * Marcar início do processamento Claude
     */
    public function markClaudeProcessingStarted(): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_CLAUDE_PROCESSING,
            'claude_processing_started_at' => now(),
            'processing_attempts' => $this->processing_attempts + 1
        ]);
    }

    /**
     * Marcar Claude como concluído
     */
    public function markClaudeCompleted(array $refinedArticle): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_CLAUDE_COMPLETED,
            'article_refined' => $refinedArticle,
            'claude_completed_at' => now(),
            'content_quality_score' => $this->calculateContentQuality($refinedArticle),
            'seo_score' => $this->calculateSeoScore($refinedArticle),
            'last_error' => null
        ]);
    }

    /**
     * Marcar como completamente finalizado
     */
    public function markCompleted(): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_COMPLETED,
            'last_error' => null
        ]);
    }

    /**
     * Marcar como falha com erro
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_FAILED,
            'last_error' => $error,
            'processing_attempts' => $this->processing_attempts + 1,
            'updated_at' => now()
        ]);
    }

    // =======================================================================
    // MÉTODOS DE CÁLCULO DE QUALIDADE
    // =======================================================================

    /**
     * Calcular score de completude dos dados (0-10)
     */
    public function calculateDataCompleteness(): float
    {
        $score = 0;
        $maxScore = 10;

        // Dados básicos obrigatórios (3 pontos)
        if (!empty($this->vehicle_make)) $score += 1;
        if (!empty($this->vehicle_model)) $score += 1;
        if (!empty($this->vehicle_year)) $score += 1;

        // Especificações de pressão (4 pontos)
        $pressureSpecs = $this->pressure_specifications ?? [];
        if (!empty($pressureSpecs['pressure_light_front'])) $score += 1;
        if (!empty($pressureSpecs['pressure_light_rear'])) $score += 1;
        if (!empty($pressureSpecs['pressure_max_front'])) $score += 1;
        if (!empty($pressureSpecs['pressure_max_rear'])) $score += 1;

        // Dados técnicos adicionais (3 pontos)
        if (!empty($this->main_category)) $score += 1;
        if (!empty($this->vehicle_basic_data['tire_size'])) $score += 1;
        if (!empty($this->vehicle_features)) $score += 1;

        return round(($score / $maxScore) * 10, 2);
    }

    /**
     * Calcular score de qualidade do conteúdo (0-10)
     */
    protected function calculateContentQuality(array $article): float
    {
        $score = 0;
        $maxScore = 10;

        // Estrutura básica (3 pontos)
        if (!empty($article['title'])) $score += 1;
        if (!empty($article['slug'])) $score += 1;
        if (!empty($article['seo_data']['meta_description'])) $score += 1;

        // Conteúdo técnico (4 pontos)
        if (!empty($article['technical_content'])) $score += 2;
        if (!empty($article['benefits_content'])) $score += 1;
        if (!empty($article['maintenance_tips'])) $score += 1;

        // Qualidade SEO (3 pontos)
        $seoData = $article['seo_data'] ?? [];
        if (!empty($seoData['primary_keyword'])) $score += 1;
        if (!empty($seoData['secondary_keywords']) && count($seoData['secondary_keywords']) >= 3) $score += 1;
        if (!empty($article['critical_alerts'])) $score += 1;

        return round(($score / $maxScore) * 10, 2);
    }

    /**
     * Calcular score SEO (0-10)
     */
    protected function calculateSeoScore(array $article): float
    {
        $score = 0;
        $maxScore = 10;
        $seoData = $article['seo_data'] ?? [];

        // Elementos básicos SEO (5 pontos)
        if (!empty($seoData['page_title']) && strlen($seoData['page_title']) <= 60) $score += 1;
        if (!empty($seoData['meta_description']) && strlen($seoData['meta_description']) <= 160) $score += 1;
        if (!empty($seoData['h1'])) $score += 1;
        if (!empty($seoData['primary_keyword'])) $score += 1;
        if (!empty($seoData['secondary_keywords']) && count($seoData['secondary_keywords']) >= 3) $score += 1;

        // Estrutura do conteúdo (3 pontos)
        if (isset($article['technical_content']) && is_array($article['technical_content'])) $score += 1;
        if (isset($article['benefits_content']) && is_array($article['benefits_content'])) $score += 1;
        if (isset($article['maintenance_tips']) && is_array($article['maintenance_tips'])) $score += 1;

        // Open Graph (2 pontos)
        if (!empty($seoData['og_title'])) $score += 1;
        if (!empty($seoData['og_description'])) $score += 1;

        return round(($score / $maxScore) * 10, 2);
    }

    // =======================================================================
    // MÉTODOS ESTÁTICOS PARA ESTATÍSTICAS
    // =======================================================================

    /**
     * Obter estatísticas de processamento
     */
    public static function getProcessingStats(): array
    {
        $total = self::count();
        $pending = self::pending()->count();
        $readyForClaude = self::readyForClaudeRefinement()->count();
        $processingClaude = self::processingByClaude()->count();
        $completed = self::completed()->count();
        $failed = self::failed()->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'pending_claude' => $readyForClaude,
            'processing_claude' => $processingClaude,
            'completed' => $completed,
            'failed' => $failed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'success_rate' => $total > 0 ? round((($completed + $readyForClaude + $processingClaude) / $total) * 100, 2) : 0
        ];
    }

    /**
     * Top marcas por volume
     */
    public static function getTopMakes(int $limit = 10): array
    {
        return self::selectRaw('vehicle_make, COUNT(*) as count')
            ->whereNotNull('vehicle_make')
            ->groupBy('vehicle_make')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
