<?php

namespace Src\ContentGeneration\TireSchedule\Provider;

use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\TireSchedule\Console\TireCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Console\FindTireArticlesCommand;
use Src\ContentGeneration\TireSchedule\Console\CleanupTireScheduleTicker;
use Src\ContentGeneration\TireSchedule\Console\FixSpecificArticleCommand;

// Commands
use Src\ContentGeneration\TireSchedule\Console\TitleYearCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Console\ForceCompleteCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Console\VerifyCorrectionCreationCommand;
use Src\ContentGeneration\TireSchedule\Console\CleanupArticleTemplateTireTicker;

// Micro-Services
use Src\ContentGeneration\TireSchedule\Console\EmergencyTitleCorrectionsCommand;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionOrchestrator;

// Legacy Services (compatibilidade)
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TitleYearCorrectionService;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\ClaudeApiService;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\MicroServices\TireDataValidationService;

class TireCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 🆕 Micro-Services (Nova Arquitetura)
        $this->app->singleton(TireDataValidationService::class);
        $this->app->singleton(ClaudeApiService::class);
        
        $this->app->singleton(TireCorrectionOrchestrator::class, function ($app) {
            return new TireCorrectionOrchestrator(
                $app->make(TireDataValidationService::class),
                $app->make(ClaudeApiService::class)
            );
        });

        // 🔄 Legacy Services (compatibilidade temporária)
        $this->app->singleton(TireCorrectionService::class);
        $this->app->singleton(TitleYearCorrectionService::class);

        // 📋 Commands
        $this->commands([
            TireCorrectionsCommand::class,
            TitleYearCorrectionsCommand::class,
            FixSpecificArticleCommand::class,
            CleanupArticleTemplateTireTicker::class,
            CleanupTireScheduleTicker::class,
            ForceCompleteCorrectionsCommand::class,
            VerifyCorrectionCreationCommand::class,
            EmergencyTitleCorrectionsCommand::class,
            FindTireArticlesCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Carregar rotas
        $this->loadRoutesFrom(
            base_path('src/ContentGeneration/TireSchedule/Routes/tire-corrections.php')
        );

        // Schedule apenas em produção/staging
        if (in_array(app()->environment(), ['production', 'staging'])) {
            $this->registerSchedule();
        }
    }

    /**
     * 📅 Schedule otimizado
     */
    private function registerSchedule(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // 🎯 Workflow principal - a cada 10 minutos
            // FIXED: Removed runInBackground() from closure
            $schedule->call(function () {
                $orchestrator = app(TireCorrectionOrchestrator::class);
                $results = $orchestrator->runOptimizedWorkflow(30, 2);
                
                \Illuminate\Support\Facades\Log::info('🎯 Workflow executado', [
                    'created' => $results['steps']['creation']['corrections_created'] ?? 0,
                    'processed' => $results['steps']['processing']['successful'] ?? 0,
                    'duration' => $results['total_duration_seconds'] ?? 0
                ]);
            })
                ->everyTenMinutes()
                ->name('tire-workflow')
                ->withoutOverlapping(8);
                // Removed ->runInBackground() as it's not supported for closures

            // 🧹 Limpeza - a cada 30 minutos
            $schedule->call(function () {
                $orchestrator = app(TireCorrectionOrchestrator::class);
                $results = $orchestrator->intelligentCleanup();
                
                if (array_sum($results) > 0) {
                    \Illuminate\Support\Facades\Log::info('🧹 Limpeza executada', $results);
                }
            })
                ->everyThirtyMinutes()
                ->name('tire-cleanup')
                ->withoutOverlapping(25);

            // 🔄 Fallback legado - apenas se micro-services falharem
            // This can use runInBackground() because it's an Artisan command
            $schedule->command('tire-pressure-corrections --process --limit=1 --force')
                ->everyThirtyMinutes()
                ->name('tire-fallback')
                ->withoutOverlapping(5)
                ->runInBackground()
                ->when(function () {
                    $lastSuccess = \Illuminate\Support\Facades\Cache::get('tire_microservices_last_success', 0);
                    return (time() - $lastSuccess) > 3600; // 1 hora sem sucesso
                });
        });
    }
}