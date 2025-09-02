<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3BService;

/**
 * RefineWithClaudePhase3BCommand - Refinamento Técnico
 * 
 * FASE 3B: Enriquece apenas especificações técnicas:
 * - Especificações por versão com nomes reais
 * - Tabela de carga completa
 * - Gera article_refined final combinando 3A + 3B
 * 
 * REQUISITO: Fase 3A deve estar completa
 * 
 * USO:
 * php artisan tire-calibration:refine-3b --limit=5
 * php artisan tire-calibration:refine-3b --category=sedan --dry-run
 * 
 * @version V4 Phase 3B Command
 */
class RefineWithClaudePhase3BCommand extends Command
{
    protected $signature = 'tire-calibration:refine-3b
                            {--limit=10 : Número máximo de artigos a processar}
                            {--category= : Filtrar por categoria específica}
                            {--dry-run : Simular execução sem salvar}
                            {--force : Reprocessar artigos já refinados na 3B}
                            {--delay=5 : Delay entre requests (segundos)}
                            {--test-api : Testar Claude API antes de processar}
                            {--debug : Mostrar informações de debug}
                            {--cleanup : Limpar registros travados antes de processar}';

    protected $description = 'FASE 3B: Refinar especificações técnicas (versões, tabelas) e gerar article_refined final';

    private ClaudePhase3BService $claudePhase3BService;
    private int $processedCount = 0;
    private int $successCount = 0;
    private int $errorCount = 0;
    private int $skippedCount = 0;
    private array $errorDetails = [];

    public function __construct(ClaudePhase3BService $claudePhase3BService)
    {
        parent::__construct();
        $this->claudePhase3BService = $claudePhase3BService;
    }

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $startTime = microtime(true);

        $this->info('🔧 CLAUDE FASE 3B - REFINAMENTO TÉCNICO');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Cleanup se solicitado
            if ($config['cleanup']) {
                $this->performCleanup();
            }

            // Testar API se solicitado
            if ($config['test_api']) {
                $this->testClaudeConnection();
            }

            // Buscar candidatos para Fase 3B
            $candidates = $this->getCandidatesPhase3B($config);

            if ($candidates->isEmpty()) {
                $this->warn('Nenhum artigo encontrado para Fase 3B');
                $this->info('💡 Certifique-se que existem artigos com Fase 3A completa');
                $this->info('   Execute: php artisan tire-calibration:refine-3a primeiro');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} artigo(s) prontos para Fase 3B");

            // Debug do primeiro candidato
            if ($config['debug'] && $candidates->count() > 0) {
                $this->debugCandidate($candidates->first());
            }

            $this->newLine();

            // Processar Fase 3B
            $results = $this->processPhase3B($candidates, $config);

