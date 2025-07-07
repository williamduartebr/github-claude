<?php

namespace Src\ContentGeneration\TireSchedule\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\TireDataValidationService;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\ClaudeApiService;

/**
 * 🔧 TireCorrectionOrchestrator - CORRIGIDO
 */
class TireCorrectionOrchestrator
{
    private $validationService;
    private $claudeApiService;
    
    public function __construct(
        TireDataValidationService $validationService,
        ClaudeApiService $claudeApiService
    ) {
        $this->validationService = $validationService;
        $this->claudeApiService = $claudeApiService;
    }

    /**
     * 🎯 Criação inteligente - CORRIGIDO para MongoDB
     */
    public function createIntelligentCorrections(int $limit = 100): array
    {
        $results = [
            'analyzed' => 0,
            'corrections_created' => 0,
            'skipped_no_issues' => 0,
            'skipped_already_exists' => 0,
            'high_priority' => 0,
            'medium_priority' => 0,
            'low_priority' => 0,
            'articles_details' => []
        ];

        try {
            // ✅ CORRIGIDO: Buscar slugs já corrigidos primeiro (compatível com MongoDB)
            $alreadyCorrectedSlugs = ArticleCorrection::whereIn('correction_type', [
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            ])
            ->distinct('article_slug')
            ->pluck('article_slug')
            ->toArray();

            Log::info("📊 Slugs já corrigidos encontrados: " . count($alreadyCorrectedSlugs));

            // ✅ CORRIGIDO: Buscar artigos sem correção (método compatível com MongoDB)
            $query = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft');

            // ✅ Se há slugs corrigidos, excluir eles
            if (!empty($alreadyCorrectedSlugs)) {
                $query->whereNotIn('slug', $alreadyCorrectedSlugs);
            }

            $articles = $query->limit($limit)->get();

            Log::info("📋 Artigos encontrados para análise: " . $articles->count());

            if ($articles->isEmpty()) {
                Log::info('ℹ️ Nenhum artigo encontrado para correção');
                return $results;
            }

            foreach ($articles as $article) {
                $results['analyzed']++;
                
                // ✅ Validação rápida: só prossegue se realmente precisa
                $validation = $this->validationService->validateArticleIntegrity($article);
                
                if (!$validation['needs_any_correction']) {
                    $results['skipped_no_issues']++;
                    continue;
                }

                // ✅ Verificar duplicatas específicas antes de criar
                $existingPressure = ArticleCorrection::where('article_slug', $article->slug)
                    ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                    ->exists();

                $existingTitle = ArticleCorrection::where('article_slug', $article->slug)
                    ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                    ->exists();

                // ✅ Criar correções apenas para problemas específicos
                $correctionsCreated = 0;
                
                if ($validation['needs_pressure_correction'] && !$existingPressure) {
                    $this->createPressureCorrection($article, $validation['pressure_details']);
                    $correctionsCreated++;
                }
                
                if ($validation['needs_title_correction'] && !$existingTitle) {
                    $this->createTitleCorrection($article, $validation['title_details']);
                    $correctionsCreated++;
                }

                if ($correctionsCreated > 0) {
                    $results['corrections_created'] += $correctionsCreated;
                    
                    // Contar por prioridade
                    switch ($validation['overall_priority']) {
                        case 'high':
                            $results['high_priority']++;
                            break;
                        case 'medium':
                            $results['medium_priority']++;
                            break;
                        default:
                            $results['low_priority']++;
                    }
                    
                    $results['articles_details'][] = [
                        'slug' => $article->slug,
                        'vehicle_name' => $article->vehicle_data['vehicle_name'] ?? 'N/A',
                        'priority' => $validation['overall_priority'],
                        'corrections_created' => $correctionsCreated,
                        'pressure_correction' => $validation['needs_pressure_correction'],
                        'title_correction' => $validation['needs_title_correction']
                    ];
                } else {
                    $results['skipped_already_exists']++;
                }
            }

            Log::info("🎯 Criação inteligente concluída", $results);
            return $results;

        } catch (\Exception $e) {
            Log::error("❌ Erro na criação inteligente", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'results' => $results
            ]);
            
            return array_merge($results, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🚀 Processamento não-bloqueante - CORRIGIDO para MongoDB
     */
    public function processAvailableCorrections(int $limit = 3): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped_rate_limit' => 0,
            'pressure_corrections' => 0,
            'title_corrections' => 0,
            'details' => []
        ];

        // ✅ Verificar se API está disponível ANTES de buscar correções
        if (!$this->claudeApiService->canMakeRequest()) {
            $waitTime = $this->claudeApiService->getNextAvailableTime();
            Log::info("⏸️ Claude API não disponível. Aguardar {$waitTime}s");
            
            return array_merge($results, [
                'skipped_rate_limit' => 1,
                'next_available_in' => $waitTime,
                'message' => "API rate limited. Próximo processamento em {$waitTime}s"
            ]);
        }

        try {
            // ✅ CORRIGIDO: Buscar correções por prioridade (compatível com MongoDB)
            $corrections = ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)
                ->whereIn('correction_type', [
                    ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                    ArticleCorrection::TYPE_TITLE_YEAR_FIX
                ])
                ->orderBy('created_at', 'asc') // ✅ Simples: mais antigas primeiro
                ->limit($limit)
                ->get();

            Log::info("📋 Correções encontradas para processamento: " . $corrections->count());

            foreach ($corrections as $correction) {
                // ✅ Verificar API antes de cada processamento
                if (!$this->claudeApiService->canMakeRequest()) {
                    $results['skipped_rate_limit']++;
                    Log::info("⏸️ Rate limit atingido após {$results['processed']} processamentos");
                    break;
                }

                $results['processed']++;
                $correction->markAsProcessing();

                $success = false;
                
                if ($correction->correction_type === ArticleCorrection::TYPE_TIRE_PRESSURE_FIX) {
                    $success = $this->processPressureCorrection($correction);
                    if ($success) $results['pressure_corrections']++;
                } else {
                    $success = $this->processTitleCorrection($correction);
                    if ($success) $results['title_corrections']++;
                }

                if ($success) {
                    $results['successful']++;
                    $results['details'][] = [
                        'id' => $correction->_id,
                        'slug' => $correction->article_slug,
                        'type' => $correction->correction_type,
                        'status' => 'success'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'id' => $correction->_id,
                        'slug' => $correction->article_slug,
                        'type' => $correction->correction_type,
                        'status' => 'failed'
                    ];
                }
            }

            Log::info("🚀 Processamento concluído", $results);
            return $results;

        } catch (\Exception $e) {
            Log::error("❌ Erro no processamento", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'results' => $results
            ]);
            
