<?php

namespace Src\GuideDataCenter\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories Contracts
use Src\GuideDataCenter\Domain\Mongo\Guide;
use Src\GuideDataCenter\Observers\GuideObserver;
use Src\GuideDataCenter\Domain\Services\GuideImportService;


// Repositories Implementations
use Src\GuideDataCenter\Domain\Services\GuideCreationService;
use Src\GuideDataCenter\Domain\Services\GuideValidatorService;
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideRepository;

// Services
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideCategoryRepository;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;

/**
 * GuideDataCenter Service Provider
 * 
 * Registra todos os bindings, repositories, services e configurações
 * do módulo GuideDataCenter
 */
class GuideDataCenterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra Repositories
        $this->registerRepositories();

        // Registra Services
        $this->registerServices();

        // Merge configurations
        $this->mergeConfigFrom(
            __DIR__ . '/../config/guide-datacenter.php',
            'guide-datacenter'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->publishResources();

        Guide::observe(GuideObserver::class);
    }

    /**
     * Registra os repositories com suas interfaces
     */
    protected function registerRepositories(): void
    {
        // Guide Repository
        $this->app->bind(
            GuideRepositoryInterface::class,
            GuideRepository::class
        );

        // Guide Category Repository
        $this->app->bind(
            GuideCategoryRepositoryInterface::class,
            GuideCategoryRepository::class
        );

    }

    /**
     * Registra os services como singletons
     */
    protected function registerServices(): void
    {
        // Guide Validator Service
        $this->app->singleton(GuideValidatorService::class, function ($app) {
            return new GuideValidatorService();
        });

        // Guide Creation Service
        $this->app->singleton(GuideCreationService::class, function ($app) {
            return new GuideCreationService(
                $app->make(GuideRepositoryInterface::class),
                $app->make(GuideCategoryRepositoryInterface::class),
                $app->make(GuideValidatorService::class),
            );
        });

        // Guide Import Service
        $this->app->singleton(GuideImportService::class, function ($app) {
            return new GuideImportService(
                $app->make(GuideRepositoryInterface::class),
                $app->make(GuideCreationService::class),
                $app->make(GuideValidatorService::class)
            );
        });

        // Guide Relationship Service
        $this->app->singleton(
            \Src\GuideDataCenter\Domain\Services\GuideRelationshipService::class,
            function ($app) {
                return new \Src\GuideDataCenter\Domain\Services\GuideRelationshipService(
                    $app->make(\Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface::class),
                    $app->make(\Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface::class)
                );
            }
        );
    }

    /**
     * Publica recursos do módulo
     */
    protected function publishResources(): void
    {
        // // Carrega migrations
        // $this->loadMigrationsFrom(__DIR__ . '/../Migrations/mongo');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Presentation/Resources/views', 'guide-data-center');

        // Publica configurações
        $this->publishes([
            __DIR__ . '/../config/guide-datacenter.php' => config_path('guide-datacenter.php'),
        ], 'guide-datacenter-config');

        // // Registra seeders
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         // Adicionar commands aqui se necessário
        //     ]);
        // }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            // Repositories
            GuideRepositoryInterface::class,
            GuideCategoryRepositoryInterface::class,

            // Services
            GuideCreationService::class,
            GuideImportService::class,
            GuideValidatorService::class,
        ];
    }

    /**
     * Carrega as rotas do módulo
     */
    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Presentation/Routes/web.php');
    }

    /**
     * Carrega as views do módulo
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../Presentation/Resources/views',
            'guide'
        );
    }
}
