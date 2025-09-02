<?php

namespace Src\ContentGeneration\TireCalibration\Domain\Entities;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Src\VehicleData\Domain\Entities\VehicleData;
use Carbon\Carbon;

/**
* TireCalibration Model - V4 com Refinamento em Duas Fases
* 
* EVOLUÇÃO DO PROCESSO:
* FASE 1+2: Script gera estrutura técnica (generated_article)
* FASE 3A: Claude refina conteúdo editorial (introdução, FAQs, meta_description)
* FASE 3B: Claude refina especificações técnicas (versões, tabelas)
* RESULTADO: article_refined final combinando ambas as fases
* 
* @property string $generated_article                 // String estruturada (Fase 2 - script)
* @property array $article_refined                   // JSON final refinado (Fase 3A + 3B)
* @property array $claude_phase_3a_enhancements     // Melhorias Fase 3A (editorial)
* @property array $claude_phase_3b_enhancements     // Melhorias Fase 3B (técnico)
* @property array $claude_enhancements              // Compatibilidade - merge de 3A + 3B
* @property string $claude_refinement_version       // v4_phase_3a, v4_phase_3b, v4_completed
* @property Carbon $phase_3a_completed_at           // Timestamp Fase 3A
* @property Carbon $phase_3b_completed_at           // Timestamp Fase 3B
* 
* @author Claude Sonnet 4
* @version 4.0 - Dual Phase Claude Refinement
*/
class TireCalibration extends Model
{
   use HasFactory;

   protected $connection = 'mongodb';
   protected $table = 'tire_calibrations';
   protected $guarded = ['_id'];

   protected $casts = [
       // Campos temporais originais
       'blog_modified_time' => 'datetime',
       'blog_published_time' => 'datetime',

       // Dados mapeados do VehicleData
       'vehicle_basic_data' => 'array',
       'pressure_specifications' => 'array',
       'vehicle_features' => 'array',

       // Timestamps de processamento original
       'article_generated_at' => 'datetime',
       'claude_processing_started_at' => 'datetime',
       'claude_completed_at' => 'datetime',
       'last_claude_processing' => 'datetime',

       // V4: Timestamps das fases específicas
       'phase_3a_completed_at' => 'datetime',
       'phase_3b_completed_at' => 'datetime',

       // Artigos e enhancements
       'article_refined' => 'array',                    // Resultado final
       'claude_phase_3a_enhancements' => 'array',      // Editorial (V4)
       'claude_phase_3b_enhancements' => 'array',      // Técnico (V4)
       'claude_enhancements' => 'array',               // Compatibilidade (merge)

       // Controle de versão e histórico
       'claude_refinement_version' => 'string',
       'claude_processing_history' => 'array',

       // Métricas e scores
       'claude_improvement_score' => 'float',
       'claude_api_calls' => 'integer',
       'data_completeness_score' => 'float',
       'content_quality_score' => 'float',
       'seo_score' => 'float',
       'processing_attempts' => 'integer',

    //    'generated_article' => 'array',

       'created_at' => 'datetime',
       'updated_at' => 'datetime',
   ];

   // ========================================================================
   // CONSTANTES DE FASE V4 - Duas Fases Claude
   // ========================================================================

   // Fases originais mantidas para compatibilidade
   const PHASE_PENDING = 'pending';
   const PHASE_VEHICLE_ENRICHED = 'vehicle_enriched';
   const PHASE_ARTICLE_GENERATED = 'article_generated';
   const PHASE_COMPLETED = 'completed';
   const PHASE_FAILED = 'failed';

   // V4: Fases específicas do refinamento Claude
   const PHASE_CLAUDE_3A_PROCESSING = 'claude_3a_processing';
   const PHASE_CLAUDE_3A_COMPLETED = 'claude_3a_completed';
   const PHASE_CLAUDE_3B_PROCESSING = 'claude_3b_processing';
   const PHASE_CLAUDE_3B_COMPLETED = 'claude_3b_completed';

   // Aliases para compatibilidade com código existente
   const PHASE_CLAUDE_PROCESSING = 'claude_3a_processing';  // Aponta para 3A
   const PHASE_CLAUDE_COMPLETED = 'claude_3b_completed';    // Aponta para 3B final

   // ========================================================================
   // MÉTODOS PARA FASE 3A - CONTEÚDO EDITORIAL
   // ========================================================================

   /**
    * Verificar se precisa de refinamento Fase 3A (Editorial)
    */
   public function needsClaudePhase3A(): bool
   {
       return $this->enrichment_phase === self::PHASE_ARTICLE_GENERATED 
           && empty($this->claude_phase_3a_enhancements)
           && !empty($this->generated_article);
   }

