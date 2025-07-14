<?php

namespace Src\ArticleGenerator\Infrastructure\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Src\AutoInfoCenter\Domain\Eloquent\Article;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;
use Src\ArticleGenerator\Domain\Services\AutoSyncService;
use Src\ArticleGenerator\Domain\Services\WorkingDaysCalculatorService;
use Src\ArticleGenerator\Domain\Services\HumanTimeDistributionService;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;
use Src\ArticleGenerator\Domain\ValueObjects\PublishingPeriod;

class ScheduleDraftArticles extends Command
{
    /**
     * Nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'articles:schedule-drafts 
                           {--start-date= : Data inicial para agendamento (formato Y-m-d)}
                           {--days=5 : Número de dias úteis para distribuir os artigos}
                           {--min-posts=50 : Mínimo de posts por dia}
                           {--max-posts=80 : Máximo de posts por dia}
                           {--imported-only : Processar apenas artigos importados (com original_post_id)}
                           {--new-only : Processar apenas artigos novos (sem original_post_id)}
                           {--auto-sync : Executar sincronização automática completa após agendamento}
                           {--batch-size=100 : Número de artigos a processar por lote}
                           {--max-execution-time=3600 : Tempo máximo de execução em segundos}
                           {--manual-content : Remover validação de erros}
                           {--dry-run : Simular a execução sem fazer alterações}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Agenda artigos temporários automaticamente com distribuição inteligente em dias úteis';

    private AutoSyncService $autoSyncService;
    private WorkingDaysCalculatorService $workingDaysCalculator;
    private HumanTimeDistributionService $timeDistribution;
    private WorkingHours $workingHours;
    private int $startTime;
    private int $maxExecutionTime;

    // ATRIBUTOS PARA CONTROLE DE CAPACIDADE
    private int $maxSlotsPerDay = 100;
    private int $maxSlotsPerHour = 8;

    /**
     * Autores para atribuição automática
     */
    private array $authors = [
        'imported' => [
            'William Duarte' => 'Entusiasta automotivo e mecânica automotiva',
            'Marley Rondon' => 'Especialista em veículos e mecânica automotiva',

            'Equipe Editorial' => 'Equipe especializada em conteúdo automotivo',
            'Redação' => 'Editores especialistas em veículos',
            'Equipe de Conteúdo' => 'Especialistas em informação automotiva'
        ],
        'new' => [
            'Equipe Editorial' => 'Equipe especializada em conteúdo automotivo',
            // 'Departamento Técnico' => 'Engenheiros e mecânicos especializados',
            'Redação' => 'Editores especialistas em veículos',
            'Equipe de Conteúdo' => 'Especialistas em informação automotiva'
        ]
    ];

