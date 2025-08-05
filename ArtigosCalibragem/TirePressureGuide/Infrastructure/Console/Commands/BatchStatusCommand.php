<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Command para monitorar status de batches
 * 
 * Mostra informações detalhadas sobre batches de refinamento
 * incluindo progresso, estatísticas e estimativas
 */
class BatchStatusCommand extends Command
{
    protected $signature = 'tire-pressure:batch-status 
                           {batch? : ID do batch específico}
                           {--all : Listar todos os batches}
                           {--active : Apenas batches ativos}
                           {--detailed : Informações detalhadas}';

    protected $description = 'Monitorar status dos batches de refinamento';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchId = $this->argument('batch');
        $showAll = $this->option('all');
        $activeOnly = $this->option('active');
        $detailed = $this->option('detailed');

        try {
            if ($batchId) {
                // Mostrar status de um batch específico
                $this->showBatchDetails($batchId, $detailed);
            } elseif ($showAll || $activeOnly) {
                // Listar múltiplos batches
                $this->listBatches($activeOnly, $detailed);
            } else {
                // Mostrar resumo geral
                $this->showGeneralSummary();
            }

            // Mostrar estatísticas do dia
            $this->showDailyStats();

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Mostrar detalhes de um batch específico
     */
    private function showBatchDetails(string $batchId, bool $detailed): void
    {
        $stats = TirePressureArticle::getBatchStats($batchId);
        
        if ($stats['total'] === 0) {
            $this->error("Batch não encontrado: {$batchId}");
            return;
        }

        $this->info("📦 Batch: {$batchId}\n");

        // Barra de progresso visual
        $this->displayProgressBar($stats);

        // Tabela de estatísticas
        $this->table(
            ['Métrica', 'Valor', 'Percentual'],
            [
                ['✅ Concluídos', $stats['completed'], $this->formatPercentage($stats['completed'], $stats['total'])],
                ['⏳ Pendentes', $stats['pending'], $this->formatPercentage($stats['pending'], $stats['total'])],
                ['🔄 Processando', $stats['processing'], $this->formatPercentage($stats['processing'], $stats['total'])],
                ['❌ Falhas', $stats['failed'], $this->formatPercentage($stats['failed'], $stats['total'])],
                ['', '', ''],
                ['📊 Total', $stats['total'], '100%']
            ]
        );

        // Estimativas
        $this->showBatchEstimates($stats);

        // Detalhes adicionais
        if ($detailed) {
            $this->showDetailedBatchInfo($batchId);
        }
    }

    /**
     * Mostrar barra de progresso
     */
    private function displayProgressBar(array $stats): void
    {
        $percentage = $stats['progress_percentage'];
        $filled = (int) ($percentage / 5);
        $empty = 20 - $filled;
        
        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);
        
        $this->line("Progresso: {$bar} {$percentage}%");
        $this->newLine();
    }

    /**
     * Mostrar estimativas do batch
     */
    private function showBatchEstimates(array $stats): void
    {
        $this->info("💰 Estimativas:");
        
        // Custos
        $costSpent = round($stats['completed'] * 0.04, 2);
        $costRemaining = round($stats['pending'] * 0.04, 2);
        $this->line("  • Custo total: ~\${$stats['estimated_cost']} USD");
        $this->line("  • Já gasto: ~\${$costSpent} USD");
        $this->line("  • Restante: ~\${$costRemaining} USD");
        
        // Tempo
        $minutesRemaining = $stats['pending'] * 2;
        $timeRemaining = $this->formatTime($minutesRemaining);
        $this->line("  • Tempo restante: ~{$timeRemaining} (com rate limiting)");
        
        // Taxa de sucesso
        if ($stats['completed'] + $stats['failed'] > 0) {
            $successRate = round(($stats['completed'] / ($stats['completed'] + $stats['failed'])) * 100, 1);
            $this->line("  • Taxa de sucesso: {$successRate}%");
        }
        
        $this->newLine();
    }

