<?php

namespace Src\GenericArticleGenerator\Infrastructure\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Src\GenericArticleGenerator\Infrastructure\Eloquent\GenerationTempArticle;

/**
 * ClaudeGenerationSchedule
 * 
 * Automação de Geração de Artigos na MADRUGADA
 * 
 * ESTRATÉGIA DE GERAÇÃO:
 * - Standard: 5 execuções na madrugada (01h às 05h)
 * - Intermediate: 5 execuções na madrugada (01h30 às 05h30)
 * - Limite: 1 artigo por execução
 * - Intervalo: 1 hora entre execuções
 * 
 * HORÁRIOS STANDARD:
 * 01:00, 02:00, 03:00, 04:00, 05:00
 * 
 * HORÁRIOS INTERMEDIATE:
 * 01:30, 02:30, 03:30, 04:30, 05:30
 * 
 * BENEFÍCIOS:
 * - Geração em horário de baixo tráfego
 * - Artigos prontos pela manhã
 * - Formato duplicável (fácil adicionar/remover horários)
 * - Logs centralizados
 * 
 * @author Claude Sonnet 4.5
 * @version 3.0 - Madrugada Clean
 */
class ClaudeGenerationSchedule
{
    private const LOG_STANDARD = 'logs/claude-generation-standard.log';
    private const LOG_INTERMEDIATE = 'logs/claude-generation-intermediate.log';

    /**
     * Registrar tarefas agendadas
     */
    public static function register(Schedule $schedule): void
    {
        // ========================================
        // GERAÇÃO STANDARD (Sonnet 4.5)
        // Madrugada: 6 execuções com 1h de intervalo
        // ========================================

        self::scheduleStandard($schedule, '00:00');
        self::scheduleStandard($schedule, '01:00');
        self::scheduleStandard($schedule, '02:00');
        self::scheduleStandard($schedule, '03:00');
        self::scheduleStandard($schedule, '04:00');
        self::scheduleStandard($schedule, '05:00');
        self::scheduleStandard($schedule, '06:00');
        self::scheduleStandard($schedule, '07:00');
        self::scheduleStandard($schedule, '08:00');
        self::scheduleStandard($schedule, '09:00');
        self::scheduleStandard($schedule, '10:00');

        // ========================================
        // GERAÇÃO INTERMEDIATE (Sonnet 4.5)
        // Madrugada: 6 execuções com 1h de intervalo
        // Processar falhas do standard
        // ========================================
        self::scheduleStandard($schedule, '00:30');
        self::scheduleStandard($schedule, '01:30');
        self::scheduleStandard($schedule, '02:30');
        self::scheduleStandard($schedule, '03:30');
        self::scheduleStandard($schedule, '04:30');
        self::scheduleStandard($schedule, '05:30');
        self::scheduleStandard($schedule, '06:30');
        self::scheduleStandard($schedule, '07:30');
        self::scheduleStandard($schedule, '08:30');
        self::scheduleStandard($schedule, '09:30');
        self::scheduleStandard($schedule, '10:30');

        // ========================================
        // MONITORAMENTO
        // ========================================

        // Limpeza de cache - todo dia às 23:30
        $schedule->command('optimize:clear')
            ->dailyAt('23:30')
            ->timezone('America/Sao_Paulo');
    }

    /**
     * Agendar geração standard
     * 
     * Para adicionar mais horários, basta duplicar esta linha:
     * self::scheduleStandard($schedule, '06:00'); // Adiciona execução às 06h
     */
    private static function scheduleStandard(Schedule $schedule, string $time): void
    {
        $schedule->command('temp-article:generate-standard --limit=1 --category=duvidas-oleo-motor --priority=high --delay=3')
            ->dailyAt($time)
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path(self::LOG_STANDARD))
            ->before(fn() => self::logStandardBefore($time))
            ->onSuccess(fn() => self::logStandardSuccess($time))
            ->onFailure(fn() => self::logStandardFailure($time));
    }

    /**
     * Agendar geração intermediate
     * 
     * Para adicionar mais horários, basta duplicar esta linha:
     * self::scheduleIntermediate($schedule, '06:30'); // Adiciona execução às 06h30
     */
    private static function scheduleIntermediate(Schedule $schedule, string $time): void
    {
        // $schedule->command('temp-article:generate-intermediate --only-failed-standard --limit=1 --category=duvidas-oleo-motor --priority=high --delay=5')
        //     ->dailyAt($time)
        //     ->timezone('America/Sao_Paulo')
        //     ->withoutOverlapping(15)
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path(self::LOG_INTERMEDIATE))
        //     ->before(fn() => self::logIntermediateBefore($time))
        //     ->onSuccess(fn() => self::logIntermediateSuccess($time))
        //     ->onFailure(fn() => self::logIntermediateFailure($time));


        $schedule->command('temp-article:generate-standard --only-failed-standard --limit=1 --category=duvidas-oleo-motor --priority=high --delay=5')
            ->dailyAt($time)
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(15)
            ->runInBackground()
            ->appendOutputTo(storage_path(self::LOG_INTERMEDIATE))
            ->before(fn() => self::logIntermediateBefore($time))
            ->onSuccess(fn() => self::logIntermediateSuccess($time))
            ->onFailure(fn() => self::logIntermediateFailure($time));
    }

    /**
     * Logs - Standard
     */
    private static function logStandardBefore(string $time): void
    {
        Log::info("ClaudeGeneration: Iniciando geração STANDARD ({$time})", [
            'model' => 'Claude 3.7 Sonnet',
            'pending' => self::getPendingCount(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private static function logStandardSuccess(string $time): void
    {
        Log::info("ClaudeGeneration: Geração STANDARD concluída ({$time})", [
            'pending' => self::getPendingCount(),
            'generated_today' => self::getGeneratedTodayCount('standard')
        ]);
    }

    private static function logStandardFailure(string $time): void
    {
        Log::error("ClaudeGeneration: Falha na geração STANDARD ({$time})");
    }

    /**
     * Logs - Intermediate
     */
    private static function logIntermediateBefore(string $time): void
    {
        $failedStandard = self::getFailedStandardCount();

        Log::info("ClaudeGeneration: Iniciando geração INTERMEDIATE ({$time})", [
            'model' => 'Claude Sonnet 4.0',
            'failed_standard' => $failedStandard,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        if ($failedStandard === 0) {
            Log::info("ClaudeGeneration: Nenhuma falha standard para processar");
        }
    }

    private static function logIntermediateSuccess(string $time): void
    {
        Log::info("ClaudeGeneration: Geração INTERMEDIATE concluída ({$time})", [
            'failed_standard_remaining' => self::getFailedStandardCount(),
            'generated_today' => self::getGeneratedTodayCount('intermediate')
        ]);
    }

    private static function logIntermediateFailure(string $time): void
    {
        Log::error("ClaudeGeneration: Falha na geração INTERMEDIATE ({$time})");
    }

    /**
     * Helpers
     */
    private static function getPendingCount(): int
    {
        try {
            return GenerationTempArticle::pending()->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private static function getGeneratedTodayCount(?string $model = null): int
    {
        try {
            $query = GenerationTempArticle::whereDate('generated_at', today())
                ->whereIn('generation_status', ['generated', 'validated', 'published']);

            if ($model) {
                $query->where('generation_model_used', $model);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private static function getFailedStandardCount(): int
    {
        try {
            return GenerationTempArticle::where('generation_status', 'failed')
                ->where('generation_model_used', 'standard')
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
