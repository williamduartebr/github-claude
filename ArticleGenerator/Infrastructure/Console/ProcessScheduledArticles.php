<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\AutoInfoCenter\Domain\Eloquent\MaintenanceCategory;

class ProcessScheduledArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:process-scheduled 
                           {--batch-size=50 : Número de artigos a processar por execução}
                           {--time-buffer=5 : Buffer em minutos para processar artigos (publicar artigos agendados até X minutos atrás)}
                           {--dry-run : Simular a execução sem fazer alterações}
                           {--force : Forçar processamento mesmo se não estiver no horário}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Processa artigos agendados que estão prontos para publicação automaticamente';

    /**
     * Cache para MaintenanceCategories já processadas
     */
    private array $processedCategories = [];

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = now();
        $this->info("Iniciando processamento de artigos agendados - {$startTime->format('Y-m-d H:i:s')}");

        // Configurações
        $batchSize = (int) $this->option('batch-size');
        $timeBuffer = (int) $this->option('time-buffer');
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        // Validações automáticas
        if (!$this->validateExecutionTime() && !$isForced) {
            $this->info('Fora do horário de processamento (07:00-22:00). Use --force para ignorar.');
            return Command::SUCCESS;
        }

        // Obter artigos prontos para publicação
        $scheduledArticles = $this->getArticlesReadyForPublication($timeBuffer, $batchSize);

        if ($scheduledArticles->isEmpty()) {
            $this->info('Nenhum artigo agendado pronto para publicação no momento.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$scheduledArticles->count()} artigos prontos para publicação.");

        // Processar artigos
        $results = $this->processArticles($scheduledArticles, $isDryRun);

        // Log dos resultados
        $this->logResults($results, $startTime);

        return $results['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Valida se está dentro do horário permitido para processamento
     */
    private function validateExecutionTime(): bool
    {
        $currentHour = (int) now()->format('H');
        return $currentHour >= 7 && $currentHour <= 22;
    }

    /**
     * Obtém artigos agendados prontos para publicação
     */
    private function getArticlesReadyForPublication(int $timeBuffer, int $batchSize): Collection
    {
        // Calcular limite de tempo (agora + buffer)
        $timeLimit = now()->addMinutes($timeBuffer);

        return Article::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $timeLimit)
            ->orderBy('scheduled_at', 'asc')
            ->limit($batchSize)
            ->get();
    }

    /**
     * Processa os artigos agendados
     */
    private function processArticles(Collection $articles, bool $isDryRun): array
    {
        $results = [
            'success' => true,
            'processed' => 0,
            'published' => 0,
            'failed' => 0,
            'errors' => [],
            'published_articles' => [],
        ];

        foreach ($articles as $article) {
            try {
                $this->info("Processando artigo: {$article->_id} - {$article->title}");

                if (!$isDryRun) {
                    $publishResult = $this->publishArticle($article);
                    
                    if ($publishResult['success']) {
                        $results['published']++;
                        $results['published_articles'][] = [
                            'id' => $article->_id,
                            'title' => $article->title,
                            'slug' => $article->slug,
                            'scheduled_at' => $article->scheduled_at,
                            'published_at' => $publishResult['published_at'],
                        ];
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Falha ao publicar {$article->_id}: {$publishResult['error']}";
                    }
                } else {
                    $this->info("  [DRY RUN] Artigo seria publicado agora");
                    $results['published']++;
                }

                $results['processed']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Erro ao processar artigo {$article->_id}: {$e->getMessage()}";
                $results['success'] = false;
            }
        }

        return $results;
    }

    /**
     * Publica um artigo individual
     */
    private function publishArticle(Article $article): array
    {
        try {
            $publishedAt = now();

            // Atualizar status e data de publicação
            $updateData = [
                'status' => 'published',
                'published_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ];

            // Para artigos importados, preservar a data original de publicação
            if (!empty($article->original_post_id)) {
                // Manter published_at original, apenas atualizar status
                unset($updateData['published_at']);
                $publishedAt = $article->published_at; // Usar data original
            }

            Article::where('_id', $article->_id)->update($updateData);

            // Atualizar MaintenanceCategory se necessário
            $this->updateMaintenanceCategoryIfNeeded($article);

            // Limpar cache se necessário
            $this->clearArticleCache($article);

            // Registrar publicação no log
            $this->logArticlePublication($article, $publishedAt);

            return [
                'success' => true,
                'published_at' => $publishedAt,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
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

    /**
     * Limpa cache relacionado ao artigo
     */
    private function clearArticleCache(Article $article): void
    {
        try {
            // Implementar limpeza de cache específica da aplicação
            // Exemplo: limpar cache de categorias, tags, etc.
            
            // Cache por slug
            if (function_exists('cache')) {
                cache()->forget("article.{$article->slug}");
                cache()->forget("article.{$article->_id}");
                
                // Cache de categoria
                if (!empty($article->category_slug)) {
                    cache()->forget("category.{$article->category_slug}.articles");
                }
                
                // Cache de tags
                if (!empty($article->tags)) {
                    foreach ($article->tags as $tag) {
                        cache()->forget("tag.{$tag}.articles");
                    }
                }
            }

        } catch (\Exception $e) {
            // Log erro de cache mas não falhar a publicação
            $this->warn("Erro ao limpar cache para artigo {$article->_id}: {$e->getMessage()}");
        }
    }

    /**
     * Registra publicação no log
     */
    private function logArticlePublication(Article $article, Carbon $publishedAt): void
    {
        $logData = [
            'timestamp' => $publishedAt->format('Y-m-d H:i:s'),
            'article_id' => $article->_id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category_slug,
            'scheduled_at' => $article->scheduled_at ? $article->scheduled_at->format('Y-m-d H:i:s') : null,
            'published_at' => $publishedAt->format('Y-m-d H:i:s'),
            'article_type' => !empty($article->original_post_id) ? 'imported' : 'new',
            'delay_minutes' => $article->scheduled_at ? $publishedAt->diffInMinutes($article->scheduled_at) : 0,
        ];

        $logLine = json_encode($logData) . "\n";
        
        // Escrever no log de publicações
        $logFile = storage_path('logs/article-publications-' . date('Y-m-d') . '.log');
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log dos resultados da execução
     */
    private function logResults(array $results, Carbon $startTime): void
    {
        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->info('=== RESULTADOS DO PROCESSAMENTO ===');
        $this->info("Duração: {$duration} segundos");
        $this->info("Artigos processados: {$results['processed']}");
        $this->info("Artigos publicados: {$results['published']}");
        $this->info("Artigos com falha: {$results['failed']}");

        if (!empty($results['errors'])) {
            $this->warn('Erros encontrados:');
            foreach ($results['errors'] as $error) {
                $this->error("  • {$error}");
            }
        }

        // Log estruturado para monitoramento
        $executionLog = [
            'command' => 'articles:process-scheduled',
            'started_at' => $startTime->format('Y-m-d H:i:s'),
            'completed_at' => $endTime->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'results' => $results,
            'memory_usage' => memory_get_peak_usage(true),
            'success' => $results['success'],
        ];

        $logLine = json_encode($executionLog) . "\n";
        $logFile = storage_path('logs/scheduled-processing-' . date('Y-m-d') . '.log');
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Estatísticas para monitoramento
        if ($results['processed'] > 0) {
            $avgTimePerArticle = round($duration / $results['processed'], 2);
            $this->info("Tempo médio por artigo: {$avgTimePerArticle} segundos");
            
            $successRate = round(($results['published'] / $results['processed']) * 100, 1);
            $this->info("Taxa de sucesso: {$successRate}%");
        }

        // Alertas automáticos
        $this->checkForAlerts($results);
    }

    /**
     * Verifica condições que requerem alertas
     */
    private function checkForAlerts(array $results): void
    {
        $alerts = [];

        // Alta taxa de falha
        if ($results['processed'] > 0) {
            $failureRate = ($results['failed'] / $results['processed']) * 100;
            if ($failureRate > 10) {
                $alerts[] = "Alta taxa de falha: {$failureRate}%";
            }
        }

        // Muitos artigos falharam
        if ($results['failed'] > 5) {
            $alerts[] = "Muitos artigos falharam: {$results['failed']}";
        }

        // Nenhum artigo processado quando deveria haver
        $currentHour = (int) now()->format('H');
        if ($currentHour >= 8 && $currentHour <= 20 && $results['processed'] === 0) {
            // Verificar se há artigos agendados para agora
            $articlesWaiting = Article::where('status', 'scheduled')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now()->addMinutes(60))
                ->count();
                
            if ($articlesWaiting > 0) {
                $alerts[] = "Há {$articlesWaiting} artigos aguardando publicação mas nenhum foi processado";
            }
        }

        // Log de alertas
        if (!empty($alerts)) {
            $this->warn('ALERTAS DETECTADOS:');
            foreach ($alerts as $alert) {
                $this->error("  ⚠️  {$alert}");
            }

            // Log estruturado de alertas
            $alertLog = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'command' => 'articles:process-scheduled',
                'alerts' => $alerts,
                'results' => $results,
            ];

            $logLine = json_encode($alertLog) . "\n";
            $alertFile = storage_path('logs/alerts-' . date('Y-m-d') . '.log');
            file_put_contents($alertFile, $logLine, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Limpa dados antigos de processamento
     */
    public function cleanupOldLogs(): void
    {
        try {
            $logDirectory = storage_path('logs');
            $patterns = [
                'article-publications-*.log',
                'scheduled-processing-*.log',
                'alerts-*.log'
            ];

            $thirtyDaysAgo = now()->subDays(30)->timestamp;

            foreach ($patterns as $pattern) {
                $files = glob($logDirectory . '/' . $pattern);
                foreach ($files as $file) {
                    if (filemtime($file) < $thirtyDaysAgo) {
                        unlink($file);
                    }
                }
            }

        } catch (\Exception $e) {
            $this->warn("Erro ao limpar logs antigos: {$e->getMessage()}");
        }
    }

    /**
     * Relatório de status para monitoramento externo
     */
    public function getStatusReport(): array
    {
        $now = now();
        
        // Artigos agendados para as próximas horas
        $nextHourArticles = Article::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$now, $now->copy()->addHour()])
            ->count();

        // Artigos agendados para hoje
        $todayArticles = Article::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->count();

        // Artigos atrasados (deveriam ter sido publicados)
        $overdueArticles = Article::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $now->subMinutes(30))
            ->count();

        // Artigos publicados hoje
        $publishedToday = Article::where('status', 'published')
            ->whereBetween('updated_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->count();

        return [
            'timestamp' => $now->format('Y-m-d H:i:s'),
            'next_hour_scheduled' => $nextHourArticles,
            'today_scheduled' => $todayArticles,
            'overdue_articles' => $overdueArticles,
            'published_today' => $publishedToday,
            'system_status' => $overdueArticles > 10 ? 'warning' : 'healthy',
        ];
    }
}