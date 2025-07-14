<?php

namespace Src\ArticleGenerator\Domain\Services;

use Carbon\Carbon;
use Src\ArticleGenerator\Domain\ValueObjects\ScheduleSlot;
use Src\ArticleGenerator\Domain\ValueObjects\WorkingHours;

class HumanTimeDistributionService
{
    private WorkingHours $workingHours;
    private array $usedTimestamps = [];
    
    // LIMITES DE SEGURANÇA - CORRIGIDOS
    private int $maxSlotsPerDay = 100; // Aumento para 100 slots por dia
    private int $maxSlotsPerHour = 8;  // Aumento para 8 slots por hora
    private int $maxRetries = 100;     // Aumento para 100 tentativas

    public function __construct(WorkingHours $workingHours)
    {
        $this->workingHours = $workingHours;
    }

    /**
     * Gera cronograma humanizado com validação de capacidade - VERSÃO REFATORADA
     */
    public function generateDaySchedule(
        Carbon $baseDate,
        int $articlesCount,
        string $articleType = 'new'
    ): array {
        if (!$this->workingHours->isWorkingDay($baseDate->dayOfWeekIso)) {
            throw new \InvalidArgumentException("Data deve ser um dia útil: {$baseDate->format('Y-m-d')}");
        }

        $dayKey = $baseDate->format('Y-m-d');
        
        // CORREÇÃO CRÍTICA: Sempre resetar timestamps para o dia específico
        $this->resetUsedTimestamps($dayKey);
        
        error_log("GERANDO CRONOGRAMA: {$dayKey} com {$articlesCount} artigos");

        // NOVA VALIDAÇÃO: Verificar e ajustar capacidade
        $originalCount = $articlesCount;
        $articlesCount = $this->validateAndAdjustCapacity($articlesCount, $baseDate);
        
        if ($articlesCount !== $originalCount) {
            error_log("INFO: Ajustado de {$originalCount} para {$articlesCount} artigos no dia {$baseDate->format('Y-m-d')}");
        }

        $scheduleSlots = [];
        
        try {
            // Distribuir artigos pelas horas do dia com validação
            $hourDistribution = $this->distributeArticlesByHourWithValidation($articlesCount, $baseDate->dayOfWeekIso);
            
            error_log("DISTRIBUIÇÃO HORÁRIA para {$dayKey}: " . json_encode($hourDistribution));
            
            // Gerar slots para cada hora
            foreach ($hourDistribution as $hour => $articlesInHour) {
                if ($articlesInHour <= 0) {
                    continue;
                }

                $hourSlots = $this->generateHourScheduleWithValidation(
                    $baseDate,
                    $hour,
                    $articlesInHour,
                    $articleType
                );

                $scheduleSlots = array_merge($scheduleSlots, $hourSlots);
                
                error_log("HORA {$hour}: Gerados " . count($hourSlots) . " de {$articlesInHour} slots");
            }

            // Validação final
            if (count($scheduleSlots) < $articlesCount) {
                $shortage = $articlesCount - count($scheduleSlots);
                error_log("AVISO: Conseguiu gerar apenas " . count($scheduleSlots) . " de {$articlesCount} slots solicitados. Faltam: {$shortage}");
                
                // Tentar completar com slots simples
                $scheduleSlots = $this->fillMissingSlots($scheduleSlots, $shortage, $baseDate, $articleType);
            }

        } catch (\Exception $e) {
            error_log("ERRO ao gerar cronograma para {$dayKey}: {$e->getMessage()}");
            
            // Fallback: gerar cronograma básico
            $scheduleSlots = $this->generateFallbackSchedule($baseDate, $articlesCount, $articleType);
        }

        // Ordenar por timestamp
        usort($scheduleSlots, function($a, $b) {
            return $a->getScheduledAt()->timestamp <=> $b->getScheduledAt()->timestamp;
        });

        error_log("RESULTADO FINAL {$dayKey}: " . count($scheduleSlots) . " slots gerados");

        return $scheduleSlots;
    }

