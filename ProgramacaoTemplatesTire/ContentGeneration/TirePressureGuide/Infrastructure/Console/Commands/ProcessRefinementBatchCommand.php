<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;
use Src\ContentGeneration\TirePressureGuide\Application\Services\SectionRefinementService;

/**
 * Command para processar batches de refinamento
 * 
 * Processa artigos de um batch específico com rate limiting
 * e monitoramento de progresso em tempo real
 */
class ProcessRefinementBatchCommand extends Command
{
    protected $signature = 'tire-pressure:process-batch 
                           {batch : ID do batch a processar}
                           {--limit=10 : Número máximo de artigos a processar}
                           {--delay=60 : Delay em segundos entre processamentos}
                           {--dry-run : Preview sem executar}
                           {--continue : Continuar de onde parou}';

    protected $description = 'Processar batch de refinamento de seções';

    private SectionRefinementService $refinementService;

    public function __construct(SectionRefinementService $refinementService)
    {
        parent::__construct();
        $this->refinementService = $refinementService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchId = $this->argument('batch');
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $isDryRun = $this->option('dry-run');
        $continueProcessing = $this->option('continue');

        $this->info("🚀 Processando batch: {$batchId}\n");

        try {
            // Verificar se batch existe
            $batchStats = TirePressureArticle::getBatchStats($batchId);
            
            if ($batchStats['total'] === 0) {
                $this->error("❌ Batch não encontrado: {$batchId}");
                return 1;
            }

            // Mostrar estado do batch
            $this->displayBatchStatus($batchStats);

            // Verificar se há artigos para processar
            if ($batchStats['pending'] === 0 && !$continueProcessing) {
                $this->info("✅ Todos os artigos já foram processados!");
                return 0;
            }

            // Preview em dry run
            if ($isDryRun) {
                $this->runDryRunPreview($batchId, $limit);
                return 0;
            }

            // Confirmar processamento
            if (!$this->confirmProcessing($batchStats, $limit, $delay)) {
                $this->comment("Processamento cancelado");
                return 0;
            }

            // Processar batch
            $results = $this->processBatchWithProgress($batchId, $limit, $delay);

            // Mostrar resultados
            $this->displayResults($results);

            // Mostrar próximos passos
            $this->showNextSteps($batchId, $batchStats);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            Log::error("Erro ao processar batch", [
                'batch_id' => $batchId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Mostrar status do batch
     */
    private function displayBatchStatus(array $stats): void
    {
        $this->info("📊 Status do Batch:");
        
        $progressBar = $this->generateProgressBar($stats['progress_percentage']);
        $this->line("Progresso: {$progressBar} {$stats['progress_percentage']}%");
        
        $this->table(
            ['Status', 'Quantidade'],
            [
                ['✅ Concluídos', $stats['completed']],
                ['⏳ Pendentes', $stats['pending']],
                ['🔄 Processando', $stats['processing']],
                ['❌ Falhas', $stats['failed']],
                ['📊 Total', $stats['total']]
            ]
        );

        $this->info("💰 Estimativas:");
        $this->line("  • Custo total: ~\${$stats['estimated_cost']} USD");
        $this->line("  • Custo já gasto: ~\$" . round($stats['completed'] * 0.04, 2) . " USD");
        $this->line("  • Tokens estimados: ~" . number_format($stats['estimated_tokens']));
        $this->newLine();
    }

    /**
     * Gerar barra de progresso visual
     */
    private function generateProgressBar(float $percentage): string
    {
        $filled = (int) ($percentage / 5);
        $empty = 20 - $filled;
        
        return str_repeat('█', $filled) . str_repeat('░', $empty);
    }

    /**
     * Preview em dry run
     */
    private function runDryRunPreview(string $batchId, int $limit): void
    {
        $this->newLine();
        $this->comment("🔍 Modo DRY RUN - Preview do processamento");
        
        $articles = TirePressureArticle::inBatch($batchId)
                                      ->pendingRefinement()
                                      ->limit($limit)
                                      ->get();

        if ($articles->isEmpty()) {
            $this->warn("Nenhum artigo pendente para processar");
            return;
        }

        $this->info("Artigos que seriam processados:");
        
        $this->table(
            ['#', 'Veículo', 'Template', 'Tentativas'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    $article->vehicle_data['vehicle_full_name'] ?? 'N/A',
                    $article->template_type,
                    $article->refinement_attempts ?? 0
                ];
            })
        );

        $totalTime = $limit * ($this->option('delay') / 60);
        $this->newLine();
        $this->line("⏱️  Tempo estimado: " . $this->formatTime($totalTime));
        $this->line("💰 Custo estimado: ~\$" . ($limit * 0.04) . " USD");
    }

    /**
     * Confirmar processamento
     */
    private function confirmProcessing(array $stats, int $limit, int $delay): bool
    {
        $actualLimit = min($limit, $stats['pending']);
        $totalTime = $actualLimit * ($delay / 60);
        $estimatedCost = $actualLimit * 0.04;

        $this->info("📋 Configuração do Processamento:");
        $this->line("  • Artigos a processar: {$actualLimit}");
        $this->line("  • Delay entre artigos: {$delay} segundos");
        $this->line("  • Tempo estimado: " . $this->formatTime($totalTime));
        $this->line("  • Custo estimado: ~\${$estimatedCost} USD");
        $this->newLine();

        return $this->confirm("Iniciar processamento?");
    }

    /**
     * Processar batch com barra de progresso
     */
    private function processBatchWithProgress(string $batchId, int $limit, int $delay): array
    {
        $results = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'start_time' => now()
        ];

        $articles = TirePressureArticle::inBatch($batchId)
                                      ->pendingRefinement()
                                      ->limit($limit)
                                      ->get();

        if ($articles->isEmpty()) {
            $this->warn("Nenhum artigo pendente para processar");
            return $results;
        }

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %message%');

        $this->newLine(2);
        $progressBar->start();

        foreach ($articles as $index => $article) {
            $vehicleName = $article->vehicle_data['vehicle_full_name'] ?? 'N/A';
            $progressBar->setMessage("Processando: {$vehicleName}");

            // Processar artigo
            try {
                $success = $this->refinementService->refineArticleSections($article);
                
                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'vehicle' => $vehicleName,
                        'error' => 'Falha no refinamento'
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'vehicle' => $vehicleName,
                    'error' => $e->getMessage()
                ];
            }

            $results['processed']++;
            $progressBar->advance();

            // Rate limiting - aguardar entre processamentos
            if ($index < $articles->count() - 1) {
                $progressBar->setMessage("Aguardando {$delay}s (rate limiting)...");
                sleep($delay);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $results['end_time'] = now();
        $results['duration'] = $results['start_time']->diffForHumans($results['end_time'], true);

        return $results;
    }

    /**
     * Mostrar resultados do processamento
     */
    private function displayResults(array $results): void
    {
        $this->info("📊 Resultados do Processamento:");
        
        $successRate = $results['processed'] > 0 
            ? round(($results['success'] / $results['processed']) * 100, 1) 
            : 0;

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['✅ Sucessos', $results['success']],
                ['❌ Falhas', $results['failed']],
                ['📊 Total Processado', $results['processed']],
                ['📈 Taxa de Sucesso', "{$successRate}%"],
                ['⏱️  Duração', $results['duration'] ?? 'N/A']
            ]
        );

        // Mostrar erros se houver
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error("❌ Erros encontrados:");
            
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error['vehicle']}: {$error['error']}");
            }
        }

        // Custo real
        $realCost = $results['success'] * 0.04;
        $this->newLine();
        $this->info("💰 Custo real: ~\${$realCost} USD");
    }

    /**
     * Mostrar próximos passos
     */
    private function showNextSteps(string $batchId, array $stats): void
    {
        $this->newLine();
        $this->info("📝 Próximos Passos:");

        // Atualizar estatísticas
        $updatedStats = TirePressureArticle::getBatchStats($batchId);
        
        if ($updatedStats['pending'] > 0) {
            $this->line("1. Continuar processamento do batch:");
            $this->line("   php artisan tire-pressure:process-batch {$batchId} --continue");
        }

        if ($updatedStats['failed'] > 0) {
            $this->line("\n2. Reprocessar artigos com falha:");
            $this->line("   php artisan tire-pressure:retry-failed {$batchId}");
        }

        if ($updatedStats['completed'] === $updatedStats['total']) {
            $this->line("\n✅ Batch completamente processado!");
            $this->line("\n3. Validar qualidade das seções:");
            $this->line("   php artisan tire-pressure:validate-sections --batch={$batchId}");
            
            $this->line("\n4. Publicar artigos refinados:");
            $this->line("   php artisan tire-pressure:publish-refined --batch={$batchId}");
        }

        $this->line("\n5. Ver relatório completo do batch:");
        $this->line("   php artisan tire-pressure:batch-report {$batchId}");
    }

    /**
     * Formatar tempo em minutos
     */
    private function formatTime(float $minutes): string
    {
        if ($minutes < 60) {
            return round($minutes) . " minutos";
        }
        
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        
        return "{$hours}h {$mins}min";
    }
}