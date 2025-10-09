<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3AService;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudePhase3BService;

/**
 * RefineWithClaudeCommand - Command Unificado V4 
 * 
 * Mantém compatibilidade com scheduling existente executando:
 * 1. Fase 3A (editorial) - se necessário
 * 2. Fase 3B (técnico) - se necessário
 * 
 * Permite executar dual-phase em um único comando
 * 
 * USO:
 * php artisan tire-calibration:refine-with-claude --limit=5
 * php artisan tire-calibration:refine-with-claude --phase=3a (apenas 3A)
 * php artisan tire-calibration:refine-with-claude --phase=3b (apenas 3B)
 * 
 * @version V4 Unified Command - Dual Phase Support
 */
class RefineWithClaudeCommand extends Command
{
    protected $signature = 'tire-calibration:refine-with-claude
                            {--limit=5 : Número máximo de artigos a processar}
                            {--phase=both : Fase específica (3a, 3b, both)}
                            {--category= : Filtrar por categoria específica}
                            {--dry-run : Simular execução sem salvar}
                            {--delay=5 : Delay entre requests (segundos)}
                            {--test-api : Testar Claude API antes de processar}
                            {--force : Reprocessar artigos já refinados}';

    protected $description = 'V4: Refinar artigos com Claude (dual-phase: 3A editorial + 3B técnico)';

    private ClaudePhase3AService $claudePhase3AService;
    private ClaudePhase3BService $claudePhase3BService;

