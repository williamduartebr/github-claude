<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
use Src\ContentGeneration\TireCalibration\Application\Services\PickupArticleFixService;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * FixIncompletePickupArticlesCommand V4
 * 
 * Command harmonizado com PickupArticleFixService para correção definitiva de pickups
 * com estruturas JSON incorretas que causam erro fatal no ViewModel.
 * 
 * CORREÇÃO APLICADA:
 * - Análise harmonizada com service (ambos detectam mesmos problemas)
 * - Loop de processamento de todos pickups problemáticos
 * - Rate limiting adequado (3 pickups por batch, 5s entre requests)
 * - Sistema de retry robusto
 * - Logging detalhado do processo
 * 
 * FOCO CRÍTICO: localizacao_etiqueta como string → object estruturado
 */

// php artisan tinker
// > use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;
//
// $updated = TireCalibration::where('main_category', 'pickup')
//     ->where('claude_refinement_version', 'v4_completed')
//     ->update([
//         'claude_refinement_version' => 'v4_pickup_fixing',
//         'pickup_fix_notes' => null,
//         'last_pickup_fix_at' => null
//     ]);
// echo "Pickups resetados: $updated\n";
// Pickups resetados: 16

class FixIncompletePickupArticlesCommand extends Command
{
    protected $signature = 'calibration:fix-incomplete-pickups
                            {--limit=1 : Limite de pickups para processar por execução}
                            {--batch-size=3 : Número de pickups por batch (rate limiting)}
                            {--delay=5 : Segundos de delay entre batches}
                            {--dry-run : Simular sem fazer alterações}
                            {--force-all : Processar todos pickups problemáticos}
                            {--skip-analysis : Pular análise inicial e ir direto para correção}
                            {--debug : Mostrar análise detalhada de cada pickup}';

    protected $description = 'Corrigir definitivamente pickups com estruturas JSON incorretas que causam erro no ViewModel';

    private PickupArticleFixService $fixService;
    private int $processedCount = 0;
    private int $fixedCount = 0;
    private int $errorCount = 0;
    private int $skippedCount = 0;
    private array $processingResults = [];
    private array $errorDetails = [];

    public function __construct(PickupArticleFixService $fixService)
    {
        parent::__construct();
        $this->fixService = $fixService;
    }

