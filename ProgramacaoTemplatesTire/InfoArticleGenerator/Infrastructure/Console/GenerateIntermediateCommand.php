<?php

namespace Src\InfoArticleGenerator\Infrastructure\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\InfoArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\InfoArticleGenerator\Application\Services\GenerationClaudeApiService;

/**
 * GenerateIntermediateCommand - Modelo Intermediário
 * 
 * QUANDO USAR:
 * - Artigos que falharam com modelo standard
 * - Temas complexos que precisam mais qualidade
 * - Artigos de alta prioridade
 * 
 * CUSTO: 2.3x mais caro que standard
 * QUALIDADE: Superior, mais consistente
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
 * @author Claude Sonnet 4
 * @version 1.0
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

    protected $description = 'Gerar artigos usando modelo INTERMEDIATE (claude-3-7-sonnet) - Escalação';

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

            // Buscar artigos para processar
            $articles = $this->fetchArticlesToProcess($limit);

            if ($articles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para processar com modelo INTERMEDIATE');
                return self::SUCCESS;
            }

            $this->displayArticlesSummary($articles);

            if ($dryRun) {
                $this->warn('🧪 DRY RUN - Nenhuma geração real será executada');
                return self::SUCCESS;
            }

            // Confirmar execução
            if (!$this->confirmExecution($articles->count())) {
                $this->info('⏹️ Execução cancelada pelo usuário');
                return self::SUCCESS;
            }

            // Processar artigos em lotes
            $this->processArticlesInBatches($articles, $batchSize, $delay);

            // Resultados finais
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

    /**
     * Buscar artigos para processar
     */
    private function fetchArticlesToProcess(int $limit)
    {
        $query = GenerationTempArticle::query();

        // if ($this->option('only-failed-standard')) {
        //     // Artigos que falharam com standard
        //     $query->where('generation_status', 'failed')
        //           ->where('generation_model_used', 'standard');
        // } else {
        //     // Artigos pending de alta prioridade ou que falharam
        //     $query->where(function($q) {
        //         $q->where('generation_status', 'pending')
        //           ->where('generation_priority', 'high')
        //           ->orWhere(function($subQ) {
        //               $subQ->where('generation_status', 'failed')
        //                    ->where('generation_retry_count', '<', 3);
        //           });
        //     });
        // }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($category = $this->option('category')) {
            $query->where('category_slug', $category);
        }

        // Forçar retry mesmo com 3+ tentativas
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

    /**
     * Processar artigos em lotes
     */
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
                
                // Delay entre requisições (exceto último artigo do último lote)
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

    /**
     * Processar um artigo individual
     */
    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        $this->line("📝 Processando: {$article->title}");
        $this->line("   📁 Categoria: {$article->category_name} > {$article->subcategory_name}");
        $this->line("   🎯 Prioridade: {$article->generation_priority}");
        $this->line("   🔄 Tentativas anteriores: " . ($article->generation_retry_count ?? 0));

        try {
            // Marcar como gerando
            $article->markAsGenerating('intermediate');

            // Chamar API
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
                // Sucesso
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
                // Falha
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

    /**
     * Exibir header
     */
    private function displayHeader(): void
    {
        $this->info('🚀 GERAÇÃO DE ARTIGOS - MODELO INTERMEDIATE');
        $this->info('📊 Claude 3.7 Sonnet (Escalação)');
        $this->info('💰 Custo: 2.3x modelo standard');
        $this->info('🎯 Uso: Falhas do standard + Alta prioridade');
        $this->newLine();
    }

    /**
     * Exibir resumo dos artigos
     */
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

    /**
     * Confirmar execução
     */
    private function confirmExecution(int $count): bool
    {
        $estimatedCost = $count * 2.3;
        $estimatedTime = $count * ((int)$this->option('delay') + 30); // delay + ~30s por artigo

        $this->warn('⚠️ CONFIRMAÇÃO:');
        $this->line("📊 Artigos: {$count}");
        $this->line("💰 Custo estimado: {$estimatedCost} unidades");
        $this->line("⏱️ Tempo estimado: " . gmdate("H:i:s", $estimatedTime));
        $this->line("🤖 Modelo: claude-3-7-sonnet-20250219");
        $this->newLine();

        return $this->confirm('Continuar com a geração?');
    }

    /**
     * Estatísticas intermediárias
     */
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

    /**
     * Resultados finais
     */
    private function displayFinalResults(): void
    {
        $this->newLine();
        $this->info('🏆 RESULTADOS FINAIS - MODELO INTERMEDIATE');
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
                ['Processados', $this->stats['processed']],
                ['✅ Sucessos', $this->stats['successful']],
                ['❌ Falhas', $this->stats['failed']],
                ['📈 Taxa de Sucesso', $successRate . '%'],
                ['💰 Custo Total', $this->stats['total_cost'] . ' unidades'],
                ['💵 Custo Médio/Artigo', $avgCostPerArticle . ' unidades'],
                ['⏱️ Tempo Total', $this->stats['total_time'] . 's'],
            ]
        );

        $this->newLine();

        // Recomendações
        if ($successRate >= 85) {
            $this->info('🎉 EXCELENTE! Modelo intermediate está performando muito bem.');
        } elseif ($successRate >= 70) {
            $this->info('👍 BOA performance. Continue monitorando.');
        } else {
            $this->warn('⚠️ Performance ABAIXO do esperado. Considere:');
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
            $this->line('   📦 php artisan temp-article:validate (validar JSONs)');
            $this->line('   🚀 php artisan temp-article:publish (publicar para produção)');
        }
    }
}