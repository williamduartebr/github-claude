<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Carbon\Carbon;

/**
 * ClaudeEscalationSchedule - Schedule para Correção de Versões Genéricas
 * 
 * Schedule otimizado para execução automática da correção de versões genéricas
 * com escalação inteligente de modelos Claude, maximizando eficiência e 
 * minimizando custos operacionais
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Schedule inteligente com escalação automática
 */
class ClaudeEscalationSchedule
{
    public static function register($schedule): void
    {
        // ========================================
        // CORREÇÃO PRINCIPAL - ALTA PRIORIDADE
        // ========================================
        $schedule->command('temp-article:correct-generic-versions --priority=high --limit=3 --delay=2')
            ->cron('*/2 * * * *')  // Cada 2 minutos - prioridade alta
            ->withoutOverlapping(4)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-correction-high.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Correção alta prioridade concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha na correção alta prioridade');
            });

        // ========================================
        // CORREÇÃO MÉDIA PRIORIDADE
        // ========================================
        $schedule->command('temp-article:correct-generic-versions --priority=medium --limit=2 --delay=3')
            ->cron('*/5 * * * *')  // Cada 5 minutos - prioridade média
            ->withoutOverlapping(8)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-correction-medium.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Correção média prioridade concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha na correção média prioridade');
            });

        // ========================================
        // CORREÇÃO BAIXA PRIORIDADE
        // ========================================
        $schedule->command('temp-article:correct-generic-versions --priority=low --limit=1 --delay=5')
            ->cron('*/10 * * * *')  // Cada 10 minutos - prioridade baixa
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-correction-low.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Correção baixa prioridade concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha na correção baixa prioridade');
            });

        // ========================================
        // REPROCESSAMENTO DE FALHAS PERSISTENTES
        // ========================================
        $schedule->command('temp-article:correct-generic-versions --force-reprocess --priority=all --limit=2 --force-model=intermediate')
            ->cron('*/15 * * * *')  // Cada 15 minutos - reprocessar com modelo intermediário
            ->withoutOverlapping(20)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-reprocess-failures.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Reprocessamento de falhas concluído');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha no reprocessamento');
            });

        // ========================================
        // LIMPEZA NOTURNA COM MODELO PREMIUM
        // ========================================
        $schedule->command('temp-article:correct-generic-versions --force-reprocess --priority=all --limit=5 --force-model=premium --delay=10')
            ->dailyAt('02:00')  // 02:00 da madrugada - horário de baixo tráfego
            ->withoutOverlapping(120)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-premium-cleanup.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Limpeza noturna com modelo premium concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha na limpeza noturna premium');
            });

        // ========================================
        // MIGRAÇÃO AUTOMÁTICA (EXECUÇÃO ÚNICA DIÁRIA)
        // ========================================
        $schedule->command('claude:escalation-management migrate --force')
            ->dailyAt('01:30')
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-migration.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Migração automática concluída');
            });

        // ========================================
        // ESTATÍSTICAS E ANÁLISES
        // ========================================

        // Relatório de escalação a cada hora
        $schedule->command('claude:escalation-management stats --period=1 --export=' . storage_path('reports/escalation-hourly.json'))
            ->hourly()
            ->withoutOverlapping(30)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-stats-hourly.log'));

        // Relatório diário detalhado
        $schedule->command('claude:escalation-management stats --period=7 --export=' . storage_path('reports/escalation-weekly.json'))
            ->dailyAt('06:00')
            ->withoutOverlapping(45)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-stats-daily.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Relatório diário gerado');
            });

        // Otimização semanal automática
        $schedule->command('claude:escalation-management optimize --dry-run')
            ->weeklyOn(1, '03:00')  // Segunda-feira às 03:00
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-optimization.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Análise de otimização semanal concluída');
            });

        // ========================================
        // HEALTH CHECKS E MONITORAMENTO
        // ========================================

        // Health check básico a cada 10 minutos
        $schedule->call(function () {
            self::performBasicHealthCheck();
        })
            ->everyTenMinutes()
            ->name('claude-escalation-health-check');

        // Health check avançado a cada hora
        $schedule->call(function () {
            self::performAdvancedHealthCheck();
        })
            ->hourly()
            ->name('claude-escalation-advanced-health-check');

        // Teste de conectividade Claude API a cada 30 minutos
        $schedule->command('claude:escalation-management test')
            ->everyThirtyMinutes()
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-connectivity-test.log'))
            ->onFailure(function () {
                Log::critical('Claude Escalation: Falha crítica na conectividade da API');
            });

        // ========================================
        // LIMPEZA E MANUTENÇÃO
        // ========================================

        // Limpeza de logs antigos - semanal
        $schedule->call(function () {
            self::cleanupOldLogs();
        })
            ->weeklyOn(0, '04:00')  // Domingo às 04:00
            ->name('claude-escalation-log-cleanup');

        // Limpeza de histórico de escalação muito antigo - mensal
        $schedule->call(function () {
            self::cleanupOldEscalationHistory();
        })
            ->monthlyOn(1, '05:00')  // Primeiro dia do mês às 05:00
            ->name('claude-escalation-history-cleanup');
    }

    /**
     * Health check básico - verifica filas e estado geral
     */
    private static function performBasicHealthCheck(): void
    {
        try {
            $stats = self::getEscalationQueueStats();

            // Verificar se há muitos registros travados
            if ($stats['high_priority_pending'] > 50) {
                Log::warning('Claude Escalation: Fila alta prioridade congestionada', [
                    'high_priority_pending' => $stats['high_priority_pending'],
                    'recommendation' => 'Considere aumentar frequência ou limits dos schedules'
                ]);
            }

            // Verificar se sistema está funcionando
            if ($stats['total_pending'] === 0) {
                Log::info('Claude Escalation: Todas as filas processadas', $stats);
            } elseif ($stats['total_pending'] < 100) {
                Log::info('Claude Escalation: Sistema funcionando normalmente', $stats);
            }

            // Alertas por nível de prioridade
            if ($stats['high_priority_pending'] > 20) {
                Log::warning('Claude Escalation: Alta prioridade acumulando', [
                    'count' => $stats['high_priority_pending'],
                    'suggestion' => 'Revisar schedule de alta prioridade'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro no health check básico', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Health check avançado - análise de performance e custos
     */
    private static function performAdvancedHealthCheck(): void
    {
        try {
            $hourlyStats = self::getHourlyPerformanceStats();
            $costAnalysis = self::getCostAnalysis();

            // Análise de performance
            if ($hourlyStats['success_rate'] < 80) {
                Log::warning('Claude Escalation: Taxa de sucesso baixa na última hora', [
                    'success_rate' => $hourlyStats['success_rate'],
                    'total_attempts' => $hourlyStats['total_attempts'],
                    'recommendation' => 'Verificar qualidade dos prompts ou conectividade API'
                ]);
            }

            // Análise de custos
            if ($costAnalysis['premium_usage_percentage'] > 25) {
                Log::warning('Claude Escalation: Alto uso do modelo premium', [
                    'premium_percentage' => $costAnalysis['premium_usage_percentage'],
                    'cost_efficiency' => $costAnalysis['cost_efficiency'],
                    'recommendation' => 'Revisar estratégia de escalação'
                ]);
            }

            // Alertas de tendência
            if ($hourlyStats['escalation_trend'] === 'increasing') {
                Log::info('Claude Escalation: Tendência crescente de escalações', [
                    'trend' => $hourlyStats['escalation_trend'],
                    'note' => 'Monitorar para otimizações'
                ]);
            }

            // Log de status saudável
            if ($hourlyStats['success_rate'] >= 90 && $costAnalysis['cost_efficiency'] >= 80) {
                Log::info('Claude Escalation: Sistema operando com excelente performance', [
                    'success_rate' => $hourlyStats['success_rate'],
                    'cost_efficiency' => $costAnalysis['cost_efficiency']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro no health check avançado', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtém estatísticas da fila de escalação
     */
    private static function getEscalationQueueStats(): array
    {
        $highPriority = TempArticle::where('needs_version_correction', true)
            ->where('version_correction_priority', 'high')
            ->whereNull('version_corrected_at')
            ->count();

        $mediumPriority = TempArticle::where('needs_version_correction', true)
            ->where('version_correction_priority', 'medium')
            ->whereNull('version_corrected_at')
            ->count();

        $lowPriority = TempArticle::where('needs_version_correction', true)
            ->where('version_correction_priority', 'low')
            ->whereNull('version_corrected_at')
            ->count();

        $totalPending = $highPriority + $mediumPriority + $lowPriority;

        $recentlyProcessed = TempArticle::where('version_corrected_at', '>=', now()->subHour())
            ->count();

        return [
            'high_priority_pending' => $highPriority,
            'medium_priority_pending' => $mediumPriority,
            'low_priority_pending' => $lowPriority,
            'total_pending' => $totalPending,
            'recently_processed' => $recentlyProcessed,
            'queue_health' => $totalPending < 200 ? 'healthy' : ($totalPending < 500 ? 'moderate' : 'congested')
        ];
    }

    /**
     * Obtém estatísticas de performance da última hora
     */
    private static function getHourlyPerformanceStats(): array
    {
        $oneHourAgo = now()->subHour();

        $recentArticles = TempArticle::where('last_escalation_at', '>=', $oneHourAgo)
            ->whereNotNull('escalation_history')
            ->get();

        $totalAttempts = 0;
        $successfulAttempts = 0;
        $escalations = 0;

        foreach ($recentArticles as $article) {
            foreach ($article->escalation_history ?? [] as $escalation) {
                $escalationTime = Carbon::parse($escalation['timestamp']);
                
                if ($escalationTime->gte($oneHourAgo)) {
                    $totalAttempts++;
                    
                    if (($escalation['result'] ?? '') === 'success') {
                        $successfulAttempts++;
                    }
                    
                    if ($escalation['escalated'] ?? false) {
                        $escalations++;
                    }
                }
            }
        }

        $successRate = $totalAttempts > 0 ? ($successfulAttempts / $totalAttempts) * 100 : 0;
        $escalationRate = $totalAttempts > 0 ? ($escalations / $totalAttempts) * 100 : 0;

        // Simples análise de tendência baseada na taxa de escalação
        $escalationTrend = $escalationRate > 30 ? 'increasing' : ($escalationRate < 10 ? 'decreasing' : 'stable');

        return [
            'total_attempts' => $totalAttempts,
            'successful_attempts' => $successfulAttempts,
            'success_rate' => round($successRate, 1),
            'escalation_rate' => round($escalationRate, 1),
            'escalation_trend' => $escalationTrend
        ];
    }

    /**
     * Análise de custos da última hora
     */
    private static function getCostAnalysis(): array
    {
        $oneHourAgo = now()->subHour();
        $costMultipliers = ['standard' => 1, 'intermediate' => 3, 'premium' => 10];
        
        $recentArticles = TempArticle::where('last_escalation_at', '>=', $oneHourAgo)
            ->whereNotNull('escalation_history')
            ->get();

        $modelUsage = ['standard' => 0, 'intermediate' => 0, 'premium' => 0];
        $totalCostUnits = 0;
        $totalSuccesses = 0;

        foreach ($recentArticles as $article) {
            foreach ($article->escalation_history ?? [] as $escalation) {
                $escalationTime = Carbon::parse($escalation['timestamp']);
                
                if ($escalationTime->gte($oneHourAgo) && ($escalation['result'] ?? '') === 'success') {
                    $model = $escalation['model_used'] ?? 'standard';
                    if (isset($modelUsage[$model])) {
                        $modelUsage[$model]++;
                        $totalCostUnits += $costMultipliers[$model];
                        $totalSuccesses++;
                    }
                }
            }
        }

        $premiumUsagePercentage = $totalSuccesses > 0 ? ($modelUsage['premium'] / $totalSuccesses) * 100 : 0;
        $maxPossibleCost = $totalSuccesses * 10; // Se tudo fosse premium
        $costEfficiency = $maxPossibleCost > 0 ? (1 - ($totalCostUnits / $maxPossibleCost)) * 100 : 100;

        return [
            'model_usage' => $modelUsage,
            'total_cost_units' => $totalCostUnits,
            'premium_usage_percentage' => round($premiumUsagePercentage, 1),
            'cost_efficiency' => round($costEfficiency, 1),
            'average_cost_per_success' => $totalSuccesses > 0 ? round($totalCostUnits / $totalSuccesses, 2) : 0
        ];
    }

    /**
     * Limpeza de logs antigos (mais de 30 dias)
     */
    private static function cleanupOldLogs(): void
    {
        try {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/claude-*.log');
            $cleanedCount = 0;

            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                    $cleanedCount++;
                }
            }

            Log::info('Claude Escalation: Limpeza de logs concluída', [
                'files_cleaned' => $cleanedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro na limpeza de logs', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Limpeza de histórico de escalação muito antigo (mais de 90 dias)
     */
    private static function cleanupOldEscalationHistory(): void
    {
        try {
            $ninetyDaysAgo = now()->subDays(90);
            $cleanedCount = 0;

            TempArticle::whereNotNull('escalation_history')
                ->chunk(100, function ($articles) use ($ninetyDaysAgo, &$cleanedCount) {
                    foreach ($articles as $article) {
                        $history = $article->escalation_history ?? [];
                        $filteredHistory = [];

                        foreach ($history as $escalation) {
                            $escalationDate = Carbon::parse($escalation['timestamp']);
                            if ($escalationDate->gte($ninetyDaysAgo)) {
                                $filteredHistory[] = $escalation;
                            }
                        }

                        if (count($filteredHistory) !== count($history)) {
                            $article->update(['escalation_history' => $filteredHistory]);
                            $cleanedCount++;
                        }
                    }
                });

            Log::info('Claude Escalation: Limpeza de histórico concluída', [
                'articles_cleaned' => $cleanedCount,
                'cutoff_date' => $ninetyDaysAgo->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro na limpeza de histórico', [
                'error' => $e->getMessage()
            ]);
        }
    }
}