<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceSubcategory;

/**
 * GeneratePremiumCommand - Modelo Premium (Sonnet 4.5)
 * 
 * MODELO: claude-sonnet-4-5-20250929
 * CUSTO: 4.0x (CARO!)
 * QUALIDADE: Máxima disponível
 * VELOCIDADE: ~30-60s por artigo
 * 
 * ⚠️ ATENÇÃO: USE COM MODERAÇÃO!
 * 
 * ✅ CORREÇÕES v2.1:
 * - Busca category/subcategory do MySQL antes de gerar
 * - Mescla dados após resposta do Claude
 * - Atualiza campos na raiz do MongoDB
 * 
 * QUANDO USAR:
 * - Artigos que falharam 2+ vezes
 * - Temas extremamente complexos
 * - Artigos flagship/pillar content
 * 
 * USO:
 * php artisan temp-article:generate-premium --limit=1
 * php artisan temp-article:generate-premium --only-critical
 * 
 * @author Claude Sonnet 4.5
 * @version 2.1 - Corrigido para buscar dados do MySQL
 */
class GeneratePremiumCommand extends Command
{
    protected $signature = 'temp-article:generate-premium
                            {--limit=1 : Quantidade máxima (MÁXIMO RECOMENDADO: 3)}
                            {--delay=10 : Delay entre requisições (mínimo: 10s)}
                            {--only-critical : Apenas artigos críticos (falharam 2+ vezes)}
                            {--priority=high : Prioridade mínima (high apenas)}
                            {--category= : Categoria específica}
                            {--dry-run : Simulação sem gerar}
                            {--force-confirm : Pular confirmação (cuidado!)}
                            {--max-cost=20 : Limite máximo de custo}';

