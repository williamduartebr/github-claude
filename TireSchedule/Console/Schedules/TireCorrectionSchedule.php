<?php

namespace Src\ContentGeneration\TireSchedule\Console\Schedules;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Src\ContentGeneration\TireSchedule\Infrastructure\Eloquent\TireArticleCorrection as ArticleCorrection;

class TireCorrectionSchedule
{
    /**
     * 🚗 Schedules para correção de artigos sobre pneus
     * Sistema simplificado 24h para TempArticles
     * Processamento contínuo de correções de pneus E título/ano
     */
    public static function register(Schedule $schedule): void
    {
        // ========================================
        // 🚗 CORREÇÕES DE PRESSÃO DE PNEUS
        // ========================================
        
        // Criar correções de pneus a cada 30 minutos
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Só cria se há menos de 30 pendentes
            if ($pendingCount < 30) {
                Artisan::call('tire-pressure-corrections', [
                    '--all' => true,
                    '--limit' => 1000,
                    '--force' => true
                ]);
                
                Log::info("🚗 Correções de pneus criadas: pendentes antes = {$pendingCount}");
            }
        })
            ->everyThirtyMinutes()
            ->name('tire-pressure-creation-24h')
            ->withoutOverlapping(20);

        // Processar correções de pneus a cada 3 minutos
        $schedule->command('tire-pressure-corrections --process --limit=1 --force')
            ->everyThreeMinutes()
            ->name('tire-pressure-processing-24h')
            ->withoutOverlapping(2)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-processing.log'));

        // ========================================
        // 📝 CORREÇÕES DE TÍTULO/ANO
        // ========================================
        
        // Criar correções de título/ano a cada 45 minutos (offset para não conflitar)
        $schedule->call(function () {
            $pendingCount = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PENDING)
                ->count();

            // Só cria se há menos de 40 pendentes
            if ($pendingCount < 40) {
                Artisan::call('tire-title-year-corrections', [
                    '--all' => true,
                    '--limit' => 800,
                    '--force' => true
                ]);
                
                Log::info("📝 Correções de título/ano criadas: pendentes antes = {$pendingCount}");
            }
        })
            ->cron('15,45 * * * *') // A cada 30min com offset de 15min
            ->name('title-year-creation-24h')
            ->withoutOverlapping(20);

        // Processar correções de título/ano a cada 4 minutos (offset para não conflitar)
        $schedule->command('tire-title-year-corrections --process --limit=1 --force')
            ->cron('1,5,9,13,17,21,25,29,33,37,41,45,49,53,57 * * * *') // A cada 4min
            ->name('title-year-processing-24h')
            ->withoutOverlapping(2)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/title-year-processing.log'));

        // ========================================
        // 📊 MONITORAMENTO E ESTATÍSTICAS
        // ========================================

        // Stats consolidadas a cada 2 horas
        $schedule->call(function () {
            Artisan::call('tire-pressure-corrections', ['--stats' => true]);
            Artisan::call('tire-title-year-corrections', ['--stats' => true]);
            
            // Log consolidado
            $tireStats = ArticleCorrection::getTireStats();
            $titleYearStats = ArticleCorrection::getTitleYearStats();
            
            Log::info('📊 Stats consolidadas', [
                'tire' => $tireStats,
                'title_year' => $titleYearStats,
                'total_pending' => $tireStats['pending'] + $titleYearStats['pending'],
                'total_processing' => $tireStats['processing'] + $titleYearStats['processing']
            ]);
        })
            ->everyTwoHours()
            ->name('tire-stats-consolidated');

        // ========================================
        // 🧹 MANUTENÇÃO AUTOMÁTICA
        // ========================================

        // Reset de correções travadas - a cada 6 horas
        $schedule->call(function () {
            $tireReset = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(4)) // Travadas há mais de 4 horas
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            $titleYearReset = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(4))
                ->update([
                    'status' => ArticleCorrection::STATUS_PENDING,
                    'updated_at' => now()
                ]);

            if ($tireReset > 0 || $titleYearReset > 0) {
                Log::info("🔄 Reset de correções travadas", [
                    'tire_reset' => $tireReset,
                    'title_year_reset' => $titleYearReset,
                    'total_reset' => $tireReset + $titleYearReset
                ]);
            }
        })
            ->everySixHours()
            ->name('tire-reset-stuck-processing')
            ->withoutOverlapping(10);

        // Limpeza de falhas antigas - diário às 3h
        $schedule->call(function () {
            $tireFailures = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(48))
                ->delete();

            $titleYearFailures = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '<', now()->subHours(48))
                ->delete();

            if ($tireFailures > 0 || $titleYearFailures > 0) {
                Log::info("🧹 Limpeza de falhas antigas", [
                    'tire_failures_removed' => $tireFailures,
                    'title_year_failures_removed' => $titleYearFailures,
                    'total_removed' => $tireFailures + $titleYearFailures
                ]);
            }
        })
            ->dailyAt('03:00')
            ->name('tire-cleanup-old-failures')
            ->withoutOverlapping(30);

        // Limpeza de duplicatas - semanal aos domingos às 4h
        $schedule->call(function () {
            // Limpeza de pneus
            Artisan::call('tire-pressure-corrections', [
                '--clean-duplicates' => true,
                '--force' => true
            ]);

            // Limpeza de título/ano
            Artisan::call('tire-title-year-corrections', [
                '--clean-duplicates' => true,
                '--force' => true
            ]);

            Log::info("🧹 Limpeza semanal de duplicatas executada");
        })
            ->weeklyOn(0, '04:00') // Domingo às 4h
            ->name('tire-weekly-duplicates-cleanup')
            ->withoutOverlapping(60);

        // ========================================
        // 🚨 ALERTAS E MONITORAMENTO
        // ========================================

        // Verificação de saúde do sistema - a cada hora
        $schedule->call(function () {
            $alerts = [];

            // Verificar correções travadas há muito tempo
            $stuckTire = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6))
                ->count();

            $stuckTitleYear = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_PROCESSING)
                ->where('updated_at', '<', now()->subHours(6))
                ->count();

            if ($stuckTire > 0) {
                $alerts[] = "🚨 {$stuckTire} correções de pneus travadas há mais de 6 horas";
            }

            if ($stuckTitleYear > 0) {
                $alerts[] = "🚨 {$stuckTitleYear} correções de título/ano travadas há mais de 6 horas";
            }

            // Verificar alta taxa de falhas
            $tireFailures = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '>', now()->subHours(6))
                ->count();

            $titleYearFailures = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
                ->where('status', ArticleCorrection::STATUS_FAILED)
                ->where('created_at', '>', now()->subHours(6))
                ->count();

            if ($tireFailures > 15) {
                $alerts[] = "⚠️ Alta taxa de falhas de pneus: {$tireFailures} nas últimas 6 horas";
            }

            if ($titleYearFailures > 20) {
                $alerts[] = "⚠️ Alta taxa de falhas de título/ano: {$titleYearFailures} nas últimas 6 horas";
            }

            // Log apenas se houver alertas
            if (!empty($alerts)) {
                Log::warning('🚨 Alertas do sistema de correções', [
                    'alerts' => $alerts,
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
            }
        })
            ->hourly()
            ->name('tire-health-check');
    }

    /**
     * 📋 Método para verificar saúde dos schedules
     */
    public static function getScheduleHealth(): array
    {
        return [
            'schedule_name' => 'TireCorrectionSchedule',
            'version' => '2.1_simplified_24h',
            'creation_schedules' => [
                'tire_pressure' => 'A cada 30 minutos',
                'title_year' => 'A cada 30 minutos (offset +15min)'
            ],
            'processing_schedules' => [
                'tire_pressure' => 'A cada 3 minutos',
                'title_year' => 'A cada 4 minutos (offset +1min)'
            ],
            'maintenance_schedules' => [
                'reset_stuck' => 'A cada 6 horas',
                'cleanup_failures' => 'Diário às 3h',
                'cleanup_duplicates' => 'Semanal domingo às 4h'
            ],
            'monitoring_schedules' => [
                'stats' => 'A cada 2 horas',
                'health_check' => 'A cada hora'
            ],
            'total_schedules' => 8,
            'runtime' => '24 horas por dia',
            'domain_focus' => 'when_to_change_tires (TempArticles)',
            'correction_types' => [
                'TYPE_TIRE_PRESSURE_FIX',
                'TYPE_TITLE_YEAR_FIX'
            ]
        ];
    }

    /**
     * 🔧 Método para diagnosticar problemas comuns
     */
    public static function diagnoseIssues(): array
    {
        $issues = [];

        // Verificar se há TempArticles disponíveis
        $availableArticles = \Src\ArticleGenerator\Infrastructure\Eloquent\TempArticle::where('domain', 'when_to_change_tires')
            ->where('status', 'draft')
            ->count();

        if ($availableArticles === 0) {
            $issues[] = "⚠️ Nenhum TempArticle disponível para correção (domain: when_to_change_tires)";
        }

        // Verificar backlog excessivo
        $tirePending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->count();

        $titleYearPending = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_PENDING)
            ->count();

        if ($tirePending > 100) {
            $issues[] = "📈 Backlog alto de correções de pneus: {$tirePending}";
        }

        if ($titleYearPending > 150) {
            $issues[] = "📈 Backlog alto de correções de título/ano: {$titleYearPending}";
        }

        // Verificar se há processamento recente
        $recentTireProcessing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TIRE_PRESSURE_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('processed_at', '>', now()->subHours(2))
            ->exists();

        $recentTitleYearProcessing = ArticleCorrection::where('correction_type', ArticleCorrection::TYPE_TITLE_YEAR_FIX)
            ->where('status', ArticleCorrection::STATUS_COMPLETED)
            ->where('processed_at', '>', now()->subHours(2))
            ->exists();

        if (!$recentTireProcessing && $tirePending > 0) {
            $issues[] = "🚫 Nenhum processamento de pneus nas últimas 2 horas";
        }

        if (!$recentTitleYearProcessing && $titleYearPending > 0) {
            $issues[] = "🚫 Nenhum processamento de título/ano nas últimas 2 horas";
        }

        return $issues;
    }
}