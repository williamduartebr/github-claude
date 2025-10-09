<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\PriceCorrectionService;


class FixPricesCommand extends Command
{
    protected $signature = 'review-schedule:fix-prices 
                          {--limit=50 : Limite de artigos para processar}
                          {--process : Processar correções pendentes}
                          {--stats : Mostrar estatísticas}
                          {--all : Criar correções para TODOS os artigos}
                          {--force : Não pedir confirmação}';
    
    protected $description = 'Corrige preços de revisões usando Claude API';

    protected $priceService;

    public function __construct(PriceCorrectionService $priceService)
    {
        parent::__construct();
        $this->priceService = $priceService;
    }

    public function handle(): int
    {
        $limit = $this->option('limit');

        if ($this->option('stats')) {
            return $this->showStats();
        }

        if ($this->option('process')) {
            return $this->processCorrections($limit);
        }

        if ($this->option('all')) {
            return $this->createAllCorrections($limit);
        }

        $this->info('ℹ️  Use uma das opções:');
        $this->line('  --stats     : Ver estatísticas');
        $this->line('  --all       : Criar correções para todos os artigos');
        $this->line('  --process   : Processar correções pendentes');

        return self::SUCCESS;
    }

    private function showStats(): int
    {
        $this->info('📊 ESTATÍSTICAS DE CORREÇÕES');
        $this->newLine();

        $stats = $this->priceService->getStats();

        $this->table(
            ['Status', 'Quantidade'],
            [
                ['Pendentes', $stats['pending']],
                ['Processando', $stats['processing']],
                ['Concluídas', $stats['completed']],
                ['Falhadas', $stats['failed']],
                ['Total', array_sum($stats)]
            ]
        );

        return self::SUCCESS;
    }

    private function createAllCorrections(int $limit): int
    {
        $this->info("🚀 Criando correções para TODOS os artigos...");

        $allSlugs = $this->priceService->getAllArticleSlugs($limit);

        if (empty($allSlugs)) {
            $this->info('❌ Nenhum artigo encontrado.');
            return self::SUCCESS;
        }

        $this->warn("📊 Encontrados " . count($allSlugs) . " artigos");
        $this->info("💰 Custo estimado: ~$" . round(count($allSlugs) * 0.00175, 2) . " USD");

        if (!$this->option('force') && !$this->confirm('Criar correções para todos os artigos?')) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        $results = $this->priceService->createCorrectionsForSlugs($allSlugs);

        $this->newLine();
        $this->info("✅ Criação concluída!");
        $this->info("📊 Criadas: {$results['created']}");
        $this->info("📊 Puladas: {$results['skipped']}");
        
        if ($results['errors'] > 0) {
            $this->warn("⚠️  Erros: {$results['errors']}");
        }

        if ($results['created'] > 0) {
            $this->newLine();
            $this->info('💡 PRÓXIMO PASSO:');
            $this->line("   php artisan review-schedule:fix-prices --process --limit={$results['created']}");
        }

        return self::SUCCESS;
    }

    private function processCorrections(int $limit): int
    {
        $this->info("⚡ Processando correções pendentes...");

        $results = $this->priceService->processAllPendingCorrections($limit);

        if ($results['processed'] === 0) {
            $this->info('✅ Nenhuma correção pendente encontrada!');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("✅ Processamento concluído!");
        $this->info("📊 Processadas: {$results['processed']}");
        $this->info("📊 Sucessos: {$results['successful']}");
        
        if ($results['failed'] > 0) {
            $this->warn("⚠️  Falhas: {$results['failed']}");
        }

        $successRate = round(($results['successful'] / $results['processed']) * 100, 1);
        $this->info("📈 Taxa de sucesso: {$successRate}%");

        return self::SUCCESS;
    }
}