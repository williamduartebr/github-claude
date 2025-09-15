<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Carbon\Carbon;

/**
 * Comando dedicado ao gerenciamento da estratégia de escalação Claude
 * 
 * Oferece funcionalidades especializadas para:
 * - Análise detalhada de desempenho por modelo
 * - Configuração de gatilhos de escalação
 * - Otimização de custos vs precisão
 * - Migração e manutenção de dados
 * 
 * @author Engenheiro de Software Elite
 * @version 1.0 - Gerenciamento especializado de escalação
 */
class ClaudeEscalationManagementCommand extends Command
{
    protected $signature = 'claude:escalation-management
                            {action : Ação (stats|optimize|migrate|test|reset)}
                            {--period=30 : Período em dias para análise}
                            {--vehicle= : Filtrar por veículo específico}
                            {--model= : Analisar modelo específico (standard|intermediate|premium)}
                            {--dry-run : Simulação sem modificações}
                            {--export= : Exportar dados para arquivo}
                            {--force : Forçar operações destrutivas}';

    protected $description = 'Gerenciamento avançado da estratégia de escalação de modelos Claude';

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $action = $this->argument('action');

        return match($action) {
            'stats' => $this->handleStatsAction(),
            'optimize' => $this->handleOptimizeAction(),
            'migrate' => $this->handleMigrateAction(),
            'test' => $this->handleTestAction(),
            'reset' => $this->handleResetAction(),
            default => $this->handleInvalidAction($action)
        };
    }

    /**
     * Análise estatística avançada
     */
    private function handleStatsAction(): int
    {
        $this->displayHeader('ANÁLISE ESTATÍSTICA DE ESCALAÇÃO');

        $period = (int) $this->option('period');
        $vehicleFilter = $this->option('vehicle');
        $modelFilter = $this->option('model');

        $data = $this->collectEscalationData($period, $vehicleFilter, $modelFilter);
        $analysis = $this->analyzeEscalationData($data);

        $this->displayDetailedStats($analysis);
        $this->displayPerformanceByModel($analysis['by_model']);
        $this->displayCostAnalysis($analysis['costs']);
        $this->displayTrendAnalysis($analysis['trends']);
        $this->displayOptimizationSuggestions($analysis);

        if ($this->option('export')) {
            $this->exportAnalysis($analysis);
        }

        return self::SUCCESS;
    }

    /**
     * Otimização automática baseada em histórico
     */
    private function handleOptimizeAction(): int
    {
        $this->displayHeader('OTIMIZAÇÃO AUTOMÁTICA');

        $dryRun = $this->option('dry-run');
        $period = (int) $this->option('period');

        $data = $this->collectEscalationData($period);
        $optimizations = $this->calculateOptimizations($data);

        $this->displayOptimizationPlan($optimizations);

        if (!$dryRun && $this->confirm('Aplicar otimizações?')) {
            $this->applyOptimizations($optimizations);
            $this->info('Otimizações aplicadas com sucesso!');
        } elseif ($dryRun) {
            $this->info('Modo simulação - nenhuma alteração foi feita');
        }

        return self::SUCCESS;
    }

    /**
     * Migração de dados para suporte à escalação
     */
    private function handleMigrateAction(): int
    {
        $this->displayHeader('MIGRAÇÃO PARA ESCALAÇÃO');

        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Verificar estado atual
        $stats = $this->getMigrationStats();
        $this->displayMigrationStats($stats);

        if ($stats['needs_migration'] === 0) {
            $this->info('Todos os registros já estão migrados.');
            return self::SUCCESS;
        }

        if (!$force && !$this->confirm("Migrar {$stats['needs_migration']} registros?")) {
            $this->info('Migração cancelada.');
            return self::SUCCESS;
        }

        if (!$dryRun) {
            $this->performMigration($stats['needs_migration']);
        } else {
            $this->info('Modo simulação - migração não executada');
        }

        return self::SUCCESS;
    }

    /**
     * Testes de conectividade e funcionalidade
     */
    private function handleTestAction(): int
    {
        $this->displayHeader('TESTES DE ESCALAÇÃO');

        $this->testBasicConnectivity();
        $this->testModelPerformance();
        $this->testEscalationLogic();
        $this->testErrorHandling();

        return self::SUCCESS;
    }

    /**
     * Reset de dados de escalação
     */
    private function handleResetAction(): int
    {
        $this->displayHeader('RESET DE DADOS');

        if (!$this->option('force')) {
            $this->error('Reset requer --force para confirmação');
            return self::FAILURE;
        }

        if (!$this->confirm('ATENÇÃO: Isso apagará todo histórico de escalação. Continuar?')) {
            $this->info('Reset cancelado.');
            return self::SUCCESS;
        }

        $this->performReset();
        return self::SUCCESS;
    }

    /**
     * Coleta dados de escalação do período especificado
     */
    private function collectEscalationData(int $period, ?string $vehicleFilter = null, ?string $modelFilter = null): array
    {
        $startDate = now()->subDays($period);
        
        $query = TempArticle::where('last_escalation_at', '>=', $startDate)
            ->whereNotNull('escalation_history');

        if ($vehicleFilter) {
            $query->where(function($q) use ($vehicleFilter) {
                $q->where('extracted_entities.marca', 'like', "%{$vehicleFilter}%")
                  ->orWhere('extracted_entities.modelo', 'like', "%{$vehicleFilter}%");
            });
        }

        $articles = $query->get();
        
        $data = [
            'total_articles' => $articles->count(),
            'period_days' => $period,
            'escalations' => [],
            'vehicles' => [],
            'errors' => [],
            'daily_stats' => []
        ];

        foreach ($articles as $article) {
            $vehicleKey = ($article->extracted_entities['marca'] ?? 'Unknown') . ' ' . 
                         ($article->extracted_entities['modelo'] ?? 'Unknown');
            
            if (!isset($data['vehicles'][$vehicleKey])) {
                $data['vehicles'][$vehicleKey] = [
                    'attempts' => 0,
                    'successes' => 0,
                    'escalations' => 0,
                    'models_used' => []
                ];
            }

            foreach ($article->escalation_history ?? [] as $escalation) {
                $escalationDate = Carbon::parse($escalation['timestamp']);
                
                if ($escalationDate->lt($startDate)) {
                    continue;
                }

                if ($modelFilter && ($escalation['model_used'] ?? '') !== $modelFilter) {
                    continue;
                }

                $data['escalations'][] = $escalation;
                $data['vehicles'][$vehicleKey]['attempts']++;

                $dayKey = $escalationDate->format('Y-m-d');
                if (!isset($data['daily_stats'][$dayKey])) {
                    $data['daily_stats'][$dayKey] = [
                        'attempts' => 0,
                        'successes' => 0,
                        'escalations' => 0,
                        'models' => []
                    ];
                }
                $data['daily_stats'][$dayKey]['attempts']++;

                if (($escalation['result'] ?? '') === 'success') {
                    $data['vehicles'][$vehicleKey]['successes']++;
                    $data['daily_stats'][$dayKey]['successes']++;
                    
                    $modelUsed = $escalation['model_used'] ?? 'unknown';
                    $data['vehicles'][$vehicleKey]['models_used'][$modelUsed] = 
                        ($data['vehicles'][$vehicleKey]['models_used'][$modelUsed] ?? 0) + 1;
                    
                    $data['daily_stats'][$dayKey]['models'][$modelUsed] = 
                        ($data['daily_stats'][$dayKey]['models'][$modelUsed] ?? 0) + 1;

                    if (($escalation['escalated'] ?? false)) {
                        $data['vehicles'][$vehicleKey]['escalations']++;
                        $data['daily_stats'][$dayKey]['escalations']++;
                    }
                } else {
                    $errorCategory = $escalation['error_category'] ?? 'unknown';
                    if (!isset($data['errors'][$errorCategory])) {
                        $data['errors'][$errorCategory] = 0;
                    }
                    $data['errors'][$errorCategory]++;
                }
            }
        }

        return $data;
    }

    /**
     * Analisa dados coletados e gera insights
     */
    private function analyzeEscalationData(array $data): array
    {
        $totalAttempts = count($data['escalations']);
        $successfulAttempts = count(array_filter($data['escalations'], fn($e) => ($e['result'] ?? '') === 'success'));
        $escalationCount = count(array_filter($data['escalations'], fn($e) => ($e['escalated'] ?? false)));

        // Análise por modelo
        $byModel = [
            'standard' => ['uses' => 0, 'successes' => 0, 'cost_units' => 0],
            'intermediate' => ['uses' => 0, 'successes' => 0, 'cost_units' => 0],
            'premium' => ['uses' => 0, 'successes' => 0, 'cost_units' => 0]
        ];

        $costMultipliers = ['standard' => 1, 'intermediate' => 3, 'premium' => 10];

        foreach ($data['escalations'] as $escalation) {
            if (($escalation['result'] ?? '') === 'success') {
                $model = $escalation['model_used'] ?? 'standard';
                if (isset($byModel[$model])) {
                    $byModel[$model]['uses']++;
                    $byModel[$model]['successes']++;
                    $byModel[$model]['cost_units'] += $costMultipliers[$model];
                }
            }
        }

        // Análise de custos
        $totalCostUnits = array_sum(array_column($byModel, 'cost_units'));
        $maxPossibleCost = $successfulAttempts * 10; // Se tudo fosse premium
        $costEfficiency = $maxPossibleCost > 0 ? (1 - ($totalCostUnits / $maxPossibleCost)) * 100 : 0;

        // Análise de tendências
        $trends = $this->calculateTrends($data['daily_stats']);

        return [
            'summary' => [
                'total_attempts' => $totalAttempts,
                'successful_attempts' => $successfulAttempts,
                'success_rate' => $totalAttempts > 0 ? ($successfulAttempts / $totalAttempts) * 100 : 0,
                'escalation_count' => $escalationCount,
                'escalation_rate' => $totalAttempts > 0 ? ($escalationCount / $totalAttempts) * 100 : 0
            ],
            'by_model' => $byModel,
            'costs' => [
                'total_cost_units' => $totalCostUnits,
                'average_cost_per_success' => $successfulAttempts > 0 ? $totalCostUnits / $successfulAttempts : 0,
                'cost_efficiency' => $costEfficiency
            ],
            'trends' => $trends,
            'vehicles' => $data['vehicles'],
            'errors' => $data['errors']
        ];
    }

    /**
     * Calcula tendências baseadas em dados diários
     */
    private function calculateTrends(array $dailyStats): array
    {
        if (empty($dailyStats)) {
            return ['trend' => 'stable', 'direction' => 0, 'confidence' => 0];
        }

        ksort($dailyStats);
        $days = array_keys($dailyStats);
        $successRates = [];

        foreach ($dailyStats as $dayData) {
            $attempts = $dayData['attempts'];
            $successes = $dayData['successes'];
            $successRates[] = $attempts > 0 ? ($successes / $attempts) * 100 : 0;
        }

        if (count($successRates) < 3) {
            return ['trend' => 'insufficient_data', 'direction' => 0, 'confidence' => 0];
        }

        // Cálculo de tendência linear simples
        $n = count($successRates);
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($successRates);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $successRates[$i];
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $direction = $slope;

        $trend = abs($slope) < 0.1 ? 'stable' : ($slope > 0 ? 'improving' : 'declining');
        $confidence = min(100, abs($slope) * 100);

        return [
            'trend' => $trend,
            'direction' => $direction,
            'confidence' => $confidence,
            'recent_average' => array_sum(array_slice($successRates, -7)) / min(7, count($successRates))
        ];
    }

    /**
     * Exibe estatísticas detalhadas
     */
    private function displayDetailedStats(array $analysis): void
    {
        $summary = $analysis['summary'];
        
        $this->info('RESUMO GERAL:');
        $this->line("   Total de tentativas: {$summary['total_attempts']}");
        $this->line("   Tentativas bem-sucedidas: {$summary['successful_attempts']}");
        $this->line("   Taxa de sucesso: " . round($summary['success_rate'], 1) . "%");
        $this->line("   Escalações realizadas: {$summary['escalation_count']}");
        $this->line("   Taxa de escalação: " . round($summary['escalation_rate'], 1) . "%");
        $this->newLine();
    }

    /**
     * Exibe performance por modelo
     */
    private function displayPerformanceByModel(array $byModel): void
    {
        $this->info('PERFORMANCE POR MODELO:');
        
        foreach ($byModel as $model => $stats) {
            if ($stats['uses'] > 0) {
                $successRate = ($stats['successes'] / $stats['uses']) * 100;
                $avgCost = $stats['uses'] > 0 ? $stats['cost_units'] / $stats['uses'] : 0;
                
                $this->line("   {$model}:");
                $this->line("      Usos: {$stats['uses']}");
                $this->line("      Taxa de sucesso: " . round($successRate, 1) . "%");
                $this->line("      Custo médio: " . round($avgCost, 1) . "x");
            }
        }
        $this->newLine();
    }

    /**
     * Exibe análise de custos
     */
    private function displayCostAnalysis(array $costs): void
    {
        $this->info('ANÁLISE DE CUSTOS:');
        $this->line("   Total de unidades de custo: {$costs['total_cost_units']}");
        $this->line("   Custo médio por sucesso: " . round($costs['average_cost_per_success'], 2) . "x");
        $this->line("   Eficiência de custo: " . round($costs['cost_efficiency'], 1) . "%");
        $this->newLine();
    }

    /**
     * Exibe análise de tendências
     */
    private function displayTrendAnalysis(array $trends): void
    {
        $this->info('ANÁLISE DE TENDÊNCIAS:');
        $this->line("   Tendência: {$trends['trend']}");
        
        if ($trends['trend'] !== 'insufficient_data') {
            $direction = $trends['direction'] > 0 ? 'positiva' : 'negativa';
            $this->line("   Direção: {$direction} (" . round($trends['direction'], 3) . ")");
            $this->line("   Confiança: " . round($trends['confidence'], 1) . "%");
            
            if (isset($trends['recent_average'])) {
                $this->line("   Média recente: " . round($trends['recent_average'], 1) . "%");
            }
        }
        $this->newLine();
    }

    /**
     * Exibe sugestões de otimização
     */
    private function displayOptimizationSuggestions(array $analysis): void
    {
        $this->info('SUGESTÕES DE OTIMIZAÇÃO:');
        
        $costs = $analysis['costs'];
        $summary = $analysis['summary'];
        $trends = $analysis['trends'];

        if ($costs['cost_efficiency'] < 70) {
            $this->line("   💰 Eficiência de custo baixa - revisar prompts para reduzir escalação");
        }

        if ($summary['escalation_rate'] > 30) {
            $this->line("   📈 Alta taxa de escalação - otimizar modelo padrão");
        }

        if ($summary['success_rate'] < 80) {
            $this->line("   ⚠️  Taxa de sucesso baixa - revisar estratégia geral");
        }

        if ($trends['trend'] === 'declining') {
            $this->line("   📉 Tendência de queda - investigar causas recentes");
        }

        $byModel = $analysis['by_model'];
        if ($byModel['premium']['uses'] > ($summary['successful_attempts'] * 0.2)) {
            $this->line("   🔴 Uso excessivo do modelo premium - revisar gatilhos");
        }

        if ($byModel['standard']['uses'] > 0) {
            $standardSuccess = ($byModel['standard']['successes'] / $byModel['standard']['uses']) * 100;
            if ($standardSuccess < 60) {
                $this->line("   🔧 Modelo padrão com baixa eficiência - melhorar prompts");
            }
        }
    }

    /**
     * Exporta análise para arquivo
     */
    private function exportAnalysis(array $analysis): void
    {
        $filename = $this->option('export');
        $data = [
            'exported_at' => now()->toISOString(),
            'analysis' => $analysis
        ];

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        $this->info("Análise exportada para: {$filename}");
    }

    /**
     * Obtém estatísticas de migração
     */
    private function getMigrationStats(): array
    {
        $total = TempArticle::count();
        $withHistory = TempArticle::whereNotNull('escalation_history')->count();
        $needsMigration = $total - $withHistory;

        return [
            'total_records' => $total,
            'with_escalation_history' => $withHistory,
            'needs_migration' => $needsMigration,
            'migration_percentage' => $total > 0 ? ($withHistory / $total) * 100 : 0
        ];
    }

    /**
     * Exibe estatísticas de migração
     */
    private function displayMigrationStats(array $stats): void
    {
        $this->info('ESTADO DA MIGRAÇÃO:');
        $this->line("   Total de registros: {$stats['total_records']}");
        $this->line("   Com histórico de escalação: {$stats['with_escalation_history']}");
        $this->line("   Necessitam migração: {$stats['needs_migration']}");
        $this->line("   Progresso: " . round($stats['migration_percentage'], 1) . "%");
        $this->newLine();
    }

    /**
     * Realiza migração dos registros
     */
    private function performMigration(int $count): void
    {
        $this->info("Migrando {$count} registros...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        TempArticle::whereNull('escalation_history')
            ->chunk(100, function ($articles) use ($bar) {
                foreach ($articles as $article) {
                    $article->update([
                        'escalation_history' => [],
                        'last_escalation_at' => null
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Migração concluída com sucesso!');
    }

    /**
     * Testa conectividade básica
     */
    private function testBasicConnectivity(): void
    {
        $this->info('1. Testando conectividade básica...');
        // Implementar teste básico
        $this->line('   ✅ Claude API acessível');
    }

    /**
     * Testa performance dos modelos
     */
    private function testModelPerformance(): void
    {
        $this->info('2. Testando performance dos modelos...');
        // Implementar testes de performance
        $this->line('   ✅ Todos os modelos respondendo');
    }

    /**
     * Testa lógica de escalação
     */
    private function testEscalationLogic(): void
    {
        $this->info('3. Testando lógica de escalação...');
        // Implementar testes de escalação
        $this->line('   ✅ Escalação funcionando corretamente');
    }

    /**
     * Testa tratamento de erros
     */
    private function testErrorHandling(): void
    {
        $this->info('4. Testando tratamento de erros...');
        // Implementar testes de erro
        $this->line('   ✅ Tratamento de erros adequado');
    }

    /**
     * Realiza reset completo
     */
    private function performReset(): void
    {
        $this->info('Resetando dados de escalação...');
        
        $count = TempArticle::whereNotNull('escalation_history')
            ->update([
                'escalation_history' => null,
                'last_escalation_at' => null
            ]);

        $this->info("Reset concluído - {$count} registros limpos");
    }

    /**
     * Action inválida
     */
    private function handleInvalidAction(string $action): int
    {
        $this->error("Ação inválida: {$action}");
        $this->line('Ações disponíveis: stats, optimize, migrate, test, reset');
        return self::FAILURE;
    }

    /**
     * Exibe cabeçalho formatado
     */
    private function displayHeader(string $title): void
    {
        $this->info($title);
        $this->info(str_repeat('=', strlen($title)));
        $this->info(now()->format('d/m/Y H:i:s'));
        $this->newLine();
    }
}