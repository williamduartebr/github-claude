<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;
use Src\GenericArticleGenerator\Application\Services\GenerationClaudeApiService;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceSubcategory;

/**
 * GenerateStandardCommand - Modelo Standard (Econômico)
 * 
 * MODELO: claude-3-7-sonnet-20250219
 * CUSTO: 2.3x (base)
 * QUALIDADE: Boa para maioria dos artigos
 * VELOCIDADE: Rápida (~15-30s por artigo)
 * 
 * ✅ CORREÇÕES v2.1:
 * - Busca category/subcategory do MySQL antes de gerar
 * - Mescla dados após resposta do Claude
 * - Atualiza campos na raiz do MongoDB para queries
 * - Validação de existência de category/subcategory
 * - Logging detalhado de todo o processo
 * 
 * QUANDO USAR:
 * - Primeira tentativa de geração (sempre)
 * - Artigos de complexidade baixa/média
 * - Processamento em massa
 * - Uso contínuo automatizado
 * 
 * USO:
 * php artisan temp-article:generate-standard --limit=10
 * php artisan temp-article:generate-standard --category=oleo --limit=5
 * php artisan temp-article:generate-standard --priority=high
 * 
 * @author Claude Sonnet 4.5
 * @version 2.1 - Corrigido para buscar dados do MySQL
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

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return Command::FAILURE;
        }

        $startTime = microtime(true);

        $this->displayHeader();

        if (!$this->claudeService->isConfigured()) {
            $this->error('❌ Claude API Key não configurada!');
            $this->line('   Configure CLAUDE_API_KEY no arquivo .env');
            return self::FAILURE;
        }

        $articles = $this->getArticlesToProcess();

        if ($articles->isEmpty()) {
            $this->warn('⚠️ Nenhum artigo encontrado para processar!');
            $this->displaySuggestions();
            return self::SUCCESS;
        }

        if ($this->option('show-stats')) {
            $this->displayPreGenerationStats($articles);
        }

        if ($this->option('dry-run')) {
            $this->displayArticlesSummary($articles);
            $this->info('🏁 DRY-RUN: Simulação concluída sem gerar artigos');
            return self::SUCCESS;
        }

        $this->displayArticlesSummary($articles);

        $this->newLine();
        $this->processBatchWithProgress($articles);

        if ($this->option('auto-escalate') && $this->stats['failed'] > 0) {
            $this->autoEscalateFailures();
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $this->stats['total_time'] = $totalTime;

        $this->displayFinalStats();

        return $this->stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function getArticlesToProcess()
    {
        $query = GenerationTempArticle::query();

        // Status base: pending ou retry-failed se solicitado
        if ($this->option('retry-failed')) {
            $query->where(function ($q) {
                $q->where('generation_status', 'pending')
                    ->orWhere(function ($subQ) {
                        $subQ->where('generation_status', 'failed')
                            ->where('generation_model_used', 'standard')
                            ->where('generation_retry_count', 1);
                    });
            });
        } else {
            $query->where('generation_status', 'pending');
        }

        // Filtros opcionais
        if ($priority = $this->option('priority')) {
            $query->where('generation_priority', $priority);
        }

        if ($category = $this->option('category')) {
            // Buscar category_id pelo slug
            $categoryModel = MaintenanceCategory::where('slug', $category)->first();
            if ($categoryModel) {
                $query->where('maintenance_category_id', $categoryModel->id);
            }
        }

        if ($subcategory = $this->option('subcategory')) {
            // Buscar subcategory_id pelo slug
            $subcategoryModel = MaintenanceSubcategory::where('slug', $subcategory)->first();
            if ($subcategoryModel) {
                $query->where('maintenance_subcategory_id', $subcategoryModel->id);
            }
        }

        return $query->orderBy('generation_priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($this->option('limit'))
            ->get();
    }

    private function processBatchWithProgress($articles): void
    {
        $delay = max(1, $this->option('delay'));
        $batchSize = max(1, $this->option('batch-size'));
        $batches = $articles->chunk($batchSize);
        $totalBatches = $batches->count();

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Iniciando...');
        $progressBar->start();

        $currentBatch = 0;

        foreach ($batches as $batch) {
            $currentBatch++;

            foreach ($batch as $article) {
                $progressBar->setMessage("Gerando: " . \Illuminate\Support\Str::limit($article->title, 40));

                $this->processArticle($article);

                $progressBar->advance();

                // Delay entre artigos (exceto no último artigo do último batch)
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

    /**
     * Processa um artigo individual
     * 
     * FLUXO CORRIGIDO:
     * 1. Busca category e subcategory do MySQL
     * 2. Valida existência dos dados
     * 3. Envia dados necessários para Claude (name, slug) para prompt
     * 4. Recebe resposta do Claude
     * 5. Mescla dados de category/subcategory na resposta
     * 6. Salva JSON completo em generated_json
     * 7. Atualiza campos na raiz do MongoDB para facilitar queries
     */
    private function processArticle(GenerationTempArticle $article): void
    {
        $articleStartTime = microtime(true);

        try {
            // 1. BUSCAR CATEGORIA E SUBCATEGORIA DO MYSQL
            $category = MaintenanceCategory::find($article->maintenance_category_id);
            $subcategory = MaintenanceSubcategory::find($article->maintenance_subcategory_id);

            // 2. VALIDAR EXISTÊNCIA
            if (!$category) {
                $error = "Category ID {$article->maintenance_category_id} não encontrada no MySQL";
                $this->logAndSkipArticle($article, $error);
                return;
            }

            if (!$subcategory) {
                $error = "Subcategory ID {$article->maintenance_subcategory_id} não encontrada no MySQL";
                $this->logAndSkipArticle($article, $error);
                return;
            }

            Log::info('Claude Standard: Iniciando geração', [
                'article_id' => $article->_id,
                'title' => $article->title,
                'category' => $category->name,
                'category_slug' => $category->slug,
                'subcategory' => $subcategory->name,
                'subcategory_slug' => $subcategory->slug,
            ]);

            // 3. MARCAR COMO EM GERAÇÃO
            $article->markAsGenerating('standard');

            // 4. ENVIAR PARA CLAUDE (dados necessários para o prompt)
            $result = $this->claudeService->generateArticle([
                'title' => $article->title,
                'category_name' => $category->name,
                'category_slug' => $category->slug,
                'subcategory_name' => $subcategory->name,
                'subcategory_slug' => $subcategory->slug,
            ], 'standard');

            // 5. PROCESSAR RESULTADO
            if ($result['success']) {
                // 6. MESCLAR DADOS DE CATEGORIA/SUBCATEGORIA
                $generatedData = $result['json'];

                $completeData = array_merge($generatedData, [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category_slug' => $category->slug,
                    'subcategory_id' => $subcategory->id,
                    'subcategory_name' => $subcategory->name,
                    'subcategory_slug' => $subcategory->slug,
                ]);

                // 7. SALVAR JSON COMPLETO E ATUALIZAR RAIZ
                $article->markAsGenerated(
                    $completeData,
                    'standard',
                    $result['cost']
                );

                // 8. ATUALIZAR CAMPOS NA RAIZ DO MONGODB (para queries rápidas)
                $article->update([
                    'category_name' => $category->name,
                    'category_slug' => $category->slug,
                    'subcategory_name' => $subcategory->name,
                    'subcategory_slug' => $subcategory->slug,
                ]);

                $executionTime = round(microtime(true) - $articleStartTime, 2);

                $this->stats['successful']++;
                $this->stats['total_cost'] += $result['cost'];

                $categorySlug = $category->slug;
                if (!isset($this->stats['by_category'][$categorySlug])) {
                    $this->stats['by_category'][$categorySlug] = ['success' => 0, 'failed' => 0];
                }
                $this->stats['by_category'][$categorySlug]['success']++;

                Log::info('Claude Standard: Artigo gerado com sucesso', [
                    'article_id' => $article->_id,
                    'title' => $article->title,
                    'category' => $category->name,
                    'subcategory' => $subcategory->name,
                    'execution_time' => $executionTime,
                    'tokens' => $result['tokens_estimated'] ?? 'N/A',
                    'blocks' => count($completeData['metadata']['content_blocks'] ?? []),
                    'cost' => $result['cost'],
                ]);
            } else {
                // FALHA NA GERAÇÃO
                $article->markAsFailed($result['error'], 'standard');

                $this->stats['failed']++;

                $categorySlug = $category->slug ?? 'unknown';
                if (!isset($this->stats['by_category'][$categorySlug])) {
                    $this->stats['by_category'][$categorySlug] = ['success' => 0, 'failed' => 0];
                }
                $this->stats['by_category'][$categorySlug]['failed']++;

                $this->stats['errors'][] = [
                    'title' => $article->title,
                    'error' => $result['error']
                ];

                Log::warning('Claude Standard: Falha na geração', [
                    'article_id' => $article->_id,
                    'title' => $article->title,
                    'category' => $category->name ?? 'N/A',
                    'subcategory' => $subcategory->name ?? 'N/A',
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
                'article_id' => $article->_id ?? 'N/A',
                'title' => $article->title,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->stats['processed']++;
    }

    /**
     * Loga e pula artigo quando há erro de validação
     */
    private function logAndSkipArticle(GenerationTempArticle $article, string $error): void
    {
        $this->stats['skipped']++;
        $this->stats['errors'][] = [
            'title' => $article->title,
            'error' => $error
        ];

        Log::error('Claude Standard: Artigo pulado', [
            'article_id' => $article->_id,
            'title' => $article->title,
            'error' => $error
        ]);

        // Marcar como failed no banco para não processar novamente
        $article->update([
            'generation_status' => 'validation_error',
            'generation_error' => $error,
        ]);
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
                // Buscar categoria e subcategoria
                $category = MaintenanceCategory::find($article->maintenance_category_id);
                $subcategory = MaintenanceSubcategory::find($article->maintenance_subcategory_id);

                if (!$category || !$subcategory) {
                    $this->error("      ❌ Category/Subcategory não encontrada");
                    continue;
                }

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

                    $this->info("      ✅ Sucesso com INTERMEDIATE! Custo: {$result['cost']}");
                } else {
                    $article->markAsFailed($result['error'], 'intermediate');
                    $this->error("      ❌ Falhou no INTERMEDIATE: {$result['error']}");
                }

                sleep(5);
            } catch (\Exception $e) {
                $this->error("      💥 Exceção: " . $e->getMessage());
            }
        }
    }

    private function displayHeader(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║   🚀 GERAÇÃO DE ARTIGOS - MODELO STANDARD                ║');
        $this->info('║   📊 Claude 3.7 Sonnet (Econômico)                      ║');
        $this->info('║   💰 Custo: 2.3x (base)                                  ║');
        $this->info('║   ✅ v2.1 - Com busca MySQL de categorias               ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function displayArticlesSummary($articles): void
    {
        $this->info('📋 ARTIGOS PARA PROCESSAR:');
        $this->table(
            ['#', 'Título', 'Cat.ID', 'SubCat.ID', 'Prioridade', 'Status'],
            $articles->map(function ($article, $index) {
                return [
                    $index + 1,
                    \Illuminate\Support\Str::limit($article->title, 50),
                    $article->maintenance_category_id,
                    $article->maintenance_subcategory_id,
                    strtoupper($article->generation_priority ?? 'medium'),
                    $article->generation_status ?? 'pending',
                ];
            })
        );
        $this->newLine();
    }

    private function displayBatchStats(int $current, int $total): void
    {
        $this->line("📦 Batch {$current}/{$total} concluído");
        $this->line("   ✅ Sucesso: {$this->stats['successful']}");
        $this->line("   ❌ Falhas: {$this->stats['failed']}");
        $this->line("   ⏭️ Pulados: {$this->stats['skipped']}");
    }

    private function displayPreGenerationStats($articles): void
    {
        $priorities = $articles->groupBy('generation_priority');
        $this->info('📊 ESTATÍSTICAS PRÉ-GERAÇÃO:');
        foreach ($priorities as $priority => $group) {
            $this->line("   " . strtoupper($priority) . ": {$group->count()} artigos");
        }
        $this->newLine();
    }

    private function displayFinalStats(): void
    {
        $this->info('╔═══════════════════════════════════════════════════════════╗');
        $this->info('║                    📊 ESTATÍSTICAS FINAIS                ║');
        $this->info('╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Artigos processados: {$this->stats['processed']}");
        $this->line("🎉 Gerados com sucesso: {$this->stats['successful']}");
        $this->line("❌ Falhas: {$this->stats['failed']}");
        $this->line("⏭️ Pulados (validação): {$this->stats['skipped']}");
        $this->line("💰 Custo total: {$this->stats['total_cost']} unidades");
        $this->line("⏱️ Tempo total: {$this->stats['total_time']}s");

        if (!empty($this->stats['by_category'])) {
            $this->newLine();
            $this->info('📂 POR CATEGORIA:');
            foreach ($this->stats['by_category'] as $category => $stats) {
                $this->line("   {$category}: ✅ {$stats['success']} | ❌ {$stats['failed']}");
            }
        }

        if (!empty($this->stats['errors'])) {
            $this->newLine();
            $this->error('⚠️ ERROS ENCONTRADOS:');
            foreach (array_slice($this->stats['errors'], 0, 5) as $error) {
                $this->line("   • " . \Illuminate\Support\Str::limit($error['title'], 50));
                $this->line("     Erro: " . \Illuminate\Support\Str::limit($error['error'], 70));
            }

            if (count($this->stats['errors']) > 5) {
                $remaining = count($this->stats['errors']) - 5;
                $this->line("   ... e mais {$remaining} erros");
            }
        }

        $this->newLine();
    }

    private function displaySuggestions(): void
    {
        $this->newLine();
        $this->info('💡 SUGESTÕES:');
        $this->line('   • Use --priority=high para processar artigos prioritários');
        $this->line('   • Use --category=slug-categoria para filtrar por categoria');
        $this->line('   • Use --retry-failed para reprocessar falhas do standard');
        $this->line('   • Use --show-stats para ver estatísticas antes de iniciar');
        $this->newLine();
    }
}
