<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;

/**
 * GenerateStandardCommand - Modelo Standard (Econômico)
 * 
 * MODELO: claude-3-7-sonnet-20250219
 * CUSTO: 2.3x (base)
 * QUALIDADE: Boa para maioria dos artigos
 * VELOCIDADE: Rápida (~15-30s por artigo)
 * 
 * QUANDO USAR:
 * - Primeira tentativa de geração (sempre)
 * - Artigos de complexidade baixa/média
 * - Processamento em massa
 * - Uso contínuo automatizado
 * 
 * ESTRATÉGIA:
 * 1. Processar artigos pending de qualquer prioridade
 * 2. Batch processing com delay entre requisições
 * 3. Auto-retry limitado (máx 2x neste modelo)
 * 4. Escalar para intermediate em caso de falha
 * 
 * USO:
 * php artisan temp-article:generate-standard --limit=10
 * php artisan temp-article:generate-standard --category=oleo --limit=5
 * php artisan temp-article:generate-standard --priority=high
 * php artisan temp-article:generate-standard --batch-size=5 --delay=3
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Atualizado para Claude 3.7 Sonnet
 */
class GenerateStandardCommand extends Command
{
    protected $signature = 'temp-article:generate-standard
                            {--limit=1 : Quantidade máxima de artigos}
                            {--delay=3 : Delay entre requisições (segundos)}
                            {--batch-size=5 : Tamanho do lote de processamento}
                            {--priority= : Prioridade específica (high|medium|low)}
                            {--category= : Categoria específica (slug)}
                            {--subcategory= : Subcategoria específica (slug)}
                            {--retry-failed : Incluir artigos que falharam 1x com standard}
                            {--dry-run : Simulação sem gerar}
                            {--show-stats : Mostrar estatísticas antes de iniciar}
                            {--auto-escalate : Auto-escalar falhas para intermediate}';

    protected $description = 'Gerar artigos usando modelo STANDARD (claude-3-7-sonnet) - Econômico e Eficiente';

    private GenerationClaudeApiService $claudeService;

    private array $stats = [
        'processed' => 0,
        'successful' => 0,
        'failed' => 0,
        'skipped' => 0,
        'total_cost' => 0.0,
        'total_time' => 0.0,
        'by_category' => [],
        'errors' => []
    ];

    public function __construct(GenerationClaudeApiService $claudeService)
    {
        parent::__construct();
        $this->claudeService = $claudeService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);