   /**
    * Marcar início do processamento Fase 3A
    */
   public function startClaudePhase3A(): void
   {
       $this->update([
           'enrichment_phase' => self::PHASE_CLAUDE_3A_PROCESSING,
           'claude_processing_started_at' => now(),
           'processing_attempts' => ($this->processing_attempts ?? 0) + 1,
           'claude_api_calls' => ($this->claude_api_calls ?? 0) + 1,
           'claude_refinement_version' => 'v4_phase_3a',
           'last_error' => null,
       ]);
   }

   /**
    * Finalizar processamento Fase 3A com enhancements editoriais
    */
   public function completeClaudePhase3A(array $phase3aEnhancements): void
   {
       // Adicionar ao histórico de processamento
       $processingHistory = $this->claude_processing_history ?? [];
       $processingHistory[] = [
           'phase' => '3A_Editorial',
           'processed_at' => now()->toISOString(),
           'model_used' => 'claude-3-7-sonnet-20250219',
           'enhancement_areas' => array_keys($phase3aEnhancements),
           'processing_time_seconds' => $this->calculateProcessingTime(),
           'api_calls_in_session' => 1,
       ];

       $this->update([
           'enrichment_phase' => self::PHASE_CLAUDE_3A_COMPLETED,
           'phase_3a_completed_at' => now(),
           'last_claude_processing' => now(),
           
           // Armazenar enhancements da Fase 3A
           'claude_phase_3a_enhancements' => $phase3aEnhancements,
           'claude_processing_history' => $processingHistory,
           'claude_refinement_version' => 'v4_phase_3a_completed',
           
           'last_error' => null,
       ]);
   }

   // ========================================================================
   // MÉTODOS PARA FASE 3B - ESPECIFICAÇÕES TÉCNICAS
   // ========================================================================

   /**
    * Verificar se precisa de refinamento Fase 3B (Técnico)
    */
   public function needsClaudePhase3B(): bool
   {
       return $this->enrichment_phase === self::PHASE_CLAUDE_3A_COMPLETED
           && empty($this->claude_phase_3b_enhancements)
           && !empty($this->claude_phase_3a_enhancements);
   }

   /**
    * Marcar início do processamento Fase 3B
    */
   public function startClaudePhase3B(): void
   {
       $this->update([
           'enrichment_phase' => self::PHASE_CLAUDE_3B_PROCESSING,
           'claude_processing_started_at' => now(),
           'processing_attempts' => ($this->processing_attempts ?? 0) + 1,
           'claude_api_calls' => ($this->claude_api_calls ?? 0) + 1,
           'claude_refinement_version' => 'v4_phase_3b',
           'last_error' => null,
       ]);
   }

   /**
    * Finalizar processamento Fase 3B e gerar article_refined final
    */
   public function completeClaudePhase3B(array $phase3bEnhancements): void
   {
       // Gerar artigo final combinando ambas as fases
       $finalArticle = $this->generateFinalRefinedArticle($phase3bEnhancements);

       // Adicionar ao histórico de processamento
       $processingHistory = $this->claude_processing_history ?? [];
       $processingHistory[] = [
           'phase' => '3B_Technical',
           'processed_at' => now()->toISOString(),
           'model_used' => 'claude-3-7-sonnet-20250219',
           'enhancement_areas' => array_keys($phase3bEnhancements),
           'processing_time_seconds' => $this->calculateProcessingTime(),
           'api_calls_in_session' => 1,
           'final_article_generated' => true,
       ];

       $this->update([
           'enrichment_phase' => self::PHASE_CLAUDE_COMPLETED,
           'phase_3b_completed_at' => now(),
           'claude_completed_at' => now(),
           'last_claude_processing' => now(),
           
           // Armazenar dados da Fase 3B
           'claude_phase_3b_enhancements' => $phase3bEnhancements,
           'article_refined' => $finalArticle,
           'claude_processing_history' => $processingHistory,
           'claude_refinement_version' => 'v4_completed',
           
           // Manter compatibilidade com estrutura V3
           'claude_enhancements' => array_merge(
               $this->claude_phase_3a_enhancements ?? [],
               $phase3bEnhancements
           ),
           
           // Recalcular scores finais
           'claude_improvement_score' => $this->calculateV4ImprovementScore($phase3bEnhancements),
           'content_quality_score' => $this->calculateContentQuality($finalArticle),
           'seo_score' => $this->calculateSeoScore($finalArticle),
           
           'last_error' => null,
       ]);
   }

   // ========================================================================
   // GERAÇÃO DO ARTIGO FINAL COMBINADO
   // ========================================================================

