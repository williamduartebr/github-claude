<?php

namespace Src\Sitemap\Provider;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\Sitemap\Domain\Services\SitemapService;
use Src\Sitemap\Infrastructure\Commands\SitemapGenerateCommand;
use Src\Sitemap\Infrastructure\Observers\ArticleObserver;
use Src\Sitemap\Infrastructure\Middleware\SitemapMiddleware;

class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar o serviço principal
        $this->app->singleton(SitemapService::class, function ($app) {
            return new SitemapService();
        });
        
        // Registrar comandos
        $this->commands([
            SitemapGenerateCommand::class,
        ]);
        
        // Registrar middleware
        $this->app['router']->aliasMiddleware('sitemap', SitemapMiddleware::class);
        
        // Registrar configuração
        $this->mergeConfigFrom(__DIR__ . '/../Config/sitemap.php', 'sitemap');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar observer para invalidação automática de cache
        Article::observe(ArticleObserver::class);
        
        // Carregar rotas
        $this->loadRoutes();
        
        // Publicar configuração
        $this->publishes([
            __DIR__ . '/../Config/sitemap.php' => config_path('sitemap.php'),
        ], 'sitemap-config');
        
        // Configurar agendamento automático
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $this->scheduleCommands($schedule);
        });
        
        // Criar diretório de sitemaps se não existir
        $this->ensureSitemapDirectory();
    }

    /**
     * Carregar rotas do sitemap
     */
    private function loadRoutes(): void
    {
        $routePath = base_path('src/Sitemap/Routes/sitemap.php');
        if (file_exists($routePath)) {
            $this->loadRoutesFrom($routePath);
        }
    }

    /**
     * Agendar comandos automáticos
     */
    private function scheduleCommands(Schedule $schedule): void
    {
        // Regenerar sitemaps diariamente às 02:00
        $schedule->command('sitemap:generate --clear-cache')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // Submeter aos motores de busca semanalmente
        $schedule->command('sitemap:generate --submit')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping()
            ->runInBackground();
    }
    
    /**
     * Garantir que o diretório de sitemaps existe
     */
    private function ensureSitemapDirectory(): void
    {
        $sitemapPath = storage_path('app/public/sitemaps');
        
        if (!is_dir($sitemapPath)) {
            mkdir($sitemapPath, 0755, true);
        }
    }
}