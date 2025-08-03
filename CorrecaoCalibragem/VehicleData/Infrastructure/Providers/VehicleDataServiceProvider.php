<?php

namespace Src\VehicleData\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\VehicleData\Infrastructure\Console\Commands\ExtractVehicleDataCommand;
use Src\VehicleData\Infrastructure\Console\Commands\ValidateVehicleDataCommand;
use Src\VehicleData\Infrastructure\Console\Commands\CleanVehicleDataCommand;
use Src\VehicleData\Infrastructure\Console\Commands\VehicleDataStatsCommand;

/**
 * VehicleDataServiceProvider - Provider para o módulo de dados de veículos
 * 
 * Responsável por registrar os commands criados para extração,
 * validação, limpeza e estatísticas de dados de veículos
 */
class VehicleDataServiceProvider extends ServiceProvider
{
    /**
     * Commands que serão registrados
     */
    protected array $commands = [
        ExtractVehicleDataCommand::class,
        ValidateVehicleDataCommand::class,
        CleanVehicleDataCommand::class,
        VehicleDataStatsCommand::class,
        \Src\VehicleData\Infrastructure\Console\Commands\SearchVehicleCommand::class,
    ];

    /**
     * Registrar serviços no container
     */
    public function register(): void
    {
        // Registrar commands
        $this->registerCommands();

        // Registrar configurações básicas
        $this->registerConfigurations();
    }

    /**
     * Boot do provider - executado após todos os providers
     */
    public function boot(): void
    {
        // Publicar migrations se estiver em console
        // $this->publishMigrations();
    }

    /**
     * Registrar commands do módulo
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);

            // Registrar aliases para facilitar uso
            $this->app->singleton('command.vehicle-data.extract', ExtractVehicleDataCommand::class);
            $this->app->singleton('command.vehicle-data.validate', ValidateVehicleDataCommand::class);
            $this->app->singleton('command.vehicle-data.clean', CleanVehicleDataCommand::class);
            $this->app->singleton('command.vehicle-data.stats', VehicleDataStatsCommand::class);
        }
    }

    /**
     * Registrar configurações básicas do módulo
     */
    protected function registerConfigurations(): void
    {
        // Configurações principais do módulo
        $this->app->singleton('vehicle-data.config', function ($app) {
            return [
                'extraction' => [
                    'default_batch_size' => 100,
                    'max_batch_size' => 1000,
                    'retry_attempts' => 3,
                    'timeout_seconds' => 300,
                ],
                'validation' => [
                    'min_quality_score' => 6.0,
                    'required_fields' => ['make', 'model', 'year', 'main_category'],
                    'auto_validation' => true,
                ],
                'cleanup' => [
                    'remove_duplicates' => true,
                    'archive_old_data' => false,
                    'retention_days' => 365,
                ]
            ];
        });

        // Registrar regras de qualidade de dados
        $this->app->singleton('vehicle-data.quality-rules', function ($app) {
            return [
                'pressure_ranges' => [
                    'motorcycle' => ['min' => 20, 'max' => 45],
                    'hatch' => ['min' => 22, 'max' => 40],
                    'sedan' => ['min' => 24, 'max' => 42],
                    'suv' => ['min' => 26, 'max' => 48],
                    'pickup' => ['min' => 30, 'max' => 80],
                    'car_electric' => ['min' => 22, 'max' => 50],
                ],
                'year_ranges' => [
                    'min_year' => 1950,
                    'max_year' => now()->year + 2,
                ],
                'mandatory_fields' => [
                    'basic' => ['make', 'model', 'year'],
                    'pressure' => ['pressure_light_front', 'pressure_light_rear'],
                    'category' => ['main_category', 'vehicle_segment'],
                ]
            ];
        });
    }

    /**
     * Publicar migrations
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $migrationsPath = __DIR__ . '/../../Database/Migrations';

            if (is_dir($migrationsPath)) {
                $this->publishes([
                    $migrationsPath => database_path('migrations')
                ], 'vehicle-data-migrations');
            }
        }
    }

    /**
     * Definir services que este provider oferece
     */
    public function provides(): array
    {
        return array_merge($this->commands, [
            'vehicle-data.config',
            'vehicle-data.quality-rules',
        ]);
    }
}
