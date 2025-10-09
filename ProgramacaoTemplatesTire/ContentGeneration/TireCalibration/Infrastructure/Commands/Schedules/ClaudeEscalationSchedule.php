<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

/**
 * ClaudeEscalationSchedule - Registry para Escalação Automática
 * 
 * ESTRATÉGIA DE ESCALAÇÃO AUTOMÁTICA:
 * 1. PADRÃO: claude-3-5-sonnet-20240620 (executa primeiro - mais econômico)
 * 2. INTERMEDIÁRIO: claude-3-7-sonnet-20250219 (escalação para falhas)  
 * 3. PREMIUM: claude-3-opus-20240229 (último recurso - casos críticos)
 * 
 * ECONOMIA INTELIGENTE:
 * - 70% resolvidos com modelo padrão
 * - 25% escalados para intermediário
 * - 5% casos críticos para premium
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Registry Pattern para Escalação Inteligente
 */
class ClaudeEscalationSchedule
{
    public static function register($schedule): void
    {
        // ========================================
        // FASE 1: MODELO PADRÃO (ECONÔMICO)
        // ========================================
        $schedule->command('temp-article:correct-standard --limit=1 --delay=3')
            ->cron('*/3 * * * *')  // Cada 5 minutos
            ->appendOutputTo(storage_path('logs/claude-escalation-standard.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Modelo PADRÃO automático concluído');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha no modelo PADRÃO automático');
            });

