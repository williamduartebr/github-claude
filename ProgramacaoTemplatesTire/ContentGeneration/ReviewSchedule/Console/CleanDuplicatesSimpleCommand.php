<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class CleanDuplicatesSimpleCommand extends Command
{
    protected $signature = 'review-schedule:clean-duplicates-simple 
                          {--dry-run : Apenas mostrar o que seria removido}
                          {--force : Executar limpeza sem confirmação}';
    
    protected $description = 'Remove correções duplicadas - versão que REALMENTE funciona com MongoDB';

    public function handle(): int
    {
        $this->info('🧹 LIMPEZA DE CORREÇÕES DUPLICADAS (MongoDB Real)');
        $this->newLine();

        if ($this->option('dry-run')) {
            return $this->dryRun();
        }

        if (!$this->option('force') && !$this->confirm('⚠️  Isso vai DELETAR correções duplicadas. Continuar?')) {
            $this->info('❌ Operação cancelada.');
            return self::SUCCESS;
        }

        return $this->executeCleanup();
    }

    private function dryRun(): int
    {
        $this->warn('🔍 MODO DRY-RUN - Análise REAL das duplicatas');
        $this->newLine();

        $results = $this->findDuplicatesCorrectly(true);
        $this->displayResults($results, true);

        return self::SUCCESS;
    }

    private function executeCleanup(): int
    {
        $this->info('🚀 Executando limpeza REAL...');
        $this->newLine();

        $results = $this->findDuplicatesCorrectly(false);
        $this->displayResults($results, false);

        return self::SUCCESS;
    }

    /**
     * 🎯 MÉTODO CORRETO: Busca duplicatas de verdade
     */
    private function findDuplicatesCorrectly(bool $dryRun): array
    {
        $results = [
            'total_corrections' => 0,
            'articles_processed' => 0,
            'articles_with_duplicates' => 0,
            'duplicates_removed' => 0,
            'cleaned_articles' => []
        ];

        // Contar total de correções
        $results['total_corrections'] = ArticleCorrection::where('correction_type', 'price_correction')->count();

        // 🔧 BUSCAR DUPLICATAS DE FORMA SIMPLES E EFICAZ
        // Agrupa por slug e conta, mas usando Collection ao invés de SQL
        $allCorrections = ArticleCorrection::where('correction_type', 'price_correction')
            ->get(['article_slug', 'created_at', '_id'])
            ->groupBy('article_slug');

        $this->info("🔍 Analisando " . $allCorrections->count() . " grupos de artigos...");
        $progressBar = $this->output->createProgressBar($allCorrections->count());

        foreach ($allCorrections as $slug => $corrections) {
            $results['articles_processed']++;
            
            if ($corrections->count() > 1) {
                $results['articles_with_duplicates']++;
                
                // Ordenar por created_at descendente (mais recente primeiro)
                $sortedCorrections = $corrections->sortByDesc('created_at');
                
                // Manter o primeiro (mais recente)
                $toKeep = $sortedCorrections->first();
                $toDelete = $sortedCorrections->skip(1);

                $this->line(""); // Nova linha para não bagunçar o progress bar
                $this->line("📦 {$slug}: {$corrections->count()} correções, removendo {$toDelete->count()}");

                if (!$dryRun) {
                    // Deletar as duplicatas do banco
                    foreach ($toDelete as $duplicate) {
                        ArticleCorrection::where('_id', $duplicate['_id'])->delete();
                        $results['duplicates_removed']++;
                    }
                } else {
                    $results['duplicates_removed'] += $toDelete->count();
                }

                $results['cleaned_articles'][] = [
                    'slug' => $slug,
                    'total_found' => $corrections->count(),
                    'removed' => $toDelete->count(),
                    'kept_id' => $toKeep['_id']
                ];
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        return $results;
    }

    private function displayResults(array $results, bool $isDryRun): void
    {
        $this->info($isDryRun ? '📊 RESULTADO DA ANÁLISE REAL:' : '✅ LIMPEZA EXECUTADA!');
        $this->newLine();

        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Total de correções no banco', $results['total_corrections']],
                ['Artigos processados', $results['articles_processed']],
                ['Artigos com duplicatas', $results['articles_with_duplicates']],
                [$isDryRun ? 'Duplicatas para remover' : 'Duplicatas removidas', $results['duplicates_removed']],
            ]
        );

        if (!empty($results['cleaned_articles'])) {
            $this->newLine();
            $this->info('📋 Primeiros 15 artigos com duplicatas:');
            
            foreach (array_slice($results['cleaned_articles'], 0, 15) as $article) {
                $action = $isDryRun ? 'removeria' : 'removeu';
                $this->line("  📄 {$article['slug']}: {$action} {$article['removed']} de {$article['total_found']} correções");
            }

            if (count($results['cleaned_articles']) > 15) {
                $remaining = count($results['cleaned_articles']) - 15;
                $this->line("  📄 ... e mais {$remaining} artigos com duplicatas");
            }
        }

        if ($isDryRun && $results['duplicates_removed'] > 0) {
            $this->newLine();
            $this->info('💡 Para executar a limpeza real:');
            $this->line('   php artisan review-schedule:clean-duplicates-simple --force');
        } elseif (!$isDryRun && $results['duplicates_removed'] > 0) {
            $this->newLine();
            $this->info('🎉 Limpeza concluída! Sistema agora tem apenas UMA correção por artigo!');
            $this->info("💾 {$results['duplicates_removed']} correções duplicadas foram removidas");
        } elseif ($results['articles_with_duplicates'] === 0) {
            $this->newLine();
            $this->info('✨ Nenhuma duplicata encontrada! Sistema já está limpo!');
        }
    }
}