    protected $description = 'Gerar artigos usando modelo PREMIUM (claude-sonnet-4-5) - ÚLTIMA INSTÂNCIA';

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

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return Command::FAILURE;
        }

        $startTime = microtime(true);

        $this->displayWarningHeader();

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
        $this->displayCostEstimate($articles);

        if ($this->option('dry-run')) {
            $this->info('🏁 DRY-RUN: Simulação concluída');
            return self::SUCCESS;
        }

        if (!$this->option('force-confirm')) {
            if (!$this->confirm('⚠️ CONFIRMAR uso do modelo PREMIUM (custo alto)?', false)) {
                $this->info('❌ Operação cancelada');
                return self::SUCCESS;
            }
        }

        $this->newLine();

        foreach ($articles as $index => $article) {
            $articles_count = ($index + 1 / $articles->count());
            $this->warn("⚡ [{$articles_count}] PREMIUM: {$article->title}");
            $this->processArticle($article);

            if ($index < $articles->count() - 1) {
                sleep(max(10, $this->option('delay')));
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

        if ($this->option('only-critical')) {
            // Artigos que falharam 2+ vezes
            $query->where('generation_status', 'failed')
                ->where('generation_retry_count', '>=', 2);
        } else {
            // Artigos pending de alta prioridade
            $query->where('generation_status', 'pending')
                ->where('generation_priority', 'high');
        }

        if ($category = $this->option('category')) {
            $categoryModel = MaintenanceCategory::where('slug', $category)->first();
            if ($categoryModel) {
                $query->where('maintenance_category_id', $categoryModel->id);
            }
        }

        return $query->orderBy('generation_priority', 'desc')
            ->limit(min(3, $this->option('limit'))) // Máximo 3!
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
            $this->line("   🔥 Prioridade: " . strtoupper($article->generation_priority));
            $this->line("   💥 Tentativas anteriores: " . ($article->generation_retry_count ?? 0));
            $this->newLine();

            $article->markAsGenerating('premium');

            $this->warn('   ⚙️ Chamando Claude Sonnet 4.5 (aguarde ~30-60s)...');

            $result = $this->claudeService->generateArticle([
                'title' => $article->title,
                'category_name' => $category->name,
                'category_slug' => $category->slug,
                'subcategory_name' => $subcategory->name,
                'subcategory_slug' => $subcategory->slug,
            ], 'premium');

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

                $article->markAsGenerated($completeData, 'premium', $result['cost']);

                // Atualizar raiz
                $article->update([
                    'category_name' => $category->name,
                    'category_slug' => $category->slug,
                    'subcategory_name' => $subcategory->name,
                    'subcategory_slug' => $subcategory->slug,
                ]);

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->info("   🎉 SUCESSO COM MODELO PREMIUM!");
                $this->line("   ⏱️ Tempo: {$executionTime}s");
                $this->line("   💰 Custo: {$result['cost']} unidades (4.0x standard)");
                $this->line("   📊 Tokens: ~{$result['tokens_estimated']}");
                $this->line("   📏 Blocos gerados: " . count($completeData['metadata']['content_blocks'] ?? []));

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

                Log::info('Claude Premium: Sucesso', [
                    'article_id' => $article->_id,
                    'title' => $article->title,
                    'category' => $category->name,
                    'cost' => $result['cost'],
                ]);
            } else {
                $article->markAsFailed($result['error'], 'premium');
                $this->error("   ❌ FALHA MESMO COM PREMIUM: {$result['error']}");
                $this->warn("   ⚠️ Artigo pode ter problemas fundamentais");
                $this->stats['failed']++;

                Log::warning('Claude Premium: Falha', [
                    'article_id' => $article->_id,
                    'error' => $result['error'],
                ]);
            }
        } catch (\Exception $e) {
            $article->markAsFailed($e->getMessage(), 'premium');
            $this->error("   💥 Exceção: " . $e->getMessage());
            $this->stats['failed']++;

            Log::error('Claude Premium: Exceção', [
                'article_id' => $article->_id ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
        }

        $this->stats['processed']++;
        $this->newLine();
    }

    private function displayWarningHeader(): void
    {
        $this->warn('╔═══════════════════════════════════════════════════════════╗');
        $this->warn('║   ⚠️ MODELO PREMIUM - CUSTO ALTO - USE COM CUIDADO! ⚠️  ║');
        $this->warn('╠═══════════════════════════════════════════════════════════╣');
        $this->warn('║   🚀 Claude Sonnet 4.5 (Máxima Qualidade)               ║');
        $this->warn('║   💰 Custo: 4.0x (muito caro!)                           ║');
        $this->warn('║   ✅ v2.1 - Com busca MySQL de categorias               ║');
        $this->warn('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PARA PROCESSAR (PREMIUM):');
        $this->table(
            ['#', 'Título', 'Cat.ID', 'SubCat.ID', 'Prioridade', 'Tentativas'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 50),
                    $article->maintenance_category_id,
                    $article->maintenance_subcategory_id,
                    strtoupper($article->generation_priority ?? 'high'),
                    $article->generation_retry_count ?? 0,
                ];
            })
        );
        $this->newLine();
    }

    private function displayCostEstimate($articles): void
    {
        $estimatedCost = $articles->count() * 4.0; // 4.0x standard por artigo

        $this->warn('💰 ESTIMATIVA DE CUSTO:');
        $this->line("   Artigos: {$articles->count()}");
        $this->line("   Custo estimado: ~{$estimatedCost} unidades");
        $this->line("   (baseado em 4.0x custo standard por artigo)");
        $this->newLine();

        if ($estimatedCost > $this->option('max-cost')) {
            $this->error("⚠️ AVISO: Custo estimado excede limite de {$this->option('max-cost')}!");
            $this->line("   Considere reduzir --limit ou aumentar --max-cost");
            $this->newLine();
        }
    }

    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                📊 ESTATÍSTICAS FINAIS (PREMIUM)          ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Processados: {$this->stats['processed']}");
        $this->line("🎉 Sucesso: {$this->stats['successful']}");
        $this->line("❌ Falhas: {$this->stats['failed']}");
        $this->line("⏭️ Pulados: {$this->stats['skipped']}");
        $this->warn("💰 Custo total: {$this->stats['total_cost']} unidades (PREMIUM)");
        $this->line("⏱️ Tempo total: {$this->stats['total_time']}s");
        $this->newLine();

        if ($this->stats['total_cost'] > 15) {
            $this->warn('⚠️ CUSTO ALTO! Considere usar modelos mais econômicos no futuro.');
        }
    }
}
