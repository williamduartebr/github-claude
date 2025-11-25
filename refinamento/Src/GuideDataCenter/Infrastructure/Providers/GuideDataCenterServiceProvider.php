<?php

namespace Src\GuideDataCenter\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

// Repositories Contracts
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideCategoryRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideClusterRepositoryInterface;
use Src\GuideDataCenter\Domain\Repositories\Contracts\GuideSeoRepositoryInterface;

// Repositories Implementations
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideRepository;
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideCategoryRepository;
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideClusterRepository;
use Src\GuideDataCenter\Domain\Repositories\Mongo\GuideSeoRepository;

// Services
use Src\GuideDataCenter\Domain\Services\GuideCreationService;
use Src\GuideDataCenter\Domain\Services\GuideClusterService;
use Src\GuideDataCenter\Domain\Services\GuideSeoService;
use Src\GuideDataCenter\Domain\Services\GuideImportService;
use Src\GuideDataCenter\Domain\Services\GuideValidatorService;

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
            __DIR__ . '/../../config/guide-datacenter.php',
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

        // Guide Cluster Repository
        $this->app->bind(
            GuideClusterRepositoryInterface::class,
            GuideClusterRepository::class
        );

        // Guide SEO Repository
        $this->app->bind(
            GuideSeoRepositoryInterface::class,
            GuideSeoRepository::class
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

        // Guide SEO Service
        $this->app->singleton(GuideSeoService::class, function ($app) {
            return new GuideSeoService(
                $app->make(GuideSeoRepositoryInterface::class)
            );
        });

        // Guide Cluster Service
        $this->app->singleton(GuideClusterService::class, function ($app) {
            return new GuideClusterService(
                $app->make(GuideClusterRepositoryInterface::class),
                $app->make(GuideRepositoryInterface::class)
            );
        });

        // Guide Creation Service
        $this->app->singleton(GuideCreationService::class, function ($app) {
            return new GuideCreationService(
                $app->make(GuideRepositoryInterface::class),
                $app->make(GuideCategoryRepositoryInterface::class),
                $app->make(GuideValidatorService::class),
                $app->make(GuideSeoService::class),
                $app->make(GuideClusterService::class)
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
    }

        /**
     * Publica recursos do módulo
     */
    protected function publishResources(): void
    {
        // // Carrega migrations
        // $this->loadMigrationsFrom(__DIR__ . '/../../Migrations/mongo');

                // Load views
        $this->loadViewsFrom(__DIR__ . '/../Presentation/Resources/views', 'vehicle-data-center');

        // Publica configurações
        $this->publishes([
            __DIR__ . '/../../config/guide-datacenter.php' => config_path('guide-datacenter.php'),
        ], 'guide-datacenter-config');

        // // Publica migrations
        // $this->publishes([
        //     __DIR__ . '/../../Migrations/mongo' => database_path('migrations/mongo'),
        // ], 'guide-datacenter-migrations');

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
            GuideClusterRepositoryInterface::class,
            GuideSeoRepositoryInterface::class,

            // Services
            GuideCreationService::class,
            GuideClusterService::class,
            GuideSeoService::class,
            GuideImportService::class,
            GuideValidatorService::class,
        ];
    }

      /**
     * Carrega as rotas do módulo
     */
    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/web.php');
    }

    /**
     * Carrega as views do módulo
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../Presentation/Resources/views',
            'guide'
        );
    }
}