    /**
     * Execute o comando automaticamente
     *
     * @return int
     */
    public function handle()
    {
        $this->startTime = time();
        $this->maxExecutionTime = (int) $this->option('max-execution-time');

        $this->info("🚀 Iniciando agendamento automático inteligente - " . date('Y-m-d H:i:s'));

        try {
            $this->initializeServices();

            $options = $this->processOptionsWithValidation();
            if (!$options) {
                return Command::FAILURE;
            }

            $draftArticles = $this->getDraftArticlesWithValidation($options);
            if ($draftArticles->isEmpty()) {
                $this->info('✅ Nenhum artigo encontrado para agendamento.');
                return Command::SUCCESS;
            }

            $result = $this->executeIntelligentScheduling($draftArticles, $options);
            $this->logFinalResults($result, $options);

            return $result['success'] ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Erro crítico: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Inicializa serviços
     */
    private function initializeServices(): void
    {
        $this->workingHours = new WorkingHours();
        $this->workingDaysCalculator = new WorkingDaysCalculatorService($this->workingHours);
        $this->timeDistribution = new HumanTimeDistributionService($this->workingHours);
        $this->autoSyncService = new AutoSyncService();
    }

    /**
     * Processa opções com validação inteligente
     */
    private function processOptionsWithValidation(): ?array
    {
        try {
            $startDate = $this->option('start-date')
                ? Carbon::createFromFormat('Y-m-d', $this->option('start-date'))
                : Carbon::tomorrow();

            if ($startDate->lt(Carbon::today())) {
                $startDate = Carbon::tomorrow();
                $this->info("📅 Data ajustada para amanhã.");
            }

            $days = max(1, min(90, (int) $this->option('days')));
            $minPosts = max(1, min(200, (int) $this->option('min-posts')));
            $maxPosts = max($minPosts, min(200, (int) $this->option('max-posts')));
            $batchSize = max(10, min(500, (int) $this->option('batch-size')));

            $adjustedStartDate = $this->workingDaysCalculator->adjustToNextWorkingDay($startDate);

            if (!$adjustedStartDate->eq($startDate)) {
                $this->info("📅 Data ajustada para próximo dia útil: {$adjustedStartDate->format('Y-m-d')}");
            }

            $preliminaryOptions = [
                'start_date' => $adjustedStartDate,
                'days' => $days,
                'min_posts' => $minPosts,
                'max_posts' => $maxPosts,
                'batch_size' => $batchSize,
                'imported_only' => $this->option('imported-only'),
                'new_only' => $this->option('new-only'),
                'auto_sync' => $this->option('auto-sync'),
                'manual-content' => $this->option('manual-content'),
                'dry_run' => $this->option('dry-run'),
            ];

            return $this->validateAndAdjustCapacity($preliminaryOptions);
        } catch (\Exception $e) {
            $this->error("❌ Erro ao processar opções: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Valida capacidade e ajusta dias automaticamente
     */
    private function validateAndAdjustCapacity(array $options): array
    {
        $articlesCount = $this->countDraftArticles($options);

        if ($articlesCount === 0) {
            return $options;
        }

        $maxCapacity = $this->maxSlotsPerDay * $options['days'];
        $daysNeeded = max(1, ceil($articlesCount / $this->maxSlotsPerDay));

        $this->info("📊 Análise de Capacidade:");
        $this->info("   • Artigos encontrados: {$articlesCount}");
        $this->info("   • Dias solicitados: {$options['days']}");
        $this->info("   • Capacidade atual: {$maxCapacity} slots");
        $this->info("   • Dias necessários: {$daysNeeded}");

        if ($daysNeeded > $options['days']) {
            $oldDays = $options['days'];
            $options['days'] = $daysNeeded;

            $this->warn("⚠️  AJUSTE AUTOMÁTICO:");
            $this->warn("   • Dias aumentados de {$oldDays} para {$daysNeeded}");
            $this->warn("   • Motivo: Capacidade insuficiente");
            $this->info("✅ Agendamento será distribuído em {$daysNeeded} dias úteis");
        } else {
            $this->info("✅ Capacidade suficiente para {$options['days']} dias");
        }

        return $options;
    }

    /**
     * Conta artigos draft preliminarmente
     */
    private function countDraftArticles(array $options): int
    {
        $query = TempArticle::where('status', 'draft');

        if ($options['imported_only']) {
            $query->where(function ($q) {
                $q->whereNotNull('original_post_id')
                    ->where('original_post_id', '!=', '');
            });
        } elseif ($options['new_only']) {
            $query->where(function ($q) {
                $q->whereNull('original_post_id')
                    ->orWhere('original_post_id', '');
            });
        }

        return $query->count();
    }

    /**
     * Obtém artigos draft com validação
     */
    private function getDraftArticlesWithValidation(array $options): Collection
    {
        $query = TempArticle::where('status', 'draft');

        if ($options['imported_only']) {
            $query->where(function ($q) {
                $q->whereNotNull('original_post_id')
                    ->where('original_post_id', '!=', '');
            });
            $this->info('🎯 Processando apenas artigos importados.');
        } elseif ($options['new_only']) {
            $query->where(function ($q) {
                $q->whereNull('original_post_id')
                    ->orWhere('original_post_id', '');
            });
            $this->info('🎯 Processando apenas artigos novos.');
        }

        $articles = $query->get();

        if (!$options['manual-content']) {
            if (!$this->validateArticlesQuickly($articles)) {
                $this->error("❌ Muitos artigos com dados inválidos");
                return collect();
            }
        }

        return $articles;
    }

    /**
     * Validação rápida melhorada
     */
    private function validateArticlesQuickly(Collection $articles): bool
    {
        if ($articles->isEmpty()) {
            return true;
        }

        $criticalErrors = 0;
        $sampleSize = min(100, $articles->count());

        foreach ($articles->take($sampleSize) as $article) {
            if (empty($article->title) || empty($article->slug)) {
                $criticalErrors++;
            }
        }

        $errorRate = ($criticalErrors / $sampleSize) * 100;

        if ($errorRate > 20) {
            $this->error("❌ Taxa de erro muito alta: {$errorRate}%");
            return false;
        }

        if ($errorRate > 10) {
            $this->warn("⚠️  Taxa de erro moderada: {$errorRate}%");
        }

        return true;
    }

    /**
     * Executa agendamento inteligente
     */
    private function executeIntelligentScheduling(Collection $articles, array $options): array
    {
        $result = [
            'success' => false,
            'total_articles' => $articles->count(),
            'processed' => 0,
            'scheduled' => 0,
            'failed' => 0,
            'execution_stopped_early' => false,
            'errors' => [],
            'sync_results' => null,
            'capacity_info' => [
                'max_slots_per_day' => $this->maxSlotsPerDay,
                'days_used' => $options['days'],
                'total_capacity' => $this->maxSlotsPerDay * $options['days']
            ]
        ];

        try {
            $this->info("🎯 Iniciando distribuição inteligente:");
            $this->info("   • Total de artigos: {$articles->count()}");
            $this->info("   • Capacidade total: {$result['capacity_info']['total_capacity']} slots");
            $this->info("   • Período: {$options['days']} dias úteis");

            $scheduledArticles = $this->processWithIntelligentDistribution($articles, $options, $result);
            $result['success'] = true;

            if ($options['auto_sync'] && !$options['dry_run'] && !empty($scheduledArticles)) {
                $this->info('🔄 Executando sincronização automática...');
                $syncResults = $this->autoSyncService->performCompleteSync(collect($scheduledArticles));
                $result['sync_results'] = $syncResults;
            }
        } catch (\Exception $e) {
            $result['errors'][] = "Erro geral: {$e->getMessage()}";
            $result['success'] = false;
            $this->error("❌ Erro durante agendamento: {$e->getMessage()}");
        }

        return $result;
    }

    /**
     * Processa com distribuição inteligente
     */
    private function processWithIntelligentDistribution(Collection $articles, array $options, array &$result): array
    {
        $scheduledArticles = [];

        $publishingPeriod = PublishingPeriod::createForDays(
            $options['start_date'],
            $options['days'],
            $options['min_posts'],
            min($this->maxSlotsPerDay, $options['max_posts'])
        );

        $this->info("📅 Período criado: {$publishingPeriod->getStartDate()->format('Y-m-d')} até {$publishingPeriod->getEndDate()->format('Y-m-d')}");

        $distribution = $this->createIntelligentDistribution($articles, $publishingPeriod);

        foreach ($distribution as $dayKey => $dayInfo) {
            if ($this->shouldStopExecution()) {
                $this->warn('⏰ Tempo limite atingido. Parando...');
                $result['execution_stopped_early'] = true;
                break;
            }

            $dayResult = $this->processDayWithValidation($dayInfo, $options);

            $result['processed'] += $dayResult['processed'];
            $result['scheduled'] += $dayResult['scheduled'];
            $result['failed'] += $dayResult['failed'];
            $scheduledArticles = array_merge($scheduledArticles, $dayResult['scheduled_articles']);

            if (!empty($dayResult['errors'])) {
                $result['errors'] = array_merge($result['errors'], $dayResult['errors']);
            }

            $this->info("📊 Dia {$dayInfo['date']->format('Y-m-d')}: {$dayResult['scheduled']} agendados, {$dayResult['failed']} falharam");
        }

        return $scheduledArticles;
    }

    /**
     * Cria distribuição inteligente
     */
    private function createIntelligentDistribution(Collection $articles, PublishingPeriod $publishingPeriod): array
    {
        $workingDays = $publishingPeriod->getWorkingDaysList();
        $totalArticles = $articles->count();
        $distribution = [];

        $this->info("🧮 Calculando distribuição inteligente...");

        $articleIndex = 0;
        foreach ($workingDays as $index => $workingDay) {
            $remainingDays = count($workingDays) - $index;
            $remainingArticles = $totalArticles - $articleIndex;

            if ($remainingArticles <= 0) {
                break;
            }

            if ($remainingDays === 1) {
                $articlesForDay = min($remainingArticles, $this->maxSlotsPerDay);
            } else {
                $avgPerDay = $remainingArticles / $remainingDays;
                $articlesForDay = min(
                    $this->maxSlotsPerDay,
                    max(1, round($avgPerDay))
                );
            }

            if ($articlesForDay > 0 && $articleIndex < $totalArticles) {
                $actualArticlesForDay = min($articlesForDay, $remainingArticles);
                $dayArticles = $articles->slice($articleIndex, $actualArticlesForDay);

                if ($dayArticles->count() !== $actualArticlesForDay) {
                    error_log("AVISO: Esperados {$actualArticlesForDay} artigos, obtidos {$dayArticles->count()}");
                    $actualArticlesForDay = $dayArticles->count();
                }

                $distribution[$workingDay->format('Y-m-d')] = [
                    'date' => $workingDay,
                    'articles' => $dayArticles,
                    'planned_count' => $actualArticlesForDay,
                    'actual_count' => $dayArticles->count(),
                    'start_index' => $articleIndex,
                    'end_index' => $articleIndex + $actualArticlesForDay - 1
                ];

                $articleIndex += $actualArticlesForDay;
            }
        }

        $totalDistributed = array_sum(array_column($distribution, 'actual_count'));
        if ($totalDistributed !== $totalArticles) {
            $this->warn("⚠️  Distribuição inconsistente: {$totalDistributed} distribuídos de {$totalArticles} totais");
        }

        $this->info("📊 Distribuição calculada:");
        foreach ($distribution as $dayKey => $dayInfo) {
            $this->info("   • {$dayKey}: {$dayInfo['actual_count']} artigos");
        }

        return $distribution;
    }

    /**
     * Processa dia com validação
     */
    private function processDayWithValidation(array $dayInfo, array $options): array
    {
        $dayResult = [
            'processed' => 0,
            'scheduled' => 0,
            'failed' => 0,
            'scheduled_articles' => [],
            'errors' => [],
        ];

        $dayArticles = $dayInfo['articles'];
        $dayDate = $dayInfo['date'];
        $dayKey = $dayDate->format('Y-m-d');

        if ($dayArticles->isEmpty()) {
            return $dayResult;
        }

        $articlesCount = $dayArticles->count();

        try {
            if ($articlesCount > $this->maxSlotsPerDay) {
                $this->warn("⚠️  Dia {$dayDate->format('Y-m-d')}: {$articlesCount} artigos excedem limite de {$this->maxSlotsPerDay}");
                $dayArticles = $dayArticles->take($this->maxSlotsPerDay);
                $articlesCount = $dayArticles->count();
            }

            $this->timeDistribution->resetUsedTimestamps($dayKey);
            $daySchedule = $this->generateDayScheduleWithFallback($dayDate, $articlesCount);

            if (empty($daySchedule)) {
                $dayResult['errors'][] = "Falha ao gerar cronograma para {$dayDate->format('Y-m-d')}";
                $dayResult['failed'] += $articlesCount;
                return $dayResult;
            }

            $slotsCount = count($daySchedule);

            if ($slotsCount < $articlesCount) {
                $this->warn("⚠️  {$dayKey}: Apenas {$slotsCount} slots para {$articlesCount} artigos");
                $dayArticles = $dayArticles->take($slotsCount);
                $articlesCount = $dayArticles->count();
            }

            $dayArticles = $dayArticles->values();
            $localIndex = 0;

            foreach ($dayArticles as $article) {
                try {
                    if (!isset($daySchedule[$localIndex])) {
                        $dayResult['errors'][] = "Slot indisponível para artigo {$article->_id} no dia {$dayDate->format('Y-m-d')} (index {$localIndex} de {$slotsCount} slots)";
                        $dayResult['failed']++;
                        $localIndex++;
                        continue;
                    }

                    $scheduleSlot = $daySchedule[$localIndex];
                    $articleData = $this->createScheduledArticleData($article, $scheduleSlot);

                    if (!$options['dry_run']) {
                        $this->saveScheduledArticle($article, $articleData);
                    }

                    $dayResult['scheduled_articles'][] = $articleData;
                    $dayResult['scheduled']++;
                } catch (\Exception $e) {
                    $dayResult['errors'][] = "Erro no artigo {$article->_id}: {$e->getMessage()}";
                    $dayResult['failed']++;
                }

                $dayResult['processed']++;
                $localIndex++;
            }
        } catch (\Exception $e) {
            $dayResult['errors'][] = "Erro no dia {$dayDate->format('Y-m-d')}: {$e->getMessage()}";
            $dayResult['failed'] += $articlesCount;
        }

        return $dayResult;
    }

    /**
     * Gera cronograma com fallback melhorado
     */
    private function generateDayScheduleWithFallback(Carbon $dayDate, int $articlesCount): array
    {
        $dayKey = $dayDate->format('Y-m-d');

        try {
            $schedule = $this->timeDistribution->generateDaySchedule($dayDate, $articlesCount, 'mixed');

            if (count($schedule) >= $articlesCount) {
                return $schedule;
            }

            $this->warn("⚠️  Gerando cronograma reduzido: {$articlesCount} → " . count($schedule));
            return $schedule;
        } catch (\Exception $e) {
            $this->error("❌ Erro ao gerar cronograma para {$dayKey}: {$e->getMessage()}");
            return $this->generateMinimalSchedule($dayDate, min($articlesCount, $this->maxSlotsPerDay));
        }
    }

    /**
     * Gera cronograma mínimo como fallback
     */
    private function generateMinimalSchedule(Carbon $dayDate, int $count): array
    {
        $schedule = [];
        $baseHour = 8;
        $maxHours = 15;
        $slotsPerHour = min(8, ceil($count / $maxHours));

        for ($i = 0; $i < $count && $i < $this->maxSlotsPerDay; $i++) {
            $hour = $baseHour + floor($i / $slotsPerHour);
            $minute = ($i % $slotsPerHour) * (60 / $slotsPerHour);

            if ($hour > 22) break;

            $scheduledTime = $dayDate->copy()->setTime($hour, (int)$minute, rand(1, 59));
            $schedule[] = new \Src\ArticleGenerator\Domain\ValueObjects\ScheduleSlot(
                $scheduledTime,
                'new'
            );
        }

        return $schedule;
    }

    /**
     * Cria dados do artigo agendado com autor automático
     */
    private function createScheduledArticleData($article, $scheduleSlot): array
    {
        $isImported = $this->isImportedArticle($article);
        $scheduledTime = $scheduleSlot->getScheduledAt();

        // Atribuir autor automaticamente
        $authorData = $this->assignAuthor($isImported);

        if ($isImported) {
            $originalDate = $article->published_at ?? $article->created_at ?? Carbon::now();

            return [
                'article' => $article,
                'status' => 'scheduled',
                'scheduled_at' => $scheduledTime,
                'created_at' => $originalDate,
                'published_at' => $originalDate,
                'updated_at' => $scheduleSlot->generateHumanizedUpdatedAt(),
                'article_type' => 'imported',
                'author' => $authorData,
            ];
        } else {
            return [
                'article' => $article,
                'status' => 'scheduled',
                'scheduled_at' => $scheduledTime,
                'created_at' => $scheduledTime,
                'published_at' => $scheduledTime,
                'updated_at' => $scheduledTime->copy()->addMinutes(rand(1, 30)),
                'article_type' => 'new',
                'author' => $authorData,
            ];
        }
    }

    /**
     * Verifica se artigo é importado (original_post_id válido)
     */
    private function isImportedArticle($article): bool
    {
        return !empty($article->original_post_id) &&
            $article->original_post_id !== '' &&
            $article->original_post_id !== null;
    }

    /**
     * Atribui autor baseado no tipo do artigo
     */
    private function assignAuthor(bool $isImported): array
    {
        $authorPool = $isImported ? $this->authors['imported'] : $this->authors['new'];
        $authorNames = array_keys($authorPool);
        $authorName = $authorNames[array_rand($authorNames)];
        $authorBio = $authorPool[$authorName];

        return [
            'name' => $authorName,
            'bio' => $authorBio
        ];
    }

    /**
     * Salva artigo agendado com autor
     */
    private function saveScheduledArticle($tempArticle, array $articleData): void
    {
        $tags = $this->extractTags($tempArticle);
        $relatedTopics = $this->extractRelatedTopics($tempArticle);
        $vehicleData = $this->extractVehicleData($tempArticle);

        Article::create([
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
            'tags' => $tags,
            'related_topics' => $relatedTopics ?? [],
            'status' => 'scheduled',
            'original_post_id' => $tempArticle->original_post_id ?? null,
            'created_at' => $articleData['created_at'],
            'published_at' => $articleData['published_at'],
            'updated_at' => $articleData['updated_at'],
            'scheduled_at' => $articleData['scheduled_at'],
            'vehicle_info' => $vehicleData['vehicle_info'] ?? null,
            'filter_data' => $vehicleData['filter_data'] ?? null,
            'author' => $articleData['author'], // Incluir dados do autor
        ]);

        $tempArticle->update(['status' => 'scheduled']);
    }

    /**
     * Verifica tempo limite
     */
    private function shouldStopExecution(): bool
    {
        return (time() - $this->startTime) >= $this->maxExecutionTime;
    }

    /**
     * Log dos resultados finais
     */
    private function logFinalResults(array $result, array $options): void
    {
        $duration = time() - $this->startTime;

        $this->info('');
        $this->info('🎉 === AGENDAMENTO AUTOMÁTICO CONCLUÍDO ===');
        $this->info("⏱️  Duração: {$duration} segundos");
        $this->info("📊 Total de artigos: {$result['total_articles']}");
        $this->info("✅ Processados: {$result['processed']}");
        $this->info("📅 Agendados: {$result['scheduled']}");
        $this->info("❌ Falharam: {$result['failed']}");
        $this->info("👤 Autores atribuídos automaticamente");

        if (isset($result['capacity_info'])) {
            $this->info("🎯 Capacidade utilizada: {$result['scheduled']}/{$result['capacity_info']['total_capacity']} slots");
        }

        if ($options['dry_run']) {
            $this->warn('🧪 MODO SIMULAÇÃO - Nenhuma alteração feita');
        }

        if ($result['execution_stopped_early']) {
            $this->warn('⏰ EXECUÇÃO INTERROMPIDA por tempo limite');
        }

        if ($result['processed'] > 0) {
            $avgTimePerArticle = round($duration / $result['processed'], 3);
            $successRate = round(($result['scheduled'] / $result['processed']) * 100, 1);

            $this->info("📈 Tempo médio por artigo: {$avgTimePerArticle}s");
            $this->info("📈 Taxa de sucesso: {$successRate}%");
        }

        if (!empty($result['errors'])) {
            $this->warn("⚠️  Erros encontrados: " . count($result['errors']));
            foreach (array_slice($result['errors'], 0, 5) as $error) {
                $this->error("   • {$error}");
            }

            if (count($result['errors']) > 5) {
                $remaining = count($result['errors']) - 5;
                $this->warn("   ... e mais {$remaining} erros (veja logs detalhados)");
            }
        }

        $logData = [
            'command' => 'articles:schedule-drafts',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'options' => $options,
            'results' => $result,
            'success' => $result['success'],
        ];

        file_put_contents(
            storage_path('logs/schedule-drafts-' . date('Y-m-d') . '.log'),
            json_encode($logData) . "\n",
            FILE_APPEND | LOCK_EX
        );

        $this->checkAlertsImproved($result);
    }

    /**
     * Verificar alertas melhorado
     */
    private function checkAlertsImproved(array $result): void
    {
        $alerts = [];

        if ($result['processed'] > 0) {
            $failureRate = ($result['failed'] / $result['processed']) * 100;
            if ($failureRate > 25) {
                $alerts[] = "Taxa de falha muito alta: {$failureRate}%";
            } elseif ($failureRate > 10) {
                $alerts[] = "Taxa de falha moderada: {$failureRate}%";
            }
        }

        if ($result['failed'] > 50) {
            $alerts[] = "Muitos artigos falharam: {$result['failed']}";
        }

        if ($result['execution_stopped_early']) {
            $alerts[] = "Execução interrompida por tempo";
        }

        if ($result['total_articles'] > 0 && $result['scheduled'] === 0 && !$result['execution_stopped_early']) {
            $alerts[] = "Nenhum artigo foi agendado com sucesso";
        }

        if (!empty($alerts)) {
            $this->warn('🚨 ALERTAS DETECTADOS:');
            foreach ($alerts as $alert) {
                $this->error("   ⚠️  {$alert}");
            }

            file_put_contents(
                storage_path('logs/alerts-' . date('Y-m-d') . '.log'),
                json_encode([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'command' => 'articles:schedule-drafts',
                    'alerts' => $alerts,
                    'results' => $result
                ]) . "\n",
                FILE_APPEND | LOCK_EX
            );
        } else {
            $this->info('✅ Nenhum alerta detectado - Execução bem-sucedida!');
        }
    }

    // Métodos auxiliares
    private function extractTags($article): array
    {
        $tags = [];

        if (!empty($article->seo_data['primary_keyword'])) {
            $tags[] = $article->seo_data['primary_keyword'];
        }

        if (!empty($article->seo_data['secondary_keywords']) && is_array($article->seo_data['secondary_keywords'])) {
            $tags = array_merge($tags, $article->seo_data['secondary_keywords']);
        }

        if (!empty($article->extracted_entities)) {
            foreach (['marca', 'modelo', 'categoria', 'tipo_veiculo', 'motorizacao'] as $entity) {
                if (!empty($article->extracted_entities[$entity])) {
                    $tags[] = $article->extracted_entities[$entity];
                }
            }
        }

        return array_values(array_unique(array_filter($tags)));
    }

    private function extractRelatedTopics($article): array
    {
        $topics = [];

        if (!empty($article->metadata['related_content']) && is_array($article->metadata['related_content'])) {
            foreach ($article->metadata['related_content'] as $related) {
                if (!empty($related['title']) && !empty($related['slug'])) {
                    $topics[] = [
                        'title' => $related['title'],
                        'slug' => $related['slug'],
                        'icon' => $related['icon'] ?? null
                    ];
                }
            }
        }

        return $topics;
    }

    private function extractVehicleData($article): array
    {
        $result = ['vehicle_info' => [], 'filter_data' => []];

        if (empty($article->extracted_entities)) {
            return $result;
        }

        $vehicleInfo = [];
        $filterData = [];

        $fields = [
            'marca' => 'make',
            'modelo' => 'model',
            'ano' => 'year',
            'versao' => 'version',
            'motorizacao' => 'engine',
            'combustivel' => 'fuel',
            'categoria' => 'category',
            'tipo_veiculo' => 'vehicle_type'
        ];

        foreach ($fields as $source => $target) {
            if (!empty($article->extracted_entities[$source])) {
                $vehicleInfo[$target] = $article->extracted_entities[$source];
                $filterData[$source] = $article->extracted_entities[$source];
            }
        }

        if (!empty($vehicleInfo['make'])) {
            $makeSlug = \Illuminate\Support\Str::slug($vehicleInfo['make']);
            $vehicleInfo['make_slug'] = $makeSlug;
            $filterData['marca_slug'] = $makeSlug;
        }

        return ['vehicle_info' => $vehicleInfo, 'filter_data' => $filterData];
    }
}
