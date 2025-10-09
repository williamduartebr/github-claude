<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ContentGeneration\TireCalibration\Application\Services\ClaudeApiService;

/**
 * ClaudeEscalationCommand - Comando Manual para Escalação
 * 
 * COMANDO SIMPLES QUE EXECUTA A ESTRATÉGIA DE ESCALAÇÃO:
 * 1. Executa Standard primeiro (mais barato)
 * 2. Escalona para Intermediate (falhas do standard)
 * 3. Escalona para Premium (casos críticos)
 * 
 * DIFERENÇA DO SCHEDULER:
 * - Scheduler: execução automática contínua
 * - Command: execução manual pontual com relatórios
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Manual Escalation Orchestrator
 */
class ClaudeEscalationCommand extends Command
{
    protected $signature = 'temp-article:escalation-run
                            {--max-total=15 : Máximo total de artigos}
                            {--max-cost=35 : Limite máximo de custo}
                            {--dry-run : Simulação completa}
                            {--priority=high : Prioridade (high|medium|low|all)}
                            {--skip-standard : Pular modelo padrão}
                            {--skip-intermediate : Pular modelo intermediário}
                            {--only-premium : Executar apenas modelo premium}
                            {--cost-analysis : Apenas análise de custos}
                            {--debug : Debug detalhado}';

    protected $description = 'Executar escalação manual inteligente: Standard → Intermediate → Premium';

    private ClaudeApiService $claudeService;
    
    // Estatísticas da execução
    private array $executionStats = [
        'total_processed' => 0,
        'total_successes' => 0,
        'total_cost' => 0.0,
        'models_used' => ['standard' => 0, 'intermediate' => 0, 'premium' => 0],
        'phase_results' => [],
        'execution_time' => 0
    ];

    public function __construct(ClaudeApiService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);
        
        $this->displayHeader();

