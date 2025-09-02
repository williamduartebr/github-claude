<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3AService;

/**
 * RefineWithClaudePhase3ACommand - Refinamento Editorial
 * 
 * FASE 3A: Enriquece apenas conteúdo editorial:
 * - Meta description atrativa (sem pressões PSI)
 * - Introdução contextualizada
 * - Considerações finais personalizadas  
 * - Perguntas frequentes específicas
 * 
 * USO:
 * php artisan tire-calibration:refine-3a --limit=5
 * php artisan tire-calibration:refine-3a --category=sedan --dry-run
 * 
 * @version V4 Phase 3A Command
 */
class RefineWithClaudePhase3ACommand extends Command
{
    protected $signature = 'tire-calibration:refine-3a
                            {--limit=10 : Número máximo de artigos a processar}
                            {--category= : Filtrar por categoria específica}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Reprocessar artigos já refinados na 3A}
                            {--delay=3 : Delay entre requests (segundos)}
                            {--test-api : Testar Claude API antes de processar}
                            {--debug : Mostrar informações de debug}';

    protected $description = 'FASE 3A: Refinar conteúdo editorial (introdução, FAQs, meta_description)';

    private ClaudePhase3AService $claudePhase3AService;
    private int $processedCount = 0;
    private int $successCount = 0;
    private int $errorCount = 0;
    private int $skippedCount = 0;
    private array $errorDetails = [];

    public function __construct(ClaudePhase3AService $claudePhase3AService)
    {
        parent::__construct();
        $this->claudePhase3AService = $claudePhase3AService;
    }

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $startTime = microtime(true);

        $this->info('📝 CLAUDE FASE 3A - REFINAMENTO EDITORIAL');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Testar API se solicitado
            if ($config['test_api']) {
                $this->testClaudeConnection();
            }

            // Buscar candidatos para Fase 3A
            $candidates = $this->getCandidatesPhase3A($config);

            if ($candidates->isEmpty()) {
                $this->warn('Nenhum artigo encontrado para Fase 3A');
                $this->info('💡 Certifique-se que existem artigos com enrichment_phase = "article_generated"');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} artigo(s) para Fase 3A");

            // Debug do primeiro candidato
            if ($config['debug'] && $candidates->count() > 0) {
                $this->debugCandidate($candidates->first());
            }

            $this->newLine();

            // Processar Fase 3A
            $results = $this->processPhase3A($candidates, $config);

            // Exibir resultados
            $this->displayResults($results, microtime(true) - $startTime);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            Log::error('RefineWithClaudePhase3ACommand: Erro fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do command
     */
    private function getConfig(): array
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');

        if ($limit <= 0 || $limit > 100) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 100 para Fase 3A');
        }

        if ($delay < 2 || $delay > 30) {
            throw new \InvalidArgumentException('Delay deve estar entre 2 e 30 segundos');
        }

        return [
            'limit' => $limit,
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'delay' => $delay,
            'test_api' => $this->option('test-api'),
            'debug' => $this->option('debug'),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO FASE 3A:');
        $this->line("   • Limite: {$config['limit']} artigos");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Delay: {$config['delay']}s entre requests");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN' : '💾 PRODUÇÃO'));
        $this->line("   • Reprocessar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Debug: " . ($config['debug'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        $this->info('🎯 FASE 3A PROCESSA:');
        $this->line('   • Meta description atrativa (SEM pressões PSI)');
        $this->line('   • Introdução contextualizada (180-220 palavras)');
        $this->line('   • Considerações finais (150-180 palavras)');
        $this->line('   • 5 perguntas frequentes específicas');
        $this->newLine();
    }

    /**
     * Testar conexão Claude API
     */
    private function testClaudeConnection(): void
    {
        $this->info('🔍 Testando Claude API Fase 3A...');

        $result = $this->claudePhase3AService->testApiConnection();

        if ($result['success']) {
            $this->info("✅ {$result['message']}");
        } else {
            $this->error("❌ {$result['message']}");
            throw new \Exception('Falha na conexão Claude API Fase 3A');
        }

        $this->newLine();
    }

    /**
     * Buscar candidatos para Fase 3A
     */
    private function getCandidatesPhase3A(array $config)
    {
        $query = TireCalibration::readyForClaudePhase3A();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        if (!$config['force']) {
            // Apenas registros que não foram processados na 3A
            $query->whereNull('claude_phase_3a_enhancements');
        }

        return $query->orderBy('updated_at', 'asc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Debug do candidato
     */
    private function debugCandidate(TireCalibration $calibration): void
    {
        $this->info('🔍 DEBUG - CANDIDATO FASE 3A:');
        $this->line("   • ID: {$calibration->_id}");
        $this->line("   • Veículo: {$calibration->vehicle_make} {$calibration->vehicle_model}");
        $this->line("   • Fase atual: {$calibration->enrichment_phase}");
        $this->line("   • Tem generated_article: " . (!empty($calibration->generated_article) ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Já processou 3A: " . (!empty($calibration->claude_phase_3a_enhancements) ? '✅ SIM' : '❌ NÃO'));

        // Verificar estrutura do generated_article
        if (!empty($calibration->generated_article)) {
            $baseArticle = is_string($calibration->generated_article) ?
                json_decode($calibration->generated_article, true) :
                $calibration->generated_article;

            if ($baseArticle) {
                $this->line("   • Artigo base válido: ✅ SIM");
                $this->line("   • Seções: " . implode(', ', array_keys($baseArticle['content'] ?? [])));
            } else {
                $this->warn("   • Artigo base inválido: ❌");
            }
        }

        $this->newLine();
    }

    /**
     * Processar registros Fase 3A
     */
    private function processPhase3A($candidates, array $config): array
    {
        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($candidates as $calibration) {
            $this->processedCount++;

            $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model}";
            $progressBar->setMessage("3A: {$vehicleInfo}");

            try {
                if (!$config['dry_run']) {
                    // Executar Fase 3A
                    $enhancements = $this->claudePhase3AService->enhanceEditorialContent($calibration);

                    $this->logSuccessfulPhase3A($calibration, $enhancements);
                } else {
                    $this->line("\n[DRY-RUN] Fase 3A simulada para: {$vehicleInfo}");
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $errorMessage = $e->getMessage();
                $this->errorDetails[] = "{$vehicleInfo}: {$errorMessage}";

                Log::error('RefineWithClaudePhase3ACommand: Erro na Fase 3A', [
                    'calibration_id' => $calibration->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $errorMessage,
                    'phase' => '3A'
                ]);
            }

            $progressBar->advance();

            // Rate limiting
            if (!$config['dry_run'] && $this->processedCount < $candidates->count()) {
                sleep($config['delay']);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        return [
            'processed' => $this->processedCount,
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'skipped' => $this->skippedCount,
            'error_details' => $this->errorDetails
        ];
    }

    /**
     * Log de sucesso da Fase 3A
     */
    private function logSuccessfulPhase3A(TireCalibration $calibration, array $enhancements): void
    {
        Log::info('RefineWithClaudePhase3ACommand: Fase 3A concluída com sucesso', [
            'calibration_id' => $calibration->_id,
            'vehicle' => $calibration->vehicle_make . ' ' . $calibration->vehicle_model,
            'enhanced_sections' => array_keys($enhancements),
            'meta_description_length' => strlen($enhancements['meta_description'] ?? ''),
            'intro_word_count' => str_word_count($enhancements['introducao'] ?? ''),
            'faqs_count' => count($enhancements['perguntas_frequentes'] ?? []),
            'phase' => '3A_completed'
        ]);
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(array $results, float $duration): void
    {
        $this->info('📈 RESULTADOS FASE 3A:');
        $this->newLine();

        $this->line("✅ <fg=green>Processados com sucesso:</fg=green> {$results['success']}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$results['errors']}");
        $this->line("📊 <fg=blue>Total processado:</fg=blue> {$results['processed']}");
        $this->line("⏱️ <fg=cyan>Tempo total:</fg=cyan> " . round($duration, 2) . "s");

        if ($results['success'] > 0) {
            $avgTime = round($duration / $results['success'], 2);
            $this->line("📊 <fg=cyan>Média por artigo:</fg=cyan> {$avgTime}s");
        }

        $this->newLine();

        // Mostrar alguns erros se houver
        if (!empty($results['error_details'])) {
            $this->error('🚨 ERROS ENCONTRADOS:');
            foreach (array_slice($results['error_details'], 0, 3) as $error) {
                $this->line("   • {$error}");
            }

            if (count($results['error_details']) > 3) {
                $remaining = count($results['error_details']) - 3;
                $this->line("   ... e mais {$remaining} erro(s)");
            }
            $this->newLine();
        }

        if ($results['success'] > 0) {
            $this->info('🎉 FASE 3A CONCLUÍDA!');
            $this->line('   • Meta descriptions otimizadas sem pressões PSI');
            $this->line('   • Introduções contextualizadas para mercado brasileiro');
            $this->line('   • FAQs específicas por modelo');
            $this->line('   • Considerações finais personalizadas');
            $this->newLine();

            $this->info('➡️ PRÓXIMO PASSO:');
            $this->line('   php artisan tire-calibration:refine-3b --limit=10');
        }

        if ($results['errors'] > 0) {
            $this->newLine();
            $this->warn('⚠️ SUGESTÕES PARA ERROS:');
            $this->line('   • Verifique ANTHROPIC_API_KEY');
            $this->line('   • Execute com --debug para investigar');
            $this->line('   • Reduza --limit para 3-5');
            $this->line('   • Aumente --delay para 5-10s');
        }

        $this->newLine();
        $this->info('💡 COMANDOS ÚTEIS:');
        $this->line('   • Stats: php artisan tire-calibration:stats');
        $this->line('   • Debug: php artisan tire-calibration:refine-3a --limit=1 --debug');
        $this->line('   • Test API: php artisan tire-calibration:refine-3a --test-api --dry-run');
    }
}
