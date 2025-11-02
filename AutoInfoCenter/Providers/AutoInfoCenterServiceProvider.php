<?php

namespace Src\AutoInfoCenter\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Src\AutoInfoCenter\ViewModels\ArticleViewModel;
use Src\AutoInfoCenter\Domain\Services\ArticleService;
use Src\AutoInfoCenter\Factories\TemplateViewModelFactory;
// use Src\AutoInfoCenter\ViewModels\Templates\TirePressureViewModel;
use Src\AutoInfoCenter\Domain\Repositories\ArticleRepository;
use Src\AutoInfoCenter\ViewModels\Templates\TemplateViewModel;
use Src\AutoInfoCenter\Domain\Services\TemplateDetectorService;
use Src\AutoInfoCenter\ViewModels\Templates\GenericArticleViewModel;
use Src\AutoInfoCenter\Domain\Repositories\ArticleRepositoryInterface;
use Src\AutoInfoCenter\ViewModels\Templates\OilRecommendationViewModel;

class AutoInfoCenterServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços no container.
     *
     * @return void
     */
    public function register()
    {
        // Repositories
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);

        // Services
        $this->app->singleton(ArticleService::class, function ($app) {
            return new ArticleService(
                $app->make(ArticleRepositoryInterface::class)
            );
        });

        $this->app->singleton(TemplateDetectorService::class, function ($app) {
            return new TemplateDetectorService();
        });

        // Factories
        $this->app->singleton(TemplateViewModelFactory::class, function ($app) {
            return new TemplateViewModelFactory();
        });

        // ViewModels
        $this->app->bind(ArticleViewModel::class, function ($app) {
            return new ArticleViewModel(
                $app->make(ArticleService::class),
                $app->make(TemplateDetectorService::class),
                $app->make(TemplateViewModelFactory::class)
            );
        });

        // Template ViewModels - registrando apenas quando necessário via factory
        $this->app->bind(OilRecommendationViewModel::class);
        //    $this->app->bind(TirePressureViewModel::class);
        $this->app->bind(GenericArticleViewModel::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Carbon::setLocale('pt_BR');

        // Registra as views
        $this->loadViewsFrom(base_path('src/AutoInfoCenter/Presentation/Resources/views'), 'auto-info-center');
        // $this->loadViewsFrom(base_path('resources/views/auto-info-center'), 'auto-info-center');

        // Registra as rotas
        $this->loadRoutesFrom(base_path('src/AutoInfoCenter/Presentation/Routes/web.php'));

        //     // Registra as views
        //     $this->loadViewsFrom(__DIR__.'/../Resources/views', 'auto-info-center');

        //    // Publicação de assets
        //    $this->publishes([
        //        __DIR__.'/../Resources/assets' => public_path('vendor/auto-info-center'),
        //    ], 'auto-info-center-assets');

        //    // Publicação de views para customização
        //    $this->publishes([
        //        __DIR__.'/../Resources/views' => resource_path('views/vendor/auto-info-center'),
        //    ], 'auto-info-center-views');
    }
}
