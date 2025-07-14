<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ArticleGenerator\Domain\Services\HumanTimeDistributionService;

class ManageManualArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:manage-manual 
                           {--domain=when_to_change_tires : Domínio específico a processar}
                           {--batch-size=50 : Número de artigos a processar por lote}
                           {--dry-run : Simular a execução sem fazer alterações}
                           {--force : Processar artigos mesmo se já existirem no Article}
                           {--start-date= : Data de início para filtros (formato Y-m-d)}
                           {--end-date= : Data final para filtros (formato Y-m-d)}
                           {--detailed : Exibir logs detalhados}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Gerencia artigos criados manualmente com diferentes templates e status de blog';

    private HumanTimeDistributionService $timeDistribution;
    private Carbon $currentDate;

    /**
     * Cache para MaintenanceCategories já processadas
     */
    private array $processedCategories = [];

    /**
     * Execute o comando.
     */
    public function handle(): int
    {
        $this->currentDate = Carbon::parse('2025-01-02');
        $this->timeDistribution = app(HumanTimeDistributionService::class);

        $this->info('🚀 Iniciando gerenciamento de artigos manuais...');
        $this->showOptions();

        $stats = $this->processManualArticles();
        $this->showFinalResults($stats);

        return Command::SUCCESS;
    }

    /**
     * Mostra opções configuradas
     */
    private function showOptions(): void
    {
        $this->table(['Configuração', 'Valor'], [
            ['Template', $this->option('domain')],
            ['Batch Size', $this->option('batch-size')],
            ['Dry Run', $this->option('dry-run') ? 'Sim' : 'Não'],
            ['Force', $this->option('force') ? 'Sim' : 'Não'],
            ['Data Atual', $this->currentDate->format('Y-m-d')],
            ['Detailed', $this->option('detailed') ? 'Sim' : 'Não'],
        ]);

        if ($this->option('dry-run')) {
            $this->warn('⚠️  MODO SIMULAÇÃO - Nenhuma alteração será feita');
        }

        $this->line('');
    }

    /**
     * Processa todos os artigos manuais
     */
    private function processManualArticles(): array
    {
        $stats = [
            'total_found' => 0,
            'published_processed' => 0,
            'future_processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'already_exists' => 0,
        ];

        // Buscar artigos do template especificado
        $articles = $this->getManualArticles();
        $stats['total_found'] = $articles->count();

        if ($stats['total_found'] === 0) {
            $this->warn('❌ Nenhum artigo encontrado com os critérios especificados');
            return $stats;
        }

        $this->info("📊 Encontrados {$stats['total_found']} artigos para processar");
        $this->line('');

        $bar = $this->output->createProgressBar($stats['total_found']);
        $bar->start();

        foreach ($articles->chunk($this->option('batch-size')) as $chunk) {
            foreach ($chunk as $article) {
                try {
                    $result = $this->processIndividualArticle($article);
                    $stats[$result]++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $this->logError($article, $e);
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line('');

        return $stats;
    }

    /**
     * Busca artigos manuais baseado nos critérios
     */
    private function getManualArticles()
    {
        $query = TempArticle::where('source', $this->option('domain'))
            ->whereIn('blog_status', ['publish', 'future']);

        // Filtros opcionais por data
        if ($this->option('start-date')) {
            $startDate = Carbon::parse($this->option('start-date'));
            $query->where('published_at', '>=', $startDate);
        }

        if ($this->option('end-date')) {
            $endDate = Carbon::parse($this->option('end-date'));
            $query->where('published_at', '<=', $endDate);
        }

        return $query->orderBy('published_at', 'asc')->get();
    }

    /**
     * Processa um artigo individual
     */
    private function processIndividualArticle($tempArticle): string
    {
        // Verificar se já existe no Article (a menos que force seja usado)
        if (!$this->option('force') && $this->articleAlreadyExists($tempArticle)) {
            $this->detailedLog("Artigo {$tempArticle->slug} já existe no Article - pulando");
            return 'already_exists';
        }

        if ($tempArticle->blog_status === 'publish') {
            return $this->processPublishedArticle($tempArticle);
        } elseif ($tempArticle->blog_status === 'future') {
            return $this->processFutureArticle($tempArticle);
        }

        return 'skipped';
    }

    /**
     * Processa artigo com status "publish"
     */
    private function processPublishedArticle($tempArticle): string
    {
        $publishedAt = Carbon::parse($tempArticle->published_at);
        $createdAt = $publishedAt->copy();

        // Para artigos publicados: humanizar published_at até a data atual
        $humanizedPublishedAt = $this->humanizePublishedDate($publishedAt);

        // Gerar updated_at aleatório se published_at for anterior à data atual
        $updatedAt = $this->generateRandomUpdatedDate($publishedAt, $humanizedPublishedAt);

        $articleData = [
            'title' => $tempArticle->title,
            'slug' => $tempArticle->new_slug ?? $tempArticle->slug,
            'template' => $tempArticle->template,
            'category_id' => $tempArticle->category_id,
            'category_name' => $tempArticle->category_name,
            'category_slug' => $tempArticle->category_slug,
            'content' => $tempArticle->content,
            'extracted_entities' => $tempArticle->extracted_entities,
            'seo_data' => $tempArticle->seo_data,
            'metadata' => $tempArticle->metadata ?? [],
            'status' => 'published',
            'original_post_id' => $tempArticle->original_post_id ?? null,
            'created_at' => $createdAt,
            'published_at' => $humanizedPublishedAt,
            'updated_at' => $updatedAt,
            'scheduled_at' => null,
        ];

        if (!$this->option('dry-run')) {
            $article = Article::create($articleData);
            // Atualizar MaintenanceCategory se necessário
            $this->updateMaintenanceCategoryIfNeeded($article);
        }

        $this->detailedLog("✅ Artigo publicado processado: {$tempArticle->slug}");
        return 'published_processed';
    }

    /**
     * Processa artigo com status "future"
     */
    private function processFutureArticle($tempArticle): string
    {
        $publishedAt = Carbon::parse($tempArticle->published_at);
        $modifiedAt = Carbon::parse($tempArticle->modified_at);

        $articleData = [
            'title' => $tempArticle->title,
            'slug' => $tempArticle->new_slug ?? $tempArticle->slug,
            'template' => $tempArticle->template,
            'category_id' => $tempArticle->category_id,
            'category_name' => $tempArticle->category_name,
            'category_slug' => $tempArticle->category_slug,
            'content' => $tempArticle->content,
            'extracted_entities' => $tempArticle->extracted_entities,
            'seo_data' => $tempArticle->seo_data,
            'metadata' => $tempArticle->metadata ?? [],
            'status' => 'scheduled',
            'original_post_id' => $tempArticle->original_post_id ?? null,
            'created_at' => $publishedAt,        // created_at = published_at
            'published_at' => $publishedAt,       // mantém inalterado
            'updated_at' => $modifiedAt,          // updated_at = modified_at
            'scheduled_at' => $modifiedAt,        // scheduled_at = modified_at
        ];

        if (!$this->option('dry-run')) {
            Article::create($articleData);
        }

        $this->detailedLog("📅 Artigo futuro processado: {$tempArticle->slug}");
        return 'future_processed';
    }

    /**
     * Humaniza a data de publicação até a data atual
     */
    private function humanizePublishedDate(Carbon $originalDate): Carbon
    {
        // Se a data já é posterior à data atual, mantém inalterada
        if ($originalDate->isAfter($this->currentDate)) {
            return $originalDate;
        }

        // Calcular diferença em dias
        $daysDiff = $originalDate->diffInDays($this->currentDate);

        // Se a diferença é muito pequena (menos de 1 dia), manter original
        if ($daysDiff < 1) {
            return $originalDate;
        }

        // Gerar nova data aleatória entre a original e a atual
        $randomDays = mt_rand(0, $daysDiff);
        $randomHours = mt_rand(0, 23);
        $randomMinutes = mt_rand(0, 59);
        $randomSeconds = mt_rand(0, 59);

        return $originalDate->copy()
            ->addDays($randomDays)
            ->setTime($randomHours, $randomMinutes, $randomSeconds);
    }

    /**
     * Gera data aleatória para updated_at
     */
    private function generateRandomUpdatedDate(Carbon $publishedAt, Carbon $humanizedPublishedAt): Carbon
    {
        // Se a data humanizada é posterior à data atual, usar a data atual como limite
        $endDate = $humanizedPublishedAt->isAfter($this->currentDate)
            ? $this->currentDate
            : $this->currentDate;

        // Se published_at é muito próximo da data atual, gerar update próximo
        $daysDiff = $humanizedPublishedAt->diffInDays($endDate);

        if ($daysDiff < 1) {
            // Para datas muito próximas, gerar update algumas horas depois
            return $humanizedPublishedAt->copy()->addHours(mt_rand(1, 12));
        }

        // Gerar data aleatória entre published_at humanizado e data atual
        $randomDays = mt_rand(1, max(1, $daysDiff));
        $randomHours = mt_rand(0, 23);
        $randomMinutes = mt_rand(0, 59);

        return $humanizedPublishedAt->copy()
            ->addDays($randomDays)
            ->setTime($randomHours, $randomMinutes);
    }

    /**
     * Verifica se o artigo já existe no Article
     */
    private function articleAlreadyExists($tempArticle): bool
    {
        $slug = $tempArticle->new_slug ?? $tempArticle->slug;

        $existsBy = Article::where('slug', $slug)->exists();

        if (!$existsBy && $tempArticle->original_post_id) {
            $existsBy = Article::where('original_post_id', $tempArticle->original_post_id)->exists();
        }

        return $existsBy;
    }

    /**
     * Log de erro
     */
    private function logError($article, \Exception $e): void
    {
        $message = "Erro ao processar artigo {$article->slug}: " . $e->getMessage();

        Log::error($message, [
            'article_id' => $article->_id,
            'article_slug' => $article->slug,
            'exception' => $e->getTraceAsString()
        ]);

        if ($this->option('detailed')) {
            $this->error($message);
        }
    }

    /**
     * Log detalhado
     */
    private function detailedLog(string $message): void
    {
        if ($this->option('detailed')) {
            $this->line($message);
        }
    }

    /**
     * Mostra resultados finais
     */
    private function showFinalResults(array $stats): void
    {
        $this->line('');
        $this->info('📊 RESULTADOS FINAIS');
        $this->line('====================');

        $this->table(['Métrica', 'Quantidade'], [
            ['📄 Total de artigos encontrados', $stats['total_found']],
            ['✅ Artigos "publish" processados', $stats['published_processed']],
            ['📅 Artigos "future" processados', $stats['future_processed']],
            ['🔄 Artigos já existentes (pulados)', $stats['already_exists']],
            ['⏭️ Artigos ignorados', $stats['skipped']],
            ['❌ Erros durante processamento', $stats['errors']],
        ]);

        $totalProcessed = $stats['published_processed'] + $stats['future_processed'];
        $successRate = $stats['total_found'] > 0
            ? round(($totalProcessed / $stats['total_found']) * 100, 2)
            : 0;

        $this->line('');
        $this->info("🎯 Taxa de sucesso: {$successRate}% ({$totalProcessed}/{$stats['total_found']})");

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  {$stats['errors']} erros encontrados. Verifique os logs para detalhes.");
        }

        if ($totalProcessed > 0) {
            $this->info("✨ Processamento concluído com sucesso!");

            if (!$this->option('dry-run')) {
                $this->line('');
                $this->info('💡 Próximos passos recomendados:');
                $this->line('1. Verificar artigos processados no painel administrativo');
                $this->line('2. Executar sincronização com MySQL se necessário');
                $this->line('3. Testar publicação dos artigos futuros');
            }
        }
    }

    /**
     * Atualiza MaintenanceCategory para to_follow = true se necessário
     */
    private function updateMaintenanceCategoryIfNeeded(Article $article): void
    {
        if (empty($article->category_slug)) {
            return;
        }

        // Evitar processamento duplicado da mesma categoria
        if (in_array($article->category_slug, $this->processedCategories)) {
            return;
        }

        try {
            $category = MaintenanceCategory::where('slug', $article->category_slug)
                ->where('to_follow', false)
                ->first();

            if ($category) {
                $category->update(['to_follow' => true]);
                $this->info("MaintenanceCategory '{$article->category_slug}' marcada como to_follow = true");
            }

            // Adicionar ao cache para evitar reprocessamento
            $this->processedCategories[] = $article->category_slug;
        } catch (\Exception $e) {
            $this->warn("Erro ao atualizar MaintenanceCategory '{$article->category_slug}': {$e->getMessage()}");
        }
    }
}
