<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Domain\Services\ArticleSchedulingService;
use Src\ArticleGenerator\Domain\Services\WorkingDaysCalculatorService;
use Src\ArticleGenerator\Domain\Services\HumanTimeDistributionService;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;
use Src\ArticleGenerator\Domain\ValueObjects\PublishingPeriod;

class RescheduleArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:reschedule 
                           {--start-date= : Nova data inicial para reagendamento (formato Y-m-d)}
                           {--days=30 : Número de dias úteis para redistribuir os artigos}
                           {--min-posts=50 : Mínimo de posts por dia}
                           {--max-posts=80 : Máximo de posts por dia}
                           {--status=scheduled : Status dos artigos a reagendar (scheduled|published)}
                           {--date-filter= : Reagendar apenas artigos agendados/publicados a partir desta data (Y-m-d)}
                           {--batch-size=100 : Número de artigos a processar por lote}
                           {--imported-only : Reagendar apenas artigos importados}
                           {--new-only : Reagendar apenas artigos novos}
                           {--overdue-only : Reagendar apenas artigos em atraso}
                           {--dry-run : Simular reagendamento sem fazer alterações}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Reagenda artigos automaticamente com nova distribuição inteligente';

    private ArticleSchedulingService $schedulingService;
    private WorkingDaysCalculatorService $workingDaysCalculator;
    private HumanTimeDistributionService $timeDistribution;
    private WorkingHours $workingHours;

    /**
     * Execute o comando.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = now();
        $this->info("Iniciando reagendamento automático - {$startTime->format('Y-m-d H:i:s')}");

        // Inicializar serviços
        $this->initializeServices();

        // Validar e processar opções
        $options = $this->validateAndParseOptions();
        if ($options === null) {
            return Command::FAILURE;
        }

        // Obter artigos para reagendamento
        $articlesToReschedule = $this->getArticlesToReschedule($options);
        if ($articlesToReschedule->isEmpty()) {
            $this->info('Nenhum artigo encontrado para reagendamento.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$articlesToReschedule->count()} artigos para reagendamento.");

        // Executar reagendamento em lotes
        $results = $this->executeRescheduling($articlesToReschedule, $options);

        // Log dos resultados
        $this->logResults($results, $startTime, $options);

        return $results['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Inicializa os serviços necessários
     */
    private function initializeServices(): void
    {
        $this->workingHours = new WorkingHours();
        $this->workingDaysCalculator = new WorkingDaysCalculatorService($this->workingHours);
        $this->timeDistribution = new HumanTimeDistributionService($this->workingHours);
        $this->schedulingService = new ArticleSchedulingService(
            $this->workingDaysCalculator,
            $this->timeDistribution,
            $this->workingHours
        );
    }

    /**
     * Valida e processa as opções do comando
     */
    private function validateAndParseOptions(): ?array
    {
        try {
            // Data inicial para reagendamento
            $startDate = $this->option('start-date') 
                ? Carbon::createFromFormat('Y-m-d', $this->option('start-date'))
                : Carbon::tomorrow();

            // Data de filtro
            $dateFilter = $this->option('date-filter')
                ? Carbon::createFromFormat('Y-m-d', $this->option('date-filter'))
                : null;

            $days = (int) $this->option('days');
            $minPosts = (int) $this->option('min-posts');
            $maxPosts = (int) $this->option('max-posts');
            $batchSize = (int) $this->option('batch-size');
            $status = $this->option('status');

            // Validações automáticas
            if ($startDate->lt(Carbon::today())) {
                $this->info('Data inicial está no passado, ajustando para amanhã.');
                $startDate = Carbon::tomorrow();
            }

            if ($days <= 0 || $days > 90) {
                $this->warn('Número de dias inválido, usando padrão: 30');
                $days = 30;
            }

            if ($minPosts <= 0 || $maxPosts <= 0 || $minPosts > $maxPosts) {
                $this->warn('Valores de posts por dia inválidos, usando padrões.');
                $minPosts = PublishingPeriod::MIN_POSTS_PER_DAY;
                $maxPosts = PublishingPeriod::MAX_POSTS_PER_DAY;
            }

            if ($batchSize <= 0 || $batchSize > 500) {
                $this->warn('Batch size inválido, usando padrão: 100');
                $batchSize = 100;
            }

            if (!in_array($status, ['scheduled', 'published'])) {
                $this->warn('Status inválido, usando padrão: scheduled');
                $status = 'scheduled';
            }

            // Ajustar para próximo dia útil
            $adjustedStartDate = $this->workingDaysCalculator->adjustToNextWorkingDay($startDate);
            
            if (!$adjustedStartDate->eq($startDate)) {
                $this->info("Data ajustada para próximo dia útil: {$adjustedStartDate->format('Y-m-d')}");
            }

            return [
                'start_date' => $adjustedStartDate,
                'date_filter' => $dateFilter,
                'days' => $days,
                'min_posts' => $minPosts,
                'max_posts' => $maxPosts,
                'batch_size' => $batchSize,
                'status' => $status,
                'imported_only' => $this->option('imported-only'),
                'new_only' => $this->option('new-only'),
                'overdue_only' => $this->option('overdue-only'),
                'dry_run' => $this->option('dry-run'),
            ];

        } catch (\Exception $e) {
            $this->error("Erro ao validar opções: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Obtém artigos para reagendamento
     */
    private function getArticlesToReschedule(array $options): Collection
    {
        $query = Article::where('status', $options['status']);

        // Filtros de tipo de artigo
        if ($options['imported_only']) {
            $query->whereNotNull('original_post_id');
            $this->info('Reagendando apenas artigos importados.');
        } elseif ($options['new_only']) {
            $query->whereNull('original_post_id');
            $this->info('Reagendando apenas artigos novos.');
        }

        // Filtro de data
        if ($options['date_filter']) {
            $dateField = $options['status'] === 'scheduled' ? 'scheduled_at' : 'published_at';
            $query->where($dateField, '>=', $options['date_filter']);
            $this->info("Filtrando artigos com {$dateField} >= {$options['date_filter']->format('Y-m-d')}");
        }

        // Filtro para artigos em atraso
        if ($options['overdue_only']) {
            if ($options['status'] === 'scheduled') {
                $query->where('scheduled_at', '<', now());
                $this->info('Reagendando apenas artigos em atraso.');
            } else {
                $this->warn('Filtro --overdue-only só funciona com status "scheduled".');
            }
        }

        // Ordenar para processamento consistente
        $orderField = $options['status'] === 'scheduled' ? 'scheduled_at' : 'published_at';
        $query->orderBy($orderField, 'asc');

        return $query->get();
    }

    /**
     * Executa o reagendamento em lotes
     */
    private function executeRescheduling(Collection $articles, array $options): array
    {
        $results = [
            'success' => true,
            'total_articles' => $articles->count(),
            'processed' => 0,
            'rescheduled' => 0,
            'failed' => 0,
            'batches_processed' => 0,
            'errors' => [],
            'rescheduled_articles' => [],
        ];

        // Processar em lotes
        $batches = $articles->chunk($options['batch_size']);
        $totalBatches = $batches->count();

        $this->info("Processando {$results['total_articles']} artigos em {$totalBatches} lotes...");

        foreach ($batches as $batchIndex => $batch) {
            $this->info("Processando lote " . ($batchIndex + 1) . "/{$totalBatches} ({$batch->count()} artigos)");

            try {
                $batchResult = $this->processBatch($batch, $options);
                
                $results['processed'] += $batchResult['processed'];
                $results['rescheduled'] += $batchResult['rescheduled'];
                $results['failed'] += $batchResult['failed'];
                $results['batches_processed']++;
                
                if (!empty($batchResult['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $batchResult['errors']);
                }
                
                if (!empty($batchResult['rescheduled_articles'])) {
                    $results['rescheduled_articles'] = array_merge(
                        $results['rescheduled_articles'], 
                        $batchResult['rescheduled_articles']
                    );
                }

            } catch (\Exception $e) {
                $results['errors'][] = "Erro no lote " . ($batchIndex + 1) . ": {$e->getMessage()}";
                $results['failed'] += $batch->count();
                $results['success'] = false;
            }

            // Pequena pausa entre lotes para não sobrecarregar
            if ($batchIndex < $totalBatches - 1) {
                usleep(100000); // 100ms
            }
        }

        return $results;
    }

    /**
     * Processa um lote de artigos
     */
    private function processBatch(Collection $batch, array $options): array
    {
        $batchResult = [
            'processed' => 0,
            'rescheduled' => 0,
            'failed' => 0,
            'errors' => [],
            'rescheduled_articles' => [],
        ];

        // Criar cronograma para este lote
        $publishingPeriod = PublishingPeriod::createForArticleCount(
            $options['start_date']->copy()->addDays($batchResult['processed'] * 0.1), // Pequeno offset por lote
            $batch->count(),
            $options['min_posts'],
            $options['max_posts']
        );

        // Distribuir artigos pelos dias
        $distribution = $publishingPeriod->distributeArticles($batch->count());

        $articleIndex = 0;
        foreach ($distribution as $dateKey => $dayInfo) {
            $dayDate = $dayInfo['date'];
            $postsForDay = $dayInfo['posts_count'];

            // Pegar artigos para este dia
            $dayArticles = $batch->slice($articleIndex, $postsForDay);
            $articleIndex += $postsForDay;

            // Gerar cronograma para o dia
            $daySchedule = $this->generateDayScheduleForReschedule($dayDate, $dayArticles->count());

            // Aplicar reagendamento aos artigos
            foreach ($dayArticles as $index => $article) {
                if (!isset($daySchedule[$index])) {
                    $batchResult['errors'][] = "Slot não disponível para artigo {$article->_id}";
                    $batchResult['failed']++;
                    continue;
                }

                try {
                    $scheduleSlot = $daySchedule[$index];
                    $this->rescheduleArticle($article, $scheduleSlot, $options);
                    
                    $batchResult['rescheduled']++;
                    $batchResult['rescheduled_articles'][] = [
                        'id' => $article->_id,
                        'title' => $article->title,
                        'old_date' => $article->scheduled_at ?? $article->published_at,
                        'new_date' => $scheduleSlot->getScheduledAt(),
                        'article_type' => !empty($article->original_post_id) ? 'imported' : 'new',
                    ];

                } catch (\Exception $e) {
                    $batchResult['errors'][] = "Erro ao reagendar artigo {$article->_id}: {$e->getMessage()}";
                    $batchResult['failed']++;
                }

                $batchResult['processed']++;
            }
        }

        return $batchResult;
    }

    /**
     * Gera cronograma para reagendamento
     */
    private function generateDayScheduleForReschedule(Carbon $date, int $articlesCount): array
    {
        if ($articlesCount <= 0) {
            return [];
        }

        // Resetar timestamps para este dia
        $this->timeDistribution->resetUsedTimestamps();

        // Gerar cronograma misto (imported + new)
        return $this->timeDistribution->generateDaySchedule($date, $articlesCount, 'mixed');
    }

    /**
     * Reagenda um artigo individual
     */
    private function rescheduleArticle(Article $article, $scheduleSlot, array $options): void
    {
        if ($options['dry_run']) {
            return; // Não fazer alterações em dry run
        }

        $newScheduledAt = $scheduleSlot->getScheduledAt();
        $isImported = !empty($article->original_post_id);

        $updateData = [
            'scheduled_at' => $newScheduledAt,
            'updated_at' => now(),
        ];

        // Para artigos importados, preservar datas originais
        if ($isImported) {
            // Manter created_at e published_at originais
            // Apenas atualizar scheduled_at e updated_at
        } else {
            // Para artigos novos, atualizar todas as datas se necessário
            if ($options['status'] === 'scheduled') {
                $updateData['published_at'] = $newScheduledAt;
                $updateData['created_at'] = $newScheduledAt;
            }
        }

        // Se estava publicado, voltar para agendado
        if ($options['status'] === 'published') {
            $updateData['status'] = 'scheduled';
        }

        Article::where('_id', $article->_id)->update($updateData);

        // Log da operação
        $this->logRescheduleOperation($article, $newScheduledAt, $isImported);
    }

    /**
     * Log da operação de reagendamento
     */
    private function logRescheduleOperation(Article $article, Carbon $newDate, bool $isImported): void
    {
        $logData = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'operation' => 'reschedule',
            'article_id' => $article->_id,
            'title' => $article->title,
            'slug' => $article->slug,
            'article_type' => $isImported ? 'imported' : 'new',
            'old_scheduled_at' => $article->scheduled_at ? $article->scheduled_at->format('Y-m-d H:i:s') : null,
            'old_published_at' => $article->published_at ? $article->published_at->format('Y-m-d H:i:s') : null,
            'new_scheduled_at' => $newDate->format('Y-m-d H:i:s'),
            'preserved_original_dates' => $isImported,
        ];

        $logLine = json_encode($logData) . "\n";
        $logFile = storage_path('logs/article-reschedule-' . date('Y-m-d') . '.log');
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log dos resultados da execução
     */
    private function logResults(array $results, Carbon $startTime, array $options): void
    {
        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->info('=== RESULTADOS DO REAGENDAMENTO ===');
        $this->info("Duração: {$duration} segundos");
        $this->info("Total de artigos: {$results['total_articles']}");
        $this->info("Artigos processados: {$results['processed']}");
        $this->info("Artigos reagendados: {$results['rescheduled']}");
        $this->info("Artigos com falha: {$results['failed']}");
        $this->info("Lotes processados: {$results['batches_processed']}");

        if ($options['dry_run']) {
            $this->warn('MODO SIMULAÇÃO - Nenhuma alteração foi feita no banco de dados');
        }

        if (!empty($results['errors'])) {
            $this->warn('Erros encontrados:');
            foreach (array_slice($results['errors'], 0, 10) as $error) { // Mostrar apenas primeiros 10
                $this->error("  • {$error}");
            }
            
            if (count($results['errors']) > 10) {
                $remaining = count($results['errors']) - 10;
                $this->warn("  ... e mais {$remaining} erros (verifique o log para detalhes)");
            }
        }

        // Estatísticas
        if ($results['processed'] > 0) {
            $avgTimePerArticle = round($duration / $results['processed'], 3);
            $this->info("Tempo médio por artigo: {$avgTimePerArticle} segundos");
            
            $successRate = round(($results['rescheduled'] / $results['processed']) * 100, 1);
            $this->info("Taxa de sucesso: {$successRate}%");
        }

        // Log estruturado para monitoramento
        $executionLog = [
            'command' => 'articles:reschedule',
            'started_at' => $startTime->format('Y-m-d H:i:s'),
            'completed_at' => $endTime->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'options' => $options,
            'results' => $results,
            'memory_usage' => memory_get_peak_usage(true),
            'success' => $results['success'],
        ];

        $logLine = json_encode($executionLog) . "\n";
        $logFile = storage_path('logs/reschedule-execution-' . date('Y-m-d') . '.log');
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

        // Alertas automáticos
        $this->checkForAlerts($results, $options);
    }

    /**
     * Verifica condições que requerem alertas
     */
    private function checkForAlerts(array $results, array $options): void
    {
        $alerts = [];

        // Alta taxa de falha
        if ($results['processed'] > 0) {
            $failureRate = ($results['failed'] / $results['processed']) * 100;
            if ($failureRate > 15) {
                $alerts[] = "Alta taxa de falha no reagendamento: {$failureRate}%";
            }
        }

        // Muitos erros
        if ($results['failed'] > 20) {
            $alerts[] = "Muitos artigos falharam no reagendamento: {$results['failed']}";
        }

        // Nenhum artigo reagendado quando deveria haver
        if ($results['total_articles'] > 0 && $results['rescheduled'] === 0 && !$options['dry_run']) {
            $alerts[] = "Nenhum artigo foi reagendado apesar de haver artigos para processar";
        }

        // Log de alertas
        if (!empty($alerts)) {
            $this->warn('ALERTAS DETECTADOS:');
            foreach ($alerts as $alert) {
                $this->error("  ⚠️  {$alert}");
            }

            $alertLog = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'command' => 'articles:reschedule',
                'alerts' => $alerts,
                'results' => $results,
                'options' => $options,
            ];

            $logLine = json_encode($alertLog) . "\n";
            $alertFile = storage_path('logs/alerts-' . date('Y-m-d') . '.log');
            file_put_contents($alertFile, $logLine, FILE_APPEND | LOCK_EX);
        }
    }
}