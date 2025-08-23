<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Src\ArticleGenerator\Infrastructure\Services\RelatedArticlesService;


class UpdateRelatedArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:update-related';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Atualiza os artigos relacionados para todos os artigos publicados';

    /**
     * Execute o comando.
     *
     * @param RelatedArticlesService $relatedArticlesService
     * @return int
     */
    public function handle(RelatedArticlesService $relatedArticlesService)
    {
        $this->info('Iniciando atualização de artigos relacionados...');

        $updatedCount = $relatedArticlesService->updateAllRelatedArticles();

        $this->info("Processo concluído. {$updatedCount} artigos foram atualizados com tópicos relacionados.");

        return Command::SUCCESS;
    }
}
