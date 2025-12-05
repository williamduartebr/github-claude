<?php

namespace Src\VehicleDataCenter\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class VehicleDataCenterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repository Bindings
        $this->app->bind(
            \Src\VehicleDataCenter\Domain\Repositories\VehicleMakeRepositoryInterface::class,
            \Src\VehicleDataCenter\Infrastructure\Repositories\VehicleMakeRepository::class
        );

        $this->app->bind(
            \Src\VehicleDataCenter\Domain\Repositories\VehicleModelRepositoryInterface::class,
            \Src\VehicleDataCenter\Infrastructure\Repositories\VehicleModelRepository::class
        );

        $this->app->bind(
            \Src\VehicleDataCenter\Domain\Repositories\VehicleVersionRepositoryInterface::class,
            \Src\VehicleDataCenter\Infrastructure\Repositories\VehicleVersionRepository::class
        );

        $this->app->bind(
            \Src\VehicleDataCenter\Domain\Repositories\VehicleSpecsRepositoryInterface::class,
            \Src\VehicleDataCenter\Infrastructure\Repositories\VehicleSpecsRepository::class
        );

        $this->app->bind(
            \Src\VehicleDataCenter\Domain\Repositories\VehicleMongoRepositoryInterface::class,
            \Src\VehicleDataCenter\Infrastructure\Repositories\VehicleMongoRepository::class
        );

        // Register Services
        $this->app->singleton(
            \Src\VehicleDataCenter\Domain\Services\VehicleIngestionService::class
        );

        $this->app->singleton(
            \Src\VehicleDataCenter\Domain\Services\VehicleSyncService::class
        );

        $this->app->singleton(
            \Src\VehicleDataCenter\Domain\Services\VehicleSearchService::class
        );

        $this->app->singleton(
            \Src\VehicleDataCenter\Domain\Services\VehicleSeoBuilderService::class
        );

        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vehicle-data-center.php',
            'vehicle-data-center'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // // Load migrations
        // $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Presentation/Resources/views', 'vehicle-data-center');

        // Register routes
        $this->registerRoutes();

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/vehicle-data-center.php' => config_path('vehicle-data-center.php'),
        ], 'vehicle-data-center-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Presentation/Resources/views' => resource_path('views/vendor/vehicle-data-center'),
        ], 'vehicle-data-center-views');

    }

    /**
     * Register routes
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'middleware' => config('vehicle-data-center.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        });
    }
}
