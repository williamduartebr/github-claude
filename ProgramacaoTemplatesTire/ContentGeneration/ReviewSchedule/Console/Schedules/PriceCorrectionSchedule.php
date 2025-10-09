<?php

namespace Src\ContentGeneration\ReviewSchedule\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;

class PriceCorrectionSchedule
{
    /**
     * Registra schedules simplificados de correção de preços
     */
    public static function register(Schedule $schedule): void
    {
        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return;
        }

        static::registerCreation($schedule);
        static::registerProcessing($schedule);
        static::registerMonitoring($schedule);
        static::registerMaintenance($schedule);
        static::registerFailedCleanup($schedule);
    }

    /**
     * Criação de correções - executa quando necessário
     */
    private static function registerCreation(Schedule $schedule): void
    {
        // Cria correções para todos os artigos a cada 30 minutos
        // Só executa se não houver muitas pendentes
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', 'price_correction')
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Só cria novas se há menos de 50 pendentes
            if ($pendingCount < 50) {
                \Artisan::call('review-schedule:fix-prices', [
                    '--all' => true,
                    '--limit' => 2000,
                    '--force' => true
                ]);
            }
        })
            ->everyThirtyMinutes()
            ->name('price-creation')
            ->withoutOverlapping(15);
    }

    /**
     * Processamento via Claude API - 1 por minuto
     */
    private static function registerProcessing(Schedule $schedule): void
    {
        // Processa 1 correção por minuto via Claude API
        // 1440 correções por dia - suficiente para manter em dia
        $schedule->command('review-schedule:fix-prices --process --limit=1')
            ->everyMinute()
            ->withoutOverlapping(2)  // Timeout curto
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/price-processing.log'))
            ->name('price-processing');
    }

    /**
     * Monitoramento simples
     */
    private static function registerMonitoring(Schedule $schedule): void
    {
        // Estatísticas de hora em hora
        $schedule->command('review-schedule:fix-prices --stats')
            ->hourly()
            ->appendOutputTo(storage_path('logs/price-stats.log'))
            ->name('price-monitoring');

        // Alerta se há muitas pendentes
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', 'price_correction')
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($pendingCount > 200) {
                \Log::warning("Muitas correções pendentes: {$pendingCount}");
            }
        })
            ->everyTwoHours()
            ->name('price-alerts');
    }

    /**
     * Limpeza de registros falhados - a cada 10 minutos
     */
    private static function registerFailedCleanup(Schedule $schedule): void
    {
        $schedule->call(function () {
            $deletedCount = ArticleCorrection::where('correction_type', 'price_correction')
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->delete();

            if ($deletedCount > 0) {
                \Log::info("Limpeza de correções falhadas: {$deletedCount} registros removidos");
            }
        })
            ->everyTenMinutes()
            ->name('price-failed-cleanup')
            ->withoutOverlapping(5);
    }


    /**
     * Limpeza semanal
     */
    private static function registerMaintenance(Schedule $schedule): void
    {
        // Limpa correções antigas aos domingos
        $schedule->call(function () {
            // Remove concluídas com mais de 180 dias
            $completedDeleted = ArticleCorrection::where('correction_type', 'price_correction')
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('created_at', '<', now()->subMonths(6))
                ->delete();

            \Log::info("Limpeza mensal de correções concluída: {$completedDeleted} registros removidos");
        })
            ->monthly()
            ->sundays()
            ->at('02:00')
            ->name('price-cleanup');

        // Rotaciona logs diariamente
        $schedule->call(function () {
            $logFile = storage_path('logs/price-processing.log');

            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
                $backup = $logFile . '.' . date('Y-m-d');
                rename($logFile, $backup);

                // Remove backups antigos
                foreach (glob($logFile . '.*') as $oldLog) {
                    if (filemtime($oldLog) < strtotime('-14 days')) {
                        unlink($oldLog);
                    }
                }
            }
        })
            ->daily()
            ->at('01:30')
            ->name('price-log-cleanup');
    }

    /**
     * Schedule especial para acelerar processamento quando necessário
     */
    public static function registerBoostMode(Schedule $schedule): void
    {
        // Modo acelerado - usar temporariamente quando há muito backlog
        // Processa 10 por vez, a cada 10 minutos
        $schedule->command('review-schedule:fix-prices --process --limit=10')
            ->everyTenMinutes()
            ->withoutOverlapping(8)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/price-boost.log'))
            ->name('price-boost-mode');
    }
}
