<?php

namespace Src\ContentGeneration\TirePressureGuide\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TirePressureGuide\Domain\Entities\TirePressureArticle;

/**
 * Command para criar batches de refinamento
 * 
 * Permite criar lotes de artigos para processamento controlado
 * com filtros por template, marca, prioridade, etc.
 */
class CreateRefinementBatchCommand extends Command
{
    protected $signature = 'tire-pressure:create-batch 
                           {--size=100 : Tamanho do batch}
                           {--template= : Filtrar por template (ideal/calibration)}
                           {--make= : Filtrar por marca}
                           {--priority= : Prioridade mínima (1-10)}
                           {--dry-run : Preview sem criar}
                           {--auto-process : Iniciar processamento após criar}';

    protected $description = 'Criar batch de artigos para refinamento de seções';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $size = (int) $this->option('size');
        $template = $this->option('template');
        $make = $this->option('make');
        $priority = $this->option('priority');
        $isDryRun = $this->option('dry-run');
        $autoProcess = $this->option('auto-process');

        $this->info("🚀 Criando batch de refinamento...\n");

        try {
            // Validar opções
            if ($template && !in_array($template, ['ideal', 'calibration'])) {
                $this->error("Template inválido. Use 'ideal' ou 'calibration'");
                return 1;
            }

            // Construir filtros
            $filters = array_filter([
                'template' => $template,
                'make' => $make,
                'priority' => $priority ? (int)$priority : null
            ]);

            // Mostrar filtros aplicados
            if (!empty($filters)) {
                $this->info("Filtros aplicados:");
                foreach ($filters as $key => $value) {
                    $this->line("  • {$key}: {$value}");
                }
                $this->newLine();
            }

            // Verificar artigos disponíveis
            $availableCount = $this->getAvailableArticlesCount($filters);
            
            if ($availableCount === 0) {
                $this->warn("⚠️  Nenhum artigo disponível para refinamento com os filtros aplicados.");
                $this->suggestAlternatives();
                return 0;
            }

            $this->info("📊 Artigos disponíveis: {$availableCount}");
            $actualSize = min($size, $availableCount);
            
            if ($actualSize < $size) {
                $this->warn("⚠️  Tamanho do batch ajustado para {$actualSize} (máximo disponível)");
            }

            // Preview do batch
            $this->showBatchPreview($actualSize, $filters);

            if ($isDryRun) {
                $this->newLine();
                $this->comment("🔍 Modo DRY RUN - Nenhum batch foi criado");
                return 0;
            }

            // Confirmar criação
            if (!$this->confirm("Criar batch com {$actualSize} artigos?")) {
                $this->comment("Operação cancelada");
                return 0;
            }

            // Criar o batch
            $batchId = TirePressureArticle::createRefinementBatch($actualSize, $filters);

            if (!$batchId) {
                $this->error("❌ Erro ao criar batch");
                return 1;
            }

            // Mostrar resultado
            $this->newLine();
            $this->info("✅ Batch criado com sucesso!");
            $this->line("📦 Batch ID: {$batchId}");
            $this->line("📊 Tamanho: {$actualSize} artigos");
            
            // Estatísticas do batch
            $stats = TirePressureArticle::getBatchStats($batchId);
            $this->showBatchStats($stats);

            // Estimativas
            $this->newLine();
            $this->info("💰 Estimativas:");
            $this->line("  • Custo: ~\${$stats['estimated_cost']} USD");
            $this->line("  • Tokens: ~" . number_format($stats['estimated_tokens']));
            $this->line("  • Tempo: ~" . $this->estimateProcessingTime($actualSize) . " (com rate limiting)");

            // Próximos passos
            $this->showNextSteps($batchId, $autoProcess);

            // Auto processar se solicitado
            if ($autoProcess) {
                $this->newLine();
                $this->info("🤖 Iniciando processamento automático...");
                $this->call('tire-pressure:process-batch', [
                    'batch' => $batchId,
                    '--limit' => 5
                ]);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            Log::error("Erro ao criar batch", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Contar artigos disponíveis
     */
    private function getAvailableArticlesCount(array $filters): int
    {
        $query = TirePressureArticle::readyForRefinement()
                                   ->whereNull('refinement_batch_id');

        if (!empty($filters['template'])) {
            $query->byTemplate($filters['template']);
        }
        if (!empty($filters['make'])) {
            $query->byMake($filters['make']);
        }
        if (!empty($filters['priority'])) {
            $query->where('refinement_priority', '>=', $filters['priority']);
        }

        return $query->count();
    }

    /**
     * Mostrar preview do batch
     */
    private function showBatchPreview(int $size, array $filters): void
    {
        $this->newLine();
        $this->info("📋 Preview do Batch:");
        
        // Buscar amostra
        $query = TirePressureArticle::readyForRefinement()
                                   ->whereNull('refinement_batch_id');

        if (!empty($filters['template'])) {
            $query->byTemplate($filters['template']);
        }
        if (!empty($filters['make'])) {
            $query->byMake($filters['make']);
        }

        $sample = $query->limit(5)->get();

        if ($sample->isEmpty()) {
            $this->warn("Nenhum artigo encontrado para preview");
            return;
        }

        $this->table(
            ['Veículo', 'Template', 'Slug', 'vehicle_data v3.1'],
            $sample->map(function ($article) {
                return [
                    $article->vehicle_data['vehicle_full_name'] ?? 'N/A',
                    $article->template_type,
                    \Str::limit($article->slug, 40),
                    $article->vehicle_data_version === 'v3.1' ? '✅' : '❌'
                ];
            })
        );
    }

    /**
     * Mostrar estatísticas do batch
     */
    private function showBatchStats(array $stats): void
    {
        $this->newLine();
        $this->info("📊 Estatísticas do Batch:");
        
        $this->table(
            ['Status', 'Quantidade', 'Percentual'],
            [
                ['Pendente', $stats['pending'], $this->formatPercentage($stats['pending'], $stats['total'])],
                ['Processando', $stats['processing'], $this->formatPercentage($stats['processing'], $stats['total'])],
                ['Concluído', $stats['completed'], $this->formatPercentage($stats['completed'], $stats['total'])],
                ['Falha', $stats['failed'], $this->formatPercentage($stats['failed'], $stats['total'])],
                ['Total', $stats['total'], '100%']
            ]
        );
    }

    /**
     * Estimar tempo de processamento
     */
    private function estimateProcessingTime(int $size): string
    {
        // 2 minutos por artigo (rate limiting Claude 3.5 Sonnet)
        $minutes = $size * 2;
        
        if ($minutes < 60) {
            return "{$minutes} minutos";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours < 24) {
            return "{$hours}h {$remainingMinutes}min";
        }
        
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        
        return "{$days} dias, {$remainingHours}h";
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
     * Sugerir alternativas quando não há artigos
     */
    private function suggestAlternatives(): void
    {
        $this->newLine();
        $this->info("💡 Sugestões:");
        
        // Verificar pré-requisitos
        $withoutV31 = TirePressureArticle::where('generation_status', 'generated')
                                         ->where('vehicle_data_version', '!=', 'v3.1')
                                         ->count();
        
        if ($withoutV31 > 0) {
            $this->line("  • {$withoutV31} artigos precisam da correção do vehicle_data primeiro");
            $this->line("    Execute: php artisan tire-pressure:correct-vehicle-data");
        }
        
        // Verificar batches existentes
        $inBatches = TirePressureArticle::whereNotNull('refinement_batch_id')
                                        ->where('refinement_status', '!=', 'completed')
                                        ->count();
        
        if ($inBatches > 0) {
            $this->line("  • {$inBatches} artigos já estão em outros batches");
            $this->line("    Execute: php artisan tire-pressure:list-batches");
        }
        
        // Verificar já refinados
        $refined = TirePressureArticle::where('sections_refinement_version', 'v2.0')->count();
        
        if ($refined > 0) {
            $this->line("  • {$refined} artigos já foram refinados");
        }
    }

    /**
     * Mostrar próximos passos
     */
    private function showNextSteps(string $batchId, bool $autoProcess): void
    {
        if ($autoProcess) {
            return;
        }

        $this->newLine();
        $this->info("📝 Próximos passos:");
        
        $this->line("1. Processar o batch:");
        $this->line("   php artisan tire-pressure:process-batch {$batchId}");
        
        $this->line("\n2. Monitorar progresso:");
        $this->line("   php artisan tire-pressure:batch-status {$batchId}");
        
        $this->line("\n3. Ver todos os batches:");
        $this->line("   php artisan tire-pressure:list-batches");
        
        $this->line("\n4. Processar com schedule automático:");
        $this->line("   php artisan schedule:work");
    }
}