    public function __construct(
        ClaudePhase3AService $claudePhase3AService,
        ClaudePhase3BService $claudePhase3BService
    ) {
        parent::__construct();
        $this->claudePhase3AService = $claudePhase3AService;
        $this->claudePhase3BService = $claudePhase3BService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->info('🤖 CLAUDE V4 - REFINAMENTO DUAL-PHASE');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            if ($config['test_api']) {
                $this->testBothApis();
            }

            $results = ['phase_3a' => null, 'phase_3b' => null];

            // Executar fases conforme solicitado
            if ($config['phase'] === 'both' || $config['phase'] === '3a') {
                $results['phase_3a'] = $this->executePhase3A($config);
            }

            if ($config['phase'] === 'both' || $config['phase'] === '3b') {
                $results['phase_3b'] = $this->executePhase3B($config);
            }

            // Exibir resultados combinados
            $this->displayUnifiedResults($results, $config['phase'], microtime(true) - $startTime);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            Log::error('RefineWithClaudeCommand: Erro fatal V4', [
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
        $phase = $this->option('phase');
        $validPhases = ['3a', '3b', 'both'];
        
        if (!in_array($phase, $validPhases)) {
            throw new \InvalidArgumentException("Fase inválida: {$phase}. Use: 3a, 3b ou both");
        }

        $limit = (int) $this->option('limit');
        if ($limit <= 0 || $limit > 50) {
            throw new \InvalidArgumentException('Limite deve estar entre 1 e 50');
        }

        return [
            'limit' => $limit,
            'phase' => $phase,
            'category' => $this->option('category'),
            'dry_run' => $this->option('dry-run'),
            'delay' => (int) $this->option('delay'),
            'test_api' => $this->option('test-api'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Exibir configuração
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO DUAL-PHASE:');
        $this->line("   • Limite: {$config['limit']} artigos");
        $this->line("   • Fase(s): " . strtoupper($config['phase']));
        $this->line("   • Categoria: " . ($config['category'] ?? 'Todas'));
        $this->line("   • Delay: {$config['delay']}s entre requests");
        $this->line("   • Modo: " . ($config['dry_run'] ? '🔍 DRY-RUN' : '💾 PRODUÇÃO'));
        $this->newLine();

        // Explicar o que cada fase faz
        if ($config['phase'] === 'both' || $config['phase'] === '3a') {
            $this->line('📝 FASE 3A (Editorial):');
            $this->line('   • Meta description atrativa (sem PSI)');
            $this->line('   • Introdução contextualizada');
            $this->line('   • 5 FAQs específicas');
        }

        if ($config['phase'] === 'both' || $config['phase'] === '3b') {
            $this->line('🔧 FASE 3B (Técnico):');
            $this->line('   • Especificações por versão (reais)');
            $this->line('   • Tabela de carga completa');
            $this->line('   • Gera article_refined final');
        }

        $this->newLine();
    }

    /**
     * Testar ambas as APIs
     */
    private function testBothApis(): void
    {
        $this->info('🔍 Testando Claude APIs...');

        $result3A = $this->claudePhase3AService->testApiConnection();
        $result3B = $this->claudePhase3BService->testApiConnection();

        if ($result3A['success'] && $result3B['success']) {
            $this->info('✅ Ambas APIs Claude conectadas');
        } else {
            $errors = [];
            if (!$result3A['success']) $errors[] = "Fase 3A: {$result3A['message']}";
            if (!$result3B['success']) $errors[] = "Fase 3B: {$result3B['message']}";
            
            throw new \Exception('Falha nas APIs: ' . implode('; ', $errors));
        }

        $this->newLine();
    }

    /**
     * Executar Fase 3A
     */
    private function executePhase3A(array $config): array
    {
        $this->info('▶️ Executando FASE 3A...');

        // Buscar candidatos 3A
        $candidates3A = TireCalibration::readyForClaudePhase3A();
        
        if ($config['category']) {
            $candidates3A->where('main_category', $config['category']);
        }
        
        if (!$config['force']) {
            $candidates3A->whereNull('claude_phase_3a_enhancements');
        }

        $candidates = $candidates3A->limit($config['limit'])->get();

        if ($candidates->isEmpty()) {
            $this->warn('   Nenhum candidato para Fase 3A');
            return ['processed' => 0, 'success' => 0, 'errors' => 0];
        }

        $this->line("   Processando {$candidates->count()} candidato(s)...");

        $results = ['processed' => 0, 'success' => 0, 'errors' => 0, 'error_details' => []];

        foreach ($candidates as $calibration) {
            try {
                if (!$config['dry_run']) {
                    $this->claudePhase3AService->enhanceEditorialContent($calibration);
                }
                
                $results['success']++;
                $this->line("   ✅ {$calibration->vehicle_make} {$calibration->vehicle_model}");

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = "{$calibration->vehicle_make} {$calibration->vehicle_model}: {$e->getMessage()}";
                $this->line("   ❌ {$calibration->vehicle_make} {$calibration->vehicle_model}");
            }

            $results['processed']++;

            if (!$config['dry_run'] && $results['processed'] < $candidates->count()) {
                sleep($config['delay']);
            }
        }

        $this->newLine();
        return $results;
    }

    /**
     * Executar Fase 3B
     */
    private function executePhase3B(array $config): array
    {
        $this->info('▶️ Executando FASE 3B...');

        // Buscar candidatos 3B
        $candidates3B = TireCalibration::readyForClaudePhase3B();
        
        if ($config['category']) {
            $candidates3B->where('main_category', $config['category']);
        }
        
        if (!$config['force']) {
            $candidates3B->whereNull('claude_phase_3b_enhancements');
        }

        $candidates = $candidates3B->limit($config['limit'])->get();

        if ($candidates->isEmpty()) {
            $this->warn('   Nenhum candidato para Fase 3B');
            $this->line('   💡 Execute Fase 3A primeiro se necessário');
            return ['processed' => 0, 'success' => 0, 'errors' => 0];
        }

        $this->line("   Processando {$candidates->count()} candidato(s)...");

        $results = ['processed' => 0, 'success' => 0, 'errors' => 0, 'error_details' => []];

        foreach ($candidates as $calibration) {
            try {
                // Validar readiness
                $readiness = $this->claudePhase3BService->validateReadinessForPhase3B($calibration);
                if (!$readiness['can_process']) {
                    $this->line("   ⏭️ {$calibration->vehicle_make} {$calibration->vehicle_model} (não pronto)");
                    continue;
                }

                if (!$config['dry_run']) {
                    $enhancements = $this->claudePhase3BService->enhanceTechnicalSpecifications($calibration);
                    $versionsCount = count($enhancements['especificacoes_por_versao'] ?? []);
                    $this->line("   ✅ {$calibration->vehicle_make} {$calibration->vehicle_model} ({$versionsCount} versões)");
                } else {
                    $this->line("   🔍 {$calibration->vehicle_make} {$calibration->vehicle_model} (dry-run)");
                }
                
                $results['success']++;

            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_details'][] = "{$calibration->vehicle_make} {$calibration->vehicle_model}: {$e->getMessage()}";
                $this->line("   ❌ {$calibration->vehicle_make} {$calibration->vehicle_model}");
            }

            $results['processed']++;

            if (!$config['dry_run'] && $results['processed'] < $candidates->count()) {
                sleep($config['delay']);
            }
        }

        $this->newLine();
        return $results;
    }

    /**
     * Exibir resultados unificados
     */
    private function displayUnifiedResults(array $results, string $phase, float $duration): void
    {
        $this->info('📈 RESULTADOS DUAL-PHASE:');
        $this->newLine();

        if ($results['phase_3a']) {
            $r3a = $results['phase_3a'];
            $this->line("📝 <fg=blue>FASE 3A:</fg=blue> {$r3a['success']} sucessos, {$r3a['errors']} erros");
        }

        if ($results['phase_3b']) {
            $r3b = $results['phase_3b'];
            $this->line("🔧 <fg=green>FASE 3B:</fg=green> {$r3b['success']} sucessos, {$r3b['errors']} erros");
        }

        $totalSuccess = ($results['phase_3a']['success'] ?? 0) + ($results['phase_3b']['success'] ?? 0);
        $totalErrors = ($results['phase_3a']['errors'] ?? 0) + ($results['phase_3b']['errors'] ?? 0);

        $this->line("📊 <fg=cyan>TOTAL:</fg=cyan> {$totalSuccess} sucessos, {$totalErrors} erros");
        $this->line("⏱️ <fg=cyan>Tempo:</fg=cyan> " . round($duration, 2) . "s");

        // Estatísticas atuais do sistema
        $this->newLine();
        $stats = TireCalibration::getProcessingStats();
        $this->info('📊 STATUS DO SISTEMA V4:');
        $this->line("   • Prontos para 3A: {$stats['ready_for_3a']}");
        $this->line("   • Prontos para 3B: {$stats['ready_for_3b']}");
        $this->line("   • Completados: {$stats['completed_3b']}");
        $this->line("   • Dual-phase concluído: {$stats['dual_phase_completed']}");

        if ($totalSuccess > 0) {
            $this->newLine();
            $this->info('🎉 REFINAMENTO CONCLUÍDO!');
            
            if ($phase === 'both' && $results['phase_3b']['success'] > 0) {
                $this->line('   ✅ Artigos finalizados e prontos para publicação');
                $this->line('   ✅ article_refined contém JSON final');
            } elseif ($phase === '3a') {
                $this->line('   ✅ Conteúdo editorial refinado');
                $this->line('   ➡️ Execute Fase 3B: --phase=3b');
            }
        }

        $this->newLine();
        $this->info('💡 COMANDOS ESPECÍFICOS:');
        $this->line('   • Apenas 3A: php artisan tire-calibration:refine-3a');
        $this->line('   • Apenas 3B: php artisan tire-calibration:refine-3b');
        $this->line('   • Stats: php artisan tire-calibration:stats --detailed');
    }
}