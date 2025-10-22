<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;



use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;

/**
 * GenerateIntermediateCommand - Modelo Intermediate (Sonnet 4.0)
 * 
 * MODELO: claude-sonnet-4-20250514
 * CUSTO: 3.5x
 * QUALIDADE: Superior, mais consistente
 * VELOCIDADE: ~20-40s por artigo
 * 
 * QUANDO USAR:
 * - Artigos que falharam com modelo standard
 * - Temas complexos que precisam mais qualidade
 * - Artigos de alta prioridade
 * 
 * ESTRATÉGIA:
 * 1. Processar falhas do standard primeiro
 * 2. Artigos de prioridade alta
 * 3. Temas técnicos complexos
 * 
 * USO:
 * php artisan temp-article:generate-intermediate --limit=5
 * php artisan temp-article:generate-intermediate --only-failed-standard
 * php artisan temp-article:generate-intermediate --priority=high --limit=10
 * 
 * @author Claude Sonnet 4.5
 * @version 2.0 - Atualizado para Claude Sonnet 4.0
 */
class GenerateIntermediateCommand extends Command
{
    protected $signature = 'temp-article:generate-intermediate
                            {--limit=5 : Quantidade máxima de artigos}
                            {--delay=5 : Delay entre requisições (segundos)}
                            {--only-failed-standard : Apenas artigos que falharam no standard}
                            {--priority= : Prioridade específica (high|medium|low)}
                            {--category= : Categoria específica}
                            {--batch-size=3 : Tamanho do lote}
                            {--dry-run : Simulação sem gerar}
                            {--force-retry : Forçar retry mesmo com 3 tentativas}';

    protected $description = 'Gerar artigos usando modelo INTERMEDIATE (claude-sonnet-4) - Escalação';

    private GenerationClaudeApiService $claudeService;
    private array $stats = [
        'processed' => 0,
        'successful' => 0,
        'failed' => 0,
        'skipped' => 0,
        'total_cost' => 0.0,
        'total_time' => 0.0
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
            return self::FAILURE;
        }

        try {
            $limit = (int) $this->option('limit');
            $delay = (int) $this->option('delay');
            $batchSize = (int) $this->option('batch-size');
            $dryRun = $this->option('dry-run');

            $articles = $this->fetchArticlesToProcess($limit);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para processar com modelo INTERMEDIATE');
                $this->line('💡 Isso é BOM! Significa que o modelo standard está funcionando bem.');
                return self::SUCCESS;
            }

            $this->displayArticlesSummary($articles);

            if ($dryRun) {
                $this->warn('🧪 DRY RUN - Nenhuma geração real será executada');
                $this->displayDryRunSimulation($articles->count());
                return self::SUCCESS;
            }

            if (!$this->confirmExecution($articles->count())) {
                $this->info('⏹️ Execução cancelada pelo usuário');
                return self::SUCCESS;
            }

            $this->processArticlesInBatches($articles, $batchSize, $delay);

            $this->stats['total_time'] = round(microtime(true) - $startTime, 2);
            $this->displayFinalResults();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("💥 Erro crítico: " . $e->getMessage());
            Log::error('GenerateIntermediateCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function fetchArticlesToProcess(int $limit)
    {
        $query = GenerationTempArticle::query();

        if ($this->option('only-failed-standard')) {
            $query->where('generation_status', 'failed')
                  ->where('generation_model_used', 'standard');
        } else {
            $query->where(function($q) {
                $q->where('generation_status', 'pending')
                  ->where('generation_priority', 'high')
                  ->orWhere(function($subQ) {
                      $subQ->where('generation_status', 'failed')
                           ->where('generation_retry_count', '<', 3);
                  });
            });
        }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        if ($this->option('force-retry')) {
            $query->orWhere(function($q) {
                $q->where('generation_status', 'failed')
                  ->where('generation_retry_count', '>=', 3);
            });
        }

        return $query->orderBy('generation_priority', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->limit($limit)
                    ->get();
    }

    private function processArticlesInBatches($articles, int $batchSize, int $delay): void
    {
        $batches = $articles->chunk($batchSize);
        $totalBatches = $batches->count();
        $currentBatch = 0;

        foreach ($batches as $batch) {
            $currentBatch++;
            $this->info("🔄 Processando lote {$currentBatch}/{$totalBatches}");
            $this->newLine();

            foreach ($batch as $article) {
                $this->processArticle($article);
                
                if (!($currentBatch === $totalBatches && $article === $batch->last())) {
                    $this->line("⏳ Aguardando {$delay}s...");
                    sleep($delay);
                }
            }

            $this->newLine();
            $this->displayIntermediateStats($currentBatch, $totalBatches);
            $this->newLine();
        }
    }

    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        $this->line("📝 Processando: {$article->title}");
        $this->line("   📁 Categoria: {$article->category_name} > {$article->subcategory_name}");
        $this->line("   🎯 Prioridade: {$article->generation_priority}");
        $this->line("   🔄 Tentativas anteriores: " . ($article->generation_retry_count ?? 0));

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
                $article->markAsGenerated(
                    $result['json'],
                    'intermediate',
                    $result['cost']
                );

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->info("   ✅ Gerado com sucesso!");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->line("   💰 Custo: {$result['cost']} unidades");
                $this->line("   📊 Tokens: ~{$result['tokens_estimated']}");

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

            } else {
                $article->markAsFailed($result['error'], 'intermediate');

                $this->error("   ❌ Falha: {$result['error']}");

                $this->stats['failed']++;
            }

        } catch (\Exception $e) {
            $article->markAsFailed($e->getMessage(), 'intermediate');
            $this->error("   💥 Exceção: " . $e->getMessage());
            $this->stats['failed']++;
        }

