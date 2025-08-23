<?php

namespace Src\ArticleGenerator\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Src\ArticleGenerator\Domain\ValueObjects\ScheduleSlot;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;
use Src\ArticleGenerator\Domain\ValueObjects\PublishingPeriod;
use Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle;

class ArticleSchedulingService
{
    private WorkingDaysCalculatorService $workingDaysCalculator;
    private HumanTimeDistributionService $timeDistribution;
    private WorkingHours $workingHours;

    public function __construct(
        WorkingDaysCalculatorService $workingDaysCalculator,
        HumanTimeDistributionService $timeDistribution,
        WorkingHours $workingHours
    ) {
        $this->workingDaysCalculator = $workingDaysCalculator;
        $this->timeDistribution = $timeDistribution;
        $this->workingHours = $workingHours;
    }

    /**
     * Agenda artigos importados preservando datas originais
     */
    public function scheduleImportedArticle(
        TempArticle $article,
        Carbon $scheduleDate
    ): array {
        if (empty($article->original_post_id)) {
            throw new \InvalidArgumentException('Artigo deve ter original_post_id para ser considerado importado');
        }

        // Para artigos importados: preservar created_at e published_at originais
        $originalCreatedAt = $article->published_at ?? $article->created_at ?? Carbon::now();
        $originalPublishedAt = $article->published_at ?? $article->created_at ?? Carbon::now();

        // Gerar apenas updated_at humanizado
        $scheduleSlot = $this->timeDistribution->generateImportedArticleSchedule(
            $scheduleDate,
            $originalCreatedAt->format('Y-m-d H:i:s'),
            $originalPublishedAt->format('Y-m-d H:i:s')
        );

        return [
            'status' => 'scheduled',
            'scheduled_at' => $scheduleSlot->getScheduledAt(),
            'created_at' => Carbon::parse($originalCreatedAt), // Preserva original
            'published_at' => Carbon::parse($originalPublishedAt), // Preserva original
            'updated_at' => $scheduleSlot->generateHumanizedUpdatedAt(), // Humaniza apenas updated_at
            'schedule_slot' => $scheduleSlot,
            'article_type' => 'imported',
        ];
    }

    /**
     * Agenda artigos novos com datas completamente humanizadas
     */
    public function scheduleNewArticle(
        TempArticle $article,
        Carbon $scheduleDate
    ): array {
        if (!empty($article->original_post_id)) {
            throw new \InvalidArgumentException('Artigo não deve ter original_post_id para ser considerado novo');
        }

        // Para artigos novos: gerar todas as datas baseadas no agendamento
        $scheduleSlot = $this->timeDistribution->generateNewArticleSchedule($scheduleDate);
        $scheduledTime = $scheduleSlot->getScheduledAt();

        return [
            'status' => 'scheduled',
            'scheduled_at' => $scheduledTime,
            'created_at' => $scheduledTime, // Usa data do agendamento
            'published_at' => $scheduledTime, // Usa data do agendamento
            'updated_at' => $this->generateUpdatedAtForNewArticle($scheduledTime), // Humaniza updated_at
            'schedule_slot' => $scheduleSlot,
            'article_type' => 'new',
        ];
    }

    /**
     * Agenda múltiplos artigos em lotes organizados por dias úteis
     */
    public function scheduleBatchArticles(
        Collection $articles,
        Carbon $startDate,
        int $minPostsPerDay = PublishingPeriod::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = PublishingPeriod::MAX_POSTS_PER_DAY
    ): array {
        // Separar artigos por tipo
        $importedArticles = $articles->filter(fn($article) => !empty($article->original_post_id));
        $newArticles = $articles->filter(fn($article) => empty($article->original_post_id));

        // Criar período de publicação
        $publishingPeriod = PublishingPeriod::createForArticleCount(
            $startDate,
            $articles->count(),
            $minPostsPerDay,
            $maxPostsPerDay
        );

        // Distribuir artigos pelos dias úteis
        $distribution = $publishingPeriod->distributeArticles($articles->count());

        $scheduledArticles = [];
        $articleIndex = 0;

        foreach ($distribution as $dateKey => $dayInfo) {
            $dayDate = $dayInfo['date'];
            $postsForDay = $dayInfo['posts_count'];

            // Pegar artigos para este dia
            $dayArticles = $articles->slice($articleIndex, $postsForDay);
            $articleIndex += $postsForDay;

            // Separar por tipo para este dia
            $dayImported = $dayArticles->filter(fn($article) => !empty($article->original_post_id));
            $dayNew = $dayArticles->filter(fn($article) => empty($article->original_post_id));

            // Gerar cronograma para o dia
            $daySchedule = $this->generateDaySchedule(
                $dayDate,
                $dayImported->count(),
                $dayNew->count()
            );

            // Aplicar cronograma aos artigos
            $scheduledDay = $this->applyScheduleToArticles(
                $dayImported,
                $dayNew,
                $daySchedule
            );

            $scheduledArticles[$dateKey] = [
                'date' => $dayDate,
                'day_info' => $dayInfo,
                'articles' => $scheduledDay,
                'total_scheduled' => count($scheduledDay),
            ];
        }

        return [
            'publishing_period' => $publishingPeriod,
            'scheduled_articles' => $scheduledArticles,
            'statistics' => $this->calculateBatchStatistics($scheduledArticles),
        ];
    }

