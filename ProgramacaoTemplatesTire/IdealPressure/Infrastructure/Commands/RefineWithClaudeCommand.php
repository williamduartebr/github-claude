<?php

namespace Src\ContentGeneration\IdealPressure\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\IdealPressure\Domain\Entities\IdealPressure;
use Src\ContentGeneration\IdealPressure\Application\Services\ClaudeRefinementService;
use Carbon\Carbon;

/**
 * RefineWithClaudeCommand - FASE 3: Refinamento de linguagem e SEO via Claude API
 * 
 * Command para refinamento textual dos artigos já estruturados:
 * - RECEBE artigos JSON da Fase 2
 * - REFINA linguagem, fluidez e SEO via Claude API
 * - OTIMIZA meta tags, keywords e legibilidade
 * - MELHORA transições e naturalidade do texto
 * 
 * ⚠️ FOCO: Refinamento textual apenas, NÃO re-processamento de dados técnicos
 * 
 * USO:
 * php artisan ideal-pressure:refine-with-claude
 * php artisan ideal-pressure:refine-with-claude --limit=5 --dry-run
 * php artisan ideal-pressure:refine-with-claude --category=sedan --force
 * 
 * @author Claude Sonnet 4
 * @version 1.0
 */
class RefineWithClaudeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ideal-pressure:refine-with-claude
                            {--limit=20 : Número máximo de artigos a refinar (recomendado: 5-20)}
                            {--category= : Filtrar por categoria específica}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Forçar re-refinamento de artigos já refinados}
                            {--delay=3 : Delay entre requests (segundos) para respeitar rate limits}
                            {--test-api : Testar conexão com Claude API antes de processar}';

    /**
     * The console command description.
     */
    protected $description = 'FASE 3: Refinar linguagem e SEO dos artigos estruturados via Claude API';

    private ClaudeRefinementService $claudeService;

    public function __construct(ClaudeRefinementService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🤖 INICIANDO REFINAMENTO VIA CLAUDE API - FASE 3');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            // 1. Validar configurações e API
            $config = $this->validateAndGetConfig();

            if ($config['test_api']) {
                $this->testClaudeApiConnection();
            }

            $this->displayConfig($config);

            // 2. Buscar artigos prontos para refinamento
            $candidates = $this->getCandidateCalibrations($config);

            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhum artigo encontrado para refinamento.');
                $this->info('💡 Execute primeiro: php artisan ideal-pressure:generate-articles');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} artigo(s) para refinamento");
            $this->newLine();

            // 3. Processar refinamentos
            $results = $this->processCandidates($candidates, $config);

            // 4. Exibir estatísticas finais
            $this->displayFinalStats($results, microtime(true) - $startTime);

            Log::info('RefineWithClaudeCommand: Execução concluída', [
                'total_processed' => $results['processed'],
                'success_count' => $results['success'],
                'api_errors' => $results['api_errors'],
                'duration_seconds' => round(microtime(true) - $startTime, 2),
                'config' => $config
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro durante execução: ' . $e->getMessage());
            Log::error('RefineWithClaudeCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Validar parâmetros e configurações
     */
    private function validateAndGetConfig(): array
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $category = $this->option('category');

        // Validações
        if ($limit <= 0 || $limit > 100) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 100 (recomendado: 5-20)');
        }

        if ($delay < 1 || $delay > 30) {
            throw new \InvalidArgumentException('Delay deve estar entre 1 e 30 segundos');
        }

        $validCategories = ['sedan', 'suv', 'hatch', 'pickup', 'motorcycle', 'motorcycle_street', 'motorcycle_scooter', 'car_electric', 'truck'];
        if ($category && !in_array($category, $validCategories)) {
            throw new \InvalidArgumentException("Categoria inválida. Disponíveis: " . implode(', ', $validCategories));
        }

        return [
            'limit' => $limit,
            'category' => $category,
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'delay' => $delay,
            'test_api' => $this->option('test-api'),
        ];
    }

    /**
     * Testar conexão com Claude API
     */
    private function testClaudeApiConnection(): void
    {
        $this->info('🔍 Testando conexão com Claude API...');

        $testResult = $this->claudeService->testApiConnection();

        if ($testResult['success']) {
            $this->info('✅ Claude API: Conectado com sucesso');
        } else {
            $this->error('❌ Claude API: ' . $testResult['message']);
            throw new \Exception('Falha na conexão com Claude API. Verifique configuração.');
        }

        $this->newLine();
    }

    /**
     * Exibir configuração da execução
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️  CONFIGURAÇÃO:');
        $this->line("   • Limite: {$config['limit']} artigos (RECOMENDADO: 5-20)");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Delay entre requests: {$config['delay']}s");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN (simulação)' : '💾 PRODUÇÃO'));
        $this->line("   • Re-refinar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        if ($config['limit'] > 20) {
            $this->warn('⚠️  ATENÇÃO: Limite alto pode causar rate limits. Recomendado: 5-20');
            $this->newLine();
        }
    }

    /**
     * Buscar artigos candidatos para refinamento
     */
    private function getCandidateCalibrations(array $config)
    {
        $query = IdealPressure::query()
            ->where('enrichment_phase', IdealPressure::PHASE_ARTICLE_GENERATED)
            ->whereNotNull('generated_article');

        // Filtrar por categoria específica
        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        // Se não forçar, excluir já refinados
        if (!$config['force']) {
            $query->whereNull('article_refined');
        }

        return $query->limit($config['limit'])->get();
    }

    /**
     * Processar candidates para refinamento
     */
    private function processCandidates($candidates, array $config): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'api_errors' => 0,
            'validation_errors' => 0,
            'skipped' => 0,
            'error_details' => [],
            'api_stats' => [
                'total_requests' => 0,
                'avg_response_time' => 0,
                'rate_limit_hits' => 0
            ]
        ];

        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setMessage('Iniciando refinamento...');

        foreach ($candidates as $index => $calibration) {
            $results['processed']++;

            try {
                $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model} {$calibration->vehicle_year}";
                $progressBar->setMessage("Refinando: {$vehicleInfo}");

                // Validar artigo estruturado
                if (!$this->validateStructuredArticle($calibration)) {
                    $results['skipped']++;
                    $progressBar->advance();
                    continue;
                }

                $requestStart = microtime(true);

                // Refinar via Claude API
                $refinedArticle = null;
                if (!$config['dry_run']) {
                    $calibration->enrichment_phase = IdealPressure::PHASE_CLAUDE_PROCESSING;
                    $calibration->save();

                    $refinedArticle = $this->claudeService->refineCalibrationArticle($calibration);
                    $results['api_stats']['total_requests']++;
                } else {
                    // Simular refinamento em dry-run
                    $refinedArticle = $calibration->generated_article;
                    $refinedArticle['_dry_run_refined'] = true;
                }

                $requestTime = microtime(true) - $requestStart;
                $results['api_stats']['avg_response_time'] += $requestTime;

                if ($refinedArticle && !$config['dry_run']) {
                    // Salvar artigo refinado
                    $calibration->article_refined = $refinedArticle;
                    $calibration->enrichment_phase = IdealPressure::PHASE_CLAUDE_COMPLETED;
                    $calibration->last_processing_at = now();
                    $calibration->claude_processing_history = array_merge(
                        $calibration->claude_processing_history ?? [],
                        [[
                            'refined_at' => now()->toISOString(),
                            'model' => 'claude-3-7-sonnet-20250219',
                            'processing_time_seconds' => round($requestTime, 2),
                            'improvement_score' => $refinedArticle['refinement_metadata']['improvement_score'] ?? null,
                            'focus' => 'language_seo_refinement'
                        ]]
                    );
                    $calibration->save();
                }

                $results['success']++;

                if ($config['dry_run']) {
                    $this->line("✅ [DRY-RUN] {$vehicleInfo} - Simulação de refinamento");
                } else {
                    $improvementScore = $refinedArticle['refinement_metadata']['improvement_score'] ?? 0;
                    $this->line("✅ {$vehicleInfo} - Score: {$improvementScore}/10 - {$requestTime}s");
                }

                // Delay entre requests para respeitar rate limits
                if (!$config['dry_run'] && $index < $candidates->count() - 1) {
                    sleep($config['delay']);
                }
            } catch (\Exception $e) {
                $errorType = $this->categorizeError($e);
                $results[$errorType]++;

                $results['error_details'][] = [
                    'vehicle' => $vehicleInfo ?? 'N/A',
                    'error_type' => $errorType,
                    'error' => $e->getMessage()
                ];

                if (!$config['dry_run']) {
                    $calibration->enrichment_phase = IdealPressure::PHASE_FAILED;
                    $calibration->last_error = $e->getMessage();
                    $calibration->claude_error_count = ($calibration->claude_error_count ?? 0) + 1;
                    $calibration->save();
                }

                $this->newLine();
                $this->error("❌ [{$errorType}] " . ($vehicleInfo ?? 'veículo desconhecido') . ": {$e->getMessage()}");

                // Delay maior em caso de erro de API
                if ($errorType === 'api_errors' && !$config['dry_run']) {
                    sleep($config['delay'] * 2);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Calcular média de tempo de resposta
        if ($results['api_stats']['total_requests'] > 0) {
            $results['api_stats']['avg_response_time'] = round(
                $results['api_stats']['avg_response_time'] / $results['api_stats']['total_requests'],
                2
            );
        }

        return $results;
    }

    /**
     * Validar se artigo tem estrutura adequada para refinamento
     */
    private function validateStructuredArticle(IdealPressure $calibration): bool
    {
        if (empty($calibration->generated_article)) {
            Log::warning('RefineWithClaudeCommand: Artigo estruturado não encontrado', [
                'tire_calibration_id' => $calibration->_id
            ]);
            return false;
        }

        $article = $calibration->generated_article;
        $requiredFields = ['title', 'slug', 'seo_data', 'technical_content'];

        foreach ($requiredFields as $field) {
            if (!isset($article[$field]) || empty($article[$field])) {
                Log::warning('RefineWithClaudeCommand: Campo obrigatório ausente no artigo', [
                    'tire_calibration_id' => $calibration->_id,
                    'missing_field' => $field
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Categorizar tipo de erro
     */
    private function categorizeError(\Exception $e): string
    {
        $message = strtolower($e->getMessage());

        if (strpos($message, 'api') !== false || strpos($message, 'rate limit') !== false || strpos($message, 'timeout') !== false) {
            return 'api_errors';
        }

        if (strpos($message, 'validation') !== false || strpos($message, 'required') !== false) {
            return 'validation_errors';
        }

        return 'api_errors'; // Default para erros de API
    }

    /**
     * Exibir estatísticas finais
     */
    private function displayFinalStats(array $results, float $duration): void
    {
        $this->info('📈 ESTATÍSTICAS DE REFINAMENTO:');
        $this->newLine();

        // Estatísticas principais
        $this->line("✅ <fg=green>Refinados com sucesso:</fg=green> {$results['success']}");
        $this->line("🤖 <fg=red>Erros de API:</fg=red> {$results['api_errors']}");
        $this->line("🔍 <fg=yellow>Erros de validação:</fg=yellow> {$results['validation_errors']}");
        $this->line("⏭️  <fg=cyan>Ignorados:</fg=cyan> {$results['skipped']}");
        $this->line("📊 <fg=blue>Total processado:</fg=blue> {$results['processed']}");
        $this->newLine();

        // Performance e API
        $avgTime = $results['processed'] > 0 ? round($duration / $results['processed'], 2) : 0;
        $this->line("⏱️  <fg=cyan>Tempo total:</fg=cyan> " . round($duration, 2) . "s");
        $this->line("🔄 <fg=cyan>Tempo médio por artigo:</fg=cyan> {$avgTime}s");
        $this->line("🌐 <fg=cyan>Requests à Claude API:</fg=cyan> {$results['api_stats']['total_requests']}");
        $this->line("📡 <fg=cyan>Tempo médio de resposta:</fg=cyan> {$results['api_stats']['avg_response_time']}s");

        // Taxa de sucesso
        $successRate = $results['processed'] > 0 ? round(($results['success'] / $results['processed']) * 100, 1) : 0;
        $this->line("🎯 <fg=magenta>Taxa de sucesso:</fg=magenta> {$successRate}%");
        $this->newLine();

        // Mostrar erros se houver
        if (!empty($results['error_details'])) {
            $this->error('🚨 DETALHES DOS ERROS:');
            foreach (array_slice($results['error_details'], 0, 3) as $error) {
                $this->line("   • [{$error['error_type']}] {$error['vehicle']}: {$error['error']}");
            }

            if (count($results['error_details']) > 3) {
                $remaining = count($results['error_details']) - 3;
                $this->line("   ... e mais {$remaining} erro(s). Verifique os logs para detalhes.");
            }
            $this->newLine();
        }

        // Recommendations baseadas nos resultados
        if ($results['api_errors'] > $results['success']) {
            $this->warn('⚠️  MUITOS ERROS DE API. Sugestões:');
            $this->line('   • Verifique ANTHROPIC_API_KEY no .env');
            $this->line('   • Reduza --limit para 5-10');
            $this->line('   • Aumente --delay para 5-10 segundos');
            $this->line('   • Verifique logs para rate limits');
        }

        if ($results['success'] > 0) {
            $this->info('🎉 ARTIGOS REFINADOS COM SUCESSO!');
            $this->line('   • Linguagem e SEO otimizados via Claude');
            $this->line('   • Prontos para publicação');
            $this->line('   • Execute: php artisan ideal-pressure:stats');
            $this->newLine();
        }

        // Rate limiting awareness
        $this->info('💡 DICAS PARA PRÓXIMA EXECUÇÃO:');
        $this->line('   • Claude API tem rate limits - use --limit baixo (5-20)');
        $this->line('   • Use --delay adequado (3-10s) entre requests');
        $this->line('   • Execute em horários de menor tráfego');
        $this->line('   • Use --test-api para verificar conexão');
    }

    /**
     * Obter estatísticas do refinamento
     */
    public function getRefinementStats(): array
    {
        $readyForRefinement = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_ARTICLE_GENERATED)->count();
        $refined = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_CLAUDE_COMPLETED)->count();
        $processing = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_CLAUDE_PROCESSING)->count();
        $failed = IdealPressure::where('enrichment_phase', IdealPressure::PHASE_FAILED)
            ->whereNotNull('claude_error_count')->count();

        return [
            'ready_for_refinement' => $readyForRefinement,
            'articles_refined' => $refined,
            'currently_processing' => $processing,
            'claude_failures' => $failed,
            'command_focus' => 'language_seo_refinement',
            'phase_coverage' => 'FASE_3_CLAUDE_API',
            'success_rate' => ($refined + $failed) > 0 ? round(($refined / ($refined + $failed)) * 100, 2) : 0,
        ];
    }
}
