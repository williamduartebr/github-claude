<?php

namespace Src\ContentGeneration\TireSchedule\Provider;

use Illuminate\Support\ServiceProvider;

use Src\ContentGeneration\TireSchedule\Console\TireCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Console\TitleYearCorrectionsCommand; // 🆕 NOVO
use Src\ContentGeneration\TireSchedule\Console\CleanupTireScheduleTicker;
use Src\ContentGeneration\TireSchedule\Console\CleanupArticleTemplateTireTicker;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TitleYearCorrectionService; // 🆕 NOVO
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class TireCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 🚗 Registrar o serviço de correção de pneus
        $this->app->singleton(TireCorrectionService::class, function ($app) {
            return new TireCorrectionService();
        });

        // 🆕 Registrar o serviço de correção de título/ano
        $this->app->singleton(TitleYearCorrectionService::class, function ($app) {
            return new TitleYearCorrectionService();
        });

        // Registrar comandos específicos de pneus
        $this->commands([
            TireCorrectionsCommand::class,
            TitleYearCorrectionsCommand::class,  // 🆕 NOVO
            CleanupArticleTemplateTireTicker::class,
            CleanupTireScheduleTicker::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Carregar rotas específicas de pneus
        $this->loadTireRoutes();

        // 🆕 Registrar schedule se não estiver em ambiente local/testing
        if (!app()->environment(['local', 'testing'])) {
            $this->registerTireSchedule();
        }
    }

    /**
     * Carregar rotas específicas de correções de pneus
     */
    private function loadTireRoutes(): void
    {
        $tireRoutePath = base_path('src/ContentGeneration/TireSchedule/Routes/tire-corrections.php');

        if (file_exists($tireRoutePath)) {
            $this->loadRoutesFrom($tireRoutePath);
        }
    }

    /**
     * 🆕 Registrar schedule específico para correções de pneus
     */
    private function registerTireSchedule(): void
    {
        // Registrar o schedule no callback do app
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // 🚗 Registrar schedule de correção de pneus
            \Src\ContentGeneration\TireSchedule\Console\Schedules\TireCorrectionSchedule::register($schedule);
        });
    }

    /**
     * 🔍 Verificar saúde do serviço de correções de pneus
     */
    public function getServiceHealth(): array
    {
        return [
            'service_name' => 'TireCorrectionService',
            'service_registered' => $this->app->bound(TireCorrectionService::class),
            'title_year_service_registered' => $this->app->bound(TitleYearCorrectionService::class), // 🆕
            'commands_registered' => [
                'TireCorrectionsCommand',
                'TitleYearCorrectionsCommand' // 🆕
            ],
            'routes_loaded' => file_exists(base_path('src/ContentGeneration/TireSchedule/Routes/tire-corrections.php')),
            'schedule_active' => !app()->environment(['local', 'testing']),
            'schedule_health' => \Src\ContentGeneration\TireSchedule\Console\Schedules\TireCorrectionSchedule::getScheduleHealth(),
            'environment' => app()->environment(),
            'domain_focus' => 'when_to_change_tires',
            'correction_types' => [
                'TYPE_TIRE_PRESSURE_FIX',
                'TYPE_TITLE_YEAR_FIX' // 🆕
            ]
        ];
    }

    /**
     * 📊 Obter estatísticas do serviço
     */
    public function getServiceStats(): array
    {
        try {
            $tireService = $this->app->make(TireCorrectionService::class);
            $titleYearService = $this->app->make(TitleYearCorrectionService::class); // 🆕

            $tireStats = $tireService->getStats();
            $titleYearStats = $titleYearService->getStats(); // 🆕

            // Estatísticas adicionais específicas para pneus
            $totalTireArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            $tireCorrectionRate = $totalTireArticles > 0 ?
                round(($tireStats['total'] / $totalTireArticles) * 100, 2) : 0;

            $titleYearCorrectionRate = $totalTireArticles > 0 ?
                round(($titleYearStats['total'] / $totalTireArticles) * 100, 2) : 0;

            return [
                'tire_stats' => $tireStats,
                'title_year_stats' => $titleYearStats, // 🆕
                'total_tire_articles' => $totalTireArticles,
                'tire_correction_rate' => $tireCorrectionRate . '%',
                'title_year_correction_rate' => $titleYearCorrectionRate . '%', // 🆕
                'service_status' => 'active',
                'last_updated' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'stats' => null,
                'service_status' => 'error',
                'error' => $e->getMessage(),
                'last_updated' => now()->toISOString()
            ];
        }
    }

    /**
     * 🚨 Diagnosticar problemas específicos do serviço de pneus
     */
    public function diagnoseService(): array
    {
        $issues = [];

        // Verificar se os serviços estão registrados
        if (!$this->app->bound(TireCorrectionService::class)) {
            $issues[] = '🚫 Serviço TireCorrectionService não registrado';
        }

        if (!$this->app->bound(TitleYearCorrectionService::class)) {
            $issues[] = '🚫 Serviço TitleYearCorrectionService não registrado'; // 🆕
        }

        // Verificar configuração da API Claude
        if (empty(config('services.claude.api_key'))) {
            $issues[] = '🚫 Chave da API Claude não configurada';
        }

        // Verificar problemas específicos dos schedules de pneus
        try {
            $scheduleIssues = \Src\ContentGeneration\TireSchedule\Console\Schedules\TireCorrectionSchedule::diagnoseIssues();
            $issues = array_merge($issues, $scheduleIssues);
        } catch (\Exception $e) {
            $issues[] = "🚫 Erro ao diagnosticar schedule de pneus: " . $e->getMessage();
        }

        // Verificar se há artigos de pneus disponíveis
        try {
            $availableArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            if ($availableArticles === 0) {
                $issues[] = '⚠️ Nenhum artigo de pneu disponível para correção';
            }
        } catch (\Exception $e) {
            $issues[] = "🚫 Erro ao verificar artigos disponíveis: " . $e->getMessage();
        }

        // Verificar conexão com MongoDB
        try {
            ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX
            )->count();

            ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            )->count(); // 🆕
        } catch (\Exception $e) {
            $issues[] = "🚫 Erro de conexão com MongoDB: " . $e->getMessage();
        }

        return [
            'service_name' => 'TireCorrectionService',
            'issues_found' => count($issues),
            'issues' => $issues,
            'status' => count($issues) === 0 ? 'healthy' : 'has_issues',
            'checked_at' => now()->toISOString()
        ];
    }

    /**
     * 🧹 Executar manutenção específica do serviço de pneus
     */
    public function runServiceMaintenance(): array
    {
        $results = [
            'service_name' => 'TireCorrectionService',
            'maintenance_started_at' => now()->toISOString()
        ];

        try {
            // Limpeza de correções de pneus duplicadas
            $tireService = $this->app->make(TireCorrectionService::class);
            $results['tire_duplicates_cleanup'] = $tireService->cleanAllDuplicates();
        } catch (\Exception $e) {
            $results['tire_duplicates_cleanup'] = ['error' => $e->getMessage()];
        }

        try {
            // 🆕 Limpeza de correções de título/ano duplicadas
            $titleYearService = $this->app->make(TitleYearCorrectionService::class);
            $results['title_year_duplicates_cleanup'] = $titleYearService->cleanAllDuplicates();
        } catch (\Exception $e) {
            $results['title_year_duplicates_cleanup'] = ['error' => $e->getMessage()];
        }

        try {
            // Reset de correções de pneus travadas
            $tireResetCount = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX
            )
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6))
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            // 🆕 Reset de correções de título/ano travadas
            $titleYearResetCount = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            )
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6))
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            $results['stuck_processing_reset'] = [
                'tire_corrections' => $tireResetCount,
                'title_year_corrections' => $titleYearResetCount
            ];
        } catch (\Exception $e) {
            $results['stuck_processing_reset'] = ['error' => $e->getMessage()];
        }

        try {
            // Limpeza de correções falhadas antigas (mais de 48h)
            $tireDeletedCount = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX
            )
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(48))
                ->delete();

            // 🆕 Limpeza de correções de título/ano falhadas antigas
            $titleYearDeletedCount = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            )
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(48))
                ->delete();

            $results['old_failed_cleanup'] = [
                'tire_corrections' => $tireDeletedCount,
                'title_year_corrections' => $titleYearDeletedCount
            ];
        } catch (\Exception $e) {
            $results['old_failed_cleanup'] = ['error' => $e->getMessage()];
        }

        $results['maintenance_completed_at'] = now()->toISOString();

        return $results;
    }

    /**
     * 📈 Obter métricas de performance específicas para pneus
     */
    public function getServicePerformance(): array
    {
        try {
            // Estatísticas específicas de pneus
            $tireStats = ArticleCorrection::getTireStats();
            $titleYearStats = ArticleCorrection::getTitleYearStats(); // 🆕

            // Calcular tempo médio de processamento para correções de pneus
            $recentTireCorrections = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX
            )
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->orderBy('processed_at', 'desc')
                ->limit(50)
                ->get();

            // 🆕 Calcular tempo médio para correções de título/ano
            $recentTitleYearCorrections = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            )
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->orderBy('processed_at', 'desc')
                ->limit(50)
                ->get();

            $avgTireProcessingTime = 0;
            if ($recentTireCorrections->count() > 0) {
                $totalTime = $recentTireCorrections->sum(function ($correction) {
                    return $correction->processed_at ?
                        $correction->processed_at->diffInSeconds($correction->created_at) : 0;
                });
                $avgTireProcessingTime = round($totalTime / $recentTireCorrections->count(), 2);
            }

            $avgTitleYearProcessingTime = 0;
            if ($recentTitleYearCorrections->count() > 0) {
                $totalTime = $recentTitleYearCorrections->sum(function ($correction) {
                    return $correction->processed_at ?
                        $correction->processed_at->diffInSeconds($correction->created_at) : 0;
                });
                $avgTitleYearProcessingTime = round($totalTime / $recentTitleYearCorrections->count(), 2);
            }

            // Estatísticas de pressões corrigidas
            $pressureCorrections = $recentTireCorrections->filter(function ($correction) {
                return isset($correction->correction_data['corrected_pressures']);
            })->count();

            // 🆕 Estatísticas de atualizações de título/ano
            $titleUpdates = $recentTitleYearCorrections->filter(function ($correction) {
                return $correction->correction_data['title_updated'] ?? false;
            })->count();

            $metaUpdates = $recentTitleYearCorrections->filter(function ($correction) {
                return $correction->correction_data['meta_updated'] ?? false;
            })->count();

            $faqUpdates = $recentTitleYearCorrections->filter(function ($correction) {
                return $correction->correction_data['faq_updated'] ?? false;
            })->count();

            return [
                'service_name' => 'TireCorrectionService',
                'tire_stats' => $tireStats,
                'title_year_stats' => $titleYearStats, // 🆕
                'performance' => [
                    'tire_corrections' => [
                        'avg_processing_time_seconds' => $avgTireProcessingTime,
                        'recent_corrections_analyzed' => $recentTireCorrections->count(),
                        'pressure_corrections_count' => $pressureCorrections,
                        'current_queue_size' => $tireStats['pending'],
                        'processing_efficiency' => $tireStats['success_rate'] . '%',
                        'pressure_correction_rate' => $recentTireCorrections->count() > 0 ?
                            round(($pressureCorrections / $recentTireCorrections->count()) * 100, 2) . '%' : '0%'
                    ],
                    'title_year_corrections' => [ // 🆕
                        'avg_processing_time_seconds' => $avgTitleYearProcessingTime,
                        'recent_corrections_analyzed' => $recentTitleYearCorrections->count(),
                        'title_updates_count' => $titleUpdates,
                        'meta_updates_count' => $metaUpdates,
                        'faq_updates_count' => $faqUpdates,
                        'current_queue_size' => $titleYearStats['pending'],
                        'processing_efficiency' => $titleYearStats['success_rate'] . '%',
                        'title_update_rate' => $recentTitleYearCorrections->count() > 0 ?
                            round(($titleUpdates / $recentTitleYearCorrections->count()) * 100, 2) . '%' : '0%',
                        'meta_update_rate' => $recentTitleYearCorrections->count() > 0 ?
                            round(($metaUpdates / $recentTitleYearCorrections->count()) * 100, 2) . '%' : '0%',
                        'faq_update_rate' => $recentTitleYearCorrections->count() > 0 ?
                            round(($faqUpdates / $recentTitleYearCorrections->count()) * 100, 2) . '%' : '0%'
                    ]
                ],
                'domain_metrics' => [
                    'target_domain' => 'when_to_change_tires',
                    'target_status' => 'draft',
                    'correction_types' => [
                        'TYPE_TIRE_PRESSURE_FIX',
                        'TYPE_TITLE_YEAR_FIX'
                    ]
                ],
                'measured_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TireCorrectionService',
                'error' => $e->getMessage(),
                'measured_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🎯 Executar processamento manual da fila de pneus
     */
    public function processTireQueue(int $limit = 5): array
    {
        try {
            $service = $this->app->make(TireCorrectionService::class);
            $results = $service->processAllPendingCorrections($limit);

            return [
                'service_name' => 'TireCorrectionService',
                'queue_type' => 'tire_pressure',
                'queue_processing' => $results,
                'processed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TireCorrectionService',
                'queue_type' => 'tire_pressure',
                'error' => $e->getMessage(),
                'processed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🎯 Executar processamento manual da fila de título/ano
     */
    public function processTitleYearQueue(int $limit = 5): array
    {
        try {
            $service = $this->app->make(TitleYearCorrectionService::class);
            $results = $service->processAllPendingCorrections($limit);

            return [
                'service_name' => 'TitleYearCorrectionService',
                'queue_type' => 'title_year',
                'queue_processing' => $results,
                'processed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TitleYearCorrectionService',
                'queue_type' => 'title_year',
                'error' => $e->getMessage(),
                'processed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🔧 Criar correções de pneus para novos artigos
     */
    public function createTireCorrections(int $limit = 100): array
    {
        try {
            $service = $this->app->make(TireCorrectionService::class);
            $slugs = $service->getAllTireArticleSlugs($limit);

            if (empty($slugs)) {
                return [
                    'service_name' => 'TireCorrectionService',
                    'correction_type' => 'tire_pressure',
                    'message' => 'Nenhum artigo novo encontrado para correção de pneus',
                    'created_at' => now()->toISOString()
                ];
            }

            $results = $service->createCorrectionsForSlugs($slugs);

            return [
                'service_name' => 'TireCorrectionService',
                'correction_type' => 'tire_pressure',
                'creation_results' => $results,
                'articles_processed' => count($slugs),
                'created_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TireCorrectionService',
                'correction_type' => 'tire_pressure',
                'error' => $e->getMessage(),
                'created_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🔧 Criar correções de título/ano para novos artigos
     */
    public function createTitleYearCorrections(int $limit = 100): array
    {
        try {
            $service = $this->app->make(TitleYearCorrectionService::class);
            $slugs = $service->getAllTireArticleSlugs($limit);

            if (empty($slugs)) {
                return [
                    'service_name' => 'TitleYearCorrectionService',
                    'correction_type' => 'title_year',
                    'message' => 'Nenhum artigo novo encontrado para correção de título/ano',
                    'created_at' => now()->toISOString()
                ];
            }

            $results = $service->createCorrectionsForSlugs($slugs);

            return [
                'service_name' => 'TitleYearCorrectionService',
                'correction_type' => 'title_year',
                'creation_results' => $results,
                'articles_processed' => count($slugs),
                'created_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TitleYearCorrectionService',
                'correction_type' => 'title_year',
                'error' => $e->getMessage(),
                'created_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 📊 Obter estatísticas consolidadas de ambos os serviços
     */
    public function getConsolidatedStats(): array
    {
        try {
            return ArticleCorrection::getConsolidatedStats();
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'generated_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🔧 Executar manutenção completa de ambos os serviços
     */
    public function runFullMaintenance(): array
    {
        $results = [
            'service_name' => 'TireCorrectionService',
            'maintenance_type' => 'full_service',
            'maintenance_started_at' => now()->toISOString()
        ];

        // Executar manutenção básica
        $basicMaintenance = $this->runServiceMaintenance();
        $results['basic_maintenance'] = $basicMaintenance;

        // Reset adicional de correções muito antigas
        try {
            $oldStuckTire = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TIRE_PRESSURE_FIX
            )
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(24))
                ->update([
                    'status' => ArticleCorrection::STATUS_FAILED,
                    'error_message' => 'Timeout - processamento travado por mais de 24h',
                    'updated_at' => now()
                ]);

            $oldStuckTitleYear = ArticleCorrection::where(
                'correction_type',
                ArticleCorrection::TYPE_TITLE_YEAR_FIX
            )
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(24))
                ->update([
                    'status' => ArticleCorrection::STATUS_FAILED,
                    'error_message' => 'Timeout - processamento travado por mais de 24h',
                    'updated_at' => now()
                ]);

            $results['old_stuck_cleanup'] = [
                'tire_corrections_failed' => $oldStuckTire,
                'title_year_corrections_failed' => $oldStuckTitleYear
            ];
        } catch (\Exception $e) {
            $results['old_stuck_cleanup'] = ['error' => $e->getMessage()];
        }

        $results['maintenance_completed_at'] = now()->toISOString();

        return $results;
    }

    /**
     * 🎯 Executar processamento de ambas as filas
     */
    public function processAllQueues(int $tireLimit = 3, int $titleYearLimit = 5): array
    {
        $results = [
            'service_name' => 'TireCorrectionService',
            'processing_type' => 'all_queues',
            'processing_started_at' => now()->toISOString()
        ];

        // Processar fila de pneus
        $results['tire_queue'] = $this->processTireQueue($tireLimit);

        // Aguardar entre processamentos
        sleep(60); // 1 minuto

        // Processar fila de título/ano
        $results['title_year_queue'] = $this->processTitleYearQueue($titleYearLimit);

        $results['processing_completed_at'] = now()->toISOString();

        return $results;
    }

    /**
     * 🔧 Criar correções para ambos os tipos
     */
    public function createAllCorrections(int $limit = 50): array
    {
        $results = [
            'service_name' => 'TireCorrectionService',
            'creation_type' => 'all_corrections',
            'creation_started_at' => now()->toISOString()
        ];

        // Criar correções de pneus
        $results['tire_corrections'] = $this->createTireCorrections($limit);

        // Criar correções de título/ano
        $results['title_year_corrections'] = $this->createTitleYearCorrections($limit);

        $results['creation_completed_at'] = now()->toISOString();

        return $results;
    }
}