    /**
     * Gera cronograma para um dia específico
     */
    private function generateDaySchedule(
        Carbon $dayDate,
        int $importedCount,
        int $newCount
    ): array {
        $totalArticles = $importedCount + $newCount;
        
        if ($totalArticles === 0) {
            return [];
        }

        // Resetar timestamps usados para este dia
        $this->timeDistribution->resetUsedTimestamps();

        // Gerar slots para artigos importados
        $importedSlots = [];
        if ($importedCount > 0) {
            $importedSlots = $this->timeDistribution->generateDaySchedule(
                $dayDate,
                $importedCount,
                'imported'
            );
        }

        // Gerar slots para artigos novos
        $newSlots = [];
        if ($newCount > 0) {
            $newSlots = $this->timeDistribution->generateDaySchedule(
                $dayDate,
                $newCount,
                'new'
            );
        }

        // Combinar e ordenar todos os slots
        $allSlots = array_merge($importedSlots, $newSlots);
        
        usort($allSlots, function($a, $b) {
            return $a->getScheduledAt()->timestamp <=> $b->getScheduledAt()->timestamp;
        });

        return $allSlots;
    }

    /**
     * Aplica cronograma aos artigos
     */
    private function applyScheduleToArticles(
        Collection $importedArticles,
        Collection $newArticles,
        array $daySchedule
    ): array {
        $scheduledArticles = [];
        $slotIndex = 0;

        // Processar artigos importados
        foreach ($importedArticles as $article) {
            if (!isset($daySchedule[$slotIndex])) {
                break;
            }

            $scheduleSlot = $daySchedule[$slotIndex];
            $scheduledData = $this->applyScheduleSlotToImportedArticle($article, $scheduleSlot);
            
            $scheduledArticles[] = array_merge($scheduledData, [
                'article' => $article,
                'slot_index' => $slotIndex,
            ]);

            $slotIndex++;
        }

        // Processar artigos novos
        foreach ($newArticles as $article) {
            if (!isset($daySchedule[$slotIndex])) {
                break;
            }

            $scheduleSlot = $daySchedule[$slotIndex];
            $scheduledData = $this->applyScheduleSlotToNewArticle($article, $scheduleSlot);
            
            $scheduledArticles[] = array_merge($scheduledData, [
                'article' => $article,
                'slot_index' => $slotIndex,
            ]);

            $slotIndex++;
        }

        return $scheduledArticles;
    }

    /**
     * Aplica slot de agendamento a artigo importado
     */
    private function applyScheduleSlotToImportedArticle(
        TempArticle $article,
        ScheduleSlot $scheduleSlot
    ): array {
        $originalCreatedAt = $article->published_at ?? $article->created_at ?? Carbon::now();
        $originalPublishedAt = $article->published_at ?? $article->created_at ?? Carbon::now();

        return [
            'status' => 'scheduled',
            'scheduled_at' => $scheduleSlot->getScheduledAt(),
            'created_at' => Carbon::parse($originalCreatedAt),
            'published_at' => Carbon::parse($originalPublishedAt),
            'updated_at' => $scheduleSlot->generateHumanizedUpdatedAt(),
            'schedule_slot' => $scheduleSlot,
            'article_type' => 'imported',
        ];
    }

    /**
     * Aplica slot de agendamento a artigo novo
     */
    private function applyScheduleSlotToNewArticle(
        TempArticle $article,
        ScheduleSlot $scheduleSlot
    ): array {
        $scheduledTime = $scheduleSlot->getScheduledAt();

        return [
            'status' => 'scheduled',
            'scheduled_at' => $scheduledTime,
            'created_at' => $scheduledTime,
            'published_at' => $scheduledTime,
            'updated_at' => $this->generateUpdatedAtForNewArticle($scheduledTime),
            'schedule_slot' => $scheduleSlot,
            'article_type' => 'new',
        ];
    }

    /**
     * Gera updated_at humanizado para artigos novos
     */
    private function generateUpdatedAtForNewArticle(Carbon $scheduledTime): Carbon
    {
        // Para artigos novos, updated_at é próximo ao scheduled_at
        $minutesAfter = rand(1, 30); // Entre 1 e 30 minutos depois
        $secondsAfter = rand(1, 59);
        
        return $scheduledTime->copy()->addMinutes($minutesAfter)->addSeconds($secondsAfter);
    }

    /**
     * Reagenda artigos já existentes
     */
    public function rescheduleArticles(
        Collection $articles,
        Carbon $newStartDate,
        int $minPostsPerDay = PublishingPeriod::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = PublishingPeriod::MAX_POSTS_PER_DAY
    ): array {
        // Mesmo processo de agendamento, mas preservando metadados quando possível
        return $this->scheduleBatchArticles($articles, $newStartDate, $minPostsPerDay, $maxPostsPerDay);
    }

