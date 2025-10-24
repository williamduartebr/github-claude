<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceSubcategory;

/**
 * GenerateIntermediateCommand - Modelo Intermediate (Sonnet 4.0)
 * 
 * MODELO: claude-sonnet-4-20250514
 * CUSTO: 3.5x
 * QUALIDADE: Superior, mais consistente
 * VELOCIDADE: ~20-40s por artigo
 * 
 * ✅ CORREÇÕES v2.1:
 * - Busca category/subcategory do MySQL antes de gerar
 * - Mescla dados após resposta do Claude
 * - Atualiza campos na raiz do MongoDB
 * 
 * QUANDO USAR:
 * - Artigos que falharam com modelo standard
 * - Temas complexos que precisam mais qualidade
 * - Artigos de alta prioridade
 * 
 * USO:
 * php artisan temp-article:generate-intermediate --limit=5
 * php artisan temp-article:generate-intermediate --only-failed-standard
 * 
 * @author Claude Sonnet 4.5
 * @version 2.1 - Corrigido para buscar dados do MySQL
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

        $articles = $this->getArticlesToProcess();

        if ($articles->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo encontrado para processar!');
            return self::SUCCESS;
        }

        $this->displayArticlesSummary($articles);

        if ($this->option('dry-run')) {
            $this->info('🏁 DRY-RUN: Simulação concluída');
            return self::SUCCESS;
        }

        if (!$this->confirm('Deseja continuar com a geração?', true)) {
            $this->info('❌ Operação cancelada');
            return self::SUCCESS;
        }

        $this->newLine();

        foreach ($articles as $index => $article) {
            $articles_count = ($index + 1 / $articles->count());
            $this->info("📄 [{$articles_count}] {$article->title}");
            $this->processArticle($article);

            if ($index < $articles->count() - 1) {
                sleep($this->option('delay'));
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $this->stats['total_time'] = $totalTime;

        $this->displayFinalStats();

        return $this->stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function getArticlesToProcess()
    {
        $query = GenerationTempArticle::query();

        if ($this->option('only-failed-standard')) {
            $query->where('generation_status', 'failed')
                ->where('generation_model_used', 'standard');
        } else {
            $query->where('generation_status', 'pending')
                ->orWhere(function ($q) {
                    $q->where('generation_status', 'failed')
                        ->where('generation_retry_count', '<', 3);
                });
        }

        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($category = $this->option('category')) {
            $categoryModel = MaintenanceCategory::where('slug', $category)->first();
            if ($categoryModel) {
                $query->where('maintenance_category_id', $categoryModel->id);
            }
        }

        return $query->orderBy('generation_priority', 'desc')
            ->limit($this->option('limit'))
            ->get();
    }

    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        try {
            // Buscar categoria e subcategoria
            $category = MaintenanceCategory::find($article->maintenance_category_id);
            $subcategory = MaintenanceSubcategory::find($article->maintenance_subcategory_id);

            if (!$category || !$subcategory) {
                $error = "Category/Subcategory não encontrada";
                $this->error("   ❌ {$error}");
                $this->stats['skipped']++;
                return;
            }

            $this->line("   🏷️ Categoria: {$category->name} > {$subcategory->name}");
            $this->line("   🔄 Tentativa: " . ($article->generation_retry_count ?? 0));

            $article->markAsGenerating('intermediate');

            $result = $this->claudeService->generateArticle([
                'title' => $article->title,
                'category_name' => $category->name,
                'category_slug' => $category->slug,
                'subcategory_name' => $subcategory->name,
                'subcategory_slug' => $subcategory->slug,
            ], 'intermediate');

            if ($result['success']) {
                // Mesclar dados
                $completeData = array_merge($result['json'], [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category_slug' => $category->slug,
                    'subcategory_id' => $subcategory->id,
                    'subcategory_name' => $subcategory->name,
                    'subcategory_slug' => $subcategory->slug,
                ]);

                $article->markAsGenerated($completeData, 'intermediate', $result['cost']);

                // Atualizar raiz
                $article->update([
                    'category_name' => $category->name,
                    'category_slug' => $category->slug,
                    'subcategory_name' => $subcategory->name,
                    'subcategory_slug' => $subcategory->slug,
                ]);

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->info("   ✅ Gerado com sucesso!");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->line("   💰 Custo: {$result['cost']} unidades");
                $this->line("   📊 Tokens: ~{$result['tokens_estimated']}");

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

                Log::info('Claude Intermediate: Sucesso', [
                    'article_id' => $article->_id,
                    'title' => $article->title,
                    'category' => $category->name,
                    'cost' => $result['cost'],
                ]);
            } else {
                $article->markAsFailed($result['error'], 'intermediate');
                $this->error("   ❌ Falha: {$result['error']}");
                $this->stats['failed']++;

                Log::warning('Claude Intermediate: Falha', [
                    'article_id' => $article->_id,
                    'error' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            $article->markAsFailed($e->getMessage(), 'intermediate');
            $this->error("   💥 Exceção: " . $e->getMessage());
            $this->stats['failed']++;

            Log::error('Claude Intermediate: Exceção', [
                'article_id' => $article->_id ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
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
        $this->info('║   ✅ v2.1 - Com busca MySQL de categorias               ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PARA PROCESSAR:');
        $this->table(
            ['#', 'Título', 'Cat.ID', 'SubCat.ID', 'Prioridade', 'Status', 'Tentativas'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 50),
                    $article->maintenance_category_id,
                    $article->maintenance_subcategory_id,
                    strtoupper($article->generation_priority ?? 'medium'),
                    $article->generation_status ?? 'pending',
                    $article->generation_retry_count ?? 0,
                ];
            })
        );
        $this->newLine();
    }

    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                    📊 ESTATÍSTICAS FINAIS                ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Processados: {$this->stats['processed']}");
        $this->line("🎉 Sucesso: {$this->stats['successful']}");
        $this->line("❌ Falhas: {$this->stats['failed']}");
        $this->line("⏭️ Pulados: {$this->stats['skipped']}");
        $this->line("💰 Custo total: {$this->stats['total_cost']} unidades");
        $this->line("⏱️ Tempo total: {$this->stats['total_time']}s");
        $this->newLine();
    }
}
