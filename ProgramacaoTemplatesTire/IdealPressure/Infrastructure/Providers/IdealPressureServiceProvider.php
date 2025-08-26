<?php

namespace Src\ContentGeneration\IdealPressure\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Schedule;
use Src\ContentGeneration\IdealPressure\Application\Services\TestArticleService;
use Src\ContentGeneration\IdealPressure\Application\Services\ClaudeRefinementService;

use Src\ContentGeneration\IdealPressure\Application\Services\ArticleGenerationService;
use Src\ContentGeneration\IdealPressure\Infrastructure\Commands\RefineWithClaudeCommand;
use Src\ContentGeneration\IdealPressure\Infrastructure\Commands\GenerateTestArticlesCommand;
use Src\ContentGeneration\IdealPressure\Infrastructure\Commands\IdealPressureStatsCommand;
use Src\ContentGeneration\IdealPressure\Infrastructure\Commands\GenerateArticlesPhase1Command;
use Src\ContentGeneration\IdealPressure\Infrastructure\Commands\CopyIdealPressureArticlesCommand;

/**
 * IdealPressureServiceProvider - Provider completo do módulo IdealPressure
 * 
 * Responsável por:
 * - Registrar todos os services do módulo
 * - Registrar commands do Artisan
 * - Configurar scheduling automático
 * - Definir configurações do módulo
 * - Publicar assets e migrations
 * 
 * ARQUITETURA SIMPLIFICADA (2 fases):
 * - FASE 1+2: VehicleData → JSON estruturado (ArticleGenerationService)
 * - FASE 3: JSON estruturado → JSON refinado via Claude (ClaudeRefinementService)
 * 
 * @author Claude Sonnet 4
 * @version 1.0 - Implementação simplificada sem redundâncias
 */
class IdealPressureServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     */
    public array $bindings = [
        // Services principais
        ArticleGenerationService::class => ArticleGenerationService::class,
        ClaudeRefinementService::class => ClaudeRefinementService::class,
        TestArticleService::class => TestArticleService::class,
    ];

    /**
     * All of the container singletons that should be registered.
     */
    public array $singletons = [
        // Services como singletons para performance
        'ideal-pressure.article-service' => ArticleGenerationService::class,
        'ideal-pressure.claude-service' => ClaudeRefinementService::class,
        'ideal-pressure.test-service' => TestArticleService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // 1. Registrar configurações do módulo
        $this->registerModuleConfig();

        // 2. Registrar services principais
        $this->registerServices();

        // 3. Registrar aliases para facilitar injeção
        $this->registerAliases();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 1. Registrar commands do Artisan
        $this->registerCommands();

        // 2. Configurar scheduling automático
        $this->configureScheduling();

        // 3. Publicar assets se em modo de desenvolvimento
        $this->publishAssets();

        // 4. Configurar event listeners (se necessário)
        $this->configureEventListeners();

        // 5. Registrar middleware personalizado (se necessário)
        $this->registerMiddleware();
    }

    /**
     * Registrar configurações do módulo (HARDCODED - sem arquivo externo)
     */
    private function registerModuleConfig(): void
    {
        // Configurações hardcoded para evitar problemas de path
        $defaultConfig = [
            // Configurações da Claude API
            'claude' => [
                'model' => env('IDEAL_PRESSURE_CLAUDE_MODEL', 'claude-3-7-sonnet-20250219'),
                'timeout' => (int) env('IDEAL_PRESSURE_CLAUDE_TIMEOUT', 90),
                'max_retries' => (int) env('IDEAL_PRESSURE_CLAUDE_MAX_RETRIES', 3),
                'rate_limit_delay' => (int) env('IDEAL_PRESSURE_CLAUDE_DELAY', 3),
                'max_tokens' => (int) env('IDEAL_PRESSURE_CLAUDE_MAX_TOKENS', 3000),
                'temperature' => (float) env('IDEAL_PRESSURE_CLAUDE_TEMPERATURE', 0.2),
            ],

            // Configurações de Geração de Artigos
            'article' => [
                'min_quality_score' => (int) env('IDEAL_PRESSURE_MIN_QUALITY', 70),
                'max_word_count' => (int) env('IDEAL_PRESSURE_MAX_WORDS', 3000),
                'min_word_count' => (int) env('IDEAL_PRESSURE_MIN_WORDS', 800),
                'templates_enabled' => env('IDEAL_PRESSURE_TEMPLATES_ENABLED', true),
                'valid_categories' => [
                    'sedan',
                    'suv',
                    'hatch',
                    'pickup',
                    'truck',
                    'motorcycle',
                    'motorcycle_street',
                    'motorcycle_scooter',
                    'car_electric',
                ],
                'category_templates' => [
                    'sedan' => 'tire_calibration_car',
                    'suv' => 'tire_calibration_car',
                    'hatch' => 'tire_calibration_car',
                    'pickup' => 'tire_calibration_pickup',
                    'truck' => 'tire_calibration_pickup',
                    'motorcycle' => 'tire_calibration_motorcycle',
                    'motorcycle_street' => 'tire_calibration_motorcycle',
                    'motorcycle_scooter' => 'tire_calibration_motorcycle',
                    'car_electric' => 'tire_calibration_electric',
                ],
            ],

            // Configurações de Processamento
            'processing' => [
                'batch_size' => (int) env('IDEAL_PRESSURE_BATCH_SIZE', 50),
                'max_concurrent_claude' => (int) env('IDEAL_PRESSURE_MAX_CONCURRENT', 5),
                'error_threshold' => (int) env('IDEAL_PRESSURE_ERROR_THRESHOLD', 10),
                'single_article_timeout' => (int) env('IDEAL_PRESSURE_ARTICLE_TIMEOUT', 120),
                'parallel_processing' => env('IDEAL_PRESSURE_PARALLEL', false),
                'max_memory_per_worker' => (int) env('IDEAL_PRESSURE_MAX_MEMORY', 256),
            ],

            // Configurações de Scheduling
            'scheduling' => [
                'enabled' => env('IDEAL_PRESSURE_SCHEDULING_ENABLED', false),
                'article_generation_time' => env('IDEAL_PRESSURE_SCHEDULE_ARTICLES', '02:00'),
                'claude_refinement_time' => env('IDEAL_PRESSURE_SCHEDULE_CLAUDE', '03:30'),
                'auto_generation_limit' => (int) env('IDEAL_PRESSURE_AUTO_GEN_LIMIT', 100),
                'auto_refinement_limit' => (int) env('IDEAL_PRESSURE_AUTO_REF_LIMIT', 20),
                'auto_refinement_delay' => (int) env('IDEAL_PRESSURE_AUTO_DELAY', 5),
                'allowed_environments' => ['production', 'staging'],
                'weekly_stats' => env('IDEAL_PRESSURE_WEEKLY_STATS', true),
            ],

            // Configurações de Monitoramento
            'monitoring' => [
                'detailed_logging' => env('IDEAL_PRESSURE_DETAILED_LOGS', false),
                'stats_retention' => (int) env('IDEAL_PRESSURE_STATS_RETENTION', 90),
                'alert_errors' => env('IDEAL_PRESSURE_ALERT_ERRORS', true),
                'alert_error_threshold' => (int) env('IDEAL_PRESSURE_ALERT_THRESHOLD', 15),
                'performance_metrics' => env('IDEAL_PRESSURE_PERFORMANCE_METRICS', true),
                'claude_api_tracking' => env('IDEAL_PRESSURE_CLAUDE_TRACKING', true),
                'log_channel' => env('IDEAL_PRESSURE_LOG_CHANNEL', null),
            ],

            // Configurações de Limpeza
            'cleanup' => [
                'enabled' => env('IDEAL_PRESSURE_CLEANUP_ENABLED', false),
                'retention_days' => (int) env('IDEAL_PRESSURE_RETENTION_DAYS', 90),
                'compress_old_data' => env('IDEAL_PRESSURE_COMPRESS_OLD', false),
                'max_history_records' => (int) env('IDEAL_PRESSURE_MAX_HISTORY', 10),
            ],

            // Configurações de Desenvolvimento
            'development' => [
                'enable_test_articles' => env('IDEAL_PRESSURE_TEST_ARTICLES', true),
                'default_dry_run' => env('IDEAL_PRESSURE_DEFAULT_DRY_RUN', false),
                'debug_traces' => env('IDEAL_PRESSURE_DEBUG_TRACES', false),
                'save_claude_debug' => env('IDEAL_PRESSURE_CLAUDE_DEBUG', false),
                'debug_storage_path' => env('IDEAL_PRESSURE_DEBUG_PATH', 'ideal-pressure-debug'),
            ],

            // Configurações de SEO
            'seo' => [
                'max_title_length' => (int) env('IDEAL_PRESSURE_MAX_TITLE', 65),
                'max_description_length' => (int) env('IDEAL_PRESSURE_MAX_DESC', 165),
                'max_secondary_keywords' => (int) env('IDEAL_PRESSURE_MAX_KEYWORDS', 8),
                'auto_optimize_meta' => env('IDEAL_PRESSURE_AUTO_META', true),
                'keyword_density_analysis' => env('IDEAL_PRESSURE_KEYWORD_ANALYSIS', false),
            ],

            // Configurações de Cache
            'cache' => [
                'claude_results' => env('IDEAL_PRESSURE_CACHE_CLAUDE', false),
                'article_cache_ttl' => (int) env('IDEAL_PRESSURE_CACHE_TTL', 1440),
                'cache_driver' => env('IDEAL_PRESSURE_CACHE_DRIVER', 'redis'),
                'cache_prefix' => 'tire_calibration:',
            ],
        ];

        // Registrar configuração no container
        config(['tire_calibration' => $defaultConfig]);

        // Configurações específicas para fácil acesso
        $this->app->singleton('ideal-pressure.config', function () use ($defaultConfig) {
            return [
                'claude_api' => [
                    'api_key' => config('services.anthropic.api_key'),
                    'model' => $defaultConfig['claude']['model'],
                    'timeout' => $defaultConfig['claude']['timeout'],
                    'max_retries' => $defaultConfig['claude']['max_retries'],
                    'rate_limit_delay' => $defaultConfig['claude']['rate_limit_delay'],
                ],
                'article_generation' => [
                    'min_quality_score' => $defaultConfig['article']['min_quality_score'],
                    'max_word_count' => $defaultConfig['article']['max_word_count'],
                    'templates_enabled' => $defaultConfig['article']['templates_enabled'],
                ],
                'processing' => [
                    'batch_size' => $defaultConfig['processing']['batch_size'],
                    'max_concurrent_claude_requests' => $defaultConfig['processing']['max_concurrent_claude'],
                    'error_threshold' => $defaultConfig['processing']['error_threshold'],
                ],
                'monitoring' => [
                    'enable_detailed_logging' => $defaultConfig['monitoring']['detailed_logging'],
                    'stats_retention_days' => $defaultConfig['monitoring']['stats_retention'],
                    'alert_on_high_error_rate' => $defaultConfig['monitoring']['alert_errors'],
                ]
            ];
        });
    }

    /**
     * Registrar services principais
     */
    private function registerServices(): void
    {
        // ArticleGenerationService - FASE 1+2: Mapeamento VehicleData → JSON
        $this->app->singleton(ArticleGenerationService::class, function ($app) {
            return new ArticleGenerationService();
        });

        // ClaudeRefinementService - FASE 3: Refinamento via Claude API
        $this->app->singleton(ClaudeRefinementService::class, function ($app) {
            return new ClaudeRefinementService();
        });

        // TestArticleService - Geração de artigos mock para desenvolvimento
        $this->app->singleton(TestArticleService::class, function ($app) {
            return new TestArticleService();
        });
    }

    /**
     * Registrar aliases para facilitar injeção
     */
    private function registerAliases(): void
    {
        $this->app->alias(ArticleGenerationService::class, 'ideal-pressure.article-generator');
        $this->app->alias(ClaudeRefinementService::class, 'ideal-pressure.claude-refiner');
        $this->app->alias(TestArticleService::class, 'ideal-pressure.test-generator');
    }

    /**
     * Registrar commands do Artisan
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyIdealPressureArticlesCommand::class,

                // Commands principais do workflow
                GenerateArticlesPhase1Command::class,  // FASE 1+2: VehicleData → JSON estruturado
                RefineWithClaudeCommand::class,        // FASE 3: Refinamento via Claude

                // Commands auxiliares
                GenerateTestArticlesCommand::class,    // Desenvolvimento e testes
                IdealPressureStatsCommand::class,    // Monitoramento e estatísticas
            ]);
        }
    }

    /**
     * Configurar scheduling automático
     */
    private function configureScheduling(): void
    {
        $this->app->booted(function () {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);

            // Configurações do schedule baseadas no arquivo de configuração
            $scheduleEnabled = config('tire_calibration.scheduling.enabled', false);

            if (!$scheduleEnabled) {
                return;
            }

            // FASE 1+2: Executar geração de artigos diariamente às 02:00
            $schedule->command('ideal-pressure:generate-articles --limit=100')
                ->dailyAt('02:00')
                ->environments(['production', 'staging'])
                ->onSuccess(function () {
                    \Log::info('IdealPressure: Geração automática de artigos executada com sucesso');
                })
                ->onFailure(function () {
                    \Log::error('IdealPressure: Falha na geração automática de artigos');
                });

            // FASE 3: Executar refinamento via Claude às 03:30 (após geração)
            $schedule->command('ideal-pressure:refine-with-claude --limit=20 --delay=5')
                ->dailyAt('03:30')
                ->environments(['production'])
                ->onSuccess(function () {
                    \Log::info('IdealPressure: Refinamento automático via Claude executado com sucesso');
                })
                ->onFailure(function () {
                    \Log::error('IdealPressure: Falha no refinamento automático via Claude');
                });

            // Estatísticas semanais para monitoramento
            $schedule->command('ideal-pressure:stats --export=json --output-file=weekly_stats')
                ->weekly()
                ->mondays()
                ->at('06:00')
                ->environments(['production', 'staging']);

            // Limpeza de logs antigos (se configurado)
            if (config('tire_calibration.cleanup.enabled', false)) {
                $schedule->call(function () {
                    $this->cleanupOldLogs();
                })->weekly();
            }
        });
    }

    /**
     * Publicar assets para desenvolvimento (OPCIONAL)
     */
    private function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            // Não precisamos mais publicar arquivo de configuração pois está hardcoded
            // Usuário pode criar manualmente se quiser customizar via arquivo

            // Nota: Assets de templates e mocks podem ser criados conforme necessário
            // Por enquanto, tudo funciona com configurações hardcoded
        }
    }

    /**
     * Configurar event listeners (se necessário)
     */
    private function configureEventListeners(): void
    {
        // Event listeners para monitoramento podem ser adicionados aqui
        // Exemplo: listener para falhas na Claude API

        /*
        Event::listen('ideal-pressure.claude-api-failed', function ($event) {
            Log::error('IdealPressure: Claude API failure', [
                'tire_calibration_id' => $event->tireCalibrationId,
                'error' => $event->error,
                'retry_count' => $event->retryCount
            ]);
            
            // Implementar alertas, notificações, etc.
        });
        */
    }

    /**
     * Registrar middleware personalizado (se necessário)
     */
    private function registerMiddleware(): void
    {
        // Middleware específico do módulo pode ser registrado aqui
        // Exemplo: rate limiting para Claude API, authentication, etc.
    }

    /**
     * Limpeza de logs antigos
     */
    private function cleanupOldLogs(): void
    {
        $retentionDays = config('tire_calibration.monitoring.stats_retention', 90);
        $cutoffDate = now()->subDays($retentionDays);

        try {
            // Limpar logs de processamento antigos dos registros IdealPressure
            \DB::collection('tire_calibrations')->where('updated_at', '<', $cutoffDate)->update([
                '$unset' => [
                    'processing_history' => '',
                    'claude_processing_history' => ''
                ]
            ]);

            \Log::info('IdealPressure: Limpeza de logs antigos executada', [
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoffDate->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('IdealPressure: Erro na limpeza de logs antigos', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            // Services
            ArticleGenerationService::class,
            ClaudeRefinementService::class,
            TestArticleService::class,

            // Aliases
            'ideal-pressure.article-generator',
            'ideal-pressure.claude-refiner',
            'ideal-pressure.test-generator',
            'ideal-pressure.config',

            // Commands
            CopyIdealPressureArticlesCommand::class,
            GenerateArticlesPhase1Command::class,
            RefineWithClaudeCommand::class,
            GenerateTestArticlesCommand::class,
            IdealPressureStatsCommand::class,
        ];
    }

    /**
     * Verificar saúde do módulo
     */
    public function healthCheck(): array
    {
        try {
            $articleService = $this->app->make(ArticleGenerationService::class);
            $claudeService = $this->app->make(ClaudeRefinementService::class);
            $testService = $this->app->make(TestArticleService::class);

            return [
                'module' => 'IdealPressure',
                'status' => 'healthy',
                'services' => [
                    'article_generation' => $articleService ? 'registered' : 'missing',
                    'claude_refinement' => $claudeService ? 'registered' : 'missing',
                    'test_service' => $testService ? 'registered' : 'missing',
                ],
                'commands_registered' => count($this->provides()),
                'claude_api_configured' => !empty(config('services.anthropic.api_key')),
                'scheduling_enabled' => config('tire_calibration.scheduling.enabled', false),
                'checked_at' => now()->toISOString()
            ];
        } catch (\Exception $e) {
            return [
                'module' => 'IdealPressure',
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'checked_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Configuração padrão do módulo (exemplo)
     */
    public static function getDefaultConfig(): array
    {
        return [
            // Configurações da Claude API
            'claude' => [
                'model' => 'claude-3-7-sonnet-20250219',
                'timeout' => 90,
                'max_retries' => 3,
                'rate_limit_delay' => 3,
            ],

            // Configurações de geração de artigos
            'article' => [
                'min_quality_score' => 70,
                'max_word_count' => 3000,
                'templates_enabled' => true,
            ],

            // Configurações de processamento
            'processing' => [
                'batch_size' => 50,
                'max_concurrent_claude' => 5,
                'error_threshold' => 10,
            ],

            // Configurações de scheduling
            'scheduling' => [
                'enabled' => false, // Desabilitado por padrão
                'article_generation_time' => '02:00',
                'claude_refinement_time' => '03:30',
            ],

            // Configurações de monitoramento
            'monitoring' => [
                'detailed_logging' => false,
                'stats_retention' => 90,
                'alert_errors' => true,
            ],

            // Configurações de limpeza
            'cleanup' => [
                'enabled' => false,
                'retention_days' => 90,
            ]
        ];
    }
}