    /**
     * Calcula estatísticas do lote agendado
     */
    private function calculateBatchStatistics(array $scheduledArticles): array
    {
        $totalArticles = 0;
        $importedCount = 0;
        $newCount = 0;
        $dayCount = 0;
        $postCounts = [];

        foreach ($scheduledArticles as $dayData) {
            $dayCount++;
            $dayTotal = $dayData['total_scheduled'];
            $totalArticles += $dayTotal;
            $postCounts[] = $dayTotal;

            foreach ($dayData['articles'] as $articleData) {
                if ($articleData['article_type'] === 'imported') {
                    $importedCount++;
                } else {
                    $newCount++;
                }
            }
        }

        return [
            'total_articles' => $totalArticles,
            'imported_articles' => $importedCount,
            'new_articles' => $newCount,
            'working_days_used' => $dayCount,
            'average_posts_per_day' => $dayCount > 0 ? round($totalArticles / $dayCount, 2) : 0,
            'min_posts_per_day' => !empty($postCounts) ? min($postCounts) : 0,
            'max_posts_per_day' => !empty($postCounts) ? max($postCounts) : 0,
            'distribution_efficiency' => $this->calculateDistributionEfficiency($postCounts),
        ];
    }

    /**
     * Calcula eficiência da distribuição
     */
    private function calculateDistributionEfficiency(array $postCounts): float
    {
        if (empty($postCounts)) {
            return 0.0;
        }

        $min = min($postCounts);
        $max = max($postCounts);
        
        if ($max == 0) {
            return 0.0;
        }

        return round(($min / $max) * 100, 2);
    }

    /**
     * Valida se artigos podem ser agendados
     */
    public function validateArticlesForScheduling(Collection $articles): array
    {
        $errors = [];
        $warnings = [];

        if ($articles->isEmpty()) {
            $errors[] = 'Nenhum artigo fornecido para agendamento';
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        // Verificar se artigos têm dados necessários
        foreach ($articles as $index => $article) {
            if (empty($article->title)) {
                $errors[] = "Artigo {$index}: título não pode estar vazio";
            }

            if (empty($article->new_slug)) {
                $errors[] = "Artigo {$index}: slug não pode estar vazio";
            }

            if (empty($article->content)) {
                $warnings[] = "Artigo {$index}: conteúdo está vazio";
            }

            if ($article->status !== 'draft') {
                $warnings[] = "Artigo {$index}: status não é 'draft' ({$article->status})";
            }
        }

        // Verificar se há artigos demais para um período razoável
        $maxReasonableArticles = 30 * PublishingPeriod::MAX_POSTS_PER_DAY; // 30 dias
        if ($articles->count() > $maxReasonableArticles) {
            $warnings[] = "Muitos artigos ({$articles->count()}). Considere processar em lotes menores.";
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors),
        ];
    }

    /**
     * Gera relatório detalhado do agendamento
     */
    public function generateSchedulingReport(array $batchResult): array
    {
        $publishingPeriod = $batchResult['publishing_period'];
        $scheduledArticles = $batchResult['scheduled_articles'];
        $statistics = $batchResult['statistics'];

        return [
            'summary' => [
                'period' => [
                    'start_date' => $publishingPeriod->getStartDate()->format('Y-m-d'),
                    'end_date' => $publishingPeriod->getEndDate()->format('Y-m-d'),
                    'working_days' => $publishingPeriod->getWorkingDays(),
                ],
                'articles' => $statistics,
            ],
            'daily_breakdown' => array_map(function($dayData) {
                return [
                    'date' => $dayData['date']->format('Y-m-d'),
                    'day_name' => $dayData['date']->format('l'),
                    'total_articles' => $dayData['total_scheduled'],
                    'imported_articles' => count(array_filter($dayData['articles'], 
                        fn($a) => $a['article_type'] === 'imported')),
                    'new_articles' => count(array_filter($dayData['articles'], 
                        fn($a) => $a['article_type'] === 'new')),
                    'first_post_time' => !empty($dayData['articles']) 
                        ? $dayData['articles'][0]['scheduled_at']->format('H:i:s') 
                        : null,
                    'last_post_time' => !empty($dayData['articles']) 
                        ? end($dayData['articles'])['scheduled_at']->format('H:i:s') 
                        : null,
                ];
            }, $scheduledArticles),
            'recommendations' => $this->generateRecommendations($statistics),
        ];
    }

    /**
     * Gera recomendações baseadas nas estatísticas
     */
    private function generateRecommendations(array $statistics): array
    {
        $recommendations = [];

        if ($statistics['distribution_efficiency'] < 70) {
            $recommendations[] = 'Distribuição desigual detectada. Considere ajustar limites de posts por dia.';
        }

        if ($statistics['max_posts_per_day'] > PublishingPeriod::MAX_POSTS_PER_DAY) {
            $recommendations[] = 'Alguns dias excedem o limite recomendado. Considere estender o período.';
        }

        if ($statistics['working_days_used'] < 5) {
            $recommendations[] = 'Período muito curto. Considere distribuir por mais dias para melhor naturalidade.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Agendamento está bem distribuído e dentro dos parâmetros recomendados.';
        }

        return $recommendations;
    }
}