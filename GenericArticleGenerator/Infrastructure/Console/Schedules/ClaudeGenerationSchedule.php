<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console\Schedules;


use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

/**
 * ClaudeGenerationSchedule - Automação de Geração de Artigos
 * 
 * ESTRATÉGIA DE ESCALAÇÃO AUTOMÁTICA (Modelos 2025):
 * 1. STANDARD (3.7 Sonnet): Executa primeiro (mais econômico, mais frequente)
 * 2. INTERMEDIATE (Sonnet 4.0): Escalação para falhas (balanceado)
 * 3. PREMIUM (Sonnet 4.5): Desabilitado por padrão (muito caro - apenas manual)
 * 
 * ECONOMIA INTELIGENTE:
 * - Standard roda a cada 10 minutos (1 artigo por vez) - Custo: 2.3x
 * - Intermediate roda a cada 30 minutos (apenas falhas) - Custo: 3.5x
 * - Premium: NUNCA automatizado (só manual) - Custo: 4.0x
 * 
 * CONTROLE DE CUSTOS:
 * - Máximo 144 artigos standard/dia (custo: ~331 unidades)
 * - Máximo 48 artigos intermediate/dia (custo: ~168 unidades)
 * - Total diário controlado: ~500 unidades (ajustável)
 * 
 * SEGURANÇA:
 * - withoutOverlapping: previne execuções simultâneas
 * - onOneServer: garante 1 instância apenas
 * - Health checks a cada hora
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Atualizado para novos modelos 2025
 */
class ClaudeGenerationSchedule
{
    /**
     * Registrar tarefas agendadas
     */
    public static function register(Schedule $schedule): void
    {
        // ========================================
        // FASE 1: GERAÇÃO STANDARD (ECONÔMICA)
        // ========================================

        // Gerar com modelo standard (3.7 Sonnet) - execução frequente
        $schedule->command('temp-article:generate-standard --limit=1 --delay=3')
            // ->everyTenMinutes()
            ->everySixHours(0)
            ->withoutOverlapping(5)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-generation-standard.log'))
            ->before(function () {
                Log::info('Claude Generation: Iniciando geração STANDARD automática (3.7 Sonnet)');
            })
            ->onSuccess(function () {
                Log::info('Claude Generation: Geração STANDARD concluída com sucesso');
            })
            ->onFailure(function () {
                Log::error('Claude Generation: Falha na geração STANDARD automática');
            });

        // ========================================
        // FASE 2: ESCALAÇÃO INTERMEDIATE
        // ========================================

        // Processar falhas do standard com intermediate (Sonnet 4.0) - menos frequente
        $schedule->command('temp-article:generate-intermediate --only-failed-standard --limit=1 --delay=5')
            //->everyThirtyMinutes()
            ->everySixHours(0)
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/claude-generation-intermediate.log'))
            ->before(function () {
                Log::info('Claude Generation: Iniciando escalação INTERMEDIATE automática (Sonnet 4.0)');
            })
            ->onSuccess(function () {
                Log::info('Claude Generation: Escalação INTERMEDIATE concluída');
            })
            ->onFailure(function () {
                Log::error('Claude Generation: Falha na escalação INTERMEDIATE');
            });

        // ========================================
        // FASE 3: PREMIUM - DESABILITADO
        // ========================================

        // ⚠️ PREMIUM (Sonnet 4.5) NUNCA É AUTOMATIZADO (muito caro)
        // Use apenas manualmente: php artisan temp-article:generate-premium

        // ========================================
        // SEED DE TÍTULOS AUTOMÁTICO (OPCIONAL)
        // ========================================

        // Gerar novos títulos automaticamente uma vez por dia
        // DESCOMENTE se quiser seed automático:
        // $schedule->command('temp-article:seed --count=20 --category=all --priority=medium')
        //     ->dailyAt('02:00')
        //     ->withoutOverlapping()
        //     ->onOneServer()
        //     ->appendOutputTo(storage_path('logs/claude-generation-seed.log'))
        //     ->onSuccess(function () {
        //         Log::info('Claude Generation: Seed automático de títulos concluído');
        //     });

        // ========================================
        // VALIDAÇÃO AUTOMÁTICA
        // ========================================

        // Validar JSONs gerados a cada hora
        // $schedule->command('temp-article:validate --limit=10 --auto-fix')
        //     ->hourly()
        //     ->withoutOverlapping(10)
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/claude-generation-validate.log'))
        //     ->onSuccess(function () {
        //         Log::info('Claude Generation: Validação automática concluída');
        //     });

        // ========================================
        // PUBLICAÇÃO AUTOMÁTICA
        // ========================================

        // Publicar artigos validados a cada 2 horas
        // $schedule->command('temp-article:publish --limit=5 --auto-approve')
        //     ->everyTwoHours()
        //     ->withoutOverlapping(15)
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/claude-generation-publish.log'))
        //     ->before(function () {
        //         Log::info('Claude Generation: Iniciando publicação automática');
        //     })
        //     ->onSuccess(function () {
        //         $published = GenerationTempArticle::where('generation_status', 'published')
        //             ->whereDate('published_at', today())
        //             ->count();
        //         Log::info("Claude Generation: Publicação concluída - {$published} artigos hoje");
        //     });

        // ========================================
        // MONITORAMENTO E HEALTH CHECKS
        // ========================================

        // Health check geral do sistema - a cada hora
        $schedule->call(function () {
            self::performHealthCheck();
        })
            ->hourly()
            ->name('claude-generation-health-check');

        // Estatísticas diárias - todo dia às 7h
        $schedule->call(function () {
            self::generateDailyReport();
        })
            ->dailyAt('07:00')
            ->name('claude-generation-daily-report');

        // Análise de custos - todo dia às 23h30
        $schedule->call(function () {
            self::analyzeDailyCosts();
        })
            ->dailyAt('23:30')
            ->name('claude-generation-cost-analysis');

        // Limpeza de artigos antigos falhados - semanal
        $schedule->call(function () {
            self::cleanupOldFailures();
        })
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->name('claude-generation-cleanup');

        // Alerta de excesso de custos - a cada 6 horas
        $schedule->call(function () {
            self::checkCostOverrun();
        })
            ->everySixHours()
            ->name('claude-generation-cost-alert');
    }