    /**
     * NOVA FUNÇÃO: Valida e ajusta capacidade de acordo com limites
     */
    private function validateAndAdjustCapacity(int $requestedCount, Carbon $baseDate): int
    {
        // Calcular capacidade máxima real baseada nos horários
        $maxCapacity = $this->calculateRealMaxCapacity($baseDate);
        
        if ($requestedCount > $maxCapacity) {
            error_log("CAPACIDADE: Solicitados {$requestedCount}, máximo possível: {$maxCapacity}");
            return $maxCapacity;
        }
        
        // Verificar se não excede limite diário global
        if ($requestedCount > $this->maxSlotsPerDay) {
            error_log("LIMITE DIÁRIO: Solicitados {$requestedCount}, máximo permitido: {$this->maxSlotsPerDay}");
            return $this->maxSlotsPerDay;
        }

        return $requestedCount;
    }

    /**
     * NOVA FUNÇÃO: Calcula capacidade máxima real baseada nos horários de trabalho
     */
    private function calculateRealMaxCapacity(Carbon $baseDate): int
    {
        $workingHours = $this->workingHours->getWorkingHoursForDay($baseDate->dayOfWeekIso);
        $startHour = $workingHours['start'];
        $endHour = $workingHours['end'];
        
        $totalHours = $endHour - $startHour + 1;
        $maxCapacity = $totalHours * $this->maxSlotsPerHour;
        
        // Aplicar fator de eficiência mais generoso
        $efficiencyFactor = 0.95; // 95% de eficiência (era 80%)
        
        return (int) floor($maxCapacity * $efficiencyFactor);
    }

    /**
     * Distribui artigos pelas horas com validação melhorada - VERSÃO REFATORADA
     */
    private function distributeArticlesByHourWithValidation(int $totalArticles, int $dayOfWeek): array
    {
        $hourWeights = $this->workingHours->getAllHourWeights();
        $hourDistribution = [];
        
        // Inicializar distribuição
        foreach ($hourWeights as $hour => $weight) {
            $hourDistribution[$hour] = 0;
        }

        $remainingArticles = $totalArticles;

        // Primeira passagem: distribuição proporcional com limites
        foreach ($hourWeights as $hour => $weight) {
            if ($remainingArticles <= 0) {
                break;
            }

            $idealForHour = round($totalArticles * $weight);
            $actualForHour = min(
                $remainingArticles,
                $this->maxSlotsPerHour,
                max(0, $idealForHour)
            );
            
            $hourDistribution[$hour] = (int) $actualForHour;
            $remainingArticles -= $actualForHour;
        }

        // Segunda passagem: distribuir artigos restantes
        $attempts = 0;
        $maxAttempts = count($hourWeights) * 2;
        
        while ($remainingArticles > 0 && $attempts < $maxAttempts) {
            $distributed = false;
            
            // Priorizar horários de pico primeiro
            $peakHours = $this->workingHours->getPeakHoursForDay($dayOfWeek);
            
            foreach ($peakHours as $hour) {
                if ($remainingArticles <= 0) break;
                
                if (isset($hourDistribution[$hour]) && $hourDistribution[$hour] < $this->maxSlotsPerHour) {
                    $hourDistribution[$hour]++;
                    $remainingArticles--;
                    $distributed = true;
                }
            }
            
            // Se ainda há artigos, distribuir em qualquer horário disponível
            if ($remainingArticles > 0 && !$distributed) {
                foreach ($hourWeights as $hour => $weight) {
                    if ($remainingArticles <= 0) break;
                    
                    if ($hourDistribution[$hour] < $this->maxSlotsPerHour) {
                        $hourDistribution[$hour]++;
                        $remainingArticles--;
                        $distributed = true;
                    }
                }
            }
            
            if (!$distributed) {
                break; // Não conseguiu distribuir mais nada
            }
            
            $attempts++;
        }

        if ($remainingArticles > 0) {
            error_log("AVISO: {$remainingArticles} artigos não puderam ser distribuídos");
        }

        return array_filter($hourDistribution); // Remove horas com 0 artigos
    }

