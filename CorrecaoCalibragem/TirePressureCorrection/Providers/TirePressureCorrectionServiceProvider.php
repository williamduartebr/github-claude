<?php

namespace Src\TirePressureCorrection\Providers;

use Illuminate\Support\ServiceProvider;
use Src\TirePressureCorrection\Infrastructure\Services\ClaudeSonnetService;
use Src\TirePressureCorrection\Infrastructure\Services\TirePressureCorrectionService;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\TestMongoDbQueryCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\ApplyTirePressuresCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\CollectTirePressuresCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\DiagnoseTirePressureCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\Schedules\TirePressureCorrectionSchedule;

/**
 * Service Provider para o módulo de correção de pressões de pneus
 */
class TirePressureCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Commands que serão registrados
     */
    protected array $commands = [
        CollectTirePressuresCommand::class,
        ApplyTirePressuresCommand::class,
        TirePressureCorrectionSchedule::class,
        DiagnoseTirePressureCommand::class,
        TestMongoDbQueryCommand::class,
    ];

    /**
     * Registrar services
     */
    public function register(): void
    {
        // Registrar services
        $this->registerServices();

        // Registrar commands
        $this->registerCommands();

        // Registrar configurações
        $this->registerConfig();
    }

    /**
     * Boot do provider
     */
    public function boot(): void
    {
        // Publicar migrations se necessário
        // $this->publishMigrations();

        // Registrar schedules
        $this->registerSchedules();
    }

    /**
     * Registrar services do módulo
     */
    protected function registerServices(): void
    {
        // ClaudeSonnetService - reutilizar se já existir
        if (!$this->app->bound(ClaudeSonnetService::class)) {
            $this->app->singleton(ClaudeSonnetService::class);
        }

        // TirePressureCorrectionService
        $this->app->singleton(TirePressureCorrectionService::class, function ($app) {
            return new TirePressureCorrectionService(
                $app->make(ClaudeSonnetService::class)
            );
        });

        // Alias para facilitar
        $this->app->alias(
            TirePressureCorrectionService::class,
            'tire.pressure.correction.service'
        );
    }

    /**
     * Registrar commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);

            // Registrar aliases para commands
            $this->app->singleton('command.tire-pressure.collect', CollectTirePressuresCommand::class);
            $this->app->singleton('command.tire-pressure.apply', ApplyTirePressuresCommand::class);
            $this->app->singleton('command.tire-pressure.schedule', TirePressureCorrectionSchedule::class);
        }
    }

    /**
     * Registrar configurações
     */
    protected function registerConfig(): void
    {
        // Configurações do módulo
        $this->app->singleton('tire.pressure.correction.config', function () {
            return [
                // Limites padrão
                'default_article_limit' => 50,
                'default_group_limit' => 10,
                'default_apply_limit' => 100,

                // Rate limiting
                'api_rate_limit_seconds' => 120,
                'between_stages_delay' => 30,

                // Limpeza
                'cleanup_days' => 30,
                'recent_correction_hours' => 24,

                // Validação de pressões
                'pressure_limits' => [
                    'min' => 10,
                    'max' => 100,
                    'motorcycle_min' => 22,
                    'motorcycle_max' => 42,
                    'car_min' => 28,
                    'car_max' => 36,
                ],

                // Schedule
                'schedule_enabled' => env('TIRE_PRESSURE_CORRECTION_ENABLED', true),
                'schedule_interval' => 'everyThreeHours',
            ];
        });
    }

    /**
     * Publicar migrations
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $migrationsPath = __DIR__ . '/../../database/migrations';

            // Se houver migrations específicas no futuro
            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations')
                ], 'tire-pressure-correction-migrations');
            }
        }
    }

    /**
     * Registrar schedules
     */
    protected function registerSchedules(): void
    {
        if ($this->app->runningInConsole()) {
            // O schedule será registrado no console.php
            // mas podemos adicionar configurações aqui se necessário
            $this->app->booted(function () {
                $config = $this->app->make('tire.pressure.correction.config');

                // Log de inicialização
                if ($config['schedule_enabled']) {
                    \Log::info('TirePressureCorrectionProvider: Schedule habilitado', [
                        'interval' => $config['schedule_interval']
                    ]);
                }
            });
        }
    }

    /**
     * Services fornecidos pelo provider
     */
    public function provides(): array
    {
        return [
            TirePressureCorrectionService::class,
            'tire.pressure.correction.service',
            'tire.pressure.correction.config',
            'command.tire-pressure.collect',
            'command.tire-pressure.apply',
            'command.tire-pressure.schedule',
        ];
    }

    /**
     * Verificar dependências
     */
    protected function checkDependencies(): void
    {
        // Verificar se o modelo Article existe
        if (!class_exists(\Src\AutoInfoCenter\Domain\Eloquent\Article::class)) {
            throw new \Exception('TirePressureCorrection depende do modelo Article');
        }

        // Verificar se ClaudeSonnetService está disponível
        if (!class_exists(ClaudeSonnetService::class)) {
            \Log::warning('TirePressureCorrection: ClaudeSonnetService não encontrado, será criado');
        }
    }

    /**
     * Registrar event listeners se necessário
     */
    protected function registerEventListeners(): void
    {
        // Exemplo de listener para quando um artigo é criado/atualizado
        \Event::listen('article.created', function ($article) {
            if ($article->template === 'when_to_change_tires') {
                \Log::info('TirePressureCorrection: Novo artigo detectado', [
                    'article_id' => $article->_id,
                    'slug' => $article->slug
                ]);
            }
        });
    }
}
