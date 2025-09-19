<?php

namespace Src\Sitemap\Infrastructure\Commands;

use Illuminate\Console\Command;
use Src\Sitemap\Domain\Services\SitemapService;

class SitemapGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sitemap:generate 
                            {--clear-cache : Limpar cache antes de gerar}
                            {--submit : Submeter aos motores de busca após gerar}';

    /**
     * The console command description.
     */
    protected $description = 'Gera todos os sitemaps automaticamente';

    private SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        parent::__construct();
        $this->sitemapService = $sitemapService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Iniciando geração dos sitemaps...');
        
        try {
            // Limpar cache se solicitado
            if ($this->option('clear-cache')) {
                $this->info('🧹 Limpando cache...');
                $this->sitemapService->clearCache();
                $this->info('✅ Cache limpo com sucesso!');
            }
            
            // Gerar sitemaps
            $this->info('📝 Gerando sitemaps...');
            $results = $this->sitemapService->generateAll();
            
            // Exibir resultados
            $this->displayResults($results);
            
            // Submeter aos motores de busca se solicitado
            if ($this->option('submit')) {
                $this->info('📤 Submetendo aos motores de busca...');
                $submitResults = $this->sitemapService->submitToSearchEngines();
                $this->displaySubmitResults($submitResults);
            }
            
            $this->info('🎉 Processo concluído com sucesso!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro durante a geração: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Exibe resultados da geração
     */
    private function displayResults(array $results): void
    {
        $this->info('📊 Resultados da geração:');
        
        if (isset($results['articles']) && is_array($results['articles'])) {
            $this->line("  📰 Artigos: " . count($results['articles']) . " arquivo(s)");
            foreach ($results['articles'] as $file) {
                $this->line("    - {$file}");
            }
        }
        
        if (isset($results['categories'])) {
            $this->line("  📂 Categorias: {$results['categories']}");
        }
        
        if (isset($results['pages'])) {
            $this->line("  📄 Páginas: {$results['pages']}");
        }
        
        if (isset($results['index'])) {
            $this->line("  📋 Index: {$results['index']}");
        }
    }
    
    /**
     * Exibe resultados da submissão
     */
    private function displaySubmitResults(array $results): void
    {
        $this->info('📤 Resultados da submissão:');
        
        foreach ($results as $engine => $success) {
            $status = $success ? '✅' : '❌';
            $this->line("  {$status} " . ucfirst($engine) . ": " . ($success ? 'Sucesso' : 'Falha'));
        }
    }
}