    /**
     * Gera cronograma para uma hora específica com validação - VERSÃO REFATORADA
     */
    private function generateHourScheduleWithValidation(
        Carbon $baseDate,
        int $hour,
        int $articlesCount,
        string $articleType
    ): array {
        $scheduleSlots = [];
        
        // Validar limites da hora
        $actualCount = min($articlesCount, $this->maxSlotsPerHour);
        
        if ($actualCount !== $articlesCount) {
            error_log("HORA {$hour}: Ajustado de {$articlesCount} para {$actualCount} artigos");
        }
        
        $isPeakHour = $this->workingHours->isPeakHour($baseDate->dayOfWeekIso, $hour);
        $baseSpacing = $this->calculateHourSpacing($hour, $actualCount, $isPeakHour);
        
        for ($i = 0; $i < $actualCount; $i++) {
            try {
                $scheduledTime = $this->generateUniqueTimeInHourWithValidation(
                    $baseDate,
                    $hour,
                    $i,
                    $baseSpacing,
                    $isPeakHour
                );

                if ($scheduledTime) {
                    $scheduleSlot = $articleType === 'imported' 
                        ? ScheduleSlot::forImportedArticle($scheduledTime, null, null, $isPeakHour)
                        : ScheduleSlot::forNewArticle($scheduledTime, $isPeakHour);

                    $scheduleSlots[] = $scheduleSlot;
                } else {
                    error_log("AVISO: Não foi possível gerar horário único para slot {$i} na hora {$hour}");
                }
                
            } catch (\Exception $e) {
                error_log("ERRO ao gerar slot {$i} na hora {$hour}: {$e->getMessage()}");
            }
        }

        return $scheduleSlots;
    }

    /**
     * Gera horário único dentro de uma hora com validação melhorada - VERSÃO REFATORADA
     */
    private function generateUniqueTimeInHourWithValidation(
        Carbon $baseDate,
        int $hour,
        int $articleIndex,
        float $baseSpacing,
        bool $isPeakHour
    ): ?Carbon {
        $dayKey = $baseDate->format('Y-m-d');
        $attempts = 0;

        do {
            // Calcular minuto base com espaçamento
            $baseMinute = ($articleIndex + 1) * $baseSpacing;
            
            // Adicionar variação natural
            $variation = $this->generateTimeVariationWithValidation($isPeakHour, $attempts);
            $minute = max(0, min(59, $baseMinute + $variation));
            
            // Gerar segundo aleatório
            $second = rand(1, 59);
            
            $scheduledTime = $baseDate->copy()->setTime($hour, (int)$minute, $second);
            $timestamp = $scheduledTime->timestamp;
            
            // Verificar se já foi usado
            if (!in_array($timestamp, $this->usedTimestamps[$dayKey])) {
                $this->usedTimestamps[$dayKey][] = $timestamp;
                return $scheduledTime;
            }
            
            $attempts++;
            
        } while ($attempts < $this->maxRetries);

        // Se não conseguiu gerar horário único, retornar null
        error_log("ERRO: Não foi possível gerar horário único após {$this->maxRetries} tentativas");
        return null;
    }

    /**
     * Gera variação temporal com validação - VERSÃO REFATORADA
     */
    private function generateTimeVariationWithValidation(bool $isPeakHour, int $attempts): int
    {
        $baseVariation = $isPeakHour ? 3 : 5;
        $attemptMultiplier = min(3, 1 + ($attempts * 0.2));
        
        // Comportamentos especiais com validação
        $rand = mt_rand(1, 1000) / 1000;
        
        if ($rand <= 0.1) {
            // 10% chance de cluster (posts próximos)
            return rand(-2, 2);
        } elseif ($rand <= 0.15) {
            // 5% chance de pausa (espaçamento maior)
            return rand(8, 15);
        } else {
            // 85% comportamento normal
            $maxVariation = (int)($baseVariation * $attemptMultiplier);
            return rand(-$maxVariation, $maxVariation);
        }
    }

