<?php

namespace Src\ContentGeneration\ReviewSchedule\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\ReviewSchedule\Console\CsvStatsCommand;
use Src\ContentGeneration\ReviewSchedule\Console\FixPricesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\QuickContentCheck;
use Src\ContentGeneration\ReviewSchedule\Console\FixOverviewSection;
use Src\ContentGeneration\ReviewSchedule\Console\DebugOverviewIssues;
use Src\ContentGeneration\ReviewSchedule\Console\FixDetailedSchedule;
use Src\ContentGeneration\ReviewSchedule\Console\AnalyzeArticleQuality;
use Src\ContentGeneration\ReviewSchedule\Console\AnalyzeOverviewSection;
use Src\ContentGeneration\ReviewSchedule\Console\CleanDuplicatesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\DebugArticlesStructure;
use Src\ContentGeneration\ReviewSchedule\Console\DebugGenerationCommand;
use Src\ContentGeneration\ReviewSchedule\Console\PublishArticlesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\ResetFutureSyncCommand;
use Src\ContentGeneration\ReviewSchedule\Console\AnalyzeDetailedSchedule;
use Src\ContentGeneration\ReviewSchedule\Console\GenerateArticlesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\FindRealOverviewProblems;
use Src\ContentGeneration\ReviewSchedule\Console\DebugCarCostIssuesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\ValidateCarContentCommand;
use Src\ContentGeneration\ReviewSchedule\Console\UpdateArticleStatusCommand;
use Src\ContentGeneration\ReviewSchedule\Console\CleanupReviewScheduleTicker;
use Src\ContentGeneration\ReviewSchedule\Console\CleanDuplicatesSimpleCommand;
use Src\ContentGeneration\ReviewSchedule\Console\PublishToTempArticlesCommand;
use Src\ContentGeneration\ReviewSchedule\Console\ValidateHybridContentCommand;
use Src\ContentGeneration\ReviewSchedule\Console\FixCarDetailedScheduleCommand;
use Src\ContentGeneration\ReviewSchedule\Console\FixIntroductionContentCommand;
use Src\ContentGeneration\ReviewSchedule\Console\SyncBlogReviewScheduleCommand;
use Src\ContentGeneration\ReviewSchedule\Console\ValidateElectricContentCommand;
use Src\ContentGeneration\ReviewSchedule\Console\FixBrokenReviewScheduleArticles;
use Src\ContentGeneration\ReviewSchedule\Console\FixHybridDetailedScheduleCommand;
use Src\ContentGeneration\ReviewSchedule\Console\ValidateMotorcycleContentCommand;
use Src\ContentGeneration\ReviewSchedule\Console\CleanupArticleTemplateReviewTicker;
use Src\ContentGeneration\ReviewSchedule\Console\FixElectricDetailedScheduleCommand;
use Src\ContentGeneration\ReviewSchedule\Domain\Services\VehicleTypeDetectorService;
use Src\ContentGeneration\ReviewSchedule\Console\FixMotorcycleDetailedScheduleCommand;
use Src\ContentGeneration\ReviewSchedule\Domain\Services\ArticleContentGeneratorService;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Repositories\CsvVehicleRepository;
use Src\ContentGeneration\ReviewSchedule\Application\Services\ReviewScheduleApplicationService;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\CarMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\MotorcycleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\HybridVehicleMaintenanceTemplate;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Repositories\MongoReviewScheduleArticleRepository;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\ContentTemplates\ElectricVehicleMaintenanceTemplate;

class ReviewScheduleServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar repositories
        $this->app->singleton(CsvVehicleRepository::class, function ($app) {
            return new CsvVehicleRepository($app->make(VehicleTypeDetectorService::class));
        });

        $this->app->singleton(MongoReviewScheduleArticleRepository::class);

        // Registrar templates de conteúdo
        $this->app->singleton(CarMaintenanceTemplate::class);
        $this->app->singleton(MotorcycleMaintenanceTemplate::class);
        $this->app->singleton(ElectricVehicleMaintenanceTemplate::class);
        $this->app->singleton(HybridVehicleMaintenanceTemplate::class);

        // Registrar services
        $this->app->singleton(VehicleTypeDetectorService::class);

        $this->app->singleton(ArticleContentGeneratorService::class, function ($app) {
            return new ArticleContentGeneratorService(
                $app->make(CarMaintenanceTemplate::class),
                $app->make(MotorcycleMaintenanceTemplate::class),
                $app->make(ElectricVehicleMaintenanceTemplate::class),
                $app->make(HybridVehicleMaintenanceTemplate::class),
                $app->make(VehicleTypeDetectorService::class)
            );
        });

        $this->app->singleton(ReviewScheduleApplicationService::class, function ($app) {
            return new ReviewScheduleApplicationService(
                $app->make(ArticleContentGeneratorService::class),
                $app->make(CsvVehicleRepository::class),
                $app->make(MongoReviewScheduleArticleRepository::class)
            );
        });
    }

    public function boot()
    {
        // Registrar comandos console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Comandos principais
                GenerateArticlesCommand::class,
                CsvStatsCommand::class,
                PublishArticlesCommand::class,

                // Debug, análise e correções
                DebugGenerationCommand::class,
                DebugArticlesStructure::class,
                AnalyzeArticleQuality::class,
                AnalyzeDetailedSchedule::class,
                FixDetailedSchedule::class,
                AnalyzeOverviewSection::class,
                FixOverviewSection::class,
                QuickContentCheck::class,
                DebugOverviewIssues::class,
                FindRealOverviewProblems::class,
                DebugCarCostIssuesCommand::class,
                FixPricesCommand::class,    
                FixIntroductionContentCommand::class,

                FixMotorcycleDetailedScheduleCommand::class,
                ValidateMotorcycleContentCommand::class,

                FixHybridDetailedScheduleCommand::class,
                ValidateHybridContentCommand::class,

                FixCarDetailedScheduleCommand::class,
                ValidateCarContentCommand::class,

                FixElectricDetailedScheduleCommand::class,
                ValidateElectricContentCommand::class,

                CleanDuplicatesCommand::class,
                CleanDuplicatesSimpleCommand::class,

                // Sincronização e utilitários
                SyncBlogReviewScheduleCommand::class,
                ResetFutureSyncCommand::class,

                // Limpeza
                CleanupArticleTemplateReviewTicker::class,
                CleanupReviewScheduleTicker::class,

                // Publicar
                PublishToTempArticlesCommand::class,
                UpdateArticleStatusCommand::class,



            ]);
            
        }

        //  $this->loadRoutes();
    }

 
    /**
     * Carregar rotas das correções de artigos
     */
    private function loadRoutes(): void
    {
        // Rotas originais de correção
        $routePath = base_path('src/ContentGeneration/ReviewSchedule/Routes/article-corrections.php');
        
        if (file_exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }

        // 🆕 Novas rotas de correção de introdução
        $introRoutePath = base_path('src/ContentGeneration/ReviewSchedule/Routes/article-introduction-corrections.php');
        
        if (file_exists($introRoutePath)) {
            $this->loadRoutesFrom($introRoutePath);
        }
    }
}
