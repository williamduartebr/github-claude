<?php

namespace Src\ArticleGenerator\Domain\ValueObjects;

use InvalidArgumentException;

class WorkingHours
{
    private array $weeklySchedule;
    private array $peakHours;
    private array $hourWeights;

    public function __construct()
    {
        $this->setupWeeklySchedule();
        $this->setupHourWeights();
    }

    private function setupWeeklySchedule(): void
    {
        // Configuração de horários por dia da semana
        $this->weeklySchedule = [
            // Segunda a quinta (8h às 18h, com maior concentração em horários comerciais)
            1 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Segunda
            2 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Terça
            3 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Quarta
            4 => ['start' => 8, 'end' => 18, 'peak' => [10, 15]], // Quinta
            5 => ['start' => 8, 'end' => 17, 'peak' => [10, 14]], // Sexta (término mais cedo)
        ];

        // Configuração de horários de pico por dia
        $this->peakHours = [
            1 => [9, 10, 11, 14, 15, 16], // Segunda
            2 => [9, 10, 11, 14, 15, 16], // Terça
            3 => [9, 10, 11, 14, 15, 16], // Quarta
            4 => [9, 10, 11, 14, 15, 16], // Quinta
            5 => [9, 10, 11, 14, 15],     // Sexta
        ];
    }

    private function setupHourWeights(): void
    {
        // Pesos para distribuição por hora do dia (maior peso = mais posts)
        $this->hourWeights = [];
        
        // Início do dia (7-8): começo lento
        for ($h = 7; $h < 9; $h++) {
            $this->hourWeights[$h] = 0.08;
        }
        
        // Pico da manhã (9-12): alta atividade
        for ($h = 9; $h < 12; $h++) {
            $this->hourWeights[$h] = 0.18;
        }
        
        // Almoço (12-14): redução
        for ($h = 12; $h < 14; $h++) {
            $this->hourWeights[$h] = 0.06;
        }
        
        // Tarde (14-18): pico de atividade
        for ($h = 14; $h < 18; $h++) {
            $this->hourWeights[$h] = 0.20;
        }
        
        // Final do dia (18-22): atividade moderada a baixa
        for ($h = 18; $h < 22; $h++) {
            $this->hourWeights[$h] = 0.05;
        }

        // Normalizar os pesos para somar 1.0
        $totalWeight = array_sum($this->hourWeights);
        foreach ($this->hourWeights as $h => $weight) {
            $this->hourWeights[$h] = $weight / $totalWeight;
        }
    }

    public function getWorkingHoursForDay(int $dayOfWeek): array
    {
        $this->validateDayOfWeek($dayOfWeek);
        
        if (!$this->isWorkingDay($dayOfWeek)) {
            throw new InvalidArgumentException("Dia {$dayOfWeek} não é um dia útil");
        }

        return $this->weeklySchedule[$dayOfWeek];
    }

    public function isWorkingDay(int $dayOfWeek): bool
    {
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }

    public function isPeakHour(int $dayOfWeek, int $hour): bool
    {
        $this->validateDayOfWeek($dayOfWeek);
        
        if (!$this->isWorkingDay($dayOfWeek)) {
            return false;
        }

        return in_array($hour, $this->peakHours[$dayOfWeek] ?? []);
    }

    public function getHourWeight(int $hour): float
    {
        return $this->hourWeights[$hour] ?? 0.0;
    }

    public function getAllHourWeights(): array
    {
        return $this->hourWeights;
    }

    public function getPeakHoursForDay(int $dayOfWeek): array
    {
        $this->validateDayOfWeek($dayOfWeek);
        
        if (!$this->isWorkingDay($dayOfWeek)) {
            return [];
        }

        return $this->peakHours[$dayOfWeek] ?? [];
    }

    /**
     * Gera um horário aleatório respeitando a distribuição de pesos
     */
    public function generateWeightedHour(): int
    {
        $random = mt_rand(1, 10000) / 10000; // 0.0001 a 1.0000
        $cumulative = 0.0;

        foreach ($this->hourWeights as $hour => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $hour;
            }
        }

