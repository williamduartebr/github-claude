<?php

namespace Src\TestimonyCorrection\Infrastructure\Console\Schedules;

use Illuminate\Console\Scheduling\Schedule;

class HumanizedTestimonyCorrectionSchedule
{
    public static function register(Schedule $schedule): void
    {

        // Só executa em produção e staging
        if (app()->environment(['local', 'testing'])) {
            return;
        }

        $cmd = 'testimony:process --limit=1';
        $log = storage_path('logs/testimony-correction.log');

        // -------------------------------------
        // SEG - SEX: Manhã (08:00 → 12:00)
        // ⏱️ A cada 20 minutos (cron)
        // -------------------------------------
        $schedule->command($cmd)
            ->weekdays()
            ->cron('*/20 8-11 * * *')  // 08:00–11:59
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(20)
            ->runInBackground()
            ->appendOutputTo($log);

        // -------------------------------------
        // SEG - SEX: Tarde (13:30 → 18:00)
        // ⏱️ A cada 30 minutos (nativo)
        // -------------------------------------
        $schedule->command($cmd)
            ->weekdays()
            ->cron('*/30 13-17 * * *') // 13:00–17:59
            ->timezone('America/Sao_Paulo')
            ->between('13:30', '18:00')
            ->withoutOverlapping(20)
            ->runInBackground()
            ->appendOutputTo($log);

        // -------------------------------------
        // SEG - SEX: Fim de tarde/noite (18:00 → 20:00)
        // ⏱️ A cada 45 minutos (cron)
        // -------------------------------------

        $schedule->command($cmd)
            ->weekdays()
            ->cron('*/45 18-19 * * *') // 18:00–19:59
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(20)
            ->runInBackground()
            ->appendOutputTo($log);

        // -------------------------------------
        // SÁBADO (09:00 → 12:00)
        // ⏱️ A cada 40 minutos (cron)
        // -------------------------------------
        $schedule->command($cmd)
            ->saturdays()
            ->cron('*/40 9-11 * * *') // 09:00–11:59
            ->timezone('America/Sao_Paulo')
            ->withoutOverlapping(20)
            ->runInBackground()
            ->appendOutputTo($log);

        // -------------------------------------
        // DOMINGO — não roda
        // -------------------------------------
    }
}
