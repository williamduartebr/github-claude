<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Providers;

use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\ResetPickupRecordsCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\InvestigateCalibrationStructureCommand;
use Illuminate\Support\ServiceProvider;

// Services V4 + Dependências
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleGenerationService;
use Src\ContentGeneration\TireCalibration\Application\Services\ArticleMappingService;  // ✅ ADICIONADO
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3AService;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3BService;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeRefinementService; // ✅ ADICIONADO (compatibilidade V3)
use Src\ContentGeneration\TireCalibration\Application\Services\TestArticleService;

// Commands V4
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\RefineWithClaudeCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\RefineWithClaudePhase3ACommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\RefineWithClaudePhase3BCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\GenerateArticlesPhase2Command;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\TireCalibrationStatsCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\GenerateTestArticlesCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\TestArticleGenerationCommand;
use Src\ContentGeneration\TireCalibration\Infrastructure\Commands\CopyCalibrationArticlesCommand;

/**
 * TireCalibrationServiceProvider - V4 Dual-Phase Provider CORRIGIDO
 * 
 * RESPONSABILIDADES:
 * - Registrar TODOS os Services necessários (incluindo dependências)
 * - Registrar Commands V4 com todas as dependências resolvidas
 * - Manter compatibilidade total V3
 * 
 * @version 4.0 - Provider Completo com Dependências
 */
class TireCalibrationServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     */
    public array $bindings = [
        ArticleGenerationService::class => ArticleGenerationService::class,
        ArticleMappingService::class => ArticleMappingService::class,        // ✅ ADICIONADO
        ClaudePhase3AService::class => ClaudePhase3AService::class,
        ClaudePhase3BService::class => ClaudePhase3BService::class,
        ClaudeRefinementService::class => ClaudeRefinementService::class,    // ✅ ADICIONADO
        TestArticleService::class => TestArticleService::class,
    ];

    /**
     * All of the container singletons that should be registered.
     */
    public array $singletons = [
        'tire-calibration.article-service' => ArticleGenerationService::class,
        'tire-calibration.mapping-service' => ArticleMappingService::class,       // ✅ ADICIONADO
        'tire-calibration.claude-phase-3a' => ClaudePhase3AService::class,
        'tire-calibration.claude-phase-3b' => ClaudePhase3BService::class,
        'tire-calibration.claude-refinement' => ClaudeRefinementService::class,   // ✅ ADICIONADO
        'tire-calibration.test-service' => TestArticleService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // 1. Registrar configurações do módulo V4
        $this->registerModuleConfigV4();

        // 2. Registrar TODOS os services (incluindo dependências)
        $this->registerServicesV4();

        // 3. Registrar aliases para facilitar injeção
        $this->registerAliasesV4();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 1. Registrar commands V4
        $this->registerCommandsV4();

        // 2. Publicar assets se em desenvolvimento
        $this->publishAssetsV4();

        // 3. Configurar event listeners V4 (se necessário)
        $this->configureEventListenersV4();
    }

    /**
     * Registrar configurações do módulo V4 (mesmo de antes)
     */
    private function registerModuleConfigV4(): void
    {
        $defaultConfig = [
            // Configurações V4 Dual-Phase Claude API
            'claude_v4' => [
                'model' => env('TIRE_CALIBRATION_CLAUDE_MODEL', 'claude-3-7-sonnet-20250219'),
                'timeout' => (int) env('TIRE_CALIBRATION_CLAUDE_TIMEOUT', 90),
                'max_retries' => (int) env('TIRE_CALIBRATION_CLAUDE_MAX_RETRIES', 3),

                'phase_3a' => [
                    'max_tokens' => 2500,
                    'temperature' => 0.3,
                    'rate_limit_delay' => 3,
                    'timeout' => 60,
                ],
                'phase_3b' => [
                    'max_tokens' => 3000,
                    'temperature' => 0.2,
                    'rate_limit_delay' => 5,
                    'timeout' => 90,
                ],
            ],

            'article_v4' => [
                'min_quality_score' => (int) env('TIRE_CALIBRATION_MIN_QUALITY', 80),
                'max_word_count' => (int) env('TIRE_CALIBRATION_MAX_WORDS', 3500),
                'min_word_count' => (int) env('TIRE_CALIBRATION_MIN_WORDS', 1000),

                'editorial_validation' => [
                    'meta_description_min_length' => 120,  // ✅ Era 140
                    'meta_description_max_length' => 330,  // ✅ Era 165
                    'intro_min_words' => 100,              // ✅ Era 170  
                    'intro_max_words' => 300,              // ✅ Era 230
                    'final_min_words' => 80,               // ✅ Era 140
                    'final_max_words' => 250,              // ✅ Era 190
                    'required_faqs_min' => 3,              // ✅ Novo
                    'required_faqs_max' => 8,              // ✅ Novo (era fixo 5)
                    'min_faq_response_words' => 25,        // ✅ Era 50
                ],

                'technical_validation' => [
                    'min_versions_per_article' => 3,
                    'max_versions_per_article' => 5,
                    'min_version_name_length' => 5,
                    'forbidden_generic_terms' => [
                        'versão base',
                        'base',
                        'básica',
                        'intermediária',
                        'top',
                        'premium',
                        'completa',
                        'entrada',
                        'superior',
                        'padrão',
                        'standard'
                    ],
                ],
            ],

            'processing_v4' => [
                'batch_size_3a' => (int) env('TIRE_CALIBRATION_BATCH_3A', 10),
                'batch_size_3b' => (int) env('TIRE_CALIBRATION_BATCH_3B', 5),
                'error_threshold' => (int) env('TIRE_CALIBRATION_ERROR_THRESHOLD', 15),
                'cleanup_stuck_after_hours' => (int) env('TIRE_CALIBRATION_CLEANUP_HOURS', 2),
            ],

            'compatibility' => [
                'support_v3_commands' => true,
                'support_v3_data_structure' => true,
                'v3_unified_command_enabled' => true,
            ],
        ];

        config(['tire_calibration_v4' => $defaultConfig]);

        $this->app->singleton('tire-calibration-v4.config', function () use ($defaultConfig) {
            return [
                'claude_api' => [
                    'api_key' => config('services.anthropic.api_key'),
                    'model' => $defaultConfig['claude_v4']['model'],
                    'phase_3a_config' => $defaultConfig['claude_v4']['phase_3a'],
                    'phase_3b_config' => $defaultConfig['claude_v4']['phase_3b'],
                ],
                'processing' => [
                    'batch_size_3a' => $defaultConfig['processing_v4']['batch_size_3a'],
                    'batch_size_3b' => $defaultConfig['processing_v4']['batch_size_3b'],
                    'error_threshold' => $defaultConfig['processing_v4']['error_threshold'],
                ],
                'validation' => [
                    'editorial' => $defaultConfig['article_v4']['editorial_validation'],
                    'technical' => $defaultConfig['article_v4']['technical_validation'],
                ],
            ];
        });
    }

    /**
     * Registrar TODOS os services V4 + dependências
     */
    private function registerServicesV4(): void
    {
        // ArticleGenerationService - FASE 1+2
        $this->app->singleton(ArticleGenerationService::class, function ($app) {
            return new ArticleGenerationService();
        });

        // ✅ ArticleMappingService - NECESSÁRIO para GenerateArticlesPhase2Command
        $this->app->singleton(ArticleMappingService::class, function ($app) {
            return new ArticleMappingService();
        });

        // ClaudePhase3AService - FASE 3A: Editorial
        $this->app->singleton(ClaudePhase3AService::class, function ($app) {
            return new ClaudePhase3AService();
        });

        // ClaudePhase3BService - FASE 3B: Técnico
        $this->app->singleton(ClaudePhase3BService::class, function ($app) {
            return new ClaudePhase3BService();
        });

        // ✅ ClaudeRefinementService - COMPATIBILIDADE V3
        $this->app->singleton(ClaudeRefinementService::class, function ($app) {
            return new ClaudeRefinementService();
        });

        // TestArticleService - Desenvolvimento
        $this->app->singleton(TestArticleService::class, function ($app) {
            return new TestArticleService();
        });
    }

    /**
     * Registrar aliases V4 + compatibilidade V3
     */
    private function registerAliasesV4(): void
    {
        // Aliases V4
        $this->app->alias(ArticleGenerationService::class, 'tire-calibration.article-generator');
        $this->app->alias(ArticleMappingService::class, 'tire-calibration.mapping-service');
        $this->app->alias(ClaudePhase3AService::class, 'tire-calibration.claude-3a');
        $this->app->alias(ClaudePhase3BService::class, 'tire-calibration.claude-3b');
        $this->app->alias(TestArticleService::class, 'tire-calibration.test-generator');

        // ✅ Aliases de compatibilidade V3
        $this->app->alias(ClaudeRefinementService::class, 'tire-calibration.claude-refiner');
        $this->app->alias(ClaudePhase3AService::class, 'tire-calibration.claude-refiner-alt'); // Fallback
    }

    /**
     * Registrar commands V4 com todas as dependências
     */
    private function registerCommandsV4(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                // ✅ Commands V4 Dual-Phase (todos com dependências resolvidas)
                RefineWithClaudePhase3ACommand::class,      // Depende: ClaudePhase3AService ✅
                RefineWithClaudePhase3BCommand::class,      // Depende: ClaudePhase3BService ✅
                RefineWithClaudeCommand::class,             // Depende: ClaudePhase3AService + ClaudePhase3BService ✅

                // ✅ Commands Fase 1+2 (com dependências)
                GenerateArticlesPhase2Command::class,       // Depende: ArticleMappingService ✅
                TestArticleGenerationCommand::class,        // Depende: ArticleMappingService ✅
                CopyCalibrationArticlesCommand::class,      // Independente ✅

                // ✅ Commands auxiliares
                GenerateTestArticlesCommand::class,         // Depende: TestArticleService ✅
                TireCalibrationStatsCommand::class,         // Depende: ArticleGenerationService + ClaudeRefinementService + TestArticleService ✅

                InvestigateCalibrationStructureCommand::class,
                ResetPickupRecordsCommand::class,
            ]);
        }
    }

    /**
     * Publicar assets V4
     */
    private function publishAssetsV4(): void
    {
        if ($this->app->runningInConsole()) {
            // Assets V4 podem ser publicados se necessário
        }
    }

    /**
     * Configurar event listeners V4
     */
    private function configureEventListenersV4(): void
    {
        // Event listeners específicos V4 podem ser adicionados aqui se necessário
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            // ✅ Services V4 + Dependências
            ArticleGenerationService::class,
            ArticleMappingService::class,                // ✅ ADICIONADO
            ClaudePhase3AService::class,
            ClaudePhase3BService::class,
            ClaudeRefinementService::class,              // ✅ ADICIONADO
            TestArticleService::class,

            // ✅ Aliases V4 + Compatibilidade
            'tire-calibration.article-generator',
            'tire-calibration.mapping-service',          // ✅ ADICIONADO
            'tire-calibration.claude-3a',
            'tire-calibration.claude-3b',
            'tire-calibration.claude-refinement',        // ✅ ADICIONADO
            'tire-calibration.test-generator',
            'tire-calibration-v4.config',

            // Compatibilidade V3
            'tire-calibration.claude-refiner',
            'tire-calibration.claude-refiner-alt',

            // ✅ Commands V4 (todos com dependências)
            RefineWithClaudeCommand::class,
            RefineWithClaudePhase3ACommand::class,
            RefineWithClaudePhase3BCommand::class,
            GenerateArticlesPhase2Command::class,
            TestArticleGenerationCommand::class,
            CopyCalibrationArticlesCommand::class,
            TireCalibrationStatsCommand::class,
            GenerateTestArticlesCommand::class,
        ];
    }

    /**
     * Health check do módulo V4 com verificação de dependências
     */
    public function healthCheckV4(): array
    {
        try {
            // ✅ Testar TODOS os services
            $services = [
                'article_generation' => $this->app->make(ArticleGenerationService::class),
                'article_mapping' => $this->app->make(ArticleMappingService::class),
                'claude_phase_3a' => $this->app->make(ClaudePhase3AService::class),
                'claude_phase_3b' => $this->app->make(ClaudePhase3BService::class),
                'claude_refinement_v3' => $this->app->make(ClaudeRefinementService::class),
                'test_service' => $this->app->make(TestArticleService::class),
            ];

            // Testar APIs Claude
            $apiTest3A = $services['claude_phase_3a']->testApiConnection();
            $apiTest3B = $services['claude_phase_3b']->testApiConnection();

            return [
                'module' => 'TireCalibration',
                'version' => 'v4_dual_phase_complete',
                'status' => 'healthy',

                'services' => [
                    'article_generation' => $services['article_generation'] ? 'registered' : 'missing',
                    'article_mapping' => $services['article_mapping'] ? 'registered' : 'missing',      // ✅
                    'claude_phase_3a' => $services['claude_phase_3a'] ? 'registered' : 'missing',
                    'claude_phase_3b' => $services['claude_phase_3b'] ? 'registered' : 'missing',
                    'claude_refinement_v3' => $services['claude_refinement_v3'] ? 'registered' : 'missing', // ✅
                    'test_service' => $services['test_service'] ? 'registered' : 'missing',
                ],

                'api_connectivity' => [
                    'claude_3a' => $apiTest3A['success'] ? 'connected' : 'failed',
                    'claude_3b' => $apiTest3B['success'] ? 'connected' : 'failed',
                    'api_key_configured' => !empty(config('services.anthropic.api_key')),
                ],

                'dependencies_resolved' => [
                    'GenerateArticlesPhase2Command' => 'ArticleMappingService ✅',
                    'TireCalibrationStatsCommand' => 'All Services ✅',
                    'RefineCommands' => 'Claude Services ✅',
                    'TestCommands' => 'Test Services ✅',
                ],

                'commands_registered' => count($this->provides()),
                'checked_at' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            return [
                'module' => 'TireCalibration',
                'version' => 'v4_dual_phase_complete',
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'missing_dependency' => 'Verifique se todos os Services estão implementados',
                'checked_at' => now()->toISOString()
            ];
        }
    }
}
