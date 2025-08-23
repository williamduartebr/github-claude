<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Services\VehicleSyncService;


class SyncVehiclesToMySQL extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:sync-vehicles 
                           {--create-tables : Criar as tabelas MySQL necessárias}
                           {--only-missing : Sincronizar apenas artigos que ainda não estão no MySQL}
                           {--update-counters : Atualizar apenas os contadores de artigos}
                           {--article-id= : Sincronizar apenas um artigo específico}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Sincroniza informações de veículos dos artigos com o MySQL';

    /**
     * Execute o comando.
     *
     * @param VehicleSyncService $syncService
     * @return int
     */
    public function handle(VehicleSyncService $syncService)
    {
        // Verificar se é apenas para criar as tabelas
        if ($this->option('create-tables')) {
            $this->info('Criando tabelas MySQL para veículos...');
            
            if ($syncService->createMySQLTables()) {
                $this->info('Tabelas criadas com sucesso!');
                return Command::SUCCESS;
            } else {
                $this->error('Erro ao criar tabelas MySQL.');
                return Command::FAILURE;
            }
        }
        
        // Verificar se é apenas para atualizar contadores
        if ($this->option('update-counters')) {
            $this->info('Atualizando contadores de artigos...');
            $syncService->updateAllCounters();
            $this->info('Contadores atualizados com sucesso!');
            return Command::SUCCESS;
        }
        
        // Verificar se é para sincronizar um artigo específico
        if ($articleId = $this->option('article-id')) {
            $this->info("Sincronizando artigo {$articleId}...");
            
            $article = Article::find($articleId);
            
            if (!$article) {
                $this->error("Artigo não encontrado.");
                return Command::FAILURE;
            }
            
            if ($syncService->syncArticleToMySQL($article)) {
                $this->info("Artigo sincronizado com sucesso!");
                return Command::SUCCESS;
            } else {
                $this->error("Erro ao sincronizar artigo.");
                return Command::FAILURE;
            }
        }
        
        // Sincronizar todos os artigos
        $this->info('Iniciando sincronização de artigos com MySQL...');
        
        $onlyMissing = $this->option('only-missing');
        if ($onlyMissing) {
            $this->info('Sincronizando apenas artigos que ainda não estão no MySQL.');
        } else {
            $this->info('Sincronizando todos os artigos com informações de veículo.');
        }
        
        $stats = $syncService->syncAllArticlesToMySQL($onlyMissing);
        
        $this->newLine();
        $this->info("Sincronização concluída!");
        $this->info("Total de artigos: {$stats['total']}");
        $this->info("Processados: {$stats['processed']}");
        $this->info("Sincronizados com sucesso: {$stats['success']}");
        
        if ($stats['failed'] > 0) {
            $this->error("Falhas: {$stats['failed']}");
        }
        
        if ($stats['skipped'] > 0) {
            $this->line("Ignorados: {$stats['skipped']}");
        }
        
        return Command::SUCCESS;
    }
}
