<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class CleanupTireScheduleTicker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticker:cleanup-tire 
                            {--dry-run : Executa sem deletar para visualizar os registros}
                            {--batch-size=100 : Tamanho do lote para processamento}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove todos os registros TempArticle com domain = when_to_change_tires';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        $this->info('🚀 Iniciando ticker de limpeza...');
        
        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: Nenhum registro será deletado');
        }

        // Conta total de registros a serem processados
        $totalCount = TempArticle::where('domain', 'when_to_change_tires')->count();
        
        if ($totalCount === 0) {
            $this->info('✅ Nenhum registro encontrado com domain = "when_to_change_tires"');
            return Command::SUCCESS;
        }

        $this->info("📊 Total de registros encontrados: {$totalCount}");
        
        if ($dryRun) {
            // Mostra uma amostra dos registros que seriam deletados
            $sampleRecords = TempArticle::where('domain', 'when_to_change_tires')
                ->take(5)
                ->get(['_id', 'source', 'created_at']);
                
            $this->table(
                ['ID', 'Source', 'Created At'],
                $sampleRecords->map(function ($record) {
                    return [
                        $record->_id,
                        $record->source,
                        $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A'
                    ];
                })->toArray()
            );
            
            if ($totalCount > 5) {
                $this->info("... e mais " . ($totalCount - 5) . " registros");
            }
            
            return Command::SUCCESS;
        }

        // Confirma a exclusão
        if (!$this->confirm("Tem certeza que deseja deletar {$totalCount} registros?")) {
            $this->info('❌ Operação cancelada pelo usuário');
            return Command::FAILURE;
        }

        $deletedCount = 0;
        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        // Processa em lotes para evitar problemas de memória
        do {
            $records = TempArticle::where('domain', 'when_to_change_tires')
                ->take($batchSize)
                ->get();

            if ($records->isEmpty()) {
                break;
            }

            $batchDeleted = 0;
            foreach ($records as $record) {
                try {
                    $record->delete();
                    $batchDeleted++;
                    $deletedCount++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $this->error("Erro ao deletar registro {$record->_id}: " . $e->getMessage());
                }
            }

            // Pequena pausa para não sobrecarregar o banco
            usleep(100000); // 0.1 segundo

        } while ($records->count() === $batchSize);

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Ticker finalizado com sucesso!");
        $this->info("🗑️  Total de registros deletados: {$deletedCount}");
        
        // Verifica se ainda restam registros
        $remainingCount = TempArticle::where('domain', 'when_to_change_tires')->count();
        if ($remainingCount > 0) {
            $this->warn("⚠️  Ainda restam {$remainingCount} registros com domain = 'when_to_change_tires'");
        }

        return Command::SUCCESS;
    }
}