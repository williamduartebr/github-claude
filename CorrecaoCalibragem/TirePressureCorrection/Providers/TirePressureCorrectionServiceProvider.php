<?php

namespace Src\TirePressureCorrection\Providers;

use Illuminate\Support\ServiceProvider;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\UpdateTirePressuresFromVehicleDataCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\DiagnosticTirePressureStatusCommand;
use Src\TirePressureCorrection\Infrastructure\Console\Commands\Schedules\TirePressureCorrectionSchedule;

/**
 * Service Provider para o módulo de correção de pressões de pneus via VehicleData
 * 
 * Versão limpa - apenas commands essenciais usando VehicleData
 */
class TirePressureCorrectionServiceProvider extends ServiceProvider
{
    /**
     * Commands essenciais (método VehicleData)
     */
    protected array $commands = [
        UpdateTirePressuresFromVehicleDataCommand::class,
        DiagnosticTirePressureStatusCommand::class,
        TirePressureCorrectionSchedule::class,
    ];

    /**
     * Registrar services
     */
    public function register(): void
    {
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
        // Registrar schedules
        $this->registerSchedules();
    }

    /**
     * Registrar commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);

            // Aliases para commands principais
            $this->app->singleton('command.tire-pressure.update-from-vehicle-data', UpdateTirePressuresFromVehicleDataCommand::class);
            $this->app->singleton('command.tire-pressure.diagnostic', DiagnosticTirePressureStatusCommand::class);
            $this->app->singleton('command.tire-pressure.schedule', TirePressureCorrectionSchedule::class);
        }
    }

    /**
     * Registrar configurações
     */
    protected function registerConfig(): void
    {
        $this->app->singleton('tire.pressure.correction.config', function () {
            return [
                // Configurações para VehicleData
                'vehicle_data' => [
                    'min_quality_score' => env('TIRE_PRESSURE_MIN_QUALITY_SCORE', 6.0),
                    'max_year_difference' => env('TIRE_PRESSURE_MAX_YEAR_DIFF', 5),
                    'prefer_exact_year' => env('TIRE_PRESSURE_PREFER_EXACT_YEAR', true),
                    'fallback_to_similar' => env('TIRE_PRESSURE_FALLBACK_SIMILAR', true),
                ],

                // Limites padrão
                'limits' => [
                    'default_article_limit' => env('TIRE_PRESSURE_DEFAULT_LIMIT', 50),
                    'max_article_limit' => env('TIRE_PRESSURE_MAX_LIMIT', 500),
                ],

                // Limpeza e manutenção
                'cleanup' => [
                    'retention_days' => env('TIRE_PRESSURE_RETENTION_DAYS', 30),
                    'recent_correction_days' => env('TIRE_PRESSURE_RECENT_DAYS', 7),
                ],

                // Validação de pressões
                'pressure_validation' => [
                    'min_pressure' => 10,
                    'max_pressure' => 100,
                    'motorcycle_min' => 22,
                    'motorcycle_max' => 50,
                    'car_min' => 24,
                    'car_max' => 50,
                ],

                // Schedule
                'schedule' => [
                    'enabled' => env('TIRE_PRESSURE_SCHEDULE_ENABLED', true),
                    'interval' => env('TIRE_PRESSURE_SCHEDULE_INTERVAL', 'everyThreeHours'),
                    'dry_run_in_local' => env('TIRE_PRESSURE_DRY_RUN_LOCAL', true),
                ],
            ];
        });
    }

    /**
     * Registrar schedules
     */
    protected function registerSchedules(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $config = $this->app->make('tire.pressure.correction.config');

                if ($config['schedule']['enabled']) {
                    \Log::info('TirePressureCorrectionProvider: Schedule habilitado (VehicleData)', [
                        'interval' => $config['schedule']['interval']
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
            'tire.pressure.correction.config',
            'command.tire-pressure.update-from-vehicle-data',
            'command.tire-pressure.diagnostic',
            'command.tire-pressure.schedule',
        ];
    }
}