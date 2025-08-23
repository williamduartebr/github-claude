<?php

namespace Src\ArticleGenerator\Domain\Services;

use Carbon\Carbon;
use Src\ArticleGenerator\Domain\ValueObjects\PublishingPeriod;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;

class WorkingDaysCalculatorService
{
    private WorkingHours $workingHours;

    public function __construct(WorkingHours $workingHours)
    {
        $this->workingHours = $workingHours;
    }

    /**
     * Calcula quantos dias úteis são necessários para agendar determinada quantidade de artigos
     */
    public function calculateWorkingDaysNeeded(
        int $totalArticles,
        int $minPostsPerDay = PublishingPeriod::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = PublishingPeriod::MAX_POSTS_PER_DAY
    ): int {
        if ($totalArticles <= 0) {
            return 0;
        }

        $avgPostsPerDay = ($minPostsPerDay + $maxPostsPerDay) / 2;
        return (int) ceil($totalArticles / $avgPostsPerDay);
    }

    /**
     * Calcula a data final baseada em uma data inicial e quantidade de dias úteis
     */
    public function calculateEndDate(Carbon $startDate, int $workingDaysNeeded): Carbon
    {
        $adjustedStartDate = $this->adjustToNextWorkingDay($startDate);
        $current = $adjustedStartDate->copy();
        $workingDaysCount = 0;

        while ($workingDaysCount < $workingDaysNeeded) {
            if ($this->workingHours->isWorkingDay($current->dayOfWeekIso)) {
                $workingDaysCount++;
            }
            
            if ($workingDaysCount < $workingDaysNeeded) {
                $current->addDay();
            }
        }

        return $current->endOfDay();
    }

