<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeRefinementService;

/**
 * RefineWithClaudeCommand - ATUALIZADO para foco em enhancements
 * 
 * FASE 3: Enhancements específicos via Claude API
 * - Introdução contextualizada
 * - Considerações finais personalizadas  
 * - FAQs específicas do modelo
 * - Alertas críticos por categoria
 * 
 * @author Claude Sonnet 4
 * @version 3.0 - Especializado em enhancements
 */
class RefineWithClaudeCommand extends Command
{
    protected $signature = 'tire-calibration:refine-with-claude
                            {--limit=1 : Número máximo de artigos (recomendado: 1-2)}
                            {--category= : Filtrar por categoria específica}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Reprocessar artigos já refinados}
                            {--delay=5 : Delay entre requests (segundos)}
                            {--test-api : Testar Claude API antes de processar}';

    protected $description = 'FASE 3: Enriquecer artigos com Claude API - foco em contexto e linguagem';

    private ClaudeRefinementService $claudeService;

    public function __construct(ClaudeRefinementService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🤖 CLAUDE API - FASE 3: ENHANCEMENTS CONTEXTUAIS');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            if ($config['test_api']) {
                $this->testClaudeConnection();
            }

            // Buscar candidatos para enhancement
            $candidates = $this->getCandidates($config);

            if ($candidates->isEmpty()) {
                $this->warn('Nenhum artigo encontrado para enhancement Claude');
                $this->info('💡 Execute primeiro: php artisan tire-calibration:generate-articles-phase2');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} artigo(s) para enhancement Claude");
            $this->newLine();

            // Processar enhancements
            $results = $this->processEnhancements($candidates, $config);

            // Exibir resultados
            $this->displayResults($results, microtime(true) - $startTime);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            Log::error('RefineWithClaudeCommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function getConfig(): array
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');

        if ($limit <= 0 || $limit > 50) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 50 (recomendado: 5-15)');
        }

        if ($delay < 3 || $delay > 30) {
            throw new \InvalidArgumentException('Delay deve estar entre 3 e 30 segundos');
        }

        return [
            'limit' => $limit,
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'delay' => $delay,
            'test_api' => $this->option('test-api'),
        ];
    }

    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO CLAUDE ENHANCEMENT:');
        $this->line("   • Limite: {$config['limit']} artigos (RECOMENDADO: 5-15)");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Delay: {$config['delay']}s entre requests");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN' : '💾 PRODUÇÃO'));
        $this->line("   • Reprocessar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        if ($config['limit'] > 15) {
            $this->warn('⚠️ Limite alto pode causar rate limits na Claude API');
        }
    }

    private function testClaudeConnection(): void
    {
        $this->info('🔍 Testando Claude API...');

        $result = $this->claudeService->testApiConnection();

        if ($result['success']) {
            $this->info("✅ Claude API: Conectada ({$result['model']})");
        } else {
            $this->error("❌ Claude API: {$result['message']}");
            throw new \Exception('Falha na conexão Claude API');
        }

        $this->newLine();
    }

    private function getCandidates(array $config)
    {
        $query = TireCalibration::where('enrichment_phase', TireCalibration::PHASE_ARTICLE_GENERATED)
            ->whereNotNull('generated_article');

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        if (!$config['force']) {
            $query->whereNull('claude_enhancements');
        }

        return $query->limit($config['limit'])->get();
    }

    private function processEnhancements($candidates, array $config): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'api_calls' => 0,
            'total_improvement' => 0,
            'error_details' => []
        ];

        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($candidates as $calibration) {
            $results['processed']++;

            $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model}";
            $progressBar->setMessage("Enriquecendo: {$vehicleInfo}");

            try {
                if (!$config['dry_run']) {
                    // Enhancement via Claude API
                    $enhancedArticle = $this->claudeService->enhanceWithClaude($calibration);
                    $results['api_calls']++;
                    $results['total_improvement'] += $calibration->claude_improvement_score ?? 0;
                } else {
                    // Simulação
                    $this->line("\n[DRY-RUN] {$vehicleInfo} - Enhancement simulado");
                }

                $results['success']++;

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = "{$vehicleInfo}: {$e->getMessage()}";

                Log::error('RefineWithClaudeCommand: Erro no enhancement', [
                    'calibration_id' => $calibration->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();

            // Rate limiting
            if (!$config['dry_run'] && $results['processed'] < $candidates->count()) {
                sleep($config['delay']);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    private function displayResults(array $results, float $duration): void
    {
        $this->info('📈 RESULTADOS CLAUDE ENHANCEMENT:');
        $this->newLine();

        // Estatísticas principais
        $this->line("✅ <fg=green>Enriquecidos:</fg=green> {$results['success']}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$results['errors']}");
        $this->line("📊 <fg=blue>Total processado:</fg=blue> {$results['processed']}");
        $this->line("🤖 <fg=cyan>Calls Claude API:</fg=cyan> {$results['api_calls']}");

        // Performance
        $this->line("⏱️ <fg=cyan>Tempo total:</fg=cyan> " . round($duration, 2) . "s");
        
        if ($results['success'] > 0) {
            $avgTime = round($duration / $results['success'], 2);
            $avgImprovement = round($results['total_improvement'] / $results['success'], 2);
            $this->line("📊 <fg=cyan>Média por artigo:</fg=cyan> {$avgTime}s");
            $this->line("⭐ <fg=magenta>Score médio melhoria:</fg=magenta> {$avgImprovement}/10");
        }

        $this->newLine();

        // Mostrar alguns erros
        if (!empty($results['error_details'])) {
            $this->error('🚨 ALGUNS ERROS:');
            foreach (array_slice($results['error_details'], 0, 3) as $error) {
                $this->line("   • {$error}");
            }
            $this->newLine();
        }

        if ($results['success'] > 0) {
            $this->info('🎉 ENHANCEMENTS CLAUDE CONCLUÍDOS!');
            $this->line('   • Introduções contextualizadas');
            $this->line('   • Considerações finais personalizadas');
            $this->line('   • FAQs específicas por modelo');
            $this->line('   • Linguagem enriquecida e envolvente');
            $this->newLine();
        }

        // Recomendações
        if ($results['errors'] > $results['success']) {
            $this->warn('⚠️ MUITOS ERROS. Sugestões:');
            $this->line('   • Verifique ANTHROPIC_API_KEY');
            $this->line('   • Reduza --limit para 5-10');
            $this->line('   • Aumente --delay para 8-15s');
        }

        $this->info('💡 PRÓXIMOS PASSOS:');
        $this->line('   • Verificar: php artisan tire-calibration:stats --detailed');
        $this->line('   • API tem rate limits - use limits baixos');
        $this->line('   • Artigos enriquecidos prontos para publicação');
    }
}