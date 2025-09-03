<?php

namespace Src\ContentGeneration\TireCalibration\Infrastructure\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Src\ContentGeneration\TireCalibration\Domain\Entities\TireCalibration;

/**
 * TireCalibrationV4Schedule - Schedule Dual-Phase OTIMIZADO
 * 
 * Versão final: Apenas as fases específicas + utilitários
 * Remove comando unificado redundante
 */
class TireCalibrationV4Schedule
{
    public static function register($schedule): void
    {
        // ========================================
        // FASE 3A: REFINAMENTO EDITORIAL
        // ========================================
        $schedule->command('tire-calibration:refine-3a --limit=1 --delay=3')
            ->cron('*/3 * * * *')  // Cada 3 minutos
            // ->withoutOverlapping(6)
            // ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-calibration-3a.log'))
            ->onSuccess(function () {
                Log::info('TireCalibration V4: Fase 3A automática concluída');
            })
            ->onFailure(function () {
                Log::error('TireCalibration V4: Falha na Fase 3A automática');
            });

        // ========================================  
        // FASE 3B: REFINAMENTO TÉCNICO
        // ========================================
        $schedule->command('tire-calibration:refine-3b --limit=1 --delay=5')
            ->cron('*/4 * * * *') // Cada 4 minutos
            // ->withoutOverlapping(10)
            // ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-calibration-3b.log'))
            ->onSuccess(function () {
                Log::info('TireCalibration V4: Fase 3B automática concluída');
            })
            ->onFailure(function () {
                Log::error('TireCalibration V4: Falha na Fase 3B automática');
            });


        // ========================================
        // FASE 3C: REPROCESSAR FALHAS
        // ========================================
        $schedule->command('tire-calibration:reprocess-failed --limit=2')
            ->cron('*/3 * * * *')  // Cada 3 minutos
            // ->withoutOverlapping(6)
            // ->runInBackground()
            ->appendOutputTo(storage_path('logs/reprocess-failed.log'))
            ->onSuccess(function () {
                Log::info('TireCalibration V4: Reprocessar falha automática concluída');
            })
            ->onFailure(function () {
                Log::error('TireCalibration V4: Falha na Reprocessar falha automática');
            });

        // ========================================
        // GERAÇÃO DE ARTIGOS BASE (FASE 2) - DIÁRIO
        // ========================================
        // $schedule->command('tire-calibration:generate-articles-phase2 --limit=100')
        //     ->dailyAt('02:30')
        //     ->withoutOverlapping(60)
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/tire-calibration-generation.log'))
        //     ->onSuccess(function () {
        //         Log::info('TireCalibration V4: Geração automática de artigos concluída');
        //     });

        // ========================================
        // UTILITÁRIOS
        // ========================================

        // Cleanup registros travados - a cada hora
        $schedule->command('tire-calibration:refine-3b --cleanup')
            ->hourly()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tire-calibration-cleanup.log'));

        // Estatísticas diárias
        $schedule->command('tire-calibration:stats --detailed')
            ->dailyAt('06:00')
            ->appendOutputTo(storage_path('logs/tire-calibration-stats.log'));

        // Health check a cada 30 minutos
        $schedule->call(function () {
            self::performHealthCheck();
        })
            ->everyThirtyMinutes()
            ->name('tire-calibration-health-check');
    }

    /**
     * Health check otimizado
     */
    private static function performHealthCheck(): void
    {
        try {
            $stats = TireCalibration::getProcessingStats();

            // Alertas inteligentes
            $stuckProcessing = $stats['processing_3a'] + $stats['processing_3b'];
            if ($stuckProcessing > 15) {
                Log::warning('TireCalibration V4: Muitos registros travados', [
                    'stuck_count' => $stuckProcessing,
                    'recommendation' => 'Execute: php artisan tire-calibration:refine-3b --cleanup'
                ]);
            }

            // Log de status normal (mais conciso)
            if ($stuckProcessing <= 5) {
                Log::info('TireCalibration V4: Sistema funcionando normalmente', [
                    'ready_3a' => $stats['ready_for_3a'],
                    'ready_3b' => $stats['ready_for_3b'],
                    'completed' => $stats['completed_3b']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TireCalibration V4: Erro no health check', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