        // ========================================  
        // FASE 2: MODELO INTERMEDIÁRIO (ESCALAÇÃO)
        // ========================================
        $schedule->command('temp-article:correct-intermediate --only-failed-standard --limit=1 --delay=5')
            ->cron('*/5 * * * *') // Cada 8 minutos (mais devagar, mais caro)
            ->appendOutputTo(storage_path('logs/claude-escalation-intermediate.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Modelo INTERMEDIÁRIO automático concluído');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha no modelo INTERMEDIÁRIO automático');
            });

        // ========================================
        // FASE 3: MODELO PREMIUM (CRÍTICO)
        // ========================================
        // $schedule->command('temp-article:correct-premium --only-critical --limit=1 --delay=10')
        //     ->cron('*/15 * * * *')  // Cada 15 minutos (muito devagar, muito caro)
        //     ->appendOutputTo(storage_path('logs/claude-escalation-premium.log'))
        //     ->onSuccess(function () {
        //         Log::info('Claude Escalation: Modelo PREMIUM automático concluído');
        //     })
        //     ->onFailure(function () {
        //         Log::error('Claude Escalation: Falha no modelo PREMIUM automático');
        //     });

        // ========================================
        // INVESTIGAÇÃO E FLAGGING AUTOMÁTICO
        // ========================================
        $schedule->command('temp-article:investigate-generic-versions --flag-for-correction --limit=20')
            ->cron('*/10 * * * *')  // Cada 10 minutos
            ->withoutOverlapping(5)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-escalation-investigation.log'))
            ->onSuccess(function () {
                Log::info('Claude Escalation: Investigação automática concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Escalation: Falha na investigação automática');
            });

        // ========================================
        // UTILITÁRIOS E MONITORAMENTO
        // ========================================

        // Health check da escalação - a cada 30 minutos
        $schedule->call(function () {
            self::performEscalationHealthCheck();
        })
            ->everyThirtyMinutes()
            ->name('claude-escalation-health-check');

        // Estatísticas de escalação - diário
        $schedule->call(function () {
            self::generateDailyEscalationReport();
        })
            ->dailyAt('07:00')
            ->name('claude-escalation-daily-report');

        // Cleanup de flags antigos - semanal
        $schedule->call(function () {
            self::cleanupOldFlags();
        })
            ->weekly()
            ->name('claude-escalation-cleanup');

        // Análise de custos - diário
        $schedule->call(function () {
            self::analyzeDailyCosts();
        })
            ->dailyAt('23:30')
            ->name('claude-escalation-cost-analysis');
    }

    /**
     * Health check da escalação inteligente
     */
    private static function performEscalationHealthCheck(): void
    {
        try {
            // Estatísticas básicas
            $totalPending = TempArticle::where('has_generic_versions', true)
                ->where(function ($q) {
                    $q->where('has_specific_versions', '!=', true)
                        ->orWhereNull('has_specific_versions');
                })
                ->count();

            $totalCorrected = TempArticle::where('has_specific_versions', true)->count();

            // Estatísticas por modelo
            $correctedByStandard = TempArticle::where('corrected_by', 'claude_standard_v1')->count();
            $correctedByIntermediate = TempArticle::where('corrected_by', 'claude_intermediate_v1')->count();
            $correctedByPremium = TempArticle::where('corrected_by', 'claude_premium_v1')->count();

            // Detectar problemas
            $alerts = [];

            if ($totalPending > 100) {
                $alerts[] = 'Alto volume pendente: ' . $totalPending . ' artigos';
            }

            // Verificar distribuição de escalação
            $totalCorrectedByModels = $correctedByStandard + $correctedByIntermediate + $correctedByPremium;
            if ($totalCorrectedByModels > 0) {
                $standardPercentage = round(($correctedByStandard / $totalCorrectedByModels) * 100, 1);
                $premiumPercentage = round(($correctedByPremium / $totalCorrectedByModels) * 100, 1);

                if ($standardPercentage < 60) {
                    $alerts[] = "Baixo uso do modelo padrão: {$standardPercentage}% (ideal: 70%+)";
                }

                if ($premiumPercentage > 15) {
                    $alerts[] = "Alto uso do modelo premium: {$premiumPercentage}% (ideal: <10%) - CUSTOS ALTOS!";
                }
            }

            // Verificar artigos travados em processamento
            $stuckProcessing = TempArticle::where('has_generic_versions', true)
                ->whereNotNull('corrected_by')
                ->where('has_specific_versions', '!=', true)
                ->where('version_corrected_at', '<', now()->subHours(2))
                ->count();

            if ($stuckProcessing > 10) {
                $alerts[] = "Artigos travados em processamento: {$stuckProcessing}";
            }

            // Log baseado em alertas
            if (!empty($alerts)) {
                Log::warning('Claude Escalation: Alertas detectados', [
                    'pending' => $totalPending,
                    'corrected_total' => $totalCorrected,
                    'distribution' => [
                        'standard' => $correctedByStandard,
                        'intermediate' => $correctedByIntermediate,
                        'premium' => $correctedByPremium
                    ],
                    'alerts' => $alerts,
                    'recommendations' => self::getHealthCheckRecommendations($alerts)
                ]);
            } else {
                Log::info('Claude Escalation: Sistema funcionando normalmente', [
                    'pending' => $totalPending,
                    'corrected_total' => $totalCorrected,
                    'distribution_ok' => true
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro no health check', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Gerar relatório diário de escalação
     */
    private static function generateDailyEscalationReport(): void
    {
        try {
            $yesterday = now()->subDay();

            // Correções do dia anterior
            $dailyCorrections = TempArticle::where('version_corrected_at', '>=', $yesterday->startOfDay())
                ->where('version_corrected_at', '<=', $yesterday->endOfDay())
                ->get()
                ->groupBy('corrected_by');

            $report = [
                'date' => $yesterday->format('Y-m-d'),
                'total_corrections' => 0,
                'model_breakdown' => [],
                'cost_analysis' => [],
                'efficiency_metrics' => []
            ];

            foreach ($dailyCorrections as $model => $corrections) {
                $count = $corrections->count();
                $report['total_corrections'] += $count;

                // Calcular custo baseado no modelo
                $costMultiplier = self::getCostMultiplierByModel($model);
                $dailyCost = $count * $costMultiplier;

                $report['model_breakdown'][$model] = [
                    'corrections' => $count,
                    'cost_multiplier' => $costMultiplier,
                    'daily_cost' => $dailyCost
                ];
            }

            // Calcular métricas de eficiência
            $totalCost = array_sum(array_column($report['model_breakdown'], 'daily_cost'));
            $report['cost_analysis'] = [
                'total_daily_cost' => round($totalCost, 2),
                'cost_per_correction' => $report['total_corrections'] > 0 ?
                    round($totalCost / $report['total_corrections'], 2) : 0,
                'efficiency_score' => self::calculateEfficiencyScore($report['model_breakdown'])
            ];

            Log::info('Claude Escalation: Relatório diário gerado', $report);
        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro no relatório diário', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cleanup de flags antigos
     */
    private static function cleanupOldFlags(): void
    {
        try {
            // Limpar flags de artigos muito antigos que não foram processados
            $oldFlagged = TempArticle::where('flagged_at', '<', now()->subDays(7))
                ->where('has_generic_versions', true)
                ->whereNull('version_corrected_at')
                ->get();

            foreach ($oldFlagged as $article) {
                // Re-investigar ou limpar
                $article->update([
                    'has_generic_versions' => null,
                    'flagged_at' => null,
                    'version_correction_priority' => null
                ]);
            }

            if ($oldFlagged->count() > 0) {
                Log::info('Claude Escalation: Cleanup de flags antigos', [
                    'cleaned_count' => $oldFlagged->count(),
                    'reason' => 'Flags com mais de 7 dias sem processamento'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro no cleanup', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Análise de custos diários
     */
    private static function analyzeDailyCosts(): void
    {
        try {
            $today = now();

            // Custos do dia
            $todayCorrections = TempArticle::where('version_corrected_at', '>=', $today->startOfDay())
                ->where('version_corrected_at', '<=', $today->endOfDay())
                ->get();

            $dailyCosts = [
                'date' => $today->format('Y-m-d'),
                'total_articles' => $todayCorrections->count(),
                'cost_breakdown' => [],
                'total_cost' => 0,
                'efficiency_alerts' => []
            ];

            foreach ($todayCorrections->groupBy('corrected_by') as $model => $corrections) {
                $count = $corrections->count();
                $costMultiplier = self::getCostMultiplierByModel($model);
                $cost = $count * $costMultiplier;

                $dailyCosts['cost_breakdown'][$model] = [
                    'count' => $count,
                    'cost' => $cost,
                    'percentage' => round(($count / $todayCorrections->count()) * 100, 1)
                ];

                $dailyCosts['total_cost'] += $cost;
            }

            // Alertas de eficiência
            if ($dailyCosts['total_cost'] > 50) {
                $dailyCosts['efficiency_alerts'][] = 'Custo diário alto: ' . $dailyCosts['total_cost'] . ' unidades';
            }

            $premiumUsage = $dailyCosts['cost_breakdown']['claude_premium_v1']['percentage'] ?? 0;
            if ($premiumUsage > 20) {
                $dailyCosts['efficiency_alerts'][] = "Uso excessivo do modelo premium: {$premiumUsage}%";
            }

            Log::info('Claude Escalation: Análise de custos diários', $dailyCosts);
        } catch (\Exception $e) {
            Log::error('Claude Escalation: Erro na análise de custos', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obter multiplicador de custo por modelo
     */
    private static function getCostMultiplierByModel(string $model): float
    {
        $costMap = [
            'claude_standard_v1' => 1.0,
            'claude_intermediate_v1' => 2.3,
            'claude_premium_v1' => 4.8
        ];

        return $costMap[$model] ?? 1.0;
    }

    /**
     * Calcular score de eficiência
     */
    private static function calculateEfficiencyScore(array $modelBreakdown): float
    {
        $totalCorrections = array_sum(array_column($modelBreakdown, 'corrections'));

        if ($totalCorrections === 0) return 100.0;

        $standardCount = $modelBreakdown['claude_standard_v1']['corrections'] ?? 0;
        $intermediateCount = $modelBreakdown['claude_intermediate_v1']['corrections'] ?? 0;
        $premiumCount = $modelBreakdown['claude_premium_v1']['corrections'] ?? 0;

        // Score baseado na distribuição ideal (70% standard, 25% intermediate, 5% premium)
        $standardPercentage = ($standardCount / $totalCorrections) * 100;
        $intermediatePercentage = ($intermediateCount / $totalCorrections) * 100;
        $premiumPercentage = ($premiumCount / $totalCorrections) * 100;

        $score = 100;

        // Penalizar desvios da distribuição ideal
        $score -= abs($standardPercentage - 70) * 0.5;
        $score -= abs($intermediatePercentage - 25) * 0.3;
        $score -= abs($premiumPercentage - 5) * 2.0; // Premium tem penalidade maior

        return max(0, round($score, 1));
    }

    /**
     * Obter recomendações baseadas em alertas
     */
    private static function getHealthCheckRecommendations(array $alerts): array
    {
        $recommendations = [];

        foreach ($alerts as $alert) {
            if (strpos($alert, 'Alto volume pendente') !== false) {
                $recommendations[] = 'Aumentar frequência dos comandos ou limites de processamento';
            }

            if (strpos($alert, 'Baixo uso do modelo padrão') !== false) {
                $recommendations[] = 'Investigar qualidade dos prompts do modelo padrão';
            }

            if (strpos($alert, 'Alto uso do modelo premium') !== false) {
                $recommendations[] = 'URGENTE: Revisar estratégia de escalação para reduzir custos';
            }

            if (strpos($alert, 'travados em processamento') !== false) {
                $recommendations[] = 'Executar cleanup: investigar artigos travados';
            }
        }

        return array_unique($recommendations);
    }
}