        // Fallback para horário de pico se algo der errado
        return 15;
    }

    /**
     * Gera horário considerando se deve ser horário de pico ou não
     */
    public function generateHour(int $dayOfWeek, bool $preferPeakHour = false): int
    {
        $this->validateDayOfWeek($dayOfWeek);
        
        if (!$this->isWorkingDay($dayOfWeek)) {
            throw new InvalidArgumentException("Não é possível gerar horário para dia não útil: {$dayOfWeek}");
        }

        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if ($preferPeakHour) {
            $peakHours = $this->getPeakHoursForDay($dayOfWeek);
            if (!empty($peakHours)) {
                return $peakHours[array_rand($peakHours)];
            }
        }

        // Gerar horário normal dentro do expediente
        return rand($workingHours['start'], $workingHours['end']);
    }

    /**
     * Verifica se um horário está dentro do expediente
     */
    public function isWithinWorkingHours(int $dayOfWeek, int $hour): bool
    {
        if (!$this->isWorkingDay($dayOfWeek)) {
            return false;
        }

        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        return $hour >= $workingHours['start'] && $hour <= $workingHours['end'];
    }

    /**
     * Retorna o próximo horário comercial válido
     */
    public function getNextWorkingHour(int $dayOfWeek, int $currentHour): array
    {
        if (!$this->isWorkingDay($dayOfWeek)) {
            // Se não é dia útil, retorna próxima segunda-feira às 9h
            $nextMonday = $dayOfWeek == 6 ? 2 : 1; // Sábado +2, Domingo +1
            return [
                'day_offset' => $nextMonday,
                'hour' => 9
            ];
        }

        $workingHours = $this->getWorkingHoursForDay($dayOfWeek);
        
        if ($currentHour < $workingHours['start']) {
            return [
                'day_offset' => 0,
                'hour' => $workingHours['start']
            ];
        }

        if ($currentHour > $workingHours['end']) {
            // Próximo dia útil
            $nextDay = $dayOfWeek == 5 ? 3 : 1; // Sexta para segunda = +3, outros = +1
            return [
                'day_offset' => $nextDay,
                'hour' => 9
            ];
        }

        // Está dentro do horário
        return [
            'day_offset' => 0,
            'hour' => $currentHour
        ];
    }

    /**
     * Calcula intervalos humanizados entre posts
     */
    public function generateHumanInterval(int $hour): int
    {
        // Intervalos base em minutos
        $baseInterval = ['min' => 10, 'max' => 20];
        
        // Ajustar baseado no horário
        if ($hour >= 0 && $hour < 6) {
            // Madrugada: posts muito espaçados (não deveria acontecer)
            $baseInterval = ['min' => 90, 'max' => 180];
        } elseif ($hour >= 6 && $hour < 9) {
            // Início da manhã: espaçamento moderado
            $baseInterval = ['min' => 20, 'max' => 40];
        } elseif ($hour >= 9 && $hour < 12) {
            // Pico da manhã: mais frequente
            $baseInterval = ['min' => 8, 'max' => 15];
        } elseif ($hour >= 12 && $hour < 14) {
            // Hora do almoço: menos frequente
            $baseInterval = ['min' => 25, 'max' => 45];
        } elseif ($hour >= 14 && $hour < 18) {
            // Tarde: frequente
            $baseInterval = ['min' => 10, 'max' => 20];
        } elseif ($hour >= 18 && $hour < 22) {
            // Noite: moderado
            $baseInterval = ['min' => 15, 'max' => 30];
        } else {
            // Fim da noite: menos frequente
            $baseInterval = ['min' => 30, 'max' => 60];
        }

        // Variações especiais
        // 5% de chance de um intervalo longo (pausa)
        if (mt_rand(1, 100) <= 5) {
            return mt_rand(60, 120);
        }
        
        // 10% de chance de um cluster (posts em sequência rápida)
        if (mt_rand(1, 100) <= 10) {
            return mt_rand(3, 7);
        }

        // Intervalo padrão com variação natural
        return mt_rand($baseInterval['min'], $baseInterval['max']);
    }

    public function toArray(): array
    {
        return [
            'weekly_schedule' => $this->weeklySchedule,
            'peak_hours' => $this->peakHours,
            'hour_weights' => $this->hourWeights,
        ];
    }

    private function validateDayOfWeek(int $dayOfWeek): void
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new InvalidArgumentException(
                "Dia da semana deve estar entre 1 (segunda) e 7 (domingo). Recebido: {$dayOfWeek}"
            );
        }
    }
}