            // Exibir resultados
            $this->displayResults($results, microtime(true) - $startTime);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            Log::error('RefineWithClaudePhase3BCommand: Erro fatal', [
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

        if ($limit <= 0 || $limit > 50) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 50 para Fase 3B');
        }

        if ($delay < 3 || $delay > 60) {
            throw new \InvalidArgumentException('Delay deve estar entre 3 e 60 segundos');
        }

        return [
            'limit' => $limit,
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'delay' => $delay,
            'test_api' => $this->option('test-api'),
            'debug' => $this->option('debug'),
            'cleanup' => $this->option('cleanup'),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO FASE 3B:');
        $this->line("   • Limite: {$config['limit']} artigos");
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Delay: {$config['delay']}s entre requests");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN' : '💾 PRODUÇÃO'));
        $this->line("   • Reprocessar: " . ($config['force'] ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Cleanup: " . ($config['cleanup'] ? '✅ SIM' : '❌ NÃO'));
        $this->newLine();

        $this->info('🎯 FASE 3B PROCESSA:');
        $this->line('   • Especificações por versão (3-5 versões reais)');
        $this->line('   • Tabela de carga completa');
        $this->line('   • Gera article_refined FINAL combinado');
        $this->line('   • ❌ PROÍBE versões genéricas (Base, Premium, etc.)');
        $this->newLine();
    }

    /**
     * Executar cleanup de registros travados
     */
    private function performCleanup(): void
    {
        $this->info('🧹 Executando cleanup...');

        $cleanedCount = $this->claudePhase3BService->cleanupStuckPhase3B();

        if ($cleanedCount > 0) {
            $this->info("✅ {$cleanedCount} registro(s) limpo(s)");
        } else {
            $this->line('   Nenhum registro travado encontrado');
        }

        $this->newLine();
    }

    /**
     * Testar conexão Claude API
     */
    private function testClaudeConnection(): void
    {
        $this->info('🔍 Testando Claude API Fase 3B...');

        $result = $this->claudePhase3BService->testApiConnection();

        if ($result['success']) {
            $this->info("✅ {$result['message']}");
        } else {
            $this->error("❌ {$result['message']}");
            throw new \Exception('Falha na conexão Claude API Fase 3B');
        }

        $this->newLine();
    }

    /**
     * Buscar candidatos para Fase 3B
     */
    private function getCandidatesPhase3B(array $config)
    {
        $query = TireCalibration::readyForClaudePhase3B();

        if ($config['category']) {
            $query->where('main_category', $config['category']);
        }

        if (!$config['force']) {
            // Apenas registros que não foram processados na 3B
            $query->whereNull('claude_phase_3b_enhancements');
        }

        return $query->orderBy('phase_3a_completed_at', 'asc')
            ->limit($config['limit'])
            ->get();
    }

    /**
     * Debug do candidato
     */
    private function debugCandidate(TireCalibration $calibration): void
    {
        $this->info('🔍 DEBUG - CANDIDATO FASE 3B:');
        $this->line("   • ID: {$calibration->_id}");
        $this->line("   • Veículo: {$calibration->vehicle_make} {$calibration->vehicle_model}");
        $this->line("   • Fase atual: {$calibration->enrichment_phase}");
        $this->line("   • 3A completa: " . (!empty($calibration->claude_phase_3a_enhancements) ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • 3B completa: " . (!empty($calibration->claude_phase_3b_enhancements) ? '✅ SIM' : '❌ NÃO'));
        $this->line("   • Article refined: " . (!empty($calibration->article_refined) ? '✅ SIM' : '❌ NÃO'));

        if (!empty($calibration->claude_phase_3a_enhancements)) {
            $phase3A = $calibration->claude_phase_3a_enhancements;
            $this->line("   • Seções 3A: " . implode(', ', array_keys($phase3A)));
        }

        // Validar readiness
        $readiness = $this->claudePhase3BService->validateReadinessForPhase3B($calibration);
        $this->line("   • Pode processar 3B: " . ($readiness['can_process'] ? '✅ SIM' : '❌ NÃO'));

        if (!empty($readiness['issues'])) {
            $this->warn("   • Problemas: " . implode('; ', $readiness['issues']));
        }

        $this->newLine();
    }

    /**
     * Processar registros Fase 3B
     */
    private function processPhase3B($candidates, array $config): array
    {
        $progressBar = $this->output->createProgressBar($candidates->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        foreach ($candidates as $calibration) {
            $this->processedCount++;

            $vehicleInfo = "{$calibration->vehicle_make} {$calibration->vehicle_model}";
            $progressBar->setMessage("3B: {$vehicleInfo}");

            try {
                // Validar readiness específica
                $readiness = $this->claudePhase3BService->validateReadinessForPhase3B($calibration);

                if (!$readiness['can_process']) {
                    $this->skippedCount++;
                    $this->errorDetails[] = "{$vehicleInfo}: " . implode('; ', $readiness['issues']);
                    $progressBar->advance();
                    continue;
                }

                if (!$config['dry_run']) {
                    // Executar Fase 3B
                    $enhancements = $this->claudePhase3BService->enhanceTechnicalSpecifications($calibration);

                    $this->logSuccessfulPhase3B($calibration, $enhancements);
                } else {
                    $this->line("\n[DRY-RUN] Fase 3B simulada para: {$vehicleInfo}");
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $errorMessage = $e->getMessage();
                $this->errorDetails[] = "{$vehicleInfo}: {$errorMessage}";

                Log::error('RefineWithClaudePhase3BCommand: Erro na Fase 3B', [
                    'calibration_id' => $calibration->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $errorMessage,
                    'phase' => '3B'
                ]);
            }

            $progressBar->advance();

            // Rate limiting mais conservador para 3B
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
     * Log de sucesso da Fase 3B
     */
    private function logSuccessfulPhase3B(TireCalibration $calibration, array $enhancements): void
    {
        $versions = [];
        if (isset($enhancements['especificacoes_por_versao'])) {
            foreach ($enhancements['especificacoes_por_versao'] as $spec) {
                $versions[] = $spec['versao'] ?? 'N/A';
            }
        }

        Log::info('RefineWithClaudePhase3BCommand: Fase 3B concluída com sucesso', [
            'calibration_id' => $calibration->_id,
            'vehicle' => $calibration->vehicle_make . ' ' . $calibration->vehicle_model,
            'enhanced_sections' => array_keys($enhancements),
            'versions_generated' => $versions,
            'versions_count' => count($versions),
            'article_refined_ready' => true,
            'phase' => '3B_completed_final'
        ]);
    }

    /**
     * Exibir resultados finais
     */
    private function displayResults(array $results, float $duration): void
    {
        $this->info('📈 RESULTADOS FASE 3B:');
        $this->newLine();

        $this->line("✅ <fg=green>Processados com sucesso:</fg=green> {$results['success']}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$results['errors']}");
        $this->line("⏭️ <fg=yellow>Ignorados:</fg=yellow> {$results['skipped']}");
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
            $this->info('🎉 FASE 3B CONCLUÍDA - ARTIGOS FINALIZADOS!');
            $this->line('   • Especificações por versão com nomes reais');
            $this->line('   • Tabelas de carga específicas');
            $this->line('   • article_refined final gerado (3A + 3B)');
            $this->line('   • Zero versões genéricas');
            $this->newLine();

            $this->info('✅ PROCESSO COMPLETO!');
            $this->line('   Os artigos estão prontos para publicação');
            $this->line('   Campo: article_refined contém JSON final');
        }

        if ($results['errors'] > 0) {
            $this->newLine();
            $this->warn('⚠️ SUGESTÕES PARA ERROS:');
            $this->line('   • Execute --cleanup para limpar registros travados');
            $this->line('   • Verifique se Fase 3A foi executada primeiro');
            $this->line('   • Execute com --debug para investigar');
            $this->line('   • Aumente --delay para 8-15s');
        }

        if ($results['skipped'] > 0) {
            $this->newLine();
            $this->warn("⏭️ {$results['skipped']} REGISTRO(S) IGNORADO(S):");
            $this->line('   • Certifique-se que Fase 3A foi executada');
            $this->line('   • Execute: php artisan tire-calibration:refine-3a');
        }

        $this->newLine();
        $this->info('💡 COMANDOS ÚTEIS:');
        $this->line('   • Stats: php artisan tire-calibration:stats --detailed');
        $this->line('   • Debug: php artisan tire-calibration:refine-3b --limit=1 --debug');
        $this->line('   • Cleanup: php artisan tire-calibration:refine-3b --cleanup --dry-run');
        $this->line('   • Quality: Verificar especificações geradas no article_refined');
    }
}