   /**
    * Gerar article_refined final combinando Fase 3A + Fase 3B
    */
   private function generateFinalRefinedArticle(array $phase3bEnhancements): array
   {
       // Obter artigo base do generated_article
       $baseArticle = $this->extractBaseArticle($this->generated_article);
       
       if (empty($baseArticle)) {
           throw new \Exception('Artigo base não encontrado para combinar fases');
       }

       // Aplicar melhorias da Fase 3A (Editorial)
       if (!empty($this->claude_phase_3a_enhancements)) {
           $baseArticle = $this->applyPhase3AEnhancements($baseArticle, $this->claude_phase_3a_enhancements);
       }
       
       // Aplicar melhorias da Fase 3B (Técnico)
       $baseArticle = $this->applyPhase3BEnhancements($baseArticle, $phase3bEnhancements);
       
       // Adicionar metadata de enhancement V4
       $baseArticle['enhancement_metadata'] = [
           'enhanced_by' => 'claude-api-v4-dual-phase',
           'enhanced_at' => now()->toISOString(),
           'enhanced_areas' => array_merge(
               array_keys($this->claude_phase_3a_enhancements ?? []),
               array_keys($phase3bEnhancements)
           ),
           'model_used' => 'claude-3-7-sonnet-20250219',
           'versions_generated' => $this->extractVersionNames($phase3bEnhancements),
           'validation_passed' => true,
           
           // Timestamps das fases
           'phase_3a_completed_at' => $this->phase_3a_completed_at?->toISOString(),
           'phase_3b_completed_at' => now()->toISOString(),
           
           // Estatísticas do processamento dual
           'total_api_calls' => 2, // 3A + 3B
           'total_processing_time' => $this->calculateTotalDualPhaseTime(),
           'dual_phase_success' => true,
       ];

       return $baseArticle;
   }

   /**
    * Aplicar melhorias da Fase 3A - Conteúdo Editorial
    */
   private function applyPhase3AEnhancements(array $article, array $phase3aEnhancements): array
   {
       // Atualizar SEO meta_description (sem pressões PSI)
       if (isset($phase3aEnhancements['meta_description'])) {
           if (!isset($article['seo_data'])) {
               $article['seo_data'] = [];
           }
           $article['seo_data']['meta_description'] = $phase3aEnhancements['meta_description'];
       }

       // Garantir que content existe
       if (!isset($article['content'])) {
           $article['content'] = [];
       }

       // Atualizar seções editoriais
       $editorialSections = ['introducao', 'consideracoes_finais', 'perguntas_frequentes'];
       
       foreach ($editorialSections as $section) {
           if (isset($phase3aEnhancements[$section])) {
               $article['content'][$section] = $phase3aEnhancements[$section];
           }
       }

       return $article;
   }

   /**
    * Aplicar melhorias da Fase 3B - Especificações Técnicas
    */
   private function applyPhase3BEnhancements(array $article, array $phase3bEnhancements): array
   {
       // Garantir que content existe
       if (!isset($article['content'])) {
           $article['content'] = [];
       }

       // Atualizar seções técnicas
       $technicalSections = ['especificacoes_por_versao', 'tabela_carga_completa'];
       
       foreach ($technicalSections as $section) {
           if (isset($phase3bEnhancements[$section])) {
               $article['content'][$section] = $phase3bEnhancements[$section];
           }
       }

       // Aplicar outras seções técnicas se existirem
       $otherTechnicalSections = [
           'localizacao_etiqueta', 'condicoes_especiais', 'conversao_unidades',
           'cuidados_recomendacoes', 'impacto_pressao'
       ];

       foreach ($otherTechnicalSections as $section) {
           if (isset($phase3bEnhancements[$section])) {
               $article['content'][$section] = $phase3bEnhancements[$section];
           }
       }

       return $article;
   }

   // ========================================================================
   // MÉTODOS DE VERIFICAÇÃO E ESTADO
   // ========================================================================

   /**
    * Verificar se está em alguma fase de processamento Claude
    */
   public function isProcessingClaude(): bool
   {
       return in_array($this->enrichment_phase, [
           self::PHASE_CLAUDE_3A_PROCESSING,
           self::PHASE_CLAUDE_3B_PROCESSING,
           // Compatibilidade
           self::PHASE_CLAUDE_PROCESSING,
       ]);
   }

   /**
    * Verificar se completou todo o processo Claude (ambas as fases)
    */
   public function isClaudeCompleted(): bool
   {
       return $this->enrichment_phase === self::PHASE_CLAUDE_COMPLETED
           && !empty($this->claude_phase_3a_enhancements)
           && !empty($this->claude_phase_3b_enhancements)
           && !empty($this->article_refined);
   }

   /**
    * Obter progresso atual das fases Claude
    */
   public function getClaudeProgress(): array
   {
       return [
           'phase_3a_completed' => !empty($this->claude_phase_3a_enhancements),
           'phase_3b_completed' => !empty($this->claude_phase_3b_enhancements),
           'final_article_ready' => !empty($this->article_refined),
           'current_phase' => $this->enrichment_phase,
           'version' => $this->claude_refinement_version,
           'total_api_calls' => $this->claude_api_calls ?? 0,
       ];
   }

