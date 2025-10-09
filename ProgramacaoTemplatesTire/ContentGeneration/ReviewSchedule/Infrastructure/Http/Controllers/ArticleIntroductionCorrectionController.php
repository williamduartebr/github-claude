<?php

namespace Src\ContentGeneration\ReviewSchedule\Infrastructure\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\ArticleIntroductionCorrectionService;


class ArticleIntroductionCorrectionController extends Controller
{
    protected ArticleIntroductionCorrectionService $correctionService;

    public function __construct(ArticleIntroductionCorrectionService $correctionService)
    {
        $this->correctionService = $correctionService;
    }

    /**
     * 📊 Obter estatísticas gerais
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->correctionService->getStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de correção de conteúdo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => 'Não foi possível obter as estatísticas'
            ], 500);
        }
    }

    /**
     * 📈 Obter estatísticas detalhadas
     */
    public function getDetailedStats(): JsonResponse
    {
        try {
            $stats = $this->correctionService->getStats();
            
            // Estatísticas adicionais
            $today = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->whereDate('created_at', today())
                ->count();
                
            $thisWeek = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $successRate = $stats['total'] > 0 ? 
                round(($stats['completed'] / $stats['total']) * 100, 2) : 0;

            $detailedStats = array_merge($stats, [
                'today' => $today,
                'this_week' => $thisWeek,
                'success_rate' => $successRate,
                'avg_processing_time' => $this->getAverageProcessingTime(),
                'last_processed' => $this->getLastProcessedCorrection()
            ]);

            return response()->json([
                'success' => true,
                'data' => $detailedStats,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas detalhadas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 📋 Listar correções com filtros
     */
    public function listCorrections(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|in:pending,processing,completed,failed',
                'limit' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1',
                'sort' => 'sometimes|in:created_at,updated_at,processed_at',
                'direction' => 'sometimes|in:asc,desc'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT);

            // Filtros
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Ordenação
            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            // Paginação
            $limit = $request->get('limit', 20);
            $corrections = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'data' => $corrections,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar correções: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🔍 Obter correção específica
     */
    public function getCorrection(string $slug): JsonResponse
    {
        try {
            $correction = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->first();

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Correção não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $correction,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar correção para {$slug}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🆕 Criar correção para artigo específico
     */
    public function createCorrection(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'article_slug' => 'required|string|max:255',
                'force' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $slug = $request->article_slug;
            $force = $request->get('force', false);

            // Verificar se já existe (a menos que force seja true)
            if (!$force) {
                $existing = ArticleCorrection::where('article_slug', $slug)
                    ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                    ->exists();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Correção já existe para este artigo'
                    ], 409);
                }
            }

            $results = $this->correctionService->createCorrectionsForSlugs([$slug]);

            if ($results['created'] > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Correção criada com sucesso',
                    'data' => $results,
                    'timestamp' => now()->format('Y-m-d H:i:s.u')
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível criar a correção',
                    'data' => $results
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao criar correção: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * ⚡ Processar correção específica
     */
    public function processCorrection(string $slug): JsonResponse
    {
        try {
            $correction = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->first();

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Correção pendente não encontrada'
                ], 404);
            }

            $success = $this->correctionService->processIntroductionCorrection($correction);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Correção processada com sucesso',
                    'data' => $correction->fresh(),
                    'timestamp' => now()->format('Y-m-d H:i:s.u')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Falha ao processar correção',
                    'data' => $correction->fresh()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao processar correção {$slug}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🔄 Reprocessar correção falhada
     */
    public function retryCorrection(string $slug): JsonResponse
    {
        try {
            $correction = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->first();

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Correção falhada não encontrada'
                ], 404);
            }

            // Reset para pending
            $correction->resetForReprocessing();

            return response()->json([
                'success' => true,
                'message' => 'Correção resetada para reprocessamento',
                'data' => $correction->fresh(),
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao resetar correção {$slug}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🗑️ Deletar correção
     */
    public function deleteCorrection(string $slug): JsonResponse
    {
        try {
            $correction = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->first();

            if (!$correction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Correção não encontrada'
                ], 404);
            }

            $correction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Correção deletada com sucesso',
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao deletar correção {$slug}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 📦 Criar correções em lote
     */
    public function bulkCreateCorrections(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'sometimes|integer|min:1|max:1000',
                'force' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $limit = $request->get('limit', 100);
            $slugs = $this->correctionService->getAllArticleSlugs($limit);

            if (empty($slugs)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Todos os artigos já possuem correções',
                    'data' => ['created' => 0, 'skipped' => 0, 'errors' => 0]
                ]);
            }

            $results = $this->correctionService->createCorrectionsForSlugs($slugs);

            return response()->json([
                'success' => true,
                'message' => "Processamento em lote concluído",
                'data' => $results,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no processamento em lote: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * ⚡ Processar correções em lote
     */
    public function bulkProcessCorrections(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $limit = $request->get('limit', 10);
            $results = $this->correctionService->processAllPendingCorrections($limit);

            return response()->json([
                'success' => true,
                'message' => 'Processamento em lote concluído',
                'data' => $results,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no processamento em lote: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🧹 Limpar duplicatas
     */
    public function cleanDuplicates(): JsonResponse
    {
        try {
            $results = $this->correctionService->cleanAllDuplicates();

            return response()->json([
                'success' => true,
                'message' => 'Limpeza de duplicatas concluída',
                'data' => $results,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na limpeza de duplicatas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🔧 Reset do sistema completo
     */
    public function resetSystem(): JsonResponse
    {
        try {
            // Reset apenas correções falhadas ou com erro
            $deletedCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->whereIn('status', [ArticleCorrection::STATUS_FAILED, ArticleCorrection::STATUS_PROCESSING])
                ->delete();

            Log::info("Sistema de correção de conteúdo resetado: {$deletedCount} registros removidos");

            return response()->json([
                'success' => true,
                'message' => 'Sistema resetado com sucesso',
                'data' => ['deleted_corrections' => $deletedCount],
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao resetar sistema: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 📊 Saúde do sistema
     */
    public function systemHealth(): JsonResponse
    {
        try {
            $stats = $this->correctionService->getStats();
            
            $health = [
                'status' => 'healthy',
                'checks' => [
                    'api_key_configured' => !empty(config('services.claude.api_key')),
                    'pending_reasonable' => $stats['pending'] < 100,
                    'no_stuck_processing' => $stats['processing'] < 10,
                    'success_rate_good' => $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) > 0.8 : true
                ],
                'stats' => $stats,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ];

            // Determinar status geral
            $healthyChecks = array_filter($health['checks']);
            if (count($healthyChecks) < count($health['checks'])) {
                $health['status'] = 'degraded';
            }

            return response()->json([
                'success' => true,
                'data' => $health
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar saúde do sistema: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🧪 Teste de conectividade com Claude API
     */
    public function testClaudeApi(): JsonResponse
    {
        try {
            $testPrompt = "Responda apenas 'OK' se você conseguir me ouvir.";
            
            $response = Http::timeout(30)->withHeaders([
                'x-api-key' => config('services.claude.api_key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 50,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $testPrompt
                    ]
                ]
            ]);

            $successful = $response->successful();
            $responseData = $successful ? $response->json() : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'api_accessible' => $successful,
                    'status_code' => $response->status(),
                    'response_time_ms' => $response->transferStats->getTransferTime() * 1000,
                    'claude_response' => $responseData['content'][0]['text'] ?? null,
                    'test_passed' => $successful && str_contains(strtolower($responseData['content'][0]['text'] ?? ''), 'ok')
                ],
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no teste da API Claude: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [
                    'api_accessible' => false,
                    'error' => $e->getMessage(),
                    'test_passed' => false
                ]
            ]);
        }
    }

    /**
     * 📋 Log de atividades recentes
     */
    public function getActivityLog(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            
            $recentActivity = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->whereNotNull('processed_at')
                ->orderBy('processed_at', 'desc')
                ->limit($limit)
                ->get(['article_slug', 'status', 'processed_at', 'created_at', 'error_message'])
                ->map(function ($correction) {
                    return [
                        'article_slug' => $correction->article_slug,
                        'status' => $correction->status,
                        'processed_at' => $correction->processed_at?->format('Y-m-d H:i:s'),
                        'created_at' => $correction->created_at->format('Y-m-d H:i:s'),
                        'duration_minutes' => $correction->processed_at && $correction->created_at 
                            ? $correction->created_at->diffInMinutes($correction->processed_at)
                            : null,
                        'has_error' => !empty($correction->error_message)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentActivity,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter log de atividades: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 📈 Métricas de performance
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 7);
            $startDate = now()->subDays($days);

            $dailyStats = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date')
                ->map(function ($dayData) {
                    $stats = ['pending' => 0, 'completed' => 0, 'failed' => 0, 'processing' => 0];
                    foreach ($dayData as $item) {
                        $stats[$item->status] = $item->count;
                    }
                    $stats['total'] = array_sum($stats);
                    return $stats;
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'period_days' => $days,
                    'daily_metrics' => $dailyStats,
                    'avg_processing_time' => $this->getAverageProcessingTime(),
                    'peak_day' => $dailyStats->sortByDesc('total')->keys()->first()
                ],
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter métricas de performance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 🎯 Taxa de sucesso
     */
    public function getSuccessRate(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', 'week'); // day, week, month
            
            $startDate = match($period) {
                'day' => now()->subDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                default => now()->subWeek()
            };

            $total = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('created_at', '>=', $startDate)
                ->whereIn('status', [ArticleCorrection::STATUS_COMPLETED, ArticleCorrection::STATUS_FAILED])
                ->count();

            $successful = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('created_at', '>=', $startDate)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->count();

            $successRate = $total > 0 ? round(($successful / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'total_processed' => $total,
                    'successful' => $successful,
                    'failed' => $total - $successful,
                    'success_rate_percent' => $successRate,
                    'benchmark' => 85.0 // Meta de 85%
                ],
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao calcular taxa de sucesso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * 📡 Webhook para correções concluídas
     */
    public function webhookCorrectionCompleted(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'article_slug' => 'required|string',
                'correction_id' => 'required|string',
                'webhook_secret' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid payload'], 422);
            }

            // Verificar webhook secret (se configurado)
            $expectedSecret = config('services.claude.webhook_secret');
            if ($expectedSecret && $request->webhook_secret !== $expectedSecret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Processar webhook
            Log::info('Webhook de correção concluída recebido', [
                'article_slug' => $request->article_slug,
                'correction_id' => $request->correction_id,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no webhook de correção concluída: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * ⚠️ Webhook para correções falhadas
     */
    public function webhookCorrectionFailed(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'article_slug' => 'required|string',
                'correction_id' => 'required|string',
                'error_message' => 'sometimes|string',
                'webhook_secret' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid payload'], 422);
            }

            // Verificar webhook secret
            $expectedSecret = config('services.claude.webhook_secret');
            if ($expectedSecret && $request->webhook_secret !== $expectedSecret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Processar webhook de falha
            Log::warning('Webhook de correção falhada recebido', [
                'article_slug' => $request->article_slug,
                'correction_id' => $request->correction_id,
                'error_message' => $request->error_message,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no webhook de correção falhada: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ========================================

    /**
     * Calcula tempo médio de processamento
     */
    private function getAverageProcessingTime(): ?float
    {
        try {
            $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->whereNotNull('processed_at')
                ->limit(100)
                ->get(['created_at', 'processed_at']);

            if ($corrections->isEmpty()) {
                return null;
            }

            $totalMinutes = $corrections->sum(function ($correction) {
                return $correction->created_at->diffInMinutes($correction->processed_at);
            });

            return round($totalMinutes / $corrections->count(), 2);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obter última correção processada
     */
    private function getLastProcessedCorrection(): ?array
    {
        try {
            $last = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->whereNotNull('processed_at')
                ->orderBy('processed_at', 'desc')
                ->first(['article_slug', 'status', 'processed_at']);

            if (!$last) {
                return null;
            }

            return [
                'article_slug' => $last->article_slug,
                'status' => $last->status,
                'processed_at' => $last->processed_at->format('Y-m-d H:i:s'),
                'minutes_ago' => $last->processed_at->diffInMinutes(now())
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}