    /**
     * NOVA FUNÇÃO: Preenche slots faltantes com horários simples
     */
    private function fillMissingSlots(array $existingSlots, int $missingCount, Carbon $baseDate, string $articleType): array
    {
        $dayKey = $baseDate->format('Y-m-d');
        $additionalSlots = [];
        
        // Estratégia: distribuir uniformemente ao longo do dia
        $startHour = 8;
        $endHour = 22;
        $totalHours = $endHour - $startHour;
        $slotsPerHour = min(8, ceil($missingCount / $totalHours));
        
        $slotIndex = 0;
        
        for ($hour = $startHour; $hour <= $endHour && $slotIndex < $missingCount; $hour++) {
            for ($slot = 0; $slot < $slotsPerHour && $slotIndex < $missingCount; $slot++) {
                $minute = $slot * (60 / $slotsPerHour); // Distribuir uniformemente
                $second = rand(1, 59);
                
                $scheduledTime = $baseDate->copy()->setTime($hour, (int)$minute, $second);
                $timestamp = $scheduledTime->timestamp;
                
                // Verificar se já foi usado
                if (!in_array($timestamp, $this->usedTimestamps[$dayKey])) {
                    $this->usedTimestamps[$dayKey][] = $timestamp;
                    
                    $scheduleSlot = $articleType === 'imported' 
                        ? ScheduleSlot::forImportedArticle($scheduledTime, null, null, false)
                        : ScheduleSlot::forNewArticle($scheduledTime, false);
                    
                    $additionalSlots[] = $scheduleSlot;
                    $slotIndex++;
                }
            }
        }
        
        return array_merge($existingSlots, $additionalSlots);
    }

    /**
     * NOVA FUNÇÃO: Gera cronograma de fallback em caso de erro
     */
    private function generateFallbackSchedule(Carbon $baseDate, int $articlesCount, string $articleType): array
    {
        $scheduleSlots = [];
        $dayKey = $baseDate->format('Y-m-d');
        
        // Cronograma agressivo: começar às 8h, distribuir uniformemente
        $startHour = 8;
        $endHour = 22;
        $totalHours = $endHour - $startHour; // 14 horas
        $maxCount = min($articlesCount, $this->maxSlotsPerDay);
        
        $slotsPerHour = min(8, ceil($maxCount / $totalHours));
        $slotIndex = 0;
        
        for ($hour = $startHour; $hour <= $endHour && $slotIndex < $maxCount; $hour++) {
            for ($slot = 0; $slot < $slotsPerHour && $slotIndex < $maxCount; $slot++) {
                $minute = $slot * (60 / $slotsPerHour); // Distribuir uniformemente na hora
                $second = rand(1, 59);
                
                $currentTime = $baseDate->copy()->setTime($hour, (int)$minute, $second);
                $timestamp = $currentTime->timestamp;
                
                // Verificar se já foi usado
                if (!in_array($timestamp, $this->usedTimestamps[$dayKey])) {
                    $this->usedTimestamps[$dayKey][] = $timestamp;
                    
                    $scheduleSlot = $articleType === 'imported' 
                        ? ScheduleSlot::forImportedArticle($currentTime->copy(), null, null, false)
                        : ScheduleSlot::forNewArticle($currentTime->copy(), false);
                    
                    $scheduleSlots[] = $scheduleSlot;
                    $slotIndex++;
                }
            }
        }
        
        error_log("FALLBACK: Gerados " . count($scheduleSlots) . " slots de emergência para {$articlesCount} solicitados");
        
        return $scheduleSlots;
    }

    /**
     * Calcula espaçamento entre artigos em uma hora (mantido igual)
     */
    private function calculateHourSpacing(int $hour, int $articlesCount, bool $isPeakHour): float
    {
        if ($articlesCount <= 1) {
            return 30.0;
        }

        $baseSpacing = 60.0 / ($articlesCount + 1);
        $hourMultiplier = $this->getHourSpacingMultiplier($hour);
        $peakMultiplier = $isPeakHour ? 0.8 : 1.2;
        
        return $baseSpacing * $hourMultiplier * $peakMultiplier;
    }

    /**
     * Multiplicador de espaçamento baseado na hora (mantido igual)
     */
    private function getHourSpacingMultiplier(int $hour): float
    {
        $multipliers = [
            7 => 2.0,   8 => 1.5,   9 => 1.0,   10 => 0.8,  11 => 0.8,
            12 => 1.5,  13 => 1.5,  14 => 0.9,  15 => 0.8,  16 => 0.8,
            17 => 1.0,  18 => 1.2,  19 => 1.5,  20 => 1.8,  21 => 2.0,
        ];

        return $multipliers[$hour] ?? 1.0;
    }

