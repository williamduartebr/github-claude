<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ArticleGenerator\Infrastructure\Services\ArticleAnalysisService;

class AnalyzeArticlesPunctuation extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:analyze-punctuation 
                           {--slug= : Analisar apenas um artigo específico}
                           {--limit=50 : Limite de artigos a analisar por execução}
                           {--status=published : Status dos artigos a analisar}
                           {--template= : Filtrar por template específico}
                           {--skip-analyzed : Pular artigos já analisados}
                           {--force-reanalyze : Forçar reanálise de artigos já analisados}
                           {--reanalyze-old : Reanalisar apenas artigos antigos (>3 dias)}
                           {--dry-run : Apenas listar artigos que seriam analisados}
                           {--stats : Mostrar apenas estatísticas}
                           {--reset-loop : Resetar loop infinito forçando análise de novos artigos}
                           {--clean-failed : Limpar análises falhadas antigas}
                           {--batch-size=10 : Tamanho do lote para processamento}
                           {--progress : Mostrar barra de progresso detalhada}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Analisa artigos em busca de problemas de pontuação na introdução (versão completa v2.1)';

    protected $analysisService;

    public function __construct(ArticleAnalysisService $analysisService)
    {
        parent::__construct();
        $this->analysisService = $analysisService;
    }

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $this->info('🔍 Sistema de Análise de Pontuação v2.1');
        $this->line('=======================================');
        $this->line('');

        // Limpar análises falhadas
        if ($this->option('clean-failed')) {
            return $this->cleanFailedAnalyses();
        }

        // Mostrar apenas estatísticas
        if ($this->option('stats')) {
            return $this->showStats();
        }

        // Analisar artigo específico
        if ($this->option('slug')) {
            return $this->analyzeSingleArticle();
        }

        // Reanálise de artigos antigos
        if ($this->option('reanalyze-old')) {
            return $this->reanalyzeOldArticles();
        }

        // Análise em lote
        return $this->analyzeBulkArticles();
    }

    /**
     * Limpa análises falhadas antigas
     */
    protected function cleanFailedAnalyses()
    {
        $this->info('🧹 Limpando análises falhadas antigas...');

        $oldFailedAnalyses = ArticleCorrection::where('status', ArticleCorrection::STATUS_FAILED)
            ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '<', now()->subDays(1))
            ->get();

        if ($oldFailedAnalyses->isEmpty()) {
            $this->info('✅ Nenhuma análise falhada antiga encontrada.');
            return Command::SUCCESS;
        }

        $this->info("🗑️ Encontradas {$oldFailedAnalyses->count()} análises falhadas para limpeza.");

        if ($this->option('dry-run')) {
            $this->showFailedAnalysesTable($oldFailedAnalyses->take(10));
            return Command::SUCCESS;
        }

        if ($this->confirm("Deseja deletar {$oldFailedAnalyses->count()} análises falhadas?")) {
            $deleted = $this->deleteFailedAnalyses($oldFailedAnalyses);
            $this->info("✅ {$deleted} análises falhadas foram removidas.");
        }

        return Command::SUCCESS;
    }

    /**
     * Mostra tabela de análises falhadas
     */
    protected function showFailedAnalysesTable($analyses)
    {
        $tableData = [];
        foreach ($analyses as $analysis) {
            $tableData[] = [
                $analysis->article_slug,
                $analysis->created_at->format('d/m H:i'),
                substr($analysis->error_message ?? 'N/A', 0, 50) . '...'
            ];
        }
        $this->table(['Slug', 'Criado', 'Erro'], $tableData);
        
        if ($analyses->count() < 10) {
            $this->line('(mostrando todas as análises falhadas)');
        } else {
            $this->line('... (mostrando apenas as primeiras 10)');
        }
    }

    /**
     * Deleta análises falhadas
     */
    protected function deleteFailedAnalyses($analyses)
    {
        $deleted = 0;
        foreach ($analyses as $analysis) {
            try {
                $analysis->delete();
                $deleted++;
            } catch (\Exception $e) {
                $this->error("Erro ao deletar análise {$analysis->_id}: " . $e->getMessage());
            }
        }
        return $deleted;
    }

    /**
     * Reanálise de artigos antigos
     */
    protected function reanalyzeOldArticles()
    {
        $limit = (int) $this->option('limit');
        
        $this->info('🔄 Buscando artigos que precisam de reanálise (>3 dias)...');

        $oldArticleSlugs = $this->analysisService->getArticlesNeedingReanalysis($limit);

        if (empty($oldArticleSlugs)) {
            $this->info('✅ Todos os artigos foram analisados recentemente.');
            return Command::SUCCESS;
        }

        $this->info("📝 Encontrados " . count($oldArticleSlugs) . " artigos para reanálise.");

        if ($this->option('dry-run')) {
            $this->showArticlesList($oldArticleSlugs, 'reanálise');
            return Command::SUCCESS;
        }

        return $this->processArticlesList($oldArticleSlugs, true);
    }

    /**
     * Mostra lista de artigos
     */
    protected function showArticlesList(array $slugs, string $action = 'análise')
    {
        $this->info("🔍 [DRY RUN] Artigos que seriam processados para {$action}:");
        
        $displaySlugs = array_slice($slugs, 0, 15);
        foreach ($displaySlugs as $index => $slug) {
            $this->line(sprintf("• %d. %s", $index + 1, $slug));
        }
        
        if (count($slugs) > 15) {
            $this->line('... (mostrando apenas os primeiros 15 de ' . count($slugs) . ')');
        }
    }

    /**
     * Mostra estatísticas das análises
     */
    protected function showStats()
    {
        $this->info('📊 Estatísticas de Análise de Pontuação v2.1');
        $this->line('');

        $stats = ArticleCorrection::getDetailedStats();
        $totalArticles = Article::where('status', 'published')->count();

        $this->table(['Categoria', 'Quantidade', 'Percentual'], [
            ['Total de artigos publicados', number_format($totalArticles), '100%'],
            ['Análises pendentes', $stats['pending_analysis'], $this->percentage($stats['pending_analysis'], $totalArticles)],
            ['Análises concluídas', $stats['completed_analysis'], $this->percentage($stats['completed_analysis'], $totalArticles)],
            ['Artigos que precisam correção', $stats['needs_correction'], $this->percentage($stats['needs_correction'], $totalArticles)],
            ['Correções pendentes', $stats['pending_fixes'], '-'],
            ['Correções concluídas', $stats['completed_fixes'], '-'],
            ['Sem alterações necessárias', $stats['no_changes'], $this->percentage($stats['no_changes'], $stats['total_processed'])],
            ['Pulados', $stats['skipped'], $this->percentage($stats['skipped'], $stats['total_processed'])],
            ['Falhas', $stats['failed'], $this->percentage($stats['failed'], $stats['total_processed'])]
        ]);

        // Mostrar queue de correções por prioridade
        $queue = ArticleCorrection::getCorrectionQueue();

        $this->line('');
        $this->info('🔥 Fila de Correções por Prioridade:');
        $this->line("🔴 Alta prioridade: {$queue['high_priority']->count()}");
        $this->line("🟡 Média prioridade: {$queue['medium_priority']->count()}");
        $this->line("🟢 Baixa prioridade: {$queue['low_priority']->count()}");

        // Estatísticas adicionais
        $this->line('');
        $this->info('📈 Estatísticas Adicionais:');
        
        $recentlyAnalyzed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '>=', now()->subDays(1))
            ->count();
        
        $needReanalysis = count($this->analysisService->getArticlesNeedingReanalysis(1000));
        
        $this->line("📅 Analisados hoje: {$recentlyAnalyzed}");
        $this->line("⏰ Precisam reanálise (>3 dias): {$needReanalysis}");
        $this->line("📊 Taxa de sucesso: {$stats['success_rate']}%");
        $this->line("⚠️ Taxa de problemas: {$stats['problem_rate']}%");

        return Command::SUCCESS;
    }

    /**
     * Analisa um único artigo
     */
    protected function analyzeSingleArticle()
    {
        $slug = $this->option('slug');
        $forceReanalyze = $this->option('force-reanalyze');

        $this->info("🔍 Analisando artigo: {$slug}");

        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            $this->error("❌ Artigo não encontrado: {$slug}");
            return Command::FAILURE;
        }

        $this->showArticleInfo($article, $forceReanalyze);

        if ($this->option('dry-run')) {
            $this->showArticleDetails($article);
            return Command::SUCCESS;
        }

        return $this->processSingleArticle($article, $forceReanalyze);
    }

    /**
     * Mostra informações do artigo
     */
    protected function showArticleInfo($article, $forceReanalyze)
    {
        $this->table(['Campo', 'Valor'], [
            ['Slug', $article->slug],
            ['Título', $article->title],
            ['Status', $article->status],
            ['Template', $article->template ?? 'N/A'],
            ['Força reanálise', $forceReanalyze ? 'SIM' : 'NÃO'],
            ['Última atualização', $article->updated_at ? $article->updated_at->format('d/m/Y H:i:s') : 'N/A']
        ]);
    }

    /**
     * Mostra detalhes do artigo em modo dry-run
     */
    protected function showArticleDetails($article)
    {
        $introducao = $article->content['introducao'] ?? '';
        $this->line('');
        $this->info('📝 Introdução atual (' . strlen($introducao) . ' caracteres):');
        $this->line($introducao ? substr($introducao, 0, 200) . '...' : 'Sem introdução');
        
        // Verificar análises anteriores
        $previousAnalysis = ArticleCorrection::where('article_slug', $article->slug)
            ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($previousAnalysis) {
            $this->line('');
            $this->info('📋 Última análise:');
            $this->table(['Campo', 'Valor'], [
                ['Status', $previousAnalysis->status],
                ['Data', $previousAnalysis->created_at->format('d/m/Y H:i:s')],
                ['Precisa correção', isset($previousAnalysis->correction_data['needs_correction']) 
                    ? ($previousAnalysis->correction_data['needs_correction'] ? 'SIM' : 'NÃO') 
                    : 'N/A'],
                ['Prioridade', $previousAnalysis->correction_data['correction_priority'] ?? 'N/A']
            ]);
        } else {
            $this->line('');
            $this->warn('⚠️ Nenhuma análise anterior encontrada');
        }
    }

    /**
     * Processa um único artigo
     */
    protected function processSingleArticle($article, $forceReanalyze)
    {
        $result = $this->analysisService->analyzeArticlePunctuation($article->slug, $forceReanalyze);

        if ($result === false) {
            $this->error("❌ Erro ao analisar artigo");
            return Command::FAILURE;
        }

        if ($result === null) {
            $this->info("✅ Nenhum problema detectado");
            return Command::SUCCESS;
        }

        $needsCorrection = $result->correction_data['needs_correction'] ?? false;
        
        if ($needsCorrection) {
            $this->info("⚠️ Problemas detectados! Análise criada ID: {$result->_id}");
            $this->showProblemsSummary($result);
        } else {
            $this->info("✅ Análise concluída - artigo está correto. ID: {$result->_id}");
        }

        if ($needsCorrection && $this->confirm('Deseja processar a análise com Claude agora?')) {
            $success = $this->analysisService->processAnalysisWithClaude($result);

            if ($success) {
                $this->info('✅ Análise processada com sucesso!');
            } else {
                $this->error('❌ Falha ao processar análise.');
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Mostra resumo dos problemas encontrados
     */
    protected function showProblemsSummary($analysis)
    {
        if (!isset($analysis->correction_data['problems_found'])) {
            return;
        }

        $this->line('');
        $this->info('🔍 Problemas detectados:');
        
        foreach ($analysis->correction_data['problems_found'] as $problem) {
            $this->line("• {$problem['type']}: {$problem['description']}");
            if (!empty($problem['text_fragment'])) {
                $this->line("  Trecho: \"{$problem['text_fragment']}\"");
            }
        }
        
        $priority = $analysis->correction_data['correction_priority'] ?? 'medium';
        $confidence = $analysis->correction_data['confidence_level'] ?? 'medium';
        
        $this->line('');
        $this->line("Prioridade: " . strtoupper($priority));
        $this->line("Confiança: " . strtoupper($confidence));
    }

    /**
     * Análise em lote de artigos
     */
    protected function analyzeBulkArticles()
    {
        $limit = (int) $this->option('limit');
        $status = $this->option('status');
        $template = $this->option('template');
        $skipAnalyzed = $this->option('skip-analyzed');
        $forceReanalyze = $this->option('force-reanalyze');
        $resetLoop = $this->option('reset-loop');

        $this->info("🔍 Iniciando análise em lote de artigos (v2.1)...");
        $this->showAnalysisParameters($limit, $status, $template, $skipAnalyzed, $forceReanalyze, $resetLoop);

        $articleSlugs = $this->getArticlesToAnalyze($status, $template, $skipAnalyzed, $forceReanalyze, $resetLoop, $limit);

        if (empty($articleSlugs)) {
            $this->info("ℹ️ Nenhum artigo encontrado para análise.");
            $this->showAnalysisGuidance();
            return Command::SUCCESS;
        }

        return $this->processArticlesList($articleSlugs, $forceReanalyze);
    }

    /**
     * Mostra parâmetros da análise
     */
    protected function showAnalysisParameters($limit, $status, $template, $skipAnalyzed, $forceReanalyze, $resetLoop)
    {
        $this->table(['Parâmetro', 'Valor'], [
            ['Limite', $limit],
            ['Status', $status],
            ['Template', $template ?: 'Todos'],
            ['Pular analisados', $skipAnalyzed ? 'SIM' : 'NÃO'],
            ['Forçar reanálise', $forceReanalyze ? 'SIM' : 'NÃO'],
            ['Reset de loop', $resetLoop ? 'SIM' : 'NÃO']
        ]);
        $this->line('');
    }

    /**
     * Obtém artigos para analisar
     */
    protected function getArticlesToAnalyze($status, $template, $skipAnalyzed, $forceReanalyze, $resetLoop, $limit)
    {
        // Construir query
        $query = Article::where('status', $status);

        if ($template) {
            $query->where('template', $template);
        }

        // Filtrar artigos já analisados se solicitado
        if ($skipAnalyzed && !$forceReanalyze) {
            $analyzedSlugs = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('created_at', '>=', now()->subDays(3)) // Apenas análises recentes
                ->pluck('article_slug')
                ->toArray();

            if (!empty($analyzedSlugs)) {
                $query->whereNotIn('slug', $analyzedSlugs);
                $analyzedCount = count($analyzedSlugs);
                $this->info("📝 Pulando {$analyzedCount} artigos analisados recentemente");
            }
        }

        // Ordenação estratégica para evitar loop
        if ($resetLoop) {
            $query->orderBy('updated_at', 'desc')->orderBy('_id', 'desc');
            $this->warn("🔄 Modo reset-loop ativo: buscando artigos em ordem diferente");
        } else {
            $query->orderBy('created_at', 'asc')->orderBy('_id', 'asc');
        }

        $articles = $query->limit($limit)->get();
        return $articles->pluck('slug')->toArray();
    }

    /**
     * Processa uma lista de slugs de artigos
     */
    protected function processArticlesList(array $articleSlugs, $forceReanalyze = false)
    {
        $this->info("📝 Processando " . count($articleSlugs) . " artigos.");

        // Detectar possível loop
        if (!$forceReanalyze && $this->detectPossibleLoop($articleSlugs)) {
            $this->warn("⚠️ Possível loop detectado! Os mesmos artigos estão sendo analisados repetidamente.");
            $this->showLoopGuidance();

            if (!$this->option('dry-run') && !$this->confirm('Continuar mesmo assim?')) {
                return Command::SUCCESS;
            }
        }

        if ($this->option('dry-run')) {
            $this->showDryRunResults($articleSlugs);
            return Command::SUCCESS;
        }

        return $this->executeAnalysis($articleSlugs, $forceReanalyze);
    }

    /**
     * Executa a análise dos artigos
     */
    protected function executeAnalysis(array $articleSlugs, $forceReanalyze)
    {
        $batchSize = (int) $this->option('batch-size');
        $showProgress = $this->option('progress');
        
        if ($showProgress) {
            $bar = $this->output->createProgressBar(count($articleSlugs));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% -- %message%');
            $bar->setMessage('Iniciando análise...');
            $bar->start();
        }

        $stats = $this->initializeStats();
        $batches = array_chunk($articleSlugs, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            if ($showProgress) {
                $bar->setMessage("Processando lote " . ($batchIndex + 1) . "/" . count($batches));
            }

            foreach ($batch as $slug) {
                try {
                    $result = $this->analysisService->analyzeArticlePunctuation($slug, $forceReanalyze);
                    $this->updateStats($stats, $result, $slug, $forceReanalyze);

                    if ($showProgress) {
                        $bar->advance();
                        $bar->setMessage("Processado: {$slug}");
                    }

                    // Delay pequeno para não sobrecarregar
                    usleep(250000); // 0.25 segundo

                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->handleAnalysisError($slug, $e, $showProgress);
                }
            }

            // Delay entre lotes
            if ($batchIndex < count($batches) - 1) {
                sleep(1);
            }
        }

        if ($showProgress) {
            $bar->finish();
            $this->newLine(2);
        }

        $this->showFinalStats($stats);
        return Command::SUCCESS;
    }

    /**
     * Inicializa estatísticas
     */
    protected function initializeStats()
    {
        return [
            'analyzed' => 0,
            'problems_found' => 0,
            'clean' => 0,
            'errors' => 0,
            'skipped' => 0,
            'reanalyzed' => 0,
            'no_introduction' => 0
        ];
    }

    /**
     * Atualiza estatísticas
     */
    protected function updateStats(&$stats, $result, $slug, $forceReanalyze)
    {
        if ($result === false) {
            $stats['errors']++;
            if (!$this->option('progress')) {
                $this->newLine();
                $this->error("❌ Erro: {$slug}");
            }
        } elseif ($result === null) {
            $stats['skipped']++;
            if (!$this->option('progress')) {
                $this->newLine();
                $this->line("⏭️ Pulado: {$slug}");
            }
        } else {
            $stats['analyzed']++;
            
            if ($forceReanalyze) {
                $stats['reanalyzed']++;
            }

            // Verificar se é caso especial (sem introdução)
            if (isset($result->correction_data['analysis_result']) && 
                $result->correction_data['analysis_result'] === 'no_introduction_found') {
                $stats['no_introduction']++;
                if (!$this->option('progress')) {
                    $this->newLine();
                    $this->line("📄 Sem introdução: {$slug}");
                }
                return;
            }

            $needsCorrection = $result->correction_data['needs_correction'] ?? false;
            
            if ($needsCorrection) {
                $stats['problems_found']++;
                if (!$this->option('progress')) {
                    $this->newLine();
                    $this->info("⚠️ Problemas: {$slug}");
                }
            } else {
                $stats['clean']++;
                if (!$this->option('progress')) {
                    $this->newLine();
                    $this->line("✅ Limpo: {$slug}");
                }
            }
        }
    }

    /**
     * Trata erro de análise
     */
    protected function handleAnalysisError($slug, $exception, $showProgress)
    {
        if (!$showProgress) {
            $this->newLine();
            $this->error("❌ Exceção: {$slug} - {$exception->getMessage()}");
        }
        
        Log::error("Erro ao analisar artigo {$slug}: " . $exception->getMessage());
    }

    /**
     * Mostra resultados do modo dry-run
     */
    protected function showDryRunResults(array $articleSlugs)
    {
        $this->info("🔍 [DRY RUN] Artigos que seriam analisados:");
        
        $articles = Article::whereIn('slug', array_slice($articleSlugs, 0, 10))->get();
        $tableData = [];
        
        foreach ($articles as $article) {
            $introducaoLength = strlen($article->content['introducao'] ?? '');
            
            $lastAnalysis = ArticleCorrection::where('article_slug', $article->slug)
                ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->orderBy('created_at', 'desc')
                ->first();

            $wasAnalyzed = $lastAnalysis ? '✅ ' . $lastAnalysis->created_at->format('d/m') : '❌';

            $tableData[] = [
                $article->slug,
                substr($article->title, 0, 40) . '...',
                $article->template ?? 'N/A',
                $introducaoLength . ' chars',
                $wasAnalyzed
            ];
        }
        
        $this->table(['Slug', 'Título', 'Template', 'Introdução', 'Última Análise'], $tableData);
        
        if (count($articleSlugs) > 10) {
            $this->line('... (mostrando apenas os primeiros 10 de ' . count($articleSlugs) . ')');
        }
    }

    /**
     * Mostra estatísticas finais
     */
    protected function showFinalStats($stats)
    {
        $total = $stats['analyzed'] + $stats['errors'] + $stats['skipped'];
        
        $this->info("📊 Análise concluída:");
        $this->table(['Métrica', 'Quantidade', 'Percentual'], [
            ['✅ Artigos analisados', $stats['analyzed'], $this->percentage($stats['analyzed'], $total)],
            ['🔄 Reanalisados', $stats['reanalyzed'], $this->percentage($stats['reanalyzed'], $stats['analyzed'])],
            ['⚠️ Problemas encontrados', $stats['problems_found'], $this->percentage($stats['problems_found'], $stats['analyzed'])],
            ['✨ Artigos limpos', $stats['clean'], $this->percentage($stats['clean'], $stats['analyzed'])],
            ['📄 Sem introdução', $stats['no_introduction'], $this->percentage($stats['no_introduction'], $stats['analyzed'])],
            ['⏭️ Artigos pulados', $stats['skipped'], $this->percentage($stats['skipped'], $total)],
            ['❌ Erros', $stats['errors'], $this->percentage($stats['errors'], $total)]
        ]);

        if ($stats['problems_found'] > 0) {
            $this->line('');
            $this->info("💡 Próximos passos:");
            $this->line("1. Execute: php artisan articles:process-analysis --type=punctuation_analysis");
            $this->line("2. Execute: php artisan articles:process-corrections --type=introduction_fix");
            $this->line("3. Para monitorar: php artisan articles:punctuation-stats --detailed");
        }

        // Recomendações baseadas nos resultados
        $this->showRecommendations($stats);
    }

    /**
     * Mostra recomendações baseadas nos resultados
     */
    protected function showRecommendations($stats)
    {
        $this->line('');
        $this->info('💡 RECOMENDAÇÕES:');

        $total = $stats['analyzed'] + $stats['errors'] + $stats['skipped'];
        $problemRate = $stats['analyzed'] > 0 ? ($stats['problems_found'] / $stats['analyzed']) * 100 : 0;
        $errorRate = $total > 0 ? ($stats['errors'] / $total) * 100 : 0;

        if ($problemRate > 50) {
            $this->line('🔥 Alta taxa de problemas detectados - considere revisar templates de conteúdo');
        } elseif ($problemRate > 20) {
            $this->line('⚠️ Taxa moderada de problemas - monitoramento regular recomendado');
        } elseif ($problemRate < 5) {
            $this->line('✅ Baixa taxa de problemas - sistema funcionando bem');
        }

        if ($errorRate > 10) {
            $this->line('❌ Alta taxa de erros - execute: php artisan articles:health-check');
        }

        if ($stats['skipped'] > ($total * 0.5)) {
            $this->line('⏭️ Muitos artigos pulados - considere usar --force-reanalyze ou --reanalyze-old');
        }

        if ($stats['no_introduction'] > 0) {
            $this->line("📄 {$stats['no_introduction']} artigos sem introdução - revisar processo de criação");
        }
    }

    /**
     * Detecta se os mesmos artigos estão sendo analisados repetidamente
     */
    protected function detectPossibleLoop($articleSlugs)
    {
        $recentlyAnalyzed = 0;
        $twoHoursAgo = now()->subHours(2);

        // Verificar apenas uma amostra para performance
        $sampleSize = min(10, count($articleSlugs));
        $sample = array_slice($articleSlugs, 0, $sampleSize);

        foreach ($sample as $slug) {
            $recentAnalysis = ArticleCorrection::where('article_slug', $slug)
                ->where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
                ->where('created_at', '>=', $twoHoursAgo)
                ->exists();

            if ($recentAnalysis) {
                $recentlyAnalyzed++;
            }
        }

        // Se mais de 70% da amostra foi analisada recentemente, provável loop
        $loopThreshold = 0.7;
        return ($recentlyAnalyzed / $sampleSize) > $loopThreshold;
    }

    /**
     * Mostra orientações sobre análise
     */
    protected function showAnalysisGuidance()
    {
        $totalArticles = Article::where('status', 'published')->count();
        $analyzedCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)->count();
        $recentCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_PUNCTUATION_ANALYSIS)
            ->where('created_at', '>=', now()->subDays(3))
            ->count();

        $this->table(['Métrica', 'Valor'], [
            ['Total de artigos publicados', number_format($totalArticles)],
            ['Total já analisados', number_format($analyzedCount)],
            ['Analisados recentemente (3 dias)', number_format($recentCount)],
            ['Percentual analisado', $this->percentage($analyzedCount, $totalArticles)],
            ['Percentual recente', $this->percentage($recentCount, $totalArticles)]
        ]);
        
        $this->line('');
        $this->info('💡 Opções disponíveis:');
        $this->line('• --force-reanalyze: Forçar reanálise de todos os artigos');
        $this->line('• --reanalyze-old: Reanalisar apenas artigos >3 dias');
        $this->line('• --reset-loop: Tentar ordem diferente para evitar loops');
        $this->line('• --template=X: Focar em template específico');
        $this->line('• --clean-failed: Limpar análises problemáticas');
        $this->line('• --batch-size=N: Ajustar tamanho do lote de processamento');
        $this->line('• --progress: Mostrar barra de progresso detalhada');
    }

    /**
     * Mostra orientações sobre loop
     */
    protected function showLoopGuidance()
    {
        $this->line("💡 Sugestões para resolver loop:");
        $this->line("   1. Execute com --reset-loop para tentar ordem diferente");
        $this->line("   2. Execute com --reanalyze-old para focar em artigos antigos");
        $this->line("   3. Execute com --clean-failed para limpar análises problemáticas");
        $this->line("   4. Verifique se o serviço está marcando análises corretamente");
        $this->line("   5. Use --template=X para focar em templates específicos");
        $this->line("   6. Considere reduzir --limit para lotes menores");
    }

    /**
     * Calcula percentual
     */
    protected function percentage($part, $total)
    {
        if ($total == 0) return '0%';
        return round(($part / $total) * 100, 1) . '%';
    }
}