<?php

namespace Src\ArticleGenerator\Domain\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

class PublishingPeriod
{
    private Carbon $startDate;
    private Carbon $endDate;
    private int $totalDays;
    private int $workingDays;
    private int $minPostsPerDay;
    private int $maxPostsPerDay;
    private array $workingDaysList;

    // LIMITES DE SEGURANÇA - CORRIGIDOS
    private int $maxSlotsPerDay = 100;        // Aumento para 100 slots por dia
    private int $absoluteMaxPostsPerDay = 120; // Aumento para 120 posts por dia

    // Configurações padrão mantidas (estas estão corretas)
    public const MIN_POSTS_PER_DAY = 50; // ✅ MANTIDO
    public const MAX_POSTS_PER_DAY = 80; // ✅ MANTIDO

    public function __construct(
        Carbon $startDate,
        Carbon $endDate,
        int $minPostsPerDay = self::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = self::MAX_POSTS_PER_DAY
    ) {
        $this->validateDates($startDate, $endDate);
        $this->validatePostLimits($minPostsPerDay, $maxPostsPerDay);

        $this->startDate = $startDate->copy()->startOfDay();
        $this->endDate = $endDate->copy()->endOfDay();
        
        // NOVA VALIDAÇÃO: Ajustar limites se necessário
        $this->minPostsPerDay = min($minPostsPerDay, $this->maxSlotsPerDay);
        $this->maxPostsPerDay = min($maxPostsPerDay, $this->maxSlotsPerDay);

        $this->calculatePeriodMetrics();
    }

    /**
     * VERSÃO REFATORADA: Cria período para dias específicos com validação
     */
    public static function createForDays(
        Carbon $startDate,
        int $workingDays,
        int $minPostsPerDay = self::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = self::MAX_POSTS_PER_DAY
    ): self {
        $adjustedStartDate = self::adjustToNextWorkingDay($startDate);
        $endDate = self::calculateEndDateForWorkingDays($adjustedStartDate, $workingDays);

        return new self($adjustedStartDate, $endDate, $minPostsPerDay, $maxPostsPerDay);
    }

    /**
     * VERSÃO REFATORADA: Cria período para quantidade de artigos com validação inteligente
     */
    public static function createForArticleCount(
        Carbon $startDate,
        int $totalArticles,
        int $minPostsPerDay = self::MIN_POSTS_PER_DAY,
        int $maxPostsPerDay = self::MAX_POSTS_PER_DAY
    ): self {
        $adjustedStartDate = self::adjustToNextWorkingDay($startDate);
        
        // NOVA LÓGICA: Calcular dias necessários com limite de segurança
        $maxSlotsPerDay = 80; // Usar o limite máximo padrão, não o conservador
        $effectiveMaxPerDay = min($maxPostsPerDay, $maxSlotsPerDay);
        
        $workingDaysNeeded = max(1, ceil($totalArticles / $effectiveMaxPerDay));
        
        // Log da decisão
        error_log("PERÍODO: {$totalArticles} artigos → {$workingDaysNeeded} dias (max {$effectiveMaxPerDay}/dia)");
        
        $endDate = self::calculateEndDateForWorkingDays($adjustedStartDate, $workingDaysNeeded);

        return new self($adjustedStartDate, $endDate, $minPostsPerDay, $effectiveMaxPerDay);
    }

    private function calculatePeriodMetrics(): void
    {
        $this->totalDays = $this->startDate->diffInDays($this->endDate) + 1;
        $this->workingDaysList = $this->generateWorkingDaysList();
        $this->workingDays = count($this->workingDaysList);
    }

