<?php

namespace Src\ContentGeneration\TireSchedule\Console\Schedules;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Src\ArticleGenerator\Infrastructure\Eloquent\ArticleCorrection;
use Src\ContentGeneration\TireSchedule\Infrastructure\Services\TireCorrectionService;

class TireCorrectionSchedule
{
    /**
     * 🚗 Schedules para correção de artigos sobre pneus
     * TESTE: A cada 15 minutos
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
     * 🆕 Criação de correções - TESTE: a cada 15 minutos
     */
    private static function registerCreation(Schedule $schedule): void
    {
        // TESTE: A cada 15 minutos das 8h às 20h (horário de São Paulo)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Só cria se há menos de 25 pendentes
            if ($pendingCount < 25) {
                Artisan::call('tire-pressure-corrections', [
                    '--all' => true,
                    '--limit' => 3000,
                    '--force' => true
                ]);
            }
        })
            ->cron('*/15 8-21 * * 1-5') // Segunda a Sexta, 8h-21h, a cada 15min
            ->name('tire-creation-weekdays')
            ->withoutOverlapping(30);

        // Sábados até meio-dia (12h)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            if ($pendingCount < 25) {
                Artisan::call('tire-pressure-corrections', [
                    '--all' => true,
                    '--limit' => 3000,
                    '--force' => true
                ]);
            }
        })
            ->cron('*/15 8-12 * * 6') // Sábado, 8h-12h, a cada 15min
            ->name('tire-creation-saturday')
            ->withoutOverlapping(30);
    }

    /**
     * ⚡ Processamento via Claude API - TESTE: a cada 15 minutos
     */
    private static function registerProcessing(Schedule $schedule): void
    {
        // Processamento 24h - limite de 1 por vez para não sobrecarregar
        $schedule->command('tire-pressure-corrections --process --limit=1 --force')
            ->cron('*/3 * * * *') // A cada 3 minutos, 24 horas
            ->name('tire-processing-24h')
            ->withoutOverlapping(10)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-processing.log'));
    }

    /**
     * 📊 Monitoramento em horário comercial
     */
    private static function registerMonitoring(Schedule $schedule): void
    {
        // Stats a cada 2 horas no horário comercial
        $schedule->command('tire-pressure-corrections --stats')
            ->cron('0 8-21/2 * * 1-5') // A cada 2 horas, 8h-21h, Seg-Sex
            ->appendOutputTo(storage_path('logs/tire-stats.log'))
            ->name('tire-monitoring-weekdays');

        // Stats sábado a cada 2 horas até meio-dia
        $schedule->command('tire-pressure-corrections --stats')
            ->cron('0 8-12/2 * * 6') // A cada 2 horas, 8h-12h, Sábado
            ->appendOutputTo(storage_path('logs/tire-stats.log'))
            ->name('tire-monitoring-saturday');

        // Alerta no final do expediente (20h)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            $processingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->count();

            if ($pendingCount > 50) {
                Log::warning("🚗 Muitas correções de pneus pendentes: {$pendingCount}");
            }

            if ($processingCount > 10) {
                Log::warning("🚗 Muitas correções de pneus em processamento: {$processingCount} (possível travamento)");
            }
        })
            ->cron('0 20 * * 1-5') // 20h, Segunda a Sexta
            ->name('tire-alerts-end-of-day');
    }

    /**
     * 📈 Schedule para análise de performance - final do dia
     */
    private static function registerAnalytics(Schedule $schedule): void
    {
        // Relatório diário - final do expediente (19h)
        $schedule->call(function () {
            $service = app(TireCorrectionService::class);
            $stats = $service->getStats();

            // Estatísticas adicionais específicas para pneus
            $totalTireArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
                ->where('status', 'draft')
                ->count();

            $correctionRate = $totalTireArticles > 0 ? round(($stats['total'] / $totalTireArticles) * 100, 2) : 0;
            $successRate = ($stats['completed'] + $stats['failed']) > 0 ?
                round(($stats['completed'] / ($stats['completed'] + $stats['failed'])) * 100, 2) : 0;

            Log::info('🚗 Relatório diário de correções de pneus', [
                'date' => now()->format('Y-m-d'),
                'stats' => $stats,
                'total_tire_articles' => $totalTireArticles,
                'correction_rate' => $correctionRate . '%',
                'success_rate' => $successRate . '%',
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        })
            ->cron('0 19 * * 1-5') // 19h, Segunda a Sexta
            ->name('tire-daily-report-end-of-day');

        // Relatório semanal - sexta às 18h
        $schedule->call(function () {
            $service = app(TireCorrectionService::class);

            // Estatísticas da semana
            $weeklyStats = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            Log::info('🚗 Relatório semanal de correções de pneus', [
                'week_start' => now()->startOfWeek()->format('Y-m-d'),
                'week_end' => now()->endOfWeek()->format('Y-m-d'),
                'weekly_stats' => $weeklyStats,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);
        })
            ->cron('0 18 * * 5') // 18h, Sexta-feira
            ->name('tire-weekly-report-friday');
    }

    /**
     * 🧹 Manutenção fora do horário comercial
     */
    private static function registerMaintenance(Schedule $schedule): void
    {
        // Limpeza de correções falhadas - após expediente (21h)
        $schedule->call(function () {
            $deletedCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(24)) // Só remove falhas antigas
                ->delete();

            if ($deletedCount > 0) {
                Log::info("🚗 Limpeza de correções de pneus falhadas: {$deletedCount} registros removidos");
            }
        })
            ->cron('0 21 * * 1-6') // 21h, Segunda a Sábado
            ->name('tire-failed-cleanup-after-hours')
            ->withoutOverlapping(10);

        // Limpeza de duplicatas - madrugada de terça (4h)
        $schedule->call(function () {
            $service = app(TireCorrectionService::class);
            $results = $service->cleanAllDuplicates();

            if ($results['corrections_removed'] > 0) {
                Log::info("🚗 Limpeza de duplicatas de pneus", $results);
            }
        })
            ->cron('0 4 * * 2') // 4h terça-feira
            ->name('tire-duplicates-cleanup-tuesday');

        // Reset de correções travadas em processamento - madrugada (3h)
        $schedule->call(function () {
            $resetCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6)) // Travadas há mais de 6 horas
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            if ($resetCount > 0) {
                Log::info("🚗 Reset de correções de pneus travadas: {$resetCount} registros");
            }
        })
            ->cron('0 3 * * *') // 3h todos os dias
            ->name('tire-reset-stuck-processing-daily');

        // Limpeza semanal - madrugada de domingo (6h)
        $schedule->call(function () {
            // Remove concluídas com mais de 24 meses (pneus são mais estáveis)
            $completedDeleted = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_COMPLETED)
                ->where('created_at', '<', now()->subMonths(24))
                ->delete();

            Log::info("🚗 Limpeza semanal de correções de pneus: {$completedDeleted} registros removidos");
        })
            ->cron('0 6 * * 0') // 6h domingo
            ->name('tire-weekly-cleanup-sunday');

        // Rotação de logs - madrugada (5h)
        $schedule->call(function () {
            static::rotateLogs('tire-processing.log');
            static::rotateLogs('tire-stats.log');
        })
            ->cron('0 5 * * *') // 5h todos os dias
            ->name('tire-log-rotation-daily');
    }

    /**
     * 🗂️ Rotaciona logs quando ficam grandes
     */
    private static function rotateLogs(string $logName): void
    {
        $logFile = storage_path("logs/{$logName}");

        if (file_exists($logFile) && filesize($logFile) > 20 * 1024 * 1024) { // 20MB
            $backup = $logFile . '.' . date('Y-m-d-His');
            rename($logFile, $backup);

            // Remove backups antigos (mais de 30 dias)
            foreach (glob($logFile . '.*') as $oldLog) {
                if (filemtime($oldLog) < strtotime('-30 days')) {
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
            'analytics_schedules' => 2, // Relatório diário + semanal
            'maintenance_schedules' => 5, // Limpeza + Reset + Rotação
            'total_schedules' => 14,
            'test_frequency' => 'A cada 15 minutos',
            'production_frequency' => 'De hora em hora',
            'business_hours' => '8h-21h (Seg-Sex) + 8h-12h (Sáb)',
            'timezone' => 'America/Sao_Paulo',
            'weekends' => 'Domingo: Apenas manutenção / Sábado: Até 12h',
            'overlapping_protection' => true,
            'domain_focus' => 'when_to_change_tires',
            'correction_type' => 'TYPE_TIRE_PRESSURE_FIX'
        ];
    }

    /**
     * 🔧 Método para diagnosticar problemas comuns
     */
    public static function diagnoseIssues(): array
    {
        $issues = [];

        // Verificar correções travadas
        $stuckProcessing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PROCESSING)
            ->where('updated_at', '<', now()->subHours(2))
            ->count();

        if ($stuckProcessing > 0) {
            $issues[] = "🚫 {$stuckProcessing} correções travadas em processamento";
        }

        // Verificar muitas falhas
        $recentFailed = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_FAILED)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentFailed > 20) {
            $issues[] = "⚠️ Muitas falhas recentes: {$recentFailed} nas últimas 24h";
        }

        // Verificar backlog
        $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->count();

        if ($pendingCount > 100) {
            $issues[] = "📈 Backlog alto: {$pendingCount} correções pendentes";
        }

        return $issues;
    }
}
