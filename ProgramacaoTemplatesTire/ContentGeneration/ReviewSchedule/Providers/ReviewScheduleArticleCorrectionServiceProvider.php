<?php

namespace Src\ContentGeneration\ReviewSchedule\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\ReviewSchedule\Console\FixIntroductionContentCommand;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\ArticleIntroductionCorrectionService;

class ReviewScheduleArticleCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        // 🎨 Registrar o novo serviço de correção de introdução
        $this->app->singleton(ArticleIntroductionCorrectionService::class, function ($app) {
            return new ArticleIntroductionCorrectionService();
        });
        
        // Registrar comandos
        $this->commands([
            FixIntroductionContentCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Carregar rotas da API
        $this->loadRoutes();
    }

    /**
     * Carregar rotas das correções de artigos
     */
    private function loadRoutes(): void
    {
        // Rotas originais de correção
        $routePath = base_path('src/ArticleGenerator/Routes/article-corrections.php');
        
        if (file_exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }

        // 🆕 Novas rotas de correção de introdução
        $introRoutePath = base_path('src/ArticleGenerator/Routes/article-introduction-corrections.php');
        
        if (file_exists($introRoutePath)) {
            $this->loadRoutesFrom($introRoutePath);
        }
    }
}