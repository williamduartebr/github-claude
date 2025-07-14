<?php

namespace Src\ArticleGenerator\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ArticleGenerator\Infrastructure\Console\ProcessArticleCorrections;
use Src\ArticleGenerator\Infrastructure\Services\ArticleCorrectionService;
use Src\ArticleGenerator\Infrastructure\Console\ManageManualArticles;

class ArticleCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar o serviço principal de correção
        $this->app->singleton(ArticleCorrectionService::class, function ($app) {
            return new ArticleCorrectionService();
        });
        
        // Registrar comandos
        $this->commands([
            ProcessArticleCorrections::class,
            ManageManualArticles::class, // NOVO COMANDO ADICIONADO

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
        $routePath = base_path('src/ArticleGenerator/Routes/article-corrections.php');
        
        if (file_exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }
    }
}