            return array_merge($results, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🧹 Limpeza inteligente - CORRIGIDO para MongoDB
     */
    public function intelligentCleanup(): array
    {
        $results = [
            'duplicates_removed' => 0,
            'stuck_processing_reset' => 0,
            'old_failures_cleaned' => 0
        ];

        try {
            // ✅ CORRIGIDO: Buscar duplicatas compatível com MongoDB
            $allCorrections = ArticleCorrection::whereIn('correction_type', [
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            ])->get();

            // ✅ Agrupar por slug + tipo para encontrar duplicatas
            $grouped = $allCorrections->groupBy(function($correction) {
                return $correction->article_slug . '|' . $correction->correction_type;
            });

            foreach ($grouped as $key => $corrections) {
                if ($corrections->count() > 1) {
                    // Manter o mais recente, deletar os outros
                    $keepFirst = $corrections->sortByDesc('created_at')->first();
                    $duplicatesToDelete = $corrections->except($keepFirst->_id);

                    foreach ($duplicatesToDelete as $duplicate) {
                        $duplicate->delete();
                        $results['duplicates_removed']++;
                    }
                }
            }

            // ✅ Reset processamentos travados (mais de 2 horas)
            $stuckCount = ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(2))
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            $results['stuck_processing_reset'] = $stuckCount;

            // ✅ Limpar falhas antigas (mais de 24 horas)
            $oldFailuresCount = ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(24))
                ->delete();

            $results['old_failures_cleaned'] = $oldFailuresCount;

            Log::info("🧹 Limpeza inteligente concluída", $results);
            return $results;

        } catch (\Exception $e) {
            Log::error("❌ Erro na limpeza inteligente", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'results' => $results
            ]);
            
