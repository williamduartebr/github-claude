<?php

namespace Src\ContentGeneration\TireSchedule\Provider;

use Illuminate\Support\ServiceProvider;

use Src\ContentGeneration\TireSchedule\Console\TireCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Console\CleanupTireScheduleTicker;
use Src\ContentGeneration\TireSchedule\Console\CleanupArticleTemplateTireTicker;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;


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
        
        // Registrar comando específico de pneus
        $this->commands([
            TireCorrectionsCommand::class,
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
            'command_registered' => 'TireCorrectionsCommand',
            'routes_loaded' => file_exists(base_path('src/ContentGeneration/TireSchedule/Routes/tire-corrections.php')),
            'schedule_active' => !app()->environment(['local', 'testing']),
            'schedule_health' => \Src\ContentGeneration\TireSchedule\Console\Schedules\TireCorrectionSchedule::getScheduleHealth(),
            'environment' => app()->environment(),
            'domain_focus' => 'when_to_change_tires',
            'correction_type' => 'TYPE_TIRE_PRESSURE_FIX'
        ];
    }

    /**
     * 📊 Obter estatísticas do serviço
     */
    public function getServiceStats(): array
    {
        try {
            $service = $this->app->make(TireCorrectionService::class);
            $stats = $service->getStats();

            // Estatísticas adicionais específicas para pneus
            $totalTireArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            $correctionRate = $totalTireArticles > 0 ? 
                round(($stats['total'] / $totalTireArticles) * 100, 2) : 0;

            return [
                'stats' => $stats,
                'total_tire_articles' => $totalTireArticles,
                'correction_rate' => $correctionRate . '%',
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

        // Verificar se o serviço está registrado
        if (!$this->app->bound(TireCorrectionService::class)) {
            $issues[] = '🚫 Serviço TireCorrectionService não registrado';
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
            \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::where('correction_type', 
                \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)->count();
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
            $service = $this->app->make(TireCorrectionService::class);
            $results['duplicates_cleanup'] = $service->cleanAllDuplicates();
        } catch (\Exception $e) {
            $results['duplicates_cleanup'] = ['error' => $e->getMessage()];
        }

        try {
            // Reset de correções de pneus travadas
            $resetCount = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::where('correction_type', 
                \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6))
                ->update([
                    'status' => \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            $results['stuck_processing_reset'] = ['count' => $resetCount];
        } catch (\Exception $e) {
            $results['stuck_processing_reset'] = ['error' => $e->getMessage()];
        }

        try {
            // Limpeza de correções de pneus falhadas antigas (mais de 48h)
            $deletedCount = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::where('correction_type', 
                \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(48))
                ->delete();

            $results['old_failed_cleanup'] = ['count' => $deletedCount];
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
            $tireStats = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::getTireStats();
            
            // Calcular tempo médio de processamento para correções de pneus
            $recentTireCorrections = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::where('correction_type', 
                \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->orderBy('processed_at', 'desc')
                ->limit(50)
                ->get();

            $avgProcessingTime = 0;
            if ($recentTireCorrections->count() > 0) {
                $totalTime = $recentTireCorrections->sum(function($correction) {
                    return $correction->processed_at ? 
                        $correction->processed_at->diffInSeconds($correction->created_at) : 0;
                });
                $avgProcessingTime = round($totalTime / $recentTireCorrections->count(), 2);
            }

            // Estatísticas de pressões corrigidas
            $pressureCorrections = $recentTireCorrections->filter(function($correction) {
                return isset($correction->correction_data['corrected_pressures']);
            })->count();

            return [
                'service_name' => 'TireCorrectionService',
                'tire_stats' => $tireStats,
                'performance' => [
                    'avg_processing_time_seconds' => $avgProcessingTime,
                    'recent_corrections_analyzed' => $recentTireCorrections->count(),
                    'pressure_corrections_count' => $pressureCorrections,
                    'current_queue_size' => $tireStats['pending'],
                    'processing_efficiency' => $tireStats['success_rate'] . '%',
                    'pressure_correction_rate' => $recentTireCorrections->count() > 0 ? 
                        round(($pressureCorrections / $recentTireCorrections->count()) * 100, 2) . '%' : '0%'
                ],
                'domain_metrics' => [
                    'target_domain' => 'when_to_change_tires',
                    'target_status' => 'draft',
                    'correction_type' => 'TYPE_TIRE_PRESSURE_FIX'
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
     * 🎯 Executar processamento manual da fila
     */
    public function processQueue(int $limit = 5): array
    {
        try {
            $service = $this->app->make(TireCorrectionService::class);
            $results = $service->processAllPendingCorrections($limit);

            return [
                'service_name' => 'TireCorrectionService',
                'queue_processing' => $results,
                'processed_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TireCorrectionService',
                'error' => $e->getMessage(),
                'processed_at' => now()->toISOString()
            ];
        }
    }

    /**
     * 🔧 Criar correções para novos artigos
     */
    public function createCorrections(int $limit = 100): array
    {
        try {
            $service = $this->app->make(TireCorrectionService::class);
            $slugs = $service->getAllTireArticleSlugs($limit);
            
            if (empty($slugs)) {
                return [
                    'service_name' => 'TireCorrectionService',
                    'message' => 'Nenhum artigo novo encontrado para correção',
                    'created_at' => now()->toISOString()
                ];
            }

            $results = $service->createCorrectionsForSlugs($slugs);

            return [
                'service_name' => 'TireCorrectionService',
                'creation_results' => $results,
                'articles_processed' => count($slugs),
                'created_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'service_name' => 'TireCorrectionService',
                'error' => $e->getMessage(),
                'created_at' => now()->toISOString()
            ];
        }
    }
}