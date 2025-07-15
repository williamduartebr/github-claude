<?php

namespace Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Repositories\VehicleRepository;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Repositories\VehicleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\DebugCategoriesCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Repositories\TireChangeArticleRepository;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Repositories\TireChangeArticleRepositoryInterface;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\ProcessVehicleBatchCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\PublishTireArticlesCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\DebugExistingArticlesCommand;

use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\ImportVehiclesFromCsvCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\DebugContentGenerationCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\InstallWhenToChangeTiresCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\SyncBlogWhenToChangeTiresCommand;
use Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Console\Commands\GenerateInitialTireArticlesCommand;

class WhenToChangeTiresServiceProvider extends ServiceProvider
{
    /**
     * Commands que serão registrados
     */
    protected array $commands = [
        GenerateInitialTireArticlesCommand::class,
        ProcessVehicleBatchCommand::class,
        ImportVehiclesFromCsvCommand::class,
        InstallWhenToChangeTiresCommand::class,
        DebugContentGenerationCommand::class,
        SyncBlogWhenToChangeTiresCommand::class,
        PublishTireArticlesCommand::class,
        DebugCategoriesCommand::class,
        DebugExistingArticlesCommand::class,
    ];

    /**
     * Services que serão registrados como singletons
     * IMPORTANTE: Propriedade deve ser public para Laravel acessar
     */
    public array $singletons = [
        VehicleRepositoryInterface::class => VehicleRepository::class,
        TireChangeArticleRepositoryInterface::class => TireChangeArticleRepository::class,
    ];

    /**
     * Registrar serviços
     */
    public function register(): void
    {
        // Registrar services principais
        $this->registerCoreServices();

        // Registrar commands
        $this->registerCommands();

        // Registrar configurações
        $this->registerConfigurations();
    }

    /**
     * Boot do provider
     */
    public function boot(): void
    {
        // Publicar configurações
        $this->publishConfigurations();

        // Publicar migrations
        $this->publishMigrations();

        // Registrar scheduled tasks
        $this->registerScheduledTasks();

        // Registrar event listeners
        $this->registerEventListeners();
    }

    /**
     * Registrar services principais
     */
    protected function registerCoreServices(): void
    {
        // VehicleDataProcessorService
        $this->app->singleton(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\VehicleDataProcessorService::class
        );

        // TemplateBasedContentService
        $this->app->singleton(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TemplateBasedContentService::class
        );

        // ArticleJsonStorageService
        $this->app->singleton(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\ArticleJsonStorageService::class
        );

        // TireChangeArticleService
        $this->app->singleton(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TireChangeArticleService::class
        );

        // EventDispatcherService
        $this->app->singleton(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\EventDispatcherService::class
        );

        // Use Cases
        $this->app->bind(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Application\UseCases\GenerateInitialArticlesUseCase::class
        );

        $this->app->bind(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Application\UseCases\ProcessVehicleBatchUseCase::class
        );

        $this->app->bind(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Application\UseCases\ValidateVehiclesForGenerationUseCase::class
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
     * Registrar configurações
     */
    protected function registerConfigurations(): void
    {
        // Configurações do módulo
        $configPath = base_path('src/ContentGeneration/WhenToChangeTiresWithYear/config/when-to-change-tires.php');

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'when-to-change-tires');
        }
    }

    /**
     * Publicar configurações
     */
    protected function publishConfigurations(): void
    {
        if ($this->app->runningInConsole()) {
            $configPath = base_path('src/ContentGeneration/WhenToChangeTiresWithYear/config/when-to-change-tires.php');

            if (file_exists($configPath)) {
                $this->publishes([
                    $configPath => config_path('when-to-change-tires.php'),
                ], 'when-to-change-tires-config');
            }
        }
    }

    /**
     * Publicar migrations
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $migrationsPath = base_path('src/ContentGeneration/WhenToChangeTiresWithYear/database/migrations');

            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations'),
                ], 'when-to-change-tires-migrations');
            }
        }
    }

    /**
     * Registrar tarefas agendadas
     */
    protected function registerScheduledTasks(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Gerar artigos diariamente às 2:00 AM
            $schedule->command('when-to-change-tires:generate-initial-articles --batch-size=30')
                ->name('tire-articles-auto-generation')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->runInBackground()
                ->onSuccess(function () {
                    Log::info('Geração automática de artigos concluída com sucesso');
                })
                ->onFailure(function () {
                    Log::error('Falha na geração automática de artigos');
                });

            // Limpeza semanal às 3:00 AM no domingo
            $schedule->call(function () {
                $service = app(\Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TireChangeArticleService::class);
                $result = $service->cleanupOldArticles(90);
                Log::info('Limpeza automática executada', $result);
            })
                ->name('tire-articles-cleanup')
                ->weeklyOn(0, '03:00') // Domingo às 3:00
                ->withoutOverlapping();

            // Validação de integridade semanal
            $schedule->call(function () {
                $service = app(\Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Services\TireChangeArticleService::class);
                $result = $service->validateArticleIntegrity();

                if ($result['has_issues']) {
                    Log::warning('Problemas de integridade encontrados', $result);
                }
            })
                ->name('tire-articles-validation')
                ->weeklyOn(1, '01:00') // Segunda às 1:00
                ->withoutOverlapping();
        });
    }

    /**
     * Registrar event listeners
     */
    protected function registerEventListeners(): void
    {
        // Event listener para quando um artigo é criado
        Event::listen(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events\TireChangeArticleCreated::class,
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Listeners\LogArticleCreated::class
        );

        // Event listener para quando um artigo é refinado pelo Claude
        Event::listen(
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Domain\Events\TireChangeArticleEnhanced::class,
            \Src\ContentGeneration\WhenToChangeTiresWithYear\Infrastructure\Listeners\LogArticleEnhanced::class
        );
    }

    /**
     * Verificar se o módulo está habilitado
     */
    public function isEnabled(): bool
    {
        return config('when-to-change-tires.enabled', true);
    }

    /**
     * Obter services fornecidos
     */
    public function provides(): array
    {
        return array_merge(
            array_keys($this->singletons),
            $this->commands
        );
    }
}