            return array_merge($results, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 📊 Obter estatísticas consolidadas - CORRIGIDO
     */
    public function getConsolidatedStats(): array
    {
        try {
            // Estatísticas da API
            $apiStats = $this->claudeApiService->getApiStats();

            // ✅ CORRIGIDO: Criar validation_stats com estrutura correta
            $totalTireArticles = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            // Pegar uma amostra para validação
            $sampleArticles = TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->limit(100)
                ->get();

            $validationResults = $this->validationService->validateBatch($sampleArticles->toArray(), 100);

            // ✅ CORRIGIDO: Garantir que validation_stats tenha todas as chaves esperadas
            $validationStats = [
                'total_articles' => $totalTireArticles,
                'analyzed' => $validationResults['analyzed'] ?? 0,
                'needs_pressure_correction' => $validationResults['needs_pressure_correction'] ?? 0,
                'needs_title_correction' => $validationResults['needs_title_correction'] ?? 0,
                'high_priority' => $validationResults['high_priority'] ?? 0,
                'medium_priority' => $validationResults['medium_priority'] ?? 0,
                'low_priority' => $validationResults['low_priority'] ?? 0,
                'correction_rate' => $validationResults['correction_rate'] ?? 0,
                'correction_coverage' => $totalTireArticles > 0 ? 
                    round(((ArticleCorrection::whereIn('correction_type', [
                        ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
                        ArticleCorrection::TYPE_TITLE_YEAR_FIX
                    ])->distinct('article_slug')->count()) / $totalTireArticles) * 100, 2) : 0
            ];

            // Estatísticas das correções - SIMPLIFICADO para MongoDB
            $correctionStats = [
                'pressure_corrections' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)->count(),
                'title_corrections' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)->count(),
                'pending_total' => ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)->count(),
                'processing_total' => ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)->count(),
                'completed_total' => ArticleCorrection::where('status', ArticleCorrection::STATUS_COMPLETED)->count()
            ];

            return [
                'orchestrator_version' => '1.0_microservices_mongodb_fixed',
                'validation_stats' => $validationStats, // ✅ CORRIGIDO: Estrutura completa
                'api_stats' => $apiStats,
                'correction_stats' => $correctionStats,
                'system_health' => [
                    'validation_service' => 'active',
                    'claude_api_service' => $apiStats['api_available'] ? 'available' : 'rate_limited',
                    'overall_status' => $apiStats['api_available'] ? 'healthy' : 'rate_limited'
                ],
                'generated_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            Log::error("❌ Erro ao obter stats consolidadas", [
                'error' => $e->getMessage()
            ]);

            return [
                'orchestrator_version' => '1.0_microservices_mongodb_fixed',
                'validation_stats' => [
                    'total_articles' => 0,
                    'analyzed' => 0,
                    'needs_pressure_correction' => 0,
                    'needs_title_correction' => 0,
                    'high_priority' => 0,
                    'medium_priority' => 0,
                    'low_priority' => 0,
                    'correction_rate' => 0,
                    'correction_coverage' => 0
                ], // ✅ CORRIGIDO: Fallback com estrutura correta
                'error' => $e->getMessage(),
                'generated_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🎯 Workflow completo otimizado - CORRIGIDO
     */
    public function runOptimizedWorkflow(int $createLimit = 50, int $processLimit = 3): array
    {
        $workflow = [
            'workflow_started_at' => now()->toISOString(),
            'steps' => []
        ];

        try {
            // Passo 1: Criar correções inteligentes
            $workflow['steps']['creation'] = $this->createIntelligentCorrections($createLimit);

            // Passo 2: Processar apenas se API disponível
            $workflow['steps']['processing'] = $this->processAvailableCorrections($processLimit);

            // Passo 3: Limpeza se necessário (10% de chance)
            if (rand(1, 10) === 1) {
                $workflow['steps']['cleanup'] = $this->intelligentCleanup();
            }

            $workflow['workflow_completed_at'] = now()->toISOString();
            $workflow['total_duration_seconds'] = now()->diffInSeconds($workflow['workflow_started_at']);

            Log::info("🎯 Workflow otimizado concluído", [
                'duration' => $workflow['total_duration_seconds'],
                'created' => $workflow['steps']['creation']['corrections_created'] ?? 0,
                'processed' => $workflow['steps']['processing']['successful'] ?? 0
            ]);

            return $workflow;
        } catch (\Exception $e) {
            Log::error("❌ Erro no workflow otimizado", [
                'error' => $e->getMessage()
            ]);

            $workflow['error'] = $e->getMessage();
            $workflow['workflow_completed_at'] = now()->toISOString();
            
            return $workflow;
        }
    }

    // ✅ Métodos privados auxiliares (corrigidos)
    
    private function createPressureCorrection(TempArticle $article, array $validationDetails): void
    {
        $originalData = [
            'title' => $article->title,
            'domain' => $article->domain,
            'vehicle_data' => $article->vehicle_data ?? [],
            'validation_details' => $validationDetails,
            'priority' => $validationDetails['priority'] ?? 'medium',
            'issues_found' => $validationDetails['issues_found'] ?? []
        ];

        ArticleCorrection::createCorrection(
            $article->slug,
            ArticleCorrection::TYPE_TIRE_PRESSURE_FIX,
            $originalData,
            'Correção inteligente de pressões baseada em validação automática'
        );
    }

    private function createTitleCorrection(TempArticle $article, array $validationDetails): void
    {
        $originalData = [
            'title' => $article->title,
            'domain' => $article->domain,
            'vehicle_data' => $article->vehicle_data ?? [],
            'seo_data' => $article->seo_data ?? [],
            'validation_details' => $validationDetails,
            'priority' => $validationDetails['priority'] ?? 'medium',
            'issues_found' => $validationDetails['issues_found'] ?? []
        ];

        ArticleCorrection::createCorrection(
            $article->slug,
            ArticleCorrection::TYPE_TITLE_YEAR_FIX,
            $originalData,
            'Correção inteligente de SEO baseada em validação automática'
        );
    }

    private function processPressureCorrection(ArticleCorrection $correction): bool
    {
        try {
            $tempArticle = TempArticle::where('slug', $correction->article_slug)
                ->where('domain', 'when_to_change_tires')
                ->first();

            if (!$tempArticle) {
                $correction->markAsFailed("Artigo temporário não encontrado");
                return false;
            }

            $correctedData = $this->claudeApiService->processTirePressureCorrection(
                $tempArticle->vehicle_data ?? [],
                $tempArticle->content ?? []
            );

            if ($correctedData && ($correctedData['needs_update'] ?? false)) {
                if ($this->applyPressureCorrections($tempArticle, $correctedData)) {
                    $correction->markAsCompleted($correctedData);
                    return true;
                }
            } else {
                $correction->markAsNoChanges("Claude determinou que não precisa de atualização");
                return true;
            }

            $correction->markAsFailed("Falha ao aplicar correções");
            return false;

        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("❌ Erro ao processar correção de pressão", [
                'slug' => $correction->article_slug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function processTitleCorrection(ArticleCorrection $correction): bool
    {
        try {
            $tempArticle = TempArticle::where('slug', $correction->article_slug)
                ->where('domain', 'when_to_change_tires')
                ->first();

            if (!$tempArticle) {
                $correction->markAsFailed("Artigo temporário não encontrado");
                return false;
            }

            // ✅ CORRIGIDO: Passar FAQs como array, não como string
            $faqs = $tempArticle->content['perguntas_frequentes'] ?? [];
            
            // ✅ Garantir que FAQs é um array
            if (!is_array($faqs)) {
                Log::warning("⚠️ FAQs não é array para {$correction->article_slug}, convertendo");
                $faqs = [];
            }

            $correctedData = $this->claudeApiService->processTitleSeoCorrection(
                $tempArticle->vehicle_data ?? [],
                $tempArticle->seo_data ?? [],
                $faqs // ✅ CORRIGIDO: Passar como array
            );

            if ($correctedData && ($correctedData['needs_update'] ?? false)) {
                if ($this->applyTitleCorrections($tempArticle, $correctedData)) {
                    $correction->markAsCompleted($correctedData);
                    return true;
                }
            } else {
                $correction->markAsNoChanges("Claude determinou que não precisa de atualização");
                return true;
            }

            $correction->markAsFailed("Falha ao aplicar correções");
            return false;

        } catch (\Exception $e) {
            $correction->markAsFailed($e->getMessage());
            Log::error("❌ Erro ao processar correção de título", [
                'slug' => $correction->article_slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // ✅ Adicionar trace para debug
            ]);
            return false;
        }
    }

    private function applyPressureCorrections(TempArticle $tempArticle, array $correctedData): bool
    {
        try {
            $updated = false;
            $content = $tempArticle->content ?? [];
            $vehicleData = $tempArticle->vehicle_data ?? [];

            if (isset($correctedData['corrected_content'])) {
                if (!empty($correctedData['corrected_content']['introducao'])) {
                    $content['introducao'] = $correctedData['corrected_content']['introducao'];
                    $updated = true;
                }

                if (!empty($correctedData['corrected_content']['consideracoes_finais'])) {
                    $content['consideracoes_finais'] = $correctedData['corrected_content']['consideracoes_finais'];
                    $updated = true;
                }
            }

            if (isset($correctedData['corrected_pressures'])) {
                if (!isset($vehicleData['pressures'])) {
                    $vehicleData['pressures'] = [];
                }

                foreach ($correctedData['corrected_pressures'] as $key => $value) {
                    if (in_array($key, ['pressure_display', 'pressure_loaded_display'])) {
                        $vehicleData[$key] = $value;
                        $updated = true;
                    } else {
                        $vehicleData['pressures'][$key] = $value;
                        $updated = true;
                    }
                }
            }

            if ($updated) {
                $tempArticle->update([
                    'content' => $content,
                    'vehicle_data' => $vehicleData,
                    'updated_at' => now()
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao aplicar correções de pressão", [
                'slug' => $tempArticle->slug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function applyTitleCorrections(TempArticle $tempArticle, array $correctedData): bool
    {
        try {
            $updated = false;
            $content = $tempArticle->content ?? [];
            $seoData = $tempArticle->seo_data ?? [];

            if (isset($correctedData['corrected_seo'])) {
                if (!empty($correctedData['corrected_seo']['page_title'])) {
                    $seoData['page_title'] = $correctedData['corrected_seo']['page_title'];
                    $updated = true;
                }

                if (!empty($correctedData['corrected_seo']['meta_description'])) {
                    $seoData['meta_description'] = $correctedData['corrected_seo']['meta_description'];
                    $updated = true;
                }
            }

            if (isset($correctedData['corrected_content']['perguntas_frequentes'])) {
                $content['perguntas_frequentes'] = $correctedData['corrected_content']['perguntas_frequentes'];
                $updated = true;
            }

            if ($updated) {
                $tempArticle->update([
                    'content' => $content,
                    'seo_data' => $seoData,
                    'updated_at' => now()
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao aplicar correções de título", [
                'slug' => $tempArticle->slug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}