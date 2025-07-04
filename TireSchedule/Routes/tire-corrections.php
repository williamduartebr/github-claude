<?php

use Illuminate\Support\Facades\Route;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

/**
 * 🚗 Rotas para correções de artigos sobre pneus
 * Arquivo: src/ContentGeneration/TireSchedule/Routes/tire-corrections.php
 * Prefixo: /api/tire-corrections
 */
Route::prefix('api/tire-corrections')->group(function () {

    /**
     * 📊 Estatísticas das correções de pneus
     */
    Route::get('/stats', function () {
        try {
            $service = app(TireCorrectionService::class);
            $stats = $service->getStats();
            
            // Estatísticas adicionais
            $totalTireArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            $correctionRate = $totalTireArticles > 0 ? 
                round(($stats['total'] / $totalTireArticles) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => array_merge($stats, [
                    'total_tire_articles' => $totalTireArticles,
                    'correction_rate' => $correctionRate . '%',
                    'domain' => 'when_to_change_tires'
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 📋 Listar correções pendentes
     */
    Route::get('/pending', function () {
        try {
            $limit = request('limit', 20);
            
            $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->orderBy('created_at', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $corrections->map(function($correction) {
                    return [
                        'id' => $correction->_id,
                        'article_slug' => $correction->article_slug,
                        'status' => $correction->status,
                        'created_at' => $correction->created_at,
                        'vehicle_name' => $correction->original_data['vehicle_data']['vehicle_name'] ?? 'N/A'
                    ];
                }),
                'total' => $corrections->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar correções pendentes: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🔍 Buscar correção por slug
     */
    Route::get('/by-slug/{slug}', function ($slug) {
        try {
            $correction = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma correção encontrada para este slug'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $correction->_id,
                    'article_slug' => $correction->article_slug,
                    'status' => $correction->status,
                    'correction_type' => $correction->correction_type,
                    'created_at' => $correction->created_at,
                    'processed_at' => $correction->processed_at,
                    'original_data' => $correction->original_data,
                    'correction_data' => $correction->correction_data,
                    'summary' => $correction->getTireCorrectionsSummary()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar correção: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🆕 Criar correção para um slug específico
     */
    Route::post('/create/{slug}', function ($slug) {
        try {
            $service = app(TireCorrectionService::class);
            
            // Verificar se já existe correção
            $existingCorrection = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->exists();

            if ($existingCorrection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma correção para este artigo'
                ], 409);
            }

            $result = $service->createCorrectionsForSlugs([$slug]);

            if ($result['created'] > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Correção criada com sucesso',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível criar a correção',
                    'data' => $result
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar correção: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * ⚙️ Processar correção específica
     */
    Route::post('/process/{correctionId}', function ($correctionId) {
        try {
            $correction = ArticleCorrection::find($correctionId);

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correção não encontrada'
                ], 404);
            }

            if ($correction->correction_type !== ArticleCorrection::TYPE_TIRE_PRESSURE_FIX) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta não é uma correção de pneus'
                ], 400);
            }

            if ($correction->status !== ArticleCorrection::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correção não está pendente'
                ], 400);
            }

            $service = app(TireCorrectionService::class);
            $success = $service->processTireCorrection($correction);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Correção processada com sucesso' : 'Falha ao processar correção',
                'data' => [
                    'correction_id' => $correctionId,
                    'status' => $correction->fresh()->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar correção: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 📈 Relatório detalhado de correções
     */
    Route::get('/report', function () {
        try {
            $service = app(TireCorrectionService::class);
            $stats = $service->getStats();

            // Estatísticas por período
            $today = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->whereDate('created_at', today())->count();

            $thisWeek = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

            $thisMonth = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->whereMonth('created_at', now()->month)->count();

            // Top 10 artigos corrigidos recentemente
            $recentCorrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->orderBy('processed_at', 'desc')
                ->limit(10)
                ->get(['article_slug', 'processed_at', 'correction_data']);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'periods' => [
                        'today' => $today,
                        'this_week' => $thisWeek,
                        'this_month' => $thisMonth
                    ],
                    'recent_corrections' => $recentCorrections->map(function($correction) {
                        return [
                            'slug' => $correction->article_slug,
                            'processed_at' => $correction->processed_at,
                            'needs_update' => $correction->correction_data['needs_update'] ?? false,
                            'reason' => $correction->correction_data['reason'] ?? null
                        ];
                    }),
                    'generated_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🧹 Limpar correções duplicadas
     */
    Route::post('/clean-duplicates', function () {
        try {
            $service = app(TireCorrectionService::class);
            $results = $service->cleanAllDuplicates();

            return response()->json([
                'success' => true,
                'message' => 'Limpeza concluída',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro na limpeza: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🔄 Resetar correções travadas
     */
    Route::post('/reset-stuck', function () {
        try {
            $hours = request('hours', 6);
            $resetCount = ArticleCorrection::resetStuckProcessing($hours);

            return response()->json([
                'success' => true,
                'message' => "Reset concluído: {$resetCount} correções",
                'data' => [
                    'reset_count' => $resetCount,
                    'hours_threshold' => $hours
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no reset: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 📊 Health check do sistema de correções de pneus
     */
    Route::get('/health', function () {
        try {
            $issues = [];
            $status = 'healthy';

            // Verificar correções travadas
            $stuckCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(2))
                ->count();

            if ($stuckCount > 0) {
                $issues[] = "🚫 {$stuckCount} correções travadas em processamento";
                $status = 'warning';
            }

            // Verificar muitas falhas
            $recentFailed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '>', now()->subHours(24))
                ->count();

            if ($recentFailed > 10) {
                $issues[] = "⚠️ Muitas falhas recentes: {$recentFailed}";
                $status = 'warning';
            }

            // Verificar conexão com API Claude
            $apiKey = config('services.claude.api_key');
            if (empty($apiKey)) {
                $issues[] = "🚫 API Claude não configurada";
                $status = 'error';
            }

            // Verificar artigos disponíveis para correção
            $service = app(TireCorrectionService::class);
            $availableArticles = count($service->getAllTireArticleSlugs(10));

            return response()->json([
                'success' => true,
                'status' => $status,
                'data' => [
                    'status' => $status,
                    'issues' => $issues,
                    'metrics' => [
                        'stuck_processing' => $stuckCount,
                        'recent_failed' => $recentFailed,
                        'available_articles' => $availableArticles,
                        'api_configured' => !empty($apiKey)
                    ],
                    'checked_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Erro no health check: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🎯 Processar próximas N correções da fila
     */
    Route::post('/process-queue', function () {
        try {
            $limit = request('limit', 5);
            $service = app(TireCorrectionService::class);
            
            $results = $service->processAllPendingCorrections($limit);

            return response()->json([
                'success' => true,
                'message' => 'Processamento da fila concluído',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no processamento da fila: ' . $e->getMessage()
            ], 500);
        }
    });

    /**
     * 🔍 Buscar artigos que precisam de correção
     */
    Route::get('/articles-needing-correction', function () {
        try {
            $limit = request('limit', 50);
            $service = app(TireCorrectionService::class);
            
            $slugs = $service->getAllTireArticleSlugs($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'slugs' => $slugs,
                    'count' => count($slugs),
                    'limit' => $limit
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar artigos: ' . $e->getMessage()
            ], 500);
        }
    });

});