    /**
     * Gera schedule para artigos importados (mantido igual)
     */
    public function generateImportedArticleSchedule(
        Carbon $scheduleDate,
        string $originalCreatedAt,
        string $originalPublishedAt
    ): ScheduleSlot {
        $scheduledTime = $this->generateRealisticTime($scheduleDate);
        
        return ScheduleSlot::forImportedArticle(
            $scheduledTime,
            $originalCreatedAt,
            $originalPublishedAt,
            $this->workingHours->isPeakHour($scheduleDate->dayOfWeekIso, $scheduledTime->hour)
        );
    }

    /**
     * Gera schedule para artigos novos (mantido igual)
     */
    public function generateNewArticleSchedule(Carbon $scheduleDate): ScheduleSlot
    {
        $scheduledTime = $this->generateRealisticTime($scheduleDate);
        
        return ScheduleSlot::forNewArticle(
            $scheduledTime,
            $this->workingHours->isPeakHour($scheduleDate->dayOfWeekIso, $scheduledTime->hour)
        );
    }

    /**
     * Gera horário realista para um artigo individual (mantido igual)
     */
    private function generateRealisticTime(Carbon $baseDate): Carbon
    {
        $dayKey = $baseDate->format('Y-m-d');
        $attempts = 0;

        do {
            $hour = $this->workingHours->generateWeightedHour();
            $minute = rand(0, 59);
            $second = rand(1, 59);
            
            $scheduledTime = $baseDate->copy()->setTime($hour, $minute, $second);
            $timestamp = $scheduledTime->timestamp;
            
            $attempts++;
            
        } while (
            in_array($timestamp, $this->usedTimestamps[$dayKey] ?? []) && 
            $attempts < 30
        );

        $this->usedTimestamps[$dayKey][] = $timestamp;

        return $scheduledTime;
    }

    /**
     * Reseta timestamps usados - VERSÃO CORRIGIDA
     */
    public function resetUsedTimestamps(?string $specificDay = null): void
    {
        if ($specificDay) {
            // Reset apenas para um dia específico
            $this->usedTimestamps[$specificDay] = [];
            error_log("RESET: Timestamps resetados para o dia {$specificDay}");
        } else {
            // Reset geral
            $this->usedTimestamps = [];
            error_log("RESET: Todos os timestamps resetados");
        }
    }

    /**
     * NOVA FUNÇÃO: Retorna estatísticas de capacidade
     */
    public function getCapacityStatistics(Carbon $date): array
    {
        return [
            'max_slots_per_day' => $this->maxSlotsPerDay,
            'max_slots_per_hour' => $this->maxSlotsPerHour,
            'real_max_capacity' => $this->calculateRealMaxCapacity($date),
            'working_hours' => $this->workingHours->getWorkingHoursForDay($date->dayOfWeekIso),
        ];
    }

    /**
     * Retorna estatísticas dos timestamps usados (mantido igual)
     */
    public function getUsageStatistics(): array
    {
        $stats = [];
        
        foreach ($this->usedTimestamps as $date => $timestamps) {
            $stats[$date] = [
                'total_slots' => count($timestamps),
                'hour_distribution' => $this->calculateHourDistribution($timestamps),
                'conflicts_avoided' => $this->calculateConflictsAvoided($timestamps),
            ];
        }

        return $stats;
    }

    /**
     * Calcula distribuição por hora (mantido igual)
     */
    private function calculateHourDistribution(array $timestamps): array
    {
        $distribution = [];
        
        foreach ($timestamps as $timestamp) {
            $hour = (int)date('G', $timestamp);
            $distribution[$hour] = ($distribution[$hour] ?? 0) + 1;
        }

        return $distribution;
    }

    /**
     * Calcula quantos conflitos foram evitados (mantido igual)
     */
    private function calculateConflictsAvoided(array $timestamps): int
    {
        $totalSlots = count($timestamps);
        $secondsInDay = 24 * 60 * 60;
        $conflictProbability = $totalSlots / $secondsInDay;
        
        return (int)round($totalSlots * $conflictProbability);
    }
}
