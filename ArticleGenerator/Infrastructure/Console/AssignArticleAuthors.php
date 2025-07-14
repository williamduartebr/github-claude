<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;

class AssignArticleAuthors extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:assign-authors 
                           {--imported-only : Processar apenas artigos importados (com original_post_id)}
                           {--new-only : Processar apenas artigos gerados (sem original_post_id)}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Atribui autores aos artigos com base em seu tipo (importado ou gerado)';


    /**
     * Lista de autores possíveis para artigos
     * 
     * @var array
     */
    protected $authors = [
        // Autores para artigos importados
        'imported' => [
            'William Duarte' => 'Entusiasta automotivo e mecânica automotiva',
            'Marley Rondon' => 'Especialista em veículos e mecânica automotiva',
        ],

        // Autores para novos artigos
        'new' => [
            'Equipe Editorial' => 'Equipe especializada em conteúdo automotivo',
            'Departamento Técnico' => 'Engenheiros e mecânicos especializados',
            'Redação' => 'Editores especialistas em veículos',
            'Equipe de Conteúdo' => 'Especialistas em informação automotiva'
        ]
    ];

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando atribuição de autores aos artigos...');

        // Determinar quais artigos processar
        $query = Article::query();
        
        if ($this->option('imported-only')) {
            $query->whereNotNull('original_post_id');
            $authorPool = $this->authors['imported'];
            $this->info('Processando apenas artigos importados.');
        } elseif ($this->option('new-only')) {
            $query->whereNull('original_post_id');
            $authorPool = $this->authors['new'];
            $this->info('Processando apenas artigos novos.');
        } else {
            // Processa ambos os tipos, mas com autores diferentes
            $this->info('Processando todos os artigos.');
        }
        
        // Contar todos os artigos para a barra de progresso
        $articlesCount = $query->count();
        
        if ($articlesCount === 0) {
            $this->warn('Nenhum artigo encontrado para processar.');
            return Command::SUCCESS;
        }
        
        $this->info("Encontrados {$articlesCount} artigos para processar.");
        
        $bar = $this->output->createProgressBar($articlesCount);
        $bar->start();
        
        $processed = 0;
        
        // Processar artigos em lotes para evitar problemas de memória
        $perPage = 100;
        $page = 1;
        
        do {
            $articles = $query->forPage($page, $perPage)->get();
            
            if ($articles->isEmpty()) {
                break;
            }
            
            foreach ($articles as $article) {
                // Determinar o pool de autores com base no tipo do artigo
                if (isset($authorPool)) {
                    $pool = $authorPool;
                } else {
                    $pool = empty($article->original_post_id) 
                        ? $this->authors['new'] 
                        : $this->authors['imported'];
                }
                
                // Selecionar um autor aleatório
                $authorNames = array_keys($pool);
                $authorName = $authorNames[array_rand($authorNames)];
                $authorBio = $pool[$authorName];
                
                // Atribuir ao artigo
                   Article::find($article->_id)
                    ->update([
                        'author' => [
                            'name' => $authorName,
                            'bio' => $authorBio
                        ]
                    ]);
                
                $processed++;
                $bar->advance();
            }
            
            // Limpar a memória
            $articles = null;
            gc_collect_cycles();
            
            // Avançar para a próxima página
            $page++;
            
        } while (true);
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Concluído! {$processed} artigos foram atualizados com atribuição de autores.");
        
        return Command::SUCCESS;
    }
}
