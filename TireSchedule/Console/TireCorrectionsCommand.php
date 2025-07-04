<?php

namespace Src\ContentGeneration\TireSchedule\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;

class TireCorrectionsCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'tire-pressure-corrections 
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
    protected $description = 'Corrige conteúdo e pressões de artigos sobre pneus usando Claude API';

    protected $tireCorrectionService;

    public function __construct(TireCorrectionService $tireCorrectionService)
    {
        parent::__construct();
        $this->tireCorrectionService = $tireCorrectionService;
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
        $this->info('📊 Estatísticas de Correções de Pneus');
        $this->line('');

        $stats = $this->tireCorrectionService->getStats();

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

        return Command::SUCCESS;
    }

    /**
     * 🧹 Limpa correções duplicadas
     */
    protected function cleanDuplicates()
    {
        $this->info('🧹 Limpando correções de pneus duplicadas...');

        if ($this->option('dry-run')) {
            $this->warn('🔍 [DRY RUN] Apenas listando duplicatas...');
        }

        $results = $this->tireCorrectionService->cleanAllDuplicates();

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

        $this->info("⚙️ Processando correções de pneus pendentes (limite: {$limit})...");

        if ($this->option('dry-run')) {
            $corrections = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->limit($limit)
                ->get();

            $this->info("🔍 [DRY RUN] {$corrections->count()} correções seriam processadas:");
            $tableData = [];
            foreach ($corrections as $correction) {
                $tableData[] = [
                    $correction->article_slug,
                    $correction->created_at->format('d/m H:i'),
                    $correction->status
                ];
            }
            $this->table(['Slug', 'Criado em', 'Status'], $tableData);
            return Command::SUCCESS;
        }

        $results = $this->tireCorrectionService->processAllPendingCorrections($limit);

        $this->newLine();
        $this->info('📊 Resultado do processamento:');
        $this->info("⚙️ Processadas: {$results['processed']}");
        $this->info("✅ Sucessos: {$results['successful']}");
        $this->info("❌ Falhas: {$results['failed']}");

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
            ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->first();

        if (!$correction) {
            $this->warn("⚠️ Nenhuma correção pendente encontrada para: {$slug}");
            
            if ($this->confirm('Deseja criar uma nova correção para este artigo?')) {
                $result = $this->tireCorrectionService->createCorrectionsForSlugs([$slug]);
                
                if ($result['created'] > 0) {
                    $this->info("✅ Correção criada para: {$slug}");
                    
                    if ($this->confirm('Deseja processar a correção agora?')) {
                        $correction = ArticleCorrection::where('article_slug', $slug)
                            ->where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                            ->where('status', ArticleCorrection::STATUS_PENDING)
                            ->first();
                            
                        if ($correction) {
                            $success = $this->tireCorrectionService->processTireCorrection($correction);
                            
                            if ($success) {
                                $this->info('✅ Correção processada com sucesso!');
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
            $this->info("🔍 [DRY RUN] Seria processada:");
            $this->table(['ID', 'Slug', 'Status', 'Criado em'], [
                [$correction->_id, $correction->article_slug, $correction->status, $correction->created_at->format('d/m H:i')]
            ]);
            return Command::SUCCESS;
        }

        $this->info("⚙️ Processando correção...");
        $success = $this->tireCorrectionService->processTireCorrection($correction);

        if ($success) {
            $this->info("✅ Correção processada com sucesso para: {$slug}");
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

        $this->info("📝 Criando correções para artigos de pneus (limite: {$limit})...");

        $slugs = $this->tireCorrectionService->getAllTireArticleSlugs($limit);

        if (empty($slugs)) {
            $this->info('ℹ️ Nenhum artigo de pneu encontrado para correção.');
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
            $chunkResults = $this->tireCorrectionService->createCorrectionsForSlugs($chunk);
            
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
}