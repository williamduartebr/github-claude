<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\GenerateInitialTirePressureArticlesCommand;

class TirePressureGuideServiceProvider extends ServiceProvider
{
    /**
     * Commands que serão registrados
     */
    protected array $commands = [
        GenerateInitialTirePressureArticlesCommand::class,
    ];

    /**
     * Registrar serviços
     */
    public function register(): void
    {
        // Registrar services da Etapa 1
        $this->registerCoreServices();

        // Registrar commands
        $this->registerCommands();
    }

    /**
     * Boot do provider
     */
    public function boot(): void
    {
        // Publicar migrations se estiver em console
        $this->publishMigrations();
    }

    /**
     * Registrar services essenciais da Etapa 1
     */
    protected function registerCoreServices(): void
    {
        // VehicleDataProcessorService - processa CSV
        $this->app->singleton(
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService::class
        );

        // InitialArticleGeneratorService - gera conteúdo
        $this->app->singleton(
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService::class
        );

        // GenerateInitialArticlesUseCase - orquestra o processo
        $this->app->bind(
            \Src\ContentGeneration\TirePressureGuide\Application\UseCases\GenerateInitialArticlesUseCase::class
        );
    }

    /**
     * Registrar commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Publicar migrations
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $migrationsPath = __DIR__ . '/../../database/migrations';

            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations')
                ], 'tire-pressure-guide-migrations');
            }
        }
    }
}