    /**
     * Mostrar informações detalhadas do batch
     */
    private function showDetailedBatchInfo(string $batchId): void
    {
        $this->info("📋 Detalhes do Batch:");
        
        // Últimos artigos processados
        $recent = TirePressureArticle::where('refinement_batch_id', $batchId)
                                    ->where('refinement_status', 'completed')
                                    ->orderBy('refinement_completed_at', 'desc')
                                    ->limit(5)
                                    ->get();
        
        if ($recent->isNotEmpty()) {
            $this->line("\nÚltimos artigos refinados:");
            foreach ($recent as $article) {
                $time = $article->refinement_completed_at->diffForHumans();
                $vehicle = $article->vehicle_data['vehicle_full_name'] ?? 'N/A';
                $this->line("  • {$vehicle} - {$time}");
            }
        }
        
        // Artigos com falha
        $failed = TirePressureArticle::where('refinement_batch_id', $batchId)
                                    ->where('refinement_status', 'failed')
                                    ->limit(5)
                                    ->get();
        
        if ($failed->isNotEmpty()) {
            $this->line("\nArtigos com falha:");
            foreach ($failed as $article) {
                $vehicle = $article->vehicle_data['vehicle_full_name'] ?? 'N/A';
                $attempts = $article->refinement_attempts ?? 0;
                $this->line("  • {$vehicle} - {$attempts} tentativas");
            }
        }
        
        // Distribuição por template
        $this->showTemplateDistribution($batchId);
        
        // Distribuição por marca
        $this->showMakeDistribution($batchId);
    }

    /**
     * Mostrar distribuição por template
     */
    private function showTemplateDistribution(string $batchId): void
    {
        $distribution = TirePressureArticle::where('refinement_batch_id', $batchId)
            ->selectRaw('template_type, COUNT(*) as count')
            ->groupBy('template_type')
            ->get();
        
        if ($distribution->isNotEmpty()) {
            $this->line("\nDistribuição por template:");
            foreach ($distribution as $item) {
                $this->line("  • {$item->template_type}: {$item->count} artigos");
            }
        }
    }

    /**
     * Mostrar distribuição por marca
     */
    private function showMakeDistribution(string $batchId): void
    {
        $distribution = TirePressureArticle::where('refinement_batch_id', $batchId)
            ->selectRaw('vehicle_data.make as make, COUNT(*) as count')
            ->groupBy('vehicle_data.make')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        if ($distribution->isNotEmpty()) {
            $this->line("\nTop 10 marcas no batch:");
            foreach ($distribution as $item) {
                $this->line("  • {$item->make}: {$item->count} artigos");
            }
        }
    }

    /**
     * Listar múltiplos batches
     */
    private function listBatches(bool $activeOnly, bool $detailed): void
    {
        $this->info("📦 Listagem de Batches\n");
        
        // Buscar todos os batches únicos
        $batches = TirePressureArticle::whereNotNull('refinement_batch_id')
            ->distinct('refinement_batch_id')
            ->pluck('refinement_batch_id');
        
        if ($batches->isEmpty()) {
            $this->warn("Nenhum batch encontrado");
            return;
        }
        
        $batchData = [];
        
        foreach ($batches as $batchId) {
            $stats = TirePressureArticle::getBatchStats($batchId);
            
            // Filtrar apenas ativos se solicitado
            if ($activeOnly && $stats['pending'] === 0) {
                continue;
            }
            
            $batchData[] = [
                'id' => \Str::limit($batchId, 20),
                'total' => $stats['total'],
                'progress' => $stats['progress_percentage'] . '%',
                'status' => $this->getBatchStatus($stats),
                'cost' => number_format($stats['estimated_cost'], 2)
            ];
        }
        
        if (empty($batchData)) {
            $this->warn("Nenhum batch ativo encontrado");
            return;
        }
        
        // Ordenar por progresso
        usort($batchData, function($a, $b) {
            return $b['progress'] <=> $a['progress'];
        });
        
        $this->table(
            ['Batch ID', 'Total', 'Progresso', 'Status', 'Custo Est.'],
            $batchData
        );
        
        if ($detailed) {
            $this->newLine();
            foreach ($batches as $batchId) {
                $this->showBatchDetails($batchId, false);
                $this->newLine();
            }
        }
    }

