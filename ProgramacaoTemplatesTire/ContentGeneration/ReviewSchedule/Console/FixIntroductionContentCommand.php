<?php

namespace Src\ContentGeneration\ReviewSchedule\Console;

use Illuminate\Console\Command;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\ArticleIntroductionCorrectionService;


class FixIntroductionContentCommand extends Command
{
    /**
     * Nome e assinatura do comando
     */
    protected $signature = 'articles:fix-introduction 
                           {--all : Criar correções para todos os artigos não corrigidos}
                           {--process : Processar correções pendentes via Claude API}
                           {--limit=50 : Limite de artigos para processar}
                           {--stats : Mostrar estatísticas das correções}
                           {--clean : Limpar correções duplicadas}
                           {--force : Forçar execução sem confirmação}';

    /**
     * Descrição do comando
     */
    protected $description = 'Gerencia correções humanizadas de introdução e considerações finais via Claude API';

    protected ArticleIntroductionCorrectionService $correctionService;

    /**
     * Construtor
     */
    public function __construct(ArticleIntroductionCorrectionService $correctionService)
    {
        parent::__construct();
        $this->correctionService = $correctionService;
    }

    /**
     * Executa o comando
     */
    public function handle(): int
    {
        $this->info('🎨 Sistema de Correção de Conteúdo - Introdução & Considerações Finais');
        $this->line('==================================================================');
        $this->line('');

        try {
            // Estatísticas
            if ($this->option('stats')) {
                return $this->showStats();
            }

            // Limpeza de duplicatas
            if ($this->option('clean')) {
                return $this->cleanDuplicates();
            }

            // Processamento via Claude API
            if ($this->option('process')) {
                return $this->processCorrections();
            }

            // Criação de correções
            if ($this->option('all')) {
                return $this->createCorrections();
            }

            // Menu interativo se nenhuma opção foi selecionada
            return $this->showInteractiveMenu();

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 📊 Mostra estatísticas
     */
    protected function showStats(): int
    {
        $this->info('📊 ESTATÍSTICAS DE CORREÇÕES DE CONTEÚDO');
        $this->line('=======================================');

        $stats = $this->correctionService->getStats();

        $this->table(['Métrica', 'Quantidade'], [
            ['⏳ Pendentes', $stats['pending']],
            ['⚡ Processando', $stats['processing']],
            ['✅ Concluídas', $stats['completed']],
            ['❌ Falhadas', $stats['failed']],
            ['📈 Total', $stats['total']],
        ]);

        // Taxa de sucesso
        if ($stats['total'] > 0) {
            $successRate = round(($stats['completed'] / $stats['total']) * 100, 1);
            $this->line('');
            $this->info("🎯 Taxa de Sucesso: {$successRate}%");
        }

        return Command::SUCCESS;
    }

    /**
     * 🧹 Limpeza de duplicatas
     */
    protected function cleanDuplicates(): int
    {
        if (!$this->option('force') && !$this->confirm('Confirma a limpeza de correções duplicadas?')) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        $this->info('🧹 Iniciando limpeza de duplicatas...');
        
        $results = $this->correctionService->cleanAllDuplicates();

        $this->table(['Métrica', 'Valor'], [
            ['Artigos analisados', $results['articles_analyzed']],
            ['Duplicatas encontradas', $results['duplicates_found']],
            ['Correções removidas', $results['corrections_removed']],
        ]);

        if (!empty($results['articles_cleaned'])) {
            $this->line('');
            $this->info('Artigos limpos:');
            foreach (array_slice($results['articles_cleaned'], 0, 10) as $slug) {
                $this->line("  • {$slug}");
            }
            
            if (count($results['articles_cleaned']) > 10) {
                $remaining = count($results['articles_cleaned']) - 10;
                $this->line("  ... e mais {$remaining} artigos");
            }
        }

        $this->info('✅ Limpeza concluída com sucesso!');
        return Command::SUCCESS;
    }

    /**
     * ⚡ Processa correções via Claude API
     */
    protected function processCorrections(): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("⚡ Processando até {$limit} correções via Claude API...");
        $this->line('');

        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        $results = $this->correctionService->processAllPendingCorrections($limit);

        $bar->finish();
        $this->line('');
        $this->line('');

        $this->table(['Métrica', 'Quantidade'], [
            ['Processadas', $results['processed']],
            ['✅ Sucessos', $results['successful']],
            ['❌ Falhas', $results['failed']],
        ]);

        if ($results['successful'] > 0) {
            $this->info("🎉 {$results['successful']} artigos tiveram conteúdo aprimorado!");
        }

        if ($results['failed'] > 0) {
            $this->warn("⚠️ {$results['failed']} correções falharam (logs disponíveis)");
        }

        return Command::SUCCESS;
    }

    /**
     * 🆕 Cria novas correções
     */
    protected function createCorrections(): int
    {
        $limit = (int) $this->option('limit');
        
        $this->info("🆕 Criando correções para até {$limit} artigos...");
        
        // Buscar slugs disponíveis
        $slugs = $this->correctionService->getAllArticleSlugs($limit);
        
        if (empty($slugs)) {
            $this->info('✅ Todos os artigos já possuem correções criadas!');
            return Command::SUCCESS;
        }

        $slugCount = count($slugs);
        $this->line("📝 Encontrados {$slugCount} artigos para correção");
        
        if (!$this->option('force') && !$this->confirm("Criar correções para {$slugCount} artigos?")) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($slugCount);
        $bar->start();

        $results = $this->correctionService->createCorrectionsForSlugs($slugs);

        $bar->finish();
        $this->line('');
        $this->line('');

        $this->table(['Resultado', 'Quantidade'], [
            ['✅ Criadas', $results['created']],
            ['⏭️ Puladas', $results['skipped']],
            ['❌ Erros', $results['errors']],
        ]);

        if ($results['created'] > 0) {
            $this->info("🎯 {$results['created']} novas correções criadas com sucesso!");
        }

        return Command::SUCCESS;
    }

    /**
     * 🎛️ Menu interativo
     */
    protected function showInteractiveMenu(): int
    {
        $this->info('🎛️ MENU INTERATIVO');
        $this->line('=================');
        $this->line('');

        $choice = $this->choice('O que deseja fazer?', [
            'stats' => 'Ver estatísticas',
            'create' => 'Criar correções para artigos não corrigidos',
            'process' => 'Processar correções pendentes via Claude API',
            'clean' => 'Limpar correções duplicadas',
            'exit' => 'Sair'
        ], 'stats');

        switch ($choice) {
            case 'stats':
                return $this->showStats();
                
            case 'create':
                $limit = $this->ask('Quantos artigos processar?', '50');
                $this->input->setOption('limit', $limit);
                return $this->createCorrections();
                
            case 'process':
                $limit = $this->ask('Quantas correções processar?', '10');
                $this->input->setOption('limit', $limit);
                return $this->processCorrections();
                
            case 'clean':
                return $this->cleanDuplicates();
                
            case 'exit':
                $this->info('👋 Até logo!');
                return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }
}