        if ($this->option('cost-analysis')) {
            return $this->performCostAnalysisOnly();
        }

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            return self::FAILURE;
        }

        try {
            $config = $this->getConfiguration();
            $this->displayConfiguration($config);

            // Análise inicial
            $initialAnalysis = $this->performInitialAnalysis();
            $this->displayInitialAnalysis($initialAnalysis);

            if ($initialAnalysis['total_pending'] === 0) {
                $this->info('✅ Não há artigos pendentes para processar!');
                return self::SUCCESS;
            }

            // Calcular distribuição
            $distribution = $this->calculateDistribution($initialAnalysis, $config);
            $this->displayDistributionPlan($distribution);

            if (!$config['dry_run'] && !$this->confirmExecution($distribution)) {
                $this->info('⏹️ Execução cancelada pelo usuário.');
                return self::SUCCESS;
            }

            // Executar escalação
            $this->executeEscalationSequence($distribution, $config);

            // Finalizar
            $this->executionStats['execution_time'] = round(microtime(true) - $startTime, 2);
            $this->displayFinalResults();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("💥 Erro durante escalação manual: " . $e->getMessage());
            Log::error('ClaudeEscalationCommand failed', [
                'error' => $e->getMessage(),
                'stats' => $this->executionStats
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Análise inicial dos dados
     */
    private function performInitialAnalysis(): array
    {
        $totalPending = TempArticle::where('has_generic_versions', true)
                                  ->where(function ($q) {
                                      $q->where('has_specific_versions', '!=', true)
                                        ->orWhereNull('has_specific_versions');
                                  })
                                  ->count();

        $correctedByModel = [
            'standard' => TempArticle::where('corrected_by', 'claude_standard_v1')
                                    ->where('has_specific_versions', true)
                                    ->count(),
            'intermediate' => TempArticle::where('corrected_by', 'claude_intermediate_v1')
                                        ->where('has_specific_versions', true)
                                        ->count(),
            'premium' => TempArticle::where('corrected_by', 'claude_premium_v1')
                                   ->where('has_specific_versions', true)
                                   ->count()
        ];

        $failedByModel = [
            'standard' => TempArticle::where('corrected_by', 'claude_standard_v1')
                                    ->where('has_specific_versions', '!=', true)
                                    ->count(),
            'intermediate' => TempArticle::where('corrected_by', 'claude_intermediate_v1')
                                        ->where('has_specific_versions', '!=', true)
                                        ->count()
        ];

        return [
            'total_pending' => $totalPending,
            'corrected_by_model' => $correctedByModel,
            'failed_by_model' => $failedByModel,
            'success_rates' => [
                'standard' => $this->calculateSuccessRate($correctedByModel['standard'], $failedByModel['standard']),
                'intermediate' => $this->calculateSuccessRate($correctedByModel['intermediate'], $failedByModel['intermediate']),
                'premium' => 95.0 // Assumir alta taxa para premium
            ]
        ];
    }

    /**
     * Calcular distribuição inteligente
     */
    private function calculateDistribution(array $analysis, array $config): array
    {
        $maxTotal = min($config['max_total'], $analysis['total_pending']);
        
        // Estratégia baseada em performance histórica
        $standardSuccess = $analysis['success_rates']['standard'];
        
        if ($config['only_premium']) {
            return [
                'standard' => 0,
                'intermediate' => 0,
                'premium' => min($maxTotal, 3), // Máximo 3 premium por execução manual
                'estimated_cost' => min($maxTotal, 3) * 4.8
            ];
        }

        // Distribuição baseada em sucesso do standard
        if ($standardSuccess >= 80) {
            $standardCount = (int) ($maxTotal * 0.8);
            $intermediateCount = (int) ($maxTotal * 0.15);
            $premiumCount = (int) ($maxTotal * 0.05);
        } elseif ($standardSuccess >= 60) {
            $standardCount = (int) ($maxTotal * 0.6);
            $intermediateCount = (int) ($maxTotal * 0.3);
            $premiumCount = (int) ($maxTotal * 0.1);
        } else {
            $standardCount = (int) ($maxTotal * 0.4);
            $intermediateCount = (int) ($maxTotal * 0.4);
            $premiumCount = (int) ($maxTotal * 0.2);
        }

        // Aplicar configurações de skip
        if ($config['skip_standard']) $standardCount = 0;
        if ($config['skip_intermediate']) $intermediateCount = 0;

        // Ajustar para não ultrapassar limite de custo
        $estimatedCost = ($standardCount * 1.0) + ($intermediateCount * 2.3) + ($premiumCount * 4.8);
        
        while ($estimatedCost > $config['max_cost'] && $premiumCount > 0) {
            $premiumCount--;
            $estimatedCost = ($standardCount * 1.0) + ($intermediateCount * 2.3) + ($premiumCount * 4.8);
        }
        
        while ($estimatedCost > $config['max_cost'] && $intermediateCount > 0) {
            $intermediateCount--;
            $estimatedCost = ($standardCount * 1.0) + ($intermediateCount * 2.3) + ($premiumCount * 4.8);
        }

        return [
            'standard' => $standardCount,
            'intermediate' => $intermediateCount,
            'premium' => $premiumCount,
            'estimated_cost' => round($estimatedCost, 2),
            'strategy' => $standardSuccess >= 80 ? 'aggressive_standard' : ($standardSuccess >= 60 ? 'balanced' : 'conservative')
        ];
    }

    /**
     * Executar sequência de escalação
     */
    private function executeEscalationSequence(array $distribution, array $config): void
    {
        $this->info('🚀 INICIANDO SEQUÊNCIA DE ESCALAÇÃO MANUAL');
        $this->newLine();

        // Fase 1: Standard
        if ($distribution['standard'] > 0) {
            $this->executePhase('standard', $distribution['standard'], $config);
        }

        // Fase 2: Intermediate  
        if ($distribution['intermediate'] > 0) {
            $this->executePhase('intermediate', $distribution['intermediate'], $config);
        }

        // Fase 3: Premium
        if ($distribution['premium'] > 0) {
            $this->executePhase('premium', $distribution['premium'], $config);
        }
    }

    /**
     * Executar uma fase específica
     */
    private function executePhase(string $phase, int $count, array $config): void
    {
        $commands = [
            'standard' => 'temp-article:correct-standard',
            'intermediate' => 'temp-article:correct-intermediate',
            'premium' => 'temp-article:correct-premium'
        ];

        $delays = [
            'standard' => 3,
            'intermediate' => 5,
            'premium' => 8
        ];

        $batchSizes = [
            'standard' => 5,
            'intermediate' => 3,
            'premium' => 1
        ];

        $this->info("🔄 EXECUTANDO FASE: " . strtoupper($phase));
        $this->line("   📊 Artigos: {$count}");

        if ($config['dry_run']) {
            $this->line("   🧪 [DRY RUN] Simulando fase {$phase}...");
            $this->executionStats['phase_results'][$phase] = [
                'processed' => $count,
                'successes' => (int) round($count * 0.85),
                'simulated' => true
            ];
            return;
        }

        $commandOptions = [
            '--limit' => $count,
            '--batch-size' => $batchSizes[$phase],
            '--delay' => $delays[$phase],
            '--priority' => $config['priority']
        ];

        // Opções específicas por fase
        if ($phase === 'intermediate') {
            $commandOptions['--only-failed-standard'] = true;
        } elseif ($phase === 'premium') {
            $commandOptions['--only-critical'] = true;
        }

        $startTime = microtime(true);
        $exitCode = Artisan::call($commands[$phase], $commandOptions);
        $processingTime = round(microtime(true) - $startTime, 2);

        // Analisar resultado
        $result = $this->analyzePhaseResult($phase, $count, $exitCode, $processingTime);
        
        $this->line("   ✅ Sucessos: {$result['successes']}");
        $this->line("   ❌ Falhas: {$result['failures']}");
        $this->line("   📈 Taxa: {$result['success_rate']}%");
        $this->line("   💰 Custo: {$result['cost']} unidades");
        $this->line("   ⏱️ Tempo: {$processingTime}s");
        
        $this->executionStats['phase_results'][$phase] = $result;
        $this->updateGlobalStats($result);
        
        $this->newLine();
    }

    /**
     * Analisar resultado de uma fase
     */
    private function analyzePhaseResult(string $phase, int $expected, int $exitCode, float $time): array
    {
        $modelMap = [
            'standard' => 'claude_standard_v1',
            'intermediate' => 'claude_intermediate_v1',
            'premium' => 'claude_premium_v1'
        ];

        $costMap = [
            'standard' => 1.0,
            'intermediate' => 2.3,
            'premium' => 4.8
        ];

        // Contar sucessos recentes deste modelo
        $successes = TempArticle::where('corrected_by', $modelMap[$phase])
                               ->where('has_specific_versions', true)
                               ->where('version_corrected_at', '>=', now()->subMinutes(5))
                               ->count();

        $failures = max(0, $expected - $successes);
        $successRate = $expected > 0 ? round(($successes / $expected) * 100, 1) : 0;
        $cost = $expected * $costMap[$phase];

        return [
            'processed' => $expected,
            'successes' => $successes,
            'failures' => $failures,
            'success_rate' => $successRate,
            'cost' => $cost,
            'processing_time' => $time,
            'exit_code' => $exitCode,
            'simulated' => false
        ];
    }

    /**
     * Atualizar estatísticas globais
     */
    private function updateGlobalStats(array $phaseResult): void
    {
        $this->executionStats['total_processed'] += $phaseResult['processed'];
        $this->executionStats['total_successes'] += $phaseResult['successes'];
        $this->executionStats['total_cost'] += $phaseResult['cost'];
    }

    /**
     * Calcular taxa de sucesso
     */
    private function calculateSuccessRate(int $successes, int $failures): float
    {
        $total = $successes + $failures;
        return $total > 0 ? round(($successes / $total) * 100, 1) : 0;
    }

    /**
     * Análise de custos apenas
     */
    private function performCostAnalysisOnly(): int
    {
        $this->info('💰 ANÁLISE DE CUSTOS - ESCALAÇÃO MANUAL');
        $this->newLine();

        $analysis = $this->performInitialAnalysis();
        $this->displayInitialAnalysis($analysis);

        $scenarios = [
            'conservador' => ['max_total' => 5, 'max_cost' => 15],
            'balanceado' => ['max_total' => 15, 'max_cost' => 35],
            'agressivo' => ['max_total' => 30, 'max_cost' => 70]
        ];

        $this->info('📊 CENÁRIOS DE CUSTO:');
        foreach ($scenarios as $name => $params) {
            $distribution = $this->calculateDistribution($analysis, $params);
            
            $this->line("🎯 Cenário {$name}:");
            $this->line("   Standard: {$distribution['standard']} artigos (custo: " . ($distribution['standard'] * 1.0) . ")");
            $this->line("   Intermediate: {$distribution['intermediate']} artigos (custo: " . ($distribution['intermediate'] * 2.3) . ")");
            $this->line("   Premium: {$distribution['premium']} artigos (custo: " . ($distribution['premium'] * 4.8) . ")");
            $this->line("   TOTAL: {$distribution['estimated_cost']} unidades");
            $this->line("   Estratégia: {$distribution['strategy']}");
            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * Confirmar execução
     */
    private function confirmExecution(array $distribution): bool
    {
        $total = $distribution['standard'] + $distribution['intermediate'] + $distribution['premium'];
        
        $this->warn('⚠️ CONFIRMAÇÃO DE EXECUÇÃO MANUAL:');
        $this->line("Total artigos: {$total}");
        $this->line("Custo estimado: {$distribution['estimated_cost']} unidades");
        $this->line("Estratégia: {$distribution['strategy']}");
        $this->newLine();

        return $this->confirm('Continuar com a escalação manual?');
    }

    /**
     * Obter configuração
     */
    private function getConfiguration(): array
    {
        return [
            'max_total' => (int) $this->option('max-total'),
            'max_cost' => (float) $this->option('max-cost'),
            'dry_run' => $this->option('dry-run'),
            'priority' => $this->option('priority'),
            'skip_standard' => $this->option('skip-standard'),
            'skip_intermediate' => $this->option('skip-intermediate'),
            'only_premium' => $this->option('only-premium'),
            'debug' => $this->option('debug')
        ];
    }

    /**
     * Exibir cabeçalho
     */
    private function displayHeader(): void
    {
        $this->info('🚀 ESCALAÇÃO MANUAL CLAUDE - EXECUÇÃO PONTUAL');
        $this->info('🎯 Estratégia: Standard → Intermediate → Premium');
        $this->info('💰 Foco: Execução controlada com relatórios detalhados');
        $this->info('📅 ' . now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }

    /**
     * Exibir configuração
     */
    private function displayConfiguration(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO DA EXECUÇÃO MANUAL:');
        $this->line('   📊 Máximo artigos: ' . $config['max_total']);
        $this->line('   💰 Limite custo: ' . $config['max_cost'] . ' unidades');
        $this->line('   🎯 Prioridade: ' . $config['priority']);
        $this->line('   🔄 Modo: ' . ($config['dry_run'] ? '🧪 SIMULAÇÃO' : '💾 EXECUÇÃO'));
        $this->line('   ⏭️ Pular Standard: ' . ($config['skip_standard'] ? 'SIM' : 'NÃO'));
        $this->line('   ⏭️ Pular Intermediate: ' . ($config['skip_intermediate'] ? 'SIM' : 'NÃO'));
        $this->line('   💎 Apenas Premium: ' . ($config['only_premium'] ? 'SIM' : 'NÃO'));
        $this->line('   🐛 Debug: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();
    }

    /**
     * Exibir análise inicial
     */
    private function displayInitialAnalysis(array $analysis): void
    {
        $this->info('📊 ANÁLISE INICIAL:');
        $this->line("Artigos pendentes: {$analysis['total_pending']}");
        $this->newLine();

        $this->info('📈 PERFORMANCE HISTÓRICA:');
        foreach ($analysis['success_rates'] as $model => $rate) {
            $corrections = $analysis['corrected_by_model'][$model];
            $failures = $analysis['failed_by_model'][$model] ?? 0;
            $emoji = $rate >= 80 ? '🟢' : ($rate >= 60 ? '🟡' : '🔴');
            
            $this->line("{$emoji} {$model}: {$rate}% sucesso ({$corrections} sucessos, {$failures} falhas)");
        }
        $this->newLine();
    }

    /**
     * Exibir plano de distribuição
     */
    private function displayDistributionPlan(array $distribution): void
    {
        $this->info('🎯 PLANO DE DISTRIBUIÇÃO:');
        $this->line("Standard: {$distribution['standard']} artigos (custo: " . ($distribution['standard'] * 1.0) . " unidades)");
        $this->line("Intermediate: {$distribution['intermediate']} artigos (custo: " . ($distribution['intermediate'] * 2.3) . " unidades)");
        $this->line("Premium: {$distribution['premium']} artigos (custo: " . ($distribution['premium'] * 4.8) . " unidades)");
        $this->line("TOTAL: {$distribution['estimated_cost']} unidades");
        $this->line("Estratégia: {$distribution['strategy']}");
        $this->newLine();
    }

    /**
     * Exibir resultados finais
     */
    private function displayFinalResults(): void
    {
        $this->newLine();
        $this->info('🏆 RESULTADOS DA ESCALAÇÃO MANUAL');
        $this->newLine();

        $overallSuccessRate = $this->executionStats['total_processed'] > 0 ?
            round(($this->executionStats['total_successes'] / $this->executionStats['total_processed']) * 100, 1) : 0;

        $costEfficiency = $this->executionStats['total_successes'] > 0 ?
            round($this->executionStats['total_cost'] / $this->executionStats['total_successes'], 2) : 0;

        $this->line("📊 Total processado: {$this->executionStats['total_processed']}");
        $this->line("✅ Total sucessos: {$this->executionStats['total_successes']}");
        $this->line("📈 Taxa sucesso geral: {$overallSuccessRate}%");
        $this->line("💰 Custo total: {$this->executionStats['total_cost']} unidades");
        $this->line("⚡ Custo por sucesso: {$costEfficiency} unidades");
        $this->line("⏱️ Tempo total: {$this->executionStats['execution_time']}s");
        $this->newLine();

        // Resultados por fase
        if (!empty($this->executionStats['phase_results'])) {
            $this->info('📋 RESULTADOS POR FASE:');
            foreach ($this->executionStats['phase_results'] as $phase => $result) {
                $status = $result['simulated'] ? '[SIMULADO]' : '[REAL]';
                $this->line("{$phase} {$status}: {$result['success_rate']}% sucesso, {$result['cost']} unidades");
            }
            $this->newLine();
        }

        // Recomendações finais
        $this->displayFinalRecommendations($overallSuccessRate);
    }

    /**
     * Exibir recomendações finais
     */
    private function displayFinalRecommendations(float $successRate): void
    {
        $this->info('💡 RECOMENDAÇÕES:');

        if ($successRate >= 90) {
            $this->line('🎉 EXCELENTE! Escalação manual muito eficaz.');
            $this->line('✅ Estratégia funcionando perfeitamente.');
            $this->line('🔄 Pode executar novamente: php artisan temp-article:escalation-run');
        } elseif ($successRate >= 75) {
            $this->line('👍 BOA performance da escalação manual.');
            $this->line('🔧 Pequenos ajustes podem melhorar ainda mais.');
            $this->line('📊 Continue monitorando performance dos modelos.');
        } elseif ($successRate >= 60) {
            $this->line('⚠️ Performance MODERADA - investigar problemas.');
            $this->line('🔍 Revisar qualidade dos dados de entrada.');
            $this->line('🛠️ Considerar ajustar prompts e validações.');
        } else {
            $this->line('🚨 Performance BAIXA - ação imediata necessária!');
            $this->line('🛑 NÃO execute novamente até resolver problemas.');
            $this->line('🔧 Revisar configurações e dados de entrada.');
        }

        $this->newLine();
        $this->info('📝 PRÓXIMAS AÇÕES:');

        $remainingPending = TempArticle::where('has_generic_versions', true)
                                      ->where(function ($q) {
                                          $q->where('has_specific_versions', '!=', true)
                                            ->orWhereNull('has_specific_versions');
                                      })
                                      ->count();

        if ($remainingPending > 0) {
            $this->line("📊 Ainda restam {$remainingPending} artigos pendentes");
            $this->line('🔄 Para continuar: php artisan temp-article:escalation-run');
            $this->line('🤖 Para automático: verificar scheduler em ClaudeEscalationSchedule');
        } else {
            $this->line('🎉 TODOS os artigos foram processados!');
            $this->line('✅ Missão cumprida!');
        }

        $this->line('📈 Monitorar: TempArticle::where("has_specific_versions", true)->count()');
        $this->line('💰 Custos: revisar logs de escalação para otimização');
    }
}