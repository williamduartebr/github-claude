<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\DebugFailedVehiclesCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\SyncBlogTiresPressureCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\TestEnhancedProcessingCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\DiagnosticCsvProcessingCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\PublishTirePressureArticlesCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\GenerateTirePressureArticlesCommand;
use Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands\PublishTempTirePressureArticlesCommand;

/**
 * Updated TirePressureGuideServiceProvider
 * 
 * ADICIONADO:
 * - PublishTempTirePressureArticlesCommand para testes
 * - ExtendedTirePressureGuideApplicationService com novos métodos
 * - Binding para InitialArticleGeneratorService modificado
 */
class TirePressureGuideServiceProvider extends ServiceProvider
{
    /**
     * Commands que serão registrados
     */
    protected array $commands = [
        GenerateTirePressureArticlesCommand::class,
        PublishTirePressureArticlesCommand::class,
        PublishTempTirePressureArticlesCommand::class,
        DiagnosticCsvProcessingCommand::class,
        DebugFailedVehiclesCommand::class,
        SyncBlogTiresPressureCommand::class,
        TestEnhancedProcessingCommand::class,
    ];

    /**
     * Registrar serviços
     */
    public function register(): void
    {
        // Registrar services da Etapa 1 (modificados)
        $this->registerCoreServices();

        // Registrar services estendidos
        $this->registerExtendedServices();

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
     * Registrar services essenciais da Etapa 1 (modificados)
     */
    protected function registerCoreServices(): void
    {
        // VehicleDataProcessorService - processa CSV (mantém igual)
        $this->app->singleton(
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService::class
        );

        // InitialArticleGeneratorService - MODIFICADO para novo formato
        $this->app->singleton(
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService::class,
            function ($app) {
                // Aqui podemos injetar dependências específicas se necessário
                return new \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService();
            }
        );

        // GenerateInitialArticlesUseCase - orquestra o processo
        $this->app->bind(
            \Src\ContentGeneration\TirePressureGuide\Application\UseCases\GenerateInitialArticlesUseCase::class
        );
    }

    /**
     * Registrar services estendidos
     */
    protected function registerExtendedServices(): void
    {
        // TirePressureGuideApplicationService - ESTENDIDO com novos métodos
        $this->app->singleton(
            \Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService::class,
            function ($app) {
                // Usar a versão estendida
                return new \Src\ContentGeneration\TirePressureGuide\Application\Services\ExtendedTirePressureGuideApplicationService();
            }
        );

        // Alias para facilitar injeção
        $this->app->alias(
            \Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService::class,
            'tire.pressure.guide.service'
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

    /**
     * Registrar bindings de configuração
     */
    protected function registerConfigBindings(): void
    {
        // Configurações específicas do sistema
        $this->app->singleton('tire.pressure.config', function ($app) {
            return [
                'default_template_car' => 'ideal_tire_pressure_car',
                'default_template_motorcycle' => 'ideal_tire_pressure_motorcycle',
                'supported_formats' => ['json', 'xml', 'yaml'],
                'validation_rules' => [
                    'min_content_score' => 5.0,
                    'required_sections_count' => 6,
                    'max_errors_per_article' => 3
                ],
                'auto_fix_enabled' => true,
                'compatibility_threshold' => 80.0
            ];
        });
    }

    /**
     * Obter services disponíveis para informação
     */
    public function provides(): array
    {
        return [
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\VehicleDataProcessorService::class,
            \Src\ContentGeneration\TirePressureGuide\Infrastructure\Services\InitialArticleGeneratorService::class,
            \Src\ContentGeneration\TirePressureGuide\Application\UseCases\GenerateInitialArticlesUseCase::class,
            \Src\ContentGeneration\TirePressureGuide\Application\Services\TirePressureGuideApplicationService::class,
            'tire.pressure.guide.service',
            'tire.pressure.config'
        ];
    }

    /**
     * Definir aliases de commands para facilitar uso
     */
    protected function registerCommandAliases(): void
    {
        if ($this->app->runningInConsole()) {
            // Aliases mais curtos para os commands
            $this->app->singleton('command.tire-pressure.generate', 
                GenerateTirePressureArticlesCommand::class);
            
            $this->app->singleton('command.tire-pressure.publish', 
                PublishTirePressureArticlesCommand::class);
            
            $this->app->singleton('command.tire-pressure.publish-temp', 
                PublishTempTirePressureArticlesCommand::class);
        }
    }
}