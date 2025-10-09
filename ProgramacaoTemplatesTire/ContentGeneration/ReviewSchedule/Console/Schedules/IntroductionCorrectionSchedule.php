<?php

namespace Src\ContentGeneration\ReviewSchedule\Console\Schedules;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ContentGeneration\ReviewSchedule\Infrastructure\Services\ArticleIntroductionCorrectionService;

class IntroductionCorrectionSchedule
{
    /**
     * 🕐 Schedules simplificados para horário comercial brasileiro
     * TESTE: A cada 10 minutos
     * PRODUÇÃO: De hora em hora das 8h às 20h
     * Segunda a Sexta + Sábado até meio-dia
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
        static::registerAnalytics($schedule);
        static::registerMaintenance($schedule);
    }

    /**
     * 🆕 Criação de correções - TESTE: a cada 10 minutos
     */
    private static function registerCreation(Schedule $schedule): void
    {
        // TESTE: A cada 10 minutos das 8h às 20h (horário de São Paulo)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Só cria se há menos de 30 pendentes
            if ($pendingCount < 30) {
                Artisan::call('articles:fix-introduction', [
                    '--all' => true,
                    '--limit' => 5000,
                    '--force' => true
                ]);
            }
        })
            ->cron('*/10 8-21 * * 1-5') // Segunda a Sexta, 8h-21h, a cada 10min
            ->name('intro-creation-weekdays')
            ->withoutOverlapping(30);

        // Sábados até meio-dia (12h)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($pendingCount < 30) {
                Artisan::call('articles:fix-introduction', [
                    '--all' => true,
                    '--limit' => 5000,
                    '--force' => true
                ]);
            }
        })
            ->cron('*/10 8-12 * * 6') // Sábado, 8h-12h, a cada 10min
            ->name('intro-creation-saturday')
            ->withoutOverlapping(30);
    }

    /**
     * ⚡ Processamento via Claude API - TESTE: a cada 10 minutos
     */
    private static function registerProcessing(Schedule $schedule): void
    {
        // Processamento principal: Segunda a Sexta
        $schedule->command('articles:fix-introduction --process --limit=1 --force')
            ->cron('*/10 8-21 * * 1-5') // A cada 10 minutos, 8h-21h, Seg-Sex
            ->name('intro-processing-weekdays')
            ->withoutOverlapping(3)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/intro-processing.log'));

        // Processamento sábado até meio-dia
        $schedule->command('articles:fix-introduction --process --limit=1 --force')
            ->cron('*/10 8-12 * * 6') // A cada 10 minutos, 8h-12h, Sábado
            ->name('intro-processing-saturday')
            ->withoutOverlapping(3)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/intro-processing.log'));
    }

    /**
     * 📊 Monitoramento em horário comercial
     */
    private static function registerMonitoring(Schedule $schedule): void
    {
        // Stats a cada hora no horário comercial
        $schedule->command('articles:fix-introduction --stats')
            ->cron('0 8-21 * * 1-5') // De hora em hora, 8h-21h, Seg-Sex
            ->appendOutputTo(storage_path('logs/intro-stats.log'))
            ->name('intro-monitoring-weekdays');

        // Stats sábado até meio-dia
        $schedule->command('articles:fix-introduction --stats')
            ->cron('0 8-12 * * 6') // De hora em hora, 8h-12h, Sábado
            ->appendOutputTo(storage_path('logs/intro-stats.log'))
            ->name('intro-monitoring-saturday');

        // Alerta no final do expediente (20h)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($pendingCount > 100) {
                Log::warning("Muitas correções de conteúdo pendentes: {$pendingCount}");
            }
        })
            ->cron('0 20 * * 1-5') // 20h, Segunda a Sexta
            ->name('intro-alerts-end-of-day');
    }

    /**
     * 📈 Schedule para análise de performance - final do dia
     */
    private static function registerAnalytics(Schedule $schedule): void
    {
        // Relatório diário - final do expediente (19h)
        $schedule->call(function () {
            $service = app(ArticleIntroductionCorrectionService::class);
            $stats = $service->getStats();

            Log::info('📊 Relatório diário de correções de conteúdo', [
                'date' => now()->format('Y-m-d'),
                'stats' => $stats,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        })
            ->cron('0 19 * * 1-5') // 19h, Segunda a Sexta
            ->name('intro-daily-report-end-of-day');
    }

    /**
     * 🧹 Manutenção fora do horário comercial
     */
    private static function registerMaintenance(Schedule $schedule): void
    {
        // Limpeza de correções falhadas - após expediente (21h)
        $schedule->call(function () {
            $deletedCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Limpeza de correções de conteúdo falhadas: {$deletedCount} registros removidos");
            }
        })
            ->cron('0 21 * * 1-6') // 21h, Segunda a Sábado
            ->name('intro-failed-cleanup-after-hours')
            ->withoutOverlapping(10);

        // Limpeza semanal - madrugada de domingo (6h)
        $schedule->call(function () {
            // Remove concluídas com mais de 18 meses
            $completedDeleted = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_CONTENT_ENHANCEMENT)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('created_at', '<', now()->subMonths(18))
                ->delete();

            Log::info("Limpeza semanal de correções de conteúdo: {$completedDeleted} registros removidos");
        })
            ->cron('0 6 * * 0') // 6h domingo
            ->name('intro-weekly-cleanup-sunday');

        // Rotação de logs - madrugada (5h)
        $schedule->call(function () {
            static::rotateLogs('intro-processing.log');
            static::rotateLogs('intro-stats.log');
        })
            ->cron('0 5 * * *') // 5h todos os dias
            ->name('intro-log-rotation-daily');
    }

    /**
     * 🗂️ Rotaciona logs quando ficam grandes
     */
    private static function rotateLogs(string $logName): void
    {
        $logFile = storage_path("logs/{$logName}");

        if (file_exists($logFile) && filesize($logFile) > 15 * 1024 * 1024) { // 15MB
            $backup = $logFile . '.' . date('Y-m-d-His');
            rename($logFile, $backup);

            // Remove backups antigos (mais de 21 dias)
            foreach (glob($logFile . '.*') as $oldLog) {
                if (filemtime($oldLog) < strtotime('-21 days')) {
                    unlink($oldLog);
                }
            }
        }
    }

    /**
     * 📋 Método para verificar saúde dos schedules
     */
    public static function getScheduleHealth(): array
    {
        return [
            'creation_schedules' => 2, // Seg-Sex + Sábado
            'processing_schedules' => 2, // Seg-Sex + Sábado  
            'monitoring_schedules' => 3, // Stats + Alertas
            'analytics_schedules' => 1, // Relatório diário
            'maintenance_schedules' => 3, // Limpeza + Rotação
            'total_schedules' => 11,
            'test_frequency' => 'A cada 10 minutos',
            'production_frequency' => 'De hora em hora',
            'business_hours' => '8h-21h (Seg-Sex) + 8h-12h (Sáb)',
            'timezone' => 'America/Sao_Paulo',
            'weekends' => 'Domingo: Não executa / Sábado: Até 12h',
            'overlapping_protection' => true
        ];
    }
}