    /**
     * Health check do sistema de geração
     */
    private static function performHealthCheck(): void
    {
        try {
            $stats = [
                'timestamp' => now()->toISOString(),
                'pending' => GenerationTempArticle::pending()->count(),
                'generating' => GenerationTempArticle::where('generation_status', 'generating')->count(),
                'generated' => GenerationTempArticle::where('generation_status', 'generated')->count(),
                'validated' => GenerationTempArticle::where('generation_status', 'validated')->count(),
                'failed' => GenerationTempArticle::where('generation_status', 'failed')->count(),
                'published_today' => GenerationTempArticle::where('generation_status', 'published')
                    ->whereDate('published_at', today())
                    ->count(),
            ];

            $totalProcessed = GenerationTempArticle::whereNotNull('generated_at')->count();
            $totalSuccess = GenerationTempArticle::whereIn('generation_status', ['generated', 'validated', 'published'])->count();

            $stats['success_rate'] = $totalProcessed > 0
                ? round(($totalSuccess / $totalProcessed) * 100, 1)
                : 0;

            $alerts = [];

            if ($stats['pending'] > 100) {
                $alerts[] = "Alto volume pendente: {$stats['pending']} artigos";
            }

            if ($stats['generating'] > 5) {
                $alerts[] = "Muitos artigos em geração simultânea: {$stats['generating']}";
            }

            if ($stats['failed'] > 50) {
                $alerts[] = "Alto número de falhas: {$stats['failed']} artigos";
            }

            if ($stats['success_rate'] < 70 && $totalProcessed > 10) {
                $alerts[] = "Taxa de sucesso baixa: {$stats['success_rate']}%";
            }

            // Verificar artigos travados
            $stuck = GenerationTempArticle::where('generation_status', 'generating')
                ->where('generation_started_at', '<', now()->subHours(2))
                ->count();

            if ($stuck > 0) {
                $alerts[] = "{$stuck} artigos travados em 'generating' por mais de 2 horas";

                // Auto-recovery
                GenerationTempArticle::where('generation_status', 'generating')
                    ->where('generation_started_at', '<', now()->subHours(2))
                    ->update([
                        'generation_status' => 'failed',
                        'generation_error' => 'Timeout - travado por mais de 2 horas'
                    ]);
            }

            if (!empty($alerts)) {
                Log::warning('Claude Generation Health Check: Alertas detectados', [
                    'stats' => $stats,
                    'alerts' => $alerts,
                    'recommendations' => self::getHealthRecommendations($alerts)
                ]);
            } else {
                Log::info('Claude Generation Health Check: Sistema saudável', $stats);
            }
        } catch (\Exception $e) {
            Log::error('Claude Generation Health Check: Erro', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Relatório diário
     */
    private static function generateDailyReport(): void
    {
        try {
            $yesterday = now()->subDay();

            $report = [
                'date' => $yesterday->format('Y-m-d'),
                'generated_count' => 0,
                'published_count' => 0,
                'failed_count' => 0,
                'models_used' => [],
                'total_cost' => 0,
                'categories' => [],
            ];

            $generated = GenerationTempArticle::whereBetween('generated_at', [
                $yesterday->startOfDay(),
                $yesterday->endOfDay()
            ])->get();

            $report['generated_count'] = $generated->count();

            foreach ($generated->groupBy('generation_model_used') as $model => $articles) {
                $count = $articles->count();
                $costMultiplier = self::getCostMultiplier($model);
                $cost = $count * $costMultiplier;

                $report['models_used'][$model] = [
                    'count' => $count,
                    'cost' => $cost,
                    'percentage' => round(($count / $report['generated_count']) * 100, 1)
                ];

                $report['total_cost'] += $cost;
            }

            $report['published_count'] = GenerationTempArticle::whereBetween('published_at', [
                $yesterday->startOfDay(),
                $yesterday->endOfDay()
            ])->count();

            $report['failed_count'] = GenerationTempArticle::where('generation_status', 'failed')
                ->whereBetween('generation_last_attempt_at', [
                    $yesterday->startOfDay(),
                    $yesterday->endOfDay()
                ])->count();

            $categories = $generated->groupBy('category_slug');
            foreach ($categories as $slug => $articles) {
                $report['categories'][$slug] = $articles->count();
            }

            $report['metrics'] = [
                'success_rate' => $report['generated_count'] > 0
                    ? round((($report['generated_count'] - $report['failed_count']) / $report['generated_count']) * 100, 1)
                    : 0,
                'cost_per_article' => $report['generated_count'] > 0
                    ? round($report['total_cost'] / $report['generated_count'], 2)
                    : 0,
                'publication_rate' => $report['generated_count'] > 0
                    ? round(($report['published_count'] / $report['generated_count']) * 100, 1)
                    : 0
            ];

            Log::info('Claude Generation: Relatório diário gerado', $report);
        } catch (\Exception $e) {
            Log::error('Claude Generation: Erro no relatório diário', [
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

            $costs = [
                'date' => $today->format('Y-m-d'),
                'standard' => 0,
                'intermediate' => 0,
                'premium' => 0,
                'total' => 0,
                'projected_monthly' => 0,
                'alerts' => []
            ];

            $todayGenerated = GenerationTempArticle::whereDate('generated_at', $today)->get();

            foreach ($todayGenerated->groupBy('generation_model_used') as $model => $articles) {
                $count = $articles->count();
                $costMultiplier = self::getCostMultiplier($model);
                $cost = $count * $costMultiplier;

                if ($model === 'standard') {
                    $costs['standard'] = $cost;
                } elseif ($model === 'intermediate') {
                    $costs['intermediate'] = $cost;
                } elseif ($model === 'premium') {
                    $costs['premium'] = $cost;
                }

                $costs['total'] += $cost;
            }

            $costs['projected_monthly'] = round($costs['total'] * 30, 2);

            if ($costs['total'] > 500) {
                $costs['alerts'][] = "Custo diário muito alto: {$costs['total']} unidades";
            }

            if ($costs['premium'] > 50) {
                $costs['alerts'][] = "Uso excessivo de modelo premium: {$costs['premium']} unidades";
            }

            if ($costs['projected_monthly'] > 10000) {
                $costs['alerts'][] = "Projeção mensal acima do limite: {$costs['projected_monthly']} unidades";
            }

            $standardPercentage = $costs['total'] > 0
                ? round(($costs['standard'] / $costs['total']) * 100, 1)
                : 0;

            $costs['efficiency_score'] = $standardPercentage;

            if ($standardPercentage < 60) {
                $costs['alerts'][] = "Baixo uso de modelo econômico: {$standardPercentage}% standard";
            }

            Log::info('Claude Generation: Análise de custos diários', $costs);

            if (!empty($costs['alerts']) && $costs['total'] > 700) {
                Log::critical('Claude Generation: ALERTA DE CUSTOS ELEVADOS', $costs);
            }
        } catch (\Exception $e) {
            Log::error('Claude Generation: Erro na análise de custos', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Limpeza de falhas antigas
     */
    private static function cleanupOldFailures(): void
    {
        try {
            $oldFailures = GenerationTempArticle::where('generation_status', 'failed')
                ->where('generation_last_attempt_at', '<', now()->subDays(30))
                ->where('generation_retry_count', '>=', 3)
                ->get();

            $count = $oldFailures->count();

            if ($count > 0) {
                foreach ($oldFailures as $article) {
                    $article->update([
                        'generation_status' => 'discarded',
                        'generation_error' => 'Descartado após 30 dias sem solução'
                    ]);
                }

                Log::info("Claude Generation: Cleanup - {$count} artigos antigos descartados");
            }
        } catch (\Exception $e) {
            Log::error('Claude Generation: Erro no cleanup', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar excesso de custos
     */
    private static function checkCostOverrun(): void
    {
        try {
            $last6Hours = now()->subHours(6);

            $recentGenerated = GenerationTempArticle::where('generated_at', '>=', $last6Hours)->get();

            $cost6h = 0;
            foreach ($recentGenerated as $article) {
                $cost6h += self::getCostMultiplier($article->generation_model_used ?? 'standard');
            }

            // Se custo em 6h > 150 unidades, alertar
            if ($cost6h > 150) {
                Log::warning('Claude Generation: Alerta de custos nas últimas 6h', [
                    'cost_6h' => $cost6h,
                    'articles_generated' => $recentGenerated->count(),
                    'projected_daily' => round($cost6h * 4, 2),
                    'recommendation' => 'Considere pausar geração automática temporariamente'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Claude Generation: Erro no check de custos', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obter multiplicador de custo por modelo (ATUALIZADO 2025)
     */
    private static function getCostMultiplier(string $model): float
    {
        $costs = [
            'standard' => 2.3,      // Claude 3.7 Sonnet
            'intermediate' => 3.5,  // Claude Sonnet 4.0
            'premium' => 4.0        // Claude Sonnet 4.5
        ];

        return $costs[$model] ?? 2.3;
    }

    /**
     * Obter recomendações de health check
     */
    private static function getHealthRecommendations(array $alerts): array
    {
        $recommendations = [];

        foreach ($alerts as $alert) {
            if (str_contains($alert, 'Alto volume pendente')) {
                $recommendations[] = 'Aumentar frequência dos comandos standard';
            }
            if (str_contains($alert, 'simultânea')) {
                $recommendations[] = 'Verificar withoutOverlapping nos schedules';
            }
            if (str_contains($alert, 'falhas')) {
                $recommendations[] = 'Investigar erros recorrentes e ajustar prompts';
            }
            if (str_contains($alert, 'Taxa de sucesso baixa')) {
                $recommendations[] = 'Revisar qualidade dos títulos de entrada';
            }
            if (str_contains($alert, 'travados')) {
                $recommendations[] = 'Verificar timeouts da API e conexões';
            }
        }

        return array_unique($recommendations);
    }
}