    private function generateWorkingDaysList(): array
    {
        $workingDays = [];
        $current = $this->startDate->copy();

        while ($current->lte($this->endDate)) {
            // 1-5 = segunda a sexta
            if ($current->dayOfWeekIso >= 1 && $current->dayOfWeekIso <= 5) {
                $workingDays[] = $current->copy();
            }
            $current->addDay();
        }

        return $workingDays;
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getEndDate(): Carbon
    {
        return $this->endDate;
    }

    public function getTotalDays(): int
    {
        return $this->totalDays;
    }

    public function getWorkingDays(): int
    {
        return $this->workingDays;
    }

    public function getWorkingDaysList(): array
    {
        return $this->workingDaysList;
    }

    public function getMinPostsPerDay(): int
    {
        return $this->minPostsPerDay;
    }

    public function getMaxPostsPerDay(): int
    {
        return $this->maxPostsPerDay;
    }

    public function getAveragePostsPerDay(): float
    {
        return ($this->minPostsPerDay + $this->maxPostsPerDay) / 2;
    }

    /**
     * VERSÃO REFATORADA: Calcula capacidade máxima com limites de segurança
     */
    public function getMaxArticleCapacity(): int
    {
        return $this->workingDays * min($this->maxPostsPerDay, $this->maxSlotsPerDay);
    }

    /**
     * VERSÃO REFATORADA: Calcula capacidade mínima com validação
     */
    public function getMinArticleCapacity(): int
    {
        return $this->workingDays * min($this->minPostsPerDay, $this->maxSlotsPerDay);
    }

    /**
     * VERSÃO REFATORADA: Distribui artigos com validação inteligente e ajuste automático
     */
    public function distributeArticles(int $totalArticles): array
    {
        // NOVA VALIDAÇÃO: Verificar capacidade e ajustar se necessário
        $maxCapacity = $this->getMaxArticleCapacity();
        
        if ($totalArticles > $maxCapacity) {
            error_log("CAPACIDADE EXCEDIDA: {$totalArticles} artigos > {$maxCapacity} capacidade");
            
            // Opção 1: Estender período automaticamente
            $this->extendPeriodForCapacity($totalArticles);
            
            // Opção 2: Ou simplesmente limitar (mais conservador)
            // $totalArticles = $maxCapacity;
        }

        return $this->performIntelligentDistribution($totalArticles);
    }

    /**
     * NOVA FUNÇÃO: Estende período automaticamente para acomodar todos os artigos
     */
    private function extendPeriodForCapacity(int $totalArticles): void
    {
        $effectiveMaxPerDay = min($this->maxPostsPerDay, $this->maxSlotsPerDay);
        $daysNeeded = ceil($totalArticles / $effectiveMaxPerDay);
        
        if ($daysNeeded > $this->workingDays) {
            error_log("ESTENDENDO PERÍODO: {$this->workingDays} → {$daysNeeded} dias");
            
            $newEndDate = self::calculateEndDateForWorkingDays($this->startDate, $daysNeeded);
            $this->endDate = $newEndDate;
            $this->workingDaysList = $this->generateWorkingDaysList();
            $this->workingDays = count($this->workingDaysList);
        }
    }

    /**
     * NOVA FUNÇÃO: Executa distribuição inteligente com validação
     */
    private function performIntelligentDistribution(int $totalArticles): array
    {
        $distribution = [];
        $remainingArticles = $totalArticles;

        foreach ($this->workingDaysList as $index => $workingDay) {
            $remainingDays = count($this->workingDaysList) - $index;
            
            if ($remainingDays === 1) {
                // Último dia: todos os restantes, mas respeitando limite
                $postsForDay = min($remainingArticles, $this->maxSlotsPerDay);
            } else {
                // Calcular distribuição inteligente para este dia
                $postsForDay = $this->calculatePostsForDay(
                    $remainingArticles, 
                    $remainingDays, 
                    $workingDay
                );
            }

            $postsForDay = max(0, min($postsForDay, $this->maxSlotsPerDay));
            
            if ($postsForDay > 0) {
                $distribution[$workingDay->format('Y-m-d')] = [
                    'date' => $workingDay->copy(),
                    'posts_count' => $postsForDay,
                    'day_of_week' => $workingDay->dayOfWeekIso,
                    'day_name' => $workingDay->format('l'),
                    'is_peak_capacity' => $postsForDay >= $this->maxSlotsPerDay * 0.9, // 90% da capacidade
                ];

                $remainingArticles -= $postsForDay;
            }
            
            if ($remainingArticles <= 0) {
                break;
            }
        }

        // NOVA VALIDAÇÃO: Redistribuir artigos restantes se necessário
        if ($remainingArticles > 0) {
            $distribution = $this->redistributeRemainingArticlesIntelligent($distribution, $remainingArticles);
        }

        return $distribution;
    }

    /**
     * NOVA FUNÇÃO: Calcula posts para um dia específico com lógica inteligente
     */
    private function calculatePostsForDay(int $remainingArticles, int $remainingDays, Carbon $workingDay): int
    {
        // Base: distribuição uniforme
        $avgRemaining = $remainingArticles / $remainingDays;
        
        // Aplicar variação baseada no dia da semana
        $dayVariation = $this->getDayOfWeekVariationImproved($workingDay->dayOfWeekIso);
        $adjustedAvg = $avgRemaining * $dayVariation;
        
        // Aplicar limites dinâmicos
        $minForDay = max($this->minPostsPerDay, floor($adjustedAvg * 0.7));
        $maxForDay = min($this->maxSlotsPerDay, ceil($adjustedAvg * 1.3));
        
        // Gerar valor final dentro dos limites
        $postsForDay = min($remainingArticles, rand((int)$minForDay, (int)$maxForDay));
        
        return max(1, $postsForDay); // Pelo menos 1 post por dia
    }

    /**
     * NOVA FUNÇÃO: Variação por dia da semana melhorada
     */
    private function getDayOfWeekVariationImproved(int $dayOfWeek): float
    {
        $variations = [
            1 => 1.05, // Segunda - início da semana, um pouco mais
            2 => 1.15, // Terça - pico da semana
            3 => 1.10, // Quarta - ainda alto
            4 => 1.00, // Quinta - normal
            5 => 0.85, // Sexta - fim de semana, menos
        ];

        return $variations[$dayOfWeek] ?? 1.0;
    }

    /**
     * VERSÃO REFATORADA: Redistribui artigos restantes de forma inteligente
     */
    private function redistributeRemainingArticlesIntelligent(array $distribution, int $remainingArticles): array
    {
        $distributionCount = count($distribution);
        
        // Primeira tentativa: adicionar aos dias existentes que têm capacidade
        for ($i = $distributionCount - 1; $i >= 0 && $remainingArticles > 0; $i--) {
            $dayKey = array_keys($distribution)[$i];
            $currentPosts = $distribution[$dayKey]['posts_count'];
            $canAdd = $this->maxSlotsPerDay - $currentPosts;
            
            if ($canAdd > 0) {
                $toAdd = min($remainingArticles, $canAdd);
                $distribution[$dayKey]['posts_count'] += $toAdd;
                $remainingArticles -= $toAdd;
                
                // Marcar como pico de capacidade se necessário
                if ($distribution[$dayKey]['posts_count'] >= $this->maxSlotsPerDay * 0.9) {
                    $distribution[$dayKey]['is_peak_capacity'] = true;
                }
            }
        }

        // Segunda tentativa: adicionar dias extras se ainda restam artigos
        if ($remainingArticles > 0) {
            error_log("ADICIONANDO DIAS EXTRAS: {$remainingArticles} artigos restantes");
            $distribution = $this->addExtraDays($distribution, $remainingArticles);
        }

        return $distribution;
    }

    /**
     * NOVA FUNÇÃO: Adiciona dias extras para artigos restantes
     */
    private function addExtraDays(array $distribution, int $remainingArticles): array
    {
        $lastDate = end($distribution)['date'] ?? $this->endDate;
        $currentDate = $lastDate->copy()->addDay();
        
        while ($remainingArticles > 0) {
            // Encontrar próximo dia útil
            while ($currentDate->dayOfWeekIso > 5) {
                $currentDate->addDay();
            }
            
            $postsForDay = min($remainingArticles, $this->maxSlotsPerDay);
            
            $distribution[$currentDate->format('Y-m-d')] = [
                'date' => $currentDate->copy(),
                'posts_count' => $postsForDay,
                'day_of_week' => $currentDate->dayOfWeekIso,
                'day_name' => $currentDate->format('l'),
                'is_peak_capacity' => $postsForDay >= $this->maxSlotsPerDay * 0.9,
                'is_extra_day' => true, // Marca como dia adicional
            ];
            
            $remainingArticles -= $postsForDay;
            $currentDate->addDay();
            
            // Atualizar período
            $this->endDate = $currentDate->copy();
            $this->workingDaysList[] = $currentDate->copy()->subDay();
            $this->workingDays++;
        }
        
        return $distribution;
    }

    /**
     * Verifica se uma data está dentro do período (mantido)
     */
    public function containsDate(Carbon $date): bool
    {
        return $date->gte($this->startDate) && $date->lte($this->endDate);
    }

    /**
     * Verifica se uma data é um dia útil dentro do período (mantido)
     */
    public function isWorkingDateInPeriod(Carbon $date): bool
    {
        if (!$this->containsDate($date)) {
            return false;
        }

        return $date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 5;
    }

    /**
     * Retorna o próximo dia útil dentro do período (mantido)
     */
    public function getNextWorkingDay(Carbon $fromDate): ?Carbon
    {
        $current = $fromDate->copy();
        
        while ($current->lte($this->endDate)) {
            if ($current->dayOfWeekIso >= 1 && $current->dayOfWeekIso <= 5) {
                return $current;
            }
            $current->addDay();
        }

        return null;
    }

    /**
     * VERSÃO REFATORADA: Calcula estatísticas com informações de capacidade
     */
    public function getStatistics(): array
    {
        return [
            'period' => [
                'start_date' => $this->startDate->format('Y-m-d'),
                'end_date' => $this->endDate->format('Y-m-d'),
                'total_days' => $this->totalDays,
                'working_days' => $this->workingDays,
                'weekend_days' => $this->totalDays - $this->workingDays,
            ],
            'capacity' => [
                'min_posts_per_day' => $this->minPostsPerDay,
                'max_posts_per_day' => $this->maxPostsPerDay,
                'max_slots_per_day' => $this->maxSlotsPerDay,
                'avg_posts_per_day' => $this->getAveragePostsPerDay(),
                'min_total_capacity' => $this->getMinArticleCapacity(),
                'max_total_capacity' => $this->getMaxArticleCapacity(),
                'efficiency_rate' => $this->calculateEfficiencyRate(),
            ],
            'working_days_breakdown' => array_map(function($day) {
                return [
                    'date' => $day->format('Y-m-d'),
                    'day_name' => $day->format('l'),
                    'day_of_week' => $day->dayOfWeekIso,
                    'day_variation' => $this->getDayOfWeekVariationImproved($day->dayOfWeekIso),
                ];
            }, $this->workingDaysList),
        ];
    }

    /**
     * NOVA FUNÇÃO: Calcula taxa de eficiência do período
     */
    private function calculateEfficiencyRate(): float
    {
        if ($this->maxPostsPerDay === 0) {
            return 0.0;
        }

        // Eficiência = capacidade real / capacidade teórica
        $theoreticalCapacity = $this->workingDays * $this->absoluteMaxPostsPerDay;
        $realCapacity = $this->getMaxArticleCapacity();
        
        return round(($realCapacity / $theoreticalCapacity) * 100, 2);
    }

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate->format('Y-m-d H:i:s'),
            'total_days' => $this->totalDays,
            'working_days' => $this->workingDays,
            'min_posts_per_day' => $this->minPostsPerDay,
            'max_posts_per_day' => $this->maxPostsPerDay,
            'max_slots_per_day' => $this->maxSlotsPerDay,
            'min_capacity' => $this->getMinArticleCapacity(),
            'max_capacity' => $this->getMaxArticleCapacity(),
            'efficiency_rate' => $this->calculateEfficiencyRate(),
        ];
    }

    private static function adjustToNextWorkingDay(Carbon $date): Carbon
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

    private static function calculateEndDateForWorkingDays(Carbon $startDate, int $workingDays): Carbon
    {
        $current = $startDate->copy();
        $count = 0;

        while ($count < $workingDays) {
            if ($current->dayOfWeekIso >= 1 && $current->dayOfWeekIso <= 5) {
                $count++;
            }
            
            if ($count < $workingDays) {
                $current->addDay();
            }
        }

        return $current->endOfDay();
    }

    /**
     * VERSÃO REFATORADA: Validação de datas melhorada
     */
    private function validateDates(Carbon $startDate, Carbon $endDate): void
    {
        if ($startDate->gt($endDate)) {
            throw new InvalidArgumentException('Data inicial deve ser anterior à data final');
        }

        if ($startDate->lt(Carbon::today())) {
            throw new InvalidArgumentException('Data inicial não pode ser no passado');
        }

        // NOVA VALIDAÇÃO: Período muito longo
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff > 365) {
            throw new InvalidArgumentException('Período não pode exceder 1 ano');
        }
    }

    /**
     * VERSÃO REFATORADA: Validação de limites com novos parâmetros
     */
    private function validatePostLimits(int $minPostsPerDay, int $maxPostsPerDay): void
    {
        if ($minPostsPerDay <= 0) {
            throw new InvalidArgumentException('Mínimo de posts por dia deve ser maior que zero');
        }

        if ($maxPostsPerDay <= 0) {
            throw new InvalidArgumentException('Máximo de posts por dia deve ser maior que zero');
        }

        if ($minPostsPerDay > $maxPostsPerDay) {
            throw new InvalidArgumentException('Mínimo de posts por dia não pode ser maior que o máximo');
        }

        // NOVA VALIDAÇÃO: Limite absoluto
        if ($maxPostsPerDay > $this->absoluteMaxPostsPerDay) {
            throw new InvalidArgumentException("Máximo de posts por dia não pode exceder {$this->absoluteMaxPostsPerDay} (limite de segurança)");
        }

        // NOVA VALIDAÇÃO: Aviso para limites altos
        if ($maxPostsPerDay > $this->maxSlotsPerDay) {
            error_log("AVISO: Limite solicitado ({$maxPostsPerDay}) maior que capacidade recomendada ({$this->maxSlotsPerDay})");
        }
    }
}