    /**
     * Gera lista de todos os dias úteis entre duas datas
     */
    public function getWorkingDaysBetween(Carbon $startDate, Carbon $endDate): array
    {
        $workingDays = [];
        $current = $this->adjustToNextWorkingDay($startDate);

        while ($current->lte($endDate)) {
            if ($this->workingHours->isWorkingDay($current->dayOfWeekIso)) {
                $workingDays[] = $current->copy();
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Conta quantos dias úteis existem entre duas datas
     */
    public function countWorkingDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        return count($this->getWorkingDaysBetween($startDate, $endDate));
    }

    /**
     * Ajusta uma data para o próximo dia útil se necessário
     */
    public function adjustToNextWorkingDay(Carbon $date): Carbon
    {
        $adjusted = $date->copy()->startOfDay();
        
        // Se for fim de semana, ajustar para próxima segunda
        if ($adjusted->dayOfWeekIso == 6) { // Sábado
            $adjusted->addDays(2);
        } elseif ($adjusted->dayOfWeekIso == 7) { // Domingo
            $adjusted->addDay();
        }

        return $adjusted;
    }

    /**
     * Retorna o próximo dia útil após uma data específica
     */
    public function getNextWorkingDay(Carbon $date): Carbon
    {
        $next = $date->copy()->addDay();
        
        while (!$this->workingHours->isWorkingDay($next->dayOfWeekIso)) {
            $next->addDay();
        }

        return $next;
    }

    /**
     * Retorna o dia útil anterior a uma data específica
     */
    public function getPreviousWorkingDay(Carbon $date): Carbon
    {
        $previous = $date->copy()->subDay();
        
        while (!$this->workingHours->isWorkingDay($previous->dayOfWeekIso)) {
            $previous->subDay();
        }

        return $previous;
    }

    /**
     * Verifica se uma data é um dia útil
     */
    public function isWorkingDay(Carbon $date): bool
    {
        return $this->workingHours->isWorkingDay($date->dayOfWeekIso);
    }

    /**
     * Distribui artigos de forma otimizada pelos dias úteis
     */
    public function distributeArticlesAcrossWorkingDays(
        array $workingDays,
        int $totalArticles,
        int $minPostsPerDay = PublishingPeriod::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = PublishingPeriod::MAX_POSTS_PER_DAY
    ): array {
        if (empty($workingDays)) {
            return [];
        }

        $distribution = [];
        $remainingArticles = $totalArticles;
        $totalDays = count($workingDays);

        foreach ($workingDays as $index => $workingDay) {
            $remainingDays = $totalDays - $index;
            
            if ($remainingDays === 1) {
                // Último dia, distribuir todos os artigos restantes
                $postsForDay = min($remainingArticles, $maxPostsPerDay);
            } else {
                // Calcular distribuição ótima para este dia
                $avgRemaining = $remainingArticles / $remainingDays;
                
                // Adicionar variação baseada no dia da semana
                $dayVariation = $this->getDayOfWeekVariation($workingDay->dayOfWeekIso);
                $adjustedAvg = $avgRemaining * $dayVariation;
                
                // Definir limites para este dia
                $minForDay = max($minPostsPerDay, floor($adjustedAvg * 0.8));
                $maxForDay = min($maxPostsPerDay, ceil($adjustedAvg * 1.2));
                
                $postsForDay = min($remainingArticles, rand((int)$minForDay, (int)$maxForDay));
            }

            $postsForDay = max(0, min($postsForDay, $maxPostsPerDay));
            
            $distribution[] = [
                'date' => $workingDay->copy(),
                'posts_count' => $postsForDay,
                'day_of_week' => $workingDay->dayOfWeekIso,
                'day_name' => $workingDay->format('l'),
                'date_formatted' => $workingDay->format('Y-m-d'),
            ];

            $remainingArticles -= $postsForDay;
            
            if ($remainingArticles <= 0) {
                break;
            }
        }

        // Se ainda restam artigos, redistribuir pelos últimos dias
        if ($remainingArticles > 0) {
            $distribution = $this->redistributeRemainingArticles($distribution, $remainingArticles, $maxPostsPerDay);
        }

        return $distribution;
    }

    /**
     * Aplica variação baseada no dia da semana para distribuição mais realista
     */
    private function getDayOfWeekVariation(int $dayOfWeek): float
    {
        $variations = [
            1 => 1.1,  // Segunda - mais posts (começando a semana)
            2 => 1.2,  // Terça - pico
            3 => 1.2,  // Quarta - pico
            4 => 1.1,  // Quinta - normal
            5 => 0.9,  // Sexta - menos posts (final da semana)
        ];

        return $variations[$dayOfWeek] ?? 1.0;
    }

    /**
     * Redistribui artigos restantes quando a distribuição inicial não foi suficiente
     */
    private function redistributeRemainingArticles(array $distribution, int $remainingArticles, int $maxPostsPerDay): array
    {
        $distributionCount = count($distribution);
        
        // Tentar adicionar aos dias existentes primeiro
        for ($i = $distributionCount - 1; $i >= 0 && $remainingArticles > 0; $i--) {
            $currentPosts = $distribution[$i]['posts_count'];
            $canAdd = $maxPostsPerDay - $currentPosts;
            
            if ($canAdd > 0) {
                $toAdd = min($remainingArticles, $canAdd);
                $distribution[$i]['posts_count'] += $toAdd;
                $remainingArticles -= $toAdd;
            }
        }

        return $distribution;
    }

    /**
     * Calcula métricas de distribuição para análise
     */
    public function calculateDistributionMetrics(array $distribution): array
    {
        if (empty($distribution)) {
            return [];
        }

        $postsCounts = array_column($distribution, 'posts_count');
        $totalPosts = array_sum($postsCounts);
        
        return [
            'total_posts' => $totalPosts,
            'total_days' => count($distribution),
            'average_posts_per_day' => round($totalPosts / count($distribution), 2),
            'min_posts_per_day' => min($postsCounts),
            'max_posts_per_day' => max($postsCounts),
            'standard_deviation' => $this->calculateStandardDeviation($postsCounts),
            'distribution_efficiency' => $this->calculateDistributionEfficiency($postsCounts),
        ];
    }

    /**
     * Calcula desvio padrão da distribuição
     */
    private function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count <= 1) {
            return 0.0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / $count;

        return round(sqrt($variance), 2);
    }

    /**
     * Calcula eficiência da distribuição (quão uniforme está)
     */
    private function calculateDistributionEfficiency(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $min = min($values);
        $max = max($values);
        
        if ($max == 0) {
            return 0.0;
        }

        // Eficiência = quão próximo o mínimo está do máximo (0-100%)
        return round(($min / $max) * 100, 2);
    }

    /**
     * Gera relatório detalhado da distribuição
     */
    public function generateDistributionReport(array $distribution): array
    {
        $metrics = $this->calculateDistributionMetrics($distribution);
        
        $dayBreakdown = [];
        foreach ($distribution as $day) {
            $dayBreakdown[] = [
                'date' => $day['date_formatted'],
                'day_name' => $day['day_name'],
                'posts_count' => $day['posts_count'],
                'percentage_of_total' => round(($day['posts_count'] / $metrics['total_posts']) * 100, 1),
            ];
        }

        return [
            'summary' => $metrics,
            'day_breakdown' => $dayBreakdown,
            'period_info' => [
                'start_date' => $distribution[0]['date_formatted'] ?? null,
                'end_date' => end($distribution)['date_formatted'] ?? null,
                'working_days' => count($distribution),
            ],
        ];
    }
}