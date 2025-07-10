<?php

namespace Src\ContentGeneration\TireSchedule\Console\Schedules;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class TireCorrectionSchedule
{
    /**
     * 🚗 Schedules OTIMIZADOS para correção de artigos sobre pneus
     * Versão 3.0 - Sistema híbrido com micro-services + comandos legados
     * Performance aprimorada baseada nos testes de sucesso
     */
    public static function register(Schedule $schedule): void
    {
        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return;
        }

        // ========================================
        // 🎯 WORKFLOW PRINCIPAL OTIMIZADO (NOVO)
        // ========================================
        
        // Workflow híbrido a cada 10 minutos - combina criação + processamento
        $schedule->call(function () {
            $pendingTire = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            $pendingTitle = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Se há poucas correções pendentes, criar novas
            if ($pendingTire < 20) {
                Artisan::call('tire-pressure-corrections', [
                    '--workflow' => true,
                    '--limit' => 100,
                    '--force' => true
                ]);
                
                Log::info("🎯 Workflow de pneus executado", [
                    'pending_before' => $pendingTire,
                    'type' => 'pressure'
                ]);
            }

            if ($pendingTitle < 30) {
                Artisan::call('tire-title-year-corrections', [
                    '--all' => true,
                    '--limit' => 50,
                    '--force' => true
                ]);
                
                Log::info("🎯 Workflow de título/ano executado", [
                    'pending_before' => $pendingTitle,
                    'type' => 'title_year'
                ]);
            }
        })
            ->everyTenMinutes()
            ->name('tire-hybrid-workflow')
            ->withoutOverlapping(8);

        // ========================================
        // ⚡ PROCESSAMENTO CONTÍNUO
        // ========================================
        
        // Processamento de pressões a cada 2 minutos (mais agressivo)
        // FIXED: Only Artisan commands can use runInBackground()
        $schedule->command('tire-pressure-corrections --process --limit=2 --force')
            ->everyTwoMinutes()
            ->name('tire-pressure-processing-v3')
            ->withoutOverlapping(1)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-processing.log'));

        // Processamento de título/ano a cada 3 minutos
        $schedule->command('tire-title-year-corrections --process --limit=1 --force')
            ->cron('*/3 * * * *')
            ->name('title-year-processing-v3')
            ->withoutOverlapping(2)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/title-year-processing.log'));

        // ========================================
        // 🚨 SISTEMA DE RECUPERAÇÃO AUTOMÁTICA
        // ========================================
        
        // Comando específico para artigos problemáticos - a cada 30 minutos
        // FIXED: Removed runInBackground() from closure
        $schedule->call(function () {
            // Buscar artigos com problemas críticos não resolvidos
            $problematicSlugs = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->where(function($query) {
                    $query->where('content.introducao', 'like', '%{year}%')
                          ->orWhere('vehicle_data.pressure_loaded_display', '0/0 PSI')
                          ->orWhere('seo_data.page_title', 'like', '%N/A N/A N/A%');
                })
                ->limit(10)
                ->pluck('slug');

            foreach ($problematicSlugs as $slug) {
                try {
                    Artisan::call('fix-specific-article', [
                        'slug' => $slug,
                        '--force' => true,
                        '--create-correction' => true
                    ]);
                    
                    Log::info("🔧 Artigo problemático corrigido: {$slug}");
                } catch (\Exception $e) {
                    Log::error("❌ Falha ao corrigir artigo {$slug}: " . $e->getMessage());
                }
            }
        })
            ->everyThirtyMinutes()
            ->name('auto-fix-problematic-articles')
            ->withoutOverlapping(25);

        // ========================================
        // 📊 MONITORAMENTO INTELIGENTE
        // ========================================

        // Stats e health check unificados - a cada hora
        $schedule->call(function () {
            // Stats consolidadas
            Artisan::call('tire-pressure-corrections', ['--stats' => true]);
            
            // Health check automático
            $issues = self::diagnoseIssues();
            $health = self::getScheduleHealth();
            
            if (!empty($issues)) {
                Log::warning('⚠️ Issues detectados no sistema', [
                    'issues' => $issues,
                    'health' => $health
                ]);
                
                // Auto-correção de problemas simples
                self::autoFixSimpleIssues($issues);
            }
            
            // Log de performance
            $tireStats = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)->count();
            $titleStats = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)->count();
            