    /**
     * Determinar status do batch
     */
    private function getBatchStatus(array $stats): string
    {
        if ($stats['progress_percentage'] == 100) {
            return '✅ Completo';
        } elseif ($stats['processing'] > 0) {
            return '🔄 Processando';
        } elseif ($stats['failed'] > 0 && $stats['pending'] === 0) {
            return '⚠️ Com Falhas';
        } elseif ($stats['pending'] > 0) {
            return '⏳ Ativo';
        }
        
        return '❓ Indeterminado';
    }

    /**
     * Mostrar resumo geral
     */
    private function showGeneralSummary(): void
    {
        $this->info("📊 Resumo Geral do Sistema de Refinamento\n");
        
        // Total de artigos
        $totalArticles = TirePressureArticle::where('vehicle_data_version', 'v3.1')->count();
        $refinedArticles = TirePressureArticle::where('sections_refinement_version', 'v2.0')->count();
        $pendingArticles = TirePressureArticle::readyForRefinement()->count();
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['📄 Total de Artigos (v3.1)', number_format($totalArticles)],
                ['✅ Artigos Refinados', number_format($refinedArticles)],
                ['⏳ Artigos Pendentes', number_format($pendingArticles)],
                ['📈 Progresso Total', $this->formatPercentage($refinedArticles, $totalArticles)]
            ]
        );
        
        // Batches ativos
        $activeBatches = $this->getActiveBatchesCount();
        $this->line("\n🔄 Batches ativos: {$activeBatches}");
        
        // Estimativas gerais
        if ($pendingArticles > 0) {
            $this->newLine();
            $this->info("💰 Estimativas para artigos pendentes:");
            $this->line("  • Custo: ~$" . number_format($pendingArticles * 0.04, 2) . " USD");
            $this->line("  • Tempo: ~" . $this->formatTime($pendingArticles * 2));
        }
    }

    /**
     * Mostrar estatísticas diárias
     */
    private function showDailyStats(): void
    {
        $statsKey = 'tire_pressure_refinement_stats_' . now()->format('Y-m-d');
        $stats = Cache::get($statsKey);
        
        if (!$stats || $stats['total'] === 0) {
            return;
        }
        
        $this->newLine();
        $this->info("📅 Estatísticas de Hoje:");
        
        $avgDuration = $stats['total'] > 0 ? round($stats['total_duration'] / $stats['total'], 2) : 0;
        $successRate = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0;
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['✅ Refinamentos com sucesso', $stats['success']],
                ['❌ Falhas', $stats['failed']],
                ['⏱️ Tempo médio', "{$avgDuration}s"],
                ['📈 Taxa de sucesso', "{$successRate}%"],
                ['💰 Custo estimado hoje' . number_format($stats['success'] * 0.04, 2)]
            ]
        );
        
        // Top templates processados
        if (!empty($stats['templates'])) {
            $this->line("\nTemplates processados hoje:");
            foreach ($stats['templates'] as $template => $count) {
                $this->line("  • {$template}: {$count}");
            }
        }
    }

    /**
     * Contar batches ativos
     */
    private function getActiveBatchesCount(): int
    {
        return TirePressureArticle::whereNotNull('refinement_batch_id')
            ->where('refinement_status', 'pending')
            ->distinct('refinement_batch_id')
            ->count('refinement_batch_id');
    }

    /**
     * Formatar percentual
     */
    private function formatPercentage(int $value, int $total): string
    {
        if ($total === 0) return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }

    /**
     * Formatar tempo
     */
    private function formatTime(float $minutes): string
    {
        if ($minutes < 60) {
            return round($minutes) . " minutos";
        }
        
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        
        if ($hours < 24) {
            return "{$hours}h {$mins}min";
        }
        
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        
        return "{$days} dias, {$remainingHours}h";
    }
}