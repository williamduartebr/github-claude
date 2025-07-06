<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TitleYearCorrectionService;

class TitleYearCorrectionsCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'tire-title-year-corrections 
                           {--all : Criar correções para todos os artigos de pneus não corrigidos}
                           {--process : Processar correções pendentes}
                           {--slug= : Processar apenas um artigo específico por slug}
                           {--limit=50 : Limite de artigos para processar}
                           {--stats : Mostrar apenas estatísticas}
                           {--clean-duplicates : Limpar correções duplicadas}
                           {--force : Força execução mesmo em produção}
                           {--dry-run : Apenas listar o que seria processado}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Corrige títulos, meta descriptions e perguntas frequentes com ano do veículo usando Claude API';

    protected $titleYearService;

    public function __construct(TitleYearCorrectionService $titleYearService)
    {
        parent::__construct();
        $this->titleYearService = $titleYearService;
    }

    /**
     * Execute o comando.
     */
    public function handle()
    {
        // Verificação de ambiente
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('⚠️ Comando bloqueado em produção. Use --force para executar.');
            return Command::FAILURE;
        }

        // Mostrar estatísticas
        if ($this->option('stats')) {
            return $this->showStats();
        }

        // Limpar duplicatas
        if ($this->option('clean-duplicates')) {
            return $this->cleanDuplicates();
        }

        // Processar correções pendentes
        if ($this->option('process')) {
            return $this->processCorrections();
        }

        // Processar slug específico
        if ($this->option('slug')) {
            return $this->processSingleSlug();
        }

        // Criar correções para todos os artigos
        if ($this->option('all')) {
            return $this->createCorrectionsForAll();
        }

        $this->info('📋 Use uma das opções: --all, --process, --stats, --clean-duplicates ou --slug=');
        return Command::SUCCESS;
    }

    /**
     * 📊 Mostra estatísticas detalhadas
     */
    protected function showStats()
    {
        $this->info('📊 Estatísticas de Correções de Títulos e Ano');
        $this->line('');

        $stats = $this->titleYearService->getStats();

        $this->table(['Categoria', 'Quantidade'], [
            ['⏳ Pendentes', $stats['pending']],
            ['⚙️ Processando', $stats['processing']],
            ['✅ Concluídas', $stats['completed']],
            ['❌ Falharam', $stats['failed']],
            ['📊 Total', $stats['total']]
        ]);

        // Estatísticas por domínio
        $totalTireArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->count();

        $this->line('');
        $this->info("🚗 Total de artigos de pneus: {$totalTireArticles}");
        $this->info("🔧 Taxa de correção: " . ($totalTireArticles > 0 ? round(($stats['total'] / $totalTireArticles) * 100, 2) : 0) . "%");

        if ($stats['completed'] > 0) {
            $successRate = round(($stats['completed'] / ($stats['completed'] + $stats['failed'])) * 100, 2);
            $this->info("✅ Taxa de sucesso: {$successRate}%");
        }

        // Mostrar estatísticas específicas de título/ano
        $this->line('');
        $this->info('🎯 Estatísticas Específicas:');
        
        $titleUpdates = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('correction_data.title_updated', true)
            ->count();

        $metaUpdates = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('correction_data.meta_updated', true)
            ->count();

        $faqUpdates = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('correction_data.faq_updated', true)
            ->count();

        $this->table(['Tipo de Atualização', 'Quantidade'], [
            ['📝 Títulos atualizados', $titleUpdates],
            ['🔍 Meta descriptions atualizadas', $metaUpdates],
            ['❓ FAQs atualizadas', $faqUpdates]
        ]);

        return Command::SUCCESS;
    }

    /**
     * 🧹 Limpa correções duplicadas
     */
    protected function cleanDuplicates()
    {
        $this->info('🧹 Limpando correções de título/ano duplicadas...');

        if ($this->option('dry-run')) {
            $this->warn('🔍 [DRY RUN] Apenas listando duplicatas...');
        }

        $results = $this->titleYearService->cleanAllDuplicates();

        $this->table(['Métrica', 'Valor'], [
            ['Artigos analisados', $results['articles_analyzed']],
            ['Duplicatas encontradas', $results['duplicates_found']],
            ['Correções removidas', $results['corrections_removed']]
        ]);

        if (!empty($results['articles_cleaned'])) {
            $this->line('');
            $this->info('📋 Artigos limpos:');
            foreach ($results['articles_cleaned'] as $slug) {
                $this->line("  • {$slug}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * ⚙️ Processa correções pendentes
     */
    protected function processCorrections()
    {
        $limit = (int) $this->option('limit');

        $this->info("⚙️ Processando correções de título/ano pendentes (limite: {$limit})...");

        if ($this->option('dry-run')) {
            $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->limit($limit)
                ->get();

            $this->info("🔍 [DRY RUN] {$corrections->count()} correções seriam processadas:");
            $tableData = [];
            foreach ($corrections as $correction) {
                $vehicleName = $correction->original_data['vehicle_data']['vehicle_name'] ?? 'N/A';
                $year = $correction->original_data['vehicle_data']['vehicle_year'] ?? 'N/A';
                
                $tableData[] = [
                    $correction->article_slug,
                    "{$vehicleName} {$year}",
                    $correction->created_at->format('d/m H:i'),
                    $correction->status
                ];
            }
            $this->table(['Slug', 'Veículo', 'Criado em', 'Status'], $tableData);
            return Command::SUCCESS;
        }

        $results = $this->titleYearService->processAllPendingCorrections($limit);

        $this->newLine();
        $this->info('📊 Resultado do processamento:');
        $this->info("⚙️ Processadas: {$results['processed']}");
        $this->info("✅ Sucessos: {$results['successful']}");
        $this->info("❌ Falhas: {$results['failed']}");

        // Detalhes dos tipos de atualização
        if ($results['successful'] > 0) {
            $this->line('');
            $this->info('📋 Detalhes das atualizações:');
            $this->info("📝 Títulos: {$results['details']['titles_updated']}");
            $this->info("🔍 Meta descriptions: {$results['details']['metas_updated']}");
            $this->info("❓ FAQs: {$results['details']['faqs_updated']}");
        }

        return Command::SUCCESS;
    }

    /**
     * 🎯 Processa slug específico
     */
    protected function processSingleSlug()
    {
        $slug = $this->option('slug');

        $this->info("🎯 Processando artigo específico: {$slug}");

        // Verificar se existe correção pendente
        $correction = ArticleCorrection::where('article_slug', $slug)
            ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->first();

        if (!$correction) {
            $this->warn("⚠️ Nenhuma correção de título/ano pendente encontrada para: {$slug}");
            
            if ($this->confirm('Deseja criar uma nova correção para este artigo?')) {
                $result = $this->titleYearService->createCorrectionsForSlugs([$slug]);
                
                if ($result['created'] > 0) {
                    $this->info("✅ Correção criada para: {$slug}");
                    
                    if ($this->confirm('Deseja processar a correção agora?')) {
                        $correction = ArticleCorrection::where('article_slug', $slug)
                            ->where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                            ->where('status', ArticleCorrection::STATUS_PENDING)
                            ->first();
                            
                        if ($correction) {
                            $success = $this->titleYearService->processTitleYearCorrection($correction);
                            
                            if ($success) {
                                $this->info('✅ Correção processada com sucesso!');
                                
                                // Mostrar detalhes do que foi atualizado
                                $fresh = $correction->fresh();
                                $this->showCorrectionDetails($fresh);
                            } else {
                                $this->error('❌ Falha ao processar a correção.');
                            }
                        }
                    }
                } else {
                    $this->error("❌ Falha ao criar correção para: {$slug}");
                }
            }
            
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $vehicleName = $correction->original_data['vehicle_data']['vehicle_name'] ?? 'N/A';
            $year = $correction->original_data['vehicle_data']['vehicle_year'] ?? 'N/A';
            
            $this->info("🔍 [DRY RUN] Seria processada:");
            $this->table(['ID', 'Slug', 'Veículo', 'Status', 'Criado em'], [
                [
                    $correction->_id, 
                    $correction->article_slug, 
                    "{$vehicleName} {$year}",
                    $correction->status, 
                    $correction->created_at->format('d/m H:i')
                ]
            ]);
            return Command::SUCCESS;
        }

        $this->info("⚙️ Processando correção...");
        $success = $this->titleYearService->processTitleYearCorrection($correction);

        if ($success) {
            $this->info("✅ Correção processada com sucesso para: {$slug}");
            $this->showCorrectionDetails($correction->fresh());
        } else {
            $this->error("❌ Falha ao processar correção para: {$slug}");
        }

        return Command::SUCCESS;
    }

    /**
     * 📝 Cria correções para todos os artigos
     */
    protected function createCorrectionsForAll()
    {
        $limit = (int) $this->option('limit');

        $this->info("📝 Criando correções de título/ano para artigos de pneus (limite: {$limit})...");

        $slugs = $this->titleYearService->getAllTireArticleSlugs($limit);

        if (empty($slugs)) {
            $this->info('ℹ️ Nenhum artigo de pneu encontrado para correção de título/ano.');
            return Command::SUCCESS;
        }

        $this->info("📋 Encontrados {" . count($slugs) . "} artigos para correção.");

        if ($this->option('dry-run')) {
            $this->info("🔍 [DRY RUN] Artigos que receberiam correções:");
            $chunks = array_chunk($slugs, 10);
            foreach ($chunks as $index => $chunk) {
                $this->line("Lote " . ($index + 1) . ":");
                foreach ($chunk as $slug) {
                    $this->line("  • {$slug}");
                }
            }
            return Command::SUCCESS;
        }

        // Confirmar criação em massa
        if (!$this->option('force') && !$this->confirm("Confirma criação de correções para {" . count($slugs) . "} artigos?")) {
            $this->info('Operação cancelada.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($slugs));
        $bar->start();

        $results = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        // Processar em lotes para evitar sobrecarga
        $chunks = array_chunk($slugs, 50);

        foreach ($chunks as $chunk) {
            $chunkResults = $this->titleYearService->createCorrectionsForSlugs($chunk);
            
            $results['created'] += $chunkResults['created'];
            $results['skipped'] += $chunkResults['skipped'];
            $results['errors'] += $chunkResults['errors'];

            $bar->advance(count($chunk));

            // Pausa entre lotes
            usleep(500000); // 0.5 segundos
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('📊 Resultado da criação:');
        $this->info("✅ Criadas: {$results['created']}");
        $this->info("⚠️ Ignoradas: {$results['skipped']}");
        $this->info("❌ Erros: {$results['errors']}");
        $this->info("📊 Total processado: " . array_sum($results));

        return Command::SUCCESS;
    }

    /**
     * 📋 Mostra detalhes do que foi corrigido
     */
    protected function showCorrectionDetails($correction)
    {
        if (!$correction || !isset($correction->correction_data)) {
            return;
        }

        $data = $correction->correction_data;

        $this->line('');
        $this->info('📋 Detalhes da correção aplicada:');

        if ($data['title_updated'] ?? false) {
            $this->info("📝 Título atualizado");
            if (isset($data['corrected_seo']['page_title'])) {
                $this->line("   Novo: " . $data['corrected_seo']['page_title']);
            }
        }

        if ($data['meta_updated'] ?? false) {
            $this->info("🔍 Meta description atualizada");
            if (isset($data['corrected_seo']['meta_description'])) {
                $preview = substr($data['corrected_seo']['meta_description'], 0, 100) . '...';
                $this->line("   Novo: " . $preview);
            }
        }

        if ($data['faq_updated'] ?? false) {
            $this->info("❓ Perguntas frequentes atualizadas");
            $faqCount = count($data['corrected_content']['perguntas_frequentes'] ?? []);
            $this->line("   {$faqCount} perguntas processadas");
        }

        if (isset($data['reason'])) {
            $this->line('');
            $this->comment("💡 Motivo: " . $data['reason']);
        }
    }
}