        $this->stats['processed']++;
        $this->newLine();
    }

    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   🚀 GERAÇÃO DE ARTIGOS - MODELO INTERMEDIATE            ║');
        $this->info('║   📊 Claude Sonnet 4.0 (Escalação)                      ║');
        $this->info('║   💰 Custo: 3.5x                                         ║');
        $this->info('║   🎯 Uso: Falhas do standard + Alta prioridade          ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PARA PROCESSAR:');
        $this->table(
            ['#', 'Título', 'Categoria', 'Prioridade', 'Status', 'Tentativas'],
            $articles->map(function($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 50),
                    $article->category_name,
                    $article->generation_priority ?? 'medium',
                    $article->generation_status ?? 'pending',
                    $article->generation_retry_count ?? 0
                ];
            })
        );
        $this->newLine();
    }

    private function displayDryRunSimulation(int $count): void
    {
        $estimatedCost = $count * 3.5;
        $estimatedTime = $count * ((int)$this->option('delay') + 30);

        $this->info('🧪 SIMULAÇÃO (DRY RUN):');
        $this->line("   📊 Artigos a processar: {$count}");
        $this->line("   💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("   ⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("   📈 Taxa de sucesso esperada: ~85-95%");
        $this->newLine();
        $this->info('💡 Para executar de verdade, remova --dry-run');
    }

    private function confirmExecution(int $count): bool
    {
        $estimatedCost = $count * 3.5;
        $estimatedTime = $count * ((int)$this->option('delay') + 30);

        $this->warn('⚠️ CONFIRMAÇÃO:');
        $this->line("📊 Artigos: {$count}");
        $this->line("💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("🤖 Modelo: claude-sonnet-4-20250514");
        $this->newLine();

        return $this->confirm('Continuar com a geração?');
    }

    private function displayIntermediateStats(int $currentBatch, int $totalBatches): void
    {
        $successRate = $this->stats['processed'] > 0 
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1) 
            : 0;

        $this->info("📊 PROGRESSO - Lote {$currentBatch}/{$totalBatches}:");
        $this->line("   ✅ Sucessos: {$this->stats['successful']}");
        $this->line("   ❌ Falhas: {$this->stats['failed']}");
        $this->line("   📈 Taxa: {$successRate}%");
        $this->line("   💰 Custo acumulado: {$this->stats['total_cost']} unidades");
    }

    private function displayFinalResults(): void
    {
        $this->newLine();
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║         🏆 RESULTADOS FINAIS - INTERMEDIATE              ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $successRate = $this->stats['processed'] > 0 
            ? round(($this->stats['successful'] / $this->stats['processed']) * 100, 1) 
            : 0;

        $avgCostPerArticle = $this->stats['successful'] > 0 
            ? round($this->stats['total_cost'] / $this->stats['successful'], 2) 
            : 0;

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['📊 Processados', $this->stats['processed']],
                ['✅ Sucessos', $this->stats['successful']],
                ['❌ Falhas', $this->stats['failed']],
                ['📈 Taxa de Sucesso', $successRate . '%'],
                ['💰 Custo Total', $this->stats['total_cost'] . ' unidades'],
                ['💵 Custo Médio/Artigo', $avgCostPerArticle . ' unidades'],
                ['⏱️ Tempo Total', $this->stats['total_time'] . 's'],
            ]
        );

        $this->newLine();

        if ($successRate >= 90) {
            $this->info('🎉 EXCELENTE! Modelo intermediate resolveu os casos complexos.');
            $this->line('✅ Justifica o custo adicional para estes artigos.');
        } elseif ($successRate >= 75) {
            $this->info('👍 BOA performance com intermediate.');
            $this->line('💡 Continue monitorando os casos de falha.');
        } else {
            $this->warn('⚠️ Performance ABAIXO do esperado para intermediate.');
            $this->line('💡 Considere:');
            $this->line('   • Revisar qualidade dos títulos de entrada');
            $this->line('   • Escalar casos críticos para modelo premium');
            $this->line('   • Verificar logs para erros recorrentes');
        }

        $this->newLine();
        $this->info('📝 PRÓXIMAS AÇÕES:');
        
        $pendingCount = GenerationTempArticle::pending()->count();
        $failedCount = GenerationTempArticle::where('generation_status', 'failed')->count();
        $generatedCount = GenerationTempArticle::where('generation_status', 'generated')->count();

        $this->line("   📊 Ainda pendentes: {$pendingCount}");
        $this->line("   ❌ Falhados: {$failedCount}");
        $this->line("   ✅ Gerados (aguardando validação): {$generatedCount}");

        if ($failedCount > 0) {
            $this->newLine();
            $this->warn("⚠️ {$failedCount} artigos falharam. Considere:");
            $this->line('   🔴 php artisan temp-article:generate-premium --limit=3 (casos críticos)');
        }

        if ($generatedCount > 0) {
            $this->newLine();
            $this->info("✅ {$generatedCount} artigos gerados. Próximo passo:");
            $this->line('   📦 php artisan temp-article:validate');
        }
    }
}