   /**
    * Obter conteúdo final considerando o progresso das fases
    */
   public function getFinalContent(): array
   {
       // Se completou ambas as fases, usar article_refined
       if ($this->isClaudeCompleted()) {
           return $this->article_refined;
       }

       // Se completou apenas Fase 3A, aplicar parcialmente
       if (!empty($this->claude_phase_3a_enhancements) && !empty($this->generated_article)) {
           $baseArticle = $this->extractBaseArticle($this->generated_article);
           return $this->applyPhase3AEnhancements($baseArticle, $this->claude_phase_3a_enhancements);
       }

       // Fallback para generated_article ou claude_enhancements (compatibilidade V3)
       if (!empty($this->article_refined)) {
           return $this->article_refined;
       }

       if (!empty($this->generated_article)) {
           $baseArticle = $this->extractBaseArticle($this->generated_article);
           
           // Aplicar claude_enhancements se existir (compatibilidade V3)
           if (!empty($this->claude_enhancements)) {
               return $this->mergeClaudeEnhancements($baseArticle, $this->claude_enhancements);
           }
           
           return $baseArticle;
       }

       return [];
   }

   // ========================================================================
   // MÉTODOS DE CÁLCULO E UTILITÁRIOS
   // ========================================================================

   /**
    * Calcular score de melhoria V4 considerando ambas as fases
    */
   private function calculateV4ImprovementScore(array $phase3bEnhancements): float
   {
       $score = 6.0; // Base V4

       // Pontuação Fase 3A (Editorial)
       if (!empty($this->claude_phase_3a_enhancements)) {
           $phase3aAreas = array_keys($this->claude_phase_3a_enhancements);
           
           // +1 para cada área editorial refinada
           $score += count($phase3aAreas) * 0.5;
           
           // +1 se meta_description foi melhorada
           if (in_array('meta_description', $phase3aAreas)) {
               $score += 1.0;
           }
           
           // +1 se FAQs foram contextualizadas
           if (in_array('perguntas_frequentes', $phase3aAreas)) {
               $faqs = $this->claude_phase_3a_enhancements['perguntas_frequentes'] ?? [];
               if (count($faqs) >= 5) {
                   $score += 1.0;
               }
           }
       }

       // Pontuação Fase 3B (Técnica)
       $phase3bAreas = array_keys($phase3bEnhancements);
       
       // +1 para cada área técnica refinada
       $score += count($phase3bAreas) * 0.5;
       
       // +1 se versões específicas foram geradas
       if (in_array('especificacoes_por_versao', $phase3bAreas)) {
           $versions = $phase3bEnhancements['especificacoes_por_versao'] ?? [];
           if (count($versions) >= 3) {
               $score += 1.0;
           }
       }

       // Bônus por completar ambas as fases
       $score += 1.0;

       return min(10.0, round($score, 2));
   }

   /**
    * Calcular tempo total do processamento dual-phase
    */
   private function calculateTotalDualPhaseTime(): int
   {
       $phase3aTime = 0;
       $phase3bTime = 0;

       if ($this->phase_3a_completed_at && $this->claude_processing_started_at) {
           // Assumir que 3A começou no claude_processing_started_at
           $phase3aTime = $this->claude_processing_started_at->diffInSeconds($this->phase_3a_completed_at);
       }

       if ($this->phase_3b_completed_at && $this->phase_3a_completed_at) {
           // 3B começou após 3A
           $phase3bTime = $this->phase_3a_completed_at->diffInSeconds($this->phase_3b_completed_at);
       }

       return $phase3aTime + $phase3bTime;
   }

   /**
    * Calcular tempo de processamento da fase atual
    */
   private function calculateProcessingTime(): int
   {
       if (!$this->claude_processing_started_at) {
           return 0;
       }

       return now()->diffInSeconds($this->claude_processing_started_at);
   }

   /**
    * Extrair dados do artigo base (compatibilidade)
    */
   private function extractBaseArticle($generatedArticle): array
   {
       if (is_array($generatedArticle)) {
           return $generatedArticle;
       }

       if (is_string($generatedArticle)) {
           $decoded = json_decode($generatedArticle, true);
           if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
               return $decoded;
           }
       }

