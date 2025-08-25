<?php

namespace Src\ContentGeneration\TireCalibration\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Src\VehicleData\Domain\Entities\VehicleData;
use Carbon\Carbon;

/**
 * TireCalibration Model - Entidade expandida para geração de artigos
 * 
 * FASE 1: Mapeamento com VehicleData
 * FASE 2: Geração de artigos completos  
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
 * @property Carbon $phase1_completed_at
 * @property Carbon $phase2_completed_at
 * @property Carbon $claude_processing_started_at
 * @property Carbon $claude_completed_at
 * @property array $generated_article
 * @property array $article_refined
 * @property int $processing_attempts
 * @property string $last_error
 * @property float $data_completeness_score
 * @property float $content_quality_score
 * @property float $seo_score
 */
class TireCalibration extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $table = 'tire_calibrations';
    protected $guarded = ['_id'];

    /**
     * Campos que devem ser convertidos em tipos nativos
     */
    protected $casts = [
        // Campos originais
        'blog_modified_time' => 'datetime',
        'blog_published_time' => 'datetime',
        
        // FASE 1: Dados mapeados do VehicleData
        'vehicle_basic_data' => 'array',
        'pressure_specifications' => 'array',
        'vehicle_features' => 'array',
        
        // Timestamps de processamento
        'phase1_completed_at' => 'datetime',
        'phase2_completed_at' => 'datetime',
        'claude_processing_started_at' => 'datetime',
        'claude_completed_at' => 'datetime',
        
        // Artigos gerados
        'generated_article' => 'array',      // JSON completo Fase 2
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
    // CONSTANTES DE FASES DE PROCESSAMENTO
    // =======================================================================

    /**
     * Fases do enriquecimento
     */
    const PHASE_PENDING = 'pending';                    // Inicial - sem processamento
    const PHASE_VEHICLE_MATCHED = 'vehicle_matched';    // VehicleData encontrado
    const PHASE_VEHICLE_ENRICHED = 'vehicle_enriched';  // Dados do VehicleData mapeados
    const PHASE_ARTICLE_GENERATED = 'article_generated'; // Artigo JSON completo gerado
    const PHASE_CLAUDE_PROCESSING = 'claude_processing'; // Claude processando refinamento
    const PHASE_CLAUDE_COMPLETED = 'claude_completed';   // Claude finalizou refinamento
    const PHASE_COMPLETED = 'completed';                 // Processo 100% concluído
    const PHASE_FAILED = 'failed';                      // Falhou em alguma etapa

    /**
     * Categorias de veículos para templates
     */
    const TEMPLATE_CAR = 'tire_calibration_car';
    const TEMPLATE_MOTORCYCLE = 'tire_calibration_motorcycle';
    const TEMPLATE_PICKUP = 'tire_calibration_pickup';
    const TEMPLATE_ELECTRIC = 'tire_calibration_electric';

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
    // SCOPES PARA CONSULTAS
    // =======================================================================

    /**
     * Registros pendentes para mapeamento com VehicleData
     */
    public function scopePendingVehicleMapping($query)
    {
        return $query->whereIn('enrichment_phase', [
            self::PHASE_PENDING,
            self::PHASE_VEHICLE_MATCHED
        ]);
    }

    /**
     * Registros prontos para geração de artigos (Fase 2)
     */
    public function scopeReadyForArticleGeneration($query)
    {
        return $query->where('enrichment_phase', self::PHASE_VEHICLE_ENRICHED);
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

    /**
     * Ordenar por prioridade de processamento (MongoDB compatível)
     */
    public function scopeByProcessingPriority($query)
    {
        // MongoDB não suporta CASE - usar ordenação por coleção
        $priorities = [
            'motorcycle' => 1,
            'motorcycle_street' => 1,
            'motorcycle_scooter' => 1,
            'car_electric' => 2,
            'suv' => 3,
            'pickup' => 3,
            'hatch' => 4,
            'sedan' => 4
        ];

        // Primeiro ordenar por ano (mais recente primeiro)
        // Depois aplicar ordenação manual por prioridade no Collection
        return $query->orderBy('vehicle_year', 'desc')
                    ->orderBy('main_category');
    }

    /**
     * Obter registros ordenados por prioridade (método alternativo)
     */
    public static function getByProcessingPriority(int $limit = null)
    {
        $priorities = [
            'motorcycle' => 1,
            'motorcycle_street' => 1, 
            'motorcycle_scooter' => 1,
            'car_electric' => 2,
            'suv' => 3,
            'pickup' => 3,
            'hatch' => 4,
            'sedan' => 4
        ];

        $query = self::orderBy('vehicle_year', 'desc');
        
        if ($limit) {
            $query->limit($limit * 2); // Buscar mais para poder ordenar
        }

        return $query->get()->sortBy(function ($item) use ($priorities) {
            return $priorities[$item->main_category] ?? 999;
        })->take($limit ?? 100);
    }

    // =======================================================================
    // MÉTODOS DE CONTROLE DE FASE
    // =======================================================================

    /**
     * Marcar como veículo encontrado no VehicleData
     */
    public function markVehicleMatched(string $vehicleDataId): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_VEHICLE_MATCHED,
            'vehicle_data_id' => $vehicleDataId,
            'processing_attempts' => $this->processing_attempts + 1,
            'last_error' => null
        ]);
    }

    /**
     * Marcar Fase 1 como concluída (dados mapeados)
     */
    public function markPhase1Completed(array $vehicleData): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_VEHICLE_ENRICHED,
            'vehicle_make' => $vehicleData['make'],
            'vehicle_model' => $vehicleData['model'], 
            'vehicle_year' => $vehicleData['year'],
            'vehicle_basic_data' => $vehicleData['basic_data'] ?? [],
            'pressure_specifications' => $vehicleData['pressure_specifications'] ?? [],
            'vehicle_features' => $vehicleData['vehicle_features'] ?? [],
            'main_category' => $vehicleData['main_category'] ?? null,
            'phase1_completed_at' => now(),
            'data_completeness_score' => $this->calculateDataCompleteness($vehicleData),
            'last_error' => null
        ]);
    }

    /**
     * Marcar Fase 2 como concluída (artigo gerado)
     */
    public function markPhase2Completed(array $generatedArticle): void
    {
        $this->update([
            'enrichment_phase' => self::PHASE_ARTICLE_GENERATED,
            'generated_article' => $generatedArticle,
            'phase2_completed_at' => now(),
            'content_quality_score' => $this->calculateContentQuality($generatedArticle),
            'seo_score' => $this->calculateSeoScore($generatedArticle),
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
    public function markFailed(string $error, string $phase = null): void
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
    protected function calculateDataCompleteness(array $vehicleData): float
    {
        $score = 0;
        $maxScore = 10;

        // Dados básicos obrigatórios (3 pontos)
        if (!empty($vehicleData['make'])) $score += 1;
        if (!empty($vehicleData['model'])) $score += 1; 
        if (!empty($vehicleData['year'])) $score += 1;

        // Especificações de pressão (4 pontos)
        $pressureSpecs = $vehicleData['pressure_specifications'] ?? [];
        if (!empty($pressureSpecs['pressure_light_front'])) $score += 1;
        if (!empty($pressureSpecs['pressure_light_rear'])) $score += 1;
        if (!empty($pressureSpecs['pressure_max_front'])) $score += 1;
        if (!empty($pressureSpecs['pressure_max_rear'])) $score += 1;

        // Dados técnicos adicionais (3 pontos)
        if (!empty($vehicleData['main_category'])) $score += 1;
        if (!empty($vehicleData['basic_data']['tire_size'])) $score += 1;
        if (!empty($vehicleData['vehicle_features'])) $score += 1;

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
    // MÉTODOS UTILITÁRIOS
    // =======================================================================

    /**
     * Extrair informações do veículo da URL do WordPress
     */
    public function extractVehicleInfoFromUrl(): ?array
    {
        if (empty($this->wordpress_url)) {
            return null;
        }

        // Padrão: pneu-honda-bros-160-2025
        preg_match('/pneu-([^-]+)-(.+)-(\d{4})/', $this->wordpress_url, $matches);
        
        if (count($matches) >= 4) {
            return [
                'make' => ucfirst($matches[1]),
                'model' => str_replace('-', ' ', ucwords($matches[2], '-')),
                'year' => (int) $matches[3]
            ];
        }

        return null;
    }

    /**
     * Determinar template baseado na categoria
     */
    public function getTemplateType(): string
    {
        return match($this->main_category) {
            'motorcycle', 'motorcycle_street', 'motorcycle_scooter' => self::TEMPLATE_MOTORCYCLE,
            'pickup', 'truck' => self::TEMPLATE_PICKUP,
            'car_electric' => self::TEMPLATE_ELECTRIC,
            default => self::TEMPLATE_CAR
        };
    }

    /**
     * Verificar se está pronto para a próxima fase
     */
    public function isReadyForNextPhase(): bool
    {
        return match($this->enrichment_phase) {
            self::PHASE_PENDING, self::PHASE_VEHICLE_MATCHED => !empty($this->vehicle_data_id),
            self::PHASE_VEHICLE_ENRICHED => !empty($this->vehicle_basic_data),
            self::PHASE_ARTICLE_GENERATED => !empty($this->generated_article),
            self::PHASE_CLAUDE_PROCESSING => false, // Aguardar conclusão
            default => false
        };
    }

    /**
     * Verificar se pode ser reprocessado
     */
    public function canBeReprocessed(): bool
    {
        return $this->enrichment_phase === self::PHASE_FAILED || 
               ($this->processing_attempts < 3 && !empty($this->last_error));
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
        $pendingMapping = self::pendingVehicleMapping()->count();
        $readyForArticles = self::readyForArticleGeneration()->count();
        $readyForClaude = self::readyForClaudeRefinement()->count();
        $processingClaude = self::processingByClaude()->count();
        $completed = self::completed()->count();
        $failed = self::failed()->count();

        return [
            'total' => $total,
            'pending_mapping' => $pendingMapping,
            'pending_articles' => $readyForArticles,
            'pending_claude' => $readyForClaude,
            'processing_claude' => $processingClaude,
            'completed' => $completed,
            'failed' => $failed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'success_rate' => $total > 0 ? round((($completed + $readyForClaude + $processingClaude) / $total) * 100, 2) : 0
        ];
    }

    /**
     * Estatísticas por categoria
     */
    public static function getStatsByCategory(): array
    {
        return self::selectRaw('main_category, enrichment_phase, COUNT(*) as count')
            ->groupBy(['main_category', 'enrichment_phase'])
            ->orderBy('main_category')
            ->get()
            ->groupBy('main_category')
            ->toArray();
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