    public function handle(): ?int
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return null;
        }

        $startTime = microtime(true);
        
        $this->info('🔧 CORREÇÃO DEFINITIVA DE PICKUPS COM ESTRUTURAS JSON INCORRETAS');
        $this->line('   Harmonizado com PickupArticleFixService V4');
        $this->newLine();

        try {
            $config = $this->getConfig();
            $this->displayConfig($config);

            // Teste de conectividade API
            if (!$this->testApiConnectivity()) {
                return self::FAILURE;
            }

            // Análise inicial dos problemas
            if (!$config['skip_analysis']) {
                $this->performInitialAnalysis();
            }

            // Buscar candidatos para correção
            $candidates = $this->getCandidatesForFix($config);
            
            if ($candidates->isEmpty()) {
                $this->warn('❌ Nenhum pickup problemático encontrado para correção');
                return self::SUCCESS;
            }

            $this->info("📊 Encontrados {$candidates->count()} pickup(s) com estruturas incorretas");
            $this->newLine();

            // Processar pickups em batches
            $this->processPickupsBatched($candidates, $config);

            // Exibir resultados finais
            $this->displayFinalResults(microtime(true) - $startTime);

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ ERRO CRÍTICO: ' . $e->getMessage());
            Log::error('FixIncompletePickupArticlesCommand: Erro crítico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Obter configuração do comando
     */
    private function getConfig(): array
    {
        return [
            'limit' => $this->option('force-all') ? 1000 : (int) $this->option('limit'),
            'batch_size' => min(5, max(1, (int) $this->option('batch-size'))), // Entre 1-5
            'delay' => max(3, (int) $this->option('delay')), // Mínimo 3s
            'dry_run' => $this->option('dry-run'),
            'force_all' => $this->option('force-all'),
            'skip_analysis' => $this->option('skip-analysis'),
            'debug' => $this->option('debug')
        ];
    }

    /**
     * Exibir configuração do comando
     */
    private function displayConfig(array $config): void
    {
        $this->info('⚙️ CONFIGURAÇÃO:');
        $this->line('   • Limite por execução: ' . ($config['force_all'] ? 'TODOS' : $config['limit']));
        $this->line('   • Pickups por batch: ' . $config['batch_size']);
        $this->line('   • Delay entre batches: ' . $config['delay'] . 's');
        $this->line('   • Modo: ' . ($config['dry_run'] ? '🔍 SIMULAÇÃO' : '💾 EXECUÇÃO REAL'));
        $this->line('   • Pular análise inicial: ' . ($config['skip_analysis'] ? 'SIM' : 'NÃO'));
        $this->line('   • Debug detalhado: ' . ($config['debug'] ? 'SIM' : 'NÃO'));
        $this->newLine();

        $this->info('🎯 FOCO CRÍTICO:');
        $this->line('   • localizacao_etiqueta: string → object estruturado');
        $this->line('   • condicoes_especiais: string → array de objects');
        $this->line('   • conversao_unidades: string → object com tabela_conversao');
        $this->line('   • Corrigir estruturas que causam erro fatal no ViewModel');
        $this->newLine();
    }

    /**
     * Testar conectividade com Claude API
     */
    private function testApiConnectivity(): bool
    {
        $this->info('🌐 Testando conectividade com Claude API...');
        
        $test = $this->fixService->testApiConnection();
        
        if ($test['success']) {
            $responseTime = isset($test['response_time']) ? round($test['response_time'] * 1000) . 'ms' : 'N/A';
            $this->line("   ✅ Claude API conectada (Status: {$test['status']}, Tempo: {$responseTime})");
            return true;
        } else {
            $this->error("   ❌ Falha na conexão: " . ($test['error'] ?? 'Erro desconhecido'));
            $this->error('   Verifique a configuração da API key do Anthropic');
            return false;
        }
    }

    /**
     * Análise inicial dos problemas existentes
     */
    private function performInitialAnalysis(): void
    {
        $this->info('🔬 ANÁLISE INICIAL DOS PROBLEMAS ESTRUTURAIS');
        $this->line('─────────────────────────────────────────────');

        $allPickups = TireCalibration::where('main_category', 'pickup')
            ->where('version', 'v2')
            ->whereIn('enrichment_phase', [
                'claude_3a_completed',
                'claude_3b_completed', 
                'claude_completed'
            ])
            ->whereNotNull('generated_article')
            ->get();

        $this->info("📊 Total de pickups V2: {$allPickups->count()}");

        $problemCounts = [
            'localizacao_etiqueta_string' => 0,
            'condicoes_especiais_string' => 0,
            'conversao_unidades_string' => 0,
            'structures_missing' => 0,
            'needs_fix_total' => 0
        ];

        $criticalPickups = [];

        foreach ($allPickups as $pickup) {
            $analysis = $this->fixService->analyzeMissingSections($pickup);
            
            if ($analysis['needs_fix']) {
                $problemCounts['needs_fix_total']++;
                
                // Contar problemas específicos
                foreach ($analysis['incorrect_structures'] as $issue) {
                    switch ($issue['section']) {
                        case 'localizacao_etiqueta':
                            if ($issue['actual_type'] === 'string') {
                                $problemCounts['localizacao_etiqueta_string']++;
                                if ($issue['issue'] === 'critical_structure_error') {
                                    $criticalPickups[] = [
                                        'vehicle' => "{$pickup->vehicle_make} {$pickup->vehicle_model}",
                                        'id' => $pickup->_id,
                                        'issue' => $issue['description']
                                    ];
                                }
                            }
                            break;
                        case 'condicoes_especiais':
                            if ($issue['actual_type'] === 'string') {
                                $problemCounts['condicoes_especiais_string']++;
                            }
                            break;
                        case 'conversao_unidades':
                            if ($issue['actual_type'] === 'string') {
                                $problemCounts['conversao_unidades_string']++;
                            }
                            break;
                    }
                }
            }
        }

        $this->newLine();
        $this->line('📈 PROBLEMAS DETECTADOS:');
        $this->line("   • localizacao_etiqueta como string: {$problemCounts['localizacao_etiqueta_string']} (CRÍTICO)");
        $this->line("   • condicoes_especiais como string: {$problemCounts['condicoes_especiais_string']}");
        $this->line("   • conversao_unidades como string: {$problemCounts['conversao_unidades_string']}");
        $this->line("   • Total precisando correção: {$problemCounts['needs_fix_total']}");

        if (!empty($criticalPickups)) {
            $this->newLine();
            $this->error('🚨 PICKUPS CRÍTICOS (causam erro fatal no ViewModel):');
            foreach (array_slice($criticalPickups, 0, 5) as $critical) {
                $this->line("   • {$critical['vehicle']} - {$critical['issue']}");
            }
            
            if (count($criticalPickups) > 5) {
                $remaining = count($criticalPickups) - 5;
                $this->line("   ... e mais {$remaining} pickup(s) crítico(s)");
            }
        }

        $this->newLine();
    }

    /**
     * Buscar candidatos para correção
     */
    private function getCandidatesForFix(array $config)
    {
        $query = TireCalibration::where('main_category', 'pickup')
            ->where('version', 'v2')
            ->whereIn('enrichment_phase', [
                'claude_3a_completed',
                'claude_3b_completed', 
                'claude_completed'
            ])
            ->whereNotNull('generated_article');

        // Priorizar pickups que ainda não foram corrigidos
        $query->where(function($q) {
            $q->whereNull('claude_refinement_version')
              ->orWhere('claude_refinement_version', '!=', 'v4_pickup_fixed');
        });

        if (!$config['force_all']) {
            $query->limit($config['limit']);
        }

        $candidates = $query->orderBy('updated_at', 'asc')->get();

        // Filtrar apenas os que realmente precisam de correção
        return $candidates->filter(function ($pickup) {
            $analysis = $this->fixService->analyzeMissingSections($pickup);
            return $analysis['needs_fix'];
        });
    }

 
    /**
     * Processar pickups em batches com rate limiting
     */
    private function processPickupsBatched($candidates, array $config): void
    {
        $this->info('🚀 INICIANDO CORREÇÃO DOS PICKUPS...');
        $this->newLine();

        $batches = $candidates->chunk($config['batch_size']);
        $totalBatches = $batches->count();
        $currentBatch = 1;

        foreach ($batches as $batch) {
            $this->line("📦 Processando batch {$currentBatch}/{$totalBatches} ({$batch->count()} pickup(s))...");
            
            // Processar cada pickup do batch
            foreach ($batch as $pickup) {
                $this->processSinglePickup($pickup, $config);
            }

            // Rate limiting entre batches (exceto no último)
            if ($currentBatch < $totalBatches) {
                $this->line("   ⏱️ Aguardando {$config['delay']}s antes do próximo batch...");
                sleep($config['delay']);
            }

            $currentBatch++;
        }

        $this->newLine();
    }

    /**
     * Processar pickup individual
     */
    private function processSinglePickup(TireCalibration $pickup, array $config): void
    {
        $vehicleInfo = "{$pickup->vehicle_make} {$pickup->vehicle_model}";
        $this->processedCount++;

        try {
            // Análise prévia
            $analysis = $this->fixService->analyzeMissingSections($pickup);
            
            if (!$analysis['needs_fix']) {
                $this->line("   ✅ {$vehicleInfo} - Já está correto");
                $this->skippedCount++;
                return;
            }

            if ($config['debug']) {
                $this->displayPickupAnalysis($pickup, $analysis);
            }

            if ($config['dry_run']) {
                $this->line("   🔍 [SIMULAÇÃO] {$vehicleInfo} - Seria corrigido ({$analysis['missing_sections']} estruturas)");
                $this->processingResults[] = [
                    'vehicle' => $vehicleInfo,
                    'status' => 'simulated',
                    'structures_count' => $analysis['missing_sections'],
                    'is_critical' => $analysis['is_critical'] ?? false
                ];
                return;
            }

            // Correção efetiva
            $this->line("   🔧 Corrigindo {$vehicleInfo}...");
            $result = $this->fixService->fixPickupArticle($pickup);

            if ($result['success']) {
                $this->line("   ✅ {$vehicleInfo} - Corrigido ({$result['structures_fixed']} estruturas)");
                $this->fixedCount++;
                
                $this->processingResults[] = [
                    'vehicle' => $vehicleInfo,
                    'status' => 'fixed',
                    'structures_fixed' => $result['structures_fixed'],
                    'fixed_sections' => $result['fixed_sections'],
                    'is_critical' => $analysis['is_critical'] ?? false
                ];

                Log::info('FixIncompletePickupArticlesCommand: Pickup corrigido', [
                    'pickup_id' => $pickup->_id,
                    'vehicle' => $vehicleInfo,
                    'structures_fixed' => $result['structures_fixed'],
                    'sections' => $result['fixed_sections']
                ]);

            } else {
                $this->error("   ❌ {$vehicleInfo} - Erro: " . ($result['error'] ?? 'Erro desconhecido'));
                $this->errorCount++;
                
                $this->errorDetails[] = [
                    'vehicle' => $vehicleInfo,
                    'error' => $result['error'] ?? 'Erro desconhecido'
                ];

                Log::error('FixIncompletePickupArticlesCommand: Erro na correção', [
                    'pickup_id' => $pickup->_id,
                    'vehicle' => $vehicleInfo,
                    'error' => $result['error'] ?? 'Erro desconhecido',
                    'analysis' => $analysis
                ]);
            }

        } catch (Exception $e) {
            $this->error("   ❌ {$vehicleInfo} - Exceção: " . $e->getMessage());
            $this->errorCount++;
            
            $this->errorDetails[] = [
                'vehicle' => $vehicleInfo,
                'error' => $e->getMessage()
            ];

            Log::error('FixIncompletePickupArticlesCommand: Exceção', [
                'pickup_id' => $pickup->_id,
                'vehicle' => $vehicleInfo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Exibir análise detalhada do pickup
     */
    private function displayPickupAnalysis(TireCalibration $pickup, array $analysis): void
    {
        $vehicleInfo = "{$pickup->vehicle_make} {$pickup->vehicle_model}";
        
        $this->newLine();
        $this->line("🔍 ANÁLISE DETALHADA: {$vehicleInfo}");
        $this->line("   • Precisa correção: " . ($analysis['needs_fix'] ? 'SIM' : 'NÃO'));
        $this->line("   • É crítico: " . ($analysis['is_critical'] ? 'SIM' : 'NÃO'));
        $this->line("   • Estruturas problemáticas: {$analysis['missing_sections']}");
        
        if (!empty($analysis['incorrect_structures'])) {
            $this->line("   • Problemas encontrados:");
            foreach ($analysis['incorrect_structures'] as $issue) {
                $critical = ($issue['issue'] === 'critical_structure_error') ? ' [CRÍTICO]' : '';
                $this->line("     - {$issue['section']}: {$issue['actual_type']} → {$issue['expected_type']}{$critical}");
            }
        }
        $this->newLine();
    }

    /**
     * Exibir resultados finais
     */
    private function displayFinalResults(float $duration): void
    {
        $this->info('📊 RESULTADOS FINAIS DA CORREÇÃO:');
        $this->newLine();

        $this->line("✅ <fg=green>Pickups corrigidos:</fg=green> {$this->fixedCount}");
        $this->line("❌ <fg=red>Erros:</fg=red> {$this->errorCount}");
        $this->line("⏭️ <fg=yellow>Ignorados (já corretos):</fg=yellow> {$this->skippedCount}");
        $this->line("📊 <fg=blue>Total processado:</fg=blue> {$this->processedCount}");
        $this->line("⏱️ <fg=cyan>Tempo total:</fg=cyan> " . round($duration, 2) . "s");

        if ($this->fixedCount > 0) {
            $avgTime = round($duration / $this->processedCount, 2);
            $this->line("📊 <fg=cyan>Média por pickup:</fg=cyan> {$avgTime}s");
        }

        $this->newLine();

        // Mostrar pickups críticos corrigidos
        $criticalFixed = array_filter($this->processingResults, function($result) {
            return $result['status'] === 'fixed' && ($result['is_critical'] ?? false);
        });

        if (!empty($criticalFixed)) {
            $this->info('🚨 PICKUPS CRÍTICOS CORRIGIDOS (não causarão mais erro no ViewModel):');
            foreach ($criticalFixed as $result) {
                $this->line("   • {$result['vehicle']} - {$result['structures_fixed']} estruturas corrigidas");
            }
            $this->newLine();
        }

        // Mostrar erros se houver
        if (!empty($this->errorDetails)) {
            $this->error('❌ ERROS ENCONTRADOS:');
            foreach (array_slice($this->errorDetails, 0, 5) as $error) {
                $this->line("   • {$error['vehicle']}: {$error['error']}");
            }

            if (count($this->errorDetails) > 5) {
                $remaining = count($this->errorDetails) - 5;
                $this->line("   ... e mais {$remaining} erro(s)");
            }
            $this->newLine();
        }

        if ($this->fixedCount > 0) {
            $this->info('🎉 CORREÇÃO CONCLUÍDA!');
            $this->line('   • Estruturas JSON corrigidas para formato object/array');
            $this->line('   • localizacao_etiqueta agora é object estruturado');
            $this->line('   • ViewModel não terá mais erro fatal');
            $this->line('   • Pickups prontos para renderização');
        }

        // Status para próximas execuções
        $remainingPickups = $this->getRemainingPickupsCount();
        if ($remainingPickups > 0) {
            $this->newLine();
            $this->warn("⚠️ Ainda restam {$remainingPickups} pickup(s) para correção.");
            $this->line('   Execute novamente o comando para processar os restantes.');
        } else {
            $this->newLine();
            $this->info('✅ Todos os pickups com problemas estruturais foram corrigidos!');
        }
    }

    /**
     * Contar pickups restantes que precisam de correção
     */
    private function getRemainingPickupsCount(): int
    {
        $remaining = TireCalibration::where('main_category', 'pickup')
            ->where('version', 'v2')
            ->whereIn('enrichment_phase', [
                'claude_3a_completed',
                'claude_3b_completed', 
                'claude_completed'
            ])
            ->whereNotNull('generated_article')
            ->where(function($q) {
                $q->whereNull('claude_refinement_version')
                  ->orWhere('claude_refinement_version', '!=', 'v4_pickup_fixed');
            })
            ->get();

        return $remaining->filter(function ($pickup) {
            $analysis = $this->fixService->analyzeMissingSections($pickup);
            return $analysis['needs_fix'];
        })->count();
    }
}