       return [];
   }

   /**
    * Extrair nomes de versões de enhancements técnicos
    */
   private function extractVersionNames(array $enhancements): array
   {
       $versions = [];

       if (isset($enhancements['especificacoes_por_versao'])) {
           foreach ($enhancements['especificacoes_por_versao'] as $spec) {
               $versions[] = $spec['versao'] ?? 'N/A';
           }
       }

       return $versions;
   }

   /**
    * Merge claude_enhancements V3 (compatibilidade)
    */
   private function mergeClaudeEnhancements(array $baseArticle, array $enhancements): array
   {
       if (!isset($baseArticle['content'])) {
           $baseArticle['content'] = [];
       }

       foreach ($enhancements as $section => $content) {
           if ($section === 'meta_description') {
               $baseArticle['seo_data']['meta_description'] = $content;
           } else {
               $baseArticle['content'][$section] = $content;
           }
       }

       return $baseArticle;
   }

   // ========================================================================
   // MÉTODOS DE FALLBACK E COMPATIBILIDADE V3
   // ========================================================================

   /**
    * Marcar como falhou (compatibilidade V3)
    */
   public function markFailed(string $errorMessage): void
   {
       $processingHistory = $this->claude_processing_history ?? [];
       $processingHistory[] = [
           'failed_at' => now()->toISOString(),
           'error_message' => $errorMessage,
           'processing_attempt' => $this->processing_attempts ?? 0,
           'phase_when_failed' => $this->enrichment_phase,
           'version' => $this->claude_refinement_version ?? 'unknown',
       ];

       $this->update([
           'enrichment_phase' => self::PHASE_FAILED,
           'last_error' => $errorMessage,
           'failed_at' => now(),
           'claude_processing_history' => $processingHistory,
       ]);
   }

   /**
    * Verificar se pode tentar novamente (compatibilidade V3)
    */
   public function canRetryProcessing(): bool
   {
       if ($this->enrichment_phase === self::PHASE_FAILED) {
           $failedAt = $this->failed_at ?? $this->updated_at;
           return $failedAt->diffInHours(now()) >= 1;
       }

       if ($this->isProcessingClaude()) {
           $startedAt = $this->claude_processing_started_at ?? $this->updated_at;
           return $startedAt->diffInMinutes(now()) >= 30;
       }

       return false;
   }

   /**
    * Reset para retry (compatibilidade V3)
    */
   public function resetForRetry(): void
   {
       // Determinar para qual fase resetar
       $targetPhase = self::PHASE_ARTICLE_GENERATED;
       
       // Se Fase 3A foi completada, resetar para 3B
       if (!empty($this->claude_phase_3a_enhancements)) {
           $targetPhase = self::PHASE_CLAUDE_3A_COMPLETED;
       }

       $this->update([
           'enrichment_phase' => $targetPhase,
           'claude_processing_started_at' => null,
           'last_error' => null,
           'processing_attempts' => ($this->processing_attempts ?? 0) + 1,
       ]);
   }

   // ========================================================================
   // SCOPES E ESTATÍSTICAS
   // ========================================================================

   /**
    * Estatísticas de processamento V4
    */
   public static function getProcessingStats(): array
   {
       $total = self::count();
       $pending = self::pending()->count();
       
       // V4: Estatísticas por fase específica
       $readyFor3A = self::where('enrichment_phase', self::PHASE_ARTICLE_GENERATED)->count();
       $processing3A = self::where('enrichment_phase', self::PHASE_CLAUDE_3A_PROCESSING)->count();
       $completed3A = self::where('enrichment_phase', self::PHASE_CLAUDE_3A_COMPLETED)->count();
       
       $readyFor3B = $completed3A; // 3A completed = ready for 3B
       $processing3B = self::where('enrichment_phase', self::PHASE_CLAUDE_3B_PROCESSING)->count();
       $completed3B = self::where('enrichment_phase', self::PHASE_CLAUDE_COMPLETED)->count();
       
       $failed = self::failed()->count();
       
       // Estatísticas Claude específicas
       $totalApiCalls = self::sum('claude_api_calls');
       $avgImprovementScore = self::whereNotNull('claude_improvement_score')->avg('claude_improvement_score');
       
       // V4: Dual-phase específico
       $dualPhaseCompleted = self::whereNotNull('claude_phase_3a_enhancements')
           ->whereNotNull('claude_phase_3b_enhancements')
           ->count();

       return [
           'total' => $total,
           'pending' => $pending,
           'failed' => $failed,
           
           // V4: Estatísticas por fase
           'ready_for_3a' => $readyFor3A,
           'processing_3a' => $processing3A,
           'completed_3a' => $completed3A,
           'ready_for_3b' => $readyFor3B,
           'processing_3b' => $processing3B,
           'completed_3b' => $completed3B,
           
           // Compatibilidade V3
           'pending_claude' => $readyFor3A + $readyFor3B,
           'completed' => $completed3B,
           
           // Rates de sucesso
           'completion_rate' => $total > 0 ? round(($completed3B / $total) * 100, 2) : 0,
           'dual_phase_success_rate' => $readyFor3A > 0 ? round(($dualPhaseCompleted / $readyFor3A) * 100, 2) : 0,
           
           // Stats Claude
           'total_api_calls' => $totalApiCalls,
           'avg_improvement_score' => round($avgImprovementScore ?? 0, 2),
           'dual_phase_completed' => $dualPhaseCompleted,
           
           // V4 specific
           'version' => 'v4_dual_phase',
           'phase_3a_efficiency' => $readyFor3A > 0 ? round((($completed3A + $processing3A) / $readyFor3A) * 100, 2) : 0,
           'phase_3b_efficiency' => $readyFor3B > 0 ? round((($completed3B + $processing3B) / $readyFor3B) * 100, 2) : 0,
       ];
   }

   // Scopes V4 específicos
   public function scopeReadyForClaudePhase3A($query)
   {
       return $query->where('enrichment_phase', self::PHASE_ARTICLE_GENERATED)
           ->whereNotNull('generated_article')
           ->whereNull('claude_phase_3a_enhancements');
   }

   public function scopeReadyForClaudePhase3B($query)
   {
       return $query->where('enrichment_phase', self::PHASE_CLAUDE_3A_COMPLETED)
           ->whereNotNull('claude_phase_3a_enhancements')
           ->whereNull('claude_phase_3b_enhancements');
   }

   public function scopeDualPhaseCompleted($query)
   {
       return $query->where('enrichment_phase', self::PHASE_CLAUDE_COMPLETED)
           ->whereNotNull('claude_phase_3a_enhancements')
           ->whereNotNull('claude_phase_3b_enhancements')
           ->whereNotNull('article_refined');
   }

   // Scopes originais mantidos para compatibilidade
   public function scopePending($query) 
   {
       return $query->where('enrichment_phase', self::PHASE_PENDING);
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

   // Compatibilidade V3
   public function scopeReadyForClaudeRefinement($query)
   {
       return $this->scopeReadyForClaudePhase3A($query);
   }

   // ========================================================================
   // MÉTODOS AUXILIARES PARA CÁLCULOS
   // ========================================================================

   /**
     * Calcular qualidade do conteúdo baseado no artigo final
     */
    protected function calculateContentQuality(array $article): float
    {
        $score = 0;
        $maxScore = 10;

        // Estrutura básica (2 pontos)
        if (!empty($article['title']) && !empty($article['seo_data'])) {
            $score += 2;
        }

        // Conteúdo editorial (3 pontos)
        $content = $article['content'] ?? [];
        
        if (!empty($content['introducao']) && str_word_count($content['introducao']) >= 150) {
            $score += 1;
        }
        
        if (!empty($content['consideracoes_finais']) && str_word_count($content['consideracoes_finais']) >= 120) {
            $score += 1;
        }
        
        if (!empty($content['perguntas_frequentes']) && count($content['perguntas_frequentes']) >= 5) {
            $score += 1;
        }

        // Especificações técnicas (3 pontos)
        if (!empty($content['especificacoes_por_versao']) && count($content['especificacoes_por_versao']) >= 3) {
            $score += 1.5;
        }
        
        if (!empty($content['tabela_carga_completa'])) {
            $score += 1.5;
        }

        // SEO otimizado (2 pontos)
        $seoData = $article['seo_data'] ?? [];
        
        if (!empty($seoData['meta_description']) && strlen($seoData['meta_description']) >= 140) {
            $score += 1;
        }
        
        if (!empty($seoData['primary_keyword'])) {
            $score += 1;
        }

        return min($maxScore, round($score, 1));
    }

    /**
     * Calcular score SEO baseado no artigo final
     */
    protected function calculateSeoScore(array $article): float
    {
        $score = 0;
        $maxScore = 10;
        
        $seoData = $article['seo_data'] ?? [];

        // Title otimizado (2 pontos)
        $title = $seoData['page_title'] ?? '';
        if (!empty($title)) {
            $titleLength = strlen($title);
            if ($titleLength >= 50 && $titleLength <= 65) {
                $score += 2;
            } elseif ($titleLength >= 40 && $titleLength <= 75) {
                $score += 1;
            }
        }

        // Meta description otimizada (3 pontos)
        $metaDesc = $seoData['meta_description'] ?? '';
        if (!empty($metaDesc)) {
            $metaLength = strlen($metaDesc);
            if ($metaLength >= 140 && $metaLength <= 165) {
                $score += 2;
                
                // Bonus: sem pressões PSI na meta description (V4)
                if (!preg_match('/\d+\s*PSI/i', $metaDesc)) {
                    $score += 1;
                }
            } elseif ($metaLength >= 120 && $metaLength <= 180) {
                $score += 1;
            }
        }

        // Keywords estruturadas (2 pontos)
        if (!empty($seoData['primary_keyword'])) {
            $score += 1;
        }
        
        if (!empty($seoData['secondary_keywords']) && count($seoData['secondary_keywords']) >= 3) {
            $score += 1;
        }

        // Estrutura H1/OG (2 pontos)
        if (!empty($seoData['h1'])) {
            $score += 1;
        }
        
        if (!empty($seoData['og_title']) && !empty($seoData['og_description'])) {
            $score += 1;
        }

        // Canonical URL (1 ponto)
        if (!empty($seoData['canonical_url'])) {
            $score += 1;
        }

        return min($maxScore, round($score, 1));
    }

    // ========================================================================
    // MÉTODOS ESTÁTICOS E UTILITÁRIOS FINAIS
    // ========================================================================

    /**
     * Obter próximo registro para Fase 3A
     */
    public static function getNextForPhase3A(): ?self
    {
        return self::readyForClaudePhase3A()
            ->orderBy('updated_at', 'asc')
            ->first();
    }

    /**
     * Obter próximo registro para Fase 3B
     */
    public static function getNextForPhase3B(): ?self
    {
        return self::readyForClaudePhase3B()
            ->orderBy('phase_3a_completed_at', 'asc')
            ->first();
    }

    /**
     * Limpar registros orfãos (processando há muito tempo)
     */
    public static function cleanupStuckProcessing(): int
    {
        $cutoffTime = now()->subHours(2);
        $stuck = [];

        // Fase 3A travada há mais de 2 horas
        $stuck3A = self::where('enrichment_phase', self::PHASE_CLAUDE_3A_PROCESSING)
            ->where('claude_processing_started_at', '<', $cutoffTime)
            ->get();

        // Fase 3B travada há mais de 2 horas
        $stuck3B = self::where('enrichment_phase', self::PHASE_CLAUDE_3B_PROCESSING)
            ->where('claude_processing_started_at', '<', $cutoffTime)
            ->get();

        $cleanedCount = 0;

        foreach ($stuck3A as $record) {
            $record->update([
                'enrichment_phase' => self::PHASE_ARTICLE_GENERATED,
                'claude_processing_started_at' => null,
                'last_error' => 'Cleaned up - stuck in Phase 3A processing',
            ]);
            $cleanedCount++;
        }

        foreach ($stuck3B as $record) {
            $record->update([
                'enrichment_phase' => self::PHASE_CLAUDE_3A_COMPLETED,
                'claude_processing_started_at' => null,
                'last_error' => 'Cleaned up - stuck in Phase 3B processing',
            ]);
            $cleanedCount++;
        }

        if ($cleanedCount > 0) {
            Log::info('TireCalibration: Limpeza de registros travados', [
                'cleaned_count' => $cleanedCount,
                'cutoff_time' => $cutoffTime->toISOString()
            ]);
        }

        return $cleanedCount;
    }

    /**
     * Obter estatísticas detalhadas V4
     */
    public static function getDetailedV4Stats(): array
    {
        $baseStats = self::getProcessingStats();
        
        // Estatísticas de tempo médio por fase
        $avg3ATime = self::whereNotNull('phase_3a_completed_at')
            ->whereNotNull('claude_processing_started_at')
            ->get()
            ->avg(function ($record) {
                return $record->claude_processing_started_at->diffInSeconds($record->phase_3a_completed_at);
            });

        $avg3BTime = self::whereNotNull('phase_3b_completed_at')
            ->whereNotNull('phase_3a_completed_at')
            ->get()
            ->avg(function ($record) {
                return $record->phase_3a_completed_at->diffInSeconds($record->phase_3b_completed_at);
            });

        // Análise de qualidade por fase
        $qualityAnalysis = [
            'avg_content_quality' => self::whereNotNull('content_quality_score')->avg('content_quality_score'),
            'avg_seo_score' => self::whereNotNull('seo_score')->avg('seo_score'),
            'high_quality_articles' => self::where('content_quality_score', '>=', 8.0)->count(),
            'seo_optimized_articles' => self::where('seo_score', '>=', 8.0)->count(),
        ];

        // Top categorias por sucesso
        $categorySuccess = self::dualPhaseCompleted()
            ->groupBy('main_category')
            ->selectRaw('main_category, count(*) as completed')
            ->pluck('completed', 'main_category')
            ->toArray();

        return array_merge($baseStats, [
            // Timing analysis
            'avg_phase_3a_time_seconds' => round($avg3ATime ?? 0),
            'avg_phase_3b_time_seconds' => round($avg3BTime ?? 0),
            'avg_total_dual_phase_time_seconds' => round(($avg3ATime ?? 0) + ($avg3BTime ?? 0)),
            
            // Quality analysis
            'quality_analysis' => $qualityAnalysis,
            
            // Category breakdown
            'category_success_breakdown' => $categorySuccess,
            
            // API usage optimization
            'api_calls_per_completion' => $baseStats['total_api_calls'] > 0 && $baseStats['dual_phase_completed'] > 0 
                ? round($baseStats['total_api_calls'] / $baseStats['dual_phase_completed'], 2) 
                : 0,
                
            // Error analysis
            'error_analysis' => [
                'failed_in_3a' => self::where('last_error', 'LIKE', '%Phase 3A%')->count(),
                'failed_in_3b' => self::where('last_error', 'LIKE', '%Phase 3B%')->count(),
                'api_errors' => self::where('last_error', 'LIKE', '%API%')->count(),
                'validation_errors' => self::where('last_error', 'LIKE', '%validation%')->count(),
            ],
        ]);
    }

    /**
     * Migration helper - converter registros V3 para V4
     */
    public static function migrateV3ToV4(int $limit = 100): int
    {
        $v3Records = self::whereNotNull('claude_enhancements')
            ->whereNull('claude_phase_3a_enhancements')
            ->whereNull('claude_phase_3b_enhancements')
            ->where('enrichment_phase', self::PHASE_CLAUDE_COMPLETED)
            ->limit($limit)
            ->get();

        $migratedCount = 0;

        foreach ($v3Records as $record) {
            try {
                $enhancements = $record->claude_enhancements;
                
                // Dividir enhancements em editoriais e técnicos
                $editorialSections = ['introducao', 'consideracoes_finais', 'perguntas_frequentes', 'meta_description'];
                $technicalSections = ['especificacoes_por_versao', 'tabela_carga_completa'];
                
                $phase3A = [];
                $phase3B = [];
                
                foreach ($enhancements as $section => $content) {
                    if (in_array($section, $editorialSections)) {
                        $phase3A[$section] = $content;
                    } elseif (in_array($section, $technicalSections)) {
                        $phase3B[$section] = $content;
                    }
                }

                // Atualizar registro
                $record->update([
                    'claude_phase_3a_enhancements' => $phase3A,
                    'claude_phase_3b_enhancements' => $phase3B,
                    'claude_refinement_version' => 'v4_migrated_from_v3',
                    'phase_3a_completed_at' => $record->claude_completed_at,
                    'phase_3b_completed_at' => $record->claude_completed_at,
                ]);

                $migratedCount++;

            } catch (\Exception $e) {
                Log::error('Erro na migração V3->V4', [
                    'record_id' => $record->_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Migração V3->V4 concluída', [
            'migrated_count' => $migratedCount,
            'target_limit' => $limit
        ]);

        return $migratedCount;
    }

    /**
     * Health check do sistema V4
     */
    public static function healthCheckV4(): array
    {
        $stats = self::getProcessingStats();
        
        return [
            'system_health' => 'v4_dual_phase',
            'overall_status' => $stats['completion_rate'] > 70 ? 'healthy' : 'needs_attention',
            
            'pipeline_health' => [
                'phase_3a_backlog' => $stats['ready_for_3a'],
                'phase_3b_backlog' => $stats['ready_for_3b'],
                'processing_stuck' => $stats['processing_3a'] + $stats['processing_3b'],
                'bottleneck' => $stats['ready_for_3a'] > $stats['ready_for_3b'] ? 'phase_3a' : 'phase_3b',
            ],
            
            'performance_indicators' => [
                'dual_phase_success_rate' => $stats['dual_phase_success_rate'],
                'avg_improvement_score' => $stats['avg_improvement_score'],
                'api_efficiency' => $stats['total_api_calls'] > 0 ? round($stats['dual_phase_completed'] / ($stats['total_api_calls'] / 2), 2) : 0,
            ],
            
            'recommendations' => self::generateHealthRecommendations($stats),
            'checked_at' => now()->toISOString(),
        ];
    }

    /**
     * Gerar recomendações baseadas nas estatísticas
     */
    private static function generateHealthRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['ready_for_3a'] > 100) {
            $recommendations[] = 'Alto backlog na Fase 3A - considere aumentar frequência do processamento';
        }

        if ($stats['ready_for_3b'] > 50) {
            $recommendations[] = 'Backlog na Fase 3B - verifique se Phase3BCommand está executando corretamente';
        }

        if ($stats['dual_phase_success_rate'] < 80) {
            $recommendations[] = 'Taxa de sucesso dual-phase baixa - investigar falhas na Claude API';
        }

        if (($stats['processing_3a'] + $stats['processing_3b']) > 10) {
            $recommendations[] = 'Muitos registros em processamento - executar cleanup de registros travados';
        }

        if ($stats['avg_improvement_score'] < 7.0) {
            $recommendations[] = 'Score de melhoria baixo - revisar prompts da Claude API';
        }

        return $recommendations;
    }
}