        $this->displayHeader();

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            $this->line('💡 Configure em: .env → ANTHROPIC_API_KEY=sk-ant-...');
            return self::FAILURE;
        }

        try {
            $limit = (int) $this->option('limit');
            $delay = max((int) $this->option('delay'), 1);
            $batchSize = (int) $this->option('batch-size');
            $dryRun = $this->option('dry-run');
            $showStats = $this->option('show-stats');

            if ($showStats) {
                $this->displaySystemStats();
            }

            $articles = $this->fetchArticlesToProcess($limit);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para processar com modelo STANDARD');
                $this->line('💡 Possíveis razões:');
                $this->line('   • Todos artigos pending já foram processados');
                $this->line('   • Filtros muito restritivos (--priority, --category)');
                $this->line('   • Execute: php artisan temp-article:seed --count=10');
                return self::SUCCESS;
            }

            $this->displayArticlesSummary($articles);

            if ($dryRun) {
                $this->warn('🧪 DRY RUN - Nenhuma geração real será executada');
                $this->displayDryRunSimulation($articles->count());
                return self::SUCCESS;
            }

            if (!$this->confirmExecution($articles->count(), $delay)) {
                $this->info('⏹️ Execução cancelada pelo usuário');
                return self::SUCCESS;
            }

            $this->processArticlesInBatches($articles, $batchSize, $delay);

            if ($this->option('auto-escalate') && $this->stats['failed'] > 0) {
                $this->autoEscalateFailures();
            }

            $this->stats['total_time'] = round(microtime(true) - $startTime, 2);
            $this->displayFinalResults();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("💥 Erro crítico: " . $e->getMessage());
            Log::error('GenerateStandardCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $this->stats
            ]);
            return self::FAILURE;
        }
    }

    private function fetchArticlesToProcess(int $limit)
    {
        $query = GenerationTempArticle::query();

        $query->where(function ($q) {
            $q->where('generation_status', 'pending')
                ->whereNull('generated_at');
        });

        if ($this->option('retry-failed')) {
            $query->orWhere(function ($q) {
                $q->where('generation_status', 'failed')
                    ->where('generation_model_used', 'standard')
                    ->where('generation_retry_count', '<=', 1);
            });
        }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        if ($subcategory = $this->option('subcategory')) {
            $query->where('subcategory_slug', $subcategory);
        }

        // MongoDB não suporta orderByRaw, então fazemos ordenação manual
        $results = $query->get();
        
        // Ordenar por prioridade manualmente
        $sorted = $results->sortBy(function($article) {
            $priority = $article->generation_priority ?? 'medium';
            return match($priority) {
                'high' => 1,
                'medium' => 2,
                'low' => 3,
                default => 4
            };
        })->sortBy('created_at');
        
        return $sorted->take($limit);
    }

    private function processArticlesInBatches($articles, int $batchSize, int $delay): void
    {
        $batches = $articles->chunk($batchSize);
        $totalBatches = $batches->count();
        $currentBatch = 0;

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $this->newLine(2);

        foreach ($batches as $batch) {
            $currentBatch++;

            $this->line("🔄 Lote {$currentBatch}/{$totalBatches}");

            foreach ($batch as $article) {
                $this->processArticle($article);
                $progressBar->advance();

                if (!($currentBatch === $totalBatches && $article === $batch->last())) {
                    sleep($delay);
                }
            }

            $this->newLine();

            if ($currentBatch < $totalBatches) {
                $this->displayBatchStats($currentBatch, $totalBatches);
                $this->newLine();
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        try {
            $article->markAsGenerating('standard');

            $result = $this->claudeService->generateArticle([
                'title' => $article->title,
                'category_id' => $article->category_id,
                'category_name' => $article->category_name,
                'category_slug' => $article->category_slug,
                'subcategory_id' => $article->subcategory_id,
                'subcategory_name' => $article->subcategory_name,
                'subcategory_slug' => $article->subcategory_slug,
            ], 'standard');

            if ($result['success']) {
                $article->markAsGenerated(
                    $result['json'],
                    'standard',
                    $result['cost']
                );

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

                $category = $article->category_slug;
                if (!isset($this->stats['by_category'][$category])) {
                    $this->stats['by_category'][$category] = ['success' => 0, 'failed' => 0];
                }
                $this->stats['by_category'][$category]['success']++;

                Log::info('Claude Standard: Artigo gerado', [
                    'title' => $article->title,
                    'category' => $article->category_name,
                    'execution_time' => $executionTime,
                    'tokens' => $result['tokens_estimated'],
                    'blocks' => count($result['json']['metadata']['content_blocks'] ?? [])
                ]);
            } else {
                $article->markAsFailed($result['error'], 'standard');

                $this->stats['failed']++;

                $category = $article->category_slug;
                if (!isset($this->stats['by_category'][$category])) {
                    $this->stats['by_category'][$category] = ['success' => 0, 'failed' => 0];
                }
                $this->stats['by_category'][$category]['failed']++;

                $this->stats['errors'][] = [
                    'title' => $article->title,
                    'error' => $result['error']
                ];

                Log::warning('Claude Standard: Falha na geração', [
                    'title' => $article->title,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            $article->markAsFailed($e->getMessage(), 'standard');

            $this->stats['failed']++;
            $this->stats['errors'][] = [
                'title' => $article->title,
                'error' => $e->getMessage()
            ];

            Log::error('Claude Standard: Exceção durante geração', [
                'title' => $article->title,
                'error' => $e->getMessage()
            ]);
        }

        $this->stats['processed']++;
    }

    private function autoEscalateFailures(): void
    {
        $this->newLine();
        $this->warn('🔄 AUTO-ESCALAÇÃO: Tentando reprocessar falhas com modelo INTERMEDIATE...');
        $this->newLine();

        $failed = GenerationTempArticle::where('generation_status', 'failed')
            ->where('generation_model_used', 'standard')
            ->where('generation_retry_count', 1)
            ->limit(min($this->stats['failed'], 3))
            ->get();

        if ($failed->isEmpty()) {
            $this->line('   ℹ️ Nenhuma falha elegível para escalação automática');
            return;
        }

        $this->line("   📊 Escalando {$failed->count()} artigos para INTERMEDIATE...");

        foreach ($failed as $article) {
            $this->line("   🔄 {$article->title}");

            try {
                $article->markAsGenerating('intermediate');

                $result = $this->claudeService->generateArticle([
                    'title' => $article->title,
                    'category_id' => $article->category_id,
                    'category_name' => $article->category_name,
                    'category_slug' => $article->category_slug,
                    'subcategory_id' => $article->subcategory_id,
                    'subcategory_name' => $article->subcategory_name,
                    'subcategory_slug' => $article->subcategory_slug,
                ], 'intermediate');

                if ($result['success']) {
                    $article->markAsGenerated($result['json'], 'intermediate', $result['cost']);
                    $this->info("      ✅ Sucesso com INTERMEDIATE!");

                    $this->stats['successful']++;
                    $this->stats['failed']--;
                    $this->stats['total_cost'] += $result['cost'];
                } else {
                    $article->markAsFailed($result['error'], 'intermediate');
                    $this->warn("      ❌ Falhou também no INTERMEDIATE");
                }

                sleep(5);

            } catch (\Exception $e) {
                $this->error("      💥 Erro: {$e->getMessage()}");
            }
        }

        $this->newLine();
    }

    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   🚀 GERAÇÃO DE ARTIGOS - MODELO STANDARD                ║');
        $this->info('║   📊 Claude 3.7 Sonnet (Econômico)                      ║');
        $this->info('║   💰 Custo: 2.3x (base)                                  ║');
        $this->info('║   🎯 Uso: Primeira tentativa + Processamento em massa   ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displaySystemStats(): void
    {
        $this->info('📊 ESTATÍSTICAS DO SISTEMA:');
        $this->newLine();

        $pending = GenerationTempArticle::pending()->count();
        $generating = GenerationTempArticle::where('generation_status', 'generating')->count();
        $generated = GenerationTempArticle::where('generation_status', 'generated')->count();
        $validated = GenerationTempArticle::where('generation_status', 'validated')->count();
        $failed = GenerationTempArticle::where('generation_status', 'failed')->count();
        $published = GenerationTempArticle::where('generation_status', 'published')->count();

        $this->table(
            ['Status', 'Quantidade'],
            [
                ['⏳ Pendentes', $pending],
                ['🔄 Gerando', $generating],
                ['✅ Gerados', $generated],
                ['✔️ Validados', $validated],
                ['❌ Falhados', $failed],
                ['🌐 Publicados', $published],
            ]
        );

        $byModel = GenerationTempArticle::whereNotNull('generation_model_used')
            ->selectRaw('generation_model_used as model, COUNT(*) as count')
            ->groupBy('generation_model_used')
            ->get();

        if ($byModel->isNotEmpty()) {
            $this->newLine();
            $this->info('📈 GERADOS POR MODELO:');
            $this->table(
                ['Modelo', 'Quantidade'],
                $byModel->map(fn($item) => [$item->model, $item->count])
            );
        }

        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PARA PROCESSAR:');

        $byCategory = $articles->groupBy('category_name');

        foreach ($byCategory as $category => $items) {
            $this->line("   📁 {$category}: {$items->count()} artigos");
        }

        $byPriority = $articles->groupBy('generation_priority');
        $this->newLine();
        $this->info('🎯 POR PRIORIDADE:');
        foreach ($byPriority as $priority => $items) {
            $emoji = $priority === 'high' ? '🔴' : ($priority === 'medium' ? '🟡' : '🟢');
            $this->line("   {$emoji} {$priority}: {$items->count()} artigos");
        }

        $this->newLine();

        $this->info('👀 PREVIEW (primeiros 5):');
        $preview = $articles->take(5);
        $this->table(
            ['#', 'Título', 'Categoria', 'Prior.'],
            $preview->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 50),
                    $article->category_name,
                    $article->generation_priority ?? 'medium'
                ];
            })
        );

        if ($articles->count() > 5) {
            $this->line("   ... e mais " . ($articles->count() - 5) . " artigos");
        }

        $this->newLine();
    }

    private function displayDryRunSimulation(int $count): void
    {
        $estimatedCost = $count * 2.3;
        $estimatedTime = $count * ((int)$this->option('delay') + 20);

        $this->info('🧪 SIMULAÇÃO (DRY RUN):');
        $this->line("   📊 Artigos a processar: {$count}");
        $this->line("   💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("   ⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("   📈 Taxa de sucesso esperada: ~75-85%");
        $this->line("   ✅ Sucessos esperados: " . round($count * 0.8));
        $this->line("   ❌ Falhas esperadas: " . round($count * 0.2));
        $this->newLine();
        $this->info('💡 Para executar de verdade, remova --dry-run');
    }

    private function confirmExecution(int $count, int $delay): bool
    {
        $estimatedCost = $count * 2.3;
        $estimatedTime = $count * ($delay + 20);

        $this->warn('⚠️ CONFIRMAÇÃO DE EXECUÇÃO:');
        $this->line("📊 Artigos: {$count}");
        $this->line("💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("🤖 Modelo: claude-3-7-sonnet-20250219");
        $this->line("⏳ Delay entre artigos: {$delay}s");
        $this->newLine();

        return $this->confirm('Iniciar geração com modelo STANDARD?', true);
    }

    private function displayBatchStats(int $currentBatch, int $totalBatches): void
    {
        $successRate = $this->stats['processed'] > 0
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1)
            : 0;

        $this->line("   ✅ Sucessos: {$this->stats['successful']} | ❌ Falhas: {$this->stats['failed']} | 📈 Taxa: {$successRate}%");
    }

    private function displayFinalResults(): void
    {
        $this->newLine();
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║            🏆 RESULTADOS FINAIS - STANDARD               ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $successRate = $this->stats['processed'] > 0
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1)
            : 0;

        $avgTimePerArticle = $this->stats['processed'] > 0
            ? round($this->stats['total_time'] / $this->stats['processed'], 1)
            : 0;

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['📊 Total Processado', $this->stats['processed']],
                ['✅ Sucessos', $this->stats['successful']],
                ['❌ Falhas', $this->stats['failed']],
                ['📈 Taxa de Sucesso', $successRate . '%'],
                ['💰 Custo Total', $this->stats['total_cost'] . ' unidades'],
                ['⏱️ Tempo Total', $this->stats['total_time'] . 's'],
                ['⚡ Tempo Médio/Artigo', $avgTimePerArticle . 's'],
            ]
        );

        if (!empty($this->stats['by_category'])) {
            $this->newLine();
            $this->info('📊 PERFORMANCE POR CATEGORIA:');

            $categoryData = [];
            foreach ($this->stats['by_category'] as $category => $data) {
                $total = $data['success'] + $data['failed'];
                $rate = $total > 0 ? round(($data['success'] / $total) * 100, 1) : 0;
                $categoryData[] = [
                    $category,
                    $data['success'],
                    $data['failed'],
                    $rate . '%'
                ];
            }

            $this->table(
                ['Categoria', 'Sucessos', 'Falhas', 'Taxa'],
                $categoryData
            );
        }

        if (!empty($this->stats['errors']) && count($this->stats['errors']) <= 5) {
            $this->newLine();
            $this->warn('❌ ERROS DETECTADOS:');
            foreach (array_slice($this->stats['errors'], 0, 5) as $error) {
                $this->line("   • " . \Illuminate\Support\Str::limit($error['title'], 40));
                $this->line("     └─ " . \Illuminate\Support\Str::limit($error['error'], 60));
            }
        }

        $this->newLine();

        if ($successRate >= 85) {
            $this->info('🎉 EXCELENTE! Modelo standard está performando muito bem.');
            $this->line('✅ Continue usando este modelo como primeira opção.');
        } elseif ($successRate >= 70) {
            $this->info('👍 BOA performance do modelo standard.');
            $this->line('💡 Pequenas melhorias podem ser feitas nos títulos de entrada.');
        } elseif ($successRate >= 50) {
            $this->warn('⚠️ Performance MODERADA do modelo standard.');
            $this->line('💡 Recomendações:');
            $this->line('   • Revisar qualidade dos títulos');
            $this->line('   • Considerar usar --auto-escalate');
            $this->line('   • Escalar falhas manualmente: php artisan temp-article:generate-intermediate');
        } else {
            $this->error('🚨 Performance BAIXA do modelo standard!');
            $this->line('⚠️ AÇÃO IMEDIATA:');
            $this->line('   • Verificar qualidade dos títulos de entrada');
            $this->line('   • Revisar logs detalhados de erros');
            $this->line('   • Testar com intermediate: php artisan temp-article:generate-intermediate --limit=3');
        }

        $this->newLine();
        $this->info('📝 PRÓXIMAS AÇÕES:');

        $pendingCount = GenerationTempArticle::pending()->count();
        $failedCount = GenerationTempArticle::where('generation_status', 'failed')
            ->where('generation_model_used', 'standard')
            ->count();
        $generatedCount = GenerationTempArticle::where('generation_status', 'generated')->count();

        if ($failedCount > 0) {
            $this->warn("   ⚠️ {$failedCount} artigos falharam com standard:");
            $this->line('      🔄 php artisan temp-article:generate-intermediate --only-failed-standard --limit=5');
        }

        if ($generatedCount > 0) {
            $this->info("   ✅ {$generatedCount} artigos gerados (aguardando validação):");
            $this->line('      📦 php artisan temp-article:validate --limit=10');
        }

        if ($pendingCount > 0) {
            $this->info("   📊 {$pendingCount} artigos ainda pendentes:");
            $this->line('      🔄 php artisan temp-article:generate-standard --limit=10');
        }

        if ($failedCount == 0 && $generatedCount == 0 && $pendingCount == 0) {
            $this->info('   🎉 TODOS os artigos foram processados!');
        }
    }
}