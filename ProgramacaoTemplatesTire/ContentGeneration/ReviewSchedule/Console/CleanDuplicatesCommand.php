<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\PriceCorrectionService;

class CleanDuplicatesCommand extends Command
{
    protected $signature = 'review-schedule:clean-duplicates 
                          {--dry-run : Apenas mostrar o que seria removido, sem executar}
                          {--force : Executar limpeza sem confirmação}';
    
    protected $description = 'Remove correções duplicadas mantendo apenas uma por artigo';

    protected $priceService;

    public function __construct(PriceCorrectionService $priceService)
    {
        parent::__construct();
        $this->priceService = $priceService;
    }

    public function handle(): int
    {
        $this->info('🧹 LIMPEZA DE CORREÇÕES DUPLICADAS');
        $this->newLine();

        if ($this->option('dry-run')) {
            return $this->dryRun();
        }

        if (!$this->option('force') && !$this->confirm('⚠️  Isso vai DELETAR correções duplicadas. Continuar?')) {
            $this->info('❌ Operação cancelada.');
            return self::SUCCESS;
        }

        $this->info('🚀 Executando limpeza...');
        $this->newLine();

        $results = $this->priceService->cleanAllDuplicates();

        $this->displayResults($results);

        return self::SUCCESS;
    }

    private function dryRun(): int
    {
        $this->warn('🔍 MODO DRY-RUN - Apenas análise, nada será deletado');
        $this->newLine();

        // 🔧 CORREÇÃO MONGODB: Usar agregação para encontrar duplicatas
        $duplicateArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::raw(function($collection) {
            return $collection->aggregate([
                [
                    '$match' => ['correction_type' => 'price_correction']
                ],
                [
                    '$group' => [
                        '_id' => '$article_slug',
                        'count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$match' => ['count' => ['$gt' => 1]]
                ],
                [
                    '$project' => [
                        'article_slug' => '$_id',
                        'count' => 1,
                        '_id' => 0
                    ]
                ]
            ]);
        });

        $totalDuplicates = 0;
        $this->info('📦 Artigos com duplicatas encontrados:');
        
        foreach ($duplicateArticles as $article) {
            $corrections = \Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection::where('article_slug', $article['article_slug'])
                ->where('correction_type', 'price_correction')
                ->orderBy('created_at', 'desc')
                ->get();

            $duplicateCount = $corrections->count() - 1; // -1 porque vai manter 1
            $totalDuplicates += $duplicateCount;

            $this->line("  📄 {$article['article_slug']}: {$corrections->count()} correções (remover {$duplicateCount})");
        }

        $this->newLine();
        $this->info("📊 RESUMO DRY-RUN:");
        $this->info("   Artigos com duplicatas: " . count($duplicateArticles));
        $this->info("   Total de duplicatas para remover: {$totalDuplicates}");
        $this->newLine();
        $this->info("💡 Para executar a limpeza real:");
        $this->line("   php artisan review-schedule:clean-duplicates --force");

        return self::SUCCESS;
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('✅ LIMPEZA CONCLUÍDA!');
        $this->newLine();

        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Artigos analisados', $results['articles_analyzed']],
                ['Artigos com duplicatas', $results['duplicates_found']],
                ['Correções removidas', $results['corrections_removed']],
            ]
        );

        if (!empty($results['articles_cleaned'])) {
            $this->newLine();
            $this->info('📋 Artigos limpos:');
            foreach ($results['articles_cleaned'] as $slug) {
                $this->line("  ✓ {$slug}");
            }
        }

        $this->newLine();
        $this->info('💡 Agora cada artigo tem apenas UMA correção definitiva!');
    }
}