            Log::info('📊 Status do sistema', [
                'tire_corrections_total' => $tireStats,
                'title_corrections_total' => $titleStats,
                'health_version' => $health['version'],
                'issues_count' => count($issues)
            ]);
        })
            ->hourly()
            ->name('intelligent-monitoring')
            ->withoutOverlapping(10);

        // ========================================
        // 🧹 MANUTENÇÃO OTIMIZADA
        // ========================================

        // Reset de travamentos mais agressivo - a cada 4 horas
        $schedule->call(function () {
            $resetCount = ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(2)) // Reduzido de 4h para 2h
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            if ($resetCount > 0) {
                Log::info("🔄 Reset automático de travamentos", [
                    'reset_count' => $resetCount,
                    'threshold_hours' => 2
                ]);
            }
        })
            ->cron('0 */4 * * *') // A cada 4 horas
            ->name('aggressive-stuck-reset')
            ->withoutOverlapping(5);

        // Limpeza de falhas recentes - diário às 2h
        $schedule->call(function () {
            $cleanupResults = [
                'old_failures' => 0,
                'duplicates' => 0,
                'orphaned_corrections' => 0
            ];

            // Limpar falhas antigas (reduzido de 48h para 24h)
            $cleanupResults['old_failures'] = ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(24))
                ->delete();

            // Limpar duplicatas automaticamente
            Artisan::call('tire-pressure-corrections', [
                '--clean-duplicates' => true,
                '--force' => true
            ]);

            // Limpar correções órfãs (artigos que não existem mais)
            $orphanedSlugs = ArticleCorrection::whereNotIn('article_slug', 
                \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                    ->pluck('slug')
                    ->toArray()
            )->pluck('article_slug')->unique();

            foreach ($orphanedSlugs as $slug) {
                $deleted = ArticleCorrection::where('article_slug', $slug)->delete();
                $cleanupResults['orphaned_corrections'] += $deleted;
            }

            if (array_sum($cleanupResults) > 0) {
                Log::info("🧹 Limpeza diária executada", $cleanupResults);
            }
        })
            ->dailyAt('02:00')
            ->name('daily-aggressive-cleanup')
            ->withoutOverlapping(30);

        // Limpeza profunda semanal - aos domingos às 3h
        $schedule->call(function () {
            // Stats antes da limpeza
            $beforeStats = [
                'tire_total' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)->count(),
                'title_total' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)->count()
            ];

            // Limpeza profunda
            Artisan::call('tire-title-year-corrections', [
                '--clean-duplicates' => true,
                '--force' => true
            ]);

            // Reset de correções muito antigas sem sucesso
            $veryOldReset = ArticleCorrection::where('status', ArticleCorrection::STATUS_PENDING)
                ->where('created_at', '<', now()->subDays(7))
                ->delete();

            // Stats depois da limpeza
            $afterStats = [
                'tire_total' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)->count(),
                'title_total' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)->count()
            ];

            Log::info("🧹 Limpeza profunda semanal", [
                'before' => $beforeStats,
                'after' => $afterStats,
                'very_old_reset' => $veryOldReset,
                'cleaned_total' => ($beforeStats['tire_total'] + $beforeStats['title_total']) - 
                                  ($afterStats['tire_total'] + $afterStats['title_total'])
            ]);
        })
            ->weeklyOn(0, '03:00') // Domingo às 3h
            ->name('weekly-deep-cleanup')
            ->withoutOverlapping(60);

        // ========================================
        // 🚨 ALERTAS CRÍTICOS
        // ========================================

        // Sistema de alertas mais inteligente - a cada 2 horas
        $schedule->call(function () {
            $criticalAlerts = [];
            $warningAlerts = [];

            // Verificar Claude API health
            try {
                $apiService = app(\Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\ClaudeApiService::class);
                $apiStats = $apiService->getApiStats();
                
                if (!$apiStats['api_available']) {
                    $criticalAlerts[] = "🚨 Claude API indisponível há " . $apiStats['seconds_since_last_request'] . "s";
                }
            } catch (\Exception $e) {
                $criticalAlerts[] = "🚨 Erro ao verificar Claude API: " . $e->getMessage();
            }

            // Verificar backlog crítico
            $tirePending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            $titlePending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($tirePending > 150) {
                $criticalAlerts[] = "🚨 Backlog crítico de pneus: {$tirePending}";
            } elseif ($tirePending > 100) {
                $warningAlerts[] = "⚠️ Backlog alto de pneus: {$tirePending}";
            }

            if ($titlePending > 200) {
                $criticalAlerts[] = "🚨 Backlog crítico de títulos: {$titlePending}";
            } elseif ($titlePending > 150) {
                $warningAlerts[] = "⚠️ Backlog alto de títulos: {$titlePending}";
            }

            // Verificar taxa de falhas
            $recentFailures = ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '>', now()->subHours(4))
                ->count();

            if ($recentFailures > 25) {
                $criticalAlerts[] = "🚨 Taxa de falhas crítica: {$recentFailures} em 4h";
            } elseif ($recentFailures > 15) {
                $warningAlerts[] = "⚠️ Taxa de falhas elevada: {$recentFailures} em 4h";
            }

            // Log apenas se houver alertas
            if (!empty($criticalAlerts)) {
                Log::critical('🚨 ALERTAS CRÍTICOS DO SISTEMA', [
                    'critical_alerts' => $criticalAlerts,
                    'warning_alerts' => $warningAlerts,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
            } elseif (!empty($warningAlerts)) {
                Log::warning('⚠️ Alertas de atenção', [
                    'warning_alerts' => $warningAlerts,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
            }
        })
            ->cron('0 */2 * * *') // A cada 2 horas
            ->name('critical-alerts-system')
            ->withoutOverlapping(5);
    }

    /**
     * 🔧 Auto-correção de problemas simples
     */
    private static function autoFixSimpleIssues(array $issues): void
    {
        foreach ($issues as $issue) {
            try {
                // Reset automático de processamentos travados
                if (strpos($issue, 'travadas há mais de') !== false) {
                    $resetCount = ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)
                        ->where('updated_at', '<', now()->subHours(1))
                        ->update([
                            'status' => ArticleCorrection::STATUS_PENDING,
                            'updated_at' => now()
                        ]);
                    
                    if ($resetCount > 0) {
                        Log::info("🔧 Auto-correção: {$resetCount} processamentos resetados");
                    }
                }

                // Reduzir backlog automaticamente
                if (strpos($issue, 'Backlog alto') !== false) {
                    Artisan::call('tire-pressure-corrections', [
                        '--process' => true,
                        '--limit' => 10,
                        '--force' => true
                    ]);
                    
                    Log::info("🔧 Auto-correção: processamento em lote executado para reduzir backlog");
                }
            } catch (\Exception $e) {
                Log::error("❌ Falha na auto-correção: " . $e->getMessage());
            }
        }
    }

    /**
     * 📋 Método para verificar saúde dos schedules (atualizado)
     */
    public static function getScheduleHealth(): array
    {
        return [
            'schedule_name' => 'TireCorrectionSchedule',
            'version' => '3.0_hybrid_optimized',
            'main_workflow' => [
                'hybrid_workflow' => 'A cada 10 minutos',
                'pressure_processing' => 'A cada 2 minutos',
                'title_processing' => 'A cada 3 minutos'
            ],
            'recovery_systems' => [
                'auto_fix_problematic' => 'A cada 30 minutos',
                'aggressive_stuck_reset' => 'A cada 4 horas',
                'daily_cleanup' => 'Diário às 2h'
            ],
            'monitoring_systems' => [
                'intelligent_monitoring' => 'A cada hora',
                'critical_alerts' => 'A cada 2 horas',
                'weekly_deep_cleanup' => 'Semanal domingo às 3h'
            ],
            'total_schedules' => 9,
            'runtime' => '24 horas por dia',
            'optimization_level' => 'high_performance',
            'auto_recovery' => 'enabled',
            'domain_focus' => 'when_to_change_tires (TempArticles)',
            'correction_types' => [
                'TYPE_TIRE_PRESSURE_FIX',
                'TYPE_TITLE_YEAR_FIX'
            ]
        ];
    }

    /**
     * 🔧 Método para diagnosticar problemas comuns (melhorado)
     */
    public static function diagnoseIssues(): array
    {
        $issues = [];

        try {
            // Verificar se há TempArticles disponíveis
            $availableArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            if ($availableArticles === 0) {
                $issues[] = "⚠️ Nenhum TempArticle disponível para correção (domain: when_to_change_tires)";
            }

            // Verificar backlog excessivo
            $tirePending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            $titleYearPending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($tirePending > 150) {
                $issues[] = "🚨 Backlog crítico de correções de pneus: {$tirePending}";
            } elseif ($tirePending > 100) {
                $issues[] = "📈 Backlog alto de correções de pneus: {$tirePending}";
            }

            if ($titleYearPending > 200) {
                $issues[] = "🚨 Backlog crítico de correções de título/ano: {$titleYearPending}";
            } elseif ($titleYearPending > 150) {
                $issues[] = "📈 Backlog alto de correções de título/ano: {$titleYearPending}";
            }

            // Verificar processamentos travados
            $stuckProcessing = ArticleCorrection::where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(2))
                ->count();

            if ($stuckProcessing > 10) {
                $issues[] = "🚨 Muitos processamentos travados: {$stuckProcessing}";
            } elseif ($stuckProcessing > 5) {
                $issues[] = "⚠️ Processamentos travados detectados: {$stuckProcessing}";
            }

            // Verificar se há processamento recente
            $recentTireProcessing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('processed_at', '>', now()->subHours(1))
                ->exists();

            $recentTitleYearProcessing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('processed_at', '>', now()->subHours(1))
                ->exists();

            if (!$recentTireProcessing && $tirePending > 0) {
                $issues[] = "🚫 Nenhum processamento de pneus na última hora";
            }

            if (!$recentTitleYearProcessing && $titleYearPending > 0) {
                $issues[] = "🚫 Nenhum processamento de título/ano na última hora";
            }

            // Verificar taxa de falhas recentes
            $recentFailures = ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '>', now()->subHours(6))
                ->count();

            if ($recentFailures > 25) {
                $issues[] = "🚨 Taxa de falhas muito alta: {$recentFailures} nas últimas 6 horas";
            } elseif ($recentFailures > 15) {
                $issues[] = "⚠️ Taxa de falhas elevada: {$recentFailures} nas últimas 6 horas";
            }

        } catch (\Exception $e) {
            $issues[] = "❌ Erro no diagnóstico: " . $e->getMessage();
        }

        return $issues;
    }

    /**
     * 📊 Método para obter métricas de performance
     */
    public static function getPerformanceMetrics(): array
    {
        try {
            $now = now();
            
            return [
                'corrections_last_hour' => [
                    'tire' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                        ->where('status', ArticleCorrection::STATUS_COMPLETED)
                        ->where('processed_at', '>', $now->subHour())
                        ->count(),
                    'title' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                        ->where('status', ArticleCorrection::STATUS_COMPLETED)
                        ->where('processed_at', '>', $now->subHour())
                        ->count()
                ],
                'corrections_last_24h' => [
                    'tire' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                        ->where('status', ArticleCorrection::STATUS_COMPLETED)
                        ->where('processed_at', '>', $now->subDay())
                        ->count(),
                    'title' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                        ->where('status', ArticleCorrection::STATUS_COMPLETED)
                        ->where('processed_at', '>', $now->subDay())
                        ->count()
                ],
                'pending_queue' => [
                    'tire' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                        ->where('status', ArticleCorrection::STATUS_PENDING)
                        ->count(),
                    'title' => ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                        ->where('status', ArticleCorrection::STATUS_PENDING)
                        ->count()
                ],
                'success_rate_24h' => [
                    'tire' => self::calculateSuccessRate(ArticleCorrection::TYPE_TIRE_PRESSURE_FIX),
                    'title' => self::calculateSuccessRate(ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ],
                'generated_at' => $now->toISOString()
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 📈 Calcular taxa de sucesso
     */
    private static function calculateSuccessRate(string $type): float
    {
        $completed = ArticleCorrection::where('correction_type', $type)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('processed_at', '>', now()->subDay())
            ->count();

        $failed = ArticleCorrection::where('correction_type', $type)
            ->where('status', ArticleCorrection::STATUS_FAILED)
            ->where('processed_at', '>', now()->subDay())
            ->count();

        $total = $completed + $failed;
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}