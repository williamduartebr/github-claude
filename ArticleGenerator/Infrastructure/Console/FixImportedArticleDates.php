<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;


class FixImportedArticleDates extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:fix-imported-dates
                           {--dry-run : Simular a execução sem fazer alterações}
                           {--only-published : Corrigir apenas artigos com status published}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Corrige as datas de criação e publicação dos artigos importados';

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando correção de datas para artigos importados...');

        // Buscar artigos temporários com original_post_id
        $tempArticles = TempArticle::whereNotNull('original_post_id')
            ->whereNotNull('published_at')
            ->get(['_id', 'original_post_id', 'published_at']);

        if ($tempArticles->isEmpty()) {
            $this->warn('Nenhum artigo temporário importado encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$tempArticles->count()} artigos temporários importados.");

        $bar = $this->getOutput()->isVerbose() ? null : $this->output->createProgressBar($tempArticles->count());
        if ($bar) $bar->start();

        $updated = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($tempArticles as $tempArticle) {
            // Buscar o artigo correspondente no MongoDB
            $query = Article::where('original_post_id', $tempArticle->original_post_id);

            // Se solicitado, filtra apenas artigos publicados
            if ($this->option('only-published')) {
                $query->where('status', 'published');
            }

            $article = $query->first();

            if (!$article) {
                if ($this->getOutput()->isVerbose()) {
                    $this->line("Artigo para original_post_id {$tempArticle->original_post_id} não encontrado. Pulando...");
                }
                $skipped++;
                if ($bar) $bar->advance();
                continue;
            }

            // Log detalhado se verbose estiver ativado
            if ($this->getOutput()->isVerbose()) {
                $this->line("Artigo encontrado: {$article->_id} ({$article->title})");
                $this->line("  Data atual published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'NULL'));
                $this->line("  Data atual created_at: " . ($article->created_at ? $article->created_at->format('Y-m-d H:i:s') : 'NULL'));
                $this->line("  Nova data de published_at/created_at: " . $tempArticle->published_at->format('Y-m-d H:i:s'));
            }

            try {
                // Se for apenas simulação, não faz a atualização
                if (!$this->option('dry-run')) {
                    Article::find($article->_id)
                        ->update([
                            'published_at' => $tempArticle->published_at,
                            'created_at' => $tempArticle->published_at,
                        ]);
                }

                $updated++;

                if ($this->getOutput()->isVerbose()) {
                    $this->info("  ✓ Datas atualizadas com sucesso!");
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->getOutput()->isVerbose()) {
                    $this->error("  ✗ Erro ao atualizar datas: " . $e->getMessage());
                }
            }

            if ($bar) $bar->advance();
        }

        if ($bar) {
            $bar->finish();
            $this->newLine(2);
        }

        // Resumo da operação
        $this->info("Operação concluída!");
        if ($this->option('dry-run')) {
            $this->line("Modo simulação: nenhuma alteração foi feita.");
        }
        $this->info("Artigos que seriam atualizados: {$updated}");

        if ($skipped > 0) {
            $this->line("Artigos ignorados (não encontrados): {$skipped}");
        }

        if ($errors > 0) {
            $this->error("Erros encontrados: {$errors}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
